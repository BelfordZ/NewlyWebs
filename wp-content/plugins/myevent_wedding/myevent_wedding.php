<?php
/*
Plugin Name: MyEvent Wedding Event Site Plugin
Plugin URI: http://narmo.com
Description: 
Version: 1
Author: Narmo
Author URI: http://narmo.com
*/


add_action( 'init', 'create_post_type' );
function create_post_type() {
	register_post_type( 'content',
		array(
			'labels' => array(
				'name' => __( 'Contents' ),
				'singular_name' => __( 'Content' )
			),
		'public' => false,
		)
	);
}


function get_content_id_by_post_name($post_name) {
	global $wpdb;
	
	$query = $wpdb->prepare(
	        'SELECT ID FROM ' . $wpdb->posts . '
	        WHERE post_name = %s
	        AND post_type = \'content\'',
	        $post_name
	);
	$post_array = $wpdb->get_results( $query );
	
	if (!empty($post_array))
		return $post_array[0]->ID;
	else
		return null;
}

function get_content_by_post_name($post_name) {
	global $wpdb;
	
	$post_id = get_content_id_by_post_name($post_name);
	
	if (!empty($post_id))
		return get_post($post_id);
	else
		return null;
}

function get_formatted_content_by_post_name($post_name, $editor_mode=false) {
	$post = get_content_by_post_name($post_name);
	$content = $post->post_content;
	
	// check for short codes
	$content = shortcode_covert_to_display($content, $editor_mode);

	
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}

function shortcode_covert_to_display($content, $editor_mode=false) {
	
	$preg_needle = '/\[([a-zA-Z0-9_]+):::([0-9,]+)\]/i';

	preg_match_all($preg_needle, $content, $matches);

	$shortcode_name = $matches[1][0];
	$shortcode_id = $matches[2][0];
	/*
	print "<pre>";
	print_r($matches);
	print "</pre>";
	*/

	$needle = '[' . $shortcode_name . ':::';
	//$preg_needle = '/\[img:::([0-9,]+)\]/i';
	//preg_match_all($preg_needle, $content, $matches);

	if (strpos($content, $needle) !== false) {
	    //preg_match_all($preg_needle, $content, $matches);
	    //foreach ($matches[0] as $match) {
	        //$shortcode_id = str_replace('[img:::', '', $match);
	        //$shortcode_id = str_replace(']', '', $shortcode_id);

	        //$shortcode_ids = explode(',', $shortcode_id);

	        //for ($i=0; $i<count($shortcode_ids); $i++) {
				if (!$editor_mode)
					$func = 'shortcode_covert_to_display_'.$shortcode_name;
				else
					$func = 'shortcode_covert_to_editor_display_'.$shortcode_name;
				
				$formatted_display = $func($shortcode_id);
	        //}

	        $content = str_replace('[' . $shortcode_name . ':::' . $shortcode_id . ']', $formatted_display, $content);

		//}
	}

	//$content = $post_text;
	
	return $content;
}

function shortcode_covert_to_display_img($content_id) {
	$content = "";
	
	$content_ids = explode(',', $content_id);
	
    for ($i=0; $i<count($content_ids); $i++) {
		
		$image_attributes = wp_get_attachment_image_src($content_ids[$i], 'large');
		
		$content .= '<img src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'">';
		
	}
	
	return $content;
}

function shortcode_covert_to_display_myevent_gallery($content_id) {
	$content = "";
	
	$myevent_gallery = get_post_meta($content_id, 'myevent_gallery');
	
	$content .= '<ul class="sortable grid" id="gallery_grid">';
	
	for ($i=0; $i<count($myevent_gallery[0]); $i++) {
		
		$image_attributes = wp_get_attachment_image_src($myevent_gallery[0][$i], array(400,400));
		
		if ($image_attributes != null)
			$content .= '<li id="' . $myevent_gallery[0][$i] . '"><img src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'"></li>';
		
	}
	
	$content .= '</ul>';
	
	return $content;
}

function shortcode_covert_to_editor_display_myevent_gallery($content_id) {
	$content = "";
	
	$myevent_gallery = get_post_meta($content_id, 'myevent_gallery');
	
	$content .= '<ul class="sortable grid" id="gallery_grid">';
	
	for ($i=0; $i<count($myevent_gallery[0]); $i++) {
		
		$image_attributes = wp_get_attachment_image_src($myevent_gallery[0][$i], array(200,200));
		
		if ($image_attributes != null)
			$content .= '<li id="' . $myevent_gallery[0][$i] . '"><img src="'.$image_attributes[0].'" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'"><input type="checkbox" name="delete-' . $myevent_gallery[0][$i] . '" value="y">Delete Photo?</li>';
		
	}
	
	$content .= '</ul>';
	
	return $content;
}

