<?php
global $thepostid;
$field_val = get_post_meta($thepostid, '_' . $key, true);
if (empty($field['value']) || empty($field_val)) {
    $field['value'] = get_post_meta($thepostid, '_' . $key, true);
}
$name = (!empty($field['name'])) ? $field['name'] : $key;
$exp_arr = explode("_", $key); ?>
<label class="wpfm-field-switch" for="<?php echo esc_attr($key); ?>">
    <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($field['value'], 1); ?>>
    <span class="wpfm-field-switch-slider round"></span>
</label>