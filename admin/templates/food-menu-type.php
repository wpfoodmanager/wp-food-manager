 <div class="wpfm-admin-food-menu-container wpfm-flex-col wpfm-admin-postbox-meta-data">
    <div class="wpfm-admin-food-menu-items">
        <?php $item_menu_option = get_post_meta($food_menu_id, '_food_menu_option', true); 
        $key = 'food_menu_options';
        $field = array(
            'name'        => 'wpfm_food_menu_option',
            'label'       => __('Food Menu Options', 'wp-food-manager'),
            'type'        => 'radio',
            'required'    => true,
            'options' 	  => array(
                'static_menu' => __('Static Menu', 'wp-food-manager'),
                'dynamic_menu' => __('Dynamic Menu', 'wp-food-manager'),
            ),
            'value'       => $item_menu_option,
        );
        get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => esc_attr($key), 'field' => $field)); ?>
    </div>
</div>