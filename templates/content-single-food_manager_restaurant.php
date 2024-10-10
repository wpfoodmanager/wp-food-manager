<?php
global $post;
$id = isset($menu_id) ? $menu_id : get_the_ID();



// Get the featured image URL
$featured_img_url = get_the_post_thumbnail_url($id, 'full');
$thumbnail_option = get_option('food_manager_enable_thumbnail');

if (empty($featured_img_url)) {
    $featured_img_url = '';
}

// Get term details
$term = get_queried_object();
$term_id = !empty($term) ? get_post_meta($term->ID, '_food_item_cat_ids', true) : '';
$term_name = !empty($term_id[0]) ? get_term($term_id[0])->name : '';
$image_id = !empty($term_id) ? get_term_meta($term_id[0], 'food_cat_image_id', true) : '';
$image_url = !empty($image_id) ? wp_get_attachment_image_src($image_id, 'full') : '';

// Check if post type is 'food_manager_menu' or specific menu ID
if ('food_manager_menu' == get_post_type() || (isset($menu_id) && !empty($menu_id))) {
    $featured_img_url = get_the_post_thumbnail_url($id, 'full');
}
?>
<div class="wpfm-main wpfm-single-food-menu-page wpfm-accordion-body">
    <?php 
    if (empty($_GET['search_term'])): 
        if ('food_manager_menu' == get_post_type($id)) { ?>
            <h2 class="wpfm-heading-text">
                <?php 
                echo get_the_title($id);
                $wpfm_radio_icons = get_post_meta($id, 'wpfm_radio_icons', true);
                $data_food_menu2 = ucwords(str_replace(["wpfm-menu-", "dashicons-"], "", $wpfm_radio_icons));
                
                if (strpos($wpfm_radio_icons, "dashicons") === 0) {
                    if ($wpfm_radio_icons) {
                        echo "<span class='wpfm-front-radio-icon food-icon' data-food-menu='" . esc_attr($data_food_menu2) . "'><span class='wpfm-menu dashicons " . esc_attr($wpfm_radio_icons) . "'></span></span>";
                    }
                } else {
                    if ($wpfm_radio_icons) {
                        $icon_classes = 'wpfm-menu ' . esc_attr($wpfm_radio_icons);
                        if ($wpfm_radio_icons == 'wpfm-menu-fast-cart') {
                            $icon_classes .= ' path1 path2 path3 path4 path5';
                        } elseif ($wpfm_radio_icons == 'wpfm-menu-rice-bowl') {
                            $icon_classes .= ' path1 path2 path3 path4';
                        }
                        echo "<span class='wpfm-front-radio-icon food-icon' data-food-menu='" . esc_attr($data_food_menu2) . "'><span class='" . esc_attr($icon_classes) . "'></span></span>";
                    }
                }
                ?>
            </h2>
        <?php }
        if (!empty($featured_img_url)) {
            echo "<div class='wpfm-single-food-menu-category-banner'>";
                echo "<div class='wpfm-single-food-menu-category-title'>" . esc_html($term_name) . "</div>";
                echo "<img src='" . esc_url($featured_img_url) . "' alt='" . esc_attr($term_name) . "'>";
            echo "</div>";
        } elseif (!empty($image_url) && is_array($image_url)) {
            echo "<div class='wpfm-single-food-menu-category-banner'>";
                echo "<div class='wpfm-single-food-menu-category-title'>" . esc_html($term_name) . "</div>";
                echo "<img src='" . esc_url($image_url[0]) . "' alt='" . esc_attr($term_name) . "'>";
            echo "</div>";
        } elseif (!empty($term_name)) {
            echo "<h2>" . esc_html($term_name) . "</h2>";
        }
    endif; 
    // Retrieve food menu IDs and redirect option
    $food_menu_ids = $food_redirect_option = '';
    if ('food_manager_menu' == get_post_type()) {
        $food_menu_ids = get_post_meta($post->ID, '_food_item_ids', true);
        $food_redirect_option = get_post_meta($post->ID, '_wpfm_disable_food_redirect', true);
    } elseif (isset($menu_id) && !empty($menu_id)) {
        $food_menu_ids = get_post_meta($menu_id, '_food_item_ids', true);
        $food_redirect_option = get_post_meta($menu_id, '_wpfm_disable_food_redirect', true);
    }
    
    if (isset($menu_search)) {
        $food_menu_ids = $menu_search;
    }
    
    if (!empty($food_menu_ids)) {
        $food_listings = get_posts(array(
            'include'   => $food_menu_ids,
            'post_type' => 'food_manager',
            'orderby'   => 'post__in',
        ));
        echo "<div class='fm-food-menu-container'>";
            foreach ($food_listings as $food_listing) {
                $price_decimals = wpfm_get_price_decimals();
                $price_format = get_food_manager_price_format();
                $price_thousand_separator = wpfm_get_price_thousand_separator();
                $price_decimal_separator = wpfm_get_price_decimal_separator();
                $menu_food_desc = '';
                $sale_price = get_post_meta($food_listing->ID, '_food_sale_price', true);
                $regular_price = get_post_meta($food_listing->ID, '_food_price', true);
                $food_label = get_post_meta($food_listing->ID, '_food_label', true);
                $featured_img = get_the_post_thumbnail_url($food_listing->ID, 'full') ?: apply_filters('wpfm_default_food_banner', esc_url(WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg'));
                
                $formatted_sale_price = !empty($sale_price) ? number_format($sale_price, $price_decimals, $price_decimal_separator, $price_thousand_separator) : '';
                $formatted_regular_price = !empty($regular_price) ? number_format($regular_price, $price_decimals, $price_decimal_separator, $price_thousand_separator) : '';

                $food_menu_permalink = $food_redirect_option == 'yes' ? '#' : esc_url(get_permalink($food_listing->ID));
                $food_menu_return_false = $food_redirect_option == 'yes' ? 'onclick="return false;"' : '';

                if (!empty($food_listing->post_content)) {
                    $menu_food_desc = "<p class='fm-food-menu-desc'>" . wp_kses_post($food_listing->post_content) . "</p>";
                }

                echo "<div class='food-list-box'>";            
                    if ($thumbnail_option != 'thumbnail_disabled') {
                        echo "<div class='wpfm-food-list-box-image-col wpfm-food-image-" . esc_attr($thumbnail_option) . "'><img src='" . esc_url($featured_img) . "' alt='" . esc_html($food_listing->post_title) . "'></div>";
                    }

                    echo "<div class='wpfm-food-list-box-content-col'>";
                        if (!empty($food_label)) {
                            echo "<div class='food-menu-label'>" . esc_html($food_label) . "</div>";
                        }

                        echo "<a href='" . $food_menu_permalink . "'>";
                        
                            echo "<h3 class='fm-food-menu-title'>" . esc_html($food_listing->post_title) . "</h3>";
                            echo "<div class='fm-food-menu-pricing'>";
                                if (!empty($regular_price) && !empty($sale_price)) {
                                    $food_regular_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_sale_price);
                                    $food_sale_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_regular_price);
                                    echo "<del> " . $food_sale_price . "</del> <ins><span class='food-manager-Price-currencySymbol'><strong>" . $food_regular_price . "</strong></span></ins>";
                                } elseif (!empty($regular_price)) {
                                    echo sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_regular_price);
                                }
                            echo "</div>";
                        echo "</a>";
                        echo $menu_food_desc;
                        do_action('food_menu_list_overview_after', $food_listing->ID);
                    echo "</div>";
                echo "</div>";
            }
        echo "</div>";
    }
    ?>
</div>
<!-- add to cart notification -->
<?php do_action('wpfm_food_manager_food_menu_listing_after'); ?>
