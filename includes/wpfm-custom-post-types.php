<?php
/**
 * WPFM_Post_Types class.
 */

class WPFM_Post_Types {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.0.0
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
	 * Constructor
	 */

	public function __construct() {

		add_action( 'init', array( $this, 'register_post_types' ), 0 );

		add_filter( 'admin_head', array( $this, 'admin_head' ) );

		add_filter( 'the_content', array( $this, 'food_content' ) );

		add_filter( 'the_content', array( $this, 'food_menu_content' ) );

		add_filter( 'archive_template', array( $this, 'food_archive' ), 20 );

		add_action( 'wp_footer', array( $this, 'output_structured_data' ) );

		add_action( 'wp_head', array( $this, 'noindex_expired_cancelled_food_listings' ) );

		add_filter('use_block_editor_for_post_type', array($this,'wpfm_disable_gutenberg'), 10, 2);

		add_filter( 'wp_insert_post_data', array( $this, 'fix_post_name' ), 10, 2 );
		add_action( 'wp_insert_post', array( $this, 'maybe_add_default_meta_data' ), 10, 2 );
		
		//view count action
		add_action( 'set_single_listing_view_count', array( $this, 'set_single_listing_view_count' ));

		if (get_option('food_manager_enable_categories')) {

			add_action('restrict_manage_posts', array($this, 'foods_by_category'));
		}

		if (get_option('food_manager_enable_food_types') && get_option('food_manager_enable_categories')) {

			add_action('restrict_manage_posts', array($this, 'foods_by_food_type'));
		}

		// Admin notices.
        //add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages' ), 10, 2 );
	}

	/**
	 * register_post_types function.
	 *
	 * @access public
	 * @return void
	 */

