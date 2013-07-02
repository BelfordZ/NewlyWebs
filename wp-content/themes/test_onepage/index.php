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
				/*
				print "<pre>";
				print_r($page);
				print "</pre>";
				*/
				//print $page->object_id;
				$page_id = $page->object_id;
				$page_obj = get_page($page_id);
				/*
				print "<pre>";
				print_r($page_obj);
				print "</pre>";
				*/
				//print $page_obj->post_content;
				$post_meta = get_post_meta($page_id);
				
				/*
				print "<pre>";
				print_r($post_meta['_wp_page_template']);
				print "</pre>";
				*/
				
				if ($post_meta['_wp_page_template'][0] == 'default' || empty($post_meta['_wp_page_template'][0]))
					$page_template = "page.php";
				else
					$page_template = $post_meta['_wp_page_template'][0];
				
				//print $page_template;
				
				if ( $page_template_file = locate_template($page_template,false,false) ) {
					
					// create anchor name for each section 
					print '<a name="' . $page_obj->post_name . '"></a>';
					query_posts( 'page_id='.$page_id );
					load_template($page_template_file, false);
					
				}
				print "
				<div class=\"clear\"></div>
				<hr>";
			?>
			
			
			<?php endforeach; ?>
			
			

<?php //get_sidebar(); ?>
<?php get_footer(); ?>