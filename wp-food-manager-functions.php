<?php

/**
 * True if an the user can post a event. If accounts are required, and reg is enabled, users can post (they signup at the same time).
 *
 * @return bool
 */

function wpfm_user_can_post_food() {

	$can_post = true;

	if ( ! is_user_logged_in() ) {

		if ( wpfm_user_requires_account() ) {

			$can_post = false;
		}
	}
	return apply_filters( 'wpfm_user_can_post_food', $can_post );
}
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
/**
 * True if an account is required to post a event.
 *
 * @return bool
 */

function wpfm_user_requires_account() {

	return apply_filters( 'wpfm_user_requires_account', get_option( 'wpfm_user_requires_account' ) == 1 ? true : false );
}

/**
 * True if users are allowed to edit submissions that are pending approval.
 *
 * @return bool
 */

function wpfm_user_can_edit_pending_submissions() {

	return apply_filters( 'wpfm_user_can_edit_pending_submissions', get_option( 'wpfm_user_can_edit_pending_submissions' ) == 1 ? true : false );
}

/**
 * Checks if the user can upload a file via the Ajax endpoint.
 *
 * @since 1.7
 * @return bool
 */
function wpfm_user_can_upload_file_via_ajax() {
	$can_upload = is_user_logged_in() && wpfm_user_can_post_food();
	/**
	 * Override ability of a user to upload a file via Ajax.
	 *
	 * @since 1.7
	 * @param bool $can_upload True if they can upload files from Ajax endpoint.
	 */
	return apply_filters( 'wpfm_user_can_upload_file_via_ajax', $can_upload );
}