<?php
/**
 * PayPal Express Checkout Plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Gateway_PPEC_Plugin {

	/**
	 * Filepath of main plugin file.
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Absolute plugin path.
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Absolute plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Absolute path to plugin includes dir.
	 *
	 * @var string
	 */
	public $includes_path;

	/**
	 * Flag to indicate the plugin has been boostrapped.
	 *
	 * @var bool
	 */
	private $_bootstrapped = false;

	/**
	 * Instance of WC_Gateway_PPEC_Settings.
	 *
	 * @var WC_Gateway_PPEC_Settings
	 */
	public $settings;

	/**
	 * Constructor.
	 *
	 * @param string $file    Filepath of main plugin file
	 * @param string $version Plugin version
	 */
	public function __construct( $file, $version ) {
		$this->file    = $file;
		$this->version = $version;

		// Path.
		$this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
		$this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
		$this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
	}

	/**
	 * Maybe run the plugin.
	 */
	public function maybe_run() {
		register_activation_hook( $this->file, array( $this, 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'bootstrap' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file ), array( $this, 'plugin_action_links' ) );
		add_filter( 'allowed_redirect_hosts' , array( $this, 'whitelist_paypal_domains_for_redirect' ) );
	}

	public function bootstrap() {
		try {
			if ( $this->_bootstrapped ) {
				throw new Exception( __( '%s in WooCommerce Gateway PayPal Express Checkout plugin can only be called once', 'woocommerce-gateway-paypal-express-checkout' ) );
			}

			$this->_check_dependencies();
			$this->_run();

			$this->_bootstrapped = true;
			delete_option( 'wc_gateway_ppce_bootstrap_warning_message' );
		} catch ( Exception $e ) {
			update_option( 'wc_gateway_ppce_bootstrap_warning_message', $e->getMessage() );
			add_action( 'admin_notices', array( $this, 'show_bootstrap_warning' ) );
		}
	}

	public function show_bootstrap_warning() {
		$dependencies_message = get_option( 'wc_gateway_ppce_bootstrap_warning_message', '' );
		if ( ! empty( $dependencies_message ) ) {
			?>
			<div class="error fade">
				<p>
					<strong><?php echo esc_html( $dependencies_message ); ?></strong>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Check dependencies.
	 *
	 * @throws Exception
	 */
	protected function _check_dependencies() {
		if ( ! function_exists( 'WC' ) ) {
			throw new Exception( __( 'WooCommerce Gateway PayPal Express Checkout requires WooCommerce to be activated', 'woocommerce-gateway-paypal-express-checkout' ) );
		}

		if ( version_compare( WC()->version, '2.5', '<' ) ) {
			throw new Exception( __( 'WooCommerce Gateway PayPal Express Checkout requires WooCommerce version 2.5 or greater', 'woocommerce-gateway-paypal-express-checkout' ) );
		}
	}

	/**
	 * Run the plugin.
	 */
	protected function _run() {
		require_once( $this->includes_path . 'functions.php' );
		$this->_load_handlers();
	}

	/**
	 * Callback for activation hook.
	 */
	public function activate() {
		// Enable some options that we recommend for all merchants.
		add_option( 'pp_woo_allowGuestCheckout', true );
		add_option( 'pp_woo_enableInContextCheckout', true );
		/* defer ppc for next next release.
		add_option( 'pp_woo_ppc_enabled', true );
		*/

		if ( ! isset( $this->setings ) ) {
			require_once( $this->includes_path . 'class-wc-gateway-ppec-settings.php' );
			$settings = new WC_Gateway_PPEC_Settings();
		} else {
			$settings = $this->settings;
		}

		// Force zero decimal on specific currencies.
		if ( $settings->currency_has_decimal_restriction() ) {
			update_option( 'woocommerce_price_num_decimals', 0 );
			update_option( 'wc_gateway_ppce_display_decimal_msg', true );
		}
	}

	/**
	 * Load handlers.
	 */
	protected function _load_handlers() {
		// Client.
		require_once( $this->includes_path . 'abstracts/abstract-wc-gateway-ppec-client-credential.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-client-credential-certificate.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-client-credential-signature.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-client.php' );

		// Load handlers.
		require_once( $this->includes_path . 'class-wc-gateway-ppec-settings.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-gateway-loader.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-admin-handler.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-checkout-handler.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-cart-handler.php' );
		require_once( $this->includes_path . 'class-wc-gateway-ppec-ips-handler.php' );

		$this->settings = new WC_Gateway_PPEC_Settings();
		$this->settings->loadSettings();

		$this->gateway_loader = new WC_Gateway_PPEC_Gateway_Loader();
		$this->admin          = new WC_Gateway_PPEC_Admin_Handler();
		$this->checkout       = new WC_Gateway_PPEC_Checkout_Handler();
		$this->cart           = new WC_Gateway_PPEC_Cart_Handler();
		$this->ips            = new WC_Gateway_PPEC_IPS_Handler();

		$this->client = new WC_Gateway_PPEC_Client( $this->settings->getActiveApiCredentials(), $this->settings->environment );
	}

	/**
	 * Adds plugin action links
	 *
	 * @since 1.0.0
	 */
	public function plugin_action_links( $links ) {

		$section_slug = strtolower( 'WC_Gateway_PPEC_With_PayPal' );

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_slug ) . '">' . __( 'Settings', 'woocommerce-gateway-paypal-express-checkout' ) . '</a>',
			'<a href="http://docs.woothemes.com/document/woocommerce-gateway-paypal-express-checkout/">' . __( 'Docs', 'woocommerce-gateway-paypal-express-checkout' ) . '</a>',
			'<a href="http://support.woothemes.com/">' . __( 'Support', 'woocommerce-gateway-paypal-express-checkout' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Allow PayPal domains for redirect.
	 *
	 * @since 1.0.0
	 *
	 * @param array $domains Whitelisted domains for `wp_safe_redirect`
	 *
	 * @return array $domains Whitelisted domains for `wp_safe_redirect`
	 */
	public function whitelist_paypal_domains_for_redirect( $domains ) {
		$domains[] = 'www.paypal.com';
		$domains[] = 'paypal.com';
		$domains[] = 'www.sandbox.paypal.com';
		$domains[] = 'sandbox.paypal.com';

		return $domains;
	}

}
