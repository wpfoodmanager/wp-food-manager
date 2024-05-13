<?php
$featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
if (isset($featured_img_url) && empty($featured_img_url)) {
    $featured_img_url = apply_filters('wpfm_default_food_banner', esc_url(WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg'));
} else {
    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
}
?>
<div class="wpfm-col-12">
    <div class="wpfm-row">
        <div>
            <h3><?php esc_html_e('Food Lists'); ?></h3>
            <?php
            $food_menu_ids = get_post_meta($post->ID, '_food_item_ids', true);
            if (!empty($food_menu_ids)) {
                $food_listings = get_posts(array(
                    'include'   => implode(",", $food_menu_ids),
                    'post_type' => 'food_manager',
                    'orderby'   => 'post__in',
                ));
                foreach ($food_listings as $food_listing) {
                    echo wp_kses_post("<a href='" . esc_url(get_permalink($food_listing->ID)) . "' class='food-list-box'>" . esc_html($food_listing->post_title) . "</a>");
                }
            }
            ?>
        </div>
    </div>
</div>