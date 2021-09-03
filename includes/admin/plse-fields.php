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
     * Maximum duration.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $DURATION_MAX    max number of repeater fields
     */
    private $DURATION_MAX = '21600';

    /**
     * Maximum for integer fields.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $INT_MAX    max allowed valued for 32-bit integer
     */
    private $FLOAT_MAX = 1e+80;

    /**
     * CSS error class.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $ERROR_CLASS    CSS formatting for error message
     */
    private $ERROR_CLASS = 'plse-input-msg-err';

    /**
     * CSS caution class.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $ERROR_CLASS    CSS formatting for caution message
     */
    private $CAUTION_CLASS = 'plse-input-msg-caution';

    /**
     * CSS ok, validated class.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string    $ERROR_CLASS    CSS formatting for ok, validated message
     */
    private $OK_CLASS = 'plse-input-msg-ok';

    /**
     * Check plugin options to see if we need to dynamically check URLs.
     * 
     * @since    1.0.0
     * @access   private
     * @var      string|null    $check_urls    if not null, check URLS
     */
    private $check_urls = null;

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct() {

        // utilities
        $this->init = PLSE_Init::getInstance();

        // datalists, e.g. country name lists
        $this->datalists = PLSE_Datalists::getInstance();

        // check plugin options to configure validation and rendering
        $this->check_options();

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
     * VALIDATION
     * -----------------------------------------------------------------------
     */

    /**
     * Check validation options from plugin settings relevant to field rendering
     * 
     * @since    1.0.0
     * @access   public
     */
    public function check_options() {
        // see if URLs should be actively checked for validity (e.g. 404 errors)
        $this->check_urls = get_option( PLSE_CHECK_URLS_SLUG );
    }

    /**
     * Get value, either an array or simple value
     * 1. Most metabox fields
     *   - simple value, text, number
     *   -  Array ( [0] => MultiPlayer [1] => SinglePlayer [2] => )
     * 2. metabox multi-select <select multiple...>
     *   - Array ( [0] => Array ( [plyo-schema-extender-game-operating_system] => Array ( [0] => steamos ) ) )
     * 3. Options (Settings API)
     *   - simple value
     *   . Array ( [plse-settings-game-cpt-slug] => Array ( [0] => game ) )
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $field the incoming value, which may be array, string, etc.
     * @return   mixed    the value, removed from wrapper arrays, either an array, string, number
     */
    public function parse_value ( $field ) {

        $val = $field['value'];
        if ( is_array( $val ) ) {
            if ( isset( $val[0] ) ) {
                if ( isset( $val[0][ $field['slug'] ] ) ) { 
                    ///////echo '$val[0][ $field[\'slug\'] ]';
                    return $val[0][ $field['slug'] ]; // indexed array under slug
                } else {
                    /////echo '$val[0]';
                    return $val; // simple indexed array
                }
            } else if ( isset( $val[ $field['slug'] ] ) ) {
                /////echo '$val[ $field[\'slug\'] ]';
                /////print_r ($val[ $field['slug' ] ]);
                return $val[ $field['slug' ] ];
            }
        }

        /////echo 'SIMPLE VALUE';
        return $val;
    }

    /**
     * Get field value, checking plugin settings via Settings API for globals
     * 
     * @since    1.0.0
     * @access   public
     * @param    array     $field    field descriptor
     * @return   mixed     $value    value from plugin settings, if present
     */
    public function get_field_value ( &$field ) {

        // field is empty and there is a global settings equivalent, use it
        if ( empty( $field['value'] ) ) {
            $field['value'] =  get_option( $field['slug'] );

            // if we were meta, and used settings, flag it
            if ( ! empty( $field['value'] ) && $field['wp_data'] == PLSE_DATA_POST_META ) {
                $field['err'] = $this->add_status_to_field( __( 'used global settings'), $this->CAUTION_CLASS );
            }

        }
        $value = $this->parse_value( $field );
        return $value;
    }

    /**
     * Field is required.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field
     */
    public function is_required ( $field ) {
        if ( ! empty( $field['value'] ) && $field['required'] == 'required') {
           return true;
        }
        return false;
    }

    /**
     * Field values were changed by sanitization.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field
     */
    public function was_sanitized ( $field, $val2 ) {
        $val1 = $field['value']; // space-stripping not sanitization
        if ( is_array( $val1 ) ) {
            foreach ( $val1 as $key => $v ) {
                if ( ! isset( $val2[ $key ] ) ) return true;
                if ( $v != $val2[$key] ) return true;
            }
        } else {
            // if the field is a string, and was not empty
            if ( ! empty( $field['value'] ) ) {

                // if the field changed after processing (aside from whitespace stripping)
                if ( is_string( $value ) && trim( $field['value'] ) != $value ) {
                //if ( $field['value'] != $value ) {
                    return true;
                }
            }
        }
 
        return false;
    }

    /**
     * Validate for letters, numbers, spaces only.
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in   incoming variable
     * @return   boolean  if alphanumeric, return true, else false
     */
    public function is_alphanumeric ( $in ) {
        if ( preg_match('/^[a-zA-Z0-9\s]+$/', $out ) ) {
            return $in;
        }
        return false;
    }

    /**
     * Postal (alphanumeric) validation.
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in    incoming variable
     * @return   boolean  if alphanumeric, return true, else false
     */
    public function is_postal ( $in ) {
      return ctype_alnum ( $in );
    }

    /**
     * Phone number validation, after woocommerce (not actually tested).
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in    incoming variable
     * @return   boolean  if phone syntax, return true, else false
     */
    public function is_phone ( $in ) {
        if ( preg_match('/^\(?([0-9]{3})\)?[-]?([0-9]{3})[-]?([0-9]{4})$/', $in ) ) {
            return true;
        }
        return false;
    }

    /**
     * Email validation accd to WP (not RFC compliant)
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in    incoming value
     * @return   boolean  if    is email syntax, return true, else false
     */
    public function is_email ( $in ) {
        return is_email( $in );
    }

    /**
     * numeric validation of string.
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in   incoming value
     * @return   boolean  if can be converted to number, return true, else false
     */
    public function is_number( $in ) {
        return is_numeric( $in );
    }

    /**
     * integer validation of string.
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in   incoming value
     * @return   boolean  if can be converted to number, return true, else false
     */
    public function is_int ( $in ) {
        $number = filter_var( $in, FILTER_VALIDATE_INT );
        return ( $number !== false );
    }

    /**
     * float validation of string.
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in   incoming value
     * @return   boolean  if can be converted to float, return true, else false
     */
    public function is_float ( $in ) {
        $number = filter_var( $in, FILTER_VALIDATE_FLOAT );
        return ( $number !== false );
    }

    /**
     * URL string validation (not tested to see if it works)
     * 
     * @since    1.0.0
     * @access   public
     * @param    mixed    $in   incoming value
     * @return   boolean  if can act as a URL, return true, else false
     */
    public function is_url( $in ) {
        return (bool) parse_url( $in );
    }

    /**
     * Check datestring with format:
     * yyyy-mm-dd (what is returned from HTML5 type=date fields)
     * by converting to timestamp (strtotime()), then using wp_checkdate()
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $in    the date, in yyyy-mm-dd format
     * @return   boolean    if valid, return true, else false
     */
    public function is_date ( $in ) {

        if( strtotime( $in ) ) {

            // assumes yyyy-mm-dd string format!
            $dd = explode( '-', $in );

            // check if the string can be converted to a Gregorian date
            return wp_checkdate( $dd[1], $dd[2], $dd[0], $in );

        }

        return false;

    }

    /**
     * Check if we have a valid time string
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    the time, in string format
     * @return   boolean    if valid time, return true, else false
     */
    public function is_time ( $in ) {
        return strtotime( $in );
    }

    /**
     * Check if we have a duration (seconds, s must be a number)
     * 
     * @since    1.0.0
     * @access   public
     * @param    string     $in    the time, in string format
     * @return   boolean    if valid time, return true, else false
     */
    public function is_duration ( $in ) {
        return is_numeric( $in );
    }

    /**
     * -----------------------------------------------------------------------
     * URL VALIDATIONS
     * See if URL resolves to a real Internet address
     * {@link https://stackoverflow.com/questions/3799134/how-to-get-final-url-after-following-http-redirections-in-pure-php/7555543}
     * -----------------------------------------------------------------------
     */

    /**
     * get_redirect_url()
     * 
     * Gets the address that the provided URL redirects to, or just returns the original 
     * URL if there is no redirection. Returns errors for common HTTP/HTTPS errors
     * 
     * Modified from:
     * {@link https://stackoverflow.com/questions/3799134/how-to-get-final-url-after-following-http-redirections-in-pure-php/7555543}
     *
     * @since    1.0.0
     * @access   public
     * @param    string    $url   (http: or https:)
     * @return   string    if ok, return URL (redirected), else false
     */
    public function get_redirect_url ( $url ) {

        $redirect_url = null;

        // break up the url into its components
        $url_parts = @parse_url( $url );
        if ( ! $url_parts ) return false;
        if ( ! isset( $url_parts['host'] ) ) return false; //can't process relative URLs
        if ( ! isset( $url_parts['path'] ) ) $url_parts['path'] = '/';

        // url structure valid, so try to connect
        $sock = @fsockopen( $url_parts['host'], ( isset($url_parts['port'] ) ? (int)$url_parts['port'] : 80 ), $errno, $errstr, 30 );
        if ( ! $sock ) return 'Error: No Response';

        // build the request
        $request = "HEAD " . $url_parts['path'] . ( isset($url_parts['query'] ) ? '?' . $url_parts['query'] : '' ) . " HTTP/1.1\r\n";
        $request .= 'Host: ' . $url_parts['host'] . "\r\n";
        $request .= "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36\r\n";
        $request .= "Connection: Close\r\n\r\n";
        fwrite( $sock, $request );
        $response = '';

        // wait for a response
        while ( ! feof( $sock ) ) $response .= fread($sock, 8192);
        fclose($sock);

        // not a redirect, but if we get a 200 response with no redirects, the URL is valid, so return it
        if ( stripos( $response, '200 OK') !== false ) {
            return $url;
        }

        // if the URL doesn't exist, return an error
        if ( stripos( $response, '404 Not Found' ) !== false ) {
            return 'Error: 404 Not Found';
        }

        // if the server is valid, but request isn't return an error
        if ( stripos( $response, '400 Bad Request' ) !== false ) {
            return 'Error: 400 malformed URL';
        }

        // if the server is valid, but request is invalid (e.g. not logged in), return an error
        if ( stripos( $response, '403 Forbidden' ) !== false ) {
            return 'Error: 403 forbidden';
        }

        // if the server is valid, but it doesn't return a value 
        //if ( stripos( $response, '304 Not Modified' ) !== false ) {
        //    return 'Error: clear your cache';
        //}

        // if the URL was moved, return the redirect
        if ( preg_match( '/^Location: (.+?)$/m', $response, $matches ) ) {
            if ( substr( $matches[1], 0, 1 ) == "/" )
                return $url_parts['scheme'] . "://" . $url_parts['host'] . trim( $matches[1] );
            else
                return trim( $matches[1] );

        } else {
            return false;
        }

    }

    /**
     * get_all_redirects()
     * 
     * Follows and collects the original URL, plus all redirects, in order, for the given URL.
     * 
     * Modified from:
     * {@link https://stackoverflow.com/questions/3799134/how-to-get-final-url-after-following-http-redirections-in-pure-php/7555543}
     * 
     * @since    1.0.0
     * @access   private
     * @param    string   $url
     * @return   array    $redirects
     */
    public function get_all_redirects( $url ) {
        $redirects = array();
        while ( $newurl = $this->get_redirect_url( $url ) ) {
            if ( in_array( $newurl, $redirects ) ) { break; }
            $redirects[] = $newurl;
            $url = $newurl;
        }

        return $redirects;
    }

    /**
     * get_final_url()
     * 
     * Gets the address that the URL ultimately leads to.
     * Returns $url itself if it isn't a redirect,
     * or 'Error: No Response'
     * or 'Error: 404 Not Found',
     * 
     * Modified from:
     * {@link https://stackoverflow.com/questions/3799134/how-to-get-final-url-after-following-http-redirections-in-pure-php/7555543}
     *
     * @since    1.0.0
     * @access   public
     * @param    string $url
     * @return   string|false if OK, return the final URL, else return false
     */
    public function get_final_url ( $url ) {

        // if a URL is typed in without http|https add it so we can test (altered URL not returned by this test).
        if ( stripos( $url, 'http' ) === false ) $url = 'http://' . $url;

        // check if URL is valid, following redirects as necesary
        $redirects = $this->get_all_redirects( $url );
        if ( count( $redirects) > 0 ) {
            return array_pop( $redirects );
        } else {
            return false;
        }

    }

    /**
     * Complete URL status check and reporting.
     * 
     * @since    1.0.0
     * @access   public
     * @param    string    $url    the http/https address to check
     * @param    
     */
    public function get_url_status ( $url ) {

        $err = ''; // just a string

        // check if the URL (or a redirect) is reachable
        $valid = $this->get_final_url( $url );

        if ( ! $valid ) { // a false was returned, nothing came back (Internet down?)

            $err = __( 'status unknown (check connection) for:' ) . $url; // caution
            $class = 'plse-input-msg-caution';

        } else {

            if ( stripos( $valid, 'Error:') !== false ) {
                $err = $valid;
                $class = $this->ERROR_CLASS;
            } else if ( $valid != $url ) {
                if ( stripos( $url, 'http:') !== false && stripos( $url, 'https') !== false ) {
                    $err   = __( 'valid, url was changed to https' );
                    $class = $this->CAUTION_CLASS;
                    $url = $valid; // convert http to https
                } else {
                    $err = __( 'valid, redirected, change to: ' ) . $valid;
                    $class = 'plse-input-msg-caution';
                }
            } else {
                $err = __( 'validated');
                $class = $this->OK_CLASS;
            }

        }

        // return the status, and altered (if changed) URL value
        return array(
            'err' => $err,
            'value' => $url,
            'class' => $class,
        );

    }

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
                $class = 'plse-label-description';
                break;
        }

        $label = '<label class="" style="display:block;" for="' . $field['slug'] . '"><span class="' . $class . '">' . $field['label'] . ':</span></label>';

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
    public function render_simple_field ( &$field ) {

        $value = $this->get_field_value( $field );

        // required fields
        $value = esc_html( sanitize_text_field( $value ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );
        $type = esc_html( $field['type'] );

        // optional textlike field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";
        if ( isset( $field['size'] ) ) $size = $field['size']; else $size = '40';

        // add extra attributes if type 'INT' or type 'FLOAT'
        if ( $type == PLSE_INPUT_TYPES['INT'] || $type == PLSE_INPUT_TYPES['float'] ) {
            $m = ' min="' . $field['min'] . '" max="' . $field['max'] . '" step="' . $field['step'] . '"';
        }

        echo $this->render_label( $field );
        echo '<input title="'. $title .'" class="' . $class . '" type="' . $type . '" id="' . $slug . '" name="' . $slug . '" size="' . $size . '" value="' . $value . '" ' . $m . '/>';	

        return $value; // for additional error checks and output

    }

    /**
     * Render a hidden field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field parameters
     */
    public function render_hidden_field ( $field ) {
        $value = $this->render_simple_field( $field );
        if ( $this->was_sanitized( $field, $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'hidden field was sanitized:') . $field['slug'], $this->ERROR_CLASS );
        }
        if ( ! empty ( $field['err'] ) ) echo $field['err'];
        return $value;
    }

    /**
     * Render a field with text.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field parameters
     */
    public function render_text_field( $field ) {
        $value = $this->render_simple_field( $field );
        if ( $this->was_sanitized( $field, $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'text field was sanitized'), $this->CAUTION_CLASS );
        }
        if ( ! empty( $field['err'] ) ) echo $field['err'];
        return $value;
    }

    /**
     * Render a field with a postal code.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field parameters
     */
    public function render_postal_field( $field ) {
        $value = $this->render_simple_field( $field );
        if ( $this->was_sanitized( $field, $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'field was sanitized' ), $this->ERROR_CLASS );
        }
        if ( ! empty( $value ) && ! $this->is_postal( $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'this is not a valid postal code' ), $this->ERROR_CLASS );
        }
        // render error messages next to field, if present (errors also appear on top of page)
        if ( ! empty( $field['err'] ) ) echo $field['err'];
        return $value;
    }

    /**
     * Render phone input field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_phone_field ( $field ) {
        $value = $this->render_simple_field( $field );
        if ( $this->was_sanitized( $field, $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'field was sanitized'), $this->ERROR_CLASS );
        }
        if ( ! empty( $value ) && ! $this->is_phone( $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'Invalid phone'), $this->ERROR_CLASS );
        }
        // render error messages next to field, if present (errors also appear on top of page)
        if ( ! empty( $field['err'] ) ) echo $field['err'];
        return $value;
    }

    /**
     * render email input field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_email_field ( $field ) {
        $value = $this->render_simple_field( $field );
        if ( $this->was_sanitized( $field, $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'field was sanitized'), $this->ERROR_CLASS );
        }
        if ( ! empty( $value) && ! $this->is_email( $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'Invalid email' ), $this->ERROR_CLASS );
        }
        // render error messages next to field, if present (errors also appear on top of page)
        if ( ! empty( $field['err'] ) ) echo $field['err'];
        return $value;
    }

    /**
     * Render a URL field (http or https), optionally checking for validity.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string   $value    the field value
     */
    public function render_url_field ( $field ) {
        $value = $this->render_simple_field( $field );
        if ( ! empty( $value ) && $this->check_urls ) { // no checks or message if field empty
            // check error, render status
            $result = $this->get_url_status( $value );
            if ( ! empty( $result['err'] ) ) {
                $field['err'] = $this->add_status_to_field( $result['err'], $result['class'] );
            } else {
                $value = $result['value']; // might be modified
            }
        }
         // render error messages next to field, if present (errors also appear on top of page)
         if ( ! empty( $field['err'] ) ) echo $field['err'];
         return $value;

    }


    /**
     * Render an integer field, validating min, max, float
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_int_field ( $field ) {

        $value = $this->get_field_value( $field );
        $value = (int) $value;

        // adjust field type
        $field['type'] = 'number'; // change since there is no type='int'

        // range attributes
        if ( isset( $field['min'] ) ) $field['min'] = (int) $field['min']; else $field['min'] = 0;
        if ( isset( $field['max'] ) ) $field['max'] = (int) $field['max']; else $field['max'] = PHP_INT_MAX - 1;
        if ( isset( $field['step'] ) ) $field['step'] = (int) $field['step']; else $field['step'] = 1;

        if ( ! empty( $value ) ) {
            if ( ! $this->is_number( $value ) ) {
                $field['err'] = $this->add_status_to_field( __( 'not a number' ), $this->ERROR_CLASS );
            }
            if (  $value > $field['max'] ) {
                $field['err'] = $this->add_status_to_field( __( 'value greater than maximum' ), $this->ERROR_CLASS );
            }
            if ( $value < $field['min'] ) {
                $field['err'] = $this->add_status_to_field( __( 'value less than minimum' ), $this->ERROR_CLASS );
            }
        } else {
            $field['value'] = $value; // cleaned up
        }

        $value = $this->render_simple_field( $field );

        if ( $field['err'] ) echo $field['err'];

        return $value;
    }

    /**
     * render an float field
     * @since    1.0.0
     * @access   public
     * @param    array    $field name of field, state, additional properties
     */
    public function render_float_field ( $field ) {

        $value = $this->get_field_value( $field );
        $value = (float) $value;

        // adjust field type
        $field['type'] = 'number';

        // range attributes
        if ( isset( $field['min'] ) ) $field['min'] = (float) $field['min']; else $field['min'] = 0;
        if ( isset( $field['max'] ) ) $field['max'] = (float) $field['max']; else $field['max'] = $this->FLOAT_MAX;
        if ( isset( $field['step'] ) ) $field['step'] = (float) $field['step']; else $field['step'] = 1.0;

        if ( ! empty( $value ) ) {
            if ( ! $this->is_number( $value ) ) {
                $field['err'] = $this->add_status_to_field( __( 'not a number' ), $this->ERROR_CLASS );
            }
            if (  $value > $field['max'] ) {
                $field['err'] = $this->add_status_to_field( __( 'value greater than maximum' ), $this->ERROR_CLASS );
            }
            if ( $value < $field['min'] ) {
                $field['err'] = $this->add_status_to_field( __( 'value less than minimum' ), $this->ERROR_CLASS );
            }
        } else {
            $field['value'] = $value; // cleaned up
        }

        $value = $this->render_simple_field( $field );

        if ( $field['err'] ) echo $field['err'];

        return $value;

    }

    /**
     * Render a textarea field.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field parameters
     */
    public function render_textarea_field ( $field ) {

        $value = $this->get_field_value( $field );

        // if value is an array, return the first value only (can happen with post meta-data)
        ////////if ( is_array( $field['value'] ) ) $value = $field['value'][0]; else $value = $field['value'];

        // required fields
        $value = esc_html( sanitize_text_field( $value ) );
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

        if ( $this->was_sanitized( $field, $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'field was sanitized'), $this->ERROR_CLASS );
        }

        // render error messages next to field
        if ( ! empty( $field['err'] ) ) echo $field['err'];

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

        $value = $this->get_field_value( $field );

        // if value is an array, return the first value only (can happen with post meta-data)
        ////if ( is_array( $field['value'] ) ) $value = $field['value'][0]; else $value = $field['value'];

        // required fields
        $value = esc_html( sanitize_text_field( $value ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        echo $this->render_label( $field );
        echo '<input title="' . $field['title'] . '" class="' . $class . '" id="' . $slug . '" type="date" name="' . $slug . '" value="' . $value . '">';

        if ( ! empty( $value ) && ! $this->is_date( $value ) ) { // no checks or message if field empty
            $field['err'] = $this->add_status_to_field( __( 'invalid date' ), $this->ERROR_CLASS );
        }

        if ( ! empty( $field['err'] ) ) echo $field['err'];

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

        $value = $this->get_field_value( $field );

        // if value is an array, return the first value only (can happen with post meta-data)
        /////if ( is_array( $field['value'] ) ) $value = $field['value'][0]; else $value = $field['value'];

        // required fields
        $value = esc_html( sanitize_text_field( $value ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        echo $this->render_label( $field );
        echo '<input title="' . $field['title'] . '" id="' . $slug . '" type="time" name="' . $slug . '" value="' . $value . '">';

        if ( ! empty( $value ) && ! $this->is_time( $value ) ) { // no checks or message if field empty
            echo $this->add_status_to_field( __( 'invalid time' ), $this->ERROR_CLASS );
        }

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

        $value = $this->get_field_value( $field );

        // if value is an array, return the first value only (can happen with post meta-data)
        //if ( is_array( $field['value'] ) ) {
        //    echo "ITS AN ARRAY!!!!!!";
        //    $value = $field['value'][0];
        //} else {
        //    $value = $field['value'];
        //}

        // required fields
        $value = esc_attr( sanitize_text_field( $value ) );
        $slug  = sanitize_key( $field['slug'] );
        $title = esc_html( $field['title'] );

        // if value is missing or some falsy thing, make it zero
        if ( ! $value ) $value = '0';

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        // max is defined in seconds. 21600 = 6 hours default
        if ( isset( $field['max'] ) ) $max = $field['max']; else $max = $this->DURATION_MAX;

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

        $value = $this->get_field_value( $field );

        // if value is an array, return the first value only (can happen with post meta-data)
        ////////if ( is_array( $field['value'] ) ) $value = $field['value'][0]; else $value = $field['value'];
    
        // required fields
        $value = esc_html( sanitize_key( $value ) );
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
     * Create an input field similar to the old 'comb box' - typing narrows the 
     * results of the list, but users can type in a value not on the list.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array    $field     field parameters, select
     * @param    string   $value    the field value
     */
    public function render_datalist_field ( $field ) {

        $value = $this->get_field_value( $field );

        // if value is an array, return the first value only (can happen with post meta-data)
        //////////if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $value ) );
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

                $list = $this->datalists->get_datalist( $option_list, $list_id );
                if ( empty( $list ) ) {
                    $field['err'] = $this->add_status_to_field( __( 'datalist not defined' ), $this->CAUTION_CLASS );
                } else {
                    echo $list;
                }

            } else if ( is_string( $option_list ) ) {

                $list_id = 'plse-' . $option_list . '-data';

                $method = 'get_' . $option_list . '_datalist';
                if ( method_exists( $this->datalists, $method ) ) { 
                    $list = $this->datalists->$method();
                    if ( empty( $list ) ) {
                        $field['err'] = $this->add_status_to_field( __( 'datalist not defined' ), $this->CAUTION_CLASS );
                    } else {
                        echo $list;
                    }    
                }

            } else {

                $field['err'] = $this->add_status_to_field( __( 'datalist not defined' ), $this->CAUTION_CLASS );

            }

        }

        echo '<div class="' . $class . '">';
        echo $this->render_label( $field );
        echo '<input type="' . $type .'" title="' . $title . '" id="' . $slug . '" name="' . $slug . '" autocomplete="on" class="plse-datalist" size="' . $size . '" value="' . $value . '" list="' . $list_id . '">';
        echo '</div>';
        if ( ! empty( $field['err'] ) ) echo $field['err'];
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

        $value = $this->get_field_value( $field );

        // if value is an array, return the first value only (can happen with post meta-data)
        /////////if ( is_array( $field['value'] ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( sanitize_text_field( $value ) );
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

                if ( empty( $options ) ) {
                    $field['err'] = $this->add_status_to_field( __( 'options not defined' ), $this->ERROR_CLASS );
                }

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
        if ( ! empty( $field['err'] ) ) echo $field['err'];
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
        $values = $this->get_field_value( $field );

        // required fields
        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        $option_list = $field['option_list'];

        if ( isset( $option_list ) ) {

            if ( is_array( $option_list ) ) {

                // converts array to <option>...</option>
                $options = $this->datalists->get_select( $option_list, $values );

                if ( empty( $options ) ) {
                    $field['err'] = $this->add_status_to_field( __( 'options not defined' ), $this->ERROR_CLASS );
                }

            } else {

                // get a standard <option>...</option> list
                $method = 'get_' . $option_list . '_select';
                if ( method_exists( $this->datalists, $method ) ) { 
                    $options = $this->datalists->$method( $values );
                    if ( empty( $options ) ) {
                        $field['err'] = $this->add_status_to_field( __( 'options not defined' ), $this->ERROR_CLASS );
                    }
                }

            }

        }

        // note $slug[], which specifies multiple values stored in one option.
        echo '<div class="plse-option-select">';
        // add the field label
        echo $this->render_label( $field );
        echo '<select multiple name="' . $slug .'[' . $slug . '][]" class="plse-option-select-dropdown">' . "\n";
        echo $options;
        echo '</select>' . "\n";
        if ( ! empty( $field['err'] ) ) echo $field['err'];
        echo '<span>' . 'Use Ctl-Click to select and deselect multiple options.'. '</span>';

        //echo '<label class="plse-option-select-description" for="' . $slug . '">' . $label . '<br>' . __( '(CTL-Click to deselect)') . '</label>';
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

        $values = $this->get_field_value( $field );

        $slug = sanitize_key( $field['slug'] );

        // URL field specifies an image
        if ( isset( $field['is_image'] ) ) $is_image = $field['is_image']; else $is_image = '';

        // adjust size of fields
        if ( isset( $field['size'] ) ) $size = $field['size']; else $size = '40';

        // adjust text field type (url, date, time...), which is 'subtype' for repeaters
        if ( isset( $field['subtype'] ) ) $type = $field['subtype']; else $type ='text';

        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = '';

        // maximum number of repeater fields allowed (adjusted if we have a datalist)
        $max = $this->REPEATER_MAX;

        // adjust table width to allow tiny thumbnail
        $img_thumb_class = ''; // added class if repeater fields specify images
        if( $is_image == true ) {
            $table_width = '100%';
            $img_thumb_class = ' plse-repeater-url-is-image';
        }
        else {
            $table_width = '74%';
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

                if ( empty( $datalist ) ) {
                    $field['err'] = $this->add_status_to_field( __( 'datalist not defined' ), $this->ERROR_CLASS );
                }

            } else { // option list specifies a standard list in PLSE_Datalists

                // load the datalist (note they must follow naming conventions)
                $method = 'get_' . $option_list . '_datalist';
                if ( method_exists( $this->datalists, $method ) ) { 
                    $datalist .= $this->datalists->$method();
                    $datalist_id = $option_list;
                    $datalist_id = 'plse-' . $datalist_id . '-data'; // $option list is the id value 
                    if ( empty( $datalist ) ) {
                        $field['err'] = $this->add_status_to_field( __( 'datalist not defined' ), $this->ERROR_CLASS );
                    }
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
        echo $this->render_label( $field );

        ?>
        <div id="plse-repeater-<?php echo $slug; ?>" class="plse-repeater <?php echo $class; ?>">
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

                        // set width of first <td> based on whether a small icon of the image will be drawn
                        if ( $is_image ) $tdstyle = 'width:328px; white-space: nowrap;'; else $tdstyle = 'width:300px';

                        // create fields already in the database
                        foreach( $values as $repeater_value ) {

                            $repeater_value = esc_attr( $repeater_value );

                            if ( ! empty( $repeater_value ) ) {
                                $wroteflag = true; // field saved to DB was not empty

                                // if we are checking URLs, check them here
                                if ( $field['subtype'] == PLSE_INPUT_TYPES['URL'] && $this->check_urls ) { // no checks or message if field empty

                                    // try connecting to the supplied URL
                                    $result = $this->get_url_status( $repeater_value );
                                    $field['err']   = $this->add_status_to_field( $result['err'], $result['class'] );
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
                                    <?php if ( ! empty( $field['err'] ) ) echo $field['err']; ?>
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
                                    <?php if ( ! empty( $field['err'] ) ) echo $field['err']; ?>
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
                            <?php if ( ! empty( $field['err'] ) ) echo $field['err']; ?>
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

        $value = $this->get_field_value( $field );

        $slug  = sanitize_key( $field['slug'] );
        //////////////////$value = $field['value'];

        // if value is an array, return the first value only (can happen with post meta-data)
       //////////////// if ( is_array( $value ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( esc_url_raw( $value, ['http','https'] ) );
        if ( ! $this->is_url( $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'not valid url' ), $this->ERROR_CLASS );
        }

        $title = esc_html( $field['title'] );
       
        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";
        if ( isset( $field['size'] ) ) $size = $field['size']; else $size = '24';
        if ( isset( $field['width'] ) ) $width = $field['width']; else $width = '128';
        if ( isset( $field['height'] ) ) $heigh = $field['height']; else $height = '128';

        echo $this->render_label( $field );

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

        if ( ! empty( $field['err'] ) ) echo $field['err'];

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

        $value = $this->field_value( $field );

        $slug = sanitize_key( $field['slug'] );

        // if value is an array, return the first value only (can happen with post meta-data)
        //////////////////////if ( is_array( $value ) ) $value = $field['value'][0];

        // required fields
        $value = esc_html( esc_url_raw( $value, ['http','https'] ) );
        if ( ! $this->is_url( $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'not valid url' ), $this->ERROR_CLASS );
        }

        // test URLs: https://www.soundhelix.com/audio-examples

        $title = esc_html( $field['title'] );

        // optional field values
        if ( isset( $field['state'] ) ) $state = esc_html( $field['state'] ); else $state = '';
        if ( isset( $field['class'] ) ) $class = $field['class']; else $class = "";

        echo $this->render_label( $field );

        echo '<div class="plse-audio-metabox plse-meta-ctl-highlight">';

        echo '<audio controls>';
        if ( stripos( $value, '.mp3') !== false ) echo '<source src="' . $value . '" type="audio/mpeg">';
        else if ( stripos( $value, '.wav' ) !== false ) echo '<source src="' . $value . '" type="audio/wav">';
        else if ( stripos( $value, '.ogg') !== false )  echo '<source src="' . $value . '" type="audio/ogg">';
        else echo __( 'audio field not supported in this version' );
        echo '</audio>';

        if ( ! empty( $field['err'] ) ) echo $field['err'];

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

        $value = $this->get_field_value( $field );

        $slug = sanitize_key( $field['slug'] );

        // required fields
        $value = esc_html( esc_url_raw( $value, ['http','https'] ) );
        if ( ! $this->is_url( $value ) ) {
            $field['err'] = $this->add_status_to_field( __( 'not valid url' ), $this->ERROR_CLASS );
        }

        $title = esc_attr( $field['title'] );

        echo $this->render_label( $field );

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
        if ( ! empty( $field['err'] ) ) echo $field['err'];
        echo '<p>' . __( 'Supports YouTube and Vimeo. Enter the video url, the hit the "tab" key to check if the video embed and thumbnail are valid. Schema will use both the video url, and the default thumbnail defined by the video service.' ) . '</p>';

        echo '</div>';

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