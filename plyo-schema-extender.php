<?php
/**
 * Plugin Name:       Plyo Schema Extender
 * Plugin URI:        https://github.com/pindiespace/plyo-schema-extender
 * Description:       A basic plugin which extends Yoast SEO schema using custom post types, post and page categories.
 * Version:           1.0.0
 * Author:            Pete Markiewicz
 * Author URI:        https://plyojump.com
 * Text Domain:       plyo-schema-extender
 * Domain Path:       /languages
 * 
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * Released under the GPL license
 * https://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * https://wordpress.org/
 *
 * -----------------------------------------------------------------------
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *  
 * -----------------------------------------------------------------------
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// We are running outside of the context of WordPress.
if ( ! function_exists( 'add_action' ) ) return;

if ( ! defined( 'PLSE_SCHEMA_PHP_MIN_VERSION' ) ) {
    define( 'PLSE_SCHEMA_PHP_MIN_VERSION', '5.6' );
}

// Current plugin name.
if ( ! defined( 'PLSE_SCHEMA_EXTENDER_NAME' ) ) {
    define ( 'PLSE_SCHEMA_EXTENDER_NAME', 'Plyo Schema Extender' );
}

/*
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'PLSE_SCHEMA_EXTENDER_VERSION', '1.0.0' );

// define the plugin slug for the admin options menu
if ( ! defined( 'PLSE_SCHEMA_EXTENDER_SLUG' ) ) {
    define( 'PLSE_SCHEMA_EXTENDER_SLUG', 'plyo-schema-extender' );
}

/*
 * -----------------------------------------------------------------------
 * String constants. Put here so they can be translated.
 * -----------------------------------------------------------------------
 */

/*
 * Basic Plugin description (for options page)
 */
if ( ! defined( 'PLSE_SCHEMA_OPTIONS_DESCRIPTION' ) ) {
    define( 'PLSE_SCHEMA_OPTIONS_DESCRIPTION', __( 'This plugin works with Yoast SEO and adds additional schema to the default schema provided by Yoast. Schemas can be added through a custom post type whose name matches the schema.org schema, or by creating a category name matching the schema name. Plugin is NOT compatible with other Schema plugins' ) );
}


/*
 * -----------------------------------------------------------------------
 * DEFINE FILE PATHS.
 * -----------------------------------------------------------------------
 */

// define the default file
if ( ! defined( 'PLSE_SCHEMA_DEFAULT_FILE' ) ) {
    define( 'PLSE_SCHEMA_DEFAULT_FILE', __FILE__ );
}

if ( ! defined( 'PLSE_SCHEMA_EXTENDER_BASE' ) ) {
    define( 'PLSE_SCHEMA_EXTENDER_BASE', plugin_basename( PLSE_SCHEMA_DEFAULT_FILE ) );
}

if ( ! defined( 'PLSE_SCHEMA_EXTENDER_PATH' ) ) {
    define( 'PLSE_SCHEMA_EXTENDER_PATH', dirname( __FILE__ ) );
}

/*
 * -----------------------------------------------------------------------
 * Yoast SEO CONSTANTS
 * -----------------------------------------------------------------------
 */

// define the minimum version of Yoast that the PLSE supports.
if ( ! defined( 'PLSE_SCHEMA_YOAST_MIN_VERSION' ) ) {
    define( 'PLSE_SCHEMA_YOAST_MIN_VERSION', '14.0' );
}

if ( ! defined( 'YOAST_DIR' ) ) {
    define( 'YOAST_DIR', dirname( __FILE__ ) . '/wordpress-seo' );
}

// define Yoast file indicating that it is installed
if ( ! defined( 'YOAST_PLUGIN' ) ) {
    define( 'YOAST_PLUGIN', 'wordpress-seo/wp-seo.php' );
}

if ( ! defined( 'YOAST_MENU_SLUG' ) ) {
    define( 'YOAST_MENU_SLUG', 'wpseo_dashboard' );
}

// this option is defined if Yoast Local SEO is installed
if ( ! defined( 'YOAST_LOCAL_SEO_SLUG' ) ) {
    define( 'YOAST_LOCAL_SEO_SLUG', 'wpseo_local' );
}

/**
 * --------------------------------------------------------------------------
 * SUPPORTED SCHEMA
 * --------------------------------------------------------------------------
 */
if ( ! defined( 'PLSE_SCHEMA_GAME' ) ) {
    define( 'PLSE_SCHEMA_GAME', 'game' );
}

if ( ! defined( 'PLSE_SCHEMA_EVENT' ) ) {
    define( 'PLSE_SCHEMA_EVENT', 'event' );
}

