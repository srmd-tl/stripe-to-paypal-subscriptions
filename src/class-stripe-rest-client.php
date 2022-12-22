<?php
defined( 'ABSPATH' ) or exit;

class StripeToPaypal_StripeClient {
	/**
	 * @var StripeToPaypal_StripeClient single instance of this file;
	 */
	protected static $instance;

	private $stripeCreds;
	private $base_url;
	private $headers;


	public function __construct() {
		$this->stripeCreds = $this->getCreds();
		$this->base_url    = 'https://api.stripe.com/v1';
		$this->headers     = [
			'Authorization' => 'Bearer ' . $this->stripeCreds['secret_key']
		];
	}

	public function getCreds() {
		$creds = get_option( 'stripe_api_creds' );

		if ( $creds ) {
			$creds = unserialize( $creds );
		}
		return $creds;

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

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getStripeSubscriptions() {
		$headers  = [ 'headers' => $this->headers ];
		$url =$this->base_url . '/subscriptions';
		$response = wp_remote_get($url , $headers );
		if ( wp_remote_retrieve_response_code($response) != 200 ) {
			throw new Exception( wp_remote_retrieve_response_message($response) );
		}
		return json_decode(wp_remote_retrieve_body($response));
	}

	public function getStripePlan() {

	}
}
