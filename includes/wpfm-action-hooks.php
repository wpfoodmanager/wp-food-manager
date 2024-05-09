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

        // wpfm ajax's action.
        add_action('init', array($this, 'add_endpoint'));
        add_action('template_redirect', array($this, 'do_fm_ajax'), 0);

        // FM Ajax endpoints.
        add_action('food_manager_ajax_get_listings', array($this, 'get_listings'));
        add_action('food_manager_ajax_upload_file', array($this, 'upload_file'));

        // BW compatible handlers.
        add_action('wp_ajax_nopriv_food_manager_get_listings', array($this, 'get_listings'));
        add_action('wp_ajax_food_manager_get_listings', array($this, 'get_listings'));
        add_action('wp_ajax_nopriv_food_manager_upload_file', array($this, 'upload_file'));
        add_action('wp_ajax_food_manager_upload_file', array($this, 'upload_file'));

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

        // View count action.
        add_action('set_single_listing_view_count', array($this, 'set_single_listing_view_count'));

        // wpfm shortcode's action.
        add_action('food_manager_food_dashboard_contents_edit', array($this, 'edit_food'));
        add_action('food_manager_food_filters_end', array($this, 'food_filter_results'), 30);
        add_action('food_manager_output_foods_no_results', array($this, 'output_no_results'));
        add_action('wp_ajax_term_ajax_search',        array($this, 'term_ajax_search'));
        add_action('wp_ajax_nopriv_term_ajax_search', array($this, 'term_ajax_search'));
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
        $rlike          = array();

        // New status transient option name.
        if (in_array($new_status, $valid_statuses)) {
            $rlike[] = "^_transient_fm_{$new_status}_{$post->post_type}_count_user_";
        }

        // Old status transient option name.
        if (in_array($old_status, $valid_statuses)) {
            $rlike[] = "^_transient_fm_{$old_status}_{$post->post_type}_count_user_";
        }

        if (empty($rlike)) {
            return;
        }

        $sql        = $wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name RLIKE '%s'", implode('|', $rlike));
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
     * Upload file via ajax.
     * No nonce field since the form may be statically cached.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function upload_file() {
        if (!food_manager_user_can_upload_file_via_ajax()) {
            wp_send_json_error(new WP_Error('upload', __('You must be logged in to upload files using this method.', 'wp-food-manager')));
            return;
        }

        $data = array('files' => array());
        if (!empty($_FILES)) {
            foreach ($_FILES as $file_key => $file) {
                $files_to_upload = wpfm_prepare_uploaded_files($file);
                foreach ($files_to_upload as $file_to_upload) {
                    $uploaded_file = wpfm_upload_file($file_to_upload, array('file_key' => $file_key));
                    if (is_wp_error($uploaded_file)) {
                        $data['files'][] = array('error' => $uploaded_file->get_error_message());
                    } else {
                        $data['files'][] = $uploaded_file;
                    }
                }
            }
        }

        wp_send_json($data);
    }

    /**
     * Get listings via ajax.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function get_listings() {
        global $wp_post_types;

        $result            = array();
        $search_keywords   = sanitize_text_field(stripslashes($_REQUEST['search_keywords']));
        $search_categories = isset($_REQUEST['search_categories']) ? $_REQUEST['search_categories'] : '';
        $search_food_types = isset($_REQUEST['search_food_types']) ? $_REQUEST['search_food_types'] : '';
        $search_food_menu  = isset($_REQUEST['search_food_menu']) ? $_REQUEST['search_food_menu'] : '';
        $post_type_label   = $wp_post_types['food_manager']->labels->name;
        $orderby           = sanitize_text_field($_REQUEST['orderby']);

        if (is_array($search_categories)) {
            $search_categories = array_filter(array_map('sanitize_text_field', array_map('stripslashes', $search_categories)));
        } else {
            $search_categories = array_filter(array(sanitize_text_field(stripslashes($search_categories))));
        }
        if (is_array($search_food_types)) {
            $search_food_types = array_filter(array_map('sanitize_text_field', array_map('stripslashes', $search_food_types)));
        } else {
            $search_food_types = array_filter(array(sanitize_text_field(stripslashes($search_food_types))));
        }

        $args = array(
            'search_keywords'    => $search_keywords,
            'search_categories'  => $search_categories,
            'search_food_types'  => $search_food_types,
            'search_food_menu'   => $search_food_menu,
            'orderby'            => $orderby,
            'order'              => sanitize_text_field($_REQUEST['order']),
            'offset'             => (absint($_REQUEST['page']) - 1) * absint($_REQUEST['per_page']),
            'posts_per_page'     => absint($_REQUEST['per_page'])
        );

        if (isset($_REQUEST['featured']) && ($_REQUEST['featured'] === 'true' || $_REQUEST['featured'] === 'false')) {
            $args['featured'] = $_REQUEST['featured'] === 'true' ? true : false;
            $args['orderby']  = 'featured' === $orderby ? 'date' : $orderby;
        }

        ob_start();
        $foods = get_food_listings(apply_filters('food_manager_get_listings_args', $args));
        $result['found_foods'] = false;
        $food_cnt = 0;

        if ($foods->have_posts()) : $result['found_foods'] = true; ?>
            <?php while ($foods->have_posts()) : $foods->the_post(); ?>
                <?php
                if (get_option('food_manager_food_item_show_hide') == 0 && get_stock_status() !== 'fm_outofstock') {
                    $food_cnt++;
                } elseif (get_option('food_manager_food_item_show_hide') == 1 && get_stock_status()) {
                    $food_cnt++;
                }
                get_food_manager_template_part('content', 'food_manager'); ?>
            <?php endwhile; ?>
            <?php else :

            // Check there is a publish food or not.
            $default_foods = get_posts(array(
                'numberposts' => -1,
                'post_type'   => 'food_manager',
                'post_status' => 'publish'
            ));
            if (count($default_foods) == 0) { ?>
                <div class="no_food_listings_found wpfm-alert wpfm-alert-danger"><?php _e('There is no food item listed in your food manager.', 'wp-food-manager'); ?></div>
            <?php } else {
                get_food_manager_template_part('content', 'no-foods-found');
            }
        endif;

        $result['html']    = ob_get_clean();
        $result['filter_value'] = array();

        // Categories.
        if ($search_categories) {
            $showing_categories = array();
            foreach ($search_categories as $category) {
                $category_object = get_term_by(is_numeric($category) ? 'id' : 'slug', $category, 'food_manager_category');
                if (!is_wp_error($category_object)) {
                    $showing_categories[] = $category_object->name;
                }
            }
            $result['filter_value'][] = implode(', ', $showing_categories);
        }

        // Food types.
        if ($search_food_types) {
            $showing_food_types = array();
            foreach ($search_food_types as $food_type) {
                $food_type_object = get_term_by(is_numeric($food_type) ? 'id' : 'slug', $food_type, 'food_manager_type');
                if (!is_wp_error($food_type_object)) {
                    $showing_food_types[] = $food_type_object->name;
                }
            }
            $result['filter_value'][] = implode(', ', $showing_food_types);
        }

        // Food Menu.
        $hide_flag = 0;
        if (is_array($search_food_menu) && implode(',', $search_food_menu)) {
            $showing_food_menus = array();
            foreach ($search_food_menu as $food_menu) {
                $food_item_ids = get_post_meta($food_menu, '_food_item_ids', true);
                if ($food_item_ids) {
                    foreach ($food_item_ids as $food_item_id) {
                        $showing_food_menus[] = $food_item_id;
                    }
                }
            }
            if (count($showing_food_menus) <= 0) $hide_flag = 1;
            else $hide_flag = 0;
            $result['filter_value'][] = implode(', ', $showing_food_menus);
        }

        if ($search_keywords) {
            $result['filter_value'][] = '&ldquo;' . $search_keywords . '&rdquo;';
        }

        $last_filter_value = array_pop($result['filter_value']);
        $result_implode = implode(', ', $result['filter_value']);

        if (count($result['filter_value']) >= 1) {
            $result['filter_value'] = explode(" ", $result_implode);
            $result['filter_value'][] = " &amp; ";
        } else {
            if (!empty($last_filter_value))
                $result['filter_value'] = explode(" ", $result_implode);
        }

        $result['filter_value'][] = $last_filter_value . " " . $post_type_label;

        if (sizeof($result['filter_value']) > 1) {
            $message = sprintf(_n('Search completed. Found %d matching record.', 'Search completed. Found %d matching records.', $foods->found_posts, 'wp-food-manager'), $foods->found_posts);
            $result['showing_applied_filters'] = true;
        } else {
            $message = "";
            $result['showing_applied_filters'] = false;
        }

        $search_values = array(
            'keywords'   => $search_keywords,
            'types'      => $search_food_types,
            'categories' => $search_categories
        );
        $result['filter_value'] = apply_filters('food_manager_get_listings_custom_filter_text', $message, $search_values);

        // Generate RSS link.
        $result['showing_links'] = wpfm_get_filtered_links(array(
            'search_keywords'   => $search_keywords,
            'search_categories' => $search_categories,
            'search_food_types' => $search_food_types,
            'search_food_menu'  => $search_food_menu,
        ));
        $result['max_num_pages'] = $foods->max_num_pages;

        if ($hide_flag) {
            $result['html'] = '<div class="no_food_listings_found wpfm-alert wpfm-alert-danger">There are no foods matching your search.</div>';
            $result['found_foods'] = '';
        }

        wp_send_json(apply_filters('food_manager_get_listings_result', $result, $foods));
    }

    /**
     * Check for WC Ajax request and fire action.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public static function do_fm_ajax() {
        global $wp_query;

        if (!empty($_GET['fm-ajax'])) {
            $wp_query->set('fm-ajax', sanitize_text_field($_GET['fm-ajax']));
        }

        if ($action = $wp_query->get('fm-ajax')) {
            if (!defined('DOING_AJAX')) {
                define('DOING_AJAX', true);
            }
            // Not home - this is an ajax endpoint.
            $wp_query->is_home = false;
            do_action('food_manager_ajax_' . sanitize_text_field($action));
            die();
        }
    }

    /**
     * Add our endpoint for frontend ajax requests.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public static function add_endpoint() {
        add_rewrite_tag('%fm-ajax%', '([^/]*)');
        add_rewrite_rule('fm-ajax/([^/]*)/?', 'index.php?fm-ajax=$matches[1]', 'top');
        add_rewrite_rule('index.php/fm-ajax/([^/]*)/?', 'index.php?fm-ajax=$matches[1]', 'top');
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
        if (apply_filters('wpfm_ajax_file_upload_enabled', true)) {
            wp_register_script('jquery-iframe-transport', esc_url(WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.iframe-transport.min.js'), array('jquery'), '1.8.3', true);
            wp_register_script('jquery-fileupload', esc_url(WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.fileupload.min.js'), array('jquery', 'jquery-iframe-transport', 'jquery-ui-widget'), '5.42.3', true);
            wp_register_script('wpfm-ajax-file-upload', esc_url(WPFM_PLUGIN_URL . '/assets/js/ajax-file-upload.min.js'), array('jquery', 'jquery-fileupload'), WPFM_VERSION, true);
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
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');

        // Frontend Css.
        wp_enqueue_style('wpfm-frontend', esc_url(WPFM_PLUGIN_URL . '/assets/css/frontend.css'));

        // Frontend js.
        wp_register_script('wp-food-manager-frontend', esc_url(WPFM_PLUGIN_URL . '/assets/js/frontend.min.js'), array('jquery'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-frontend');

        // Common js.
        wp_register_script('wp-food-manager-common', esc_url(WPFM_PLUGIN_URL . '/assets/js/common.min.js'), array('jquery'), WPFM_VERSION, true);
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
        wp_register_script('wpfm-ajax-filters', esc_url(WPFM_PLUGIN_URL . '/assets/js/food-ajax-filters.min.js'), $ajax_filter_deps, WPFM_VERSION, true);
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
}
WPFM_ActionHooks::instance();
