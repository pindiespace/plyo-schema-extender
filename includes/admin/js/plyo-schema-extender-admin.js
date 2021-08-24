(function( $ ) {
    'use strict';

    $( window ).load( function () {

    /**
     * TODO:
     * Combat FOUC in WordPress
     * @link https://stackoverflow.com/questions/3221561/eliminate-flash-of-unstyled-content
     */

        // if tabs are present, we are on the plugin options page

        var tabs = $('.plse-tab-nav').find('a');

        if (tabs) {

            /*
             * ---------------------------------------------------
             * handle tabs
             * Adapted from Tabs Switcher by Paolo Duzioni
             * Copyright (c) 2021 by Paolo Duzioni (https://codepen.io/Paolo-Duzioni/pen/MaXPXJ)
             * ---------------------------------------------------
             */
            var tsel = 'plse-settings-tabsel';
            
            var tabContents = $('#content-tabs').children('.content-tab');
            var tabState = $('#plse-settings-tabsel').val();

            // Switch Tab Handler
            tabs.on('click', function(e) {

                e.preventDefault();

                // get the tab panel
                var contentTab = $(this).attr('href');

                ////////////////////this.style.boxShadow =  '0px 0px';

                //tabs btn
                tabs.removeClass('open');
                $(this).addClass('open');

                //remove class from all tabs
                tabContents.removeClass('open');

                // add class to panel connected to clicked-on tab
                $(contentTab).addClass('open');

                // strip non-numeric ('tab3' to '3')
                //var num = e.target.id.replace(/\D/g,'');

                console.log("TARGET:" + e.target.id);

                // save status (which tab is open = tab #ID) into hidden options field, used by Options API
                var tabsel = $('#plse-settings-tabsel').val(e.target.id);

            });

            /*
            * --------------------------------------------------
            * handle toggling panel access from checkbox
            * --------------------------------------------------
            */

            // collect the tab panels into an array
            let contentPanels = $('.content-tab');

            // check for a checkbox checked, find if it is inside a panel
            $(':checkbox').on('click', function () {

                let n = this.name;

                if(n.includes('-used')) {

                    if(this.checked) {

                        // show the panel
                        for (let i = 0; i < contentPanels.length; i++) {
                            if(contentPanels[i].contains(this)) {

                                let p = contentPanels[i].getElementsByClassName('plse-panel-mask')[0];

                                if (p) {
                                    $(p).slideDown( "slow", function() {
                                        // Animation complete.
                                    });
                                }
                                break;
                            }
                        }

                    } else {

                        // hide the panel
                        for (let i = 0; i < contentPanels.length; i++) {
                            if(contentPanels[i].contains(this)) {
                                let p = contentPanels[i].getElementsByClassName('plse-panel-mask')[0];
                                if (p) {
                                    p.style.display = 'none';
                                }

                            }

                        }

                    }

                }

            });

            /* 
             * ---------------------------------------------------
             * accordion jQuery UI initialization
             * {@link https://api.jqueryui.com/accordion/}
             * ---------------------------------------------------
             */
            $('.accordion').accordion({
                collapsible: false,
                active: false,
                heightStyle: "content",
                icons: { "header": "fa fa-chevron-down", "activeHeader": "fa fa-chevron-up" }
            });

        } // end of if(tabs)

        // TODO:
        // TODO:
        // TODO: flag if changes have happened, let user know
        // TODO: so they save them

        /*
         * ---------------------------------------------------
         * Load Yoast Local SEO data (if plugin is present) 
         * into matching plugin fields like phone, email, address
         * NOTE: this just puts the values in, NOT saved to database
         * ---------------------------------------------------
         */
        $('#plse-settings-config-import-yoast-local').on('click', function (e) {

            console.log('loading local seo');
            e.preventDefault(); // stop click from refreshing page

            // when we built the plugin page, we injected two JS variables
            // plse_plugin_options field names for plugin options
            // plse_wpseo_local_options Yoast SEO local
            if ( plse_plugin_options && plse_wpseo_local_options) {

                for (let i in plse_plugin_options) {
                    if (plse_plugin_options[i].yoast_slug) {
                        let slug = plse_plugin_options[i].yoast_slug;
                        let value = plse_wpseo_local_options[slug];
                        if (value) {
                            console.log('assigning value:' + value + ' to field id:' + i)
                            $('#' + i ).val(value);
                        }
                    }
                }

            }

        });

        /*
         * ---------------------------------------------------
         * Load YouTube or Vimeo video embedded player and image thumbnail
         * when input URL is typed in
         * https://developers.google.com/youtube/iframe_api_reference
         * ---------------------------------------------------
         */
        $('.plse-embedded-video-url').on('blur', function (e) {

            e.preventDefault(); // stop click from refreshing page
            let url = e.target.value;

            // https://gist.github.com/yangshun/9892961
            function getVideoId(url) {

                url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

                // youtube, video, dailymotion
                //url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com)|dailymotion.com)\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

                if (RegExp.$3.indexOf('youtu') > -1) {
                    var type = 'youtube';
                } else if (RegExp.$3.indexOf('vimeo') > -1) {
                    var type = 'vimeo';
                }

                // return ID and video service
                return {
                    type: type,
                    id: RegExp.$6
                };
            }

            // look for a field for 'thumbnailURL' and add the thumbnail we get from Youtube or Vimeo to it as the first field.
            function addThumbnailURL (elem, thumb_link) {
                // see if 
                let table = $(elem).parent().parent().parent();
                //////console.log('TABLE IS:' + table)
                if(table) {
                    // look for first repeater <input> field holding thumbnail URLs
                    let urls = table.find('input[name*="[]"]');
                    window.urls = urls;
                    //////console.log('URLS are:' + urls);
                    if (urls[0]) {
                        //////console.log('URLS[0]:' + urls[0])
                        if ( ! urls[0].value ) {
                            urls[0].value = thumb_link;
                        }
                    }
                }
            }

            // get the video ID
            var id = getVideoId(url);
            let url_field = this;

            // create the thumbnail image, based on video service
            if(id.type == 'youtube') {

                // load the video
                $('.plse-embed-video').html('<iframe width="320" height="240" src="//www.youtube.com/embed/' + id.id + '" frameborder="0"></iframe>');

                // load thumbnail
                let thumb_link =  'https://img.youtube.com/vi/' + id.id + '/' + 'hqdefault' + '.jpg';

                $('.plse-upload-repeater-img-box').attr('src', thumb_link);

                // add url to trailer <input> field
                addThumbnailURL(url_field, thumb_link);

            } else if(id.type == 'vimeo') {

                // load the video
                $('.plse-embed-video').html('<iframe src="https://player.vimeo.com/video/' + id.id + '?portrait=0" width="600" height="320" frameborder="0"></iframe> ');

                $.getJSON('https://vimeo.com/api/oembed.json?url=https://vimeo.com/' + id.id, {format: "json"}, function(data) {
                    $('.plse-upload-repeater-img-box').attr('src', data.thumbnail_url);

                    // add url to trailer <input> field
                    addThumbnailURL(url_field, data.thumbnail_url);
                });

            }

        });

        // trigger on startup, so video thumbnails are loaded from video service
        $('.plse-embedded-video-url').trigger('blur');

        /*
         * ---------------------------------------------------
         * Repeater fields
         * NOTE: classes, ids, elements have to exactly match PHP generated HTML
         * in order for the repeaters to work!
         * ---------------------------------------------------
         */

        // add a input repeater row (text, url, url + 'is_image')
        $('.plse-repeater-add-row-btn').on('click', function(e) {

            // find the repeater table
            let btnP = $(this).parent(); // enclosing <p></p>
            let fieldSet = btnP.parent(); // enclosing <div>, with <table> inside
            let table = fieldSet.find('table');

            // find the number of rows on the table. If it is >= max, keep it at max
            length = table.find('tbody>tr').length;
            let max = table.attr('data-max'); 

            // check if we exceeded the maximum allowed number of repeater fields
            if (length > max) {
                $('.plse-repeater-max-warning').css('display','block');
            } else {
                // find the last table row in the control <table> holding repeated fields
                let prev = fieldSet.find('tbody>tr:last');

                // clone the empty, invisible (display:none) row
                let emptyRow = $(fieldSet).find('.plse-repeater-empty-row').clone(true);
                length = table.find('tbody>tr').length;

                let mediaInput, mediaBtn;

                // get the second column (<td>), which might have 1 or 2 buttons in it
                let td2 = $(emptyRow).children()[1];
                let numBtns = td2.children.length;

                // switch adding ids to column 1 (<td>) input field based on number of buttons
                if ( numBtns == 1) {

                    // the 'remove' button is second
                    mediaInput = $(emptyRow).children()[0].children[0];
                    mediaInput.id = mediaInput.id + length;

                } else if (numBtns == 2) {

                    // the 'media_upload' button is first
                    mediaBtn = $(emptyRow).children()[0].children[0];
                    mediaBtn.setAttribute('data-media', mediaBtn.getAttribute('data-media') + length);

                    // the 'remove' button is second
                    mediaInput = $(emptyRow).children()[1].children[1];
                    mediaInput.id = mediaInput.id + length;

                } else {

                    console.error('wrong number of buttons in repeater');

                }

                // change the display of the new row to visible, 'table-row'
                emptyRow.removeClass( 'plse-repeater-empty-row' ).css('display','table-row');
                emptyRow.insertBefore( prev );

                //console.log('adding');

            }

        });

        // remove a text input repeater row
        $('.plse-repeater-remove-row-btn').on('click', function(e) {
            let table = $(this).parents('table'); // enclosing table
            $(this).parents('tr').remove();
            let length = table.find('tbody>tr').length;
            let max = table.attr('data-max'); 
            /////////console.log('table:' + table + ' length:' + length + ' max:' + max)
            if ( length < max+1) { // number of rows not updated
                $('.plse-repeater-max-warning').css('display','none');
            }
        });

        /**
         * ---------------------------------------------------
         * add an <img> tag when an image repeater URL input field is present. 
         * Each time the user enters an image and exits the field, it checks for validity
         * ---------------------------------------------------
         */
        $(".plse-repeater-input.plse-repeater-url-is-image").on('blur', function (e) {
        //$("input[name*='_thumbnail_url'").on('blur', function(e) {
            ///////console.log("blurred out of image thumbnail")
            let url = this.value;
            let col = $(this).parents('td');

            // delete any previously-attached images
            $(col).find('.repeater-thumbnail-img').remove();

            // try to add an image, using the input field URL
            if (!$(col).find('.repeater-thumbnail-img').length) {

                // check if image actually exists
                function imageExists(url, callback) {
                    var img = new Image();
                    img.onload = function() { callback(true); };
                    img.onerror = function() { callback(false); };
                    img.src = url;
                }

                // if the image exists, show it. Otherwise, create a dummy image that is invisible
                imageExists(this.value, function(exists) {
                    if (exists == true)
                    $(col).append('<img style="display:inline-block;border:1px solid black;vertical-align:middle;border:1px solid black;" class="repeater-thumbnail-img" src="' + url + '" width="30" height="30" />');
                    else
                    $(col).append('<img style="display:inline-block;border:1px solid black;vertical-align:middle;" class="repeater-thumbnail-img" width="30" height="30" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==">')
                });

            }

        });

        // fire this once,for all thumbnails on load
        $(".plse-repeater-input.plse-repeater-url-is-image").trigger('blur');


        /*
         * ---------------------------------------------------
         * Duration Picker (HTML5 range slider)
         * use with <input type="range" min="0" max="1000">
         * ---------------------------------------------------
         */

        $('.plse-duration-picker').on('input', function (e) {
        //    console.log('in duration picker');
            let val = e.target.value;
            let hms = new Date(val * 1000).toISOString().substr(11, 8);
            // NOTE: "value=" is only the INITIAL value - have to force the attribute
            this.setAttribute('value', val );
            let p = $(this).parents('div')[0];
            p.getElementsByClassName('plse-slider-output')[0].innerHTML = hms;

        });

        // fire this once on load
        $('.plse-duration-picker').trigger('input');

        /*
         * ---------------------------------------------------
         * select Schema images using the WP Media Library. 
         * We move this here since there's no way
         * to pass control parameters with register_settings(). The ID 
         * needs to match the option name in Settings.
         * 1. IMPORTANT!!!! must call wp_enqueue_media(); in PHP
         * 2. must enqueue all the jQuery UI as 
         *      wp_enqueue_script( 'jquery-ui-core' );
         *      wp_enqueue_script( 'jquery-ui-widget' );
         *      wp_enqueue_script( 'jquery-ui-mouse' );
         *      wp_enqueue_script( 'jquery-ui-accordion' );
         *      wp_enqueue_script( 'jquery-ui-autocomplete' );
         *      wp_enqueue_script( 'jquery-ui-slider' );
         * 3. IMPORTANT!! re-open the mediaUploader EVERY TIME the button is 
         *    clicked. Otherwise, we keep inserting values from the first click
         *    into any other media uploaders on the page
         * https://stackoverflow.com/questions/52216061/how-i-can-use-wordpress-media-uploader-with-multiple-image-upload-button
         * https://wordpress.stackexchange.com/questions/273986/correct-way-to-enqueue-jquery-ui
         * ---------------------------------------------------
         */
        
        var mediaUploader;

        // find all buttons on the page, but process from the one that was clicked
        $('.plse-media-button').on('click', function(e) {

            console.log("CLICKED BY CLASS");
            e.preventDefault();
            var slug = $(this).data('media');
            console.log("BUTTON DATA-slug:" + slug);
            console.log("IMAGE SLUG:" + $('#' + slug + '-img-id').attr('src'));

            // if we do this, media uploader is stuck on the first data-media we return. 
            // if(mediaUploader) {
            //    mediaUploader.open();
            //    return;
            // }

            mediaUploader = wp.media.frames.meta_image_frame = wp.media({
                title: 'Select or upload image',
                frame: 'select',
                library: { // remove these to show all
                   type: 'image' // specific mime
                },
                button : { text : 'Insert' },
                multiple: false  // Set to true to allow multiple files to be selected
            });

            mediaUploader.on('select', function () {

                // return the uploaded file data
                let attachment = mediaUploader.state().get('selection').first().toJSON();

                console.log('SELECT ATTACH:' + attachment.url);
                console.log('SELECT slug:' + slug);

                // update the input field with the revised URL
                $('#' + slug).val(attachment.url);

                // preview
                $('#' + slug + '-img-id').attr('src',attachment.url);

                // IMPORTANT - close after every upload
                mediaUploader.close();

                // if we just added an image URL to a repeater field, create the thumbnail
                $(".plse-repeater-input.plse-repeater-url-is-image").trigger('blur');

            });

            mediaUploader.on('open', function () {

            });

            mediaUploader.on('close', function () {

            });

            // IMPORTANT - fire this on every click
            mediaUploader.open();

        });

        /**
         * ---------------------------------------------------
         * Unfortunately, there is no way to detect saved changes when 
         * navigating from one plugin to another that doesn't trigger 
         * the annoying 'beforeunload' dialog everywhere.
         * 
         * You can compare form data before and after with:
         * let initialData = $(theform).serialize();
         * ----------------------------------------------------
         */
        var theform = jQuery('#plse-options-form')[0]; // first form only

    }); // endof window.load

})( jQuery );

