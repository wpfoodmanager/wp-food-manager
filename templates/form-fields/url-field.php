<?php

/**
 * Shows the url field on food listing forms.
 *
 * This template can be overridden by copying it to yourtheme/wp-food-manager/form-fields/text-field.php.
 *
 * @author      WP Food Manager
 * @package     WP Food Manager
 * @category    Template
 * @version     1.8
 */
?>
<input type="url" class="input-text <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) :  esc_attr($key); ?>" placeholder="<?php echo empty($field['placeholder']) ? '' : esc_attr($field['placeholder']); ?>" attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" value="<?php echo isset($field['value']) ? esc_attr($field['value']) : ''; ?>" maxlength="<?php echo !empty($field['maxlength']) ? esc_attr($field['maxlength']) : ''; ?>" <?php if (!empty($field['required'])) echo esc_attr('required'); ?> />
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_textarea($field['description']); ?></small><?php endif; ?>