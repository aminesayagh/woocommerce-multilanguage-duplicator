<?php

$product_id = '9910';
$product = wc_get_product($product_id);
$product_lang = pll_get_post_language($product_id);
$other_langs = pll_languages_list();
unset($other_langs[$product_lang]);
echo 'test '. $product_lang;
echo '<pre>';
print_r($other_langs);
echo '</pre>';