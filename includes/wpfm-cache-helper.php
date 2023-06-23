<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WPFM_Cache_Helper class.
 */
class WPFM_Cache_Helper {

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
	 * Get transient version
	 *
	 * When using transients with unpredictable names, e.g. those containing an md5
	 * hash in the name, we need a way to invalidate them all at once.
	 *
	 * When using default WP transients we're able to do this with a DB query to
	 * delete transients manually.
	 *
	 * With external cache however, this isn't possible. Instead, this function is used
	 * to append a unique string (based on time()) to each transient. When transients
	 * are invalidated, the transient version will increment and data will be regenerated.
	 *
	 * @static
	 * @param  string  $group   Name for the group of transients we need to invalidate
	 * @param  boolean $refresh true to force a new version
	 * @return string transient version based on time(), 10 digits
	 * @since 1.0.0
	 */
	public static function get_transient_version($group, $refresh = false) {
		$transient_name  = sanitize_key($group) . '-transient-version';
		$transient_value = get_transient($transient_name);

		if (false === $transient_value || true === $refresh) {
			self::delete_version_transients($transient_value);
			set_transient($transient_name, $transient_value = time());
		}

		return $transient_value;
	}

	/**
	 * When the transient version increases, this is used to remove all past transients to avoid filling the DB.
	 *
	 * Note; this only works on transients appended with the transient version, and when object caching is not being used.
	 * 
	 * @static
	 * @param string $version
	 * @return string void
	 * @since 1.0.0
	 */
	private static function delete_version_transients($version) {
		if (!wp_using_ext_object_cache() && !empty($version)) {
			global $wpdb;
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s;", '\_transient\_%' . sanitize_text_field($version)));
		}
	}

	/**
	 * Get Listings Count from Cache
	 *
	 * @static
	 * @param string $post_type
	 * @param string $status
	 * @param bool   $force Force update cache
	 * @return int
	 */
	public static function get_listings_count($post_type = 'food_manager', $status = 'pending', $force = false) {
		// Get user based cache transient
		$user_id   = get_current_user_id();
		$transient = "em_" . sanitize_key($status) . "_" . sanitize_key($post_type) . "_count_user_" . absint($user_id);
		// Set listings_count value from cache if exists, otherwise set to 0 as default
		$status_count = ($cached_count = get_transient($transient)) ? absint($cached_count) : 0;

		// $cached_count will be false if transient does not exist
		if ($cached_count === false || $force) {
			$count_posts = wp_count_posts(sanitize_key($post_type), 'readable');

			// Default to 0 $status if object does not have a value
			$status_count = isset($count_posts->$status) ? absint($count_posts->$status) : 0;
			set_transient($transient, $status_count, DAY_IN_SECONDS * 7);
		}

		return $status_count;
	}
}

WPFM_Cache_Helper::instance();
