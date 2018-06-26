<?php
global $product;
// Ensure visibility
if ( empty( $product ) || !$product->is_visible() ) {
	return;
}
// Custom columns
$kuteshop_woo_bg_items      = apply_filters( 'theme_get_option', 'kuteshop_woo_bg_items', 4 );
$kuteshop_woo_lg_items      = apply_filters( 'theme_get_option', 'kuteshop_woo_lg_items', 4 );
$kuteshop_woo_md_items      = apply_filters( 'theme_get_option', 'kuteshop_woo_md_items', 4 );
$kuteshop_woo_sm_items      = apply_filters( 'theme_get_option', 'kuteshop_woo_sm_items', 6 );
$kuteshop_woo_xs_items      = apply_filters( 'theme_get_option', 'kuteshop_woo_xs_items', 6 );
$kuteshop_woo_ts_items      = apply_filters( 'theme_get_option', 'kuteshop_woo_ts_items', 12 );
$kuteshop_woo_product_style = apply_filters( 'theme_get_option', 'kuteshop_shop_product_style', 1 );
$shop_display_mode          = apply_filters( 'theme_get_option', 'shop_page_layout', 'grid' );
if ( isset( $_SESSION['shop_display_mode'] ) ) {
	$shop_display_mode = $_SESSION['shop_display_mode'];
}
$classes[] = 'product-item';
if ( $shop_display_mode == 'grid' ) {
	$classes[] = 'col-bg-' . $kuteshop_woo_bg_items;
	$classes[] = 'col-lg-' . $kuteshop_woo_lg_items;
	$classes[] = 'col-md-' . $kuteshop_woo_md_items;
	$classes[] = 'col-sm-' . $kuteshop_woo_sm_items;
	$classes[] = 'col-xs-' . $kuteshop_woo_xs_items;
	$classes[] = 'col-ts-' . $kuteshop_woo_ts_items;
} else {
	$classes[] = 'list col-sm-12';
}
$template_style = 'style-' . $kuteshop_woo_product_style;
if ( $shop_display_mode == 'grid' ) {
	$classes[] = 'style-' . $kuteshop_woo_product_style;
}
?>
<li <?php post_class( $classes ); ?>>
	<?php if ( $shop_display_mode == 'grid' ):
		get_template_part( 'woo-templates/product-styles/content-product', $template_style );
	else:
		get_template_part( 'woo-templates/content-product', 'list' );
	endif; ?>
</li>