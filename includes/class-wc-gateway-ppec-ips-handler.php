<?php
/**
 * PayPal Express Integrated PayPal Signup Handler.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Gateway_PPEC_IPS_Handler {

	const MIDDLEWARE_BASE_URL = 'https://connect.woocommerce.com';

	/**
	 * Countries that support IPS.
	 *
	 * @var array
	 */
	private $_supported_countries = array(
		'AL', 'DZ', 'AO', 'AI', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ', 'BS',
		'BH', 'BB', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BA', 'BW', 'VG', 'BN',
		'BG', 'BF', 'BI', 'KH', 'CA', 'CV', 'KY', 'TD', 'CL', 'CN', 'C2', 'CO',
		'KM', 'CG', 'CK', 'CR', 'HR', 'CY', 'CZ', 'CD', 'DK', 'DJ', 'DM', 'DO',
		'EC', 'EG', 'SV', 'ER', 'EE', 'ET', 'FK', 'FM', 'FJ', 'FI', 'FR', 'GF',
		'PF', 'GA', 'GM', 'GE', 'DE', 'GI', 'GR', 'GL', 'GD', 'GP', 'GU', 'GT',
		'GN', 'GW', 'GY', 'VA', 'HN', 'HK', 'HU', 'IS', 'ID', 'IE', 'IT', 'JM',
		'JO', 'KZ', 'KE', 'KI', 'KW', 'KG', 'LA', 'LV', 'LS', 'LI', 'LT', 'LU',
		'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX',
		'MN', 'MS', 'MA', 'MZ', 'NA', 'NR', 'NP', 'NL', 'AN', 'NC', 'NZ', 'NI',
		'NE', 'NU', 'NF', 'NO', 'OM', 'PW', 'PA', 'PG', 'PE', 'PH', 'PN', 'PL',
		'PT', 'QA', 'RE', 'RO', 'RU', 'RW', 'SH', 'KN', 'LC', 'PM', 'VC', 'WS',
		'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO',
		'ZA', 'KR', 'ES', 'LK', 'SR', 'SJ', 'SZ', 'SE', 'CH', 'TW', 'TJ', 'TH',
		'TG', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE', 'GB',
		'TZ', 'US', 'UY', 'VU', 'VE', 'VN', 'WF', 'YE', 'ZM',
	);

	/**
	 * Get merchant redirect URL for IPS.
	 *
	 * This is store URL that will be redirected from middleware.
	 *
	 * @param string $env Environment
	 *
	 * @return string Redirect URL
	 */
	public function get_redirect_url( $env ) {
		if ( ! in_array( $env, array( 'live', 'sandbox' ) ) ) {
			$env = 'live';
		}

		return add_query_arg(
			array(
				'env'                     => $env,
				'wc_ppec_ips_admin_nonce' => wp_create_nonce( 'wc_ppec_ips' ),
			),
			wc_gateway_ppec()->admin->gateway_admin_url( 'WC_Gateway_PPEC_With_PayPal' )
		);
	}

	/**
	 * Get login URL to WC middleware.
	 *
	 * @param string $env Environment
	 *
	 * @return string Signup URL
	 */
	public function get_middleware_login_url( $env ) {
		$service = 'ppe';
		if ( 'sandbox' === $env ) {
			$service = 'ppesandbox';
		}

		return self::MIDDLEWARE_BASE_URL . '/login/' . $service;
	}

	/**
	 * Get signup URL to WC middleware.
	 *
	 * @param string $env Environment
	 *
	 * @return string Signup URL
	 */
	public function get_signup_url( $env ) {
		$query_args = array(
			'redirect'    => urlencode( $this->get_redirect_url( $env ) ),
			'countryCode' => WC()->countries->get_base_country(),
			'merchantId'  => md5( site_url( '/' ) . time() ),
		);

		return add_query_arg( $query_args, $this->get_middleware_login_url( $env ) );
	}

	/**
	 * Check if base location country supports IPS.
	 *
	 * @return bool Returns true of base country in supported countries
	 */
	public function is_supported() {
		return in_array( WC()->countries->get_base_country(), $this->_supported_countries );
	}

	/**
	 * Redirect with messages.
	 *
	 * @return void
	 */
	protected function _redirect_with_messages( $error_msg ) {
		if ( ! is_array( $error_msg ) ) {
			$error_msgs = array( array(
				'error' => $error_msg
			) );
		} else {
			$error_msgs = $error_msg;
		}

		add_option( 'woo_pp_admin_error', $error_msgs );
		wp_safe_redirect( wc_gateway_ppec()->admin->gateway_admin_url( 'WC_Gateway_PPEC_With_PayPal' ) );
		exit;
	}

	/**
	 * Maybe received credentials after successfully returned from IPS flow.
	 *
	 * @return mixed
	 */
	public function maybe_received_credentials() {
		if ( ! is_admin() || ! is_user_logged_in() ) {
			return false;
		}

		// Require the nonce.
		if ( empty( $_GET['wc_ppec_ips_admin_nonce'] ) || empty( $_GET['env'] ) ) {
			return false;
		}
		$env = in_array( $_GET['env'], array( 'live', 'sandbox' ) ) ? $_GET['env'] : 'live';

		// Verify the nonce.
		if ( ! wp_verify_nonce( $_GET['wc_ppec_ips_admin_nonce'], 'wc_ppec_ips') ) {
			wp_die( __( 'Invalid connection request', 'woocommerce-gateway-paypal-express-checkout' ) );
		}

		wc_gateway_ppec_log( sprintf( '%s: returned back from IPS flow with parameters: %s', __METHOD__, print_r( $_GET, true ) ) );

		// Check if error.
		if ( ! empty( $_GET['error'] ) ) {
			$error_message = ! empty( $_GET['error_message'] ) ? $_GET['error_message'] : '';
			wc_gateway_ppec_log( sprintf( '%s: returned back from IPS flow with error: %s', __METHOD__, $error_message ) );

			$this->_redirect_with_messages( __( 'Sorry, Easy Setup encountered an error.  Please try again.', 'woocommerce-gateway-paypal-express-checkout' ) );
		}

		// Make sure credentials present in query string.
		foreach ( array( 'api_style', 'api_username', 'api_password', 'signature' ) as $param ) {
			if ( empty( $_GET[ $param ] ) ) {
				wc_gateway_ppec_log( sprintf( '%s: returned back from IPS flow but missing parameter %s', __METHOD__, $param ) );

				$this->_redirect_with_messages( __( 'Sorry, Easy Setup encountered an error.  Please try again.', 'woocommerce-gateway-paypal-express-checkout' ) );
			}
		}

		// TODO: Check for IPS certificate-style.
		$creds = new WC_Gateway_PPEC_Client_Credential_Signature(
			$_GET['api_username'],
			$_GET['api_password'],
			$_GET['signature']
		);

		$error_msgs = array();
		try {
			$payer_id = wc_gateway_ppec()->client->test_api_credentials( $creds, $env );
			if ( ! $payer_id ) {
				$this->_redirect_with_messages( __( 'Easy Setup was able to obtain your API credentials, but was unable to verify that they work correctly.  Please make sure your PayPal account is set up properly and try Easy Setup again.', 'woocommerce-gateway-paypal-express-checkout' ) );
			}
			$creds->set_payer_id( $payer_id );
		} catch( PayPal_API_Exception $ex ) {
			$error_msgs[] = array(
				'warning' => __( 'Easy Setup was able to obtain your API credentials, but an error occurred while trying to verify that they work correctly.  Please try Easy Setup again.', 'woocommerce-gateway-paypal-express-checkout' )
			);
		}

		$is_enabled_for_billing_address = false;
		try {
			$is_enabled_for_billing_address = wc_gateway_ppec()->client->test_for_billing_address_enabled( $creds, $env );
		} catch( PayPal_API_Exception $ex ) {
			$error_msgs[] = array(
				'warning' => __( 'Easy Setup encountered an error while trying to determine which features are enabled on your PayPal account.  You may not see all of the features below that are enabled for your PayPal account.  To try again, click "Save Changes".', 'woocommerce-gateway-paypal-express-checkout' )
			);
		}

		$error_msgs[] = array(
			'success' => __( 'Success!  Your PayPal account has been set up successfully.', 'woocommerce-gateway-paypal-express-checkout' )
		);

		$settings = wc_gateway_ppec()->settings->loadSettings();

		if ( ! $settings->enabled ) {
			$error_msgs[] = array(
				'warning' => __( 'PayPal Express Checkout is not enabled.  To allow your buyers to pay with PayPal, make sure "Enable PayPal Express Checkout" is checked.', 'woocommerce-gateway-paypal-express-checkout' )
			);
		}

		if ( ! empty( $error_msgs ) ) {
			wc_gateway_ppec_log( sprintf( '%s: returned back from IPS flow but with warnings/errors: %s', __METHOD__, print_r( $error_msgs, true ) ) );
		}

		$settings->environment = $env;
		if ( 'live' == $env ) {
			$settings->liveApiCredentials = $creds;
		} else {
			$settings->sandboxApiCredentials = $creds;
		}

		$settings->saveSettings();

		$this->_redirect_with_messages( $error_msgs );
	}
}
