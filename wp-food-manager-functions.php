<?php

if (!function_exists('get_food_listings')) :
	/**
	 * Queries food listings with certain criteria and returns them.
	 * 
	 * @access public
	 * @param array $args (default: array())
	 * @return WP_Query
	 * @since 1.0.0
	 */
	function get_food_listings($args = array()) {
		global $food_manager_keyword;

		$args = wp_parse_args($args, array(
			'search_keywords'   => '',
			'search_categories' => array(),
			'search_food_types' => array(),
			'search_food_menu' => array(),
			'offset'            => 0,
			'posts_per_page'    => 15,
			'orderby'           => 'date',
			'order'             => 'DESC',
			'featured'          => null,
			'cancelled'         => null,
			'fields'            => 'all',
			'post_status'       => array(),
		));

		/**
		 * Perform actions that need to be done prior to the start of the food listings query.
		 *
		 * @param array $args Arguments used to retrieve food listings.
		 * @since 1.0.0
		 */
		do_action('get_food_listings_init', $args);

		$query_args = array(
			'post_type'              => 'food_manager',
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => 1,
			'offset'                 => absint($args['offset']),
			'posts_per_page'         => intval($args['posts_per_page']),
			'orderby'                => $args['orderby'],
			'order'                  => $args['order'],
			'tax_query'              => array(),
			'meta_query'             => array(),
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'fields'                 => $args['fields']
		);

		if ($args['posts_per_page'] < 0) {
			$query_args['no_found_rows'] = true;
		}

		if (!empty($args['search_categories'][0])) {
			$field    = is_numeric($args['search_categories'][0]) ? 'term_id' : 'slug';
			$operator = 'all' === get_option('food_manager_category_filter_type', 'all') && sizeof($args['search_categories']) > 1 ? 'AND' : 'IN';
			$query_args['tax_query'][] = array(
				'taxonomy'         => 'food_manager_category',
				'field'            => $field,
				'terms'            => array_values($args['search_categories']),
				'include_children' => 'AND' !== $operator,
				'operator'         => $operator
			);
		}

		if (!empty($args['search_food_types'][0])) {
			$field    = is_numeric($args['search_food_types'][0]) ? 'term_id' : 'slug';
			$operator = 'all' === get_option('food_manager_food_type_filter_type', 'all') && sizeof($args['search_food_types']) > 1 ? 'AND' : 'IN';
			$tax_food_type_args['relation'] = 'OR';
			$search_food_types = array_values($args['search_food_types']);
			foreach ($search_food_types as $search_food_type) {
				$tax_food_type_args[] = array(
					'taxonomy'         => 'food_manager_type',
					'field'            => $field,
					'terms'            => $search_food_type,
					'include_children' => $operator !== 'AND',
					'operator'         => $operator
				);
			}
			$query_args['tax_query'][] = $tax_food_type_args;
		}

		if (!empty($args['search_food_menu'])) {
			$food_ids = [];
			foreach ($args['search_food_menu'] as $menu_id) {
				$food_item_ids = get_menu_list($menu_id,get_the_ID());
				if ($food_item_ids) {
					foreach ($food_item_ids as $food_item_id) {
						$food_ids[] = absint($food_item_id);
					}
				}
			}
			$query_args['post__in'] = $food_ids;
		}

		if (!empty($args['search_tags'][0])) {
			$field    = is_numeric($args['search_tags'][0]) ? 'term_id' : 'slug';
			$operator = 'all' === get_option('food_manager_food_type_filter_type', 'all') && sizeof($args['search_tags']) > 1 ? 'AND' : 'IN';
			$query_args['tax_query'][] = array(
				'taxonomy'         => 'food_listing_tag',
				'field'            => $field,
				'terms'            => array_values($args['search_tags']),
				'include_children' => $operator !== 'AND',
				'operator'         => $operator
			);
		}

		if ('featured' === $args['orderby']) {
			$query_args['orderby'] = array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			);
		}

		if ('rand_featured' === $args['orderby']) {
			$query_args['orderby'] = array(
				'menu_order' => 'ASC',
				'rand'       => 'ASC',
			);
		}

		if ('food_start_date' === $args['orderby']) {
			$query_args['orderby'] = 'meta_value';
			$query_args['meta_key'] = '_food_start_date';
			$query_args['meta_type'] = 'DATE';
		}

		$food_manager_keyword = sanitize_text_field($args['search_keywords']);
		if (!empty($food_manager_keyword) && strlen($food_manager_keyword) >= apply_filters('food_manager_get_listings_keyword_length_threshold', 2)) {
			$query_args['s'] = $food_manager_keyword;
			add_filter('posts_search', 'get_food_listings_keyword_search');
		}

		$query_args = apply_filters('food_manager_get_listings', $query_args, $args);
		if (empty($query_args['meta_query'])) {
			unset($query_args['meta_query']);
		}

		if (empty($query_args['tax_query'])) {
			unset($query_args['tax_query']);
		}

		//Sets the Polylang LANG arg to the current language.
		if(function_exists('pll_current_language') && !empty($args['lang'])) {
			$query_args['lang'] = $args['lang'];
		}

		/* This filter is documented in wp-food-manager.php */
		$query_args['lang'] = apply_filters('wpfm_lang', null);

		// Filter args.
		$query_args = apply_filters('wpfm_get_food_listings_query_args', $query_args, $args);
		do_action('before_get_food_listings', $query_args, $args);

		// Cache results.
		if (apply_filters('get_food_listings_cache_results', false)) {
			$to_hash              = wp_json_encode($query_args);
			$query_args_hash      = 'wpfm_' . md5($to_hash . WPFM_VERSION) . WPFM_Cache_Helper::get_transient_version('get_food_listings');
			$result               = false;
			$cached_query_results = true;
			$cached_query_posts   = get_transient($query_args_hash);

			if (is_string($cached_query_posts)) {
				$cached_query_posts = json_decode($cached_query_posts, false);
				if ($cached_query_posts && is_object($cached_query_posts) && isset($cached_query_posts->max_num_pages) && isset($cached_query_posts->found_posts) && isset($cached_query_posts->posts) && is_array($cached_query_posts->posts)) {
					$posts  = array_map('get_post', $cached_query_posts->posts);
					$result = new WP_Query();
					$result->parse_query($query_args);
					$result->posts         = $posts;
					$result->found_posts   = intval($cached_query_posts->found_posts);
					$result->max_num_pages = intval($cached_query_posts->max_num_pages);
					$result->post_count    = count($posts);
				}
			}

			if (false === $result) {
				$result               			   = new WP_Query($query_args);
				$cached_query_results 			   = false;
				$cacheable_result                  = array();
				$cacheable_result['posts']         = array_values($result->posts);
				$cacheable_result['found_posts']   = $result->found_posts;
				$cacheable_result['max_num_pages'] = $result->max_num_pages;
			}

			if ($cached_query_results) {
				if ('rand_featured' === $args['orderby']) {
					usort($result->posts, '_wpfm_shuffle_featured_post_results_helper');
				} elseif ('rand' === $args['orderby']) {
					shuffle($result->posts);
				}
			}
		} else {
			$result = new WP_Query(apply_filters('get_food_listings_query_args',$query_args));
		}

		// Generate hash
		$to_hash  = json_encode($query_args) . apply_filters('wpml_current_language', '');
		$result = apply_filters('get_food_listings_result_args', $result, $query_args);
		do_action('after_get_food_listings', $query_args, $args);
		remove_filter('posts_search', 'get_food_listings_keyword_search');

		return $result;
	}
endif;

/**
 * True if an the user can post a food. If accounts are required, and reg is enabled, users can post (they signup at the same time).
 *
 * @return bool
 * @since 1.0.0
 */
function wpfm_user_can_post_food() {
	$can_post = true;
	if (!is_user_logged_in()) {
		if (food_manager_user_requires_account() && !food_manager_enable_registration()) {
			$can_post = false;
		}
	}
	return apply_filters('wpfm_user_can_post_food', $can_post);
}

if (!function_exists('wpfm_notify_new_user')) :
	/**
	 * This wpfm_notify_new_user() function used to send notification to the new users.
	 *
	 * @param int $user_id
	 * @param string $password
	 * @since 1.0.0
	 */
	function wpfm_notify_new_user($user_id, $password) {
		global $wp_version;
		if (version_compare($wp_version, '4.3.1', '<')) {
			wp_new_user_notification($user_id, $password);
		} else {
			$notify = 'admin';
			if (empty($password)) {
				$notify = 'both';
			}
			wp_new_user_notification($user_id, null, $notify);
		}
	}
endif;

if (!function_exists('wpfm_create_account')) :
	/**
	 * This wpfm_create_account() function is used to create the account.
	 *
	 * @param  array $args containing username, email, role
	 * @param  string $deprecated role string
	 * @return WP_error | bool was an account created?
	 * @since 1.0.0
	 */
	function wpfm_create_account($args, $deprecated = '') {
		global $current_user;

		// Soft Deprecated in 1.0
		if (!is_array($args)) {
			$args = array(
				'username' => '',
				'password' => false,
				'email'    => $args,
				'role'     => $deprecated,
			);
		} else {
			$defaults = array(
				'username' => '',
				'email'    => '',
				'password' => false,
				'role'     => get_option('default_role')
			);
			$args = wp_parse_args($args, $defaults);
			extract($args);
		}

		$username = sanitize_user($args['username'], true);
		$email    = apply_filters('user_registration_email', sanitize_email($args['email']));

		if (empty($email)) {
			return new WP_Error('validation-error', __('Invalid email address.', 'wp-food-manager'));
		}

		if (empty($username)) {
			$username = sanitize_user(current(explode('@', $email)));
		}

		if (!is_email($email)) {
			return new WP_Error('validation-error', __('Your email address isn&#8217;t correct.', 'wp-food-manager'));
		}

		if (email_exists($email)) {
			return new WP_Error('validation-error', __('This email is already registered, please choose another one.', 'wp-food-manager'));
		}

		// Ensure username is unique.
		$append     = 1;
		$o_username = $username;
		while (username_exists($username)) {
			$username = $o_username . $append;
			$append++;
		}

		// Final error checking.
		$reg_errors = new WP_Error();
		$reg_errors = apply_filters('food_manager_registration_errors', $reg_errors, $username, $email);
		do_action('food_manager_register_post', $username, $email, $reg_errors);
		if ($reg_errors->get_error_code()) {
			return $reg_errors;
		}

		// Create account.
		$new_user = array(
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $email,
			'role'       => $role
		);

		// User is forced to set up account with email sent to them. This password will remain a secret.
		if (empty($new_user['user_pass'])) {
			$new_user['user_pass'] = wp_generate_password();
		}

		$user_id = wp_insert_user(apply_filters('food_manager_create_account_data', $new_user));
		if (is_wp_error($user_id)) {
			return $user_id;
		}

		/**
		 * Send notification to new users.
		 *
		 * @param  int         $user_id
		 * @param  string|bool $password
		 * @param  array       $new_user {
		 * Information about the new user.
		 *
		 * @type string $user_login Username for the user.
		 * @type string $user_pass  Password for the user (may be blank).
		 * @type string $user_email Email for the new user account.
		 * @type string $role New user's role.
		 * }
		 * @since 1.0.0
		 */
		do_action('food_manager_notify_new_user', $user_id, $password, $new_user);
		if (!is_user_logged_in()) {
			wp_set_auth_cookie($user_id, true, is_ssl());
			$current_user = get_user_by('id', $user_id);
		}

		return true;
	}
endif;

/**
 * True if an the user can edit a food.
 *
 * @return bool
 * @param int $food_id
 * @since 1.0.0
 */
function food_manager_user_can_edit_food($food_id) {
	$can_edit = true;
	if (!is_user_logged_in() || !$food_id) {
		$can_edit = false;
	} else {
		$food      = get_post($food_id);
		if (!$food || (absint($food->post_author) !== get_current_user_id() && !current_user_can('edit_post', $food_id))) {
			$can_edit = false;
		}
	}
	return apply_filters('food_manager_user_can_edit_food', $can_edit, $food_id);
}

/**
 * True if registration is enabled.
 *
 * @return bool
 * @since 1.0.0
 */
function food_manager_enable_registration() {
	return apply_filters('food_manager_enable_registration', get_option('food_manager_enable_registration') == 1 ? true : false);
}

/**
 * True if usernames are generated from email addresses.
 *
 * @return bool
 * @since 1.0.0
 */
function food_manager_generate_username_from_email() {
	return apply_filters('food_manager_generate_username_from_email', get_option('food_manager_generate_username_from_email') == 1 ? true : false);
}

/**
 * True if an account is required to post a food.
 *
 * @return bool
 * @since 1.0.0
 */
function food_manager_user_requires_account() {
	return apply_filters('food_manager_user_requires_account', get_option('food_manager_user_requires_account') == 1 ? true : false);
}

/**
 * True if users are allowed to edit submissions that are pending approval.
 *
 * @return bool
 * @since 1.0.0
 */
function food_manager_user_can_edit_pending_submissions() {
	return apply_filters('food_manager_user_can_edit_pending_submissions', get_option('food_manager_user_can_edit_pending_submissions') == 1 ? true : false);
}

/**
 * Checks if the user can upload a file via the Ajax endpoint.
 * @param bool $can_upload True if they can upload files from Ajax endpoint.
 * @return bool
 * @since 1.0.0
 */
function wpfm_user_can_upload_file_via_ajax() {
	$can_upload = is_user_logged_in() && wpfm_user_can_post_food();

	//  Override ability of a user to upload a file via Ajax.
	 
	return apply_filters('wpfm_user_can_upload_file_via_ajax', $can_upload);
}

/**
 * Based on wp_dropdown_categories, with the exception of supporting multiple selected categories, food types.
 * 
 * @see wp_dropdown_categories
 * @param int $args (default: '')
 * @since 1.0.0
 */
