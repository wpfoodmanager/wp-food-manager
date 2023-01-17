<?php
/**
*  Template ingredient panel
*/
	
echo '<div id="ingredient_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
<div class="wp_food_manager_meta_data">';

do_action( 'food_manager_food_data_ingredient_start', $thepostid );

$metaIngredients = get_post_meta( $post->ID, '_ingredient' );
$excludeIngredients = [];
if ( ! empty( $metaIngredients ) ) {
	foreach ( $metaIngredients as $items ) {
		foreach ( $items as $item ) {
			$excludeIngredients[] = $item['id'];
		}
	}
}

$ingredient_terms = get_terms(
					[
						'taxonomy'   => 'food_manager_ingredient',
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
						'exclude'    => $excludeIngredients,
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
<div class="wpfm-ingredient-fields wpfm-metaboxes">
	<div id="wpfm-ingredient-container" class="wpfm-clear wpfm-lists-container">
		<ul id="wpfm-active-ing-list" class="wpfm-active-list wpfm-sortable-list wpfm-clear ui-sortable"
			data-title="Active Ingredient">
			<?php
			if ( ! empty( $metaIngredients ) ) {
				foreach ( $metaIngredients as $ingredients ) {
					foreach ( $ingredients as $ingredient ) {
						$ingTerm = get_term(
										! empty( $ingredient['id'] ) ? absint( $ingredient['id'] ) : 0,
										'food_manager_ingredient'
									);
						$unit_id     = ! empty( $ingredient['unit_id'] ) ? absint( $ingredient['unit_id'] ) : 0;
						$ingValue    = ! empty( $ingredient['value'] ) ? absint( $ingredient['value'] ) : null;
						$ingTermID   = ! empty( $ingTerm->term_id ) ? $ingTerm->term_id : null;
						$ingTermName = ! empty( $ingTerm->name ) ? $ingTerm->name : null;
						if( $ingTermID ){
							echo "<li class='wpfm-sortable-item active-item' data-id='{$ingTermID}'>" .
							"<label>{$ingTermName}</label>" .
							"<div class='wpfm-sortable-item-values'>" .
							"<input type='number' class='item-value' name='_ingredient[{$ingTermID}][value]' value='{$ingValue}'>" .
							"<select name='_ingredient[{$ingTermID}][unit_id]' class='item-unit'>" .
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
			}
			?>
		</ul>
		<ul id="wpfm-available-ing-list"
			class="wpfm-available-list wpfm-sortable-list wpfm-clear ui-sortable"
			data-title="Available Ingredient">
			<li class="wpfm-item-search with-title">
				<label class="wpfm-search-label">
					<span>Search Ingredient</span>
					<input type="text" placeholder="Search">
				</label>
			</li>
			<?php
			if ( ! empty( $ingredient_terms ) ) {
				foreach ( $ingredient_terms as $ing ) {
					echo "<li class='wpfm-sortable-item available-item' data-id='{$ing->term_id}'>" .
						"<label>{$ing->name}</label><div class='wpfm-sortable-item-values'></div>" .
						'</li>';
				}
			}
			?>
		</ul>
	</div>
</div>
<?php
do_action( 'food_manager_food_data_ingredient_end', $thepostid );
echo '</div></div>';