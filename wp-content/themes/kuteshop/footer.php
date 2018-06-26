<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Kuteshop
 * @since 1.0
 * @version 1.0
 **/
do_action( 'kuteshop_get_footer' );
if ( is_front_page() ) {
	do_action( 'popup_content' );
}
?>
<a href="#" class="backtotop">
    <i class="pe-7s-angle-up"></i>
</a>
<?php wp_footer(); ?>
</body>
</html>
