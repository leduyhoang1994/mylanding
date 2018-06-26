<?php get_header();
/*Shop layout*/
$kuteshop_woo_shop_layout = apply_filters( 'theme_get_option', 'sidebar_shop_page_position', 'left' );
if ( is_product() ) {
	$kuteshop_woo_shop_layout = apply_filters( 'theme_get_option', 'sidebar_product_position', 'left' );
}
/*Setting single product*/
$main_content_class   = array();
$main_content_class[] = 'main-content';
if ( $kuteshop_woo_shop_layout == 'full' ) {
	$main_content_class[] = 'col-sm-12';
} else {
	$main_content_class[] = 'col-lg-9 col-md-8 has-sidebar';
}
/**
 * woocommerce_before_main_content hook
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );
?>
    <div class="<?php echo esc_attr( implode( ' ', $main_content_class ) ); ?>">
		<?php if ( !is_product() ) {
			do_action( 'kuteshop_shop_banner' );
		} ?>
		<?php do_action( 'kuteshop_woocommerce_content' ); ?>
    </div>
<?php
/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );
?>
<?php get_footer(); ?>