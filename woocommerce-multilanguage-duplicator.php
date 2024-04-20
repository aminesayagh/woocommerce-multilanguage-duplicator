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


function add_custom_category_column($columns) {
    // Adds a new column to the end of the table
    // 'custom_column' is the identifier for the column
    // 'Custom Column' is the header text
    $columns['Equivalent'] = __('Equivalent', 'woocommerce-multilanguage-duplicator');
    return $columns;
}

add_filter('manage_edit-product_cat_columns', 'add_custom_category_column');

function custom_category_column_content($deprecated, $column_name, $term_id) {
    if ($column_name === 'Equivalent') {
        // Get the current language of the term
        $current_language = pll_get_term_language($term_id);
        
        // Suppose we want to display the translation based on the term's current language
        // For example, if the current term is in French, we want to show the English translation
        $target_language = ($current_language == 'fr') ? 'en' : 'fr';  // Default to French if current is English and vice versa

        $translated_term_id = pll_get_term($term_id, $target_language);
        if ($translated_term_id) {
            $translated_term = get_term($translated_term_id);
            $translated_link = get_term_link($translated_term_id);
            if (!is_wp_error($translated_term) && $translated_term) {
                echo '<a href="#" class="copy-icon" data-copytext="' . esc_attr($translated_term->name) . '" title="Copy Name"><i class="dashicons dashicons-admin-page"></i></a>';
                echo '<a href="#" class="copy-link" data-link="' . esc_url($translated_link) . '" title="Copy Link"><i class="dashicons dashicons-admin-links"></i></a>';
            } else {
                echo __('No translation found', 'your-text-domain');
            }
        } else {
            echo __('No translation found', 'your-text-domain');
        }
    }
}

add_filter('manage_product_cat_custom_column', 'custom_category_column_content', 10, 3);

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


function add_commercent_columns($columns) {
    $columns['translation_column'] = __('Translation & Link', 'your-text-domain');
    return $columns;
}

add_filter('manage_commercent_posts_columns', 'add_commercent_columns');

function commercent_custom_column_content($column, $post_id) {
    if ($column === 'translation_column') {
        $english_post_id = pll_get_post($post_id, 'en');  // Get the English version of the post
        if ($english_post_id) {
            $english_post = get_post($english_post_id);
            $post_link = get_permalink($english_post_id);

            echo $english_post ? esc_html($english_post->post_title) : __('No translation found', 'your-text-domain');

            // Icons with data attributes
            echo '<a href="#" class="copy-icon" data-copytext="' . esc_attr($english_post->post_title) . '" title="Copy Name"><i class="dashicons dashicons-admin-page"></i></a>';
            echo '<a href="#" class="copy-link" data-link="' . esc_url($post_link) . '" title="Copy Link"><i class="dashicons dashicons-admin-links"></i></a>';
        } else {
            echo __('No English version available', 'your-text-domain');
        }
    }
}

add_action('manage_commercent_posts_custom_column', 'commercent_custom_column_content', 10, 2);



add_action('admin_post_submit_translation', 'handle_product_translation_submission');

function handle_product_translation_submission() {
    if (!current_user_can('edit_products') || !isset($_POST['translate_nonce']) || !wp_verify_nonce($_POST['translate_nonce'], 'translate_product_nonce')) {
        wp_die('You do not have sufficient permissions or the security check failed.');
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $title = isset($_POST['translated_title']) ? sanitize_text_field($_POST['translated_title']) : '';
    $description = isset($_POST['translated_description']) ? sanitize_textarea_field($_POST['translated_description']) : '';
    $language_code = 'en';  // The language code for English translations

    if (!$product_id || !$title) {
        wp_die('Missing required fields.');
    }

    // Check if a translation already exists
    $translated_id = pll_get_post($product_id, $language_code);
    if(!$translated_id) {
        // Create a new post for the translation
        $translated_id = wp_insert_post(array(
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'publish',
            'post_type' => 'product',
            'post_parent' => $product_id,
        ));

        // Update the language for the translation
        pll_set_post_language($translated_id, $language_code);
        pll_save_post_translations([
            'fr' => $product_id,
            'en' => $translated_id,
        ]);
    } else {
        // Update the existing translation
        wp_update_post(array(
            'ID' => $translated_id,
            'post_title' => $title,
            'post_content' => $description,
        ));
    }

    $taxonomies = get_object_taxonomies('product');
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_post_terms($product_id, $taxonomy);
        $term_ids = array_map(function ($term) use ($language_code) {
            return pll_get_term($term->term_id, $language_code);
        }, $terms);
        wp_set_post_terms($translated_id, $term_ids, $taxonomy);
    }

    

    wp_redirect(admin_url('edit.php?post_type=product&translation_success=' . $translated_id));
    exit;
}