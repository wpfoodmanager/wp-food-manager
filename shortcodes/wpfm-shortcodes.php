<?php

/**
 * This file is use to create a shortcode of wp food manager plugin.
 * This file include shortcode of food listing, food submit form, and food dashboard, etc.
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly.

/**
 * WPFM_Shortcodes class.
 * Add all of the shortcodes which are used in the entire plugin.
 */
class WPFM_Shortcodes {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;
	public $food_dashboard_message;

	/**
	 * Allows for accessing the single instance of the class. Class should only be constructed once per call.
	 *
	 * @static
	 * @return self Main instance.
	 * @since 1.0.1
	 */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action('wp', array($this, 'shortcode_action_handler'));
		add_shortcode('add_food', array($this, 'add_food'));
		add_shortcode('food_dashboard', array($this, 'food_dashboard'));
		add_shortcode('foods', array($this, 'output_foods'));
		add_shortcode('food', array($this, 'output_food'));
		if (get_option('food_manager_enable_food_menu', true)) {
			add_shortcode('wpfm_food_menu', array($this, 'output_food_menu'));
		}
		add_shortcode('food_menu', array($this, 'food_menu_output_callback_function'));
		add_shortcode('food_menu_search', array($this, 'food_menu_output_search_callback_function'));
		add_shortcode('restaurant_food_menu_title', array($this, 'food_menu_title_output_callback_function_for_restaurant'));
		add_shortcode('restaurant_food_menu', array($this, 'food_menu_output_callback_function_for_restaurant'));
	}

	/**
	 * Show the food submission form.
	 *
	 * @access public
	 * @param array $atts
	 * @since 1.0.1
	 */
	public function add_food($atts = array()) {
		return $GLOBALS['food_manager']->forms->get_form('add-food', $atts);
	}

	/**
	 * Handles actions on food dashboard.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.1
	 */
	public function food_dashboard_handler() {
		if (!empty($_REQUEST['action']) && !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'food_manager_my_food_actions')) {
			$action = sanitize_title($_REQUEST['action']);
			$food_id = absint($_REQUEST['food_id']);
			try {
				// Get food.
				$food    = get_post($food_id);
				// Check ownership.
				if (!food_manager_user_can_edit_food($food_id)) {
					throw new Exception(__('Invalid ID', 'wp-food-manager'));
				}
				switch ($action) {
					case 'delete':
						$foods_status = get_post_status($food_id);
						// Trash it.
						wp_trash_post($food_id);
						// Message.
						if (!in_array($foods_status, ['trash'])) {
							// translators: %s: Title of the food item that has been deleted
							$this->food_dashboard_message = '<div class="food-manager-message wpfm-alert wpfm-alert-danger">' . sprintf(__('%s has been deleted.', 'wp-food-manager'), esc_html($food->post_title)) . '</div>';
						}
						break;
					case 'duplicate':
						if (!food_manager_get_permalink('add_food')) {
							throw new Exception(__('Missing submission page.', 'wp-food-manager'));
						}
						$new_food_id = food_manager_duplicate_listing($food_id);
						if ($new_food_id) {
							wp_redirect(add_query_arg(array('food_id' => absint($new_food_id)), food_manager_get_permalink('add_food')));
							exit;
						}
						break;
					case 'relist':
						// redirect to post page.
						wp_redirect(add_query_arg(array('food_id' => absint($food_id)), food_manager_get_permalink('add_food')));
						break;
					default:
						do_action('food_manager_food_dashboard_do_action_' . $action);
						break;
				}
				do_action('food_manager_my_food_do_action', $action, $food_id);
			} catch (Exception $e) {
				$this->food_dashboard_message = '<div class="food-manager-error wpfm-alert wpfm-alert-danger">' . esc_html($e->getMessage()) . '</div>';
			}
		}
	}

	/**
	 * Handle actions which need to be run before the shortcode e.g. post actions.
	 *
	 * @access public
	 * @since 1.0.1
	 */
	public function shortcode_action_handler() {
		global $post;
		if (is_page() && strstr($post->post_content, '[food_dashboard')) {
			$this->food_dashboard_handler();
		}
	}

	/**
	 * Shortcode which lists the logged-in user's foods.
	 *
	 * @access public
	 * @param $atts
	 * @since 1.0.1
	 */
	public function food_dashboard($atts) {
		global $wpdb, $food_manager_keyword;

		if (!is_user_logged_in()) {
			ob_start();
			get_food_manager_template('food-dashboard-login.php');
			return ob_get_clean();
		}

		extract(shortcode_atts(array(
			'posts_per_page' => '10',
		), $atts));

		wp_enqueue_script('wp-food-manager-food-dashboard');

		ob_start();
		// If doing an action, show conditional content if needed....
		if (!empty($_REQUEST['action'])) {
			$action = sanitize_title($_REQUEST['action']);
			// Show alternative content if a plugin wants to.
			if (has_action('food_manager_food_dashboard_content_' . $action)) {
				do_action('food_manager_food_dashboard_content_' . $action, $atts);
				return ob_get_clean();
			}
		}

		$search_order_by = isset($_GET['search_order_by']) ? sanitize_text_field($_GET['search_order_by']) : '';
		if (isset($search_order_by) && !empty($search_order_by)) {
			$search_order_by = explode('|', $search_order_by);
			$orderby = $search_order_by[0];
			$order = $search_order_by[1];
		} else {
			$orderby = 'date';
			$order = 'desc';
		}

		// If not show the food dashboard.
		$args = apply_filters('food_manager_get_dashboard_foods_args', array(
			'post_type'           => 'food_manager',
			'post_status'         => array('publish', 'expired', 'pending'),
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $posts_per_page,
			'offset'              => (max(1, get_query_var('paged')) - 1) * $posts_per_page,
			'orderby'             => $orderby,
			'order'               => $order,
			'author'              => get_current_user_id()
		));

		$food_manager_keyword = isset($_GET['search_keywords']) ? sanitize_text_field($_GET['search_keywords']) : '';
		if (!empty($food_manager_keyword) && strlen($food_manager_keyword) >= apply_filters('food_manager_get_listings_keyword_length_threshold', 2)) {
			$args['s'] = $food_manager_keyword;
			add_filter('posts_search', 'get_food_listings_keyword_search');
		}

		if (isset($args['orderby']) && !empty($args['orderby'])) {
			if ($args['orderby'] == 'food_manager') {
				$args['meta_query'] = array(
					'relation' => 'AND',
					'food_manager_type_clause' => array(
						'key'     => '_food_manager',
						'compare' => 'EXISTS',
					),
					'food_manager_clause' => array(
						'key'     => '_food_manager',
						'compare' => 'EXISTS',
					),
				);
				$args['orderby'] = array(
					'food_manager_type_clause' => ($search_order_by[1] === 'desc') ? 'asc' : 'desc',
					'food_manager_clause' => $search_order_by[1],
				);
			}
		}

		$foods = new WP_Query(apply_filters('food_manager_food_dashboard_args',$args));
		if (!empty($this->food_dashboard_message)) {
			echo wp_kses($this->food_dashboard_message, array(
				'div' => [
					'id' => true,
					'class' => true,
				],
			));
		}
		$food_dashboard_columns = apply_filters('food_manager_food_dashboard_columns', array(
			'food_title' => esc_html__('Title', 'wp-food-manager'),
			'view_count' => esc_html__('Viewed', 'wp-food-manager'),
			'food_action' => esc_html__('Action', 'wp-food-manager'),
		));
		get_food_manager_template('food-dashboard.php', array('foods' => $foods->query($args), 'max_num_pages' => $foods->max_num_pages, 'food_dashboard_columns' => $food_dashboard_columns));

		return ob_get_clean();
	}

	/**
	 * Output the filtered or default foods on food listing page.
	 *
	 * @access public
	 * @param mixed $atts
	 * @return void
	 * @since 1.0.1
	 */
	public function output_foods($atts) {
		ob_start();
		extract($atts = shortcode_atts(apply_filters('food_manager_output_foods_defaults', array(
			'per_page'                  => get_option('food_manager_per_page') ? get_option('food_manager_per_page') : 5,
			'orderby'                   => 'meta_value', // meta_value
			'order'                     => 'ASC',
			// Filters + cats
			'show_filters'              => true,
			'show_categories'           => true,
			'show_food_types'           => true,
			'show_food_tags'            => true,
			'show_category_multiselect' => get_option('food_manager_enable_default_category_multiselect', false),
			'show_food_type_multiselect' => get_option('food_manager_enable_default_food_type_multiselect', false),
			'show_food_menu_multiselect' => get_option('food_manager_enable_default_food_menu_multiselect', false),
			'show_pagination'           => false,
			'show_more'                 => true,
			// Limit what foods are shown based on category and type.
			'categories'                => '',
			'food_types'                => '',
			'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
			'cancelled'                 => null, // True to show only cancelled, false to hide cancelled, leave null to show both/use the settings.
			// Default values for filters.
			'location'                  => '',
			'keywords'                  => '',
			'selected_category'         => '',
			'selected_food_type'        => '',
			'layout_type'               => 'all',
			'title'			            => __('Foods', 'wp-food-manager'),
		)), $atts));		
		//Categories.
		if (!get_option('food_manager_enable_categories')) {
			$show_categories = false;
		}

		//food types.
		if (!get_option('food_manager_enable_food_types')) {
			$show_food_types = false;
		}

		//food tags.
		if (!get_option('food_manager_enable_food_tags')) {
			$show_food_tags = false;
		}

		// String and bool handling.
		$show_filters              = $this->string_to_bool($show_filters);
		$show_categories           = $this->string_to_bool($show_categories);
		$show_food_types          = $this->string_to_bool($show_food_types);
		$show_food_tags          = $this->string_to_bool($show_food_tags);
		$show_category_multiselect = $this->string_to_bool($show_category_multiselect);
		$show_food_type_multiselect = $this->string_to_bool($show_food_type_multiselect);
		$show_more                 = $this->string_to_bool($show_more);
		$show_pagination           = $this->string_to_bool($show_pagination);

		// Order by meta value and it will take default sort order by start date of food.
		if (is_null($orderby) || empty($orderby)) {
			$orderby  = 'meta_value'; //meta_value
		}

		if (!is_null($featured)) {
			$featured = (is_bool($featured) && $featured) || in_array($featured, array('1', 'true', 'yes')) ? true : false;
		}

		if (!is_null($cancelled)) {
			$cancelled = (is_bool($cancelled) && $cancelled) || in_array($cancelled, array('1', 'true', 'yes')) ? true : false;
		}

		// Array handling.
		$categories           = is_array($categories) ? $categories : array_filter(array_map('trim', explode(',', $categories)));
		$food_types          = is_array($food_types) ? $food_types : array_filter(array_map('trim', explode(',', $food_types)));

		// Get keywords, location, category and food type from query string if set.
		if (!empty($_GET['search_keywords'])) {
			$keywords = sanitize_text_field($_GET['search_keywords']);
		}
		if (!empty($_GET['search_datetime'])) {
			$selected_datetime = sanitize_text_field($_GET['search_datetime']);
		}
		if (!empty($_GET['search_category'])) {
			$selected_category = sanitize_text_field($_GET['search_category']);
		}
		if (!empty($_GET['search_food_type'])) {
			$selected_food_type = sanitize_text_field($_GET['search_food_type']);
		}
		
		$args = array(
			'post_type'      => 'food_manager_menu',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		$food_menu_query = new WP_Query(apply_filters('food_manager_filter_foods_args',$args));

		if ($show_filters) {
			get_food_manager_template('food-filters.php', apply_filters('food_manager_food_filter_result', array(
				'per_page' => $per_page,
				'orderby' => $orderby,
				'order' => $order,
				'show_categories' => $show_categories,
				'show_category_multiselect' => $show_category_multiselect,
				'categories' => $categories,
				'selected_category' => $selected_category,
				'show_food_types' => $show_food_types,
				'show_food_tags' => $show_food_tags,
				'show_food_type_multiselect' => $show_food_type_multiselect,
				'show_food_menu_multiselect' => $show_food_menu_multiselect,
				'food_types' => $food_types,
				'selected_food_type' => $selected_food_type,
				'atts' => $atts,
				'keywords' => $keywords,
				'food_menu_query' => $food_menu_query->query($args)
			)));
			get_food_manager_template('food-listings-start.php', array('layout_type' => $layout_type, 'title' => $title));
			get_food_manager_template('food-listings-end.php');
			if (!$show_pagination && $show_more) {
				echo '<a class="load_more_foods" id="load_more_foods" href="javascript:void(0);" style="display:none;"><strong>' . esc_html__('Load more foods', 'wp-food-manager') . '</strong></a>';

			}
		} else {
			$foods = get_food_listings(apply_filters('food_manager_output_foods_args', array(
				'search_keywords'   => $keywords,
				'search_categories' => $categories,
				'search_food_types' => $food_types,
				'orderby'           => $orderby,
				'order'             => $order,
				'posts_per_page'    => $per_page,
				'featured'          => $featured,
				'cancelled'         => $cancelled
			)));
			if ($foods->have_posts()) : ?>
				<?php get_food_manager_template('food-listings-start.php', array('layout_type' => $layout_type)); ?>
				<?php while ($foods->have_posts()) : $foods->the_post(); ?>
					<?php get_food_manager_template_part('content', 'food_manager'); ?>
				<?php endwhile; ?>
				<?php get_food_manager_template('food-listings-end.php'); ?>
				<?php if ($foods->found_posts > $per_page && $show_more) : ?>
					<?php wp_enqueue_script('wpfm-ajax-filters'); ?>
					<?php if ($show_pagination) : ?>
						<?php echo get_food_manager_pagination($foods->max_num_pages); ?>
					<?php else : ?>
						<a class="load_more_foods" id="load_more_foods" href="javascript:void(0);"><strong><?php _e('Load more listings', 'wp-food-manager'); ?></strong></a>
					<?php endif; ?>
				<?php endif; ?>
		<?php else :
				do_action('food_manager_output_foods_no_results');
			endif;
			wp_reset_postdata();
		}

		$data_attributes_string = '';
		$data_attributes        = apply_filters('food_manager_data_attributes_args', array(
			'location'        => $location,
			'keywords'        => $keywords,
			'show_filters'    => $show_filters ? 'true' : 'false',
			'show_pagination' => $show_pagination ? 'true' : 'false',
			'per_page'        => $per_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'categories'      => !empty($selected_category) ? implode(',', array_map('esc_attr', $selected_category)) : '',
			'food_types'     => !empty($selected_food_type) ? implode(',', array_map('esc_attr', $selected_food_type)) : '',
		));

		if (!is_null($featured)) {
			$data_attributes['featured'] = $featured ? 'true' : 'false';
		}
		if (!is_null($cancelled)) {
			$data_attributes['cancelled']   = $cancelled ? 'true' : 'false';
		}

		foreach ($data_attributes as $key => $value) {
			$data_attributes_string .= 'data-' . esc_attr($key) . '="' . esc_attr($value) . '" ';
		}
		$food_managers_output = apply_filters('wpfm_food_managers_output', ob_get_clean());

		return '<div class="food_listings" ' . $data_attributes_string . '>' . $food_managers_output . '</div>';
	}

	/**
	 * Output content of food categories.
	 * 
	 * @access public
	 * @return void
	 * @since 1.0.1
	 */
	public function output_foods_categories() { ?>
		<h2>
			<?php esc_html_e('Food Categories'); ?>
		</h2>
		<div id="food-listing-view" class="wpfm-main wpfm-food-listings food_listings wpfm-row wpfm-food-listing-box-view">
			<?php get_food_manager_template('content-food-categories.php'); ?>
		</div>
	<?php }

	/**
	 * Output content of food types.
	 * 
	 * @access public
	 * @return void
	 * @since 1.0.1
	 */
	public function output_foods_types() { ?>
		<h2>
			<?php esc_html_e('Food Types'); ?>
		</h2>
		<div id="food-listing-view" class="wpfm-main wpfm-food-listings food_listings wpfm-row wpfm-food-listing-box-view">
			<?php get_food_manager_template('content-food-types.php'); ?>
		</div>
		<?php
	}

	/**
	 * Get string as a bool.
	 * 
	 * @access public
	 * @param  string $value
	 * @return bool
	 * @since 1.0.1
	 */
	public function string_to_bool($value) {
		return (is_bool($value) && $value) || in_array($value, array('1', 'true', 'yes')) ? true : false;
	}

	/**
	 * Output the single food by food id.
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 * @since 1.0.0
	 */
	public function output_food($atts) {
		extract(shortcode_atts(array(
			'id' => '',
		), $atts));

		if (!$id)
			return;

		ob_start();
		$args = array(
			'post_type'   => 'food_manager',
			'post_status' => 'publish',
			'p'           => $id
		);

		$foods = new WP_Query(apply_filters('food_manager_food_listing_ids_args',$args));
		if ($foods->have_posts()) : ?>
			<?php while ($foods->have_posts()) : $foods->the_post(); ?>
				<div class="clearfix">
					<?php get_food_manager_template_part('content-single', 'food_manager'); ?>
				</div>
			<?php endwhile; ?>
		<?php endif;
		wp_reset_postdata();

		return '<div class="food_shortcode single_food_manager">' . ob_get_clean() . '</div>';
	}

	/**
	 * food Summary shortcode.
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 * @since 1.0.0
	 */
	public function output_food_summary($atts) {
		extract(shortcode_atts(array(
			'id'       => '',
			'width'    => '250px',
			'align'    => 'left',
			'limit'    => 1
		), $atts));

		ob_start();
		$args = array(
			'post_type'   => 'food_manager',
			'post_status' => 'publish'
		);

		if (!$id) {
			$args['posts_per_page'] = $limit;
			$args['orderby']        = 'rand';
		} else {
			$args['p'] = absint($id);
		}

		$foods = new WP_Query(apply_filters('food_manager_food_summary_args',$args));
		if ($foods->have_posts()) : ?>
			<?php while ($foods->have_posts()) : $foods->the_post();
				echo '<div class="food_summary_shortcode align' . esc_attr($align) . '" style="width: ' . esc_attr($width) . '">';
				get_food_manager_template_part('content-summary', 'food_manager');
				echo '</div>';
			endwhile; ?>
		<?php endif;
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * output food menu by menu id.
	 * 		 
	 * @access public
	 * @param array $atts
	 * @return string
	 * @since 1.0.0
	 */
	public function output_food_menu($atts) {
		ob_start();
		$atts = shortcode_atts(array(
			'restaurant_id' => '',
		), $atts);
		
		$restaurant_id = (isset($atts['restaurant_id'])) ? $atts['restaurant_id'] : $_GET['restaurant_id'];
		$search_term = (isset($_GET['search_term'])) ? $_GET['search_term'] : '';
		
		$food_menu_ids = [];
		$food_ids = [];
		$duplicate_records = [];
		?>
		<div id="food-menu-container" class="wpfm-food-menu-page-main-container">
			<?php 
			// Initialize arrays
			$restaurant_ids = [];
			$restaurant_menus = [];
	
			// Query to retrieve all restaurant IDs
			$restaurant_args = array(                
				'post_type'   => 'restaurant_manager',
				'post_status' => 'publish',
				'fields'      => 'ids', 
				'posts_per_page' => -1
			);
			
			if ($restaurant_id) {
				$restaurant_args['p'] = $restaurant_id;
				$restaurant_args['posts_per_page'] = 1;
				$restaurant_query = new WP_Query($restaurant_args);
				if($restaurant_query->found_posts == 0){ 
					error_message_for_menu_page('Invalid restaurant id.');
					return ob_get_clean(); 
				}
			} 
			
			$restaurant_query = new WP_Query($restaurant_args);
			if ($restaurant_query->have_posts()) {
				while ($restaurant_query->have_posts()) { 
					$restaurant_query->the_post();
					$restaurant_menus = get_post_meta(get_the_ID(), '_restaurant_menus', true);    
					if (is_array($restaurant_menus)) {
						$restaurant_ids = array_merge($restaurant_ids, $restaurant_menus);
					}
				}
				// Remove duplicate IDs
				$restaurant_ids = array_unique($restaurant_ids);
			} else {
				// Query all posts of the 'food_manager_menu' post type
				$args = array(
					'post_type'  => 'food_manager_menu',
					'posts_per_page' => -1,
					'post_status'	=> 'publish'
				);

				$query = new WP_Query($args);

				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$post_id = get_the_ID();
						// Get the meta value for this post
						$food_ids = get_post_meta($post_id, '_food_item_ids', true);
					}
				}

				if($food_ids && $restaurant_query->found_posts == 0){ 
					error_message_for_menu_page('No Restaurant found.'); 
					return ob_get_clean();
				}
				else{
					error_message_for_menu_page('No Food Menu are available'); 
					return ob_get_clean();
				}
			}
			wp_reset_postdata();

			if(!$restaurant_menus){
				error_message_for_menu_page('No Food Menu are available'); 
				return ob_get_clean();
			}

			if (!isset($_GET['is_ajax'])) {
				// Query to retrieve all menu titles
				if (!empty($restaurant_ids)) {
					$title_query = food_manager_menu($restaurant_ids);
					if ($title_query->have_posts()) { ?>
						<div class="food-menu-page-filters">
							<div class="wpfm-form-wrapper">
								<div class="wpfm-form-group">
									<form method="GET" action="<?php echo esc_url(get_permalink()); ?>">
										<?php if ($restaurant_id) { ?> 
											<input type="hidden" name="restaurant_id" id="restaurant-id-search" value="<?php echo esc_attr($restaurant_id); ?>">
										<?php } ?>
										<input type="text" name="search_term" id="food-menu-search" value = "<?php echo esc_attr($search_term); ?>" placeholder="Search for food items...">
									</form>
								</div>
							</div> 
							<?php 
							if (empty($_GET['search_term'])) { ?>         
								<div class="food-menu-page-filter-tabs-wrapper">
									<div class="food-menu-page-filter-tabs" id="food-menu-titles">
										<?php 
										if($title_query->have_posts()) {
											while ($title_query->have_posts()) { $title_query->the_post(); ?>
												<div class="food-menu-page-filter-tab">
													<a href="#menu-<?php the_ID(); ?>" class="food-menu-page-filter-tab-link"><?php echo get_the_title(); ?></a>
												</div>
											<?php } wp_reset_postdata();
										} ?>
									</div>
								</div>
							<?php } ?>
						</div>
					<?php 
					}
					wp_reset_postdata();
				}
			} 
			?>
			<div id="food-menu-results">
				<?php
				if (!empty($restaurant_ids)) {
					$food_menus = food_manager_menu($restaurant_ids);
					// Display the specific menu or search results
					if ($food_menus->have_posts()) {
						while ($food_menus->have_posts()) { 
							$food_menus->the_post(); 
							$food_menu_ids = get_menu_list(get_the_ID(), get_the_ID());
							
							if (!empty($food_menu_ids) && is_array($food_menu_ids)) {
								// Check if search term is provided
								if ($search_term) {
									$search_food_items = array(
										'post_type' => 'food_manager',
										'post_status' => 'publish',
										'fields' => 'ids', 
										'posts_per_page' => -1,
										's' => $search_term,
									);

									$food_menu_ids = get_posts($search_food_items);
									if (!empty($food_menu_ids) && is_array($food_menu_ids) && count($duplicate_records) == 0) {
										$duplicate_records[] = $food_menu_ids;
										$food_item_id = $food_menu_ids[0];
										$found_post_id = null;

										// Query all posts of the 'food_manager_menu' post type
										$args = array(
											'post_type'  => 'food_manager_menu',
											'posts_per_page' => -1,
											'post_status'	=> 'publish'
										);

										$query = new WP_Query($args);

										if ($query->have_posts()) {

											while ($query->have_posts()) {
												$query->the_post();
												$post_id = get_the_ID();

												// Get the meta value for this post
												$food_ids = get_post_meta($post_id, '_food_item_ids', true);
												
												// Check if the food item ID is in the array
												if (is_array($food_ids) && in_array($food_item_id, $food_ids)) {
													$found_post_id = $post_id;
													break; // Exit loop once the post is found
												}
											}
											wp_reset_postdata();
										}

										if (isset($found_post_id)) {
											set_query_var('menu_search', $food_menu_ids);
											set_query_var('menu_id', $found_post_id);
										}
									} else {
										continue;
									}
								}

								$food_items = array(
									'post_type' => 'food_manager',
									'post__in'  => $food_menu_ids,
									'orderby'   => 'post__in',
								);

								$food_listings = get_posts($food_items);
							} else {
								$food_listings = array();
							}
							?>
							<div id="menu-<?php the_ID(); ?>" class="food-menu-section">
								<div class="clearfix">
									<?php get_food_manager_template_part('content-single', 'food_manager_menu'); ?>
								</div>
							</div>
						<?php 
						}
						do_action('food_menu_list_end');
					} 
				}

				if (!empty(trim($search_term)) && !isset($found_post_id)) { 
					error_message_for_menu_page('No search result found.');  
				}
				else if(!$food_menu_ids){ 
					error_message_for_menu_page('No Food Menu are available'); 
					return ob_get_clean();
				}
				?>
			</div>
			<div id="food_menu_results_block" class="fm-food-menu-container"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Output food menu title by restaurant ID.
	 * 
	 * @access public
	 * @param array $atts
	 * @return string
	 * @since 1.0.0
	 */
	public function food_menu_title_output_callback_function_for_restaurant($atts) {
		ob_start();
		$atts = shortcode_atts(array(
			'restaurant_id' => '',
			'count' => 'no',
		), $atts);
	
		$restaurant_id = (isset($atts['restaurant_id'])) ? $atts['restaurant_id'] : '';
		$count_display = $atts['count'];
	
		// Check if restaurant ID is provided
		if (!$restaurant_id) {
			error_message_for_menu_page('No restaurant ID provided.');
			return ob_get_clean();
		}
	
		// Query for the restaurant menu
		$restaurant_args = array(
			'post_type'   => 'restaurant_manager',
			'post_status' => 'publish',
			'p'           => $restaurant_id,
			'posts_per_page' => 1
		);
	
		$restaurant_query = new WP_Query($restaurant_args);
		if ($restaurant_query->have_posts()) {
			while ($restaurant_query->have_posts()) {
				$restaurant_query->the_post();
				$restaurant_menus = get_post_meta(get_the_ID(), '_restaurant_menus', true);
				
				if (!empty($restaurant_menus) && is_array($restaurant_menus)) {
					echo '<h2>'. get_the_title() .'</h2>';
					foreach ($restaurant_menus as $menu_id) {
						$menu_title = get_the_title($menu_id);
						$menu_link = get_permalink($menu_id); 
						$food_item_ids = get_post_meta($menu_id, '_food_item_ids', true);
						$food_item_ids_count = is_array($food_item_ids) ? count($food_item_ids) : 0;

                        // Display menu title with count if specified
                        $count_display_text = ($count_display === 'yes') ? ' &nbsp;( ' . intval($food_item_ids_count) . ' )' : '';
						// echo '<div class="menu-title"><a href="' . esc_url($menu_link) . '">' . esc_html($menu_title) . ' &nbsp;( '. intval($food_item_ids_count) . ' )' .'</a></div>';
						echo '<div class="menu-title"><a href="' . esc_url($menu_link) . '">' . esc_html($menu_title) . $count_display_text . '</a></div>';

					}
				} else {
					error_message_for_menu_page('No menus found for this restaurant.');
					
				}
			}
			wp_reset_postdata();
		} else {
			error_message_for_menu_page('Invalid restaurant ID.');
		}
	
		return ob_get_clean();
	} 

	/**
	 * Output food menu by restaurant ID.
	 * 
	 * @access public
	 * @param array $atts
	 * @return string
	 * @since 1.0.0
	 */
	public function food_menu_output_callback_function_for_restaurant($atts) {
		ob_start();
		$atts = shortcode_atts(array(
			'restaurant_id' => '',
		), $atts);
	
		$restaurant_id = (isset($atts['restaurant_id'])) ? $atts['restaurant_id'] : $_GET['restaurant_id'];
		$search_term = (isset($_GET['search_term'])) ? sanitize_text_field($_GET['search_term']) : '';
	
		if (empty($restaurant_id)) {
			error_message_for_menu_page('Restaurant ID is required.');
			return ob_get_clean();
		}
	
		// Get the restaurant title
		$restaurant_title = get_the_title($restaurant_id);
	
		// Get restaurant menus
		$restaurant_menus = get_post_meta($restaurant_id, '_restaurant_menus', true);
	
		if (empty($restaurant_menus) || !is_array($restaurant_menus)) {
			error_message_for_menu_page('No Food Menu available for this restaurant.');
			return ob_get_clean();
		}
	
		// Display the restaurant title, search form, and food menu titles
		if (!isset($_GET['is_ajax'])) {
			?>
			<div class="food-menu-page-filters">
				<h2 class="restaurant-title"><?php echo esc_html($restaurant_title); ?></h2>
				<div class="wpfm-form-wrapper">
					<div class="wpfm-form-group">
						<form method="GET" action="<?php echo esc_url(get_permalink()); ?>">
							<input type="hidden" name="restaurant_id" value="<?php echo esc_attr($restaurant_id); ?>">
							<input type="text" name="search_term" id="food-menu-search" value="<?php echo esc_attr($search_term); ?>" placeholder="Search for food items...">
						</form>
					</div>
				</div>
				<?php if (empty($_GET['search_term'])) { ?>
					<div class="food-menu-page-filter-tabs-wrapper">
						<div class="food-menu-page-filter-tabs" id="food-menu-titles">
							<?php
							$title_query = new WP_Query(array(
								'post_type'      => 'food_manager_menu',
								'post__in'       => $restaurant_menus,
								'posts_per_page' => -1,
								'post_status'    => 'publish'
							));
							if ($title_query->have_posts()) {
								while ($title_query->have_posts()) {
									$title_query->the_post();
									?>
									<div class="food-menu-page-filter-tab">
										<a href="#menu-<?php the_ID(); ?>" class="food-menu-page-filter-tab-link"><?php echo get_the_title(); ?></a>
									</div>
									<?php
								}
								wp_reset_postdata();
							}
							?>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	
		// Query food menus by restaurant
		$args = array(
			'post_type'      => 'food_manager_menu',
			'post__in'       => $restaurant_menus,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
	
		$menu_query = new WP_Query($args);
	
		if ($menu_query->have_posts()) {
			echo '<div id="food-menu-results">';
			while ($menu_query->have_posts()) {
				$menu_query->the_post();
	
				// Get food items in this menu
				$food_menu_ids = get_menu_list($menu_id , get_the_ID());
				if (!empty($food_menu_ids) && is_array($food_menu_ids)) {
					$food_args = array(
						'post_type'      => 'food_manager',
						'post__in'       => $food_menu_ids,
						'orderby'        => 'post__in',
						'posts_per_page' => -1,
						's'              => $search_term,
					);
	
					$food_items = get_posts($food_args);
	
					if ($food_items) {
						?>
						<div id="menu-<?php the_ID(); ?>" class="food-menu-section">
							<div class="clearfix">
								<?php get_food_manager_template_part('content-single', 'food_manager_menu'); ?>
							</div>
						</div>
						<?php
					} else {
						error_message_for_menu_page('No food items found for this menu.');
					}
				}
			}
			echo '</div>';
		} else {
			error_message_for_menu_page('No Food Menu available.');
		}
	
		wp_reset_postdata();
		return ob_get_clean();
	}
	
	/**
	 * output food menu by menu id.
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 * @since 1.0.0
	 */
	public function food_menu_output_callback_function($atts){
		ob_start();
		
		$atts = shortcode_atts(array(
			'id' => '',
		), $atts);
		
		$id = (isset($atts['id'])) ? $atts['id'] : '';
		$food_exists = get_post_meta($id, '_wpfm_food_menu_visibility', true);
		if($food_exists == 'yes'){
			return;
		} else {
			$args = array(
				'post_type'   => 'food_manager_menu',
				'post_status' => 'publish',
				'posts_per_page' => -1,
			);
			
			if(isset($id)){
				$args['p'] = $id;
			}
	
			$food_menus = new WP_Query(apply_filters('food_manager_food_menu_args',$args));
			if ($food_menus->have_posts()) { ?>
				<?php while ($food_menus->have_posts()) : $food_menus->the_post();
					$post_id = get_the_ID();
					// Get the meta value for this post
					$food_ids = get_post_meta($post_id, '_food_item_ids', true);
				?>
					<div class="fm-food-menu-block">
						<?php get_food_manager_template_part('content-single','food_manager_menu'); ?>
					</div>
				<?php endwhile; ?>
			<?php } else{
				error_message_for_menu_page('No Foods are available..'); 
				return ob_get_clean();
			}
			wp_reset_postdata();
		}
		

		return ob_get_clean();
	
	}

	/**
	 * output search food menu by menu id.
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 * @since 1.0.0
	 */
	public function food_menu_output_search_callback_function($atts){
		ob_start();
		$atts = shortcode_atts(array(
			'id' => '',
			'search_term' => '',
			'is_ajax'     => 'false',
		), $atts);
		
		$id = (isset($atts['id'])) ? $atts['id'] : '';
		$search_term = sanitize_text_field($atts['search_term']);
		$args = array(
			'post_type'   => 'food_manager',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);

		if(isset($id)){
			$args['p'] = $id;
		}
		if (!empty($search_term)) {
			$args['s'] = $search_term; // This will search the title and content
		}

		$food_menus = new WP_Query(apply_filters('food_manager_food_menu_args',$args));
		if ($food_menus->have_posts()) { ?>
			<?php while ($food_menus->have_posts()) : $food_menus->the_post();
				$post_id = get_the_ID();
				// Get the meta value for this post
				$food_ids = get_post_meta($post_id, '_food_item_ids', true);
			?>
				<div class="fm-food-menu-block food-list-box search_filter">
					<?php get_food_manager_template_part('content-single','food_manager_menu_list'); ?>
				</div>
			<?php endwhile; ?>
		<?php } else{
			error_message_for_menu_page('No Foods are available..'); 
			return ob_get_clean();
		}
		wp_reset_postdata();

		return ob_get_clean();
	
	}
}

WPFM_Shortcodes::instance();