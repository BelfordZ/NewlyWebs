<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

			
			<?php
			
			$menu = wp_get_nav_menu_object('menu');

			$args = array(
			        'order'                  => 'ASC',
			        'orderby'                => 'menu_order',
			        'post_type'              => 'nav_menu_item',
			        'post_status'            => 'publish',
			        'output'                 => ARRAY_A,
			        'output_key'             => 'menu_order',
			        'nopaging'               => true,
			        'update_post_term_cache' => false );
			$pages = wp_get_nav_menu_items($menu->term_id, $args);

			/*
			print "<pre>";
			print_r($pages);
			print "</pre>";
			*/
			
			?>
			
			<?php
			foreach ($pages as $page) :
				//print $page->object_id;
				$page_obj = get_page($page->object_id);
				print $page_obj->post_content;
				print "<hr>";
			?>
			
			
			<?php endforeach; ?>
			
			

<?php //get_sidebar(); ?>
<?php get_footer(); ?>