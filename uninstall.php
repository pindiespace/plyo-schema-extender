

<?php // exit if uninstall constant is not defined

/**
 * Uninstall the plugin
 * {@link https://digwp.com/2019/11/wordpress-uninstall-php/}
 */

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// include field constants
require_once PLSE_SCHEMA_EXTENDER_PATH .'/includes/plse-constants.php';

// require options fields array list

require_once PLSE_SCHEMA_EXTENDER_PATH . '/includes/admin/plse-options-data.php';

$options_data = new PLSE_Options_Data();
$options_fields = $options_data->get_options_fields();

// check options to see if meta-data should be deleted

// remove plugin options

foreach( $options_fields as $key => $field ) {
    $slug = $field['slug'];
    if (get_option( $slug ) ) delete_option( $slug );
}

// remove plugin transients

delete_transient( PLSE_TRANSIENT_SLUG );

// remove plugin cron events

/**
 * remove meta-data from posts (lengthy)
 * load the Schema file arrays statically
 * get all the custom post types
 */


$args = array(
    'public'   => true,
    '_builtin' => false,
 );

 $output = 'names'; // names or objects, note names is the default
 $operator = 'and'; // 'and' or 'or'

 $post_types = get_post_types( $args, $output, $operator ); 

 // get all defined Schema files, to get Schema fields

 $schema_fields = array();

 // TODO: get them all
 // TODO:
 // TODO:

 foreach ( $post_types as $post_type ) {
    //echo '<p>' . $post_type . '</p>';

    foreach ( $schema_fields as $field ) {
        // loop through each post type, deleting all Schema keys
        delete_metadata('post', 0, $field['slug'], '', true);
    }

 }

