<?php
defined( 'ABSPATH' ) or exit;

class StripeToPaypalAPI {
	/**
	 * @var StripeToPaypalSettings single instance of this file;
	 */
	protected static $instance;
	private $stripeCreds;
	private $paypalCreds;

	public function __construct() {

	}

	/**
	 * Returns the Memberships instance singleton.
	 *
	 * Ensures only one instance is/can be loaded.
	 * @return StripeToPaypalAPI
	 * @since 1.0.0
	 *
	 * @see wc_memberships()
	 *
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

	/** Step 1. */
	public function add_menu() {
		add_menu_page(
			__( 'Stripe To PayPal Subscription Exporter-Process', 'stripe-to-paypal-exporter' ),
			__( 'Stripe To PayPal Exporter', 'stripe-to-paypal-exporter' ),
			'manage_options',
			'stripe-to-paypal-exporter-process',
			[
				$this,
				'process'
			],
			plugins_url( 'myplugin/images/icon.png' ),
			6


		);
	}


	/**
	 * @throws Exception
	 */
	public function process() {
		$obj       = StripeToPaypal_StripeClient::instance();
		$paypalObj = StripeToPaypal_PaypalClient::instance();

		try {
			//Fetch products from stripe and store in paypal.
			$startingAfter = null;
			do {
				$products = $obj->getStripeProducts( $startingAfter );
				foreach ( $products->data as $product ) {
					$data = [
						'name'        => $product->name,
						'description' => $product->description,
						'id'          => $product->id,
						'type'        => $product->type
					];

					$paypalObj->createProduct( $data );
				}
				$next = $products->has_more;
			} while ( $next );
		} catch ( Exception $e ) {
			error_log( print_r( $e->getMessage(),true) );
			print_r( $e->getMessage() );
		}

		try {
			//Fetch plans from stripe and store in paypal.
			$startingAfter = null;
			do {

				$plans = $obj->getStripePlans( $startingAfter );
				foreach ( $plans->data as $plan ) {
					$isTrial      = $plan->trial_period_days ? true : false;
					$billingCycle = [
						'frequency'      => [
							'interval_unit'  => $plan->interval,
							'interval_count' => $plan->interval_count,
						],
						'sequence'       => 1,
						'tenure_type'    => $isTrial ? 'TRIAL' : 'REGULAR',
						'total_cycles'   => $plan->interval_count,
						'pricing_scheme' => [
							'fixed_price' => [
								'value'         => $plan->amount,
								'currency_code' => strtoupper( $plan->currency )
							]
						]
					];
					$paymentPref  = [
						'auto_bill_outstanding'     => true,
						'setup_fee'                 => [
							'value'         => $plan->amount,
							'currency_code' => strtoupper( $plan->currency )
						],
						'setup_fee_failure_action'  => 'CONTINUE',
						'payment_failure_threshold' => 3
					];
					$data         = [
						'billing_cycles'      => $billingCycle,
						'name'                => $plan->nickname ?? $plan->id,
						'payment_preferences' => $paymentPref,
						'product_id'          => $plan->product,
						'status'              => $plan->active ? 'ACTIVE' : 'INACTIVE'
					];
					$paypalObj->createPlan( $data );
				}
				$startingAfter = $plan->id;
				$next          = $plans->has_more;
			} while ( $next );
		} catch ( Exception $e ) {
			error_log( print_r( $e->getMessage(),true) );
			print_r( $e->getMessage() );
		}

		//Fetch subscriptions from stripe and store in paypal.
		$startingAfter = null;
		do {
			$subscriptions = $obj->getStripeSubscriptions();
			foreach ( $subscriptions as $subscription ) {
				//save in paypal.
			}
			$startingAfter = $subscription->id;
			$next          = $subscriptions->has_more;
		} while ( $next );

		echo "<pre>";
//		print_r($paypalObj->getToken());
		print_r( $obj->getStripeSubscriptions() );
	}
}

//if ( $_POST['action'] ?? false ) {
//
//	$obj = StripeToPaypalAPI::instance();
//	try {
//		$obj->storeKeys();
//	} catch ( Exception $e ) {
//		var_dump( $e->getMessage() );
//	}
//}