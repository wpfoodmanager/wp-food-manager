<?php
global $thepostid;

if(isset($food_id) && !empty($food_id))
$value = get_post_meta($food_id, '_' . $key, true);
else
$value = get_post_meta($thepostid, '_' . $key, true);
$food_id = (isset($_GET['food_id']) && !empty($_GET['food_id'])) ? esc_attr($_GET['food_id']) : esc_attr($thepostid);
$name = (!empty($field['name'])) ? esc_attr($field['name']) : esc_attr($key);
$exp_arr = explode("_", $key);

?>
<label class="wpfm-field-switch" for="<?php echo esc_attr($key); ?>">
    <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo $name; ?>" value="1" <?php echo (!empty($value) && $value == 1) ? 'checked' : ''; ?>>
    <span class="wpfm-field-switch-slider round"></span>
</label>