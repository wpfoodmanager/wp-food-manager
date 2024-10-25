<?php
global $wp_post_types;

switch (esc_attr($food->post_status)):
	case 'publish':
		// Translators: %1$s is replaced with the singular name of the food type, %2$s is replaced with the permalink to the food listing.
		printf('<p class="post-submitted-success-green-message wpfm-alert wpfm-alert-success">' . __('%1$s listed successfully. To view your listing <a href="%2$s">click here</a>.', 'wp-food-manager') . '</p>', 
		esc_html($wp_post_types['food_manager']->labels->singular_name), esc_url(get_permalink($food->ID)));
		break;
	case 'pending':
		// Translators: %s is replaced with the singular name of the food type
		printf('<p class="post-submitted-success-green-message wpfm-alert wpfm-alert-success">' . __("Your %s has been successfully added to WP Food Manager. Your food listing will be visible after the admin's approval.", 'wp-food-manager') . '</p>', esc_html($wp_post_types['food_manager']->labels->singular_name), esc_url(get_permalink($food->ID)));
		break;
	default:
		do_action('food_manager_food_submitted_content_' . str_replace('-', '_', sanitize_title($food->post_status)), $food);
		break;
endswitch;

do_action('food_manager_food_submitted_content_after', sanitize_title($food->post_status), $food);
