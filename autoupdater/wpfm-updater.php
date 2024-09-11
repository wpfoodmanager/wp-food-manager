<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() ) {
	include( 'wpfm-updater-license.php' );
}

/**
 * WPFM_Updater.
 *
 * @version 1.0
 * @author  WPFM Team
 */
class WPFM_Updater {
	private $plugin_name = '';
	private $plugin_file = '';
	private $plugin_slug = '';
	private $errors      = array();
	private $plugin_data = array();

	/**
	 * Constructor, used if called directly.
	 */
	public function __construct( $file ) {
		$this->plugin_data = get_wpfm_plugins_info();
		$this->init_updates( $file );
	}

	//Init the updater.
	public function init_updates( $file ) {
		$this->plugin_data = get_wpfm_plugins_info();
		foreach($this->plugin_data as $plugin_info){
			register_activation_hook( $plugin_info['TextDomain'], array( $this, 'plugin_activation' ), 10 );
			register_deactivation_hook( $plugin_info['TextDomain'], array( $this, 'plugin_deactivation' ), 10 );
		}
		add_filter( 'block_local_requests', '__return_false' );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		include_once( 'wpfm-updater-api.php' );
		include_once( 'wpfm-updater-key-api.php' );
	}

	//Ran on WP admin_init hook.
	public function admin_init() {
		global $wp_version;
		$this->load_errors();

		add_action( 'shutdown', array( $this, 'store_errors' ) );
		add_action( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

		if ( current_user_can( 'update_plugins' ) ) {
			$this->admin_requests();
			$this->init_key_ui();
		}
	}
	
	//Process admin requests.
	private function admin_requests() {
		foreach($this->plugin_data as $plugin_info){
			if ( !empty( $_POST[ $plugin_info['TextDomain'] . '_licence_key' ] ) ) {
				$this->activate_licence_request($plugin_info);
			} elseif ( !empty( $_GET[ 'dismiss-' . sanitize_title( $plugin_info['TextDomain'] ) ] ) ) {
				update_option( $plugin_info['TextDomain'] . '_hide_key_notice', 1 );
			} elseif ( !empty( $_GET['activated_licence'] ) && $_GET['activated_licence'] === $plugin_info['TextDomain'] ) {
				$this->add_notice( array( $this, 'activated_key_notice' ) );
			} elseif ( !empty( $_GET['deactivated_licence'] ) && $_GET['deactivated_licence'] === $plugin_info['TextDomain'] ) {
				$this->add_notice( array( $this, 'deactivated_key_notice' ) );
			} elseif ( !empty( $_GET[ $plugin_info['TextDomain'] . '_deactivate_licence' ] ) ) {
				$this->deactivate_licence_request($plugin_info);
			}
		}
	}

	//Deactivate a licence request.
	private function deactivate_licence_request($plugin_info) {
		$this->deactivate_licence($plugin_info);
		wp_redirect( remove_query_arg( array( 'activated_licence', $plugin_info['TextDomain'] . '_deactivate_licence' ), add_query_arg( 'deactivated_licence', $plugin_info['TextDomain'] ) ) );
		exit;
	}
	
	//Activate a licence request.
	private function activate_licence_request($plugin_info) {
		if ( $this->activate_licence( $plugin_info ) ) {
			wp_redirect( remove_query_arg( array( 'deactivated_licence', $plugin_info['TextDomain'] . '_deactivate_licence' ), add_query_arg( 'activated_licence', $plugin_info['TextDomain'] ) ) );
			exit;
		} else {
			wp_redirect( remove_query_arg( array( 'activated_licence', 'deactivated_licence', $plugin_info['TextDomain'] . '_deactivate_licence' ) ) );
			exit;
		}
	}
	
	//Init keys UI.
	private function init_key_ui() {
		foreach($this->plugin_data as $plugin_info){
			$licence_key = get_option( $plugin_info['TextDomain'] . '_licence_key' );
			$email       = get_option( $plugin_info['TextDomain'] . '_email' );
			if ( ! $licence_key ) {
				add_action( 'admin_print_styles-plugins.php', array( $this, 'styles' ) );
				add_filter( 'plugin_action_links_' . $plugin_info['TextDomain'], array( $this, 'activation_links' ) );
				$this->add_notice( array( $this, 'key_notice' ) );
			} else {
				add_action( 'after_plugin_row_' . $plugin_info['TextDomain'], array( $this, 'multisite_updates' ), 10, 2 );
				add_filter( 'plugin_action_links_' . $plugin_info['TextDomain'], array( $this, 'deactivation_links' ) );
			}
		}
		$this->add_notice( array( $this, 'error_notices' ) );
		
	}

    //Add notices.
	private function add_notice( $callback ) {
		add_action( 'admin_notices', $callback );
		add_action( 'network_admin_notices', $callback );
	}

	/**
	 * Add an error message.
	 *
	 * @param string $message Your error message
	 * @param string $type    Type of error message
	 */
	public function add_error( $message, $type = '' ) {
		if ( $type ) {
			$this->errors[ $type ] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	//Load errors from option.
	public function load_errors() {
		foreach($this->plugin_data as $plugin_info){
			$this->errors = get_option( $plugin_info['TextDomain'] . '_errors', array() );
		}
	}

	//Store errors in option.
	public function store_errors() {
		foreach($this->plugin_data as $plugin_info){
			if ( sizeof( $this->errors ) > 0 ) {
				update_option( $plugin_info['TextDomain'] . '_errors', $this->errors );
			} else {
				delete_option( $plugin_info['TextDomain'] . '_errors' );
			}
		}
	}

	//Output errors.
	public function error_notices() {
		if ( !empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				include( 'templates/error-notice.php' );
				if ( $key !== 'invalid_key' ) {
					unset( $this->errors[ $key ] );
				}
			}
		}
	}

	//Ran on plugin-activation.
	public function plugin_activation() {
		$plugin_slug = dirname( plugin_basename( __FILE__ ) );

        // Log or use the plugin slug
		delete_option( $this->plugin_slug . '_hide_key_notice' );
	}

	//Ran on plugin-deactivation.
	public function plugin_deactivation() {
		$plugin_slug = dirname( plugin_basename( __FILE__ ) );

        // Log or use the plugin slug
		// $this->deactivate_licence();
	}

	//Try to activate a licence.
	public function activate_licence( $plugin_info ) {
		$licence_key = sanitize_text_field( $_POST[ $plugin_info['TextDomain'] . '_licence_key' ] );
		$email       = sanitize_text_field( $_POST[ $plugin_info['TextDomain'] . '_email' ] );

		try {
			if ( empty( $licence_key ) ) {
				throw new Exception( 'Please enter your licence key' );
			}

			if ( empty( $email ) ) {
				throw new Exception( 'Please enter the email address associated with your licence' );
			}

			$activate_results = json_decode( WPFM_Updater_Key_API::activate( array(
				'email'          => $email,
				'licence_key'    => $licence_key,
				'api_product_id' => $plugin_info['TextDomain']
			) ), true );

			if ( !empty( $activate_results['activated'] ) ) {
				$this->errors           = array();

				update_option( $plugin_info['TextDomain'] . '_licence_key', $licence_key );
				update_option( $plugin_info['TextDomain'] . '_email', $email );
				update_option( $plugin_info['TextDomain'] . '_licence_key_activate', 1 );
				update_option( $plugin_info['TextDomain'] . '_licence_key_activate', 1 );
				delete_option( $plugin_info['TextDomain'] . '_errors' );
				return true;
			} elseif ( $activate_results === false ) {
				throw new Exception( 'Connection failed to the Licence Key API server. Try again later.' );
			} elseif ( isset( $activate_results['error_code'] ) ) {
				throw new Exception( $activate_results['error'] );
			}

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return false;
		}
	}

	//Deactivate a licence.
	public function deactivate_licence($plugin_info) {
		$licence_key = get_option(  $plugin_info['TextDomain'] . '_licence_key', true );
		$reset = WPFM_Updater_Key_API::deactivate( array(
				'api_product_id' => $plugin_info['TextDomain'],
				'licence_key'    => $licence_key,
		) );                                                                    

		delete_option( $plugin_info['TextDomain'] . '_licence_key' );
		delete_option( $plugin_info['TextDomain'] . '_email' );
		delete_option( $plugin_info['TextDomain'] . '_licence_key_activate' );
		delete_option( $plugin_info['TextDomain'] . '_errors' );
		delete_site_transient( 'update_plugins' );
		$this->errors           = array();
		$api_key          = '';
		$activation_email = '';
	}

	//Activation links.
	public function activation_links( $links ) {
		$links[] = '<a href="' . add_query_arg( array('post_type' => 'food_manager', 'page' => 'wpfm_license'), admin_url( 'edit.php' ) ) . '">' . __('Activate licence', 'wpfm-restaurant-manager') . '</a>';
		return $links;
	}

	//Deactivation links.
	public function deactivation_links( $links ) {
		foreach ($this->plugin_data as $plugin) {
			$links[] = '<a href="' . remove_query_arg( array( 'deactivated_licence', 'activated_licence' ), add_query_arg( $plugin['TextDomain'] . '_deactivate_licence', 1 ) ) . '">' . __('Deactivate licence', 'wpfm-restaurant-manager') . '</a>';
		}
		return $links;
	}

	//Show a notice prompting the user to update.
	public function key_notice() {
		foreach ($this->plugin_data as $plugin) {
			if (  sizeof( $this->errors ) === 0 && (! get_option( $plugin['TextDomain'] . '_hide_key_notice' ) && ! get_option( $plugin['TextDomain'] . '_licence_key' ))) {
				include( 'templates/key-notice.php' );
			}
		}
	}

	//Activation success notice.
	public function activated_key_notice() {
		$plugin_name = '';
		$plugin_slug = $_GET['activated_licence'];
		foreach ($this->plugin_data as $plugin) {
			if ($plugin['TextDomain'] === $plugin_slug) {
				$plugin_name = $plugin['Name'];
				break;
			}
		}
		include( 'templates/activated-key.php' );
	}

	//Dectivation success notice.
	public function deactivated_key_notice() {
		$plugin_name = '';
		$plugin_slug = $_GET['deactivated_licence'];
		foreach ($this->plugin_data as $plugin) {
			if ($plugin['TextDomain'] === $plugin_slug) {
				$plugin_name = $plugin['Name'];
				break;
			}
		}
		include( 'templates/deactivated-key.php' );
	}

	//Enqueue admin styles.
	public function styles() {
		if ( ! wp_style_is( 'wpfm-updater-styles', 'enqueued' ) ) {
			wp_enqueue_style( 'wpfm-updater-styles', plugins_url( basename( plugin_dir_path( $this->plugin_file ) ), basename( $this->plugin_file ) ) . '/autoupdater/assets/css/backend.css' );
		}
	}

	//Check for plugin updates.
	public function check_for_updates( $check_for_updates_data ) {
		global $wp_version;

		if ( empty( $check_for_updates_data->checked ) ) {
			return $check_for_updates_data;
		}

		$plugin_names = array();
		$plugin_slugs = array();
		$plugin_licenses = array();
		$plugin_emails = array();
		$plugin_versions = array();
		
		foreach($this->plugin_data as $plugin_info){
			$licence_key = get_option(  $plugin_info['TextDomain'] . '_licence_key', true );
			$email       = get_option(  $plugin_info['TextDomain'] . '_email', true );
			if ( ! $licence_key ) {
				return $check_for_updates_data;
			}
			array_push($plugin_names,  $plugin_info['Name']);
			array_push($plugin_slugs,  $plugin_info['TextDomain']);
			array_push($plugin_versions,  $plugin_info['Version']);
			array_push($plugin_emails,  $email);
			array_push($plugin_licenses,  $licence_key);
		}

		// Set version variables.
		$response = $this->get_plugin_version($plugin_names, $plugin_slugs, $plugin_licenses, $plugin_emails, $plugin_versions);
		if(isset($response) && !empty($response) && is_object($response)){
			
			foreach ($this->plugin_data as $plugin_info) {
				$plugin_slug = $plugin_info['TextDomain'];
				if(isset($response->$plugin_slug->new_version)){
					
					$new_version = $response->$plugin_slug->new_version;
					if (isset($transient->checked[$plugin_slug]) && !isset($transient->response[$plugin_slug]) && version_compare( $new_version, $plugin_info['Version'], '>' ) ) {
						$check_for_updates_data->response[ $plugin_info['TextDomain'] ] = $response[$plugin_slug];

						// $transient->response[$plugin_slug] = array(
						// 	'theme'       => $plugin_slug,
						// 	'new_version' => $response_theme_version,
						// 	'package'     => $response->$theme_slug->package, // Replace with the actual theme package URL
						// 	'url'         => $response->$theme_slug->url,
						// 	'requires'    => $response->$theme_slug->requires,
						// );
					}
				}
			}
		}
			
		return $check_for_updates_data;
	}

	
	//Take over the Plugin info screen.
	public function plugins_api( $false, $action, $args ) {
		global $wp_version;
		foreach($this->plugin_data as $plugin_info){
			$licence_key = get_option(  $plugin_info['TextDomain'] . '_licence_key', true );
			$email       = get_option(  $plugin_info['TextDomain'] . '_email', true );
			if ( ! $licence_key ) {
				return $false;
			}

			if ( ! isset( $args->slug ) || ( $args->slug !== $plugin_info['TextDomain'] ) ) {
				return $false;
			}

			if ( $response = $this->get_plugin_info() ) {
				return $response;
			}
		}
	}

	/**
	 * Get plugin version info from API.
	 * @return array|bool
	 */
	public function get_plugin_version($plugin_names, $plugin_slugs, $plugin_licenses, $plugin_emails, $plugin_versions) {
		$response = WPFM_Updater_API::plugin_update_check( array(
			'plugin_name'    => $plugin_names,
			'version'        => $plugin_versions,
			'api_product_id' => $plugin_slugs,
			'licence_key'    => $plugin_licenses,
			'email'          => $plugin_emails
		) );
		if ( isset( $response->errors ) ) {
			delete_option( $this->plugin_slug . '_licence_key_activate' );
			$this->handle_errors( $response->errors );
		}

		// Set version variables.
		if ( isset( $response ) && is_object( $response ) && $response !== false ) {
			return $response;
		}

		return false;
	}

	/**
	 * Get plugin info from API.
	 * @return array|bool
	 */
	public function get_plugin_info() {
		$response = WPFM_Updater_API::plugin_information( array(
			'plugin_name'    => $this->plugin_name,
			'version'        => $this->plugin_data['Version'],
			'api_product_id' => $this->plugin_slug,
			'licence_key'    => $this->api_key,
			'email'          => $this->activation_email
		) );

		if ( isset( $response->errors ) ) {
			$this->handle_errors( $response->errors );
		}

		// If everything is okay return the $response.
		if ( isset( $response ) && is_object( $response ) && $response !== false ) {
			return $response;
		}

		return false;
	}

	/**
	 * Handle errors from the API.
	 * @param  array $errors
	 */
	public function handle_errors( $errors ) {
		if ( !empty( $errors['no_key'] ) ) {
			$this->add_error( sprintf( __('A licence key for %1$s could not be found. Maybe you forgot to enter a licence key when setting up %2$s.', 'wpfm-restaurant-manager'), esc_html( $this->plugin_data['Name'] ), esc_html( $this->plugin_data['Name'] ) ) );
		} elseif ( !empty( $errors['invalid_request'] ) ) {
			$this->add_error( 'Invalid update request' );
		} elseif ( !empty( $errors['invalid_key'] ) ) {
			$this->add_error( $errors['invalid_key'], 'invalid_key' );
		} elseif ( !empty( $errors['no_activation'] ) ) {
			// $this->deactivate_licence();
			$this->add_error( $errors['no_activation'] );
		}
	}

 	/**
     * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
     *
     * Based on code by Pippin Williamson.
     *
     * @param string  $file
     * @param array   $plugin
     */
    public function multisite_updates( $file, $plugin ) {
        if ( ! is_multisite() || is_network_admin() ) {
            return;
		}

		// Remove our filter on the site transient.
		remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );

		$update_cache = get_site_transient( 'update_plugins' );

		// Check if we have no version info, or every hour.
		if ( empty( $update_cache->response ) || empty( $update_cache->response[ $this->plugin_name ] ) || empty( $update_cache->last_checked ) || $update_cache->last_checked < strtotime( '-1 hour' ) ) {
			// Get plugin version info.
			if ( $version_info = $this->get_plugin_version() ) {
				//if ( version_compare( $this->plugin_data['Version'], $version_info->new_version, '<' ) ) {
				$update_cache->response[ $this->plugin_name ] = $version_info;
				//}
				$update_cache->last_checked                  = time();
				$update_cache->checked[ $this->plugin_name ] = $this->plugin_data['Version'];

				set_site_transient( 'update_plugins', $update_cache );
			}
		} else {
			$version_info = $update_cache->response[ $this->plugin_name ];
		}

		// Restore our filter.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );

        if ( !empty( $version_info->new_version ) && version_compare( $this->plugin_data['Version'], $version_info->new_version, '<' ) ) {

			$wp_list_table  = _get_list_table( 'WP_Plugins_List_Table' );
			$changelog_link = network_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $this->plugin_name . '&amp;section=changelog&amp;TB_iframe=true&amp;width=772&amp;height=597' );

            include( 'templates/ms-update.php' );
        }
    }

}
