

<?php // exit if uninstall constant is not defined

/**
 * Uninstall the plugin
 * {@link https://digwp.com/2019/11/wordpress-uninstall-php/}
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// include base file path (redefined, originally defined in plyo-schema-extender.php)
if ( ! defined( 'PLSE_SCHEMA_EXTENDER_PATH' ) ) {
    define( 'PLSE_SCHEMA_EXTENDER_PATH', dirname( __FILE__ ) );
}

/* 
 * include plyo-schema-extender, which includes plse-constants. We 
 * manually include plse-constants.php, since the plugins_loaded action 
 * hook isn't firing in uninstall.php
 */
require_once PLSE_SCHEMA_EXTENDER_PATH .'/includes/plse-constants.php';
require_once PLSE_SCHEMA_EXTENDER_PATH .'/plyo-schema-extender.php';

// require all the Schema files to delete individual meta fields
require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/plse-init.php';
$plse_init = PLSE_Init::getInstance();

// require options fields array list, since used in meta-data
require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options-data.php';
$options_data = PLSE_Options_Data::getInstance();

// remove plugin transients
delete_transient( PLSE_TRANSIENT_SLUG );

// remove plugin cron events


/**
 * ---------------------------------------------------------------------
 * Check plugin options to see if meta-data should be deleted
 * ---------------------------------------------------------------------
*/
if ( get_option( PLSE_UNINSTALL_META_DELETE )  == true) {

    // require options fields array list, since used in meta-data
    //require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options-data.php';
    //$options_data = PLSE_Options_Data::getInstance();

    ///////////////////////////////////
    ///////////////////
    update_option( PLSE_DEBUG, 'INSIDE META BIGLERRRRR' );
    //////////////////
    //////////////////////////////////

    // metabox uses plse-datalists.php
    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-datalists.php';

    // metabox uses plse-fields.php
    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-fields.php';

    // load the metabox class
    require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-metabox.php';
    $plse_metabox = PLSE_Metabox::getInstance();

    // get all defined Schema files, to get Schema fields
    $schema_list = $plse_init->get_available_schemas();

    // get all the Schema field slugs defined by the plugin
    foreach ( $schema_list as $schema_label ) {
        $schema_fields[] = $plse_metabox->load_schema_fields( $schema_label );
    }

    $args = array(
        'public'   => true,
        '_builtin' => false,
    );
    $output = 'names'; // names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'. 
    $post_types = get_post_types( $args, $output, $operator );

    // add relevant builtins, since we didn't do it above
    $post_types[] = 'page';
    $post_types[] = 'post';

    ///////////////////////////////////////////////////////////////////
    ////////////////
    $bob = array();
    //////////////$bob = $post_types;
    /////////////$bob[] = ' NUM:' . count( $post_types );
    ////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * delete all meta data from pages, posts, custom posts
     */
    foreach ( $post_types as $post_type ) {

        // get all the posts for a cpt 
        $posts = get_posts( 
            array(
            'post_type' => $post_type,
            //'_builtin' => true,
            'post_status' => 'publish',
            'numberposts' => -1
            // 'order'    => 'ASC'
            )
        );

        /////////////////////////////////////
        ///////////////////////
        $bob[] = 'NTH POST_TYPE:'. $post_type;
        ///////////////////////
        /////////////////////////////////////

        // loop through the posts, scan for all defined Schema fields, delete meta-data if present
        foreach ( $posts as $curr_post ) {

            // loop through each post type, deleting all Schema keys
            foreach ( $schema_fields as $field_array ) {

                $fields = $field_array['fields'];

                // https://developer.wordpress.org/reference/functions/metadata_exists/
                foreach ( $fields as $field ) {

                    if ( metadata_exists( 'game', $curr_post->ID, $field['slug'] ) ) {
                        $bob[] = '+++' . $post_type . '-' . $curr_post->ID . '-' . $field['slug'] . '-EXISTS-, ';
                        delete_metadata( $post_type, 0, $field['slug'], '', true );
                    } else {
                        $bob[] = '---' . $post_type . '-'. $curr_post->ID . '-' . $field['slug'] . '-NOT_EXIST-, ';
                    }

                }

            }

        }

    }

    ////////////////////////////////////////////////////
    ///////////////////////////////
    update_option( PLSE_DEBUG, $bob );
    ///////////////////////////////
    ////////////////////////////////////////////////////

}

/**
 * ---------------------------------------------------------------------
 * Check plugin options to see if admin options data should be deleted
 * ---------------------------------------------------------------------
 */
if ( get_option( PLSE_UNINSTALL_OPTIONS_DELETE ) == true ) {

    // require options fields array list for plugin admin configuration
    //require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options-data.php';

    /////////////$options_data = PLSE_Options_Data::getInstance();
    $options_fields = $options_data->get_options_fields();
    $toggle_fields = $options_data->get_toggles_fields();

    // remove plugin options
    foreach( $options_fields as $key => $field ) {
        $slug = $field['slug'];
        if ( get_option( $slug ) ) delete_option( $slug );
    }

} // end of plugin options tables deletes