	public function register_post_types() {

		if ( post_type_exists( "food_manager" ) )
			return;


		$admin_capability = 'manage_food_managers';
		$permalink_structure = WPFM_Post_Types::get_permalink_structure();

		include_once( WPFM_PLUGIN_DIR . '/includes/wpfm-custom-taxonomies.php' );
		
		/**
		 * Post types
		 */

		$singular  = __( 'Food', 'wp-food-manager' );

		$plural    = __( 'Foods', 'wp-food-manager' );
		
		/**
		 * Set whether to add archive page support when registering the food manager post type.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $enable_food_archive_page
		 */
		if ( apply_filters( 'food_manager_enable_food_archive_page', current_theme_supports( 'food-manager-templates' ) ) ) {
			$has_archive = _x( 'foods', 'Post type archive slug - resave permalinks after changing this', 'wp-food-manager' );
		} else {
			$has_archive = false;
		}

		$rewrite     = array(
		    
			'slug'       => $permalink_structure['food_rewrite_slug'],

			'with_front' => false,

			'feeds'      => true,

			'pages'      => false
		);

		register_post_type( "food_manager",

			apply_filters( "register_post_type_food_manager", array(

				'labels' => array(

					'name' 					=> $plural,

					'singular_name' 		=> $singular,

					'menu_name'             => __( 'Food Manager', 'wp-food-manager' ),

					'all_items'             => sprintf( wp_kses( 'All %s', 'wp-food-manager' ), $plural ),

					'add_new' 				=> __( 'Add Food', 'wp-food-manager' ),

					'add_new_item' 			=> sprintf( wp_kses( 'Add %s', 'wp-food-manager' ), $singular ),

					'edit' 					=> __( 'Edit', 'wp-food-manager' ),

					'edit_item' 			=> sprintf( wp_kses( 'Edit %s', 'wp-food-manager' ), $singular ),

					'new_item' 				=> sprintf( wp_kses( 'New %s', 'wp-food-manager' ), $singular ),

					'view' 					=> sprintf( wp_kses( 'View %s', 'wp-food-manager' ), $singular ),

					'view_item' 			=> sprintf( wp_kses( 'View %s', 'wp-food-manager' ), $singular ),

					'search_items' 			=> sprintf( wp_kses( 'Search %s', 'wp-food-manager' ), $plural ),

					'not_found' 			=> sprintf( wp_kses( 'No %s found', 'wp-food-manager' ), $plural ),

					'not_found_in_trash' 	=> sprintf( wp_kses( 'No %s found in trash', 'wp-food-manager' ), $plural ),

					'parent' 				=> sprintf( wp_kses( 'Parent %s', 'wp-food-manager' ), $singular ),
					
					'featured_image'        => __( 'Food Thumbnail', 'wp-food-manager' ),
					
					'set_featured_image'    => __( 'Set food thumbnail', 'wp-food-manager' ),
					
					'remove_featured_image' => __( 'Remove food thumbnail', 'wp-food-manager' ),
					
					'use_featured_image'    => __( 'Use as food thumbnail', 'wp-food-manager' ),
				),

				'description' => sprintf( wp_kses( 'This is where you can create and manage %s.', 'wp-food-manager' ), $plural ),

				'public' 				=> true,

				'show_ui' 				=> true,

				'capability_type' 		=> 'post',

				'map_meta_cap'          => true,

				'publicly_queryable' 	=> true,

				'exclude_from_search' 	=> false,

				'hierarchical' 			=> false,

				'rewrite' 				=> $rewrite,

				'query_var' 			=> true,
					
				'show_in_rest' 			=> true,

				'supports' 				=> array( 'title', 'editor', 'custom-fields', 'publicize' , 'thumbnail'),

				'has_archive' 			=> $has_archive,

				'show_in_nav_menus' 	=> true,				

				'menu_icon' => WPFM_PLUGIN_URL . '/assets/images/wpfm-icon.png' // It's use to display food manager icon at admin site. 
			) )
		);

		/**
		 * Feeds
		 */

		add_feed( 'food_feed', array( $this, 'food_feed' ) );

		/**
		 * Post types
		 */

		$singular_menu  = __( 'Menu', 'wp-food-manager' );

		$plural_menu    = __( 'Menus', 'wp-food-manager' );

		$rewrite_menu     = array(
		    
			'slug'       => 'food-menu',

			'with_front' => false,

			'feeds'      => true,

			'pages'      => true
		);

		register_post_type( "food_manager_menu",

			apply_filters( "register_post_type_food_manager_menu", array(

				'labels' => array(

					'name' 					=> $plural_menu,

					'singular_name' 		=> $singular_menu,

					'menu_name'             => __( 'Food Menu', 'wp-food-manager' ),

					'all_items'             => sprintf( __( '%s', 'wp-food-manager' ), $plural_menu ),

					'add_new' 				=> __( 'Add New', 'wp-food-manager' ),

					'add_new_item' 			=> sprintf( __( 'Add %s', 'wp-food-manager' ), $singular_menu ),

					'edit' 					=> __( 'Edit', 'wp-food-manager' ),

					'edit_item' 			=> sprintf( __( 'Edit %s', 'wp-food-manager' ), $singular_menu ),

					'new_item' 				=> sprintf( __( 'New %s', 'wp-food-manager' ), $singular_menu ),

					'view' 					=> sprintf( __( 'View %s', 'wp-food-manager' ), $singular_menu ),

					'view_item' 			=> sprintf( __( 'View %s', 'wp-food-manager' ), $singular_menu ),

					'search_items' 			=> sprintf( __( 'Search %s', 'wp-food-manager' ), $plural_menu ),

					'not_found' 			=> sprintf( __( 'No %s found', 'wp-food-manager' ), $plural_menu ),

					'not_found_in_trash' 	=> sprintf( __( 'No %s found in trash', 'wp-food-manager' ), $plural_menu ),

					'parent' 				=> sprintf( __( 'Parent %s', 'wp-food-manager' ), $singular_menu ),
					
					'featured_image'        => __( 'Food Menu Image', 'wp-food-manager' ),
					
					'set_featured_image'    => __( 'Add Image', 'wp-food-manager' ),
					
					'remove_featured_image' => __( 'Remove Image', 'wp-food-manager' ),
					
					'use_featured_image'    => __( 'Use as food thumbnail', 'wp-food-manager' ),
				),

				'description' => sprintf( __( 'This is where you can create and manage %s.', 'wp-food-manager' ), $plural_menu ),

				'public' 				=> true,

				'show_ui' 				=> true,

				//'capability_type' 		=> 'food_manager',

				'map_meta_cap'          => true,

				'publicly_queryable' 	=> true,

				'exclude_from_search' 	=> false,

				'hierarchical' 			=> false,

				'rewrite' 				=> $rewrite_menu,

				'query_var' 			=> true,
					
				'show_in_rest' 			=> true,

				'supports' 				=> array( 'title', 'thumbnail', 'publicize'), //'editor', 'custom-fields'

				'has_archive' 			=> true,

				'show_in_menu' => 'edit.php?post_type=food_manager'

				//'menu_icon' => 'dashicons-carrot' // It's use to display food manager icon at admin site. 
			) )
		);

		/**
		 * Post status
		 */
		register_post_status( 'preview', array(

			'public'                    => true,

			'exclude_from_search'       => true,

			'show_in_admin_all_list'    => true,

			'show_in_admin_status_list' => true,
	
			'label_count'               => _n_noop( 'Preview <span class="count">(%s)</span>', 'Preview <span class="count">(%s)</span>', 'wp-food-manager' )
		) );

			

	}

