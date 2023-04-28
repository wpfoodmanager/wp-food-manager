<?php

/**
 * This file the functionality of ajax for food listing and file upload.
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * WPFM_Ajax class.
 */
class WPFM_Ajax {

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
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Get food Manager Ajax Endpoint
	 * 
	 * @access public
	 * @param  string $request Optional
	 * @param  string $ssl     Optional
	 * @return string
	 * @since 1.0.1
	 */
	public static function get_endpoint($request = '%%endpoint%%', $ssl = null) {
		if (strstr(get_option('permalink_structure'), '/index.php/')) {
			$endpoint = trailingslashit(home_url('/index.php/fm-ajax/' . $request . '/', 'relative'));
		} elseif (get_option('permalink_structure')) {
			$endpoint = trailingslashit(home_url('/fm-ajax/' . $request . '/', 'relative'));
		} else {
			$endpoint = add_query_arg('fm-ajax', $request, trailingslashit(home_url('', 'relative')));
		}
		
		return esc_url_raw($endpoint);
	}
}

WPFM_Ajax::instance();
