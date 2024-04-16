<?php


namespace WooCommerceMLDuplicator;

require_once WMLD_PLUGIN_DIR . 'includes/class-notification.php';
define('UNVALIDE_TAXO_TO_TRANSLATE', ['language', 'post_translations', 'nav_menu', 'link_category', 'post_format', 'product_type', 'product_visibility', 'product_shipping_class']); 
define('PRODUCT_VARIATION_PREFIX', 'pa_');

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
                'valid_taxonomies' => self::get_valid_taxonomies($post_type),
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
    }
}

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

if (!class_exists('WMLD_Duplicator')) {
    class WMLD_Duplicator
    {
        /**
         * Holds the class instance.
         *
         * @var WMLD_Duplicator
         */
        private static $instance = null;
        /**
         * Constructor.
         */
        private function __construct()
        {
            // Add the admin menu
            if ($this->check_dependencies()) {
                add_action('admin_menu', array($this, 'initialize'));
            }
        }
        /**
         * Prevent cloning of the class instance.
         */
        private function __clone()
        {
        }

        /**
         * Prevent unserializing of the class instance.
         */
        public function __wakeup()
        {
        }
        /**
         * Returns the class instance.
         *
         * @return WMLD_Duplicator
         */
        private function check_dependencies(): bool
        {
            if (!function_exists('pll_languages_list')) {
                add_action('admin_notices', \Notification::error(__('Polylang is not active', 'woocommerce-multilanguage-duplicator')));
                return false;
            }
            // check if the rest api is enabled
            if (!function_exists('rest_url')) {
                add_action('admin_notices', \Notification::error(__('The REST API is not enabled', 'woocommerce-multilanguage-duplicator')));
                return false;
            }
            return true;
        }
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Initialize the plugin.
         */
        public function initialize()
        {
            if (!function_exists('pll_languages_list')) {
                add_action('admin_notices', \Notification::error(__('Polylang is not active', 'woocommerce-multilanguage-duplicator')));
                return;
            }
            add_menu_page(
                __('WMLD', 'woocommerce-multilanguage-duplicator'), // Page title 
                __('WMLD', 'woocommerce-multilanguage-duplicator'), // Page title 
                'manage_options', // Capability
                'woocommerce-multilanguage-duplicator', // Menu slug
                array($this, 'render_admin_page'), // Function to display page content
                'dashicons-translation', // Icon URL
                6
            );
        }
        /**
         * Render the admin page.
         */
        public function render_admin_page()
        {
            $settings = get_option('wmld_settings');
            $settings['valid_languages'] = pll_languages_list();
            $settings['default_language'] = pll_default_language();
            $settings['current_language'] = pll_current_language();

            // get a list of custom post types names from the database
            $custom_post_types = get_post_types(['public' => true, '_builtin' => false]);
            echo '<pre>';
            print_r($custom_post_types);
            echo '</pre>';

            // TODO:: Manage the setting of the plugin to got the post types and taxonomies to translate
            $settings['commercent'] = WMLD_Duplicator_Posts::get_static('commercent');
            
            echo '<pre>'; 
            print_r($settings);
            echo '</pre>';
            $settings['posts'] = WMLD_Duplicator_Posts::get_static('post');
            $settings['products'] = WMLD_Duplicator_Products::get_static_products();
            $valid_taxonomies = [...$settings['posts']['valid_taxonomies'], ...$settings['products']['valid_taxonomies']];
            foreach ($valid_taxonomies as $taxonomy) {
                $settings['taxonomies'][$taxonomy] = WMLD_Duplicator_Taxonomies::get_static($taxonomy);
            }
            // echo '<pre>'; 
            // print_r($settings);
            // echo '</pre>';

        }
        /**
         * Register the plugin settings.
         */
        public function register_settings()
        {
            register_setting('wmld_settings', 'wmld_settings', array($this, 'validate_settings'));
            add_settings_section('wmld_settings_section', __('Settings', 'woocommerce-multilanguage-duplicator'), array($this, 'settings_section_callback'), 'wmld_settings');
            add_settings_field('wmld_settings_field', __('Enable duplication', 'woocommerce-multilanguage-duplicator'), array($this, 'settings_field_callback'), 'wmld_settings', 'wmld_settings_section');
        }
        /**
         * Validate the plugin settings.
         *
         * @param array $input The input settings.
         * @return array The validated settings.
         */
        public function validate_settings($input)
        {
            return $input;
        }
        /**
         * Callback for the settings section.
         */
        public function settings_section_callback()
        {
            echo '<p>' . __('Enable or disable the duplication of WooCommerce products, categories, tags, and custom taxonomies for multilingual support.', 'woocommerce-multilanguage-duplicator') . '</p>';
        }
        /**
         * Callback for the settings field.
         */
        public function settings_field_callback()
        {
            $options = get_option('wmld_settings');
            echo '<input type="checkbox" id="wmld_settings_field" name="wmld_settings[enable_duplication]" value="1" ' . checked(1, $options['enable_duplication'], false) . ' />';
        }
    }
}