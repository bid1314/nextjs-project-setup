<?php
/**
 * Plugin Name: Garment Customizer
 * Plugin URI: https://example.com/garment-customizer
 * Description: A WordPress plugin for customizing garments with color, text, and logo options.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: garment-customizer
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.2
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include the main loader class
if (!class_exists('GC_Loader')) {
    include_once dirname(__FILE__) . '/includes/class-gc-loader.php';
}

/**
 * Returns the main instance of GC_Loader.
 *
 * @return GC_Loader
 */
function GC() {
    return GC_Loader::instance();
}

// Global for backwards compatibility
$GLOBALS['garment_customizer'] = GC();

// Initialize the plugin
GC();
