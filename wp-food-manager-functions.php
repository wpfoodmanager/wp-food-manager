<?php

if ( ! function_exists( 'get_food_listings' ) ) :

/**
 * Queries food listings with certain criteria and returns them
 *
 * @access public
 * @return WP_Query
 */

function get_food_listings( $args = array() ) {

	global $wpdb, $food_manager_keyword;

	$args = wp_parse_args( $args, array(

		'search_location'   => '',

		'search_keywords'   => '',

		'search_datetimes' => array(),

		'search_categories' => array(),

		'search_food_types' => array(),

		'offset'            => 0,

		'posts_per_page'    => 15,

		'orderby'           => 'date',

		'order'             => 'DESC',

		'featured'          => null,

		'cancelled'         => null,

		'fields'            => 'all',

		'post_status'       => array(),
	) );

		/**
		 * Perform actions that need to be done prior to the start of the food listings query.
		 *
		 * @since 1.5
		 *
		 * @param array $args Arguments used to retrieve food listings.
		 */
		do_action( 'get_food_listings_init', $args );


	/*if ( false == get_option( 'food_manager_hide_expired', get_option( 'food_manager_hide_expired_content', 1 ) ) ) {
		$post_status = array( 'publish', 'expired' );
	} else {
		$post_status = 'publish';
	}*/
	
	$query_args = array(

		'post_type'              => 'food_manager',

		'post_status'            => 'publish',

		'ignore_sticky_posts'    => 1,

		'offset'                 => absint( $args['offset'] ),

		'posts_per_page'         => intval( $args['posts_per_page'] ),

		'orderby'                => $args['orderby'],

		'order'                  => $args['order'],

		'tax_query'              => array(),

		'meta_query'             => array(),

		'update_post_term_cache' => false,

		'update_post_meta_cache' => false,

		'cache_results'          => false,

		'fields'                 => $args['fields']
	);
	if ( $args['posts_per_page'] < 0 ) {
		$query_args['no_found_rows'] = true;
	}

	if ( ! empty( $args['search_location'] ) ) {

		$location_meta_keys = array( 'geolocation_formatted_address', '_food_location', 'geolocation_state_long' );

		$location_search    = array( 'relation' => 'OR' );

		foreach ( $location_meta_keys as $meta_key ) {

			$location_search[] = array(

				'key'     => $meta_key,

				'value'   => $args['search_location'],

				'compare' => 'like'
			);
		}
		$query_args['meta_query'][] = $location_search;
	}

	if ( ! is_null( $args['featured'] ) ) {

		$query_args['meta_query'][] = array(

			'key'     => '_featured',

			'value'   => '1',

			'compare' => $args['featured'] ? '=' : '!='
		);
	}

	if ( ! is_null( $args['cancelled'] ) || 1 === absint( get_option( 'food_manager_hide_cancelled_foods' ) ) ) {

		$query_args['meta_query'][] = array(

			'key'     => '_cancelled',

			'value'   => '1',

			'compare' => $args['cancelled'] ? '=' : '!='
		);
	}

	if ( ! empty( $args['search_datetimes'][0] ) ) 
	{		
	    $date_search=array();
			if($args['search_datetimes'][0]=='datetime_today')
			{	
				$datetime=date('Y-m-d');
				
				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $datetime,
						'compare' => 'LIKE',
					);
			}
			elseif($args['search_datetimes'][0]=='datetime_tomorrow')
			{ 
				$datetime=date('Y-m-d',strtotime("+1 day")); 
				
				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $datetime,
						'compare' => 'LIKE',
					);
			}
			elseif($args['search_datetimes'][0]=='datetime_thisweek')
			{					
				$year=date('Y');
				$weekNumber=date('W');                 
                $dates[0]= date('Y-m-d', strtotime($year.'W'.str_pad($weekNumber, 2, 0, STR_PAD_LEFT)));
                $dates[1] = date('Y-m-d', strtotime($year.'W'.str_pad($weekNumber, 2, 0, STR_PAD_LEFT).' +6 days'));				

				$date_search[] = array(
					'key'     => '_food_start_date',
					'value'   => $dates,
					'compare' => 'BETWEEN',
					'type'    => 'date'
				);
			} 
			elseif($args['search_datetimes'][0]=='datetime_thisweekend')
			{
				$saturday_date=date('Y-m-d', strtotime('this Saturday', time()));
				$sunday_date=date('Y-m-d', strtotime('this Saturday +1 day', time()));
                $dates[0]= $saturday_date;
                $dates[1]= $sunday_date;
                
			    $date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $dates,
					    'compare' => 'BETWEEN',
					    'type'    => 'date'
					);
			} 
			elseif($args['search_datetimes'][0]=='datetime_thismonth')
			{	
                $dates[0]= date('Y-m-d', strtotime('first day of this month', time()));
                $dates[1] = date('Y-m-d', strtotime('last day of this month', time()));				

				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $dates,
					    'compare' => 'BETWEEN',
					    'type'    => 'date'
					);
			}
			elseif($args['search_datetimes'][0]=='datetime_thisyear')
			{
				$dates[0]= date('Y-m-d', strtotime('first day of january', time()));
                $dates[1] = date('Y-m-d', strtotime('last day of december', time()));	

				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $dates,
					    'compare' => 'BETWEEN',
					    'type'    => 'date'
					);
			}
			elseif($args['search_datetimes'][0]=='datetime_nextweek')
			{
			    $year=date('Y');
				$weekNumber=date('W')+1;                 
                $dates[0]= date('Y-m-d', strtotime($year.'W'.str_pad($weekNumber, 2, 0, STR_PAD_LEFT)));
                $dates[1] = date('Y-m-d', strtotime($year.'W'.str_pad($weekNumber, 2, 0, STR_PAD_LEFT).' +6 days'));	
               
				$date_search[] = array(
					'key'     => '_food_start_date',
					'value'   => $dates,
					'compare' => 'BETWEEN',
					'type'    => 'date'
				);		    
			
			}
			elseif($args['search_datetimes'][0]=='datetime_nextweekend')
			{
				$next_saturday_date=date('Y-m-d', strtotime('next week Saturday', time()));
				$next_sunday_date=date('Y-m-d', strtotime('next week Sunday', time()));
                $dates[0]= $next_saturday_date;
                $dates[1]= $next_sunday_date;               
                
			    $date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $dates,
					    'compare' => 'BETWEEN',
					    'type'    => 'date'
					);
			} 
			elseif($args['search_datetimes'][0]=='datetime_nextmonth')
			{
				$dates[0]= date('Y-m-d', strtotime('first day of next month', time()));
                $dates[1] = date('Y-m-d', strtotime('last day of next month', time()));	
                
				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $dates,
					    'compare' => 'BETWEEN',
					    'type'    => 'date'
					);
			}
			elseif($args['search_datetimes'][0]=='datetime_nextyear')
			{
			    $year=date('Y')+1;
			    $dates[0]= date('Y-m-d', strtotime('first day of January ' . $year, time()));
                $dates[1] = date('Y-m-d', strtotime('last day of december '. $year, time()));              

				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $dates,
					    'compare' => 'BETWEEN',
					    'type'    => 'date'
					);
			}
			else
			{
				$dates = json_decode($args['search_datetimes'][0], true);

				$date_search[] = array(
					'key'     => '_food_start_date',
					'value'   => [$dates['start'], $dates['end']],
				    'compare' => 'BETWEEN',
				    'type'    => 'date'
				);
			}

			$query_args['meta_query'][] = $date_search;
	}

	if ( ! empty( $args['search_categories'][0] ) ) 
	{
		$field    = is_numeric( $args['search_categories'][0] ) ? 'term_id' : 'slug';

		$operator = 'all' === get_option( 'food_manager_category_filter_type', 'all' ) && sizeof( $args['search_categories'] ) > 1 ? 'AND' : 'IN';

		$query_args['tax_query'][] = array(

								'taxonomy'         => 'food_manager_category',

								'field'            => $field,

								'terms'            => array_values( $args['search_categories'] ),

								'include_children' => 'AND' !== $operator,

								'operator'         => $operator
							);
	}
	
	if ( ! empty( $args['search_food_types'][0] ) ) 
	{
		$field    = is_numeric( $args['search_food_types'][0] ) ? 'term_id' : 'slug';	

		$operator = 'all' === get_option( 'food_manager_food_type_filter_type', 'all' ) && sizeof( $args['search_food_types'] ) > 1 ? 'AND' : 'IN';	

		$query_args['tax_query'][] = array(

								'taxonomy'         => 'food_manager_type',

								'field'            => $field,

								'terms'            => array_values( $args['search_food_types'] ),

								'include_children' => $operator !== 'AND' ,

								'operator'         => $operator
							);	
	}
	if ( ! empty( $args['search_tags'][0] ) )
	{
	    $field    = is_numeric( $args['search_tags'][0] ) ? 'term_id' : 'slug';
	    
	    $operator = 'all' === get_option( 'food_manager_food_type_filter_type', 'all' ) && sizeof( $args['search_tags'] ) > 1 ? 'AND' : 'IN';
	    
	    $query_args['tax_query'][] = array(
	        
	        'taxonomy'         => 'food_listing_tag',
	        
	        'field'            => $field,
	        
	        'terms'            => array_values( $args['search_tags'] ),
	        
	        'include_children' => $operator !== 'AND' ,
	        
	        'operator'         => $operator
	    );
	}
	//must match with food_ticket_options options value at wp-food-manager-form-submit-food.php
	if ( ! empty( $args['search_ticket_prices'][0] ) ) 
	{	
	    $ticket_price_value='';
		if($args['search_ticket_prices'][0]=='ticket_price_paid')
		{  
		  $ticket_price_value='paid';     
		}
		else if ($args['search_ticket_prices'][0]=='ticket_price_free')
		{
		  $ticket_price_value='free';
		}
		
		$ticket_search[] = array(

						'key'     => '_food_ticket_options',

						'value'   => $ticket_price_value,

						'compare' => 'LIKE',
					);
		$query_args['meta_query'][] = $ticket_search;
	}

	if ( 'featured' === $args['orderby'] ) {

		$query_args['orderby'] = array(

			'menu_order' => 'ASC',

			'date'       => 'DESC',

			'ID'         => 'DESC',
		);
	}

	if ( 'rand_featured' === $args['orderby'] ) {
			$query_args['orderby'] = array(
				'menu_order' => 'ASC',
				'rand'       => 'ASC',
			);
	}
	//if orderby meta key _food_start_date 
	if ( 'food_start_date' === $args['orderby'] ) {
		$query_args['orderby'] ='meta_value';
		$query_args['meta_key'] ='_food_start_date';
		$query_args['meta_type'] ='DATE';
	}
	
	$food_manager_keyword = sanitize_text_field( $args['search_keywords'] ); 
	if ( ! empty($food_manager_keyword ) && strlen($food_manager_keyword) >= apply_filters( 'food_manager_get_listings_keyword_length_threshold', 2 ) ) {

		$query_args['s'] = $food_manager_keyword;
		
		add_filter( 'posts_search', 'get_food_listings_keyword_search' );
	}
	
	$query_args = apply_filters( 'food_manager_get_listings', $query_args, $args );

	if ( empty( $query_args['meta_query'] ) ) {

		unset( $query_args['meta_query'] );
	}

	if ( empty( $query_args['tax_query'] ) ) {

		unset( $query_args['tax_query'] );
	}
	
	// Polylang LANG arg
	if ( function_exists( 'pll_current_language' ) ) {
		$query_args['lang'] = pll_current_language();
	}
	/** This filter is documented in wp-food-manager.php */
	$query_args['lang'] = apply_filters( 'wpfm_lang', null );
	// Filter args

	$query_args = apply_filters( 'get_food_listings_query_args', $query_args, $args );
	do_action( 'before_get_food_listings', $query_args, $args );
	// Cache results.
		if ( apply_filters( 'get_food_listings_cache_results', false ) ) {
			$to_hash              = wp_json_encode( $query_args );
			$query_args_hash      = 'wpfm_' . md5( $to_hash . WPFM_VERSION ) . WPFM_Cache_Helper::get_transient_version( 'get_food_listings' );
			$result               = false;
			$cached_query_results = true;
			$cached_query_posts   = get_transient( $query_args_hash );
			if ( is_string( $cached_query_posts ) ) {
				$cached_query_posts = json_decode( $cached_query_posts, false );
				if ( $cached_query_posts
				 && is_object( $cached_query_posts )
				 && isset( $cached_query_posts->max_num_pages )
				 && isset( $cached_query_posts->found_posts )
				 && isset( $cached_query_posts->posts )
				 && is_array( $cached_query_posts->posts )
				) {
					$posts  = array_map( 'get_post', $cached_query_posts->posts );
					$result = new WP_Query();
					$result->parse_query( $query_args );
					$result->posts         = $posts;
					$result->found_posts   = intval( $cached_query_posts->found_posts );
					$result->max_num_pages = intval( $cached_query_posts->max_num_pages );
					$result->post_count    = count( $posts );
				}
			}

			if ( false === $result ) {
				$result               = new WP_Query( $query_args );
				$cached_query_results = false;

				$cacheable_result                  = array();
				$cacheable_result['posts']         = array_values( $result->posts );
				$cacheable_result['found_posts']   = $result->found_posts;
				$cacheable_result['max_num_pages'] = $result->max_num_pages;
				//set_transient( $query_args_hash, wp_json_encode( $cacheable_result ), DAY_IN_SECONDS );
			}

			if ( $cached_query_results ) {
				// random order is cached so shuffle them.
				if ( 'rand_featured' === $args['orderby'] ) {
					usort( $result->posts, '_wpfm_shuffle_featured_post_results_helper' );
				} elseif ( 'rand' === $args['orderby'] ) {
					shuffle( $result->posts );
				}
			}
		} else {
			
			$result = new WP_Query( $query_args );
		}
	// Generate hash
	$to_hash  = json_encode( $query_args ) . apply_filters( 'wpml_current_language', '' );

	//$query_args_hash = 'em_' . md5( $to_hash ) . WPFM_Cache_Helper::get_transient_version( 'get_food_listings' );

	

	/*if ( false === ( $result = get_transient( $query_args_hash ) ) ) {
		$result = new WP_Query( $query_args );

		set_transient( $query_args_hash, $result, DAY_IN_SECONDS * 30 );
	}*/

	$result = apply_filters('get_food_listings_result_args',$result,$query_args );
	
	do_action( 'after_get_food_listings', $query_args, $args );

	remove_filter( 'posts_search', 'get_food_listings_keyword_search' );

	return $result;
}