function food_manager_dropdown_selection($args = '') {
	$defaults = array(
		'orderby'         => 'id',
		'order'           => 'ASC',
		'show_count'      => 0,
		'hide_empty'      => 1,
		'child_of'        => 0,
		'exclude'         => '',
		'echo'            => 1,
		'selected'        => 0,
		'hierarchical'    => 0,
		'name'            => 'cat',
		'id'              => '',
		'class'           => 'food-manager-category-dropdown ' . (is_rtl() ? 'chosen-rtl' : ''),
		'depth'           => 0,
		'taxonomy'        => 'food_manager_category',
		'value'           => 'id',
		'multiple'        => true,
		'show_option_all' => false,
		'placeholder'     => __('Choose a Food category&hellip;', 'wp-food-manager'),
		'no_results_text' => __('No results match', 'wp-food-manager'),
		'multiple_text'   => __('Select Some Options', 'wp-food-manager')
	);

	$food_dropdown = wp_parse_args($args, $defaults);
	if (!isset($food_dropdown['pad_counts']) && $food_dropdown['show_count'] && $food_dropdown['hierarchical']) {
		$food_dropdown['pad_counts'] = true;
	}
	extract($food_dropdown);

	// Store in a transient to help sites with many cats.
	if (empty($categories)) {
		$categories = get_terms($taxonomy, array(
			'orderby'         => $food_dropdown['orderby'],
			'order'           => $food_dropdown['order'],
			'hide_empty'      => $food_dropdown['hide_empty'],
			'child_of'        => $food_dropdown['child_of'],
			'exclude'         => $food_dropdown['exclude'],
			'hierarchical'    => $food_dropdown['hierarchical']
		));
	}

	$name       = esc_attr($name);
	$class      = esc_attr($class);
	$id = $food_dropdown['id'] ? $food_dropdown['id'] : $food_dropdown['name'];
	$args['name_attr'] = isset($args['name_attr']) ? $args['name_attr'] : true;
	$data_taxonomy = '';

	if ($taxonomy) {
		$data_taxonomy = 'data-taxonomy="' . $taxonomy . '"';
	}

	if ($taxonomy == 'food_manager_category') {
		$multiple_text = __('Choose a food Category&hellip;', 'wp-food-manager');
	} else if ($taxonomy == 'food_manager_type') {
		$multiple_text = __('Choose a Food Type&hellip;', 'wp-food-manager');
	} else if ($taxonomy == 'food_manager_ingredient') {
		$multiple_text = __('Choose a food Ingredients&hellip;', 'wp-food-manager');
	} else if ($taxonomy == 'food_manager_nutrition') {
		$multiple_text = __('Choose a food Nutritions&hellip;', 'wp-food-manager');
	}

	if ($taxonomy == 'food_manager_type') :
		$placeholder = __('Choose a Food type&hellip;', 'wp-food-manager');
	endif;

	$item_cat_ids = get_post_meta(get_the_ID(), '_food_item_cat_ids', true);
	$name_attr = ($args['name_attr'] == true) ? 'name="' . esc_attr($name) . '[]"' : '';
	$output = '<select ' . $data_taxonomy . ' ' . $name_attr . '  id="' . esc_attr($id) . '" class="' . esc_attr($class) . '" ' . ($multiple ? 'multiple="multiple"' : "") . ' data-placeholder="' . esc_attr($placeholder) . '" data-no_results_text="' . esc_attr($no_results_text) . '" data-multiple_text="' . esc_attr($multiple_text) . '">\n';


	if ($show_option_all) {
		$output .= '<option value="">' . esc_html($show_option_all) . '</option>';
	}

	if (!empty($categories)) {
		include_once(WPFM_PLUGIN_DIR . '/includes/wpfm-category-walker.php');
		$walker = WPFM_Category_Walker::instance();
		if ($hierarchical) {
			$depth = $food_dropdown['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}
		$output .= $walker->walk($categories, $depth, $food_dropdown);
	}

	$output .= "</select>\n";
	if ($echo) {
		echo $output;
	}

	return $output;
}

/**
 * This has_wpfm_shortcode() function is used to checks if the provided content or the current single page or post has a WPFM shortcode.
 *
 * @param string|null       $content   Content to check. If not provided, it uses the current post content.
 * @param string|array|null $tag Check specifically for one or more shortcodes. If not provided, checks for any WPJM shortcode.
 * @param string[] $has_wpfm_shortcode.
 * @param bool $has_wpfm_shortcode
 * @return bool
 * @since 1.0.0
 */
function has_wpfm_shortcode($content = null, $tag = null) {
	global $post;

	$has_wpfm_shortcode = false;
	if (null === $content && is_singular() && is_a($post, 'WP_Post')) {
		$content = $post->post_content;
	}

	if (!empty($content)) {
		$has_wpfm_shortcode = array('add_food', 'food_dashboard', 'foods', 'food_categories', 'food_type', 'food', 'food_summary', 'food_apply');

		// Filters a list of all shortcodes associated with WPFM.

		$has_wpfm_shortcode = array_unique(apply_filters('food_manager_shortcodes', $has_wpfm_shortcode));
		if (null !== $tag) {
			if (!is_array($tag)) {
				$tag = array($tag);
			}
			$has_wpfm_shortcode = array_intersect($has_wpfm_shortcode, $tag);
		}

		foreach ($has_wpfm_shortcode as $shortcode) {
			if (has_shortcode($content, $shortcode)) {
				$has_wpfm_shortcode = true;
				break;
			}
		}
	}

	// Filter the result of has_wpfm_shortcode() function.
	return apply_filters('has_wpfm_shortcode', $has_wpfm_shortcode);
}

/**
 * This is_wpfm_food_listing() functio is used to Checks if the current page is a food listing.
 *
 * @return bool
 * @since 1.0.0
 */
function is_wpfm_food_listing() {
	return is_singular(array('food_manager'));
}

if (!function_exists('wpfm_get_filtered_links')) :
	/**
	 * This wpfm_get_filtered_links() function Shows links after filtering foods.
	 * 
	 * @param array $args (default: array())
	 * @since 1.0.0
	 */
	function wpfm_get_filtered_links($args = array()) {
		$search_categories = array();
		$search_food_types = array();
		$search_food_menu = '';

		// Convert to slugs
		if (isset($args['search_categories'])) {
			foreach ($args['search_categories'] as $category) {
				if (is_numeric($category)) {
					$category_object = get_term_by('id', $category, 'food_manager_category');
					if (!is_wp_error($category_object)) {
						$search_categories[] = sanitize_title($category_object->slug);
					}
				} else {
					$search_categories[] = $category;
				}
			}
		}

		// Convert to slugs.
		if (isset($args['search_food_types'])) {
			foreach ($args['search_food_types'] as $type) {
				if (is_numeric($type)) {
					$type_object = get_term_by('id', $type, 'food_manager_type');
					if (!is_wp_error($type_object)) {
						$search_food_types[] = sanitize_title($type_object->slug);
					}
				} else {
					$search_food_types[] = $type;
				}
			}
		}

		if (isset($args['search_food_menu']) && !empty($args['search_food_menu'])) {
			$search_food_menu = implode(',', $args['search_food_menu']);
		}

		$links = apply_filters('wpfm_food_filters_showing_foods_links', array(
			'reset' => array(
				'name' => __('Reset', 'wp-food-manager'),
				'url'  => '#'
			),
			'rss_link' => array(
				'name' => __('RSS', 'wp-food-manager'),
				'url'  => get_food_manager_rss_link(
					apply_filters(
						'wpfm_get_listings_custom_filter_rss_args',
						array(
							'search_keywords' => $args['search_keywords'],
							'search_categories'  => implode(',', $search_categories),
							'search_food_types'  => implode(',', $search_food_types),
							'search_food_menu'  => $search_food_menu,
						)
					)
				)
			)
		), $args);

		if (!isset($args['search_keywords']) && !isset($args['search_categories']) && !$search_food_menu && !$args['search_food_types']  && !apply_filters('wpfm_get_listings_custom_filter', false)) {
			unset($links['reset']);
		}

		$return = '';
		$i = 1;
		foreach ($links as $key => $link) {
			if ($i > 1)
				$return .= ' <a href="#">|</a> ';
			$return .= '<a href="' . esc_url($link['url']) . '" class="' . esc_attr($key) . '">' . sanitize_text_field($link['name']) . '</a>';
			$i++;
		}

		return $return;
	}
endif;

if (!function_exists('get_food_manager_rss_link')) :
	/**
	 * This get_food_manager_rss_link() function is used to get the Food Listing RSS link.
	 * 
	 * @return string
	 * @param array $args (default: array())
	 * @since 1.0.0
	 */
	function get_food_manager_rss_link($args = array()) {
		$rss_link = add_query_arg(urlencode_deep(array_merge(array('feed' => 'food_feed'), $args)), home_url());
		return esc_url($rss_link);
	}
endif;

/**
 * This wpfm_prepare_uploaded_files() function Prepare files for upload by standardizing them into an array. This adds support for multiple file upload fields.
 * 
 * @param  array $file_data
 * @return array
 * @since 1.0.0
 */
function wpfm_prepare_uploaded_files($file_data) {
	$files_to_upload = array();
	if (is_array($file_data['name'])) {
		foreach ($file_data['name'] as $file_data_key => $file_data_value) {
			if ($file_data['name'][$file_data_key]) {
				$type              = wp_check_filetype($file_data['name'][$file_data_key]); // Map mime types to those that WordPress knows.
				$files_to_upload[] = array(
					'name'     => esc_attr($file_data['name'][$file_data_key]),
					'type'     => esc_attr($type['type']),
					'tmp_name' => esc_attr($file_data['tmp_name'][$file_data_key]),
					'error'    => esc_attr($file_data['error'][$file_data_key]),
					'size'     => absint($file_data['size'][$file_data_key])
				);
			}
		}
	} else {
		$type              = wp_check_filetype($file_data['name']); // Map mime types to those that WordPress knows.
		$file_data['type'] = esc_attr($type['type']);
		$files_to_upload[] = $file_data;
	}

	return apply_filters('wpfm_prepare_uploaded_files', $files_to_upload);
}

/**
 * This wpfm_upload_file() function is used to Upload a file using WordPress file API.
 * 
 * @param  array $file_data Array of $_FILE data to upload.
 * @param  array $args Optional arguments.
 * @return array|WP_Error Array of objects containing either file information or an error.
 * @since 1.0.0
 */
function wpfm_upload_file($file, $args = array()) {
	global $food_manager_upload, $food_manager_uploading_file;

	include_once(ABSPATH . 'wp-admin/includes/file.php');
	include_once(ABSPATH . 'wp-admin/includes/media.php');

	$args = wp_parse_args($args, array(
		'file_key'           => '',
		'file_label'         => '',
		'allowed_mime_types' => ''
	));

	$food_manager_upload         = true;
	$food_manager_uploading_file = sanitize_title($args['file_key']);
	$uploaded_file              = new stdClass();

	if ('' === $args['allowed_mime_types']) {
		$allowed_mime_types = wpfm_get_allowed_mime_types($food_manager_uploading_file);
	} else {
		$allowed_mime_types = $args['allowed_mime_types'];
	}

	/**
	 * Filter file configuration before upload.
	 * This filter can be used to modify the file arguments before being uploaded, or return a WP_Error object to prevent the file from being uploaded, and return the error.
	 *
	 * @param array $file - Array of $_FILE data to upload.
	 * @param array $args - Optional file arguments.
	 * @param array $allowed_mime_types Array of allowed mime types from field config or defaults
	 * @since 1.0.0
	 */
	$file = apply_filters('wpfm_upload_file_pre_upload', $file, $args, $allowed_mime_types);
	if (is_wp_error($file)) {
		return $file;
	}

	if (!in_array($file['type'], $allowed_mime_types)) {
		if ($args['file_label']) {
			// Translators: %1$s is replaced with the file label, %2$s is replaced with the file type, %3$s is replaced with the allowed file types.
			return new WP_Error('upload', sprintf(__('"%1$s" (filetype %2$s) needs to be one of the following file types: %3$s.', 'wp-food-manager'), $args['file_label'], $file['type'], implode(', ', array_keys($args['allowed_mime_types']))));
		} else {
			// Translators: %s is replaced with a comma-separated list of allowed file types.
			return new WP_Error('upload', sprintf(__('Uploaded files need to be one of the following file types: %s.', 'wp-food-manager'), implode(', ', array_keys($args['allowed_mime_types']))));
		}
	} else {
		$upload = wp_handle_upload($file, apply_filters('add_food_wp_handle_upload_overrides', array('test_form' => false)));
		if (!empty($upload['error'])) {
			return new WP_Error('upload', $upload['error']);
		} else {
			$uploaded_file->url       = esc_url($upload['url']);
			$uploaded_file->file      = esc_attr($upload['file']);
			$uploaded_file->name      = esc_attr(basename($upload['file']));
			$uploaded_file->type      = esc_attr($upload['type']);
			$uploaded_file->size      = absint($file['size']);
			$uploaded_file->extension = substr(strrchr($uploaded_file->name, '.'), 1);
		}
	}

	$food_manager_upload         = false;
	$food_manager_uploading_file = '';

	return $uploaded_file;
}

/**
 * This get_food_order_by() function is used to get the food order which is arrange by the backend.
 * 
 * @return array
 * @since 1.0.0
 */
function get_food_order_by() {
	$args = [
		'title'   => [
			'label' => __('Food Title', 'wp-food-manager'),
			'type' => [
				'title|asc' => __('Ascending (ASC)', 'wp-food-manager'),
				'title|desc' => __('Descending (DESC)', 'wp-food-manager'),
			]
		]
	];

	return apply_filters('get_food_order_by_args', $args);
}

/**
 * This wpfm_get_allowed_mime_types() function is Allowed Mime types specifically for WP Food Manager.
 * 
 * @param   string $field The field key for the upload..
 * @return  array  Array of allowed mime types
 * @param array  {
 *  Array of allowed file extensions and mime types.
 *  Key is pipe-separated file extensions. Value is mime type.
 * }
 * @since 1.0.0
 */
function wpfm_get_allowed_mime_types($field = '') {
	$allowed_mime_types = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif'          => 'image/gif',
		'png'          => 'image/png',
		'webp'          => 'image/webp',
		'pdf'          => 'application/pdf',
		'doc'          => 'application/msword',
		'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	);

	//  Mime types to accept in uploaded files, Default is image, pdf, and doc(x) files.
	return apply_filters('wpfm_mime_types', $allowed_mime_types, $field);
}

/**
 * This food_manager_get_page_id() function is used to get the page ID of a page if set, with PolyLang compat.
 * @param  string $page e.g. food_dashboard, add_food, foods.
 * @return int
 * @since 1.0.0
 */
function food_manager_get_page_id($page) {
	$page_id = get_option('food_manager_' . $page . '_page_id', false);
	if ($page_id) {
		return apply_filters('wpml_object_id', absint(function_exists('pll_get_post') ? pll_get_post($page_id) : $page_id), 'page', TRUE);
	} else {
		return 0;
	}
}

/**
 * This food_manager_get_permalink() function is used to get the permalink of a page if set.
 * @param  string $page e.g. food_dashboard, add_food, foods
 * @return string|bool
 * @since 1.0.0
 */
function food_manager_get_permalink($page) {
	if ($page_id = food_manager_get_page_id($page)) {
		return esc_url(get_permalink($page_id));
	} else {
		return false;
	}
}

/**
 * This food_manager_duplicate_listing() function Duplicates the food by food id.
 * @param  int $post_id
 * @return int 0 on fail or the post ID.
 * @since 1.0.0
 */
function food_manager_duplicate_listing($post_id) {
	if (empty($post_id) || !($post = get_post($post_id))) {
		return 0;
	}
	global $wpdb;

	// Duplicate the post.

	$new_post_id = wp_insert_post(array(
		'comment_status' => esc_attr($post->comment_status),
		'ping_status'    => esc_attr($post->ping_status),
		'post_author'    => esc_attr($post->post_author),
		'post_content'   => sanitize_textarea_field($post->post_content),
		'post_excerpt'   => sanitize_textarea_field($post->post_excerpt),
		'post_name'      => sanitize_title($post->post_name),
		'post_parent'    => absint($post->post_parent),
		'post_password'  => $post->post_password,
		'post_status'    => 'preview',
		'post_title'     => sanitize_text_field($post->post_title),
		'post_type'      => sanitize_title($post->post_type),
		'to_ping'        => $post->to_ping,
		'menu_order'     => absint($post->menu_order)
	));

	// Copy taxonomies.
	$taxonomies = get_object_taxonomies($post->post_type);
	foreach ($taxonomies as $taxonomy) {
		$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
		wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
	}
	
	//  Duplicate post meta, aside from some reserved fields.
	
	$post_meta = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d", $post_id));
	do_action('food_manager_duplicate_listing_meta_start', $post_meta, $post, $new_post_id);
	if (!empty($post_meta)) {
		$post_meta = wp_list_pluck($post_meta, 'meta_value', 'meta_key');
		foreach ($post_meta as $meta_key => $meta_value) {
			if (in_array($meta_key, apply_filters('food_manager_duplicate_listing_ignore_keys', array('_food_expires', '_food_duration')))) {
				continue;
			}
			update_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value));
		}
	}

	do_action('food_manager_duplicate_listing_meta_end', $post_meta, $post, $new_post_id);
	return $new_post_id;
}

