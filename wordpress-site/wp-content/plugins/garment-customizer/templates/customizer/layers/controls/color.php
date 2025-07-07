<?php
/**
 * Color Layer Controls Template
 *
 * This template displays the controls for a color layer.
 *
 * @package GarmentCustomizer
 * @var array $layer The layer data
 */

if (!defined('ABSPATH')) {
    exit;
}

$garment_id = get_the_ID();
$colors = get_post_meta($garment_id, 'gc_colors', true) ?: array();
$current_color = isset($layer['color']) ? $layer['color'] : '';
?>

<div class="gc-layer-controls gc-layer-controls--color">
    <div class="gc-color-picker">
        <label class="gc-color-picker__label">
            <?php esc_html_e('Select Color', 'garment-customizer'); ?>
        </label>
        
        <div class="gc-color-picker__options">
            <?php foreach ($colors as $color) : ?>
                <button type="button" 
                        class="gc-color-picker__option <?php echo esc_attr($current_color === $color ? 'is-active' : ''); ?>"
                        data-color="<?php echo esc_attr($color); ?>"
                        style="background-color: <?php echo esc_attr($color); ?>"
                        aria-label="<?php echo esc_attr(sprintf(__('Select color: %s', 'garment-customizer'), $color)); ?>"
                        <?php echo $current_color === $color ? 'aria-pressed="true"' : ''; ?>>
                    <span class="screen-reader-text"><?php echo esc_html($color); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="gc-layer-controls__options">
        <div class="gc-layer-control">
            <label class="gc-layer-control__label" for="layer-opacity-<?php echo esc_attr($layer['id']); ?>">
                <?php esc_html_e('Opacity', 'garment-customizer'); ?>
            </label>
            <input type="range" 
                   class="gc-layer-control__range" 
                   id="layer-opacity-<?php echo esc_attr($layer['id']); ?>"
                   min="0" 
                   max="100" 
                   value="<?php echo esc_attr(isset($layer['opacity']) ? $layer['opacity'] * 100 : 100); ?>"
                   data-property="opacity">
            <output class="gc-layer-control__value">
                <?php echo esc_html(isset($layer['opacity']) ? $layer['opacity'] * 100 : 100); ?>%
            </output>
        </div>

        <div class="gc-layer-control">
            <label class="gc-layer-control__label" for="layer-blend-<?php echo esc_attr($layer['id']); ?>">
                <?php esc_html_e('Blend Mode', 'garment-customizer'); ?>
            </label>
            <select class="gc-layer-control__select" 
                    id="layer-blend-<?php echo esc_attr($layer['id']); ?>"
                    data-property="blend-mode">
                <option value="normal" <?php selected(isset($layer['blend_mode']) ? $layer['blend_mode'] : 'normal', 'normal'); ?>>
                    <?php esc_html_e('Normal', 'garment-customizer'); ?>
                </option>
                <option value="multiply" <?php selected(isset($layer['blend_mode']) ? $layer['blend_mode'] : 'normal', 'multiply'); ?>>
                    <?php esc_html_e('Multiply', 'garment-customizer'); ?>
                </option>
                <option value="screen" <?php selected(isset($layer['blend_mode']) ? $layer['blend_mode'] : 'normal', 'screen'); ?>>
                    <?php esc_html_e('Screen', 'garment-customizer'); ?>
                </option>
                <option value="overlay" <?php selected(isset($layer['blend_mode']) ? $layer['blend_mode'] : 'normal', 'overlay'); ?>>
                    <?php esc_html_e('Overlay', 'garment-customizer'); ?>
                </option>
            </select>
        </div>
    </div>
</div>
