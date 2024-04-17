<?php



if (!class_exists('WMLD_Duplicator_Taxonomies')) {
    class WMLD_Duplicator_Taxonomies
    {
        static function count_taxonomies(string $taxonomy, string $lang = ''): int
        {
            $args = [
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'meta_query' => [
                    [
                        'key' => '_translated',
                        'compare' => 'EXISTS'
                    ]
                ],
                'lang' => $lang
            ];
            $terms = get_terms($args);
            return count($terms);
        }
        static function get_valid_taxonomies($post_type): array {
            $taxonomies = get_object_taxonomies($post_type);
            $valid_taxonomies = [];
            foreach ($taxonomies as $taxonomy) {
                if (!in_array($taxonomy, UNVALIDE_TAXO_TO_TRANSLATE) && strpos($taxonomy, PRODUCT_VARIATION_PREFIX) !== 0) {
                    $valid_taxonomies[] = $taxonomy;
                }
            }
            return $valid_taxonomies;
        }
        static function count_untranslated_taxonomies(string $taxonomy): int
        {
            $args = [
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => '_translated',
                        'compare' => 'NOT EXISTS'
                    )
                )
            ];
            $terms = get_terms($args);
            return count($terms);
        }
        static function get_static(string $taxonomy): array {
            $valid_languages = pll_languages_list();

            $response = [
                'taxonomy' => $taxonomy,
                'valid_languages' => $valid_languages,
                'total_terms' => [
                    'all' => self::count_taxonomies($taxonomy)
                ],
                'untranslated_terms' => [
                    'all' => self::count_untranslated_taxonomies($taxonomy)
                ]
            ];
            foreach ($valid_languages as $lang) {
                $response['total_terms'][$lang] = self::count_taxonomies($taxonomy, $lang);
                $response['untranslated_terms'][$lang] = self::count_untranslated_taxonomies($taxonomy);
            }
            return $response;
        }
    }
}