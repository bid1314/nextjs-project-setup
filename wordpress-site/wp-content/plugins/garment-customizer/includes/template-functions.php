<?php
/**
 * Template Functions
 *
 * Functions for the templating system.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get template part
 *
 * @param string $slug Template slug
 * @param string $name Template name (optional)
 * @param array  $args Additional arguments
 */
function gc_get_template_part($slug, $name = '', $args = array()) {
    $template = '';

    // Look in yourtheme/garment-customizer/slug-name.php and yourtheme/garment-customizer/slug.php
    if ($name) {
        $template = locate_template(array(
            "garment-customizer/{$slug}-{$name}.php",
            "garment-customizer/{$slug}.php"
        ));
    }

    // Get default slug-name.php
    if (!$template && $name && file_exists(dirname(dirname(__FILE__)) . "/templates/{$slug}-{$name}.php")) {
        $template = dirname(dirname(__FILE__)) . "/templates/{$slug}-{$name}.php";
    }

    // If template file doesn't exist, look for the slug.php
    if (!$template && file_exists(dirname(dirname(__FILE__)) . "/templates/{$slug}.php")) {
        $template = dirname(dirname(__FILE__)) . "/templates/{$slug}.php";
    }

    // Allow 3rd party plugins to filter template file from their plugin
    $template = apply_filters('gc_get_template_part', $template, $slug, $name);

    if ($template) {
        load_template($template, false, $args);
    }
}

/**
 * Get other templates passing attributes and including the file
 *
 * @param string $template_name Template name
 * @param array  $args          Arguments
 * @param string $template_path Template path (optional)
 * @param string $default_path  Default path (optional)
 */
function gc_get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
    if ($args && is_array($args)) {
        extract($args);
    }

    $located = gc_locate_template($template_name, $template_path, $default_path);

    if (!file_exists($located)) {
        _doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'garment-customizer'), '<code>' . $located . '</code>'), '1.0.0');
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin
    $located = apply_filters('gc_get_template', $located, $template_name, $args, $template_path, $default_path);

    do_action('gc_before_template_part', $template_name, $template_path, $located, $args);

    include $located;

    do_action('gc_after_template_part', $template_name, $template_path, $located, $args);
}

/**
 * Locate a template and return the path for inclusion
 *
 * @param string $template_name Template name
 * @param string $template_path Template path (optional)
 * @param string $default_path  Default path (optional)
 * @return string
 */
function gc_locate_template($template_name, $template_path = '', $default_path = '') {
    if (!$template_path) {
        $template_path = 'garment-customizer/';
    }

    if (!$default_path) {
        $default_path = dirname(dirname(__FILE__)) . '/templates/';
    }

    // Look within passed path within the theme - this is priority
    $template = locate_template(array(
        trailingslashit($template_path) . $template_name,
        $template_name,
    ));

    // Get default template
    if (!$template) {
        $template = $default_path . $template_name;
    }

    // Return what we found
    return apply_filters('gc_locate_template', $template, $template_name, $template_path);
}

/**
 * Get template loader
 *
 * @param string $template Template to load
 * @return string
 */
function gc_template_loader($template) {
    if (is_singular('garment')) {
        $template = gc_locate_template('single-garment.php');
    } elseif (is_post_type_archive('garment') || is_tax('garment_category')) {
        $template = gc_locate_template('archive-garment.php');
    }

    return $template;
}
add_filter('template_include', 'gc_template_loader');

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file
 *
 * @param string $template_name Template name
 * @param array  $args          Arguments
 */
function gc_get_template_html($template_name, $args = array()) {
    ob_start();
    gc_get_template($template_name, $args);
    return ob_get_clean();
}

/**
 * Add body classes for GC pages
 *
 * @param array $classes Body classes
 * @return array
 */
function gc_body_class($classes) {
    if (is_singular('garment')) {
        $classes[] = 'gc-garment';
        $classes[] = 'gc-garment-' . get_the_ID();
    } elseif (is_post_type_archive('garment') || is_tax('garment_category')) {
        $classes[] = 'gc-archive';
    }

    return $classes;
}
add_filter('body_class', 'gc_body_class');
