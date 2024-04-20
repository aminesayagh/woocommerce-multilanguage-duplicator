<?php


namespace WooCommerceMLDuplicator;





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
                
                add_action('admin_notices', function () {
                    return \Notification::error(__('Polylang is not active', 'woocommerce-multilanguage-duplicator'));
                });
                return false;
            }
            // check if the rest api is enabled
            if (!function_exists('rest_url')) {
                add_action('admin_notices', function () {
                    return \Notification::error(__('The REST API is not enabled', 'woocommerce-multilanguage-duplicator'));
                });
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
                add_action('admin_notices', function () {
                    return \Notification::error(__('Polylang is not active', 'woocommerce-multilanguage-duplicator'));
                });
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

            add_submenu_page(
                'woocommerce-multilanguage-duplicator',
                __('Translation Configuration', 'woocommerce-multilanguage-duplicator'),
                __('Configuration', 'woocommerce-multilanguage-duplicator'),
                'manage_options',
                'wmld-configuration',
                array($this, 'render_configuration_page')
            );

            add_submenu_page(
                'woocommerce-multilanguage-duplicator',
                __('Translation Taxonomy', 'woocommerce-multilanguage-duplicator'),
                __('Taxonomy', 'woocommerce-multilanguage-duplicator'),
                'manage_options',
                'wmld-taxonomy',
                array($this, 'render_taxonomy_page')
            );

            // add_submenu_page(
            //     'woocommerce-multilanguage-duplicator',
            //     __('Translation Menu', 'woocommerce-multilanguage-duplicator'),
            //     __('Menu', 'woocommerce-multilanguage-duplicator'),
            //     'manage_options',
            //     'wmld-menu',
            //     array($this, 'render_menu_page')
            // );

            // add submenu page for product
            add_submenu_page(
                'woocommerce-multilanguage-duplicator',
                __('Translation Product', 'woocommerce-multilanguage-duplicator'),
                __('Product', 'woocommerce-multilanguage-duplicator'),
                'manage_options',
                'wmld-product',
                array($this, 'render_product_page')
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
            
            $settings['posts'] = WMLD_Duplicator_Posts::get_static('post');
            $settings['products'] = WMLD_Duplicator_Products::get_static_products();
            $valid_taxonomies = [...$settings['posts']['valid_taxonomies'], ...$settings['products']['valid_taxonomies']];
            foreach ($valid_taxonomies as $taxonomy) {
                $settings['taxonomies'][$taxonomy] = WMLD_Duplicator_Taxonomies::get_static($taxonomy);
            }
            
            require_once WMLD_PLUGIN_DIR . 'templates/admin-page.php';
        }
        public function render_configuration_page() {
            $settings['post_types'] = WMLD_Duplicator_Posts::get_valid_post_types(['public' => true, '_builtin' => false]);
            foreach ($settings['post_types'] as $post_type) {
                $settings['taxonomies'][$post_type] = WMLD_Duplicator_Posts::get_valid_taxonomies($post_type);
            }

            require_once WMLD_PLUGIN_DIR . 'templates/configuration-page.php';
        }
        public function render_taxonomy_page() {
            $settings['taxonomies'] = WMLD_Duplicator_Taxonomies::get_valid_taxonomies('product');
            require_once WMLD_PLUGIN_DIR . 'templates/taxonomies-page.php';
        }
        public function render_menu_page() {
            require_once WMLD_PLUGIN_DIR . 'templates/menu-page.php';
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
        public function render_product_page() {
            $settings['products'] = WMLD_Duplicator_Products::get_static_products();
            require_once WMLD_PLUGIN_DIR . 'templates/product-page.php';
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