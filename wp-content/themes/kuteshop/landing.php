	
<?php /* Template Name: Landing */ ?>
<?php get_header(); ?>
<style>
.row{
    padding-right: 0px !important;
}
.landing-container{
    max-width: 1366px !important;
    margin: auto !important;
    padding-left: 20px;
    padding-right: 20px;
}
.blog-thumb a {
    width: 100%;
}
.vc_custom_heading{
    /* padding: 20px 0px; */
}

.vc_images_carousel .vc_carousel-indicators {
    bottom: -50px !important;
}

@media screen and (max-width: 768px) {
    .landing-container{
        overflow-x: hidden;
    }
    .vc_basic_grid .vc_grid.vc_row .vc_grid-item.vc_visible-item, .vc_masonry_grid .vc_grid.vc_row .vc_grid-item.vc_visible-item, .vc_masonry_media_grid .vc_grid.vc_row .vc_grid-item.vc_visible-item, .vc_media_grid .vc_grid.vc_row .vc_grid-item.vc_visible-item{
        width: 50%;
        float: left;        
    }

	h2 {
		font-size: 23px;
	}
}
</style>
<?php
/* Data MetaBox */
$data_meta = get_post_meta( get_the_ID(), '_custom_page_side_options', true );
/* Data MetaBox */
$data_meta_banner               = get_post_meta( get_the_ID(), '_custom_metabox_theme_options', true );
$kuteshop_metabox_enable_banner = isset( $data_meta_banner['kuteshop_metabox_enable_banner'] ) ? $data_meta_banner['kuteshop_metabox_enable_banner'] : 0;
/*Default page layout*/
$kuteshop_page_extra_class = isset( $data_meta['page_extra_class'] ) ? $data_meta['page_extra_class'] : '';
$kuteshop_page_layout      = isset( $data_meta['sidebar_page_layout'] ) ? $data_meta['sidebar_page_layout'] : 'left';
$kuteshop_page_sidebar     = isset( $data_meta['page_sidebar'] ) ? $data_meta['page_sidebar'] : 'widget-area';
/*Main container class*/
$kuteshop_main_container_class   = array();
$kuteshop_main_container_class[] = $kuteshop_page_extra_class;
// $kuteshop_main_container_class[] = 'main-container';
if ( $kuteshop_page_layout == 'full' ) {
	$kuteshop_main_container_class[] = 'no-sidebar';
} else {
	$kuteshop_main_container_class[] = $kuteshop_page_layout . '-sidebar';
}
$kuteshop_main_content_class   = array();
$kuteshop_main_content_class[] = 'main-content';
if ( $kuteshop_page_layout == 'full' ) {
	$kuteshop_main_content_class[] = 'col-sm-12';
} else {
	$kuteshop_main_content_class[] = 'col-lg-9 col-md-8';
}
$kuteshop_slidebar_class   = array();
$kuteshop_slidebar_class[] = 'sidebar';
if ( $kuteshop_page_layout != 'full' ) {
	$kuteshop_slidebar_class[] = 'col-lg-3 col-md-4';
}
?>
    <main class=" <?php echo esc_attr( implode( ' ', $kuteshop_main_container_class ) ); ?>">
		<!-- <?php do_action( 'kuteshop_page_banner' ); ?> -->
        <div class="container-flud landing-container">
            <div class="row">
                <div class="<?php echo esc_attr( implode( ' ', $kuteshop_main_content_class ) ); ?>">
					<?php if ( $kuteshop_metabox_enable_banner != 1 ) : ?>
					<?php endif;
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							?>
                            <div class="page-main-content">
								<?php
								the_content();
								wp_link_pages( array(
										'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'kuteshop' ) . '</span>',
										'after'       => '</div>',
										'link_before' => '<span>',
										'link_after'  => '</span>',
										'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'kuteshop' ) . ' </span>%',
										'separator'   => '<span class="screen-reader-text">, </span>',
									)
								);
								?>
                            </div>
							<?php
						}
					}
					?>
                </div>
				<?php if ( $kuteshop_page_layout != "full" ):
					if ( is_active_sidebar( $kuteshop_page_sidebar ) ) : ?>
                        <div id="widget-area"
                             class="widget-area <?php echo esc_attr( implode( ' ', $kuteshop_slidebar_class ) ); ?>">
							<?php dynamic_sidebar( $kuteshop_page_sidebar ); ?>
                        </div><!-- .widget-area -->
					<?php endif;
				endif; ?>
            </div>
        </div>
    </main>
<?php get_footer(); ?>
<script>
    jQuery(".parralax-customizer").css("background-image", "url("+jQuery(".parralax-customizer").attr("data-vc-parallax-image")+")");
</script>