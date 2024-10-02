<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if(!function_exists('license_enqueue_scripts')){
    /**
     * license_enqueue_scripts function.
     *
     * @access public
     * @return void
     * @since 1.2
     */
    function license_enqueue_scripts() {
        if ( ! wp_style_is( 'wpfm-updater-styles', 'enqueued' ) ) {
            wp_register_style( 'wpfm-updater-styles', plugin_dir_url(__DIR__) . 'autoupdater/assets/css/backend.css' );
        }
    }
}

add_action('admin_menu', 'wpfm_addon_license_manage_menu', 10);

if (!function_exists('wpfm_addon_license_manage_menu')) {
	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 * @since 1.2
	 */
	function wpfm_addon_license_manage_menu() {
		if(!empty(get_wpfm_plugins_info())){
			add_submenu_page(
				'edit.php?post_type=food_manager',
				__('License', 'wpfm-restaurant-manager'),
				__('License', 'wpfm-restaurant-manager'),
				'manage_options',
				'wpfm_license',
				'wpfm_manage_license'
			);
		}
	}
}

/**
 * wpfm_manage_license function.
 *
 * @access public
 * @return void
 * @since 1.2
 */
if (!function_exists('wpfm_manage_license')) {
	function wpfm_manage_license() {
		wp_enqueue_style('wpfm-updater-styles');

		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins(); ?>

		<div class="wrap wpfm-updater-licence-wrap">
			<h2><?php _e('License', 'wpfm-restaurant-manager'); ?></h2>

			<div class="wpfm-updater-licence">
				<?php
				foreach ($plugins as $filename => $plugin) {
					if ($plugin['AuthorName'] == 'WP Food Manager' && is_plugin_active($filename) && !in_array($plugin['TextDomain'], ["wp-food-manager"]) && !in_array($plugin['TextDomain'], ["wpfm-rest-api"])) {
						$licence_key = get_option($plugin['TextDomain'] . '_licence_key');
						$email = get_option($plugin['TextDomain'] . '_email');

						$disabled = '';
						if (!empty($licence_key)) {
							$disabled = 'disabled';
						}

						include('templates/addon-licence.php');
					}
				} ?>
			</div>
			<div class="notice notice-info inline">
				<p><?php _e('Lost your license key?', 'wpfm-restaurant-manager'); ?> <a target="_blank" href="https://wpfoodmanager.com/lost-license-key/"><?php _e('Retrieve it here', 'wpfm-restaurant-manager'); ?></a>.</p>
			</div>
		</div>
		<?php
	}
} ?>