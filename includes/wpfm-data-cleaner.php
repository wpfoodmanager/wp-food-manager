<?php
/**
 * Defines a class with methods for cleaning up plugin data. To be used when the plugin is deleted.
 *
 * @package Core
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * Methods for cleaning up all plugin data.
 *
 * @since 1.0.0
 */
class WPFM_Data_Cleaner {

	/**
	 * Custom post types to be deleted.
	 *
	 * @var $custom_post_types
	 */
	private static $custom_post_types = array(
		'food_manager',
		'food_manager_menu',
	);

	/**
	 * Taxonomies to be deleted.
	 *
	 * @var $taxonomies
	 */
	private static $taxonomies = array(
		'food_manager_type',
		'food_manager_ingredient',
		'food_manager_nutrition',
		'food_manager_unit',
		'food_manager_category',
		'food_manager_topping',
		'food_manager_tag'
	);

	/**
	 * Cron jobs to be unscheduled.
	 *
	 * @var $cron_jobs
	 */
	private static $cron_jobs = array(
		'food_manager_check_for_expired_foods',
		'food_manager_delete_old_previews',
		'food_manager_clear_expired_transients',
		'food_manager_email_daily_notices',
		'food_manager_usage_tracking_send_usage_data',
	);

	/**
	 * Options to be deleted.
	 *
	 * @var $options
	 */
	private static $options = array(
		'food_manager_add_food_form_fields',
		'food_manager_submit_toppings_form_fields',
		'food_manager_form_fields',
	);

	/**
	 * Site options to be deleted.
	 *
	 * @var $site_options
	 */
	private static $site_options = array(
		'food_manager_helper',
	);

	/**
	 * Transient names (as MySQL regexes) to be deleted. The prefixes "_transient_" and "_transient_timeout_" will be prepended.
	 *
	 * @var $transients
	 */
	private static $transients = array(
		'_food_manager_activation_redirect',
		'get_food_manager-transient-version',
		'fm_.*',
	);

	/**
	 * Role to be removed.
	 *
	 * @var $role
	 */
	private static $role = 'fm_restaurant_owner';

	/**
	 * Capabilities to be deleted.
	 *
	 * @var $caps
	 */
	private static $caps = array(
		'manage_food_managers',
		'edit_food_manager',
		'read_food_manager',
		'delete_food_manager',
		'edit_others_food_manager',
		'publish_food_manager',
		'read_private_food_manager',
		'delete_private_food_manager',
		'delete_published_food_manager',
		'delete_others_food_manager',
		'edit_private_food_manager',
		'edit_published_food_manager',
		'manage_food_manager_terms',
		'edit_food_manager_terms',
		'delete_food_manager_terms',
		'assign_food_manager_terms',
	);

	/**
	 * Cleanup all data.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public static function cleanup_all() {
		self::cleanup_custom_post_types();
		self::cleanup_taxonomies();
		self::cleanup_pages();
		self::cleanup_cron_jobs();
		self::cleanup_roles_and_caps();
		self::cleanup_transients();
		self::cleanup_options();
		self::cleanup_site_options();
	}

	/**
	 * Cleanup data for custom post types.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_custom_post_types() {
		foreach (self::$custom_post_types as $post_type) {
			$items = get_posts(
				array(
					'post_type'   => $post_type,
					'post_status' => 'any',
					'numberposts' => -1,
					'fields'      => 'ids',
				)
			);
			foreach ($items as $item) {
				self::delete_food_with_attachment($item);
				wp_delete_post($item);
			}
		}
	}

	/**
	 * wpfm_delete_food_with_attachment function.
	 *
	 * @access private
	 * @param int $post_id
	 * @return void
	 * @since 1.0.0
	 */
	private static function delete_food_with_attachment($post_id) {
		if (!in_array(get_post_type($post_id), ['food_manager'], true))
			return;

		$food_banner = get_post_meta($post_id, '_food_banner', true);
		if (!empty($food_banner)) {
			$wp_upload_dir = wp_get_upload_dir();
			$baseurl = $wp_upload_dir['baseurl'] . '/';
			if (is_array($food_banner)) {
				foreach ($food_banner as $banner) {
					$wp_attached_file = str_replace($baseurl, '', $banner);
					$args = array(
						'meta_key'          => '_wp_attached_file',
						'meta_value'        => $wp_attached_file,
						'post_type'         => 'attachment',
						'posts_per_page'    => 1,
					);
					$attachments = get_posts($args);
					if (!empty($attachments)) {
						foreach ($attachments as $attachment) {
							wp_delete_attachment($attachment->ID, true);
						}
					}
				}
			} else {
				$wp_attached_file = str_replace($baseurl, '', $food_banner);
				$args = array(
					'meta_key'          => '_wp_attached_file',
					'meta_value'        => $wp_attached_file,
					'post_type'         => 'attachment',
					'posts_per_page'    => 1,
				);
				$attachments = get_posts($args);
				if (!empty($attachments)) {
					foreach ($attachments as $attachment) {
						wp_delete_attachment($attachment->ID, true);
					}
				}
			}
		}
		$thumbnail_id = get_post_thumbnail_id($post_id);
		if (!empty($thumbnail_id)) {
			wp_delete_attachment($thumbnail_id, true);
		}
	}

