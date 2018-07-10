<?php
// Prevent direct access to this file
defined( 'ABSPATH' ) || die( 'Direct access to this file is not allowed.' );
/**
 * Core class.
 *
 * @package  KuteTheme
 * @since    1.0
 */
if ( !class_exists( 'Kuteshop_framework' ) ) {
	class Kuteshop_framework
	{
		/**
		 * Define theme version.
		 *
		 * @var  string
		 */
		const VERSION = '1.0.0';

		public function __construct()
		{
			add_action( 'init', array( $this, 'start_session' ), 1 );
			add_filter( 'cs_framework_override', create_function( '', 'return "framework/settings";' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'popup_content', array( $this, 'popup_content' ) );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_filter( 'theme_get_meta', array( $this, 'theme_get_meta' ), 10, 2 );
			add_filter( 'theme_get_option', array( $this, 'theme_get_option' ), 10, 2 );
			add_filter( 'theme_resize_image', array( $this, 'theme_resize_image' ), 10, 5 );
			$this->includes();
		}

		function start_session()
		{
			if ( !session_id() ) {
				session_start();
			}
		}

		function theme_get_meta( $meta_key, $meta_value )
		{
			$main_data            = '';
			$enable_theme_options = self::theme_get_option( 'enable_theme_options' );
			$meta_data            = get_post_meta( get_the_ID(), $meta_key, true );
			if ( is_page() && isset( $meta_data[$meta_value] ) && $enable_theme_options == 1 ) {
				$main_data = $meta_data[$meta_value];
			}

			return $main_data;
		}

		function theme_get_option( $option_name = '', $default = '' )
		{
			$get_value = isset( $_GET[$option_name] ) ? $_GET[$option_name] : '';
			$cs_option = null;
			if ( defined( 'CS_VERSION' ) ) {
				$cs_option = get_option( CS_OPTION );
			}
			if ( isset( $_GET[$option_name] ) ) {
				$cs_option = $get_value;
				$default   = $get_value;
			}
			$options = apply_filters( 'cs_get_option', $cs_option, $option_name, $default );
			if ( !empty( $option_name ) && !empty( $options[$option_name] ) ) {
				$option = $options[$option_name];
				if ( is_array( $option ) && isset( $option['multilang'] ) && $option['multilang'] == true ) {
					if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
						if ( isset( $option[ICL_LANGUAGE_CODE] ) ) {
							return $option[ICL_LANGUAGE_CODE];
						}
					}
				}

				return $option;
			} else {
				return ( !empty( $default ) ) ? $default : null;
			}
		}

		function body_class( $classes )
		{
			$my_theme  = wp_get_theme();
			$classes[] = $my_theme->get( 'Name' ) . "-" . $my_theme->get( 'Version' );

			return $classes;
		}

		function popup_content()
		{
			$kuteshop_enable_popup            = self::theme_get_option( 'kuteshop_enable_popup' );
			$kuteshop_popup_title             = self::theme_get_option( 'kuteshop_popup_title', 'signup for our newsletter & promotions' );
			$kuteshop_popup_highlight         = self::theme_get_option( 'kuteshop_popup_highlight', '' );
			$kuteshop_popup_desc              = self::theme_get_option( 'kuteshop_popup_desc', '' );
			$kuteshop_popup_input_placeholder = self::theme_get_option( 'kuteshop_popup_input_placeholder', 'Enter your email...' );
			$kuteshop_poppup_background       = self::theme_get_option( 'kuteshop_poppup_background', '' );
			$kuteshop_blog_lazy               = self::theme_get_option( 'kuteshop_theme_lazy_load' );
			$lazy_check                       = $kuteshop_blog_lazy == 1 ? true : false;
			if ( $kuteshop_enable_popup == 1 ) :
				?>
                <!--  Popup Newsletter-->
                <div class="modal fade" id="popup-newsletter" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i class="pe-7s-close"></i>
                            </button>
                            <div class="modal-inner">
								<?php if ( $kuteshop_poppup_background ) : ?>
                                    <div class="modal-thumb">
										<?php
										$image_thumb = apply_filters( 'theme_resize_image', $kuteshop_poppup_background, 400, 500, true, $lazy_check );
										echo htmlspecialchars_decode( $image_thumb['img'] );
										?>
                                    </div>
								<?php endif; ?>
                                <div class="modal-info">
									<?php if ( $kuteshop_popup_title ): ?>
                                        <h2 class="title"><?php echo esc_html( $kuteshop_popup_title ); ?></h2>
									<?php endif;
									if ( $kuteshop_popup_highlight ) : ?>
                                        <p class="highlight"><?php echo htmlspecialchars_decode( $kuteshop_popup_highlight ); ?></p>
									<?php endif;
									if ( $kuteshop_popup_desc ): ?>
                                        <p class="des"><?php echo esc_html( $kuteshop_popup_desc ); ?></p>
									<?php endif; ?>
                                    <div class="newsletter-form-wrap">
                                        <input class="email" type="email" name="email"
                                               placeholder="<?php echo esc_html( $kuteshop_popup_input_placeholder ); ?>">
                                        <button type="submit" name="submit_button" class="btn-submit submit-newsletter">
                                            <span class="pe-7s-check"></span>
                                        </button>
                                    </div>
                                    <div class="checkbox btn-checkbox">
                                        <label>
                                            <input class="kuteshop_disabled_popup_by_user" type="checkbox">
                                            <span><?php echo esc_html__( 'Don&rsquo;t show this popup again', 'kuteshop' ); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--  Popup Newsletter-->
			<?php endif;
		}

		function theme_resize_image( $attach_id = null, $width, $height, $crop = false, $use_lazy = false )
		{
			$img_lazy = "data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%20" . $width . "%20" . $height . "%27%2F%3E";
			if ( is_singular() && !$attach_id ) {
				if ( has_post_thumbnail() && !post_password_required() ) {
					$attach_id = get_post_thumbnail_id();
				}
			}
			$image_src = array();
			if ( $attach_id ) {
				$image_src        = wp_get_attachment_image_src( $attach_id, 'full' );
				$actual_file_path = get_attached_file( $attach_id );
			}
			if ( !empty( $actual_file_path ) && file_exists( $actual_file_path ) ) {
				$file_info        = pathinfo( $actual_file_path );
				$extension        = '.' . $file_info['extension'];
				$no_ext_path      = $file_info['dirname'] . '/' . $file_info['filename'];
				$cropped_img_path = $no_ext_path . '-' . $width . 'x' . $height . $extension;
				if ( $image_src[1] > $width || $image_src[2] > $height ) {
					if ( file_exists( $cropped_img_path ) ) {
						$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
						$vt_image        = array(
							'url'    => $cropped_img_url,
							'width'  => $width,
							'height' => $height,
							'img'    => '<img class="img-responsive" src="' . esc_url( $cropped_img_url ) . '" ' . image_hwstring( $width, $height ) . ' alt="kuteshop">',
						);
						if ( $use_lazy == true ) {
							$vt_image['img'] = '<img class="img-responsive lazy" src="' . esc_attr( $img_lazy ) . '" data-src="' . esc_url( $cropped_img_url ) . '" ' . image_hwstring( $width, $height ) . ' alt="kuteshop">';
						}

						return $vt_image;
					}
					if ( $crop == false ) {
						$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
						$resized_img_path  = $no_ext_path . '-' . $proportional_size[0] . 'x' . $proportional_size[1] . $extension;
						if ( file_exists( $resized_img_path ) ) {
							$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );
							$vt_image        = array(
								'url'    => $resized_img_url,
								'width'  => $proportional_size[0],
								'height' => $proportional_size[1],
								'img'    => '<img class="img-responsive" src="' . esc_url( $resized_img_url ) . '" ' . image_hwstring( $proportional_size[0], $proportional_size[1] ) . ' alt="kuteshop">',
							);
							if ( $use_lazy == true ) {
								$vt_image['img'] = '<img class="img-responsive lazy" src="' . esc_attr( $img_lazy ) . '" data-src="' . esc_url( $resized_img_url ) . '" ' . image_hwstring( $proportional_size[0], $proportional_size[1] ) . ' alt="kuteshop">';
							}

							return $vt_image;
						}
					}
					/*no cache files - let's finally resize it*/
					$img_editor = wp_get_image_editor( $actual_file_path );
					if ( is_wp_error( $img_editor ) || is_wp_error( $img_editor->resize( $width, $height, $crop ) ) ) {
						return array(
							'url'    => '',
							'width'  => '',
							'height' => '',
							'img'    => '',
						);
					}
					$new_img_path = $img_editor->generate_filename();
					if ( is_wp_error( $img_editor->save( $new_img_path ) ) ) {
						return array(
							'url'    => '',
							'width'  => '',
							'height' => '',
							'img'    => '',
						);
					}
					if ( !is_string( $new_img_path ) ) {
						return array(
							'url'    => '',
							'width'  => '',
							'height' => '',
							'img'    => '',
						);
					}
					$new_img_size = getimagesize( $new_img_path );
					$new_img      = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );
					$vt_image     = array(
						'url'    => $new_img,
						'width'  => $new_img_size[0],
						'height' => $new_img_size[1],
						'img'    => '<img class="img-responsive" src="' . esc_url( $new_img ) . '" ' . image_hwstring( $new_img_size[0], $new_img_size[1] ) . ' alt="kuteshop">',
					);
					if ( $use_lazy == true ) {
						$vt_image['img'] = '<img class="img-responsive lazy" src="' . esc_attr( $img_lazy ) . '" data-src="' . esc_url( $new_img ) . '" ' . image_hwstring( $new_img_size[0], $new_img_size[1] ) . ' alt="kuteshop">';
					}

					return $vt_image;
				}
				$vt_image = array(
					'url'    => $image_src[0],
					'width'  => $image_src[1],
					'height' => $image_src[2],
					'img'    => '<img class="img-responsive" src="' . esc_url( $image_src[0] ) . '" ' . image_hwstring( $image_src[1], $image_src[2] ) . ' alt="kuteshop">',
				);
				if ( $use_lazy == true ) {
					$vt_image['img'] = '<img class="img-responsive lazy" src="' . esc_attr( $img_lazy ) . '" data-src="' . esc_url( $image_src[0] ) . '" ' . image_hwstring( $image_src[1], $image_src[2] ) . ' alt="kuteshop">';
				}

				return $vt_image;
			} else {
				$width    = intval( $width );
				$height   = intval( $height );
				$vt_image = array(
					'url'    => 'https://via.placeholder.com/' . $width . 'x' . $height,
					'width'  => $width,
					'height' => $height,
					'img'    => '<img class="img-responsive" src="' . esc_url( 'https://via.placeholder.com/' . $width . 'x' . $height ) . '" ' . image_hwstring( $width, $height ) . ' alt="kuteshop">',
				);

				return $vt_image;
			}
		}

		function enqueue_scripts( $hook )
		{
			/* CUSTOM FRAMEWORK */
			wp_enqueue_style( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'pe-icon-7-stroke', get_theme_file_uri( '/assets/css/pe-icon-7-stroke.min.css' ), array(), '1.0' );
			wp_enqueue_style( 'flaticon', get_theme_file_uri( '/assets/css/flaticon.css' ), array(), '1.0' );
			wp_enqueue_style( 'custom-admin-css', get_theme_file_uri( '/framework/assets/css/admin.css' ), array(), '1.0' );
			wp_enqueue_script( 'custom-admin-js', get_theme_file_uri( '/framework/assets/js/admin.js' ), array(), '1.0', true );
			if ( $hook == 'kuteshop_page_kuteshop' ) {
				// ACE Editor
				wp_enqueue_style( 'cs-vendor-ace-style', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/ace.css' ), array(), '1.0' );
				wp_enqueue_script( 'cs-vendor-ace', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/ace.js' ), array(), false, true );
				wp_enqueue_script( 'cs-vendor-ace-mode', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/mode-css.js' ), array(), false, true );
				wp_enqueue_script( 'cs-vendor-ace-language_tools', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/ext-language_tools.js' ), array(), false, true );
				wp_enqueue_script( 'cs-vendor-ace-css', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/css.js' ), array(), false, true );
				wp_enqueue_script( 'cs-vendor-ace-text', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/text.js' ), array(), false, true );
				wp_enqueue_script( 'cs-vendor-ace-javascript', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/javascript.js' ), array(), false, true );
				// You do not need to use a separate file if you do not like.
				wp_enqueue_script( 'cs-vendor-ace-load', get_theme_file_uri( '/framework/settings/fields/ace_editor/assets/ace-load.js' ), array(), false, true );
			}
		}

		public function includes()
		{
			/* Classes */
			require_once get_parent_theme_file_path( '/framework/includes/classes/class-tgm-plugin-activation.php' );
			require_once get_parent_theme_file_path( '/framework/includes/classes/breadcrumbs.php' );
			require_once get_parent_theme_file_path( '/framework/settings/theme-options.php' );
			/*Mega menu */
			require_once get_parent_theme_file_path( '/framework/includes/megamenu/megamenu.php' );
			/*Plugin load*/
			require_once get_parent_theme_file_path( '/framework/settings/plugins-load.php' );
			/*Theme Functions*/
			require_once get_parent_theme_file_path( '/framework/includes/theme-functions.php' );
			/* Custom css and js*/
			require_once get_parent_theme_file_path( '/framework/settings/custom-css-js.php' );
			// Register custom shortcodes
			if ( class_exists( 'Vc_Manager' ) ) {
				require_once get_parent_theme_file_path( '/framework/includes/visual-composer.php' );
			}
			if ( class_exists( 'WooCommerce' ) ) {
				require_once get_parent_theme_file_path( '/framework/includes/woo-functions.php' );
			}
		}
	}

	new Kuteshop_framework();
}