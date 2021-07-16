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
class PLSE_Options_Data {

    /**
     * Store reference for singleton pattern.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $instance    static reference to initialized class.
     */
    static private $__instance = null;

    /**
     * Toggle checkbox for Schema on/off in plugin options
     */
    private $options_toggle = array(

        'SERVICE' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-toggle',
            'section_title'   => 'Turn on the Service Schema',
            'section_message' => 'Clicking this checkbox will trigger addition of Service Schema to Custom Post Types and categories listed below',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-toggle-box', // <div> for section, used in display_options_page
            'tab'             => 'content-tab2',

            'fields' => array(

                'used' => array(  // is being used (checkbox)
                    'slug' => 'plse-' . PLSE_SCHEMA_SERVICE . '-used',
                    'title' => 'Use this Schema',
                    'type' => PLSE_INPUT_TYPES['CHECKBOX']
                )

            ),

        ),

        'GAME' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . '-toggle',
            'section_title'   => 'Turn on the Game Schema',
            'section_message' => 'Clicking this checkbox will trigger addition of Game Schema to Custom Post Types and categories listed below',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . '-toggle-box', // <div> for section, used in display_options_page()
            'tab'             => 'content-tab3',

            'fields' => array( // is being used (checkbox)

                'used' => array(
                    'slug' => 'plse-' . PLSE_SCHEMA_GAME . '-used',
                    'title' => 'Use this Schema',
                    'type' => PLSE_INPUT_TYPES['CHECKBOX']
                )

            ),

        ),

        'EVENT' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-toggle',
            'section_title'   => 'Turn on the Event Schema',
            'section_message' => 'Clicking this checkbox will trigger addition of Event Schema to Custom Post Types and categories listed below',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-toggle-box', // <div> for section, used in display_options_page()
            'tab'             => 'content-tab4',

            'fields' => array( // is being used (checkbox)
                'used' => array(
                    'slug' => 'plse-' . PLSE_SCHEMA_EVENT . '-used',
                    'title' => 'Use this Schema',
                    'type' => PLSE_INPUT_TYPES['CHECKBOX']
                )
            )

        ),

        'PRODUCT_REVIEW' => array(

            'fields' => array(
                'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_PRODUCT_REVIEW . '-toggle',
                'section_title'   => 'Turn on the Event Schema',
                'section_message' => 'Clicking this checkbox will trigger addition of Event Schema to Custom Post Types and categories listed below',
                'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_PRODUCT_REVIEW . '-toggle-box', // <div> for section, used in display_options_page()
                'tab'             => 'content-tab4',


                'fields' => array( // is being used (checkbox)
                    'used' => array(
                        'slug' => 'plse-' . PLSE_SCHEMA_PRODUCT_REVIEW . '-used',
                        'title' => 'Use this Schema',
                        'type' => PLSE_INPUT_TYPES['CHECKBOX']
                    )

                )

            )

        )

    );

    /** 
     * information for creating plugin options fields
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $options    global data fields associated with the plugin
     */
    private $options = array(

        'HIDDEN'  => array( // hidden fields
            'section_slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_HIDDEN,
            'section_title' => '', // hidden, so no title
            'section_box'   =>  PLSE_OPTIONS_SLUG . PLSE_SCHEMA_HIDDEN . '-box',
            'tab'           => null,

            'fields' => array(

                'tabsel' => array( // remember tab selection
                    'slug' => PLSE_OPTIONS_SLUG . 'tabsel',
                    'title' => 'Tab selection',
                    'type' => PLSE_INPUT_TYPES['HIDDEN']
                ),

            )

        ),

        'GENERAL' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL,
            'section_title'   => 'General Settings for Schema',
            'section_message' => 'General settings, which provide some addtional fields not in the default Yoast installation',
            'section_box'     =>  PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-box',// <div> for section
            'tab'             => 'content-tabl1',
            'tab_title'       => 'Contact',

            'fields' => array(

                'phone' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-phone-field',
                    'title' => 'Organization Phone (if different from Wordpress Admin):',
                    'type'  => PLSE_INPUT_TYPES['PHONE']
                ),

                'email' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-email-field',
                    'title' => 'Organization Email contact (if different from Wordpress Admin):',
                    'type'  => PLSE_INPUT_TYPES['EMAIL']
                ),
    
                'contact_url' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-contact-url-field',
                    'title' => 'Contact URL for organization on website',
                    'type' => PLSE_INPUT_TYPES['URL']
                )

            )

        ),

        'ADDRESS' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS,
            'section_title'   => 'Local Business Address',
            'section_message' => 'Address providing fields missing from default Yoast installation',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-box',// <div> for section
            'tab'             => 'content-tab1',
            'tab_title'       => 'Address',

            'fields' => array(

                'street' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-street-field',
                    'title' => 'Organization Street Address (uses Yoast Local SEO if present)',
                    'type'  => PLSE_INPUT_TYPES['TEXT']
                ),

                'city' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-city-field',
                    'title' => 'Organization City (uses Yoast Local SEO if present)',
                    'type'  => PLSE_INPUT_TYPES['TEXT']
                ),

                'state' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-state-field',
                    'title' => 'Organization State or region (uses Yoast Local SEO if present)',
                    'type'  => PLSE_INPUT_TYPES['TEXT']
                ),

                'country' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-country-field',
                    'title' => 'Organization Country (uses Yoast Local SEO if present)',
                    'type'  => PLSE_INPUT_TYPES['TEXT']
                ),

                'postal' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-postal-field',
                    'title' => 'Organization Postal Code (uses Yoast Local SEO if present)',
                    'type'  => PLSE_INPUT_TYPES['POSTAL']
                )

            )

        ),

        'SERVICE' => array(

            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE,
            'section_title'   => 'Service Settings for Schema',
            'section_message' => 'Parameters for Service Schema. These are global, and can be over-ridden on individual pages. Adjust individual pages and posts with custom fields.',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-box', // <div> for section, used in display_options_page
            'tab'             => 'content-tab2',
            'tab_title'       => 'Service',

            'fields' => array(

                // cpt dropdown
                'cpt' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . PLSE_CPT_SLUG,
                    'title' => 'Custom Post Types using Service Schema',
                    'type'  => PLSE_INPUT_TYPES['CPT']
                ),

                // cat dropdown
                'cat' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . PLSE_CAT_SLUG,
                    'title' => 'Categories using Service Schema',
                    'type'  => PLSE_INPUT_TYPES['CAT']
                ),

                // service type
                'service_type' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-type-field',
                    'title' => 'Service Type (e.g. "Game PR):"',
                    'type'  => PLSE_INPUT_TYPES['TEXT']
                ),

                'logo' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-logo',
                    'title' => 'Service brand logo or icon (global to site)',
                    'type'  => PLSE_INPUT_TYPES['IMAGE'],
                    'width' => '120',
                    'height'=> '120'
                ),

                'image' => array(
                    'slug'  => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-image',
                    'title' => 'Image of Service (distinct from brand logo, global to site)',
                    'type'  => PLSE_INPUT_TYPES['IMAGE'],
                    'width' => '240',
                    'height'=> '120'
                )

            )

        ),

        'GAME'    => array(

            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME,
            'section_title'   => 'Game Settings for Schema',
            'section_message' => 'Parameters for Game Schema. These apply to all instances of Game Schema loaded. These are global, and can be over-ridden. Adjust individual pages and posts with custom fields.',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . '-box', // <div> for section, used in display_options_page()
            'tab'             => 'content-tab3',
            'tab_title'       => 'Game',

            'fields' => array(

                // cpt
                'cpt' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . PLSE_CPT_SLUG,
                    'title' => 'Custom Post Types using Game Schema',
                    'type' => PLSE_INPUT_TYPES['CPT']
                ),

                // cat
                'cat' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . PLSE_CAT_SLUG,
                    'title' => 'Categories using Game Schema',
                    'type' => PLSE_INPUT_TYPES['CAT']
                ),

                // genre
                'genre' => array(
                    'slug' => 'plse-' . PLSE_SCHEMA_GAME . '-type-field',
                    'title' => 'Game Genre (e.g. "Platformer):"',
                    'type' => PLSE_INPUT_TYPES['TEXT']
                ),

                'image' => array(
                    'slug'  => 'plse-' . PLSE_SCHEMA_GAME . '-image',
                    'title' => 'Image of Game (global to site)',
                    'type'  => PLSE_INPUT_TYPES['IMAGE'],
                    'width' => '240',
                    'height'=> '120'
                )

            )

        ),

        'EVENT'   => array(

            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT,
            'section_title'   => 'Event Settings for Schema',
            'section_message' => 'Parameters for Event Schema. These apply to all instances of Event Schema loaded. These are global, and can be over-ridden. Adjust individual pages and posts with custom fields.',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-box', // <div> for section, used in display_options_page()
            'tab'             => 'content-tab4',
            'tab_title'       => 'Event',

            'fields' => array(
                // cpt
                'cpt' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . PLSE_CPT_SLUG,
                    'title' => 'Custom Post Types using Event Schema',
                    'type' => PLSE_INPUT_TYPES['CPT']
                ),

                // cat
                'cat' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . PLSE_CAT_SLUG,
                    'title' => 'Categories using Event Schema',
                    'type' => PLSE_INPUT_TYPES['CAT']
                )

            )

                ),

    );

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct () {

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Base    $self__instance
     */
    public static function getInstance () {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Options_Data();
        }
        return self::$__instance;
    }

    /**
     * ---------------------------------------------------------------------
     * SCHEMA
     * ---------------------------------------------------------------------
     */

    /**
     * Get Settings API options, grouped by Schema
     */
    public function get_options () {
        return $this->options;
    }

    /**
     * Unwrap individual fields out of the data object
     * 
     * @since    1.0.0
     * @access   public
     * @return   array    array with just the fields, extracted from their groups
     */
    public function get_options_fields () {

        $data = $this->options;

        foreach ( $data as $data_group ) {

            foreach ( $data_group['fields'] as $fields ) {

                $field_list[] = $fields;

            }

        }

        return $field_list;

    }

    /**
     * ask for all data by schema
     */
    public function get_options_by_schema ( $schema_label ) {

        $data = $this->options;

        if ( isset( $data[ $schema_label ] ) ) {
            return $data[ $schema_label ];
        }

        return null;

    }

    /**
     * ---------------------------------------------------------------------
     * SCHEMA TOGGLES
     * ---------------------------------------------------------------------
     */

    public function get_toggles () {
        return $this->options_toggle;
    }

    /**
     * ask for data toggle by schema
     */
    public function get_toggles_by_schema ( $schema_label ) {
        
        $data = $this->options_toggle;

        if ( isset( $data[ $schema_label ] ) ) {
            return $data[ $schema_label ];
        }

        return null;

    }

    /**
     * ---------------------------------------------------------------------
     * CHECK OPTIONS (used by PLSE_Metabox and PLSE_Options)
     * ---------------------------------------------------------------------
     */

    /**
     * Check if plugin options show a Schema is active.
     * User controls by clicking a checkbox
     * 
     */
    public function check_if_schema_active ( $schema_label ) {
        $toggle_slug = $this->settings_schema_toggle[ $schema_label ]['fields']['used']['slug'];
        if ( get_option( $toggle_slug )  == $this->ON ) return true; else return false;
    }

    /**
     * Check plugin options to see if a particular CPT and/or category (from the 
     * current post) has been assigned a Schema.
     */
    public function check_if_schema_assigned ( $schema_label, string $cpt_slug, string $cat_slug ) {
        // TODO:
        return true;

    }

    public function section_has_panel_tab ( $section_label ) {
        if ( $this->options[ $section_label ]['tab'] ) return true;
        else return false;
    }

    public function section_has_toggle ( $section_label ) {
        if ( isset( $this->options_toggle[ $section_label ]['section_box'] ) ) return true;
        if ( isset( $this->options_tobble[ strtoupper( $section_label ) ] ) ) return true;
        else return false;
    }

    public function get_section_toggle_slug ( $section_label ) {
        $box = $this->options_toggle[ $section_label ]['section_box'];
        if ( ! $box ) {
            $box = $this->options_toggle[ strtoupper( $section_label ) ]['section_box'];
        }
        return $box;
        //return $this->options_toggle[ $section ]['section_box'];
    }

    /**
     * Get an options section div slug
     */
    public function get_section_slug ( $section_label ) {
        $box = $this->options[ $section_label ]['section_box'];
        if ( ! $box ) {
            $box = $this->options[ strtoupper( $section_label ) ]['section_box'];
        }
        return $box;
    }

    /**
     * Get the global state of the tabs for the whole UI, 
     * read the stored value in the hidden field:
     * tab1, tab2, tab3...
     * return content-tab1, content-tab2...
     */
    public function get_tabsel () {
        $tab_href = $tab_href = 'content-tab1';
        $option = get_option( $this->options['HIDDEN']['fields']['tabsel']['slug'] );
        if ( $option ) {
            $option = filter_var( $option, FILTER_SANITIZE_NUMBER_INT );
            $tab_href = 'content-tab' . $option;
        }
        return $tab_href;
    }

} // end of class