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

    $content = $data['choices'][0]['message']['content'];
    $is_safe = stripos($content, 'inappropriate') === false;

    return new WP_REST_Response(array(
        'success' => true,
        'safe' => $is_safe,
        'message' => $is_safe ? __('Logo is safe to use.', 'garment-customizer') : __('Logo contains inappropriate content.', 'garment-customizer')
    ), 200);
}

/**
 * Get customizations for a garment
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_get_customizations($request) {
    $garment_id = $request->get_param('id');
    
    if (!$garment_id) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Garment ID is required.', 'garment-customizer')
        ), 400);
    }

    $garment = get_post($garment_id);
    if (!$garment || $garment->post_type !== 'garment') {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Garment not found.', 'garment-customizer')
        ), 404);
    }

    $customizations = get_post_meta($garment_id, 'gc_layers', true) ?: array();
    $base_image = get_post_meta($garment_id, 'gc_base_image', true);
    $available_sizes = get_post_meta($garment_id, 'gc_available_sizes', true) ?: array();
    $default_color = get_post_meta($garment_id, 'gc_default_color', true);

    return new WP_REST_Response(array(
        'success' => true,
        'data' => array(
            'garment_id' => $garment_id,
            'title' => get_the_title($garment_id),
            'base_image' => $base_image,
            'available_sizes' => $available_sizes,
            'default_color' => $default_color,
            'customizations' => $customizations
        )
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
    
    if (!$garment_id) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Garment ID is required.', 'garment-customizer')
        ), 400);
    }

    $garment = get_post($garment_id);
    if (!$garment || $garment->post_type !== 'garment') {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Garment not found.', 'garment-customizer')
        ), 404);
    }

    // Save customizations to session or user meta
    if (is_user_logged_in()) {
        update_user_meta(get_current_user_id(), "gc_customizations_{$garment_id}", $customizations);
    } else {
        // Save to session
        if (!session_id()) {
            session_start();
        }
        $_SESSION["gc_customizations_{$garment_id}"] = $customizations;
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Customizations saved successfully.', 'garment-customizer')
    ), 200);
}

/**
 * Add item to cart
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
            'message' => __('Garment ID and customizations are required.', 'garment-customizer')
        ), 400);
    }

    // Add to cart
    $cart_key = gc_add_item_to_cart($garment_id, $customizations, $quantity);
    
    if (!$cart_key) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to add item to cart.', 'garment-customizer')
        ), 500);
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Item added to cart successfully.', 'garment-customizer'),
        'cart_key' => $cart_key,
        'cart_count' => gc_get_cart_item_count()
    ), 200);
}

/**
 * Request a quote
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
            'message' => __('All fields are required.', 'garment-customizer')
        ), 400);
    }

    // Validate customer data
    if (empty($customer_data['name']) || empty($customer_data['email'])) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Name and email are required.', 'garment-customizer')
        ), 400);
    }

    // Create quote
    $quote_id = gc_create_quote($garment_id, $customizations, $customer_data);
    
    if (!$quote_id || is_wp_error($quote_id)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to create quote request.', 'garment-customizer')
        ), 500);
    }

    // Send notifications
    gc_send_quote_notifications($quote_id);

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Quote request submitted successfully.', 'garment-customizer'),
        'quote_id' => $quote_id
    ), 200);
}

/**
 * Send quote notifications
 *
 * @param int $quote_id Quote ID
 */
function gc_send_quote_notifications($quote_id) {
    $quote = get_post($quote_id);
    if (!$quote) {
        return;
    }

    $customer_data = get_post_meta($quote_id, 'gc_customer_data', true);
    $garment_id = get_post_meta($quote_id, 'gc_garment_id', true);
    $customizations = get_post_meta($quote_id, 'gc_customizations', true);

    // Send email to admin
    $admin_email = get_option('admin_email');
    $subject = sprintf(__('New Quote Request - %s', 'garment-customizer'), get_the_title($garment_id));
    
    $message = sprintf(
        __('A new quote request has been submitted for %s.', 'garment-customizer'),
        get_the_title($garment_id)
    );
    $message .= "\n\n";
    $message .= __('Customer Details:', 'garment-customizer') . "\n";
    $message .= __('Name:', 'garment-customizer') . ' ' . $customer_data['name'] . "\n";
    $message .= __('Email:', 'garment-customizer') . ' ' . $customer_data['email'] . "\n";
    
    if (!empty($customer_data['phone'])) {
        $message .= __('Phone:', 'garment-customizer') . ' ' . $customer_data['phone'] . "\n";
    }
    
    if (!empty($customer_data['message'])) {
        $message .= __('Message:', 'garment-customizer') . "\n" . $customer_data['message'] . "\n";
    }
    
    $message .= "\n" . __('View Quote:', 'garment-customizer') . ' ' . get_edit_post_link($quote_id);

    wp_mail($admin_email, $subject, $message);

    // Send confirmation email to customer
    $customer_subject = sprintf(__('Quote Request Confirmation - %s', 'garment-customizer'), get_bloginfo('name'));
    $customer_message = sprintf(
        __('Thank you for your quote request for %s. We will review your request and get back to you soon.', 'garment-customizer'),
        get_the_title($garment_id)
    );

    wp_mail($customer_data['email'], $customer_subject, $customer_message);
}
