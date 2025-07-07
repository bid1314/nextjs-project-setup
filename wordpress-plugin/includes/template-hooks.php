<?php
/**
 * Template Hooks
 *
 * Action/filter hooks used for GarmentCustomizer functions/templates
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Wrappers
 */
add_action('gc_before_main_content', 'gc_output_content_wrapper', 10);
add_action('gc_after_main_content', 'gc_output_content_wrapper_end', 10);

/**
 * Garment Loop
 */
add_action('gc_before_garment_loop', 'gc_garment_loop_start', 10);
add_action('gc_after_garment_loop', 'gc_garment_loop_end', 10);
add_action('gc_no_garments_found', 'gc_no_garments_found', 10);

/**
 * Single Garment
 */
add_action('gc_before_single_garment', 'gc_before_single_garment', 10);
add_action('gc_after_single_garment', 'gc_after_single_garment', 10);

add_action('gc_single_garment_summary', 'gc_template_single_title', 5);
add_action('gc_single_garment_summary', 'gc_template_single_price', 10);
add_action('gc_single_garment_summary', 'gc_template_single_excerpt', 20);
add_action('gc_single_garment_summary', 'gc_template_single_meta', 40);
add_action('gc_single_garment_summary', 'gc_template_single_customizer', 60);

/**
 * Customizer Interface
 */
add_action('gc_before_customizer', 'gc_customizer_wrapper_start', 10);
add_action('gc_after_customizer', 'gc_customizer_wrapper_end', 10);

add_action('gc_customizer_content', 'gc_customizer_preview', 10);
add_action('gc_customizer_content', 'gc_customizer_controls', 20);
add_action('gc_customizer_content', 'gc_customizer_actions', 30);

/**
 * Layers Panel
 */
add_action('gc_customizer_layers_panel', 'gc_customizer_layers_list', 10);
add_action('gc_customizer_layers_panel', 'gc_customizer_layer_controls', 20);

/**
 * Colors Panel
 */
add_action('gc_customizer_colors_panel', 'gc_customizer_color_picker', 10);
add_action('gc_customizer_colors_panel', 'gc_customizer_color_presets', 20);

/**
 * Text Panel
 */
add_action('gc_customizer_text_panel', 'gc_customizer_text_input', 10);
add_action('gc_customizer_text_panel', 'gc_customizer_text_controls', 20);

/**
 * Logo Panel
 */
add_action('gc_customizer_logo_panel', 'gc_customizer_logo_upload', 10);
add_action('gc_customizer_logo_panel', 'gc_customizer_logo_controls', 20);

/**
 * Cart
 */
add_action('gc_before_cart', 'gc_cart_wrapper_start', 10);
add_action('gc_after_cart', 'gc_cart_wrapper_end', 10);

add_action('gc_cart_content', 'gc_cart_items', 10);
add_action('gc_cart_content', 'gc_cart_totals', 20);
add_action('gc_cart_content', 'gc_cart_actions', 30);

/**
 * Request for Quote
 */
add_action('gc_before_rfq_form', 'gc_rfq_form_wrapper_start', 10);
add_action('gc_after_rfq_form', 'gc_rfq_form_wrapper_end', 10);

add_action('gc_rfq_form_content', 'gc_rfq_form_fields', 10);
add_action('gc_rfq_form_content', 'gc_rfq_form_summary', 20);
add_action('gc_rfq_form_content', 'gc_rfq_form_submit', 30);

/**
 * Archive Pages
 */
add_action('gc_before_garment_archive', 'gc_garment_archive_description', 10);
add_action('gc_before_garment_archive', 'gc_garment_archive_ordering', 20);
add_action('gc_before_garment_archive', 'gc_garment_archive_filters', 30);

/**
 * Sidebar
 */
add_action('gc_sidebar', 'gc_get_sidebar', 10);

/**
 * Messages
 */
add_action('gc_before_main_content', 'gc_output_all_notices', 10);

/**
 * Account
 */
add_action('gc_account_navigation', 'gc_account_navigation');
add_action('gc_account_content', 'gc_account_content');
add_action('gc_account_orders_endpoint', 'gc_account_orders');
add_action('gc_account_quotes_endpoint', 'gc_account_quotes');
add_action('gc_account_edit_account_endpoint', 'gc_account_edit_account');

/**
 * Auth
 */
add_action('gc_before_customer_login_form', 'gc_login_wrapper_start', 10);
add_action('gc_after_customer_login_form', 'gc_login_wrapper_end', 10);

/**
 * Structured Data
 */
add_action('gc_single_garment_summary', 'gc_structured_data', 60);
