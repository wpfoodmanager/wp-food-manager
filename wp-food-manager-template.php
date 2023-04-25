<?php

/**
 * Template Functions
 * Template functions specifically created for food listings and other food related methods.
 *
 * @author WP Food Manager
 * @category Core
 */

/**
 * Returns the translated role of the current user. If that user has
 * no role for the current blog, it returns false.
 *
 * @return string The name of the current role
 * @since 1.0.0
 */
function get_food_manager_current_user_role() {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role]) : false;
}

/**
 * Returns the registration fields used when an account is required.
 *
 * @return array $registration_fields
 * @since 1.0.0
 */
function wpfm_get_registration_fields() {
	$generate_username_from_email      = food_manager_generate_username_from_email();
	$use_standard_password_setup_email = food_manager_use_standard_password_setup_email();
	$account_required  = food_manager_user_requires_account();
	$registration_fields = array();
	if (food_manager_enable_registration()) {
		$registration_fields['create_account_email'] = array(
			'type'        => 'text',
			'label'       => __('Your email', 'wp-food-manager'),
			'placeholder' => __('you@yourdomain.com', 'wp-food-manager'),
			'required'    => $account_required,
			'value'       => isset($_POST['create_account_email']) ? sanitize_email($_POST['create_account_email']) : '',
		);
		if (!$generate_username_from_email) {
			$registration_fields['create_account_username'] = array(
				'type'     => 'text',
				'label'    => __('Username', 'wp-food-manager'),
				'required' => $account_required,
				'value'    => isset($_POST['create_account_username']) ? sanitize_text_field($_POST['create_account_username']) : '',
			);
		}
		if (!$use_standard_password_setup_email) {
			$registration_fields['create_account_password'] = array(
				'type'         => 'password',
				'label'        => __('Password', 'wp-food-manager'),
				'placeholder' => __('Password', 'wp-food-manager'),
				'autocomplete' => false,
				'required'     => $account_required,
			);
			$password_hint = food_manager_get_password_rules_hint();
			if ($password_hint) {
				$registration_fields['create_account_password']['description'] = $password_hint;
			}
			$registration_fields['create_account_password_verify'] = array(
				'type'         => 'password',
				'label'        => __('Verify Password', 'wp-food-manager'),
				'placeholder' => __('Confirm Password', 'wp-food-manager'),
				'autocomplete' => false,
				'required'     => $account_required,
			);
		}
	}
	return apply_filters('food_manager_get_registration_fields', $registration_fields);
}

/**
 * Get and include template files.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 * @since 1.0.0
 */
function get_food_manager_template($template_name, $args = array(), $template_path = 'wp-food-manager', $default_path = '') {
	if ($args && is_array($args)) {
		extract($args);
	}
	include(locate_food_manager_template($template_name, $template_path, $default_path));
}

/**
 * Locate a template and return the path for inclusion.
 * This is the load order:
 *
 * wp-food-manager	/ $template_path / $template_name
 * wp-food-manager	/ $template_name
 * $default_path	/ $template_name
 *
 * @param string $template_name
 * @param string $template_path (default: 'wp-food-manager')
 * @param string|bool $default_path (default: '') False to not load a default
 * @return string
 * @since 1.0.0
 */
function locate_food_manager_template($template_name, $template_path = 'wp-food-manager', $default_path = '') {
	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit($template_path) . $template_name,
			$template_name
		)
	);
	// Get default template
	if (!$template && $default_path !== false) {
		$default_path = $default_path ? $default_path : WPFM_PLUGIN_DIR . '/templates/';
		if (file_exists(trailingslashit($default_path) . $template_name)) {
			$template = trailingslashit($default_path) . $template_name;
		}
	}
	// Return what we found
	return apply_filters('food_manager_locate_template', $template, $template_name, $template_path);
}

/**
 * Get template part (for templates in loops).
 *
 * @param string $slug
 * @param string $name (default: '')
 * @param string $template_path (default: 'wp-food-manager')
 * @param string|bool $default_path (default: '') False to not load a default
 * @since 1.0.0
 */
