<?php

/**
 * This file contain all templates realted functions.
 * Template functions specifically created for food listings and other food related methods.
 *
 * @author WP Food Manager
 * @category Core
 */


/**
 * Returns the translated role of the current user. If that user has no role for the current blog, it returns false.
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
 * This get_food_manager_template_part() function is used to get the template part (for templates in loops).
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
 * This get_food_banner() function is used to get the food banner url if not then return placeholder image.
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

	if (is_array($food_banner)) {
		$food_banner = array_map('esc_url', $food_banner);
	}
	return apply_filters('display_food_banner', $food_banner, $post);
}

/**
 * This get_food_thumbnail() function is used to get the food Thumbnail url if not then return placeholder image.
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
	return apply_filters('display_food_thumbnail', esc_url($food_thumbnail), $post);
}

/**
 * This display_food_price_tag() function is used to display the food price tag.
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
		$food_regular_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . get_food_manager_currency_symbol() . '</span>', $formatted_sale_price);
		$food_sale_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . get_food_manager_currency_symbol() . '</span>', $formatted_regular_price);
		echo "<del> " . $food_sale_price . "</del><ins> <span class='food-manager-Price-currencySymbol'>" . $food_regular_price . "</ins>";
	}
	if (empty($regular_price) && empty($sale_price)) {
		return false;
	}
	if (empty($sale_price)) {
		echo sprintf(esc_html( $price_format ),'<span class="food-manager-Price-currencySymbol">' . esc_html( get_food_manager_currency_symbol() ) . '</span>',esc_html( $formatted_regular_price ));
	}
}

/**
 * This display_food_banner() function is used to display the food banner.
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
		echo '<img itemprop="image" content="' . esc_attr($banner) . '" src="' . esc_url(esc_attr($banner)) . '" alt="" />';
	} else if ($default) {
		echo '<img itemprop="image" content="' . esc_attr($default) . '" src="' . esc_url(esc_attr($default)) . '" alt="" />';
	} else if (is_array($banner)) {
		$banner = array_values(array_filter($banner));
		if (isset($banner[0])) {
			echo '<img itemprop="image" content="' . esc_attr($banner[0]) . '" src="' . esc_url(esc_attr($banner[0])) . '" alt="' .  '" />';
		}
	} else {
		echo '<img itemprop="image" content="' . esc_attr(apply_filters('food_manager_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg')) . '" src="' . esc_attr(apply_filters('food_manager_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg')) . '" alt="' . esc_attr(get_the_title()) . '" />';
	}
}

/**
 * This get_food_views_count() function is use to get the counts of the food views and also used at food.
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
 * This display_food_veg_nonveg_icon_tag() function is used to display the food veg or non-veg or vegan icon.
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
					$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-veg-organic.svg";
				}
				if ($wpfm_veg_nonveg_tag->slug === 'non-vegetarian') {
					$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-non-veg-organic.svg";
				}
				if ($wpfm_veg_nonveg_tag->slug === 'vegan') {
					$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-vegan-organic.svg";
				}
			} else {
				$imagePath = $image_src[0];
			}
			if (!empty($imagePath)) {
				$data_icon_label = ucwords(str_replace("-", " ", sanitize_title($wpfm_veg_nonveg_tag->slug)));
				echo '<div class="wpfm-food-type-tag ' . esc_attr( sanitize_title( $wpfm_veg_nonveg_tag->slug ) ) . '"><img alt="' . esc_attr( $wpfm_veg_nonveg_tag->slug ) . '" src="' . esc_url( $imagePath ) . '" class="wpfm-organic-tag-icon ' . esc_attr( sanitize_title( $wpfm_veg_nonveg_tag->slug ) ) . '"></div>';
			}
		}
	}
}

/**
 * This get_food_veg_nonveg_icon_tag() function is used to get the food veg or non-veg or vegan icon.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return mixed
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
 * This display_food_type() function is used to display the food type.
 *
 * @access public
 * @param mixed $post (default: null)
 * @param mixed $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_food_type($post = null, $after = '') {
	if ($food_type = get_food_type($post)) {
		if (!empty($food_type)) {
			$numType = count($food_type);
			$i = 0;
			foreach ($food_type as $type) {
				echo wp_kses(('<a href="' . get_term_link($type->term_id) . '"><span class="wpfm-food-type-text food-type ' . esc_attr(sanitize_title($type->slug)) . ' ">' . esc_attr(sanitize_text_field($type->name)) . '</span></a>'), array(
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'span' => array(
						'class'       => array()
					),
				));
				if ($numType > ++$i) {
					echo esc_attr($after);
				}
			}
		}
	}
}

/**
 * This get_food_type() function is used get the food type.
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
 * This display_food_tag() function is used to display the food tag.
 *
 * @access public
 * @param mixed $post (default: null)
 * @param mixed $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_food_tag($post = null, $after = '') {
	if ($food_tag = get_food_tag($post)) {
		if (!empty($food_tag)) {
			$numTag = count($food_tag);
			$i = 0;
			foreach ($food_tag as $tag) {
				echo wp_kses(('<a href="' . get_term_link($tag->term_id) . '"><span class="wpfm-food-tag-text food-tag ' . esc_attr(sanitize_title($tag->slug)) . ' ">' . esc_attr(sanitize_text_field($tag->name)) . '</span></a>'), array(
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'span' => array(
						'class'       => array()
					),
				));
				if ($numTag > ++$i) {
					echo esc_attr($after);
				}
			}
		}
	}
}

/**
 * This get_food_tag() function is used to get the food tag.
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
 * This display_food_category() function is used to display the food Category.
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
				echo wp_kses(('<a href="' . get_term_link($cat->term_id) . '"><span class="wpfm-food-category-text food-category ' . esc_attr(sanitize_title($cat->slug)) . ' ">' . esc_attr(sanitize_text_field($cat->name)) . '</span></a>'), array(
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'span' => array(
						'class'       => array()
					),
				));
				if ($numCategory > ++$i) {
					echo esc_attr($after);
				}
			}
		}
	} else {
		echo "-";
	}
}

/**
 * This get_food_category() function is used to get the food Category.
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
 * This display_food_ingredients() function is used to display the food ingredients.
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
					$ingredient_slug = strtolower(str_replace(" ", "_", sanitize_text_field($ingredient['ingredient_term_name'])));
					echo '<span class="food-ingredients ' . esc_attr( sanitize_title( $ingredient_slug ) ) . ' ">' . esc_html( sanitize_text_field( $ingredient['ingredient_term_name'] ) ) . ' - ' . esc_html( sanitize_text_field( $ingredient['value'] ) ) . ' ' . esc_html( sanitize_text_field( $ingredient['unit_term_name'] ) ) . '</span>';
						if ($numIngredient > ++$i) {
						echo esc_attr($after);
					}
				}
			}
		}
	}
}

/**
 * This get_food_ingredients() function is used to get the food ingredients.
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
 * This display_food_nutritions() function is used to  display the food nutritions.
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
					$nutrition_slug = strtolower(str_replace(" ", "_", sanitize_text_field($nutrition['nutrition_term_name'])));
					echo '<span class="food-nutritions ' . esc_attr( sanitize_title( $nutrition_slug ) ) . ' ">' . esc_html( sanitize_text_field( $nutrition['nutrition_term_name'] ) ) . ' - ' . esc_html( sanitize_text_field( $nutrition['value'] ) ) . ' ' . esc_html( sanitize_text_field( $nutrition['unit_term_name'] ) ) . '</span>';
					if ($numNutrition > ++$i) {
						echo esc_attr($after);
					}
				}
			}
		}
	}
}

/**
 * This get_food_nutritions() function is used to get the food nutritions.
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
 * This display_food_units() function is used to display the food Units.
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
				echo '<span class="food-units ' . esc_attr(sanitize_title($unit->slug)) . ' ">' . esc_attr(sanitize_title($unit->name)) . '</span>';
				if ($numUnit > ++$i) {
					echo esc_attr($after);
				}
			}
		}
	}
}

/**
 * This get_food_units() function is used to get the food Units.
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
 * This display_food_permalink() function is used to diplay the food permalink.
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
 * This get_food_permalink() function is used to get the food permalink.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 * @since 1.0.0
 */
