<?php
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value']) && isset($field['value'])) {
    $tmp_cnt = explode("_", $key);
    $counter = end($tmp_cnt);
    $field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
    $field_val_num = !empty($field['value']) ? $field['value'] : '';
}
?>
<input type="number" class="input-text <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) :  esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" value="<?php echo isset($field_val_num) ? esc_attr($field_val_num) : ''; ?>" min="<?php echo isset($field['min']) ? esc_attr($field['min']) : '0'; ?>" max="<?php echo isset($field['max']) ? esc_attr($field['max']) : ''; ?>" maxlength="75" <?php if (!empty($field['required'])) echo 'required'; ?> oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" step="any" />
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html(sanitize_textarea_field($field['description'])); ?></small><?php endif; ?>