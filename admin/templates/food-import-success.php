<div class="wrap wp-food-manager-wrap">
   <h2><?php echo sprintf( __( '%s Import Successfully', 'wp-food-manager' ),$import_type_label ); ?></h2>
   
   <div class="wp-admin-timeline">
        <ul>
            <li class="wp-admin-timeline-active">Upload CSV file</li>
            <li class="wp-admin-timeline-active">Column mapping</li>
            <li class="wp-admin-timeline-active">Import</li>
            <li class="wp-admin-timeline-active">Done!</li>
        </ul>
    </div>

    <div class="wp-admin-import-food-box">
        <div class="wp-admin-import-food-box-header">
            <h3><?php _e('Step 4: Import Completed', 'wp-food-manager'); ?></h3>
            <p><?php echo sprintf( __( 'All %s have been successfully imported.', 'wp-food-manager' ), $import_type_label ); ?></p>
        </div>

        <div class="wp-admin-import-success-step">
            <h4><?php echo sprintf( __( '<b>%s</b> %s Successfully Imported', 'wp-food-manager' ), $total_records, $import_type_label ); ?></h4>
            <div>
                <a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=food-manager-import" class="button-primary">
                    <?php _e('Import new .csv file', 'wp-food-manager'); ?>
                </a>
                        
                <?php $button_text = '';
                $button_link = '';
                // Check post type and assign appropriate values to the button
                if ($food_post_type == 'food_manager') {
                    $button_text = __('View Food', 'wp-food-manager');
                    $button_link = get_site_url() . '/wp-admin/edit.php?post_type='.$food_post_type;
                } elseif ($food_post_type == 'food_manager_menu') {
                    $button_text = __('View Menu', 'wp-food-manager');
                    $button_link = get_site_url() . '/wp-admin/edit.php?post_type='.$food_post_type;
                }

                if (!empty($button_text) && !empty($button_link)) {
                    echo '<a href="' . esc_url($button_link) . '" class="button-primary">';
                    echo esc_html($button_text);
                    echo '</a>';
                } ?>
            </div>
        </div>
    </div>
    
</div>