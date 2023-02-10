<?php
global $post;

$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); 

if(isset($featured_img_url) && empty($featured_img_url)){
    //$featured_img_url = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );
    $featured_img_url = '';
} else {
    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); 
}

$term = get_queried_object();
$term_id = !empty($term) ? get_post_meta ( $term->ID, '_food_item_cat_ids', true ) : '';
$term_name = !empty($term_id[0]) ? get_term( $term_id[0] )->name : '';

$image_id = !empty($term_id) ? get_term_meta ( $term_id[0], 'food_cat_image_id', true ) : '';
$image_url = wp_get_attachment_image_src ( $image_id, 'full' );
?>

<div class="wpfm-main wpfm-single-food-menu-page">
    <?php if(!empty($featured_img_url)): ?>
        <div class="wpfm-single-food-image">
            <img itemprop="image" content="<?php echo esc_url($featured_img_url); ?>" src="<?php echo esc_url($featured_img_url); ?>" alt="">
        </div>
    <?php endif; ?>
    <?php the_content(); ?>
    
    <h3>
        <?php the_title(); 
        $wpfm_radio_icons = get_post_meta(get_the_ID(), 'wpfm_radio_icons', true); 
        $without_food_str = str_replace("wpfm-menu-", "", $wpfm_radio_icons); 
        $without_fa_str = str_replace("fa-", "", $wpfm_radio_icons); 
        $data_food_menu = ucwords(str_replace("-", " ", $without_fa_str));
        $data_food_menu2 = ucwords(str_replace("-", " ", $without_food_str));
        
        if(wpfm_begnWith($wpfm_radio_icons,"fa")){
            if($wpfm_radio_icons){ 
                echo "<span class='wpfm-front-radio-icon fa-icon' data-food-menu='".$data_food_menu."'><i class='fa ".$wpfm_radio_icons."'></i></span>"; 
            }                                    
        } else {
            if($wpfm_radio_icons){
                if($wpfm_radio_icons == 'wpfm-menu-fast-cart'){
                    echo '<span class="wpfm-front-radio-icon food-icon" data-food-menu="'.$data_food_menu2.'"><span class="wpfm-menu '.$wpfm_radio_icons.'"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></span></span>';
                } elseif($wpfm_radio_icons == 'wpfm-menu-rice-bowl'){
                    echo '<span class="wpfm-front-radio-icon food-icon" data-food-menu="'.$data_food_menu2.'"><span class="wpfm-menu '.$wpfm_radio_icons.'"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span></span>';
                } else {
                    echo "<span class='wpfm-front-radio-icon food-icon' data-food-menu='".$data_food_menu2."'><span class='wpfm-menu ".$wpfm_radio_icons."'></span></span>"; 
                }
            }
        }

        ?>
    </h3>
    <?php
    if(!empty($featured_img_url)){
        echo "<div class='wpfm-single-food-menu-category-banner' style='display: none;'>";
        echo "<div class='wpfm-single-food-menu-category-title'>".$term_name."</div>";
        echo "<img src='".$featured_img_url."' alt='".$term_name."'>";
        echo "</div>";
    } elseif(!empty($image_url) && is_array($image_url)) {
        echo "<div class='wpfm-single-food-menu-category-banner'>";
        echo "<div class='wpfm-single-food-menu-category-title'>".$term_name."</div>";
        echo "<img src='".$image_url[0]."' alt='".$term_name."'>";
        echo "</div>";
    } else {
        echo "<h2>".$term_name."</h2>";
    }
    //$term_list = get_the_terms($post->ID, 'food_manager_category');
    /*$term_lists = wp_get_post_terms( $post->ID, 'food_manager_category' );
    $term_arr = array();
    foreach ($term_lists as $key => $term_list) {
        $term_arr[] = $term_list->name;
    }
    $myposts = get_posts(array(
        'showposts' => -1,
        'post_type' => 'food_manager',
        'tax_query' => array(
            array(
            'taxonomy' => 'food_manager_category',
            'field' => 'slug',
            'terms' => $term_arr)
        ))
    );
        
    foreach ($myposts as $mypost) {
        echo wp_kses_post("<a href='".get_permalink($mypost->ID)."' class='food-list-box'>".esc_html($mypost->post_title)."</a>");
    }*/
    

    $po_ids = get_post_meta($post->ID, '_food_item_ids', true);
    if(!empty($po_ids)){
        $food_listings = get_posts( array(
            'include'   => implode(",", $po_ids),
            'post_type' => 'food_manager',
            'orderby'   => 'post__in',
        ) );
        echo "<div class='fm-food-menu-container'>";
        foreach ($food_listings as $food_listing) {
            $price_decimals = wpfm_get_price_decimals();
            $price_format = get_food_manager_price_format();
            $price_thousand_separator = wpfm_get_price_thousand_separator();
            $price_decimal_separator = wpfm_get_price_decimal_separator();
            $menu_food_desc = '';
            $sale_price = get_post_meta($food_listing->ID, '_food_sale_price', true);
            $regular_price = get_post_meta($food_listing->ID, '_food_price', true);

            if(!empty($sale_price)){
                $formatted_sale_price = number_format($sale_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
            }
            if(!empty($regular_price)){
                $formatted_regular_price = number_format($regular_price, $price_decimals, $price_decimal_separator, $price_thousand_separator);
            }
            if(!empty($food_listing->post_content)){
                $menu_food_desc = "<div class='fm-food-menu-desc'>".$food_listing->post_content."</div>";
            }
            echo wp_kses_post("<div class='food-list-box'><a href='".get_permalink($food_listing->ID)."'><div class='fm-food-menu-title'><strong>".esc_html($food_listing->post_title))."</strong></div>";
                //echo "<span class='fm-divider'> - - - - - - </span>";
                echo "<div class='fm-food-menu-pricing'>";
                if(!empty($regular_price) && !empty($sale_price)){
                    $f_regular_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">'.get_food_manager_currency_symbol().'</span>', $formatted_sale_price);
                    $f_sale_price = sprintf($price_format, '<span class="food-manager-Price-currencySymbol">'.get_food_manager_currency_symbol().'</span>', $formatted_regular_price);
                    echo "<del> ".$f_sale_price."</del> <ins><span class='food-manager-Price-currencySymbol'><strong>".$f_regular_price."</strong></ins>"; 
                }
                if(empty($regular_price) && empty($sale_price)){
                    return false;
                }
                if(empty($sale_price)){
                    echo sprintf($price_format, '<span class="food-manager-Price-currencySymbol">'.get_food_manager_currency_symbol().'</span>', $formatted_regular_price);
                }
                echo "</div>";
                echo "</a>";
                echo "$menu_food_desc";
                
            echo "</div>";
        }
        echo "</div>";
    }
    ?>
</div>
