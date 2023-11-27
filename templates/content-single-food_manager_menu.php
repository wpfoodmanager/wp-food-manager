<?php
global $post;
//echo $post->ID;
$featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
if (isset($featured_img_url) && empty($featured_img_url)) {
    $featured_img_url = '';
} else {
    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
}

$term = get_queried_object();
$term_id = !empty($term) ? get_post_meta($term->ID, '_food_item_cat_ids', true) : '';
$term_name = !empty($term_id[0]) ? get_term($term_id[0])->name : '';
$image_id = !empty($term_id) ? get_term_meta($term_id[0], 'food_cat_image_id', true) : '';
$image_url = wp_get_attachment_image_src($image_id, 'full');


if ( 'food_manager_menu' == get_post_type() ) {
    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
} elseif(isset($menu_id) && !empty($menu_id) ) {
    $featured_img_url = get_the_post_thumbnail_url($menu_id, 'full');
}
?>

<div class="wpfm-main wpfm-single-food-menu-page wpfm-accordion-body">

    <?php if ( 'food_manager_menu' == get_post_type() ) { 
        the_content(); ?>
        <h3>
            <?php the_title();
            $wpfm_radio_icons = get_post_meta(get_the_ID(), 'wpfm_radio_icons', true);
            $without_food_str = str_replace("wpfm-menu-", "", $wpfm_radio_icons);
            $without_dashicons_str = str_replace("dashicons-", "", $wpfm_radio_icons);
            $data_food_menu = ucwords(str_replace("-", " ", $without_dashicons_str));
            $data_food_menu2 = ucwords(str_replace("-", " ", $without_food_str));
            if (wpfm_begnWith($wpfm_radio_icons, "dashicons")) {
                if ($wpfm_radio_icons) {
                    echo "<span class='wpfm-front-radio-icon food-icon' data-food-menu='" . esc_attr($data_food_menu2) . "'><span class='wpfm-menu dashicons " . esc_attr($wpfm_radio_icons) . "'></span></span>";
                }
            } else {
                if ($wpfm_radio_icons) {
                    if ($wpfm_radio_icons == 'wpfm-menu-fast-cart') {
                        echo '<span class="wpfm-front-radio-icon food-icon" data-food-menu="' . esc_attr($data_food_menu2) . '"><span class="wpfm-menu ' . esc_attr($wpfm_radio_icons) . '"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></span></span>';
                    } elseif ($wpfm_radio_icons == 'wpfm-menu-rice-bowl') {
                        echo '<span class="wpfm-front-radio-icon food-icon" data-food-menu="' . esc_attr($data_food_menu2) . '"><span class="wpfm-menu ' . esc_attr($wpfm_radio_icons) . '"><span class="path1"></span><span class "path2"></span><span class="path3"></span><span class="path4"></span></span></span>';
                    } else {
                        echo "<span class 'wpfm-front-radio-icon food-icon' data-food-menu='" . esc_attr($data_food_menu2) . "'><span class='wpfm-menu " . esc_attr($wpfm_radio_icons) . "'></span></span>";
                    }
                }
            }
            ?>
        </h3>
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
    } elseif (!empty($term_name) && is_array($term_name)) {
        echo "<h2>" . esc_html($term_name) . "</h2>";
    }

    if ( 'food_manager_menu' == get_post_type() ) {
        $po_ids = get_post_meta($post->ID, '_food_item_ids', true);
    } elseif(isset($menu_id) && !empty($menu_id) ){
        $po_ids = get_post_meta($menu_id, '_food_item_ids', true);
    }

   
    if (!empty($po_ids)) {
        $food_listings = get_posts(array(
            'include'   => implode(",", $po_ids),
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
            
            $formatted_sale_price = ''; // Initialize the variable
            $formatted_regular_price = ''; // Initialize the variable

            if (!empty($sale_price)) {
                $formatted_sale_price = number_format($sale_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
            }
            if (!empty($regular_price)) {
                $formatted_regular_price = number_format($regular_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
            }
            if (!empty($food_listing->post_content)) {
                $menu_food_desc = "<div class='fm-food-menu-desc'>" . wp_kses_post($food_listing->post_content) . "</div>";
            }
            echo "<div class='food-list-box'>";
            echo "<a href='" . esc_url(get_permalink($food_listing->ID)) . "'>";
            echo "<div class='fm-food-menu-title'><strong>" . esc_html($food_listing->post_title) . "</strong></div>";
            echo "<div class='fm-food-menu-pricing'>";

            if (!empty($regular_price) && !empty($sale_price)) {
                $f_regular_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_sale_price);
                $f_sale_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_regular_price);
                echo "<del> " . $f_sale_price . "</del> <ins><span class='food-manager-Price-currencySymbol'><strong>" . $f_regular_price . "</strong></span></ins>";
            } elseif (!empty($regular_price)) {
                echo sprintf($price_format, '<span class="food-manager-Price-currencySymbol">' . esc_html(get_food_manager_currency_symbol()) . '</span>', $formatted_regular_price);
            }
            echo "</div>";
            echo "</a>";
            echo $menu_food_desc;
            echo "</div>";
        }
        echo "</div>";
    }
    ?>
</div>
