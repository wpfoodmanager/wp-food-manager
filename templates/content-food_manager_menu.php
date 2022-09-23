<?php
$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); 

if(isset($featured_img_url) && empty($featured_img_url)){
    $featured_img_url = apply_filters( 'wpfm_default_food_banner', WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg' );
} else {
    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); 
}
?>

<div class="wpfm-col-12">
	<div class="wpfm-row">
		<div>
			<h3>
				<?php _e('Food Lists');?>
			</h3>
			<?php
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
