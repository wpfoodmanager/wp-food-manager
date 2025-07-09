<div class="wrap wp-food-manager-wrap">
    <h2><?php echo sprintf(__('Import %s', 'wp-food-manager'), $import_type_label); ?></h2>

    <div class="wp-admin-timeline">
        <ul>
            <li class="wp-admin-timeline-active">Upload CSV file</li>
            <li class="wp-admin-timeline-active">Column mapping</li>
            <li class="wp-admin-timeline-active">Import</li>
            <li>Done!</li>
        </ul>
    </div>

    <div class="wp-admin-import-food-box">
        <div class="wp-admin-import-food-box-header">
            <h3><?php _e('Step 3: Column Mapping', 'wp-food-manager'); ?></h3>
            <p><?php echo sprintf(__('Map the columns from your CSV file to the fields in %s.', 'wp-food-manager'), $import_type_label); ?></p>
        </div>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Field Name', 'wp-food-manager' ); ?></th>
                    <th><?php _e('Field Value', 'wp-food-manager' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($sample_data)) :
                    foreach ( $sample_data as $field_name => $field_value ) : ?>
                        <tr>
                            <td><?php echo $field_name; ?></td>
                            <td><?php echo $field_value; ?></td>
                        </tr>
                    <?php endforeach; 
                endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <form method="post" class="wp-food-manager-import">
                            <input type="hidden" name="page" value="import-food" />
                            <input type="hidden" name="food_post_type" value="<?php echo $food_post_type; ?>" />
                            <input type="hidden" name="file_id" id="file_id" value="<?php echo $file_id; ?>" />
                            <input type="hidden" name="file_type" id="file_type" value="<?php echo $file_type; ?>" />
                            <input type="hidden" name="action" value="import" />
                            <input type="submit" class="button-primary" name="wp_food_manager_import" value="<?php _e( 'Import', 'wp-food-manager' ); ?>" />
                            <?php wp_nonce_field( 'food_manager_import' ); ?>
                        </form>
                    </td>
                </tr>
            </tfoot>
        </table>

    </div>
    
</div>