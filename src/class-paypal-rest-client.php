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
			'Content-Type'  => 'application/json',
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
		$headers  = [ 'headers' => $this->headers, 'body' => [ 'grant_type' => 'client_credentials' ] ];
		$url      = $this->base_url . '/oauth2/token';
		$response = wp_remote_post( $url, $headers );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			throw new Exception( wp_remote_retrieve_response_message( $response ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Create Product.
	 *
	 * @param $data
	 *
	 * @return object
	 * @throws Exception
	 */
	public function createProduct( $data ): object {
		try {
			$headers                      = [ 'headers' => $this->headers ];
			$headers['headers']['Prefer'] = 'minimal';
			$headers['body']              = json_encode( [
				'id'          => $data['id'],
				'name'        => $data['name'],
				'type'        => strtoupper( $data['type'] ),
				'description' => $data['description']
			] );
			$url                          = $this->base_url . '/catalogs/products';
			$response                     = wp_remote_post( $url, $headers );
			if ( ! in_array( wp_remote_retrieve_response_code( $response ), [ 200, 202, 201, 204 ] ) ) {
				throw new Exception( wp_remote_retrieve_response_message( $response ) );
			}

			return json_decode( wp_remote_retrieve_body( $response ) );

		} catch ( Exception $e ) {
			throw new Exception( $e->getMessage() );

		}

	}

	/**
	 * Create Plan.
	 *
	 * @param $data
	 *
	 * @return object
	 * @throws Exception
	 */
	public function createPlan( $data ): object {
		try {
			$headers                      = [ 'headers' => $this->headers ];
			$headers['headers']['Prefer'] = 'minimal';

			$headers['body'] = json_encode( [
				'billing_cycles'      => [ $data['billing_cycles'] ],
				'name'                => $data['name'],
				'payment_preferences' => $data['payment_preferences'],
				'product_id'          => $data['product_id'],
				'status'              => $data['status']
			] );


			$url      = $this->base_url . '/billing/plans';
			$response = wp_remote_post( $url, $headers );
			if ( ! in_array( wp_remote_retrieve_response_code( $response ), [ 200, 202, 201, 204 ] ) ) {
				throw new Exception( wp_remote_retrieve_response_message( $response ) );
			}

			return json_decode( wp_remote_retrieve_body( $response ) );

		} catch ( Exception $exception ) {
			throw new Exception( $exception->getMessage() );
		}

	}

	public function createSubscription( $data ) {
		$headers  = [ 'headers' => $this->headers, 'body' => [ 'grant_type' => 'client_credentials' ] ];
		$url      = $this->base_url . '/billing/subscriptions';
		$response = wp_remote_post( $url, $headers );
		if ( ! in_array( wp_remote_retrieve_response_code( $response ), [ 200, 202, 201, 204 ] ) ) {
			throw new Exception( wp_remote_retrieve_response_message( $response ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}
}