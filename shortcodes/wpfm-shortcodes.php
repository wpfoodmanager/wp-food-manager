<?php
/*
* This file is use to create a sortcode of wp food manager plugin. 
* This file include sortcode of food listing,food submit form and food dashboard etc.
*/
?>
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * WP_food_Manager_Shortcodes class.
 */

class WPFM_Shortcodes {

	private $food_dashboard_message = '';
	private $neutrition_dashboard_message = '';
	private $venue_dashboard_message = '';
	
	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'shortcode_action_handler' ) );
		add_shortcode( 'submit_food_form', array( $this, 'submit_food_form' ) );
		add_shortcode( 'food_dashboard', array( $this, 'food_dashboard' ) );
		add_shortcode( 'foods', array( $this, 'output_foods' ) );
		add_shortcode( 'food_categories', array( $this, 'output_foods_categories' ) );
		add_shortcode( 'food_type', array( $this, 'output_foods_type' ) );
		add_shortcode( 'food_menu', array( $this, 'output_food_menu' ) );
	}

	/**
	 * Handle actions which need to be run before the shortcode e.g. post actions
	 */
	public function shortcode_action_handler() {

		global $post;
		
		if ( is_page() && strstr( $post->post_content, '[food_dashboard' ) ) {
			$this->food_dashboard_handler();
		}
		elseif ( is_page() && strstr( $post->post_content, '[neutritions_dashboard' )) {
			$this->neutritions_dashboard_handler();
		}
		elseif ( is_page() && strstr( $post->post_content, '[ingredients_dashboard' )) {
			$this->ingredients_dashboard_handler();
		}
	}
	
	/**
	 * Show the food submission form
	*/
	public function submit_food_form( $atts = array() ) {
		

		return $GLOBALS['food_manager']->forms->get_form( 'submit-food', $atts );
	}
	
	/**
	 * Show the organizer submission form
	 */
	public function submit_organizer_form( $atts = array() ) {

		return $GLOBALS['food_manager']->forms->get_form( 'submit-neutritions', $atts );
	}


	/**
	 * Handles actions on food dashboard
	 */
	public function food_dashboard_handler() {

		if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'food_manager_my_food_actions' ) ) {

			$action = sanitize_title( $_REQUEST['action'] );

			$food_id = absint( $_REQUEST['food_id'] );

			try {

				// Get food

				$food    = get_post( $food_id );

				// Check ownership

				if ( ! food_manager_user_can_edit_food( $food_id ) ) {

					throw new Exception( __( 'Invalid ID', 'wp-food-manager' ) );
				}

				switch ( $action ) {

					case 'mark_cancelled' :

						// Check status

						if ( $food->_cancelled == 1 )

							throw new Exception( __( 'This food has already been cancelled', 'wp-food-manager' ) );

						// Update

						update_post_meta( $food_id, '_cancelled', 1 );

						// Message

						$this->food_dashboard_message = '<div class="food-manager-message wpfm-alert wpfm-alert-success">' . sprintf( __( '%s has been cancelled', 'wp-food-manager' ), esc_html( $food->post_title ) ) . '</div>';

						break;

					case 'mark_not_cancelled' :

						// Check status
						if ( $food->_cancelled != 1 ) {

							throw new Exception( __( 'This food is not cancelled', 'wp-food-manager' ) );

						}

						// Update
						update_post_meta( $food_id, '_cancelled', 0 );
						
						// Message
						$this->food_dashboard_message = '<div class="food-manager-message wpfm-alert wpfm-alert-success">' . sprintf( __( '%s has been marked as not cancelled', 'wp-food-manager' ), esc_html( $food->post_title ) ) . '</div>';

						break;

					case 'delete' :

						$foods_status = get_post_status($food_id);

						// Trash it
						wp_trash_post( $food_id );

						// Message
						if (!in_array($foods_status, ['trash'])) {
							$this->food_dashboard_message = '<div class="food-manager-message wpfm-alert wpfm-alert-danger">' . sprintf( __( '%s has been deleted', 'wp-food-manager' ), esc_html( $food->post_title ) ) . '</div>';
						}

						break;
					case 'duplicate' :
						if ( ! food_manager_get_permalink( 'submit_food_form' ) ) {
							throw new Exception( __( 'Missing submission page.', 'wp-food-manager' ) );
						}
					
						$new_food_id = food_manager_duplicate_listing( $food_id );
					
						if ( $new_food_id ) {
							wp_redirect( add_query_arg( array( 'food_id' => absint( $new_food_id ) ), food_manager_get_permalink( 'submit_food_form' ) ) );
							exit;
						}
					
					break;

					case 'relist' :

						// redirect to post page

						wp_redirect( add_query_arg( array( 'food_id' => absint( $food_id ) ), food_manager_get_permalink( 'submit_food_form' ) ) );

						break;

					default :

						do_action( 'food_manager_food_dashboard_do_action_' . $action );

						break;
				}
				
				do_action( 'food_manager_my_food_do_action', $action, $food_id );

			} catch ( Exception $e ) {

				$this->food_dashboard_message = '<div class="food-manager-error wpfm-alert wpfm-alert-danger">' . $e->getMessage() . '</div>';
			}
		}
	}
	
	/**
	 * Shortcode which lists the logged in user's foods
	 */	 
	public function food_dashboard( $atts ) {

		if ( ! is_user_logged_in() ) {

			ob_start();

			get_food_manager_template( 'food-dashboard-login.php' );

			return ob_get_clean();
		}
		
		extract( shortcode_atts( array(

			'posts_per_page' => '25',

		), $atts ) );

		wp_enqueue_script( 'wp-food-manager-food-dashboard' );

		ob_start();

		// If doing an action, show conditional content if needed....

		if ( ! empty( $_REQUEST['action'] ) ) {

			$action = sanitize_title( $_REQUEST['action'] );

			// Show alternative content if a plugin wants to

			if ( has_action( 'food_manager_food_dashboard_content_' . $action ) ) {

				do_action( 'food_manager_food_dashboard_content_' . $action, $atts );

				return ob_get_clean();
			}
		}
		
		// ....If not show the food dashboard

		$args     = apply_filters( 'food_manager_get_dashboard_foods_args', array(

			'post_type'           => 'food_manager',
			'post_status'         => array( 'publish', 'expired', 'pending' ),
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $posts_per_page,
			'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * $posts_per_page,
			'orderby'             => 'date',
			'order'               => 'desc',
			'author'              => get_current_user_id()

		) );

		$foods = new WP_Query;
		echo $this->food_dashboard_message;

		$food_dashboard_columns = apply_filters( 'food_manager_food_dashboard_columns', array(

			'food_title' => __( 'Title', 'wp-food-manager' ),
			'food_categories' => __( 'Category', 'wp-food-manager' ),
			/*'food_start_date' => __( 'Start Date', 'wp-food-manager' ),
			'food_end_date' => __( 'End Date', 'wp-food-manager' ),*/
			'view_count' => __( 'Viewed', 'wp-food-manager' ),
			'food_action' => __( 'Action', 'wp-food-manager' ), 
		) );

		get_food_manager_template( 'food-dashboard.php', array( 'foods' => $foods->query( $args ), 'max_num_pages' => $foods->max_num_pages, 'food_dashboard_columns' => $food_dashboard_columns ) );

		return ob_get_clean();
	}

	/**
	 * Edit food form
	 */
	public function edit_food() {

		global $food_manager;

		echo $food_manager->forms->get_form( 'edit-food' );
	}

	

	/**
	 * output_foods function.
	 *
	 * @access public
	 * @param mixed $args
	 * @return void
	 */
	public function output_foods( $atts ) {

		ob_start();

		extract( $atts = shortcode_atts( apply_filters( 'food_manager_output_foods_defaults', array(

			'per_page'                  => get_option( 'food_manager_per_page' ),

			'orderby'                   => 'meta_value', // meta_value

			'order'                     => 'ASC',

			// Filters + cats

			'show_filters'              => true,			

			'show_categories'           => true,

			'show_food_types'          => true,

			
			'show_category_multiselect' => get_option( 'food_manager_enable_default_category_multiselect', false ),

			'show_food_type_multiselect' => get_option( 'food_manager_enable_default_food_type_multiselect', false ),

			'show_pagination'           => false,

			'show_more'                 => true,

			// Limit what foods are shown based on category and type

			'categories'                => '',

			'food_types'               => '',

			'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.

			'cancelled'                 => null, // True to show only cancelled, false to hide cancelled, leave null to show both/use the settings.

			// Default values for filters

			'location'                  => '',

			'keywords'                  => '',

			'selected_category'         => '',

			'selected_food_type'       => '',

			'layout_type'      => 'all',

		) ), $atts ) );

		//Categories

		if ( ! get_option( 'food_manager_enable_categories' ) ) {

			$show_categories = false;

		}

		//food types

		if ( ! get_option( 'food_manager_enable_food_types' ) ) {

			$show_food_types = false;

		}

		//food ticket prices		

		if ( ! get_option( 'food_manager_enable_food_ticket_prices' ) ) {

			$show_ticket_prices = false;

		}

		// String and bool handling

		$show_filters              = $this->string_to_bool( $show_filters );

		$show_categories           = $this->string_to_bool( $show_categories );

		$show_food_types          = $this->string_to_bool( $show_food_types );

		$show_ticket_prices        = $this->string_to_bool( $show_ticket_prices );

		$show_category_multiselect = $this->string_to_bool( $show_category_multiselect );

		$show_food_type_multiselect= $this->string_to_bool( $show_food_type_multiselect);

		$show_more                 = $this->string_to_bool( $show_more );

		$show_pagination           = $this->string_to_bool( $show_pagination );
		
		//order by meta value and it will take default sort order by start date of food
		if ( is_null( $orderby ) ||  empty($orderby ) ) {
			$orderby  = 'meta_value';
		}
		
		if ( ! is_null( $featured ) ) {

			$featured = ( is_bool( $featured ) && $featured ) || in_array( $featured, array( '1', 'true', 'yes' ) ) ? true : false;
		}

		if ( ! is_null( $cancelled ) ) {

			$cancelled = ( is_bool( $cancelled ) && $cancelled ) || in_array( $cancelled, array( '1', 'true', 'yes' ) ) ? true : false;
		}

		

		// Array handling

	

		$categories           = is_array( $categories ) ? $categories : array_filter( array_map( 'trim', explode( ',', $categories ) ) );

		$food_types          = is_array( $food_types ) ? $food_types : array_filter( array_map( 'trim', explode( ',', $food_types ) ) );

	

		// Get keywords, location, datetime, category, food type and ticket price from query string if set

		if ( ! empty( $_GET['search_keywords'] ) ) {

			$keywords = sanitize_text_field( $_GET['search_keywords'] );
		}

		if ( ! empty( $_GET['search_location'] ) ) {

			$location = sanitize_text_field( $_GET['search_location'] );
		}

		if ( ! empty( $_GET['search_datetime'] ) ) {

			$selected_datetime = sanitize_text_field( $_GET['search_datetime'] );
		}

		if ( ! empty( $_GET['search_category'] ) ) {

			$selected_category = sanitize_text_field( $_GET['search_category'] );
		}

		if ( ! empty( $_GET['search_food_type'] ) ) {

			$selected_food_type = sanitize_text_field( $_GET['search_food_type'] );
		}

		if ( ! empty( $_GET['search_ticket_price'] ) ) {

			$selected_ticket_price = sanitize_text_field( $_GET['search_ticket_price'] );
		}

		if ( $show_filters ) {

			get_food_manager_template( 'food-filters.php', array( 

										'per_page' => $per_page, 

										'orderby' => $orderby, 

										'order' => $order, 

										
										'show_categories' => $show_categories, 

										'show_category_multiselect' => $show_category_multiselect,

										'categories' => $categories,

										'selected_category' => $selected_category, 

										'show_food_types' => $show_food_types ,

										'show_food_type_multiselect' => $show_food_type_multiselect,

										'food_types' => $food_types, 

										'selected_food_type' => $selected_food_type, 

										'show_ticket_prices' => $show_ticket_prices ,

										
							

										'atts' => $atts, 

										'location' => $location, 

										'keywords' => $keywords,						
										
									      ));

			get_food_manager_template( 'food-listings-start.php',array('layout_type'=>$layout_type) );
			

			get_food_manager_template( 'food-listings-end.php' );

			if ( ! $show_pagination && $show_more ) {

				echo '<a class="load_more_foods" id="load_more_foods" href="#" style="display:none;"><strong>' . __( 'Load more foods', 'wp-food-manager' ) . '</strong></a>';
			}
			
		} else {
		    
			$foods = get_food_managers( apply_filters( 'food_manager_output_foods_args', array(

				'search_location'   => $location,

				'search_keywords'   => $keywords,

				'search_datetimes'  => $datetimes,

				'search_categories' => $categories,

				'search_food_types'       => $food_types,

				'search_ticket_prices'       => $ticket_prices,

				'orderby'           => $orderby,

				'order'             => $order,

				'posts_per_page'    => $per_page,

				'featured'          => $featured,

				'cancelled'         => $cancelled

			) ) );

			if ( $foods->have_posts() ) : ?>

				<?php get_food_manager_template( 'food-listings-start.php' ,array('layout_type'=>$layout_type)); ?>			

				<?php while ( $foods->have_posts() ) : $foods->the_post(); ?>

					<?php  get_food_manager_template_part( 'content', 'food_manager' ); ?>
					
				<?php endwhile; ?>

				<?php get_food_manager_template( 'food-listings-end.php' ); ?>

				<?php if ( $foods->found_posts > $per_page && $show_more ) : ?>

					<?php wp_enqueue_script( 'wpfm-ajax-filters' ); ?>

					<?php if ( $show_pagination ) : ?>

						<?php echo get_food_manager_pagination( $foods->max_num_pages ); ?>

					<?php else : ?>

						<a class="load_more_foods" id="load_more_foods" href="#"><strong><?php _e( 'Load more listings', 'wp-food-manager' ); ?></strong></a>

					<?php endif; ?>

				<?php endif; ?>

			<?php else :

				do_action( 'food_manager_output_foods_no_results' );

			endif;

			wp_reset_postdata();
		}

		$data_attributes_string = '';

		$data_attributes        = array(

			'location'        => $location,

			'keywords'        => $keywords,

			'show_filters'    => $show_filters ? 'true' : 'false',

			'show_pagination' => $show_pagination ? 'true' : 'false',

			'per_page'        => $per_page,

			'orderby'         => $orderby,

			'order'           => $order,


			'categories'      => !empty($selected_category) ? implode( ',', $selected_category ) : '',

			'food_types'     => !empty($selected_food_type) ? implode( ',', $selected_food_type) : '',

			'ticket_prices'   => !empty($selected_ticket_price) ? $selected_ticket_price : ''
		);

		if ( ! is_null( $featured ) ) {

			$data_attributes[ 'featured' ] = $featured ? 'true' : 'false';
		}

		if ( ! is_null( $cancelled ) ) {

			$data_attributes[ 'cancelled' ]   = $cancelled ? 'true' : 'false';
		}

		foreach ( $data_attributes as $key => $value ) {

			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}
		
		$food_managers_output = apply_filters( 'wpfm_food_managers_output', ob_get_clean() );

		return '<div class="food_listings" ' . $data_attributes_string . '>' . $food_managers_output . '</div>';
	}

	/**
	 * Output some content when no results were found
	 */
	public function output_no_results() {

		get_food_manager_template( 'content-no-foods-found.php' );
	}

	/**
	 * Output anchor tag close: single organizer details url
	 */
	public function organizer_more_info_link( $organizer_id ) {

		global $post;
		
		if ( ! $post || 'food_manager' !== $post->post_type ) {
			return;
		}

		if(isset($organizer_id) && !empty($organizer_id))
		{	
			$organizer_url = get_permalink( $organizer_id );

			if(isset($organizer_url) && !empty($organizer_url))
			{
				printf( '<div class="wpfm-organizer-page-url-button"><a href="%s" class="wpfm-theme-button"><span>%s</span></a></div>',  get_permalink( $organizer_id ), __( 'More info', 'wp-food-manager' ) );	
			}
		}
	}
	
	/**
	 * Get string as a bool
	 * @param  string $value
	 * @return bool
	 */
	public function string_to_bool( $value ) {

		return ( is_bool( $value ) && $value ) || in_array( $value, array( '1', 'true', 'yes' ) ) ? true : false;
	}

	/**
	 * Show results div
	 */
	public function food_filter_results() {

		echo '<div class="showing_applied_filters"></div>';
	}

	/**
	 * output_food function.
	 *
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_food( $atts ) {
	    
		extract( shortcode_atts( array(
		    
			'id' => '',

		), $atts ) );

		if ( ! $id )

			return;
			
		ob_start();

		$args = array(

			'post_type'   => 'food_manager',

			'post_status' => 'publish',

			'p'           => $id
		);

		$foods = new WP_Query( $args );

		if ( $foods->have_posts() ) : ?>

			<?php while ( $foods->have_posts() ) : $foods->the_post(); ?>
				
				<div class="clearfix" />
                <?php get_food_manager_template_part( 'content-single', 'food_manager' ); ?>

			<?php endwhile; ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="food_shortcode single_food_manager">' . ob_get_clean() . '</div>';
	}
	
	/**
	 * food Summary shortcode
	 *
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_food_summary( $atts ) {

		extract( shortcode_atts( array(

			'id'       => '',	
			'width'    => '250px',
			'align'    => 'left',
			'featured' => null, // True to show only featured, false to hide featured, leave null to show both (when leaving out id)

			'limit'    => 1

		), $atts ) );

		ob_start();
		
		$args = array(

			'post_type'   => 'food_manager',

			'post_status' => 'publish'
		);


		if ( ! $id ) {

			$args['posts_per_page'] = $limit;

			$args['orderby']        = 'rand';

			if ( ! is_null( $featured ) ) {

				$args['meta_query'] = array( array(

					'key'     => '_featured',

					'value'   => '1',

					'compare' => $featured ? '=' : '!='
				) );
			}
			
		} else {

			$args['p'] = absint( $id );
		}

		$foods = new WP_Query( $args );

		if ( $foods->have_posts() ) : ?>

			<?php while ( $foods->have_posts() ) : $foods->the_post();

				echo '<div class="food_summary_shortcode align' . esc_attr( $align ) . '" style="width: ' . esc_attr( $width ) . '">';

				get_food_manager_template_part( 'content-summary', 'food_manager' );

				echo '</div>';

			endwhile; ?>

		<?php endif;

		wp_reset_postdata();

		return ob_get_clean();
	}
	
	/**
	 * Show the registration area
	 */
	public function output_food_register( $atts ) {
		extract( shortcode_atts( array(
			'id'       => ''
		), $atts ) );

		ob_start();

		$args = array(
			'post_type'   => 'food_manager',
			'post_status' => 'publish'
		);

		if ( ! $id ) {
			return '';
		} else {
			$args['p'] = absint( $id );
		}

		$foods = new WP_Query( $args );

		if ( $foods->have_posts() ) : ?>

			<?php while ( $foods->have_posts() ) : $foods->the_post(); ?>

				<div class="food-manager-registration-wrapper">
					<?php
						$register = get_food_registration_method();
						do_action( 'food_manager_registration_details_' . $register->type, $register );
					?>
				</div>

			<?php endwhile; ?>

		<?php endif;

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * output_foods function.
	 *
	 * @access public
	 * @param mixed $args
	 * @return void
	 */
	public function output_past_foods( $atts ) {

		ob_start();

		extract( shortcode_atts ( array(

			'show_pagination'           => true,

			'per_page'                  => get_option( 'food_manager_per_page' ),

			'order'                     => 'DESC',

			'orderby'                   => 'meta_value', // meta_value

			'meta_key'  				=> 'food_start_date',

			'location'                  => '',

			'keywords'                  => '',

			'selected_datetime'         => '',

			'selected_categories'       => '',

			'selected_food_types'     => '',
		), $atts ) );

		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

		if(substr( $meta_key, 0, 1 ) !== "_")
		{
			$meta_key = '_'.$meta_key;
		}
		
		$args_past = array(
			'post_type'  	=> 'food_manager',
			'post_status'	=> array('expired'),
			'posts_per_page' => $per_page,
			'paged'			=> $paged,
			'order'			=> $order,
			'orderby'		=> $orderby,
			'meta_key'		=> $meta_key,
		);

		if(!empty($keywords))
		{
			$args_past['s'] = $keywords;
		}

		if(!empty($selected_categories))
		{
			$categories = explode(',', sanitize_text_field($selected_categories) );

			$args_past['tax_query'][] = [
				'taxonomy'	=> 'food_manager_category',
				'field'   	=> 'name',
				'terms'   	=> $categories,
			];
		}

		if(!empty($selected_food_types))
		{
			$food_types = explode(',', sanitize_text_field($selected_food_types) );

			$args_past['tax_query'][] = [
				'taxonomy'	=> 'food_manager_type',
				'field'   	=> 'name',
				'terms'   	=> $food_types,
			];
		}

		if(!empty($selected_datetime))
		{
			$datetimes = explode(',', $selected_datetime);

			$args_past['meta_query'][] = [
				'key' => '_food_start_date',
				'value'   => $datetimes,
				'compare' => 'BETWEEN',
				'type'    => 'date'
			];
		}

		if(!empty($location))
		{
			$args_past['meta_query'][] = [
				'key' 		=> '_food_location',
				'value'  	=> $location,
				'compare'	=> 'LIKE'
			];
		}

		$past_foods = new WP_Query( $args_past );

		wp_reset_query();

		if ( $past_foods->have_posts() ) : ?>
			<div id="food-listing-view" class="wpfm-main wpfm-food-listings food_managers wpfm-food-listing-list-view">	
			<?php while ( $past_foods->have_posts() ) : $past_foods->the_post(); ?>

				<?php  get_food_manager_template_part( 'content', 'past_food_manager' ); ?>
				
			<?php endwhile; ?>

			<?php if ($past_foods->found_posts > $per_page) : ?>
                <?php if ($show_pagination == "true") : ?>
                    <div class="food-organizer-pagination">
                    	<?php get_food_manager_template('pagination.php', array('max_num_pages' => $past_foods->max_num_pages)); ?>
                    </div> 
                <?php endif; ?>
            <?php endif; ?>

			</div>
		<?php else :

			do_action( 'food_manager_output_foods_no_results' );

		endif;

		wp_reset_postdata();
		
		$food_managers_output = apply_filters( 'food_manager_food_managers_output', ob_get_clean() );

		return  $food_managers_output;
		
	}

	/**
	 *  It is very simply a plugin that outputs a list of all organizers that have listed foods on your website. 
     *  Once you have installed " WP food Manager - Organizer Profiles" simply visit "Pages > Add New". 
     *  Once you have added a title to your page add the this shortcode: [food_organizers]
     *  This will output a grouped and alphabetized list of all organizers.
	 *
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_food_organizers($atts)
	{
		$organizers   = get_all_organizer_array();
		$countAllfoods = get_food_organizer_count();        
        $organizers_array = [];

        if(!empty($organizers))
        {
        	foreach ( $organizers as $organizer_id => $organizer )
        	{
        		$organizers_array[ strtoupper( $organizer[0] ) ][$organizer_id] = $organizer;
        	}
        }        
         
		//wp_enqueue_script( 'wp-food-manager-organizer');
        
        get_food_manager_template( 
      		'food-organizers.php', 
      		array(
				'organizers'		=> $organizers,
				'organizers_array'  => $organizers_array,
            	'countAllfoods'    => $countAllfoods,
			), 
			'wp-food-manager', 
			food_MANAGER_PLUGIN_DIR . '/templates/organizer/' 
		);
              
		wp_reset_postdata();
		
		return ob_get_clean();
	}


	/**
	 *  It is very simply a plugin that outputs a list of all organizers that have listed foods on your website. 
     *  Once you have installed " WP food Manager - Organizer Profiles" simply visit "Pages > Add New". 
     *  Once you have added a title to your page add the this shortcode: [food_organizer]
     *  This will output a grouped and alphabetized list of all organizers.
	 *
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_food_organizer($atts)
	{
		extract( shortcode_atts( array(		    
			'id' => '',
		), $atts ) );

		if ( ! $id )
			return;

		ob_start();

		$args = array(
			'post_type'   => 'food_organizer',
			'post_status' => 'publish',
			'p'           => $id
		);

		$organizers = new WP_Query( $args );

		if(empty($organizers->posts))
			return;

		ob_start();

		$organizer    = $organizers->posts[0];

        $paged           = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $per_page        = 10;
        $today_date      = date("Y-m-d");
        $organizer_id    = $organizer->ID;
        $show_pagination = true;

        $args_upcoming = array(
            'post_type'      => 'food_manager',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged
        );

        $args_upcoming['meta_query'] = array(
            'relation' => 'AND',
            array(
                'key'     => '_food_organizer_ids',
                'value'   => $organizer_id,
                'compare' => 'LIKE',
            ),
            array(
                'key'     => '_food_start_date',
                'value'   => $today_date,
                'type'    => 'date',
                'compare' => '>'
            )
        );

        $upcomingfoods = new WP_Query($args_upcoming);
        wp_reset_query();

        $args_current = $args_upcoming;

        $args_current['meta_query'] = array(
            'relation' => 'AND',
            array(
                'key'     => '_food_organizer_ids',
                'value'   => $organizer_id,
                'compare' => 'LIKE',
            ),
            array(
                'key'     => '_food_start_date',
                'value'   => $today_date,
                'type'    => 'date',
                'compare' => '<='
            ),
            array(
                'key'     => '_food_end_date',
                'value'   => $today_date,
                'type'    => 'date',
                'compare' => '>='
            )
        );

        $currentfoods = new WP_Query($args_current);
        wp_reset_query();

        $args_past = array(
            'post_type'      => 'food_manager',
            'post_status'    => array('expired', 'publish'),
            'posts_per_page' => $per_page,
            'paged'          => $paged
        );

        $args_past['meta_query'] = array(
            'relation' => 'AND',
            array(
                'key'     => '_food_organizer_ids',
                'value'   => $organizer_id,
                'compare' => 'LIKE',
            ),
            array(
                'key'     => '_food_end_date',
                'value'   => $today_date,
                'type'    => 'date',
                'compare' => '<'
            )
        );
        $pastfoods              = new WP_Query($args_past);
        wp_reset_query();

        do_action('organizer_content_start');

        wp_enqueue_script('wp-food-manager-organizer');

        get_food_manager_template(
            'content-single-food_organizer.php', array(
            'organizer_id'    => $organizer_id,
            'per_page'        => $per_page,
            'show_pagination' => $show_pagination,
            'upcomingfoods'  => $upcomingfoods,
            'currentfoods'   => $currentfoods,
            'pastfoods'      => $pastfoods,
                ), 'wp-food-manager', food_MANAGER_PLUGIN_DIR . '/templates/organizer/'
        );

        wp_reset_postdata();

        do_action('organizer_content_end');

        return ob_get_clean();
	}

	/**
	 *  output food menu
	 *
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_food_menu($atts){
		extract( shortcode_atts( array(		    
			'id' => '',
		), $atts ) );

		$args = array(
			'post_type'   => 'food_manager_menu',
			'post_status' => 'publish',
			'p'           => $id
		);

		$food_menus = new WP_Query( $args );

		if ( $food_menus->have_posts() ) : ?>

			<?php while ( $food_menus->have_posts() ) : $food_menus->the_post(); ?>
				
				<div class="clearfix" />
                <?php  get_food_manager_template_part( 'content', 'food_manager_menu' ); ?>

			<?php endwhile; ?>

		<?php endif;

		wp_reset_postdata();
	}

}

new WPFM_Shortcodes(); ?>
