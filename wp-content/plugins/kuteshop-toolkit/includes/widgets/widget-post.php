<?php
/**
 *
 * Kuteshop post
 *
 */
if ( !class_exists( 'Post_Widget' ) ) {
	class Post_Widget extends WP_Widget
	{
		function __construct()
		{
			$widget_ops = array(
				'classname'   => 'widget-recent-post',
				'description' => 'Widget post.',
			);

			parent::__construct( 'widget_kuteshop_post', '1 - Kuteshop Post', $widget_ops );
		}

		function widget( $args, $instance )
		{

			extract( $args );
			echo $args[ 'before_widget' ];

			$args_loop = array(
				'post_type'           => 'post',
				'showposts'           => $instance[ 'number' ],
				'nopaging'            => 0,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'order'               => 'DESC',
			);

			if ( $instance[ 'choose_post' ] == '0' ) {
				if ( $instance[ 'type_post' ] == 'popular' ) {
					$args_loop[ 'cat' ]      = $instance[ 'category' ];
					$args_loop[ 'meta_key' ] = 'kuteshop_post_views_count';
					$args_loop[ 'olderby' ]  = 'meta_value_num';
				} else {
					$args_loop[ 'cat' ] = $instance[ 'category' ];
				}
			} else {
				$args_loop[ 'post__in' ] = $instance[ 'ids' ];
			}

			$loop_posts = new WP_Query( $args_loop );

			$atts         = array(
				'owl_loop'            => 'false',
				'owl_autoplay'        => 'false',
				'owl_vertical'        => 'true',
				'owl_verticalswiping' => 'true',
				'owl_slidespeed'      => '400',
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
			<?php if ( $loop_posts->have_posts() ) : ?>
            <div class="kuteshop-posts owl-slick kuteshop-blog style-4 equal-container better-height" <?php echo $owl_settings; ?>>
				<?php while ( $loop_posts->have_posts() ) : $loop_posts->the_post() ?>
                    <article <?php post_class('equal-elem'); ?>>
                        <div class="blog-inner">
							<?php get_template_part( 'templates/blog/blog-styles/content-blog', 'style-4' ); ?>
                        </div>
                    </article>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
            </div>
		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>
			<?php
			echo $args[ 'after_widget' ];
		}

		function update( $new_instance, $old_instance )
		{

			$instance                  = $old_instance;
			$instance[ 'ids' ]         = $new_instance[ 'ids' ];
			$instance[ 'title' ]       = $new_instance[ 'title' ];
			$instance[ 'number' ]      = $new_instance[ 'number' ];
			$instance[ 'items' ]       = $new_instance[ 'items' ];
			$instance[ 'choose_post' ] = $new_instance[ 'choose_post' ];
			$instance[ 'type_post' ]   = $new_instance[ 'type_post' ];
			$instance[ 'category' ]    = $new_instance[ 'category' ];

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
					'title'       => '',
					'number'      => '8',
					'items'       => '5',
					'choose_post' => '0',
					'ids'         => '',
					'type_post'   => '',
					'category'    => '',
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

			$choose_post_value = $instance[ 'choose_post' ];
			$choose_post_field = array(
				'id'         => $this->get_field_name( 'choose_post' ),
				'name'       => $this->get_field_name( 'choose_post' ),
				'type'       => 'select',
				'options'    => array(
					'0' => 'Loop Post',
					'1' => 'Single Post',
				),
				'attributes' => array(
					'data-depend-id' => 'choose_post',
				),
				'title'      => esc_html__( 'Choose Type Post', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $choose_post_field, $choose_post_value );
			echo '</p>';

			$ids_value = $instance[ 'ids' ];
			$ids_field = array(
				'id'         => $this->get_field_name( 'ids' ),
				'name'       => $this->get_field_name( 'ids' ),
				'type'       => 'select',
				'options'    => 'posts',
				'query_args' => array(
					'post_type' => 'post',
					'orderby'   => 'post_date',
					'order'     => 'DESC',
				),
				'class'      => 'chosen',
				'attributes' => array(
					'multiple' => 'multiple',
					'style'    => 'width: 100%;',
				),
				'dependency' => array( 'choose_post', '==', '1' ),
				'title'      => esc_html__( 'Choose Type Post', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $ids_field, $ids_value );
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
				'dependency' => array( 'choose_post', '==', '0' ),
				'title'      => esc_html__( 'Category Post', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $category_field, $category_value );
			echo '</p>';

			$type_post_value = $instance[ 'type_post' ];
			$type_post_field = array(
				'id'         => $this->get_field_name( 'type_post' ),
				'name'       => $this->get_field_name( 'type_post' ),
				'type'       => 'select',
				'options'    => array(
					'latest'  => 'Latest Post',
					'popular' => 'Popular Post',
				),
				'dependency' => array( 'choose_post', '==', '0' ),
				'title'      => esc_html__( 'Type Post', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $type_post_field, $type_post_value );
			echo '</p>';

			$number_value = $instance[ 'number' ];
			$number_field = array(
				'id'         => $this->get_field_name( 'number' ),
				'name'       => $this->get_field_name( 'number' ),
				'type'       => 'number',
				'dependency' => array( 'choose_post', '==', '0' ),
				'title'      => esc_html__( 'Number Post', 'kuteshop' ),
			);
			echo '<p>';
			echo cs_add_element( $number_field, $number_value );
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

if ( !function_exists( 'Post_Widget_init' ) ) {
	function Post_Widget_init()
	{
		register_widget( 'Post_Widget' );
	}

	add_action( 'widgets_init', 'Post_Widget_init', 2 );
}