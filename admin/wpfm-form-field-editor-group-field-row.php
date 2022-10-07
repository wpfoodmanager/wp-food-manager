<tr class="group">
	<td colspan="10">
		<table class="widefat child_table">
			<thead>
				<tr>
					<th width="1%">&nbsp;</th>
					<th><?php esc_attr_e( 'Field Label', 'wp-food-manager' ); ?></th>
					<th width="1%"><?php esc_attr_e( 'Type', 'wp-food-manager' ); ?></th>
					<th><?php esc_attr_e( 'Description', 'wp-food-manager' ); ?></th>
					<th><?php esc_attr_e( 'Placeholder / Options', 'wp-food-manager' ); ?></th>
					<th width="1%"><?php esc_attr_e( 'Meta Key', 'wp-food-manager' ); ?></th>
					<th width="1%"><?php esc_attr_e( 'Only For Admin', 'wp-food-manager' ); ?></th>
					<th width="1%"><?php esc_attr_e( 'Priority', 'wp-food-manager' ); ?></th>
					<th width="1%"><?php esc_attr_e( 'Validation', 'wp-food-manager' ); ?></th>
					<th width="1%" class="field-actions">&nbsp;</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="4">
						<a class="button child-add-field" href="javascript:void(0)"><?php esc_attr_e( 'Add Child field', 'wp-food-manager' ); ?></a>
					</th>			
				</tr>
			</tfoot>
			<tbody class="child-form-fields" data-field="
			<?php
							ob_start();
							$child_index     = -1;
							$child_field_key = '';
							$child_field     = array(
								'type'        => 'text',
								'label'       => '',
								'placeholder' => '',
							);
							require 'wp-food-manager-form-field-editor-group-field.php';
			echo wp_kses_post(ob_get_clean());
							?>
						">
			</tbody>
		</table>
	</td>
</tr>
