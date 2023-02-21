<div class="panel-wrap">
	<ul class="wpfm-tabs">
		<?php foreach ($this->get_food_data_tabs() as $key => $tab) : ?>
			<li class="<?php echo esc_attr($key); ?>_options <?php echo esc_attr($key); ?>_tab <?php echo esc_attr(isset($tab['class']) ? implode(' ', (array) $tab['class']) : ''); ?>">
				<a href="#<?php if (isset($tab['target'])) echo $tab['target']; ?>" class=""><span><?php echo esc_html($tab['label']); ?></span></a>
			</li>
		<?php endforeach; ?>
		<?php do_action('wpfm_food_write_panel_tabs'); ?>
	</ul>

	<?php
	//output tab
	self::output_tabs();
	?>
	<div class="clear"></div>
</div>