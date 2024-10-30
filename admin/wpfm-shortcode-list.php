<?php
/**
 * Shortcodes Page.
*/

if(!defined('ABSPATH')){
	 exit;// Exit if accessed directly
} 

if(!class_exists('WP_Food_Manager_Shortcode_List')) :

	/**
	 * WP_Food_Manager_Shortcode_List Class
	*/
	class WP_Food_Manager_Shortcode_List {
		/**
		 * Handles output of the reports page in admin.
		 */
		public function shortcode_list() { 
			wp_enqueue_script('wpfm-admin');
			$detail_link = esc_url("https://wpfoodmanager.com/knowledge-base/");

			$shortcode_plugins = apply_filters('wp_food_manager_shortcode_plugin', 
				array(
					'wp-food-manager' => __('WP Food Manager', 'wp-food-manager')
				)
			);	
			if(isset($_GET['plugin']) && !empty($_GET['plugin']))
				$plugin_slug = esc_attr($_GET['plugin']);
			else
				$plugin_slug = esc_attr('wp-food-manager');
			?>
			<style>
				.<?php echo esc_attr($plugin_slug);?>{display:table-row;}
			</style>
			<div class="wrap wp_food_manager wp_food_manager_shortcodes_wrap">
				<h2><?php _e('WP Food Manager Shortcodes', 'wp-food-manager'); ?></h2>
				<div class="wpfm-shortcode-page">

					<div class="wpfm-shortcode-filters">
						<select name="wpfm_shortcode_filter" id="wpfm_shortcode_filter">
							<option value=""><?php _e('Select Plugin', 'wp-food-manager');?></option> 
							<?php 
							foreach ($shortcode_plugins as $key => $value) { 
								if($key == $plugin_slug) 
									$selected = 'selected="selected"';
								else
									$selected = ''; 
								echo '<option class="level-0" value="'.esc_attr($key).'" '.$selected.'>'.esc_attr($value).'</option>';
							 } ?>
						</select>
						<input type="button" name="shortcode_list_filter_action" id="shortcode_list_filter_action" class="button" value="<?php _e('Filter', 'wp-food-manager');?>">
					</div>

					<div class="wpfm-shortcode-table">
						<table>
							<thead>
								<tr>
									<th><?php _e('Shortcode', 'wp-food-manager');?></th>
									<th><?php _e('Title', 'wp-food-manager');?></th>
									<th><?php _e('Description', 'wp-food-manager');?></th>
									<th><?php _e('Action', 'wp-food-manager');?></th>
								</tr>
							</thead>
							<tbody>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[foods]</td>
									<td><?php _e('The food listings', 'wp-food-manager');?></td>
									<td><?php _e('To display all the food listings, users need to create a new page from the Pages menu at the Admin Panel and add the shortcode  [foods] or can add the shortcode in the Template file that is attached to the page created.', 'wp-food-manager');?></td>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-listings/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></a></td>
								</tr>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[add_food]</td>
									<td><?php _e('The food submission form', 'wp-food-manager');?></td>
									<td><?php _e('To display the Food Submission Form, a user needs to create a new page from the Pages menu at the Admin Panel and then add the shortcode [add_food].', 'wp-food-manager');?></td>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-submission-form/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></a></td>
								</tr>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[food_dashboard]</td>
									<td><?php _e('The Food Dashboard', 'wp-food-manager');?></td>
									<td><?php _e('You can add an Food Dashboard to a new page by pasting the appropriate shortcode on the HTML editor.To display an Food Dashboard, users need to create a page from the pages menu at the Admin Panel and add the shortcode [food_dashboard].', 'wp-food-manager');?></td>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-dashboard/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></a></td>
								</tr>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[wpfm_food_menu]</td>
									<td><?php _e('The Food Menu', 'wp-food-manager');?></td>
									<td><?php _e('You can add a Food Menu to a new page by pasting the appropriate shortcode on the HTML editor. To display a specific food menu by menu ID, create a page from the Pages menu in the Admin Panel and add the shortcode [wpfm_food_menu id="YOUR_MENU_ID"]. Replace "YOUR_MENU_ID" with the actual ID of the food menu you want to display.', 'wp-food-manager'); ?>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-dashboard/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></td>
								</tr>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[food_menu id='']</td>
									<td><?php _e('The Food Menu Based on ID', 'wp-food-manager');?></td>
									<td><?php _e('You can add a Food Menu to a new page by pasting the appropriate shortcode on the HTML editor. To display a specific food menu by menu ID, create a page from the Pages menu in the Admin Panel and add the shortcode [food_menu id="YOUR_MENU_ID"]. Replace "YOUR_MENU_ID" with the actual ID of the food menu you want to display.', 'wp-food-manager'); ?>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-dashboard/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></td>
								</tr>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[restaurant_food_menu_title restaurant_id=""]</td>
									<td><?php _e('The Restaurant Food Menu', 'wp-food-manager');?></td>
									<td><?php _e('To display the title of a specific food menu for a restaurant, insert the shortcode in the HTML editor. Create a new page from the Pages menu in the Admin Panel and use the shortcode [restaurant_food_menu_title restaurant_id="YOUR_RESTAURANT_ID"]. Replace "YOUR_RESTAURANT_ID" with the actual ID of the restaurant to show its menu title.', 'wp-food-manager'); ?>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-dashboard/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></td>
								</tr>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[restaurant_food_menu restaurant_id=""]</td>
									<td><?php _e('The Restaurant Food Menu Title', 'wp-food-manager');?></td>
									<td><?php _e('To display the full food menu for a specific restaurant, use the following shortcode in the HTML editor. Create a new page from the Pages menu in the Admin Panel and add the shortcode [restaurant_food_menu restaurant_id="YOUR_RESTAURANT_ID"]. Replace "YOUR_RESTAURANT_ID" with the actual ID of the restaurant to showcase its menu.', 'wp-food-manager');?>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-dashboard/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></td>
								</tr>
								<tr class="shortcode_list wp-food-manager">
									<td class="wpfm-shortcode-td">[restaurant_food_menu count="yes" restaurant_id=""]</td>
									<td><?php _e('Apply Count Filter on Food Menu', 'wp-food-manager');?></td>
									<td><?php _e('To display the food menu for a specific restaurant along with the item count, use this shortcode in the HTML editor. Create a new page from the Pages menu in the Admin Panel and add the shortcode [restaurant_food_menu count="yes" restaurant_id="YOUR_RESTAURANT_ID"]. Replace "YOUR_RESTAURANT_ID" with the actual ID of the restaurant to show its menu and the total number of items.', 'wp-food-manager');?>
									<td><a class="button add-field" href="<?php echo $detail_link.'the-food-dashboard/';?>" target="_blank"><?php _e('View Details', 'wp-food-manager');?></td>
								</tr>
								<?php do_action('WP_Food_Manager_Shortcode_List', $detail_link); ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		<?php
		} 
	}
endif;
return new WP_Food_Manager_Shortcode_List();