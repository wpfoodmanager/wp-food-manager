<?php
/**
 * Event Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $food_manager;
?>
<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-food-form" class="wpfm-form-wrapper wpfm-main food-manager-form" enctype="multipart/form-data">
	<?php if ( apply_filters( 'submit_food_form_show_signin', true ) ) : ?>
		<?php get_food_manager_template( 'account-signin.php' ); ?>
	<?php endif; ?>
	<?php if ( wpfm_user_can_post_food() || wpfm_user_can_edit_food( $food_id )   ) : ?>
		<!-- Event Information Fields -->
    	<h2 class="wpfm-form-title wpfm-heading-text"><?php _e( 'Food Details', 'wp-food-manager' ); ?></h2>
    <?php
	if ( isset( $resume_edit ) && $resume_edit ) {
		printf( '<p class="wpfm-alert wpfm-alert-info"><strong>' . __( "You are editing an existing food. %s","wp-food-manager" ) . '</strong></p>', '<a href="?new=1&key=' . $resume_edit . '">' . __( 'Create A New Event','wp-food-manager' ) . '</a>' );
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