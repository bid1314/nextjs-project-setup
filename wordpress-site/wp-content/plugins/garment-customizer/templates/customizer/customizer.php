<?php
/**
 * Garment Customizer Interface
 *
 * This template displays the main customizer interface.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('gc_before_customizer');
?>

<div id="gc-customizer" class="gc-customizer">
    <?php do_action('gc_before_customizer_content'); ?>

    <div class="gc-customizer__main">
        <!-- Preview Section -->
        <div class="gc-customizer__preview">
            <?php do_action('gc_customizer_preview'); ?>
        </div>

        <!-- Controls Section -->
        <div class="gc-customizer__controls">
            <!-- Layers Panel -->
            <div class="gc-customizer__panel gc-customizer__panel--layers">
                <h3 class="gc-customizer__panel-title"><?php esc_html_e('Layers', 'garment-customizer'); ?></h3>
                <div class="gc-customizer__panel-content">
                    <?php do_action('gc_customizer_layers_panel'); ?>
                </div>
            </div>

            <!-- Colors Panel -->
            <div class="gc-customizer__panel gc-customizer__panel--colors">
                <h3 class="gc-customizer__panel-title"><?php esc_html_e('Colors', 'garment-customizer'); ?></h3>
                <div class="gc-customizer__panel-content">
                    <?php do_action('gc_customizer_colors_panel'); ?>
                </div>
            </div>

            <!-- Text Panel -->
            <div class="gc-customizer__panel gc-customizer__panel--text">
                <h3 class="gc-customizer__panel-title"><?php esc_html_e('Text', 'garment-customizer'); ?></h3>
                <div class="gc-customizer__panel-content">
                    <?php do_action('gc_customizer_text_panel'); ?>
                </div>
            </div>

            <!-- Logo Panel -->
            <div class="gc-customizer__panel gc-customizer__panel--logo">
                <h3 class="gc-customizer__panel-title"><?php esc_html_e('Logo', 'garment-customizer'); ?></h3>
                <div class="gc-customizer__panel-content">
                    <?php do_action('gc_customizer_logo_panel'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="gc-customizer__actions">
        <div class="gc-customizer__price">
            <span class="gc-customizer__price-label"><?php esc_html_e('Total:', 'garment-customizer'); ?></span>
            <span class="gc-customizer__price-amount"></span>
        </div>
        <div class="gc-customizer__buttons">
            <button type="button" class="gc-button gc-button--secondary gc-customizer__reset">
                <?php esc_html_e('Reset', 'garment-customizer'); ?>
            </button>
            <button type="button" class="gc-button gc-button--primary gc-customizer__add-to-cart">
                <?php esc_html_e('Add to Cart', 'garment-customizer'); ?>
            </button>
            <button type="button" class="gc-button gc-button--secondary gc-customizer__request-quote">
                <?php esc_html_e('Request Quote', 'garment-customizer'); ?>
            </button>
        </div>
    </div>

    <?php do_action('gc_after_customizer_content'); ?>
</div>

<?php do_action('gc_after_customizer'); ?>
