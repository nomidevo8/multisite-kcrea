<?php
namespace ServtechMPP\Includes\FluentDonationFormHook;

if (!defined('ABSPATH')) exit;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Product;
use Stripe\Price;
use Stripe\Checkout\Session;

class DonationForm {
    public function __construct(){
        add_action('fluentform/submission_inserted', [$this, 'handle_submission'], 10, 3);
    }

        // Static initializer
    public static function init() {
        new self();
    }

    /**
     * Handle FluentForm submission and generate a Stripe payment link
     */
    public function handle_submission($insertId, $insertData, $form)
    {
        try {
            $data = is_object($insertData) ? (array)$insertData : (array)$insertData;

            $stripe_secret = get_option('ffwcs_stripe_secret_key');
            if (!$stripe_secret) {
                wp_die('Stripe secret key not set.');
                return;
            }

            Stripe::setApiKey($stripe_secret);

            $email      = $data['donor_email'] ?? '';
            $first_name = $data['donpr_names']['first_name'] ?? '';
            $last_name  = $data['donpr_names']['last_name'] ?? '';

            if (!$email) {
                wp_die('Email is required.');
                return;
            }

            // 1️⃣ Create or retrieve Stripe Customer
            $customer = Customer::create([
                'email' => $email,
                'name'  => trim("$first_name $last_name"),
            ]);

            // 2️⃣ Donation details
            $amount    = (float)($data['donations'][0][0] ?? 0) * 100; // cents
            $currency  = 'usd';
            $frequency = strtolower($data['frequency'] ?? 'one time');
            $raw_interval = strtolower($data['billing_interval'] ?? 'month');

            $interval_map = [
                'daily'          => 'day',
                'weekly'         => 'week',
                'every_two_week' => 'week', // double the amount later
                'monthly'        => 'month',
                'yearly'         => 'year',
            ];

            $interval = $interval_map[$raw_interval] ?? 'month';
            if ($raw_interval === 'every_two_week') {
                $amount = $amount * 2;
            }
            $donation_type = $data['donations'][0][1] ?? 'Donation';


            // 3️⃣ Create Product & Price
            if ($frequency === 'regularly') {
                $product = Product::create([
                    'name' => $donation_type . 'Donation Subscription', 
                ]);

                $price = Price::create([
                    'unit_amount' => $amount,
                    'currency'    => $currency,
                    'recurring'   => ['interval' => $interval],
                    'product'     => $product->id,
                ]);

                $mode = 'subscription';
            } else {
                $product = Product::create([
                    'name' => $donation_type . ' One-Time Donation',
                ]);


                $price = Price::create([
                    'unit_amount' => $amount,
                    'currency'    => $currency,
                    'product'     => $product->id,
                ]);

                $mode = 'payment';
            }

            // 4️⃣ Create Checkout Session but do not redirect
            // 4️⃣ Create Checkout Session but do not redirect
            $checkout_session_data = [
                'customer'             => $customer->id,
                'payment_method_types' => ['card'],
                'mode'                 => $mode,
                'line_items'           => [[
                    'price'    => $price->id,
                    'quantity' => 1,
                ]],
                'success_url'          => site_url('/thank-you?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'           => site_url('/give-away'),
            ];

            // ✅ Only for subscriptions: set trial_end to delay first payment
            if ($frequency === 'regularly' && !empty($data['billing_start_date'])) {
                $start_timestamp = strtotime($data['billing_start_date'] . ' 00:00:00 UTC');
                if ($start_timestamp > time()) { // only if start date is in the future
                    $checkout_session_data['subscription_data'] = [
                        'trial_end' => $start_timestamp
                    ];
                }
            }

            $checkout_session = Session::create($checkout_session_data);


            // 5️⃣ Send the payment link back to the user (instead of redirect)
            // Example: you can store it in session, send via email, or return JSON
            // Here we just redirect to the link for simplicity:

                 // 5️⃣ Send JSON response if AJAX, else redirect
            if (defined('DOING_AJAX') && DOING_AJAX) {
                wp_send_json_success([
                    'result' => [
                        'redirectUrl' => $checkout_session->url
                    ]
                ]);
            } else {
                wp_safe_redirect($checkout_session->url);
            }
            exit;

        } catch (\Throwable $e) {
            error_log('Stripe error: ' . $e->getMessage());
            wp_die('Payment link creation failed: ' . $e->getMessage());
        }
    }
}