function get_food_permalink($post = null) {
	$post = get_post($post);
	$link = get_permalink($post);
	return apply_filters('display_food_permalink', esc_url($link), $post);
}

/**
 * This food_manager_class() function is used to display the Class attribute with the class formatted string.
 *
 * @access public
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 * @since 1.0.0
 */
function food_manager_class($class = '', $post_id = null) {
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . esc_attr( join( ' ', get_food_manager_class( $class, $post_id ) ) ) . '"';
}

/**
 * This get_food_manager_class() function is used to get the Class with the class formatted string.
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
 * This display_food_status() function is used to outputs Of the Food status.
 * 
 * @param $post (default: null)
 * @return void
 * @since 1.0.0
 */
function display_food_status($post = null) {
	echo esc_attr(get_food_status($post));
}

/**
 * This get_food_status() function is used to gets the food status.
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
 * This display_stock_status() function is used to display the food stock status.
 *
 * @access public
 * @param $post (default: null)
 * @param $after (default: '')
 * @return void
 * @since 1.0.0
 */
function display_stock_status($post = null, $after = '') {
    $food_stock_status = get_stock_status($post);

    if (is_array($food_stock_status)) {
        //display individual stock statuses.
        foreach ($food_stock_status as $status) {
            display_single_stock_status($status);
        }
    } else if (is_string($food_stock_status) && !empty($food_stock_status)) {
        // Handling string type
        $food_stock_status_label = "";
        if ($food_stock_status == 'food_instock') {
            $food_stock_status_label = 'In stock';
        } elseif ($food_stock_status == 'food_outofstock') {
            $food_stock_status_label = 'Out of stock';
        }
        // Apply filter to the label
    	$food_stock_status_label = apply_filters('wpfm_food_stock_status_label', $food_stock_status_label);
        echo '<mark class="' . esc_attr($food_stock_status) . '">' . esc_html($food_stock_status_label) . '</mark>';
    }
}

