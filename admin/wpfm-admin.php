<?php

/**
 * Main Admin functions class which responsible for the entire admin functionality and scripts loaded and files.
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * WPFM_Admin class.
 * Class for the admin handler.
 */
class WPFM_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Init settings_page.
	 *
	 * @since 1.0.0
	 */
	public $settings_page;
	
	/**
     * Init post_types.
     *
     * @since 1.0.1
     */
    public $post_types;

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
	 * @since 1.0.0
	 */
	public function __construct() {
		include_once('wpfm-settings.php');
		include_once('wpfm-cpt.php');
		include_once('wpfm-writepanels.php');
		include_once('wpfm-setup.php');
		include_once('wpfm-import.php');
		include_once('wpfm-field-editor.php');
		include_once('wpfm-shortcode-list.php');
        $this->post_types = WPFM_Post_Types::instance();
		
		add_action('food_manager_type_add_form_fields', array($this, 'add_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('created_food_manager_type', array($this, 'save_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('food_manager_type_edit_form_fields', array($this, 'update_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('edited_food_manager_type', array($this, 'updated_custom_taxonomy_image_for_food_type'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'custom_taxonomy_load_media_for_food_type'));
        add_action('admin_footer', array($this, 'add_custom_taxonomy_script_for_food_type'));
        add_action('manage_food_manager_type_custom_column', array($this, 'display_custom_taxonomy_image_column_value_for_food_type'), 10, 3);
        add_action('food_manager_category_add_form_fields', array($this, 'add_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('created_food_manager_category', array($this, 'save_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('food_manager_category_edit_form_fields', array($this, 'update_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('edited_food_manager_category', array($this, 'updated_custom_taxonomy_image_for_food_category'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'custom_taxonomy_load_media_for_food_category'));
        add_action('admin_footer', array($this, 'add_custom_taxonomy_script_for_food_category'));
        add_action('manage_food_manager_category_custom_column', array($this, 'display_custom_taxonomy_image_column_value_for_food_category'), 10, 3);
        add_action('after_switch_theme', array($this->post_types, 'register_post_types'), 11);
        add_action('after_switch_theme', 'flush_rewrite_rules', 15);
        add_action('admin_menu', array($this, 'admin_menu'), 12);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('current_screen', array($this, 'conditional_includes'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_init', array($this, 'redirect'));
        add_action('admin_notices', array($this, 'display_notice'));
        add_action('wp_ajax_wpfm-logo-update-menu-order', array($this, 'menuUpdateOrder'));
        add_action('init', array($this->post_types, 'register_post_types'), 0);
        if (get_option('food_manager_enable_categories')) {
            add_action('restrict_manage_posts', array($this, 'foods_by_category'));
        }
        if (get_option('food_manager_enable_food_types') && get_option('food_manager_enable_categories')) {
            add_action('restrict_manage_posts', array($this, 'foods_by_food_type'));
        }
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_page_food_manager_settings_link'));
        add_action('admin_init', array($this, 'init_user_roles'));
        add_action('admin_init',  array($this, 'wpfm_export_csv'));	
	}

	/**
	 * Ran on WP admin_init hook.
	 * 
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function admin_init() {
		if (!empty($_GET['food-manager-main-admin-dismiss'])) {
			update_option('food_manager_rating_showcase_admin_notices_dismiss', 1);
		}
	}
	
	/**
     * Add image field in 'food_manager_type' taxonomy page.
     * 
     * @access public
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    public function add_custom_taxonomy_image_for_food_type($taxonomy) { ?>
        <div class="form-field term-group">
            <label for="image_id" class="wpfm-food-type-tax-image"><?php esc_html_e('Image/Icon', 'taxt-domain'); ?></label>
            <input type="hidden" id="image_id" name="image_id" class="custom_media_url" value="">
            <div id="image_wrapper"></div>
            <p>
                <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php esc_html_e('Add Image', 'taxt-domain'); ?>">
                <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php esc_html_e('Remove Image', 'taxt-domain'); ?>">
            </p>
        </div>
    <?php
    }

    /**
     * Save the 'food_manager_type' taxonomy image field.
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @return void
     * @since 1.0.0
     */
    public function save_custom_taxonomy_image_for_food_type($term_id, $tt_id) {
        if (isset($_POST['image_id']) && '' !== $_POST['image_id']) {
            $image = wp_unslash($_POST['image_id']);
            add_term_meta($term_id, 'image_id', $image, true);
        }
    }

    /**
     * Add the image field in edit form page.
     * 
     * @access public
     * @param mixed $term
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    public function update_custom_taxonomy_image_for_food_type($term, $taxonomy) { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="image_id"><?php esc_html_e('Image', 'taxt-domain'); ?></label>
            </th>
            <td>
                <?php $image_id = get_term_meta($term->term_id, 'image_id', true); ?>
                <input type="hidden" id="image_id" name="image_id" value="<?php echo esc_attr($image_id); ?>">
                <div id="image_wrapper">
                    <?php if ($image_id) { ?>
                        <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                    <?php } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php esc_html_e('Add Image', 'taxt-domain'); ?>">
                    <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php esc_html_e('Remove Image', 'taxt-domain'); ?>">
                </p>
                </div>
            </td>
        </tr>
    <?php
    }

    /**
     * Update the 'food_manager_type' taxonomy image field.
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @since 1.0.0
     */
    public function updated_custom_taxonomy_image_for_food_type($term_id, $tt_id) {
        if (isset($_POST['image_id']) && '' !== $_POST['image_id']) {
            $image = wp_unslash($_POST['image_id']);
            update_term_meta($term_id, 'image_id', $image);
        } else {
            update_term_meta($term_id, 'image_id', '');
        }
    }

    /**
     * Enqueue the wp_media library.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function custom_taxonomy_load_media_for_food_type() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_type') {
            return;
        }
        wp_enqueue_media();
    }

    /**
     * Custom script.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function add_custom_taxonomy_script_for_food_type() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_type') {
            return;
        }
    ?>

        <script>
            jQuery(document).ready(function($) {
                function taxonomy_media_upload(button_class) {
                    var custom_media = true,
                        original_attachment = wp.media.editor.send.attachment;

                    $('body').on('click', button_class, function(e) {
                        var button_id = '#' + $(this).attr('id');
                        var send_attachment = wp.media.editor.send.attachment;
                        var button = $(button_id);
                        custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            if (custom_media) {
                                $('#image_id').val(attachment.id);
                                $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#image_wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                            } else {
                                return original_attachment.apply(button_id, [props, attachment]);
                            }
                        }
                        wp.media.editor.open(button);
                        return false;
                    });
                }

                taxonomy_media_upload('.taxonomy_media_button.button');
                $('body').on('click', '.taxonomy_media_remove', function() {
                    $('#image_id').val('');
                    $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;display:none;" />');
                });

                $(document).ajaxComplete(function(event, xhr, settings) {
                    var queryStringArr = settings.data.split('&');
                    if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                        var xml = xhr.responseXML;
                        $response = $(xml).find('term_id').text();
                        if ($response != "") {
                            $('#image_wrapper').html('');
                        }
                    }
                });
            });
        </script>
    <?php
    }

    /**
     * Display new columns values.
     * 
     * @access public
     * @param string $columns
     * @param string $column
     * @param int $id
     * @return void
     * @since 1.0.0
     */
    public function display_custom_taxonomy_image_column_value_for_food_type($columns, $column, $id) {
        if ('category_image' == $column) {
            $image_id = esc_html(get_term_meta($id, 'image_id', true));
            $columns = wp_get_attachment_image($image_id, array('50', '50'));
        }
        return $columns;
    }

    /**
     * Add image field in 'food_manager_category' taxonomy page.
     * 
     * @access public
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    public function add_custom_taxonomy_image_for_food_category($taxonomy) {
    ?>
        <div class="form-field term-group">
            <label for="food_cat_image_id" class="wpfm-food-category-tax-image"><?php esc_html_e('Image/Icon', 'taxt-domain'); ?></label>
            <input type="hidden" id="food_cat_image_id" name="food_cat_image_id" class="custom_media_url" value="">
            <div id="image_wrapper"></div>
            <p>
                <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php esc_attr_e('Add Image', 'taxt-domain'); ?>">
                <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php esc_attr_e('Remove Image', 'taxt-domain'); ?>">
            </p>
        </div>
        
        <div class="form-field term-group">
            <label for="wpfm_disable_cat_visibility">Food Category Enable/Disable</label>
            <input type="radio" name="wpfm_disable_cat_visibility" value="yes" id="yes" /> Yes
            <input type="radio" name="wpfm_disable_cat_visibility" value="no" id="no" checked /> No
        </div>
    <?php
    }

    /**
     * Save the 'food_manager_category' taxonomy image field.
     * 
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @return void
     * @since 1.0.0
     */
    public function save_custom_taxonomy_image_for_food_category($term_id, $tt_id) {
        if (isset($_POST['food_cat_image_id']) && '' !== $_POST['food_cat_image_id']) {
            $image = wp_unslash($_POST['food_cat_image_id']);
            add_term_meta($term_id, 'food_cat_image_id', $image, true);
        }
        
        if (isset($_POST['wpfm_disable_cat_visibility'])) {
            add_term_meta($term_id, '_wpfm_disable_cat_visibility', sanitize_text_field($_POST['wpfm_disable_cat_visibility']), true);
        }
    }

    /**
     * Add the image field in edit form page.
     * 
     * @access public
     * @param object $term
     * @param string $taxonomy
     * @return void
     * @since 1.0.0
     */
    public function update_custom_taxonomy_image_for_food_category($term, $taxonomy) { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="food_cat_image_id"><?php esc_html_e('Image', 'taxt-domain'); ?></label>
            </th>
            <td>
                <?php $food_cat_image_id = get_term_meta($term->term_id, 'food_cat_image_id', true); ?>
                <input type="hidden" id="food_cat_image_id" name="food_cat_image_id" value="<?php echo esc_attr($food_cat_image_id); ?>">
                <div id="image_wrapper">
                    <?php if ($food_cat_image_id) { ?>
                        <?php echo wp_get_attachment_image($food_cat_image_id, 'thumbnail'); ?>
                    <?php } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary taxonomy_media_button" id="taxonomy_media_button" name="taxonomy_media_button" value="<?php esc_attr_e('Add Image', 'taxt-domain'); ?>">
                    <input type="button" class="button button-secondary taxonomy_media_remove" id="taxonomy_media_remove" name="taxonomy_media_remove" value="<?php esc_attr_e('Remove Image', 'taxt-domain'); ?>">
                </p>
                </div>
            </td>
        </tr>
    <?php $value = get_term_meta($term->term_id, '_wpfm_disable_cat_visibility', true);
    $checked_option_1 = ($value === 'yes') ? 'checked' : '';
    $checked_option_2 = ($value === 'no' || !$value) ? 'checked' : '';
    ?>
         <tr class="form-field term-group-wrap">
             <th scope="row"><label for="wpfm_disable_cat_visibility">Hide Category</label></th>
             <td>
                 <input type="radio" name="wpfm_disable_cat_visibility" value="yes" id="yes" <?php echo $checked_option_1; ?> /> Yes<br />
                 <input type="radio" name="wpfm_disable_cat_visibility" value="no" id="no" <?php echo $checked_option_2; ?> /> No
             </td>
         </tr>
    <?php
    }

    /**
     * Update the 'food_manager_category' taxonomy image field.
     *
     * @access public
     * @param int $term_id
     * @param int $tt_id
     * @return void
     * @since 1.0.0
     */
    public function updated_custom_taxonomy_image_for_food_category($term_id, $tt_id) {
        if (isset($_POST['food_cat_image_id']) && '' !== $_POST['food_cat_image_id']) {
            $image = sanitize_text_field(wp_unslash($_POST['food_cat_image_id']));
            update_term_meta($term_id, 'food_cat_image_id', $image);
        } else {
            update_term_meta($term_id, 'food_cat_image_id', '');
        }
        
        if (isset($_POST['wpfm_disable_cat_visibility'])) {
            update_term_meta($term_id, '_wpfm_disable_cat_visibility', sanitize_text_field($_POST['wpfm_disable_cat_visibility']));
        }
    }

    /**
     * Enqueue the wp_media library.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function custom_taxonomy_load_media_for_food_category() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_category') {
            return;
        }
        wp_enqueue_media();
    }

    /**
     * Custom script.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function add_custom_taxonomy_script_for_food_category() {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'food_manager_category') {
            return;
        }
    ?>

        <script>
            jQuery(document).ready(function($) {
                function taxonomy_media_upload(button_class) {
                    var custom_media = true,
                        original_attachment = wp.media.editor.send.attachment;

                    $('body').on('click', button_class, function(e) {
                        var button_id = '#' + $(this).attr('id');
                        var send_attachment = wp.media.editor.send.attachment;
                        var button = $(button_id);
                        custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            if (custom_media) {
                                $('#food_cat_image_id').val(attachment.id);
                                $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#image_wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                            } else {
                                return original_attachment.apply(button_id, [props, attachment]);
                            }
                        }
                        wp.media.editor.open(button);
                        return false;
                    });
                }

                taxonomy_media_upload('.taxonomy_media_button.button');
                $('body').on('click', '.taxonomy_media_remove', function() {
                    $('#food_cat_image_id').val('');
                    $('#image_wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;display:none;" />');
                });

                $(document).ajaxComplete(function(event, xhr, settings) {
                    var queryStringArr = settings.data.split('&');
                    if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                        var xml = xhr.responseXML;
                        $response = $(xml).find('term_id').text();
                        if ($response != "") {
                            $('#image_wrapper').html('');
                        }
                    }
                });
            });
        </script>
    <?php
    }
    
    /**
     * Display new columns values.
     *
     * @access public
     * @param string $columns
     * @param string $column
     * @param int $id
     * @return $columns
     * @since 1.0.0
     */
    public function display_custom_taxonomy_image_column_value_for_food_category($columns, $column, $id) {
        if ('category_image' == $column) {
            $food_cat_image_id = esc_html(get_term_meta($id, 'food_cat_image_id', true));
            $columns = wp_get_attachment_image($food_cat_image_id, array('50', '50'));
        }
        return $columns;
    }
    
    /**
     * Add the menu in admin (backend).
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_menu() {
        $this->settings_page = WPFM_Settings::instance();
        if (get_option('food_manager_enable_field_editor', true)) {
            add_submenu_page('edit.php?post_type=food_manager', esc_html__('Field Editor', 'wp-food-manager'), esc_html__('Field Editor', 'wp-food-manager'), 'manage_options', 'food-manager-form-editor', array(WPFM_Field_Editor::instance(), 'output'));
        }
        add_submenu_page('edit.php?post_type=food_manager', esc_html__('Settings', 'wp-food-manager'), esc_html__('Settings', 'wp-food-manager'), 'manage_options', 'food-manager-settings', array($this->settings_page, 'output'));
        add_dashboard_page(esc_html__('Setup', 'wp-food-manager'), esc_html__('Setup', 'wp-food-manager'), 'manage_options', 'food_manager_setup', array(WPFM_Setup::instance(), 'output'));
		add_submenu_page('edit.php?post_type=food_manager', __('WPFM Shortcodes', 'wp-food-manager'), __('Shortcodes', 'wp-food-manager'), 'manage_options', 'food-manager-shortcodes', array($this, 'shortcodes_page'));
		add_submenu_page('edit.php?post_type=food_manager', __('WPFM Import', 'wp-food-manager'), __('Import', 'wp-food-manager'), 'manage_options', 'food-manager-import', array(WPFM_Import::instance(), 'output'));
        
    }
    
    /**
	 * Output shortcode page.
	 */
	public function shortcodes_page() {
		$shortcodes = new WP_Food_Manager_Shortcode_List();
		$shortcodes->shortcode_list();
	}

    /**
     * Enqueue the scripts in the admin.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_enqueue_scripts() {
        global $wp_scripts;
        $screen = get_current_screen();
        $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

        wp_enqueue_style('wpfm-backend-css', esc_url(WPFM_PLUGIN_URL) . '/assets/css/backend.css');
        wp_enqueue_style('jquery-ui-style', esc_url(WPFM_PLUGIN_URL) . '/assets/css/jquery-ui/jquery-ui.min.css', array(), $jquery_version);

        $units = get_terms([
            'taxonomy'   => 'food_manager_unit',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        $unitList = [];
        if (!empty($units)) {
            foreach ($units as $unit) {
                $unitList[$unit->term_id] = $unit->name;
            }
        }

        wp_register_script('wpfm-admin', esc_url(WPFM_PLUGIN_URL) . '/assets/js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), WPFM_VERSION, true);
        wp_register_script('wpfm-loader', esc_url(WPFM_PLUGIN_URL) . '/assets/js/loader.js', array('jquery'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-admin',
            'wpfm_admin',
            array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'security' => wp_create_nonce('wpfm-admin-security'),
                'start_of_week'                      => get_option('start_of_week'),
                'i18n_datepicker_format'             => WPFM_Date_Time::get_datepicker_format(),
            )
        );

        wp_localize_script(
            'wpfm-admin',
            'wpfm_var',
            [
                'units'   => $unitList,
            ]
        );

        wp_enqueue_script('wpfm-admin');
        wp_register_script('wp-food-manager-admin-settings', esc_url(WPFM_PLUGIN_URL) . '/assets/js/admin-settings.min.js', array('jquery'), WPFM_VERSION, true);
        wp_register_script('chosen', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
        wp_enqueue_script('chosen');
        wp_enqueue_style('chosen', esc_url(WPFM_PLUGIN_URL) . '/assets/css/chosen.min.css');
        wp_enqueue_style('wpfm-font-style', esc_url(WPFM_PLUGIN_URL) . '/assets/fonts/style.min.css');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_register_script('wpfm-accounting', esc_url(WPFM_PLUGIN_URL) . '/assets/js/accounting/accounting.min.js', array('jquery'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-accounting',
            'wpfm_accounting_params',
            array(
                'wpfm_sale_less_than_regular_error' => esc_html__('Please enter in a value less than the regular price.', 'woocommerce'),
            )
        );
        wp_enqueue_script('wpfm-accounting');
        // wp_enqueue_style('dashicons');

        // Register the JavaScript file for info tooltiop - Food Redirection Enable/Displabe.
        wp_register_script('admin-tooltip-script', plugin_dir_url(__FILE__) . 'assets/js/admin-tooltip.js', array('jquery'), WPFM_VERSION, true); 
        // Localize the script to pass dynamic data.
        wp_localize_script('admin-tooltip-script', 'wpfmTooltipData', array(
            'redirection' => array(
               'url'   => esc_url(plugin_dir_url(__FILE__) . '../assets/images/service-tooptip-icon.png'),
                'alt'   => esc_attr__('Info', 'wp-food-manager'),
                'title' => esc_attr__('If Food Redirection is enabled, it will not redirect to the food.', 'wp-food-manager')
            ),
            'image' => array(
                'url'   => esc_url(plugin_dir_url(__FILE__) . '../assets/images/service-tooptip-icon.png'),
                'alt'   => esc_attr__('Info', 'wp-food-manager'),
                'title' => esc_attr__('If Food Image is enabled, it will not be displayed for food.', 'wp-food-manager')
            ),
            'fm_menu' => array(
                'url'   => esc_url(plugin_dir_url(__FILE__) . '../assets/images/service-tooptip-icon.png'),
                'alt'   => esc_attr__('Info', 'wp-food-manager'),
                'title' => esc_html__('If static menu is enabled, A fixed menu with a predetermined list of food items that remains the same every day. Ideal for establishments offering a consistent menu. If dynamic menu is enabled, A menu that changes daily, offering a rotating selection of dishes based on the current day. Perfect for establishments with daily specials or rotating menus.', 'wp-food-manager')
            ),
        ));
        // File upload - vendor.
        if (apply_filters('wpfm_ajax_file_upload_enabled', true)) {
            wp_register_script('jquery-iframe-transport', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-fileupload/jquery.iframe-transport.min.js', array('jquery'), '1.8.3', true);
            wp_register_script('jquery-fileupload', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-fileupload/jquery.fileupload.min.js', array('jquery', 'jquery-iframe-transport', 'jquery-ui-widget'), '5.42.3', true);
            wp_register_script('wpfm-ajax-file-upload', esc_url(WPFM_PLUGIN_URL) . '/assets/js/ajax-file-upload.js', array('jquery', 'jquery-fileupload'), WPFM_VERSION, true);

            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'jpg'));
            $js_field_html_img = ob_get_clean();

            ob_start();
            get_food_manager_template('form-fields/uploaded-file-html.php', array('name' => '', 'value' => '', 'extension' => 'zip'));
            $js_field_html = ob_get_clean();
            wp_localize_script('wpfm-ajax-file-upload', 'wpfm_ajax_file_upload', array(
                'ajax_url'               => esc_url(admin_url('admin-ajax.php')),
                'js_field_html_img'      => esc_js(str_replace("\n", "", $js_field_html_img)),
                'js_field_html'          => esc_js(str_replace("\n", "", $js_field_html)),
                'i18n_invalid_file_type' => esc_html__('The file type you have mentioned is invalid.', 'wp-food-manager')
            ));
        }
        wp_enqueue_editor();
        wp_register_script('chosen', esc_url(WPFM_PLUGIN_URL) . '/assets/js/jquery-chosen/chosen.jquery.min.js', array('jquery'), '1.1.0', true);
        wp_register_script('wp-food-manager-form-field-editor', esc_url(WPFM_PLUGIN_URL) . '/assets/js/field-editor.min.js', array('jquery', 'jquery-ui-sortable', 'chosen'), WPFM_VERSION, true);
        wp_localize_script(
            'wp-food-manager-form-field-editor',
            'wpfm_form_editor',
            array(
                'cofirm_delete_i18n'                    => esc_html__('Are you sure you want to delete this row?', 'wp-food-manager'),
                'cofirm_reset_i18n'                     => esc_html__('Are you sure you want to reset your changes? This cannot be undone.', 'wp-food-manager'),
                'ajax_url'                              => esc_url(admin_url('admin-ajax.php')),
                'wpfm_form_editor_security' => wp_create_nonce('_nonce_wpfm_form_editor_security'),
            )
        );
        wp_register_style('food_manager_setup_css', esc_url(WPFM_PLUGIN_URL) . '/assets/css/setup.min.css', array());
        wp_register_script('wpfm-term-autocomplete', esc_url(WPFM_PLUGIN_URL) . '/assets/js/term-autocomplete.min.js', array('jquery', 'jquery-ui-autocomplete'), WPFM_VERSION, true);
        wp_localize_script(
            'wpfm-term-autocomplete',
            'wpfm_term_autocomplete',
            array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'security' => wp_create_nonce('wpfm-autocomplete-security'),
            )
        );
    }

    /**
     * Include admin files conditionally.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function conditional_includes() {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }
        switch ($screen->id) {
            case 'options-permalink':
                $file_path = (WPFM_PLUGIN_DIR).'/admin/wpfm-permalink-settings.php';
                if (file_exists($file_path)) {
                require_once $file_path;
                } else {
                // Handle the case where the file doesn't exist.
               echo 'File not found: ' . esc_html($file_path);
                }
                break;
        }
        
    }

    /**
     * Add the pending count which food submitted from frontend and admin gets pending food count.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function admin_head() {

        /**
         * Add styles just for this page and remove dashboard page links.
         *
         * @since 1.0.0
         */
        remove_submenu_page('index.php', 'food_manager_setup');

        global $menu;
        $plural      = __('Food Manager', 'wp-food-manager');
        $count_foods = wp_count_posts('food_manager', 'readable');
        if (!empty($menu) && is_array($menu)) {
            foreach ($menu as $key => $menu_item) {
                if (strpos($menu_item[0], $plural) === 0) {
                    if ($order_count = $count_foods->pending) {
                        $menu[$key][0] .= " <span class='awaiting-mod update-plugins count-$order_count'><span class='pending-count'>" . number_format_i18n($count_foods->pending) . "</span></span>";
                    }
                    break;
                }
            }
        }
    }

    /**
     * Sends user to the setup page on first activation.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function redirect() {
        global $pagenow;

        if (isset($_GET['page']) && $_GET['page'] === 'food_manager_setup') {
            if (get_option('food_manager_installation', false)) {
                wp_redirect(admin_url('index.php'));
                exit;
            }
        }

        // Bail if no activation redirect transient is set.
        if (!get_transient('_food_manager_activation_redirect')) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        // Delete the redirect transient.
        delete_transient('_food_manager_activation_redirect');

        // Bail if activating from network, or bulk, or within an iFrame.
        if (is_network_admin() || isset($_GET['activate-multi']) || defined('IFRAME_REQUEST')) {
            return;
        }
        if ((isset($_GET['action']) && 'upgrade-plugin' == $_GET['action']) && (isset($_GET['plugin']) && strstr(wp_unslash($_GET['plugin']), 'wp-food-manager.php'))) {
            return;
        }
        wp_redirect(admin_url('index.php?page=food_manager_setup'));
        exit;
    }
    
    /**
     * Display notice in formatted HTML.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function display_notice() {
        $notice = get_transient('WPFM_Food_Notice');
        if (!empty($notice)) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html($notice) . '</p>';
            echo '</div>';
        }
    }
	
	/**
     * Category order update.
     *
     * @access public
     * @return void|bool
     * @since 1.0.0
     */
    public function menuUpdateOrder() {
        global $wpdb;

        $data = (!empty($_POST['post']) ? wp_unslash($_POST['post']) : []);
        if (!is_array($data)) {
            return false;
        }

        $id_arr = [];
        foreach ($data as $position => $id) {
            $id_arr[] = $id;
        }

        $menu_order_arr = [];
        foreach ($id_arr as $key => $id) {
            $results = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($id));
            foreach ($results as $result) {
                $menu_order_arr[] = $result->menu_order;
            }
        }

        sort($menu_order_arr);
        array_unshift($data, "");
        unset($data[0]);

        foreach ($data as $key => $id) {
            $wpdb->update($wpdb->posts, ['menu_order' => $key], ['ID' => intval($id)]);
        }

        wp_send_json_success();
    }
    
    /**
     * Show Food type dropdown.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function foods_by_food_type() {
        global $typenow, $wp_query;
        if ($typenow != 'food_manager' || !taxonomy_exists('food_manager_type')) {
            return;
        }

        $food_dropdown = array();
        $food_dropdown['pad_counts']   = 1;
        $food_dropdown['hierarchical'] = 1;
        $food_dropdown['hide_empty']   = 0;
        $food_dropdown['show_count']   = 1;
        $food_dropdown['selected']     = (isset($wp_query->query['food_manager_type'])) ? $wp_query->query['food_manager_type'] : '';
        $food_dropdown['menu_order']   = false;
         $terms = get_terms('food_manager_type');

        $walker            = WPFM_Category_Walker::instance();

        if (!$terms) {
            return;
        }

        $output  = "<select name='food_manager_type' id='dropdown_food_manager_type'>";
        $output .= '<option value="" ' . selected(isset($_GET['food_manager_type']) ? wp_unslash($_GET['food_manager_type']) : '', '', false) . '>' . esc_html(__('Select Food Type', 'wp-food-manager')) . '</option>';;
        $output .= $walker->walk($terms, 0, $food_dropdown);
        $output .= '</select>';
        $output .= '<a href="' . esc_url(add_query_arg('wpfm_export_csv', '1')) . '" class="button" style="margin-left: 10px; float: right;">' . esc_html__('Export to CSV', 'wp-food-manager') . '</a>';
        printf('%s', $output);
    }

    /**
     * Show category dropdown.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function foods_by_category() {
        global $typenow, $wp_query;
        if ($typenow != 'food_manager' || !taxonomy_exists('food_manager_category')) {
            return;
        }

        include_once WPFM_PLUGIN_DIR . '/includes/wpfm-category-walker.php';

        $food_dropdown = array();
        $food_dropdown['pad_counts'] = 1;
        $food_dropdown['hierarchical'] = 1;
        $food_dropdown['hide_empty'] = 0;
        $food_dropdown['show_count'] = 1;
        $food_dropdown['selected'] = (isset($wp_query->query['food_manager_category'])) ? $wp_query->query['food_manager_category'] : '';
        $food_dropdown['menu_order'] = false;
        $terms = get_terms('food_manager_category');
        $walker = WPFM_Category_Walker::instance();

        if (!$terms) {
            return;
        }

        $output = "<select name='food_manager_category' id='dropdown_food_manager_category'>";
        $output .= '<option value="" ' . selected(isset($_GET['food_manager_category']) ? wp_unslash($_GET['food_manager_category']) : '', '', false) . '>' . esc_html(__('Select Food Category', 'wp-food-manager')) . '</option>';
        $output .= $walker->walk($terms, 0, $food_dropdown);
        $output .= '</select>';
        printf('%s', $output);
    }
    
    /**
     * Create link on plugin page for food manager plugin settings.
     * 
     * @access public
     * @param array $links
     * @return array
     * @since 1.0.0
     */
    public function add_plugin_page_food_manager_settings_link($links) {
        $links[] = '<a href="' .
            esc_url(admin_url('edit.php?post_type=food_manager&page=food-manager-settings')) .
            '">' . esc_html__('Settings', 'wp-food-manager') . '</a>';
        return $links;
    }
    
    public static function init_user_roles()
     {
         global $wp_roles;
     
         if (class_exists('WP_Roles') && !isset($wp_roles)) {
             $wp_roles = new WP_Roles();
         }
     
         if (is_object($wp_roles)) {
             // Update or add 'fm_' prefixed roles
             self::fm_update_or_add_role('restaurant_owner', 'fm_restaurant_owner', __('Restaurant Manager', 'wpfm-restaurant-manager'), array(
                 'read'         => true,
                 'edit_posts'   => false,
                 'delete_posts' => false
             ));
     
             $capabilities = self::get_core_capabilities();
             foreach ($capabilities as $cap_group) {
                 foreach ($cap_group as $cap) {
                     $wp_roles->add_cap('administrator', $cap);
                 }
             }
         }
     }
	
	/**
	 * Get the core capabilities.
	 * 
	 * @access private
	 * @return array
	 * @since 1.0.0
	 */
	private static function get_core_capabilities() {
		return array(
			'core' => array(
				'manage_food_managers'
			),
			'food_manager' => array(
				"edit_food_manager",
				"read_food_manager",
				"delete_food_manager",
				"edit_food_managers",
				"edit_others_food_managers",
				"publish_food_managers",
				"read_private_food_managers",
				"delete_food_managers",
				"delete_private_food_managers",
				"delete_published_food_managers",
				"delete_others_food_managers",
				"edit_private_food_managers",
				"edit_published_food_managers",
				"manage_food_manager_terms",
				"edit_food_manager_terms",
				"delete_food_manager_terms",
				"assign_food_manager_terms"
			)
		);
	}
	
	private static function fm_update_or_add_role($old_role_slug, $new_role_slug, $role_name, $capabilities)
     {
         // Check if the old role exists
         $old_role = get_role($old_role_slug);
         
         if ($old_role) {
             // Remove the old role if it exists
             remove_role($old_role_slug);
         }
         
         // Add the new role with 'fm_' prefix
         add_role($new_role_slug, $role_name, $capabilities);
     }

      /**
	 * Export filtered posts as a CSV file.
	 * 
	 * The CSV file is generated and downloaded directly via the browser.
	 * 
	 * @access public
	 * @return void
	 */
	public function wpfm_export_csv() {
		if (isset($_GET['wpfm_export_csv'])) { // phpcs:ignore
			// Check user capabilities
			if (!current_user_can('manage_options')) {
				return;
			}
			wpfm_export_csv_file('food-manager');
			exit;
		}
	}

}

WPFM_Admin::instance();
