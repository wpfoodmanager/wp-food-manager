<?php
include_once('wpfm-add-food-form.php');

/**
 * WPFM_Edit_Food_Form class.
 */
class WPFM_Edit_Food_Form extends WPFM_Add_Food_Form {

	public $form_name           = 'edit-food';
	/** @var WPFM_Edit_Food_Form The single instance of the class */
	protected static $_instance = null;

	/**
	 * Main Instance
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
		$this->food_id = !empty($_REQUEST['food_id']) ? absint($_REQUEST['food_id']) : 0;
		if (!food_manager_user_can_edit_food($this->food_id)) {
			$this->food_id = 0;
		}
	}

	/**
	 * output function.
	 */
	public function output($atts = array()) {
		$this->submit_handler();
		$this->submit();
	}

	/**
	 * Submit Step
	 */
	public function submit() {
		global $wpdb;
		$food = get_post($this->food_id);
		if (empty($this->food_id) || ($food->post_status !== 'publish' && $food->post_status !== 'pending' && !food_manager_user_can_edit_pending_submissions())) {
			echo wpautop(__('Invalid listing', 'wp-food-manager'));
			return;
		}
		// Init fields
		//$this->init_fields(); We dont need to initialize with this function because of field edior
		// Now field editor function will return all the fields 
		//Get merged fields from db and default fields.
		$this->merge_with_custom_fields('frontend');
		$parent_row_fields_count = !empty(get_post_meta($food->ID, '_food_repeated_options', true)) ? get_post_meta($food->ID, '_food_repeated_options', true) : array();
		$extra_fields_options = get_post_meta($food->ID, '_food_toppings', true) ? get_post_meta($food->ID, '_food_toppings', true) : '';
		foreach ($this->fields as $group_key => $group_fields) {
			foreach ($group_fields as $key => $field) {
				if ($group_key == 'toppings') {
					foreach ($parent_row_fields_count as $row_key => $row_value) {
						$key_row_val = '';
						if ($key !== 'topping_name') {
							$key_row_val = '_' . $key . '_' . $row_value;
						} else {
							$key_row_val = $key . '_' . $row_value;
						}
						if (count($parent_row_fields_count) == "1") {
							$this->fields[$group_key][$key]['value'] = get_post_meta($food->ID, $key_row_val, true);
							if ($key == 'topping_options') {
								if (!empty($extra_fields_options)) {
									foreach ($extra_fields_options as $ext_key => $extra_fields_option) {
										$this->fields[$group_key][$key]['value'] = $extra_fields_option['topping_options'];
									}
								}
							}
						} else {
							if ($key !== 'topping_options') {
								if (isset($this->fields[$group_key][$key]['value']) && !empty($this->fields[$group_key][$key]['value'])) {
									$this->fields[$group_key][$key]['value'][] = get_post_meta($food->ID, $key_row_val, true);
									array_unshift($this->fields[$group_key][$key]['value'], "");
									unset($this->fields[$group_key][$key]['value'][0]);
								}
							}
							if ($key == 'topping_options') {
								if (!empty($extra_fields_options)) {
									foreach ($extra_fields_options as $ext_key => $extra_fields_option) {
										$this->fields[$group_key][$key]['value'][$ext_key] = $extra_fields_option['topping_options'];
										array_unshift($this->fields[$group_key][$key]['value'], "");
										unset($this->fields[$group_key][$key]['value'][0]);
									}
								}
							}
						}
					}
				} else {
					if (!isset($this->fields[$group_key][$key]['value'])) {
						if ('food_title' === $key) {
							$this->fields[$group_key][$key]['value'] = $food->post_title;
						} elseif ('food_description' === $key) {
							$this->fields[$group_key][$key]['value'] = $food->post_content;
						} elseif (!empty($field['taxonomy'])) {
							$this->fields[$group_key][$key]['value'] = wp_get_object_terms($food->ID, $field['taxonomy'], array('fields' => 'ids'));
						}elseif ('food_tag' === $key) {
							$this->fields[$group_key][$key]['value'] = wp_get_object_terms($food->ID, 'food_manager_tag', array('fields' => 'ids'));
						} else {
							$this->fields[$group_key][$key]['value'] = get_post_meta($food->ID, '_' . $key, true);
						}
					}
				}
				if (!empty($field['type']) &&  $field['type'] == 'button') {
					if (isset($this->fields[$group_key][$key]['value']) && empty($this->fields[$group_key][$key]['value'])) {
						$this->fields[$group_key][$key]['value'] = $field['placeholder'];
					}
				}
			}
		}
		$this->fields = apply_filters('add_food_fields_get_user_data', $this->fields, $food);
		wp_enqueue_script('wp-food-manager-food-submission');
		get_food_manager_template('food-submit.php', array(
			'form'               => $this->form_name,
			'food_id'             => $this->get_food_id(),
			'action'             => $this->get_action(),
			'food_fields'         => $this->get_fields('food'),
			'topping_fields'     => $this->get_fields('toppings'),
			'step'               => $this->get_step(),
			'submit_button_text' => __('Save changes', 'wp-food-manager')
		));
		do_action('wpfm_edit_food_form', $this->food_id, $food);
	}

	/**
	 * Submit Step is posted
	 */
	public function submit_handler() {
		if (empty($_POST['add_food'])) {
			return;
		}
		try {
			// Get posted values
			$values = $this->get_posted_fields();
			// Validate required
			if (is_wp_error(($return = $this->validate_fields($values)))) {
				throw new Exception($return->get_error_message());
			}
			// Update the food
			$food_title = isset($values['food']['food_title']) && !empty($values['food']['food_title']) ? $values['food']['food_title'] : '';
			$food_description = isset($values['food']['food_description']) && !empty($values['food']['food_description']) ? $values['food']['food_description'] : '';
			$this->save_food($food_title, $food_description, '', $values, false);
			// Successful
			switch (get_post_status($this->food_id)) {
				case 'publish':
					echo wp_kses_post('<div class="food-manager-message wpfm-alert wpfm-alert-success">' . __('Your changes have been saved.', 'wp-food-manager') . ' <a href="' . get_permalink($this->food_id) . '">' . __('View &rarr;', 'wp-food-manager') . '</a>' . '</div>');
					break;
				default:
					echo wp_kses_post('<div class="food-manager-message wpfm-alert wpfm-alert-success">' . __('Your changes have been saved.', 'wp-food-manager') . '</div>');
					break;
			}
		} catch (Exception $e) {
			echo wp_kses_post('<div class="food-manager-error wpfm-alert wpfm-alert-danger">' .  esc_html($e->getMessage()) . '</div>');
			return;
		}
	}
}
