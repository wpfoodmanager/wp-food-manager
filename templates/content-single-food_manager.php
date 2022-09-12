<?php
global $post;

do_action('set_single_listing_view_count');
?>
<div class="single_food_listing">

    <div class="wpfm-main wpfm-single-food-page">
        <?php if (get_option('food_manager_hide_expired_content', 1) && 'expired' === $post->post_status): ?>
            <div class="food-manager-info wpfm-alert wpfm-alert-danger" ><?php _e('This listing has been expired.', 'wp-food-manager'); ?></div>
        <?php else: ?>
            <?php if (is_food_cancelled()): ?>
                <div class="wpfm-alert wpfm-alert-danger">
                    <span class="food-cancelled"><?php _e('This food has been cancelled', 'wp-food-manager'); ?></span>
                </div>
    
            <?php endif; ?>
            <?php
            /**
             * single_food_listing_start hook
             */
            do_action('single_food_listing_start');
            ?>
            <div class="wpfm-single-food-wrapper">
                <div class="wpfm-single-food-header-top">
                    <div class="wpfm-row">

                        <div class="wpfm-col-xs-12 wpfm-col-sm-12 wpfm-col-md-12 wpfm-single-food-images">
                            <?php
                            $food_banners = get_food_banner();
                            if (is_array($food_banners) && sizeof($food_banners) > 1):
                                ?>
                                <div class="wpfm-single-food-slider-wrapper">
                                    <div class="wpfm-single-food-slider">
                                        <?php foreach ($food_banners as $banner_key => $banner_value): ?>
                                            <div class="wpfm-slider-items">
                                                <img src="<?php echo $banner_value; ?>" alt="<?php the_title(); ?>" />
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="wpfm-food-single-image-wrapper">
                                    <div class="wpfm-food-single-image"><?php display_food_banner(); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="wpfm-single-food-body">
                    <div class="wpfm-row">
                        <div class="wpfm-col-xs-12 wpfm-col-sm-7 wpfm-col-md-8 wpfm-single-food-left-content">
                            <?php do_action('single_food_overview_before'); ?>
                            <div class="wpfm-single-food-short-info">
                                <div class="wpfm-food-details">
                                    <?php if (get_option('food_manager_food_item_show_hide') && get_stock_status()) : ?>
                                        <div class="food-stock-status">
                                            <?php display_stock_status(); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="wpfm-food-title">
                                        <h3 class="wpfm-heading-text"><?php the_title(); ?> - <?php display_food_price_tag(); ?></h3>
                                    </div>
                                    <div class="wpfm-food-organizer">
                                        <div class="wpfm-food-organizer-name">by <?php echo get_the_author_link(); ?></div>
                                    </div>
                                    <?php
                                    $view_count = get_food_views_count($post);
                                    if ($view_count) : ?>
                                        <div class="wpfm-viewed-food wpfm-tooltip wpfm-tooltip-bottom"><i class="wpfm-icon-eye"></i> <?php echo esc_html($view_count); ?> <span class="wpfm-tooltiptext"><?php printf(__('%d people viewed this food.', 'wp-food-manager'), $view_count); ?></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="wpfm-single-food-body-content">
                                <?php do_action('single_food_overview_start'); ?>
                                <?php echo apply_filters('display_food_description', get_the_content()); ?>
                                <?php do_action('single_food_overview_end'); ?>
                            </div>
                            <div class="wpfm-single-food-body-content wpfm-extra-options">
                                <?php
                                $ext_options = get_post_meta(get_the_ID(), '_wpfm_extra_options', true);
                                $food_data_option_value_count = get_post_meta(get_the_ID(), 'wpfm_option_value_count', true);
                                $repeated_count = get_post_meta(get_the_ID(), 'wpfm_repeated_options', true);

                                if(!empty($repeated_count) || !empty($ext_options)){
                                    echo "<h1>Extra Toppings</h1>";
                                    foreach ($ext_options as $key => $ext_option) {
                                        if($ext_option['option_type'] == 'radio'){
                                            echo "<div class='wpfm-radio-options wpfm-input-field-common'>";
                                                echo '<label for="'.str_replace(" ", "-", strtolower($ext_option['option_name'])).'"><strong>'.$ext_option['option_name'].'</strong></label>';
                                                if(!empty($ext_option['option_description'])){
                                                    echo '<div class="wpfm-input-description">'.$ext_option['option_description'].'</div>';
                                                }
                                                echo '<div class="wpfm-inner-field-content">';
                                                    foreach ($ext_option['option_options'] as $key2 => $value2) {
                                                        $checked = ($value2['option_value_default']) == 'on' ? 'checked' : '';
                                                        echo "<div class='wpfm-input-singular'>";
                                                            echo '<input type="radio" id="'.esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))).'" name="'.esc_attr($key).'" value="'.esc_attr($value2['option_value_name']).'" '.$checked.'>';                                                
                                                            echo '<label for="'.esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))).'"> '.esc_html($value2['option_value_name']).' - '.get_food_manager_currency_symbol().$value2['option_value_price'].'</label>';
                                                        echo "</div>";
                                                    }
                                                echo "</div>";
                                            echo "</div>";
                                        }

                                        if($ext_option['option_type'] == 'checkbox'){
                                            echo "<div class='wpfm-checkbox-options wpfm-input-field-common'>";
                                                echo '<label for="'.str_replace(" ", "-", strtolower($ext_option['option_name'])).'"><strong>'.$ext_option['option_name'].'</strong></label>';
                                                if(!empty($ext_option['option_description'])){
                                                    echo '<div class="wpfm-input-description">'.$ext_option['option_description'].'</div>';
                                                }
                                                echo '<div class="wpfm-inner-field-content">';
                                                    foreach ($ext_option['option_options'] as $key2 => $value2) {
                                                        // echo "<pre>";
                                                        // print_r($value2);
                                                        // echo "</pre>";
                                                        $checked = ($value2['option_value_default']) == 'on' ? 'checked' : '';
                                                        echo "<div class='wpfm-input-singular'>";
                                                            echo '<input type="checkbox" id="'.esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))).'" name="'.esc_attr($key).'" value="'.esc_attr($value2['option_value_name']).'" '.$checked.'>';                                                
                                                            echo '<label for="'.esc_attr(str_replace(" ", "-", strtolower($value2['option_value_name']))).'"> '.esc_html($value2['option_value_name']).' - '.get_food_manager_currency_symbol().$value2['option_value_price'].'</label>';
                                                        echo "</div>";
                                                    }
                                                echo "</div>";
                                            echo "</div>";
                                        }

                                        if($ext_option['option_type'] == 'select'){
                                            echo "<div class='wpfm-select-options wpfm-input-field-common'>";
                                                echo '<label for="'.str_replace(" ", "-", strtolower($ext_option['option_name'])).'"><strong>'.$ext_option['option_name'].'</strong></label>';
                                                if(!empty($ext_option['option_description'])){
                                                    echo '<div class="wpfm-input-description">'.$ext_option['option_description'].'</div>';
                                                }
                                                echo '<select name="'.esc_attr($key).'">';
                                                    foreach ($ext_option['option_options'] as $key2 => $value2) {
                                                        // echo "<pre>";
                                                        // print_r($value2);
                                                        // echo "</pre>";
                                                        $selected = ($value2['option_value_default']) == 'on' ? 'selected' : '';
                                                        
                                                        echo '<option value="'.esc_attr($value2['option_value_name']).'" '.$selected.'>'.esc_attr($value2['option_value_name']).' - '.get_food_manager_currency_symbol().$value2['option_value_price'].'</option>';
                                                    }
                                                echo '</select>';
                                            echo "</div>";
                                        }
                                    }
                                }

                                /*if(!empty($repeated_count) || !empty($ext_options)){
                                    echo "<h1>Extra Options</h1>";
                                    $ext_counts = count($ext_options);
                                    for ($i=1; $i<=$ext_counts; $i++){
                                        foreach ($ext_options as $key => $ext_option) {
                                            $ext_sub_counts = count($ext_option['option_options']);

                                            // Radio button selection
                                            if($ext_option['option_type'] == 'radio'){
                                                echo "<div class='radio-options'>";
                                                    echo '<label for="'.str_replace(" ", "-", strtolower($ext_option['option_name'])).'"><strong>'.$ext_option['option_name'].'</strong></label><br>';
                                                    for ($j=1; $j<=$ext_sub_counts; $j++){
                                                        if(isset($ext_option['option_options'][$i.'_option_value_name_'.$j])){
                                                            $checked = ($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_default_'.$j]) == 'on' ? 'checked' : ''; 
                                                            echo '<input type="radio" id="'.esc_attr(str_replace(" ", "-", strtolower($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]))).'" name="'.esc_attr($key).'" value="'.esc_attr($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]).'" '.$checked.'>';
                                                            echo '<label for="'.esc_attr(str_replace(" ", "-", strtolower($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]))).'"> '.esc_html($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]).' - '.get_food_manager_currency_symbol().$ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_price_'.$j].'</label><br>';
                                                        }
                                                    }
                                                echo "</div>";
                                            }

                                            // Select Dropdown
                                            if($ext_option['option_type'] == 'select'){
                                                echo "<div class='select-options'>";
                                                    echo '<label for="'.str_replace(" ", "-", strtolower($ext_option['option_name'])).'"><strong>'.$ext_option['option_name'].'</strong></label><br>';
                                                    $select_checked = ($ext_option['option_required'] == "yes") ? "required" : "";
                                                    echo '<select name="'.esc_attr($key).'" '.$select_checked.'>';
                                                        for ($j=1; $j<=$ext_sub_counts; $j++){
                                                            if(isset($ext_option['option_options'][$i.'_option_value_name_'.$j])){
                                                                
                                                                $selected = ($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_default_'.$j]) == 'on' ? 'selected' : '';
                                                                echo '<option value="'.esc_attr($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]).'" '.$selected.'>'.esc_attr($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]).' - '.get_food_manager_currency_symbol().$ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_price_'.$j].'</option>';
                                                            }
                                                        }
                                                    echo "</select>";
                                            echo "</div>";
                                            }

                                            // Checkbox Selection
                                            if($ext_option['option_type'] == 'checkbox'){
                                                echo "<div class='checkbox-options'>";
                                                    echo '<label for="'.str_replace(" ", "-", strtolower($ext_option['option_name'])).'"><strong>'.$ext_option['option_name'].'</strong></label><br>';
                                                    for ($j=1; $j<=$ext_sub_counts; $j++){
                                                        if(isset($ext_option['option_options'][$i.'_option_value_name_'.$j])){
                                                            $checkbox_checked = ($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_default_'.$j]) == 'on' ? 'checked' : '';
                                                            echo '<input type="checkbox" id="'.esc_attr(str_replace(" ", "-", strtolower($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]))).'" name="'.esc_attr($key).'" value="'.esc_attr($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]).'" '.$checkbox_checked.'>';
                                                            echo '<label for="'.esc_attr(str_replace(" ", "-", strtolower($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]))).'"> '.esc_html($ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_name_'.$j]).' - '.get_food_manager_currency_symbol().$ext_option['option_options'][$i.'_option_value_name_'.$j][$i.'_option_value_price_'.$j].'</label><br>';
                                                        }
                                                    }
                                                echo "</div>";
                                            }
                                        }
                                    }
                                }*/
                                ?>
                            </div>
                            <?php do_action('single_food_overview_after'); ?>
                        </div>
                        <div class="wpfm-col-xs-12 wpfm-col-sm-5 wpfm-col-md-4 wpfm-single-food-right-content">
                            <div class="wpfm-single-food-body-sidebar">
                                <?php do_action('single_food_listing_button_start'); ?>

                                
                       

                                <?php do_action('single_food_listing_button_end'); ?>

                                <div class="wpfm-single-food-sidebar-info">

                                    <?php do_action('single_food_sidebar_start'); ?>
                                    <?php if (get_option('food_manager_enable_food_types') && get_food_type()) :?>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Types', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-type"><?php display_food_type(); ?></div>
                                    <?php endif; ?>
                                    <?php if (get_option('food_manager_enable_food_tags') && get_food_tag()) :?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Tags', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-tag"><?php display_food_tag(); ?></div>
                                    <?php endif; ?>

                                    <?php if (get_option('food_manager_enable_categories') && get_food_category()) : ?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Category', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-category"><?php display_food_category(); ?></div>
                                    <?php endif; ?>

                                    <?php if (get_option('food_manager_enable_food_ingredients') && get_food_ingredients() && get_post_meta($post->ID, '_enable_food_ingre', true)) : ?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Ingredients', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-ingredients"><?php display_food_ingredients(); ?></div>
                                    <?php endif; ?>

                                    <?php if (get_option('food_manager_enable_food_nutritions') && get_food_nutritions() && get_post_meta($post->ID, '_enable_food_nutri', true)) : ?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Nutritions', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-nutritions"><?php display_food_nutritions(); ?></div>
                                    <?php endif; ?>

                                    <?php if (get_option('food_manager_enable_food_units') && get_food_units()) : ?>
                                        <div class="clearfix">&nbsp;</div>
                                        <h3 class="wpfm-heading-text"><?php _e('Food Units', 'wp-food-manager'); ?></h3>
                                        <div class="wpfm-food-units"><?php display_food_units(); ?></div>
                                    <?php endif; ?>

                                    <?php do_action('single_food_sidebar_end'); ?>
                                </div>
                                <?php
                                $is_friend_share = apply_filters('food_manager_food_friend_share', true);

                                if ($is_friend_share): ?>
                                    <h3 class="wpfm-heading-text"><?php _e('Share With Friends', 'wp-food-manager'); ?></h3>
                                    <div class="wpfm-share-this-food">
                                        <div class="wpfm-food-share-lists">
                                            <?php do_action('single_food_listing_social_share_start'); ?>
                                            <div class="wpfm-social-icon wpfm-facebook">
                                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php display_food_permalink(); ?>"
                                                   title="Share this page on Facebook">Facebook</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-twitter">
                                                <a href="https://twitter.com/share?text=twitter&url=<?php display_food_permalink(); ?>"
                                                   title="Share this page on Twitter">Twitter</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-linkedin">
                                                <a href="https://www.linkedin.com/sharing/share-offsite/?&url=<?php display_food_permalink(); ?>"
                                                   title="Share this page on Linkedin">Linkedin</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-xing">
                                                <a href="https://www.xing.com/spi/shares/new?url=<?php display_food_permalink(); ?>"
                                                   title="Share this page on Xing">Xing</a>
                                            </div>
                                            <div class="wpfm-social-icon wpfm-pinterest">
                                                <a href="https://pinterest.com/pin/create/button/?url=<?php display_food_permalink(); ?>"
                                                   title="Share this page on Pinterest">Pinterest</a>
                                            </div>
                                            <?php do_action('single_food_listing_social_share_end'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

                <?php
               // get_food_manager_template_part('content', 'single-food_listing-organizer');
                /**
                 * single_food_listing_end hook
                 */
                do_action('single_food_listing_end');
                ?>
            <?php endif; ?>
            <!-- Main if condition end -->
        </div>
        <!-- / wpfm-wrapper end  -->

    </div>
    <!-- / wpfm-main end  -->
</div>
<!-- override the script if needed -->

<script type="text/javascript">
    jQuery(document).ready(function ()
    {
        jQuery('.wpfm-single-food-slider').slick({
            dots: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            responsive: [{
                    breakpoint: 992,
                    settings: {
                        dots: true,
                        infinite: true,
                        speed: 500,
                        fade: true,
                        cssEase: 'linear',
                        adaptiveHeight: true
                    }
                }]
        });

    });
</script>