endif;

/**
 * True if an the user can post a food. If accounts are required, and reg is enabled, users can post (they signup at the same time).
 *
 * @return bool
 */

function wpfm_user_can_post_food() {

	$can_post = true;

	if ( ! is_user_logged_in() ) {

		if ( food_manager_user_requires_account() && ! food_manager_enable_registration() ) {

			$can_post = false;
		}
	}
	return apply_filters( 'wpfm_user_can_post_food', $can_post );
}

if ( ! function_exists( 'wp_food_manager_notify_new_user' ) ) :

/**
 * Handle account creation.
*
* @param  int $user_id
* @param  string $password
*/
function wp_food_manager_notify_new_user( $user_id, $password ) {
	global $wp_version;
	
	if ( version_compare( $wp_version, '4.3.1', '<' ) ) {
		wp_new_user_notification( $user_id, $password );
	} else {
		$notify = 'admin';
		if ( empty( $password ) ) {
			$notify = 'both';
		}
		
		wp_new_user_notification( $user_id, null, $notify );
	}
}
endif;

if ( ! function_exists( 'wp_food_manager_create_account' ) ) :

/**
 * Handle account creation.
 *
 * @param  array $args containing username, email, role
 * @param  string $deprecated role string
 * @return WP_error | bool was an account created?
 */

function wp_food_manager_create_account( $args, $deprecated = '' ) {

	global $current_user;
	global $wp_version;
	
	// Soft Deprecated in 1.0
	
	if ( ! is_array( $args ) ) {
		$args = array(
					'username' => '',
					'password' => false,
					'email'    => $args,
					'role'     => $deprecated,
				);
	} else {
		
		$defaults = array(
				
				'username' => '',
				
				'email'    => '',
				
				'password' => false,
				
				'role'     => get_option( 'default_role' )
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
	}
	
	$username = sanitize_user( $args['username'], true );
	
	$email    = apply_filters( 'user_registration_email', sanitize_email( $args['email'] ) );
	
	if ( empty( $email ) ) {
		
		return new WP_Error( 'validation-error', __( 'Invalid email address.', 'wp-food-manager' ) );
	}
	
	if ( empty( $username ) ) {
		
		$username = sanitize_user( current( explode( '@', $email ) ) );
	}
	
	if ( ! is_email( $email ) ) {
		
		return new WP_Error( 'validation-error', __( 'Your email address isn&#8217;t correct.', 'wp-food-manager' ) );
	}
	
	if ( email_exists( $email ) ) {
		
		return new WP_Error( 'validation-error', __( 'This email is already registered, please choose another one.', 'wp-food-manager' ) );
	}
	
	// Ensure username is unique
	
	$append     = 1;
	
	$o_username = $username;
	
	while ( username_exists( $username ) ) {
		
		$username = $o_username . $append;
		
		$append ++;
	}
	
	// Final error checking
	
	$reg_errors = new WP_Error();
	
	$reg_errors = apply_filters( 'food_manager_registration_errors', $reg_errors, $username, $email );
	
	do_action( 'food_manager_register_post', $username, $email, $reg_errors );
	
	if ( $reg_errors->get_error_code() ) {
		
		return $reg_errors;
	}
	
	// Create account
	
	$new_user = array(
			
			'user_login' => $username,
			
			'user_pass'  => $password,
			
			'user_email' => $email,
			
			'role'       => $role
	);
	
	// User is forced to set up account with email sent to them. This password will remain a secret.
	if ( empty( $new_user['user_pass'] ) ) {
		$new_user['user_pass'] = wp_generate_password();
	}
	
	$user_id = wp_insert_user( apply_filters( 'food_manager_create_account_data', $new_user ) );
	
	if ( is_wp_error( $user_id ) ) {
		
		return $user_id;
	}
	
	// Notify
	/**
	 * Send notification to new users.
	 *
	 * @since 1.8
	 *
	 * @param  int         $user_id
	 * @param  string|bool $password
	 * @param  array       $new_user {
	 *     Information about the new user.
	 *
	 *     @type string $user_login Username for the user.
	 *     @type string $user_pass  Password for the user (may be blank).
	 *     @type string $user_email Email for the new user account.
	 *     @type string $role       New user's role.
	 * }
	 */
	do_action( 'food_manager_notify_new_user', $user_id, $password, $new_user );
	
	// Login
	if(!is_user_logged_in()){
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		$current_user = get_user_by( 'id', $user_id );
	}
	
	
	return true;
}

endif;

/**
 * True if an the user can edit a food.
 *
 * @return bool
 */

function food_manager_user_can_edit_food( $food_id ) {

	$can_edit = true;
	
	if ( ! is_user_logged_in() || ! $food_id ) {
		$can_edit = false;
	} else {
		$food      = get_post( $food_id );

		if ( ! $food || ( absint( $food->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $food_id ) ) ) {
			$can_edit = false;
		}
	}
	
	return apply_filters( 'food_manager_user_can_edit_food', $can_edit, $food_id );
}

/**
 * True if registration is enabled.
 *
 * @return bool
 */

function food_manager_enable_registration() {

	return apply_filters( 'food_manager_enable_registration', get_option( 'food_manager_enable_registration' ) == 1 ? true : false );
}

/**
 * True if usernames are generated from email addresses.
 *
 * @return bool
 */

function food_manager_generate_username_from_email() {

	return apply_filters( 'food_manager_generate_username_from_email', get_option( 'food_manager_generate_username_from_email' ) == 1 ? true : false );
}

/**
 * True if an account is required to post a food.
 *
 * @return bool
 */

function food_manager_user_requires_account() {

	return apply_filters( 'food_manager_user_requires_account', get_option( 'food_manager_user_requires_account' ) == 1 ? true : false );
}

/**
 * True if users are allowed to edit submissions that are pending approval.
 *
 * @return bool
 */

function food_manager_user_can_edit_pending_submissions() {

	return apply_filters( 'food_manager_user_can_edit_pending_submissions', get_option( 'food_manager_user_can_edit_pending_submissions' ) == 1 ? true : false );
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


/**
 * Based on wp_dropdown_categories, with the exception of supporting multiple selected categories, food types.
 * @see  wp_dropdown_categories
 */

function food_manager_dropdown_selection( $args = '' ) {

	$defaults = array(

		'orderby'         => 'id',

		'order'           => 'ASC',

		'show_count'      => 0,

		'hide_empty'      => 1,

		'child_of'        => 0,

		'exclude'         => '',

		'echo'            => 1,

		'selected'        => 0,

		'hierarchical'    => 0,

		'name'            => 'cat',

		'id'              => '',

		'class'           => 'food-manager-category-dropdown ' . ( is_rtl() ? 'chosen-rtl' : '' ),

		'depth'           => 0,

		'taxonomy'        => 'food_manager_category',

		'value'           => 'id',

		'multiple'        => true,

		'show_option_all' => false,

		'placeholder'     => __( 'Choose a category&hellip;', 'wp-food-manager' ),

		'no_results_text' => __( 'No results match', 'wp-food-manager' ),

		'multiple_text'   => __( 'Select Some Options', 'wp-food-manager' )
	);

	$r = wp_parse_args( $args, $defaults );

	if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {

		$r['pad_counts'] = true;
	}

	extract( $r );

	// Store in a transient to help sites with many cats

	//$categories_hash = 'wpfm_cats_' . md5( json_encode( $r ) . WPFM_Cache_Helper::get_transient_version( 'wpfm_get_' . $r['taxonomy'] ) );

	//$categories      = get_transient( $categories_hash );

	if ( empty( $categories ) ) {

		$categories = get_terms( $taxonomy, array(

			'orderby'         => $r['orderby'],

			'order'           => $r['order'],

			'hide_empty'      => $r['hide_empty'],

			'child_of'        => $r['child_of'],

			'exclude'         => $r['exclude'],

			'hierarchical'    => $r['hierarchical']
		) );

		//set_transient( $categories_hash, $categories, DAY_IN_SECONDS * 30 );
	}

	$name       = esc_attr( $name );

	$class      = esc_attr( $class );

	$id = $r['id'] ? $r['id'] : $r['name'];

	if($taxonomy=='food_manager_type'):

		$placeholder=__( 'Choose a food type&hellip;', 'wp-food-manager' );

	endif;

	$output = "<select name='" . esc_attr( $name ) . "[]' id='" . esc_attr( $id ) . "' class='" . esc_attr( $class ) . "' " . ( $multiple ? "multiple='multiple'" : '' ) . " data-placeholder='" . esc_attr( $placeholder ) . "' data-no_results_text='" . esc_attr( $no_results_text ) . "' data-multiple_text='" . esc_attr( $multiple_text ) . "'>\n";

	if ( $show_option_all ) {
		/*$cat_arr = array();
		foreach($categories as $category){
			$cat_arr[] = "'$category->slug'";
		}*/
		$output .= '<option value="">' . esc_html( $show_option_all ) . '</option>'; //'.implode(",", $cat_arr).'
	}

	if ( ! empty( $categories ) ) {

		include_once( WPFM_PLUGIN_DIR . '/includes/wpfm-category-walker.php' );

		$walker = new WPFM_Category_Walker;

		if ( $hierarchical ) {

			$depth = $r['depth'];  // Walk the full depth.

		} else {

			$depth = -1; // Flat.
		}

		$output .= $walker->walk( $categories, $depth, $r );
	}

	$output .= "</select>\n";

	if ( $echo ) {

		echo $output;
	}

	return $output;
}

/**
 * Checks if the provided content or the current single page or post has a WPFM shortcode.
 *
 * @param string|null       $content   Content to check. If not provided, it uses the current post content.
 * @param string|array|null $tag Check specifically for one or more shortcodes. If not provided, checks for any WPJM shortcode.
 *
 * @return bool
 */
function has_wpfm_shortcode( $content = null, $tag = null ) {
	global $post;

	$has_wpfm_shortcode = false;

	if ( null === $content && is_singular() && is_a( $post, 'WP_Post' ) ) {
		$content = $post->post_content;
	}

	if ( ! empty( $content ) ) {
		$has_wpfm_shortcode = array( 'add_food', 'food_dashboard', 'foods', 'food_categories', 'food_type', 'food', 'food_summary', 'food_apply' );
		/**
		 * Filters a list of all shortcodes associated with WPFM.
		 *
		 * @since 2.5
		 *
		 * @param string[] $has_wpfm_shortcode
		 */
		$has_wpfm_shortcode = array_unique( apply_filters( 'food_manager_shortcodes', $has_wpfm_shortcode ) );

		if ( null !== $tag ) {
			if ( ! is_array( $tag ) ) {
				$tag = array( $tag );
			}
			$has_wpfm_shortcode = array_intersect( $has_wpfm_shortcode, $tag );
		}

		foreach ( $has_wpfm_shortcode as $shortcode ) {
			if ( has_shortcode( $content, $shortcode ) ) {
				$has_wpfm_shortcode = true;
				break;
			}
		}
	}

	/**
	 * Filter the result of has_wpfm_shortcode()
	 *
	 * @since 2.5
	 *
	 * @param bool $has_wpfm_shortcode
	 */
	return apply_filters( 'has_wpfm_shortcode', $has_wpfm_shortcode );
}

/**
 * Checks if the current page is a food listing.
 *
 * @since 2.5
 *
 * @return bool
 */
function is_wpfm_food_listing() {
	return is_singular( array( 'food_manager' ) );
}


if ( ! function_exists( 'wpfm_get_filtered_links' ) ) :

/**
 * Shows links after filtering foods
 */

function wpfm_get_filtered_links( $args = array() ) {

   
	$search_categories = array();

	$search_food_types= array();

	
	// Convert to slugs

	if ( $args['search_categories'] ) {

		foreach ( $args['search_categories'] as $category ) {

			if ( is_numeric( $category ) ) {

				$category_object = get_term_by( 'id', $category, 'food_manager_category' );

				if ( ! is_wp_error( $category_object ) ) {

					$search_categories [] = $category_object->slug;
				}
				
			} else {

				$search_categories [] = $category;
			}
		}
	}
	
	// Convert to slugs

	if ( $args['search_food_types'] ) {

		foreach ( $args['search_food_types'] as $type) {

			if ( is_numeric( $type) ) {

				$type_object = get_term_by( 'id', $type, 'food_manager_type' );

				if ( ! is_wp_error( $type_object ) ) {

					$search_food_types[] = $type_object->slug;
				}

			} else {

				$search_food_types[] = $type;
			}
		}
	}
	

	$links = apply_filters( 'wpfm_food_filters_showing_foods_links', array(

		'reset' => array(

			'name' => __( 'Reset', 'wp-food-manager' ),

			'url'  => '#'
		),

		/*'rss_link' => array(

			'name' => __( 'RSS', 'wp-food-manager' ),

			'url'  => get_food_manager_rss_link( apply_filters( 'wpfm_get_listings_custom_filter_rss_args', array(

				'search_keywords' => $args['search_keywords'],

				//'search_location' => $args['search_location'],	

				'search_categories'  => implode( ',', $search_categories ),

				'search_food_types'  => implode( ',', $search_food_types),

			) ) )
		)*/
	), $args );

	if ( ! $args['search_keywords'] && ! $args['search_location'] && ! $args['search_categories'] && ! $args['search_food_types']  && ! apply_filters( 'wpfm_get_listings_custom_filter', false ) ) {

		unset( $links['reset'] );
	}

	$return = '';

	$i = 1;
	foreach ( $links as $key => $link ) {

		if($i > 1)
			$return .= ' <a href="#">|</a> ';

		$return .= '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $key ) . '">' . $link['name'] . '</a>';

		$i++;
	}
	
	return $return;
}

endif;


if ( ! function_exists( 'get_food_manager_rss_link' ) ) :

/**
 * Get the Food Listing RSS link
 *
 * @return string
 */

function get_food_manager_rss_link( $args = array() ) {

	$rss_link = add_query_arg( urlencode_deep( array_merge( array( 'feed' => 'food_feed' ), $args ) ), home_url() );

	return $rss_link;
}
endif;


/**
 * Filters the upload dir when $food_manager_upload is true
 * @param  array $pathdata
 * @return array
 */

function wpfm_upload_dir( $pathdata ) {

	global $food_manager_upload, $food_manager_uploading_file;

	if ( ! empty( $food_manager_upload ) ) {

		$dir = untrailingslashit( apply_filters( 'wpfm_upload_dir', 'wpfm-uploads/' . sanitize_key( $food_manager_uploading_file ), sanitize_key( $food_manager_uploading_file ) ) );

		if ( empty( $pathdata['subdir'] ) ) {

			$pathdata['path']   = $pathdata['path'] . '/' . $dir;

			$pathdata['url']    = $pathdata['url'] . '/' . $dir;

			$pathdata['subdir'] = '/' . $dir;

		} else {

			$new_subdir         = '/' . $dir . $pathdata['subdir'];

			$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );

			$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );

			$pathdata['subdir'] = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['subdir'] );
		}
	}
	return $pathdata;
}

