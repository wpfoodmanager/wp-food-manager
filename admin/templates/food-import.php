<div class="wrap wp-food-manager-wrap">
    <h2><?php echo sprintf(__('Import %s', 'wp-food-manager'), $import_type_label); ?></h2>

    <table class="widefat">
        <tr>
            <th><?php _e('Field Name', 'wp-food-manager' ); ?></th>
            <th><?php _e('Field Value', 'wp-food-manager' ); ?></th>
        </tr>

        <?php if(!empty($sample_data)) :
            foreach ( $sample_data as $field_name => $field_value ) : ?>
                <tr>
                    <td><?php echo $field_name; ?></td>
                    <td><?php echo $field_value; ?></td>
                </tr>
            <?php endforeach; 
        endif; ?>
    </table>

	<form method="post" class="wp-food-manager-import">
		<table class="widefat">
            <tr>
                <td>
                    <input type="hidden" name="page" value="import-food" />
                    <input type="hidden" name="food_post_type" value="<?php echo $food_post_type; ?>" />
                    <input type="hidden" name="file_id" id="file_id" value="<?php echo $file_id; ?>" />
                    <input type="hidden" name="file_type" id="file_type" value="<?php echo $file_type; ?>" />
                    <input type="hidden" name="action" value="import" />
                    <input type="submit" class="button-primary" name="wp_food_manager_import" value="<?php _e( 'Import', 'wp-food-manager' ); ?>" />
                    <?php wp_nonce_field( 'food_manager_import' ); ?>
                </td>
            </tr>
        </table>
	</form>
</div>