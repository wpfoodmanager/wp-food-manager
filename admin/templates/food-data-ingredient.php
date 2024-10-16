<?php

/**
 * Template ingredient panel.
 */

echo '<div id="ingredient_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
<div class="wp_food_manager_meta_data">';

do_action('food_manager_food_data_ingredient_start', $thepostid);
$food_meta_ingredients = get_post_meta($post->ID, '_food_ingredients');
$excludeIngredients = [];

if (!empty($food_meta_ingredients)) {
	foreach ($food_meta_ingredients as $items) {
		foreach ($items as $item) {
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
); ?>
<div class="wpfm-ingredient-fields wpfm-metaboxes">
	<div id="wpfm-ingredient-container" class="wpfm-clear wpfm-lists-container">
		<ul id="wpfm-active-ing-list" class="wpfm-active-list wpfm-sortable-list wpfm-clear ui-sortable" data-title="Active Ingredient">

			<?php
			if (!empty($food_meta_ingredients)) {
				foreach ($food_meta_ingredients as $ingredients) {
					foreach ($ingredients as $ingredient) {

						$ingredient_term = get_term(
							!empty($ingredient['id']) ? absint($ingredient['id']) : 0,
							'food_manager_ingredient'
						);

						$unit_id     = !empty($ingredient['unit_id']) ? absint($ingredient['unit_id']) : 0;
						$ingredient_value    = !empty($ingredient['value']) ? $ingredient['value'] : null;
						$ingredient_term_id   = !empty($ingredient_term->term_id) ? $ingredient_term->term_id : null;
						$ingredient_term_name = !empty($ingredient_term->name) ? $ingredient_term->name : null;

						if ($ingredient_term_id) {

							echo "<li class='wpfm-sortable-item active-item' data-id='" . esc_attr($ingredient_term_id) . "'>" .
								"<label>" . esc_html($ingredient_term_name) . "</label>" .
								"<div class='wpfm-sortable-item-values'>" .
								"<input type='number' step='any' class='item-value' name='food_ingredients[" . esc_attr($ingredient_term_id) . "][value]' value='" . esc_attr($ingredient_value) . "'>" .
								"<select name='food_ingredients[" . esc_attr($ingredient_term_id) . "][unit_id]' class='item-unit'>" .
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
		<ul id="wpfm-available-ing-list" class="wpfm-available-list wpfm-sortable-list wpfm-clear ui-sortable" data-title="Available Ingredient">
			<li class="wpfm-item-search with-title">
				<label class="wpfm-search-label">
					<span><?php _e('Search Ingredient', 'wp-food-manager')?></span>
					<input type="text" placeholder="Search">
				</label>
			</li>

			<?php
			if (!empty($ingredient_terms)) {
				foreach ($ingredient_terms as $ing) {
					echo "<li class='wpfm-sortable-item available-item' data-id='" . esc_attr($ing->term_id) . "'>" .
						"<label>" . esc_html($ing->name) . "</label><div class='wpfm-sortable-item-values'></div>" .
						'</li>';
				}
			}
			?>
			<!-- Placeholder for 'No results found' message -->
			<li class="wpfm-no-results" style="display: none;"><?php _e('No results found', 'wp-food-manager'); ?></li>
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
					<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => esc_attr($key), 'field' => $field)); ?>
				</span>
			</p>
	<?php }
		} ?>
</div>
<?php
do_action('food_manager_food_data_ingredient_end', $thepostid);
echo '</div></div>';
