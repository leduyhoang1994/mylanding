<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
if ( !class_exists( 'Kuteshop_ThemeOption' ) ) {
	class Kuteshop_ThemeOption
	{
		public function __construct()
		{
			add_action( 'admin_bar_menu', array( $this, 'kuteshop_custom_menu' ), 1000 );
			add_filter( 'cs_framework_settings', array( $this, 'framework_settings' ) );
			add_filter( 'cs_framework_options', array( $this, 'framework_options' ) );
			add_filter( 'cs_metabox_options', array( $this, 'metabox_options' ) );
			add_filter( 'cs_taxonomy_options', array( $this, 'taxonomy_options' ) );
		}

		public function get_header_options()
		{
			$layoutDir      = get_template_directory() . '/templates/headers/';
			$header_options = array();
			if ( is_dir( $layoutDir ) ) {
				$files = scandir( $layoutDir );
				if ( $files && is_array( $files ) ) {
					foreach ( $files as $file ) {
						if ( $file != '.' && $file != '..' ) {
							$fileInfo = pathinfo( $file );
							if ( $fileInfo['extension'] == 'php' && $fileInfo['basename'] != 'index.php' ) {
								$file_data                  = get_file_data( $layoutDir . $file, array( 'Name' => 'Name' ) );
								$file_name                  = str_replace( 'header-', '', $fileInfo['filename'] );
								$header_options[$file_name] = array(
									'title'   => $file_data['Name'],
									'preview' => get_theme_file_uri( '/templates/headers/header-' . $file_name . '.jpg' ),
								);
							}
						}
					}
				}
			}

			return $header_options;
		}

		public function get_product_options()
		{
			$layoutDir       = get_template_directory() . '/woo-templates/product-styles/';
			$product_options = array();
			$match           = array( 4, 11, 12, 13 );
			if ( is_dir( $layoutDir ) ) {
				$files = scandir( $layoutDir );
				if ( $files && is_array( $files ) ) {
					foreach ( $files as $file ) {
						if ( $file != '.' && $file != '..' ) {
							$fileInfo = pathinfo( $file );
							if ( $fileInfo['extension'] == 'php' && $fileInfo['basename'] != 'index.php' ) {
								$file_data = get_file_data( $layoutDir . $file, array( 'Name' => 'Name' ) );
								$file_name = str_replace( 'content-product-style-', '', $fileInfo['filename'] );
								if ( !in_array( $file_name, $match ) ) {
									$product_options[$file_name] = array(
										'title'   => $file_data['Name'],
										'preview' => get_theme_file_uri( 'woo-templates/product-styles/content-product-style-' . $file_name . '.jpg' ),
									);
								}
							}
						}
					}
				}
			}

			return $product_options;
		}

		public function get_sidebar_options()
		{
			$sidebars = array();
			global $wp_registered_sidebars;
			foreach ( $wp_registered_sidebars as $sidebar ) {
				$sidebars[$sidebar['id']] = $sidebar['name'];
			}

			return $sidebars;
		}

		public function kuteshop_custom_menu()
		{
			global $wp_admin_bar;
			if ( !is_super_admin() || !is_admin_bar_showing() ) return;
			// Add Parent Menu
			$argsParent = array(
				'id'    => 'theme_option',
				'title' => esc_html__( 'Kuteshop Options', 'kuteshop' ),
				'href'  => admin_url( 'admin.php?page=kuteshop' ),
			);
			$wp_admin_bar->add_menu( $argsParent );
		}

		public function get_social_options()
		{
			$socials     = array();
			$all_socials = cs_get_option( 'user_all_social' );
			if ( $all_socials ) {
				foreach ( $all_socials as $key => $social ) {
					$socials[$key] = $social['title_social'];
				}
			}

			return $socials;
		}

		public function get_footer_options()
		{
			$layoutDir      = get_template_directory() . '/templates/footers/';
			$footer_options = array();
			if ( is_dir( $layoutDir ) ) {
				$files = scandir( $layoutDir );
				if ( $files && is_array( $files ) ) {
					foreach ( $files as $file ) {
						if ( $file != '.' && $file != '..' ) {
							$fileInfo = pathinfo( $file );
							if ( $fileInfo['extension'] == 'php' && $fileInfo['basename'] != 'index.php' ) {
								$file_data                  = get_file_data( $layoutDir . $file, array( 'Name' => 'Name' ) );
								$file_name                  = str_replace( 'footer-', '', $fileInfo['filename'] );
								$footer_options[$file_name] = array(
									'title'   => $file_data['Name'],
									'preview' => get_theme_file_uri( '/templates/footers/footer-' . $file_name . '.jpg' ),
								);
							}
						}
					}
				}
			}

			return $footer_options;
		}

		public function get_footer_preview()
		{
			$footer_preview = array();
			$args           = array(
				'post_type'      => 'footer',
				'posts_per_page' => -1,
				'orderby'        => 'ASC',
			);
			$loop           = get_posts( $args );
			foreach ( $loop as $value ) {
				setup_postdata( $value );
				$data_meta                  = get_post_meta( $value->ID, '_custom_footer_options', true );
				$template_style             = isset( $data_meta['kuteshop_footer_style'] ) ? $data_meta['kuteshop_footer_style'] : 'default';
				$footer_preview[$value->ID] = array(
					'title'   => $value->post_title,
					'preview' => get_theme_file_uri( '/templates/footers/footer-' . $template_style . '.jpg' ),
				);
			}

			return $footer_preview;
		}

		function framework_settings( $settings )
		{
			// ===============================================================================================
			// -----------------------------------------------------------------------------------------------
			// FRAMEWORK SETTINGS
			// -----------------------------------------------------------------------------------------------
			// ===============================================================================================
			$settings = array(
				'menu_title'      => esc_html__( 'Theme Options', 'kuteshop' ),
				'menu_type'       => 'submenu', // menu, submenu, options, theme, etc.
				'menu_slug'       => 'kuteshop',
				'ajax_save'       => false,
				'menu_parent'     => 'kuteshop_menu',
				'show_reset_all'  => true,
				'menu_position'   => 2,
				'framework_title' => '<a href="' . esc_url( 'http://kuteshop.kutethemes.net/' ) . '" target="_blank"><img src="' . get_theme_file_uri( '/framework/assets/images/logo.png' ) . '" alt="kuteshop"></a> <i>' . esc_html__( 'By ', 'kuteshop' ) . '<a href="' . esc_url( 'https://themeforest.net/user/kutethemes/portfolio' ) . '" target="_blank">' . esc_html__( 'KuteThemes', 'kuteshop' ) . '</a></i>',
			);

			return $settings;
		}

		function framework_options( $options )
		{
			// ===============================================================================================
			// -----------------------------------------------------------------------------------------------
			// FRAMEWORK OPTIONS
			// -----------------------------------------------------------------------------------------------
			// ===============================================================================================
			$options = array();
			// ----------------------------------------
			// a option section for options overview  -
			// ----------------------------------------
			$options[] = array(
				'name'     => 'general',
				'title'    => esc_html__( 'General', 'kuteshop' ),
				'icon'     => 'fa fa-wordpress',
				'sections' => array(
					array(
						'name'   => 'main_settings',
						'title'  => esc_html__( 'Main Settings', 'kuteshop' ),
						'fields' => array(
							array(
								'id'    => 'kuteshop_logo',
								'type'  => 'image',
								'title' => esc_html__( 'Logo', 'kuteshop' ),
							),
							array(
								'id'      => 'kuteshop_main_color',
								'type'    => 'color_picker',
								'title'   => esc_html__( 'Main Color', 'kuteshop' ),
								'default' => '#ff3366',
								'rgba'    => true,
							),
							array(
								'id'    => 'gmap_api_key',
								'type'  => 'text',
								'title' => esc_html__( 'Google Map API Key', 'kuteshop' ),
								'desc'  => esc_html__( 'Enter your Google Map API key. ', 'kuteshop' ) . '<a href="' . esc_url( 'https://developers.google.com/maps/documentation/javascript/get-api-key' ) . '" target="_blank">' . esc_html__( 'How to get?', 'kuteshop' ) . '</a>',
							),
							array(
								'id'      => 'enable_theme_options',
								'type'    => 'switcher',
								'title'   => esc_html__( 'Enable Meta Box Options', 'kuteshop' ),
								'default' => true,
								'desc'    => esc_html__( 'Enable for using Themes setting each single page.', 'kuteshop' ),
							),
							array(
								'id'    => 'kuteshop_theme_lazy_load',
								'type'  => 'switcher',
								'title' => esc_html__( 'Use image Lazy Load', 'kuteshop' ),
							),
						),
					),
					array(
						'name'   => 'popup_settings',
						'title'  => esc_html__( 'Newsletter Settings', 'kuteshop' ),
						'fields' => array(
							array(
								'id'      => 'kuteshop_enable_popup',
								'type'    => 'switcher',
								'title'   => esc_html__( 'Enable Popup Newsletter', 'kuteshop' ),
								'default' => false,
							),
							array(
								'id'         => 'kuteshop_poppup_background',
								'type'       => 'image',
								'title'      => esc_html__( 'Popup Background', 'kuteshop' ),
								'dependency' => array( 'kuteshop_enable_popup', '==', '1' ),
							),
							array(
								'id'         => 'kuteshop_popup_title',
								'type'       => 'text',
								'title'      => esc_html__( 'Title', 'kuteshop' ),
								'dependency' => array( 'kuteshop_enable_popup', '==', '1' ),
							),
							array(
								'id'         => 'kuteshop_popup_highlight',
								'type'       => 'wysiwyg',
								'title'      => esc_html__( 'Highlight', 'kuteshop' ),
								'dependency' => array( 'kuteshop_enable_popup', '==', '1' ),
							),
							array(
								'id'         => 'kuteshop_popup_desc',
								'type'       => 'text',
								'title'      => esc_html__( 'Description', 'kuteshop' ),
								'dependency' => array( 'kuteshop_enable_popup', '==', '1' ),
							),
							array(
								'id'         => 'kuteshop_popup_input_placeholder',
								'type'       => 'text',
								'title'      => esc_html__( 'Input placeholder text', 'kuteshop' ),
								'default'    => esc_html__( 'Enter your email...', 'kuteshop' ),
								'dependency' => array( 'kuteshop_enable_popup', '==', '1' ),
							),
							array(
								'id'         => 'kuteshop_popup_delay_time',
								'type'       => 'number',
								'title'      => esc_html__( 'Delay time', 'kuteshop' ),
								'default'    => '0',
								'dependency' => array( 'kuteshop_enable_popup', '==', '1' ),
							),
							array(
								'id'         => 'kuteshop_enable_popup_mobile',
								'type'       => 'switcher',
								'title'      => esc_html__( 'Enable Poppup on Mobile', 'kuteshop' ),
								'default'    => false,
								'dependency' => array( 'kuteshop_enable_popup', '==', '1' ),
							),
						),
					),
					array(
						'name'   => 'widget_settings',
						'title'  => esc_html__( 'Widget Settings', 'kuteshop' ),
						'fields' => array(
							array(
								'id'              => 'multi_widget',
								'type'            => 'group',
								'title'           => esc_html__( 'Multi Widget', 'kuteshop' ),
								'button_title'    => esc_html__( 'Add Widget', 'kuteshop' ),
								'accordion_title' => esc_html__( 'Add New Field', 'kuteshop' ),
								'fields'          => array(
									array(
										'id'    => 'add_widget',
										'type'  => 'text',
										'title' => esc_html__( 'Name Widget', 'kuteshop' ),
									),
								),
							),
						),
					),
					array(
						'name'   => 'theme_js_css',
						'title'  => esc_html__( 'Customs JS/CSS', 'kuteshop' ),
						'fields' => array(
							array(
								'id'         => 'kuteshop_custom_js',
								'type'       => 'ace_editor',
								'before'     => '<h1>' . esc_html__( 'Custom JS', 'kuteshop' ) . '</h1>',
								'attributes' => array(
									'data-theme' => 'twilight',  // the theme for ACE Editor
									'data-mode'  => 'javascript',     // the language for ACE Editor
								),
							),
						),
					),
				),
			);
			$options[] = array(
				'name'     => 'header',
				'title'    => esc_html__( 'Header Settings', 'kuteshop' ),
				'icon'     => 'fa fa-folder-open-o',
				'sections' => array(
					array(
						'name'   => 'main_header',
						'title'  => esc_html__( 'Header Settings', 'kuteshop' ),
						'fields' => array(
							array(
								'id'    => 'kuteshop_enable_sticky_menu',
								'type'  => 'switcher',
								'title' => esc_html__( 'Main Menu Sticky', 'kuteshop' ),
							),
							array(
								'id'         => 'kuteshop_used_header',
								'type'       => 'select_preview',
								'title'      => esc_html__( 'Header Layout', 'kuteshop' ),
								'desc'       => esc_html__( 'Select a header layout', 'kuteshop' ),
								'options'    => self::get_header_options(),
								'default'    => 'style-01',
								'attributes' => array(
									'data-depend-id' => 'kuteshop_used_header',
								),
							),
							array(
								'id'         => 'header_text_box',
								'type'       => 'text',
								'title'      => esc_html__( 'Header Text Box', 'kuteshop' ),
								'dependency' => array(
									'kuteshop_used_header', '==', 'style-03',
								),
								'multilang'  => true,
							),
							array(
								'id'              => 'header_service_box',
								'type'            => 'group',
								'title'           => esc_html__( 'Header Service', 'kuteshop' ),
								'button_title'    => esc_html__( 'Add New', 'kuteshop' ),
								'accordion_title' => esc_html__( 'Header Service Settings', 'kuteshop' ),
								'dependency'      => array(
									'kuteshop_used_header', '==', 'style-07',
								),
								'fields'          => array(
									array(
										'id'    => 'service_box_image',
										'type'  => 'image',
										'title' => esc_html__( 'Image', 'kuteshop' ),
									),
									array(
										'id'        => 'service_box_text',
										'type'      => 'text',
										'title'     => esc_html__( 'Text', 'kuteshop' ),
										'multilang' => true,
									),
								),
							),
							array(
								'id'         => 'header_phone',
								'type'       => 'text',
								'title'      => esc_html__( 'Header Phone Number', 'kuteshop' ),
								'dependency' => array(
									'kuteshop_used_header', '==', 'style-11',
								),
							),
							array(
								'id'         => 'header_social',
								'type'       => 'select',
								'title'      => esc_html__( 'Select Social', 'kuteshop' ),
								'options'    => self::get_social_options(),
								'attributes' => array(
									'multiple' => 'multiple',
									'style'    => 'width: 50%;',
								),
								'class'      => 'chosen',
							),
						),
					),
					array(
						'name'   => 'vertical_menu',
						'title'  => esc_html__( 'Vertical Menu Settings', 'kuteshop' ),
						'fields' => array(
							array(
								'id'         => 'enable_vertical_menu',
								'type'       => 'switcher',
								'attributes' => array(
									'data-depend-id' => 'enable_vertical_menu',
								),
								'title'      => esc_html__( 'Vertical Menu', 'kuteshop' ),
								'default'    => false,
							),
							array(
								'id'         => 'block_vertical_menu',
								'type'       => 'switcher',
								'title'      => esc_html__( 'Vertical Menu Alway Open', 'kuteshop' ),
								'desc'       => esc_html__( 'Vertical menu will be alway open', 'kuteshop' ),
								'dependency' => array(
									'enable_vertical_menu', '==', true,
								),
							),
							array(
								'title'      => esc_html__( 'Vertical Menu Title', 'kuteshop' ),
								'id'         => 'vertical_menu_title',
								'type'       => 'text',
								'default'    => esc_html__( 'CATEGORIES', 'kuteshop' ),
								'dependency' => array(
									'enable_vertical_menu', '==', true,
								),
								'multilang'  => true,
							),
							array(
								'title'      => esc_html__( 'Vertical Menu Button show all text', 'kuteshop' ),
								'id'         => 'vertical_menu_button_all_text',
								'type'       => 'text',
								'default'    => esc_html__( 'All Categories', 'kuteshop' ),
								'dependency' => array(
									'enable_vertical_menu', '==', true,
								),
								'multilang'  => true,
							),
							array(
								'title'      => esc_html__( 'Vertical Menu Button close text', 'kuteshop' ),
								'id'         => 'vertical_menu_button_close_text',
								'type'       => 'text',
								'default'    => esc_html__( 'Close', 'kuteshop' ),
								'dependency' => array(
									'enable_vertical_menu', '==', true,
								),
							),
							array(
								'title'      => esc_html__( 'The number of visible vertical menu items', 'kuteshop' ),
								'desc'       => esc_html__( 'The number of visible vertical menu items', 'kuteshop' ),
								'id'         => 'vertical_item_visible',
								'default'    => 10,
								'type'       => 'number',
								'dependency' => array(
									'enable_vertical_menu', '==', true,
								),
							),
						),
					),
				),
			);
			$options[] = array(
				'name'   => 'footer',
				'title'  => esc_html__( 'Footer Settings', 'kuteshop' ),
				'icon'   => 'fa fa-folder-open-o',
				'fields' => array(
					array(
						'id'      => 'kuteshop_footer_options',
						'type'    => 'select_preview',
						'title'   => esc_html__( 'Select Footer Builder', 'kuteshop' ),
						'options' => self::get_footer_preview(),
						'default' => 'default',
					),
				),
			);
			$options[] = array(
				'name'     => 'blog',
				'title'    => esc_html__( 'Blog Settings', 'kuteshop' ),
				'icon'     => 'fa fa-rss',
				'sections' => array(
					array(
						'name'   => 'blog_page',
						'title'  => esc_html__( 'Blog Page', 'kuteshop' ),
						'fields' => array(
							array(
								'id'      => 'sidebar_blog_layout',
								'type'    => 'image_select',
								'title'   => esc_html__( 'Single Post Sidebar Position', 'kuteshop' ),
								'desc'    => esc_html__( 'Select sidebar position on Blog.', 'kuteshop' ),
								'options' => array(
									'left'  => get_theme_file_uri( 'framework/assets/images/left-sidebar.png' ),
									'right' => get_theme_file_uri( 'framework/assets/images/right-sidebar.png' ),
									'full'  => get_theme_file_uri( 'framework/assets/images/default-sidebar.png' ),
								),
								'default' => 'left',
							),
							array(
								'id'         => 'blog_sidebar',
								'type'       => 'select',
								'title'      => esc_html__( 'Blog Sidebar', 'kuteshop' ),
								'options'    => self::get_sidebar_options(),
								'dependency' => array( 'sidebar_blog_layout_full', '==', false ),
							),
							array(
								'id'    => 'blog_full_content',
								'type'  => 'switcher',
								'title' => esc_html__( 'Show Full Content', 'kuteshop' ),
							),
							array(
								'id'         => 'using_placeholder',
								'type'       => 'switcher',
								'title'      => esc_html__( 'Using Placeholder', 'kuteshop' ),
								'dependency' => array( 'sidebar_blog_layout_full', '==', false ),
							),
						),
					),
					array(
						'name'   => 'single_post',
						'title'  => esc_html__( 'Single Post', 'kuteshop' ),
						'fields' => array(
							array(
								'id'      => 'sidebar_single_post_position',
								'type'    => 'image_select',
								'title'   => esc_html__( 'Single Post Sidebar Position', 'kuteshop' ),
								'desc'    => esc_html__( 'Select sidebar position on Single Post.', 'kuteshop' ),
								'options' => array(
									'left'  => get_theme_file_uri( 'framework/assets/images/left-sidebar.png' ),
									'right' => get_theme_file_uri( 'framework/assets/images/right-sidebar.png' ),
									'full'  => get_theme_file_uri( 'framework/assets/images/default-sidebar.png' ),
								),
								'default' => 'left',
							),
							array(
								'id'         => 'single_post_sidebar',
								'type'       => 'select',
								'title'      => esc_html__( 'Single Post Sidebar', 'kuteshop' ),
								'options'    => self::get_sidebar_options(),
								'default'    => '',
								'dependency' => array( 'sidebar_single_post_position_full', '==', false ),
							),
							array(
								'id'      => 'kuteshop_single_related',
								'type'    => 'switcher',
								'default' => false,
								'title'   => esc_html__( 'Enable Related', 'kuteshop' ),
							),
							array(
								'title'      => esc_html__( 'Related items per row on Desktop', 'kuteshop' ),
								'desc'       => esc_html__( '(Screen resolution of device >= 1500px )', 'kuteshop' ),
								'id'         => 'related_ls_items',
								'type'       => 'select',
								'default'    => '3',
								'options'    => array(
									'1' => esc_html__( '1 item', 'kuteshop' ),
									'2' => esc_html__( '2 items', 'kuteshop' ),
									'3' => esc_html__( '3 items', 'kuteshop' ),
									'4' => esc_html__( '4 items', 'kuteshop' ),
									'5' => esc_html__( '5 items', 'kuteshop' ),
									'6' => esc_html__( '6 items', 'kuteshop' ),
								),
								'dependency' => array( 'kuteshop_single_related', '==', true ),
							),
							array(
								'title'      => esc_html__( 'Related items per row on Desktop', 'kuteshop' ),
								'desc'       => esc_html__( '(Screen resolution of device >= 1200px < 1500px )', 'kuteshop' ),
								'id'         => 'related_lg_items',
								'type'       => 'select',
								'default'    => '3',
								'options'    => array(
									'1' => esc_html__( '1 item', 'kuteshop' ),
									'2' => esc_html__( '2 items', 'kuteshop' ),
									'3' => esc_html__( '3 items', 'kuteshop' ),
									'4' => esc_html__( '4 items', 'kuteshop' ),
									'5' => esc_html__( '5 items', 'kuteshop' ),
									'6' => esc_html__( '6 items', 'kuteshop' ),
								),
								'dependency' => array( 'kuteshop_single_related', '==', true ),
							),
							array(
								'title'      => esc_html__( 'Related items per row on landscape tablet', 'kuteshop' ),
								'desc'       => esc_html__( '(Screen resolution of device >=992px and < 1200px )', 'kuteshop' ),
								'id'         => 'related_md_items',
								'type'       => 'select',
								'default'    => '3',
								'options'    => array(
									'1' => esc_html__( '1 item', 'kuteshop' ),
									'2' => esc_html__( '2 items', 'kuteshop' ),
									'3' => esc_html__( '3 items', 'kuteshop' ),
									'4' => esc_html__( '4 items', 'kuteshop' ),
									'5' => esc_html__( '5 items', 'kuteshop' ),
									'6' => esc_html__( '6 items', 'kuteshop' ),
								),
								'dependency' => array( 'kuteshop_single_related', '==', true ),
							),
							array(
								'title'      => esc_html__( 'Related items per row on portrait tablet', 'kuteshop' ),
								'desc'       => esc_html__( '(Screen resolution of device >=768px and < 992px )', 'kuteshop' ),
								'id'         => 'related_sm_items',
								'type'       => 'select',
								'default'    => '2',
								'options'    => array(
									'1' => esc_html__( '1 item', 'kuteshop' ),
									'2' => esc_html__( '2 items', 'kuteshop' ),
									'3' => esc_html__( '3 items', 'kuteshop' ),
									'4' => esc_html__( '4 items', 'kuteshop' ),
									'5' => esc_html__( '5 items', 'kuteshop' ),
									'6' => esc_html__( '6 items', 'kuteshop' ),
								),
								'dependency' => array( 'kuteshop_single_related', '==', true ),
							),
							array(
								'title'      => esc_html__( 'Related items per row on Mobile', 'kuteshop' ),
								'desc'       => esc_html__( '(Screen resolution of device >=480  add < 768px)', 'kuteshop' ),
								'id'         => 'related_xs_items',
								'type'       => 'select',
								'default'    => '1',
								'options'    => array(
									'1' => esc_html__( '1 item', 'kuteshop' ),
									'2' => esc_html__( '2 items', 'kuteshop' ),
									'3' => esc_html__( '3 items', 'kuteshop' ),
									'4' => esc_html__( '4 items', 'kuteshop' ),
									'5' => esc_html__( '5 items', 'kuteshop' ),
									'6' => esc_html__( '6 items', 'kuteshop' ),
								),
								'dependency' => array( 'kuteshop_single_related', '==', true ),
							),
							array(
								'title'      => esc_html__( 'Related items per row on Mobile', 'kuteshop' ),
								'desc'       => esc_html__( '(Screen resolution of device < 480px)', 'kuteshop' ),
								'id'         => 'related_ts_items',
								'type'       => 'select',
								'default'    => '1',
								'options'    => array(
									'1' => esc_html__( '1 item', 'kuteshop' ),
									'2' => esc_html__( '2 items', 'kuteshop' ),
									'3' => esc_html__( '3 items', 'kuteshop' ),
									'4' => esc_html__( '4 items', 'kuteshop' ),
									'5' => esc_html__( '5 items', 'kuteshop' ),
									'6' => esc_html__( '6 items', 'kuteshop' ),
								),
								'dependency' => array( 'kuteshop_single_related', '==', true ),
							),
						),
					),
				),
			);
			if ( class_exists( 'WooCommerce' ) ) {
				$options[] = array(
					'name'     => 'wooCommerce',
					'title'    => esc_html__( 'WooCommerce', 'kuteshop' ),
					'icon'     => 'fa fa-shopping-bag',
					'sections' => array(
						array(
							'name'   => 'shop_product',
							'title'  => esc_html__( 'Shop Page', 'kuteshop' ),
							'fields' => array(
								array(
									'type'    => 'subheading',
									'content' => esc_html__( 'Shop Settings', 'kuteshop' ),
								),
								array(
									'id'      => 'product_newness',
									'type'    => 'number',
									'title'   => esc_html__( 'Products Newness', 'kuteshop' ),
									'default' => '10',
								),
								array(
									'id'      => 'sidebar_shop_page_position',
									'type'    => 'image_select',
									'title'   => esc_html__( 'Shop Page Sidebar Position', 'kuteshop' ),
									'desc'    => esc_html__( 'Select sidebar position on Shop Page.', 'kuteshop' ),
									'options' => array(
										'left'  => get_theme_file_uri( '/framework/assets/images/left-sidebar.png' ),
										'right' => get_theme_file_uri( '/framework/assets/images/right-sidebar.png' ),
										'full'  => get_theme_file_uri( '/framework/assets/images/default-sidebar.png' ),
									),
									'default' => 'left',
								),
								array(
									'id'         => 'shop_page_sidebar',
									'type'       => 'select',
									'title'      => esc_html__( 'Shop Sidebar', 'kuteshop' ),
									'options'    => self::get_sidebar_options(),
									'dependency' => array( 'sidebar_shop_page_position_full', '==', false ),
								),
								array(
									'id'      => 'shop_page_layout',
									'type'    => 'image_select',
									'title'   => esc_html__( 'Shop Default Layout', 'kuteshop' ),
									'desc'    => esc_html__( 'Select default layout for shop, product category archive.', 'kuteshop' ),
									'options' => array(
										'grid' => get_template_directory_uri() . '/assets/images/grid-display.png',
										'list' => get_template_directory_uri() . '/assets/images/list-display.png',
									),
									'default' => 'grid',
								),
								array(
									'id'      => 'product_per_page',
									'type'    => 'number',
									'title'   => esc_html__( 'Products perpage', 'kuteshop' ),
									'desc'    => esc_html__( 'Number of products on shop page.', 'kuteshop' ),
									'default' => '10',
								),
								array(
									'id'         => 'kuteshop_shop_product_style',
									'type'       => 'select_preview',
									'title'      => esc_html__( 'Product Shop Layout', 'kuteshop' ),
									'desc'       => esc_html__( 'Select a Product layout in shop page', 'kuteshop' ),
									'options'    => self::get_product_options(),
									'default'    => '1',
									'attributes' => array(
										'data-depend-id' => 'kuteshop_shop_product_style',
									),
								),
								array(
									'type'    => 'subheading',
									'content' => esc_html__( 'Image Settings', 'kuteshop' ),
								),
								array(
									'id'      => 'enable_shop_banner',
									'type'    => 'switcher',
									'title'   => esc_html__( 'Shop Banner', 'kuteshop' ),
									'default' => false,
								),
								array(
									'id'         => 'woo_shop_banner',
									'type'       => 'gallery',
									'title'      => esc_html__( 'Shop Banner', 'kuteshop' ),
									'add_title'  => esc_html__( 'Add Banner', 'kuteshop' ),
									'dependency' => array( 'enable_shop_banner', '==', true ),
								),
								array(
									'type'    => 'subheading',
									'content' => esc_html__( 'Carousel Settings', 'kuteshop' ),
								),
								array(
									'title'   => esc_html__( 'Items per row on Desktop( For grid mode )', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_bg_items',
									'type'    => 'select',
									'default' => '4',
									'options' => array(
										'12' => esc_html__( '1 item', 'kuteshop' ),
										'6'  => esc_html__( '2 items', 'kuteshop' ),
										'4'  => esc_html__( '3 items', 'kuteshop' ),
										'3'  => esc_html__( '4 items', 'kuteshop' ),
										'15' => esc_html__( '5 items', 'kuteshop' ),
										'2'  => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Items per row on Desktop( For grid mode )', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1200px < 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_lg_items',
									'type'    => 'select',
									'default' => '4',
									'options' => array(
										'12' => esc_html__( '1 item', 'kuteshop' ),
										'6'  => esc_html__( '2 items', 'kuteshop' ),
										'4'  => esc_html__( '3 items', 'kuteshop' ),
										'3'  => esc_html__( '4 items', 'kuteshop' ),
										'15' => esc_html__( '5 items', 'kuteshop' ),
										'2'  => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Items per row on landscape tablet( For grid mode )', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=992px and < 1200px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_md_items',
									'type'    => 'select',
									'default' => '4',
									'options' => array(
										'12' => esc_html__( '1 item', 'kuteshop' ),
										'6'  => esc_html__( '2 items', 'kuteshop' ),
										'4'  => esc_html__( '3 items', 'kuteshop' ),
										'3'  => esc_html__( '4 items', 'kuteshop' ),
										'15' => esc_html__( '5 items', 'kuteshop' ),
										'2'  => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Items per row on portrait tablet( For grid mode )', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=768px and < 992px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_sm_items',
									'type'    => 'select',
									'default' => '4',
									'options' => array(
										'12' => esc_html__( '1 item', 'kuteshop' ),
										'6'  => esc_html__( '2 items', 'kuteshop' ),
										'4'  => esc_html__( '3 items', 'kuteshop' ),
										'3'  => esc_html__( '4 items', 'kuteshop' ),
										'15' => esc_html__( '5 items', 'kuteshop' ),
										'2'  => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Items per row on Mobile( For grid mode )', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=480  add < 768px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_xs_items',
									'type'    => 'select',
									'default' => '6',
									'options' => array(
										'12' => esc_html__( '1 item', 'kuteshop' ),
										'6'  => esc_html__( '2 items', 'kuteshop' ),
										'4'  => esc_html__( '3 items', 'kuteshop' ),
										'3'  => esc_html__( '4 items', 'kuteshop' ),
										'15' => esc_html__( '5 items', 'kuteshop' ),
										'2'  => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Items per row on Mobile( For grid mode )', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device < 480px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_ts_items',
									'type'    => 'select',
									'default' => '12',
									'options' => array(
										'12' => esc_html__( '1 item', 'kuteshop' ),
										'6'  => esc_html__( '2 items', 'kuteshop' ),
										'4'  => esc_html__( '3 items', 'kuteshop' ),
										'3'  => esc_html__( '4 items', 'kuteshop' ),
										'15' => esc_html__( '5 items', 'kuteshop' ),
										'2'  => esc_html__( '6 items', 'kuteshop' ),
									),
								),
							),
						),
						array(
							'name'   => 'single_product',
							'title'  => esc_html__( 'Single product', 'kuteshop' ),
							'fields' => array(
								array(
									'id'         => 'sidebar_product_position',
									'type'       => 'image_select',
									'title'      => esc_html__( 'Single Product Sidebar Position', 'kuteshop' ),
									'desc'       => esc_html__( 'Select sidebar position on single product page.', 'kuteshop' ),
									'options'    => array(
										'left'  => get_theme_file_uri( 'framework/assets/images/left-sidebar.png' ),
										'right' => get_theme_file_uri( 'framework/assets/images/right-sidebar.png' ),
										'full'  => get_theme_file_uri( 'framework/assets/images/default-sidebar.png' ),
									),
									'default'    => 'left',
									'attributes' => array(
										'data-depend-id' => 'sidebar_product_position',
									),
								),
								array(
									'id'         => 'single_product_sidebar',
									'type'       => 'select',
									'title'      => esc_html__( 'Single Product Sidebar', 'kuteshop' ),
									'options'    => self::get_sidebar_options(),
									'default'    => '',
									'dependency' => array( 'sidebar_product_position', '!=', 'full' ),
								),
								array(
									'id'    => 'enable_share_product',
									'type'  => 'switcher',
									'title' => esc_html__( 'Enable Product Share', 'kuteshop' ),
								),
							),
						),
						array(
							'name'   => 'cross_sell',
							'title'  => esc_html__( 'Cross sell', 'kuteshop' ),
							'fields' => array(
								array(
									'title'   => esc_html__( 'Cross sell title', 'kuteshop' ),
									'id'      => 'kuteshop_woo_crosssell_products_title',
									'type'    => 'text',
									'default' => esc_html__( 'You may be interested in...', 'kuteshop' ),
									'desc'    => esc_html__( 'Cross sell title', 'kuteshop' ),
								),
								array(
									'title'   => esc_html__( 'Cross sell items per row on Desktop', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_crosssell_ls_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Cross sell items per row on Desktop', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1200px < 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_crosssell_lg_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Cross sell items per row on landscape tablet', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=992px and < 1200px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_crosssell_md_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Cross sell items per row on portrait tablet', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=768px and < 992px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_crosssell_sm_items',
									'type'    => 'select',
									'default' => '2',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Cross sell items per row on Mobile', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=480  add < 768px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_crosssell_xs_items',
									'type'    => 'select',
									'default' => '1',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Cross sell items per row on Mobile', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device < 480px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_crosssell_ts_items',
									'type'    => 'select',
									'default' => '1',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
							),
						),
						array(
							'name'   => 'related_product',
							'title'  => esc_html__( 'Related Products', 'kuteshop' ),
							'fields' => array(
								array(
									'title'   => esc_html__( 'Related products title', 'kuteshop' ),
									'id'      => 'kuteshop_woo_related_products_title',
									'type'    => 'text',
									'default' => esc_html__( 'Related Products', 'kuteshop' ),
									'desc'    => esc_html__( 'Related products title', 'kuteshop' ),
								),
								array(
									'title'   => esc_html__( 'Related products items per row on Desktop', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_related_ls_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Related products items per row on Desktop', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1200px < 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_related_lg_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Related products items per row on landscape tablet', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=992px and < 1200px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_related_md_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Related product items per row on portrait tablet', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=768px and < 992px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_related_sm_items',
									'type'    => 'select',
									'default' => '2',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Related products items per row on Mobile', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=480  add < 768px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_related_xs_items',
									'type'    => 'select',
									'default' => '1',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Related products items per row on Mobile', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device < 480px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_related_ts_items',
									'type'    => 'select',
									'default' => '1',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
							),
						),
						array(
							'name'   => 'upsells_product',
							'title'  => esc_html__( 'Up sells Products', 'kuteshop' ),
							'fields' => array(
								array(
									'title'   => esc_html__( 'Up sells title', 'kuteshop' ),
									'id'      => 'kuteshop_woo_upsell_products_title',
									'type'    => 'text',
									'default' => esc_html__( 'You may also like&hellip;', 'kuteshop' ),
									'desc'    => esc_html__( 'Up sells products title', 'kuteshop' ),
								),
								array(
									'title'   => esc_html__( 'Up sells items per row on Desktop', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_upsell_ls_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Up sells items per row on Desktop', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >= 1200px < 1500px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_upsell_lg_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Up sells items per row on landscape tablet', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=992px and < 1200px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_upsell_md_items',
									'type'    => 'select',
									'default' => '3',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Up sells items per row on portrait tablet', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=768px and < 992px )', 'kuteshop' ),
									'id'      => 'kuteshop_woo_upsell_sm_items',
									'type'    => 'select',
									'default' => '2',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Up sells items per row on Mobile', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device >=480  add < 768px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_upsell_xs_items',
									'type'    => 'select',
									'default' => '2',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
								array(
									'title'   => esc_html__( 'Up sells items per row on Mobile', 'kuteshop' ),
									'desc'    => esc_html__( '(Screen resolution of device < 480px)', 'kuteshop' ),
									'id'      => 'kuteshop_woo_upsell_ts_items',
									'type'    => 'select',
									'default' => '1',
									'options' => array(
										'1' => esc_html__( '1 item', 'kuteshop' ),
										'2' => esc_html__( '2 items', 'kuteshop' ),
										'3' => esc_html__( '3 items', 'kuteshop' ),
										'4' => esc_html__( '4 items', 'kuteshop' ),
										'5' => esc_html__( '5 items', 'kuteshop' ),
										'6' => esc_html__( '6 items', 'kuteshop' ),
									),
								),
							),
						),
					),
				);
			}
			$options[] = array(
				'name'   => 'social_settings',
				'title'  => esc_html__( 'Social Settings', 'kuteshop' ),
				'icon'   => 'fa fa-users',
				'fields' => array(
					array(
						'type'    => 'subheading',
						'content' => esc_html__( 'Social User', 'kuteshop' ),
					),
					array(
						'id'              => 'user_all_social',
						'type'            => 'group',
						'title'           => esc_html__( 'Social', 'kuteshop' ),
						'button_title'    => esc_html__( 'Add New Social', 'kuteshop' ),
						'accordion_title' => esc_html__( 'Social Settings', 'kuteshop' ),
						'fields'          => array(
							array(
								'id'      => 'title_social',
								'type'    => 'text',
								'title'   => esc_html__( 'Title Social', 'kuteshop' ),
								'default' => 'Facebook',
							),
							array(
								'id'      => 'link_social',
								'type'    => 'text',
								'title'   => esc_html__( 'Link Social', 'kuteshop' ),
								'default' => 'https://facebook.com',
							),
							array(
								'id'      => 'icon_social',
								'type'    => 'icon',
								'title'   => esc_html__( 'Icon Social', 'kuteshop' ),
								'default' => 'fa fa-facebook',
							),
						),
					),
				),
			);
			$options[] = array(
				'name'   => 'typography',
				'title'  => esc_html__( 'Typography Options', 'kuteshop' ),
				'icon'   => 'fa fa-font',
				'fields' => array(
					array(
						'id'      => 'typography_font_family',
						'type'    => 'typography',
						'title'   => esc_html__( 'Font Family', 'kuteshop' ),
						'default' => array(
							'family' => 'Arial',
						),
						'variant' => false,
						'chosen'  => false,
					),
					array(
						'id'      => 'typography_font_size',
						'type'    => 'number',
						'title'   => esc_html__( 'Font Size', 'kuteshop' ),
						'default' => 14,
					),
					array(
						'id'      => 'typography_line_height',
						'type'    => 'number',
						'title'   => esc_html__( 'Line Height', 'kuteshop' ),
						'default' => 24,
					),
				),
			);
			$options[] = array(
				'name'   => 'backup_option',
				'title'  => esc_html__( 'Backup Options', 'kuteshop' ),
				'icon'   => 'fa fa-bold',
				'fields' => array(
					array(
						'type'  => 'backup',
						'title' => esc_html__( 'Backup Field', 'kuteshop' ),
					),
				),
			);

			return $options;
		}

		function metabox_options( $options )
		{
			$options = array();
			// -----------------------------------------
			// Page Meta box Options                   -
			// -----------------------------------------
			$options[] = array(
				'id'        => '_custom_metabox_theme_options',
				'title'     => esc_html__( 'Custom Theme Options', 'kuteshop' ),
				'post_type' => 'page',
				'context'   => 'normal',
				'priority'  => 'high',
				'sections'  => array(
					'banner' => array(
						'name'   => 'page_banner_settings',
						'title'  => esc_html__( 'Banner Settings', 'kuteshop' ),
						'icon'   => 'fa fa-picture-o',
						'fields' => array(
							array(
								'id'         => 'kuteshop_metabox_enable_banner',
								'type'       => 'switcher',
								'title'      => esc_html__( 'Enable Banner', 'kuteshop' ),
								'default'    => false,
								'attributes' => array(
									'data-depend-id' => 'kuteshop_metabox_enable_banner',
								),
							),
							array(
								'id'      => 'bg_banner_page',
								'type'    => 'background',
								'title'   => esc_html__( 'Background Banner', 'kuteshop' ),
								'default' => array(
									'image'      => '',
									'repeat'     => 'repeat-x',
									'position'   => 'center center',
									'attachment' => 'fixed',
									'size'       => 'cover',
									'color'      => '#d4bd52',
								),
							),
							array(
								'id'      => 'height_banner',
								'type'    => 'number',
								'title'   => esc_html__( 'Height Banner', 'kuteshop' ),
								'default' => '400',
							),
							array(
								'id'      => 'page_margin_top',
								'type'    => 'number',
								'title'   => esc_html__( 'Margin Top', 'kuteshop' ),
								'default' => 0,
							),
							array(
								'id'      => 'page_margin_bottom',
								'type'    => 'number',
								'title'   => esc_html__( 'Margin Bottom', 'kuteshop' ),
								'default' => 0,
							),
						),
					),
					array(
						'name'   => 'page_theme_options',
						'title'  => esc_html__( 'Theme Options', 'kuteshop' ),
						'icon'   => 'fa fa-wordpress',
						'fields' => array(
							array(
								'id'    => 'metabox_kuteshop_logo',
								'type'  => 'image',
								'title' => esc_html__( 'Logo', 'kuteshop' ),
							),
							array(
								'id'      => 'metabox_kuteshop_main_color',
								'type'    => 'color_picker',
								'title'   => esc_html__( 'Main Color', 'kuteshop' ),
								'default' => '#ff3366',
								'rgba'    => true,
							),
						),
					),
					array(
						'name'   => 'vertical_theme_options',
						'title'  => esc_html__( 'Vertical Menu Settings', 'kuteshop' ),
						'icon'   => 'fa fa-bar-chart',
						'fields' => array(
							array(
								'id'         => 'metabox_enable_vertical_menu',
								'type'       => 'switcher',
								'attributes' => array(
									'data-depend-id' => 'metabox_enable_vertical_menu',
								),
								'default'    => false,
								'title'      => esc_html__( 'Vertical Menu', 'kuteshop' ),
							),
							array(
								'id'         => 'metabox_block_vertical_menu',
								'type'       => 'switcher',
								'title'      => esc_html__( 'Vertical Menu Alway Open', 'kuteshop' ),
								'desc'       => esc_html__( 'Vertical menu will be alway open', 'kuteshop' ),
								'dependency' => array(
									'metabox_enable_vertical_menu', '==', true,
								),
							),
							array(
								'title'      => esc_html__( 'Vertical Menu Title', 'kuteshop' ),
								'id'         => 'metabox_vertical_menu_title',
								'type'       => 'text',
								'default'    => esc_html__( 'CATEGORIES', 'kuteshop' ),
								'dependency' => array(
									'metabox_enable_vertical_menu', '==', true,
								),
							),
							array(
								'title'      => esc_html__( 'Vertical Menu Button show all text', 'kuteshop' ),
								'id'         => 'metabox_vertical_menu_button_all_text',
								'type'       => 'text',
								'default'    => esc_html__( 'All Categories', 'kuteshop' ),
								'dependency' => array(
									'metabox_enable_vertical_menu', '==', true,
								),
							),
							array(
								'title'      => esc_html__( 'Vertical Menu Button close text', 'kuteshop' ),
								'id'         => 'metabox_vertical_menu_button_close_text',
								'type'       => 'text',
								'default'    => esc_html__( 'Close', 'kuteshop' ),
								'dependency' => array(
									'metabox_enable_vertical_menu', '==', true,
								),
							),
							array(
								'title'      => esc_html__( 'The number of visible vertical menu items', 'kuteshop' ),
								'desc'       => esc_html__( 'The number of visible vertical menu items', 'kuteshop' ),
								'id'         => 'metabox_vertical_item_visible',
								'default'    => 10,
								'type'       => 'number',
								'dependency' => array(
									'metabox_enable_vertical_menu', '==', true,
								),
							),
						),
					),
					array(
						'name'   => 'header_theme_options',
						'title'  => esc_html__( 'Header Settings', 'kuteshop' ),
						'icon'   => 'fa fa-folder-open-o',
						'fields' => array(
							array(
								'id'         => 'kuteshop_metabox_used_header',
								'type'       => 'select_preview',
								'title'      => esc_html__( 'Header Layout', 'kuteshop' ),
								'desc'       => esc_html__( 'Select a header layout', 'kuteshop' ),
								'options'    => self::get_header_options(),
								'default'    => 'style-01',
								'attributes' => array(
									'data-depend-id' => 'kuteshop_metabox_used_header',
								),
							),
							array(
								'id'         => 'metabox_header_text_box',
								'type'       => 'text',
								'title'      => esc_html__( 'Header Text Box', 'kuteshop' ),
								'dependency' => array(
									'kuteshop_metabox_used_header', '==', 'style-03',
								),
							),
							array(
								'id'              => 'metabox_header_service_box',
								'type'            => 'group',
								'title'           => esc_html__( 'Header Service', 'kuteshop' ),
								'button_title'    => esc_html__( 'Add New', 'kuteshop' ),
								'accordion_title' => esc_html__( 'Header Service Settings', 'kuteshop' ),
								'dependency'      => array(
									'kuteshop_metabox_used_header', '==', 'style-07',
								),
								'fields'          => array(
									array(
										'id'    => 'service_box_image',
										'type'  => 'image',
										'title' => esc_html__( 'Image', 'kuteshop' ),
									),
									array(
										'id'    => 'service_box_text',
										'type'  => 'text',
										'title' => esc_html__( 'Text', 'kuteshop' ),
									),
								),
							),
							array(
								'id'         => 'metabox_header_phone',
								'type'       => 'text',
								'title'      => esc_html__( 'Header Phone Number', 'kuteshop' ),
								'dependency' => array(
									'kuteshop_metabox_used_header', '==', 'style-11',
								),
							),
						),
					),
					array(
						'name'   => 'footer_theme_options',
						'title'  => esc_html__( 'Footer Settings', 'kuteshop' ),
						'icon'   => 'fa fa-folder-open-o',
						'fields' => array(
							array(
								'id'      => 'kuteshop_metabox_footer_options',
								'type'    => 'select_preview',
								'title'   => esc_html__( 'Select Footer Builder', 'kuteshop' ),
								'options' => self::get_footer_preview(),
							),
						),
					),
				),
			);
			// -----------------------------------------
			// Page Footer Meta box Options            -
			// -----------------------------------------
			$options[] = array(
				'id'        => '_custom_footer_options',
				'title'     => esc_html__( 'Custom Footer Options', 'kuteshop' ),
				'post_type' => 'footer',
				'context'   => 'normal',
				'priority'  => 'high',
				'sections'  => array(
					array(
						'name'   => esc_html__( 'FOOTER STYLE', 'kuteshop' ),
						'fields' => array(
							array(
								'id'       => 'kuteshop_footer_style',
								'type'     => 'select_preview',
								'title'    => esc_html__( 'Footer Style', 'kuteshop' ),
								'subtitle' => esc_html__( 'Select a Footer Style', 'kuteshop' ),
								'options'  => self::get_footer_options(),
								'default'  => 'style-01',
							),
						),
					),
				),
			);
			// -----------------------------------------
			// Page Side Meta box Options              -
			// -----------------------------------------
			$options[] = array(
				'id'        => '_custom_page_side_options',
				'title'     => esc_html__( 'Custom Page Side Options', 'kuteshop' ),
				'post_type' => 'page',
				'context'   => 'side',
				'priority'  => 'default',
				'sections'  => array(
					array(
						'name'   => 'page_option',
						'fields' => array(
							array(
								'id'      => 'sidebar_page_layout',
								'type'    => 'image_select',
								'title'   => esc_html__( 'Single Post Sidebar Position', 'kuteshop' ),
								'desc'    => esc_html__( 'Select sidebar position on Page.', 'kuteshop' ),
								'options' => array(
									'left'  => get_theme_file_uri( 'framework/assets/images/left-sidebar.png' ),
									'right' => get_theme_file_uri( 'framework/assets/images/right-sidebar.png' ),
									'full'  => get_theme_file_uri( 'framework/assets/images/default-sidebar.png' ),
								),
								'default' => 'left',
							),
							array(
								'id'         => 'page_sidebar',
								'type'       => 'select',
								'title'      => esc_html__( 'Page Sidebar', 'kuteshop' ),
								'options'    => self::get_sidebar_options(),
								'default'    => 'blue',
								'dependency' => array( 'sidebar_page_layout_full', '==', false ),
							),
							array(
								'id'    => 'page_extra_class',
								'type'  => 'text',
								'title' => esc_html__( 'Extra Class', 'kuteshop' ),
							),
						),
					),
				),
			);
			// -----------------------------------------
			// Page Testimonials Meta box Options      -
			// -----------------------------------------
			$options[] = array(
				'id'        => '_custom_testimonial_options',
				'title'     => esc_html__( 'Custom Testimonial Options', 'kuteshop' ),
				'post_type' => 'testimonial',
				'context'   => 'normal',
				'priority'  => 'high',
				'sections'  => array(
					array(
						'name'   => 'testimonial_info',
						'fields' => array(
							array(
								'id'        => 'avatar_testimonial',
								'type'      => 'image',
								'title'     => esc_html__( 'Avatar', 'kuteshop' ),
								'add_title' => esc_html__( 'Add Avatar', 'kuteshop' ),
							),
							array(
								'id'    => 'name_testimonial',
								'type'  => 'text',
								'title' => esc_html__( 'Name', 'kuteshop' ),
							),
							array(
								'id'    => 'position_testimonial',
								'type'  => 'text',
								'title' => esc_html__( 'Position', 'kuteshop' ),
							),
						),
					),
				),
			);

			return $options;
		}

		function taxonomy_options( $options )
		{
			$options = array();
			// -----------------------------------------
			// Taxonomy Options                        -
			// -----------------------------------------
			$options[] = array(
				'id'       => '_custom_taxonomy_options',
				'taxonomy' => 'product_cat', // category, post_tag or your custom taxonomy name
				'fields'   => array(
					array(
						'id'      => 'icon_taxonomy',
						'type'    => 'icon',
						'title'   => esc_html__( 'Icon Taxonomy', 'kuteshop' ),
						'default' => '',
					),
				),
			);

			return $options;
		}
	}

	new Kuteshop_ThemeOption();
}
