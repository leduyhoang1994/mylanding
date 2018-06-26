<?php
if ( !class_exists( 'Kuteshop_Visual_Composer' ) ) {
	class Kuteshop_Visual_Composer
	{
		public $data_responsive;

		public function __construct()
		{
			$this->define_constants();
			add_filter( 'generate_carousel_data_attributes', array( $this, 'generate_carousel_data_attributes' ), 10, 2 );
			add_filter( 'vc_iconpicker-type-kuteshopcustomfonts', array( $this, 'iconpicker_type_kuteshopcustomfonts' ) );
			add_filter( 'getProducts', array( $this, 'getProducts' ), 10, 3 );
			add_action( 'vc_after_mapping', array( $this, 'autocomplete' ) );
			add_action( 'vc_after_mapping', array( $this, 'params' ) );
			$this->map_shortcode();
		}

		/**
		 * Define  Constants.
		 */
		private function define_constants()
		{
			$this->define( 'KUTESHOP_SHORTCODE_PREVIEW', get_theme_file_uri( '/framework/assets/images/shortcode-previews/' ) );
			$this->define( 'KUTESHOP_PRODUCT_STYLE_PREVIEW', get_theme_file_uri( '/woo-templates/product-styles/' ) );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value )
		{
			if ( !defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * load param autocomplete render
		 * */
		public function autocomplete()
		{
			add_filter( 'vc_autocomplete_kuteshop_blog_post_ids_callback', array( $this, 'vc_include_field_search' ), 10, 1 );
			add_filter( 'vc_autocomplete_kuteshop_blog_post_ids_render', 'vc_include_field_render', 10, 1 );
			add_filter( 'vc_autocomplete_kuteshop_products_ids_callback', array( $this, 'productIdAutocompleteSuggester' ), 10, 1 );
			add_filter( 'vc_autocomplete_kuteshop_products_ids_render', array( $this, 'productIdAutocompleteRender' ), 10, 1 );
			add_filter( 'vc_autocomplete_kuteshop_banner_ids_callback', array( $this, 'productIdAutocompleteSuggester' ), 10, 1 );
			add_filter( 'vc_autocomplete_kuteshop_banner_ids_render', array( $this, 'productIdAutocompleteRender' ), 10, 1 );
		}

		function params()
		{
			if ( function_exists( 'kuteshop_toolkit_vc_param' ) ) {
				kuteshop_toolkit_vc_param( 'taxonomy', array( $this, 'taxonomy_field' ) );
				kuteshop_toolkit_vc_param( 'select_preview', array( $this, 'select_preview_field' ) );
				kuteshop_toolkit_vc_param( 'number', array( $this, 'number_field' ) );
				kuteshop_toolkit_vc_param( 'datepicker', array( $this, 'datepicker_field' ) );
			}
		}

		/**
		 * datepicker_field
		 **/
		function datepicker_field( $settings, $value )
		{
			$dependency = '';
			$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
			$type       = isset( $settings['type '] ) ? $settings['type'] : '';
			$suffix     = isset( $settings['suffix'] ) ? $settings['suffix'] : '';
			$class      = isset( $settings['class'] ) ? $settings['class'] : '';
			if ( !$value && isset( $settings['std'] ) ) {
				$value = $settings['std'];
			}
			$main_class = $param_name . ' ' . $type . ' ' . $class;
			ob_start();
			?>
            <label class="cs-field-date" <?php echo esc_attr( $dependency ); ?>>
                <input name="<?php echo esc_attr( $param_name ); ?>" value="<?php echo esc_attr( $value ); ?>"
                       type="text"
                       class="wpb_vc_param_value textfield <?php echo esc_attr( $main_class ); ?>"
                       style="min-width:100%; margin-right: 10px;"><?php echo esc_html( $suffix ); ?>
                <textarea class="cs-datepicker-options hidden">{"dateFormat":"m\/d\/yy"}</textarea>
            </label>
			<?php
			return $output = ob_get_clean();
		}

		/**
		 * taxonomy_field
		 */
		public function taxonomy_field( $settings, $value )
		{
			$dependency = '';
			$value_arr  = $value;
			if ( !is_array( $value_arr ) ) {
				$value_arr = array_map( 'trim', explode( ',', $value_arr ) );
			}
			$output = '';
			if ( isset( $settings['settings']['hide_empty'] ) && $settings['settings']['hide_empty'] == true ) {
				$settings['settings']['hide_empty'] = 1;
			} else {
				$settings['settings']['hide_empty'] = 0;
			}
			if ( !empty( $settings['settings']['taxonomy'] ) ) {
				$terms_fields = array();
				if ( isset( $settings['settings']['placeholder'] ) && $settings['settings']['placeholder'] ) {
					$terms_fields[] = "<option value=''>" . $settings['settings']['placeholder'] . "</option>";
				}
				$terms = get_terms( $settings['settings']['taxonomy'],
					array(
						'hierarchical' => 1,
						'hide_empty'   => $settings['settings']['hide_empty'],
					)
				);
				if ( $terms && !is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$selected       = ( in_array( $term->slug, $value_arr ) ) ? ' selected="selected"' : '';
						$terms_fields[] = "<option value='{$term->slug}' {$selected}>{$term->name}</option>";
					}
				}
				$size     = ( !empty( $settings['settings']['size'] ) ) ? 'size="' . $settings['settings']['size'] . '"' : '';
				$multiple = ( !empty( $settings['settings']['multiple'] ) ) ? 'multiple="multiple"' : '';
				$uniqeID  = uniqid();
				$output   = '<select style="width:100%;" id="vc_taxonomy-' . $uniqeID . '" ' . $multiple . ' ' . $size . ' name="' . $settings['param_name'] . '" class="kuteshop_vc_taxonomy wpb_vc_param_value wpb-input wpb-select ' . $settings['param_name'] . ' ' . $settings['type'] . '_field" ' . $dependency . '>'
					. implode( $terms_fields )
					. '</select>';
			}

			return $output;
		}

		/**
		 * Suggester for autocomplete by id/name/title/sku
		 * @since 4.4
		 *
		 * @param $query
		 *
		 * @return array - id's from products with title/sku.
		 */
		public function productIdAutocompleteSuggester( $query )
		{
			global $wpdb;
			$product_id      = (int)$query;
			$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.ID AS id, a.post_title AS title, b.meta_value AS sku
					FROM {$wpdb->posts} AS a
					LEFT JOIN ( SELECT meta_value, post_id  FROM {$wpdb->postmeta} WHERE `meta_key` = '_sku' ) AS b ON b.post_id = a.ID
					WHERE a.post_type = 'product' AND ( a.ID = '%d' OR b.meta_value LIKE '%%%s%%' OR a.post_title LIKE '%%%s%%' )", $product_id > 0 ? $product_id : -1, stripslashes( $query ), stripslashes( $query )
			), ARRAY_A
			);
			$results         = array();
			if ( is_array( $post_meta_infos ) && !empty( $post_meta_infos ) ) {
				foreach ( $post_meta_infos as $value ) {
					$data          = array();
					$data['value'] = $value['id'];
					$data['label'] = esc_html__( 'Id', 'kuteshop' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . esc_html__( 'Title', 'kuteshop' ) . ': ' . $value['title'] : '' ) . ( ( strlen( $value['sku'] ) > 0 ) ? ' - ' . esc_html__( 'Sku', 'kuteshop' ) . ': ' . $value['sku'] : '' );
					$results[]     = $data;
				}
			}

			return $results;
		}

		/**
		 * Find product by id
		 * @since 4.4
		 *
		 * @param $query
		 *
		 * @return bool|array
		 */
		public function productIdAutocompleteRender( $query )
		{
			$query = trim( $query['value'] ); // get value from requested
			if ( !empty( $query ) ) {
				// get product
				$product_object = wc_get_product( (int)$query );
				if ( is_object( $product_object ) ) {
					$product_sku         = $product_object->get_sku();
					$product_title       = $product_object->get_title();
					$product_id          = $product_object->get_id();
					$product_sku_display = '';
					if ( !empty( $product_sku ) ) {
						$product_sku_display = ' - ' . esc_html__( 'Sku', 'kuteshop' ) . ': ' . $product_sku;
					}
					$product_title_display = '';
					if ( !empty( $product_title ) ) {
						$product_title_display = ' - ' . esc_html__( 'Title', 'kuteshop' ) . ': ' . $product_title;
					}
					$product_id_display = esc_html__( 'Id', 'kuteshop' ) . ': ' . $product_id;
					$data               = array();
					$data['value']      = $product_id;
					$data['label']      = $product_id_display . $product_title_display . $product_sku_display;

					return !empty( $data ) ? $data : false;
				}

				return false;
			}

			return false;
		}

		function vc_include_field_search( $search_string )
		{
			$query                           = $search_string;
			$data                            = array();
			$args                            = array(
				's'         => $query,
				'post_type' => 'post',
			);
			$args['vc_search_by_title_only'] = true;
			$args['numberposts']             = -1;
			if ( 0 === strlen( $args['s'] ) ) {
				unset( $args['s'] );
			}
			add_filter( 'posts_search', 'vc_search_by_title_only', 500, 2 );
			$posts = get_posts( $args );
			if ( is_array( $posts ) && !empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$data[] = array(
						'value' => $post->ID,
						'label' => $post->post_title,
						'group' => $post->post_type,
					);
				}
			}

			return $data;
		}

		public function number_field( $settings, $value )
		{
			$dependency = '';
			$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
			$type       = isset( $settings['type '] ) ? $settings['type'] : '';
			$min        = isset( $settings['min'] ) ? $settings['min'] : '';
			$max        = isset( $settings['max'] ) ? $settings['max'] : '';
			$suffix     = isset( $settings['suffix'] ) ? $settings['suffix'] : '';
			$class      = isset( $settings['class'] ) ? $settings['class'] : '';
			if ( !$value && isset( $settings['std'] ) ) {
				$value = $settings['std'];
			}
			$output = '<input type="number" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" class="wpb_vc_param_value textfield ' . $param_name . ' ' . $type . ' ' . $class . '" name="' . $param_name . '" value="' . esc_attr( $value ) . '" ' . $dependency . ' style="max-width:100px; margin-right: 10px;" />' . $suffix;

			return $output;
		}

		public function select_preview_field( $settings, $value )
		{
			ob_start();
			// Get menus list
			$options = $settings['value'];
			$default = $settings['default'];
			if ( is_array( $options ) && count( $options ) > 0 ) {
				$uniqeID = uniqid();
				?>
                <div class="container-select_preview">
                    <label for="<?php echo esc_attr( $settings['param_name'] ); ?>">
                        <select id="kuteshop_select_preview-<?php echo esc_attr( $uniqeID ); ?>"
                                name="<?php echo esc_attr( $settings['param_name'] ); ?>"
                                class="cs-select-images vc_select_image wpb_vc_param_value wpb-input wpb-select <?php echo esc_attr( $settings['param_name'] ); ?> <?php echo esc_attr( $settings['type'] ); ?>_field">
							<?php foreach ( $options as $k => $option ): ?>
								<?php $selected = ( $k == $value ) ? ' selected="selected"' : ''; ?>
                                <option data-preview="<?php echo esc_url( $option['img'] ); ?>"
                                        value='<?php echo esc_attr( $k ) ?>' <?php echo esc_attr( $selected ) ?>><?php echo esc_attr( $option['alt'] ) ?></option>
							<?php endforeach; ?>
                        </select>
                    </label>
                    <div class="image-preview">
						<?php if ( isset( $options[$value] ) && $options[$value] && ( isset( $options[$value]['img'] ) ) ): ?>
                            <img style="margin-top: 10px; max-width: 100%;height: auto;"
                                 src="<?php echo esc_url( $options[$value]['img'] ); ?>" alt="kuteshop">
						<?php else: ?>
                            <img style="margin-top: 10px; max-width: 100%;height: auto;"
                                 src="<?php echo esc_url( $options[$default]['img'] ); ?>" alt="kuteshop">
						<?php endif; ?>
                    </div>
                </div>
				<?php
			}

			return ob_get_clean();
		}

		/* Custom Font icon*/
		function iconpicker_type_kuteshopcustomfonts( $icons )
		{
			$icons['Flaticon'] = array(
				array( 'flaticon-clock' => '01' ),
				array( 'flaticon-plane' => '02' ),
				array( 'flaticon-time' => '03' ),
				array( 'flaticon-phone' => '04' ),
				array( 'flaticon-umbrela' => '05' ),
				array( 'flaticon-coin' => '06' ),
				array( 'flaticon-truck' => '07' ),
				array( 'flaticon-payment' => '08' ),
				array( 'flaticon-security' => '09' ),
				array( 'flaticon-support' => '10' ),
				array( 'flaticon-android' => '11' ),
				array( 'flaticon-tab-fashion' => '12' ),
				array( 'flaticon-tab-bike' => '13' ),
				array( 'flaticon-tab-tv' => '14' ),
				array( 'flaticon-tab-cam' => '15' ),
				array( 'flaticon-tab-funi' => '16' ),
				array( 'flaticon-tab-ring' => '17' ),
				array( 'flaticon-tab-ring2' => '18' ),
				array( 'flaticon-tab-ball' => '19' ),
				array( 'flaticon-tab-tech' => '20' ),
				array( 'flaticon-tab-fashion2' => '21' ),
				array( 'flaticon-tab-phone' => '22' ),
				array( 'flaticon-icon01' => '23' ),
				array( 'flaticon-icon02' => '24' ),
				array( 'flaticon-icon03' => '25' ),
				array( 'flaticon-icon04' => '26' ),
			);

			return $icons;
		}

		function bootstrap_settings( $dependency, $value_dependency )
		{
			$data_value     = array();
			$data_bootstrap = array(
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Rows space', 'kuteshop' ),
					'param_name' => 'boostrap_rows_space',
					'value'      => array(
						esc_html__( 'Default', 'kuteshop' ) => 'rows-space-0',
						esc_html__( '10px', 'kuteshop' )    => 'rows-space-10',
						esc_html__( '20px', 'kuteshop' )    => 'rows-space-20',
						esc_html__( '30px', 'kuteshop' )    => 'rows-space-30',
						esc_html__( '40px', 'kuteshop' )    => 'rows-space-40',
						esc_html__( '50px', 'kuteshop' )    => 'rows-space-50',
						esc_html__( '60px', 'kuteshop' )    => 'rows-space-60',
						esc_html__( '70px', 'kuteshop' )    => 'rows-space-70',
						esc_html__( '80px', 'kuteshop' )    => 'rows-space-80',
						esc_html__( '90px', 'kuteshop' )    => 'rows-space-90',
						esc_html__( '100px', 'kuteshop' )   => 'rows-space-100',
					),
					'std'        => 'rows-space-0',
					'group'      => esc_html__( 'Boostrap settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Items per row on Desktop', 'kuteshop' ),
					'param_name'  => 'boostrap_bg_items',
					'value'       => array(
						esc_html__( '1 item', 'kuteshop' )  => '12',
						esc_html__( '2 items', 'kuteshop' ) => '6',
						esc_html__( '3 items', 'kuteshop' ) => '4',
						esc_html__( '4 items', 'kuteshop' ) => '3',
						esc_html__( '5 items', 'kuteshop' ) => '15',
						esc_html__( '6 items', 'kuteshop' ) => '2',
					),
					'description' => esc_html__( '(Item per row on screen resolution of device >= 1500px )', 'kuteshop' ),
					'group'       => esc_html__( 'Boostrap settings', 'kuteshop' ),
					'std'         => '12',
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Items per row on Desktop', 'kuteshop' ),
					'param_name'  => 'boostrap_lg_items',
					'value'       => array(
						esc_html__( '1 item', 'kuteshop' )  => '12',
						esc_html__( '2 items', 'kuteshop' ) => '6',
						esc_html__( '3 items', 'kuteshop' ) => '4',
						esc_html__( '4 items', 'kuteshop' ) => '3',
						esc_html__( '5 items', 'kuteshop' ) => '15',
						esc_html__( '6 items', 'kuteshop' ) => '2',
					),
					'description' => esc_html__( '(Item per row on screen resolution of device >= 1200px and < 1500px )', 'kuteshop' ),
					'group'       => esc_html__( 'Boostrap settings', 'kuteshop' ),
					'std'         => '12',
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Items per row on landscape tablet', 'kuteshop' ),
					'param_name'  => 'boostrap_md_items',
					'value'       => array(
						esc_html__( '1 item', 'kuteshop' )  => '12',
						esc_html__( '2 items', 'kuteshop' ) => '6',
						esc_html__( '3 items', 'kuteshop' ) => '4',
						esc_html__( '4 items', 'kuteshop' ) => '3',
						esc_html__( '5 items', 'kuteshop' ) => '15',
						esc_html__( '6 items', 'kuteshop' ) => '2',
					),
					'description' => esc_html__( '(Item per row on screen resolution of device >=992px and < 1200px )', 'kuteshop' ),
					'group'       => esc_html__( 'Boostrap settings', 'kuteshop' ),
					'std'         => '12',
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Items per row on portrait tablet', 'kuteshop' ),
					'param_name'  => 'boostrap_sm_items',
					'value'       => array(
						esc_html__( '1 item', 'kuteshop' )  => '12',
						esc_html__( '2 items', 'kuteshop' ) => '6',
						esc_html__( '3 items', 'kuteshop' ) => '4',
						esc_html__( '4 items', 'kuteshop' ) => '3',
						esc_html__( '5 items', 'kuteshop' ) => '15',
						esc_html__( '6 items', 'kuteshop' ) => '2',
					),
					'description' => esc_html__( '(Item per row on screen resolution of device >=768px and < 992px )', 'kuteshop' ),
					'group'       => esc_html__( 'Boostrap settings', 'kuteshop' ),
					'std'         => '12',
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Items per row on Mobile', 'kuteshop' ),
					'param_name'  => 'boostrap_xs_items',
					'value'       => array(
						esc_html__( '1 item', 'kuteshop' )  => '12',
						esc_html__( '2 items', 'kuteshop' ) => '6',
						esc_html__( '3 items', 'kuteshop' ) => '4',
						esc_html__( '4 items', 'kuteshop' ) => '3',
						esc_html__( '5 items', 'kuteshop' ) => '15',
						esc_html__( '6 items', 'kuteshop' ) => '2',
					),
					'description' => esc_html__( '(Item per row on screen resolution of device >=480  add < 768px )', 'kuteshop' ),
					'group'       => esc_html__( 'Boostrap settings', 'kuteshop' ),
					'std'         => '12',
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Items per row on Mobile', 'kuteshop' ),
					'param_name'  => 'boostrap_ts_items',
					'value'       => array(
						esc_html__( '1 item', 'kuteshop' )  => '12',
						esc_html__( '2 items', 'kuteshop' ) => '6',
						esc_html__( '3 items', 'kuteshop' ) => '4',
						esc_html__( '4 items', 'kuteshop' ) => '3',
						esc_html__( '5 items', 'kuteshop' ) => '15',
						esc_html__( '6 items', 'kuteshop' ) => '2',
					),
					'description' => esc_html__( '(Item per row on screen resolution of device < 480px)', 'kuteshop' ),
					'group'       => esc_html__( 'Boostrap settings', 'kuteshop' ),
					'std'         => '12',
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
			);
			if ( $dependency == null && $value_dependency == null ) {
				foreach ( $data_bootstrap as $value ) {
					unset( $value['dependency'] );
					$data_value[] = $value;
				}
			} else {
				$data_value = $data_bootstrap;
			}

			return $data_value;
		}

		function carousel_settings( $dependency, $value_dependency )
		{
			$data_value    = array();
			$data_carousel = array(
				array(
					'type'       => 'dropdown',
					'value'      => array(
						esc_html__( '1 Row', 'kuteshop' )  => '1',
						esc_html__( '2 Rows', 'kuteshop' ) => '2',
						esc_html__( '3 Rows', 'kuteshop' ) => '3',
						esc_html__( '4 Rows', 'kuteshop' ) => '4',
						esc_html__( '5 Rows', 'kuteshop' ) => '5',
						esc_html__( '6 Rows', 'kuteshop' ) => '6',
					),
					'std'        => '1',
					'heading'    => esc_html__( 'The number of rows which are shown on block', 'kuteshop' ),
					'param_name' => 'owl_number_row',
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Rows space', 'kuteshop' ),
					'param_name' => 'owl_rows_space',
					'value'      => array(
						esc_html__( 'Default', 'kuteshop' ) => 'rows-space-0',
						esc_html__( '10px', 'kuteshop' )    => 'rows-space-10',
						esc_html__( '20px', 'kuteshop' )    => 'rows-space-20',
						esc_html__( '30px', 'kuteshop' )    => 'rows-space-30',
						esc_html__( '40px', 'kuteshop' )    => 'rows-space-40',
						esc_html__( '50px', 'kuteshop' )    => 'rows-space-50',
						esc_html__( '60px', 'kuteshop' )    => 'rows-space-60',
						esc_html__( '70px', 'kuteshop' )    => 'rows-space-70',
						esc_html__( '80px', 'kuteshop' )    => 'rows-space-80',
						esc_html__( '90px', 'kuteshop' )    => 'rows-space-90',
						esc_html__( '100px', 'kuteshop' )   => 'rows-space-100',
					),
					'std'        => 'rows-space-0',
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => 'owl_number_row', 'value' => array( '2', '3', '4', '5', '6' ),
					),
				),
				array(
					'type'       => 'dropdown',
					'value'      => array(
						esc_html__( 'Yes', 'kuteshop' ) => 'true',
						esc_html__( 'No', 'kuteshop' )  => 'false',
					),
					'std'        => 'false',
					'heading'    => esc_html__( 'Vertical Mode', 'kuteshop' ),
					'param_name' => 'owl_vertical',
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'dropdown',
					'value'      => array(
						esc_html__( 'Yes', 'kuteshop' ) => 'true',
						esc_html__( 'No', 'kuteshop' )  => 'false',
					),
					'std'        => 'false',
					'heading'    => esc_html__( 'verticalSwiping', 'kuteshop' ),
					'param_name' => 'owl_verticalswiping',
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => 'owl_vertical', 'value' => array( 'true' ),
					),
				),
				array(
					'type'       => 'dropdown',
					'value'      => array(
						esc_html__( 'Yes', 'kuteshop' ) => 'true',
						esc_html__( 'No', 'kuteshop' )  => 'false',
					),
					'std'        => 'false',
					'heading'    => esc_html__( 'AutoPlay', 'kuteshop' ),
					'param_name' => 'owl_autoplay',
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'number',
					'heading'     => esc_html__( 'Autoplay Speed', 'kuteshop' ),
					'param_name'  => 'owl_autoplayspeed',
					'value'       => '1000',
					'suffix'      => esc_html__( 'milliseconds', 'kuteshop' ),
					'description' => esc_html__( 'Autoplay speed in milliseconds', 'kuteshop' ),
					'group'       => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency'  => array(
						'element' => 'owl_autoplay', 'value' => array( 'true' ),
					),
				),
				array(
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'No', 'kuteshop' )  => 'false',
						esc_html__( 'Yes', 'kuteshop' ) => 'true',
					),
					'std'         => 'true',
					'heading'     => esc_html__( 'Navigation', 'kuteshop' ),
					'param_name'  => 'owl_navigation',
					'description' => esc_html__( "Show buton 'next' and 'prev' buttons.", 'kuteshop' ),
					'group'       => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Navigation style', 'kuteshop' ),
					'param_name' => 'owl_navigation_style',
					'value'      => array(
						esc_html__( 'Default', 'kuteshop' )      => '',
						esc_html__( 'Style Center', 'kuteshop' ) => 'nav-center',
					),
					'std'        => '',
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array( 'element' => 'owl_navigation', 'value' => array( 'true' ) ),
				),
				array(
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Yes', 'kuteshop' ) => 'true',
						esc_html__( 'No', 'kuteshop' )  => 'false',
					),
					'std'         => 'false',
					'heading'     => esc_html__( 'Loop', 'kuteshop' ),
					'param_name'  => 'owl_loop',
					'description' => esc_html__( "Inifnity loop. Duplicate last and first items to get loop illusion.", 'kuteshop' ),
					'group'       => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'number',
					'heading'     => esc_html__( 'Slide Speed', 'kuteshop' ),
					'param_name'  => 'owl_slidespeed',
					'value'       => '300',
					'suffix'      => esc_html__( 'milliseconds', 'kuteshop' ),
					'description' => esc_html__( 'Slide speed in milliseconds', 'kuteshop' ),
					'group'       => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'        => 'number',
					'heading'     => esc_html__( "Margin", 'kuteshop' ),
					'param_name'  => 'slide_margin',
					'value'       => '0',
					'suffix'      => esc_html__( "Pixel", 'kuteshop' ),
					'description' => esc_html__( 'Distance( or space) between 2 item', 'kuteshop' ),
					'group'       => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency'  => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'number',
					'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 1500px )", 'kuteshop' ),
					'param_name' => 'owl_ls_items',
					'value'      => '4',
					'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'number',
					'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 1200px and < 1500px )", 'kuteshop' ),
					'param_name' => 'owl_lg_items',
					'value'      => '4',
					'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'number',
					'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 992px < 1200px )", 'kuteshop' ),
					'param_name' => 'owl_md_items',
					'value'      => '3',
					'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'number',
					'heading'    => esc_html__( "The items on tablet (Screen resolution of device >=768px and < 992px )", 'kuteshop' ),
					'param_name' => 'owl_sm_items',
					'value'      => '2',
					'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'number',
					'heading'    => esc_html__( "The items on mobile landscape(Screen resolution of device >=480px and < 768px)", 'kuteshop' ),
					'param_name' => 'owl_xs_items',
					'value'      => '2',
					'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
				array(
					'type'       => 'number',
					'heading'    => esc_html__( "The items on mobile (Screen resolution of device < 480px)", 'kuteshop' ),
					'param_name' => 'owl_ts_items',
					'value'      => '1',
					'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
					'group'      => esc_html__( 'Carousel settings', 'kuteshop' ),
					'dependency' => array(
						'element' => $dependency, 'value' => array( $value_dependency ),
					),
				),
			);
			if ( $dependency == null && $value_dependency == null ) {
				$match = array(
					'owl_navigation_style',
					'owl_autoplayspeed',
					'owl_rows_space',
					'owl_verticalswiping',
				);
				foreach ( $data_carousel as $value ) {
					if ( !in_array( $value['param_name'], $match ) ) {
						unset( $value['dependency'] );
					}
					$data_value[] = $value;
				}
			} else {
				$data_value = $data_carousel;
			}

			return $data_value;
		}

		function generate_carousel_data_attributes( $prefix = '', $atts )
		{
			$responsive = array();
			$slick      = array();
			$results    = '';
			if ( isset( $atts[$prefix . 'autoplay'] ) && $atts[$prefix . 'autoplay'] == 'true' ) {
				$slick['autoplay'] = true;
			}
			if ( isset( $atts[$prefix . 'autoplayspeed'] ) && $atts[$prefix . 'autoplay'] == 'true' ) {
				$slick['autoplaySpeed'] = intval( $atts[$prefix . 'autoplayspeed'] );
			}
			if ( isset( $atts[$prefix . 'navigation'] ) ) {
				$slick['arrows'] = $atts[$prefix . 'navigation'] == 'true' ? true : false;
			}
			if ( isset( $atts[$prefix . 'dots'] ) ) {
				$slick['dots'] = $atts[$prefix . 'dots'] == 'true' ? true : false;
			}
			if ( isset( $atts[$prefix . 'loop'] ) ) {
				$slick['infinite'] = $atts[$prefix . 'loop'] == 'true' ? true : false;
			}
			if ( isset( $atts[$prefix . 'fade'] ) ) {
				$slick['fade'] = $atts[$prefix . 'fade'] == 'true' ? true : false;
			}
			if ( isset( $atts[$prefix . 'slidespeed'] ) ) {
				$slick['speed'] = intval( $atts[$prefix . 'slidespeed'] );
			}
			if ( isset( $atts[$prefix . 'ls_items'] ) ) {
				$slick['slidesToShow'] = intval( $atts[$prefix . 'ls_items'] );
			}
			if ( isset( $atts[$prefix . 'slidestoscroll'] ) ) {
				$slick['slidesToScroll'] = intval( $atts[$prefix . 'slidestoscroll'] );
			}
			if ( isset( $atts[$prefix . 'vertical'] ) && $atts[$prefix . 'vertical'] == 'true' ) {
				$slick['vertical'] = true;
			}
			if ( isset( $atts[$prefix . 'center_mode'] ) && $atts[$prefix . 'center_mode'] == 'true' ) {
				$slick['centerMode'] = true;
			}
			if ( isset( $atts[$prefix . 'verticalswiping'] ) && $atts[$prefix . 'verticalswiping'] == 'true' ) {
				$slick['verticalSwiping'] = true;
			}
			if ( isset( $atts[$prefix . 'number_row'] ) ) {
				$slick['rows'] = intval( $atts[$prefix . 'number_row'] );
			}
			$results .= ' data-slick = ' . json_encode( $slick ) . ' ';
			if ( isset( $atts[$prefix . 'ts_items'] ) ) {
				$responsive[] = array(
					'breakpoint' => 480,
					'settings'   => array(
						'slidesToShow' => intval( $atts[$prefix . 'ts_items'] ),
					),
				);
			}
			if ( isset( $atts[$prefix . 'xs_items'] ) ) {
				$responsive[] = array(
					'breakpoint' => 768,
					'settings'   => array(
						'slidesToShow' => intval( $atts[$prefix . 'xs_items'] ),
					),
				);
			}
			if ( isset( $atts[$prefix . 'sm_items'] ) ) {
				$responsive[] = array(
					'breakpoint' => 992,
					'settings'   => array(
						'slidesToShow' => intval( $atts[$prefix . 'sm_items'] ),
					),
				);
			}
			if ( isset( $atts[$prefix . 'md_items'] ) ) {
				$responsive[] = array(
					'breakpoint' => 1200,
					'settings'   => array(
						'slidesToShow' => intval( $atts[$prefix . 'md_items'] ),
					),
				);
			}
			if ( isset( $atts[$prefix . 'lg_items'] ) ) {
				$responsive[] = array(
					'breakpoint' => 1500,
					'settings'   => array(
						'slidesToShow' => intval( $atts[$prefix . 'lg_items'] ),
					),
				);
			}
			if ( isset( $atts[$prefix . 'responsive_vertical'] ) && $atts[$prefix . 'responsive_vertical'] >= 480 ) {
				$responsive[0]['settings']['vertical'] = false;
			}
			if ( isset( $atts[$prefix . 'responsive_vertical'] ) && $atts[$prefix . 'responsive_vertical'] >= 768 ) {
				$responsive[1]['settings']['vertical'] = false;
			}
			if ( isset( $atts[$prefix . 'responsive_vertical'] ) && $atts[$prefix . 'responsive_vertical'] >= 992 ) {
				$responsive[2]['settings']['vertical'] = false;
			}
			if ( isset( $atts[$prefix . 'responsive_vertical'] ) && $atts[$prefix . 'responsive_vertical'] >= 1200 ) {
				$responsive[3]['settings']['vertical'] = false;
			}
			if ( isset( $atts[$prefix . 'responsive_vertical'] ) && $atts[$prefix . 'responsive_vertical'] >= 1500 ) {
				$responsive[4]['settings']['vertical'] = false;
			}
			if ( isset( $atts[$prefix . 'responsive_rows'] ) && $atts[$prefix . 'responsive_rows'] >= 480 ) {
				$responsive[0]['settings']['rows'] = 2;
			}
			if ( isset( $atts[$prefix . 'responsive_rows'] ) && $atts[$prefix . 'responsive_rows'] >= 768 ) {
				$responsive[1]['settings']['rows'] = 2;
			}
			if ( isset( $atts[$prefix . 'responsive_rows'] ) && $atts[$prefix . 'responsive_rows'] >= 992 ) {
				$responsive[2]['settings']['rows'] = 2;
			}
			if ( isset( $atts[$prefix . 'responsive_rows'] ) && $atts[$prefix . 'responsive_rows'] >= 1200 ) {
				$responsive[3]['settings']['rows'] = 2;
			}
			if ( isset( $atts[$prefix . 'responsive_rows'] ) && $atts[$prefix . 'responsive_rows'] >= 1500 ) {
				$responsive[4]['settings']['rows'] = 2;
			}
			$results .= 'data-responsive = ' . json_encode( $responsive ) . ' ';

			return $results;
		}

		function getProducts( $atts, $args = array(), $ignore_sticky_posts = 1 )
		{
			extract( $atts );
			$target             = isset( $target ) ? $target : 'recent-product';
			$meta_query         = WC()->query->get_meta_query();
			$args['meta_query'] = $meta_query;
			$args['post_type']  = 'product';
			if ( isset( $taxonomy ) and $taxonomy ) {
				$args['tax_query'] =
					array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => array_map( 'sanitize_title', explode( ',', $taxonomy )
							),
						),
					);
			}
			$args['post_status']         = 'publish';
			$args['ignore_sticky_posts'] = $ignore_sticky_posts;
			$args['suppress_filter']     = true;
			if ( isset( $atts['per_page'] ) && $atts['per_page'] ) {
				$args['posts_per_page'] = $atts['per_page'];
			}
			if ( !isset( $orderby ) ) {
				$ordering_args = WC()->query->get_catalog_ordering_args();
				$orderby       = $ordering_args['orderby'];
				$order         = $ordering_args['order'];
			} else {
				$orderby = $atts['orderby'];
				$order   = $atts['order'];
			}
			switch ( $target ):
				case 'best-selling' :
					$args['meta_key'] = 'total_sales';
					$args['orderby']  = 'meta_value_num';
					break;
				case 'top-rated' :
					$args['meta_key'] = '_wc_average_rating';
					$args['orderby']  = array(
						'meta_value_num' => $order,
						'ID'             => 'ASC',
					);
					break;
				case 'product-category' :
					$ordering_args   = WC()->query->get_catalog_ordering_args( $atts['orderby'], $atts['order'] );
					$args['orderby'] = $ordering_args['orderby'];
					$args['order']   = $ordering_args['order'];
					break;
				case 'products' :
					$args['posts_per_page'] = -1;
					if ( !empty( $ids ) ) {
						$args['post__in'] = array_map( 'trim', explode( ',', $ids ) );
						$args['orderby']  = 'post__in';
					}
					if ( !empty( $skus ) ) {
						$args['meta_query'][] = array(
							'key'     => '_sku',
							'value'   => array_map( 'trim', explode( ',', $skus ) ),
							'compare' => 'IN',
						);
					}
					break;
				case 'featured_products' :
					$meta_query         = WC()->query->get_meta_query();
					$tax_query          = WC()->query->get_tax_query();
					$tax_query[]        = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
						'operator' => 'IN',
					);
					$args['tax_query']  = $tax_query;
					$args['meta_query'] = $meta_query;
					break;
				case 'product_attribute' :
					//'recent-product'
					$args['tax_query'] = array(
						array(
							'taxonomy' => strstr( $atts['attribute'], 'pa_' ) ? sanitize_title( $atts['attribute'] ) : 'pa_' . sanitize_title( $atts['attribute'] ),
							'terms'    => array_map( 'sanitize_title', explode( ',', $atts['filter'] ) ),
							'field'    => 'slug',
						),
					);
					break;
				case 'on_new' :
					$newness            = apply_filters( 'theme_get_option', 'product_newness', 7 );    // Newness in days as defined by option
					$args['date_query'] = array(
						array(
							'after'     => '' . $newness . ' days ago',
							'inclusive' => true,
						),
					);
					if ( $orderby == '_sale_price' ) {
						$orderby = 'date';
						$order   = 'DESC';
					}
					$args['orderby'] = $orderby;
					$args['order']   = $order;
					break;
				case 'on_sale' :
					$product_ids_on_sale = wc_get_product_ids_on_sale();
					$args['post__in']    = array_merge( array( 0 ), $product_ids_on_sale );
					if ( $orderby == '_sale_price' ) {
						$orderby = 'date';
						$order   = 'DESC';
					}
					$args['orderby'] = $orderby;
					$args['order']   = $order;
					break;
				default :
					//'recent-product'
					$args['orderby'] = $orderby;
					$args['order']   = $order;
					if ( isset( $ordering_args['meta_key'] ) ) {
						$args['meta_key'] = $ordering_args['meta_key'];
					}
					// Remove ordering query arguments
					WC()->query->remove_ordering_args();
					break;
			endswitch;

			return $products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		}

		public function map_shortcode()
		{
			// CUSTOM PRODUCT SIZE
			$product_size_width_list = array();
			$width                   = 300;
			$height                  = 300;
			$crop                    = 1;
			if ( function_exists( 'wc_get_image_size' ) ) {
				$size   = wc_get_image_size( 'shop_catalog' );
				$width  = isset( $size['width'] ) ? $size['width'] : $width;
				$height = isset( $size['height'] ) ? $size['height'] : $height;
				$crop   = isset( $size['crop'] ) ? $size['crop'] : $crop;
			}
			for ( $i = 100; $i < $width; $i = $i + 10 ) {
				array_push( $product_size_width_list, $i );
			}
			$product_size_list                         = array();
			$product_size_list[$width . 'x' . $height] = $width . 'x' . $height;
			foreach ( $product_size_width_list as $k => $w ) {
				$w = intval( $w );
				if ( isset( $width ) && $width > 0 ) {
					$h = round( $height * $w / $width );
				} else {
					$h = $w;
				}
				$product_size_list[$w . 'x' . $h] = $w . 'x' . $h;
			}
			$product_size_list['Custom'] = 'custom';
			$attributes_tax              = array();
			if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
				$attributes_tax = wc_get_attribute_taxonomies();
			}
			$attributes = array();
			if ( is_array( $attributes_tax ) && count( $attributes_tax ) > 0 ) {
				foreach ( $attributes_tax as $attribute ) {
					$attributes[$attribute->attribute_label] = $attribute->attribute_name;
				}
			}
			/* Map New blog */
			$categories_array = array(
				esc_html__( 'All', 'kuteshop' ) => '',
			);
			$args             = array();
			$categories       = get_categories( $args );
			foreach ( $categories as $category ) {
				$categories_array[$category->name] = $category->slug;
			}
			/* Map New Custom menu */
			$all_menu = array();
			$menus    = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
			if ( $menus && count( $menus ) > 0 ) {
				foreach ( $menus as $m ) {
					$all_menu[$m->name] = $m->slug;
				}
			}
			/* Map New Social */
			$socials     = array();
			$all_socials = apply_filters( 'theme_get_option', 'user_all_social' );
			if ( $all_socials ) {
				foreach ( $all_socials as $key => $value )
					$socials[$value['title_social']] = $key;
			}
			/* ADD PARAM*/
			vc_add_params(
				'vc_single_image',
				array(
					array(
						'param_name' => 'image_effect',
						'heading'    => esc_html__( 'Effect', 'kuteshop' ),
						'group'      => esc_html__( 'Image Effect', 'kuteshop' ),
						'type'       => 'dropdown',
						'value'      => array(
							esc_html__( 'None', 'kuteshop' )                      => 'none',
							esc_html__( 'Normal Effect', 'kuteshop' )             => 'effect normal-effect',
							esc_html__( 'Normal Effect Dark Color', 'kuteshop' )  => 'effect normal-effect dark-bg',
							esc_html__( 'Normal Effect Light Color', 'kuteshop' ) => 'effect normal-effect light-bg',
							esc_html__( 'Bounce In', 'kuteshop' )                 => 'effect bounce-in',
							esc_html__( 'Plus Zoom', 'kuteshop' )                 => 'effect plus-zoom',
							esc_html__( 'Border Zoom', 'kuteshop' )               => 'effect border-zoom',
							esc_html__( 'Border ScaleUp', 'kuteshop' )            => 'effect border-scale',
						),
						'sdt'        => 'none',
					),
				)
			);
			vc_add_params(
				'vc_tta_section',
				array(
					array(
						'type'        => 'attach_image',
						'param_name'  => 'title_image',
						'heading'     => esc_html__( 'Title image', 'kuteshop' ),
						'description' => esc_html__( 'If you select image, title will display none', 'kuteshop' ),
					),
				)
			);
			vc_add_params(
				'kuteshop_blog',
				$this->carousel_settings( null, null )
			);
			vc_add_params(
				'kuteshop_products',
				array_merge( $this->carousel_settings( 'productsliststyle', 'owl' ), $this->bootstrap_settings( 'productsliststyle', 'grid' ) )
			);
			vc_add_params(
				'kuteshop_slider',
				$this->carousel_settings( null, null )
			);
			vc_map(
				array(
					'name'                    => esc_html__( 'Kuteshop: Custom Heading', 'kuteshop' ),
					'base'                    => 'kuteshop_custom_heading',
					'icon'                    => 'pe pe-7s-pen',
					'show_settings_on_create' => true,
					'category'                => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description'             => esc_html__( 'Custom Heading content', 'kuteshop' ),
					'params'                  => array(
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Text source', 'kuteshop' ),
							'param_name'  => 'source',
							'value'       => array(
								esc_html__( 'Custom text', 'kuteshop' )        => '',
								esc_html__( 'Post or Page Title', 'kuteshop' ) => 'post_title',
							),
							'std'         => '',
							'description' => esc_html__( 'Select text source.', 'kuteshop' ),
						),
						array(
							'type'        => 'textarea',
							'heading'     => esc_html__( 'Text', 'kuteshop' ),
							'param_name'  => 'text',
							'admin_label' => true,
							'value'       => esc_html__( 'This is custom heading element', 'kuteshop' ),
							'description' => esc_html__( 'Note: If you are using non-latin characters be sure to activate them under Settings/WPBakery Page Builder/General Settings.', 'kuteshop' ),
							'dependency'  => array(
								'element'  => 'source',
								'is_empty' => true,
							),
						),
						array(
							'type'        => 'vc_link',
							'heading'     => esc_html__( 'URL (Link)', 'kuteshop' ),
							'param_name'  => 'link',
							'description' => esc_html__( 'Add link to custom heading.', 'kuteshop' ),
							// compatible with btn2 and converted from href{btn1}
						),
						array(
							'type'       => 'font_container',
							'param_name' => 'font_container',
							'value'      => 'tag:h2|text_align:left',
							'settings'   => array(
								'fields' => array(
									'tag'                     => 'h2',
									// default value h2
									'text_align',
									'font_size',
									'line_height',
									'color',
									'tag_description'         => esc_html__( 'Select element tag.', 'kuteshop' ),
									'text_align_description'  => esc_html__( 'Select text alignment.', 'kuteshop' ),
									'font_size_description'   => esc_html__( 'Enter font size.', 'kuteshop' ),
									'line_height_description' => esc_html__( 'Enter line height.', 'kuteshop' ),
									'color_description'       => esc_html__( 'Select heading color.', 'kuteshop' ),
								),
							),
						),
						array(
							'type'        => 'checkbox',
							'heading'     => esc_html__( 'Use theme default font family?', 'kuteshop' ),
							'param_name'  => 'use_theme_fonts',
							'value'       => array( esc_html__( 'Yes', 'kuteshop' ) => 'yes' ),
							'description' => esc_html__( 'Use font family from the theme.', 'kuteshop' ),
						),
						array(
							'type'       => 'google_fonts',
							'param_name' => 'google_fonts',
							'value'      => 'font_family:Abril%20Fatface%3Aregular|font_style:400%20regular%3A400%3Anormal',
							'settings'   => array(
								'fields' => array(
									'font_family_description' => esc_html__( 'Select font family.', 'kuteshop' ),
									'font_style_description'  => esc_html__( 'Select font styling.', 'kuteshop' ),
								),
							),
							'dependency' => array(
								'element'            => 'use_theme_fonts',
								'value_not_equal_to' => 'yes',
							),
						),
						array(
							'type'       => 'param_group',
							'param_name' => 'kuteshop_heading_reponsive',
							'heading'    => esc_html__( 'Extend Reponsive Options', 'kuteshop' ),
							'params'     => array(
								array(
									'type'        => 'dropdown',
									'heading'     => esc_html__( 'Screen Device', 'kuteshop' ),
									'param_name'  => 'screen',
									'value'       => array(
										esc_html__( '1366px', 'kuteshop' )  => '1366',
										esc_html__( '1280px', 'kuteshop' )  => '1280',
										esc_html__( '991px', 'kuteshop' )   => '991',
										esc_html__( '767px ', 'kuteshop' )  => '767',
										esc_html__( '480px ', 'kuteshop' )  => '480',
										esc_html__( '320px ', 'kuteshop' )  => '320',
										esc_html__( 'Custom ', 'kuteshop' ) => 'custom',
									),
									'std'         => '1366',
									'admin_label' => true,
								),
								array(
									'type'       => 'textfield',
									'heading'    => esc_html__( 'Screen Custom', 'kuteshop' ),
									'param_name' => 'screen_custom',
									'suffix'     => esc_html__( 'px', 'kuteshop' ),
									'dependency' => array( 'element' => 'screen', 'value' => array( 'custom' ) ),
								),
								array(
									'type'       => 'font_container',
									'param_name' => 'responsive_font_container',
									'settings'   => array(
										'fields' => array(
											'text_align',
											'font_size',
											'line_height',
											'color',
											'text_align_description'  => esc_html__( 'Select text alignment.', 'kuteshop' ),
											'font_size_description'   => esc_html__( 'Enter font size.', 'kuteshop' ),
											'line_height_description' => esc_html__( 'Enter line height.', 'kuteshop' ),
											'color_description'       => esc_html__( 'Select heading color.', 'kuteshop' ),
										),
									),
								),
							),
							'group'      => esc_html__( 'Responsive Options', 'kuteshop' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'CSS box', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design Options', 'kuteshop' ),
						),
						array(
							'param_name'       => 'custom_heading_custom_id',
							'heading'          => esc_html__( 'Hidden ID', 'kuteshop' ),
							'type'             => 'el_id',
							'settings'         => array(
								'auto_generate' => true,
							),
							'edit_field_class' => 'hidden',
						),
					),
				)
			);
			// Map new Tabs element.
			vc_map(
				array(
					'name'                    => esc_html__( 'Kuteshop: Tabs', 'kuteshop' ),
					'base'                    => 'kuteshop_tabs',
					'icon'                    => 'icon-wpb-ui-tab-content',
					'is_container'            => true,
					'show_settings_on_create' => false,
					'as_parent'               => array(
						'only' => 'vc_tta_section',
					),
					'category'                => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description'             => esc_html__( 'Tabs content', 'kuteshop' ),
					'params'                  => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style2.jpg',
								),
								'style3'  => array(
									'alt' => 'Style 03',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style3.jpg',
								),
								'style4'  => array(
									'alt' => 'Style 04',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style4.jpg',
								),
								'style5'  => array(
									'alt' => 'Style 05',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style5.jpg',
								),
								'style6'  => array(
									'alt' => 'Style 06',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style6.jpg',
								),
								'style7'  => array(
									'alt' => 'Style 07',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style7.jpg',
								),
								'style8'  => array(
									'alt' => 'Style 08',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style8.jpg',
								),
								'style9'  => array(
									'alt' => 'Style 09',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style9.jpg',
								),
								'style10' => array(
									'alt' => 'Style 10',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style10.jpg',
								),
								'style11' => array(
									'alt' => 'Style 11',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style11.jpg',
								),
								'style12' => array(
									'alt' => 'Style 12',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style12.jpg',
								),
								'style13' => array(
									'alt' => 'Style 13',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style13.jpg',
								),
								'style14' => array(
									'alt' => 'Style 14',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'tabs/style14.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'       => 'datepicker',
							'heading'    => esc_html__( 'Countdown', 'kuteshop' ),
							'param_name' => 'time_countdown',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style4' ),
							),
						),
						array(
							'type'             => 'colorpicker',
							'heading'          => esc_html__( 'Tab Head Color', 'kuteshop' ),
							'param_name'       => 'tab_color',
							'std'              => '#000000',
							'edit_field_class' => 'vc_col-sm-6 vc_col-xm-12',
							'dependency'       => array( 'element' => 'style', 'value' => array( 'style1', 'style3', 'style5', 'style6', 'style9' ) ),
						),
						array(
							'type'             => 'dropdown',
							'value'            => array(
								esc_html__( 'Left', 'kuteshop' )  => 'left',
								esc_html__( 'Right', 'kuteshop' ) => 'right',
							),
							'std'              => 'left',
							'heading'          => esc_html__( 'Tab Position', 'kuteshop' ),
							'param_name'       => 'tab_position',
							'edit_field_class' => 'vc_col-sm-6 vc_col-xm-12',
							'dependency'       => array( 'element' => 'style', 'value' => array( 'style6' ) ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'tab_title',
							'description' => esc_html__( 'The title of shortcode', 'kuteshop' ),
							'admin_label' => true,
							'group'       => esc_html__( 'Title Settings', 'kuteshop' ),
							'dependency'  => array( 'element' => 'style', 'value' => array( 'style1', 'style2', 'style3', 'style4', 'style5', 'style6', 'style7', 'style8', 'style9', 'style10', 'style11', 'style12', 'style14' ) ),
						),
						array(
							'type'       => 'vc_link',
							'heading'    => esc_html__( 'Button Link', 'kuteshop' ),
							'param_name' => 'tab_link_button',
							'dependency' => array( 'element' => 'style', 'value' => array( 'style13' ) ),
						),
						array(
							'type'       => 'checkbox',
							'heading'    => esc_html__( 'Title Icon', 'kuteshop' ),
							'param_name' => 'use_tabs_icon',
							'value'      => false,
							'group'      => esc_html__( 'Title Settings', 'kuteshop' ),
							'dependency' => array( 'element' => 'style', 'value' => array( 'style1', 'style3', 'style6' ) ),
						),
						array(
							'param_name' => 'icon_type',
							'heading'    => esc_html__( 'Icon Library', 'kuteshop' ),
							'type'       => 'dropdown',
							'value'      => array(
								esc_html__( 'Font Awesome', 'kuteshop' )  => 'fontawesome',
								esc_html__( 'Font Flaticon', 'kuteshop' ) => 'kuteshopcustomfonts',
							),
							'group'      => esc_html__( 'Title Settings', 'kuteshop' ),
							'dependency' => array(
								'element' => 'use_tabs_icon',
								'value'   => 'true',
							),
						),
						array(
							'param_name'  => 'icon_kuteshopcustomfonts',
							'heading'     => esc_html__( 'Icon', 'kuteshop' ),
							'description' => esc_html__( 'Select icon from library.', 'kuteshop' ),
							'type'        => 'iconpicker',
							'settings'    => array(
								'emptyIcon' => false,
								'type'      => 'kuteshopcustomfonts',
							),
							'group'       => esc_html__( 'Title Settings', 'kuteshop' ),
							'dependency'  => array(
								'element' => 'icon_type',
								'value'   => 'kuteshopcustomfonts',
							),
						),
						array(
							'param_name'  => 'icon_fontawesome',
							'heading'     => esc_html__( 'Icon', 'kuteshop' ),
							'description' => esc_html__( 'Select icon from library.', 'kuteshop' ),
							'type'        => 'iconpicker',
							'settings'    => array(
								'emptyIcon'    => true,
								'iconsPerPage' => 4000,
							),
							'group'       => esc_html__( 'Title Settings', 'kuteshop' ),
							'dependency'  => array(
								'element' => 'icon_type',
								'value'   => 'fontawesome',
							),
						),
						vc_map_add_css_animation(),
						array(
							'param_name'       => 'ajax_check',
							'heading'          => esc_html__( 'Using Ajax Tabs', 'kuteshop' ),
							'type'             => 'dropdown',
							'value'            => array(
								esc_html__( 'Yes', 'kuteshop' ) => '1',
								esc_html__( 'No', 'kuteshop' )  => '0',
							),
							'std'              => '0',
							'edit_field_class' => 'vc_col-sm-4 vc_col-xm-12',
						),
						array(
							'param_name'       => 'short_title',
							'heading'          => esc_html__( 'Short Product Title', 'kuteshop' ),
							'type'             => 'dropdown',
							'value'            => array(
								esc_html__( 'Yes', 'kuteshop' ) => '1',
								esc_html__( 'No', 'kuteshop' )  => '0',
							),
							'std'              => '1',
							'edit_field_class' => 'vc_col-sm-4 vc_col-xm-12',
						),
						array(
							'type'             => 'number',
							'heading'          => esc_html__( 'Active Section', 'kuteshop' ),
							'param_name'       => 'active_section',
							'std'              => 0,
							'edit_field_class' => 'vc_col-sm-2 vc_col-xm-12',
						),
						array(
							'type'             => 'checkbox',
							'heading'          => esc_html__( 'Tabs Filter', 'kuteshop' ),
							'param_name'       => 'use_tabs_filter',
							'value'            => false,
							'edit_field_class' => 'vc_col-sm-2 vc_col-xm-12',
						),
						/* TABS FILTER OPTIONS */
						array(
							'type'             => 'attach_image',
							'heading'          => esc_html__( 'Image Banner', 'kuteshop' ),
							'param_name'       => 'tabs_banner',
							'group'            => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency'       => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
							'edit_field_class' => 'vc_col-sm-3 vc_col-xm-12',
						),
						array(
							'type'             => 'taxonomy',
							'heading'          => esc_html__( 'Product Category', 'kuteshop' ),
							'param_name'       => 'taxonomy',
							'settings'         => array(
								'multiple'   => true,
								'hide_empty' => true,
								'taxonomy'   => 'product_cat',
							),
							'placeholder'      => esc_html__( 'Choose category', 'kuteshop' ),
							'description'      => esc_html__( 'Note: If you want to narrow output, select category(s) above. Only selected categories will be displayed.', 'kuteshop' ),
							'group'            => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency'       => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
							'edit_field_class' => 'vc_col-sm-9 vc_col-xm-12',
						),
						array(
							'type'             => 'dropdown',
							'value'            => array(
								esc_html__( '1 Row', 'kuteshop' )  => '1',
								esc_html__( '2 Rows', 'kuteshop' ) => '2',
								esc_html__( '3 Rows', 'kuteshop' ) => '3',
								esc_html__( '4 Rows', 'kuteshop' ) => '4',
								esc_html__( '5 Rows', 'kuteshop' ) => '5',
								esc_html__( '6 Rows', 'kuteshop' ) => '6',
								esc_html__( '7 Rows', 'kuteshop' ) => '7',
								esc_html__( '8 Rows', 'kuteshop' ) => '8',
							),
							'dependency'       => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
							'std'              => '1',
							'heading'          => esc_html__( 'The number of rows which are shown on block', 'kuteshop' ),
							'param_name'       => 'cats_number_row',
							'group'            => esc_html__( 'Filter Options', 'kuteshop' ),
							'edit_field_class' => 'vc_col-sm-6 vc_col-xm-12',
						),
						array(
							'param_name'       => 'cats_responsive_rows',
							'heading'          => esc_html__( 'Row Responsive', 'kuteshop' ),
							'type'             => 'dropdown',
							'value'            => array(
								esc_html__( '1500', 'kuteshop' ) => '1500',
								esc_html__( '1200', 'kuteshop' ) => '1200',
								esc_html__( '992', 'kuteshop' )  => '992',
								esc_html__( '768', 'kuteshop' )  => '768',
								esc_html__( '480', 'kuteshop' )  => '480',
							),
							'dependency'       => array(
								'element' => 'cats_number_row',
								'value'   => array( '2', '3', '4', '5', '6', '7', '8' ),
							),
							'group'            => esc_html__( 'Filter Options', 'kuteshop' ),
							'std'              => '992',
							'description'      => esc_html__( 'slide Category filter will be transform to 2 row from this screen.', 'kuteshop' ),
							'edit_field_class' => 'vc_col-sm-6 vc_col-xm-12',
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 1500px )", 'kuteshop' ),
							'param_name' => 'cats_ls_items',
							'value'      => '4',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency' => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 1200px and < 1500px )", 'kuteshop' ),
							'param_name' => 'cats_lg_items',
							'value'      => '1',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency' => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 992px < 1200px )", 'kuteshop' ),
							'param_name' => 'cats_md_items',
							'value'      => '1',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency' => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on tablet (Screen resolution of device >=768px and < 992px )", 'kuteshop' ),
							'param_name' => 'cats_sm_items',
							'value'      => '1',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency' => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on mobile landscape(Screen resolution of device >=480px and < 768px)", 'kuteshop' ),
							'param_name' => 'cats_xs_items',
							'value'      => '1',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency' => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on mobile (Screen resolution of device < 480px)", 'kuteshop' ),
							'param_name' => 'cats_ts_items',
							'value'      => '1',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Filter Options', 'kuteshop' ),
							'dependency' => array(
								'element' => 'use_tabs_filter',
								'value'   => array( 'true' ),
							),
						),
						/* Tab Link Carousel Settings */
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 1500px )", 'kuteshop' ),
							'param_name' => 'tabs_ls_items',
							'value'      => '4',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Carousel Title', 'kuteshop' ),
							'dependency' => array( 'element' => 'style', 'value' => array( 'style2', 'style12', 'style14' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 1200px and < 1500px )", 'kuteshop' ),
							'param_name' => 'tabs_lg_items',
							'value'      => '4',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Carousel Title', 'kuteshop' ),
							'dependency' => array( 'element' => 'style', 'value' => array( 'style2', 'style12', 'style14' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on desktop (Screen resolution of device >= 992px < 1200px )", 'kuteshop' ),
							'param_name' => 'tabs_md_items',
							'value'      => '3',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Carousel Title', 'kuteshop' ),
							'dependency' => array( 'element' => 'style', 'value' => array( 'style2', 'style12', 'style14' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on tablet (Screen resolution of device >=768px and < 992px )", 'kuteshop' ),
							'param_name' => 'tabs_sm_items',
							'value'      => '2',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Carousel Title', 'kuteshop' ),
							'dependency' => array( 'element' => 'style', 'value' => array( 'style2', 'style12', 'style14' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on mobile landscape(Screen resolution of device >=480px and < 768px)", 'kuteshop' ),
							'param_name' => 'tabs_xs_items',
							'value'      => '2',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Carousel Title', 'kuteshop' ),
							'dependency' => array( 'element' => 'style', 'value' => array( 'style2', 'style12', 'style14' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "The items on mobile (Screen resolution of device < 480px)", 'kuteshop' ),
							'param_name' => 'tabs_ts_items',
							'value'      => '1',
							'suffix'     => esc_html__( 'item(s)', 'kuteshop' ),
							'group'      => esc_html__( 'Carousel Title', 'kuteshop' ),
							'dependency' => array( 'element' => 'style', 'value' => array( 'style2', 'style12', 'style14' ) ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'             => 'checkbox',
							'param_name'       => 'collapsible_all',
							'heading'          => esc_html__( 'Allow collapse all?', 'kuteshop' ),
							'description'      => esc_html__( 'Allow collapse all accordion sections.', 'kuteshop' ),
							'edit_field_class' => 'hidden',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'CSS box', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design Options', 'kuteshop' ),
						),
						array(
							'param_name'       => 'tabs_custom_id',
							'heading'          => esc_html__( 'Hidden ID', 'kuteshop' ),
							'type'             => 'el_id',
							'settings'         => array(
								'auto_generate' => true,
							),
							'edit_field_class' => 'hidden',
						),
					),
					'js_view'                 => 'VcBackendTtaTabsView',
					'custom_markup'           => '
                    <div class="vc_tta-container" data-vc-action="collapse">
                        <div class="vc_general vc_tta vc_tta-tabs vc_tta-color-backend-tabs-white vc_tta-style-flat vc_tta-shape-rounded vc_tta-spacing-1 vc_tta-tabs-position-top vc_tta-controls-align-left">
                            <div class="vc_tta-tabs-container">'
						. '<ul class="vc_tta-tabs-list">'
						. '<li class="vc_tta-tab" data-vc-tab data-vc-target-model-id="{{ model_id }}" data-element_type="vc_tta_section"><a href="javascript:;" data-vc-tabs data-vc-container=".vc_tta" data-vc-target="[data-model-id=\'{{ model_id }}\']" data-vc-target-model-id="{{ model_id }}"><span class="vc_tta-title-text">{{ section_title }}</span></a></li>'
						. '</ul>
                            </div>
                            <div class="vc_tta-panels vc_clearfix {{container-class}}">
                              {{ content }}
                            </div>
                        </div>
                    </div>',
					'default_content'         => '
                        [vc_tta_section title="' . sprintf( '%s %d', esc_html__( 'Tab', 'kuteshop' ), 1 ) . '"][/vc_tta_section]
                        [vc_tta_section title="' . sprintf( '%s %d', esc_html__( 'Tab', 'kuteshop' ), 2 ) . '"][/vc_tta_section]
                    ',
					'admin_enqueue_js'        => array(
						vc_asset_url( 'lib/vc_tabs/vc-tabs.min.js' ),
					),
				)
			);
			// Map new Products
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Products', 'kuteshop' ),
					'base'        => 'kuteshop_products', // shortcode
					'icon'        => 'pe pe-7s-shopbag',
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a product list.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'the_title',
							'admin_label' => true,
						),
						array(
							'type'             => 'checkbox',
							'heading'          => esc_html__( 'Content Ajax', 'kuteshop' ),
							'param_name'       => 'content_ajax',
							'edit_field_class' => 'vc_col-sm-4',
						),
						array(
							'type'             => 'checkbox',
							'heading'          => esc_html__( 'Box Brand', 'kuteshop' ),
							'param_name'       => 'box_brand',
							'edit_field_class' => 'vc_col-sm-4',
						),
						array(
							'type'             => 'checkbox',
							'heading'          => esc_html__( 'Enable Countdown', 'kuteshop' ),
							'param_name'       => 'enable_countdown',
							'edit_field_class' => 'vc_col-sm-4',
						),
						array(
							'type'       => 'select_preview',
							'heading'    => esc_html__( 'Countdown style', 'kuteshop' ),
							'param_name' => 'countdown_style',
							'value'      => array(
								'style1' => array(
									'alt' => esc_html__( 'Style 01', 'kuteshop' ),
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'countdown/style1.jpg',
								),
								'style4' => array(
									'alt' => esc_html__( 'Style 02', 'kuteshop' ),
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'countdown/style2.jpg',
								),
								'style5' => array(
									'alt' => esc_html__( 'Style 03', 'kuteshop' ),
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'countdown/style3.jpg',
								),
								'style7' => array(
									'alt' => esc_html__( 'Style 04', 'kuteshop' ),
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'countdown/style4.jpg',
								),
							),
							'std'        => 'style1',
							'dependency' => array(
								'element' => 'enable_countdown',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'datepicker',
							'heading'    => esc_html__( 'Countdown', 'kuteshop' ),
							'param_name' => 'time_countdown',
							'dependency' => array(
								'element' => 'enable_countdown',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'attach_image',
							'heading'    => esc_html__( 'Banner', 'kuteshop' ),
							'param_name' => 'banner_brand',
							'group'      => esc_html__( 'Box Brand', 'kuteshop' ),
							'dependency' => array(
								'element' => 'box_brand',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'textarea',
							'heading'    => esc_html__( 'Descriptions', 'kuteshop' ),
							'param_name' => 'desc',
							'group'      => esc_html__( 'Box Brand', 'kuteshop' ),
							'dependency' => array(
								'element' => 'box_brand',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'       => 'vc_link',
							'heading'    => esc_html__( 'Link', 'kuteshop' ),
							'param_name' => 'link_brand',
							'group'      => esc_html__( 'Box Brand', 'kuteshop' ),
							'dependency' => array(
								'element' => 'box_brand',
								'value'   => array( 'true' ),
							),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Product List style', 'kuteshop' ),
							'param_name'  => 'productsliststyle',
							'value'       => array(
								esc_html__( 'Grid Bootstrap', 'kuteshop' ) => 'grid',
								esc_html__( 'Owl Carousel', 'kuteshop' )   => 'owl',
							),
							'description' => esc_html__( 'Select a style for list', 'kuteshop' ),
							'std'         => 'grid',
						),
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Product style', 'kuteshop' ),
							'value'       => array(
								'1'  => array(
									'alt' => esc_html__( 'Style 01', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-1.jpg',
								),
								'2'  => array(
									'alt' => esc_html__( 'Style 02', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-2.jpg',
								),
								'3'  => array(
									'alt' => esc_html__( 'Style 03', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-3.jpg',
								),
								'4'  => array(
									'alt' => esc_html__( 'Style 04', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-4.jpg',
								),
								'5'  => array(
									'alt' => esc_html__( 'Style 05', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-5.jpg',
								),
								'6'  => array(
									'alt' => esc_html__( 'Style 06', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-6.jpg',
								),
								'7'  => array(
									'alt' => esc_html__( 'Style 07', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-7.jpg',
								),
								'8'  => array(
									'alt' => esc_html__( 'Style 08', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-8.jpg',
								),
								'9'  => array(
									'alt' => esc_html__( 'Style 09', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-9.jpg',
								),
								'10' => array(
									'alt' => esc_html__( 'Style 10', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-10.jpg',
								),
								'11' => array(
									'alt' => esc_html__( 'Style 11', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-11.jpg',
								),
								'12' => array(
									'alt' => esc_html__( 'Style 12', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-12.jpg',
								),
								'13' => array(
									'alt' => esc_html__( 'Style 13', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-13.jpg',
								),
								'14' => array(
									'alt' => esc_html__( 'Style 14', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-14.jpg',
								),
								'15' => array(
									'alt' => esc_html__( 'Style 15', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-15.jpg',
								),
								'16' => array(
									'alt' => esc_html__( 'Style 16', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-16.jpg',
								),
								'17' => array(
									'alt' => esc_html__( 'Style 17', 'kuteshop' ),
									'img' => KUTESHOP_PRODUCT_STYLE_PREVIEW . 'content-product-style-17.jpg',
								),
							),
							'default'     => '1',
							'param_name'  => 'product_style',
							'description' => esc_html__( 'Select a style for product item', 'kuteshop' ),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Image size', 'kuteshop' ),
							'param_name'  => 'product_image_size',
							'value'       => $product_size_list,
							'description' => esc_html__( 'Select a size for product', 'kuteshop' ),
							'dependency'  => array( 'element' => "product_style", 'value' => array( '1', '2', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( 'Width', 'kuteshop' ),
							'param_name' => 'product_custom_thumb_width',
							'value'      => $width,
							'suffix'     => esc_html__( 'px', 'kuteshop' ),
							'dependency' => array( 'element' => 'product_image_size', 'value' => array( 'custom' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( 'Height', 'kuteshop' ),
							'param_name' => 'product_custom_thumb_height',
							'value'      => $height,
							'suffix'     => esc_html__( 'px', 'kuteshop' ),
							'dependency' => array( 'element' => 'product_image_size', 'value' => array( 'custom' ) ),
						),
						/*Products */
						array(
							'type'        => 'taxonomy',
							'heading'     => esc_html__( 'Product Category', 'kuteshop' ),
							'param_name'  => 'taxonomy',
							'settings'    => array(
								'multiple'   => true,
								'hide_empty' => true,
								'taxonomy'   => 'product_cat',
							),
							'placeholder' => esc_html__( 'Choose category', 'kuteshop' ),
							'description' => esc_html__( 'Note: If you want to narrow output, select category(s) above. Only selected categories will be displayed.', 'kuteshop' ),
							'group'       => esc_html__( 'Products options', 'kuteshop' ),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Target', 'kuteshop' ),
							'param_name'  => 'target',
							'value'       => array(
								esc_html__( 'Best Selling Products', 'kuteshop' ) => 'best-selling',
								esc_html__( 'Top Rated Products', 'kuteshop' )    => 'top-rated',
								esc_html__( 'Recent Products', 'kuteshop' )       => 'recent-product',
								esc_html__( 'Product Category', 'kuteshop' )      => 'product-category',
								esc_html__( 'Products', 'kuteshop' )              => 'products',
								esc_html__( 'Featured Products', 'kuteshop' )     => 'featured_products',
								esc_html__( 'On Sale', 'kuteshop' )               => 'on_sale',
								esc_html__( 'On New', 'kuteshop' )                => 'on_new',
							),
							'description' => esc_html__( 'Choose the target to filter products', 'kuteshop' ),
							'std'         => 'recent-product',
							'group'       => esc_html__( 'Products options', 'kuteshop' ),
						),
						array(
							'type'        => "dropdown",
							'heading'     => esc_html__( "Order by", 'kuteshop' ),
							'param_name'  => "orderby",
							'value'       => array(
								'',
								esc_html__( 'Date', 'kuteshop' )          => 'date',
								esc_html__( 'ID', 'kuteshop' )            => 'ID',
								esc_html__( 'Author', 'kuteshop' )        => 'author',
								esc_html__( 'Title', 'kuteshop' )         => 'title',
								esc_html__( 'Modified', 'kuteshop' )      => 'modified',
								esc_html__( 'Random', 'kuteshop' )        => 'rand',
								esc_html__( 'Comment count', 'kuteshop' ) => 'comment_count',
								esc_html__( 'Menu order', 'kuteshop' )    => 'menu_order',
								esc_html__( 'Sale price', 'kuteshop' )    => '_sale_price',
							),
							'std'         => 'date',
							'description' => esc_html__( "Select how to sort.", 'kuteshop' ),
							'dependency'  => array( 'element' => "target", 'value' => array( 'top-rated', 'recent-product', 'product-category', 'featured_products', 'on_sale', 'on_new', 'product_attribute' ) ),
							'group'       => esc_html__( 'Products options', 'kuteshop' ),
						),
						array(
							'type'        => "dropdown",
							'heading'     => esc_html__( "Order", 'kuteshop' ),
							'param_name'  => "order",
							'value'       => array(
								esc_html__( 'ASC', 'kuteshop' )  => 'ASC',
								esc_html__( 'DESC', 'kuteshop' ) => 'DESC',
							),
							'std'         => 'DESC',
							'description' => esc_html__( "Designates the ascending or descending order.", 'kuteshop' ),
							'dependency'  => array( 'element' => "target", 'value' => array( 'top-rated', 'recent-product', 'product-category', 'featured_products', 'on_sale', 'on_new', 'product_attribute' ) ),
							'group'       => esc_html__( 'Products options', 'kuteshop' ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( 'Product per page', 'kuteshop' ),
							'param_name' => 'per_page',
							'value'      => 6,
							'dependency' => array( 'element' => "target", 'value' => array( 'best-selling', 'top-rated', 'recent-product', 'product-category', 'featured_products', 'product_attribute', 'on_sale', 'on_new' ) ),
							'group'      => esc_html__( 'Products options', 'kuteshop' ),
						),
						array(
							'type'        => 'autocomplete',
							'heading'     => esc_html__( 'Products', 'kuteshop' ),
							'param_name'  => 'ids',
							'settings'    => array(
								'multiple'      => true,
								'sortable'      => true,
								'unique_values' => true,
							),
							'save_always' => true,
							'description' => esc_html__( 'Enter List of Products', 'kuteshop' ),
							'dependency'  => array( 'element' => "target", 'value' => array( 'products' ) ),
							'group'       => esc_html__( 'Products options', 'kuteshop' ),
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Extra class name", "kuteshop" ),
							'param_name'  => "el_class",
							'description' => esc_html__( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "kuteshop" ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
						array(
							'param_name'       => 'products_custom_id',
							'heading'          => esc_html__( 'Hidden ID', 'kuteshop' ),
							'type'             => 'el_id',
							'settings'         => array(
								'auto_generate' => true,
							),
							'edit_field_class' => 'hidden',
						),
					),
				)
			);
			/* Map New Simple SEO */
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Simple SEO', 'kuteshop' ),
					'base'        => 'kuteshop_simpleseo', // shortcode
					"icon"        => "pe pe-7s-graph1",
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a Simple SEO.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'simpleseo/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'simpleseo/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'simpleseo/style2.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'title',
							'admin_label' => true,
						),
						array(
							'type'       => 'attach_images',
							'heading'    => esc_html__( 'Gallery', 'kuteshop' ),
							'param_name' => 'partner_banner',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style2' ),
							),
						),
						array(
							'type'       => 'param_group',
							'heading'    => esc_html__( 'List item', 'kuteshop' ),
							'param_name' => 'items',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'default', 'style1' ),
							),
							'params'     => array(
								array(
									'type'        => 'textfield',
									'heading'     => esc_html__( 'Title', 'kuteshop' ),
									'param_name'  => 'title',
									'admin_label' => true,
								),
								array(
									'type'        => 'exploded_textarea_safe',
									'heading'     => esc_html__( 'Custom links', 'kuteshop' ),
									'param_name'  => 'custom_links',
									'description' => esc_html__( 'Enter links for each item (Note: divide links with linebreaks (Enter)).EX: {name}|{url}', 'kuteshop' ),
								),
								array(
									'type'        => 'dropdown',
									'heading'     => esc_html__( 'Custom link target', 'kuteshop' ),
									'param_name'  => 'custom_links_target',
									'description' => esc_html__( 'Select where to open  custom links.', 'kuteshop' ),
									'value'       => array(
										esc_html__( 'Same window', 'kuteshop' ) => '_self',
										esc_html__( 'New window', 'kuteshop' )  => '_blank',
									),
								),
							),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
					),
				)
			);
			/* Map New Categories */
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Category', 'kuteshop' ),
					'base'        => 'kuteshop_category', // shortcode
					"icon"        => "pe pe-7s-server",
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a Category.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'category/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'category/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'category/style2.jpg',
								),
								'style3'  => array(
									'alt' => 'Style 03',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'category/style3.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'       => 'attach_image',
							'heading'    => esc_html__( 'Banner Category', 'kuteshop' ),
							'param_name' => 'banner',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'title',
							'description' => esc_html__( 'The title of shortcode', 'kuteshop' ),
							'admin_label' => true,
						),
						array(
							'type'       => 'colorpicker',
							'heading'    => esc_html__( 'Title Color', 'kuteshop' ),
							'param_name' => 'title_color',
							'std'        => '#FE3466',
							'dependency' => array( 'style' => 'style', 'value' => array( 'style3' ) ),
						),
						array(
							'type'        => 'textarea',
							'heading'     => esc_html__( 'Descriptions', 'kuteshop' ),
							'param_name'  => 'desc',
							'description' => esc_html__( 'The Descriptions of shortcode', 'kuteshop' ),
							'dependency'  => array(
								'element' => 'style',
								'value'   => array( 'style2' ),
							),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Author', 'kuteshop' ),
							'param_name' => 'author',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style2' ),
							),
						),
						array(
							'type'       => 'dropdown',
							'heading'    => esc_html__( 'Content Position', 'kuteshop' ),
							'param_name' => 'content_position',
							'value'      => array(
								esc_html__( 'Top', 'kuteshop' )    => 'top',
								esc_html__( 'Bottom', 'kuteshop' ) => 'bottom',
							),
							'std'        => 'bottom',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style2' ),
							),
						),
						array(
							'type'        => 'taxonomy',
							'heading'     => esc_html__( 'Product Category', 'kuteshop' ),
							'param_name'  => 'taxonomy',
							'settings'    => array(
								'multiple'   => true,
								'hide_empty' => true,
								'taxonomy'   => 'product_cat',
							),
							'dependency'  => array(
								'element' => 'style',
								'value'   => array( 'default', 'style1', 'style3' ),
							),
							'placeholder' => esc_html__( 'Choose category', 'kuteshop' ),
							'description' => esc_html__( 'Note: If you want to narrow output, select category(s) above. Only selected categories will be displayed.', 'kuteshop' ),
						),
						array(
							'type'        => 'vc_link',
							'heading'     => esc_html__( 'Link', 'kuteshop' ),
							'param_name'  => 'link',
							'description' => esc_html__( 'The Link to Category', 'kuteshop' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
					),
				)
			);
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Blog', 'kuteshop' ),
					'base'        => 'kuteshop_blog', // shortcode
					'icon'        => 'pe pe-7s-sun',
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a blog lists.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'style1' => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'blog/style1.jpg',
								),
								'style2' => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'blog/style2.jpg',
								),
								'style3' => array(
									'alt' => 'Style 03',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'blog/style3.jpg',
								),
								'style4' => array(
									'alt' => 'Style 04',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'blog/style4.jpg',
								),
								'style5' => array(
									'alt' => 'Style 05',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'blog/style5.jpg',
								),
								'style6' => array(
									'alt' => 'Style 06',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'blog/style6.jpg',
								),
							),
							'default'     => 'style1',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'blog_title',
							'description' => esc_html__( 'The title of shortcode', 'kuteshop' ),
							'admin_label' => true,
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Subtitle', 'kuteshop' ),
							'param_name' => 'blog_desc',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style3', 'style4', 'style5' ),
							),
						),
						array(
							'type'       => 'dropdown',
							'heading'    => esc_html__( 'Style Title', 'kuteshop' ),
							'param_name' => 'style_title',
							'value'      => array(
								esc_html__( 'Style 01', 'kuteshop' ) => 'style1',
								esc_html__( 'Style 02', 'kuteshop' ) => 'style2',
								esc_html__( 'Style 03', 'kuteshop' ) => 'style3',
								esc_html__( 'Style 04', 'kuteshop' ) => 'style4',
							),
							'std'        => 'style1',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style1', 'style2', 'style3', 'style4', 'style5' ),
							),
						),
						array(
							'type'       => 'dropdown',
							'heading'    => esc_html__( 'Select Type Post', 'kuteshop' ),
							'param_name' => 'select_post',
							'value'      => array(
								esc_html__( 'Single Post', 'kuteshop' )   => '1',
								esc_html__( 'Multiple Post', 'kuteshop' ) => '0',
							),
							'std'        => '0',
						),
						array(
							'type'        => 'autocomplete',
							'heading'     => esc_html__( 'Select a Post', 'kuteshop' ),
							'param_name'  => 'post_ids',
							'description' => esc_html__( 'Only work with Post.', 'kuteshop' ),
							'settings'    => array(
								'multiple' => true,
								'sortable' => true,
								'groups'   => false,
							),
							'dependency'  => array(
								'element' => 'select_post',
								'value'   => array( '1' ),
							),
							'admin_label' => true,
						),
						array(
							'type'        => 'number',
							'heading'     => esc_html__( 'Number Post', 'kuteshop' ),
							'param_name'  => 'per_page',
							'value'       => 3,
							'suffix'      => esc_html__( 'item(s)', 'kuteshop' ),
							'admin_label' => true,
							'dependency'  => array(
								'element' => 'select_post',
								'value'   => array( '0' ),
							),
						),
						array(
							'param_name'  => 'category_slug',
							'type'        => 'dropdown',
							'value'       => $categories_array, // here I'm stuck
							'heading'     => esc_html__( 'Category filter:', 'kuteshop' ),
							"admin_label" => true,
							'dependency'  => array(
								'element' => 'select_post',
								'value'   => array( '0' ),
							),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Order by', 'kuteshop' ),
							'param_name'  => 'orderby',
							'value'       => array(
								esc_html__( 'None', 'kuteshop' )     => 'none',
								esc_html__( 'ID', 'kuteshop' )       => 'ID',
								esc_html__( 'Author', 'kuteshop' )   => 'author',
								esc_html__( 'Name', 'kuteshop' )     => 'name',
								esc_html__( 'Date', 'kuteshop' )     => 'date',
								esc_html__( 'Modified', 'kuteshop' ) => 'modified',
								esc_html__( 'Rand', 'kuteshop' )     => 'rand',
							),
							'std'         => 'date',
							'description' => esc_html__( 'Select how to sort retrieved posts.', 'kuteshop' ),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Order', 'kuteshop' ),
							'param_name'  => 'order',
							'value'       => array(
								esc_html__( 'ASC', 'kuteshop' )  => 'ASC',
								esc_html__( 'DESC', 'kuteshop' ) => 'DESC',
							),
							'std'         => 'DESC',
							'description' => esc_html__( "Designates the ascending or descending order.", 'kuteshop' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
						array(
							'param_name'       => 'blog_custom_id',
							'heading'          => esc_html__( 'Hidden ID', 'kuteshop' ),
							'type'             => 'el_id',
							'settings'         => array(
								'auto_generate' => true,
							),
							'edit_field_class' => 'hidden',
						),
					),
				)
			);
			/* New Slider */
			vc_map(
				array(
					'name'                    => esc_html__( 'Kuteshop: Slider', 'kuteshop' ),
					'base'                    => 'kuteshop_slider',
					'icon'                    => 'pe pe-7s-photo-gallery',
					'category'                => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description'             => esc_html__( 'Display a custom slide.', 'kuteshop' ),
					'as_parent'               => array( 'only' => 'vc_single_image, vc_custom_heading,vc_column_text, kuteshop_iconbox, kuteshop_category' ),
					'content_element'         => true,
					'show_settings_on_create' => true,
					'js_view'                 => 'VcColumnView',
					'params'                  => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => '',
								),
								'style1'  => array(
									'alt' => 'Style 1',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'slider/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 2',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'slider/style2.jpg',
								),
								'style3'  => array(
									'alt' => 'Style 3',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'slider/style3.jpg',
								),
								'style4'  => array(
									'alt' => 'Style 4',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'slider/style4.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Title', 'kuteshop' ),
							'param_name' => 'slider_title',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style2', 'style3', 'style4' ),
							),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Subtitle', 'kuteshop' ),
							'param_name' => 'slider_sub_title',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style3' ),
							),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
						array(
							'param_name'       => 'slider_custom_id',
							'heading'          => esc_html__( 'Hidden ID', 'kuteshop' ),
							'type'             => 'el_id',
							'settings'         => array(
								'auto_generate' => true,
							),
							'edit_field_class' => 'hidden',
						),
					),
				)
			);
			/* Map New Banner */
			vc_map(
				array(
					'name'     => esc_html__( 'Kuteshop: Banner', 'kuteshop' ),
					'base'     => 'kuteshop_banner',
					'icon'     => 'pe pe-7s-photo',
					'category' => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'params'   => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'banner/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'banner/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'banner/style2.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'       => 'dropdown',
							'heading'    => esc_html__( 'Position', 'kuteshop' ),
							'param_name' => 'position',
							'value'      => array(
								esc_html__( 'Left', 'kuteshop' )  => 'left',
								esc_html__( 'Right', 'kuteshop' ) => 'right',
							),
							'std'        => 'left',
							'dependency' => array( 'element' => 'style', 'value' => array( 'style1' ) ),
						),
						array(
							'type'       => 'attach_image',
							'heading'    => esc_html__( 'Banner Image', 'kuteshop' ),
							'param_name' => 'banner_img',
							'dependency' => array( 'element' => 'style', 'value' => array( 'default', 'style2' ) ),
						),
						array(
							'type'       => 'vc_link',
							'heading'    => esc_html__( 'Banner Link', 'kuteshop' ),
							'param_name' => 'banner_link',
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Banner Title', 'kuteshop' ),
							'param_name' => 'banner_title',
							'dependency' => array( 'element' => 'style', 'value' => array( 'default', 'style1' ) ),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Banner Number Products', 'kuteshop' ),
							'param_name' => 'banner_number',
							'dependency' => array( 'element' => 'style', 'value' => array( 'default' ) ),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Banner Price Products', 'kuteshop' ),
							'param_name' => 'banner_price',
							'dependency' => array( 'element' => 'style', 'value' => array( 'style1' ) ),
						),
						array(
							'type'       => 'datepicker',
							'heading'    => esc_html__( 'Countdown', 'kuteshop' ),
							'param_name' => 'time_countdown',
							'dependency' => array(
								'element' => 'style',
								'value'   => array( 'style2' ),
							),
						),
						array(
							'type'        => 'autocomplete',
							'heading'     => esc_html__( 'Products', 'kuteshop' ),
							'param_name'  => 'ids',
							'settings'    => array(
								'multiple'      => true,
								'sortable'      => true,
								'unique_values' => true,
							),
							'save_always' => true,
							'description' => esc_html__( 'Enter List of Products', 'kuteshop' ),
							'dependency'  => array( 'element' => 'style', 'value' => array( 'style1' ) ),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Image size', 'kuteshop' ),
							'param_name'  => 'product_image_size',
							'value'       => $product_size_list,
							'description' => esc_html__( 'Select a size for product', 'kuteshop' ),
							'dependency'  => array( 'element' => 'style', 'value' => array( 'style1' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( 'Width', 'kuteshop' ),
							'param_name' => 'product_custom_thumb_width',
							'value'      => $width,
							'suffix'     => esc_html__( 'px', 'kuteshop' ),
							'dependency' => array( 'element' => 'product_image_size', 'value' => array( 'custom' ) ),
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( 'Height', 'kuteshop' ),
							'param_name' => 'product_custom_thumb_height',
							'value'      => $height,
							'suffix'     => esc_html__( 'px', 'kuteshop' ),
							'dependency' => array( 'element' => 'product_image_size', 'value' => array( 'custom' ) ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
					),
				)
			);
			/* Map New Testimonial */
			vc_map(
				array(
					'name'     => esc_html__( 'Kuteshop: Testimonials', 'kuteshop' ),
					'base'     => 'kuteshop_testimonials',
					'icon'     => 'pe pe-7s-users',
					'category' => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'params'   => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'testimonials/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'testimonials/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'testimonials/style2.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'title',
							'description' => esc_html__( 'The title of shortcode', 'kuteshop' ),
							'admin_label' => true,
						),
						array(
							'type'        => 'number',
							'heading'     => esc_html__( 'Number Post', 'kuteshop' ),
							'param_name'  => 'per_page',
							'value'       => 3,
							'suffix'      => esc_html__( 'item(s)', 'kuteshop' ),
							'admin_label' => true,
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Order by', 'kuteshop' ),
							'param_name'  => 'orderby',
							'value'       => array(
								esc_html__( 'None', 'kuteshop' )     => 'none',
								esc_html__( 'ID', 'kuteshop' )       => 'ID',
								esc_html__( 'Author', 'kuteshop' )   => 'author',
								esc_html__( 'Name', 'kuteshop' )     => 'name',
								esc_html__( 'Date', 'kuteshop' )     => 'date',
								esc_html__( 'Modified', 'kuteshop' ) => 'modified',
								esc_html__( 'Rand', 'kuteshop' )     => 'rand',
							),
							'std'         => 'date',
							'description' => esc_html__( 'Select how to sort retrieved posts.', 'kuteshop' ),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Order', 'kuteshop' ),
							'param_name'  => 'order',
							'value'       => array(
								esc_html__( 'ASC', 'kuteshop' )  => 'ASC',
								esc_html__( 'DESC', 'kuteshop' ) => 'DESC',
							),
							'std'         => 'DESC',
							'description' => esc_html__( 'Designates the ascending or descending order.', 'kuteshop' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
					),
				)
			);
			/* new icon box*/
			vc_map(
				array(
					'name'     => esc_html__( 'Kuteshop: Icon Box', 'kuteshop' ),
					'base'     => 'kuteshop_iconbox',
					'icon'     => 'pe pe-7s-smile',
					'category' => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'params'   => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'iconbox/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'iconbox/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'iconbox/style2.jpg',
								),
								'style3'  => array(
									'alt' => 'Style 03',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'iconbox/style3.jpg',
								),
								'style4'  => array(
									'alt' => 'Style 04',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'iconbox/style4.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'title',
							'admin_label' => true,
						),
						array(
							'param_name' => 'text_content',
							'heading'    => esc_html__( 'Content', 'kuteshop' ),
							'type'       => 'textarea',
						),
						array(
							'param_name' => 'icon_type',
							'heading'    => esc_html__( 'Icon Library', 'kuteshop' ),
							'type'       => 'dropdown',
							'value'      => array(
								esc_html__( 'Font Awesome', 'kuteshop' )  => 'fontawesome',
								esc_html__( 'Font Flaticon', 'kuteshop' ) => 'kuteshopcustomfonts',
							),
						),
						array(
							'param_name'  => 'icon_kuteshopcustomfonts',
							'heading'     => esc_html__( 'Icon', 'kuteshop' ),
							'description' => esc_html__( 'Select icon from library.', 'kuteshop' ),
							'type'        => 'iconpicker',
							'settings'    => array(
								'emptyIcon' => false,
								'type'      => 'kuteshopcustomfonts',
							),
							'dependency'  => array(
								'element' => 'icon_type',
								'value'   => 'kuteshopcustomfonts',
							),
						),
						array(
							'param_name'  => 'icon_fontawesome',
							'heading'     => esc_html__( 'Icon', 'kuteshop' ),
							'description' => esc_html__( 'Select icon from library.', 'kuteshop' ),
							'type'        => 'iconpicker',
							'settings'    => array(
								'emptyIcon'    => false,
								'iconsPerPage' => 4000,
							),
							'dependency'  => array(
								'element' => 'icon_type',
								'value'   => 'fontawesome',
							),
						),
						array(
							'type'        => 'vc_link',
							'heading'     => esc_html__( 'Link', 'kuteshop' ),
							'param_name'  => 'link',
							'description' => esc_html__( 'The Link to Icon', 'kuteshop' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'kuteshop' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'kuteshop' ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'CSS box', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design Options', 'kuteshop' ),
						),
					),
				)
			);
			/* Map New Newsletter */
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Newsletter', 'kuteshop' ),
					'base'        => 'kuteshop_newsletter', // shortcode
					'icon'        => 'pe pe-7s-mail',
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a newsletter box.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'newsletter/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'newsletter/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'newsletter/style2.jpg',
								),
								'style3'  => array(
									'alt' => 'Style 03',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'newsletter/style3.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'       => 'attach_image',
							'heading'    => esc_html__( 'Background', 'kuteshop' ),
							'param_name' => 'background',
							'dependency' => array(
								'element' => 'style',
								'value'   => 'style3',
							),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Title', 'kuteshop' ),
							'param_name' => 'title_text',
							'dependency' => array(
								'element' => 'style',
								'value'   => 'style3',
							),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Subtitle', 'kuteshop' ),
							'param_name' => 'subtitle_text',
							'dependency' => array(
								'element' => 'style',
								'value'   => 'style3',
							),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Placeholder text', 'kuteshop' ),
							'param_name' => 'placeholder_text',
							'std'        => esc_html__( 'Your Email Address', 'kuteshop' ),
						),
						array(
							'type'       => 'textfield',
							'heading'    => esc_html__( 'Button text', 'kuteshop' ),
							'param_name' => 'button_text',
							'dependency' => array(
								'element' => 'style',
								'value'   => 'style2',
							),
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Extra class name", "kuteshop" ),
							'param_name'  => "el_class",
							'description' => esc_html__( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "kuteshop" ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
					),
				)
			);
			/* Map Google Map */
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Google Map', 'kuteshop' ),
					'base'        => 'kuteshop_googlemap', // shortcode
					'icon'        => 'pe pe-7s-map-2',
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a google map.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Title", 'kuteshop' ),
							'param_name'  => "title",
							'admin_label' => true,
							'description' => esc_html__( "title.", 'kuteshop' ),
							'std'         => 'Kute themes',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Phone", 'kuteshop' ),
							'param_name'  => "phone",
							'description' => esc_html__( "phone.", 'kuteshop' ),
							'std'         => '088-465 9965 02',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Email", 'kuteshop' ),
							'param_name'  => "email",
							'description' => esc_html__( "email.", 'kuteshop' ),
							'std'         => 'kutethemes@gmail.com',
						),
						array(
							'type'       => 'number',
							'heading'    => esc_html__( "Map Height", 'kuteshop' ),
							'param_name' => "map_height",
							'std'        => '400',
						),
						array(
							'type'       => 'dropdown',
							'heading'    => esc_html__( 'Maps type', 'kuteshop' ),
							'param_name' => 'map_type',
							'value'      => array(
								esc_html__( 'ROADMAP', 'kuteshop' )   => 'ROADMAP',
								esc_html__( 'SATELLITE', 'kuteshop' ) => 'SATELLITE',
								esc_html__( 'HYBRID', 'kuteshop' )    => 'HYBRID',
								esc_html__( 'TERRAIN', 'kuteshop' )   => 'TERRAIN',
							),
							'std'        => 'ROADMAP',
						),
						array(
							'type'       => 'dropdown',
							'heading'    => esc_html__( 'Show info content?', 'kuteshop' ),
							'param_name' => 'info_content',
							'value'      => array(
								esc_html__( 'Yes', 'kuteshop' ) => '1',
								esc_html__( 'No', 'kuteshop' )  => '2',
							),
							'std'        => '1',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Address", 'kuteshop' ),
							'param_name'  => "address",
							'admin_label' => true,
							'description' => esc_html__( "address.", 'kuteshop' ),
							'std'         => 'Z115 TP. Thai Nguyen',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Longitude", 'kuteshop' ),
							'param_name'  => "longitude",
							'admin_label' => true,
							'description' => esc_html__( "longitude.", 'kuteshop' ),
							'std'         => '105.800286',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Latitude", 'kuteshop' ),
							'param_name'  => "latitude",
							'admin_label' => true,
							'description' => esc_html__( "latitude.", 'kuteshop' ),
							'std'         => '21.587001',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Zoom", 'kuteshop' ),
							'param_name'  => "zoom",
							'admin_label' => true,
							'description' => esc_html__( "zoom.", 'kuteshop' ),
							'std'         => '14',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Extra class name", 'kuteshop' ),
							'param_name'  => "el_class",
							'description' => esc_html__( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "kuteshop" ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
						array(
							'param_name'       => 'googlemap_custom_id',
							'heading'          => esc_html__( 'Hidden ID', 'kuteshop' ),
							'type'             => 'el_id',
							'settings'         => array(
								'auto_generate' => true,
							),
							'edit_field_class' => 'hidden',
						),
					),
				)
			);
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Custom Menu', 'kuteshop' ),
					'base'        => 'kuteshop_custommenu', // shortcode
					'icon'        => 'pe pe-7s-menu',
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a custom menu.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => 'select_preview',
							'heading'     => esc_html__( 'Select style', 'kuteshop' ),
							'value'       => array(
								'default' => array(
									'alt' => 'Default',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'custommenu/default.jpg',
								),
								'style1'  => array(
									'alt' => 'Style 01',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'custommenu/style1.jpg',
								),
								'style2'  => array(
									'alt' => 'Style 02',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'custommenu/style2.jpg',
								),
								'style3'  => array(
									'alt' => 'Style 03',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'custommenu/style3.jpg',
								),
								'style4'  => array(
									'alt' => 'Style 04',
									'img' => KUTESHOP_SHORTCODE_PREVIEW . 'custommenu/style4.jpg',
								),
							),
							'default'     => 'default',
							'admin_label' => true,
							'param_name'  => 'style',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'kuteshop' ),
							'param_name'  => 'title',
							'description' => esc_html__( 'The title of menu', 'kuteshop' ),
							'admin_label' => true,
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Menu', 'kuteshop' ),
							'param_name'  => 'menu',
							'value'       => $all_menu,
							'admin_label' => true,
							'description' => esc_html__( 'Select menu to display.', 'kuteshop' ),
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Extra class name", "kuteshop" ),
							'param_name'  => "el_class",
							'description' => esc_html__( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "kuteshop" ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
					),
				)
			);
			vc_map(
				array(
					'name'        => esc_html__( 'Kuteshop: Socials', 'kuteshop' ),
					'base'        => 'kuteshop_socials', // shortcode
					'icon'        => 'pe pe-7s-share',
					'category'    => esc_html__( 'Kuteshop Elements', 'kuteshop' ),
					'description' => esc_html__( 'Display a social list.', 'kuteshop' ),
					'params'      => array(
						array(
							'type'        => 'checkbox',
							'heading'     => esc_html__( 'Display on', 'kuteshop' ),
							'param_name'  => 'use_socials',
							'admin_label' => true,
							'class'       => 'checkbox-display-block',
							'value'       => $socials,
						),
						array(
							'type'       => 'dropdown',
							'heading'    => esc_html__( 'Style', 'kuteshop' ),
							'param_name' => 'socials_style',
							'value'      => array(
								esc_html__( 'Circle', 'kuteshop' ) => 'style-circle',
								esc_html__( 'Square', 'kuteshop' ) => 'style-square',
							),
							'std'        => '',
						),
						array(
							'type'        => "textfield",
							'heading'     => esc_html__( "Extra class name", "kuteshop" ),
							'param_name'  => "el_class",
							'description' => esc_html__( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "kuteshop" ),
						),
						array(
							'type'       => 'css_editor',
							'heading'    => esc_html__( 'Css', 'kuteshop' ),
							'param_name' => 'css',
							'group'      => esc_html__( 'Design options', 'kuteshop' ),
						),
					),
				)
			);
		}
	}

	new Kuteshop_Visual_Composer();
}
VcShortcodeAutoloader::getInstance()->includeClass( 'WPBakeryShortCode_VC_Tta_Accordion' );

class WPBakeryShortCode_Kuteshop_Tabs extends WPBakeryShortCode_VC_Tta_Accordion
{
}

class WPBakeryShortCode_Kuteshop_Slider extends WPBakeryShortCodesContainer
{
}