	/**
	 * Show category dropdown
	 */
	public function foods_by_category()
	{

		global $typenow, $wp_query;

		if ($typenow != 'food_manager' || !taxonomy_exists('food_manager_category')) {

			return;
		}

		include_once WPFM_PLUGIN_DIR . '/includes/wpfm-category-walker.php';

		$r = array();

		$r['pad_counts'] = 1;

		$r['hierarchical'] = 1;

		$r['hide_empty'] = 0;

		$r['show_count'] = 1;

		$r['selected'] = (isset($wp_query->query['food_manager_category'])) ? $wp_query->query['food_manager_category'] : '';

		$r['menu_order'] = false;

		$terms = get_terms('food_manager_category', $r);

		$walker = new WPFM_Category_Walker();

		if (!$terms) {

			return;
		}

		$output = "<select name='food_manager_category' id='dropdown_food_manager_category'>";

		$output .= '<option value="" ' . selected(isset($_GET['food_manager_category']) ? $_GET['food_manager_category'] : '', '', false) . '>' . __('Select Food Category', 'wp-food-manager') . '</option>';

		$output .= $walker->walk($terms, 0, $r);

		$output .= '</select>';

		printf($output);
	}

	/**
	 * Show Food type dropdown
	 */
	public function foods_by_food_type()
	{
		global $typenow, $wp_query;

		if ($typenow != 'food_manager' || !taxonomy_exists('food_manager_type')) {
			return;
		}

		$r                 = array();
		$r['pad_counts']   = 1;
		$r['hierarchical'] = 1;
		$r['hide_empty']   = 0;
		$r['show_count']   = 1;
		$r['selected']     = (isset($wp_query->query['food_manager_type'])) ? $wp_query->query['food_manager_type'] : '';
		$r['menu_order']   = false;
		$terms             = get_terms('food_manager_type', $r);
		$walker            = new WPFM_Category_Walker();

		if (!$terms) {
			return;
		}

		$output  = "<select name='food_manager_type' id='dropdown_food_manager_type'>";
		$output .= '<option value="" ' . selected(isset($_GET['food_manager_type']) ? $_GET['food_manager_type'] : '', '', false) . '>' . __('Select Food Type', 'wp-food-manager') . '</option>';
		$output .= $walker->walk($terms, 0, $r);
		$output .= '</select>';

		printf($output);
	}

	/**
	 * Change label
	 */

	public function admin_head() {

		global $menu;

		$plural     = __( 'Food Manager', 'wp-food-manager' );

		$count_foods = wp_count_posts( 'food_manager', 'readable' );
		
		if ( ! empty( $menu ) && is_array( $menu ) ) {

			foreach ( $menu as $key => $menu_item ) {

				if ( strpos( $menu_item[0], $plural ) === 0 ) {

					if ( $order_count = $count_foods->pending ) {

						$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-$order_count'><span class='pending-count'>" . number_format_i18n( $count_foods->pending ) . "</span></span>" ;
					}

					break;
				}
			}
		}
	}

	/**
	 * Add extra content when showing food content
	 */
	public function food_content( $content ) {

		global $post;

		if ( ! is_singular( 'food_manager' ) || ! in_the_loop() ) {

			return $content;
		}

		remove_filter( 'the_content', array( $this, 'food_content' ) );

		if ( 'food_manager' === $post->post_type ) {

			ob_start();

			do_action( 'food_content_start' );

			get_food_manager_template_part( 'content-single', 'food_manager' );

			do_action( 'food_content_end' );

			$content = ob_get_clean();
		}

		add_filter( 'the_content', array( $this, 'food_content' ) );

		return apply_filters( 'food_manager_single_food_content', $content, $post );
	}

	/**
	 * food_archive function.
	 *
	 * @access public
	 * @return void
	 */
	public function food_archive($template) 
	{
		if ( is_tax( 'food_manager_category' ) ) {

			$template = WPFM_PLUGIN_DIR . '/templates/content-food_listing_category.php';
	    }
	    elseif ( is_tax( 'food_manager_type' ) ) {

			$template = WPFM_PLUGIN_DIR . '/templates/content-food_listing_type.php';
	    }
	    elseif ( is_tax( 'food_manager_tag' ) ) {

			$template = WPFM_PLUGIN_DIR . '/templates/content-food_listing_tag.php';
	    }

	    return $template;
	}

	/**
	 * Add Food menu content
	 */
	public function food_menu_content( $content ) {

		global $post;

		if ( ! is_singular( 'food_manager_menu' ) || ! in_the_loop() ) {

			return $content;
		}

		remove_filter( 'the_content', array( $this, 'food_menu_content' ) );

		if ( 'food_manager_menu' === $post->post_type ) {

			ob_start();

			do_action( 'food_menu_content_start' );

			get_food_manager_template_part( 'content-single', 'food_manager_menu' );

			do_action( 'food_menu_content_end' );

			$content = ob_get_clean();
		}

		add_filter( 'the_content', array( $this, 'food_menu_content' ) );

		return apply_filters( 'food_manager_single_food_menu_content', $content, $post );
	}

