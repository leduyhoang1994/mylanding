<?php
/**
 * Name:  Header style 14
 **/
?>
<header id="header" class="header style14 cart-style14">
    <div class="header-middle">
        <div class="container">
            <div class="header-middle-inner main-menu-wapper">
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
                <div class="logo">
					<?php do_action( 'kuteshop_get_logo' ); ?>
                </div>
                <div class="header-control">
					<?php
					do_action( 'kuteshop_user_link' );
					do_action( 'kuteshop_header_control' );
					do_action( 'kuteshop_header_mini_cart' );
					do_action( 'kuteshop_search_form' );
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
</header>