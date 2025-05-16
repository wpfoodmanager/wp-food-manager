<?php
// Start session to use for storing the error message temporarily
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WPFM_Import class used to import food and foodmenus.
 */
class WPFM_Import{

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
     * @static
     * @return self Main instance.
     * @since 1.0.0
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * __construct function.
     * @access public
     * @return void
     */
    public function __construct() {
        add_action('admin_init',  array($this, 'wpfm_export_menu_csv'));	
        add_action('restrict_manage_posts', array($this, 'export_menu_button'), 10, 2);  // Add to the table's 'alignleft actions'
    }

    /**
     * This function is used to show export button
     * @since 1.0.8
     */
    public function export_menu_button() {
        if (isset($_GET['post_type']) && $_GET['post_type'] == 'food_manager_menu') {
            echo '<div class="alignleft actions">
                    <a href="' . esc_url(add_query_arg('wpfm_export_menu_csv', '1')) . '" class="button" style="margin-left: 10px;">' . esc_html__('Export to CSV', 'wp-food-manager') . '</a>
                  </div>';
        }
    }

    /**
     * Output Setup page.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function output() {
        global $wpdb;
        wp_enqueue_media();

        // Check for error message in session
        if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])) {
            $error_message = $_SESSION['error_message']; // Get error message from session
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html($error_message) . '</p>';
            echo '</div>';
            // Unset error message from session to prevent it from showing on next page load
            unset($_SESSION['error_message']);
        }

        if (!empty($_POST['wp_food_manager_upload']) && wp_verify_nonce($_POST['_wpnonce'], 'food_manager_file_upload')) {
            if ($_POST['action'] == 'upload' && $_POST['file_id'] != '') {
                $file = get_attached_file(absint($_POST['file_id']));
                $file_data = $this->get_wpfm_import_file_data($_POST['file_type'], $file);
                $file_head_fields = array_shift($file_data);
                if (in_array('_menu_title', $file_head_fields)) {
                    // Check if the food_post_type is 'food_manager'
                    if (sanitize_text_field($_POST['food_post_type']) == 'food_manager') {
                        // Store error message in session
                        $_SESSION['error_message'] = __('Please select Food Menu as a content type.', 'wp-food-manager');
                        wp_redirect(admin_url('admin.php?page=food-manager-import'));
                        exit; 
                    }
                } elseif (in_array('_food_title', $file_head_fields)) {
                    // Check if the food_post_type is 'food_manager_menu'
                    if (sanitize_text_field($_POST['food_post_type']) == 'food_manager_menu') {
                        // Store error message in session
                        $_SESSION['error_message'] = __('Please select Food as a content type.', 'wp-food-manager');
                        wp_redirect(admin_url('admin.php?page=food-manager-import'));
                        exit; 
                    }
                }else {
                    $_SESSION['error_message'] = __('Please upload proper csv file', 'wp-food-manager');
                    wp_redirect(admin_url('admin.php?page=food-manager-import'));
                    exit; 
                }
                
                $food_import_fields = get_wpfm_food_form_field_list(sanitize_text_field($_POST['food_post_type']));
                $taxonomies = get_object_taxonomies(sanitize_text_field($_POST['food_post_type']), 'objects');
                $food_post_type = get_wpfm_food_post_type();
                $import_type_label = $food_post_type[sanitize_text_field($_POST['food_post_type'])];
                get_food_manager_template(
                    'food-mapping-form.php',
                    array(
                        'file_id' => sanitize_text_field($_POST['file_id']),
                        'file_type' => sanitize_text_field($_POST['file_type']),
                        'file_head_fields' => $file_head_fields,
                        'food_import_fields' => $food_import_fields,
                        'import_type_label' => $import_type_label,
                        'food_post_type' => sanitize_text_field($_POST['food_post_type']),
                        'taxonomies' => $taxonomies,
                    ),
                    'wp-food-manager',
                    WPFM_PLUGIN_DIR . '/admin/templates/'
                );
            }
        }else if (!empty($_POST['wp_food_manager_mapping']) && wp_verify_nonce($_POST['_wpnonce'], 'food_manager_mapping')) {
            $import_fields = [];
            if (!empty($_POST['food_import_field']) && is_array($_POST['food_import_field'])) {
                foreach ($_POST['food_import_field'] as $key => $field) {
                    if ($field != '') {
                        if ($field == 'custom_field') {
                            $field = sanitize_text_field($_POST['custom_field'][$key]);
                        }
                        $file_field = [];
                        $file_field['key'] = $key;
                        $file_field['file_field'] = sanitize_text_field($_POST['file_field'][$key]);
                        $file_field['taxonomy'] = sanitize_text_field($_POST['taxonomy_field'][$key]);
                        $file_field['default_value'] = sanitize_text_field($_POST['default_value'][$key]);
                        $import_fields[$field] = $file_field;
                    }
                }
            }

            update_option('wpfm_food_import_fields', $import_fields);
            if ($_POST['action'] == 'mapping' && $_POST['file_id'] != '') {
                $file = get_attached_file($_POST['file_id']);
                $file_data = $this->get_wpfm_import_file_data(sanitize_text_field($_POST['file_type']), $file);
                $file_head_fields = array_shift($file_data);
                $file_sample_data = $file_data[0];
                $sample_data = [];
                foreach ($import_fields as $field_name => $field_data) {
                    $value = !empty($file_sample_data[$field_data['key']]) ? $file_sample_data[$field_data['key']] : $field_data['default_value'];
                    $sample_data[$field_name] = $value;
                }
                $food_post_type = get_wpfm_food_post_type();
                $import_type_label = $food_post_type[sanitize_text_field($_POST['food_post_type'])];
                get_food_manager_template(
                    'food-import.php',
                    array(
                        'file_id' => sanitize_text_field($_POST['file_id']),
                        'file_type' => sanitize_text_field($_POST['file_type']),
                        'import_fields' => $import_fields,
                        'food_post_type' => sanitize_text_field($_POST['food_post_type']),
                        'sample_data' => $sample_data,
                        'import_type_label' => $import_type_label,
                    ),
                    'wp-food-manager',
                    WPFM_PLUGIN_DIR . '/admin/templates/'
                );
            }
        }  else if (!empty($_POST['wp_food_manager_import']) && wp_verify_nonce($_POST['_wpnonce'], 'food_manager_import')) {
            if ($_POST['action'] == 'import' && $_POST['file_id'] != '') {
                $import_fields = get_option('wpfm_food_import_fields', true);

                $file = get_attached_file(sanitize_text_field($_POST['file_id']));
                $file_data = $this->get_wpfm_import_file_data(sanitize_text_field($_POST['file_type']), $file);
                $file_head_fields = array_shift($file_data);
                if (!empty($file_data)) {
                    for ($i = 0; $i < count($file_data); $i++) {
                        $import_data = [];
                        foreach ($import_fields as $field_name => $field_date) {
                            if(array_key_exists($field_date['key'],$file_data[$i])){
                                $import_data[$field_name] = $file_data[$i][$field_date['key']];
                            }
                        }
                        $this->wpfm_import_food(sanitize_text_field($_POST['food_post_type']), $import_data);
                    }
                }

                $food_post_type = get_wpfm_food_post_type();
                $import_type_label = $food_post_type[sanitize_text_field($_POST['food_post_type'])];
                get_food_manager_template(
                    'food-import-success.php',
                    array(
                        'total_records' => count($file_data),
                        'import_type_label' => $import_type_label,
                        'food_post_type' => sanitize_text_field($_POST['food_post_type']),
                    ),
                    'wp-food-manager',
                    WPFM_PLUGIN_DIR . '/admin/templates/'
                );
            }
        } else {
            $food_post_type = get_wpfm_food_post_type();
            get_food_manager_template(
                'food-file-upload.php',
                array(
                    'food_post_type' => $food_post_type,
                ),
                'wp-food-manager',
                WPFM_PLUGIN_DIR . '/admin/templates/'
            );
        }
    }

    /**
     * Export filtered posts as a CSV file.
     * 
     * The CSV file is generated and downloaded directly via the browser.
     * 
     * @access public
     * @return void
     */
    public function wpfm_export_menu_csv($msg) {
        if (isset($_GET['wpfm_export_menu_csv'])) { // phpcs:ignore
            // Check user capabilities
            if (!current_user_can('manage_options')) {
                return;
            }
            $this->wpfm_export_menu_csv_file('food-manager-menu');
            exit;
        }
    }
    
