<?php

/**
 * This file the functionality of ajax for food listing and file upload.
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * WP_food_Manager_Ajax class.
 */
class WPFM_Ajax {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 1.0.0
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
		add_action('init', array(__CLASS__, 'add_endpoint'));
		add_action('template_redirect', array(__CLASS__, 'do_fm_ajax'), 0);
		// FM Ajax endpoints
		add_action('food_manager_ajax_get_listings', array($this, 'get_listings'));
		add_action('food_manager_ajax_upload_file', array($this, 'upload_file'));
		// BW compatible handlers
		add_action('wp_ajax_nopriv_food_manager_get_listings', array($this, 'get_listings'));
		add_action('wp_ajax_food_manager_get_listings', array($this, 'get_listings'));
		add_action('wp_ajax_nopriv_food_manager_upload_file', array($this, 'upload_file'));
		add_action('wp_ajax_food_manager_upload_file', array($this, 'upload_file'));
		add_action('wp_ajax_wpfm_extra_option_tab', array($this, 'get_fieldtype_action'));
		add_action('wp_ajax_wpfm-logo-update-menu-order', array($this, 'menuUpdateOrder'));
	}

	/**
	 * Add our endpoint for frontend ajax requests
	 */
	public static function add_endpoint() {
		add_rewrite_tag('%fm-ajax%', '([^/]*)');
		add_rewrite_rule('fm-ajax/([^/]*)/?', 'index.php?fm-ajax=$matches[1]', 'top');
		add_rewrite_rule('index.php/fm-ajax/([^/]*)/?', 'index.php?fm-ajax=$matches[1]', 'top');
	}

	/**
	 * Get food Manager Ajax Endpoint
	 * @param  string $request Optional
	 * @param  string $ssl     Optional
	 * @return string
	 */
	public static function get_endpoint($request = '%%endpoint%%', $ssl = null) {
		if (strstr(get_option('permalink_structure'), '/index.php/')) {
			$endpoint = trailingslashit(home_url('/index.php/fm-ajax/' . $request . '/', 'relative'));
		} elseif (get_option('permalink_structure')) {
			$endpoint = trailingslashit(home_url('/fm-ajax/' . $request . '/', 'relative'));
		} else {
			$endpoint = add_query_arg('fm-ajax', $request, trailingslashit(home_url('', 'relative')));
		}
		return esc_url_raw($endpoint);
	}

	/**
	 * Check for WC Ajax request and fire action
	 */
	public static function do_fm_ajax() {
		global $wp_query;
		if (!empty($_GET['fm-ajax'])) {
			$wp_query->set('fm-ajax', sanitize_text_field($_GET['fm-ajax']));
		}
		if ($action = $wp_query->get('fm-ajax')) {
			if (!defined('DOING_AJAX')) {
				define('DOING_AJAX', true);
			}
			// Not home - this is an ajax endpoint
			$wp_query->is_home = false;
			do_action('food_manager_ajax_' . sanitize_text_field($action));
			die();
		}
	}

	/**
	 * Category order update.
	 *
	 * @return void|bool
	 */
	public function menuUpdateOrder() {
		global $wpdb;
		$data = (!empty($_POST['post']) ? $_POST['post'] : []);
		if (!is_array($data)) {
			return false;
		}
		$id_arr = [];
		foreach ($data as $position => $id) {
			$id_arr[] = $id;
		}
		$menu_order_arr = [];
		foreach ($id_arr as $key => $id) {
			$results = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($id));
			foreach ($results as $result) {
				$menu_order_arr[] = $result->menu_order;
			}
		}
		sort($menu_order_arr);
		array_unshift($data, "");
		unset($data[0]);
		foreach ($data as $key => $id) {
			$wpdb->update($wpdb->posts, ['menu_order' => $key], ['ID' => intval($id)]);
		}
		wp_send_json_success();
	}

	/**
	 * Get listings via ajax
	 */
	public function get_listings() {
		global $wp_post_types;
		$result            = array();
		$search_location   = sanitize_text_field(stripslashes($_REQUEST['search_location']));
		$search_keywords   = sanitize_text_field(stripslashes($_REQUEST['search_keywords']));
		$search_categories = isset($_REQUEST['search_categories']) ? $_REQUEST['search_categories'] : '';
		$search_food_types = isset($_REQUEST['search_food_types']) ? $_REQUEST['search_food_types'] : '';
		$post_type_label   = $wp_post_types['food_manager']->labels->name;
		$orderby           = sanitize_text_field($_REQUEST['orderby']);
		if (is_array($search_categories)) {
			$search_categories = array_filter(array_map('sanitize_text_field', array_map('stripslashes', $search_categories)));
		} else {
			$search_categories = array_filter(array(sanitize_text_field(stripslashes($search_categories))));
		}
		if (is_array($search_food_types)) {
			$search_food_types = array_filter(array_map('sanitize_text_field', array_map('stripslashes', $search_food_types)));
		} else {
			$search_food_types = array_filter(array(sanitize_text_field(stripslashes($search_food_types))));
		}
		$args = array(
			'search_location'    => $search_location,
			'search_keywords'    => $search_keywords,
			'search_categories'  => $search_categories,
			'search_food_types'  => $search_food_types,
			'orderby'            => $orderby,
			'order'              => sanitize_text_field($_REQUEST['order']),
			'offset'             => (absint($_REQUEST['page']) - 1) * absint($_REQUEST['per_page']),
			'posts_per_page'     => absint($_REQUEST['per_page'])
		);
		if (isset($_REQUEST['featured']) && ($_REQUEST['featured'] === 'true' || $_REQUEST['featured'] === 'false')) {
			$args['featured'] = $_REQUEST['featured'] === 'true' ? true : false;
			$args['orderby']  = 'featured' === $orderby ? 'date' : $orderby;
		}
		ob_start();
		$foods = get_food_listings(apply_filters('food_manager_get_listings_args', $args));
		$result['found_foods'] = false;
		if ($foods->have_posts()) : $result['found_foods'] = true; ?>
			<?php while ($foods->have_posts()) : $foods->the_post(); ?>
				<?php get_food_manager_template_part('content', 'food_manager'); ?>
			<?php endwhile; ?>
			<?php else :
			// Check there is a publish food or not.
			$default_foods = get_posts(array(
				'numberposts' => -1,
				'post_type'   => 'food_manager',
				'post_status'   => 'publish'
			));
			if (count($default_foods) == 0) { ?>
				<div class="no_food_listings_found wpfm-alert wpfm-alert-danger"><?php _e('There is no food item listed in your food manager.', 'wp-food-manager'); ?></div>
			<?php } else {
				get_food_manager_template_part('content', 'no-foods-found');
			}
		endif;
		$result['html']    = ob_get_clean();
		$result['filter_value'] = array();
		// Categories
		if ($search_categories) {
			$showing_categories = array();
			foreach ($search_categories as $category) {
				$category_object = get_term_by(is_numeric($category) ? 'id' : 'slug', $category, 'food_manager_category');
				if (!is_wp_error($category_object)) {
					$showing_categories[] = $category_object->name;
				}
			}
			$result['filter_value'][] = implode(', ', $showing_categories);
		}
		// Food types
		if ($search_food_types) {
			$showing_food_types = array();
			foreach ($search_food_types as $food_type) {
				$food_type_object = get_term_by(is_numeric($food_type) ? 'id' : 'slug', $food_type, 'food_manager_type');
				if (!is_wp_error($food_type_object)) {
					$showing_food_types[] = $food_type_object->name;
				}
			}
			$result['filter_value'][] = implode(', ', $showing_food_types);
		}
		if ($search_keywords) {
			$result['filter_value'][] = '&ldquo;' . $search_keywords . '&rdquo;';
		}
		$last_filter_value = array_pop($result['filter_value']);
		$result_implode = implode(', ', $result['filter_value']);
		if (count($result['filter_value']) >= 1) {
			$result['filter_value'] = explode(" ",  $result_implode);
			$result['filter_value'][] =  " &amp; ";
		} else {
			if (!empty($last_filter_value))
				$result['filter_value'] = explode(" ",  $result_implode);
		}
		$result['filter_value'][] =  $last_filter_value . " " . $post_type_label;
		if ($search_location) {
			$result['filter_value'][] = sprintf(__('located in &ldquo;%s&rdquo;', 'wp-food-manager'), $search_location);
		}
		if (sizeof($result['filter_value']) > 1) {
			$message = sprintf(_n('Search completed. Found %d matching record.', 'Search completed. Found %d matching records.', $foods->found_posts, 'wp-food-manager'), $foods->found_posts);
			$result['showing_applied_filters'] = true;
		} else {
			$message = "";
			$result['showing_applied_filters'] = false;
		}
		$search_values = array(
			'location'   => $search_location,
			'keywords'   => $search_keywords,
			'types'		 => $search_food_types,
			'categories' => $search_categories
		);
		$result['filter_value'] = apply_filters('food_manager_get_listings_custom_filter_text', $message, $search_values);
		// Generate RSS link
		$result['showing_links'] = wpfm_get_filtered_links(array(
			'search_keywords'   => $search_keywords,
			'search_location'   => $search_location,
			'search_categories' => $search_categories,
			'search_food_types' => $search_food_types,
		));
		// Generate pagination
		if (isset($_REQUEST['show_pagination']) && $_REQUEST['show_pagination'] === 'true') {
			$result['pagination'] = get_food_listing_pagination($foods->max_num_pages, absint($_REQUEST['page']));
		}
		$result['max_num_pages'] = $foods->max_num_pages;
		wp_send_json(apply_filters('food_manager_get_listings_result', $result, $foods));
	}

	/**
	 * Upload file via ajax
	 *
	 * No nonce field since the form may be statically cached.
	 */
	public function upload_file() {
		if (!food_manager_user_can_upload_file_via_ajax()) {
			wp_send_json_error(new WP_Error('upload', __('You must be logged in to upload files using this method.', 'wp-food-manager')));
			return;
		}
		$data = array('files' => array());
		if (!empty($_FILES)) {
			foreach ($_FILES as $file_key => $file) {
				$files_to_upload = wpfm_prepare_uploaded_files($file);
				foreach ($files_to_upload as $file_to_upload) {
					$uploaded_file = wpfm_upload_file($file_to_upload, array('file_key' => $file_key));
					if (is_wp_error($uploaded_file)) {
						$data['files'][] = array('error' => $uploaded_file->get_error_message());
					} else {
						$data['files'][] = $uploaded_file;
					}
				}
			}
		}
		wp_send_json($data);
	}
}

WPFM_Ajax::instance();
