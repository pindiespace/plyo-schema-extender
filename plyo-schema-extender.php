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
 * -----------------------------------------------------------------------
 * 
**/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// We are running outside of the context of WordPress.
if ( ! function_exists( 'add_action' ) ) return;

// Current plugin name.
if ( ! defined( 'PLSE_SCHEMA_EXTENDER_NAME' ) ) {
    define ( 'PLSE_SCHEMA_EXTENDER_NAME', 'Plyo Schema Extender' );
}

/*
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'PLSE_SCHEMA_EXTENDER_VERSION', '1.0.0' );

if ( ! defined( 'PLSE_SCHEMA_PHP_MIN_VERSION' ) ) {
    define( 'PLSE_SCHEMA_PHP_MIN_VERSION', '5.6' );
}

/*
 * Basic Plugin description (for options page)
 */
if ( ! defined( 'PLSE_SCHEMA_OPTIONS_DESCRIPTION' ) ) {
    define( 'PLSE_SCHEMA_OPTIONS_DESCRIPTION', __( 'This plugin works with Yoast SEO and adds additional schema to the default schema provided by Yoast. Schemas can be added through a custom post type whose name matches the schema.org schema, or by creating a category name matching the schema name. Plugin is NOT compatible with other Schema plugins' ) );
}



// define the plugin slug for the admin options menu
if ( ! defined( 'PLSE_SCHEMA_EXTENDER_SLUG' ) ) {
    define( 'PLSE_SCHEMA_EXTENDER_SLUG', 'plyo-schema-extender' );
}

/*
 * -----------------------------------------------------------------------
 * Define file paths.
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
 * Yoast SEO constants.
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
 * LOCAL SEO DATA
 * --------------------------------------------------------------------------
 */

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
 * INTERNALLY USED IN FORM CONTROLS
 * --------------------------------------------------------------------------
 */

if ( ! defined( 'PLSE_INPUT_TYPES' ) ) {

    define( 'PLSE_INPUT_TYPES', array(
        'HIDDEN' => 'hidden',
        'TEXT' => 'text',
        'TEXTAREA' => 'textarea',
        'DATE' => 'date',
        'TIME' => 'time',
        'DATETIME' => 'datetime',
        'DATERANGE' => 'daterange',
        'POSTAL' => 'text',
        'PHONE' => 'tel',
        'EMAIL' => 'email',
        'URL' => 'url',
        'CHECKBOX' => 'checkbox',
        'SELECT_SINGLE' => 'select_single',
        'SELECT_MULTIPLE' => 'select_multiple',
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

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

add_action( 'plugins_loaded', function () {

    // initialize and decide what to load, or display error message
    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/plse-init.php';

    // placed here so categories and tags add to pages and custom post types
    $plse_init = PLSE_Init::getInstance();

    // only load if the Yoast Plugin is available
    if ( is_plugin_active( YOAST_PLUGIN ) ) {

        if ( is_admin() ) {

            global $pagenow;


            // global fields used in plugin options, also used by metabox
            require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options-data.php';

            // load options class
            require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options.php';

            /*
             * Reorder menus. Put this plugin menu item right below the
             * Yoast listing in the Admin menu.
             */
            add_filter('custom_menu_order', function() { return true; });
            add_filter( 'menu_order', function ( $menu_order ) {

                $yoast_pos = 0;
                $plse_pos  = 0;

                // find the position of Yoas
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

            // load admin options
            $plse_options = PLSE_Options::getInstance();
            //add_action('admin_menu', [ $plse_options, 'setup_options_menu'] );

            // decide whether to load options page (admin), or custom fields (in post)
            if ( $pagenow == 'post.php' ) {

                // load metabox class
                require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-metabox.php';
                $plse_metabox = PLSE_Metabox::getInstance();

            }

        } else {
            // add Categories and Tags to Pages
            add_action( 'pre_get_posts', [ 'PLSE_Init', 'category_and_tag_archives' ] );

        }

    }

} );
