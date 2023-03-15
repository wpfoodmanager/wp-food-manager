<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * WPFM_FilterHooks class.
 */

class WPFM_FilterHooks {

    /**
     * The single instance of the class.
     *
     * @var self
     * @since 1.0.1
     */
    private static $_instance = null;

    /**
     * Allows for accessing single instance of class. Class should only be constructed once per call.
     *
     * @since 1.0.1
     * @static
     * @return self Main instance.
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
        // Writepanel's filters
        add_filter('manage_food_manager_menu_posts_columns', array($this, 'set_shortcode_copy_columns'));
        add_filter('manage_edit-food_manager_columns', array($this, 'columns'));
        add_filter('manage_food_manager_posts_columns', array($this, 'set_custom_food_columns'));
        add_filter('manage_edit-food_manager_sortable_columns', array($this, 'set_custom_food_sortable_columns'));
        add_filter('post_row_actions', array($this, 'row_actions'));

        // wpfm custom post-types
        add_filter('the_content', array($this, 'food_content'));
        add_filter('the_content', array($this, 'food_menu_content'));
        add_filter('archive_template', array($this, 'food_archive'), 20);
        add_filter('use_block_editor_for_post_type', array($this, 'wpfm_disable_gutenberg'), 10, 2);
        add_filter('wp_insert_post_data', array($this, 'fix_post_name'), 10, 2);

        // wpfm functions
        add_filter('upload_dir', array($this, 'wpfm_upload_dir'));
        add_filter('wp_terms_checklist_args', 'wpfm_term_radio_checklist_for_food_type');
        add_filter('manage_edit-food_manager_type_columns', array($this, 'wpfm_display_custom_taxonomy_image_column_heading_for_food_type'));
        add_filter('manage_edit-food_manager_category_columns', array($this, 'wpfm_display_custom_taxonomy_image_column_heading_for_food_category'));

        // wpfm core file
        add_filter('pre_option_wpfm_enable_categories', '__return_true');
        add_filter('pre_option_wpfm_enable_food_types', '__return_true');
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_page_food_manager_settings_link'));
    }

    /**
     * Create link on plugin page for food manager plugin settings
     * 
     * @since 1.0.0
     * @param array $links
     * @return array
     */
    function add_plugin_page_food_manager_settings_link($links) {
        $links[] = '<a href="' .
            admin_url('edit.php?post_type=food_manager&page=food-manager-settings') .
            '">' . __('Settings', 'wp-food-manager') . '</a>';
        return $links;
    }

    /**
     * Add new column heading
     *
     * @since 1.0.0
     * @param array $columns
     * @return array
     */
    function wpfm_display_custom_taxonomy_image_column_heading_for_food_category($columns) {
        $columns['category_image'] = __('Image', 'taxt-domain');
        return $columns;
    }

    /**
     * Add new column heading
     *
     * @since 1.0.0
     * @param array $columns
     * @return array
     */
    function wpfm_display_custom_taxonomy_image_column_heading_for_food_type($columns) {
        $columns['category_image'] = __('Image', 'taxt-domain');
        return $columns;
    }

    /**
     * Filters the upload dir when $food_manager_upload is true
     * 
     * @since 1.0.0
     * @param  array $pathdata
     * @return array
     */
    function wpfm_upload_dir($pathdata) {
        global $food_manager_upload, $food_manager_uploading_file;
        if (!empty($food_manager_upload)) {
            $dir = untrailingslashit(apply_filters('wpfm_upload_dir', 'wpfm-uploads/' . sanitize_key($food_manager_uploading_file), sanitize_key($food_manager_uploading_file)));
            if (empty($pathdata['subdir'])) {
                $pathdata['path']   = $pathdata['path'] . '/' . $dir;
                $pathdata['url']    = $pathdata['url'] . '/' . $dir;
                $pathdata['subdir'] = '/' . $dir;
            } else {
                $new_subdir         = '/' . $dir . $pathdata['subdir'];
                $pathdata['path']   = str_replace($pathdata['subdir'], $new_subdir, $pathdata['path']);
                $pathdata['url']    = str_replace($pathdata['subdir'], $new_subdir, $pathdata['url']);
                $pathdata['subdir'] = str_replace($pathdata['subdir'], $new_subdir, $pathdata['subdir']);
            }
        }
        return $pathdata;
    }

    /**
     * Fix post name when wp_update_post changes it
     * 
     * @since 1.0.0
     * @param  array $data
     * @return array $postarr
     */
    public function fix_post_name($data, $postarr) {
        if ('food_manager' === $data['post_type'] && 'pending' === $data['post_status'] && !current_user_can('publish_posts')) {
            $data['post_name'] = $postarr['post_name'];
        }
        return $data;
    }

