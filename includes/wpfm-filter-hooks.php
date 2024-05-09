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
        // wpfm custom post-types.
        add_filter('the_content', array($this, 'food_content'));
        add_filter('the_content', array($this, 'food_menu_content'));
        add_filter('archive_template', array($this, 'food_archive'), 20);
        add_filter('wp_insert_post_data', array($this, 'fix_post_name'), 10, 2);

        // wpfm functions.
        add_filter('upload_dir', array($this, 'upload_dir'));
        add_filter('manage_edit-food_manager_type_columns', array($this, 'display_custom_taxonomy_image_column_heading_for_food_type'));
        add_filter('manage_edit-food_manager_category_columns', array($this, 'display_custom_taxonomy_image_column_heading_for_food_category'));

        // wpfm core file.
        add_filter('pre_option_wpfm_enable_categories', '__return_true');
        add_filter('pre_option_wpfm_enable_food_types', '__return_true');        
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
}

WPFM_FilterHooks::instance();
