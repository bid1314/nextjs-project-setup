<?php
/**
 * Customizer Preview Template
 *
 * This template displays the live preview of the garment with customizable layers.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

$garment_id = get_the_ID();
$layers = get_post_meta($garment_id, 'gc_layers', true) ?: array();
$base_image = get_post_meta($garment_id, 'gc_base_image', true);
$preview_size = apply_filters('gc_preview_size', array(
    'width' => 600,
    'height' => 600
));
?>

<div class="gc-preview" 
     data-garment-id="<?php echo esc_attr($garment_id); ?>"
     style="width: <?php echo esc_attr($preview_size['width']); ?>px; height: <?php echo esc_attr($preview_size['height']); ?>px;">
    
    <!-- Base Garment Image -->
    <div class="gc-preview__base">
        <?php if ($base_image) : ?>
            <img src="<?php echo esc_url($base_image); ?>"
                 alt="<?php echo esc_attr(get_the_title($garment_id)); ?>"
                 class="gc-preview__image"
                 width="<?php echo esc_attr($preview_size['width']); ?>"
                 height="<?php echo esc_attr($preview_size['height']); ?>">
        <?php else : ?>
            <div class="gc-preview__placeholder">
                <?php esc_html_e('No base image set', 'garment-customizer'); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Customizable Layers -->
    <div class="gc-preview__layers">
        <?php foreach ($layers as $index => $layer) : ?>
            <div class="gc-preview__layer"
                 data-layer-id="<?php echo esc_attr($index); ?>"
                 data-layer-type="<?php echo esc_attr($layer['type']); ?>"
                 style="<?php echo esc_attr(gc_get_layer_style($layer)); ?>">
                
                <?php if ($layer['type'] === 'text') : ?>
                    <div class="gc-preview__text" contenteditable="true">
                        <?php echo esc_html($layer['text'] ?? ''); ?>
                    </div>
                <?php elseif ($layer['type'] === 'logo' && !empty($layer['logo_url'])) : ?>
                    <img src="<?php echo esc_url($layer['logo_url']); ?>"
                         alt="<?php echo esc_attr(__('Custom Logo', 'garment-customizer')); ?>"
                         class="gc-preview__logo">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Preview Controls -->
    <div class="gc-preview__controls">
        <button type="button" 
                class="gc-preview__zoom"
                aria-label="<?php esc_attr_e('Toggle zoom view', 'garment-customizer'); ?>">
            <svg class="gc-icon" width="24" height="24" viewBox="0 0 24 24">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </button>

        <button type="button" 
                class="gc-preview__reset"
                aria-label="<?php esc_attr_e('Reset all customizations', 'garment-customizer'); ?>">
            <svg class="gc-icon" width="24" height="24" viewBox="0 0 24 24">
                <path d="M12 5V2L8 6l4 4V7c3.31 0 6 2.69 6 6 0 2.97-2.17 5.43-5 5.91v2.02c3.95-.49 7-3.85 7-7.93 0-4.42-3.58-8-8-8zm-6 8c0-1.65.67-3.15 1.76-4.24L6.34 7.34C4.9 8.79 4 10.79 4 13c0 4.08 3.05 7.44 7 7.93v-2.02c-2.83-.48-5-2.94-5-5.91z"/>
            </svg>
        </button>
    </div>

    <!-- Zoom View -->
    <div class="gc-preview__zoom-view" hidden>
        <div class="gc-preview__zoom-content">
            <!-- Content will be cloned from preview -->
        </div>
    </div>
</div>

<?php
/**
 * Get layer style string
 *
 * @param array $layer Layer data
 * @return string
 */
function gc_get_layer_style($layer) {
    $styles = array();

    if (!empty($layer['position'])) {
        $styles[] = sprintf('left: %s%%', esc_attr($layer['position']['x']));
        $styles[] = sprintf('top: %s%%', esc_attr($layer['position']['y']));
    }

    if (!empty($layer['scale'])) {
        $styles[] = sprintf('transform: scale(%s)', esc_attr($layer['scale']));
    }

    if (!empty($layer['rotation'])) {
        $styles[] = sprintf('transform: rotate(%sdeg)', esc_attr($layer['rotation']));
    }

    if (!empty($layer['opacity'])) {
        $styles[] = sprintf('opacity: %s', esc_attr($layer['opacity']));
    }

    if ($layer['type'] === 'color' && !empty($layer['color'])) {
        $styles[] = sprintf('background-color: %s', esc_attr($layer['color']));
    }

    if ($layer['type'] === 'text') {
        if (!empty($layer['font_family'])) {
            $styles[] = sprintf('font-family: %s', esc_attr($layer['font_family']));
        }
        if (!empty($layer['font_size'])) {
            $styles[] = sprintf('font-size: %spx', esc_attr($layer['font_size']));
        }
        if (!empty($layer['font_weight'])) {
            $styles[] = sprintf('font-weight: %s', esc_attr($layer['font_weight']));
        }
        if (!empty($layer['text_align'])) {
            $styles[] = sprintf('text-align: %s', esc_attr($layer['text_align']));
        }
        if (!empty($layer['text_color'])) {
            $styles[] = sprintf('color: %s', esc_attr($layer['text_color']));
        }
    }

    return implode('; ', $styles);
}