	/**
	 * Add extra content when showing organizer content
	 */
	public function nutritions_content( $content ) {

		global $post;

		if ( ! is_singular( 'food_nutritions' ) || ! in_the_loop() ) 
		{
			return $content;
		}

		remove_filter( 'the_content', array( $this, 'nutritions_content' ) );

		if ( 'food_nutritions' === $post->post_type ) {

			ob_start();

			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $per_page = 10;
            $today_date=date("Y-m-d");
            $nutritions_id = get_the_ID();
            $show_pagination = true;

            $args_upcoming = array(
                'post_type'   => 'food_manager',
                'post_status' => 'publish',
                'posts_per_page' => $per_page,                                              
                'paged' => $paged
            );

            $args_upcoming['meta_query'] = array( 
                'relation' => 'AND', 
                array(
                        'key'     => '_food_nutritions_ids',
                        'value'   => $nutritions_id, 
                        'compare' => 'LIKE',
                    )
            );

            $upcomingEvents = new WP_Query( $args_upcoming );
            wp_reset_query();

            $args_current = $args_upcoming;
            
            $args_current['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key'     => '_food_nutritions_ids',
                    'value'   => $nutritions_id, 
                    'compare' => 'LIKE',
                )
            );

            $currentEvents = new WP_Query( $args_current );
            wp_reset_query();

            $args_past = array(
                'post_type'   => 'food_manager',
                'post_status' => array('expired', 'publish'),
                'posts_per_page' => $per_page,
                'paged' => $paged
            );
        
            $args_past['meta_query'] = array( 
                'relation' => 'AND', 
                array(
                    'key'     => '_food_organizer_ids',
                    'value'   => $nutritions_id, 
                    'compare' => 'LIKE',
                ),
                array(
                    'key'     => '_food_end_date',
                    'value'   => $today_date,
                    'type'    => 'date',
                    'compare' => '<'   
                )
            );
            $pastEvents = new WP_Query( $args_past );
            wp_reset_query();

			do_action( 'organizer_content_start' );

			wp_enqueue_script( 'wp-food-manager-organizer');

			get_food_manager_template( 
			    'content-single-food_organizer.php', 
			    array(
			        'organizer_id'	=> $nutritions_id,
			        'per_page'		=> $per_page,
			        'show_pagination'	=> $show_pagination,
			        'upcomingEvents' => $upcomingEvents,
			        'currentEvents' => $currentEvents,
			        'pastEvents' 	=> $pastEvents,
			    ), 
			    'wp-food-manager', 
			    EVENT_MANAGER_PLUGIN_DIR . '/templates/organizer/'
			);

			wp_reset_postdata();
			//get_food_manager_template_part( 'content-single', 'food_organizer', 'wp-food-manager', EVENT_MANAGER_PLUGIN_DIR . '/templates/organizer/');

			do_action( 'organizer_content_end' );

			$content = ob_get_clean();
		}

		add_filter( 'the_content', array( $this, 'organizer_content' ) );

