
<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<?php
$setting = array_merge([
], $settings);

$taxonomy = $setting['taxonomy'];

// TODO:: Manage the setting of the plugin to got the post types and taxonomies to translate
$french_language_code = 'fr';
$english_language_code = 'en';

$args = [
    'taxonomy'   => $taxonomy,
    'hide_empty' => false,
    'lang'       => $french_language_code, // Make sure to fetch terms in French
];

$terms = get_terms($args);
if (is_wp_error($terms) || empty($terms)) {
    echo '<p>' . esc_html__('No terms found or invalid taxonomy.', 'woocommerce-multilanguage-duplicator') . '</p>';
    return;
}

?>
<div class='widefat fixed'  >
    <>
        <?php 
        foreach ($terms as $term) {
            // Get the term in French
            $french_term = $term->name;
            // Get the term in English
            $english_term = pll_translate_string($french_term, $english_language_code);
            ?>
            <div>
                <div>
                    <?= esc_html($french_term); ?>
                </div>
                <div>
                    <?= esc_html($english_term); ?>
                </div>
            </div>
        <?php 
        }
        ?>
    </div>
</div>
