<?php
/**
 * Name:  Header style 06
 **/
?>
<header id="header" class="header style2 style5 style6 cart-style2">
    <div class="header-top">
        <div class="container">
            <div class="top-bar-menu left">
				<?php
				do_action( 'kuteshop_header_control' );
				do_action( 'kuteshop_header_social' );
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
                <div class="logo">
					<?php do_action( 'kuteshop_get_logo' ); ?>
                </div>
                <div class="header-control">
					<?php do_action( 'kuteshop_search_form' );
					do_action( 'kuteshop_header_mini_cart' );
					if ( defined( 'YITH_WCWL' ) ) :
						$yith_wcwl_wishlist_page_id = get_option( 'yith_wcwl_wishlist_page_id' );
						$wishlist_url = get_page_link( $yith_wcwl_wishlist_page_id );
						if ( $wishlist_url != '' ) : ?>
                            <div class="block-wishlist">
                                <a class="woo-wishlist-link" href="<?php echo esc_url( $wishlist_url ); ?>">
                                    <span class="flaticon-heart01 icon"></span>
                                </a>
                            </div>
						<?php endif;
					endif;
					if ( class_exists( 'YITH_Woocompare' ) ) :
						global $yith_woocompare; ?>
                        <div class="block-compare yith-woocompare-widget">
                            <a href="<?php echo add_query_arg( array( 'iframe' => 'true' ), $yith_woocompare->obj->view_table_url() ) ?>"
                               class="compare added" rel="nofollow">
                                <span class="flaticon-compare01 icon"></span>
                            </a>
                        </div>
					<?php endif; ?>
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
</header>