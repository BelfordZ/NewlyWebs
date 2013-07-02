<?php
/**
 * 
 */

$settings['min-images'] = 0;
$settings['max-images'] = 1;

$settings['min-image-height'] = 0;
$settings['min-image-width'] = 0;

$settings['max-image-height'] = 1000;
$settings['max-image-width'] = 1000;

function upload_image($fileData, $post_id) {
	global $settings;

	function imageIsRightSize($width, $height) {
		global $settings;

		//$settings = $this->getSettings();
		$widthFits = ($width <= intval($settings['max-image-width'])) && ($width >= $settings['min-image-width']);
		$heightFits = ($height <= $settings['max-image-height']) && ($height >= $settings['min-image-height']);
		return $widthFits && $heightFits;
	}

	// image upload section
	if (!function_exists('media_handle_upload')) {
		require_once (ABSPATH.'/wp-admin/includes/media.php');
		require_once (ABSPATH.'/wp-admin/includes/file.php');
		require_once (ABSPATH.'/wp-admin/includes/image.php');
	}
	$attachmentIds = array();
	$imageCounter = 0;
	for ($i = 0; $i < count($fileData['name']); $i++) {
		$imageInfo = getimagesize($fileData['tmp_name'][$i]);
		if (false === $imageInfo || !imageIsRightSize($imageInfo[0], $imageInfo[1])) {
			continue;
		}
		$key = "public-submission-attachment-{$i}";
		$_FILES[$key] = array();
		$_FILES[$key]['name'] = $fileData['name'][$i];
		$_FILES[$key]['tmp_name'] = $fileData['tmp_name'][$i];
		$_FILES[$key]['type'] = $fileData['type'][$i];
		$_FILES[$key]['error'] = $fileData['error'][$i];
		$_FILES[$key]['size'] = $fileData['size'][$i];
		//$attachmentId = media_handle_upload($key, $newPost);
		$attachmentId = media_handle_upload($key, $post_id);
		//print "attachmentId: ". $attachmentId . "<br>";
		if (!is_wp_error($attachmentId) && wp_attachment_is_image($attachmentId)) {
			$attachmentIds[] = $attachmentId;
			//add_post_meta($newPost, $_post_meta_Image, wp_get_attachment_url($attachmentId));
			$imageCounter++;
		} else {
			wp_delete_attachment($attachmentId);
		}
		if ($imageCounter == $settings['max-images']) {
			break;
		}
	}
	if (count($attachmentIds) < $settings['min-images']) {
		foreach ($attachmentIds as $idToDelete) {
			wp_delete_attachment($idToDelete);
		}
		wp_delete_post($newPost);
		return false;
	}

	if ($attachmentId > 0) {
		// add image to post as featured image
		//add_post_meta($post_id, '_thumbnail_id', $attachmentId);

		return $attachmentId;
	}
}


$override_default_editor = true;
/*
$template_settings = array(
	'single_template_per_site' => 'yes', // yes, limits one template page to be used / site
);
*/

function update_page_content($post_array) {
	
	insert_or_update_content_by_post_name('bride-story', $post_array['bride_story']);
	insert_or_update_content_by_post_name('groom-story', $post_array['groom_story']);
	
	/* handle image */
	// create placeholder post for first time picture uploading
	if (get_content_id_by_post_name('bride-groom-story-pic')==null)
		insert_or_update_content_by_post_name('bride-groom-story-pic', '');
	
	$post_id = get_content_id_by_post_name('bride-groom-story-pic');
	$fileData = $_FILES['user-submitted-image'];
	if ($fileData['name'][0] != "") {
        $attachment_post_id = upload_image($fileData, $post_id);
		//$image_file_path = get_post_meta($attachment_post_id, '_wp_attached_file', true);
		//$upload_dir = wp_upload_dir();
		//echo $upload_dir['baseurl'];
		//insert_or_update_content_by_post_name('bride-groom-story-pic', $upload_dir['baseurl'].'/'.$image_file_path);
		insert_or_update_content_by_post_name('bride-groom-story-pic', '[img:::'.$attachment_post_id.']');
    }
	
	
}

function display_template_editor() {
?>

<div>
	<?php
	print get_formatted_content_by_post_name('bride-groom-story-pic');
	?>
	Upload image: <input class="usp_input usp_clone" type="file" size="25" id="user-submitted-image" name="user-submitted-image[]" />
	
</div>

<div class="clear"></div>

<div style="float:left; padding: 10px;">
	Bride Story:<br>
	<?php
	$post = get_content_by_post_name('bride-story');

	wp_editor($post->post_content, 'bride_story', array('dfw' => true, 'editor_height' => 180, 'media_buttons' => false, 'tinymce' => array(
		'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,outdent,indent,fontsizeselect,forecolor',
		'theme_advanced_buttons2' => '',
		'theme_advanced_buttons3' => ''
	)) ); ?>

</div>

<div style="float:left; padding: 10px;">
	Groom Story:<br>
	<?php
	$post = get_content_by_post_name('groom-story');

	wp_editor($post->post_content, 'groom_story', array('dfw' => true, 'editor_height' => 180, 'media_buttons' => false, 'tinymce' => array(
		'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,outdent,indent,fontsizeselect,forecolor',
		'theme_advanced_buttons2' => '',
		'theme_advanced_buttons3' => ''
	)) ); ?>

</div>

<?php
}
?>