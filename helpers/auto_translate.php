<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

require_once WMLD_PLUGIN_DIR . 'helpers/duplication/product.php';

function duplicate_product_to_other_languages_on_save($post_id)
{   
    if (wp_is_post_revision($post_id)) {
        return;
    }
    // Check if the post is a product
    if (get_post_type($post_id) !== 'product') {
        return;
    }
    

    $product_id = $post_id;
    $product_lang = pll_get_post_language($product_id); // 
    $other_langs = pll_languages_list();
    foreach ($other_langs as $key => $lang) {
        if ($lang == $product_lang) {
            unset($other_langs[$key]);
        }
    }
    
    foreach ($other_langs as $lang) {
        // send a log to the console
        error_log('Duplicating product ' . $product_id . ' to ' . $lang);
        $has_translation = pll_get_post($product_id, $lang);
        if ($has_translation) {
            continue;
        }
        $new_product = duplicate_product_to_other_languages($product_id, $lang);
        if ($new_product) {
            reassign_product_media($product_id, $new_product);
            duplicate_product_meta($product_id, $new_product);
            duplicate_product_taxonomies($product_id, $new_product, $lang);
            duplicate_product_variations($product_id, $new_product);
        }
    }
    return;
}

