<?php

/**
 * Initialize the plugin options and metabox, provide methods used by 
 * both options and metabox fields.
 *
 * @since      1.0.0
 * @category   WordPress_Plugin
 * @package    PLSE_SCHEMA_Extender
 * @subpackage PlyoSchema_Extender/admin
 * @author     Pete Markeiwicz <pindiespace@gmail.com>
 * @license    GPL-2.0+
 * @link       https://plyojump.com
 */
class PLSE_Init {

    /**
     * Store reference for singleton pattern.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $instance    static reference to initialized class.
     */
    static private $__instance = null;

    /**
     * Admin JS for this plugin in options and metaboxes.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string     $plse_admin_js
     */
    private $plse_admin_js = 'admin/js/plyo-schema-extender-admin.js';

    /**
     * Admin CSS for this plugin in options and metaboxes.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $plse_admin_css
     */
    private $plse_admin_css = 'admin/css/plyo-schema-extender-admin.css';

    /**
     * Language subdirectory
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $language_dir
     */
    private $language_dir = 'languages';

    /**
     * Includes subdirectory (holds most plugin files)
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $language_dir
     */
    private $includes_dir = 'includes';

    /**
     * Schema subdirectory in the plugin.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string     $schema_dir
     */
    private $schema_dir = 'schema';

    /**
     * Class name prefix.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $schema_classname_prefix
     */
    private $schema_classname_prefix = 'PLSE_';

    /**
     * Prefix for schema files.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $schema_file_prefix
     */
    private $schema_file_prefix = 'plse-schema-';

    /**
     * Slug prefix for metabox values.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $meta_slug_prefix
     */
    private $metabox_slug_prefix = PLSE_SCHEMA_EXTENDER_SLUG;

    /**
     * Plugin menu name in WP_Admin.
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_menu_name    plugin menu name in WP_Admin.
     */
    private $plugin_menu_title = 'Plyo Schema Ext';

    /**
     * Slug name for plugin options page.
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_menu_slug    The base slug for plugin options page.
     */
    private $plugin_menu_slug  = 'plse-options-page';

    /**
     * The the returned $option value for the selected checkbox or radio option.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $option_group    name for storing plugin options.
     */
    private $ON = 'on';

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct() {

        // internationalization
        $this->l10ni18n();

        add_action( 'init', [ $this, 'add_taxonomies_to_pages' ], 100 );

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Init    $self__instance
     */
    public static function getInstance () {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Init();
        }
        return self::$__instance;
    }

    /**
     * Load the textdomain for the plugin.
     * TODO: add languages.
     * @since    1.0.0
     * @access   public
     */
    private function l10ni18n () {

        $loaded = load_plugin_textdomain( PLSE_SCHEMA_EXTENDER_SLUG, false, basename( dirname( __FILE__ ) ) . '/languages' );

        if ( ! $loaded ) {
            $loaded = load_muplugin_textdomain( PLSE_SCHEMA_EXTENDER_SLUG, basename( dirname( __FILE__ ) ) . '/languages/' );
        }

        if ( ! $loaded ) {
            $loaded = load_theme_textdomain( PLSE_SCHEMA_EXTENDER_SLUG, get_stylesheet_directory() . '/languages/' );
        }

        // manually determine locale
        if ( ! $loaded ) {
            $locale = apply_filters( 'plugin_locale', function_exists( 'determine_locale' ) ? determine_locale() : get_locale(), 'cmb2' );
            $mofile = dirname( __FILE__ ) . '/' . $this->language_dir . '/' .PLSE_SCHEMA_EXTENDER_SLUG . '-' . $locale . '.mo';
            load_textdomain( PLSE_SCHEMA_EXTENDER_SLUG, $mofile );
        }


    }

    /**
     * ----------------------------------------------------------------------
     * LOAD SCRIPTS AND STYLES
     * ----------------------------------------------------------------------
     */

    /**
     * Enqueue scripts and styles used in admin area. 
     * 'admin_enqueue_scripts' hook. 
     * 
     * @since    1.0.0
     * @access   public
     */
    public function load_admin_scripts () {

        $url = plugin_dir_url( __FILE__ );

        // load our fonts
        wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
        wp_enqueue_style( 'load-osans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,700' );

        // load our plugin CSS
        wp_enqueue_style( PLSE_SCHEMA_EXTENDER_SLUG, $url . $this->plse_admin_css, array(), $this->version, 'all' );

        /*
         * load jQuery UI for accordion, tabbed options, and media library ajax
         * jQuery UI is registered by core WP. to make the media library loads work,
         * all of these need to be enqueued.
         */
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-mouse' );
        wp_enqueue_script( 'jquery-ui-accordion' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'jquery-ui-slider' );

        // load WP media files (so we can do Ajax calls to use the Media Library)
        wp_enqueue_media();

        // load our plugin-specific JS
        wp_enqueue_script( PLSE_SCHEMA_EXTENDER_SLUG, $url . $this->plse_admin_js, array('jquery'), null, true );

        // the calling methods in PLSE_Settings and PLSE_Meta may inject additional JS after this
        return PLSE_SCHEMA_EXTENDER_SLUG;

    }

