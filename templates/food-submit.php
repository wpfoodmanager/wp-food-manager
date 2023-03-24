<?php

/**
 * Food Submission Form
 */
if (!defined('ABSPATH')) exit;

global $food_manager;
$add_food_page_id = get_option('food_manager_add_food_page_id');
$food_dashboard_page_id = get_option('food_manager_food_dashboard_page_id');
$extra_fields_options = get_post_meta($food_id, '_toppings', true) ? get_post_meta($food_id, '_toppings', true) : '';
if (!empty($extra_fields_options)) {
	$option_value_counts1 = array();
	for ($i = 1; $i <= count($extra_fields_options); $i++) {
		foreach ($extra_fields_options as $key => $value) {
			for ($j = 1; $j <= count($value['topping_options']); $j++) {
				$option_value_counts1[$key][] = $j;
			}
		}
	}
	$option_value_counts = array();
	foreach ($option_value_counts1 as $option_value_count) {
		$option_value_counts[] = array_unique($option_value_count);
	}
	array_unshift($option_value_counts, "");
	unset($option_value_counts[0]);
	$option_value_counts2 = array();
	for ($i = 1; $i <= count($extra_fields_options); $i++) {
		foreach ($extra_fields_options as $key => $value) {
			for ($j = 1; $j <= count($value['topping_options']); $j++) {
				$option_value_counts2[$key] = $value;
			}
		}
	}
	$option_value_counts3 = array();
	foreach ($option_value_counts2 as $option_value2_count) {
		$option_value_counts3[] = $option_value2_count;
	}
	array_unshift($option_value_counts3, "");
	unset($option_value_counts3[0]);
}
?>
<form action="<?php echo esc_url($action); ?>" method="post" id="submit-food-form" class="wpfm-form-wrapper wpfm-main food-manager-form" enctype="multipart/form-data">
	<?php if (apply_filters('add_food_show_signin', true)) : ?>
		<?php get_food_manager_template('account-signin.php'); ?>
	<?php endif; ?>
	<?php if (wpfm_user_can_post_food() || food_manager_user_can_edit_food($food_id)) : ?>
		<!-- Food Information Fields -->
		<h2 class="wpfm-form-title wpfm-heading-text"><?php _e('Food Details', 'wp-food-manager'); ?></h2>
		<?php
		if (isset($resume_edit) && $resume_edit) {
			printf('<p class="wpfm-alert wpfm-alert-info"><strong>' . __("You are editing an existing food. %s", "wp-food-manager") . '</strong></p>', '<a href="?new=1&key=' . $resume_edit . '">' . __('Create A New Food', 'wp-food-manager') . '</a>');
		}
		?>
		<?php do_action('add_food_fields_start'); ?>
		<?php
		$count = 1;
		foreach ($food_fields as $key => $field) :
		?>
			<fieldset class="wpfm-form-group fieldset-<?php echo esc_attr($key); ?>">
				<label for="<?php esc_attr_e($key); ?>" class="wpfm-form-label-text"><?php echo $field['label'] . apply_filters('add_food_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
					<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field)); ?>
				</div>
			</fieldset>
		<?php
	$count++;
	endforeach; ?>
		<?php do_action('add_food_fields_end'); ?>
		<!-- Extra options Fields -->
		<?php
		if ((isset($_POST['food_id']) && !empty($_POST['food_id'])) || (isset($_GET['action']) == 'edit')) {
			if ($food_extra_fields) : ?>
				<?php do_action('add_food_extra_fields_start'); ?>
				<h3 class="wpfm-form-title wpfm-heading-text"><?php _e('Extra Toppings', 'wp-food-manager'); ?></h3>
				<div class="wpfm-options-wrapper wpfm-metaboxes">
					<?php if (!empty($extra_fields_options)) {
						foreach ($option_value_counts3 as $key => $extra_fields_option) {
							$selected_check = (isset($extra_fields_option['topping_type']) && !empty($extra_fields_option['topping_type']) ? (($extra_fields_option['topping_type'] === 'checkbox') ? 'selected' : '') : '');
							$selected_radio = (isset($extra_fields_option['topping_type']) && !empty($extra_fields_option['topping_type']) ? (($extra_fields_option['topping_type'] === 'radio') ? 'selected' : ''): '');
							$selected_select = (isset($extra_fields_option['topping_type']) && !empty($extra_fields_option['topping_type']) ? (($extra_fields_option['topping_type'] === 'select') ? 'selected' : ''): '');
							$topping_required = (isset($extra_fields_option['topping_required']) && !empty($extra_fields_option['topping_required']) ? (($extra_fields_option['topping_required'] === 'yes') ? 'checked' : ''): '');
							$topping_key = str_replace(" ", "_", strtolower($extra_fields_option['topping_name']));
							?>
							<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-<?php echo $key; ?>">
								<input type="hidden" name="repeated_options[]" value="<?php echo $key; ?>" class="repeated-options">
								<h3 class="">
									<a href="javascript: void(0);" data-id="<?php echo $key; ?>" class="wpfm-delete-btn dashicons dashicons-dismiss">Remove</a>
									<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="<?php echo $key; ?>"></div>
									<div class="wpfm-sort"></div>
									<strong class="attribute_name"><?php echo $extra_fields_option['topping_name']; ?></strong>
									<span class="attribute_key"><input type="hidden" name="topping_key_<?php echo esc_attr($key); ?>" value="<?php echo $topping_key; ?>" readonly=""></span>
								</h3>
								<div class="wpfm-metabox-content wpfm-options-box">
									<div class="wpfm-content">
										<?php
										foreach ($food_extra_fields as $key2 => $field) :
											if ($key2 !== 'topping_options') {
												if ($key2 !== 'topping_name') {
													$key2 = "_" . $key2 . "_" . $key;
												} else {
													$key2 = $key2 . "_" . $key;
												}
												if (empty($field['value'])) {
													$field['value'] = get_post_meta($food_id, $key2, true);
												}
												$fieldClassLabel = '';
												if (!empty($field['type']) && $field['type'] == 'wp-editor') {
													$fieldClassLabel = 'wp-editor-field';
												}
												?>
												<fieldset class="wpfm-form-group fieldset<?php echo $key2; ?> <?php echo $fieldClassLabel; ?>" data-field-name="<?php echo $key2; ?>">
													<label for="<?php echo $key2; ?>" class="wpfm-form-label-text"><?php echo $field['label'] . apply_filters('add_food_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
													<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
														<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key2, 'field' => $field)); ?>
													</div>
												</fieldset>
											<?php }
											if ($key2 == 'topping_options') { ?>
												<fieldset class="wpfm-form-group fieldset_topping_options_<?php echo $key; ?> ">
													<label for="_topping_options_<?php echo $key; ?>">Options <small>(optional)</small></label>
													<div class="field ">
														<table class="widefat">
															<thead>
																<tr>
																	<th> </th>
																	<th>#</th>
																	<th>Label</th>
																	<th>Default</th>
																	<th>Price</th>
																	<th>Type of Price</th>
																	<th></th>
																</tr>
															</thead>
															<tbody class="ui-sortable">
																<?php
																foreach ($extra_fields_option['topping_options'] as $sub_value_count => $values) {
																	$option_default = ($values['option_default'] === 'on') ? 'checked' : '';
																	$option_fixed_amount = ($values['option_price_type'] === 'fixed_amount') ? 'selected' : '';
																	$option_quantity_based = ($values['option_price_type'] === 'quantity_based') ? 'selected' : '';
																	?>
																	<tr class="option-tr-<?php echo $sub_value_count; ?>">
																		<td><span class="wpfm-option-sort">☰</span></td>
																		<td><?php echo $sub_value_count; ?></td>
																		<td>
																			<input type="text" name="<?php echo $key; ?>_option_name_<?php echo $sub_value_count; ?>" value="<?php echo $values['option_name']; ?>" class="opt_name" pattern=".*\S+.*" required>
																		</td>
																		<td>
																			<input type="checkbox" name="<?php echo $key; ?>_option_default_<?php echo $sub_value_count; ?>" class="opt_default" <?php echo $option_default; ?>>
																		</td>
																		<td>
																			<input type="number" name="<?php echo $key; ?>_option_price_<?php echo $sub_value_count; ?>" value="<?php echo $values['option_price']; ?>" class="opt_price" step="any" required>
																		</td>
																		<td>
																			<select name="<?php echo $key; ?>_option_price_type_<?php echo $sub_value_count; ?>" class="opt_select">
																				<option value="quantity_based" <?php echo $option_quantity_based; ?>>Quantity Based</option>
																				<option value="fixed_amount" <?php echo $option_fixed_amount; ?>>Fixed Amount</option>
																			</select>
																		</td>
																		<td><a href="javascript: void(0);" data-id="<?php echo $sub_value_count; ?>" class="option-delete-btn dashicons dashicons-dismiss">Remove</a></td>
																		<input type="hidden" class="option-value-class" name="option_value_count[<?php echo $key; ?>][]" value="<?php echo $sub_value_count; ?>">
																	</tr>
																<?php } ?>
															</tbody>
															<tfoot>
																<tr>
																	<td colspan="7"> <a class="button wpfm-add-row" data-row="<tr class='option-tr-%%repeated-option-index3%%'>
								                    <td><span class='wpfm-option-sort'>☰</span></td>
								                    <td>%%repeated-option-index3%%</td>
								                    <td><input type='text' name='%%repeated-option-index2%%_option_name_%%repeated-option-index3%%' value='' class='opt_name' pattern='.*\S+.*' required></td>
								                    <td><input type='checkbox' name='%%repeated-option-index2%%_option_default_%%repeated-option-index3%%' class='opt_default'></td>
								                    <td><input type='number' name='%%repeated-option-index2%%_option_price_%%repeated-option-index3%%' value='' class='opt_price' required></td>
								                    <td>
								                        <select name='%%repeated-option-index2%%_option_price_type_%%repeated-option-index3%%' class='opt_select'>
								                        <option value='quantity_based'>Quantity Based</option>
								                        <option value='fixed_amount'>Fixed Amount</option>
								                        </select>
								                    </td>
								                    <td><a href='javascript: void(0);' data-id='%%repeated-option-index3%%' class='option-delete-btn dashicons dashicons-dismiss'>Remove</a></td>
								                    <input type='hidden' class='option-value-class' name='option_value_count[%%repeated-option-index2%%][]' value='%%repeated-option-index3%%'>
								                </tr>">Add Row</a>
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
						<button type="button" class="wpfm-add-button button button-primary" id="wpfm-add-new-option" data-row='<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-%%repeated-option-index%%">
						        <input type="hidden" name="repeated_options[]" value="%%repeated-option-index%%" class="repeated-options">
						        <h3 class="">
						            <a href="javascript: void(0);" data-id="%%repeated-option-index%%" class="wpfm-delete-btn dashicons dashicons-dismiss">Remove</a>
						            <div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="%%repeated-option-index%%"></div>
						            <div class="wpfm-sort"></div>
						            <strong class="attribute_name"><?php _e("Topping Option %%repeated-option-index%%", "wp-food-manager"); ?></strong>
						            <span class="attribute_key"><input type="hidden" name="topping_key_%%repeated-option-index%%" value="option_%%repeated-option-index%%" readonly>
						                </span>
						        </h3>
						        <div class="wpfm-metabox-content wpfm-options-box">
						            <div class="wpfm-content">
						                <?php
										foreach ($food_extra_fields as $key => $field) :

											if ($key == "topping_name") {
												if (strpos($key, '_') !== 0) {
													$key  = $key . '_%%repeated-option-index%%';
												}
											} else {
												if (strpos($key, '_') !== 0) {
													$key  = "_" . $key . "_%%repeated-option-index%%";
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
						    </div>'>+ Add Topping
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
			<input type="submit" name="submit_food" class="wpfm-theme-button" value="<?php esc_attr_e($submit_button_text); ?>" />
		</div>
	<?php else : ?>
		<?php do_action('add_food_disabled'); ?>
	<?php endif; ?>
</form>