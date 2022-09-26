<?php
/**
*  Template nutrition panel
*/
	
echo '<div id="nutritions_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
<div class="wp_food_manager_meta_data">';

do_action( 'food_manager_food_data_nutrition_start', $thepostid );

$metaNutritions = get_post_meta( $post->ID, '_nutrition' );
$excludeNutritions = [];
if ( ! empty( $metaNutritions ) ) {
	foreach ( $metaNutritions as $items ) {
		foreach ( $items as $item ) {
			$excludeNutritions[] = $item['id'];
		}
	}
}

$nutrition_terms = get_terms(
					[
						'taxonomy'   => 'food_manager_nutrition',
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
						'exclude'    => $excludeNutritions,
					]
				);
$units = get_terms(
					[
						'taxonomy'   => 'food_manager_unit',
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					]
				);
?>
<div class="wpfm-nutrition-fields wpfm-metaboxes">
	<div id="wpfm-nutrition-container" class="wpfm-clear wpfm-lists-container">
		<ul id="wpfm-active-nutri-list" class="wpfm-active-list wpfm-sortable-list wpfm-clear ui-sortable"
			data-title="Active Nutrition">
			<?php
			if ( ! empty( $metaNutritions ) ) {
				foreach ( $metaNutritions as $nutritions ) {
					foreach ( $nutritions as $nutrition ) {
						$nutriTerm = get_term(
										! empty( $nutrition['id'] ) ? absint( $nutrition['id'] ) : 0,
										'food_manager_nutrition'
									);
						$unit_id     = ! empty( $nutrition['unit_id'] ) ? absint( $nutrition['unit_id'] ) : 0;
						$nutriValue    = ! empty( $nutrition['value'] ) ? absint( $nutrition['value'] ) : null;
						$nutriTermID   = ! empty( $nutriTerm->term_id ) ? $nutriTerm->term_id : null;
						$nutriTermName = ! empty( $nutriTerm->name ) ? $nutriTerm->name : null;
						echo "<li class='wpfm-sortable-item active-item' data-id='{$nutriTermID}'>" .
							"<label>{$nutriTermName}</label>" .
							"<div class='wpfm-sortable-item-values'>" .
							"<input type='text' class='item-value' name='_nutrition[{$nutriTermID}][value]' value='{$nutriValue}'>" .
							"<select name='_nutrition[{$nutriTermID}][unit_id]' class='item-unit'>" .
							"<option value=''>Unit</option>";
						if ( ! empty( $units ) ) {
							foreach ( $units as $unit ) {
								$sel = ( $unit_id == $unit->term_id ? ' selected' : null );
								echo "<option value='{$unit->term_id}'{$sel}>{$unit->name}</option>";
							}
						}
						echo '</select>' .
							'</div>' .
							'</li>';
					}
				}
			}
			?>
		</ul>
		<ul id="wpfm-available-nutri-list"
			class="wpfm-available-list wpfm-sortable-list wpfm-clear ui-sortable"
			data-title="Available Nutrition">
			<li class="wpfm-item-search with-title">
				<label class="wpfm-search-label">
					<span>Search nutrition</span>
					<input type="text" placeholder="Search">
				</label>
			</li>
			<?php
			if ( ! empty( $nutrition_terms ) && get_option( 'food_manager_enable_food_nutritions' )) {
				foreach ( $nutrition_terms as $nutri ) {
					echo "<li class='wpfm-sortable-item available-item' data-id='{$nutri->term_id}'>" .
						"<label>{$nutri->name}</label><div class='wpfm-sortable-item-values'></div>" .
						'</li>';
				}
			}
			?>
		</ul>
	</div>
</div>
<?php
do_action( 'food_manager_food_data_nutrition_end', $thepostid );
echo '</div></div>';