if ( ! defined( 'PLSE_SCHEMA_SERVICE ') ) {
    define( 'PLSE_SCHEMA_SERVICE', 'service');
}

if ( ! defined( 'PLSE_SCHEMA_PRODUCT_REVIEW' ) ) {
    define( 'PLSE_SCHEMA_PRODUCT_REVIEW', 'product-review' );
}

/**
 * --------------------------------------------------------------------------
 * SLUG CONSTANTS
 * --------------------------------------------------------------------------
 */

// prefix for all database keys (Settings API, and in metabox data)
if ( ! defined( 'PLSE_OPTIONS_SLUG' ) ) {
    define( 'PLSE_OPTIONS_SLUG', 'plse-settings-' );
}

// options slug to report problems in a meta field (e.g. expired Event)
if ( ! defined( 'PLSE_OPTIONS_FIELD_WARNING' ) ) {
    define( 'PLSE_OPTIONS_FIELD_WARNING', 'plse-field-warning' );
}

// field is required by Schema
if ( ! defined( 'PLSE_OPTIONS_REQUIRED' ) ) {
    define( 'PLSE_OPTIONS_REQUIRED', 'required' );
}

// flag for checking current date
if ( ! defined( 'PLSE_OPTIONS_CURRENT_DATE' ) ) {
    define( 'PLSE_OPTIONS_CURRENT_DATE', 'current' );
}

// flag value ( 1 second, minute, hour day - set to 1 day)
if ( ! defined( 'PLSE_OPTIONS_DATE_FLAG') ) {
    define( 'PLSE_OPTIONS_DATE_FLAG', 86400 );
}

/**
 * --------------------------------------------------------------------------
 * YOAST LOCAL SEO DATA
 * --------------------------------------------------------------------------
 */

if ( ! defined( 'PLSE_SCHEMA_CONFIG ' ) ) {
    define( 'PLSE_SCHEMA_CONFIG', 'config' ); // option to import Yoast Local SEO
}

if ( ! defined( 'PLSE_SCHEMA_GENERAL' ) ) {
    define( 'PLSE_SCHEMA_GENERAL', 'general' );
}

if ( ! defined( 'PLSE_SCHEMA_ADDRESS' ) ) {
    define( 'PLSE_SCHEMA_ADDRESS', 'address');
}

if ( ! defined( 'PLSE_SCHEMA_HIDDEN' ) ) {
    define( 'PLSE_SCHEMA_HIDDEN', 'hidden' );
}

// local phone slug (used by several Schema)
if ( ! defined( 'PLSE_LOCAL_PHONE_SLUG' ) ) {
    define( 'PLSE_LOCAL_PHONE_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-phone-field' );
}

// local email slug (used by several Schema)
if  ( ! defined( 'PLSE_LOCAL_EMAIL_SLUG' ) ) {
    define( 'PLSE_LOCAL_EMAIL_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-email-field' );
}

// local 'contact us' URL (used by several Schema)
if ( ! defined( 'PLSE_LOCAL_CONTACT_URL_SLUG' ) ) {
    define( 'PLSE_LOCAL_CONTACT_URL_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-contact-url-field' );
}

// street address of local business
if ( ! defined( 'PLSE_LOCAL_STREET_ADDRESS_SLUG' ) ) {
    define( 'PLSE_LOCAL_STREET_ADDRESS_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-street-field' );
}

// street name of local business
if ( ! defined( 'PLSE_LOCAL_STREET_NAME_SLUG' ) ) {
    define( 'PLSE_LOCAL_STREET_NAME_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-street2-field' );
}

// city of local business
if ( ! defined( 'PLSE_LOCAL_CITY_SLUG' ) ) {
    define( 'PLSE_LOCAL_CITY_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-city-field' );
}

// state of local business
if ( ! defined( 'PLSE_LOCAL_STATE_SLUG' ) ) {
    define( 'PLSE_LOCAL_STATE_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-state-field' );
}

// country of local business
if ( ! defined( 'PLSE_LOCAL_COUNTRY_SLUG' ) ) {
    define( 'PLSE_LOCAL_COUNTRY_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-country-field' );
}

