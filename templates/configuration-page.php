<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


$setting = array_merge([
], $settings);

?>

<div class="wrap">
    <h1><?= esc_html__('Translation Configuration', 'woocommerce-multilanguage-duplicator'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('wmld_translation_options');
        do_settings_sections('wmld_translation_options');

        // Check if options already saved
        $selected_post_types = get_option('wmld_selected_post_types', []);
        ?>

        <h2><?= esc_html__('Select Post Types to Translate', 'woocommerce-multilanguage-duplicator'); ?></h2>
        <p><?= esc_html__('Check the post types that should be included in the translation process.', 'woocommerce-multilanguage-duplicator'); ?></p>

        <?php foreach ($post_types as $post_type): ?>
            <input type="checkbox" 
                   id="wmld_post_type_<?= esc_attr($post_type->name); ?>" 
                   name="wmld_selected_post_types[]" 
                   value="<?= esc_attr($post_type->name); ?>"
                   <?= in_array($post_type->name, $selected_post_types) ? 'checked' : ''; ?> />
            <label for="wmld_post_type_<?= esc_attr($post_type->name); ?>"><?= esc_html($post_type->label); ?></label><br />
        <?php endforeach; ?>

        <?php submit_button(); ?>
    </form>
</div>