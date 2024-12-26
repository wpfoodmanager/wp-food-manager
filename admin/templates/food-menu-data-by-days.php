    <div class="wpfm-admin-postbox-meta-data">
        <table class="wpem-backend-custom-table wpem-backend-food-menu-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Days', 'wp-food-manager'); ?></th>
                    <th><?php esc_html_e('Select Food', 'wp-food-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
                // Days of the week, starting with Sunday
                $days_of_week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                // Retrieve the saved open hours data
                $post_id = get_the_ID();

                // Loop through the days of the week
                foreach ($days_of_week as $day):
                    // Retrieve the serialized data for this day (if it exists)
                    $saved_data = get_post_meta($post_id, '_wpfm_food_menu_by_days', true);
                    $day_data = isset($saved_data[$day]) ? $saved_data[$day] : array();

                    ?>
                    <tr>
                        <th>
                            <label>
                                <input type="hidden" name="days_<?php echo esc_attr($day); ?>"
                                    value="days_<?php echo esc_attr($day); ?>">
                                <?php echo esc_html($day); ?>
                            </label>
                        </th>
                        <td>
                            <div class="wpfm-admin-postbox-meta-data">
                                <!-- Food Category Selection -->
                                <div class="wpfm-admin-menu-selected-item wpfm-admin-postbox-form-field">
                                    <?php
                                    $selected_ids = isset($day_data['food_categories']) ? $day_data['food_categories'] : array();
                                    food_manager_dropdown_selection(array(
                                        'multiple' => true,
                                        'show_option_all' => __('Select food category', 'wp-food-manager'),
                                        'id' => 'wpfm-admin-food-selections',
                                        'taxonomy' => 'food_manager_category',
                                        'hide_empty' => false,
                                        'pad_counts' => true,
                                        'show_count' => true,
                                        'hierarchical' => false,
                                        'selected' => $selected_ids,
                                        'class'   => 'food-manager-cat-dropdown',
                                        'name'    => 'food_cats_'.$day
                                    ));
                                    ?>
                                </div>
            
                                <!-- Food Type Selection -->
                                <div class="wpfm-admin-menu-selected-item wpfm-admin-postbox-form-field">
                                    <?php
                                    $selected_ids = isset($day_data['food_types']) ? $day_data['food_types'] : array();
                                    food_manager_dropdown_selection(array(
                                        'multiple' => true,
                                        'show_option_all' => __('Select food types', 'wp-food-manager'),
                                        'id' => 'wpfm-admin-food-types-selections',
                                        'taxonomy' => 'food_manager_type',
                                        'hide_empty' => false,
                                        'pad_counts' => true,
                                        'show_count' => true,
                                        'hierarchical' => false,
                                        'name' => 'food_types_'.$day,
                                        'selected' => $selected_ids,
                                        'class'   => 'food-manager-cat-dropdown',
                                    ));
                                    ?>
                                </div>
                            </div>
            
                            <!-- Menu Items -->
                            <div class="wpfm-admin-food-menu-items">
                                <?php
                                $item_ids = isset($day_data['food_items']) ? $day_data['food_items'] : array();
                                ?>
                                <ul class="wpfm-food-menus menu-item-bar" id="wpfm-food-menu-list">
                                    <?php if ($item_ids && is_array($item_ids)) { ?>
                                        <?php foreach ($item_ids as $key => $id) { ?>
                                            <li class="menu-item-handle" data-food-id="<?php echo esc_attr($id); ?>">
                                                <div class="wpfm-admin-left-col">
                                                    <span class="dashicons dashicons-menu"></span>
                                                    <span class="item-title"><?php echo esc_html(get_the_title($id)); ?></span>
                                                </div>
                                                <div class="wpfm-admin-right-col">
                                                    <a href="javascript:void(0);" class="wpfm-food-item-remove">
                                                        <span class="dashicons dashicons-dismiss"></span>
                                                    </a>
                                                </div>
                                                <input type="hidden" name="wpfm_food_menu_listing_ids_<?php echo $day?>[]" value="<?php echo esc_attr($id); ?>" />
                                            </li>
                                        <?php }
                                    } ?>
                                </ul>
                                <span class="no-menu-item-handle no-menu-item-handle_<?php echo $day?>" style="display: none;">Please select the food category or food types
                                    to add food items to the menu.</span>
                                <div class="wpfm-loader_<?php echo $day?>" style="display: none;">
                                    <img src="<?php echo esc_url(WPFM_PLUGIN_URL . '/assets/images/loader.gif'); ?>" alt="Loading..."
                                        class="wpfm-loader-image">
                                </div>
                                <div class="success_message"><span class="wpfm-success-message wpfm-success-message_<?php echo $day?>" style="display: none;">Foods added to
                                        the menu successfully!</span></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>