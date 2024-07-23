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
		 // wpfm cache helper.
		 add_action('save_post', array($this, 'flush_get_food_managers_cache'));
		 add_action('delete_post', array($this, 'flush_get_food_managers_cache'));
		 add_action('trash_post', array($this, 'flush_get_food_managers_cache'));
		 add_action('set_object_terms', array($this, 'set_term'), 10, 4);
		 add_action('edited_term', array($this, 'edited_term'), 10, 3);
		 add_action('create_term', array($this, 'edited_term'), 10, 3);
		 add_action('delete_term', array($this, 'edited_term'), 10, 3);
		 add_action('food_manager_clear_expired_transients', array($this, 'clear_expired_transients'), 10);
		 add_action('transition_post_status', array($this, 'maybe_clear_count_transients'), 10, 3);
	}

	  /**
     * Flush the cache.
     * 
     * @access public
     * @param mixed $post_id
     * @return void
     * @since 1.0.0
     */
    public static function flush_get_food_managers_cache($post_id) {
        if ('food_manager' === get_post_type($post_id)) {
            WPFM_Cache_Helper::get_transient_version('get_food_managers', true);
        }
    }

	  /**
     * Maybe remove pending count transients.
     *
     * When a supported post type status is updated, check if any cached count transients need to be removed.
     *
     * @access public
     * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
     * @return void
     * @since 1.0.0
     */
    public static function maybe_clear_count_transients($new_status, $old_status, $post) {
        global $wpdb;

        /**
         * Get supported post types for count caching.
         * @param array   $post_types Post types that should be cached.
         * @param string  $new_status New post status.
         * @param string  $old_status Old post status.
         * @param WP_Post $post       Post object.
         */
        $post_types = apply_filters('wp_foodmanager_count_cache_supported_post_types', array('food_manager'), $new_status, $old_status, $post);

        // Only proceed when statuses do not match, and post type is supported post type.
        if ($new_status === $old_status || !in_array($post->post_type, $post_types)) {
            return;
        }

        /**
         * Get supported post statuses for count caching.
         * @param array   $post_statuses Post statuses that should be cached.
         * @param string  $new_status    New post status.
         * @param string  $old_status    Old post status.
         * @param WP_Post $post          Post object.
         */
        $valid_statuses = apply_filters('wp_foodmanager_count_cache_supported_statuses', array('pending'), $new_status, $old_status, $post);
        $wpfm_like          = array();

        // New status transient option name.
        if (in_array($new_status, $valid_statuses)) {
            $wpfm_like[] = "^_transient_fm_{$new_status}_{$post->post_type}_count_user_";
        }

        // Old status transient option name.
        if (in_array($old_status, $valid_statuses)) {
            $wpfm_like[] = "^_transient_fm_{$old_status}_{$post->post_type}_count_user_";
        }

        if (empty($wpfm_like)) {
            return;
        }

        $sql        = $wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name RLIKE '%s'", implode('|', $wpfm_like));
        $transients = $wpdb->get_col($sql);

        // For each transient...
        foreach ($transients as $transient) {
            // Strip away the WordPress prefix in order to arrive at the transient key.
            $key = str_replace('_transient_', '', $transient);
            // Now that we have the key, use WordPress core to delete the transient.
            delete_transient($key);
        }

        // Sometimes transients are not in the DB, so we have to do this too:
        wp_cache_flush();
    }

	/**
     * Clear expired transients.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public static function clear_expired_transients() {
        global $wpdb;
        if (!wp_using_ext_object_cache() && !defined('WP_SETUP_CONFIG') && !defined('WP_INSTALLING')) {
            $sql = "
			DELETE a, b FROM $wpdb->options a, $wpdb->options b	
			WHERE a.option_name LIKE %s	
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %s;";
            $wpdb->query($wpdb->prepare($sql, $wpdb->esc_like('_transient_fm_') . '%', $wpdb->esc_like('_transient_timeout_fm_') . '%', time()));
        }
    }
	
    /**
     * When any term is edited.
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    public static function edited_term($term_id = '', $tt_id = '', $taxonomy = '') {
        WPFM_Cache_Helper::get_transient_version('fm_get_' . sanitize_text_field($taxonomy), true);
    }
	
	/**
     * When any post has a term set.
     * 
     * @access public
     * @param mixed $object_id
     * @param int $terms
     * @param int $tt_ids
     * @param mixed $taxonomy
     * @return void
     * @since 1.0.0
     */
    public static function set_term($object_id = '', $terms = '', $tt_ids = '', $taxonomy = '') {
        WPFM_Cache_Helper::get_transient_version('fm_get_' . sanitize_text_field($taxonomy), true);
    }
	/**
	 * Get transient version.
	 *
	 * When using transients with unpredictable names, e.g. those containing an md5 hash in the name, we need a way to invalidate them all at once.
	 * 
	 * When using default WP transients we're able to do this with a DB query to delete transients manually.
	 * 
	 * With external cache however, this isn't possible. Instead, this function is used to append a unique string (based on time()) to each transient. When transients are invalidated, the transient version will increment and data will be regenerated.
	 *
	 * @static
	 * @param  string  $group   Name for the group of transients we need to invalidate.
	 * @param  boolean $refresh true to force a new version.
	 * @return string transient version based on time(), 10 digits.
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
	 * Get Listings Count from Cache.
	 *
	 * @static
	 * @param string $post_type
	 * @param string $status
	 * @param bool   $force Force update cache.
	 * @return int
	 */
	public static function get_listings_count($post_type = 'food_manager', $status = 'pending', $force = false) {
		// Get user based cache transient
		$user_id   = get_current_user_id();
		$transient = "em_" . sanitize_key($status) . "_" . sanitize_key($post_type) . "_count_user_" . absint($user_id);
		// Set listings_count value from cache if exists, otherwise set to 0 as default.
		$status_count = ($cached_count = get_transient($transient)) ? absint($cached_count) : 0;

		// $cached_count will be false if transient does not exist.
		if ($cached_count === false || $force) {
			$count_posts = wp_count_posts(sanitize_key($post_type), 'readable');

			// Default to 0 $status if object does not have a value.
			$status_count = isset($count_posts->$status) ? absint($count_posts->$status) : 0;
			set_transient($transient, $status_count, DAY_IN_SECONDS * 7);
		}

		return $status_count;
	}
}

WPFM_Cache_Helper::instance();
