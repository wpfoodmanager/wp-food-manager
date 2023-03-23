<?php

/**
 * Abstract WP_food_Manager_Form class.
 *
 * @abstract
 */
abstract class WPFM_Form {

	protected $fields    = array();
	protected $action    = '';
	protected $errors    = array();
	protected $steps     = array();
	protected $step      = 0;
	public    $form_name = '';

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong(__FUNCTION__);
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong(__FUNCTION__);
	}

	/**
	 * Process function. all processing code if needed - can also change view if step is complete
	 */
	public function process() {
		// reset cookie
		if (isset($_GET['new']) && isset($_COOKIE['wpfm-submitting-food-id']) && isset($_COOKIE['wpfm-submitting-food-key']) && get_post_meta(sanitize_text_field($_COOKIE['wpfm-submitting-food-id']), '_submitting_key', true) == $_COOKIE['wpfm-submitting-food-key']) {
			delete_post_meta($_COOKIE['wpfm-submitting-food-id'], '_submitting_key');
			setcookie('wpfm-submitting-food-id', '', 0, COOKIEPATH, COOKIE_DOMAIN, false);
			setcookie('wpfm-submitting-food-key', '', 0, COOKIEPATH, COOKIE_DOMAIN, false);
			wp_redirect(remove_query_arg(array('new', 'key'), $_SERVER['REQUEST_URI']));
		}
		$step_key = $this->get_step_key($this->step);
		if ($step_key && is_callable($this->steps[$step_key]['handler'])) {
			call_user_func($this->steps[$step_key]['handler']);
		}
		$next_step_key = $this->get_step_key($this->step);
		// if the step changed, but the next step has no 'view', call the next handler in sequence.
		if ($next_step_key && $step_key !== $next_step_key && !is_callable($this->steps[$next_step_key]['view'])) {
			$this->process();
		}
	}

	/**
	 * Get formn name.
	 * @since 1.0.0
	 * @return string
	 */
	public function get_form_name() {
		return $this->form_name;
	}

	/**
	 * output function. Call the view handler.
	 */
	public function output($atts = array()) {
		$step_key = $this->get_step_key($this->step);
		$this->show_errors();
		if ($step_key && is_callable($this->steps[$step_key]['view'])) {
			call_user_func($this->steps[$step_key]['view'], $atts);
		}
	}

	/**
	 * Add an error
	 * @param string $error
	 */
	public function add_error($error) {
		$this->errors[] = $error;
	}

	/**
	 * Show errors
	 */
	public function show_errors() {
		foreach ($this->errors as $error) {
			echo '<div class="food-manager-error wpfm-alert wpfm-alert-danger">' . $error . '</div>';
		}
	}

	/**
	 * Get action (URL for forms to post to).
	 * As of 1.0.0 this defaults to the current page permalink.
	 *
	 * @return string
	 */
	public function get_action() {
		return esc_url_raw($this->action ? $this->action : wp_unslash($_SERVER['REQUEST_URI']));
	}

	/**
	 * Get step from outside of the class
	 */
	public function get_step() {
		return $this->step;
	}

	/**
	 * Get steps from outside of the class
	 * @since 1.0.0
	 */
	public function get_steps() {
		return $this->steps;
	}

	/**
	 * Get step key from outside of the class
	 * @since 1.0.0
	 */
	public function get_step_key($step = '') {
		if (!$step) {
			$step = $this->step;
		}
		$keys = array_keys($this->steps);
		return isset($keys[$step]) ? $keys[$step] : '';
	}

	/**
	 * Get step from outside of the class
	 * @since 1.0.0
	 */
	public function set_step($step) {
		$this->step = absint($step);
	}

	/**
	 * Increase step from outside of the class
	 */
	public function next_step() {
		$this->step++;
	}

	/**
	 * Decrease step from outside of the class
	 */
	public function previous_step() {
		$this->step--;
	}

	/**
	 * get_fields function.
	 *
	 * @param string $key
	 * @return array
	 */
	public function get_fields($key) {
		if (empty($this->fields[$key])) {
			return array();
		}
		$fields = $this->fields[$key];
		uasort($fields, array($this, 'sort_by_priority'));
		return $fields;
	}

	/**
	 * Sort array by priority value
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	protected function sort_by_priority($a, $b) {
		if ($a['priority'] == $b['priority']) {
			return 0;
		}
		return ($a['priority'] < $b['priority']) ? -1 : 1;
	}

	/**
	 * Init form fields
	 */
	protected function init_fields() {
		$this->fields = array();
	}

	/**
	 * Enqueue the scripts for the form.
	 */
	public function enqueue_scripts() {
		if ($this->use_recaptcha_field()) {
			wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js');
		}
	}

	/**
	 * Get post data for fields
	 *
	 * @return array of data
	 */
	protected function get_posted_fields() {
		global $post;
		// Init fields
		// $this->init_fields(); We dont need to initialize with this function because of field edior
		// Now field editor function will return all the fields 
		// Get merged fields from db and default fields.
		$this->merge_with_custom_fields('frontend');
		$values = array();
		$option_value_count = isset($_POST['option_value_count']) ? $_POST['option_value_count'] : '';
		$repeated_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
		$add_food_page_id = get_option('food_manager_add_food_page_id');
		$food_dashboard_page_id = get_option('food_manager_food_dashboard_page_id');
		$food_id = '';
		if ($add_food_page_id) {
			if ($add_food_page_id == get_the_ID()) {
				$food_id = isset($_POST['food_id']) ? $_POST['food_id'] : '';
			}
		}
		if ($food_dashboard_page_id) {
			if ($food_dashboard_page_id == get_the_ID()) {
				$food_id = isset($_GET['food_id']) ? $_GET['food_id'] : '';
			}
		}
		$food_id = isset($_POST['food_id']) ? $_POST['food_id'] : '';
		$ext_multi_options = '';
		if (!empty($repeated_options) && empty($option_value_count)) {
			$ext_multi_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
		}
		if (!empty($repeated_options) && !empty($option_value_count)) {
			$ext_multi_options = isset($_POST['option_value_count']) ? $_POST['option_value_count'] : '';
		}
		if (isset($_POST['_nutritions']) && !empty($_POST['_nutritions'])) {
			$values['_nutritions'] = $_POST['_nutritions'];
		}
		if (isset($_POST['_ingredients']) && !empty($_POST['_ingredients'])) {
			$values['_ingredients'] = $_POST['_ingredients'];
		}
		if (!add_post_meta($food_id, 'wpfm_repeated_options', $repeated_options, true)) {
			update_post_meta($food_id, 'wpfm_repeated_options', $repeated_options);
		}
		if (($option_value_count && is_array($option_value_count)) && ($repeated_options && is_array($repeated_options))) {
			foreach ($ext_multi_options as $option_count => $option_value) {
				foreach ($this->fields as $group_key => $group_fields) {
					foreach ($group_fields as $key => $field) {
						// Get the value
						$field_type = str_replace('-', '_', $field['type']);
						if ($handler = apply_filters("food_manager_get_posted_{$field_type}_field", false)) {
							$values[$group_key][$key] = call_user_func($handler, $key, $field);
						} elseif ($group_key == "extra_options") {
							$key2 = "";
							if ($key == "topping_name") {
								$first_key = $key . "_" . $option_count;
								$key2 = $key . "_" . $option_count;
							} else {
								$key2 = "_" . $key . "_" . $option_count;
							}
							$first_out = str_replace(" ", "_", strtolower($this->get_posted_field($first_key, $field)));
							$output = $this->get_posted_field($key2, $field);
							if ($field['type'] == 'file') {
								$output = $this->get_posted_field("current_" . $key2, $field);
								update_post_meta($food_id, "current_" . $key2, $output);
							}
							$values[$group_key][$first_out][$key] = $output;
							$output2 = array();
							if ($key == "topping_options") {
								foreach ($option_value as $option_value_count) {
									$output2[$option_value_count] =
										array(
											'option_name' => isset($_POST[$option_count . '_option_name_' . $option_value_count]) ? $_POST[$option_count . '_option_name_' . $option_value_count] : '',
											'option_default' => isset($_POST[$option_count . '_option_default_' . $option_value_count]) ? $_POST[$option_count . '_option_default_' . $option_value_count] : '',
											'option_price' => isset($_POST[$option_count . '_option_price_' . $option_value_count]) ? $_POST[$option_count . '_option_price_' . $option_value_count] : '',
											'option_price_type' => isset($_POST[$option_count . '_option_price_type_' . $option_value_count]) ? $_POST[$option_count . '_option_price_type_' . $option_value_count] : ''
										);
									$values[$group_key][$first_out][$key] = $output2;
								}
							}
						} elseif (method_exists($this, "get_posted_{$field_type}_field")) {
							$values[$group_key][$key] = call_user_func(array($this, "get_posted_{$field_type}_field"), $key, $field);
						} else {
							$values[$group_key][$key] = $this->get_posted_field($key, $field);
						}
					}
				}
			}
			update_post_meta($food_id, '_toppings', $values[$group_key]);
		} else {
			foreach ($this->fields as $group_key => $group_fields) {
				foreach ($group_fields as $key => $field) {
					// Get the value
					$field_type = str_replace('-', '_', $field['type']);
					if ($handler = apply_filters("food_manager_get_posted_{$field_type}_field", false)) {
						$values[$group_key][$key] = call_user_func($handler, $key, $field);
					} elseif (method_exists($this, "get_posted_{$field_type}_field")) {
						$values[$group_key][$key] = call_user_func(array($this, "get_posted_{$field_type}_field"), $key, $field);
					} else {
						$values[$group_key][$key] = $this->get_posted_field($key, $field);
					}
					// Set fields value
					$this->fields[$group_key][$key]['value'] = $values[$group_key][$key];
				}
			}
			update_post_meta($food_id, '_toppings', '');
		}
		return $values;
	}

	/** 
	 * Get the value of a repeated fields (e.g. repeated)
	 * @param  array $fields
	 * @return array
	 */
	protected function get_repeated_field($field_prefix, $fields) {
		$items       = array();
		$field_keys  = array_keys($fields);
		if (!empty($_POST['repeated-row-' . $field_prefix]) && is_array($_POST['repeated-row-' . $field_prefix])) {
			$indexes = array_map('absint', $_POST['repeated-row-' . $field_prefix]);
			foreach ($indexes as $index) {
				$item = array();
				foreach ($fields as $key => $field) {
					$field_name = $field_prefix . '_' . $key . '_' . $index;
					switch ($field['type']) {
						case 'textarea':
							$item[$key] = wp_kses_post(stripslashes($_POST[$field_name]));
							break;
						case 'file':
							$file = $this->upload_file($field_name, $field);
							if (!$file) {
								$file = $this->get_posted_field('current_' . $field_name, $field);
							} elseif (is_array($file)) {
								$file = array_filter(array_merge($file, (array) $this->get_posted_field('current_' . $field_name, $field)));
							}
							$item[$key] = $file;
							break;
						case 'checkbox':
							if (!empty($_POST[$field_name]) && $_POST[$field_name] > 0) {
								$item[$key] = wp_kses_post(stripslashes($_POST[$field_name]));
							}
							break;
						default:
							if (is_array($_POST[$field_name])) {
								$item[$key] = array_filter(array_map('sanitize_text_field', array_map('stripslashes', $_POST[$field_name])));
							} else {
								$item[$key] = sanitize_text_field(stripslashes($_POST[$field_name]));
							}
							break;
					}
					if (empty($item[$key]) && !empty($field['required'])) {
						continue 2;
					}
				}
				$items[] = $item;
			}
		}
		return $items;
	}

	/**
	 * Get the value of a posted repeated field
	 * @since  1.0.0
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected function get_posted_repeated_field($key, $field) {
		return  $this->get_repeated_field($key, $field['fields']);
	}

	/**
	 * Navigates through an array and sanitizes the field.
	 *
	 * @param array|string $value The array or string to be sanitized.
	 * @return array|string $value The sanitized array (or string from the callback).
	 */
	protected function sanitize_posted_field($value) {
		// Decode URLs
		if (is_string($value) && (strstr($value, 'http:') || strstr($value, 'https:'))) {
			$value = urldecode($value);
		}
		// Santize value
		$value = is_array($value) ? array_map(array($this, 'sanitize_posted_field'), $value) : sanitize_text_field(stripslashes(trim($value)));
		return $value;
	}

	/**
	 * Get the value of a posted field
	 * @param  string $key
	 * @param  array $field
	 * @return string|array
	 */
	protected function get_posted_field($key, $field) {
		return isset($_POST[$key]) ? $_POST[$key] : '';
	}

	/**
	 * Get the value of a posted multiselect field
	 * @param  string $key
	 * @param  array $field
	 * @return array
	 */
	protected function get_posted_multiselect_field($key, $field) {
		return isset($_POST[$key]) ? array_map('sanitize_text_field', $_POST[$key]) : array();
	}

	/**
	 * Get the value of a posted file field
	 * @param  string $key
	 * @param  array $field
	 * @return string|array
	 */
	protected function get_posted_file_field($key, $field) {
		$file = $this->upload_file($key, $field);
		if (!$file) {
			$file = $this->get_posted_field('current_' . $key, $field);
		} elseif (is_array($file)) {
			$file = array_filter(array_merge($file, (array) $this->get_posted_field('current_' . $key, $field)));
		}
		return $file;
	}

	/**
	 * Get the value of a posted textarea field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected function get_posted_textarea_field($key, $field) {
		return isset($_POST[$key]) ? wp_kses_post(trim(stripslashes($_POST[$key]))) : '';
	}

	/**
	 * Get the value of a posted textarea field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected function get_posted_wp_editor_field($key, $field) {
		return $this->get_posted_textarea_field($key, $field);
	}

	/**
	 * Get posted terms for the taxonomy
	 * @param  string $key
	 * @param  array $field
	 * @return array
	 */
	protected function get_posted_term_checklist_field($key, $field) {
		if (isset($_POST['tax_input']) && isset($_POST['tax_input'][$field['taxonomy']])) {
			return array_map('absint', $_POST['tax_input'][$field['taxonomy']]);
		} else {
			return array();
		}
	}

	/**
	 * Get posted terms for the taxonomy
	 * @param  string $key
	 * @param  array $field
	 * @return int
	 */
	protected function get_posted_term_multiselect_field($key, $field) {
		return isset($_POST[$key]) ? array_map('absint', $_POST[$key]) : array();
	}

	/**
	 * Get posted terms for the taxonomy
	 * @param  string $key
	 * @param  array $field
	 * @return int
	 */
	protected function get_posted_term_select_field($key, $field) {
		return !empty($_POST[$key]) && $_POST[$key] > 0 ? absint($_POST[$key]) : '';
	}

	/**
	 * Upload a file
	 * @return  string or array
	 */
	protected function upload_file($field_key, $field) {
		if (isset($_FILES[$field_key]) && !empty($_FILES[$field_key]) && !empty($_FILES[$field_key]['name'])) {
			if (!empty($field['allowed_mime_types'])) {
				$allowed_mime_types = $field['allowed_mime_types'];
			} else {
				$allowed_mime_types = get_allowed_mime_types();
			}
			$file_urls       = array();
			$files_to_upload = wpfm_prepare_uploaded_files($_FILES[$field_key]);
			foreach ($files_to_upload as $file_to_upload) {
				$uploaded_file = wpfm_upload_file($file_to_upload, array('file_key' => $field_key, 'allowed_mime_types' => $allowed_mime_types));
				if (is_wp_error($uploaded_file)) {
					throw new Exception($uploaded_file->get_error_message());
				} else {
					$file_urls[] = $uploaded_file->url;
				}
			}
			if (!empty($field['multiple'])) {
				return $file_urls;
			} else {
				return current($file_urls);
			}
		}
	}

	/**
	 * Merge and replace $default_fields with custom fields
	 *
	 * @return array Returns merged and replaced fields
	 */
	public function merge_with_custom_fields($field_view = 'frontend') {
		$custom_food_fields  = !empty($this->get_food_manager_fieldeditor_fields()) ? $this->get_food_manager_fieldeditor_fields() : array();
		$custom_extra_options_fields  = !empty($this->get_food_manager_fieldeditor_extra_options_fields()) ? $this->get_food_manager_fieldeditor_extra_options_fields() : array();
		$custom_fields = '';
		if (!empty($custom_extra_options_fields)) {
			$custom_fields = array_merge($custom_food_fields, $custom_extra_options_fields);
		} else {
			$custom_fields = $custom_food_fields;
		}
		$default_fields = $this->get_default_fields();
		if (!get_option('food_manager_enable_categories') || (wp_count_terms('food_manager_category') == 0 && isset($custom_fields['food']['food_category']))) {
			if (isset($custom_fields['food']['food_category']))
				$custom_fields['food']['food_category']['visibility'] = false;
			unset($default_fields['food']['food_category']);
		}
		if (!get_option('food_manager_enable_food_tags') || (wp_count_terms('food_manager_tag') == 0 && isset($custom_fields['food']['food_tag']))) {
			if (isset($custom_fields['food']['food_tag']))
				$custom_fields['food']['food_tag']['visibility'] = false;
			unset($default_fields['food']['food_tag']);
		}
		if (!get_option('food_manager_enable_food_types') || (wp_count_terms('food_manager_type') == 0 && isset($custom_fields['food']['food_type']))) {
			if (isset($custom_fields['food']['food_type']))
				$custom_fields['food']['food_type']['visibility'] = false;
			unset($default_fields['food']['food_type']);
		}
		if ((wp_count_terms('food_manager_ingredient') == 0 && isset($custom_fields['food']['food_ingredient']))) {
			if (isset($custom_fields['food']['food_ingredient']))
				$custom_fields['food']['food_ingredient']['visibility'] = false;
			unset($default_fields['food']['food_ingredient']);
		}
		if (!is_array($custom_fields)) {
			$this->fields = apply_filters('merge_with_custom_fields', $default_fields, $default_fields);
			return $this->fields;
		}
		$updated_fields = !empty($custom_fields) ? array_replace_recursive($default_fields, $custom_fields) : $default_fields;
		/**
		 * Above array_replace_recursive function will replace the default fields by custom fields.
		 * If array key is not same then it will merge array. This is only case for the Radio and Select Field(In case of array if key is not same).
		 * For eg. options key it has any value or option as per user requested or overrided but array_replace_recursive will merge both 		options of default field and custom fields.
		 * User change the default value of the food_online (radio button) from Yes --> Y and No--> N then array_replace_recursive will merge both valus of the options array for food_online like options('yes'=>'yes', 'no'=>'no','y'=>'y','n'=>'n') but  we need to keep only updated options value of the food_online so we have to remove old default options values and for that we have to do the following procedure.
		 * In short: To remove default options need to replace the options array with custom options which is added by user.
		 **/
		foreach ($default_fields as $default_group_key => $default_group) {
			foreach ($default_group as $field_key => $field_value) {
				foreach ($field_value as $key => $value) {
					if (isset($custom_fields[$default_group_key][$field_key][$key]) && ($key == 'options' || is_array($value)))
						$updated_fields[$default_group_key][$field_key][$key] = $custom_fields[$default_group_key][$field_key][$key];
				}
			}
		}
		/**
		 * If default field is removed via field editor then we can not removed this field from the code because it is hardcode in the file so we need to set flag to identify to keep the record which perticular field is removed by the user.
		 * Using visibility flag we can identify those fields need to remove or keep in the Field Editor based on visibility flag value. if visibility true then we will keep the field and if visibility flag false then we will not show this default field in the field editor. (As action of user removed this field from the field editor but not removed from the code so we have to set this flag)
		 * We are getting several default fields from the addons and using theme side customization via 'add_food_fields' filter.
		 * Now, Not easy to manage filter fields and default fields of plugin in this case so we need to set this flag for identify wheather field show  or not in the field editor.
		 *
		 * If user selected admin only fields then we need to unset that fields from the frontend user.
		 **/
		if (!empty($updated_fields))
			foreach ($updated_fields as $group_key => $group_fields) {
				foreach ($group_fields as $key => $field) {
					$updated_fields[$group_key][$key] = array_map('stripslashes_deep', $updated_fields[$group_key][$key]);
					// remove if visiblity is false
					if (isset($field['visibility']) && $field['visibility'] == false)
						unset($updated_fields[$group_key][$key]);
					// remove admin fields if view type is frontend
					if (isset($field['admin_only']) &&  $field_view == 'frontend' &&  $field['admin_only'] == true)
						unset($updated_fields[$group_key][$key]);
				}
				uasort($updated_fields[$group_key], array($this, 'sort_by_priority'));
			}
		$this->fields = apply_filters('merge_with_custom_fields', $updated_fields, $default_fields);
		return $this->fields;
	}
}
