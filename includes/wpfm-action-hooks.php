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
        add_action('food_manager_save_food_manager', array($this, 'food_manager_save_food_manager_data'), 20, 2);

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

        // Register topping's meta fields
        add_action('init', array($this, 'register_topping_fields'));
        if (class_exists('WPFM_Writepanels')) {
            add_action('food_manager_topping_add_form_fields', array(WPFM_Writepanels::instance(), 'add_topping_fields'));
            add_action('food_manager_topping_edit_form_fields', array(WPFM_Writepanels::instance(), 'edit_topping_fields'));
        }
        add_action('edit_food_manager_topping', array($this, 'save_topping_fields'), 9);
        add_action('create_food_manager_topping', array($this, 'save_topping_fields'));

        add_action('wp_ajax_term_ajax_search',        array($this, 'term_ajax_search'));
        add_action('wp_ajax_nopriv_term_ajax_search', array($this, 'term_ajax_search'));
    }

    /**
     * Save Topping fields
     * 
     * @since 1.0.1
     */
    public function save_topping_fields($term_id) {
        if (!isset($_POST['topping_nonce']) || !wp_verify_nonce($_POST['topping_nonce'], 'save_toppings'))
            return;
        $topping_type = isset($_POST['topping_type']) ? $_POST['topping_type'] : '';
        $topping_required = isset($_POST['topping_required']) ? $_POST['topping_required'] : '';
        update_term_meta($term_id, 'topping_type', $topping_type);
        update_term_meta($term_id, 'topping_required', $topping_required);
    }

    /**
     * This will register topping's meta fields
     * 
     * @since 1.0.1
     */
    public function register_topping_fields() {
        register_meta('term', 'topping_required', array());
        register_meta('term', 'topping_type', array());
    }

    /**
     * Output some content when no results were found
     * 
     * @since 1.0.1
     */
    public function output_no_results() {
        get_food_manager_template('content-no-foods-found.php');
    }

    /**
     * Show results div
     * 
     * @since 1.0.1
     */
    public function food_filter_results() {
        echo '<div class="showing_applied_filters"></div>';
    }

    /**
     * Edit food form
     * 
     * @since 1.0.1
     */
    public function edit_food() {
        global $food_manager;
        echo $food_manager->forms->get_form('edit-food');
    }

    /**
     * Show Food type dropdown
     * 
     * @since 1.0.0
     */
    public function foods_by_food_type() {
        global $typenow, $wp_query;
        if ($typenow != 'food_manager' || !taxonomy_exists('food_manager_type')) {
            return;
        }
        $r                 = array();
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
        $output .= '<option value="" ' . selected(isset($_GET['food_manager_type']) ? $_GET['food_manager_type'] : '', '', false) . '>' . __('Select Food Type', 'wp-food-manager') . '</option>';
        $output .= $walker->walk($terms, 0, $r);
        $output .= '</select>';
        printf($output);
    }

    /**
     * Show category dropdown
     * 
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
        $output .= '<option value="" ' . selected(isset($_GET['food_manager_category']) ? $_GET['food_manager_category'] : '', '', false) . '>' . __('Select Food Category', 'wp-food-manager') . '</option>';
        $output .= $walker->walk($terms, 0, $r);
        $output .= '</select>';
        printf($output);
    }

    /**
     * Set post view on the single listing page
     * 
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
     * Change label
     * 
     * @since 1.0.0
     */
    public function admin_head() {

        /**
         * Add styles just for this page, and remove dashboard page links.
         *
         * @since 1.0.0
         */
        remove_submenu_page('index.php', 'food_manager_setup');

        global $menu;
        $plural     = __('Food Manager', 'wp-food-manager');
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
     * When a supported post type status is updated, check if any cached count transients
     * need to be removed, and remove the
     *
     * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
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
        $rlike = array();
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
            // Now that we have the key, use WordPress core to the delete the transient.
            delete_transient($key);
        }
        // Sometimes transients are not in the DB, so we have to do this too:
        wp_cache_flush();
    }

    /**
     * Clear expired transients
     * 
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
     * @since 1.0.0
     */
    public static function set_term($object_id = '', $terms = '', $tt_ids = '', $taxonomy = '') {
        WPFM_Cache_Helper::get_transient_version('fm_get_' . sanitize_text_field($taxonomy), true);
    }

    /**
     * When any term is edited
     * 
     * @since 1.0.0
     */
    public static function edited_term($term_id = '', $tt_id = '', $taxonomy = '') {
        WPFM_Cache_Helper::get_transient_version('fm_get_' . sanitize_text_field($taxonomy), true);
    }

    /**
     * Flush the cache
     * 
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
     * @since 1.0.0
     */
    public function get_listings() {
        global $wp_post_types;
        $result            = array();
        $search_location   = sanitize_text_field(stripslashes($_REQUEST['search_location']));
        $search_keywords   = sanitize_text_field(stripslashes($_REQUEST['search_keywords']));
        $search_categories = isset($_REQUEST['search_categories']) ? $_REQUEST['search_categories'] : '';
        $search_food_types = isset($_REQUEST['search_food_types']) ? $_REQUEST['search_food_types'] : '';
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
            'search_location'    => $search_location,
            'search_keywords'    => $search_keywords,
            'search_categories'  => $search_categories,
            'search_food_types'  => $search_food_types,
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
                }
                get_food_manager_template_part('content', 'food_manager'); ?>
            <?php endwhile; ?>
            <?php else :
            // Check there is a publish food or not.
            $default_foods = get_posts(array(
                'numberposts' => -1,
                'post_type'   => 'food_manager',
                'post_status'   => 'publish'
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
        if ($search_keywords) {
            $result['filter_value'][] = '&ldquo;' . $search_keywords . '&rdquo;';
        }
        $last_filter_value = array_pop($result['filter_value']);
        $result_implode = implode(', ', $result['filter_value']);
        if (count($result['filter_value']) >= 1) {
            $result['filter_value'] = explode(" ",  $result_implode);
            $result['filter_value'][] =  " &amp; ";
        } else {
            if (!empty($last_filter_value))
                $result['filter_value'] = explode(" ",  $result_implode);
        }
        $result['filter_value'][] =  $last_filter_value . " " . $post_type_label;
        if ($search_location) {
            $result['filter_value'][] = sprintf(__('located in &ldquo;%s&rdquo;', 'wp-food-manager'), $search_location);
        }
        if (sizeof($result['filter_value']) > 1) {
            $message = sprintf(_n('Search completed. Found %d matching record.', 'Search completed. Found %d matching records.', $food_cnt, 'wp-food-manager'), $food_cnt);
            $result['showing_applied_filters'] = true;
        } else {
            $message = "";
            $result['showing_applied_filters'] = false;
        }
        $search_values = array(
            'location'   => $search_location,
            'keywords'   => $search_keywords,
            'types'         => $search_food_types,
            'categories' => $search_categories
        );
        $result['filter_value'] = apply_filters('food_manager_get_listings_custom_filter_text', $message, $search_values);
        // Generate RSS link
        $result['showing_links'] = wpfm_get_filtered_links(array(
            'search_keywords'   => $search_keywords,
            'search_location'   => $search_location,
            'search_categories' => $search_categories,
            'search_food_types' => $search_food_types,
        ));
        $result['max_num_pages'] = $foods->max_num_pages;
        wp_send_json(apply_filters('food_manager_get_listings_result', $result, $foods));
    }

    /**
     * Check for WC Ajax request and fire action
     * 
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
     * @since 1.0.0
     */
    public function load_posted_form() {
        $forms = WPFM_Forms::instance();
        if (!empty($_POST['food_manager_form'])) {
            $forms->load_form_class(sanitize_title($_POST['food_manager_form']));
        }
    }

    /**
     * Display notice
     * 
     * @since 1.0.0
     */
    public function display_notice() {
        $notice = get_transient('WPFM_Food_Notice');
        if (!empty($notice)) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . $notice . '</p>';
            echo '</div>';
        }
    }

    /**
     * column content
     *
     * @since 1.0.0
     */
    public function custom_food_content_column($column, $post_id) {
        global $post;
        $thispost = get_post($post_id);
        switch ($column) {
            case 'food_title':
                echo wp_kses_post('<div class="food_title">');
                echo '<a href="' . esc_url(admin_url('post.php?post=' . $post->ID . '&action=edit')) . '" class="wpfm-tooltip food_title" wpfm-data-tip="' . sprintf(wp_kses('ID: %d', 'wp-food-manager'), $post->ID) . '">' . esc_html($post->post_title) . '</a>';
                echo wp_kses_post('</div>');
                echo wp_kses_post('<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__('Show more details', 'wp-food-manager') . '</span></button>');
                break;
            case 'food_banner':
                echo wp_kses_post('<div class="food_banner">');
                display_food_banner();
                echo wp_kses_post('</div>');
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
                echo $thispost->menu_order;
                break;
            case 'food_status':
                echo ucfirst($thispost->post_status);
                break;
            case 'food_actions':
                echo wp_kses_post('<div class="actions">');
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
                        printf('<a class="button button-icon wpfm-tooltip" href="%2$s" wpfm-data-tip="%3$s"><span class="dashicons dashicons-%1$s"></span></a>', $action['action'], esc_url($action['url']), esc_attr($action['name']), esc_html($action['name']));
                    } else {
                        echo esc_attr(str_replace('class="', 'class="button ', $action));
                    }
                }
                echo wp_kses_post('</div>');
                break;
        }
    }

    /**
     * content for Copy Shortcode
     *
     * @since 1.0.1
     */
    public function shortcode_copy_content_column($column, $post_id) {
        echo '<code>';
        printf(__('[food_menu id=%d]', 'wp-food-manager'), $post_id);
        echo '</code>';
    }

    /**
     * food_manager_save_food_manager_menu_data function.
     *
     * @access public
     * @param post_id numeric
     * @param post Object
     * @return void
     * @since 1.0.0
     */
    public function food_manager_save_food_manager_menu_data($post_id, $post) {
        if (isset($_POST['radio_icons']) && !empty($_POST['radio_icons'])) {
            $wpfm_radio_icon = $_POST['radio_icons'];
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
     * get_food_listings_by_category_id function.
     *
     * @access public
     * @param NULL
     * @return void
     * @since 1.0.0
     */
    public function get_food_listings_by_category_id() {
        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            $args = [
                'post_type' => 'food_manager',
                'post_per_page' => -1,
                'post_status' => 'publish',
                'post__not_in' => isset($_POST['exclude']) && !empty($_POST['exclude']) ? $_POST['exclude'] : array(),
                'tax_query' => [
                    [
                        'taxonomy' => $_POST['taxonomy'],
                        'terms' => $_POST['category_id'],
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
                        '<li class="menu-item-handle" data-food-id="' . $id . '">
							<div class="wpfm-admin-left-col">
								<span class="dashicons dashicons-menu"></span>
								<span class="item-title">' . get_the_title($id) . '</span>
							</div>
							<div class="wpfm-admin-right-col">
								<a href="javascript:void(0);" class="wpfm-food-item-remove">
									<span class="dashicons dashicons-dismiss"></span>
								</a>
							</div>
							<input type="hidden" name="wpfm_food_listing_ids[]" value="' . $id . '" />
						</li>';
                endwhile;
            endif;
            wp_reset_postdata();
            wp_send_json(array('html' => $html, 'success' => true));
        } else {
            $args = [
                'post_type' => 'food_manager',
                'post_per_page' => -1,
                'post__not_in' => isset($_POST['exclude']) && !empty($_POST['exclude']) ? $_POST['exclude'] : array(),
            ];
            $food_listing = new WP_Query($args);
            $html = [];
            if ($food_listing->have_posts()) :
                while ($food_listing->have_posts()) : $food_listing->the_post();
                    $id = get_the_ID();
                    $html[] =
                        '<li class="menu-item-handle" data-food-id="' . $id . '">
							<div class="wpfm-admin-left-col">
								<span class="dashicons dashicons-menu"></span>
								<span class="item-title">' . get_the_title($id) . '</span>
							</div>
							<div class="wpfm-admin-right-col">
								<a href="javascript:void(0);" class="wpfm-food-item-remove">
									<span class="dashicons dashicons-dismiss"></span>
								</a>
							</div>
							<input type="hidden" name="wpfm_food_listing_ids[]" value="' . $id . '" />
						</li>';
                endwhile;
            endif;
            wp_reset_postdata();
            wp_send_json(array('html' => $html, 'success' => true));
        }
        wp_die();
    }

    /**
     * food_manager_save_food_manager_data function.
     *
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @return void
     * @since 1.0.0
     */
    public function food_manager_save_food_manager_data($post_id, $post) {
        global $wpdb;
        $writepanels = WPFM_Writepanels::instance();
        // Advanced tab fields
        if (!empty($_POST['_food_menu_order'])) {
            $fd_menu_order = sanitize_text_field($_POST['_food_menu_order']);
            if (!add_post_meta($post_id, '_food_menu_order', $fd_menu_order, true)) {
                update_post_meta($post_id, '_food_menu_order', $fd_menu_order);
            }
        }
        if (isset($_POST['_enable_food_ingre'])) {
            $fd_food_ingre = sanitize_text_field($_POST['_enable_food_ingre']);
            if (!add_post_meta($post_id, '_enable_food_ingre', $fd_food_ingre, true)) {
                update_post_meta($post_id, '_enable_food_ingre', $fd_food_ingre);
            }
        } else {
            update_post_meta($post_id, '_enable_food_ingre', '');
        }
        if (isset($_POST['_enable_food_nutri'])) {
            $fd_food_nutri = sanitize_text_field($_POST['_enable_food_nutri']);
            if (!add_post_meta($post_id, '_enable_food_nutri', $fd_food_nutri, true)) {
                update_post_meta($post_id, '_enable_food_nutri', $fd_food_nutri);
            }
        } else {
            update_post_meta($post_id, '_enable_food_nutri', '');
        }
        // Ingredients.
        delete_post_meta($post_id, '_ingredients');
        $multiArrayIng = array();
        if (!empty($_POST['_ingredients'])) {
            foreach ($_POST['_ingredients'] as $id => $ingredient) {
                $term_name = get_term($id)->name;
                $unit_name = "Unit";
                if ($ingredient['unit_id'] == '' && empty($ingredient['unit_id'])) {
                    $unit_name = "Unit";
                } else {
                    $unit_name = get_term($ingredient['unit_id'])->name;
                }
                $item = [
                    'id'      => $id,
                    'unit_id' => !empty($ingredient['unit_id']) ? $ingredient['unit_id'] : null,
                    'value'   => !empty($ingredient['value']) ? $ingredient['value'] : null,
                    'ingredient_term_name' => $term_name,
                    'unit_term_name' => $unit_name
                ];
                $multiArrayIng[$id] = $item;
            }
            if (!add_post_meta($post_id, '_ingredients', $multiArrayIng, true)) {
                update_post_meta($post_id, '_ingredients', $multiArrayIng);
            }
        }
        // Nutritions.
        delete_post_meta($post_id, '_nutritions');
        $multiArrayNutri = array();
        if (!empty($_POST['_nutritions'])) {
            foreach ($_POST['_nutritions'] as $id => $nutrition) {
                $term_name = get_term($id)->name;
                $unit_name = "Unit";
                if ($nutrition['unit_id'] == '' && empty($nutrition['unit_id'])) {
                    $unit_name = "Unit";
                } else {
                    $unit_name = get_term($nutrition['unit_id'])->name;
                }
                $item = [
                    'id'      => $id,
                    'unit_id' => !empty($nutrition['unit_id']) ? $nutrition['unit_id'] : null,
                    'value'   => !empty($nutrition['value']) ? $nutrition['value'] : null,
                    'nutrition_term_name' => $term_name,
                    'unit_term_name' => $unit_name
                ];
                $multiArrayNutri[$id] = $item;
            }
            if (!add_post_meta($post_id, '_nutritions', $multiArrayNutri, true)) {
                update_post_meta($post_id, '_nutritions', $multiArrayNutri);
            }
        }
        // Food price
        $fd_price = sanitize_text_field($_POST['_food_price']);
        if (!add_post_meta($post_id, '_food_price', $fd_price, true)) {
            update_post_meta($post_id, '_food_price', $fd_price);
        }
        // Food sale price
        $fd_sale_price = sanitize_text_field($_POST['_food_sale_price']);
        if (!add_post_meta($post_id, '_food_sale_price', $fd_sale_price, true)) {
            update_post_meta($post_id, '_food_sale_price', $fd_sale_price);
        }
        // Food stock status
        $fd_stock_status = sanitize_text_field($_POST['_food_stock_status']);
        if (!add_post_meta($post_id, '_food_stock_status', $fd_stock_status, true)) {
            update_post_meta($post_id, '_food_stock_status', $fd_stock_status);
        }
        // Repeated options
        $repeated_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
        if (!add_post_meta($post_id, '_food_repeated_options', $repeated_options, true)) {
            update_post_meta($post_id, '_food_repeated_options', $repeated_options);
        }
        // Options value count
        $array_cnt = isset($_POST['option_value_count']) ? $_POST['option_value_count'] : '';
        if (isset($array_cnt) && !empty($array_cnt)) {
            $food_data_option_value_count = array();
            $index = 0;
            foreach ($array_cnt as $number) {
                if ($number == 1) {
                    $index++;
                }
                $food_data_option_value_count[$index][] = $number;
            }
            if (!add_post_meta($post_id, 'wpfm_option_value_count', $food_data_option_value_count, true)) {
                update_post_meta($post_id, 'wpfm_option_value_count', $food_data_option_value_count);
            }
        }
        // Save Food Form fields values
        foreach ($writepanels->food_manager_data_fields()['food'] as $key => $field) {
            $type = !empty($field['type']) ? $field['type'] : '';
            // food banner
            if ('_food_banner' === "_" . $key) {
                if (isset($_POST["_" . $key]) && !empty($_POST["_" . $key])) {
                    $thumbnail_image = is_array($_POST["_" . $key]) ? array_values(array_filter($_POST["_" . $key])) : $_POST["_" . $key];
                    update_post_meta($post_id, "_" . $key, $thumbnail_image);
                    if (is_array($_POST["_" . $key])) {
                        $_POST["_" . $key] = array_values(array_filter($_POST["_" . $key]));
                    }
                }
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
            if (isset($_POST["_" . $key]) && !empty($_POST["_" . $key])) {
                update_post_meta($post_id, "_" . $key, $_POST["_" . $key]);
            } else {
                update_post_meta($post_id, "_" . $key, "");
            }
            switch ($type) {
                case 'textarea':
                    if (isset($_POST[$key])) {
                        update_post_meta($post_id, $key, wp_kses_post(stripslashes($_POST[$key])));
                    }
                    break;
                case 'checkbox':
                    if (isset($_POST[$key])) {
                        update_post_meta($post_id, $key, 1);
                    } else {
                        update_post_meta($post_id, $key, 0);
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
                        update_post_meta($post_id, $key, $date_dbformatted);
                    }
                    break;
                default:
                    if (!isset($_POST[$key])) {
                        continue 2;
                    } elseif (is_array($_POST[$key])) {
                        update_post_meta($post_id, $key, array_filter(array_map('sanitize_text_field', $_POST[$key])));
                    } else {
                        update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
                    }
                    break;
            }
        }
        // Save Extra Options/Topping form fields values
        foreach ($writepanels->food_manager_data_fields()['extra_options'] as $key => $field) {
            // Author
            if ('_food_author' === $key) {
                $wpdb->update($wpdb->posts, array('post_author' => $_POST[$key] > 0 ? absint($_POST[$key]) : 0), array('ID' => $post_id));
            }
            // Everything else		
            else {
                $type = !empty($field['type']) ? $field['type'] : '';
                $extra_options = array();
                $food = $post;
                $form_add_food_instance = call_user_func(array('WPFM_Add_Food_Form', 'instance'));
                $custom_food_fields  = !empty($form_add_food_instance->get_food_manager_fieldeditor_fields()) ? $form_add_food_instance->get_food_manager_fieldeditor_fields() : array();
                $custom_extra_options_fields  = !empty($form_add_food_instance->get_food_manager_fieldeditor_extra_options_fields()) ? $form_add_food_instance->get_food_manager_fieldeditor_extra_options_fields() : array();
                $custom_fields = '';
                if (!empty($custom_extra_options_fields)) {
                    $custom_fields = array_merge($custom_food_fields, $custom_extra_options_fields);
                } else {
                    $custom_fields = $custom_food_fields;
                }
                $default_fields = $form_add_food_instance->get_default_food_fields();
                $additional_fields = [];
                if (!empty($custom_fields) && isset($custom_fields) && !empty($custom_fields['extra_options'])) {
                    foreach ($custom_fields['extra_options'] as $field_name => $field_data) {
                        if (!array_key_exists($field_name, $default_fields['extra_options'])) {
                            $meta_key = '_' . $field_name;
                            $field_value = $food->$meta_key;
                            if (isset($field_value)) {
                                $additional_fields[$field_name] = $field_data;
                            }
                        }
                    }
                    $additional_fields = apply_filters('food_manager_show_additional_details_fields', $additional_fields);
                }
                // Find how many total reapeated extra option there then store it.
                $toppings_arr = array();
                if (isset($_POST['repeated_options']) && is_array($_POST['repeated_options'])) {
                    foreach ($_POST['repeated_options'] as $option_count) {
                        $counter = 0;
                        if (isset($_POST['topping_key_' . $option_count])) {
                            $topping_key = isset($_POST['topping_key_' . $option_count]) ? $_POST['topping_key_' . $option_count] : '';
                            $topping_name = isset($_POST['topping_name_' . $option_count]) ? $_POST['topping_name_' . $option_count] : '';
                            $toppings_arr[] = $topping_name;
                            $topping_type = isset($_POST['_topping_type_' . $option_count]) ? $_POST['_topping_type_' . $option_count] : '';
                            $topping_required = isset($_POST['_topping_required_' . $option_count]) ? $_POST['_topping_required_' . $option_count] : '';
                            $topping_description = isset($_POST['_topping_description_' . $option_count]) ? $_POST['_topping_description_' . $option_count] : '';
                            $option_values = array();
                            if (isset($_POST['option_value_count'])) {
                                $find_option = array_search('%%repeated-option-index%%', $_POST['option_value_count']);
                                if ($find_option !== false) {
                                    // Remove from array
                                    unset($_POST['option_value_count'][$find_option]);
                                }
                                foreach ($_POST['option_value_count'] as $option_value_count) {
                                    if (!empty($_POST[$option_count . '_option_name_' . $option_value_count]) || !empty($_POST[$option_count . '_option_default_' . $option_value_count]) || !empty($_POST[$option_count . '_option_price_' . $option_value_count])) {
                                        $option_values[$option_value_count] = array(
                                            'option_name' => isset($_POST[$option_count . '_option_name_' . $option_value_count]) ? $_POST[$option_count . '_option_name_' . $option_value_count] : '',
                                            'option_default' => isset($_POST[$option_count . '_option_default_' . $option_value_count]) ? $_POST[$option_count . '_option_default_' . $option_value_count] : '',
                                            'option_price' => isset($_POST[$option_count . '_option_price_' . $option_value_count]) ? $_POST[$option_count . '_option_price_' . $option_value_count] : '',
                                            'option_price_type' => isset($_POST[$option_count . '_option_price_type_' . $option_value_count]) ? $_POST[$option_count . '_option_price_type_' . $option_value_count] : ''
                                        );
                                    }
                                }
                            }
                            if (!empty($custom_extra_options_fields)) {
                                $extra_options[$option_count] = array(
                                    'topping_key' => $topping_key,
                                    'topping_name' => $topping_name,
                                );
                                foreach ($custom_extra_options_fields as $custom_ext_key => $custom_extra_options_field) {
                                    foreach ($custom_extra_options_field as $custom_ext_single_key => $custom_extra_options_single_field) {
                                        if ($custom_ext_single_key !== 'topping_name' && $custom_ext_single_key !== 'topping_options') {
                                            $custom_ext_key_post = isset($_POST["_" . $custom_ext_single_key . "_" . $option_count]) ? $_POST["_" . $custom_ext_single_key . "_" . $option_count] : '';
                                            $extra_options[$option_count][$custom_ext_single_key] = $custom_ext_key_post;
                                        }
                                        if ($custom_ext_single_key == 'topping_name') {
                                            $custom_ext_key_post = isset($_POST[$custom_ext_single_key . "_" . $option_count]) ? $_POST[$custom_ext_single_key . "_" . $option_count] : '';
                                            $extra_options[$option_count][$custom_ext_single_key] = $custom_ext_key_post;
                                        }
                                        if ($custom_ext_single_key == 'topping_options') {
                                            $extra_options[$option_count][$custom_ext_single_key] = $option_values;
                                        }
                                    }
                                }
                            } else {
                                $extra_options[$option_count] = array(
                                    'topping_key' => $topping_key,
                                    'topping_name' => $topping_name,
                                    'topping_type' => $topping_type,
                                    'topping_required' => $topping_required,
                                    'topping_description' => $topping_description,
                                    'topping_options' => $option_values,
                                );
                            }

                            if (!empty($additional_fields)) {
                                foreach ($additional_fields as $add_key => $additional_field) {
                                    $key_post = isset($_POST["_" . $add_key . "_" . $option_count]) ? $_POST["_" . $add_key . "_" . $option_count] : '';
                                    $extra_options[$option_count][$add_key] = $key_post;
                                }
                            }
                        }
                    }

                    $counter++;
                }
                $exist_toppings = get_the_terms($post_id, 'food_manager_topping');
                if ($exist_toppings) {
                    $removed_toppings_ids = [];
                    foreach ($exist_toppings as $toppings) {
                        if (!in_array($toppings->slug, $toppings_arr)) {
                            $removed_toppings_ids[] = (int)$toppings->term_id;
                        }
                    }
                    wp_remove_object_terms($post_id, $removed_toppings_ids, 'food_manager_topping');
                }
                $term_ids = wp_set_object_terms($post_id, $toppings_arr, 'food_manager_topping');
                update_post_meta($post_id, '_food_toppings', $extra_options);
                if ($term_ids) {
                    foreach ($term_ids as $key => $term_id) {
                        $key++;
                        $description = (isset($_POST['_topping_description_' . $key]) && !empty($_POST['_topping_description_' . $key])) ? $_POST['_topping_description_' . $key] : '';
                        $topping_required = (isset($_POST['_topping_required_' . $key]) && !empty($_POST['_topping_required_' . $key])) ? $_POST['_topping_required_' . $key] : '';
                        $topping_type = (isset($_POST['_topping_type_' . $key]) && !empty($_POST['_topping_type_' . $key])) ? $_POST['_topping_type_' . $key] : '';
                        wp_update_term($term_id, 'food_manager_topping', array('description' => $description));
                        update_term_meta($term_id, 'topping_required', $topping_required);
                        update_term_meta($term_id, 'topping_type', $topping_type);
                    }
                }
            }
        }
        remove_action('food_manager_save_food_manager', array($this, 'food_manager_save_food_manager_data'), 20, 2);
        $food_data = array(
            'ID'          => $post_id,
        );
        wp_update_post($food_data);
        add_action('food_manager_save_food_manager', array($this, 'food_manager_save_food_manager_data'), 20, 2);
    }

    /**
     * Edit bulk actions
     * 
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
     * save_post function.
     *
     * @since 1.0.0
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @return void
     */
    public function save_post($post_id, $post) {
        global $wpdb;
        $ingredient_ids = [];
        $nutrition_ids = [];
        $meta_ingredient = get_post_meta($post_id, '_ingredients', true);
        if ($meta_ingredient) {
            foreach ($meta_ingredient as $value) {
                $ingredient_ids[] = $value['id'];
            }
        }
        $meta_nutrition = get_post_meta($post_id, '_nutritions', true);
        if ($meta_nutrition) {
            foreach ($meta_nutrition as $value) {
                $nutrition_ids[] = $value['id'];
            }
        }
        if (!empty($ingredient_ids)) {
            wp_set_object_terms($post_id, $ingredient_ids, 'food_manager_ingredient');
        }
        if (!empty($nutrition_ids)) {
            wp_set_object_terms($post_id, $nutrition_ids, 'food_manager_nutrition');
        }
        if (empty($post_id) || empty($post) || empty($_POST)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (is_int(wp_is_post_revision($post))) return;
        if (is_int(wp_is_post_autosave($post))) return;
        if (empty($_POST['food_manager_nonce']) || !wp_verify_nonce($_POST['food_manager_nonce'], 'save_meta_data')) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type == 'food_manager') {
            do_action('food_manager_save_food_manager', $post_id, $post);
            $unit_ids = [];
            $ingredient_ids = [];
            $nutrition_ids = [];
            if (isset($_POST['_ingredients']) && !empty($_POST['_ingredients'])) {
                $ingredients = $_POST['_ingredients'];
                foreach ($ingredients as $ingredient_id => $ingredient) {
                    $ingredient_ids[] = $ingredient_id;
                    if (trim($ingredient['unit_id'])) {
                        $unit_ids[] = (int)$ingredient['unit_id'];
                    }
                }
            }
            $exist_ingredients = get_the_terms($post_id, 'food_manager_ingredient');
            if ($exist_ingredients) {
                $removed_ingredient_ids = [];
                foreach ($exist_ingredients as $ingredient) {
                    if (!in_array($ingredient->term_id, $ingredient_ids)) {
                        $removed_ingredient_ids[] = $ingredient->term_id;
                    }
                }
                wp_remove_object_terms($post_id, $removed_ingredient_ids, 'food_manager_ingredient');
            }
            if (!empty($ingredient_ids)) {
                wp_set_object_terms($post_id, $ingredient_ids, 'food_manager_ingredient');
            }
            if (isset($_POST['_nutritions']) && !empty($_POST['_nutritions'])) {
                $nutritions = $_POST['_nutritions'];
                foreach ($nutritions as $nutrition_id => $nutrition) {
                    $nutrition_ids[] = $nutrition_id;
                    if (trim($nutrition['unit_id'])) {
                        $unit_ids[] = (int)$nutrition['unit_id'];
                    }
                }
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
            if ($unit_ids) {
                wp_set_object_terms($post_id, $unit_ids, 'food_manager_unit');
            }
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
        if ($post->post_type == 'food_manager_menu')
            do_action('food_manager_save_food_manager_menu', $post_id, $post);
    }

    /**
     * add_meta_boxes function.
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
        add_meta_box('radio-food_manager_typediv', $taxonomy->labels->name, array($this, 'replace_food_manager_type_metabox'), 'food_manager', 'side', 'core', array('taxonomy' => $taxonomy_slug));
        if ('add' != $screen->action) {
            // Show food menu Shortcode on edit menu page - admin.
            add_meta_box('wpfm_menu_shortcode', 'Shortcode', array($this, 'food_menu_shortcode'), 'food_manager_menu', 'side', 'low');
        }
    }

    /**
     * Add image field in 'food_manager_type' taxonomy page
     * 
     * @param $taxonomy
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
     * @param $tt_id
     * @param $term_id
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
     * @param $term
     * @param $taxonomy
     * @since 1.0.0
     */
    function update_custom_taxonomy_image_for_food_type($term, $taxonomy) { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="image_id"><?php _e('Image', 'taxt-domain'); ?></label>
            </th>
            <td>
                <?php $image_id = get_term_meta($term->term_id, 'image_id', true); ?>
                <input type="hidden" id="image_id" name="image_id" value="<?php echo $image_id; ?>">
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
     * @param $term_id
     * @param $tt_id
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
     * @since 1.0.0
     */
    function add_custom_taxonomy_script_for_food_type() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_type') {
            return;
        } ?>
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
     * @param string $columns
     * @param string $column
     * @param int $id
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
     * @param string $taxonomy
     * @since 1.0.0
     */
    function add_custom_taxonomy_image_for_food_category($taxonomy) {
    ?>
        <div class="form-field term-group">
            <label for="food_cat_image_id" class="wpfm-food-category-tax-image"><?php _e('Image/Icon', 'taxt-domain'); ?></label>
            <input type="hidden" id="food_cat_image_id" name="food_cat_image_id" class="custom_media_url" value="">
            <div id="image_wrapper"></div>
            <p>
                <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php _e('Add Image', 'taxt-domain'); ?>">
                <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php _e('Remove Image', 'taxt-domain'); ?>">
            </p>
        </div>
    <?php
    }

    /**
     * Save the 'food_manager_category' taxonomy image field
     * 
     * @param string $term_id
     * @param string $tt_id
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
     * @param object $term
     * @param string $taxonomy
     * @since 1.0.0
     */
    function update_custom_taxonomy_image_for_food_category($term, $taxonomy) { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="food_cat_image_id"><?php _e('Image', 'taxt-domain'); ?></label>
            </th>
            <td>
                <?php $food_cat_image_id = get_term_meta($term->term_id, 'food_cat_image_id', true); ?>
                <input type="hidden" id="food_cat_image_id" name="food_cat_image_id" value="<?php echo $food_cat_image_id; ?>">
                <div id="image_wrapper">
                    <?php if ($food_cat_image_id) { ?>
                        <?php echo wp_get_attachment_image($food_cat_image_id, 'thumbnail'); ?>
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
     * Update the 'food_manager_category' taxonomy image field
     * 
     * @param string $term_id
     * @param string $tt_id
     * @since 1.0.0
     */
    function updated_custom_taxonomy_image_for_food_category($term_id, $tt_id) {
        if (isset($_POST['food_cat_image_id']) && '' !== $_POST['food_cat_image_id']) {
            $image = $_POST['food_cat_image_id'];
            update_term_meta($term_id, 'food_cat_image_id', $image);
        } else {
            update_term_meta($term_id, 'food_cat_image_id', '');
        }
    }

    /**
     * Enqueue the wp_media library
     * 
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
     * @param string $columns
     * @param string $column
     * @param int $id
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
     * Localisation
     * 
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        $domain = 'wp-food-manager';
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain($domain, WP_LANG_DIR . "/wp-food-manager/" . $domain . "-" . $locale . ".mo");
        load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Load functions
     * 
     * @since 1.0.0
     */
    public function include_template_functions() {
        include(WPFM_PLUGIN_DIR . '/wp-food-manager-functions.php');
        include(WPFM_PLUGIN_DIR . '/wp-food-manager-template.php');
    }

    /**
     * Register and enqueue scripts and css
     * 
     * @since 1.0.0
     */
    public function frontend_scripts() {
        $ajax_url         = WPFM_Ajax::get_endpoint();
        $ajax_filter_deps = array('jquery', 'jquery-deserialize');
        $chosen_shortcodes   = array('add_food', 'food_dashboard', 'foods', 'food_categories', 'food_type');
        $chosen_used_on_page = has_wpfm_shortcode(null, $chosen_shortcodes);
        // jQuery Chosen - vendor
        if (apply_filters('food_manager_chosen_enabled', $chosen_used_on_page)) {
            wp_register_script('chosen', WPFM_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
            wp_register_script('wp-food-manager-term-multiselect', WPFM_PLUGIN_URL . '/assets/js/term-multiselect.min.js', array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_register_script('wp-food-manager-term-select-multi-appearance', WPFM_PLUGIN_URL . '/assets/js/term-select-multi-appearance.min.js', array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_register_script('wp-food-manager-multiselect', WPFM_PLUGIN_URL . '/assets/js/multiselect.min.js', array('jquery', 'chosen'), WPFM_VERSION, true);
            wp_enqueue_style('chosen', WPFM_PLUGIN_URL . '/assets/css/chosen.min.css');
            $ajax_filter_deps[] = 'chosen';
        }
        // File upload - vendor
        if (apply_filters('wpfm_ajax_file_upload_enabled', true)) {
            wp_register_script('jquery-iframe-transport', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.iframe-transport.min.js', array('jquery'), '1.8.3', true);
            wp_register_script('jquery-fileupload', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.fileupload.min.js', array('jquery', 'jquery-iframe-transport', 'jquery-ui-widget'), '5.42.3', true);
            wp_register_script('wpfm-ajax-file-upload', WPFM_PLUGIN_URL . '/assets/js/ajax-file-upload.min.js', array('jquery', 'jquery-fileupload'), WPFM_VERSION, true);
            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'jpg'));
            $js_field_html_img = ob_get_clean();
            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'zip'));
            $js_field_html = ob_get_clean();
            wp_localize_script('wpfm-ajax-file-upload', 'wpfm_ajax_file_upload', array(
                'ajax_url'               => $ajax_url,
                'js_field_html_img'      => esc_js(str_replace("\n", "", $js_field_html_img)),
                'js_field_html'          => esc_js(str_replace("\n", "", $js_field_html)),
                'i18n_invalid_file_type' => __('Invalid file type. Accepted types:', 'wp-food-manager')
            ));
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        // jQuery Deserialize - vendor
        wp_register_script('jquery-deserialize', WPFM_PLUGIN_URL . '/assets/js/jquery-deserialize/jquery.deserialize.js', array('jquery'), '1.2.1', true);
        wp_enqueue_style('wpfm-frontend', WPFM_PLUGIN_URL . '/assets/css/frontend.min.css');
        // Common js
        wp_register_script('wp-food-manager-frontend', WPFM_PLUGIN_URL . '/assets/js/frontend.min.js', array('jquery'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-frontend');
        // Common js
        wp_register_script('wp-food-manager-common', WPFM_PLUGIN_URL . '/assets/js/common.min.js', array('jquery'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-common');
        // Food submission forms and validation js
        wp_register_script('wp-food-manager-food-submission', WPFM_PLUGIN_URL . '/assets/js/food-submission.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), WPFM_VERSION, true);
        wp_enqueue_script('wp-food-manager-food-submission');
        wp_localize_script('wp-food-manager-food-submission', 'wpfm_food_submission', array(
            'i18n_datepicker_format' => WPFM_Date_Time::get_datepicker_format(),
            'ajax_url'      => admin_url('admin-ajax.php'),
        ));
        wp_enqueue_script('wpfm-accounting');
        wp_enqueue_style('dashicons');
        wp_register_script('wpfm-accounting', WPFM_PLUGIN_URL . '/assets/js/accounting/accounting.min.js', array('jquery'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-accounting',
            'wpfm_accounting_params',
            array(
                'wpfm_sale_less_than_regular_error' => __('Please enter in a value less than the regular price.', 'woocommerce'),
            )
        );
        wp_register_script('wpfm-content-food-listing', WPFM_PLUGIN_URL . '/assets/js/content-food-listing.min.js', array('jquery', 'wp-food-manager-common'), WPFM_VERSION, true);
        wp_localize_script('wpfm-content-food-listing', 'wpfm_content_food_listing', array(
            'i18n_dateLabel' => __('Select Date', 'wp-food-manager'),
            'i18n_today' => __('Today', 'wp-food-manager'),
            'i18n_tomorrow' => __('Tomorrow', 'wp-food-manager'),
            'i18n_thisWeek' => __('This Week', 'wp-food-manager'),
            'i18n_nextWeek' => __('Next Week', 'wp-food-manager'),
            'i18n_thisMonth' => __('This Month', 'wp-food-manager'),
            'i18n_nextMonth' => __('Next Month', 'wp-food-manager'),
            'i18n_thisYear' => __('This Year', 'wp-food-manager'),
            'i18n_nextYear' => __('Next Month', 'wp-food-manager')
        ));
        // Ajax filters js
        wp_register_script('wpfm-ajax-filters', WPFM_PLUGIN_URL . '/assets/js/food-ajax-filters.min.js', $ajax_filter_deps, WPFM_VERSION, true);
        wp_localize_script('wpfm-ajax-filters', 'wpfm_ajax_filters', array(
            'ajax_url'                => $ajax_url,
            'is_rtl'                  => is_rtl() ? 1 : 0,
            'lang'                    => apply_filters('wpfm_lang', null)
        ));
        // Dashboard
        wp_register_script('wp-food-manager-food-dashboard', WPFM_PLUGIN_URL . '/assets/js/food-dashboard.min.js', array('jquery'), WPFM_VERSION, true);
        wp_localize_script('wp-food-manager-food-dashboard', 'food_manager_food_dashboard', array(
            'i18n_btnOkLabel' => __('Delete', 'wp-food-manager'),
            'i18n_btnCancelLabel' => __('Cancel', 'wp-food-manager'),
            'i18n_confirm_delete' => __('Are you sure you want to delete this food?', 'wp-food-manager')
        ));
        wp_enqueue_style('wpfm-jquery-ui-css', WPFM_PLUGIN_URL . '/assets/css/jquery-ui/jquery-ui.min.css');
        wp_register_script('wpfm-slick-script', WPFM_PLUGIN_URL . '/assets/js/slick/slick.min.js', array('jquery'));
        wp_register_style('wpfm-slick-style', WPFM_PLUGIN_URL . '/assets/js/slick/slick.min.css', array());
        wp_register_style('wpfm-slick-theme-style', WPFM_PLUGIN_URL . '/assets/js/slick/slick-theme.min.css', array());
        wp_register_style('wpfm-grid-style', WPFM_PLUGIN_URL . '/assets/css/wpfm-grid.min.css');
        wp_register_style('wp-food-manager-font-style', WPFM_PLUGIN_URL . '/assets/fonts/style.min.css');
        wp_enqueue_style('wpfm-grid-style');
        wp_enqueue_style('wp-food-manager-font-style');
        wp_enqueue_style('wp-food-manager-food-icons-style');
        wp_enqueue_editor();
        wp_register_script('wpfm-term-autocomplete', WPFM_PLUGIN_URL . '/assets/js/term-autocomplete.js', array('jquery'), '1.0.1', true);
        wp_localize_script(
            'wpfm-term-autocomplete',
            'wpfm_term_autocomplete',
            array(
                'ajax_url' => admin_url('admin-ajax.php')
            )
        );
    }

    /**
     * admin_menu function.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_menu() {
        $this->settings_page = WPFM_Settings::instance();
        if (get_option('food_manager_enable_field_editor', true)) {
            add_submenu_page('edit.php?post_type=food_manager', __('Field Editor', 'wp-food-manager'), __('Field Editor', 'wp-food-manager'), 'manage_options', 'food-manager-form-editor', array(WPFM_Field_Editor::instance(), 'output'));
        }
        add_submenu_page('edit.php?post_type=food_manager', __('Settings', 'wp-food-manager'), __('Settings', 'wp-food-manager'), 'manage_options', 'food-manager-settings', array($this->settings_page, 'output'));
        add_dashboard_page(__('Setup', 'wp-food-manager'), __('Setup', 'wp-food-manager'), 'manage_options', 'food_manager_setup', array(WPFM_Setup::instance(), 'output'));
    }

    /**
     * admin_enqueue_scripts function.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_enqueue_scripts() {
        global $wp_scripts;
        $screen = get_current_screen();
        $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
        wp_enqueue_style('wpfm-backend-css', WPFM_PLUGIN_URL . '/assets/css/backend.min.css');
        wp_enqueue_style('jquery-ui-style', WPFM_PLUGIN_URL . '/assets/css/jquery-ui/jquery-ui.min.css', array(), $jquery_version);
        $units    = get_terms(
            [
                'taxonomy'   => 'food_manager_unit',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]
        );
        $unitList = [];
        if (!empty($units)) {
            foreach ($units as $unit) {
                $unitList[$unit->term_id] = $unit->name;
            }
        }
        wp_register_script('wpfm-admin', WPFM_PLUGIN_URL . '/assets/js/admin.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-admin',
            'wpfm_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
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
        wp_register_script('wp-food-manager-admin-settings', WPFM_PLUGIN_URL . '/assets/js/admin-settings.min.js', array('jquery'), WPFM_VERSION, true);
        if (is_admin() && !isset($_GET['page']) == 'wc-settings') {
            wp_enqueue_script('wp-food-manager-admin-settings');
        }
        wp_register_script('chosen', WPFM_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
        wp_enqueue_script('chosen');
        wp_enqueue_style('chosen', WPFM_PLUGIN_URL . '/assets/css/chosen.min.css');
        wp_enqueue_style('wpfm-font-style', WPFM_PLUGIN_URL . '/assets/fonts/style.min.css');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_register_script('wpfm-accounting', WPFM_PLUGIN_URL . '/assets/js/accounting/accounting.min.js', array('jquery'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-accounting',
            'wpfm_accounting_params',
            array(
                'wpfm_sale_less_than_regular_error' => __('Please enter in a value less than the regular price.', 'woocommerce'),
            )
        );
        wp_enqueue_script('wpfm-accounting');
        wp_enqueue_style('dashicons');
        // File upload - vendor
        if (apply_filters('wpfm_ajax_file_upload_enabled', true)) {
            wp_register_script('jquery-iframe-transport', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.iframe-transport.min.js', array('jquery'), '1.8.3', true);
            wp_register_script('jquery-fileupload', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.fileupload.min.js', array('jquery', 'jquery-iframe-transport', 'jquery-ui-widget'), '5.42.3', true);
            wp_register_script('wpfm-ajax-file-upload', WPFM_PLUGIN_URL . '/assets/js/ajax-file-upload.min.js', array('jquery', 'jquery-fileupload'), WPFM_VERSION, true);
            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'jpg'));
            $js_field_html_img = ob_get_clean();
            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'zip'));
            $js_field_html = ob_get_clean();
            wp_localize_script('wpfm-ajax-file-upload', 'wpfm_ajax_file_upload', array(
                'ajax_url'               => admin_url('admin-ajax.php'),
                'js_field_html_img'      => esc_js(str_replace("\n", "", $js_field_html_img)),
                'js_field_html'          => esc_js(str_replace("\n", "", $js_field_html)),
                'i18n_invalid_file_type' => __('Invalid file type. Accepted types:', 'wp-food-manager')
            ));
        }
        wp_enqueue_editor();
        wp_register_script('chosen', WPFM_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
        wp_register_script('wp-food-manager-form-field-editor', WPFM_PLUGIN_URL . '/assets/js/field-editor.min.js', array('jquery', 'jquery-ui-sortable', 'chosen'), WPFM_VERSION, true);
        wp_localize_script(
            'wp-food-manager-form-field-editor',
            'wpfm_form_editor',
            array(
                'cofirm_delete_i18n'                    => __('Are you sure you want to delete this row?', 'wp-food-manager'),
                'cofirm_reset_i18n'                     => __('Are you sure you want to reset your changes? This cannot be undone.', 'wp-food-manager'),
                'ajax_url'                              => admin_url('admin-ajax.php'),
                'wpfm_form_editor_security' => wp_create_nonce('_nonce_wpfm_form_editor_security'),
            )
        );
        if (isset($_GET['page']) && 'food_manager_setup' === $_GET['page']) {
            wp_enqueue_style('food_manager_setup_css', WPFM_PLUGIN_URL . '/assets/css/setup.min.css', array('dashicons'));
        }
        wp_register_script('wpfm-term-autocomplete', WPFM_PLUGIN_URL . '/assets/js/term-autocomplete.js', array('jquery', 'jquery-ui-autocomplete'), '1.0.1', true);
        wp_localize_script(
            'wpfm-term-autocomplete',
            'wpfm_term_autocomplete',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('wpfm-autocomplete-security'),
            )
        );
    }

    /**
     * Include admin files conditionally.
     * 
     * @since 1.0.0
     */
    public function conditional_includes() {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }
        switch ($screen->id) {
            case 'options-permalink':
                include WPFM_PLUGIN_DIR . '/admin/wpfm-permalink-settings.php';
                break;
        }
    }

    /**
     * get_group_field_html function.
     *
     * @access public
     * @since 1.0.0
     */
    public function get_group_field_html() {
        check_ajax_referer('_nonce_wpfm_form_editor_security', 'security');
        $field_types = apply_filters(
            'food_manager_form_group_field_types',
            array(
                'text'        => __('Text', 'wp-food-manager'),
                'checkbox'    => __('Checkbox', 'wp-food-manager'),
                'date'        => __('Date', 'wp-food-manager'),
                'file'        => __('File', 'wp-food-manager'),
                'hidden'      => __('Hidden', 'wp-food-manager'),
                'multiselect' => __('Multiselect', 'wp-food-manager'),
                'number'      => __('Number', 'wp-food-manager'),
                'password'    => __('Password', 'wp-food-manager'),
                'radio'       => __('Radio', 'wp-food-manager'),
                'select'      => __('Select', 'wp-food-manager'),
                'textarea'    => __('Textarea', 'wp-food-manager'),
                'options'    => __('Options', 'wp-food-manager'),
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
        include WPFM_PLUGIN_DIR . '/admin/wpfm-form-field-editor-group-field-row.php';
        echo esc_attr(ob_get_clean());
        wp_die();
    }

    /**
     * register_settings function.
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
                    add_option($option['name'], $option['std']);
                register_setting($this->settings_group, $option['name']);
            }
        }
    }

    /**
     * Sends user to the setup page on first activation
     * 
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
                $topping_type = get_term_meta($term->term_id, 'topping_type', true);
                $topping_required = get_term_meta($term->term_id, 'topping_required', true);
                $items[] = [
                    'id' => $term->term_id,
                    'label' => $term->name,
                    'description' => term_description($term->term_id, $_REQUEST['taxonomy']),
                    'selection_type' => $topping_type,
                    'required' => $topping_required,
                ];
            }
        }
        wp_send_json_success($items);
    }

    /**
     * Callback to set up the metabox
     * Mimicks the traditional hierarchical term metabox, but modified with our nonces 
     * 	 
     * @param  object $post
     * @param  array $args
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
        <div id="taxonomy-<?php echo $tax_name; ?>" class="radio-buttons-for-taxonomies categorydiv">
            <ul id="<?php echo $tax_name; ?>-tabs" class="category-tabs">
                <li class="tabs"><a href="#<?php echo $tax_name; ?>-all"><?php echo $taxonomy->labels->all_items; ?></a></li>
                <li class="hide-if-no-js"><a href="#<?php echo $tax_name; ?>-pop"><?php echo esc_html($taxonomy->labels->most_used); ?></a></li>
            </ul>
            <div id="<?php echo $tax_name; ?>-pop" class="tabs-panel" style="display: none;">
                <ul id="<?php echo $tax_name; ?>checklist-pop" class="categorychecklist form-no-clear">
                    <?php
                    $popular_terms = get_terms($tax_name, array('orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false));
                    $popular_ids = array();
                    foreach ($popular_terms as $term) {
                        $popular_ids[] = $term->term_id;
                        $value = is_taxonomy_hierarchical($tax_name) ? $term->term_id : $term->slug;
                        $id = 'popular-' . $tax_name . '-' . $term->term_id;
                        $checked = checked($single_term_id, $term->term_id, false); ?>
                        <li id="<?php echo $id; ?>" class="popular-category">
                            <label class="selectit">
                                <input id="in-<?php echo $id; ?>" type="radio" <?php echo $checked; ?> name="tax_input[<?php echo $tax_name ?>][]" value="<?php echo (int) $term->term_id; ?>" <?php disabled(!current_user_can($taxonomy->cap->assign_terms)); ?> />
                                <?php
                                /** This filter is documented in wp-includes/category-template.php */
                                echo esc_html(apply_filters('the_category', $term->name, '', ''));
                                ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div id="<?php echo $tax_name; ?>-all" class="tabs-panel">
                <ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name; ?>" class="categorychecklist form-no-clear">
                    <?php wp_terms_checklist($post->ID, array('taxonomy' => $tax_name, 'popular_cats' => $popular_ids, 'selected_cats' => array($single_term_id))); ?>
                </ul>
            </div>
            <?php if (current_user_can($taxonomy->cap->edit_terms)) : ?>
                <div id="<?php echo $tax_name; ?>-adder" class="wp-hidden-children">
                    <a id="<?php echo $tax_name; ?>-add-toggle" href="#<?php echo $tax_name; ?>-add" class="hide-if-no-js taxonomy-add-new">
                        <?php
                        /* translators: %s: add new taxonomy label */
                        printf(__('+ %s'), $taxonomy->labels->add_new_item);
                        ?>
                    </a>
                    <p id="<?php echo $tax_name; ?>-add" class="category-add wp-hidden-child">
                        <label class="screen-reader-text" for="new<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
                        <input type="text" name="new<?php echo $tax_name; ?>" id="new<?php echo $tax_name; ?>" class="form-required form-input-tip" value="<?php echo esc_attr($taxonomy->labels->new_item_name); ?>" aria-required="true" />
                        <label class="screen-reader-text" for="new<?php echo $tax_name; ?>_parent">
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
                        <input type="button" id="<?php echo $tax_name; ?>-add-submit" data-wp-lists="add:<?php echo $tax_name; ?>checklist:<?php echo $tax_name; ?>-add" class="button category-add-submit" value="<?php echo esc_attr($taxonomy->labels->add_new_item); ?>" />
                        <?php wp_nonce_field('add-' . $tax_name, '_ajax_nonce-add-' . $tax_name, false); ?>
                        <span id="<?php echo $tax_name; ?>-ajax-response"></span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
<?php
    }

    /**
     * Show menu shortcode in single edit menu.
     * 
     * @since 1.0.2
     */
    function food_menu_shortcode() {
        global $post;
        $menu_id = $post->ID;
        echo '<input type="text" value="[food_menu id=' . $menu_id . ']" readonly>';
    }
}
WPFM_ActionHooks::instance();
