<div class="licence-row">
	<div class="plugin-info"><?php echo esc_html( $plugin['Title'] ); ?></div>
		<div class="plugin-author">
			<a target="_blank" href="//wpfoodmanager.com/"><?php echo esc_html($plugin['Author']); ?></a>				
		</div>
	</div>

	<div class="plugin-licence">
		<form method="post">
			<label for="<?php echo esc_attr( $plugin['TextDomain'] ); ?>_licence_key"><?php esc_html_e('License', 'wpfm-restaurant-manager'); ?>
				<input <?php echo esc_attr( $disabled ); ?> type="text" id="<?php echo esc_attr( $plugin['TextDomain'] ); ?>_licence_key" name="<?php echo esc_attr( $plugin['TextDomain'] ); ?>_licence_key" placeholder="XXXX-XXXX-XXXX-XXXX" value="<?php echo esc_attr( $licence_key ); ?>">
			</label>

			<label for="<?php echo esc_attr( $plugin['TextDomain'] ); ?>_email"><?php esc_html_e('Email', 'wpfm-restaurant-manager'); ?>
				<input <?php echo esc_attr($disabled); ?> type="email" id="<?php echo esc_attr( $plugin['TextDomain'] ); ?>_email" name="<?php echo esc_attr( $plugin['TextDomain'] ); ?>_email" placeholder="<?php esc_html_e('Email address', 'wpfm-restaurant-manager'); ?>" value="<?php echo esc_attr( $email ); ?>">
			</label>

			<?php if(!empty($licence_key) ) : ?>
				<a href="<?php echo esc_url( remove_query_arg( array( 'deactivated_licence', 'activated_licence' ), add_query_arg( $plugin['TextDomain'] . '_deactivate_licence', 1 ) ) ); ?>" class="button"><?php esc_html_e('Deactivate License', 'wpfm-restaurant-manager'); ?></a>
			<?php else : ?>
				<input type="submit" class="button" id="submit_wpfm_licence_key" name="submit_wpfm_licence_key" value="<?php esc_html_e('Activate License', 'wpfm-restaurant-manager'); ?>">
			<?php endif ; ?>
		</form>
	</div>
</div>