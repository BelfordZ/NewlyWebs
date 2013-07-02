<?php

require_once(dirname(dirname(__FILE__)) . '/wp-load.php');


$post_type = 'post';
$post_type_object = get_post_type_object( $post_type );

if ( ! current_user_can( $post_type_object->cap->edit_posts ) )
	wp_die( __( 'Cheatin&#8217; uh?' ) );


function web_safe_url($str)
{
	$search = array('/\//',
					'/\&/',
					'/ /',
					'/"/',
					'/\'/');
	$replace = array('-',
					 '',
					 '-',
					 '',
					 '');
	
	return urlencode(preg_replace($search, $replace, strtolower($str)));
}

// Inserts the tracking data into the database
function storelink_add_product_data($post_info) {
	global $user_ID;
	
	// call the storelink API to add product
	$user_info = get_userdata($user_ID);
	
	$fields = array(
	            'product_name'=>urlencode($post_info["product_name"]),
	            'product_price'=>urlencode($post_info["product_price"]),
				'username'=>$user_info->user_email,
	        );

	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string,'&');
	
	$url = STORE_API_URL . "admin/add_product.php";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST,count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS,$fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//curl_setopt($ch, CURLOPT_USERPWD, COUPON_API_KEY . ":" . COUPON_API_KEY);
	$remote_data = curl_exec($ch);
	curl_close($ch);
	
	$remote_data = stripslashes($remote_data);
	$product_data = json_decode($remote_data);
	$storelink_product_id = (int)$product_data->storelink_product_id;
	
    return $storelink_product_id;
}

function storelink_add_product_image($storelink_product_id, $image_file_path) {
	
	$fields = array(
				'product_id'=> $storelink_product_id,
	            'product_image'=>urlencode($image_file_path),
	        );

	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string,'&');
	
	$url = STORE_API_URL . "add_product.php";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST,count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS,$fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//curl_setopt($ch, CURLOPT_USERPWD, COUPON_API_KEY . ":" . COUPON_API_KEY);
	$remote_data = curl_exec($ch);
	curl_close($ch);
	
	
}

function insert_product_post($storelink_product_id, $post_info) {
	global $wpdb;
	global $user_ID;
	
	$post_content = "[storelink:" . $storelink_product_id . "]\n";
	$post_content .= wp_kses_post($post_info["product_description"]);
	//$post_content .= $post_info["tracking_note"];
	
	//if ($post_info["post_private"] == "y")
	//	$post_status = "private";
	//else
		$post_status = "publish";
	
	$post = array (
        "post_author" => $user_ID,
        "post_date" => date("Y-m-d H:i:s"),
        "post_date_gmt" => date("Y-m-d H:i:s"),
        "post_content" => $post_content,
        "post_title" => ($post_info["product_name"]),
        "post_excerpt" => "",
        "post_status" => $post_status,
        "comment_status" => "open",
        "ping_status" => "open",
		"post_name" => web_safe_url(strtolower($post_info["product_name"])),
        "post_modified" => date("Y-m-d H:i:s"),
        "post_modified_gmt" => date("Y-m-d H:i:s"),
        "post_parent" => 0,
		"guid" => "",
		"post_type" => "products",
    );
    
    $wpdb->insert( $wpdb->prefix . "posts", $post );
    $post_id = $wpdb->insert_id;
	
	$wpdb->update( $wpdb->prefix . "posts", array( 'guid' => site_url() . '/web/?post_type=products&p=' . $post_id ), array( 'ID' => $post_id ));
	
	return $post_id;
}

function assign_product_category($post_id, $post_info) {
	global $wpdb;
	
	$product_category = array (
        "object_id" => $post_id,
        "term_taxonomy_id" => $post_info["product_category"],
        "term_order" => 0,
    );
	
	$wpdb->insert( $wpdb->prefix . "term_relationships", $product_category );
	
}

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


if (isset($_POST['submit'])) {
	$post_info = $_POST;
	$fileData = $_FILES['user-submitted-image'];
	if (!empty($post_info["product_name"])) {
	    //print_r($_FILES['user-submitted-image']['name']);
   	    $storelink_product_id = storelink_add_product_data($post_info);
		if (empty($storelink_product_id))
			exit();
   	    $post_id = insert_product_post($storelink_product_id, $post_info);
		assign_product_category($post_id, $post_info);
		// assign product_type
		add_post_meta($post_id, 'product_type', 'physical');

   	    if ($fileData['name'][0] != "") {
   	        $attachment_post_id = upload_image($fileData, $post_id);
   	    }
		$image_file_path = get_post_meta($attachment_post_id, '_wp_attached_file', true);
		storelink_add_product_image($storelink_product_id, $image_file_path);
   	
    }
	//if ($post_info["post_privacy"] == "public")
	//post_activity_update("I'm at " . $post_info["tracking_weight"] . " lb", '1'); // group the status post goes under, hard coded for now. Group ID?
	// short code: [tracking-cr5-1000:#]
	
	/*
	$recipe_title = htmlentities($post_info["recipe_title"], ENT_QUOTES);
	$summary = htmlentities($post_info["summary"], ENT_QUOTES);
	$rating = htmlentities($post_info["rating"], ENT_QUOTES);
	*/	
}

//$url = get_option('siteurl');
//header("Location:" . $url . '/progress/');


?>



<form enctype="multipart/form-data" method="post" action="add_product.php" name="product_form">
<label>Product Category:</label> 
<?php

$args = array(
	'show_option_all'    => '',
	'show_option_none'   => '',
	'orderby'            => 'ID', 
	'order'              => 'ASC',
	'show_count'         => 0,
	'hide_empty'         => 1, 
	'child_of'           => 0,
	'exclude'            => '',
	'echo'               => 1,
	'selected'           => 0,
	'hierarchical'       => 1, 
	'name'               => 'product_category',
	'id'                 => '',
	'class'              => 'postform',
	'depth'              => 0,
	'tab_index'          => 0,
	'taxonomy'           => 'pcategory',
	'hide_if_empty'      => false
);

wp_dropdown_categories( $args );

?><br>
<label>Product Name:</label> <input type="text" name="product_name" size="40"><br>
<label>Product Price:</label> <input type="text" name="product_price" size="10"><br>
Product Description:<br>
<textarea name="product_description" rows="6"></textarea>
<br>
Upload product image: <input class="usp_input usp_clone" type="file" size="25" id="user-submitted-image" name="user-submitted-image[]" />
<br>
<div align="center"><input type="submit" name="submit" value="Submit" /></div>
</form>