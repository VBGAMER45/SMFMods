<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2024 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.4
 */

// This won't be dedicated without this - this must exist in each gateway!
// SMF Payment Gateway: stripe

if (!defined('SMF'))
	die('No direct access...');

/**
 * Class for returning available form data for the Stripe gateway
 */
class stripe_display
{
	/**
	 * @var string Name of this payment gateway
	 */
	public $title = 'Stripe';

	/**
	 * Return the admin settings for this gateway
	 *
	 * @return array An array of settings data
	 */
	public function getGatewaySettings()
	{
		global $txt;

		$setting_data = array(
			array(
				'text', 'stripe_secret_key',
				'subtext' => $txt['stripe_secret_key_desc'],
				'size' => 60,
			),
			array(
				'text', 'stripe_publishable_key',
				'subtext' => $txt['stripe_publishable_key_desc'],
				'size' => 60,
			),
			array(
				'text', 'stripe_webhook_secret',
				'subtext' => $txt['stripe_webhook_secret_desc'],
				'size' => 60,
			),
		);

		return $setting_data;
	}

	/**
	 * Is this enabled for new payments?
	 *
	 * @return boolean Whether this gateway is enabled
	 */
	public function gatewayEnabled()
	{
		global $modSettings;

		return !empty($modSettings['stripe_secret_key']);
	}

	/**
	 * Return the fields needed for the payment form.
	 *
	 * Unlike PayPal (which POSTs directly to paypal.com), Stripe requires
	 * server-side Checkout Session creation first. We POST back to our own
	 * subscriptions.php with stripe_session_create=1.
	 *
	 * @param string $unique_id The unique ID (member_id+subscription_id)
	 * @param array $sub_data Subscription data
	 * @param int|float $value The amount of the subscription
	 * @param string $period The subscription period
	 * @param string $return_url The URL to return the user to after payment
	 * @return array An array of data for the form
	 */
	public function fetchGatewayFields($unique_id, $sub_data, $value, $period, $return_url)
	{
		global $modSettings, $txt, $boardurl;

		$return_data = array(
			'form' => $boardurl . '/subscriptions.php',
			'id' => 'stripe',
			'hidden' => array(),
			'title' => $txt['stripe'],
			'desc' => $txt['paid_confirm_stripe'],
			'submit' => $txt['paid_stripe_order'],
			'javascript' => '',
		);

		$return_data['hidden']['stripe_session_create'] = 1;
		$return_data['hidden']['item_number'] = $unique_id;
		$return_data['hidden']['item_name'] = $sub_data['name'] . ' ' . $txt['subscription'];
		$return_data['hidden']['amount'] = $value;
		$return_data['hidden']['currency_code'] = strtoupper($modSettings['paid_currency_code']);
		$return_data['hidden']['return_url'] = $return_url;
		$return_data['hidden']['repeatable'] = !empty($sub_data['repeatable']) ? 1 : 0;

		// Determine the period.
		if ($sub_data['flexible'])
		{
			$return_data['hidden']['period'] = strtoupper(substr($period, 0, 1));
		}
		else
		{
			preg_match('~(\d*)(\w)~', $sub_data['real_length'], $match);
			$return_data['hidden']['period_unit'] = $match[1];
			$return_data['hidden']['period'] = $match[2];
		}

		// If it's repeatable do some javascript to respect this idea.
		if (!empty($sub_data['repeatable']))
			$return_data['javascript'] = '
				document.write(\'<label for="do_stripe_recur"><input type="checkbox" name="do_stripe_recur" id="do_stripe_recur" checked onclick="switchStripeRecur();">' . $txt['paid_make_recurring'] . '</label><br>\');

				function switchStripeRecur()
				{
					document.getElementById("stripe_repeatable").value = document.getElementById("do_stripe_recur").checked ? "1" : "0";
				}';

		return $return_data;
	}
}

/**
 * Class of functions to validate a Stripe webhook and provide details of the payment
 */
class stripe_payment
{
	/**
	 * @var array The decoded webhook event
	 */
	private $event = null;

	/**
	 * @var string The event type
	 */
	private $event_type = '';

	/**
	 * @var array The event data object
	 */
	private $event_object = null;

	/**
	 * @var int The subscription ID extracted during precheck
	 */
	private $subscription_id = 0;

