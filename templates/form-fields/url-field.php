<?php
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value'])) {
    $tmp_cnt = explode("_", $key);
    $counter = end($tmp_cnt);
    $field_val_num = $field['value'][$counter];
} else {
    $field_val_num = !empty($field['value']) ? $field['value'] : '';
}
if (!wpfm_begin_with($field_val_num, "http")) {
    $field_val_num = '';
} ?>
<input type="url" class="input-text <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) :  esc_attr($key); ?>" placeholder="<?php echo empty($field['placeholder']) ? '' : esc_attr($field['placeholder']); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" value="<?php echo esc_attr(isset($field_val_num) ? $field_val_num : ''); ?>" maxlength="<?php echo !empty($field['maxlength']) ? esc_attr($field['maxlength']) : ''; ?>" <?php if (!empty($field['required'])) echo esc_attr('required'); ?> />
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_textarea($field['description']); ?></small><?php endif; ?>