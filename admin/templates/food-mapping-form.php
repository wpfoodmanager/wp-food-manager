<?php wp_enqueue_script('wpfm-admin'); ?>
<div class="wrap wp-food-manager-wrap">
    <h2><?php echo sprintf(__('%s Mapping Form', 'wp-food-manager'), $import_type_label); ?></h2>
    
    <form method="post" class="wp-food-manager-mapping-form">
        <table class="widefat">
            <thead>
                <tr>
                    <th width="25%"><?php _e('File Field', 'wp-food-manager'); ?></th>
                    <th width="25%"><?php echo sprintf(__('%s Field', 'wp-food-manager'), $import_type_label); ?></th>
                    <th width="25%"><?php _e('Custom Field', 'wp-food-manager'); ?></th>
                    <th width="1%"><?php _e('&nbsp;', 'wp-food-manager'); ?></th>
                    <th width="24%"><?php _e('Default Value', 'wp-food-manager'); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php 
                if ($food_post_type == 'food_manager') {
                    // Handle fields for food_manager
                    if (!empty($file_head_fields)) : 
                        foreach ($file_head_fields as $key => $head_fields) : ?>
                            <tr>
                                <td>
                                    <input readonly type="text" name="file_field[<?php echo $key; ?>]" value="<?php echo $head_fields; ?>" />
                                </td>
                                <td>
                                    <select class="food-field" name="food_import_field[<?php echo $key; ?>]" id="food_import_field_<?php echo $key; ?>" data-type="text">
                                        <option value=""><?php echo sprintf(__('Select %s Field', 'wp-food-manager'), $import_type_label); ?></option>
                                        <option class="text" value="_post_id" <?php selected($head_fields, '_post_id'); ?>><?php _e('ID', 'wp-food-manager'); ?></option>
                                        <?php
                                        foreach ($food_import_fields as $group_key => $group_fields) : ?>
                                            <optgroup label="<?php echo $group_key; ?>">
                                                <?php foreach ($group_fields as $name => $field) : 
                                                    if (!in_array($field['type'], ['term-select'])) : 
                                                        if ($head_fields == '_thumbnail_id') { ?>
                                                            <option class="text" value="_<?php echo esc_attr($name); ?>" selected ><?php _e(esc_attr($field['label']), 'wp-food-manager'); ?></option>
                                                        <?php } else { ?>
                                                            <option class="text" value="_<?php echo esc_attr($name); ?>" <?php selected($head_fields, '_' . $name); ?> ><?php _e(esc_attr($field['label']), 'wp-food-manager'); ?></option>
                                                        <?php }
                                                    endif;
                                                endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>

                                        <?php if (!empty($taxonomies)) : ?>
                                            <optgroup label="<?php _e('Taxonomy', 'wp-food-manager') ?>">
                                                <?php foreach ($taxonomies as $name => $taxonomy) : ?>
                                                    <option class="taxonomy" value="<?php echo esc_attr($name); ?>" <?php selected($head_fields, $name); ?> ><?php echo esc_html($taxonomy->label); ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>

                                        <optgroup label="<?php _e('Other', 'wp-food-manager') ?>">
                                            <option class="custom-field" value="custom_field" ><?php _e('Custom Field', 'wp-food-manager') ?></option>
                                        </optgroup>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="custom_field[<?php echo $key; ?>]" class="food_import_field_<?php echo $key; ?>" value="" />
                                    <input type="hidden" name="taxonomy_field[<?php echo $key; ?>]" class="taxonomy_field_<?php echo $key; ?>" value="" />
                                </td>
                                <td>
                                    <input type="checkbox" class="add-default-value" id="default_value_<?php echo $key; ?>">
                                </td>
                                <td>
                                    <input type="hidden" name="default_value[<?php echo $key; ?>]" class="default_value_<?php echo $key; ?>" value="" />
                                    <select style="display: none;" class="default_value_<?php echo $key; ?>"></select>
                                </td>
                            </tr>
                        <?php endforeach;
                    endif;
                } elseif ($food_post_type == 'food_manager_menu') {
                    // Handle fields for food_manager_menu
                    if (!empty($file_head_fields)) : 
                        foreach ($file_head_fields as $key => $head_fields) : ?>
                            <tr>
                                <td>
                                    <input readonly type="text" name="file_field[<?php echo $key; ?>]" value="<?php echo $head_fields; ?>" />
                                </td>
                                <td>
                                    <select class="food-field" name="food_import_field[<?php echo $key; ?>]" id="food_import_field_<?php echo $key; ?>" data-type="text">
                                        <option value=""><?php echo sprintf(__('Select %s Field', 'wp-food-manager'), $import_type_label); ?></option>
                                        <option class="text" value="_post_id" <?php selected($head_fields, '_post_id'); ?>><?php _e('ID', 'wp-food-manager'); ?></option>

                                        <optgroup label="<?php _e('Food Menu', 'wp-food-manager') ?>">
                                        <option class="text" value="_menu_title" <?php selected($head_fields, '_menu_title'); ?>><?php _e('Menu Title', 'wp-food-manager'); ?></option>

                                        <?php
                                        
                                        // Loop through food_import_fields for food_manager_menu and generate options
                                        foreach ($food_import_fields as $group_key => $group_fields) : ?>
                                                <?php 
                                                        if ($head_fields == '_thumbnail_id') { ?>
                                                            <option class="text" value="_<?php echo esc_attr($group_fields); ?>" selected ><?php _e(esc_attr($group_fields), 'wp-food-manager'); ?></option>
                                                            <?php } elseif ($head_fields == '_wpfm_radio_icons') { ?>
                                                                <option class="text" value="_<?php echo esc_attr($group_fields); ?>" <?php selected($head_fields, '_' . $group_fields); ?> ><?php _e(esc_attr($group_fields), 'wp-food-manager'); ?></option>
                                                            <?php } else { ?>
                                                            <option class="text" value="_<?php echo esc_attr($group_fields); ?>" <?php selected($head_fields, '_' . $group_fields); ?> ><?php _e(esc_attr($group_fields), 'wp-food-manager'); ?></option>
                                                        <?php }
                                               
                                                 ?>
                                                 
                                        <?php endforeach; ?>
                                        </optgroup>
                                        <optgroup label="<?php _e('Other', 'wp-food-manager') ?>">
                                            <option class="custom-field" value="custom_field" ><?php _e('Custom Field', 'wp-food-manager') ?></option>
                                        </optgroup>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="custom_field[<?php echo $key; ?>]" class="food_import_field_<?php echo $key; ?>" value="" />
                                    <input type="hidden" name="taxonomy_field[<?php echo $key; ?>]" class="taxonomy_field_<?php echo $key; ?>" value="" />
                                </td>
                                <td>
                                    <input type="checkbox" class="add-default-value" id="default_value_<?php echo $key; ?>">
                                </td>
                                <td>
                                    <input type="hidden" name="default_value[<?php echo $key; ?>]" class="default_value_<?php echo $key; ?>" value="" />
                                    <select style="display: none;" class="default_value_<?php echo $key; ?>"></select>
                                </td>
                            </tr>
                        <?php endforeach;
                    endif;
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="5">
                        <input type="hidden" name="page" value="import_food_data" />
                        <input type="hidden" name="food_post_type" value="<?php echo $food_post_type; ?>" />
                        <input type="hidden" name="file_id" id="file_id" value="<?php echo $file_id; ?>" />
                        <input type="hidden" name="file_type" id="file_type" value="<?php echo $file_type; ?>" />
                        <input type="hidden" name="action" value="mapping" />
                        <input type="submit" class="button-primary mbtn" name="wp_food_manager_mapping" value="<?php esc_attr_e('Step 2', 'wp-food-manager'); ?>"  />
                        <?php wp_nonce_field('food_manager_mapping'); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>