// check if the content exists
function insert_or_update_content_by_post_name($post_name, $content) {
	global $wpdb, $user_ID;
	
	$post_id = get_content_id_by_post_name($post_name);
	
	if (!empty($post_id)) {
		$my_post = array(
		  'ID'    => $post_id,
		  'post_content'  => $content,
		);
		wp_update_post( $my_post );
	} else {
		// Create post object
		$my_post = array(
		  'post_title'    => $post_name,
		  'post_content'  => $content,
		  'post_status'   => 'publish',
		  'post_author'   => $user_ID,
		  'post_name'     => $post_name,
		  'post_type'     => 'content',
		);
		// Insert the post into the database
		wp_insert_post( $my_post );
	}
}

function delete_content_by_post_name($post_name) {
	global $wpdb, $user_ID;
		
	$post_id = get_content_id_by_post_name($post_name);
	
	if (!empty($post_id)) {
		wp_delete_post( $post_id, true );
	}
}

/***************************************************************************/
function insert_or_update_option_by_option_name($option_name, $option_value) {
	//global $wpdb;
	
	$option_value_array = get_option($option_name);
	
	if (!$option_value_array) {
		add_option($option_name, $option_value);
	} else {
		update_option($option_name, $option_value);
		
	}
	
	
}

/*
function display_event_input_fields($event_type) {
	
	switch ($event_type) {
		case 'wedding_ceremony':
			display_event_input_fields_wedding_ceremony($myevent_event_array, $i);
			break;
		case 'wedding_reception':
			display_event_input_fields_wedding_reception($myevent_event_array, $i);
			break;
	}
	
}
*/

function display_event_input_fields_wedding_ceremony($myevent_event_array, $event_id) {
	
	print <<<HTML
	
	Wedding Date: <input type="text" name="myevent_date_{$event_id}" size="10" value="{$myevent_event_array['date']}">
	<br>
	Wedding Time: <input type="text" name="myevent_time_{$event_id}" size="10" value="{$myevent_event_array['time']}">
	<br>
	Wedding Location:<br>
	Venue: <input type="text" name="myevent_location_{$event_id}" size="30" value="{$myevent_event_array['venue']}">
	<br>
	Address: <input type="text" name="myevent_location_address_{$event_id}" size="50" value="{$myevent_event_array['address']}">
	
HTML;
	
}

/*
function prepare_events_array_1($post_array) {
	
	for ($i=1; $i<=$post_array['myevent_last_event_id']; $i++) {
		
		if (!empty($post_array['myevent_date_'.$i])||
			!empty($post_array['myevent_time_'.$i])||
			!empty($post_array['myevent_location_'.$i])||
			!empty($post_array['myevent_location_address_'.$i]))
		{
			$temp_array = array(
				'type' => $post_array['myevent_event_type_'.$i],
				'date' => $post_array['myevent_date_'.$i],
				'time' => $post_array['myevent_time_'.$i],
				'venue' => $post_array['myevent_location_'.$i],
				'address' => $post_array['myevent_location_address_'.$i],
			);
			$myevent_events_array[$i] = $temp_array;
			$myevent_events_array['myevent_last_event_id'] = $i;
		}
		
	}
	
	return $myevent_events_array;
}
*/

function display_event_input_fields_wedding_reception($myevent_event_array, $event_id) {
	
	print <<<HTML
	
	Reception Date: <input type="text" name="myevent_date_{$event_id}" size="10" value="{$myevent_event_array['date']}">
	<br>
	Reception Time: <input type="text" name="myevent_time_{$event_id}" size="10" value="{$myevent_event_array['time']}">
	<br>
	Reception Location:<br>
	Venue: <input type="text" name="myevent_location_{$event_id}" size="30" value="{$myevent_event_array['venue']}">
	<br>
	Address: <input type="text" name="myevent_location_address_{$event_id}" size="50" value="{$myevent_event_array['address']}">
	
HTML;
	
}

