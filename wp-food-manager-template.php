<?php
/**
 * Template Functions
 *
 * Template functions specifically created for food listings and other food related methods.
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
 * @param string $template_path (default: 'wp-food-manager')
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
 * @param string $template_path (default: 'wp-food-manager')
 * @param string|bool $default_path (default: '') False to not load a default
 */
function get_food_manager_template_part( $slug, $name = '', $template_path = 'wp-food-manager', $default_path = '' ) {

	$template = '';

	if ( $name ) {

		$template = locate_food_manager_template( "{$slug}-{$name}.php", $template_path, $default_path );
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wp-food-manager/slug.php

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
 * get_food_location function.
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
* display_food_location function.
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

		echo  apply_filters( 'display_food_location_anywhere_text', __( 'Online food', 'wp-food-manager' ) );
	}
}

/**
 * get_the_food_logo function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 */
function get_food_banner( $post = null ) {

	$post = get_post( $post );

	if ( $post->post_type !== 'food_manager' )
		return;

	if(isset($post->_food_banner) && empty($post->_food_banner))
		$food_banner = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );
	else
		$food_banner = $post->_food_banner;			
	
	return apply_filters( 'display_food_banner', $food_banner, $post );
}

/**
 * get_food_thumbnail function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 */
function get_food_thumbnail( $post = null ) {

	$post = get_post( $post );

	if ( $post->post_type !== 'food_listing' )
		return;

	$food_thumbnail = get_the_post_thumbnail_url( $post );

	if( isset($food_thumbnail) && empty($food_thumbnail) )
		$food_thumbnail = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );	
	
	return apply_filters( 'display_food_thumbnail', $food_thumbnail, $post );
}

/**
 * display_food_banner function.
 *
 * @access public
 * @param string $size (default: 'full')
 * @param mixed $default (default: null)
 * @return void
 */
function display_food_banner( $size = 'full', $default = null, $post = null ) {

	$banner = get_food_banner( $post );

	if ( ! empty( $banner ) && ! is_array( $banner )  && ( strstr( $banner, 'http' ) || file_exists( $banner ) ) )
	{
		if ( $size !== 'full' ) {
				
			$banner = wpfm_get_resized_image( $banner, $size );
		}
		echo '<img itemprop="image" content="' . esc_attr( $banner ) . '" src="' . esc_attr( $banner ) . '" alt="" />';

	} else if ( $default ) {

		echo '<img itemprop="image" content="' . esc_attr( $default ) . '" src="' . esc_attr( $default ) . '" alt="" />';

	} else if(is_array($banner) && isset($banner[0]) ){
		echo '<img itemprop="image" content="' . esc_attr( $banner[0] ) . '" src="' . esc_attr( $banner[0] ) . '" alt="' .  '" />';
	}
	else  {
		echo '<img itemprop="image" content="' . esc_attr( apply_filters( 'food_manager_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' ) ) . '" src="' . esc_attr( apply_filters( 'food_manager_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' ) ) . '" alt="' . esc_attr( get_the_title() ) . '" />';
	}
}

/** This function is use to get the counts the food views and attendee views.
 *   This function also used at food, attendee dashboard file.
 *   @return number counted view.
 *   @param $post
 **/
function get_food_views_count($post)
{
	$count_key = '_view_count';
	$count = get_post_meta($post->ID, $count_key, true);

	if($count=='' || $count==null)
	{
		delete_post_meta($post->ID, $count_key);
		add_post_meta($post->ID, $count_key, '0');
		return "-";
	}
	return $count;
}


/**
 * display_event_type function.
 *
 * @access public
 * @return void
 */
function display_food_type( $post = null, $after = '') {

	if ( $event_type = get_event_type( $post ) ) {
		if (! empty( $event_type ) ) {
		    $numType = count($event_type);
		    $i = 0;
			foreach ( $event_type as $type ) {
				echo '<span class="wpem-event-type-text event-type '. esc_attr( sanitize_title( $type->slug ) ).' ">'. $type->name.'</span>';
				if($numType > ++$i){
				    echo $after;
				}
			}
		}
	}
}

/**
 * get_event_type function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 */
function get_food_type( $post = null ) {

	$post = get_post( $post );

	if ( $post->post_type !== 'food_manager' || !get_option( 'food_manager_enable_event_types' ) ) {
		return;
	}

	$types = wp_get_post_terms( $post->ID, 'food_manager_type' );

	// Return single if not enabled.
	if ( ! empty( $types ) && ! event_manager_multiselect_event_type() ) {
		$types = array( current( $types ) );
	}
	if(empty($types))
		$types = '';
	return apply_filters( 'display_food_type', $types, $post );
}
/**
 * display_event_category function.
 *
 * @access public
 * @return void
 */
function display_food_category( $post = null, $after = '' ) {

	if ( $event_category = get_food_category( $post ) ) {

		if (! empty( $event_category ) ) {
		    $numCategory = count($event_category);
		    $i = 0;
			foreach ( $event_category as $cat ) {
				echo '<span class="event-category '. esc_attr( sanitize_title( $cat->slug ) ).' ">'. $cat->name.'</span>';
				if($numCategory > ++$i){
				    echo $after;
				}
			}
		}
	}
}

/**
 * get_event_category function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 */
function get_food_category( $post = null ) {

	$post = get_post( $post );

	if ( $post->post_type !== 'food_manager' || !get_option( 'food_manager_enable_categories' ) ) {
		return;
	}

	$categories = wp_get_post_terms( $post->ID, 'food_manager_category' );

	// Return single if not enabled.
	if ( !empty( $categories ) && ! food_manager_multiselect_food_category() ) {
		$categories = array( current( $categories ) );
	}
	return apply_filters( 'display_food_category', $categories, $post );
}
/**
 * display_food_permalink function.
 *
 * @access public
 * @return void
 */
function display_food_permalink( $post = null ) {

	echo get_food_permalink( $post );
}

/**
 * get_event_permalink function
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 */
function get_food_permalink( $post = null ) {

	$post = get_post( $post );

	$link = get_permalink( $post );

	return apply_filters( 'display_food_permalink', $link, $post );
}


/**
 * event_listing_class function.
 *
 * @access public
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 */
function food_manager_class( $class = '', $post_id = null ) {

	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . join( ' ', get_food_manager_class( $class, $post_id ) ) . '"';

}

/**
 * get_event_listing_class function.
 *
 * @access public
 * @return array
 */
function get_food_manager_class( $class = '', $post_id = null ) {

	$post = get_post( $post_id );

	if ( $post->post_type !== 'food_manager' ) {
		return array();
	}

	$classes = array();

	if ( empty( $post ) ) {
		return $classes;
	}

	$classes[] = 'food_manager';

	if ( $event_type = get_food_type() ) {

		if ( $event_type && ! empty( $event_type ) ) {
			foreach ( $event_type as $type ) {
				$classes[] = 'food-type-' . sanitize_title( $type->name );
			}
		}
	}

	
	if ( is_food_featured( $post ) ) {

		$classes[] = 'food_featured';
	}

	if ( ! empty( $class ) ) {

		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}

		$classes = array_merge( $classes, $class );
	}

	return get_post_class( $classes, $post->ID );
}