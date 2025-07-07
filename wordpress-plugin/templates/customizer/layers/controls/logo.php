<?php
/**
 * Logo Layer Controls Template
 *
 * This template displays the controls for a logo layer.
 *
 * @package GarmentCustomizer
 * @var array $layer The layer data
 */

if (!defined('ABSPATH')) {
    exit;
}

$logo_url = isset($layer['logo_url']) ? $layer['logo_url'] : '';
$max_size = apply_filters('gc_max_logo_size', 5 * 1024 * 1024); // 5MB default
$allowed_types = apply_filters('gc_allowed_logo_types', array('image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'));
?>

<div class="gc-layer-controls gc-layer-controls--logo">
    <div class="gc-logo-upload">
        <div class="gc-logo-upload__preview <?php echo $logo_url ? 'has-logo' : ''; ?>"
             style="<?php echo $logo_url ? sprintf('background-image: url(%s);', esc_url($logo_url)) : ''; ?>">
            <?php if (!$logo_url) : ?>
                <span class="gc-logo-upload__placeholder">
                    <?php esc_html_e('No logo uploaded', 'garment-customizer'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="gc-logo-upload__controls">
            <input type="file" 
                   class="gc-logo-upload__input" 
                   id="layer-logo-<?php echo esc_attr($layer['id']); ?>"
                   accept="<?php echo esc_attr(implode(',', $allowed_types)); ?>"
                   data-max-size="<?php echo esc_attr($max_size); ?>"
                   hidden>
            
            <button type="button" 
                    class="gc-button gc-logo-upload__button"
                    data-action="upload">
                <?php echo $logo_url ? esc_html__('Change Logo', 'garment-customizer') : esc_html__('Upload Logo', 'garment-customizer'); ?>
            </button>

            <?php if ($logo_url) : ?>
                <button type="button" 
                        class="gc-button gc-button--link gc-logo-upload__remove"
                        data-action="remove">
                    <?php esc_html_e('Remove Logo', 'garment-customizer'); ?>
                </button>
            <?php endif; ?>
        </div>

        <p class="gc-logo-upload__help">
            <?php
            printf(
                /* translators: %s: maximum upload size */
                esc_html__('Maximum upload size: %s. Supported formats: JPG, PNG, GIF, SVG.', 'garment-customizer'),
                size_format($max_size)
            );
            ?>
        </p>
    </div>

    <?php if ($logo_url) : ?>
        <div class="gc-logo-controls">
            <div class="gc-logo-control">
                <label class="gc-logo-control__label" for="layer-logo-scale-<?php echo esc_attr($layer['id']); ?>">
                    <?php esc_html_e('Scale', 'garment-customizer'); ?>
                </label>
                <input type="range"
                       class="gc-logo-control__range"
                       id="layer-logo-scale-<?php echo esc_attr($layer['id']); ?>"
                       min="10"
                       max="200"
                       value="<?php echo esc_attr(isset($layer['scale']) ? $layer['scale'] * 100 : 100); ?>"
                       data-property="scale">
                <output class="gc-logo-control__value">
                    <?php echo esc_html(isset($layer['scale']) ? $layer['scale'] * 100 : 100); ?>%
                </output>
            </div>

            <div class="gc-logo-control">
                <label class="gc-logo-control__label" for="layer-logo-rotation-<?php echo esc_attr($layer['id']); ?>">
                    <?php esc_html_e('Rotation', 'garment-customizer'); ?>
                </label>
                <input type="range"
                       class="gc-logo-control__range"
                       id="layer-logo-rotation-<?php echo esc_attr($layer['id']); ?>"
                       min="-180"
                       max="180"
                       value="<?php echo esc_attr(isset($layer['rotation']) ? $layer['rotation'] : 0); ?>"
                       data-property="rotation">
                <output class="gc-logo-control__value">
                    <?php echo esc_html(isset($layer['rotation']) ? $layer['rotation'] : 0); ?>°
                </output>
            </div>

            <div class="gc-logo-control">
                <label class="gc-logo-control__label" for="layer-logo-opacity-<?php echo esc_attr($layer['id']); ?>">
                    <?php esc_html_e('Opacity', 'garment-customizer'); ?>
                </label>
                <input type="range"
                       class="gc-logo-control__range"
                       id="layer-logo-opacity-<?php echo esc_attr($layer['id']); ?>"
                       min="0"
                       max="100"
                       value="<?php echo esc_attr(isset($layer['opacity']) ? $layer['opacity'] * 100 : 100); ?>"
                       data-property="opacity">
                <output class="gc-logo-control__value">
                    <?php echo esc_html(isset($layer['opacity']) ? $layer['opacity'] * 100 : 100); ?>%
                </output>
            </div>
        </div>
    <?php endif; ?>
</div>