	/**
	 * Export food manager data as CSV file
	 * 
	 * @param string $message
	 * @return void
	 */
	public function wpfm_export_menu_csv_file($message) {
		// Setup WP_Query to get the posts
		$query = new WP_Query(array(
			'post_type'      => 'food_manager_menu',
			'posts_per_page' => -1,
		));
	
		// Prepare headers to generate a CSV file with the dynamic filename
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . sanitize_file_name($message) . '.csv"');
	
		// Load WP_Filesystem API if not already loaded
		if (!function_exists('get_filesystem_method')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
	
		$output = fopen('php://output', 'w'); // phpcs:ignore
	
		// Output column headers in the CSV file
		fputcsv($output, apply_filters('wpfm_reservation_export_file_headers', array(
			__('_post_id', 'wp-food-manager'),__('_menu_title', 'wp-food-manager'),__('_wpfm_radio_icons', 'wp-food-manager'),__('_thumbnail_id', 'wp-food-manager'),
			__('_wpfm_disable_food_redirect', 'wp-food-manager'),__('_wpfm_disable_food_image', 'wp-food-manager'),__('_wpfm_food_menu_visibility', 'wp-food-manager'),
			__('_food_menu_option', 'wp-food-manager'),
			__('_food_item_ids', 'wp-food-manager'),__('_food_cats_ids', 'wp-food-manager'),__('_food_type_ids', 'wp-food-manager'),__('_wpfm_food_menu_by_days', 'wp-food-manager'),
		)));
	
		// Loop through the posts and add each row to the CSV
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
	
				// Get post meta
				$menu_by_days_data = get_post_meta(get_the_ID(), '_wpfm_food_menu_by_days', true);
				$menu_by_days = maybe_unserialize($menu_by_days_data);
				$thumbnail_url = wp_get_attachment_url(get_post_meta(get_the_ID(), '_thumbnail_id', true));
	
				// Fetch and unserialize relevant fields
				$food_item_ids = maybe_unserialize(get_post_meta(get_the_ID(), '_food_item_ids', true));
				$food_item_ids = is_array($food_item_ids) ? implode(', ', $food_item_ids) : '';
				
				$food_cats_ids = maybe_unserialize(get_post_meta(get_the_ID(), '_food_cats_ids', true));
				$food_type_ids = maybe_unserialize(get_post_meta(get_the_ID(), '_food_type_ids', true));
				$get_menu_option = get_post_meta(get_the_ID(), '_food_menu_option', true);
				// Fetch category and type names
				if($get_menu_option == 'static_menu'){
					$food_cats_names = '';
					if (!empty($food_cats_ids)) {
						$food_cats_names = $this->get_wpfm_term_names_from_ids($food_cats_ids, 'food_manager_category');
					}
					$food_type_names = '';
					if (!empty($food_type_ids)) {
						$food_type_names = $this->get_wpfm_term_names_from_ids($food_type_ids, 'food_manager_type');
					}
				}elseif($get_menu_option == 'dynamic_menu') {
					$food_cats_names = '';
					$food_type_names = '';
				}
	
				if (is_array($menu_by_days)) {
					foreach ($menu_by_days as $day => &$data) {
						if (isset($data['food_categories']) && is_array($data['food_categories'])) {
							$category_names = array();
							foreach ($data['food_categories'] as $category_id) {
								$term = get_term($category_id, 'food_manager_category');
								if (!is_wp_error($term) && $term) {
									$category_names[] = $term->name;
								}
							}
							$data['food_categories'] = $category_names;
						}
	
						if (isset($data['food_types']) && is_array($data['food_types'])) {
							$type_names = array();
							foreach ($data['food_types'] as $type_id) {
								$term = get_term($type_id, 'food_manager_type');
								if (!is_wp_error($term) && $term) {
									$type_names[] = $term->name;
								}
							}
							$data['food_types'] = $type_names;
						}
					}
				}				
				$json_menu_by_days = json_encode($menu_by_days);
				
				// Prepare meta values for the CSV
				$meta_values = array(
					'post_id' => get_the_ID(),
					'menu_title' => get_the_title(),
					'wpfm_radio_icons' => get_post_meta(get_the_ID(), 'wpfm_radio_icons', true),
					'thumbnail_id' => $thumbnail_url,
					'wpfm_disable_food_redirect' => get_post_meta(get_the_ID(), '_wpfm_disable_food_redirect', true),
					'wpfm_disable_food_image' => get_post_meta(get_the_ID(), '_wpfm_disable_food_image', true),
					'wpfm_food_menu_visibility' => get_post_meta(get_the_ID(), '_wpfm_food_menu_visibility', true),
					'food_menu_option' => get_post_meta(get_the_ID(), '_food_menu_option', true),
					'food_item_ids' => $food_item_ids,
					'food_cats_ids' => $food_cats_names,
					'food_type_ids' => $food_type_names,
					'wpfm_food_menu_by_days' => $json_menu_by_days,
				);
	
				// Write the row data to CSV
				fputcsv($output, apply_filters('wpfm_reservation_export_file_data', array_values($meta_values), get_the_ID(), $meta_values));
			}
		} else {
			fputcsv($output, array('No records found'));
		}
		fclose($output); // Close output stream
		exit;
	}

    /**
     *Function to get term names from term IDs.
    * 
    * @param $term_ids, $taxonomy
    * @return $terms
    */
    public function get_wpfm_term_names_from_ids($term_ids, $taxonomy) {
        if (is_array($term_ids)) {
            // Get all terms in the taxonomy, including those that may not be assigned to any food items.
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'include' => $term_ids,
                'fields' => 'names',
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => false, // This ensures that all terms are included, even if not assigned to any food.
            ));
            
            // If there's an error, return an empty string, otherwise return the terms as a comma-separated list
            return is_wp_error($terms) ? '' : implode(', ', $terms);
        }
        return '';
    }

    /**
     * get_file_data function.
     *
     * @access public
     * @param $type, $file
     * @return array
     */
    public function get_wpfm_import_file_data($type, $file) {
        $file_data = [];
        if ($type == 'csv') {
            $file_data = $this->wpfm_get_csv_file_data($file);
        }
        do_action('wpfm_food_get_file_data', $file, $type);
        $file_data = apply_filters('wpfm_update_file_data', $file_data, $type);
        return $file_data;
    }
    
    /**
     * wpfm_get_csv_file_data function.
     *
     * @access public
     * @param $file
     * @return array
     * @since 1.0
     */
    public function wpfm_get_csv_file_data($file) {
        $csv_data = [];
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                $csv_data[] = $data;
            }
            fclose($handle);
        }
        return $csv_data;
    }

    /**
     * This function is used to import food data based on selected file
     * @since 1.0.8
     */
    public function wpfm_import_food($post_type, $params) {
        $user_id = get_current_user_id();
        global $wpdb;
        
        // Check if $params is a WP_Error
        if (is_wp_error($params)) {
            return; 
        }

        $post_id = '';
        if (isset($params['_post_id']) && $params['_post_id'] != '') {
            $type = get_post_type($params['_post_id']);
            if ($post_type == $type) {
                $post_id = $params['_post_id'];
            }
        }
        if ($post_type == 'food_manager') {
            $post_title = !empty($params['_food_title']) ? $params['_food_title'] : '';
            $post_description = !empty($params['_food_description']) ? $params['_food_description'] : '';
        } else if ($post_type == 'food_manager_menu') {
            $post_title = !empty($params['_menu_title']) ? $params['_menu_title'] : '';
            $post_description ='';
        }
        $post_title = apply_filters('wpfm_food_import_set_post_title', $post_title, $params);
        $args = [
            'post_title' => $post_title,
            'post_type' => $post_type,
            'post_author' => $user_id,
            'comment_status' => 'closed',
            'post_status' => 'publish',
        ];
        $post_id = wp_insert_post($args);

        // Update product or process further
        if ($post_type == 'food_manager') {
            $this->wpfm_import_food_data($post_id, $post_type, $params);
        } else if ($post_type == 'food_manager_menu') {
            $this->wpfm_import_food_menu_data($post_id, $post_type, $params);
        }
        
        do_action('wpfm_food_import_file_data', $post_id, $post_type, $params);	
    }

    /**
     * wpfm_import_food_data function.
     *
     * @access public
     * @param $post_id, $post_type, $params
     * @return 
     */
    public function wpfm_import_food_data($post_id, $post_type, $params) {
        if (!$post_id) return;

        // Prepare post data for update
        $update_food = ['ID' => $post_id];
        if (!empty($params['_food_title'])) $update_food['post_title'] = sanitize_text_field($params['_food_title']);
        if (!empty($params['_food_description'])) $update_food['post_content'] = sanitize_textarea_field($params['_food_description']);

        wp_update_post($update_food);
        $wpfm_food_import_fields = get_option('wpfm_food_import_fields', true);

        // Handle banner image
        foreach ($params as $meta_key => $meta_value) {
            if (empty($wpfm_food_import_fields[$meta_key])) continue;
            $import_fields = $wpfm_food_import_fields[$meta_key];

            if ($meta_key == '_food_banner') {
                $this->wpfm_import_food_file_upload($post_id,$meta_value,$meta_key,$params);
            }elseif ($import_fields['taxonomy'] != '') {
                if($meta_key == 'food_manager_ingredient'){
                    $this->wpfm_import_food_ingredient($post_id, $meta_value);
                }elseif ($meta_key == 'food_manager_nutrition') {
                    $this->wpfm_import_food_nutrition($post_id, $meta_value);
                }else {
                    $this->wpfm_import_food_taxonomy_terms($post_id,  $meta_key, $meta_value, $import_fields);
                }
            }
            elseif (($meta_key == '_topping_names') || ($meta_key == '_topping_description') || ($meta_key == '_topping_image')|| ($meta_key == '_topping_options')) {
                    $this->wpfm_import_food_topping_data($post_id, $params );
            }else {
                $this->wpfm_import_food_post_meta($post_id, $meta_key, $meta_value, $import_fields,$params);
            }
        }
    }

    /**
     * wpfm_import_food_menu_data function.
     *
     * @access public
     * @param $post_id, $post_type, $params
     * @return 
     */
    public function wpfm_import_food_menu_data($post_id, $post_type, $params) {
        if ($post_id != '') {
            // Set the post title and content (description)
            $post_title = !empty($params['_menu_title']) ? $params['_menu_title'] : '';
            
            // Create or update the post
            $update_menu = ['ID' => $post_id];
            if ($post_title != '') {
                $update_menu['post_title'] = $post_title;
            }

            // Update the post in WordPress
            wp_update_post($update_menu);

            if(isset($params['_post_id']) && !empty($params['_post_id']))
                update_post_meta($post_id, '_post_id', $params['_post_id']);

            // Handle fields and update post meta
            $wpfm_radio_icons = isset($params['_wpfm_radio_icons']) ? $params['_wpfm_radio_icons'] : '';
            $wpfm_disable_food_redirect = isset($params['_wpfm_disable_food_redirect']) ? $params['_wpfm_disable_food_redirect'] : '';
            $wpfm_disable_food_image = isset($params['_wpfm_disable_food_image']) ? $params['_wpfm_disable_food_image'] : '';
            $wpfm_food_menu_visibility = isset($params['_wpfm_food_menu_visibility']) ? $params['_wpfm_food_menu_visibility'] : '';
            $food_menu_option = isset($params['_food_menu_option']) ? $params['_food_menu_option'] : '';
            $image_url = isset($params['_thumbnail_id']) ? $params['_thumbnail_id'] : '';

            if (!empty($image_url)) {
                $response = $this->wpfm_check_import_image_exists($image_url);
                if ($response == 'true' || $response == 'false' ) {
                    $image = $this->wpfm_upload_import_image($image_url);
                    if (!empty($image)) {
                        $imageData =  $image['image_url'];
                        $image_post_id = attachment_url_to_postid($imageData);
                        if ($image_post_id) {
                            update_post_meta($post_id, '_thumbnail_id', $image_post_id);
                        }
                    }
                }
            }

            $food_cats_ids = isset($params['_food_cats_ids']) ? $this->wpfm_get_term_id_by_name(explode(", ", $params['_food_cats_ids']), 'food_manager_category') : array();
            update_post_meta($post_id, '_food_cats_ids', $food_cats_ids);
            $food_type_ids = isset($params['_food_type_ids']) ? $this->wpfm_get_term_id_by_name(explode(", ", $params['_food_type_ids']), 'food_manager_type') : array();
            update_post_meta($post_id, '_food_type_ids', $food_type_ids);

            // Handle other fields as normal
            update_post_meta($post_id, 'wpfm_radio_icons', $wpfm_radio_icons);
            update_post_meta($post_id, '_wpfm_disable_food_redirect', $wpfm_disable_food_redirect);
            update_post_meta($post_id, '_wpfm_disable_food_image', $wpfm_disable_food_image);
            update_post_meta($post_id, '_wpfm_food_menu_visibility', $wpfm_food_menu_visibility);
            update_post_meta($post_id, '_food_menu_option', $food_menu_option);
            
            // Initialize food IDs with empty arrays if not set
            $food_item_ids = isset($params['_food_item_ids']) ? $this->wpfm_check_imported_food_ids(explode(", ", $params['_food_item_ids'])) : array();
            update_post_meta($post_id, '_food_item_ids', $food_item_ids);

            // Handle 'food menu by days' field
            if (isset($params['_wpfm_food_menu_by_days'])) {
                $menu_by_days_data = json_decode($params['_wpfm_food_menu_by_days'], true);
                if (is_array($menu_by_days_data)) {
                    foreach ($menu_by_days_data as $day => &$data) {
                        if (isset($data['food_categories'])) {
                            $data['food_categories'] = $this->wpfm_get_term_id_by_name($data['food_categories'], 'food_manager_category');
                        }
                        if (isset($data['food_types'])) {
                            $data['food_types'] = $this->wpfm_get_term_id_by_name($data['food_types'], 'food_manager_type');
                        }
                        if(isset($data['food_items']) && !empty($data['food_items'])){
                            $data['food_items'] = $this->wpfm_check_imported_food_ids($data['food_items']);
                        }
                    }
                }
                update_post_meta($post_id, '_wpfm_food_menu_by_days', $menu_by_days_data);
            }
        }
    }
    				
    /**
     * get food id if exist based on parent id.
     *
     * @param $term_names, $taxonomy
     * @return $term_ids
     */
    public function wpfm_check_imported_food_ids($food_item_ids) {
        $new_food_ids = [];
        if (!empty($food_item_ids)) {
            foreach ($food_item_ids as $item_id) {
                // Query posts of type 'food_manager' with meta key '_post_id' matching $item_id
                $food_query = new WP_Query(array(
                    'post_type'  => 'food_manager',
                    'meta_query' => array(
                        array(
                            'key'     => '_post_id',
                            'value'   => $item_id,
                            'compare' => '='
                        )
                    ),
                    'fields' => 'ids', // Only return post IDs
                    'posts_per_page' => -1
                ));

                if (!empty($food_query->posts)) {
                    // Store matching post IDs in the new array
                    foreach ($food_query->posts as $food_id) {
                        $new_food_ids[] = $food_id;
                    }
                }
            }
        }    
        return $new_food_ids;
    }

    /**
     * Upload image function.
     *
     * @param string $url The URL of the image to be uploaded.
     * @return array|WP_Error The uploaded image data or a WP_Error object.
     */
    public function wpfm_upload_import_image($url) {
        $arrData = [];

        if ($url != '') {
            // Get file name and extension
            $path_info = pathinfo($url);
            $file_name = $path_info['filename'];
            $extension = $path_info['extension'];
                
            // Get upload directory
            $upload_dir = wp_upload_dir()['basedir'];
            $upload_path = '/' . date('Y') . '/' . date('m') . '/';
                
            // Check if the file exists
            $original_file_path = $upload_dir . $upload_path . $file_name . '.' . $extension;
            if (file_exists($original_file_path)) {
                $attachment_url = wp_upload_dir()['baseurl'] . $upload_path . $file_name . '.' . $extension;
                $attachment_id = attachment_url_to_postid($attachment_url);
                if ($attachment_id) {
                    $arrData['image_id'] = $attachment_id;
                    $arrData['image_url'] = wp_get_attachment_url($attachment_id);
                    return $arrData;  // Return existing image details
                }
            }
            $count = 1;
            while (file_exists($upload_dir . $upload_path . $file_name . '-' . $count . '.' . $extension)) {
                $file_with_number_path = $upload_dir . $upload_path . $file_name . '-' . $count . '.' . $extension;
                if (file_exists($file_with_number_path)) {
                    $attachment_url = wp_upload_dir()['baseurl'] . $upload_path . $file_name . '-' . $count . '.' . $extension;
                    $attachment_id = attachment_url_to_postid($attachment_url);
                    if ($attachment_id) {
                        $arrData['image_id'] = $attachment_id;
                        $arrData['image_url'] = wp_get_attachment_url($attachment_id);
                        return $arrData;  // Return existing image details
                    }
                }
                $count++;
            }

            // If image doesn't exist, proceed with upload
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $url = stripslashes($url);
            $tmp = download_url($url);

            // Check for download errors
            if (is_wp_error($tmp)) {
                // Handle the error, return the error object or log it
                return $tmp;  // You can return or log the error depending on your requirements
            }

            // Proceed with media_handle_sideload to handle the file upload
            $file_array = array(
                'name' => basename($url),
                'tmp_name' => $tmp
            );

            // Handle the file upload
            $post_id = 0;  // No specific post to attach the image to
            $image_id = media_handle_sideload($file_array, $post_id);

            // Check for errors after upload
            if (is_wp_error($image_id)) {
                @unlink($file_array['tmp_name']);
                return $image_id;  // Return the error if sideload fails
            }

            // Get the URL of the uploaded image
            $image_url = wp_get_attachment_url($image_id);

            // Prepare and return the result
            $arrData['image_id'] = $image_id;
            $arrData['image_url'] = $image_url;
        }

        return $arrData;
    }

    /**
     * Check if image exists via URL.
     *
     * @param string $url The image URL to check.
     * @return bool True if image exists, otherwise false.
     */
    public function wpfm_check_import_image_exists($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // ⛔ Not safe for production
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // ⛔ Not safe for production
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * convert term name to term id.
     *
     * @param $term_names, $taxonomy
     * @return $term_ids
     */
    public function wpfm_get_term_id_by_name($term_names, $taxonomy) {
        $term_ids = [];
        if(!empty( $term_names )) {
            foreach ( $term_names as $name) {
                $term = get_term_by('name', $name, $taxonomy);

                if (!$term) {
                    // If term doesn't exist, create it
                    $new_term = wp_insert_term($name, $taxonomy);
                    if (!is_wp_error($new_term)) {
                        $term_ids[] = (string) $new_term['term_id'];
                    }
                } else {
                    // If term exists, get the term_id
                    $term_ids[] = (string) $term->term_id;
                }
            }
        }
        
        return $term_ids;
    }

    /**
    * save the food banner
    *
    * @access public
    * @param $post_id,$banner_url
    * @return 
    */
    public function wpfm_import_food_file_upload($post_id, $meta_value, $meta_key, $params) {
        $is_json = is_string($meta_value) && is_array(json_decode($meta_value, true)) ? true : false;

        if ($is_json) {
            $images = json_decode($meta_value, true);
        } else {
            if (strpos($meta_value, ',') !== false) {
                $images = explode(',', $meta_value);
            } else if (strpos($meta_value, '|') !== false) {
                $images = explode('|', $meta_value);
            } else {
                $images = [$meta_value];
            }
        }
        if (!empty($images)) {
            $img_url = [];
            foreach ($images as $url) {
                $response = $this->wpfm_check_import_image_exists($url);
                if ($response) {
                    $image = $this->wpfm_upload_import_image($url);
                    if (!empty($image)) {
                        $img_url[] = $image['image_url'];
                        // Make sure you are passing only a single image URL to attachment_url_to_postid
                        $image_post_id = attachment_url_to_postid($image['image_url']);
                    }
                }
            }
            // If images are found, update the post meta
            if (!empty($img_url)) {
                update_post_meta($post_id, $meta_key, $img_url);
                if (empty($params['_thumbnail_id'])) {
                    update_post_meta($post_id, '_thumbnail_id', $image_post_id);  // Use the image post ID
                }
            }
        }
    }
        
    /**
     * save the food taxonomy data
     *
     * @access public
     * @param $post_id,meta_key, $meta_value, $import_fields
     * @return 
     */
    public function wpfm_import_food_taxonomy_terms($post_id, $meta_key, $meta_value, $import_fields) {
        if ($meta_value != '') {
            $terms = explode(',', $meta_value);
            $term_ids = [];
            foreach ($terms as $term_name) {
                $term_name = sanitize_text_field(trim($term_name));
                $term = term_exists($term_name, $import_fields['taxonomy']) ?: wp_insert_term($term_name, $import_fields['taxonomy']);
                if (!is_wp_error($term)) {
                    $term_ids[] = $term['term_id'];
                }
            }
            if (!empty($term_ids)) {
                if($meta_key == 'food_manager_tax_classes' ){
                    wp_set_post_terms($post_id, $terms , $import_fields['taxonomy'], true);
                    update_post_meta($post_id,'_tax_class_id',$term['term_id']);
                    update_post_meta($post_id,'_tax_classes_cat',$term['term_id']);
                }
                elseif ($meta_key == 'food_manager_tag') {
                    wp_set_post_terms($post_id, $terms , $import_fields['taxonomy'], true);
                }
                else{
                    wp_set_post_terms($post_id, $term_ids, $import_fields['taxonomy'], true);
                }
            }
        } else {
            // Default term if meta value is empty
            $term_id = $import_fields['default_value'];
            if ($term_id != '') {
                wp_set_post_terms($post_id, $term_id, $import_fields['taxonomy'], true);
            }
        }
    }
        
    /**
     * save the food ingredients
     *
     * @access public
     * @param $post_id,$meta_value
     * @return 
     */
    public function wpfm_import_food_ingredient($post_id, $meta_value) {
        $ingredients = explode(',', $meta_value);
        $ingredients_meta = [];
        foreach ($ingredients as $ingredient) {
            $ingredient_parts = explode('(', $ingredient);
            $ingredient_name = trim($ingredient_parts[0]);

            // Extract quantity and unit from parentheses 
            $ingredient_quantity = '';
            $ingredient_unit = '';
            if (isset($ingredient_parts[1])) {
                preg_match('/([0-9]+)\s*([a-zA-Z]+)/', $ingredient_parts[1], $matches);
                if ($matches) {
                    $ingredient_quantity = $matches[1];
                    $ingredient_unit = $matches[2];
                }
            }

            // Insert ingredient into taxonomy and get term ID
            $taxonomy = 'food_manager_ingredient';
            $term = term_exists($ingredient_name, $taxonomy) ?: wp_insert_term(trim($ingredient_name), $taxonomy);
            $term_id = is_array($term) ? $term['term_id'] : $term;
            // Insert unit into taxonomy and get term ID
            $unitaxonomy = 'food_manager_unit';
            $term = term_exists(trim($ingredient_unit), $unitaxonomy) ?: wp_insert_term(trim($ingredient_unit), $unitaxonomy);
            $unit_id = is_array($term) ? $term['term_id'] : $term;

            // Build serialized ingredient structure
            $ingredients_meta[] = [
                'id' => $term_id,
                'unit_id' =>$unit_id,
                'value' => $ingredient_quantity,
                'ingredient_term_name' => $ingredient_name,
                'unit_term_name' => $ingredient_unit
            ];
        }
        update_post_meta($post_id, '_food_ingredients', $ingredients_meta);
    }
        
    /**
     * save the food nutrition
     *
     * @access public
     * @param $post_id,$meta_value
     * @return 
     */
    public function wpfm_import_food_nutrition($post_id, $meta_value) {
        $nutritions = explode(',', $meta_value);
        $nutritions_meta = [];
        foreach ($nutritions as $nutrition) {
            $nutrition_parts = explode('(', $nutrition);
            $nutrition_name = trim($nutrition_parts[0]);

            // Extract quantity and unit from parentheses
            $nutrition_quantity = '';
            $nutrition_unit = '';
            if (isset($nutrition_parts[1])) {
                preg_match('/([0-9]+)\s*([a-zA-Z]+)/', $nutrition_parts[1], $matches);
                if ($matches) {
                    $nutrition_quantity = $matches[1];
                    $nutrition_unit = $matches[2];
                }
            }

            // Insert nutrition into taxonomy and get term ID
            $taxonomy = 'food_manager_nutrition';
            $term = term_exists($nutrition_name, $taxonomy) ?: wp_insert_term(trim($nutrition_name), $taxonomy);
            $term_id = is_array($term) ? $term['term_id'] : $term;

            $nutritions_meta[] = [
                'id' => $term_id,
                'unit_id' => '99',
                'value' => $nutrition_quantity,
                'nutrition_term_name' => $nutrition_name,
                'unit_term_name' => $nutrition_unit
            ];
        }
        update_post_meta($post_id, '_food_nutritions', $nutritions_meta);
    }
    
    /**
     * save the food topping data
     *
     * @access public
     * @param $post_id,$meta_key,$meta_value
     * @return 
     */
    public function wpfm_import_food_topping_data($post_id, $params) {
        $topping_names = explode(',', $params['_topping_name']);
        $topping_descriptions = explode(',', $params['_topping_description']);
        $topping_images = explode(',', $params['_topping_image']);
        $topping_options = explode(';', $params['_topping_options']);

        $toppings_arr = [];
        $toppings_meta = [];

        // Loop through the toppings and process them
        foreach ($topping_names as $index => $topping_name) {
            // Ensure each value exists and is valid
            $topping_name = trim($topping_name ?? '');
            $topping_description = isset($topping_descriptions[$index]) ? trim($topping_descriptions[$index]) : '';
            $topping_image = isset($topping_images[$index]) ? trim($topping_images[$index]) : '';
            $topping_option = isset($topping_options[$index]) ? trim($topping_options[$index]) : '';

            // Handle the topping term in taxonomy
            $taxonomy = 'food_manager_topping';
            $term = term_exists(trim($topping_name), $taxonomy) ?: wp_insert_term(trim($topping_name), $taxonomy);

            if (is_wp_error($term)) {
                continue;
            }

            $term_id = is_array($term) ? $term['term_id'] : $term;
            $toppings_arr[] = $term_id;
            $topping_option_data = []; 

            if (!empty($topping_option)) {
                // Split the topping options by semicolons
                $option_pairs = explode(' ', $topping_option);
                foreach ($option_pairs as $pair) {
                    $pair = trim($pair);

                    // Check if the pair contains both a topping name and price
                    if (preg_match('/([a-zA-Z\s]+)\s*,\s*(\d+)/', $pair, $matches)) {
                        $topping_option_data[] = ['option_name'  => trim($matches[1]),'option_price' => (float) $matches[2]];
                    } elseif (preg_match('/([a-zA-Z\s]+)/', $pair, $matches)) {
                        $topping_option_data[] = ['option_name'  => trim($matches[1]), 'option_price' => ''              
                        ];
                    } elseif (preg_match('/(\d+)/', $pair, $matches)) {
                        $topping_option_data[] = ['option_name'  => '', 'option_price' =>  (float) $matches[1]            
                        ];
                    }
                }
            }

            // Add topping metadata for the current topping
            $toppings_meta[] = [
                '_topping_name' => $topping_name,
                '_topping_description' => '<p>' . $topping_description . '</p>',
                '_topping_image' => [$topping_image],
                '_topping_options' => $topping_option_data	
            ];
        }

        // Assign toppings terms to the post and save meta data
        if ($toppings_arr) {
            update_post_meta($post_id, '_food_toppings', $toppings_meta);
        }
    }
        
    /**
     * save the post meta fields
     *
     * @access public
     * @param $post_id,$meta_key, $import_fields
     * @return 
     */
    public function wpfm_import_food_post_meta($post_id, $meta_key, $meta_value, $import_fields, $params) {

        if (empty($meta_value) && isset($import_fields['default_value'])) {
            $meta_value = $import_fields['default_value'];
        }

        // Update the main post meta
        update_post_meta($post_id, $meta_key, sanitize_text_field($meta_value));
    }
}

WPFM_Import::instance();