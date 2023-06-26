<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
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
     * Init settings_page.
     *
     * @since 1.0.1
     */
    public $settings_page;

    /**
     * Init settings_page.
     *
     * @since 1.0.1
     */
    public $settings_group;

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
     * get the plugin hooked in and ready
     * 
     * @since 1.0.1
     */
    public function __construct() {
        $this->post_types = WPFM_Post_Types::instance();
        $this->settings_group = 'food_manager';
        add_action('food_manager_type_add_form_fields', array($this, 'add_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('created_food_manager_type', array($this, 'save_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('food_manager_type_edit_form_fields', array($this, 'update_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('edited_food_manager_type', array($this, 'updated_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'custom_taxonomy_load_media_for_food_type'));
        add_action('admin_footer', array($this, 'add_custom_taxonomy_script_for_food_type'));
        add_action('manage_food_manager_type_custom_column', array($this, 'display_custom_taxonomy_image_column_value_for_food_type'), 10, 3);
        add_action('food_manager_category_add_form_fields', array($this, 'add_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('created_food_manager_category', array($this, 'save_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('food_manager_category_edit_form_fields', array($this, 'update_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('edited_food_manager_category', array($this, 'updated_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'custom_taxonomy_load_media_for_food_category'));
        add_action('admin_footer', array($this, 'add_custom_taxonomy_script_for_food_category'));
        add_action('manage_food_manager_category_custom_column', array($this, 'display_custom_taxonomy_image_column_value_for_food_category'), 10, 3);
        add_action('after_switch_theme', array($this->post_types, 'register_post_types'), 11);
        add_action('after_switch_theme', 'flush_rewrite_rules', 15);
        add_action('after_setup_theme', array($this, 'load_plugin_textdomain'));
        add_action('after_setup_theme', array($this, 'include_template_functions'), 11);
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('food_manager_notify_new_user', 'wpfm_notify_new_user', 10, 2);
        add_action('admin_menu', array($this, 'admin_menu'), 12);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('current_screen', array($this, 'conditional_includes'));
        add_action('wp_ajax_get_group_field_html', array($this, 'get_group_field_html'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_init', array($this, 'redirect'));

        // Writepanel's Actions
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_post'), 1, 2);
        add_action('admin_init', array($this, 'approve_food'));
        add_action('load-edit.php', array($this, 'do_bulk_actions'));
        add_action('admin_footer-edit.php', array($this, 'add_bulk_actions'));
        add_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);

        // Food menu 
        add_action('wp_ajax_wpfm_get_food_listings_by_category_id', array($this, 'get_food_listings_by_category_id'));
        add_action('food_manager_save_food_manager_menu', array($this, 'food_manager_save_food_manager_menu_data'), 20, 2);
        add_action('manage_food_manager_menu_posts_custom_column', array($this, 'shortcode_copy_content_column'), 10, 2);
        add_action('manage_food_manager_posts_custom_column', array($this, 'custom_food_content_column'), 10, 2);
        add_action('admin_notices', array($this, 'display_notice'));

        // wpfm form's action
        add_action('init', array($this, 'load_posted_form'));

        // wpfm ajax's action
        add_action('init', array($this, 'add_endpoint'));
        add_action('template_redirect', array($this, 'do_fm_ajax'), 0);

        // FM Ajax endpoints
        add_action('food_manager_ajax_get_listings', array($this, 'get_listings'));
        add_action('food_manager_ajax_upload_file', array($this, 'upload_file'));

        // BW compatible handlers
        add_action('wp_ajax_nopriv_food_manager_get_listings', array($this, 'get_listings'));
        add_action('wp_ajax_food_manager_get_listings', array($this, 'get_listings'));
        add_action('wp_ajax_nopriv_food_manager_upload_file', array($this, 'upload_file'));
        add_action('wp_ajax_food_manager_upload_file', array($this, 'upload_file'));
        add_action('wp_ajax_wpfm_extra_option_tab', array($this, 'get_fieldtype_action'));
        add_action('wp_ajax_wpfm-logo-update-menu-order', array($this, 'menuUpdateOrder'));

        // wpfm cache helper
        add_action('save_post', array($this, 'flush_get_food_managers_cache'));
        add_action('delete_post', array($this, 'flush_get_food_managers_cache'));
        add_action('trash_post', array($this, 'flush_get_food_managers_cache'));
        add_action('set_object_terms', array($this, 'set_term'), 10, 4);
        add_action('edited_term', array($this, 'edited_term'), 10, 3);
        add_action('create_term', array($this, 'edited_term'), 10, 3);
        add_action('delete_term', array($this, 'edited_term'), 10, 3);
        add_action('food_manager_clear_expired_transients', array($this, 'clear_expired_transients'), 10);
        add_action('transition_post_status', array($this, 'maybe_clear_count_transients'), 10, 3);

        // wpfm custom post-types
        add_action('init', array($this->post_types, 'register_post_types'), 0);
        add_action('wp_footer', array($this, 'output_structured_data'));

        // View count action
        add_action('set_single_listing_view_count', array($this, 'set_single_listing_view_count'));
        if (get_option('food_manager_enable_categories')) {
            add_action('restrict_manage_posts', array($this, 'foods_by_category'));
        }
        if (get_option('food_manager_enable_food_types') && get_option('food_manager_enable_categories')) {
            add_action('restrict_manage_posts', array($this, 'foods_by_food_type'));
        }

        // wpfm shortcode's action
        add_action('food_manager_food_dashboard_content_edit', array($this, 'edit_food'));
        add_action('food_manager_food_filters_end', array($this, 'food_filter_results'), 30);
        add_action('food_manager_output_foods_no_results', array($this, 'output_no_results'));
        add_action('wp_ajax_term_ajax_search',        array($this, 'term_ajax_search'));
        add_action('wp_ajax_nopriv_term_ajax_search', array($this, 'term_ajax_search'));
    }

    /**
     * Output some content when no results were found
     * 
     * @access public
     * @return void
     * @since 1.0.1
     */
    public function output_no_results() {
        get_food_manager_template(esc_html('content-no-foods-found.php'));
    }

    /**
     * Show results div
     * 
     * @access public
     * @return void
     * @since 1.0.1
     */
    public function food_filter_results() {
        echo '<div class="showing_applied_filters"></div>';
    }

    /**
     * Edit food form
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
     * Show Food type dropdown
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function foods_by_food_type() {
        global $typenow, $wp_query;
        if ($typenow != 'food_manager' || !taxonomy_exists('food_manager_type')) {
            return;
        }

        $r = array();
        $r['pad_counts']   = 1;
        $r['hierarchical'] = 1;
        $r['hide_empty']   = 0;
        $r['show_count']   = 1;
        $r['selected']     = (isset($wp_query->query['food_manager_type'])) ? $wp_query->query['food_manager_type'] : '';
        $r['menu_order']   = false;
        $terms             = get_terms('food_manager_type', $r);
        $walker            = WPFM_Category_Walker::instance();

        if (!$terms) {
            return;
        }

        $output  = "<select name='food_manager_type' id='dropdown_food_manager_type'>";
        $output .= '<option value="" ' . selected(isset($_GET['food_manager_type']) ? $_GET['food_manager_type'] : '', '', false) . '>' . esc_html(__('Select Food Type', 'wp-food-manager')) . '</option>';
        $output .= $walker->walk($terms, 0, $r);
        $output .= '</select>';
        printf('%s', $output);
    }

    /**
     * Show category dropdown
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function foods_by_category() {
        global $typenow, $wp_query;
        if ($typenow != 'food_manager' || !taxonomy_exists('food_manager_category')) {
            return;
        }

        include_once WPFM_PLUGIN_DIR . '/includes/wpfm-category-walker.php';

        $r = array();
        $r['pad_counts'] = 1;
        $r['hierarchical'] = 1;
        $r['hide_empty'] = 0;
        $r['show_count'] = 1;
        $r['selected'] = (isset($wp_query->query['food_manager_category'])) ? $wp_query->query['food_manager_category'] : '';
        $r['menu_order'] = false;
        $terms = get_terms('food_manager_category', $r);
        $walker = WPFM_Category_Walker::instance();

        if (!$terms) {
            return;
        }

        $output = "<select name='food_manager_category' id='dropdown_food_manager_category'>";
        $output .= '<option value="" ' . selected(isset($_GET['food_manager_category']) ? $_GET['food_manager_category'] : '', '', false) . '>' . esc_html(__('Select Food Category', 'wp-food-manager')) . '</option>';
        $output .= $walker->walk($terms, 0, $r);
        $output .= '</select>';
        printf('%s', $output);
    }

    /**
     * Set post view on the single listing page
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
     * output_structured_data
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
     * Add the pending count which food submitted from frontend and admin gets pending food count.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_head() {

        /**
         * Add styles just for this page and remove dashboard page links.
         *
         * @since 1.0.0
         */
        remove_submenu_page('index.php', 'food_manager_setup');

        global $menu;
        $plural      = __('Food Manager', 'wp-food-manager');
        $count_foods = wp_count_posts('food_manager', 'readable');
        if (!empty($menu) && is_array($menu)) {
            foreach ($menu as $key => $menu_item) {
                if (strpos($menu_item[0], $plural) === 0) {
                    if ($order_count = $count_foods->pending) {
                        $menu[$key][0] .= " <span class='awaiting-mod update-plugins count-$order_count'><span class='pending-count'>" . number_format_i18n($count_foods->pending) . "</span></span>";
                    }
                    break;
                }
            }
        }
    }

    /**
     * Maybe remove pending count transients
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
         * Get supported post types for count caching
         * @param array   $post_types Post types that should be cached.
         * @param string  $new_status New post status.
         * @param string  $old_status Old post status.
         * @param WP_Post $post       Post object.
         */
        $post_types = apply_filters('wp_foodmanager_count_cache_supported_post_types', array('food_manager'), $new_status, $old_status, $post);

        // Only proceed when statuses do not match, and post type is supported post type
        if ($new_status === $old_status || !in_array($post->post_type, $post_types)) {
            return;
        }

        /**
         * Get supported post statuses for count caching
         * @param array   $post_statuses Post statuses that should be cached.
         * @param string  $new_status    New post status.
         * @param string  $old_status    Old post status.
         * @param WP_Post $post          Post object.
         */
        $valid_statuses = apply_filters('wp_foodmanager_count_cache_supported_statuses', array('pending'), $new_status, $old_status, $post);
        $rlike          = array();

        // New status transient option name
        if (in_array($new_status, $valid_statuses)) {
            $rlike[] = "^_transient_fm_{$new_status}_{$post->post_type}_count_user_";
        }

        // Old status transient option name
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
     * Clear expired transients
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
     * When any post has a term set
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
     * When any term is edited
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
     * Flush the cache
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
     * Category order update.
     *
     * @access public
     * @return void|bool
     * @since 1.0.0
     */
    public function menuUpdateOrder() {
        global $wpdb;

        $data = (!empty($_POST['post']) ? $_POST['post'] : []);
        if (!is_array($data)) {
            return false;
        }

        $id_arr = [];
        foreach ($data as $position => $id) {
            $id_arr[] = $id;
        }

        $menu_order_arr = [];
        foreach ($id_arr as $key => $id) {
            $results = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($id));
            foreach ($results as $result) {
                $menu_order_arr[] = $result->menu_order;
            }
        }

        sort($menu_order_arr);
        array_unshift($data, "");
        unset($data[0]);

        foreach ($data as $key => $id) {
            $wpdb->update($wpdb->posts, ['menu_order' => $key], ['ID' => intval($id)]);
        }

        wp_send_json_success();
    }

    /**
     * Upload file via ajax
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
     * Get listings via ajax
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

        // Categories
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

        // Food types
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

        // Food Menu
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
            $message = sprintf(_n('Search completed. Found %d matching record.', 'Search completed. Found %d matching records.', $food_cnt, 'wp-food-manager'), $food_cnt);
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

        // Generate RSS link
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
     * Check for WC Ajax request and fire action
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
            // Not home - this is an ajax endpoint
            $wp_query->is_home = false;
            do_action('food_manager_ajax_' . sanitize_text_field($action));
            die();
        }
    }

    /**
     * Add our endpoint for frontend ajax requests
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
     * Display notice in formatted HTML.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function display_notice() {
        $notice = get_transient('WPFM_Food_Notice');
        if (!empty($notice)) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html($notice) . '</p>';
            echo '</div>';
        }
    }

    /**
     * Display the content according to the column.
     *
     * @access public
     * @param string $column
     * @param int $post_id
     * @return void
     * @since 1.0.0
     */
    public function custom_food_content_column($column, $post_id) {
        global $post;
        $thispost = get_post($post_id);

        switch ($column) {
            case 'food_title':
                echo '<div class="food_title">';
                echo '<a href="' . esc_url(admin_url('post.php?post=' . $post->ID . '&action=edit')) . '" class="wpfm-tooltip food_title" wpfm-data-tip="' . sprintf(wp_kses('ID: %d', 'wp-food-manager'), $post->ID) . '">' . esc_html($post->post_title) . '</a>';
                echo '</div>';
                echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__('Show more details', 'wp-food-manager') . '</span></button>';
                break;

            case 'food_banner':
                echo '<div class="food_banner">';
                display_food_banner();
                echo '</div>';
                display_food_veg_nonveg_icon_tag();
                break;

            case 'fm-price':
                display_food_price_tag();
                break;

            case 'fm_categories':
                echo display_food_category();
                break;

            case 'fm_stock_status':
                echo display_stock_status();
                break;

            case 'food_menu_order':
                echo esc_html($thispost->menu_order);
                break;

            case 'food_status':
                echo ucfirst($thispost->post_status);
                break;

            case 'food_actions':
                echo '<div class="actions">';
                $admin_actions = apply_filters('post_row_actions', array(), $post);

                if (in_array($post->post_status, array('pending', 'pending_payment')) && current_user_can('publish_post', $post->ID)) {
                    $admin_actions['publish'] = array(
                        'action' => 'saved',
                        'name'   => __('Publish', 'wp-food-manager'),
                        'url'    => wp_nonce_url(add_query_arg('approve_food', $post->ID), 'approve_food'),
                    );
                }

                if ($post->post_status !== 'trash') {
                    if (current_user_can('read_post', $post->ID)) {
                        $admin_actions['view'] = array(
                            'action' => 'welcome-view-site',
                            'name'   => __('View', 'wp-food-manager'),
                            'url'    => get_permalink($post->ID),
                        );
                    }

                    if (current_user_can('edit_post', $post->ID)) {
                        $admin_actions['edit'] = array(
                            'action' => 'edit',
                            'name'   => __('Edit', 'wp-food-manager'),
                            'url'    => get_edit_post_link($post->ID),
                        );
                    }

                    if (current_user_can('delete_post', $post->ID)) {
                        $admin_actions['delete'] = array(
                            'action' => 'trash',
                            'name'   => __('Delete', 'wp-food-manager'),
                            'url'    => get_delete_post_link($post->ID),
                        );
                    }
                }

                $admin_actions = apply_filters('food_manager_admin_actions', $admin_actions, $post);
                foreach ($admin_actions as $action) {
                    if (is_array($action)) {
                        printf('<a class="button button-icon wpfm-tooltip" href="%2$s" wpfm-data-tip="%3$s"><span class="dashicons dashicons-%1$s"></span></a>', esc_attr($action['action']), esc_url($action['url']), esc_attr($action['name']), esc_html($action['name']));
                    } else {
                        echo esc_attr(str_replace('class="', 'class="button ', $action));
                    }
                }

                echo '</div>';
                break;
        }
    }

    /**
     * content for Copy Shortcode
     *
     * @access public
     * @param string $column
     * @param int $post_id
     * @return void
     * @since 1.0.1
     */
    public function shortcode_copy_content_column($column, $post_id) {
        echo '<code>';
        printf(esc_html__('[food_menu id=%d]', 'wp-food-manager'), esc_attr($post_id));
        echo '</code>';
    }

    /**
     * Save the food menu meta data.
     *
     * @access public
     * @param int $post_id 
     * @param object $post 
     * @return void
     * @since 1.0.0
     */
    public function food_manager_save_food_manager_menu_data($post_id, $post) {
        if (isset($_POST['radio_icons']) && !empty($_POST['radio_icons'])) {
            $wpfm_radio_icon = esc_attr($_POST['radio_icons']);

            if (isset($wpfm_radio_icon)) {
                if (!add_post_meta($post_id, 'wpfm_radio_icons', $wpfm_radio_icon, true)) {
                    update_post_meta($post_id, 'wpfm_radio_icons', $wpfm_radio_icon);
                }
            }
        }

        if (isset($_POST['wpfm_food_listing_ids'])) {
            $item_ids = array_map('esc_attr', $_POST['wpfm_food_listing_ids']);
            update_post_meta($post_id, '_food_item_ids', $item_ids);
        } else {
            update_post_meta($post_id, '_food_item_ids', '');
        }
    }

    /**
     * Get the food layout by the given category id.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function get_food_listings_by_category_id() {
        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            $args = [
                'post_type' => 'food_manager',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'post__not_in' => isset($_POST['exclude']) && !empty($_POST['exclude']) ? array_map('esc_attr', $_POST['exclude']) : array(),
                'tax_query' => [
                    [
                        'taxonomy' => esc_attr($_POST['taxonomy']),
                        'terms' => esc_attr($_POST['category_id']),
                    ],
                ],
                // Rest of your arguments
            ];

            $food_listing = new WP_Query($args);
            $html = [];

            if ($food_listing->have_posts()) :
                while ($food_listing->have_posts()) : $food_listing->the_post();
                    $id = get_the_ID();
                    $html[] =
                        '<li class="menu-item-handle" data-food-id="' . esc_attr($id) . '">
                        <div class="wpfm-admin-left-col">
                            <span class="dashicons dashicons-menu"></span>
                            <span class="item-title">' . esc_html(get_the_title($id)) . '</span>
                        </div>
                        <div class="wpfm-admin-right-col">
                            <a href="javascript:void(0);" class="wpfm-food-item-remove">
                                <span class="dashicons dashicons-dismiss"></span>
                            </a>
                        </div>
                        <input type="hidden" name="wpfm_food_listing_ids[]" value="' . esc_attr($id) . '" />
                    </li>';
                endwhile;
            endif;
            wp_reset_postdata();
            wp_send_json(array('html' => $html, 'success' => true));
        } else {

            $args = [
                'post_type' => 'food_manager',
                'posts_per_page' => -1,
                'post__not_in' => isset($_POST['exclude']) && !empty($_POST['exclude']) ? array_map('esc_attr', $_POST['exclude']) : array(),
            ];

            $food_listing = new WP_Query($args);
            $html = [];

            if ($food_listing->have_posts()) :
                while ($food_listing->have_posts()) : $food_listing->the_post();
                    $id = get_the_ID();

                    $html[] =
                        '<li class="menu-item-handle" data-food-id="' . esc_attr($id) . '">
                        <div class="wpfm-admin-left-col">
                            <span class="dashicons dashicons-menu"></span>
                            <span class="item-title">' . esc_html(get_the_title($id)) . '</span>
                        </div>
                        <div class="wpfm-admin-right-col">
                            <a href="javascript:void(0);" class="wpfm-food-item-remove">
                                <span class="dashicons dashicons-dismiss"></span>
                            </a>
                        </div>
                        <input type="hidden" name="wpfm_food_listing_ids[]" value="' . esc_attr($id) . '" />
                    </li>';
                endwhile;
            endif;
            wp_reset_postdata();

            wp_send_json(array('html' => $html, 'success' => true));
        }
        wp_die();
    }

    /**
     * Save the food data from backend and frontend both side is handle by this function.
     *
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @param array $form_fields
     * @return void
     * @since 1.0.0
     */
    public function food_manager_save_food_manager_data($post_id, $post, $form_fields) {
        global $wpdb;
        $thumbnail_image = array();
        // Save Food Form fields values
        if (isset($form_fields['food'])) {
            foreach ($form_fields['food'] as $key => $field) {
                $type = !empty($field['type']) ? $field['type'] : '';

                // Food Banner
                if ('food_banner' === $key) {
                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        $thumbnail_image = is_array($_POST[$key]) ? array_values(array_filter($_POST[$key])) : $_POST[$key];

                        // Update Food Banner Meta Data
                        update_post_meta($post_id, '_' . esc_attr($key), $thumbnail_image);
                        if (is_array($_POST[$key])) {
                            $_POST[$key] = array_values(array_filter($_POST[$key]));
                        }
                    }

                    // Create Attachments ( If not exist ).
                    if (!is_admin()) {
                        $maybe_attach = array_filter((array)$thumbnail_image);

                        // Handle attachments
                        if (sizeof($maybe_attach) && apply_filters('wpfm_attach_uploaded_files', true)) {

                            // Get attachments
                            $attachments     = get_posts('post_parent=' . $post_id . '&post_type=attachment&fields=ids&numberposts=-1');
                            $attachment_urls = array();

                            // Loop attachments already attached to the food
                            foreach ($attachments as $attachment_key => $attachment) {
                                $attachment_urls[] = wp_get_attachment_url($attachment);
                            }

                            foreach ($maybe_attach as $key => $attachment_url) {
                                if (!in_array($attachment_url, $attachment_urls) && !is_numeric($attachment_url)) {
                                    $WPFM_Add_Food_Form = WPFM_Add_Food_Form::instance();
                                    $attachment_id = $WPFM_Add_Food_Form->create_attachment($attachment_url);

                                    /*
                    * set first image of banner as a thumbnail
                    */
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
                                    'meta_key'       => '_wp_attached_file',
                                    'meta_value'     => $wp_attached_file,
                                    'post_type'      => 'attachment',
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

                // Other form field's value
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
                            // Convert date and time value into DB formatted format and save eg. 1970-01-01
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

                // Set Ingredients
                if ($key = 'food_ingredients') {
                    $taxonomy = 'food_manager_ingredient';
                    $multiArrayIng = array();

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

                            $multiArrayIng[$id] = $item;
                            $ingredient_ids[] = $id;
                            if (trim($ingredient['unit_id'])) {
                                $unit_ids[] = (int)$ingredient['unit_id'];
                            }
                        }
                        update_post_meta($post_id, '_' . esc_attr($key), $multiArrayIng);
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

                // Set Nutrition
                if ($key = 'food_nutritions') {
                    $taxonomy = 'food_manager_nutrition';
                    $multiArrayNutri = array();

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

                            $multiArrayNutri[$id] = $item;
                            $nutrition_ids[] = $id;
                            if (trim($nutrition['unit_id'])) {
                                $unit_ids[] = (int)$nutrition['unit_id'];
                            }
                        }
                        update_post_meta($post_id, '_' . esc_attr($key), $multiArrayNutri);
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

                        if (is_array($terms)) {
                            $terms = array_map(function ($value) {
                                return (int)$value;
                            }, $terms);
                            wp_set_object_terms($post_id, $terms, $field['taxonomy'], false);
                        } else {
                            if (!empty($terms)) {
                                wp_set_object_terms($post_id, array($terms), $field['taxonomy'], false);
                            }
                        }
                    }
                }

                // Food Tags
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

                                // Remove from array
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
                            // Toppings Array
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
                            $removed_toppings_ids[] = (int)$toppings->term_id;
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

        // Update repeated_options meta for the count of toppings
        $repeated_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
        if (is_array($repeated_options)) {
            $repeated_options = array_map('esc_attr', $repeated_options);
        }
        update_post_meta($post_id, '_food_repeated_options', $repeated_options);

        // Set orders according to previous inserted post.
        $order_menu = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($post_id));
        if ($order_menu && $order_menu[0]->menu_order == 0) {
            $last_inserted_post = get_posts(array(
                'post_type' => 'food_manager',
                'posts_per_page' => 2,
                'offset' => 0,
                'orderby' => 'ID',
                'order' => 'DESC',
                'post_status' => 'any',
            ));

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
            'ID'          => intval($post_id),
        );
        wp_update_post($food_data);
        add_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);
    }

    /**
     * Edit bulk actions.
     * 
     * @return void
     * @since 1.0.0
     */
    public function add_bulk_actions() {
        global $post_type, $wp_post_types;
        if ($post_type == 'food_manager') { ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery('<option>').val('approve_food').text('<?php printf(__('Approve %s', 'wp-food-manager'), esc_attr($wp_post_types['food_manager']->labels->name)); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('approve_food').text('<?php printf(__('Approve %s', 'wp-food-manager'), esc_attr($wp_post_types['food_manager']->labels->name)); ?>').appendTo("select[name='action2']");
                });
            </script>
        <?php
        }
    }

    /**
     * Do custom bulk actions
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function do_bulk_actions() {
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();

        switch ($action) {
            case 'approve_food':
                check_admin_referer('bulk-posts');
                $post_ids = array_map('absint', array_filter((array) $_GET['post']));
                $published_foods = array();
                if (!empty($post_ids)) {
                    foreach ($post_ids as $post_id) {
                        $food_data = array(
                            'ID'          => $post_id,
                            'post_status' => 'publish',
                        );
                        if (in_array(get_post_status($post_id), array('pending', 'pending_payment')) && current_user_can('publish_post', $post_id) && wp_update_post($food_data)) {
                            $published_foods[] = $post_id;
                        }
                    }
                }
                wp_redirect(add_query_arg('published_foods', $published_foods, remove_query_arg(array('published_foods'), admin_url('edit.php?post_type=food_manager'))));
                exit;
                break;
        }

        return;
    }

    /**
     * Approve a single food
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function approve_food() {
        if (!empty($_GET['approve_food']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_food') && current_user_can('publish_post', $_GET['approve_food'])) {

            $post_id = absint($_GET['approve_food']);
            $food_data = array(
                'ID'          => $post_id,
                'post_status' => 'publish',
            );

            wp_update_post($food_data);
            set_transient('WPFM_Food_Notice', 'Food Item Approved!', 1 * MINUTE_IN_SECONDS);
            wp_redirect(remove_query_arg('approve_food', add_query_arg('published_foods', $post_id, admin_url('edit.php?post_type=food_manager'))));

            exit;
        }
    }

    /**
     * Save post when food updated or submitted by the backend.
     *
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @return void
     * @since 1.0.0
     */
    public function save_post($post_id, $post) {
        global $wpdb;

        if (empty($post_id) || empty($post) || empty($_POST)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (is_int(wp_is_post_revision($post))) return;
        if (is_int(wp_is_post_autosave($post))) return;
        if (empty($_POST['food_manager_nonce']) || !wp_verify_nonce($_POST['food_manager_nonce'], 'save_meta_data')) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type == 'food_manager') {
            $writepanels = WPFM_Writepanels::instance();
            do_action('wpfm_save_food_data', $post_id, $post, $writepanels->food_manager_data_fields());

            // Set Order Menu
            $order_menu = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($post_id));
            if ($order_menu && $order_menu[0]->menu_order == 0) {
                $last_inserted_post = get_posts(array(
                    'post_type' => $post->post_type,
                    'posts_per_page' => 2,
                    'offset' => 0,
                    'orderby' => 'ID',
                    'order' => 'DESC',
                    'post_status' => 'any',
                ));

                if (count($last_inserted_post) > 1) {
                    $last_menu_order = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($last_inserted_post[1]->ID));
                    $next_menu_order = $last_menu_order[0]->menu_order + 1;
                    $wpdb->update($wpdb->posts, ['menu_order' => $next_menu_order], ['ID' => intval($post_id)]);
                } else {
                    $wpdb->update($wpdb->posts, ['menu_order' => 1], ['ID' => intval($post_id)]);
                }
            }
        }
        if ($post->post_type == 'food_manager_menu') {
            do_action('food_manager_save_food_manager_menu', $post_id, $post);
        }
    }

    /**
     * Adding the meta boxes to the backend.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function add_meta_boxes() {
        global $wp_post_types;

        $screen = get_current_screen();
        $taxonomy_slug = 'food_manager_type';
        $taxonomy = get_taxonomy($taxonomy_slug);

        add_meta_box('food_manager_data', sprintf(__('%s Data', 'wp-food-manager'), $wp_post_types['food_manager']->labels->singular_name), array(WPFM_Writepanels::instance(), 'food_manager_data'), 'food_manager', 'normal', 'high');
        add_meta_box('food_manager_menu_data', __('Menu Icon', 'wp-food-manager'), array(WPFM_Writepanels::instance(), 'food_manager_menu_data'), 'food_manager_menu', 'normal', 'high');
        add_meta_box('food_manager_menu_data_icons', __('Select Food ', 'wp-food-manager'), array(WPFM_Writepanels::instance(), 'food_manager_menu_data_icons'), 'food_manager_menu', 'normal', 'high');

        // Replace the food_manager_type taxonomy metabox for changing checkbox to radio button in backend.
        remove_meta_box('food_manager_typediv', 'food_manager', 'side');
        add_meta_box('radio-food_manager_typediv', (isset($taxonomy->labels->name) ? esc_html($taxonomy->labels->name) : ''), array($this, 'replace_food_manager_type_metabox'), 'food_manager', 'side', 'core', array('taxonomy' => $taxonomy_slug));
        if ('add' != $screen->action) {

            // Show food menu Shortcode on edit menu page - admin.
            add_meta_box('wpfm_menu_shortcode', 'Shortcode', array($this, 'food_menu_shortcode'), 'food_manager_menu', 'side', 'low');
        }
    }

    /**
     * Add image field in 'food_manager_type' taxonomy page
     * 
     * @access public
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    function add_custom_taxonomy_image_for_food_type($taxonomy) { ?>
        <div class="form-field term-group">
            <label for="image_id" class="wpfm-food-type-tax-image"><?php _e('Image/Icon', 'taxt-domain'); ?></label>
            <input type="hidden" id="image_id" name="image_id" class="custom_media_url" value="">
            <div id="image_wrapper"></div>
            <p>
                <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php _e('Add Image', 'taxt-domain'); ?>">
                <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php _e('Remove Image', 'taxt-domain'); ?>">
            </p>
        </div>
    <?php
    }

    /**
     * Save the 'food_manager_type' taxonomy image field
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @return void
     * @since 1.0.0
     */
    function save_custom_taxonomy_image_for_food_type($term_id, $tt_id) {
        if (isset($_POST['image_id']) && '' !== $_POST['image_id']) {
            $image = $_POST['image_id'];
            add_term_meta($term_id, 'image_id', $image, true);
        }
    }

    /**
     * Add the image field in edit form page
     * 
     * @access public
     * @param mixed $term
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    function update_custom_taxonomy_image_for_food_type($term, $taxonomy) { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="image_id"><?php _e('Image', 'taxt-domain'); ?></label>
            </th>
            <td>
                <?php $image_id = get_term_meta($term->term_id, 'image_id', true); ?>
                <input type="hidden" id="image_id" name="image_id" value="<?php echo esc_attr($image_id); ?>">
                <div id="image_wrapper">
                    <?php if ($image_id) { ?>
                        <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                    <?php } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php _e('Add Image', 'taxt-domain'); ?>">
                    <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php _e('Remove Image', 'taxt-domain'); ?>">
                </p>
                </div>
            </td>
        </tr>
    <?php
    }

    /**
     * Update the 'food_manager_type' taxonomy image field
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @since 1.0.0
     */
    function updated_custom_taxonomy_image_for_food_type($term_id, $tt_id) {
        if (isset($_POST['image_id']) && '' !== $_POST['image_id']) {
            $image = $_POST['image_id'];
            update_term_meta($term_id, 'image_id', $image);
        } else {
            update_term_meta($term_id, 'image_id', '');
        }
    }

    /**
     * Enqueue the wp_media library
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    function custom_taxonomy_load_media_for_food_type() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_type') {
            return;
        }
        wp_enqueue_media();
    }

    /**
     * Custom script
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    function add_custom_taxonomy_script_for_food_type() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_type') {
            return;
        }
    ?>

        <script>
            jQuery(document).ready(function($) {
                function taxonomy_media_upload(button_class) {
                    var custom_media = true,
                        original_attachment = wp.media.editor.send.attachment;

                    $('body').on('click', button_class, function(e) {
                        var button_id = '#' + $(this).attr('id');
                        var send_attachment = wp.media.editor.send.attachment;
                        var button = $(button_id);
                        custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            if (custom_media) {
                                $('#image_id').val(attachment.id);
                                $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#image_wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                            } else {
                                return original_attachment.apply(button_id, [props, attachment]);
                            }
                        }
                        wp.media.editor.open(button);
                        return false;
                    });
                }

                taxonomy_media_upload('.taxonomy_media_button.button');
                $('body').on('click', '.taxonomy_media_remove', function() {
                    $('#image_id').val('');
                    $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;display:none;" />');
                });

                $(document).ajaxComplete(function(event, xhr, settings) {
                    var queryStringArr = settings.data.split('&');
                    if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                        var xml = xhr.responseXML;
                        $response = $(xml).find('term_id').text();
                        if ($response != "") {
                            $('#image_wrapper').html('');
                        }
                    }
                });
            });
        </script>
    <?php
    }

    /**
     * Display new columns values
     * 
     * @access public
     * @param string $columns
     * @param string $column
     * @param int $id
     * @return void
     * @since 1.0.0
     */
    function display_custom_taxonomy_image_column_value_for_food_type($columns, $column, $id) {
        if ('category_image' == $column) {
            $image_id = esc_html(get_term_meta($id, 'image_id', true));
            $columns = wp_get_attachment_image($image_id, array('50', '50'));
        }
        return $columns;
    }

    /**
     * Add image field in 'food_manager_category' taxonomy page
     * 
     * @access public
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    function add_custom_taxonomy_image_for_food_category($taxonomy) {
    ?>
        <div class="form-field term-group">
            <label for="food_cat_image_id" class="wpfm-food-category-tax-image"><?php esc_html_e('Image/Icon', 'taxt-domain'); ?></label>
            <input type="hidden" id="food_cat_image_id" name="food_cat_image_id" class="custom_media_url" value="">
            <div id="image_wrapper"></div>
            <p>
                <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php esc_attr_e('Add Image', 'taxt-domain'); ?>">
                <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php esc_attr_e('Remove Image', 'taxt-domain'); ?>">
            </p>
        </div>
    <?php
    }

    /**
     * Save the 'food_manager_category' taxonomy image field
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @return void
     * @since 1.0.0
     */
    function save_custom_taxonomy_image_for_food_category($term_id, $tt_id) {
        if (isset($_POST['food_cat_image_id']) && '' !== $_POST['food_cat_image_id']) {
            $image = $_POST['food_cat_image_id'];
            add_term_meta($term_id, 'food_cat_image_id', $image, true);
        }
    }

    /**
     * Add the image field in edit form page
     * 
     * @access public
     * @param object $term
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    function update_custom_taxonomy_image_for_food_category($term, $taxonomy) { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="food_cat_image_id"><?php esc_html_e('Image', 'taxt-domain'); ?></label>
            </th>
            <td>
                <?php $food_cat_image_id = get_term_meta($term->term_id, 'food_cat_image_id', true); ?>
                <input type="hidden" id="food_cat_image_id" name="food_cat_image_id" value="<?php echo esc_attr($food_cat_image_id); ?>">
                <div id="image_wrapper">
                    <?php if ($food_cat_image_id) { ?>
                        <?php echo wp_get_attachment_image($food_cat_image_id, 'thumbnail'); ?>
                    <?php } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php esc_attr_e('Add Image', 'taxt-domain'); ?>">
                    <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php esc_attr_e('Remove Image', 'taxt-domain'); ?>">
                </p>
                </div>
            </td>
        </tr>
    <?php
    }

    /**
     * Update the 'food_manager_category' taxonomy image field
     *
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @return void
     * @since 1.0.0
     */
    function updated_custom_taxonomy_image_for_food_category($term_id, $tt_id) {
        if (isset($_POST['food_cat_image_id']) && '' !== $_POST['food_cat_image_id']) {
            $image = sanitize_text_field($_POST['food_cat_image_id']);
            update_term_meta($term_id, 'food_cat_image_id', $image);
        } else {
            update_term_meta($term_id, 'food_cat_image_id', '');
        }
    }

    /**
     * Enqueue the wp_media library
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    function custom_taxonomy_load_media_for_food_category() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_category') {
            return;
        }
        wp_enqueue_media();
    }

    /**
     * Custom script
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    function add_custom_taxonomy_script_for_food_category() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_category') {
            return;
        }
    ?>

        <script>
            jQuery(document).ready(function($) {
                function taxonomy_media_upload(button_class) {
                    var custom_media = true,
                        original_attachment = wp.media.editor.send.attachment;

                    $('body').on('click', button_class, function(e) {
                        var button_id = '#' + $(this).attr('id');
                        var send_attachment = wp.media.editor.send.attachment;
                        var button = $(button_id);
                        custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            if (custom_media) {
                                $('#food_cat_image_id').val(attachment.id);
                                $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#image_wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                            } else {
                                return original_attachment.apply(button_id, [props, attachment]);
                            }
                        }
                        wp.media.editor.open(button);
                        return false;
                    });
                }

                taxonomy_media_upload('.taxonomy_media_button.button');
                $('body').on('click', '.taxonomy_media_remove', function() {
                    $('#food_cat_image_id').val('');
                    $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;display:none;" />');
                });

                $(document).ajaxComplete(function(event, xhr, settings) {
                    var queryStringArr = settings.data.split('&');
                    if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                        var xml = xhr.responseXML;
                        $response = $(xml).find('term_id').text();
                        if ($response != "") {
                            $('#image_wrapper').html('');
                        }
                    }
                });
            });
        </script>
    <?php
    }

    /**
     * Display new columns values
     *
     * @access public
     * @param string $columns
     * @param string $column
     * @param int $id
     * @return $columns
     * @since 1.0.0
     */
    function display_custom_taxonomy_image_column_value_for_food_category($columns, $column, $id) {
        if ('category_image' == $column) {
            $food_cat_image_id = esc_html(get_term_meta($id, 'food_cat_image_id', true));
            $columns = wp_get_attachment_image($food_cat_image_id, array('50', '50'));
        }
        return $columns;
    }

    /**
     * Localise the plugin text domain ('wp-food-manager').
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        $domain = 'wp-food-manager';
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain($domain, WP_LANG_DIR . "/wp-food-manager/" . $domain . "-" . $locale . ".mo");
        load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Load function and template files.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function include_template_functions() {
        include(WPFM_PLUGIN_DIR . '/wp-food-manager-functions.php');
        include(WPFM_PLUGIN_DIR . '/wp-food-manager-template.php');
    }

    /**
     * Register and enqueue scripts and css
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function frontend_scripts() {
        $ajax_url = esc_url(WPFM_Ajax::get_endpoint());
        $ajax_filter_deps = array('jquery');
        $chosen_shortcodes = array('add_food', 'food_dashboard', 'foods', 'food_categories', 'food_type');
        $chosen_used_on_page = has_wpfm_shortcode(null, $chosen_shortcodes);

        // jQuery Chosen - vendor
        if (apply_filters('food_manager_chosen_enabled', $chosen_used_on_page)) {
            wp_register_script('chosen', esc_url(WPFM_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js'), array('jquery'), '1.1.0', true);
            wp_register_script('wp-food-manager-term-multiselect', esc_url(WPFM_PLUGIN_URL . '/assets/js/term-multiselect.min.js'), array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_register_script('wp-food-manager-term-select-multi-appearance', esc_url(WPFM_PLUGIN_URL . '/assets/js/term-select-multi-appearance.min.js'), array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_register_script('wp-food-manager-multiselect', esc_url(WPFM_PLUGIN_URL . '/assets/js/multiselect.min.js'), array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_enqueue_style('chosen', esc_url(WPFM_PLUGIN_URL . '/assets/css/chosen.min.css'));
            $ajax_filter_deps[] = 'chosen';
        }

        // File upload - vendor
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

        // Frontend Css
        wp_enqueue_style('wpfm-frontend', esc_url(WPFM_PLUGIN_URL . '/assets/css/frontend.min.css'));

        // Frontend js
        wp_register_script('wp-food-manager-frontend', esc_url(WPFM_PLUGIN_URL . '/assets/js/frontend.min.js'), array('jquery'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-frontend');

        // Common js
        wp_register_script('wp-food-manager-common', esc_url(WPFM_PLUGIN_URL . '/assets/js/common.min.js'), array('jquery'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-common');

        // Food submission forms and validation js
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

        // Ajax filters js
        wp_register_script('wpfm-ajax-filters', esc_url(WPFM_PLUGIN_URL . '/assets/js/food-ajax-filters.min.js'), $ajax_filter_deps, WPFM_VERSION, true);
        wp_localize_script('wpfm-ajax-filters', 'wpfm_ajax_filters', array(
            'ajax_url' => $ajax_url,
            'is_rtl' => is_rtl() ? 1 : 0,
            'lang' => apply_filters('wpfm_lang', null)
        ));

        // Dashboard
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
     * Add the menu in admin (backend).
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_menu() {
        $this->settings_page = WPFM_Settings::instance();
        if (get_option('food_manager_enable_field_editor', true)) {
            add_submenu_page('edit.php?post_type=food_manager', esc_html__('Field Editor', 'wp-food-manager'), esc_html__('Field Editor', 'wp-food-manager'), 'manage_options', 'food-manager-form-editor', array(WPFM_Field_Editor::instance(), 'output'));
        }
        add_submenu_page('edit.php?post_type=food_manager', esc_html__('Settings', 'wp-food-manager'), esc_html__('Settings', 'wp-food-manager'), 'manage_options', 'food-manager-settings', array($this->settings_page, 'output'));
        add_dashboard_page(esc_html__('Setup', 'wp-food-manager'), esc_html__('Setup', 'wp-food-manager'), 'manage_options', 'food_manager_setup', array(WPFM_Setup::instance(), 'output'));
    }

    /**
     * Enqueue the scripts in the admin.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_enqueue_scripts() {
        global $wp_scripts;
        $screen = get_current_screen();
        $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

        wp_enqueue_style('wpfm-backend-css', esc_url(WPFM_PLUGIN_URL) . '/assets/css/backend.min.css');
        wp_enqueue_style('jquery-ui-style', esc_url(WPFM_PLUGIN_URL) . '/assets/css/jquery-ui/jquery-ui.min.css', array(), $jquery_version);

        $units = get_terms([
            'taxonomy'   => 'food_manager_unit',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        $unitList = [];
        if (!empty($units)) {
            foreach ($units as $unit) {
                $unitList[$unit->term_id] = $unit->name;
            }
        }

        wp_register_script('wpfm-admin', esc_url(WPFM_PLUGIN_URL) . '/assets/js/admin.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-admin',
            'wpfm_admin',
            array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'security' => wp_create_nonce('wpfm-admin-security'),
                'start_of_week'                      => get_option('start_of_week'),
                'i18n_datepicker_format'             => WPFM_Date_Time::get_datepicker_format(),
            )
        );

        wp_localize_script(
            'wpfm-admin',
            'wpfm_var',
            [
                'units'   => $unitList,
            ]
        );

        wp_enqueue_script('wpfm-admin');
        wp_register_script('wp-food-manager-admin-settings', esc_url(WPFM_PLUGIN_URL) . '/assets/js/admin-settings.min.js', array('jquery'), WPFM_VERSION, true);
        if (is_admin() && !isset($_GET['page']) == 'wc-settings') {
            wp_enqueue_script('wp-food-manager-admin-settings');
        }

        wp_register_script('chosen', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
        wp_enqueue_script('chosen');
        wp_enqueue_style('chosen', esc_url(WPFM_PLUGIN_URL) . '/assets/css/chosen.min.css');
        wp_enqueue_style('wpfm-font-style', esc_url(WPFM_PLUGIN_URL) . '/assets/fonts/style.min.css');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_register_script('wpfm-accounting', esc_url(WPFM_PLUGIN_URL) . '/assets/js/accounting/accounting.min.js', array('jquery'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-accounting',
            'wpfm_accounting_params',
            array(
                'wpfm_sale_less_than_regular_error' => esc_html__('Please enter in a value less than the regular price.', 'woocommerce'),
            )
        );
        wp_enqueue_script('wpfm-accounting');
        wp_enqueue_style('dashicons');

        // File upload - vendor
        if (apply_filters('wpfm_ajax_file_upload_enabled', true)) {
            wp_register_script('jquery-iframe-transport', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-fileupload/jquery.iframe-transport.min.js', array('jquery'), '1.8.3', true);
            wp_register_script('jquery-fileupload', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-fileupload/jquery.fileupload.min.js', array('jquery', 'jquery-iframe-transport', 'jquery-ui-widget'), '5.42.3', true);
            wp_register_script('wpfm-ajax-file-upload', esc_url(WPFM_PLUGIN_URL) . '/assets/js/ajax-file-upload.min.js', array('jquery', 'jquery-fileupload'), WPFM_VERSION, true);

            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'jpg'));
            $js_field_html_img = ob_get_clean();

            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'zip'));
            $js_field_html = ob_get_clean();
            wp_localize_script('wpfm-ajax-file-upload', 'wpfm_ajax_file_upload', array(
                'ajax_url'               => esc_url(admin_url('admin-ajax.php')),
                'js_field_html_img'      => esc_js(str_replace("\n", "", $js_field_html_img)),
                'js_field_html'          => esc_js(str_replace("\n", "", $js_field_html)),
                'i18n_invalid_file_type' => esc_html__('The file type you have mentioned is invalid.', 'wp-food-manager')
            ));
        }
        wp_enqueue_editor();
        wp_register_script('chosen', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
        wp_register_script('wp-food-manager-form-field-editor', esc_url(WPFM_PLUGIN_URL) . '/assets/js/field-editor.min.js', array('jquery', 'jquery-ui-sortable', 'chosen'), WPFM_VERSION, true);
        wp_localize_script(
            'wp-food-manager-form-field-editor',
            'wpfm_form_editor',
            array(
                'cofirm_delete_i18n'                    => esc_html__('Are you sure you want to delete this row?', 'wp-food-manager'),
                'cofirm_reset_i18n'                     => esc_html__('Are you sure you want to reset your changes? This cannot be undone.', 'wp-food-manager'),
                'ajax_url'                              => esc_url(admin_url('admin-ajax.php')),
                'wpfm_form_editor_security' => wp_create_nonce('_nonce_wpfm_form_editor_security'),
            )
        );

        if (isset($_GET['page']) && 'food_manager_setup' === $_GET['page']) {
            wp_enqueue_style('food_manager_setup_css', esc_url(WPFM_PLUGIN_URL) . '/assets/css/setup.min.css', array('dashicons'));
        }

        wp_register_script('wpfm-term-autocomplete', esc_url(WPFM_PLUGIN_URL) . '/assets/js/term-autocomplete.min.js', array('jquery', 'jquery-ui-autocomplete'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-term-autocomplete',
            'wpfm_term_autocomplete',
            array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'security' => wp_create_nonce('wpfm-autocomplete-security'),
            )
        );
    }

    /**
     * Include admin files conditionally.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function conditional_includes() {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }
        switch ($screen->id) {
            case 'options-permalink':
                include esc_url(WPFM_PLUGIN_DIR) . '/admin/wpfm-permalink-settings.php';
                break;
        }
    }

    /**
     * Display the group field html by the given array.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function get_group_field_html() {
        check_ajax_referer('_nonce_wpfm_form_editor_security', 'security');
        $field_types = apply_filters(
            'food_manager_form_group_field_types',
            array(
                'text'        => esc_html__('Text', 'wp-food-manager'),
                'checkbox'    => esc_html__('Checkbox', 'wp-food-manager'),
                'date'        => esc_html__('Date', 'wp-food-manager'),
                'file'        => esc_html__('File', 'wp-food-manager'),
                'hidden'      => esc_html__('Hidden', 'wp-food-manager'),
                'multiselect' => esc_html__('Multiselect', 'wp-food-manager'),
                'number'      => esc_html__('Number', 'wp-food-manager'),
                'password'    => esc_html__('Password', 'wp-food-manager'),
                'radio'       => esc_html__('Radio', 'wp-food-manager'),
                'select'      => esc_html__('Select', 'wp-food-manager'),
                'textarea'    => esc_html__('Textarea', 'wp-food-manager'),
                'options'    => esc_html__('Options', 'wp-food-manager'),
            )
        );
        ob_start();
        $child_index     = -1;
        $child_field_key = '';
        $child_field     = array(
            'type'        => 'text',
            'label'       => '',
            'placeholder' => '',
        );
        include esc_url(WPFM_PLUGIN_DIR) . '/admin/wpfm-field-editor-group-form-field-row.php';
        echo esc_attr(ob_get_clean());
        wp_die();
    }

    /**
     * Register the settings by loading the Class WPFM_Settings.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function register_settings() {
        $wpfm_Settings = WPFM_Settings::instance();
        $wpfm_Settings->init_settings();

        foreach ($wpfm_Settings->settings as $section) {
            foreach ($section[1] as $option) {
                if (isset($option['std']))
                    add_option(esc_attr($option['name']), $option['std']);
                register_setting($this->settings_group, esc_attr($option['name']));
            }
        }
    }

    /**
     * Sends user to the setup page on first activation
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function redirect() {
        global $pagenow;

        if (isset($_GET['page']) && $_GET['page'] === 'food_manager_setup') {
            if (get_option('food_manager_installation', false)) {
                wp_redirect(admin_url('index.php'));
                exit;
            }
        }

        // Bail if no activation redirect transient is set
        if (!get_transient('_food_manager_activation_redirect')) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        // Delete the redirect transient
        delete_transient('_food_manager_activation_redirect');

        // Bail if activating from network, or bulk, or within an iFrame
        if (is_network_admin() || isset($_GET['activate-multi']) || defined('IFRAME_REQUEST')) {
            return;
        }
        if ((isset($_GET['action']) && 'upgrade-plugin' == $_GET['action']) && (isset($_GET['plugin']) && strstr($_GET['plugin'], 'wp-food-manager.php'))) {
            return;
        }
        wp_redirect(admin_url('index.php?page=food_manager_setup'));
        exit;
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
     * Callback to set up the metabox
     * Mimicks the traditional hierarchical term metabox, but modified with our nonces 
     * 
     * @access public
     * @param object $post
     * @param array $box
     * @return void
     * @since 1.0.1
     */
    public function replace_food_manager_type_metabox($post, $box) {
        $defaults = array('taxonomy' => 'category');

        if (!isset($box['args']) || !is_array($box['args'])) {
            $args = array();
        } else {
            $args = $box['args'];
        }

        $r = wp_parse_args($args, $defaults);
        $tax_name = esc_attr($r['taxonomy']);
        $taxonomy = get_taxonomy($r['taxonomy']);
        $checked_terms = isset($post->ID) ? get_the_terms($post->ID, $tax_name) : array();
        $single_term = !empty($checked_terms) && !is_wp_error($checked_terms) ? array_pop($checked_terms) : false;
        $single_term_id = $single_term ? (int) $single_term->term_id : 0; ?>

        <div id="taxonomy-<?php echo esc_attr($tax_name); ?>" class="radio-buttons-for-taxonomies categorydiv">
            <ul id="<?php echo esc_attr($tax_name); ?>-tabs" class="category-tabs">
                <li class="tabs"><a href="#<?php echo esc_attr($tax_name); ?>-all"><?php echo esc_html($taxonomy->labels->all_items); ?></a></li>
                <li class="hide-if-no-js"><a href="#<?php echo esc_attr($tax_name); ?>-pop"><?php echo esc_html($taxonomy->labels->most_used); ?></a></li>
            </ul>
            <div id="<?php echo esc_attr($tax_name); ?>-pop" class="tabs-panel" style="display: none;">
                <ul id="<?php echo esc_attr($tax_name); ?>checklist-pop" class="categorychecklist form-no-clear">
                    <?php
                    $popular_terms = get_terms($tax_name, array('orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false));
                    $popular_ids = array();

                    foreach ($popular_terms as $term) {
                        $popular_ids[] = $term->term_id;
                        $value = is_taxonomy_hierarchical($tax_name) ? $term->term_id : $term->slug;
                        $id = 'popular-' . $tax_name . '-' . $term->term_id;
                        $checked = checked($single_term_id, $term->term_id, false); ?>

                        <li id="<?php echo esc_attr($id); ?>" class="popular-category">
                            <label class="selectit">
                                <input id="in-<?php echo esc_attr($id); ?>" type="radio" <?php echo $checked; ?> name="tax_input[<?php echo esc_attr($tax_name) ?>][]" value="<?php echo (int) $term->term_id; ?>" <?php disabled(!current_user_can($taxonomy->cap->assign_terms)); ?> />
                                <?php
                                /** This filter is documented in wp-includes/category-template.php */
                                echo esc_html(apply_filters('the_category', $term->name, '', ''));
                                ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div id="<?php echo esc_attr($tax_name); ?>-all" class="tabs-panel">
                <ul id="<?php echo esc_attr($tax_name); ?>checklist" data-wp-lists="list:<?php echo esc_attr($tax_name); ?>" class="categorychecklist form-no-clear">
                    <?php wp_terms_checklist($post->ID, array('taxonomy' => $tax_name, 'popular_cats' => $popular_ids, 'selected_cats' => array($single_term_id))); ?>
                </ul>
            </div>
            <?php if (current_user_can($taxonomy->cap->edit_terms)) : ?>
                <div id="<?php echo esc_attr($tax_name); ?>-adder" class="wp-hidden-children">
                    <a id="<?php echo esc_attr($tax_name); ?>-add-toggle" href="#<?php echo esc_attr($tax_name); ?>-add" class="hide-if-no-js taxonomy-add-new">

                        <?php
                        /* translators: %s: add new taxonomy label */
                        printf(__('+ %s'), $taxonomy->labels->add_new_item);
                        ?>
                    </a>
                    <p id="<?php echo esc_attr($tax_name); ?>-add" class="category-add wp-hidden-child">
                        <label class="screen-reader-text" for="new<?php echo esc_attr($tax_name); ?>"><?php echo esc_html($taxonomy->labels->add_new_item); ?></label>
                        <input type="text" name="new<?php echo esc_attr($tax_name); ?>" id="new<?php echo esc_attr($tax_name); ?>" class="form-required form-input-tip" value="<?php echo esc_attr($taxonomy->labels->new_item_name); ?>" aria-required="true" />
                        <label class="screen-reader-text" for="new<?php echo esc_attr($tax_name); ?>_parent">
                            <?php echo $taxonomy->labels->parent_item_colon; ?>
                        </label>

                        <?php
                        // Only add parent option for hierarchical taxonomies.
                        if (is_taxonomy_hierarchical($tax_name)) {
                            $parent_dropdown_args = array(
                                'taxonomy'         => $tax_name,
                                'hide_empty'       => 0,
                                'name'             => 'new' . $tax_name . '_parent',
                                'orderby'          => 'name',
                                'hierarchical'     => 1,
                                'show_option_none' => '&mdash; ' . $taxonomy->labels->parent_item . ' &mdash;',
                            );
                            $parent_dropdown_args = apply_filters('post_edit_category_parent_dropdown_args', $parent_dropdown_args);
                            wp_dropdown_categories($parent_dropdown_args);
                        }
                        ?>
                        <input type="button" id="<?php echo esc_attr($tax_name); ?>-add-submit" data-wp-lists="add:<?php echo esc_attr($tax_name); ?>checklist:<?php echo esc_attr($tax_name); ?>-add" class="button category-add-submit" value="<?php echo esc_attr($taxonomy->labels->add_new_item); ?>" />
                        <?php wp_nonce_field('add-' . $tax_name, '_ajax_nonce-add-' . $tax_name, false); ?>
                        <span id="<?php echo esc_attr($tax_name); ?>-ajax-response"></span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
<?php
    }

    /**
     * Show menu shortcode in single edit menu.
     * 
     * @access public
     * @return void
     * @since 1.0.2
     */
    function food_menu_shortcode() {
        global $post;
        $menu_id = $post->ID;
        echo '<input type="text" value="[food_menu id=' . esc_attr($menu_id) . ']" readonly>';
    }
}
WPFM_ActionHooks::instance();
