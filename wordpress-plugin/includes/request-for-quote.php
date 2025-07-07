<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Request for Quote (RFQ) form handling
 */

function gc_handle_rfq_submission() {
    $params = json_decode( file_get_contents( 'php://input' ), true );

    if ( empty( $params['name'] ) || empty( $params['email'] ) || empty( $params['message'] ) ) {
        return new WP_Error( 'gc_rfq_missing_fields', __( 'Please fill in all required fields.', 'garment-customizer' ), array( 'status' => 400 ) );
    }

    $name = sanitize_text_field( $params['name'] );
    $email = sanitize_email( $params['email'] );
    $message = sanitize_textarea_field( $params['message'] );

    // Store RFQ as custom post type
    $rfq_post = array(
        'post_title'   => wp_trim_words( $message, 10, '...' ),
        'post_content' => $message,
        'post_status'  => 'publish',
        'post_type'    => 'rfq',
        'meta_input'   => array(
            'rfq_name'  => $name,
            'rfq_email' => $email,
        ),
    );

    $post_id = wp_insert_post( $rfq_post );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'gc_rfq_save_failed', __( 'Failed to save RFQ.', 'garment-customizer' ), array( 'status' => 500 ) );
    }

    // Optionally send email notification to admin
    $admin_email = get_option( 'admin_email' );
    $subject = __( 'New Garment Customizer RFQ Submission', 'garment-customizer' );
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    $body = sprintf(
        '<p><strong>Name:</strong> %s</p><p><strong>Email:</strong> %s</p><p><strong>Message:</strong><br/>%s</p>',
        esc_html( $name ),
        esc_html( $email ),
        nl2br( esc_html( $message ) )
    );

    wp_mail( $admin_email, $subject, $body, $headers );

    return rest_ensure_response( array( 'success' => true, 'post_id' => $post_id ) );
}

/**
 * Register REST API routes for RFQ
 */
function gc_register_rfq_routes() {
    register_rest_route( 'gc/v1', '/rfq/submit', array(
        'methods'  => 'POST',
        'callback' => 'gc_handle_rfq_submission',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'gc_register_rfq_routes' );

/**
 * Register RFQ custom post type
 */
function gc_register_rfq_post_type() {
    $labels = array(
        'name'               => _x( 'RFQs', 'Post Type General Name', 'garment-customizer' ),
        'singular_name'      => _x( 'RFQ', 'Post Type Singular Name', 'garment-customizer' ),
        'menu_name'          => __( 'Request for Quotes', 'garment-customizer' ),
        'name_admin_bar'     => __( 'RFQ', 'garment-customizer' ),
        'archives'           => __( 'RFQ Archives', 'garment-customizer' ),
        'attributes'         => __( 'RFQ Attributes', 'garment-customizer' ),
        'parent_item_colon'  => __( 'Parent RFQ:', 'garment-customizer' ),
        'all_items'          => __( 'All RFQs', 'garment-customizer' ),
        'add_new_item'       => __( 'Add New RFQ', 'garment-customizer' ),
        'add_new'            => __( 'Add New', 'garment-customizer' ),
        'new_item'           => __( 'New RFQ', 'garment-customizer' ),
        'edit_item'          => __( 'Edit RFQ', 'garment-customizer' ),
        'update_item'        => __( 'Update RFQ', 'garment-customizer' ),
        'view_item'          => __( 'View RFQ', 'garment-customizer' ),
        'view_items'         => __( 'View RFQs', 'garment-customizer' ),
        'search_items'       => __( 'Search RFQ', 'garment-customizer' ),
        'not_found'          => __( 'Not found', 'garment-customizer' ),
        'not_found_in_trash' => __( 'Not found in Trash', 'garment-customizer' ),
        'featured_image'     => __( 'Featured Image', 'garment-customizer' ),
        'set_featured_image' => __( 'Set featured image', 'garment-customizer' ),
        'remove_featured_image' => __( 'Remove featured image', 'garment-customizer' ),
        'use_featured_image' => __( 'Use as featured image', 'garment-customizer' ),
        'insert_into_item'   => __( 'Insert into RFQ', 'garment-customizer' ),
        'uploaded_to_this_item' => __( 'Uploaded to this RFQ', 'garment-customizer' ),
        'items_list'         => __( 'RFQs list', 'garment-customizer' ),
        'items_list_navigation' => __( 'RFQs list navigation', 'garment-customizer' ),
        'filter_items_list'  => __( 'Filter RFQs list', 'garment-customizer' ),
    );
    $args = array(
        'label'               => __( 'RFQ', 'garment-customizer' ),
        'description'         => __( 'Request for Quote submissions', 'garment-customizer' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'custom-fields' ),
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 6,
        'menu_icon'           => 'dashicons-email-alt',
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => false,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'post',
        'show_in_rest'        => true,
    );
    register_post_type( 'rfq', $args );
}
add_action( 'init', 'gc_register_rfq_post_type' );
