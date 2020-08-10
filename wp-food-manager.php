<?php
/**
Plugin Name: WP Food Manager

Plugin URI: https://www.wpfoodmanager.com/

Description: Lightweight, scalable and full-featured food listings & management plugin for managing food listings from the Frontend and Backend.

Author: WP Food Manager

Author URI: https://www.wpfoodmanager.com

Text Domain: wp-food-manager

Domain Path: /languages

Version: 1.0.0

Since: 1.0.0

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
		define( 'WPFM_VERSION', '1.0.0' );
		define( 'WPFM_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'WPFM_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		//includes
		include( 'includes/wpfm-install.php' );
		include( 'includes/wpfm-ajax.php' );
		include( 'includes/wpfm-post-types.php' );
		include( 'includes/wpfm-cache-helper.php' );

		//forms
		include( 'forms/wpfm-forms.php' );
		include( 'shortcodes/wpfm-shortcodes.php' );

		

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

		// after theme setup
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );

		//actions
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

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
		add_filter( 'pre_option_wpfm_enable_food_types', '__return_true' );
		$this->post_types->register_post_types();
		remove_filter( 'pre_option_wpfm_categories', '__return_true' );
		remove_filter( 'pre_option_wpfm_enable_food_types', '__return_true' );
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

	/**
	 * Register and enqueue scripts and css
	 */

	public function frontend_scripts() 
	{
		$ajax_url         = WPFM_Ajax::get_endpoint();
		$ajax_filter_deps = array( 'jquery', 'jquery-deserialize' );

		$chosen_shortcodes   = array( 'submit_food_form', 'food_dashboard', 'foods' );
		$chosen_used_on_page = has_wpfm_shortcode( null, $chosen_shortcodes );
	
		//file upload - vendor
		if ( apply_filters( 'wpfm_ajax_file_upload_enabled', true ) ) {

			wp_register_script( 'jquery-iframe-transport', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.iframe-transport.js', array( 'jquery' ), '1.8.3', true );
			wp_register_script( 'jquery-fileupload', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.fileupload.js', array( 'jquery', 'jquery-iframe-transport', 'jquery-ui-widget' ), '5.42.3', true );
			wp_register_script( 'wpfm-ajax-file-upload', WPFM_PLUGIN_URL . '/assets/js/ajax-file-upload.min.js', array( 'jquery', 'jquery-fileupload' ), WPFM_VERSION, true );

			ob_start();
			get_food_manager_template( 'form-fields/uploaded-file-html.php', array( 'name' => '', 'value' => '', 'extension' => 'jpg' ) );
			$js_field_html_img = ob_get_clean();

			ob_start();
			get_food_manager_template( 'form-fields/uploaded-file-html.php', array( 'name' => '', 'value' => '', 'extension' => 'zip' ) );
			$js_field_html = ob_get_clean();

			wp_localize_script( 'wpfm-ajax-file-upload', 'wpfm_ajax_file_upload', array(
				'ajax_url'               => $ajax_url,
				'js_field_html_img'      => esc_js( str_replace( "\n", "", $js_field_html_img ) ),
				'js_field_html'          => esc_js( str_replace( "\n", "", $js_field_html ) ),
				'i18n_invalid_file_type' => __( 'Invalid file type. Accepted types:', 'wp-food-manager' )
			) );
			
		}

		//jQuery Deserialize - vendor
		wp_register_script( 'jquery-deserialize', WPFM_PLUGIN_URL . '/assets/js/jquery-deserialize/jquery.deserialize.js', array( 'jquery' ), '1.2.1', true );						
	
		wp_enqueue_style( 'wpfm-frontend', WPFM_PLUGIN_URL . '/assets/css/frontend.min.css');

		//common js
		wp_register_script('wp-food-manager-common', WPFM_PLUGIN_URL . '/assets/js/common.min.js', array('jquery'), WPFM_VERSION, true);	
		wp_enqueue_script('wp-food-manager-common'); 		

		//food submission forms and validation js
		wp_register_script( 'wpfm-food-submission', WPFM_PLUGIN_URL . '/assets/js/food-submission.min.js', array('jquery','jquery-ui-core','jquery-ui-datepicker') , WPFM_VERSION, true );
		wp_localize_script( 'wpfm-food-submission', 'wpfm_food_submission', array(
			
		// 'i18n_datepicker_format' => WP_Event_Manager_Date_Time::get_datepicker_format(),
		
		// 'i18n_timepicker_format' => WP_Event_Manager_Date_Time::get_timepicker_format(),
		
		// 'i18n_timepicker_step' => WP_Event_Manager_Date_Time::get_timepicker_step(),
		
		) );
		
		wp_register_script( 'wpfm-content-food-listing', WPFM_PLUGIN_URL . '/assets/js/content-food-listing.min.js', array('jquery','wp-food-manager-common'), WPFM_VERSION, true );					
		wp_localize_script( 'wpfm-content-food-listing', 'wpfm_content_food_listing', array(
				
				'i18n_dateLabel' => __( 'Select Date', 'wp-food-manager' ),
				
				'i18n_today' => __( 'Today', 'wp-food-manager' ),
				'i18n_tomorrow' => __( 'Tomorrow', 'wp-food-manager' ),
				'i18n_thisWeek' => __( 'This Week', 'wp-food-manager' ),
				'i18n_nextWeek' => __( 'Next Week', 'wp-food-manager' ),
				'i18n_thisMonth' => __( 'This Month', 'wp-food-manager' ),
				'i18n_nextMonth' => __( 'Next Month', 'wp-food-manager' ),
				'i18n_thisYear' => __( 'This Year', 'wp-food-manager' ),
				'i18n_nextYear' => __( 'Next Month', 'wp-food-manager' )
		) );

		//ajax filters js
		wp_register_script( 'wpfm-ajax-filters', WPFM_PLUGIN_URL . '/assets/js/food-ajax-filters.js', $ajax_filter_deps, WPFM_VERSION, true );
		wp_localize_script( 'wpfm-ajax-filters', 'wpfm_ajax_filters', array(
			'ajax_url'                => $ajax_url,
			'is_rtl'                  => is_rtl() ? 1 : 0,
			'lang'                    => apply_filters( 'wpfm_lang', null ) //defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '', // WPML workaround until this is standardized			
		) );

		//dashboard
		wp_register_script( 'wpfm-food-dashboard', WPFM_PLUGIN_URL . '/assets/js/food-dashboard.min.js', array( 'jquery' ), WPFM_VERSION, true );	
		wp_localize_script( 'wp-food-manager-food-dashboard', 'food_manager_food_dashboard', array(

			'i18n_btnOkLabel' => __( 'Delete', 'wp-food-manager' ),

			'i18n_btnCancelLabel' => __( 'Cancel', 'wp-food-manager' ),

			'i18n_confirm_delete' => __( 'Are you sure you want to delete this food?', 'wp-food-manager' )

		) );

	
		
		wp_register_script( 'wpfm-slick-script', WPFM_PLUGIN_URL . '/assets/js/slick/slick.min.js', array( 'jquery' ) );
		wp_register_style( 'wpfm-slick-style', WPFM_PLUGIN_URL . '/assets/js/slick/slick.css' , array( ) );
		
		wp_register_style( 'wpfm-grid-style', WPFM_PLUGIN_URL . '/assets/css/wpfm-grid.min.css');
		wp_register_style( 'wp-food-manager-font-style', WPFM_PLUGIN_URL . '/assets/fonts/style.css');
		
		wp_enqueue_style( 'wpfm-grid-style');
		wp_enqueue_style( 'wpfm-font-style');
	}

}

if(!function_exists('WPFM')){
	/**
	 * Main instance of WP Food Manager.
	 *
	 * Returns the main instance of WP Food Manager to prfood the need to use globals.
	 *
	 * @since  1.0
	 * @return WP_Event_Manager
	 */
	function WPFM() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		return WP_Food_Manager::instance();
	}
}
$GLOBALS['food_manager'] =  WPFM();