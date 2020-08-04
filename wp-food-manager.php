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

		include( 'includes/wpfm-post-types.php' );
		include( 'forms/wpfm-forms.php' );
		include( 'shortcodes/wpfm-shortcodes.php' );

		// Init classes
		$this->forms      = WPFM_Forms::instance();
		$this->post_types = WPFM_Post_Types::instance();

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'activate' ) );

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