/**
 * This food_manager_multiselect_food_type() function return True if only one type allowed per food.
 *
 * @return bool
 * @since 1.0.0
 */
function food_manager_multiselect_food_type() {
	if (!class_exists('WPFM_Add_Food_Form')) {
		include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-abstract-form.php');
		include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-add-food-form.php');
	}
	$form_add_food_instance = call_user_func(array('WPFM_Add_Food_Form', 'instance'));
	$food_fields = $form_add_food_instance->merge_with_custom_fields();
	if (isset($food_fields['food']['food_type']['type']) && $food_fields['food']['food_type']['type'] === 'term-multiselect') {
		return apply_filters('food_manager_multiselect_food_type', true);
	} else {
		return apply_filters('food_manager_multiselect_food_type', false);
	}
}

/**
 * This food_manager_multiselect_food_category() function return True if only one category allowed per food.
 *
 * @return bool
 * @since 1.0.0
 */
function food_manager_multiselect_food_category() {
	if (!class_exists('WPFM_Add_Food_Form')) {
		include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-abstract-form.php');
		include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-add-food-form.php');
	}
	$form_add_food_instance = call_user_func(array('WPFM_Add_Food_Form', 'instance'));
	$food_fields = $form_add_food_instance->merge_with_custom_fields();
	if (isset($food_fields['food']['food_category']['type']) && $food_fields['food']['food_category']['type'] === 'term-multiselect') {
		return apply_filters('food_manager_multiselect_food_category', true);
	} else {
		return apply_filters('food_manager_multiselect_food_category', false);
	}
}

/**
 * This food_manager_use_standard_password_setup_email() function checks to see if the standard password setup email should be used.
 * @return bool True if they are to use standard email, false to allow user to set password at first food creation.
 * @param bool $use_standard_password_setup_email True if a standard account setup email should be sent.
 * @since 1.0.0
 */
function food_manager_use_standard_password_setup_email() {
	$use_standard_password_setup_email = false;
	// If username is being automatically generated, force them to send password setup email.
	if (food_manager_generate_username_from_email()) {
		$use_standard_password_setup_email = get_option('food_manager_use_standard_password_setup_email', 1) == 1 ? true : false;
	}

	// Allows an override of the setting for if a password should be auto-generated for new users.
	return apply_filters('food_manager_use_standard_password_setup_email', $use_standard_password_setup_email);
}

/**
 * This food_manager_validate_new_password() function Check if a password should be auto-generated for new users.
 *
 * @param string $password Password to validate.
 * @return bool True if password meets rules.
 * @param bool   $is_valid_password True if new password is validated.
 * @param string $password - Password to validate.
 * @since 1.0.0
 */
function food_manager_validate_new_password($password) {
	// Password must be at least 8 characters long. Trimming here because `wp_hash_password()` will later on.
	$is_valid_password = strlen(trim($password)) >= 8;

	// Allows overriding default food Manager password validation rules.
	return apply_filters('food_manager_validate_new_password', $is_valid_password, $password);
}

/**
 * This food_manager_get_password_rules_hint() function Returns the password rules hint.
 * @param string $password_rules Password rules description.
 * @return string
 * @since 1.0.0
 */
function food_manager_get_password_rules_hint() {
	// Allows overriding the hint shown below the new password input field. Describes rules set in `food_manager_validate_new_password`.
	return apply_filters('food_manager_password_rules_hint', __('Passwords must be at least 8 characters long.', 'wp-food-manager'));
}

if (!function_exists('get_food_listing_post_statuses')) :
	/**
	 * This get_food_listing_post_statuses() function is used to get post statuses used for foods.
	 *
	 * @access public
	 * @return array
	 * @since 1.0.0
	 */
	function get_food_listing_post_statuses() {
		return apply_filters('food_listing_post_statuses', array(
			'draft'           => _x('Draft', 'post status', 'wp-food-manager'),
			'expired'         => _x('Expired', 'post status', 'wp-food-manager'),
			'preview'         => _x('Preview', 'post status', 'wp-food-manager'),
			'pending'         => _x('Pending approval', 'post status', 'wp-food-manager'),
			'pending_payment' => _x('Pending payment', 'post status', 'wp-food-manager'),
			'publish'         => _x('Active', 'post status', 'wp-food-manager'),
		));
	}
endif;

if (!function_exists('get_food_listing_types')) :
	/**
	 * This get_food_listing_types() function is used to get food listing types.
	 *
	 * @access public
	 * @param string $fields (default: 'all')
	 * @return array
	 * @since 1.0.0
	 */
	function get_food_listing_types($fields = 'all') {
		if (!get_option('food_manager_enable_food_types')) {
			return array();
		} else {
			$args = array(
				'fields'     => $fields,
				'hide_empty' => false,
				'order'      => 'ASC',
				'orderby'    => 'name'
			);
			$args = apply_filters('get_food_listing_types_args', $args);
			// Prevent users from filtering the taxonomy.
			$args['taxonomy'] = 'food_manager_type';
			return get_terms($args);
		}
	}
endif;

if (!function_exists('get_food_listing_categories')) :
	/**
	 * This get_food_listing_categories() function is used to get food categories.
	 *
	 * @access public
	 * @param array $args
	 * @return array
	 * @since 1.0.0
	 */
	function get_food_listing_categories() {
		if (!get_option('food_manager_enable_categories')) {
			return array();
		}
		$args = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		// Change the category query arguments.
		$args = apply_filters('get_food_listing_category_args', $args);
		// Prevent users from filtering the taxonomy.
		$args['taxonomy'] = 'food_manager_category';
		return get_terms($args);
	}
endif;

/**
 * This get_food_manager_currency() function is used to get Base Currency Code.
 *
 * @return string
 * @since 1.0.0
 */
function get_food_manager_currency() {
	return apply_filters('wpfm_currency', get_option('wpfm_currency'));
}

/**
 * This get_food_manager_currencies() function is used to get full list of currency codes.
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols).
 *
 * @return array
 * @since 1.0.0
 */
function get_food_manager_currencies() {
	static $currencies;

	if (!isset($currencies)) {
		$currencies = array_unique(
			apply_filters(
				'food_manager_currencies',
				array(
					'AED' => __('United Arab Emirates dirham', 'wp-food-manager'),
					'AFN' => __('Afghan afghani', 'wp-food-manager'),
					'ALL' => __('Albanian lek', 'wp-food-manager'),
					'AMD' => __('Armenian dram', 'wp-food-manager'),
					'ANG' => __('Netherlands Antillean guilder', 'wp-food-manager'),
					'AOA' => __('Angolan kwanza', 'wp-food-manager'),
					'ARS' => __('Argentine peso', 'wp-food-manager'),
					'AUD' => __('Australian dollar', 'wp-food-manager'),
					'AWG' => __('Aruban florin', 'wp-food-manager'),
					'AZN' => __('Azerbaijani manat', 'wp-food-manager'),
					'BAM' => __('Bosnia and Herzegovina convertible mark', 'wp-food-manager'),
					'BBD' => __('Barbadian dollar', 'wp-food-manager'),
					'BDT' => __('Bangladeshi taka', 'wp-food-manager'),
					'BGN' => __('Bulgarian lev', 'wp-food-manager'),
					'BHD' => __('Bahraini dinar', 'wp-food-manager'),
					'BIF' => __('Burundian franc', 'wp-food-manager'),
					'BMD' => __('Bermudian dollar', 'wp-food-manager'),
					'BND' => __('Brunei dollar', 'wp-food-manager'),
					'BOB' => __('Bolivian boliviano', 'wp-food-manager'),
					'BRL' => __('Brazilian real', 'wp-food-manager'),
					'BSD' => __('Bahamian dollar', 'wp-food-manager'),
					'BTC' => __('Bitcoin', 'wp-food-manager'),
					'BTN' => __('Bhutanese ngultrum', 'wp-food-manager'),
					'BWP' => __('Botswana pula', 'wp-food-manager'),
					'BYR' => __('Belarusian ruble (old)', 'wp-food-manager'),
					'BYN' => __('Belarusian ruble', 'wp-food-manager'),
					'BZD' => __('Belize dollar', 'wp-food-manager'),
					'CAD' => __('Canadian dollar', 'wp-food-manager'),
					'CDF' => __('Congolese franc', 'wp-food-manager'),
					'CHF' => __('Swiss franc', 'wp-food-manager'),
					'CLP' => __('Chilean peso', 'wp-food-manager'),
					'CNY' => __('Chinese yuan', 'wp-food-manager'),
					'COP' => __('Colombian peso', 'wp-food-manager'),
					'CRC' => __('Costa Rican col&oacute;n', 'wp-food-manager'),
					'CUC' => __('Cuban convertible peso', 'wp-food-manager'),
					'CUP' => __('Cuban peso', 'wp-food-manager'),
					'CVE' => __('Cape Verdean escudo', 'wp-food-manager'),
					'CZK' => __('Czech koruna', 'wp-food-manager'),
					'DJF' => __('Djiboutian franc', 'wp-food-manager'),
					'DKK' => __('Danish krone', 'wp-food-manager'),
					'DOP' => __('Dominican peso', 'wp-food-manager'),
					'DZD' => __('Algerian dinar', 'wp-food-manager'),
					'EGP' => __('Egyptian pound', 'wp-food-manager'),
					'ERN' => __('Eritrean nakfa', 'wp-food-manager'),
					'ETB' => __('Ethiopian birr', 'wp-food-manager'),
					'EUR' => __('Euro', 'wp-food-manager'),
					'FJD' => __('Fijian dollar', 'wp-food-manager'),
					'FKP' => __('Falkland Islands pound', 'wp-food-manager'),
					'GBP' => __('Pound sterling', 'wp-food-manager'),
					'GEL' => __('Georgian lari', 'wp-food-manager'),
					'GGP' => __('Guernsey pound', 'wp-food-manager'),
					'GHS' => __('Ghana cedi', 'wp-food-manager'),
					'GIP' => __('Gibraltar pound', 'wp-food-manager'),
					'GMD' => __('Gambian dalasi', 'wp-food-manager'),
					'GNF' => __('Guinean franc', 'wp-food-manager'),
					'GTQ' => __('Guatemalan quetzal', 'wp-food-manager'),
					'GYD' => __('Guyanese dollar', 'wp-food-manager'),
					'HKD' => __('Hong Kong dollar', 'wp-food-manager'),
					'HNL' => __('Honduran lempira', 'wp-food-manager'),
					'HRK' => __('Croatian kuna', 'wp-food-manager'),
					'HTG' => __('Haitian gourde', 'wp-food-manager'),
					'HUF' => __('Hungarian forint', 'wp-food-manager'),
					'IDR' => __('Indonesian rupiah', 'wp-food-manager'),
					'ILS' => __('Israeli new shekel', 'wp-food-manager'),
					'IMP' => __('Manx pound', 'wp-food-manager'),
					'INR' => __('Indian rupee', 'wp-food-manager'),
					'IQD' => __('Iraqi dinar', 'wp-food-manager'),
					'IRR' => __('Iranian rial', 'wp-food-manager'),
					'IRT' => __('Iranian toman', 'wp-food-manager'),
					'ISK' => __('Icelandic kr&oacute;na', 'wp-food-manager'),
					'JEP' => __('Jersey pound', 'wp-food-manager'),
					'JMD' => __('Jamaican dollar', 'wp-food-manager'),
					'JOD' => __('Jordanian dinar', 'wp-food-manager'),
					'JPY' => __('Japanese yen', 'wp-food-manager'),
					'KES' => __('Kenyan shilling', 'wp-food-manager'),
					'KGS' => __('Kyrgyzstani som', 'wp-food-manager'),
					'KHR' => __('Cambodian riel', 'wp-food-manager'),
					'KMF' => __('Comorian franc', 'wp-food-manager'),
					'KPW' => __('North Korean won', 'wp-food-manager'),
					'KRW' => __('South Korean won', 'wp-food-manager'),
					'KWD' => __('Kuwaiti dinar', 'wp-food-manager'),
					'KYD' => __('Cayman Islands dollar', 'wp-food-manager'),
					'KZT' => __('Kazakhstani tenge', 'wp-food-manager'),
					'LAK' => __('Lao kip', 'wp-food-manager'),
					'LBP' => __('Lebanese pound', 'wp-food-manager'),
					'LKR' => __('Sri Lankan rupee', 'wp-food-manager'),
					'LRD' => __('Liberian dollar', 'wp-food-manager'),
					'LSL' => __('Lesotho loti', 'wp-food-manager'),
					'LYD' => __('Libyan dinar', 'wp-food-manager'),
					'MAD' => __('Moroccan dirham', 'wp-food-manager'),
					'MDL' => __('Moldovan leu', 'wp-food-manager'),
					'MGA' => __('Malagasy ariary', 'wp-food-manager'),
					'MKD' => __('Macedonian denar', 'wp-food-manager'),
					'MMK' => __('Burmese kyat', 'wp-food-manager'),
					'MNT' => __('Mongolian t&ouml;gr&ouml;g', 'wp-food-manager'),
					'MOP' => __('Macanese pataca', 'wp-food-manager'),
					'MRU' => __('Mauritanian ouguiya', 'wp-food-manager'),
					'MUR' => __('Mauritian rupee', 'wp-food-manager'),
					'MVR' => __('Maldivian rufiyaa', 'wp-food-manager'),
					'MWK' => __('Malawian kwacha', 'wp-food-manager'),
					'MXN' => __('Mexican peso', 'wp-food-manager'),
					'MYR' => __('Malaysian ringgit', 'wp-food-manager'),
					'MZN' => __('Mozambican metical', 'wp-food-manager'),
					'NAD' => __('Namibian dollar', 'wp-food-manager'),
					'NGN' => __('Nigerian naira', 'wp-food-manager'),
					'NIO' => __('Nicaraguan c&oacute;rdoba', 'wp-food-manager'),
					'NOK' => __('Norwegian krone', 'wp-food-manager'),
					'NPR' => __('Nepalese rupee', 'wp-food-manager'),
					'NZD' => __('New Zealand dollar', 'wp-food-manager'),
					'OMR' => __('Omani rial', 'wp-food-manager'),
					'PAB' => __('Panamanian balboa', 'wp-food-manager'),
					'PEN' => __('Sol', 'wp-food-manager'),
					'PGK' => __('Papua New Guinean kina', 'wp-food-manager'),
					'PHP' => __('Philippine peso', 'wp-food-manager'),
					'PKR' => __('Pakistani rupee', 'wp-food-manager'),
					'PLN' => __('Polish z&#x142;oty', 'wp-food-manager'),
					'PRB' => __('Transnistrian ruble', 'wp-food-manager'),
					'PYG' => __('Paraguayan guaran&iacute;', 'wp-food-manager'),
					'QAR' => __('Qatari riyal', 'wp-food-manager'),
					'RON' => __('Romanian leu', 'wp-food-manager'),
					'RSD' => __('Serbian dinar', 'wp-food-manager'),
					'RUB' => __('Russian ruble', 'wp-food-manager'),
					'RWF' => __('Rwandan franc', 'wp-food-manager'),
					'SAR' => __('Saudi riyal', 'wp-food-manager'),
					'SBD' => __('Solomon Islands dollar', 'wp-food-manager'),
					'SCR' => __('Seychellois rupee', 'wp-food-manager'),
					'SDG' => __('Sudanese pound', 'wp-food-manager'),
					'SEK' => __('Swedish krona', 'wp-food-manager'),
					'SGD' => __('Singapore dollar', 'wp-food-manager'),
					'SHP' => __('Saint Helena pound', 'wp-food-manager'),
					'SLL' => __('Sierra Leonean leone', 'wp-food-manager'),
					'SOS' => __('Somali shilling', 'wp-food-manager'),
					'SRD' => __('Surinamese dollar', 'wp-food-manager'),
					'SSP' => __('South Sudanese pound', 'wp-food-manager'),
					'STN' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'wp-food-manager'),
					'SYP' => __('Syrian pound', 'wp-food-manager'),
					'SZL' => __('Swazi lilangeni', 'wp-food-manager'),
					'THB' => __('Thai baht', 'wp-food-manager'),
					'TJS' => __('Tajikistani somoni', 'wp-food-manager'),
					'TMT' => __('Turkmenistan manat', 'wp-food-manager'),
					'TND' => __('Tunisian dinar', 'wp-food-manager'),
					'TOP' => __('Tongan pa&#x2bb;anga', 'wp-food-manager'),
					'TRY' => __('Turkish lira', 'wp-food-manager'),
					'TTD' => __('Trinidad and Tobago dollar', 'wp-food-manager'),
					'TWD' => __('New Taiwan dollar', 'wp-food-manager'),
					'TZS' => __('Tanzanian shilling', 'wp-food-manager'),
					'UAH' => __('Ukrainian hryvnia', 'wp-food-manager'),
					'UGX' => __('Ugandan shilling', 'wp-food-manager'),
					'USD' => __('United States (US) dollar', 'wp-food-manager'),
					'UYU' => __('Uruguayan peso', 'wp-food-manager'),
					'UZS' => __('Uzbekistani som', 'wp-food-manager'),
					'VEF' => __('Venezuelan bol&iacute;var', 'wp-food-manager'),
					'VES' => __('Bol&iacute;var soberano', 'wp-food-manager'),
					'VND' => __('Vietnamese &#x111;&#x1ed3;ng', 'wp-food-manager'),
					'VUV' => __('Vanuatu vatu', 'wp-food-manager'),
					'WST' => __('Samoan t&#x101;l&#x101;', 'wp-food-manager'),
					'XAF' => __('Central African CFA franc', 'wp-food-manager'),
					'XCD' => __('East Caribbean dollar', 'wp-food-manager'),
					'XOF' => __('West African CFA franc', 'wp-food-manager'),
					'XPF' => __('CFP franc', 'wp-food-manager'),
					'YER' => __('Yemeni rial', 'wp-food-manager'),
					'ZAR' => __('South African rand', 'wp-food-manager'),
					'ZMW' => __('Zambian kwacha', 'wp-food-manager'),
				)
			)
		);
	}

	return $currencies;
}