add_filter( 'upload_dir', 'wpfm_upload_dir' );

/**
 * Prepare files for upload by standardizing them into an array. This adds support for multiple file upload fields.
 * @param  array $file_data
 * @return array
 */

function wpfm_prepare_uploaded_files( $file_data ) {

	$files_to_upload = array();
	
	if ( is_array( $file_data['name'] ) ) {
		foreach( $file_data['name'] as $file_data_key => $file_data_value ) {
			if ( $file_data['name'][ $file_data_key ] ) {
				$type              = wp_check_filetype( $file_data['name'][ $file_data_key ] ); // Map mime types to those that WordPress knows.
				$files_to_upload[] = array(
					'name'     => $file_data['name'][ $file_data_key ],
					'type'     => $type['type'],
					'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
					'error'    => $file_data['error'][ $file_data_key ],
					'size'     => $file_data['size'][ $file_data_key ]
				);
			}
		}
	} else {
		$type              = wp_check_filetype( $file_data['name'] ); // Map mime types to those that WordPress knows.
		$file_data['type'] = $type['type'];
		$files_to_upload[] = $file_data;
	}
	return apply_filters( 'wpfm_prepare_uploaded_files', $files_to_upload );
}

/**
 * Upload a file using WordPress file API.
 * @param  array $file_data Array of $_FILE data to upload.
 * @param  array $args Optional arguments
 * @return array|WP_Error Array of objects containing either file information or an error
 */

function wpfm_upload_file( $file, $args = array() ) {

	global $food_manager_upload, $food_manager_uploading_file;

	include_once( ABSPATH . 'wp-admin/includes/file.php' );

	include_once( ABSPATH . 'wp-admin/includes/media.php' );

	$args = wp_parse_args( $args, array(

		'file_key'           => '',

		'file_label'         => '',

		'allowed_mime_types' => ''

	) );

	$food_manager_upload         = true;

	$food_manager_uploading_file = $args['file_key'];

	$uploaded_file              = new stdClass();
	
    if ( '' === $args['allowed_mime_types'] ) {
        $allowed_mime_types = wpfm_get_allowed_mime_types( $food_manager_uploading_file );
        
    } else {
        $allowed_mime_types = $args['allowed_mime_types'];
    }
 
    /**
     * Filter file configuration before upload
     *
     * This filter can be used to modify the file arguments before being uploaded, or return a WP_Error
     * object to prevent the file from being uploaded, and return the error.
     *
     * @since 1.0
     *
     * @param array $file               Array of $_FILE data to upload.
     * @param array $args               Optional file arguments
     * @param array $allowed_mime_types Array of allowed mime types from field config or defaults
     */
    $file = apply_filters( 'wpfm_upload_file_pre_upload', $file, $args, $allowed_mime_types );
   
    if ( is_wp_error( $file ) ) {
        return $file;
    }
    
	if ( ! in_array( $file['type'], $allowed_mime_types ) ) {

		if ( $args['file_label'] ) {

			return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s.', 'wp-food-manager' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );

		} else {

			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s.', 'wp-food-manager' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
		}

	} else {

		$upload = wp_handle_upload( $file, apply_filters( 'submit_food_wp_handle_upload_overrides', array( 'test_form' => false ) ) );

		if ( ! empty( $upload['error'] ) ) {

			return new WP_Error( 'upload', $upload['error'] );

		} else {

			$uploaded_file->url       = $upload['url'];

			$uploaded_file->file      = $upload['file'];

			$uploaded_file->name      = basename( $upload['file'] );

			$uploaded_file->type      = $upload['type'];

			$uploaded_file->size      = $file['size'];

			$uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
		}
	}

	$food_manager_upload         = false;

	$food_manager_uploading_file = '';

	return $uploaded_file;
}

/**
 * 
 * @since 3.1.18
 * @param null
 * @return array
 */
function get_food_order_by() 
{
	$args = [
				'title'   => [
					'label' => __('Food Title', 'wp-food-manager'),
					'type' => [
						'title|asc' => __('Ascending (ASC)', 'wp-food-manager'),
						'title|desc' => __('Descending (DESC)', 'wp-food-manager'),
					]
				],
				/*'food_category'   => [
					'label' => __('Food Category', 'wp-food-manager'),
					'type' => [
						'food_category|asc' => __('Ascending (ASC)', 'wp-food-manager'),
						'food_category|desc' => __('Descending (DESC)', 'wp-food-manager'),
					]
				],
				'food_type'   => [
					'label' => __('Food Type', 'wp-food-manager'),
					'type' => [
						'food_type|asc' => __('Ascending (ASC)', 'wp-food-manager'),
						'food_type|desc' => __('Descending (DESC)', 'wp-food-manager'),
					]
				],*/
				/*'food_location'   => [
					'label' => __('Event Location', 'wp-food-manager'),
					'type' => [
						'food_location|asc' => __('Ascending (ASC)', 'wp-food-manager'),
						'food_location|desc' => __('Descending (DESC)', 'wp-food-manager'),
					]
				],*/
			];

	return apply_filters('get_food_order_by_args', $args);
}

/**
 * Allowed Mime types specifically for WP Food Manager.
 * @param   string $field Field used.
 * @return  array  Array of allowed mime types
 */
function wpfm_get_allowed_mime_types( $field = '' ){
	if ( 'organizer_logo' === $field ) {
		$allowed_mime_types = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
		);
	} else {
		$allowed_mime_types = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
				'pdf'          => 'application/pdf',
				'doc'          => 'application/msword',
				'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
	}

	/**
	 * Mime types to accept in uploaded files.
	 *
	 * Default is image, pdf, and doc(x) files.
	 *
	 * @since 1.1
	 *
	 * @param array  {
	 *     Array of allowed file extensions and mime types.
	 *     Key is pipe-separated file extensions. Value is mime type.
	 * }
	 * @param string $field The field key for the upload.
	 */
	return apply_filters( 'wpfm_mime_types', $allowed_mime_types, $field );
}



/**
 * True if only one type allowed per food
 *
 * @return bool
 */
/*function food_manager_multiselect_food_type() {
	return apply_filters( 'food_manager_multiselect_food_type', get_option( 'food_manager_multiselect_food_type' ) == 1 ? true : false );
}*/

/**
 * True if only one category allowed per food
 *
 * @return bool
 */
/*function food_manager_multiselect_food_category() {
	return apply_filters( 'food_manager_multiselect_food_category', get_option( 'food_manager_multiselect_food_category' ) == 1 ? true : false );
}*/

/**
 * Get the page ID of a page if set, with PolyLang compat.
 * @param  string $page e.g. food_dashboard, add_food, foods
 * @return int
 */
function food_manager_get_page_id( $page ) 
{	
	$page_id = get_option( 'food_manager_' . $page . '_page_id', false );
	if ( $page_id ) {
		return apply_filters( 'wpml_object_id', absint( function_exists( 'pll_get_post' ) ? pll_get_post( $page_id ) : $page_id ), 'page', TRUE );
	} else {
		return 0;
	}
	
}

/**
 * Get the permalink of a page if set
 * @param  string $page e.g. food_dashboard, add_food, foods
 * @return string|bool
 */

function food_manager_get_permalink( $page ) {

	if ( $page_id = food_manager_get_page_id( $page ) ) {
		return get_permalink( $page_id );
	} else {
		return false;
	}
}

/**
 * Duplicate a listing.
 * @param  int $post_id
 * @return int 0 on fail or the post ID.
 */
function food_manager_duplicate_listing( $post_id ) {
	if ( empty( $post_id ) || ! ( $post = get_post( $post_id ) ) ) {
		return 0;
	}

	global $wpdb;

	/**
	 * Duplicate the post.
	 */

	$new_post_id = wp_insert_post( array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $post->post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'preview',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
	) );

	
	/**
	 * Copy taxonomies.
	 */
	$taxonomies = get_object_taxonomies( $post->post_type );

	foreach ( $taxonomies as $taxonomy ) {
		$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
		wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
	}

	/*
	 * Duplicate post meta, aside from some reserved fields.
	 */
	$post_meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d", $post_id ) );

	do_action('food_manager_duplicate_listing_meta_start',$post_meta,$post,$new_post_id);

	if ( ! empty( $post_meta ) ) {
		$post_meta = wp_list_pluck( $post_meta, 'meta_value', 'meta_key' );
		foreach ( $post_meta as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, apply_filters( 'food_manager_duplicate_listing_ignore_keys', array( '_cancelled', '_featured', '_food_expires', '_food_duration' ) ) ) ) {
				continue;
			}
			update_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value ) );
		}
	}

	update_post_meta( $new_post_id, '_cancelled', 0 );
	update_post_meta( $new_post_id, '_featured', 0 );

	do_action('food_manager_duplicate_listing_meta_end',$post_meta,$post,$new_post_id);

	return $new_post_id;
}

