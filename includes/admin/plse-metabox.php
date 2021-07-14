<?php

/**
 * Handles meta-boxes and custom fields posted to specific CPTs and categories.
 *
 * @since      1.0.0
 * @category   WordPress_Plugin
 * @package    PLSE_SCHEMA_Extender
 * @subpackage PlyoSchema_Extender/admin
 * @author     Pete Markeiwicz <pindiespace@gmail.com>
 * @license    GPL-2.0+
 * @link       https://plyojump.com
 */
class PLSE_Metabox {

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
     * name of JS variable holding relevant PHP variables passed from this class by PLSE_Init.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $options_js_name    name of the JS variable holding field names
     */
    private $options_js_name = 'plse_plugin_options';

    /**
     * name of JS variable holding relevant PHP variables passed from this class.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $options_js_name
     */
    private $meta_js_name = 'plse_plugin_custom_fields';

    /**
     * label for injected script for enqueueing in PLSE_init.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $options_js_name
     */
    private $meta_js_label = 'plse_metabox_options_js';

    /**
     * Schema directory
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
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct () {

        // utilities
        $this->util = PLSE_Util::getInstance();

        // shared field definitions, Schema data is loaded separately
        $this->options_data = PLSE_Options_Data::getInstance();

        add_action( 'admin_init', [ $this, 'setup_metaboxes' ] );
        add_action( 'admin_notices',   [ $this, 'metabox_show_errors' ], 12   );

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Base    $self__instance
     */
    public static function getInstance () {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Metabox();
        }
        return self::$__instance;
    }

    /**
     * Look in the Schema directory, extract current Schema list
     * pattern: 'plse-schema-xxxx.php' to 'xxxx'
     * 
     * @since    1.0.0
     * @access   private
     * @return   array    a list of the defined Schemas
     */
    public function get_available_schemas () {

        $schemas = array();

        $patterns = array( '/' . $this->schema_file_prefix . '/', '/.php/' );
        $replacements = array( '', '' );

        $dir = plugin_dir_path( dirname( __FILE__ ) ) . $this->schema_dir . '/';

        $handle = opendir( $dir );

        while ( false !== ( $entry = readdir( $handle ) ) ) { 

            // strip out non-schema substrings
            if ( $entry != "." && $entry != ".." ) {
                $schemas[] = preg_replace( $patterns, $replacements, $entry );
            }

        }

        closedir( $handle );

        return $schemas;

    }

    /**
     * Based on required schema, get data from the Schema class for the metabox.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array    schema field data needed to create metabox
     */
    public function load_schema_fields ( $schema_label ) {

        // upper-case the first letter, from 'game' to 'Game' to make the class name
        $class_name = 'PLSE_Schema_' . ucfirst( $schema_label );

        // load the appropriate class.
        if ( ! class_exists( $class_name ) ) {

            $class_path = plugin_dir_path( dirname( __FILE__ ) ) . $this->schema_dir . '/'. $this->schema_file_prefix . $schema_label . '.php';

            if ( file_exists( $class_path ) ) {
                require_once $class_path; // now the class exists
                return $class_name::$schema_fields; // read static public member variable
            }

        }

        return null;

    }

    /**
     * Get the field list from the Schema file
     */
    public function get_schema_fields ( $schema_label ) {

        $schema_data = $this->load_schema_fields( $schema_label );

        if ( $schema_data ) {
            return $schema_data['fields'];
        }

        return null;

    }

    /**
     * Check if a Schema was defined as 'active'.
     */
    public function check_if_schema_active ( $schema_label ) {
        return true;
    }

    /**
     * Check if a metabox should be drawn (Schema assigned by post type or category).
     */
    public function check_if_metabox_needed ( $schema_label ) {

        if ( $this->check_if_schema_active( $schema_label ) ) {

            // test cpt type
            // test categories

        }

        return true;
    }

    /**
     * Set up metaboxes
     * 'admin_init' hook
     */
    public function setup_metaboxes () {

        /* 
         * Enqueue our scripts and styles for the post area. 
         * Separate from, mutually exclusive loading from admin options pages.
         */
        add_action( 'admin_enqueue_scripts', function ( $hook ) {

            // load scripts common to PLSE_Settings and PLSE_Meta, get the label for where to position
            $plse_init = PLSE_Init::getInstance();
            $script_label = $plse_init->load_admin_scripts();

            // use PLSE_Options to inject variables into JS specifically for PLSE_Meta media library button clicks 
            $plse_init->load_js_passthrough_script( 
                $script_label,
                $this->meta_js_name,
                $this->options_data->get_options_fields()
            );

            // find the schema that are specified for this post type or category

            // TODO: Will have to look at what the global plugin finds

            // get the Schema definitions, and read the field list
            ////////////////////////////
            //$schema_metabox = $this->load_schema_fields( PLSE_SCHEMA_GAME );
            //echo "SSSSSSSSSCCCCCCCCCCCCCCCHHHHEMAAAA:";
            //print_r( $schema_metabox );
            ///////////////////////////
            // get a list of all available schema classes in the /schema directory
            $schema_list = $this->get_available_schemas();
            //////////////////print_r( $schema_list );

            // check if any of these schema are defined for our CPT or categories
            // if so, create a metabox. We're using the options data class, 
            // PLSE_Options_Data, not PLSE_Options
            foreach ( $schema_list as $schema_label ) {

                if ( $this->check_if_metabox_needed( $schema_label ) ) {

                    $this->metabox_register( 
                        $schema_label, 
                        $this->load_schema_fields( $schema_label ), 
                        'nothing...'
                    );

                }

            }

         } );

        //add_action( 'admin_notices',   [ $this, 'metabox_show_errors' ], 12   );
        //add_action( 'pre_post_update', [ $this, 'metabox_before_save' ], 1, 2);
        //add_action( 'save_post',       [ $this, 'metabox_save'     ],  2, 2  );
        //add_action( 'wp_insert_post',  [ $this, 'metabox_after_save' ], 12, 4 );

    }

    /**
     * --------------------------------------------------------------------------
     * CREATE METABOX AND ITS FIELDS
     * --------------------------------------------------------------------------
     */

    /**
     * Create a metabox for a Schema.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string   $schema_label     the name of the Schema
     * @param    array    $schema_fields    the data for the metabox and its fields
     * @paeram   string   $msg              additional information about why the box is being rendered
     */
    public function metabox_register ( $schema_label, $schema_data, $msg ) {

        global $post;

        //echo "POST IS:";
        //print_r( $post );

        //echo "SCHEMA DATA:";
        //print_r( $schema_data );

        // pass to rendering function
        $args = array(
            'schema_label' => $schema_label,
            'schema_fields' => $schema_data['fields'],
            'msg' => $msg
        );

        // actually add the metabox
        add_meta_box(
            $schema_data['slug'],
            $schema_data['title'], // visible name
            [ $this, 'render_metabox'], // render callback
            $post->post_type, // CPT, can be 'post'
            'normal',
            'high',
            $args // callback args, contains 'schema_fields'
        );

    }

    public function render_metabox ( $post, $args ) {

        echo "IIIIIIIIIIIIRENDER METABOXXXXXXXXXXXXX";

        // Note our passed parameters were merged into the default $args callback
        $schema_label = $args['args']['schema_label'];
        $fields = $args['args']['schema_fields'];
        $msg = $args['args']['msg'];
        $value = null;

        // create the metabox
        echo '<div class="plse-meta-containers">';
        if ( $msg ) echo '<p>' . __( 'Schema Added due to assignments:' ) . $msg . '</p>';
        echo '<ul class="plse-meta-list">';

        // add nonce


        // create the fields

        echo '</ul>';

        // loop through each Schema field
        foreach ( $fields as $field ) {

            // render the label as a list bullet
            echo '<li><label for="' . $field['slug'] . '">';
            _e( $field['title'], PLSE_SCHEMA_EXTENDER_SLUG );
             echo '</label>';

            // render the field
            // get the stored option value for metabox field directly from database
            if( $field[ 'wp_data' ] == 'option' ) {
                $value = get_option( $field['slug'] );
            }
            elseif ( $field[ 'wp_data' ] == 'post_meta' ) {
                // get the string associated with this field in this post (if no slug, get all the CPTs for this post)
                $value = get_post_meta( $post->ID, $field['slug'], true );
            }

            // use dynamic method to fire the rendering function for the field
            $method = 'render_' . PLSE_INPUT_TYPES[ $field['type'] ] . '_field';

            if ( method_exists( $this, $method ) ) { 
                $this->$method( $field, $value ); 
            }

            echo '</li>';

        }

        // close the box
        echo '</div>';

    }

    /**
     * --------------------------------------------------------------------------
     * RENDER METABOX FIELDS
     * --------------------------------------------------------------------------
     */
    
    /**
     * Render a hidden field (not the nonce).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $args     arguments needed to render the field
     * @param    string    $value    serialized or unserialized field value
     */
    public function render_hidden_field ( $args, $value ) {
        $value = ( $args['value_type'] == 'serialized' ) ? serialize( $value ) : $value;
        echo '<input type="hidden" id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" value="' . esc_attr( $value ) . '" />';
    }

    /**
     * Render a simple input field (type: text, url, email )
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args    arguments neeeded to render the field
     * @param    string   $value   serialized or unserialized field value
     * @param    string   $type    type="xxx" for the field
     */
    public function render_simple_field ( $args, $value, $type ) {
        $value = ( $args['value_type'] == 'serialized' ) ? serialize( $value ) : $value;
        echo '<input type="' . $type . '" id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" size="40" value="' . esc_attr( $value ) . '" />';
    }

    /**
     * Render a text input field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $args arguments needed to render the field
     * @param    string    $value serialized or unserialized field value
     */
    public function render_text_field ( $args, $value ) {
        // TODO:
        return $this->render_simple_field( $args, $value, 'text' );
    }

    public function render_postal_field ( $args, $value ) {
        // TODO:
        return $this->render_simple_field( $args, $value, 'text' );
    }

    public function render_phone_field ( $args, $value ) {
        //TODO:
        return $this->render_simple_field( $args, $value, 'tel' );
    }

    public function render_email_field ( $args, $value ) {
        //TODO:
        return $this->render_simple_field( $args, $value, 'email' );

    }

    public function render_url_field ( $args, $value ) {
        //TODO:
        $this->render_simple_field( $args, $value, 'url' );
    }

    public function render_textarea_field ( $args, $value ) {
        $value = ( $args['value_type'] == 'serialized' ) ? serialize( $value ) : $value;
        echo '<textarea id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" rows="5" cols="60">' . esc_attr( $value ) . '</textarea>';

    }

    public function render_date_field ( $args, $value ) {
        //TODO:
        echo "DATE FIELD...............";
    }

    public function render_time_field ( $args, $value ) {
        //TODO:
        echo "TIME FIELD...............";
    }

    /**
     * Render a field with date (calendar) and time settings.
     * {@link https://codepen.io/herteleo/pen/LraqoZ}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args   default arguments for field
     * @param    mixed    $value  value of field (may have multiple features)
     */
    public function render_datetime_field ( $args, $value ) {
        $value = ( $args['value_type'] == 'serialized' ) ? serialize( $value ) : $value;
        // TODO:
        echo '<div class="plse-datetimepicker">';
        echo '<input type="date" id="date" value="2018-07-03"><span></span>';
        echo '<input type="time" id="time" value="08:00">';
        echo '</div>';

        echo '<div class="plse-datetimepicker-info">
        <strong>Compatibility check:</strong>
        Date "<span id="date-output"></span>",
        Time "<span id="time-output"></span>"
        </div>';
    }

    public function render_daterange_field ( $args, $value ) {
        // TODO:
        echo "DATERANGE............";
    }

    public function render_checkbox_field ( $args, $value ) {
        //TODO:
        echo "CHECKBOX FIELD...............";
    }

    public function render_multi_cpt_field ( $args, $value ) {
        //TODO:
        echo "MULTI CPT FIELD...............";
    }

    public function render_multi_cat_field ( $args, $value ) {
        //TODO:
        echo "MULTI CAT FIELD...............";
    }

    public function render_image_field ( $args, $value ) {

        $plse_init = PLSE_Init::getInstance();
        $slug = $args['slug'];

        if ( $value ) {
            echo '<img id="' . sanitize_key( $slug ) . '-img-id" src="' . esc_url( $value ) . '" width="128" height="128">';
        } else {
            echo '<img id="'. sanitize_key( $slug ) . '-img-id" src="' . $plse_init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }

        // media library button (ajax)
        echo '<input type="text" name="' . sanitize_key( $slug ) . '" id="' . sanitize_key( $slug ) . '" value="' . $value . '">';
        echo '<input type="button" class="button plse-media-button" data-media="'. $slug . '" value="Upload Image" />';

    }

    /**
     * --------------------------------------------------------------------------
     * SAVING METABOX CUSTOM FIELDS
     * --------------------------------------------------------------------------
     */

    public function metabox_before_save () {

    }

    public function metabox_save () {

    }

    public function metabox_after_save () {

    }


    public function metabox_show_errors () {

    }

} // end of class