// postal code of local business
if ( ! defined( 'PLSE_LOCAL_POSTAL_SLUG' ) ) {
    define( 'PLSE_LOCAL_POSTAL_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_ADDRESS . '-postal-field' );
}

if ( ! defined( 'PLSE_LOCAL_CONTACT_PHONE_SLUG' ) ) {
    define( 'PLSE_LOCAL_CONTACT_PHONE_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-phone-field' );
}

if ( ! defined( 'PLSE_LOCAL_CONTACT_EMAIL_SLUG' ) ) {
    define( 'PLSE_LOCAL_CONTACT_EMAIL_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-email-field' );
}

if ( ! defined( 'PLSE_LOCAL_CONTACT_URL_SLUG' ) ) {
    define( 'PLSE_LOCAL_CONTACT_URL_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-contact-url-field' );
}

if ( ! defined( 'PLSE_LOCAL_CONTACT_LANGUAGES_SLUG' ) ) {
    define( 'PLSE_LOCAL_CONTACT_LANGUAGES_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_GENERAL . '-contact-url-languages' );
}

/**
 * --------------------------------------------------------------------------
 * PLUGIN OPTION SLUGS, SHARED
 * These options slugs are used by metaboxes and plse-schema-xxx.php classes, 
 * so define them here instead of in plse-options-data.php
 * This way, we avoid having to load PLSE_Options or PLSE_Options_Data while
 * rendering Schema.
 * --------------------------------------------------------------------------
 */

//'use_yoast_metadata'
if ( ! defined( 'PLSE_USE_YOAST_METADATA_SLUG' ) ) {
    define('PLSE_USE_YOAST_METADATA_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-import-yoast-metadata' );
}

// determines whether Schema generation can be turned on/off at the individual post level
if ( ! defined( 'PLSE_LOCAL_POST_CONTROL_SLUG') ) {
    define( 'PLSE_LOCAL_POST_CONTROL_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-local-post-control' );
}

if ( ! defined( 'PLSE_CHECK_URLS_SLUG' ) ) {
    define( 'PLSE_CHECK_URLS_SLUG', PLSE_OPTIONS_SLUG . PLSE_SCHEMA_CONFIG . '-check-urls' );
}

/**
 * --------------------------------------------------------------------------
 * METABOX FIELD SLUGS
 * field keys in plse-schema-xxx.php that need to have the same for PLSE_Metabox processing
 * --------------------------------------------------------------------------
 */
if ( ! defined( 'PLSE_SCHEMA_RENDER_KEY' ) ) {
    define( 'PLSE_SCHEMA_RENDER_KEY', 'render_schema' );
}

if ( ! defined( 'PLSE_DATA_POST_META') ) {
    define( 'PLSE_DATA_POST_META', 'post_meta' );
}

if ( ! defined( 'PLSE_DATA_SETTINGS' ) ) {
    define( 'PLSE_DATA_SETTINGS', 'plugin_settings');
}

if ( ! defined( 'PLSE_META_USED_SETTINGS') ) {
    define( 'PLSE_META_USED_SETTINGS', 'meta_to_settings' );
}

// erase data flag
if ( ! defined( 'PLSE_ERASE' ) ) {
    define( 'PLSE_ERASE', true );
}

/**
 * --------------------------------------------------------------------------
 * INTERNALLY USED IN FORM CONTROLS
 * --------------------------------------------------------------------------
 */

if ( ! defined( 'PLSE_INPUT_TYPES' ) ) {

    define( 'PLSE_INPUT_TYPES', array(
        'HIDDEN' => 'hidden',
        'BUTTON' => 'button',
        'TEXT' => 'text',
        'PHONE' => 'tel',
        'POSTAL' => 'postal',
        'EMAIL' => 'email',
        'URL' => 'url',
        'INT' => 'int',
        'FLOAT' => 'float',
        'TEXTAREA' => 'textarea',
        'CHECKBOX' => 'checkbox',
        'DATE' => 'date', // DD:MM:YEAR
        'TIME' => 'time', // HH:MM:AM/PM
        'DURATION' => 'duration', // custom duration field HH:MM:SS
        'SELECT_SINGLE' => 'select_single', // uses <select>
        'SELECT_MULTIPLE' => 'select_multiple', // uses <select multiple>
        'REPEATER' => 'repeater', // table of text fields for expandable lists
        'DATALIST' => 'datalist', // uses <datalist>
        'CPT' => 'cpt',
        'CAT' => 'cat',
        'IMAGE' => 'image',
        'AUDIO' => 'audio',
        'VIDEO' => 'video',
        'POST_WARNING' => 'post_warning', // warning in post relayed to plugin

    ) );

}

// slug for Custom Post Type <select> control in plugin settings
if ( ! defined( 'PLSE_CPT_SLUG' ) ) {
    define( 'PLSE_CPT_SLUG', '-cpt-slug' );
}

// slug for Category <select> control in plugin settings
if ( ! defined( 'PLSE_CAT_SLUG' ) ) {
    define ( 'PLSE_CAT_SLUG', '-cat-slug' );
}

/**
 * --------------------------------------------------------------------------
 * LOADER - loads classes depending on context
 * - admin menu options (options added)
 * - in a post (custom fields added)
 * - on a user-directed page (schema added)
 * --------------------------------------------------------------------------
 */

// *************************************************************
// TODO:
// TODO: DATE RANGE AND TIME RANGE VALIDATION
// TODO: VALIDATION FOR ALL METABOX FIELDS
// TODO: SANITIZE FOR ALL METABOX FIELDS
// TODO: PRIVATE ERRORS TO PLUGIN SETTINGS FROM RENDER, METABOX

// TODO: $field['err'] to compartmentalize

// TODO: remove option saving from metabox, just wp_data

// TODO: $check_xxxx for checking get_option('slug') outside of PLSE_Options
//
// TODO: convert $args to $field
// AUDIT: string constants
// AUDIT: code names
// AUDIT: css classes
// AUDIT: active jQuery

// TODO: secondary dashicons in metabox 
// 
// TODO: on reload, save the current open tab automatically
//
// TODO: copy Yoast Local SEO should bounce the user to the "address" tab, status message
// TODO: warning messages for field value
// TODO: warning for invalid minimal schema (required fields can't be filled)
// TODO:
// TODO: add Service schema
// TODO: add Product Review to schema
// TODO:
// *************************************************************

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

add_action( 'plugins_loaded', function () {

    /* 
     * Initialization, PHP and Yoast checks.
     * also includes shared utilities and constants.
     * initialized here so categories and tags add correctly to pages and custom post types
     */
    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/plse-init.php';
    $plse_init = PLSE_Init::getInstance();

    /*
     * Plugin options, global for the options page, metabox on posts, and graph generation 
     * for user-facing pages.
     */
    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options-data.php';

    // make sure the plugin can run
    if ( $plse_init->check_php() ) {  // adequate PHP versions

        if ( $plse_init->check_yoast() ) { // Yoast plugin installed

            if ( $plse_init->check_yoast_active() ) { // Yoast is activated

                if ( is_admin() ) {

                    global $pagenow;

                    // load datalists (e.g. list of countries)
                    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-datalists.php';

                    // load input field rendering (not needed outside admin)
                    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-fields.php';

                    // load options class
                    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options.php';

                     // Reorder menus. Put the menu for this plugin below the Yoast listing
                    add_filter('custom_menu_order', function() { return true; });
                    add_filter( 'menu_order', function ( $menu_order ) {

                        $yoast_pos = 0;
                        $plse_pos  = 0;

                        // find the position of Yoast
                        foreach ($menu_order as $key => $value ) {
                            if ( $value == YOAST_MENU_SLUG ) {
                                $yoast_pos = $key;
                            }
                            if ( $value == PLSE_SCHEMA_EXTENDER_SLUG ) {
                                $plse_pos = $key;
                            }
                        }

                        // move an array element to a new index
                        function move_element(&$array, $a, $b) {
                            $out = array_splice($array, $a, 1);
                            array_splice($array, $b, 0, $out);
                        }
                        move_element( $menu_order, $plse_pos, $yoast_pos + 1 );

                        return $menu_order;

                    } );

                    // load admin options to add menu in WP_Admin (menu needed for options page and metabo)
                    $plse_options = PLSE_Options::getInstance();

                    /*
                     * Decide whether to load options page (plugin settings), or metabox 
                     * custom fields (in post). 
                     * 
                     * NOTE: $pagenow is 'post.php' when editing drafts or saved 
                     * posts, but 'post-new.php' is used when the 'Add New' button is clicked 
                     * BEFORE there is any page draft. So, check for both.
                     * 
                     */
                     if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {

                        // load metabox class
                        require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-metabox.php';
                        $plse_metabox = PLSE_Metabox::getInstance();

                    } else {
                        
                    }

                } else {

                    /* 
                     * add Schema classes to Yoast graph on the viewing page only. As each 
                     * Schema class initializes, it will determe, via the is_needed() method, 
                     * whether it needs to add to the Yoast Schema graph. 
                     */
                    $plse_init->add_schemas();

                    /*
                     * add Categories and Tags to Pages so they can be used to select 
                     * Schemas (controlled in plugin options).
                     */
                    add_action( 'pre_get_posts', [ 'PLSE_Init', 'category_and_tag_archives' ] );

                }

            }

        }

    }

} );
