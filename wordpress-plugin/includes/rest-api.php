<?php
/**
 * REST API Handler
 *
 * Handles all REST API endpoints for the garment customizer.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register REST API routes
 */
function gc_register_rest_routes() {
    register_rest_route('garment-customizer/v1', '/customizations/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'gc_get_customizations',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));

    register_rest_route('garment-customizer/v1', '/customizations/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'gc_save_customizations',
        'permission_callback' => function() {
            return wp_verify_nonce($_REQUEST['nonce'], 'gc_customizer_nonce');
        },
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));

    register_rest_route('garment-customizer/v1', '/cart/add', array(
        'methods' => 'POST',
        'callback' => 'gc_add_to_cart',
        'permission_callback' => function() {
            return wp_verify_nonce($_REQUEST['nonce'], 'gc_customizer_nonce');
        },
    ));

    register_rest_route('garment-customizer/v1', '/quote/request', array(
        'methods' => 'POST',
        'callback' => 'gc_request_quote',
        'permission_callback' => function() {
            return wp_verify_nonce($_REQUEST['nonce'], 'gc_customizer_nonce');
        },
    ));
}
add_action('rest_api_init', 'gc_register_rest_routes');

/**
 * Validate logo content using Open Router API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_validate_logo($request) {
    if (empty($_FILES['logo'])) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('No logo file uploaded.', 'garment-customizer')
        ), 400);
    }

    $logo_file = $_FILES['logo'];

    // Validate file size and type
    $max_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/svg+xml');

    if ($logo_file['size'] > $max_size) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Logo file size exceeds the maximum allowed size.', 'garment-customizer')
        ), 400);
    }

    if (!in_array($logo_file['type'], $allowed_types)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Unsupported logo file type.', 'garment-customizer')
        ), 400);
    }

    // Read file content
    $file_content = file_get_contents($logo_file['tmp_name']);
    $base64_logo = base64_encode($file_content);

    // Prepare Open Router API request
    $api_key = get_option('gc_open_router_api_key');
    if (!$api_key) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Open Router API key is not configured.', 'garment-customizer')
        ), 500);
    }

    $endpoint = 'https://api.openrouter.ai/v1/chat/completions';
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    );

    $body = json_encode(array(
        'model' => 'gpt-4o-mini',
        'messages' => array(
            array(
                'role' => 'system',
                'content' => 'You are a content safety checker for logos.'
            ),
            array(
                'role' => 'user',
                'content' => 'Check the following base64 encoded logo for inappropriate content: ' . $base64_logo
            )
        )
    ));

    $response = wp_remote_post($endpoint, array(
        'headers' => $headers,
        'body' => $body,
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to connect to Open Router API.', 'garment-customizer')
        ), 500);
    }

    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    if (empty($data['choices'][0]['message']['content'])) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Invalid response from Open Router API.', 'garment-customizer')
        ), 500);
    }

    $result = strtolower($data['choices'][0]['message']['content']);
    $is_safe = strpos($result, 'safe') !== false;

    if (!$is_safe) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Logo content is not safe.', 'garment-customizer')
        ), 400);
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Logo content is safe.', 'garment-customizer')
    ), 200);
}

/**
 * Save customizations for a garment
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_save_customizations($request) {
    $garment_id = $request->get_param('id');
    $customizations = $request->get_param('customizations');

    if (!$customizations) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('No customization data provided.', 'garment-customizer')
        ), 400);
    }

    $result = update_post_meta($garment_id, 'gc_customizations', $customizations);

    if (!$result) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to save customizations.', 'garment-customizer')
        ), 500);
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Customizations saved successfully.', 'garment-customizer')
    ), 200);
}

/**
 * Add customized garment to cart
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_add_to_cart($request) {
    $garment_id = $request->get_param('garment_id');
    $customizations = $request->get_param('customizations');
    $quantity = $request->get_param('quantity') ?: 1;

    if (!$garment_id || !$customizations) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Invalid request data.', 'garment-customizer')
        ), 400);
    }

    // Add item to custom cart
    $cart_item_key = gc_add_item_to_cart($garment_id, $customizations, $quantity);

    if (!$cart_item_key) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to add item to cart.', 'garment-customizer')
        ), 500);
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Item added to cart successfully.', 'garment-customizer'),
        'data' => array(
            'cart_url' => wc_get_cart_url() // This should be updated to custom cart page URL
        )
    ), 200);
}

/**
 * Get cart items
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_get_cart_items($request) {
    $items = gc_get_cart_items();

    return new WP_REST_Response(array(
        'success' => true,
        'data' => $items
    ), 200);
}

/**
 * Remove item from cart
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_remove_cart_item($request) {
    $cart_item_key = $request->get_param('cart_item_key');

    if (!$cart_item_key) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Invalid cart item key.', 'garment-customizer')
        ), 400);
    }

    gc_remove_cart_item($cart_item_key);

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Item removed from cart.', 'garment-customizer')
    ), 200);
}

/**
 * Clear cart
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_clear_cart($request) {
    gc_clear_cart();

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Cart cleared.', 'garment-customizer')
    ), 200);
}

/**
 * Request quote for customized garment
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_request_quote($request) {
    $garment_id = $request->get_param('garment_id');
    $customizations = $request->get_param('customizations');
    $customer_data = $request->get_param('customer');

    if (!$garment_id || !$customizations || !$customer_data) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Invalid request data.', 'garment-customizer')
        ), 400);
    }

    // Create quote request post
    $quote_data = array(
        'post_title' => sprintf(
            __('Quote Request for %s', 'garment-customizer'),
            get_the_title($garment_id)
        ),
        'post_type' => 'gc_quote',
        'post_status' => 'publish',
        'meta_input' => array(
            'gc_garment_id' => $garment_id,
            'gc_customizations' => $customizations,
            'gc_customer_data' => $customer_data
        )
    );

    $quote_id = wp_insert_post($quote_data);

    if (!$quote_id || is_wp_error($quote_id)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to create quote request.', 'garment-customizer')
        ), 500);
    }

    // Send notification emails
    gc_send_quote_notifications($quote_id);

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Quote request submitted successfully.', 'garment-customizer'),
        'data' => array(
            'quote_id' => $quote_id,
            'quote_url' => get_permalink($quote_id)
        )
    ), 200);
}

/**
 * Generate unique cart item key
 *
 * @param int   $garment_id Garment post ID
 * @param array $customizations Customization data
 * @return string
 */
