<?php

use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Context\Meta_Tags_Context;

/**
 * Returns Service Schema data
 * {@link https://github.com/schemaorg/schemaorg/tree/main/data}
 *
 * @since      1.0.0
 * @category   WordPress_Plugin
 * @package    PLSE_SCHEMA_Extender
 * @subpackage PLSE_SCHEMA_Extender/schema
 * @author     Pete Markeiwicz <pindiespace@gmail.com>
 * @license    GPL-2.0+
 * @link       https://plyojump.com
 */
class PLSE_SCHEMA_SERVICE extends Abstract_Schema_Piece {

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

    public $schema_slug = PLSE_SCHEMA_SERVICE;

    /**
     * Validation for Schema. If the class can't build a valid Schema, set to false.
     * - 'required' flag in $schema_fields
     * -  missing meta-data with not fallback (e.g. plugin options)
     */
    public $valid = true;

    /** 
     * information for creating metabox.
     * Note: a partial description may cause a 500 error reported to the JS console. Look
     * in the web server error_log.
     * 
     * $field[]
     * |- slug            = the id used to access the field, store in metabox data
     * |- label           = the text in the <label>...</label> field
     * |- title           = appears when user mouses over the field
     * |- type            = type of control (PLSE_INPUT_TYPES[VALUE])
     * |- subtype         = if field is 'REPEATER' subtype is the field type for individual entries
     * |- required        = field entry required
     * |- wp_data         = whether values in post meta, or an option
     * |- select_multiple = if true, multiple options selected from a list
     * |- option_list     = either an array of values, or a string specifying a datalist in PLSE_Datalists
     * |- is_image        = for url fields, if the value is an image, show a thumbnail,
     * |- start_of_block  = lets metabox frame the start of a block of related fields
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $schema_fields    data fields associated with this Schema
     */
    public static $schema_fields = array(
        'slug'  => 'plse-meta-service',
        'title' => 'Plyo Schema Extender - Service',
        'message' => 'Use this box to add fields to create a Service Schema. It should be used when the primary focus of content is a business service.',
        'nonce' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE .'-metabox-nonce',
        'dashicon' => 'dashicons-businessperson',

        // fields in the metabox, set for each post
        'fields' => array(

            // special activation field - activate $post for output, if plugin options set so...
            PLSE_SCHEMA_RENDER_KEY => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-render-schema',
                'label' => 'Activate the Schema for this Post.  Otherwise, you can fill in data, but the Schema won\'t be attached to Yoast page output.',
                'title' => 'If checked, Schema will be output to the final page.',
                'type' => PLSE_INPUT_TYPES['CHECKBOX'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            // when checked by the user, the Schema will try to substitute URLs for text where possible
            'service_favor_urls' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-favor-urls',
                'label' => 'Favor URLs over text',
                'title' => 'Values with URLs (e.g. Wikipedia link for a word) will be used in the Schema',
                'type' => PLSE_INPUT_TYPES['CHECKBOX'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'service_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-name',
                'label' => 'Name of Service',
                'title' => 'Official name of the service',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false,
                'start_of_block' => 'General Service Information'
            ),


            // detailed type of service
            'service_type' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-type',
                'label' => 'Type Service',
                'title' => 'Broad category for the service',
                'type'   => PLSE_INPUT_TYPES['DATALIST'],
                'subtype' => PLSE_INPUT_TYPES['TEXT'],
                'description'  => 'Default Service Business Type',
                'option_list' => 'service_genres', // slug for function name in PLSE_Datalist
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false,
                'start_of_block' => 'General Service Information',
            ),

            'service_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-url',
                'label' => 'Service URL',
                'title' => 'Home page of website, or page describing the service',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'service_same_as' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-sameas',
                'label' => 'Other URLs directly related to the service',
                'title' => 'Additional URLs related to the service',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'is_image' => false
            ),

            'service_logo' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-logo',
                'label' => 'Brand Logo for Service',
                'title' => 'use the service brand, or the company brand',
                'type' => PLSE_INPUT_TYPES['IMAGE'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'service_image' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-image',
                'label' => 'Service Image',
                'title' => 'Click button to upload image, or use one from Media Library',
                'type' => PLSE_INPUT_TYPES['IMAGE'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'service_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-description',
                'label' => 'Service Description',
                'title' => 'One-paragraph description the service, and value to consumers',
                'type' => PLSE_INPUT_TYPES['TEXTAREA'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'service_slogan' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_SERVICE . '-slogan',
                'label' => 'Service Slogan or Tagline',
                'title' => 'list the slogan, tagline, or value proposition',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

        )

    );

    /**
     * WPSEO_Schema_Service constructor.
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
     * @return   PLSE_SCHEMA_SERVICE    $self__instance
     */
    public static function getInstance ( $args ) {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_SCHEMA_SERVICE ( $args );
        }
        return self::$__instance;
    }

    /**
     * Get the data associated with this Schema.
     */
    public function get_data () {
        return $this::schema_fields;
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
        return $this::schema_fields['fields'];
    }

    /**
     * Determines whether or not a piece should be added to the graph.
     * - Custom Post Type 'Service' is present
     * - Service category was added to the post
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
     * Returns the Service Schema data.
     *
     * @since     1.0.0
     * @access    public
     * @return    array     $data The Service schema.
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

        if ( empty( $values ) ) return array(); // yoast expects an array

        // since the arrays are static, access statically here
        $fields = self::$schema_fields['fields'];

        // if the plugin options specify that posts control Schema rendering, check to see if we should actually render.
        if ( ! $this->is_rendered( $values[ $fields[PLSE_SCHEMA_RENDER_KEY]['slug'] ][0] ) ) return array();

        // validation flag
        $this->valid = true;

        // data must be at least an empty array
        $data = array(
            '@context' => 'https://schema.org',
            '@type'  => $this->get_service_type( $fields['service_type'], $values, $post ),
            '@id' => $this->context->canonical . Schema_IDs::WEBPAGE_HASH,
            'name' => $this->get_service_name( $fields['service_name'], $values, $post ), 
            'url' => $this->get_service_url( $fields['service_url'], $values, $post ),
            'logo' => $this->get_service_image( $fields['service_image'], $values, $post ),
            'image' => $this->get_service_image( $fields['service_image'], $values, $post ),
            'description' => $this->get_service_description( $fields['service_description'], $values, $post ),
            'slogan' => $this->get_service_slogan( $fields['service_slogan'], $values, $post ),

            //'serviceOutput' => $this->get_service_output( $fields['service_output'], $values, $post ),
            // 'audience' => $this->get_service_audience( $fields['service_audience'], $values, $post ),
            //'provider' => $this->get_service_provider( $fields['provider'], $values, $post ),
            //'potentialAction' => $this->get_potential_action( $fields['potential_action'], $values, $post ),
            //'availableChannel' => $this->get_available_channel( $fields['available_channels'], $values, $post )
            // 'offers' => $this->get_offers( $fields['offers'] $values, $post ),
        );

        return $data;

    }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - SCHEMA-SPECIFIC, SINGLE FIELDS
     * Handles logic for specific fields
     * ---------------------------------------------------------------------
     */
    public function get_service_type ( $field, $values, $post ) {
        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) $this->valid = false;

        return $val;
    }


