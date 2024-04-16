<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$setting = array_merge([
], $settings);


?>

<div class="wrap">
    <h1><?= esc_html__('WooCommerce Multilanguage Duplicator', 'woocommerce-multilanguage-duplicator') ?></h1>
    <p>
        <?= esc_html__('Enable or disable the duplication of WooCommerce products, categories, tags, and custom taxonomies for multilingual support.', 'woocommerce-multilanguage-duplicator') ?>
    </p>

    <form id="wmld-select-taxonomy-form" method="post">
        <h2><?= esc_html__('Select a Taxonomy to View Details', 'woocommerce-multilanguage-duplicator'); ?></h2>
        <?php foreach(get_taxonomies() as $id => $taxonomy): ?>
            <?php 
            // get all terms for the taxonomy
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ]);

            echo '<pre>';
            print_r([$id => $terms]);
            echo '</pre>';
            ?>
        <?php endforeach; ?>
        <div>
            <label for="taxonomy_terms"><?= esc_html__('Taxonomy:', 'woocommerce-multilanguage-duplicator'); ?></label>
            <select class="select2" name="taxonomy_terms" style="width: 50%;">
                <?php
                $terms = get_taxonomies();
                foreach ($terms as $id => $term) {
                    echo '<option value="' . esc_attr($id) . '">' . esc_html($term) . '</option>';
                }
                ?>
            </select>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('.select2').select2();
                    $('.select2').on('change', function() {
                        let taxonomy = $(this).val();
                        fetch('<?= esc_url(rest_url('wp/v2/taxonomies/')); ?>' + taxonomy).then(response => response.json()).then(terms => {
                        }).catch(error => {
                            console.error('Error:', error);
                        });
                    })
                });
            </script>
        </div>
    </form>

    <!-- /worpdress divider  -->


    <h2><?= esc_html__('Language Settings', 'woocommerce-multilanguage-duplicator'); ?></h2>
    <ul>
        <li><strong><?= esc_html__('Default Language:', 'woocommerce-multilanguage-duplicator'); ?></strong>
            <?= esc_html($setting['default_language']); ?></li>
        <li><strong><?= esc_html__('Current Language:', 'woocommerce-multilanguage-duplicator'); ?></strong>
            <?= esc_html($setting['current_language']); ?></li>
        <li><strong><?= esc_html__('Available Languages:', 'woocommerce-multilanguage-duplicator'); ?></strong>
            <?= implode(', ', array_map('esc_html', $setting['valid_languages'])); ?></li>
    </ul>

    <h2><?= esc_html__('Translation Status', 'woocommerce-multilanguage-duplicator'); ?></h2>
    <?php foreach ($settings['valid_languages'] as $lang): ?>
        <h3><?= esc_html__('Language:', 'woocommerce-multilanguage-duplicator') . ' ' . esc_html($lang); ?></h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?= esc_html__('Type', 'woocommerce-multilanguage-duplicator'); ?></th>
                    <th><?= esc_html__('Total', 'woocommerce-multilanguage-duplicator'); ?></th>
                    <th><?= esc_html__('Untranslated', 'woocommerce-multilanguage-duplicator'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= esc_html__('Posts', 'woocommerce-multilanguage-duplicator'); ?></td>
                    <td><?= esc_html($setting['total_posts'][$lang]); ?></td>
                    <td><?= esc_html($setting['untranslated_posts']); ?></td>
                </tr>
                <tr>
                    <td><?= esc_html__('Products', 'woocommerce-multilanguage-duplicator'); ?></td>
                    <td><?= esc_html($setting['total_products'][$lang]); ?></td>
                    <td><?= esc_html($setting['untranslated_products']); ?></td>
                </tr>
                <tr>
                    <td><?= esc_html__('Product Categories', 'woocommerce-multilanguage-duplicator'); ?></td>
                    <td><?= esc_html($setting['total_products_categories'][$lang]); ?></td>
                    <td><?= esc_html($setting['untranslated_categories']); ?></td>
                </tr>
                <tr>
                    <td><?= esc_html__('Product Categories', 'woocommerce-multilanguage-duplicator'); ?></td>
                    <td><?= esc_html($setting['total_products_categories'][$lang]); ?></td>
                    <td><?= esc_html($setting['untranslated_categories']); ?></td>
                </tr>
                <tr>
                    <td><?= esc_html__('Product Tags', 'woocommerce-multilanguage-duplicator'); ?></td>
                    <td><?= esc_html($setting['product_tag'][$lang]); ?></td>
                    <td><?= esc_html($setting['untranslated_product_tag']); ?></td>
                </tr>
            </tbody>
        </table>

    <?php endforeach; ?>
    <h2><?= esc_html__('Actions', 'woocommerce-multilanguage-duplicator'); ?></h2>
    <p>
        <button
            class="button button-primary"><?= esc_html__('Start Duplication Process', 'woocommerce-multilanguage-duplicator'); ?></button>
    </p>
</div>