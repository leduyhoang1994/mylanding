<?php
if ( !class_exists( 'Kuteshop_Woo_Functions' ) ) {
	class Kuteshop_Woo_Functions
	{
		/**
		 * Single instance of the class
		 *
		 * @var \Kuteshop_Woo_Functions
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \Kuteshop_Woo_Functions
		 * @since 1.0.0
		 */
		public static function get_instance()
		{
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct()
		{
			add_action( 'init', array( $this, 'product_per_page_request' ) );
			add_action( 'init', array( $this, 'product_display_mode_request' ) );
			add_action( 'kuteshop_header_mini_cart', array( $this, 'kuteshop_header_mini_cart' ) );
			add_action( 'kuteshop_woocommerce_content', array( $this, 'kuteshop_woocommerce_content' ) );
			$this->action_product_woocommerce();
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'kuteshop_cart_link_fragment' ) );
			add_action( 'wp_ajax_kuteshop_remove_cart_item_via_ajax', array( $this, 'kuteshop_remove_cart_item_via_ajax' ) );
			add_action( 'wp_ajax_nopriv_kuteshop_remove_cart_item_via_ajax', array( $this, 'kuteshop_remove_cart_item_via_ajax' ) );
		}

		function action_product_woocommerce()
		{
			add_action( 'woocommerce_before_main_content', array( $this, 'kuteshop_woocommerce_before_main_content' ) );
			add_action( 'woocommerce_after_main_content', array( $this, 'kuteshop_woocommerce_after_main_content' ) );
			/* CUSTOM WC TEMPLATE PART */
			add_filter( 'wc_get_template', array( $this, 'kuteshop_wc_get_template' ), 10, 5 );
			add_filter( 'wc_get_template_part', array( $this, 'kuteshop_wc_get_template_part' ), 10, 3 );
			/* REMOVE Div COVE IN CONTENT SHOP */
			remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
			remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
			/* BREADCRUMB */
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
			add_action( 'woocommerce_before_main_content', array( $this, 'kuteshop_woocommerce_breadcrumb' ), 20 );
			/* REMOVE CSS */
			add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
			remove_action( 'yith_wcqv_product_summary', 'woocommerce_template_single_meta', 30 );
			/* PRODUCT THUMBNAIL */
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'kuteshop_template_loop_product_thumbnail' ), 10 );
			/* REMOVE woocommerce_template_loop_product_link_open */
			remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
			/* ADD COUNTDOWN PRODUCT */
			add_action( 'kuteshop_function_shop_loop_item_countdown', array( $this, 'kuteshop_function_shop_loop_item_countdown' ), 1 );
			/* CUSTOM FLASH */
			add_filter( 'woocommerce_sale_flash', array( $this, 'kuteshop_custom_sale_flash' ) );
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'kuteshop_group_flash' ), 5 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'kuteshop_group_flash' ), 10 );
			/* CUSTOM PRODUCT NAME */
			remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
			add_action( 'woocommerce_shop_loop_item_title', array( $this, 'kuteshop_template_loop_product_title' ), 10 );
			/* QUICK VIEW */
			if ( class_exists( 'YITH_WCQV_Frontend' ) ) {
				// Class frontend
				$enable           = get_option( 'yith-wcqv-enable' ) == 'yes' ? true : false;
				$enable_on_mobile = get_option( 'yith-wcqv-enable-mobile' ) == 'yes' ? true : false;
				// Class frontend
				if ( ( !wp_is_mobile() && $enable ) || ( wp_is_mobile() && $enable_on_mobile && $enable ) ) {
					remove_action( 'woocommerce_after_shop_loop_item', array( YITH_WCQV_Frontend::get_instance(), 'yith_add_quick_view_button' ), 15 );
					add_action( 'kuteshop_function_shop_loop_item_quickview', array( YITH_WCQV_Frontend::get_instance(), 'yith_add_quick_view_button' ), 5 );
				}
			}
			/* WISH LIST */
			if ( defined( 'YITH_WCWL' ) ) {
				add_action( 'kuteshop_function_shop_loop_item_wishlist', create_function( '', 'echo do_shortcode( "[yith_wcwl_add_to_wishlist]" );' ), 1 );
			}
			/* COMPARE */
			if ( class_exists( 'YITH_Woocompare' ) && get_option( 'yith_woocompare_compare_button_in_products_list' ) == 'yes' ) {
				global $yith_woocompare;
				$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
				if ( $yith_woocompare->is_frontend() || $is_ajax ) {
					if ( $is_ajax ) {
						if ( !class_exists( 'YITH_Woocompare_Frontend' ) && file_exists( YITH_WOOCOMPARE_DIR . 'includes/class.yith-woocompare-frontend.php' ) ) {
							require_once YITH_WOOCOMPARE_DIR . 'includes/class.yith-woocompare-frontend.php';
						}
						$yith_woocompare->obj = new YITH_Woocompare_Frontend();
					}
					/* Remove button */
					remove_action( 'woocommerce_after_shop_loop_item', array( $yith_woocompare->obj, 'add_compare_link' ), 20 );
					/* Add compare button */
					if ( !function_exists( 'kuteshop_wc_loop_product_compare_btn' ) ) {
						function kuteshop_wc_loop_product_compare_btn()
						{
							if ( shortcode_exists( 'yith_compare_button' ) ) {
								echo do_shortcode( '[yith_compare_button product_id="' . get_the_ID() . '"]' );
							} else {
								if ( class_exists( 'YITH_Woocompare_Frontend' ) ) {
									echo do_shortcode( '[yith_compare_button product_id="' . get_the_ID() . '"]' );
								}
							}
						}
					}
					add_action( 'kuteshop_function_shop_loop_item_compare', 'kuteshop_wc_loop_product_compare_btn', 1 );
				}
			}
			/* REMOVE STAR RATING */
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 20 );
			/* CUSTOM RATING SINGLE */
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'kuteshop_custom_ratting_single_product' ), 5 );
			/* UPSELL */
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'kuteshop_upsell_display' ), 15 );
			/* RELATED */
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'kuteshop_related_products' ), 20 );
			/* CROSS SELL */
			remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
			add_action( 'woocommerce_cart_collaterals', array( $this, 'kuteshop_cross_sell_products' ), 30 );
			/* CUSTOM PRODUCT POST PER PAGE */
			add_filter( 'loop_shop_per_page', array( $this, 'kuteshop_loop_shop_per_page' ), 20 );
			add_filter( 'woof_products_query', array( $this, 'kuteshop_woof_products_query' ), 20 );
			/* CUSTOM SHOP CONTROL */
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
			remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
			add_filter( 'woocommerce_page_title', create_function( '$page_title', 'return "<span>" . $page_title . "</span>";' ) );
			add_action( 'woocommerce_before_shop_loop', array( $this, 'kuteshop_before_shop_control' ), 1 );
			add_action( 'woocommerce_after_shop_loop', array( $this, 'kuteshop_after_shop_control' ), 1 );
			/* SHOP BANNER */
			add_action( 'kuteshop_shop_banner', array( $this, 'kuteshop_shop_banner' ), 1 );
		}

		function kuteshop_wc_get_template( $located, $template_name, $args, $template_path, $default_path )
		{
			if ( $template_name == 'global/quantity-input.php' ) {
				$located = get_template_directory() . '/woo-templates/quantity-input.php';
			}

			return $located;
		}

		function kuteshop_wc_get_template_part( $template, $slug, $name )
		{
			if ( $slug == 'content' && $name == 'product' ) {
				$template = get_template_directory() . '/woo-templates/content-product.php';
			}

			return $template;
		}

		function kuteshop_woocommerce_content()
		{
			if ( is_singular( 'product' ) ) {
				while ( have_posts() ) : the_post();
					wc_get_template_part( 'content', 'single-product' );
				endwhile;
			} else {
				if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
                    <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
				<?php endif;
				do_action( 'woocommerce_archive_description' );
				if ( have_posts() ) :
					do_action( 'woocommerce_before_shop_loop' );
					echo '<ul class="row products auto-clear equal-container product-grid better-height">';
					woocommerce_product_subcategories();
					while ( have_posts() ) : the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					echo '</ul>';
					do_action( 'woocommerce_after_shop_loop' );
                elseif ( !woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) :
					do_action( 'woocommerce_no_products_found' );
				endif;
			}
		}

		function kuteshop_woocommerce_before_main_content()
		{
			/*Shop layout*/
			$kuteshop_woo_shop_layout = apply_filters( 'theme_get_option', 'sidebar_shop_page_position', 'left' );
			if ( is_product() ) {
				$kuteshop_woo_shop_layout = apply_filters( 'theme_get_option', 'sidebar_product_position', 'left' );
			}
			/*Main container class*/
			$main_container_class   = array();
			$main_container_class[] = 'main-container shop-page';
			if ( $kuteshop_woo_shop_layout == 'full' ) {
				$main_container_class[] = 'no-sidebar';
			} else {
				$main_container_class[] = $kuteshop_woo_shop_layout . '-sidebar';
			}
			?>
            <div class="<?php echo esc_attr( implode( ' ', $main_container_class ) ); ?>">
            <div class="container">
            <div class="row">
			<?php
		}

		function kuteshop_woocommerce_after_main_content()
		{
			/*Shop layout*/
			$kuteshop_woo_shop_layout  = apply_filters( 'theme_get_option', 'sidebar_shop_page_position', 'left' );
			$kuteshop_woo_shop_sidebar = apply_filters( 'theme_get_option', 'shop_page_sidebar', 'shop-widget-area' );
			if ( is_product() ) {
				$kuteshop_woo_shop_layout  = apply_filters( 'theme_get_option', 'sidebar_product_position', 'left' );
				$kuteshop_woo_shop_sidebar = apply_filters( 'theme_get_option', 'single_product_sidebar', 'product-widget-area' );
			}
			$slidebar_class   = array();
			$slidebar_class[] = 'sidebar';
			if ( $kuteshop_woo_shop_layout != 'full' ) {
				$slidebar_class[] = 'col-lg-3 col-md-4';
			}
			if ( $kuteshop_woo_shop_layout != "full" ): ?>
                <div class="<?php echo esc_attr( implode( ' ', $slidebar_class ) ); ?>">
					<?php if ( is_active_sidebar( $kuteshop_woo_shop_sidebar ) ) : ?>
                        <div id="widget-area" class="widget-area shop-sidebar">
							<?php dynamic_sidebar( $kuteshop_woo_shop_sidebar ); ?>
                        </div><!-- .widget-area -->
					<?php endif; ?>
                </div>
			<?php endif; ?>
            </div>
            </div>
            </div>
			<?php
		}

		function kuteshop_before_shop_control()
		{
			echo '<div class="shop-before-control">';
			woocommerce_catalog_ordering();
			self::kuteshop_product_per_page_tmp();
			self::kuteshop_shop_display_mode_tmp();
			echo '</div>';
		}

		function kuteshop_after_shop_control()
		{
			echo '<div class="shop-after-control">';
			woocommerce_result_count();
			self::kuteshop_custom_pagination();
			echo '</div>';
		}

		function kuteshop_woocommerce_breadcrumb()
		{
			$args = array(
				'delimiter'   => '',
				'wrap_before' => '<div class="woocommerce-breadcrumb breadcrumbs col-sm-12"><ul class="breadcrumb">',
				'wrap_after'  => '</ul></div>',
				'before'      => '<li>',
				'after'       => '</li>',
			);
			woocommerce_breadcrumb( $args );
		}

		/* SHOP BANNER */
		function kuteshop_shop_banner()
		{
			$enable_shop_banner = apply_filters( 'theme_get_option', 'enable_shop_banner' );
			$woo_shop_banner    = apply_filters( 'theme_get_option', 'woo_shop_banner', '' );
			$sidebar_shop_page  = apply_filters( 'theme_get_option', 'sidebar_shop_page_position', 'left' );
			$lazy_check         = apply_filters( 'theme_get_option', 'kuteshop_theme_lazy_load' ) == 1 ? true : false;
			$banner_class       = array( 'banner-shop owl-slick' );
			$woo_shop_banner    = explode( ',', $woo_shop_banner );
			if ( $sidebar_shop_page == 'full' ) {
				$w = '1170';
				$h = '300';
			} else {
				$w = '870';
				$h = '288';
			}
			$atts         = array(
				'owl_loop'       => 'false',
				'owl_autoplay'   => 'true',
				'owl_ls_items'   => 1,
				'owl_navigation' => 'true',
			);
			$owl_settings = apply_filters( 'generate_carousel_data_attributes', 'owl_', $atts );
			if ( $enable_shop_banner == 1 ) : ?>
                <div class="<?php echo esc_attr( implode( ' ', $banner_class ) ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
					<?php foreach ( $woo_shop_banner as $value ) : ?>
                        <div class="banner-item">
							<?php
							$image_thumb = apply_filters( 'theme_resize_image', $value, $w, $h, true, $lazy_check );
							echo htmlspecialchars_decode( $image_thumb['img'] );
							?>
                        </div>
					<?php endforeach; ?>
                </div>
			<?php endif;
		}

		/* VIEW MORE */
		function product_display_mode_request()
		{
			if ( isset( $_POST['display_mode_action'] ) ) {
				$_SESSION['shop_display_mode'] = $_POST["display_mode_value"];
				wp_redirect( $_POST['display_mode_action'] );
				exit();
			}
		}

		function kuteshop_shop_display_mode_tmp()
		{
			$shop_display_mode = apply_filters( 'theme_get_option', 'shop_page_layout', 'grid' );
			$current_url       = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			if ( isset( $_SESSION['shop_display_mode'] ) ) {
				$shop_display_mode = $_SESSION['shop_display_mode'];
			}
			?>
            <div class="grid-view-mode">
                <form method="POST" action="">
                    <button type="submit"
                            class="modes-mode mode-grid display-mode <?php if ( $shop_display_mode == "grid" ): ?>active<?php endif; ?>"
                            value="<?php echo esc_attr( $current_url ); ?>"
                            name="display_mode_action">
                        <span class="button-inner">
                            <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                        </span>
                    </button>
                    <label for="display_mode_value"></label>
                    <input type="hidden" value="grid" name="display_mode_value">
                </form>
                <form method="POST" action="">
                    <button type="submit"
                            class="modes-mode mode-list display-mode <?php if ( $shop_display_mode == "list" ): ?>active<?php endif; ?>"
                            value="<?php echo esc_attr( $current_url ); ?>"
                            name="display_mode_action">
                        <span class="button-inner">
                            <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                        </span>
                    </button>
                    <label for="display_mode_value"></label>
                    <input type="hidden" value="list" name="display_mode_value">
                </form>
            </div>
			<?php
		}

		/*----------------------
        POST PER PAGE SHOP
        ----------------------*/
		function kuteshop_loop_shop_per_page()
		{
			$kuteshop_woo_products_perpage = apply_filters( 'theme_get_option', 'product_per_page', '12' );
			if ( isset( $_SESSION['kuteshop_woo_products_perpage'] ) ) {
				$kuteshop_woo_products_perpage = $_SESSION['kuteshop_woo_products_perpage'];
			}

			return $kuteshop_woo_products_perpage;
		}

		function kuteshop_woof_products_query( $wr )
		{
			$kuteshop_woo_products_perpage = apply_filters( 'theme_get_option', 'product_per_page', '12' );
			if ( isset( $_SESSION['kuteshop_woo_products_perpage'] ) ) {
				$kuteshop_woo_products_perpage = $_SESSION['kuteshop_woo_products_perpage'];
			}
			$wr['posts_per_page'] = $kuteshop_woo_products_perpage;

			return $wr;
		}

		function product_per_page_request()
		{
			if ( isset( $_POST['onchange_action_form'] ) ) {
				$_SESSION['kuteshop_woo_products_perpage'] = $_POST["product_per_page_filter"];
				wp_redirect( $_POST['onchange_action_form'] );
				exit();
			}
		}

		function kuteshop_product_per_page_tmp()
		{
			$perpage = apply_filters( 'theme_get_option', 'product_per_page', '12' );
			if ( isset( $_SESSION['kuteshop_woo_products_perpage'] ) ) {
				$perpage = $_SESSION['kuteshop_woo_products_perpage'];
			}
			$current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$products    = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => -1 ) );
			$i           = 0;
			?>
            <form class="per-page-form" method="POST" action="">
                <label>
                    <select name="product_per_page_filter" class="option-perpage" onchange="this.form.submit()">
                        <option value="5" <?php if ( $perpage == 5 ) {
							echo 'selected';
							$i++;
						} ?>><?php echo esc_html__( 'Show 05', 'kuteshop' ); ?>
                        </option>
                        <option value="10" <?php if ( $perpage == 10 ) {
							echo 'selected';
							$i++;
						} ?>><?php echo esc_html__( 'Show 10', 'kuteshop' ); ?>
                        </option>
                        <option value="12" <?php if ( $perpage == 12 ) {
							echo 'selected';
							$i++;
						} ?>><?php echo esc_html__( 'Show 12', 'kuteshop' ); ?>
                        </option>
                        <option value="15" <?php if ( $perpage == 15 ) {
							echo 'selected';
							$i++;
						} ?>><?php echo esc_html__( 'Show 15', 'kuteshop' ); ?>
                        </option>
                        <option value="<?php echo esc_attr( $products->post_count ); ?>" <?php if ( $perpage == $products->post_count ) {
							echo 'selected';
							$i++;
						} ?>><?php echo esc_html__( 'Show All', 'kuteshop' ); ?>
                        </option>
                        <option value="" <?php if ( $i == 0 ) {
							echo 'selected';
						} ?>><?php echo esc_html__( 'Select value', 'kuteshop' ); ?>
                        </option>
                    </select>
                </label>
                <label>
                    <input type="hidden" name="onchange_action_form" value="<?php echo esc_attr( $current_url ); ?>">
                </label>
            </form>
			<?php
		}

		function kuteshop_carousel_products( $prefix, $data_args )
		{
			$classes                    = array();
			$kuteshop_woo_product_style = apply_filters( 'theme_get_option', 'kuteshop_shop_product_style', 1 );
			$classes[]                  = 'product-item style-' . $kuteshop_woo_product_style;
			$template_style             = 'style-' . $kuteshop_woo_product_style;
			$woo_ls_items               = apply_filters( 'theme_get_option', '' . $prefix . '_ls_items', 3 );
			$woo_lg_items               = apply_filters( 'theme_get_option', '' . $prefix . '_lg_items', 3 );
			$woo_md_items               = apply_filters( 'theme_get_option', '' . $prefix . '_md_items', 3 );
			$woo_sm_items               = apply_filters( 'theme_get_option', '' . $prefix . '_sm_items', 2 );
			$woo_xs_items               = apply_filters( 'theme_get_option', '' . $prefix . '_xs_items', 1 );
			$woo_ts_items               = apply_filters( 'theme_get_option', '' . $prefix . '_ts_items', 1 );
			$atts                       = array(
				'owl_loop'     => 'false',
				'owl_ts_items' => $woo_ts_items,
				'owl_xs_items' => $woo_xs_items,
				'owl_sm_items' => $woo_sm_items,
				'owl_md_items' => $woo_md_items,
				'owl_lg_items' => $woo_lg_items,
				'owl_ls_items' => $woo_ls_items,
			);
			$owl_settings               = apply_filters( 'generate_carousel_data_attributes', 'owl_', $atts );
			$title                      = apply_filters( 'theme_get_option', '' . $prefix . '_products_title', 'You may be interested in...' );
			if ( $data_args ) : ?>
                <div class="products product-grid related-product">
                    <h2 class="product-grid-title"><?php echo esc_html( $title ); ?></h2>
                    <div class="owl-slick owl-products equal-container better-height" <?php echo esc_attr( $owl_settings ); ?>>
						<?php foreach ( $data_args as $value ) : ?>
                            <div <?php post_class( $classes ) ?>>
								<?php
								$post_object = get_post( $value->get_id() );
								setup_postdata( $GLOBALS['post'] =& $post_object );
								get_template_part( 'woo-templates/product-styles/content-product', $template_style ); ?>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>
			<?php endif;
			wp_reset_postdata();
		}

		function kuteshop_cross_sell_products( $limit = 2, $columns = 2, $orderby = 'rand', $order = 'desc' )
		{
			if ( is_checkout() ) {
				return;
			}
			$cross_sells                 = array_filter( array_map( 'wc_get_product', WC()->cart->get_cross_sells() ), 'wc_products_array_filter_visible' );
			$woocommerce_loop['name']    = 'cross-sells';
			$woocommerce_loop['columns'] = apply_filters( 'woocommerce_cross_sells_columns', $columns );
			// Handle orderby and limit results.
			$orderby     = apply_filters( 'woocommerce_cross_sells_orderby', $orderby );
			$cross_sells = wc_products_array_orderby( $cross_sells, $orderby, $order );
			$limit       = apply_filters( 'woocommerce_cross_sells_total', $limit );
			$cross_sells = $limit > 0 ? array_slice( $cross_sells, 0, $limit ) : $cross_sells;
			self::kuteshop_carousel_products( 'kuteshop_woo_crosssell', $cross_sells );
		}

		function kuteshop_related_products()
		{
			global $product;
			$defaults                    = array(
				'posts_per_page' => 6,
				'columns'        => 6,
				'orderby'        => 'rand',
				'order'          => 'desc',
			);
			$args                        = wp_parse_args( $defaults );
			$args['related_products']    = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product->get_id(), $args['posts_per_page'], $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );
			$args['related_products']    = wc_products_array_orderby( $args['related_products'], $args['orderby'], $args['order'] );
			$woocommerce_loop['name']    = 'related';
			$woocommerce_loop['columns'] = apply_filters( 'woocommerce_related_products_columns', $args['columns'] );
			$related_products            = $args['related_products'];
			self::kuteshop_carousel_products( 'kuteshop_woo_related', $related_products );
		}

		function kuteshop_upsell_display( $orderby = 'rand', $order = 'desc', $limit = '-1', $columns = 4 )
		{
			global $product;
			$args                        = array(
				'posts_per_page' => 4,
				'orderby'        => 'rand',
				'columns'        => 4,
			);
			$woocommerce_loop['name']    = 'up-sells';
			$woocommerce_loop['columns'] = apply_filters( 'woocommerce_upsells_columns', isset( $args['columns'] ) ? $args['columns'] : $columns );
			$orderby                     = apply_filters( 'woocommerce_upsells_orderby', isset( $args['orderby'] ) ? $args['orderby'] : $orderby );
			$limit                       = apply_filters( 'woocommerce_upsells_total', isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : $limit );
			// Get visible upsells then sort them at random, then limit result set.
			$upsells = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $product->get_upsell_ids() ), 'wc_products_array_filter_visible' ), $orderby, $order );
			$upsells = $limit > 0 ? array_slice( $upsells, 0, $limit ) : $upsells;
			self::kuteshop_carousel_products( 'kuteshop_woo_upsell', $upsells );
		}

		function kuteshop_custom_ratting_single_product()
		{
			global $product;
			if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
				return;
			}
			$rating_count = $product->get_rating_count();
			$average      = $product->get_average_rating();
			if ( $rating_count > 0 ) : ?>
                <div class="woocommerce-product-rating">
                <span class="star-rating">
                    <span style="width:<?php echo( ( $average / 5 ) * 100 ); ?>%">
                        <?php printf(
							__( '%1$s out of %2$s', 'kuteshop' ),
							'<strong class="rating">' . esc_html( $average ) . '</strong>',
							'<span>5</span>'
						); ?>
                    </span>
                </span>
                    <span>
                    <?php printf(
						_n( 'based on %s rating', 'Based on %s ratings', $rating_count, 'kuteshop' ),
						'<span class="rating">' . esc_html( $rating_count ) . '</span>'
					); ?>
                </span>
					<?php if ( comments_open() ) : ?>
                        <a href="#reviews" class="woocommerce-review-link" rel="nofollow">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
							<?php echo esc_html__( 'write a preview', 'kuteshop' ) ?>
                        </a>
					<?php endif ?>
                </div>
			<?php endif;
		}

		function kuteshop_custom_pagination()
		{
			global $wp_query;
			if ( $wp_query->max_num_pages > 1 ) {
				?>
                <nav class="woocommerce-pagination pagination">
					<?php
					echo paginate_links( apply_filters( 'woocommerce_pagination_args', array(
								'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
								'format'    => '',
								'add_args'  => false,
								'current'   => max( 1, get_query_var( 'paged' ) ),
								'total'     => $wp_query->max_num_pages,
								'prev_text' => esc_html__( 'Previous', 'kuteshop' ),
								'next_text' => esc_html__( 'Next', 'kuteshop' ),
								'type'      => 'plain',
								'end_size'  => 3,
								'mid_size'  => 3,
							)
						)
					);
					?>
                </nav>
				<?php
			}
		}

		function kuteshop_template_loop_product_title()
		{
			$title_class = array( 'product-name product_title' );
			?>
            <h3 class="<?php echo esc_attr( implode( ' ', $title_class ) ); ?>">
                <a href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a>
            </h3>
			<?php
		}

		function kuteshop_template_loop_product_thumbnail()
		{
			global $product;
			// GET SIZE IMAGE SETTING
			$w    = 250;
			$h    = 305;
			$crop = true;
			$size = wc_get_image_size( 'shop_catalog' );
			if ( $size ) {
				$w = $size['width'];
				$h = $size['height'];
				if ( !$size['crop'] ) {
					$crop = false;
				}
			}
			$w          = apply_filters( 'kuteshop_shop_pruduct_thumb_width', $w );
			$h          = apply_filters( 'kuteshop_shop_pruduct_thumb_height', $h );
			$lazy_check = apply_filters( 'theme_get_option', 'kuteshop_theme_lazy_load' ) == 1 ? true : false;
			if ( is_shop() || is_product() || is_cart() ) {
				$w = 300;
				$h = 356;
			}
			?>
            <a class="thumb-link" href="<?php the_permalink(); ?>">
				<?php
				$image_thumb = apply_filters( 'theme_resize_image', get_post_thumbnail_id( $product->get_id() ), $w, $h, $crop, $lazy_check );
				echo htmlspecialchars_decode( $image_thumb['img'] );
				?>
            </a>
			<?php
		}

		function kuteshop_group_flash()
		{
			global $post, $product;
			$postdate      = get_the_time( 'Y-m-d' );            // Post date
			$postdatestamp = strtotime( $postdate );            // Timestamped post date
			$newness       = apply_filters( 'theme_get_option', 'product_newness', 7 );    // Newness in days as defined by option
			?>
            <div class="flash">
				<?php woocommerce_show_product_loop_sale_flash();
				if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) :
					echo apply_filters( 'woocommerce_new_flash', '<span class="onnew"><span class="text">' . esc_html__( 'New', 'kuteshop' ) . '</span></span>', $post, $product );
				endif; ?>
            </div>
			<?php
		}

		function kuteshop_custom_sale_flash( $text )
		{
			$percent = self::kuteshop_get_percent_discount();
			if ( $percent != '' )
				return '<span class="onsale">' . $percent . '</span>';

			return '';
		}

		function kuteshop_get_percent_discount()
		{
			global $product;
			$percent = '';
			if ( $product->is_on_sale() ) {
				if ( $product->is_type( 'variable' ) ) {
					$available_variations = $product->get_available_variations();
					$maximumper           = 0;
					$minimumper           = 0;
					$percentage           = 0;
					for ( $i = 0; $i < count( $available_variations ); ++$i ) {
						$variation_id      = $available_variations[$i]['variation_id'];
						$variable_product1 = new WC_Product_Variation( $variation_id );
						$regular_price     = $variable_product1->get_regular_price();
						$sales_price       = $variable_product1->get_sale_price();
						if ( $regular_price > 0 && $sales_price > 0 ) {
							$percentage = round( ( ( ( $regular_price - $sales_price ) / $regular_price ) * 100 ), 0 );
						}
						if ( $minimumper == 0 ) {
							$minimumper = $percentage;
						}
						if ( $percentage > $maximumper ) {
							$maximumper = $percentage;
						}
						if ( $percentage < $minimumper ) {
							$minimumper = $percentage;
						}
					}
					if ( $minimumper == $maximumper ) {
						$percent .= '-' . $minimumper . '%';
					} else {
						$percent .= '-(' . $minimumper . '-' . $maximumper . ')%';
					}
				} else {
					if ( $product->get_regular_price() > 0 && $product->get_sale_price() > 0 ) {
						$percentage = round( ( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 ), 0 );
						$percent    .= '-' . $percentage . '%';
					}
				}
			}

			return $percent;
		}

		function kuteshop_function_shop_loop_item_countdown()
		{
			global $product;
			$date = self::kuteshop_get_max_date_sale( $product->get_id() );
			if ( $date > 0 ) {
				?>
                <div class="kuteshop-countdown"
                     data-datetime="<?php echo date( 'm/j/Y', $date ); ?>">
                </div>
				<?php
			}
		}

		function kuteshop_get_max_date_sale( $product_id )
		{
			$date_now = current_time( 'timestamp', 0 );
			// Get variations
			$args          = array(
				'post_type'   => 'product_variation',
				'post_status' => array( 'private', 'publish' ),
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'asc',
				'post_parent' => $product_id,
			);
			$variations    = get_posts( $args );
			$variation_ids = array();
			if ( $variations ) {
				foreach ( $variations as $variation ) {
					$variation_ids[] = $variation->ID;
				}
			}
			$sale_price_dates_to = false;
			if ( !empty( $variation_ids ) ) {
				global $wpdb;
				$sale_price_dates_to = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_sale_price_dates_to' and post_id IN(" . join( ',', $variation_ids ) . ") ORDER BY meta_value DESC LIMIT 1" );
				if ( $sale_price_dates_to != '' ) {
					return $sale_price_dates_to;
				}
			}
			if ( !$sale_price_dates_to ) {
				$sale_price_dates_to   = get_post_meta( $product_id, '_sale_price_dates_to', true );
				$sale_price_dates_from = get_post_meta( $product_id, '_sale_price_dates_from', true );
				if ( $sale_price_dates_to == '' || $date_now < $sale_price_dates_from ) {
					$sale_price_dates_to = '0';
				}
			}

			return $sale_price_dates_to;
		}

		function header_cart_link()
		{
			global $woocommerce;
			?>
            <div class="shopcart-dropdown block-cart-link" data-kuteshop="kuteshop-dropdown">
                <a class="link-dropdown style1" href="<?php echo wc_get_cart_url(); ?>">
                    <span class="text"><?php echo esc_html__( 'SHOPPING CART', 'kuteshop' ); ?></span>
                    <span class="item"><?php printf( esc_html__( '%1$s item(s) - ', 'kuteshop' ), WC()->cart->cart_contents_count ); ?></span>
                    <span class="total"><?php echo $woocommerce->cart->get_cart_subtotal(); ?></span>
                    <span class="flaticon-cart01 icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                        </span>
                </a>
                <a class="link-dropdown style2" href="<?php echo wc_get_cart_url(); ?>">
                    <span class="flaticon-cart02 icon">
                        <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                    </span>
                </a>
                <a class="link-dropdown style3" href="<?php echo wc_get_cart_url(); ?>">
                        <span class="flaticon-cart03 icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                        </span>
                    <span class="text"><?php echo esc_html__( 'Cart', 'kuteshop' ); ?></span>
                </a>
                <a class="link-dropdown style4" href="<?php echo wc_get_cart_url(); ?>">
                        <span class="flaticon-cart05 icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                        </span>
                    <span class="text"><?php echo esc_html__( 'My Cart', 'kuteshop' ); ?></span>
                </a>
                <a class="link-dropdown style7" href="<?php echo wc_get_cart_url(); ?>">
                        <span class="flaticon-cart02 icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                        </span>
                </a>
                <a class="link-dropdown style9" href="<?php echo wc_get_cart_url(); ?>">
                        <span class="flaticon-cart06 icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                        </span>
                </a>
                <a class="link-dropdown style11" href="<?php echo wc_get_cart_url(); ?>">
                        <span class="flaticon-cart06 icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                        </span>
                    <span class="total"><?php echo $woocommerce->cart->get_cart_subtotal(); ?></span>
                </a>
                <a class="link-dropdown style12" href="<?php echo wc_get_cart_url(); ?>">
                        <span class="flaticon-cart06 icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                        </span>
                    <span class="text"><?php echo esc_html__( 'CART:', 'kuteshop' ); ?></span>
                    <span class="total"><?php echo $woocommerce->cart->get_cart_subtotal(); ?></span>
                </a>
                <a class="link-dropdown style14" href="<?php echo wc_get_cart_url(); ?>">
                    <span class="count"><?php echo WC()->cart->cart_contents_count ?></span>
                    <span class="text"><?php echo esc_html__( 'Cart', 'kuteshop' ); ?></span>
                </a>
            </div>
			<?php
		}

		function kuteshop_header_mini_cart()
		{
			?>
            <div class="block-minicart kuteshop-mini-cart kuteshop-dropdown">
				<?php self::header_cart_link(); ?>
                <div class="shopcart-description">
					<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
                </div>
            </div>
			<?php
		}

		function kuteshop_cart_link_fragment( $fragments )
		{
			ob_start();
			self::header_cart_link();
			$fragments['div.block-cart-link'] = ob_get_clean();

			return $fragments;
		}

		function kuteshop_remove_cart_item_via_ajax()
		{
			$response      = array(
				'message'        => '',
				'fragments'      => '',
				'cart_hash'      => '',
				'mini_cart_html' => '',
				'err'            => 'no',
			);
			$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : '';
			$nonce         = isset( $_POST['nonce'] ) ? trim( $_POST['nonce'] ) : '';
			if ( $cart_item_key == '' || $nonce == '' ) {
				$response['err'] = 'yes';
				wp_send_json( $response );
			}
			if ( ( wp_verify_nonce( $nonce, 'woocommerce-cart' ) ) ) {
				if ( $cart_item = WC()->cart->get_cart_item( $cart_item_key ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
					$product            = wc_get_product( $cart_item['product_id'] );
					$item_removed_title = apply_filters( 'woocommerce_cart_item_removed_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'kuteshop' ), $product->get_name() ) : esc_html__( 'Item', 'kuteshop' ), $cart_item );
					// Don't show undo link if removed item is out of stock.
					if ( $product->is_in_stock() && $product->has_enough_stock( $cart_item['quantity'] ) ) {
						$removed_notice = sprintf( esc_html__( '%s removed.', 'kuteshop' ), $item_removed_title );
						$removed_notice .= ' <a href="' . esc_url( WC()->cart->get_undo_url( $cart_item_key ) ) . '">' . esc_html__( 'Undo?', 'kuteshop' ) . '</a>';
					} else {
						$removed_notice = sprintf( esc_html__( '%s removed.', 'kuteshop' ), $item_removed_title );
					}
					wc_add_notice( $removed_notice );
				}
			} else {
				$response['message'] = esc_html__( 'Security check error!', 'kuteshop' );
				$response['err']     = 'yes';
				wp_send_json( $response );
			}
			ob_start();
			self::kuteshop_header_mini_cart();
			$minicart                   = ob_get_clean();
			$response['cart_hash']      = apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() );
			$response['mini_cart_html'] = $minicart;
			wp_send_json( $response );
			die();
		}
	}
}
/**
 * Unique access to instance of Woo_Functions class
 * @return \Kuteshop_Woo_Functions
 * @since 1.0.0
 */
function Kuteshop_Woo_Functions()
{
	return Kuteshop_Woo_Functions::get_instance();
}

Kuteshop_Woo_Functions();