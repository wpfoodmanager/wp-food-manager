<?php

/**
 * Plugin Name: WP Food Manager
 * Plugin URI: https://www.wpfoodmanager.com/
 * Description: Lightweight, scalable and full-featured food listings & management plugin for managing food listings from the Frontend and Backend.
 * Author: WP Food Manager
 * Author URI: https://www.wpfoodmanager.com
 * Text Domain: wp-food-manager
 * Domain Path: /languages
 * Version: 1.0.2
 * Since: 1.0.0
 * Requires WordPress Version at least: 4.1
 * Copyright: 2020 WP Food Manager
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WP_Food_Manager class.
 */
class WP_Food_Manager {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Init post_types.
	 *
	 * @since 1.0.0
	 */
	public $post_types;

	/**
	 * Init forms.
	 *
	 * @since 1.0.0
	 */
	public $forms;

	/**
	 * Main WP Food Manager Instance.
	 * Ensures only one instance of WP Food Manager is loaded or can be loaded.
	 *
	 * @static
	 * @see WP_Food_Manager()
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
	 * Constructor - get the plugin hooked in and ready
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {

		// Define constants
		define('WPFM_VERSION', '1.0.2');
		define('WPFM_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
		define('WPFM_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));

		// Core
		include('includes/wpfm-install.php');
		include('includes/wpfm-date-time.php');
		include('includes/wpfm-ajax.php');
		include('includes/wpfm-custom-post-types.php');
		include('includes/wpfm-cache-helper.php');

		//forms
		include('forms/wpfm-forms.php');
		include('shortcodes/wpfm-shortcodes.php');

		if (is_admin()) {
			include('admin/wpfm-admin.php');
		}

		// Actions and Filters Hooks
		include('includes/wpfm-action-hooks.php');
		include('includes/wpfm-filter-hooks.php');

		// Init classes
		$this->forms      = WPFM_Forms::instance();
		$this->post_types = WPFM_Post_Types::instance();

		// Activation - works with symlinks
		register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), array($this, 'activate'));

		// Overwritting the content of custom post types of WP food manager.
		global $wp_embed;
		add_filter('wpfm_the_content', array($wp_embed, 'run_shortcode'), 8);
		add_filter('wpfm_the_content', array($wp_embed, 'autoembed'), 8);
		add_filter('wpfm_the_content', 'wptexturize');
		add_filter('wpfm_the_content', 'convert_chars');
		add_filter('wpfm_the_content', 'wpautop');
		add_filter('wpfm_the_content', 'shortcode_unautop');
		add_filter('wpfm_the_content', 'do_shortcode');

		// Schedule cron foods
		self::check_schedule_crons();
	}

	/**
	 * Called on plugin activation
	 * 
	 * @since 1.0.0
	 */
	public function activate() {
		unregister_post_type('food_manager');
		$this->post_types->register_post_types();
		remove_filter('pre_option_wpfm_categories', '__return_true');
		remove_filter('pre_option_wpfm_enable_food_types', '__return_true');
		WPFM_Install::install();
		flush_rewrite_rules();
	}

	/**
	 * Handle Updates
	 * 
	 * @since 1.0.0
	 */
	public function updater() {
		if (version_compare(WPFM_VERSION, get_option('food_manager_version'), '>')) {
			WPFM_Install::update();
			flush_rewrite_rules();
		}
	}

	/**
	 * Check cron status
	 * 
	 * @since 1.0.0
	 */
	public function check_schedule_crons() {
		if (!wp_next_scheduled('food_manager_check_for_expired_foods')) {
			wp_schedule_event(time(), 'hourly', 'food_manager_check_for_expired_foods');
		}
		if (!wp_next_scheduled('food_manager_delete_old_previews')) {
			wp_schedule_event(time(), 'daily', 'food_manager_delete_old_previews');
		}
		if (!wp_next_scheduled('food_manager_clear_expired_transients')) {
			wp_schedule_event(time(), 'twicedaily', 'food_manager_clear_expired_transients');
		}
	}
}

$GLOBALS['food_manager'] =  WP_Food_Manager::instance();
