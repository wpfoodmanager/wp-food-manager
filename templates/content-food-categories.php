<?php

$food_cats = get_food_listing_categories();

foreach($food_cats as $food_cat){
$image_id           = get_term_meta( $food_cat->term_id, 'thumbnail_id', true );
$post_thumbnail_img = wp_get_attachment_image_src( $image_id, 'full' );

if(isset($post_thumbnail_img) && empty($post_thumbnail_img)){
    $post_thumbnail_img = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );
} else {
    $post_thumbnail_img = $post_thumbnail_img[0];
}
?>

<div class="wpfm-food-box-col wpfm-col wpfm-col-12 wpfm-col-md-6 wpfm-col-lg-<?php echo apply_filters('food_manager_food_wpfm_column', '4'); ?>">
    <!----- wpfm-col-lg-4 value can be change by admin settings ------->
    <div class="wpfm-food-layout-wrapper">
        <!-- <div href="javascript:void(0);<?php //echo esc_url( get_term_link( $food_cat->term_id ) ); ?>" class="wpfm-food-action-url food-style-color <?php echo $food_cat->slug; ?>"> -->
            <!-- <div class="wpfm-food-banner">
                <div class="wpfm-food-banner-img" style="background-image: url('<?php echo $post_thumbnail_img ?>')">
                </div>
            </div> -->

            <div class="wpfm-food-infomation">
                <div class="wpfm-food-details">
                    <div class="wpfm-food-title"><h3 class="wpfm-heading-text"><?php echo esc_html($food_cat->name); ?> (<?php echo esc_html($food_cat->count); ?>)</h3></div>
                </div>
            </div>   
        <!-- </div> -->
    </div>
</div>
<?php 
}