<?php

if ( !class_exists( 'Kuteshop_PluginLoad' ) ) {
	class Kuteshop_PluginLoad
	{
		public $plugins = array();
		public $config  = array();

		public function __construct()
		{
			$this->plugins();
			$this->config();
			if ( !class_exists( 'TGM_Plugin_Activation' ) ) {
				return false;
			}

			if ( function_exists( 'tgmpa' ) ) {
				tgmpa( $this->plugins, $this->config );
			}

		}

		public function plugins()
		{
			$this->plugins = array(
				array(
					'name'               => 'kuteshop-toolkit', // The plugin name
					'slug'               => 'kuteshop-toolkit', // The plugin slug (typically the folder name)
					'source'             => esc_url( 'http://kutethemes.net/wordpress/plugins/kuteshop-toolkit.zip' ), // The plugin source
					'required'           => true, // If false, the plugin is only 'recommended' instead of required
					'version'            => '1.0.1', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
					'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
					'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
					'external_url'       => '', // If set, overrides default API URL and points to an external URL
					'image'              => '',
				),
				array(
					'name'               => 'Revolution Slider', // The plugin name
					'slug'               => 'revslider', // The plugin slug (typically the folder name)
					'source'             => esc_url( 'http://kutethemes.net/wordpress/plugins/revslider.zip' ), // The plugin source
					'required'           => true, // If false, the plugin is only 'recommended' instead of required
					'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
					'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
					'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
					'external_url'       => '', // If set, overrides default API URL and points to an external URL
					'image'              => esc_url( 'http://kutethemes.net/wordpress/demos/images/images-import/slider-revolution.jpg' ),
				),
				array(
					'name'               => 'WPBakery Visual Composer', // The plugin name
					'slug'               => 'js_composer', // The plugin slug (typically the folder name)
					'source'             => esc_url( 'http://kutethemes.net/wordpress/plugins/js_composer.zip' ), // The plugin source
					'required'           => true, // If false, the plugin is only 'recommended' instead of required
					'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
					'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
					'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
					'external_url'       => '', // If set, overrides default API URL and points to an external URL
					'image'              => esc_url( 'http://kutethemes.net/wordpress/demos/images/images-import/visual-composer.png' ),
				),
				array(
					'name'     => 'WooCommerce',
					'slug'     => 'woocommerce',
					'required' => true,
					'image'    => esc_url( 'http://kutethemes.net/wordpress/demos/images/images-import/woocommerce.png' ),
				),
				array(
					'name'     => 'YITH WooCommerce Compare',
					'slug'     => 'yith-woocommerce-compare',
					'required' => false,
					'image'    => esc_url( 'http://kutethemes.net/wordpress/demos/images/images-import/compare.jpg' ),
				),
				array(
					'name'     => 'YITH WooCommerce Wishlist', // The plugin name
					'slug'     => 'yith-woocommerce-wishlist', // The plugin slug (typically the folder name)
					'required' => false, // If false, the plugin is only 'recommended' instead of required
					'image'    => esc_url( 'http://kutethemes.net/wordpress/demos/images/images-import/wishlist.jpg' ),
				),
				array(
					'name'     => 'YITH WooCommerce Quick View', // The plugin name
					'slug'     => 'yith-woocommerce-quick-view', // The plugin slug (typically the folder name)
					'required' => false, // If false, the plugin is only 'recommended' instead of required
					'image'    => esc_url( 'http://kutethemes.net/wordpress/demos/images/images-import/quickview.jpg' ),
				),
				array(
					'name'     => 'Contact Form 7', // The plugin name
					'slug'     => 'contact-form-7', // The plugin slug (typically the folder name)
					'required' => false, // If false, the plugin is only 'recommended' instead of required
					'image'    => esc_url( 'http://kutethemes.net/wordpress/demos/images/images-import/contactform7.png' ),
				),
			);
		}

		public function config()
		{
			$this->config = array(
				'id'           => 'kuteshop',                 // Unique ID for hashing notices for multiple instances of TGMPA.
				'default_path' => '',                      // Default absolute path to bundled plugins.
				'menu'         => 'kuteshop-install-plugins', // Menu slug.
				'parent_slug'  => 'themes.php',            // Parent menu slug.
				'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
				'has_notices'  => true,                    // Show admin notices or not.
				'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
				'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
				'is_automatic' => true,                   // Automatically activate plugins after installation or not.
				'message'      => '',                      // Message to output right before the plugins table.
			);
		}
	}


}
if ( !function_exists( 'Kuteshop_PluginLoad' ) ) {
	function Kuteshop_PluginLoad()
	{
		new  Kuteshop_PluginLoad();
	}
}
add_action( 'tgmpa_register', 'Kuteshop_PluginLoad' );