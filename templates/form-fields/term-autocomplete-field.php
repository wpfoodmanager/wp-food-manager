<?php

/**
 * Shows the autocomplete field on food listing forms.
 *
 * This template can be overridden by copying it to yourtheme/wp-food-manager/form-fields/term-autocomplete-field.php.
 *
 * @see         https://www.wp-foodmanager.com/documentation/template-files-override/
 * @author      WP Food Manager
 * @package     WP Food Manager
 * @category    Template
 * @version     1.0.2
 */
global $post;
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
wp_enqueue_script('jquery-ui-autocomplete');
wp_enqueue_script('wpfm-term-autocomplete');
?>
<input type="text" data-taxonomy="<?php echo $field['taxonomy']; ?>" class="input-text wpfm-autocomplete <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) :  esc_attr($key); ?>" placeholder="<?php echo empty($field['placeholder']) ? '' : esc_attr($field['placeholder']); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" value="<?php echo isset($field_val_num) ? esc_attr($field_val_num) : ''; ?>" maxlength="<?php echo !empty($field['maxlength']) ? $field['maxlength'] : ''; ?>" <?php if (!empty($field['required'])) echo 'required'; ?> />
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>