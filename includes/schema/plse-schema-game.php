<?php

use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Context\Meta_Tags_Context;

/**
 * Returns Game Schema data
 * {@link https://github.com/schemaorg/schemaorg/blob/main/data/sdo-videogame-examples.txt}
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
     * |- settings_slug   = the id of a sitewide, global value in plugin settings
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
        'slug'  => 'plse-meta-game',
        'title' => 'Plyo Schema Extender - Game',
        'message' => 'Use this box to add fields to create a Game Schema. It should be used when the primary focus of content is a videogame.',
        'nonce' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME .'-metabox-nonce',
        'dashicon' => 'dashicons-games',

        // fields in the metabox, set for each post
        'fields' => array(

            // special activation field - activate $post for output, if plugin options set so...
            PLSE_SCHEMA_RENDER_KEY => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-render-schema',
                'label' => 'Activate the Schema for this Post.  Otherwise, you can fill in data, but the Schema won\'t be attached to Yoast page output.',
                'title' => 'If checked, Schema will be output to the final page.',
                'type' => PLSE_INPUT_TYPES['CHECKBOX'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            // when checked by the user, the Schema will try to substitute URLs for text where possible
            'game_favor_urls' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-favor-urls',
                'label' => 'Favor URLs over text',
                'title' => 'Values with URLs (e.g. Wikipedia link for a word) will be used in the Schema',
                'type' => PLSE_INPUT_TYPES['CHECKBOX'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-name',
                'label' => 'Game Name',
                'title' => 'Official name of the game',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false,
                'start_of_block' => 'General Game Information'
            ),

            'game_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-url',
                'label' => 'Game Website URL',
                'title' => 'Website, or page on website, that is home page for the game',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_same_as' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-sameas',
                'label' => 'Other URLs directly related to the game (e.g. fansite)',
                'title' => 'Addition URLs about the game',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'is_image' => false
            ),

            'game_image' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-image',
                'label' => 'Game Image',
                'title' => 'Click button to upload image, or use one from Media Library',
                'type' => PLSE_INPUT_TYPES['IMAGE'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false,
            ),

            'game_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-description',
                'label' => 'Game Description',
                'title' => 'One-paragraph description of game setting, genre, gameplay',
                'type' => PLSE_INPUT_TYPES['TEXTAREA'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_location' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-location',
                'label' => 'Locations in the Game',
                'title' => 'Places or scenes within the game',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'is_image' => false
            ),

            'game_in_language' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-in-language',
                'label' => 'Supported Languages',
                'title' => 'List the languages supported',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => 'languages',
                'is_image' => false
            ),

            'game_date_published' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-date-published',
                'label' => 'Date Published',
                'title' => 'Publication date for the game',
                'type' => PLSE_INPUT_TYPES['DATE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_install_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-install_url',
                'label' => 'Download Location for the game',
                'title' => 'Address where the game may be downloaded',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_ersb_content_rating' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-ersb-content-rating',
                'label' => 'ERSB Content Rating',
                'title' => 'Pick a Rating Category',
                'type' => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'Everyone' => 'ERSB E',
                    '10 and up' => 'ERSB 10+',
                    'Teens' => 'ERSB T',
                    'Mature' => 'ERSB M',
                    'Adults Only' => 'ERSB AO',
                    'Rating Pending' => 'ERSB RP'
                ),
                'select_multiple' => false
            ),

            'game_play_mode' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-play-mode',
                'label' => 'Game Play Mode (single, multi-player)',
                'title' => 'choose an ennumerated playmode',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'Cooperative (CoOp)' => 'CoOp',
                    'Multi-Player' => 'MultiPlayer',
                    'Single Player' => 'SinglePlayer'
                ),
                'is_image' => false

            ),

            'screenshot' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-screenshot',
                'label' => 'Game Screenshot',
                'title' => 'An image showing the game in action',
                'type' => PLSE_INPUT_TYPES['IMAGE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_author_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-author-name',
                'label' => 'Game Author (typically a company)',
                'title' => 'The company that designed and programmed the game',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_author_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-author-url',
                'label' => 'Game Author URL',
                'title' => 'The URL for the company that created the game',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_publisher_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-publisher-name',
                'label' => 'Game Publisher Name (if different from Game Author)',
                'title' => 'The company that distributed or publishes the game',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_publisher_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-publisher-url',
                'label' => 'Game Publisher URL',
                'title' => 'The URL for the company that publishes the game',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_genre' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-genre',
                'label' => 'Game Genre (e.g tower Defense)',
                'title' => 'Category of game, type of game and gameplay',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => 'game_genres',
                'is_image' => false
            ),

            'game_platform' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-platform',
                'label' => 'Supported Platforms',
                'title' => 'Desktops, mobiles, and game consoles compatible with the game',
                'type' => PLSE_INPUT_TYPES['SELECT_MULTIPLE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'PlayStation' => 'playstation',
                    'XBox' => 'xbox',
                    'Nintendo' => 'nintendo',
                    'iOS' => 'ios',
                    'Android' => 'android',
                    'MacOS' => 'macos',
                    'Windows' => 'windows',
                    'Linux' => 'linux',
                    'Web-Based' => 'web',
                ),
                'select_multiple' => true
            ),

            'operating_system' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-operating_system',
                'label' => 'Supported Operating Systems',
                'title' => 'Operating Systems compatible with the game',
                'type' => PLSE_INPUT_TYPES['SELECT_MULTIPLE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => 'os',
                'select_multiple' => true
            ),

            'trailer_video_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_name',
                'label' => 'Trailer Video Name',
                'title' => 'Name of game promotional video',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false,
                'start_of_block' => 'Trailer Video'
            ),

            'trailer_video_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_description',
                'label' => 'Trailer Video Description',
                'title' => 'One-paragraph description of what the game trailer video shows',
                'type' => PLSE_INPUT_TYPES['TEXTAREA'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_video_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_url',
                'label' => 'Trailer Video URL',
                'title' => 'Link to video showing gameplay',
                'type' => PLSE_INPUT_TYPES['VIDEO'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_video_duration' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_duration',
                'label' => 'Trailer Video Duration (Hours:Minutes:Seconds)',
                'title' => 'video duration',
                'type' => PLSE_INPUT_TYPES['DURATION'],
                'required' => '',
                'wp_data' => 'post_meta',
                'max' => '10800', // 3 hours, in seconds
                'select_multiple' => false
            ),

            'trailer_thumbnail_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_thumbnail_url',
                'label' => 'Additional thumbnail scenes from the video',
                'title' => 'Add one or more images captured from the video',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'is_image' => true // for repeater fields
            ),

            'trailer_video_in_language' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_in_language',
                'label' => 'Trailer Video Language',
                'title' => 'Link to video showing gameplay',
                //'type' => PLSE_INPUT_TYPES['DATALIST'],
                //'subtype' => 'text',
                'type' => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => 'languages', // datalist reference in PLSE_Datalist
                'select_multiple' => false
            ),

            'trailer_video_upload_date' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_upload_date',
                'label' => 'Trailer video upload date',
                'title' => 'Date that the video was uploaded to public server',
                'type' => PLSE_INPUT_TYPES['DATE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),


            'game_server_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-server-name',
                'label' => 'Game Server Name',
                'title' => 'Server where the game may be played',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false, // leaving this off causes validation to fail
                'start_of_block' => 'Game Server'
            ),

            'game_server_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-server',
                'label' => 'Game Server URL',
                'title' => 'Server address where the game may be played, if online',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false // leaving this off causes validation to fail
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
     * Returns the Game Schema data.
     *
     * @since     1.0.0
     * @access    public
     * @return    array     $data The Game schema.
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
            '@type'  => 'VideoGame',
            '@id' => $this->context->canonical . Schema_IDs::WEBPAGE_HASH,
            'name' => $this->get_game_name( $fields['game_name'], $values, $post ), 
            'url' => $this->get_game_url( $fields['game_url'], $values, $post ),
            'image' => $this->get_game_image( $fields['game_image'], $values, $post ),
            'screenshot' => $this->get_game_screenshot( $fields['screenshot'], $values, $post ),
            'description' => $this->get_game_description( $fields['description'], $values, $post ),
            'inLanguage' => $this->init->get_array_from_serialized( $values[ $fields['game_in_language']['slug'] ] ),
            'installURL' => $values[ $fields['game_install_url']['slug'] ][0],
            'datePublished' => $values[ $fields['game_date_published']['slug'] ][0],
            'contentRating' => $values[ $fields['game_ersb_content_rating']['slug'] ][0],
            'audience' => array(
                '@type' => 'PeopleAudience',
                'suggestedMinAge' => $values[ $fields['suggested_minimum_age']['slug'] ][0],
            ),
            'trailer' => $this->get_game_trailer( $fields, $values, $post ),
            'playMode' => $this->init->get_array_from_serialized( $values[ $fields['game_play_mode']['slug'] ] ), //[0],
            
            //"processorRequirements":"4 GHz",
            //"memoryRequirements":"8 Gb",
            //"storageRequirements":"64 Gb",

            // author of the game (can be just the name)
            // e.g., 'author' => 'Bob'
            'author' => array(
                '@type' => 'Organization', // could be Person
                'name' => $values[ $fields['game_author_name']['slug'] ][0],
                'url' => $values[ $fields['game_author_url']['slug'] ][0],
                // 'founder', 'foundingDate', 'employees' could go here
            ),

            // publisher of the game (can be just the name)
            // e.g. 'publisher' => ['Bob Games', 'Ubisoft']
            'publisher' => array(
                "@type" => "Organization", // could be Person
                'name' => $values[ $fields['game_publisher_name']['slug'] ][0],
                'url' => $values[ $fields['game_publisher_url']['slug'] ][0],
            ),

            'gameLocation' => $this->init->get_array_from_serialized( $values[ $fields['game_location']['slug'] ]), // text list of locations

            // alternate listing
            //"gameLocation":
            //{"@type":"Place",
            //"name":"Citadel",
            //"description":"Supposedly constructed by the long-extinct Protheans, this colossal deep-space station serves as the capital of the Citadel Council. Gravity is simulated through rotation, and is a comfortable 1.02 standard G's on the Wards and a light 0.3 standard G's on the Presidium Ring"
            //},

            'genre' => $this->init->get_array_from_serialized( $values[ $fields['game_genre']['slug'] ] ), //[0],
            'gamePlatform' => $this->init->get_array_from_serialized( $values[ $fields['game_platform']['slug'] ] ),

            // hard-coded for all games
            'applicationCategory' => 'GameApplication',

            // only single servers allowed
            'gameServer' => array(
                '@type' => "GameServer",
                'name' => $this->get_game_name( $fields['game_server_name'], $values, $post ),
                'url' => $this->get_game_url( $fields['game_server_url'], $values, $post ),
            )

        );

        return $data;

    }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - SCHEMA-SPECIFIC, SINGLE FIELDS
     * Handles logic for specific fields
     * ---------------------------------------------------------------------
     */
    public function get_game_name ( $field, $values, $post ) {

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
    public function get_game_description ( $field, $values, $post ) {

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
     * Get the primary URL for the game (e.g. home page of website).
     * 
     * @since    1.0.0
     * @access   private
     * @return   string    $val    if present the URL for the game
     */
    private function get_game_url( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {
            $val = $this->context->canonical . Schema_IDs::WEBPAGE_HASH;
        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;

    }

    /**
     * Get image of the game, a logo, brand.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    URL of game image
     */
    public function get_game_image ( $field, $values, $post ) {

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

    /**
     * Get a screenshot associated with the game.
     * 
     */
    public function get_game_screenshot ( $field, $values, $post ) {
        return $this->get_game_image( $field, $values, $post );
    }

    /**
     * Get the game author (not necessarily the publisher).
     */
    public function get_game_author ( $fields, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {

            // if entire site is about a game, use plugin settings data, if present
            $is_game_site = get_option( $field['slug'] );

            if ( $is_game_site == $this->init->ON ) {

                // person is author of game
                $val = $this->context->canonical . Schema_IDs::PERSON_LOGO_HASH;
    
                // organization is author
                if ( empty( $val ) ) {

                    $val = $this->context->canonical . Schema_IDs::ORGANIZATION_HASH;

                }

            }

        }

        if ( empty( $val ) ) $this->valid = false;

        return $val;

    }

    /**
     * Get the game publisher.
     */
    public function get_game_publisher ( $field, $values, $post ) {

        $val = $values[ $field['slug'] ][0];

        if ( empty( $val ) ) {

            // Access plugin options for the Game Schema, look for global assignment
            $is_game_site = get_option( $field['slug'] );
            if ( $is_game_site == $this->init->ON ) {

                $val = $this->context->canonical . Schema_IDs::ORGANIZATION_HASH;

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
     * Get all VideoObject information related to the game trailer.
     * Required properties:
     * Name: the title of the video. 
     * Description: the description of the video. HTML tags are ignored.
     * uploadDate: the date the video was first published, in ISO 8601 format
     * ThumbnailURL: a URL pointing to the video thumbnail image file.
     * 
     */
    public function get_game_trailer ( $fields, $values, $post ) {

        $trailer = array(
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $values[ $fields['trailer_video_name']['slug'] ][0],
            'description' => $values[ $fields['trailer_video_description']['slug'] ][0],
            'inLanguage' => $values[ $fields['trailer_video_in_language']['slug'] ][0],
            'url' => $values[ $fields['trailer_video_url']['slug'] ][0],
            'contentUrl' => 'https://www.example.com/video/123/file.mp4',
            'embedUrl' => 'https://www.example.com/embed/123',
            'thumbnailURL' => $this->init->get_array_from_serialized( $values[ $fields['trailer_thumbnail_url']['slug'] ] ),

            'uploadDate' => $values[ $fields['trailer_video_upload_date']['slug'] ][0],
            //'uploadDate' => '2016-03-31T08:00:00+08:00',

            //  ISO 8601 format. For example, T00H30M5S represents a duration of 'thirty minutes and five seconds'
            //'duration' => 'PT1M54S',
            'duration' => $this->init->seconds_to_ISO860_duration( $values[ $fields['trailer_video_duration']['slug'] ][0] ),
            'interactionStatistic' => array(
              '@type' => 'InteractionCounter',
               'interactionType' => array(
                    '@type' => 'WatchAction'
               ),
              'userInteractionCount' => 5647018
            ),
            'regionsAllowed' => 'US,NL'

        );

        return $trailer;

    }

    /**
     * Create a MusicGroup Schema for incorporation into other Schemas.
     * 
     * @since    1.0.0
     * @access   public
     * @param    &array    $fields    fields used to create a MusicGroup Schema.
     * @param    &array    $data      the Yoast data object for schema
     * @param    &WP_Post  $post      current post
     */
    public function get_game_music_by ( &$fields, &$data, &$post ) {

        $data['musicBy'] = array(
            '@context' => 'http://schema.org',  
            '@type' => 'MusicGroup',  
            'name' => 'London has Fallen',  
            'url' => 'http://www.londonhasfallenband.com',  
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/6/63/London_has_Fallen_%28Band%29.jpg',  
            'sameAs' => array(
                'http://www.facebook.com/londonhasfallen',
                'http://www.twitter.com/londonhasfallen',
                'http://www.instagram.com/londonhasfallenband',
                'http://www.youtube.com/londonhasfallen1',
                'http://plus.google.com/+LondonhasFallen',
            ),

        );

    }

} // end of class