	/**
	 * @var int The member ID extracted during precheck
	 */
	private $member_id = 0;

	/**
	 * Handled Stripe event types
	 */
	private $handled_events = array(
		'checkout.session.completed',
		'invoice.paid',
		'customer.subscription.deleted',
		'charge.refunded',
	);

	/**
	 * Check if this data is intended for Stripe.
	 *
	 * @return boolean Whether this gateway thinks the data is valid
	 */
	public function isValid()
	{
		global $modSettings, $smf_raw_post_body;

		// Must have a secret key configured.
		if (empty($modSettings['stripe_secret_key']))
			return false;

		// Must have the Stripe-Signature header.
		$signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
		if (empty($signature))
			return false;

		// Must have a raw body to work with.
		if (empty($smf_raw_post_body))
			return false;

		// Try to decode the JSON body.
		$this->event = json_decode($smf_raw_post_body, true);
		if (empty($this->event) || empty($this->event['type']))
			return false;

		// Is this an event type we handle?
		if (!in_array($this->event['type'], $this->handled_events))
			return false;

		$this->event_type = $this->event['type'];
		$this->event_object = isset($this->event['data']['object']) ? $this->event['data']['object'] : array();

		return true;
	}

	/**
	 * Verify the webhook signature and extract subscription/member IDs.
	 *
	 * @return array The subscription ID and member ID
	 */
	public function precheck()
	{
		global $modSettings, $txt, $smf_raw_post_body;

		// Verify the webhook signature if we have a webhook secret.
		if (!empty($modSettings['stripe_webhook_secret']))
		{
			$signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';

			// Parse the signature header.
			$sig_parts = array();
			foreach (explode(',', $signature) as $part)
			{
				$kv = explode('=', trim($part), 2);
				if (count($kv) === 2)
					$sig_parts[$kv[0]] = $kv[1];
			}

			// Must have timestamp and signature.
			if (empty($sig_parts['t']) || empty($sig_parts['v1']))
				exit;

			// Check replay window (5 minutes).
			$timestamp = (int) $sig_parts['t'];
			if (abs(time() - $timestamp) > 300)
				exit;

			// Compute expected signature.
			$signed_payload = $timestamp . '.' . $smf_raw_post_body;
			$expected_sig = hash_hmac('sha256', $signed_payload, $modSettings['stripe_webhook_secret']);

			// Compare signatures.
			if (!hash_equals($expected_sig, $sig_parts['v1']))
				exit;
		}

		// Now extract subscription_id and member_id based on event type.
		$metadata = array();

		switch ($this->event_type)
		{
			case 'checkout.session.completed':
				// Metadata is directly on the session object.
				$metadata = isset($this->event_object['metadata']) ? $this->event_object['metadata'] : array();
				break;

			case 'invoice.paid':
				// Try subscription metadata first.
				if (!empty($this->event_object['subscription']))
				{
					// Check if metadata is on the invoice lines.
					if (!empty($this->event_object['lines']['data'][0]['metadata']))
						$metadata = $this->event_object['lines']['data'][0]['metadata'];

					// If no metadata, try looking up by vendor_ref.
					if (empty($metadata['subscription_id']) && !empty($this->event_object['subscription']))
					{
						$found = $this->_findSubscriptionByVendorRef($this->event_object['subscription']);
						if ($found !== false)
							return $found;

						// Last resort: fetch metadata from the Stripe subscription object via API.
						$metadata = $this->_fetchStripeSubscriptionMetadata($this->event_object['subscription']);
					}
				}
				break;

			case 'customer.subscription.deleted':
				// Try metadata on the subscription object.
				$metadata = isset($this->event_object['metadata']) ? $this->event_object['metadata'] : array();

				// Fallback to vendor_ref lookup.
				if (empty($metadata['subscription_id']) && !empty($this->event_object['id']))
				{
					$found = $this->_findSubscriptionByVendorRef($this->event_object['id']);
					if ($found !== false)
						return $found;
				}
				break;

			case 'charge.refunded':
				// Try metadata on the charge.
				$metadata = isset($this->event_object['metadata']) ? $this->event_object['metadata'] : array();

				// Fallback: look up via payment_intent.
				if (empty($metadata['subscription_id']) && !empty($this->event_object['payment_intent']))
				{
					$found = $this->_lookupByPaymentIntent($this->event_object['payment_intent']);
					if ($found !== false)
						return $found;
				}
				break;
		}

		if (!empty($metadata['subscription_id']) && !empty($metadata['member_id']))
		{
			$this->subscription_id = (int) $metadata['subscription_id'];
			$this->member_id = (int) $metadata['member_id'];
			return array($this->subscription_id, $this->member_id);
		}

		// Could not identify the subscription.
		generateSubscriptionError('Stripe: Could not extract subscription/member ID from ' . $this->event_type . ' event');
	}

