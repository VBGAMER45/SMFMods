Stripe Payment Gateway for SMF 2.1
====================================
by vbgamer45
https://www.smfhacks.com

This mod adds Stripe as a payment gateway option for SMF 2.1 Paid Subscriptions.

FEATURES
--------
- One-time payments via Stripe Checkout
- Recurring subscriptions with automatic renewal
- Webhook support for payment confirmation, renewals, cancellations, and refunds
- No Stripe PHP SDK required (uses raw cURL, same approach as PayPal)
- Zero-decimal currency handling (JPY, KRW, etc.)
- HMAC-SHA256 webhook signature verification

INSTALLATION
------------
1. Upload this package via Admin > Package Manager > Download Packages
2. Install the package
3. Go to Admin > Paid Subscriptions > Settings
4. Enter your Stripe API keys (Secret Key, Publishable Key)
5. Enter your Stripe Webhook Signing Secret

STRIPE SETUP
------------
1. Get your API keys from https://dashboard.stripe.com/apikeys
2. Create a webhook endpoint at https://dashboard.stripe.com/webhooks
   - URL: https://yourforum.com/subscriptions.php
   - Events to listen for:
     * checkout.session.completed
     * invoice.paid
     * customer.subscription.deleted
     * charge.refunded
3. Copy the Webhook Signing Secret (whsec_...) into the SMF settings

TESTING
-------
Use Stripe test mode keys (sk_test_... and pk_test_...) with test card 4242 4242 4242 4242.
Use the Stripe CLI for local webhook testing: stripe listen --forward-to https://yourforum.com/subscriptions.php

COMPATIBILITY
-------------
- SMF 2.1.0 through 2.1.x
- PHP 7.4+ with cURL extension
- Does not affect existing PayPal functionality
