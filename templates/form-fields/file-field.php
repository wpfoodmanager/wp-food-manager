<?php

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
			get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $field_val_num, 'field' => $field));
		} elseif (!empty($field_val_num) && $field['multiple'] == 0 && is_array($field['value']) && is_array($field_val_num) && !wpfm_isMultiArray($field['value'])) {
			$field['value'] = !empty($field['value'][0]) ? $field['value'][0] : '';
			get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $field['value'], 'field' => $field));
		} elseif (!empty($field_val_num) && is_array($field_val_num) && $field['multiple'] == 1) {
			foreach ($field_val_num as $value) :
				if (!empty($value)) {
					get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $value, 'field' => $field));
				}
			endforeach;
		} elseif (!empty($field['value']) && is_array($field['value']) && $field['multiple'] == 1 && wpfm_isMultiArray($field['value'])) {
			foreach ($field['value'] as $value) :
				if (!empty($value)) {
					get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $value, 'field' => $field));
				}
			endforeach;
		} elseif (!empty($field['value']) && is_array($field['value']) && $field['multiple'] == 1 && is_array($field_val_num)) {
			foreach ($field_val_num as $value) :
				if (!empty($value)) {
					get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $value, 'field' => $field));
				}
			endforeach;
		} else {
			if (is_array($field['value']) && $field['multiple'] == 1 && count($field['value']) == 1) {
				get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $field['value'][0], 'field' => $field));
			} else {
				if (is_array($field['value']) && $field['multiple'] == 1) {
					foreach ($field['value'] as $value) :
						if (wpfm_begnWith($value, "http")) {
							get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $value, 'field' => $field));
						}
					endforeach;
				} elseif (wpfm_begnWith($field['value'], "http") && !is_array($field['value'])) {
					get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $field['value'], 'field' => $field));
				} elseif (wpfm_begnWith($field_val_num, "http") && !is_array($field_val_num) && $field['multiple'] == 1) {
					get_food_manager_template('form-fields/uploaded-file-html.php', array('key' => $key, 'name' => 'current_' . $field_name, 'value' => $field_val_num, 'field' => $field));
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