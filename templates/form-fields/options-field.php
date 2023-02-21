<?php

/**
 * Repeated fields is generated from this page .
 * Repeated fields for the paid and free tickets.
 * This field is used in submit food form.
 **/
?>
<table class="widefat">
    <thead>
        <tr>
            <th> </th>
            <th>#</th>
            <th>Option name</th>
            <th>Default</th>
            <th>Price</th>
            <th>Type of price</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr>
            <td colspan="7">
                <a class="button wpfm-add-row" data-row="<tr class=&apos;option-tr-%%repeated-option-index3%%&apos;>
                    <td><span class=&apos;wpfm-option-sort&apos;>☰</span></td>
                    <td>%%repeated-option-index3%%</td>
                    <td><input type=&apos;text&apos; name=&apos;%%repeated-option-index2%%_option_value_name_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_name&apos; pattern=&apos;.*\S+.*&apos; required></td>
                    <td><input type=&apos;checkbox&apos; name=&apos;%%repeated-option-index2%%_option_value_default_%%repeated-option-index3%%&apos; class=&apos;opt_default&apos;></td>
                    <td><input type=&apos;number&apos; name=&apos;%%repeated-option-index2%%_option_value_price_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_price&apos;  step=&apos;any&apos; required></td>
                    <td>
                        <select name=&apos;%%repeated-option-index2%%_option_value_price_type_%%repeated-option-index3%%&apos; class=&apos;opt_select&apos;>
                        <option value=&apos;quantity_based&apos;>Quantity Based</option>
                        <option value=&apos;fixed_amount&apos;>Fixed Amount</option>
                        </select>
                    </td>
                    <td><a href=&apos;javascript: void(0);&apos; data-id=&apos;%%repeated-option-index3%%&apos; class=&apos;option-delete-btn dashicons dashicons-dismiss&apos;>Remove</a></td>
                    <input type=&apos;hidden&apos; class=&apos;option-value-class&apos; name=&apos;option_value_count[%%repeated-option-index2%%][]&apos; value=&apos;%%repeated-option-index3%%&apos;>
                </tr>">Add Row</a>
            </td>
        </tr>
    </tfoot>
</table>