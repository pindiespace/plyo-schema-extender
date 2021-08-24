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
     * @return   PLSE_Base    $self__instance
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

            // use PLSE_Options to inject variables into JS specifically for PLSE_Meta media library button clicks 
            $this->init->load_js_passthrough_script( 
                $script_label,
                $this->options_js_name,
                $this->options_data->get_options_fields()
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
            //'PLYO',
            'manage_options',          // capability
            PLSE_SCHEMA_EXTENDER_SLUG, // menu slug
            [ $this, 'setup_options_page' ], // render callback
            'dashicons-networking' // schema-like hierarchy icon
        );

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

        $schema = $this->options_data->get_options();
        $toggles = $this->options_data->get_toggles();
        $tab_href = $this->options_data->get_tabsel();
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

                // show the data group fields (which can be hidden with checkbox)
                echo '<!--inside a user-clickable accordion style mask-->';
                echo '<div class="plse-panel-mask" style="display:' . $panel_display . '">';
                echo '<div>' ."\n";
                do_settings_sections( $this->options_data->get_section_box_slug( $key ) );
                echo '</div>';
                echo '</div>' . "\n";

                echo '</div>' . "\n"; // panel style
                
                echo '</div>'; // content-tabXXX
                $count++;

            }

        }

        echo '</div>'; // content-tabs

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

        // default active tab href
        ////////////////////////$tab_href = 'content-tab1'; // TODO: DELETE

        // wraps the whole page
        echo '<div class="plyo-schema-extender">' . "\n";

        // page headers
        echo '<div class="plse-options-row">' . "\n";
            echo '<div class="plse-options-col">' . $this->init->get_logo() . '</div>';
            echo '<div class="plse-options-col plse-options-valign">';
            echo '<h2 class="plse-options-h2">' . PLSE_SCHEMA_EXTENDER_NAME . '</h2>'; 
                echo '<p>' . PLSE_SCHEMA_OPTIONS_DESCRIPTION . '</p>';
                echo '</div>';
            echo '</div>' . "\n";


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

        echo $this->setup_tabs();

        // panels
        // TODO: KLUDGE
        $this->setup_panels();

        echo '</div></div>'; // end of container, row, col-md-12

        submit_button(); 
    
        echo '</form>';
        echo '</div>' . "\n"; // end of wrapper
        echo '</div>' . "\n"; // end of big row

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
             */
            $state = $this->admin_settings_toggle( $toggles[ $key ] );

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
         * Add additional parameters via the $args array.
         * Different field types pass different elements of the $field object, 
         * depending on how they are rendered in the UI.
         */
        $args = $field;
        $args['state'] = $state;

        // add the field
        add_settings_field(
            $field['slug'],
            $field['description'] . ':', // appears to the left of the field on options page
            [ $this, $render_callback ], // field rendering function callback
            $section['section_box'], // slug for section box
            $section['section_slug'], // label for general settings section
            $args
        );

        // register setting and validation callback
        register_setting(
            $this->option_group, // overall option group
            $field['slug'],      // slug for input field
            // third argument is callback for validation function
            [ $this, $validation_callback ] // VALIDATION CALLBACK
        );

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
     * @param    array    $args name of field, state, additional properties
     */
    public function render_hidden_field ( $args ) {
        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );

        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'Descriptive name of type of Schema.') . '</label>';
        echo '<input type="hidden" id="' . $slug . '" name="' . $slug . '" value="' . $option . '" />';	
    }

    /**
     * Render a button.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args    arguments neeeded to render the field
     * @param    string   $value   serialized or unserialized field value
     */
    public function render_button_field ( $args ) {

        // option value (from a different field) that affects this control 

        // use variable variable '$$' to make field value into a class variable
        if ( ! is_array( $this->{$args['slug_dependent']} ) ) {
            $disabled='disabled';
            $msg = $args['msg_disabled'];
        } else {
            $disabled = '';
            $msg = $args['msg_enabled'];
        }

        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $label = esc_html( $args['label'] );
        $title = esc_html( $args['title'] );

        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( $label ) . '</label>';
        echo '<input type="button" style="padding:2px 6px 2px 6px;" title="' . $title . '" id="' . $slug . '" name="' . $slug . '" value="' . $title . '" ' . $disabled . '>';
        echo '<span class="plse-options-msg">' . $msg . '</span>';

    }

    /**
     * Render textlike fields.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array $args name of field, state, additional properties
     * @return   string   $option    the stored option value, used to validate text field subtypes
     */
    public function render_simple_field ( $args ) {

        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );
        $type = $args['type'];
        $list_id = $args['list_id'];

        $option = get_option( $slug );
        echo '<label class="plse-option-description" for="' . $slug . '">' . $label . '</label>';
        echo '<input title="'. $title .'" class="plse-option-input" type="' . $type . '" list="' . $list_id . '" id="' . $slug . '" name="' . $slug . '" size="40" value="' . $option . '" />';	

        return $option; // for error checks
    }

    /**
     * Render a standard text field.
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_text_field ( $args ) { 
        $option = $this->render_simple_field( $args );
    }

    /**
     * Render a datalist.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args    field arguments
     */
    public function render_datalist_field ( $args ) {

        $args['type'] = 'text';
        $slug = sanitize_key( $args['slug'] );
        $option_list = $args['option_list'];

        // select and load datalists ($option_list) here
        if ( isset( $option_list ) ) {
            if ( is_array( $option_list ) ) {
                // apply custom array of options specified in the field
                $args['list_id'] = $slug . '-data'; // NOTE: wrong method name causes save to FAIL
                echo $this->datalists->get_datalist( $option_list, $args['list_id'] );
            }
            else {
                // apply a standard datalist
                $method = 'get_' . $option_list . '_datalist';
                $args['list_id'] = 'plse-' . $option_list . '-data';

                // dynamically generate render method name from PLSE_INPUT_TYPES[]
                if ( method_exists( $this->datalists, $method ) ) {
                    echo $this->datalists->$method();
                }
            }
        }

        $option = $this->render_simple_field( $args );

    }

    /**
     * Render a postal field.
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_postal_field ( $args ) {
        $option = $this->render_simple_field( $args );
        if ( ! $this->init->is_postal( $option ) ) {
            echo $this->init->add_status_to_field( __( 'Invalid postal address') );
        }
    }

    /**
     * Render phone input field
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_tel_field ( $args ) {
        $option = $this->render_simple_field( $args );
        if ( ! $this->init->is_phone( $option ) ) {
            echo $this->init->add_status_to_field( __( 'Invalid phone') );
        }
    }

    /**
     * render email input field
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_email_field ( $args ) {
        $option = $this->render_simple_field( $args );
        if ( ! $this->init->is_email( $option ) ) {
            echo $this->init->add_status_to_field( __( 'Invalid email' ) );
        }
    }

    /**
     * Render URL input field
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_url_field ( $args ) {
        $option = $this->render_simple_field( $args );
        if ( ! $this->init->get_final_url( $option ) ) {
            echo $this->init->add_status_to_field( __( 'Invalid URL') );
        }
    }

    /**
     * Render a textarea field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_textarea_field ( $args ) {

        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );
        $rows = $args['rows'];
        $cols = $args['cols'];

        $option = get_option( $slug );
        echo '<label class="plse-option-description" for="' . $slug . '">' . $label . '</label>';
        echo '<textarea title="' . $title . '" id="' . $slug . '" name="' . $slug .'" rows="' . $rows . '" cols="' . $cols . '"></textarea>';

    }

    /**
     * Render a date field.
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_date_field ( $args ) {

        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );

        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . $label . '</label>';
        echo '<input style="display:block;" type="date" id="' . $slug . '" name="' . $slug . '" value="' . $option . '" />';	

    }

    /**
     * Render checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_checkbox_field ( $args ) {

        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );

        $option = get_option( $slug );
        echo '<div class="plse-option-ctl-highlight">';
        echo '<label style="display:block;margin-bottom:6px;" for="' . $slug . '">' . $label . '</label>';
        echo '<input style="display:block;" type="checkbox" id="' . $slug . '" name="' . $slug . '"';
        if ( $option == $this->init->get_checkbox_on() ) echo ' CHECKED';
        echo ' />';	
        echo '</div>';

    }

    /**
     * Render single-choice select field (dropdown menu).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_select_single_field ( $args ) {

        // select fields need an option list
        $option_list = $args['option_list'];
        if ( ! $option_list ) return;

        // for single fields, we want the datalist array (keys and values reversed), not a datalist
        if ( ! is_array( $option_list ) ) {
           $option_list = $this->datalists->get_rev_arr( $option_list ); 
        }
 
        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );

        $option = get_option( $slug ); // selected, a single value
        $dropdown = '<div class="plse-option-select"><select title="' . $title . '" name="' . $slug . '" class="plse-option-select-dropdown" >' . "\n";
        foreach ( $option_list as $key => $opt ) {
            $dropdown .= '<option value="' . $opt . '" ';
            if ( $option == $opt ) {
                $dropdown .= 'selected';
            }
            $dropdown .= '>' . $key . '</option>' . "\n";
        }
        $dropdown .= '</select>' . "\n";

        // add formatting text
        $dropdown .= '<label class="plse-option-select-description" for="' . $slug . '">' . $label . '</label>';
        $dropdown .= '</div>';

        echo $dropdown;

    }

    /**
     * Select multi, with prebuilt option list (scrolling list).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_select_multiple_field ( $args ) {
 
        $option_list = $args['option_list'];
        if ( ! $option_list ) return;

        if ( ! is_array( $option_list ) ) {
            // TODO: use a PLSE_Datalist
        }
        
        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );

        $options = get_option( $slug );

        // get the actual options out of their enclosing array
        if ( is_array( $options ) ) $options = $options[ $slug ];

        // note $slug[], which specifies multiple values stored in one option.
        $dropdown = '<div class="plse-option-select"><select multiple name="' . $slug .'[' . $slug . '][]" class="plse-option-select-dropdown" >' . "\n";

        foreach ( $option_list as $key => $option ) {
            $dropdown .= '<option title="' . $title . '" value="' . $option . '" ';
            // highlight stored options in dropdown
            if ( is_array( $options ) ) {
                foreach ( $options as $opt ) {
                    if ( $option == $opt ) {
                        $dropdown .= 'selected="selected"';
                    }
                }
            }
            $dropdown .= '>' . $key . '</option>' . "\n";
        }
        $dropdown .= '</select>' . "\n";

        // add the field label
        $dropdown .= '<label class="plse-option-select-description" for="' . $slug . '">' . $label . '<br>' . __( '(CTL-Click to deselect)') . '</label>';
        $dropdown .= '</div>';

        echo $dropdown;

    }

    /**
     * Handle multiple-select scrolling list for Custom Post Type. Stores multiple entries for 
     * Custom Post Types used to assign Schema for specific CPTs.
     * Example: <select name='plugin_options[clusters][]' multiple='multiple'>
     * 
     * We use a custom render due to the complexity of the returned CPT array.
     * 
     * {@link https://stackoverflow.com/questions/17987233/how-can-i-set-and-get-the-values-of-a-multiple-select-with-the-wordpress-setting}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_cpt_field ( $args ) {

        // no dropdown if no Custom Post Types
        $cpts = $this->init->get_all_cpts(); // get all potential selections
        if ( ! $cpts ) {
            echo __( 'No Custom Post Types are defined yet.' );
            return;
        }
        $args['option_list'] = $this->init->get_option_list_from_cpts( $cpts );
        $this->render_select_multiple_field( $args );
    }

    /**
     * Handle multi-select scrolling list for categories.
     * 
     * We use a custom render due to the complexity of the $cat array
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_cat_field ( $args ) {

        // no dropdown if categories don't exist
        $cats = $this->init->get_all_cats();
        if ( ! $cats ) {
            echo __( 'No categories are defined yet.' );
            return;
        }
        $args['option_list'] = $this->init->get_option_list_from_cats( $cats );
        $this->render_select_multiple_field( $args );
    }


    /**
     * render an upload image field
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, state, additional properties
     */
    public function render_image_field ( $args ) {

        $slug = sanitize_key( $args['slug'] );
        $state = $args['state'];
        $title = esc_html( $args['title'] );
        $label = esc_html( $args['label'] );
        $width = $args['width'];
        if ( ! isset( $width ) ) $width = '128';
        $height = $args['height'];
        if ( ! isset( $height ) ) $height = '128';

        $option = esc_attr( get_option ( $slug ) );

        // adjust shorter dimension of image so it scales properly
        $image_properties = $this->init->get_image_properties_from_url ( $option );

        // find the largest side of the image, keep that value, and proprotionaly scale the smaller side
        if ( intval( $width ) > intval( $height ) ) {
            $ratio = $width / $image_properties['width'];
            $height = $image_properties['height'] * $ratio;
        } else {
            $ratio = $height / $image_properties['height'];
            $width = $image_properties['width'] * $ratio;
        }

        // image control
        echo '<div class="plse-option-wrapper">';
        echo '<div class="plse-meta-image-col">';

        // show the image specified by the URL accessed via $slug
        if ( $option ) {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="' . $slug . '-img-id" src="' . $option . '" width="' . $width . '" height="' . $height . '">';
        } else {
            echo '<img title="' . $title .'" class="plse-upload-img-box" id="'. $slug . '-img-id" src="' . $this->init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }
        echo '</div>';
        echo '<div class="plse-meta-upload-col">';

        echo '<div>' . __( 'Image URL in WordPress:' ) . '</div>';
        echo '<div>';

        // media library button (ajax), $slug is the key, $option if the value of the image URL
        echo '<input type="text" name="' . $slug . '" id="' . $slug . '" name="' . $slug . '" value="' . $option . '">';

        // button used by WP mediaUploader
        echo '<label for="' . $slug . '">';
        echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug . '" value="Upload Image" />';
        echo '</label>';
        echo '</div></div>';
        echo '</div>';

    }

    public function render_audio_field () {
        echo __( 'audio field not supported in this version' );
    }

    public function render_video_field () {
        echo __( 'video field not supported in this version' );
    }

    public function render_int_field () {
        echo __( 'int field not supported in this version' );
    }

    public function render_float_field () {
        echo __( 'float field not supported in this version' );
    }

    /*
     * ------------------------------------------------------------------------
     * DATA VALIDATION (AND SANITIZE) METHODS
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
     * Validate hidden (text-like) field. Used when the Options API saves a value.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
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
        return apply_filters( [ $this, 'validate_hidden_field' ], $out, $in );
    }


    /**
     * Validate phone field, after WooCommerce method, check for string length 
     * after stripping all valid characters.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_phone_field ( $in ) {
        // sanitize
        $out = $in = trim( sanitize_text_field( $in ) );
        if( ! $this->init->is_phone( $out ) ) {
            add_settings_error(
                $this->option_group,
                'phone_validation_error',
                '<span style="color:red">Error:</span> ' . __( 'Invalid Phone (extra characters?): ('.$out.'), please re-enter' ),
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_phone_field' ], $out, $in );

    }

    /**
     * Validate postal code.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_postal_field ( $in ) {
        $out = $in = trim( sanitize_text_field ( $in ) );
        if ( ! $this->init->is_postal( $out ) ) {
            add_settings_error(
                $this->option_group,
                'phone_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Postal Code, extra characters: ('.$out.'), please re-enter' ),
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_postal_field' ], $out, $in );
    }

    /**
     * Validate email field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_email_field ( $in ) {
        $out = $in = sanitize_email( trim( $in ) );
        if ( ! is_email( $out ) ) {
            add_settings_error(
                $this->option_group,
                'email_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Email ('. sanitize_email( $in ) .'), please re-enter' ),
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_email_field' ], $out, $in );
    }

    /**
     * validate URL field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_url_field ( $in ) {
        $in = sanitize_text_field( trim( $in ) );
        $out = esc_url( $in, [ 'http', 'https' ] );
        if ( $out != $in || ! $this->init->get_final_url( $out) ) {
            add_settings_error(
                $this->option_group,
                'url_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid URL ('.$out.'), please re-enter' ),
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_url_field' ], $out, $in );
    }


    /**
     * Validate checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_checkbox_field ( $in ) {
        $out = $in = sanitize_text_field( trim( $in ) );
        // TODO: validation
        return apply_filters( [ $this, 'validate_checkbox_field' ], $out, $in );
    }

    /**
     * Validate multi-select dropdown for available Custom Post Types.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_cpt_field ( $in ) {
        $out = $in;
        // TODO: sanitize
        // sanitize_key() or slug
        // return empty string if sanitize fails
        return apply_filters( [ $this, 'validate_multi_cpt_field' ], $out, $in );
    }

    /**
     * Validate multi-select dropdown for available Categories.
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_cat_field ( $in ) {
        $out = $in;
        // TODO: sanitize key or slug
        // Return the array processing any additional functions filtered by this action
        return apply_filters( [ $this, 'validate_multi_cat_field' ], $out, $in );
    }

    /**
     * Validate file upload data (and let WP do the upload).
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_image_field ( $in ) {
        $out = $in;
        // TODO: validate image
        // Return the array processing any additional functions filtered by this action
        return apply_filters( [ $this, 'validate_image_field' ], $out, $in );
    }

    /**
     * Validate the value of a field as an integer.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_int_field ( $in ) {
        $out = sanitize_text_field( trim( $in ) );
        if ( ! is_int( $out ) ) {
            add_settings_error(
                $this->option_group,
                'int_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Integer ('.$out.'), please re-enter' ),
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_int_field' ], $out, $in );
    }

    /**
     * Validate the value of a field as floating-point.
     * 
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_float_field ( $in ) {
        $out = sanitize_text_field( trim( $in ) );
        if ( ! is_float( $out ) ) {
            add_settings_error(
                $this->option_group,
                'float_validation_error',
                '<span style="color:red">Error:</span>' . __( 'Invalid Floating-point number ('.$out.'), please re-enter' ),
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_int_field' ], $out, $in );
    }

} // end of class