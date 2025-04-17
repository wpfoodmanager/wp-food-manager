<?php

/**
 * WPFM_Custom_Taxonomies class.
 */
class WPFM_Custom_Taxonomies{
	/**
	 * Register of food manager taxonomies.
	 *
	 * @access public
	 * @return void
	 */
	public static function register_post_taxonomies(){
		/**
		 * Taxonomies
		 */
		$permalink_structure = WPFM_Post_Types::get_permalink_structure();
		$admin_capability = 'manage_food_managers';

		if (get_option('food_manager_enable_categories', true)) {
			$singular = esc_html__('Food Category', 'wp-food-manager');
			$plural = esc_html__('Categories', 'wp-food-manager');

			if (current_theme_supports('food-manager-templates')) {
				$rewrite = array(
					'slug' => esc_attr($permalink_structure['category_rewrite_slug']),
					'with_front' => false,
					'hierarchical' => false
				);
				$public = true;
			} else {
				$rewrite = true;
				$public = true;
			}

			register_taxonomy(
				esc_attr("food_manager_category"),
				apply_filters('register_taxonomy_food_manager_category_object_type', array('food_manager')), //'food_manager_menu'
				apply_filters('register_taxonomy_food_manager_category_args', array(
					'hierarchical' => true,
					'update_count_callback' => '_update_post_term_count',
					'label' => $plural,
					'labels' => array(
						'name' => $plural,
						'singular_name' => $singular,
						'menu_name' => ucwords($plural),
						// translators: %s: plural form of the item
						'search_items' => sprintf(esc_html__('Search %s', 'wp-food-manager'), $plural),

						// translators: %s: plural form of the item
						'all_items' => sprintf(esc_html__('All %s', 'wp-food-manager'), $plural),

						// translators: %s: singular form of the item
						'parent_item' => sprintf(esc_html__('Parent %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'edit_item' => sprintf(esc_html__('Edit %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'update_item' => sprintf(esc_html__('Update %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'add_new_item' => sprintf(esc_html__('Add New %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'new_item_name' => sprintf(esc_html__('New %s Name', 'wp-food-manager'), $singular),

						// translators: %s: plural form of the item
						'not_found' => sprintf(esc_html__('No Food %s Found.', 'wp-food-manager'), $plural),

						// translators: %s: plural form of the item
						'back_to_items' => sprintf(esc_html__('← Go to Food %s', 'wp-food-manager'), $plural),

					),
					'show_ui' => true,
					'show_in_rest' => true,
					'public' => $public,
					'capabilities' => array(
						'manage_terms' => $admin_capability,
						'edit_terms' => $admin_capability,
						'delete_terms' => $admin_capability,
						'assign_terms' => $admin_capability,
					),
					'rewrite' => $rewrite,
				)
				)
			);
		}

		if (get_option('food_manager_enable_food_types', true)) {
			$singular = esc_html__('Food Type', 'wp-food-manager');
			$plural = esc_html__('Types', 'wp-food-manager');

			if (current_theme_supports('food-manager-templates')) {
				$rewrite = array(
					'slug' => esc_attr($permalink_structure['type_rewrite_slug']),
					'with_front' => false,
					'hierarchical' => false
				);
				$public = true;
			} else {
				$rewrite = true;
				$public = true;
			}

			register_taxonomy(
				esc_attr("food_manager_type"),
				apply_filters('register_taxonomy_food_manager_types_object_type', array('food_manager')),
				apply_filters('register_taxonomy_food_manager_types_args', array(
					'hierarchical' => true,
					'label' => $plural,
					'labels' => array(
						'name' => $plural,
						'singular_name' => $singular,
						'menu_name' => ucwords($plural),
						// translators: %s: plural form of the item
						'search_items' => sprintf(esc_html__('Search %s', 'wp-food-manager'), $plural),

						// translators: %s: plural form of the item
						'all_items' => sprintf(esc_html__('All %s', 'wp-food-manager'), $plural),

						// translators: %s: singular form of the item
						'parent_item' => sprintf(esc_html__('Parent %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'wp-food-manager'), $singular),

						// translators: %s: plural form of the item
						'not_found' => sprintf(esc_html__('No %s found', 'wp-food-manager'), strtolower($plural)),

						// translators: %s: singular form of the item
						'edit_item' => sprintf(esc_html__('Edit %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'view_item' => sprintf(esc_html__('View %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'update_item' => sprintf(esc_html__('Update %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'add_new_item' => sprintf(esc_html__('Add New %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'new_item_name' => sprintf(esc_html__('New %s Name', 'wp-food-manager'), $singular),

						// translators: %s: plural form of the item
						'not_found' => sprintf(esc_html__('No Food %s Found.', 'wp-food-manager'), $plural),

						// translators: %s: plural form of the item
						'back_to_items' => sprintf(esc_html__('← Go to Food %s', 'wp-food-manager'), $plural),

					),
					'show_ui' => true,
					'show_in_rest' => true,
					'public' => $public,
					'capabilities' => array(
						'manage_terms' => $admin_capability,
						'edit_terms' => $admin_capability,
						'delete_terms' => $admin_capability,
						'assign_terms' => $admin_capability,
					),
					'rewrite' => $rewrite,
				)
				)
			);
		}

		if (get_option('food_manager_enable_food_tags', true)) {
			$singular = esc_html__('Food Tags', 'wp-food-manager');
			$plural = esc_html__('Tags', 'wp-food-manager');

			if (current_theme_supports('food-manager-templates')) {
				$rewrite = array(
					'slug' => esc_attr($permalink_structure['tag_rewrite_slug']),
					'with_front' => false,
					'hierarchical' => false
				);
				$public = true;
			} else {
				$rewrite = true;
				$public = true;
			}

			register_taxonomy(
				"food_manager_tag",
				apply_filters('register_taxonomy_food_manager_tags_object_tag', array('food_manager')),
				apply_filters('register_taxonomy_food_manager_tags_args', array(
					'hierarchical' => true,
					'label' => $plural,
					'labels' => array(
						'name' => $plural,
						'singular_name' => $singular,
						'menu_name' => ucwords($plural),
						// translators: %s: plural form of the item
						'search_items' => sprintf(esc_html__('Search %s', 'wp-food-manager'), $plural),

						// translators: %s: plural form of the item
						'all_items' => sprintf(esc_html__('All %s', 'wp-food-manager'), $plural),

						// translators: %s: singular form of the item
						'parent_item' => sprintf(esc_html__('Parent %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'edit_item' => sprintf(esc_html__('Edit %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'update_item' => sprintf(esc_html__('Update %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'add_new_item' => sprintf(esc_html__('Add New %s', 'wp-food-manager'), $singular),

						// translators: %s: singular form of the item
						'new_item_name' => sprintf(esc_html__('New %s Name', 'wp-food-manager'), $singular),

						// translators: %s: plural form of the item
						'not_found' => sprintf(esc_html__('No Food %s Found.', 'wp-food-manager'), $plural),

						// translators: %s: plural form of the item
						'back_to_items' => sprintf(esc_html__('← Go to Food %s', 'wp-food-manager'), $plural),

					),
					'show_ui' => true,
					'show_in_rest' => true,
					'hierarchical' => false,
					'public' => $public,
					'capabilities' => array(
						'manage_terms' => $admin_capability,
						'edit_terms' => $admin_capability,
						'delete_terms' => $admin_capability,
						'assign_terms' => $admin_capability,
					),
					'rewrite' => $rewrite,
				)
				)
			);
		}

		$singular = esc_html__('Food Ingredient', 'wp-food-manager');
		$plural = esc_html__('Ingredients', 'wp-food-manager');

		if (current_theme_supports('food-manager-templates')) {
			$rewrite = array(
				'slug' => esc_attr($permalink_structure['ingredients_rewrite_slug']),
				'with_front' => false,
				'hierarchical' => false
			);
			$public = true;
		} else {
			$rewrite = false;
			$public = false;
		}

		register_taxonomy(
			"food_manager_ingredient",
			apply_filters('register_taxonomy_food_manager_ingredients_object_type', array('food_manager')),
			apply_filters('register_taxonomy_food_manager_ingredients_args', array(
				'hierarchical' => true,
				'label' => $plural,
				'labels' => array(
					'name' => $plural,
					'singular_name' => $singular,
					'menu_name' => ucwords($plural),
				// translators: %s: plural form of the item
				'search_items' => sprintf(esc_html__('Search %s', 'wp-food-manager'), $plural),

				// translators: %s: plural form of the item
				'all_items' => sprintf(esc_html__('All %s', 'wp-food-manager'), $plural),

				// translators: %s: singular form of the item
				'parent_item' => sprintf(esc_html__('Parent %s', 'wp-food-manager'), $singular),

				// translators: %s: singular form of the item
				'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'wp-food-manager'), $singular),

				// translators: %s: singular form of the item
				'edit_item' => sprintf(esc_html__('Edit %s', 'wp-food-manager'), $singular),

				// translators: %s: singular form of the item
				'update_item' => sprintf(esc_html__('Update %s', 'wp-food-manager'), $singular),

				// translators: %s: singular form of the item
				'add_new_item' => sprintf(esc_html__('Add New %s', 'wp-food-manager'), $singular),

				// translators: %s: singular form of the item
				'new_item_name' => sprintf(esc_html__('New %s Name', 'wp-food-manager'), $singular),

				// translators: %s: plural form of the item
				'not_found' => sprintf(esc_html__('No %s Found.', 'wp-food-manager'), $plural),

				// translators: %s: plural form of the item
				'back_to_items' => sprintf(esc_html__('← Go to %s', 'wp-food-manager'), $plural),

				),
				'show_ui' => true,
				'show_in_rest' => true,
				'meta_box_cb' => false,
				'public' => $public,
				'capabilities' => array(
					'manage_terms' => $admin_capability,
					'edit_terms' => $admin_capability,
					'delete_terms' => $admin_capability,
					'assign_terms' => $admin_capability,
				),
				'rewrite' => $rewrite,
			)
			)
		);

		$singular = esc_html__('Food Topping', 'wp-food-manager');
		$plural = esc_html__('Toppings', 'wp-food-manager');

		if (current_theme_supports('food-manager-templates')) {
			$rewrite = array(
				'slug' => esc_attr($permalink_structure['topping_rewrite_slug']),
				'with_front' => false,
				'hierarchical' => false
			);
			$public = true;
		} else {
			$rewrite = false;
			$public = false;
		}

		register_taxonomy(
			"food_manager_topping",
			apply_filters('register_taxonomy_food_manager_toppings_object_type', array('food_manager')),
			apply_filters('register_taxonomy_food_manager_toppings_args', array(
				'hierarchical' => true,
				'label' => $plural,
				'labels' => array(
					'name' => $plural,
					'singular_name' => $singular,
					'menu_name' => ucwords($plural),
					// translators: %s: plural form of the item
					'search_items' => sprintf(esc_html__('Search %s', 'wp-food-manager'), $plural),

					// translators: %s: plural form of the item
					'all_items' => sprintf(esc_html__('All %s', 'wp-food-manager'), $plural),

					// translators: %s: singular form of the item
					'parent_item' => sprintf(esc_html__('Parent %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'parent_item_colon' => sprintf(esc_html__('Parent %s:', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'edit_item' => sprintf(esc_html__('Edit %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'update_item' => sprintf(esc_html__('Update %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'add_new_item' => sprintf(esc_html__('Add New %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'new_item_name' => sprintf(esc_html__('New %s Name', 'wp-food-manager'), $singular),

					// translators: %s: plural form of the item
					'not_found' => sprintf(esc_html__('No %s Found.', 'wp-food-manager'), $plural),

					// translators: %s: plural form of the item
					'back_to_items' => sprintf(esc_html__('← Go to %s', 'wp-food-manager'), $plural),

				),
				'show_ui' => true,
				'show_in_rest' => true,
				'meta_box_cb' => false,
				'public' => $public,
				'capabilities' => array(
					'manage_terms' => $admin_capability,
					'edit_terms' => $admin_capability,
					'delete_terms' => $admin_capability,
					'assign_terms' => $admin_capability,
				),
				'rewrite' => $rewrite,
			)
			)
		);

		$singular = esc_html__('Food Nutritions', 'wp-food-manager');
		$plural = esc_html__('Nutritions', 'wp-food-manager');

		if (current_theme_supports('food-manager-templates')) {
			$rewrite = array(
				'slug' => sanitize_title($permalink_structure['nutritions_rewrite_slug']),
				'with_front' => false,
				'hierarchical' => false
			);
			$public = true;
		} else {
			$rewrite = false;
			$public = false;
		}

		register_taxonomy(
			"food_manager_nutrition",
			apply_filters('register_taxonomy_food_manager_nutritions_object_type', array('food_manager')),
			apply_filters('register_taxonomy_food_manager_nutritions_args', array(
				'hierarchical' => true,
				'label' => $plural,
				'labels' => array(
					'name' => $plural,
					'singular_name' => $singular,
					'menu_name' => ucwords($plural),
					// translators: %s: plural form of the item
					'search_items' => sprintf(__('Search %s', 'wp-food-manager'), $plural),

					// translators: %s: plural form of the item
					'all_items' => sprintf(__('All %s', 'wp-food-manager'), $plural),

					// translators: %s: singular form of the item
					'parent_item' => sprintf(__('Parent %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'parent_item_colon' => sprintf(__('Parent %s:', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'edit_item' => sprintf(__('Edit %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'update_item' => sprintf(__('Update %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'add_new_item' => sprintf(__('Add New %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'new_item_name' => sprintf(__('New %s Name', 'wp-food-manager'), $singular),

					// translators: %s: plural form of the item
					'not_found' => sprintf(__('No %s Found.', 'wp-food-manager'), $plural),

					// translators: %s: plural form of the item
					'back_to_items' => sprintf(__('← Go to %s', 'wp-food-manager'), $plural),

				),
				'show_ui' => true,
				'show_in_rest' => true,
				'meta_box_cb' => false,
				'public' => $public,
				'capabilities' => array(
					'manage_terms' => $admin_capability,
					'edit_terms' => $admin_capability,
					'delete_terms' => $admin_capability,
					'assign_terms' => $admin_capability,
				),
				'rewrite' => $rewrite,
			)
			)
		);

		$singular = esc_html__('Unit', 'wp-food-manager');
		$plural = esc_html__('Units', 'wp-food-manager');

		if (current_theme_supports('food-manager-templates')) {
			$rewrite = array(
				'slug' => sanitize_title($permalink_structure['units_rewrite_slug']),
				'with_front' => false,
				'hierarchical' => false
			);
			$public = true;
		} else {
			$rewrite = false;
			$public = false;
		}

		register_taxonomy(
			"food_manager_unit",
			apply_filters('register_taxonomy_food_manager_units_object_type', array('food_manager')),
			apply_filters('register_taxonomy_food_manager_units_args', array(
				'hierarchical' => true,
				'label' => $plural,
				'labels' => array(
					'name' => $plural,
					'singular_name' => $singular,
					'menu_name' => ucwords($plural),
					// translators: %s: plural form of the item
					'search_items' => sprintf(__('Search %s', 'wp-food-manager'), $plural),

					// translators: %s: plural form of the item
					'all_items' => sprintf(__('All %s', 'wp-food-manager'), $plural),

					// translators: %s: singular form of the item
					'parent_item' => sprintf(__('Parent %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'parent_item_colon' => sprintf(__('Parent %s:', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'edit_item' => sprintf(__('Edit %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'update_item' => sprintf(__('Update %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'add_new_item' => sprintf(__('Add New %s', 'wp-food-manager'), $singular),

					// translators: %s: singular form of the item
					'new_item_name' => sprintf(__('New %s Name', 'wp-food-manager'), $singular),

					// translators: %s: plural form of the item
					'not_found' => sprintf(__('No %s Found.', 'wp-food-manager'), $plural),

					// translators: %s: plural form of the item
					'back_to_items' => sprintf(__('← Go to %s', 'wp-food-manager'), $plural),

				),
				'show_ui' => true,
				'show_in_rest' => true,
				'meta_box_cb' => false,
				'public' => $public,
				'capabilities' => array(
					'manage_terms' => $admin_capability,
					'edit_terms' => $admin_capability,
					'delete_terms' => $admin_capability,
					'assign_terms' => $admin_capability,
				),
				'rewrite' => $rewrite,
			)
			)
		);

	}
}