    /**
     * Pass globals from PHP into the JavaScript as an JS object. 
     * Adds IDs for jQuery click handlers for all the WP Media Library upload buttons.
     * Note: the array of slugs loads into global space. It is processed 
     * in plyo-schema-extender.js window.load() callback.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $script_label the label for the script which has this as a dependency.
     * @param    string    $script_var_name the JS variable name
     * @param    array     $fields the fields to add
     */
    public function load_js_passthrough_script ( $script_label, $script_var_name, $field_group ) {

        $js = "\nvar " . $script_var_name . " = {\n";

            foreach( $field_group as $key => $field ) {

                // this is something from PLSE_Options_Data, or a plse-schema-xxxx.php file
                if ( isset( $field['type'] ) ) {
                    $js .= "\n'" . $field['slug'] . "': { 'yoast_slug':'" . $field['yoast_slug'] . "', 'value': ''},";
                }

                else { // assume it is a simple associative array
                    $js .= "\n'" . $key . "':'" . $field . "',";
                }

            }

        // trim the trailing comma ','
        $js = substr($js, 0, -1);
        $js .= "\n};\n";

        if ( $js ) {
            wp_add_inline_script( $script_label, $js, 'before' );
        }

    }


    /**
     * add taxonomies to pages, not just posts. Helpful for assigning Schema to 
     * individual pages. Call by 'init' - runs each time WordPress has finished 
     * loading but before any headers are sent.
     * {@link https://code.tutsplus.com/articles/wordpress-initialization-hooks-benefits-and-common-mistakes--wp-34427}
     * {@link https://thewphosting.com/add-categories-tags-pages-wordpress/}
     * 
     * @since    1.0.0
     * @access   public
     */
    public function add_taxonomies_to_pages () {
        $this->add_taxonomies_to_cpt( 'page' );
    }

    /**
     * Add taxonomies (categories and tags) to a CPT. This is 
     * distinct from defined custom taxonomies added to the CPT.
     * Call by 'init'
     * 
     * @since    1.0.0
     * @access   public
     */
    public function add_taxonomies_to_cpt ( $cpt_name ) {

        if ( $cpt_name ) {

            $taxonomies = get_object_taxonomies( $cpt_name );
            if ( ! in_array( $cpt_name, $taxonomies ) ) {
                register_taxonomy_for_object_type( 'post_tag', $cpt_name );
                register_taxonomy_for_object_type( 'category', $cpt_name );
            }

        }

    }

    /**
     * Add categories and tags to all CPTs. Independent of any 
     * custom taxonomies added to the CPT.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function add_taxonomies_to_all_cpts () {

        $my_cpt_names = $this->get_all_cpt_names();

        // if category and tag menus are missing for CPT, add it
        foreach ( $my_cpt_names as $name ) {

            //check if taxonomy is already added
            $taxonomies = get_object_taxonomies( $name );

            if ( empty( $taxonomies ) ) {
                register_taxonomy_for_object_type( 'post_tag', $name );
                register_taxonomy_for_object_type( 'category', $name );
            }

        }

    }

    /**
     * set category and tag archives in admin mode.
     * 
     * @since    1.0.0
     * @access   public
     * @param    WP_Query    $wp_query WP_Query object to modify
     * 
     * @param WP_QUERY $wp_query
     */
    public function category_and_tag_archives( $wp_query ) {

        if ( ! is_admin() && $wp_query->is_main_query() ) {

            if ( is_archive() || is_category() || is_home() ) {

                //get_all_cpt_names
                $my_cpt_names = $this->get_all_cpt_names();

                // Add CPT to the category
                $wp_query->set( 'post_type', $my_cpt_names );
            }

            if ( $wp_query->is_search() ) {
                // Add CPT to the search
                $wp_query->set( 'post_type', $my_cpt_names );
            }

        }

    }

    /**
     * -----------------------------------------------------------------------
     * GET POST INFORMATION
     * -----------------------------------------------------------------------
     */

    /**
     * Get text between specific HTML tags. Used to get a default title
     * when the 'name' field for a Schema isn't filled out.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array    $titles an array with all the content between the specified tags.
     */
    public function get_tags_from_content ( $tagname, $post ) {

        $content = get_the_content( $post );
        $findTags = preg_match_all( '/<' . $tagname . ' ?.*>(.*)<\/' . $tagname . '>/i', $content, $tags );
        if ( is_array( $tags ) && isset( $tags[0] ) ) {
            return $tags[0]; // first array element contains all the tags
        }

    }

