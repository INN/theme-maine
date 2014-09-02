<?php get_header(); ?>

<div id="content" class="span8" role="main">
	<?php
		$page_id = 5633; //the political party time page ID
		$page = get_page( $page_id );
	?>
	<div class="entry-content"><?php echo apply_filters('the_content', $page->post_content); ?></div>

	<div class="stories">
		<h3 class="recent-posts clearfix">
			<?php
				$queried_object = get_queried_object();
				$term_id = intval( $queried_object->term_id );
				$tax = $queried_object->taxonomy;
				$posts_term = 'Posts';
				printf(__('Recent %1$s<a class="rss-link" href="%2$s"><i class="icon-rss"></i></a>', 'largo'), $posts_term, get_term_feed_link( $term_id, $tax ) );
			?>
		</h3>

	    <?php
	    	$args = array(
	    		'tax_query' => array(
					array(
						'taxonomy' 	=> 'series',
						'field' 	=> 'slug',
						'terms' 	=> 'political-party-time'
					)
				),
				'posts_per_page'	=> 10
	    	);
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) : $query->the_post();
	                get_template_part( 'content' );
				endwhile;
	        } else {
	            get_template_part( 'content', 'not-found' );
	        }
	    ?>
	</div>
</div><!--#content-->
<div class="sidebar span4">
<?php dynamic_sidebar( 'political-party-time' ); ?>
</div>
<?php get_footer(); ?>