    public function get_service_name ( $field, $values, $post ) {

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
    public function get_service_description ( $field, $values, $post ) {

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
     * Get the primary URL for the Service (e.g. home page of website).
     * 
     * @since    1.0.0
     * @access   private
     * @return   string    $val    if present the URL for the Service
     */
    private function get_service_url( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {
            $val = $this->context->canonical . Schema_IDs::WEBPAGE_HASH;
        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;

    }

    /**
     * Get image of the Service, or the Service brand logo.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    URL of primary Service image
     */
    public function get_service_image ( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {

            // get the featured image
            $val = $this->init->get_featured_image_url( $post );

            if ( empty( $val ) ) {

                // get the first image in the post
                $val = $this->init->get_first_post_image_url( $post );

                // get the default image from plugin options
                if (empty( $val ) ) {
                    $val = get_option( $field['slug'] ); // from plugin options

                }

            }

        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;

    }

    public function get_service_slogan ( $fields, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {

        }

        if ( empty( $val ) ) $this->value = false;

        return $val;

    }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - SCHEMA-SPECIFIC, MULTIPLE FIELDS
     * Handles logic for specific blocks (e.g. VideoObject, ImageObject, Organization) 
     * that are not explicit Schemas in the plugin
     * ---------------------------------------------------------------------
     */

    public function get_service_provider (  $fields, $values, $post  ) {
        return array(
            '@type'   => 'Corporation',
            '@id'     => $this->context->site_url . Schema_IDs::ORGANIZATION_HASH,
            'additionalType' => $plse_service_additional_type, // public relations firm
            'url'     => $this->context->site_url . Schema_IDs::ORGANIZATION_HASH,
            'sameAs'  => $this->init->get_social_profiles(),
            'logo'    => $plse_organization_logo_image,
            'image'   => $plse_organization_image, // todo-define this elsewhere 
            'description' => $this->util->get_excerpt( $this->post ),
        );

    }

    public function get_potential_action ( $fields, $values, $post ) {
        return array(
        '@type' => 'TradeAction',
        'name' => 'Market indie game',
        // video game player might be the target
        // https://www.wikidata.org/wiki/Q5276395
            'target' => array(
                'https://www.apple.com/app-store',
                'https://play.google.com/store',
                'https://www.oculus.com/experiences/quest',
                'https://store.steampowered.com'
            )
        );

    }

    public function get_available_channel ( $fields, $values, $post ) {
        
        return array(
            '@type' =>  'ServiceChannel',
            'serviceUrl' =>  $plse_contact_url,
            'servicePhone' =>  array(
                '@type' =>  'ContactPoint',
                '@id' =>  $plse_contact_url,
                'name' =>  'Novy Unlimited Contact Information',
                'contactType' =>  'sales',
                'telephone' =>  $plse_contact_phone,
                'description' =>  'Contact Novy Unlimited by phone, MeetMe, form, or email for PR services',
                'availableLanguage' =>  'https://en.wikipedia.org/wiki/English_language',

            ),

        );

    }

    public function get_review ( $fields, $values, $post ) {
        
            // TODO: Google search console doesn't like this
        return array(
            '@type' => 'Review',
            'url' => 'https://www.facebook.com/novyunlimited/reviews/',

            'author' => array(
                    '@type' => 'Person',
                    'name' => 'Adam Solo'
            ),

            //'itemReviewed' => $this->context->site_url . Schema_IDs::ORGANIZATION_HASH, // not necessary for nested review
            'reviewRating' => array(
                '@type' => 'Rating',
                'ratingValue' => '4',
                'bestRating' => '5',
                'worstRating' => '1',
            ),

            'reviewBody' => "Working with Novy Unlimited was very easy and an absolute joy. Our interactions were always very productive too. They will take care of the PR and marketing activities for you and relieve you from the burden of having to handle social media, or to send press releases, as decided and agreed with them. 
            They are flexible and don't require much time and effort from you. They adapt to your working methods, deliver good documentation and keep you updated of results.
            These kind of services can be expensive, especially for indies. However, they have many packages so even if you go with just the basic PR package, which isn't that costly, they can send press releases and handle influencers. Their press releases are great, by the way.",
            'datePublished' => '2021-02-19'
        );

    }

} // end of class