    /**
     * Get the post excerpt. Used in descriptions, if not over-ridden by 
     * Schema descriptions for values in plugin options.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    the excerpt (which may be an empty string)
     */
    public function get_excerpt_from_content( WP_Post $post, $trim_chars = 150, $more = '&hellip;' ) {

        $excerpt = '';

        if ( is_a( $post, 'WP_Post' ) ) {

            // try getting the excerpt itself
            $excerpt = get_the_excerpt( $this->post );

            // if there's no excerpt, grab content from the post
            if ( empty( $excerpt ) ) {

                // position of first occurrence of a string within another, case insensitive
                $more_pos = mb_stripos( $post->post_content, '<!--more-->' );

                if ( $more_pos != false ) {
                    $excerpt = mb_substr( $post->post_content, 0, $more_pos );
                } else {
                    $excerpt = empty( $post->post_excerpt ) ? $post->post_content : $post->post_excerpt;
                }

                // strip shortcodes and blocks
                global $wp_version;
                if ( version_compare( $wp_version, '5.0', '>=' ) ) {
                    $excerpt = excerpt_remove_blocks( strip_shortcodes( $excerpt ) );
                } else {
                    $excerpt = strip_shortcodes( $excerpt );
                }

                // strip all NULL bytes, HTML and PHP tags
                $excerpt = trim( strip_tags( $excerpt ) );

                // add the 'more' entity
                if ( ! empty( $excerpt ) ) $excerpt = mb_substr( $excerpt, 0, $trim_chars, 'UTF-8' ) . $more;

            }

        }

        return $excerpt;

    }

    /**
     * Return the thumbail url for the featured image of the post
     * 
     * @since    1.0.0
     * @access   public
     * @return   string<url>    a text string giving the URL of the featured image thumbnail
     */
    public function get_featured_image_url ( WP_Post $post, $size = 'full' ) {
        return get_the_post_thumbnail_url( $post->ID, $size );
    }

    /**
     * get all meta data for a featured image for a post, used to create ImageObject.
     * 
     * @since    1.0.0
     * @access   public
     * @param    WP_POST    $post    the current post
     * @param    (string|int)    $size    image size in WP installation
     * @return   array    metadata for the featured image
     */
    public function get_featured_image_meta ( WP_Post $post, $size = 'full' ) {
        return $this->get_image_meta( get_post_thumbnail_id( $post ), $size );
    }


    /**
     * Get the first image in a post, return all meta data.
     * 
     * @since    1.0.0
     * @access   public
     * @param    (string|int) $size    image size in WP installation
     * @return   (array)      $img    image-related information extracted from the <figure>
     */
    public function get_first_post_image_meta ( WP_Post $post, $size = 'full' ) {

        $image_meta = '';

        if ( is_a( $post, 'WP_Post' ) ) {
            // grab the first image on the post
            $files = get_children('post_parent='.get_the_ID().'&post_type=attachment&post_mime_type=image&order=desc');

            // loop through the array, grabbing the first image (last one in array)
            if( $files ) {
                $keys = array_reverse( array_keys( $files ) );
                $j = 0;
                $attachment_id = $keys[ $j ];
                $image_meta = $this->get_image_meta( $attachment_id );

                return $image_meta;

            }
        }

        return $image_meta;
    }

    /**
     * Get image meta-data.
     * 
     * @since    1.0.0
     * @access   public
     * @param    number    $image_id    the id of the image
     * @param    string    $size    which size of image to get
     * @return   array     image meta-data
     */
    public function get_image_meta ( $image_id, $size = 'full' ) {

        $img = array();
        $i = wp_get_attachment_image_src( $image_id, $size );

        // create the image array
        $img['src']     = $i[0];
        $img['width']   = $i[1];
        $img['height']  = $i[2];
        $img['size']    = $size;

        // get additional information
        $img_post = get_post( $image_id );

        $img['title'] = $img_post->post_title;
        $img['caption'] = $img_post->post_excerpt; //image caption from media library
        $img['alt'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true ); // image alt= text
        $img['description'] = $img_post->post_content; // image description

        return $img;
    }

    /**
     * Create an ImageObject, using data returned from meta-data for an image 
     * using PLSE_Init::get_image_meta();
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $meta_arr    meta-data extracted from image post
     */
    public function get_image_object_from_meta ( $meta_arr ) {
        return array(
            '@type'  => 'ImageObject',
            '@id'    => $meta_arr['src'], // use the image path
            'inLanguage' => 'en-US',
            'url' => $meta_arr['src'],
            'width' => $meta_arr['width'],
            'height' => $meta_arr['height'],
            'caption' => $meta_arr['caption']
        );
    }

    /**
     * Get the the url of the first image in post content. 
     * Use where there is no image availabe from:
     * - featured image url
     * - metabox image url
     * 
     * @since     1.0.0
     * @access    public
     * @param     WP_POST    $post    the current post
     * @param     (string|int)    $size    image size in WP installation
     */
    public function get_first_post_image_url ( WP_Post $post, $size = 'full' ) {

        $img = '';

        if ( is_a( $post, 'WP_Post' ) ) {

            // grab the first image on the post
            $files = get_children('post_parent='.get_the_ID().'&post_type=attachment&post_mime_type=image&order=desc');

            // loop through the array, grabbing the first image (last one in array)
            if( $files ) {
                $keys = array_reverse( array_keys( $files ) );
                $j = 0;
                $attachment_id = $keys[ $j ];
                $img = wp_get_attachment_image_src( $attachment_id, $size );
            }

        }

        return $img;

    }

    /**
     * -----------------------------------------------------------------------
     * GET SCHEMA INFORMATION
     * -----------------------------------------------------------------------
     */