/**
 * This get_food_manager_currency_symbols() function is used to Get all available Currency symbols.
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols).
 *
 * @return array
 * @since 1.0.0
 */
function get_food_manager_currency_symbols() {
	$symbols = apply_filters(
		'food_manager_currency_symbols',
		array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => 'Afl.',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BYN' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'kr.',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x20be;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x62f;.&#x639;',
			'IRR' => '&#xfdfc;',
			'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
			'ISK' => 'kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x441;&#x43e;&#x43c;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => '&#8376;',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'MDL',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRU' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => 'N&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#1088;&#1089;&#1076;',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STN' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'E',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VES' => 'Bs.S',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'CFA',
			'XCD' => '&#36;',
			'XOF' => 'CFA',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		)
	);

	return $symbols;
}

/**
 * This get_food_manager_currency_symbol() function is used to get Currency symbol.
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols).
 *
 * @param string $currency (default: '').
 * @return string
 * @since 1.0.0
 */
function get_food_manager_currency_symbol($currency = '') {
	if (!$currency) {
		$currency = get_food_manager_currency();
	}
	$symbols = get_food_manager_currency_symbols();
	$currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';
	return apply_filters('food_manager_currency_symbol', esc_attr($currency_symbol), esc_attr($currency));
}

/**
 * This get_food_manager_price_format() function is used to get the price format depending on the currency position.
 *
 * @return string
 * @since 1.0.0
 */
function get_food_manager_price_format() {
	$currency_pos = get_option('wpfm_currency_pos');
	$format       = '%1$s%2$s';

	switch ($currency_pos) {
		case 'left':
			$format = '%1$s%2$s';
			break;
		case 'right':
			$format = '%2$s%1$s';
			break;
		case 'left_space':
			$format = '%1$s&nbsp;%2$s';
			break;
		case 'right_space':
			$format = '%2$s&nbsp;%1$s';
			break;
	}

	return apply_filters('food_manager_price_format', esc_attr($format), esc_attr($currency_pos));
}

/**
 * This wpfm_get_price_thousand_separator() function returns the thousand separator for prices.
 *
 * @return string
 * @since  1.0.0
 */
function wpfm_get_price_thousand_separator() {
	return stripslashes(apply_filters('wpfm_get_price_thousand_separator', get_option('wpfm_price_thousand_sep')));
}

/**
 * This wpfm_get_price_decimal_separator() function returns the decimal separator for prices.
 *
 * @return string
 * @since  1.0.0
 */
function wpfm_get_price_decimal_separator() {
	$separator = apply_filters('wpfm_get_price_decimal_separator', get_option('wpfm_price_decimal_sep'));
	return $separator ? stripslashes($separator) : '.';
}

/**
 * This wpfm_get_price_decimals() function returns the number of decimals after the decimal point.
 *
 * @return int
 * @since  1.0.0
 */
function wpfm_get_price_decimals() {
	return absint(apply_filters('wpfm_get_price_decimals', get_option('wpfm_price_num_decimals', 2)));
}

/**
 * This wpfm_get_dashicons() function returns the wordpress dashicons's content with classes.
 * 
 * @return array
 * @since  1.0.0
 */
function wpfm_get_dashicons() {
	return array(
		'dashicons-menu'                       => 'f333',
		'dashicons-admin-site'                 => 'f319',
		'dashicons-dashboard'                  => 'f226',
		'dashicons-admin-media'                => 'f104',
		'dashicons-admin-page'                 => 'f105',
		'dashicons-admin-comments'             => 'f101',
		'dashicons-admin-appearance'           => 'f100',
		'dashicons-admin-plugins'              => 'f106',
		'dashicons-admin-users'                => 'f110',
		'dashicons-admin-tools'                => 'f107',
		'dashicons-admin-settings'             => 'f108',
		'dashicons-admin-network'              => 'f112',
		'dashicons-admin-generic'              => 'f111',
		'dashicons-admin-home'                 => 'f102',
		'dashicons-admin-collapse'             => 'f148',
		'dashicons-admin-links'                => '103',
		'dashicons-format-links'               => 'f103',
		'dashicons-admin-post'                 => '109',
		'dashicons-format-standard'            => 'f109',
		'dashicons-format-image'               => 'f128',
		'dashicons-format-gallery'             => 'f161',
		'dashicons-format-audio'               => 'f127',
		'dashicons-format-video'               => 'f126',
		'dashicons-format-chat'                => 'f125',
		'dashicons-format-status'              => 'f130',
		'dashicons-format-aside'               => 'f123',
		'dashicons-format-quote'               => 'f122',
		'dashicons-welcome-write-blog'         => 'f119',
		'dashicons-welcome-edit-page'          => 'f119',
		'dashicons-welcome-add-page'           => 'f133',
		'dashicons-welcome-view-site'          => 'f115',
		'dashicons-welcome-widgets-menus'      => 'f116',
		'dashicons-welcome-comments'           => 'f117',
		'dashicons-welcome-learn-more'         => 'f118',
		'dashicons-image-crop'                 => 'f165',
		'dashicons-image-rotate-left'          => 'f166',
		'dashicons-image-rotate-right'         => 'f167',
		'dashicons-image-flip-vertical'        => 'f168',
		'dashicons-image-flip-horizontal'      => 'f169',
		'dashicons-undo'                       => 'f171',
		'dashicons-redo'                       => 'f172',
		'dashicons-editor-bold'                => 'f200',
		'dashicons-editor-italic'              => 'f201',
		'dashicons-editor-ul'                  => 'f203',
		'dashicons-editor-ol'                  => 'f204',
		'dashicons-editor-quote'               => 'f205',
		'dashicons-editor-alignleft'           => 'f206',
		'dashicons-editor-aligncenter'         => 'f207',
		'dashicons-editor-alignright'          => 'f208',
		'dashicons-editor-insertmore'          => 'f209',
		'dashicons-editor-spellcheck'          => 'f210',
		'dashicons-editor-distractionfree'     => 'f211',
		'dashicons-editor-kitchensink'         => 'f212',
		'dashicons-editor-underline'           => 'f213',
		'dashicons-editor-justify'             => 'f214',
		'dashicons-editor-textcolor'           => 'f215',
		'dashicons-editor-paste-word'          => 'f216',
		'dashicons-editor-paste-text'          => 'f217',
		'dashicons-editor-removeformatting'    => 'f218',
		'dashicons-editor-video'               => 'f219',
		'dashicons-editor-customchar'          => 'f220',
		'dashicons-editor-outdent'             => 'f221',
		'dashicons-editor-indent'              => 'f222',
		'dashicons-editor-help'                => 'f223',
		'dashicons-editor-strikethrough'       => 'f224',
		'dashicons-editor-unlink'              => 'f225',
		'dashicons-editor-rtl'                 => 'f320',
		'dashicons-align-left'                 => 'f135',
		'dashicons-align-right'                => 'f136',
		'dashicons-align-center'               => 'f134',
		'dashicons-align-none'                 => 'f138',
		'dashicons-lock'                       => 'f160',
		'dashicons-calendar'                   => 'f145',
		'dashicons-visibility'                 => 'f177',
		'dashicons-post-status'                => 'f173',
		'dashicons-post-trash'                 => 'f182',
		'dashicons-edit'                       => 'f327',
		'dashicons-trash'                      => 'rtin',
		'dashicons-arrow-up'                   => 'f142',
		'dashicons-arrow-down'                 => 'f140',
		'dashicons-arrow-left'                 => 'f141',
		'dashicons-arrow-right'                => 'f139',
		'dashicons-arrow-up-alt'               => 'f342',
		'dashicons-arrow-down-alt'             => 'f346',
		'dashicons-arrow-left-alt'             => 'f340',
		'dashicons-arrow-right-alt'            => 'f344',
		'dashicons-arrow-up-alt2'              => 'f343',
		'dashicons-arrow-down-alt2'            => 'f347',
		'dashicons-arrow-left-alt2'            => 'f341',
		'dashicons-arrow-right-alt2'           => 'f345',
		'dashicons-leftright'                  => 'f229',
		'dashicons-sort'                       => 'f156',
		'dashicons-list-view'                  => 'f163',
		'dashicons-exerpt-view'                => 'f164',
		'dashicons-share'                      => 'f237',
		'dashicons-share-alt'                  => 'f240',
		'dashicons-share-alt2'                 => 'f242',
		'dashicons-twitter'                    => 'f301',
		'dashicons-rss'                        => 'f303',
		'dashicons-facebook'                   => 'f304',
		'dashicons-facebook-alt'               => 'f305',
		'dashicons-networking'                 => 'f325',
		'dashicons-googleplus'                 => 'f462',
		'dashicons-hammer'                     => 'f308',
		'dashicons-art'                        => 'f309',
		'dashicons-migrate'                    => 'f310',
		'dashicons-performance'                => 'f311',
		'dashicons-wordpress'                  => 'f120',
		'dashicons-wordpress-alt'              => 'f324',
		'dashicons-pressthis'                  => 'f157',
		'dashicons-update'                     => 'f113',
		'dashicons-screenoptions'              => 'f180',
		'dashicons-info'                       => 'f348',
		'dashicons-cart'                       => 'f174',
		'dashicons-feedback'                   => 'f175',
		'dashicons-cloud'                      => 'f176',
		'dashicons-translation'                => 'f326',
		'dashicons-tag'                        => 'f323',
		'dashicons-category'                   => 'f318',
		'dashicons-yes'                        => 'f147',
		'dashicons-no'                         => 'f158',
		'dashicons-no-alt'                     => 'f335',
		'dashicons-plus'                       => 'f132',
		'dashicons-minus'                      => 'f460',
		'dashicons-dismiss'                    => 'f153',
		'dashicons-marker'                     => 'f159',
		'dashicons-star-filled'                => 'f155',
		'dashicons-star-half'                  => 'f459',
		'dashicons-star-empty'                 => 'f154',
		'dashicons-flag'                       => 'f227',
		'dashicons-location'                   => 'f230',
		'dashicons-location-alt'               => 'f231',
		'dashicons-camera'                     => 'f306',
		'dashicons-images-alt'                 => 'f232',
		'dashicons-images-alt2'                => 'f233',
		'dashicons-video-alt'                  => 'f234',
		'dashicons-video-alt2'                 => 'f235',
		'dashicons-video-alt3'                 => 'f236',
		'dashicons-vault'                      => 'f178',
		'dashicons-shield'                     => 'f332',
		'dashicons-shield-alt'                 => 'f334',
		'dashicons-search'                     => 'f179',
		'dashicons-slides'                     => 'f181',
		'dashicons-analytics'                  => 'f183',
		'dashicons-chart-pie'                  => 'f184',
		'dashicons-chart-bar'                  => 'f185',
		'dashicons-chart-line'                 => 'f238',
		'dashicons-chart-area'                 => 'f239',
		'dashicons-groups'                     => 'f307',
		'dashicons-businessman'                => 'f338',
		'dashicons-id'                         => 'f336',
		'dashicons-id-alt'                     => 'f337',
		'dashicons-products'                   => 'f312',
		'dashicons-awards'                     => 'f313',
		'dashicons-forms'                      => 'f314',
		'dashicons-portfolio'                  => 'f322',
		'dashicons-book'                       => 'f330',
		'dashicons-book-alt'                   => 'f331',
		'dashicons-download'                   => 'f316',
		'dashicons-upload'                     => 'f317',
		'dashicons-backup'                     => 'f321',
		'dashicons-lightbulb'                  => 'f339',
		'dashicons-smiley'                     => 'f328',
	);
}

