<?php
// Ensure the file is being accessed within WordPress context
if (!defined('ABSPATH')) exit;

// Assuming the $product variable is passed correctly from the loop
$target_language_code = 'en'; // Set the target language code for the translation
$source_language_code = 'fr'; // Source language code

// Retrieve the French version of the product
$french_product_id = pll_get_post($product->get_id(), $source_language_code);
$french_product = $french_product_id ? wc_get_product($french_product_id) : null;

// Retrieve any existing translation ID for the target language
$translated_product_id = pll_get_post($product->get_id(), $target_language_code);
$translated_product = $translated_product_id ? wc_get_product($translated_product_id) : null;

// Check if a translation already exists in the target language
if ($translated_product) {
    // If the translation exists, do not display the form
    return;  // Exit this include script without rendering the form
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Translate Product</h1>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="form-wrap">
        <input type="hidden" name="action" value="submit_translation">
        <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
        <input type="hidden" name="language_code" value="<?php echo esc_attr($target_language_code); ?>">

        <div class="form-field form-required">
            <label for="translated_title">Title:</label>
            <input type="text" id="translated_title" name="translated_title" value="<?php echo $french_product ? esc_attr($french_product->get_name()) : ''; ?>" required class="regular-text">
        </div>
        
        <div class="form-field">
            <label for="translated_description">Description:</label>
            <textarea id="translated_description" name="translated_description" rows="5" required class="large-text"><?php echo $french_product ? esc_textarea($french_product->get_description()) : ''; ?></textarea>
        </div>
        
        <?php wp_nonce_field('translate_product_nonce', 'translate_nonce'); ?>
        <p class="submit">
            <input type="submit" value="Translate Product" class="button button-primary">
        </p>
    </form>
</div>
