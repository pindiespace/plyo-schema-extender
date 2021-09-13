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
     * PLSE_Init class instance.
     * 
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Init    $init    the PLSE_Init class
     */
    private $init = null;

    /**
     * shared field definitions, Schema data is loaded separately
     * 
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Options_Data    $options_data    the PLSE_Options_Data class
     */
    private $options_data = null;

    /**
     * datalists, e.g. country name lists
     * 
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Datalists    $datalists    the PLSE_Datalists class
     */
    private $datalists = null;

    /**
     * Store reference to shared PLSE_Init class.
     *
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Fields    $fields    the PLSE_Init class.
     */
    private $fields = null;

    /**
     * name of JS variable holding relevant PHP variables passed from this class by PLSE_Init.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $options_js_name    name of the JS variable holding field names
     */
    private $options_js_name = 'plse_plugin_options';

    /**
     * name of object holding relevant PHP variables passed from this class into JavaScript.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $options_js_name
     */
    private $meta_js_name = 'plse_plugin_custom_fields';

    /**
     * Schema transient name, for errors accross $post updates
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $schema_transient_name    name for transient
     */
    private $schema_transient_name = 'plse_meta_transient';

    /**
     * Maximum for meta repeater fields.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $REPEATER_MAX    max number of repeater fields
     */
    private $REPEATER_MAX = 1000;

    /**
     * See if we need to check each URL for validity (set in plugin options)
     * 
     * @since    1.0.0
     * @access   private
     * @var      string|null    $check_urls    if not null, check URLS
     */
    private $check_urls = null;

    /**
     * See if we have local control over Schema rendering (set in plugin options)
     * If this is set, each $post can turn off actual rendering, but allow Schema 
     * data to be edited. If null, then Schmas are rendered for all posts matching 
     * the specified CPTs or categories in the plugin settings.
     */
    private $check_local_control = null;

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

        // field rendering
        $this->fields = PLSE_Fields::getInstance();

        // initialze metaboxes assigned by plugin options
        add_action( 'admin_init', [ $this, 'setup' ] );

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Metabox    $self__instance
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

        ///$plse_init = PLSE_Init::getInstance();

        // load scripts common to PLSE_Settings and PLSE_Meta, get the label for where to position
        //////$script_label = $plse_init->load_admin_scripts();
        $script_label = $this->init->load_admin_scripts();

        wp_enqueue_script( PLSE_SCHEMA_EXTENDER_SLUG, $url . $this->plse_admin_js, array('jquery'), null, true );

        // use PLSE_Options to inject variables into JS specifically for PLSE_Meta Media library button clicks 
        $this->init->load_js_passthrough_script( 
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
     * @param    string      $schema_label the Schema label, e.g. 'GAME', 'Game', 'game'
     * @return   array|null  Schema field data needed to create metabox, or null
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
     * Check if a metabox should be drawn. Schema are assigned in plugin options, either by:
     * - a Custom Post Type
     * - a category assigned to the $post
     * 
     * NOTE: users editing the metaboxes can set whether the Schema is rendered, independently 
     * of whether the metabox is drawn, if set in plugin options.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $schema_label     a Schema label, 'GAME', 'Game', or 'game'
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

        // see if plugin settings allow posts to disable Schema rendering
        $this->check_local_control = get_option( PLSE_LOCAL_POST_CONTROL_SLUG );

        // see if URLs should be actively checked for validity (e.g. 404 errors)
        $this->check_urls = get_option( PLSE_CHECK_URLS_SLUG );

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
            echo '<div class="plse-input-err-msg"><p>' . __( 'Error During Load' ) . '</p><span>' . $e . '</span></div>';
        }

        // descriptive metabox message
        echo '<p class="plse-meta-message">' . ucfirst( $msg ) . ' Schema. ' . $meta_field_args['message'] . '</p>';
        echo '<ul class="plse-meta-list">';

        // add nonce
        $nonce = $meta_field_args['nonce'];
        $context = $meta_field_args['slug'];
        wp_nonce_field( $context, $nonce );

        // loop through each Schema field
        foreach ( $fields as $key => $field ) {

            // save the post ID for error reporting
            $field['post_id'] = $post->ID;

            /*
             * Conditionally draw the local rendering checkbox:
             * - don't draw if plugin options disabled local control of Schema rendering
             * - draw if plugin options locally enabled rendering control
             */
            if ( $key == PLSE_SCHEMA_RENDER_KEY ) {
                // $option = get_option( PLSE_LOCAL_POST_CONTROL_SLUG );
                if ( $this->check_local_control != $this->init->get_checkbox_on() ) {
                // if ( $option != $this->init->get_checkbox_on() ) {
                    continue; // break out of the foreach loop
                }
            }

            // if the field is the start of a field group, put in a divider
            if ( isset( $field['start_of_block'] ) ) {
                echo '<li class="plse-group-message">Field Group: ' . $field['start_of_block'] . '</li>';
            }

            // begin rendering the field
            echo '<li>';

            /* 
             * if we store Schema data using the Options API, access the option directly.
             * Normally this is not the case, and Schema data is stored in each $post meta-data
             */
            if( $field[ 'wp_data' ] == PLSE_DATA_SETTINGS ) {

                $value = get_option( $field['slug'] );

            } else if ( $field[ 'wp_data' ] == PLSE_DATA_POST_META ) {

                /*
                 * get the string associated with this field in this post (if no slug,
                 * get all the CPTs for this post)
                 */
                if ( $field['select_multiple'] ) {
                    $value = get_post_meta( $post->ID, $field['slug'] ); // multi-select control, returns array
                } else {
                    $value = get_post_meta( $post->ID, $field['slug'], true ); // single = true, returns meta value
                }

            }

            /*
             * Flag required fields that are not filled out. 
             * (add message at top-right of <li>).
             */
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

            // add the value to the field description (allows options and metabox to use same rendering functions)
            $field['value'] = $value;

            if ( method_exists( $this, $render_method ) ) { 
                $this->$render_method( $field, $value ); 
            }

            echo '</li>';

        }

        // close the box
        echo '</ul></div>';

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
     * @param    array     $field     arguments needed to render the field
     * @param    string    $value    serialized or unserialized field value
     */
    public function render_hidden_field ( $field ) {
        $this->fields->render_simple_field( $field );
    }

    /**
     * Render a text input field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field arguments needed to render the field
     * @param    string    $value    field value
     */
    public function render_text_field ( $field ) {
        $value = $this->fields->render_text_field( $field );
    }

    /**
     * Render postal code field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value
     */
    public function render_postal_field ( $field ) {
        $value = $this->fields->render_postal_field( $field );
    }

    /**
     * Render a telephone field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value the field value
     */
    public function render_tel_field ( $field ) {
        $value = $this->fields->render_postal_field( $field );
    }

    /**
     * Render an email field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value
     */
    public function render_email_field ( $field ) {
        $value = $this->fields->render_email_field( $field );
    }

    /**
     * Render a URL field (http or https), optionally checking for validity.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string   $value    the field value
     */
    public function render_url_field ( $field ) {
        $value = $this->fields->render_url_field( $field );
    }

    /**
     * Render a textara field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value
     */
    public function render_textarea_field ( $field ) {
        $value = $this->fields->render_textarea_field ( $field );
    }

    /**
     * Render a Date field, 
     * - UI shows: dd:mm:yyyy in the UI
     * - $value is: yyyy-mm-dd value, e.g. 2021-08-27
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value, with correct date format YYYY-MM-DD
     */
    public function render_date_field ( $field ) {
        $value = $this->fields->render_date_field ( $field );
    }

    /**
     * Render a Time field, value always HH:MM:AM/PM.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string   $value    the field value, formatted HH:MM:AM/PM
     */
    public function render_time_field ( $field ) {
        $value = $this->fields->render_time_field ( $field );
    }

    /**
     * Render a slider for time duration (0-24 hours, minutes, seconds)
     * The slider saves seconds, which need to be converted to ISO format 
     * in plse-schema-xxx.php classes.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field    field parameters, select
     * @param    number   $value   duration, in seconds.
     */
    public function render_duration_field ( $field ) {
        $err = '';
        $field['class'] = 'plse-meta-ctl-highlight';
        $value = $this->fields->render_duration_field( $field );
        if ( ! empty( $err ) ) echo $err;

    }

    /**
     * Render a checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value 'on' or not on
     */
    public function render_checkbox_field ( $field ) {

        $err = '';
        $field['class'] = 'plse-meta-ctl-highlight';
        $value = $this->fields->render_checkbox_field( $field );

        // validation would go here

        if ( ! empty( $err ) ) echo $err;

    }

    /**
     * Create an input field similar to the old 'combox' - typing narrows the 
     * results of the list, but users can type in a value not on the list.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string   $value    the field value
     */
    public function render_datalist_field ( $field ) {
        $err = '';
        $field['class'] = 'plse-option-datalist';

        // validation goes here

        $this->fields->render_datalist_field( $field );
        if ( ! empty( $err ) ) echo $err;

    }

    /**
     * Render a pulldown menu with only one option selectable.
     * Requires an option list be defined in the field $field, or in PLSE_Datalists
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value
     */
    public function render_select_single_field ( $field ) {
        $field['class'] = 'plse-meta-select';
        $this->fields->render_select_single_field( $field );
    }

    /**
     * Render a scrolling list of options allowing multiple select.
     * Requires an option list be defined in the field $field, or in PLSE_Datalists
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    array    $value    an array with all options selected
     */
    public function render_select_multiple_field ( $field, $err = '' ) {
        $field['class'] = 'plse-option-select';
        $value = $this->fields->render_select_multiple_field( $field );
    }

    /**
     * Render Custom Post Type list. The option_list is a list of all 
     * Custom Post Types currently defined in WP.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    array    $value    an array with all options selected
     */
    public function render_multi_cpt_field ( $field ) {
        $cpts = $this->init->get_all_cpts(); // get all potential selections
        if ( ! $cpts ) {
            $err = $this->fields->add_status_to_field( __( 'No Custom Post Types are defined yet.' ) );
        }
        // CPTs are dynamically loaded, so they should *always* be ok
        $field['option_list'] = $this->init->get_option_list_from_cpts( $cpts );
        $this->fields->render_select_multiple_field( $field );
    }

    /**
     * Render Category list. The option_list is a list of all Categories
     * defined for the current post type in WP.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    array    $value    an array with all options selected
     */
    public function render_multi_cat_field ( $field ) {
        $cats = $this->init->get_all_cats();
        if ( ! $cats ) {
            $err = $this->fields->add_status_to_field( __( 'this is not a valid postal code' ) );
        }
        // Categories are dynamically loaded, so they *always* should be ok
        $field['option_list'] = $this->init->get_option_list_from_cats( $cats );
        $this->fields->render_select_multiple_field( $field );
    }

    /**
     * Render Repeater field, which allows users to self-generate multiple entries.
     * Requires JavaScript to work.
     * 
     * {@link https://codexcoach.com/create-repeater-meta-box-in-wordpress/}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    array    $value an array with multiple values
     */
    public function render_repeater_field ( $field ) {
        $field['class'] = 'plse-option-ctl-highlight';
        $value = $this->fields->render_repeater_field( $field );
    }

    /**
     * Render an image with its associated URL field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string    $value    the URL of the image
     */
    public function render_image_field ( $field ) {
        $field['class'] = 'plse-meta-ctl-highlight';
        $value = $this->fields->render_image_field( $field );
    }

    public function render_audio_field ( $field ) {
        $field['class'] = 'plse-meta-ctl-highlight';
        $value = $this->fields->render_audio_field( $field );
    }

    /**
     * Video URL also captures a thumbnail image.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field field parameters, select
     * @param    string    $value    the URL of the video (YouTube or Vimeo supported)
     */
    public function render_video_field ( $field ) {

        $field['class'] = 'plse-embedded-video-url';
        $value = $this->fields->render_video_field( $field );
    }


    /**
     * Render an input field, type="number" with integers only.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field field parameters, select
     * @param    string    $value    an integer value
     */
    public function render_int_field ( $field ) {
        $this->fields->render_int_field( $field );
    }

    /**
     * Render an input field, type="number" with floating-point only
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field field parameters, select
     * @param    string    $value    an floating-point value, optionally decimal places
     */
    public function render_float_field ( $field ) {
        $this->fields->render_float_field( $field );
    }

    /**
     * --------------------------------------------------------------------------
     * SAVING METABOX CUSTOM FIELDS
     * --------------------------------------------------------------------------
     */

    /**
     * Check entered data before saving.
     * 
     * @since    1.0.0
     * @access   public
     * @param    number    $post_id    the ID of the post
     * @param    mixed     $post_data  post data
     */
    public function metabox_before_save ( $post_id, $post_data ) {

    }

    /**
     * Save the metabox data.
     * NOTE: errors can prevent a save, even if the UI doesn't crash. 
     * Confirm by reloading the page, checking Web Console for 500 errors.
     * 
     * @since    1.0.0
     * @access   public
     * @param    number   $post_id    ID of current post
     * @param    WP_Post  $post       the current post
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
                                $value = date( 'Y-m-d', strtotime( $value ) );
                                break;

                            //case PLSE_INPUT_TYPES['SELECT_MULTIPLE']:
                            //    $value = $value;
                            //    break;

                            case PLSE_INPUT_TYPES['SELECT_MULTIPLE']:
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
                                // we assume all fields contain text-like data
                                if ( is_array( $value ) ) {
                                    foreach ( $value as $val ) {
                                        if ( ! is_array( $val ) ) $value = sanitize_text_field( $value );
                                    }
                                }
                                else if ( ! is_array( $value ) ) $value = sanitize_text_field( $value );
                                break;

                        }

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
     * 
     * @since    1.0.0
     * @access   public
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
        set_transient( $this->schema_transient_name . get_current_user_id(), $err_msg, $duration );
    }

    /**
     * Read any storied transient messages.
     * 
     * Used to record errors during load, NOT after the schema is updated. Errors 
     * in individual fields appear next to each field after an update.
     * 
     * @since     1.0.0
     * @access    public
     * @return    string    the string stored in the transient
     */
    public function metabox_read_transient () {
        return get_transient( $this->schema_transient_name );
    }

    /**
     * Display errors.
     * 
     * NOTE: THIS IS NOT WORKING. 
     * If you try a do_action('admin_init'), the error renders 
     * BEFORE WP begins creating the pages
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $notice_type   defined notice type, status, warning, error
     * @param    string    $err_msg       error message
     */
    public function metabox_show_errors ( $notice_type, $err_msg ) {

        $stored_err_msg = $this->metabox_read_transient();
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