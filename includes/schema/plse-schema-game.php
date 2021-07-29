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
     * A value object with context variables.
     *
     * @var Meta_Tags_Context
     */
    public $context;

    public $schema_slug = PLSE_SCHEMA_GAME;

    /** 
     * information for creating metabox 
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $schema_fields    data fields associated with this Schema
     */
    public static $schema_fields = array(
        'slug'  => 'plse-meta-game',
        'title' => 'Plyo Schema Extender - Game',
        'message' => 'Use this box to add fields to create a Game Schema',
        'nonce' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME .'-metabox-nonce',

        // fields in the metabox, set for each post
        'fields' => array(

            'game_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-name',
                'label' => 'Game Name:',
                'title' => 'Official name of the game',
                'type'  => 'TEXT',
                'required' => 'required',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-url',
                'label' => 'Game Website URL:',
                'title' => 'Website, or page on website, that is home page for the game',
                'type'  => 'URL',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_image' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-image',
                'label' => 'Game Image:',
                'title' => 'Click button to upload image, or use one from Media Library',
                'type'  => 'IMAGE',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-description',
                'label' => 'Game Description:',
                'title' => 'One-paragraph description of game setting, genre, gameplay',
                'type'  => 'TEXTAREA',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'screenshot' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-screenshot',
                'label' => 'Game Screenshot:',
                'title' => 'An image showing the game in action',
                'type'  => 'IMAGE',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_company_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_name',
                'label' => 'Game Company Name:',
                'title' => 'The company that created or produced the game',
                'type'  => 'TEXT',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_company_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_url',
                'label' => 'Game Company URL:',
                'title' => 'Website address, or address of page linking to the company',
                'type'  => 'URL',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'operating_system' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-operating_system',
                'label' => 'Supported Operating Systems:',
                'title' => 'Operating Systems compatible with the game',
                'type'  => 'SELECT_MULTIPLE',
                'required' => '',
                'value_type'=>'normal',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'Android',
                    'iOS',
                    'MacOS',
                    'Windows'
                ),
                'select_multiple' => true

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

            if ( $this->options_data->check_if_schema_assigned_cpt ( $schema_label ) && 
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

        // get required fields slugs


        $data = null;


        return $data;

    }



} // end of class