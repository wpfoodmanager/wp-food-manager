<?php if(get_option( $plugin_slug . '_key_expire_pre' )) : 
	$expire_date = get_option( $plugin_slug . '_licence_expired' );
	$expire_date = new DateTime($expire_date);
	$expire_date = $expire_date->format('jS F, Y');	?>
	<div class="updated">
		<p class="wpfm-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-key-expire' . sanitize_title( $plugin_slug ), '1' ) ); ?>"><?php _e( 'Hide notice' ); ?></a></p>
		<p><?php printf( __('A licence key for <strong>"%1$s"</strong> will expired on<strong> %2$s</strong>. Please renew your subscription to continue the plugin work.', 'wpfm-invoice'), esc_html( $plugin_name ), esc_html( $expire_date ) ); ?></p>
	</div>
<?php else: ?>
	<div class="updated">
		<?php $plugin_url = get_admin_url() . 'plugins.php' . '#' . sanitize_title( $plugin_slug . '_licence_key_row' ); ?>
		<p class="wpfm-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-key-expire' . sanitize_title( $plugin_slug ), '1' ) ); ?>"><?php _e( 'Hide notice' ); ?></a></p>
		<p><?php printf( __('A licence key for <strong>"%1$s"</strong> is expired. Please renew your subscription to continue the plugin work.', 'wpfm-invoice'), esc_html( $plugin_name ) ); ?></p>
	</div>
<?php endif; ?>