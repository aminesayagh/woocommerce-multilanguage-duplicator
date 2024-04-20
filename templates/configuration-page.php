<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


$setting = array_merge([
], $settings);

$post_Types = $setting['post_types'];
$post_types_selected = get_option('wmld_settings')['post_types_selected'] ?? [];

$taxonomies = $setting['taxonomies'];
$taxonomies_selected = get_option('wmld_settings')['taxonomies_selected'] ?? [];

?>

<div class="wrap">
    <h1><?= esc_html__('Translation Configuration', 'woocommerce-multilanguage-duplicator'); ?></h1>
    <form method="post" action="options.php">
        <?php 
            settings_fields('wmld_translation_options'); // Outputs nonce, action, and option_page fields for a settings page.
            do_settings_sections('wmld_translation_options'); // Prints out all settings sections added to a particular settings page.
        ?>
        <h2><?= esc_html__('Select the post types to translate', 'woocommerce-multilanguage-duplicator'); ?></h2>
        <div id='post-types-container'>
            <?php foreach ($post_Types as $post_type) : ?>
                <label>
                    <input type="checkbox" name="wmld_settings[post_types_selected][]" value="<?= $post_type; ?>" <?= in_array($post_type, $post_types_selected) ? 'checked' : ''; ?>>
                    <?= $post_type; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <h2><?= esc_html__('Select taxonomies to translate', 'woocommerce-multilanguage-duplicator'); ?></h2>
        <div id='taxonomies-container'>
            <?php foreach ($taxonomies as $taxonomy) : ?>
                <?php foreach ($taxonomy as $t): ?>
                    <label>
                        <input type="checkbox" name="wmld_settings[taxonomies_selected][]" value="<?= $t; ?>" <?= in_array($t, $taxonomies_selected) ? 'checked' : ''; ?>>
                        <?= $t; ?>
                    </label>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>
