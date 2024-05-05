<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly.

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
        // Writepanel's filters.
        add_filter('manage_food_manager_menu_posts_columns', array($this, 'set_shortcode_copy_columns'));
        add_filter('manage_edit-food_manager_columns', array($this, 'columns'));
        add_filter('manage_food_manager_posts_columns', array($this, 'set_custom_food_columns'));
        add_filter('manage_edit-food_manager_sortable_columns', array($this, 'set_custom_food_sortable_columns'));
        add_filter('post_row_actions', array($this, 'row_actions'));

        // wpfm custom post-types.
        add_filter('the_content', array($this, 'food_content'));
        add_filter('the_content', array($this, 'food_menu_content'));
        add_filter('archive_template', array($this, 'food_archive'), 20);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg'), 10, 2);
        add_filter('wp_insert_post_data', array($this, 'fix_post_name'), 10, 2);

        // wpfm functions.
        add_filter('upload_dir', array($this, 'upload_dir'));
        add_filter('wp_terms_checklist_args', 'wpfm_term_radio_checklist_for_food_type', 10, 2);
        add_filter('manage_edit-food_manager_type_columns', array($this, 'display_custom_taxonomy_image_column_heading_for_food_type'));
        add_filter('manage_edit-food_manager_category_columns', array($this, 'display_custom_taxonomy_image_column_heading_for_food_category'));

        // wpfm core file.
        add_filter('pre_option_wpfm_enable_categories', '__return_true');
        add_filter('pre_option_wpfm_enable_food_types', '__return_true');
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_page_food_manager_settings_link'));
        
        add_filter('wpfm_term_radio_checklist_taxonomy', array($this, 'wpfm_food_manager_taxonomy'), 10, 2);
        add_filter('wpfm_term_radio_checklist_post_type', array($this, 'wpfm_food_manager_post_type'));
        
    }

    /**
     * Create link on plugin page for food manager plugin settings.
     * 
     * @access public
     * @param array $links
     * @return array
     * @since 1.0.0
     */
    function add_plugin_page_food_manager_settings_link($links) {
        $links[] = '<a href="' .
            esc_url(admin_url('edit.php?post_type=food_manager&page=food-manager-settings')) .
            '">' . esc_html__('Settings', 'wp-food-manager') . '</a>';
        return $links;
    }

    /**
     * Add new column heading.
     *
     * @access public
     * @param array $columns
     * @return array
     * @since 1.0.0
     */
    function display_custom_taxonomy_image_column_heading_for_food_category($columns) {
        $columns['category_image'] = esc_html__('Image', 'taxt-domain');
        return $columns;
    }

    /**
     * Add new column heading.
     *
     * @access public
     * @param array $columns
     * @return array
     * @since 1.0.0
     */
    function display_custom_taxonomy_image_column_heading_for_food_type($columns) {
        $columns['category_image'] = esc_html__('Image', 'taxt-domain');
        return $columns;
    }

    /**
     * Filters the upload dir when $food_manager_upload is true.
     * 
     * @access public
     * @param  array $pathdata
     * @return array
     * @since 1.0.0
     */
    function upload_dir($pathdata) {
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
     * Fix post name when wp_update_post changes it.
     * 
     * @access public
     * @param  array $data
     * @return array $postarr
     * @since 1.0.0
     */
    public function fix_post_name($data, $postarr) {
        if ('food_manager' === $data['post_type'] && 'pending' === $data['post_status'] && !current_user_can('publish_posts')) {
            $data['post_name'] = sanitize_title($postarr['post_name']);
        }
        return $data;
    }

    /**
     * This function disables the Gutenberg editor.
     * 
     * @access public
     * @param boolean $is_enabled
     * @param string $post_type
     * @return bool $is_enabled
     * @since 1.0.0
     */
    public function disable_gutenberg($is_enabled, $post_type) {
        if (apply_filters('wpfm_disable_gutenberg', true) && $post_type === 'food_manager') return false;
        return $is_enabled;
    }

    /**
     * Return the Food archive template.
     *
     * @access public
     * @param string $template
     * @return void
     * @since 1.0.0
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
     * Add Food menu content.
     *
     * @access public
     * @param string $content
     * @return mixed
     * @since 1.0.0
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
     * Add extra content when showing food content.
     *
     * @access public
     * @param string $content
     * @return mixed
     * @since 1.0.0
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
     * @access public
     * @param array $actions
     * @return array
     * @since 1.0.0
     */
    public function row_actions($actions) {
        if ('food_manager' == get_post_type()) {
            return array();
        }
        return $actions;
    }

    /**
     * column Sortable.
     *
     * @access public
     * @param array $columns
     * @return array
     * @since 1.0.1
     */
    public function set_custom_food_sortable_columns($columns) {
        $columns['food_menu_order'] = 'menu_order';
        $columns['food_title'] = 'food_title';
        return $columns;
    }

    /**
     * Custom columns.
     *
     * @access public
     * @param array $columns
     * @return array
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
     * Set Copy Shortcode.
     *
     * @access public
     * @param array $columns
     * @return array
     * @since 1.0.1
     */
    public function set_shortcode_copy_columns($columns) {
        $columns['shortcode'] = __('Shortcode', 'wp-food-manager');
        $columns['thumbnail'] = __('Images', 'wp-food-manager');
        return $columns;
    }

    /**
     * columns function.
     *
     * @access public
     * @param array $columns
     * @return array
     * @since 1.0.0
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
    
    /**
     * Customizes the taxonomy used in wpfm_term_radio_checklist_for_taxonomy function.
     *
     * @param string $taxonomy The taxonomy name.
     * @param array  $args An array of arguments for the function.
     * @return string The modified taxonomy name.
     */
    public function wpfm_food_manager_taxonomy($taxonomy, $args) {
        if(get_post_type($args) == 'food_manager'){
            $taxonomy = 'food_manager_type';
        }
        return $taxonomy;
    }
    
    /**
     * Customizes the post type used in wpfm_term_radio_checklist_for_posttype function.
     *
     * @param string $post_type The post type name.
     * @return string The modified taxonomy name.
     */
    public function wpfm_food_manager_post_type($post_type){
        if(get_post_type() == 'food_manager'){
            $post_type = 'food_manager';
        }
        return $post_type;
    
    }

}

WPFM_FilterHooks::instance();