/**
 * True if only one type allowed per food
 *
 * @return bool
 */
function food_manager_multiselect_food_type() {

	if(!class_exists('WPFM_Form_Submit_Food') ) 
    {
        include_once( WPFM_PLUGIN_DIR . '/forms/wpfm-form-abstract.php' );
        include_once( WPFM_PLUGIN_DIR . '/forms/wpfm-form-submit-food.php' );
    }

    $form_submit_food_instance = call_user_func( array( 'WPFM_Form_Submit_Food', 'instance' ) );
    $food_fields = $form_submit_food_instance->merge_with_custom_fields();

    if( isset($food_fields['food']['food_type']['type']) && $food_fields['food']['food_type']['type'] === 'term-multiselect' )
    {
    	return apply_filters( 'food_manager_multiselect_food_type', true );
    }
    else
    {
    	return apply_filters( 'food_manager_multiselect_food_type', false );
    }
}

/**
 * True if only one category allowed per food
 *
 * @return bool
 */
function food_manager_multiselect_food_category() {

	if(!class_exists('WPFM_Form_Submit_Food') ) 
    {
        include_once( WPFM_PLUGIN_DIR . '/forms/wpfm-form-abstract.php' );
        include_once( WPFM_PLUGIN_DIR . '/forms/wpfm-form-submit-food.php' );
    }

    $form_submit_food_instance = call_user_func( array( 'WPFM_Form_Submit_Food', 'instance' ) );
    $food_fields = $form_submit_food_instance->merge_with_custom_fields();

    if( isset($food_fields['food']['food_category']['type']) && $food_fields['food']['food_category']['type'] === 'term-multiselect' )
    {
    	return apply_filters( 'food_manager_multiselect_food_category', true );
    }
    else
    {
    	return apply_filters( 'food_manager_multiselect_food_category', false );
    }
}

/**
 * Checks to see if the standard password setup email should be used.
 *
 * @since 1.8
 *
 * @return bool True if they are to use standard email, false to allow user to set password at first food creation.
 */
function food_manager_use_standard_password_setup_email() {
	$use_standard_password_setup_email = false;
	
	// If username is being automatically generated, force them to send password setup email.
	if ( food_manager_generate_username_from_email() ) {
		$use_standard_password_setup_email = get_option( 'food_manager_use_standard_password_setup_email', 1 ) == 1 ? true : false;
	}
	
	/**
	 * Allows an override of the setting for if a password should be auto-generated for new users.
	 *
	 * @since 1.8
	 *
	 * @param bool $use_standard_password_setup_email True if a standard account setup email should be sent.
	 */
	return apply_filters( 'food_manager_use_standard_password_setup_email', $use_standard_password_setup_email );
}

/**
 * Checks if a password should be auto-generated for new users.
 *
 * @since 1.8
 *
 * @param string $password Password to validate.
 * @return bool True if password meets rules.
 */
function food_manager_validate_new_password( $password ) {
	// Password must be at least 8 characters long. Trimming here because `wp_hash_password()` will later on.
	$is_valid_password = strlen( trim ( $password ) ) >= 8;
	
	/**
	 * Allows overriding default food Manager password validation rules.
	 *
	 * @since 1.8
	 *
	 * @param bool   $is_valid_password True if new password is validated.
	 * @param string $password          Password to validate.
	 */
	return apply_filters( 'food_manager_validate_new_password', $is_valid_password, $password );
}

/**
 * Returns the password rules hint.
 *
 * @return string
 */
function food_manager_get_password_rules_hint() {
	/**
	 * Allows overriding the hint shown below the new password input field. Describes rules set in `food_manager_validate_new_password`.
	 *
	 * @since 1.8
	 *
	 * @param string $password_rules Password rules description.
	 */
	return apply_filters( 'food_manager_password_rules_hint', __( 'Passwords must be at least 8 characters long.', 'wp-food-manager') );
}

if ( ! function_exists( 'get_food_listing_post_statuses' ) ) :

/**
 * Get post statuses used for foods
 *
 * @access public
 * @return array
 */

function get_food_listing_post_statuses() {

	return apply_filters( 'food_listing_post_statuses', array(

		'draft'           => _x( 'Draft', 'post status', 'wp-food-manager' ),

		'expired'         => _x( 'Expired', 'post status', 'wp-food-manager' ),

		'preview'         => _x( 'Preview', 'post status', 'wp-food-manager' ),

		'pending'         => _x( 'Pending approval', 'post status', 'wp-food-manager' ),

		'pending_payment' => _x( 'Pending payment', 'post status', 'wp-food-manager' ),

		'publish'         => _x( 'Active', 'post status', 'wp-food-manager' ),
	) );
}

endif;

if ( ! function_exists( 'get_food_listing_types' ) ) :

/**
 * Get food listing types
 *
 * @access public
 * @return array
 */

function get_food_listing_types($fields = 'all') {

	if ( ! get_option( 'food_manager_enable_food_types' ) ) 
	{
	     return array();
	}
	else 
	{	
		$args = array(
				'fields'     => $fields,
				'hide_empty' => false,
				'order'      => 'ASC',
				'orderby'    => 'name'
		);
		$args = apply_filters( 'get_food_listing_types_args', $args );
		// Prevent users from filtering the taxonomy
		$args['taxonomy'] = 'food_manager_type';
		return get_terms( $args );
	}
}

endif;

if ( ! function_exists( 'get_food_listing_categories' ) ) :

/**
 * Get food categories
 *
 * @access public
 * @return array
 */

function get_food_listing_categories() {

	if ( ! get_option( 'food_manager_enable_categories' ) ) {
		
		return array();
	}

	$args = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);

		/**
		 * Change the category query arguments.
		 *
		 * @since 2.5
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'get_food_listing_category_args', $args );

		// Prevent users from filtering the taxonomy.
		$args['taxonomy'] = 'food_manager_category';

		return get_terms( $args );
}

endif;

/**
 * Get Base Currency Code.
 *
 * @return string
 */
function get_food_manager_currency() {
	return apply_filters( 'wpfm_currency', get_option( 'wpfm_currency' ) );
}

/**
 * Get full list of currency codes.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols)
 *
 * @return array
 */
