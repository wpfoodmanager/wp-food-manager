<?php

/**
 * WPFM_Forms class.
 */
class WPFM_Forms {

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
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Load a form's class.
	 * 
	 * @access public
	 * @param  string $form_name
	 * @return string class name on success, false on failure.
	 * @since 1.0.0
	 */
	public function load_form_class($form_name) {
		if (!class_exists('WPFM_Form')) {
			include esc_html('wpfm-abstract-form.php');
		}

		// Now try to load the form_name.
		$form_class  = 'WPFM_' . str_replace('-', '_', sanitize_key($form_name)) . '_Form';
		$form_file   = WPFM_PLUGIN_DIR . '/forms/wpfm-' . sanitize_file_name($form_name) . '-form.php';

		if (class_exists($form_class)) {
			return call_user_func(array($form_class, 'instance'));
		}

		if (!file_exists($form_file)) {
			return false;
		}

		if (!class_exists($form_class)) {
			include esc_html($form_file);
		}

		// Init the form.
		return call_user_func(array($form_class, 'instance'));
	}

	/**
	 * Get the form output given by form name and atts.
	 *
	 * @access public
	 * @param string $form_name
	 * @param  array $atts Optional passed attributes.
	 * @return string
	 * @since 1.0.0
	 */
	public function get_form($form_name, $atts = array()) {
		if ($form = $this->load_form_class($form_name)) {
			ob_start();
			$form->output($atts);
			return ob_get_clean();
		}
	}

	/**
	 * Get the given form's fields.
	 *
	 * @access public
	 * @param string $form_name
	 * @param array $atts Optional passed attributes.
	 * @return string
	 * @since 1.0.0
	 */
	public function get_form_fields($form_name, $field_types = 'frontend') {
		if ($form = $this->load_form_class($form_name)) {
			$form->init_fields();
			$fields = $form->merge_with_custom_fields(sanitize_text_field($field_types));
			return $fields;
		}
	}
}
