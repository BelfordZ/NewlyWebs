<?php
/*
  Plugin Name: WP Simple Galleries
  Version: 1.33
  Description: A simple plugin that adds an image gallery to each post and page.
  Plugin URI: http://maca134.co.uk/blog/wp-simple-galleries/
  Author: Matthew McConnell
  Author URI: http://maca134.co.uk/
 */

$plugin_dir = plugin_basename(__FILE__);
$plugin_dir = str_replace(basename($plugin_dir), '', $plugin_dir);
define('WPSIMPLEGALLERY_DIR', WP_PLUGIN_DIR . '/' . $plugin_dir);
define('WPSIMPLEGALLERY_URL', WP_PLUGIN_URL . '/' . $plugin_dir);
define('WPSIMPLEGALLERY_DEBUG', false);
define('WPSIMPLEGALLERY_VERSION', '1.33');

define('WPSG_OPTIONS_FRAMEWORK_URL', WPSIMPLEGALLERY_URL . 'admin/');
define('WPSG_OPTIONS_FRAMEWORK_DIRECTORY', WPSIMPLEGALLERY_DIR . 'admin/');
define('WPSG_OPTIONS_FRAMEWORK_NAME', 'Simple Gallery');
define('WPSG_OPTIONS_FRAMEWORK_TAG', 'wpsimplegallery');
require_once (WPSG_OPTIONS_FRAMEWORK_DIRECTORY . 'options-framework.php');

class wpsimplegallery {

    private static $instance;
    private $admin_thumbnail_size = 109;
    private $thumbnail_size_w = 150;
    private $thumbnail_size_h = 150;

    public static function forge() {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    private function __construct() {
        $this->thumbnail_size_w = wpsg_of_get_option('wpsimplegallery_thumb_width');
        $this->thumbnail_size_h = wpsg_of_get_option('wpsimplegallery_thumb_height');

        add_action('admin_print_scripts-post.php', array(&$this, 'admin_print_scripts'));
        add_action('admin_print_scripts-post-new.php', array(&$this, 'admin_print_scripts'));
        add_action('admin_print_styles', array(&$this, 'admin_print_styles'));
        add_action('wp_print_scripts', array(&$this, 'print_scripts'));
        add_action('wp_print_styles', array(&$this, 'print_styles'));
        add_action('init', array(&$this, 'load_plugin_textdomain'));
        add_filter('the_content', array(&$this, 'output_gallery'), wpsg_of_get_option('wpsimplegallery_filter_priority', 10));
        add_image_size('wpsimplegallery_admin_thumb', $this->admin_thumbnail_size, $this->admin_thumbnail_size, true);
        add_image_size('wpsimplegallery_thumb', $this->thumbnail_size_w, $this->thumbnail_size_h, true);
        add_shortcode('wpsgallery', array(&$this, 'shortcode'));
        if (is_admin()) {
            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
            add_action('admin_init', array(&$this, 'add_meta_boxes'), 1);
            add_action('save_post', array(&$this, 'save_post_meta'), 9, 1);
            add_action('wp_ajax_wpsimplegallery_get_thumbnail', array(&$this, 'ajax_get_thumbnail'));
            add_action('wp_ajax_wpsimplegallery_get_all_thumbnail', array(&$this, 'ajax_get_all_attachments'));
        }
    }

    public function admin_print_scripts() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('wpsimplegallery-admin-scripts', WPSIMPLEGALLERY_URL . 'wp-simple-gallery-admin.js', array('jquery'), WPSIMPLEGALLERY_VERSION);
    }

    public function admin_print_styles() {
        wp_enqueue_style('wpsimplegallery-admin-style', WPSIMPLEGALLERY_URL . 'wp-simple-gallery-admin.css', array(), WPSIMPLEGALLERY_VERSION);
    }

    public function add_meta_boxes() {
        $post_types = wpsg_of_get_option('wpsimplegallery_post_types');
        $post_types = ($post_types !== false) ? $post_types : array('page' => '1', 'post' => '1');

        foreach ($post_types as $type => $value) {
            if ($value == '1') {
                add_meta_box(
                        'wpsimplegallery', __('WP Simple Gallery', 'wpsimplegallery'), array(&$this, 'inner_custom_box'), $type
                );
            }
        }
    }

