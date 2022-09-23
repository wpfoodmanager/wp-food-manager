<?php
global $post;

$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); 

if(isset($featured_img_url) && empty($featured_img_url)){
    $featured_img_url = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );
} else {
    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); 
}
?>
<div class="single_food_listing">
    <div class="wpfm-main wpfm-single-food-page">
        <div class="wpfm-single-food-wrapper">
            <div class="wpfm-single-food-body">
                <div class="wpfm-row">
                    <div class="wpfm-col-xs-12 wpfm-col-sm-12 wpfm-col-md-12 wpfm-single-food-images">
                        <div class="wpfm-food-single-image-wrapper">
                            <div class="wpfm-food-single-image">
                                <img itemprop="image" content="<?php echo esc_url($featured_img_url); ?>" src="<?php echo esc_url($featured_img_url); ?>" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="wpfm-col-xs-12 wpfm-col-sm-12 wpfm-col-md-12 wpfm-single-food-left-content">
                        <!-- <div class="wpfm-single-food-short-info">
                            <div class="wpfm-food-details">
                                <div class="wpfm-food-title">
                                    <h3 class="wpfm-heading-text"><?php the_title(); ?></h3>
                                </div>
                            </div>
                        </div> -->
                        <div class="wpfm-single-food-body-content">
                            <?php the_content(); ?>
                            
                            <h3>
                                <?php _e('Food Lists');?>
                            </h3>
                            <?php
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
                                
                                foreach ($food_listings as $food_listing) {    
                                    echo wp_kses_post("<a href='".get_permalink($food_listing->ID)."' class='food-list-box'>".esc_html($food_listing->post_title)."</a>");
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>