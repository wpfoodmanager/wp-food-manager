<tr class="group">
	<td colspan="10">
		<table class="widefat child_table">
			<thead>
				<tr>
					<th width="1%">&nbsp;</th>
					<th><?php echo esc_attr__('Field Label', 'wp-food-manager'); ?></th>
					<th width="1%"><?php echo esc_attr__('Type', 'wp-food-manager'); ?></th>
					<th><?php echo esc_attr__('Description', 'wp-food-manager'); ?></th>
					<th><?php echo esc_attr__('Placeholder / Options', 'wp-food-manager'); ?></th>
					<th width="1%"><?php echo esc_attr__('Meta Key', 'wp-food-manager'); ?></th>
					<th width="1%"><?php echo esc_attr__('Only For Admin', 'wp-food-manager'); ?></th>
					<th width="1%"><?php echo esc_attr__('Priority', 'wp-food-manager'); ?></th>
					<th width="1%"><?php echo esc_attr__('Validation', 'wp-food-manager'); ?></th>
					<th width="1%" class="field-actions">&nbsp;</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="4">
						<a class="button child-add-field" href="javascript:void(0)"><?php echo esc_attr__('Add Child field', 'wp-food-manager'); ?></a>
					</th>
				</tr>
			</tfoot>
			<tbody class="child-form-fields" data-field="<?php
															ob_start();
															$child_index     = -1;
															$child_field_key = '';
															$child_field     = array(
																'type'        => 'text',
																'label'       => '',
																'placeholder' => '',
															);
															require esc_html('wp-food-manager-form-field-editor-group-field.php');
															echo wp_kses_post(ob_get_clean());
															?>">

			</tbody>
		</table>
	</td>
</tr>