		return apply_filters( 'food_manager_single_organizer_content', $content, $post );
	}

	/**
	 * Food listing feeds
	 */

	public function food_feed() {

		$query_args = array(

			'post_type'           => 'food_manager',

			'post_status'         => 'publish',

			'ignore_sticky_posts' => 1,

			'posts_per_page'      => isset( $_GET['posts_per_page'] ) ? absint( $_GET['posts_per_page'] ) : 10,

			'tax_query'           => array(),

			'meta_query'          => array()
		);		

		/*if ( ! empty( $_GET['search_location'] ) ) {

			$location_meta_keys = array( 'geolocation_formatted_address', '_food_location', 'geolocation_state_long' );

			$location_search    = array( 'relation' => 'OR' );

			foreach ( $location_meta_keys as $meta_key ) {

				$location_search[] = array(

					'key'     => $meta_key,

					'value'   => sanitize_text_field( $_GET['search_location'] ),

					'compare' => 'like'
				);
			}
			
			$query_args['meta_query'][] = $location_search;
		}*/
		
		/*if ( ! empty( $_GET['search_datetimes'] ) ) 
		{
			if($_GET['search_datetimes'] == 'datetime_today')
			{	
				$datetime=date('Y-m-d');
				
				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $datetime,
						'compare' => 'LIKE',
					);
			}
			elseif( $_GET['search_datetimes'] == 'datetime_tomorrow' )
			{ 
				$datetime=date('Y-m-d',strtotime("+1 day")); 
				
				$date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $datetime,
						'compare' => 'LIKE',
					);
			}
			elseif( $_GET['search_datetimes'] == 'datetime_thisweek')
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
			elseif( $_GET['search_datetimes'] =='datetime_thisweekend' )
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
			elseif( $_GET['search_datetimes'] =='datetime_thismonth')
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
			elseif( $_GET['search_datetimes'] =='datetime_thisyear')
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
			elseif( $_GET['search_datetimes'] =='datetime_nextweek')
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
			elseif( $_GET['search_datetimes'] =='datetime_nextweekend')
			{
				$next_saturday_date=date('Y-m-d', strtotime('next Saturday', time()));
				$next_sunday_date=date('Y-m-d', strtotime('next Saturday +1 day', time()));
                $dates[0]= $next_saturday_date;
                $dates[1]= $next_sunday_date;               
                
			    $date_search[] = array(
						'key'     => '_food_start_date',
						'value'   => $dates,
					    'compare' => 'BETWEEN',
					    'type'    => 'date'
					);
			} 
			elseif( $_GET['search_datetimes'] =='datetime_nextmonth')
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
			elseif( $_GET['search_datetimes'] =='datetime_nextyear')
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
		}*/
		
		/*if ( ! empty( $_GET['search_ticket_prices'] ) ) {
		    
			if($_GET['search_ticket_prices'] =='ticket_price_paid')
			{  
			  $ticket_price_value='paid';     
			}
			else if ( $_GET['search_ticket_prices'] =='ticket_price_free')
			{
			  $ticket_price_value='free';
			}
			$ticket_search[] = array(
							'key'     => '_food_ticket_options',
							'value'   => $ticket_price_value,
							'compare' => '=',
						);
			$query_args['meta_query'][] = $ticket_search;			
		}*/
		
		if ( ! empty( $_GET['search_food_types'] ) ) {
		    
			$cats     = explode( ',', sanitize_text_field( $_GET['search_food_types'] ) ) + array( 0 );

			$field    = is_numeric( $cats ) ? 'term_id' : 'slug';

			$operator = 'all' === get_option( 'food_manager_food_type_filter_type', 'all' ) && sizeof( $args['search_food_types'] ) > 1 ? 'AND' : 'IN';

			$query_args['tax_query'][] = array(

				'taxonomy'         => 'food_manager_type',

				'field'            => $field,

				'terms'            => $cats,

				'include_children' => $operator !== 'AND' ,

				'operator'         => $operator
			);
		}
		
		if ( ! empty( $_GET['search_categories'] ) ) {

			$cats     = explode( ',', sanitize_text_field( $_GET['search_categories'] ) ) + array( 0 );

			$field    = is_numeric( $cats ) ? 'term_id' : 'slug';

			$operator = 'all' === get_option( 'food_manager_category_filter_type', 'all' ) && sizeof( $args['search_categories'] ) > 1 ? 'AND' : 'IN';

			$query_args['tax_query'][] = array(

				'taxonomy'         => 'food_manager_category',

				'field'            => $field,

				'terms'            => $cats,

				'include_children' => $operator !== 'AND' ,

				'operator'         => $operator
			);
		}
		if ( $food_manager_keyword = sanitize_text_field( $_GET['search_keywords'] ) ) {

			$query_args['s'] = $food_manager_keyword;
			
			add_filter( 'posts_search', 'get_food_listings_keyword_search' );
		}
		
		if ( empty( $query_args['meta_query'] ) ) {

			unset( $query_args['meta_query'] );
		}

		if ( empty( $query_args['tax_query'] ) ) {

			unset( $query_args['tax_query'] );
		}

		query_posts( apply_filters( 'food_feed_args', $query_args ) );

		add_action( 'rss2_ns', array( $this, 'food_feed_namespace' ) );

		add_action( 'rss2_item', array( $this, 'food_feed_item' ) );

		do_feed_rss2( false );
		remove_filter( 'posts_search', 'get_food_listings_keyword_search' );
	}
	
	/**
	 * In order to make sure that the feed properly queries the 'food_listing' type
	 *
	 * @param WP_Query $wp
	 */
	public function add_feed_query_args( $wp ) {
		
		// Let's leave if not the food feed
		if ( ! isset( $wp->query_vars['feed'] ) || 'food_feed' !== $wp->query_vars['feed'] ) {
			return;
		}
		
		// Leave if not a feed.
		if ( false === $wp->is_feed ) {
			return;
		}
		
		// If the post_type was already set, let's get out of here.
		if ( isset( $wp->query_vars['post_type'] ) && ! empty( $wp->query_vars['post_type'] ) ) {
			return;
		}
		
		$wp->query_vars['post_type'] = 'food_manager';
	}
	

	/**
	 * Add a custom namespace to the food feed
	 */

	public function food_feed_namespace() {

		echo 'xmlns:food_manager="' .  site_url() . '"' . "\n";
	}

	/**
	 * Add custom data to the food feed
	 */

	public function food_feed_item() {

		$post_id  = get_the_ID();
		get_food_manager_template( 'rss-food-feed.php', array( 'post_id' => $post_id ) );
	}

	/**
	 * Expire foods
	 */

	// public function check_for_expired_foods() {

	// 	global $wpdb;
		
	// 	// Change status to expired

	// 	$food_ids = $wpdb->get_col( $wpdb->prepare( "

	// 		SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta

	// 		LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID

	// 		WHERE postmeta.meta_key = '_food_expiry_date'

	// 		AND postmeta.meta_value > 0

	// 		AND postmeta.meta_value < %s

	// 		AND posts.post_status = 'publish'

	// 		AND posts.post_type = 'food_listing'

	// 	", date( 'Y-m-d', current_time( 'timestamp' ) ) ) );

	// 	if ( $food_ids ) {

	// 		foreach ( $food_ids as $food_id ) {

	// 			$food_data       = array();

	// 			$food_data['ID'] = $food_id;

	// 			$food_data['post_status'] = 'expired';

	// 			wp_update_post( $food_data );
	// 		}
	// 	}
		
	// 	// Delete old expired foods	
	// 	$return_flag=absint( get_option( 'food_manager_delete_expired_foods' ) ) == 1 ? true : false;
	// 	if ( apply_filters( 'food_manager_delete_expired_foods', $return_flag ) ) {

	// 		$food_ids = $wpdb->get_col( $wpdb->prepare( "

	// 			SELECT posts.ID FROM {$wpdb->posts} as posts

	// 			WHERE posts.post_type = 'food_listing'

	// 			AND posts.post_modified < %s

	// 			AND posts.post_status = 'expired'

	// 		", date( 'Y-m-d', strtotime( '-' . apply_filters( 'food_manager_delete_expired_foods_days', 30 ) . ' days', current_time( 'timestamp' ) ) ) ) );
			
	// 		if ( $food_ids ) {

	// 			foreach ( $food_ids as $food_id ) {

	// 				wp_trash_post( $food_id );
	// 			}
	// 		}
	// 	}

	// 	//Delete food after finished
	// 	$delete_foods_after_finished = absint( get_option( 'food_manager_delete_foods_after_finished' ) ) == 1 ? true : false;
	// 	if($delete_foods_after_finished)
	// 	{
	// 		$args = [
	// 			'post_type'      => 'food_listing',
	// 			'post_status'    => array( 'publish', 'expired' ),
	// 			'posts_per_page' => -1,
	// 			'meta_query' => array(
	// 		        'relation' => 'AND',
	// 		        array(
	// 		            'key'     => '_food_end_date',
	// 		            'value'   => date( 'Y-m-d'),
	// 		            'compare' => '<=',
	// 		        ),
	// 		        array(
	// 		            'key'     => '_food_end_time',
	// 		            'value'   => date( 'H:i A'),
	// 		            'compare' => '<',
	// 		        ),
	// 		    ),
	// 		];

	// 		$food_ids = get_posts($args);

	// 		if ( $food_ids ) 
	// 		{
	// 			foreach ( $food_ids as $food_id ) 
	// 			{
	// 				$food_data       = array();

	// 				$food_data['ID'] = $food_id->ID;

	// 				$food_data['post_status'] = 'expired';

	// 				wp_update_post( $food_data );

	// 				wp_trash_post( $food_id->ID );
	// 			}
	// 		}
	// 	}

	// }
	
	/**
	 * Delete old previewed foods after 30 days to keep the DB clean
	 */

	// public function delete_old_previews() {

	// 	global $wpdb;

	// 	// Delete old expired foods

	// 	$food_ids = $wpdb->get_col( $wpdb->prepare( "

	// 		SELECT posts.ID FROM {$wpdb->posts} as posts

	// 		WHERE posts.post_type = 'food_listing'

	// 		AND posts.post_modified < %s

	// 		AND posts.post_status = 'preview'

	// 	", date( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) ) ) );

	// 	if ( $food_ids ) {

	// 		foreach ( $food_ids as $food_id ) {

	// 			wp_delete_post( $food_id, true );
	// 		}
	// 	}
	// }

	/**
	 * Set expirey date when food status changes
	 */

	// public function set_food_expiry_date( $post ) {
	// 	if ( $post->post_type !== 'food_listing' ) {
	// 		return;
	// 	}
	// 	// See if it is already set
	// 	if ( metadata_exists( 'post', $post->ID, '_food_expiry_date' ) ) {
			
	// 		$expires = get_post_meta( $post->ID, '_food_expiry_date', true );
			
	// 		if ( $expires && strtotime( $expires ) < current_time( 'timestamp' ) ) {
				
	// 			update_post_meta( $post->ID, '_food_expiry_date', '' );
	// 		}
	// 	}
		
	// 	// No metadata set so we can generate an expiry date
	// 	// See if the user has set the expiry manually:
	// 	if ( ! empty( $_POST[ '_food_expiry_date' ] ) ) {
	// 		update_post_meta( $post->ID, '_food_expiry_date', date( 'Y-m-d', strtotime( sanitize_text_field( $_POST[ '_food_expiry_date' ] ) ) ) );
	// 		// No manual setting? Lets generate a date
	// 	} elseif (false == isset( $expires ) ){
	// 		$expires = get_food_expiry_date( $post->ID );
	// 		update_post_meta( $post->ID, '_food_expiry_date', $expires );
	// 		// In case we are saving a post, ensure post data is updated so the field is not overridden
	// 		if ( isset( $_POST[ '_food_expiry_date' ] ) ) {
				
	// 			$_POST[ '_food_expiry_date' ] = $expires;
	// 		}
	// 	}
	// }

	    /**
	    * Set post view on the single listing page
	    * @param  array $post	 
	    */

	    function set_single_listing_view_count($post) 
	    {     
	    	global $post; 
	       //get the user role. 
		    if ( is_user_logged_in() ) 
		     {
			     $role=get_food_manager_current_user_role();  

		         $current_user = wp_get_current_user();

			  if ( $role !='Administrator' && ($post->post_author!=$current_user->ID ) )
	                  { 
	                   	$this->set_post_views($post->ID);
	                  }   		 
		      }
		      else
		      {			  
			  $this->set_post_views($post->ID);
		      }        
	    }

    /**
	 * This function is use to set the counts the food views and attendees views.
     * This function also used at attendees dashboard file.
	 * @param  int $post_id	 
	*/

	public function set_post_views($post_id) 
    {
		    $count_key = '_view_count';
            $count = get_post_meta($post_id, $count_key, true);

            if($count=='' || $count==null)
            {
                $count = 0;
                delete_post_meta($post_id, $count_key);
                add_post_meta($post_id, $count_key, '0');
            }
            else
            {
                $count++;
                update_post_meta($post_id, $count_key, $count);
            }
	}
	
	/**
	 * The registration content when the registration method is an email
	 */
	public function registration_details_email( $register ) {
		get_food_manager_template( 'food-registration-email.php', array( 'register' => $register ) );
	}

	/**
	 * The registration content when the registration method is a url
	 */
	public function registration_details_url( $register ) {
		get_food_manager_template( 'food-registration-url.php', array( 'register' => $register ) );
	}

	/**
	 * Fix post name when wp_update_post changes it
	 * @param  array $data
	 * @return array
	 */

	public function fix_post_name( $data, $postarr ) {

		 if ( 'food_manager' === $data['post_type'] && 'pending' === $data['post_status'] && ! current_user_can( 'publish_posts' ) ) {

				$data['post_name'] = $postarr['post_name'];
		 }
		 return $data;
	}

	/**
	 * Generate location data if a post is added
	 * @param  int $post_id
	 * @param  array $post
	 */

	public function maybe_add_geolocation_data( $object_id, $meta_key, $_meta_value ) {

		if ( '_food_location' !== $meta_key || 'food_manager' !== get_post_type( $object_id ) ) {

			return;
		}
		do_action( 'food_manager_food_location_edited', $object_id, $_meta_value );
	}

	/**
	 * Triggered when updating meta on a food listing.
	 *
	 * @param int    $meta_id
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 */
	public function update_post_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( 'food_manager' === get_post_type( $object_id ) ) {
			switch ( $meta_key ) {
				case '_food_location':
					$this->maybe_update_geolocation_data( $meta_id, $object_id, $meta_key, $meta_value );
					break;
				case '_featured':
					$this->maybe_update_menu_order( $meta_id, $object_id, $meta_key, $meta_value );
					break;
			}
		}
	}


	
	/**
	 * Generate location data if a post is updated
	 */

	public function maybe_update_geolocation_data( $meta_id, $object_id, $meta_key, $_meta_value ) {

		if ( '_food_location' !== $meta_key || 'food_manager' !== get_post_type( $object_id ) ) {
		    
			return;
		}
		do_action( 'food_manager_food_location_edited', $object_id, $_meta_value );
	}

	/**
	 * Maybe set menu_order if the featured status of a food is changed
	 */

	public function maybe_update_menu_order( $meta_id, $object_id, $meta_key, $_meta_value ) {

		if ( '_featured' !== $meta_key || 'food_manager' !== get_post_type( $object_id ) ) {

			return;
		}

		global $wpdb;

		if ( '1' == $_meta_value ) {

			$wpdb->update( $wpdb->posts, array( 'menu_order' => -1 ), array( 'ID' => $object_id ) );

		} else {

			$wpdb->update( $wpdb->posts, array( 'menu_order' => 0 ), array( 'ID' => $object_id, 'menu_order' => -1 ) );
		}
		
		clean_post_cache( $object_id );
	}

	/**
	 * 
	 */

	public function maybe_generate_geolocation_data( $meta_id, $object_id, $meta_key, $_meta_value ) {

		$this->maybe_update_geolocation_data( $meta_id, $object_id, $meta_key, $_meta_value );
	}

	/**
	 * Maybe set default meta data for food listings
	 * @param  int $post_id
	 * @param  WP_Post $post
	*/

	public function maybe_add_default_meta_data( $post_id, $post = '' ) {

		if ( empty( $post ) || 'food_manager' === $post->post_type ) {

			add_post_meta( $post_id, '_cancelled', 0, true );

			add_post_meta( $post_id, '_featured', 0, true );
		}
	}

	/**
	 * After importing via WP ALL Import, add default meta data
	 * @param  int $post_id
	 */

	public function pmxi_saved_post( $post_id ) {

		if ( 'food_manager' === get_post_type( $post_id ) ) {

			$this->maybe_add_default_meta_data( $post_id );

			if ( ! WP_Food_Manager_Geocode::has_location_data( $post_id ) && ( $location = get_post_meta( $post_id, '_food_location', true ) ) ) {

				WP_Food_Manager_Geocode::generate_location_data( $post_id, $location );
			}
		}
	}

	
	/**
	 * When deleting a food, delete its attachments
	 * @param  int $post_id
	 */
	public function before_delete_food( $post_id ) {
    	if ( 'food_manager' === get_post_type( $post_id ) ) {
			$attachments = get_children( array(
		        'post_parent' => $post_id,
		        'post_type'   => 'attachment'
		    ) );

			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
					wp_delete_attachment( $attachment->ID );
					@unlink( get_attached_file( $attachment->ID ) );
				}
			}
		}
	}
	
	/**
	 * Add noindex for expired and filled food listings.
	 */
	public function noindex_expired_cancelled_food_listings() {
		if ( ! is_single() ) {
			return;
		}
		$post = get_post();
		if ( ! $post || 'food_manager' !== $post->post_type ) {
			return;
		}
		if ( food_manager_allow_indexing_food_listing() ) {
			return;
		}
		wp_no_robots();
	}
	
	/**
	 * output_structured_data
	 * @since 1.8
	 */
	public function output_structured_data() {
		if ( ! is_single() ) {
			return;
		}
		if ( ! food_manager_output_food_listing_structured_data() ) {
			return;
		}
		$structured_data = food_manager_get_food_listing_structured_data();
		if ( ! empty( $structured_data ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $structured_data ) . '</script>';
		}
	}

	/**
	 * Retrieves permalink settings.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/3.0.8/includes/wc-core-functions.php#L1573
	 * @since 2.5
	 * @return array
	 */
	public static function get_permalink_structure() {
		// Switch to the site's default locale, bypassing the active user's locale.
		if ( function_exists( 'switch_to_locale' ) && did_action( 'admin_init' ) ) {
			switch_to_locale( get_locale() );
		}

		$permalinks = wp_parse_args(
			(array) get_option( 'wpfm_permalinks', array() ),
			array(
				'food_base'      => '',
				'category_base' => '',
				'type_base'     => '',
			)
		);

		// Ensure rewrite slugs are set.
		$permalinks['food_rewrite_slug']      = untrailingslashit( empty( $permalinks['food_base'] ) ? _x( 'food', 'Food permalink - resave permalinks after changing this', 'wp-food-manager' ) : $permalinks['food_base'] );
		$permalinks['category_rewrite_slug'] = untrailingslashit( empty( $permalinks['category_base'] ) ? _x( 'food-category', 'Food category slug - resave permalinks after changing this', 'wp-food-manager' ) : $permalinks['category_base'] );
		$permalinks['type_rewrite_slug']     = untrailingslashit( empty( $permalinks['type_base'] ) ? _x( 'food-type', 'Food type slug - resave permalinks after changing this', 'wp-food-manager' ) : $permalinks['type_base'] );

		// Restore the original locale.
		if ( function_exists( 'restore_current_locale' ) && did_action( 'admin_init' ) ) {
			restore_current_locale();
		}
		return $permalinks;
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 *
	 * @param  array $bulk_messages Array of messages.
	 * @param  array $bulk_counts Array of how many objects were updated.
	 * @since 3.18
	 * @return array
	 */
	public function bulk_post_updated_messages($bulk_messages, $bulk_counts) {

		$bulk_messages['food_manager'] = array(
			/* translators: %s: product count */
			'updated'   => _n( '%s event updated.', '%s foods updated.', $bulk_counts['updated'], 'wp-food-manager' ),
			/* translators: %s: product count */
			'locked'    => _n( '%s event not updated, somebody is editing it.', '%s foods not updated, somebody is editing them.', $bulk_counts['locked'], 'wp-food-manager' ),
			/* translators: %s: product count */
			'deleted'   => _n( '%s event permanently deleted.', '%s foods permanently deleted.', $bulk_counts['deleted'], 'wp-food-manager' ),
			/* translators: %s: product count */
			'trashed'   => _n( '%s event moved to the Trash.', '%s foods moved to the Trash.', $bulk_counts['trashed'], 'wp-food-manager' ),
			/* translators: %s: product count */
			'untrashed' => _n( '%s event restored from the Trash.', '%s foods restored from the Trash.', $bulk_counts['untrashed'], 'wp-food-manager' ),
		);

		return $bulk_messages;
	}

/**
* 
* @param boolean
* @param string
* @since 1.0.0
*/
public function wpfm_disable_gutenberg($is_enabled, $post_type) {
	if (apply_filters('wpfm_disable_gutenberg',true) && $post_type === 'food_manager') return false; 
		return $is_enabled;
	}
}
