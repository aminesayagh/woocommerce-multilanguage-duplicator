<?php
require_once WMLD_PLUGIN_DIR . 'includes/class-wmld-duplicator-posts.php';


if (!class_exists('WMLD_Duplicator_Products')) {
    class WMLD_Duplicator_Products extends WMLD_Duplicator_Posts
    {
        static function count_products(string $lang = ''): int
        {
            return parent::count_post('product', $lang);
        }
        static function count_untranslated_products(): int
        {
            return parent::count_untranslated_posts('product');
        }
        static function get_static_products(): array {
            $response = parent::get_static('product');
            $response['total_products'] = [
                'all' => $response['total_posts']['all']
            ];
            $response['untranslated_products'] = [
                'all' => $response['untranslated_posts']['all']
            ];
            foreach ($response['valid_languages'] as $lang) {
                $response['total_products'][$lang] = self::count_products($lang);
                $response['untranslated_products'][$lang] = self::count_untranslated_products();
            }
            return $response;
        }
    }
}