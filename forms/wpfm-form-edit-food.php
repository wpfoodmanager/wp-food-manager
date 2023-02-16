<?php

include_once( 'wpfm-form-submit-food.php' );

/**
 * WP_Food_Manager_Form_Edit_Food class.
 */

class WPFM_Form_Edit_Food extends WPFM_Form_Submit_Food {

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
		global $wpdb;
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

		$parent_row_fields_count = !empty(get_post_meta($food->ID, 'wpfm_repeated_options', true)) ? get_post_meta($food->ID, 'wpfm_repeated_options', true) : array();
		$extra_fields_options = get_post_meta($food->ID, '_wpfm_extra_options', true) ? get_post_meta($food->ID, '_wpfm_extra_options', true) : '';

		foreach ( $this->fields as $group_key => $group_fields ) {

			foreach ( $group_fields as $key => $field ) {

				if($group_key == 'extra_options'){
					foreach ($parent_row_fields_count as $row_key => $row_value) {

						$key_row_val = '';
						if($key !== 'option_name'){
							$key_row_val = '_'.$key.'_'.$row_value;
						} else {
							$key_row_val = $key.'_'.$row_value;
						}

						if(count($parent_row_fields_count) == "1"){
							$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $food->ID, $key_row_val, true );
							if($key == 'option_options'){
								if(!empty($extra_fields_options)){
									foreach($extra_fields_options as $ext_key => $extra_fields_option){
										$this->fields[ $group_key ][ $key ]['value'] = $extra_fields_option['option_options'];
									}
								}
							}
						} else {
							if($key !== 'option_options'){
								$this->fields[ $group_key ][ $key ]['value'][] = get_post_meta( $food->ID, $key_row_val, true );
								array_unshift($this->fields[ $group_key ][ $key ]['value'], "");
								unset($this->fields[ $group_key ][ $key ]['value'][0]);
							}
							if($key == 'option_options'){
								if(!empty($extra_fields_options)){
									foreach($extra_fields_options as $ext_key => $extra_fields_option){
										$this->fields[ $group_key ][ $key ]['value'][$ext_key] = $extra_fields_option['option_options'];
										array_unshift($this->fields[ $group_key ][ $key ]['value'], "");
										unset($this->fields[ $group_key ][ $key ]['value'][0]);
									}
								}
							}
						}

					}

				} else {

					if ( ! isset( $this->fields[ $group_key ][ $key ]['value'] ) ) {
						if ( 'food_title' === $key ) {

							$this->fields[ $group_key ][ $key ]['value'] = $food->post_title;

						} elseif ( 'food_description' === $key ) {

							$this->fields[ $group_key ][ $key ]['value'] = $food->post_content;

						}
						/* elseif ( 'organizer_logo' === $key ) {
							$this->fields[ $group_key ][ $key ]['value'] = has_post_thumbnail( $food->ID ) ? get_post_thumbnail_id( $food->ID ) : get_post_meta( $food->ID, '_' . $key, true );
							
						} elseif ( 'event_start_date' === $key ) {
							$food_start_date = get_post_meta( $food->ID, '_' . $key, true );
	        				//Convert date and time value into selected datepicker value
							$this->fields[ $group_key ][ $key ]['value'] = date($php_date_format ,strtotime($food_start_date));
						} elseif('event_end_date' === $key) {
							$food_end_date = get_post_meta( $food->ID, '_' . $key, true );
	        				//Convert date and time value into selected datepicker value
							$this->fields[ $group_key ][ $key ]['value'] = date($php_date_format ,strtotime($food_end_date));
						}*/
						elseif ( ! empty( $field['taxonomy'] ) ) {
							
							$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $food->ID, $field['taxonomy'], array( 'fields' => 'ids' ) );						

						} else {

							$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $food->ID, '_' . $key, true );
						}
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
		
		$this->fields = apply_filters( 'add_food_fields_get_user_data', $this->fields, $food );

		wp_enqueue_script( 'wp-food-manager-food-submission' );

		get_food_manager_template( 'food-submit.php', array(

			'form'               => $this->form_name,

			'food_id'             => $this->get_food_id(),

			'action'             => $this->get_action(),

			'food_fields'         => $this->get_fields( 'food' ),

			'food_extra_fields'     => $this->get_fields( 'extra_options' ),

			'step'               => $this->get_step(),

			'submit_button_text' => __( 'Save changes', 'wp-food-manager' )

			) );

		// Check for WPFM Online Order & Woocommerce Active or not
		if(isset($_GET['action']) == 'edit'){
			if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) && in_array('wpfm-online-order/wpfm-online-order.php', apply_filters('active_plugins', get_option('active_plugins')))){

				$food_title_arr = $this->get_fields( 'food' );
				$food_title = $food_title_arr['food_title']['value'];

				$food_description_arr = $this->get_fields( 'food' );
				$food_description = $food_title_arr['food_description']['value'];

				$food_price = get_post_meta($this->food_id,'_food_price', true);
				$food_sale_price = get_post_meta($this->food_id,'_food_sale_price', true);
				$food_stock_status = get_post_meta($this->food_id,'_food_stock_status', true);
				$food_stock_array = explode("fm_", $food_stock_status);
				$food_ingre = get_post_meta($this->food_id,'_enable_food_ingre', true);
				$food_nutri = get_post_meta($this->food_id,'_enable_food_nutri', true);
				$food_categories = wp_get_post_terms( $this->food_id, 'food_manager_category' );
				$food_tags = wp_get_post_terms( $this->food_id, 'food_manager_tag' );
				$food_types = wp_get_post_terms( $this->food_id, 'food_manager_type' );
				//$food_types = get_post_meta( $this->food_id, 'food_manager_type', true);
				$food_ingredient = get_post_meta($this->food_id,'_ingredient', true);
				$food_nutrition = get_post_meta($this->food_id,'_nutrition', true);
				$food_post_title = isset($_POST['food_title']) ? $_POST['food_title'] : $food_title;
				$food_post_content = isset($_POST['food_description']) ? $_POST['food_description'] : $food_description;
				$food_new_price = '';

				if($food_ingre == 1){
					$food_ingre = 'yes';
				} else {
					$food_ingre = '';
				}

				if($food_nutri == 1){
					$food_nutri = 'yes';
				} else {
					$food_nutri = '';
				}

				$food_categories_arr = array();
				foreach ($food_categories as $food_cat_key => $food_cat_value) {
					$term_cat = get_term_by('slug', $food_cat_value->slug, 'product_cat');
					$food_categories_arr[] = $term_cat->term_id;
				}

				$food_tag_arr_val = array();
				foreach($food_tags as $food_tag){
					$food_tag_arr_val[] = $food_tag->slug;
				}

				$food_tags_arr = array();
				foreach ($food_tags as $food_tag_key => $food_tag_value) {
					$term_tag = get_term_by('slug', $food_tag_value->slug, 'product_tag');
					$food_tags_arr[] = $term_tag->term_id;
				}

				//wpfm_online_order_food_items_tag_sync();

				/*$food_types_arr = array();
				foreach ($food_types as $food_type_key => $food_type_value) {
					$term_type = get_term_by('slug', $food_type_value->slug, 'product_types');
					$food_types_arr[] = $term_type->term_id;
				}*/

		    	$product_obj = get_page_by_path( $food->post_name, OBJECT, 'product' );
		    	$product = wc_get_product($product_obj->ID);			

		    	if(empty($food_sale_price)){
					$food_new_price = $food_price;
			    } else {
			    	$food_new_price = $food_sale_price;
			    }

				update_post_meta( $product_obj->ID, '_stock_status', $food_stock_array[1]);
			    update_post_meta( $product_obj->ID, '_regular_price', $food_price );
			    update_post_meta( $product_obj->ID, '_sale_price', $food_sale_price );
			    update_post_meta( $product_obj->ID, '_price', $food_new_price );
			    update_post_meta( $product_obj->ID, '_ingredient', $food_ingredient );
				update_post_meta( $product_obj->ID, '_nutrition', $food_nutrition );
				update_post_meta( $product_obj->ID, '_enable_food_ingre', $food_ingre );
				update_post_meta( $product_obj->ID, '_enable_food_nutri', $food_nutri );
				update_post_meta( $product_obj->ID, '_thumbnail_id', get_post_thumbnail_id($this->food_id));
				update_post_meta( $product_obj->ID, 'food_manager_type', $food_types[0]->slug);
				
				$wpdb->update('wp_posts', array('post_content'=>$food_post_content, 'post_title'=>$food_post_title), array('ID' => $product_obj->ID, 'post_type' => 'product'));
				
				wp_set_object_terms( $product_obj->ID, $food_tags_arr, 'product_tag' );
		    	wp_set_object_terms( $product_obj->ID, $food_categories_arr, 'product_cat' );


		    	// Create variation on update food to the products
		    	$product2 = new WC_Product_Variable($product_obj->ID);
				$prod_atts = $product2->get_attributes();
				$prods_vars = $product2->get_children();

				$option_value_count = isset($_POST['option_value_count']) ? $_POST['option_value_count'] : '';
				$repeated_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
				$ext_multi_options = '';
				if(!empty($repeated_options) && empty($option_value_count)){
					$ext_multi_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
				}
				if(!empty($repeated_options) && !empty($option_value_count)){
					$ext_multi_options = isset($_POST['option_value_count']) ? $_POST['option_value_count'] : '';
				}


				// Do not delete
				/*$prod_vars_datas = array();
				if(($option_value_count && is_array($option_value_count)) && ($repeated_options && is_array($repeated_options))){
					foreach ( $ext_multi_options as $option_count => $option_value ) {
						foreach($option_value as $option_value_count){
							$opt_attr_name = isset($_POST['option_name_'.$option_count]) ? $_POST['option_name_'.$option_count] : '';

							$opt_name = isset($_POST[$option_count.'_option_value_name_'.$option_value_count]) ? $_POST[$option_count.'_option_value_name_'.$option_value_count] : '';
							$opt_dafault = isset($_POST[$option_count.'_option_value_default_'.$option_value_count]) ? $_POST[$option_count.'_option_value_default_'.$option_value_count] : '';
							$opt_price = isset($_POST[$option_count.'_option_value_price_'.$option_value_count]) ? $_POST[$option_count.'_option_value_price_'.$option_value_count] : '';
							$opt_price_type = isset($_POST[$option_count.'_option_value_price_type_'.$option_value_count]) ? $_POST[$option_count.'_option_value_price_type_'.$option_value_count] : '';
							$prod_vars_datas[$opt_attr_name][] = array(
								'option_value_name' => $opt_name,
								'option_value_default' => $opt_dafault,
								'option_value_price' => $opt_price,
								'option_value_price_type' => $opt_price_type,
							);
							
							
						}
					}
				}*/
				$option_prices = [];
				if(($option_value_count && is_array($option_value_count)) && ($repeated_options && is_array($repeated_options))){
					
					foreach ( $ext_multi_options as $option_count => $option_value ) {
						foreach($option_value as $option_value_count){
							$opt_name = isset($_POST[$option_count.'_option_value_name_'.$option_value_count]) ? $_POST[$option_count.'_option_value_name_'.$option_value_count] : '';
							$opt_price = isset($_POST[$option_count.'_option_value_price_'.$option_value_count]) ? $_POST[$option_count.'_option_value_price_'.$option_value_count] : '';

							$option_prices[] = $opt_price;							
						}
					}
				}
				
				$combine_arr = '';
				if(!empty($prods_vars) && !empty($option_prices)){
					$combine_arr = array_combine($prods_vars, $option_prices);
				}
				if(!empty($combine_arr) && is_array($combine_arr)){
					foreach($combine_arr as $v_id => $new_price){
						update_post_meta($v_id, '_price', $new_price);
						update_post_meta($v_id, '_regular_price',$new_price);
					}
				}
		    }
		}
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
