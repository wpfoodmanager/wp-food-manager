<?php
/**
 * Food Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $food_manager;

$food_id = isset($_POST['food_id']) ? $_POST['food_id'] : '';

$extra_fields_options = get_post_meta($food_id, '_wpfm_extra_options', true) ? get_post_meta($food_id, '_wpfm_extra_options', true) : '';

if(!empty($extra_fields_options)){
	$option_value_counts = array();
	for($i=1; $i <= count($extra_fields_options); $i++){
		foreach ($extra_fields_options as $key => $value) {
			for($j=1; $j <= count($value['option_options']); $j++){
				$option_value_counts[$i][] = $j;
			}
		}
	}
}

?>
<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-food-form" class="wpfm-form-wrapper wpfm-main food-manager-form" enctype="multipart/form-data">
	<?php if ( apply_filters( 'submit_food_form_show_signin', true ) ) : ?>
		<?php get_food_manager_template( 'account-signin.php' ); ?>
	<?php endif; ?>
	<?php if ( wpfm_user_can_post_food() || food_manager_user_can_edit_food( $food_id )   ) : ?>
		<!-- Food Information Fields -->
    	<h2 class="wpfm-form-title wpfm-heading-text"><?php _e( 'Food Details', 'wp-food-manager' ); ?></h2>
    <?php
    if ( isset( $resume_edit ) && $resume_edit ) {
		printf( '<p class="wpfm-alert wpfm-alert-info"><strong>' . __( "You are editing an existing food. %s","wp-food-manager" ) . '</strong></p>', '<a href="?new=1&key=' . $resume_edit . '">' . __( 'Create A New Food','wp-food-manager' ) . '</a>' );
	}

	?>
	
		<?php do_action( 'submit_food_form_food_fields_start' ); ?>
		<?php foreach ( $food_fields as $key => $field ) : ?>
			<fieldset class="wpfm-form-group fieldset-<?php echo esc_attr( $key ); ?>">
				<label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label'] . apply_filters( 'submit_food_form_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __( '(optional)', 'wp-food-manager' ) . '</small>', $field ); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
					<?php get_food_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
				</div>
			</fieldset>
		<?php endforeach; ?>
		<?php do_action( 'submit_food_form_food_fields_end' ); ?>

		<!-- Extra options Fields -->
		<?php //if (empty($extra_fields_options)) { ?>
			<?php if ($food_extra_fields) : ?>
				<?php do_action('submit_food_form_food_extra_fields_start'); ?>
				<h2 class="wpfm-form-title wpfm-heading-text"><?php _e('Extra Toppings', 'wp-food-manager'); ?></h2>
				<?php if(!empty($extra_fields_options)){
				foreach($option_value_counts as $key => $option_value_count){
					foreach($extra_fields_options as $option_key => $extra_fields_option){
						/*echo "<pre>";
						print_r($extra_fields_option);
						echo "</pre>";*/

						$selected_check = ($extra_fields_option['option_type'] === 'checkbox') ? 'selected' : '';
						$selected_radio = ($extra_fields_option['option_type'] === 'radio') ? 'selected' : '';
						$selected_select = ($extra_fields_option['option_type'] === 'select') ? 'selected' : '';

						$option_required = ($extra_fields_option['option_required'] === 'yes') ? 'checked' : '';

						$option_enable_desc = ($extra_fields_option['option_enable_desc'] === '1') ? 'checked' : '';
					?>
						<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-<?php echo $key; ?>">
							<input type="hidden" name="repeated_options[]" value="<?php echo $key; ?>" class="repeated-options">
							<h3 class="">
					            <a href="javascript: void(0);" data-id="<?php echo $key; ?>" class="wpfm-delete-btn dashicons dashicons-dismiss">Remove</a>
					            <div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="<?php echo $key; ?>"></div>
					            <div class="tips wpfm-sort"></div>
					            <strong class="attribute_name"><?php echo $extra_fields_option['option_name']; ?></strong>
					            <span class="attribute_key"><input type="text" name="option_key_<?php echo $key; ?>" value="<?php echo $option_key; ?>" readonly=""></span>
					        </h3>
							<div class="wpfm-metabox-content wpfm-options-box">
								<div class="wpfm-content">
									<fieldset class="wpfm-form-group fieldsetoption_name_<?php echo $key; ?> ">
										<label for="option_name_<?php echo $key; ?>">Name <small>(optional)</small></label>
										<div class="field ">
											<input type="text" class="input-text option_name_<?php echo $key; ?>" name="option_name_<?php echo $key; ?>" id="option_name_<?php echo $key; ?>" placeholder="Enter option name" attribute="" value="<?php echo $extra_fields_option['option_name']; ?>" maxlength="">
										</div>
									</fieldset>
									<fieldset class="wpfm-form-group fieldset_option_type_<?php echo $key; ?> ">
										<label for="_option_type_<?php echo $key; ?>">Option type <small>(optional)</small></label>
										<div class="field ">
											<select name="_option_type_<?php echo $key; ?>" id="_option_type_<?php echo $key; ?>" attribute="">
												<option value="checkbox" <?php echo esc_attr($selected_check);?> >Checkbox</option>
												<option value="radio" <?php echo esc_attr($selected_radio);?> >Radio Buttons</option>
												<option value="select" <?php echo esc_attr($selected_select);?> >Select Box</option>
											</select>
										</div>
									</fieldset>
									<fieldset class="wpfm-form-group fieldset_option_required_<?php echo $key; ?> ">
										<label for="_option_required_<?php echo $key; ?>">Required <small>(optional)</small></label>
										<div class="field ">
											<label>
												<input type="radio" name="_option_required_<?php echo $key; ?>" id="_option_required_<?php echo $key; ?>" attribute="" value="no" <?php echo $option_required; ?> > No</label>
											<label>
												<input type="radio" name="_option_required_<?php echo $key; ?>" id="_option_required_<?php echo $key; ?>" attribute="" value="yes" <?php echo $option_required; ?>> Yes</label>
										</div>
									</fieldset>
									<fieldset class="wpfm-form-group fieldset_option_enable_desc_<?php echo $key; ?>">
										<label for="_option_enable_desc_<?php echo $key; ?>">Description <small>(optional)</small></label>
										<span class="wpfm-input-field">
											<label class="wpfm-field-switch" for="_option_enable_desc_<?php echo $key; ?>">
												<input type="checkbox" class="input-checkbox" name="_option_enable_desc_<?php echo $key; ?>" id="_option_enable_desc_<?php echo $key; ?>" value="<?php echo $extra_fields_option['option_enable_desc']; ?>" attribute="" <?php echo $option_enable_desc; ?> >

												<span class="wpfm-field-switch-slider round"></span>
											</label>
										</span>
									</fieldset>
									
									<fieldset class="wpfm-form-group fieldset_option_description_<?php echo $key; ?> option-desc-common" style="<?php echo ($extra_fields_option['option_enable_desc'] !== '1') ? 'display: none;' : '';?>">
										<div class="field ">
											<textarea cols="20" rows="3" class="input-text" name="_option_description_<?php echo $key; ?>" id="_option_description_<?php echo $key; ?>" attribute="" placeholder="Enter the field description" maxlength=""><?php echo $extra_fields_option['option_description']; ?></textarea>
										</div>
									</fieldset>

									<fieldset class="wpfm-form-group fieldset_option_options_<?php echo $key; ?> ">
										<label for="_option_options_<?php echo $key; ?>">Options <span class="require-field">*</span></label>
										<div class="field ">
											<table class="widefat">
												<thead>
													<tr>
														<th> </th>
														<th>#</th>
														<th>Option name</th>
														<th>Default</th>
														<th>Price</th>
														<th>Type of price</th>
														<th></th>
													</tr>
												</thead>
												<tbody class="ui-sortable">
													<?php foreach($option_value_count as $key2 => $sub_value_count){
														$option_value_default = ($extra_fields_option['option_options'][$sub_value_count]['option_value_default'] === 'on') ? 'checked' : '';
														$option_fixed_amount = ($extra_fields_option['option_options'][$sub_value_count]['option_value_price_type'] === 'fixed_amount') ? 'selected' : '';
														$option_quantity_based = ($extra_fields_option['option_options'][$sub_value_count]['option_value_price_type'] === 'quantity_based') ? 'selected' : '';
													 ?>
														<tr class="option-tr-<?php echo $sub_value_count; ?>">
															<td><span class="wpfm-option-sort">☰</span></td>
															<td><?php echo $sub_value_count; ?></td>
															<td>
																<input type="text" name="<?php echo $key; ?>_option_value_name_<?php echo $sub_value_count; ?>" value="<?php echo $extra_fields_option['option_options'][$sub_value_count]['option_value_name']; ?>" class="opt_name">
															</td>
															<td>
																<input type="checkbox" name="<?php echo $key; ?>_option_value_default_<?php echo $sub_value_count; ?>" class="opt_default" <?php echo $option_value_default; ?>>
															</td>
															<td>
																<input type="number" name="<?php echo $key; ?>_option_value_price_<?php echo $sub_value_count; ?>" value="<?php echo $extra_fields_option['option_options'][$sub_value_count]['option_value_price']; ?>" class="opt_price">
															</td>
															<td>
																<select name="<?php echo $key; ?>_option_value_price_type_<?php echo $sub_value_count; ?>" class="opt_select">
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
						                    <td><input type='text' name='%%repeated-option-index2%%_option_value_name_%%repeated-option-index3%%' value='' class='opt_name'></td>
						                    <td><input type='checkbox' name='%%repeated-option-index2%%_option_value_default_%%repeated-option-index3%%' class='opt_default'></td>
						                    <td><input type='number' name='%%repeated-option-index2%%_option_value_price_%%repeated-option-index3%%' value='' class='opt_price'></td>
						                    <td>
						                        <select name='%%repeated-option-index2%%_option_value_price_type_%%repeated-option-index3%%' class='opt_select'>
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
								</div>
							</div>
						</div>
					<?php } 
					}
				} ?>
				<div class="wpfm-actions">
				    <button type="button" class="wpfm-add-button button button-primary" id="wpfm-add-new-option" data-row='<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-%%repeated-option-index%%">
				        <input type="hidden" name="repeated_options[]" value="%%repeated-option-index%%" class="repeated-options">
				        <h3 class="">
				            <a href="javascript: void(0);" data-id="%%repeated-option-index%%" class="wpfm-delete-btn dashicons dashicons-dismiss">Remove</a>
				            <div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="%%repeated-option-index%%"></div>
				            <div class="tips wpfm-sort"></div>
				            <strong class="attribute_name"><?php _e("Option %%repeated-option-index%%","wp-food-manager");?></strong>
				            <span class="attribute_key"><input type="text" name="option_key_%%repeated-option-index%%" value="option_%%repeated-option-index%%" readonly>
				                </span>
				        </h3>
				        <div class="wpfm-metabox-content wpfm-options-box">
				            <div class="wpfm-content">
				                <?php
				                foreach ($food_extra_fields as $key => $field) :

								if($key == 'option_enable_desc'){
				                	if( strpos($key, '_') !== 0 ) {
										$key  = "_".$key."_%%repeated-option-index%%";
									}
									?>
									<fieldset class="wpfm-form-group fieldset<?php echo esc_attr($key); ?>">
										<label for="<?php esc_attr_e($key); ?>"><?php echo esc_attr($field['label']) . apply_filters('submit_food_form_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
										<span class="wpfm-input-field">
											<label class="wpfm-field-switch" for="<?php esc_attr_e($key); ?>">
												<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field)); ?>
												<span class="wpfm-field-switch-slider round"></span>
											</label>
										</span>
									</fieldset>
								<?php } else {
									if($key == "option_name"){
										if( strpos($key, '_') !== 0 ) {
											$key  = $key.'_%%repeated-option-index%%';
										}
									} else {
										if( strpos($key, '_') !== 0 ) {
											$key  = "_".$key."_%%repeated-option-index%%";
										}
									}
									$descClass = "";
									if(!empty($field['value'])){
										$descClass = "";
									} else {
										$descClass = "option-desc-common";
									} ?>
									<fieldset class="wpfm-form-group fieldset<?php echo esc_attr($key); ?> <?php if(str_contains($key, 'description')){ echo esc_attr($descClass); } ?>">
										<?php 
										if(!str_contains($key, 'description')){ ?>
											<label for="<?php esc_attr_e($key); ?>"><?php echo esc_attr($field['label']) . apply_filters('submit_food_form_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
										<?php } ?>
										<div class="field <?php echo esc_attr($field['required'] ? 'required-field' : ''); ?>">
											<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field)); ?>
										</div>
									</fieldset>
								<?php } endforeach; ?>
				            </div>
				        </div>
				    </div>'>+ Add Option 
				    </button>
				</div>
				<?php /*foreach ($food_extra_fields as $key => $field) : ?>
				<?php
				if($key == 'option_enable_desc'){ ?>
					<fieldset class="wpfm-form-group fieldset-<?php echo esc_attr($key); ?>">
						<label for="<?php esc_attr_e($key); ?>"><?php echo esc_attr($field['label']) . apply_filters('submit_food_form_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
						<span class="wpfm-input-field">
							<label class="wpfm-field-switch" for="<?php esc_attr_e($key); ?>">
								<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field)); ?>
								<span class="wpfm-field-switch-slider round"></span>
							</label>
						</span>
					</fieldset>
				<?php } else { ?>
					<fieldset class="wpfm-form-group fieldset-<?php echo esc_attr($key); ?>">
						<?php if($key !== 'option_description'){ ?>
							<label for="<?php esc_attr_e($key); ?>"><?php echo esc_attr($field['label']) . apply_filters('submit_food_form_required_label', $field['required'] ? '<span class="require-field">*</span>' : ' <small>' . __('(optional)', 'wp-food-manager') . '</small>', $field); ?></label>
						<?php } ?>
						<div class="field <?php echo esc_attr($field['required'] ? 'required-field' : ''); ?>">
							<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field)); ?>
						</div>
					</fieldset>
				<?php } endforeach; ?>
				<?php do_action('submit_food_form_food_extra_fields_end'); */?>
			<?php endif; ?>
		<?php //} else {

		//} ?>
		
		<div class="wpfm-form-footer">
			<input type="hidden" name="food_manager_form" value="<?php echo $form; ?>" />
			<input type="hidden" name="food_id" value="<?php echo esc_attr( $food_id ); ?>" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
			<input type="submit" name="submit_food" class="wpfm-theme-button" value="<?php esc_attr_e( $submit_button_text ); ?>" />
		</div>
	<?php else : ?>
	
	  <?php do_action( 'submit_food_form_disabled' ); ?>
	  
	<?php endif; ?>
</form>