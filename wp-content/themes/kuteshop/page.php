<?php get_header(); ?>
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
$kuteshop_main_container_class[] = 'main-container';
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
    <main class="site-main <?php echo esc_attr( implode( ' ', $kuteshop_main_container_class ) ); ?>">
		<?php do_action( 'kuteshop_page_banner' ); ?>
        <div class="container">
			<?php if ( $kuteshop_metabox_enable_banner != 1 ) :
				if ( !is_front_page() ) {
					$args = array(
						'container'     => 'div',
						'before'        => '',
						'after'         => '',
						'show_on_front' => true,
						'network'       => false,
						'show_title'    => true,
						'show_browse'   => false,
						'post_taxonomy' => array(),
						'labels'        => array(),
						'echo'          => true,
					);
					do_action( 'kuteshop_breadcrumb', $args );
				}
			endif; ?>
            <div class="row">
                <div class="<?php echo esc_attr( implode( ' ', $kuteshop_main_content_class ) ); ?>">
					<?php if ( $kuteshop_metabox_enable_banner != 1 ) : ?>
                        <h2 class="page-title">
                            <span><?php single_post_title(); ?></span>
                        </h2>
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
							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) :
								comments_template();
							endif;
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
<?php get_footer();