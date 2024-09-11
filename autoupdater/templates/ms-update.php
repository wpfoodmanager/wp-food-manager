<tr id="<?php echo esc_attr( sanitize_title( $this->plugin_slug . '_ms_update_row' ) ); ?>" class="plugin-update-tr">
	<td colspan="<?php echo $wp_list_table->get_column_count(); ?>" class="plugin-update colspanchange">
		<div class="update-message">
			<?php
				printf(
					__( 'There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> or <a href="%4$s">update now</a>.', 'wpfm-restaurant-manager'),
					esc_html( $this->plugin_data['Name'] ),
					esc_url( $changelog_link ),
					esc_html( $version_info->new_version ),
					esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->plugin_name, 'upgrade-plugin_' . $this->plugin_name ) )
				);
			?>
		</div>
	</td>
	<script>
		jQuery(function(){
			jQuery('tr#<?php echo esc_attr( $this->plugin_slug ); ?>_ms_update_row').prev().addClass('update');
		});
	</script>
</tr>