    /**
     * Look in the plugin's Schema directory. 
     * Extract current Schema file list
     * pattern: 'plse-schema-xxxx.php' to 'XXX', plse-schema-game.php to 'GAME.'
     * 
     * @since    1.0.0
     * @access   private
     * @return   array    a list of the defined Schemas, capitalized
     */
    public function get_available_schemas () {

        $schemas = array();

        // construct Schema file names
        $patterns = array( '/' . $this->schema_file_prefix . '/', '/.php/' );
        $replacements = array( '', '' );
        $dir = plugin_dir_path( dirname( __FILE__ ) ) . $this->includes_dir . '/' . $this->schema_dir . '/';

        $handle = opendir( $dir );

        if ( $handle ) {

            while ( false !== ( $entry = readdir( $handle ) ) ) { 

                // strip out non-schema substrings
                if ( $entry != "." && $entry != ".." ) {
                    $schemas[] = $this->label_to_slug( preg_replace( $patterns, $replacements, $entry ) );
                }

            }

            closedir( $handle );

        }

        return $schemas;

    }

    /**
     * Get the classnames for each declared schema.
     */
    public function get_available_classes () {

        $classes = array();

        $schemas = $this->get_available_schemas();

        foreach ( $schemas as $schema ) {
            $classes[] = $this->schema_classname_prefix . ucfirst( strtolower( $schema ) );
        }

        return classes;

    }

    /**
     * Attach new Schemas to Yoast.
     * NOTE: must be added early when plugin has just loaded, waiting for 'the_content' hook is too late.
     * 
     * {@link https://developer.yoast.com/features/schema/api/#to-add-or-remove-graph-pieces}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array  $pieces array used to create JSON-LD graph.
     * @param    \WPSEO_Schema_Context $context Object with context variables.
     */
    public function add_schemas () {

        add_filter( 'wpseo_schema_graph_pieces', function( $pieces, $context ) {

            $schemas = $this->get_available_schemas();

            foreach ( $schemas as $schema ) {

                $classname = $this->schema_classname_prefix . ucfirst( $this->schema_dir ) . '_' . ucfirst( strtolower( $schema ) );

                if ( ! class_exists( $classname ) ) {

                    $class_path = plugin_dir_path( dirname( __FILE__ ) ) . $this->includes_dir . '/'. $this->schema_dir . '/' . $this->schema_file_prefix . strtolower( $schema ) . '.php';

                    require $class_path;

                    $pieces[] = $classname::getInstance( $context );

                }

            }

            return $pieces;

        }, 11, 2 );

    }


    /**
     * Remove Yoast breadcrumbs.
     * add_filter( 'wpseo_schema_graph_pieces', function( $pieces, $context )...
     * 
     * @since    1.0.0
     * @access   public
     * @param    array                 $pieces  graph pieces to output.
     * @param    \WPSEO_Schema_Context $context Yoast object with context variables.
     * @return    instanceof \Yoast\WP\SEO\Generators\Schema\Breadcrumb;
     */
    public function remove_breadcrumbs_from_schema ( $pieces, $context ) {
        // remove a breadcrumb
        unset( $pieces['breadcrumb'] );
        return \array_filter( $pieces, function( $piece ) {
            return ! $piece instanceof \Yoast\WP\SEO\Generators\Schema\Breadcrumb;
        } );
    }

    /**
     * Remove Yoast Person.
     * add_filter( 'wpseo_schema_graph_pieces', function( $pieces, $context )...
     * 
     * @since    1.0.0
     * @access   public
     * @param    array                 $pieces  graph pieces to output.
     * @param    \WPSEO_Schema_Context $context Yoast object with context variables.
     * @return   instanceof \Yoast\WP\SEO\Generators\Schema\Breadcrumb;
     */
    function remove_person_from_schema ( $pieces, $context ) {
        unset( $pieces['person'] );
        return \array_filter( $pieces, function( $piece ) {
            return ! $piece instanceof \Yoast\WP\SEO\Generators\Schema\Person;
        } );
    }

    /**
     * -----------------------------------------------------------------------
     * DEFAULT VALUES
     * -----------------------------------------------------------------------
     */


    /**
     * Return the value which a checked checkbox returns in a form
     * 
     * @since    1.0.0
     * @access   public
     * @return   string   the 'on' value returned by checkboxes
     */
    public function get_checkbox_on () {
        return $this->ON;
    }

    /**
     * Return the schema directory
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    the currently use schema directory
     */
    public function get_schema_dirname () {
        return $this->schema_dir;
    }

    /**
     * Return the prefix used by metaboxes to create slugs
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    the prefix to put in front of metabox slugs, e.g. 'plyo-schema-extender' . 'game_name'
     */
    public function get_metabox_slug_prefix () {
        return $this->metabox_slug_prefix;
    }

    /**
     * Return the prefix for schema files, e.g. 'plse-schema-' for 
     * includes/schema/plse-schema-game.php
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    the currently-used schema prefix
     */
    public function get_schema_file_prefix () {
        return $this->schema_file_prefix;
    }

    /**
     * Return the logo image for the plugin, hard-coded width and height. Note relative path ../img
     * @since    1.0.0
     * @access   public
     * @return   string
     */
    public function get_logo () {
        return '<img src="' .  esc_url( plugins_url( '../assets/images/plyo-schema-extender-logo.png', __FILE__ ) ) . '" width="243" height="135" alt="' . $this->plugin_name . '" >';
    }

