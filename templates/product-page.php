<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$args = [
    'post_type'      => 'product',
    'posts_per_page' => -1, // You might want to limit this or paginate in production
    'meta_query'     => [
        [
            'key'     => 'pll_languages', // A custom field to check if translated, this key might differ based on your setup
            'compare' => 'NOT EXISTS'    // Assumes you flag translated products somehow; adjust based on your setup
        ]
    ]
];

$query = new WP_Query($args);

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $product_id = get_the_ID();
        $product    = wc_get_product($product_id);
        require WMLD_PLUGIN_DIR . 'templates/product-item.php';
    }
    wp_reset_postdata();
} else {
    echo '<p>' . esc_html__('No products found', 'woocommerce-multilanguage-duplicator') . '</p>';
}