function gc_generate_cart_item_key($garment_id, $customizations) {
    return md5($garment_id . serialize($customizations));
}

/**
 * Send quote request notifications
 *
 * @param int $quote_id Quote post ID
 */
function gc_send_quote_notifications($quote_id) {
    $quote = get_post($quote_id);
    $customer_data = get_post_meta($quote_id, 'gc_customer_data', true);
    $garment_id = get_post_meta($quote_id, 'gc_garment_id', true);

    // Send customer email
    $to = $customer_data['email'];
    $subject = sprintf(
        __('Your Quote Request for %s', 'garment-customizer'),
        get_the_title($garment_id)
    );
    
    $message = gc_get_quote_email_content($quote_id, 'customer');
    
    wp_mail($to, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));

    // Send admin notification
    $admin_email = get_option('admin_email');
    $admin_subject = sprintf(
        __('New Quote Request: %s', 'garment-customizer'),
        get_the_title($garment_id)
    );
    
    $admin_message = gc_get_quote_email_content($quote_id, 'admin');
    
    wp_mail($admin_email, $admin_subject, $admin_message, array('Content-Type: text/html; charset=UTF-8'));
}

/**
 * Get quote email content
 *
 * @param int    $quote_id Quote post ID
 * @param string $type Email type (customer or admin)
 * @return string
 */
function gc_get_quote_email_content($quote_id, $type = 'customer') {
    ob_start();
    
    if ($type === 'customer') {
        gc_get_template('emails/customer-quote.php', array('quote_id' => $quote_id));
    } else {
        gc_get_template('emails/admin-quote.php', array('quote_id' => $quote_id));
    }
    
    return ob_get_clean();
}
