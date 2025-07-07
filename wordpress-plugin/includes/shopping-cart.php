<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Shopping Cart functionality for Garment Customizer plugin
 */

function gc_add_to_cart() {
    $params = json_decode( file_get_contents( 'php://input' ), true );

    if ( ! isset( $params['garment_id'] ) || ! is_numeric( $params['garment_id'] ) ) {
        return new WP_Error( 'gc_invalid_garment_id', __( 'Invalid garment ID', 'garment-customizer' ), array( 'status' => 400 ) );
    }

    $garment_id = intval( $params['garment_id'] );
    $customization = isset( $params['customization'] ) ? $params['customization'] : array();

    $cart = gc_get_cart();
    $cart[] = array(
        'garment_id'    => $garment_id,
        'customization' => $customization,
        'added_at'      => current_time( 'mysql' ),
    );

    gc_save_cart( $cart );

    return rest_ensure_response( array( 'success' => true, 'cart' => $cart ) );
}

function gc_get_cart() {
    $user_id = get_current_user_id();
    if ( $user_id ) {
        $cart = get_user_meta( $user_id, '_gc_cart', true );
        if ( ! is_array( $cart ) ) {
            $cart = array();
        }
        return $cart;
    } else {
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }
        return isset( $_SESSION['_gc_cart'] ) ? $_SESSION['_gc_cart'] : array();
    }
}

function gc_save_cart( $cart ) {
    $user_id = get_current_user_id();
    if ( $user_id ) {
        update_user_meta( $user_id, '_gc_cart', $cart );
    } else {
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }
        $_SESSION['_gc_cart'] = $cart;
    }
}

function gc_clear_cart() {
    $user_id = get_current_user_id();
    if ( $user_id ) {
        delete_user_meta( $user_id, '_gc_cart' );
    } else {
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }
        unset( $_SESSION['_gc_cart'] );
    }
}

/**
 * Register REST API routes for cart
 */
function gc_register_cart_routes() {
    register_rest_route( 'gc/v1', '/cart/add', array(
        'methods'  => 'POST',
        'callback' => 'gc_add_to_cart',
        'permission_callback' => '__return_true',
    ) );

    register_rest_route( 'gc/v1', '/cart', array(
        'methods'  => 'GET',
        'callback' => 'gc_get_cart_rest',
        'permission_callback' => '__return_true',
    ) );

    register_rest_route( 'gc/v1', '/cart/clear', array(
        'methods'  => 'POST',
        'callback' => 'gc_clear_cart_rest',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'gc_register_cart_routes' );

/**
 * REST callback to get cart
 */
function gc_get_cart_rest() {
    $cart = gc_get_cart();
    return rest_ensure_response( $cart );
}

/**
 * REST callback to clear cart
 */
function gc_clear_cart_rest() {
    gc_clear_cart();
    return rest_ensure_response( array( 'success' => true ) );
}
