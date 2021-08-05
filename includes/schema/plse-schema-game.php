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
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-url',
                'label' => 'Game Website URL:',
                'title' => 'Website, or page on website, that is home page for the game',
                'type'  => 'URL',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_image' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-image',
                'label' => 'Game Image:',
                'title' => 'Click button to upload image, or use one from Media Library',
                'type'  => 'IMAGE',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-description',
                'label' => 'Game Description:',
                'title' => 'One-paragraph description of game setting, genre, gameplay',
                'type'  => 'TEXTAREA',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_in_language' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-in_language',
                'label' => 'Languages supported in the game:',
                'title' => 'Choose language(s) from the list',
                'type'  => 'SELECT_MULTIPLE',
                'required' => '',
                'wp_data' => 'post_meta',
                // https://techfunda.com/howto/1163/language-codes
                // TODO: flag option list with callback function
                'option_list' => array(
                    'English' => 'en',
                    'German' => 'de',
                    'Spanish' => 'es',
                    'Russian' => 'ru',
                    'Chinese' => 'zn',
                    'Arabic' => 'ar'
                ),
                'select_multiple' => true
            ),

            'screenshot' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-screenshot',
                'label' => 'Game Screenshot:',
                'title' => 'An image showing the game in action',
                'type'  => 'IMAGE',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_company_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_name',
                'label' => 'Game Company Name:',
                'title' => 'The company that created or produced the game',
                'type'  => 'TEXT',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_company_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_url',
                'label' => 'Game Company URL:',
                'title' => 'Website address, or address of page linking to the company',
                'type'  => 'URL',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_publisher' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-publisher',
                'label' => 'Game Publisher:',
                'title' => 'The company that distributed or publishes the game',
                'type'  => 'TEXT',
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_genre' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-genre',
                'label' => 'Game Genre (e.g tower Defense):',
                'title' => 'Category of game, type of game and gameplay',
                'type'  => 'REPEATER',
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_platform' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-platform',
                'label' => 'Supported Platforms:',
                'title' => 'Desktops, mobiles, and game consoles compatible with the game',
                'type'  => 'SELECT_MULTIPLE',
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'PlayStation' => 'playstation',
                    'XBox' => 'xbox',
                    'iOS' => 'ios',
                    'Android' => 'android',
                    'MacOS' => 'macos',
                    'Windows' => 'windows'
                ),
                'select_multiple' => true
            ),

            'operating_system' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-operating_system',
                'label' => 'Supported Operating Systems:',
                'title' => 'Operating Systems compatible with the game',
                'type'  => 'SELECT_MULTIPLE',
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'Android' => 'android',
                    'iOS' => 'ios',
                    'MacOS' => 'macos',
                    'Windows' => 'windows'
                ),
                'select_multiple' => true
            ),

            'trailer_video_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_url',
                'label' => 'Trailer Video URL:',
                'title' => 'Link to video showing gameplay',
                'type'  => 'VIDEO',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_video_in_language' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_in_language',
                'label' => 'Trailer Video Language:',
                'title' => 'Link to video showing gameplay',
                'type'  => 'SELECT_SINGLE',
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'English' => 'en',
                    'Spanish' => 'es',
                    'German'  => 'de'
                ),
                'select_multiple' => false
            ),

            'trailer_video_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_name',
                'label' => 'Trailer Video Name:',
                'title' => 'Name of game promotional video',
                'type'  => 'TEXT',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_video_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_description',
                'label' => 'Trailer Video Description:',
                'title' => 'One-paragraph description of what the game trailer video shows',
                'type'  => 'TEXTAREA',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_video_upload_date' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_upload_date',
                'label' => 'Trailer video upload date:',
                'title' => 'Date that the video was uploaded to public server',
                'type'  => 'DATE',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'install_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-install_url',
                'label' => 'Download Location for the game:',
                'title' => 'Address where the game may be downloaded',
                'type'  => 'URL',
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
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
     * @return   PLSE_Schema_Game    $self__instance
     */
    public static function getInstance ( $args ) {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Schema_Game ( $args );
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
     * Get any global plugin option data associated with a post.
     * 
     * 
     */
    public function get_option ( $slug ) {

    }

    /**
     * Get the Schema data associated with a post.
     * 
     * 
     * 
     */
    public function get_post_meta ( $slug, $post ) {

        if ( $field['select_multiple'] ) {
                $value = get_post_meta( $post->ID, $field[ $slug ] ); // multi-select control, returns array
        } else {
                $value = get_post_meta( $post->ID, $field[ $slug ], true ); // single = true, returns meta value
        }

        return $value;

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

        // since the arrays are static, access statically here
        $fields = PLSE_Schema_Game::$schema_fields['fields'];

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

        // data must be at least an empty array
        $data = array(
            '@type'  => 'VideoGame',
            '@id' => $this->context->canonical . Schema_IDs::WEBPAGE_HASH,
            //'name'   => $values[ 'plyo-schema-extender-game-name' ],
            'name' => $values[ $fields['game_name']['slug'] ][0],
            'url' => $values[ $fields['game_url']['slug'] ][0],
            'image' => $values[ $fields['game_image']['slug'] ][0],
            'screenshot' => $values[ $fields['screenshot']['slug'] ][0],
            'description' => $values[ $fields['description']['slug'] ][0],
            'author' => array(
                '@type' => 'Organization',
                'name' => $values[ $fields['game_company_name']['slug'] ][0],
                'url' => $values[ $fields['game_company_url']['slug'] ][0],
            ),
            'publisher' => $values[ $fields['game_publisher']['slug'] ][0],
            'genre' => $values[ $fields['game_genre']['slug'] ][0],
            'gamePlatform' => $this->init->get_array_from_serialized( $values[ $fields['game_platform']['slug'] ] ),

        );

        /*
         * cleanup for some fields, use $post data if the metaboxes
         * aren't filled in...
         */
        if ( empty( $data['description'] ) ) {
            $data['description'] = $this->init->get_the_excerpt( $post );
        }

        if ( empty( $data['image'] ) ) {
            $data['image'] = $this->init->get_featured_image_url( $post );
        }

        if ( empty( $data['screenshot'] ) ) {
            $data['screenshot'] = $this->init->get_first_post_image_url( $post );
        }

        return $data;

    }



} // end of class