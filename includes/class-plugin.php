<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MPP_Plugin {

    private static $instance = null;

    private function __construct() {
        // Initialize features
        MPP_Giving_Link::init();
    }

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
