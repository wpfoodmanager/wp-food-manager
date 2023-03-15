<?php

/**
 * This file is use to create a sortcode of wp food manager plugin. 
 * This file include sortcode of food listing,food submit form and food dashboard etc.
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * WPFM_Shortcodes class.
 */
class WPFM_Shortcodes {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;
	private $food_dashboard_message = '';

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 1.0.1
	 * @static
	 * @return self Main instance.
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
		add_shortcode('add_food', array($this, 'add_food'));
		add_shortcode('food_dashboard', array($this, 'food_dashboard'));
		add_shortcode('foods', array($this, 'output_foods'));
		add_shortcode('food_menu', array($this, 'output_food_menu'));
	}

	/**
	 * Show the food submission form
	 * 
	 * @since 1.0.1
	 * @param array $atts
	 */
	public function add_food($atts = array()) {
		return $GLOBALS['food_manager']->forms->get_form('submit-food', $atts);
	}

	/**
	 * Handles actions on food dashboard
	 * 
	 * @since 1.0.1
	 */
	public static function food_dashboard_handler() {
		if (!empty($_REQUEST['action']) && !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'food_manager_my_food_actions')) {
			$action = sanitize_title($_REQUEST['action']);
			$food_id = absint($_REQUEST['food_id']);
			try {
				// Get food
				$food    = get_post($food_id);
				// Check ownership
				if (!food_manager_user_can_edit_food($food_id)) {
					throw new Exception(__('Invalid ID', 'wp-food-manager'));
				}
				switch ($action) {
					case 'mark_cancelled':
						// Check status
						if ($food->_cancelled == 1)
							throw new Exception(__('This food has already been cancelled', 'wp-food-manager'));
						// Update
						update_post_meta($food_id, '_cancelled', 1);
						// Message
						$this->food_dashboard_message = '<div class="food-manager-message wpfm-alert wpfm-alert-success">' . sprintf(__('%s has been cancelled.', 'wp-food-manager'), esc_html($food->post_title)) . '</div>';
						break;
					case 'mark_not_cancelled':
						// Check status
						if ($food->_cancelled != 1) {
							throw new Exception(__('This food is not cancelled', 'wp-food-manager'));
						}
						// Update
						update_post_meta($food_id, '_cancelled', 0);
						// Message
						$this->food_dashboard_message = '<div class="food-manager-message wpfm-alert wpfm-alert-success">' . sprintf(__('%s has been marked as not cancelled.', 'wp-food-manager'), esc_html($food->post_title)) . '</div>';
						break;
					case 'delete':
						$foods_status = get_post_status($food_id);
						// Trash it
						wp_trash_post($food_id);
						// Message
						if (!in_array($foods_status, ['trash'])) {
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
						// redirect to post page
						wp_redirect(add_query_arg(array('food_id' => absint($food_id)), food_manager_get_permalink('add_food')));
						break;
					default:
						do_action('food_manager_food_dashboard_do_action_' . $action);
						break;
				}
				do_action('food_manager_my_food_do_action', $action, $food_id);
			} catch (Exception $e) {
				$this->food_dashboard_message = '<div class="food-manager-error wpfm-alert wpfm-alert-danger">' . $e->getMessage() . '</div>';
			}
		}
	}

	/**
	 * Shortcode which lists the logged in user's foods
	 * 
	 * @since 1.0.1
	 * @param $atts
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
			// Show alternative content if a plugin wants to
			if (has_action('food_manager_food_dashboard_content_' . $action)) {
				do_action('food_manager_food_dashboard_content_' . $action, $atts);
				return ob_get_clean();
			}
		}
		$search_order_by = 	isset($_GET['search_order_by']) ? sanitize_text_field($_GET['search_order_by']) : '';
		if (isset($search_order_by) && !empty($search_order_by)) {
			$search_order_by = explode('|', $search_order_by);
			$orderby = $search_order_by[0];
			$order = $search_order_by[1];
		} else {
			$orderby = 'date';
			$order = 'desc';
		}
		// If not show the food dashboard
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
		$foods = new WP_Query($args);
		echo $this->food_dashboard_message;
		$food_dashboard_columns = apply_filters('food_manager_food_dashboard_columns', array(
			'food_title' => __('Title', 'wp-food-manager'),
			'view_count' => __('Viewed', 'wp-food-manager'),
			'food_action' => __('Action', 'wp-food-manager'),
		));
		get_food_manager_template('food-dashboard.php', array('foods' => $foods->query($args), 'max_num_pages' => $foods->max_num_pages, 'food_dashboard_columns' => $food_dashboard_columns));
		return ob_get_clean();
	}

	/**
	 * output_foods function.
	 *
	 * @since 1.0.1
	 * @access public
	 * @param mixed $args
	 * @return void
	 */
	public function output_foods($atts) {
		ob_start();
		extract($atts = shortcode_atts(apply_filters('food_manager_output_foods_defaults', array(
			'per_page'                  => get_option('food_manager_per_page'),
			'orderby'                   => 'menu_order', // meta_value
			'order'                     => 'ASC',
			// Filters + cats
			'show_filters'              => true,
			'show_categories'           => true,
			'show_food_types'          => true,
			'show_food_tags'          => true,
			'show_category_multiselect' => get_option('food_manager_enable_default_category_multiselect', false),
			'show_food_type_multiselect' => get_option('food_manager_enable_default_food_type_multiselect', false),
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
		)), $atts));
		//Categories
		if (!get_option('food_manager_enable_categories')) {
			$show_categories = false;
		}
		//food types
		if (!get_option('food_manager_enable_food_types')) {
			$show_food_types = false;
		}
		//food tags
		if (!get_option('food_manager_enable_food_tags')) {
			$show_food_tags = false;
		}
		// String and bool handling
		$show_filters              = $this->string_to_bool($show_filters);
		$show_categories           = $this->string_to_bool($show_categories);
		$show_food_types          = $this->string_to_bool($show_food_types);
		$show_food_tags          = $this->string_to_bool($show_food_tags);
		$show_category_multiselect = $this->string_to_bool($show_category_multiselect);
		$show_food_type_multiselect = $this->string_to_bool($show_food_type_multiselect);
		$show_more                 = $this->string_to_bool($show_more);
		$show_pagination           = $this->string_to_bool($show_pagination);
		// Order by meta value and it will take default sort order by start date of food
		if (is_null($orderby) ||  empty($orderby)) {
			$orderby  = 'menu_order'; //meta_value
		}
		if (!is_null($featured)) {
			$featured = (is_bool($featured) && $featured) || in_array($featured, array('1', 'true', 'yes')) ? true : false;
		}
		if (!is_null($cancelled)) {
			$cancelled = (is_bool($cancelled) && $cancelled) || in_array($cancelled, array('1', 'true', 'yes')) ? true : false;
		}
		// Array handling
		$categories           = is_array($categories) ? $categories : array_filter(array_map('trim', explode(',', $categories)));
		$food_types          = is_array($food_types) ? $food_types : array_filter(array_map('trim', explode(',', $food_types)));
		// Get keywords, location, category and food type from query string if set
		if (!empty($_GET['search_keywords'])) {
			$keywords = sanitize_text_field($_GET['search_keywords']);
		}
		if (!empty($_GET['search_location'])) {
			$location = sanitize_text_field($_GET['search_location']);
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
		if ($show_filters) {
			get_food_manager_template('food-filters.php', array(
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
				'food_types' => $food_types,
				'selected_food_type' => $selected_food_type,
				'atts' => $atts,
				'location' => $location,
				'keywords' => $keywords,
			));
			get_food_manager_template('food-listings-start.php', array('layout_type' => $layout_type));
			get_food_manager_template('food-listings-end.php');
			if (!$show_pagination && $show_more) {
				echo '<a class="load_more_foods" id="load_more_foods" href="javascript:void(0);" style="display:none;"><strong>' . __('Load more foods', 'wp-food-manager') . '</strong></a>';
			}
		} else {
			$foods = get_food_managers(apply_filters('food_manager_output_foods_args', array(
				'search_location'   => $location,
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
		$data_attributes        = array(
			'location'        => $location,
			'keywords'        => $keywords,
			'show_filters'    => $show_filters ? 'true' : 'false',
			'show_pagination' => $show_pagination ? 'true' : 'false',
			'per_page'        => $per_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'categories'      => !empty($selected_category) ? implode(',', $selected_category) : '',
			'food_types'     => !empty($selected_food_type) ? implode(',', $selected_food_type) : '',
		);
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
	 * Output content of food categories
	 * 
	 * @since 1.0.1
	 */
	public function output_foods_categories() { ?>
		<h2>
			<?php _e('Food Categories'); ?>
		</h2>
		<div id="food-listing-view" class="wpfm-main wpfm-food-listings food_listings wpfm-row wpfm-food-listing-box-view">
			<?php get_food_manager_template('content-food-categories.php'); ?>
		</div>
	<?php }

	/**
	 * Output content of food types
	 * 
	 * @since 1.0.1
	 */
	public function output_foods_types() { ?>
		<h2>
			<?php _e('Food Types'); ?>
		</h2>
		<div id="food-listing-view" class="wpfm-main wpfm-food-listings food_listings wpfm-row wpfm-food-listing-box-view">
			<?php get_food_manager_template('content-food-types.php'); ?>
		</div>
		<?php
	}

	/**
	 * Get string as a bool
	 * 
	 * @since 1.0.1
	 * @param  string $value
	 * @return bool
	 */
	public function string_to_bool($value) {
		return (is_bool($value) && $value) || in_array($value, array('1', 'true', 'yes')) ? true : false;
	}

	/**
	 * output_food function.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args
	 * @return string
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
		$foods = new WP_Query($args);
		if ($foods->have_posts()) : ?>
			<?php while ($foods->have_posts()) : $foods->the_post(); ?>
				<div class="clearfix">
					<?php get_food_manager_template_part('content-single', 'food_manager'); ?>
				<?php endwhile; ?>
			<?php endif;
		wp_reset_postdata();
		return '<div class="food_shortcode single_food_manager">' . ob_get_clean() . '</div>';
	}

	/**
	 * food Summary shortcode
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_food_summary($atts) {
		extract(shortcode_atts(array(
			'id'       => '',
			'width'    => '250px',
			'align'    => 'left',
			'featured' => null, // True to show only featured, false to hide featured, leave null to show both (when leaving out id)
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
			if (!is_null($featured)) {
				$args['meta_query'] = array(array(
					'key'     => '_featured',
					'value'   => '1',
					'compare' => $featured ? '=' : '!='
				));
			}
		} else {
			$args['p'] = absint($id);
		}
		$foods = new WP_Query($args);
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
	 * output food menu
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_food_menu($atts) {
		ob_start();
		extract(shortcode_atts(array(
			'id' => '',
		), $atts));
		$args = array(
			'post_type'   => 'food_manager_menu',
			'post_status' => 'publish',
			'p'           => $id
		);
		$food_menus = new WP_Query($args);
		if ($food_menus->have_posts()) : ?>
				<?php while ($food_menus->have_posts()) : $food_menus->the_post(); ?>
					<div class="clearfix">
						<?php get_food_manager_template_part('content-single', 'food_manager_menu'); ?>
					<?php endwhile; ?>
		<?php endif;
		wp_reset_postdata();
		return ob_get_clean();
	}
}

WPFM_Shortcodes::instance();
