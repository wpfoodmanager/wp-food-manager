<?php

/**
 * WPFM_Post_Types class.
 */
class WPFM_Post_Types {

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
	 * Constructor
	 */
	public function __construct() {
		 // wpfm custom post-types.
		 add_action('wp_footer', array($this, 'output_structured_data'));
		 add_action('init', array($this->post_types, 'register_post_types'), 0);
		 // View count action.
		 add_action('set_single_listing_view_count', array($this, 'set_single_listing_view_count'));
	}

	/**
	 * Register the custom post_types which is used in entire plugin.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function register_post_types() {
		if (post_type_exists("food_manager"))
			return;

		$admin_capability = 'manage_food_managers';
		$permalink_structure = WPFM_Post_Types::get_permalink_structure();
		WPFM_Custom_Taxonomies::register_post_taxonomies();
		/**
		 * Post types.
		 */
		$singular  = esc_html__('Food', 'wp-food-manager');
		$plural    = esc_html__('Foods', 'wp-food-manager');

		/**
		 * Set whether to add archive page support when registering the food manager post type.
		 *
		 * @param bool $enable_food_archive_page
		 * @since 1.0.0
		 */
		if (apply_filters('food_manager_enable_food_archive_page', current_theme_supports('food-manager-templates'))) {
			$has_archive = esc_html_x('foods', 'Post type archive slug - resave permalinks after changing this', 'wp-food-manager');
		} else {
			$has_archive = false;
		}

		$rewrite     = array(
			'slug'       => esc_attr($permalink_structure['food_rewrite_slug']),
			'with_front' => false,
			'feeds'      => true,
			'pages'      => false
		);

		register_post_type(
			"food_manager",
			apply_filters("register_post_type_food_manager", array(
				'labels' => array(
					'name'                     => $plural,
					'singular_name'         => $singular,
					'menu_name'             => esc_html__('Food Manager', 'wp-food-manager'),
					'all_items'             => sprintf(wp_kses('All %s', 'wp-food-manager'), $plural),
					'add_new'                 => esc_html__('Add Food', 'wp-food-manager'),
					'add_new_item'             => sprintf(wp_kses('Add %s', 'wp-food-manager'), $singular),
					'edit'                     => esc_html__('Edit', 'wp-food-manager'),
					'edit_item'             => sprintf(wp_kses('Edit %s', 'wp-food-manager'), $singular),
					'new_item'                 => sprintf(wp_kses('New %s', 'wp-food-manager'), $singular),
					'view'                     => sprintf(wp_kses('View %s', 'wp-food-manager'), $singular),
					'view_item'             => sprintf(wp_kses('View %s', 'wp-food-manager'), $singular),
					'search_items'             => sprintf(wp_kses('Search %s', 'wp-food-manager'), $plural),
					'not_found'             => sprintf(wp_kses('No %s found', 'wp-food-manager'), $plural),
					'not_found_in_trash'     => sprintf(wp_kses('No %s found in trash', 'wp-food-manager'), $plural),
					'parent'                 => sprintf(wp_kses('Parent %s', 'wp-food-manager'), $singular),
					'featured_image'        => esc_html__('Food Thumbnail', 'wp-food-manager'),
					'set_featured_image'    => esc_html__('Set food thumbnail', 'wp-food-manager'),
					'remove_featured_image' => esc_html__('Remove food thumbnail', 'wp-food-manager'),
					'use_featured_image'    => esc_html__('Use as food thumbnail', 'wp-food-manager'),
				),
				'description' => sprintf(wp_kses('This is where you can create and manage %s.', 'wp-food-manager'), $plural),
				'public'                 => true,
				'show_ui'                 => true,
				'capability_type'         => 'post',
				'map_meta_cap'          => true,
				'publicly_queryable'     => true,
				'exclude_from_search'     => false,
				'hierarchical'             => false,
				'rewrite'                 => $rewrite,
				'query_var'             => true,
				'show_in_rest'             => true,
				'supports'                 => array('title', 'editor', 'custom-fields', 'publicize', 'thumbnail'),
				'has_archive'             => $has_archive,
				'show_in_nav_menus'     => true,
				'menu_icon' => esc_attr(WPFM_PLUGIN_URL) . '/assets/images/wpfm-icon.png' // It's use to display food manager icon at admin site. 
			))
		);

		/**
		 * Feeds.
		 */
		add_feed('food_feed', array($this, 'food_feed'));

