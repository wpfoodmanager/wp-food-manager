<?php

/**
 * From admin panel, setuping post food page, food dashboard page and food listings page.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WPFM_Setup class.
 */
class WPFM_Import{

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
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action('admin_init',  array($this, 'wpfm_export_menu_csv'));	
        add_action('restrict_manage_posts', array($this, 'export_menu_button'), 10, 2);  // Add to the table's 'alignleft actions'

    }

    public function export_menu_button() {
        if (isset($_GET['post_type']) && $_GET['post_type'] == 'food_manager_menu') {
            echo '<div class="alignleft actions">
                    <a href="' . esc_url(add_query_arg('wpfm_export_menu_csv', '1')) . '" class="button" style="margin-left: 10px;">' . esc_html__('Export to CSV', 'wp-food-manager') . '</a>
                  </div>';
        }
    }
    /**
     * Output Setup page.
     * 
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function output() {
        global $wpdb;
        wp_enqueue_media();

        if (!empty($_POST['wp_food_manager_upload']) && wp_verify_nonce($_POST['_wpnonce'], 'food_manager_file_upload')) {
            if ($_POST['action'] == 'upload' && $_POST['file_id'] != '') {
                $file = get_attached_file(absint($_POST['file_id']));
                $file_data = get_food_file_data($_POST['file_type'], $file);
                $file_head_fields = array_shift($file_data);
                $food_import_fields = get_food_form_field_lists(sanitize_text_field($_POST['food_post_type']));
                $taxonomies = get_object_taxonomies(sanitize_text_field($_POST['food_post_type']), 'objects');
                $food_post_type = get_food_post_type();
                $import_type_label = $food_post_type[sanitize_text_field($_POST['food_post_type'])];
                get_food_manager_template(
                    'food-mapping-form.php',
                    array(
                        'file_id' => sanitize_text_field($_POST['file_id']),
                        'file_type' => sanitize_text_field($_POST['file_type']),
                        'file_head_fields' => $file_head_fields,
                        'food_import_fields' => $food_import_fields,
                        'import_type_label' => $import_type_label,
                        'food_post_type' => sanitize_text_field($_POST['food_post_type']),
                        'taxonomies' => $taxonomies,
                    ),
                    'wp-food-manager',
                    WPFM_PLUGIN_DIR . '/admin/templates/'
                );
            }
        }else if (!empty($_POST['wp_food_manager_mapping']) && wp_verify_nonce($_POST['_wpnonce'], 'food_manager_mapping')) {
            $import_fields = [];
            if (!empty($_POST['food_import_field']) && is_array($_POST['food_import_field'])) {
                foreach ($_POST['food_import_field'] as $key => $field) {
                    if ($field != '') {
                        if ($field == 'custom_field') {
                            $field = sanitize_text_field($_POST['custom_field'][$key]);
                        }
                        $file_field = [];
                        $file_field['key'] = $key;
                        $file_field['file_field'] = sanitize_text_field($_POST['file_field'][$key]);
                        $file_field['taxonomy'] = sanitize_text_field($_POST['taxonomy_field'][$key]);
                        $file_field['default_value'] = sanitize_text_field($_POST['default_value'][$key]);
                        $import_fields[$field] = $file_field;
                    }
                }
            }

            update_option('wpfm_food_import_fields', $import_fields);
            if ($_POST['action'] == 'mapping' && $_POST['file_id'] != '') {
                $file = get_attached_file($_POST['file_id']);
                $file_data = get_food_file_data(sanitize_text_field($_POST['file_type']), $file);
                $file_head_fields = array_shift($file_data);
                $file_sample_data = $file_data[0];
                $sample_data = [];
                foreach ($import_fields as $field_name => $field_data) {
                    $value = !empty($file_sample_data[$field_data['key']]) ? $file_sample_data[$field_data['key']] : $field_data['default_value'];
                    $sample_data[$field_name] = $value;
                }

                get_food_manager_template(
                    'food-import.php',
                    array(
                        'file_id' => sanitize_text_field($_POST['file_id']),
                        'file_type' => sanitize_text_field($_POST['file_type']),
                        'import_fields' => $import_fields,
                        'food_post_type' => sanitize_text_field($_POST['food_post_type']),
                        'sample_data' => $sample_data,
                    ),
                    'wp-food-manager',
                    WPFM_PLUGIN_DIR . '/admin/templates/'
                );
            }
        }  else if (!empty($_POST['wp_food_manager_import']) && wp_verify_nonce($_POST['_wpnonce'], 'food_manager_import')) {
            if ($_POST['action'] == 'import' && $_POST['file_id'] != '') {
                $import_fields = get_option('wpfm_food_import_fields', true);

                $file = get_attached_file(sanitize_text_field($_POST['file_id']));
                $file_data = get_food_file_data(sanitize_text_field($_POST['file_type']), $file);
                $file_head_fields = array_shift($file_data);
                if (!empty($file_data)) {
                    for ($i = 0; $i < count($file_data); $i++) {
                        $import_data = [];
                        foreach ($import_fields as $field_name => $field_date) {
                            if(array_key_exists($field_date['key'],$file_data[$i])){
                                $import_data[$field_name] = $file_data[$i][$field_date['key']];
                            }
                        }
                        import_data(sanitize_text_field($_POST['food_post_type']), $import_data);
                    }
                }

                $food_post_type = get_food_post_type();
                $import_type_label = $food_post_type[sanitize_text_field($_POST['food_post_type'])];
                get_food_manager_template(
                    'food-import-success.php',
                    array(
                        'total_records' => count($file_data),
                        'import_type_label' => $import_type_label,
                    ),
                    'wp-food-manager',
                    WPFM_PLUGIN_DIR . '/admin/templates/'
                );
            }
        } else {
            $food_post_type = get_food_post_type();
            get_food_manager_template(
                'food-file-upload.php',
                array(
                    'food_post_type' => $food_post_type,
                ),
                'wp-food-manager',
                WPFM_PLUGIN_DIR . '/admin/templates/'
            );
        }
    }

    /**
     * Export filtered posts as a CSV file.
     * 
     * The CSV file is generated and downloaded directly via the browser.
     * 
     * @access public
     * @return void
     */
    public function wpfm_export_menu_csv($msg) {
        if (isset($_GET['wpfm_export_menu_csv'])) { // phpcs:ignore
            // Check user capabilities
            if (!current_user_can('manage_options')) {
                return;
            }
            wpfm_export_menu_csv_file('food-manager-menu');
            exit;
        }
    }
}

WPFM_Import::instance();
