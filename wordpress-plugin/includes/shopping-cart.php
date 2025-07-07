<?php
/**
 * Shopping Cart Handler
 *
 * Handles cart functionality for customized garments.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add cart item data
 *
 * @param array $cart_item_data Cart item data
 * @param int   $product_id     Product ID
 * @param int   $variation_id   Variation ID
 * @return array
 */
function gc_add_cart_item_data($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['gc_customizations'])) {
        $customizations = json_decode(stripslashes($_POST['gc_customizations']), true);
        if (is_array($customizations)) {
            $cart_item_data['gc_customizations'] = $customizations;
            $cart_item_data['unique_key'] = gc_generate_cart_item_key($product_id, $customizations);
        }
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'gc_add_cart_item_data', 10, 3);

/**
 * Get item data to display in cart
 *
 * @param array $item_data Item data
 * @param array $cart_item Cart item
 * @return array
 */
function gc_get_item_data($item_data, $cart_item) {
    if (isset($cart_item['gc_customizations'])) {
        $customizations = $cart_item['gc_customizations'];
        
        // Add customization summary
        $item_data[] = array(
            'key' => __('Customizations', 'garment-customizer'),
            'value' => gc_get_customization_summary($customizations)
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'gc_get_item_data', 10, 2);

/**
 * Add customization data to order items
 *
 * @param WC_Order_Item_Product $item          Order item
 * @param string               $cart_item_key Cart item key
 * @param array                $values        Cart item values
 * @param WC_Order             $order         Order object
 */
function gc_add_order_item_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['gc_customizations'])) {
        $item->add_meta_data('_gc_customizations', $values['gc_customizations']);
        $item->add_meta_data(
            __('Customizations', 'garment-customizer'),
            gc_get_customization_summary($values['gc_customizations'])
        );
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'gc_add_order_item_meta', 10, 4);

/**
 * Get customization summary
 *
 * @param array $customizations Customization data
 * @return string
 */
function gc_get_customization_summary($customizations) {
    $summary = array();

    foreach ($customizations as $layer_id => $data) {
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'color':
                    if (!empty($data['color'])) {
                        $summary[] = sprintf(
                            __('Color: %s', 'garment-customizer'),
                            $data['color']
                        );
                    }
                    break;

                case 'text':
                    if (!empty($data['text'])) {
                        $summary[] = sprintf(
                            __('Text: %s', 'garment-customizer'),
                            $data['text']
                        );
                    }
                    break;

                case 'logo':
                    if (!empty($data['logo'])) {
                        $summary[] = __('Custom Logo Added', 'garment-customizer');
                    }
                    break;
            }
        }
    }

    return implode(', ', $summary);
}

/**
 * Adjust item price based on customizations
 *
 * @param string $price   Product price
 * @param array  $values  Cart item values
 * @param string $cart_item_key Cart item key
 * @return string
 */
function gc_adjust_cart_item_price($price, $values, $cart_item_key) {
    if (isset($values['gc_customizations'])) {
        $product_id = $values['product_id'];
        $base_price = floatval(get_post_meta($product_id, '_price', true));
        $customizations = $values['gc_customizations'];
        
        // Add customization costs
        foreach ($customizations as $layer_id => $data) {
            if (isset($data['type'])) {
                switch ($data['type']) {
                    case 'color':
                        $base_price += floatval(get_post_meta($product_id, 'gc_color_price', true));
                        break;

                    case 'text':
                        $base_price += floatval(get_post_meta($product_id, 'gc_text_price', true));
                        break;

                    case 'logo':
                        $base_price += floatval(get_post_meta($product_id, 'gc_logo_price', true));
                        break;
                }
            }
        }

        return wc_price($base_price);
    }
    return $price;
}
add_filter('woocommerce_cart_item_price', 'gc_adjust_cart_item_price', 10, 3);

/**
 * Add customization preview to cart item
 *
 * @param string $thumbnail Product thumbnail
 * @param array  $cart_item Cart item
 * @param string $cart_item_key Cart item key
 * @return string
 */
function gc_cart_item_thumbnail($thumbnail, $cart_item, $cart_item_key) {
    if (isset($cart_item['gc_customizations'])) {
        // Get preview image if available
        $preview_url = gc_get_customization_preview($cart_item['product_id'], $cart_item['gc_customizations']);
        if ($preview_url) {
            return sprintf(
                '<img src="%s" alt="%s" class="gc-cart-preview" />',
                esc_url($preview_url),
                esc_attr__('Customized garment preview', 'garment-customizer')
            );
        }
    }
    return $thumbnail;
}
add_filter('woocommerce_cart_item_thumbnail', 'gc_cart_item_thumbnail', 10, 3);

/**
 * Get customization preview image URL
 *
 * @param int   $product_id    Product ID
 * @param array $customizations Customization data
 * @return string|false
 */
function gc_get_customization_preview($product_id, $customizations) {
    // Implementation depends on how previews are generated and stored
    // This could involve generating images on the server or using a front-end canvas
    return false;
}

/**
 * Generate unique cart item key
 *
 * @param int   $product_id    Product ID
 * @param array $customizations Customization data
 * @return string
 */
function gc_generate_cart_item_key($product_id, $customizations) {
    return md5($product_id . serialize($customizations));
}

/**
 * Check if cart item has customizations
 *
 * @param array $cart_item Cart item
 * @return bool
 */
function gc_cart_item_has_customizations($cart_item) {
    return isset($cart_item['gc_customizations']) && !empty($cart_item['gc_customizations']);
}

/**
 * Add edit customization link to cart item
 *
 * @param string $product_name Product name
 * @param array  $cart_item    Cart item
 * @param string $cart_item_key Cart item key
 * @return string
 */
function gc_cart_item_name($product_name, $cart_item, $cart_item_key) {
    if (gc_cart_item_has_customizations($cart_item)) {
        $edit_url = add_query_arg(array(
            'edit-customization' => $cart_item_key,
            'product_id' => $cart_item['product_id']
        ), get_permalink($cart_item['product_id']));

        $product_name .= sprintf(
            ' <a href="%s" class="gc-edit-customization">%s</a>',
            esc_url($edit_url),
            esc_html__('Edit Customization', 'garment-customizer')
        );
    }
    return $product_name;
}
add_filter('woocommerce_cart_item_name', 'gc_cart_item_name', 10, 3);
