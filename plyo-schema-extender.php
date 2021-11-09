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
 * Install and Uninstall flags
 * -----------------------------------------------------------------------
 */

// flag indicating that the plugin has run at least once
if ( ! defined( 'PLSE_INSTALL_STATUS' ) ) {
    define( 'PLSE_INSTALL_STATUS', 'plse_options_old' );
}

// value written on activation installed plugin into PLSE_INSTALL_STATUS
if ( ! defined( 'PLSE_INSTALL_ACTIVATED' ) ) {
    define( 'PLSE_INSTALL_ACTIVATED', 'activated' );
}

// remove option values during plugin uninstall
if ( ! defined( 'PLSE_UNINSTALL_OPTIONS_DELETE' ) ) {
    define( 'PLSE_UNINSTALL_OPTIONS_DELETE', 'plse_uninstall_options_delete' );
}

// remove meta-data for schema from pages and posts during uninstall
if ( ! defined( 'PLSE_UNINSTALL_META_DELETE' ) ) {
    define( 'PLSE_UNINSTALL_META_DELETE', 'plse_uninstall_meta_delete' );
}

// fire this function on activate
register_activation_hook( __FILE__, 'set_firsttime_defaults' );

/**
 * --------------------------------------------------------------------------
 * LOADER - loads classes depending on context
 * - admin menu options (options added)
 * - in a post (custom fields added)
 * - on a user-directed page (schema added)
 * --------------------------------------------------------------------------
 */

// *************************************************************

// AUDIT: string constants
// AUDIT: code names
// AUDIT: css classes
// AUDIT: active jQuery

// TODO: secondary dashicons in metabox 
// 
// TODO: on reload, save the current open tab automatically
//
// TODO: copy Yoast Local SEO should bounce the user to the "address" tab, status message
// TODO: warning for invalid minimal schema (required fields can't be filled)
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
    require_once PLSE_SCHEMA_EXTENDER_PATH .'/includes/plse-constants.php';
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
                    add_action( 'pre_get_posts', [ $plse_init, 'category_and_tag_archives' ] );

                }

            }

        }

    }

} );

/**
 * callback for register activation hook. When the plugin is activated 
 * for the first time, set a flag to set a few defaults in options
 * 
 * @since    1.0.0
 * @access   public
 */
function set_firsttime_defaults () {

    // make sure user has permissions
    if ( ! current_user_can( 'activate_plugins' ) ) return;

    // make sure referrer is correct
    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "activate-plugin_{$plugin}" );

    // if this is a new install (options flag set)
    if ( ! get_option( PLSE_INSTALL_STATUS ) ) {

        if ( is_admin() && current_user_can( 'administrator' ) ) {

            // add values for a few constants
            // TODO:

        }

    }

}