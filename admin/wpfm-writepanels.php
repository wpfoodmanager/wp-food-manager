<?php

/**
 * This file use to cretae fields of wp food manager at admin side.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class WPFM_Writepanels {

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
		// Writepanel's Actions.
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_post'), 1, 2);
        add_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);
        // Food menu.
        add_action('wp_ajax_wpfm_get_food_listings_by_category_id', array($this, 'get_food_listing_by_category_id'));
        add_action('food_manager_save_food_manager_menu', array($this, 'wpfm_save_food_menu_data'), 20, 2);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg'), 10, 2);
        add_filter('wpfm_term_radio_checklist_taxonomy', array($this, 'wpfm_food_manager_taxonomy'), 10, 2);
        add_filter('wpfm_term_radio_checklist_post_type', array($this, 'wpfm_food_manager_post_type'));
	}

	/**
	 * Display the tabs which is used in edit or add food in backend.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 * @since 1.0.0
	 */
	public function food_manager_data($post) {
		global $post, $thepostid;
		$thepostid = $post->ID;
		wp_enqueue_script('wpfm-admin');
		wp_nonce_field('save_meta_data', 'food_manager_nonce');
		include('templates/food-data-tabs.php');
	}

	/**
	 * Display the food menu content.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 * @since 1.0.0
	 */
	public function food_manager_menu_data_icons($post) {
		global $post, $thepostid;
		$thepostid = $post->ID;

		wp_enqueue_script('wpfm-admin');
		wp_nonce_field('save_meta_data', 'food_manager_nonce'); ?>

		<div class="wpfm-admin-food-menu-container wpfm-flex-col wpfm-admin-postbox-meta-data">
			<div class="wpfm-admin-postbox-meta-data">
				<div class="wpfm-admin-menu-selection wpfm-admin-postbox-form-field">

					<?php food_manager_dropdown_selection(array(
						'multiple' => false, 'show_option_all' => __('All category', 'wp-food-manager'),
						'id' => 'wpfm-admin-food-selection',
						'taxonomy' => 'food_manager_category',
						'hide_empty' => false,
						'pad_counts' => true,
						'show_count' => true,
						'hierarchical' => false,
					)); ?>

				</div>
				<div class="wpfm-admin-menu-selection wpfm-admin-postbox-form-field">

					<?php food_manager_dropdown_selection(array(
						'multiple' => false, 'show_option_all' => __('All food types', 'wp-food-manager'),
						'id' => 'wpfm-admin-food-types-selection',
						'taxonomy' => 'food_manager_type',
						'hide_empty' => false,
						'pad_counts' => true,
						'show_count' => true,
						'hierarchical' => false,
						'name' => 'food_type',
					)); ?>

				</div>
			</div>
			<div class="wpfm-admin-food-menu-items">
				<?php $item_ids = get_post_meta($thepostid, '_food_item_ids', true); ?>
				<ul class="wpfm-food-menu menu menu-item-bar ">

					<?php if ($item_ids && is_array($item_ids)) { ?>
						<?php foreach ($item_ids as $key => $id) { ?>
							<li class="menu-item-handle" data-food-id="<?= $id; ?>">
								<div class="wpfm-admin-left-col">
									<span class="dashicons dashicons-menu"></span>
									<span class="item-title"><?php echo esc_html(get_the_title($id)); ?></span>
								</div>
								<div class="wpfm-admin-right-col">
									<a href="javascript:void(0);" class="wpfm-food-item-remove">
										<span class="dashicons dashicons-dismiss"></span>
									</a>
								</div>
								<input type="hidden" name="wpfm_food_listing_ids[]" value="<?= $id; ?>" />
							</li>
					<?php }
					} ?>

				</ul>
				<?php if ($item_ids && is_array($item_ids)) { ?>
					<span class="no-menu-item-handle" style="display: none;">There is no food available in the selected category.</span>
				<?php } else { ?>
					<span class="no-menu-item-handle">There is no food available in the selected category.</span>
				<?php } ?>
			</div>
		</div>
<?php
	}

	/**
	 * Display the food menu data.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 * @since 1.0.0
	 */
	public function food_manager_menu_data($post) {
		global $post, $thepostid;
		$thepostid = $post->ID;

		wp_enqueue_script('wpfm-admin');
		wp_nonce_field('save_meta_data', 'food_manager_nonce');

		$icon_arrs = wpfm_get_dashicons();
		$food_icon_arrs = wpfm_get_font_food_icons();

		echo '<div class="wpfm-parent-icons"><input type="text" id="wpfm_icon_search" name="wpfm_icon_search" placeholder="Icon Search"><span class="wpfm-searh-clear"><i class="fa fa-times"></i></span></div>';
		echo '<div class="no-radio-icons"><strong>No icons found!</strong></div>';
		echo "<div class='wpfm-food-icon-class'>";

		foreach ($icon_arrs as $key => $icon_arr) {
			$radio_checked = (get_post_meta($thepostid, 'wpfm_radio_icons', true) === $key) ? "checked" : "";
			echo '<div class="sub-font-icon"><input type="radio" id="' . $key . '" name="radio_icons" value="' . $key . '" ' . $radio_checked . '><label for="' . $key . '"><span class="wpfm-key-name">' . $key . '</span><i class="dashicons ' . $key . '"></i></label></div>';
		}

		foreach ($food_icon_arrs as $key => $icon_arr) {
			$radio_checked = (get_post_meta($thepostid, 'wpfm_radio_icons', true) === $key) ? "checked" : "";
			$key_name = str_replace("wpfm-menu-", "", $key);
			echo '<div class="sub-font-icon"><input type="radio" id="' . $key . '" name="radio_icons" value="' . $key . '" ' . $radio_checked . '><label for="' . $key . '"><span class="wpfm-key-name">' . $key_name . '</span>';

			if ($key_name == 'fast-cart') {
				echo '<span class="wpfm-menu wpfm-menu-fast-cart"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></span>';
			} elseif ($key_name == 'rice-bowl') {
				echo '<span class="wpfm-menu wpfm-menu-rice-bowl"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>';
			} else {
				echo '<span class="wpfm-menu ' . $key . '"></span>';
			}
			echo "</span></label></div>";
		}

		echo "</div>";
	}

	/**
	 * Return array of tabs to show.
	 *
	 * @access public
	 * @return array
	 * @since 1.0.0
	 */
	public function get_food_data_tabs() {
		$tabs = apply_filters(
			'wpfm_food_data_tabs',
			array(
				'general'        => array(
					'label'    => __('General', 'wp-food-manager'),
					'target'   => 'general_food_data_content',
					'class'    => array(''),
					'priority' => 1,
				),
				'toppings'        => array(
					'label'    => __('Toppings', 'wp-food-manager'),
					'target'   => 'toppings_food_data_content',
					'class'    => array(),
					'priority' => 2,
				),
				'ingredients'        => array(
					'label'    => __('Ingredients', 'wp-food-manager'),
					'target'   => 'ingredient_food_data_content',
					'class'    => array(''),
					'priority' => 3,
				),
				'nutritions'        => array(
					'label'    => __('Nutritions', 'wp-food-manager'),
					'target'   => 'nutritions_food_data_content',
					'class'    => array(''),
					'priority' => 4,
				),
				'advanced'        => array(
					'label'    => __('Advanced', 'wp-food-manager'),
					'target'   => 'advanced_food_data_content',
					'class'    => array(''),
					'priority' => 5,
				),
			)
		);

		// Sort tabs based on priority.
		uasort($tabs, array($this, 'sort_by_priority'));
		return $tabs;
	}

	/**
	 * Returns the fields with filtered fields.
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function food_manager_data_fields() {
		global $post;
		$current_user = wp_get_current_user();
		$fields =  $GLOBALS['food_manager']->forms->get_form_fields('add-food', 'backend');
		$fields = apply_filters('food_manager_food_data_fields', $fields);
		if (isset($fields['food']['food_title']))
			unset($fields['food']['food_title']);
		if (isset($fields['food']['food_description']))
			unset($fields['food']['food_description']);
		uasort($fields, array($this, 'sort_by_priority'));
		return $fields;
	}

	/**
	 * Sort array by priority value.
	 * 
	 * @access public
	 * @param array $a
	 * @param array $b
	 * @return void
	 * @since 1.0.0
	 */
	protected function sort_by_priority($item1, $item2) {
		if (!isset($item1['priority']) || !isset($item2['priority']) || $item1['priority'] === $item2['priority']) {
			return 0;
		}
		return ($item1['priority'] < $item2['priority']) ? -1 : 1;
	}
	
	/**
     * Save post when food updated or submitted by the backend.
     *
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @return void
     * @since 1.0.0
     */
    public function save_post($post_id, $post) {
        global $wpdb;

        if (empty($post_id) || empty($post) || empty($_POST)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (is_int(wp_is_post_revision($post))) return;
        if (is_int(wp_is_post_autosave($post))) return;
        if (empty($_POST['food_manager_nonce']) || !wp_verify_nonce($_POST['food_manager_nonce'], 'save_meta_data')) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type == 'food_manager') {
            $writepanels = WPFM_Writepanels::instance();
            do_action('wpfm_save_food_data', $post_id, $post, $writepanels->food_manager_data_fields());

            // Set Order Menu.
            $order_menu = get_post_field('menu_order', $post_id);             
            if ($order_menu == '0') {
                $last_inserted_posts = new WP_Query( apply_filters('food_manager_get_menu_order',array(
                    'post_type' => $post->post_type,
                    'posts_per_page' => 2,
                    'offset' => 0,
                    'orderby' => 'ID',
                    'order' => 'DESC',
                    'post_status' => 'any',
                )));
                if ($last_inserted_posts->post_count > 1) {
                    $last_menu_order = get_post_field('menu_order', $last_inserted_posts->posts[1]->ID);
                    $next_menu_order = intval($last_menu_order) + 1;
                    $wpdb->update($wpdb->posts, ['menu_order' => $next_menu_order], ['ID' => intval($post_id)]);
                } else {
                    $wpdb->update($wpdb->posts, ['menu_order' => 1], ['ID' => intval($post_id)]);
                }
                // Restore the original post data
                wp_reset_postdata();
            }
        }
        if ($post->post_type == 'food_manager_menu') {
            do_action('food_manager_save_food_manager_menu', $post_id, $post);
        }
    }
    
    /**
     * Adding the meta boxes to the backend.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function add_meta_boxes() {
        global $wp_post_types;

        $screen = get_current_screen();
        $taxonomy_slug = 'food_manager_type';
        $taxonomy = get_taxonomy($taxonomy_slug);

        add_meta_box('food_manager_data', sprintf(__('%s Data', 'wp-food-manager'), $wp_post_types['food_manager']->labels->singular_name), array($this, 'food_manager_data'), 'food_manager', 'normal', 'high');
        add_meta_box('food_manager_menu_data', __('Menu Icon', 'wp-food-manager'), array($this, 'food_manager_menu_data'), 'food_manager_menu', 'normal', 'high');
        add_meta_box('food_manager_menu_data_icons', __('Select Food ', 'wp-food-manager'), array($this, 'food_manager_menu_data_icons'), 'food_manager_menu', 'normal', 'high');

        // Replace the food_manager_type taxonomy metabox for changing checkbox to radio button in backend.
        remove_meta_box('food_manager_typediv', 'food_manager', 'side');
        add_meta_box('radio-food_manager_typediv', (isset($taxonomy->labels->name) ? esc_html($taxonomy->labels->name) : ''), 'replace_food_manager_type_metabox', 'food_manager', 'side', 'core', array('taxonomy' => $taxonomy_slug));
        if ('add' != $screen->action) {
            // Show food menu Shortcode on edit menu page - admin.
            add_meta_box('wpfm_menu_shortcode', 'Shortcode', array($this, 'food_menu_shortcode'), 'food_manager_menu', 'side', 'low');
            add_meta_box('wpfm_menu_disable_redirection', 'Disable Food Redirection', array($this, 'food_menu_disable_food_redirection'), 'food_manager_menu', 'side', 'low');
        }
    }

    /**
     * Show menu shortcode in single edit menu.
     * 
     * @access public
     * @return void
     * @since 1.0.2
     */
    public function food_menu_shortcode() {
        global $post;
        $menu_id = $post->ID;
        echo '<input type="text" value="[food_menu id=' . esc_attr($menu_id) . ']" readonly>';
    }
    
    /**
     * This function is responsible for disabling any redirection related to the food.
     * 
     * @access public
     * @return void
     * @since 1.0.2
     */
    public function food_menu_disable_food_redirection() {
        global $post;
        $thepostid = $post->ID;
        $key = 'wpfm_disable_food_redirect';
        $field = array(
            'name' => 'wpfm_disable_food_redirect',
            'label' => __('Food Redirection Enable/Disable', 'wp-food-manager'),
            'type' => 'radio',
            'desc' => '',
            'std' => 'no',
            'options' => array(
                'no' => 'No',
                'yes' => 'Yes'
            ),
            'value' => get_post_meta($thepostid, '_wpfm_disable_food_redirect', true),
        );
        get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => esc_attr($key), 'field' => $field));
    }
    
    /**
     * Save the food data from backend side is handle by this function.
     *
     * @access public
     * @param mixed $post_id
     * @param mixed $post
     * @param array $form_fields
     * @return void
     * @since 1.0.0
     */
    public function food_manager_save_food_manager_data($post_id, $post, $form_fields) {
        global $wpdb;
        $thumbnail_image = array();
        // Save Food Form fields values.
        if (isset($form_fields['food'])) {
            foreach ($form_fields['food'] as $key => $field) {
                $type = !empty($field['type']) ? $field['type'] : '';

                // Food Banner.
                if ('food_banner' === $key) {
                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        $thumbnail_image = is_array($_POST[$key]) ? array_values(array_filter($_POST[$key])) : $_POST[$key];

                        // Update Food Banner Meta Data.
                        update_post_meta($post_id, '_' . esc_attr($key), $thumbnail_image);
                        if (is_array($_POST[$key])) {
                            $_POST[$key] = array_values(array_filter($_POST[$key]));
                        }
                    }

                    // Create Attachments ( If not exist ).
                    if (!is_admin()) {
                        $maybe_attach = array_filter((array)$thumbnail_image);

                        // Handle attachments.
                        if (sizeof($maybe_attach) && apply_filters('wpfm_attach_uploaded_files', true)) {

                            // Get attachments.
                            $attachments     = get_posts('post_parent=' . $post_id . '&post_type=attachment&fields=ids&numberposts=-1');
                            $attachment_urls = array();

                            // Loop attachments already attached to the food.
                            foreach ($attachments as $attachment_key => $attachment) {
                                $attachment_urls[] = wp_get_attachment_url($attachment);
                            }

                            foreach ($maybe_attach as $key => $attachment_url) {
                                if (!in_array($attachment_url, $attachment_urls) && !is_numeric($attachment_url)) {
                                    $WPFM_Add_Food_Form = WPFM_Add_Food_Form::instance();
                                    $attachment_id = $WPFM_Add_Food_Form->create_attachment($attachment_url);

                                    // Set first image of banner as a thumbnail.
                                       
                                    if ($key == 0) {
                                        set_post_thumbnail($post_id, $attachment_id);
                                    }
                                }
                            }
                        }
                    } else {
                        $image = get_the_post_thumbnail_url($post_id);

                        if (empty($image)) {
                            if (isset($thumbnail_image) && !empty($thumbnail_image)) {

                                $wp_upload_dir = wp_get_upload_dir();
                                $baseurl = $wp_upload_dir['baseurl'] . '/';
                                $wp_attached_file = str_replace($baseurl, '', $thumbnail_image);
                                $args = array(
                                    'meta_key'       => '_wp_attached_file',
                                    'meta_value'     => $wp_attached_file,
                                    'post_type'      => 'attachment',
                                    'posts_per_page' => 1,
                                );

                                $attachments = get_posts($args);
                                if (!empty($attachments)) {
                                    foreach ($attachments as $attachment) {
                                        set_post_thumbnail($post_id, $attachment->ID);
                                    }
                                }
                            }
                        }
                    }
                }

                // Other form field's value.
                switch ($type) {
                    case 'textarea':
                        if (isset($_POST[$key])) {
                            update_post_meta($post_id, '_' . esc_attr($key), wp_kses_post(stripslashes($_POST[$key])));
                        }
                        break;

                    case 'date':
                        if (isset($_POST[$key])) {
                            $date = $_POST[$key];
                            $datepicker_date_format = !empty(get_option('date_format')) ? get_option('date_format') : 'F j, Y';
                            $php_date_format = WPFM_Date_Time::get_view_date_format_from_datepicker_date_format($datepicker_date_format);
                            // Convert date and time value into DB formatted format and save eg. 1970-01-01.
                            $date_dbformatted = WPFM_Date_Time::date_parse_from_format($php_date_format, $date);
                            $date_dbformatted = !empty($date_dbformatted) ? $date_dbformatted : $date;
                            update_post_meta($post_id, '_' . esc_attr($key), $date_dbformatted);
                        }
                        break;

                    default:
                        if (!isset($_POST[$key])) {
                            update_post_meta($post_id, '_' . esc_attr($key), '');
                            continue 2;
                        } elseif (is_array($_POST[$key])) {
                            update_post_meta($post_id, '_' . esc_attr($key), array_filter(array_map('sanitize_text_field', $_POST[$key])));
                        } else {
                            update_post_meta($post_id, '_' . esc_attr($key), sanitize_text_field($_POST[$key]));
                        }
                        break;
                }
                $unit_ids = [];
                $ingredient_ids = [];
                $nutrition_ids = [];

                // Set Ingredients.
                if ($key = 'food_ingredients') {
                    $taxonomy = 'food_manager_ingredient';
                    $multi_array_ingredient = array();

                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        foreach ($_POST[$key] as $id => $ingredient) {
                            $term_name = esc_attr(get_term($id)->name);
                            $unit_name = "Unit";

                            if ($ingredient['unit_id'] == '' && empty($ingredient['unit_id'])) {
                                $unit_name = "Unit";
                            } else {
                                $unit_name = esc_attr(get_term($ingredient['unit_id'])->name);
                            }

                            $item = [
                                'id' => $id,
                                'unit_id' => !empty($ingredient['unit_id']) ? $ingredient['unit_id'] : null,
                                'value' => !empty($ingredient['value']) ? $ingredient['value'] : null,
                                'ingredient_term_name' => $term_name,
                                'unit_term_name' => $unit_name
                            ];

                            $multi_array_ingredient[$id] = $item;
                            $ingredient_ids[] = $id;
                            if (trim($ingredient['unit_id'])) {
                                $unit_ids[] = (int)$ingredient['unit_id'];
                            }
                        }
                        update_post_meta($post_id, '_' . esc_attr($key), $multi_array_ingredient);
                    } else {
                        update_post_meta($post_id, '_' . esc_attr($key), array());
                    }

                    $exist_ingredients = get_the_terms($post_id, $taxonomy);
                    if ($exist_ingredients) {
                        $removed_ingredient_ids = [];
                        foreach ($exist_ingredients as $ingredient) {
                            if (!in_array($ingredient->term_id, $ingredient_ids)) {
                                $removed_ingredient_ids[] = $ingredient->term_id;
                            }
                        }
                        wp_remove_object_terms($post_id, $removed_ingredient_ids, $taxonomy);
                    }

                    if (!empty($ingredient_ids)) {
                        wp_set_object_terms($post_id, $ingredient_ids, $taxonomy);
                    }
                }

                // Set Nutrition.
                if ($key = 'food_nutritions') {
                    $taxonomy = 'food_manager_nutrition';
                    $multi_array_nutrition = array();

                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        foreach ($_POST[$key] as $id => $nutrition) {
                            $term_name = esc_attr(get_term($id)->name);
                            $unit_name = "Unit";
                            if ($nutrition['unit_id'] == '' && empty($nutrition['unit_id'])) {
                                $unit_name = "Unit";
                            } else {
                                $unit_name = esc_attr(get_term($nutrition['unit_id'])->name);
                            }

                            $item = [
                                'id' => $id,
                                'unit_id' => !empty($nutrition['unit_id']) ? $nutrition['unit_id'] : null,
                                'value' => !empty($nutrition['value']) ? $nutrition['value'] : null,
                                'nutrition_term_name' => $term_name,
                                'unit_term_name' => $unit_name
                            ];

                            $multi_array_nutrition[$id] = $item;
                            $nutrition_ids[] = $id;
                            if (trim($nutrition['unit_id'])) {
                                $unit_ids[] = (int)$nutrition['unit_id'];
                            }
                        }
                        update_post_meta($post_id, '_' . esc_attr($key), $multi_array_nutrition);
                    } else {
                        update_post_meta($post_id, '_' . esc_attr($key), array());
                    }

                    $exist_nutritions = get_the_terms($post_id, 'food_manager_nutrition');
                    if ($exist_nutritions) {
                        $removed_nutrition_ids = [];
                        foreach ($exist_nutritions as $nutrition) {
                            if (!in_array($nutrition->term_id, $nutrition_ids)) {
                                $removed_nutrition_ids[] = $nutrition->term_id;
                            }
                        }
                        wp_remove_object_terms($post_id, $removed_nutrition_ids, 'food_manager_nutrition');
                    }
                    if (!empty($nutrition_ids)) {
                        wp_set_object_terms($post_id, $nutrition_ids, 'food_manager_nutrition');
                    }
                }

                $exist_units = get_the_terms($post_id, 'food_manager_unit');
                $taxonomy = 'food_manager_unit';
                if ($exist_units) {
                    $removed_unit_ids = [];

                    foreach ($exist_units as $unit) {
                        if (!in_array($unit->term_id, $unit_ids)) {
                            $removed_unit_ids[] = $unit->term_id;
                        }
                    }
                    wp_remove_object_terms($post_id, $removed_unit_ids, $taxonomy);
                }

                if ($unit_ids) {
                    wp_set_object_terms($post_id, $unit_ids, $taxonomy);
                }

                if (isset($field['taxonomy']) && !empty($field['taxonomy'])) {
                    if ($field['taxonomy'] != 'food_manager_ingredient' || $field['taxonomy'] != 'food_manager_nutrition') {
                        $terms = isset($field['value']) && !empty($field['value']) ? $field['value'] : '';
                        if ($field['taxonomy'] == 'food_manager_type') {
                            if (empty($terms)) {
                                $terms = isset($_POST['food_type']) && !empty($_POST['food_type']) ? $_POST['food_type'] : '';
                            }
                        }
                        if (is_array($terms)) {
                            $terms = array_map(function ($value) {
                                return (int)$value;
                            }, $terms);
                            wp_set_object_terms($post_id, $terms, $field['taxonomy'], false);
                        } else {
                            if (!empty($terms)) {
                                wp_set_object_terms($post_id, array((int)$terms), $field['taxonomy'], false);
                            }
                        }
                    }
                }

                // Food Tags.
                if ($key = 'food_tag') {
                    if (isset($_POST[$key]) && !empty($_POST[$key])) {
                        $food_tag = explode(',', $_POST[$key]);
                        $food_tag = array_map('trim', $food_tag);
                        wp_set_object_terms($post_id, $food_tag, 'food_manager_tag');
                    }
                }
            }
        }

        $toppings_arr = array();
        if (isset($form_fields['toppings'])) {
            $toppings_meta = array();
            $topping_cnt = 0;
            foreach ($form_fields['toppings'] as $key => $field) {
                $topping_cnt++;

                // Set toppings meta and assign them into food.
                $taxonomy = 'food_manager_topping';
                if (isset($_POST['repeated_options']) && !empty($_POST['repeated_options'])) {
                    foreach ($_POST['repeated_options'] as $count) {
                        $option_values = array();
                        if (isset($_POST['option_value_count'])) {
                            $find_option = array_search('__repeated-option-index__', $_POST['option_value_count']);
                            if ($find_option !== false) {

                                // Remove from array.
                                unset($_POST['option_value_count'][$find_option]);
                            }

                            foreach ($_POST['option_value_count'] as $option_key_count) {
                                if ($option_key_count && is_array($option_key_count)) {
                                    foreach ($option_key_count as $option_value_count) {
                                        if (!empty($_POST[$count . '_option_name_' . $option_value_count]) || !empty($_POST[$count . '_option_price_' . $option_value_count])) {
                                            $option_values[$option_value_count] = apply_filters('wpfm_topping_options_values_array', array(
                                                'option_name' => isset($_POST[$count . '_option_name_' . $option_value_count]) ? $_POST[$count . '_option_name_' . $option_value_count] : '',
                                                'option_price' => isset($_POST[$count . '_option_price_' . $option_value_count]) ? $_POST[$count . '_option_price_' . $option_value_count] : '',
                                            ), array('option_count' => $count, 'option_value_count' => $option_value_count));
                                        }
                                    }
                                } else {
                                    if (!empty($_POST[$count . '_option_name_' . $option_key_count]) || !empty($_POST[$count . '_option_price_' . $option_key_count])) {
                                        $option_values[$option_key_count] = apply_filters('wpfm_topping_options_values_array', array(
                                            'option_name' => isset($_POST[$count . '_option_name_' . $option_key_count]) ? $_POST[$count . '_option_name_' . $option_key_count] : '',
                                            'option_price' => isset($_POST[$count . '_option_price_' . $option_key_count]) ? $_POST[$count . '_option_price_' . $option_key_count] : '',
                                        ), array('option_count' => $count, 'option_value_count' => $option_key_count));
                                    }
                                }
                            }
                        }

                        if ($key == 'topping_name') {
                            $toppings_arr[] = isset($_POST[$key . '_' . $count]) ? esc_attr($_POST[$key . '_' . $count]) : '';
                        }

                        if ($key == 'topping_description') {
                            $toppings_meta[$count]['_' . $key] = isset($_POST[$key . '_' . $count]) && !empty($_POST[$key . '_' . $count]) ? wp_kses_post($_POST[$key . '_' . $count]) : '';
                        } else {
                            // Toppings Array.
                            $toppings_meta[$count]['_' . $key] = isset($_POST[$key . '_' . $count]) && !empty($_POST[$key . '_' . $count]) ? esc_attr($_POST[$key . '_' . $count]) : '';
                        }
                        if ($key == 'topping_options') {
                            $toppings_meta[$count]['_' . $key] = $option_values;
                        }
                    }
                }

                $exist_toppings = get_the_terms($post_id, $taxonomy);
                if ($exist_toppings) {
                    $removed_toppings_ids = [];
                    foreach ($exist_toppings as $toppings) {
                        if (!in_array($toppings->name, $toppings_arr)) {
                            $removed_toppings_ids[] = (int)$toppings->term_id;
                        }
                    }
                    wp_remove_object_terms($post_id, $removed_toppings_ids, $taxonomy);
                }
                $term_ids = wp_set_object_terms($post_id, $toppings_arr, $taxonomy);
                if ($term_ids) {
                    foreach ($term_ids as $t_key => $term_id) {
                        $t_key++;
                        $description = (isset($_POST['topping_description_' . $t_key]) && !empty($_POST['topping_description_' . $t_key])) ? $_POST['topping_description_' . $t_key] : '';
                        wp_update_term($term_id, $taxonomy, array('description' => wp_kses_post($description)));
                        do_action('wpfm_save_topping_meta_field', array('term_id' => absint($term_id), 'taxonomy' => esc_attr($taxonomy), 'count' => absint($t_key)));
                    }
                }
            }
            update_post_meta($post_id, '_food_toppings', $toppings_meta);
        }

        // Update repeated_options meta for the count of toppings.
        $repeated_options = isset($_POST['repeated_options']) ? $_POST['repeated_options'] : '';
        if (is_array($repeated_options)) {
            $repeated_options = array_map('esc_attr', $repeated_options);
        }
        update_post_meta($post_id, '_food_repeated_options', $repeated_options);

        // Set orders according to previous inserted post.
        $order_menu = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($post_id));
        if ($order_menu && $order_menu[0]->menu_order == 0) {
            $last_inserted_post = get_posts(array(
                'post_type' => 'food_manager',
                'posts_per_page' => 2,
                'offset' => 0,
                'orderby' => 'ID',
                'order' => 'DESC',
                'post_status' => 'any',
            ));

            if ($last_inserted_post) {
                $last_menu_order = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($last_inserted_post[0]->ID));
                $next_menu_order = intval($last_menu_order[0]->menu_order) + 1;
                $wpdb->update($wpdb->posts, ['menu_order' => $next_menu_order], ['ID' => intval($post_id)]);
            } else {
                $wpdb->update($wpdb->posts, ['menu_order' => 1], ['ID' => intval($post_id)]);
            }
        }
        remove_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);
        $food_data = array(
            'ID'          => intval($post_id),
        );
        wp_update_post($food_data);
        add_action('wpfm_save_food_data', array($this, 'food_manager_save_food_manager_data'), 20, 3);
    }
    
    /**
     * Get the food layout by the given category id.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function get_food_listing_by_category_id() {
        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            $args = [
                'post_type' => 'food_manager',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'post__not_in' => isset($_POST['exclude']) && !empty($_POST['exclude']) ? array_map('esc_attr', $_POST['exclude']) : array(),
                'tax_query' => [
                    [
                        'taxonomy' => esc_attr($_POST['taxonomy']),
                        'terms' => esc_attr($_POST['category_id']),
                    ],
                ],
                // Rest of your arguments.
            ];

            $food_listing = new WP_Query(apply_filters('get_food_listings_by_category_args',$args));
            $html = [];

            if ($food_listing->have_posts()) :
                while ($food_listing->have_posts()) : $food_listing->the_post();
                    $id = get_the_ID();
                    $html[] =
                        '<li class="menu-item-handle" data-food-id="' . esc_attr($id) . '">
                        <div class="wpfm-admin-left-col">
                            <span class="dashicons dashicons-menu"></span>
                            <span class="item-title">' . esc_html(get_the_title($id)) . '</span>
                        </div>
                        <div class="wpfm-admin-right-col">
                            <a href="javascript:void(0);" class="wpfm-food-item-remove">
                                <span class="dashicons dashicons-dismiss"></span>
                            </a>
                        </div>
                        <input type="hidden" name="wpfm_food_listing_ids[]" value="' . esc_attr($id) . '" />
                    </li>';
                endwhile;
            endif;
            wp_reset_postdata();
            wp_send_json(array('html' => $html, 'success' => true));
        } else {

            $args = [
                'post_type' => 'food_manager',
                'posts_per_page' => -1,
                'post__not_in' => isset($_POST['exclude']) && !empty($_POST['exclude']) ? array_map('esc_attr', $_POST['exclude']) : array(),
            ];

            $food_listing = new WP_Query(apply_filters('get_food_listings_category_args',$args));
            $html = [];

            if ($food_listing->have_posts()) :
                while ($food_listing->have_posts()) : $food_listing->the_post();
                    $id = get_the_ID();

                    $html[] =
                        '<li class="menu-item-handle" data-food-id="' . esc_attr($id) . '">
                        <div class="wpfm-admin-left-col">
                            <span class="dashicons dashicons-menu"></span>
                            <span class="item-title">' . esc_html(get_the_title($id)) . '</span>
                        </div>
                        <div class="wpfm-admin-right-col">
                            <a href="javascript:void(0);" class="wpfm-food-item-remove">
                                <span class="dashicons dashicons-dismiss"></span>
                            </a>
                        </div>
                        <input type="hidden" name="wpfm_food_listing_ids[]" value="' . esc_attr($id) . '" />
                    </li>';
                endwhile;
            endif;
            wp_reset_postdata();

            wp_send_json(array('html' => $html, 'success' => true));
        }
        wp_die();
    }
    
    /**
     * Save the food menu meta data.
     *
     * @access public
     * @param int $post_id 
     * @param object $post 
     * @return void
     * @since 1.0.0
     */
    public function wpfm_save_food_menu_data($post_id, $post){
        if (isset($_POST['radio_icons']) && !empty($_POST['radio_icons'])) {
            $wpfm_radio_icon = esc_attr($_POST['radio_icons']);
            if (isset($wpfm_radio_icon)) {
                if (!add_post_meta($post_id, 'wpfm_radio_icons', $wpfm_radio_icon, true)) {
                    update_post_meta($post_id, 'wpfm_radio_icons', $wpfm_radio_icon);
                }
            }
        }

        if (isset($_POST['wpfm_food_listing_ids'])) {
            $item_ids = array_map('esc_attr', $_POST['wpfm_food_listing_ids']);
            update_post_meta($post_id, '_food_item_ids', $item_ids);
        } else {
            update_post_meta($post_id, '_food_item_ids', '');
        }
        
        if (isset($_POST['wpfm_disable_food_redirect'])) {
            $disable_option = esc_attr($_POST['wpfm_disable_food_redirect']);
            update_post_meta($post_id, '_wpfm_disable_food_redirect', $disable_option);
        } else {
            update_post_meta($post_id, '_wpfm_disable_food_redirect', '');
        }
    }
    
    /**
     * This function disables the Gutenberg editor.
     * 
     * @access public
     * @param boolean $is_enabled
     * @param string $post_type
     * @return bool $is_enabled
     * @since 1.0.0
     */
    public function disable_gutenberg($is_enabled, $post_type) {
        if (apply_filters('wpfm_disable_gutenberg', true) && $post_type === 'food_manager') return false;
        return $is_enabled;
    }
    
    /**
     * Customizes the taxonomy used in wpfm_term_radio_checklist_for_taxonomy function.
     *
     * @param string $taxonomy The taxonomy name.
     * @param array  $args An array of arguments for the function.
     * @return string The modified taxonomy name.
     */
    public function wpfm_food_manager_taxonomy($taxonomy, $args) {
        if(get_post_type($args) == 'food_manager'){
            $taxonomy = 'food_manager_type';
        }
        return $taxonomy;
    }
    
    /**
     * Customizes the post type used in wpfm_term_radio_checklist_for_posttype function.
     *
     * @param string $post_type The post type name.
     * @return string The modified taxonomy name.
     */
    public function wpfm_food_manager_post_type($post_type){
        if(get_post_type() == 'food_manager'){
            $post_type = 'food_manager';
        }
        return $post_type;
    
    }
}

WPFM_Writepanels::instance();
