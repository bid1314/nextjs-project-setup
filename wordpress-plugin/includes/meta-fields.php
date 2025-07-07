<?php
/**
 * Meta Fields Handler
 *
 * Handles registration and management of custom meta fields for garments.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register meta boxes for garments
 */
function gc_add_garment_meta_boxes() {
    add_meta_box(
        'gc_garment_options',
        __('Garment Options', 'garment-customizer'),
        'gc_render_garment_options_meta_box',
        'garment',
        'normal',
        'high'
    );

    add_meta_box(
        'gc_customization_areas',
        __('Customization Areas', 'garment-customizer'),
        'gc_render_customization_areas_meta_box',
        'garment',
        'normal',
        'high'
    );

    add_meta_box(
        'gc_pricing_options',
        __('Pricing Options', 'garment-customizer'),
        'gc_render_pricing_options_meta_box',
        'garment',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'gc_add_garment_meta_boxes');

/**
 * Render garment options meta box
 *
 * @param WP_Post $post Post object
 */
function gc_render_garment_options_meta_box($post) {
    // Add nonce for security
    wp_nonce_field('gc_garment_options_nonce', 'gc_garment_options_nonce');

    // Get saved values
    $base_image = get_post_meta($post->ID, 'gc_base_image', true);
    $available_sizes = get_post_meta($post->ID, 'gc_available_sizes', true) ?: array();
    $default_color = get_post_meta($post->ID, 'gc_default_color', true);

    // Size options
    $size_options = array(
        'XS' => __('Extra Small', 'garment-customizer'),
        'S'  => __('Small', 'garment-customizer'),
        'M'  => __('Medium', 'garment-customizer'),
        'L'  => __('Large', 'garment-customizer'),
        'XL' => __('Extra Large', 'garment-customizer'),
        '2XL' => __('2X Large', 'garment-customizer'),
        '3XL' => __('3X Large', 'garment-customizer'),
    );
    ?>
    <div class="gc-meta-box gc-meta-box--options">
        <p class="gc-field">
            <label for="gc_base_image"><?php esc_html_e('Base Image', 'garment-customizer'); ?></label>
            <input type="text" 
                   id="gc_base_image" 
                   name="gc_base_image" 
                   value="<?php echo esc_attr($base_image); ?>" 
                   class="large-text">
            <button type="button" class="button gc-upload-image">
                <?php esc_html_e('Upload Image', 'garment-customizer'); ?>
            </button>
        </p>

        <p class="gc-field">
            <label><?php esc_html_e('Available Sizes', 'garment-customizer'); ?></label>
            <span class="gc-checkbox-group">
                <?php foreach ($size_options as $value => $label) : ?>
                    <label class="gc-checkbox">
                        <input type="checkbox" 
                               name="gc_available_sizes[]" 
                               value="<?php echo esc_attr($value); ?>"
                               <?php checked(in_array($value, $available_sizes)); ?>>
                        <?php echo esc_html($label); ?>
                    </label>
                <?php endforeach; ?>
            </span>
        </p>

        <p class="gc-field">
            <label for="gc_default_color"><?php esc_html_e('Default Color', 'garment-customizer'); ?></label>
            <input type="color" 
                   id="gc_default_color" 
                   name="gc_default_color" 
                   value="<?php echo esc_attr($default_color); ?>">
        </p>
    </div>
    <?php
}

/**
 * Render customization areas meta box
 *
 * @param WP_Post $post Post object
 */
function gc_render_customization_areas_meta_box($post) {
    // Get saved layers
    $layers = get_post_meta($post->ID, 'gc_layers', true) ?: array();
    ?>
    <div class="gc-meta-box gc-meta-box--layers">
        <div class="gc-layers-list" data-layers="<?php echo esc_attr(wp_json_encode($layers)); ?>">
            <!-- Layers will be rendered here via JavaScript -->
        </div>
        
        <div class="gc-layers-actions">
            <button type="button" class="button gc-add-layer" data-type="color">
                <?php esc_html_e('Add Color Layer', 'garment-customizer'); ?>
            </button>
            <button type="button" class="button gc-add-layer" data-type="text">
                <?php esc_html_e('Add Text Layer', 'garment-customizer'); ?>
            </button>
            <button type="button" class="button gc-add-layer" data-type="logo">
                <?php esc_html_e('Add Logo Layer', 'garment-customizer'); ?>
            </button>
        </div>

        <input type="hidden" 
               name="gc_layers" 
               id="gc_layers" 
               value="<?php echo esc_attr(wp_json_encode($layers)); ?>">
    </div>
    <?php
}

/**
 * Render pricing options meta box
 *
 * @param WP_Post $post Post object
 */
function gc_render_pricing_options_meta_box($post) {
    // Get saved values
    $base_price = get_post_meta($post->ID, 'gc_base_price', true);
    $color_price = get_post_meta($post->ID, 'gc_color_price', true);
    $text_price = get_post_meta($post->ID, 'gc_text_price', true);
    $logo_price = get_post_meta($post->ID, 'gc_logo_price', true);
    ?>
    <div class="gc-meta-box gc-meta-box--pricing">
        <p class="gc-field">
            <label for="gc_base_price"><?php esc_html_e('Base Price', 'garment-customizer'); ?></label>
            <input type="number" 
                   id="gc_base_price" 
                   name="gc_base_price" 
                   value="<?php echo esc_attr($base_price); ?>"
                   step="0.01"
                   min="0">
        </p>

        <p class="gc-field">
            <label for="gc_color_price"><?php esc_html_e('Color Customization Price', 'garment-customizer'); ?></label>
            <input type="number" 
                   id="gc_color_price" 
                   name="gc_color_price" 
                   value="<?php echo esc_attr($color_price); ?>"
                   step="0.01"
                   min="0">
        </p>

        <p class="gc-field">
            <label for="gc_text_price"><?php esc_html_e('Text Customization Price', 'garment-customizer'); ?></label>
            <input type="number" 
                   id="gc_text_price" 
                   name="gc_text_price" 
                   value="<?php echo esc_attr($text_price); ?>"
                   step="0.01"
                   min="0">
        </p>

        <p class="gc-field">
            <label for="gc_logo_price"><?php esc_html_e('Logo Customization Price', 'garment-customizer'); ?></label>
            <input type="number" 
                   id="gc_logo_price" 
                   name="gc_logo_price" 
                   value="<?php echo esc_attr($logo_price); ?>"
                   step="0.01"
                   min="0">
        </p>
    </div>
    <?php
}

/**
 * Save garment meta box data
 *
 * @param int $post_id Post ID
 */
function gc_save_garment_meta_boxes($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['gc_garment_options_nonce'])) {
        return;
    }

    // Verify that the nonce is valid
    if (!wp_verify_nonce($_POST['gc_garment_options_nonce'], 'gc_garment_options_nonce')) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save base image
    if (isset($_POST['gc_base_image'])) {
        update_post_meta($post_id, 'gc_base_image', sanitize_text_field($_POST['gc_base_image']));
    }

    // Save available sizes
    if (isset($_POST['gc_available_sizes'])) {
        $sizes = array_map('sanitize_text_field', $_POST['gc_available_sizes']);
        update_post_meta($post_id, 'gc_available_sizes', $sizes);
    }

    // Save default color
    if (isset($_POST['gc_default_color'])) {
        update_post_meta($post_id, 'gc_default_color', sanitize_hex_color($_POST['gc_default_color']));
    }

    // Save layers
    if (isset($_POST['gc_layers'])) {
        $layers = json_decode(stripslashes($_POST['gc_layers']), true);
        if (is_array($layers)) {
            update_post_meta($post_id, 'gc_layers', $layers);
        }
    }

    // Save pricing options
    $pricing_fields = array(
        'gc_base_price',
        'gc_color_price',
        'gc_text_price',
        'gc_logo_price'
    );

    foreach ($pricing_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta(
                $post_id,
                $field,
                filter_var($_POST[$field], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
            );
        }
    }
}
add_action('save_post_garment', 'gc_save_garment_meta_boxes');
