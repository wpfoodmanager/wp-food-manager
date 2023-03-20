<?php

/**
 * This file use to cretae fields of wp food manager at admin side.
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WPFM_Writepanels {

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
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * food_manager_data function.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	public function food_manager_data($post) {
		global $post, $thepostid;
		$thepostid = $post->ID;
		wp_enqueue_script('wpfm-admin');
		wp_nonce_field('save_meta_data', 'food_manager_nonce');
		include('templates/food-data-tabs.php');
	}

	/**
	 * food_manager_data_icons function.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	public function food_manager_menu_data_icons($post) {
		global $post, $thepostid;
		$thepostid = $post->ID;
		wp_enqueue_script('wpfm-admin');
		wp_nonce_field('save_meta_data', 'food_manager_nonce'); ?>
		<div class="wpfm-admin-food-menu-container wpfm-flex-col wpfm-admin-postbox-meta-data">
			<div class="wpfm-admin-postbox-meta-data">
				<div class="wpfm-admin-menu-selection wpfm-admin-postbox-form-field">
					<?php food_manager_dropdown_selection(array(
						'multiple' => false, 'show_option_all' => __('All category', 'wp-food-manager'),
						'id' => 'wpfm-admin-food-selection',
						'taxonomy' => 'food_manager_category',
						'hide_empty' => false,
						'pad_counts' => true,
						'show_count' => true,
						'hierarchical' => false,
					)); ?>
				</div>
				<div class="wpfm-admin-menu-selection wpfm-admin-postbox-form-field">
					<?php food_manager_dropdown_selection(array(
						'multiple' => false, 'show_option_all' => __('All food types', 'wp-food-manager'),
						'id' => 'wpfm-admin-food-types-selection',
						'taxonomy' => 'food_manager_type',
						'hide_empty' => false,
						'pad_counts' => true,
						'show_count' => true,
						'hierarchical' => false,
						'name' => 'food_type',
					)); ?>
				</div>
			</div>
			<div class="wpfm-admin-food-menu-items">
				<?php $item_ids = get_post_meta($thepostid, '_food_item_ids', true); ?>
				<ul class="wpfm-food-menu menu menu-item-bar ">
					<?php if ($item_ids && is_array($item_ids)) { ?>
						<?php foreach ($item_ids as $key => $id) { ?>
							<li class="menu-item-handle" data-food-id="<?= $id; ?>">
								<div class="wpfm-admin-left-col">
									<span class="dashicons dashicons-menu"></span>
									<span class="item-title"><?php echo esc_html(get_the_title($id)); ?></span>
								</div>
								<div class="wpfm-admin-right-col">
									<a href="javascript:void(0);" class="wpfm-food-item-remove">
										<span class="dashicons dashicons-dismiss"></span>
									</a>
								</div>
								<input type="hidden" name="wpfm_food_listing_ids[]" value="<?= $id; ?>" />
							</li>
					<?php }
					} ?>
				</ul>
				<?php if ($item_ids && is_array($item_ids)) { ?>
					<span class="no-menu-item-handle" style="display: none;">Selected category has no food.</span>
				<?php } else { ?>
					<span class="no-menu-item-handle">Selected category has no food.</span>
				<?php } ?>
			</div>
		</div>
	<?php
	}

	/**
	 * food_manager_data function.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	public function food_manager_menu_data($post) {
		global $post, $thepostid;
		$thepostid = $post->ID;
		wp_enqueue_script('wpfm-admin');
		wp_nonce_field('save_meta_data', 'food_manager_nonce');
		$icon_arrs = wpfm_get_dashicons();
		$food_icon_arrs = wpfm_get_font_food_icons();
		echo '<div class="wpfm-parent-icons"><input type="text" id="wpfm_icon_search" name="wpfm_icon_search" placeholder="Icon Search"><span class="wpfm-searh-clear"><i class="fa fa-times"></i></span></div>';
		echo '<div class="no-radio-icons"><strong>No icons found!</strong></div>';
		echo "<div class='wpfm-food-icon-class'>";
		foreach ($icon_arrs as $key => $icon_arr) {
			$radio_checked = (get_post_meta($thepostid, 'wpfm_radio_icons', true) === $key) ? "checked" : "";
			echo '<div class="sub-font-icon"><input type="radio" id="' . $key . '" name="radio_icons" value="' . $key . '" ' . $radio_checked . '><label for="' . $key . '"><span class="wpfm-key-name">' . $key . '</span><i class="dashicons ' . $key . '"></i></label></div>';
		}
		foreach ($food_icon_arrs as $key => $icon_arr) {
			$radio_checked = (get_post_meta($thepostid, 'wpfm_radio_icons', true) === $key) ? "checked" : "";
			$key_name = str_replace("wpfm-menu-", "", $key);
			echo '<div class="sub-font-icon"><input type="radio" id="' . $key . '" name="radio_icons" value="' . $key . '" ' . $radio_checked . '><label for="' . $key . '"><span class="wpfm-key-name">' . $key_name . '</span>';
			if ($key_name == 'fast-cart') {
				echo '<span class="wpfm-menu wpfm-menu-fast-cart"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></span>';
			} elseif ($key_name == 'rice-bowl') {
				echo '<span class="wpfm-menu wpfm-menu-rice-bowl"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>';
			} else {
				echo '<span class="wpfm-menu ' . $key . '"></span>';
			}
			echo "</span></label></div>";
		}
		echo "</div>";
	}

	/**
	 * Return array of tabs to show.
	 *
	 * @return array
	 */
	private function get_food_data_tabs() {
		$tabs = apply_filters(
			'wpfm_food_data_tabs',
			array(
				'general'        => array(
					'label'    => __('General', 'wp-food-manager'),
					'target'   => 'general_food_data_content',
					'class'    => array(''),
					'priority' => 1,
				),
				'extra-options'        => array(
					'label'    => __('Toppings', 'wp-food-manager'),
					'target'   => 'extra_options_food_data_content',
					'class'    => array(),
					'priority' => 2,
				),
				'ingredient'        => array(
					'label'    => __('Ingredients', 'wp-food-manager'),
					'target'   => 'ingredient_food_data_content',
					'class'    => array(''),
					'priority' => 3,
				),
				'nutritions'        => array(
					'label'    => __('Nutritions', 'wp-food-manager'),
					'target'   => 'nutritions_food_data_content',
					'class'    => array(''),
					'priority' => 4,
				),
				'advanced'        => array(
					'label'    => __('Advanced', 'wp-food-manager'),
					'target'   => 'advanced_food_data_content',
					'class'    => array(''),
					'priority' => 5,
				),
			)
		);
		// Sort tabs based on priority.
		uasort($tabs, array($this, 'sort_by_priority'));
		return $tabs;
	}

	public function output_tabs() {
		global $post, $thepostid;
		$thepostid = $post->ID;
		include 'templates/food-data-general.php';
		include 'templates/food-data-extra-options.php';
		include 'templates/food-data-ingredient.php';
		include 'templates/food-data-nutrition.php';
		include 'templates/food-data-advanced.php';
	}

	/**
	 * food_manager_data_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function food_manager_data_fields() {
		global $post;
		$current_user = wp_get_current_user();
		$fields =  $GLOBALS['food_manager']->forms->get_form_fields('submit-food', 'backend');
		$fields = apply_filters('food_manager_food_data_fields', $fields);
		if (isset($fields['food']['food_title']))
			unset($fields['food']['food_title']);
		if (isset($fields['food']['food_description']))
			unset($fields['food']['food_description']);
		uasort($fields, array($this, 'sort_by_priority'));
		return $fields;
	}

	/**
	 * Sort array by priority value
	 */
	protected function sort_by_priority($a, $b) {
		if (!isset($a['priority']) || !isset($b['priority']) || $a['priority'] === $b['priority']) {
			return 0;
		}
		return ($a['priority'] < $b['priority']) ? -1 : 1;
	}

	/**
	 * input_file function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_file($key, $field) {
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (empty($field['placeholder'])) {
			$field['placeholder'] = 'http://';
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>" data-field-name="<?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>:
				<?php if (!empty($field['description'])) : ?>
					<span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
				<?php endif; ?>
			</label>
			<span class="wpfm-input-field">
				<?php
				if (!empty($field['multiple'])) {  ?>
					<span class="file_url">
						<?php foreach ((array) $field['value'] as $value) { ?>
							<span class="food-manager-uploaded-file multiple-file">
								<input type="hidden" name="<?php echo esc_attr($name); ?>[]" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($value); ?>" />
								<span class="food-manager-uploaded-file-preview">
									<?php if (in_array(pathinfo($value, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
										<img src="<?php echo esc_attr($value); ?>">
										<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
										<?php else :
										if (!wpfm_begnWith($value, "http")) {
											$value	= '';
										}
										if (!empty($value)) { ?>
											<span class="wpfm-icon">
												<strong style="display: block; padding-top: 5px;"><?php echo esc_attr(wp_basename($value)); ?></strong>
												<a target="_blank" href="<?php echo esc_attr($value); ?>"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>
											</span>
											<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
									<?php }
									endif; ?>
								</span>
							</span>
						<?php } ?>
					</span>
					<button class="button button-small wp_food_manager_upload_file_button_multiple" style="display: block;" data-uploader_button_text="<?php esc_attr_e('Use file', 'wp-food-manager'); ?>"><?php esc_attr_e('Upload', 'wp-food-manager'); ?></button>
				<?php } else { ?>
					<span class="food-manager-uploaded-file2">
						<span class="food-manager-uploaded-file">
							<?php if (!empty($field['value'])) :
								if (!wpfm_begnWith($field['value'], "http")) {
									$field['value']	= '';
								}
								if (is_array($field['value'])) {
									$field['value'] = get_the_post_thumbnail_url($thepostid, 'full');
								} ?>
								<input type="hidden" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" />
								<span class="food-manager-uploaded-file-preview">
									<?php if (in_array(pathinfo($field['value'], PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
										<img src="<?php echo esc_attr($field['value']); ?>">
										<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
										<?php else :
										if (!empty($field['value'])) { ?>
											<span class="wpfm-icon">
												<strong style="display: block; padding-top: 5px;"><?php echo esc_attr(wp_basename($field['value'])); ?></strong>
												<a target="_blank" href="<?php echo esc_attr($field['value']); ?>"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>
											</span>
											<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a>
									<?php }
									endif; ?>
								</span>
							<?php endif; ?>
						</span>
						<button class="button button-small wp_food_manager_upload_file_button" style="display: block;" data-uploader_button_text="<?php esc_attr_e('Use file', 'wp-food-manager'); ?>"><?php esc_attr_e('Upload', 'wp-food-manager'); ?></button>
					</span>
				<?php } ?>
			</span>
		</p>
	<?php
	}

	/**
	 * input_url function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_url($key, $field) {
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>:
				<?php if (!empty($field['description'])) : ?>
					<span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
				<?php endif; ?>
			</label>
			<span class="wpfm-input-field">
				<input type="url" class="wpfm-small-field" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" />
			</span>
		</p>
	<?php
	}

	/**
	 * input_text function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_text($key, $field) {
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?><span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
			<span class="wpfm-input-field">
				<input type="text" class="wpfm-small-field" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" />
			</span>
		</p>
	<?php
	}

	/**
	 * input_wp_editor function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 * @since 1.0.0
	 */
	public static function input_wp_editor($key, $field) {
		global $thepostid;
		if (!isset($field['value']) || empty($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (is_array($field['value'])) {
			$field['value'] = '';
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		if (wpfm_begnWith($field['value'], "http")) {
			$field['value'] = '';
		} ?>
		<div class="wpfm_editor" data-field-name="<?= $name; ?>">
			<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
				<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>:
					<?php if (!empty($field['description'])) : ?>
						<span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
					<?php endif; ?>
				</label>
			</p>
			<span class="wpfm-input-field">
				<?php wp_editor($field['value'], $name, array('media_buttons' => false)); ?>
			</span>
		</div>
	<?php
	}

	/**
	 * input_date function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_date($key, $field) {
		global $thepostid;
		$datepicker_date_format = !empty(get_option('date_format')) ? get_option('date_format') : 'F j, Y';
		$php_date_format        = WPFM_Date_Time::get_view_date_format_from_datepicker_date_format($datepicker_date_format);
		if (!isset($field['value'])) {
			$date = get_post_meta($thepostid, $key, true);
			if (is_array($date)) {
				$date = $date['0'];
			}
			if (!empty($date)) {
				$date = date($php_date_format, strtotime($date));
				$field['value']         = $date;
			}
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>
		<p class="wpfm-admin-postbox-form-field">
			<label for="<?php echo esc_attr($key); ?>"> <?php echo esc_html($field['label']); ?>:
				<?php if (!empty($field['description'])) : ?>
					<span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
				<?php endif; ?>
			</label>
			<input type="hidden" name="date_format" id="date_format" value="<?php echo esc_attr($php_date_format); ?>" />
			<input type="text" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo (isset($field['value']) && !empty($field['value']) ?  esc_attr($field['value']) : '') ?>" data-picker="datepicker" />
		</p>
	<?php
	}

	/**
	 * input_textarea function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_textarea($key, $field) {
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		$fieldLabel = '';
		if ($field['type'] == 'wp-editor') {
			$fieldLabel =  'wp-editor-field';
		}

		if (wpfm_begnWith($field['value'], "http") || is_array($field['value'])) {
			$field['value'] = '';
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?> <?php echo $fieldLabel; ?>" data-field-name="<?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?>: <span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
			<span class="wpfm-input-field">
				<textarea name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" rows="4" cols="63" placeholder="<?php echo esc_attr($field['placeholder']); ?>"><?php echo esc_html($field['value']); ?></textarea>
			</span>
		</p>
	<?php
	}

	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_select($key, $field) {
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?><span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
			<span class="wpfm-input-field">
				<select name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" class="input-select wpfm-small-field <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>">
					<?php foreach ($field['options'] as $key => $value) : ?>
						<option value="<?php echo esc_attr($key); ?>" <?php if (isset($field['value'])) selected($field['value'], $key); ?>><?php echo esc_html($value); ?></option>
					<?php endforeach; ?>
				</select>
			</span>
		</p>
	<?php
	}

	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_multiselect($key, $field) {
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?><span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
			<select multiple="multiple" name="<?php echo esc_attr($name); ?>[]" id="<?php echo esc_attr($key); ?>" class="input-select <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>">
				<?php foreach ($field['options'] as $key => $value) : ?>
					<option value="<?php echo esc_attr($key); ?>" <?php if (!empty($field['value']) && is_array($field['value'])) selected(in_array($key, $field['value']), true); ?>><?php echo esc_html($value); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	<?php
	}

	/**
	 * input_checkbox function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_checkbox($key, $field) {
		global $thepostid;
		$field_val = get_post_meta($thepostid, $key, true);
		if (empty($field['value']) || empty($field_val)) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		$exp_arr = explode("_", $key); ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : </label>
			<?php if ($key == '_enable_food_ingre' || $key == '_enable_food_nutri') { ?>
				<span class="wpfm-input-field">
					<label class="wpfm-field-switch" for="<?php echo esc_attr($key); ?>">
						<input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($field['value'], 1); ?>>
						<span class="wpfm-field-switch-slider round"></span>
					</label>
				</span>
			<?php } else { ?>
				<span class="wpfm-input-field">
					<?php foreach ($field['options'] as $option_key => $value) : ?>
						<input type="checkbox" id="<?php echo esc_attr($option_key); ?>" class="checkbox <?php echo esc_attr($option_key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>[]" value="<?php echo esc_attr($option_key); ?>" <?php if (!empty($field['value']) && is_array($field['value'])) checked(in_array($option_key, $field['value'], true)); ?> />
						<label for="<?php echo esc_attr($option_key); ?>"><?php echo esc_html($value); ?></label>
					<?php endforeach; ?>
				</span>
				<?php if (!empty($field['description'])) : ?><span class="description"><?php echo $field['description']; ?></span>
			<?php endif;
			} ?>
		</p>
	<?php
	}

	/**
	 * input_number function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_number($key, $field) {
		global $thepostid, $cur_symbol;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		if ($name == '_food_price' || $name == '_food_sale_price') {
			$cur_symbol = "(" . get_food_manager_currency_symbol() . ")";
		}
		if ($name == '_food_menu_order') {
			$field['value'] = (empty($field['value']) ? '0' : $field['value']);
		} else {
			$field['value'] = $field['value'];
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> <?php echo esc_html($cur_symbol); ?> : <?php if (!empty($field['description'])) : ?><span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
			<span class="wpfm-input-field">
				<input type="number" class="wpfm-small-field" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" maxlength="75" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" step="any" />
			</span>
		</p>
	<?php
	}

	/**
	 * input_radio function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_radio($key, $field) {
		global $thepostid;
		$field_val = get_post_meta($thepostid, $key, true);
		if (empty($field['value']) || !empty($field_val)) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>
		<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
			<label><?php echo esc_html($field['label']); ?> :</label>
			<span class="wpfm-input-field">
				<?php foreach ($field['options'] as $option_key => $value) : ?>
					<input type="radio" id="<?php echo esc_attr($option_key); ?>" class="radio <?php echo esc_attr($option_key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" value="<?php echo esc_attr($option_key); ?>" <?php checked($field['value'], $option_key); ?> />
					<label for="<?php echo esc_attr($option_key); ?>"><?php echo esc_html($value); ?></label>
				<?php endforeach; ?>
				<?php if (!empty($field['description'])) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
			</span>
		</p>
	<?php
	}

	/**
	 * input_options function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_options($key, $field) {
		global $thepostid;
		if (empty($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		$wpfm_key_num = explode("_", $key)['3']; ?>
		<div class="wpfm-admin-options-table <?= $name; ?>">
			<p class="wpfm-admin-postbox-form-field"><label><?php echo esc_html($field['label']); ?></label></p>
			<table class="widefat">
				<thead>
					<th> </th>
					<th>#</th>
					<th>Option name</th>
					<th>Default</th>
					<th>Price</th>
					<th>Type of price</th>
					<th></th>
				</thead>
				<tbody>
					<?php
					if (isset($field['value']) && !empty($field['value']) && is_array($field['value'])) {
						$count = 1;
						foreach ($field['value'] as $op_key => $op_value) { ?>
							<tr class="option-tr-<?php echo esc_attr($count); ?>">
								<td><span class="wpfm-option-sort">☰</span></td>
								<td><?php echo esc_html($count); ?></td>
								<td><input type="text" name="<?php echo esc_attr($wpfm_key_num); ?>_option_value_name_<?php echo esc_attr($count); ?>" value="<?php if (isset($op_value['option_value_name'])) echo $op_value['option_value_name']; ?>" class="opt_name" pattern=".*\S+.*" required></td>
								<td><input type="checkbox" name="<?php echo esc_attr($wpfm_key_num); ?>_option_value_default_<?php echo esc_attr($count); ?>" <?php if (isset($op_value['option_value_default']) && $op_value['option_value_default'] == 'on') echo 'checked="checked"'; ?> class="opt_default"></td>
								<td><input type="number" name="<?php echo esc_attr($wpfm_key_num); ?>_option_value_price_<?php echo esc_attr($count); ?>" value="<?php if (isset($op_value['option_value_price'])) echo $op_value['option_value_price']; ?>" class="opt_price" step="any" required></td>
								<td>
									<select name="<?php echo esc_attr($wpfm_key_num); ?>_option_value_price_type_<?php echo esc_attr($count); ?>" class="opt_select">
										<option value="quantity_based" <?php if (isset($op_value['option_value_price_type']) && $op_value['option_value_price_type'] == 'quantity_based') echo 'selected="selected"' ?>>Quantity Based</option>
										<option value="fixed_amount" <?php if (isset($op_value['option_value_price_type']) && $op_value['option_value_price_type'] == 'fixed_amount') echo 'selected="selected"' ?>>Fixed Amount</option>
									</select>
								</td>
								<td><a href="javascript: void(0);" data-id="<?php echo esc_attr($count); ?>" class="option-delete-btn">Remove</a></td>
								<input type="hidden" class="option-value-class" name="option_value_count[]" value="<?php echo esc_attr($count); ?>">
							</tr>
					<?php $count++;
						}
					} else {
					} ?>
				</tbody>
				<tfoot>
					<td colspan="7"><a class="button wpfm-add-row" data-row="<tr class=&apos;option-tr-%%repeated-option-index3%%&apos;>
					<td><span class=&apos;wpfm-option-sort&apos;>☰</span></td>
					<td>%%repeated-option-index3%%</td>
					<td><input type=&apos;text&apos; name=&apos;%%repeated-option-index2%%_option_value_name_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_name&apos; pattern=&apos;.*\S+.*&apos; required></td>
					<td><input type=&apos;checkbox&apos; name=&apos;%%repeated-option-index2%%_option_value_default_%%repeated-option-index3%%&apos; class=&apos;opt_default&apos;></td>
					<td><input type=&apos;number&apos; name=&apos;%%repeated-option-index2%%_option_value_price_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_price&apos; step=&apos;any&apos; required></td>
					<td>
						<select name=&apos;%%repeated-option-index2%%_option_value_price_type_%%repeated-option-index3%%&apos; class=&apos;opt_select&apos;>
						<option value=&apos;quantity_based&apos;>Quantity Based</option>
						<option value=&apos;fixed_amount&apos;>Fixed Amount</option>
						</select>
					</td>
					<td><a href=&apos;javascript: void(0);&apos; data-id=&apos;%%repeated-option-index3%%&apos; class=&apos;option-delete-btn&apos;>Remove</a></td>
					<input type=&apos;hidden&apos; class=&apos;option-value-class&apos; name=&apos;option_value_count[]&apos; value=&apos;%%repeated-option-index3%%&apos;>
				</tr>">Add Row</a>
					</td>
				</tfoot>
			</table>
		</div>
<?php
	}
}

WPFM_Writepanels::instance();
