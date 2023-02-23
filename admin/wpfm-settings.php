<?php
/*
* This file use for settings at admin site for wp food manager plugin.
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * WPFM_Settings class.
 */

class WPFM_Settings
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
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */

	public function __construct()
	{

		$this->settings_group = 'food_manager';

		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * init_settings function.
	 *
	 * @access protected
	 * @return void
	 */

	protected function init_settings()
	{

		// Prepare roles option

		$roles         = get_editable_roles();
		$account_roles = array();

		foreach ($roles as $key => $role) {

			if ($key == 'administrator') {

				continue;
			}

			$account_roles[$key] = $role['name'];
		}

		$currency_code_options = get_food_manager_currencies();

		foreach ($currency_code_options as $code => $name) {
			$currency_code_options[$code] = $name . ' (' . get_food_manager_currency_symbol($code) . ')';
		}

		$this->settings = apply_filters(
			'food_manager_settings',
			array(
				'general_settings' => array(
					__('General', 'wp-food-manager'),
					array(
						array(
							'name'       => 'food_manager_enable_categories',
							'std'        => '1',
							'label'      => __('Enable Food Categories', 'wp-food-manager'),
							'cb_label'   => __('Display categories on food.', 'wp-food-manager'),
							'desc'     => __('If enabled, the Food Categories option will display and manage from the both side at frontend and backend. ', 'wp-food-manager'),
							'type'       => 'checkbox',
							'attributes' => array(),
						),
						array(
							'name'       => 'food_manager_enable_food_types',
							'std'        => '1',
							'label'      => __('Enable Food Types', 'wp-food-manager'),
							'cb_label'   => __('Display types on food.', 'wp-food-manager'),
							'desc'       => 'If enabled, the Food Types option will display and manage from the both side at frontend and backend. ',
							'type'       => 'checkbox',
							'attributes' => array(),
						),
						array(
							'name'       => 'food_manager_enable_food_tags',
							'std'        => '1',
							'label'      => __('Enable Food Tags', 'wp-food-manager'),
							'cb_label'   => __('Display tags on food.', 'wp-food-manager'),
							'desc'       => 'If enabled, the Food Tags option will display and manage from the both side at frontend and backend. ',
							'type'       => 'checkbox',
							'attributes' => array(),
						),
						array(
							'name'       => 'food_manager_enable_field_editor',
							'std'        => '1',
							'label'      => __('Enable Field Editor', 'wp-food-manager'),
							'cb_label'   => __('Display Field editor.', 'wp-food-manager'),
							'desc'       => 'If enabled, the Field editor option will display at frontend and manage from the backend. ',
							'type'       => 'checkbox',
							'attributes' => array(),
						),
						array(
							'name'       => 'food_manager_delete_data_on_uninstall',
							'std'        => '0',
							'label'      => __('Delete Data On Uninstall', 'wp-food-manager'),
							'cb_label'   => __('Delete WP Food Manager data when the plugin is deleted. Once removed, this data cannot be restored.', 'wp-food-manager'),
							'desc'       => '',
							'type'       => 'checkbox',
							'attributes' => array(),
						),
						array(
							'name'       => 'food_manager_food_item_show_hide',
							'label'      => __('Hide Food Items', 'wp-food-manager'),
							'type'       => 'radio',
							'desc'       => '',
							'std'        => '1',
							'options'  => array(
								'0' => 'Completely hide Food Item which has stock status out of stock from the food listing page.',
								'1' => 'Display Out of Stock label for the food item which has stock status out of stock.'
							)
						),
					)
				),
				'food_listings'       => array(

					__('Food Listings', 'wp-food-manager'),

					array(

						array(

							'name'        => 'food_manager_per_page',

							'std'         => '10',

							'placeholder' => '',

							'label'       => __('Listings Per Page', 'wp-food-manager'),

							'desc'        => __('How many listings should be shown per page by default?', 'wp-food-manager'),

							'type'        => 'number',

							'custom_attributes'  => array(
								'min'  => '',
								'step' => '',
							),

						),

						array(

							'name'       => 'food_manager_enable_default_category_multiselect',

							'std'        => '0',

							'label'      => __('Multi-select Food Categories', 'wp-food-manager'),

							'cb_label'   => __('Enable food category multiselect by default.', 'wp-food-manager'),

							'desc'       => __('If enabled, the category select box will default to a multi select on the [foods] shortcode.', 'wp-food-manager'),

							'type'       => 'checkbox',

							'attributes' => array(),
						),

						array(

							'name'       => 'food_manager_enable_default_food_type_multiselect',

							'std'        => '0',

							'label'      => __('Multi-select Food Types', 'wp-food-manager'),

							'cb_label'   => __('Enable food type multiselect by default.', 'wp-food-manager'),

							'desc'       => __('If enabled, the food type select box will default to a multi select on the [foods] shortcode.', 'wp-food-manager'),

							'type'       => 'checkbox',

							'attributes' => array(),
						),
					),
				),
				'food_submission'     => array(

					__('Food Submission', 'wp-food-manager'),

					array(

						array(

							'name'       => 'food_manager_user_requires_account',

							'std'        => '1',

							'label'      => __('Account Required', 'wp-food-manager'),

							'cb_label'   => __('Submitting listings requires an account.', 'wp-food-manager'),

							'desc'       => __('If disabled, non-logged in users will be able to submit listings without creating an account.', 'wp-food-manager'),

							'type'       => 'checkbox',

							'attributes' => array(),
						),

						array(

							'name'       => 'food_manager_enable_registration',

							'std'        => '1',

							'label'      => __('Account Creation', 'wp-food-manager'),

							'cb_label'   => __('Allow account creation.', 'wp-food-manager'),

							'desc'       => __('If enabled, non-logged in users will be able to create an account by entering their email address on the submission form.', 'wp-food-manager'),

							'type'       => 'checkbox',

							'attributes' => array(),
						),

						array(

							'name'       => 'food_manager_generate_username_from_email',

							'std'        => '1',

							'label'      => __('Account Username', 'wp-food-manager'),

							'cb_label'   => __('Automatically Generate Username from Email Address.', 'wp-food-manager'),

							'desc'       => __('If enabled, a username will be generated from the first part of the user email address. Otherwise, a username field will be shown.', 'wp-food-manager'),

							'type'       => 'checkbox',

							'attributes' => array(),
						),
						array(
							'name'       => 'food_manager_use_standard_password_setup_email',
							'std'        => '1',
							'label'      => __('Account Password', 'wp-food-manager'),
							'cb_label'   => __('Use WordPress\' default behavior and email new users link to set a password.', 'wp-food-manager'),
							'desc'       => __('If enabled, an email will be sent to the user with their username and a link to set their password. Otherwise, a password field will be shown and their email address won\'t be verified.', 'wp-food-manager'),
							'type'       => 'checkbox',
							'attributes' => array(),
						),

						array(

							'name'    => 'food_manager_registration_role',

							'std'     => 'food_owner',

							'label'   => __('Account Role', 'wp-food-manager'),

							'desc'    => __('If you enable user registration on your submission form, choose a role for the new user.', 'wp-food-manager'),

							'type'    => 'select',

							'options' => $account_roles,
						),

						array(

							'name'       => 'food_manager_submission_requires_approval',

							'std'        => '1',

							'label'      => __('Moderate New Listings', 'wp-food-manager'),

							'cb_label'   => __('New listing submissions require admin approval.', 'wp-food-manager'),

							'desc'       => __('If enabled, new submissions will be inactive, pending admin approval.', 'wp-food-manager'),

							'type'       => 'checkbox',

							'attributes' => array(),
						),

						array(
							'name'       => 'food_manager_user_can_add_multiple_banner',

							'std'        => '0',

							'label'      => __('Allow Multiple Banners', 'wp-food-manager'),

							'cb_label'   => __('User can submit multiple banner.', 'wp-food-manager'),

							'desc'       => __('If enabled, Multiple banner can add at frontend by user and backend side by admin.', 'wp-food-manager'),

							'type'       => 'checkbox',

							'attributes' => array(),
						),
					),
				),
				'food_pages' => array(

					__('Pages', 'wp-food-manager'),

					array(

						array(

							'name'  => 'food_manager_add_food_page_id',

							'std'   => '',

							'label' => __('Submit a Food', 'wp-food-manager'),

							'desc'  => __('Select the page where you have placed the [add_food] shortcode. This lets the plugin know where the form is located.', 'wp-food-manager'),

							'type'  => 'page',
						),

						array(

							'name'  => 'food_manager_food_dashboard_page_id',

							'std'   => '',

							'label' => __('Food Dashboard', 'wp-food-manager'),

							'desc'  => __('Select the page where you have placed the [food_dashboard] shortcode. This lets the plugin know where the dashboard is located.', 'wp-food-manager'),

							'type'  => 'page',
						),

						array(

							'name'  => 'food_manager_foods_page_id',

							'std'   => '',

							'label' => __('Foods', 'wp-food-manager'),

							'desc'  => __('Select the page where you have placed the [foods] shortcode. This lets the plugin know where the food listings page is located.', 'wp-food-manager'),

							'type'  => 'page',
						),
					),
				),
				'food_currency' => array(

					__('Currency', 'wp-food-manager'),

					array(

						array(
							'label'    => __('Currency', 'wp-food-manager'),
							'desc'     => __('This controls what currency prices are listed at in the catalog and which currency gateways will take payments in.', 'wp-food-manager'),
							'name'       => 'wpfm_currency',
							'std'  => 'USD',
							'type'     => 'select',
							'class'    => 'wc-enhanced-select',
							'options'  => $currency_code_options,
						),

						array(
							'label'    => __('Currency Position', 'wp-food-manager'),
							'desc'     => __('This controls the position of the currency symbol.', 'wp-food-manager'),
							'name'       => 'wpfm_currency_pos',
							'class'    => 'wc-enhanced-select',
							'std'  => 'left',
							'type'     => 'select',
							'options'  => array(
								'left'        => __('Left', 'wp-food-manager'),
								'right'       => __('Right', 'wp-food-manager'),
								'left_space'  => __('Left with space', 'wp-food-manager'),
								'right_space' => __('Right with space', 'wp-food-manager'),
							),
						),

						array(
							'label'    => __('Thousand Separator', 'wp-food-manager'),
							'desc'     => __('This sets the thousand separator of displayed prices.', 'wp-food-manager'),
							'name'       => 'wpfm_price_thousand_sep',
							'css'      => 'width:50px;',
							'std'  => ',',
							'type'     => 'text',
						),

						array(
							'label'    => __('Decimal Separator', 'wp-food-manager'),
							'desc'     => __('This sets the decimal separator of displayed prices.', 'wp-food-manager'),
							'name'       => 'wpfm_price_decimal_sep',
							'css'      => 'width:50px;',
							'std'  => '.',
							'type'     => 'text',
						),

						array(
							'label'             => __('Number of Decimals', 'wp-food-manager'),
							'desc'              => __('This sets the number of decimal points shown in displayed prices.', 'wp-food-manager'),
							'name'                => 'wpfm_price_num_decimals',
							'css'               => 'width:50px;',
							'std'           => '2',
							'type'              => 'number',
							'custom_attributes' => array(
								'min'  => 0,
								'step' => 1,
							),
						),

					),
				),
			)
		);
	}

	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */

	public function register_settings()
	{

		$this->init_settings();

		foreach ($this->settings as $section) {

			foreach ($section[1] as $option) {

				if (isset($option['std']))

					add_option($option['name'], $option['std']);

				register_setting($this->settings_group, $option['name']);
			}
		}
	}

	/**
	 * output function.
	 *
	 * @access public
	 * @return void
	 */

	public function output()
	{

		$this->init_settings();
?>

		<div class="wrap food-manager-settings-wrap">

			<h1 class="wp-heading-inline">
				<?php

				esc_attr_e('Settings', 'wp-food-manager');
				?>
			</h1>

			<form method="post" name="food-manager-settings-form" action="options.php">

				<?php settings_fields($this->settings_group); ?>

				<h2 class="nav-tab-wrapper">

					<?php

					foreach ($this->settings as $key => $section) {
						echo wp_kses_post('<a href="#settings-' . sanitize_title($key) . '" class="nav-tab">' . esc_html($section[0]) . '</a>');
					}
					?>
				</h2>

				<div class="admin-setting-left">

					<div class="white-background">

						<?php

						if (!empty($_GET['settings-updated'])) {

							flush_rewrite_rules();

							echo wp_kses_post('<div class="updated fade food-manager-updated"><p>' . __('Settings successfully saved', 'wp-food-manager') . '</p></div>');
						}

						foreach ($this->settings as $key => $section) {

							echo wp_kses_post('<div id="settings-' . sanitize_title($key) . '" class="settings_panel">');

							echo wp_kses_post('<table class="form-table">');

							foreach ($section[1] as $option) {

								$placeholder    = (!empty($option['placeholder'])) ? 'placeholder="' . $option['placeholder'] . '"' : '';

								$class          = !empty($option['class']) ? $option['class'] : '';

								$value          = get_option($option['name']);

								$option['type'] = !empty($option['type']) ? $option['type'] : '';

								$attributes     = array();

								if (!empty($option['attributes']) && is_array($option['attributes']))

									foreach ($option['attributes'] as $attribute_name => $attribute_value)

										$attributes[] = esc_attr($attribute_name) . '="' . esc_attr($attribute_value) . '"';

								echo wp_kses_post('<tr valign="top" class="' . $class . '"><th scope="row"><label for="setting-' . $option['name'] . '">' . $option['label'] . '</a></th><td>');

								switch ($option['type']) {

									case "checkbox":

						?><label><input id="setting-<?php echo $option['name']; ?>" name="<?php echo $option['name']; ?>" type="checkbox" value="1" <?php echo implode(' ', $attributes); ?> <?php checked('1', $value); ?> /> <?php echo $option['cb_label']; ?></label><?php

																																																																			if ($option['desc'])

																																																																				echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');

																																																																			break;

																																																																		case "textarea":

																																																																			?>
										<textarea id="setting-<?php echo $option['name']; ?>" class="large-text" cols="50" rows="3" name="<?php echo $option['name']; ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?>><?php echo esc_textarea($value); ?></textarea><?php

																																																																								if ($option['desc'])

																																																																									echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');

																																																																								break;

																																																																							case "select":

																																																																								?><select id="setting-<?php echo $option['name']; ?>" class="regular-text" name="<?php echo $option['name']; ?>" <?php echo implode(' ', $attributes); ?>><?php

																																																																																																															foreach ($option['options'] as $key => $name)

																																																																																																																echo printf('<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($name) . '</option>');

																																																																																																															?></select><?php

																																																																																																																		if ($option['desc']) {

																																																																																																																			echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');
																																																																																																																		}

																																																																																																																		break;
																																																																																																																	case "radio":
																																																																																																																		?><fieldset>
											<legend class="screen-reader-text">
												<span><?php echo esc_html($option['label']); ?></span>
											</legend>
											<?php

																																																																																																																		if ($option['desc']) {
																																																																																																																			echo wp_kses_post('<p class="description">' . $option['desc'] . '</p>');
																																																																																																																		}

																																																																																																																		foreach ($option['options'] as $key => $name)
																																																																																																																			echo '<label><input name="' . esc_attr($option['name']) . '" type="radio" value="' . esc_attr($key) . '" ' . checked($value, $key, false) . ' />' . esc_html($name) . '</label><br>';

											?>
										</fieldset>
									<?php

																																																																																																																		break;

																																																																																																																	case "page":

																																																																																																																		$args = array(

																																																																																																																			'name'             => $option['name'],

																																																																																																																			'id'               => $option['name'],

																																																																																																																			'sort_column'      => 'menu_order',

																																																																																																																			'sort_order'       => 'ASC',

																																																																																																																			'show_option_none' => __('--no page--', 'wp-food-manager'),

																																																																																																																			'echo'             => false,

																																																																																																																			'selected'         => absint($value)

																																																																																																																		);

																																																																																																																		echo str_replace(' id=', " data-placeholder='" . __('Select a page&hellip;', 'wp-food-manager') .  "' id=", wp_dropdown_pages($args));

																																																																																																																		if ($option['desc']) {

																																																																																																																			echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');
																																																																																																																		}

																																																																																																																		break;

																																																																																																																	case "password":

									?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="password" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php

																																																																																																																		if ($option['desc']) {

																																																																																																																			echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');
																																																																																																																		}

																																																																																																																		break;

																																																																																																																	case "":

																																																																																																																	case "input":

																																																																																																																	case "text":

																																																																				?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php

																																																																																																																														if ($option['desc']) {

																																																																																																																															echo ' <p class="description">' . $option['desc'] . '</p>';
																																																																																																																														}

																																																																																																																														break;

																																																																																																																													case 'number':
																																																																																																																														?>
										<input id="setting-<?php echo esc_attr($option['name']); ?>" class="regular-text" type="number" min="<?php echo esc_attr($option['custom_attributes']['min']); ?>" step="<?php echo esc_attr($option['custom_attributes']['step']); ?>" name="<?php echo esc_attr($option['name']); ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo esc_attr($placeholder); ?> />
								<?php

																																																																																																																														if ($option['desc']) {

																																																																																																																															echo wp_kses_post(' <p class="description">' . sprintf(__('%s', 'wp-food-manager'), $option['desc']) . '</p>');
																																																																																																																														}

																																																																																																																														break;

																																																																																																																													case "multi-select-checkbox":
																																																																																																																														$this->create_multi_select_checkbox($option);
																																																																																																																														break;

																																																																																																																													default:

																																																																																																																														do_action('wp_food_manager_admin_field_' . $option['type'], $option, $attributes, $value, $placeholder);

																																																																																																																														break;
																																																																																																																												} ?>
								</td>
								</tr>
							<?php } ?>
							</table>
					</div>
				<?php } ?>
				</div> <!-- .white-background- -->
				<p class="submit">
					<input type="submit" class="button-primary" id="save-changes" value="<?php _e('Save Changes', 'wp-food-manager'); ?>" />
				</p>
		</div> <!-- .admin-setting-left -->
		</form>
		<div id="plugin_info" class="box-info">

			<h3><span><?php esc_attr_e('Helpful Resources', 'wp-food-manager'); ?></span></h3>

			<div class="wpfm-plugin_info-inside">
				<div class="wpfm-setup-help-center-block-wrap">
					<div class="wpfm-setup-help-center-block">
						<div class="wpfm-setup-help-center-block-icon">
							<span class="wpfm-setup-help-center-knowledge-base-icon"></span>
						</div>
						<div class="wpfm-setup-help-center-block-content">
							<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('Knowledge Base', 'wp-food-manager'); ?></div>
							<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Solve your queries by browsing our documentation.', 'wp-food-manager'); ?></div>
							<a href="http://wpfoodmanager.com/knowledge-base" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Browse More', 'wp-food-manager'); ?> »</span></a>
						</div>
					</div>
					<div class="wpfm-setup-help-center-block">
						<div class="wpfm-setup-help-center-block-icon">
							<span class="wpfm-setup-help-center-faqs-icon"></span>
						</div>
						<div class="wpfm-setup-help-center-block-content">
							<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('FAQs', 'wp-food-manager'); ?></div>
							<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Explore through the frequently asked questions.', 'wp-food-manager'); ?></div>
							<a href="http://wpfoodmanager.com/faqs" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Get Answers', 'wp-food-manager'); ?> »</span></a>
						</div>
					</div>
					<div class="wpfm-setup-help-center-block">
						<div class="wpfm-setup-help-center-block-icon">
							<span class="wpfm-setup-help-center-video-tutorial-icon"></span>
						</div>
						<div class="wpfm-setup-help-center-block-content">
							<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('Video Tutorials', 'wp-food-manager'); ?></div>
							<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Learn different skills by examining attractive video tutorials.', 'wp-food-manager'); ?></div>
							<a href="#" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Watch all', 'wp-food-manager'); ?> »</span></a>
						</div>
					</div>
				</div>
				<span class="light-grey"><?php esc_attr_e('Powered By', 'wp-food-manager'); ?></span> <a href="http://wpfoodmanager.com/" target="_blank"><img src="<?php echo WPFM_PLUGIN_URL . '/assets/images/wpfm-logo.svg'; ?>" alt="WP Food Manager"></a>
			</div>
		</div>
		</div>
<?php wp_enqueue_script('wp-food-manager-admin-settings');
	}

	/**
	 * Creates Multiselect checkbox.
	 * This function generate multiselect 
	 * @param $value
	 * @return void
	 */
	public function create_multi_select_checkbox($value)
	{

		echo '<ul class="mnt-checklist" id="' . $value['name'] . '" >' . "\n";
		foreach ($value['options'] as $option_value => $option_list) {
			$checked = " ";
			if (get_option($value['name'])) {

				$all_country = get_option($value['name']);
				$start_string = strpos($option_list['name'], '[');
				$country_code = substr($option_list['name'], $start_string + 1,  2);
				$coutry_exist = array_key_exists($country_code, $all_country);
				if ($coutry_exist) {
					$checked = " checked='checked' ";
				}
			}
			echo "<li>\n";

			echo '<input id="setting-' . $option_list['name'] . '" name="' . $option_list['name'] . '" type="checkbox" ' . $checked . '/>' . $option_list['cb_label'] . "\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
}
