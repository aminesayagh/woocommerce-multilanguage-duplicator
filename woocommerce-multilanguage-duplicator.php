<?php
/*
Plugin Name: WooCommerce Multilanguage Duplicator
Plugin URI: http://example.com/woocommerce-multilanguage-duplicator
Description: Automates the duplication of WooCommerce products, categories, tags, and custom taxonomies for multilingual support.
Version: 1.0
Author: Your Name
Author URI: http://example.com
Text Domain: woocommerce-multilanguage-duplicator
Domain Path: /languages
*/

// Security measure
if (!defined('ABSPATH')) {
    die('Direct access is not allowed');
}

require_once plugin_dir_path(__FILE__) . 'config.php';
require_once WMLD_PLUGIN_DIR . 'includes/class-notification.php';
require_once WMLD_PLUGIN_DIR . 'includes/class-wmld-duplicator-posts.php';
require_once WMLD_PLUGIN_DIR . 'includes/class-wmld-duplicator-taxonomies.php';
require_once WMLD_PLUGIN_DIR . 'includes/class-wmld-duplicator-products.php';
require_once WMLD_PLUGIN_DIR . 'includes/class-wmld-duplicator.php';

// Initialize the plugin
function wmld_initialize_plugin() {
    load_plugin_textdomain('woocommerce-multilanguage-duplicator', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $wmld_duplicator = \WooCommerceMLDuplicator\WMLD_Duplicator::get_instance();
    add_action( 'admin_init', array( $wmld_duplicator, 'register_settings' ) );
}

add_action('plugins_loaded', 'wmld_initialize_plugin');

function wmld_activate_plugin() {
    // Actions to perform on plugin activation, e.g., setting up initial settings
}
register_activation_hook(__FILE__, 'wmld_activate_plugin');

function wmld_deactivate_plugin() {
    // Cleanup on plugin deactivation
}
register_deactivation_hook(__FILE__, 'wmld_deactivate_plugin');

// include select2 library for the admin page
function enqueue_select2_jquery() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue Select2 CSS
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');

    // Enqueue Select2 JavaScript
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), null, true);

    // Initialize Select2
    wp_add_inline_script('select2-js', 'jQuery(document).ready(function($) { $(".select2").select2(); });');
}

add_action('admin_enqueue_scripts', 'enqueue_select2_jquery');

function wmld_enqueue_admin_scripts() {
    wp_enqueue_script('wmld-admin-js', plugin_dir_url(__FILE__) . 'js/admin-scripts.js', array('jquery'), null, true);
    wp_localize_script('wmld-admin-js', 'wmldAdminParams', array(
        'clipboardNonce' => wp_create_nonce('wmld_nonce'),
    ));
}

add_action('admin_enqueue_scripts', 'wmld_enqueue_admin_scripts');

function wmld_admin_styles() {
    wp_enqueue_style('dashicons');
    // Optionally enqueue FontAwesome or custom styles
}

add_action('admin_enqueue_scripts', 'wmld_admin_styles');

require_once WMLD_PLUGIN_DIR . 'helpers/columns.php';
require_once WMLD_PLUGIN_DIR . 'helpers/auto_translate.php';