<?php
/*
* Main Admin functions class which responsible for the entire amdin functionality and scripts loaded and files.
*
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WPFM_Admin class.
 */

class WPFM_Admin {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */

	public function __construct() {

		include_once( 'wpfm-writepanels.php' );
		include_once( 'wpfm-settings.php' );
		
		$this->settings_page = new WPFM_Settings();

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_action( 'wp_ajax_wpfm_get_food_listings_by_category_id', array( $this, 'wpfm_get_food_listings_by_category_id' ) );
		
	}

	/**
	 * admin_enqueue_scripts function.
	 *
	 * @access public
	 * @return void
	 */

	public function admin_enqueue_scripts() {

		global $wp_scripts;

		$screen = get_current_screen();	

		wp_register_script( 'wpfm-admin', WPFM_PLUGIN_URL. '/assets/js/admin.js', array( 'jquery' ), WPFM_VERSION, true );

		wp_localize_script( 'wpfm-admin', 'wpfm_admin',
					        array( 
					            'ajax_url' => admin_url( 'admin-ajax.php' ),
					        )
					    );
		wp_register_script( 'wp-food-manager-admin-settings', WPFM_PLUGIN_URL. '/assets/js/admin-settings.min.js', array( 'jquery' ), WPFM_VERSION, true );	
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */

	public function admin_menu() {

		add_submenu_page( 'edit.php?post_type=food_manager', __( 'Settings', 'wp-food-manager' ), __( 'Settings', 'wp-food-manager' ), 'manage_options', 'food-manager-settings', array( $this->settings_page, 'output' ) );
	}


  	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		switch ( $screen->id ) {
			case 'options-permalink':
				include 'wpfm-permalink-settings.php';
				break;
		}
	}
}
new WPFM_Admin();
