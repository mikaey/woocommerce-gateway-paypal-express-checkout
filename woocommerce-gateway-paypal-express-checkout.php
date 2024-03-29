<?php
/**
 * Plugin Name: WooCommerce PayPal Express Checkout Gateway
 * Plugin URI: https://woothemes.com
 * Description: A payment gateway for PayPal Express Checkout ( https://www.paypal.com/us/webapps/mpp/express-checkout ). Requires WC 2.5+
 * Version: 1.0.0
 * Author: Automattic/WooCommerce
 * Author URI: https://woocommerce.com
 * Copyright: © 2016 WooCommerce / PayPal.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-gateway-paypal-express-checkout
 * Domain Path: /languages
 */
/**
 * Copyright (c) 2015 PayPal, Inc.
 *
 * The name of the PayPal may not be used to endorse or promote products derived from this
 * software without specific prior written permission. THIS SOFTWARE IS PROVIDED ``AS IS'' AND
 * WITHOUT ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Return instance of WC_Gateway_PPEC_Plugin.
 *
 * @return WC_Gateway_PPEC_Plugin
 */
function wc_gateway_ppec() {
	static $plugin;

	if ( ! isset( $plugin ) ) {
		require_once( 'includes/class-wc-gateway-ppec-plugin.php' );

		$plugin = new WC_Gateway_PPEC_Plugin( __FILE__, '1.0.0' );
	}

	return $plugin;
}

wc_gateway_ppec()->maybe_run();
