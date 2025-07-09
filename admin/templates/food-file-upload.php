<div class="wrap wp-food-manager-wrap">
<h2><?php _e('Import Food & Menu', 'wp-food-manager'); ?></h2>
<div class="notice notice-warning is-dismissible">
	<p><?php _e('Before adding imported food items to the menu, make sure they have been successfully imported.', 'wp-food-manager'); ?></p>
</div>

<div class="wp-admin-timeline">
	<ul>
		<li class="wp-admin-timeline-active">Upload CSV file</li>
		<li>Column mapping</li>
		<li>Import</li>
		<li>Done!</li>
	</ul>
</div>


	
<div class="wp-admin-import-food-box">
	<div class="wp-admin-import-food-box-header">
		<h3><?php _e('Step 1: Upload CSV File', 'wp-food-manager'); ?></h3>
		<p><?php _e('Upload a CSV file containing food items. The file should be formatted correctly with the necessary columns for food items.', 'wp-food-manager'); ?></p>
	</div>
	<form method="post" class="wp-food-manager-upload-file">
		<table class="widefat">
			<tr>
		        <th><?php _e('Choose File', 'wp-food-manager' ); ?></th>
		        <td>
					<a href="javascript:void(0)" class="upload-file"><?php _e('Upload .csv file', 'wp-food-manager' ); ?></a>
					<span class="response-message"></span>
					<input type="hidden" name="file_id" id="file_id" value="" />
					<input type="hidden" name="file_type" id="file_type" value="" />
		        </td>
		    </tr>
		    <tr>
		        <th><?php _e('Content Type', 'wp-food-manager' ); ?></th>
		        <td>
					<select id="food_post_type" name="food_post_type">
						<?php foreach ( $food_post_type as $name => $label ) : ?>
							<option value="<?php echo esc_attr( $name ); ?>" ><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
		        </td>
		    </tr>
		    <tr>
		        <td colspan="2">
		        	<input type="hidden" name="page" value="import-food" />
		        	<input type="hidden" name="action" value="upload" />
		            <input type="button" class="button-primary" name="wp_food_manager_upload" value="<?php _e( 'Step 1', 'wp-food-manager' ); ?>" />
		            <?php wp_nonce_field( 'food_manager_file_upload' ); ?>
		        </td>
		    </tr>
		</table>
	</form>
</div>

	
</div>