<?php
if ( !class_exists( 'Kuteshop_Shortcode_Products' ) ) {
	class Kuteshop_Shortcode_Products extends Kuteshop_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'products';
		/**
		 * Default $atts .
		 *
		 * @var  array
		 */
		public $default_atts = array();

		public static function generate_css( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'kuteshop_products', $atts ) : $atts;
			extract( $atts );
			$css = '';
			if ( $atts['slide_margin'] != '' && $atts['slide_margin'] != 0 ) {
				$css .= '
				.kuteshop-products.' . $atts['products_custom_id'] . ' .slick-list{ margin:0 -' . intval( $atts['slide_margin'] ) / 2 . 'px;} 
				.kuteshop-products.' . $atts['products_custom_id'] . ' .slick-list .slick-slide{ padding:0 ' . intval( $atts['slide_margin'] ) / 2 . 'px;}
				';
			} else {
				$css .= '
				.kuteshop-products.' . $atts['products_custom_id'] . ' .slick-list{ margin: 0;} 
				.kuteshop-products.' . $atts['products_custom_id'] . ' .slick-list .slick-slide{ padding: 0;}
				';
			}

			return $css;
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'kuteshop_products', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'kuteshop-products' );
			$css_class[] = $atts['el_class'];
			$css_class[] = $atts['products_custom_id'];
			$css_class[] = 'style-' . $atts['product_style'];
			if ( $atts['box_brand'] == true ) {
				$css_class[] = 'has-brand';
			}
			if ( function_exists( 'vc_shortcode_custom_css_class' ) ) {
				$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $atts['css'], ' ' ), '', $atts );
			}
			/* Product Size */
			if ( $atts['product_image_size'] ) {
				if ( $atts['product_image_size'] == 'custom' ) {
					$thumb_width  = $atts['product_custom_thumb_width'];
					$thumb_height = $atts['product_custom_thumb_height'];
				} else {
					$product_image_size = explode( "x", $atts['product_image_size'] );
					$thumb_width        = $product_image_size[0];
					$thumb_height       = $product_image_size[1];
				}
				if ( $atts['product_style'] == 3 ) {
					$thumb_width  = 130;
					$thumb_height = 130;
				}
				if ( $thumb_width > 0 ) {
					add_filter( 'kuteshop_shop_pruduct_thumb_width', create_function( '', 'return ' . $thumb_width . ';' ) );
				}
				if ( $thumb_height > 0 ) {
					add_filter( 'kuteshop_shop_pruduct_thumb_height', create_function( '', 'return ' . $thumb_height . ';' ) );
				}
			}
			$products             = apply_filters( 'getProducts', $atts );
			$total_product        = $products->post_count;
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
			$product_list_class = array( 'content-product-append' );
			$owl_settings       = '';
			if ( $atts['productsliststyle'] == 'grid' ) {
				$product_list_class[] = 'product-list-grid row auto-clear equal-container better-height ';
				$product_item_class[] = $atts['boostrap_rows_space'];
				$product_item_class[] = 'col-bg-' . $atts['boostrap_bg_items'];
				$product_item_class[] = 'col-lg-' . $atts['boostrap_lg_items'];
				$product_item_class[] = 'col-md-' . $atts['boostrap_md_items'];
				$product_item_class[] = 'col-sm-' . $atts['boostrap_sm_items'];
				$product_item_class[] = 'col-xs-' . $atts['boostrap_xs_items'];
				$product_item_class[] = 'col-ts-' . $atts['boostrap_ts_items'];
			}
			if ( $atts['productsliststyle'] == 'owl' ) {
				if ( $total_product < $atts['owl_lg_items'] ) {
					$atts['owl_loop'] = 'false';
				}
				$product_list_class[] = 'product-list-owl owl-slick equal-container better-height';
				$product_list_class[] = $atts['owl_navigation_style'];
				$product_item_class[] = $atts['owl_rows_space'];
				$owl_settings         = apply_filters( 'generate_carousel_data_attributes', 'owl_', $atts );
			}
			ob_start();
			?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>"
                 data-self_id="<?php echo $atts['content_ajax'] == true ? esc_attr( $atts['products_custom_id'] ) : ''; ?>"
                 data-list_style="<?php echo esc_attr( $atts['productsliststyle'] ); ?>">
				<?php if ( $atts['the_title'] ) : ?>
                    <h3 class="kuteshop-title">
						<?php if ( $atts['product_style'] == '5' ) :
							$character = str_split( $atts['the_title'] );
							$color     = '';
							$count     = 1;
							foreach ( $character as $value ) {
								if ( $value == ' ' ) {
									echo '<span class="space"></span>';
									$color = 'color';
									if ( $count % 2 == 0 ) {
										$color = '';
									}
									$count++;
								} else {
									echo '<span class="text ' . $color . '">' . esc_html( $value ) . '</span>';
								}
							}
						else: ?>
                            <span class="title"><?php echo esc_html( $atts['the_title'] ); ?></span>
						<?php endif; ?>
                    </h3>
				<?php endif;
				if ( $atts['box_brand'] == true ) : ?>
                    <div class="box-brand">
						<?php if ( $atts['banner_brand'] ) {
							$kuteshop_blog_lazy = apply_filters( 'theme_get_option', 'kuteshop_theme_lazy_load' );
							$lazy_check         = $kuteshop_blog_lazy == 1 ? true : false;
							$image_thumb        = apply_filters( 'theme_resize_image', $atts['banner_brand'], 180, 100, true, $lazy_check );
							echo '<figure class="thumb">';
							echo htmlspecialchars_decode( $image_thumb['img'] );
							echo '</figure>';
						}
						if ( $atts['desc'] ) : ?>
                            <p class="desc"><?php echo htmlspecialchars_decode( $atts['desc'] ); ?></p>
						<?php endif;
						$link_brand = vc_build_link( $atts['link_brand'] );
						if ( $link_brand['url'] ) : ?>
                            <div class="button-brand">
                                <a href="<?php echo esc_url( $link_brand['url'] ); ?>"
                                   target="<?php echo esc_attr( $link_brand['target'] ); ?>" class="button">
									<?php echo esc_html( $link_brand['title'] ); ?>
                                </a>
                            </div>
						<?php endif; ?>
                    </div>
				<?php endif;
				if ( $atts['enable_countdown'] == true ): ?>
                    <div class="product-countdown <?php echo esc_attr( $atts['countdown_style'] ); ?>">
						<?php if ( $atts['countdown_style'] == 'style1' ) : ?>
                            <div class="title">
                                <i class="flaticon-clock"></i>
								<?php echo esc_html__( 'END IN', 'kuteshop-toolkit' ); ?>
                            </div>
						<?php endif; ?>
                        <div class="kuteshop-countdown"
                             data-datetime="<?php echo esc_attr( $atts['time_countdown'] ); ?>">
                        </div>
                    </div>
				<?php endif;
				if ( $products->have_posts() ):
					if ( $atts['productsliststyle'] == 'grid' ): ?>
                        <div class="<?php echo esc_attr( implode( ' ', $product_list_class ) ); ?>">
							<?php while ( $products->have_posts() ) : $products->the_post(); ?>
                                <div <?php post_class( $product_item_class ); ?>>
									<?php get_template_part( 'woo-templates/product-styles/content-product-style', $atts['product_style'] ); ?>
                                </div>
							<?php endwhile; ?>
                        </div>
                        <!-- OWL Products -->
					<?php elseif ( $atts['productsliststyle'] == 'owl' ) : ?>
                        <div class="<?php echo esc_attr( implode( ' ', $product_list_class ) ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
							<?php while ( $products->have_posts() ) : $products->the_post(); ?>
                                <div <?php post_class( $product_item_class ); ?>>
									<?php get_template_part( 'woo-templates/product-styles/content-product-style', $atts['product_style'] ); ?>
                                </div>
							<?php endwhile; ?>
                        </div>
					<?php endif;
				else: ?>
                    <p>
                        <strong><?php esc_html_e( 'No Product', 'kuteshop-toolkit' ); ?></strong>
                    </p>
				<?php endif; ?>
            </div>
			<?php
			wp_reset_postdata();
			$html = ob_get_clean();

			return apply_filters( 'Kuteshop_Shortcode_Products', $html, $atts, $content );
		}
	}

	new Kuteshop_Shortcode_Products();
}