	/**
	 * Cleanup data for taxonomies.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_taxonomies() {
		global $wpdb;
		foreach (self::$taxonomies as $taxonomy) {
			$terms = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT term_id, term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s",
					esc_sql($taxonomy)
				)
			);

			// Delete all data for each term.
			foreach ($terms as $term) {
				$wpdb->delete($wpdb->term_relationships, array('term_taxonomy_id' => $term->term_taxonomy_id));
				$wpdb->delete($wpdb->term_taxonomy, array('term_taxonomy_id' => $term->term_taxonomy_id));
				$wpdb->delete($wpdb->terms, array('term_id' => $term->term_id));
				$wpdb->delete($wpdb->termmeta, array('term_id' => $term->term_id));
			}
			if (function_exists('clean_taxonomy_cache')) {
				clean_taxonomy_cache($taxonomy);
			}
		}
	}

	/**
	 * Cleanup data for pages.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_pages() {
		// Trash the Add Food page.
		$add_food_page_id = get_option('food_manager_add_food_page_id');
		if ($add_food_page_id) {
			wp_delete_post($add_food_page_id, true);
		}

		// Trash the Food Dashboard page.
		$food_dashboard_page_id = get_option('food_manager_food_dashboard_page_id');
		if ($food_dashboard_page_id) {
			wp_delete_post($food_dashboard_page_id, true);
		}

		// Trash the foods page.
		$foods_page_id = get_option('food_manager_foods_page_id');
		if ($foods_page_id) {
			wp_delete_post($foods_page_id, true);
		}

		// Trash the Food Categories page.
		$submit_categories_form_page_id = get_option('food_manager_food_categories_page_id');
		if ($submit_categories_form_page_id) {
			wp_delete_post($submit_categories_form_page_id, true);
		}

		// Trash the Food Type page.
		$food_type_dashboard_page_id = get_option('food_manager_food_type_page_id');
		if ($food_type_dashboard_page_id) {
			wp_delete_post($food_type_dashboard_page_id, true);
		}
	}

	/**
	 * Cleanup data for options.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_options() {
		foreach (self::$options as $option) {
			delete_option(esc_html($option));
		}
	}

	/**
	 * Cleanup data for site options.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_site_options() {
		foreach (self::$site_options as $option) {
			delete_site_option(esc_html($option));
		}
	}

	/**
	 * Cleanup transients from the database.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_transients() {
		global $wpdb;

		foreach (array('_transient_', '_transient_timeout_') as $prefix) {
			foreach (self::$transients as $transient) {
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM $wpdb->options WHERE option_name RLIKE %s",
						esc_sql($prefix . $transient)
					)
				);
			}
		}
	}

	/**
	 * Cleanup data for roles and caps.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_roles_and_caps() {
		global $wp_roles;

		// Remove caps from roles.
		$role_names = array_keys($wp_roles->roles);
		foreach ($role_names as $role_name) {
			$role = get_role($role_name);
			self::remove_all_food_manager_caps($role);
		}

		// Remove caps and role from users.
		$users = get_users(array());
		foreach ($users as $user) {
			self::remove_all_food_manager_caps($user);
			$user->remove_role(esc_html(self::$role));
		}

		// Remove role.
		remove_role(esc_html(self::$role));
	}

	/**
	 * Helper method to remove WPFM caps from a user or role object.
	 *
	 * @param WP_User|WP_Role $object the user or role object.
	 * @return void
	 * @since 1.0.0
	 */
	private static function remove_all_food_manager_caps($object) {
		foreach (self::$caps as $cap) {
			$object->remove_cap(esc_html($cap));
		}
	}

	/**
	 * Cleanup cron jobs. Note that this should be done on deactivation, but doing it here as well for safety.
	 *
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private static function cleanup_cron_jobs() {
		foreach (self::$cron_jobs as $job) {
			wp_clear_scheduled_hook(esc_html($job));
		}
	}
}