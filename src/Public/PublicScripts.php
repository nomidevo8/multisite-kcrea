<?php
namespace ServtechMPP\Public;

if (!defined('ABSPATH')) exit;

class PublicScripts {
    private static $version = "1.0.0.013433";
    public static function init() {
        // Enqueue frontend scripts
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
    }

    public static function enqueue_scripts() {
        $version = self::$version;
        if (is_page('ministries')) {
            wp_enqueue_script(
                'remove-extra-accordion',
                plugins_url('assets/js/removing-extra-accordian.js', dirname(__DIR__)),
                ['jquery'],
                $version,
                true
            );
        }

        // Donation Form Js and CSS
        wp_enqueue_script(
            'fluent-form-donation',
            plugins_url('assets/js/donation-form.js', dirname(__DIR__)),
            ['jquery'],
            $version,
            true
        );

         wp_enqueue_style('fluent-form-donation-css',  plugins_url('assets/css/donation-form.css', dirname(__DIR__)));
    }

}