/**
 * This display_single_stock_status() function is used to display individual stock status.
 *
 * @access public
 * @return void
 * @since 1.0.0
 */
function display_single_stock_status($food_stock_status) {
    $food_stock_status_label = "";
    if ($food_stock_status == 'food_instock') {
        $food_stock_status_label = 'In stock';
    } elseif ($food_stock_status == 'food_outofstock') {
        $food_stock_status_label = 'Out of stock';
    }
    echo '<mark class="' . esc_attr($food_stock_status) . '">' . esc_html($food_stock_status_label) . '</mark>';
}

/**
 * This get_stock_status() function is used to get the food stock status.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 * @since 1.0.0
 */
function get_stock_status($post = null) {
	$post = get_post($post);
	$stock_status = get_post_meta($post->ID, '_food_stock_status', true);
	return apply_filters('display_stock_status', $stock_status, $post);
}

/**
 * This display_food_description() is used to display the food description.
 *
 * @param int|WP_Post $post (default: null)
 * @return string
 * @since 1.0.0
 */
function display_food_description($post = null) {
	if ($food_description = get_food_description($post)) {
		echo esc_html( sanitize_textarea_field( $food_description ) );
	}
}

/**
 * This get_food_description() function is used to get the food description.
 *
 * @param int|WP_Post $post (default: null)
 * @return string|bool|null
 * @param string $description Description to be filtered.
 * @since 1.0.0
 */
