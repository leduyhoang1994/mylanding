<?php
if ( !class_exists( 'Kuteshop_Shortcode' ) ) {
	class Kuteshop_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = '';
		/**
		 * Register shortcode with WordPress.
		 *
		 * @return  void
		 */
		/**
		 * Meta key.
		 *
		 * @var  string
		 */
		protected $metakey = '_Kuteshop_Shortcode_custom_css';

		public function __construct()
		{
			if ( !empty( $this->shortcode ) ) {
				// Add shortcode.
				add_shortcode( "kuteshop_{$this->shortcode}", array( $this, 'output_html' ) );
				// Hook into post saving.
				add_action( 'save_post', array( $this, 'update_post' ) );
			}
		}

		/**
		 * Replace and save custom css to post meta.
		 *
		 * @param   int $post_id
		 *
		 * @return  void
		 */
		public function update_post( $post_id )
		{
			if ( !isset( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
				return;
			}
			// Set and replace content.
			$post = $this->replace_post( $post_id );
			if ( $post ) {
				// Generate custom CSS.
				$css = $this->get_css( $post->post_content );
				// Update post and save CSS to post meta.
				$this->save_post( $post );
				$this->save_postmeta( $post_id, $css );
			} else {
				$this->save_postmeta( $post_id, '' );
			}
		}

		/**
		 * Parse shortcode custom css string.
		 *
		 * @param   string $content
		 * @return  string
		 */
		public function get_css( $content )
		{
			$css = '';
			if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes ) ) {
				foreach ( $shortcodes[2] as $index => $tag ) {
					if ( strpos( $tag, 'kuteshop_' ) !== false ) {
						$atts      = shortcode_parse_atts( trim( $shortcodes[3][$index] ) );
						$shortcode = explode( '_', $tag );
						$shortcode = end( $shortcode );
						$class     = 'Kuteshop_Shortcode_' . implode( '_', array_map( 'ucfirst', explode( '-', $shortcode ) ) );
						if ( class_exists( $class ) ) {
							$css .= $class::generate_css( $atts );
						}
					}
				}
				foreach ( $shortcodes[5] as $shortcode_content ) {
					$css .= $this->get_css( $shortcode_content );
				}
			}

			return $css;
		}

		/**
		 * Update post data content.
		 *
		 * @param   int $post WP_Post object.
		 *
		 * @return  void
		 */
		public function save_post( $post )
		{
			// Sanitize post data for inserting into database.
			$data = sanitize_post( $post, 'db' );
			// Update post content.
			global $wpdb;
			$wpdb->query( "UPDATE {$wpdb->posts} SET post_content = '" . esc_sql( $data->post_content ) . "' WHERE ID = {$data->ID};" );
			// Update post cache.
			$data = sanitize_post( $post, 'raw' );
			wp_cache_replace( $data->ID, $data, 'posts' );
		}

		/**
		 * Update extra post meta.
		 *
		 * @param   int $post_id Post ID.
		 * @param   string $css Custom CSS.
		 *
		 * @return  void
		 */
		public function save_postmeta( $post_id, $css )
		{
			if ( $post_id && $this->metakey ) {
				if ( empty( $css ) ) {
					delete_post_meta( $post_id, $this->metakey );
				} else {
					update_post_meta( $post_id, $this->metakey, preg_replace( '/[\t\r\n]/', '', $css ) );
				}
			}
		}

		/**
		 * Replace shortcode used in a post with real content.
		 *
		 * @param   int $post_id Post ID.
		 *
		 * @return  WP_Post object or null.
		 */
		public function replace_post( $post_id )
		{
			// Get post.
			$post = get_post( $post_id );
			if ( $post ) {
				if ( has_shortcode( $post->post_content, "kuteshop_{$this->shortcode}" ) ) {
					$post->post_content = preg_replace_callback(
						'/(' . $this->shortcode . '_custom_id)="[^"]+"/',
						'Kuteshop_Shortcode_replace_post_callback',
						$post->post_content
					);
				}
			}

			return $post;
		}

		/**
		 * Generate custom CSS.
		 *
		 * @param   array $atts Shortcode parameters.
		 *
		 * @return  string
		 */
		public static function generate_css( $atts )
		{
			return '';
		}

		public function output_html( $atts, $content = null )
		{
			return '';
		}

		function get_all_attributes( $tag, $text )
		{
			preg_match_all( '/' . get_shortcode_regex() . '/s', $text, $matches );
			$out               = array();
			$shortcode_content = array();
			if ( isset( $matches[5] ) ) {
				$shortcode_content = $matches[5];
			}
			if ( isset( $matches[2] ) ) {
				$i = 0;
				foreach ( (array)$matches[2] as $key => $value ) {
					if ( $tag === $value ) {
						$out[$i]            = shortcode_parse_atts( $matches[3][$key] );
						$out[$i]['content'] = $matches[5][$key];
					}
					$i++;
				}
			}

			return $out;
		}
	}
}
if ( !function_exists( 'Kuteshop_Shortcode_replace_post_callback' ) ) {
	function Kuteshop_Shortcode_replace_post_callback( $matches )
	{
		// Generate a random string to use as element ID.
		$id = 'kuteshop_custom_css_' . uniqid();

		return $matches[1] . '="' . $id . '"';
	}
}
if ( !function_exists( 'kuteshop_toolkit_vc_param' ) ) {
	add_action( 'init', 'kuteshop_toolkit_vc_param' );
	function kuteshop_toolkit_vc_param( $key = false, $value = false )
	{
		return vc_add_shortcode_param( $key, $value );
	}
}
// Check plugin wc is activate
$active_plugin_wc = is_plugin_active( 'woocommerce/woocommerce.php' );
$shortcodes       = array(
	'tabs',
	'custommenu',
	'blog',
	'newsletter',
	'socials',
	'banner',
	'slider',
	'iconbox',
	'category',
	'googlemap',
	'simpleSEO',
	'testimonials',
	'heading',
);
if ( $active_plugin_wc ) {
	$shortcode_woo = array(
		'products',
	);
	$shortcodes    = array_merge( $shortcodes, $shortcode_woo );
}
foreach ( $shortcodes as $shortcode ) {
	// Include shortcode class declaration file.
	$shortcode = str_replace( '_', '-', $shortcode );
	if ( is_file( KUTESHOP_TOOLKIT_PATH . '/includes/shortcodes/' . $shortcode . '.php' ) ) {
		include_once KUTESHOP_TOOLKIT_PATH . '/includes/shortcodes/' . $shortcode . '.php';
	}
}
if ( !function_exists( 'kuteshop_shortcode_print_inline_css' ) ) {
	function kuteshop_shortcode_print_inline_css( $css )
	{
		// Get all custom inline CSS.
		if ( is_singular() ) {
			$post_custom_css = get_post_meta( get_the_ID(), '_Kuteshop_Shortcode_custom_css', true );
			$inline_css[]    = $post_custom_css;
			$inline_css      = apply_filters( 'kuteshop-shortcode-inline-css', $inline_css );
			$css             .= preg_replace( '/\s+/', ' ', implode( ' ', $inline_css ) );
		}

		return $css;
	}

	add_filter( 'kuteshop_main_custom_css', 'kuteshop_shortcode_print_inline_css' );
}