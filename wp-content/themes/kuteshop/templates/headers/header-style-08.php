<?php
/**
 * Name:  Header style 08
 **/
?>
<header id="header" class="header style8 cart-style4">
    <div class="header-top">
        <div class="container">
			<?php
			wp_nav_menu( array(
					'menu'            => 'top_left_menu',
					'theme_location'  => 'top_left_menu',
					'depth'           => 1,
					'container'       => '',
					'container_class' => '',
					'container_id'    => '',
					'menu_class'      => 'kuteshop-nav top-bar-menu',
					'fallback_cb'     => 'Kuteshop_navwalker::fallback',
					'walker'          => new Kuteshop_navwalker(),
				)
			);
			?>
            <div class="top-bar-menu right">
				<?php do_action( 'kuteshop_header_control' ); ?>
                <div class="mega-item">
					<?php
					do_action( 'kuteshop_search_form' );
					do_action( 'kuteshop_user_link' );
					?>
                </div>
            </div>
        </div>
    </div>
    <div class="header-middle">
        <div class="container">
            <div class="header-middle-inner">
                <div class="logo">
					<?php do_action( 'kuteshop_get_logo' ); ?>
                </div>
                <div class="header-control main-menu-wapper">
                    <div class="box-header-nav">
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
					<?php do_action( 'kuteshop_header_mini_cart' ); ?>
                    <div class="block-menu-bar">
                        <a class="menu-bar mobile-navigation" href="#">
                            <span class="flaticon-menu03"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
