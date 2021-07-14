<?php

/**
 * Common methods, constants, checks used across multiple classes.
 *
 * @since     1.0.0
 * @category  WordPress_Plugin
 * @package   PLSE_SCHEMA_Extender
 * @author    Pete Markeiwicz <pindiespace@gmail.com>
 * @license   GPL-2.0+
 * @link      https://plyojump.com
 */
class PLSE_Util {

    /**
     * Store reference for singleton pattern.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $instance    static reference to initialized class.
     */
    static private $__instance = null;

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct() {

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Util    $self__instance
     */
    public static function getInstance () {
        if (is_null(self::$__instance)) {
            self::$__instance = new PLSE_Util();
        }
        return self::$__instance;
    }


} // end of class