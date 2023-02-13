<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Food_Manager_Install
 */

class WP_Food_Manager_Install {

	/**
	 * Install WP Food Manager
	 */
	public static function install() {

		global $wpdb;

		self::init_user_roles();

		self::default_terms();


		// Redirect to setup screen for new installs
		if ( ! get_option( 'wp_food_manager_version' ) ) {

			set_transient( '_food_manager_activation_redirect', 1, HOUR_IN_SECONDS );
		}
		
		// Update featured posts ordering.
		if ( version_compare( get_option( 'wp_food_manager_version', WPFM_VERSION ), '2.5', '<' ) ) {
			$wpdb->query( "UPDATE {$wpdb->posts} p SET p.menu_order = 0 WHERE p.post_type='food_manager';" );
			$wpdb->query( "UPDATE {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id SET p.menu_order = -1 WHERE pm.meta_key = '_featured' AND pm.meta_value='1' AND p.post_type='food_manager';" );
		}

		// Update legacy options
		if ( false === get_option( 'food_manager_add_food_page_id', false ) && get_option( 'food_manager_submit_page_slug' ) ) {

			$page_id = get_page_by_path( get_option( 'food_manager_submit_page_slug' ) )->ID;

			update_option( 'food_manager_add_food_page_id', $page_id );
		}

		if ( false === get_option( 'food_manager_food_dashboard_page_id', false ) && get_option( 'food_manager_food_dashboard_page_slug' ) ) {

			$page_id = get_page_by_path( get_option( 'food_manager_food_dashboard_page_slug' ) )->ID;

			update_option( 'food_manager_food_dashboard_page_id', $page_id );
		}

		if ( false === get_option( 'wp_food_manager_db_version', false ) )
		{
			update_option( 'wp_food_manager_db_version', '3.1.13' );
		}

		delete_transient( 'wp_food_manager_addons_html' );

		update_option( 'wp_food_manager_version', WPFM_VERSION );
	}

	public static function update() {

		global $wpdb;

		// 3.1.14 change field option name
		if ( !empty(get_option( 'food_manager_form_fields', true )) ) 
		{
			$all_fields = get_option( 'food_manager_form_fields', true );

			if(isset($all_fields) && !empty($all_fields) && is_array($all_fields))
			{
				if(isset($all_fields['food']['food_address']))
					unset($all_fields['food']['food_address']);

				if(isset($all_fields['food']['food_venue_name']))
					unset($all_fields['food']['food_venue_name']);

				update_option( 'food_manager_submit_food_form_fields', array('food' =>$all_fields['food']) );

				update_option( 'food_manager_submit_extra_options_form_fields', array('extra_options' =>$all_fields['extra_options']) );	
			}			
		}

		// 3.1.14 add organizer pages
		/*$pages_to_create = [
			'submit_organizer_form' => [
				'page_title' => 'Submit Organizer Form',
				'page_content' => '[submit_organizer_form]',
			],
			'organizer_dashboard' => [
				'page_title' => 'Organizer Dashboard',
				'page_content' => '[organizer_dashboard]',
			],
			'food_organizers' => [
				'page_title' => 'Event Organizers',
				'page_content' => '[food_organizers]',
			],
			'submit_venue_form' => [
				'page_title' => 'Submit Venue Form',
				'page_content' => '[submit_venue_form]',
			],
			'venue_dashboard' => [
				'page_title' => 'Venue Dashboard',
				'page_content' => '[venue_dashboard]',
			],
			'food_venues' => [
				'page_title' => 'Event Venues',
				'page_content' => '[food_venues]',
			],
		];

		foreach ( $pages_to_create as $page_slug => $page ) 
		{
			self::create_page( sanitize_text_field( $page['page_title'] ), $page['page_content'], 'food_manager_' . $page_slug . '_page_id' );
		}*/

		delete_transient( 'wp_food_manager_addons_html' );

		update_option( 'wp_food_manager_version', WPFM_VERSION );
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

			add_role( 'food_owner', __( 'Food Owner', 'wp-event-manager' ), array(

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
			'food_manager_type' => array(
			
				'Vegan',
				
				'Vegeterian',
			
				'Non Vegeterian',
			),
			'food_manager_unit' => array(
			
				'Gram',
			
				'MG',
			
				'PC',
			
				'KG',
			
				'Dozen',
			)
		);
	}

	/**
	 * default_terms function.
	 */
	private static function default_terms() {
		if ( get_option( 'food_manager_installed_terms' ) == 1 ) {
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
		
		update_option( 'food_manager_installed_terms', 1 );
	}

	/**
	 * Adds the employment type to default event types when updating from a previous WP Food Manager version.
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

	/**
	 * create_page function.
	 */
	/*private static function create_page( $title, $content, $option ) 
	{
		if(get_option($option) == '')
		{
			$page_data = array(

				'post_status'    => 'publish',

				'post_type'      => 'page',

				'post_author'    => 1,

				'post_name'      => sanitize_title( $title ),

				'post_title'     => $title,

				'post_content'   => $content,

				'post_parent'    => 0,

				'comment_status' => 'closed'
			);

			$page_id = wp_insert_post( $page_data );

			if ( $option ) {

				update_option( $option, $page_id );
			}
		}		
	}*/

}