/**
 * This wpfm_get_font_food_icons() function returns the food font icons.
 *
 * @return array
 * @since  1.0.1
 */
function wpfm_get_font_food_icons() {
	return array(
		'wpfm-icon-broccoli'                            => 'eaeb',
		'wpfm-icon-buffet-breakfast'                    => 'e901',
		'wpfm-icon-chocolate-bar'                       => 'e904',
		'wpfm-icon-cinnamon-roll'                       => 'e905',
		'wpfm-icon-cutlery'                             => 'e906',
		'wpfm-icon-deliver-food'                        => 'e907',
		'wpfm-icon-dog-bowl'                            => 'e908',
		'wpfm-icon-energy-drink'                        => 'e909',
		'wpfm-icon-food'                          	    => 'e90f',
		'wpfm-icon-food-bar'                            => 'e910',
		'wpfm-icon-food-receiver'                       => 'e911',
		'wpfm-icon-french-fries'                        => 'e912',
		'wpfm-icon-hamburger'                			=> 'e913',
		'wpfm-icon-hot-breakfast'                       => 'e914',
		'wpfm-icon-hot-dog'                             => 'e915',
		'wpfm-icon-ice-cream-bowl'                      => 'e916',
		'wpfm-icon-ingredients'                         => 'e917',
		'wpfm-icon-meal'                        	    => 'e918',
		'wpfm-icon-melting-ice-cream'                   => 'e919',
		'wpfm-icon-motorcycle-delivery-multiple-boxes'  => 'e91a',
		'wpfm-icon-no-food'                        	    => 'e91b',
		'wpfm-icon-noodles'                        	    => 'e91c',
		'wpfm-icon-organic-food'                        => 'e91d',
		'wpfm-icon-paprika'                        	    => 'e91e',
		'wpfm-icon-pizza'                        	 	=> 'e91f',
		'wpfm-icon-restaurant-pickup'                   => 'e920',
		'wpfm-icon-samosa'                        	    => 'e925',
		'wpfm-icon-spaghetti'                        	=> 'e926',
		'wpfm-icon-sushi'                        		=> 'e927',
		'wpfm-icon-taco'								=> 'e928',
		'wpfm-icon-vegan-food'                          => 'e929',
		'wpfm-icon-weber'                        		=> 'e92a',
		'wpfm-icon-wedding-cake'                        => 'e92b',
		'wpfm-icon-wrap'                        		=> 'e92c',
	);
}

/**
 * This wpfm_begnWith() Checks if given string ($str) is begin with the second parameter ($begin_string) of function.
 *
 * @param string $str
 * @param string $begin_string
 * @return bool
 * @since 1.0.1
 */
function wpfm_begnWith($str, $begin_string) {
	$len = strlen($begin_string);
	if (is_array($str)) {
		$str = '';
	}
	return (substr($str, 0, $len) === $begin_string);
}

if (!function_exists('get_food_listings_keyword_search')) :
	/**
	 * This get_food_listings_keyword_search() function Join and where query for keywords.
	 *
	 * @param array $search
	 * @return array
	 */
	function get_food_listings_keyword_search($search) {
		global $wpdb, $food_manager_keyword;

		// Searchable Meta Keys: set to empty to search all meta keys.
		$searchable_meta_keys = array(
			'_food_location',
			'_food_tags',
		);

		$searchable_meta_keys = apply_filters('food_listing_searchable_meta_keys', $searchable_meta_keys);
		$conditions   = array();

		// Search Post Meta.
		if (apply_filters('food_listing_search_post_meta', true)) {
			// Only selected meta keys.
			if ($searchable_meta_keys) {
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ( '" . implode("','", array_map('esc_sql', $searchable_meta_keys)) . "' ) AND meta_value LIKE '%" . esc_sql($food_manager_keyword) . "%' )";
			} else {
				// No meta keys defined, search all post meta value
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%" . esc_sql($food_manager_keyword) . "%' )";
			}
		}

		// Search taxonomy.
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id WHERE t.name LIKE '%" . esc_sql($food_manager_keyword) . "%' )";

		/**
		 * Filters the conditions to use when querying food listings. Resulting array is joined with OR statements.
		 *
		 * @param array  $conditions - Conditions to join by OR when querying food listings.
		 * @param string $food_manager_keyword Search query.
		 */
		$conditions = apply_filters('food_listing_search_conditions', $conditions, $food_manager_keyword);
		if (empty($conditions)) {
			return $search;
		}

		$conditions_str = implode(' OR ', $conditions);
		if (!empty($search)) {
			$search = preg_replace('/^ AND /', '', $search);
			$search = " AND ( {$search} OR ( {$conditions_str} ) )";
		} else {
			$search = " AND ( {$conditions_str} )";
		}

		return $search;
	}
endif;

/**
 * This food_manager_user_can_upload_file_via_ajax() function Checks if the user can upload a file via the Ajax endpoint.
 * @param bool $can_upload True if they can upload files from Ajax endpoint.
 * @return bool
 * @since 1.0.0
 */
function food_manager_user_can_upload_file_via_ajax() {
	$can_upload = wpfm_user_can_post_food();

	// Override ability of a user to upload a file via Ajax.
	return apply_filters('food_manager_user_can_upload_file_via_ajax', esc_attr($can_upload));
}

/**
 * This wpfm_extra_topping_form_fields() function checks if the user can upload a file via the Ajax endpoint.
 *
 * @param mixed $post
 * @param mixed $field
 * @param mixed $field_value
 * @return void
 * @since 1.0.0
 */
function wpfm_extra_topping_form_fields($post, $field, $field_value) {
	$date_format = !empty(get_option('date_format')) ? get_option('date_format') : 'F j, Y';
	$time_format = !empty(get_option('time_format')) ? get_option('time_format') : 'g:i a';
	get_food_manager_template('food-extra-topping.php', array('date_format' => $date_format, 'time_format' => $time_format, 'field' => $field, 'field_value' => $field_value));
}

/**
 * This wpfm_term_radio_checklist_for_food_type() Use radio inputs instead of checkboxes for term checklists in specified taxonomies such as 'food_manager_type'.
 *
 * @param array $args
 * @return array
 * @since 1.0.0
 */
function wpfm_term_radio_checklist_for_food_type($args, $taxonomy) {
	$taxonomy = apply_filters('wpfm_term_radio_checklist_taxonomy', $args, $taxonomy);
	$post_type = apply_filters('wpfm_term_radio_checklist_post_type', '');
	/* Change to your required taxonomy */
	if (get_post_type() == $post_type) {
		if (!empty($args['taxonomy']) && $args['taxonomy'] === $taxonomy || is_array($taxonomy) && in_array($args['taxonomy'], $taxonomy)) {
			// Don't override 3rd party walkers.
			if (empty($args['walker']) || is_a($args['walker'], 'Walker')) {
				require_once 'includes/wpfm-taxonomy-radio-checklist.php';
				$args['walker'] = new WPFM_Taxonomy_Radio_Checklist;
			}
		}
	}

	return $args;
}

/**
 * This wpfm_is_multi_array() Checks if given array is multi-array or not.
 *
 * @return bool
 * @param mixed $a
 * @since 1.0.1
 */
function wpfm_is_multi_array($field) {
	if (is_array($field)) {
		foreach ($field as $value) if (is_array($value)) return TRUE;
	}
	return FALSE;
}

/**
 * This wpfm_category_checklist() function return the given taxnomy list array and display the category checklist html.
 *
 * @return $wpfm_term_ids
 * @param string $taxonomy
 * @param string $key_name
 * @param array $checked_term
 * @since  1.0.1
 */
function wpfm_category_checklist($taxonomy, $key_name, $checked_term) {
	// Get terms.
	$terms = get_terms(
		array(
			'taxonomy'     => esc_attr($taxonomy),
			'orderby'          => 'name',
			'hide_empty'       => false
		)
	);

	// Get taxonomy.
	$tax = get_taxonomy($taxonomy);
	$wpfm_term_ids = array();
	foreach ((array) $terms as $term) {
		$wpfm_term_ids[] = $term->term_id;
		$id      = "$taxonomy-$term->term_id";
		$checked = in_array($term->term_id, $checked_term) ? 'checked="checked"' : ''; ?>
		<li id="<?php echo esc_attr( $tax->name . '-' . $id ); ?>" class="<?php echo esc_attr( $tax->name ); ?>">
			<label class="selectit">
				<input id="in-<?php echo esc_attr(sanitize_title($tax->name)); ?>-<?php echo absint($id); ?>" type="checkbox" <?php echo wp_kses_post($checked); ?> name="<?php echo esc_attr($key_name); ?>[<?php echo esc_attr($tax->name); ?>][]" value="<?php echo (int) $term->term_id; ?>" <?php disabled(!current_user_can($tax->cap->assign_terms)); ?> />
				<?php echo esc_html(apply_filters('the_category', $term->name, '', '')); ?>
			</label>
		</li>
	<?php
	}

	return $wpfm_term_ids;
}

/**
 * This wpfm_dropdown_categories() returns the given taxnomy list array and display the category dropdown html.
 *
 * @return $wpfm_term_ids
 * @param string $taxonomy
 * @param string $key_name
 * @param array $checked_term
 * @since  1.0.1
 */
function wpfm_dropdown_categories($taxonomy, $key_name, $selected_term) {
	// Get terms
	$terms = get_terms(
		array(
			'taxonomy'     => esc_attr($taxonomy),
			'orderby'          => 'name',
			'hide_empty'       => false
		)
	);
	if (is_wp_error($terms)) {
		echo 'Error: ' . esc_html( $terms->get_error_message() );
        return;
    }
	$wpfm_term_ids = array();
	echo '<select name="' . esc_attr(sanitize_title($key_name)) . '" id="' . esc_attr(sanitize_title($key_name)) . '" class="postform">';
	foreach ((array) $terms as $term) {
		$wpfm_term_ids[] = absint($term->term_id);
		$selected = ($term->term_id == $selected_term) ? 'selected="selected"' : ''; ?>
		<option class="level-0" value="<?php echo (int) $term->term_id; ?>" <?php echo wp_kses_post($selected); ?>>
			<?php echo esc_html(apply_filters('the_category', $term->name, '', '')); ?>
		</option>
<?php }
	echo "</select>";

	return $wpfm_term_ids;
}

/**
 * This is_wpfm_terms_exist() checks if term id given as a terms array is exist or not.
 *
 * @return $displayTerms
 * @param array $terms
 * @param string $taxonomy
 * @since  1.0.1
 */
function is_wpfm_terms_exist($terms, $taxonomy) {
	$displayTerms = 0;
	if ($terms) {
		foreach ($terms as $term) {
			$isTerm = get_term(
				!empty($term['id']) ? absint($term['id']) : 0,
				$taxonomy
			);
			$displayTerms = (!empty($isTerm->term_id)) ? 1 : 0;
		}
	}
	return esc_attr($displayTerms);
}

/** 
* This function is used to display those food menus which is added by current user.
*
* @since 1.0.0
*/
function wpfm_term_menu_lists(){
	global $wpdb;
	$current_user = wp_get_current_user();
	$authors = [ $current_user->ID ];

	$query_args = [
		'author__in'    =>  $authors, 
		'post_type' => 'food_manager_menu',
		'posts_per_page' => '-1',
		'order'         =>  'ASC' 
	];

	$wpfm_menu_lists = get_posts( $query_args );
	if( !empty( $wpfm_menu_lists ) ) :
		$items = array();       
		foreach( $wpfm_menu_lists as $wpfm_menu_list ) : 
			$items[$wpfm_menu_list->ID] =  $wpfm_menu_list->post_title;
		endforeach; ?>
	<?php endif;
	if(!empty($items)){
		return $items;
	}
}

/** 
* common error message in menu page.
*
* @since 1.0.0
*/
function error_message_for_menu_page($message){ ?>
	<div class="wpfm-alert wpfm-alert-danger">
		<?php echo esc_html($message, "wp-food-manager"); ?>
	</div>
<?php }

/** 
* common query in menu page.
*
* @since 1.0.0
*/
function food_manager_menu($restaurant_ids){
	$title_args = array(
        'post_type'   => 'food_manager_menu',
        'post_status' => 'publish',
        'post__in'    => $restaurant_ids,
        'orderby'     => 'post__in',
        'meta_query'  => array(
	        'relation' => 'OR', // Use OR for the two possible conditions
	        array(
	            'key'     => '_wpfm_food_menu_visibility',  // The postmeta key
	            'compare' => 'NOT EXISTS',                  // Include if the key does not exist
	        ),
	        array(
	            'key'     => '_wpfm_food_menu_visibility',  // The postmeta key
	            'value'   => 'yes',                         // Exclude 'yes' values
	            'compare' => '!=',                          // Only include posts where the value is NOT 'yes'
	        ),
	    ),
    );
	$food_menus = new WP_Query(apply_filters('food_manager_food_menu_args', $title_args));
	return $food_menus;
}

/** 
* This function is used to get all wpfm plugin basic information
*
* @since 1.0.6
*/
if (!function_exists('get_wpfm_plugins_info')) {
	function get_wpfm_plugins_info() {
		$plugins_info = array();
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins(); 
		
		foreach ($plugins as $filename => $plugin) {
			if ($plugin['AuthorName'] == 'WP Food Manager' && is_plugin_active($filename) && !in_array($plugin['TextDomain'], ["wp-food-manager", "wpfm-rest-api"])) {
				$plugin_info = array();
				$plugin_info['Name'] = $plugin['Name'];
				$plugin_info['TextDomain'] = $plugin['TextDomain'];
				$plugin_info['Version'] = $plugin['Version'];
				$plugin_info['Title'] = $plugin['Title'];
				$plugin_info['AuthorName'] = $plugin['AuthorName'];
				array_push($plugins_info, $plugin_info);
			}
		} 
		return $plugins_info;
	}
}

/**
 * Renders the HTML structure for a food topping.
 *
 * This function generates the markup for a food topping entry in the admin interface.
 * It displays the topping name, along with any associated fields for that topping.
 * If no topping data is provided, it shows a default message indicating that no toppings are available.
 *
 * @param int $count The index of the topping being rendered. Used for unique field names and IDs.
 * @param array|null $topping Optional. An associative array containing the topping data.
 */
