<?php

/**
 * WPFM_Custom_Taxonomies class.
 */
class WPFM_Custom_Tax_Class_Taxonomies{
	/**
	 * Register of food manager taxonomies.
	 *
	 * @access public
	 * @return void
	 */

    public function __construct() {
        add_action('init', array($this, 'wpfm_register_tax_classes_taxonomy'));
        add_action('tax_class_add_form_fields', array($this, 'wpfm_tax_classes_add_meta_field'), 10, 2);
        add_action('tax_class_edit_form_fields', array($this, 'wpfm_tax_classes_edit_meta_field'), 10, 2);
        add_action('created_tax_class', array($this, 'wpfm_save_tax_class_meta'), 10, 2);
        add_action('edited_tax_class', array($this, 'wpfm_save_tax_class_meta'), 10, 2);
    }

	public function wpfm_register_tax_classes_taxonomy() {
	    $labels = array(
	        'name'              => _x('Tax Classes', 'taxonomy general name', 'wp-food-manager'),
	        'singular_name'     => _x('Tax Class', 'taxonomy singular name', 'wp-food-manager'),
	        'search_items'      => __('Search Tax Classes', 'wp-food-manager'),
	        'all_items'         => __('All Tax Classes', 'wp-food-manager'),
	        'parent_item'       => __('Parent Tax Class', 'wp-food-manager'),
	        'parent_item_colon' => __('Parent Tax Class:', 'wp-food-manager'),
	        'edit_item'         => __('Edit Tax Class', 'wp-food-manager'),
	        'update_item'       => __('Update Tax Class', 'wp-food-manager'),
	        'add_new_item'      => __('Add New Tax Class', 'wp-food-manager'),
	        'new_item_name'     => __('New Tax Class Name', 'wp-food-manager'),
	        'menu_name'         => __('Tax Classes', 'wp-food-manager'),
	    );

	    $args = array(
	        'hierarchical'      => true,
	        'labels'            => $labels,
	        'show_ui'           => true,
	        'show_admin_column' => true,
	        'query_var'         => true,
	        'rewrite'           => array('slug' => 'tax-class'),
	    );

	    register_taxonomy('tax_class', array('food_manager'), $args);
	}
	public function wpfm_tax_classes_add_meta_field() {
	    ?>
	    <div class="form-field term-group">
	        <label for="tax_class_type"><?php _e('Tax Type', 'wp-food-manager'); ?></label>
	        <select name="tax_class_type" id="tax_class_type">
	            <option value="fix_value"><?php _e('Fix Value', 'wp-food-manager'); ?></option>
	            <option value="percentage_discount"><?php _e('Percentage Discount', 'wp-food-manager'); ?></option>
	        </select>
	    </div>
	    <?php
	}
	public function wpfm_tax_classes_edit_meta_field($term) {
	    $tax_class_type = get_term_meta($term->term_id, 'tax_class_type', true);
	    ?>
	    <tr class="form-field term-group-wrap">
	        <th scope="row"><label for="tax_class_type"><?php _e('Tax Type', 'wp-food-manager'); ?></label></th>
	        <td>
	            <select name="tax_class_type" id="tax_class_type">
	                <option value="fix_value" <?php selected($tax_class_type, 'fix_value'); ?>><?php _e('Fix Value', 'wp-food-manager'); ?></option>
	                <option value="percentage_discount" <?php selected($tax_class_type, 'percentage_discount'); ?>><?php _e('Percentage Discount', 'wp-food-manager'); ?></option>
	            </select>
	        </td>
	    </tr>
	    <?php
	}
	public function wpfm_save_tax_class_meta($term_id) {
	    if (isset($_POST['tax_class_type'])) {
	        update_term_meta($term_id, 'tax_class_type', sanitize_text_field($_POST['tax_class_type']));
	    }
	}
}

new WPFM_Custom_Tax_Class_Taxonomies();