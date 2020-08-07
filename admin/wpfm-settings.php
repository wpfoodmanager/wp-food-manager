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
											'name'       => 'enable_food_nuetritions',
											'std'        => '1',
											'label'      => __( 'Enable Food nuetritions', 'wp-food-manager' ),
											'cb_label'   => __( 'Display nuetritions on food.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),
								// array(
								// 			'name'       => 'enable_food_venue',
								// 			'std'        => '1',
								// 			'label'      => __( 'Enable venue', 'wp-food-manager' ),
								// 			'cb_label'   => __( 'Display venue on foods.', 'wp-food-manager' ),
								// 			'desc'       => '',
								// 			'type'       => 'checkbox',
								// 			'attributes' => array(),
								// 	),

									array(
											'name'       => 'food_manager_delete_data_on_uninstall',
											'std'        => '0',
											'label'      => __( 'Delete Data On Uninstall', 'wp-food-manager' ),
											'cb_label'   => __( 'Delete WP Event Manager data when the plugin is deleted. Once removed, this data cannot be restored.', 'wp-food-manager' ),
											'desc'       => '',
											'type'       => 'checkbox',
											'attributes' => array(),
									),
									array(
											'name'       => 'food_manager_google_maps_api_key',
											'std'        => '',
											'label'      => __( 'Google API Key', 'wp-food-manager' ),
											'desc'       => sprintf( __( 'If you are going to deal with google map or location then you need Google API key to retrieve location information for food listings. Also this Google API key require when you will use <a href="https://www.wp-foodmanager.com/product/wp-food-manager-google-maps/" target="__blank">Google Map Addon</a>.  Acquire an API key from the <a href="%s" target="__blank">Google Maps API developer site</a>.', 'wp-food-manager' ), 'https://developers.google.com/maps/documentation/geocoding/get-api-key' ),
											'attributes' => array()
									)
							)
					),
				'food_listings' => array(

					__( 'Food Listings', 'wp-food-manager' ),

					array(

						array(

							'name'        => 'food_manager_per_page',

							'std'         => '10',

							'placeholder' => '',

							'label'       => __( 'Listings Per Page', 'wp-food-manager' ),

							'desc'        => __( 'How many listings should be shown per page by default?', 'wp-food-manager' ),

							'attributes'  => array()
						),

						array(

							'name'       => 'food_manager_hide_cancelled_foods',

							'std'        => '0',

							'label'      => __( 'Cancelled Events', 'wp-food-manager' ),

							'cb_label'   => __( 'Hide cancelled foods', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, cancelled foods will be hidden from archives.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),
						array(
								'name'       => 'food_manager_hide_expired',
								
								'std'        => get_option( 'food_manager_hide_expired_content' ) ? '1' : '0', // back compat
								'label'      => __( 'Hide Expired Listings', 'wp-food-manager' ),
								
								'cb_label'   => __( 'Hide expired listings in food archive/search', 'wp-food-manager' ),
								
								'desc'       => __( 'If enabled, expired food listing is not searchable.', 'wp-food-manager' ),
								
								'type'       => 'checkbox',
								
								'attributes' => array()
						),

						array(

							'name'       => 'food_manager_hide_expired_content',

							'std'        => '1',

							'label'      => __( 'Hide Expired Listings Content', 'wp-food-manager' ),

							'cb_label'   => __( 'Hide expired listing content in single food listing (singular)', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, the content within expired listings will be hidden. Otherwise, expired listings will be displayed as normal (without the food registration area).', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),

						array(

							'name'       => 'food_manager_enable_default_category_multiselect',

							'std'        => '0',

							'label'      => __( 'Multi-select Categories', 'wp-food-manager' ),

							'cb_label'   => __( 'Enable category multiselect by default', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, the category select box will default to a multi select on the [foods] shortcode.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),

						array(

							'name'       => 'food_manager_enable_default_food_type_multiselect',

							'std'        => '0',

							'label'      => __( 'Multi-select Event Types', 'wp-food-manager' ),

							'cb_label'   => __( 'Enable food type multiselect by default', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, the food type select box will default to a multi select on the [foods] shortcode.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),

						array(

							'name'       => 'food_manager_category_filter_type',

							'std'        => 'any',

							'label'      => __( 'Category Filter', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, the category select box will default to a multi select on the [foods] shortcode.', 'wp-food-manager' ),

							'type'       => 'radio',

							'options' => array(

								'any'  => __( 'Events will be shown if within ANY selected category', 'wp-food-manager' ),

								'all' => __( 'Events will be shown if within ALL selected categories', 'wp-food-manager' ),
							)
						),

						array(

							'name'       => 'food_manager_food_type_filter_type',

							'std'        => 'any',

							'label'      => __( 'Event Type Filter', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, the food type select box will default to a multi select on the [foods] shortcode.', 'wp-food-manager' ),

							'type'       => 'select',

							'options' => array(

								'any'  => __( 'Events will be shown if within ANY selected food type', 'wp-food-manager' ),

								'all' => __( 'Events will be shown if within ALL selected food types', 'wp-food-manager' ),
							)
						)			
					),
				),

				'food_submission' => array(

					__( 'Event Submission', 'wp-food-manager' ),

					array(

						array(

							'name'       => 'food_manager_user_requires_account',

							'std'        => '1',

							'label'      => __( 'Account Required', 'wp-food-manager' ),

							'cb_label'   => __( 'Submitting listings requires an account', 'wp-food-manager' ),

							'desc'       => __( 'If disabled, non-logged in users will be able to submit listings without creating an account.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),

						array(

							'name'       => 'food_manager_enable_registration',

							'std'        => '1',

							'label'      => __( 'Account Creation', 'wp-food-manager' ),

							'cb_label'   => __( 'Allow account creation', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, non-logged in users will be able to create an account by entering their email address on the submission form.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),

						array(

							'name'       => 'food_manager_generate_username_from_email',

							'std'        => '1',

							'label'      => __( 'Account Username', 'wp-food-manager' ),

							'cb_label'   => __( 'Automatically Generate Username from Email Address', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, a username will be generated from the first part of the user email address. Otherwise, a username field will be shown.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),
						array(
								'name'       => 'food_manager_use_standard_password_setup_email',
								'std'        => '1',
								'label'      => __( 'Account Password', 'wp-food-manager' ),
								'cb_label'   => __( 'Use WordPress\' default behavior and email new users link to set a password', 'wp-food-manager' ),
								'desc'       => __( 'If enabled, an email will be sent to the user with their username and a link to set their password. Otherwise, a password field will be shown and their email address won\'t be verified.', 'wp-food-manager' ),
								'type'       => 'checkbox',
								'attributes' => array()
						),

						array(

							'name'       => 'food_manager_registration_role',

							'std'        => 'organizer',

							'label'      => __( 'Account Role', 'wp-food-manager' ),

							'desc'       => __( 'If you enable user registration on your submission form, choose a role for the new user.', 'wp-food-manager' ),

							'type'       => 'select',

							'options'    => $account_roles
						),

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

								'std'        => '0',
	
								'label'      => __( 'Allow Multiple Banners', 'wp-food-manager' ),
	
								'cb_label'   => __( 'User can submit multiple banner', 'wp-food-manager' ),

								'desc'       => __( 'If enabled, Multiple banner can add at frontend by user and backend side by admin.', 'wp-food-manager' ),
	
								'type'       => 'checkbox',
		
								'attributes' => array()
						),
						array(

							'name'       => 'food_manager_delete_foods_after_finished',

							'std'        => '0',

							'label'      => __( 'Delete listings after finished', 'wp-food-manager' ),

							'cb_label'   => __( 'Delete listings after finished', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, expired listings will automatically deleted after finished.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),
						array(

							'name'       => 'food_manager_delete_expired_foods',

							'std'        => '0',

							'label'      => __( 'Delete Expired listings', 'wp-food-manager' ),

							'cb_label'   => __( 'Expired listings are deleted after 30 days', 'wp-food-manager' ),

							'desc'       => __( 'If enabled, expired listings will automatically deleted after 30 days.', 'wp-food-manager' ),

							'type'       => 'checkbox',

							'attributes' => array()
						),

						array(

							'name'       => 'food_manager_submission_expire_options',

							'std'        => 'food_end_date',

							'label'      => __( 'Listing Expire', 'wp-food-manager' ),

							'desc'       => __( 'You can set food submission expiry time either food end date or specific days.', 'wp-food-manager' ),

							'type'       => 'select',

							'options' => array(

								'food_end_date'  => __( 'Listing expire on Event End Date', 'wp-food-manager' ),

								'days' => __( 'Listing expire on Specified Below Days', 'wp-food-manager' ),
							)
						),

						array(

							'name'       => 'food_manager_submission_duration',

							'std'        => '30',

							'label'      => __( 'Listing Duration', 'wp-food-manager' ),

							'desc'       => __( 'How many <strong>days</strong> listings are live before expiring. Can be left blank to never expire.', 'wp-food-manager' ),

							'attributes' => array()
						),
					    
					    array(
					        
					        'name'       => 'food_manager_enable_categories',
					        
					        'std'        => '1',
					        
					        'label'      => __( 'Categories', 'wp-food-manager' ),
					        
					        'cb_label'   => __( 'Enable categories for listings', 'wp-food-manager' ),
					        
					        'desc'       => __( 'Choose whether to enable categories. Categories must be setup by an admin to allow users to choose them during submission.', 'wp-food-manager' ),
					        
					        'type'       => 'checkbox',
					        
					        'attributes' => array()
					    ),
					    
					    array(
					        
					        'name'       => 'food_manager_enable_food_types',
					        
					        'std'        => '1',
					        
					        'label'      => __( 'Event Types', 'wp-food-manager' ),
					        
					        'cb_label'   => __( 'Enable food types for listings', 'wp-food-manager' ),
					        
					        'desc'       => __( 'Choose whether to enable food types. food types must be setup by an admin to allow users to choose them during submission.', 'wp-food-manager' ),
					        
					        'type'       => 'checkbox',
					        
					        'attributes' => array()
					    ),
					    
					    array(
					        
					        'name'       => 'food_manager_enable_food_ticket_prices',
					        
					        'std'        => '0',
					        
					        'label'      => __( 'Ticket prices', 'wp-food-manager' ),
					        
					        'cb_label'   => __( 'Enable ticket prices for listings', 'wp-food-manager' ),
					        
					        'desc'       => __( 'Choose whether to enable ticket prices. Ticket prices must be setup by an admin to allow users to choose them during submission.', 'wp-food-manager' ),
					        
					        'type'       => 'checkbox',
					        
					        'attributes' => array()
					    ),
					    
						array(
									
								'name'       => 'food_manager_multiselect_food_type',
									
								'std'        => '0',
									
								'label'      => __( 'Multi-select Event Types For Submission', 'wp-food-manager' ),
									
								'cb_label'   => __( 'Enable multi select food type for food listing submission', 'wp-food-manager' ),
									
								'desc'       => __( 'If enabled each food can have more than one type. The metabox on the post editor and the select box for food type on the frontend food submission form are changed by this.', 'wp-food-manager' ),
									
								'type'       => 'checkbox',
									
								'attributes' => array()
						),
						array(
									
								'name'       => 'food_manager_multiselect_food_category',
									
								'std'        => '0',
									
								'label'      => __( 'Multi-select Event Category For Submission', 'wp-food-manager' ),
									
								'cb_label'   => __( 'Enable multi select food category for food listing submission', 'wp-food-manager' ),
									
								'desc'       => __( 'If enabled each food can have more than one category. The metabox on the post editor and the select box for food category on the frontend food submission form are changed by this.', 'wp-food-manager' ),
									
								'type'       => 'checkbox',
									
								'attributes' => array()
						)
					)
				),

				'food_pages' => array(

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
					    
					    array(

							'name' 		=> 'food_manager_submit_organizer_form_page_id',

							'std' 		=> '',

							'label' 	=> __( 'Submit Organizer Form Page', 'wp-food-manager' ),

							'desc'		=> __( 'Select the page where you have placed the [submit_organizer_form] shortcode. This lets the plugin know where the form is located.', 'wp-food-manager' ),

							'type'      => 'page'
						),
						array(
					        
					        'name' 		=> 'food_manager_organizer_dashboard_page_id',
					        
					    	'std' 		=> '',

							'label' 	=> __( 'Organizer Dashboard Page', 'wp-food-manager' ),

							'desc'		=> __( 'Select the page where you have placed the [organizer_dashboard] shortcode. This lets the plugin know where the dashboard is located.', 'wp-food-manager' ),
					        
					        'type'      => 'page'
					    ),
						array(

							'name' 		=> 'food_manager_submit_venue_form_page_id',

							'std' 		=> '',

							'label' 	=> __( 'Submit Venue Form Page', 'wp-food-manager' ),

							'desc'		=> __( 'Select the page where you have placed the [submit_venue_form] shortcode. This lets the plugin know where the form is located.', 'wp-food-manager' ),

							'type'      => 'page'
						),
						array(
					        
					        'name' 		=> 'food_manager_venue_dashboard_page_id',
					        
					    	'std' 		=> '',

							'label' 	=> __( 'Venue Dashboard Page', 'wp-food-manager' ),

							'desc'		=> __( 'Select the page where you have placed the [venue_dashboard] shortcode. This lets the plugin know where the dashboard is located.', 'wp-food-manager' ),
					        
					        'type'      => 'page'
					    ),

					)
				),
				// 'date_time_formatting' => array(
				// 		__( 'Date & Time Format', 'wp-food-manager' ),
				
				// 		array(
				// 				array(
														
				// 						'name' 		=> 'food_manager_datepicker_format',
				
				// 						'std' 		=> '',
				
				// 						'label' 	=> __( 'Datepicker Date Format', 'wp-food-manager' ),
				
				// 						'desc'		=> __( 'Select the date format to use in datepickers', 'wp-food-manager' ),
				
				// 						'type'      => 'select',
				
				// 						'options'	=>  WP_Event_Manager_Date_Time::get_food_manager_date_admin_settings()
				// 				),
				
				// 				array(
				// 						'name' 		=> 'food_manager_timepicker_format',
				// 						'std' 		=> '12',
				// 						'label' 	=> __( 'Timepicker Format', 'wp-food-manager' ),
				
				// 						'desc'		=> __( 'Select the time format to use in timepickers', 'wp-food-manager' ),
				
				// 						'type'      => 'radio',
				
				// 						'options'	=>  array(
				// 								'12' => __( '12 Hours', 'wp-food-manager' ),
				// 								'24' => __( '24 Hours', 'wp-food-manager' )
				// 						)
				// 				),
				// 				array(
				// 						'name' 		=> 'food_manager_timepicker_step',
				
				// 						'std' 		=> '30',
				
				// 						'label' 	=> __( 'Timepicker Step', 'wp-food-manager' ),
				
				// 						'desc'		=> __( 'Select the time step to use in timepickers. Time step must have to be in between 1 to 60.', 'wp-food-manager' ),
				
				// 						'type'      => 'text',
				// 				),
				// 				array(
				// 						'name' 		=> 'food_manager_view_date_format',
				
				// 						'std' 		=> 'Y-m-d',
				
				// 						'label' 	=> __( 'Date Format', 'wp-food-manager' ),
				
				// 						'desc'		=> sprintf( __( 'This date format will be used at the frontend date display. <a href="%s" target="__blank">For more information click here</a>', 'wp-food-manager' ),'https://codex.wordpress.org/Formatting_Date_and_Time'),
				
				// 						'type'      => 'text',
				// 				),
				// 				array(
				// 						'name' 		=> 'food_manager_date_time_format_separator',
										
				// 						'std' 		=> '@',
										
				// 						'label' 	=> __( 'Date And Time Separator', 'wp-food-manager' ),
										
				// 						'desc'		=> __( 'Add date and time separator.', 'wp-food-manager' ),
										
				// 						'type'      => 'text',
				// 				),
				// 				array(
				// 						'name' 		=> 'food_manager_timezone_setting',
								
				// 						'std' 		=> 'site_timezone',
								
				// 						'label' 	=> __( 'Event Timezone', 'wp-food-manager' ),
								
				// 						'desc'		=> __( 'Timezone for the food date and time', 'wp-food-manager' ),
								
				// 						'type'      => 'radio',
				// 						'options'	=> array(
				// 								'site_timezone' 	=> __( 'Use website timezone.', 'wp-food-manager' ),
				// 								'each_food' 		=> __( 'Select timezone on each food', 'wp-food-manager' )
				// 						)
				// 				)
				// 		)
				// )
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
		    
            <div id="plugin_info" class="box-info">
                <div class="box-title" title="Click to toggle"><br></div><h3><span>Plugin Info</span></h3>
                    <div class="inside">
                        <p> 
                             <span class="premium-icon"></span><b><?php _e('Help to improve this plugin!</b> <br>Enjoyed this plugin? You can help by 5 stars rating this plugin on <a href="https://wordpress.org/plugins/wp-food-manager/" target="_blank" >wordpress.org.','wp-food-manager') ?></a>
                        </p>
                        <p>  
                           <?php _e('<span class="help-icon"></span><b>Need help?</b> <br>Read the <a href="https://wp-foodmanager.com/documentation/" target="_blank" >Documentation.</a><br>Check the <a href="https://wp-foodmanager.com/faqs/" target="_blank">FAQs.</a><br>','wp-food-manager'); ?>
                        </p>
                        <p>  
                           <span class="connect-icon"></span><b><?php _e('Demo','wp-food-manager');?></b> <br><?php _e('Visit the','wp-food-manager');?> <a href="http://www.wp-foodmanager.com/select-demo/" target="_blank"><?_e('Plugin Demo.','wp-food-manager');?></a><br>
                           <?php _e('Visit the','wp-food-manager');?> <a href="http://www.wp-foodmanager.com/plugins/" target="_blank"><?php _e('Premium Add-ons','wp-food-manager'); ?></a>.<br>                           
                        </p>
                        
                        <p><span class="light-grey"><?php _e('This plugin was made by','wp-food-manager');?></span> <a href="https://wp-foodmanager.com/" target="_blank"><?php _e('WP Event Manager','wp-food-manager');?></a>.
                        </p>
                    </div>
                </div>
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
