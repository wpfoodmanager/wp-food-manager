<?php
/*
* From admin panel, setuping post food page, food dashboard page and food listings page.
*
*/

if (!defined('ABSPATH')) {

	exit;
}

/**
 * WP_Food_Manager_Setup class.
 */

class WP_Food_Manager_Setup
{

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */

	public function __construct()
	{

		add_action('admin_menu', array($this, 'admin_menu'), 12);

		add_action('admin_head', array($this, 'admin_head'));

		add_action('admin_init', array($this, 'redirect'));

		if (isset($_GET['page']) && 'food-manager-setup' === $_GET['page']) {
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 12);
		}
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */

	public function admin_menu()
	{

		add_dashboard_page(__('Setup', 'wp-food-manager'), __('Setup', 'wp-food-manager'), 'manage_options', 'food-manager-setup', array($this, 'output'));
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 *
	 * @access public
	 * @return void
	 */

	public function admin_head()
	{

		remove_submenu_page('index.php', 'food-manager-setup');
	}

	/**
	 * Sends user to the setup page on first activation
	 */

	public function redirect()
	{

		global $pagenow;

		if (isset($_GET['page']) && $_GET['page'] === 'food-manager-setup') {
			if (get_option('wpfm_installation', false)) {
				wp_redirect(admin_url('index.php'));
				exit;
			}
		}

		// Bail if no activation redirect transient is set

		if (!get_transient('_food_manager_activation_redirect')) {

			return;
		}

		if (!current_user_can('manage_options')) {

			return;
		}

		// Delete the redirect transient
		delete_transient('_food_manager_activation_redirect');

		
		// Bail if activating from network, or bulk, or within an iFrame
		if (is_network_admin() || isset($_GET['activate-multi']) || defined('IFRAME_REQUEST')) {

			return;
		}

		if ((isset($_GET['action']) && 'upgrade-plugin' == $_GET['action']) && (isset($_GET['plugin']) && strstr($_GET['plugin'], 'wp-food-manager.php'))) {

			return;
		}

		wp_redirect(admin_url('index.php?page=food-manager-setup'));

		exit;
	}

	/**
	 * Enqueue scripts for setup page
	 */

	public function admin_enqueue_scripts()
	{

		wp_enqueue_style('food_manager_setup_css', WPFM_PLUGIN_URL . '/assets/css/setup.min.css', array('dashicons'));
	}

	/**
	 * Create a page.
	 *
	 * @param  string $title
	 * @param  string $content
	 * @param  string $option
	 */

	public function create_page($title, $content, $option)
	{

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
	 * Output addons page
	 */

	public function output()
	{

		$step = !empty($_GET['step']) ? absint($_GET['step']) : 1;

		if (isset($_GET['skip-food-manager-setup']) === 1) {
			update_option('wpfm_installation', 0);
			update_option('wpfm_installation_skip', 1);

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

				'submit_food_form'     => '[submit_food_form]',

				'food_dashboard'       => '[food_dashboard]',

				'foods'                => '[foods]',

				/*'neutritions_dashboard' => '[neutritions_dashboard]',

				'ingredients_dashboard'   => '[ingredients_dashboard]',*/

			);

			foreach ($pages_to_create as $page => $content) {

				if (!isset($create_pages[$page]) || empty($page_titles[$page])) {

					continue;
				}

				$this->create_page(sanitize_text_field($page_titles[$page]), $content, 'food_manager_' . $page . '_page_id');
			}

			update_option('wpfm_installation', 1);
			update_option('wpfm_installation_skip', 0);
		}

?>

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

						<p><?php esc_attr_e('This setup wizard will help you get started by creating various pages for food type, food management, and listing your foods, along with setting up Ingredients and Neutritions pages.'); ?></p>

						<p><?php printf(esc_attr__('The process is still relatively simple if you want to skip the wizard and manually set up the pages and shortcodes yourself. Please refer to the %1$sdocumentation%2$s for support.', 'wp-food-manager'), '<a href="https://wpfoodmanager.com/">', '</a>'); ?></p>
					</div>
					<p class="submit">

						<a href="<?php echo esc_url(add_query_arg('step', 2)); ?>" class="button button-primary"><?php esc_attr_e('Continue to page setup', 'wp-food-manager'); ?></a>

						<a href="<?php echo esc_url(add_query_arg('skip-food-manager-setup', 1, admin_url('index.php?page=food-manager-setup&step=3'))); ?>" class="button"><?php esc_attr_e('Skip for now', 'wp-food-manager'); ?></a>

					</p>

				<?php endif; ?>

				<?php if (2 === $step) : ?>

					<h3><?php esc_attr_e('Page Setup', 'wp-food-manager'); ?></h3>

					<p><?php printf(__('The <em>WP Food Manager</em> includes %1$sshortcodes%2$s which can be used to output content within your %3$spages%2$s. These can be generated directly as mentioned below. Check the shortcode documentation for more information on food %4$sshortcodes%2$s.', 'wp-food-manager'), '<a href="https://wpfoodmanager.com/" title="What is a shortcode?" target="_blank" class="help-page-link">', '</a>', '<a href="https://wordpress.org/support/article/pages/" target="_blank" class="help-page-link">', '<a href="https://wpfoodmanager.com/" target="_blank" class="help-page-link">'); ?></p>

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

									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[submit_food_form]" /></td>

									<td><input type="text" value="<?php echo esc_attr(_x('Post an Food', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[submit_food_form]" /></td>

									<td>
										<p><?php esc_attr_e('This page allows peoples to post foods to your website from the front-end.', 'wp-food-manager'); ?></p>

										<p><?php esc_attr_e('If you do not wish to accept submissions from users in this way (for example you just want to post foods from the admin dashboard) you can skip creating this page.', 'wp-food-manager'); ?></p>
									</td>
									<td><code>[submit_food_form]</code></td>
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
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[foods]" /></td>

									<td><input type="text" value="<?php echo esc_attr(_x('Foods', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[foods]" /></td>

									<td><?php esc_attr_e('This page allows users to browse, search, and filter food listings on the front-end of your site.', 'wp-food-manager'); ?></td>

									<td><code>[foods]</code></td>
								</tr>

								<tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[food_categories]" /></td>

									<td><input type="text" value="<?php echo esc_attr(_x('Food Categories', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[food_categories]" /></td>

									<td>
										<p><?php esc_attr_e('This page allows peoples to manage and edit their own food categories from the front-end.', 'wp-food-manager'); ?></p>

										<p><?php esc_attr_e('In case if you do not want to allow your users to show menus from the frontend, you can uncheck this and skip creating this page.', 'wp-food-manager'); ?></p>
									</td>

									<td><code>[food_categories]</code></td>
								</tr>

								<tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[food_type]" /></td>

									<td><input type="text" value="<?php echo esc_attr(_x('Food Type', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[food_type]" /></td>

									<td>
										<p><?php esc_attr_e('This page allows peoples to manage and edit their own food type from the front-end.', 'wp-food-manager'); ?></p>

										<p><?php esc_attr_e('In case if you do not want to allow your users to show menus from the frontend, you can uncheck this and skip creating this page.', 'wp-food-manager'); ?></p>
									</td>

									<td><code>[food_type]</code></td>
								</tr>

								<!-- <tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[neutritions_dashboard]" /></td>

									<td><input type="text" value="<?php echo esc_attr(_x('Neutritions Dashboard', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[neutritions_dashboard]" /></td>

									<td>
										<p><?php esc_attr_e('This page allows peoples to manage and edit their own neutritions from the front-end.', 'wp-food-manager'); ?></p>

										<p><?php esc_attr_e('In case if you do not want to allow your users to show menus from the frontend, you can uncheck this and skip creating this page.', 'wp-food-manager'); ?></p>
									</td>

									<td><code>[neutritions_dashboard]</code></td>
								</tr>

								<tr>
									<td><input type="checkbox" checked="checked" name="wp-food-manager-create-page[ingredients_dashboard]" /></td>

									<td><input type="text" value="<?php echo esc_attr(_x('Ingredients Dashboard', 'Default page title (wizard)', 'wp-food-manager')); ?>" name="wp-food-manager-page-title[ingredients_dashboard]" /></td>

									<td>
										<p><?php esc_attr_e('This page allows peoples to manage and edit their own ingredients from the front-end.', 'wp-food-manager'); ?></p>

										<p><?php esc_attr_e('In case if you do not want to allow your users to show menus from the frontend, you can uncheck this and skip creating this page.', 'wp-food-manager'); ?></p>
									</td>

									<td><code>[ingredients_dashboard]</code></td>
								</tr> -->

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
							<div class="wpfm-setup-done"><i class="wpem-icon-checkmark"></i>
								<h3><?php esc_attr_e('All Done!', 'wp-food-manager'); ?></h3>
							</div>
							<div class="wpfm-setup-intro-block-welcome">

								<img src="<?php echo WPFM_PLUGIN_URL; ?>/assets/images/wpfm-logo.svg" alt="WP Food Manager">
								<p><?php esc_attr_e('Thanks for installing WP Food Manager! Here are some valuable resources that will assist you in getting started with our plugins.', 'wp-food-manager'); ?></p>
								<div class="wpfm-backend-video-wrap">
									<iframe width="560" height="315" src="https://www.youtube.com/embed/hlDVYtEDOgQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
								</div>
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
											<a href="https://wpfoodmanager.com/" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Browse More', 'wp-food-manager'); ?> »</span></a>
										</div>
									</div>
									<div class="wpfm-setup-help-center-block">
										<div class="wpfm-setup-help-center-block-icon">
											<span class="wpfm-setup-help-center-faqs-icon"></span>
										</div>
										<div class="wpfm-setup-help-center-block-content">
											<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('FAQs', 'wp-food-manager'); ?></div>
											<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Explore through the frequently asked questions.', 'wp-food-manager'); ?></div>
											<a href="https://wpfoodmanager.com/" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Get Answers', 'wp-food-manager'); ?> »</span></a>
										</div>
									</div>
									<div class="wpfm-setup-help-center-block">
										<div class="wpfm-setup-help-center-block-icon">
											<span class="wpfm-setup-help-center-video-tutorial-icon"></span>
										</div>
										<div class="wpfm-setup-help-center-block-content">
											<div class="wpfm-setup-help-center-block-heading"><?php esc_attr_e('Video Tutorials', 'wp-food-manager'); ?></div>
											<div class="wpfm-setup-help-center-block-desc"><?php esc_attr_e('Learn different skills by examining attractive video tutorials.', 'wp-food-manager'); ?></div>
											<a href="https://www.youtube.com/channel/UCnfYxg-fegS_n9MaPNU61bg" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Watch all', 'wp-food-manager'); ?> »</span></a>
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
											<a href="https://wpfoodmanager.com/" target="_blank" class="wpfm-setup-help-center-block-link"><span class="wpfm-setup-help-center-box-target-text"><?php esc_attr_e('Get Add ons Support', 'wp-food-manager'); ?> »</span></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

				<?php endif; ?>

			</div>
		</div>

<?php
	}


	/**
	 * Sanitize a 2d array
	 *
	 * @param  array $array
	 * @return array
	 */
	private function sanitize_array($input)
	{
		if (is_array($input)) {
			foreach ($input as $k => $v) {
				$input[$k] = $this->sanitize_array($v);
			}
			return $input;
		} else {
			return sanitize_text_field($input);
		}
	}
}
new WP_Food_Manager_Setup();
