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
     * |- is_image        = for url fields, if the value is an image, show a thumbnail
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

            // when checked by the user, the Schema will try to substitute URLs for text where possible
            'favor_urls' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-favor-urls',
                'label' => 'Favor URLs over text:',
                'title' => 'Values with URLs (e.g. Wikipedia link for a word) will be used in the Schema',
                'type' => PLSE_INPUT_TYPES['CHECKBOX'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-name',
                'label' => 'Game Name:',
                'title' => 'Official name of the game',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => 'required',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-url',
                'label' => 'Game Website URL:',
                'title' => 'Website, or page on website, that is home page for the game',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_image' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-image',
                'label' => 'Game Image:',
                'title' => 'Click button to upload image, or use one from Media Library',
                'type' => PLSE_INPUT_TYPES['IMAGE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-description',
                'label' => 'Game Description:',
                'title' => 'One-paragraph description of game setting, genre, gameplay',
                'type' => PLSE_INPUT_TYPES['TEXTAREA'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_play_mode' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-play-mode',
                'label' => 'Game Play Mode (single, multi-player):',
                'title' => 'choose an ennumerated playmode',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'CoOp' => 'CoOp',
                    'MultiPlayer' => 'MultiPlayer',
                    'SinglePlayer' => 'SinglePlayer'
                ),
                'is_image' => false

            ),

            'game_in_language' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-in_language',
                'label' => 'Language supported in the game:',
                'title' => 'Choose language(s) from the list',
                'type' => PLSE_INPUT_TYPES['DATALIST'],
                'required' => '',
                'wp_data' => 'post_meta',
                // NOTE: option_list is the id of the datalist in PLSE_Datalists
                'option_list' => 'languages',
                'select_multiple' => false
            ),

            'screenshot' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-screenshot',
                'label' => 'Game Screenshot:',
                'title' => 'An image showing the game in action',
                'type' => PLSE_INPUT_TYPES['IMAGE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_company_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_name',
                'label' => 'Game Company Name:',
                'title' => 'The company that created or produced the game',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_company_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-company_url',
                'label' => 'Game Company URL:',
                'title' => 'Website address, or address of page linking to the company',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'game_publisher' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-publisher',
                'label' => 'Game Publisher:',
                'title' => 'The company that distributed or publishes the game',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
            ),

            'game_genre' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-genre',
                'label' => 'Game Genre (e.g tower Defense):',
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
                'label' => 'Supported Platforms:',
                'title' => 'Desktops, mobiles, and game consoles compatible with the game',
                'type' => PLSE_INPUT_TYPES['SELECT_MULTIPLE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => array(
                    'playstation' => 'PlayStation',
                    'xbox' => 'XBox',
                    'nintendo' => 'Nintendo',
                    'ios' => 'iOS',
                    'android' => 'Android',
                    'macos' => 'MacOS',
                    'windows' => 'Windows',
                    'linux' => 'Linux',
                    'web' => 'Web-Based'
                ),
                'select_multiple' => true
            ),

            'game_server' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-server',
                'label' => 'Game Server (URL):',
                'title' => 'Server address where the game may be played, if online',
                'type' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false // leaving this off causes validation to fail
            ),

            'operating_system' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-operating_system',
                'label' => 'Supported Operating Systems:',
                'title' => 'Operating Systems compatible with the game',
                'type' => PLSE_INPUT_TYPES['SELECT_MULTIPLE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => 'os',
                /*
                'option_list' => array(
                    'android' => 'Android',
                    'ios' => 'iOS',
                    'steamos' => 'SteamOS',
                    'macos' => 'MacOS',
                    'windows' => 'Windows',
                    'linux' => 'Linux'
                ),
                */
                'select_multiple' => true
            ),

            'trailer_video_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_url',
                'label' => 'Trailer Video URL:',
                'title' => 'Link to video showing gameplay',
                'type' => PLSE_INPUT_TYPES['VIDEO'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_thumbnail_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_thumbnail_url',
                'label' => 'Thumbnail scenes from the video:',
                'title' => 'Add one or more images captured from the video',
                'type' => PLSE_INPUT_TYPES['REPEATER'],
                'subtype' => PLSE_INPUT_TYPES['URL'],
                'required' => '',
                'wp_data' => 'post_meta',
                'is_image' => true // for repeater fields
            ),

            'trailer_video_in_language' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_in_language',
                'label' => 'Trailer Video Language:',
                'title' => 'Link to video showing gameplay',
                //'type' => PLSE_INPUT_TYPES['DATALIST'],
                'type' => PLSE_INPUT_TYPES['SELECT_SINGLE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'option_list' => 'languages', // datalist reference in PLSE_Datalist
                'select_multiple' => false
            ),

            'trailer_video_name' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_name',
                'label' => 'Trailer Video Name:',
                'title' => 'Name of game promotional video',
                'type' => PLSE_INPUT_TYPES['TEXT'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_video_description' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_description',
                'label' => 'Trailer Video Description:',
                'title' => 'One-paragraph description of what the game trailer video shows',
                'type' => PLSE_INPUT_TYPES['TEXTAREA'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'trailer_video_upload_date' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-trailer_video_upload_date',
                'label' => 'Trailer video upload date:',
                'title' => 'Date that the video was uploaded to public server',
                'type' => PLSE_INPUT_TYPES['DATE'],
                'required' => '',
                'wp_data' => 'post_meta',
                'select_multiple' => false
            ),

            'install_url' => array(
                'slug' => PLSE_SCHEMA_EXTENDER_SLUG . '-' . PLSE_SCHEMA_GAME . '-install_url',
                'label' => 'Download Location for the game:',
                'title' => 'Address where the game may be downloaded',
                'type' => PLSE_INPUT_TYPES['URL'],
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
            'name' => $values[ $fields['game_name']['slug'] ][0],
            'url' => $values[ $fields['game_url']['slug'] ][0],
            'image' => $values[ $fields['game_image']['slug'] ][0],
            'screenshot' => $values[ $fields['screenshot']['slug'] ][0],
            'description' => $values[ $fields['description']['slug'] ][0],
            'trailer' => array(
                "@context" => "https://schema.org",
                "@type" => "VideoObject",
                "name" => $this->get_game_name($fields['game_name'], $data, $post ), // "Introducing the self-driving bicycle in the Netherlands",
                "description" => "This spring, Google is introducing the self-driving bicycle in Amsterdam, the world's premier cycling city. The Dutch cycle more than any other nation in the world, almost 900 kilometres per year per person, amounting to over 15 billion kilometres annually. The self-driving bicycle enables safe navigation through the city for Amsterdam residents, and furthers Google's ambition to improve urban mobility with technology. Google Netherlands takes enormous pride in the fact that a Dutch team worked on this innovation that will have great impact in their home country.",
                "thumbnailUrl" => array(
                  "https://example.com/photos/1x1/photo.jpg",
                  "https://example.com/photos/4x3/photo.jpg",
                  "https://example.com/photos/16x9/photo.jpg"
                ),
                "uploadDate" => "2016-03-31T08:00:00+08:00",
                "duration" => "PT1M54S",
                "contentUrl" => "https://www.example.com/video/123/file.mp4",
                "embedUrl" => "https://www.example.com/embed/123",
                "interactionStatistic" => array(
                  "@type" => "InteractionCounter",
                   "interactionType" => array(
                        "@type" => "WatchAction"
                   ),
                  "userInteractionCount" => 5647018
                ),
                "regionsAllowed" => "US,NL"

            ),
            'playMode' => $values[ $fields['game_play_mode']['slug'] ][0],
            'author' => array(
                '@type' => 'Organization',
                'name' => $values[ $fields['game_company_name']['slug'] ][0],
                'url' => $values[ $fields['game_company_url']['slug'] ][0],
            ),
            'publisher' => $values[ $fields['game_publisher']['slug'] ][0],
            'genre' => $values[ $fields['game_genre']['slug'] ][0],
            'gamePlatform' => $this->init->get_array_from_serialized( $values[ $fields['game_platform']['slug'] ] ),
            'gameServer' => $values[ $fields['game_server']['slug'] ][0]

        );

        if( $this->valid ) $data['REPORT'] = 'VVAALLLIIDDDD';
        else $data['REPORT'] = 'EEERRRROOOORRRRR';

        return $data;

    }

    /**
     * ---------------------------------------------------------------------
     * VALIDATION
     * ---------------------------------------------------------------------
     */
    public function schema_data_valid ( $field ) {
        if ( ! $field['slug'][0] && $this->schema_fields['slug']['required'] )
            $this->valid = false;
        return $this->valid;
    }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - SCHEMA-SPECIFIC, SINGLE FIELDS
     * Handles logic for specific fields
     * ---------------------------------------------------------------------
     */
    public function get_game_name ( $field, &$data, &$post ) {
        $val = $field['slug'][0];
        if ( empty( $val ) ) {
            // look for a H1 in the content, use it
            $val = $this->init_get_text_between_tags( 'h1', $post );
            if ( empty( $val ) ) {
                // post title
                $val = get_the_title( $post );
            }
            // add value, if present, return validation status.
            if ( ! empty( $val ) ) $data['description'] = $val;
        }
        return $this->schema_data_valid( $field );
    }

    /**
     * Get a description.
     * 1. Try to use the meta field example.
     * 2. If that fails, look for $post excerpt
     * 3. If that fails, extract text content
     */
    public function get_game_description ( $field, &$data, &$post ) {
        $val = $field['slug'][0];
        if ( empty( $val ) ) {
            if ( has_excerpt( $post ) ) {
                $val = get_the_excerpt( $post ); // wordpress method
            }
            if ( empty( $val ) ) {
                $val =  $this->init->get_excerpt_from_content( $post ); // our method
            }

        }
        if ( ! empty( $val ) ) $data['description'] = $val;
        return $this->schema_data_valid( $field );
    }

    /**
     * Get image of the game, a logo, brand.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    URL of game image
     */
    public function get_game_image ( $field, &$data, &$post ) {

        $val = $field['slug'][0];
        if ( empty( $val ) ) {
            $val = $this->init->get_featured_image_url( $post );
            if ( empty( $val ) ) {
                $val = $this->init->$get_first_post_image_url( $post );
                if (empty( $val ) ) {
                    $val = get_option( 'plse-' . PLSE_SCHEMA_GAME . '-image' ); // from plugin options
                }
            }
        }
        if ( ! empty( $val ) ) $data['image'] = $val;
    }

    public function get_game_screenshot ( $field, &$data, &$post ) {
        return $this->get_game_image( $field, $data, $post );
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
    public function get_game_trailer ( &$fields, &$data, &$post ) {

        $data['trailer'] =array(
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $fields['trailer_video_name']['slug'][0],
            'description' => $fields['trailer_video_description']['slug'][0],
            'inLanguage' => $fields['trailer_video_in_language']['slug'][0],
            'url' => $fields['trailer_video_url']['slug'][0],
            'contentUrl' => 'https://www.example.com/video/123/file.mp4',
            'embedUrl' => 'https://www.example.com/embed/123',
            'thumbnailUrl' => array(
                ////////////////////////$slug . '-img-id
                'https://example.com/photos/1x1/photo.jpg',
                'https://example.com/photos/4x3/photo.jpg',
                'https://example.com/photos/16x9/photo.jpg',
            ),
            'uploadDate' => '2016-03-31T08:00:00+08:00',
            //  ISO 8601 format. For example, T00H30M5S represents a duration of “thirty minutes and five seconds”.
            'duration' => 'PT1M54S',
            'interactionStatistic' => array(
              '@type' => 'InteractionCounter',
               'interactionType' => array(
                    '@type' => 'WatchAction'
               ),
              'userInteractionCount' => 5647018
            ),
            'regionsAllowed' => 'US,NL'

        );

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

    public function get_game_publisher ( &$fields, &$data, &$post ) {

    }

} // end of class