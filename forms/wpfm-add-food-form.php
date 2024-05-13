<?php

/**
 * WPFM_Add_Food_Form class.
 */

class WPFM_Add_Food_Form extends WPFM_Form {

	public    $form_name = 'add-food';
	protected $food_id;
	protected $preview_food;
	public    $resume_edit;

	/** @var WPFM_Add_Food_Form The single instance of the class */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action('wp', array($this, 'process'));

		$this->steps  = (array) apply_filters('add_food_steps', array(
			'submit' => array(
				'name'     => esc_html__('Submit Details', 'wp-food-manager'),
				'view'     => array($this, 'submit'),
				'handler'  => array($this, 'submit_handler'),
				'priority' => 10
			),
			'preview' => array(
				'name'     => esc_html__('Preview', 'wp-food-manager'),
				'view'     => array($this, 'preview'),
				'handler'  => array($this, 'preview_handler'),
				'priority' => 20
			),
			'done' => array(
				'name'     => esc_html__('Done', 'wp-food-manager'),
				'view'     => array($this, 'done'),
				'priority' => 30
			)
		));

		uasort($this->steps, array($this, 'sort_by_priority'));

		// Get step/food.
		if (isset($_POST['step'])) {
			$this->step = is_numeric($_POST['step']) ? max(absint($_POST['step']), 0) : array_search($_POST['step'], array_keys($this->steps));
		} elseif (!empty($_GET['step'])) {
			$this->step = is_numeric($_GET['step']) ? max(absint($_GET['step']), 0) : array_search($_GET['step'], array_keys($this->steps));
		}

		$this->food_id = !empty($_REQUEST['food_id']) ? absint($_REQUEST['food_id']) : 0;
		if (!food_manager_user_can_edit_food($this->food_id)) {
			$this->food_id = 0;
		}

		// Allow resuming from cookie.
		$this->resume_edit = false;
		if (!isset($_GET['new']) && ('before' === get_option('food_manager_paid_listings_flow') || !$this->food_id) && !empty($_COOKIE['wp-food-manager-adding-food-id']) && !empty($_COOKIE['wp-food-manager-adding-food-key'])) {
			$food_id     = absint($_COOKIE['wp-food-manager-adding-food-id']);
			$food_status = get_post_status($food_id);
			if ('preview' === $food_status && get_post_meta($food_id, '_adding_key', true) === $_COOKIE['wp-food-manager-adding-food-key']) {
				$this->food_id = $food_id;
			}
		}