function get_food_manager_currencies() {
	static $currencies;

	if ( ! isset( $currencies ) ) {
		$currencies = array_unique(
			apply_filters(
				'food_manager_currencies',
				array(
					'AED' => __( 'United Arab Emirates dirham', 'wp-food-manager' ),
					'AFN' => __( 'Afghan afghani', 'wp-food-manager' ),
					'ALL' => __( 'Albanian lek', 'wp-food-manager' ),
					'AMD' => __( 'Armenian dram', 'wp-food-manager' ),
					'ANG' => __( 'Netherlands Antillean guilder', 'wp-food-manager' ),
					'AOA' => __( 'Angolan kwanza', 'wp-food-manager' ),
					'ARS' => __( 'Argentine peso', 'wp-food-manager' ),
					'AUD' => __( 'Australian dollar', 'wp-food-manager' ),
					'AWG' => __( 'Aruban florin', 'wp-food-manager' ),
					'AZN' => __( 'Azerbaijani manat', 'wp-food-manager' ),
					'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'wp-food-manager' ),
					'BBD' => __( 'Barbadian dollar', 'wp-food-manager' ),
					'BDT' => __( 'Bangladeshi taka', 'wp-food-manager' ),
					'BGN' => __( 'Bulgarian lev', 'wp-food-manager' ),
					'BHD' => __( 'Bahraini dinar', 'wp-food-manager' ),
					'BIF' => __( 'Burundian franc', 'wp-food-manager' ),
					'BMD' => __( 'Bermudian dollar', 'wp-food-manager' ),
					'BND' => __( 'Brunei dollar', 'wp-food-manager' ),
					'BOB' => __( 'Bolivian boliviano', 'wp-food-manager' ),
					'BRL' => __( 'Brazilian real', 'wp-food-manager' ),
					'BSD' => __( 'Bahamian dollar', 'wp-food-manager' ),
					'BTC' => __( 'Bitcoin', 'wp-food-manager' ),
					'BTN' => __( 'Bhutanese ngultrum', 'wp-food-manager' ),
					'BWP' => __( 'Botswana pula', 'wp-food-manager' ),
					'BYR' => __( 'Belarusian ruble (old)', 'wp-food-manager' ),
					'BYN' => __( 'Belarusian ruble', 'wp-food-manager' ),
					'BZD' => __( 'Belize dollar', 'wp-food-manager' ),
					'CAD' => __( 'Canadian dollar', 'wp-food-manager' ),
					'CDF' => __( 'Congolese franc', 'wp-food-manager' ),
					'CHF' => __( 'Swiss franc', 'wp-food-manager' ),
					'CLP' => __( 'Chilean peso', 'wp-food-manager' ),
					'CNY' => __( 'Chinese yuan', 'wp-food-manager' ),
					'COP' => __( 'Colombian peso', 'wp-food-manager' ),
					'CRC' => __( 'Costa Rican col&oacute;n', 'wp-food-manager' ),
					'CUC' => __( 'Cuban convertible peso', 'wp-food-manager' ),
					'CUP' => __( 'Cuban peso', 'wp-food-manager' ),
					'CVE' => __( 'Cape Verdean escudo', 'wp-food-manager' ),
					'CZK' => __( 'Czech koruna', 'wp-food-manager' ),
					'DJF' => __( 'Djiboutian franc', 'wp-food-manager' ),
					'DKK' => __( 'Danish krone', 'wp-food-manager' ),
					'DOP' => __( 'Dominican peso', 'wp-food-manager' ),
					'DZD' => __( 'Algerian dinar', 'wp-food-manager' ),
					'EGP' => __( 'Egyptian pound', 'wp-food-manager' ),
					'ERN' => __( 'Eritrean nakfa', 'wp-food-manager' ),
					'ETB' => __( 'Ethiopian birr', 'wp-food-manager' ),
					'EUR' => __( 'Euro', 'wp-food-manager' ),
					'FJD' => __( 'Fijian dollar', 'wp-food-manager' ),
					'FKP' => __( 'Falkland Islands pound', 'wp-food-manager' ),
					'GBP' => __( 'Pound sterling', 'wp-food-manager' ),
					'GEL' => __( 'Georgian lari', 'wp-food-manager' ),
					'GGP' => __( 'Guernsey pound', 'wp-food-manager' ),
					'GHS' => __( 'Ghana cedi', 'wp-food-manager' ),
					'GIP' => __( 'Gibraltar pound', 'wp-food-manager' ),
					'GMD' => __( 'Gambian dalasi', 'wp-food-manager' ),
					'GNF' => __( 'Guinean franc', 'wp-food-manager' ),
					'GTQ' => __( 'Guatemalan quetzal', 'wp-food-manager' ),
					'GYD' => __( 'Guyanese dollar', 'wp-food-manager' ),
					'HKD' => __( 'Hong Kong dollar', 'wp-food-manager' ),
					'HNL' => __( 'Honduran lempira', 'wp-food-manager' ),
					'HRK' => __( 'Croatian kuna', 'wp-food-manager' ),
					'HTG' => __( 'Haitian gourde', 'wp-food-manager' ),
					'HUF' => __( 'Hungarian forint', 'wp-food-manager' ),
					'IDR' => __( 'Indonesian rupiah', 'wp-food-manager' ),
					'ILS' => __( 'Israeli new shekel', 'wp-food-manager' ),
					'IMP' => __( 'Manx pound', 'wp-food-manager' ),
					'INR' => __( 'Indian rupee', 'wp-food-manager' ),
					'IQD' => __( 'Iraqi dinar', 'wp-food-manager' ),
					'IRR' => __( 'Iranian rial', 'wp-food-manager' ),
					'IRT' => __( 'Iranian toman', 'wp-food-manager' ),
					'ISK' => __( 'Icelandic kr&oacute;na', 'wp-food-manager' ),
					'JEP' => __( 'Jersey pound', 'wp-food-manager' ),
					'JMD' => __( 'Jamaican dollar', 'wp-food-manager' ),
					'JOD' => __( 'Jordanian dinar', 'wp-food-manager' ),
					'JPY' => __( 'Japanese yen', 'wp-food-manager' ),
					'KES' => __( 'Kenyan shilling', 'wp-food-manager' ),
					'KGS' => __( 'Kyrgyzstani som', 'wp-food-manager' ),
					'KHR' => __( 'Cambodian riel', 'wp-food-manager' ),
					'KMF' => __( 'Comorian franc', 'wp-food-manager' ),
					'KPW' => __( 'North Korean won', 'wp-food-manager' ),
					'KRW' => __( 'South Korean won', 'wp-food-manager' ),
					'KWD' => __( 'Kuwaiti dinar', 'wp-food-manager' ),
					'KYD' => __( 'Cayman Islands dollar', 'wp-food-manager' ),
					'KZT' => __( 'Kazakhstani tenge', 'wp-food-manager' ),
					'LAK' => __( 'Lao kip', 'wp-food-manager' ),
					'LBP' => __( 'Lebanese pound', 'wp-food-manager' ),
					'LKR' => __( 'Sri Lankan rupee', 'wp-food-manager' ),
					'LRD' => __( 'Liberian dollar', 'wp-food-manager' ),
					'LSL' => __( 'Lesotho loti', 'wp-food-manager' ),
					'LYD' => __( 'Libyan dinar', 'wp-food-manager' ),
					'MAD' => __( 'Moroccan dirham', 'wp-food-manager' ),
					'MDL' => __( 'Moldovan leu', 'wp-food-manager' ),
					'MGA' => __( 'Malagasy ariary', 'wp-food-manager' ),
					'MKD' => __( 'Macedonian denar', 'wp-food-manager' ),
					'MMK' => __( 'Burmese kyat', 'wp-food-manager' ),
					'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'wp-food-manager' ),
					'MOP' => __( 'Macanese pataca', 'wp-food-manager' ),
					'MRU' => __( 'Mauritanian ouguiya', 'wp-food-manager' ),
					'MUR' => __( 'Mauritian rupee', 'wp-food-manager' ),
					'MVR' => __( 'Maldivian rufiyaa', 'wp-food-manager' ),
					'MWK' => __( 'Malawian kwacha', 'wp-food-manager' ),
					'MXN' => __( 'Mexican peso', 'wp-food-manager' ),
					'MYR' => __( 'Malaysian ringgit', 'wp-food-manager' ),
					'MZN' => __( 'Mozambican metical', 'wp-food-manager' ),
					'NAD' => __( 'Namibian dollar', 'wp-food-manager' ),
					'NGN' => __( 'Nigerian naira', 'wp-food-manager' ),
					'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'wp-food-manager' ),
					'NOK' => __( 'Norwegian krone', 'wp-food-manager' ),
					'NPR' => __( 'Nepalese rupee', 'wp-food-manager' ),
					'NZD' => __( 'New Zealand dollar', 'wp-food-manager' ),
					'OMR' => __( 'Omani rial', 'wp-food-manager' ),
					'PAB' => __( 'Panamanian balboa', 'wp-food-manager' ),
					'PEN' => __( 'Sol', 'wp-food-manager' ),
					'PGK' => __( 'Papua New Guinean kina', 'wp-food-manager' ),
					'PHP' => __( 'Philippine peso', 'wp-food-manager' ),
					'PKR' => __( 'Pakistani rupee', 'wp-food-manager' ),
					'PLN' => __( 'Polish z&#x142;oty', 'wp-food-manager' ),
					'PRB' => __( 'Transnistrian ruble', 'wp-food-manager' ),
					'PYG' => __( 'Paraguayan guaran&iacute;', 'wp-food-manager' ),
					'QAR' => __( 'Qatari riyal', 'wp-food-manager' ),
					'RON' => __( 'Romanian leu', 'wp-food-manager' ),
					'RSD' => __( 'Serbian dinar', 'wp-food-manager' ),
					'RUB' => __( 'Russian ruble', 'wp-food-manager' ),
					'RWF' => __( 'Rwandan franc', 'wp-food-manager' ),
					'SAR' => __( 'Saudi riyal', 'wp-food-manager' ),
					'SBD' => __( 'Solomon Islands dollar', 'wp-food-manager' ),
					'SCR' => __( 'Seychellois rupee', 'wp-food-manager' ),
					'SDG' => __( 'Sudanese pound', 'wp-food-manager' ),
					'SEK' => __( 'Swedish krona', 'wp-food-manager' ),
					'SGD' => __( 'Singapore dollar', 'wp-food-manager' ),
					'SHP' => __( 'Saint Helena pound', 'wp-food-manager' ),
					'SLL' => __( 'Sierra Leonean leone', 'wp-food-manager' ),
					'SOS' => __( 'Somali shilling', 'wp-food-manager' ),
					'SRD' => __( 'Surinamese dollar', 'wp-food-manager' ),
					'SSP' => __( 'South Sudanese pound', 'wp-food-manager' ),
					'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'wp-food-manager' ),
					'SYP' => __( 'Syrian pound', 'wp-food-manager' ),
					'SZL' => __( 'Swazi lilangeni', 'wp-food-manager' ),
					'THB' => __( 'Thai baht', 'wp-food-manager' ),
					'TJS' => __( 'Tajikistani somoni', 'wp-food-manager' ),
					'TMT' => __( 'Turkmenistan manat', 'wp-food-manager' ),
					'TND' => __( 'Tunisian dinar', 'wp-food-manager' ),
					'TOP' => __( 'Tongan pa&#x2bb;anga', 'wp-food-manager' ),
					'TRY' => __( 'Turkish lira', 'wp-food-manager' ),
					'TTD' => __( 'Trinidad and Tobago dollar', 'wp-food-manager' ),
					'TWD' => __( 'New Taiwan dollar', 'wp-food-manager' ),
					'TZS' => __( 'Tanzanian shilling', 'wp-food-manager' ),
					'UAH' => __( 'Ukrainian hryvnia', 'wp-food-manager' ),
					'UGX' => __( 'Ugandan shilling', 'wp-food-manager' ),
					'USD' => __( 'United States (US) dollar', 'wp-food-manager' ),
					'UYU' => __( 'Uruguayan peso', 'wp-food-manager' ),
					'UZS' => __( 'Uzbekistani som', 'wp-food-manager' ),
					'VEF' => __( 'Venezuelan bol&iacute;var', 'wp-food-manager' ),
					'VES' => __( 'Bol&iacute;var soberano', 'wp-food-manager' ),
					'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'wp-food-manager' ),
					'VUV' => __( 'Vanuatu vatu', 'wp-food-manager' ),
					'WST' => __( 'Samoan t&#x101;l&#x101;', 'wp-food-manager' ),
					'XAF' => __( 'Central African CFA franc', 'wp-food-manager' ),
					'XCD' => __( 'East Caribbean dollar', 'wp-food-manager' ),
					'XOF' => __( 'West African CFA franc', 'wp-food-manager' ),
					'XPF' => __( 'CFP franc', 'wp-food-manager' ),
					'YER' => __( 'Yemeni rial', 'wp-food-manager' ),
					'ZAR' => __( 'South African rand', 'wp-food-manager' ),
					'ZMW' => __( 'Zambian kwacha', 'wp-food-manager' ),
				)
			)
		);
	}

	return $currencies;
}

/**
 * Get all available Currency symbols.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols)
 *
 * @since 4.1.0
 * @return array
 */
function get_food_manager_currency_symbols() {

	$symbols = apply_filters(
		'food_manager_currency_symbols',
		array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => 'Afl.',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BYN' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'kr.',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x20be;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x62f;.&#x639;',
			'IRR' => '&#xfdfc;',
			'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
			'ISK' => 'kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x441;&#x43e;&#x43c;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => '&#8376;',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'MDL',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRU' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => 'N&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#1088;&#1089;&#1076;',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STN' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'E',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VES' => 'Bs.S',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'CFA',
			'XCD' => '&#36;',
			'XOF' => 'CFA',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		)
	);

	return $symbols;
}

/**
 * Get Currency symbol.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (https://cldr.unicode.org/translation/currency-names-and-symbols)
 *
 * @param string $currency Currency. (default: '').
 * @return string
 */
function get_food_manager_currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		$currency = get_food_manager_currency();
	}

	$symbols = get_food_manager_currency_symbols();

	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

	return apply_filters( 'food_manager_currency_symbol', $currency_symbol, $currency );
}

/**
 * Get the price format depending on the currency position.
 *
 * @return string
 */
function get_food_manager_price_format() {
	$currency_pos = get_option( 'wpfm_currency_pos' );
	$format       = '%1$s%2$s';

	switch ( $currency_pos ) {
		case 'left':
			$format = '%1$s%2$s';
			break;
		case 'right':
			$format = '%2$s%1$s';
			break;
		case 'left_space':
			$format = '%1$s&nbsp;%2$s';
			break;
		case 'right_space':
			$format = '%2$s&nbsp;%1$s';
			break;
	}

	return apply_filters( 'food_manager_price_format', $format, $currency_pos );
}

/**
 * Return the thousand separator for prices.
 *
 * @since  2.3
 * @return string
 */
function wpfm_get_price_thousand_separator() {
	return stripslashes( apply_filters( 'wpfm_get_price_thousand_separator', get_option( 'wpfm_price_thousand_sep' ) ) );
}

/**
 * Return the decimal separator for prices.
 *
 * @since  2.3
 * @return string
 */
function wpfm_get_price_decimal_separator() {
	$separator = apply_filters( 'wpfm_get_price_decimal_separator', get_option( 'wpfm_price_decimal_sep' ) );
	return $separator ? stripslashes( $separator ) : '.';
}

/**
 * Return the number of decimals after the decimal point.
 *
 * @since  2.3
 * @return int
 */
function wpfm_get_price_decimals() {
	return absint( apply_filters( 'wpfm_get_price_decimals', get_option( 'wpfm_price_num_decimals', 2 ) ) );
}

/**
 * Return fields of Advanced tab in Food data section.
 */
function get_advanced_tab_fields() {
	$adv_fields = apply_filters( 'advanced_food_form_fields', array(
		'food' => array(
			/*'food_menu_order' => array(
				'label'       => __( 'Menu Order', 'wp-food-manager' ),
				'type'        => 'number',
				'required'    => true,
				'placeholder' => '0',
				'priority'    => 3
			),*/

			'enable_food_ingre' => array(
				'label'       => __( 'Enable Ingredient', 'wp-food-manager' ),
				'type'        => 'checkbox',
				'required'    => true,
				'placeholder' => '1',
				'value' => 1,
				'priority'    => 1
			),

			'enable_food_nutri' => array(
				'label'       => __( 'Enable Nutrition', 'wp-food-manager' ),
				'value'         => 1,
				'type'        => 'checkbox',
				'required'    => true,
				'placeholder' => '1',
				'priority'    => 2
			),
		)
	) );

	return $adv_fields;
}


// Font Awesome v. 4.6.

