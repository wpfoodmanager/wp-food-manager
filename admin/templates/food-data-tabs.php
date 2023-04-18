<?php
$food_fields = $this->food_manager_data_fields();
$disbled_fields_for_admin = array('food_category', 'food_tag', 'food_nutritions', 'food_ingredients');
?>
<div class="panel-wrap">
	<ul class="wpfm-tabs">
		<?php foreach ($this->get_food_data_tabs() as $key => $tab) : ?>
			<li class="<?php echo esc_attr($key); ?>_options <?php echo esc_attr($key); ?>_tab <?php echo esc_attr(isset($tab['class']) ? implode(' ', (array) $tab['class']) : ''); ?>">
				<a href="#<?php if (isset($tab['target'])) echo $tab['target']; ?>" class=""><span><?php echo esc_html($tab['label']); ?></span></a>
			</li>
		<?php endforeach; ?>
		<?php do_action('wpfm_food_write_panel_tabs'); ?>
	</ul>
	<?php foreach ($this->get_food_data_tabs() as $key => $tab) : ?>
		<?php
		if ($key == 'toppings') {
			include 'food-data-toppings.php';
		} elseif ($key == 'ingredients') {
			include 'food-data-ingredient.php';
		} elseif ($key == 'nutritions') {
			include 'food-data-nutrition.php';
		} else { ?>
			<div id="<?php echo (isset($tab['target'])) ? $tab['target'] : ''; ?>" class="panel wpfm_panel wpfm-metaboxes-wrapper">
				<div class="wp_food_manager_meta_data">
					<div class="wpfm-variation-wrapper wpfm-metaboxes">
						<?php do_action('food_manager_food_data_start', $thepostid);
						if (isset($food_fields['food']))
							foreach ($food_fields['food'] as $key => $field) {
								if (!isset($field['value'])) {
									$field['value'] = get_post_meta($thepostid, '_' . $key, true);
								}
								$field['required'] = false;
								$field['tabgroup'] = isset($field['tabgroup']) ? $field['tabgroup'] : 0;
								if (!in_array($key, $disbled_fields_for_admin) && $field['tabgroup'] == $tab['priority']) {
									$type = !empty($field['type']) ? $field['type'] : 'text';
									if ($type == 'wp-editor') {
										global $thepostid;
										if (!isset($field['value']) || empty($field['value'])) {
											$field['value'] = get_post_meta($thepostid, '_' . $key, true);
										}
										if (is_array($field['value'])) {
											$field['value'] = '';
										}
										if (!empty($field['name'])) {
											$name = $field['name'];
										} else {
											$name = $key;
										}
										if (wpfm_begnWith($field['value'], "http")) {
											$field['value'] = '';
										} ?>
									<div class="wpfm_editor" data-field-name="<?= $name; ?>">
										<p class="wpfm-admin-postbox-form-field <?= $name; ?>">
											<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?>:
												<?php if (!empty($field['description'])) : ?>
													<span class="wpfm-tooltip" wpfm-data-tip="<?php echo esc_attr($field['description']); ?>">[?]</span>
												<?php endif; ?>
											</label>
										</p>
										<span class="wpfm-input-field">
											<?php wp_editor($field['value'], $name, array('media_buttons' => false)); ?>
										</span>
									</div>
								<?php } else { ?>
									<p class="wpfm-admin-postbox-form-field <?php echo $key; ?>" data-field-name="<?= $key; ?>">
										<label for="<?php echo $key; ?>"><?php echo $field['label']; ?> : </label>
										<span class="wpfm-input-field">
											<?php get_food_manager_template('form-fields/' . $field['type'] . '-field.php', array('key' => $key, 'field' => $field)); ?>
										</span>
									</p>
						<?php
									}
								}
							}
						do_action('food_manager_food_data_end', $thepostid); ?>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php endforeach; ?>
	<div class="clear"></div>
</div>