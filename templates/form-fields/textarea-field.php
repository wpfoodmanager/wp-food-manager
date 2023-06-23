<?php
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value'])) {
    $tmp_cnt = explode("_", $key);
    $counter = end($tmp_cnt);
    $field_val_num = isset($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
    $field_val_num = !empty($field['value']) ? $field['value'] : '';
}
if (wpfm_begnWith($field_val_num, "http") || is_array($field_val_num)) {
    $field_val_num = '';
}
?>
<textarea cols="20" rows="3" class="input-text" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) :  esc_attr($key); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" maxlength="<?php echo !empty($field['maxlength']) ? $field['maxlength'] : ''; ?>" <?php if (!empty($field['required'])) echo 'required'; ?>><?php echo isset($field_val_num) ? esc_textarea($field_val_num) : ''; ?></textarea>
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>