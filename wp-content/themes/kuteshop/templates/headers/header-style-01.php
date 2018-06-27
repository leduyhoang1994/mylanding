<?php
/**
 * Name:  Header style 01
 **/
$enable_theme_options = apply_filters( 'theme_get_option', 'enable_theme_options' );
$data_meta            = get_post_meta( get_the_ID(), '_custom_metabox_theme_options', true );
$header_border_class  = '';
$header_border_html   = '';
if ( is_front_page() ) {
	$header_border_html  = '<div class="container"><div class="header-border"></div></div>';
	$header_border_class = 'has-border';
}
?>
<header id="header" class="header style1 cart-style1 <?php echo esc_attr( $header_border_class ); ?>">
    <div class="header-top hidden">
        <div class="container">
            <div class="top-bar-menu left">
				<?php
				wp_nav_menu( array(
						'menu'            => 'top_left_menu',
						'theme_location'  => 'top_left_menu',
						'depth'           => 1,
						'container'       => '',
						'container_class' => '',
						'container_id'    => '',
						'menu_class'      => 'kuteshop-nav top-bar-menu left',
						'fallback_cb'     => 'Kuteshop_navwalker::fallback',
						'walker'          => new Kuteshop_navwalker(),
					)
				);
				do_action( 'kuteshop_header_control' );
				?>
            </div>
			<?php
			wp_nav_menu( array(
					'menu'            => 'top_right_menu',
					'theme_location'  => 'top_right_menu',
					'depth'           => 1,
					'container'       => '',
					'container_class' => '',
					'container_id'    => '',
					'menu_class'      => 'kuteshop-nav top-bar-menu right',
					'fallback_cb'     => 'Kuteshop_navwalker::fallback',
					'walker'          => new Kuteshop_navwalker(),
				)
			);
			?>
        </div>
    </div>
    <div class="header-middle">
        <div class="container">
            <div class="header-middle-inner">
                <div class="logo" style="text-align: center;">
					<?php do_action( 'kuteshop_get_logo' ); ?>
                </div>
                <div class="header-control">
					<?php
					// do_action( 'kuteshop_search_form' );
					// do_action( 'kuteshop_header_mini_cart' );
					?>
                    <div class="block-menu-bar">
                        <a class="menu-bar mobile-navigation" href="#">
                            <span class="flaticon-menu01"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-nav">
        <div class="container">
            <div class="header-nav-inner">
				<?php do_action( 'kuteshop_header_vertical' ); ?>
                <div class="box-header-nav main-menu-wapper">
					<?php
					wp_nav_menu( array(
							'menu'            => 'primary',
							'theme_location'  => 'primary',
							'depth'           => 3,
							'container'       => '',
							'container_class' => '',
							'container_id'    => '',
							'menu_class'      => 'clone-main-menu kuteshop-nav main-menu',
							'fallback_cb'     => 'Kuteshop_navwalker::fallback',
							'walker'          => new Kuteshop_navwalker(),
						)
					);
					?>
                </div>
            </div>
        </div>
    </div>
	<?php echo htmlspecialchars_decode( $header_border_html ); ?>
</header>