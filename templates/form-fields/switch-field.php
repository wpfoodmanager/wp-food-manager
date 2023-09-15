<?php
global $thepostid;
$value = get_post_meta($food_id, '_' . $key, true);
$name = (!empty($field['name'])) ? esc_attr($field['name']) : esc_attr($key);
$food_id = (isset($_GET['food_id']) && !empty($_GET['food_id'])) ? esc_attr($_GET['food_id']) : esc_attr($thepostid);
$exp_arr = explode("_", $key);

?>
<label class="wpfm-field-switch" for="<?php echo esc_attr($key); ?>">
    <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo $name; ?>" value="1" <?php echo (!empty($value) && $value == 1) ? 'checked' : ''; ?>>
    <span class="wpfm-field-switch-slider round"></span>
</label>