function get_food_description($post = null) {
	$post = get_post($post);
	if (!$post || 'food_manager' !== $post->post_type) {
		return;
	}
	$description = apply_filters('display_food_description', get_the_content($post));

	// This Filter apply for the food description.
	return apply_filters('food_manager_get_food_description', sanitize_textarea_field($description), $post);
}

/**
 * This display_food_title() function is used to display the food title.
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
 * This get_food_title() funnction is used to get the food title.
 *
 * @param int|WP_Post $post (default: null)
 * @return string|bool|null
 * @param string $title Title to be filtered.
 * @since 1.0.0
 */
function get_food_title($post = null) {
	$post = get_post($post);
	if (!$post || 'food_manager' !== $post->post_type) {
		return;
	}
	$title = esc_html(get_the_title($post));

	//  This Filter apply for the food title.
	return apply_filters('display_food_title', sanitize_text_field($title), $post);
}

/**
 * This wpfm_allow_indexing_food_listing() function Returns if we allow indexing of a food listing.
 *
 * @param WP_Post|int|null $post (default: null)
 * @return bool
 * @param bool $index_food_listing True if we should allow indexing of food listing.
 * @since 1.0.0
 */
function wpfm_allow_indexing_food_listing($post = null) {
	$post = get_post($post);
	if ($post && $post->post_type !== 'food_manager') {
		return true;
	}
	// Only index food listings that are not expired and published.
	$index_food_listing = 'publish' === $post->post_status;
	
	// This Filter apply if we should allow indexing of food listing.
	return apply_filters('wpfm_allow_indexing_food_listing', $index_food_listing);
}

/**
 * This wpfm_output_food_listing_structured_data() function Returns if we output food listing structured data for a post.
 *
 * @param WP_Post|int|null $post (default: null)
 * @return bool
 * @param bool $output_structured_data True if we should show structured data for post.
 * @since 1.0.0
 */
function wpfm_output_food_listing_structured_data($post = null) {
	$post = get_post($post);
	if ($post && $post->post_type !== 'food_manager') {
		return false;
	}
	// Only show structured data for un-filled and published food listings.
	$output_structured_data = 'publish' === $post->post_status;

	// This Filter apply if we should output structured data.
	return apply_filters('wpfm_output_food_listing_structured_data', $output_structured_data);
}

/**
 * This wpfm_get_food_listing_structured_data() function is used to gets the structured data for the food listing.
 *
 * @see https://developers.google.com/search/docs/data-types/foods
 *
 * @param WP_Post|int|null $post (default: null)
 * @return bool|array False if functionality is disabled; otherwise array of structured data.
 * @param bool|array $structured_data False if functionality is disabled; otherwise array of structured data.
 * @since 1.0.0
 */
function wpfm_get_food_listing_structured_data($post = null) {
	$post = get_post($post);
	if ($post && $post->post_type !== 'food_manager') {
		return false;
	}
	$food_banner = get_food_banner($post);
	if( is_array($food_banner) ){
		$food_banner = array_map('esc_url', get_food_banner($post));
	}else{
		$food_banner = esc_url(get_food_banner($post));
	}
	$data = array();
	$data['@context'] = 'http://schema.org/';
	$data['@type'] = 'food';
	$food_expires = get_post_meta($post->ID, '_food_expires', true);
	if (!empty($food_expires)) {
		$data['validThrough'] = date('c', strtotime($food_expires));
	}
	$data['description'] = sanitize_textarea_field(get_food_description($post));
	$data['name'] = sanitize_text_field(strip_tags(get_food_title($post)));
	$data['image'] = $food_banner;
	$data['foodStatus'] = 'foodScheduled';
	
	// Filter the structured data for a food listing.
	return apply_filters('wpfm_get_food_listing_structured_data', $data, $post);
}

/**
 * Callback to set up the metabox.
 * Mimicks the traditional hierarchical term metabox, but modified with our nonces.
 * 
 * @access public
 * @param object $post
 * @param array $box
 * @return void
 * @since 1.0.1
 */
