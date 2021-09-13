<?php

/**
 * Handles meta-boxes and custom fields posted to specific CPTs and categories.
 * The initial creation of the plugin menu and options pages are handled in 
 * PLSE_Init.
 *
 * @since      1.0.0
 * @category   WordPress_Plugin
 * @package    PLSE_SCHEMA_Extender
 * @subpackage PlyoSchema_Extender/admin
 * @author     Pete Markeiwicz <pindiespace@gmail.com>
 * @license    GPL-2.0+
 * @link       https://plyojump.com
 */
class PLSE_Options {

    /**
     * Store reference for singleton pattern.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $instance    static reference to initialized class.
     */
    static private $__instance = null;

    /**
     * Store reference to shared PLSE_Init class.
     *
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Init    $init    the PLSE_Init class.
     */
    private $init = null;

    /**
     * Store reference to shared PLSE_Init class.
     *
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Options_Data    $options_data    the PLSE_Options_Data class.
     */
    private $options_data = null;

    /**
     * Store reference to shared PLSE_Init class.
     *
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Datalists    $datalists    the PLSE_Init class.
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
     * The name of the variable storing plugin data in options database.
     * @since    1.0.0
     * @access   private
     * @var      string    $option_group    name for storing plugin options.
     */
    private $option_group = 'plse-settings';

    /**
     * name of JS variable holding relevant PHP variables passed from this class by PLSE_Init.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $options_js_name    name of the JS variable holding field names
     */
    private $options_js_name = 'plse_plugin_options';

    /**
     * Name of default WPSEO data, if present, which can be triggered to replace 
     * values in some of the plugin options fields (not saved to database).
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $wpseo_local_options_name the values from Yoast Local SEO, if present
     */
    private $wpseo_local_options_js_name = 'plse_wpseo_local_options';


    /**
     * array storing Yoast Local SEO information, if present
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $wpseo_local;
     */
    private $wpseo_local = array();

    /**
     * CSS panel style
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $panel_class
     */
    private $panel_class = 'plse-panel-box';

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

        //add the menu, setup_options_page for rendering
        add_action('admin_menu', [ $this, 'setup_options_menu'] );

