<?php do_action('food_manager_food_dashboard_before'); ?>
<p></p>
<div id="food-manager-food-dashboard">
	<div class="wpfm-responsive-table-block">
		<table class="wpfm-main wpfm-responsive-table-wrapper">
			<thead>
				<tr>
					<?php foreach ( $food_dashboard_columns as $key => $column ) : ?>
					<th class="wpfm-heading-text <?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $foods ) : ?>
				<tr>
					<td colspan="6"><?php _e( 'You do not have any active listings.', 'wp-food-manager' ); ?></td>
				</tr>
				<?php else : ?>
				<?php foreach ( $foods as $food ) : ?>
				<tr>

					<?php  foreach ( $food_dashboard_columns as $key => $column ) : ?>
						<td data-title="<?php echo esc_html( $column ); ?>"
						class="<?php echo esc_attr( $key ); ?>">
							<?php if ('food_title' === $key ) : ?>
								<?php if ( $food->post_status == 'publish' ) : ?>
									<a href="<?php echo get_permalink( $food->ID ); ?>"><?php echo esc_html( $food->post_title ); ?></a>
								<?php else : ?>
									<?php echo $food->post_title; ?> <small>(<?php display_food_status( $food ); ?>)</small>
								<?php endif; ?>
								<?php elseif ('food_action' === $key ) :?>
		                            <div class="wpfm-dboard-food-action">
									<?php
								$actions = array ();
								switch ($food->post_status) {
									case 'publish' :
										$actions ['edit'] = array (
												'label' => __ ( 'Edit', 'wp-food-manager' ),
												'nonce' => false
										);
										if (is_food_cancelled ( $food )) {
											$actions ['mark_not_cancelled'] = array (
													'label' => __ ( 'Mark not cancelled', 'wp-food-manager' ),
													'nonce' => true
											);
										} else {
											$actions ['mark_cancelled'] = array (
													'label' => __ ( 'Mark cancelled', 'wp-food-manager' ),
													'nonce' => true
											);
										}
										$actions ['duplicate'] = array (
												'label' => __ ( 'Duplicate', 'wp-food-manager' ),
												'nonce' => true
										);
										break;
									case 'expired' :
										if (food_manager_get_permalink ( 'submit_food_form' )) {
											$actions ['relist'] = array (
													'label' => __ ( 'Relist', 'wp-food-manager' ),
													'nonce' => true
											);
										}
										break;
									case 'pending_payment' :
									case 'pending' :
										if (food_manager_user_can_edit_pending_submissions ()) {
											$actions ['edit'] = array (
													'label' => __ ( 'Edit', 'wp-food-manager' ),
													'nonce' => false
											);
										}
										break;
								}
								$actions ['delete'] = array (
										'label' => __ ( 'Delete', 'wp-food-manager' ),
										'nonce' => true
								);
								$actions = apply_filters ( 'food_manager_my_food_actions', $actions, $food );
								foreach ( $actions as $action => $value ) {
									$action_url = add_query_arg ( array (
											'action' => $action,
											'food_id' => $food->ID
									) );
									if ($value ['nonce']) {
										$action_url = wp_nonce_url ( $action_url, 'food_manager_my_food_actions' );
									}
									echo '<div class="wpfm-dboard-food-act-btn"><a href="' . esc_url ( $action_url ) . '" class="food-dashboard-action-' . esc_attr ( $action ) . '" title="' . esc_html ( $value ['label'] ) . '" >' . esc_html ( $value ['label'] ) . '</a></div>';
								}
								?>
								</div>		
							<?php



elseif ('food_categories' === $key) :
								display_food_category($food);

elseif ('view_count' === $key) :
								echo get_food_views_count ( $food );
								?>
							<?php else : ?>
								<?php do_action( 'food_manager_food_dashboard_column_' . $key, $food ); ?>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>

				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php get_food_manager_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>


   </div>
<?php do_action('food_manager_food_dashboard_after'); ?>
