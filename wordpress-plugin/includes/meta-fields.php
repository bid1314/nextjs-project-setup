<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Ensure this file is called by WordPress
if ( ! function_exists( 'add_action' ) ) {
    exit;
}

/**
 * Register meta fields for garment post type
 */
function gc_register_garment_meta_fields() {
    // Layers meta
    register_post_meta('garment', 'gc_layers', array(
        'type' => 'array',
        'description' => 'Garment customization layers',
        'single' => true,
        'show_in_rest' => true,
        'default' => array(),
    ));

    // Colors meta
    register_post_meta('garment', 'gc_colors', array(
        'type' => 'array',
        'description' => 'Available colors for the garment',
        'single' => true,
        'show_in_rest' => true,
        'default' => array(),
    ));

    // Customization state meta
    register_post_meta('garment', 'gc_customization_state', array(
        'type' => 'object',
        'description' => 'Current customization state',
        'single' => true,
        'show_in_rest' => true,
        'default' => array(
            'color' => '',
            'text' => '',
            'logo' => null,
            'size' => 'M'
        ),
    ));

    // Base price meta
    register_post_meta('garment', 'gc_base_price', array(
        'type' => 'number',
        'description' => 'Base price of the garment',
        'single' => true,
        'show_in_rest' => true,
        'default' => 0,
    ));
}
add_action('init', 'gc_register_garment_meta_fields');

/**
 * Add meta boxes to garment post type
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
}
add_action('add_meta_boxes', 'gc_add_garment_meta_boxes');

/**
 * Render meta box for garment options
 */
function gc_render_garment_options_meta_box($post) {
    // Get current values
    $layers = get_post_meta($post->ID, 'gc_layers', true) ?: array();
    $colors = get_post_meta($post->ID, 'gc_colors', true) ?: array();
    $base_price = get_post_meta($post->ID, 'gc_base_price', true) ?: 0;

    // Add nonce for security
    wp_nonce_field('gc_save_garment_options', 'gc_garment_options_nonce');
    ?>
    <div class="gc-meta-box-container">
        <div class="gc-meta-section">
            <h4><?php _e('Base Price', 'garment-customizer'); ?></h4>
            <input 
                type="number" 
                name="gc_base_price" 
                value="<?php echo esc_attr($base_price); ?>" 
                step="0.01" 
                min="0"
            />
        </div>

        <div class="gc-meta-section">
            <h4><?php _e('Available Colors', 'garment-customizer'); ?></h4>
            <div id="gc-colors-container">
                <?php foreach ($colors as $index => $color) : ?>
                <div class="gc-color-row">
                    <input 
                        type="text" 
                        name="gc_colors[]" 
                        value="<?php echo esc_attr($color); ?>" 
                        class="gc-color-picker"
                    />
                    <button type="button" class="button gc-remove-color">
                        <?php _e('Remove', 'garment-customizer'); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button gc-add-color">
                <?php _e('Add Color', 'garment-customizer'); ?>
            </button>
        </div>

        <div class="gc-meta-section">
            <h4><?php _e('Customization Layers', 'garment-customizer'); ?></h4>
            <div id="gc-layers-container">
                <?php foreach ($layers as $index => $layer) : ?>
                <div class="gc-layer-row">
                    <input 
                        type="text" 
                        name="gc_layers[<?php echo $index; ?>][name]" 
                        value="<?php echo esc_attr($layer['name']); ?>"
                        placeholder="<?php esc_attr_e('Layer Name', 'garment-customizer'); ?>"
                    />
                    <select name="gc_layers[<?php echo $index; ?>][type]">
                        <option value="color" <?php selected($layer['type'], 'color'); ?>>
                            <?php _e('Color', 'garment-customizer'); ?>
                        </option>
                        <option value="text" <?php selected($layer['type'], 'text'); ?>>
                            <?php _e('Text', 'garment-customizer'); ?>
                        </option>
                        <option value="logo" <?php selected($layer['type'], 'logo'); ?>>
                            <?php _e('Logo', 'garment-customizer'); ?>
                        </option>
                    </select>
                    <button type="button" class="button gc-remove-layer">
                        <?php _e('Remove', 'garment-customizer'); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button gc-add-layer">
                <?php _e('Add Layer', 'garment-customizer'); ?>
            </button>
        </div>
    </div>

    <style>
        .gc-meta-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
        }
        .gc-color-row,
        .gc-layer-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .gc-meta-section h4 {
            margin-top: 0;
            margin-bottom: 15px;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Initialize color pickers
        $('.gc-color-picker').wpColorPicker();

        // Add color
        $('.gc-add-color').on('click', function() {
            const container = $('#gc-colors-container');
            const row = $('<div class="gc-color-row"></div>');
            const input = $('<input type="text" name="gc_colors[]" class="gc-color-picker" />');
            const removeBtn = $('<button type="button" class="button gc-remove-color">Remove</button>');
            
            row.append(input, removeBtn);
            container.append(row);
            input.wpColorPicker();
        });

        // Remove color
        $(document).on('click', '.gc-remove-color', function() {
            $(this).closest('.gc-color-row').remove();
        });

        // Add layer
        $('.gc-add-layer').on('click', function() {
            const container = $('#gc-layers-container');
            const index = container.children().length;
            const row = `
                <div class="gc-layer-row">
                    <input 
                        type="text" 
                        name="gc_layers[${index}][name]" 
                        placeholder="Layer Name"
                    />
                    <select name="gc_layers[${index}][type]">
                        <option value="color">Color</option>
                        <option value="text">Text</option>
                        <option value="logo">Logo</option>
                    </select>
                    <button type="button" class="button gc-remove-layer">Remove</button>
                </div>
            `;
            container.append(row);
        });

        // Remove layer
        $(document).on('click', '.gc-remove-layer', function() {
            $(this).closest('.gc-layer-row').remove();
        });
    });
    </script>
    <?php
}

/**
 * Save garment options meta box data
 */
function gc_save_garment_options($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['gc_garment_options_nonce'])) {
        return;
    }

    // Verify that the nonce is valid
    if (!wp_verify_nonce($_POST['gc_garment_options_nonce'], 'gc_save_garment_options')) {
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

    // Save base price
    if (isset($_POST['gc_base_price'])) {
        update_post_meta(
            $post_id,
            'gc_base_price',
            floatval($_POST['gc_base_price'])
        );
    }

    // Save colors
    if (isset($_POST['gc_colors'])) {
        $colors = array_map('sanitize_text_field', $_POST['gc_colors']);
        $colors = array_filter($colors); // Remove empty values
        update_post_meta($post_id, 'gc_colors', $colors);
    }

    // Save layers
    if (isset($_POST['gc_layers'])) {
        $layers = array();
        foreach ($_POST['gc_layers'] as $layer) {
            if (!empty($layer['name'])) {
                $layers[] = array(
                    'name' => sanitize_text_field($layer['name']),
                    'type' => sanitize_text_field($layer['type'])
                );
            }
        }
        update_post_meta($post_id, 'gc_layers', $layers);
    }
}
add_action('save_post_garment', 'gc_save_garment_options');