    public function inner_custom_box($post) {
        $gallery = get_post_meta($post->ID, 'wpsimplegallery_gallery', true);
        wp_nonce_field(basename(__FILE__), 'wpsimplegallery_gallery_nonce');

        $upload_size_unit = $max_upload_size = wp_max_upload_size();
        $sizes = array( 'KB', 'MB', 'GB' );

        for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ ) {
            $upload_size_unit /= 1024;
        }

        if ( $u < 0 ) {
            $upload_size_unit = 0;
            $u = 0;
        } else {
            $upload_size_unit = (int) $upload_size_unit;
        }

        $upload_action_url = admin_url('async-upload.php');
        $post_params = array(
                "post_id" => $post->ID,
                "_wpnonce" => wp_create_nonce('media-form'),
                "short" => "1",
        );

        $post_params = apply_filters( 'upload_post_params', $post_params ); // hook change! old name: 'swfupload_post_params'

        $plupload_init = array(
            'runtimes' => 'html5,silverlight,flash,html4',
            'browse_button' => 'wpsg-plupload-browse-button',
            'file_data_name' => 'async-upload',
            'multiple_queues' => true,
            'max_file_size' => $max_upload_size . 'b',
            'url' => $upload_action_url,
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'filters' => array( array('title' => __( 'Allowed Files' ), 'extensions' => '*') ),
            'multipart' => true,
            'urlstream_upload' => true,
            'multipart_params' => $post_params
        );

        ?>
        <script type="text/javascript">
            var POST_ID = <?php echo $post->ID; ?>;
            var WPSGwpUploaderInit = <?php echo json_encode($plupload_init) ?>;
        </script>
        