function get_food_manager_template_part($slug, $name = '', $template_path = 'wp-food-manager', $default_path = '') {
	$template = '';
	if ($name) {
		$template = locate_food_manager_template("{$slug}-{$name}.php", $template_path, $default_path);
	}
	if (!$template) {
		$template = locate_food_manager_template("{$slug}.php", $template_path, $default_path);
	}
	if ($template) {
		load_template($template, false);
	}
}

/**
 * get_food_banner function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 * @since 1.0.0
 */
function get_food_banner($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager')
		return;
	if (isset($post->_food_banner) && empty($post->_food_banner))
		$food_banner = apply_filters('wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg');
	else
		$food_banner = $post->_food_banner;
	return apply_filters('display_food_banner', $food_banner, $post);
}

/**
 * get_food_thumbnail function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @param string $size (default: 'full')
 * @return string
 * @since 1.0.0
 */
function get_food_thumbnail($post = null, $size = 'full') {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager')
		return;
	$food_thumbnail = get_the_post_thumbnail_url($post->ID, $size);
	if (isset($food_thumbnail) && empty($food_thumbnail))
		$food_thumbnail = apply_filters('wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg');
	return apply_filters('display_food_thumbnail', $food_thumbnail, $post);
}

/**
 * display_food_price_tag function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 * @since 1.0.0
 */
function display_food_price_tag($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager')
		return;
	$price_decimals = wpfm_get_price_decimals();
	$price_format = get_food_manager_price_format();
	$price_thousand_separator = wpfm_get_price_thousand_separator();
	$price_decimal_separator = wpfm_get_price_decimal_separator();
	$sale_price = get_post_meta($post->ID, '_food_sale_price', true);
	$regular_price = get_post_meta($post->ID, '_food_price', true);
	if (!empty($sale_price)) {
		$formatted_sale_price = number_format($sale_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
	}
	if (!empty($regular_price)) {
		$formatted_regular_price = number_format($regular_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
	}
	if (!empty($regular_price) && !empty($sale_price)) {
		$f_regular_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . get_food_manager_currency_symbol() . '</span>', $formatted_sale_price);
		$f_sale_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . get_food_manager_currency_symbol() . '</span>', $formatted_regular_price);
		echo "<del> " . $f_sale_price . "</del><ins> <span class='food-manager-Price-currencySymbol'>" . $f_regular_price . "</ins>";
	}
	if (empty($regular_price) && empty($sale_price)) {
		return false;
	}
	if (empty($sale_price)) {
		echo sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . get_food_manager_currency_symbol() . '</span>', $formatted_regular_price);
	}
}

/**
 * display_food_banner function.
 *
 * @access public
 * @param string $size (default: 'full')
 * @param mixed $default (default: null)
 * @param mixed $post (default: null)
 * @return void
 * @since 1.0.0
 */
function display_food_banner($size = 'full', $default = null, $post = null) {
	$banner = get_food_banner($post);
	if (!empty($banner) && !is_array($banner)  && (strstr($banner, 'http') || file_exists($banner))) {
		echo '<img itemprop="image" content="' . esc_attr($banner) . '" src="' . esc_attr($banner) . '" alt="" />';
	} else if ($default) {
		echo '<img itemprop="image" content="' . esc_attr($default) . '" src="' . esc_attr($default) . '" alt="" />';
	} else if (is_array($banner)) {
		$banner = array_values(array_filter($banner));
		if( isset($banner[0]) ){
			echo '<img itemprop="image" content="' . esc_attr($banner[0]) . '" src="' . esc_attr($banner[0]) . '" alt="' .  '" />';
		}
	} else {
		echo '<img itemprop="image" content="' . esc_attr(apply_filters('food_manager_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg')) . '" src="' . esc_attr(apply_filters('food_manager_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg')) . '" alt="' . esc_attr(get_the_title()) . '" />';
	}
}

/** This function is use to get the counts the food views.
 *  This function also used at food.
 * 
 *  @return number counted view.
 *  @param mixed $post
 *  @since 1.0.0
 **/
function get_food_views_count($post) {
	$count_key = '_view_count';
	$count = get_post_meta($post->ID, $count_key, true);
	if ($count == '' || $count == null) {
		delete_post_meta($post->ID, $count_key);
		add_post_meta($post->ID, $count_key, '0');
		return "-";
	}
	return $count;
}

