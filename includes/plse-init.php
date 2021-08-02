<?php

/**
 * Initializes plugin features, creates default plugin menu and options page.
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
     * Consolidate admin JS references
     * 
     * @since    1.0.0
     * @access   private
     * @var      string     $plse_admin_js
     */
    private $plse_admin_js = 'admin/js/plyo-schema-extender-admin.js';

    /**
     * Consolidate admin CSS references
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $plse_admin_css
     */
    private $plse_admin_css = 'admin/css/plyo-schema-extender-admin.css';

    /**
     * Schema subdirectory in the plugin.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string     $schema_dir
     */
    private $schema_dir = 'schema';

    /**
     * Prefix for schema files.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $schema_file_prefix
     */
    private $schema_file_prefix = 'plse-schema-';

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
     * @return   PLSE_Base    $self__instance
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
            $mofile = dirname( __FILE__ ) . '/languages/' .PLSE_SCHEMA_EXTENDER_SLUG . '-' . $locale . '.mo';
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

            foreach( $field_group as $field ) {

                // note that property name is in single quotes
                switch ( $field['type'] ) {

                    // TODO: nothing added bu field property names at present
                    default:
                        $js .= "\n'" . $field['slug'] . "': {},";
                        break;

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
                $util = PLSE_Util::getInstance();
                $my_cpt_names = $util->get_all_cpt_names();

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
        $dir = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/' . $this->schema_dir . '/';

        $handle = opendir( $dir );

        if ( $handle ) {

            while ( false !== ( $entry = readdir( $handle ) ) ) { 

                // strip out non-schema substrings
                if ( $entry != "." && $entry != ".." ) {
                    //$schemas[] = strtoupper( preg_replace( $patterns, $replacements, $entry ) );
                    $schemas[] = $this->label_to_slug( preg_replace( $patterns, $replacements, $entry ) );
                }

            }

            closedir( $handle );

        }

        return $schemas;

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

    public function get_vimeo_thumb( $url, $type = 'small' ) {

        $url = esc_url( $url );
        $video_id = '';
        $thumb_link = '';
    
        if (preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $regs)) {
            $video_id = $regs[3];
        }

        if ( $id ) {
            $vimeo = unserialize( file_get_contents( 'http://vimeo.com/api/v2/video/' . $id . '.php' ) );
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
     * -----------------------------------------------------------------------
     * FIELD VALIDATIONS
     * -----------------------------------------------------------------------
     */

    /**
     * Field is required
     */
    public function is_required ( $in ) {
        if ( empty( $in ) && $in['required'] == 'required') {
           return true;
        }
        return false;
    }

    /**
     * Phone number validation.
     */
    public function is_phone ( $in ) {
        // sanitize
        // check for phone format
        $s = preg_replace( '/[\s\#0-9_\-\+\/\(\)\.]/', '', $in );
        if ( strlen( $s ) ) {
           return false; // empty
        }
        return true;
    }

    public function is_postal ( $in ) {
        $s = preg_replace( '/[\s\-A-Za-z0-9]/', '', $out );
        if ( strlen( $s ) ) {
            return false;
        }
        return true;
    }

    public function is_url ( $in ) {
        return filter_var( $in, FILTER_VALIDATE_URL );
    }

    //public function is_active_url ( $in ) {
    //    $url = parse_url($in);
    //    if ( ! isset( $in["host"] ) ) return false;
    //    return ! ( gethostbyname( $in["host"] ) == $url["host"] );
    //}

    /**
     * Check if the URL is active, or has a 301, 302 redirect
     * {@link https://www.secondversion.com/blog/php-check-if-a-url-is-valid-exists/}
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in the URL string to test
     * @return   boolean   if valid, return true, else false
     */
    function is_active_url( $in ) {

        // break the URL into its components
        if ( ! ( $in = @parse_url( $in ) ) ) return false;

        // Check components for validity
        $in['port']  = ( ! isset($in['port'])) ? 80 : (int)$in['port'];
        $in['path']  = ( ! empty($in['path'])) ? $in['path'] : '/';
        $in['path'] .= ( isset($in['query'])) ? "?$in[query]" : '';

        // See if URL responds to a HTTP request (assume PHP version > 5)
        if ( isset( $in['host'] ) AND $in['host'] != @gethostbyname( $in['host'] ) ) {
            $headers = @implode( '', @get_headers( "$in[scheme]://$in[host]:$in[port]$in[path]" ) );
            return (bool)preg_match( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
        }

        return false;
    }


    public function is_date ( $in ) {
        // TODO CHECK FORMATINCOMING
        //checkdate ( $month, $day, $year )
        return checkdate( $in['month'], $in['day'], $in['year'] );
    }

    public function is_time ( $in ) {
        return strtotime( $in );
    }

    /**
     * -----------------------------------------------------------------------
     * UTILITIES
     * -----------------------------------------------------------------------
     */

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

        $dir = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/' . $this->schema_dir . '/';

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
     * Get the current post type
     * TODO: currently not used
     * @since    1.0.0
     * @access   public
     * @returns  string     post_type as a text string
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

} // end of class