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
     * Slug for setting a transient message.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $schema_transient
     */
    private $schema_transient = 'plse-schema-metabox-transient';

    /**
     * Maximum for meta repeater fields.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $repeater_max    max number of repeater fields
     */
    private $repeater_max = 1000;

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct () {

        // utilities
        $this->init = PLSE_Init::getInstance();

        // shared field definitions, Schema data is loaded separately
        $this->options_data = PLSE_Options_Data::getInstance();

        // datalists, e.g. country name lists
        $this->datalists = PLSE_Datalists::getInstance();

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


    /* 
     * Enqueue our scripts and styles for the post area. 
     * Separate from, mutually exclusive loading from admin options pages.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup () {

        add_action( 'admin_enqueue_scripts', [ $this, 'setup_scripts' ] );

        add_action( 'pre_post_update', [ $this, 'metabox_before_save' ], 1, 2 );
        add_action( 'save_post',       [ $this, 'metabox_save' ],  2, 2 );
        add_action( 'wp_insert_post',  [ $this, 'metabox_after_save' ], 12, 4 );

        // NOTE: 'save_post' must be BEFORE 'add_meta_boxes'
        add_action( 'add_meta_boxes',  [ $this, 'setup_metaboxes' ] );

    }

    /**
     * Enqueue scripts and styles related to metaboxes (calls PLSE_Init).
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_scripts () {

        $plse_init = PLSE_Init::getInstance();

        // load scripts common to PLSE_Settings and PLSE_Meta, get the label for where to position
        $script_label = $plse_init->load_admin_scripts();

        //<script src="dist/html-duration-picker.min.js"></script>

        wp_enqueue_script( PLSE_SCHEMA_EXTENDER_SLUG, $url . $this->plse_admin_js, array('jquery'), null, true );

        // use PLSE_Options to inject variables into JS specifically for PLSE_Meta media library button clicks 
        $plse_init->load_js_passthrough_script( 
            $script_label,
            $this->meta_js_name,
            $this->options_data->get_options_fields()
        );

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

            $class_path = plugin_dir_path( dirname( __FILE__ ) ) . $this->init->get_schema_dirname() . '/'. $this->init->get_schema_file_prefix() . $schema_label . '.php';

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
        $schema_list = $this->init->get_available_schemas();

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

                    $this->metabox_store_transient( __( 'Could not read:' ) . $schema_label );

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
            $schema_data['title'] . '<span class="dashicons dashicons-networking" style="width:50px;"></span>', // visible name
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
        echo '<p class="plse-meta-message">' . ucfirst( $msg ) . ' Schema. ' . $meta_field_args['message'] . '</p>';
        echo '<ul class="plse-meta-list">';

        // add nonce
        $nonce = $meta_field_args['nonce'];
        $context = $meta_field_args['slug'];

        wp_nonce_field( $context, $nonce );

        // loop through each Schema field
        foreach ( $fields as $field ) {

            // render the label as a list bullet
            echo '<li><label for="' . $field['slug'] . '" class="plse-option-description"><span>';
            echo __( $field['label'], PLSE_SCHEMA_EXTENDER_SLUG ) . '</span></label>';

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

            // Flag if a required field, and if the field is not filled out. (add message at top-right of <li>)
            if ( $field['required'] ) {
                $missing = ''; $req_msg = 'required';
                if ( empty( $value ) ) {
                    $missing = 'plse-required-missing-field';
                    $req_msg .= ', not present';
                }
                echo '<span class="plse-required-field ' . $missing . '">(' . $req_msg . ')</span>';
            }

            // use dynamic method to fire the rendering function for the field
            $render_method = 'render_' . $field['type'] . '_field';

            if ( method_exists( $this, $render_method ) ) { 
                $this->$render_method( $field, $value ); 
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

    public function add_field_status () {
        
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
        $slug = sanitize_key( $args['slug'] );
        // if it's an array, flatten it
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_attr( $value );
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->init->add_status_to_field( __( 'this field is required....') );
        }
        $type = $args['type'];
        if ( $args['class'] ) $class = $args['class']; else $class = '';
        if ( $args['size'] ) $size = $args['size']; else $size = '40';
        echo '<input title="' . $args['title'] . '" type="' . $type . '" class="' . $class . '" id="' . $slug . '" name="' . $slug .'" size="' . $size . '" value="' . $value . '" />';
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
        if ( ! empty( $value ) && ! $this->init->is_postal( $value ) ) {
            $err = $this->init->add_status_to_field( __( 'this is not a valid postal code' ) );
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
        if ( ! empty( $value ) && ! $this->init->is_phone( $value ) ) {
            $err = $this->init->add_status_to_field( __( 'this is not a valid phone number' ) );
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
        if ( ! empty( $value ) && ! $this->init->is_email( $value ) ) {
            $err = $this->init->add_status_to_field( __( 'this is not a valid email' ) );
        }
        return $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Render a URL field (http or https).
     * {@link https://stackoverflow.com/questions/2280394/how-can-i-check-if-a-url-exists-via-php}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value
     */
    public function render_url_field ( $args, $value ) {

        // TODO: THIS CHECK IS NOT WORKING...
        // TODO: FOR REDIRECT
        // TODO: redo URL check

        $err = '';
        if ( ! empty( $value ) ) {
            // Only check if active checking was set in plugin options
            $option = get_option('plse-settings-config-check-urls'); // value is 'on' or nothing
            if ( $option ) {
                if ( ! $this->init->get_final_url( $value ) ) {
                    $err = $this->init->add_status_to_field( __( 'the address may not go to a valid web page (check it!)' ) );
                }
            }
        }

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
        $rows = '5';
        $cols = '60';
        $slug = sanitize_key( $args['slug'] );
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_html( $value );
        if ( isset( $args['rows'] ) ) $rows = $args['rows'];
        if ( isset( $args['cols'] ) ) $cols = $args['cols'];
        if ( $this->init->is_required( $args ) ) {
            $err = $this->init->add_status_to_field( __( 'this field is required....' ) );
        }
        echo '<textarea title="' . $args['title'] . '" id="' . $slug . '" name="' . $slug .'" rows="' . $rows . '" cols="' . $cols . '">' . $value . '</textarea>';
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
        $slug = sanitize_key( $args['slug'] );
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_attr( $value );
        if ( $this->init->is_required( $args ) ) {
            $err = $this->init->add_status_to_field( __( 'this field is required....' ) );
        }
        echo '<input title="' . $args['title'] . '" id="' . $slug . '" type="date" name="' . $slug . '" value="' . $value . '">';
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
        $slug = sanitize_key( $args['slug'] );
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_attr( $value );
        if ( $this->init->is_required( $args ) ) {
            $err = $this->init->add_status_to_field( __( 'this field is required....' ) );
        }
        echo '<input title="' . $args['title'] . '" id="' . $slug . '" type="time" name="' . $slug . '" value="' . $value . '">';
        if ( ! empty( $err ) ) echo $err;
    }

    /**
     * Render a slider for time duration (0-24 hours, minutes, seconds)
     * The slider saves seconds, which need to be converted to ISO format 
     * in plse-schema-xxx.php classes.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args    field parameters, select
     * @param    number   $value   duration, in seconds.
     */
    public function render_duration_field ( $args, $value ) {
        $err = '';
        $slug = sanitize_key( $args['slug'] );
        // max is defined in seconds. 21600 = 6 hours default
        if ( isset( $args['max'] ) ) $max = $args['max']; else $max = '21600';
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_attr( $value );
        if ( ! $value ) $value = '0';
        if ( $this->init->is_required( $args ) ) {
            $err = $this->init->add_status_to_field( __( 'this field is required....' ) );
        }

        echo '<div class="plse-slider">';
        echo '<input title="' . $args['title']. '" name="' . $slug . '" id="' . $slug . '" class="plse-duration-picker plse-slider-input" id="range-control" type="range" min="0" max="' . $max . '" step="1" value="' . $value . '">';
        echo '<span class="plse-slider-output"></span>';
        echo '</div>';
        echo '<p>Slide the slider, or use keyboard arrow keys to adjust.</p>';

        if ( ! empty( $err ) ) echo $err;

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
        $slug = sanitize_key( $args['slug'] );
        $title = esc_html( $args['title'] );
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_attr( $value );
        echo '<input title="' . $title . '" style="display:inline-block;" type="checkbox" id="' . $slug . '" name="' . $slug . '"';
        if ( $value == $this->init->get_checkbox_on() ) echo ' CHECKED';
        echo ' />&nbsp;';	
        echo '<p style="display:inline-block; width=90%;">' . $title . '</p>';
    }

    /**
     * Create an input field similar to the old 'combox' - typing narrows the 
     * results of the list, but users can type in a value not on the list.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function render_datalist_field ( $args, $value ) {
        $option_list = $args['option_list'];
        $slug = sanitize_key( $args['slug'] );
        if ( isset( $args['size'] ) ) $size = $args['size']; else $size = '30';
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_attr( $value );

        if ( $this->init->is_required( $args ) ) {
            $err = $this->init->add_status_to_field( __( 'this field is required....' ) );
        }
 
        $dropdown = '<div class="plse-options-datalist"><input type="text" title="' . $args['title'] . '" id="' . $slug . '" name="' . $slug . '" autocomplete="off" class="plse-datalist" size="' . $size . '" value="' . $value . '" list="';

        if ( is_array( $option_list ) ) { // option list in field definition

            $dropdown = $this->datalists->get_datalist( $option_list, $slug . '-data' );

        } else { // option list specifies a standard list in PLSE_Datalists

            // load the datalist (note they must follow naming conventions)
            $dropdown .= 'plse-' . $option_list . '-data' . '">'; // option list id 
            $method = 'get_' . $option_list . '_datalist';
            if ( method_exists( $this->datalists, $method ) ) { 
                $dropdown .= $this->datalists->$method(); 
            }

        }

        $dropdown .= '<p>' . __( 'Begin typing to find value, or type in your own value. Delete all text, click in the field, and re-type to search for a new value.' ) . '</p></div>';

        echo $dropdown;
    }

    /**
     * Render a pulldown menu with only one option selectable.
     * Requires an option list be defined in the field $args, or in PLSE_Datalists
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    string   $value    the field value
     */
    public function render_select_single_field ( $args, $value ) {
        $option_list = $args['option_list'];
        if ( is_array( $value ) ) $value = $value[0];
        $slug = sanitize_key( $args['slug'] );
        $value = esc_attr( $value );
        $dropdown = '<div class="plse-option-select"><select title="' . $args['title'] . ' id="' . $slug . '" name="' . $slug . '" class="cpt-dropdown">' . "\n";
        $dropdown .= $this->datalists->get_select( $option_list, $value );
        $dropdown .= '</select>' . "\n";
        $dropdown .= '<p class="plse-option-select-description">' . __( 'Select one option from the list' ) . '</p></div>';

        echo $dropdown;
    }

    /**
     * Render a scrolling list of options allowing multiple select.
     * Requires an option list be defined in the field $args, or in PLSE_Datalists
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    array    $value    an array with all options selected
     */
    public function render_select_multiple_field ( $args, $value ) {
        $option_list = $args['option_list'];
        if ( ! $option_list ) return; // options weren't added
        $slug = sanitize_key( $args['slug'] );

        // if multi-select, $value is an array with a sub-array of values
        if ( is_array( $value ) ) $value = $value[0];

        // create the scrolling list
        $dropdown = '<div class="plse-option-select"><select multiple="multiple" title="' . $args['title'] . ' id="' . $slug . '" name="' . $slug . '[]" class="cpt-dropdown" >' . "\n";   
        $dropdown .= $this->datalists->get_select( $option_list, $value );
        $dropdown .= '</select>' . "\n";
        $dropdown .= '<p class="plse-option-select-description">' . __( '(CTL-Click to for select and deselect)') . '</p></div>';

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
     * Render Repeater field, which allows users to self-generate multiple entries.
     * Requires JavaScript to work.
     * 
     * {@link https://codexcoach.com/create-repeater-meta-box-in-wordpress/}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args field parameters, select
     * @param    array    $value an array with multiple values
     */
    public function render_repeater_field ( $args, $value ) {
        $slug = sanitize_key( $args['slug'] );
        // adjust size of fields
        if ( isset( $args['size'] ) ) $size = $args['size']; else $size = '50';
        // adjust text field subtype (url, date, time...)
        if ( isset( $args['subtype'] ) ) $type = $args['subtype']; else $type ='text';
        $option_list = $args['option_list'];
        $datalist_id = '';
        $datalist = '';
        $max = $this->repeater_max; // maximum number of repeater fields allowed
        $is_image = $args['is_image'];
        if( $is_image == true ) $table_width = '80%';
        else $table_width = '70%';

        /*
         * $value is supposed to be an array, unlike other fields
         * NOTE: a maximum number of repeates is calculated from the $option_list size, if present
         * TODO: UNIQUE-IFY the RESULTS (no duplicates)
         */

        // create a datalist, if data is present
        if ( isset( $option_list ) ) {
            if ( is_array( $option_list ) ) { // $option_list is an array
                $datalist_id = $slug . '-data';
                $datalist = $this->datalists->get_datalist( $option_list, $datalist_id );
                $max = count( $option_list ); // size of array
            } else { // option list specifies a standard list in PLSE_Datalists
                // load the datalist (note they must follow naming conventions)
                $method = 'get_' . $option_list . '_datalist';
                if ( method_exists( $this->datalists, $method ) ) { 
                    $datalist .= $this->datalists->$method();
                    $datalist_id = $option_list;
                    $datalist_id = 'plse-' . $datalist_id . '-data'; // $option list is the id value 
                }
                $method = 'get_' . $option_list . '_size';
                if ( method_exists( $this->datalists, $method ) ) {
                    $max = $this->datalists->$method();
                }
    
            }
            echo $datalist;
            $list_attr = 'list="' . $datalist_id . '"';
        }

        // begin rendering the table with repeater options
        ?>
        <div id="plse-repeater-<?php echo $slug; ?>" class="plse-repeater">
            <div id="plse-repeater-max-warning" class="plse-repeater-max-warning" style="display:none;">You have reached the maximum number of values</div>
            <table class="plse-repeater-table" width="<?php echo $table_width; ?>" data-max="<?php echo $max; ?>">
                <tbody>
                    <!--default row, or rows from datatbase-->
                    <?php 
                    if( $value ):
                        foreach( $value as $field ) { 
                            if ( ! empty( $field ) ) {
                                $wroteflag = true;
                            ?>
                            <tr>
                            <td><input name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input" value="<?php if($field != '') echo esc_attr( $field ); ?>" size="<?php echo $size; ?>" placeholder="type in value" /></td>
                            <td><a class="button plse-repeater-remove-row-btn" href="#1">Remove</a></td>
                            </tr>
                        <?php 
                            }
                        }
                        if ( ! $wroteflag ):
                            ?>
                            <tr>
                            <td><input name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input" value="<?php if($field != '') echo esc_attr( $field ); ?>" size="<?php echo $size; ?>" placeholder="type in value" /></td>
                            <td><a class="button plse-repeater-remove-row-btn" href="#1">Remove</a></td>
                            </tr>
                            <?php 
                        endif;
                    else: ?>
                    <tr class="plse-repeater-default-row" style="display: table-row">
                        <td><input name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input" size="<?php echo $size; ?>" placeholder="<?php echo __( 'enter text here' ); ?>"/>
                        </td>
                        <td><a class="button plse-repeater-remove-row-btn button-disabled" href="#">Remove</a></td>
                    </tr>
                    <?php endif;
                    ?>
                    <!--invisible blank row, copied to create new visible row-->
                    <tr class="plse-repeater-empty-row" style="display: none">
                        <td><input name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input" size="<?php echo $size; ?>" placeholder="<?php echo __( 'enter text here' ); ?>"/>
                        </td>
                        <td><a class="button plse-repeater-remove-row-btn" href="#">Remove</a></td>
                    </tr>
                </tbody>
            </table>
        <p><a class="button plse-repeater-add-row-btn" href="#">Add another</a></p>
        <?php 
            if ( isset( $option_list ) ) {
            echo '<p>' . __( 'Begin typing to find value, or type in your own value. Delete all text, click in the field, and re-type to search for a new value.' ) . '</p>';
            }
            if ( $is_image ) echo __( '<p>' . __( 'Hit the tab key after entering to check if the image is valid.' ) . '</p>' );

        ?>
        </div>

<?php 

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
        $slug = sanitize_key( $args['slug'] );
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_url( $value );
        $title = $args['title'];

        echo '<div class="plse-meta-image-col">';

        if ( $value ) {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="' . $slug . '-img-id" src="' . $value . '" width="128" height="128">';
        } else {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="'. $slug . '-img-id" src="' . $plse_init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }

        echo '</div><div class="plse-meta-upload-col">';

        echo '<div>' . __( 'Image URL in WordPress' ) . '</div>';
        echo '<div>';

        // media library button (ajax call)
        echo '<input type="text" name="' . sanitize_key( $slug ) . '" id="' . $slug . '" value="' . $value . '">';
        echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug . '" value="Upload Image" />';

        echo '</div></div>';

    }

    /**
     * Video URL also captures a thumbnail
     */
    public function render_video_field ( $args, $value ) {
        
        $plse_init = PLSE_Init::getInstance();
        $slug = sanitize_key( $args['slug'] );
        $title = $args['title'];

        /**
         * create the thumbnail URL
         * {@link https://ytimg.googleusercontent.com/vi/<insert-youtube-video-id-here>/default.jpg}
         */ 
        echo '<div class="plse-video-metabox">';
        // add a special class for JS to the URL field for dynamic video embed
        $args['class'] = 'plse-embedded-video-url';
        $args['size'] = '60';
        $args['type'] = 'URL';
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_url( $value );
        //$this->render_url_field( $args, $value );

        echo '<table style="width:100%">';
        echo '<tr>';
        // create the input field for the url
        echo '<td colspan="2" style="padding-bottom:4px;">' . $this->render_url_field( $args, $value ) . '</td>';
        echo '</td>';
        echo '<tr>';
        echo '<td style="width:50%; text-align:center;position:relative">';
        if ( $value ) {
            // get a thumbnail image from the video URL
            $thumbnail_url = esc_url( $this->init->get_video_thumb( $value ) );
            // clunky inline style removes offending hyperlink border see with onblur event
            echo '<a href="' . $value . '" style="display:inline-block;height:0px;"><img title="' . $title . '" class="plse-upload-img-video-box" id="' . $slug . '-img-id" src="' . $thumbnail_url . '" width="128" height="128"></a>';
        } else {
            echo '<img title="' . $title . '" class="plse-upload-img-video-box" id="'. $slug . '-img-id" src="' . $plse_init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }
        echo '</td>';
        echo '<td class="plse-auto-resizable-iframe" style="text-align:center;">';
        echo '<div class="plse-embed-video"></div>';
        echo '</td>';
        echo '<tr>';
        echo '<td style="width:50%;text-align:center">';
        echo __( 'Thumbnail' ) . '</span>';
        echo '</td>';
        echo '<td style="width:100%;text-align:center;">';
        echo __( 'Video Player' );
        echo '</td>';
        echo '</tr>';
        echo '</table>';

        echo '</div>';

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
        $schema_list = $this->init->get_available_schemas();

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
                        switch ( $field['type'] ) {

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
                                $value = $value;
                                break;

                            case PLSE_INPUT_TYPES['REPEATER']:
                                $count = count( $value );
                                for ( $i = 0; $i < $count; $i++ ) {
                                    if ( ! empty( $value[$i] ) ) {
                                        $value[$i] = stripslashes( strip_tags( $value[$i] ) );
                                    }
                                }
                                break;

                            case PLSE_INPUT_TYPES['TIME']:
                                // format: 
                                break;

                            case PLSE_INPUT_TYPES['DURATION']:
                                break;

                            case PLSE_INPUT_TYPES['AUDIO']:
                                // might save a thumbnail, e.g. album cover
                                break;

                            case PLSE_INPUT_TYPES['VIDEO']:
                                // saving a video URL, but must also save thumbnail if present
                                break;

                            default: 
                                if ( is_array( $value ) ) {
                                    foreach ( $value as $val ) {
                                        if ( ! is_array( $val ) ) $value = sanitize_text_field( $value );
                                    }
                                }
                                if ( ! is_array( $value ) ) $value = sanitize_text_field( $value );
                                // TODO: sanitize array
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