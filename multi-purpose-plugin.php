<?php
/**
 * Plugin Name: Multi Purpose Plugin
 * Description: Modular multisite plugin with auto-loading classes. Giving Link feature included.
 * Version: 1.0
 * Author: Servtech Global
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Autoloader for plugin classes
spl_autoload_register(function($class_name){
    $prefix = 'MPP_';
    if (strpos($class_name, $prefix) !== 0) return;

    $file_name = 'class-' . strtolower(str_replace('_', '-', substr($class_name, strlen($prefix)))) . '.php';
    $folders = ['includes', 'admin', 'public'];

    foreach ($folders as $folder) {
        $file = plugin_dir_path(__FILE__) . $folder . '/' . $file_name;
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Initialize main plugin class after all plugins loaded
add_action('plugins_loaded', function() {
    MPP_Plugin::get_instance();
});
