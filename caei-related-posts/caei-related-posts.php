<?php
/*
Plugin Name: Caei Related Posts
Description: Simple widget for adding related posts feature unto your blog page. <strong>Note:</strong> This only works on singular pages (post page).
Version: 1.0.0
Author: CAEI
License: GPLv2 or later
*/

// INIT
function add_caei_related_posts () {
	register_widget( 'caei_related_posts' );
}
add_action( 'widgets_init', 'add_caei_related_posts' );

// STYLES
function enqueue_plugin_styles() {
    wp_enqueue_style( 'caei-related-posts-style', plugins_url( 'caei-related-posts-style.css',  __FILE__  ), '', '1.0' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_plugin_styles' );

// WIDGET
class caei_related_posts extends WP_Widget { 
	function __construct() {
		parent::__construct(	 
			// Base ID
			'caei_related_posts_widget', 
		 
			// Widget name
			__('Related Posts', 'caei_related_posts_widget_domain'), 
		 
			// Widget description
			array( 'description' => __( 'Widget for related blog posts in single post only', 'caei_related_posts_widget_domain' ), ) 
		);
	}
	 
	// Frontend	 
	public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $args['before_widget'];
        
		// Widget Code *** START
        ?>
        <div class="caei-widget-related-posts">
            <?php
                if ( is_singular() ) {
                    ?>
                    <div class="related-posts-title">
                        <?php if ( !empty($title) ) echo $args['before_title'] . $title . $args['after_title']; ?>
                    </div>
                    <div class="related-posts-holder">
                        <?php
                            $post_categories = get_the_category();
                            $post_categories_array = array();
                            $post_tags = get_the_tags();
                            $post_tags_array = array();
                            foreach($post_categories as $post_category) {
                                array_push($post_categories_array, $post_category->term_id);
                            }
                            if ($post_tags) {
                                foreach($post_tags as $post_tag) {
                                    array_push($post_tags_array, $post_tag->term_id);
                                }
                            }                 
                            $args = array(
                                'post__not_in' => array(get_the_ID()), 
                                'post_status' => 'publish',
                                'posts_per_page' => -1,
                                'tax_query' => array(
                                    'relation' => 'OR',
                                    array(
                                        'taxonomy' => 'post_tag',
                                        'terms'    => $post_tags_array,
                                    ),
                                    array(
                                        'taxonomy' => 'category',
                                        'terms'    => $post_categories_array,
                                    ),
                                ),
                            );
                            $the_query = new WP_Query( $args );

                            if ( $the_query->have_posts() ) {
                                echo '<div class="related-posts-holder-inner">';
                                while ( $the_query->have_posts() ) {
                                    $the_query->the_post();
                                    ?>
                                        <div class="related-post">
                                            <a href="<?php the_permalink();?>"><?php the_title(); ?></a>
                                        </div>
                                    <?php
                                }
                                echo '</div>';
                            } else {
                                echo 'No posts available';
                            }
                            
                            wp_reset_postdata();                            
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="related-posts-error">This widget is only for single post only.</div>
                    <?php
                }
            ?>
        </div>
        <?php
        // Widget Code *** END

		echo $args['after_widget'];
	}
	         
	// Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) { $title = $instance[ 'title' ]; }
		else { $title = __( 'Related Posts', 'caei_related_posts_widget_domain' ); }
		// Form
		?>
		<p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
            <input
                class="widefat"
                id="<?php echo $this->get_field_id( 'title' ); ?>"
                name="<?php echo $this->get_field_name( 'title' ); ?>"
                type="text"
                value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
	     
	// Update Process
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}