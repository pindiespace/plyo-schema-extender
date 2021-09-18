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
     * |- slug_dependent  = slug for another option field to set control value
     * |- msg_disabled    = message to show next to disabled control
     * |- msg_enabled     = message to show next to enabled control
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

                // a special field in plugin options, reporting last error in metaboxes or Schema rendering
                PLSE_OPTIONS_FIELD_WARNING => array(
                    'slug'   => PLSE_OPTIONS_FIELD_WARNING,
                    'label' => 'Warning messages from posts, pages with Schema',
                    'title' => 'Follow the link to check the post or page',
                    'type'   => PLSE_INPUT_TYPES['POST_WARNING'],
                    'description'  => 'Last Schema Warning in site pages or posts (edit page or post to clear) ',
                ),

                'import_yoast_local' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-import-yoast-local',
                    'label' => 'Copy SEO values',
                    'title' => 'Copy Yoast Local SEO Data',
                    'type'   => PLSE_INPUT_TYPES['BUTTON'],
                    'description'  => 'Click to load Yoast Local SEO values into these fields. Changing the values here won\'t affect the values you entered into Yoast.',
                    'slug_dependent' => YOAST_LOCAL_SEO_SLUG, // Yoast Local SEO slug, if installed
                    'msg_enabled' => 'Click to copy',
                    'msg_disabled' => 'Yoast Local SEO plugin not present'
                ),

                'use_yoast_metadata' => array(
                    //'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-import-yoast-metadata',
                    'slug' => PLSE_USE_YOAST_METADATA_SLUG,
                    'label' => 'Check to use Yoast meta data descriptions in the new Schema, if an excerpt isn\'t available.',
                    'title' => 'Yoast Local SEO values',
                    'type'   => PLSE_INPUT_TYPES['CHECKBOX'],
                    'description'  => 'If this is selected, the plugin will use Yoast SEO Meta descriptions and Yoast Local SEO values, where they make sense for the Schema. Overriden by excerpts added to pages and posts.',
                    'slug_dependent' => WPSEO_VERSION, // Yoast is present, a constant
                    'msg_enabled' => 'Check to use',
                    'msg_disabled' => 'Yoast plugin not present'
                ),

                //'local_post_control' => array(
                'local_post_control' => array(
                    //'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-local-post-control',
                    'slug'  => PLSE_LOCAL_POST_CONTROL_SLUG,
                    'label' => 'Check to enable local post control of Schema rendering to Yoast.',
                    'title' => 'Control Schema in individual posts',
                    'type'   => PLSE_INPUT_TYPES['CHECKBOX'],
                    'description'  => 'If this is selected, posts don\'t automatically render schema to Yoast, even if they match the Custom Post Type or Category. Each post must be activated individually.',
                ),

                'check_urls' => array(
                    'slug' => PLSE_CHECK_URLS_SLUG,
                    'label' => 'Check to have plugin actively check if URLs can be reached on the Internet.',
                    'title' => 'Check to actively test typed-in URLs',
                    'type'   => PLSE_INPUT_TYPES['CHECKBOX'],
                    'description'  => 'If this is selected, URLs typed into custom Schema fields in posts will be checked online. It may result in slow loading.',
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
                    'slug'   => PLSE_LOCAL_CONTACT_PHONE_SLUG,
                    'label' => 'Phone format: xxx-xxx-xxxx',
                    'title' => 'US and international phone numbers may be entered',
                    'type'   => PLSE_INPUT_TYPES['PHONE'],
                    'description'  => 'Organization Phone (if different from Wordpress Admin)',
                    'yoast_slug' => 'location_phone' // check for Yoast Local SEO value when initialized
                ),

                'email' => array(
                    'slug'   => PLSE_LOCAL_CONTACT_EMAIL_SLUG,
                    'label'  => 'Email format: xxxx@domainname.com',
                    'title'  => 'Provide a valid contact email for your organization',
                    'type'   => PLSE_INPUT_TYPES['EMAIL'],
                    'description'  => 'Organization Email contact (if different from Wordpress Admin)',
                    'yoast_slug'  => 'location_email'
                ),
    
                'contact_url' => array(
                    'slug'   => PLSE_LOCAL_CONTACT_URL_SLUG,
                    'label'  => 'URL format: http(s)://domain/page',
                    'title'  => 'Provide the web address of the contact page for your organization',
                    'type'   => PLSE_INPUT_TYPES['URL'],
                    'description'  => 'Contact URL for organization on website',
                    'yoast_slug'  => 'location_url'
                    ),

                'contact_languages' => array(
                    'slug'   => PLSE_LOCAL_CONTACT_LANGUAGES_SLUG,
                    'label'  => 'Languages supported for communication',
                    'title'  => 'Provide one or more languages for communicating',
                    'type'   => PLSE_INPUT_TYPES['REPEATER'],
                    'subtype' => PLSE_INPUT_TYPES['TEXT'],
                    'description'  => 'Supported Languages for Contact',
                    'option_list' => 'languages',
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

                // street address
                'street' => array(
                    'slug'   => PLSE_LOCAL_STREET_ADDRESS_SLUG,
                    'label'  => 'Address (apartment or office number)',
                    'title'  => 'US and international street addresses are ok',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'description'  => 'Organization Street Number (uses Yoast Local SEO if present)',
                    'yoast_slug'  => 'location_address'
                ),

                // street name
                'street2' => array(
                    'slug'   => PLSE_LOCAL_STREET_NAME_SLUG,
                    'label'  => 'Address (street name)',
                    'title'  => 'US and international street addresses are ok',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'description'  => 'Street Name (uses Yoast Local SEO if present)',
                    'yoast_slug'  => 'location_address_2'
                ),

                'city' => array(
                    'slug'   => PLSE_LOCAL_CITY_SLUG,
                    'label'  => 'Full name of city',
                    'title'  => 'Use the full name of the city (not an abbreviation)',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'description'  => 'Organization City (uses Yoast Local SEO if present)',
                    'yoast_slug'  => 'location_city'
                ),

                'state' => array(
                    'slug'   => PLSE_LOCAL_STATE_SLUG . '-state-field',
                    'label'  => 'Full name or abbreviation',
                    'title'  => 'supply the state, province, regional location',
                    'type'   => PLSE_INPUT_TYPES['TEXT'],
                    'description'  => 'Organization State or region (uses Yoast Local SEO if present)',
                    'yoast_slug'  => 'location_state'
                ),

                'country' => array(
                    'slug'   => PLSE_LOCAL_COUNTRY_SLUG,
                    'label'  => 'Full name or abbreviation',
                    'title'  => 'Full name of country is best for Schema',
                    'type'   => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                    'description'  => 'Organization Country (uses Yoast Local SEO if present)',
                    'option_list' => 'countries',
                    'yoast_slug'  => 'location_country'
                ),

                'postal' => array(
                    'slug'  =>  PLSE_LOCAL_POSTAL_SLUG,
                    'label' => 'Complete postal code',
                    'title' => 'US or international postal codes ok, but should be alphanumeric',
                    'type'  =>  PLSE_INPUT_TYPES['POSTAL'],
                    'description' =>  'Organization Postal Code (uses Yoast Local SEO if present)',
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
                    'label'  => 'Custom Post Types',
                    'title'  => 'Clicking a Custom Post Type will add the Schema to all posts under that CPT',
                    'type'   => PLSE_INPUT_TYPES['CPT'],
                    'description'  => 'Custom Post Types using Service Schema',
                ),

                // cat dropdown
                'cat' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . PLSE_CAT_SLUG,
                    'label'  => 'Categories',
                    'title'  => 'Clicking one of the listed categories will add the Schema to all posts using the category',
                    'type'   => PLSE_INPUT_TYPES['CAT'],
                    'description'  => 'Categories using Service Schema',
                ),

                'service_name' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-name',
                    'label' => 'Name of Service',
                    'title' => 'Official name of the service',
                    'type' => PLSE_INPUT_TYPES['TEXT'],
                    'description' => 'Name of the Service',
                    'select_multiple' => false,
                ),

                // service type
                'service_type' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-type',
                    'label' => 'Descriptive type',
                    'title' => 'Enter a specific service type',
                    'type'   => PLSE_INPUT_TYPES['DATALIST'],
                    'subtype' => PLSE_INPUT_TYPES['TEXT'],
                    'description'  => 'Default Service Business Type',
                    'option_list' => 'service_genres', // kernel of function name in PLSE_Datalist
                ),

                'service_url' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-url',
                    'label' => 'Service URL',
                    'title' => 'Home page of website, or page describing the service',
                    'type' => PLSE_INPUT_TYPES['URL'],
                    'description' => 'The home page for the service',
                    'required' => 'required',
                ),

                'service_description' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-description',
                    'label' => 'Service Description',
                    'title' => 'More detailed descritpion of the service',
                    'type' => PLSE_INPUT_TYPES['TEXTAREA'],
                    'description' => 'Detailed Description of Service',
                ),

                'service_logo' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-logo',
                    'label'  => 'Visual Brand or trademark',
                    'title'  => 'This should be the copyrighted or trademarked brand image',
                    'type'   => PLSE_INPUT_TYPES['IMAGE'],
                    'description' => 'Brand or symbol associated with the service',
                    'width'  => '128',
                    'height' => '128',
                ),

                'service_image' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-image',
                    'label'  => 'Image showing service',
                    'title'  => 'This should show a service being supplied or used',
                    'type'   => PLSE_INPUT_TYPES['IMAGE'],
                    'description'  => 'Default image of Service (distinct from brand logo)',
                    'width'  => '240',
                    'height' => '120',
                ),

                'service_slogan' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-slogan',
                    'label' => 'Service Slogan or Tagline',
                    'title' => 'list the slogan, tagline, or value proposition',
                    'type' => PLSE_INPUT_TYPES['TEXT'],
                    'description' => 'Tagline or motto associated with the service',
                    'wp_data' => PLSE_DATA_POST_META,
                ),

                // NOTE: BELOW FIELDS ARE FOR TESTING ONLY

                'start_date' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-start-date',
                    'label' => 'Start Date',
                    'title' => 'Day when the event starts',
                    'type'  => PLSE_INPUT_TYPES['DATE'],
                    'description' => 'When the Service first started',
                ),

                'start_time' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-start-time',
                    'label' => 'Start Time',
                    'title' => 'Day when the event starts',
                    'type'  => PLSE_INPUT_TYPES['TIME'],
                    'description' => 'The time the Service first started',
                ),

                'service_number' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-number',
                    'label' => 'Service Number',
                    'title' => 'The Number of the Service',
                    'type'  => PLSE_INPUT_TYPES['INT'],
                    'description' => 'The number associated with the service',
                ),