	/**
	 * Is this a refund?
	 *
	 * @return boolean
	 */
	public function isRefund()
	{
		return $this->event_type === 'charge.refunded';
	}

	/**
	 * Is this a subscription renewal payment?
	 *
	 * We only use invoice.paid for subscription payments to avoid double-processing,
	 * since Stripe fires both checkout.session.completed AND invoice.paid for
	 * the initial subscription payment.
	 *
	 * @return boolean
	 */
	public function isSubscription()
	{
		return $this->event_type === 'invoice.paid';
	}

	/**
	 * Is this a one-time payment?
	 *
	 * @return boolean
	 */
	public function isPayment()
	{
		if ($this->event_type === 'checkout.session.completed')
		{
			$mode = isset($this->event_object['mode']) ? $this->event_object['mode'] : '';
			return $mode === 'payment';
		}
		return false;
	}

	/**
	 * Is this a cancellation?
	 *
	 * @return boolean
	 */
	public function isCancellation()
	{
		return $this->event_type === 'customer.subscription.deleted';
	}

	/**
	 * Things to do in the event of a cancellation.
	 *
	 * Same as PayPal: subscription expires naturally via SMF scheduled task.
	 *
	 * @param string $subscription_id
	 * @param int $member_id
	 * @param array $subscription_info
	 */
	public function performCancel($subscription_id, $member_id, $subscription_info)
	{
		// Stripe doesn't require SMF to take any action on cancellation.
		// The subscription will expire naturally based on its end_time.
	}

	/**
	 * How much was paid?
	 *
	 * @return float The amount paid
	 */
	public function getCost()
	{
		$amount = 0;

		switch ($this->event_type)
		{
			case 'checkout.session.completed':
				$amount = isset($this->event_object['amount_total']) ? (int) $this->event_object['amount_total'] : 0;
				$currency = isset($this->event_object['currency']) ? strtoupper($this->event_object['currency']) : '';
				break;

			case 'invoice.paid':
				$amount = isset($this->event_object['amount_paid']) ? (int) $this->event_object['amount_paid'] : 0;
				$currency = isset($this->event_object['currency']) ? strtoupper($this->event_object['currency']) : '';
				break;

			case 'charge.refunded':
				$amount = isset($this->event_object['amount_refunded']) ? (int) $this->event_object['amount_refunded'] : 0;
				$currency = isset($this->event_object['currency']) ? strtoupper($this->event_object['currency']) : '';
				break;

			default:
				return 0;
		}

		// Zero-decimal currencies don't need division.
		$zero_decimal_currencies = array(
			'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
			'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
		);

		if (!in_array($currency, $zero_decimal_currencies))
			$amount = $amount / 100;

		return (float) $amount;
	}

