<?php
include_once( 'classes/welcome.php' );
include_once( 'framework/cs-framework.php' );
/* MAILCHIP */
include_once( 'classes/mailchimp/MCAPI.class.php' );
include_once( 'classes/mailchimp/mailchimp-settings.php' );
include_once( 'classes/mailchimp/mailchimp.php' );
/* WIDGET */
include_once( 'widgets/widget-post.php' );
include_once( 'widgets/widget-category.php' );
if ( class_exists( 'WooCommerce' ) ) {
	include_once( 'widgets/widget-products.php' );
	include_once( 'widgets/widget-product-filter.php' );
	include_once( 'widgets/widget-woo-layered-nav.php' );
	include_once( 'classes/woo-attributes-swatches/woo-term.php' );
	include_once( 'classes/woo-attributes-swatches/woo-product-attribute-meta.php' );
}
if ( class_exists( 'Vc_Manager' ) ) {
	add_action( 'vc_load_default_templates_action', 'add_custom_template_for_vc' );
	function is_url_exist( $url )
	{
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_exec( $ch );
		$code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		if ( $code == 200 ) {
			$status = true;
		} else {
			$status = false;
		}
		curl_close( $ch );

		return $status;
	}

	function add_custom_template_for_vc()
	{
		$option_file_url = esc_url( 'http://kuteshop.kutethemes.net/wp-content/uploads/template.txt' );
		if ( is_url_exist( $option_file_url ) == true ) {
			$option_content  = wp_remote_get( $option_file_url );
			$option_content  = $option_content['body'];
			$option_content  = base64_decode( $option_content );
			$options_configs = json_decode( $option_content, true );
			foreach ( $options_configs as $value ) {
				$data                 = array();
				$data['name']         = $value['name'];
				$data['weight']       = 1;
				$data['custom_class'] = 'custom_template_for_vc_custom_template';
				$data['content']      = $value['content'];
				vc_add_default_templates( $data );
			}
		}
	}
}