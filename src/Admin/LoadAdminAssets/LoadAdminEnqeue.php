<?php
namespace ServtechMPP\Admin\LoadAdminAssets;

if (!defined('ABSPATH'))
    exit;

class LoadAdminEnqeue
{
    public static function init()
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_scripts']);
    }

    public static function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'pastor-settings_page_menu-page-visibility')
            return;

        // Spin.js
        wp_enqueue_script(
            'spin-js',
            'https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.min.js',
            ['jquery'],
            '2.3.2',
            true
        );

        // Your JS
        wp_enqueue_script(
            'menu-visibility-js',
            plugins_url('Admin/assets/js/menu-visibility.js', dirname(__DIR__)),
            ['jquery', 'spin-js'],
            '1.0.0.01',
            true
        );

        wp_localize_script('menu-visibility-js', 'MenuVisibility', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('menu_visibility_save')
        ]);

        // Enqueue your toggle CSS
        wp_enqueue_style(
            'menu-visibility-css',
            plugins_url('Admin/assets/css/menu-visibility.css', dirname(__DIR__)),
            [],
            '1.0.0'
        );
    }
}
