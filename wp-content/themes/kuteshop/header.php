<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Kuteshop
 * @since 1.0
 * @version 1.0
 */
?>
    <!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
		<?php wp_head(); ?>
        <script>
            var $ = jQuery;
            $(window).load(function() {
                $(".bg_load").fadeOut("slow");
                $(".wrapper").fadeOut("slow");
            });
        </script>
		<script src="<?= get_theme_file_uri( '/assets/js/custom.js' ) ?>"></script>
    </head>

<body <?php body_class(); ?>>
<div class="bg_load"></div>
<div class="wrapper">
    <div class="inner">
        <span>Đ</span>
        <span>a</span>
        <span>n</span>
        <span>g</span>
        <span> </span>
        <span>T</span>
        <span>ả</span>
        <span>i</span>
    </div>
</div>
<?php do_action( 'kuteshop_header_sticky' ); ?>
    <div class="body-overlay"></div>
    <div id="box-mobile-menu" class="box-mobile-menu full-height">
        <a href="#" id="back-menu" class="back-menu"><i class="pe-7s-angle-left"></i></a>
        <span class="box-title"><?php echo esc_html__( 'MAIN MENU', 'kuteshop' ); ?></span>
        <a href="#" class="close-menu"><i class="pe-7s-close"></i></a>
        <div class="box-inner"></div>
    </div>
<?php do_action( 'kuteshop_get_header' ); ?>