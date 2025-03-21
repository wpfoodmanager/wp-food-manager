<div class="wrap wp-food-manager-wrap">
	<h2><?php _e('Food Import Successfully', 'wp-food-manager'); ?></h2>
    <table class="widefat">
        <tr>
            <th>
                <?php echo sprintf( __( 'Total: <b>%s</b> %s Successfully Import', 'wp-food-manager' ), $total_records, $import_type_label ); ?>
            </th>
        </tr>
        <tr>
            <th>
                <a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=food-manager-import" class="button">
                    <?php _e('Import new .csv file', 'wp-food-manager'); ?>
                </a>
                
                <?php
                    $button_text = '';
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
                        echo '<a href="' . esc_url($button_link) . '" class="button">';
                        echo esc_html($button_text);
                        echo '</a>';
                    }
                ?>
            </th>
        </tr>
    </table>
</div>