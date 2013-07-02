<?php
chdir("../");
//require('./wp-blog-header.php');
/** WordPress Administration Bootstrap */
require_once('./wp-admin/admin.php');

// security check


if (isset($_POST['submit'])) {
	
	print_r($_POST);
	
	insert_or_update_option_by_option_name('blogname', $_POST['site_name']);
	insert_or_update_option_by_option_name('blogdescription', $_POST['site_tagline']);
	
	$myevent_events_array = prepare_events_array($_POST);
	insert_or_update_option_by_option_name('myevent_events', $myevent_events_array);
	
	/*
	update_option('myevent_date', $_POST['myevent_date']);
	
	$myevent_location_array = array(
		'venue' => $_POST['myevent_location'],
		'address' => $_POST['myevent_location_address'],
	);
	update_option('myevent_location', $myevent_location_array);
	*/
	
	insert_or_update_content_by_post_name('bride-story', $_POST['bride_story']);
	insert_or_update_content_by_post_name('groom-story', $_POST['groom_story']);
	insert_or_update_content_by_post_name('our-story', $_POST['our_story']);
	
	
	
}

?>
<?php


/*
print "<pre>";
print_r($wp_list_table);
print "</pre>";
*/

require_once('./myevent/admin-header.php');
?>
<?php get_header(); ?>
<div id="wrapper"  class="clearfix">
<div id="page" class="container_16 clearfix " > 
		    

<?php
//print $user_ID;

?>

<br class="clear" />

<form action="" method="POST">

Website Name: <input type="text" name="site_name" value="<?php print get_option('blogname'); ?>"><br>
Website Subheading: <input type="text" name="site_tagline" value="<?php print get_option('blogdescription'); ?>"><br>

<br>

<h1 class="head">My Event</h1>

<br><br>
<?php

$myevent_events_array = get_option('myevent_events');
if ($myevent_events_array['myevent_last_event_id']==NULL)
	$myevent_last_event_id = 0;
else
	$myevent_last_event_id = $myevent_events_array['myevent_last_event_id'];

/*
print "<pre>";
print_r ($myevent_events_array);
print "</pre>";
*/
//$myevent_last_event_id = get_option('myevent_last_event_id');

?>

<div id="myevent_events_div">
<input id="myevent_last_event_id" name="myevent_last_event_id" type="hidden" value="<?php print $myevent_last_event_id; ?>">
<?php

for ($i=1; $i<=$myevent_last_event_id; $i++) {
	
	//$myevent_event_array = get_option('myevent_event_'.$i);
	$myevent_event_array = $myevent_events_array[$i];
	
	if (!empty($myevent_event_array)) {
?>
<div id="myevent_event_<?php print $i; ?>" style="border-style:dashed; border-width:1px;">
<input type="hidden" name="myevent_event_type_<?php print $i; ?>" value="<?php print $myevent_event_array['type']; ?>">
<?php
//display_event_input_fields($myevent_event_array['type']);

switch ($myevent_event_array['type']) {
	case 'wedding_ceremony':
		display_event_input_fields_wedding_ceremony($myevent_event_array, $i);
		break;
	case 'wedding_reception':
		display_event_input_fields_wedding_reception($myevent_event_array, $i);
		break;
}

?>
<br>
<input type="button" id="delete_event_<?php print $i; ?>" value="Delete Event">
</div>
<br>
<?php
	}
}

?>
</div>

Event Type: 
<select name="event_type" id="event_type">
  <option value="wedding_ceremony">Wedding Ceremony</option>
  <option value="wedding_reception">Wedding Reception</option>
  <!--<option value="bridal_shower">Bridal Shower</option>
  <option value="bachelor_party">Bachelor Party</option>-->
  <option value="custom">Custom</option>
</select>
<input type="button" id="add_new_event" value="Add New Event">

<script>
jQuery(document).ready(function($) {
	$("#add_new_event").click(function() {
		
		var newEventId = parseInt(parseFloat($("#myevent_last_event_id").val()))+1;
		$("#myevent_last_event_id").val(newEventId);
		
		if ($("#event_type").val()=="wedding_ceremony") {
			$("#myevent_events_div").append('<div id="myevent_event_'+newEventId+'" style="border-style:dashed; border-width:1px;"><input type="hidden" name="myevent_event_type_'+newEventId+'" value="wedding_ceremony">Ceremony Date: <input type="text" name="myevent_date_'+newEventId+'" size="10"><br>Ceremony Time: <input type="text" name="myevent_time_'+newEventId+'" size="10"><br>Ceremony Location:<br>Venue: <input type="text" name="myevent_location_'+newEventId+'" size="30"><br>Address: <input type="text" name="myevent_location_address_'+newEventId+'" size="50"><br><input type="button" id="delete_event_'+newEventId+'" value="Delete Event"></div><br>');
		}
		if ($("#event_type").val()=="wedding_reception") {
			$("#myevent_events_div").append('<div id="myevent_event_'+newEventId+'" style="border-style:dashed; border-width:1px;"><input type="hidden" name="myevent_event_type_'+newEventId+'" value="wedding_reception">Reception Date: <input type="text" name="myevent_date_'+newEventId+'" size="10"><br>Reception Time: <input type="text" name="myevent_time_'+newEventId+'" size="10"><br>Reception Location:<br>Venue: <input type="text" name="myevent_location_'+newEventId+'" size="30"><br>Address: <input type="text" name="myevent_location_address_'+newEventId+'" size="50"><br><input type="button" id="delete_event_'+newEventId+'" value="Delete Event"></div><br>');
		}
		if ($("#event_type").val()=="custom") {
			$("#myevent_events_div").append('<div id="myevent_event_'+newEventId+'" style="border-style:dashed; border-width:1px;"><input type="hidden" name="myevent_event_type_'+newEventId+'" value="custom">      <br><input type="button" id="delete_event_'+newEventId+'" value="Delete Event"></div><br>');
		}
		
		
		$('[id^="delete_event_"]').click(function() {
			$(this).parent().remove();
		});
	});
	
	$('[id^="delete_event_"]').click(function() {
		$(this).parent().remove();
	});
	
});
</script>

<br>
Bride Story:<br>
<?php
$post = get_content_by_post_name('bride-story');

wp_editor($post->post_content, 'bride_story', array('dfw' => true, 'editor_height' => 180, 'media_buttons' => false, 'tinymce' => array(
	'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,outdent,indent,fontsizeselect,forecolor',
	'theme_advanced_buttons2' => '',
	'theme_advanced_buttons3' => ''
)) ); ?>
<br>
Groom Story:<br>
<?php
$post = get_content_by_post_name('groom-story');

wp_editor($post->post_content, 'groom_story', array('dfw' => true, 'editor_height' => 180, 'media_buttons' => false, 'tinymce' => array(
	'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,outdent,indent,fontsizeselect,forecolor',
	'theme_advanced_buttons2' => '',
	'theme_advanced_buttons3' => ''
)) ); ?>
<br>
Couple's Story:<br>
<?php
$post = get_content_by_post_name('our-story');

wp_editor($post->post_content, 'our_story', array('dfw' => true, 'editor_height' => 220, 'media_buttons' => false, 'tinymce' => array(
	'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,outdent,indent,fontsizeselect,forecolor',
	'theme_advanced_buttons2' => '',
	'theme_advanced_buttons3' => ''
)) ); ?>
<br>
<br>

<input type="submit" name="submit" value="Submit">
</form>

<br class="clear" />

<br>



            
            

</div> <!-- page #end -->	
</div><!-- wrapper #end -->
<?php
include('./myevent/admin-footer.php');
?>
<?php get_footer(); ?>