<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PayPal NVP (Name-Value Pair) API client. This client supports both certificate
 * and signature for authentication.
 *
 * @see https://developer.paypal.com/docs/classic/api/#ec
 */
class WC_Gateway_PPEC_Client {

	/**
	 * Client credential.
	 *
	 * @var WC_Gateway_PPEC_Client_Credential
	 */
	protected $_credential;

	/**
	 * PayPal environment. Either 'sandbox' or 'live'.
	 *
	 * @var string
	 */
	protected $_environment;

	const INVALID_CREDENTIAL_ERROR  = 1;
	const INVALID_ENVIRONMENT_ERROR = 2;
	const REQUEST_ERROR             = 3;

	/**
	 * Constructor.
	 *
	 * @param WC_Gateway_PPEC_Client_Credential $credential Client's credential
	 */
	public function __construct( WC_Gateway_PPEC_Client_Credential $credential, $environment = 'live' ) {
		$this->_credential  = $credential;
		$this->_environment = $environment;
	}

	/**
	 * Get PayPal endpoint.
	 *
	 * @see https://developer.paypal.com/docs/classic/api/#ec
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return sprintf(
			'https://%s%s.paypal.com/nvp',

			$this->_credential->get_endpoint_subdomain(),
			'sandbox' === $this->_environment ? '.sandbox' : ''
		);
	}

	/**
	 * Make a remote request to PayPal API.
	 *
	 * @see https://developer.paypal.com/docs/classic/api/NVPAPIOverview/#creating-an-nvp-request
	 *
	 * @param  array $params NVP request parameters
	 * @return array         NVP response
	 */
	protected function _request( array $params ) {
		try {

			// Make sure $_credentials and $_environment have been configured.
			if ( ! $this->_credential ) {
				throw new Exception( __( 'Missing credential', 'woocommerce-gateway-ppec' ), self::INVALID_CREDENTIAL_ERROR );
			}

			if ( ! is_a( $this->_credential, 'WC_Gateway_PPEC_Client_Credential' ) ) {
				throw new Exception( __( 'Invalid credential object', 'woocommerce-gateway-ppec' ), self::INVALID_CREDENTIAL_ERROR );
			}

			if ( ! in_array( $this->_environment, array( 'live', 'sandbox' ) ) ) {
				throw new Exception( __( 'Invalid environment', 'woocommerce-gateway-ppec' ), self::INVALID_ENVIRONMENT_ERROR );
			}

			// First, add in the necessary credential parameters.
			$body = array_merge( $params, $this->_credentials->get_request_params() );
			$args = array(
				'method'      => 'POST',
				'body'        => $body,
				'user-agent'  => __CLASS__,
				'httpversion' => '1.1',
			);

			$resp = wp_safe_remote_post( $this->get_endpoint(), $args );

			if ( is_wp_error( $response ) ) {
				throw new Exception( sprintf( __( 'An error occurred while trying to connect to PayPal: %s', 'woocommerce-gateway-ppec' ), $response->get_error_message() ), self::REQUEST_ERROR );
			}

			parse_str( $resp, $result );

			if ( ! array_key_exists( 'ACK', $result ) ) {
				throw new Exception( __( 'Malformed response received from PayPal', 'woocommerce-gateway-ppec' ), self::REQUEST_ERROR );
			}

			// Let the caller deals with the response.
			return $result;

		} catch ( Exception $e ) {

			// TODO: Logging.

			// TODO: Maybe returns WP_Error ?
			return array(
				'ACK'             => 'Failure',
				'L_ERRORCODE0'    => $e->getCode(),
				'L_SHORTMESSAGE0' => 'Error in ' . __METHOD__,
				'L_LONGMESSAGE0'  => $e->getMessage(),
				'L_SEVERITYCODE0' => 'Error'
			);

		}
	}

	/**
	 * Initiates an Express Checkout transaction.
	 *
	 * @see https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
	 *
	 * @param  array $params NVP params
	 * @return array         NVP response
	 */
	public function set_express_checkout( array $params ) {
		$params['METHOD']  = 'SetExpressCheckout';
		$params['VERSION'] = '120.0';

		return $this->_request( $params );
	}

	/**
	 * Get details from a given token.
	 *
	 * @see https://developer.paypal.com/docs/classic/api/merchant/GetExpressCheckoutDetails_API_Operation_NVP/
	 *
	 * @param  array $params NVP params
	 * @return array         NVP response
	 */
	public function get_express_checkout_details( $token ) {
		$params = array(
			'METHOD'  => 'GetExpressCheckoutDetails',
			'VERSION' => '120.0',
			'TOKEN'   => $token,
		);

		return $this->_request( $params );
	}

	/**
	 * Completes an Express Checkout transaction. If you set up a billing agreement
	 * in your 'SetExpressCheckout' API call, the billing agreement is created
	 * when you call the DoExpressCheckoutPayment API operation.
	 *
	 * @see https://developer.paypal.com/docs/classic/api/merchant/GetExpressCheckoutDetails_API_Operation_NVP/
	 *
	 * @param  array $params NVP params
	 * @return array         NVP response
	 */
	public function do_express_checkout_payment( $params ) {
		$params['METHOD']  = 'DoExpressCheckoutPayment';
		$params['VERSION'] = '120.0';

		return $this->_request( $params );
	}

	/**
	 * Obtain your Pal ID, which is the PayPal–assigned merchant account number,
	 * and other informaton about your account.
	 *
	 * @see https://developer.paypal.com/docs/classic/api/merchant/GetPalDetails_API_Operation_NVP/
	 *
	 * @return array NVP response
	 */
	public function get_pal_details() {
		$params['METHOD']  = 'GetPalDetails';
		$params['VERSION'] = '120.0';

		return $this->_request( $params );
	}

	/**
	 * Issues a refund to the PayPal account holder associated with a transaction.
	 *
	 * @see https://developer.paypal.com/docs/classic/api/merchant/RefundTransaction_API_Operation_NVP/
	 *
	 * @param  array $params NVP params
	 * @return array         NVP response
	 */
	public function refund_transaction( $params ) {
		$params['METHOD']  = 'RefundTransaction';
		$params['VERSION'] = '120.0';

		return $this->_request( $params );
	}

}
