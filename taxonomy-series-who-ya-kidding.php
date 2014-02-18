<?php get_header(); ?>

<div id="content" class="stories span8" role="main">
 
    <?php
    	$args = array(
    		'tax_query' => array(
				array(
					'taxonomy' 	=> 'series',
					'field' 	=> 'slug',
					'terms' 	=> 'who-ya-kidding'
				)
			),
			'posts_per_page'	=> 1
    	);
		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) : $query->the_post();
                get_template_part( 'content', 'whoyakidding' );
			endwhile;
        } else {
            get_template_part( 'content', 'not-found' );
        }
    ?>

</div><!--#content-->

<?php get_sidebar(); ?>
<?php get_footer(); ?>