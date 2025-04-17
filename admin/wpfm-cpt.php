<?php

/**
 * This file use to cretae fields of wp food manager at admin side.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class WPFM_CPT {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @static
	 * @return self Main instance.
	 * @since 1.0.0
	 */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
        add_action('admin_init', array($this, 'approve_food'));
        add_action('load-edit.php', array($this, 'do_bulk_actions'));
        add_action('admin_footer-edit.php', array($this, 'add_bulk_actions'));
        add_action('manage_food_manager_menu_posts_custom_column', array($this, 'shortcode_copy_content_column'), 10, 2);
        add_action('manage_food_manager_posts_custom_column', array($this, 'custom_food_content_column'), 10, 2);
        add_filter('manage_food_manager_menu_posts_columns', array($this, 'set_shortcode_copy_columns'));
        add_filter('manage_edit-food_manager_columns', array($this, 'columns'));
        add_filter('manage_food_manager_posts_columns', array($this, 'set_custom_food_columns'));
        add_filter('manage_edit-food_manager_sortable_columns', array($this, 'set_custom_food_sortable_columns'));
        add_filter('post_row_actions', array($this, 'row_actions'));
        add_filter('wp_terms_checklist_args', 'wpfm_term_radio_checklist_for_food_type', 10, 2);
        
	}
	
	/**
     * Approve a single food.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function approve_food() {
      if (
        !empty($_GET['approve_food']) && isset($_REQUEST['_wpnonce']) && wp_verify_nonce(wp_unslash($_REQUEST['_wpnonce']), 'approve_food') && current_user_can('publish_post', (int) $_GET['approve_food'])) {
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
                    jQuery('<option>').val('approve_food').text('<?php 
                        // translators: %s: food manager name
                        printf(esc_html__('Approve %s', 'wp-food-manager'), esc_html($wp_post_types['food_manager']->labels->name));
                    ?>').appendTo("select[name='action']");
    
                    jQuery('<option>').val('approve_food').text('<?php 
                        // translators: %s: food manager name
                        printf(esc_html__('Approve %s', 'wp-food-manager'), esc_html($wp_post_types['food_manager']->labels->name)); 
                    ?>').appendTo("select[name='action2']");
                });
            </script>
        <?php
        }
    }
    

    /**
     * Do custom bulk actions.
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
                if (isset($_GET['post']) && is_array($_GET['post'])) {
                    $post_ids = array_map('absint', array_filter(wp_unslash($_GET['post'])));
                    $published_foods = array();
        
                    if (!empty($post_ids)) {
                        foreach ($post_ids as $post_id) {
                            $food_data = array(
                                'ID'          => $post_id,
                                'post_status' => 'publish',
                            );
        
                            if (in_array(get_post_status($post_id), array('pending', 'pending_payment')) &&current_user_can('publish_post', $post_id) &&wp_update_post($food_data)) {
                                $published_foods[] = $post_id;
                            }
                        }
                    }
                    wp_redirect(add_query_arg('published_foods', $published_foods, remove_query_arg(array('published_foods'), admin_url('edit.php?post_type=food_manager'))));
                    exit;
                }
        }
        return;
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
                echo '<a href="' . esc_url(admin_url('post.php?post=' . esc_attr($post->ID) . '&action=edit')) . '" class="wpfm-tooltip food_title" wpfm-data-tip="' . esc_attr(sprintf(wp_kses('ID: %d', 'wp-food-manager'), $post->ID)) . '">' . esc_html($post->post_title) . '</a>';
                echo '</div>';
                echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__('Show more details', 'wp-food-manager') . '</span></button>';
                break;

            case 'food_banner':
                echo '<div class="food_banner">';
                display_food_banner();
                echo '</div>';
                display_food_veg_nonveg_icon_tag();
                break;

            case 'food-price':
                display_food_price_tag();
                break;

            case 'food_categories':
               echo esc_html(display_food_category()); 
                break;

            case 'food_stock_status':
                echo esc_html(display_stock_status());
                break;

            case 'food_menu_order':
                echo esc_html($thispost->menu_order);
                break;

            case 'food_status':
                echo esc_html(ucfirst($thispost->post_status));
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
     * content for Copy Shortcode.
     *
     * @access public
     * @param string $column
     * @param int $post_id
     * @return void
     * @since 1.0.1
     */
    public function shortcode_copy_content_column($column, $post_id) {
        switch ($column) {
            case 'shortcode':
                echo '<code>';
                // translators: %d: food menu ID
                printf(esc_html__('[food_menu id=%d]', 'wp-food-manager'), esc_attr($post_id));
                echo '</code>';
                break;
            case 'thumbnail':
                if (has_post_thumbnail()) {
                    echo get_the_post_thumbnail($post_id, 'thumbnail'); 
                } else {
                    echo '-';
                }
                break;
            case 'qr_code':
                display_menu_qr_code();
                break;
            default:
                break;
        }
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
        $columns['thumbnail'] = __('Food menu image', 'wp-food-manager');
        $columns['qr_code'] = __('QR Code', 'wp-food-manager');
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
        $columns['food_stock_status'] = __('Stock Status', 'wp-food-manager');
        $columns['food_categories'] = __('Categories', 'wp-food-manager');
        $columns['food_menu_order'] = __('Order', 'wp-food-manager');
        $columns['food_status'] = __('Status', 'wp-food-manager');
        $columns['food_actions'] = __('Actions', 'wp-food-manager');
        if (!get_option('food_manager_enable_food_types')) {
            unset($columns['food_manager_type']);
        }

        return $columns;
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
            'food_stock_status' => __('Stock Status', 'wp-food-manager'),
            'food-price' => __('Price', 'wp-food-manager'),
            'food_categories' => __('Categories', 'wp-food-manager'),
            'food_menu_order' => __('Order', 'wp-food-manager'),
            'food_status' => __('Status', 'wp-food-manager'),
            'date' => $columns['date'],
            'food_actions' => __('Actions', 'wp-food-manager')
        );

        return $custom_col_order;
    }
}

WPFM_CPT::instance();
