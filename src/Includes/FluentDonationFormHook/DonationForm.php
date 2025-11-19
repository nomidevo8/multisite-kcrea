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

    public static function init() {
        new self();
    }

    public function handle_submission($insertId, $insertData, $form)
    {
        try {
            error_log('FFWCS MULTI-DONATION HANDLER');
            error_log("Submission Data: " . print_r($insertData, true));

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

            // 1️⃣ Create Stripe Customer
            $customer = Customer::create([
                'email' => $email,
                'name'  => trim("$first_name $last_name"),
            ]);

            // 2️⃣ Frequency / Interval logic
            $currency      = 'usd';
            $frequency     = strtolower($data['frequency'] ?? 'one time');
            $raw_interval  = strtolower($data['billing_interval'] ?? 'month');

            $interval_map = [
                'daily'          => 'day',
                'weekly'         => 'week',
                'every_two_week' => 'week',
                'monthly'        => 'month',
                'yearly'         => 'year',
            ];

            $interval = $interval_map[$raw_interval] ?? 'month';

            // 3️⃣ Build line items for ALL donations
            $line_items = [];

            foreach ($data['donations'] as $donation) {
                $amount  = (float)$donation[0] * 100;
                $name    = $donation[1];

                // every_two_week doubles amount
                if ($raw_interval === 'every_two_week') {
                    $amount = $amount * 2;
                }

                // Create product
                if ($frequency === 'regularly') {
                    $product = Product::create([
                        'name' => $name . ' Subscription Donation',
                    ]);

                    // Recurring price
                    $price = Price::create([
                        'unit_amount' => $amount,
                        'currency'    => $currency,
                        'recurring'   => ['interval' => $interval],
                        'product'     => $product->id,
                    ]);
                } else {
                    $product = Product::create([
                        'name' => $name . ' One-Time Donation',
                    ]);

                    // One-time price
                    $price = Price::create([
                        'unit_amount' => $amount,
                        'currency'    => $currency,
                        'product'     => $product->id,
                    ]);
                }

                // Add to line items array
                $line_items[] = [
                    'price'    => $price->id,
                    'quantity' => 1,
                ];
            }

            // Determine mode
            $mode = ($frequency === 'regularly') ? 'subscription' : 'payment';

            // 4️⃣ Build checkout session body
            $checkout_session_data = [
                'customer'             => $customer->id,
                'payment_method_types' => ['card'],
                'mode'                 => $mode,
                'line_items'           => $line_items,
                'success_url'          => site_url('/thank-you?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'           => site_url('/give-away'),
            ];

            // 5️⃣ Handle future start date
            if ($mode === 'subscription' && !empty($data['billing_start_date'])) {
                $start_timestamp = strtotime($data['billing_start_date'] . " 00:00:00 UTC");

                if ($start_timestamp > time()) {
                    $checkout_session_data['subscription_data'] = [
                        'trial_end' => $start_timestamp
                    ];
                }
            }

            // 6️⃣ Create session
            $checkout_session = Session::create($checkout_session_data);

            // AJAX OR redirect
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
            error_log("Stripe Error: " . $e->getMessage());
            wp_die('Payment link creation failed: ' . $e->getMessage());
        }
    }
}
