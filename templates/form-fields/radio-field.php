<?php

/**
 * Radio Field. Example definition:
 *
 * 'test_radio' => array(
 * 		'label'    => __( 'Test Radio', 'wp-food-manager' ),
 * 		'type'     => 'radio',
 * 		'required' => false,
 * 		'default'  => 'option2',
 * 		'priority' => 1,
 * 		'options'  => array(
 * 			'option1' => 'This is option 1',
 * 		 	'option2' => 'This is option 2'
 * 		)
 * 	)
 */
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value'])) {
    $tmp_cnt = explode("_", $key);
    $counter = end($tmp_cnt);
    $field_val_num = isset($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
    $field_val_num = !empty($field['value']) ? $field['value'] : '';
}
$field['default'] = empty($field['default']) ? current(array_keys($field['options'])) : $field['default'];
$default = !empty($field_val_num) ? $field_val_num : $field['default'];
foreach ($field['options'] as $option_key => $value) : ?>
    <label>
        <input type="radio" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo esc_attr($key); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" value="<?php echo esc_attr($option_key); ?>" <?php if ($default == $option_key) { echo 'checked="checked"'; } ?> />
        <?php echo esc_html($value); ?>
    </label>
<?php endforeach; ?>
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html(sanitize_textarea_field($field['description'])); ?></small><?php endif; ?>