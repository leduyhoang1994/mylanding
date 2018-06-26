<?php
/**
 *
 * Kuteshop Product Filter
 *
 */
if ( !class_exists( 'Product_Filter_Widget' ) ) {
	class Product_Filter_Widget extends WP_Widget
	{
		function __construct()
		{
			$widget_ops = array(
				'classname'   => 'kuteshop_product_filter',
				'description' => 'Widget Product Filter.',
			);

			parent::__construct( 'widget_kuteshop_product_filter', '1 - Kuteshop Product Filter', $widget_ops );
		}

		function widget( $args, $instance )
		{
			extract( $args );
			echo $args[ 'before_widget' ];
			if ( !empty( $instance[ 'title' ] ) ) : ?>
                <h2 class="widgettitle"><?php echo esc_html( $instance[ 'title' ] ); ?></h2>
			<?php endif; ?>
            <div class="filter-content">
				<?php the_widget( 'WC_Widget_Product_Categories', 'title=CATEGORIES&count=1' ); ?>
				<?php the_widget( 'WC_Widget_Price_Filter', 'title=PRICE' ); ?>
				<?php
				foreach ( $instance[ 'choose_attribute' ] as $value ) {
					$data          = explode( '&', $value );
					$args_attibute = 'title=' . $data[ 2 ] . '&attribute=' . $data[ 0 ] . '&query_type=AND';
					$args_attibute .= $data[ 1 ] == 'box_style' ? '&display_type=color' : '&display_type=list';
					the_widget( 'Kuteshop_Layered_Nav_Widget', $args_attibute );
				}
				?>
            </div>
			<?php
			echo $args[ 'after_widget' ];
		}

		function update( $new_instance, $old_instance )
		{

			$instance                       = $old_instance;
			$instance[ 'title' ]            = $new_instance[ 'title' ];
			$instance[ 'choose_attribute' ] = $new_instance[ 'choose_attribute' ];

			return $instance;

		}

		function form( $instance )
		{
			$attribute_array      = array();
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( !empty( $attribute_taxonomies ) ) {
				foreach ( $attribute_taxonomies as $tax ) {
					if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
						$attribute_array[ $tax->attribute_name . '&' . $tax->attribute_type . '&' . $tax->attribute_label ] = $tax->attribute_label;
					}
				}
			}
			//
			// set defaults
			// -------------------------------------------------
			$instance = wp_parse_args(
				$instance,
				array(
					'title'            => '',
					'choose_attribute' => '',
				)
			);

			$title_value = $instance[ 'title' ];
			$title_field = array(
				'id'    => $this->get_field_name( 'title' ),
				'name'  => $this->get_field_name( 'title' ),
				'type'  => 'text',
				'title' => esc_html__( 'Title', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $title_field, $title_value );
			echo '</p>';

			$choose_attribute_value = $instance[ 'choose_attribute' ];
			$choose_attribute_field = array(
				'id'         => $this->get_field_name( 'choose_attribute' ),
				'name'       => $this->get_field_name( 'choose_attribute' ),
				'type'       => 'select',
				'class'      => 'chosen',
				'attributes' => array(
					'multiple' => 'multiple',
					'style'    => 'width: 100%;',
				),
				'options'    => $attribute_array,
				'title'      => esc_html__( 'Product attribute:', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $choose_attribute_field, $choose_attribute_value );
			echo '</p>';
		}
	}
}

if ( !function_exists( 'Product_Filter_Widget_init' ) ) {
	function Product_Filter_Widget_init()
	{
		register_widget( 'Product_Filter_Widget' );
	}

	add_action( 'widgets_init', 'Product_Filter_Widget_init', 2 );
}