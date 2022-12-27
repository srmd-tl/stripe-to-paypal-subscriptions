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

	public function add_menu() {
		add_menu_page(
			__( 'Stripe To PayPal Subscription Exporter-Process', 'stripe-to-paypal-exporter' ),
			__( 'Stripe To PayPal Exporter', 'stripe-to-paypal-exporter' ),
			'manage_options',
			'stripe-to-paypal-exporter-process',
			[
				$this,
				'process',
			],
			plugins_url( 'myplugin/images/icon.png' ),
			6


		);
	}

	/**
	 * @throws Exception
	 */
	public function process() {
		error_log('Cronstarted at'.date('y-m-d h:i:s'));
		$obj       = StripeToPaypal_StripeClient::instance();
		$paypalObj = StripeToPaypal_PaypalClient::instance();
		$i=0;

		try {
			//Fetch products from stripe and store in paypal.
			$productId     = get_option( 'start_after_product_id' ) ?? null;
			$startingAfter = null;
			if ( $productId ) {
				$startingAfter = $productId;
			}
			do {
				$products = $obj->getStripeProducts( $startingAfter );
				foreach ( $products->data as $product ) {
					$i++;
					echo $i.PHP_EOL;
					$data      = [
						'name'        => $product->name,
						'description' => $product->description,
						'id'          => $product->id,
						'type'        => $product->type,
					];
					$productId = $product->id;
					$this->updateOrCreateOption( 'start_after_product_id', $productId );
					try {
						$paypalObj->createProduct( $data );
					}
					catch (Exception $e )
					{
						error_log( print_r( $e->getMessage(), true ) );
					}
				}
				$next = $products->has_more;
				$startingAfter = $productId;

			} while ( $next );
			$this->updateOrCreateOption( 'start_after_product_id', $productId );

		} catch ( Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
			print_r( $e->getMessage() );
		}
//		die();
//
//		try {
//			//Fetch plans from stripe and store in paypal.
//			$startingAfter = null;
//			do {
//
//				$plans = $obj->getStripePlans( $startingAfter );
//				foreach ( $plans->data as $plan ) {
//					$isTrial      = $plan->trial_period_days ? true : false;
//					$billingCycle = [
//						'frequency'      => [
//							'interval_unit'  => $plan->interval,
//							'interval_count' => $plan->interval_count,
//						],
//						'sequence'       => 1,
//						'tenure_type'    => $isTrial ? 'TRIAL' : 'REGULAR',
//						'total_cycles'   => $plan->interval_count,
//						'pricing_scheme' => [
//							'fixed_price' => [
//								'value'         => $plan->amount,
//								'currency_code' => strtoupper( $plan->currency )
//							]
//						]
//					];
//					$paymentPref  = [
//						'auto_bill_outstanding'     => true,
//						'setup_fee'                 => [
//							'value'         => $plan->amount,
//							'currency_code' => strtoupper( $plan->currency )
//						],
//						'setup_fee_failure_action'  => 'CONTINUE',
//						'payment_failure_threshold' => 3
//					];
//					$data         = [
//						'billing_cycles'      => $billingCycle,
//						'name'                => $plan->nickname ?? $plan->id,
//						'payment_preferences' => $paymentPref,
//						'product_id'          => $plan->product,
//						'status'              => $plan->active ? 'ACTIVE' : 'INACTIVE'
//					];
//					$paypalObj->createPlan( $data );
//				}
//				$startingAfter = $plan->id;
//				$next          = $plans->has_more;
//			} while ( $next );
//		} catch ( Exception $e ) {
//			error_log( print_r( $e->getMessage(),true) );
//			print_r( $e->getMessage() );
//		}

		//Fetch subscriptions from stripe and store in paypal.
		try {
			$startingAfter = null;
			$subscriptionId     = get_option( 'start_after_sub_id' ) ?? null;
			$startingAfter = null;
			if ( $subscriptionId ) {
				$startingAfter = $subscriptionId;
			}
			do {
				$subscriptions = $obj->getStripeSubscriptions( $startingAfter );
				foreach ( $subscriptions->data as $subscription ) {
					try {
						//customer data from stripe.
						$customer = $obj->getCustomer( $subscription->customer );
						$plan     = $subscription->plan;
						//Paypal store a plan.
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
									'currency_code' => strtoupper( $plan->currency ),
								],
							],
						];
						$paymentPref  = [
							'auto_bill_outstanding'     => true,
							'setup_fee'                 => [
								'value'         => $plan->amount,
								'currency_code' => strtoupper( $plan->currency ),
							],
							'setup_fee_failure_action'  => 'CONTINUE',
							'payment_failure_threshold' => 3,
						];
						$planData     = [
							'billing_cycles'      => $billingCycle,
							'name'                => $plan->nickname ?? $plan->id,
							'payment_preferences' => $paymentPref,
							'product_id'          => $plan->product??'prod_N2HA9wKNkAx5mp',
							'status'              => $plan->active ? 'ACTIVE' : 'INACTIVE',
						];
						$paypalPlan   = $paypalObj->createPlan( $planData );
						//Paypal Plan Created.
						$name         = null;
						if ( ! $customer->name ) {
							if ( isset( $customer->metadata ) ) {
								if ( isset( $customer->metadata->username ) ) {
									$name = $customer->metadata->username;
								}
							}
						}
						$customerShippingAddress = null;
						$customerAddressLine1    = null;
						$customerAddressLine2    = null;
						$adminArea2              = null;
						$countryCode             = null;

						if ( $customer->shipping ?? false ) {
							if ( $customer->shipping->address ?? false ) {
								if ( $customer->shipping->city ?? false ) {
									$customerShippingAddress .= $customer->shipping->city;
								}
								if ( $customer->shipping->country ?? false ) {
									$customerShippingAddress .= $customer->shipping->country;
								}
								if ( $customer->shipping->line1 ?? false ) {
									$customerAddressLine1 = $customer->shipping->line1;
								}
								if ( $customer->shipping->line2 ?? false ) {
									$customerAddressLine2 = $customer->shipping->line2;
								}
								if ( $customer->shipping->postal_code ?? false ) {
									$customerShippingAddress .= $customer->shipping->postal_code;
								}
								if ( $customer->shipping->state ?? false ) {
									$adminArea2 = $customer->shipping->state;
								}

							}

						}

						if ( $customer->email ?? false ) {
							$subscriber['email'] = $customer->email;
						}
						if ( $name && $customerAddressLine1 && $countryCode ) {
							$subscriber['name']['given_name']                    = $name;
							$subscriber['name']['surname']                       = $name;
							$subscriber['shipping_address']['name']['full_name'] = $name;
							$subscriber['shipping_address']['address']           = [ 'address_line_2' => $customerAddressLine2 ];
							$subscriber['shipping_address']['address']           = [ 'country_code' => $countryCode ];


							if ( $customerAddressLine1 ) {
								$subscriber['shipping_address']['address'] = [ 'address_line_1' => $customerAddressLine1 ];
							}
							if ( $customerAddressLine2 ) {
							}
							if ( $adminArea2 ) {
								$subscriber['shipping_address']['address'] = [ 'admin_area_2' => $adminArea2 ];
							}
						}
						$data = [
							'plan_id'         => $paypalPlan->id,
//						'quantity'        => $subscription->quantity,
//					'start_time'      => date('Y-m-d\TH:i:s\Z',$subscription->start_date),
							'shipping_amount' => [
								'currency_code' => strtoupper( $subscription->currency ),
								'value'         => $plan->amount,
							],
							'subscriber'      => $subscriber,
						];
						//save in paypal.
//				echo "<pre>";
//				print_r($data);
//				die();
						$subscriptionId=$subscription->id;
						$this->updateOrCreateOption( 'start_after_sub_id', $subscriptionId );
						$paypalObj->createSubscription( $data );


					} catch ( Exception $e ) {
						error_log( print_r( $e->getMessage(), true ) );
//			            print_r( $e->getMessage() );
					}


				}

//				print_r( "Data exported!" );
//				die();
				$startingAfter = $subscriptionId;
				$next          = $subscriptions->has_more;
			} while ( $next );
		} catch ( Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
			print_r( $e->getMessage() );
		}
		return;


//		echo "<pre>";
//		print_r($paypalObj->getToken());
//		print_r( $obj->getStripeSubscriptions() );
	}

	private function updateOrCreateOption( $key, $value ) {
		if ( get_option( $key ) ) {
			return update_option( $key, $value );
		}

		return add_option( $key, $value );

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