<?php
/**
 * Template for the garment customizer interface
 *
 * @package GarmentCustomizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include WordPress core
require_once(ABSPATH . 'wp-includes/post.php');
require_once(ABSPATH . 'wp-includes/post-template.php');
require_once(ABSPATH . 'wp-includes/formatting.php');
require_once(ABSPATH . 'wp-includes/capabilities.php');

// Get the garment data
global $post;
$garment_id = $post->ID;
$layers = get_post_meta($garment_id, 'gc_layers', true) ?: array();
$colors = get_post_meta($garment_id, 'gc_colors', true) ?: array();
$base_price = get_post_meta($garment_id, 'gc_base_price', true) ?: 0;

// Enqueue required scripts and styles
wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
wp_enqueue_style('gc-customizer-style', GC_PLUGIN_URL . 'assets/css/customizer-native.css', array(), GC_PLUGIN_VERSION);

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-draggable');
wp_enqueue_script('jquery-ui-resizable');
wp_enqueue_script('gc-customizer-script', GC_PLUGIN_URL . 'assets/js/customizer-native.js', array('jquery', 'jquery-ui-draggable', 'jquery-ui-resizable'), GC_PLUGIN_VERSION, true);

// Localize script
wp_localize_script('gc-customizer-script', 'gcCustomizer', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('gc_customizer_nonce'),
    'garmentId' => $garment_id,
    'strings' => array(
        'saveError' => __('Failed to save customization', 'garment-customizer'),
        'uploadError' => __('Failed to upload logo', 'garment-customizer'),
        'invalidLogo' => __('Invalid logo file', 'garment-customizer')
    )
));
?>

<div class="gc-customizer-container">
    <!-- Preview Section -->
    <div class="gc-preview-section">
        <div class="gc-preview-canvas">
            <?php foreach ($layers as $index => $layer) : ?>
                <div class="gc-layer" data-layer-id="<?php echo esc_attr($index); ?>" data-layer-type="<?php echo esc_attr($layer['type']); ?>">
                    <?php if ($layer['type'] === 'text') : ?>
                        <div class="gc-text-layer" contenteditable="true"></div>
                    <?php elseif ($layer['type'] === 'logo') : ?>
                        <div class="gc-logo-layer"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Controls Section -->
    <div class="gc-controls-section">
        <!-- Layers Panel -->
        <div class="gc-panel gc-layers-panel">
            <h3><?php esc_html_e('Layers', 'garment-customizer'); ?></h3>
            <div class="gc-layers-list">
                <?php foreach ($layers as $index => $layer) : ?>
                    <div class="gc-layer-control" data-layer-id="<?php echo esc_attr($index); ?>">
                        <span class="gc-layer-name"><?php echo esc_html($layer['name']); ?></span>
                        <?php if ($layer['type'] === 'color') : ?>
                            <div class="gc-color-picker">
                                <?php foreach ($colors as $color) : ?>
                                    <div class="gc-color-option" 
                                         data-color="<?php echo esc_attr($color); ?>" 
                                         style="background-color: <?php echo esc_attr($color); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($layer['type'] === 'text') : ?>
                            <input type="text" class="gc-text-input" placeholder="<?php esc_attr_e('Enter text', 'garment-customizer'); ?>">
                            <div class="gc-text-controls">
                                <select class="gc-font-family">
                                    <option value="Arial">Arial</option>
                                    <option value="Helvetica">Helvetica</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                </select>
                                <input type="number" class="gc-font-size" min="8" max="72" value="16">
                                <div class="gc-text-align">
                                    <button type="button" data-align="left">⌗</button>
                                    <button type="button" data-align="center">≡</button>
                                    <button type="button" data-align="right">⌦</button>
                                </div>
                            </div>
                        <?php elseif ($layer['type'] === 'logo') : ?>
                            <div class="gc-logo-upload">
                                <input type="file" accept="image/*" class="gc-logo-input">
                                <button type="button" class="gc-upload-btn"><?php esc_html_e('Upload Logo', 'garment-customizer'); ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Price Panel -->
        <div class="gc-panel gc-price-panel">
            <div class="gc-price">
                <span class="gc-price-label"><?php esc_html_e('Total Price:', 'garment-customizer'); ?></span>
                <span class="gc-price-amount"><?php echo esc_html(number_format($base_price, 2)); ?></span>
            </div>
            <button type="button" class="gc-add-to-cart"><?php esc_html_e('Add to Cart', 'garment-customizer'); ?></button>
            <button type="button" class="gc-request-quote"><?php esc_html_e('Request Quote', 'garment-customizer'); ?></button>
        </div>
    </div>
</div>

<style>
.gc-customizer-container {
    display: flex;
    gap: 2rem;
    padding: 2rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.gc-preview-section {
    flex: 1;
    min-height: 500px;
    border: 1px solid #ddd;
    border-radius: 4px;
    position: relative;
}

.gc-preview-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.gc-controls-section {
    width: 300px;
}

.gc-panel {
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.gc-layer-control {
    margin-bottom: 1rem;
    padding: 1rem;
    background: #fff;
    border: 1px solid #eee;
    border-radius: 4px;
}

.gc-color-picker {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.gc-color-option {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px #ddd;
}

.gc-color-option:hover {
    transform: scale(1.1);
}

.gc-text-controls {
    margin-top: 0.5rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.gc-logo-upload {
    margin-top: 0.5rem;
}

.gc-price-panel {
    text-align: center;
}

.gc-price {
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.gc-add-to-cart,
.gc-request-quote {
    width: 100%;
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.gc-add-to-cart {
    background: #4CAF50;
    color: white;
}

.gc-request-quote {
    background: #2196F3;
    color: white;
}

@media (max-width: 768px) {
    .gc-customizer-container {
        flex-direction: column;
    }

    .gc-controls-section {
        width: 100%;
    }
}
</style>
