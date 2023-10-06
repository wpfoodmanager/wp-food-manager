<?php
wp_enqueue_script('wp-food-manager-multiselect');
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value']) && isset($field['value'])) {
	$tmp_cnt = explode("_", $key);
	$counter = end($tmp_cnt);
	$field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
	$field_val_num = !empty($field['value']) ? $field['value'] : '';
}
?>
<select multiple="multiple" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>[]" id="<?php echo esc_attr($key); ?>" class="food-manager-multiselect" data-no_results_text="<?php _e('No results match', 'wp-food-manager'); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" data-multiple_text="<?php _e('Select Some Options', 'wp-food-manager'); ?>">
	<?php foreach ($field['options'] as $key => $value) : ?>
		<option value="<?php echo esc_attr($key); ?>" <?php if (!empty($field['value']) && is_array($field['value'])) if (in_array($key, $field['value'])) echo "selected"; ?>><?php echo esc_html($value); ?></option>
	<?php endforeach; ?>
</select>
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html(sanitize_textarea_field($field['description'])); ?></small><?php endif; ?>