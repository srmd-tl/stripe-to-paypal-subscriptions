<?php
defined( 'ABSPATH' ) or exit;
class StripeToPaypalSettings
{
	public function __construct()
	{

	}



	/** Step 1. */
	public function add_config_item() {
		add_options_page( __('Stripe To PayPal Subscription Exporter-Options','stripe-to-paypal-exporter'), __('Stripe To PayPal Configs','stripe-to-paypal-exporter'), 'manage_options', 'stripe-to-paypal-exporter-settings', [$this,'template'] );
	}

	/** Step 3. */
	public function template() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
        <div class="wrap"><div id="icon-tools" class="icon32"></div>
            <h2>My API Keys Page</h2>
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                <h3>Stripe API Key</h3>
                <div>
                    <input type="text" name="stripe_pub_key" placeholder="Publishable  Key">
                    <input type="text" name="stripe_sec_key" placeholder="Secret  Key">
                    <input type="hidden" name="action" value="process_form">
                    <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update API Key"  />
                </div>

            </form>

            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                <h3>PayPal API Key</h3>
                <div>
                    <input type="text" name="paypal_client_id" placeholder="Client ID">
                    <input type="text" name="paypal_sec_key" placeholder="Secret  Key">
                    <input type="hidden" name="action" value="process_form">
                    <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update API Key"  />
                </div>

            </form>
        </div>
		<?php
	}
	private function storeKeys()
	{

	}


}