    /**
     * Return a placeholder icon for uploaded images and icons in Schema
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    the URL of the placeholder icon
     */
    public function get_default_placeholder_icon_url () {
        return esc_url( plugins_url( '../assets/images/plyo-schema-extender-logo-placeholder.png', __FILE__ ) );
    }

    /**
     * Return a placeholder icon for uploaded images and icons in Schema
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    the URL of the placeholder icon
     */
    public function get_default_placeholder_image_url () {
        return esc_url( plugins_url( '../assets/images/plyo-schema-extender-image-placeholder.png', __FILE__ ) );
    }

    /**
     * Get the thumbnail image URL from a YouTube video URL
     * {@link https://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api?rq=1}
     * @since    1.0.0
     * @access   public
     * @return   string    the URL of the thumbnail associated with the video
     */
    public function get_youtube_thumb( $url, $type = 'small' ) {

        $url = esc_url( $url );
        $video_id = explode( '?v=', $url );

        if ( empty( $video_id[1] ) ) {
            $video_id = explode( "/v/", $url );
            $video_id = explode( '&', $video_id[1] );
            $video_id = $video_id[0];
        } else {
            $video_id = $video_id[1];
        }

        $thumb_link = '';

        if ( $type == 'small' || $type == 'default' ) $type = 'hqdefault';
        else if ( $type == 'medium' ) $type = 'sddefault';
        else if ( $type == 'large' ) $type = 'maxresdefault';

        if ( $type == 'default'   || $type == 'hqdefault' ||
           $type == 'mqdefault' || $type == 'sddefault' ||
           $type == 'maxresdefault') {

            $thumb_link =  'http://img.youtube.com/vi/' . $video_id . '/' . $type . '.jpg';

        } else if ( $type == "id" ) {
            $thumb_link = $video_id;
        }

        return $thumb_link;

    }

    /**
     * Get the Vimeo video thumbnail
     */
    public function get_vimeo_thumb( $url, $type = 'small' ) {

        $url = esc_url( $url );
        $video_id = '';
        $thumb_link = '';
    
        if (preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $regs)) {
            $video_id = $regs[3];
        }

        if ( $id ) {
            $vimeo = unserialize( file_get_contents( 'https://vimeo.com/api/v2/video/' . $id . '.php' ) );
            if ( $type == 'small') $thumb_link = $vimeo[0]['thumbnail_small'];
            else if ( $type == 'medium') $thumb_link = $vimeo[0]['thumbnail_medium'];
            else if ( $type == 'large' ) $thumb_link =  $vimeo[0]['thumbnail_large'];
            else if ( $type == 'id' ) $thumb_link = $video_id;
        }

