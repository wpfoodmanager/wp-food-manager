<?php

/**
 * Call when the plugin is uninstalled.
 */

// If WP_UNINSTALL_PLUGIN not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

// This Included file cleanup all data.
require 'includes/wpfm-data-cleaner.php';

if (!is_multisite()) {

	// Only do deletion if the setting is true.
	$do_deletion = get_option('food_manager_delete_data_on_uninstall');
	if ($do_deletion) {
		WPFM_Data_Cleaner::cleanup_all();
	}
} else {
	global $wpdb;
	$blog_ids         = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	$original_blog_id = get_current_blog_id();

	foreach ($blog_ids as $blog_id) {
		switch_to_blog($blog_id);

		// Only do deletion if the setting is true.
		$do_deletion = get_option('food_manager_delete_data_on_uninstall');
		if ($do_deletion) {
			WPFM_Data_Cleaner::cleanup_all();
		}
	}

	switch_to_blog(absint($original_blog_id));
}

// In the entire plugin's option name's array which is used in plugin for the deletion.
$options = array(
	'food_manager_installed_terms',
	'food_manager_enable_categories',
	'food_manager_enable_food_types',
	'food_manager_enable_food_tags',
	'food_manager_user_requires_account',
	'food_manager_generate_username_from_email',
	'food_manager_enable_registration',
	'food_manager_user_can_edit_pending_submissions',
	'food_manager_use_standard_password_setup_email',
	'food_manager_add_food_page_id',
	'food_manager_food_dashboard_page_id',
	'food_manager_foods_page_id',
	'food_manager_submission_requires_approval',
	'food_manager_per_page',
	'food_manager_food_item_show_hide',
	'food_manager_enable_default_category_multiselect',
	'food_manager_enable_default_food_type_multiselect',
	'food_manager_enable_default_food_menu_multiselect',
	'food_manager_nutritions_dashboard_page_id',
	'food_manager_ingredients_dashboard_page_id',
	'food_manager_enable_field_editor',
	'food_manager_delete_data_on_uninstall',
	'food_manager_setup',
	'food_manager_login_page_url',
	'food_manager_rating_showcase_admin_notices_dismiss',
	'food_manager_version',
	'food_manager_permalinks',
	'food_manager_upgrade_database',
	'food_manager_installation',
	'food_manager_installation_skip',
	'food_manager_enable_thumbnail',
	'food_manager_enable_food_menu',
	'food_manager_add_food_form_fields',
	'food_manager_submit_toppings_form_fields',
	'food_manager_form_fields',
	'wpfm_food_import_fields',
	''

);

//Delete the options.
foreach ($options as $option) {
	delete_option(esc_attr($option));
}