/**
 * display_food_veg_nonveg_icon_tag function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @param string $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_food_veg_nonveg_icon_tag($post = null, $after = '') {
	$wpfm_veg_nonveg_tags = get_food_veg_nonveg_icon_tag($post);
	$image_id = '';
	if (!empty($wpfm_veg_nonveg_tags)) {
		$image_id = get_term_meta($wpfm_veg_nonveg_tags[0]->term_id, 'image_id', true);
	}
	$image_src = wp_get_attachment_image_src($image_id);
	if (!empty($wpfm_veg_nonveg_tags)) {
		foreach ($wpfm_veg_nonveg_tags as $wpfm_veg_nonveg_tag) {
			$imagePath = '';
			if (empty($image_src)) {
				if ($wpfm_veg_nonveg_tag->slug === 'vegetarian') {
					$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-veg-organic.png";
				}
				if ($wpfm_veg_nonveg_tag->slug === 'non-vegetarian') {
					$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-non-veg-organic.png";
				}
				if ($wpfm_veg_nonveg_tag->slug === 'vegan') {
					$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-vegan-organic.png";
				}
			} else {
				$imagePath = $image_src[0];
			}
			if (!empty($imagePath)) {
				$data_icon_label = ucwords(str_replace("-", " ", $wpfm_veg_nonveg_tag->slug));
				echo '<div class="parent-organic-tag ' . $wpfm_veg_nonveg_tag->slug . '" data-icon-type="' . $data_icon_label . '"><img alt="' . $wpfm_veg_nonveg_tag->slug . '" src="' . $imagePath . '" class="wpfm-organic-tag-icon ' . $wpfm_veg_nonveg_tag->slug . '"></div>';
			}
		}
	}
}

/**
 * get_food_veg_nonveg_icon_tag function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 * @since 1.0.0
 */
function get_food_veg_nonveg_icon_tag($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager' || !get_option('food_manager_enable_food_types')) {
		return;
	}
	$wpfm_veg_nonveg_tag = wp_get_post_terms($post->ID, 'food_manager_type');
	return apply_filters('display_food_veg_nonveg_icon_tag', $wpfm_veg_nonveg_tag, $post);
}

/**
 * display_food_type function.
 *
 * @access public
 * @return void
 * @since 1.0.0
 */
function display_food_type($post = null, $after = '') {
	if ($food_type = get_food_type($post)) {
		if (!empty($food_type)) {
			$numType = count($food_type);
			$i = 0;
			foreach ($food_type as $type) {
				echo wp_kses(('<a href="' . get_term_link($type->term_id) . '"><span class="wpfm-food-type-text food-type ' . esc_attr(sanitize_title($type->slug)) . ' ">' . $type->name . '</span></a>'), array(
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'span' => array(
						'class'       => array()
					),
				));
				if ($numType > ++$i) {
					echo $after;
				}
			}
		}
	}
}

/**
 * get_food_type function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return mixed
 * @since 1.0.0
 */
function get_food_type($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager' || !get_option('food_manager_enable_food_types')) {
		return;
	}
	$types = wp_get_post_terms($post->ID, 'food_manager_type');
	if (empty($types))
		$types = '';
	return apply_filters('display_food_type', $types, $post);
}

/**
 * display_food_tag function.
 *
 * @access public
 * @return void
 * @since 1.0.0
 */
function display_food_tag($post = null, $after = '') {
	if ($food_tag = get_food_tag($post)) {
		if (!empty($food_tag)) {
			$numTag = count($food_tag);
			$i = 0;
			foreach ($food_tag as $tag) {
				echo wp_kses(('<a href="' . get_term_link($tag->term_id) . '"><span class="wpfm-food-tag-text food-tag ' . esc_attr(sanitize_title($tag->slug)) . ' ">' . $tag->name . '</span></a>'), array(
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'span' => array(
						'class'       => array()
					),
				));
				if ($numTag > ++$i) {
					echo $after;
				}
			}
		}
	}
}

