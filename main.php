<?php
/**
 * Plugin Name: Order Categories for WooCommerce
 * Plugin URI: https://brightvessel.com/
 * Description: Ultimate Categories Order for WooCommerce.
 * Version: 1.0
 * Author: Bright Plugins
 * Author URI: https://brightplugins.com
 * Text Domain: catorders
 * Domain Path: /etc/i18n/languages/
 *
 */

defined('ABSPATH') || exit;

// Define WCOC_PLUGIN_DIR.
if (!defined('WCOC_PLUGIN_DIR')) {
    define('WCOC_PLUGIN_DIR', __DIR__);
}

use Woocommerce_Order_Categories\Bootstrap;

/**
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Put your plugin code here

    add_action('woocommerce_loaded', function () {
        require_once WCOC_PLUGIN_DIR . '/vendor/autoload.php';

        $bootstrap = new Bootstrap();
        register_activation_hook(__FILE__, [$bootstrap, 'defaultOptions']);
        
    });
} else {
    add_action('admin_notices', function () {
        $class = 'notice notice-error';
        $message = __('Oops! looks like WooCommerce is disabled. Please, enable it in order to use WooCommerce Order Categories.', 'woocatorders');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    });
}
