<div class="notice notice-error">
	<?php $license_page_url = get_admin_url() . 'edit.php?post_type=food_manager&page=wpfm_license'; ?>
	<p class="wpfm-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-' . sanitize_title( $plugin['TextDomain'] ), '1' ) ); ?>"><?php _e( 'Hide notice' ); ?></a></p>
	<p><?php printf( __('Please enter your licence key in <a href="%1$s">the plugin list</a> below to get activate all features of "%2$s".', 'wpfm-restaurant-manager'), esc_url($license_page_url) , esc_html( $plugin['Name'] ) ); ?></p>
	<p><small class="description"><?php printf( __('Lost your key? <a href="%s">Retrieve it here</a>.', 'wpfm-restaurant-manager'), esc_url( 'https://wpfoodmanager.com/lost-license-key/' ) ); ?></small></p>
</div>