	/**
	 * Record the Stripe subscription ID as vendor_ref for future renewal/cancel tracking.
	 */
	public function close()
	{
		global $smcFunc, $subscription_id;

		$stripe_sub_id = '';

		// For checkout.session.completed with mode=subscription, store the subscription ID.
		if ($this->event_type === 'checkout.session.completed')
		{
			$stripe_sub_id = isset($this->event_object['subscription']) ? $this->event_object['subscription'] : '';
		}
		// For invoice.paid, store the subscription ID.
		elseif ($this->event_type === 'invoice.paid')
		{
			$stripe_sub_id = isset($this->event_object['subscription']) ? $this->event_object['subscription'] : '';
		}

		if (!empty($stripe_sub_id) && !empty($subscription_id))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}log_subscribed
				SET vendor_ref = {string:vendor_ref}
				WHERE id_sublog = {int:current_subscription}',
				array(
					'current_subscription' => $subscription_id,
					'vendor_ref' => $stripe_sub_id,
				)
			);
		}
	}

	/**
	 * Find a subscription by vendor_ref (Stripe subscription ID).
	 *
	 * @param string $vendor_ref The Stripe subscription ID
	 * @return array|false Array of (subscription_id, member_id) or false
	 */
	private function _findSubscriptionByVendorRef($vendor_ref)
	{
		global $smcFunc;

		if (empty($vendor_ref))
			return false;

		$request = $smcFunc['db_query']('', '
			SELECT id_member, id_subscribe
			FROM {db_prefix}log_subscribed
			WHERE vendor_ref = {string:vendor_ref}
			LIMIT 1',
			array(
				'vendor_ref' => $vendor_ref,
			)
		);

		if ($smcFunc['db_num_rows']($request) === 0)
		{
			$smcFunc['db_free_result']($request);
			return false;
		}

		list($member_id, $subscription_id) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$this->subscription_id = (int) $subscription_id;
		$this->member_id = (int) $member_id;

		return array($this->subscription_id, $this->member_id);
	}

	/**
	 * Fetch metadata from a Stripe subscription object via the API.
	 *
	 * @param string $stripe_sub_id The Stripe subscription ID
	 * @return array The metadata array
	 */
	private function _fetchStripeSubscriptionMetadata($stripe_sub_id)
	{
		global $modSettings;

		if (empty($stripe_sub_id) || empty($modSettings['stripe_secret_key']))
			return array();

		$ch = curl_init('https://api.stripe.com/v1/subscriptions/' . urlencode($stripe_sub_id));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $modSettings['stripe_secret_key'],
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);

		$response = curl_exec($ch);
		curl_close($ch);

		if (empty($response))
			return array();

		$data = json_decode($response, true);
		if (!empty($data['metadata']))
			return $data['metadata'];

		return array();
	}

	/**
	 * Look up a subscription by payment_intent (for refund tracking).
	 *
	 * First checks if we stored the payment intent as vendor_ref,
	 * then falls back to the Stripe API to get the payment intent's metadata.
	 *
	 * @param string $payment_intent_id The Stripe payment intent ID
	 * @return array|false Array of (subscription_id, member_id) or false
	 */
	private function _lookupByPaymentIntent($payment_intent_id)
	{
		global $modSettings;

		if (empty($payment_intent_id) || empty($modSettings['stripe_secret_key']))
			return false;

		// Try the Stripe API to get the payment intent's metadata.
		$ch = curl_init('https://api.stripe.com/v1/payment_intents/' . urlencode($payment_intent_id));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $modSettings['stripe_secret_key'],
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);

		$response = curl_exec($ch);
		curl_close($ch);

		if (empty($response))
			return false;

		$data = json_decode($response, true);
		if (!empty($data['metadata']['subscription_id']) && !empty($data['metadata']['member_id']))
		{
			$this->subscription_id = (int) $data['metadata']['subscription_id'];
			$this->member_id = (int) $data['metadata']['member_id'];
			return array($this->subscription_id, $this->member_id);
		}

		return false;
	}
}

/**
 * Create a Stripe Checkout Session and redirect the user to Stripe.
 *
 * Called from subscriptions.php when stripe_session_create is POSTed.
 * Uses raw cURL (no Stripe SDK required).
 */
