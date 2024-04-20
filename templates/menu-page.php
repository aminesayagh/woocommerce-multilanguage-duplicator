<?php

function get_menu_items_by_menu_name($menu_name) {
    // First get the menu object
    $menu = wp_get_nav_menu_object($menu_name);

    // Check if the menu exists
    if (!$menu) {
        return 'Menu not found';
    }

    // Get the menu items
    $menu_items = wp_get_nav_menu_items($menu->term_id);

    // Check if there are items in the menu
    if (!$menu_items) {
        return 'No items in this menu';
    }

    // Prepare an array to hold the title and URL of each menu item
    $items = array();

    foreach ($menu_items as $menu_item) {
        // Check if the menu item is not a custom link
        if ($menu_item->type !== 'custom') {
            $menu_item->url = get_permalink($menu_item->object_id);
        }
        
        // Store the title and URL
        $items[] = array(
            'title' => $menu_item->title,
            'url' => $menu_item->url
        );
    }

    return $items;
}

function add_items_to_menu($menu_items, $target_menu_name) {
    $target_menu = wp_get_nav_menu_object($target_menu_name);
    if (!$target_menu) {
        return 'Target menu not found';
    }

    foreach($menu_items as $item) {
        $item_data = array(
            'menu-item-object-id' => $item->object_id,
            'menu-item-object' => $item->object,
            'menu-item-parent-id' => 0,
            'menu-item-position' => $item->menu_order,
            'menu-item-type' => $item->type,
            'menu-item-title' => $item->title,
            'menu-item-url' => $item->url,
            'menu-item-description' => $item->description,
            'menu-item-attr-title' => $item->attr_title,
            'menu-item-target' => $item->target,
            'menu-item-classes' => implode(' ', $item->classes),
            'menu-item-xfn' => $item->xfn,
            'menu-item-status' => $item->post_status
        );

        wp_update_nav_menu_item($target_menu->term_id, 0, $item_data);
    }
    return 'Items added successfully ' . count($menu_items);
}
function get_full_menu_items_by_menu_name($menu_name) {
    $menu = wp_get_nav_menu_object($menu_name);
    if (!$menu) {
        return 'Menu not found';
    }

    $menu_items = wp_get_nav_menu_items($menu->term_id);
    if (!$menu_items) {
        return 'No items in this menu';
    }

    return $menu_items;
}
$source_items = get_full_menu_items_by_menu_name('Menu Vendor Produit - en');

// If items were fetched successfully, add them to the target menu
if (is_array($source_items)) {
    $result = add_items_to_menu($source_items, 'Main Menu - en');
    echo $result; // Outputs success message or errors
} else {
    echo $source_items; // Outputs an error message
}