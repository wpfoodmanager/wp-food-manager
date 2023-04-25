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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
					<span class="no-menu-item-handle" style="display: none;">There is no food available in the selected category.</span>
				<?php } else { ?>
					<span class="no-menu-item-handle">There is no food available in the selected category.</span>
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
	 * @since 1.0.0
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
	 * @access public
	 * @return array
	 * @since 1.0.0
	 */
	public function get_food_data_tabs() {
		$tabs = apply_filters(
			'wpfm_food_data_tabs',
			array(
				'general'        => array(
					'label'    => __('General', 'wp-food-manager'),
					'target'   => 'general_food_data_content',
					'class'    => array(''),
					'priority' => 1,
				),
				'toppings'        => array(
					'label'    => __('Toppings', 'wp-food-manager'),
					'target'   => 'toppings_food_data_content',
					'class'    => array(),
					'priority' => 2,
				),
				'ingredients'        => array(
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

	/**
	 * food_manager_data_fields function.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function food_manager_data_fields() {
		global $post;
		$current_user = wp_get_current_user();
		$fields =  $GLOBALS['food_manager']->forms->get_form_fields('add-food', 'backend');
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
	 * 
	 * @access public
	 * @param array $a
	 * @param array $b
	 * @return void
	 * @since 1.0.0
	 */
	protected function sort_by_priority($a, $b) {
		if (!isset($a['priority']) || !isset($b['priority']) || $a['priority'] === $b['priority']) {
			return 0;
		}
		return ($a['priority'] < $b['priority']) ? -1 : 1;
	}
}

WPFM_Writepanels::instance();
