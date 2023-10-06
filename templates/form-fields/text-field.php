<?php

/**
 * Shows the text field on food listing forms.
 *
 * This template can be overridden by copying it to yourtheme/wp-food-manager/form-fields/text-field.php.
 *
 * @see         https://www.wp-foodmanager.com/documentation/template-files-override/
 * @author      WP Food Manager
 * @package     WP Food Manager
 * @category    Template
 */
global $post;
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value']) && isset($field['value'])) {
    $tmp_cnt = explode("_", esc_attr($key));
    $counter = end($tmp_cnt);
    $field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';
    if ($key == 'food_tag') {
        $field_val_num = array_map(function ($value) {
            $term = get_term(esc_attr($value), 'food_manager_tag');
            return $term->name;
        }, $field['value']);
        $field_val_num = implode(', ', $field_val_num);
    }
} else {
    $field_val_num = !empty($field['value']) ? $field['value'] : '';
}
?>
<input type="text" class="input-text <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) :  esc_attr($key); ?>" placeholder="<?php echo empty($field['placeholder']) ? '' : esc_attr($field['placeholder']); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" value="<?php echo isset($field_val_num) ? esc_attr($field_val_num) : ''; ?>" maxlength="<?php echo !empty($field['maxlength']) ? $field['maxlength'] : ''; ?>" <?php if (!empty($field['required'])) echo 'required'; ?> />
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>