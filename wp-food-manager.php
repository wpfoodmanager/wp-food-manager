<?php
/**
Plugin Name: WP Food Manager

Plugin URI: https://www.wpfoodmanager.com/

Description: Lightweight, scalable and full-featured food listings & management plugin for managing food listings from the Frontend and Backend.

Author: WP Food Manager

Author URI: https://www.wpfoodmanager.com

Text Domain: wp-event-manager

Domain Path: /languages

Version: `1.0.0

Since: 1.0

Requires WordPress Version at least: 4.1

Copyright: 2020 WP Food Manager

License: GNU General Public License v3.0

License URI: http://www.gnu.org/licenses/gpl-3.0.html

**/

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
	
	exit;
}

/**
 * WP_Event_Manager class.
 */

class WP_Food_Manager {
		/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main WP Food Manager Instance.
	 *
	 * Ensures only one instance of WP Food Manager is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 * @see WP_Food_Manager()
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor - get the plugin hooked in and ready
	 */

	public function __construct() 
	{
		// Define constants
		define( 'WPFM_VERSION', '0.1.1' );
		define( 'WPFM_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'WPFM_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		//includes
		include( 'includes/wpfm-install.php' );
		include( 'includes/wpfm-post-types.php' );

		//forms
		include( 'forms/wpfm-forms.php' );
		include( 'shortcodes/wpfm-shortcodes.php' );

		include( 'wp-food-manager-template.php' );

		if(is_admin()){
			include( 'admin/wpfm-admin.php' );
			
		}

		// Init classes
		$this->forms      = WPFM_Forms::instance();
		$this->post_types = WPFM_Post_Types::instance();

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'activate' ) );
		// Switch theme
		add_action( 'after_switch_theme', array( $this->post_types, 'register_post_types' ), 11 );

		add_action( 'after_switch_theme', 'flush_rewrite_rules', 15 );


		// Actions
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );

	}


	/**
	 * Localisation
	 */

	public function load_plugin_textdomain() {

		$domain = 'wp-food-manager';       

        	$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain( $domain, WP_LANG_DIR . "/wp-food-manager/".$domain."-" .$locale. ".mo" );

		load_plugin_textdomain($domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

		/**
	 * Called on plugin activation
	 */

	public function activate() {

		unregister_post_type( 'food_manager' );
		add_filter( 'pre_option_wpfm_enable_categories', '__return_true' );
		add_filter( 'pre_option_wpfm_enable_event_types', '__return_true' );
		$this->post_types->register_post_types();
		remove_filter( 'pre_option_wpfm_categories', '__return_true' );
		remove_filter( 'pre_option_wpfm_enable_event_types', '__return_true' );
		WPFM_Install::install();
		flush_rewrite_rules();
	}


	/**
	 * Load functions
	 */

	public function include_template_functions() {

		include( 'wp-food-manager-functions.php' );

		include( 'wp-food-manager-template.php' );
	}

}

if(!function_exists('WPFM')){
	/**
	 * Main instance of WP Food Manager.
	 *
	 * Returns the main instance of WP Food Manager to prevent the need to use globals.
	 *
	 * @since  1.0
	 * @return WP_Event_Manager
	 */
	function WPFM() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		return WP_Food_Manager::instance();
	}
}
$GLOBALS['food_manager'] =  WPFM();