function render_topping($count, $topping = null) {
	global $post, $thepostid;
	$thepostid = $post->ID;
    ?>
    <div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-<?php echo esc_attr($count); ?>">
        <input type="hidden" name="repeated_options[]" value="<?php echo esc_attr($count); ?>" class="repeated-options">
        <h3 class="">
            <a href="javascript: void(0);" data-id="<?php echo esc_attr($count); ?>" class="wpfm-delete-btn">Remove</a>
            <div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="<?php echo esc_attr($count); ?>"></div>
            <div class="wpfm-sort"></div>
            <strong class="attribute_name">
			<?php // Translators: %s is replaced with the topping name or a default option if none is provided.
			printf(esc_html__('%s', 'wp-food-manager'), esc_html($topping ? $topping['_topping_name'] : esc_html__('Option 1', 'wp-food-manager'))); ?></strong>
            <span class="attribute_key">
                <input type="hidden" name="topping_key_<?php echo esc_attr($count); ?>" value="<?php echo esc_attr($topping['topping_key'] ?? ''); ?>" readonly>
            </span>
        </h3>
        <div class="wpfm-metabox-content wpfm-options-box-<?php echo esc_attr($count); ?>">
            <div class="wpfm-content">
                <?php
                do_action('food_manager_food_data_start', $thepostid);
				$writepanels = WPFM_Writepanels::instance();
                $topping_fields = $writepanels->food_manager_data_fields();
                if (isset($topping_fields['toppings'])) {
                    foreach ($topping_fields['toppings'] as $key => $field) {
                        $field['required'] = false;
                        if (!$topping || empty($topping['_' . $key])) {
                            $field['value'] = '';
                        } else {
                            $field['value'] = $topping['_' . $key];
                        }

                        $key .= '_' . $count;

                        $type = $field['type'] ?? 'text';
                        if ($type == 'wp-editor') $type = 'wp_editor';
                        if ($type == "term-autocomplete") $type = "term_autocomplete";
                        ?>
                        <p class="wpfm-admin-postbox-form-field <?php echo esc_attr($key) . ($type == 'wp_editor' ? ' wp-editor-field' : ''); ?>" <?php echo ($type == "wp_editor" || $type == "file") ? 'data-field-name="' . esc_attr($key) . '"' : ''; ?>>
                            <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : </label>
                            <?php if ($type != 'options') echo '<span class="wpfm-input-field">'; ?>
                            <?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => esc_attr($key), 'field' => $field)); ?>
                            <?php if ($type != 'options') echo '</span>'; ?>
                        </p>
                        <?php
                    }
                }
                do_action('food_manager_food_data_end', $thepostid); ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Checks if an array is not blank.
 *
 * This function checks if any of the specified keys in the array
 * contain non-empty values. If at least one key has a non-empty value,
 * the function returns true; otherwise, it returns false.
 *
 * @param array $array The array to check for non-empty values.
 * @return bool True if the array contains at least one non-empty value; otherwise, false.
 */
function isArrayNotBlank($array) {
    foreach ($array as $item) {
        // Check if required keys are not empty
        if (!empty($item['_topping_name']) || !empty($item['_topping_description']) || !empty($item['topping_image']) || !empty($item['_topping_options'])) {
            return true; // Found a non-empty value
        }
    }
    return false; // All values are empty
}

/**
 * get ids of menu items
 *
 * @param  $menu_id 
 * @param $post
 * @return $food_menu_ids
 */
// get ids of menu items
function get_menu_list($menu_id, $post) {

	$food_menu_ids=array();
	$get_menu_options = get_post_meta($menu_id, '_food_menu_option', true); 
	if(empty($get_menu_options))
	{
		$get_menu_options = get_post_meta($post, '_food_menu_option', true); 
	}
    
    if (empty($get_menu_options) || $get_menu_options == 'static_menu') {
        if ('food_manager_menu' == get_post_type($post)) {
            $food_menu_ids = get_post_meta($post, '_food_item_ids', true);
        } elseif (isset($menu_id) && !empty($menu_id)) {
            $food_menu_ids = get_post_meta($menu_id, '_food_item_ids', true);
        }
    } else {
        if ('food_manager_menu' == get_post_type($post)) {
            $food_menu_ids = get_post_meta($post, '_wpfm_food_menu_by_days', true);
        } elseif (isset($menu_id) && !empty($menu_id)) {
            $food_menu_ids = get_post_meta($menu_id, '_wpfm_food_menu_by_days', true);
        }
        
        $current_day = date('l'); 

        // Check if $food_menu_ids is an array and fetch the food items for the current day
        if (is_array($food_menu_ids) && isset($food_menu_ids[$current_day]) && isset($food_menu_ids[$current_day]['food_items'])) {
            $food_menu_ids = $food_menu_ids[$current_day]['food_items'];
        } else {
            $food_menu_ids = array(); // Return an empty array if no items for the current day
        }
    }

    return $food_menu_ids;
}


/**
 * get_food_post_type function.
 *
 * @access public
 * @param $post_type
 * @return array
 * @since 1.0
 */
function get_food_post_type() {
    $post_types = array(
        'food_manager' => __('Food', 'wp-food-manager'),
        'food_manager_menu' => __('Food Menu', 'wp-food-manager'),
    );
	
    return apply_filters('food_post_type', $post_types);
}

/**
* get_food_terms function.
*
* @access public
* @param $post_type
* @return 
* @since 1.0
*/
function get_food_terms() {
   if (isset($_POST['taxonomy'])) {
       $terms = get_categories(array('taxonomy' => sanitize_text_field($_POST['taxonomy']), 'hide_empty' => false));
   }
   $output = '<option value="">' . __('Select option', 'wp-food-manager') . '...</option>';
   if (!empty($terms)) {
       foreach ($terms as $key => $term) {
           $output .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';
       }
   }
   $output = apply_filters('customize_food_terms', $output);
   print($output);
   wp_die();
}
  
/**
 * get_food_form_field_lists function.
 *
 * @access public
 * @param $post_type
 * @return array
 * @since 1.0
 */
 function get_food_form_field_lists($post_type) {
    $fields = [];
    if ($post_type == 'food_manager') {
		$GLOBALS['food_manager']->forms->get_form('add-food', array());
		$form_add_food_instance = call_user_func(array('WPFM_Add_Food_Form', 'instance'));
		$food_fields = $form_add_food_instance->merge_with_custom_fields('backend');
		$fields = array_merge($food_fields);
    } else if ($post_type == 'food_manager_menu') {
	
		$meta_values = array(
			'wpfm_radio_icons',
			'wpfm_disable_food_redirect',
			'wpfm_disable_food_image',
			'wpfm_food_menu_visibility',
			'food_menu_option',
			'food_item_ids',
			'food_cats_ids',
			'food_type_ids',
			'wpfm_food_menu_by_days',
			'thumbnail_id',
		);
		
		$fields = $meta_values;
		
	}
    $fields = apply_filters('wpfm_food_form_field_lists', $fields, $post_type);
    return $fields;
}

/**
 * get_file_data function.
 *
 * @access public
 * @param $type, $file
 * @return array
 */
 function get_food_file_data($type, $file) {
    $file_data = [];
    if ($type == 'csv') {
        $file_data = wpfm_get_csv_file_data($file);
    }
    do_action('wpfm_food_get_file_data', $file, $type);
    $file_data = apply_filters('wpfm_update_file_data', $file_data, $type);
    return $file_data;
}

/**
 * wpfm_get_csv_file_data function.
 *
 * @access public
 * @param $file
 * @return array
 * @since 1.0
 */
 function wpfm_get_csv_file_data($file) {
    $csv_data = [];
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
            $csv_data[] = $data;
        }
        fclose($handle);
    }
    return $csv_data;
}

function wpfm_import_food($post_type, $params) {
	$user_id = get_current_user_id();
	global $wpdb;
	
	// Check if $params is a WP_Error
	if (is_wp_error($params)) {
		return; 
	}

	$post_id = '';
	if (isset($params['_post_id']) && $params['_post_id'] != '') {
		$type = get_post_type($params['_post_id']);
		if ($post_type == $type) {
			$post_id = $params['_post_id'];
		}
	}
	if ($post_type == 'food_manager') {
		$post_title = !empty($params['_food_title']) ? $params['_food_title'] : '';
		$post_description = !empty($params['_food_description']) ? $params['_food_description'] : '';
	} else if ($post_type == 'food_manager_menu') {
		$post_title = !empty($params['_menu_title']) ? $params['_menu_title'] : '';
		$post_description ='';
	}
	$post_title = apply_filters('wpfm_food_import_set_post_title', $post_title, $params);
	// if (!empty($params['_post_id'])) {
	// 	$exist_post = get_post($params['_post_id']);
	// }

	// if (empty($params['_post_id']) && $post_title != '') {
	$args = [
		'post_title' => $post_title,
		'post_type' => $post_type,
		'post_author' => $user_id,
		'comment_status' => 'closed',
		'post_status' => 'publish',
	];
	$post_id = wp_insert_post($args);

	// }elseif (empty($exist_post)) {
	// 	// Insert custom post into the database
	// 	$wpdb->insert(
	// 		$wpdb->posts,
	// 		[
	// 			'ID'            => (int) $params['_post_id'], // Ensure it's an integer
	// 			'post_title'    => sanitize_text_field($post_title), // Sanitize title
	// 			'post_content'  => sanitize_textarea_field($post_description), // Sanitize description
	// 			'post_type'     => $post_type,
	// 			'post_author'   => (int) $user_id, // Ensure user_id is an integer
	// 			'post_date'     => current_time('mysql'),
	// 			'post_date_gmt' => current_time('mysql', 1),
	// 			'comment_status'=> 'closed',
	// 			'post_status'   => 'publish',
	// 		]
	// 	);
	// 	$post_id = $params['_post_id'];

	// 	if($post_type == "food_manager"){
	// 		// Insert WooCommerce product
	// 		$post_data = array(
	// 			'post_title'    => sanitize_text_field($post_title),
	// 			'post_content'  => sanitize_textarea_field($post_description),
	// 			'post_status'   => 'publish',
	// 			'post_author'   => (int) $user_id,
	// 			'post_type'     => 'product',
	// 			'post_parent'   => (int) $params['_post_id'], 
	// 			'post_date'     => current_time('mysql'),
	// 			'post_date_gmt' => current_time('mysql', 1),
	// 			'comment_status'=> 'closed',
	// 		);

	// 		// Insert the product post
	// 		$product_id = wp_insert_post($post_data);
	// 		// Link product to custom post
	// 		update_post_meta($product_id, '_food_id', (int) $params['_post_id']);
	// 	}
	// }
	
	// Fetch the product by meta key
	// $meta_id = $wpdb->get_var(
	// 	$wpdb->prepare(
	// 		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d LIMIT 1",
	// 		'_food_id',
	// 		(int) $post_id
	// 	)
	// );
	// Update product or process further
	if ($post_type == 'food_manager') {
		wpfm_import_food_data($post_id, $post_type, $params);
	} else if ($post_type == 'food_manager_menu') {
		wpfm_import_food_menu_data($post_id, $post_type, $params);
	}
	
	do_action('wpfm_food_import_file_data', $post_id, $post_type, $params);	
}
	

 /**
 * wpfm_import_food_data function.
 *
 * @access public
 * @param $post_id, $post_type, $params
 * @return 
 */
function wpfm_import_food_data($post_id, $post_type, $params) {
	if (!$post_id) return;

	// Prepare post data for update
	$update_food = ['ID' => $post_id];
	if (!empty($params['_food_title'])) $update_food['post_title'] = sanitize_text_field($params['_food_title']);
	if (!empty($params['_food_description'])) $update_food['post_content'] = sanitize_textarea_field($params['_food_description']);

	wp_update_post($update_food);
	$wpfm_food_import_fields = get_option('wpfm_food_import_fields', true);

	// Handle banner image
	foreach ($params as $meta_key => $meta_value) {
		if (empty($wpfm_food_import_fields[$meta_key])) continue;
		$import_fields = $wpfm_food_import_fields[$meta_key];

		if ($meta_key == '_food_banner') {
			wpfm_import_food_file_upload($post_id,$meta_value,$meta_key,$params);
		}elseif ($import_fields['taxonomy'] != '') {
			if($meta_key == 'food_manager_ingredient'){
				wpfm_import_food_ingredient($post_id, $meta_value);
			}elseif ($meta_key == 'food_manager_nutrition') {
				wpfm_import_food_nutrition($post_id, $meta_value);
			}else {
				wpfm_import_food_taxonomy_terms($post_id,  $meta_key, $meta_value, $import_fields);
			}
		}
		elseif (($meta_key == '_topping_names') || ($meta_key == '_topping_description') || ($meta_key == '_topping_image')|| ($meta_key == '_topping_options')) {
				wpfm_import_food_topping_data($post_id, $params );
		}else {
			wpfm_import_food_post_meta($post_id, $meta_key, $meta_value, $import_fields,$params);
		}
	}
}
	
/**
* save the food banner
*
* @access public
* @param $post_id,$banner_url
* @return 
*/
function wpfm_import_food_file_upload($post_id, $meta_value, $meta_key, $params) {
    $is_json = is_string($meta_value) && is_array(json_decode($meta_value, true)) ? true : false;

    if ($is_json) {
        $images = json_decode($meta_value, true);
    } else {
        if (strpos($meta_value, ',') !== false) {
            $images = explode(',', $meta_value);
        } else if (strpos($meta_value, '|') !== false) {
            $images = explode('|', $meta_value);
        } else {
            $images = [$meta_value];
        }
    }
    if (!empty($images)) {
        $img_url = [];
        foreach ($images as $url) {
            $response = image_exists($url);
            if ($response) {
                $image = upload_image($url);
                if (!empty($image)) {
                    $img_url[] = $image['image_url'];
                    // Make sure you are passing only a single image URL to attachment_url_to_postid
                    $image_post_id = attachment_url_to_postid($image['image_url']);
                }
            }
        }
        // If images are found, update the post meta
        if (!empty($img_url)) {
            update_post_meta($post_id, $meta_key, $img_url);
            if (empty($params['_thumbnail_id'])) {
                update_post_meta($post_id, '_thumbnail_id', $image_post_id);  // Use the image post ID
            }
        }
    }
}

	
/**
 * save the food taxonomy data
 *
 * @access public
 * @param $post_id,meta_key, $meta_value, $import_fields
 * @return 
 */
function wpfm_import_food_taxonomy_terms($post_id, $meta_key, $meta_value, $import_fields) {
	if ($meta_value != '') {
		$terms = explode(',', $meta_value);
		$term_ids = [];
		foreach ($terms as $term_name) {
			$term_name = sanitize_text_field(trim($term_name));
			$term = term_exists($term_name, $import_fields['taxonomy']) ?: wp_insert_term($term_name, $import_fields['taxonomy']);
			if (!is_wp_error($term)) {
				$term_ids[] = $term['term_id'];
			}
		}
		if (!empty($term_ids)) {
			if($meta_key == 'food_manager_tax_classes' ){
				wp_set_post_terms($post_id, $terms , $import_fields['taxonomy'], true);
				update_post_meta($post_id,'_tax_class_id',$term['term_id']);
				update_post_meta($post_id,'_tax_classes_cat',$term['term_id']);
			}
			elseif ($meta_key == 'food_manager_tag') {
				wp_set_post_terms($post_id, $terms , $import_fields['taxonomy'], true);
			}
			else{
				wp_set_post_terms($post_id, $term_ids, $import_fields['taxonomy'], true);
			}
		}
	} else {
		// Default term if meta value is empty
		$term_id = $import_fields['default_value'];
		if ($term_id != '') {
			wp_set_post_terms($post_id, $term_id, $import_fields['taxonomy'], true);
		}
	}
}
	