		/**
		 * Post types.
		 */
		$singular_menu  = esc_html__('Menu', 'wp-food-manager');
		$plural_menu    = esc_html__('Menus', 'wp-food-manager');

		$rewrite_menu     = array(
			'slug'       => 'food-menu',
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true
		);

		register_post_type(
			"food_manager_menu",
			apply_filters("register_post_type_food_manager_menu", array(
				'labels' => array(
					'name'                     => $plural_menu,
					'singular_name'         => $singular_menu,
					'menu_name'             => esc_html__('Food Menu', 'wp-food-manager'),
					'all_items'             => sprintf(esc_html__('%s', 'wp-food-manager'), $plural_menu),
					'add_new'                 => esc_html__('Add New', 'wp-food-manager'),
					'add_new_item'             => sprintf(esc_html__('Add %s', 'wp-food-manager'), $singular_menu),
					'edit'                     => esc_html__('Edit', 'wp-food-manager'),
					'edit_item'             => sprintf(esc_html__('Edit %s', 'wp-food-manager'), $singular_menu),
					'new_item'                 => sprintf(esc_html__('New %s', 'wp-food-manager'), $singular_menu),
					'view'                     => sprintf(esc_html__('View %s', 'wp-food-manager'), $singular_menu),
					'view_item'             => sprintf(esc_html__('View %s', 'wp-food-manager'), $singular_menu),
					'search_items'             => sprintf(esc_html__('Search %s', 'wp-food-manager'), $plural_menu),
					'not_found'             => sprintf(esc_html__('No %s found', 'wp-food-manager'), $plural_menu),
					'not_found_in_trash'     => sprintf(esc_html__('No %s found in trash', 'wp-food-manager'), $plural_menu),
					'parent'                 => sprintf(esc_html__('Parent %s', 'wp-food-manager'), $singular_menu),
					'featured_image'        => esc_html__('Food Menu Image', 'wp-food-manager'),
					'set_featured_image'    => esc_html__('Add Image', 'wp-food-manager'),
					'remove_featured_image' => esc_html__('Remove Image', 'wp-food-manager'),
					'use_featured_image'    => esc_html__('Use as food thumbnail', 'wp-food-manager'),
					'view_items'    => sprintf(esc_html__('View %s', 'wp-food-manager'), $plural_menu),
				),
				'description' => sprintf(esc_html__('This is where you can create and manage %s.', 'wp-food-manager'), $plural_menu),
				'public'                 => true,
				'show_ui'                 => true,
				'map_meta_cap'          => true,
				'publicly_queryable'     => true,
				'exclude_from_search'     => false,
				'hierarchical'             => false,
				'rewrite'                 => $rewrite_menu,
				'query_var'             => true,
				'show_in_rest'             => true,
				'supports'                 => array('title', 'thumbnail', 'publicize'),
				'has_archive'             => true,
				'show_in_menu' => 'edit.php?post_type=food_manager'
			))
		);

		/**
		 * Post status.
		 */
		register_post_status('preview', array(
			'public'                    => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop('Preview <span class="count">(%s)</span>', 'Preview <span class="count">(%s)</span>', 'wp-food-manager')
		));
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
	 * Display the food's feed.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function food_feed() {
		$query_args = array(
			'post_type'           => 'food_manager',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => isset($_GET['posts_per_page']) ? absint($_GET['posts_per_page']) : -1,
			'tax_query'           => array(),
			'meta_query'          => array()
		);

		if (!empty($_GET['search_food_types'])) {
			$cats     = explode(',', sanitize_text_field($_GET['search_food_types'])) + array(0);
			$field    = is_numeric($cats) ? 'term_id' : 'slug';
			$operator = 'all' === get_option('food_manager_food_type_filter_type', 'all') && sizeof($cats) > 1 ? 'AND' : 'IN';

			$query_args['tax_query'][] = array(
				'taxonomy'         => 'food_manager_type',
				'field'            => $field,
				'terms'            => $cats,
				'include_children' => $operator !== 'AND',
				'operator'         => $operator
			);
		}

		if (!empty($_GET['search_categories'])) {
			$cats     = explode(',', sanitize_text_field($_GET['search_categories'])) + array(0);
			$field    = is_numeric($cats) ? 'term_id' : 'slug';
			$operator = 'all' === get_option('food_manager_category_filter_type', 'all') && sizeof($cats) > 1 ? 'AND' : 'IN';

			$query_args['tax_query'][] = array(
				'taxonomy'         => 'food_manager_category',
				'field'            => $field,
				'terms'            => $cats,
				'include_children' => $operator !== 'AND',
				'operator'         => $operator
			);
		}

