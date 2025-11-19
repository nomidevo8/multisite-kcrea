<?php
namespace ServtechMPP;

if (!defined('ABSPATH')) exit;

class Plugin {

    private static $instance = null;

    private function __construct()
    {
        // Includes
        // \ServtechMPP\Includes\GivingLink::init();

        // Admin
        \ServtechMPP\Admin\Admin::init();

        // Public/frontend
        \ServtechMPP\Public\PublicScripts::init();
        
        // Initialize FluentForm Stripe Donation Form
        \ServtechMPP\Includes\FluentDonationFormHook\DonationForm::init();
    }


    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
