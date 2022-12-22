<?php
defined( 'ABSPATH' ) or exit;

/**
 * Class Stripe_To_Paypal_Subscription_Exporter
 */
class Stripe_To_Paypal_Subscription_Exporter {
	/** @var Stripe_To_Paypal_Subscription_Exporter single instance of this plugin */
	protected static $instance;
	/** @var string plugin path, without trailing slash */
	private $plugin_path;
	private $setting_class;
	private $rest_api_class;
	private $stripe_rest_client;
	private $paypal_rest_client;


	public function __construct() {
		$this->includes();
		/** Step 2 (from text above). */
		add_action( 'admin_menu', [ $this->setting_class, 'add_config_item' ] );
		add_action( 'admin_menu', [ $this->rest_api_class, 'add_menu' ] );

	}

	/**
	 * Includes required files.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		$this->setting_class      = $this->load_class( '/src/stripe-to-paypal-settings.php', 'StripeToPaypalSettings' );
		$this->rest_api_class     = $this->load_class( '/src/stripe-to-paypal-rest-apis.php', 'StripeToPaypalAPI' );
		$this->stripe_rest_client = $this->load_class( '/src/class-stripe-rest-client.php', 'StripeToPaypal_StripeClient' );
		$this->paypal_rest_client = $this->load_class( '/src/class-paypal-rest-client.php', 'StripeToPaypal_PaypalClient' );


	}

	/**
	 * Require and instantiate a class
	 *
	 * @param string $local_path path to class file in plugin, e.g. '/includes/class-wc-foo.php'
	 * @param string $class_name class to instantiate
	 *
	 * @return object instantiated class instance
	 * @since 4.2.0
	 */
	public function load_class( $local_path, $class_name ): object {

		require_once( $this->get_plugin_path() . $local_path );

		return new $class_name;
	}

	/**
	 * Gets the plugin's path without a trailing slash.
	 *
	 * e.g. /path/to/wp-content/plugins/plugin-directory
	 *
	 * @return string
	 * @since 2.0.0
	 *
	 */
	public function get_plugin_path() {

		if ( null === $this->plugin_path ) {
			$this->plugin_path = untrailingslashit( plugin_dir_path( $this->get_file() ) );
		}

		return $this->plugin_path;
	}

	/**
	 * Returns the plugin filename path.
	 *
	 * @return string the full path and filename of the plugin file
	 * @since 1.0.0
	 *
	 */
	protected function get_file() {

		return __FILE__;
	}

	/**
	 * Returns the Memberships instance singleton.
	 *
	 * Ensures only one instance is/can be loaded.
	 * @return Stripe_To_Paypal_Subscription_Exporter
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
	 * Loads the plugin main classes.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 */
	public function init_plugin() {

		$this->load_class( '/src/class-wc-memberships-rules.php', 'WC_Memberships_Rules' );
	}


}

/**
 * Returns the One True Instance of Stripe_To_Paypal_Subscription_Exporter.
 *
 * @return Stripe_To_Paypal_Subscription_Exporter
 * @since 1.0.0
 *
 */
function stripe_to_paypal() {

	return Stripe_To_Paypal_Subscription_Exporter::instance();
}