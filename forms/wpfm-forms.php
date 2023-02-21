<?php

/**
 * WP_Food_Manager_Forms class.
 */

class WPFM_Forms
{

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  2.5
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  2.5
	 * @static
	 * @return self Main instance.
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action('init', array($this, 'load_posted_form'));
	}

	/**
	 * If a form was posted, load its class so that it can be processed before display.
	 */

	public function load_posted_form()
	{
		if (!empty($_POST['food_manager_form'])) {

			$this->load_form_class(sanitize_title($_POST['food_manager_form']));
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name
	 * @return string class name on success, false on failure
	 */

	private function load_form_class($form_name)
	{

		if (!class_exists('WPFM_Form')) {

			include 'wpfm-form-abstract.php';
		}

		// Now try to load the form_name
		$form_class  = 'WPFM_Form_' . str_replace('-', '_', $form_name);

		$form_file   = WPFM_PLUGIN_DIR . '/forms/wpfm-form-' . $form_name . '.php';

		if (class_exists($form_class)) {

			return call_user_func(array($form_class, 'instance'));
		}

		if (!file_exists($form_file)) {

			return false;
		}

		if (!class_exists($form_class)) {

			include $form_file;
		}

		// Init the form
		return call_user_func(array($form_class, 'instance'));
	}

	/**
	 * get_form function.
	 *
	 * @param string $form_name
	 * @param  array $atts Optional passed attributes
	 * @return string
	 */

	public function get_form($form_name, $atts = array())
	{
		if ($form = $this->load_form_class($form_name)) {
			ob_start();

			$form->output($atts);

			return ob_get_clean();
		}
	}


	/**
	 * get_form function.
	 *
	 * @param string $form_name
	 * @param  array $atts Optional passed attributes
	 * @return string
	 */

	public function get_form_fields($form_name, $field_types = 'frontend')
	{
		if ($form = $this->load_form_class($form_name)) {
			$form->init_fields();
			$fields = $form->merge_with_custom_fields($field_types);
			return $fields;
		}
	}
}
