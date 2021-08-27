<?php

/**
 * Common input field rendering for PLSE_Metabox and PLSE_Options.
 *
 * @since      1.0.0
 * @category   WordPress_Plugin
 * @package    PLSE_SCHEMA_Extender
 * @subpackage PlyoSchema_Extender/admin
 * @author     Pete Markeiwicz <pindiespace@gmail.com>
 * @license    GPL-2.0+
 * @link       https://plyojump.com
 */
class PLSE_Fields {

    /**
     * Store reference for singleton pattern.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $instance    static reference to initialized class.
     */
    static private $__instance = null;

    /**
     * PLSE_Init class instance.
     * 
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Init    $init    the PLSE_Init class
     */
    private $init = null;

    /**
     * See if we need to check each URL for validity (set in plugin options)
     * 
     * @since    1.0.0
     * @access   private
     * @var      PLSE_Datalists    $datalists    instance of PLSE_Datalists
     */
    private $datalists = null;

    /**
     * The the returned $option value for the selected checkbox or radio option.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $option_group    name for storing plugin options.
     */
    private $ON = 'on';

    /**
     * Maximum for meta repeater fields.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $REPEATER_MAX    max number of repeater fields
     */
    private $REPEATER_MAX = 1000;

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct() {

        // utilities
        $this->init = PLSE_Init::getInstance();

        // datalists, e.g. country name lists
        $this->datalists = PLSE_Datalists::getInstance();

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Fields    $self__instance
     */
    public static function getInstance () {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Fields();
        }
        return self::$__instance;
    }

    /**
     * -----------------------------------------------------------------------
     * UTILITIES
     * -----------------------------------------------------------------------
     */


    /**
     * -----------------------------------------------------------------------
     * RENDER INPUT FIELDS
     * -----------------------------------------------------------------------
     */

    /**
     * Render the <label> tag for input fields. 
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     * @return   string   $label   the rendered label in a string
     */
    public function render_label ( $field ) {

        // apply CSS to label, depending on type
        switch ( $field['type'] ) {

            default:
                $class = 'plse-option-description';
                break;
        }

        $label = '<label class="' . $class . '" for="' . $field['slug'] . '">' . $field['label'] . '</label>';

        return $label; // for error checks

    }

