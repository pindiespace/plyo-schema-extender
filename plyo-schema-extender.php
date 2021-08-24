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
 * SLUG CONSTANTS
 * --------------------------------------------------------------------------
 */

if ( ! defined( 'PLSE_CSS_SLUG' ) ) {
    define( 'PLSE_CSS_SLUG', 'plse-css-' );
}

if ( ! defined( 'PLSE_OPTIONS_SLUG' ) ) {
    define ( 'PLSE_OPTIONS_SLUG', 'plse-settings-' );
}

if ( ! defined( 'PLSE_METABOX_SLUG' ) ) {
    define( 'PLSE_METABOX_SLUG', 'plse-metabox-' );
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
        'TEXTAREA' => 'textarea',
        'DATE' => 'date', // DD:MM:YEAR
        'TIME' => 'time', // HH:MM:AM/PM
        'DURATION' => 'duration', // custom duration field HH:MM:SS
        'POSTAL' => 'text',
        'PHONE' => 'tel',
        'EMAIL' => 'email',
        'URL' => 'url',
        'CHECKBOX' => 'checkbox',
        'SELECT_SINGLE' => 'select_single', // uses <select>
        'SELECT_MULTIPLE' => 'select_multiple', // uses <select multiple>
        'REPEATER' => 'repeater', // table of text fields for expandable lists
        'DATALIST' => 'datalist', // uses <datalist>
        'CPT' => 'cpt',
        'CAT' => 'cat',
        'IMAGE' => 'image',
        'AUDIO' => 'audio',
        'VIDEO' => 'video',
        'INT' => 'int',
        'FLOAT' => 'float'
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
// TODO: VALIDATION FOR ALL METABOX FIELDS
// TODO: SANITIZE FOR ALL METABOX FIELDS
// TODO: PRIVATE ERRORS TO PLUGIN SETTINGS FROM RENDER, METABOX

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

                    // load options class
                    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options.php';

                    // load datalists (e.g. list of countries)
                    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-datalists.php';

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

                    // decide whether to load options page (admin), or metabox custom fields (in post)
                    if ( $pagenow == 'post.php' ) {

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
