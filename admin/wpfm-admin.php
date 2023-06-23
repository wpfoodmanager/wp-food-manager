<?php

/**
 * Main Admin functions class which responsible for the entire admin functionality and scripts loaded and files.
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * WPFM_Admin class.
 * Class for the admin handler.
 */
class WPFM_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Init settings_page.
	 *
	 * @since 1.0.0
	 */
	public $settings_page;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @static
	 * @return self Main instance.
	 * @since 1.0.0
	 */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * __construct function
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		include_once('wpfm-settings.php');
		include_once('wpfm-writepanels.php');
		include_once('wpfm-setup.php');
		include_once('wpfm-field-editor.php');
	}

	/**
	 * Ran on WP admin_init hook
	 * 
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function admin_init() {
		if (!empty($_GET['food-manager-main-admin-dismiss'])) {
			update_option('food_manager_rating_showcase_admin_notices_dismiss', 1);
		}
	}
}

WPFM_Admin::instance();
