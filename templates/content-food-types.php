<?php
$food_cats = get_food_listing_taxonomy('food_manager_type');
foreach ($food_cats as $food_cat) {
    $image_id           = get_term_meta($food_cat->term_id, 'thumbnail_id', true);
    $post_thumbnail_img = wp_get_attachment_image_src($image_id, 'full');
    if (isset($post_thumbnail_img) && empty($post_thumbnail_img)) {
        $post_thumbnail_img = apply_filters('wpfm_default_food_banner', esc_url(WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg'));
    } else {
        $post_thumbnail_img = $post_thumbnail_img[0];
    }
?>
    <div class="wpfm-food-box-col wpfm-col wpfm-col-12 wpfm-col-md-6 wpfm-col-lg-<?php echo esc_attr(apply_filters('food_manager_food_wpfm_column', '4')); ?>">
        <!----- wpfm-col-lg-4 value can be change by admin settings ------->
        <div class="wpfm-food-layout-wrapper">
            <div class="wpfm-food-infomation">
                <div class="wpfm-food-details">
                    <div class="wpfm-food-title">
                        <h3 class="wpfm-heading-text"><?php echo esc_html($food_cat->name); ?> (<?php echo esc_html($food_cat->count); ?>)</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>