/**
 * get_food_tag function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return mixed
 * @since 1.0.0
 */
function get_food_tag($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager' || !get_option('food_manager_enable_food_tags')) {
		return;
	}
	$tags = wp_get_post_terms($post->ID, 'food_manager_tag');
	if (empty($tags))
		$tags = '';
	return apply_filters('display_food_tag', $tags, $post);
}

/**
 * display_food_category function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @param string $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_food_category($post = null, $after = '') {
	if ($food_category = get_food_category($post)) {
		if (!empty($food_category)) {
			$numCategory = count($food_category);
			$i = 0;
			foreach ($food_category as $cat) {
				echo wp_kses(('<a href="' . get_term_link($cat->term_id) . '"><span class="wpfm-food-cat-text food-category ' . esc_attr(sanitize_title($cat->slug)) . ' ">' . $cat->name . '</span></a>'), array(
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'span' => array(
						'class'       => array()
					),

				));
				if ($numCategory > ++$i) {
					echo $after;
				}
			}
		}
	} else {
		echo "-";
	}
}

/**
 * get_food_category function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return mixed
 * @since 1.0.0
 */
function get_food_category($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager' || !get_option('food_manager_enable_categories')) {
		return;
	}
	$categories = wp_get_post_terms($post->ID, 'food_manager_category');
	return apply_filters('display_food_category', $categories, $post);
}

/**
 * display_food_ingredients function.
 *
 * @access public
 * @param $post (default: null)
 * @param $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_food_ingredients($post = null, $after = '') {
	if ($food_ingredients = get_food_ingredients($post)) {
		if (!empty($food_ingredients)) {
			$numIngredient = count($food_ingredients);
			$i = 0;
			foreach ($food_ingredients as $ingredient) {
				$ingTerm = get_term(
					!empty($ingredient['id']) ? absint($ingredient['id']) : 0,
					'food_manager_ingredient'
				);
				if (!empty($ingTerm->term_id)) {
					$ingredient_slug = strtolower(str_replace(" ", "_", $ingredient['ingredient_term_name']));
					echo '<span class="food-ingredients ' . esc_attr(sanitize_title($ingredient_slug)) . ' ">' . $ingredient['ingredient_term_name'] . ' - ' . $ingredient['value'] . ' ' . $ingredient['unit_term_name'] . '</span>';
					if ($numIngredient > ++$i) {
						echo $after;
					}
				}
			}
		}
	}
}

/**
 * get_food_ingredients function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return mixed
 * @since 1.0.0
 */
function get_food_ingredients($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager') {
		return;
	}
	$ingredients = get_post_meta(get_the_ID(), '_food_ingredients', true);
	return apply_filters('display_food_ingredients', $ingredients, $post);
}

/**
 * display_food_nutritions function.
 * 
 * @access public
 * @param $post (default: null)
 * @param $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_food_nutritions($post = null, $after = '') {
	if ($food_nutritions = get_food_nutritions($post)) {
		if (!empty($food_nutritions)) {
			$numNutrition = count($food_nutritions);
			$i = 0;
			foreach ($food_nutritions as $nutrition) {
				$nutriTerm = get_term(
					!empty($nutrition['id']) ? absint($nutrition['id']) : 0,
					'food_manager_nutrition'
				);
				if (!empty($nutriTerm->term_id)) {
					$nutrition_slug = strtolower(str_replace(" ", "_", $nutrition['nutrition_term_name']));
					echo '<span class="food-nutritions ' . esc_attr(sanitize_title($nutrition_slug)) . ' ">' . $nutrition['nutrition_term_name'] . ' - ' . $nutrition['value'] . ' ' . $nutrition['unit_term_name'] . '</span>';
					if ($numNutrition > ++$i) {
						echo $after;
					}
				}
			}
		}
	}
}

/**
 * get_food_nutritions function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return mixed
 * @since 1.0.0
 */
function get_food_nutritions($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager') {
		return;
	}
	$nutritions = get_post_meta(get_the_ID(), '_food_nutritions', true);
	return apply_filters('display_food_nutritions', $nutritions, $post);
}

