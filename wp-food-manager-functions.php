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
 * Checks if the provided content or the current single page or post has a WPEM shortcode.
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
		$has_wpfm_shortcode = array( 'submit_food_form', 'food_dashboard', 'foods', 'food_categories', 'food_type', 'food', 'food_summary', 'food_apply' );
		/**
		 * Filters a list of all shortcodes associated with WPEM.
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

		'rss_link' => array(

			'name' => __( 'RSS', 'wp-food-manager' ),

			'url'  => get_food_manager_rss_link( apply_filters( 'wpfm_get_listings_custom_filter_rss_args', array(

				'search_keywords' => $args['search_keywords'],

				'search_location' => $args['search_location'],	


				'search_categories'  => implode( ',', $search_categories ),

				'search_food_types'  => implode( ',', $search_food_types),

			) ) )
		)
	), $args );

	if ( ! $args['search_keywords'] && ! $args['search_location'] && ! $args['search_categories'] && ! $args['search_food_types']  && ! apply_filters( 'wpfm_get_listings_custom_filter', false ) ) {

		unset( $links['reset'] );
	}

	$return = '';
	
	foreach ( $links as $key => $link ) {

		$return .= '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $key ) . '">' . $link['name'] . '</a>';
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

			return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s', 'wp-food-manager' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );

		} else {

			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'wp-food-manager' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
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
 * Allowed Mime types specifically for WP Event Manager.
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
function food_manager_multiselect_food_type() {
	return apply_filters( 'food_manager_multiselect_food_type', get_option( 'food_manager_multiselect_food_type' ) == 1 ? true : false );
}

/**
 * True if only one category allowed per food
 *
 * @return bool
 */
function food_manager_multiselect_food_category() {
	return apply_filters( 'food_manager_multiselect_food_category', get_option( 'food_manager_multiselect_food_category' ) == 1 ? true : false );
}

/**
 * Get the page ID of a page if set, with PolyLang compat.
 * @param  string $page e.g. food_dashboard, submit_food_form, foods
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
 * @param  string $page e.g. food_dashboard, submit_food_form, foods
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
			if ( in_array( $meta_key, apply_filters( 'food_manager_duplicate_listing_ignore_keys', array( '_cancelled', '_featured', '_event_expires', '_event_duration' ) ) ) ) {
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
 * Checks to see if the standard password setup email should be used.
 *
 * @since 1.8
 *
 * @return bool True if they are to use standard email, false to allow user to set password at first event creation.
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
				'default' => 1,
				'priority'    => 1
			),

			'enable_food_nutri' => array(
				'label'       => __( 'Enable Nutrition', 'wp-food-manager' ),
				'default'         => 1,
				'type'        => 'checkbox',
				'required'    => true,
				'placeholder' => '1',
				'priority'    => 2
			),
		)
	) );

	return $adv_fields;
}