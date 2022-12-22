<?php

/**
 * Plugin Name: Stripe To PayPal Subscription Exporter.
 * Plugin URI: https://www.woocommerce.com/products/woocommerce-memberships/
 * Description: Export subscription related data from Stripe to Paypal.
 * Author: RevvLab
 * Author URI: https://www.revvlab.com/
 * Version: 1.0.0
 * Text Domain: stripe-to-paypal-exporter
 * Domain Path: /i18n/languages/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
defined( 'ABSPATH' ) or exit;
/**
 * Stripe To PayPal Subscription Exporter plugin loader.
 *
 * @since 1.0.0
 */
class StripeToPaypalSubscriptionExporter {
	/** minimum PHP version required by this plugin */
	const MINIMUM_PHP_VERSION = '7.4';

	/** minimum WordPress version required by this plugin */
	const MINIMUM_WP_VERSION = '6.1';

	/** the plugin name, for displaying notices */
	const PLUGIN_NAME = 'Stripe To PayPal Subscription Exporter';

	/** @var StripeToPaypalSubscriptionExporter single instance of this plugin */
	protected static $instance;

	/**
	 * Loads WooCommerce Memberships after performing environment checks.
	 *
	 * @since 1.11.0
	 */
	protected function __construct() {

		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );

		add_action( 'admin_init',    array( $this, 'is_environment_compatible' ) );

		// if the environment check fails, initialize the plugin
		if ( $this->is_environment_compatible() ) {

			add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		}

	}
	/**
	 * Determines if the server environment is compatible with Memberships.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_environment_compatible(): bool {

		return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' );
	}
	/**
	 * Initializes the plugin.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 */
	public function init_plugin() {

		if ( ! $this->plugins_compatible() ) {
			return;
		}

		// load the main plugin class
		require_once( plugin_dir_path( __FILE__ ) . 'class-stripe-to-paypal-subscription-exporter.php' );

		// fire it up!
		stripe_to_paypal();
	}
	/**
	 * Determines if the WordPress compatible.
	 *
	 * @since 1.11.0
	 *
	 * @return bool
	 */
	protected function is_wp_compatible(): bool {

		return version_compare( get_bloginfo( 'version' ), self::MINIMUM_WP_VERSION, '>=' );
	}

	/**
	 * Determines if the required plugins are compatible.
	 *
	 * @since 1.11.0
	 *
	 * @return bool
	 */
	protected function plugins_compatible() {

		return $this->is_wp_compatible();
	}

	/**
	 * Returns the main \WC_Memberships_Loader instance.
	 *
	 * Ensures only one instance can be loaded.
	 *
	 * @since 1.11.0
	 *
	 * @return \StripeToPaypalSubscriptionExporter
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}
}
// fire it up!
StripeToPaypalSubscriptionExporter::instance();


