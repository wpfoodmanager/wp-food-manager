<?php
/**
 * Product data meta box.
 *
 * @package WooCommerce/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="panel-wrap">



	<ul class="product_data_tabs wc-tabs">
		<?php foreach ( self::get_food_data_tabs() as $key => $tab ) : ?>
			<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( isset( $tab['class'] ) ? implode( ' ', (array) $tab['class'] ) : '' ); ?>">
				<a href="#<?php echo esc_attr( $tab['target'] ); ?>"><span><?php echo esc_html( $tab['label'] ); ?></span></a>
			</li>
		<?php endforeach; ?>
		<?php do_action( 'woocommerce_food_write_panel_tabs' ); ?>
	</ul>

	<?php
		self::output_tabs();
		self::output_variations();
		do_action( 'woocommerce_product_data_panels' );
		wc_do_deprecated_action( 'woocommerce_product_write_panels', array(), '2.6', 'Use woocommerce_product_data_panels action instead.' );
	?>
	<div class="clear"></div>
</div>