		// Load food details.
		if ($this->food_id) {
			$food_status = get_post_status($this->food_id);
			if ('expired' === $food_status) {
				if (!food_manager_user_can_edit_food($this->food_id)) {
					$this->food_id = 0;
					$this->step   = 0;
				}
			} elseif (!in_array($food_status, apply_filters('food_manager_valid_add_food_statuses', array('preview')))) {
				$this->food_id = 0;
				$this->step   = 0;
			}
		}
	}

	/**
	 * Get the submitted food ID
	 *
	 * @access public
	 * @return int
	 * @since 1.0.0
	 */
	public function get_food_id() {
		return absint($this->food_id);
	}

	/**
	 * Return the fields array with the merge of new field array.
	 *
	 * @access public
	 * @return $this->fields
	 * @since 1.0.0
	 */
	public function init_fields() {
		if ($this->fields) {
			return;
		}

		$this->fields = $this->get_default_food_fields();
		$food_manager_tag_terms = get_terms(array(
			'taxonomy' => 'food_manager_tag',
			'hide_empty' => false,
		));

		if (is_array($food_manager_tag_terms) && !empty($food_manager_tag_terms)) {
			$new_arr = array(
				'food_tag' => array(
					'label'       => esc_html__('Food Tag', 'wp-food-manager'),
					'type'        => 'term-multiselect',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 4,
					'default'     => '',
					'taxonomy'    => 'food_manager_tag'
				),
			);
			$this->fields['food'] = array_merge(array_slice($this->fields['food'], 0, 3), $new_arr, array_slice($this->fields['food'], 3));
		}

		return $this->fields;
	}

	/**
	 * Return the default form field's array for the field editor.
	 * 
	 * @access public
	 * @return array
	 * @since 1.0.0
	 */
	public function get_default_food_fields() {
		$current_user_id = get_current_user_id();

		return apply_filters('add_food_fields', array(
			'food' => array(
				'food_title' => array(
					'label'       => __('Food Title', 'wp-food-manager'),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __('Food Title', 'wp-food-manager'),
					'priority'    => 1
				),
				'food_category' => array(
					'label'       => __('Food Category', 'wp-food-manager'),
					'type'        => get_option('food_manager_multiselect_food_category', 1) ?  'term-multiselect' : 'term-select',
					'required'    => true,
					'placeholder' => 'Select Food Category',
					'priority'    => 2,
					'default'     => '',
					'taxonomy'    => 'food_manager_category'
				),
				'food_type' => array(
					'label'       => __('Food Type', 'wp-food-manager'),
					'type'        => 'term-select',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 3,
					'default'     => '',
					'taxonomy'    => 'food_manager_type'
				),
				'food_ingredients' => array(
					'label'       => __('Food Ingredient', 'wp-food-manager'),
					'type'        => 'term-select-multi-appearance',
					'required'    => false,
					'placeholder' => '',
					'priority'    => 4,
					'default'     => '',
					'taxonomy'    => 'food_manager_ingredient',
					'tabgroup' => 3,
				),
				'food_nutritions' => array(
					'label'       => __('Food Nutrition', 'wp-food-manager'),
					'type'        => 'term-select-multi-appearance',
					'required'    => false,
					'placeholder' => '',
					'priority'    => 5,
					'default'     => '',
					'taxonomy'    => 'food_manager_nutrition',
					'tabgroup' => 4,
				),
				'food_tag' => array(
					'label'       => __('Food Tag', 'wp-food-manager'),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => 'Delicious, Spicy',
					'priority'    => 6,
					'default'     => '',
					'description'     => 'Comma separate tags, such as required like food type or content for this food.',
				),
				'food_banner' => array(
					'label'       => __('Food Banner ', 'wp-food-manager'),
					'type'        => 'file',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 7,
					'ajax'        => true,
					'multiple'    => false,
					'allowed_mime_types' => array(
						'jpg'  => 'image/jpeg',
						'jpeg' => 'image/jpeg',
						'gif'  => 'image/gif',
						'png'  => 'image/png'
					),
					'tabgroup' => 1,
				),
				'food_description' => array(
					'label'       => __('Description', 'wp-food-manager'),
					'type'        => 'wp-editor',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 8
				),
				'food_price' => array(
					'label'       => __('Regular Price', 'wp-food-manager'),
					'type'        => 'number',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 9,
					'tabgroup' => 1,
				),
				'food_sale_price' => array(
					'label'       => __('Sale Price', 'wp-food-manager'),
					'type'        => 'number',
					'required'    => false,
					'placeholder' => '',
					'priority'    => 10,
					'tabgroup' => 1,
				),
				'food_stock_status' => array(
					'label'       => __('Stock Status', 'wp-food-manager'),
					'type'        => 'select',
					'required'    => true,
					'options' 	  => array(
						'food_instock' => __('In stock', 'wp-food-manager'),
						'food_outofstock' => __('Out of stock', 'wp-food-manager'),
					),
					'priority'    => 11,
					'tabgroup' => 1,
				),
				'enable_food_ingre' => array(
					'label'       => __('Enable Ingredient', 'wp-food-manager'),
					'type'        => 'switch',
					'required'    => false,
					'placeholder' => '1',
					'value' => 1,
					'priority'    => 12,
					'tabgroup' => 5,
				),
				'enable_food_nutri' => array(
					'label'       => __('Enable Nutrition', 'wp-food-manager'),
					'value'         => 1,
					'type'        => 'switch',
					'required'    => false,
					'placeholder' => '1',
					'priority'    => 13,
					'tabgroup' => 5,
				),
			),
			'toppings' => array(
				'topping_name' => array(
					'label'       => __('Topping Name', 'wp-food-manager'),
					'type'        => 'term-autocomplete',
					'required'    => true,
					'placeholder' => __('Enter Topping option name', 'wp-food-manager'),
					'priority'    => 1,
					'taxonomy'    => 'food_manager_topping'
				),
				'topping_description' => array(
					'label'       => __('Topping Description', 'wp-food-manager'),
					'type'        => 'wp-editor',
					'required'    => true,
					'placeholder' => 'Enter the field Topping Description',
					'default' => 1,
					'priority'    => 2
				),
				'topping_options' => array(
					'label'       => __('Options', 'wp-food-manager'),
					'type'        => 'options',
					'required'    => false,
					'placeholder' => __('Enter option name', 'wp-food-manager'),
					'priority'    => 3
				),
			)
		));
	}

	/**
	 * Validate the posted fields.
	 *
	 * @access protected
	 * @param array $values
	 * @return bool on success, WP_ERROR on failure.
	 * @since 1.0.0
	 */
	protected function validate_fields($values) {
		$this->fields =  apply_filters('before_add_food_validate_fields', $this->fields, $values);

		foreach ($this->fields as $group_key => $group_fields) {
			foreach ($group_fields as $key => $field) {

				if ($group_key == 'toppings') {
					if (isset($_POST['repeated_options'])) {
						foreach ($_POST['repeated_options'] as $repeated_options) {
							if ($field['required'] && empty($_POST[$key . '_' . $repeated_options])) {
								return new WP_Error('validation-error', sprintf(__('%s is a required field.', 'wp-food-manager'), $field['label']));
							}
						}
					}
				} else {
					if ($field['required'] && empty($values[$group_key][$key])) {
						return new WP_Error('validation-error', sprintf(__('%s is a required field.', 'wp-food-manager'), $field['label']));
					}
				}

				if (!empty($field['taxonomy']) && in_array($field['type'], array('term-checklist', 'term-select', 'term-multiselect')) && !empty($values[$group_key][$key])) {
					if (is_array($values[$group_key][$key]) && isset($values[$group_key][$key])) {
						$check_value = $values[$group_key][$key];
					} else {
						$check_value = empty($values[$group_key][$key]) ? array() : array($values[$group_key][$key]);
					}
					foreach ($check_value as $term) {
						if (!term_exists($term, $field['taxonomy'])) {
							return new WP_Error('validation-error', sprintf(__('%s is invalid.', 'wp-food-manager'), $field['label']));
						}
					}
				}

				if ('file' === $field['type'] && !empty($field['allowed_mime_types'])) {
					if (is_array($values[$group_key][$key])) {
						$check_value = array_filter($values[$group_key][$key]);
					} else {
						$check_value = array_filter(array($values[$group_key][$key]));
					}
					if (!empty($check_value)) {
						foreach ($check_value as $file_url) {
							$file_url = current(explode('?', $file_url));
							$file_info = wp_check_filetype($file_url);
							if (!is_numeric($file_url) && $file_info && !in_array($file_info['type'], $field['allowed_mime_types'])) {
								throw new Exception(sprintf(__('"%s" (filetype %s) needs to be one of the following file types: %s.', 'wp-food-manager'), $field['label'], '', implode(', ', array_keys($field['allowed_mime_types']))));
							}
						}
					}
				}
			}
		}

		return apply_filters('add_food_validate_fields', true, $this->fields, $values);
	}

	/**
	 * Return the food_type options.
	 * 
	 * @access private
	 * @return array $options
	 * @since 1.0.0
	 */
	private function food_types() {
		$options = array();
		$terms   = get_food_type();
		foreach ($terms as $term) {
			$options[$term->slug] = $term->name;
		}
		return $options;
	}

	/**
	 * Submit the form according to the steps.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function submit() {
		// get date and time setting defined in admin panel food listing -> Settings -> Date & Time formatting.
		$datepicker_date_format = esc_attr(WPFM_Date_Time::get_datepicker_format());

		// covert datepicker format into php date() function date format.
		$php_date_format = esc_attr(WPFM_Date_Time::get_view_date_format_from_datepicker_date_format($datepicker_date_format));

		// Init fields.
		// $this->init_fields(); We don't need to initialize with this function because of the field editor.
		// Now field editor function will return all the fields.
		// Get merged fields from db and default fields.
		$this->merge_with_custom_fields('frontend');

		// Load data if necessary.
		if ($this->food_id) {
			$food = get_post($this->food_id);
			foreach ($this->fields as $group_key => $group_fields) {
				foreach ($group_fields as $key => $field) {

					switch ($key) {
						case 'food_title':
							$this->fields[$group_key][$key]['value'] = sanitize_text_field($food->post_title);
							break;

						case 'food_description':
							$this->fields[$group_key][$key]['value'] = wp_kses_post($food->post_content);
							break;

						case 'food_type':
							$this->fields[$group_key][$key]['value'] = wp_get_object_terms($food->ID, 'food_manager_type', array('fields' => 'ids'));
							if (!food_manager_multiselect_food_type()) {
								$this->fields[$group_key][$key]['value'] = current($this->fields[$group_key][$key]['value']);
							}
							break;

						case 'food_category':
							$this->fields[$group_key][$key]['value'] = wp_get_object_terms($food->ID, 'food_manager_category', array('fields' => 'ids'));
							break;

						case 'food_tag':
								$this->fields[$group_key][$key]['value'] = wp_get_object_terms($food->ID, 'food_manager_tag', array('fields' => 'ids'));
								break;
	

						default:
							$this->fields[$group_key][$key]['value'] = sanitize_text_field(get_post_meta($food->ID, '_' . $key, true));
							break;
					}

					if (!empty($field['taxonomy'])) {
						$this->fields[$group_key][$key]['value'] = wp_get_object_terms($food->ID, $field['taxonomy'], array('fields' => 'ids'));
					}

					if (!empty($field['type']) && $field['type'] == 'date') {
						$food_date = get_post_meta($food->ID, '_' . $key, true);
						$this->fields[$group_key][$key]['value'] = date($php_date_format, strtotime($food_date));
					}
				}
			}
			$this->fields = apply_filters('add_food_fields_get_food_data', $this->fields, $food);

			// Get user meta.
		} elseif (is_user_logged_in() && empty($_POST['add_food'])) {
			if (!empty($this->fields['food']['registration'])) {
				$allowed_registration_method = get_option('food_manager_allowed_registration_method', '');
				if ($allowed_registration_method !== 'url') {
					$current_user = wp_get_current_user();
					$this->fields['food']['registration']['value'] = sanitize_email($current_user->user_email);
				}
			}

			$this->fields = apply_filters('add_food_fields_get_user_data', $this->fields, get_current_user_id());
		}
		wp_enqueue_script('wp-food-manager-food-submission');
		get_food_manager_template('food-submit.php', array(
			'form' => esc_attr($this->form_name),
			'food_id' => esc_attr($this->get_food_id()),
			'resume_edit' => $this->resume_edit,
			'action' => esc_url($this->get_action()),
			'food_fields' => $this->get_fields('food'),
			'topping_fields' => $this->get_fields('toppings'),
			'step' => esc_attr($this->get_step()),
			'submit_button_text' => apply_filters('add_food_submit_button_text', __('Preview', 'wp-food-manager'))
		));
	}

	/**
	 * Submit Step is posted.
	 * 
	 * @access public
	 * @return mixed
	 * @since 1.0.0
	 */
	public function submit_handler() {
		try {

			// Init fields.
			//$this->init_fields(); We dont need to initialize with this function because of field edior.
			// Now field editor function will return all the fields.
			//Get merged fields from db and default fields.
			$this->merge_with_custom_fields('frontend');

			// Get posted values.
			$values = $this->get_posted_fields();
			if (empty($_POST['add_food'])) {
				return;
			}

			// Validate required.
			if (is_wp_error(($return = $this->validate_fields($values)))) {
				throw new Exception($return->get_error_message());
			}

			// Account creation.
			if (!is_user_logged_in()) {
				$create_account = false;
				if (food_manager_enable_registration()) {
					if (food_manager_user_requires_account()) {

						if (!food_manager_generate_username_from_email() && empty($_POST['create_account_username'])) {
							throw new Exception(__('Please enter a username.', 'wp-food-manager'));
						}

						if (empty($_POST['create_account_email'])) {
							throw new Exception(__('Please enter your email address.', 'wp-food-manager'));
						}

						if (empty($_POST['create_account_email'])) {
							throw new Exception(__('Please enter your email address.', 'wp-food-manager'));
						}
					}
					if (!food_manager_use_standard_password_setup_email() && !empty($_POST['create_account_password'])) {
						if (empty($_POST['create_account_password_verify']) || $_POST['create_account_password_verify'] !== $_POST['create_account_password']) {
							throw new Exception(__('Passwords must match.', 'wp-food-manager'));
						}

						if (!food_manager_validate_new_password($_POST['create_account_password'])) {
							$password_hint = food_manager_get_password_rules_hint();
							if ($password_hint) {
								throw new Exception(sprintf(__('Invalid Password: %s', 'wp-food-manager'), $password_hint));
							} else {
								throw new Exception(__('Password is not valid.', 'wp-food-manager'));
							}
						}
					}

					if (!empty($_POST['create_account_email'])) {
						$create_account = wpfm_create_account(array(
							'username' => (food_manager_generate_username_from_email() || empty($_POST['create_account_username'])) ? '' : $_POST['create_account_username'],
							'password' => (food_manager_use_standard_password_setup_email() || empty($_POST['create_account_password'])) ? '' : $_POST['create_account_password'],
							'email'    => $_POST['create_account_email'],
							'role'     => get_option('food_manager_registration_role', 'food_owner')
						));
					}
				}

				if (is_wp_error($create_account)) {
					throw new Exception($create_account->get_error_message());
				}
			}
			if (food_manager_user_requires_account() && !is_user_logged_in()) {
				throw new Exception(__('You must be signed in to post a new listing.', 'wp-food-manager'));
			}

			// Update the food.
			$food_title = isset($values['food']['food_title']) && !empty($values['food']['food_title']) ? $values['food']['food_title'] : '';
			$food_description = isset($values['food']['food_description']) && !empty($values['food']['food_description']) ? $values['food']['food_description'] : '';

			// Determine the status based on the condition.
			$status = $this->food_id ? '' : 'preview';

			// Pass the $status variable as an argument in the save_food() method.
			$this->save_food(sanitize_text_field($food_title), wp_kses_post($food_description), $status, $values, false);
			
			// Successful, show next step.
			$this->step++;
		} catch (Exception $e) {
			$this->add_error(esc_html($e->getMessage()));
			return;
		}
	}

	/**
	 * Update or create a food listing from posted data.
	 *
	 * @access protected
	 * @param  string $post_title
	 * @param  string $post_content
	 * @param  string $status
	 * @param  array $values
	 * @param  bool $update_slug
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function save_food($post_title, $post_content, $status = 'preview', $values = array(), $update_slug = true) {
		global $wpdb;

		$food_data = array(
			'post_title'     => $post_title,
			'post_content'   => $post_content,
			'post_type'      => 'food_manager',
			'comment_status' => 'closed'
		);

		if ($update_slug) {
			$food_slug   = array();

			// Prepend with food type.
			if (apply_filters('add_food_prefix_post_name_with_food_type', true) && !empty($values['food']['food_type'])) {
				if (food_manager_multiselect_food_type() && is_array($values['food']['food_type'])) {
					$food_type = array_values($values['food']['food_type'])[0];
					if (is_int($food_type)) {
						$food_type_taxonomy = get_term($values['food']['food_type'][0]);
						$food_type = $food_type_taxonomy->name;
					}
				} else {
					$food_type = $values['food']['food_type'];
					if (is_int($food_type)) {
						$food_type_taxonomy = get_term($values['food']['food_type']);
						$food_type = $food_type_taxonomy->name;
					}
				}
			}

			$food_slug[]            = $post_title;
			$food_slugs             = implode('-', $food_slug);
			$food_data['post_name'] = apply_filters('add_food_save_slug_data', $food_slugs);
		}

		if ($status) {
			$food_data['post_status'] = $status;
		}

		$food_data = apply_filters('add_food_save_food_data', $food_data, $post_title, $post_content, $status, $values);
		if ($this->food_id) {
			$food_data['ID'] = $this->food_id;
			wp_update_post($food_data);
		} else {
			$this->food_id = wp_insert_post($food_data);
			if (!headers_sent()) {
				$adding_key = uniqid();
				setcookie('wp-food-manager-adding-food-id', $this->food_id, 0, COOKIEPATH, COOKIE_DOMAIN, false);
				setcookie('wp-food-manager-adding-food-key', $adding_key, 0, COOKIEPATH, COOKIE_DOMAIN, false);
				update_post_meta($this->food_id, '_adding_key', $adding_key);
			}
		}

		$post = get_post($this->food_id);
		do_action('wpfm_save_food_data', $this->food_id, $post, $this->fields);
		do_action('wpfm_add_food_form', $this->food_id, $post_title, $post_content, $status, $values, $update_slug);
	}

	/**
	 * Create an attachment.
	 *
	 * @access protected
	 * @param string $attachment_url
	 * @return int attachment id
	 * @since 1.0.0
	 */
	public function create_attachment($attachment_url) {
		include_once(ABSPATH . 'wp-admin/includes/image.php');
		include_once(ABSPATH . 'wp-admin/includes/media.php');

		$upload_dir     = wp_upload_dir();
		$attachment_url = esc_url($attachment_url, array('http', 'https'));

		if (empty($attachment_url)) {
			return 0;
		}

		$attachment_url_parts = wp_parse_url($attachment_url);
		if (false !== strpos($attachment_url_parts['path'], '../')) {
			return 0;
		}

		$attachment_url = sprintf('%s://%s%s', $attachment_url_parts['scheme'], $attachment_url_parts['host'], $attachment_url_parts['path']);
		$attachment_url = str_replace(array($upload_dir['baseurl'], WP_CONTENT_URL, site_url('/')), array($upload_dir['basedir'], WP_CONTENT_DIR, ABSPATH), $attachment_url);
		if (empty($attachment_url) || !is_string($attachment_url)) {
			return 0;
		}

		$attachment     = array(
			'post_title'   => get_the_title($this->food_id),
			'post_content' => '',
			'post_status'  => 'inherit',
			'post_parent'  => $this->food_id,
			'guid'         => $attachment_url
		);

		if ($info = wp_check_filetype($attachment_url)) {
			$attachment['post_mime_type'] = $info['type'];
		}

		$attachment_id = wp_insert_attachment($attachment, $attachment_url, $this->food_id);
		if (!is_wp_error($attachment_id)) {
			wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $attachment_url));
			return $attachment_id;
		}

		return 0;
	}

	/**
	 * Preview the submitted form.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function preview() {
		global $post, $food_preview;
		if ($this->food_id) {
			$food_preview       = true;
			$action            = $this->get_action();
			$post              = get_post($this->food_id);
			setup_postdata($post);
			$post->post_status = 'preview';
			get_food_manager_template('food-preview.php', array('form' => $this));
			wp_reset_postdata();
		}
	}

	/**
	 * Preview Step Form handler.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function preview_handler() {
		if (!$_POST) {
			return;
		}

		// Edit = show submit form again.
		if (!empty($_POST['edit_food'])) {
			$this->step--;
		}

		// Continue = change food status then show next screen.
		if (!empty($_POST['continue'])) {
			$food = get_post($this->food_id);
			if (in_array($food->post_status, array('preview', 'expired'))) {

				// Reset expiry.
				delete_post_meta($food->ID, '_food_expiry_date');

				// Update food listing.
				$update_food                  = array();
				$update_food['ID']            = $food->ID;
				$update_food['post_status']   = apply_filters('add_food_post_status', get_option('food_manager_submission_requires_approval') ? 'pending' : 'publish', $food);
				$update_food['post_date']     = current_time('mysql');
				$update_food['post_date_gmt'] = current_time('mysql', 1);
				wp_update_post($update_food);
			}
			$this->step++;
		}
	}

	/**
	 * Done the step and do action after the step submitted.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function done() {
		do_action('food_manager_food_submitted', $this->food_id);
		get_food_manager_template('food-submitted.php', array('food' => get_post($this->food_id)));
	}

	/**
	 * Get selected fields from the field editor.
	 *
	 * @access public
	 * @return fields Array
	 * @since 1.0.0
	 */
	public function get_food_manager_fieldeditor_fields() {
		return apply_filters('food_manager_add_food_form_fields', get_option('food_manager_add_food_form_fields', false));
	}

	/**
	 * Get extra options fields from the field editor.
	 *
	 * @access public
	 * @return fields Array
	 * @since 1.0.0
	 */
	public function get_food_manager_fieldeditor_toppings_fields() {
		return apply_filters('food_manager_submit_toppings_form_fields', get_option('food_manager_submit_toppings_form_fields', false));
	}

	/**
	 * This function will initialize default fields and return as an array.
	 *
	 * @access public
	 * @return fields Array
	 * @since 1.0.0
	 **/
	public function get_default_fields() {
		if (empty($this->fields)) {
			// Make sure fields are initialized and set.
			$this->init_fields();
		}
		return $this->fields;
	}

	/**
	 * This function will set the food id for invoking the food object.
	 *
	 * @access public
	 * @param int $id
	 * @return int $id
	 * @since 1.0.0
	 **/
	public function set_id($id) {
		$this->food_id = absint($id);
		return $this->food_id;
	}

	/**
	 * This function will get the food id for invoking the food object.
	 *
	 * @access public
	 * @return int $id
	 * @since 1.0.0
	 **/
	public function get_id() {
		if (empty($this->food_id)) {
			$this->food_id = 0;
		}
		return absint($this->food_id);
	}
}