function wpfm_get_font_icons() {

	return array(
		'fa-glass'                               => 'f000',
		'fa-music'                               => 'f001',
		'fa-search'                              => 'f002',
		'fa-envelope-o'                          => 'f003',
		'fa-heart'                               => 'f004',
		'fa-star'                                => 'f005',
		'fa-star-o'                              => 'f006',
		'fa-user'                                => 'f007',
		'fa-film'                                => 'f008',
		'fa-th-large'                            => 'f009',
		'fa-th'                                  => 'f00a',
		'fa-th-list'                             => 'f00b',
		'fa-check'                               => 'f00c',
		'fa-times'                               => 'f00d',
		'fa-search-plus'                         => 'f00e',
		'fa-search-minus'                        => 'f010',
		'fa-power-off'                           => 'f011',
		'fa-signal'                              => 'f012',
		'fa-cog'                                 => 'f013',
		'fa-trash-o'                             => 'f014',
		'fa-home'                                => 'f015',
		'fa-file-o'                              => 'f016',
		'fa-clock-o'                             => 'f017',
		'fa-road'                                => 'f018',
		'fa-download'                            => 'f019',
		'fa-arrow-circle-o-down'                 => 'f01a',
		'fa-arrow-circle-o-up'                   => 'f01b',
		'fa-inbox'                               => 'f01c',
		'fa-play-circle-o'                       => 'f01d',
		'fa-repeat'                              => 'f01e',
		'fa-refresh'                             => 'f021',
		'fa-list-alt'                            => 'f022',
		'fa-lock'                                => 'f023',
		'fa-flag'                                => 'f024',
		'fa-headphones'                          => 'f025',
		'fa-volume-off'                          => 'f026',
		'fa-volume-down'                         => 'f027',
		'fa-volume-up'                           => 'f028',
		'fa-qrcode'                              => 'f029',
		'fa-barcode'                             => 'f02a',
		'fa-tag'                                 => 'f02b',
		'fa-tags'                                => 'f02c',
		'fa-book'                                => 'f02d',
		'fa-bookmark'                            => 'f02e',
		'fa-print'                               => 'f02f',
		'fa-camera'                              => 'f030',
		'fa-font'                                => 'f031',
		'fa-bold'                                => 'f032',
		'fa-italic'                              => 'f033',
		'fa-text-height'                         => 'f034',
		'fa-text-width'                          => 'f035',
		'fa-align-left'                          => 'f036',
		'fa-align-center'                        => 'f037',
		'fa-align-right'                         => 'f038',
		'fa-align-justify'                       => 'f039',
		'fa-list'                                => 'f03a',
		'fa-outdent'                             => 'f03b',
		'fa-indent'                              => 'f03c',
		'fa-video-camera'                        => 'f03d',
		'fa-picture-o'                           => 'f03e',
		'fa-pencil'                              => 'f040',
		'fa-map-marker'                          => 'f041',
		'fa-adjust'                              => 'f042',
		'fa-tint'                                => 'f043',
		'fa-pencil-square-o'                     => 'f044',
		'fa-share-square-o'                      => 'f045',
		'fa-check-square-o'                      => 'f046',
		'fa-arrows'                              => 'f047',
		'fa-step-backward'                       => 'f048',
		'fa-fast-backward'                       => 'f049',
		'fa-backward'                            => 'f04a',
		'fa-play'                                => 'f04b',
		'fa-pause'                               => 'f04c',
		'fa-stop'                                => 'f04d',
		'fa-forward'                             => 'f04e',
		'fa-fast-forward'                        => 'f050',
		'fa-step-forward'                        => 'f051',
		'fa-eject'                               => 'f052',
		'fa-chevron-left'                        => 'f053',
		'fa-chevron-right'                       => 'f054',
		'fa-plus-circle'                         => 'f055',
		'fa-minus-circle'                        => 'f056',
		'fa-times-circle'                        => 'f057',
		'fa-check-circle'                        => 'f058',
		'fa-question-circle'                     => 'f059',
		'fa-info-circle'                         => 'f05a',
		'fa-crosshairs'                          => 'f05b',
		'fa-times-circle-o'                      => 'f05c',
		'fa-check-circle-o'                      => 'f05d',
		'fa-ban'                                 => 'f05e',
		'fa-arrow-left'                          => 'f060',
		'fa-arrow-right'                         => 'f061',
		'fa-arrow-up'                            => 'f062',
		'fa-arrow-down'                          => 'f063',
		'fa-share'                               => 'f064',
		'fa-expand'                              => 'f065',
		'fa-compress'                            => 'f066',
		'fa-plus'                                => 'f067',
		'fa-minus'                               => 'f068',
		'fa-asterisk'                            => 'f069',
		'fa-exclamation-circle'                  => 'f06a',
		'fa-gift'                                => 'f06b',
		'fa-leaf'                                => 'f06c',
		'fa-fire'                                => 'f06d',
		'fa-eye'                                 => 'f06e',
		'fa-eye-slash'                           => 'f070',
		'fa-exclamation-triangle'                => 'f071',
		'fa-plane'                               => 'f072',
		'fa-calendar'                            => 'f073',
		'fa-random'                              => 'f074',
		'fa-comment'                             => 'f075',
		'fa-magnet'                              => 'f076',
		'fa-chevron-up'                          => 'f077',
		'fa-chevron-down'                        => 'f078',
		'fa-retweet'                             => 'f079',
		'fa-shopping-cart'                       => 'f07a',
		'fa-folder'                              => 'f07b',
		'fa-folder-open'                         => 'f07c',
		'fa-arrows-v'                            => 'f07d',
		'fa-arrows-h'                            => 'f07e',
		'fa-bar-chart'                           => 'f080',
		'fa-twitter-square'                      => 'f081',
		'fa-facebook-square'                     => 'f082',
		'fa-camera-retro'                        => 'f083',
		'fa-key'                                 => 'f084',
		'fa-cogs'                                => 'f085',
		'fa-comments'                            => 'f086',
		'fa-thumbs-o-up'                         => 'f087',
		'fa-thumbs-o-down'                       => 'f088',
		'fa-star-half'                           => 'f089',
		'fa-heart-o'                             => 'f08a',
		'fa-sign-out'                            => 'f08b',
		'fa-linkedin-square'                     => 'f08c',
		'fa-thumb-tack'                          => 'f08d',
		'fa-external-link'                       => 'f08e',
		'fa-sign-in'                             => 'f090',
		'fa-trophy'                              => 'f091',
		'fa-github-square'                       => 'f092',
		'fa-upload'                              => 'f093',
		'fa-lemon-o'                             => 'f094',
		'fa-phone'                               => 'f095',
		'fa-square-o'                            => 'f096',
		'fa-bookmark-o'                          => 'f097',
		'fa-phone-square'                        => 'f098',
		'fa-twitter'                             => 'f099',
		'fa-facebook'                            => 'f09a',
		'fa-github'                              => 'f09b',
		'fa-unlock'                              => 'f09c',
		'fa-credit-card'                         => 'f09d',
		'fa-rss'                                 => 'f09e',
		'fa-hdd-o'                               => 'f0a0',
		'fa-bullhorn'                            => 'f0a1',
		'fa-bell'                                => 'f0f3',
		'fa-certificate'                         => 'f0a3',
		'fa-hand-o-right'                        => 'f0a4',
		'fa-hand-o-left'                         => 'f0a5',
		'fa-hand-o-up'                           => 'f0a6',
		'fa-hand-o-down'                         => 'f0a7',
		'fa-arrow-circle-left'                   => 'f0a8',
		'fa-arrow-circle-right'                  => 'f0a9',
		'fa-arrow-circle-up'                     => 'f0aa',
		'fa-arrow-circle-down'                   => 'f0ab',
		'fa-globe'                               => 'f0ac',
		'fa-wrench'                              => 'f0ad',
		'fa-tasks'                               => 'f0ae',
		'fa-filter'                              => 'f0b0',
		'fa-briefcase'                           => 'f0b1',
		'fa-arrows-alt'                          => 'f0b2',
		'fa-users'                               => 'f0c0',
		'fa-link'                                => 'f0c1',
		'fa-cloud'                               => 'f0c2',
		'fa-flask'                               => 'f0c3',
		'fa-scissors'                            => 'f0c4',
		'fa-files-o'                             => 'f0c5',
		'fa-paperclip'                           => 'f0c6',
		'fa-floppy-o'                            => 'f0c7',
		'fa-square'                              => 'f0c8',
		'fa-bars'                                => 'f0c9',
		'fa-list-ul'                             => 'f0ca',
		'fa-list-ol'                             => 'f0cb',
		'fa-strikethrough'                       => 'f0cc',
		'fa-underline'                           => 'f0cd',
		'fa-table'                               => 'f0ce',
		'fa-magic'                               => 'f0d0',
		'fa-truck'                               => 'f0d1',
		'fa-pinterest'                           => 'f0d2',
		'fa-pinterest-square'                    => 'f0d3',
		'fa-google-plus-square'                  => 'f0d4',
		'fa-google-plus'                         => 'f0d5',
		'fa-money'                               => 'f0d6',
		'fa-caret-down'                          => 'f0d7',
		'fa-caret-up'                            => 'f0d8',
		'fa-caret-left'                          => 'f0d9',
		'fa-caret-right'                         => 'f0da',
		'fa-columns'                             => 'f0db',
		'fa-sort'                                => 'f0dc',
		'fa-sort-desc'                           => 'f0dd',
		'fa-sort-asc'                            => 'f0de',
		'fa-envelope'                            => 'f0e0',
		'fa-linkedin'                            => 'f0e1',
		'fa-undo'                                => 'f0e2',
		'fa-gavel'                               => 'f0e3',
		'fa-tachometer'                          => 'f0e4',
		'fa-comment-o'                           => 'f0e5',
		'fa-comments-o'                          => 'f0e6',
		'fa-bolt'                                => 'f0e7',
		'fa-sitemap'                             => 'f0e8',
		'fa-umbrella'                            => 'f0e9',
		'fa-clipboard'                           => 'f0ea',
		'fa-lightbulb-o'                         => 'f0eb',
		'fa-exchange'                            => 'f0ec',
		'fa-cloud-download'                      => 'f0ed',
		'fa-cloud-upload'                        => 'f0ee',
		'fa-user-md'                             => 'f0f0',
		'fa-stethoscope'                         => 'f0f1',
		'fa-suitcase'                            => 'f0f2',
		'fa-bell-o'                              => 'f0a2',
		'fa-coffee'                              => 'f0f4',
		'fa-cutlery'                             => 'f0f5',
		'fa-file-text-o'                         => 'f0f6',
		'fa-building-o'                          => 'f0f7',
		'fa-hospital-o'                          => 'f0f8',
		'fa-ambulance'                           => 'f0f9',
		'fa-medkit'                              => 'f0fa',
		'fa-fighter-jet'                         => 'f0fb',
		'fa-beer'                                => 'f0fc',
		'fa-h-square'                            => 'f0fd',
		'fa-plus-square'                         => 'f0fe',
		'fa-angle-double-left'                   => 'f100',
		'fa-angle-double-right'                  => 'f101',
		'fa-angle-double-up'                     => 'f102',
		'fa-angle-double-down'                   => 'f103',
		'fa-angle-left'                          => 'f104',
		'fa-angle-right'                         => 'f105',
		'fa-angle-up'                            => 'f106',
		'fa-angle-down'                          => 'f107',
		'fa-desktop'                             => 'f108',
		'fa-laptop'                              => 'f109',
		'fa-tablet'                              => 'f10a',
		'fa-mobile'                              => 'f10b',
		'fa-circle-o'                            => 'f10c',
		'fa-quote-left'                          => 'f10d',
		'fa-quote-right'                         => 'f10e',
		'fa-spinner'                             => 'f110',
		'fa-circle'                              => 'f111',
		'fa-reply'                               => 'f112',
		'fa-github-alt'                          => 'f113',
		'fa-folder-o'                            => 'f114',
		'fa-folder-open-o'                       => 'f115',
		'fa-smile-o'                             => 'f118',
		'fa-frown-o'                             => 'f119',
		'fa-meh-o'                               => 'f11a',
		'fa-gamepad'                             => 'f11b',
		'fa-keyboard-o'                          => 'f11c',
		'fa-flag-o'                              => 'f11d',
		'fa-flag-checkered'                      => 'f11e',
		'fa-terminal'                            => 'f120',
		'fa-code'                                => 'f121',
		'fa-reply-all'                           => 'f122',
		'fa-star-half-o'                         => 'f123',
		'fa-location-arrow'                      => 'f124',
		'fa-crop'                                => 'f125',
		'fa-code-fork'                           => 'f126',
		'fa-chain-broken'                        => 'f127',
		'fa-question'                            => 'f128',
		'fa-info'                                => 'f129',
		'fa-exclamation'                         => 'f12a',
		'fa-superscript'                         => 'f12b',
		'fa-subscript'                           => 'f12c',
		'fa-eraser'                              => 'f12d',
		'fa-puzzle-piece'                        => 'f12e',
		'fa-microphone'                          => 'f130',
		'fa-microphone-slash'                    => 'f131',
		'fa-shield'                              => 'f132',
		'fa-calendar-o'                          => 'f133',
		'fa-fire-extinguisher'                   => 'f134',
		'fa-rocket'                              => 'f135',
		'fa-maxcdn'                              => 'f136',
		'fa-chevron-circle-left'                 => 'f137',
		'fa-chevron-circle-right'                => 'f138',
		'fa-chevron-circle-up'                   => 'f139',
		'fa-chevron-circle-down'                 => 'f13a',
		'fa-html5'                               => 'f13b',
		'fa-css3'                                => 'f13c',
		'fa-anchor'                              => 'f13d',
		'fa-unlock-alt'                          => 'f13e',
		'fa-bullseye'                            => 'f140',
		'fa-ellipsis-h'                          => 'f141',
		'fa-ellipsis-v'                          => 'f142',
		'fa-rss-square'                          => 'f143',
		'fa-play-circle'                         => 'f144',
		'fa-ticket'                              => 'f145',
		'fa-minus-square'                        => 'f146',
		'fa-minus-square-o'                      => 'f147',
		'fa-level-up'                            => 'f148',
		'fa-level-down'                          => 'f149',
		'fa-check-square'                        => 'f14a',
		'fa-pencil-square'                       => 'f14b',
		'fa-external-link-square'                => 'f14c',
		'fa-share-square'                        => 'f14d',
		'fa-compass'                             => 'f14e',
		'fa-caret-square-o-down'                 => 'f150',
		'fa-caret-square-o-up'                   => 'f151',
		'fa-caret-square-o-right'                => 'f152',
		'fa-eur'                                 => 'f153',
		'fa-gbp'                                 => 'f154',
		'fa-usd'                                 => 'f155',
		'fa-inr'                                 => 'f156',
		'fa-jpy'                                 => 'f157',
		'fa-rub'                                 => 'f158',
		'fa-krw'                                 => 'f159',
		'fa-btc'                                 => 'f15a',
		'fa-file'                                => 'f15b',
		'fa-file-text'                           => 'f15c',
		'fa-sort-alpha-asc'                      => 'f15d',
		'fa-sort-alpha-desc'                     => 'f15e',
		'fa-sort-amount-asc'                     => 'f160',
		'fa-sort-amount-desc'                    => 'f161',
		'fa-sort-numeric-asc'                    => 'f162',
		'fa-sort-numeric-desc'                   => 'f163',
		'fa-thumbs-up'                           => 'f164',
		'fa-thumbs-down'                         => 'f165',
		'fa-youtube-square'                      => 'f166',
		'fa-youtube'                             => 'f167',
		'fa-xing'                                => 'f168',
		'fa-xing-square'                         => 'f169',
		'fa-youtube-play'                        => 'f16a',
		'fa-dropbox'                             => 'f16b',
		'fa-stack-overflow'                      => 'f16c',
		'fa-instagram'                           => 'f16d',
		'fa-flickr'                              => 'f16e',
		'fa-adn'                                 => 'f170',
		'fa-bitbucket'                           => 'f171',
		'fa-bitbucket-square'                    => 'f172',
		'fa-tumblr'                              => 'f173',
		'fa-tumblr-square'                       => 'f174',
		'fa-long-arrow-down'                     => 'f175',
		'fa-long-arrow-up'                       => 'f176',
		'fa-long-arrow-left'                     => 'f177',
		'fa-long-arrow-right'                    => 'f178',
		'fa-apple'                               => 'f179',
		'fa-windows'                             => 'f17a',
		'fa-android'                             => 'f17b',
		'fa-linux'                               => 'f17c',
		'fa-dribbble'                            => 'f17d',
		'fa-skype'                               => 'f17e',
		'fa-foursquare'                          => 'f180',
		'fa-trello'                              => 'f181',
		'fa-female'                              => 'f182',
		'fa-male'                                => 'f183',
		'fa-gratipay'                            => 'f184',
		'fa-sun-o'                               => 'f185',
		'fa-moon-o'                              => 'f186',
		'fa-archive'                             => 'f187',
		'fa-bug'                                 => 'f188',
		'fa-vk'                                  => 'f189',
		'fa-weibo'                               => 'f18a',
		'fa-renren'                              => 'f18b',
		'fa-pagelines'                           => 'f18c',
		'fa-stack-exchange'                      => 'f18d',
		'fa-arrow-circle-o-right'                => 'f18e',
		'fa-arrow-circle-o-left'                 => 'f190',
		'fa-caret-square-o-left'                 => 'f191',
		'fa-dot-circle-o'                        => 'f192',
		'fa-wheelchair'                          => 'f193',
		'fa-vimeo-square'                        => 'f194',
		'fa-try'                                 => 'f195',
		'fa-plus-square-o'                       => 'f196',
		'fa-space-shuttle'                       => 'f197',
		'fa-slack'                               => 'f198',
		'fa-envelope-square'                     => 'f199',
		'fa-wordpress'                           => 'f19a',
		'fa-openid'                              => 'f19b',
		'fa-university'                          => 'f19c',
		'fa-graduation-cap'                      => 'f19d',
		'fa-yahoo'                               => 'f19e',
		'fa-google'                              => 'f1a0',
		'fa-reddit'                              => 'f1a1',
		'fa-reddit-square'                       => 'f1a2',
		'fa-stumbleupon-circle'                  => 'f1a3',
		'fa-stumbleupon'                         => 'f1a4',
		'fa-delicious'                           => 'f1a5',
		'fa-digg'                                => 'f1a6',
		'fa-pied-piper-pp'                       => 'f1a7',
		'fa-pied-piper-alt'                      => 'f1a8',
		'fa-drupal'                              => 'f1a9',
		'fa-joomla'                              => 'f1aa',
		'fa-language'                            => 'f1ab',
		'fa-fax'                                 => 'f1ac',
		'fa-building'                            => 'f1ad',
		'fa-child'                               => 'f1ae',
		'fa-paw'                                 => 'f1b0',
		'fa-spoon'                               => 'f1b1',
		'fa-cube'                                => 'f1b2',
		'fa-cubes'                               => 'f1b3',
		'fa-behance'                             => 'f1b4',
		'fa-behance-square'                      => 'f1b5',
		'fa-steam'                               => 'f1b6',
		'fa-steam-square'                        => 'f1b7',
		'fa-recycle'                             => 'f1b8',
		'fa-car'                                 => 'f1b9',
		'fa-taxi'                                => 'f1ba',
		'fa-tree'                                => 'f1bb',
		'fa-spotify'                             => 'f1bc',
		'fa-deviantart'                          => 'f1bd',
		'fa-soundcloud'                          => 'f1be',
		'fa-database'                            => 'f1c0',
		'fa-file-pdf-o'                          => 'f1c1',
		'fa-file-word-o'                         => 'f1c2',
		'fa-file-excel-o'                        => 'f1c3',
		'fa-file-powerpoint-o'                   => 'f1c4',
		'fa-file-image-o'                        => 'f1c5',
		'fa-file-archive-o'                      => 'f1c6',
		'fa-file-audio-o'                        => 'f1c7',
		'fa-file-video-o'                        => 'f1c8',
		'fa-file-code-o'                         => 'f1c9',
		'fa-vine'                                => 'f1ca',
		'fa-codepen'                             => 'f1cb',
		'fa-jsfiddle'                            => 'f1cc',
		'fa-life-ring'                           => 'f1cd',
		'fa-circle-o-notch'                      => 'f1ce',
		'fa-rebel'                               => 'f1d0',
		'fa-empire'                              => 'f1d1',
		'fa-git-square'                          => 'f1d2',
		'fa-git'                                 => 'f1d3',
		'fa-hacker-news'                         => 'f1d4',
		'fa-tencent-weibo'                       => 'f1d5',
		'fa-qq'                                  => 'f1d6',
		'fa-weixin'                              => 'f1d7',
		'fa-paper-plane'                         => 'f1d8',
		'fa-paper-plane-o'                       => 'f1d9',
		'fa-history'                             => 'f1da',
		'fa-circle-thin'                         => 'f1db',
		'fa-header'                              => 'f1dc',
		'fa-paragraph'                           => 'f1dd',
		'fa-sliders'                             => 'f1de',
		'fa-share-alt'                           => 'f1e0',
		'fa-share-alt-square'                    => 'f1e1',
		'fa-bomb'                                => 'f1e2',
		'fa-futbol-o'                            => 'f1e3',
		'fa-tty'                                 => 'f1e4',
		'fa-binoculars'                          => 'f1e5',
		'fa-plug'                                => 'f1e6',
		'fa-slideshare'                          => 'f1e7',
		'fa-twitch'                              => 'f1e8',
		'fa-yelp'                                => 'f1e9',
		'fa-newspaper-o'                         => 'f1ea',
		'fa-wifi'                                => 'f1eb',
		'fa-calculator'                          => 'f1ec',
		'fa-paypal'                              => 'f1ed',
		'fa-google-wallet'                       => 'f1ee',
		'fa-cc-visa'                             => 'f1f0',
		'fa-cc-mastercard'                       => 'f1f1',
		'fa-cc-discover'                         => 'f1f2',
		'fa-cc-amex'                             => 'f1f3',
		'fa-cc-paypal'                           => 'f1f4',
		'fa-cc-stripe'                           => 'f1f5',
		'fa-bell-slash'                          => 'f1f6',
		'fa-bell-slash-o'                        => 'f1f7',
		'fa-trash'                               => 'f1f8',
		'fa-copyright'                           => 'f1f9',
		'fa-at'                                  => 'f1fa',
		'fa-eyedropper'                          => 'f1fb',
		'fa-paint-brush'                         => 'f1fc',
		'fa-birthday-cake'                       => 'f1fd',
		'fa-area-chart'                          => 'f1fe',
		'fa-pie-chart'                           => 'f200',
		'fa-line-chart'                          => 'f201',
		'fa-lastfm'                              => 'f202',
		'fa-lastfm-square'                       => 'f203',
		'fa-toggle-off'                          => 'f204',
		'fa-toggle-on'                           => 'f205',
		'fa-bicycle'                             => 'f206',
		'fa-bus'                                 => 'f207',
		'fa-ioxhost'                             => 'f208',
		'fa-angellist'                           => 'f209',
		'fa-cc'                                  => 'f20a',
		'fa-ils'                                 => 'f20b',
		'fa-meanpath'                            => 'f20c',
		'fa-buysellads'                          => 'f20d',
		'fa-connectdevelop'                      => 'f20e',
		'fa-dashcube'                            => 'f210',
		'fa-forumbee'                            => 'f211',
		'fa-leanpub'                             => 'f212',
		'fa-sellsy'                              => 'f213',
		'fa-shirtsinbulk'                        => 'f214',
		'fa-simplybuilt'                         => 'f215',
		'fa-skyatlas'                            => 'f216',
		'fa-cart-plus'                           => 'f217',
		'fa-cart-arrow-down'                     => 'f218',
		'fa-diamond'                             => 'f219',
		'fa-ship'                                => 'f21a',
		'fa-user-secret'                         => 'f21b',
		'fa-motorcycle'                          => 'f21c',
		'fa-street-view'                         => 'f21d',
		'fa-heartbeat'                           => 'f21e',
		'fa-venus'                               => 'f221',
		'fa-mars'                                => 'f222',
		'fa-mercury'                             => 'f223',
		'fa-transgender'                         => 'f224',
		'fa-transgender-alt'                     => 'f225',
		'fa-venus-double'                        => 'f226',
		'fa-mars-double'                         => 'f227',
		'fa-venus-mars'                          => 'f228',
		'fa-mars-stroke'                         => 'f229',
		'fa-mars-stroke-v'                       => 'f22a',
		'fa-mars-stroke-h'                       => 'f22b',
		'fa-neuter'                              => 'f22c',
		'fa-genderless'                          => 'f22d',
		'fa-facebook-official'                   => 'f230',
		'fa-pinterest-p'                         => 'f231',
		'fa-whatsapp'                            => 'f232',
		'fa-server'                              => 'f233',
		'fa-user-plus'                           => 'f234',
		'fa-user-times'                          => 'f235',
		'fa-bed'                                 => 'f236',
		'fa-viacoin'                             => 'f237',
		'fa-train'                               => 'f238',
		'fa-subway'                              => 'f239',
		'fa-medium'                              => 'f23a',
		'fa-y-combinator'                        => 'f23b',
		'fa-optin-monster'                       => 'f23c',
		'fa-opencart'                            => 'f23d',
		'fa-expeditedssl'                        => 'f23e',
		'fa-battery-full'                        => 'f240',
		'fa-battery-three-quarters'              => 'f241',
		'fa-battery-half'                        => 'f242',
		'fa-battery-quarter'                     => 'f243',
		'fa-battery-empty'                       => 'f244',
		'fa-mouse-pointer'                       => 'f245',
		'fa-i-cursor'                            => 'f246',
		'fa-object-group'                        => 'f247',
		'fa-object-ungroup'                      => 'f248',
		'fa-sticky-note'                         => 'f249',
		'fa-sticky-note-o'                       => 'f24a',
		'fa-cc-jcb'                              => 'f24b',
		'fa-cc-diners-club'                      => 'f24c',
		'fa-clone'                               => 'f24d',
		'fa-balance-scale'                       => 'f24e',
		'fa-hourglass-o'                         => 'f250',
		'fa-hourglass-start'                     => 'f251',
		'fa-hourglass-half'                      => 'f252',
		'fa-hourglass-end'                       => 'f253',
		'fa-hourglass'                           => 'f254',
		'fa-hand-rock-o'                         => 'f255',
		'fa-hand-paper-o'                        => 'f256',
		'fa-hand-scissors-o'                     => 'f257',
		'fa-hand-lizard-o'                       => 'f258',
		'fa-hand-spock-o'                        => 'f259',
		'fa-hand-pointer-o'                      => 'f25a',
		'fa-hand-peace-o'                        => 'f25b',
		'fa-trademark'                           => 'f25c',
		'fa-registered'                          => 'f25d',
		'fa-creative-commons'                    => 'f25e',
		'fa-gg'                                  => 'f260',
		'fa-gg-circle'                           => 'f261',
		'fa-tripadvisor'                         => 'f262',
		'fa-odnoklassniki'                       => 'f263',
		'fa-odnoklassniki-square'                => 'f264',
		'fa-get-pocket'                          => 'f265',
		'fa-wikipedia-w'                         => 'f266',
		'fa-safari'                              => 'f267',
		'fa-chrome'                              => 'f268',
		'fa-firefox'                             => 'f269',
		'fa-opera'                               => 'f26a',
		'fa-internet-explorer'                   => 'f26b',
		'fa-television'                          => 'f26c',
		'fa-contao'                              => 'f26d',
		'fa-500px'                               => 'f26e',
		'fa-amazon'                              => 'f270',
		'fa-calendar-plus-o'                     => 'f271',
		'fa-calendar-minus-o'                    => 'f272',
		'fa-calendar-times-o'                    => 'f273',
		'fa-calendar-check-o'                    => 'f274',
		'fa-industry'                            => 'f275',
		'fa-map-pin'                             => 'f276',
		'fa-map-signs'                           => 'f277',
		'fa-map-o'                               => 'f278',
		'fa-map'                                 => 'f279',
		'fa-commenting'                          => 'f27a',
		'fa-commenting-o'                        => 'f27b',
		'fa-houzz'                               => 'f27c',
		'fa-vimeo'                               => 'f27d',
		'fa-black-tie'                           => 'f27e',
		'fa-fonticons'                           => 'f280',
		'fa-reddit-alien'                        => 'f281',
		'fa-edge'                                => 'f282',
		'fa-credit-card-alt'                     => 'f283',
		'fa-codiepie'                            => 'f284',
		'fa-modx'                                => 'f285',
		'fa-fort-awesome'                        => 'f286',
		'fa-usb'                                 => 'f287',
		'fa-product-hunt'                        => 'f288',
		'fa-mixcloud'                            => 'f289',
		'fa-scribd'                              => 'f28a',
		'fa-pause-circle'                        => 'f28b',
		'fa-pause-circle-o'                      => 'f28c',
		'fa-stop-circle'                         => 'f28d',
		'fa-stop-circle-o'                       => 'f28e',
		'fa-shopping-bag'                        => 'f290',
		'fa-shopping-basket'                     => 'f291',
		'fa-hashtag'                             => 'f292',
		'fa-bluetooth'                           => 'f293',
		'fa-bluetooth-b'                         => 'f294',
		'fa-percent'                             => 'f295',
		'fa-gitlab'                              => 'f296',
		'fa-wpbeginner'                          => 'f297',
		'fa-wpforms'                             => 'f298',
		'fa-envira'                              => 'f299',
		'fa-universal-access'                    => 'f29a',
		'fa-wheelchair-alt'                      => 'f29b',
		'fa-question-circle-o'                   => 'f29c',
		'fa-blind'                               => 'f29d',
		'fa-audio-description'                   => 'f29e',
		'fa-volume-control-phone'                => 'f2a0',
		'fa-braille'                             => 'f2a1',
		'fa-assistive-listening-systems'         => 'f2a2',
		'fa-american-sign-language-interpreting' => 'f2a3',
		'fa-deaf'                                => 'f2a4',
		'fa-glide'                               => 'f2a5',
		'fa-glide-g'                             => 'f2a6',
		'fa-sign-language'                       => 'f2a7',
		'fa-low-vision'                          => 'f2a8',
		'fa-viadeo'                              => 'f2a9',
		'fa-viadeo-square'                       => 'f2aa',
		'fa-snapchat'                            => 'f2ab',
		'fa-snapchat-ghost'                      => 'f2ac',
		'fa-snapchat-square'                     => 'f2ad',
		'fa-pied-piper'                          => 'f2ae',
		'fa-first-order'                         => 'f2b0',
		'fa-yoast'                               => 'f2b1',
		'fa-themeisle'                           => 'f2b2',
		'fa-google-plus-official'                => 'f2b3',
		'fa-font-awesome'                        => 'f2b4'
	);
}

