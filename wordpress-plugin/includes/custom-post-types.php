<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register Custom Post Type: Garment
 */
function gc_register_garment_post_type() {
    $labels = array(
        'name'                  => _x( 'Garments', 'Post Type General Name', 'garment-customizer' ),
        'singular_name'         => _x( 'Garment', 'Post Type Singular Name', 'garment-customizer' ),
        'menu_name'             => __( 'Garments', 'garment-customizer' ),
        'name_admin_bar'        => __( 'Garment', 'garment-customizer' ),
        'archives'              => __( 'Garment Archives', 'garment-customizer' ),
        'attributes'            => __( 'Garment Attributes', 'garment-customizer' ),
        'parent_item_colon'     => __( 'Parent Garment:', 'garment-customizer' ),
        'all_items'             => __( 'All Garments', 'garment-customizer' ),
        'add_new_item'          => __( 'Add New Garment', 'garment-customizer' ),
        'add_new'               => __( 'Add New', 'garment-customizer' ),
        'new_item'              => __( 'New Garment', 'garment-customizer' ),
        'edit_item'             => __( 'Edit Garment', 'garment-customizer' ),
        'update_item'           => __( 'Update Garment', 'garment-customizer' ),
        'view_item'             => __( 'View Garment', 'garment-customizer' ),
        'view_items'            => __( 'View Garments', 'garment-customizer' ),
        'search_items'          => __( 'Search Garment', 'garment-customizer' ),
        'not_found'             => __( 'Not found', 'garment-customizer' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'garment-customizer' ),
        'featured_image'        => __( 'Featured Image', 'garment-customizer' ),
        'set_featured_image'    => __( 'Set featured image', 'garment-customizer' ),
        'remove_featured_image' => __( 'Remove featured image', 'garment-customizer' ),
        'use_featured_image'    => __( 'Use as featured image', 'garment-customizer' ),
        'insert_into_item'      => __( 'Insert into garment', 'garment-customizer' ),
        'uploaded_to_this_item' => __( 'Uploaded to this garment', 'garment-customizer' ),
        'items_list'            => __( 'Garments list', 'garment-customizer' ),
        'items_list_navigation' => __( 'Garments list navigation', 'garment-customizer' ),
        'filter_items_list'     => __( 'Filter garments list', 'garment-customizer' ),
    );
    $args = array(
        'label'                 => __( 'Garment', 'garment-customizer' ),
        'description'           => __( 'Garment Customizer Products', 'garment-customizer' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'              => true,
        'show_in_menu'         => 'garment-customizer',
        'menu_position'        => 5,
        'menu_icon'            => 'dashicons-admin-appearance',
        'show_in_admin_bar'    => true,
        'show_in_nav_menus'    => true,
        'can_export'           => true,
        'has_archive'          => true,
        'exclude_from_search'  => false,
        'publicly_queryable'   => true,
        'capability_type'      => 'post',
        'show_in_rest'         => true,
        'rest_base'            => 'garments',
        'template'             => array(
            array('core/heading', array(
                'content' => 'Garment Details',
                'level' => 2
            )),
            array('core/paragraph', array(
                'placeholder' => 'Enter garment description...'
            ))
        ),
        'template_lock'        => 'all',
    );
    register_post_type( 'garment', $args );
}
add_action( 'init', 'gc_register_garment_post_type', 0 );
