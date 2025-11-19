<?php
namespace ServtechMPP\Admin\Submenus;

if (!defined('ABSPATH')) exit;

class StripeSettings {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_submenu']);
    }

    public static function register_submenu() {
        add_submenu_page(
            'pastor-settings',                             
            __('Stripe Donation', 'multi-purpose-plugin'), 
            __('Stripe Donation', 'multi-purpose-plugin'), 
            'manage_options',                              
            'stripe-donation',                             
            [__CLASS__, 'render_page']                     
        );
    }

    public static function render_page() {
        if (isset($_POST['ffwcs_save_stripe_keys'])) {
            if (!empty($_POST['stripe_secret'])) {
                update_option('ffwcs_stripe_secret_key', sanitize_text_field($_POST['stripe_secret']));
            }
            if (!empty($_POST['stripe_publishable'])) {
                update_option('ffwcs_stripe_publishable_key', sanitize_text_field($_POST['stripe_publishable']));
            }
            echo '<div class="updated"><p>Keys saved!</p></div>';
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Stripe Donation Settings', 'multi-purpose-plugin'); ?></h1>
            <form method="POST">
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Secret Key', 'multi-purpose-plugin'); ?></th>
                        <td>
                            <input type="password" name="stripe_secret" class="regular-text" placeholder="<?php esc_attr_e('Enter new secret key', 'multi-purpose-plugin'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Publishable Key', 'multi-purpose-plugin'); ?></th>
                        <td>
                            <input type="password" name="stripe_publishable" class="regular-text" placeholder="<?php esc_attr_e('Enter new publishable key', 'multi-purpose-plugin'); ?>">
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="ffwcs_save_stripe_keys" class="button-primary" value="<?php esc_attr_e('Save Keys', 'multi-purpose-plugin'); ?>">
                </p>
            </form>
        </div>
        <?php
    }
}