    /**
     * Render textlike fields (text, url, email, postal, number).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array $field name of field, state, additional properties
     * @return   string    $value    the stored option value, used to validate text field subtypes
     */
    public function render_simple_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );
        $type = $field['type'];

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";
        if ( isset( $field['size'] ) ) $size = $field['size']; else $size = '40';

        // max, min, and step for number fields - the full attribute, not just its value
        $min = ''; $max = ''; $float = '';
        if ( $type == PLSE_INPUT_TYPES['INT'] || $type == PLSE_INPUT_TYPES['FLOAT'] ) {
            if ( isset( $field['min'] ) ) $m = ' min="' . $field['min'] . '"';
            if ( isset( $field['max'] ) ) $m .= ' max="' . $field['max'] . '"';
            if ( isset ($field['step'] ) ) $m .= ' step="' . $field['step' ] . '"';
        }

        echo $this->render_label( $field );
        echo '<input title="'. $title .'" class="' . $class . '" type="' . $type . '" id="' . $slug . '" name="' . $slug . '" size="' . $size . '" value="' . $value . '" ' . $m . '/>';	

        return $value; // for additional error checks and output

    }

    /**
     * Render a textarea field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_textarea_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );
        $type = $field['type'];

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";
        if ( isset( $field['rows'] ) ) $rows  = $field['rows']; else $rows = '5';
        if ( isset( $field['cols'] ) ) $cols  = $field['cols']; else $cols = '60';

        echo $this->render_label( $field );
        echo '<textarea title="' . $title . '" class="' . $class . '" id="' . $slug . '" name="' . $slug .'" rows="' . $rows . '" cols="' . $cols . '">' . $value . '</textarea>';

        return $value;

    }


    /**
     * Render a Date field, 
     * - UI shows: dd:mm:yyyy in the UI
     * - $value stored in DB is: yyyy-mm-dd value, e.g. 2021-08-27
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value, with correct date format YYYY-MM-DD
     */
    public function render_date_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        echo $this->render_label( $field );

        // render the date field
        echo '<input title="' . $field['title'] . '" class="' . $class . '" id="' . $slug . '" type="date" name="' . $slug . '" value="' . $value . '">';

        return $value;

    }

    /**
     * Render a Time field, value always HH:MM:AM/PM.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string   $value    the field value, formatted HH:MM:AM/PM
     */
    public function render_time_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        echo $this->render_label( $field );

        // render the field
        echo '<input title="' . $field['title'] . '" id="' . $slug . '" type="time" name="' . $slug . '" value="' . $value . '">';
        
        return $value;
    }


    /**
     * Render a slider for time duration (0-24 hours, minutes, seconds)
     * The slider saves seconds, which need to be converted to ISO format 
     * in plse-schema-xxx.php classes.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field    field parameters, select
     * @param    number   $value   duration, in seconds.
     */
    public function render_duration_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];
        // if value is missing or some falsy thing, make it zero
        if ( ! $value ) $value = '0';

        // required fields
        $value = esc_attr( sanitize_text_field( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        // max is defined in seconds. 21600 = 6 hours default
        if ( isset( $field['max'] ) ) $max = $field['max']; else $max = '21600';

        echo $this->render_label( $field );

        // class is applied to wrapper, not the control itself (unlike simple text fields)
        echo '<div class="' . $class . '">';
        echo '<input title="' . $title. '" class="plse-duration-picker plse-slider-input" name="' . $slug . '" id="' . $slug . '" type="range" min="0" max="' . $max . '" step="1" value="' . $value . '">';
        echo '<span class="plse-slider-output"></span>'; // class online used in JS, not in CSS
        echo '</div>';
        echo '<p>Slide the slider, or use keyboard arrow keys to adjust.</p>';

        return $value;

    }

    
    /**
     * Render a checkbox.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value 'on' or not on
     */
    public function render_checkbox_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];
    
        // required fields
        $value = esc_html( sanitize_key( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        // if value is 'on', check the checkbox
        if ( $value == $this->init->get_checkbox_on() ) $checked = ' CHECKED'; else $checked = '';

        // render the field
        echo '<div class="' . $class . '">';
        echo $this->render_label( $field );
        echo '<input title="' . $title . '" style="display:inline-block;" type="checkbox" id="' . $slug . '" name="' . $slug . '" ' . $checked . '/>&nbsp;';

        // more descriptive text to the right of the checkbox
        echo '<span style="display:inline-block; width=90%;">' . $title . '</span>';
        echo '</div>';

        return $value;
    }

    /**
     * Create an input field similar to the old 'combox' - typing narrows the 
     * results of the list, but users can type in a value not on the list.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string   $value    the field value
     */
    public function render_datalist_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );
       
        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";
        if ( isset( $field['size'] ) ) $size = $field['size']; else $size = '30';

        // type is DATALIST, so look for subtype to type the input field
        if ( isset( $field['subtype'] ) ) $type = $field['subtype']; else $type = PLSE_INPUT_TYPES['TEXT'];

        // options in the list
        $option_list = $field['option_list'];

        if ( isset( $option_list ) ) {

            // build the datalist
            if ( is_array( $option_list ) ) {

                $list_id = $slug . '-data';
                echo $this->datalists->get_datalist( $option_list, $list_id );

            } else if ( is_string( $option_list ) ) {

                $list_id = 'plse-' . $option_list . '-data';

                $method = 'get_' . $option_list . '_datalist';
                if ( method_exists( $this->datalists, $method ) ) { 
                    echo $this->datalists->$method(); 
                }

            } else {

                echo __( 'Error: datalist for ' . $slug . ' not defined' );

            }

        }

        echo '<div class="' . $class . '">';
        echo $this->render_label( $field );
        echo '<input type="' . $type .'" title="' . $title . '" id="' . $slug . '" name="' . $slug . '" autocomplete="on" class="plse-datalist" size="' . $size . '" value="' . $value . '" list="' . $list_id . '">';
        echo '</div>';
        // message describing how to use a datalist
        echo '<p>' . __( 'Begin typing to find value, or type in your own value. Delete all text, click in the field, and re-type to search for a new value.' ) . '</p>';

        return $value;
    }


    /**
     * Render a pulldown menu with only one option selectable.
     * Requires an option list be defined in the field $field, or in PLSE_Datalists
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    string   $value    the field value
     */
    public function render_select_single_field ( $field ) {

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $field['value'] ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        // options in the list
        $option_list = $field['option_list'];

        if ( isset( $option_list ) ) {

            if ( is_array( $option_list ) ) {

                // converts array to <option>...</option>
                $options = $this->datalists->get_select( $option_list, $value );

            } else if ( is_string( $option_list ) ) {

                // get a standard <option>...</option> list, pass selected value
                $method = 'get_' . $option_list . '_select';
                if ( method_exists( $this->datalists, $method ) ) { 
                    $options = $this->datalists->$method( $value ); 
                }

            }

        }

        // since option_lists come from the plugin, not validated
        echo '<div class="' . $class .'">';
        echo $this->render_label( $field );
        echo '<select title="' . $field['title'] . ' id="' . $slug . '" name="' . $slug . '" class="plse-option-select-dropdown">' . "\n";
        echo $options;
        echo '</select>';
        echo '<p class="plse-option-select-description">' . __( 'Select one option from the list' ) . '</p></div>';

        return $value;

    }


    /**
     * Select multi, with prebuilt option list (scrolling list).
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_select_multiple_field ( $field ) {

        $slug  = sanitize_key( $field['slug'] );

        /*
         * multiple values present - get the actual option values out of their enclosing array
         * multiple values are stored as array( 'slug' => $value_array );
         */
        $values = $field['value'];

        // get the values array out of possible wrappers
        if ( isset( $values[ $slug ] ) ) {
            $v = $values[ $slug ]; // Settings API array( $array[slug1]=>array ) );
        }
        else if ( isset( $values[0] ) && is_array( $values[0] ) ) {
            $v = $values[0][ $slug ]; // metabox meta-data array=>(array[0]=>([slug1]=>(array),[slug2]=>(array)))
        }

        // required fields
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        $option_list = $field['option_list'];

        if ( isset( $option_list ) ) {

            if ( is_array( $option_list ) ) {

                // converts array to <option>...</option>
                $options = $this->datalists->get_select( $option_list, $v );

            } else {

                // get a standard <option>...</option> list
                $method = 'get_' . $option_list . '_select';
                if ( method_exists( $this->datalists, $method ) ) { 
                    $options = $this->datalists->$method( $v ); 
                }

            }

        }

        // note $slug[], which specifies multiple values stored in one option.
        echo '<div class="plse-option-select"><select multiple name="' . $slug .'[' . $slug . '][]" class="plse-option-select-dropdown" >' . "\n";
        echo $options;
        echo '</select>' . "\n";

        // add the field label
        echo '<label class="plse-option-select-description" for="' . $slug . '">' . $label . '<br>' . __( '(CTL-Click to deselect)') . '</label>';
        echo '</div>';

        return $values;

    }

    /**
     * Render Repeater field, which allows users to self-generate multiple entries.
     * Requires JavaScript to work.
     * 
     * {@link https://codexcoach.com/create-repeater-meta-box-in-wordpress/}
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field field parameters, select
     * @param    array    $value an array with multiple values
     */
    public function render_repeater_field ( $field ) {

        $values = $field['value'];
        $slug = sanitize_key( $field['slug'] );

        // URL field specifies an image
        $is_image = $field['is_image'];

        // adjust size of fields
        if ( isset( $field['size'] ) ) $size = $field['size']; else $size = '40';

        // adjust text field type (url, date, time...), which is 'subtype' for repeaters
        if ( isset( $field['subtype'] ) ) $type = $field['subtype']; else $type ='text';

        // maximum number of repeater fields allowed (adjusted if we have a datalist)
        $max = $this->REPEATER_MAX;

        // adjust table width to allow tiny thumbnail
        $img_thumb_class = ''; // added class if repeater fields specify images
        if( $is_image == true ) {
            $table_width = '100%';
            $img_thumb_class = ' plse-repeater-url-is-image';
        }
        else {
            $table_width = '70%';
        }

        /*
         * NOTE: $values is supposed to be an array, unlike most other fields stored in DB.
         * NOTE: a maximum number of added fields is calculated from the $option_list size, if present
         * TODO: UNIQUE-IFY the RESULTS (no duplicates)
         */

        // check if an option_list should be attached to repeater fields
        $option_list = $field['option_list'];
        $datalist_id = ''; // id for datalist, if option_list defined
        $datalist = '';  // storage for datalists, if option_list defined

        if ( isset( $option_list ) ) {

            if ( is_array( $option_list ) ) { // $option_list is an array

                $datalist_id = $slug . '-data';
                $datalist = $this->datalists->get_datalist( $option_list, $datalist_id );
                $max = count( $option_list ); // size of array

            } else { // option list specifies a standard list in PLSE_Datalists

                // load the datalist (note they must follow naming conventions)
                $method = 'get_' . $option_list . '_datalist';
                if ( method_exists( $this->datalists, $method ) ) { 
                    $datalist .= $this->datalists->$method();
                    $datalist_id = $option_list;
                    $datalist_id = 'plse-' . $datalist_id . '-data'; // $option list is the id value 
                }

                // get the size of the loaded datalist, set repeater max to that value
                $method = 'get_' . $option_list . '_size';
                if ( method_exists( $this->datalists, $method ) ) {
                    $max = $this->datalists->$method();
                }

            }

            // render the datalist into html
            echo $datalist;
            $list_attr = 'list="' . $datalist_id . '"';

        }

        /*
         * begin rendering the table with repeater options
         */
        ?>
        <div id="plse-repeater-<?php echo $slug; ?>" class="plse-repeater plse-meta-ctl-highlight">
            <div id="plse-repeater-max-warning" class="plse-repeater-max-warning" style="display:none;">You have reached the maximum number of values</div>
            <table class="plse-repeater-table" width="<?php echo $table_width; ?>" data-max="<?php echo $max; ?>">
                <tbody>
                    <!--default row, or rows from datatbase-->
                    <?php 
                    /*
                     * this creates a unique ID for each input field. Additional fields are added 
                     * using jQuery, and incremented from this count.
                     */
                    $count = 0;
                    if( is_array( $values ) ):

                        if ( $is_image ) $tdstyle = 'width:330px;'; else $tdstyle = '';

                        // create fields already in the database
                        foreach( $values as $repeater_value ) {

                            $repeater_value = esc_attr( $repeater_value );

                            if ( ! empty( $repeater_value ) ) {
                                $wroteflag = true; // field saved to DB was not empty

                                $err = '';

                                // if we are checking URLs, check them here
                                if ( $field['subtype'] == PLSE_INPUT_TYPES['URL'] && $this->check_urls ) { // no checks or message if field empty

                                    // try connecting to the supplied URL
                                    $result = $this->init->get_url_status( $repeater_value );
                                    $err   = $this->fields->add_status_to_field( $result['err'], $result['class'] );
                                    $repeater_value = $result['value'];

                                }

                            ?>

                            <tr>
                                <td style="<?php echo $tdstyle; ?>">
                                    <input id="<?php echo $slug . $count; ?>" name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input<?php echo $img_thumb_class; ?>" value="<?php if( $repeater_value != '' ) echo $repeater_value; ?>" size="<?php echo $size; ?>" placeholder="type in value" />
                                </td>
                                <td>
                                    <!-- media library button (children[0]) -->
                                    <?php
                                        if( $is_image ) { // repeater URL is an image
                                            echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug . $count . '" value="Upload Image" />';
                                        }
                                    ?>
                                    <!-- remove button (children[1]) -->
                                    <a class="button plse-repeater-remove-row-btn" href="#1">Remove</a>
                                    <?php if ( ! empty( $err ) ) echo $err; ?>
                                </td>
                            </tr>
                        <?php 
                            }
                        $count++; // increment count for unique input field ID
                        }
                        // default, when we saved an empty field to the DB
                        if ( ! $wroteflag ):
                            ?>
                            <tr>
                                <td>
                                    <input id="<?php echo $slug . $count; ?>" name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input<?php echo $img_thumb_class; ?>" value="<?php if($repeater_value != '') echo $repeater_value; ?>" size="<?php echo $size; ?>" placeholder="type in value" />
                                </td>
                                <td>
                                    <?php
                                        if( $is_image ) { // repeater URL is an image
                                            echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug . $count . '" value="Upload Image" />';
                                        }
                                    ?>
                                    <a class="button plse-repeater-remove-row-btn" href="#1">Remove</a>
                                    <?php if ( ! empty( $err ) ) echo $err; ?>
                                </td>
                            </tr>
                            <?php 
                            //field below is brand-new, never had a value
                        endif;
                    else: ?>
                    <tr class="plse-repeater-default-row" style="display: table-row">
                        <td>
                            <input id="<?php echo $slug . $count; ?>" name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input<?php echo $img_thumb_class; ?>" size="<?php echo $size; ?>" placeholder="<?php echo __( 'enter' ) . strtolower( $type ); ?>"/>
                        </td>
                        <td>
                            <?php
                                if( $is_image ) { // repeater URL is an image
                                    echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug . $count . '" value="Upload Image" />';
                                }
                            ?>
                            <a class="button plse-repeater-remove-row-btn button-disabled" href="#">Remove</a>
                            <?php if ( ! empty( $err ) ) echo $err; ?>
                        </td>
                    </tr>
                    <?php endif;
                    ?>
                    <!--invisible blank row, copied to create new visible row ID is filled in by jQuery when it is copied-->
                    <tr class="plse-repeater-empty-row" style="display: none">
                        <td>
                            <input id="<?php echo $slug; ?>" name="<?php echo $slug; ?>[]" type="<?php echo $type; ?>" <?php echo $list_attr; ?> class="plse-repeater-input<?php echo $img_thumb_class; ?>" size="<?php echo $size; ?>" placeholder="<?php echo __( 'enter ' ) . strtolower( $type ); ?>"/>
                        </td>
                        <td>
                            <?php 
                                // Note: we just write $slug for 'data_media' in media upload button. jQuery used to dynamically convert the 'data-media' attribute from $slug to $slug + row number
                                if( $is_image ) {
                                    echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug .'" value="Upload Image" />';
                                }
                            ?>
                            <a class="button plse-repeater-remove-row-btn" href="#">Remove</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        <p><a class="button plse-repeater-add-row-btn" href="#">Add another</a></p>
        <?php 
            if ( isset( $option_list ) ) {
            echo '<p>' . __( 'Begin typing to find value, or type in your own value. Delete all text, click in the field, and re-type to search for a new value.' ) . '</p>';
            }
            if ( $is_image ) echo __( '<p>' . __( 'Previously saved URL values have their status marked. For images, hit the tab key after entering to check if a just-entered image is valid. Otherwise, update and reload the page to confirm.' ) . '</p>' );

        ?>
        </div>

        <?php 

        return $values;

    }


    /**
     * Render an image with its associated URL field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string    $value    the URL of the image
     */
    public function render_image_field ( $field ) {

        $slug  = sanitize_key( $field['slug'] );
        $value = $field['value'];

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $value ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( esc_url_raw( $field['value'], ['http','https'] ) );
        if ( ! $this->init->is_url( $value ) ) {
            $err = $this->fields->add_status_to_field( __( 'not valid url' ) );
        }

        $title = esc_html( $field['title'] );
       
        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";
        if ( isset( $field['size'] ) ) $size = $field['size']; else $size = '24';
        if ( isset( $field['width'] ) ) $width = $field['width']; else $width = '128';
        if ( isset( $field['height'] ) ) $heigh = $field['height']; else $height = '128';

        echo '<div class="' . $class . '">'; // highlights overall control
        echo '<table><tr>';
        echo '<td class="plse-input-image-col">';

        if ( $value ) {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="' . $slug . '-img-id" src="' . $value . '" width="' . $width . '" height="' . $height . '">';
        } else {
            echo '<img title="' . $title . '" class="plse-upload-img-box" id="'. $slug . '-img-id" src="' . $this->init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }

        echo '</td><td class="plse-image-upload-col">';

        echo '<div>' . __( 'Image URL in WordPress' ) . '</div>';
        echo '<div>';

        // URL text field (which is what is actually saved to DB)
        echo '<input type="text" name="' . sanitize_key( $slug ) . '" id="' . $slug . '" value="' . $value . '">';

        // media library button (ajax call)
        echo '<input title="' . $title . '" type="button" class="button plse-media-button" data-media="'. $slug . '" value="Upload Image" />&nbsp;';

        if ( ! empty( $err ) ) echo $err;

        echo '</td></tr></table></div>';

    }

    /**
     * Render an embedded audio field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field   field parameters
     */
    public function render_audio_field ( $field ) {

        $slug = sanitize_key( $field['slug'] );
        $value = $field['value'];

        // if value is an array, return the first value only (can happen with post meta-data)
        if ( is_array( $value ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( esc_url_raw( $field['value'], ['http','https'] ) );
        if ( ! $this->init->is_url( $value ) ) {
            $err = $this->fields->add_status_to_field( __( 'not valid url' ) );
        }

        // test URLs: https://www.soundhelix.com/audio-examples

        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        echo '<div class="plse-audio-metabox plse-meta-ctl-highlight">';

        echo '<audio controls>';
        if ( stripos( $value, '.mp3') !== false ) echo '<source src="' . $value . '" type="audio/mpeg">';
        else if ( stripos( $value, '.wav' ) !== false ) echo '<source src="' . $value . '" type="audio/wav">';
        else if ( stripos( $value, '.ogg') !== false )  echo '<source src="' . $value . '" type="audio/ogg">';
        else echo __( 'audio field not supported in this version' );
        echo '</audio>';

        echo '</div>';

    }


    /**
     * Video URL also captures a thumbnail image.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field field parameters, select
     * @param    string    $value    the URL of the video (YouTube or Vimeo supported)
     */
    public function render_video_field ( $field ) {

        $slug = sanitize_key( $field['slug'] );
        $value = $field['value'];

        $title = esc_attr( $field['title'] );

        /**
         * create the thumbnail URL
         * {@link https://ytimg.googleusercontent.com/vi/<insert-youtube-video-id-here>/default.jpg}
         */ 
        echo '<div class="plse-video-metabox plse-meta-ctl-highlight">';
        // add a special class for JS to the URL field for dynamic video embed
        $field['class'] = 'plse-embedded-video-url';
        $field['size'] = '72'; // same width as video + thumbnail takes up onscreen
        $field['type'] = 'URL';
        if ( is_array( $value ) ) $value = $value[0];
        $value = esc_url( $value );

        echo '<table style="width:100%"><tr>';
        // create the input field for the url
        echo '<td colspan="2" style="padding-bottom:4px;">' . $this->render_url_field( $field, $value ) . '</td>';
        echo '</td></tr><tr><td style="width:50%; text-align:center;position:relative">';
        if ( $value ) {
            // get a thumbnail image from the video URL
            $thumbnail_url = esc_url( $this->init->get_video_thumb( $value ) );
            // clunky inline style removes offending hyperlink border see with onblur event
            echo '<a href="' . $value . '" style="display:inline-block;height:0px;"><img title="' . $title . '" class="plse-upload-repeater-img-box" id="' . $slug . '-img-id" src="' . $thumbnail_url . '" width="128" height="128"></a>';
        } else {
            echo '<img title="' . $title . '" class="plse-upload-repeater-img-box" id="'. $slug . '-img-id" src="' . $this->init->get_default_placeholder_icon_url() . '" width="128" height="128">';
        }
        echo '</td><td class="plse-auto-resizable-iframe" style="text-align:center;">';
        echo '<div class="plse-embed-video"></div></td><tr><td style="width:50%;text-align:center">';
        echo __( 'Thumbnail' ) . '</span>';
        echo '</td><td style="width:100%;text-align:center;">';
        echo __( 'Video Player' );
        echo '</td></tr></table>';
        echo '<p>' . __( 'Supports YouTube and Vimeo. Enter the video url, the hit the "tab" key to check if the video embed and thumbnail are valid. Schema will use both the video url, and the default thumbnail defined by the video service.' ) . '</p>';

        echo '</div>';

    }


    /**
     * Render an input field, type="number" with integers only.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field field parameters, select
     * @param    string    $value    an integer value
     */
    public function render_int_field ( $field ) {
        if ( isset( $field['min'] ) ) $field['min'] = (int) $field['min'];
        if ( isset( $field['max'] ) ) $field['max'] = (int) $field['max'];
        if ( isset( $field['step'] ) ) $field['step'] = (int) $field['step'];
        $this->render_simple_field( $field );
    }

    /**
     * Render an input field, type="number" with floating-point only
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field field parameters, select
     * @param    string    $value    an floating-point value, optionally decimal places
     */
    public function render_float_field ( $field ) {
        if ( isset( $field['min'] ) ) $field['min'] = (float) $field['min'];
        if ( isset( $field['max'] ) ) $field['max'] = (float) $field['max'];
        if ( isset( $field['step'] ) ) $field['step'] = (float) $field['step'];
        $this->render_simple_field( $field );
    }

    /**
     * -----------------------------------------------------------------------
     * ERROR MESSAGES
     * Resport field validity and additional checks (e.g. valid URL)
     * -----------------------------------------------------------------------
     */

    /**
     * Add an error description next to a Schema field. This should be used 
     * for specific error messages relative to the field, not if the field is empty.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $msg  error message
     * @param    string    $status_class controls appearance of message
     * @return   string    wrap the error message in HTML for display
     */
    public function add_status_to_field ( $msg = '', $status_class = 'plse-input-msg-caution' ) {
        return '<span class="plse-input-msg ' . $status_class .'">' . $msg . '</span><br>';
    }



}