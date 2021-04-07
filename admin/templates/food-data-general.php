<?php

/**
 *  Template general panel
 */
?>
<div id="general_food_data_content" class="panel wpfm_panel wpfm-metaboxes-wrapper">
	<div class="wp_food_manager_meta_data">
		<div class="wpfm-variation-wrapper wpfm-metaboxes">
		<?php
		do_action('food_manager_food_data_start', $thepostid);
		$food_fields = $this->food_manager_data_fields();
		if(isset($food_fields['food']))
		foreach ($food_fields['food'] as $key => $field) {
			if( strpos($key, '_') !== 0 ) {
					$key  = '_'.$key;	
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
		<hr/>
		 <div class="wpfm-actions">
		 	
			<!-- <button type="button" id="save-wpfm-attributes" class="wpfm-add-button button button-primary">Save</button>  -->
		</div>
	</div>
	</div>
</div>
