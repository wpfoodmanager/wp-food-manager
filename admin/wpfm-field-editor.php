<?php

/**
 * WPFM_Field_Editor class.
 * Class for the field editor handler.
 */
class WPFM_Field_Editor {

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
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Output the field editor screen.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function output() {
		wp_enqueue_style('chosen', esc_url(WPFM_PLUGIN_URL . '/assets/css/chosen.min.css'));
		wp_enqueue_script('wp-food-manager-form-field-editor');
?>
		<div class="wrap wp-food-manager-form-editor">
			<h1 class="wp-heading-inline"><?php echo esc_html__('Form fields'); ?></h1>
			<div class="wpfm-wrap wp-food-manager-form-field-editor">
				<form method="post" id="mainform" action="<?php echo esc_url('edit.php?post_type=food_manager&page=food-manager-form-editor'); ?>">
					<?php $this->form_editor(); ?>
					<?php wp_nonce_field('save-wp-food-manager-form-field-editor'); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the fronted form editor.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	private function form_editor() {
		if (!empty($_GET['food-reset-fields']) && !empty($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'reset')) {
			delete_option('food_manager_add_food_form_fields');
			echo wp_kses_post('<div class="updated"><p>' . esc_html('The fields were successfully reset.', 'wp-food-manager') . '</p></div>');
		}

		if (!empty($_GET['toppings-reset-fields']) && !empty($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'reset')) {
			delete_option('food_manager_submit_toppings_form_fields');
			echo wp_kses_post('<div class="updated"><p>' . esc_html('The fields were successfully reset.', 'wp-food-manager') . '</p></div>');
		}

		if (!empty($_POST) && !empty($_POST['_wpnonce'])) {
			echo wp_kses_post($this->form_editor_save());
		}

		$disbled_fields = apply_filters('wpfm_admin_field_editor_disabled_fields', array('food_title', 'food_category', 'food_type', 'food_ingredients', 'food_nutritions', 'food_tag', 'topping_name', 'topping_description', 'topping_options'));
		$field_types    = apply_filters(
			'food_manager_form_field_types',
			array(
				'text'             => __('Text', 'wp-food-manager'),
				'checkbox'         => __('Checkbox', 'wp-food-manager'),
				'date'             => __('Date', 'wp-food-manager'),
				'file'             => __('File', 'wp-food-manager'),
				'hidden'           => __('Hidden', 'wp-food-manager'),
				'multiselect'      => __('Multiselect', 'wp-food-manager'),
				'number'           => __('Number', 'wp-food-manager'),
				'radio'            => __('Radio', 'wp-food-manager'),
				'select'           => __('Select', 'wp-food-manager'),
				'term-checklist'   => __('Term Checklist', 'wp-food-manager'),
				'term-multiselect' => __('Term Multiselect', 'wp-food-manager'),
				'term-select'      => __('Term Select', 'wp-food-manager'),
				'term-select-multi-appearance'      => __('Term Multi Select Appearance', 'wp-food-manager'),
				'textarea'         => __('Textarea', 'wp-food-manager'),
				'wp-editor'        => __('WP Editor', 'wp-food-manager'),
				'url'              => __('URL', 'wp-food-manager'),
				'options'    => __('Options', 'wp-food-manager'),
				'switch'    => __('Switch', 'wp-food-manager'),
			)
		);

		$GLOBALS['food_manager']->forms->get_form('add-food', array());
		$form_add_food_instance = call_user_func(array('WPFM_Add_Food_Form', 'instance'));
		$food_fields = $form_add_food_instance->merge_with_custom_fields('backend');
		$fields = array_merge($food_fields);
		$add_food_form_fields = get_option('food_manager_add_food_form_fields');
		$add_toppings_form_fields = get_option('food_manager_submit_toppings_form_fields');

		foreach ($fields  as $group_key => $group_fields) {
			if (empty($group_fields)) {
				continue;
			}
		?>
			<div class="wp-food-manager-food-form-field-editor <?php echo esc_attr($group_key); ?>">
				<h3><?php printf(esc_html__('%s form fields', 'wp-food-manager'), ucfirst(str_replace("options", "Toppings", str_replace("_", " ", $group_key)))); ?></h3>
				<table class="widefat">
					<thead>
						<tr>
							<th width="1%">&nbsp;</th>
							<th><?php echo esc_html__('Field Label', 'wp-food-manager'); ?></th>
							<th width="1%"><?php echo esc_html__('Type', 'wp-food-manager'); ?></th>
							<th><?php echo esc_html__('Description', 'wp-food-manager'); ?></th>
							<th><?php echo esc_html__('Placeholder / Options', 'wp-food-manager'); ?></th>
							<th width="1%"><?php echo esc_html__('Meta Key', 'wp-food-manager'); ?></th>
							<th width="1%"><?php echo esc_html__('Only For Admin', 'wp-food-manager'); ?></th>
							<th width="1%"><?php echo esc_html__('Priority', 'wp-food-manager'); ?></th>
							<?php if ($group_key != 'toppings') { ?>
								<th width="1%"><?php echo esc_html__('Tab Group (Only For Admin)', 'wp-food-manager'); ?></th>
							<?php } ?>
							<th width="1%"><?php echo esc_html__('Validation', 'wp-food-manager'); ?></th>
							<th width="1%" class="field-actions">&nbsp;</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="4">
								<a class="button add-field" href="#"><?php echo esc_html__('Add field', 'wp-food-manager'); ?></a>
							</th>
							<th colspan="6" class="save-actions">
								<a href="<?php echo esc_url(wp_nonce_url(add_query_arg($group_key . '-reset-fields', 1), 'reset')); ?>" class="reset"><?php echo esc_html__('Reset to default', 'wp-food-manager'); ?></a>
								<input type="submit" class="save-fields button-primary" value="<?php echo esc_html__('Save Changes', 'wp-food-manager'); ?>" />
							</th>
						</tr>
					</tfoot>
					<tbody id="form-fields" data-field="
                <?php

				ob_start();
				$index     = -1;
				$field_key = '';
				$field     = array(
					'type'        => 'text',
					'label'       => '',
					'placeholder' => '',
				);

				include 'wpfm-field-editor-form-field.php';
				echo esc_attr(ob_get_clean());
				if (isset($group_fields) && !empty($group_fields)) {
					foreach ($group_fields as $field_key => $field) {

						if ($group_key == 'food') {
							if ($add_food_form_fields) {
								if (trim($field['label']) != '' && isset($add_food_form_fields['food'][$field_key])) {
									$index++;
									include 'wpfm-field-editor-form-field.php';
								}
							} else {
								if (trim($field['label']) != '') {
									$index++;
									include 'wpfm-field-editor-form-field.php';
								}
							}
						}  elseif($group_key == 'toppings'){
							if ($add_toppings_form_fields) {
								if (trim($field['label']) != '' && isset($add_toppings_form_fields['toppings'][$field_key])) {
									$index++;
									include 'wpfm-field-editor-form-field.php';
								}
							} else {
								if (trim($field['label']) != '') {
									$index++;
									include 'wpfm-field-editor-form-field.php';
								}
							}
						} else {
							if (trim($field['label']) != '') {
								$index++;
								include 'wpfm-field-editor-form-field.php';
							}
						}
					}
				}
				?>					
                </tbody>
            </table>
        </div>
        <?php
		}
	}

	/**
	 * Save the form fields.
	 * 
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private function child_form_editor_save($field) {
		$index = 0;
		$child_fields = array();

		foreach ($field['fields'] as $field_key => $field_value) {
			$index++;
			$field['fields'][$field_key]['priority'] = $index;
			$field['fields'][$field_key]['label'] = trim($field_value['label']);
			if (isset($field_value['type']) && !in_array($field_value['type'], array('term-select', 'term-select-multi-appearance', 'term-multiselect', 'term-checklist'))) {
				unset($field['fields'][$field_key]['taxonomy']);
			}

			if (isset($field_value['type']) && $field_value['type'] == 'select' || $field_value['type'] == 'radio' || $field_value['type'] == 'multiselect' || $field_value['type'] == 'button-options' || $field_value['type'] == 'checkbox') {
				if (isset($field_value['options']) && !empty($field_value['options'])) {
					$field_value['options'] = explode('|', $field_value['options']);
					$temp_options = array();
					foreach ($field_value['options'] as $val) {
						$option_key = explode(':', $val);
						if (isset($option_key[1])) {
							$temp_options[strtolower(str_replace(' ', '_', trim($option_key[0])))] = trim($option_key[1]);
						} else {
							$temp_options[strtolower(str_replace(' ', '_', trim($option_key[0])))] = trim($option_key[0]);
						}
					}
					$field['fields'][$field_key]['options'] = $temp_options;
				}
			} else {
				unset($field['fields'][$field_key]['options']);
			}

			if (!is_int($field_key)) {
				continue;
			}

			if (isset($field_value['label'])) {
				$label_key = str_replace(' ', '_', $field_value['label']);
				$field['fields'][strtolower($label_key)] = $field['fields'][$field_key];
			}

			unset($field['fields'][$field_key]);
		}
		return $field['fields'];
	}

	/**
	 * Save the form fields.
	 * 
	 * @access private
	 * @return void
	 * @since 1.0.0
	 */
	private function form_editor_save() {
		if (wp_verify_nonce($_POST['_wpnonce'], 'save-wp-food-manager-form-field-editor')) {
			$food_field     = !empty($_POST['food']) ? $this->sanitize_array($_POST['food']) : array();
			$toppings = !empty($_POST['toppings']) ? $this->sanitize_array($_POST['toppings']) : array();
			$index           = 0;
			if (!empty($food_field)) {
				$new_fields = array(
					'food'     => $food_field,
					'toppings'     => $toppings,
				);

				// Find the numers keys from the fields array and replace with lable if label not exist remove that field.
				foreach ($new_fields as $group_key => $group_fields) {
					$index = 0;
					foreach ($group_fields as $field_key => $field_value) {

						$index++;
						if (isset($new_fields[$group_key][$field_key]['type']) && $new_fields[$group_key][$field_key]['type'] === 'switch') {
							$new_fields[$group_key][$field_key]['required'] = 0;
						}
						if (isset($new_fields[$group_key][$field_key]['type']) && $new_fields[$group_key][$field_key]['type'] === 'group') {
							if (isset($field_value['fields']) && !empty($field_value['fields'])) {
								$child_fields                                     = $this->child_form_editor_save($field_value);
								$new_fields[$group_key][$field_key]['fields'] = $child_fields;
							}
						}

						$new_fields[$group_key][$field_key]['priority'] = $index;
						$new_fields[$group_key][$field_key]['label'] = trim($new_fields[$group_key][$field_key]['label']);
						if (isset($new_fields[$group_key][$field_key]['type']) && !in_array($new_fields[$group_key][$field_key]['type'], array('term-select', 'term-select-multi-appearance', 'term-multiselect', 'term-checklist'))) {
							unset($new_fields[$group_key][$field_key]['taxonomy']);
						}

						if (isset($new_fields[$group_key][$field_key]['type']) && ($new_fields[$group_key][$field_key]['type'] == 'select' || $new_fields[$group_key][$field_key]['type'] == 'radio' || $new_fields[$group_key][$field_key]['type'] == 'multiselect' || $new_fields[$group_key][$field_key]['type'] == 'button-options' || $new_fields[$group_key][$field_key]['type'] == 'checkbox')) {
							if (isset($new_fields[$group_key][$field_key]['options'])) {
								$new_fields[$group_key][$field_key]['options'] = explode('|', $new_fields[$group_key][$field_key]['options']);
								$temp_options = array();
								foreach ($new_fields[$group_key][$field_key]['options'] as $val) {
									$option_key = explode(':', $val);
									if (isset($option_key[1])) {
										$temp_options[strtolower(str_replace(' ', '_', trim($option_key[0])))] = trim($option_key[1]);
									} else {
										$temp_options[strtolower(str_replace(' ', '_', trim($option_key[0])))] = trim($option_key[0]);
									}
								}
								$new_fields[$group_key][$field_key]['options'] = $temp_options;
							}
						} else {
							unset($new_fields[$group_key][$field_key]['options']);
						}

						if (!is_int($field_key)) {
							continue;
						}

						if (isset($new_fields[$group_key][$field_key]['label'])) {
							$label_key = str_replace(' ', '_', $new_fields[$group_key][$field_key]['label']);
							$new_fields[$group_key][strtolower($label_key)] = $new_fields[$group_key][$field_key];
						}
						unset($new_fields[$group_key][$field_key]);
					}
				}
				// merge field with default fields.
				$GLOBALS['food_manager']->forms->get_form('add-food', array());
				$form_add_food_instance = call_user_func(array('WPFM_Add_Food_Form', 'instance'));
				$food_fields = $form_add_food_instance->get_default_food_fields();

				// if field in not exist in new fields array then make visiblity false.
				if (!empty($food_fields)) {
					foreach ($food_fields as $group_key => $group_fields) {
						foreach ($group_fields as $key => $field) {
							if (!isset($new_fields[$group_key][$key])) {
								$new_fields[$group_key][$key]               = $field;
								$new_fields[$group_key][$key]['visibility'] = 0; // it will make visiblity false means removed from the field editor.
							}
						}
					}
				}

				if (isset($new_fields['food'])) {
					update_option('food_manager_add_food_form_fields', array('food' => $new_fields['food']));
				}

				if (isset($new_fields['toppings'])) {
					update_option('food_manager_submit_toppings_form_fields', array('toppings' => $new_fields['toppings']));
				}

				// This will be removed in future.
				$result = update_option('food_manager_form_fields', $new_fields);
			}
		}

		echo wp_kses_post('<div class="updated"><p>' . esc_attr__('The fields were successfully saved.', 'wp-food-manager') . '</p></div>');
	}

	/**
	 * Sanitize a 2 dimension array.
	 *
	 * @access private
	 * @param  array $array
	 * @return array
	 * @since 1.0.0
	 */
	private function sanitize_array($input) {
		if (is_array($input)) {
			foreach ($input as $k => $v) {
				$input[$k] = $this->sanitize_array($v);
			}
			return $input;
		} else {
			return sanitize_text_field($input);
		}
	}
}

WPFM_Field_Editor::instance();
