<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Event_Manager_Install
 */

class WPFM_Install {

	/**
	 * Install WP Event Manager
	 */

	public static function install() {

		global $wpdb;

		self::init_user_roles();

		self::default_terms();


		// Redirect to setup screen for new installs

		if ( ! get_option( 'wpfm_version' ) ) {

			set_transient( '_wpfm_activation_redirect', 1, HOUR_IN_SECONDS );
		}

		delete_transient( 'wpfm_addons_html' );

		update_option( 'wpfm_version', WPFM_VERSION );
	}
	
	/**
	 * Init user roles
	 */

	private static function init_user_roles() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {

			$wp_roles = new WP_Roles();			
		}

		if ( is_object( $wp_roles ) ) {

			add_role( 'store_owner', __( 'Store owner', 'wp-food-manager' ), array(

				'read'         => true,

				'edit_posts'   => false,

				'delete_posts' => false
			) );

			$capabilities = self::get_core_capabilities();

			foreach ( $capabilities as $cap_group ) {

				foreach ( $cap_group as $cap ) {

					$wp_roles->add_cap( 'administrator', $cap );
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
	 * Default taxonomy terms to set up in WP Event Manager.
	 *
	 * @return array Default taxonomy terms.
	 */
	private static function get_default_taxonomy_terms() {
		return array(

			'food_manager_ingredient' => array(

				'Appearance or Signing',

				'Attraction',

				'Camp, Trip, or Retreat',

				'Class, Training, or Workshop',

				'Concert or Performance',

			),
			'food_manager_category' => array(
			
					'Appetizers/Starters',
			
					'Breakfast',
			
					'Dessert',
			
					'Beverage',
			
					'Main dishes',
			)
		);
	}
	/**
	 * default_terms function.
	 */

	private static function default_terms() {
		if ( get_option( 'wpfm_installed_terms' ) == 1 ) {
			return;
		}
		
		$taxonomies = self::get_default_taxonomy_terms();
		foreach ( $taxonomies as $taxonomy => $terms ) {

			foreach ( $terms as $term ) {

				if ( ! get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {

					wp_insert_term( $term, $taxonomy );
				}
			}
		}
		
		update_option( 'wpfm_installed_terms', 1 );
	}

	/**
	 * Adds the employment type to default food types when updating from a previous WP Event Manager version.
	 */
	private static function add_food_types() {
		$taxonomies = self::get_default_taxonomy_terms();
		$terms      = $taxonomies['food_manager_type'];

		foreach ( $terms as $term => $meta ) {
			$term = get_term_by( 'slug', sanitize_title( $term ), 'food_manager_type' );
			if ( $term ) {
				foreach ( $meta as $meta_key => $meta_value ) {
					if ( ! get_term_meta( (int) $term->term_id, $meta_key, true ) ) {
						add_term_meta( (int) $term->term_id, $meta_key, $meta_value );
					}
				}
			}
		}
	}
}