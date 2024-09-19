<?php $thumbnail_option = get_option('food_manager_enable_thumbnail');
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
                    <a href="javascript:void(0)" class="wpfm-modal-close" id="wpfm-modal-close">x</a>
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
                    echo sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', esc_attr($formatted_regular_price));
                } ?>
            </div>
            <?php do_action('wpfm_food_menu_popup_price_after', $food_id); ?>
            <div id="wpfm_food_menu_modal_description" class="wpfm-food-modal-food_description">
                <?php echo wp_kses_post($food->post_content);?>
            </div>
            <?php do_action('wpfm_food_menu_popup_after', $food_id, $quantity, $product_id); ?>
            
        </div>
    </div>
</div>
<a href="#">
    <div class="wpfm-modal-overlay"></div>
</a>