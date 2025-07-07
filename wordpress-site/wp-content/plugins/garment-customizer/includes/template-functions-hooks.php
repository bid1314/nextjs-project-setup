<?php
/**
 * Template Functions Hooks Implementation
 *
 * Functions hooked into GarmentCustomizer template actions
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output the start of the page wrapper
 */
function gc_output_content_wrapper() {
    gc_get_template('global/wrapper-start.php');
}

/**
 * Output the end of the page wrapper
 */
function gc_output_content_wrapper_end() {
    gc_get_template('global/wrapper-end.php');
}

/**
 * Output the garment loop start
 */
function gc_garment_loop_start() {
    gc_get_template('loop/loop-start.php');
}

/**
 * Output the garment loop end
 */
function gc_garment_loop_end() {
    gc_get_template('loop/loop-end.php');
}

/**
 * Show no garments found message
 */
function gc_no_garments_found() {
    gc_get_template('loop/no-garments-found.php');
}

/**
 * Single Garment Functions
 */
function gc_before_single_garment() {
    gc_get_template('single-garment/before-single-garment.php');
}

function gc_after_single_garment() {
    gc_get_template('single-garment/after-single-garment.php');
}

function gc_template_single_title() {
    gc_get_template('single-garment/title.php');
}

function gc_template_single_price() {
    gc_get_template('single-garment/price.php');
}

function gc_template_single_excerpt() {
    gc_get_template('single-garment/excerpt.php');
}

function gc_template_single_meta() {
    gc_get_template('single-garment/meta.php');
}

function gc_template_single_customizer() {
    gc_get_template('single-garment/customizer.php');
}

/**
 * Customizer Interface Functions
 */
function gc_customizer_wrapper_start() {
    gc_get_template('customizer/wrapper-start.php');
}

function gc_customizer_wrapper_end() {
    gc_get_template('customizer/wrapper-end.php');
}

function gc_customizer_preview() {
    gc_get_template('customizer/preview.php');
}

function gc_customizer_controls() {
    gc_get_template('customizer/controls.php');
}

function gc_customizer_actions() {
    gc_get_template('customizer/actions.php');
}

/**
 * Layers Panel Functions
 */
function gc_customizer_layers_list() {
    gc_get_template('customizer/layers/list.php');
}

function gc_customizer_layer_controls() {
    gc_get_template('customizer/layers/controls.php');
}

/**
 * Colors Panel Functions
 */
function gc_customizer_color_picker() {
    gc_get_template('customizer/colors/picker.php');
}

function gc_customizer_color_presets() {
    gc_get_template('customizer/colors/presets.php');
}

/**
 * Text Panel Functions
 */
function gc_customizer_text_input() {
    gc_get_template('customizer/text/input.php');
}

function gc_customizer_text_controls() {
    gc_get_template('customizer/text/controls.php');
}

/**
 * Logo Panel Functions
 */
function gc_customizer_logo_upload() {
    gc_get_template('customizer/logo/upload.php');
}

function gc_customizer_logo_controls() {
    gc_get_template('customizer/logo/controls.php');
}

/**
 * Cart Functions
 */
function gc_cart_wrapper_start() {
    gc_get_template('cart/wrapper-start.php');
}

function gc_cart_wrapper_end() {
    gc_get_template('cart/wrapper-end.php');
}

function gc_cart_items() {
    gc_get_template('cart/cart-items.php');
}

function gc_cart_totals() {
    gc_get_template('cart/cart-totals.php');
}

function gc_cart_actions() {
    gc_get_template('cart/cart-actions.php');
}

/**
 * Request for Quote Functions
 */
function gc_rfq_form_wrapper_start() {
    gc_get_template('rfq/wrapper-start.php');
}

function gc_rfq_form_wrapper_end() {
    gc_get_template('rfq/wrapper-end.php');
}

function gc_rfq_form_fields() {
    gc_get_template('rfq/form-fields.php');
}

function gc_rfq_form_summary() {
    gc_get_template('rfq/form-summary.php');
}

function gc_rfq_form_submit() {
    gc_get_template('rfq/form-submit.php');
}

/**
 * Archive Functions
 */
function gc_garment_archive_description() {
    gc_get_template('archive/description.php');
}

function gc_garment_archive_ordering() {
    gc_get_template('archive/ordering.php');
}

function gc_garment_archive_filters() {
    gc_get_template('archive/filters.php');
}

/**
 * Sidebar Functions
 */
function gc_get_sidebar() {
    gc_get_template('global/sidebar.php');
}

/**
 * Account Functions
 */
function gc_account_navigation() {
    gc_get_template('myaccount/navigation.php');
}

function gc_account_content() {
    gc_get_template('myaccount/content.php');
}

function gc_account_orders() {
    gc_get_template('myaccount/orders.php');
}

function gc_account_quotes() {
    gc_get_template('myaccount/quotes.php');
}

function gc_account_edit_account() {
    gc_get_template('myaccount/form-edit-account.php');
}

/**
 * Auth Functions
 */
function gc_login_wrapper_start() {
    gc_get_template('auth/wrapper-start.php');
}

function gc_login_wrapper_end() {
    gc_get_template('auth/wrapper-end.php');
}

/**
 * Structured Data
 */
function gc_structured_data() {
    gc_get_template('single-garment/structured-data.php');
}
