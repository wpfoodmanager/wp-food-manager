<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles front admin page for WP food Manager.
 *
 * @since 2.5
 */
class WPFM_Permalink_Settings {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  2.5
	 */
	private static $_instance = null;

	/**
	 * Permalink settings.
	 *
	 * @var array
	 * @since 2.5
	 */
	private $permalinks = array();

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  2.5
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setup_fields();
		$this->settings_save();
		$this->permalinks = WPFM_Post_Types::get_permalink_structure();
	}

	/**
	 * Add setting fields related to permalinks.
	 */
	public function setup_fields() {
		add_settings_field(
			'wpfm_food_base_slug',
			__( 'food base', 'wp-food-manager' ),
			array( $this, 'food_base_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wpfm_food_category_slug',
			__( 'food category base', 'wp-food-manager' ),
			array( $this, 'food_category_slug_input' ),
			'permalink',
			'optional'
		);
		add_settings_field(
			'wpfm_food_type_slug',
			__( 'food type base', 'wp-food-manager' ),
			array( $this, 'food_type_slug_input' ),
			'permalink',
			'optional'
		);
	}

	/**
	 * Show a slug input box for food post type slug.
	 */
	public function food_base_slug_input() {
		?>
		<input name="wpfm_food_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['food_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'food', 'food permalink - resave permalinks after changing this', 'wp-food-manager' ); ?>" />
		<?php
	}

	/**
	 * Show a slug input box for food category slug.
	 */
	public function food_category_slug_input() {
		?>
		<input name="wpfm_food_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['category_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'food-category', 'food category slug - resave permalinks after changing this', 'wp-food-manager' ); ?>" />
		<?php
	}

	/**
	 * Show a slug input box for food type slug.
	 */
	public function food_type_slug_input() {
		?>
		<input name="wpfm_food_type_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['type_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'food-type', 'food type slug - resave permalinks after changing this', 'wp-food-manager' ); ?>" />
		<?php
	}

	/**
	 * Save the settings.
	 */
	public function settings_save() {
		if ( ! is_admin() ) {
			return;
		}

		if ( isset( $_POST['permalink_structure'] ) ) {
			if ( function_exists( 'switch_to_locale' ) ) {
				switch_to_locale( get_locale() );
			}

			$permalinks                  = (array) get_option( 'wpfm_permalinks', array() );
			$permalinks['food_base']      = sanitize_title_with_dashes( $_POST['wpfm_food_base_slug'] );
			$permalinks['category_base'] = sanitize_title_with_dashes( $_POST['wpfm_food_category_slug'] );
			$permalinks['type_base']     = sanitize_title_with_dashes( $_POST['wpfm_food_type_slug'] );

			update_option( 'wpfm_permalinks', $permalinks );

			if ( function_exists( 'restore_current_locale' ) ) {
				restore_current_locale();
			}
		}
	}
}

WPFM_Permalink_Settings::instance();