        <input id="wpsg-plupload-browse-button" class="button" type="button" value="<?php echo __('Quick Upload', 'wpsimplegallery'); ?>" rel="" />
        <input style="width: auto;" id="wpsimplegallery_upload_button" class="upload_button button" type="button" value="<?php echo __('Upload Image', 'wpsimplegallery'); ?>" rel="" />
        <input id="wpsimplegallery_add_attachments_button" class="button" type="button" value="<?php echo __('Add All Attachments', 'wpsimplegallery'); ?>" rel="" />
        <input id="wpsimplegallery_delete_all_button" class="button" type="button" value="<?php echo __('Delete All', 'wpsimplegallery'); ?>" rel="" />
        <span class="spinner" id="wpsimplegallyer_spinner"></span>
        <div id="wpsimplegallery_container">
            <ul id="wpsimplegallery_thumbs" class="clearfix"><?php
                $gallery = (is_string($gallery)) ? @unserialize($gallery) : $gallery;
                if (is_array($gallery) && count($gallery) > 0) {
                    foreach ($gallery as $id) {
                        echo $this->admin_thumb($id);
                    }
                }
                ?>
            </ul>
        </div>
        <?php
    }

    private function admin_thumb($id) {
        $image = wp_get_attachment_image_src($id, 'wpsimplegallery_admin_thumb', true);
        ?>
        <li><img src="<?php echo $image[0]; ?>" width="<?php echo $image[1]; ?>" height="<?php echo $image[2]; ?>" /><a href="#" class="wpsimplegallery_remove"><?php echo __('Remove', 'wpsimplegallery'); ?></a><input type="hidden" name="wpsimplegallery_thumb[]" value="<?php echo $id; ?>" /></li>
        <?php
    }

    public function ajax_get_thumbnail() {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        echo $this->admin_thumb($_POST['imageid']);
        die;
    }

    public function ajax_get_all_attachments() {
        $post_id = $_POST['post_id'];
        $included = (isset($_POST['included'])) ? $_POST['included'] : array();

        $attachments = get_children(array(//do only if there are attachments of these qualifications
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'numberposts' => -1,
            'order' => 'ASC',
            'post_mime_type' => 'image', //MIME Type condition
                )
        );
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        if (count($attachments) > 0) {
            foreach ($attachments as $a) {
                if (!in_array($a->ID, $included)) {
                    echo $this->admin_thumb($a->ID);
                }
            }
        }
        die;
    }

    private function thumb($id, $post_id) {
        $info = get_posts(array('p' => $id, 'post_type' => 'attachment'));
		$url = wp_get_attachment_url($id);
        if (wpsg_of_get_option('wpsimplegallery_use_timthumb', '0') === '1') {
            $width = $this->thumbnail_size_w;
            $height = $this->thumbnail_size_h;
            $image = array(
                    WPSIMPLEGALLERY_URL . 'timthumb.php?src=' . $url . '&q=85&w=' . $width . '&h=' . $height,
                    $width,
                    $height
                );
        } else {
            $image = wp_get_attachment_image_src($id, 'wpsimplegallery_thumb', true);
        }
		$title = wpsg_of_get_option('wpsimplegallery_caption', '%title%');
		$alt = get_post_meta($id, '_wp_attachment_image_alt', true);
		$data = array(
			'%title%' => $info[0]->post_title,
			'%alt%' => $alt,
			'%filename%' => basename($url),
			'%caption%' => $info[0]->post_excerpt,
            "\n" => ' - '
		);
		$title = str_replace(array_keys($data), $data, $title);
        return '<li><a href="' . $url . '" title="' . $title . '" rel="wpsimplegallery_group_' . $post_id . '"><img src="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" /></a></li>';
    }

    public function save_post_meta($id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return '';
        }
        if (!isset($_POST['wpsimplegallery_gallery_nonce']) || !wp_verify_nonce($_POST['wpsimplegallery_gallery_nonce'], basename(__FILE__)))
            return (isset($post_id)) ? $post_id : 0;

        $images = (isset($_POST['wpsimplegallery_thumb'])) ? $_POST['wpsimplegallery_thumb'] : array();
        $gallery = array();
        if (count($images) > 0) {
            foreach ($images as $i => $img) {
                if (is_numeric($img))
                    $gallery[] = $img;
            }
        }
        update_post_meta($id, 'wpsimplegallery_gallery', $gallery);
        return $id;
    }

    public function print_scripts() {
        if (wpsg_of_get_option('use_colorbox', '1') == '1')
            wp_register_script('colorbox', WPSIMPLEGALLERY_URL . 'colorbox/jquery.colorbox-min.js', array('jquery'));
        wp_enqueue_script('wpsimplegallery-scripts', WPSIMPLEGALLERY_URL . 'wp-simple-gallery.js', array('colorbox'));
    }

    public function print_styles() {
        wp_enqueue_style('wpsimplegallery-style', WPSIMPLEGALLERY_URL . 'wp-simple-gallery.css');
        if (wpsg_of_get_option('use_colorbox', '1') == '1')
            wp_enqueue_style('colorbox', WPSIMPLEGALLERY_URL . 'colorbox/themes/' . wpsg_of_get_option('wpsimplegallery_colorbox_theme') . '/colorbox.css');
    }

    private function gallery($post_id = false) {
        global $post;
        $post_id = (!$post_id) ? $post->ID : $post_id;
        $gallery = get_post_meta($post_id, 'wpsimplegallery_gallery', true);
        $gallery = (is_string($gallery)) ? @unserialize($gallery) : $gallery;
        $html = '';
        
        if (is_array($gallery) && count($gallery) > 0) {
            $html = '<div id="wpsimplegallery_container"><ul id="wpsimplegallery" class="clearfix">';
            foreach ($gallery as $thumbid) {
                $html .= $this->thumb($thumbid, $post_id);
            }
            $html .= '</ul></div>';
        }
       
        return $html;
    }

    public function output_gallery($content) {
        if ( post_password_required() ) {
            return $content;
        }
        
        $append_gallery = wpsg_of_get_option('append_gallery', '1');
        if (!post_password_required() && $append_gallery == '1' && (wpsg_of_get_option('single_only', '1') !== '1' || is_singular())) {
           $content .= $this->gallery();
        }
        return $content;
    }

    public function shortcode($atts) {
        extract( shortcode_atts( array(
		'id' => false,
	), $atts ) );
        return $this->gallery($id);
    }

    public function load_plugin_textdomain() {
		load_plugin_textdomain('wpsimplegallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
    }

}

global $wpsimplegallery;
$wpsimplegallery = wpsimplegallery::forge();