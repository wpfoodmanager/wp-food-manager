<?php wp_enqueue_script('wpfm-ajax-filters'); ?>
<?php do_action('food_manager_food_filters_before', $atts); ?>

<form class="wpfm-main wpfm-form-wrapper wpfm-food-filter-wrapper food_filters" id="food_filters">
	<?php do_action('food_manager_food_filters_start', $atts); ?>

	<div class="search_foods search-form-container">
		<?php do_action('food_manager_food_filters_search_foods_start', $atts); ?>

		<div class="wpfm-row">
			<!-- Search by keywords section start -->
			<div class="wpfm-col">
				<!-- shows default keywords text field  start-->
				<div class="wpfm-form-group">
					<label for="search_keywords" class="wpfm-form-label"><?php _e('Keywords', 'wp-food-manager'); ?></label>
					<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php echo esc_attr__('Keywords', 'wp-food-manager'); ?>" value="<?php echo esc_attr($keywords); ?>" />
				</div>
				<!-- shows default keywords text field end -->
			</div>
			<!-- Search by keywords section end-->
			<?php
			if ($food_menu_query) {
				// echo '<pre>'; print_r($food_menu_query); echo '</pre>';
				?>
				<!-- Search by food menu section start -->
				<div class="wpfm-col">
					<!-- shows default food menu items text field start-->
					<div class="wpfm-form-group">
						<label for="search_food_menu" class="wpfm-form-label"><?php _e('Food Menu', 'wp-food-manager'); ?></label>

						<select name="search_food_menu[]" id="search_food_menu" class="food-manager-post_type-dropdown " <?php echo ($show_food_menu_multiselect) ? 'multiple' : ''; ?> data-placeholder="<?php echo esc_attr__('Choose a Food Menu…'); ?>" data-no_results_text="<?php echo esc_attr__('No results match'); ?>" data-multiple_text="<?php echo esc_attr__('Choose a Food Menu…'); ?>">
							<?php
							if (!$show_food_menu_multiselect) {
								echo '<option value="">' . esc_html__('Choose a Food Menu') . '</option>';
							}
							foreach($food_menu_query as $food_menu) { 
								// echo '<pre>'; print_r($food_menu); echo '</pre>';
								?>
								<option value="<?php echo esc_attr($food_menu->ID); ?>"><?php echo esc_html($food_menu->post_title); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<!-- shows default food menu items text field end -->
				</div>
				<!-- Search by food menu section end-->
				<?php
			}
			wp_reset_postdata();
			?>
			

		</div><!-- /row -->
		<div class="wpfm-row">
			<!-- Search by food categories section start -->
			<?php if ($categories) : ?>
				<?php foreach ($categories as $category) : ?>
					<input type="hidden" name="search_categories[]" value="<?php echo esc_attr(sanitize_title($category)); ?>" />
				<?php endforeach; ?>
			<?php elseif ($show_categories && !is_tax('food_manager_category') && get_terms('food_manager_category', ['hide_empty' => false])) : ?>
				<div class="wpfm-col">
					<div class="wpfm-form-group">
						<label for="search_categories" class="wpfm-form-label"><?php _e('Category', 'wp-food-manager'); ?></label>
						<?php if ($show_category_multiselect) : ?>
							<?php food_manager_dropdown_selection(array('value' => 'slug', 'taxonomy' => 'food_manager_category', 'hierarchical' => 1, 'name' => 'search_categories', 'orderby' => 'name', 'selected' => $selected_category, 'hide_empty' => false)); ?>
						<?php else : ?>
							<?php food_manager_dropdown_selection(array('value' => 'slug', 'taxonomy' => 'food_manager_category', 'hierarchical' => 1, 'show_option_all' => esc_html__('Choose a Food Category', 'wp-food-manager'), 'name' => 'search_categories', 'orderby' => 'name', 'selected' => $selected_category, 'multiple' => false, 'hide_empty' => false)); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<!-- Search by food categories section end -->
			<!-- Search by food type section start -->
			<?php if ($food_types) : ?>
				<?php foreach ($food_types as $food_type) : ?>
					<input type="hidden" name="search_food_types[]" value="<?php echo esc_attr(sanitize_title($food_type)); ?>" />
				<?php endforeach; ?>
			<?php elseif ($show_food_types && !is_tax('food_manager_type') && get_terms('food_manager_type', ['hide_empty' => false])) : ?>
				<div class="wpfm-col">
					<div class="wpfm-form-group">
						<label for="search_food_types" class="wpfm-form-label"><?php _e('food Type', 'wp-food-manager'); ?></label>
						<?php if ($show_food_type_multiselect) : ?>
							<?php food_manager_dropdown_selection(array('value' => 'slug', 'taxonomy' => 'food_manager_type', 'hierarchical' => 1, 'name' => 'search_food_types', 'orderby' => 'name', 'selected' => $selected_food_type, 'hide_empty' => false)); ?>
						<?php else : ?>
							<?php food_manager_dropdown_selection(array('value' => 'slug', 'taxonomy' => 'food_manager_type', 'hierarchical' => 1, 'show_option_all' => esc_html__('Choose a Food Type', 'wp-food-manager'), 'name' => 'search_food_types', 'orderby' => 'name', 'selected' => $selected_food_type, 'multiple' => false, 'hide_empty' => false)); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div> <!-- /row -->
		<?php do_action('food_manager_food_filters_search_foods_end', $atts); ?>
	</div>
	<?php do_action('food_manager_food_filters_end', $atts); ?>
</form>
<?php do_action('food_manager_food_filters_after', $atts); ?>
<noscript><?php echo esc_html__('Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-food-manager'); ?></noscript>