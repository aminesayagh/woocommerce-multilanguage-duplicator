<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<?php

$setting = array_merge([
], $settings);

$taxonomies = $setting['taxonomies'];

?>

<div class="wrap">
    <h1><?= esc_html__('Translation Taxonomy', 'woocommerce-multilanguage-duplicator'); ?></h1>
    <h2><?= esc_html__('Select taxonomies to translate', 'woocommerce-multilanguage-duplicator'); ?></h2>
    <? foreach ($taxonomies as $taxonomy): ?>
        <div id='taxonomies-container'>
            <h3>
                taxonomy: <?= $taxonomy; ?>
            </h3>
            <?php
                $settings['taxonomy'] = $taxonomy;
                require_once WMLD_PLUGIN_DIR . 'templates/taxonomy-page.php';
            ?>
        </div>
    <?php endforeach; ?>
</div>