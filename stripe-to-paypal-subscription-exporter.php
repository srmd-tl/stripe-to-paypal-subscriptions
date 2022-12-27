<?php

/**
 * Plugin Name: Stripe To PayPal Subscription Exporter.
 * Plugin URI: https://www.revvlab.com/
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

		register_activation_hook( __FILE__, [ $this, 'activation_check' ] );

		add_action( 'admin_init', [ $this, 'is_environment_compatible' ] );
		add_action( 'admin_init', [ $this, 'is_crontrol_active' ] );


		// if the environment check fails, initialize the plugin
		if ( $this->is_environment_compatible() ) {

			add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
		}
		if ( ! $this->is_crontrol_active() ) {
			echo '<div class="notice notice-error is-dismissible">
      <p>StripeToPayPalExporter: Activate Crontrol Plugin.</p>
      </div>';
		}

	}

	/**
	 * Determines if the server environment is compatible with Memberships.
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function is_environment_compatible(): bool {

		return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' );
	}

	public function is_crontrol_active(): bool {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'wp-crontrol/wp-crontrol.php' ) ?? false;
	}

	/**
	 * Returns the main \WC_Memberships_Loader instance.
	 *
	 * Ensures only one instance can be loaded.
	 *
	 * @return StripeToPaypalSubscriptionExporter
	 * @since 1.11.0
	 *
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
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
	 * Determines if the required plugins are compatible.
	 *
	 * @return bool
	 * @since 1.11.0
	 *
	 */
	protected function plugins_compatible() {

		return $this->is_wp_compatible();
	}

	/**
	 * Determines if the WordPress compatible.
	 *
	 * @return bool
	 * @since 1.11.0
	 *
	 */
	protected function is_wp_compatible(): bool {

		return version_compare( get_bloginfo( 'version' ), self::MINIMUM_WP_VERSION, '>=' );
	}
}

// fire it up!
StripeToPaypalSubscriptionExporter::instance();


