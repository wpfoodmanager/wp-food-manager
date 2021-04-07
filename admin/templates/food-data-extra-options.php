<?php

/**
 *  Template Extra Option panel
 */

$extra_options = get_post_meta($thepostid,'_wpfm_extra_options',true);

?>
<div id="extra_options_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
	<div class="wp_food_manager_meta_data">
		<div class="wpfm-options-wrapper wpfm-metaboxes">

			<?php if(!empty($extra_options)){
				$count = 1;
					foreach ($extra_options as $option_key => $option) {
						?>
						<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-<?php echo $count;?>" >
						<input type="hidden" name="repeated_options[]" value="<?php echo $count;?>">
						<h3 class="">
							<a href="#"  data-id="<?php echo $count;?>" class="wpfm-delete-btn">Remove</a>
							<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="<?php echo $count;?>"></div>
							<div class="tips wpfm-sort"></div>
							<strong class="attribute_name"><?php printf(__('%s','wp-food-manager'),$option_key);?></strong>
							<span class="attribute_key"> <input type="text" name="_option_key_<?php echo $count;?>" value="<?php echo $option_key;?>" readonly>
								</span>
						</h3>
						<div class="wpfm-metabox-content wpfm-options-box-<?php echo $count;?>">
						<div class="wpfm-content">
							<?php
								do_action('food_manager_food_data_start', $thepostid);
								$food_fields = $this->food_manager_data_fields();
								if(isset($food_fields['extra_options']))
								foreach ($food_fields['extra_options'] as $key => $field) {

									if(!isset($field['value']) || empty($field['value'])){
										$field['value'] = isset($option[$key]) ? $option[$key] : '';
									} 

									if( strpos($key, '_') !== 0 ) {
											$key  = '_'.$key.'_'.$count;	
										}

									$type = !empty($field['type']) ? $field['type'] : 'text';
									
									if ($type == 'wp-editor') $type = 'textarea';

									if (has_action('food_manager_input_' . $type)) {
										do_action('food_manager_input_' . $type, $key, $field);
									} elseif (method_exists($this, 'input_' . $type)) {
										call_user_func(array($this, 'input_' . $type), $key, $field);
									}
								}
								do_action('food_manager_food_data_end', $thepostid);
								?>
						</div>
					</div>

						<?php

						$count++;
					}
				
				}else{ ?>

			<!-- <div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-1" >
				<input type="hidden" name="repeated_options[]" value="1">
				<h3 class="">
					<a href="#" data-id="1" class="wpfm-delete-btn">Remove</a>
					<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="1"></div>
					<div class="tips wpfm-sort"></div>
					<strong class="attribute_name"><?php _e('Option 1','wp-food-manager');?></strong>
					<span class="attribute_key"> <input type="text" name="_option_key_1" value="_option_1" readonly>
						</span>
				</h3>
				<div class="wpfm-metabox-content wpfm-options-box-1">
					<div class="wpfm-content">
						<?php
							do_action('food_manager_food_data_start', $thepostid);
							$food_fields = $this->food_manager_data_fields();
							if(isset($food_fields['extra_options']))
							foreach ($food_fields['extra_options'] as $key => $field) {
								if( strpos($key, '_') !== 0 ) {
										$key  = '_'.$key.'_1';	
									}

								$type = !empty($field['type']) ? $field['type'] : 'text';
								if ($type == 'wp-editor') $type = 'textarea';

								if (has_action('food_manager_input_' . $type)) {
									do_action('food_manager_input_' . $type, $key, $field);
								} elseif (method_exists($this, 'input_' . $type)) {
									call_user_func(array($this, 'input_' . $type), $key, $field);
								}
							}
							do_action('food_manager_food_data_end', $thepostid);
							?>
					</div>
				</div>
			</div> -->
<?php } ?>


		</div>
		<div class="wpfm-actions">
			<button type="button" class="wpfm-add-button button button-primary" id="wpfm-add-new-option" data-row='<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-%%repeated-option-index%%">
				<input type="hidden" name="repeated_options[]" value="%%repeated-option-index%%">
				<h3 class="">
					<a href="#" data-id="%%repeated-option-index%%" class="wpfm-delete-btn">Remove</a>
					<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false"></div>
					<div class="tips wpfm-sort"></div>
					<strong class="attribute_name"><?php _e("Option %%repeated-option-index%%","wp-food-manager");?></strong>
					<span class="attribute_key"><input type="text" name="_option_key_%%repeated-option-index%%" value="_option_%%repeated-option-index%%" disabled="disabled">
						</span>
				</h3>
				<div class="wpfm-metabox-content wpfm-options-box">
					<div class="wpfm-content">
						<?php
							do_action("food_manager_food_data_start", $thepostid);
							$food_fields = $this->food_manager_data_fields();
							if(isset($food_fields["extra_options"]))
							foreach ($food_fields["extra_options"] as $key => $field) {
								if( strpos($key, '_') !== 0 ) {
										$key  = "_".$key."_%%repeated-option-index%%";	
									}

								$type = !empty($field["type"]) ? $field["type"] : "text";
								if ($type == "wp-editor") $type = "textarea";

								if (has_action("food_manager_input_" . $type)) {
									do_action("food_manager_input_" . $type, $key, $field);
								} elseif (method_exists($this, "input_" . $type)) {
									call_user_func(array($this, "input_" . $type), $key, $field);
								}
							}
							do_action("food_manager_food_data_end", $thepostid);
							?>
					</div>
				</div>
			</div>'>+ Add Option 
			</button>
		</div>


	</div>
</div>
