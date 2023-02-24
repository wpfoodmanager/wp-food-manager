<?php
global $post;

wp_enqueue_script('wpfm-slick-script');
wp_enqueue_style('wpfm-slick-style');
wp_enqueue_style('wpfm-slick-theme-style');
do_action('set_single_listing_view_count');

$food = $post;

?>
<div class="single_food_listing">

    <div class="wpfm-main wpfm-single-food-page">
        <?php if (get_option('food_manager_hide_expired_content', 1) && 'expired' === $post->post_status) : ?>
            <div class="food-manager-info wpfm-alert wpfm-alert-danger"><?php _e('This listing has been expired.', 'wp-food-manager'); ?></div>
        <?php else : ?>
            <?php if (is_food_cancelled()) : ?>
                <div class="wpfm-alert wpfm-alert-danger">
                    <span class="food-cancelled"><?php _e('This food has been cancelled', 'wp-food-manager'); ?></span>
                </div>

            <?php endif; ?>
            <?php
            /**
             * single_food_listing_start hook
             */
            do_action('single_food_listing_start');
            ?>
            <div class="wpfm-single-food-wrapper">
                <div class="wpfm-single-food-header-top">
                    <div class="wpfm-row">
                        <div class="wpfm-col-xs-12 wpfm-col-sm-12 wpfm-col-md-12 wpfm-single-food-images">
                            <?php
                            $food_banners = get_food_banner();
                            if (is_array($food_banners) && sizeof($food_banners) > 1) :
                            ?>
                                <div class="wpfm-single-food-slider-wrapper">
                                    <div class="wpfm-single-food-slider">
                                        <?php foreach ($food_banners as $banner_key => $banner_value) : ?>
                                            <div class="wpfm-slider-items">
                                                <img src="<?php echo $banner_value; ?>" alt="<?php the_title(); ?>" />
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="wpfm-food-single-image-wrapper">
                                    <div class="wpfm-food-single-image"><?php display_food_banner(); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="wpfm-single-food-body">
                    <div class="wpfm-row">
                        <div class="wpfm-col-xs-12 wpfm-col-sm-7 wpfm-col-md-8 wpfm-single-food-left-content">
                            <?php do_action('single_food_overview_before'); ?>
                            <div class="wpfm-single-food-short-info">
                                <div class="wpfm-food-details">
                                    <?php if (get_option('food_manager_food_item_show_hide') && get_stock_status()) : ?>
                                        <div class="food-stock-status">
                                            <?php display_stock_status(); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="wpfm-food-title">
                                        <h3 class="wpfm-heading-text"><?php the_title(); ?></h3>
                                        <?php display_food_veg_nonveg_icon_tag(); ?>
                                    </div>
                                    <div class="wpfm-food-price">
                                        <?php display_food_price_tag(); ?>
                                    </div>
                                    <?php /* ?><div class="wpfm-food-organizer">
                                        <div class="wpfm-food-organizer-name">by <?php echo the_author_posts_link(); ?></div>
                                    </div><?php */ ?>
                                    <?php
                                    $view_count = get_food_views_count($post);
                                    if ($view_count) : ?>
                                        <div class="wpfm-viewed-food wpfm-tooltip wpfm-tooltip-bottom"><i class="wpfm-icon-eye"></i> <?php printf(__(' %d', 'wp-food-manager'), $view_count); ?>
                                            <span class="wpfm-tooltiptext"><?php printf(__('%d people viewed this food.', 'wp-food-manager'), $view_count); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="wpfm-single-food-body-content">
                                <?php do_action('single_food_overview_start'); ?>
                                <?php echo apply_filters('display_food_description', get_the_content()); ?>
                                <?php do_action('single_food_overview_end'); ?>

                                <?php
                                $ingredients = get_food_ingredients();
                                $displaying = is_wpfm_terms_exist($ingredients, 'food_manager_ingredient');
                                if ($displaying && get_food_ingredients() && get_post_meta($post->ID, '_enable_food_ingre', true)) : ?>
                                    <div class="clearfix">&nbsp;</div>
                                    <div class="wpfm-food-ingredients"><h3 class="wpfm-heading-text"><?php _e('Food Ingredients', 'wp-food-manager'); ?></h3> <?php display_food_ingredients(); ?></div>
                                <?php endif; ?>

                                <?php
                                $nutritions = get_food_nutritions();
                                $displaynutri = is_wpfm_terms_exist($nutritions, 'food_manager_nutrition');
                                if ($displaynutri && get_food_nutritions() && get_post_meta($post->ID, '_enable_food_nutri', true)) : ?>
                                    <div class="clearfix">&nbsp;</div>
                                    <div class="wpfm-food-nutritions"><h3 class="wpfm-heading-text"><?php _e('Food Nutritions', 'wp-food-manager'); ?></h3> <?php display_food_nutritions(); ?></div>
                                <?php endif; ?>

                            </div>
                            <div class="wpfm-single-food-body-content wpfm-extra-options">
                                <?php
                                $ext_options = get_post_meta(get_the_ID(), '_wpfm_extra_options', true);
                                $food_data_option_value_count = get_post_meta(get_the_ID(), 'wpfm_option_value_count', true);
                                $repeated_count = get_post_meta(get_the_ID(), 'wpfm_repeated_options', true);

                                if (!class_exists('WPFM_Form_Submit_Food')) {
                                    include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-form-abstract.php');
                                    include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-form-submit-food.php');
                                }

                                $form_submit_food_instance = call_user_func(array('WPFM_Form_Submit_Food', 'instance'));

                                $custom_food_fields  = !empty($form_submit_food_instance->get_food_manager_fieldeditor_fields()) ? $form_submit_food_instance->get_food_manager_fieldeditor_fields() : array();

                                $custom_extra_options_fields  = !empty($form_submit_food_instance->get_food_manager_fieldeditor_extra_options_fields()) ? $form_submit_food_instance->get_food_manager_fieldeditor_extra_options_fields() : array();

                                $custom_fields = '';
                                if (!empty($custom_extra_options_fields)) {
                                    $custom_fields = array_merge($custom_food_fields, $custom_extra_options_fields);
                                } else {
                                    $custom_fields = $custom_food_fields;
                                }

                                $default_fields = $form_submit_food_instance->get_default_food_fields();

                                $additional_fields_extra_topping = [];
                                if (!empty($custom_fields) && isset($custom_fields) && !empty($custom_fields['extra_options'])) {
                                    foreach ($custom_fields['extra_options'] as $field_name => $field_data) {
                                        if (!array_key_exists($field_name, $default_fields['extra_options'])) {
                                            $meta_key = '_' . $field_name;
                                            $field_value = $food->$meta_key;

                                            if (isset($field_value)) {
                                                $additional_fields_extra_topping[$field_name] = $field_data;
                                            }
                                        }
                                    }

                                    if (isset($additional_fields_extra_topping['attendee_information_type']))
                                        unset($additional_fields_extra_topping['attendee_information_type']);

                                    if (isset($additional_fields_extra_topping['attendee_information_fields']))
                                        unset($additional_fields_extra_topping['attendee_information_fields']);

                                    $additional_fields_extra_topping = apply_filters('food_manager_show_additional_details_fields', $additional_fields_extra_topping);
                                }
                                $more_class = !empty($additional_fields_extra_topping) ? 'with-more' : '';

                                if (!empty($repeated_count)) {
                                    if (!empty($ext_options)) {
                                        echo "<h3 class='wpfm-heading-text'>Extra Toppings</h3>";
                                        foreach ($ext_options as $key => $ext_option) {
                                            $field_required = '';
                                            if($ext_option['option_required'] == 'yes'){
                                                $field_required = 'required';
                                            }
                                            if ($ext_option['option_type'] == 'radio') {
                                                echo "<div class='wpfm-radio-options wpfm-input-field-common' data-attribute_name='attribute_pa_".esc_attr($key)."'>";
                                                echo '<label for="' . str_replace(" ", "-", strtolower($ext_option['option_name'])) . '"><strong>' . $ext_option['option_name'] . '</strong></label>';
                                                if (!empty($ext_option['option_description'])) {
                                                    echo '<div class="wpfm-input-description">' . $ext_option['option_description'] . '</div>';
                                                }
                                                echo '<div class="wpfm-inner-field-content ' . $more_class . '">';
                                                foreach ($ext_option['option_options'] as $key2 => $value2) {
                                                    $checked = ($value2['option_value_default']) == 'on' ? 'checked' : '';
                                                    echo "<div class='wpfm-input-singular'>";
                                                    echo '<input type="radio" id="' . esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value2['option_value_price']) . '" ' . $checked . ' data-price-type='.esc_attr($value2['option_value_price_type']).' data-attr-name=' . esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))) . ' '.$field_required.'>';
                                                    echo '<label for="' . esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))) . '"> ' . esc_html($value2['option_value_name']) . ' - ' . get_food_manager_currency_symbol() . $value2['option_value_price'] . '</label>';
                                                    echo "</div>";
                                                }
                                                echo "<div class='wpfm-input-singular'>";
                                                echo '<input type="radio" id="radio-none" name="' . esc_attr($key) . '" value="0">';
                                                echo '<label for="radio-none"> None</label>';
                                                echo "</div>";
                                                if (!empty($additional_fields_extra_topping)) {
                                                    echo "<div class='wpfm-additional-main-row'>";
                                                    echo '<hr style="margin:25px 0 15px; display: block; background-color: #e4e4e4; height: 2px;"></hr>';
                                                    foreach ($additional_fields_extra_topping as $name => $field) {
                                                        $field_key = '_' . $name;
                                                        $field_value = !empty($ext_option[$name]) ? $ext_option[$name] : '';

                                                        if (isset($field_value)) {
                                                            wpfm_extra_topping_form_fields($post, $field, $field_value);
                                                        }
                                                    }
                                                    echo "</div>";
                                                    echo '<span class="wpfm-view-more">View more +</span>';
                                                    echo '<span class="wpfm-view-less">View less -</span>';
                                                }
                                                echo "</div>";
                                                echo "</div>";
                                            }

                                            if ($ext_option['option_type'] == 'checkbox') {
                                                echo "<div class='wpfm-checkbox-options wpfm-input-field-common'>";
                                                echo '<label for="' . str_replace(" ", "-", strtolower($ext_option['option_name'])) . '"><strong>' . $ext_option['option_name'] . '</strong></label>';
                                                if (!empty($ext_option['option_description'])) {
                                                    echo '<div class="wpfm-input-description">' . $ext_option['option_description'] . '</div>';
                                                }
                                                echo '<div class="wpfm-inner-field-content ' . $more_class . '">';
                                                foreach ($ext_option['option_options'] as $key2 => $value2) {
                                                    $checked = ($value2['option_value_default']) == 'on' ? 'checked' : '';
                                                    echo "<div class='wpfm-input-singular'>";
                                                    echo '<input type="checkbox" id="' . esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value2['option_value_price']) . '" ' . $checked . ' data-val="' . $value2['option_value_price'] . '" data-price-type='.esc_attr($value2['option_value_price_type']).' data-attr-name=' . esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))) . ' '.$field_required.'>';
                                                    echo '<label for="' . esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))) . '"> ' . esc_html($value2['option_value_name']) . ' - ' . get_food_manager_currency_symbol() . $value2['option_value_price'] . '</label>';
                                                    echo "</div>";
                                                }
                                                if (!empty($additional_fields_extra_topping)) {
                                                    echo "<div class='wpfm-additional-main-row'>";
                                                    echo '<hr style="margin:25px 0 15px; display: block; background-color: #e4e4e4; height: 2px;"></hr>';
                                                    foreach ($additional_fields_extra_topping as $name => $field) {
                                                        $field_key = '_' . $name;
                                                        $field_value = !empty($ext_option[$name]) ? $ext_option[$name] : '';

                                                        if (isset($field_value)) {
                                                            wpfm_extra_topping_form_fields($post, $field, $field_value);
                                                        }
                                                    }
                                                    echo "</div>";
                                                    echo '<span class="wpfm-view-more">View more +</span>';
                                                    echo '<span class="wpfm-view-less">View less -</span>';
                                                }
                                                echo "</div>";
                                                echo "</div>";
                                            }

                                            if ($ext_option['option_type'] == 'select') {
                                                echo "<div class='wpfm-select-options wpfm-input-field-common " . $more_class . "'>";
                                                echo '<label for="' . str_replace(" ", "-", strtolower($ext_option['option_name'])) . '"><strong>' . $ext_option['option_name'] . '</strong></label>';
                                                if (!empty($ext_option['option_description'])) {
                                                    echo '<div class="wpfm-input-description">' . $ext_option['option_description'] . '</div>';
                                                }
                                                echo '<select name="' . esc_attr($key) . '">';
                                                $d_selected_flg = 'selected';
                                                foreach ($ext_option['option_options'] as $key2 => $value2) {
                                                    $selected = ($value2['option_value_default']) == 'on' ? 'selected' : '';
                                                    $d_selected_flg = ($selected) ? '' : $d_selected_flg;
                                                    echo '<option value="' . esc_attr($value2['option_value_price']) . '" ' . $selected . ' data-price-type='.esc_attr($value2['option_value_price_type']).' data-attr-name=' . esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))) . '>' . esc_attr($value2['option_value_name']) . ' - ' . get_food_manager_currency_symbol() . $value2['option_value_price'] . '</option>';
                                                }
                                                echo '<option value="" '.$d_selected_flg.'>None</option>';
                                                echo '</select>';

                                                if (!empty($additional_fields_extra_topping)) {
                                                    echo "<div class='wpfm-additional-main-row'>";
                                                    echo '<hr style="margin:25px 0 15px; display: block; background-color: #e4e4e4; height: 2px;"></hr>';
                                                    foreach ($additional_fields_extra_topping as $name => $field) {
                                                        $field_key = '_' . $name;
                                                        $field_value = !empty($ext_option[$name]) ? $ext_option[$name] : '';

                                                        if (isset($field_value)) {
                                                            wpfm_extra_topping_form_fields($post, $field, $field_value);
                                                        }
                                                    }
                                                    echo "</div>";
                                                    echo '<span class="wpfm-view-more">View more +</span>';
                                                    echo '<span class="wpfm-view-less">View less -</span>';
                                                }
                                                echo "</div>";
                                            }
                                        }
                                    }
                                }

                                do_action('single_food_extra_toppings_after');
                                ?>
                            </div>
                            <!-- Additional Info Block Start -->
                            <?php
                            $show_additional_details = apply_filters('food_manager_show_additional_details', true);

                            if ($show_additional_details) :

                                if (!class_exists('WPFM_Form_Submit_Food')) {
                                    include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-form-abstract.php');
                                    include_once(WPFM_PLUGIN_DIR . '/forms/wpfm-form-submit-food.php');
                                }

                                $form_submit_food_instance = call_user_func(array('WPFM_Form_Submit_Food', 'instance'));

                                $custom_food_fields  = !empty($form_submit_food_instance->get_food_manager_fieldeditor_fields()) ? $form_submit_food_instance->get_food_manager_fieldeditor_fields() : array();

                                $custom_extra_options_fields  = !empty($form_submit_food_instance->get_food_manager_fieldeditor_extra_options_fields()) ? $form_submit_food_instance->get_food_manager_fieldeditor_extra_options_fields() : array();

                                $custom_fields = '';
                                if (!empty($custom_extra_options_fields)) {
                                    $custom_fields = array_merge($custom_food_fields, $custom_extra_options_fields);
                                } else {
                                    $custom_fields = $custom_food_fields;
                                }

                                $default_fields = $form_submit_food_instance->get_default_food_fields();

                                $additional_fields = [];
                                if (!empty($custom_fields) && isset($custom_fields) && !empty($custom_fields['food'])) {
                                    foreach ($custom_fields['food'] as $field_name => $field_data) {
                                        if (!array_key_exists($field_name, $default_fields['food'])) {
                                            $meta_key = '_' . $field_name;
                                            $field_value = $food->$meta_key;

                                            if (isset($field_value)) {
                                                $additional_fields[$field_name] = $field_data;
                                            }
                                        }
                                    }

                                    if (isset($additional_fields['attendee_information_type']))
                                        unset($additional_fields['attendee_information_type']);

                                    if (isset($additional_fields['attendee_information_fields']))
                                        unset($additional_fields['attendee_information_fields']);

                                    $additional_fields = apply_filters('food_manager_show_additional_details_fields', $additional_fields);
                                }

                                if (!empty($additional_fields)) : ?>
                                    <div class="wpfm-additional-info-block-wrapper">

                                        <div class="wpfm-additional-info-block">
                                            <h3 class="wpfm-heading-text"><?php _e('Additional Details', 'wp-food-manager'); ?></h3>
                                        </div>

                                        <div class="wpfm-additional-info-block-details">

                                            <?php do_action('single_food_additional_details_start'); ?>

                                            <div class="wpfm-row">

                                                <?php
                                                $date_format = !empty(get_option('date_format')) ? get_option('date_format') : 'F j, Y';
                                                $time_format = !empty(get_option('time_format')) ? get_option('time_format') : 'g:i a';

                                                foreach ($additional_fields as $name => $field) : ?>

                                                    <?php
                                                    $field_key = '_' . $name;
                                                    $field_value = $food->$field_key;
                                                    ?>
                                                    <?php if (isset($field_value)) : ?>

                                                        <?php if ($field['type'] == 'group') : ?>

                                                            <?php if (isset($field['fields']) && !empty($field['fields'])) : ?>

                                                                <div class="wpfm-col-12 wpfm-additional-info-block-group">

                                                                    <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr($field['label']); ?></strong></p>

                                                                    <?php foreach ($field_value as $child_index => $child_value) : ?>

                                                                        <?php foreach ($field['fields'] as $child_field_name => $child_field) : ?>

                                                                            <?php if (!empty($child_value[$child_field_name])) : ?><div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                    <div class="wpfm-additional-info-block-details-content-items">
                                                                                        <?php
                                                                                        $my_value_arr = [];
                                                                                        foreach ($child_value[$child_field_name] as $key => $my_value) {
                                                                                            $my_value_arr[] = $child_field['options'][$my_value];
                                                                                        }
                                                                                        ?>
                                                                                        <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?> -</strong> <?php printf(__('%s', 'wp-food-manager'),  implode(', ', $my_value_arr)); ?></p>
                                                                                    </div>
                                                                                </div>
                                                                            <?php elseif ($child_field['type'] == 'select') : ?>
                                                                                <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                    <div class="wpfm-additional-info-block-details-content-items">
                                                                                        <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?> - </strong> <?php printf(__('%s', 'wp-food-manager'),  $child_value[$child_field_name]); ?></p>
                                                                                    </div>
                                                                                </div>

                                                                                <?php if ($child_field['type'] == 'textarea' || $child_field['type'] == 'wp-editor') : ?>
                                                                                    <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
                                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                                            <p class="wpfm-additional-info-block-title"><strong> <?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?></strong></p>
                                                                                            <p class="wpfm-additional-info-block-textarea-text"><?php printf(__('%s', 'wp-food-manager'),  $child_value[$child_field_name]); ?></p>
                                                                                        </div>
                                                                                    </div>

                                                                                <?php elseif ($child_field['type'] == 'multiselect') : ?>

                                                                                    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                                            <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?> -</strong> <?php printf(__('%s', 'wp-food-manager'),  implode(', ', $my_value_arr)); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php elseif ($child_field['type'] == 'select') : ?>
                                                                                    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                                            <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?> - </strong> <?php printf(__('%s', 'wp-food-manager'),  $child_value[$child_field_name]); ?></p>
                                                                                        </div>
                                                                                    </div>

                                                                                <?php elseif ($child_field['type'] == 'date') : ?>
                                                                                    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                                            <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?> - </strong> <?php echo date_i18n($date_format, strtotime($child_value[$child_field_name])); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php elseif ($child_field['type'] == 'time') : ?>
                                                                                    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                                            <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?> - </strong> <?php echo date($time_format, strtotime($child_value[$child_field_name])); ?></p>
                                                                                        </div>
                                                                                    </div>

                                                                                <?php elseif ($child_field['type'] == 'file') : ?>
                                                                                    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                        <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?> - </strong></p>
                                                                                        <div class="wpfm-additional-info-block-details-content-items wpfm-additional-file-slider">
                                                                                            <?php if (is_array($child_value[$child_field_name])) : ?>
                                                                                                <?php foreach ($child_value[$child_field_name] as $file) : ?>
                                                                                                    <?php if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
                                                                                                        <div><img src="<?php echo esc_attr($file); ?>"></div>
                                                                                                    <?php else : ?>
                                                                                                        <div class="wpfm-icon"><a target="_blank" class="wpfm-icon-download3" href="<?php echo esc_attr($file); ?>"> <?php _e('Download', 'wp-food-manager'); ?></a></div>
                                                                                                    <?php endif; ?>
                                                                                                <?php endforeach; ?>
                                                                                            <?php else : ?>
                                                                                                <?php if (in_array(pathinfo($child_value[$child_field_name], PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
                                                                                                    <div><img src="<?php echo esc_attr($child_value[$child_field_name]); ?>"></div>
                                                                                                <?php else : ?>
                                                                                                    <div class="wpfm-icon"><a target="_blank" class="wpfm-icon-download3" href="<?php echo esc_attr($child_value[$child_field_name]); ?>"> <?php _e('Download', 'wp-food-manager'); ?></a></div>
                                                                                                <?php endif; ?>
                                                                                            <?php endif; ?>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php elseif ($child_field['type'] == 'url') : ?>
                                                                                    <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
                                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                                            <p class="wpfm-additional-info-block-textarea-text"><a href="<?php if (isset($child_value[$child_field_name])) echo esc_attr($child_value[$child_field_name]); ?>"><?php printf(__('%s', 'wp-food-manager'),  $child_field['label']); ?></a></p>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php else : ?>
                                                                                    <?php if (is_array($child_value[$child_field_name])) : ?>
                                                                                        <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                            <div class="wpfm-additional-info-block-details-content-items">
                                                                                                <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr($child_field['label']); ?> -</strong> <?php echo esc_attr(implode(', ', $child_value[$child_field_name])); ?></p>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php else : ?>
                                                                                        <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                                            <div class="wpfm-additional-info-block-details-content-items">
                                                                                                <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr($child_field['label']); ?> -</strong> <?php echo esc_attr($child_value[$child_field_name]); ?></p>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php endif; ?>

                                                                                <?php endif; ?>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php elseif ($field['type'] == 'textarea' || $field['type'] == 'wp-editor') :
                                                            if (is_array($field_value) || wpfm_begnWith($field_value, "http")) {
                                                                $field_value = '';
                                                            }
                                                        ?>
                                                            <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-title"><strong> <?php printf(__('%s', 'wp-food-manager'),  $field['label']); ?></strong></p>
                                                                    <p class="wpfm-additional-info-block-textarea-text"><?php printf(__('%s', 'wp-food-manager'),  $field_value); ?></p>
                                                                </div>
                                                            </div>
                                                        <?php elseif ($field['type'] == 'multiselect') : ?>
                                                            <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <?php
                                                                    $my_value_arr = [];
                                                                    if (isset($field_value) && !empty($field_value) && is_array($field_value)) {
                                                                        foreach ($field_value as $key => $my_value) {
                                                                            if (isset($field['options'][$my_value])) {
                                                                                $my_value_arr[] = $field['options'][$my_value];
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', '-food-manager'),  $field['label']); ?> -</strong> <?php printf(__('%s', 'wp-food-manager'),  implode(', ', $my_value_arr)); ?></p>
                                                                </div>
                                                            </div>

                                                        <?php elseif ($field['type'] == 'select') : ?>
                                                            <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $field['label']); ?> - </strong>
                                                                        <?php
                                                                        if (isset($field['options'][$field_value]))
                                                                            printf(__('%s', 'wp-food-manager'),  $field['options'][$field_value]);
                                                                        else
                                                                            printf(__('%s', 'wp-food-manager'), $field_value);
                                                                        ?></p>
                                                                </div>
                                                            </div>

                                                        <?php elseif (isset($field['type']) && $field['type'] == 'date') :
                                                            if (is_array($field_value)) {
                                                                $field_value = $field_value['0'];
                                                            } ?>
                                                            <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $field['label']); ?> - </strong> <?php echo date_i18n($date_format, strtotime($field_value)); ?></p>
                                                                </div>
                                                            </div>

                                                        <?php elseif (isset($field['type']) && $field['type'] == 'time') : ?>
                                                            <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $field['label']); ?> - </strong> <?php echo date($time_format, strtotime($field_value)); ?></p>
                                                                </div>
                                                            </div>

                                                        <?php elseif ($field['type'] == 'file') : ?>
                                                            <div class="wpfm-col-md-12 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left" style="margin-bottom: 20px;">
                                                                <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $field['label']); ?> - </strong></p>
                                                                <div class="wpfm-additional-info-block-details-content-items wpfm-additional-file-slider">
                                                                    <?php if (is_array($field_value)) : ?>
                                                                        <div class="wpfm-img-multi-container">
                                                                            <?php foreach ($field_value as $file) : ?>
                                                                                <?php
                                                                                if (!empty($file)) {
                                                                                    if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
                                                                                        <div class="wpfm-img-multiple"><img src="<?php echo esc_attr($file); ?>"></div>
                                                                                    <?php else : ?>
                                                                                        <div>
                                                                                            <div class="wpfm-icon">
                                                                                                <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr(wp_basename($file)); ?></strong></p>
                                                                                                <a target="_blank" href="<?php echo esc_attr($file); ?>"><i class='wpfm-icon-download3' style='margin-right: 3px;'></i> <?php _e('Download', 'wp-food-manager'); ?></a>
                                                                                            </div>
                                                                                        </div>
                                                                                <?php endif;
                                                                                }
                                                                                ?>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php else : ?>
                                                                        <?php if (in_array(pathinfo($field_value, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) : ?>
                                                                            <div class="wpfm-img-single"><img src="<?php echo esc_attr($field_value); ?>"></div>
                                                                            <?php else :
                                                                            if (wpfm_begnWith($field_value, "http")) { ?>
                                                                                <div class="wpfm-icon">
                                                                                    <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr(wp_basename($field_value)); ?></strong></p><a target="_blank" href="<?php echo esc_attr($field_value); ?>"><i class='wpfm-icon-download3' style='margin-right: 3px;'></i> <?php _e('Download', 'wp-food-manager'); ?></a>
                                                                                </div>
                                                                        <?php }
                                                                        endif; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                        <?php elseif ($field['type'] == 'url') : ?>
                                                            <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-textarea-text">
                                                                        <?php if (isset($field_value) && !empty($field_value)) { ?>
                                                                            <a target="_blank" href="<?php echo esc_url($field_value); ?>"><?php printf(__('%s', 'wp-food-manager'),  $field['label']); ?></a>
                                                                        <?php } else {
                                                                            printf(__('%s', 'wp-food-manager'),  $field['label']);
                                                                        } ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        <?php elseif ($field['type'] == 'radio' && array_key_exists('options', $field)) : ?>
                                                            <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr($field['label']); ?> -</strong> <?php echo isset($field['options'][$field_value]) ? esc_attr($field['options'][$field_value]) : ''; ?></p>
                                                                </div>
                                                            </div>
                                                        <?php elseif ($field['type'] == 'term-checklist' && array_key_exists('taxonomy', $field)) : ?>
                                                            <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-title"><strong><?php printf(__('%s', 'wp-food-manager'),  $field['label']); ?> - </strong>
                                                                        <?php
                                                                        $terms = wp_get_post_terms($post->ID, $field['taxonomy']);
                                                                        $term_checklist = '';
                                                                        if (!empty($terms)) :
                                                                            $numTerm = count($terms);
                                                                            $i = 0;
                                                                            foreach ($terms as $term) :
                                                                                $term_checklist .= $term->name;
                                                                                if ($numTerm > ++$i)
                                                                                    $term_checklist .= ', ';
                                                                            endforeach;
                                                                        endif;
                                                                        echo esc_attr($term_checklist); ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        <?php elseif ($field['type'] == 'checkbox' && array_key_exists('options', $field)) : ?>
                                                            <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-textarea-text">
                                                                        <strong><?php echo esc_attr($field['label']); ?></strong> -
                                                                        <?php
                                                                        if (is_array($field_value)) {
                                                                            $my_check_value_arr = [];
                                                                            foreach ($field_value as $key => $my_value) {
                                                                                $my_check_value_arr[] = $field['options'][$my_value];
                                                                            }
                                                                            printf(__('%s', 'wp-food-manager'),  implode(', ', $my_check_value_arr));
                                                                        } else {
                                                                            if ($field_value == 1) {
                                                                                echo esc_attr("Yes");
                                                                            } else {
                                                                                echo esc_attr("No");
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        <?php elseif ($field['type'] == 'term-select') : ?>
                                                            <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
                                                                <div class="wpfm-additional-info-block-details-content-items">
                                                                    <p class="wpfm-additional-info-block-textarea-text">
                                                                        <strong><?php echo esc_attr($field['label']); ?></strong> -
                                                                        <?php
                                                                        if ($field['taxonomy'] == 'food_manager_tag') {
                                                                            display_food_tag();
                                                                        } elseif ($field['taxonomy'] == 'food_manager_category') {
                                                                            display_food_category();
                                                                        } elseif ($field['taxonomy'] == 'food_manager_type') {
                                                                            display_food_type();
                                                                        } else {
                                                                            echo esc_attr($field_value);
                                                                        }
                                                                        ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        <?php else : ?>
                                                            <?php
                                                            if ($field_value) {
                                                                if (is_array($field_value)) : ?>
                                                                    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                            <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr($field['label']); ?> -</strong> <?php echo  esc_attr(implode(', ', $field_value)); ?></p>
                                                                        </div>
                                                                    </div>
                                                                <?php else : ?>
                                                                    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
                                                                        <div class="wpfm-additional-info-block-details-content-items">
                                                                            <p class="wpfm-additional-info-block-title"><strong><?php echo esc_attr($field['label']); ?> -</strong> <?php echo esc_attr($field_value); ?></p>
                                                                        </div>
                                                                    </div>
                                                            <?php endif;
                                                            }
                                                            ?>
                                                        <?php endif; ?>

                                                    <?php endif; ?>

                                                <?php endforeach; ?>

                                            </div>

                                            <?php do_action('single_food_additional_details_end'); ?>

                                        </div>

                                    </div>
                            <?php endif;

                                $additional_fields_extra_topping = [];
                                if (!empty($custom_fields) && isset($custom_fields) && !empty($custom_fields['extra_options'])) {
                                    foreach ($custom_fields['extra_options'] as $field_name => $field_data) {
                                        if (!array_key_exists($field_name, $default_fields['extra_options'])) {
                                            $meta_key = '_' . $field_name;
                                            $field_value = $food->$meta_key;

                                            if (isset($field_value)) {
                                                $additional_fields_extra_topping[$field_name] = $field_data;
                                            }
                                        }
                                    }

                                    if (isset($additional_fields_extra_topping['attendee_information_type']))
                                        unset($additional_fields_extra_topping['attendee_information_type']);

                                    if (isset($additional_fields_extra_topping['attendee_information_fields']))
                                        unset($additional_fields_extra_topping['attendee_information_fields']);

                                    $additional_fields_extra_topping = apply_filters('food_manager_show_additional_details_fields', $additional_fields_extra_topping);
                                }
                            endif; ?>

                            <!-- Additional Info Block End  -->
                            <?php do_action('single_food_overview_after'); ?>
                        </div>
                        <div class="wpfm-col-xs-12 wpfm-col-sm-5 wpfm-col-md-4 wpfm-single-food-right-content">
                            <div class="wpfm-single-food-body-sidebar">
                                <?php do_action('single_food_listing_button_start'); ?>

                                <?php do_action('single_food_listing_button_end'); ?>

                                <div class="wpfm-single-food-sidebar-info">

                                    <?php do_action('single_food_sidebar_start'); ?>
                                    <?php if (get_option('food_manager_enable_food_types') && get_food_type()) : ?>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Types', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-type"><?php display_food_type(); ?></div>
                                    <?php endif; ?>
                                    <?php if (get_option('food_manager_enable_food_tags') && get_food_tag()) : ?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Tags', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-tag"><?php display_food_tag(); ?></div>
                                    <?php endif; ?>

                                    <?php if (get_option('food_manager_enable_categories') && get_food_category()) : ?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Category', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-category"><?php display_food_category(); ?></div>
                                    <?php endif; ?>

                                    <?php if (get_food_units()) : ?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Units', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-units"><?php display_food_units(); ?></div>
                                    <?php endif; ?>

                                    <?php do_action('single_food_sidebar_end'); ?>
                                </div>
                                <?php
                                $is_friend_share = apply_filters('food_manager_food_friend_share', true);

                                if ($is_friend_share) : ?>
                                    <div class="wpfm-share-this-food">
                                        <h3 class="wpfm-heading-text"><?php _e('Share With Friends', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-share-lists">
                                            <?php do_action('single_food_listing_social_share_start'); ?>
                                            <div class="wpfm-social-icon wpfm-facebook">
                                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php display_food_permalink(); ?>" title="Share this page on Facebook">Facebook</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-twitter">
                                                <a href="https://twitter.com/share?text=twitter&url=<?php display_food_permalink(); ?>" title="Share this page on Twitter">Twitter</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-linkedin">
                                                <a href="https://www.linkedin.com/sharing/share-offsite/?&url=<?php display_food_permalink(); ?>" title="Share this page on Linkedin">Linkedin</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-xing">
                                                <a href="https://www.xing.com/spi/shares/new?url=<?php display_food_permalink(); ?>" title="Share this page on Xing">Xing</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-pinterest">
                                                <a href="https://pinterest.com/pin/create/button/?url=<?php display_food_permalink(); ?>" title="Share this page on Pinterest">Pinterest</a>
                                            </div>
                                            <?php do_action('single_food_listing_social_share_end'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

                <?php
                // get_food_manager_template_part('content', 'single-food_listing-organizer');
                /**
                 * single_food_listing_end hook
                 */
                do_action('single_food_listing_end');
                ?>
            <?php endif; ?>
            <!-- Main if condition end -->
            </div>
            <!-- / wpfm-wrapper end  -->

    </div>
    <!-- / wpfm-main end  -->
</div>
<!-- override the script if needed -->

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('.wpfm-single-food-slider, .wpfm-img-multi-container').slick({
            dots: true,
            infinite: true,
            speed: 500,
            adaptiveHeight: true,
        });
    });
</script>