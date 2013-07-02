<?php
/**
 * WordPress Administration Template Footer
 *
 * @package WordPress
 * @subpackage Administration
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<?php
/*
<div id="wpfooter">
<?php do_action( 'in_admin_footer' ); ?>
<p id="footer-upgrade" class="alignright"><?php echo apply_filters( 'update_footer', '' ); ?></p>
<div class="clear"></div>
</div>
*/
?>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
do_action("admin_footer-" . $GLOBALS['hook_suffix']);

// get_site_option() won't exist when auto upgrading from <= 2.7
if ( function_exists('get_site_option') ) {
	if ( false === get_site_option('can_compress_scripts') )
		compression_test();
}

?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
