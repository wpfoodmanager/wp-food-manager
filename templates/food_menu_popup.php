<?php 
$thumbnail_option = get_option('food_manager_enable_thumbnail');
$featured_img = get_the_post_thumbnail_url($food_id, 'full');
if (isset($featured_img) && empty($featured_img)) {
    $featured_img = apply_filters('wpfm_default_food_banner', esc_url(WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg'));
}
$sale_price = get_post_meta($food_id, '_food_sale_price', true);
$regular_price = get_post_meta($food_id, '_food_price', true);
$price_decimals = wpfm_get_price_decimals();
$price_format = get_food_manager_price_format();
$price_thousand_separator = wpfm_get_price_thousand_separator();
$price_decimal_separator = wpfm_get_price_decimal_separator();
$formatted_sale_price = '';
$formatted_regular_price = '';
if (!empty($sale_price)) {
    $formatted_sale_price = number_format($sale_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
}
if (!empty($regular_price)) {
    $formatted_regular_price = number_format($regular_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
}
?>
<div class="wpfm-modal-content-wrapper wpfm-modal-content-centered">
    <div class="wpfm-modal-content wpfm-main wpfm-food-modal-content">
        <div id="wpfm_food_menu_modal_image" class="wpfm-food-modal-food_image">
            <img src="<?php echo esc_url($featured_img); ?>" alt="<?php echo esc_attr($food->post_title); ?>" />
            <div class="wpfm-modal-header wpfm-food-popup-header">
                <div class="wpfm-modal-header-close">
                    <button type="button" class="wpfm-modal-close" aria-label="Close" id="wpfm-modal-close">X</button>
                </div>
            </div>
        </div>
        <div class="wpfm-food-modal-food_details">
            <div class="wpfm-food-modal-food_title">
                <h3 id="wpfm_food_menu_modal_title"><?php echo wp_kses_post($food->post_title);?></h3>
            </div>
            <?php do_action('wpfm_food_menu_popup_title_after', $food_id); ?>
            <div id="wpfm_food_menu_modal_price" class="wpfm-food-modal-food_price">
                <?php if (!empty($regular_price) && !empty($sale_price)) {
                    $food_regular_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_sale_price);
                    $food_sale_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_regular_price);
                    echo "<del> " . $food_sale_price . "</del>";
                    echo "<ins><strong>" . wp_kses_post($food_regular_price) . "</strong></ins>"; 
                } elseif (!empty($regular_price)) {
                    echo sprintf(esc_html($price_format), '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', esc_attr($formatted_regular_price));
                } ?>
            </div>
            <?php do_action('wpfm_food_menu_popup_price_after', $food_id); ?>
            <div id="wpfm_food_menu_modal_description" class="wpfm-food-modal-food_description">
                <?php echo wp_kses_post($food->post_content);?>
            </div>
            <div class="food-stock-status">
                <?php display_stock_status($food_id);
                    if (get_stock_status($food_id) == 'food_instock') {
                        display_food_quantity($food_id);     
                    }
                ?>
            </div>
            <form class="wpfm-toppings" id="wpfm_single_food_topping_form" method="post" action="" data-product-attribute='<?php echo esc_attr(apply_filters('wpfm_food_toppings_form_variation', '', $food_id)); ?>'>
                                    <?php
                                    $ext_options = get_post_meta($food_id, '_food_toppings', true);
                                    $repeated_count = get_post_meta($food_id, '_food_repeated_options', true);
                                    if (!class_exists('WPFM_Add_Food_Form')) {
                                        include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-abstract-form.php');
                                        include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-add-food-form.php');
                                    }
                                    $form_add_food_instance = call_user_func(array('WPFM_Add_Food_Form', 'instance'));
                                    $custom_food_fields  = !empty($form_add_food_instance->get_food_manager_fieldeditor_fields()) ? $form_add_food_instance->get_food_manager_fieldeditor_fields() : array();
                                    $custom_toppings_fields  = !empty($form_add_food_instance->get_food_manager_fieldeditor_toppings_fields()) ? $form_add_food_instance->get_food_manager_fieldeditor_toppings_fields() : array();
                                    $custom_fields = '';
                                    if (!empty($custom_toppings_fields)) {
                                        $custom_fields = array_merge($custom_food_fields, $custom_toppings_fields);
                                    } else {
                                        $custom_fields = $custom_food_fields;
                                    }
                                    $default_fields = $form_add_food_instance->get_default_food_fields();
                                    $additional_fields_extra_topping = [];
                                    if (!empty($custom_fields) && isset($custom_fields) && !empty($custom_fields['toppings'])) {
                                        foreach ($custom_fields['toppings'] as $field_name => $field_data) {
                                            if (!array_key_exists($field_name, $default_fields['toppings'])) {
                                                $meta_key = '_' . $field_name;
                                                $field_value = $food->$meta_key;
                                                if (isset($field_value)) {
                                                    $additional_fields_extra_topping[$field_name] = $field_data;
                                                }
                                            }
                                        }
                                        $additional_fields_extra_topping = apply_filters('food_manager_show_additional_details_fields', $additional_fields_extra_topping);
                                    }
                                    $more_class = !empty($additional_fields_extra_topping) ? 'with-more' : '';
                                    if (!empty($repeated_count)) {
                                        if (!empty($ext_options) && isArrayNotBlank($ext_options)) {
                                            echo "<h3 class='wpfm-heading-text'>Extra Toppings</h3>";
                                            foreach ($ext_options as $key => $ext_option) {
                                                if (!empty($ext_option['_topping_name']) && !empty($ext_option['_topping_options'])) {
                                                    $field_required = '';
                                                    $topping_images = is_array($ext_option['_topping_image']) ? $ext_option['_topping_image'][0] : $ext_option['_topping_image'];
                                                    echo "<div class='wpfm-input-field-common " . esc_attr($more_class) . "'>";
                                                    echo '<img src="'.$topping_images.'" alt="topping_image" width="100" height="20" /> <h4 class="wpfm-heading-text">' . esc_html($ext_option['_topping_name']) . '';
                                                    if( isset($ext_option['_topping_required']) && $ext_option['_topping_required'] === 'yes') {
                                                        echo '<span class="wpfm-require-mark"> *</span></h4>';
                                                    } else {
                                                        echo '</h4>';
                                                    }
                                                    if( isset($ext_option['_topping_description']) && !empty($ext_option['_topping_description'])) {
                                                        echo '<div class="wpfm-input-description">' . wp_kses_post($ext_option['_topping_description']) . '</div>';
                                                    }
                                                    do_action('wpfm_singular_option_input_before');
    
                                                    $topping_htm = '<ul class="wpfm-topping-options">';
                                                    if (isset($ext_option['_topping_options']) && !empty($ext_option['_topping_options'])) {
                                                        foreach ($ext_option['_topping_options'] as $key2 => $value2) {
                                                            $price_decimals = wpfm_get_price_decimals();
                                                            $price_format = get_food_manager_price_format();
                                                            $price_thousand_separator = wpfm_get_price_thousand_separator();
                                                            $price_decimal_separator = wpfm_get_price_decimal_separator();
                                                            $option_price = $value2['option_price'];
                                                            $f_formatted_option_price = '';
    
                                                            if (!empty($option_price)) {
                                                                $formatted_option_price = number_format($option_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
                                                                $f_formatted_option_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . get_food_manager_currency_symbol() . '</span>', $formatted_option_price);
                                                            }
    
                                                            $option_price_sep = '';
                                                            if ($f_formatted_option_price != '') {
                                                                $option_price_sep = ' - ';
                                                            }
    
                                                            $topping_htm .= '<li class="wpfm-topping-items">' . esc_attr($value2['option_name']) . $option_price_sep . $f_formatted_option_price . '</li>';
                                                        }
                                                    }
                                                    $topping_htm .= '</ul>';
                                                     $allowed_html = array(
                                                        'input' => array(
                                                            'type'    => array('checkbox'), // Only allow input with type="checkbox"
                                                            'name'    => array(),
                                                            'value'   => array(),
                                                            'checked' => array(),           // Allow the checked attribute
                                                            'class'   => array(),
                                                            'id'      => array(),
                                                            'data-attribute_name' => array(),
                                                            'data-val' => array(),
                                                            'data-attr-name' => array(),
                                                            'data-attribute_name' => array(),
                                                        ),
                                                        'label' => array(
                                                            'for'   => array(),
                                                            'class' => array(),
                                                        ),
                                                        'p'     => array(),
                                                        'div'   => array(
                                                            'class' => array(),
                                                            'data-attribute_name' => array()
                                                        ),
                                                        // Add other allowed tags and attributes as needed
                                                    );
                                                    
                                                    echo wp_kses( apply_filters( 'wpfm_toppings_list_htm', $topping_htm, array(
                                                        'ext_option' => $ext_option,
                                                        'more_class' => $more_class,
                                                        'key'        => $key
                                                    )), $allowed_html );
                                                    do_action('wpfm_singular_option_input_after');
                                                   if (!empty($additional_fields_extra_topping)) {
                                                        echo "<div class='wpfm-additional-main-row wpfm-row'>";
                                                        $val_flag = 0;
                                                        foreach ($additional_fields_extra_topping as $name => $field) {
                                                            $field_key = '_' . $name;
                                                            $field_value = !empty($ext_option[$field_key]) ? $ext_option[$field_key] : '';
                                                            if (isset($field_value) && !empty($field_value)) {
                                                                $val_flag = 1;
                                                                wpfm_extra_topping_form_fields($post, $field, $field_value);
                                                            }
                                                        }
                                                        echo "</div>"; 
                                                        if ($val_flag) {
                                                            echo '<span class="wpfm-view-more">' . esc_html__('View more +', 'wp-food-manager') . '</span>';
                                                        }
                                                    }
                                                    echo "</div>";
                                                }
                                            }
                                        }
                                    }
                                    do_action('single_food_toppings_after');
                                    ?>
                                </form>
            <?php do_action('wpfm_food_menu_popup_after', $food_id, $quantity, $product_id); ?>
            
        </div>
    </div>
</div>
<a href="#">
    <div class="wpfm-modal-overlay"></div>
</a>