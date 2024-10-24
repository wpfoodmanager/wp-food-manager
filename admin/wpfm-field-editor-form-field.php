<?php
if (empty($field_key)) {
	$field_key = $index;
}
$taxonomies = get_object_taxonomies((object) array('post_type' => 'food_manager'));
if ($taxonomies) {
	$count = $remove_tax = 0;
	foreach ($taxonomies as $taxonomy) {
		if ($taxonomy == 'food_manager_unit') {
			$remove_tax = $count;
		}
		$count++;
	}
}

if ($remove_tax != 0) {
	unset($taxonomies[$remove_tax]);
}
	
$field_types    = apply_filters(
	'food_manager_form_field_types',
	array(
		'text'             => esc_html__('Text', 'wp-food-manager'),
		'checkbox'         => esc_html__('Checkbox', 'wp-food-manager'),
		'date'             => esc_html__('Date', 'wp-food-manager'),
		'file'             => esc_html__('File', 'wp-food-manager'),
		'hidden'           => esc_html__('Hidden', 'wp-food-manager'),
		'multiselect'      => esc_html__('Multiselect', 'wp-food-manager'),
		'number'           => esc_html__('Number', 'wp-food-manager'),
		'radio'            => esc_html__('Radio', 'wp-food-manager'),
		'select'           => esc_html__('Select', 'wp-food-manager'),
		'term-checklist'   => esc_html__('Term Checklist', 'wp-food-manager'),
		'term-multiselect' => esc_html__('Term Multiselect', 'wp-food-manager'),
		'term-select'      => esc_html__('Term Select', 'wp-food-manager'),
		'term-select-multi-appearance'      => esc_html__('Term Multi Select Appearance', 'wp-food-manager'),
		'textarea'         => esc_html__('Textarea', 'wp-food-manager'),
		'wp-editor'        => esc_html__('WP Editor', 'wp-food-manager'),
		'url'              => esc_html__('URL', 'wp-food-manager'),
		'term-autocomplete' => esc_html__('Term Autocomplete', 'wp-food-manager'),
		'switch'    => esc_html__('Switch', 'wp-food-manager'),
	)
);

if ($field_key == 'topping_options') {
	$field_types['options'] = esc_html__('Options', 'wp-food-manager');
}
	
$wpfm_admin_class = '';
if ($field_key == 'food_category') {
	$wpfm_admin_class = '';
} elseif ($field_key == 'food_type') {
	$wpfm_admin_class = '';
} elseif ($field_key == 'food_tag') {
	$wpfm_admin_class = '';
} elseif ($field_key == 'food_ingredients') {
	$wpfm_admin_class = '';
} elseif ($field_key == 'food_nutritions') {
	$wpfm_admin_class = '';
}else {
	$wpfm_admin_class = apply_filters('wpfm_check_field_editor_key', 'wpfm-admin-common', $field_key);
}

// Disable field_types this array's field type is only for the core functionality types it doesn't usable in the field editor.
$disable_field_types = array('term-autocomplete', 'term-select-multi-appearance'); ?>

