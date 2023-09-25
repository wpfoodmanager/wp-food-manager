<?php

/**
 * Template Extra Option panel.
 */
$food_toppings = get_post_meta($thepostid, '_food_toppings', true);
?>

<div id="toppings_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
	<div class="wp_food_manager_meta_data">
		<div class="wpfm-options-wrapper wpfm-metaboxes">
			<?php if (!empty($food_toppings)) {
				$count = 1;

				foreach ($food_toppings as $topping_key => $topping) {
			?>

					<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-<?php echo esc_attr($count); ?>">
						<input type="hidden" name="repeated_options[]" value="<?php echo esc_attr($count); ?>" class="repeated-options">
						<h3 class="">
							<a href="javascript: void(0);" data-id="<?php echo esc_attr($count); ?>" class="wpfm-delete-btn">Remove</a>
							<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="<?php echo esc_attr($count); ?>"></div>
							<div class="wpfm-sort"></div>
							<strong class="attribute_name"><?php printf(__('%s', 'wp-food-manager'), esc_html($topping['_topping_name'])); ?></strong>
							<span class="attribute_key"> <input type="hidden" name="topping_key_<?php echo esc_attr($count); ?>" value="<?php echo (isset($topping['topping_key']) && !empty($topping['topping_key']) ? esc_attr($topping['topping_key']) : ''); ?>" readonly>
							</span>
						</h3>
						<div class="wpfm-metabox-content wpfm-options-box-<?php echo esc_attr($count); ?>">
							<div class="wpfm-content">
								<?php
								do_action('food_manager_food_data_start', $thepostid);
								$topping_fields = $this->food_manager_data_fields();
								if (isset($topping_fields['toppings'])) {
									foreach ($topping_fields['toppings'] as $key => $field) {

										$field['required'] = false;
										if (!isset($field['value']) || empty($field['value'])) {
											$field['value'] = isset($topping['_' . $key]) ? $topping['_' . $key] : '';
										}

										if ($key == "_topping_name") {
											if (strpos($key, '_') !== 0) {
												$key  = $key . '_' . $count;
											}
										} else {
											if (strpos($key, '_') !== 0) {
												$key  = $key . '_' . $count;
											}
										}

										$type = !empty($field['type']) ? $field['type'] : 'text';
										if ($type == 'wp-editor') $type = 'wp_editor';
										if ($type == "term-autocomplete") $type = "term_autocomplete";
								?>

										<p class="wpfm-admin-postbox-form-field <?php echo esc_attr($key);
																				echo ($type == "wp_editor") ? ' wp-editor-field' : ''; ?>" <?php echo ($type == "wp_editor") ? 'data-field-name="' . esc_attr($key) . '"' : ''; ?>>
											<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : </label>
											<?php if ($type != 'options') echo '<span class="wpfm-input-field">'; ?>
											<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => esc_attr($key), 'field' => $field)); ?>
											<?php if ($type != 'options') echo '</span>'; ?>
										</p>
								<?php
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
				<button type="button" class="wpfm-add-button button button-primary" id="wpfm-add-new-option" data-row='<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-__repeated-option-index__">
					<input type="hidden" name="repeated_options[]" value="__repeated-option-index__" class="repeated-options">
					<h3 class="">
						<a href="javascript: void(0);" data-id="__repeated-option-index__" class="wpfm-delete-btn">Remove</a>
						<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="__repeated-option-index__"></div>
						<div class="wpfm-sort"></div>
						<strong class="attribute_name"><?php _e("Option __repeated-option-index__", "wp-food-manager"); ?></strong>
						<span class="attribute_key"><input type="hidden" name="topping_key___repeated-option-index__" value="option___repeated-option-index__" readonly>
							</span>
					</h3>
					<div class="wpfm-metabox-content wpfm-options-box">
						<div class="wpfm-content">
							<?php
							do_action("food_manager_food_data_start", $thepostid);
							$topping_fields = $this->food_manager_data_fields();
							if (isset($topping_fields["toppings"]))
								foreach ($topping_fields["toppings"] as $key => $field) {

									$field['required'] = false;
									if ($key == "_topping_name") {
										if (strpos($key, '_') !== 0) {
											$key  = $key . '___repeated-option-index__';
										}
									} else {
										if (strpos($key, '_') !== 0) {
											$key  = $key . "___repeated-option-index__";
										}
									}

									$type = !empty($field["type"]) ? $field["type"] : "text";
									if ($type == "wp-editor") $type = "wp_editor";
									if ($type == "term-autocomplete") $type = "term_autocomplete";
							?>
									<p class="wpfm-admin-postbox-form-field <?php echo esc_attr($key);
																			echo ($type == "wp_editor") ? ' wp-editor-field' : ''; ?>" <?php echo ($type == "wp_editor") ? 'data-field-name="' . esc_attr($key) . '"' : ''; ?>>
										<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : </label>
										<?php if ($type != 'options') echo '<span class="wpfm-input-field">'; ?>
											<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => esc_attr($key), 'field' => $field)); ?>
										<?php if ($type != 'options') echo '</span>'; ?>
									</p>
									<?php
								}
							do_action("food_manager_food_data_end", $thepostid); ?>
						</div>
					</div>
				</div>'>+ Add Topping
				</button>
			</div>

			<?php
			if (isset($food_fields['food']))
				foreach ($food_fields['food'] as $key => $field) {
					$field['required'] = false;
					if (!isset($field['value'])) {
						$field['value'] = get_post_meta($thepostid, '_' . $key, true);
					}
					$field['tabgroup'] = isset($field['tabgroup']) ? $field['tabgroup'] : 0;
					if (!in_array($key, $disbled_fields_for_admin) && $field['tabgroup'] == $tab['priority']) {
						$type = !empty($field['type']) ? $field['type'] : 'text';
						if ($type == 'wp-editor') $type = 'wp_editor';
			?>
					<p class="wpfm-admin-postbox-form-field <?php echo esc_attr($key); ?>">
						<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : </label>
						<span class="wpfm-input-field">
							<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => esc_attr($key), 'field' => $field)); ?>
						</span>
					</p>
			<?php }
				} ?>
		</div>
	</div>
</div>