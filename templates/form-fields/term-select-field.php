<?php
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value']) && isset($field['value'])) {
	$tmp_cnt = explode("_", $key);
	$counter = end($tmp_cnt);
	$field_val_num = isset($field['value'][0]) ? $field['value'][0] : '';
} else {
	$field_val_num = !empty($field['value']) ? $field['value'] : '';
}
wpfm_dropdown_categories(esc_attr($field['taxonomy']), esc_attr($key), $field_val_num);
if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>