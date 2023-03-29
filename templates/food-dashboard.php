<?php do_action('food_manager_food_dashboard_before'); ?>
<div id="food-manager-food-dashboard">
	<div class="wpfm-dashboard-main-header">
		<div class="wpfm-dashboard-main-title wpfm-dashboard-main-filter">
			<h3 class="wpfm-theme-text"><?php _e('Food Dashboard', 'wp-food-manager'); ?></h3>
			<div class="wpfm-d-inline-block wpfm-dashboard-i-block-btn">
				<?php do_action('food_manager_food_dashboard_button_action_start'); ?>
				<?php if (isset($_GET['search_keywords']) || !empty($_GET['search_keywords'])) { ?>
					<a href="<?php echo esc_url(get_permalink()); ?>" class="reset" title="Reset Filter" style="margin-right: 5px;">Reset</a>
				<?php }
				$add_food = get_option('food_manager_add_food_page_id');
				if (!empty($add_food)) : ?>
					<a class="wpfm-dashboard-header-btn wpfm-dashboard-header-add-btn" title="<?php _e('Add Food', 'wp-food-manager'); ?>" href="<?php echo get_permalink($add_food); ?>"><i class="wpfm-icon-plus"></i></a>
				<?php endif; ?>
				<?php do_action('food_manager_food_dashboard_button_action_end'); ?>
				<a href="javascript:void(0)" title="<?php _e('Filter', 'wp-food-manager'); ?>" class="wpfm-dashboard-food-filter wpfm-dashboard-header-btn"><i class="wpfm-icon-filter"></i></a>
			</div>
		</div>
		<?php
		$_GET = array_map('stripslashes_deep', $_GET);
		$search_keywords  = isset($_GET['search_keywords']) ? sanitize_text_field($_GET['search_keywords']) : '';
		$search_order_by  = isset($_GET['search_order_by']) ? sanitize_text_field($_GET['search_order_by']) : '';
		$display_block = '';
		if (!empty($search_keywords) || !empty($search_order_by)) {
			$display_block = 'wpfm-d-block';
		}
		?>
		<form action="<?php echo esc_url(get_permalink(get_the_ID())); ?>" method="get" class="wpfm-form-wrapper wpfm-food-dashboard-filter-toggle wpfm-dashboard-main-filter-block <?php printf($display_block); ?>">
			<div class="wpfm-foods-filter">
				<?php do_action('food_manager_food_dashboard_food_filter_start'); ?>
				<div class="wpfm-foods-filter-block">
					<?php $search_keywords = isset($_GET['search_keywords']) ? $_GET['search_keywords'] : ''; ?>
					<div class="wpfm-form-group"><input name="search_keywords" id="search_keywords" type="text" value="<?php echo esc_attr($search_keywords); ?>" placeholder="<?php _e('Keywords', 'wp-food-manager'); ?>"></div>
				</div>
				<div class="wpfm-foods-filter-block">
					<div class="wpfm-form-group">
						<select name="search_order_by" id="search_order_by">
							<option value=""><?php _e('Order by', 'wp-food-manager'); ?></option>
							<?php
							foreach (get_food_order_by() as $order_by) : ?>
								<?php if (isset($order_by['type']) && !empty($order_by['type'])) : ?>
									<optgroup label="<?php echo esc_html($order_by['label']); ?>">
										<?php foreach ($order_by['type'] as $order_key => $order_value) : ?>
											<option value="<?php echo esc_html($order_key); ?>" <?php selected($order_key, $search_order_by); ?>><?php echo esc_html($order_value); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<?php do_action('food_manager_food_dashboard_food_filter_end'); ?>
				<div class="wpfm-foods-filter-block wpfm-foods-filter-submit">
					<div class="wpfm-form-group">
						<button type="submit" class="wpfm-theme-button"><?php _e('Filter', 'wp-food-manager'); ?></button>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="wpfm-responsive-table-block">
		<?php if (!$foods) : ?>
			<div class="wpfm-alert wpfm-alert-danger wpfm-mb-0">You do not have any active listings.</div>
		<?php else : ?>
			<table class="wpfm-main wpfm-responsive-table-wrapper">
				<thead>
					<tr>
						<?php foreach ($food_dashboard_columns as $key => $column) : ?>
							<th class="wpfm-heading-text <?php echo esc_attr($key); ?>"><?php echo esc_html($column); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($foods as $food) : ?>
						<tr>
							<?php foreach ($food_dashboard_columns as $key => $column) : ?>
								<td data-title="<?php echo esc_html($column); ?>" class="<?php echo esc_attr($key); ?>">
									<?php if ('food_title' === $key) : ?>
										<?php if ($food->post_status == 'publish') :
											$out = strlen($food->post_title) > 50 ? substr($food->post_title, 0, 50) . "..." : $food->post_title; ?>
											<a href="<?php echo get_permalink($food->ID); ?>"><?php echo esc_html($out); ?></a>
											<?php
											$wpfm_veg_nonveg_tags = get_food_veg_nonveg_icon_tag($food);
											$image_id = !empty($wpfm_veg_nonveg_tags) ? get_term_meta($wpfm_veg_nonveg_tags[0]->term_id, 'image_id', true) : '';
											$image_src = wp_get_attachment_image_src($image_id);
											if (!empty($wpfm_veg_nonveg_tags)) {
												foreach ($wpfm_veg_nonveg_tags as $wpfm_veg_nonveg_tag) {
													$imagePath = '';
													if (empty($image_src)) {
														if ($wpfm_veg_nonveg_tag->slug === 'vegetarian') {
															$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-veg-organic.png";
														}
														if ($wpfm_veg_nonveg_tag->slug === 'non-vegetarian') {
															$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-non-veg-organic.png";
														}
														if ($wpfm_veg_nonveg_tag->slug === 'vegan') {
															$imagePath = WPFM_PLUGIN_URL . "/assets/images/wpfm-vegan-organic.png";
														}
													} else {
														$imagePath = $image_src[0];
													}
													if (!empty($imagePath)) {
														$data_icon_label = ucwords(str_replace("-", " ", $wpfm_veg_nonveg_tag->slug));
														echo '<div class="parent-organic-tag ' . $wpfm_veg_nonveg_tag->slug . '" data-icon-type="' . $data_icon_label . '"><img alt="' . $wpfm_veg_nonveg_tag->slug . '" src="' . $imagePath . '" class="wpfm-organic-tag-icon ' . $wpfm_veg_nonveg_tag->slug . '"></div>';
													}
												}
											}
											?>
										<?php else : ?>
											<?php echo $food->post_title; ?> <small class="wpfm-food-status-pending-approval"><?php display_food_status($food); ?></small>
										<?php endif; ?>
									<?php elseif ('food_action' === $key) : ?>
										<div class="wpfm-dboard-food-action">
											<?php
											$actions = array();
											switch ($food->post_status) {
												case 'publish':
													$actions['edit'] = array(
														'label' => __('Edit', 'wp-food-manager'),
														'nonce' => false
													);
													$actions['duplicate'] = array(
														'label' => __('Duplicate', 'wp-food-manager'),
														'nonce' => true
													);
													break;
												case 'expired':
													if (food_manager_get_permalink('add_food')) {
														$actions['relist'] = array(
															'label' => __('Relist', 'wp-food-manager'),
															'nonce' => true
														);
													}
													break;
												case 'pending_payment':
												case 'pending':
													$actions['edit'] = array(
														'label' => __('Edit', 'wp-food-manager'),
														'nonce' => false
													);
													break;
											}
											$actions['delete'] = array(
												'label' => __('Delete', 'wp-food-manager'),
												'nonce' => true
											);
											$actions = apply_filters('food_manager_my_food_actions', $actions, $food);
											foreach ($actions as $action => $value) {
												$action_url = add_query_arg(array(
													'action' => $action,
													'food_id' => $food->ID
												));
												if ($value['nonce']) {
													$action_url = wp_nonce_url($action_url, 'food_manager_my_food_actions');
												}
												echo '<div class="wpfm-dboard-food-act-btn"><a href="' . esc_url($action_url) . '" class="food-dashboard-action-' . esc_attr($action) . '" title="' . esc_html($value['label']) . '" >' . esc_html($value['label']) . '</a></div>';
											}
											?>
										</div>
									<?php
									elseif ('food_categories' === $key) :
										display_food_category($food);
									elseif ('view_count' === $key) :
										echo get_food_views_count($food);
									?>
									<?php else : ?>
										<?php do_action('food_manager_food_dashboard_column_' . $key, $food); ?>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php get_food_manager_template('pagination.php', array('max_num_pages' => $max_num_pages)); ?>
</div>
<?php do_action('food_manager_food_dashboard_after'); ?>