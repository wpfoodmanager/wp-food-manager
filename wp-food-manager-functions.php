<?php


/**
 * True if an the user can edit a event.
 *
 * @return bool
 */

function wpfm_user_can_edit_food( $food_id ) {

	$can_edit = true;
	
	if ( ! is_user_logged_in() || ! $food_id ) {
		$can_edit = false;
	} else {
		$food      = get_post( $food_id );

		if ( ! $event || ( absint( $food->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $food_id ) ) ) {
			$can_edit = false;
		}
	}
	
	return apply_filters( 'wpfm_user_can_edit_food', $can_edit, $food_id );
}