    /**
     * wpfm_disable_gutenberg functions
     * 
     * @since 1.0.0
     * @param boolean $is_enabled
     * @param string $post_type
     */
    public function wpfm_disable_gutenberg($is_enabled, $post_type) {
        if (apply_filters('wpfm_disable_gutenberg', true) && $post_type === 'food_manager') return false;
        return $is_enabled;
    }

    /**
     * food_archive function.
     *
     * @since 1.0.0
     * @param string $template
     * @access public
     * @return void
     */
    public function food_archive($template) {
        if (is_tax('food_manager_category')) {
            $template = WPFM_PLUGIN_DIR . '/templates/content-food_listing_category.php';
        } elseif (is_tax('food_manager_type')) {
            $template = WPFM_PLUGIN_DIR . '/templates/content-food_listing_type.php';
        } elseif (is_tax('food_manager_tag')) {
            $template = WPFM_PLUGIN_DIR . '/templates/content-food_listing_tag.php';
        }
        return $template;
    }

    /**
     * Add Food menu content
     * 
     * @since 1.0.0
     * @param string $content
     */
    public function food_menu_content($content) {
        global $post;
        if (!is_singular('food_manager_menu') || !in_the_loop()) {
            return $content;
        }
        remove_filter('the_content', array($this, 'food_menu_content'));
        if ('food_manager_menu' === $post->post_type) {
            ob_start();
            do_action('food_menu_content_start');
            get_food_manager_template_part('content-single', 'food_manager_menu');
            do_action('food_menu_content_end');
            $content = ob_get_clean();
        }
        add_filter('the_content', array($this, 'food_menu_content'));
        return apply_filters('food_manager_single_food_menu_content', $content, $post);
    }

    /**
     * Add extra content when showing food content
     * 
     * @since 1.0.0
     * @param string $content
     */
    public function food_content($content) {
        global $post;
        if (!is_singular('food_manager') || !in_the_loop()) {
            return $content;
        }
        remove_filter('the_content', array($this, 'food_content'));
        if ('food_manager' === $post->post_type) {
            ob_start();
            do_action('food_content_start');
            get_food_manager_template_part('content-single', 'food_manager');
            do_action('food_content_end');
            $content = ob_get_clean();
        }
        add_filter('the_content', array($this, 'food_content'));
        return apply_filters('food_manager_single_food_content', $content, $post);
    }

    /**
     * Removes all action links because WordPress add it to primary column.
     * Note: Removing all actions also remove mobile "Show more details" toggle button.
     * So the button need to be added manually in custom_columns callback for primary column.
     *
     * @since 1.0.0
     * @access public
     * @param array $actions
     * @return array
     */
    public function row_actions($actions) {
        if ('food_manager' == get_post_type()) {
            return array();
        }
        return $actions;
    }

    /**
     * column Sortable
     *
     * @since 1.0.1
     */
    public function set_custom_food_sortable_columns($columns) {
        $columns['food_menu_order'] = 'menu_order';
        $columns['food_title'] = 'food_title';
        return  $columns;
    }

    /**
     * Custom columns
     *
     * @since 1.0.0
     */
    public function set_custom_food_columns($columns) {
        $custom_col_order = array(
            'cb' => $columns['cb'],
            'food_title' => $columns['title'],
            'food_banner' => __('Image', 'wp-food-manager'),
            'fm_stock_status' => __('Stock Status', 'wp-food-manager'),
            'fm-price' => __('Price', 'wp-food-manager'),
            'fm_categories' => __('Categories', 'wp-food-manager'),
            'food_menu_order' => __('Order', 'wp-food-manager'),
            'food_status' => __('Status', 'wp-food-manager'),
            'date' => $columns['date'],
            'food_actions' => __('Actions', 'wp-food-manager')
        );
        return $custom_col_order;
    }

    /**
     * Set Copy Shortcode
     *
     * @since 1.0.1
     */
    public function set_shortcode_copy_columns($columns) {
        $columns['shortcode'] = __('Shortcode', 'wp-food-manager');
        return  $columns;
    }

    /**
     * columns function.
     *
     * @since 1.0.0
     * @param array $columns
     * @return array
     */
    public function columns($columns) {
        if (!is_array($columns)) {
            $columns = array();
        }
        unset($columns['title'], $columns['date'], $columns['author']);
        $columns['food_title'] = __('Title', 'wp-food-manager');
        $columns['food_banner'] = '<span class="dashicons dashicons-format-image">' . __('Banner', 'wp-food-manager') . '</span>';
        $columns['fm_stock_status'] = __('Stock Status', 'wp-food-manager');
        $columns['fm_categories'] = __('Categories', 'wp-food-manager');
        $columns['food_menu_order'] = __('Order', 'wp-food-manager');
        $columns['food_status'] = __('Status', 'wp-food-manager');
        $columns['food_actions'] = __('Actions', 'wp-food-manager');
        if (!get_option('food_manager_enable_food_types')) {
            unset($columns['food_manager_type']);
        }
        return $columns;
    }
}

WPFM_FilterHooks::instance();
