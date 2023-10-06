<?php

/**
 * Repeated fields is generated from this page .
 * Repeated fields for options.
 * This field is used in add food form.
 **/
?>
<table class="widefat">
    <thead>
        <tr>
            <th> </th>
            <th>#</th>
            <th>Label</th>
            <?php do_action('wpfm_repeated_option_name_label_after'); ?>
            <th>Price</th>
            <?php do_action('wpfm_repeated_option_price_label_after'); ?>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($field['value']) && !empty($field['value']) && is_array($field['value'])) {
            $count = 1;
            $wpfm_key_num = explode("_", $key)[2];
            foreach ($field['value'] as $op_key => $op_value) {
                $option_default = (isset($op_value['option_default']) && $op_value['option_default'] === 'on') ? 'checked' : '';
                $option_fixed_amount = (isset($op_value['option_price_type']) && $op_value['option_price_type'] === 'fixed_amount') ? 'selected' : '';
                $option_quantity_based = (isset($op_value['option_price_type']) && $op_value['option_price_type'] === 'quantity_based') ? 'selected' : '';
                $args = array(
                    'key' => $wpfm_key_num,
                    'sub_value_count' => esc_attr($count),
                    'values' => $op_value,
                    'option_default' => esc_attr($option_default),
                    'option_fixed_amount' => esc_attr($option_fixed_amount),
                    'option_quantity_based' => esc_attr($option_quantity_based),
                );
        ?>
                <tr class="option-tr-<?php echo esc_attr($count); ?>">
                    <td><span class="wpfm-option-sort">☰</span></td>
                    <td><?php echo esc_html($count); ?></td>
                    <td><input type="text" name="<?php echo esc_attr($wpfm_key_num); ?>_option_name_<?php echo esc_attr($count); ?>" value="<?php if (isset($op_value['option_name'])) echo $op_value['option_name']; ?>" class="opt_name" pattern=".*\S+.*" <?php echo (is_admin()) ? '' : 'required'; ?>></td>
                    <?php do_action('wpfm_repeated_option_name_after', $args); ?>
                    <td><input type="number" name="<?php echo esc_attr($wpfm_key_num); ?>_option_price_<?php echo esc_attr($count); ?>" value="<?php if (isset($op_value['option_price'])) echo $op_value['option_price']; ?>" class="opt_price" step="any" min="0" <?php echo (is_admin()) ? '' : 'required'; ?>></td>
                    <?php do_action('wpfm_repeated_option_price_after', $args); ?>
                    <td><a href="javascript: void(0);" data-id="<?php echo esc_attr($count); ?>" class="option-delete-btn dashicons dashicons-dismiss"></a></td>
                    <input type="hidden" class="option-value-class" name="option_value_count[]" value="<?php echo esc_attr($count); ?>">
                </tr>
        <?php $count++;
            }
        } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7">
                <a class="button wpfm-add-row" data-row="<tr class=&apos;option-tr-%%repeated-option-index3%%&apos;>
                    <td><span class=&apos;wpfm-option-sort&apos;>☰</span></td>
                    <td>%%repeated-option-index3%%</td>
                    <td><input type=&apos;text&apos; name=&apos;%%repeated-option-index2%%_option_name_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_name&apos; pattern=&apos;.*\S+.*&apos; <?php echo (is_admin()) ? '' : 'required'; ?>></td>
                    <?php do_action('wpfm_repeated_option_name_after', array()); ?>
                    <td><input type=&apos;number&apos; name=&apos;%%repeated-option-index2%%_option_price_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_price&apos; min=&apos;0&apos;  step=&apos;any&apos; <?php echo (is_admin()) ? '' : 'required'; ?>></td>
                    <?php do_action('wpfm_repeated_option_price_after', array()); ?>
                    <td><a href=&apos;javascript: void(0);&apos; data-id=&apos;%%repeated-option-index3%%&apos; class=&apos;option-delete-btn dashicons dashicons-dismiss&apos;></a></td>
                    <input type=&apos;hidden&apos; class=&apos;option-value-class&apos; name=&apos;option_value_count[]&apos; value=&apos;%%repeated-option-index3%%&apos;>
                </tr>">Add Row</a>
            </td>
        </tr>
    </tfoot>
</table>