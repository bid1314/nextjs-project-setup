<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register REST API routes for Garment Customizer
 */
function gc_register_rest_routes() {
    register_rest_route( 'gc/v1', '/garments', array(
        'methods'  => 'GET',
        'callback' => 'gc_get_garments',
        'permission_callback' => '__return_true',
    ) );

    register_rest_route( 'gc/v1', '/garment/(?P<id>\d+)', array(
        'methods'  => 'GET',
        'callback' => 'gc_get_garment',
        'permission_callback' => '__return_true',
        'args'     => array(
            'id' => array(
                'validate_callback' => 'is_numeric',
            ),
        ),
    ) );

    register_rest_route( 'gc/v1', '/garment/(?P<id>\d+)', array(
        'methods'  => 'POST',
        'callback' => 'gc_update_garment',
        'permission_callback' => 'gc_rest_permission_check',
        'args'     => array(
            'id' => array(
                'validate_callback' => 'is_numeric',
            ),
        ),
    ) );

    register_rest_route( 'gc/v1', '/logo/validate', array(
        'methods'  => 'POST',
        'callback' => 'gc_validate_logo',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'gc_register_rest_routes' );

/**
 * Permission check for updating garment
 */
function gc_rest_permission_check() {
    return current_user_can( 'edit_posts' );
}

/**
 * Get all garments
 */
function gc_get_garments( $request ) {
    $args = array(
        'post_type'      => 'garment',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );
    $query = new WP_Query( $args );
    $garments = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $garments[] = array(
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'content' => get_the_content(),
                'meta'  => get_post_meta( get_the_ID() ),
            );
        }
        wp_reset_postdata();
    }

    return rest_ensure_response( $garments );
}

/**
 * Get single garment by ID
 */
function gc_get_garment( $request ) {
    $id = (int) $request['id'];
    $post = get_post( $id );

    if ( ! $post || $post->post_type !== 'garment' ) {
        return new WP_Error( 'gc_garment_not_found', __( 'Garment not found', 'garment-customizer' ), array( 'status' => 404 ) );
    }

    $data = array(
        'id'      => $post->ID,
        'title'   => $post->post_title,
        'content' => $post->post_content,
        'meta'    => get_post_meta( $post->ID ),
    );

    return rest_ensure_response( $data );
}

/**
 * Update garment customization data
 */
function gc_update_garment( $request ) {
    $id = (int) $request['id'];
    $post = get_post( $id );

    if ( ! $post || $post->post_type !== 'garment' ) {
        return new WP_Error( 'gc_garment_not_found', __( 'Garment not found', 'garment-customizer' ), array( 'status' => 404 ) );
    }

    if ( ! current_user_can( 'edit_post', $id ) ) {
        return new WP_Error( 'gc_permission_denied', __( 'You do not have permission to edit this garment', 'garment-customizer' ), array( 'status' => 403 ) );
    }

    $params = $request->get_json_params();

    if ( isset( $params['meta'] ) && is_array( $params['meta'] ) ) {
        foreach ( $params['meta'] as $key => $value ) {
            update_post_meta( $id, sanitize_key( $key ), sanitize_text_field( $value ) );
        }
    }

    return rest_ensure_response( array( 'success' => true ) );
}

/**
 * Validate logo content via external API (stub)
 */
function gc_validate_logo( $request ) {
    $params = $request->get_json_params();

    if ( empty( $params['logo_url'] ) ) {
        return new WP_Error( 'gc_logo_url_missing', __( 'Logo URL is required', 'garment-customizer' ), array( 'status' => 400 ) );
    }

    $logo_url = esc_url_raw( $params['logo_url'] );

    // TODO: Implement actual API call to Open Router or other service for content validation
    // For now, simulate success response
    $response = array(
        'valid' => true,
        'message' => __( 'Logo content is valid.', 'garment-customizer' ),
    );

    return rest_ensure_response( $response );
}
