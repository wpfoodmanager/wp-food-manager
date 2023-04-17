<?php
if (is_admin()) {
	global $thepostid;
	if (!isset($field['value'])) {
		$field['value'] = get_post_meta($thepostid, '_' . $key, true);
	}
	if (!empty($field['name'])) {
		$name = $field['name'];
	} else {
		$name = $key;
	}
	$fieldLabel = '';
	if ($field['type'] == 'wp-editor') {
		$fieldLabel =  'wp-editor-field';
	}
	if (wpfm_begnWith($field['value'], "http") || is_array($field['value'])) {
		$field['value'] = '';
	}
?>
	<textarea name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($key); ?>" rows="4" cols="63" placeholder="<?php echo esc_attr($field['placeholder']); ?>"><?php echo esc_html($field['value']); ?></textarea>
<?php } else {
	$editor = apply_filters('add_food_wp_editor_args', array(
		'textarea_name' => isset($field['name']) ? $field['name'] : $key,
		'media_buttons' => false,
		'wpautop' 		=> false,
		'textarea_rows' => 8,
		'quicktags'     => false,
		'tinymce'       => array(
			'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
			'paste_as_text'                 => true,
			'paste_auto_cleanup_on_paste'   => true,
			'paste_remove_spans'            => true,
			'paste_remove_styles'           => true,
			'paste_remove_styles_if_webkit' => true,
			'paste_strip_class_attributes'  => true,
			'toolbar1'                      => 'bold,italic,|,bullist,numlist,|,link,unlink,|,undo,redo',
			'toolbar2'                      => '',
			'toolbar3'                      => '',
			'toolbar4'                      => ''
		),
	));
	$field_val_num = '';
	if (!empty($field['value']) && is_array($field['value'])) {
		$tmp_cnt = explode("_", $key);
		$counter = end($tmp_cnt);
		$field_val_num = isset($field['value'][$counter]) ? $field['value'][$counter] : '';
	} else {
		$field_val_num = !empty($field['value']) ? $field['value'] : '';
	}
	if (wpfm_begnWith($field_val_num, "http") || is_array($field_val_num)) {
		$field_val_num = '';
	}
	wp_editor(isset($field_val_num) ? $field_val_num : '', $key, $editor);
}
if (!empty($field['description'])) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>