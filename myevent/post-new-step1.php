<?php
/**
 * New Post Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

chdir("../");
/** WordPress Administration Bootstrap */
require_once( './wp-admin/admin.php' );

if ( !isset($_GET['post_type']) )
	$post_type = 'post';
elseif ( in_array( $_GET['post_type'], get_post_types( array('show_ui' => true ) ) ) )
	$post_type = $_GET['post_type'];
else
	wp_die( __('Invalid post type') );

$post_type_object = get_post_type_object( $post_type );

if ( 'post' == $post_type ) {
	$parent_file = 'edit.php';
	$submenu_file = 'post-new.php';
} elseif ( 'attachment' == $post_type ) {
	wp_redirect( admin_url( 'media-new.php' ) );
	exit;
} else {
	$submenu_file = "post-new.php?post_type=$post_type";
	if ( isset( $post_type_object ) && $post_type_object->show_in_menu && $post_type_object->show_in_menu !== true ) {
		$parent_file = $post_type_object->show_in_menu;
		if ( ! isset( $_registered_pages[ get_plugin_page_hookname( "post-new.php?post_type=$post_type", $post_type_object->show_in_menu ) ] ) )
			$submenu_file = $parent_file;
	} else {
		$parent_file = "edit.php?post_type=$post_type";
	}
}

$title = $post_type_object->labels->add_new_item;

$editing = true;

if ( ! current_user_can( $post_type_object->cap->edit_posts ) || ! current_user_can( $post_type_object->cap->create_posts ) )
	wp_die( __( 'Cheatin&#8217; uh?' ) );

// Schedule auto-draft cleanup
/*
if ( ! wp_next_scheduled( 'wp_scheduled_auto_draft_delete' ) )
	wp_schedule_event( time(), 'daily', 'wp_scheduled_auto_draft_delete' );

wp_enqueue_script( 'autosave' );
*/

// Show post form.
$post = get_default_post_to_edit( $post_type, true );
$post_ID = $post->ID;
//include('edit-form-advanced.php');
?>
<?php
/********START of edit-form-advanced.php********/
?>
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


$post_type_object = get_post_type_object($post_type);

// All meta boxes should be defined and added before the first do_meta_boxes() call (or potentially during the do_meta_boxes action).
//require_once('./myevent/includes/meta-boxes.php');
?>
<?php
/********START of includes/meta-boxes.php********/
/**
 * Display post submit form fields.
 *
 * @since 2.7.0
 *
 * @param object $post
 */
function post_submit_meta_box($post) {
	global $action;

	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
?>
<div class="submitbox" id="submitpost">

<div id="minor-publishing">

<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
<div style="display:none;">
<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
</div>

<div id="minor-publishing-actions">
<div id="save-action">

<span class="spinner"></span>
</div>

<div id="misc-publishing-actions">

<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
<input type="hidden" name="post_status" id="post_status" value="publish" />


<?php do_action('post_submitbox_misc_actions'); ?>
</div>
<div class="clear"></div>
</div>

<div id="major-publishing-actions">
<?php do_action('post_submitbox_start'); ?>

<?php /*<div id="publishing-action">
<span class="spinner"></span>*/ ?>
<?php
if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
	if ( $can_publish ) :
		if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
		<?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	else : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
		<?php submit_button( __( 'Next' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	endif;
	else : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
		<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php
	endif;
}
?>
<?php /*</div>*/ ?>
<div class="clear"></div>
</div>
</div>

<?php
}

/**
 * Display page attributes form fields.
 *
 * @since 2.7.0
 *
 * @param object $post
 */
function page_attributes_meta_box($post) {
	$post_type_object = get_post_type_object($post->post_type);
/*
	if ( $post_type_object->hierarchical ) {
		$dropdown_args = array(
			'post_type'        => $post->post_type,
			'exclude_tree'     => $post->ID,
			'selected'         => $post->post_parent,
			'name'             => 'parent_id',
			'show_option_none' => __('(no parent)'),
			'sort_column'      => 'menu_order, post_title',
			'echo'             => 0,
		);

		$dropdown_args = apply_filters( 'page_attributes_dropdown_pages_args', $dropdown_args, $post );
		$pages = wp_dropdown_pages( $dropdown_args );
		if ( ! empty($pages) ) {
?>
<p><strong><?php _e('Parent') ?></strong></p>
<label class="screen-reader-text" for="parent_id"><?php _e('Parent') ?></label>
<?php echo $pages; ?>
<?php
		} // end empty pages check
	} // end hierarchical check.
*/
	if ( 'page' == $post->post_type && 0 != count( get_page_templates() ) ) {
		$template = !empty($post->page_template) ? $post->page_template : false;
?>
<p><strong><?php _e('Page Template') ?></strong></p>
<label class="screen-reader-text" for="page_template"><?php _e('Page Template') ?></label><select name="page_template" id="page_template">
<option value='default'><?php _e('Default Template'); ?></option>
<?php myevent_page_template_dropdown($template); ?>
</select>
<?php
	} ?>
<input name="menu_order" type="hidden" id="menu_order" value="<?php echo esc_attr($post->menu_order) ?>" />
<?php
}

/********END of includes/meta-boxes.php********/
?>
<?php

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

if ( post_type_supports($post_type, 'page-attributes') )
	add_meta_box('pageparentdiv', 'page' == $post_type ? __(' ') : __('Attributes'), 'page_attributes_meta_box', null, 'normal', 'core');

// "next" button
add_meta_box( 'submitdiv', __( ' ' ), 'post_submit_meta_box', null, 'normal', 'core' );


do_action('add_meta_boxes', $post_type, $post);
do_action('add_meta_boxes_' . $post_type, $post);

do_action('do_meta_boxes', $post_type, 'normal', $post);
do_action('do_meta_boxes', $post_type, 'advanced', $post);
//do_action('do_meta_boxes', $post_type, 'side', $post);

add_screen_option('layout_columns', array('max' => 1, 'default' => 1) );


require_once('./myevent/admin-header.php');
?>
<?php get_header(); ?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<br>

<form name="post" action="post.php" method="post" id="post"<?php do_action('post_edit_form_tag'); ?>>
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
	<p><strong><?php _e('Page Title') ?></strong></p>
	<input type="text" name="post_title" size="30" value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />
</div>

<?php
wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );
?>
</div><!-- /titlediv -->
<?php
}

do_action( 'edit_form_after_title' );
?>

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
<?php
/********END of edit-form-advanced.php********/
?>
<?php
include('./myevent/admin-footer.php');
?>
<?php get_footer(); ?>