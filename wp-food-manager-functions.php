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
				$food_item_ids = get_post_meta($menu_id, '_food_item_ids', true);
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
		if (function_exists('pll_current_language')) {
			$query_args['lang'] = pll_current_language();
		}

		/* This filter is documented in wp-food-manager.php */
		$query_args['lang'] = apply_filters('wpfm_lang', null);

		// Filter args.
		$query_args = apply_filters('get_food_listings_query_args', $query_args, $args);
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
			$result = new WP_Query($query_args);
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

	$r = wp_parse_args($args, $defaults);
	if (!isset($r['pad_counts']) && $r['show_count'] && $r['hierarchical']) {
		$r['pad_counts'] = true;
	}
	extract($r);

	// Store in a transient to help sites with many cats.
	if (empty($categories)) {
		$categories = get_terms($taxonomy, array(
			'orderby'         => $r['orderby'],
			'order'           => $r['order'],
			'hide_empty'      => $r['hide_empty'],
			'child_of'        => $r['child_of'],
			'exclude'         => $r['exclude'],
			'hierarchical'    => $r['hierarchical']
		));
	}

	$name       = esc_attr($name);
	$class      = esc_attr($class);
	$id = $r['id'] ? $r['id'] : $r['name'];
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
			$depth = $r['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}
		$output .= $walker->walk($categories, $depth, $r);
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
			return new WP_Error('upload', sprintf(__('"%s" (filetype %s) needs to be one of the following file types: %s.', 'wp-food-manager'), $args['file_label'], $file['type'], implode(', ', array_keys($args['allowed_mime_types']))));
		} else {
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
 * This wpfm_begnWith() Checks if given string ($str) is begin with the second parameter ($begnString) of function.
 *
 * @param string $str
 * @param string $begnString
 * @return bool
 * @since 1.0.1
 */
function wpfm_begnWith($str, $begnString) {
	$len = strlen($begnString);
	if (is_array($str)) {
		$str = '';
	}
	return (substr($str, 0, $len) === $begnString);
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
	$can_upload = is_user_logged_in() && wpfm_user_can_post_food();

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

	if ($field['type'] == 'url') {
		echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-textarea-text">';
		if (isset($field_value) && !empty($field_value) && wpfm_begnWith($field_value, "http")) {
			echo '<a target="_blank" href="' . esc_url($field_value) . '">' . sanitize_title($field['label']) . '</a>';
		} else {
			printf(__('%s', 'wp-food-manager'), sanitize_title($field['label']));
		}
		echo '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'text') {
		if (is_array($field_value)) {
			$field_value = '';
		}
		echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($field_value) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'textarea' || $field['type'] == 'wp-editor') {
		if (wpfm_begnWith($field_value, "http") || is_array($field_value)) {
			$field_value = '';
		}
		echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_html(sanitize_title($field['label'])) . '</strong></p>';
		echo '<p class="wpfm-additional-info-block-textarea-text">' . esc_html(sanitize_title($field_value)) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'multiselect') {
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		$my_value_arr = [];
		if (is_array($field_value)) {
			foreach ($field_value as $key => $my_value) {
				if (in_array(ucfirst($my_value), $field['options'])) {
					$my_value_arr[] = esc_attr($field['options'][$my_value]);
				} else {
					$my_value_arr[] = '';
				}
			}
		}
		echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_html(sanitize_title($field['label'])) . ' -</strong> ' . implode(', ', $my_value_arr) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif (isset($field['type']) && $field['type'] == 'date') {
		if (is_array($field_value)) {
			$field_value = esc_attr($field_value['0']);
		}
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(sanitize_title($field['label'])) . ' - </strong> ' . date_i18n(esc_attr($date_format), absint(strtotime($field_value))) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif (isset($field['type']) && $field['type'] == 'time') {
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong>' . printf(__('%s', 'wp-food-manager'), esc_attr(sanitize_title($field['label']))) . ' - </strong> ' . date(esc_attr($time_format), absint(strtotime($field_value))) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'file') {
		echo '<div class="wpfm-col-md-12 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left" style="margin-bottom: 20px;">';
		echo '<div class="wpfm-additional-info-block-details-content-items wpfm-additional-file-slider">';
		echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(sanitize_title($field['label'])) . ' - </strong></p>';
		if (is_array($field_value)) :
			echo '<div class="wpfm-img-multi-container">';
			foreach ($field_value as $file) :
				if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) :
					echo '<div class="wpfm-img-multiple"><img src="' . esc_attr($file) . '"></div>';
				else :
					if (!empty($file)) {
						echo '<div><div class="wpfm-icon">';
						echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(wp_basename($file)) . '</strong></p>';
						echo '<a target="_blank" href="' . esc_attr($file) . '"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>';
						echo '</div></div>';
					}
				endif;
			endforeach;
			echo '</div>';
		else :
			if (in_array(pathinfo($field_value, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) :
				echo '<div class="wpfm-img-single"><img src="' . esc_attr($field_value) . '"></div>';
			else :
				if (wpfm_begnWith($field_value, "http")) {
					echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(wp_basename($field_value)) . '</strong></p>';
					echo '<a target="_blank" href="' . esc_attr($field_value) . '"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>';
				}
			endif;
		endif;
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'radio' && array_key_exists('options', $field)) {
		$fields_val = isset($field['options'][$field_value]) ? esc_attr($field['options'][$field_value]) : '';
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($fields_val) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'term-checklist' && array_key_exists('taxonomy', $field)) {
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(sanitize_title($field['label'])) . ' - </strong>';
		if (!empty($field_value)) {
			if (is_array($field_value)) {
				$my_checks_value_arr = [];
				if (isset($field_value[$field['taxonomy']])) {
					foreach ($field_value[$field['taxonomy']] as $key => $my_value) {
						$term_name = esc_attr(sanitize_title(get_term($my_value)->name));
						$my_checks_value_arr[] = esc_attr(sanitize_title($term_name));
					}
				}
				printf(__('%s', 'wp-food-manager'),  implode(', ', $my_checks_value_arr));
			} else {
				echo !empty(get_term(ucfirst($field_value))) ? esc_attr(sanitize_title(get_term(ucfirst($field_value))->name)) : '';
			}
		}
		echo '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'checkbox' && array_key_exists('options', $field)) {
		echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-textarea-text">';
		echo '<strong>' . esc_attr($field['label']) . '</strong> - ';
		if (is_array($field_value)) {
			$my_check_value_arr = [];
			foreach ($field_value as $key => $my_value) {
				$my_check_value_arr[] = $field['options'][$my_value];
			}
			printf(__('%s', 'wp-food-manager'),  implode(', ', $my_check_value_arr));
		} else {
			if ($field_value == 1) {
				echo esc_attr("Yes");
			} else {
				echo esc_attr("No");
			}
		}
		echo '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'term-select') {
		$term_name = esc_html(sanitize_title(get_term($field_value)->name));
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($term_name) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'number') {
		if (!is_array($field_value)) {
			$field_value_count = preg_match('/^[1-9][0-9]*$/', $field_value);
			if ($field_value_count == 0) {
				$field_value = '';
			}
		} else {
			$field_value = '';
		}
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($field_value) . '</p>';
		echo '</div>';
		echo '</div>';
	} elseif ($field['type'] == 'term-multiselect') {
		echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
		echo '<div class="wpfm-additional-info-block-details-content-items">';
		echo '<p class="wpfm-additional-info-block-title">';
		echo '<strong>' . esc_attr($field['label']) . '</strong> - ';
		if (!empty($field_value)) {
			if (is_array($field_value)) {
				$my_select_value_arr = [];
				foreach ($field_value as $key => $my_value) {
					$term_name = get_term($my_value)->name;
					$my_select_value_arr[] = $term_name;
				}
				printf(__('%s', 'wp-food-manager'),  implode(', ', $my_select_value_arr));
			} else {
				echo esc_attr(sanitize_title(get_term(ucfirst($field_value))->name));
			}
		}
		echo '</p>';
		echo '</div>';
		echo '</div>';
	} else {
		if (is_array($field_value)) :
			echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
			echo '<div class="wpfm-additional-info-block-details-content-items">';
			echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr(implode(', ', $field_value)) . '</p>';
			echo '</div>';
			echo '</div>';
		else :
			echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
			echo '<div class="wpfm-additional-info-block-details-content-items">';
			echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr(ucfirst($field_value)) . '</p>';
			echo '</div>';
			echo '</div>';
		endif;
	}
}

/**
 * This wpfm_term_radio_checklist_for_food_type() Use radio inputs instead of checkboxes for term checklists in specified taxonomies such as 'food_manager_type'.
 *
 * @param array $args
 * @return array
 * @since 1.0.0
 */
function wpfm_term_radio_checklist_for_food_type($args) {
	/* Change to your required taxonomy */
	if (!empty($args['taxonomy']) && $args['taxonomy'] === 'food_manager_type') {

		// Don't override 3rd party walkers.
		if (empty($args['walker']) || is_a($args['walker'], 'Walker')) {
			if (!class_exists('WPFM_Walker_Category_Radio_Checklist_For_Food_Type')) {

				/**
				 * Custom walker for switching checkbox inputs to radio.
				 *
				 * @see Walker_Category_Checklist
				 */
				class WPFM_Walker_Category_Radio_Checklist_For_Food_Type extends Walker_Category_Checklist {
					function walk($elements, $max_depth, ...$args) {
						$output = parent::walk($elements, $max_depth, ...$args);
						$output = str_replace(
							array('type="checkbox"', "type='checkbox'"),
							array('type="radio"', "type='radio'"),
							$output
						);
						return $output;
					}
				}
			}
			$args['walker'] = new WPFM_Walker_Category_Radio_Checklist_For_Food_Type;
		}
	}

	return $args;
}

/**
 * This wpfm_isMultiArray() Checks if given array is multi-array or not.
 *
 * @return bool
 * @param mixed $a
 * @since 1.0.1
 */
function wpfm_isMultiArray($a) {
	if (is_array($a)) {
		foreach ($a as $v) if (is_array($v)) return TRUE;
	}
	return FALSE;
}

/**
 * This wpfm_category_checklist() function return the given taxnomy list array and display the category checklist html.
 *
 * @return $popular_ids
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
	$popular_ids = array();
	foreach ((array) $terms as $term) {
		$popular_ids[] = $term->term_id;
		$id      = "$taxonomy-$term->term_id";
		$checked = in_array($term->term_id, $checked_term) ? 'checked="checked"' : ''; ?>
		<li id="<?php echo $tax->name; ?>-<?php echo $id; ?>" class="<?php echo $tax->name; ?>">
			<label class="selectit">
				<input id="in-<?php echo esc_attr(sanitize_title($tax->name)); ?>-<?php echo absint($id); ?>" type="checkbox" <?php echo wp_kses_post($checked); ?> name="<?php echo esc_attr($key_name); ?>[<?php echo esc_attr($tax->name); ?>][]" value="<?php echo (int) $term->term_id; ?>" <?php disabled(!current_user_can($tax->cap->assign_terms)); ?> />
				<?php echo esc_html(apply_filters('the_category', $term->name, '', '')); ?>
			</label>
		</li>
	<?php
	}

	return $popular_ids;
}

/**
 * This wpfm_dropdown_categories() returns the given taxnomy list array and display the category dropdown html.
 *
 * @return $popular_ids
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

	$popular_ids = array();
	echo '<select name="' . esc_attr(sanitize_title($key_name)) . '" id="' . esc_attr(sanitize_title($key_name)) . '" class="postform">';
	foreach ((array) $terms as $term) {
		$popular_ids[] = absint($term->term_id);
		$selected = ($term->term_id == $selected_term) ? 'selected="selected"' : ''; ?>
		<option class="level-0" value="<?php echo (int) $term->term_id; ?>" <?php echo wp_kses_post($selected); ?>>
			<?php echo esc_html(apply_filters('the_category', $term->name, '', '')); ?>
		</option>
<?php }
	echo "</select>";

	return $popular_ids;
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