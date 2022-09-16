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
		add_filter('manage_food_manager_posts_columns', array($this, 'set_custom_food_columns'));
		add_filter('manage_edit-food_manager_sortable_columns', array($this, 'set_custom_food_sortable_columns'));
		add_action('manage_food_manager_posts_custom_column', array($this, 'custom_food_content_column'), 10, 2);

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

		//add_meta_box('food_manager_menu_data', __('Menu items', 'wp-food-manager'), array($this, 'food_manager_menu_data'), 'food_manager_menu', 'normal', 'high');
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
?>

		<div class="wpfm-admin-food-menu-container wpfm-flex-col wpfm-admin-postbox-meta-data">
			<div class="wpfm-admin-menu-selection wpfm-admin-postbox-form-field">
				<label for="_add_food"><?php _e('Select food category'); ?></label>
				<div class="wpfm-admin-postbox-drop-btn">
					<?php food_manager_dropdown_selection(array(
						'multiple' => false, 'show_option_all' => __('Select category', 'wp-food-manager'),
						'id' => 'wpfm-admin-food-selection',
						'taxonomy' => 'food_manager_category',
						'hide_empty' => false,
						'pad_counts' => true,
						'show_count' => true,
						'hierarchical' => false,
					)); ?>
					<input type="button" id="wpfm-admin-add-food" class="button button-small" value="<?php _e('Add food', 'wp-food-manager'); ?>" />
				</div>
			</div>
			<div class="wpfm-admin-food-menu-items">
				<ul class="wpfm-food-menu menu menu-item-bar ">
					<?php $item_ids = get_post_meta($thepostid, '_food_item_ids', true);
					if ($item_ids && is_array($item_ids)) {
						foreach ($item_ids as $key => $id) {
					?>
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
			</div>
		</div>
	<?php
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
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>: <?php if (!empty($field['description'])) : ?><span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
			<span class="wpfm-input-field">
				<input type="text" class="wpfm-small-field" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" />
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

		$descClass = "";
		if(!empty($field['value'])){
			$descClass = "";
		} else {
			$descClass = "option-desc-common";
		}
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?> <?=$descClass;?>">
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> <?php if (!empty($field['description'])) : ?>: <span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
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
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>: <?php if (!empty($field['description'])) : ?><span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
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
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>: <?php if (!empty($field['description'])) : ?><span class="tips" data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span><?php endif; ?></label>
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
		if (empty($field['value'])) {
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
			<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label>
			<?php if($key == '_enable_food_ingre' || $key == '_enable_food_nutri' || $key == '_option_enable_desc_'.end($exp_arr)) { ?>
				<span class="wpfm-input-field">
					<label class="wpfm-field-switch" for="<?php echo esc_attr($key); ?>">
						<input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($name); ?>" value="1"
						<?php checked($field['value'], 1); ?>>
						<span class="wpfm-field-switch-slider round"></span>
					</label>
				</span>
			<?php } else { ?>
				<input type="checkbox" class="checkbox " name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" value="1" <?php checked($field['value'], 1); ?> />
				<?php if (!empty($field['description'])) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; 
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
	public static function input_number($key, $field)
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
				<input type="number" class="wpfm-small-field" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" value="<?php echo esc_attr($field['value']); ?>" />
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
		if (empty($field['value'])) {
			$field['value'] = get_post_meta($thepostid, $key, true);
		}
		if (!empty($field['name'])) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
	?>
		<p class="wpfm-admin-postbox-form-field <?=$name;?>">
			<label><?php echo esc_html($field['label']); ?></label>
			<span class="wpfm-input-field">
				<?php foreach ($field['options'] as $option_key => $value) : ?>
					<input type="radio" class="radio" name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" value="<?php echo esc_attr($option_key); ?>" <?php checked($field['value'], $option_key); ?> /> <?php echo esc_html($value); ?>
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
									<td><input type="text" name="<?php echo esc_attr($wpfm_key_num);?>_option_value_name_<?php echo esc_attr($count); ?>" value="<?php if(isset($op_value['option_value_name']) ) echo $op_value['option_value_name']; ?>" class="opt_name"></td>
									<!-- <td><input type="checkbox" name="%%repeated-option-index2%%_option_value_default_<?php //echo esc_attr($count);?>" value="1"<?php //if(isset($op_value['option_value_default']) && $op_value['option_value_price_type'] == 'option_value_default') echo 'checked="checked"' ?> class="opt_default"></td> -->
									<td><input type="checkbox" name="<?php echo esc_attr($wpfm_key_num);?>_option_value_default_<?php echo esc_attr($count);?>" <?php if(isset($op_value['option_value_default']) && $op_value['option_value_default'] == 'on') echo 'checked="checked"'; ?> class="opt_default"></td>

									<td><input type="text" name="<?php echo esc_attr($wpfm_key_num);?>_option_value_price_<?php echo esc_attr($count);?>" value="<?php if(isset($op_value['option_value_price']) ) echo $op_value['option_value_price']; ?>" class="opt_price"></td>

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
					<td><input type=&apos;text&apos; name=&apos;%%repeated-option-index2%%_option_value_name_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_name&apos;></td>
					<td><input type=&apos;checkbox&apos; name=&apos;%%repeated-option-index2%%_option_value_default_%%repeated-option-index3%%&apos; class=&apos;opt_default&apos;></td>
					<td><input type=&apos;number&apos; name=&apos;%%repeated-option-index2%%_option_value_price_%%repeated-option-index3%%&apos; value=&apos;&apos; class=&apos;opt_price&apos;></td>
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

		//error_log(print_r($_POST,true));

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
		}
		
		if(isset($_POST['_enable_food_nutri'])){
			$fd_food_nutri = sanitize_text_field($_POST['_enable_food_nutri']);
			if( !add_post_meta($post_id,'_enable_food_nutri', $fd_food_nutri, true) ){
				update_post_meta($post_id,'_enable_food_nutri', $fd_food_nutri);
			}
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


		foreach ($this->food_manager_data_fields() as $key => $field) {
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
			if( !add_post_meta($post_id,'wpfm_repeated_options', $_POST['repeated_options'], true) ){
				update_post_meta($post_id,'wpfm_repeated_options', $_POST['repeated_options']);
			}

			// Options value count
			$array = $_POST['option_value_count'];
			$food_data_option_value_count = array();
			$index = 0;
			foreach ($array as $number) {
			    if ($number == 1) {
			        $index++;
			    }
			    $food_data_option_value_count[$index][] = $number;
			}
			if( !add_post_meta($post_id,'wpfm_option_value_count', $food_data_option_value_count, true) ){
				update_post_meta($post_id,'wpfm_option_value_count', $food_data_option_value_count);
			}

			// author
			if ('_food_author' === $key) {
				$wpdb->update($wpdb->posts, array('post_author' => $_POST[$key] > 0 ? absint($_POST[$key]) : 0), array('ID' => $post_id));
			}
			// Everything else		
			else {
				$type = !empty($field['type']) ? $field['type'] : '';
				$extra_options = array();

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
							$option_description = $_POST['_option_description_'.$option_count];
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
										
										// Old Logic
										/*$option_values[$option_count.'_option_value_name_'.$option_value_count] = array(
															$option_count.'_option_value_name_'.$option_value_count => isset($_POST[$option_count.'_option_value_name_'.$option_value_count]) ? $_POST[$option_count.'_option_value_name_'.$option_value_count] : '',

															$option_count.'_option_value_default_'.$option_value_count => isset($_POST[$option_count.'_option_value_default_'.$option_value_count]) ? $_POST[$option_count.'_option_value_default_'.$option_value_count] : '',

															$option_count.'_option_value_price_'.$option_value_count => isset($_POST[$option_count.'_option_value_price_'.$option_value_count]) ? $_POST[$option_count.'_option_value_price_'.$option_value_count] : '',

															$option_count.'_option_value_price_type_'.$option_value_count => isset($_POST[$option_count.'_option_value_price_type_'.$option_value_count]) ? $_POST[$option_count.'_option_value_price_type_'.$option_value_count] : ''
														);*/

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

							$extra_options[$option_key] = array(
																'option_name' => $option_name,
																'option_type' => $option_type,
																'option_required' => $option_required,
																'option_enable_desc' => $option_enable_desc,
																'option_description' => $option_description,
																/*'option_minimum' => $option_minimum,
																'option_maximum' => $option_maximum,
																'option_price' => $option_price,
																'option_price_type' => $option_price_type,*/
																'option_options' => $option_values,
															);
						}

					}
					$counter++;
				}

				update_post_meta($post_id,'_wpfm_extra_options',$extra_options);
				
				switch ($type) {
					case 'textarea':
						update_post_meta($post_id, $key, wp_kses_post(stripslashes($_POST[$key])));
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
							$date_dbformatted = WP_Event_Manager_Date_Time::date_parse_from_format($php_date_format, $date);
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
				'tax_query' => [
					[
						'taxonomy' => 'food_manager_category',
						'terms' => $_POST['category_id'],
					],
				],
				// Rest of your arguments
			];

			$food_listing = new WP_Query($args);
			$html = '';
			if ($food_listing->have_posts()) :
				while ($food_listing->have_posts()) : $food_listing->the_post();
					$id = get_the_ID();
					$html = '<li class="menu-item-handle" data-food-id="' . $id . '">
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
	 * wpfm_get_food_listings_by_category_id function.
	 *
	 * @access public
	 * @param post_id numeric
	 * @param post Object
	 * @return void
	 */
	public function food_manager_save_food_manager_menu_data($post_id, $post)
	{
		if (isset($_POST['wpfm_food_listing_ids'])) {
			$item_ids = array_map('esc_attr', $_POST['wpfm_food_listing_ids']);
			update_post_meta($post_id, '_food_item_ids', $item_ids);
		} else {
			update_post_meta($post_id, '_food_item_ids', '');
		}
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
	        'title' => $columns['title'],
	        'image' => __( 'Image', 'wp-food-manager' ),
	        'fm_stock_status' => __( 'Stock Status', 'wp-food-manager' ),
	        'price' => __( 'Price', 'wp-food-manager' ),
	        'fm_categories' => __( 'Categories', 'wp-food-manager' ),
	        'food_menu_order' => __( 'Order', 'wp-food-manager' ),
	        'date' => $columns['date']
	    );
	    return $custom_col_order;

		/*$columns['image'] = __('Image', 'wp-food-manager');
		$columns['price'] = __('Price', 'wp-food-manager');
		$columns['fm_categories'] = __('Categories', 'wp-food-manager');
		return  $columns;*/
	}

	public function set_custom_food_sortable_columns($columns)
	{
		$columns['food_menu_order'] = 'menu_order';
		
		return  $columns;
	}

	public function custom_food_content_column($column, $post_id)
	{
		if($column == 'image'){
			$food_thumbnail = get_the_post_thumbnail( $post_id, 'thumbnail', array( 'class' => 'alignleft' ) );
			if(empty($food_thumbnail) || $food_thumbnail == ''){
				$food_thumbnail_url = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );
				$food_thumbnail = '<img src='.esc_url($food_thumbnail_url).' height="60px" width="60px">';
			} else {
				$food_thumbnail = get_the_post_thumbnail( $post_id, array( 60, 60), array( 'class' => 'alignleft' ) );
			}
			echo $food_thumbnail;
		}
		if($column == 'price'){
			display_food_price_tag();
		}
		
		if($column == 'fm_categories'){
			echo display_food_category();
		}

		if($column == 'fm_stock_status'){
			echo display_stock_status();
		}

		if($column == 'food_menu_order'){
			$thispost = get_post($post_id);
			echo $thispost->menu_order;
		}
	}
}
WPFM_Writepanels::instance();
