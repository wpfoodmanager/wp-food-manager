<?php
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value'])) {
	$tmp_cnt = explode("_", $key);
	$counter = end($tmp_cnt);
	$field_val_num = isset($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
	$field_val_num = !empty($field['value']) ? $field['value'] : '';
}
?>
<select name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo esc_attr($key); ?>" <?php if (!empty($field['required'])) echo 'required'; ?> attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>">
	<?php foreach ($field['options'] as $key => $value) :
		$field_val = (!empty($field_val_num) && $field_val_num === $key) ? 'selected' : '';
	?>
		<option value="<?php echo esc_attr($key); ?>" <?php echo $field_val; ?>><?php echo esc_html($value); ?></option>
	<?php endforeach; ?>
</select>
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>