/**
 * display_food_units function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @param mixed $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_food_units($post = null, $after = '') {
	if ($food_units = get_food_units($post)) {
		if (!empty($food_units)) {
			$numUnit = count($food_units);
			$i = 0;
			foreach ($food_units as $unit) {
				echo '<span class="food-units ' . esc_attr(sanitize_title($unit->slug)) . ' ">' . $unit->name . '</span>';
				if ($numUnit > ++$i) {
					echo $after;
				}
			}
		}
	}
}

/**
 * get_food_units function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return mixed
 * @since 1.0.0
 */
function get_food_units($post = null) {
	$post = get_post($post);
	if ($post->post_type !== 'food_manager') {
		return;
	}
	$units = wp_get_post_terms($post->ID, 'food_manager_unit');
	return apply_filters('display_food_units', $units, $post);
}

/**
 * display_food_permalink function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 * @since 1.0.0
 */
function display_food_permalink($post = null) {
	echo esc_attr(get_food_permalink($post));
}

/**
 * get_food_permalink function
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 * @since 1.0.0
 */
function get_food_permalink($post = null) {
	$post = get_post($post);
	$link = get_permalink($post);
	return apply_filters('display_food_permalink', $link, $post);
}

/**
 * food_manager_class function.
 *
 * @access public
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 * @since 1.0.0
 */
function food_manager_class($class = '', $post_id = null) {
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . join(' ', get_food_manager_class($class, $post_id)) . '"';
}

/**
 * get_food_manager_class function.
 *
 * @access public
 * @param $class (default: '')
 * @param $post_id (default: null)
 * @return array
 * @since 1.0.0
 */
function get_food_manager_class($class = '', $post_id = null) {
	$post = get_post($post_id);
	if ($post->post_type !== 'food_manager') {
		return array();
	}
	$classes = array();
	if (empty($post)) {
		return $classes;
	}
	$classes[] = 'food_manager';
	if ($food_type = get_food_type()) {
		if ($food_type && !empty($food_type)) {
			foreach ($food_type as $type) {
				$classes[] = 'food-type-' . sanitize_title($type->name);
			}
		}
	}
	if (!empty($class)) {
		if (!is_array($class)) {
			$class = preg_split('#\s+#', $class);
		}
		$classes = array_merge($classes, $class);
	}
	return get_post_class($classes, $post->ID);
}

/**
 * Outputs the Foods status.
 * 
 * @param $post (default: null)
 * @return void
 * @since 1.0.0
 */
function display_food_status($post = null) {
	echo esc_attr(get_food_status($post));
}

/**
 * Gets the food status.
 * 
 * @param $post (default: null)
 * @return string
 * @since 1.0.0
 */
function get_food_status($post = null) {
	$post     = get_post($post);
	$status   = $post->post_status;
	$statuses = get_food_listing_post_statuses();
	if (isset($statuses[$status])) {
		$status = $statuses[$status];
	} else {
		$status = __('Inactive', 'wp-food-manager');
	}
	return apply_filters('display_food_status', $status, $post);
}

/**
 * display_stock_status function.
 *
 * @access public
 * @param $post (default: null)
 * @param $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_stock_status($post = null, $after = '') {
	if ($food_stock_status = get_stock_status($post)) {
		if (!empty($food_stock_status)) {
			$food_stock_status_label = "";
			if ($food_stock_status == 'fm_instock') {
				$food_stock_status_label = 'In stock';
			}
			if ($food_stock_status == 'fm_outofstock') {
				$food_stock_status_label = 'Out of stock';
			}
			echo '<mark class="' . $food_stock_status . '">' . esc_html($food_stock_status_label) . '</mark>';
		}
	}
}

/**
 * get_stock_status function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 * @since 1.0.0
 */
function get_stock_status($post = null) {
	$post = get_post($post);
	$stock_status = get_post_meta(get_the_ID(), '_food_stock_status', true);
	return apply_filters('display_stock_status', $stock_status, $post);
}

/**
 * Display the food description.
 *
 * @param int|WP_Post $post (default: null)
 * @return string
 * @since 1.0.0
 */
function display_food_description($post = null) {
	if ($food_description = get_food_description($post)) {
		echo esc_attr($food_description);
	}
}

