<?php
defined( 'ABSPATH' ) or exit;

class StripeToPaypal_PaypalClient {
	/**
	 * @var StripeToPaypal_PaypalClient single instance of this file;
	 */
	protected static $instance;

	private $paypalCreds;
	private $base_url;
	private $headers;


	public function __construct() {
		$this->paypalCreds = $this->getCreds();
		$this->base_url    = 'https://api-m.sandbox.paypal.com/v1';
		$creds             = $this->paypalCreds['client_id'] . ':' . $this->paypalCreds['secret_key'];
		$this->headers     = [
			'Authorization' => 'Basic ' . base64_encode( $creds ),
			'Content-Type'  => 'application/x-www-form-urlencoded'
		];
	}

	public function getCreds() {
		$creds = get_option( 'paypal_api_creds' );

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
	 * @throws Exception
	 */
	public function getToken() {
		$headers = [ 'headers' => $this->headers, 'body' => [ 'grant_type' => 'client_credentials' ] ];
		$url      = $this->base_url . '/oauth2/token';
		$response = wp_remote_post( $url, $headers );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			throw new Exception( wp_remote_retrieve_response_message( $response ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );

	}
}