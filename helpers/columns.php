<?php

// Custom column content for the 'custom_column' column, in the 'product_cat' taxonomy
/**
 * Adds a custom category column to the table.
 *
 * @param array $columns The existing columns in the table.
 * @return array The updated columns array with the new custom column added.
 */
function add_custom_category_column($columns) {
    // 'Equivalent' is the identifier for the column
    $columns['Equivalent'] = __('Equivalent', 'woocommerce-multilanguage-duplicator');
    return $columns;
}

// add_filter('manage_edit-product_cat_columns', 'add_custom_category_column');

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

// add_filter('manage_product_cat_custom_column', 'custom_category_column_content', 10, 3);

// Custom column content for the 'translation_column' column, in the 'commercent' post type
function add_commercent_columns($columns) {
    $columns['translation_column'] = __('Translation & Link', 'your-text-domain');
    return $columns;
}

// add_filter('manage_commercent_posts_columns', 'add_commercent_columns');
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

// add_action('manage_commercent_posts_custom_column', 'commercent_custom_column_content', 10, 2);