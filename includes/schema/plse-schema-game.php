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
class PLSE_Schema_Game extends Abstract_Schema_Piece {

    /**
     * Store reference to utilities.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $util    the PLSE_Util class.
     */
    private $util;

    /**
     * A value object with context variables.
     *
     * @var Meta_Tags_Context
     */
    public $context;

    /** 
     * information for creating metabox 
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $schema_fields    data fields associated with this Schema
     */
    public static $schema_fields = array(
        'slug'  => 'plse-meta-game',
        'title' => 'Game Schema',
        'message' => 'Use this box to add fields to create a Game Schema',
        'nonce' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME .'-metabox-nonce',

        // fields in the metabox, set for each post
        'fields' => array(

            'game_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-name',
                'title' => 'Game Name:',
                'type'  => 'TEXT',
                'required' => 'required',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
            ),

            'game_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-url',
                'title' => 'Game URL:',
                'type'  => 'URL',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
            ),

            'game_image' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-image',
                'title' => 'Game Image and Image URL:',
                'type'  => 'IMG',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
            ),

            'game_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-description',
                'title' => 'Game Description:',
                'type'  => 'TEXTAREA',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
            ),

            'game_company_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_name',
                'title' => 'Game Company Name:',
                'type'  => 'TEXT',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
            ),

            'game_company_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_url',
                'title' => 'Game Company URL:',
                'type'  => 'URL',
                'required' => '',
                'value_type'=>'normal',
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
        $this->util = PLSE_Util::getInstance();
        $this->context = $context;
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
     * @return   bool    if schema should be added, return true, else false
     */
    public function is_needed () {

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

        $data = null;


        return $data;

    }



} // end of class