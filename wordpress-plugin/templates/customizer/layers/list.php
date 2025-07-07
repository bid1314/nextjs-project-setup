<?php
/**
 * Customizer Layers List Template
 *
 * This template displays the list of customizable layers.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

$garment_id = get_the_ID();
$layers = get_post_meta($garment_id, 'gc_layers', true);

if (!$layers || !is_array($layers)) {
    return;
}
?>

<div class="gc-layers-list" data-garment-id="<?php echo esc_attr($garment_id); ?>">
    <?php foreach ($layers as $index => $layer) : ?>
        <div class="gc-layer-item" 
             data-layer-id="<?php echo esc_attr($index); ?>"
             data-layer-type="<?php echo esc_attr($layer['type']); ?>">
            
            <div class="gc-layer-item__header">
                <span class="gc-layer-item__name"><?php echo esc_html($layer['name']); ?></span>
                <div class="gc-layer-item__controls">
                    <button type="button" class="gc-layer-item__toggle" aria-expanded="false">
                        <span class="screen-reader-text">
                            <?php esc_html_e('Toggle layer controls', 'garment-customizer'); ?>
                        </span>
                        <svg class="gc-icon" width="24" height="24" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </button>
                    <button type="button" class="gc-layer-item__visibility" aria-pressed="true">
                        <span class="screen-reader-text">
                            <?php esc_html_e('Toggle layer visibility', 'garment-customizer'); ?>
                        </span>
                        <svg class="gc-icon" width="24" height="24" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="gc-layer-item__content" hidden>
                <?php 
                switch ($layer['type']) {
                    case 'color':
                        gc_get_template('customizer/layers/controls/color.php', array('layer' => $layer));
                        break;
                    case 'text':
                        gc_get_template('customizer/layers/controls/text.php', array('layer' => $layer));
                        break;
                    case 'logo':
                        gc_get_template('customizer/layers/controls/logo.php', array('layer' => $layer));
                        break;
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
