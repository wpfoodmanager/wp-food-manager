<?php
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value']) && isset($field['value'])) {
   $tmp_cnt = explode("_", $key);
   $counter = end($tmp_cnt);
   $field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
   $field_val_num = !empty($field['value']) ? $field['value'] : '';
}
if (is_array($field_val_num)) {
   $field_val_num = '';
}
if (!preg_match("^[0-9_\\-]+$^", $field_val_num)) {
   $field_val_num = '';
}
?>

   <input type="text" class="input-text" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) :  esc_attr($key); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo isset($field_val_num) ? esc_attr($field_val_num) : ''; ?>" maxlength="<?php echo !empty($field['maxlength']) ? esc_attr($field['maxlength']) : ''; ?>" <?php if (!empty($field['required'])) echo esc_attr('required'); ?> data-picker="datepicker" />
   <?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html(sanitize_textarea_field($field['description'])); ?></small><?php endif; ?>
