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
     * label for injected script for enqueueing via PLSE_init.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $options_js_name
     */
    private $meta_js_label = 'plse_metabox_options_js';

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
     * Slug for setting a transient message.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $schema_transient
     */
    private $schema_transient = 'plse-schema-metabox-transient';

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct () {

        // utilities
        $this->init = PLSE_Init::getInstance();

        // shared field definitions, Schema data is loaded separately
        $this->options_data = PLSE_Options_Data::getInstance();

        // initialze metaboxes assigned by plugin options
        add_action( 'admin_init', [ $this, 'setup' ] );

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
        $dir = plugin_dir_path( dirname( __FILE__ ) ) . $this->schema_dir . '/';

        $handle = opendir( $dir );

        while ( false !== ( $entry = readdir( $handle ) ) ) { 

            // strip out non-schema substrings
            if ( $entry != "." && $entry != ".." ) {
                //$schemas[] = strtoupper( preg_replace( $patterns, $replacements, $entry ) );
                $schemas[] = $this->init->slug_to_label( preg_replace( $patterns, $replacements, $entry ) );
            }

        }

        closedir( $handle );

        return $schemas;

    }


    /**
     * Based on required Schema, get data from the Schema class into the metabox.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array|null    schema field data needed to create metabox, or null
     */
    public function load_schema_fields ( $schema_label ) {

        // Create the class name, upper-case the first letter, from 'game' to 'Game'
        $class_name = 'PLSE_Schema_' . $this->init->label_to_class_slug( $schema_label );

        // load the appropriate class.
        if ( ! class_exists( $class_name ) ) {

            $class_path = plugin_dir_path( dirname( __FILE__ ) ) . $this->schema_dir . '/'. $this->schema_file_prefix . $schema_label . '.php';

            if ( file_exists( $class_path ) ) {
                require $class_path; // now the class exists
                return $class_name::$schema_fields; // read static public member variable
            } else {
                return null; // CLASS FILE NOT PRESENT
            }

        } else {
            // class already exists
            return $class_name::$schema_fields;
        }

    }

    /**
     * Check if a metabox should be drawn. Schema are assigned either to a Custom Post Type,
     * or through a category assigned to the post, both in plugin options.
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean   if a metabox needed for Schema, return true, else false
     */
    public function check_if_metabox_needed ( $schema_label ) {

        // check if the Schema is active, being used
        if ( $this->options_data->check_if_schema_active( $schema_label ) ) {

            // test Custom Post Types types associated with the Schema
            if ( $this->options_data->check_if_schema_assigned_cpt( $schema_label ) ) {
                return true;
            }

            // test categories associated with the Schema
            if ( $this->options_data->check_if_schema_assigned_cat( $schema_label ) ) {
                return true;
            }

        }

        return false;
    }


    /* 
     * Enqueue our scripts and styles for the post area. 
     * Separate from, mutually exclusive loading from admin options pages.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup () {

        add_action( 'admin_enqueue_scripts', [ $this, 'setup_scripts' ] );

        // NOTE: 'save_post' must be BEFORE 'add_meta_boxes'
        add_action( 'pre_post_update', [ $this, 'metabox_before_save' ], 1, 2);
        add_action( 'save_post',       [ $this, 'metabox_save'     ],  2, 2  );
        add_action( 'wp_insert_post',  [ $this, 'metabox_after_save' ], 12, 4 );
        add_action( 'add_meta_boxes', [ $this, 'setup_metaboxes' ] );

    }

    /**
     * Enqueue scripts and styles related to metaboxes (calls PLSE_Init).
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_scripts () {

        // load scripts common to PLSE_Settings and PLSE_Meta, get the label for where to position
        $plse_init = PLSE_Init::getInstance();
        $script_label = $plse_init->load_admin_scripts();

        // use PLSE_Options to inject variables into JS specifically for PLSE_Meta media library button clicks 
        $plse_init->load_js_passthrough_script( 
            $script_label,
            $this->meta_js_name,
            $this->options_data->get_options_fields()
        );

        // load a local JS validation script
        // TODO:
        // TODO: determine if any fields need dynamic validation while typing
        ///wp_register_script('jquery-validation-plugin', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js', array('jquery' ) );
        ////wp_enqueue_script('jquery-validation-plugin');

    }

    /**
     * Initialize metabox display
     * - enqueue scripts
     * - set up metaboxes, determining which should be shown
     * - use the 'admin_init' hook
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_metaboxes () {

        // get a list of all defined schema classes in the /schema directory
        $schema_list = $this->get_available_schemas();

        // determine which Schema metaboxes should be loaded
        foreach ( $schema_list as $schema_label ) {

            // Check if Schema is active, and if we have a CPT or category requiring a Schema
            if ( $this->check_if_metabox_needed( $schema_label ) ) {

                /*
                 * NOTE: if there is an error here ( for example, the 
                 * schema/plse-schema-xxx.php file is not available), we can't display ERROR
                 */
                $schema_fields = $this->load_schema_fields( $schema_label );
                if ( ! $this->load_schema_fields( $schema_label ) ) {

                    $this->metabox_store_transient( 'Could not read:' . $schema_label );

                } else {

                    $this->metabox_register( 
                        $schema_label, 
                        $schema_fields, //$this->load_schema_fields( $schema_label ), 
                        $schema_label // additional argument passed, 'GAME', 'EVENT'
                    );

                }

            }

        }

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
     * @param    string   $msg              additional information about why the box is being rendered
     */
    public function metabox_register ( $schema_label, $schema_data, $msg ) {

        // get the current post
        $post = $this->init->get_post();

        /*
         * build an argument array containing the metabox field descriptions 
         * from the Schema file, and any messages from check_if_metabox_needed()
         */
        $schema_args = array(
            'schema_label' => $schema_label,
            'schema_fields' => $schema_data['fields'],
            'nonce' => $schema_data['nonce'],
            'slug' => $schema_data['slug'],
            'message' => $schema_data['message'],
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
            $schema_args // callback args, contains 'schema_fields'
        );

    }

    /**
     * Render an individual metabox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    WP_POST    $post    the current post
     * @param    array      args     field data needed to render metabox
     */
    public function render_metabox ( $post, $args ) {

        /*
         * To render the metabox, we had to pass our field descriptions in metabox_register(),
         * this becomes $args in this function.
         */
        $meta_field_args = $args['args'];
        $schema_label = $meta_field_args['schema_label'];
        $fields = $meta_field_args['schema_fields'];
        $msg = $meta_field_args['msg'];
        $message = $meta_field_args['message'];
        $value = null;

        // create the metabox
        echo '<div class="plse-meta-container">';

        // look for errors during the load (not from previous save)
        $e = $this->metabox_read_transient();
        if ( ! empty( $e ) ) {
            echo '<div class="plse-input-err-msg"><p>Error During Load</p><span>' . $e . '</span></div>';
        }

        // descriptive metabox message
        echo '<p class="plse-meta-message">' . $msg . ' Schema.' . $meta_field_args['message'] . '</p>';
        echo '<ul class="plse-meta-list">';

        // add nonce
        $nonce = $meta_field_args['nonce'];
        $context = $meta_field_args['slug'];

        wp_nonce_field( $context, $nonce );

        // loop through each Schema field
        foreach ( $fields as $field ) {

            // render the label as a list bullet
            echo '<li><label for="' . $field['slug'] . '">';
            _e( $field['label'], PLSE_SCHEMA_EXTENDER_SLUG );
             echo '</label>';

            // get the stored option value for metabox field directly from database
            if( $field[ 'wp_data' ] == 'option' ) {
                $value = get_option( $field['slug'] );
            }
            elseif ( $field[ 'wp_data' ] == 'post_meta' ) {
                // get the string associated with this field in this post (if no slug, get all the CPTs for this post)
                if ( $field['select_multiple'] ) {
                    $value = get_post_meta( $post->ID, $field['slug'] ); // multi-select control, returns array
                } else {
                    $value = get_post_meta( $post->ID, $field['slug'], true ); // single = true, returns meta value
                }

            }

            // use dynamic method to fire the rendering function for the field
            $method = 'render_' . PLSE_INPUT_TYPES[ $field['type'] ] . '_field';

            if ( method_exists( $this, $method ) ) { 
                $this->$method( $field, $value, $field['title'] ); 
            }

            echo '</li>';

        }

        echo '</ul>';

        // close the box
        echo '</div>';

    }

    /**
     * --------------------------------------------------------------------------
     * RENDER METABOX FIELDS
     * 
     * Strategy: Render, and validate. Check if there is an error in input.
     * NOTE: unlike PLSE_Options, <label> is rendered in the calling function.
     * --------------------------------------------------------------------------
     */

    /**
     * Add an error description next to the Schema field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $msg  error message
     * @return   string    wrap the error message in HTML for display
     */
    public function add_error_to_field ( $msg = '' ) {
        return '<span class="plse-input-err-msg">' . $msg . '</span>';
    }
    
    /**
     * Render a hidden field (not the nonce).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $args     arguments needed to render the field
     * @param    string    $value    serialized or unserialized field value
     */
    public function render_hidden_field ( $args, $value ) {
        echo '<input type="hidden" id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" value="' . esc_attr( $value ) . '" />';
    }

    /**
     * Render a simple input field (type: text, url, email )
     * NOT directly called - Used by other fields
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args    arguments neeeded to render the field
     * @param    string   $value   serialized or unserialized field value
     * @param    string   $err     error message, formated in HTML error style
     */
    public function render_simple_field ( $args, $value, $err = '' ) {
        $type = $this->init->label_to_slug( $args['type'] );
        if ( $args['class']) $class = ' class="' .  $args['class'] . '"'; else $class = '';
        echo '<input title="' . $args['title'] . '" type="' . $type . '"' . $class . ' id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" size="40" value="' . esc_attr( $value ) . '" />';
        if ( $err )echo $err;
    }

    /**
     * Render a text input field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $args arguments needed to render the field
     * @param    string    $value    field value
     */
    public function render_text_field ( $args, $value ) {
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __( 'this field is required....') );
        }
        return $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Render postal code field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value
     */
    public function render_postal_field ( $args, $value ) {
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        if ( ! $this->init->is_postal( $value ) ) {
            $err = $this->add_error_to_field( __( 'this is not a valid postal code' ) );
        }
        return $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Render a telephone field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value the field value
     */
    public function render_tel_field ( $args, $value ) {
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        if ( ! $this->init->is_phone( $value ) ) {
            $err = $this->add_error_to_field( __( 'this is not a valid phone number' ) );
        }
        return $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Render an email field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value
     */
    public function render_email_field ( $args, $value ) {
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        if ( ! $this->init->is_email( $value ) ) {
            $err = $this->add_error_to_field( __( 'this is not a valid email' ) );
        }
        return $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Render a URL field (http or https).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value
     */
    public function render_url_field ( $args, $value ) {
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __('this field is required....') );
        } else if ( ! $this->init->is_url( $value ) ) {
            $err = $this->add_error_to_field( __( 'invalid address (URL)' ) );
        } 
        // TODO: TOO SLOW - MAKE INTO A USER BUTTON
        // TODO:
        //else if ( ! $this->init->is_active_url( $value ) ) {
        //    $err = $this->add_error_to_field( __('the address does not go to a valid web page' ) );
        //}
        $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Render a textara field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value
     */
    public function render_textarea_field ( $args, $value ) {
        $err = '';
        if ( $this->init->is_required( $args ) ) {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        echo '<textarea title="' . $args['title'] . '" id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" rows="5" cols="60">' . esc_attr( $value ) . '</textarea>';
        if ( ! empty( $err ) ) echo $err;
    }

    /**
     * Render a Date field, value always DD:MM:YEAR.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value, with correct date format YYYY-MM-DD
     */
    public function render_date_field ( $args, $value ) {
        $err = '';
        if ( $this->init->is_required( $args ) ) {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        echo '<input title="' . $args['title'] . '" id="' . sanitize_key( $args['slug'] ) . '" type="date" name="' . sanitize_key( $args['slug'] ) . '" value="' . esc_attr( $value ) . '">';
        if ( ! empty( $err ) ) echo $err;
    }

    /**
     * Render a Time field, value always HH:MM:AM/PM.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args     field parameters, select
     * @param    string   $value    the field value, formatted HH:MM:AM/PM
     */
    public function render_time_field ( $args, $value ) {
        $err = '';
        if ( $this->init->is_required( $args ) ) {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        echo '<input title="' . $args['title'] . '" id="' . sanitize_key( $args['slug'] ) . '" type="time" name="' . sanitize_key( $args['slug'] ) . '" value="' . esc_attr( $value ) . '">';
        if ( ! empty( $err ) ) echo $err;
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
        echo '<input type="date" id="date" name="" value="2018-07-03"><span></span>';
        echo '<input type="time" id="time" name="" value="08:00">';
        echo '</div>';
    }

    public function render_daterange_field ( $args, $value ) {
        // TODO: PROBABLY DON'T NEED THIS
    }

    /**
     * Render a checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value 'on' or not on
     */
    public function render_checkbox_field ( $args, $value ) {
        //TODO:
        echo "CHECKBOX FIELD...............";
        echo '<input title="' . $args['title'] . '" style="display:block;" type="checkbox" id="' . $slug . '" name="' . $slug . '"';
        if ( $option == $this->init->get_checkbox_on() ) echo ' CHECKED';
        echo ' />';	
    }

    /**
     * Render a pulldown menu with only one option selectable.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value
     */
    public function render_select_single_field ( $args, $value ) {
        $option_list = $args['option_list'];
        $slug = $args['slug'];
        $dropdown = '<div class="plse-option-select"><select title="' . $args['title'] . ' id="' . $slug . '" name="' . $slug . '" class="cpt-dropdown" >' . "\n";
        foreach ( $option_list as $option_label => $option ) {

            $dropdown .= '<option value="' . $option . '" ';
            if ( $value == $option ) {
                $dropdown .= 'selected';
            }

            $dropdown .= '>' . $option_label . '</option>' . "\n";
        }
        $dropdown .= '</select>' . "\n";
        $dropdown .= '<p class="plse-option-select-description">' . __( 'Select one option from the list' ) . '</p>';

        echo $dropdown;
    }

    /**
     * Render a scrolling list of options allowing multiple select.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    array    $value    an array with all options selected
     */
    public function render_select_multiple_field ( $args, $value ) {
        $option_list = $args['option_list'];
        if ( ! $option_list ) return; // options weren't added
        $slug = $args['slug'];
        // if multi-select, value is an array with a sub-array of values
        if ( is_array( $value) ) $value = $value[0];
        $dropdown = '<div class="plse-option-select"><select multiple="multiple" title="' . $args['title'] . ' id="' . $slug . '" name="' . $slug . '[]" class="cpt-dropdown" >' . "\n";
        foreach ( $option_list as $option_label => $option ) {
            $dropdown .= '<option value="' . $option . '" ';
            if ( is_array( $value ) ) {
               foreach ( $value as $v) {
                   if ( $option == $v) {
                       $dropdown .= 'selected';
                   }
               }
            } else if ( $option == $value ) {
                $dropdown .= 'selected';
            }

            $dropdown .= '>' . $option_label . '</option>' . "\n";
        }
        $dropdown .= '</select>' . "\n";
        $dropdown .= '<p class="plse-option-select-description">' . __( '(CTL-Click to for select and deselect)') . '</p>';

        echo $dropdown;
    }

    /**
     * Render Custom Post Type list.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    array    $value    an array with all options selected
     */
    public function render_multi_cpt_field ( $args, $value ) {
        $this->render_select_multiple_field( $args, $value );
    }

    /**
     * Render Category list.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    array    $value    an array with all options selected
     */
    public function render_multi_cat_field ( $args, $value ) {
        $this->render_select_multiple_field( $args, $value );
    }

    /**
     * Render an image with its associated URL field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string    $value    the URL of the image
     */
    public function render_image_field ( $args, $value ) {

        $plse_init = PLSE_Init::getInstance();
        $slug = $args['slug'];
        $title = $args['title'];

        echo '<div class="plse-meta-image-col">';

        if ( $value ) {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="' . sanitize_key( $slug ) . '-img-id" src="' . esc_url( $value ) . '" width="128" height="128">';
        } else {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="'. sanitize_key( $slug ) . '-img-id" src="' . $plse_init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }

        echo '</div><div class="plse-meta-upload-col">';

        echo '<div>' . __( 'Image URL in WordPress' ) . '</div>';
        echo '<div>';

        // media library button (ajax)
        echo '<input type="text" name="' . sanitize_key( $slug ) . '" id="' . sanitize_key( $slug ) . '" value="' . $value . '">';
        echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug . '" value="Upload Image" />';

        echo '</div></div>';

    }

    /**
     * Video URL also captures a thumbnail
     */
    public function render_video_field ( $args, $value ) {
        
        $plse_init = PLSE_Init::getInstance();
        $slug = $args['slug'];
        $title = $args['title'];

        // create the thumbnail URL
        //https://ytimg.googleusercontent.com/vi/<insert-youtube-video-id-here>/default.jpg
        echo '<div>';
        // add a special class for JS to the URL field for dynamic video embed
        $args['class'] = 'pulse-embedded-video-url';
        $this->render_url_field( $args, $value );

        // TODO: MAKE THIS A TABLE

        echo '</div><div style="display:inline-block;" class="">';

        if ( $value ) {

            $thumbnail_url = $this->init->get_video_thumb( $value );

            echo '<a href="' . $value . '"><img title="' . $title . '" class="plse-upload-img-box" id="' . sanitize_key( $slug ) . '-img-id" src="' . esc_url( $thumbnail_url ) . '" width="128" height="128"></a>';
        } else {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="'. sanitize_key( $slug ) . '-img-id" src="' . $plse_init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }

        echo '<div style="float:right;" class="plse-auto-resizable-iframe">';
        echo '<div class="plse-embed-video"></div>'; //////////////////
        echo '</div>';

        echo '<div>';

        echo '</div></div>';

    }

    /**
     * --------------------------------------------------------------------------
     * SAVING METABOX CUSTOM FIELDS
     * --------------------------------------------------------------------------
     */

    /**
     * Check entered data before saving.
     */
    public function metabox_before_save ( $post_id, $post_data ) {

    }

    /**
     * Save the metabox data.
     * 
     * @since    1.0.0
     * @access   public
     * @param    number   $post_id    ID of current post
     * @param    WP_Post  $post    the current post
     */
    public function metabox_save ( $post_id, $post ) {

        // don't update on autosave
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

        // don't update on Ajax
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return $post_id;

        // don't update on cron
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) return $post_id;

        // check user permissions to post
        if ( ! current_user_can( 'edit_posts' ) ) return $post_id;

        // check permissions for post type
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        }

        // get the Schemas that need to be saved for this post
        $schema_list = $this->get_available_schemas();

        // determine which Schema metaboxes should be loaded
        foreach ( $schema_list as $schema_label ) {

            // Check if Schema is active, and if we have a CPT or category requiring a Schema
            if ( $this->check_if_metabox_needed( $schema_label ) ) {

                // load the Schema fields from /schema/plyo-schema-XXX.php
                $schema = $this->load_schema_fields( $schema_label );

                // verify nonce
                $nonce = $schema['nonce'];
                $context = $schema['slug'];
    
                // if there's an invalid nonce, loop to the next metabox
                if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( $_POST[ $nonce ], $context ) ) {
                    continue;
                }

                $fields = $schema['fields'];

                 // save individual field values
                foreach ( $fields as $key => $field ) {

                    $slug = $field['slug'];

                    if( ! isset( $_POST[ $slug ] ) ) {

                        delete_post_meta( $post_id, $slug );

                    } else {

                        $value = $_POST[ $slug ];

                        // switch, converting our uppercase label to a lowercase slug
                        switch ( $this->init->label_to_slug( $field['type'] ) ) {

                            case PLSE_INPUT_TYPES['EMAIL']:
                                $value = sanitize_email( trim( $value ) );
                                break;

                            case PLSE_INPUT_TYPES['URL']:
                                $value = esc_url_raw( trim( $value ), [ 'http', 'https' ] );
                                break;

                            case PLSE_INPUT_TYPES['TEXTAREA']:
                                $value = esc_textarea( trim( $value ) );
                                break;

                            case PLSE_INPUT_TYPES['DATE']:
                                // format: '2015-11-26'
                                break;

                            case PLSE_INPUT_TYPES['SELECT_MULTIPLE']:
                                $value = $value; ////////////////////////////////////
                                break;

                            case PLSE_INPUT_TYPES['TIME']:
                                // format: 
                                break;

                            default: 
                                $value = sanitize_text_field( trim( $value ) );
                                break;

                        }

                        // TODO: Sanitize here (though should have been done in jquery)
                        //https://newbedev.com/validating-custom-meta-box-values-required-fields
                        // check for validation, flag
                        //////set_user_setting('plse-user-setting-error', 'ERROR'); //////////////////

                        // update or delete data
                        if ( empty( $value ) ) {

                            delete_post_meta( $post_id, $slug );

                        } else {

                            update_post_meta( $post_id, $slug, $value );

                        }

                    }

                }

            }

        }

    }

    /**
     * Do something after metabox data is saved.
     */
    public function metabox_after_save () {

    }

    /**
     * Set a transient that expires after 5 seconds. Add user ID to 
     * prevent collisions if more than one admin is logged in.
     * 
     * Used to record errors during load, NOT after the schema is updated. Errors 
     * in individual fields appear next to each field after an update.
     * 
     * @since     1.0.0
     * @access    public
     * @param     string     $err_message    the error message
     * @param     number     $duration    the duration of the transient, default to 5 seconds
     */
    public function metabox_store_transient ( $err_msg, $duration = 5 ) {
        set_transient( $this->schema_transient . get_current_user_id(), $err_msg, $duration );
    }

    /**
     * Read any storied transient messages.
     * 
     * Used to record errors during load, NOT after the schema is updated. Errors 
     * in individual fields appear next to each field after an update.
     * 
     * @since     1.0.0
     * @access    public
     */
    public function metabox_read_transient () {
        return get_transient( $this->schema_transient );
    }

    /**
     * Display errors.
     * 
     * TODO:
     * TODO:
     * THIS IS NOT WORKING. IF you try a do_action('admin_init'), the error renders 
     * BEFORE WP begins creating the pages
     */
    public function metabox_show_errors ( $notice_type, $err_msg ) {

        $stored_err_msg = get_transient( $this->schema_transient );
        if( $stored_error_msg ) {
            $err_msg = $stored_err_msg;
        }

        add_action(
            'admin_notices',
            function () use ( $err_msg, $message ) {
                printf(
                    '<div class="%1$s"><p>%2$s</p></div>',
                    esc_attr( $notice_type ),
                    esc_html( $err_msg )
                );
            }
        );
        do_action( 'admin_notices' );

    }

} // end of class