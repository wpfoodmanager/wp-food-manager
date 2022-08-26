
<?php 
wp_enqueue_script( 'wpfm-ajax-filters' ); 
//if(wp_script_is( 'wpfm-ajax-filters','enqueued' ))
	
?>
<?php do_action( 'food_manager_food_filters_before', $atts ); ?>
<form class="wpfm-main wpfm-form-wrapper wpfm-food-filter-wrapper food_filters" id="food_filters">
	<?php do_action( 'food_manager_food_filters_start', $atts ); ?>
	<div class="search_foods search-form-container">
	<?php do_action( 'food_manager_food_filters_search_foods_start', $atts ); ?>
		 <div class="wpfm-row">
			<!-- Search by keywords section start -->
			<div class="wpfm-col">
				<!-- shows default keywords text field  start-->
				<div class="wpfm-form-group">
				<label for="search_keywords" class="wpfm-form-label"><?php _e( 'Keywords', 'wp-food-manager' ); ?></label>
				<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'wp-food-manager' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" /> 
				</div>
				<!-- shows default keywords text field end -->
			</div>
			<!-- Search by keywords section end-->

			<!-- Search by location section start -->
			<div class="wpfm-col">
			<div class="wpfm-form-group">
				<label for="search_location" class="wpfm-form-label"><?php _e( 'Location', 'wp-food-manager' ); ?></label>
				<input type="text" name="search_location" id="search_location"  placeholder="<?php esc_attr_e( 'Location', 'wp-food-manager' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
			</div>
			</div>

			<!-- Search by location section end -->

		



	         </div> <!-- /row -->
		<div class="wpfm-row">
			<!-- Search by food categories section start -->
			<?php if ( $categories ) : ?>
				<?php foreach ( $categories as $category ) : ?>
					<input type="hidden" name="search_categories[]" value="<?php  echo sanitize_title( $category ); ?>" />
				<?php endforeach; ?>
			<?php elseif ( $show_categories && ! is_tax( 'food_manager_category' ) && get_terms( 'food_manager_category', ['hide_empty' => false] ) ) : ?>
				<div class="wpfm-col">
					<div class="wpfm-form-group">
					<label for="search_categories" class="wpfm-form-label"><?php _e( 'Category', 'wp-food-manager' ); ?></label>
					<?php if ( $show_category_multiselect ) : ?>
						<?php food_manager_dropdown_selection( array( 'value'=>'slug', 'taxonomy' => 'food_manager_category', 'hierarchical' => 1, 'name' => 'search_categories', 'orderby' => 'name', 'selected' => $selected_category, 'hide_empty' => false) ); ?>
					<?php else : ?>
						<?php food_manager_dropdown_selection( array( 'value'=>'slug', 'taxonomy' => 'food_manager_category', 'hierarchical' => 1, 'show_option_all' => __( 'Any Category', 'wp-food-manager' ), 'name' => 'search_categories', 'orderby' => 'name', 'selected' => $selected_category, 'multiple' => false, 'hide_empty' => false) ); ?>
					<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>	 
			<!-- Search by food categories section end -->

			<!-- Search by food type section start -->
			<?php  if ( $food_types) :?>
				<?php foreach ( $food_types as $food_type) : ?>
					<input type="hidden" name="search_food_types[]" value="<?php echo sanitize_title( $food_type); ?>" />
				<?php endforeach; ?>
			<?php elseif ( $show_food_types && ! is_tax( 'food_manager_type' ) && get_terms( 'food_manager_type', ['hide_empty' => false] ) ) : ?>		
				<div class="wpfm-col">
					<div class="wpfm-form-group">
					<label for="search_food_types" class="wpfm-form-label"><?php _e( 'food Type', 'wp-food-manager' ); ?></label>
					<?php if ( $show_food_type_multiselect) : ?>
 					    <?php food_manager_dropdown_selection( array( 'value'=>'slug', 'taxonomy' => 'food_manager_type', 'hierarchical' => 1, 'name' => 'search_food_types', 'orderby' => 'name', 'selected' => $selected_food_type, 'hide_empty' => false) ); ?>
					<?php else : ?>
						<?php food_manager_dropdown_selection( array( 'value'=>'slug', 'taxonomy' => 'food_manager_type', 'hierarchical' => 1, 'show_option_all' => __( 'Any food Type', 'wp-food-manager' ), 'name' => 'search_food_types', 'orderby' => 'name', 'selected' => $selected_food_type, 'multiple' => false,'hide_empty' => false) ); ?>
					<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>		        
			<!-- Search by food type section end -->
			<!-- Search by any ticket price section start -->			
			<?php //if ( $show_ticket_prices && $ticket_prices) : ?>				
				<!-- <div class="wpfm-col">
				<div class="wpfm-form-group">
					<label for="search_ticket_prices" class="wpfm-form-label"><?php //_e( 'Ticket Prices', 'wp-food-manager' ); ?></label>
					<select name="search_ticket_prices[]" id="search_ticket_prices" class="food-manager-category-dropdown" data-placeholder="Choose any ticket price…" data-no_results_text="<?php //_e('No results match','wp-food-manager'); ?>" data-multiple_text="<?php //__('Select Some Options','wp-food-manager'); ?>" >
					<?php //foreach ( $ticket_prices as $key => $value ) :
						//if(!strcasecmp($selected_ticket_price, $value) || $selected_ticket_price==$key) : ?>
							<option selected=selected value="<?php //echo $key !='ticket_price_any' ? $key : ""; ?>" ><?php //echo  $value; ?></option>
						<?php //else : ?>
							<option value="<?php //echo $key !='ticket_price_any' ? $key : ""; ?>" ><?php //echo  $value; ?></option>
						<?php //endif;
					//endforeach; ?>
					</select>
					</div>
				</div> -->
			<?php //endif; ?>	  
			<!-- Search by any ticket price section end -->  
    </div> <!-- /row -->

    <?php do_action( 'food_manager_food_filters_search_foods_end', $atts ); ?>	

  </div>
  <?php do_action( 'food_manager_food_filters_end', $atts ); ?>
</form>
<?php do_action( 'food_manager_food_filters_after', $atts ); ?>
<noscript><?php _e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-food-manager' ); ?></noscript>