<?php

use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Context\Meta_Tags_Context;

/**
 * Returns Event Schema data
 *
 * @since      1.0.0
 * @category   WordPress_Plugin
 * @package    PLSE_SCHEMA_Extender
 * @subpackage PLSE_SCHEMA_Extender/schema
 * @author     Pete Markeiwicz <pindiespace@gmail.com>
 * @license    GPL-2.0+
 * @link       https://plyojump.com
 */
class PLSE_Schema_Event extends Abstract_Schema_Piece {

    /**
     * Store reference for singleton pattern.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $instance    static reference to initialized class.
     */
    static private $__instance = null;

    /**
     * A value object with context variables.
     *
     * @var Meta_Tags_Context
     */
    public $context;

    public $schema_slug = PLSE_SCHEMA_EVENT;

    /** 
     * information for creating metabox 
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $schema_fields    data fields associated with this Schema
     */
    public static $schema_fields = array(
        'slug'  => 'plse-meta-event',
        'title' => 'Plyo Schema Extender - Event',
        'message' => 'Use this box to add fields to create an Event. Events may be online, offline, or mixed. Only a single date and time may be specified.',
        'nonce' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT .'-metabox-nonce',
        'dashicon' => 'dashicons-megaphone',

        // fields in the metabox, set for each post
        'fields' => array(

            // special activation field - activate $post for output, if plugin options set so...
            PLSE_SCHEMA_RENDER_KEY => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-render-schema',
                'label' => 'Activate the Schema for this Post',
                'title' => 'If checked, Schema will be output to the final page.',
                'type' => PLSE_INPUT_TYPES['CHECKBOX'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_type' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-type',
                'label' => 'Event Type (defaults to "Event") if left blank',
                'title' => 'Sub-Type of event',
                'type'  => PLSE_INPUT_TYPES['DATALIST'],
                'option_list' => 'event_types',
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
                'start_of_block' => 'Primary Event Information'
            ),

            'event_name' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-name',
                'label' => 'Event Name',
                'title' => 'Official name of the event',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
                'start_of_block' => 'Primary Event Information'
            ),

            'event_url' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-url',
                'label' => 'Event URL',
                'title' => 'Website, or page on website, that is home page for this event',
                'type'  => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'sameAs' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-sameas',
                'label' => 'Alternate Event URLs (enter URLs)',
                'title' => 'Specific alternate URLs',
                'type'  => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['URL'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_description' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-description',
                'label' => 'Event Description',
                'title' => 'One-paragraph description of the event',
                'type'  => PLSE_INPUT_TYPES['TEXTAREA'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_images' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-image',
                'label' => 'Images related to the Event',
                'title' => 'Click button to typin in image URLS Event, or add from Media Library',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['URL'], // 'don't use IMAGE'
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
                'select_multiple' => false,
                'is_image' => true  // must be explicitly provided for Media Library button
            ),

            'event_status' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-event_attendance_mode',
                'label' => 'Event Status',
                'title' => 'Specify one or more attendance modes',
                'type' => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
                'option_list' => array(
                    'Event Scheduled' => 'EventScheduled',
                    'Event Canceled' => 'EventCancelled',
                    'Event Moved Online' => 'EventMovedOnline',
                    'Event Postphoned' => 'EventPostponed',
                    'Event Rescheduled' => 'EventRescheduled',
                ),
            ),

            // FORMAT: 'yyyy-mm-dd'
            // NOTE: sanitize_key() doesn't let us use -'startDate'
            'event_start_date' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-start-date',
                'label' => 'Start Date',
                'title' => 'Day when the event starts',
                'type'  => PLSE_INPUT_TYPES['DATE'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
                'start_of_block' => 'Event Dates and Times'
            ),

            // FORMAT: 'HH:MM:SS'
            'event_start_time' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-start-time',
                'label' => 'Start Time(Hour:Minute:AM or PM)',
                'title' => 'Time when the event starts',
                'type'  => PLSE_INPUT_TYPES['TIME'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_end_date' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-end-date',
                'label' => 'End Date',
                'title' => 'Day when the event ends',
                'type'  => PLSE_INPUT_TYPES['DATE'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
                'validate_date' => 'event_start_date' // slug, validate against start time field
            ),

            // FORMAT: 'HH:MM:SS'
            'event_end_time' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-end-time',
                'label' => 'End Time(Hour:Minute:AM/PM)',
                'title' => 'Time when the event ends',
                'type'  => PLSE_INPUT_TYPES['TIME'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_performing_group' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-performing-group',
                'label' => 'Perfoming Group, or describe who the event perfomers are (e.g. accountants)',
                'title' => 'Describe a specific group, or a class of performers',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_attendance_mode' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-event_attendance_mode',
                'label' => 'Online and Offline Attendance',
                'title' => 'Specify one or more attendance modes',
                'type' => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                'subtype' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
                'option_list' => array(
                    'Mixed Online and Offline' => 'MixedEventAttendanceMode',
                    'Offline' =>'OfflineEventAttendanceMode',
                    'Online' => 'OnlineEventAttendanceMode' ,
                ),

            ),

            'event_location_name' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-location_name',
                'label' => 'Event Location Name',
                'title' => 'Official name of the location',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
                'start_of_block' => 'Physical Location (if present)'
            ),

            'event_street_address' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-location_street_address',
                'label' => 'Event Location Address',
                'title' => 'Address',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),
            'event_address_locality' => array( //city
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-address_locality',
                'label' => 'Event Location City',
                'title' => 'City for the the event',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),
            'event_address_region' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-address_region',
                'label' => 'Event State or Region',
                'title' => 'Add a state or region of country',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_address_country' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-address-country',
                'label' => 'Event Country',
                'title' => 'Country in which the event is held',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_postal_code' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-postal-code',
                'label' => 'Event Address Postal Code',
                'title' => 'Postal Code for the event location',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_virtual_location' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-virtual_location',
                'label' => 'Online Location (URL) of where remote users can participate in event',
                'title' => 'URL giving online access to event',
                'type'  => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
                'start_of_block' => 'Online Location (if present)'
            ),

            // fields to specify a separate organizer
            'event_organizer_name' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-organizer-name',
                'label' => 'The name of the organization putting on the event (if not this site)',
                'title' => 'Organizer name, company name',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
                'start_of_block' => 'Organizer (if different from Yoast Organization Data)'
            ),

            'event_organizer_url' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-organizer-url',
                'label' => 'The address of the Organizer (if not this site)',
                'title' => 'URL leading to a third-party organizer, not this site\'s organization',
                'type'  => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            // event price (just the lowest-priced ticket)

            'event_price' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-price',
                'label' => 'The (lowest) price to attend the event (using the Currency listed below)',
                'title' => 'lowest price, URL directs to other prices',
                'type'  => PLSE_INPUT_TYPES['FLOAT'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
                'start_of_block' => 'Event Price (lowest-cost ticket)',
            ),

            'event_price_currency' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-price-currency',
                'label' => 'Web URL of the Organizer home page (if not this site)',
                'title' => 'URL leading to a third-party organizer, not this site\'s organization',
                'type'  => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                'option_list' => 'currency',
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
            ),
            'event_price_url' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-price-url',
                'label' => 'Web URL to buy tickets online for the event',
                'title' => 'URL leading ticket purchase page',
                'type'  => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            'event_price_availability' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-price-availability',
                'label' => 'Ticket Availability',
                'title' => 'Status of ticket availability',
                'type'  => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                'option_list' => 'offer_types',
                'required' => '',
                'wp_data' => PLSE_DATA_POST_META,
            ),

            // once this date passes, Google won't display the snippet
            'event_price_valid_until' => array(
                'slug' => PLSE_OPTIONS_SLUG . PLSE_SCHEMA_EVENT . '-price-valid-until',
                'label' => 'Ticket price is valid until',
                'title' => 'Last day the listed price applies',
                'type'  => PLSE_INPUT_TYPES['DATE'],
                'required' => 'required',
                'wp_data' => PLSE_DATA_POST_META,
                'validate_date' => PLSE_OPTIONS_CURRENT_DATE, // validate against current date
            ),


        )

    );

    /**
     * WPSEO_Schema_Event constructor.
     *
     * @param Meta_Tags_Context $context A value object with context variables.
     * @param string $cat A category flag to include this Schema.
     * @param string $ptype A Custom Post Type flag to include this Schema.
     */
    public function __construct( WPSEO_Schema_Context $context ) {

        // shared field definitions, Schema data is loaded separately
        $this->options_data = PLSE_Options_Data::getInstance();

        $this->init = PLSE_Init::getInstance();
        $this->context = $context;
    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Schema_Event    $self__instance
     */
    public static function getInstance ( $args ) {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Schema_Event( $args );
        }
        return self::$__instance;
    }

    /**
     * Get the data associated with this Schema.
     */
    public function get_data () {
        return $this->schema_fields;
    }

    /**
     * Unwrap individual fields out of the data object, different 
     * loop that PLSE_Options or PLSE_Metabox
     * 
     * @since    1.0.0
     * @access   public
     * @return   array    array with just the fields, extracted from their groups
     */
    public function get_data_fields () {

        $data = $this->schema_fields;

            foreach ( $data_group['fields'] as $fields ) {

                $field_list[] = $fields;

        }

        return $field_list;

    }

    /**
     * Determines whether or not a piece should be added to the graph.
     * - Custom Post Type 'Event' is present
     * - Event category was added to the post
     * 
     * @since    1.0.0
     * @access   public
     * @return   bool    if Schema should be added, return true, else false
     */
    public function is_needed () {

        $schema_label = $this->init->slug_to_label( $this->schema_slug );

        $post = get_post( $this->context->id );

        if( $post ) {
            $this->post = $post;

            if ( $this->options_data->check_if_schema_assigned_cpt ( $schema_label ) || 
                $this->options_data->check_if_schema_assigned_cat( $schema_label ) ) {
                return true;
            }

        }

        return false;

    }

    /**
     * Check if local post control is required. If it is, the Schema won't be 
     * added unless the 'xxx_activate' meta field is set to ON.
     * 
     * @since    1.0.0
     * @access   public
     * @return   boolean    if local post control over adding Schema enabled, return true, else false
     */
    public function is_rendered ( $val ) {
        $option = get_option( PLSE_LOCAL_POST_CONTROL_SLUG );
        if ( $option == $this->init->get_checkbox_on() ) {
            if ( $val == $this->init->get_checkbox_on() ) return true; 
            else return false;
        }
        return true;
    }

    /**
     * Returns the Event Schema data.
     *
     * @since     1.0.0
     * @access    public
     * @return    array     $data The Event schema.
     */
    public function generate () {

        $post = $this->init->get_post();

        /**
         * Assign values into the Schema array. We do this explicitly, rather than 
         * trying to loop through $fields since
         * - some fields go into subfields (e.g. ImageObject or VideoObject)
         * - some fields are used in multiple locations
         * 
         * Rather than making repeated calls, get the entire meta data array 
         * all at once. NOTE: this also gets Yoast-assigned values and values from 
         * any other metaboxes.
         */
        $values = get_post_meta( $post->ID, '', true );

        if ( empty( $values ) ) return array();

        // since the arrays are static, access statically here
        $fields = self::$schema_fields['fields'];

        if ( ! $this->is_rendered( $values[ $fields[PLSE_SCHEMA_RENDER_KEY]['slug'] ][0] ) ) return array();

        // validation flag
        $this->valid = true;


        // data must be at least an empty array
        $data = array(
            '@type'            => $this->get_event_type( $fields['event_type'], $values, $post ),
            '@id'              => $this->context->canonical . '#event',
            'mainEntityOfPage' => array( '@id' => $this->context->canonical . Schema_IDs::WEBPAGE_HASH ),
            'name' => $this->get_event_name( $fields['event_name'], $values, $post ),
            'description' => $this->get_event_description( $fields['event_description'], $values, $post ),

            // NOTE: Google requires that this be a unique page, not just the home page of a larger site
            'url' => $this->get_event_url( $fields['event_url'], $values, $post ),

            // multiple Event images in an array
            'image' => $this->get_event_images( $fields['event_images'], $values, $post ),

            'startDate' => $values[ $fields['event_start_date']['slug'] ][0],
            'endDate' => $values[ $fields['event_end_date']['slug'] ][0],

            // startTime and endTime are used to create the ISO date for Google
            'startTime' => $values[ $fields['event_start_time']['slug'] ][0],
            'endTime' => $values[ $fields['event_end_time']['slug'] ][0],

            // status of event (ongoing, cancelled...)
            'eventStatus' => $values[ $fields['event_status']['slug'] ][0],

            // attendance mode (offline, online, mixed...)
            'eventAttendanceMode' => $values[ $fields['event_attendance_mode']['slug'] ][0],

            'location' => $this->get_event_location( $fields, $values, $post ),
            'performer'=> $this->get_event_performers( $fields, $values, $post ),
            'organizer' => $this->get_event_organizer( $fields, $values, $post ),
            'offers' => $this->get_event_offers( $fields, $values, $post ),

        );

        return $data;

    }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - SCHEMA-SPECIFIC
     * Handles logic for specific fields
     * ---------------------------------------------------------------------
     */

    /**
     * Specify 'Event' or an Event subtype
     */
    public function get_event_type ( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {
            $val = 'Event';
        }

        return $val;

    }

    public function get_event_name ( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {

            // look for the first <h1> tag in the content, use it
            $val = $this->init->get_tags_from_content( 'h1', $post );
            if ( is_array( $val ) ) $val = wp_strip_all_tags( $val[0] ); // first h1 in content

            if ( empty( $val ) ) {

                // get the post title
                $val = get_the_title( $post );

            }

        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;
    }

    /**
     * Get a description.
     * 1. Try to use the meta field example.
     * 2. If that fails, look for $post excerpt
     * 3. If that fails, extract text content
     */
    public function get_event_description ( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {

            // get the excerpt, if present

            if ( has_excerpt( $post ) ) {

                $val = get_the_excerpt( $post ); // wordpress method

                // generate an excerpt from the content
                if ( empty( $val ) ) {
                    $val =  $this->init->get_excerpt_from_content( $post ); // our method
                }

            }

        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;

    }

    /**
     * Get the primary URL for the event (e.g. home page of website).
     * 
     * @since    1.0.0
     * @access   private
     * @return   string    $val    if present the URL for the event
     */
    private function get_event_url( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {
            $val = $this->context->canonical . Schema_IDs::WEBPAGE_HASH;
        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;

    }

    /**
     * Get image of the event, a logo, brand.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    URL of event image
     */
    public function get_event_images ( $field, $values, $post ) {

        //$val = $values[ $field['slug'] ][0];

        $val = $this->init->get_array_from_serialized( $values[ $field['slug'] ] ); // NOTE: no [0]

        if ( empty( $val ) ) {

            // get the featured image
            $val = $this->init->get_featured_image_url( $post );

            if ( empty( $val ) ) {

                // get the first image in the post
                $val = $this->init->get_first_post_image_url( $post );

                // get the default image from plugin options, if present
                if (empty( $val ) ) {

                    $val = get_option( $field['slug'] ); // from plugin options

                }

            }

        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;

    }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - SCHEMA-SPECIFIC, MULTIPLE FIELDS
     * Handles logic for specific blocks (e.g. VideoObject, ImageObject, Organization) 
     * that are not explicit Schemas in the plugin
     * ---------------------------------------------------------------------
     */

    /**
     * Use to get location of the event. 
     */
    public function get_event_location ( $fields, $values, $post ) {

       return array(

            array(
                '@type' => 'VirtualLocation',
                'url' => $values[ $fields['event_virtual_location']['slug'] ][0],
            ),

            array( 
                '@type'   => 'Place',
                'name'    => $values[ $fields['event_location_name']['slug'] ][0],
                'address' => array(
                    '@type'           => 'PostalAddress',
                    "streetAddress"   => $values[ $fields['event_street_address']['slug'] ][0],
                    "addressLocality" => $values[ $fields['event_address_locality']['slug'] ][0],
                    "postalCode"      => $values[ $fields['event_postal_code']['slug'] ][0],
                    "addressRegion"   => $values[ $fields['event_address_region']['slug'] ][0],
                    "addressCountry"  => $values[ $fields['event_address_country']['slug'] ][0]
                ),
            ),

        );

    }

    /**
     * Get Event performer(s). This could be a musical group, or a more general description 
     * of the 'performers,' e.g. 'game marketing experts'
     * 
     */
    public function get_event_performers ( $fields, $values, $post ) {

        return array(
            '@type' => 'PerformingGroup',
            'name' => $values[ $fields['event_performing_group']['slug'] ][0],
        );

    }

    /**
     * Get Event organizer
     * 
     * 
     */
    public function get_event_organizer( $fields, $values, $post ) {

        $organizer_name = $values[ $fields['event_organizer_name']['slug'] ][0];

        if ( ! empty( $organizer_name ) ) {

            return array(
                '@type' => 'Organization',
                'name' => $organizer_name,
                'url' => $values[ $fields['event_organizer_url']['slug'] ][0],
            );

        } else {

            // return the Yoast site-wide Organization data
            $logo_schema_id = $this->context->site_url . Schema_IDs::ORGANIZATION_LOGO_HASH;

            return array(
                '@type'  => 'Organization',
                '@id'    => $this->context->site_url . Schema_IDs::ORGANIZATION_HASH,
                'name'   => $this->helpers->schema->html->smart_strip_tags( $this->context->company_name ),
                'url'    => $this->context->site_url,
                'sameAs' => $this->init->get_social_profiles( $this->helpers ),
                'logo'   => $this->helpers->schema->image->generate_from_attachment_meta( $logo_schema_id, $this->context->company_logo_meta, $this->context->company_name ),
                'image'  => [ '@id' => $logo_schema_id ],
                'description' => $this->init->get_excerpt_from_content( $this->post ),
            );

        }

    }

    /**
     * Use to get Event offers. Google wants the lowest price ticket, so just collect 
     * one Offer, and link to a page with additional offers.
     */
    public function get_event_offers ( $fields, $values, $post ) {

        return array(

            'offers' => array(

                array(
                    '@type' => 'Offer',
                    'price' => $values[ $fields['event_price']['slug'] ][0],
                    'priceCurrency' => $values[ $fields['event_price_currency']['slug'] ][0],
                    'url' => $values[ $fields['event_price_url']['slug'] ][0],
                    'availability' => $values[ $fields['event_price_availability']['slug'] ][0],
                    'validFrom' => $values[ $fields['event_price_valid_from']['slug'] ][0],
                ),

            )

        );

    }

} // end of class