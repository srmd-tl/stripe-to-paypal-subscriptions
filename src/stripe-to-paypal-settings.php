<?php
defined( 'ABSPATH' ) or exit;

class StripeToPaypalSettings {
	const TYPE = [ 'STRIPE' => 'stripe', 'PAYPAL' => 'paypal' ];
	/**
	 * @var StripeToPaypalSettings single instance of this file;
	 */
	protected static $instance;

	public function __construct() {

	}

	/**
	 * Returns the Memberships instance singleton.
	 *
	 * Ensures only one instance is/can be loaded.
	 * @return StripeToPaypalSettings
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
	public function add_config_item() {
		add_options_page( __( 'Stripe To PayPal Subscription Exporter-Options', 'stripe-to-paypal-exporter' ), __( 'Stripe To PayPal Configs', 'stripe-to-paypal-exporter' ), 'manage_options', 'stripe-to-paypal-exporter-settings', [
			$this,
			'template'
		] );
	}

	/** Step 3. */
	public function template() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$stripeCreds = get_option( 'stripe_api_creds' );
		if ( $stripeCreds ) {
			$stripeCreds = unserialize( $stripeCreds );

		}
		$paypalCreds = get_option( 'paypal_api_creds' );
		if ( $paypalCreds ) {
			$paypalCreds = unserialize( $paypalCreds );

		}
		?>
        <div class="wrap">
            <div id="icon-tools" class="icon32"></div>
            <h2>My API Keys Page</h2>
            <form action="" method="POST">
                <h3>Stripe API Key</h3>
                <div>
                    <input type="text" name="stripe_pub_key" placeholder="Publishable  Key" required value="<?=$stripeCreds['pub_key']?>">
                    <input type="text" name="stripe_sec_key" placeholder="Secret  Key" required value="<?=$stripeCreds['secret_key']?>">
                    <input type="hidden" name="action" value="stripe">
                    <input type="submit" name="submit" id="submit" class="update-button button button-primary"
                           value="Update API Key"/>
                </div>

            </form>

            <form action="" method="POST">
                <h3>PayPal API Key</h3>
                <div>
                    <input type="text" name="paypal_client_id" placeholder="Client ID" value="<?=$paypalCreds['client_id']?>">
                    <input type="text" name="paypal_sec_key" placeholder="Secret  Key" value="<?=$paypalCreds['secret_key']?>">
                    Live <input type="checkbox" name="paypal_mode"  <?= $paypalCreds['mode']=='on'?'checked':''?>>

                    <input type="hidden" name="action" value="paypal">
                    <input type="submit" name="submit" id="submit" class="update-button button button-primary"
                           value="Update API Key"/>
                </div>

            </form>
        </div>
		<?php
	}

	/**
	 * @throws Exception
	 */
	public function storeKeys() {

		//bale if user is not admin
		if ( ! current_user_can( 'manage_options' ) ) {
			throw new Exception( 'Not enough access' );
		}
		if ( ! isset( $_POST['action'] ) && empty( $_POST['action'] ) ) {
			throw new Exception( 'Not enough access' );
		}
		$payload = [];
		$type    = sanitize_text_field( $_POST['action'] );

		if ( self::TYPE['STRIPE'] === $type ) {

			$apiExists = get_option( 'stripe_api_creds' );
			if ( $apiExists ) {
				$apiExists = unserialize( $apiExists );

				$apiExists = count( $apiExists ) > 0 ?? false;
			}
			$payload['pub_key']    = sanitize_text_field( $_POST['stripe_pub_key'] );
			$payload['secret_key'] = sanitize_text_field( $_POST['stripe_sec_key'] );
			if ( ! $apiExists && ! empty( $_POST['stripe_pub_key'] ) && ! empty( $_POST['stripe_sec_key'] ) ) {


				add_option( 'stripe_api_creds', serialize( $payload ) );

			} else if ( $apiExists && ! empty( $_POST['stripe_pub_key'] ) && ! empty( $_POST['stripe_sec_key'] ) ) {

				update_option( 'stripe_api_creds', serialize( $payload ) );
			}
		} else if ( self::TYPE['PAYPAL'] === $type ) {

			$apiExists = get_option( 'paypal_api_creds' );
			if ( $apiExists ) {
				$apiExists = unserialize( $apiExists );

				$apiExists = count( $apiExists ) > 0 ?? false;
			}

			$payload['client_id']  = sanitize_text_field( $_POST['paypal_client_id'] );
			$payload['secret_key'] = sanitize_text_field( $_POST['paypal_sec_key'] );
			$payload['mode'] = sanitize_text_field( $_POST['paypal_mode'] );

			if ( ! $apiExists && ! empty( $_POST['paypal_client_id'] ) && ! empty( $_POST['paypal_sec_key'] ) ) {

				add_option( 'paypal_api_creds', serialize( $payload ) );

			} else if ( $apiExists && self::TYPE['PAYPAL'] === $type ) {
				update_option( 'paypal_api_creds', serialize( $payload ) );
			}
		}


		return 1;

	}


}

if ( $_POST['action'] ?? false ) {

	$obj = StripeToPaypalSettings::instance();
	try {
		$obj->storeKeys();
	} catch ( Exception $e ) {
		var_dump( $e->getMessage() );
	}
}