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
     * Store reference to shared PLSE_Util class.
     *
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Util    $util    the PLSE_Util class.
     */
    private $util = null;

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

        $this->util = PLSE_Util::getInstance();

        // internationalization
        $this->l10ni18n();

        //add_action('admin_menu', [ $this, 'setup_options_menu'] );

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

        //echo "\nSSSSSSSSSSSSSSSSSSCRIPT LABEL:" . $script_label;
        //echo "\nVVVVVVVVVVVVVVVVVVVVVVVAR NAME:" . $script_var_name;
        //echo "\nJJJJJJJJJJJJJJJJJS:" . $js;

        if ( $js ) {
            wp_add_inline_script( $script_label, $js, 'before' );
        }

    }

    /**
     * -----------------------------------------------------------------------
     * DEFAULT VALUES
     * -----------------------------------------------------------------------
     */

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

        $dir = plugin_dir_path( dirname( __FILE__ ) ) . $this->schema_dir . '/';

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