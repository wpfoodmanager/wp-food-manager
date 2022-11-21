<?php
/*
* This file use to cretae fields of wp food manager at admin side.
*/
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class WPFM_Writepanels
{

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  2.5
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  2.5
	 * @static
	 * @return self Main instance.
	 */
	public static function instance()
	{
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
	public function __construct()
	{

		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_post'), 1, 2);
		add_action('admin_init', array($this, 'approve_food'));

		add_action('load-edit.php', array($this, 'do_bulk_actions'));

		add_action('admin_footer-edit.php', array($this, 'add_bulk_actions'));

		add_action('food_manager_save_food_manager', array($this, 'food_manager_save_food_manager_data'), 20, 2);

		// save food attributes
		add_action('wp_ajax_wpfm_update_food_attributes', array($this, 'wpfm_update_food_attributes'));

		//food menu 
		add_action('wp_ajax_wpfm_get_food_listings_by_category_id', array($this, 'wpfm_get_food_listings_by_category_id'));

		add_action('food_manager_save_food_manager_menu', array($this, 'food_manager_save_food_manager_menu_data'), 20, 2);

		//add food menu column
		add_filter('manage_food_manager_menu_posts_columns', array($this, 'set_shortcode_copy_columns'));
		add_action('manage_food_manager_menu_posts_custom_column', array($this, 'shortcode_copy_content_column'), 10, 2);

		//add food image column
		add_filter('manage_edit-food_manager_columns', array($this, 'columns'));
		add_filter('manage_food_manager_posts_columns', array($this, 'set_custom_food_columns'));
		add_filter('manage_edit-food_manager_sortable_columns', array($this, 'set_custom_food_sortable_columns'));
		add_action('manage_food_manager_posts_custom_column', array($this, 'custom_food_content_column'), 10, 2);
		add_filter('post_row_actions', array($this, 'row_actions'));

		//add food price column
		/*add_filter('manage_food_manager_posts_columns', array($this, 'set_price_copy_columns'));
		add_action('manage_food_manager_posts_custom_column', array($this, 'price_copy_content_column'), 10, 2);*/
	}


	/**
	 * add_meta_boxes function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes()
	{
		global $wp_post_types;

		add_meta_box('food_manager_data', sprintf(__('%s Data', 'wp-food-manager'), $wp_post_types['food_manager']->labels->singular_name), array($this, 'food_manager_data'), 'food_manager', 'normal', 'high');

		add_meta_box('food_manager_menu_data', __('Menu Icon', 'wp-food-manager'), array($this, 'food_manager_menu_data'), 'food_manager_menu', 'normal', 'high');

		add_meta_box('food_manager_menu_data_icons', __('All Food', 'wp-food-manager'), array($this, 'food_manager_menu_data_icons'), 'food_manager_menu', 'normal', 'high');
	}

	/**
	 * food_manager_data function.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	public function food_manager_data($post)
	{
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
	public function food_manager_menu_data_icons($post)
	{
		global $post, $thepostid;
		$thepostid = $post->ID;
		wp_enqueue_script('wpfm-admin');

		wp_nonce_field('save_meta_data', 'food_manager_nonce');

		?>
		<div class="wpfm-admin-food-menu-container wpfm-flex-col wpfm-admin-postbox-meta-data">
			<div class="wpfm-admin-food-menu-container wpfm-flex-col wpfm-admin-postbox-meta-data">
				<div class="wpfm-admin-menu-selection wpfm-admin-postbox-form-field">
					<!-- <label for="_add_food"><?php _e('Select food category'); ?></label> -->
					<?php food_manager_dropdown_selection(array(
						'multiple' => false, 'show_option_all' => __('All category', 'wp-food-manager'),
						'id' => 'wpfm-admin-food-selection',
						'taxonomy' => 'food_manager_category',
						'hide_empty' => false,
						'pad_counts' => true,
						'show_count' => true,
						'hierarchical' => false,
					)); ?>

					<!-- Do not remove -->
					<!--<div class="wpfm-admin-postbox-drop-btn">
						<input type="button" id="wpfm-admin-add-food" class="button button-small" value="<?php //_e('Add food', 'wp-food-manager'); ?>" />
					</div> -->
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
			<!-- Do not remove -->
			<!-- <div class="wpfm-admin-menu-selection wpfm-admin-postbox-form-field">
				<div class="wpfm-admin-postbox-drop-btn">
					<input type="button" id="wpfm-admin-add-food" class="button button-small" value="<?php _e('Add', 'wp-food-manager'); ?>" />
				</div>
				<label for="_add_food" class="add-food-small"><i><?php _e('Add your food item'); ?></i></label>
			</div> -->
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
	public function food_manager_menu_data($post)
	{
		global $post, $thepostid;
		$thepostid = $post->ID;
		wp_enqueue_script('wpfm-admin');

		wp_nonce_field('save_meta_data', 'food_manager_nonce');

		$icon_arrs = wpfm_get_font_icons();
		
		?>

		<?php

		echo '<div class="wpfm-parent-icons"><input type="text" id="wpfm_icon_search" name="wpfm_icon_search" placeholder="Icon Search"><span class="wpfm-searh-clear"><i class="fa fa-times"></i></span></div>';
		echo '<div class="no-radio-icons"><strong>No icons found!</strong></div>';
		echo "<div class='wpfm-font-wesome-class'>";
			foreach($icon_arrs as $key => $icon_arr){
				$radio_checked = (get_post_meta($thepostid, 'wpfm_radio_icons', true) === $key) ? "checked" : "";
				$key_name = str_replace("fa-", "", $key);
				echo '<div class="sub-font-icon"><input type="radio" id="'.$key.'" name="radio_icons" value="'.$key.'" '.$radio_checked.'><label for="'.$key.'"><span class="wpfm-icon-key-name">'.$key_name.'</span><i class="fa '.$key.'"></i></label></div>';
			}
		echo "</div>";
	}

	/**
	 * Return array of tabs to show.
	 *
	 * @return array
	 */
	private function get_food_data_tabs()
	{
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
					'label'    => __('Options / Add ons / Toppings', 'wp-food-manager'),
					'target'   => 'extra_options_food_data_content',
					'class'    => array(),
					'priority' => 2,
				),
				'ingredient'        => array(
					'label'    => __('Ingredient', 'wp-food-manager'),
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


	public function output_tabs()
	{
		global $post, $thepostid;
		$thepostid = $post->ID;

		include 'templates/food-data-general.php';
		include 'templates/food-data-extra-options.php';
		include 'templates/food-data-ingredient.php';
		include 'templates/food-data-nutrition.php';
		include 'templates/food-data-advanced.php';
	}

	/**
	 * food_listing_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function food_manager_data_fields()
	{
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


		/*global $post;
		$current_user = wp_get_current_user();
		
		$GLOBALS['food_manager']->forms->get_form_fields('submit-food', array());
		$form_submit_food_instance = call_user_func(array('WPFM_Form_Submit_Food', 'instance'));
		$fields                     = $form_submit_food_instance->merge_with_custom_fields('backend');

		foreach ($fields as $group_key => $group_fields) {
			foreach ($group_fields as $field_key => $field_value) {

				if ($field_key === 'registration') {
					$field_value['value'] = $registration;
				}

				if (strpos($field_key, '_') !== 0) {
					$fields['_' . $field_key] = $field_value;
				} else {
					$fields[$field_key] = $field_value;
				}
			}
			unset($fields[$group_key]);
		}

		$fields = apply_filters('food_manager_food_listing_data_fields', $fields);

		if (isset($fields['food_title'])) {
			unset($fields['food_title']);
		}

		if (isset($fields['food_description'])) {
			unset($fields['food_description']);
		}

		if ($current_user->has_cap('edit_others_food_manager')) {
			$fields['food_author'] = array(
				'label'    => __('Posted by', 'wp-food-manager'),
				'type'     => 'author',
				'priority' => 41,
			);
		}

		uasort($fields, array($this, 'sort_by_priority'));
		return $fields;*/
	}



	/**
	 * Sort array by priority value
	 */
	protected function sort_by_priority($a, $b)
	{
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
	public static function input_file($key, $field)
	{
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
		}
	?>

		<p class="wpfm-admin-postbox-form-field <?=$name;?>" data-field-name="<?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>:
				<?php if (!empty($field['description'])) : ?>
					<span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
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
										if(!empty($value)){ ?>
				                            <span class="wpfm-icon">
				                                <strong style="display: block; padding-top: 5px;"><?php echo esc_attr(wp_basename($value)); ?></strong>
				                                <a target="_blank" href="<?php echo esc_attr($value);?>"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>
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
							<?php if(!empty($field['value'])) : 
								if(is_array($field['value'])){ 
									$field['value'] = get_the_post_thumbnail_url($thepostid, 'full'); 
								} 
								?>
								<input type="hidden" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" /> 
								<span class="food-manager-uploaded-file-preview">
									<?php if (in_array(pathinfo($field['value'], PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
										<img src="<?php echo esc_attr($field['value']); ?>">
										<a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a> 
									<?php else :
										if(!empty($field['value'])){ ?>
				                            <span class="wpfm-icon">
				                                <strong style="display: block; padding-top: 5px;"><?php echo esc_attr(wp_basename($field['value'])); ?></strong>
				                                <a target="_blank" href="<?php echo esc_attr($field['value']);?>"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>
				                            </span>
				                            <a class="food-manager-remove-uploaded-file" href="javascript:void(0);">[remove]</a> 
				                        <?php }
			                        endif; ?>
								</span> 
							<?php endif; ?> 
						</span>
						<button class="button button-small wp_food_manager_upload_file_button" style="display: block;" data-uploader_button_text="<?php esc_attr_e('Use file', 'wp-food-manager'); ?>"><?php esc_attr_e('Upload', 'wp-food-manager'); ?></button> 
					</span>
				<?php
				}
				if (!empty($field['multiple'])) { 
				?> 
					<!-- <button class="button button-small wp_food_manager_add_another_file_button" data-field_name="<?php echo esc_attr($key); ?>" data-field_placeholder="<?php echo esc_attr($field['placeholder']); ?>" data-uploader_button_text="<?php esc_attr_e('Use file', 'wp-food-manager'); ?>" data-uploader_button="<?php esc_attr_e('Upload', 'wp-food-manager'); ?>"><?php esc_attr_e('Add file', 'wp-food-manager'); ?></button> --> 
				<?php 
				} 
				?>
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
	public static function input_url($key, $field)
	{
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>:
				<?php if (!empty($field['description'])) : ?>
					<span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
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
	public static function input_text($key, $field)
	{
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?><span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
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
	 * @since 2.8
	 */
	public static function input_wp_editor($key, $field)
	{
		global $thepostid;
		if (!isset($field['value']) || empty($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>
		<div class="wpfm_editor" data-field-name="<?=$name;?>">
			<p class="wpfm-admin-postbox-form-field <?=$name;?>">
				<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>:
					<?php if (!empty($field['description'])) : ?>
						<span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
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
	public static function input_date($key, $field)
	{
		global $thepostid;
		$datepicker_date_format = !empty(get_option('date_format')) ? get_option('date_format') : 'F j, Y'; //WP_Food_Manager_Date_Time::get_datepicker_format();
		$php_date_format        = WP_Food_Manager_Date_Time::get_view_date_format_from_datepicker_date_format($datepicker_date_format);
		if (!isset($field['value'])) {
			$date = get_post_meta($thepostid, $key, true);
			if(is_array($date)){
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
		}
	?>
		<p class="wpfm-admin-postbox-form-field">
			<label for="<?php echo esc_attr($key); ?>"> <?php echo esc_html($field['label']); ?>:
				<?php
				if (!empty($field['description'])) :
				?>
					<span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
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
	public static function input_textarea($key, $field)
	{
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
		if($field['type'] == 'wp-editor'){
			$fieldLabel =  'wp-editor-field';
		}
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?> <?php echo $fieldLabel; ?>" data-field-name="<?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?>: <span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
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
	public static function input_select($key, $field)
	{
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>

		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?><span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
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
	public static function input_multiselect($key, $field)
	{
		global $thepostid;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : <?php if (!empty($field['description'])) : ?><span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
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
	public static function input_checkbox($key, $field)
	{
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

		$exp_arr = explode("_", $key);
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : </label>
			<?php if($key == '_enable_food_ingre' || $key == '_enable_food_nutri') { //$key == '_option_enable_desc_'.end($exp_arr) ?>
				<span class="wpfm-input-field">
					<label class="wpfm-field-switch" for="<?php echo esc_attr($key); ?>">
						<input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($name); ?>" value="1"
						<?php checked($field['value'], 1); ?>>
						<span class="wpfm-field-switch-slider round"></span>
					</label>
				</span>
			<?php } else { ?>
				<span class="wpfm-input-field">
				<?php foreach ($field['options'] as $option_key => $value) :?>
					<input type="checkbox" id="<?php echo esc_attr($option_key); ?>" class="checkbox <?php echo esc_attr($option_key); ?>" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>[]" value="<?php echo esc_attr($option_key); ?>" <?php if (!empty($field['value']) && is_array($field['value'])) checked(in_array($option_key, $field['value'], true)); ?> />
					<label for="<?php echo esc_attr($option_key); ?>"><?php echo esc_html($value); ?></label>
				<?php endforeach; ?>
				</span>
				<!-- <input type="checkbox" class="checkbox " name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" value="1" <?php checked($field['value'], 1); ?> /> -->
				<?php if (!empty($field['description'])) : ?><span class="description"><?php echo $field['description']; ?></span>
			<?php endif; } ?>
		</p>
	<?php
	}


	/**
	 * Edit bulk actions
	 */

	public function add_bulk_actions()
	{

		global $post_type, $wp_post_types;

		if ($post_type == 'food_manager') { ?>
			<script type="text/javascript">
				jQuery(document).ready(function() {

					jQuery('<option>').val('approve_food').text('<?php printf(__('Approve %s', 'wp-food-manager'), esc_attr($wp_post_types['food_manager']->labels->name)); ?>').appendTo("select[name='action']");

					jQuery('<option>').val('approve_food').text('<?php printf(__('Approve %s', 'wp-food-manager'), esc_attr($wp_post_types['food_manager']->labels->name)); ?>').appendTo("select[name='action2']");

				});
			</script>

		<?php
		}
	}

	/**
	 * Do custom bulk actions
	 */

	public function do_bulk_actions()
	{

		$wp_list_table = _get_list_table('WP_Posts_List_Table');

		$action = $wp_list_table->current_action();

		switch ($action) {

			case 'approve_food':
				check_admin_referer('bulk-posts');

				$post_ids = array_map('absint', array_filter((array) $_GET['post']));

				$published_foods = array();

				if (!empty($post_ids)) {

					foreach ($post_ids as $post_id) {

						$food_data = array(

							'ID'          => $post_id,

							'post_status' => 'publish',
						);

						if (in_array(get_post_status($post_id), array('pending', 'pending_payment')) && current_user_can('publish_post', $post_id) && wp_update_post($food_data)) {

							$published_foods[] = $post_id;
						}
					}
				}

				wp_redirect(add_query_arg('published_foods', $published_foods, remove_query_arg(array('published_foods', 'expired_events'), admin_url('edit.php?post_type=food_manager'))));

				exit;

				break;
		}

		return;
	}

	/**
	 * Approve a single food
	 */

	public function approve_food()
	{

		if (!empty($_GET['approve_food']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_food') && current_user_can('publish_post', $_GET['approve_food'])) {

			$post_id = absint($_GET['approve_food']);

			$food_data = array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			);

			wp_update_post($food_data);

			wp_redirect(remove_query_arg('approve_food', add_query_arg('published_foods', $post_id, admin_url('edit.php?post_type=food_manager'))));

			exit;
		}
	}


	/**
	 * input_number function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_number($key, $field)
	{
		global $thepostid, $cur_symbol;
		if (!isset($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}


		if($name == '_food_price' || $name == '_food_sale_price'){
			$cur_symbol = "(".get_food_manager_currency_symbol().")";
		}

		if($name == '_food_menu_order'){
			$field['value'] = (empty($field['value']) ? '0' : $field['value']);
		} else {
			$field['value'] = $field['value'];
		}
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> <?php echo esc_html($cur_symbol); ?> : <?php if (!empty($field['description'])) : ?><span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
			<span class="wpfm-input-field">
				<input type="number" class="wpfm-small-field" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" maxlength="75" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" step="any"/>
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
	public static function input_radio($key, $field)
	{
		global $thepostid;
		
		$field_val = get_post_meta($thepostid, $key, true);

		if (empty($field['value']) || !empty($field_val)) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
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
	public static function input_options($key, $field)
	{
		global $thepostid;
		if (empty($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}

		$wpfm_key_num = explode("_", $key)['3'];

	?>
	<div class="wpfm-admin-options-table <?=$name;?>" >
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
				if(isset($field['value']) && !empty($field['value']) && is_array($field['value'])){

					//$repeated_rows = array_unique(get_post_meta($thepostid ,'wpfm_repeated_options', true));
						$count = 1;
						foreach ($field['value'] as $op_key => $op_value) {
							//for($i=1; $i <= count($repeated_rows); $i++){
								?>
								<tr class="option-tr-<?php echo esc_attr($count);?>">
									<td><span class="wpfm-option-sort">☰</span></td>
									<td><?php echo esc_html($count);?></td>
									<td><input type="text" name="<?php echo esc_attr($wpfm_key_num);?>_option_value_name_<?php echo esc_attr($count); ?>" value="<?php if(isset($op_value['option_value_name']) ) echo $op_value['option_value_name']; ?>" class="opt_name" pattern=".*\S+.*" required></td>
									<!-- <td><input type="checkbox" name="%%repeated-option-index2%%_option_value_default_<?php //echo esc_attr($count);?>" value="1"<?php //if(isset($op_value['option_value_default']) && $op_value['option_value_price_type'] == 'option_value_default') echo 'checked="checked"' ?> class="opt_default"></td> -->
									<td><input type="checkbox" name="<?php echo esc_attr($wpfm_key_num);?>_option_value_default_<?php echo esc_attr($count);?>" <?php if(isset($op_value['option_value_default']) && $op_value['option_value_default'] == 'on') echo 'checked="checked"'; ?> class="opt_default" ></td>

									<td><input type="number" name="<?php echo esc_attr($wpfm_key_num);?>_option_value_price_<?php echo esc_attr($count);?>" value="<?php if(isset($op_value['option_value_price']) ) echo $op_value['option_value_price']; ?>" class="opt_price" step="any" required></td>

									<td>
										<select name="<?php echo esc_attr($wpfm_key_num);?>_option_value_price_type_<?php echo esc_attr($count);?>" class="opt_select">
										<option value="quantity_based" <?php if(isset($op_value['option_value_price_type']) && $op_value['option_value_price_type'] == 'quantity_based') echo 'selected="selected"' ?>>Quantity Based</option>
										<option value="fixed_amount" <?php if(isset($op_value['option_value_price_type']) && $op_value['option_value_price_type'] == 'fixed_amount') echo 'selected="selected"' ?>>Fixed Amount</option>
										</select>
									</td>
									<td><a href="javascript: void(0);" data-id="<?php echo esc_attr($count);?>" class="option-delete-btn">Remove</a></td>
									<input type="hidden" class="option-value-class" name="option_value_count[]" value="<?php echo esc_attr($count);?>">
								</tr>
							<?php 
							$count++;
							//}
						}
				}else{
					
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

	/**
	 * save_post function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function save_post($post_id, $post)
	{
		if (empty($post_id) || empty($post) || empty($_POST)) return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (is_int(wp_is_post_revision($post))) return;
		if (is_int(wp_is_post_autosave($post))) return;
		if (empty($_POST['food_manager_nonce']) || !wp_verify_nonce($_POST['food_manager_nonce'], 'save_meta_data')) return;
		if (!current_user_can('edit_post', $post_id)) return;

		if ($post->post_type == 'food_manager')
			do_action('food_manager_save_food_manager', $post_id, $post);

		if ($post->post_type == 'food_manager_menu')
			do_action('food_manager_save_food_manager_menu', $post_id, $post);
	}

	/**
	 * save_food_manager_data function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function food_manager_save_food_manager_data($post_id, $post)
	{
		global $wpdb;

		// Save fields

		// Advanced tab fields
		if ( ! empty( $_POST['_food_menu_order'] ) ) {
			$fd_menu_order = sanitize_text_field($_POST['_food_menu_order']);
			if( !add_post_meta($post_id,'_food_menu_order', $fd_menu_order, true) ){
				update_post_meta($post_id,'_food_menu_order', $fd_menu_order);
			}
		}
		
		if(isset($_POST['_enable_food_ingre'])){
			$fd_food_ingre = sanitize_text_field($_POST['_enable_food_ingre']);
			if( !add_post_meta($post_id,'_enable_food_ingre', $fd_food_ingre, true) ){
				update_post_meta($post_id,'_enable_food_ingre', $fd_food_ingre);
			}
		} else {
			update_post_meta($post_id,'_enable_food_ingre', '');
		}
		
		if(isset($_POST['_enable_food_nutri'])){
			$fd_food_nutri = sanitize_text_field($_POST['_enable_food_nutri']);
			if( !add_post_meta($post_id,'_enable_food_nutri', $fd_food_nutri, true) ){
				update_post_meta($post_id,'_enable_food_nutri', $fd_food_nutri);
			}
		} else {
			update_post_meta($post_id,'_enable_food_nutri', '');
		}

		// Ingredients.
		delete_post_meta( $post_id, '_ingredient' );

		$multiArrayIng = array();
		if ( ! empty( $_POST['_ingredient'] ) ) {
			foreach ( $_POST['_ingredient'] as $id => $ingredient ) {
				$term_name = get_term( $id )->name;
				$unit_name = "Unit";
				if($ingredient['unit_id'] == '' && empty($ingredient['unit_id'])){
					$unit_name = "Unit";
				} else {
					$unit_name = get_term( $ingredient['unit_id'] )->name;
				}

				$item = [
					'id'      => $id,
					'unit_id' => ! empty( $ingredient['unit_id'] ) ? $ingredient['unit_id'] : null,
					'value'   => ! empty( $ingredient['value'] ) ? $ingredient['value'] : null,
					'term_name' => $term_name,
					'unit_name' => $unit_name
				];
				$multiArrayIng[$id] = $item;
				//add_post_meta( $post_id, '_ingredient', $item );
			}
			if( !add_post_meta($post_id,'_ingredient', $multiArrayIng, true) ){
				update_post_meta($post_id,'_ingredient', $multiArrayIng);
			}
		}

		// Nutritions.
		delete_post_meta( $post_id, '_nutrition' );

		$multiArrayNutri = array();
		if ( ! empty( $_POST['_nutrition'] ) ) {
			foreach ( $_POST['_nutrition'] as $id => $nutrition ) {
				$term_name = get_term( $id )->name;
				$unit_name = "Unit";
				if($nutrition['unit_id'] == '' && empty($nutrition['unit_id'])){
					$unit_name = "Unit";
				} else {
					$unit_name = get_term( $nutrition['unit_id'] )->name;
				}

				$item = [
					'id'      => $id,
					'unit_id' => ! empty( $nutrition['unit_id'] ) ? $nutrition['unit_id'] : null,
					'value'   => ! empty( $nutrition['value'] ) ? $nutrition['value'] : null,
					'term_name' => $term_name,
					'unit_name' => $unit_name
				];
				$multiArrayNutri[$id] = $item;
				//add_post_meta( $post_id, '_ingredient', $item );
			}
			if( !add_post_meta($post_id,'_nutrition', $multiArrayNutri, true) ){
				update_post_meta($post_id,'_nutrition', $multiArrayNutri);
			}
		}

		// Food type
		/*$fd_type = sanitize_text_field($_POST['_food_veg_nonveg']);
		if( !add_post_meta($post_id,'_food_veg_nonveg', $fd_type, true) ){
			update_post_meta($post_id,'_food_veg_nonveg', $fd_type);
		}*/
		
		// Food price
		$fd_price = sanitize_text_field($_POST['_food_price']);
		if( !add_post_meta($post_id,'_food_price', $fd_price, true) ){
			update_post_meta($post_id,'_food_price', $fd_price);
		}

		// Food sale price
		$fd_sale_price = sanitize_text_field($_POST['_food_sale_price']);
		if( !add_post_meta($post_id,'_food_sale_price', $fd_sale_price, true) ){
			update_post_meta($post_id,'_food_sale_price', $fd_sale_price);
		}

		// Food stock status
		$fd_stock_status = sanitize_text_field($_POST['_food_stock_status']);
		if( !add_post_meta($post_id,'_food_stock_status', $fd_stock_status, true) ){
			update_post_meta($post_id,'_food_stock_status', $fd_stock_status);
		}

		// Repeated options
		$repeated_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
		if( !add_post_meta($post_id,'wpfm_repeated_options', $repeated_options, true) ){
			update_post_meta($post_id,'wpfm_repeated_options', $repeated_options);
		}

		// Options value count
		$array_cnt = isset($_POST['option_value_count']) ? $_POST['option_value_count'] : '';
		if(isset($array_cnt) && !empty($array_cnt)){
			$food_data_option_value_count = array();
			$index = 0;
			foreach ($array_cnt as $number) {
			    if ($number == 1) {
			        $index++;
			    }
			    $food_data_option_value_count[$index][] = $number;
			}
			if( !add_post_meta($post_id,'wpfm_option_value_count', $food_data_option_value_count, true) ){
				update_post_meta($post_id,'wpfm_option_value_count', $food_data_option_value_count);
			}
		}

		// Save Food Form fields values
		foreach ($this->food_manager_data_fields()['food'] as $key => $field) {
			
			//foreach ($field as $key2 => $fiel) {

			$type = !empty($field['type']) ? $field['type'] : '';
			// food banner
			if ('_food_banner' === "_".$key) {
				if (isset($_POST["_".$key]) && !empty($_POST["_".$key])) {
					$thumbnail_image = $_POST["_".$key];
					update_post_meta($post_id, "_".$key, $_POST["_".$key]);
				} else {
				$thumbnail_image = $_POST["_".$key];
					update_post_meta($post_id, "_".$key, $_POST["_".$key]);
				}

				$image = get_the_post_thumbnail_url($post_id);

				if (empty($image)) {
					if (isset($thumbnail_image) && !empty($thumbnail_image)) {
						$wp_upload_dir = wp_get_upload_dir();

						$baseurl = $wp_upload_dir['baseurl'] . '/';

						$wp_attached_file = str_replace($baseurl, '', $thumbnail_image);

						$args = array(
							'meta_key'       => '_wp_attached_file',
							'meta_value'     => $wp_attached_file,
							'post_type'      => 'attachment',
							'posts_per_page' => 1,
						);

						$attachments = get_posts($args);

						if (!empty($attachments)) {
							foreach ($attachments as $attachment) {
								set_post_thumbnail($post_id, $attachment->ID);
							}
						}
					}
				}
			}

			if(isset($_POST["_".$key]) && !empty($_POST["_".$key])){
				update_post_meta($post_id, "_".$key, $_POST["_".$key]);
			} else {
				update_post_meta($post_id, "_".$key, "");
			}
		
			switch ($type) {
				case 'textarea':
					if (isset($_POST[$key])) {
						update_post_meta($post_id, $key, wp_kses_post(stripslashes($_POST[$key])));
					}
					break;
				case 'checkbox':
					if (isset($_POST[$key])) {
						update_post_meta($post_id, $key, 1);
					} else {
						update_post_meta($post_id, $key, 0);
					}
					break;
				case 'date':
					if (isset($_POST[$key])) {
						$date = $_POST[$key];

						//Convert date and time value into DB formatted format and save eg. 1970-01-01
						$date_dbformatted = WP_Food_Manager_Date_Time::date_parse_from_format($php_date_format, $date);
						$date_dbformatted = !empty($date_dbformatted) ? $date_dbformatted : $date;
						update_post_meta($post_id, $key, $date_dbformatted);
					}
					break;
				default:
					if (!isset($_POST[$key])) {
						continue 2;
					} elseif (is_array($_POST[$key])) {
						update_post_meta($post_id, $key, array_filter(array_map('sanitize_text_field', $_POST[$key])));
					} else {
						update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
					}
					break;
			}
			//}
		}

		// Save Extra Options/Topping form fields values
		foreach ($this->food_manager_data_fields()['extra_options'] as $key => $field) {
			
			// author
			if ('_food_author' === $key) {
				$wpdb->update($wpdb->posts, array('post_author' => $_POST[$key] > 0 ? absint($_POST[$key]) : 0), array('ID' => $post_id));
			}
			// Everything else		
			else {
				$type = !empty($field['type']) ? $field['type'] : '';
				$extra_options = array();

				$food = $post;
				$form_submit_food_instance = call_user_func(array('WPFM_Form_Submit_Food', 'instance'));
		        //$custom_fields = $form_submit_food_instance->get_food_manager_fieldeditor_fields();

		        $custom_food_fields  = !empty($form_submit_food_instance->get_food_manager_fieldeditor_fields()) ? $form_submit_food_instance->get_food_manager_fieldeditor_fields() : array();

		        $custom_extra_options_fields  = !empty($form_submit_food_instance->get_food_manager_fieldeditor_extra_options_fields()) ? $form_submit_food_instance->get_food_manager_fieldeditor_extra_options_fields() : array();

		        $custom_fields = '';
		        if(!empty($custom_extra_options_fields)){
		            $custom_fields = array_merge($custom_food_fields, $custom_extra_options_fields);
		        } else {
		            $custom_fields = $custom_food_fields;
		        }

		        $default_fields = $form_submit_food_instance->get_default_food_fields();
		        
		        $additional_fields = [];
		        if (!empty($custom_fields) && isset($custom_fields) && !empty($custom_fields['extra_options'])) {
		            foreach ($custom_fields['extra_options'] as $field_name => $field_data) {
		                if (!array_key_exists($field_name, $default_fields['extra_options'])) {
		                    $meta_key = '_' . $field_name;
		                    $field_value = $food->$meta_key;
		                    if (isset($field_value)) {
		                        $additional_fields[$field_name] = $field_data;
		                    }
		                }
		            }

		            if (isset($additional_fields['attendee_information_type']))
		                unset($additional_fields['attendee_information_type']);

		            if (isset($additional_fields['attendee_information_fields']))
		                unset($additional_fields['attendee_information_fields']);

		            $additional_fields = apply_filters('food_manager_show_additional_details_fields', $additional_fields);
		        }				

				//find how many total reapeated extra option there then store it.
				if(isset($_POST['repeated_options']) && is_array($_POST['repeated_options'])){
					foreach ( $_POST['repeated_options'] as $option_count) {
						$counter = 0;
						if(isset($_POST['option_key_'.$option_count])){

							$option_key = $_POST['option_key_'.$option_count];
							$option_name = $_POST['option_name_'.$option_count];
							$option_type = $_POST['_option_type_'.$option_count];
							$option_required = $_POST['_option_required_'.$option_count];
							$option_enable_desc = isset($_POST['_option_enable_desc_'.$option_count]) ? $_POST['_option_enable_desc_'.$option_count] : '';
							$option_description = isset($_POST['_option_description_'.$option_count]) ? $_POST['_option_description_'.$option_count] : '';
							/*$option_minimum = $_POST['_option_minimum_'.$option_count];
							$option_maximum = $_POST['_option_maximum_'.$option_count];
							$option_price = $_POST['_option_price_'.$option_count];
							$option_price_type = $_POST['_option_price_type_'.$option_count];*/
							
							$option_values = array();

							if(isset($_POST['option_value_count'])){
								$find_option = array_search('%%repeated-option-index%%', $_POST['option_value_count']);
								if ($find_option !== false) {
									// Remove from array
    								unset($_POST['option_value_count'][$find_option]);
								}
								
								foreach ( $_POST['option_value_count'] as $option_value_count) {
									if(!empty($_POST[$option_count.'_option_value_name_'.$option_value_count]) || !empty($_POST[$option_count.'_option_value_default_'.$option_value_count]) || !empty($_POST[$option_count.'_option_value_price_'.$option_value_count])){

										// New Logic
										$option_values[$option_value_count] = array(
															'option_value_name' => isset($_POST[$option_count.'_option_value_name_'.$option_value_count]) ? $_POST[$option_count.'_option_value_name_'.$option_value_count] : '',

															'option_value_default' => isset($_POST[$option_count.'_option_value_default_'.$option_value_count]) ? $_POST[$option_count.'_option_value_default_'.$option_value_count] : '',

															'option_value_price' => isset($_POST[$option_count.'_option_value_price_'.$option_value_count]) ? $_POST[$option_count.'_option_value_price_'.$option_value_count] : '',

															'option_value_price_type' => isset($_POST[$option_count.'_option_value_price_type_'.$option_value_count]) ? $_POST[$option_count.'_option_value_price_type_'.$option_value_count] : ''
														);

									}
								}
								
							}
							
							if(!empty($custom_extra_options_fields)){
								$extra_options[$option_key] = array(
														'option_name' => $option_name,
													);
								foreach($custom_extra_options_fields as $custom_ext_key => $custom_extra_options_field){
									foreach($custom_extra_options_field as $custom_ext_single_key => $custom_extra_options_single_field){
										if($custom_ext_single_key !== 'option_name' && $custom_ext_single_key !== 'option_options'){
											$custom_ext_key_post = isset($_POST["_".$custom_ext_single_key."_".$option_count]) ? $_POST["_".$custom_ext_single_key."_".$option_count] : '';
									        $extra_options[$option_key][$custom_ext_single_key] = $custom_ext_key_post;

										    if(!empty($custom_ext_key_post)){
										        update_post_meta($post_id, "_".$custom_ext_single_key."_".$option_count, $custom_ext_key_post);
										    } else {
										    	update_post_meta($post_id, "_".$custom_ext_single_key."_".$option_count, "");
										    }
									    }
									    if($custom_ext_single_key == 'option_name'){
									    	$custom_ext_key_post = isset($_POST[$custom_ext_single_key."_".$option_count]) ? $_POST[$custom_ext_single_key."_".$option_count] : '';

									        $extra_options[$option_key][$custom_ext_single_key] = $custom_ext_key_post;

										    if(!empty($custom_ext_key_post)){
										        update_post_meta($post_id, $custom_ext_single_key."_".$option_count, $custom_ext_key_post);
										    }
									    }
									    if($custom_ext_single_key == 'option_options'){
									    	$extra_options[$option_key][$custom_ext_single_key] = $option_values;
									    }
								    }
								}
							} else {
								update_post_meta($post_id, 'option_name_'.$option_count , $option_name);
								update_post_meta($post_id, '_option_description_'.$option_count , $option_description);
								update_post_meta($post_id, '_option_type_'.$option_count , $option_type);
								update_post_meta($post_id, '_option_required_'.$option_count , $option_required);

								$extra_options[$option_key] = array(
													'option_name' => $option_name,
													'option_type' => $option_type,
													'option_required' => $option_required,
													'option_enable_desc' => $option_enable_desc,
													'option_description' => $option_description,
													'option_options' => $option_values,
												);
							}
							
							if(!empty($additional_fields)){
								foreach($additional_fields as $add_key => $additional_field){
									$key_post = isset($_POST["_".$add_key."_".$option_count]) ? $_POST["_".$add_key."_".$option_count] : '';
							        $extra_options[$option_key][$add_key] = $key_post;
								}
							}
						}

					}
					
					$counter++;
				}
				
				update_post_meta($post_id,'_wpfm_extra_options',$extra_options);

			}
		}
		
		remove_action('food_manager_save_food_manager', array($this, 'food_manager_save_food_manager_data'), 20, 2);
		$food_data = array(
			'ID'          => $post_id,
			//'post_status' => $post_status,
		);
		wp_update_post($food_data);
		add_action('food_manager_save_food_manager', array($this, 'food_manager_save_food_manager_data'), 20, 2);
	}

	
	/**
	 * wpfm_get_food_listings_by_category_id function.
	 *
	 * @access public
	 * @param NULL
	 * @return void
	 */
	public function wpfm_get_food_listings_by_category_id()
	{
		
		if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {

			$args = [
				'post_type' => 'food_manager',
				'post_per_page' => -1,
				'post_status' => 'publish',
				'tax_query' => [
					[
						'taxonomy' => 'food_manager_category',
						'terms' => $_POST['category_id'],
					],
				],

				// Rest of your arguments
			];


			$food_listing = new WP_Query($args);
			$html = [];
			if ($food_listing->have_posts()) :
				while ($food_listing->have_posts()) : $food_listing->the_post();
					$id = get_the_ID();
					$html[] = '<li class="menu-item-handle" data-food-id="' . $id . '">
			    										<div class="wpfm-admin-left-col">
			    											<span class="dashicons dashicons-menu"></span>
			    											<span class="item-title">' . get_the_title($id) . '</span>
			    										</div>
			    										<div class="wpfm-admin-right-col">
			    											<a href="javascript:void(0);" class="wpfm-food-item-remove">
			    												<span class="dashicons dashicons-dismiss"></span>
			    											</a>
			    										</div>
			    										<input type="hidden" name="wpfm_food_listing_ids[]" value="' . $id . '" />
			    									</li>';

				endwhile;
			endif;
			wp_reset_postdata();

			wp_send_json(array('html' => $html, 'success' => true));
		} else {
			$args = [
				'post_type' => 'food_manager',
				'post_per_page' => -1,
				// Rest of your arguments
			];

			$food_listing = new WP_Query($args);
			$html = [];
			if ($food_listing->have_posts()) :
				while ($food_listing->have_posts()) : $food_listing->the_post();
					$id = get_the_ID();
					$html[] = '<li class="menu-item-handle" data-food-id="' . $id . '">
			    										<div class="wpfm-admin-left-col">
			    											<span class="dashicons dashicons-menu"></span>
			    											<span class="item-title">' . get_the_title($id) . '</span>
			    										</div>
			    										<div class="wpfm-admin-right-col">
			    											<a href="javascript:void(0);" class="wpfm-food-item-remove">
			    												<span class="dashicons dashicons-dismiss"></span>
			    											</a>
			    										</div>
			    										<input type="hidden" name="wpfm_food_listing_ids[]" value="' . $id . '" />
			    									</li>';

				endwhile;
			endif;
			wp_reset_postdata();

			wp_send_json(array('html' => $html, 'success' => true));
		}
		wp_die();
	}

	/**
	 * Removes all action links because WordPress add it to primary column.
	 * Note: Removing all actions also remove mobile "Show more details" toggle button.
	 * So the button need to be added manually in custom_columns callback for primary column.
	 *
	 * @access public
	 * @param array $actions
	 * @return array
	 */
	public function row_actions($actions)
	{
		if ('food_manager' == get_post_type()) {
			return array();
		}
		return $actions;
	}

	/**
	 * wpfm_get_food_listings_by_category_id function.
	 *
	 * @access public
	 * @param post_id numeric
	 * @param post Object
	 * @return void
	 */
	public function food_manager_save_food_manager_menu_data($post_id, $post)
	{
		$wpfm_radio_icon = $_POST['radio_icons'];
		if (isset($wpfm_radio_icon)) {
			if( !add_post_meta($post_id,'wpfm_radio_icons', $wpfm_radio_icon, true) ){
				update_post_meta($post_id,'wpfm_radio_icons', $wpfm_radio_icon);
			}
		}

		if (isset($_POST['wpfm_food_listing_ids'])) {
			$item_ids = array_map('esc_attr', $_POST['wpfm_food_listing_ids']);
			update_post_meta($post_id, '_food_item_ids', $item_ids);
		} else {
			update_post_meta($post_id, '_food_item_ids', '');
		}

		if (isset($_POST['cat'])) {
			$cat_ids = array_map('esc_attr', $_POST['cat']);
			update_post_meta($post_id, '_food_item_cat_ids', $cat_ids);
		} else {
			update_post_meta($post_id, '_food_item_cat_ids', '');
		}
	}

	/**
	 * columns function.
	 *
	 * @param array $columns
	 * @return array
	 */

	public function columns($columns)
	{

		if (!is_array($columns)) {

			$columns = array();
		}

		unset($columns['title'], $columns['date'], $columns['author']);

		$columns['food_title'] = __('Title', 'wp-food-manager');

		$columns['food_banner'] = '<span class="tips dashicons dashicons-format-image" data-tip="' . __('Banner', 'wp-food-manager') . '">' . __('Banner', 'wp-food-manager') . '</span>';

		$columns['fm_stock_status'] = __('Stock Status', 'wp-food-manager');

		$columns['fm_categories'] = __('Categories', 'wp-food-manager');

		$columns['food_menu_order'] = __('Order', 'wp-food-manager');

		$columns['food_actions'] = __('Actions', 'wp-food-manager');

		if (!get_option('food_manager_enable_food_types')) {

			unset($columns['food_manager_type']);
		}
		return $columns;
	}

	public function set_shortcode_copy_columns($columns)
	{
		$columns['shortcode'] = __('Shortcode', 'wp-food-manager');
		return  $columns;
	}

	public function shortcode_copy_content_column($column, $post_id)
	{
		echo '<code>';
		printf(__('[food_menu id=%d]', 'wp-food-manager'), $post_id);
		echo '</code>';
	}

	public function set_custom_food_columns($columns)
	{
		$custom_col_order = array(
	        'cb' => $columns['cb'],
	        'food_title' => $columns['title'],
	        'food_banner' => __( 'Image', 'wp-food-manager' ),
	        'fm_stock_status' => __( 'Stock Status', 'wp-food-manager' ),
	        'fm-price' => __( 'Price', 'wp-food-manager' ),
	        'fm_categories' => __( 'Categories', 'wp-food-manager' ),
	        'food_menu_order' => __( 'Order', 'wp-food-manager' ),
	        'date' => $columns['date'],
	        'food_actions' => __( 'Actions', 'wp-food-manager' )
	    );
	    return $custom_col_order;
	}

	public function set_custom_food_sortable_columns($columns)
	{
		$columns['food_menu_order'] = 'menu_order';
		
		return  $columns;
	}

	public function custom_food_content_column($column, $post_id)
	{
		global $post;

		switch ($column) {

			case 'food_title':
				echo wp_kses_post('<div class="food_title">');

				echo wp_kses_post('<a href="' . esc_url(admin_url('post.php?post=' . $post->ID . '&action=edit')) . '" class="tips food_title" data-tip="' . sprintf(wp_kses('ID: %d', 'wp-food-manager'), $post->ID) . '">' . esc_html($post->post_title) . '</a>');

				echo wp_kses_post('</div>');

				echo wp_kses_post('<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__('Show more details', 'wp-food-manager') . '</span></button>');

				break;

			case 'food_banner':
				echo wp_kses_post('<div class="food_banner">');

				display_food_banner();

				echo wp_kses_post('</div>');
				/*$food_thumbnail = get_the_post_thumbnail( $post_id, 'thumbnail', array( 'class' => 'alignleft' ) );
				if(empty($food_thumbnail) || $food_thumbnail == ''){
					$food_thumbnail_url = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );
					$food_thumbnail = '<img src='.esc_url($food_thumbnail_url).' height="60px" width="60px">';
				} else {
					$food_thumbnail = get_the_post_thumbnail( $post_id, array( 60, 60), array( 'class' => 'alignleft' ) );
				}
				echo $food_thumbnail;*/
				display_food_veg_nonveg_icon_tag();

				break;

			case 'fm-price':
				display_food_price_tag();

				break;

			case 'fm_categories':
				echo display_food_category();

				break;

			case 'fm_stock_status':
				echo display_stock_status();

				break;

			case 'food_menu_order':
				$thispost = get_post($post_id);
				echo $thispost->menu_order;

				break;

			case 'food_actions':
				echo wp_kses_post('<div class="actions">');

				$admin_actions = apply_filters('post_row_actions', array(), $post);

				if (in_array($post->post_status, array('pending', 'pending_payment')) && current_user_can('publish_post', $post->ID)) {

					$admin_actions['publish'] = array(

						'action' => 'publish',

						'name'   => __('Publish', 'wp-food-manager'),

						'url'    => wp_nonce_url(add_query_arg('approve_food', $post->ID), 'approve_food'),
					);
				}

				if ($post->post_status !== 'trash') {

					if (current_user_can('read_post', $post->ID)) {

						$admin_actions['view'] = array(

							'action' => 'view',

							'name'   => __('View', 'wp-food-manager'),

							'url'    => get_permalink($post->ID),
						);
					}

					if (current_user_can('edit_post', $post->ID)) {

						$admin_actions['edit'] = array(

							'action' => 'edit',

							'name'   => __('Edit', 'wp-food-manager'),

							'url'    => get_edit_post_link($post->ID),
						);
					}

					if (current_user_can('delete_post', $post->ID)) {

						$admin_actions['delete'] = array(

							'action' => 'delete',

							'name'   => __('Delete', 'wp-food-manager'),

							'url'    => get_delete_post_link($post->ID),
						);
					}
				}

				$admin_actions = apply_filters('food_manager_admin_actions', $admin_actions, $post);

				foreach ($admin_actions as $action) {

					if (is_array($action)) {

						printf('<a class="button button-icon tips icon-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', $action['action'], esc_url($action['url']), esc_attr($action['name']), esc_html($action['name']));
					} else {

						echo esc_attr(str_replace('class="', 'class="button ', $action));
					}
				}

				echo wp_kses_post('</div>');
				
				break;

		}
	}
}
WPFM_Writepanels::instance();
