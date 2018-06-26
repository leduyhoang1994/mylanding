<?php
/**
 * Name:  Header style 03
 **/
$header_text_box      = apply_filters( 'theme_get_option', 'header_text_box', '' );
$enable_theme_options = apply_filters( 'theme_get_option', 'enable_theme_options' );
$data_meta            = get_post_meta( get_the_ID(), '_custom_metabox_theme_options', true );
if ( !empty( $data_meta ) && $enable_theme_options == 1 ) {
	$header_text_box = isset( $data_meta['metabox_header_text_box'] ) ? $data_meta['metabox_header_text_box'] : $header_text_box;
}
?>
<header id="header" class="header style3 cart-style3">
    <div class="header-top">
        <div class="container">
            <div class="top-bar-menu left">
				<?php do_action( 'kuteshop_header_control' ); ?>
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
                <div class="logo">
					<?php do_action( 'kuteshop_get_logo' ); ?>
                </div>
                <div class="header-mega-box">
                    <div class="top-bar-menu">
						<?php
						wp_nav_menu( array(
								'menu'            => 'top_center_menu',
								'theme_location'  => 'top_center_menu',
								'depth'           => 1,
								'container'       => '',
								'container_class' => '',
								'container_id'    => '',
								'menu_class'      => 'kuteshop-nav top-bar-menu left',
								'fallback_cb'     => 'Kuteshop_navwalker::fallback',
								'walker'          => new Kuteshop_navwalker(),
							)
						);
						?>
                        <div class="header-text-box"><a><?php echo esc_html( $header_text_box ); ?></a></div>
                    </div>
                    <div class="header-search-box">
						<?php do_action( 'kuteshop_search_form' ); ?>
                        <div class="header-control">
							<?php if ( defined( 'YITH_WCWL' ) ) :
								$yith_wcwl_wishlist_page_id = get_option( 'yith_wcwl_wishlist_page_id' );
								$wishlist_url = get_page_link( $yith_wcwl_wishlist_page_id );
								if ( $wishlist_url != '' ) : ?>
                                    <div class="block-wishlist">
                                        <a class="woo-wishlist-link" href="<?php echo esc_url( $wishlist_url ); ?>">
                                            <span class="flaticon-heart01 icon"></span>
                                            <span class="text"><?php echo esc_html__( 'Wishlist', 'kuteshop' ) ?></span>
                                        </a>
                                    </div>
								<?php endif;
							endif;
							do_action( 'kuteshop_user_link' );
							do_action( 'kuteshop_header_mini_cart' );
							?>
                            <div class="block-menu-bar">
                                <a class="menu-bar mobile-navigation" href="#">
                                    <span class="flaticon-menu03"></span>
                                </a>
                            </div>
                        </div>
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
</header>