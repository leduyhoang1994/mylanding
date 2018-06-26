<?php
/**
 *
 * Kuteshop post
 *
 */
if ( !class_exists( 'Category_Widget' ) ) {
	class Category_Widget extends WP_Widget
	{
		function __construct()
		{
			$widget_ops = array(
				'classname'   => 'widget-kuteshop-category',
				'description' => 'Widget Category.',
			);

			parent::__construct( 'widget_kuteshop_category', '1 - Kuteshop Category', $widget_ops );
		}

		function widget( $args, $instance )
		{

			extract( $args );
			echo $args[ 'before_widget' ];

			$categories = array();

			if ( !empty( $instance[ 'category' ] ) ) {
				$categories = $instance[ 'category' ];
			} else {
				$terms = get_terms( 'category' );
				if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
					$categories = $terms;
				}
			}

			$atts         = array(
				'owl_loop'            => 'false',
				'owl_autoplay'        => 'false',
				'owl_vertical'        => 'true',
				'owl_verticalswiping' => 'true',
				'owl_slidestoscroll'  => $instance[ 'items' ],
				'owl_slidespeed'      => '200',
				'owl_ts_items'        => $instance[ 'items' ],
				'owl_xs_items'        => $instance[ 'items' ],
				'owl_sm_items'        => $instance[ 'items' ],
				'owl_md_items'        => $instance[ 'items' ],
				'owl_lg_items'        => $instance[ 'items' ],
				'owl_ls_items'        => $instance[ 'items' ],
			);
			$owl_settings = apply_filters( 'generate_carousel_data_attributes', 'owl_', $atts );
			?>
            <?php if ( !empty( $instance[ 'title' ] ) ) : ?>
            <div class="widgettitle-wrap">
                <h2 class="widgettitle"><?php echo esc_html( $instance[ 'title' ] ); ?></h2>
            </div>
            <?php endif; ?>
            <ul class="category-list owl-slick" <?php echo $owl_settings; ?>>
				<?php foreach ( $categories as $key => $value ) : ?>
					<?php
					$term     = get_category( $value );
					$term_url = get_category_link( $value );
					?>
                    <li class="cat-item">
                        <a href="<?php echo esc_url( $term_url ); ?>">
							<?php echo esc_html( $term->name ); ?>
                        </a>
                    </li>
				<?php endforeach; ?>
            </ul>
			<?php
			echo $args[ 'after_widget' ];
		}

		function update( $new_instance, $old_instance )
		{

			$instance               = $old_instance;
			$instance[ 'title' ]    = $new_instance[ 'title' ];
			$instance[ 'items' ]    = $new_instance[ 'items' ];
			$instance[ 'category' ] = $new_instance[ 'category' ];

			return $instance;

		}

		function form( $instance )
		{
			//
			// set defaults
			// -------------------------------------------------
			$instance = wp_parse_args(
				$instance,
				array(
					'title'    => '',
					'items'    => '5',
					'category' => '',
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

			$category_value = $instance[ 'category' ];
			$category_field = array(
				'id'         => $this->get_field_name( 'category' ),
				'name'       => $this->get_field_name( 'category' ),
				'type'       => 'select',
				'options'    => 'categories',
				'query_args' => array(
					'orderby' => 'name',
					'order'   => 'ASC',
				),
				'class'      => 'chosen',
				'attributes' => array(
					'multiple' => 'multiple',
					'style'    => 'width: 100%;',
				),
				'title'      => esc_html__( 'Category Post', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $category_field, $category_value );
			echo '</p>';

			$items_value = $instance[ 'items' ];
			$items_field = array(
				'id'    => $this->get_field_name( 'items' ),
				'name'  => $this->get_field_name( 'items' ),
				'type'  => 'number',
				'title' => esc_html__( 'Number Post show', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $items_field, $items_value );
			echo '</p>';
		}
	}
}

if ( !function_exists( 'Category_Widget_init' ) ) {
	function Category_Widget_init()
	{
		register_widget( 'Category_Widget' );
	}

	add_action( 'widgets_init', 'Category_Widget_init', 2 );
}