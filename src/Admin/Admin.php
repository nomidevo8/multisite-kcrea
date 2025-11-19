<?php
namespace ServtechMPP\Admin;

if (!defined('ABSPATH'))
    exit;

class Admin
{

    public static function init()
    {
        // Hook to register main menu
        add_action('admin_menu', [__CLASS__, 'register_admin_menu']);

        // Initialize Stripe submenu class
        \ServtechMPP\Admin\Submenus\StripeSettings::init();

        // Initialize Menu Page Visibility submenu
        \ServtechMPP\Admin\Submenus\MenuPageVisibility::init();

        // Initialize Admin Assets
        \ServtechMPP\Admin\LoadAdminAssets\LoadAdminEnqeue::init();
    }

    public static function register_admin_menu()
    {
        add_menu_page(
            __('Pastor Settings', 'multi-purpose-plugin'),
            __('Pastor Settings', 'multi-purpose-plugin'),
            'manage_options',
            'pastor-settings',
            [__CLASS__, 'render_main_page'],
            'dashicons-admin-users',
            2
        );
    }

    public static function render_main_page()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Pastor Settings', 'multi-purpose-plugin'); ?></h1>
            <p><?php esc_html_e('Welcome to the Pastor Settings main page.', 'multi-purpose-plugin'); ?></p>
        </div>
        <?php
    }
}
