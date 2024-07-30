<?php
wp_enqueue_script('wpfm-ajax-file-upload');
if (is_numeric($value)) {
	$image_src = wp_get_attachment_image_src(absint($value));
	$image_src = $image_src ? $image_src[0] : '';
} else {
	$image_src = $value;
}
$image_src_class = !empty($image_src) ? '' : 'empty-src';
if (!is_array($image_src)) { ?>
	<div class="food-manager-uploaded-file">
		<?php
		$extension = !empty($extension) ? $extension : substr(strrchr($image_src, '.'), 1);
		if (3 !== strlen($extension) || in_array($extension, array('jpg', 'gif', 'png', 'jpeg', 'jpe', 'webp'))) : ?>
			<span class="food-manager-uploaded-file-preview"><img src="<?php echo esc_url($image_src); ?>" /> <a class="food-manager-remove-uploaded-file" href="#">[<?php _e('remove', 'wp-food-manager'); ?>]</a></span>
		<?php else : ?>
			<span class="food-manager-uploaded-file-preview"><span class="food-manager-uploaded-file-name"><code><?php echo esc_html(basename($image_src)); ?></code> <a class="food-manager-remove-uploaded-file" href="#">[<?php _e('remove', 'wp-food-manager'); ?>]</a></span></span>
		<?php endif; ?>
		<input type="hidden" class="input-text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
	</div>
<?php } ?>