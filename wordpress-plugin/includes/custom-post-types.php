<?php
/**
 * Custom Post Types Registration
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Garment post type
 */
function gc_register_garment_post_type() {
    $labels = array(
        'name'                  => _x('Garments', 'Post type general name', 'garment-customizer'),
        'singular_name'         => _x('Garment', 'Post type singular name', 'garment-customizer'),
        'menu_name'            => _x('Garments', 'Admin Menu text', 'garment-customizer'),
        'name_admin_bar'       => _x('Garment', 'Add New on Toolbar', 'garment-customizer'),
        'add_new'              => __('Add New', 'garment-customizer'),
        'add_new_item'         => __('Add New Garment', 'garment-customizer'),
        'new_item'             => __('New Garment', 'garment-customizer'),
        'edit_item'            => __('Edit Garment', 'garment-customizer'),
        'view_item'            => __('View Garment', 'garment-customizer'),
        'all_items'            => __('All Garments', 'garment-customizer'),
        'search_items'         => __('Search Garments', 'garment-customizer'),
        'parent_item_colon'    => __('Parent Garments:', 'garment-customizer'),
        'not_found'            => __('No garments found.', 'garment-customizer'),
        'not_found_in_trash'   => __('No garments found in Trash.', 'garment-customizer'),
        'featured_image'       => _x('Garment Cover Image', 'Overrides the "Featured Image" phrase', 'garment-customizer'),
        'set_featured_image'   => _x('Set cover image', 'Overrides the "Set featured image" phrase', 'garment-customizer'),
        'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase', 'garment-customizer'),
        'use_featured_image'   => _x('Use as cover image', 'Overrides the "Use as featured image" phrase', 'garment-customizer'),
        'archives'             => _x('Garment archives', 'The post type archive label used in nav menus', 'garment-customizer'),
        'insert_into_item'     => _x('Insert into garment', 'Overrides the "Insert into post" phrase', 'garment-customizer'),
        'uploaded_to_this_item' => _x('Uploaded to this garment', 'Overrides the "Uploaded to this post" phrase', 'garment-customizer'),
        'filter_items_list'    => _x('Filter garments list', 'Screen reader text for the filter links', 'garment-customizer'),
        'items_list_navigation' => _x('Garments list navigation', 'Screen reader text for the pagination', 'garment-customizer'),
        'items_list'           => _x('Garments list', 'Screen reader text for the items list', 'garment-customizer'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'garment'),
        'capability_type'   => 'post',
        'has_archive'       => true,
        'hierarchical'      => false,
        'menu_position'     => 20,
        'supports'          => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'      => true,
        'menu_icon'         => 'dashicons-admin-customizer',
    );

    register_post_type('garment', $args);
}
add_action('init', 'gc_register_garment_post_type');

/**
 * Register Request for Quote post type
 */
function gc_register_rfq_post_type() {
    $labels = array(
        'name'                  => _x('Quotes', 'Post type general name', 'garment-customizer'),
        'singular_name'         => _x('Quote', 'Post type singular name', 'garment-customizer'),
        'menu_name'            => _x('Quote Requests', 'Admin Menu text', 'garment-customizer'),
        'name_admin_bar'       => _x('Quote', 'Add New on Toolbar', 'garment-customizer'),
        'add_new'              => __('Add New', 'garment-customizer'),
        'add_new_item'         => __('Add New Quote', 'garment-customizer'),
        'new_item'             => __('New Quote', 'garment-customizer'),
        'edit_item'            => __('Edit Quote', 'garment-customizer'),
        'view_item'            => __('View Quote', 'garment-customizer'),
        'all_items'            => __('All Quotes', 'garment-customizer'),
        'search_items'         => __('Search Quotes', 'garment-customizer'),
        'parent_item_colon'    => __('Parent Quotes:', 'garment-customizer'),
        'not_found'            => __('No quotes found.', 'garment-customizer'),
        'not_found_in_trash'   => __('No quotes found in Trash.', 'garment-customizer'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'quote-request'),
        'capability_type'   => 'post',
        'has_archive'       => false,
        'hierarchical'      => false,
        'menu_position'     => 21,
        'supports'          => array('title', 'editor', 'custom-fields'),
        'show_in_rest'      => true,
        'menu_icon'         => 'dashicons-cart',
    );

    register_post_type('rfq', $args);
}
add_action('init', 'gc_register_rfq_post_type');

/**
 * Register Garment Categories taxonomy
 */
function gc_register_garment_taxonomies() {
    $labels = array(
        'name'              => _x('Garment Categories', 'taxonomy general name', 'garment-customizer'),
        'singular_name'     => _x('Category', 'taxonomy singular name', 'garment-customizer'),
        'search_items'      => __('Search Categories', 'garment-customizer'),
        'all_items'         => __('All Categories', 'garment-customizer'),
        'parent_item'       => __('Parent Category', 'garment-customizer'),
        'parent_item_colon' => __('Parent Category:', 'garment-customizer'),
        'edit_item'         => __('Edit Category', 'garment-customizer'),
        'update_item'       => __('Update Category', 'garment-customizer'),
        'add_new_item'      => __('Add New Category', 'garment-customizer'),
        'new_item_name'     => __('New Category Name', 'garment-customizer'),
        'menu_name'         => __('Categories', 'garment-customizer'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'           => $labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'garment-category'),
        'show_in_rest'     => true,
    );

    register_taxonomy('garment_category', array('garment'), $args);
}
add_action('init', 'gc_register_garment_taxonomies');