/*
                'service_quantity' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-quantity',
                    'label' => 'Service Quantity',
                    'title' => 'The Quantity of the Service',
                    'type'  => PLSE_INPUT_TYPES['FLOAT'],
                    'description' => 'The quantity associated with the service',
                    'required' => 'required',
                ),
*/

                'service_length' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_SERVICE . '-service-length',
                    'label' => 'Length of Service',
                    'type'  => PLSE_INPUT_TYPES['DURATION'],
                    'description' => 'The length the service typically runs',
                    'title' => 'Start of Service',
                    'max' => '10800', // 3 hours, in seconds
                ),

                'service_focus' => array(
                    'slug' => PLSE_OPTIONS_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-service_focus',
                    'label' => 'Service Focus',
                    'title' => 'Service Focus',
                    'type' => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                    'description' => 'Most important of the services',
                    'option_list' => array(
                        'PHILKEY' => 'phil', // stores 'phil', displays 'PHIL'
                        'BOBKEY' => 'bob',
                        'KRESLKEY' => 'kresl'
                    ),
                ),

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
                    'label'  => 'Select Multiple ok',
                    'title'  => 'Clicking a Custom Post Type will add the Schema to all posts under that CPT',
                    'type'   => PLSE_INPUT_TYPES['CPT'],
                    'description'  => 'Custom Post Types using Game Schema',
                ),

                // cat
                'cat' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . PLSE_CAT_SLUG,
                    'label'  => 'Select Multiple ok',
                    'title'  => 'Clicking one of the listed categories will add the Schema to all posts using the category',
                    'type'   => PLSE_INPUT_TYPES['CAT'],
                    'description'  => 'Categories using Game Schema',
                ),

                'game_name' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . '-name',
                    'label' => 'Game Name',
                    'title' => 'Official name of the game',
                    'type' => PLSE_INPUT_TYPES['TEXT'],
                    'description' => 'Name of the Game',
                ),

                'game_url' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . '-url',
                    'label' => 'Game Website URL',
                    'title' => 'Website, or page on website, that is home page for the game',
                    'type' => PLSE_INPUT_TYPES['URL'],
                    'description' => 'Home page for the game site',
                ),
    
                'game_description' => array(
                    'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GAME . '-description',
                    'label' => 'Game Description',
                    'title' => 'One-paragraph description of game setting, genre, gameplay',
                    'type' => PLSE_INPUT_TYPES['TEXTAREA'],
                    'description' => 'a paragraph summarizing the game',
                    'select_multiple' => false
                ),

                'image' => array(
                    'slug'   => 'plse-' . PLSE_SCHEMA_GAME . '-image',
                    'label'  => 'Image of Game',
                    'title'  => 'Show a screenshot from the game',
                    'type'   => PLSE_INPUT_TYPES['IMAGE'],
                    'description'  => 'Default image of Game',
                    'width'  => '240',
                    'height' => '120',

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
                    'label' => 'Select Multiple',
                    'title' => 'Select CPT that should use this Schema',
                    'type'   => PLSE_INPUT_TYPES['CPT'],
                    'description'  => 'Custom Post Types using Event Schema',
                ),

                // cat
                'cat' => array(
                    'slug'   => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . PLSE_CAT_SLUG,
                    'label' => 'Select Multiple',
                    'title' => 'Select Categories that shold use this Schema',
                    'type'   => PLSE_INPUT_TYPES['CAT'],
                    'description'  => 'Categories using Event Schema',
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
     * @return   PLSE_Options_Data    $self__instance
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