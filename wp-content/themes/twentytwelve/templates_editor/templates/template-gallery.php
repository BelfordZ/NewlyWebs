<?php
/**
 * 
 */

$settings['min-images'] = 0;
$settings['max-images'] = 5;

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

function update_page_content($post_array) {
	
	// create placeholder post for first time picture uploading
	if (get_content_id_by_post_name('gallery')==null)
		insert_or_update_content_by_post_name('gallery', '');
	
	$post_id = get_content_id_by_post_name('gallery');
	
	// get pictures layout order
	$display_layout = $post_array['display_layout'];
	$display_layout_array = preg_split('/,/', $display_layout, -1, PREG_SPLIT_NO_EMPTY);
	// temporary bug fix: if form being resubmitted mulitple times,
	// we risk adding additional display_layout text string unto itself
	$display_layout_array = array_unique($display_layout_array);
	
	// process deleting pictures
	$array_item_count = count($display_layout_array);
	for ($i=0; $i<$array_item_count; $i++) {
		$attachment_id = $display_layout_array[$i];
		if ($post_array['delete-'.$attachment_id]=='y') {
			wp_delete_post( $attachment_id, true );
			$index = array_keys($display_layout_array, $attachment_id);
			if (!empty($index))
				unset($display_layout_array[$index[0]]);
		}
	}
	$display_layout_array = array_values($display_layout_array);
	
	// update pictures layout order
	$myevent_gallery = get_post_meta($post_id, 'myevent_gallery');
	if ($myevent_gallery != '') {
		// for whatever reason wordpress "wraps" another array on top of the current array
		// we need to strip out the "wrapper" array before updating the post_meta value
		//$temp_array = $myevent_gallery[0];
		//if (count($temp_array) <= count($display_layout_array)) {
		//	for ($i=0; $i<count($temp_array); $i++) {
				// temporary bug fix: if form being resubmitted mulitple times,
				// we risk adding additional display_layout text string unto itself
		//		$temp_array[$i] = $display_layout_array[$i];
		//	}
		//} else { // picture(s) deleted, use the smaller array
		//	$temp_array = $display_layout_array;
		//}
		
		update_post_meta($post_id, 'myevent_gallery', $display_layout_array);
	}
	
	/* handle image */
	$fileData = $_FILES['user-submitted-image'];
	if ($fileData['name'][0] != "") {
        $attachment_post_id = upload_image($fileData, $post_id);
		//$image_file_path = get_post_meta($attachment_post_id, '_wp_attached_file', true);
		//$upload_dir = wp_upload_dir();
		//echo $upload_dir['baseurl'];
		//insert_or_update_content_by_post_name('bride-groom-story-pic', $upload_dir['baseurl'].'/'.$image_file_path);
		//get_content_by_post_name('gallery');
		insert_or_update_content_by_post_name('gallery', '[gallery:::' . $post_id . ']');
		$myevent_gallery = get_post_meta($post_id, 'myevent_gallery');
		if ($myevent_gallery != '') {
			// post_meta key exists, update value
			//$temp_array = unserialize($myevent_gallery);
			
			// for whatever reason wordpress "wraps" another array on top of the current array
			// we need to strip out the "wrapper" array before updating the post_meta value
			$temp_array = $myevent_gallery[0];
			if ($attachment_post_id) {
				// if $attachment_post_id exists, upload successful
				$temp_array[] = $attachment_post_id;

				update_post_meta($post_id, 'myevent_gallery', $temp_array);
			}
		} else {
			// post_meta key doesn't exist, create post_meta entry
			add_post_meta($post_id, 'myevent_gallery', array(0 => $attachment_post_id));
		}
    }
	
}

function display_template_editor() {
	global $settings;
	
?>
<style>
	.sortable {
		margin: auto;
		padding: 0;
		width: 475px;
		-webkit-touch-callout: none;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
	}
	.sortable.grid {
		overflow: hidden;
	}
	.sortable li {
		list-style: none;
		border: 2px solid #CCC;
		/*background: #F6F6F6;*/
		color: #1C94C4;
		margin: 5px;
		padding: 5px;
		/*height: 22px;*/
	}
	.sortable.grid li {
		cursor: move;
		/*line-height: 200px;*/
		float: left;
		width: 200px;
		height: 200px;
		/*height: auto;*/
		text-align: center;
	}
	li.sortable-placeholder {
		border: 2px dashed #CCC;
		background: none;
	}
	li.ui-sortable-placeholder {
		border: 2px dashed #CCC;
		background: none;
	}
</style>

<div>
	<?php
	print get_formatted_content_by_post_name('gallery', true);
	?>
	Upload image:<br>
	<?php
	//$numberImages = $settings['max-images'];
	//for($i = 0; $i < $numberImages; $i++) { ?>
	<input class="usp_input usp_clone" type="file" size="25" id="user-submitted-image" name="user-submitted-image[]" /><br>
	<?php //} ?>
	
	<input type="hidden" name="display_layout" id="display_layout" value="">
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script src="<?php print network_site_url('/myevent/includes/js/html5sortable/'); ?>jquery.sortable.js"></script>
	<script>
		$(document).ready(function() {
			$('#gallery_grid li').each(function () {
				//alert($(this).attr('id'))
				$('#display_layout').val($('#display_layout').val() + ',' + $(this).attr('id'));
			});
			$('.sortable').sortable({
			      placeholder: "ui-sortable-placeholder"
			  	}).bind('sortupdate', function() {
				//lis = $('#gallery_grid li');
				//alert(lis.html());
				//alert($('#gallery_grid li').html());
				//reset field
				$('#display_layout').val('');
				$('#gallery_grid li').each(function () {
					//alert($(this).attr('id'))
					$('#display_layout').val($('#display_layout').val() + ',' + $(this).attr('id'));
				});
			});
			
		});
	</script>
</div>

<div class="clear"></div>


<?php
}
?>