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
	public function process()
	{
		$obj=StripeToPaypal_StripeClient::instance();
		$paypalObj=StripeToPaypal_PaypalClient::instance();

		echo "<pre>";
		print_r($paypalObj->getToken());
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