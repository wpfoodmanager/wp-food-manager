<?php
/**
 * Event listing preview when submitting food listing.
 *
 * This template can be overridden by copying it to yourtheme/wp-event-manager/event-preview.php.
 *
 * @see         https://www.wp-eventmanager.com/template-files-override/
 * @author      WP Event Manager
 * @category    template
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<form method="post" id="food_preview" action="<?php echo esc_url( $form->get_action() ); ?>">
	<div class="food_listing_preview_title">
		<input type="submit" name="edit_food" class="button food-manager-button-edit-listing" value="<?php esc_attr_e( '← Edit listing', 'wp-food-manager' ); ?>" />
		<input type="submit" name="continue" id="food_preview_submit_button" class="button food-manager-button-submit-listing" value="<?php echo esc_attr( apply_filters( 'submit_food_step_preview_submit_text', __( 'Submit Listing →', 'wp-food-manager' ) ) ); ?>" />		
		<h2><?php esc_html_e( 'Preview', 'wp-food-manager' ); ?></h2>
	</div>
	<div class="food_listing_preview single_food_listing">
		<?php get_food_manager_template_part( 'content-single', 'food_manager' ); ?>
		<input type="hidden" name="food_id" value="<?php echo esc_attr( $form->get_food_id() ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $form->get_step() ); ?>" />
		<input type="hidden" name="food_manager_form" value="<?php echo esc_attr( $form->get_form_name() ); ?>" />
	</div>
</form>
