<?php
/*
* This file use to cretae fields of wp food manager at admin side.
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WPFM_Writepanels {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  2.5
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  2.5
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}


	/**
	 * add_meta_boxes function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes() {
		global $wp_post_types;
		
		add_meta_box( 'food_manager_data', sprintf( __( '%s Data', 'wp-food-manager' ), $wp_post_types['food_manager']->labels->singular_name ), array( $this, 'food_manager_data' ), 'food_manager', 'normal', 'high' );
	}

	/**
	 * food_manager_data function.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	public function food_manager_data( $post ) {
		global $post, $thepostid;
		$thepostid = $post->ID;
		wp_enqueue_script('wpfm-admin');

		wp_nonce_field( 'save_meta_data', 'food_manager_nonce' );
		?>
		<div class="panel-wrap">
			<ul class="wpfm-tabs">
				<?php foreach ( $this->get_food_data_tabs() as $key => $tab ) : ?>
					<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( isset( $tab['class'] ) ? implode( ' ', (array) $tab['class'] ) : '' ); ?>">
						<a href="#<?php if(isset($tab['target'] )) echo $tab['target'];?>" class=""><span><?php echo esc_html( $tab['label'] ); ?></span></a>
					</li>
				<?php endforeach; ?>
				<?php do_action( 'wpfm_food_write_panel_tabs' ); ?>
			</ul>

			<?php
				//output tab
				self::output_tabs();
			?>
			<div class="clear"></div>
		</div>
		<style type="text/css">
			.wpfm_panel {
				  display: none;
				  padding: 6px 12px;
				  border: 1px solid #ccc;
				  border-top: none;
				  float: left;
    			  width: 80%;
				}
				ul.wpfm-tabs{
					margin: 0;
				    width: 20%;
				    float: left;
				    line-height: 1em;
				    padding: 0 0 10px;
				    position: relative;
				    background-color: #fafafa;
				    border-right: 1px solid #eee;
				    box-sizing: border-box;
				}
				ul.wpfm-tabs li a{
				    color: #555;
				    position: relative;
				    background-color: #eee;
				    margin: 0;
				    padding: 10px;
				    display: block;
				    box-shadow: none;
				    text-decoration: none;
				    line-height: 20px!important;
				    border-bottom: 1px solid #eee;
				}
		</style>
	<?php
		
	}


	/**
	 * Return array of tabs to show.
	 *
	 * @return array
	 */
	private function get_food_data_tabs() {
		$tabs = apply_filters(
			'wpfm_food_data_tabs',
			array(
				'general'        => array(
					'label'    => __( 'General', 'wp-food-manager' ),
					'target'   => 'general_food_data_content',
					'class'    => array( '' ),
					'priority' => 1,
				),
				// 'ingredient'      => array(
				// 	'label'    => __( 'Ingredients', 'wp-food-manager' ),
				// 	'target'   => 'ingredient_food_data_content',
				// 	'class'    => array( '' ),
				// 	'priority' => 2,
				// ),
				// 'neutritions'       => array(
				// 	'label'    => __( 'Neutritions', 'wp-food-manager' ),
				// 	'target'   => 'neutritions_food_data_content',
				// 	'class'    => array( '' ),
				// 	'priority' => 3,
				// ),
				
			)
		);

		// Sort tabs based on priority.
		uasort( $tabs, array( $this, 'sort_by_priority' ) );

		return $tabs;
	}


	public function output_tabs(){
		global $post, $thepostid;
		$thepostid = $post->ID;

		include 'templates/food-data-general.php';
		include 'templates/food-data-ingredient.php';
		
	}



	/**
	 * food_listing_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function food_listing_fields() {	    
		global $post;
		$current_user = wp_get_current_user();
		
		
		$GLOBALS['food_manager']->forms->get_form( 'submit-food', array() );
		$form_submit_food_instance = call_user_func( array( 'WPFM_Form_Submit_Food', 'instance' ) );
		$fields = $form_submit_food_instance->merge_with_custom_fields('backend');
		
		/** add _ (prefix) for all backend fields. 
		* 	Field editor will only return fields without _(prefix).
		**/
		foreach ($fields as $group_key => $group_fields) {
			foreach ($group_fields as $field_key => $field_value) {
				
				if( strpos($field_key, '_') !== 0 ) {
					$fields['_'.$field_key]  = $field_value;	
				}else{
					$fields[$field_key]  = $field_value;	
				}
			}
			unset($fields[$group_key]);
		}
		$fields = apply_filters( 'food_manager_food_data_fields', $fields );

		if(isset($fields['_food_title']))
			unset($fields['_food_title']);

		if(isset( $fields['_food_description'] )) 
			unset($fields['_food_description']);
		
		if ( $current_user->has_cap( 'manage_food_managers' ) ) {
			$fields['_featured'] = array(
				'label'       => __( 'Featured Listing', 'wp-food-manager' ),
				'type'        => 'checkbox',
				'description' => __( 'Featured listings will be sticky during searches, and can be styled differently.', 'wp-food-manager' ),
				'priority'    => 39
			);
		}

		if ( $current_user->has_cap( 'edit_others_food_managers' ) ) {
			$fields['_food_author'] = array(
				'label'    => __( 'Posted by', 'wp-food-manager' ),
				'type'     => 'author',
				'priority' => 41
			);
		}

		uasort( $fields, array( $this, 'sort_by_priority' ) );
		return $fields;
	}

	/**
	 * Sort array by priority value
	 */
	protected function sort_by_priority( $a, $b ) {
	    if ( ! isset( $a['priority'] ) || ! isset( $b['priority'] ) || $a['priority'] === $b['priority'] ) {
	        return 0;
	    }
	    return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
	}

	/**
	 * input_file function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_file( $key, $field ) {
		global $thepostid;
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( empty( $field['placeholder'] ) ) {
			$field['placeholder'] = 'http://';
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>	
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
			<?php
			if ( ! empty( $field['multiple'] ) ) {
				foreach ( (array) $field['value'] as $value ) {
					?><span class="file_url"><input type="text" name="<?php echo esc_attr( $name ); ?>[]" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $value ); ?>" /><button class="button button-small wp_food_manager_upload_file_button" data-uploader_button_text="<?php _e( 'Use file', 'wp-food-manager' ); ?>"><?php _e( 'Upload', 'wp-food-manager' ); ?></button></span><?php
				}
			} else {
				if(isset($field['value']) && is_array($field['value']) )
					$field['value'] = array_shift($field['value']);
				?><span class="file_url"><input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" /><button class="button button-small wp_food_manager_upload_file_button" data-uploader_button_text="<?php _e( 'Use file', 'wp-food-manager' ); ?>"><?php _e( 'Upload', 'wp-food-manager' ); ?></button></span><?php
			}
			if ( ! empty( $field['multiple'] ) ) {
				?><button class="button button-small wp_food_manager_add_another_file_button" data-field_name="<?php echo esc_attr( $key ); ?>" data-field_placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" data-uploader_button_text="<?php _e( 'Use file', 'wp-food-manager' ); ?>" data-uploader_button="<?php _e( 'Upload', 'wp-food-manager' ); ?>"><?php _e( 'Add file', 'wp-food-manager' ); ?></button><?php
			}
			?>
		</p>
		<?php
	}

	/**
	 * input_text function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_text( $key, $field ) {
		global $thepostid;
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>	
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
			<input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
		</p>
		<?php
	}
	
	/**
	 * input_wp_editor function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 * @since 2.8
	 */
	public static function input_wp_editor( $key, $field ) {
		global $thepostid;
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
			}?>
			<p class="form-field">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
			
	
			<?php
			wp_editor( $field['value'], $name, array("media_buttons" => false) );
			?>
			</p>
			<?php
		}
	
	

	/**
	 * input_text function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_textarea( $key, $field ) {
		global $thepostid;
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
			<textarea name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_html( $field['value'] ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_select( $key, $field ) {	   
		global $thepostid;
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>

		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
			<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" class="input-select <?php echo esc_attr( isset( $field['class'] ) ? $field['class'] : $key ); ?>">
				<?php foreach ( $field['options'] as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php if ( isset( $field['value'] ) ) selected( $field['value'], $key ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_multiselect( $key, $field ) {
		global $thepostid;
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
			<select multiple="multiple" name="<?php echo esc_attr( $name ); ?>[]" id="<?php echo esc_attr( $key ); ?>" class="input-select <?php echo esc_attr( isset( $field['class'] ) ? $field['class'] : $key ); ?>">
				<?php foreach ( $field['options'] as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field['value'] ) && is_array( $field['value'] ) ) selected( in_array( $key, $field['value'] ), true ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * input_checkbox function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_checkbox( $key, $field ) {
		global $thepostid;
		if ( empty( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>
		<p class="form-field form-field-checkbox">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?></label>
			<input type="checkbox" class="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $field['value'], 1 ); ?> />
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php
	}

	
		
		
		
		/**
		 * input_number function.
		 *
		 * @param mixed $key
		 * @param mixed $field
		 */
		public static function input_number( $key, $field ) {
			global $thepostid;
			if ( ! isset( $field['value'] ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}
			if ( ! empty( $field['name'] ) ) {
				$name = $field['name'];
			} else {
				$name = $key;
			}
			?>
				<p class="form-field">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
					<input type="number" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
				</p>
				<?php
			}
			/**
			 * input_button function.
			 *
			 * @param mixed $key
			 * @param mixed $field
			 */
			public static function input_button( $key, $field ) {
				global $thepostid;
				if ( ! isset( $field['value'] ) ) {
					$field['value'] = $field['placeholder'];
				}
			
				if ( ! empty( $field['name'] ) ) {
					$name = $field['name'];
				} else {
					$name = $key;
				}
				?>
						<p class="form-field">
							<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
							<input type="button" class="button button-small" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
						</p>
						<?php
		}	
		
	/**
	 * Box to choose who posted the food
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */	 
	public static function input_author( $key, $field ) {
		global $thepostid, $post;
		if ( ! $post || $thepostid !== $post->ID ) {
			$the_post  = get_post( $thepostid );
			$author_id = $the_post->post_author;
		} else {
			$author_id = $post->post_author;
		}
		$posted_by      = get_user_by( 'id', $author_id );
		$field['value'] = ! isset( $field['value'] ) ? get_post_meta( $thepostid, $key, true ) : $field['value'];
		$name           = ! empty( $field['name'] ) ? $field['name'] : $key;
		?>
		<p class="form-field form-field-author">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<span class="current-author">
				<?php
					if ( $posted_by ) {
						echo '<a href="' . admin_url( 'user-edit.php?user_id=' . absint( $author_id ) ) . '">#' . absint( $author_id ) . ' &ndash; ' . $posted_by->user_login . '</a>';
					} else {
						 _e( 'Guest User', 'wp-food-manager' );
					}
				?> <a href="#" class="change-author button button-small"><?php _e( 'Change', 'wp-food-manager' ); ?></a>
			</span>
			<span class="hidden change-author">
				<input type="number" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" step="1" value="<?php echo esc_attr( $author_id ); ?>" style="width: 4em;" />
				<span class="description"><?php _e( 'Enter the ID of the user, or leave blank if submitted by a guest.', 'wp-food-manager' ) ?></span>
			</span>
		</p>
		<?php
	}

	/**
	 * input_radio function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_radio( $key, $field ) {
		global $thepostid;
		if ( empty( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>
		<p class="form-field form-field-checkbox">
			<label><?php echo esc_html( $field['label'] ) ; ?></label>
			<?php foreach ( $field['options'] as $option_key => $value ) : ?>
				<label><input type="radio" class="radio" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $field['value'], $option_key ); ?> /> <?php echo esc_html( $value ); ?></label>
			<?php endforeach; ?>
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php
	}

}
WPFM_Writepanels::instance();
