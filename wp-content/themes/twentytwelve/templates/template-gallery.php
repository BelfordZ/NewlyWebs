<?php
/**
 * Template Name: Gallery
 */
?>
<?php get_header(); ?>

<style>
	.sortable {
		margin: auto;
		padding: 0;
		width: 875px;
	}
	.sortable.grid {
		overflow: hidden;
	}
	.sortable li {
		list-style: none;
		border: 0px solid #CCC;
		/*background: #F6F6F6;*/
		color: #1C94C4;
		margin: 5px;
		padding: 5px;
		/*height: 22px;*/
	}
	.sortable.grid li {
		/*line-height: 400px;*/
		float: left;
		width: 400px;
		/*height: 400px;*/
		height: auto;
		text-align: center;
	}
</style>

<div>
	<?php
	print get_formatted_content_by_post_name('gallery');
	?>
</div>

<div class="clear"></div>
<footer class="entry-meta">
	<?php myevent_edit_post_link( __( 'Edit', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?>
</footer>

<?php get_footer(); ?>