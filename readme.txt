=== WooCommerce PayPal Express Checkout Payment Gateway ===
Contributors: automattic, woothemes, akeda, dwainm, royho, allendav, slash1andy, woosteve, spraveenitpro, mikedmoore, fernashes, shellbeezy
Tags: ecommerce, e-commerce, commerce, woothemes, wordpress ecommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, paypal
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Accept PayPal, Credit Cards and Debit Cards on your WooCommerce store.

== Description ==

This is a PayPal Express Payment Gateway for WooCommerce.

PayPal Express allows you to securely sell your products and subscriptions online using In-Context Checkout to help you meet security requirements without causing your theme to suffer.  In-Context Checkout uses a modal iFrame, hosted on PayPal's servers, that overlays the checkout form and provides a secure means for your customers to enter their account information.

Also, with Integrated PayPal Setup (Easy Setup), connecting to PayPal is as simple as clicking a button - no complicated API keys to cut and paste.

== Installation ==

= Minimum Requirements =

* WordPress 4.4 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "WooCommerce PayPal Express Checkout" and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The
WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

If on the off-chance you do encounter issues with the shop/category pages after an update you simply need to flush the permalinks by going to WordPress > Settings > Permalinks and hitting 'save'. That should return things to normal.

== Frequently Asked Questions ==

= Does this plugin work with credit cards or just PayPal? =

This plugin supports payments using both credit and debit cards as well as PayPal.

= Does this support Checkout with PayPal from the cart view? =

Yes!

= Does this support both production mode and sandbox mode for testing? =

Yes it does - production and sandbox mode is driven by how you connect.  You may choose to connect in either mode, and disconnect and reconnect in the other mode whenever you want.

= Where can I find documentation? =

For help setting up and configuring, please refer to our [user guide](http://docs.woothemes.com/document/woocommerce-gateway-paypal-express-checkout/)

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the Plugin Forum.

= Will this plugin work with my theme? =

Yes, this plugin will work with any theme, but may require some styling to make it match nicely. Please see
our [codex](http://docs.woothemes.com/documentation/plugins/woocommerce/woocommerce-codex/) for help. If you're
looking for a theme with built in WooCommerce integration we recommend [Storefront](http://www.woothemes.com/storefront/).

= Where can I request new features or report bugs? =

New feature requests and bugs reports can be made in the plugin forum.

== Screenshots ==

1. Click the "Click Here to Set Up Your PayPal Account" button. If you want to test before goes live, you can switch the Environment, above the button, to Sandbox.
2. API credentials will be set after Easy Setup. Or, you can set that manually.
3. Checkout with PayPal directly from the Cart.

== Changelog ==

= 1.0.0 =
* Initial stable release

= 0.2.0 =
* Fix - Add cancel link on checkout page when session for PPEC is active
* Fix - In-context mini browser keeps spinning because failure xhr response is not handled properly

= 0.1.0 =
* Beta release
