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
	 * @return StripeToPaypal_StripeClient
	 * @since 1.0.0
	 *
	 * @see wc_memberships()
	 *
	 */
	public static function instance(): StripeToPaypal_StripeClient {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get all subscriptions.
	 *
	 * @param null $startingAfter
	 *
	 * @return object
	 * @throws Exception
	 */
	public function getStripeSubscriptions( $startingAfter = null ): object {
		$headers = [
			'headers' => $this->headers,
			'body'    => [ 'limit' => 100 ]
		];
		if ( $startingAfter ) {
			$headers['body']['starting_after'] = $startingAfter;
		}
		$url      = $this->base_url . '/subscriptions';
		$response = wp_remote_get( $url, $headers );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			throw new Exception( wp_remote_retrieve_response_message( $response ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get all prices/plans
	 *
	 * @param null $startingAfter
	 *
	 * @return object
	 * @throws Exception
	 */
	public function getStripePlans( $startingAfter = null ): object {
		$headers = [ 'headers' => $this->headers, 'body' => [ 'limit' => 100 ] ];
		if ( $startingAfter ) {
			$headers['body']['starting_after'] = $startingAfter;
		}
		$url      = $this->base_url . '/plans';
		$response = wp_remote_get( $url, $headers );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			throw new Exception( wp_remote_retrieve_response_message( $response ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get all products.
	 *
	 * @param null $startingAfter the last object id.
	 *
	 * @return object
	 * @throws Exception
	 */
	public function getStripeProducts( $startingAfter = null ): object {
		$headers = [ 'headers' => $this->headers, 'body' => [ 'limit' => 100 ] ];
		if ( $startingAfter ) {
			$headers['body']['starting_after'] = $startingAfter;
		}
		$url      = $this->base_url . '/products';
		$response = wp_remote_get( $url, $headers );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			throw new Exception( wp_remote_retrieve_response_message( $response ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get customer info by its id.
	 * @param $id
	 *
	 * @return object
	 * @throws Exception
	 */
	public function getCustomer( $id ):object {
		$headers = [ 'headers' => $this->headers ];

		$url      = $this->base_url . '/customers/' . $id;
		$response = wp_remote_get( $url, $headers );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			throw new Exception( wp_remote_retrieve_response_message( $response ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}
}
