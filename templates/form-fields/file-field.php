<?php
if (is_admin()) {
	global $thepostid;
	if (!isset($field['value'])) {
		$field['value'] = get_post_meta($thepostid, '_' . $key, true);
	}
	if (empty($field['placeholder'])) {
		$field['placeholder'] = 'http://';
	}
	if (!empty($field['name'])) {
		$name = $field['name'];
	} else {
		$name = $key;
	} ?>
	<?php
	if (!empty($field['multiple'])) { ?>
		<span class="file_url">
			<?php foreach ((array) $field['value'] as $value) { ?>
				<span class="food-manager-uploaded-file multiple-file">
					<input type="hidden" name="<?php echo esc_attr($name); ?>[]" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($value); ?>" />
					<span class="food-manager-uploaded-file-preview">
						<?php if (in_array(pathinfo($value, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
							<img src="<?php echo esc_attr($value); ?>">
							<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
							<?php else :
							if (!wpfm_begnWith($value, "http")) {
								$value	= '';
							}
							if (!empty($value)) { ?>
								<span class="wpfm-icon">
									<strong style="display: block; padding-top: 5px;"><?php echo esc_attr(wp_basename($value)); ?></strong>
									<a target="_blank" href="<?php echo esc_attr($value); ?>"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>
								</span>
								<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
						<?php }
						endif; ?>
					</span>
				</span>
			<?php } ?>
		</span>
		<button class="button button-small wp_food_manager_upload_file_button_multiple" style="display: block;" data-uploader_button_text="<?php esc_attr_e('Use file', 'wp-food-manager'); ?>"><?php esc_attr_e('Upload', 'wp-food-manager'); ?></button>
	<?php } else { ?>
		<span class="food-manager-uploaded-file2">
			<span class="food-manager-uploaded-file">
				<?php if (!empty($field['value'])) :
					if (!wpfm_begnWith($field['value'], "http")) {
						$field['value']	= '';
					}
					if (is_array($field['value'])) {
						$field['value'] = get_the_post_thumbnail_url($thepostid, 'full');
					} ?>
					<input type="hidden" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" />
					<span class="food-manager-uploaded-file-preview">
						<?php if (in_array(pathinfo($field['value'], PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
							<img src="<?php echo esc_attr($field['value']); ?>">
							<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
							<?php else :
							if (!empty($field['value'])) { ?>
								<span class="wpfm-icon">
									<strong style="display: block; padding-top: 5px;"><?php echo esc_attr(wp_basename($field['value'])); ?></strong>
									<a target="_blank" href="<?php echo esc_attr($field['value']); ?>"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>
								</span>
								<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
						<?php }
						endif; ?>
					</span>
				<?php endif; ?>
			</span>
			<button class="button button-small wp_food_manager_upload_file_button" style="display: block;" data-uploader_button_text="<?php esc_attr_e('Use file', 'wp-food-manager'); ?>"><?php esc_attr_e('Upload', 'wp-food-manager'); ?></button>
		</span>
	<?php } ?>
<?php } else {
	$classes            = array('input-text');
	$allowed_mime_types = array_keys(!empty($field['allowed_mime_types']) ? $field['allowed_mime_types'] : get_allowed_mime_types());
	$field_name         = isset($field['name']) ? $field['name'] : $key;
	$field_name         .= !empty($field['multiple']) ? '[]' : '';
	if (!empty($field['ajax']) && food_manager_user_can_upload_file_via_ajax()) {
		wp_enqueue_script('wpfm-ajax-file-upload');
		$classes[] = 'wp-food-manager-file-upload';
	}
	$field_val_num = '';
	if (!empty($field['value']) && is_array($field['value'])) {
		$tmp_cnt = explode("_", $key);
		$counter = end($tmp_cnt);
		$field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';
	} else {
		$field_val_num = !empty($field['value']) ? $field['value'] : '';
	}
?>
	<div class="food-manager-uploaded-files">
		<?php
		if (!empty($field_val_num) || !empty($field['value'])) {
			if (is_array($field_val_num) && count($field_val_num) == 1 && $field['multiple'] == 1) {
				$field_val_num = array_shift($field_val_num);
				get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $field_val_num, 'field' => $field));
			} elseif (!empty($field_val_num) && $field['multiple'] == 0 && is_array($field['value']) && is_array($field_val_num) && !wpfm_isMultiArray($field['value'])) {
				$field['value'] = !empty($field['value'][0]) ? $field['value'][0] : '';
				get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $field['value'], 'field' => $field));
			} elseif (!empty($field_val_num) && is_array($field_val_num) && $field['multiple'] == 1) {
				foreach ($field_val_num as $value) :
					if (!empty($value)) {
						get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $value, 'field' => $field));
					}
				endforeach;
			} elseif (!empty($field['value']) && is_array($field['value']) && $field['multiple'] == 1 && wpfm_isMultiArray($field['value'])) {
				foreach ($field['value'] as $value) :
					if (!empty($value)) {
						get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $value, 'field' => $field));
					}
				endforeach;
			} elseif (!empty($field['value']) && is_array($field['value']) && $field['multiple'] == 1 && is_array($field_val_num)) {
				foreach ($field_val_num as $value) :
					if (!empty($value)) {
						get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $value, 'field' => $field));
					}
				endforeach;
			} else {
				if (is_array($field['value']) && $field['multiple'] == 1 && count($field['value']) == 1) {
					get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $field['value'][0], 'field' => $field));
				} else {
					if (is_array($field['value']) && $field['multiple'] == 1) {
						foreach ($field['value'] as $value) :
							if (wpfm_begnWith($value, "http")) {
								get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $value, 'field' => $field));
							}
						endforeach;
					} elseif (wpfm_begnWith($field['value'], "http") && !is_array($field['value'])) {
						get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $field['value'], 'field' => $field));
					} elseif (wpfm_begnWith($field_val_num, "http") && !is_array($field_val_num) && $field['multiple'] == 1) {
						get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => $field_name, 'value' => $field_val_num, 'field' => $field));
					}
				}
			}
		}
		?>
	</div>
	<input type="file" class="wp-food-manager-file-upload <?php echo esc_attr(implode(' ', $classes)); ?>" data-file_types="<?php echo esc_attr(implode('|', $allowed_mime_types)); ?>" <?php if (!empty($field['multiple'])) echo 'multiple'; ?> name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?><?php if (!empty($field['multiple'])) echo '[]'; ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo empty($field['placeholder']) ? '' : esc_attr($field['placeholder']); ?>" />
	<small class="description">
		<?php if (!empty($field['description'])) : ?>
			<?php echo $field['description']; ?>
		<?php else : ?>
			<?php printf(__('Maximum file size: %s.', 'wp-food-manager'), size_format(wp_max_upload_size())); ?>
		<?php endif; ?>
	</small>
<?php } ?>