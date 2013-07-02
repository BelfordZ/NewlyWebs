<?php

/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 * 
 */
function wpsg_optionsframework_option_name() {
    $wpsg_optionsframework_settings = get_option('wpsg_optionsframework');
    $wpsg_optionsframework_settings['id'] = WPSG_OPTIONS_FRAMEWORK_TAG;
    update_option('wpsg_optionsframework', $wpsg_optionsframework_settings);
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *  
 */
function wpsg_optionsframework_options() {
    $colorboxpath = WPSIMPLEGALLERY_URL . 'colorbox/';
    $options = array();

    $options[] = array('name' => __('Basic Settings', 'wpsimplegallery'),
        'type' => 'heading');
	
	$options[] = array('name' => __('Caption', 'wpsimplegallery'),
        'desc' => __('A formatted string to be used as a caption.<br>%title% - Image title<br>%alt% - Alternative Text<br>%filename% - Filename<br>%caption% - Caption', 'wpsimplegallery'),
        'id' => 'wpsimplegallery_caption',
        'std' => '%title%',
        'class' => '',
        'type' => 'textarea');
	
    $options[] = array('name' => __('Thumbnail Width', 'wpsimplegallery'),
        'desc' => __('Thumbnail width (changing this will require thumbnails to be regenerated, this plugin will help: <a href=\'http://wordpress.org/extend/plugins/regenerate-thumbnails/\' target=\'_blank\'>Regenerate Thumbnails</a>).', 'wpsimplegallery'),
        'id' => 'wpsimplegallery_thumb_width',
        'std' => '100',
        'class' => 'mini',
        'type' => 'text');
		
    $options[] = array('name' => __('Thumbnail Height', 'wpsimplegallery'),
        'desc' => __('Thumbnail height (changing this will require thumbnails to be regenerated, this plugin will help: <a href=\'http://wordpress.org/extend/plugins/regenerate-thumbnails/\' target=\'_blank\'>Regenerate Thumbnails</a>).', 'wpsimplegallery'),
        'id' => 'wpsimplegallery_thumb_height',
        'std' => '100',
        'class' => 'mini',
        'type' => 'text');

    $options[] = array('name' => __('Use TimThumb', 'wpsimplegallery'),
        'desc' => __('Instead of using WP\'s thumbnail generator, uses <a href="http://code.google.com/p/timthumb/">TimThumb</a>, which will do the thumbnails as they are requested, so allowing you to change the thumbnail without having to regenerate all the thumbnails.', 'wpsimplegallery'),
        'id' => 'wpsimplegallery_use_timthumb',
        'std' => '0',
        'type' => 'checkbox');

    $options[] = array('name' => __('Content Filter Priority', 'wpsimplegallery'),
        'desc' => __('Used to specify the order execution. Lower numbers correspond with earlier execution, so set it to 1 to make it appear before all other plugins on content pages.', 'wpsimplegallery'),
        'id' => 'wpsimplegallery_filter_priority',
        'std' => '10',
        'class' => 'mini',
        'type' => 'text');

    $post_types_default = array('post' => '1', 'page' => '1');
    $post_types = get_post_types();

    unset($post_types['attachment']);
    unset($post_types['revision']);
    unset($post_types['nav_menu_item']);
    unset($post_types['mediapage']);
    
    $options[] = array('name' => __('Post Types', 'wpsimplegallery'),
        'desc' => __('What post types do you want to have galleries enabled', 'wpsimplegallery'),
        'id' => 'wpsimplegallery_post_types',
        'std' => $post_types_default,
        'options' => $post_types,
        'type' => 'multicheck');

    $options[] = array('name' => __('Show on single posts only', 'wpsimplegallery'),
        'desc' => __('This will stop galleries from showing when more than one post is being shown.', 'wpsimplegallery'),
        'id' => 'single_only',
        'std' => '1',
        'type' => 'checkbox');
    
    $options[] = array('name' => __('Append to posts', 'wpsimplegallery'),
        'desc' => __('This will append the gallery to the bottom of the post. You can use the shortcode "[wpsgallery]" if you want the gallery to be inserted with the post.', 'wpsimplegallery'),
        'id' => 'append_gallery',
        'std' => '1',
        'type' => 'checkbox');
    
    $options[] = array('name' => __('Please Donate', 'wpsimplegallery'),
        'desc' => __('Alot of time and effort went into making this plugin, all donations are hugely appreciated.', 'wpsimplegallery'),
        'url' => 'http://maca134.co.uk/blog/wp-simple-galleries/',
        'type' => 'donate');


    $options[] = array('name' => __('Colorbox Settings', 'wpsimplegallery'),
        'type' => 'heading');

    $options[] = array('name' => __('Use Colorbox', 'wpsimplegallery'),
        'desc' => __('Check to use colorbox. Sometimes it can conflict with other plugins such as Shopp.', 'wpsimplegallery'),
        'id' => 'use_colorbox',
        'std' => '1',
        'type' => 'checkbox');

    $options[] = array('name' => __('Colorbox Theme', 'wpsimplegallery'),
        'id' => 'wpsimplegallery_colorbox_theme',
        'std' => 'theme1',
        'type' => 'images',
        'options' => array(
            'theme1' => $colorboxpath . 'themes/theme1/style.png',
            'theme2' => $colorboxpath . 'themes/theme2/style.png',
            'theme3' => $colorboxpath . 'themes/theme3/style.png',
            'theme4' => $colorboxpath . 'themes/theme4/style.png',
            'theme5' => $colorboxpath . 'themes/theme5/style.png'
        )
    );

    $options[] = array('name' => __('Please Donate', 'wpsimplegallery'),
        'desc' => __('Alot of time and effort went into making this plugin, all donations are hugely appreciated.', 'wpsimplegallery'),
        'url' => 'http://maca134.co.uk/blog/wp-simple-galleries/',
        'type' => 'donate');

    return $options;
}