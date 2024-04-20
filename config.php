<?php 


if(!defined('ABSPATH')) {
    die('Direct access is not allowed');
}

if (!defined('WMLD_VERSION')) {
    define('WMLD_VERSION', '1.0');
}

if (!defined('WMLD_PLUGIN_DIR')) {
    define('WMLD_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('WMLD_PLUGIN_URL')) {
    define('WMLD_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('UNVALIDE_TAXO_TO_TRANSLATE')) {
    define('UNVALIDE_TAXO_TO_TRANSLATE', ['language', 'post_translations', 'nav_menu', 'link_category', 'post_format', 'product_type', 'product_visibility', 'product_shipping_class']); 
}

if (!defined('PRODUCT_VARIATION_PREFIX')) {
    define('PRODUCT_VARIATION_PREFIX', 'pa_');
}

if (!defined('UNVALIDE_POSTS_TO_TRANSLATE')) {
    define('UNVALIDE_POSTS_TO_TRANSLATE', ['e-landing-page']);
}