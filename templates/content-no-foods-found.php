<?php if (defined('DOING_AJAX')) : ?>
	<div class="no_food_listings_found wpfm-alert wpfm-alert-danger"><?php _e('There are no foods matching your search.', 'wp-food-manager'); ?></div>
<?php else : ?>
	<div class="no_food_listings_found wpfm-alert wpfm-alert-danger"><?php _e('There are currently no foods.', 'wp-food-manager'); ?></div>
<?php endif; ?>