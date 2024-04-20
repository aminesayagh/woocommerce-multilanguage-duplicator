<?php 


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