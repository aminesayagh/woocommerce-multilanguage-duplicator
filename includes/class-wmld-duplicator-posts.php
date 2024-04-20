<?php
namespace WooCommerceMLDuplicator;

if (!class_exists('WMLD_Duplicator_Posts')) {
    class WMLD_Duplicator_Posts
    {
        static function count_post(string $post_type = 'post', string $lang = ''): int
        {
            $args = array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'lang' => $lang
            );
            $posts = new \WP_Query($args);
            return $posts->found_posts;
        }
        static function count_untranslated_posts(string $post_type = 'post'): int
        {
            $args = array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_translated',
                        'compare' => 'NOT EXISTS'
                    )
                )
            );
            $posts = new \WP_Query($args);
            return $posts->found_posts;
        }
        static function get_valid_taxonomies(string $post_type): array {
            $taxonomies = get_object_taxonomies($post_type);
            $valid_taxonomies = [];
            foreach ($taxonomies as $taxonomy) {
                if (!in_array($taxonomy, UNVALIDE_TAXO_TO_TRANSLATE) && strpos($taxonomy, PRODUCT_VARIATION_PREFIX) !== 0) {
                    $valid_taxonomies[] = $taxonomy;
                }
            }
            return $valid_taxonomies;
        }
        static function get_static($post_type): array {
            $valid_languages = pll_languages_list();

            $response = [
                'post_type' => $post_type,
                'valid_languages' => $valid_languages,
                'valid_taxonomies' => WMLD_Duplicator_Taxonomies::get_valid_taxonomies($post_type),
                'total_posts' => [
                    'all' => self::count_post($post_type)
                ],
                'untranslated_posts' => [  
                    'all' => self::count_untranslated_posts($post_type)
                ],
            ];

            foreach ($valid_languages as $lang) {
                $response['total_posts'][$lang] = self::count_post($post_type, $lang);
                $response['untranslated_posts'][$lang] = self::count_untranslated_posts($post_type);
            }
            return $response;
        }
        static function get_valid_post_types(array $args) {
            $post_types = get_post_types($args);
            $valid_post_types = [];
            foreach ($post_types as $post_type) {
                if (!in_array($post_type, UNVALIDE_POSTS_TO_TRANSLATE)) {
                    $valid_post_types[] = $post_type;
                }
            }
            return $valid_post_types;
        }
    }
}