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

        /*
         * ---------------------------------------------------
         * Load Yoast Local SEO data (if plugin is present) 
         * into matching plugin fields like phone, email, address
         * NOTE: this just puts the values in, NOT saved to database
         * ---------------------------------------------------
         */
        // https://www.jackreichert.com/2013/03/using-ajax-in-wordpress-development-the-quickstart-guide/
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
         * Load YouTube Video when input URL is typed in
         * https://developers.google.com/youtube/iframe_api_reference
         * ---------------------------------------------------
         */
        $('.plse-embedded-video-url').on('blur', function (e) {

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

            var id = getVideoId(url);

            if(id.type == 'youtube') {

                // load the video
                $('.plse-embed-video').html('<iframe width="320" height="240" src="//www.youtube.com/embed/' + id.id + '" frameborder="0"></iframe>');

                // load thumbnail
                let thumb_link =  'https://img.youtube.com/vi/' + id.id + '/' + 'hqdefault' + '.jpg';

                $('.plse-upload-img-video-box').attr('src', thumb_link);

            } else if(id.type == 'vimeo') {

                // load the video
                $('.plse-embed-video').html('<iframe src="https://player.vimeo.com/video/' + id.id + '?portrait=0" width="600" height="320" frameborder="0"></iframe> ');

                // load thumbnail

                $.getJSON('https://vimeo.com/api/oembed.json?url=https://vimeo.com/' + id.id, {format: "json"}, function(data) {
                    $('.plse-upload-img-video-box').attr('src', data.thumbnail_url);
                });

            }

        });

        // trigger on startup
        $('.plse-embedded-video-url').trigger('blur');

        /*
         * ---------------------------------------------------
         * Repeater fields
         * ---------------------------------------------------
         */

        // add a text input repeater row
        $('.plse-repeater-add-row-btn').on('click', function(e) {

            let btnP = $(this).parent(); // enclosing <p></p>
            let fieldSet = btnP.parent(); // enclosing <div>, with <table> inside

            // find the last table row in the control <table> holding repeated fields
            let prev = fieldSet.find('tbody>tr:last');

            let emptyRow = $(fieldSet).find('.plse-repeater-empty-row').clone(true);
            emptyRow.removeClass( 'plse-repeater-empty-row' ).css('display','table-row');
            emptyRow.insertBefore( prev );

            console.log('adding');

        });

        // remove a text input repeater row
        $('.plse-repeater-remove-row-btn').on('click', function(e) {
            $(this).parents('tr').remove();
        });

        /*
         * ---------------------------------------------------
         * Datetimepicker
         * ---------------------------------------------------
         */
        $('.plse-datetimepicker').on('change', function () {
            //var dateEl = document.getElementById('date');
            //var timeEl = document.getElementById('time');
            this.querySelector('[type="date"]')[0].innerHTML = dateE1.type;
            this.queryselector('[type="time"]')[0].innerHTML = timeE1.type;
            //document.getElementById('date-output').innerHTML = dateEl.type === 'date';
            //document.getElementById('time-output').innerHTML = timeEl.type === 'time';
        });

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

        // find all but buttons on the page, but process from the one that was clicked
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

            });

            mediaUploader.on('open', function () {

            });

            mediaUploader.on('close', function () {

            });

            // IMPORTANT - fire this on every click
            mediaUploader.open();

        });

        /**
         *  ---------------------------------------------------
         * detect unsaved changes on the option forms
         * ----------------------------------------------------
         */
        var theform = jQuery('#plse-options-form')[0]; // first form only

            var needToConfirm = false;

            // prevent the onbeforeunload event from firing if we are submitting the form
            $(theform).submit(function(e) {
                window.onbeforeunload = null;
            });

            // grab the initial form data before starting
            let initialData = $(theform).serialize();	

            // if we just "click away" from the plugin, put up a warning message
            window.onbeforeunload = function(e) {

                console.log('onbeforeunload event fired')

                var e = e || window.event, simon = "go"; //, needToConfirm = false;

                // newer browsers won't alter the message, done for back-compatibility
                if (initialData !== $(theform).serialize()) {
                    const unsaved_changes_warning = "Changes you made may not be saved.";
                    e.returnValue = unsaved_changes_warning;
                    return unsaved_changes_warning;
                }

            } // onbeforeunload callback

    }); // endof window.load


})( jQuery );