jQuery(function ($) {
    if (typeof plupload !== 'undefined' && typeof WPSGwpUploaderInit !== 'undefined') {
        var uploader = new plupload.Uploader(WPSGwpUploaderInit);
        uploader.init();
        uploader.bind('FilesAdded', function(up, files) {
            up.start();
            $('#wpsimplegallyer_spinner').show();
        });
        uploader.bind('FileUploaded', function(up, file, res) {
            if (typeof res.response !== 'undefined') wpsimplegallery.get_thumbnail(res.response);
        });
    } else {
        $('#wpsg-plupload-browse-button').hide();
    }
    window.original_send_to_editor = window.send_to_editor;
    var wpsimplegallery = {
        admin_thumb_ul: '',
        init: function () {
            this.admin_thumb_ul = $('#wpsimplegallery_thumbs');
            this.admin_thumb_ul.sortable({
                placeholder: 'wpsimplegallery_placeholder'
            });
            this.admin_thumb_ul.on('click', '.wpsimplegallery_remove', function () {
                if (confirm('Are you sure you want to delete this?')) {
                    $(this).parent().fadeOut(1000, function () {
                        $(this).remove();
                    });
                }
                return false;
            });
            
            $('#wpsimplegallery_upload_button').on('click', function () {
                window.send_to_editor = function(html) {
                    html = html || false;
                    if (html === false) {
                        alert('There seem to be an error parsing the uploaded image.');
                        tb_remove();
                        window.send_to_editor = window.original_send_to_editor;
                        return false;
                    }
                    var imageid = $(html).find('img').attr('class').match(/wp\-image\-([0-9]+)/)[1];
                    wpsimplegallery.get_thumbnail(imageid);
                    tb_remove();
                    window.send_to_editor = window.original_send_to_editor;
                }
                var title = 'Select Image';
                tb_show( title, 'media-upload.php?post_id=' + POST_ID + '&amp;type=image&amp;TB_iframe=1' );
                return false;
            });
            
            $('#wpsimplegallery_add_attachments_button').on('click', function() {
                
                var included = [];
                $('#wpsimplegallery_thumbs input[type=hidden]').each(function (i, e) {
                    included.push($(this).val());
                });
                wpsimplegallery.get_all_thumbnails(POST_ID, included);
            });

            $('#wpsimplegallery_delete_all_button').on('click', function () {
                if (confirm('Are you sure you want to delete all the images in the gallery?')) {
                    wpsimplegallery.admin_thumb_ul.empty();
                }
                return false;
            });
        },
        get_thumbnail: function (id) {
            var data = {
                action: 'wpsimplegallery_get_thumbnail',
                imageid: id
            };
            $('#wpsimplegallyer_spinner').show();
            jQuery.post(ajaxurl, data, function(response) {
                $('#wpsimplegallyer_spinner').hide();
                wpsimplegallery.admin_thumb_ul.append(response);
            });
        },
        get_all_thumbnails: function (post_id, included) {
            var data = {
                action: 'wpsimplegallery_get_all_thumbnail',
                post_id: post_id,
                included: included
            };
            $('#wpsimplegallyer_spinner').show();
            $.post(ajaxurl, data, function(response) {
                wpsimplegallery.admin_thumb_ul.append(response);
                $('#wpsimplegallyer_spinner').hide();
            });
        }
    };
    wpsimplegallery.init();
});