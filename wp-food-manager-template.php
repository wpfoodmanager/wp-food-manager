<?php
/**
 * Template Functions
 *
 * Template functions specifically created for event listings and other event related methods.
 *
 * @author 	WP Food Manager
 * @category 	Core
 * @version     1.0.5
 */

/**
 * Returns the translated role of the current user. If that user has
 * no role for the current blog, it returns false.
 *
 * @return string The name of the current role
 * @since 1.0.0
 */
function get_food_manager_current_user_role() {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
}

/**
 * Get and include template files.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function get_food_manager_template( $template_name, $args = array(), $template_path = 'wp-food-manager', $default_path = '' ) {

	if ( $args && is_array( $args ) ) {

		extract( $args );
	}
	include( locate_food_manager_template( $template_name, $template_path, $default_path ) );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @param string $template_name
 * @param string $template_path (default: 'wp-event-manager')
 * @param string|bool $default_path (default: '') False to not load a default
 * @return string
 */
function locate_food_manager_template( $template_name, $template_path = 'wp-food-manager', $default_path = '' ) {

	// Look within passed path within the theme - this is priority

	$template = locate_template(
		
	array(
		
	trailingslashit( $template_path ) . $template_name,
		
	$template_name
	)
	);

	// Get default template

	if ( ! $template && $default_path !== false ) {

		$default_path = $default_path ? $default_path : WPFM_PLUGIN_DIR . '/templates/';

		if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
				
			$template = trailingslashit( $default_path ) . $template_name;
		}
	}

	// Return what we found

	return apply_filters( 'food_manager_locate_template', $template, $template_name, $template_path );
}

/**
 * Get template part (for templates in loops).
 *
 * @param string $slug
 * @param string $name (default: '')
 * @param string $template_path (default: 'wp-event-manager')
 * @param string|bool $default_path (default: '') False to not load a default
 */
function get_food_manager_template_part( $slug, $name = '', $template_path = 'wp-food-manager', $default_path = '' ) {

	$template = '';

	if ( $name ) {

		$template = locate_food_manager_template( "{$slug}-{$name}.php", $template_path, $default_path );
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wp-event-manager/slug.php

	if ( ! $template ) {

		$template = locate_food_manager_template( "{$slug}.php", $template_path, $default_path );
	}

	if ( $template ) {

		load_template( $template, false );
	}
}










/**
 * Return whether or not the position has been marked as cancelled
 *
 * @param  object $post
 * @return boolean
 */
function is_food_cancelled( $post = null ) {

	$post = get_post( $post );

	return $post->_cancelled ? true : false;
}

/**
 * Return whether or not the position has been featured
 *
 * @param  object $post
 * @return boolean
 */
function is_food_featured( $post = null ) {

	$post = get_post( $post );

	return $post->_featured ? true : false;
}


/**
 * get_event_location function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 */
function get_food_location( $post = null ) {

	$post = get_post( $post );

	if ( $post->post_type !== 'food_manager' )
		return;

	return apply_filters( 'display_food_location', $post->_food_location, $post );
}

/**
* display_event_location function.
* @param  boolean $map_link whether or not to link to the map on google maps
* @return [type]
*/
function display_food_location( $map_link = true, $post = null ) {

	$location = get_food_location( $post );

	if ( $location ) {

		if ( $map_link )
			echo apply_filters( 'display_food_location_map_link', '<a  href="http://maps.google.com/maps?q=' . urlencode( $location ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false" target="_blank">' . $location . '</a>', $location, $post );
		else
			echo  $location;

	} else {

		echo  apply_filters( 'display_food_location_anywhere_text', __( 'Online food', 'wp-event-manager' ) );
	}
}
