<?php if (is_user_logged_in()) : ?>
	<div class="wpfm-form-group ">
		<label class="wpfm-form-label-text"><?php _e('Your Account', 'wp-food-manager'); ?></label>
		<div class="field account-sign-in wpfm-alert wpfm-alert-info">
			<?php $user = wp_get_current_user();
			printf(wp_kses(__('You are currently signed in as <strong>%s</strong>.', 'wp-food-manager'), array('strong' => array())), $user->user_login);    		?> <a href="<?php echo apply_filters('add_food_logout_url', wp_logout_url(get_permalink())); ?>"><?php _e('Sign out', 'wp-food-manager'); ?></a>
		</div>
	</div>
<?php else :
	$account_required             = food_manager_user_requires_account();
	$generate_username_from_email = food_manager_generate_username_from_email(); ?>
	<div class="wpfm-form-group">
		<label class="wpfm-form-label-text"><?php _e('Have an account?', 'wp-food-manager'); ?></label>
		<div class="field account-sign-in wpfm-alert wpfm-alert-info">
			<a href="<?php echo !empty(get_option('food_manager_login_page_url')) ? esc_url(apply_filters('add_food_login_url', get_option('food_manager_login_page_url'))) : 	home_url() . '/wp-login.php'; ?>"><?php _e('Sign in', 'wp-food-manager'); ?></a>
			<?php if ($account_required) : ?>
				<?php echo  wp_kses_post(apply_filters('add_food_login_required_message',  __(' You must sign in to create a new listing.', 'wp-food-manager'))); ?>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>