<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly.

/**
 * WPFM_Category_Walker class.
 *
 * @extends Walker
 */
class WPFM_Category_Walker extends Walker {

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

	var $tree_type = 'category';
	var $db_fields = array('parent' => 'parent', 'id' => 'term_id', 'slug' => 'slug');

	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int $depth Depth of category in reference to parents.
	 * @param array $args
	 * @since 1.0.0
	 */
	function start_el(&$output, $object, $depth = 0, $args = array(), $current_object_id = 0) {
		$cat_arr_ids = array($object->term_id);
		$item_cat_ids = isset($_GET['post']) && !empty(get_post_meta($_GET['post'], '_food_item_cat_ids', true)) ? get_post_meta($_GET['post'], '_food_item_cat_ids', true) : '';
		$field_val = '';

		if (!empty($item_cat_ids)) {
			$field_val = ($item_cat_ids[0] == $object->term_id) ? "selected" : "";
		}

		if (!empty($args['hierarchical']))
			$pad = str_repeat('&nbsp;', $depth * 3);
		else
			$pad = '';

		$cat_name = apply_filters('list_food_cats', $object->name, $object);
		$value = isset($args['value']) && $args['value'] == 'id' ? $object->term_id : $object->slug;
		$output .= "\t<option class=\"level-" . intval($depth) . '" value="' . esc_attr($value) . '" ' . esc_attr($field_val) . ' ';
		$output .= (!empty($args['show_count'])) ? 'data-count="' . esc_attr($object->count) . '"' : '';

		if ($value == $args['selected'] || (is_array($args['selected']) && in_array($value, $args['selected'])))
			$output .= ' selected="selected"';

		$output .= '>';
		$output .= $pad . esc_html($cat_name);
		if (!empty($args['show_count'])) {
			$output .= '&nbsp;(' . esc_html($object->count) . ')';
		}

		$output .= "</option>\n";
	}
}
