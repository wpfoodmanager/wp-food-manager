<?php

/**
 * Food Submission Form.
 */
if (!defined('ABSPATH')) exit;

global $food_manager;
$add_food_page_id = get_option('food_manager_add_food_page_id');
$food_dashboard_page_id = get_option('food_manager_food_dashboard_page_id');
$extra_fields_options = get_post_meta($food_id, '_food_toppings', true) ? get_post_meta($food_id, '_food_toppings', true) : '';
if (!empty($extra_fields_options)) {
	$topping_item_count1 = array();
	for ($i = 1; $i <= count($extra_fields_options); $i++) {
		foreach ($extra_fields_options as $key => $value) {
			for ($j = 1; $j <= count($value['_topping_options']); $j++) {
				$topping_item_count1[$key][] = $j;
			}
		}
	}
	$topping_item_count = array();
	foreach ($topping_item_count1 as $option_value_count) {
		$topping_item_count[] = array_unique($option_value_count);
	}
	array_unshift($topping_item_count1, "");
	unset($topping_item_count[0]);
	$topping_item_list = array();

	for ($i = 1; $i <= count($extra_fields_options); $i++) {
		foreach ($extra_fields_options as $key => $value) {
				$topping_item_list[$key] = $value;
		}
	}
	$topping_items = array();
	foreach ($topping_item_list as $option_value_list_count) {
		$topping_items[] = $option_value_list_count;
	}

	array_unshift($topping_items, "");
	unset($topping_items[0]);
} ?>
<form action="<?php echo esc_url($action); ?>" method="post" id="add-food-form" class="wpfm-form-wrapper wpfm-main food-manager-form" enctype="multipart/form-data">
	<?php if (apply_filters('add_food_show_signin', true)) : ?>
		<?php get_food_manager_template('account-signin.php'); ?>
	<?php endif; ?>
	<?php if (wpfm_user_can_post_food() || food_manager_user_can_edit_food($food_id)) : ?>
		<!-- Food Information Fields -->
		<h2 class="wpfm-form-title wpfm-heading-text"><?php esc_html_e('Food Details', 'wp-food-manager'); ?></h2>
		<?php
		if (isset($resume_edit) && $resume_edit) {
			printf('<p class="wpfm-alert wpfm-alert-info"><strong>' . esc_html(__("You are editing an existing food. %s", "wp-food-manager")) . '</strong></p>', '<a href="?new=1&key=' . esc_attr($resume_edit) . '">' . esc_html__('Create A New Food', 'wp-food-manager') . '</a>');
		}

		do_action('add_food_fields_start');

		$count = 1;
		foreach ($food_fields as $key => $field) :
			if (!isset($field['value'])) {
				$field['value'] = get_post_meta($food_id, '_' . $key, true);
			} ?>
			<fieldset class="wpfm-form-group fieldset-<?php echo esc_attr($key); ?>">
				<label for="<?php echo esc_attr($key); ?>" class="wpfm-form-label-text"><?php echo esc_html($field['label']) . apply_filters('add_food_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
					<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field, 'food_id' => $food_id)); ?>
				</div>
			</fieldset>
		<?php
			$count++;
		endforeach; ?>
		<?php do_action('add_food_fields_end'); ?>
		<!-- Extra options Fields -->
		<?php

		if ((isset($_POST['food_id']) && !empty($_POST['food_id'])) || (isset($_GET['action']) == 'edit')) {
			if ($topping_fields) : ?>
				<?php do_action('add_topping_fields_start'); ?>
				<h3 class="wpfm-form-title wpfm-heading-text"><?php _e('Extra Toppings', 'wp-food-manager'); ?></h3>
				<div class="wpfm-options-wrapper wpfm-metaboxes">
					<?php if (!empty($extra_fields_options)) {
						foreach ($topping_items as $key => $extra_fields_option) {
							$toppings = get_post_meta($food_id, '_food_toppings', true);
							$topping_key = str_replace(" ", "_", strtolower($extra_fields_option['_topping_name'])); ?>
							<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-<?php echo $key; ?>">
								<input type="hidden" name="repeated_options[]" value="<?php echo $key; ?>" class="repeated-options">
								<h3 class="">
									<a href="javascript: void(0);" data-id="<?php echo $key; ?>" class="wpfm-delete-btn dashicons dashicons-dismiss">Remove</a>
									<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="<?php echo $key; ?>"></div>
									<div class="wpfm-sort"></div>
									<strong class="attribute_name"><?php echo $extra_fields_option['_topping_name']; ?></strong>
									<span class="attribute_key"><input type="hidden" name="topping_key_<?php echo esc_attr($key); ?>" value="<?php echo $topping_key; ?>" readonly=""></span>
								</h3>
								<div class="wpfm-metabox-content wpfm-options-box">
									<div class="wpfm-content">
										<?php
										$count = 0;
										foreach ($topping_fields as $topping_field_key => $field) :
											if ($topping_field_key !== 'topping_options') {
												$field['value'] = isset($toppings[$key]['_' . $topping_field_key]) && !empty($toppings[$key]['_' . $topping_field_key]) ? $toppings[$key]['_' . $topping_field_key] : '';
												if ($topping_field_key !== 'topping_name') {
													$topping_field_key = $topping_field_key . "_" . $key;
												} else {
													$topping_field_key = $topping_field_key . "_" . $key;
												}
												$fieldClassLabel = '';
												if (!empty($field['type']) && $field['type'] == 'wp-editor') {
													$fieldClassLabel = 'wp-editor-field';
												} ?>
												<fieldset class="wpfm-form-group fieldset<?php echo $topping_field_key; ?> <?php echo $fieldClassLabel; ?>" data-field-name="<?php echo $topping_field_key; ?>">
													<label class="wpfm-form-label-text"><?php echo $field['label'] . apply_filters('add_food_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
													<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
														<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $topping_field_key, 'field' => $field)); ?>
													</div>
												</fieldset>
											<?php }
											if ($topping_field_key == 'topping_options') { ?>
												<fieldset class="wpfm-form-group fieldset_topping_options_<?php echo $key; ?> ">
													<label><?php _e('Options', 'wp-food-manager');?> <small><?php _e('(optional)', 'wp-food-manager');?></small></label>
													<div class="field ">
														<table class="widefat">
															<thead>
																<tr>
																	<th> </th>
																	<th>#</th>
																	<th><?php _e('Label', 'wp-food-manager');?></th>
																	<?php do_action('wpfm_repeated_option_name_label_after'); ?>
																	<th><?php _e('Price', 'wp-food-manager');?></th>
																	<?php do_action('wpfm_repeated_option_price_label_after'); ?>
																	<th></th>
																</tr>
															</thead>
															<tbody class="ui-sortable">
																<?php
																foreach ($extra_fields_option['_topping_options'] as $sub_value_count => $values) {
																	$option_default = (isset($values['option_default']) && $values['option_default'] === 'on') ? 'checked' : '';
																	$option_fixed_amount = (isset($values['option_price_type']) && $values['option_price_type'] === 'fixed_amount') ? 'selected' : '';
																	$option_quantity_based = (isset($values['option_price_type']) && $values['option_price_type'] === 'quantity_based') ? 'selected' : '';
																	$args = array(
																		'key' => $key,
																		'sub_value_count' => $sub_value_count,
																		'values' => $values,
																		'option_default' => $option_default,
																		'option_fixed_amount' => $option_fixed_amount,
																		'option_quantity_based' => $option_quantity_based,
																	); ?>
																	<tr class="option-tr-<?php echo $sub_value_count; ?>">
																		<td><span class="wpfm-option-sort">☰</span></td>
																		<td><?php echo $sub_value_count; ?></td>
																		<td>
																			<input type="text" name="<?php echo $key; ?>_option_name_<?php echo $sub_value_count; ?>" value="<?php echo $values['option_name']; ?>" class="opt_name" pattern=".*\S+.*" required>
																		</td>
																		<?php do_action('wpfm_repeated_option_name_after', $args); ?>
																		<td>
																			<input type="number" name="<?php echo $key; ?>_option_price_<?php echo $sub_value_count; ?>" value="<?php echo $values['option_price']; ?>" class="opt_price" step="any" min="0" required>
																		</td>
																		<?php do_action('wpfm_repeated_option_price_after', $args); ?>
																		<td><a href="javascript: void(0);" data-id="<?php echo $sub_value_count; ?>" class="option-delete-btn dashicons dashicons-dismiss"></a></td>
																		<input type="hidden" class="option-value-class" name="option_value_count[<?php echo $key; ?>][]" value="<?php echo $sub_value_count; ?>">
																	</tr>
																<?php } ?>
															</tbody>
															<tfoot>
																<tr>
																	<td colspan="7"> <a class="button wpfm-add-row" data-row="<?php
                                                                                                                            ob_start();
                                                                                                                            ?>
																	<tr class='option-tr-%%repeated-option-index3%%'>
								                    <td><span class='wpfm-option-sort'>☰</span></td>
								                    <td>%%repeated-option-index3%%</td>
								                    <td><input type='text' name='%%repeated-option-index2%%_option_name_%%repeated-option-index3%%' value='' class='opt_name' pattern='.*\S+.*' required></td>
													<?php do_action('wpfm_repeated_option_name_after', $args); ?>
								                    <td><input type='number' name='%%repeated-option-index2%%_option_price_%%repeated-option-index3%%' value='' class='opt_price' min='0' required></td>
													<?php do_action('wpfm_repeated_option_price_after', $args); ?>
								                    <td><a href='javascript: void(0);' data-id='%%repeated-option-index3%%' class='option-delete-btn dashicons dashicons-dismiss'></a></td>
								                    <input type='hidden' class='option-value-class' name='option_value_count[%%repeated-option-index2%%][]' value='%%repeated-option-index3%%'>
								                </tr>
												<?php echo esc_attr(ob_get_clean()); ?>
												"><?php _e('Add Row', 'wp-food-manager'); ?></a>
																	</td>
																</tr>
															</tfoot>
														</table>
													</div>
												</fieldset>
										<?php }
										endforeach; ?>
									</div>
								</div>
							</div>
					<?php }
					}
					?>
					<div class="wpfm-actions">
						<button type="button" class="wpfm-add-button button button-primary" id="wpfm-add-new-option" data-row='<?php
                                                                                                                            ob_start();
                                                                                                                            ?>
						<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-__repeated-option-index__">
						        <input type="hidden" name="repeated_options[]" value="__repeated-option-index__" class="repeated-options">
						        <h3 class="">
						            <a href="javascript: void(0);" data-id="__repeated-option-index__" class="wpfm-delete-btn dashicons dashicons-dismiss">Remove</a>
						            <div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="__repeated-option-index__"></div>
						            <div class="wpfm-sort"></div>
						            <strong class="attribute_name"><?php _e("Topping Option __repeated-option-index__", "wp-food-manager"); ?></strong>
						            <span class="attribute_key"><input type="hidden" name="topping_key___repeated-option-index__" value="option___repeated-option-index__" readonly>
						                </span>
						        </h3>
						        <div class="wpfm-metabox-content wpfm-options-box">
						            <div class="wpfm-content">
						                <?php
										foreach ($topping_fields as $key => $field) :

											if ($key == "topping_name") {
												if (strpos($key, '_') !== 0) {
													$key  = $key . '___repeated-option-index__';
												}
											} else {
												if (strpos($key, '_') !== 0) {
													$key  = $key . "___repeated-option-index__";
												}
											}
											$fieldClassLabel = '';
											if (!empty($field['type']) && $field['type'] == 'wp-editor') {
												$fieldClassLabel = 'wp-editor-field';
											}
											$type = !empty($field["type"]) ? $field["type"] : "text";
											if ($type == "wp-editor") $type = "textarea";

											$field['value'] = '';
										?>
											<fieldset class="wpfm-form-group fieldset<?php echo esc_attr($key); ?> <?php echo esc_attr($fieldClassLabel); ?>" data-field-name="<?php echo $key; ?>">
													<label for="<?php esc_attr_e($key); ?>" class="wpfm-form-label-text"><?php echo esc_attr($field['label']) . apply_filters('add_food_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
												<div class="field <?php echo esc_attr($field['required'] ? 'required-field' : ''); ?>">
													<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field)); ?>
												</div>
											</fieldset>
										<?php endforeach; ?>
						            </div>
						        </div>
						    </div>
							<?php echo esc_attr(ob_get_clean());
        					?>
							'><?php _e('+ Add Topping', 'wp-food-manager'); ?>
						</button>
					</div>
				</div>
		<?php
			endif;
		} ?>
		<div class="wpfm-form-footer">
			<input type="hidden" name="food_manager_form" value="<?php echo $form; ?>" />
			<input type="hidden" name="food_id" value="<?php echo esc_attr($food_id); ?>" />
			<input type="hidden" name="step" value="<?php echo esc_attr($step); ?>" />
			<input type="submit" name="add_food" class="wpfm-theme-button" value="<?php esc_attr_e($submit_button_text); ?>" />
		</div>
	<?php else : ?>
		<?php do_action('add_food_disabled'); ?>
	<?php endif; ?>
</form>