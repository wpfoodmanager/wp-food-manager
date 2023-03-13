<?php

/**
 * Main Admin functions class which responsible for the entire amdin functionality and scripts loaded and files.
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * WPFM_Admin class.
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
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 1.0.0
	 * @static
	 * @return self Main instance.
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
	 */
	public function __construct() {
		include_once('wpfm-settings.php');
		include_once('wpfm-writepanels.php');
		include_once('wpfm-setup.php');
		include_once('wpfm-field-editor.php');
		$this->settings_page = WPFM_Settings::instance();
		add_action('admin_menu', array($this, 'admin_menu'), 12);
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
		add_action('current_screen', array($this, 'conditional_includes'));
		add_action('wp_ajax_wpfm_get_food_listings_by_category_id', array($this, 'wpfm_get_food_listings_by_category_id'));
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
		$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
		wp_enqueue_style('wpfm-backend-css', WPFM_PLUGIN_URL . '/assets/css/backend.min.css');
		wp_enqueue_style('jquery-ui-style', WPFM_PLUGIN_URL . '/assets/js/jquery-ui/jquery-ui.min.css', array(), $jquery_version);
		$units    = get_terms(
			[
				'taxonomy'   => 'food_manager_unit',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			]
		);
		$unitList = [];
		if (!empty($units)) {
			foreach ($units as $unit) {
				$unitList[$unit->term_id] = $unit->name;
			}
		}
		wp_register_script('wpfm-jquery-tiptip', WPFM_PLUGIN_URL . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array('jquery'), WPFM_VERSION, true);
		wp_register_script('wpfm-admin', WPFM_PLUGIN_URL . '/assets/js/admin.min.js', array('jquery', 'wpfm-jquery-tiptip', 'jquery-ui-core', 'jquery-ui-datepicker'), WPFM_VERSION, true);
		wp_localize_script(
			'wpfm-admin',
			'wpfm_admin',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'security' => wp_create_nonce('wpfm-admin-security'),
				'start_of_week'                      => get_option('start_of_week'),
				'i18n_datepicker_format'             => WPFM_Date_Time::get_datepicker_format(),
			)
		);
		wp_localize_script(
			'wpfm-admin',
			'wpfm_var',
			[
				'units'   => $unitList,
			]
		);
		wp_enqueue_script('wpfm-admin');
		wp_register_script('wp-food-manager-admin-settings', WPFM_PLUGIN_URL . '/assets/js/admin-settings.min.js', array('jquery'), WPFM_VERSION, true);
		if (is_admin() && !isset($_GET['page']) == 'wc-settings') {
			wp_enqueue_script('wp-food-manager-admin-settings');
		}
		wp_register_script('chosen', WPFM_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
		wp_enqueue_script('chosen');
		wp_enqueue_style('chosen', WPFM_PLUGIN_URL . '/assets/css/chosen.min.css');
		wp_enqueue_style('wpfm-font-style', WPFM_PLUGIN_URL . '/assets/fonts/style.min.css');
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
		wp_register_script('wpfm-accounting', WPFM_PLUGIN_URL . '/assets/js/accounting/accounting.min.js', array('jquery'), WPFM_VERSION, true);
		wp_localize_script(
			'wpfm-accounting',
			'wpfm_accounting_params',
			array(
				'wpfm_sale_less_than_regular_error' => __('Please enter in a value less than the regular price.', 'woocommerce'),
			)
		);
		wp_enqueue_script('wpfm-accounting');
		// File upload - vendor
		if (apply_filters('wpfm_ajax_file_upload_enabled', true)) {
			wp_register_script('jquery-iframe-transport', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.iframe-transport.min.js', array('jquery'), '1.8.3', true);
			wp_register_script('jquery-fileupload', WPFM_PLUGIN_URL . '/assets/js/jquery-fileupload/jquery.fileupload.min.js', array('jquery', 'jquery-iframe-transport', 'jquery-ui-widget'), '5.42.3', true);
			wp_register_script('wpfm-ajax-file-upload', WPFM_PLUGIN_URL . '/assets/js/ajax-file-upload.min.js', array('jquery', 'jquery-fileupload'), WPFM_VERSION, true);
			ob_start();
			get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'jpg'));
			$js_field_html_img = ob_get_clean();
			ob_start();
			get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'zip'));
			$js_field_html = ob_get_clean();
			wp_localize_script('wpfm-ajax-file-upload', 'wpfm_ajax_file_upload', array(
				'ajax_url'               => admin_url('admin-ajax.php'),
				'js_field_html_img'      => esc_js(str_replace("\n", "", $js_field_html_img)),
				'js_field_html'          => esc_js(str_replace("\n", "", $js_field_html)),
				'i18n_invalid_file_type' => __('Invalid file type. Accepted types:', 'wp-food-manager')
			));
		}
		wp_enqueue_editor();
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page('edit.php?post_type=food_manager', __('Settings', 'wp-food-manager'), __('Settings', 'wp-food-manager'), 'manage_options', 'food-manager-settings', array($this->settings_page, 'output'));
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		$screen = get_current_screen();
		if (!$screen) {
			return;
		}
		switch ($screen->id) {
			case 'options-permalink':
				include 'wpfm-permalink-settings.php';
				break;
		}
	}

	/**
	 * Ran on WP admin_init hook
	 */
	public function admin_init() {
		if (!empty($_GET['food-manager-main-admin-dismiss'])) {
			update_option('food_manager_rating_showcase_admin_notices_dismiss', 1);
		}
	}
}
WPFM_Admin::instance();
