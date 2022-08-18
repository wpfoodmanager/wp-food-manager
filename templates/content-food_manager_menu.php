<div class="wpfm-col-12">
	<div class="wpfm-row">
		<div>
			<h3>
				<?php _e('Food Lists');?>
			</h3>
			<?php
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
