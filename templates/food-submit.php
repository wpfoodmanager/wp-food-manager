<?php
/**
 * Food Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $food_manager;
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
		<?php //if (get_option('enable_food_organizer')) : ?>
			<?php if ($food_extra_fields) : ?>
				<?php do_action('submit_food_form_food_extra_fields_start'); ?>
				<h2 class="wpfm-form-title wpfm-heading-text"><?php _e('Extra Toppings', 'wp-food-manager'); ?></h2>
				<div class="wpfm-actions">
				    <button type="button" class="wpfm-add-button button button-primary" id="wpfm-add-new-option" data-row='<div class="wpfm-options-wrap wpfm-metabox postbox wpfm-options-box-%%repeated-option-index%%">
				        <input type="hidden" name="repeated_options[]" value="%%repeated-option-index%%" class="repeated-options">
				        <h3 class="">
				            <a href="javascript: void(0);" data-id="%%repeated-option-index%%" class="wpfm-delete-btn">Remove</a>
				            <div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false" data-row-count="%%repeated-option-index%%"></div>
				            <div class="tips wpfm-sort"></div>
				            <strong class="attribute_name"><?php _e("Option %%repeated-option-index%%","wp-food-manager");?></strong>
				            <span class="attribute_key"><input type="text" name="option_key_%%repeated-option-index%%" value="option_%%repeated-option-index%%" readonly>
				                </span>
				        </h3>
				        <div class="wpfm-metabox-content wpfm-options-box">
				            <div class="wpfm-content">
				                <?php foreach ($food_extra_fields as $key => $field) : ?>
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
		<?php //endif; ?>
		
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