<header class="archive-background clearfix">
	<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/whoyakidding_logo.jpg">
</header>

<?php
	// I'm so, so sorry.
	$ids = array();
	$args = array(
	    'tax_query' => array(
			array(
				'taxonomy' 	=> 'series',
				'field' 	=> 'slug',
				'terms' 	=> 'who-ya-kidding'
			)
		),
		'posts_per_page'	=> -1
	);
	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) : $query->the_post();
			$ids[] = $post->ID;
		endwhile;
	}

	wp_reset_postdata();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'hnews whoyakidding' ); ?> itemscope itemtype="http://schema.org/Article">

	<header>
 		<h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>
 		<h5 class="byline"><?php largo_byline(); ?></h5>

 		<meta itemprop="description" content="<?php echo strip_tags(largo_excerpt( $post, 5, false, '', false ) ); ?>" />
 		<meta itemprop="datePublished" content="<?php echo get_the_date( 'c' ); ?>" />
 		<meta itemprop="dateModified" content="<?php echo get_the_modified_date( 'c' ); ?>" />
	</header><!-- / entry header -->

	<div class="entry-content clearfix" itemprop="articleBody">
		<?php largo_entry_content( $post ); ?>
	</div>
	<!-- .entry-content -->

	<div class="submit">
		<a href="/tips/"><img class="left" alt="..." src="/files/2014/02/WhoYaKidding_the_quote_small.png" /></a>
		<p><em>Have you got a quote or statement you think would be appropriate for this column?<br />
		<a href="/tips/">Send it in</a> &ndash; and let us know whether we should use
your name if we publish it.</em></p>
	</div>

	<footer class="post-meta bottom-meta">
		<nav id="nav-below" class="post-nav clearfix">
			<a class="left desc-collapse" data-toggle="collapse" data-target=".desc-collapse" title="">about this column</a>
		<?php
			$thisindex = array_search( $post->ID, $ids );

            if ( $thisindex != 0 ) {
				$previd = $ids[$thisindex - 1];
				if ( ! empty( $previd ) ) {
				   echo '<a rel="prev" href="' . get_permalink( $previd ). '">previous entry</a>';
				}
			}

			echo '<a rel="all" href="/series/who-ya-kidding/">column home</a>';

			if ( $thisindex < ( count( $ids ) - 1 ) ) {
				$nextid = $ids[$thisindex + 1];
				if ( ! empty( $nextid ) ) {
					echo '<a rel="next" href="' . get_permalink( $nextid ). '">next entry</a>';
				}
			}

		?>
		</nav>
	</footer><!-- /.post-meta -->

	<div class="archive-description">
		<div class="desc-collapse">
			<p>The Maine Center for Public Interest Reporting specializes in watchdog reporting, but as much as our government’s policies and actions need watching, so does their language. During the Vietnam war, we had  “protective air strikes” -- doublespeak for bombing villages. And during the 2004 presidential election, John Kerry gave this twisted explanation of his vote on a military spending bill: "I actually did vote for the $87 billion before I voted against it."</p>

			<p>This new feature of pinetreewatchdog will call attention to fresh cases of the “pure wind” that blows regularly from our public figures: politicians, business people, commentators and others “practiced in the art of deception.”</p>

			<p>As usual, we aim to be equal opportunity offenders. Because no party, no ideology, no special interest is immune from spin, obfuscation, weasel words, cant, misdirection, euphemisms, flummery, cheap shots and other forms of BS.</p>

			<p>&mdash; John Christie, editor-in-chief</p>
		</div>
	</div>

</article>
