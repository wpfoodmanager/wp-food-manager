<?php

/**
 *  Template advanced panel
 */

echo '<div id="advanced_food_data_content" class="panel wpfm_panel">
<div class="wp_food_manager_meta_data">';

do_action('food_manager_food_data_advanced_start', $thepostid); ?>
<div id="advanced_product_data" class="panel woocommerce_options_panel hidden" style="display: block;">
	<div class="wpfm-variation-wrapper wpfm-metaboxes" id="advanced_fmp_data" style="display: block;">
		<?php
		do_action('food_manager_food_data_start', $thepostid);
		$food_fields = get_advanced_tab_fields();

		if (isset($food_fields['food']))
			foreach ($food_fields['food'] as $key => $field) {
				if (strpos($key, '_') !== 0) {
					$key  = '_' . $key;
				}
				$type = !empty($field['type']) ? $field['type'] : 'text';
				if ($type == 'wp-editor') $type = 'textarea';

				if (has_action('food_manager_input_' . $type)) {
					do_action('food_manager_input_' . $type, $key, $field);
				} elseif (method_exists($this, 'input_' . $type)) {
					call_user_func(array($this, 'input_' . $type), $key, $field);
				}
			}
		do_action('food_manager_food_data_end', $thepostid);
		?>
	</div>
</div>

<?php
do_action('food_manager_food_data_advanced_end', $thepostid);
echo '</div></div>';
