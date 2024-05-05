<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!defined('duplicate_product_variations')) {
    /**
     * Duplicate product variations from original product to a new product.
     *
     * @param int $original_id The ID of the original product.
     * @param int $new_id The ID of the new product.
     * @return void
     */
    function duplicate_product_variations(int $original_id, int $new_id): void
    {
        $variations = get_posts([
            'post_type' => 'product_variation',
            'post_parent' => $original_id,
            'posts_per_page' => -1
        ]);

        foreach ($variations as $variation) {
            $variation_id = $variation->ID;
            $new_variation_id = wp_insert_post([
                'post_title' => $variation->post_title,
                'post_content' => $variation->post_content,
                'post_type' => 'product_variation',
                'post_parent' => $new_id,
                'post_status' => 'publish'
            ]);

            $variation_meta_keys = ['_price', '_regular_price', '_sale_price', '_stock'];  // Include other meta keys as necessary
            foreach ($variation_meta_keys as $key) {
                $value = get_post_meta($variation_id, $key, true);
                update_post_meta($new_variation_id, $key, $value);
            }
        }
    }
}

if (!defined('duplicate_product_taxonomies')) {
    /**
     * Duplicate product taxonomies from the original product to the new product.
     *
     * @param int $original_id The ID of the original product.
     * @param int $new_id The ID of the new product.
     * @param string $target_language_code The language code for translation (default: WMLD_PRODUCT_TRANSLATION_LANGUAGE).
     * @return void
     */
    function duplicate_product_taxonomies(int $original_id, int $new_id, string $target_language_code)
    {
        $taxonomies = get_object_taxonomies('product', 'names'); // Fetching taxonomies related to the 'product'
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy == 'product_shipping_class' || $taxonomy == 'post_translations' || $taxonomy == 'language' || $taxonomy == 'product_visibility') {
                continue;
            }
            $terms = wp_get_post_terms($original_id, $taxonomy, ['fields' => 'ids']);
            $translated_terms = [];

            foreach ($terms as $term_id) {
                // Attempt to get a translated term ID
                $translated_term_id = pll_get_term($term_id, $target_language_code);

                // Check if the translation exists
                if ($translated_term_id) {
                    $translated_terms[] = $translated_term_id;
                } else {
                    // Optional: Add the original term if no translation exists
                    // Comment out the next line if you don't want to use original terms for missing translations
                    $translated_terms[] = $term_id;
                }
            }

            // Assign translated terms to the new product, only if there are any translated terms
            if (!empty($translated_terms)) {
                wp_set_object_terms($new_id, $translated_terms, $taxonomy);
            }
        }
    }
}

if (!defined('duplicate_product_meta')) {
    /**
     * Duplicate product meta from original product to new product.
     *
     * @param int $original_product_id The ID of the original product.
     * @param int $new_product_id The ID of the new product.
     * @return void
     */
    function duplicate_product_meta(int $original_product_id, int $new_product_id)
    {
        // Duplicate custom product attributes
        $product_attributes = get_post_meta($original_product_id, '_product_attributes', true);
        if (!empty($product_attributes)) {
            update_post_meta($new_product_id, '_product_attributes', $product_attributes);
        }

        // Duplicate global attribute term associations
        $taxonomies = wc_get_attribute_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            $taxonomy_name = 'pa_' . wc_attribute_taxonomy_name($taxonomy->attribute_name);
            if (taxonomy_exists($taxonomy_name)) {
                $terms = wp_get_post_terms($original_product_id, $taxonomy_name, array('fields' => 'ids'));
                if (!is_wp_error($terms) && !empty($terms)) {
                    wp_set_object_terms($new_product_id, $terms, $taxonomy_name);
                }
            }
        }
    }
}

if (!defined('duplicate_product_to_other_languages')) {
    /**
     * Duplicate a product to other languages.
     *
     * This function duplicates a product to the specified target language.
     *
     * @param int $original_id The ID of the original product to duplicate.
     * @param string $target_lang_code The language code of the target language.
     * @return int|bool The ID of the duplicated product if successful, false otherwise.
     */
    function duplicate_product_to_other_languages(int $original_id, string $target_lang_code): int|bool
    {
        $original_product = wc_get_product($original_id);

        // create a new product in the target language
        $new_product_id = wp_insert_post([
            'post_title' => $original_product->get_title(),
            'post_content' => $original_product->get_description(),
            'post_type' => 'product',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
            'post_name' => $original_product->get_slug() . '-' . $target_lang_code,
            'post_excerpt' => $original_product->get_short_description()
        ]);

        // product type 
        update_post_meta($new_product_id, '_visibility', $original_product->get_catalog_visibility());
        update_post_meta($new_product_id, '_stock_status', $original_product->get_stock_status());
        update_post_meta($new_product_id, '_stock', $original_product->get_stock_quantity());
        update_post_meta($new_product_id, '_price', $original_product->get_price());
        update_post_meta($new_product_id, '_regular_price', $original_product->get_regular_price());
        update_post_meta($new_product_id, '_sale_price', $original_product->get_sale_price());
        update_post_meta($new_product_id, '_sku', $original_product->get_sku() ? $original_product->get_sku() . '-en' : '');
        update_post_meta($new_product_id, '_weight', $original_product->get_weight());
        update_post_meta($new_product_id, '_length', $original_product->get_length());
        update_post_meta($new_product_id, '_width', $original_product->get_width());
        update_post_meta($new_product_id, '_height', $original_product->get_height());
        update_post_meta($new_product_id, '_product_attributes', $original_product->get_attributes());
        update_post_meta($new_product_id, '_sale_price_dates_from', $original_product->get_date_on_sale_from());
        update_post_meta($new_product_id, '_sale_price_dates_to', $original_product->get_date_on_sale_to());

        if (is_wp_error($new_product_id)) {
            printf('Error duplicating product %d: %s', $original_id, $new_product_id->get_error_message());
            return false;
        }

        pll_set_post_language($new_product_id, $target_lang_code);
        pll_save_post_translations([
            pll_get_post_language($original_id) => $original_id,
            $target_lang_code => $new_product_id,
        ]);
        return $new_product_id;
    }
}

if (!defined('reassign_product_media')) {
    /**
     * Reassigns product media from the original product to a new product.
     *
     * @param int $original_product_id The ID of the original product.
     * @param int $new_product_id The ID of the new product.
     * @return void
     */
    function reassign_product_media(int $original_product_id, int $new_product_id)
    {
        // Get all attached media from the original product
        $media = get_attached_media('image', $original_product_id);
        foreach ($media as $file) {
            // Simply update the 'post_parent' to reassign the media to the new product
            $mediaUpdated = wp_update_post(
                array(
                    'ID' => $file->ID,
                    'post_parent' => $new_product_id
                )
            );
            if (is_wp_error($mediaUpdated)) {
                printf('Error reassigning media %d to product %d: %s', $file->ID, $new_product_id, $mediaUpdated->get_error_message());
            } else {
                printf('Media %d reassigned to product %d', $file->ID, $new_product_id);
            }
        }
        // Optional: Copy the featured image to the new product
        $featured_image_id = get_post_thumbnail_id($original_product_id);
        if ($featured_image_id) {
            set_post_thumbnail($new_product_id, $featured_image_id);
        }
    }
}