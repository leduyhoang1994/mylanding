<?php
if ( !class_exists( 'Kuteshop_Shortcode_Googlemap' ) ) {
	class Kuteshop_Shortcode_Googlemap extends Kuteshop_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'googlemap';
		/**
		 * Default $atts .
		 *
		 * @var  array
		 */
		public $default_atts = array();

		public static function generate_css( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'kuteshop_googlemap', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';
			$css .= '.' . $atts['googlemap_custom_id'] . '.kuteshop-google-maps { min-height:' . $atts['map_height'] . 'px;} ';

			return $css;
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'kuteshop_googlemap', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css_class   = array( 'kuteshop-google-maps' );
			$css_class[] = $atts['el_class'];
			$css_class[] = $atts['googlemap_custom_id'];
			if ( function_exists( 'vc_shortcode_custom_css_class' ) ) {
				$css_class[] = ' ' . apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $atts['css'], ' ' ), '', $atts );
			}
			ob_start();
			?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>"
                 id="az-google-maps<?php echo uniqid(); ?>"
                 data-hue=""
                 data-lightness="1"
                 data-map-style="<?php echo esc_attr( $atts['info_content'] ) ?>"
                 data-saturation="-100"
                 data-modify-coloring="false"
                 data-title_maps="<?php echo esc_html( $atts['title'] ) ?>"
                 data-phone="<?php echo esc_html( $atts['phone'] ) ?>"
                 data-email="<?php echo esc_html( $atts['email'] ) ?>"
                 data-address="<?php echo esc_html( $atts['address'] ) ?>"
                 data-longitude="<?php echo esc_html( $atts['longitude'] ) ?>"
                 data-latitude="<?php echo esc_html( $atts['latitude'] ) ?>"
                 data-pin-icon=""
                 data-zoom="<?php echo esc_html( $atts['zoom'] ) ?>"
                 data-map-type="<?php echo esc_attr( $atts['map_type'] ) ?>">
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Kuteshop_Shortcode_Googlemap', $html, $atts, $content );
		}
	}

	new Kuteshop_Shortcode_Googlemap();
}