function stripe_create_checkout_session()
{
	global $modSettings, $txt, $boardurl;

	// Parse the item_number to get member and subscription IDs.
	if (empty($_POST['item_number']) || strpos($_POST['item_number'], '+') === false)
		exit;

	list($member_id, $subscription_id) = explode('+', $_POST['item_number']);
	$member_id = (int) $member_id;
	$subscription_id = (int) $subscription_id;

	if (empty($member_id) || empty($subscription_id))
		exit;

	// Must have Stripe configured.
	if (empty($modSettings['stripe_secret_key']))
		exit;

	$amount = (float) $_POST['amount'];
	$currency = strtolower(!empty($_POST['currency_code']) ? $_POST['currency_code'] : $modSettings['paid_currency_code']);
	$item_name = !empty($_POST['item_name']) ? $_POST['item_name'] : 'Subscription';
	$return_url = !empty($_POST['return_url']) ? $_POST['return_url'] : $boardurl;
	$repeatable = !empty($_POST['repeatable']) ? (int) $_POST['repeatable'] : 0;

	// Zero-decimal currencies don't need multiplication.
	$zero_decimal_currencies = array(
		'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga',
		'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
	);

	$unit_amount = in_array($currency, $zero_decimal_currencies)
		? (int) $amount
		: (int) round($amount * 100);

	// Metadata to attach to the payment for webhook identification.
	$metadata = array(
		'subscription_id' => $subscription_id,
		'member_id' => $member_id,
		'forum_url' => $boardurl,
	);

	// Build the Stripe API request.
	$post_fields = array(
		'success_url' => $return_url,
		'cancel_url' => $return_url,
		'payment_method_types[0]' => 'card',
		'metadata[subscription_id]' => $metadata['subscription_id'],
		'metadata[member_id]' => $metadata['member_id'],
		'metadata[forum_url]' => $metadata['forum_url'],
	);

	// Determine mode: subscription (recurring) or payment (one-time).
	if ($repeatable)
	{
		// Recurring subscription via Stripe.
		$post_fields['mode'] = 'subscription';

		// Map SMF period to Stripe interval.
		$period = isset($_POST['period']) ? strtoupper($_POST['period']) : 'M';
		$period_unit = isset($_POST['period_unit']) ? (int) $_POST['period_unit'] : 1;

		$interval_map = array(
			'D' => 'day',
			'W' => 'week',
			'M' => 'month',
			'Y' => 'year',
		);

		$interval = isset($interval_map[$period]) ? $interval_map[$period] : 'month';
		$interval_count = max(1, $period_unit);

		$post_fields['line_items[0][price_data][currency]'] = $currency;
		$post_fields['line_items[0][price_data][product_data][name]'] = $item_name;
		$post_fields['line_items[0][price_data][unit_amount]'] = $unit_amount;
		$post_fields['line_items[0][price_data][recurring][interval]'] = $interval;
		$post_fields['line_items[0][price_data][recurring][interval_count]'] = $interval_count;
		$post_fields['line_items[0][quantity]'] = 1;

		// Attach metadata to the subscription for renewal/cancel tracking.
		$post_fields['subscription_data[metadata][subscription_id]'] = $metadata['subscription_id'];
		$post_fields['subscription_data[metadata][member_id]'] = $metadata['member_id'];
		$post_fields['subscription_data[metadata][forum_url]'] = $metadata['forum_url'];
	}
	else
	{
		// One-time payment.
		$post_fields['mode'] = 'payment';

		$post_fields['line_items[0][price_data][currency]'] = $currency;
		$post_fields['line_items[0][price_data][product_data][name]'] = $item_name;
		$post_fields['line_items[0][price_data][unit_amount]'] = $unit_amount;
		$post_fields['line_items[0][quantity]'] = 1;

		// Attach metadata to the payment intent for refund tracking.
		$post_fields['payment_intent_data[metadata][subscription_id]'] = $metadata['subscription_id'];
		$post_fields['payment_intent_data[metadata][member_id]'] = $metadata['member_id'];
		$post_fields['payment_intent_data[metadata][forum_url]'] = $metadata['forum_url'];
	}

	// Make the API call to create the Checkout Session.
	$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer ' . $modSettings['stripe_secret_key'],
	));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	$response = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curl_error = curl_error($ch);
	curl_close($ch);

	// Handle errors.
	if (empty($response) || $http_code !== 200)
	{
		loadLanguage('ManagePaid');
		log_error(
			'Stripe Checkout Session creation failed. HTTP ' . $http_code
			. (!empty($curl_error) ? ' cURL error: ' . $curl_error : '')
			. (!empty($response) ? ' Response: ' . $response : ''),
			'paidsubs'
		);
		fatal_error($txt['stripe_could_not_connect'], false);
	}

	$session = json_decode($response, true);

	if (empty($session['url']))
	{
		log_error('Stripe Checkout Session response missing URL. Response: ' . $response, 'paidsubs');
		fatal_error($txt['stripe_could_not_connect'], false);
	}

	// Redirect the user to Stripe's hosted checkout page.
	header('Location: ' . $session['url']);
	exit;
}

?>
