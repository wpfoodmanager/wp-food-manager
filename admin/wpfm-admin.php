<?php
/*
* Main Admin functions class which responsible for the entire amdin functionality and scripts loaded and files.
*
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_food_Manager_Admin class.
 */

class WPFM_Admin {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */

	public function __construct() {

		// include_once( 'wp-food-manager-cpt.php' );

		 include_once( 'wpfm-settings.php' );

		// include_once( 'wp-food-manager-writepanels.php' );

		// include_once( 'wp-food-manager-setup.php' );
		
		// include_once( 'wp-food-manager-field-editor.php' );

		$this->settings_page = new WPFM_Settings();

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );

		//add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		
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

		//main frontend style 	
		//wp_enqueue_style( 'food_manager_admin_css', food_MANAGER_PLUGIN_URL . '/assets/css/backend.min.css' );	
	
		if ( in_array( $screen->id, apply_filters( 'food_manager_admin_screen_ids', array( 'edit-food_listing', 'food_listing', 'food_listing_page_food-manager-settings', 'food_listing_page_food-manager-addons' ) ) ) ) 
		{
			// $jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
			
			// wp_enqueue_style( 'jquery-ui-style',food_MANAGER_PLUGIN_URL. '/assets/js/jquery-ui/jquery-ui.min.css', array(), $jquery_version );			

			// wp_register_script( 'jquery-tiptip', food_MANAGER_PLUGIN_URL. '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), food_MANAGER_VERSION, true );	

			// wp_register_script( 'wp-food-manager-admin-js', food_MANAGER_PLUGIN_URL. '/assets/js/admin.min.js', array( 'jquery', 'jquery-tiptip','jquery-ui-core','jquery-ui-datepicker'), food_MANAGER_VERSION, true );
			// wp_localize_script( 'wp-food-manager-admin-js', 'wp_food_manager_admin_js', array(
			
			// 	'i18n_datepicker_format' => WP_food_Manager_Date_Time::get_datepicker_format(),
				
			// 	'i18n_timepicker_format' => WP_food_Manager_Date_Time::get_timepicker_format(),
				
			// 	'i18n_timepicker_step' => WP_food_Manager_Date_Time::get_timepicker_step(),

			// 	'show_past_date' => apply_filters( 'food_manager_show_past_date', false ),
				
			// 	) );
			// wp_enqueue_script('wp-food-manager-admin-js');
			
		}	
		
		// wp_register_script( 'wp-food-manager-admin-settings', food_MANAGER_PLUGIN_URL. '/assets/js/admin-settings.min.js', array( 'jquery' ), food_MANAGER_VERSION, true );
		// wp_register_script( 'chosen', food_MANAGER_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js', array( 'jquery' ), '1.1.0', true );
		// wp_enqueue_script('chosen');
		// wp_enqueue_style( 'chosen', food_MANAGER_PLUGIN_URL . '/assets/css/chosen.css' );
		
		// wp_enqueue_style( 'wp-food-manager-jquery-timepicker-css', food_MANAGER_PLUGIN_URL . '/assets/js/jquery-timepicker/jquery.timepicker.min.css');
		// wp_register_script( 'wp-food-manager-jquery-timepicker', food_MANAGER_PLUGIN_URL. '/assets/js/jquery-timepicker/jquery.timepicker.min.js', array( 'jquery' ,'jquery-ui-core'), food_MANAGER_VERSION, true );
		// wp_enqueue_script( 'wp-food-manager-jquery-timepicker');
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