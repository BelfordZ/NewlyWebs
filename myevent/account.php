<?php
chdir("../");
//require('./wp-blog-header.php');
/** WordPress Administration Bootstrap */
require_once('./wp-admin/admin.php');

?>

<?php get_header(); ?>
<div id="wrapper"  class="clearfix">
<div id="page" class="container_16 clearfix " > 
		    
		    <h1 class="head">My Event</h1>
            
            <br><br>
<?php
//print $user_ID;

?>

<form method="post" action="../wp-admin/options.php">
<?php settings_fields('general'); ?>

<table class="form-table">
<tr valign="top">
<th scope="row"><label for="blogname"><?php _e('Site Title') ?></label></th>
<td><input name="blogname" type="text" id="blogname" value="<?php form_option('blogname'); ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="blogdescription"><?php _e('Tagline') ?></label></th>
<td><input name="blogdescription" type="text" id="blogdescription" value="<?php form_option('blogdescription'); ?>" class="regular-text" />
<p class="description"><?php _e('In a few words, explain what this site is about.') ?></p></td>
</tr>
</table>
<?php submit_button(); ?>
</form>


<br>



            
            

</div> <!-- page #end -->	
</div><!-- wrapper #end -->
<?php get_footer(); ?>