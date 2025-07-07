<?php
/**
 * Text Layer Controls Template
 *
 * This template displays the controls for a text layer.
 *
 * @package GarmentCustomizer
 * @var array $layer The layer data
 */

if (!defined('ABSPATH')) {
    exit;
}

$text_content = isset($layer['text']) ? $layer['text'] : '';
$font_family = isset($layer['font_family']) ? $layer['font_family'] : 'Arial';
$font_size = isset($layer['font_size']) ? $layer['font_size'] : '16';
$font_weight = isset($layer['font_weight']) ? $layer['font_weight'] : '400';
$text_align = isset($layer['text_align']) ? $layer['text_align'] : 'left';
$text_color = isset($layer['text_color']) ? $layer['text_color'] : '#000000';

// Available font families
$font_families = array(
    'Arial' => __('Arial', 'garment-customizer'),
    'Helvetica' => __('Helvetica', 'garment-customizer'),
    'Times New Roman' => __('Times New Roman', 'garment-customizer'),
    'Georgia' => __('Georgia', 'garment-customizer'),
    'Verdana' => __('Verdana', 'garment-customizer'),
);

// Font weights
$font_weights = array(
    '300' => __('Light', 'garment-customizer'),
    '400' => __('Regular', 'garment-customizer'),
    '500' => __('Medium', 'garment-customizer'),
    '600' => __('Semi Bold', 'garment-customizer'),
    '700' => __('Bold', 'garment-customizer'),
);
?>

<div class="gc-layer-controls gc-layer-controls--text">
    <div class="gc-text-input">
        <label class="gc-text-input__label" for="layer-text-<?php echo esc_attr($layer['id']); ?>">
            <?php esc_html_e('Text Content', 'garment-customizer'); ?>
        </label>
        <textarea class="gc-text-input__field"
                  id="layer-text-<?php echo esc_attr($layer['id']); ?>"
                  data-property="text"
                  rows="2"><?php echo esc_textarea($text_content); ?></textarea>
    </div>

    <div class="gc-text-controls">
        <div class="gc-text-control">
            <label class="gc-text-control__label" for="layer-font-family-<?php echo esc_attr($layer['id']); ?>">
                <?php esc_html_e('Font Family', 'garment-customizer'); ?>
            </label>
            <select class="gc-text-control__select"
                    id="layer-font-family-<?php echo esc_attr($layer['id']); ?>"
                    data-property="font-family">
                <?php foreach ($font_families as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($font_family, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="gc-text-control">
            <label class="gc-text-control__label" for="layer-font-size-<?php echo esc_attr($layer['id']); ?>">
                <?php esc_html_e('Font Size', 'garment-customizer'); ?>
            </label>
            <div class="gc-text-control__size">
                <input type="range"
                       class="gc-text-control__range"
                       id="layer-font-size-<?php echo esc_attr($layer['id']); ?>"
                       min="8"
                       max="72"
                       value="<?php echo esc_attr($font_size); ?>"
                       data-property="font-size">
                <output class="gc-text-control__value">
                    <?php echo esc_html($font_size); ?>px
                </output>
            </div>
        </div>

        <div class="gc-text-control">
            <label class="gc-text-control__label" for="layer-font-weight-<?php echo esc_attr($layer['id']); ?>">
                <?php esc_html_e('Font Weight', 'garment-customizer'); ?>
            </label>
            <select class="gc-text-control__select"
                    id="layer-font-weight-<?php echo esc_attr($layer['id']); ?>"
                    data-property="font-weight">
                <?php foreach ($font_weights as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($font_weight, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="gc-text-control">
            <span class="gc-text-control__label"><?php esc_html_e('Text Alignment', 'garment-customizer'); ?></span>
            <div class="gc-text-control__align" role="group">
                <button type="button" 
                        class="gc-text-control__align-button <?php echo esc_attr($text_align === 'left' ? 'is-active' : ''); ?>"
                        data-align="left"
                        aria-label="<?php esc_attr_e('Align left', 'garment-customizer'); ?>"
                        aria-pressed="<?php echo $text_align === 'left' ? 'true' : 'false'; ?>">
                    <svg class="gc-icon" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3 3h18v2H3zm0 4h12v2H3zm0 4h18v2H3zm0 4h12v2H3zm0 4h18v2H3z"/>
                    </svg>
                </button>
                <button type="button"
                        class="gc-text-control__align-button <?php echo esc_attr($text_align === 'center' ? 'is-active' : ''); ?>"
                        data-align="center"
                        aria-label="<?php esc_attr_e('Align center', 'garment-customizer'); ?>"
                        aria-pressed="<?php echo $text_align === 'center' ? 'true' : 'false'; ?>">
                    <svg class="gc-icon" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3 3h18v2H3zm3 4h12v2H6zm-3 4h18v2H3zm3 4h12v2H6zm-3 4h18v2H3z"/>
                    </svg>
                </button>
                <button type="button"
                        class="gc-text-control__align-button <?php echo esc_attr($text_align === 'right' ? 'is-active' : ''); ?>"
                        data-align="right"
                        aria-label="<?php esc_attr_e('Align right', 'garment-customizer'); ?>"
                        aria-pressed="<?php echo $text_align === 'right' ? 'true' : 'false'; ?>">
                    <svg class="gc-icon" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3 3h18v2H3zm6 4h12v2H9zm-6 4h18v2H3zm6 4h12v2H9zm-6 4h18v2H3z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="gc-text-control">
            <label class="gc-text-control__label" for="layer-text-color-<?php echo esc_attr($layer['id']); ?>">
                <?php esc_html_e('Text Color', 'garment-customizer'); ?>
            </label>
            <input type="color"
                   class="gc-text-control__color"
                   id="layer-text-color-<?php echo esc_attr($layer['id']); ?>"
                   value="<?php echo esc_attr($text_color); ?>"
                   data-property="color">
        </div>
    </div>
</div>
