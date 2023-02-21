<ul class="food-manager-term-checklist food-manager-term-checklist-<?php echo $key ?>">
	<?php
	$field_val_num = '';
	if (!empty($field['value']) && is_array($field['value']) && isset($field['value'])) {
		$tmp_cnt = explode("_", $key);
		$counter = end($tmp_cnt);
		$field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';
	} else {
		$field_val_num = !empty($field['value']) ? $field['value'] : '';
	}

	$my_check_value_arr = [];
	if (is_array($field_val_num)) {
		if (isset($field_val_num[$field['taxonomy']])) {
			foreach ($field_val_num[$field['taxonomy']] as $my_value) {
				$my_check_value_arr[] = $my_value;
			}
		}
	}

	ob_start();

	wpfm_category_checklist($field['taxonomy'], $key, $my_check_value_arr);
	$checklist = ob_get_clean();
	echo str_replace("disabled='disabled'", '', $checklist);
	?>
</ul>
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>