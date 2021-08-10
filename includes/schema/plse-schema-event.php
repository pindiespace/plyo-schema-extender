<?php

use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Context\Meta_Tags_Context;

/**
 * Returns Game Schema data
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
        'message' => 'Use this box to add fields to create an Event',
        'nonce' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT .'-metabox-nonce',

        // fields in the metabox, set for each post
        'fields' => array(

            'event_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-name',
                'label' => 'Event Name:',
                'title' => 'Official name of the event',
                'type'  => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => 'post_meta',
            ),

            'event_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-url',
                'label' => 'Event URL:',
                'title' => 'Website, or page on website, that is home page for this event',
                'type'  => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'event_image' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-image',
                'label' => 'Event Image:',
                'title' => 'Click button to upload image, or use one from Media Library',
                'type'  => PLSE_INPUT_TYPES['IMAGE'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'event_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-description',
                'label' => 'Event Description:',
                'title' => 'One-paragraph description of the event',
                'type'  => PLSE_INPUT_TYPES['TEXTAREA'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            // FORMAT: 'yyyy-mm-dd'
            // NOTE: sanitize_key() doesn't let us use -'startDate'
            'event_start_date' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-start-date',
                'label' => 'Start Date:',
                'title' => 'Day when the event starts',
                'type'  => PLSE_INPUT_TYPES['DATE'],
                'required' => 'required',
                'wp_data' => 'post_meta',
            ),

            // FORMAT: 'HH:MM:SS'
            'event_start_time' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-start-time',
                'label' => 'Start Time(Hour:Minute:Seconds):',
                'title' => 'Time when the event starts',
                'type'  => PLSE_INPUT_TYPES['TIME'],
                'required' => 'required',
                'wp_data' => 'post_meta',
            ),

            'event_end_date' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-end-date',
                'label' => 'End Date:',
                'title' => 'Day when the event ends',
                'type'  => PLSE_INPUT_TYPES['DATE'],
                'required' => 'required',
                'wp_data' => 'post_meta',
            ),

            // FORMAT: 'HH:MM:SS'
            'event_end_time' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_EVENT . '-end-time',
                'label' => 'End Time(Hour:Minute:AM/PM):',
                'title' => 'Time when the event ends',
                'type'  => PLSE_INPUT_TYPES['TIME'],
                'required' => 'required',
                'wp_data' => 'post_meta',
            ),

        )

    );

    /**
     * WPSEO_Schema_Game constructor.
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
     * - Custom Post Type 'Game' is present
     * - Game category was added to the post
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
     * Returns the Game Schema data.
     *
     * @since     1.0.0
     * @access    public
     * @return    array     $data The Game schema.
     */
    public function generate () {

        $post = $this->init->get_post();

        // data must be at least an empty array
        $data = array();


        return $data;

    }



} // end of class