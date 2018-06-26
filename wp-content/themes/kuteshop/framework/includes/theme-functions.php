<?php
if ( !class_exists( 'Theme_Functions' ) ) {
	class Theme_Functions
	{
		public function __construct()
		{
			add_action( 'kuteshop_get_header', array( $this, 'kuteshop_get_header' ) );
			add_action( 'kuteshop_get_footer', array( $this, 'kuteshop_get_footer' ) );
			add_action( 'kuteshop_get_logo', array( $this, 'kuteshop_get_logo' ) );
			add_action( 'kuteshop_post_thumbnail', array( $this, 'kuteshop_post_thumbnail' ) );
			add_action( 'kuteshop_paging_nav', array( $this, 'kuteshop_paging_nav' ) );
			add_action( 'kuteshop_header_control', array( $this, 'kuteshop_header_control' ) );
			add_action( 'kuteshop_header_social', array( $this, 'kuteshop_header_social' ) );
			add_action( 'kuteshop_search_form', array( $this, 'kuteshop_search_form' ) );
			add_action( 'kuteshop_user_link', array( $this, 'kuteshop_user_link' ) );
			add_action( 'kuteshop_page_banner', array( $this, 'kuteshop_page_banner' ) );
			add_action( 'kuteshop_header_sticky', array( $this, 'kuteshop_header_sticky' ) );
			add_action( 'kuteshop_header_vertical', array( $this, 'kuteshop_header_vertical' ) );
			add_action( 'kuteshop_time_ago', array( $this, 'kuteshop_time_ago' ) );
			add_filter( 'wcml_multi_currency_ajax_actions', array( $this, 'kuteshop_add_action_to_multi_currency_ajax' ), 10, 1 );
			/* AJAX */
			add_action( 'wp_ajax_kuteshop_ajax_tabs', array( $this, 'kuteshop_ajax_tabs' ) );
			add_action( 'wp_ajax_nopriv_kuteshop_ajax_tabs', array( $this, 'kuteshop_ajax_tabs' ) );
			add_action( 'wp_ajax_kuteshop_ajax_tabs_filter', array( $this, 'kuteshop_ajax_tabs_filter' ) );
			add_action( 'wp_ajax_nopriv_kuteshop_ajax_tabs_filter', array( $this, 'kuteshop_ajax_tabs_filter' ) );
		}

		function kuteshop_get_logo()
		{
			$logo_url  = get_theme_file_uri( '/assets/images/logo.png' );
			$logo      = apply_filters( 'theme_get_option', 'kuteshop_logo' );
			$data_meta = apply_filters( 'theme_get_meta', '_custom_metabox_theme_options', 'metabox_kuteshop_logo' );
			$logo      = $data_meta != '' ? $data_meta : $logo;
			if ( $logo != '' ) {
				$logo_url = wp_get_attachment_image_url( $logo, 'full' );
			}
			$html = '<a href="' . esc_url( home_url( '/' ) ) . '"><img alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" src="' . esc_url( $logo_url ) . '" class="_rw" /></a>';
			echo apply_filters( 'kuteshop_site_logo', $html );
		}

		function kuteshop_time_ago( $type = 'post' )
		{
			$d = 'comment' == $type ? 'get_comment_time' : 'get_post_time';

			echo human_time_diff( $d( 'U' ), current_time( 'timestamp' ) ) . " " . esc_html__( 'ago', 'kuteshop' );
		}

		function kuteshop_header_vertical()
		{
			/* MAIN THEME OPTIONS */
			$enable_vertical_menu  = apply_filters( 'theme_get_option', 'enable_vertical_menu' );
			$block_vertical_menu   = apply_filters( 'theme_get_option', 'block_vertical_menu' );
			$vertical_item_visible = apply_filters( 'theme_get_option', 'vertical_item_visible', 10 );
			/* META BOX THEME OPTIONS */
			$enable_theme_options = apply_filters( 'theme_get_option', 'enable_theme_options' );
			$meta_data            = get_post_meta( get_the_ID(), '_custom_metabox_theme_options', true );
			if ( !empty( $meta_data ) && $enable_theme_options == 1 ) {
				$enable_vertical_menu  = isset( $meta_data['metabox_enable_vertical_menu'] ) ? $meta_data['metabox_enable_vertical_menu'] : '';
				$block_vertical_menu   = isset( $meta_data['metabox_block_vertical_menu'] ) ? $meta_data['metabox_block_vertical_menu'] : '';
				$vertical_item_visible = isset( $meta_data['metabox_vertical_item_visible'] ) ? $meta_data['metabox_vertical_item_visible'] : '';
			}
			if ( $enable_vertical_menu == 1 ):
				$block_vertical_class = array( 'vertical-wapper block-nav-category' );
				/* MAIN THEME OPTIONS */
				$vertical_menu_title             = apply_filters( 'theme_get_option', 'vertical_menu_title', 'Shop By Category' );
				$vertical_menu_button_all_text   = apply_filters( 'theme_get_option', 'vertical_menu_button_all_text', 'All categoryes' );
				$vertical_menu_button_close_text = apply_filters( 'theme_get_option', 'vertical_menu_button_close_text', 'Close' );
				/* META BOX THEME OPTIONS */
				if ( !empty( $meta_data ) && $enable_theme_options == 1 ) {
					$vertical_menu_title             = isset( $meta_data['metabox_vertical_menu_title'] ) ? $meta_data['metabox_vertical_menu_title'] : '';
					$vertical_menu_button_all_text   = isset( $meta_data['metabox_vertical_menu_button_all_text'] ) ? $meta_data['metabox_vertical_menu_button_all_text'] : '';
					$vertical_menu_button_close_text = isset( $meta_data['metabox_vertical_menu_button_close_text'] ) ? $meta_data['metabox_vertical_menu_button_close_text'] : '';
				}
				if ( $enable_vertical_menu == 1 ) {
					$block_vertical_class[] = 'has-vertical-menu';
				}
				if ( $block_vertical_menu == 1 ) {
					$block_vertical_class[] = 'alway-open';
				}
				?>
                <!-- block category -->
                <div data-items="<?php echo esc_attr( $vertical_item_visible ); ?>"
                     class="<?php echo esc_attr( implode( ' ', $block_vertical_class ) ); ?>">
                    <div class="block-title">
                        <span class="fa fa-bars icon-title before" aria-hidden="true"></span>
                        <span class="text-title"><?php echo esc_html( $vertical_menu_title ); ?></span>
                        <span class="fa fa-bars icon-title after" aria-hidden="true"></span>
                    </div>
                    <div class="block-content verticalmenu-content">
						<?php
						wp_nav_menu( array(
								'menu'            => 'vertical_menu',
								'theme_location'  => 'vertical_menu',
								'depth'           => 4,
								'container'       => '',
								'container_class' => '',
								'container_id'    => '',
								'menu_class'      => 'kuteshop-nav vertical-menu',
								'fallback_cb'     => 'Kuteshop_navwalker::fallback',
								'walker'          => new Kuteshop_navwalker(),
							)
						);
						?>
                        <div class="view-all-category">
                            <a href="javascript:void(0);"
                               data-closetext="<?php echo esc_attr( $vertical_menu_button_close_text ); ?>"
                               data-alltext="<?php echo esc_attr( $vertical_menu_button_all_text ) ?>"
                               class="btn-view-all open-cate"><?php echo esc_html( $vertical_menu_button_all_text ) ?></a>
                        </div>
                    </div>
                </div><!-- block category -->
			<?php endif;
		}

		function kuteshop_header_sticky()
		{
			$enable_sticky_menu = apply_filters( 'theme_get_option', 'kuteshop_enable_sticky_menu' );
			if ( $enable_sticky_menu == 1 ) :
				?>
                <div id="header-sticky-menu" class="header-sticky-menu">
                    <div class="container">
                        <div class="header-nav-inner">
							<?php self::kuteshop_header_vertical(); ?>
                            <div class="box-header-nav main-menu-wapper">
								<?php
								wp_nav_menu( array(
										'menu'            => 'primary',
										'theme_location'  => 'primary',
										'depth'           => 3,
										'container'       => '',
										'container_class' => '',
										'container_id'    => '',
										'menu_class'      => 'kuteshop-nav main-menu',
										'fallback_cb'     => 'Kuteshop_navwalker::fallback',
										'walker'          => new Kuteshop_navwalker(),
									)
								);
								?>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endif;
		}

		function kuteshop_page_banner()
		{
			/* Data MetaBox */
			$data_meta                       = get_post_meta( get_the_ID(), '_custom_metabox_theme_options', true );
			$kuteshop_metabox_enable_banner  = isset( $data_meta['kuteshop_metabox_enable_banner'] ) ? $data_meta['kuteshop_metabox_enable_banner'] : 0;
			$kuteshop_page_header_background = isset( $data_meta['bg_banner_page'] ) ? $data_meta['bg_banner_page'] : '';
			$kuteshop_page_heading_height    = isset( $data_meta['height_banner'] ) ? $data_meta['height_banner'] : '';
			$kuteshop_page_margin_top        = isset( $data_meta['page_margin_top'] ) ? $data_meta['page_margin_top'] : '';
			$kuteshop_page_margin_bottom     = isset( $data_meta['page_margin_bottom'] ) ? $data_meta['page_margin_bottom'] : '';
			$css                             = '';
			if ( $kuteshop_metabox_enable_banner != 1 ) {
				return;
			}
			if ( $kuteshop_page_header_background != "" ) {
				$css .= 'background-image:  url("' . esc_url( $kuteshop_page_header_background['image'] ) . '");';
				$css .= 'background-repeat: ' . esc_attr( $kuteshop_page_header_background['repeat'] ) . ';';
				$css .= 'background-position:   ' . esc_attr( $kuteshop_page_header_background['position'] ) . ';';
				$css .= 'background-attachment: ' . esc_attr( $kuteshop_page_header_background['attachment'] ) . ';';
				$css .= 'background-size:   ' . esc_attr( $kuteshop_page_header_background['size'] ) . ';';
				$css .= 'background-color:  ' . esc_attr( $kuteshop_page_header_background['color'] ) . ';';
			}
			if ( $kuteshop_page_heading_height != "" ) {
				$css .= 'min-height:' . $kuteshop_page_heading_height . 'px;';
			}
			if ( $kuteshop_page_margin_top != "" ) {
				$css .= 'margin-top:' . $kuteshop_page_margin_top . 'px;';
			}
			if ( $kuteshop_page_margin_bottom != "" ) {
				$css .= 'margin-bottom:' . $kuteshop_page_margin_bottom . 'px;';
			}
			?>
            <!-- Banner page -->
            <div class="inner-page-banner" style='<?php echo esc_attr( $css ); ?>'>
                <div class="container">
                    <h1 class="page-title"><?php single_post_title(); ?></h1>
					<?php
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
					?>
                </div>
            </div>
            <!-- /Banner page -->
			<?php
		}

		function kuteshop_user_link()
		{
			$myaccount_link = wp_login_url();
			$currentUser    = wp_get_current_user();
			if ( class_exists( 'WooCommerce' ) ) {
				$myaccount_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
			}
			?>
            <div class="block-userlink kuteshop-dropdown">
				<?php if ( is_user_logged_in() ): ?>
                    <a data-kuteshop="kuteshop-dropdown" class="woo-wishlist-link"
                       href="<?php echo esc_url( $myaccount_link ); ?>">
                        <span class="flaticon-user icon"></span>
                        <span class="text"><?php echo esc_html( $currentUser->display_name ); ?></span>
                    </a>
					<?php if ( function_exists( 'wc_get_account_menu_items' ) ): ?>
                        <ul class="submenu">
							<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
                                <li class="menu-item <?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
                                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
                                </li>
							<?php endforeach; ?>
                        </ul>
					<?php else: ?>
                        <ul class="submenu">
                            <li class="menu-item">
                                <a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php esc_html_e( 'Logout', 'kuteshop' ); ?></a>
                            </li>
                        </ul>
					<?php endif;
				else: ?>
                    <a class="woo-wishlist-link" href="<?php echo esc_url( $myaccount_link ); ?>">
                        <span class="flaticon-user icon"></span>
                        <span class="text"><?php echo esc_html__( 'Login', 'kuteshop' ); ?></span>
                    </a>
				<?php endif; ?>
            </div>
			<?php
		}

		function kuteshop_search_form()
		{
			$header_style = apply_filters( 'theme_get_option', 'kuteshop_used_header' );
			$data_meta    = apply_filters( 'theme_get_meta', '_custom_metabox_theme_options', 'kuteshop_metabox_used_header' );
			$header_style = $data_meta != '' ? $data_meta : $header_style;
			$selected     = '';
			if ( isset( $_GET['product_cat'] ) && $_GET['product_cat'] ) {
				$selected = $_GET['product_cat'];
			}
			$args               = array(
				'show_option_none'  => esc_html__( 'All Categories', 'kuteshop' ),
				'taxonomy'          => 'product_cat',
				'class'             => 'category-search-option',
				'hide_empty'        => 1,
				'orderby'           => 'name',
				'order'             => "ASC",
				'tab_index'         => true,
				'hierarchical'      => true,
				'id'                => rand(),
				'name'              => 'product_cat',
				'value_field'       => 'slug',
				'selected'          => $selected,
				'option_none_value' => '0',
			);
			$block_search_html  = '';
			$block_search_class = '';
			$form_search_class  = '';
			if (
				$header_style == 'style-14' ||
				$header_style == 'style-10' ||
				$header_style == 'style-09' ||
				$header_style == 'style-08'
			) {
				$block_search_class = 'kuteshop-dropdown';
				$form_search_class  = 'submenu';
				$block_search_html  = '<a href="#" class="icon" data-kuteshop="kuteshop-dropdown"><span class="fa fa-search" aria-hidden="true"></span></a>';
			}
			?>
            <div class="block-search <?php echo esc_attr( $block_search_class ); ?>">
				<?php echo htmlspecialchars_decode( $block_search_html ); ?>
                <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ) ?>"
                      class="form-search <?php echo esc_attr( $form_search_class ); ?>">
					<?php if (
						$header_style == 'style-02' ||
						$header_style == 'style-06' ||
						$header_style == 'style-12' ||
						$header_style == 'style-13'
					) : ?>
                        <div class="form-content">
                            <div class="inner">
                                <input type="text" class="input" name="s"
                                       value="<?php echo esc_attr( get_search_query() ); ?>"
                                       placeholder="<?php echo esc_html__( 'I&#39;m searching for...', 'kuteshop' ); ?>">
                            </div>
                        </div>
						<?php if ( class_exists( 'WooCommerce' ) ): ?>
                            <input type="hidden" name="post_type" value="product"/>
                            <input type="hidden" name="taxonomy" value="product_cat">
                            <div class="category">
								<?php wp_dropdown_categories( $args ); ?>
                            </div>
						<?php else: ?>
                            <input type="hidden" name="post_type" value="post"/>
						<?php endif; ?>
                        <button type="submit" class="btn-submit">
                            <span class="fa fa-search" aria-hidden="true"></span>
                        </button>
					<?php else : ?>
						<?php if ( class_exists( 'WooCommerce' ) ): ?>
                            <input type="hidden" name="post_type" value="product"/>
                            <input type="hidden" name="taxonomy" value="product_cat">
							<?php if ( $header_style != 'style-05' ) : ?>
                                <div class="category">
									<?php wp_dropdown_categories( $args ); ?>
                                </div>
							<?php endif; ?>
						<?php else: ?>
                            <input type="hidden" name="post_type" value="post"/>
						<?php endif; ?>
                        <div class="form-content">
                            <div class="inner">
                                <input type="text" class="input" name="s"
                                       value="<?php echo esc_attr( get_search_query() ); ?>"
                                       placeholder="<?php echo esc_html__( 'I&#39;m searching for...', 'kuteshop' ); ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">
                            <span class="fa fa-search" aria-hidden="true"></span>
                        </button>
					<?php endif; ?>
                </form><!-- block search -->
            </div>
			<?php
		}

		function kuteshop_header_social()
		{
			$all_socials = apply_filters( 'theme_get_option', 'user_all_social' );
			$socials     = apply_filters( 'theme_get_option', 'header_social' );
			if ( !empty( $socials ) ) :
				?>
                <div class="header-social">
                    <ul class="socials">
						<?php foreach ( $socials as $social ) :
							$array_social = $all_socials[$social]; ?>
                            <li class="social-item">
                                <a href="<?php echo esc_url( $array_social['link_social'] ) ?>"
                                   target="_blank">
                                    <i class="<?php echo esc_attr( $array_social['icon_social'] ); ?>"></i>
                                </a>
                            </li>
						<?php endforeach; ?>
                    </ul>
                </div>
			<?php
			endif;
		}

		function kuteshop_header_control()
		{
			$current_language = '';
			$list_language    = '';
			$menu_language    = '';
			$languages        = apply_filters( 'wpml_active_languages', NULL, 'skip_missing=0' );
			if ( !empty( $languages ) ) {
				foreach ( $languages as $l ) {
					if ( !$l['active'] ) {
						$list_language .= '
						<li class="menu-item">
                            <a href="' . esc_url( $l['url'] ) . '">
                                <img src="' . esc_url( $l['country_flag_url'] ) . '" height="12"
                                     alt="' . esc_attr( $l['language_code'] ) . '" width="18"/>
								' . esc_html( $l['native_name'] ) . '
                            </a>
                        </li>';
					} else {
						$current_language = '
						<a href="' . esc_url( $l['url'] ) . '" data-kuteshop="kuteshop-dropdown">
                            <img src="' . esc_url( $l['country_flag_url'] ) . '" height="12"
                                 alt="' . esc_attr( $l['language_code'] ) . '" width="18"/>
							' . esc_html( $l['native_name'] ) . '
                        </a>
                        <span class="toggle-submenu"></span>';
					}
				}
				$menu_language = '
                 <div class="kuteshop-dropdown block-language">
                    ' . $current_language . '
                    <ul class="submenu">
                        ' . $list_language . '
                    </ul>
                </div>';
			}
			ob_start();
			do_action( 'wcml_currency_switcher', array( 'format' => '%code%', 'switcher_style' => 'wcml-dropdown' ) );
			echo htmlspecialchars_decode( $menu_language );
			$html = ob_get_clean();
			echo htmlspecialchars_decode( $html );
		}

		function kuteshop_share_button( $post_id )
		{
			$enable_share_post     = apply_filters( 'theme_get_option', 'enable_share_button' );
			$share_image_url       = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
			$share_link_url        = get_permalink( $post_id );
			$share_link_title      = get_the_title();
			$share_twitter_summary = get_the_excerpt();
			if ( $enable_share_post != 1 ) {
				return;
			} ?>
            <div class="kuteshop-share-socials">
                <a target="_blank" class="facebook"
                   href="https://www.facebook.com/sharer.php?s=100&amp;p%5Btitle%5D=<?php echo esc_html( $share_link_title ); ?>&amp;p%5Burl%5D=<?php echo urlencode( $share_link_url ); ?>"
                   title="<?php echo esc_html__( 'Facebook', 'kuteshop' ) ?>"
                   onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
                    <i class="fa fa-facebook"></i>
                </a>
                <a target="_blank" class="twitter"
                   href="https://twitter.com/share?url=<?php echo urlencode( $share_link_url ) ?>&amp;text=<?php echo esc_html( $share_twitter_summary ); ?>"
                   title="<?php echo esc_html__( 'Twitter', 'kuteshop' ) ?>"
                   onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
                    <i class="fa fa-twitter"></i>
                </a>
                <a target="_blank" class="googleplus"
                   href="https://plus.google.com/share?url=<?php echo urlencode( $share_link_url ) ?>&amp;title=<?php echo esc_html( $share_link_title ); ?>"
                   title="<?php echo esc_html__( 'Google+', 'kuteshop' ) ?>"
                   onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
                    <i class="fa fa-google-plus"></i>
                </a>
                <a target="_blank" class="pinterest"
                   href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode( $share_link_url ) ?>&amp;description=<?php echo esc_html( $share_twitter_summary ); ?>&amp;media=<?php echo urlencode( $share_image_url[0] ); ?>"
                   title="<?php echo esc_html__( 'Pinterest', 'kuteshop' ) ?>"
                   onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
                    <i class="fa fa-pinterest"></i>
                </a>
            </div>
			<?php
		}

		function kuteshop_paging_nav()
		{
			global $wp_query;
			$max = $wp_query->max_num_pages;
			// Don't print empty markup if there's only one page.
			if ( $max >= 2 ) {
				echo get_the_posts_pagination( array(
						'screen_reader_text' => '&nbsp;',
						'before_page_number' => '',
						'prev_text'          => esc_html__( 'Prev', 'kuteshop' ),
						'next_text'          => esc_html__( 'Next', 'kuteshop' ),
					)
				);
			}
		}

		function detected_shortcode( $id, $tab_id = null, $product_id = null )
		{
			$post              = get_post( $id );
			$content           = preg_replace( '/\s+/', ' ', $post->post_content );
			$shortcode_section = '';
			if ( $tab_id == null ) {
				$out = array();
				preg_match_all( '/\[kuteshop_products(.*?)\]/', $content, $matches );
				if ( $matches[0] && is_array( $matches[0] ) && count( $matches[0] ) > 0 ) {
					foreach ( $matches[0] as $key => $value ) {
						if ( shortcode_parse_atts( $matches[1][$key] )['products_custom_id'] == $product_id ) {
							$out['atts']    = shortcode_parse_atts( $matches[1][$key] );
							$out['content'] = $value;
						}
					}
				}
				$shortcode_section = $out;
			}
			if ( $product_id == null ) {
				preg_match_all( '/\[vc_tta_section(.*?)vc_tta_section\]/', $content, $matches );
				if ( $matches[0] && is_array( $matches[0] ) && count( $matches[0] ) > 0 ) {
					foreach ( $matches[0] as $key => $value ) {
						preg_match_all( '/tab_id="([^"]+)"/', $matches[0][$key], $matches_ids );
						foreach ( $matches_ids[1] as $matches_id ) {
							if ( $tab_id == $matches_id ) {
								$shortcode_section = $value;
							}
						}
					}
				}
			}

			return $shortcode_section;
		}

		function kuteshop_add_action_to_multi_currency_ajax( $ajax_actions )
		{
			$ajax_actions[] = 'kuteshop_ajax_tabs'; // Add a AJAX action to the array
			$ajax_actions[] = 'kuteshop_ajax_tabs_filter'; // Add a AJAX action to the array

			return $ajax_actions;
		}

		function kuteshop_ajax_tabs()
		{
			$response   = array(
				'html'    => '',
				'message' => '',
				'success' => 'no',
			);
			$section_id = isset( $_POST['section_id'] ) ? $_POST['section_id'] : '';
			$id         = isset( $_POST['id'] ) ? $_POST['id'] : '';
			$shortcode  = self::detected_shortcode( $id, $section_id, null );
			WPBMap::addAllMappedShortcodes();
			$response['html']    = wpb_js_remove_wpautop( $shortcode );
			$response['success'] = 'ok';
			wp_send_json( $response );
			die();
		}

		function kuteshop_ajax_tabs_filter()
		{
			$response             = array(
				'html'    => '',
				'message' => '',
				'success' => 'no',
			);
			$cat                  = isset( $_POST['cat'] ) ? $_POST['cat'] : '';
			$id                   = isset( $_POST['id'] ) ? $_POST['id'] : '';
			$check                = isset( $_POST['check'] ) ? $_POST['check'] : '';
			$product_id           = isset( $_POST['product_id'] ) ? $_POST['product_id'] : '';
			$list_style           = isset( $_POST['list_style'] ) ? $_POST['list_style'] : '';
			$shortcode_data       = self::detected_shortcode( $id, null, $product_id );
			$atts                 = $shortcode_data['atts'];
			$atts['taxonomy']     = $cat;
			$products             = apply_filters( 'getProducts', $atts );
			$product_item_class   = array( 'product-item', $atts['target'] );
			$product_item_class[] = 'style-' . $atts['product_style'];
			if (
				$atts['product_style'] == '2' ||
				$atts['product_style'] == '4' ||
				$atts['product_style'] == '8' ||
				$atts['product_style'] == '9' ||
				$atts['product_style'] == '10'
			) {
				$product_item_class[] = 'style-1';
			}
			$product_list_class = array();
			if ( $list_style == 'grid' ) {
				$product_list_class[] = 'product-list-grid row auto-clear equal-container better-height ';
				$product_item_class[] = $atts['boostrap_rows_space'];
				$product_item_class[] = 'col-bg-' . $atts['boostrap_bg_items'];
				$product_item_class[] = 'col-lg-' . $atts['boostrap_lg_items'];
				$product_item_class[] = 'col-md-' . $atts['boostrap_md_items'];
				$product_item_class[] = 'col-sm-' . $atts['boostrap_sm_items'];
				$product_item_class[] = 'col-xs-' . $atts['boostrap_xs_items'];
				$product_item_class[] = 'col-ts-' . $atts['boostrap_ts_items'];
			}
			ob_start();
			WPBMap::addAllMappedShortcodes();
			if ( $check == 1 ) :
				if ( $products->have_posts() ): ?>
					<?php while ( $products->have_posts() ) : $products->the_post(); ?>
                        <div <?php post_class( $product_item_class ); ?>>
							<?php get_template_part( 'woo-templates/product-styles/content-product-style', $atts['product_style'] ); ?>
                        </div>
					<?php endwhile; ?>
				<?php else: ?>
                    <p>
                        <strong><?php esc_html_e( 'No Product', 'kuteshop' ); ?></strong>
                    </p>
				<?php endif;
			else:
				echo do_shortcode( $shortcode_data['content'] );
			endif;
			$response['html']    = ob_get_clean();
			$response['success'] = 'ok';
			wp_send_json( $response );
			die();
		}

		function kuteshop_post_thumbnail()
		{
			$using_placeholder   = apply_filters( 'theme_get_option', 'using_placeholder' );
			$sidebar_blog_layout = apply_filters( 'theme_get_option', 'sidebar_blog_layout', 'left' );
			$kuteshop_blog_lazy  = apply_filters( 'theme_get_option', 'kuteshop_theme_lazy_load' );
			$lazy_check          = $kuteshop_blog_lazy == 1 ? true : false;
			if ( $using_placeholder != 1 && !has_post_thumbnail() ||
				$sidebar_blog_layout == 'full' && !has_post_thumbnail() ) {
				return;
			}
			if ( $sidebar_blog_layout == 'full' ) {
				$width  = 1170;
				$height = 610;
			} else {
				$width  = 770;
				$height = 454;
			} ?>
            <div class="post-thumb">
				<?php
				if ( is_single() ) {
					the_post_thumbnail( 'full' );
				} else {
					$image_thumb = apply_filters( 'theme_resize_image', get_post_thumbnail_id(), $width, $height, true, $lazy_check );
					echo '<a href="' . get_permalink() . '">';
					echo htmlspecialchars_decode( $image_thumb['img'] );
					echo '</a>';
				}
				?>
            </div>
			<?php
		}

		function kuteshop_get_header()
		{
			/* Data MetaBox */
			$kuteshop_used_header = apply_filters( 'theme_get_option', 'kuteshop_used_header', 'style-01' );
			$data_meta            = apply_filters( 'theme_get_meta', '_custom_metabox_theme_options', 'kuteshop_metabox_used_header' );
			$kuteshop_used_header = $data_meta != '' ? $data_meta : $kuteshop_used_header;
			get_template_part( 'templates/headers/header', $kuteshop_used_header );
		}

		function kuteshop_get_footer()
		{
			$footer_options        = apply_filters( 'theme_get_option', 'kuteshop_footer_options', 'default' );
			$data_meta             = apply_filters( 'theme_get_meta', '_custom_metabox_theme_options', 'kuteshop_metabox_footer_options' );
			$footer_options        = $data_meta != '' ? $data_meta : $footer_options;
			$meta_template_style   = get_post_meta( $footer_options, '_custom_footer_options', true );
			$footer_template_style = isset( $meta_template_style['kuteshop_footer_style'] ) ? $meta_template_style['kuteshop_footer_style'] : 'style-01';
			ob_start();
			$query = new WP_Query( array( 'p' => $footer_options, 'post_type' => 'footer', 'posts_per_page' => 1 ) );
			if ( $query->have_posts() ):
				while ( $query->have_posts() ): $query->the_post();
					get_template_part( 'templates/footers/footer', $footer_template_style );
				endwhile;
			endif;
			wp_reset_postdata();
			echo ob_get_clean();
		}
	}

	new Theme_Functions();
}