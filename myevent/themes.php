<?php
chdir("../");
//require('./wp-blog-header.php');
/** WordPress Administration Bootstrap */
require_once('./wp-admin/admin.php');

?>
<?php
if ( !current_user_can('switch_themes') && !current_user_can('edit_theme_options') )
	wp_die( __( 'Cheatin&#8217; uh?' ) );

$wp_list_table = _get_list_table('WP_Themes_List_Table');

if ( current_user_can( 'switch_themes' ) && isset($_GET['action'] ) ) {
	if ( 'activate' == $_GET['action'] ) {
		check_admin_referer('switch-theme_' . $_GET['stylesheet']);
		$theme = wp_get_theme( $_GET['stylesheet'] );
		if ( ! $theme->exists() || ! $theme->is_allowed() )
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		switch_theme( $theme->get_stylesheet() );
		wp_redirect( site_url('myevent/themes.php?activated=true') );
		exit;
	}
}

$wp_list_table->prepare_items();

/*
print "<pre>";
print_r($wp_list_table);
print "</pre>";
*/
?>

<?php get_header(); ?>
<div id="wrapper"  class="clearfix">
<div id="page" class="container_16 clearfix " > 
		    
		    <h1 class="head">My Event</h1>
            
            <br><br>
<?php
//print $user_ID;

?>

<br class="clear" />
<?php
if ( ! current_user_can( 'switch_themes' ) ) {
	echo '</div>';
	require( './admin-footer.php' );
	exit;
}
?>

<h3 class="available-themes"><?php _e('Available Themes'); ?></h3>

<br class="clear" />

<?php //$wp_list_table->display(); ?>

<?php

foreach ($wp_list_table->items as $theme) :
	/*
	print "<pre>";
	print_r($theme);
	print "</pre>";
	*/
?>
<div style="float:left; padding:10px;">
<img src="<?php print $theme->get_screenshot(); ?>" width="300" /><br>
<?php print wp_get_theme($theme->get_stylesheet())->Name; ?><br>
<a href="<?php print wp_nonce_url(site_url('myevent/themes.php?action=activate&template='.$theme->get_template().'&stylesheet='.$theme->get_stylesheet()), 'switch-theme_'.$theme->get_stylesheet()); ?>">Activate</a>
</div>

<?php endforeach; ?>

<br class="clear" />

<br>



            
            

</div> <!-- page #end -->	
</div><!-- wrapper #end -->
<?php get_footer(); ?>