<?php

/**
 * Abstract WPFM_Form class.
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
	 * Process function - all processing code if needed can also change view if step is complete.
	 * 
	 * @access public
	 * @return string
	 * @since 1.0.0
	 */
	public function process() {
		// Reset cookie.
		if (isset($_GET['new']) && isset($_COOKIE['wpfm-adding-food-id']) && isset($_COOKIE['wpfm-adding-food-key']) && get_post_meta(sanitize_text_field($_COOKIE['wpfm-adding-food-id']), '_adding_key', true) == $_COOKIE['wpfm-adding-food-key']) {
			delete_post_meta(sanitize_text_field($_COOKIE['wpfm-adding-food-id']), '_adding_key');
			setcookie('wpfm-adding-food-id', '', 0, COOKIEPATH, COOKIE_DOMAIN, false);
			setcookie('wpfm-adding-food-key', '', 0, COOKIEPATH, COOKIE_DOMAIN, false);
			wp_redirect(remove_query_arg(array('new', 'key'), esc_url($_SERVER['REQUEST_URI'])));
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
	 * Return the form name which is set initially.
	 * 
	 * @access public
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_form_name() {
		return $this->form_name;
	}

	/**
	 * output function. Call the view handler.
	 * 
	 * @access public
	 * @param array $atts
	 * @return void
	 * @since 1.0.0
	 */
	public function output($atts = array()) {
		$step_key = $this->get_step_key($this->step);
		$this->show_errors();

		if ($step_key && is_callable($this->steps[$step_key]['view'])) {
			call_user_func($this->steps[$step_key]['view'], $atts);
		}
	}

	/**
	 * Store the error.
	 * 
	 * @access public
	 * @param string $error
	 * @return void
	 * @since 1.0.0
	 */
	public function add_error($error) {
		$this->errors[] = sanitize_text_field($error);
	}

	/**
	 * Display the error which is set by the plugin.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function show_errors() {
		foreach ($this->errors as $error) {
			echo '<div class="food-manager-error wpfm-alert wpfm-alert-danger">' . esc_html($error) . '</div>';
		}
	}

	/**
	 * Get action (URL for forms to post to).
	 * As of 1.0.0 this defaults to the current page permalink.
	 *
	 * @access public
	 * @return string
	 * @since 1.0.0
	 */
	public function get_action() {
		return esc_url_raw($this->action ? $this->action : wp_unslash($_SERVER['REQUEST_URI']));
	}

	/**
	 * Get step from outside of the class.
	 *
	 * @access public
	 * @return $this->step
	 * @since 1.0.0
	 */
	public function get_step() {
		return $this->step;
	}

	/**
	 * Get steps from outside of the class.
	 *
	 * @access public
	 * @return $this->step
	 * @since 1.0.0
	 */
	public function get_steps() {
		return $this->steps;
	}

	/**
	 * Get step key from outside of the class.
	 *
	 * @access public
	 * @param string $step
	 * @return mixed
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
	 * Get step from outside of the class.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function set_step($step) {
		$this->step = absint($step);
	}

	/**
	 * Increase step from outside of the class.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function next_step() {
		$this->step++;
	}

	/**
	 * Decrease step from outside of the class.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function previous_step() {
		$this->step--;
	}

	/**
	 * Get the fields from the field editor.
	 *
	 * @access public
	 * @param string $key
	 * @return array
	 * @since 1.0.0
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
	 * Sort array by priority value.
	 *
	 * @access public
	 * @param array $a
	 * @param array $b
	 * @return int
	 * @since 1.0.0
	 */
	protected function sort_by_priority($item1, $item2) {
		if ($item1['priority'] == $item2['priority']) {
			return 0;
		}
		return ($item1['priority'] < $item2['priority']) ? -1 : 1;
	}

	/**
	 * Init form fields.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	protected function init_fields() {
		$this->fields = array();
	}

	/**
	 * Get post data for fields.
	 *
	 * @access protected
	 * @return array of data
	 * @since 1.0.0
	 */
	protected function get_posted_fields() {
		global $post;

		// Init fields.
		// $this->init_fields(); We dont need to initialize with this function because of field editor.
		// Now field editor function will return all the fields.
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

		if (isset($_POST['food_nutritions']) && !empty($_POST['food_nutritions'])) {
			$values['food_nutritions'] = wp_unslash($_POST['food_nutritions']);
		}

		if (isset($_POST['food_ingredients']) && !empty($_POST['food_ingredients'])) {
			$values['food_ingredients'] = wp_unslash($_POST['food_ingredients']);
		}

		if (($option_value_count && is_array($option_value_count)) && ($repeated_options && is_array($repeated_options))) {
			foreach ($ext_multi_options as $option_count => $option_value) {
				foreach ($this->fields as $group_key => $group_fields) {
					foreach ($group_fields as $key => $field) {

						// Get the value.
						$field_type = str_replace('-', '_', $field['type']);
						if ($handler = apply_filters("food_manager_get_posted_{$field_type}_field", false)) {
							$values[$group_key][$key] = call_user_func($handler, $key, $field);
							if (is_string($values[$group_key][$key])) {
								$values[$group_key][$key] = wp_unslash($values[$group_key][$key]);
							}
						} elseif ($group_key == "toppings") {
							$key2 = "";
							$first_key = '';

							if ($key == "topping_name") {
								$first_key = $key . "_" . $option_count;
								$key2 = $key . "_" . $option_count;
							} else {
								$key2 = "_" . $key . "_" . $option_count;
							}

							$first_out = str_replace(" ", "_", strtolower($this->get_posted_field($first_key, $field)));
							$output = $this->get_posted_field($key2, $field);

							if ($field['type'] == 'file') {
								$output = $this->get_posted_field($key2, $field);
							}

							$values[$group_key][$first_out][$key] = $output;
							$output2 = array();

							if ($key == "topping_options") {
								if ($option_value && is_array($option_value)) {
									foreach ($option_value as $option_value_count) {
										$output2[$option_value_count] = apply_filters('wpfm_topping_options_values_array', array(
											'option_name' => isset($_POST[$option_count . '_option_name_' . $option_value_count]) ? $_POST[$option_count . '_option_name_' . $option_value_count] : '',
											'option_price' => isset($_POST[$option_count . '_option_price_' . $option_value_count]) ? $_POST[$option_count . '_option_price_' . $option_value_count] : '',
										), array('option_count' => $option_count, 'option_value_count' => $option_value_count));
										$values[$group_key][$first_out][$key] = $output2;
									}
								}
							}
						} elseif (method_exists($this, "get_posted_{$field_type}_field")) {
							$values[$group_key][$key] = call_user_func(array($this, "get_posted_{$field_type}_field"), $key, $field);
							if (is_string($values[$group_key][$key])) {
								$values[$group_key][$key] = wp_unslash($values[$group_key][$key]);
							}
						} else {
							$values[$group_key][$key] = $this->get_posted_field($key, $field);
							if (is_string($values[$group_key][$key])) {
								$values[$group_key][$key] = wp_unslash($values[$group_key][$key]);
							}
						}
					}
				}
			}
		} else {
			foreach ($this->fields as $group_key => $group_fields) {
				foreach ($group_fields as $key => $field) {

					// Get the value.
					$field_type = str_replace('-', '_', $field['type']);
					if ($handler = apply_filters("food_manager_get_posted_{$field_type}_field", false)) {
						$values[$group_key][$key] = call_user_func($handler, $key, $field);
						if (is_string($values[$group_key][$key])) {
							$values[$group_key][$key] = wp_unslash($values[$group_key][$key]);
						}
					} elseif (method_exists($this, "get_posted_{$field_type}_field")) {
						$values[$group_key][$key] = call_user_func(array($this, "get_posted_{$field_type}_field"), $key, $field);
						if (is_string($values[$group_key][$key])) {
							$values[$group_key][$key] = wp_unslash($values[$group_key][$key]);
						}
					} else {
						$values[$group_key][$key] = $this->get_posted_field($key, $field);
						if (is_string($values[$group_key][$key])) {
							$values[$group_key][$key] = wp_unslash($values[$group_key][$key]);
						}
					}

					// Set fields value.
					$this->fields[$group_key][$key]['value'] = $values[$group_key][$key];
				}
			}
		}
		return $values;
	}

	/**
	 * Get the value of a repeated fields (e.g. repeated).
	 *
	 * @access protected
	 * @param string $field_prefix
	 * @param mixed $fields
	 * @return array of data
	 * @since 1.0.0
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
								$file = $this->get_posted_field($field_name, $field);
							} elseif (is_array($file)) {
								$file = array_filter(array_merge($file, (array) $this->get_posted_field($field_name, $field)));
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
								$item[$key] = array_filter(array_map(array($this, 'sanitize_posted_field'), array_map('stripslashes', $_POST[$field_name])));
							} else {
								$item[$key] = $this->sanitize_posted_field($_POST[$field_name]);
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
	 * Get the value of a posted repeated field.
	 *
	 * @access protected
	 * @param string $key
	 * @param array $field
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_posted_repeated_field($key, $field) {
		return  $this->get_repeated_field($key, $field['fields']);
	}

	/**
	 * Navigates through an array and sanitizes the field.
	 *
	 * @access protected
	 * @param array|string $value The array or string to be sanitized.
	 * @return array|string $value The sanitized array (or string from the callback).
	 * @since 1.0.0
	 */
	protected function sanitize_posted_field($value) {
		// Decode URLs.
		if (is_string($value) && (strstr($value, 'http:') || strstr($value, 'https:'))) {
			$value = urldecode($value);
		}

		// Sanitize value.
		$value = is_array($value) ? array_map(array($this, 'sanitize_posted_field'), $value) : sanitize_text_field(stripslashes(trim($value)));

		return $value;
	}

	/**
	 * Get the value of a posted field.
	 *
	 * @access protected
	 * @param  string $key
	 * @param  array $field
	 * @return string|array
	 * @since 1.0.0
	 */
	protected function get_posted_field($key, $field) {
		return isset($_POST[$key]) ? $this->sanitize_posted_field($_POST[$key]) : '';
	}

	/**
	 * Get the value of a posted multiselect field.
	 *
	 * @access protected
	 * @param  string $key
	 * @param  array $field
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_posted_multiselect_field($key, $field) {
		return isset($_POST[$key]) ? array_map('sanitize_text_field', $_POST[$key]) : array();
	}

	/**
	 * Get the value of a posted file field.
	 *
	 * @access protected
	 * @param  string $key
	 * @param  array $field
	 * @return string|array
	 * @since 1.0.0
	 */
	protected function get_posted_file_field($key, $field) {
		$file = $this->upload_file($key, $field);

		if (!$file) {
			$file = $this->get_posted_field($key, $field);
		} elseif (is_array($file)) {
			$file = array_filter(array_merge($file, (array) $this->get_posted_field($key, $field)));
		}

		return $file;
	}

	/**
	 * Get the value of a posted textarea field.
	 *
	 * @access protected
	 * @param string $key
	 * @param array $field
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_posted_textarea_field($key, $field) {
		return isset($_POST[$key]) ? wp_kses_post(trim(stripslashes($_POST[$key]))) : '';
	}

	/**
	 * Get the value of a posted textarea field.
	 *
	 * @access protected
	 * @param string $key
	 * @param array $field
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_posted_wp_editor_field($key, $field) {
		return $this->get_posted_textarea_field($key, $field);
	}

	/**
	 * Get posted terms for the taxonomy.
	 *
	 * @access protected
	 * @param string $key
	 * @param array $field
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_posted_term_checklist_field($key, $field) {
		if (isset($_POST['tax_input']) && isset($_POST['tax_input'][$field['taxonomy']])) {
			return array_map('absint', $_POST['tax_input'][$field['taxonomy']]);
		} else {
			return array();
		}
	}

	/**
	 * Get posted terms for the taxonomy.
	 *
	 * @access protected
	 * @param string $key
	 * @param array $field
	 * @return int
	 * @since 1.0.0
	 */
	protected function get_posted_term_multiselect_field($key, $field) {
		return isset($_POST[$key]) ? array_map('absint', $_POST[$key]) : array();
	}

	/**
	 * Get posted terms for the taxonomy.
	 *
	 * @access protected
	 * @param string $key
	 * @param array $field
	 * @return int
	 * @since 1.0.0
	 */
	protected function get_posted_term_select_field($key, $field) {
		return !empty($_POST[$key]) && $_POST[$key] > 0 ? absint($_POST[$key]) : '';
	}

	/**
	 * Upload a file which is set in from editor.
	 *
	 * @access protected
	 * @param string $field_key
	 * @param mixed $field
	 * @return string or array
	 * @since 1.0.0
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
	 * Merge and replace $default_fields with custom fields.
	 *
	 * @access public
	 * @param string $field_view
	 * @return array Returns merged and replaced fields.
	 * @since 1.0.0
	 */
	public function merge_with_custom_fields($field_view = 'frontend') {
		$custom_food_fields = !empty($this->get_food_manager_fieldeditor_fields()) ? $this->get_food_manager_fieldeditor_fields() : array();
		$custom_toppings_fields = !empty($this->get_food_manager_fieldeditor_toppings_fields()) ? $this->get_food_manager_fieldeditor_toppings_fields() : array();
		$custom_fields = '';

		if (!empty($custom_toppings_fields)) {
			$custom_fields = array_merge($custom_food_fields, $custom_toppings_fields);
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

		if ((wp_count_terms('food_manager_ingredient') == 0 && isset($custom_fields['food']['food_ingredients']))) {
			if (isset($custom_fields['food']['food_ingredients']))
				$custom_fields['food']['food_ingredients']['visibility'] = false;
			unset($default_fields['food']['food_ingredients']);
		}

		if (!is_array($custom_fields)) {
			$this->fields = apply_filters('merge_with_custom_fields', $default_fields, $default_fields);
			return $this->fields;
		}
		$updated_fields = !empty($custom_fields) ? array_replace_recursive($default_fields, $custom_fields) : $default_fields;

		/**
		 * Above array_replace_recursive function will replace the default fields by custom fields.
		 * If array key is not the same then it will merge the array. This is only the case for the Radio and Select Field (In case of array if the key is not the same).
		 * For example, options key, if it has any value or option as per user requested or overridden, but array_replace_recursive will merge both options of the default field and custom fields.
		 * If the user changes the default value of the food_online (radio button) from Yes --> Y and No --> N, then array_replace_recursive will merge both values of the options array for food_online like options('yes' => 'yes', 'no' => 'no', 'y' => 'y', 'n' => 'n'), but we need to keep only the updated options value of the food_online so we have to remove the old default options values and for that, we have to do the following procedure.
		 * In short: To remove default options, we need to replace the options array with custom options that are added by the user.
		 **/
		foreach ($default_fields as $default_group_key => $default_group) {
			foreach ($default_group as $field_key => $field_value) {
				foreach ($field_value as $key => $value) {
					if (isset($custom_fields[$default_group_key][$field_key][$key]) && ($key == 'options' || is_array($value))) {
						$updated_fields[$default_group_key][$field_key][$key] = $custom_fields[$default_group_key][$field_key][$key];
					}
				}
			}
		}

		/**
		 * If the default field is removed via the field editor, then we cannot remove this field from the code because it is hardcoded in the file, so we need to set a flag to identify which particular field is removed by the user.
		 * Using the visibility flag, we can identify those fields that need to be removed or kept in the Field Editor based on the visibility flag value. If the visibility is true, then we will keep the field, and if the visibility flag is false, then we will not show this default field in the field editor. (As an action of the user removed this field from the field editor but not removed from the code, so we have to set this flag).
		 * We are getting several default fields from the addons and using theme-side customization via the 'add_food_fields' filter.
		 * Now, it's not easy to manage filter fields and default fields of the plugin in this case, so we need to set this flag to identify whether the field should be shown or not in the field editor.
		 *
		 * If the user selected admin-only fields, then we need to unset those fields from the frontend user.
		 **/
		if (!empty($updated_fields)) {
			foreach ($updated_fields as $group_key => $group_fields) {
				foreach ($group_fields as $key => $field) {
					$updated_fields[$group_key][$key] = array_map('stripslashes_deep', $updated_fields[$group_key][$key]);

					// remove if visibility is false
					if (isset($field['visibility']) && $field['visibility'] == false) {
						unset($updated_fields[$group_key][$key]);
					}

					// remove admin fields if view type is frontend
					if (isset($field['admin_only']) && $field_view == 'frontend' && $field['admin_only'] == true) {
						unset($updated_fields[$group_key][$key]);
					}
				}
				uasort($updated_fields[$group_key], array($this, 'sort_by_priority'));
			}
		}
		$this->fields = apply_filters('merge_with_custom_fields', $updated_fields, $default_fields);
		return $this->fields;
	}
}
