<?php
/**
 * Name:  Header style 12
 **/
?>
<header id="header" class="header style12 cart-style12">
    <div class="header-top">
        <div class="container">
            <div class="header-top-inner">
                <div class="top-bar-menu left">
					<?php
					wp_nav_menu( array(
							'menu'            => 'top_right_menu',
							'theme_location'  => 'top_right_menu',
							'depth'           => 1,
							'container'       => '',
							'container_class' => '',
							'container_id'    => '',
							'menu_class'      => 'kuteshop-nav top-bar-menu',
							'fallback_cb'     => 'Kuteshop_navwalker::fallback',
							'walker'          => new Kuteshop_navwalker(),
						)
					);
					do_action( 'kuteshop_header_control' );
					?>
                </div>
                <div class="top-bar-menu right">
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
					do_action( 'kuteshop_header_social' );
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
				<?php
				do_action( 'kuteshop_header_vertical' );
				do_action( 'kuteshop_search_form' );
				do_action( 'kuteshop_header_mini_cart' );
				?>
            </div>
        </div>
    </div>
</header>