<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly.
/**
 * WPFM_ActionHooks class.
 */

class WPFM_ActionHooks {

    /**
     * The single instance of the class.
     *
     * @var self
     * @since 1.0.1
     */
    private static $_instance = null;

    /**
     * Init post_types.
     *
     * @since 1.0.1
     */
    public $post_types;

    /**
     * Allows for accessing single instance of class. Class should only be constructed once per call.
     *
     * @static
     * @return self Main instance.
     * @since 1.0.1
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * __construct function. 
     * get the plugin hooked in and ready.
     * 
     * @since 1.0.1
     */
    public function __construct() {
        $this->post_types = WPFM_Post_Types::instance();
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('food_manager_notify_new_user', 'wpfm_notify_new_user', 10, 2);

        // wpfm form's action.
        add_action('init', array($this, 'load_posted_form'));

        // wpfm cache helper.
        add_action('save_post', array($this, 'flush_get_food_managers_cache'));
        add_action('delete_post', array($this, 'flush_get_food_managers_cache'));
        add_action('trash_post', array($this, 'flush_get_food_managers_cache'));
        add_action('set_object_terms', array($this, 'set_term'), 10, 4);
        add_action('edited_term', array($this, 'edited_term'), 10, 3);
        add_action('create_term', array($this, 'edited_term'), 10, 3);
        add_action('delete_term', array($this, 'edited_term'), 10, 3);
        add_action('food_manager_clear_expired_transients', array($this, 'clear_expired_transients'), 10);
        add_action('transition_post_status', array($this, 'maybe_clear_count_transients'), 10, 3);

        // wpfm custom post-types.
        add_action('wp_footer', array($this, 'output_structured_data'));
        add_action('init', array($this->post_types, 'register_post_types'), 0);

        // View count action.
        add_action('set_single_listing_view_count', array($this, 'set_single_listing_view_count'));

        // wpfm shortcode's action.
        add_action('food_manager_food_dashboard_contents_edit', array($this, 'edit_food'));
        add_action('food_manager_food_filters_end', array($this, 'food_filter_results'), 30);
        add_action('food_manager_output_foods_no_results', array($this, 'output_no_results'));
        add_action('wp_ajax_term_ajax_search', array($this, 'term_ajax_search'));
        add_action('wp_ajax_nopriv_term_ajax_search', array($this, 'term_ajax_search'));
        add_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);
    }

    /**
     * Output some content when no results were found.
     * 
     * @access public
     * @return void
     * @since 1.0.1
     */
    public function output_no_results() {
        get_food_manager_template(esc_html('content-no-foods-found.php'));
    }

    /**
     * Show results div.
     * 
     * @access public
     * @return void
     * @since 1.0.1
     */
    public function food_filter_results() {
        echo '<div class="showing_applied_filters"></div>';
    }

    /**
     * Edit food form.
     * 
     * @access public
     * @return void
     * @since 1.0.1
     */
    public function edit_food() {
        global $food_manager;
        echo $food_manager->forms->get_form('edit-food');
    }

    /**
     * Set post view on the single listing page.
     * 
     * @access public
     * @return void
     * @param object $post	 
     * @since 1.0.0
     */
    public function set_single_listing_view_count($post) {
        global $post;
        $post_types = WPFM_Post_Types::instance();

        // Get the user role. 
        if (is_user_logged_in()) {
            $role = get_food_manager_current_user_role();
            $current_user = wp_get_current_user();

            if ($role != 'Administrator' && ($post->post_author != $current_user->ID)) {
                $post_types->set_post_views($post->ID);
            }
        } else {
            $post_types->set_post_views($post->ID);
        }
    }

    /**
     * output_structured_data.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function output_structured_data() {
        if (!is_single()) {
            return;
        }
        if (!wpfm_output_food_listing_structured_data()) {
            return;
        }
        $structured_data = wpfm_get_food_listing_structured_data();
        if (!empty($structured_data)) {
            echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
        }
    }
    /**
     * Maybe remove pending count transients.
     *
     * When a supported post type status is updated, check if any cached count transients need to be removed.
     *
     * @access public
     * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
     * @return void
     * @since 1.0.0
     */
    public static function maybe_clear_count_transients($new_status, $old_status, $post) {
        global $wpdb;

        /**
         * Get supported post types for count caching.
         * @param array   $post_types Post types that should be cached.
         * @param string  $new_status New post status.
         * @param string  $old_status Old post status.
         * @param WP_Post $post       Post object.
         */
        $post_types = apply_filters('wp_foodmanager_count_cache_supported_post_types', array('food_manager'), $new_status, $old_status, $post);

        // Only proceed when statuses do not match, and post type is supported post type.
        if ($new_status === $old_status || !in_array($post->post_type, $post_types)) {
            return;
        }

        /**
         * Get supported post statuses for count caching.
         * @param array   $post_statuses Post statuses that should be cached.
         * @param string  $new_status    New post status.
         * @param string  $old_status    Old post status.
         * @param WP_Post $post          Post object.
         */
        $valid_statuses = apply_filters('wp_foodmanager_count_cache_supported_statuses', array('pending'), $new_status, $old_status, $post);
        $wpfm_like          = array();

        // New status transient option name.
        if (in_array($new_status, $valid_statuses)) {
            $wpfm_like[] = "^_transient_fm_{$new_status}_{$post->post_type}_count_user_";
        }

        // Old status transient option name.
        if (in_array($old_status, $valid_statuses)) {
            $wpfm_like[] = "^_transient_fm_{$old_status}_{$post->post_type}_count_user_";
        }

        if (empty($wpfm_like)) {
            return;
        }

        $sql        = $wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name RLIKE '%s'", implode('|', $wpfm_like));
        $transients = $wpdb->get_col($sql);

        // For each transient...
        foreach ($transients as $transient) {
            // Strip away the WordPress prefix in order to arrive at the transient key.
            $key = str_replace('_transient_', '', $transient);
            // Now that we have the key, use WordPress core to delete the transient.
            delete_transient($key);
        }

        // Sometimes transients are not in the DB, so we have to do this too:
        wp_cache_flush();
    }

    /**
     * Clear expired transients.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public static function clear_expired_transients() {
        global $wpdb;
        if (!wp_using_ext_object_cache() && !defined('WP_SETUP_CONFIG') && !defined('WP_INSTALLING')) {
            $sql = "
			DELETE a, b FROM $wpdb->options a, $wpdb->options b	
			WHERE a.option_name LIKE %s	
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %s;";
            $wpdb->query($wpdb->prepare($sql, $wpdb->esc_like('_transient_fm_') . '%', $wpdb->esc_like('_transient_timeout_fm_') . '%', time()));
        }
    }

    /**
     * When any post has a term set.
     * 
     * @access public
     * @param mixed $object_id
     * @param int $terms
     * @param int $tt_ids
     * @param mixed $taxonomy
     * @return void
     * @since 1.0.0
     */
    public static function set_term($object_id = '', $terms = '', $tt_ids = '', $taxonomy = '') {
        WPFM_Cache_Helper::get_transient_version('fm_get_' . sanitize_text_field($taxonomy), true);
    }

    /**
     * When any term is edited.
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    public static function edited_term($term_id = '', $tt_id = '', $taxonomy = '') {
        WPFM_Cache_Helper::get_transient_version('fm_get_' . sanitize_text_field($taxonomy), true);
    }

    /**
     * Flush the cache.
     * 
     * @access public
     * @param mixed $post_id
     * @return void
     * @since 1.0.0
     */
    public static function flush_get_food_managers_cache($post_id) {
        if ('food_manager' === get_post_type($post_id)) {
            WPFM_Cache_Helper::get_transient_version('get_food_managers', true);
        }
    }

    /**
     * If a form was posted, load its class so that it can be processed before display.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function load_posted_form() {
        $forms = WPFM_Forms::instance();
        if (!empty($_POST['food_manager_form'])) {
            $forms->load_form_class(sanitize_title($_POST['food_manager_form']));
        }
    }

    /**
     * Register and enqueue scripts and css.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function frontend_scripts() {
        global $post;
        $ajax_url = esc_url(WPFM_Ajax::get_endpoint());
        $ajax_filter_deps = array('jquery');
        $chosen_shortcodes = array('add_food', 'food_dashboard', 'foods', 'food_categories', 'food_type');
        $chosen_used_on_page = has_wpfm_shortcode(null, $chosen_shortcodes);
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        // jQuery Chosen - vendor.
        if (apply_filters('food_manager_chosen_enabled', $chosen_used_on_page)) {
            wp_register_script('chosen', esc_url(WPFM_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js'), array('jquery'), '1.1.0', true);
            wp_register_script('wp-food-manager-term-multiselect', esc_url(WPFM_PLUGIN_URL . '/assets/js/term-multiselect.min.js'), array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_register_script('wp-food-manager-term-select-multi-appearance', esc_url(WPFM_PLUGIN_URL . '/assets/js/term-select-multi-appearance.js'), array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_register_script('wp-food-manager-multiselect', esc_url(WPFM_PLUGIN_URL . '/assets/js/multiselect.min.js'), array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_enqueue_style('chosen', esc_url(WPFM_PLUGIN_URL . '/assets/css/chosen.min.css'));
            $ajax_filter_deps[] = 'chosen';
        }
        
        wp_enqueue_style('chosen', esc_url(WPFM_PLUGIN_URL . '/assets/css/chosen.min.css'));
        
        // File upload - vendor.
        // if (apply_filters('wpfm_ajax_file_upload_enabled', true)) {
            wp_register_script('jquery-iframe-transport', esc_url(WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.iframe-transport.min.js'), array('jquery'), '1.8.3', true);
            wp_register_script('jquery-fileupload', esc_url(WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.fileupload.min.js'), array('jquery', 'jquery-iframe-transport', 'jquery-ui-widget'), '5.42.3', true);
            wp_register_script('wpfm-ajax-file-upload', esc_url(WPFM_PLUGIN_URL . '/assets/js/ajax-file-upload.js'), array('jquery', 'jquery-fileupload'), WPFM_VERSION, true);
            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'jpg'));
            $js_field_html_img = ob_get_clean();
            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'zip'));
            $js_field_html = ob_get_clean();
            wp_localize_script('wpfm-ajax-file-upload', 'wpfm_ajax_file_upload', array(
                'ajax_url' => $ajax_url,
                'js_field_html_img' => esc_js(str_replace(array("\n", "\r"), '', $js_field_html_img)),
                'js_field_html' => esc_js(str_replace(array("\n", "\r"), '', $js_field_html)),
                'i18n_invalid_file_type' => esc_html__('The file type you have mentioned is invalid.', 'wp-food-manager')
            ));
        // }
        
        // Frontend Css.
        wp_enqueue_style('wpfm-frontend', esc_url(WPFM_PLUGIN_URL . '/assets/css/frontend.css'));

        // Frontend js.
        wp_register_script('wp-food-manager-frontend', esc_url(WPFM_PLUGIN_URL . '/assets/js/frontend.min.js'), array('jquery'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-frontend');

        // Common js.
        wp_register_script('wp-food-manager-common', esc_url(WPFM_PLUGIN_URL . '/assets/js/common.js'), array('jquery'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-common');        
        // Food submission forms and validation js.
        wp_register_script('wp-food-manager-food-submission', esc_url(WPFM_PLUGIN_URL . '/assets/js/food-submission.min.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-food-submission');
        wp_localize_script('wp-food-manager-food-submission', 'wpfm_food_submission', array(
            'i18n_datepicker_format' => WPFM_Date_Time::get_datepicker_format(),
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
        ));
        wp_enqueue_script('wpfm-accounting');
        wp_enqueue_style('dashicons');
        wp_register_script('wpfm-accounting', esc_url(WPFM_PLUGIN_URL . '/assets/js/accounting/accounting.min.js'), array('jquery'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-accounting',
            'wpfm_accounting_params',
            array(
                'wpfm_sale_less_than_regular_error' => esc_html__('Please enter in a value less than the regular price.', 'woocommerce'),
            )
        );
        wp_register_script('wpfm-content-food-listing', esc_url(WPFM_PLUGIN_URL . '/assets/js/content-food-listing.min.js'), array('jquery', 'wp-food-manager-common'), WPFM_VERSION, true);
        wp_localize_script('wpfm-content-food-listing', 'wpfm_content_food_listing', array(
            'i18n_dateLabel' => esc_html__('Select Date', 'wp-food-manager'),
            'i18n_today' => esc_html__('Today', 'wp-food-manager'),
            'i18n_tomorrow' => esc_html__('Tomorrow', 'wp-food-manager'),
            'i18n_thisWeek' => esc_html__('This Week', 'wp-food-manager'),
            'i18n_nextWeek' => esc_html__('Next Week', 'wp-food-manager'),
            'i18n_thisMonth' => esc_html__('This Month', 'wp-food-manager'),
            'i18n_nextMonth' => esc_html__('Next Month', 'wp-food-manager'),
            'i18n_thisYear' => esc_html__('This Year', 'wp-food-manager'),
            'i18n_nextYear' => esc_html__('Next Month', 'wp-food-manager')
        ));

        // Ajax filters js.
        wp_register_script('wpfm-ajax-filters', esc_url(WPFM_PLUGIN_URL . '/assets/js/food-ajax-filters.js'), $ajax_filter_deps, WPFM_VERSION, true);
        wp_localize_script('wpfm-ajax-filters', 'wpfm_ajax_filters', array(
            'ajax_url' => $ajax_url,
            'is_rtl' => is_rtl() ? 1 : 0,
            'lang' => apply_filters('wpfm_lang', null)
        ));

        // Dashboard.
        wp_register_script('wp-food-manager-food-dashboard', esc_url(WPFM_PLUGIN_URL . '/assets/js/food-dashboard.min.js'), array('jquery'), WPFM_VERSION, true);
        wp_localize_script('wp-food-manager-food-dashboard', 'food_manager_food_dashboard', array(
            'i18n_btnOkLabel' => esc_html__('Delete', 'wp-food-manager'),
            'i18n_btnCancelLabel' => esc_html__('Cancel', 'wp-food-manager'),
            'i18n_confirm_delete' => esc_html__('Are you sure you want to delete this food?', 'wp-food-manager')
        ));
        wp_enqueue_style('wpfm-jquery-ui-css', esc_url(WPFM_PLUGIN_URL . '/assets/css/jquery-ui/jquery-ui.min.css'));
        wp_register_script('wpfm-slick-script', esc_url(WPFM_PLUGIN_URL . '/assets/js/slick/slick.min.js'), array('jquery'));
        wp_register_style('wpfm-slick-style', esc_url(WPFM_PLUGIN_URL . '/assets/js/slick/slick.min.css'), array());
        wp_register_style('wpfm-slick-theme-style', esc_url(WPFM_PLUGIN_URL . '/assets/js/slick/slick-theme.min.css'), array());
        wp_register_style('wpfm-grid-style', esc_url(WPFM_PLUGIN_URL . '/assets/css/wpfm-grid.min.css'));
        wp_register_style('wp-food-manager-font-style', esc_url(WPFM_PLUGIN_URL . '/assets/fonts/style.min.css'));
        wp_enqueue_style('wpfm-grid-style');
        wp_enqueue_style('wp-food-manager-font-style');
        wp_enqueue_style('wp-food-manager-food-icons-style');
        wp_enqueue_editor();
        wp_register_script('wpfm-term-autocomplete', esc_url(WPFM_PLUGIN_URL . '/assets/js/term-autocomplete.min.js'), array('jquery'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-term-autocomplete',
            'wpfm_term_autocomplete',
            array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php'))
            )
        );
    }

    /**
     * autocomplete term search feature.
     *
     * @access public
     * @return void
     * @since 1.0.1
     */
    function term_ajax_search() {
        if (!isset($_REQUEST['term']) && empty($_REQUEST['term']) && !isset($_REQUEST['taxonomy']) && empty($_REQUEST['taxonomy']))
            return;

        $results = new WP_Term_Query([
            'search'        => stripslashes($_REQUEST['term']),
            'taxonomy'        => stripslashes($_REQUEST['taxonomy']),
            'hide_empty'    => false
        ]);

        $items = array();
        if ($results->terms) {
            foreach ($results->terms as $term) {
                $items[] = apply_filters('wpfm_term_ajax_search_return_args', array(
                    'id' => $term->term_id,
                    'label' => $term->name,
                    'description' => term_description($term->term_id, $_REQUEST['taxonomy']),
                ), array('term_id' => $term->term_id));
            }
        }

        wp_send_json_success($items);
    }
    
    /**
     * Save the food data from frontend side is handle by this function.
     *
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @param array $form_fields
     * @return void
     * @since 1.0.0
     */
    public function food_manager_save_food_manager_data($post_id, $post, $form_fields){
        global $wpdb;
        $thumbnail_image = array();
        // Save Food Form fields values.
        if (isset($form_fields['food'])) {
            foreach ($form_fields['food'] as $key => $field) {
                $type = !empty($field['type']) ? $field['type'] : '';
                // Food Banner.
                if ('food_banner' === $key) {
                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        $thumbnail_image = is_array($_POST[$key]) ? array_values(array_filter($_POST[$key])) : $_POST[$key];
                        // Update Food Banner Meta Data.
                        update_post_meta($post_id, '_' . esc_attr($key), $thumbnail_image);
                        if (is_array($_POST[$key])) {
                            $_POST[$key] = array_values(array_filter($_POST[$key]));
                        }
                    }

                    // Create Attachments ( If not exist ).
                    if (!is_admin()) {
                        $maybe_attach = array_filter((array) $thumbnail_image);
                        // Handle attachments.
                        if (sizeof($maybe_attach) && apply_filters('wpfm_attach_uploaded_files', true)) {
                            // Get attachments.
                            $attachments = get_posts('post_parent=' . $post_id . '&post_type=attachment&fields=ids&numberposts=-1');
                            $attachment_urls = array();
                            // Loop attachments already attached to the food.
                            foreach ($attachments as $attachment_key => $attachment) {
                                $attachment_urls[] = wp_get_attachment_url($attachment);
                            }
                            foreach ($maybe_attach as $key => $attachment_url) {
                                if (!in_array($attachment_url, $attachment_urls) && !is_numeric($attachment_url)) {
                                    $WPFM_Add_Food_Form = WPFM_Add_Food_Form::instance();
                                    $attachment_id = $WPFM_Add_Food_Form->create_attachment($attachment_url);
                                    // Set first image of banner as a thumbnail.
                                    if ($key == 0) {
                                        set_post_thumbnail($post_id, $attachment_id);
                                    }
                                }
                            }
                        }
                    } else {
                        $image = get_the_post_thumbnail_url($post_id);
                        if (empty($image)) {
                            if (isset($thumbnail_image) && !empty($thumbnail_image)) {
                                $wp_upload_dir = wp_get_upload_dir();
                                $baseurl = $wp_upload_dir['baseurl'] . '/';
                                $wp_attached_file = str_replace($baseurl, '', $thumbnail_image);
                                $args = array(
                                    'meta_key' => '_wp_attached_file',
                                    'meta_value' => $wp_attached_file,
                                    'post_type' => 'attachment',
                                    'posts_per_page' => 1,
                                );
                                $attachments = get_posts($args);
                                if (!empty($attachments)) {
                                    foreach ($attachments as $attachment) {
                                        set_post_thumbnail($post_id, $attachment->ID);
                                    }
                                }
                            }
                        }
                    }
                }

                // Other form field's value.
                switch ($type) {
                    case 'textarea':
                        if (isset($_POST[$key])) {
                            update_post_meta($post_id, '_' . esc_attr($key), wp_kses_post(stripslashes($_POST[$key])));
                        }
                        break;

                    case 'date':
                        if (isset($_POST[$key])) {
                            $date = $_POST[$key];
                            $datepicker_date_format = !empty(get_option('date_format')) ? get_option('date_format') : 'F j, Y';
                            $php_date_format = WPFM_Date_Time::get_view_date_format_from_datepicker_date_format($datepicker_date_format);
                            // Convert date and time value into DB formatted format and save eg. 1970-01-01.
                            $date_dbformatted = WPFM_Date_Time::date_parse_from_format($php_date_format, $date);
                            $date_dbformatted = !empty($date_dbformatted) ? $date_dbformatted : $date;
                            update_post_meta($post_id, '_' . esc_attr($key), $date_dbformatted);
                        }
                        break;

                    default:
                        if (!isset($_POST[$key])) {
                            update_post_meta($post_id, '_' . esc_attr($key), '');
                            continue 2;
                        } elseif (is_array($_POST[$key])) {
                            update_post_meta($post_id, '_' . esc_attr($key), array_filter(array_map('sanitize_text_field', $_POST[$key])));
                        } else {
                            update_post_meta($post_id, '_' . esc_attr($key), sanitize_text_field($_POST[$key]));
                        }
                        break;
                }
                $unit_ids = [];
                $ingredient_ids = [];
                $nutrition_ids = [];

                // Set Ingredients.
                if ($key = 'food_ingredients') {
                    $taxonomy = 'food_manager_ingredient';
                    $multi_array_ingredient = array();

                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        foreach ($_POST[$key] as $id => $ingredient) {
                            $term_name = esc_attr(get_term($id)->name);
                            $unit_name = "Unit";

                            if ($ingredient['unit_id'] == '' && empty($ingredient['unit_id'])) {
                                $unit_name = "Unit";
                            } else {
                                $unit_name = esc_attr(get_term($ingredient['unit_id'])->name);
                            }

                            $item = [
                                'id' => $id,
                                'unit_id' => !empty($ingredient['unit_id']) ? $ingredient['unit_id'] : null,
                                'value' => !empty($ingredient['value']) ? $ingredient['value'] : null,
                                'ingredient_term_name' => $term_name,
                                'unit_term_name' => $unit_name
                            ];

                            $multi_array_ingredient[$id] = $item;
                            $ingredient_ids[] = $id;
                            if (trim($ingredient['unit_id'])) {
                                $unit_ids[] = (int) $ingredient['unit_id'];
                            }
                        }
                        update_post_meta($post_id, '_' . esc_attr($key), $multi_array_ingredient);
                    } else {
                        update_post_meta($post_id, '_' . esc_attr($key), array());
                    }

                    $exist_ingredients = get_the_terms($post_id, $taxonomy);
                    if ($exist_ingredients) {
                        $removed_ingredient_ids = [];
                        foreach ($exist_ingredients as $ingredient) {
                            if (!in_array($ingredient->term_id, $ingredient_ids)) {
                                $removed_ingredient_ids[] = $ingredient->term_id;
                            }
                        }
                        wp_remove_object_terms($post_id, $removed_ingredient_ids, $taxonomy);
                    }

                    if (!empty($ingredient_ids)) {
                        wp_set_object_terms($post_id, $ingredient_ids, $taxonomy);
                    }
                }

                // Set Nutrition.
                if ($key = 'food_nutritions') {
                    $taxonomy = 'food_manager_nutrition';
                    $multi_array_nutrition = array();

                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        foreach ($_POST[$key] as $id => $nutrition) {
                            $term_name = esc_attr(get_term($id)->name);
                            $unit_name = "Unit";
                            if ($nutrition['unit_id'] == '' && empty($nutrition['unit_id'])) {
                                $unit_name = "Unit";
                            } else {
                                $unit_name = esc_attr(get_term($nutrition['unit_id'])->name);
                            }

                            $item = [
                                'id' => $id,
                                'unit_id' => !empty($nutrition['unit_id']) ? $nutrition['unit_id'] : null,
                                'value' => !empty($nutrition['value']) ? $nutrition['value'] : null,
                                'nutrition_term_name' => $term_name,
                                'unit_term_name' => $unit_name
                            ];

                            $multi_array_nutrition[$id] = $item;
                            $nutrition_ids[] = $id;
                            if (trim($nutrition['unit_id'])) {
                                $unit_ids[] = (int) $nutrition['unit_id'];
                            }
                        }
                        update_post_meta($post_id, '_' . esc_attr($key), $multi_array_nutrition);
                    } else {
                        update_post_meta($post_id, '_' . esc_attr($key), array());
                    }

                    $exist_nutritions = get_the_terms($post_id, 'food_manager_nutrition');
                    if ($exist_nutritions) {
                        $removed_nutrition_ids = [];
                        foreach ($exist_nutritions as $nutrition) {
                            if (!in_array($nutrition->term_id, $nutrition_ids)) {
                                $removed_nutrition_ids[] = $nutrition->term_id;
                            }
                        }
                        wp_remove_object_terms($post_id, $removed_nutrition_ids, 'food_manager_nutrition');
                    }
                    if (!empty($nutrition_ids)) {
                        wp_set_object_terms($post_id, $nutrition_ids, 'food_manager_nutrition');
                    }
                }

                $exist_units = get_the_terms($post_id, 'food_manager_unit');
                $taxonomy = 'food_manager_unit';
                if ($exist_units) {
                    $removed_unit_ids = [];

                    foreach ($exist_units as $unit) {
                        if (!in_array($unit->term_id, $unit_ids)) {
                            $removed_unit_ids[] = $unit->term_id;
                        }
                    }
                    wp_remove_object_terms($post_id, $removed_unit_ids, $taxonomy);
                }

                if ($unit_ids) {
                    wp_set_object_terms($post_id, $unit_ids, $taxonomy);
                }

                if (isset($field['taxonomy']) && !empty($field['taxonomy'])) {
                    if ($field['taxonomy'] != 'food_manager_ingredient' || $field['taxonomy'] != 'food_manager_nutrition') {
                        $terms = isset($field['value']) && !empty($field['value']) ? $field['value'] : '';
                        if ($field['taxonomy'] == 'food_manager_type') {
                            if (empty($terms)) {
                                $terms = isset($_POST['food_type']) && !empty($_POST['food_type']) ? $_POST['food_type'] : '';
                            }
                        }
                        if (is_array($terms)) {
                            $terms = array_map(function ($value) {
                                return (int) $value;
                            }, $terms);
                            wp_set_object_terms($post_id, $terms, $field['taxonomy'], false);
                        } else {
                            if (!empty($terms)) {
                                wp_set_object_terms($post_id, array((int) $terms), $field['taxonomy'], false);
                            }
                        }
                    }
                }

                // Food Tags.
                if ($key = 'food_tag') {
                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        $food_tag = explode(',', $_POST[$key]);
                        $food_tag = array_map('trim', $food_tag);
                        wp_set_object_terms($post_id, $food_tag, 'food_manager_tag');
                    }
                }
            }
        }

        $toppings_arr = array();
        if (isset($form_fields['toppings'])) {
            $toppings_meta = array();
            $topping_cnt = 0;
            foreach ($form_fields['toppings'] as $key => $field) {
                $topping_cnt++;

                // Set toppings meta and assign them into food.
                $taxonomy = 'food_manager_topping';
                if (isset($_POST['repeated_options']) && !empty($_POST['repeated_options'])) {
                    foreach ($_POST['repeated_options'] as $count) {
                        $option_values = array();
                        if (isset($_POST['option_value_count'])) {
                            $find_option = array_search('__repeated-option-index__', $_POST['option_value_count']);
                            if ($find_option !== false) {

                                // Remove from array.
                                unset($_POST['option_value_count'][$find_option]);
                            }

                            foreach ($_POST['option_value_count'] as $option_key_count) {
                                if ($option_key_count && is_array($option_key_count)) {
                                    foreach ($option_key_count as $option_value_count) {
                                        if (!empty($_POST[$count . '_option_name_' . $option_value_count]) || !empty($_POST[$count . '_option_price_' . $option_value_count])) {
                                            $option_values[$option_value_count] = apply_filters('wpfm_topping_options_values_array', array(
                                                'option_name' => isset($_POST[$count . '_option_name_' . $option_value_count]) ? $_POST[$count . '_option_name_' . $option_value_count] : '',
                                                'option_price' => isset($_POST[$count . '_option_price_' . $option_value_count]) ? $_POST[$count . '_option_price_' . $option_value_count] : '',
                                            ), array('option_count' => $count, 'option_value_count' => $option_value_count));
                                        }
                                    }
                                } else {
                                    if (!empty($_POST[$count . '_option_name_' . $option_key_count]) || !empty($_POST[$count . '_option_price_' . $option_key_count])) {
                                        $option_values[$option_key_count] = apply_filters('wpfm_topping_options_values_array', array(
                                            'option_name' => isset($_POST[$count . '_option_name_' . $option_key_count]) ? $_POST[$count . '_option_name_' . $option_key_count] : '',
                                            'option_price' => isset($_POST[$count . '_option_price_' . $option_key_count]) ? $_POST[$count . '_option_price_' . $option_key_count] : '',
                                        ), array('option_count' => $count, 'option_value_count' => $option_key_count));
                                    }
                                }
                            }
                        }

                        if ($key == 'topping_name') {
                            $toppings_arr[] = isset($_POST[$key . '_' . $count]) ? esc_attr($_POST[$key . '_' . $count]) : '';
                        }

                        if ($key == 'topping_description') {
                            $toppings_meta[$count]['_' . $key] = isset($_POST[$key . '_' . $count]) && !empty($_POST[$key . '_' . $count]) ? wp_kses_post($_POST[$key . '_' . $count]) : '';
                        } else {
                            // Toppings Array.
                            $toppings_meta[$count]['_' . $key] = isset($_POST[$key . '_' . $count]) && !empty($_POST[$key . '_' . $count]) ? esc_attr($_POST[$key . '_' . $count]) : '';
                        }
                        if ($key == 'topping_options') {
                            $toppings_meta[$count]['_' . $key] = $option_values;
                        }
                    }
                }

                $exist_toppings = get_the_terms($post_id, $taxonomy);
                if ($exist_toppings) {
                    $removed_toppings_ids = [];
                    foreach ($exist_toppings as $toppings) {
                        if (!in_array($toppings->name, $toppings_arr)) {
                            $removed_toppings_ids[] = (int) $toppings->term_id;
                        }
                    }
                    wp_remove_object_terms($post_id, $removed_toppings_ids, $taxonomy);
                }
                $term_ids = wp_set_object_terms($post_id, $toppings_arr, $taxonomy);
                if ($term_ids) {
                    foreach ($term_ids as $t_key => $term_id) {
                        $t_key++;
                        $description = (isset($_POST['topping_description_' . $t_key]) && !empty($_POST['topping_description_' . $t_key])) ? $_POST['topping_description_' . $t_key] : '';
                        wp_update_term($term_id, $taxonomy, array('description' => wp_kses_post($description)));
                        do_action('wpfm_save_topping_meta_field', array('term_id' => absint($term_id), 'taxonomy' => esc_attr($taxonomy), 'count' => absint($t_key)));
                    }
                }
            }
            update_post_meta($post_id, '_food_toppings', $toppings_meta);
        }

        // Update repeated_options meta for the count of toppings.
        $repeated_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
        if (is_array($repeated_options)) {
            $repeated_options = array_map('esc_attr', $repeated_options);
        }
        update_post_meta($post_id, '_food_repeated_options', $repeated_options);

        // Set orders according to previous inserted post.
        $order_menu = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($post_id));
        if ($order_menu && $order_menu[0]->menu_order == 0) {
            $last_inserted_post = get_posts(
                array(
                    'post_type' => 'food_manager',
                    'posts_per_page' => 2,
                    'offset' => 0,
                    'orderby' => 'ID',
                    'order' => 'DESC',
                    'post_status' => 'any',
                )
            );

            if ($last_inserted_post) {
                $last_menu_order = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($last_inserted_post[0]->ID));
                $next_menu_order = intval($last_menu_order[0]->menu_order) + 1;
                $wpdb->update($wpdb->posts, ['menu_order' => $next_menu_order], ['ID' => intval($post_id)]);
            } else {
                $wpdb->update($wpdb->posts, ['menu_order' => 1], ['ID' => intval($post_id)]);
            }
        }
        remove_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);
        $food_data = array(
            'ID' => intval($post_id),
        );
        wp_update_post($food_data);
        add_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);
    }
}
WPFM_ActionHooks::instance();
