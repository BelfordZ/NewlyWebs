=== WP Simple Galleries ===
Contributors: maca134
Tags: Gallery, Simple, Images, Photographs
Requires at least: 3.4.0
Tested up to: 3.5
Stable tag: 1.33
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LDZBJ2YP5GBRE

A simple and easy to use plugin that adds an image gallery to each post and page on your site.

== Description ==

A simple plugin that adds an image gallery to each post and page. 
Most gallery plugins seem to be fancy and advanced but sometimes you just want a simple solution to add images to a post or page. I created this to solve that problem.
The plugin uses the Wordpress image manager, so you can add already uploaded images too a gallery.

You can now upload a number of images and add them all at once.

If you change the size of the thumbnails after uploading images to a gallery, I suggest using [Regenerate Thumbnails](http://wordpress.org/extend/plugins/regenerate-thumbnails/ "Regenerate Thumbnails") to regenerate the correct size thumbnails.

Too add a gallery within the content use the shortcode [wpsgallery].

Requires WordPress 3.4 and PHP 5.

[youtube http://www.youtube.com/watch?v=-2erd1jrgyo]

= Translators =
* Lithuanian (lt_LT) - [Vincent G] (http://www.host1free.com/)
* Ukrainian (uk_UA)
* Brazilian Portuguese (pt_BR)

Some of the translations are incomplete. I have tried to translate some of the smaller phrase using Google, so they might not be correct.

**Current Features**

* Uses Wordpress image manager.
* Can add images to any post or page.
* Re-arrange images in galleries by drag-and-drop.
* Can set thumbnail size.
* Uses colorbox to view images
* Refactored options framework to prevent collisions
* Galleries are either appended to posts/pages or you can use the shortcode '[wpsgallery]' or '[wpsgallery id=2]' to show a gallery from another post/page.

If you have suggestions for a new add-on, feel free to email me at maca134@googlemail.com.

Follow me on Twitter! http://twitter.com/maca134uk

== Installation ==

1. Go to your admin area and select Plugins -> Add new from the menu.
2. Search for "WP Simple Galleries".
3. Click install.
4. Click activate.
5. Upload/Add images to a post or page.

== Screenshots ==

1. This is an example of what is displayed on a post.
2. This is an example of editting a post.

== ChangeLog ==

== Version 1.33 ==

* Can now set an ID in the gallery shortcode [wpsgallery id=POSTID]
* Updated language files

== Version 1.32 ==

* Fixed a JS error that seemed to occur on some WordPress instances. Basicly, if your WordPress instance isn't the right version, it will just remove the 'Quick Upload' button.
* Add TimThumb option, to try alleviate some thumbnail size issues. [TimThumb] (http://code.google.com/p/timthumb/)

== Version 1.31 ==

* Added quick upload

== Version 1.3 ==

* Fixed various support issues

= Version 1.29 =

* Custom captions fixed

= Version 1.27 =

* Custom captions
* Admin icon

= Version 1.26 =

* Added a delete all images button

= Version 1.25 =

* Brazilian Portuguese language translation

= Version 1.24 =

* Added Shortcode feature

= Version 1.23 =

* Added Ukrainian language translation

= Version 1.22 =

* Added Lithuanian language translation, (Vincent G from http://www.Host1Free.com)

= Version 1.21 =

* Fixed IE8 issue

= Version 1.2 =

* Added translation file and all strings are now using WP underscore function

= Version 1.1 =

* Added option to show gallery on single posts
* Added 'Add All Attachments' button
* Added option to turn Colorbox on and off

= Version 1.0 =

* Uses colorbox to view images
* Refactored options framework to prevent collisions

= Version 0.14 =

* Add option to set the position of galleries if you have a number of plugins which use the 'the_content' filter.

= Version 0.13 =

* Can now select which post types galleries are shown.

= Version 0.12 =

* Updated readme.txt

= Version 0.11 =

* Updated readme.txt
* Add nonce_field for extra security
* Valid image ID when saving post

= Version 0.10 =

* Javascript remove button not working on thumbnails added but are not saved

= Version 0.9 =

* More Bug fixes

= Version 0.8 =

* More Bug fixes

= Version 0.7 =

* More Bug fixes

= Version 0.6 =

* Bug fixes

= Version 0.5 =

* Small issue solved

= Version 0.4 =

* Cleaned code

= Version 0.3 =

* Updated readme.

= Version 0.2 =

* Added Options Framework admin section. Added screenshots.

= Version 0.1 =

* Initial public release.