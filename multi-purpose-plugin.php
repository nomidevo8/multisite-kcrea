<?php
/**
 * Plugin Name: Multi Purpose Plugin
 * Description: Modular multisite plugin with PSR-4 autoloading and modular classes.
 * Version: 1.0
 * Author: Servtech Global
 */

if (!defined('ABSPATH')) exit;

define('SERVETECH_VERSION', '1.0.0.0');

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Initialize main plugin class
add_action('plugins_loaded', function () {
    \ServtechMPP\Plugin::get_instance();
});