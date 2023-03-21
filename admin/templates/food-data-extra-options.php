<?php

/**
 *  Template Extra Option panel
 */
$extra_options = get_post_meta($thepostid, '_toppings', true); ?>
<div id="extra_options_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
	<div class="wp_food_manager_meta_data">
		<div class="wpfm-options-wrapper wpfm-metaboxes">
			<?php if (!empty($extra_options)) {
				$count = 1;
				foreach ($extra_options as $option_key => $option) { ?>
					<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-<?php echo esc_attr($count); ?>">
						<input type="hidden" name="repeated_options[]" value="<?php echo esc_attr($count); ?>" class="repeated-options">
						<h3 class="">
							<a href="javascript: void(0);" data-id="<?php echo esc_attr($count); ?>" class="wpfm-delete-btn">Remove</a>
							<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="<?php echo esc_attr($count); ?>"></div>
							<div class="wpfm-sort"></div>
							<strong class="attribute_name"><?php printf(__('%s', 'wp-food-manager'), $option['option_name']); ?></strong>
							<span class="attribute_key"> <input type="hidden" name="option_key_<?php echo esc_attr($count); ?>" value="<?php echo $option['option_key']; ?>" readonly>
							</span>
						</h3>
						<div class="wpfm-metabox-content wpfm-options-box-<?php echo esc_attr($count); ?>">
							<div class="wpfm-content">
								<?php
								do_action('food_manager_food_data_start', $thepostid);
								$food_extra_fields = $this->food_manager_data_fields();
								if (isset($food_extra_fields['extra_options']))
									foreach ($food_extra_fields['extra_options'] as $key => $field) {
										if (!isset($field['value']) || empty($field['value'])) {
											$field['value'] = isset($option[$key]) ? $option[$key] : '';
										}
										if ($key == "option_name") {
											if (strpos($key, '_') !== 0) {
												$key  = $key . '_' . $count;
											}
										} else {
											if (strpos($key, '_') !== 0) {
												$key  = '_' . $key . '_' . $count;
											}
										}
										$type = !empty($field['type']) ? $field['type'] : 'text';
										if ($type == 'wp-editor') $type = 'wp_editor';
										if (has_action('food_manager_input_' . $type)) {
											do_action('food_manager_input_' . $type, $key, $field);
										} elseif (method_exists($this, 'input_' . $type)) {
											call_user_func(array($this, 'input_' . $type), $key, $field);
										}
									}
								do_action('food_manager_food_data_end', $thepostid); ?>
							</div>
						</div>
						<?php $count++; ?>
					</div>
			<?php
				}
			}
			?>
			<div class="wpfm-actions">
				<button type="button" class="wpfm-add-button button button-primary" id="wpfm-add-new-option" data-row='<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-%%repeated-option-index%%">
					<input type="hidden" name="repeated_options[]" value="%%repeated-option-index%%" class="repeated-options">
					<h3 class="">
						<a href="javascript: void(0);" data-id="%%repeated-option-index%%" class="wpfm-delete-btn">Remove</a>
						<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="%%repeated-option-index%%"></div>
						<div class="wpfm-sort"></div>
						<strong class="attribute_name"><?php _e("Option %%repeated-option-index%%", "wp-food-manager"); ?></strong>
						<span class="attribute_key"><input type="hidden" name="option_key_%%repeated-option-index%%" value="option_%%repeated-option-index%%" readonly>
							</span>
					</h3>
					<div class="wpfm-metabox-content wpfm-options-box">
						<div class="wpfm-content">
							<?php
							do_action("food_manager_food_data_start", $thepostid);
							$food_extra_fields = $this->food_manager_data_fields();
							if (isset($food_extra_fields["extra_options"]))
								foreach ($food_extra_fields["extra_options"] as $key => $field) {
									if ($key == "option_name") {
										if (strpos($key, '_') !== 0) {
											$key  = $key . '_%%repeated-option-index%%';
										}
									} else {
										if (strpos($key, '_') !== 0) {
											$key  = "_" . $key . "_%%repeated-option-index%%";
										}
									}
									$type = !empty($field["type"]) ? $field["type"] : "text";
									if ($type == "wp-editor") $type = "textarea";
									if (has_action("food_manager_input_" . $type)) {
										do_action("food_manager_input_" . $type, $key, $field);
									} elseif (method_exists($this, "input_" . $type)) {
										call_user_func(array($this, "input_" . $type), $key, $field);
									}
								}
							do_action("food_manager_food_data_end", $thepostid); ?>
						</div>
					</div>
				</div>'>+ Add Topping
				</button>
			</div>
		</div>
	</div>
</div>