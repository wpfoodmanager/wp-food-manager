<?php
global $wp_post_types;
switch ($food->post_status):
	case 'publish':
		printf('<p class="post-submitted-success-green-message wpfm-alert wpfm-alert-success">' . __('%s listed successfully. To view your listing <a href="%s">click here</a>.', 'wp-food-manager') . '</p>', $wp_post_types['food_manager']->labels->singular_name, get_permalink($food->ID));
		break;
	case 'pending':
		printf('<p class="post-submitted-success-green-message wpfm-alert wpfm-alert-success">' . __("Your %s has been successfully added to WP Food Manager. Your food listing will be visible after the admin's approval.", 'wp-food-manager') . '</p>', $wp_post_types['food_manager']->labels->singular_name, get_permalink($food->ID));
		break;
	default:
		do_action('food_manager_food_submitted_content_' . str_replace('-', '_', sanitize_title($food->post_status)), $food);
		break;
endswitch;
do_action('food_manager_food_submitted_content_after', sanitize_title($food->post_status), $food);