        // we don't need to set up options if we are editing a post
        if ( $pagenow != 'post.php' ) {

            // enqueue scripts
            add_action( 'admin_init', [ $this, 'setup_options' ] );

            add_action( 'admin_notices',   [ $this, 'options_show_errors' ], 12   );

        }

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Options    $self__instance
     */
    public static function getInstance () {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Options();
        }
        return self::$__instance;
    }

    /**
     * --------------------------------------------------------------------------
     * SPECIFIC DATABASE OPERATIONS RELATED TO UI
     * --------------------------------------------------------------------------
     */

    /**
     * Set panel visibility style, based on whether the Schema is active
     * 
     * @since    1.0.0
     * @access   public
     * @return   string   a CSS visibility style, e.g. 'block' or 'none'
     */
    public function set_panel_visibility ( $schema_label ) {
        if ( $this->options_data->check_if_schema_active( $schema_label ) ) {
            return 'block';
        } else {
            return 'none';
        }

    }

    /**
     * --------------------------------------------------------------------------
     * ENQUEUE SCRIPTS AND STYLES
     * --------------------------------------------------------------------------
     */

    /**
     * Setup for the options UI (tabbed) on the page.
     * 'admin_init' hook
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_options () {

        /*
         * if Yoast Local SEO is present, get the data. Data is initially 
         * copied into the blank fields in this plugin, and can be reset 
         * from Yoast Local SEO, using a button in the plugin options UI page.
         */
        $this->wpseo_local = get_option( YOAST_LOCAL_SEO_SLUG );

        // DUMMY WPSEO LOCAL TEST
        //$this->wpseo_local = array(
        //    'location_phone' => '333-298-22222'
        //);

        // load fields
        $this->admin_init_fields();

        /* 
         * Enqueue our scripts and styles for the post area. 
         * Separate from, mutually exclusive loading from admin options pages.
         */
        add_action( 'admin_enqueue_scripts', function ( $hook ) {

            // load scripts common to PLSE_Settings and PLSE_Meta, get the label for where to position
            $script_label = $this->init->load_admin_scripts();

            // use inject variables into JS specifically for PLSE_Meta media library button clicks 
            $this->init->load_js_passthrough_script( 
                $script_label,
                $this->options_js_name,
                $this->options_data->get_options_fields() // all PLSE_Options fields
            );

            // if Yoast Local SEO is present, inject the values into JS so users can copy them to PLSE if desired
            if ( is_array( $this->wpseo_local ) ) {
                $this->init->load_js_passthrough_script(
                $script_label,
                $this->wpseo_local_options_js_name,
                $this->wpseo_local
                );
            }

        } );

    }

    /**
     * ---------------------------------------------------------------------
     * CREATE OPTIONS PAGE
     * ---------------------------------------------------------------------
     */

    /**
     * Set up the options menu in WP-Admin.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_options_menu () {

        // Put under WP_Admin->Tools, save result to decide when to enqueue scripts.
        add_menu_page( 
            PLSE_SCHEMA_EXTENDER_NAME, // page <title>
            $this->plugin_menu_title,  // admin menu text
            'manage_options',          // WP capability
            PLSE_SCHEMA_EXTENDER_SLUG, // menu slug
            [ $this, 'setup_options_page' ], // render callback
            'dashicons-networking' // schema-like hierarchy icon
        );

    }

    /**
     * Set up the default options page
     * - called by 'admin_menu' hook -> setup_options_menu -> add_menu_page()
     * - fields are added separately by 'admin_init' hook -> admin_settings(), 
     *   using the same section slug.
     * 
     * @since    1.0.0
     * access    public
     */
    public function setup_options_page () {

        // css style for panel
        $panel_style = $this->panel_class;

        // define the default panel style for toggling active/inactive
        $panel_display = 'none';

        // wraps the whole page
        echo '<div class="plyo-schema-extender">' . "\n";

        // page headers
        echo '<div class="plse-options-row">' . "\n";
        echo '<div class="plse-options-col">' . $this->init->get_logo() . '</div>';
        echo '<div class="plse-options-col plse-options-valign">';
        echo '<h2 class="plse-options-h2">' . PLSE_SCHEMA_EXTENDER_NAME . '</h2>'; 
        echo '<p class="plse-options-description">' . PLSE_SCHEMA_OPTIONS_DESCRIPTION . '</p>';
        echo '</div></div>';

        // begin the form
        echo '<form id="plse-options-form" method="post" action="options.php">';

        settings_fields( $this->option_group ); // options, also auto-generates the nonce

            // get the options value for the last tab selected (1, 2, 3...)
            $tab_href = $this->options_data->get_tabsel();

            // draw the tabs
            ?>
            <!-- plugin settings page -->
            <div class="container">
            <div class="row">
            <div class="col-md-12">
        <!-- page tabs, hidden field and section here -->
        <?php 
        echo '<div class="' . $panel_style . '" style="display:none">' ."\n";
        do_settings_sections( $this->options_data->get_section_box_slug( 'HIDDEN' ) );
        echo "</div>\n"; 

        // tab system (really one big long form)
        echo $this->setup_tabs();

        // panels
        $this->setup_panels();

        echo '</div></div>'; // end of container, row, col-md-12

        submit_button(); 

        echo '</form>';
        echo '</div>' . "\n"; // end of wrapper
        echo '</div>' . "\n"; // end of big row

    }


    /**
     * Read the Schema array, create the necessary number of onscreen tabs.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_tabs () {

        //$tab_href = 'content-tab1';
        $tab_href = $this->options_data->get_tabsel();
        $schema = $this->options_data->get_options();
        $count = 1;

        $tabstring = '<nav class="plse-tab-nav" role="navigation">' . "\n" . "<ul>\n";

        foreach ( $schema as $section ) {
            if ( $section['tab_title'] ) { // ignore hidden field blocks
                $tab = '<li><a id="tab' . $count . '" class="tab'. $count; 
                if ( $tab_href == ('content-tab' . $count ) ) $tab .= ' open';
                $tab .= '" href="#content-tab' . $count . '">' . $section['tab_title'] . "</a></li>\n";
                $tabstring .= $tab;
                $count++;
            }

        }

        $tabstring .= "</ul>\n</nav>\n";

        return $tabstring;

    }

    /**
     * Set up a group of associated options on one panel. Groups include:
     * - Schema assignment fields (which CPT or category gets a Schema)
     * - Schema globals (e.g. 'Service' may apply to the whole site/organization)
     * - Some address fields applied by Yoast Local SEO, but not free plugin
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_panels () {

        // content linked to each tab
        echo '<div id="content-tabs">';

        $schema = $this->options_data->get_options();  // Schema
        $toggles = $this->options_data->get_toggles(); // checkbox to turn Schema on|off
        $tab_href = $this->options_data->get_tabsel(); // tabbing system
        $panel_style = $this->panel_class;
        $count = 1;

        foreach ( $this->options_data->get_options() as $key => $section ) {

            // ignore 'HIDDEN'
            if ( $this->options_data->section_has_panel_tab( $key ) ) {   

                $panel_id = 'content-tab' . $count;
                $open = '';

                if ( $tab_href == $panel_id ) $open = 'open';

                echo '<div class="content-tab ' . $open . '" id="' . $panel_id . '">';

                $panel_display = $this->set_panel_visibility( $key );

                // if there's a checkbox for turning Schema on and off, show it
                echo '<div class="' . $panel_style . '">' . "\n";
                if ( $this->options_data->section_has_toggle( $key ) ) {
                    do_settings_sections( $this->options_data->get_section_toggle_slug( $key ) );
                    echo '<hr>';
                }

                // show the data group fields (which can be hidden with checkbox & JS)
                echo '<div class="plse-panel-mask" style="display:' . $panel_display . '">';
                echo '<div>' ."\n";
                do_settings_sections( $this->options_data->get_section_box_slug( $key ) );
                echo '</div></div></div></div>'; //content-tab, checkbox panel style, content-tabXXX
                $count++;

            }

        }

        echo '</div>'; // content-tabs

    }

    /**
     * Initialize all the fields in the options tab panel using WP Settings API. The
     * methods with '_toggle' are used to activate and deactivate each Schema.
     * 'admin_init hook -> setup_options()
     * 
     * @since    1.0.0
     * @access   public
     */
    public function admin_init_fields () {

        $schema = $this->options_data->get_options();

        $toggles = $this->options_data->get_toggles();

        foreach( $schema as $key => $section ) {

            /* 
             * get the checkbox toggle for Schema tabs (not config, general, address)
             * variable $state is added to the $field array for use in rendering
             * Only Schemas have panels with a $toggles entry
             */
            $state = '';
            $toggle_section = $toggles[ $key ];
            if ( ! empty( $toggle_section ) ) {
                $state = $this->admin_settings_toggle( $toggles[ $key ] );
            }

            // initialize the section (e.g. tab panel)
            $this->init_section( $section );

            $fields = $section['fields'];

            // add fields to the section
            foreach ( $fields as $field ) {
                $this->init_field( $section, $field, $state );
            }

        }

    }

    /**
     * Create the checkbox for turning Schema tabbed panels on and off. Needs 
     * its own section because it is always visible.
     * @since    1.0.0
     * @access   public
     * @param    array    $section array with section data, plus checkbox toggle for Schema use.
     * @return   string   the state stored using the Options API.
     */
    public function admin_settings_toggle ( $section ) {

        // get the checkbox field value for the entire tab
        $state = get_option( $section['fields']['used']['slug'] );

        // make sure we have Schema checked
        $this->init_section( $section );

        $this->init_field( $section, $section['fields']['used'], $state ); // checkbox is never disabled

        return $state;

    }

    /**
     * Render a section in the options page (containing multiple fields).
     * @since    1.0.0
     * @access   public
     * @param    array    $fields    slugs, titles to use in add_settings_section
     */
    public function init_section ( $section ) {

        // we have to make this local scope for function(), so use () 
        $msg = $section['section_message'];

        add_settings_section(
            $section['section_slug'],
            $section['section_title'],
            function () use ( $msg ) {
              echo $msg;
            },
            $section['section_box']
        );

    }

    /**
     * Render an individual form field, using the supplied class function name 
     * in the options page.
     * @since    1.0.0
     * @access   public
     * @param    array    $section  slugs, titles used to define section
     * @param    array    $field    slugs, titles to use to define an indivdiual field
     * @param    array    $state    $state, field is enabled or disabled, or 'width' for IMG
     */
    public function init_field ( $section, $field, $state = '' ) {

        $render_callback = 'render_' . $field['type'] . '_field';
        $validation_callback = 'validate_' . $field['type'] . '_field';

        /*
         * NOTE: Different $field types pass different elements
         * depending on how they are rendered in the UI.
         */
        $field['state'] = $state;

        /**
         * Put the value into the field description. This allows a unified
         * $field array for both option and metabox input rendering.
         */
        $field['value'] = get_option( $field['slug'] );

        ///////////////////////////////
        // NOTE: THIS SHOWS THAT LOADING THE METABOX PAGE
        // ALSO LOADS THIS PAGE!!!!
        //echo 'O:::::';
        //print_r($field['value']);
        ///////////////////////////////

        // add the field
        add_settings_field(
            $field['slug'],
            $field['description'] . ':', // appears to the left of the field on plugin options pages only
            [ $this, $render_callback ], // field rendering function callback
            $section['section_box'], // slug for section box
            $section['section_slug'], // label for general settings section
            $field // callback arguments in an array
        );

        // register setting and validation callback
        register_setting(
            $this->option_group, // overall option group
            $field['slug'],      // slug for input field (option name)
            array(
                'sanitize_callback' => array( $this, $validation_callback ),
                'default' => NULL,
            )
            //array( $this, $validation_callback ) // third argument is callback for validation function
        );

    }

    /* 
     * ------------------------------------------------------------------------
     * CONTROL RENDERING METHODS
     * NOTE: if names of these functions are changed, they also need to 
     * be changed in the list of render fields functions in private class variables.
     * ------------------------------------------------------------------------
     */

    /**
     * Render a hidden field.
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_hidden_field ( $field ) {
        $this->fields->render_simple_field( $field );
    }

    /**
     * Render a button. Doesn't save DB values, but connects to JS or Ajax 
     * scripts.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field    arguments neeeded to render the field
     * @param    string   $value    serialized or unserialized field value
     */
    public function render_button_field ( $field ) {
        $slug  = sanitize_key( $field['slug'] );
        $state = esc_html( $field['state'] );
        $title = esc_html( $field['title'] );
        $label = esc_html( $field['label'] );   

        /*
         * option value (from a different field) that affects this control 
         * use variable variable '${...}' to make field ($field) value into a class variable
         * 
         * for Yoast Local SEO, this will create a test for the array that plugin generates.
         */
        if ( ! is_array( $this->{ $field['slug_dependent'] } ) ) {
            $disabled='disabled';
            $msg = $field['msg_disabled'];
        } else {
            $disabled = '';
            $msg = $field['msg_enabled'];
        }

        echo '<label style="display:block;" for="' . $slug . '">' . $label . '</label>';
        echo '<input type="button" style="padding:2px 6px 2px 6px;" title="' . $title . '" id="' . $slug . '" name="' . $slug . '" value="' . $title . '" ' . $disabled . '>';
        echo '<span class="plse-options-msg">' . $msg . '</span>';

    }

    /**
     * Render textlike fields (text, email, postal, url).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array $field name of field, state, additional properties
     * @return   string    $value    the stored option value, used to validate text field subtypes
     */
    public function render_simple_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_simple_field ( $field );
    }

    /**
     * Render a standard text field.
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_text_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_text_field( $field );
    }

    /**
     * Render a postal field.
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_postal_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_postal_field( $field );
    }

    /**
     * Render phone input field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_tel_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_phone_field( $field );
    }

    /**
     * render email input field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_email_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_email_field( $field );
    }

    /**
     * Render URL input field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_url_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_url_field( $field );
    }

    /**
     * render an integer field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_int_field ( $field ) {
        $this->fields->render_int_field( $field );
    }

    /**
     * render an float field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_float_field ( $field ) {
        $this->fields->render_float_field( $field );
    }

    /**
     * Render a textarea field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_textarea_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_textarea_field( $field );
    }

    /**
     * Render a date field.
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_date_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_date_field( $field );
    }

    /**
     * Render a time field.
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_time_field ( $field ) {
        $field['class'] = 'plse-option-input';
        $value = $this->fields->render_time_field( $field );
    }

    /**
     * Render duration
     * 
     * @since    1.0.0
     * @access   public
     * @param    array
     */
    public function render_duration_field ( $field ) {
        $field['class'] = 'plse-option-ctl-highlight';
        return $this->fields->render_duration_field( $field );
    }

    /**
     * Render checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_checkbox_field ( $field ) {
        $field['class'] = 'plse-option-ctl-highlight';
        $value = $this->fields->render_checkbox_field( $field );
    }

    /**
     * Render a datalist. Only text datalists supported.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field    field arguments
     */
    public function render_datalist_field ( $field ) {
        $field['class'] = 'plse-option-datalist';
        $value = $this->fields->render_datalist_field( $field );
    }

    /**
     * Render single-choice select field (dropdown menu).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_select_single_field ( $field ) {
        $field['class'] = 'plse-option-select';
        $value = $this->fields->render_select_single_field( $field );
    }

    /**
     * Select multi, with prebuilt option list (scrolling list).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_select_multiple_field ( $field ) {
        $field['class'] = 'plse-option-select';
        $value = $this->fields->render_select_multiple_field( $field );
    }

    /**
     * Custom Post Types used to assign Schema for specific CPTs.
     * Example: <select name='plugin_options[clusters][]' multiple='multiple'>
     * 
     * We use a custom render due to the complexity of the returned CPT array.
     * 
     * {@link https://stackoverflow.com/questions/17987233/how-can-i-set-and-get-the-values-of-a-multiple-select-with-the-wordpress-setting}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_cpt_field ( $field ) {

        // no dropdown if no Custom Post Types
        $cpts = $this->init->get_all_cpts(); // get all potential selections
        if ( ! $cpts ) {
            echo __( 'No Custom Post Types are defined yet.' );
            return;
        }
        $field['option_list'] = $this->init->get_option_list_from_cpts( $cpts );
        $this->render_select_multiple_field( $field );
    }

    /**
     * Handle multi-select scrolling list for categories.
     * 
     * We use a custom render due to the complexity of the $cat array
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_cat_field ( $field ) {
        $cats = $this->init->get_all_cats(); // no dropdown if categories don't exist
        if ( ! $cats ) {
            echo __( 'No categories are defined yet.' );
            return;
        }
        $field['option_list'] = $this->init->get_option_list_from_cats( $cats );
        $this->render_select_multiple_field( $field );
    }

    /**
     * Render a repeater field text, urls, image URLs
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field    name of field, state, additional properties
     */
    public function render_repeater_field ( $field ) {
        $field['class'] = 'plse-option-ctl-highlight';
        $value = $this->fields->render_repeater_field( $field );
    }

    /**
     * render an upload image field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_image_field ( $field ) {
        $field['class'] =  'plse-option-ctl-highlight';
        $value = $this->fields->render_image_field( $field );
    }

    /**
     * render an embedded <audio> field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_audio_field ( $field ) {
        $field['class'] = 'plse-option-ctl-highlight';
        $value = $this->fields->render_audio_field( $field );
    }

    /**
     * render an embedded <video> field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_video_field ( $field ) {
        $field['class'] = 'plse-option-ctl-highlight';
        $value = $this->fields->render_video_field( $field );
    }

    /**
     * render a plugin warning message field (just one, on first tab of 
     * plugin options). It writes some additional $field data to help 
     * debugging posts with Schema data problems.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_post_warning_field ( $field ) {

        echo '<div class="plse-label-description" style="border-radius: 6px;">';
        echo '<table><thead><tr><td><b>Link to Post</b></td><td><b>Warning Message</b></td><td><b>Time</b></td></tr></thead><tbody><tr>';

        // error list storied when posts were saved
        $val = $field['value'];

        // loose check if current user can edit meta-data for the post
        // if ( current_user_can( 'administrator' ) ) {
        if ( current_user_can_for_blog( get_current_blog_id(), 'edit_posts' ) ) {

            if ( is_array( $val ) && count( $val ) > 0 ) {

                foreach( $val as $key => $err ) {

                    $post_id = $err[1];

                    echo '<tr><td><a href="' . get_edit_post_link( $post_id ) . '">' . get_the_title( $post_id ) . '</a></td>';
                    echo '<td>' . $err[0] . '</td>';
                    echo '<td>' . $err[2] . '</td></tr>';
                }

            } else {

                // no Schema errors recorded that weren't cleared by updating a $post
                echo '<tr><td colspan="3">' . __( 'No schema errors at present' ) . '</td></tr>';

            }

        } else {

            // user can't edit the meta-data on this post
            echo '<tr><td><a href="' . get_edit_post_link( $post_id ) . '">' . get_the_title( $post_id ) . '</a></td><td colspan="2">' . __( 'your user account doesn\'t have permissions to edit Schemas. Check with your site admin.' ) . '</td></tr>';

        }

        echo '</tbody></table></div>';

    }

    /*
     * ------------------------------------------------------------------------
     * DATA VALIDATION (AND SANITIZE) METHODS
     * These methods run when the options are saved, independently of 
     * validation and sanitization when rendering the form fields
     * 
     * Note: We don't use apply_filters since the field types are 
     * standardized by content, rather than field name
     * ------------------------------------------------------------------------
     */

    /**
     * Display standard errors in the Settings API.
     * 
     * @since    1.0.0
     * @access   public
     */
    public function options_show_errors () {
        settings_errors();
    }

    /**
     * Validate Button field. 
     * 
     * We DO NOT do validation for buttons, since 
     * they interact with JavaScript only, not Ajax.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_button_field ( $in ) {
        $out = $in;
        return $out;
    }

    /**
     * Validate hidden (text-like) field. Used when the Options API saves a value.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_hidden_field ( $in ) {
        $out = $in = sanitize_text_field( trim( $in ) );
        if ( ! $out || empty( $out ) || strlen( $out ) != strlen( $in ) ) {
            add_settings_error(
                $this->option_group,
                'hidden_validation_error',
                '<span style="color:red">Error:</span> Hidden field ('.$out.'), contact Administrator',
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate text field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_text_field ( $in ) {
        $out = $in = trim( sanitize_text_field( $in ) );
        return $out;
    }

    /**
     * Validate phone field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_tel_field ( $in ) {
        $out = $in = trim( sanitize_text_field( $in ) );
        if( ! $this->fields->is_phone( $out ) ) {
            add_settings_error(
                $this->option_group,
                'phone_validation_error',
                '<span style="color:red">Error:</span> ' . __( 'Invalid Phone (extra characters?): ('.$out.'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate postal code.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_postal_field ( $in ) {

        $out = $in = trim( sanitize_text_field ( $in ) );
        if ( ! $this->fields->is_postal( $out ) ) {
            add_settings_error(
                $this->option_group,
                'phone_validation_error',
                '<span style="color:red">Error: </span>' . __( 'Invalid Postal Code, extra characters: ('.$out.'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate email field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_email_field ( $in ) {
        $out = $in = sanitize_email( trim( $in ) );
        if ( ! $this->fields->is_email( $out ) ) {
            add_settings_error(
                $this->option_group,
                'email_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Email ('. sanitize_email( $in ) .'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * validate URL field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_url_field ( $in ) {
        $in = sanitize_text_field( trim( $in ) );
        $out = esc_url_raw( $in, [ 'http', 'https' ] );
        $final_url =  $this->fields->get_url_status( $out );
        if ( $out != $in || $out != $final_url['value'] ) {
            add_settings_error(
                $this->option_group,
                'url_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid URL (' ) . $final_url['err'] . $final_url['value'] . $final_url['class'] . __( '), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate the value of a field as an integer.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_int_field ( $in ) {
        $out = sanitize_text_field( trim( $in ) );
        if ( ! $this->fields->is_int( $out ) ) {
            add_settings_error(
                $this->option_group,
                'int_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Integer ('. $out .'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate the value of a field as an integer.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_float_field ( $in ) {
        $out = sanitize_text_field( trim( $in ) );
        if ( ! $this->fields->is_float( $out ) ) {
            add_settings_error(
                $this->option_group,
                'float_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Float ('. $out .'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate textarea.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_textarea_field ( $in ) {
        $out = $in = sanitize_text_field( trim( $in ) );
        return $out;
    }

    /**
     * Validate checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validate input data
     */
    public function validate_checkbox_field ( $in ) {
        $out = $in = sanitize_text_field( trim( $in ) );
        return $out;
    }

    /**
     * Validate date.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_date_field ( $in ) {

        $out = $in = sanitize_text_field( trim( $in ) );
        if ( ! $this->fields->is_date( $out ) ) {
            add_settings_error(
                $this->option_group,
                'date_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Date ('. $out .'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate time.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_time_field ( $in ) {

        $out = $in = sanitize_text_field( trim( $in ) );
        if ( ! $this->fields->is_time( $out ) ) {
            add_settings_error(
                $this->option_group,
                'time_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Time ('. $out .'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate duration.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_duration_field ( $in ) {
        $out = $in = sanitize_text_field( trim( $in ) );
        if ( ! $this->fields->is_number( $out ) ) {
            add_settings_error(
                $this->option_group,
                'duration_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Duration ('. $out .'), please re-enter' ),
                'error'
            );
        }
        return $out;
    }

    /**
     * Validate datalist.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_datalist_field ( $in ) {
        $out = $in = sanitize_text_field( trim( $in ) );
        return $out;
    }

    /**
     * Validate <select> single (pulldown menu) field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_select_single_field ( $in ) {
        $out = $in = sanitize_text_field( trim( $in ) );
        return $out;
    }

    /**
     * Validate a <select multi...> field, assuming nested arrays.
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $input  value
     * @return   array    $out    validated input data
     */
    public function validate_select_multiple_field ( $in ) {
        $out = $in;
        if ( ! is_array( $in ) ) return $in;
        else {
            foreach ( $in as $key => $value ) {
                $out[ $key ] = $value;
                if ( is_array( $out[ $key ] ) ) {
                    foreach ( $out[ $key ] as $key1 => $value1 ) {
                        $out[ $key ][ $key1 ] = sanitize_text_field( $value1 );
                    }
                }
            }
        }
        return $out;
    }

    /**
     * Validate multi-select dropdown for available Custom Post Types.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    data input into the field.
     * @return   mixed      $out   validated input data
     */
    public function validate_cpt_field ( $in ) {
        return $this->validate_select_multiple_field( $in );
    }

    /**
     * Validate multi-select dropdown for available Categories.
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_cat_field ( $in ) {
        return $this->validate_select_multiple_field( $in );
    }

    /**
     * Validate repeater field.
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_repeater_field ( $in ) {
        return $this->validate_select_multiple_field( $in );
    }

    /**
     * Validate image field URL.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_image_field ( $in ) {
        return $this->validate_url_field( $in );
    }

    /**
     * Validate audio field data.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_audio_field ( $in ) {
        return $this->validate_url_field( $in );
    }

    /**
     * Validate video field data.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    data input into the field.
     * @return   mixed     $out   validated input data
     */
    public function validate_video_field ( $in ) {
        return $this->validate_url_field( $in );
    }

    /**
     * Validate post warning field. Appears in plugin options, 
     * is filled is a metabox edit or rendering of Schema resulted in 
     * a problem in a field, e.g. an Event expiration date is in the past
     * 
     * @since    1.0.0
     * @access   public
     * $param    string     $in   data input into the field
     * @return   mixed      $in   no validation
     */
    public function validate_post_warning_field ( $in ) {
        return $in;
    }

} // end of class