if ( ! function_exists( 'get_food_listings_keyword_search' ) ) :

	/**
	 * Join and where query for keywords
	 *
	 * @param array $search
	 * @return array
	 */

function get_food_listings_keyword_search( $search) {
		
		global $wpdb, $food_manager_keyword;
		// Searchable Meta Keys: set to empty to search all meta keys
		$searchable_meta_keys = array(
				'_food_location',
				'_organizer_name',
				'_food_tags',
		);
		$searchable_meta_keys = apply_filters( 'food_listing_searchable_meta_keys', $searchable_meta_keys );
		
		$conditions   = array();
		
		// Search Post Meta
		if( apply_filters( 'food_listing_search_post_meta', true ) ) {
			// Only selected meta keys
			if( $searchable_meta_keys ) {
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ( '" . implode( "','", array_map( 'esc_sql', $searchable_meta_keys ) ) . "' ) AND meta_value LIKE '%" . esc_sql( $food_manager_keyword ) . "%' )";
			} else {
				// No meta keys defined, search all post meta value
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%" . esc_sql( $food_manager_keyword ) . "%' )";
			}
		}
		
		// Search taxonomy
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id WHERE t.name LIKE '%" . esc_sql( $food_manager_keyword ) . "%' )";
		
		/**
 		 * Filters the conditions to use when querying food listings. Resulting array is joined with OR statements.
 		 *
 		 * @since 1.5
 		 *
 		 * @param array  $conditions          Conditions to join by OR when querying food listings.
 		 * @param string $food_manager_keyword Search query.
 		 */
		$conditions = apply_filters( 'food_listing_search_conditions', $conditions, $food_manager_keyword );
		if ( empty( $conditions ) ) {
				return $search;			
		}
		$conditions_str = implode( ' OR ', $conditions );
		
		if ( ! empty( $search ) ) {
			$search = preg_replace( '/^ AND /', '', $search );
			$search = " AND ( {$search} OR ( {$conditions_str} ) )";
		} else {
			$search = " AND ( {$conditions_str} )";
		}
		return $search;
	}

