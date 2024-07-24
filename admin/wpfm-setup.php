<?php

/**
 * From admin panel, setuping post food page, food dashboard page and food listings page.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WPFM_Setup class.
 */
class WPFM_Setup {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @static
	 * @return self Main instance.
	 * @since 1.0.0
	 */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Create a default page for the plugin uses.
	 *
	 * @access public
	 * @param  string $title
	 * @param  string $content
	 * @param  string $option
	 * @return void
	 * @since 1.0.0
	 */
	public function create_page($title, $content, $option) {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => sanitize_title($title),
			'post_title'     => $title,
			'post_content'   => $content,
			'post_parent'    => 0,
			'comment_status' => 'closed',
		);

		$page_id = wp_insert_post($page_data);
		if ($option) {
			update_option($option, $page_id);
		}
	}

	/**
	 * Output Setup page.
	 * 
	 * @access public
	 * @return void
	 * @since 1.0.0
	 */
	public function output() {
		wp_enqueue_style('food_manager_setup_css');
		$step = !empty($_GET['step']) ? absint($_GET['step']) : 1;
		if (isset($_GET['skip-food-manager-setup']) === 1) {
			update_option('food_manager_installation', 0);
			update_option('food_manager_installation_skip', 1);
			wp_redirect(admin_url('index.php'));
			exit;
		}

		if (3 === $step && !empty($_POST)) {
			if (false == wp_verify_nonce($_REQUEST['setup_wizard'], 'step_3')) {
				wp_die(esc_attr__('Error in nonce. Try again.', 'wp-food-manager'));
			}

			$create_pages = isset($_POST['wp-food-manager-create-page']) ? $this->sanitize_array($_POST['wp-food-manager-create-page']) : array();
			$page_titles = $this->sanitize_array($_POST['wp-food-manager-page-title']);
			$pages_to_create = array(
				'add_food'     => '[add_food]',
				'food_dashboard'       => '[food_dashboard]',
				'foods'                => '[foods]',
				'wpfm_food_menu' => '[wpfm_food_menu]',
			);

			foreach ($pages_to_create as $page => $content) {
				if (!isset($create_pages[$page]) || empty($page_titles[$page])) {
					continue;
				}
				$this->create_page(sanitize_text_field($page_titles[$page]), $content, 'food_manager_' . $page . '_page_id');
			}

			update_option('food_manager_installation', 1);
			update_option('food_manager_installation_skip', 0);
		} ?>

		<div class="wrap wp_food_manager wp_food_manager_addons_wrap">
			<h2><?php esc_attr_e('WP Food Manager Setup', 'wp-food-manager'); ?></h2>
			<div class="wpfm-setup-wrapper">
				<ul class="wp-food-manager-setup-steps">

					<?php if ($step === 1) : ?>
						<li class="wp-food-manager-setup-active-step"><?php esc_attr_e('1. Introduction', 'wp-food-manager'); ?></li>
						<li><?php esc_attr_e('2. Page Setup', 'wp-food-manager'); ?></li>
						<li><?php esc_attr_e('3. Done', 'wp-food-manager'); ?></li>
					<?php elseif ($step === 2) : ?>

						<li class="wp-food-manager-setup-active-step"><?php esc_attr_e('1. Introduction', 'wp-food-manager'); ?></li>
						<li class="wp-food-manager-setup-active-step"><?php esc_attr_e('2. Page Setup', 'wp-food-manager'); ?></li>
						<li><?php esc_attr_e('3. Done', 'wp-food-manager'); ?></li>
					<?php elseif ($step === 3) : ?>

						<li class="wp-food-manager-setup-active-step"><?php esc_attr_e('1. Introduction', 'wp-food-manager'); ?></li>
						<li class="wp-food-manager-setup-active-step"><?php esc_attr_e('2. Page Setup', 'wp-food-manager'); ?></li>
						<li class="wp-food-manager-setup-active-step"><?php esc_attr_e('3. Done', 'wp-food-manager'); ?></li>
					<?php endif; ?>

				</ul>
				<?php if (1 === $step) : ?>

					<div class="wpfm-step-window">
						<h3><?php esc_attr_e('Setup Wizard Introduction', 'wp-food-manager'); ?></h3>
						<p><?php printf(esc_attr_e('Thanks for installing WP Food Manager!', 'wp-food-manager')); ?></p>
						<p><?php esc_attr_e('This setup wizard will help you get started by creating various pages for listing your foods, food dashboard and add food along with setting up Ingredients and Nutritions.'); ?></p>
						<p><?php printf(esc_attr__('The process is still relatively simple if you want to skip the wizard and manually set up the pages and shortcodes yourself. Please refer to the %1$sdocumentation%2$s for support.', 'wp-food-manager'), '<a href="https://wpfoodmanager.com/knowledge-base/">', '</a>'); ?></p>
					</div>
					<p class="submit">
						<a href="<?php echo esc_url(add_query_arg('step', 2)); ?>" class="button button-primary"><?php esc_attr_e('Continue to page setup', 'wp-food-manager'); ?></a>
						<a href="<?php echo esc_url(add_query_arg('skip-food-manager-setup', 1, admin_url('index.php?page=food_manager_setup&step=3'))); ?>" class="button"><?php esc_attr_e('Skip for now', 'wp-food-manager'); ?></a>
					</p>
				<?php endif; ?>

				<?php if (2 === $step) : ?>
					<h3><?php esc_attr_e('Page Setup', 'wp-food-manager'); ?></h3>
					<p><?php printf(__('The <em>WP Food Manager</em> includes %1$sshortcodes%2$s which can be used to output content within your %3$spages%2$s. These can be generated directly as mentioned below. Check the shortcode documentation for more information on food %4$sshortcodes%2$s.', 'wp-food-manager'), '<a href="https://wpfoodmanager.com/knowledge-base/" title="What is a shortcode?" target="_blank" class="help-page-link">', '</a>', '<a href="https://wordpress.org/support/article/pages/" target="_blank" class="help-page-link">', '<a href="https://wpfoodmanager.com/knowledge-base/" target="_blank" class="help-page-link">'); ?></p>
					<form action="<?php echo esc_url(add_query_arg('step', 3)); ?>" method="post">
						<?php wp_nonce_field('step_3', 'setup_wizard'); ?>
						<table class="wp-food-manager-shortcodes widefat">
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th><?php esc_attr_e('Page Title', 'wp-food-manager'); ?></th>
									<th><?php esc_attr_e('Page Description', 'wp-food-manager'); ?></th>
									<th><?php esc_attr_e('Content Shortcode', 'wp-food-manager'); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[foods]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Foods', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[foods]" /></td>
									<td><?php esc_attr_e('This page allows users to browse, search, and filter food listings on the front-end of your site.', 'wp-food-manager'); ?></td>
									<td><code>[foods]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[add_food]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Add Food', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[add_food]" /></td>
									<td>
										<p><?php esc_attr_e('This page allows peoples to add food to your website from the front-end.', 'wp-food-manager'); ?></p>
										<p><?php esc_attr_e('If you do not wish to accept submissions from users in this way (for example you just want to post foods from the admin dashboard) you can skip creating this page.', 'wp-food-manager'); ?></p>
									</td>
									<td><code>[add_food]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[food_dashboard]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Food Dashboard', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[food_dashboard]" /></td>
									<td>
										<p><?php esc_attr_e('This page allows peoples to manage and edit their own foods from the front-end.', 'wp-food-manager'); ?></p>
										<p><?php esc_attr_e('If you plan on managing all listings from the admin dashboard you can skip creating this page.', 'wp-food-manager'); ?></p>
									</td>
									<td><code>[food_dashboard]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[wpfm_food_menu]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Menu', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[wpfm_food_menu]" /></td>
									<td>
										<p><?php esc_attr_e('This page allows peoples to manage and edit their own food menu from the front-end.', 'wp-food-manager'); ?></p>
										<p><?php esc_attr_e('If you plan on managing all listings from the admin dashboard you can skip creating this page.', 'wp-food-manager'); ?></p>
									</td>
									<td><code>[wpfm_food_menu]</code></td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="4">
										<input type="submit" class="button button-primary" value="Create selected pages" />
										<a href="<?php echo esc_url(add_query_arg('step', 3)); ?>" class="button"><?php esc_attr_e('Skip this step', 'wp-food-manager'); ?></a>
									</th>
								</tr>
							</tfoot>
						</table>
					</form>
				<?php endif; ?>

				<?php if (3 === $step) : ?>
					<div class="wpfm-setup-next-block-wrap">
						<div class="wpfm-setup-intro-block">
							<div class="wpfm-setup-done"><i class="wpfm-icon-checkmark"></i>
								<h3><?php esc_attr_e('All Done!', 'wp-food-manager'); ?></h3>
							</div>
							<div class="wpfm-setup-intro-block-welcome">
								<img src="<?php echo WPFM_PLUGIN_URL; ?>/assets/images/wpfm-logo.svg" alt="WP Food Manager">
								<p><?php esc_attr_e('Thanks for installing WP Food Manager! Here are some valuable resources that will assist you in getting started with our plugins.', 'wp-food-manager'); ?></p>
								<div class="wpfm-setup-intro-block-btn">
									<a href="<?php echo esc_url(admin_url('post-new.php?post_type=food_manager')); ?>" class="button button-primary button-hero"><?php esc_attr_e('Create Your First Food', 'wp-food-manager'); ?></a>
									<a href="<?php echo esc_url(admin_url('edit.php?post_type=food_manager&page=food-manager-settings')); ?>" class="button button-secondary button-hero"><?php esc_attr_e('Settings', 'wp-food-manager'); ?></a>
								</div>
							</div>
							<div class="wpfm-setup-help-center">
								<h1><?php esc_attr_e('Helpful Resources', 'wp-food-manager'); ?></h1>
								<div class="wpfm-setup-help-center-block-wrap">
									<div class="wpfm-setup-help-center-block">
										<div class="wpfm-setup-help-center-block-icon">
											<span class="wpfm-setup-help-center-knowledge-base-icon"></span>
										</div>
										<div class="wpfm-setup-help-center-block-content">
											<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('Knowledge Base', 'wp-food-manager'); ?></div>
											<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Solve your queries by browsing our documentation.', 'wp-food-manager'); ?></div>
											<a href="https://wpfoodmanager.com/knowledge-base/" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Browse More', 'wp-food-manager'); ?> »</span></a>
										</div>
									</div>
									<div class="wpfm-setup-help-center-block">
										<div class="wpfm-setup-help-center-block-icon">
											<span class="wpfm-setup-help-center-faqs-icon"></span>
										</div>
										<div class="wpfm-setup-help-center-block-content">
											<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('FAQs', 'wp-food-manager'); ?></div>
											<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Explore through the frequently asked questions.', 'wp-food-manager'); ?></div>
											<a href="https://wpfoodmanager.com/faqs/" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Get Answers', 'wp-food-manager'); ?> »</span></a>
										</div>
									</div>
									<div class="wpfm-setup-help-center-block">
										<div class="wpfm-setup-help-center-block-icon">
											<span class="wpfm-setup-help-center-video-tutorial-icon"></span>
										</div>
										<div class="wpfm-setup-help-center-block-content">
											<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('Video Tutorials', 'wp-food-manager'); ?></div>
											<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Learn different skills by examining attractive video tutorials.', 'wp-food-manager'); ?></div>
											<a href="https://www.youtube.com/channel/UC5j54ZQs7DLM8Dcvc2FwpPQ" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Watch all', 'wp-food-manager'); ?> »</span></a>
										</div>
									</div>
								</div>
								<div class="wpfm-setup-addon-support">
									<div class="wpfm-setup-addon-support-wrap">
										<div class="wpfm-setup-help-center-block-icon">
											<span class="wpfm-setup-help-center-support-icon"></span>
										</div>
										<div class="wpfm-setup-help-center-block-content">
											<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('Add ons Support', 'wp-food-manager'); ?></div>
											<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Get support for all the Add ons related queries with our experienced/ talented support team.', 'wp-food-manager'); ?></div>
											<a href="https://wpfoodmanager.com/help-center/" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Get Add ons Support', 'wp-food-manager'); ?> »</span></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
<?php }

	/**
	 * Sanitize a 2 dimension array.
	 *
	 * @access private
	 * @param  array $array
	 * @return array
	 * @since 1.0.0
	 */
	private function sanitize_array($input) {
		if (is_array($input)) {
			foreach ($input as $key => $value) {
				$input[$key] = $this->sanitize_array($value);
			}
			return $input;
		} else {
			return sanitize_text_field($input);
		}
	}
}

WPFM_Setup::instance();
