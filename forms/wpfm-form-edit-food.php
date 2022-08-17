<?php

include_once( 'wpfm-form-submit-food.php' );

/**
 * WP_Food_Manager_Form_Edit_Food class.
 */

class WP_Food_Manager_Form_Edit_Food extends WP_Food_Manager_Form_Submit_Food {

	public $form_name           = 'edit-food';

	/** @var WP_Food_Manager_Form_Edit_Food The single instance of the class */

	protected static $_instance = null;

	/**
	 * Main Instance
	 */

	public static function instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}
		
		return self::$_instance;
	}

	/**
	 * Constructor
	*/
	
	public function __construct() {

		$this->food_id = ! empty( $_REQUEST['food_id'] ) ? absint( $_REQUEST[ 'food_id' ] ) : 0;

		if  ( ! food_manager_user_can_edit_food( $this->food_id ) ) {

			$this->food_id = 0;
		}
	}

	/**
	 * output function.
	*/

	public function output( $atts = array() ) {

		$this->submit_handler();

		$this->submit();
	}

	/**
	 * Submit Step
	 */

	public function submit() {

		$food = get_post( $this->food_id );

		if ( empty( $this->food_id  ) || ( $food->post_status !== 'publish' && ! food_manager_user_can_edit_pending_submissions() ) ) {

			echo wpautop( __( 'Invalid listing', 'wp-food-manager' ) );

			return;
		}

		// Init fields
		//$this->init_fields(); We dont need to initialize with this function because of field edior
		// Now field editor function will return all the fields 
		//Get merged fields from db and default fields.
		$this->merge_with_custom_fields( 'frontend' );
		
		foreach ( $this->fields as $group_key => $group_fields ) {

			foreach ( $group_fields as $key => $field ) {

				if ( ! isset( $this->fields[ $group_key ][ $key ]['value'] ) ) {

					if ( 'food_title' === $key ) {

						$this->fields[ $group_key ][ $key ]['value'] = $food->post_title;

					} elseif ( 'food_description' === $key ) {

						$this->fields[ $group_key ][ $key ]['value'] = $food->post_content;

					}/* elseif ( 'organizer_logo' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = has_post_thumbnail( $food->ID ) ? get_post_thumbnail_id( $food->ID ) : get_post_meta( $food->ID, '_' . $key, true );
						
					} elseif ( 'event_start_date' === $key ) {
						$food_start_date = get_post_meta( $food->ID, '_' . $key, true );
        				//Convert date and time value into selected datepicker value
						$this->fields[ $group_key ][ $key ]['value'] = date($php_date_format ,strtotime($food_start_date));
					} elseif('event_end_date' === $key) {
						$food_end_date = get_post_meta( $food->ID, '_' . $key, true );
        				//Convert date and time value into selected datepicker value
						$this->fields[ $group_key ][ $key ]['value'] = date($php_date_format ,strtotime($food_end_date));
					}*/ elseif ( ! empty( $field['taxonomy'] ) ) {

						$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $food->ID, $field['taxonomy'], array( 'fields' => 'ids' ) );

					} else {

						$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $food->ID, '_' . $key, true );
					}
				}
				if(! empty( $field['type'] ) &&  $field['type'] == 'button'){
					if(isset($this->fields[ $group_key ][ $key ]['value']) && empty($this->fields[ $group_key ][ $key ]['value']))
					{
						$this->fields[ $group_key ][ $key ]['value'] = $field['placeholder'];
					}
				}
			}
		}

		$this->fields = apply_filters( 'submit_food_form_fields_get_user_data', $this->fields, $food );

		

		wp_enqueue_script( 'wp-food-manager-food-submission' );

		get_food_manager_template( 'food-submit.php', array(

			'form'               => $this->form_name,

			'food_id'             => $this->get_food_id(),

			'action'             => $this->get_action(),

			'food_fields'         => $this->get_fields( 'food' ),

			'step'               => $this->get_step(),

			'submit_button_text' => __( 'Save changes', 'wp-food-manager' )

			) );
	}

	/**
	 * Submit Step is posted
	 */

	public function submit_handler() {

		if ( empty( $_POST['submit_food'] ) ) {

			return;
		}

		try {

			// Get posted values

			$values = $this->get_posted_fields();

			// Validate required

			if ( is_wp_error( ( $return = $this->validate_fields( $values ) ) ) ) {

				throw new Exception( $return->get_error_message() );
			}
			
			// Update the food

			$this->save_food( $values['food']['food_title'], $values['food']['food_description'], '', $values, false );

			$this->update_food_data( $values );

			// Successful

			switch ( get_post_status( $this->food_id ) ) {

				case 'publish' :

					echo wp_kses_post('<div class="food-manager-message wpfm-alert wpfm-alert-success">' . __('Your changes have been saved.', 'wp-food-manager') . ' <a href="' . get_permalink($this->food_id) . '">' . __('View &rarr;', 'wp-food-manager') . '</a>' . '</div>');

				break;

				default :

					echo wp_kses_post('<div class="food-manager-message wpfm-alert wpfm-alert-success">' . __('Your changes have been saved.', 'wp-food-manager') . '</div>');

				break;
			}

		} catch ( Exception $e ) {

			echo wp_kses_post('<div class="food-manager-error wpfm-alert wpfm-alert-danger">' .  esc_html($e->getMessage()) . '</div>');

			return;
		}
	}
}