function replace_food_manager_type_metabox($post, $box) {
	$defaults = array('taxonomy' => 'category');

	if (!isset($box['args']) || !is_array($box['args'])) {
		$args = array();
	} else {
		$args = $box['args'];
	}

	$food_taxonomy = wp_parse_args($args, $defaults);
	$tax_name = esc_attr($food_taxonomy['taxonomy']);
	$taxonomy = get_taxonomy($food_taxonomy['taxonomy']);
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
						<input id="in-<?php echo esc_attr($id); ?>" type="radio" <?php echo esc_attr($checked); ?> name="tax_input[<?php echo esc_attr($tax_name); ?>][]" value="<?php echo esc_attr((int) $term->term_id); ?>" <?php disabled(!current_user_can($taxonomy->cap->assign_terms)); ?> />
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
					printf( esc_html__( '+ %s', 'wp-food-manager' ), esc_html( $taxonomy->labels->add_new_item ) );

					?>
				</a>
				<p id="<?php echo esc_attr($tax_name); ?>-add" class="category-add wp-hidden-child">
					<label class="screen-reader-text" for="new<?php echo esc_attr($tax_name); ?>"><?php echo esc_html($taxonomy->labels->add_new_item); ?></label>
					<input type="text" name="new<?php echo esc_attr($tax_name); ?>" id="new<?php echo esc_attr($tax_name); ?>" class="form-required form-input-tip" value="<?php echo esc_attr($taxonomy->labels->new_item_name); ?>" aria-required="true" />
					<label class="screen-reader-text" for="new<?php echo esc_attr($tax_name); ?>_parent">
						<?php echo esc_html($taxonomy->labels->parent_item_colon); ?>
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
function display_menu_qr_code(){
	global $post;
        
        // Get the Post ID and Post URL
        $menu_id = $post->ID;
        $post_url = get_permalink($menu_id);  // Get the URL of the post
    
        // Check if the QR code class exists and include it if it doesn't
        if(!class_exists('QRcode')) {
            require_once WPFM_PLUGIN_DIR . '/includes/lib/phpqrcode/qrlib.php';
        }
    
        // Define the path to store the generated QR code image
        $upload_dir = wp_upload_dir(); // Get the upload directory
        $qr_code_image = $upload_dir['path'] . "/qr_code_$menu_id.png"; // Path for the QR code image
        
        // Generate QR code image
        QRcode::png($post_url, $qr_code_image, 'L', 4, 2);  // 'L' for low error correction, 4 is the size, 2 is the margin
		$qr_code_url = $upload_dir['url'] . "/qr_code_$menu_id.png";

        // Output the QR code image and the download button
	    echo '<div class="qr_code-actions">';
	     // Print button
		 echo '<a href="javascript:void(0)" class="qr_print_button button button-icon wpfm-tooltip" wpfm-data-tip="' . esc_attr(sprintf(__('Print', 'wpfm-food-manager'))) . '"><span class="dashicons dashicons-printer"></span> </a>';
	    echo '<a href="' . $qr_code_url . '" download="QR_Code_' . $menu_id . '.png" class="button button-icon wpfm-tooltip" wpfm-data-tip="' . esc_attr(sprintf(__('Download', 'wpfm-restaurant-manager'))) . '"><span class="dashicons dashicons-download"></span></a>';
	    echo '<a href="javascript:void(0)" class="qr_preview button button-icon wpfm-tooltip" wpfm-data-tip="' . esc_attr(sprintf(__('Qr Code', 'wpfm-restaurant-manager'))) . '"><span class="dashicons dashicons-visibility"></span></a>';
	    echo '<div class="qrcode_img" style="display: none"><div class="qr_code-modal"><h2>QR Code Scan</h2><img src="' . $qr_code_url . '" alt="QR Code"><span class="dashicons dashicons-no-alt"></span></div></div>';
	    echo '</div>';
}
