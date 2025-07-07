<?php
/**
 * Custom Cart Handler
 *
 * Manages cart functionality independently of WooCommerce.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize cart session
 */
function gc_init_cart_session() {
    if (!session_id()) {
        session_start();
    }
    if (!isset($_SESSION['gc_cart'])) {
        $_SESSION['gc_cart'] = array();
    }
}
add_action('init', 'gc_init_cart_session');

/**
 * Add item to cart
 *
 * @param int   $product_id Product ID
 * @param array $customizations Customization data
 * @param int   $quantity Quantity
 * @return string Cart item key
 */
function gc_add_item_to_cart($product_id, $customizations, $quantity = 1) {
    gc_init_cart_session();

    $cart_item_key = gc_generate_cart_item_key($product_id, $customizations);

    if (isset($_SESSION['gc_cart'][$cart_item_key])) {
        $_SESSION['gc_cart'][$cart_item_key]['quantity'] += $quantity;
    } else {
        $_SESSION['gc_cart'][$cart_item_key] = array(
            'product_id' => $product_id,
            'customizations' => $customizations,
            'quantity' => $quantity,
        );
    }

    return $cart_item_key;
}

/**
 * Get cart items
 *
 * @return array
 */
function gc_get_cart_items() {
    gc_init_cart_session();
    return $_SESSION['gc_cart'];
}

/**
 * Remove item from cart
 *
 * @param string $cart_item_key Cart item key
 */
function gc_remove_cart_item($cart_item_key) {
    gc_init_cart_session();
    if (isset($_SESSION['gc_cart'][$cart_item_key])) {
        unset($_SESSION['gc_cart'][$cart_item_key]);
    }
}

/**
 * Clear cart
 */
function gc_clear_cart() {
    gc_init_cart_session();
    $_SESSION['gc_cart'] = array();
}

/**
 * Calculate cart total price
 *
 * @return float
 */
function gc_calculate_cart_total() {
    $total = 0;
    $items = gc_get_cart_items();

    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $base_price = floatval(get_post_meta($product_id, '_price', true));
        $customizations = $item['customizations'];

        $customization_cost = 0;
        foreach ($customizations as $layer_id => $data) {
            if (isset($data['type'])) {
                switch ($data['type']) {
                    case 'color':
                        $customization_cost += floatval(get_post_meta($product_id, 'gc_color_price', true));
                        break;
                    case 'text':
                        $customization_cost += floatval(get_post_meta($product_id, 'gc_text_price', true));
                        break;
                    case 'logo':
                        $customization_cost += floatval(get_post_meta($product_id, 'gc_logo_price', true));
                        break;
                }
            }
        }

        $total += ($base_price + $customization_cost) * $quantity;
    }

    return $total;
}

/**
 * Generate cart item key
 *
 * @param int   $product_id Product ID
 * @param array $customizations Customization data
 * @return string
 */
function gc_generate_cart_item_key($product_id, $customizations) {
    return md5(serialize(array(
        'product_id' => $product_id,
        'customizations' => $customizations
    )));
}

/**
 * Update cart item quantity
 *
 * @param string $cart_item_key Cart item key
 * @param int    $quantity      New quantity
 */
function gc_update_cart_item_quantity($cart_item_key, $quantity) {
    gc_init_cart_session();
    if (isset($_SESSION['gc_cart'][$cart_item_key])) {
        $_SESSION['gc_cart'][$cart_item_key]['quantity'] = $quantity;
    }
}

/**
 * Get cart item count
 *
 * @return int
 */
function gc_get_cart_item_count() {
    $count = 0;
    $items = gc_get_cart_items();
    
    foreach ($items as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}
?>