endif;


/**
 * Checks if the user can upload a file via the Ajax endpoint.
 *
 * @since 1.7
 * @return bool
 */
function food_manager_user_can_upload_file_via_ajax() {
	$can_upload = is_user_logged_in() && wpfm_user_can_post_food();
	/**
	 * Override ability of a user to upload a file via Ajax.
	 *
	 * @since 1.7
	 * @param bool $can_upload True if they can upload files from Ajax endpoint.
	 */
	return apply_filters( 'food_manager_user_can_upload_file_via_ajax', $can_upload );
}


/**
 * Use radio inputs instead of checkboxes for term checklists in specified taxonomies such as 'food_manager_type'.
 *
 * @param   array   $args
 * @return  array
 */
function wpfm_term_radio_checklist_for_food_type( $args ) {
    if ( ! empty( $args['taxonomy'] ) && $args['taxonomy'] === 'food_manager_type' /* <== Change to your required taxonomy */ ) {
        if ( empty( $args['walker'] ) || is_a( $args['walker'], 'Walker' ) ) { // Don't override 3rd party walkers.
            if ( ! class_exists( 'WPFM_Walker_Category_Radio_Checklist_For_Food_Type' ) ) {
                /**
                 * Custom walker for switching checkbox inputs to radio.
                 *
                 * @see Walker_Category_Checklist
                 */
                class WPFM_Walker_Category_Radio_Checklist_For_Food_Type extends Walker_Category_Checklist {
                    function walk( $elements, $max_depth, ...$args ) {
                        $output = parent::walk( $elements, $max_depth, ...$args );
                        $output = str_replace(
                            array( 'type="checkbox"', "type='checkbox'" ),
                            array( 'type="radio"', "type='radio'" ),
                            $output
                        );

                        return $output;
                    }
                }
            }

            $args['walker'] = new WPFM_Walker_Category_Radio_Checklist_For_Food_Type;
        }
    }

    return $args;
}

add_filter( 'wp_terms_checklist_args', 'wpfm_term_radio_checklist_for_food_type' );


//Add image field in 'food_manager_type' taxonomy page
add_action( 'food_manager_type_add_form_fields', 'wpfm_add_custom_taxonomy_image_for_food_type', 10, 2 );
function wpfm_add_custom_taxonomy_image_for_food_type ( $taxonomy ) {
?>
    <div class="form-field term-group">

        <label for="image_id" class="wpfm-food-type-tax-image"><?php _e('Image/Icon', 'taxt-domain'); ?></label>
        <input type="hidden" id="image_id" name="image_id" class="custom_media_url" value="">

        <div id="image_wrapper"></div>

        <p>
            <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php _e( 'Add Image', 'taxt-domain' ); ?>">
            <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php _e( 'Remove Image', 'taxt-domain' ); ?>">
        </p>

    </div>
<?php
}

//Save the 'food_manager_type' taxonomy image field
add_action( 'created_food_manager_type', 'wpfm_save_custom_taxonomy_image_for_food_type', 10, 2 );
function wpfm_save_custom_taxonomy_image_for_food_type ( $term_id, $tt_id ) {
    if( isset( $_POST['image_id'] ) && '' !== $_POST['image_id'] ){
     $image = $_POST['image_id'];
     add_term_meta( $term_id, 'image_id', $image, true );
    }
}

//Add the image field in edit form page
add_action( 'food_manager_type_edit_form_fields', 'wpfm_update_custom_taxonomy_image_for_food_type', 10, 2 );
function wpfm_update_custom_taxonomy_image_for_food_type ( $term, $taxonomy ) { ?>
    <tr class="form-field term-group-wrap">
        <th scope="row">
            <label for="image_id"><?php _e( 'Image', 'taxt-domain' ); ?></label>
        </th>
        <td>

            <?php $image_id = get_term_meta ( $term->term_id, 'image_id', true ); ?>
            <input type="hidden" id="image_id" name="image_id" value="<?php echo $image_id; ?>">

            <div id="image_wrapper">
            <?php if ( $image_id ) { ?>
               <?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
            <?php } ?>

            </div>

            <p>
                <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php _e( 'Add Image', 'taxt-domain' ); ?>">
                <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php _e( 'Remove Image', 'taxt-domain' ); ?>">
            </p>

        </div></td>
    </tr>
<?php
}

//Update the 'food_manager_type' taxonomy image field
add_action( 'edited_food_manager_type', 'wpfm_updated_custom_taxonomy_image_for_food_type', 10, 2 );
function wpfm_updated_custom_taxonomy_image_for_food_type ( $term_id, $tt_id ) {
    if( isset( $_POST['image_id'] ) && '' !== $_POST['image_id'] ){
        $image = $_POST['image_id'];
        update_term_meta ( $term_id, 'image_id', $image );
    } else {
        update_term_meta ( $term_id, 'image_id', '' );
    }
}

//Enqueue the wp_media library
add_action( 'admin_enqueue_scripts', 'wpfm_custom_taxonomy_load_media_for_food_type' );
function wpfm_custom_taxonomy_load_media_for_food_type() {
    if( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'food_manager_type' ) {
       return;
    }
    wp_enqueue_media();
}

//Custom script
add_action( 'admin_footer', 'wpfm_add_custom_taxonomy_script_for_food_type' );
function wpfm_add_custom_taxonomy_script_for_food_type() {
    if( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'food_manager_type' ) {
       return;
    }
    ?> <script>jQuery(document).ready( function($) {
            function taxonomy_media_upload(button_class) {
                var custom_media = true,
                original_attachment = wp.media.editor.send.attachment;
                $('body').on('click', button_class, function(e) {
                    var button_id = '#'+$(this).attr('id');
                    var send_attachment = wp.media.editor.send.attachment;
                    var button = $(button_id);
                    custom_media = true;
                    wp.media.editor.send.attachment = function(props, attachment){
                        if ( custom_media ) {
                            $('#image_id').val(attachment.id);
                            $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                            $('#image_wrapper .custom_media_image').attr('src',attachment.url).css('display','block');
                        } else {
                            return original_attachment.apply( button_id, [props, attachment] );
                        }
                    }
                    wp.media.editor.open(button);
                    return false;
                });
            }
            taxonomy_media_upload('.taxonomy_media_button.button'); 
            $('body').on('click','.taxonomy_media_remove',function(){
                $('#image_id').val('');
                $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;display:none;" />');
            });

            $(document).ajaxComplete(function(event, xhr, settings) {
                var queryStringArr = settings.data.split('&');
                if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
                    var xml = xhr.responseXML;
                    $response = $(xml).find('term_id').text();
                    if($response!=""){
                        $('#image_wrapper').html('');
                    }
                }
            });
        });</script> <?php
}

//Add new column heading
add_filter( 'manage_edit-food_manager_type_columns', 'wpfm_display_custom_taxonomy_image_column_heading_for_food_type' ); 
function wpfm_display_custom_taxonomy_image_column_heading_for_food_type( $columns ) {
    $columns['category_image'] = __( 'Image', 'taxt-domain' );
    return $columns;
}

//Display new columns values
add_action( 'manage_food_manager_type_custom_column', 'wpfm_display_custom_taxonomy_image_column_value_for_food_type' , 10, 3); 
function wpfm_display_custom_taxonomy_image_column_value_for_food_type( $columns, $column, $id ) {
    if ( 'category_image' == $column ) {
        $image_id = esc_html( get_term_meta($id, 'image_id', true) );
        
        $columns = wp_get_attachment_image ( $image_id, array('50', '50') );
    }
    return $columns;
}