/**
 * Get the food description.
 *
 * @param int|WP_Post $post (default: null)
 * @return string|bool|null
 * @since 1.0.0
 */
function get_food_description($post = null) {
	$post = get_post($post);
	if (!$post || 'food_manager' !== $post->post_type) {
		return;
	}
	$description = apply_filters('display_food_description', get_the_content($post));
	/**
	 * Filter for the food description.
	 *
	 * @param string $description Description to be filtered.
	 * @param int|WP_Post $post
	 * @since 1.0.0
	 */
	return apply_filters('food_manager_get_food_description', $description, $post);
}

/**
 * Display the food.
 *
 * @param int|WP_Post $post
 * @return string
 * @since 1.0.0
 */
function display_food_title($post = null) {
	if ($food_title = get_food_title($post)) {
		echo esc_attr($food_title);
	}
}

/**
 * Get the food title.
 *
 * @param int|WP_Post $post (default: null)
 * @return string|bool|null
 * @since 1.0.0
 */
function get_food_title($post = null) {
	$post = get_post($post);
	if (!$post || 'food_manager' !== $post->post_type) {
		return;
	}
	$title = esc_html(get_the_title($post));
	/**
	 * Filter for the food title.
	 *
	 * @param string $title Title to be filtered.
	 * @param int|WP_Post $post
	 * @since 1.0.0
	 */
	return apply_filters('display_food_title', $title, $post);
}

/**
 * Returns if we allow indexing of a food listing.
 *
 * @param WP_Post|int|null $post (default: null)
 * @return bool
 * @since 1.0.0
 */
function wpfm_allow_indexing_food_listing($post = null) {
	$post = get_post($post);
	if ($post && $post->post_type !== 'food_manager') {
		return true;
	}
	// Only index food listings that are not expired and published.
	$index_food_listing = 'publish' === $post->post_status;
	/**
	 * Filter if we should allow indexing of food listing.
	 *
	 * @param bool $index_food_listing True if we should allow indexing of food listing.
	 * @since 1.0.0
	 */
	return apply_filters('wpfm_allow_indexing_food_listing', $index_food_listing);
}

/**
 * Returns if we output food listing structured data for a post.
 *
 * @param WP_Post|int|null $post (default: null)
 * @return bool
 * @since 1.0.0
 */
function wpfm_output_food_listing_structured_data($post = null) {
	$post = get_post($post);
	if ($post && $post->post_type !== 'food_manager') {
		return false;
	}
	// Only show structured data for un-filled and published food listings.
	$output_structured_data = 'publish' === $post->post_status;
	/**
	 * Filter if we should output structured data.
	 *
	 * @param bool $output_structured_data True if we should show structured data for post.
	 * @since 1.0.0
	 */
	return apply_filters('wpfm_output_food_listing_structured_data', $output_structured_data);
}

/**
 * Gets the structured data for the food listing.
 *
 * @see https://developers.google.com/search/docs/data-types/foods
 *
 * @param WP_Post|int|null $post (default: null)
 * @return bool|array False if functionality is disabled; otherwise array of structured data.
 * @since 1.0.0
 */
function wpfm_get_food_listing_structured_data($post = null) {
	$post = get_post($post);
	if ($post && $post->post_type !== 'food_manager') {
		return false;
	}
	$data = array();
	$data['@context'] = 'http://schema.org/';
	$data['@type'] = 'food';
	$food_expires = get_post_meta($post->ID, '_food_expires', true);
	if (!empty($food_expires)) {
		$data['validThrough'] = date('c', strtotime($food_expires));
	}
	$data['description'] = get_food_description($post);
	$data['name'] = strip_tags(get_food_title($post));
	$data['image'] = get_food_banner($post);
	$data['foodStatus'] = 'foodScheduled';
	/**
	 * Filter the structured data for a food listing.
	 *
	 * @param bool|array $structured_data False if functionality is disabled; otherwise array of structured data.
	 * @param WP_Post $post
	 * @since 1.0.0
	 */
	return apply_filters('wpfm_get_food_listing_structured_data', $data, $post);
}