<tr data-field-type="<?php echo esc_attr($field['type']); ?>" class="<?php echo esc_attr($wpfm_admin_class); ?> <?php echo esc_attr($field_key); ?>">
	<td class="sort-column">&nbsp;</td>
	<td>
		<input type="text" class="input-text" name="<?php echo wp_kses_post($group_key); ?>[<?php echo esc_attr($field_key); ?>][label]" value="<?php echo esc_attr(stripslashes($field['label'])); ?>" />
	</td>
	<td class="field-type">
		<select name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][type]" class="field_type">

			<?php
			foreach ($field_types as $key => $type) {
				if (in_array($field_key, $disbled_fields)) {
					if ($key == $field['type']) {
						printf('<option value="' . esc_attr($key) . '" ' . selected($field['type'], $key, false) . ' class="wpfm-opt-val ' . esc_attr($key) . '">' . esc_html($type) . '</option>');
					}
				} else {
					if (!in_array($key, $disable_field_types)) {
						printf('<option value="' . esc_attr($key) . '" ' . selected($field['type'], $key, false) . ' class="wpfm-opt-val ' . esc_attr($key) . '">' . esc_html($type) . '</option>');
					}
				}
			} ?>

		</select>
	</td>
	<td>
		<input type="text" class="input-text" name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][description]" value="<?php echo esc_attr(isset($field['description']) ? stripslashes($field['description']) : ''); ?>" placeholder="<?php esc_attr_e('N/A', 'wp-food-manager'); ?>" />
	</td>
	<td class="field-options">
		<?php
		if (isset($field['options'])) {
			$options = implode(
				'|',
				array_map(
					function ($v, $k) {
						return sprintf($k . ' : %s ', $v);
					},
					$field['options'],
					array_keys($field['options'])
				)
			);
		} else {
			$options = '';
		} ?>
		<input type="text" class="input-text placeholder" name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][placeholder]" value="<?php if (isset($field['placeholder']) && !empty(esc_attr(stripslashes($field['placeholder'])))) {
																																									// translators: %s: placeholder text for the field
printf(esc_html__('%s', 'wp-food-manager'), esc_attr(stripslashes($field['placeholder'])));

																																								}	?>" placeholder="<?php esc_attr_e('N/A', 'wp-food-manager'); ?>" />
		<input type="text" class="input-text options" name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][options]" placeholder="<?php esc_attr_e('Pipe (|) separate options.', 'wp-food-manager'); ?>" value="<?php echo esc_attr($options); ?>" />
		<div class="file-options">
			<label class="multiple-files"><input type='hidden' value='0' name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][multiple]"><input type="checkbox" class="input-text" name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][multiple]" value="1" <?php checked(!empty($field['multiple']), true); ?> /> <?php esc_attr_e('Multiple Files?', 'wp-food-manager'); ?></label>
		</div>
		<div class="taxonomy-options">
			<label class="taxonomy-option">
				<?php if ($taxonomies) : ?>
					<select class="input-text taxonomy-select" name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][taxonomy]">
						<?php foreach ($taxonomies  as $taxonomy) : ?>
							<option value="<?php echo esc_attr($taxonomy); ?>" <?php
																				if (isset($field['taxonomy'])) {
																					echo esc_attr(selected($field['taxonomy'], $taxonomy, false));
																				}
																				?>>
								<?php echo esc_html($taxonomy); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</label>
		</div>
		<span class="na">&ndash;</span>
	</td>
	<td> <input type="text" value="_<?php echo esc_attr($field_key); ?>" readonly></td>
	<td>
		<?php if (!in_array($field_key, $disbled_fields)) : ?>
			<input type="checkbox" name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][admin_only]" value="1" <?php checked(!empty($field['admin_only']), true); ?> />
		<?php endif; ?>
	</td>
	<td>
		<input type="text" class="input-text placeholder" name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][priority]" value="<?php
																																							if (isset($field['priority'])) {
																																								echo esc_attr($field['priority']);
																																							}
																																							?>" placeholder="<?php esc_attr_e('N/A', 'wp-food-manager'); ?>" disabled />
	</td>
	<?php if ($group_key != 'toppings') { ?>
		<td>
			<select <?php if (in_array($field_key, $disbled_fields)) echo 'disabled'; ?> name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][tabgroup]" class="field_type">
				<?php
				$field['tabgroup'] = isset($field['tabgroup']) ? $field['tabgroup'] : 1;
				$Writepanels = WPFM_Writepanels::instance();
				$cnt = 1;
				foreach ($Writepanels->get_food_data_tabs() as $key => $tab) {
					$selected = ($field['tabgroup'] == $cnt) ? 'selected' : '';
					echo '<option value="' . $cnt . '"' . $selected . '>' . $tab['label'] . '</option>';
					$cnt++;
				}
				?>
			</select>
		</td>
	<?php } ?>
	<td class="field-rules">
		<?php if (!in_array($field_key, $disbled_fields) && $field['type'] != 'switch') : ?>
			<div class="rules">
				<select name="<?php echo esc_attr($group_key); ?>[<?php echo esc_attr($field_key); ?>][required]">
					<?php $field['required'] = (isset($field['required']) ? $field['required'] : false); ?>
					<option value="0" <?php
										if ($field['required'] == false) {
											echo wp_kses_post('selected="selected"');
										}
										?>>
						<?php esc_attr_e('Not Required', 'wp-food-manager'); ?></option>
					<option value="1" <?php
										if ($field['required'] == true) {
											echo wp_kses_post('selected="selected"');
										}
										?>>
						<?php esc_attr_e('Required', 'wp-food-manager'); ?></option>
				</select>
			</div>
		<?php endif; ?>
		<span class="na">&ndash;</span>
	</td>
	<td class="field-actions">
		<?php if (!in_array($field_key, $disbled_fields)) : ?>
			<a class="delete-field" href='#'>X</a>
		<?php endif; ?>
	</td>
</tr>