function prepare_events_array($post_array) {
	/*
	switch ($event_type) {
		case 'wedding_ceremony':
			prepare_events_array_1($post_array);
			break;
		case 'wedding_reception':
			prepare_events_array_1($post_array);
			break;
	}
	*/
	
	$myevent_events_array = array();
	
	for ($i=1; $i<=$post_array['myevent_last_event_id']; $i++) {
		
		if (!empty($post_array['myevent_date_'.$i])||
			!empty($post_array['myevent_time_'.$i])||
			!empty($post_array['myevent_location_'.$i])||
			!empty($post_array['myevent_location_address_'.$i]))
		{
			$temp_array = array(
				'type' => $post_array['myevent_event_type_'.$i],
				'date' => $post_array['myevent_date_'.$i],
				'time' => $post_array['myevent_time_'.$i],
				'venue' => $post_array['myevent_location_'.$i],
				'address' => $post_array['myevent_location_address_'.$i],
			);
			$myevent_events_array[$i] = $temp_array;
			$myevent_events_array['myevent_last_event_id'] = $i;
		}
		
	}
	
	return $myevent_events_array;
	
}

/********************************************************************************/
// Overwrite Wordpress default functions
/********************************************************************************/

function myevent_edit_post_link( $link = null, $before = '', $after = '', $id = 0 ) {
	if ( !$post = get_post( $id ) )
		return;

	if ( !$url = get_edit_post_link( $post->ID ) )
		return;

	if ( null === $link )
		$link = __('Edit This');
	
	$url = str_replace("wp-admin", "myevent", $url);
	
	$post_type_obj = get_post_type_object( $post->post_type );
	$link = '<a class="post-edit-link" href="' . $url . '" title="' . esc_attr( $post_type_obj->labels->edit_item ) . '">' . $link . '</a>';
	echo $before . apply_filters( 'edit_post_link', $link, $post->ID ) . $after;
}

/*
function myevent_edit_post_link( $link = null, $before = '', $after = '', $id = 0 ) {
	if ( !$post = get_post( $id ) )
		return;

	if ( !$url = get_edit_post_link( $post->ID ) )
		return;

	if ( null === $link )
		$link = __('Edit This');

	$post_type_obj = get_post_type_object( $post->post_type );
	$link = '<a class="post-edit-link" href="' . $url . '" title="' . esc_attr( $post_type_obj->labels->edit_item ) . '">' . $link . '</a>';
	echo $before . apply_filters( 'edit_post_link', $link, $post->ID ) . $after . "sdsds";
}
add_filter('edit_post_link', 'myevent_edit_post_link', 10, 4);

function myevent_get_edit_post_link( $id = 0, $context = 'display' ) {
	if ( ! $post = get_post( $id ) )
		return;

	if ( 'display' == $context )
		$action = '&amp;action=edit';
	else
		$action = '&action=edit';

	$post_type_object = get_post_type_object( $post->post_type );
	if ( !$post_type_object )
		return;

	if ( !current_user_can( $post_type_object->cap->edit_post, $post->ID ) )
		return;

	return apply_filters( 'get_edit_post_link', admin_url( sprintf($post_type_object->_edit_link . $action, $post->ID) ), $post->ID, $context );
}
//add_filter('get_edit_post_link', 'myevent_get_edit_post_link', 10, 2);
*/

function myevent_page_template_dropdown( $default = '' ) {
	$post_page_templates = wp_get_theme()->get_page_templates();
	
	// Get template configs for the template page
	foreach (array_keys( $post_page_templates ) as $post_page_template ) :
		if (file_exists(get_template_directory().'/templates_config/'.$post_page_template) && !empty($post_page_template))
			include(get_template_directory().'/templates_config/'.$post_page_template);
		
		if (isset($template_configs)) {
			// if yes, limits one template page to be used / site
			// remove the template from the drop down menu
			if ($template_configs['single_template_per_site'] == 'yes') {
				$pages = get_pages();
				foreach($pages as $page) {
					print $_wp_page_template = get_post_meta($page->ID, '_wp_page_template', true);
					if ($_wp_page_template == $post_page_template) {
						unset($post_page_templates[$post_page_template]);
						break;
					}
				}
			}
			
		}
	endforeach;
	
	$templates = array_flip( $post_page_templates );
	ksort( $templates );
	foreach (array_keys( $templates ) as $template )
		: if ( $default == $templates[$template] )
			$selected = " selected='selected'";
		else
			$selected = '';
	echo "\n\t<option value='".$templates[$template]."' $selected>$template</option>";
	endforeach;
}


?>