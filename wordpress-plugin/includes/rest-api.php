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
 * Get customizations for a garment
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response
 */
function gc_get_customizations($request) {
    $garment_id = $request->get_param('id');
    $customizations = get_post_meta($garment_id, 'gc_customizations', true);

    if (!$customizations) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('No customizations found.', 'garment-customizer')
        ), 404);
    }

    return new WP_REST_Response(array(
        'success' => true,
        'data' => $customizations
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

    // Generate unique cart item key
    $cart_item_key = gc_generate_cart_item_key($garment_id, $customizations);

    // Add to cart
    $cart_item_data = array(
        'gc_customizations' => $customizations,
        'unique_key' => $cart_item_key,
    );

    $cart_item_id = WC()->cart->add_to_cart($garment_id, $quantity, 0, array(), $cart_item_data);

    if (!$cart_item_id) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Failed to add item to cart.', 'garment-customizer')
        ), 500);
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Item added to cart successfully.', 'garment-customizer'),
        'data' => array(
            'cart_url' => wc_get_cart_url()
        )
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