/**
 * save the food ingredients
 *
 * @access public
 * @param $post_id,$meta_value
 * @return 
 */
function wpfm_import_food_ingredient($post_id, $meta_value) {
	$ingredients = explode(',', $meta_value);
	$ingredients_meta = [];
	foreach ($ingredients as $ingredient) {
		$ingredient_parts = explode('(', $ingredient);
		$ingredient_name = trim($ingredient_parts[0]);

		// Extract quantity and unit from parentheses 
		$ingredient_quantity = '';
		$ingredient_unit = '';
		if (isset($ingredient_parts[1])) {
			preg_match('/([0-9]+)\s*([a-zA-Z]+)/', $ingredient_parts[1], $matches);
			if ($matches) {
				$ingredient_quantity = $matches[1];
				$ingredient_unit = $matches[2];
			}
		}

		// Insert ingredient into taxonomy and get term ID
		$taxonomy = 'food_manager_ingredient';
		$term = term_exists($ingredient_name, $taxonomy) ?: wp_insert_term(trim($ingredient_name), $taxonomy);
		$term_id = is_array($term) ? $term['term_id'] : $term;
		// Insert unit into taxonomy and get term ID
		$unitaxonomy = 'food_manager_unit';
		$term = term_exists(trim($ingredient_unit), $unitaxonomy) ?: wp_insert_term(trim($ingredient_unit), $unitaxonomy);
		$unit_id = is_array($term) ? $term['term_id'] : $term;

		// Build serialized ingredient structure
		$ingredients_meta[] = [
			'id' => $term_id,
			'unit_id' =>$unit_id,
			'value' => $ingredient_quantity,
			'ingredient_term_name' => $ingredient_name,
			'unit_term_name' => $ingredient_unit
		];
	}
	update_post_meta($post_id, '_food_ingredients', $ingredients_meta);
}
	
/**
 * save the food nutrition
 *
 * @access public
 * @param $post_id,$meta_value
 * @return 
 */
function wpfm_import_food_nutrition($post_id, $meta_value) {
	$nutritions = explode(',', $meta_value);
	$nutritions_meta = [];
	foreach ($nutritions as $nutrition) {
		$nutrition_parts = explode('(', $nutrition);
		$nutrition_name = trim($nutrition_parts[0]);

		// Extract quantity and unit from parentheses
		$nutrition_quantity = '';
		$nutrition_unit = '';
		if (isset($nutrition_parts[1])) {
			preg_match('/([0-9]+)\s*([a-zA-Z]+)/', $nutrition_parts[1], $matches);
			if ($matches) {
				$nutrition_quantity = $matches[1];
				$nutrition_unit = $matches[2];
			}
		}

		// Insert nutrition into taxonomy and get term ID
		$taxonomy = 'food_manager_nutrition';
		$term = term_exists($nutrition_name, $taxonomy) ?: wp_insert_term(trim($nutrition_name), $taxonomy);
		$term_id = is_array($term) ? $term['term_id'] : $term;

		$nutritions_meta[] = [
			'id' => $term_id,
			'unit_id' => '99',
			'value' => $nutrition_quantity,
			'nutrition_term_name' => $nutrition_name,
			'unit_term_name' => $nutrition_unit
		];
	}
	update_post_meta($post_id, '_food_nutritions', $nutritions_meta);
}

/**
 * save the food topping data
 *
 * @access public
 * @param $post_id,$meta_key,$meta_value
 * @return 
 */
function wpfm_import_food_topping_data($post_id, $params) {
    $topping_names = explode(',', $params['_topping_name']);
    $topping_descriptions = explode(',', $params['_topping_description']);
    $topping_images = explode(',', $params['_topping_image']);
    $topping_options = explode(';', $params['_topping_options']);

    $toppings_arr = [];
    $toppings_meta = [];

    // Loop through the toppings and process them
    foreach ($topping_names as $index => $topping_name) {
        // Ensure each value exists and is valid
        $topping_name = trim($topping_name ?? '');
        $topping_description = isset($topping_descriptions[$index]) ? trim($topping_descriptions[$index]) : '';
        $topping_image = isset($topping_images[$index]) ? trim($topping_images[$index]) : '';
        $topping_option = isset($topping_options[$index]) ? trim($topping_options[$index]) : '';

        // Handle the topping term in taxonomy
        $taxonomy = 'food_manager_topping';
        $term = term_exists(trim($topping_name), $taxonomy) ?: wp_insert_term(trim($topping_name), $taxonomy);

        if (is_wp_error($term)) {
            continue;
        }

        $term_id = is_array($term) ? $term['term_id'] : $term;
        $toppings_arr[] = $term_id;
        $topping_option_data = []; 

        if (!empty($topping_option)) {
            // Split the topping options by semicolons
            $option_pairs = explode(' ', $topping_option);
            foreach ($option_pairs as $pair) {
                $pair = trim($pair);

                // Check if the pair contains both a topping name and price
                if (preg_match('/([a-zA-Z\s]+)\s*,\s*(\d+)/', $pair, $matches)) {
                    $topping_option_data[] = ['option_name'  => trim($matches[1]),'option_price' => (float) $matches[2]];
                } elseif (preg_match('/([a-zA-Z\s]+)/', $pair, $matches)) {
                    $topping_option_data[] = ['option_name'  => trim($matches[1]), 'option_price' => ''              
                    ];
                } elseif (preg_match('/(\d+)/', $pair, $matches)) {
                    $topping_option_data[] = ['option_name'  => '', 'option_price' =>  (float) $matches[1]            
                    ];
                }
            }
        }

        // Add topping metadata for the current topping
        $toppings_meta[] = [
            '_topping_name' => $topping_name,
            '_topping_description' => '<p>' . $topping_description . '</p>',
            '_topping_image' => [$topping_image],
            '_topping_options' => $topping_option_data	
        ];
    }

    // Assign toppings terms to the post and save meta data
    if ($toppings_arr) {
        update_post_meta($post_id, '_food_toppings', $toppings_meta);
    }
}
	
/**
 * save the post meta fields
 *
 * @access public
 * @param $post_id,$meta_key, $import_fields
 * @return 
 */
function wpfm_import_food_post_meta($post_id, $meta_key, $meta_value, $import_fields, $params) {

	if (empty($meta_value) && isset($import_fields['default_value'])) {
        $meta_value = $import_fields['default_value'];
    }

    // Update the main post meta
    update_post_meta($post_id, $meta_key, sanitize_text_field($meta_value));

    // update_post_meta($meta_id, '_stock', sanitize_text_field($params['_food_quantity']));
    // update_post_meta($meta_id, '_stock_status', sanitize_text_field($params['_food_stock_status']));
    // update_post_meta($meta_id, '_sale_price', floatval($params['_food_sale_price']));
    // update_post_meta($meta_id, '_regular_price', floatval($params['_food_price']));
    // update_post_meta($meta_id, '_price', floatval($params['_food_price']));
    // update_post_meta($meta_id, '_tax_class', '');

    // $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    // update_post_meta($meta_id, '_thumbnail_id', $thumbnail_id);

    // if (!empty($params['_food_quantity'])) {
    //     update_post_meta($meta_id, '_manage_stock', 'yes');
    // }
}
			
 /**
 * wpfm_import_food_menu_data function.
 *
 * @access public
 * @param $post_id, $post_type, $params
 * @return 
 */
function wpfm_import_food_menu_data($post_id, $post_type, $params) {
    if ($post_id != '') {
        // Set the post title and content (description)
        $post_title = !empty($params['_menu_title']) ? $params['_menu_title'] : '';
        
        // Create or update the post
        $update_menu = ['ID' => $post_id];
        if ($post_title != '') {
            $update_menu['post_title'] = $post_title;
        }

        // Update the post in WordPress
        wp_update_post($update_menu);

		if(isset($params['_post_id']) && !empty($params['_post_id']))
        	update_post_meta($post_id, '_post_id', $params['_post_id']);

        // Handle fields and update post meta
        $wpfm_radio_icons = isset($params['_wpfm_radio_icons']) ? $params['_wpfm_radio_icons'] : '';
        $wpfm_disable_food_redirect = isset($params['_wpfm_disable_food_redirect']) ? $params['_wpfm_disable_food_redirect'] : '';
        $wpfm_disable_food_image = isset($params['_wpfm_disable_food_image']) ? $params['_wpfm_disable_food_image'] : '';
        $wpfm_food_menu_visibility = isset($params['_wpfm_food_menu_visibility']) ? $params['_wpfm_food_menu_visibility'] : '';
        $food_menu_option = isset($params['_food_menu_option']) ? $params['_food_menu_option'] : '';
        $image_url = isset($params['_thumbnail_id']) ? $params['_thumbnail_id'] : '';

		if (!empty($image_url)) {
			$response = image_exists($image_url);
			if ($response == 'true' || $response == 'false' ) {
				$image = upload_image($image_url);
				if (!empty($image)) {
					$imageData =  $image['image_url'];
					$image_post_id = attachment_url_to_postid($imageData);
					if ($image_post_id) {
						update_post_meta($post_id, '_thumbnail_id', $image_post_id);
					}
				}
			}
		}

		$food_cats_ids = isset($params['_food_cats_ids']) ? wpfm_get_term_id_by_name(explode(", ", $params['_food_cats_ids']), 'food_manager_category') : array();
        update_post_meta($post_id, '_food_cats_ids', $food_cats_ids);
		$food_type_ids = isset($params['_food_type_ids']) ? wpfm_get_term_id_by_name(explode(", ", $params['_food_type_ids']), 'food_manager_type') : array();
        update_post_meta($post_id, '_food_type_ids', $food_type_ids);

		// Handle other fields as normal
        update_post_meta($post_id, 'wpfm_radio_icons', $wpfm_radio_icons);
        update_post_meta($post_id, '_wpfm_disable_food_redirect', $wpfm_disable_food_redirect);
        update_post_meta($post_id, '_wpfm_disable_food_image', $wpfm_disable_food_image);
        update_post_meta($post_id, '_wpfm_food_menu_visibility', $wpfm_food_menu_visibility);
        update_post_meta($post_id, '_food_menu_option', $food_menu_option);
		
        // Initialize food IDs with empty arrays if not set
		$food_item_ids = isset($params['_food_item_ids']) ? wpfm_get_food_ids(explode(", ", $params['_food_item_ids'])) : array();
        update_post_meta($post_id, '_food_item_ids', $food_item_ids);

    	// Handle 'food menu by days' field
    	if (isset($params['_wpfm_food_menu_by_days'])) {
    	    $menu_by_days_data = json_decode($params['_wpfm_food_menu_by_days'], true);
    	    if (is_array($menu_by_days_data)) {
    	        foreach ($menu_by_days_data as $day => &$data) {
    	            if (isset($data['food_categories'])) {
						$data['food_categories'] = wpfm_get_term_id_by_name($data['food_categories'], 'food_manager_category');
    	            }
    	            if (isset($data['food_types'])) {
						$data['food_types'] = wpfm_get_term_id_by_name($data['food_types'], 'food_manager_type');
    	            }
					if(isset($data['food_items']) && !empty($data['food_items'])){
						$data['food_items'] = wpfm_get_food_ids($data['food_items']);
					}
    	        }
    	    }
    	    update_post_meta($post_id, '_wpfm_food_menu_by_days', $menu_by_days_data);
    	}
    }
}

/**
 * convert term name to term id.
 *
 * @param $term_names, $taxonomy
 * @return $term_ids
 */
function wpfm_get_term_id_by_name($term_names, $taxonomy) {
    $term_ids = [];
	if(!empty( $term_names )) {
		foreach ( $term_names as $name) {
			$term = get_term_by('name', $name, $taxonomy);

			if (!$term) {
				// If term doesn't exist, create it
				$new_term = wp_insert_term($name, $taxonomy);
				if (!is_wp_error($new_term)) {
					$term_ids[] = (string) $new_term['term_id'];
				}
			} else {
				// If term exists, get the term_id
				$term_ids[] = (string) $term->term_id;
			}
		}
	}
    
    return $term_ids;
}

/**
 * get food id if exist based on parent id.
 *
 * @param $term_names, $taxonomy
 * @return $term_ids
 */
function wpfm_get_food_ids($food_item_ids) {
	$new_food_ids = [];
    if (!empty($food_item_ids)) {
		foreach ($food_item_ids as $item_id) {
			// Query posts of type 'food_manager' with meta key '_post_id' matching $item_id
			$food_query = new WP_Query(array(
				'post_type'  => 'food_manager',
				'meta_query' => array(
					array(
						'key'     => '_post_id',
						'value'   => $item_id,
						'compare' => '='
					)
				),
				'fields' => 'ids', // Only return post IDs
				'posts_per_page' => -1
			));

			if (!empty($food_query->posts)) {
				// Store matching post IDs in the new array
				foreach ($food_query->posts as $food_id) {
					$new_food_ids[] = $food_id;
				}
			}
		}
	}    
    return $new_food_ids;
}

/**
 * Upload image function.
 *
 * @param string $url The URL of the image to be uploaded.
 * @return array|WP_Error The uploaded image data or a WP_Error object.
 */
function upload_image($url) {
	$arrData = [];

	if ($url != '') {
		// Get file name and extension
		$path_info = pathinfo($url);
		$file_name = $path_info['filename'];
		$extension = $path_info['extension'];
			
		// Get upload directory
		$upload_dir = wp_upload_dir()['basedir'];
		$upload_path = '/' . date('Y') . '/' . date('m') . '/';
			
		// Check if the file exists
		$original_file_path = $upload_dir . $upload_path . $file_name . '.' . $extension;
		if (file_exists($original_file_path)) {
		    $attachment_url = wp_upload_dir()['baseurl'] . $upload_path . $file_name . '.' . $extension;
		    $attachment_id = attachment_url_to_postid($attachment_url);
		    if ($attachment_id) {
		        $arrData['image_id'] = $attachment_id;
		        $arrData['image_url'] = wp_get_attachment_url($attachment_id);
		        return $arrData;  // Return existing image details
		    }
		}
		$count = 1;
		while (file_exists($upload_dir . $upload_path . $file_name . '-' . $count . '.' . $extension)) {
		    $file_with_number_path = $upload_dir . $upload_path . $file_name . '-' . $count . '.' . $extension;
		    if (file_exists($file_with_number_path)) {
		        $attachment_url = wp_upload_dir()['baseurl'] . $upload_path . $file_name . '-' . $count . '.' . $extension;
		        $attachment_id = attachment_url_to_postid($attachment_url);
		        if ($attachment_id) {
		            $arrData['image_id'] = $attachment_id;
		            $arrData['image_url'] = wp_get_attachment_url($attachment_id);
		            return $arrData;  // Return existing image details
		        }
		    }
		    $count++;
		}

		// If image doesn't exist, proceed with upload
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');

		$url = stripslashes($url);
		$tmp = download_url($url);

		// Check for download errors
		if (is_wp_error($tmp)) {
			// Handle the error, return the error object or log it
			return $tmp;  // You can return or log the error depending on your requirements
		}

		// Proceed with media_handle_sideload to handle the file upload
		$file_array = array(
			'name' => basename($url),
			'tmp_name' => $tmp
		);

		// Handle the file upload
		$post_id = 0;  // No specific post to attach the image to
		$image_id = media_handle_sideload($file_array, $post_id);

		// Check for errors after upload
		if (is_wp_error($image_id)) {
			@unlink($file_array['tmp_name']);
			return $image_id;  // Return the error if sideload fails
		}

		// Get the URL of the uploaded image
		$image_url = wp_get_attachment_url($image_id);

		// Prepare and return the result
		$arrData['image_id'] = $image_id;
		$arrData['image_url'] = $image_url;
	}

	return $arrData;
}
	