		if (isset($_GET['search_food_menu']) && !empty($_GET['search_food_menu'])) {
			$search_food_menu = explode(',', sanitize_text_field($_GET['search_food_menu']));
			$food_ids = [];
			foreach ($search_food_menu as $menu_id) {
				$food_item_ids = get_post_meta($menu_id, '_food_item_ids', true);
				if ($food_item_ids) {
					foreach ($food_item_ids as $food_item_id) {
						$food_ids[] = $food_item_id;
					}
				}
			}
			$query_args['post__in'] = $food_ids;
		}

		if ($food_manager_keyword = sanitize_text_field($_GET['search_keywords'])) {
			$query_args['s'] = $food_manager_keyword;
		}

		if (empty($query_args['meta_query'])) {
			unset($query_args['meta_query']);
		}

		if (empty($query_args['tax_query'])) {
			unset($query_args['tax_query']);
		}

		query_posts(apply_filters('food_feed_args', $query_args));
		add_action('rss2_ns', array($this, 'food_feed_namespace'));
		add_action('rss2_item', array($this, 'food_feed_item'));
		do_feed_rss2(false);
		remove_filter('posts_search', 'get_food_listings_keyword_search');
	}

	/**
	 * In order to make sure that the feed properly queries the 'food_listing' type.
	 *
	 * @access public
	 * @param WP_Query $wp
	 * @return void
	 * @since 1.0.0
	 */
	public function add_feed_query_args($wp) {
		// Let's leave if not the food feed.
		if (!isset($wp->query_vars['feed']) || 'food_feed' !== $wp->query_vars['feed']) {
			return;
		}

		// Leave if not a feed.
		if (false === $wp->is_feed) {
			return;
		}

		// If the post_type was already set, let's get out of here.
		if (isset($wp->query_vars['post_type']) && !empty($wp->query_vars['post_type'])) {
			return;
		}
		$wp->query_vars['post_type'] = 'food_manager';
	}

	/**
	 * Add a custom namespace to the food feed.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function food_feed_namespace() {
		echo 'xmlns:food_manager="' . esc_url(site_url()) . '"' . "\n";
	}

	/**
	 * Add custom data to the food feed.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function food_feed_item() {
		$post_id  = absint(get_the_ID());
		get_food_manager_template('rss-food-feed.php', array('post_id' => $post_id));
	}

	/**
	 * This function is used to set the count of food views.
	 * This function is also used in the Foods dashboard file.
	 *
	 * @access public
	 * @param int $post_id
	 * @return void
	 * @since 1.0.0
	 */
	public function set_post_views($post_id) {
		$count_key = '_view_count';
		$count = get_post_meta($post_id, $count_key, true);

		if ($count == '' || $count == null) {
			$count = 0;
			delete_post_meta($post_id, $count_key);
			add_post_meta($post_id, $count_key, '0');
		} else {
			$count++;
			update_post_meta($post_id, $count_key, $count);
		}
	}

	/**
	 * Generate location data if a post is updated.
	 *
	 * @access public
	 * @param int $meta_id
	 * @param int $object_id
	 * @param string $meta_key
	 * @param mixed $_meta_value
	 * @return void
	 * @since 1.0.0
	 */
	public function maybe_update_geolocation_data($meta_id, $object_id, $meta_key, $_meta_value) {
		if ('_food_location' !== $meta_key || 'food_manager' !== get_post_type($object_id)) {
			return;
		}

		do_action('food_manager_food_location_edited', $object_id, $_meta_value);
	}

	/**
	 * Maybe set menu_order if the featured status of a food is changed.
	 *
	 * @access public
	 * @param int $meta_id
	 * @param int $object_id
	 * @param string $meta_key
	 * @param mixed $_meta_value
	 * @return void
	 * @since 1.0.0
	 */
	public function maybe_update_menu_order($meta_id, $object_id, $meta_key, $_meta_value) {
		if ('food_manager' !== get_post_type($object_id)) {
			return;
		}

		global $wpdb;
		if ('1' == $_meta_value) {
			$wpdb->update($wpdb->posts, array('menu_order' => -1), array('ID' => $object_id));
		} else {
			$wpdb->update($wpdb->posts, array('menu_order' => 0), array('ID' => $object_id, 'menu_order' => -1));
		}

		clean_post_cache($object_id);
	}

	/**
	 * After importing via WP ALL Import, add default meta data.
	 *
	 * @access public
	 * @param  int $post_id
	 * @return void
	 * @since 1.0.0
	 */
	public function pmxi_saved_post($post_id) {
		if ('food_manager' === get_post_type($post_id)) {
			$actionhooks = WPFM_ActionHooks::instance();
			$actionhooks->maybe_add_default_meta_data(absint($post_id));
		}
	}

	/**
	 * When deleting a food, delete its attachments.
	 *
	 * @access public
	 * @param int $post_id
	 * @return void
	 * @since 1.0.0
	 */
	public function before_delete_food($post_id) {
		if ('food_manager' === get_post_type($post_id)) {
			$attachments = get_children(array(
				'post_parent' => $post_id,
				'post_type'   => 'attachment'
			));

			if ($attachments) {
				foreach ($attachments as $attachment) {
					wp_delete_attachment($attachment->ID);
					@unlink(get_attached_file($attachment->ID));
				}
			}
		}
	}

	/**
	 * Retrieves permalink settings.
	 *
	 * @access public
	 * @see https://github.com/woocommerce/woocommerce/blob/3.0.8/includes/wc-core-functions.php#L1573
	 * @return array $permalinks
	 * @since 1.0.0
	 */
	public static function get_permalink_structure() {
		// Switch to the site's default locale, bypassing the active user's locale.
		if (function_exists('switch_to_locale') && did_action('admin_init')) {
			switch_to_locale(get_locale());
		}

		$permalinks = wp_parse_args(
			(array) get_option('food_manager_permalinks', array()),
			array(
				'food_base'      => '',
				'category_base'  => '',
				'type_base'      => '',
				'topping_base'   => '',
			)
		);

		// Ensure rewrite slugs are set.
		$permalinks['food_rewrite_slug']      = untrailingslashit(empty($permalinks['food_base']) ? _x('food', 'Food permalink - resave permalinks after changing this', 'wp-food-manager') : $permalinks['food_base']);
		$permalinks['category_rewrite_slug'] = untrailingslashit(empty($permalinks['category_base']) ? _x('food-category', 'Food category slug - resave permalinks after changing this', 'wp-food-manager') : $permalinks['category_base']);
		$permalinks['type_rewrite_slug']     = untrailingslashit(empty($permalinks['type_base']) ? _x('food-type', 'Food type slug - resave permalinks after changing this', 'wp-food-manager') : $permalinks['type_base']);
		$permalinks['topping_rewrite_slug']  = untrailingslashit(empty($permalinks['topping_base']) ? _x('food-topping', 'Food topping slug - resave permalinks after changing this', 'wp-food-manager') : $permalinks['topping_base']);

		// Restore the original locale.
		if (function_exists('restore_current_locale') && did_action('admin_init')) {
			restore_current_locale();
		}

		return $permalinks;
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 *
	 * @access public
	 * @param  array $bulk_messages Array of messages.
	 * @param  array $bulk_counts Array of how many objects were updated.
	 * @return array $bulk_messages
	 * @since 1.0.0
	 */
	public function bulk_post_updated_messages($bulk_messages, $bulk_counts) {
		$bulk_messages['food_manager'] = array(
			/* translators: %s: product count */
			'updated'   => _n('%s food updated.', '%s foods updated.', $bulk_counts['updated'], 'wp-food-manager'),
			/* translators: %s: product count */
			'locked'    => _n('%s food not updated, somebody is editing it.', '%s foods not updated, somebody is editing them.', $bulk_counts['locked'], 'wp-food-manager'),
			/* translators: %s: product count */
			'deleted'   => _n('%s food permanently deleted.', '%s foods permanently deleted.', $bulk_counts['deleted'], 'wp-food-manager'),
			/* translators: %s: product count */
			'trashed'   => _n('%s food moved to the Trash.', '%s foods moved to the Trash.', $bulk_counts['trashed'], 'wp-food-manager'),
			/* translators: %s: product count */
			'untrashed' => _n('%s food restored from the Trash.', '%s foods restored from the Trash.', $bulk_counts['untrashed'], 'wp-food-manager'),
		);

		return $bulk_messages;
	}
}
