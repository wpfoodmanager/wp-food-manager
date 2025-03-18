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
                    <?php _e('Import new .csv or .xml', 'wp-food-manager'); ?>
                </a>
            </th>
        </tr>
    </table>
</div>