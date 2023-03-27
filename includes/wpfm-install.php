<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WPFM_Install
 */
class WPFM_Install {

	/**
	 * Install WP Food Manager
	 */
	public static function install() {
		global $wpdb;
		self::init_user_roles();
		self::default_terms();
		// Redirect to setup screen for new installs
		if (!get_option('food_manager_version')) {
			set_transient('_food_manager_activation_redirect', 1, HOUR_IN_SECONDS);
		}
		// Update featured posts ordering.
		if (version_compare(get_option('food_manager_version', WPFM_VERSION), '2.5', '<')) {
			$wpdb->query("UPDATE {$wpdb->posts} p SET p.menu_order = 0 WHERE p.post_type='food_manager';");
		}
		// Update legacy options
		if (false === get_option('food_manager_add_food_page_id', false) && get_option('food_manager_submit_page_slug')) {
			$page_id = get_page_by_path(get_option('food_manager_submit_page_slug'))->ID;
			update_option('food_manager_add_food_page_id', $page_id);
		}
		if (false === get_option('food_manager_food_dashboard_page_id', false) && get_option('food_manager_food_dashboard_page_slug')) {
			$page_id = get_page_by_path(get_option('food_manager_food_dashboard_page_slug'))->ID;
			update_option('food_manager_food_dashboard_page_id', $page_id);
		}
		delete_transient('food_manager_addons_html');
		update_option('food_manager_version', WPFM_VERSION);
	}

	/**
	 * Update WP Food Manager
	 */
	public static function update() {
		global $wpdb;
		// 1.0.0 change field option name
		if (!empty(get_option('food_manager_form_fields', true))) {
			$all_fields = get_option('food_manager_form_fields', true);
			if (isset($all_fields) && !empty($all_fields) && is_array($all_fields)) {
				if (isset($all_fields['food']['food_address']))
					unset($all_fields['food']['food_address']);
				if (isset($all_fields['food']['food_venue_name']))
					unset($all_fields['food']['food_venue_name']);
				update_option('food_manager_submit_food_form_fields', array('food' => $all_fields['food']));
				update_option('food_manager_submit_extra_options_form_fields', array('extra_options' => $all_fields['extra_options']));
			}
		}
		delete_transient('food_manager_addons_html');
		update_option('food_manager_version', WPFM_VERSION);
	}

	/**
	 * Init user roles
	 */
	private static function init_user_roles() {
		global $wp_roles;
		if (class_exists('WP_Roles') && !isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}
		if (is_object($wp_roles)) {
			add_role('restaurant_owner', __('Restaurant Owner', 'wp-food-manager'), array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false
			));
			$capabilities = self::get_core_capabilities();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					$wp_roles->add_cap('administrator', $cap);
				}
			}
		}
	}

	/**
	 * Get capabilities
	 * @return array
	 */
	private static function get_core_capabilities() {
		return array(
			'core' => array(
				'manage_food_managers'
			),
			'food_manager' => array(
				"edit_food_manager",
				"read_food_manager",
				"delete_food_manager",
				"edit_food_managers",
				"edit_others_food_managers",
				"publish_food_managers",
				"read_private_food_managers",
				"delete_food_managers",
				"delete_private_food_managers",
				"delete_published_food_managers",
				"delete_others_food_managers",
				"edit_private_food_managers",
				"edit_published_food_managers",
				"manage_food_manager_terms",
				"edit_food_manager_terms",
				"delete_food_manager_terms",
				"assign_food_manager_terms"
			)
		);
	}

	/**
	 * Default taxonomy terms to set up in WP Food Manager.
	 *
	 * @return array Default taxonomy terms.
	 */
	private static function get_default_taxonomy_terms() {
		return array(
			'food_manager_ingredient' => array(
				'Vegetables',
				'Spices and Herbs',
				'Cereals and Pulses',
				'Meat',
				'Seafood',
				'Salt',
				'Pepper',
				'Olive oil',
				'Vegetable oil',
				'Granulated sugar',
				'Vegetable oil',
			),
			'food_manager_nutrition' => array(
				'Calcium',
				'Potassium',
				'Fiber',
				'Magnesium',
				'Vitamin A',
				'Vitamin C',
				'Vitamin E',
			),
			'food_manager_category' => array(
				'Appetizers/Starters',
				'Breakfast',
				'Dessert',
				'Beverage',
				'Main dishes',
			),
			'food_manager_topping' => array(
				'Yellow mustard',
				'Relish',
				'Vinegar',
				'Wasabi',
				'Hot sauce',
				'Dijon mustard',
				'Mayonnaise',
				'Ketchup',
				'Barbecue sauce',
				'Soy sauce',
				'Hot sauce',
			),
			'food_manager_type' => array(
				'Vegan',
				'Vegetarian',
				'Non Vegetarian',
			),
			'food_manager_unit' => array(
				'dozen',
				'gram',
				'litre',
				'degrees Celcius',
				'kg',
				'hg',
				'dag',
				'dg',
				'cg',
				'mg',
				'kL',
				'hL',
				'daL',
				'L',
				'dL',
				'cL',
				'mL',
				'pc',
			)
		);
	}

	/**
	 * default_terms function.
	 */
	private static function default_terms() {
		if (get_option('food_manager_installed_terms') == 1) {
			return;
		}
		$taxonomies = self::get_default_taxonomy_terms();
		foreach ($taxonomies as $taxonomy => $terms) {
			foreach ($terms as $term) {
				if (!get_term_by('slug', sanitize_title($term), $taxonomy)) {
					wp_insert_term($term, $taxonomy);
				}
			}
		}
		update_option('food_manager_installed_terms', 1);
	}

	/**
	 * Adds the employment type to default food types when updating from a previous WP Food Manager version.
	 */
	private static function add_food_types() {
		$taxonomies = self::get_default_taxonomy_terms();
		$terms      = $taxonomies['food_manager_type'];
		foreach ($terms as $term => $meta) {
			$term = get_term_by('slug', sanitize_title($term), 'food_manager_type');
			if ($term) {
				foreach ($meta as $meta_key => $meta_value) {
					if (!get_term_meta((int) $term->term_id, $meta_key, true)) {
						add_term_meta((int) $term->term_id, $meta_key, $meta_value);
					}
				}
			}
		}
	}
}