        return $thumb_link;
    }

    /**
     * Get video thumbnail from a variety of streaming services
     */
    public function get_video_thumb( $url, $type = 'small' ) {

        if ( strpos($url, 'youtube') !== false ) {
            return $this->get_youtube_thumb( $url );
        } 

        if ( strpos( $url, 'vimeo' ) !== false ) {
            return $this->get_vimeo_thumb( $url );
        }

    }

    /**
     * Get an array, filtering out empty entries.
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $value    a serialized array, 
     *           e.g. $values[ $fields['service_action_target']['slug'] ] NOTE: NO ZERO [0]
     * @param    array    $value    standard array, suitable for JSON
     */
    public function get_array_from_serialized ( $value ) {
        $value = maybe_unserialize( $value );
        if ( is_array( $value ) ) {
            $value = unserialize( $value[0] );
            if ( is_array( $value ) ) {
                $value = array_filter( $value, function ( $var ) {
                    return ($var !== NULL && $var !== FALSE && $var !== "");
                });
            }
        }
        return $value;
    }

    /**
     * -----------------------------------------------------------------------
     * DATE AND TIME CONVERSIONS
     * -----------------------------------------------------------------------
     */

    /**
     * Convert time duration, in seconds to ISO8601
     * {@link https://stackoverflow.com/questions/30916902/php-convert-integer-number-of-seconds-to-iso8601-format}
     * 
     * @since    1.0.0
     * @access   public
     * @return   string   $duration the ISO860 formated duration
     */
    public function seconds_to_ISO860_duration ( $seconds ) {

        $intervals = array('D' => 60*60*24, 'H' => 60*60, 'M' => 60, 'S' => 1);
        $result = '';

        foreach ( $intervals as $tag => $divisor ) {
            $qty = floor( $seconds/$divisor );
            if ( !$qty && $result == '' ) {
                $pt = 'T';
                continue;
            }
            
            $seconds -= $qty * $divisor;
            $result  .= "$qty$tag";
        }

        if ( $result=='' ) $result='0S';

        return 'P' . $result;

    }

    /**
     * -----------------------------------------------------------------------
     * POSTS, POST TYPES, CATEGORIES, TAGS
     * -----------------------------------------------------------------------
     */

    /**
     * Get the current post type
     * 
     * @since    1.0.0
     * @access   public
     * @return   string     the current post, WP_Post
     */
    public function get_post () {

        global $post, $current_screen;

        // we have a post so we can just get the post type from that
        if ( $post && $post->post_type ) return $post;

        // check the global $current_screen object - set in sceen.php
        elseif ( $current_screen && $current_screen->post_type ) return $current_screen;

        // if current page is post.php and post isset(), query for its post type 
        elseif ( $pagenow === 'post.php'  && isset( $_GET['post'] ) ) {
            $post_id = $_GET['post'];
            return get_post( $post_id );
        }

        // post type unknown
        return null;

      }

    /**
     * Get the type of the current post.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    the current post type (slug)
     */
    public function get_post_cpt () {

        // check the global $typenow - set in admin.php
        global $typenow;
        if ( $typenow ) return $typenow;

        // check the global post
        $post = $this->get_post();
        if ( $post ) return $post->post_type;

        // try to pick it up from the query string
        if ( ! empty( $_GET['post'] ) ) {
            $post = get_post( $_GET['post'] );
            $typenow = $post->post_type;
        }

        // try to pick it up from the quick edit AJAX post
        elseif ( ! empty( $_POST['post_ID'] ) ) {
            $post = get_post( $_POST['post_ID'] );
            $typenow = $post->post_type;
        }

        return $typenow;

    }

    /**
     * Extract the option and value from a returned list of Custom Post Types.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $cpts   an array of all custom post types defined in the system
     * @return   array    $option_list the list of options, formatted as $key => $value
     */
    public function get_option_list_from_cpts ( $cpts ) {
        $option_list = array();

        foreach ( $cpts as $cpt ) {

            $option_list[ $cpt->label ] = $cpt->rewrite['slug'];

        }
        return $option_list;
    }

    /**
     * Get a standard option_list from returned categories.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array   $cats    list of categories defined for the post
     * @return   array   $option_list the list of categories, formatted as key => value
     */
    public function get_option_list_from_cats ( $cats ) {

        $option_list = array();

        if ( is_array( $cats ) ) {
            foreach ( $cats as $term ) {
                // a WP_Term object, not an array...
                $option_list[ $term->name ] = $term->slug;
            }
        }
        return $option_list;
    }

    /**
     * Get the full Custom Post Type List (non-built-in)
     * by setting to 'public' we ignore the pb_* post types
     * @since    1.0.0
     * @access   public
     * @return   array    $post_types    return all the Custom Post Types
     */
    public function get_all_cpts () {
        $args = array(
            'public'   => true,
            '_builtin' => false
        );
        $post_types = get_post_types( $args, 'objects' );
        return $post_types;
    }

    /**
     * Get the full category list
     * @since    1.0.0
     * @access   public
     * @return   array    $cats    return categories
     */
    public function get_all_cats () {
        $args = array(
            'hide_empty' => 0,
        );
        $cats = get_categories( $args );
        return $cats;
    }

    /**
     * -----------------------------------------------------------------------
     * DATA RETRIEVED FROM YOAST SEO
     * -----------------------------------------------------------------------
     */

    /**
     * Fetch organization social profiles from Yoast (must be present)
     * {@link https://wordpress.org/support/topic/bad-code-implementation/}
     */
    public function get_social_profiles ( $helpers ) {

        $profiles        = [];
        $social_profiles = [
            'facebook_site',
            'instagram_url',
            'linkedin_url',
            'myspace_url',
            'youtube_url',
            'pinterest_url',
            'wikipedia_url',
            'twitter_site'
        ];

        foreach ( $social_profiles as $profile ) {
            $social_profile = $helpers->options->get( $profile, '' );
            if ( $social_profile !== '' ) {
                    $prefix     = ($profile === 'twitter_site') ? 'https://twitter.com/'  : '';
                $profiles[] = urldecode( $prefix . $social_profile );
            }
        }

        /**
         * Filter: 'wpseo_schema_organization_social_profiles'
         * Allows filtering social profiles for the represented organization.
         *
         * @api string[] $profiles
         */
        $profiles = \apply_filters( 'wpseo_schema_organization_social_profiles', $profiles );

        return $profiles;
    }

    /**
     * Adds a term or multiple terms, comma separated, to a field.
     *
     * @param array  $data     Article data.
     * @param string $key      The key in data to save the terms in.
     * @param string $taxonomy The taxonomy to retrieve the terms from.
     *
     * @return mixed array $data Article data.
     */
    private function add_keywords( $data, $key, $taxonomy ) {

        $terms = get_the_terms( $this->context->id, $taxonomy );

        if ( is_array( $terms ) ) {
            $keywords = array();
            foreach ( $terms as $term ) {
                // We are checking against the WordPress internal translation.
                // @codingStandardsIgnoreLine
                if ( $term->name !== __( 'Uncategorized' ) ) {
                    $keywords[] = $term->name;
                }
            }
            $data[ $key ] = implode( ',', $keywords );
        }

        return $data;
    }


    /**
     * -----------------------------------------------------------------------
     * SCHEMA UTILITIES
     * -----------------------------------------------------------------------
     */

    /**
     * Convert slug (e.g. 'game') to label (e.g. 'GAME' )
     */
    public function slug_to_label ( $slug ) {
        return strtoupper( $slug );
    }

    /**
     * Convert label (e.g. 'GAME') to slug (e.g. 'game')
     */
    public function label_to_slug ( $label ) {
        return strtolower( $label );
    }

    /**
     * Convert label to corresponding class slug
     * 'GAME' to 'Game'
     */
    public function label_to_class_slug ( $label ) {
        return ucfirst( strtolower( $label ) );
    }

    /**
     * Get image properties from a URL, useful in creating ImageObject
     * 
     * @since    1.0.0
     * @access   public
     * @return   array
     */
    public function get_image_properties_from_url ( $url ) {
        $image_url = attachment_url_to_postid( $url );
        $props = wp_get_attachment_image_src( $image_url, 'full' );
        return array(
            'url' => $props[0],
            'width' => $props[1],
            'height' => $props[2],
            'ratio' => intval( $props[1] ) / intval( $props[2] ) // width to height ratio
        );
    }

    /**
     * If there is an error in meta-fields or rendering the Schema, put a flag up 
     * in the plugin options screen. Examples include:
     * - a Schema which is missing required fields
     * - an Event which is over, or with invalid dates
     * - multiple broken links
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field  the field with the error (use $field['err'])
     * @param    boolean   $write if set to true, write, otherwise clear the field
     */
    public function plugin_options_field_warning ( $field, $erase = false ) {

        //delete_option( PLSE_OPTIONS_FIELD_WARNING );

        $curr_warning_arr = get_option( PLSE_OPTIONS_FIELD_WARNING );

        if ( $erase == PLSE_ERASE ) {

            if ( isset ( $curr_warning_arr[ $field['slug'] ] ) ) {
                unset( $curr_warning_arr[ $field['slug'] ] );
            }

        } else {

            if ( ! is_array( $curr_warning_arr ) ) $curr_warning_arr = array();

            $curr_warning_arr[ $field['slug'] ] = array( $field['err'], $field['post_id'], date( 'm/d/Y h:i:s a', time() ) );

        }

        // update the error array
        update_option( PLSE_OPTIONS_FIELD_WARNING, $curr_warning_arr );

    }

    /**
     * -----------------------------------------------------------------------
     * COMPATIBILITY CHECKS
     * -----------------------------------------------------------------------
     */

    /**
     * end early if incompatible version of PHP used. Note that this will fail if 
     * PHP is downgraded while the plugin settings screen is running.
     * 
     * @since     1.0.0
     * @access    public
     * @return    boolean    (TRUE|FALSE) if true, PHP is OK, othewise, upgrade is needed
     */
    public function check_php () {

        // End early if incompatible version of PHP used
        if ( version_compare( PHP_VERSION, PLSE_SCHEMA_PHP_MIN_VERSION, '>=' ) ) {
            return true;
        }

        // add a blank, no-option plugin page to explain the error, in addition to the specific error message below.
        $err = PLSE_SCHEMA_EXTENDER_NAME . __( ' requires <strong>PHP ' . PLSE_SCHEMA_PHP_MIN_VERSION . ' (or above)</strong> (or higher) to function properly. Please upgrade PHP. The Plugin has been auto-deactivated.', PLSE_SCHEMA_EXTENDER_SLUG );
        $this->add_config_error_page( $err );

        $err = PLSE_SCHEMA_EXTENDER_NAME . __( ' Plugin was deactivated. Upgrade PHP to at least version ' . PLSE_SCHEMA_PHP_MIN_VERSION . ' in your web host administrative tools (usually CPanel) then re-activate this plugin.' );
        $this->add_config_error_message( $err );

        // remove the 'plugin activated' message
        unset( $_GET['activate'] );

        // deactivate the plugin
        add_action( 'admin_init', function () {
            deactivate_plugins( PLSE_SCHEMA_EXTENDER_BASE );
        } );

        return false;
    }

    /**
     * error message if Yoast plugin isn't installed, or version is not high enough to support Schema.
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean    if compatible Yoast is installed, return true, else false
     */
    public function check_yoast () {

        // Make sure our version of Yoast supports schemas
        if ( defined( 'WPSEO_VERSION' ) && version_compare( WPSEO_VERSION, PLSE_SCHEMA_YOAST_MIN_VERSION, '>=') ) {
            return true;
        }

        // add a blank, no-option plugin page to explain the error, in addition to the specific error message below.
        $err =  PLSE_SCHEMA_EXTENDER_NAME . __( ' requires <strong>Yoast version ' . PLSE_SCHEMA_YOAST_MIN_VERSION . ' (or above)</strong> (or higher) to function properly. Please upgrade Yoast. The Plugin has been auto-deactivated.', PLSE_SCHEMA_EXTENDER_SLUG );
        $this->add_config_error_page( $err );

        $err =  PLSE_SCHEMA_EXTENDER_NAME . __( ' Plugin was deactivated. Install and/or ypgrade Yoast SEO using the Plugins menu option in your WP Admin.' );
        $this->add_config_error_message( $err );

        // remove the 'plugin activated' message
        unset( $_GET['activate'] );

        // deactivate the plugin
        add_action( 'admin_init', function () {
            deactivate_plugins( PLSE_SCHEMA_EXTENDER_BASE );
        } );

        return false;
    }

    /**
     * Check if the Yoast plugin is active.
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean    if Yoast is active, return true, else false
     */
    public function check_yoast_active () {

        if( is_plugin_active( YOAST_PLUGIN ) ) {
            return true;
        }

        // add a blank, no-option plugin page to explain the error, in addition to the specific error message below.
        $err = __( 'Yoast is present, but needs to be activated' , PLSE_SCHEMA_EXTENDER_SLUG );
        $this->add_config_error_page( $err );

        $err = __( 'Go to the plugins menu, and activate Yoast. Then activate ' ) .  PLSE_SCHEMA_EXTENDER_NAME;
        $this->add_config_error_message( $err );

        // remove the 'plugin activated' message
        unset( $_GET['activate'] );

        // deactivate the plugin
        add_action( 'admin_init', function () {
            deactivate_plugins( PLSE_SCHEMA_EXTENDER_BASE );
        } );

        return false;

    }

    /**
     * Check if a Schema file has been defined. 
     * If Schema: 'game' or 'GAME', look for 'plse-schema-game.php'
     * Independent of the fields data defined in plse-options-data.php
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $schema_label    the slug or label for the schema ('game' or 'GAME')
     * @return   boolean    if a file exists with 'plse-schema-xxx.php' return true, else return false
     */
    public function check_if_schema_defined ( $schema_label ) {

        $dir = plugin_dir_path( dirname( __FILE__ ) ) . $this->includes_dir . '/' . $this->schema_dir . '/';

        $s = strtolower( $schema_label ) . '.php';

        $handle = opendir( $dir );

        while ( false !== ( $entry = readdir( $handle ) ) ) { 

            if ( $entry != '.' && $entry != '..' ) {

                if ( strpos( $entry, $s ) !== false ) {
                    closedir ( $handle );
                    return true;
                }

            }

        }

        closedir( $handle );
        return false;

    }

    /**
     * -----------------------------------------------------------------------
     * ERROR MESSAGES
     * Used when the plugin can't work (e.g. no Yoast)
     * -----------------------------------------------------------------------
     */

    /**
     * Add an error description next to a Schema field. This should be used 
     * for specific error messages relative to the field, not if the field is empty.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $msg  error message
     * @param    string    $status_class controls appearance of message
     * @return   string    wrap the error message in HTML for display
     */
    //public function add_status_to_field ( $msg = '', $status_class = 'plse-input-msg-caution' ) {
    //    return '<span class="plse-input-msg ' . $status_class .'">' . $msg . '</span><br>';
    //}

    /**
     * Add an error dialog at the top of the WP_Admin options explaining prblems.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function add_config_error_message ( $err ) {

            // create the top dialog error, passing in error message with 'use' operator
            add_action( 'admin_notices', function () use ( $err ) {

                $plugin_data = get_plugin_data( __FILE__ );
                ?>
                <div class="updated error">
                    <p><?php
                       
                        echo $err;
                        echo '<br><strong>' . $plugin_data['Name'] . __( 'has been deactivated' ) . '</strong>';
                        ?>
                    </p>
                </div>

                <?php
                // deactivate the plugin
                ///if ( isset( $_GET['activate'] ) ) {
                ///    unset( $_GET['activate'] );
                ///}
            } );

    }

    /**
     * If there is a fatal error, and the user is on the plugin options page, 
     * replace the options with a blank options page. This way, the user won't 
     * be confused by the sudden disappearance of the plugin options pages.
     * Possible triggers include:
     * - Yoast is downgraded below the minimum
     * - Yoast is deactivated
     * 
     * @since    1.0.0
     * @access   public
     */
    public function add_config_error_page ( $err = '' ) {

        // Put under WP_Admin->Tools, save result to decide when to enqueue scripts.
        add_menu_page( 
            PLSE_SCHEMA_EXTENDER_NAME, // page <title>
            $this->plugin_menu_title,  // admin menu text
            'manage_options',          // capability
            PLSE_SCHEMA_EXTENDER_SLUG, // menu slug
            function () {
                echo '<div style="background-color:#eee">' . "\n";
                echo '<div style="background-color:#fefefe;border-radius:6px;margin:8px;padding:8px;">';
                echo '<h2 style="font-size:24px;">Plyo Schema Extender requires the following software versions...</h2>';
                echo '<ul><li><strong>PHP:</strong> ' . PLSE_SCHEMA_PHP_MIN_VERSION . ', <strong>Currently Installed:</strong> ' . PHP_VERSION . '</li>';
                echo '<li><strong>Yoast:</strong> ' . PLSE_SCHEMA_YOAST_MIN_VERSION . ', <strong>Currently Installed:</strong> ' . WPSEO_VERSION . '</li></ul>';
                if ( ! empty ( $err ) ) echo '<li>' . $err . '</li>' . "\n";
                echo '<hr><p>This plugin has been deactivated. Upgrade, PHP and/or Yoast, then go to WP_Admin->Plugins, and re-activate.</p>';
                echo '</div>';
            }, // render callback
            'dashicons-networking' // schema-like hierarchy icon
        );

    }

} // end of class