/**
 * Check if image exists via URL.
 *
 * @param string $url The image URL to check.
 * @return bool True if image exists, otherwise false.
 */
function image_exists($url) {
	 $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // ⛔ Not safe for production
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // ⛔ Not safe for production
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
	
	if ($http_code == 200) {
		return true;
	} else {
		return false;
	}
}
	

if (!function_exists('wpfm_export_csv_file')) {
	/**
 	* export food csv data.
 	*
 	* @param string $message.
 	* @return 
 	*/
	function wpfm_export_csv_file($message) {
		// Query to fetch 'food_manager' posts
		$query = new WP_Query([
			'post_type'      => 'food_manager',
			'posts_per_page' => -1,
		]);

		// Set headers for CSV export
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . sanitize_file_name($message) . '.csv"');

		// Open output stream
		$output = fopen('php://output', 'w');
		$headers = [
			__('_post_id', 'wp-food-manager'), __('_food_title', 'wp-food-manager'), __('_food_description', 'wp-food-manager'), __('_food_banner', 'wp-food-manager'),
			__('_food_quantity', 'wp-food-manager'), __('_food_price', 'wp-food-manager'), __('_food_sale_price', 'wp-food-manager'), __('_food_stock_status', 'wp-food-manager'),
			__('_food_label', 'wp-food-manager'), __('food_manager_tax_classes', 'wp-food-manager'), __('_food_thumbnail', 'wp-food-manager'), __('_food_reward_point', 'wp-food-manager'),
			__('_gallery_title', 'wp-food-manager'), __('food_manager_category', 'wp-food-manager'),
			__('food_manager_tag', 'wp-food-manager'), __('food_manager_type', 'wp-food-manager'),
			__('food_manager_ingredient', 'wp-food-manager'), __('food_manager_nutrition', 'wp-food-manager'),
			__('_topping_name', 'wp-food-manager'), __('_topping_description', 'wp-food-manager'), __('_topping_image', 'wp-food-manager'), __('_topping_options', 'wp-food-manager'),
			__('_enable_food_ingre', 'wp-food-manager'), __('_enable_food_nutri', 'wp-food-manager')
		];
		fputcsv($output, apply_filters('wpfm_reservation_export_file_headers', $headers));

		// Process each post
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();

				// Retrieve meta data
				$food_banner = maybe_unserialize(get_post_meta(get_the_ID(), '_food_banner', true));
				$food_banner = is_array($food_banner) ? implode(', ', $food_banner) : $food_banner;
				$attachment_id = get_post_meta(get_the_ID(), '_thumbnail_id', true);
				$thumbnail_id = wp_get_attachment_url($attachment_id);
				$tax_name = get_term_name_from_meta('_tax_classes_cat', 'food_manager_tax_classes');
				$cat_names = implode(', ', get_terms_names(get_the_ID(), 'food_manager_category'));
				$type_names = implode(', ', get_terms_names(get_the_ID(), 'food_manager_type'));
				$tag_names = implode(', ', get_terms_names(get_the_ID(), 'food_manager_tag'));
				$ingredients = get_ingredients(get_the_ID());
				$nutrition_names = get_nutritions(get_the_ID());
				$topping_data = maybe_unserialize(get_post_meta(get_the_ID(), '_food_toppings', true)); // Unserialize topping data

				// Prepare topping data
				$topping_names = [];
				$topping_descriptions = [];
				$topping_images = [];
				$topping_options = [];

				if (!empty($topping_data) && is_array($topping_data)) {
					foreach ($topping_data as $topping) {
						// Extracting each topping's details
						$topping_names[] = $topping['_topping_name'] ?? '';
						$topping_descriptions[] = strip_tags($topping['_topping_description'] ?? '');
						if (isset($topping['_topping_image'])) {
							if (is_array($topping['_topping_image'])) {
								$topping_images[] = $topping['_topping_image'][0] ?? '';
							} else {
								$topping_images[] = $topping['_topping_image'] ?? '';
							}
						}							
						// Process topping options
						$options = [];
						if (isset($topping['_topping_options']) && is_array($topping['_topping_options'])) {
							foreach ($topping['_topping_options'] as $option) {
								$options[] = $option['option_name'] . ',' . $option['option_price'];
							}
						}
						$topping_options[] = implode(' ', $options);
					}
				}

				// Prepare meta values for CSV
				$meta_values = [
					'post_id'               => get_the_ID(),
					'food_title'            => get_the_title(),
					'food_description'      => get_the_content(),
					'food_banner'           => $food_banner,
					'food_quantity'         => get_post_meta(get_the_ID(), '_food_quantity', true),
					'food_price'            => get_post_meta(get_the_ID(), '_food_price', true),
					'food_sale_price'       => get_post_meta(get_the_ID(), '_food_sale_price', true),
					'food_stock_status'     => get_post_meta(get_the_ID(), '_food_stock_status', true),
					'food_label'            => get_post_meta(get_the_ID(), '_food_label', true),
					'food_manager_tax_classes' =>$tax_name,
					'food_thumbnail'        => $thumbnail_id,
					'food_reward_point'     => get_post_meta(get_the_ID(), '_food_reward_point', true),
					'gallery_title'         => get_post_meta(get_the_ID(), '_gallery_title', true),
					'food_manager_category' => $cat_names,
					'food_manager_tag'      => $tag_names,
					'food_manager_type'     => $type_names,
					'food_manager_ingredient' => $ingredients,
					'food_manager_nutrition'=> $nutrition_names,
					'topping_names'         => implode(', ', $topping_names),
					'topping_descriptions'  => implode(', ', $topping_descriptions),
					'topping_images'        => implode(', ', $topping_images),
					'topping_options'       => implode('; ', $topping_options), // Using semicolon to separate multiple options
					'enable_food_ingre'     => get_post_meta(get_the_ID(), '_enable_food_ingre', true),
					'enable_food_nutri'     => get_post_meta(get_the_ID(), '_enable_food_nutri', true),
				];

				// Prepare CSV row
				$data = apply_filters('wpfm_reservation_export_file_data', array_values($meta_values), get_the_ID(), $meta_values);
				fputcsv($output, $data);
			}
		}
		fclose($output); // Close output stream
		exit;
	}
}
	
/**
* return term name
* @param $post_id, $taxonomy
* @return $term->name
*/
function get_terms_names($post_id, $taxonomy) {
	$terms = wp_get_post_terms($post_id, $taxonomy, true);
	return !empty($terms) && !is_wp_error($terms) ? array_map(function($term) { return $term->name; }, $terms) : [];
}
	
/**
* return term name from meta data
* @param $post_id, $taxonomy
* @return $term->name
*/
function get_term_name_from_meta($meta_key, $taxonomy) {
	$term_id = get_post_meta(get_the_ID(), $meta_key, true);
	$term = get_term($term_id, $taxonomy);
	return !is_wp_error($term) ? $term->name : '';
}
	
/**
* return ingredient details
* @param $post_id, $taxonomy
* @return $ingredient_details
*/
function get_ingredients($post_id) {
	$ingredient_meta = maybe_unserialize(get_post_meta($post_id, '_food_ingredients', true));
	$ingredient_details = [];
	if (!empty($ingredient_meta) && is_array($ingredient_meta)) {   
		foreach ($ingredient_meta as $ingredient) {
			if (isset($ingredient['ingredient_term_name'])) {
				$ingredient_details[] = $ingredient['ingredient_term_name'] . ' (' . $ingredient['value'] . ' ' . $ingredient['unit_term_name'] . ')';
			}
		}
	}
	return implode(', ', $ingredient_details);
}
	
/**
* return nutrition details
* @param $post_id
* @return $nutrition_details
*/
function get_nutritions($post_id) {
	$nutrition_meta = maybe_unserialize(get_post_meta($post_id, 'food_manager_nutrition', true));
	$nutrition_details = [];         
	if (!empty($nutrition_meta) && is_array($nutrition_meta)) {   
		foreach ($nutrition_meta as $nutrition) {
			if (isset($nutrition['nutrition_term_name'], $nutrition['unit_term_name'], $nutrition['value'])) {
				$nutrition_details[] = $nutrition['nutrition_term_name'] . ' (' . $nutrition['value'] . ' ' . $nutrition['unit_term_name'] . ')';
			}
		}
	}
	return implode(', ', $nutrition_details);
}
				
if (!function_exists('wpfm_export_menu_csv_file')) {
	/**
	 * Export food manager data as CSV file
	 * 
	 * @param string $message
	 * @return void
	 */
	function wpfm_export_menu_csv_file($message) {
		// Setup WP_Query to get the posts
		$query = new WP_Query(array(
			'post_type'      => 'food_manager_menu',
			'posts_per_page' => -1,
		));
	
		// Prepare headers to generate a CSV file with the dynamic filename
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . sanitize_file_name($message) . '.csv"');
	
		// Load WP_Filesystem API if not already loaded
		if (!function_exists('get_filesystem_method')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
	
		$output = fopen('php://output', 'w'); // phpcs:ignore
	
		// Output column headers in the CSV file
		fputcsv($output, apply_filters('wpfm_reservation_export_file_headers', array(
			__('_post_id', 'wp-food-manager'),__('_menu_title', 'wp-food-manager'),__('_wpfm_radio_icons', 'wp-food-manager'),__('_thumbnail_id', 'wp-food-manager'),
			__('_wpfm_disable_food_redirect', 'wp-food-manager'),__('_wpfm_disable_food_image', 'wp-food-manager'),__('_wpfm_food_menu_visibility', 'wp-food-manager'),
			__('_food_menu_option', 'wp-food-manager'),
			__('_food_item_ids', 'wp-food-manager'),__('_food_cats_ids', 'wp-food-manager'),__('_food_type_ids', 'wp-food-manager'),__('_wpfm_food_menu_by_days', 'wp-food-manager'),
		)));
	
		// Loop through the posts and add each row to the CSV
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
	
				// Get post meta
				$menu_by_days_data = get_post_meta(get_the_ID(), '_wpfm_food_menu_by_days', true);
				$menu_by_days = maybe_unserialize($menu_by_days_data);
				$thumbnail_url = wp_get_attachment_url(get_post_meta(get_the_ID(), '_thumbnail_id', true));
	
				// Fetch and unserialize relevant fields
				$food_item_ids = maybe_unserialize(get_post_meta(get_the_ID(), '_food_item_ids', true));
				$food_item_ids = is_array($food_item_ids) ? implode(', ', $food_item_ids) : '';
				
				$food_cats_ids = maybe_unserialize(get_post_meta(get_the_ID(), '_food_cats_ids', true));
				$food_type_ids = maybe_unserialize(get_post_meta(get_the_ID(), '_food_type_ids', true));
				$get_menu_option = get_post_meta(get_the_ID(), '_food_menu_option', true);
				// Fetch category and type names
				if($get_menu_option == 'static_menu'){
					$food_cats_names = '';
					if (!empty($food_cats_ids)) {
						$food_cats_names = get_term_names_from_ids($food_cats_ids, 'food_manager_category');
					}
					$food_type_names = '';
					if (!empty($food_type_ids)) {
						$food_type_names = get_term_names_from_ids($food_type_ids, 'food_manager_type');
					}
				}elseif($get_menu_option == 'dynamic_menu') {
					$food_cats_names = '';
					$food_type_names = '';
				}
	
				if (is_array($menu_by_days)) {
					foreach ($menu_by_days as $day => &$data) {
						if (isset($data['food_categories']) && is_array($data['food_categories'])) {
							$category_names = array();
							foreach ($data['food_categories'] as $category_id) {
								$term = get_term($category_id, 'food_manager_category');
								if (!is_wp_error($term) && $term) {
									$category_names[] = $term->name;
								}
							}
							$data['food_categories'] = $category_names;
						}
	
						if (isset($data['food_types']) && is_array($data['food_types'])) {
							$type_names = array();
							foreach ($data['food_types'] as $type_id) {
								$term = get_term($type_id, 'food_manager_type');
								if (!is_wp_error($term) && $term) {
									$type_names[] = $term->name;
								}
							}
							$data['food_types'] = $type_names;
						}
					}
				}				
				$json_menu_by_days = json_encode($menu_by_days);
				
				// Prepare meta values for the CSV
				$meta_values = array(
					'post_id' => get_the_ID(),
					'menu_title' => get_the_title(),
					'wpfm_radio_icons' => get_post_meta(get_the_ID(), 'wpfm_radio_icons', true),
					'thumbnail_id' => $thumbnail_url,
					'wpfm_disable_food_redirect' => get_post_meta(get_the_ID(), '_wpfm_disable_food_redirect', true),
					'wpfm_disable_food_image' => get_post_meta(get_the_ID(), '_wpfm_disable_food_image', true),
					'wpfm_food_menu_visibility' => get_post_meta(get_the_ID(), '_wpfm_food_menu_visibility', true),
					'food_menu_option' => get_post_meta(get_the_ID(), '_food_menu_option', true),
					'food_item_ids' => $food_item_ids,
					'food_cats_ids' => $food_cats_names,
					'food_type_ids' => $food_type_names,
					'wpfm_food_menu_by_days' => $json_menu_by_days,
				);
	
				// Write the row data to CSV
				fputcsv($output, apply_filters('wpfm_reservation_export_file_data', array_values($meta_values), get_the_ID(), $meta_values));
			}
		} else {
			fputcsv($output, array('No records found'));
		}
		fclose($output); // Close output stream
		exit;
	}
}

/**
 *Function to get term names from term IDs.
 * 
 * @param $term_ids, $taxonomy
 * @return $terms
*/
function get_term_names_from_ids($term_ids, $taxonomy) {
	if (is_array($term_ids)) {
		// Get all terms in the taxonomy, including those that may not be assigned to any food items.
		$terms = get_terms(array(
			'taxonomy' => $taxonomy,
			'include' => $term_ids,
			'fields' => 'names',
			'orderby' => 'name',
			'order' => 'ASC',
			'hide_empty' => false, // This ensures that all terms are included, even if not assigned to any food.
		));
		
		// If there's an error, return an empty string, otherwise return the terms as a comma-separated list
		return is_wp_error($terms) ? '' : implode(', ', $terms);
	}
	return '';
}
