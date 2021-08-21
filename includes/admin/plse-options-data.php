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
     * Toggle checkbox for Schema on/off in plugin options. These appear above 
     * the fields associated with the Schema in a options page tab
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
                    'description' => 'Check this to enable the ' . PLSE_SCHEMA_SERVICE . ' Schema',
                    'title' => 'Use this Schema',
                    'label' => 'If checked, Schema are applied to the Custom Post Types and Categories selected below. Data from additional fields is available to every post or page using the Schema, but can be over-ridden by adding information in the post custom fields.',
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
                    'description' => 'Check this to enable the ' . PLSE_SCHEMA_GAME . ' Schema',
                    'title' => 'Use this Schema',
                    'label' => 'If checked, Schema are applied to the Custom Post Types and Categories selected below. Data from additional fields is available to every post or page using the Schema, but can be over-ridden by adding information in the post custom fields.',
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
                    'description' => 'Check this to enable the ' . PLSE_SCHEMA_EVENT . ' Schema',
                    'title' => 'Use this Schema',
                    'label' => 'If checked, Schema are applied to the Custom Post Types and Categories selected below. Data from additional fields is available to every post or page using the Schema, but can be over-ridden by adding information in the post custom fields.',
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
                        'description' => 'Check this to enable the ' . PLSE_SCHEMA_PRODUCT_REVIEW . ' Schema',
                        'title' => 'Use this Schema',
                        'label' => 'If checked, Schema are applied to the Custom Post Types and Categories selected below. Data from additional fields is available to every post or page using the Schema, but can be over-ridden by adding information in the post custom fields.',
                        'type' => PLSE_INPUT_TYPES['CHECKBOX']
                    )

                )

            )

        )

    );

    /** 
     * Information for creating plugin options fields in the Admin menu. 
     * 
     * - These are global fields which will be associated with ALL Schema.
     * - Individual fields for each Schema are defined in /schema/plse-schema-xxx.php
     * - NOTE: add an 'option_list' => array(...) to any $options['fields']['xxx'] to pass a static list.
     * - NOTE: add an 'option_list' => string to any field to use a PLSE_Datalist
     * - NOTE: NOT IDENTICAL TO METABOX FIELDS (e.g. yoast_slug not in metabox)
     * 
     * $field[]
     * |- slug            = the id used to access the field, store in metabox data
     * |- yoast_slug      = equivalent value in Yoast Local SEO (NOT IN METABOX)
     * |- description     = field description (for UI to the left of the field in first <td>...</td>)
     * |- label           = the text in the <label>...</label> field
     * |- title           = appears when user mouses over the field
     * |- type            = type of control (PLSE_INPUT_TYPES[VALUE])
     * |- width           = if PLSE_INPUT_TYPES['IMAGE'], the image width
     * |- height          = if PLSE_INPUT_TYPES['IMAGE], the image height
     * |- subtype         = if field is 'REPEATER' subtype is the field type for individual entries
     * |- required        = field entry required
     * |- select_multiple = if true, multiple options selected from a list
     * |- option_list     = either an array of values, or a string specifying a datalist in PLSE_Datalists
     * |- is_image        = for url fields, if the value is an image, show a thumbnail
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
                    'description' => 'Tabbed list separating options into groups',
                    'title' => 'Tab selection',
                    'type' => PLSE_INPUT_TYPES['HIDDEN'],
                    'label' => '', // <label>
                    'title' => ''  // appears on mouseover
                ),

            )

        ),

        // Config the plugin (first visible tab on plugins options page)

        'CONFIG' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG,
            'section_title'   => 'Plugin Configuration',
            'section_message' => 'Plugin configuration. This plugin stores data from Yoast, but can copy the Yoast Local SEO fields if the plugin is installed. The data is a copy, so if you edit it, it won\'t affect your Yoast settings',
            'section_box'     =>  PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-box',// <div> for section
            'tab'             => 'content-tab1', // MUST be 'content-tab' + number
            'tab_title'       => 'Config',

            'fields' => array(

                'import_yoast_local' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-import-yoast-local',
                    'description'  => 'Click to load Yoast Local SEO values into these fields. Changing the values here won\'t affect the values you entered into Yoast.',
                    'type'   => PLSE_INPUT_TYPES['BUTTON'],
                    'label' => 'Copy SEO values',
                    'title' => 'Copy Yoast Local SEO Data'
                ),

                'use_yoast_metadata' => array(
                    //'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-import-yoast-metadata',
                    'slug' => PLSE_USE_YOAST_METADATA_SLUG,
                    'description'  => 'If this is selected, the plugin will use Yoast SEO Meta descriptions and Yoast Local SEO values, where they make sense for the Schema. Overriden by excerpts added to pages and posts.',
                    'type'   => PLSE_INPUT_TYPES['CHECKBOX'],
                    'label' => 'Check to use Yoast meta data descriptions in the new Schema, if an excerpt isn\'t available.',
                    'title' => 'Yoast Local SEO values',
                ),

                //'local_post_control' => array(
                'local_post_control' => array(
                    //'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-local-post-control',
                    'slug'  => PLSE_LOCAL_POST_CONTROL_SLUG,
                    'description'  => 'If this is selected, posts don\'t automatically render schema to Yoast, even if they match the Custom Post Type or Category. Each post must be activated individually.',
                    'type'   => PLSE_INPUT_TYPES['CHECKBOX'],
                    'label' => 'Check to enable local post control of Schema rendering to Yoast.',
                    'title' => 'Control Schema in individual posts',
                ),

                'check_urls' => array(
                    //'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-check-urls',
                    'slug' => PLSE_CHECK_URLS_SLUG,
                    'description'  => 'If this is selected, URLs typed into custom Schema fields in posts will be checked online. It may result in slow loading.',
                    'type'   => PLSE_INPUT_TYPES['CHECKBOX'],
                    'label' => 'Check to have plugin actively check if URLs can be reached on the Internet.',
                    'title' => 'Check to actively test typed-in URLs',
                )

            )
        ),

        // general data, which may be imported from Yoast Local SEO if it exists

        'GENERAL' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL,
            'section_title'   => 'General Settings for Schema',
            'section_message' => 'General settings, which provide some addtional fields not in the default Yoast installation, but present in Yoast Local SEO. Select the Config tab to load Yoast Local SEO values, if present, into these fields.',
            'section_box'     =>  PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-box',// <div> for section
            'tab'             => 'content-tab2',
            'tab_title'       => 'Contact',

            'fields' => array(

                'phone' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-phone-field',
                    'description'  => 'Organization Phone (if different from Wordpress Admin)',
                    'type'   => PLSE_INPUT_TYPES['PHONE'],
                    'label' => 'Phone format: xxx-xxx-xxxx',
                    'title' => 'US and international phone numbers may be entered',
                    'yoast_slug' => 'location_phone' // check for Yoast Local SEO value when initialized
                ),

                'email' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-email-field',
                    'description'  => 'Organization Email contact (if different from Wordpress Admin)',
                    'type'   => PLSE_INPUT_TYPES['EMAIL'],
                    'label'  => 'Email format: xxxx@domainname.com',
                    'title'  => 'Provide a valid contact email for your organization',
                    'yoast_slug'  => 'location_email'
                ),
    
                'contact_url' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-contact-url-field',
                    'description'  => 'Contact URL for organization on website',
                    'type'   => PLSE_INPUT_TYPES['URL'],
                    'label'  => 'URL format: https://domain/page',
                    'title'  => 'Provide the web address of the contact page for your organization',
                    'yoast_slug'  => 'location_url'
                )

            )

        ),

        // organization address, which may be imported from Yoast Local SEO if it exists.

        'ADDRESS' => array(
            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS,
            'section_title'   => 'Local Business Address',
            'section_message' => 'Address providing fields missing from default Yoast installation, but present in Yoast Local SEO. Select the Config tab to load values from Yoast Local SEO, if present, into these fields.',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-box',// <div> for section
            'tab'             => 'content-tab3',
            'tab_title'       => 'Address',

            'fields' => array(

                'street' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-street-field',
                    'description'  => 'Organization Street Number (uses Yoast Local SEO if present)',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'label'  => 'Address (apartment or office number)',
                    'title'  => 'US and international street addresses are ok',
                    'yoast_slug'  => 'location_address'
                ),

                'street2' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-street2-field',
                    'description'  => 'Street Name (uses Yoast Local SEO if present)',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'label'  => 'Address (street name)',
                    'title'  => 'US and international street addresses are ok',
                    'yoast_slug'  => 'location_address_2'
                ),

                'city' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-city-field',
                    'description'  => 'Organization City (uses Yoast Local SEO if present)',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'label'  => 'Full name of city',
                    'title'  => 'Use the full name of the city (not an abbreviation)',
                    'yoast_slug'  => 'location_city'
                ),

                'state' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-state-field',
                    'description'  => 'Organization State or region (uses Yoast Local SEO if present)',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'label'  => 'Full name or abbreviation',
                    'title'  => 'supply the state, province, regional location',
                    'yoast_slug'  => 'location_state'
                ),

                'country' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-country-field',
                    'description'  => 'Organization Country (uses Yoast Local SEO if present)',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'label'  => 'Full name or abbreviation',
                    'title'  => 'Full name of country is best for Schema',
                    'yoast_slug'  => 'location_country'
                ),

                'postal' => array(
                    'slug'  =>  PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-postal-field',
                    'description' =>  'Organization Postal Code (uses Yoast Local SEO if present)',
                    'type'  =>  PLSE_INPUT_TYPES['POSTAL'],
                    'label' => 'Complete postal code',
                    'title' => 'US or international postal codes ok, but should be alphanumeric',
                    'yoast_slug' => 'location_zipcode'
                )

            )

        ),

        'SERVICE' => array(

            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE,
            'section_title'   => 'Service Settings for Schema',
            'section_message' => 'Parameters for Service Schema. Use Custom Post Types and Categories to choose which posts and pages get the Schema applied.',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-box', // <div> for section, used in display_options_page
            'tab'             => 'content-tab4',
            'tab_title'       => 'Service',

            'fields' => array(

                // cpt dropdown
                'cpt' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . PLSE_CPT_SLUG,
                    'description'  => 'Custom Post Types using Service Schema',
                    'type'   => PLSE_INPUT_TYPES['CPT'],
                    'label'  => 'Select Multiple ok',
                    'title'  => 'Clicking a Custom Post Type will add the Schema to all posts under that CPT'
                ),

                // cat dropdown
                'cat' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . PLSE_CAT_SLUG,
                    'description'  => 'Categories using Service Schema',
                    'type'   => PLSE_INPUT_TYPES['CAT'],
                    'label'  => 'Select Multiple ok',
                    'title'  => 'Clicking one of the listed categories will add the Schema to all posts using the category',
                ),

/////////////////////////////////////
/*
                // service subtype
                'service_subtype' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-subtype-field',
                    'description'  => 'Default Service Business SubType',
                    'type'   => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                    'label' => 'Descriptive type',
                    'title' => 'Enter a specific service type',
                    'option_list' => 'countries',
                    //'option_list' => array(
                    //    'PHIL' => 'phil',
                    //    'BOB' => 'bob'
                    //)
                ),
*/
//////////////////////////////////////

                // service type
                'service_type' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-type-field',
                    'description'  => 'Default Service Business Type',
                    'type'   => PLSE_INPUT_TYPES['DATALIST'],
                    'option_list' => 'service_genres', // kernel of function name in PLSE_Datalist
                    'label' => 'Descriptive type',
                    'title' => 'Enter a specific service type',
                ),

                'logo' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-logo',
                    'description'  => 'Default Service brand logo or icon',
                    'type'   => PLSE_INPUT_TYPES['IMAGE'],
                    'width'  => '128',
                    'height' => '128',
                    'label'  => 'Visual Brand or trademark',
                    'title'  => 'This should be the copyrighted or trademarked brand image'
                ),

                'image' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-image',
                    'description'  => 'Default image of Service (distinct from brand logo)',
                    'type'   => PLSE_INPUT_TYPES['IMAGE'],
                    'width'  => '240',
                    'height' => '120',
                    'label'  => 'Image showing service',
                    'title'  => 'This should show a service being supplied or used'
                )

            )

        ),

        'GAME'    => array(

            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME,
            'section_title'   => 'Game Settings for Schema',
            'section_message' => 'Parameters for Game Schema. These apply to all instances of Game Schema loaded. These are defaults, and can be over-ridden in individual pages and posts with custom fields.',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . '-box', // <div> for section, used in display_options_page()
            'tab'             => 'content-tab5',
            'tab_title'       => 'Game',

            'fields' => array(

                // cpt
                'cpt' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . PLSE_CPT_SLUG,
                    'description'  => 'Custom Post Types using Game Schema',
                    'type'   => PLSE_INPUT_TYPES['CPT'],
                    'label'  => 'Select Multiple ok',
                    'title'  => 'Clicking a Custom Post Type will add the Schema to all posts under that CPT'
                ),

                // cat
                'cat' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . PLSE_CAT_SLUG,
                    'description'  => 'Categories using Game Schema',
                    'type'   => PLSE_INPUT_TYPES['CAT'],
                    'label'  => 'Select Multiple ok',
                    'title'  => 'Clicking one of the listed categories will add the Schema to all posts using the category'

                ),

                'image' => array(
                    'slug'   => 'plse-' . PLSE_SCHEMA_GAME . '-image',
                    'description'  => 'Default image of Game',
                    'type'   => PLSE_INPUT_TYPES['IMAGE'],
                    'width'  => '240',
                    'height' => '120',
                    'label'  => 'Image of Game',
                    'title'  => 'Show a screenshot from the game'
                )

            )

        ),

        'EVENT'   => array(

            'section_slug'    => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT,
            'section_title'   => 'Event Settings for Schema',
            'section_message' => 'Parameters for Event Schema. These apply to all instances of Event Schema loaded. These are defaults, and can be over-ridden in indivdiual pages and posts with custom fields.',
            'section_box'     => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-box', // <div> for section, used in display_options_page()
            'tab'             => 'content-tab6',
            'tab_title'       => 'Event',

            'fields' => array(
                // cpt
                'cpt' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . PLSE_CPT_SLUG,
                    'description'  => 'Custom Post Types using Event Schema',
                    'type'   => PLSE_INPUT_TYPES['CPT'],
                    'label' => 'Select Multiple',
                    'title' => 'Select CPT that should use this Schema'
                ),

                // cat
                'cat' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . PLSE_CAT_SLUG,
                    'description'  => 'Categories using Event Schema',
                    'type'   => PLSE_INPUT_TYPES['CAT'],
                    'label' => 'Select Multiple',
                    'title' => 'Select Categories that shold use this Schema'
                )

            )

        ),

    );

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct () {

        // utilities
        $this->init = PLSE_Init::getInstance();

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
     * GETTERS
     * ---------------------------------------------------------------------
     */

    /**
     * Get Settings API options arrat, grouped by Schema
     * 
     * @since    1.0.0
     * @access   public
     * @return   array  $this->options the entire options array defining global Schema data
     */
    public function get_options () {
        return $this->options;
    }

    /**
     * Get Settings API option for activating/deactiving Schema in plugin options.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array $this->options_toggle a list of checkbox values for activating/deactivating Schema
     */
    public function get_toggles () {
        return $this->options_toggle;
    }


    /**
     * Unwrap individual fields for all Schema out of the data object.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array    array with just the fields, extracted from their groups
     */
    public function get_options_fields () {

        $field_list = array();

        foreach ( $this->options as $data_group ) {
            foreach ( $data_group['fields'] as $fields ) {
                $field_list[] = $fields;
            }
        }

        return $field_list;
    }

    /**
     * Unwrap all checkbox toggle fields for Schemas out of the data object.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array    $field_list a list of the checkbox field values
     */
    public function get_toggles_fields () {

        $field_list = array();

        foreach ( $this_options_toggle as $data_group ) {
            foreach ( $data_group['fields'] as $fields ) {
                $field_list[] = $fields;
            }
        }
        
        return $field_list;
    }

    /**
     * Get the options for a specific Schema.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array   $this->options[SCHEMA]
     */
    public function get_options_by_schema ( $schema_label ) {
        $s = $this->init->slug_to_label( $schema_label );
        return $this->options[ $s ];
    }

    /**
     * ask for data toggle by Schema.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string   $this->options_toggle[SCHEMA]
     */
    public function get_toggles_by_schema ( $schema_label ) {
        $s = $this->init->slug_to_label( $schema_label );
        return $this->options_toggle[ $s ];
    }

    /**
     * Get an options Schema <div> slug.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string   $this->options[SCHEMA]['section_box']
     */
    public function get_section_box_slug ( $schema_label ) {
        $s = $this->init->slug_to_label( $schema_label );
        return $this->options[ $s ]['section_box'];
    }

    /**
     * Get the slug for the Schema checkbox toggle (activate/deactivate Schema) in options
     * 
     * @since    1.0.0
     * @access   public
     * @return   string   $this->options_toggle[SCHEMA]['section_box]
     */
    public function get_section_toggle_slug ( $schema_label ) {
        $s = $this->init->slug_to_label( $schema_label );
        return $this->options_toggle[ $s ]['section_box'];
    }

    /**
     * ---------------------------------------------------------------------
     * CHECK FEATURES OF SECTIONS, FIELDS (used by PLSE_Metabox and PLSE_Options)
     * ---------------------------------------------------------------------
     */

    /**
     * Check if the Schema has a panel (not true for hidden field groups).
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean    if the panel has a tab return true, else false
     */
    public function section_has_panel_tab ( $schema_label ) {
        $s = $this->init->slug_to_label( $schema_label );
        if ( $this->options[ $s ]['tab'] ) return true;
        else return false;
    }

    /**
     * Check to see if the Schema panel has a checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean    if the panel has an activate/deactive checkbox return true, else false
     */
    public function section_has_toggle ( $schema_label ) {
        $s = $this->init->slug_to_label( $schema_label );
        if ( isset( $this->options_toggle[ $s ]['section_box'] ) ) return true;
        else return false;
    }

    /**
     * ---------------------------------------------------------------------
     * CHECK OPTIONS DATABASE (used by PLSE_Metabox and PLSE_Options)
     * ---------------------------------------------------------------------
     */

    /**
     * Check if plugin options show a Schema is active.
     * - if $schema_label isn't in $this->options_toggle, return always true
     * - if  $schema_label is in $this->options_toggle, check the options database for 
     *   its value, and return the checkbox state.
     * 
     * Used by plse-metabox.php, plse-options-data.php
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean   if Schema is active, return true, else return false
     */
    public function check_if_schema_active ( $schema_label ) {
        //$s = strtoupper( $schema_label );
        $s = $this->init->slug_to_label( $schema_label );
        if ( ! isset( $this->options_toggle[ $s ] ) ) return true;
        if ( ! isset( $this->options_toggle[ $s ]['fields']['used']['slug'] ) ) return false;
        else if ( get_option( $this->options_toggle[ $s ]['fields']['used']['slug'] ) == $this->init->get_checkbox_on() ) return true; 
        else return false;
    }

    /**
     * Check plugin options to see if a particular CPT and/or category (from the 
     * current post) has been assigned a Schema.
     */
    public function check_if_schema_assigned_cpt ( $schema_label ) {
        
        $post = $this->init->get_post();
        $s = $this->init->slug_to_label( $schema_label );

        // get the slug, retrieve option values of CPTs assigned to this Schema
        $slug = $this->options[ $s ]['fields']['cpt']['slug'];


        if ( ! $slug ) return false;

        // check the CPTs and Categories which have been associated with this Schema
        $cpts = get_option( $slug );

        // check custom post type
        if ( $cpts ) {

            // get the sub-array from get_option() returned value
            $cpts_arr = $cpts[ $slug ];

            if ( count( $cpts_arr ) ) {

                // is_singular() may not work (only works when user viewing post), so check manually
                $post_cpt = $this->init->get_post_cpt();
                foreach ( $cpts_arr as $cpt ) {
                    if ( $post_cpt == $cpt ) return true;
                }

            }

        }

        return false;
    }

    /**
     * Check if a category for the post was assigned a Schema.
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean    if Schema associated with post category, return true, else false
     */
    public function check_if_schema_assigned_cat ( $schema_label ) {

        $post = $this->init->get_post();
        $s = $this->init->slug_to_label( $schema_label );
        //$s = strtoupper( $schema_label );

        // get the slug, retrieve option values of CPTs assigned to this Schema
        $slug = $this->options[ $s ]['fields']['cat']['slug'];

        if ( ! $slug ) return false;

        // check the CPTs and Categories which have been associated with this Schema
        $cats = get_option( $slug );

        // check categories of Page and Post (we defined pages to have categories in PLSE_Init)
        if ( $cats ) {

            $cats_arr = $cats[ $slug ];

            if ( count( $cats_arr ) ) {
               /// echo "XXXXXXFOUND USER CATS ARRAY...\n";

                // check page and post categories
                if ( has_category( $cats_arr, $post ) ) {
                 ///   echo "XXXXXFOUND A User-Defined CATEGORY using has_category()....\n";
                    return true;
                }

                // if post is a CPT, check any assigned custom taxonomy
                $taxonomy_names = get_object_taxonomies( $post->post_type );
                foreach ( $taxonomy_names as $tax_name ) {
                    if ( is_taxonomy_hierarchical( $tax_name ) ) { // eliminates tags
                        $terms = get_the_terms( $post, $tax_name );
                        if ( $terms ) {
                            foreach($terms as $term) {
                                foreach( $cats_arr as $cat )
                                if ( $term->name == $cat || 
                                    $term->name == $post->post_type ) {
                                    return true;
                                } //if
                            } // foreach
                        } // if
                    } // if
                } // foreach
            } // else
        }

        return false;

    }

    /**
     * Get the stored global state for the last-selected tab
     * read the stored value in the hidden field. Use to make the 
     * tab and show/hide panels in the UI work properly.
     * 
     * option saves: tab1, tab2, tab3...
     * function returns: content-tab1, content-tab2...
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    $tab_href    the stored tab value from the last option
     */
    public function get_tabsel () {
        $tab_href = 'content-tab1';
        $option = get_option( $this->options['HIDDEN']['fields']['tabsel']['slug'] );
        if ( $option ) {
            $option = filter_var( $option, FILTER_SANITIZE_NUMBER_INT );
            $tab_href = 'content-tab' . $option;
        }
        return $tab_href;
    }

} // end of class