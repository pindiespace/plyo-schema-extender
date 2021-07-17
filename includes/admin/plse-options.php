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
     * Store reference to shared PLSE_Util class.
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
     * CSS panel style
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $panel_class
     */
    private $panel_class = 'plyo-schema-extender-box';

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct () {

        // utilities
        $this->init = PLSE_Init::getInstance();

        // shared field definitions, Schema data is loaded separately
        $this->options_data = PLSE_Options_Data::getInstance();

        // add the menu
        //setup_options_page for rendering
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
     * Setup for the options UI (tabbed) on the page
     * 'admin_init' hook
     * 
     * @since    1.0.0
     * @access   public
     */
    public function setup_options () {

        // load fields
        $this->admin_init_fields();

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
                $this->options_js_name,
                $this->options_data->get_options_fields()
            );

        } );

    }

    /**
     * ---------------------------------------------------------------------
     * CREATE OPTIONS PAGE
     * ---------------------------------------------------------------------
     */

    /**
     * Set up the options menu in WP-Admin.
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
     * Read the Schema array, create the necessary number of tabs
     */
    public function setup_tabs () {

        //$tab_href = 'content-tab1';
        $tab_href = $this->options_data->get_tabsel();
        $schema = $this->options_data->get_options();
        $count = 1;

        $tabstring = '<nav class="plyo-schema-extender-tab-nav" role="navigation">' . "\n" . "<ul>\n";

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
     * Setup group or panels of options by Schema
     * TODO:
     * TODO:
     * TODO:
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

                // TODO:
                // TODO: WRONG DIV NUM CAUSING FOOTER TO JUMP
                // TODO:

                // show the data group fields (which can be hidden with checkbox)
                echo '<!--inside a mask-->';
                echo '<div class="plse-panel-mask" style="display:' . $panel_display . '">';
                echo '<div>' ."\n";
                echo "TABSEL IS A:" . $tab_href;
                do_settings_sections( $this->options_data->get_section_slug( $key ) );
                echo '</div>';
                echo '</div>' . "\n";

                ////do_settings_sections( $this->options_data->get_section_slug( $key ) );
                ////echo 'count2:' . $count;

                echo '</div>' . "\n"; // panel style
                
                echo '</div>'; // content-tabXXX
                $count++;

            }

        }

        echo '</div>'; // content-tabs

    }

    /**
     * Set up the default options page
     * Called by 'admin_menu' hook -> setup_options_menu -> add_menu_page()
     * Fields are added separately by 'admin_init' hook -> admin_settings(), 
     * using the same section slug.
     * 
     */
    public function setup_options_page () {

        // css style for panel
        $panel_style = 'plyo-schema-extender-box';

        // define the default panel style for toggling active/inactive
        $panel_display = 'none';

        // default active tab href
        $tab_href = 'content-tab1';

        // wraps the whole page
        echo '<div class="plyo-schema-extender">' . "\n";
        // page headers
        echo '<div class="plyo-schema-extender-row">' . "\n";
            echo '<div class="plyo-schema-extender-col">' . $this->init->get_logo() . '</div>';
            echo '<div class="plyo-schema-extender-col plyo-schema-extender-valign">';
            echo '<h2 class="plyo-schema-extender-h2">' . PLSE_SCHEMA_EXTENDER_NAME . '</h2>'; 
                echo '<p>' . PLSE_SCHEMA_OPTIONS_DESCRIPTION . '</p>';
                echo '</div>';
            echo '</div>' . "\n";


                 // begin the form
            echo '<form id="plyo-schema-extender-form" method="post" action="options.php">';

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
            do_settings_sections( $this->options_data->get_section_slug( 'HIDDEN' ) );
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
     * Initialize all the fields in the admin window using WP Settings API. The
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

            // get the toggle for Schema tabs (not used in the 'general' tab)
            $state = $this->admin_settings_toggle( $toggles[ $key ] );

            // initialize the section (e.g. tab panel)
            $this->init_section( $section );

            $fields = $section['fields'];

            // add fields to the section
            foreach ( $fields as $field ) {

                switch ( $field['type'] ) {

                    case PLSE_INPUT_TYPES['IMAGE']:
                        $this->init_field( $section, $field, $state, $field['width'], $field['height'] );
                        break;

                    default: 
                        $this->init_field( $section, $field, $state );
                        break;

                }

            }

        }

    }


    /**
     * Render a section in the options page, containing multiple fields.
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
     * @param    array    $p1       additional parameter, e.g. 'width' for IMG
     * @param    array    $p2       additional parameter, e.g. 'height' for IMG
     */
    public function init_field ( $section, $field, $state = '', $p1 = '', $p2 = '' ) {

        $render_callback = 'render_' . $field['type'] . '_field';
        $validation_callback = 'validate_' . $field['type'] . '_field';

        // add_settings_field prevents script loading!!!!!!!!!!!!!!

        // add the field
        add_settings_field(
            $field['slug'],
            $field['title'],
            [ $this, $render_callback ], // RENDER CALLBACK
            $section['section_box'],
            $section['section_slug'], // label for general settings section
            [ $field['slug'], $state, $p1, $p2 ] // callback function passed additional parameters
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

    /**
     * Set up tabs UI
     */
    public function setup_options_tabs () {
        echo "IN SETUP OPTIONS TABS.........";
    }

    /**
     * Create individual fields for plugin options.
     */
    public function setup_options_fields () {
        echo "IN SETUP OPTIONS FIELDS............";
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
     * @param    string    $slug name of field
     */
    public function render_hidden_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'Descriptive name of type of schema.') . '</label>';
        echo '<input style="display:block;" type="hidden" id="' . $slug . '" name="' . $slug . '" value="' . $option . '" />';	
    }

    /**
     * Render a standard text field.
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, plus its state
     */
    public function render_text_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'Descriptive text.') . '</label>';
        echo '<input style="display:block;" type="text" id="' . $slug . '" name="' . $slug . '" value="' . $option . '" />';	
    }

    /**
     * Render a date field.
     * @since    1.0.0
     * @access   public
     * @param    array    $args name of field, plus its state
     */
    public function render_date_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'Descriptive text.') . '</label>';
        echo '<input style="display:block;" type="date" id="' . $slug . '" name="' . $slug . '" value="' . $option . '" />';	
    }

    /**
     * Render a postal field.
     * @since    1.0.0
     * @access   public
     * @param    string    $slug name of field
     */
    public function render_postal_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'Formatted Postal Code.') . '</label>';
        echo '<input style="display:block;" type="text" id="' . $slug . '" name="' . $slug . '" value="' . $option . '" />';	
    }

    /**
     * Render phone input field
     * @since    1.0.0
     * @access   public
     * @param    string    $slug name of field
     */
    public function render_phone_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'Phone format: xxx-xxx-xxxx' ) . '</label>';
        echo '<input style="display:block;" type="tel" id="' . $slug . '" name="' . $slug . '" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" value="' . esc_html( $option ) . '" />';	
    }

    /**
     * render email input field
     * @since    1.0.0
     * @access   public
     * @param    string    $slug    name of field
     */
    public function render_email_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'Email format: xxxx@novyunlimited.com' ) . '</label>';
        echo '<input style="display:block;" type="email" id="' . $slug . '" name="' . $slug . '" pattern=".+@novyunlimited.com" size="40" value="' . esc_html( $option ) . '" />';	
    }

    /**
     * Render URL input field
     * @since    1.0.0
     * @access   public
     * @param    string    $slug name of field
     */
    public function render_url_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option( $slug );
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'URL format: https://domain/page' ) . '</label>';
        echo '<input style="display:block;" type="url" id="' . $slug . '" name="' . $slug . '" pattern="https://.*" size="60" value="' . esc_url( $option ) . '" required/>';	
    }


    /**
     * Render checkbox
     * @since    1.0.0
     * @access   public
     * @param    string    $slug name of field
     */
    public function render_checkbox_field ( $args ) {
        $slug = $args[0];
        $state = $args[1];
        $option = get_option ( $slug );
        ///////echo "ON for slug: " . $slug . " IS:" . $option;
        echo '<label style="display:block;" for="' . $slug . '">' . esc_html_e( 'If checked, Schema applied to the following Custom Post Types and Categories' ) . '</label>';
        echo '<input style="display:block;" type="checkbox" id="' . $slug . '" name="' . $slug . '"';
        if ( $option == $this->init->get_checkbox_on() ) echo ' CHECKED';
        echo ' />';	
    }

    /**
     * Handle multiple-select dropdown for Custom Post Type. Stores multiple entries for 
     * Custom Post Types used to assign Schema for specific CPTs.
     * Example: <select name='plugin_options[clusters][]' multiple='multiple'>
     * {@link https://stackoverflow.com/questions/17987233/how-can-i-set-and-get-the-values-of-a-multiple-select-with-the-wordpress-setting}
     * @since    1.0.0
     * @access   public
     * @param    string    $slug    slug for option storage
     * 
     * TODO:
     * TODO: confirm Custom Post Type still exists
     * TODO: if it does not, remove the stored option for the CPT
     * TODO:
     * 
     */
    public function render_cpt_field ( $args ) {
        // no dropdown if no Custom Post Types
        $cpts = $this->init->get_all_cpts();
        if ( ! $cpts ) return;

        $slug = $args[0];
        $state = $args[1];
        $options = get_option( $slug );
        $dropdown = '<select multiple name="' . $slug .'[' . $slug . '][]" class="cpt-dropdown" >' . "\n";

        foreach ( $cpts as $cpt ) {
            $dropdown .= '<option value="' . $cpt->rewrite['slug'] . '" ';
            // highlight stored options in dropdown
            if ( is_array( $options ) ) {
                foreach ( $options as $opt ) {
                    if ( is_array( $opt ) ) {
                        foreach ( $opt as $subopt ) {
                            if ( $subopt == $cpt->rewrite['slug'] ) {
                                $dropdown .= 'selected';
                            }
                        }
                    }
                }
            }
            $dropdown .= '>' . $cpt->label . '</option>' . "\n";
        }
        $dropdown .= '</select>' . "\n";

        echo $dropdown;

    }

    /**
     * Handle multi-select dropdown for categories
     * @since    1.0.0
     * @access   public
     * @param    string    $slug slug for option storage
     */
    public function render_cat_field ( $args ) {
        // no dropdown if categories don't exist
        $cats = $this->init->get_all_cats();
        if ( ! $cats ) return;

        $slug = $args[0];
        $state = $args[1];
        $options = get_option( $slug );
        $dropdown = '<select multiple name="' . $slug .'[' . $slug . '][]" class="cpt-dropdown">' . "\n";
        foreach ( $cats as $cat ) {
            $dropdown .= '<option value="' . $cat->slug . '" ';
            // highlight stored options in dropdown
            if ( is_array( $options ) ) {
                foreach ( $options as $opt ) {
                    if ( is_array( $opt ) ) {
                        foreach ( $opt as $subopt ) {
                            if ( $subopt == $cat->slug ) {
                                $dropdown .= 'selected';
                            }
                        }
                    }
                }
            }
            $dropdown .= '>' . $cat->name . '</option>' . "\n";
        }
        $dropdown .= '</select>' . "\n";

        echo $dropdown;
    }


    /**
     * render an upload image field
     * @since    1.0.0
     * @access   public
     * @param    array    $args  arguments needed to render image field
     */
    public function render_image_field ( $args ) {

        $slug = $args[0];
        $state = $args[1];
        $width = $args[2];
        $height = $args[3];
        $option = esc_attr( get_option ( $slug ) );

        // current image (default to plugin blank if not set)
        $plse_init = PLSE_Init::getInstance();

        // show the image specified by the URL accessed via $slug
        if ( $option ) {
            echo '<img id="' . $slug . '-img-id" src="' . $option . '" width="128" height="128">';
        } else {
            echo '<img id="'. $slug . '-img-id" src="' . $plse_init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }

        // media library button (ajax), $slug is the key, $option if the value of the image URL
        echo '<input type="text" name="' . $slug . '" id="' . $slug . '" name="' . $slug . '" value="' . $option . '">';

        // button used by WP mediaUploader
        echo '<input type="button" class="button plse-media-button" data-media="'. $slug . '" value="Upload Image" />';

    }

    /*
     * ------------------------------------------------------------------------
     * DATA VALIDATION (AND SANITIZE) METHODS
     * ------------------------------------------------------------------------
     */

    /**
     * Validate hidden (text-like) field.
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_hidden_field ( $in ) {
        $out = $in = trim( $in );
        $out = sanitize_text_field ( $out );
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
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_phone_field ( $in ) {
        $out = $in = trim ( $in );
        $out = preg_replace( '/[\s\#0-9_\-\+\/\(\)\.]/', '', $in );
        if ( strlen( $out ) ) {
            $out = $in = sanitize_text_field( $in );
            add_settings_error(
                $this->option_group,
                'phone_validation_error',
                '<span style="color:red">Error:</span> Invalid Phone, extra characters: ('.$out.'), please re-enter',
                'error'
            );
        } else {
            $out = $in;
        }
        return apply_filters( [ $this, 'validate_phone_field' ], $out, $in );
    }

    /**
     * Validate postal code
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_postal_field ( $in ) {
        $out = $in = trim( $in );
        $out = preg_replace( '/[\s\-A-Za-z0-9]/', '', $in );
        if ( strlen( $out ) ) {
            $out = $in = sanitize_text_field( $in );
            add_settings_error(
                $this->option_group,
                'phone_validation_error',
                '<span style="color:red">Error:</span> Invalid Postal Code, extra characters: ('.$out.'), please re-enter',
                'error'
            );
        } else {
            $out = $in;
        }
        return apply_filters( [ $this, 'validate_postal_field' ], $out, $in );
    }

    /**
     * Validate email field
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_email_field ( $in ) {
        $out = $in  = sanitize_email( $in );
        if ( ! is_email( $out ) ) {
            add_settings_error(
                $this->option_group,
                'email_validation_error',
                '<span style="color:red">Error:</span> Invalid Email ('. sanitize_email( $in ) .'), please re-enter',
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_email_field' ], $out, $in );
    }

    /**
     * validate URL field
     */
    public function validate_url_field ( $in ) {
        $out = $in = trim ( $in );
        $out = esc_url_raw( $in , [ 'http', 'https' ] );
        if ( $out != $in ) {
            $out = $in = sanitize_text_field( $in );
            add_settings_error(
                $this->option_group,
                'url_validation_error',
                '<span style="color:red">Error:</span> Invalid URL ('.$out.'), please re-enter',
                'error'
            );
        }
        return apply_filters( [ $this, 'validate_url_field' ], $out, $in );
    }


    /**
     * Validate checkbox.
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_checkbox_field ( $in ) {
        $out = $in = sanitize_text_field(trim( $in ) );
        return apply_filters( [ $this, 'validate_checkbox_field' ], $out, $in );
    }

    /**
     * Validate multi-select dropdown for available Custom Post Types.
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_cpt_field ( $in ) {
        $out = $in;
        // sanitize_key()
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
        // Return the array processing any additional functions filtered by this action
        return apply_filters( [ $this, 'validate_multi_cat_field' ], $out, $in );
    }

    /**
     * Validate file upload data (and let WP do the upload)
     * @since    1.0.0
     * @access   public
     * @param    $string    $in    data input into the field.
     * @return   mixed      use apply_filters to return $in and $out
     */
    public function validate_image_field ( $in ) {
        $out = $in;
        // Return the array processing any additional functions filtered by this action
        return apply_filters( [ $this, 'validate_image_field' ], $out, $in );
    }

    public function validate_int_field ( $in ) {
        // TODO:
        return $in;
    }

    public function validate_float_field ( $in ) {
        // TODO:
        return $in;
    }

    /**
     * Display standard errors.
     */
    public function options_show_errors () {
        settings_errors();
    }

} // end of class