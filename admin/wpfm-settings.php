<?php
/*
* This file use for settings at admin site for wp food manager plugin.
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * WPFM_Settings class.
 * Class for the admin Settings.
 */
class WPFM_Settings {

    /**
     * Init settings_page.
     *
     * @since 1.0.1
     */
    public $settings_page;

    /**
     * The single instance of the class.
     *
     * @var self
     * @since 1.0.0
     */
    private static $_instance = null;

    /**
     * Init settings_group.
     *
     * @since 1.0.0
     */
    public $settings_group;

    /**
     * Init settings.
     *
     * @since 1.0.0
     */
    public $settings;

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
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->settings_group = 'food_manager';
        add_action('admin_init', array($this, 'register_settings'));
        
    }

    /**
     * Register the settings by loading the Class WPFM_Settings.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function register_settings() {
        $this->init_settings();
        foreach ($this->settings as $section) {
            foreach ($section[1] as $option) {
                if (isset($option['std']))
                    add_option(esc_attr($option['name']), $option['std']);
                register_setting($this->settings_group, esc_attr($option['name']));
            }
        }
    }
    
    /**
     * Set the init settings for the backend.
     *
     * @access protected
     * @return void
     * @since 1.0.0
     */
    public function init_settings() {
        // Prepare roles option.
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
                            'label'      => __('Food Categories', 'wp-food-manager'),
                            'cb_label'   => __('Display Food Categories.', 'wp-food-manager'),
                            'desc'     => __('If enabled, the Food Categories option will display and manage from the both side at frontend and backend. ', 'wp-food-manager'),
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'       => 'food_manager_enable_food_types',
                            'std'        => '1',
                            'label'      => __('Food Types', 'wp-food-manager'),
                            'cb_label'   => __('Display Food Types.', 'wp-food-manager'),
                            'desc'       => 'If enabled, the Food Types option will display and manage from the both side at frontend and backend. ',
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'       => 'food_manager_enable_food_tags',
                            'std'        => '1',
                            'label'      => __('Food Tags', 'wp-food-manager'),
                            'cb_label'   => __('Display Food Tags.', 'wp-food-manager'),
                            'desc'       => 'If enabled, the Food Tags option will display and manage from the both side at frontend and backend. ',
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'       => 'food_manager_enable_field_editor',
                            'std'        => '1',
                            'label'      => __('Field Editor', 'wp-food-manager'),
                            'cb_label'   => __('Display Field Editor.', 'wp-food-manager'),
                            'desc'       => 'If enabled, the Field Editor option will display at frontend and manage from the backend. ',
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'       => 'food_manager_food_item_show_hide',
                            'label'      => __('Food Items', 'wp-food-manager'),
                            'type'       => 'radio',
                            'desc'       => '',
                            'std'        => '1',
                            'options'  => array(
                                '0' => 'Completely hide Food Item which has stock status out of stock from the food listing page.',
                                '1' => 'Display Out of Stock label for the food item which has stock status out of stock.'
                            )
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
                    )
                ),
                'food_menu' => array(
                    __('Food Menu', 'wp-food-manager'),
                    array(
                        array(
                            'name'       => 'food_manager_enable_food_menu',
                            'std'        => '1',
                            'label'      => __('Food Menu', 'wp-food-manager'),
                            'cb_label'   => __('Display Food Menu.', 'wp-food-manager'),
                            'desc'       => 'If enabled, the Food Menu option will display at frontend and manage from the backend. ',
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'       => 'wpfm_enable_webshop_food_menu_icon',
                            'std'        => '1',
                            'label'      => __('Webshop Foodmenu Icon', 'wp-food-manager'),
                            'cb_label'   => __('Display Food Menu Icon on webshop.', 'wp-food-manager'),
                            'desc'       => 'If enabled, the Foodmenu Icon woll show in webshop foodmenu page. ',
                            'type'       => 'checkbox',
                        ),
                        array(
                            'name'       => 'wpfm_enable_mobileapp_food_menu_icon',
                            'std'        => '1',
                            'label'      => __('Mobile App Foodmenu Icon', 'wp-food-manager'),
                            'cb_label'   => __('Display Food Menu Icon on Mobileapp.', 'wp-food-manager'),
                            'desc'       => 'If enabled, the Foodmenu Icon woll show in Mobileapp foodmenu page. ',
                            'type'       => 'checkbox',
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
                            'cb_label'   => __('Enable Food Category multiselect by default.', 'wp-food-manager'),
                            'desc'       => __('If enabled, the Food Category select box will default to a multi select on the food listing page in filter section.', 'wp-food-manager'),
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'       => 'food_manager_enable_default_food_type_multiselect',
                            'std'        => '0',
                            'label'      => __('Multi-select Food Types', 'wp-food-manager'),
                            'cb_label'   => __('Enable Food Type multiselect by default.', 'wp-food-manager'),
                            'desc'       => __('If enabled, the Food Type select box will default to a multi select on the food listing page in filter section.', 'wp-food-manager'),
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'       => 'food_manager_enable_default_food_menu_multiselect',
                            'std'        => '0',
                            'label'      => __('Multi-select Food Menu', 'wp-food-manager'),
                            'cb_label'   => __('Enable Food Menu multiselect by default.', 'wp-food-manager'),
                            'desc'       => __('If enabled, the Food Menu select box will default to a multi select on the food listing page in filter section.', 'wp-food-manager'),
                            'type'       => 'checkbox',
                            'attributes' => array(),
                        ),
                        array(
                            'name'    => 'food_manager_enable_thumbnail',
                            'std'     => 'right_side',
                            'label'   => __('Food Thumbnail', 'wp-food-manager'),
                            'desc'    => __('Based on any option selection, it will reflect the thumbnail placement on food menu page.', 'wp-food-manager'),
                            'type'    => 'radio',
                            'options' => array(
                                'left' => __('Left Side.', 'wp-food-manager'),
                                'right' => __('Right Side.', 'wp-food-manager'),
                                'thumbnail_disabled' => __('Disable.', 'wp-food-manager')
                            ),
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
                            'std'     => 'fm_restaurant_owner',
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
                        array(
                            'name'  => 'food_manager_wpfm_food_menu_page_id',
                            'std'   => '',
                            'label' => __('Menu', 'wp-food-manager'),
                            'desc'  => __('Select the page where you have placed the [wpfm_food_menu] shortcode. This lets the plugin know where the food menu listings page is located.', 'wp-food-manager'),
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
                            'std'  => 'EUR',
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
     * Output the settings.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function output() {
        $this->init_settings(); 
        wp_enqueue_script('wp-food-manager-admin-settings');?>
        <div class="wrap food-manager-settings-wrap">
            <h1 class="wp-heading-inline">
                <?php esc_attr_e('Settings', 'wp-food-manager'); ?>
            </h1>
            <form method="post" name="food-manager-settings-form" action="options.php">
                <?php settings_fields($this->settings_group); ?>
                <h2 class="nav-tab-wrapper">
                    <?php foreach ($this->settings as $key => $section) {
                        echo wp_kses_post('<a href="#settings-' . sanitize_title($key) . '" class="nav-tab">' . esc_html($section[0]) . '</a>');
                    } ?>
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
                                    case "checkbox": ?>
                                        <label>
                                        <input id="setting-<?php echo esc_attr($option['name']); ?>" name="<?php echo esc_attr($option['name']); ?>" type="checkbox" value="1" <?php echo implode(' ', array_map('esc_attr', $attributes)); ?> <?php checked('1', $value); ?> /> <?php echo esc_html($option['cb_label']); ?>
                                        </label>
                                    <?php
                                        if ($option['desc'])
                                            echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');
                                        break;

                                    case "textarea": ?>
                                        <textarea id="setting-<?php echo esc_attr($option['name']); ?>" class="large-text" cols="50" rows="3" name="<?php echo esc_attr($option['name']); ?>" <?php echo implode(' ', array_map('esc_attr', $attributes)); ?> placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_textarea($value); ?></textarea>
                                    <?php
                                        if ($option['desc'])
                                            echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');
                                        break;

                                    case "select": ?>
                                        <select id="setting-<?php echo esc_attr($option['name']); ?>" class="regular-text" name="<?php echo esc_attr($option['name']); ?>" <?php echo implode(' ', array_map('esc_attr', $attributes)); ?>>
                                            <?php
                                            foreach ($option['options'] as $key => $name)
                                            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($name) . '</option>';
                                            ?>
                                        </select>
                                        <?php
                                        if ($option['desc']) {
                                            echo wp_kses_post(' <p class="description">' . $option['desc'] . '</p>');
                                        }
                                        break;

                                    case "radio": ?>
                                        <fieldset>
                                            <legend class="screen-reader-text">
                                                <span><?php echo esc_html($option['label']); ?></span>
                                            </legend>
                                            <?php
                                            foreach ($option['options'] as $key => $name){
                                                echo '<label><input name="' . esc_attr($option['name']) . '" type="radio" value="' . esc_attr($key) . '" ' . checked($value, $key, false) . ' />' . esc_html($name) . '</label><br>'; 
                                            }
                                            if ($option['desc']) {
                                                echo wp_kses_post('<p class="description">' . $option['desc'] . '</p>');
                                            } ?>
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

                                    case "password": ?>
                                        <?php // Escape attributes in the $attributes array
                                        $escaped_attributes = array_map('esc_attr', $attributes); ?>
                                        <input id="setting-<?php echo esc_attr($option['name']); ?>" class="regular-text" type="password" name="<?php echo esc_attr($option['name']); ?>" value="<?php echo esc_attr($value); ?>" <?php echo esc_attr($escaped_attributes); ?> placeholder="<?php echo esc_attr($placeholder); ?>" />
                                        <input id="setting-<?php echo esc_attr($option['name']); ?>-duplicate" class="regular-text" type="password" name="<?php echo esc_attr($option['name']); ?>" value="<?php echo esc_attr($value); ?>" <?php echo esc_attr($escaped_attributes); ?>  placeholder="<?php echo esc_attr($placeholder); ?>" /><?php
                                        if ($option['desc']) {
                                            echo wp_kses_post('<p class="description">' . esc_html($option['desc']) . '</p>'); // Escaping description
                                        }
                                        break;

                                    case "text": ?>
                                        <input id="setting-<?php echo esc_attr($option['name']); ?>" class="regular-text" type="text" name="<?php echo esc_attr($option['name']); ?>" value="<?php echo esc_attr($value); ?>" <?php echo implode(' ', array_map('esc_attr', $attributes)); ?> placeholder="<?php echo esc_attr($placeholder); ?>" />
                                        <?php
                                        if ($option['desc']) {
                                            echo '<p class="description">' . esc_html($option['desc']) . '</p>'; 
                                        }
                                        break;

                                    case 'number': ?>
                                        <input id="setting-<?php echo esc_attr($option['name']); ?>" class="regular-text" type="number" min="<?php echo esc_attr($option['custom_attributes']['min']); ?>" step="<?php echo esc_attr($option['custom_attributes']['step']); ?>" name="<?php echo esc_attr($option['name']); ?>" value="<?php echo esc_attr($value); ?>" <?php echo implode(' ', array_map('esc_attr', $attributes)); ?> placeholder="<?php echo esc_attr($placeholder); ?>" />
                                        <?php if ($option['desc']) {
                                            // translators: %s: description text for the option
                                            echo wp_kses_post('<p class="description">' . sprintf(__('%s', 'wp-food-manager'), $option['desc']) . '</p>');
                                        }
                                        break;

                                    case "multi-select-checkbox":
                                        $this->create_multi_select_checkbox($option);
                                        break;

                                    default:
                                        do_action('wpfm_admin_field_' . $option['type'], $option, $attributes, $value, $placeholder);
                                        break;
                                } ?>
                                </td>
                                </tr>
                            <?php } ?>
                            </table>
                    </div>
                <?php } ?>
                </div>
                <!-- .white-background- -->
                <p class="submit">
                    <input type="submit" class="button-primary" id="save-changes" value="<?php esc_html_e('Save Changes', 'wp-food-manager'); ?>" />
                </p>
        </div>
        <!-- .admin-setting-left -->
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
                            <a href="http://foodmato.com/knowledge-base" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Browse More', 'wp-food-manager'); ?> »</span></a>
                        </div>
                    </div>
                    <div class="wpfm-setup-help-center-block">
                        <div class="wpfm-setup-help-center-block-icon">
                            <span class="wpfm-setup-help-center-faqs-icon"></span>
                        </div>
                        <div class="wpfm-setup-help-center-block-content">
                            <div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('FAQs', 'wp-food-manager'); ?></div>
                            <div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Explore through the frequently asked questions.', 'wp-food-manager'); ?></div>
                            <a href="http://foodmato.com/faqs" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Get Answers', 'wp-food-manager'); ?> »</span></a>
                        </div>
                    </div>
                    <div class="wpfm-setup-help-center-block">
                        <div class="wpfm-setup-help-center-block-icon">
                            <span class="wpfm-setup-help-center-video-tutorial-icon"></span>
                        </div>
                        <div class="wpfm-setup-help-center-block-content">
                            <div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('Video Tutorials', 'wp-food-manager'); ?></div>
                            <div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Learn different skills by examining attractive video tutorials.', 'wp-food-manager'); ?></div>
                            <a href="https://www.youtube.com/channel/UC5j54ZQs7DLM8Dcvc2FwpPQ" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Watch all', 'wp-food-manager'); ?> »</span></a>
                        </div>
                    </div>
                </div>
                <span class="light-grey"><?php esc_html_e('Powered By', 'wp-food-manager'); ?></span><a href="http://foodmato.com/" target="_blank"><img src="<?php echo esc_url(WPFM_PLUGIN_URL . '/assets/images/foodmato-logo.svg'); ?>" alt="WP Food Manager"></a>
            </div>
        </div>
        </div>
<?php wp_enqueue_script('wp-food-manager-admin-settings');
    }

    /**
     * Creates Multiselect checkbox.
     * This function generate multiselect.
     * 
     * @access public
     * @param $value
     * @return void
     * @since 1.0.0
     */
    public function create_multi_select_checkbox($value) {
        echo '<ul class="mnt-checklist" id="' . esc_attr($value['name']) . '">' . "\n";
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
            echo '<input id="setting-' . esc_attr($option_list['name']) . '" name="' . esc_attr($option_list['name']) . '" type="checkbox" ' . esc_attr($checked) . '/>' . esc_html($option_list['cb_label']) . "\n";
            echo "</li>\n";
        }
        
        echo "</ul>\n";
    }
}