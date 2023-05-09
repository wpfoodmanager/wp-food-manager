<?php
global $thepostid;
$name = (!empty($field['name'])) ? $field['name'] : $key;
$food_id = (isset($_GET['food_id']) && !empty($_GET['food_id'])) ? $_GET['food_id'] : $thepostid;
$exp_arr = explode("_", $key); ?>
<label class="wpfm-field-switch" for="<?php echo esc_attr($key); ?>">
    <input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($name); ?>" value="1" <?php echo (!empty($field['value']) && $field['value'] == 1) ? 'checked' : ''; ?>>
    <span class="wpfm-field-switch-slider round"></span>
</label>