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
        $this->init = PLSE_Init::getInstance();

        // shared field definitions, Schema data is loaded separately
        $this->options_data = PLSE_Options_Data::getInstance();

        ////$s = get_user_setting('plse-user-setting-error');
        ////if ($s == 'ERROR') {
        add_action( 'admin_notices',   [ $this, 'metabox_show_errors' ], 12   );
        ////}

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
     * Look in the Schema directory, extract current Schema list from the FILEs
     * pattern: 'plse-schema-xxxx.php' to 'XXX', plse-schema-game.php to 'GAME.'
     * 
     * @since    1.0.0
     * @access   private
     * @return   array    a list of the defined Schemas, capitalized
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
                //$schemas[] = strtoupper( preg_replace( $patterns, $replacements, $entry ) );
                $schemas[] = $this->init->slug_to_label( preg_replace( $patterns, $replacements, $entry ) );
            }

        }

        closedir( $handle );

        return $schemas;

    }


    /**
     * Based on required Schema, get data from the Schema class for the metabox.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array|null    schema field data needed to create metabox, or null
     */
    public function load_schema_fields ( $schema_label ) {

        // upper-case the first letter, from 'game' to 'Game' to make the class name
        $class_name = 'PLSE_Schema_' . $this->init->label_to_class_slug( $schema_label );

        // load the appropriate class.
        if ( ! class_exists( $class_name ) ) {

            $class_path = plugin_dir_path( dirname( __FILE__ ) ) . $this->schema_dir . '/'. $this->schema_file_prefix . $schema_label . '.php';

            if ( file_exists( $class_path ) ) {
                require $class_path; // now the class exists
                return $class_name::$schema_fields; // read static public member variable
            }

        }

        return null; // read static public member variable

    }

    /**
     * Check if a metabox should be drawn (Schema assigned by post type or category).
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
     */
    public function setup () {

        add_action( 'admin_enqueue_scripts', [ $this, 'setup_scripts' ] );

        // NOTE: 'save_post' needs to come BEFORE 'add_meta_boxes'
        add_action( 'pre_post_update', [ $this, 'metabox_before_save' ], 1, 2);
        add_action( 'save_post',       [ $this, 'metabox_save'     ],  2, 2  );
        add_action( 'wp_insert_post',  [ $this, 'metabox_after_save' ], 12, 4 );
        add_action( 'add_meta_boxes', [ $this, 'setup_metaboxes' ] );

    }

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

    }

    /**
     * Initialize metabox display
     * - enqueue scripts
     * - set up metaboxes, determining which should be shown
     * 'admin_init' hook
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
                 * NOTE:
                 * NOTE:
                 * NOTE: if there is an error here ( for example, the 
                 * schema/plse-schema-xxx.php file is not available), can't display ERROR
                 */

                $this->metabox_register( 
                    $schema_label, 
                    $this->load_schema_fields( $schema_label ), 
                    $schema_label // additional argument passed
                );

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

        // pass field information to the metabox rendering function
        $args = array(
            'schema_label' => $schema_label,
            'schema_fields' => $schema_data['fields'],
            'nonce' => $schema_data['nonce'],
            'slug' => $schema_data['slug'],
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

    /**
     * Render an individual metabox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    WP_POST    $post    the current post
     * @param    array      args     field data needed to render metabox
     */
    public function render_metabox ( $post, $args ) {

        // Note our passed parameters were merged into the default $args callback
        $schema_label = $args['args']['schema_label'];
        $fields = $args['args']['schema_fields'];
        $msg = $args['args']['msg'];
        $value = null;

        // create the metabox
        echo '<div class="plse-meta-container">';
        if ( $msg ) echo '<p>' . __( 'Schema Added due to assignments:' ) . $msg . '</p>';
        echo '<ul class="plse-meta-list">';

        // add nonce
        $nonce = $args['args']['nonce'];
        $context = $args['args']['slug'];
        wp_nonce_field( $context, $nonce );

        // loop through each Schema field
        foreach ( $fields as $field ) {

            // render the label as a list bullet
            echo '<li><label for="' . $field['slug'] . '">';
            _e( $field['label'], PLSE_SCHEMA_EXTENDER_SLUG );
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
     * Strategy: Render, and validate. Check if there is an error in input.
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
     * @param    string   $type    type="xxx" for the field
     */
    public function render_simple_field ( $args, $value, $err = '' ) {
        $type = $this->init->label_to_slug( $args['type'] );
        echo '<input title="' . $args['title'] . '" type="' . $type . '" id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" size="40" value="' . esc_attr( $value ) . '" />';
        if ( $err )echo $err;
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
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __( 'this field is required....') );
        }
        return $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Postal code
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
     * Telephone
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
     * Email field.
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
     * URL field (http or https)
     */
    public function render_url_field ( $args, $value ) {
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __('this field is required....') );
        } else if ( ! $this->init->is_url( $value ) ) {
            $err = $this->add_error_to_field( __( 'invalid address (URL)' ) );
        }
        $this->render_simple_field( $args, $value, $err );
    }

    /**
     * Textarea field.
     */
    public function render_textarea_field ( $args, $value ) {
        $err = '';
        if ( empty( $value ) && $args['required'] == 'required') {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        echo '<textarea title="' . $args['title'] . '" id="' . sanitize_key( $args['slug'] ) . '" name="' . sanitize_key( $args['slug'] ) .'" rows="5" cols="60">' . esc_attr( $value ) . '</textarea>';
        if ( ! empty( $err ) ) echo $err;
    }

    /**
     * Date field.
     */
    public function render_date_field ( $args, $value ) {
        $err = '';
        if ( $this->init->is_required( $args ) ) {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        echo '<input title="' . $args['title'] . '" type="date" name="' . sanitize_key( $args['slug'] ) . '" value="' . esc_attr( $value ) . '">';
        if ( ! empty( $err ) ) echo $err;
    }

    /**
     * Time field, value always HH:MM
     */
    public function render_time_field ( $args, $value ) {
        //TODO: value is HH:MM:SS
        $err = '';
        if ( $this->init->is_required( $args ) ) {
            $err = $this->add_error_to_field( __('this field is required....') );
        }
        echo '<input title="' . $args['title'] . '" type="time" id="' . sanitize_key( $arg['slug' ] ) . '" name="' . sanitize_key( $arg['slug' ] ) . '" min="00:00" max="24:00" value="' . $esc_attr( $value ) . '">';
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

    public function render_checkbox_field ( $args, $value ) {
        //TODO:
        echo "CHECKBOX FIELD...............";
        echo '<input title="' . $args['title'] . '" style="display:block;" type="checkbox" id="' . $slug . '" name="' . $slug . '"';
        if ( $option == $this->ON ) echo ' CHECKED';
        echo ' />';	
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
     * --------------------------------------------------------------------------
     * SAVING METABOX CUSTOM FIELDS
     * --------------------------------------------------------------------------
     */

    /**
     * Check entered data before saving.
     */
    public function metabox_before_save ( $post_id, $post_data ) {

        //if ( ! is_admin() ) return;

        //$s = print_r( $post_data, true );
        $slug = 'plyo-schema-extender-game-description';
        update_post_meta( $post_id, $slug, '666' );

        // use http://rachievee.com/how-to-intercept-post-publishing-based-on-post-meta/

    }

    /**
     * Save the metabox data.
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

                //////set_user_setting('plse-user-setting-error', 'OK'); //////////////////////

                 // save individual field values
                foreach ( $fields as $key => $field ) {

                    $slug = $field['slug'];

                    if( ! isset( $_POST[ $slug ] ) ) {

                        delete_post_meta( $post_id, $slug );

                    } else {

                        $value = trim( $_POST[ $slug ] );

                        switch ( $field['type'] ) {

                            case PLSE_INPUT_TYPES['EMAIL']:
                                $value = sanitize_email( $value );
                                break;

                            case PLSE_INPUT_TYPES['URL']:
                                $value = esc_url_raw( $value, [ 'http', 'https' ] );
                                break;

                            case PLSE_INPUT_TYPES['TEXTAREA']:
                                $value = esc_textarea( $value );

                            default: 
                                $value = sanitize_text_field( $value );
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
     * Display errors.
     */
    public function metabox_show_errors () {

    }

} // end of class