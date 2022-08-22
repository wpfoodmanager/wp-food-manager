<?php
/*
* This file use for settings at admin site for wp food manager plugin.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WPFM_Settings class.
 */

class WPFM_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */

	public function __construct() {

		$this->settings_group = 'food_manager';

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * init_settings function.
	 *
	 * @access protected
	 * @return void
	 */

	protected function init_settings() {

		// Prepare roles option

		$roles         = get_editable_roles();

		$account_roles = array();
		foreach ( $roles as $key => $role ) {

			if ( $key == 'administrator' ) {

				continue;
			}

			$account_roles[ $key ] = $role['name'];
		}

		$this->settings = apply_filters( 'food_manager_settings',

			array(
					'general_settings' => array(
							
							__( 'General', 'wp-food-manager' ),
							
							array(

								array(
											'name'       => 'food_manager_enable_categories',
											'std'        => '1',
											'label'      => __( 'Enable Food categories', 'wp-food-manager' ),
											'cb_label'   => __( 'Display categories on food.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),
								array(
											'name'       => 'food_manager_enable_food_types',
											'std'        => '1',
											'label'      => __( 'Enable Food types', 'wp-food-manager' ),
											'cb_label'   => __( 'Display types on food.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),
								array(
											'name'       => 'food_manager_enable_food_tags',
											'std'        => '1',
											'label'      => __( 'Enable Food tags', 'wp-food-manager' ),
											'cb_label'   => __( 'Display tags on food.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),

								array(
											'name'       => 'food_manager_enable_food_ingredients',
											'std'        => '1',
											'label'      => __( 'Enable Food ingredients', 'wp-food-manager' ),
											'cb_label'   => __( 'Display ingredients on food.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),

								array(
											'name'       => 'food_manager_enable_food_nutritions',
											'std'        => '1',
											'label'      => __( 'Enable Food nutritions', 'wp-food-manager' ),
											'cb_label'   => __( 'Display nutritions on food.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),
								array(
											'name'       => 'food_manager_enable_food_units',
											'std'        => '1',
											'label'      => __( 'Enable Food units', 'wp-food-manager' ),
											'cb_label'   => __( 'Display units on food.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),
								
								

									array(
											'name'       => 'food_manager_delete_data_on_uninstall',
											'std'        => '0',
											'label'      => __( 'Delete Data On Uninstall', 'wp-food-manager' ),
											'cb_label'   => __( 'Delete WP Food Manager data when the plugin is deleted. Once removed, this data cannot be restored.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),
							)
					),

				/*'food_submission' => array(

					__( 'Food Submission', 'wp-food-manager' ),

					array(

						array(

							'name'       => 'food_manager_submission_requires_approval',

							'std'        => '1',

							'label'      => __( 'Moderate New Listings', 'wp-food-manager' ),

							'cb_label'   => __( 'New listing submissions require admin approval', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, new submissions will be inactive, pending admin approval.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),

						array(

							'name'       => 'food_manager_user_can_edit_pending_submissions',

							'std'        => '0',

							'label'      => __( 'Allow Pending Edits', 'wp-food-manager' ),

							'cb_label'   => __( 'Submissions awaiting approval can be edited', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, submissions awaiting admin approval can be edited by the user.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),
						array(		
								'name'       => 'food_manager_user_can_add_multiple_banner',

								'std'        => '1',
	
								'label'      => __( 'Allow Multiple Banners', 'wp-food-manager' ),
	
								'cb_label'   => __( 'User can submit multiple banner', 'wp-food-manager' ),

								'desc'       => __( 'If enabled, Multiple banner can add at frontend by user and backend side by admin.', 'wp-food-manager' ),
	
								'type'       => 'checkbox',
		
								'attributes' => array()
						),
					)
				),*/

				/*'food_pages' => array(

					__( 'Pages', 'wp-food-manager' ),

					array(

						array(

							'name' 		=> 'food_manager_submit_food_form_page_id',

							'std' 		=> '',

							'label' 	=> __( 'Submit Event Form Page', 'wp-food-manager' ),

							'desc'		=> __( 'Select the page where you have placed the [submit_food_form] shortcode. This lets the plugin know where the form is located.', 'wp-food-manager' ),

							'type'      => 'page'
						),

						array(

							'name' 		=> 'food_manager_food_dashboard_page_id',

							'std' 		=> '',

							'label' 	=> __( 'Event Dashboard Page', 'wp-food-manager' ),

							'desc'		=> __( 'Select the page where you have placed the [food_dashboard] shortcode. This lets the plugin know where the dashboard is located.', 'wp-food-manager' ),

							'type'      => 'page'
						),

						array(

							'name' 		=> 'food_manager_foods_page_id',

							'std' 		=> '',

							'label' 	=> __( 'Event Listings Page', 'wp-food-manager' ),

							'desc'		=> __( 'Select the page where you have placed the [foods] shortcode. This lets the plugin know where the food listings page is located.', 'wp-food-manager' ),

							'type'      => 'page'
						),
					    array(
					        
					        'name' 		=> 'food_manager_login_page_url',
					        
					    	'std' 		=> wp_login_url(),
					        
					        'label' 	=> __( 'Login Page URL', 'wp-food-manager' ),
					        
					        'desc'		=> __( 'Enter the Login page URL.', 'wp-food-manager' ),
					        
					        'type'      => 'text'
					    ),
					)
				),*/
			
				
			)
		);
	}

	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */

	public function register_settings() {

		$this->init_settings();

		foreach ( $this->settings as $section ) {

			foreach ( $section[1] as $option ) {

				if ( isset( $option['std'] ) )

					add_option( $option['name'], $option['std'] );

				register_setting( $this->settings_group, $option['name'] );
			}
		}
	}

	/**
	 * output function.
	 *
	 * @access public
	 * @return void
	 */

	public function output() {

		$this->init_settings();
		wp_enqueue_script( 'wpfm-admin-settings');
		?>
		
	
       
		<div class="wrap food-manager-settings-wrap">	

			<form method="post" name="food-manager-settings-form" action="options.php">	

				<?php settings_fields( $this->settings_group ); ?>

			    <h2 class="nav-tab-wrapper">

			    	<?php

			    		foreach ( $this->settings as $key => $section ) {

			    			echo '<a href="#settings-' . sanitize_title( $key ) . '" class="nav-tab">' . esc_html( $section[0] ) . '</a>';
			    		}
			    	?>
			    </h2>
			    
			 <div class="admin-setting-left">
			     	
			     <div class="white-background">
			     		
				<?php

					if ( ! empty( $_GET['settings-updated'] ) ) {

						flush_rewrite_rules();

						echo '<div class="updated fade food-manager-updated"><p>' . __( 'Settings successfully saved', 'wp-food-manager' ) . '</p></div>';
					}
					
					foreach ( $this->settings as $key => $section ) {

						echo '<div id="settings-' . sanitize_title( $key ) . '" class="settings_panel">';

						echo '<table class="form-table">';

						foreach ( $section[1] as $option ) {

							$placeholder    = ( ! empty( $option['placeholder'] ) ) ? 'placeholder="' . $option['placeholder'] . '"' : '';

							$class          = ! empty( $option['class'] ) ? $option['class'] : '';

							$value          = get_option( $option['name'] );

							$option['type'] = ! empty( $option['type'] ) ? $option['type'] : '';

							$attributes     = array();

							if ( ! empty( $option['attributes'] ) && is_array( $option['attributes'] ) )

								foreach ( $option['attributes'] as $attribute_name => $attribute_value )

									$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';

							echo '<tr valign="top" class="' . $class . '"><th scope="row"><label for="setting-' . $option['name'] . '">' . $option['label'] . '</a></th><td>';

							switch ( $option['type'] ) {

								case "checkbox" :

									?><label><input id="setting-<?php echo $option['name']; ?>" name="<?php echo $option['name']; ?>" type="checkbox" value="1" <?php echo implode( ' ', $attributes ); ?> <?php checked( '1', $value ); ?> /> <?php echo $option['cb_label']; ?></label><?php

									if ( $option['desc'] )

										echo ' <p class="description">' . $option['desc'] . '</p>';

								break;

								case "textarea" :

									?><textarea id="setting-<?php echo $option['name']; ?>" class="large-text" cols="50" rows="3" name="<?php echo $option['name']; ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea><?php

									if ( $option['desc'] )

										echo ' <p class="description">' . $option['desc'] . '</p>';

								break;

								case "select" :

									?><select id="setting-<?php echo $option['name']; ?>" class="regular-text" name="<?php echo $option['name']; ?>" <?php echo implode( ' ', $attributes ); ?>><?php

										foreach( $option['options'] as $key => $name )

											echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $name ) . '</option>';

									?></select><?php

									if ( $option['desc'] ) {

										echo ' <p class="description">' . $option['desc'] . '</p>';

									}

								break;
								case "radio":
									?><fieldset>
										<legend class="screen-reader-text">
											<span><?php echo esc_html( $option['label'] ); ?></span>
										</legend><?php

									if ( $option['desc'] ) {
										echo '<p class="description">' . $option['desc'] . '</p>';
									}

									foreach( $option['options'] as $key => $name )
										echo '<label><input name="' . esc_attr( $option['name'] ) . '" type="radio" value="' . esc_attr( $key ) . '" ' . checked( $value, $key, false ) . ' />' . esc_html( $name ) . '</label><br>';

									?></fieldset><?php

								break;

								case "page" :

									$args = array(

										'name'             => $option['name'],

										'id'               => $option['name'],

										'sort_column'      => 'menu_order',

										'sort_order'       => 'ASC',

										'show_option_none' => __( '--no page--', 'wp-food-manager' ),

										'echo'             => false,

										'selected'         => absint( $value )

									);

									echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'wp-food-manager' ) .  "' id=", wp_dropdown_pages( $args ) );

									if ( $option['desc'] ) {

										echo ' <p class="description">' . $option['desc'] . '</p>';

									}
									
								break;

								case "password" :

									?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="password" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

									if ( $option['desc'] ) {

										echo ' <p class="description">' . $option['desc'] . '</p>';
									}

								break;

								case "" :

								case "input" :

								case "text" :

									?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

									if ( $option['desc'] ) {

										echo ' <p class="description">' . $option['desc'] . '</p>';
								}

								break;		
								
								case "multi-select-checkbox":
								    $this->create_multi_select_checkbox($option);
									break;

								default :

									do_action( 'wp_food_manager_admin_field_' . $option['type'], $option, $attributes, $value, $placeholder );

								break;
							}
							echo '</td></tr>';
						}
						echo '</table></div>';
					}
				?>
				 </div>   <!-- .white-background- -->
				<p class="submit">
					<input type="submit" class="button-primary" id="save-changes" value="<?php _e( 'Save Changes', 'wp-food-manager' ); ?>" />
				</p>
			 </div>  <!-- .admin-setting-left -->						
		    </form>
            </div>
	  	

		<?php  wp_enqueue_script( 'wp-food-manager-admin-settings');
	}
	
	/**
	 * Creates Multiselect checkbox.
	 * This function generate multiselect 
	 * @param $value
	 * @return void
	 */ 
	public function create_multi_select_checkbox($value) 
	{ 
		
		echo '<ul class="mnt-checklist" id="'.$value['name'].'" >'."\n";
		foreach ($value['options'] as $option_value => $option_list) {
			$checked = " ";
			if (get_option($value['name'] ) ) {
			
                                 $all_country = get_option( $value['name'] );
                                 $start_string = strpos($option_list['name'],'[');
                                 $country_code = substr($option_list['name'] ,$start_string + 1 ,  2 );
                                 $coutry_exist = array_key_exists($country_code , $all_country);
                              if( $coutry_exist ){
                                     $checked = " checked='checked' ";       
                                     
                              }
			}
			echo "<li>\n";

			echo '<input id="setting-'.$option_list['name'].'" name="'.$option_list['name'].'" type="checkbox" '.$checked.'/>'.$option_list['cb_label']."\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
    }	
}
