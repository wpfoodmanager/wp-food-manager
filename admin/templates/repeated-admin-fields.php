<?php

/**
 * This if only for Admin Panel
 * Repeated fields is generated from this page .
 * Repeated fields for the paid and free tickets.
 * This is field is used tickets metabox in edit event at admin panel.
 **/
global $post;
$food_attributes = get_post_meta($post->ID, '_fa_keys', true);
?>
<div class="wpfm-main-metabox-container">
	<input type="text" name="attribute_label" id="attribute-label" placeholder="<?php _e('Enter attribute lable', 'wp-food-manager'); ?>">
	<a href="javascript:void(0)" class="button button-primary wpfm-add-attributes" data-row='<div class="wpfm-attributes-wrap wpfm-metabox postbox-%%repeated-row-index%%">
	<input type="hidden" class="repeated-attribute-row" name="repeated-attribute-row[]" value="%%repeated-row-index%%" />
	<input type="hidden" class="repeated-attribute-row" name="repeated_attribute_label[]" value="%%attribute_label%%" />
	<h3 class="">
		<a href="#" class="wpfm-delete-btn wpfm-remove-attribute" data-id="wpfm-metabox postbox-%%repeated-row-index%%">Remove</a>
		<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false"></div>
		<div class="tips wpfm-sort"></div>
		<strong class="attribute_name">%%attribute_label%%</strong>
	</h3>
	<div class="wpfm-attribute-type">
		<p class="wpfm-admin-postbox-form-field">
			<label>Selection type</label>
				<input type="radio" class="radio" name="attribute_type_%%attribute_label%%[]" value="single"> Single							
				<input type="radio" class="radio" name="attribute_type_%%attribute_label%%[]" value="multiple"> Multiple
				<input type="radio" class="radio" name="attribute_type_%%attribute_label%%[]" value="multiple"> Multiple 2
		</p>
	</div>
	<div class="wpfm-metabox-content wpfm-attributes-box" id="wpfm-attributes-box-%%repeated-row-index%%">
		<?php include 'repeated-field.php'; ?>
		<div class="wpfm-metabox-footer wpfm-actions">
			<input type="button" value="Add new" class="button wpfm-add-button wpfm-add-attribute-fields" >
		</div>
	</div>
</div>'><?php _e('Add attribute', 'wp-food-manager'); ?></a>
	<?php if ($food_attributes && is_array($food_attributes)) {
		$count = 0;
		foreach ($food_attributes as $attribute) {
			$count = +1;
			$attribute_values = get_post_meta($post->ID, '_fa_' . $attribute, true); ?>
			<div class="wpfm-attributes-wrap wpfm-metabox postbox-<?= $count; ?>">
				<input type="hidden" class="repeated-attribute-row" name="repeated-attribute-row[]" value="<?= $count; ?>" />
				<input type="hidden" class="repeated-attribute-row" name="repeated_attribute_label[]" value="<?= $attribute; ?>" />
				<h3 class="">
					<a href="#" class="wpfm-delete-btn wpfm-remove-attribute" data-id="wpfm-metabox postbox-<?= $count; ?>">Remove</a>
					<div class="wpfm-togglediv" title="Click to toggle" aria-expanded="false"></div>
					<div class="tips wpfm-sort"></div>
					<strong class="attribute_name"><?= $attribute; ?></strong>
				</h3>
				<div class="wpfm-attribute-type">
					<p class="wpfm-admin-postbox-form-field">
						<label>Selection type</label>
						<input type="radio" class="radio" name="attribute_type_<?= $attribute; ?>[]" value="single" <?php echo (isset($attribute_values['type']) && $attribute_values['type'] == 'single') ? 'checked="checked"' : ''; ?>> Single
						<input type="radio" class="radio" name="attribute_type_<?= $attribute; ?>[]" value="multiple" <?php echo (isset($attribute_values['type']) && $attribute_values['type'] == 'multiple') ? 'checked="checked"' : ''; ?>> Multiple
					</p>
					<input type="radio" class="radio" name="attribute_type_<?= $attribute; ?>[]" value="multiple" <?php echo (isset($attribute_values['type']) && $attribute_values['type'] == 'multiple') ? 'checked="checked"' : ''; ?>> Multiple checkbox</p>
				</div>
				<div class="wpfm-metabox-content wpfm-attributes-box" id="wpfm-attributes-box-<?= $count; ?>">
					<?php
					unset($attribute_values['type']);
					foreach ($attribute_values as $attr_key => $attr_value) { ?>
						<div class="wpfm-content" data-field="1">
							<?php foreach ($field['fields'] as $subkey => $subfield) : ?>
								<?php
								$subfield['name']  	=  $subkey . '_%%attribute_label%%[]';
								$subfield['id']  	=   $subkey . '_%%repeated-field-index%%';
								$subfield['attribute'] = $key;
								$subfield['value'] = isset($attr_value[$subkey]) ? $attr_value[$subkey] : '';
								$type = !empty($subfield['type']) ? $subfield['type'] : 'text';
								if ($type == 'wp-editor') $type = 'wp_editor';
								if (has_action('food_manager_input_' . $type)) {
									do_action('food_manager_input_' . $type, $key, $subfield);
								} elseif (method_exists($this, 'input_' . $type)) {
									call_user_func(array($this, 'input_' . $type), $key, $subfield);
								} ?>
							<?php endforeach; ?>
							<a class="wpfm-remove-attribute-field" data-id="wpfm-attributes-box-%%repeated-row-index%%"><?php _e('Delete', 'wp-food-manager'); ?></a>
						</div>
					<?php } ?>
					<div class="wpfm-metabox-footer wpfm-actions">
						<input type="button" value="Add new" class="button wpfm-add-button wpfm-add-attribute-fields">
					</div>
				</div>
			</div>
		<?php } ?>
	<?php } ?>
</div>
<!--/end main container--->