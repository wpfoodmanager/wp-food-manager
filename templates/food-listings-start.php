<?php wp_enqueue_script(esc_attr('wpfm-content-food-listing')); 
    $title = !empty($title) ? $title : '';
?>

<?php if (esc_attr($layout_type) == 'all') : ?>
    <div class="wpfm-main wpfm-food-listings-header">
        <div class="wpfm-row">
            <div class="wpfm-col wpfm-col-12 wpfm-col-sm-6 wpfm-col-md-6 wpfm-col-lg-8">
            <div class="wpfm-food-listing-header-title">
                <h2 class="wpfm-heading-text"><?php echo esc_html($title, 'wp-food-manager'); ?></h2>
            </div>
            </div>
            <div class="wpfm-col wpfm-col-12 wpfm-col-sm-6 wpfm-col-md-6 wpfm-col-lg-4">
                <div class="wpfm-food-layout-action-wrapper">
                    <div class="wpfm-food-layout-action">
                        <?php do_action('start_food_listing_layout_icon'); ?>
                        <div class="wpfm-food-layout-icon wpfm-food-box-layout" title="<?php esc_attr_e('foods Box View', 'wp-food-manager'); ?>" id="wpfm-food-box-layout"><i class="wpfm-icon-stop2"></i></div>
                        <div class="wpfm-food-layout-icon wpfm-food-list-layout wpfm-active-layout" title="<?php esc_attr_e('foods List View', 'wp-food-manager'); ?>" id="wpfm-food-list-layout"><i class="wpfm-icon-menu"></i></div>
                        <?php do_action('end_food_listing_layout_icon'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- food listing view -->
<?php
if (esc_attr($layout_type) == 'box')
    $list_type_class = 'wpfm-row wpfm-food-listing-box-view';
else
    $list_type_class = 'wpfm-food-listing-list-view';
$list_type_class = apply_filters('wpfm_default_listing_layout_class', $list_type_class, $layout_type); ?>
<div id="food-listing-view" class="wpfm-main wpfm-food-listings food_listings <?php echo esc_attr($list_type_class); ?>">