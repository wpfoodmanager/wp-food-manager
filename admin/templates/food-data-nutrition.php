<?php

/**
 *  Template nutrition panel
 */

echo '<div id="nutritions_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
<div class="wp_food_manager_meta_data">';

do_action('food_manager_food_data_nutrition_start', $thepostid);
$metaNutritions = get_post_meta($post->ID, '_food_nutritions');
$excludeNutritions = [];

if (!empty($metaNutritions)) {
	foreach ($metaNutritions as $items) {
		foreach ($items as $item) {
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
); ?>

<div class="wpfm-nutrition-fields wpfm-metaboxes">
	<div id="wpfm-nutrition-container" class="wpfm-clear wpfm-lists-container">
		<ul id="wpfm-active-nutri-list" class="wpfm-active-list wpfm-sortable-list wpfm-clear ui-sortable" data-title="Active Nutrition">

			<?php
			if (!empty($metaNutritions)) {
				foreach ($metaNutritions as $nutritions) {
					foreach ($nutritions as $nutrition) {
						$nutriTerm = get_term(
							!empty($nutrition['id']) ? absint($nutrition['id']) : 0,
							'food_manager_nutrition'
						);
						$unit_id     = !empty($nutrition['unit_id']) ? absint($nutrition['unit_id']) : 0;
						$nutriValue    = !empty($nutrition['value']) ? $nutrition['value'] : null;
						$nutriTermID   = !empty($nutriTerm->term_id) ? $nutriTerm->term_id : null;
						$nutriTermName = !empty($nutriTerm->name) ? $nutriTerm->name : null;

						if ($nutriTermID) {
							echo "<li class='wpfm-sortable-item active-item' data-id='" . esc_attr($nutriTermID) . "'>" .
								"<label>" . esc_html($nutriTermName) . "</label>" .
								"<div class='wpfm-sortable-item-values'>" .
								"<input type='number' step='0.1' class='item-value' name='food_nutritions[" . esc_attr($nutriTermID) . "][value]' value='" . esc_attr($nutriValue) . "'>" .
								"<select name='food_nutritions[" . esc_attr($nutriTermID) . "][unit_id]' class='item-unit'>" .
								"<option value=''>Unit</option>";

							if (!empty($units)) {
								foreach ($units as $unit) {
									$sel = ($unit_id == $unit->term_id ? ' selected' : null);
									echo "<option value='" . esc_attr($unit->term_id) . "'{$sel}>" . esc_html($unit->name) . "</option>";
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
		<ul id="wpfm-available-nutri-list" class="wpfm-available-list wpfm-sortable-list wpfm-clear ui-sortable" data-title="Available Nutrition">
			<li class="wpfm-item-search with-title">
				<label class="wpfm-search-label">
					<span>Search nutrition</span>
					<input type="text" placeholder="Search">
				</label>
			</li>
			<?php
			if (!empty($nutrition_terms)) {
				foreach ($nutrition_terms as $nutri) {
					echo "<li class='wpfm-sortable-item available-item' data-id='" . esc_attr($nutri->term_id) . "'>" .
						"<label>" . esc_html($nutri->name) . "</label><div class='wpfm-sortable-item-values'></div>" .
						'</li>';
				}
			}
			?>
		</ul>
	</div>
	<?php if (isset($food_fields['food']))
		foreach ($food_fields['food'] as $key => $field) {
			$field['required'] = false;

			if (!isset($field['value'])) {
				$field['value'] = get_post_meta($thepostid, '_' . $key, true);
			}

			$field['tabgroup'] = isset($field['tabgroup']) ? $field['tabgroup'] : 0;
			if (!in_array($key, $disbled_fields_for_admin) && $field['tabgroup'] == $tab['priority']) {
				$type = !empty($field['type']) ? $field['type'] : 'text';
				if ($type == 'wp-editor') $type = 'wp_editor'; ?>
			<p class="wpfm-admin-postbox-form-field <?php echo esc_attr($key); ?>">
				<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?> : </label>
				<span class="wpfm-input-field">
					<?php get_food_manager_template('form-fields/' . esc_html($field['type']) . '-field.php', array('key' => $key, 'field' => $field)); ?>
				</span>
			</p>
	<?php }
		} ?>
</div>
<?php
do_action('food_manager_food_data_nutrition_end', $thepostid);
echo '</div></div>';
