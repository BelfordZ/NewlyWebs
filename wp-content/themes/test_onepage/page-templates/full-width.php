<?php
/**
 * Template Name: Full-width Page Template, No Sidebar
 *
 * Description: Twenty Twelve loves the no-sidebar look as much as
 * you do. Use this page template to remove the sidebar from any page.
 *
 * Tip: to remove the sidebar from all posts and pages simply remove
 * any active widgets from the Main Sidebar area, and the sidebar will
 * disappear everywhere.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

//get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php //while ( have_posts() ) : the_post(); ?>
				<?php //get_template_part( 'content', 'page' ); ?>
				<?php //comments_template( '', true ); ?>
			<?php //endwhile; // end of the loop. ?>
			
			<?php
			//print_r ($wp_query);
			//query_posts( 'page_id=2' );
			//print $post->ID;
			//print "<br>";
			//print_r ($wp_query);
			//print_r($post);
			//print "<br>";
			//$post = get_post(2);
			//print_r($post);
			the_post();
			//$post = get_post(2);
			//print $post->ID;
			//$post = get_post(2);
			the_content();
			//print $post->ID;
			//print_r($post);
			?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php //get_footer(); ?>