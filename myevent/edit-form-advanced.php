<?php
/**
 * Post advanced form for inclusion in the administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

wp_enqueue_script('post');

if ( wp_is_mobile() )
	wp_enqueue_script( 'jquery-touch-punch' );

/**
 * Post ID global
 * @name $post_ID
 * @var int
 */
$post_ID = isset($post_ID) ? (int) $post_ID : 0;
$user_ID = isset($user_ID) ? (int) $user_ID : 0;
$action = isset($action) ? $action : '';

if ( post_type_supports($post_type, 'editor') || post_type_supports($post_type, 'thumbnail') ) {
	add_thickbox();
	wp_enqueue_media( array( 'post' => $post_ID ) );
}

$messages = array();
$messages['post'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Post updated. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Post updated.'),
	/* translators: %s: date and time of the revision */
	 5 => isset($_GET['revision']) ? sprintf( __('Post restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Post published. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Post saved.'),
	 8 => sprintf( __('Post submitted. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Post draft updated. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);
$messages['page'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Page updated. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Page updated.'),
	 5 => isset($_GET['revision']) ? sprintf( __('Page restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Page published. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Page saved.'),
	 8 => sprintf( __('Page submitted. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Page scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Page draft updated. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);
$messages['attachment'] = array_fill( 1, 10, __( 'Media attachment updated.' ) ); // Hack, for now.

$messages = apply_filters( 'post_updated_messages', $messages );

$message = false;
if ( isset($_GET['message']) ) {
	$_GET['message'] = absint( $_GET['message'] );
	if ( isset($messages[$post_type][$_GET['message']]) )
		$message = $messages[$post_type][$_GET['message']];
	elseif ( !isset($messages[$post_type]) && isset($messages['post'][$_GET['message']]) )
		$message = $messages['post'][$_GET['message']];
}

$notice = false;
$form_extra = '';
if ( 'auto-draft' == $post->post_status ) {
	if ( 'edit' == $action )
		$post->post_title = '';
	$autosave = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave( $post_ID );
}

$form_action = 'editpost';
$nonce_action = 'update-post_' . $post_ID;
$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr($post_ID) . "' />";

// Detect if there exists an autosave newer than the post and if that autosave is different than the post
if ( $autosave && mysql2date( 'U', $autosave->post_modified_gmt, false ) > mysql2date( 'U', $post->post_modified_gmt, false ) ) {
	foreach ( _wp_post_revision_fields() as $autosave_field => $_autosave_field ) {
		if ( normalize_whitespace( $autosave->$autosave_field ) != normalize_whitespace( $post->$autosave_field ) ) {
			$notice = sprintf( __( 'There is an autosave of this post that is more recent than the version below. <a href="%s">View the autosave</a>' ), get_edit_post_link( $autosave->ID ) );
			break;
		}
	}
	unset($autosave_field, $_autosave_field);
}

$post_type_object = get_post_type_object($post_type);

// All meta boxes should be defined and added before the first do_meta_boxes() call (or potentially during the do_meta_boxes action).
require_once('./myevent/includes/meta-boxes.php');

if ( 'attachment' == $post_type ) {
	wp_enqueue_script( 'image-edit' );
	wp_enqueue_style( 'imgareaselect' );
	add_meta_box( 'submitdiv', __('Save'), 'attachment_submit_meta_box', null, 'normal', 'core' );
	add_action( 'edit_form_after_title', 'edit_form_image_editor' );
} else {
	add_meta_box( 'submitdiv', __( ' ' ), 'post_submit_meta_box', null, 'normal', 'core' );
}

if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post_type, 'post-formats' ) )
	add_meta_box( 'formatdiv', _x( 'Format', 'post format' ), 'post_format_meta_box', null, 'normal', 'core' );

// all taxonomies
foreach ( get_object_taxonomies( $post ) as $tax_name ) {
	$taxonomy = get_taxonomy($tax_name);
	if ( ! $taxonomy->show_ui )
		continue;

	$label = $taxonomy->labels->name;

	if ( !is_taxonomy_hierarchical($tax_name) )
		add_meta_box('tagsdiv-' . $tax_name, $label, 'post_tags_meta_box', null, 'normal', 'core', array( 'taxonomy' => $tax_name ));
	else
		add_meta_box($tax_name . 'div', $label, 'post_categories_meta_box', null, 'normal', 'core', array( 'taxonomy' => $tax_name ));
}

/*
if ( post_type_supports($post_type, 'page-attributes') )
	add_meta_box('pageparentdiv', 'page' == $post_type ? __('Page Attributes') : __('Attributes'), 'page_attributes_meta_box', null, 'side', 'core');

if ( current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports( $post_type, 'thumbnail' ) )
		add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', null, 'side', 'low');

if ( post_type_supports($post_type, 'excerpt') )
	add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', null, 'normal', 'core');

if ( post_type_supports($post_type, 'trackbacks') )
	add_meta_box('trackbacksdiv', __('Send Trackbacks'), 'post_trackback_meta_box', null, 'normal', 'core');

if ( post_type_supports($post_type, 'custom-fields') )
	add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', null, 'normal', 'core');

do_action('dbx_post_advanced');
if ( post_type_supports($post_type, 'comments') )
	add_meta_box('commentstatusdiv', __('Discussion'), 'post_comment_status_meta_box', null, 'normal', 'core');

if ( ( 'publish' == get_post_status( $post ) || 'private' == get_post_status( $post ) ) && post_type_supports($post_type, 'comments') )
	add_meta_box('commentsdiv', __('Comments'), 'post_comment_meta_box', null, 'normal', 'core');

if ( ! ( 'pending' == get_post_status( $post ) && ! current_user_can( $post_type_object->cap->publish_posts ) ) )
	add_meta_box('slugdiv', __('Slug'), 'post_slug_meta_box', null, 'normal', 'core');

if ( post_type_supports($post_type, 'author') ) {
	if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) )
		add_meta_box('authordiv', __('Author'), 'post_author_meta_box', null, 'normal', 'core');
}

if ( post_type_supports($post_type, 'revisions') && 0 < $post_ID && wp_get_post_revisions( $post_ID ) )
	add_meta_box('revisionsdiv', __('Revisions'), 'post_revisions_meta_box', null, 'normal', 'core');
*/

do_action('add_meta_boxes', $post_type, $post);
do_action('add_meta_boxes_' . $post_type, $post);

do_action('do_meta_boxes', $post_type, 'normal', $post);
do_action('do_meta_boxes', $post_type, 'advanced', $post);
do_action('do_meta_boxes', $post_type, 'side', $post);

add_screen_option('layout_columns', array('max' => 1, 'default' => 1) );


require_once('./myevent/admin-header.php');
?>
<?php get_header(); ?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>
<?php
/*
<h2><?php
if ( isset( $post_new_file ) && current_user_can( $post_type_object->cap->create_posts ) )
	echo ' <a href="' . esc_url( $post_new_file ) . '" class="add-new-h2">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
?></h2>
*/
?>
<?php /* if ( $notice ) : ?>
<div id="notice" class="error"><p><?php echo $notice ?></p></div>
<?php endif; */ ?>
<?php if ( $message ) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<form enctype="multipart/form-data" name="post" action="post.php" method="post" id="post"<?php do_action('post_edit_form_tag'); ?>>
<?php wp_nonce_field($nonce_action); ?>
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ) ?>" />
<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ) ?>" />
<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post_type ) ?>" />
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status) ?>" />
<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />
<?php if ( ! empty( $active_post_lock ) ) { ?>
<input type="hidden" id="active_post_lock" value="<?php echo esc_attr( implode( ':', $active_post_lock ) ); ?>" />
<?php
}
if ( 'draft' != get_post_status( $post ) )
	wp_original_referer_field(true, 'previous');

echo $form_extra;

wp_nonce_field( 'autosave', 'autosavenonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>

<div id="poststuff">

<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
<div id="post-body-content">
<?php if ( post_type_supports($post_type, 'title') ) { ?>
<div id="titlediv">
<div id="titlewrap">
	<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo apply_filters( 'enter_title_here', __( 'Enter title here' ), $post ); ?></label>
	<input type="text" name="post_title" size="30" value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />
</div>
<?php
/*
<div class="inside">
<?php
$sample_permalink_html = $post_type_object->public ? get_sample_permalink_html($post->ID) : '';
$shortlink = wp_get_shortlink($post->ID, 'post');
if ( !empty($shortlink) )
    $sample_permalink_html .= '<input id="shortlink" type="hidden" value="' . esc_attr($shortlink) . '" /><a href="#" class="button button-small" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val()); return false;">' . __('Get Shortlink') . '</a>';

if ( $post_type_object->public && ! ( 'pending' == get_post_status( $post ) && !current_user_can( $post_type_object->cap->publish_posts ) ) ) { ?>
	<div id="edit-slug-box" class="hide-if-no-js">
	<?php
		if ( $sample_permalink_html && 'auto-draft' != $post->post_status )
			echo $sample_permalink_html;
	?>
	</div>
<?php
}
?>
</div>
*/
?>
<?php
wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );
?>
</div><!-- /titlediv -->
<?php
}

do_action( 'edit_form_after_title' );

if ( post_type_supports($post_type, 'editor') ) {
?>
<div id="postdivrich" class="postarea">
	
<?php
// Get template editor(s) for the template page
$post_page_template = get_post_meta($post_id, '_wp_page_template', true);

if (file_exists(get_template_directory().'/templates_editor/'.$post_page_template) && !empty($post_page_template))
	include(get_template_directory().'/templates_editor/'.$post_page_template);
?>

<?php
if ($override_default_editor) {
	display_template_editor();
} else {
	wp_editor($post->post_content, 'content', array('dfw' => true, 'tabfocus_elements' => 'sample-permalink,post-preview', 'editor_height' => 360, 'tinymce' => array(
	'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,outdent,indent,fontsizeselect,forecolor',
	'theme_advanced_buttons2' => '',
	'theme_advanced_buttons3' => ''
)) );
}
?>

<?php
/*
<table id="post-status-info" cellspacing="0"><tbody><tr>
	<td id="wp-word-count"><?php printf( __( 'Word count: %s' ), '<span class="word-count">0</span>' ); ?></td>
	<td class="autosave-info">
	<span class="autosave-message">&nbsp;</span>
<?php
	if ( 'auto-draft' != $post->post_status ) {
		echo '<span id="last-edit">';
		if ( $last_id = get_post_meta($post_ID, '_edit_last', true) ) {
			$last_user = get_userdata($last_id);
			printf(__('Last edited by %1$s on %2$s at %3$s'), esc_html( $last_user->display_name ), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		} else {
			printf(__('Last edited on %1$s at %2$s'), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		}
		echo '</span>';
	} ?>
	</td>
</tr></tbody></table>
*/
?>

</div>
<?php } ?>

<?php do_action( 'edit_form_after_editor' ); ?>
</div><!-- /post-body-content -->

<div id="postbox-container-1" class="postbox-container">
<?php

if ( 'page' == $post_type )
	do_action('submitpage_box');
else
	do_action('submitpost_box');

do_meta_boxes($post_type, 'normal', $post);

?>
</div>
<div id="postbox-container-2" class="postbox-container">
<?php

do_meta_boxes(null, 'normal', $post);

if ( 'page' == $post_type )
	do_action('edit_page_form');
else
	do_action('edit_form_advanced');

do_meta_boxes(null, 'advanced', $post);

?>
</div>
<?php

do_action('dbx_post_sidebar');

?>
</div><!-- /post-body -->
<br class="clear" />
</div><!-- /poststuff -->
</form>
</div>

<?php
if ( post_type_supports( $post_type, 'comments' ) )
	wp_comment_reply();
?>

<?php if ( (isset($post->post_title) && '' == $post->post_title) || (isset($_GET['message']) && 2 > $_GET['message']) ) : ?>
<script type="text/javascript">
try{document.post.title.focus();}catch(e){}
</script>
<?php endif; ?>
