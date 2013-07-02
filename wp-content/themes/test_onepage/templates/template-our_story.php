<?php
/**
 * Template Name: Our Story
 */
?>
<?php //get_header(); ?>

<div>
	<?php
	print get_formatted_content_by_post_name('bride-groom-story-pic');
	?>
</div>

<div class="clear"></div>

<div style="float:left; padding: 10px;">
	<?php
	print get_formatted_content_by_post_name('bride-story');
	?>
</div>

<div style="float:left; padding: 10px;">
	<?php
	print get_formatted_content_by_post_name('groom-story');
	?>
</div>

<div class="clear"></div>
<footer class="entry-meta">
	<?php myevent_edit_post_link( __( 'Edit', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?>
</footer>

<?php //get_footer(); ?>