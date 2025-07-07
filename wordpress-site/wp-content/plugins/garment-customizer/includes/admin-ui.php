<?php
/**
 * Admin UI Handler
 *
 * Handles the plugin's admin interface and settings.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu items
 */
function gc_add_admin_menu() {
    add_menu_page(
        __('Garment Customizer', 'garment-customizer'),
        __('Garment Customizer', 'garment-customizer'),
        'manage_options',
        'garment-customizer',
        'gc_render_settings_page',
        'dashicons-admin-customizer',
        30
    );

    add_submenu_page(
        'garment-customizer',
        __('Settings', 'garment-customizer'),
        __('Settings', 'garment-customizer'),
        'manage_options',
        'garment-customizer',
        'gc_render_settings_page'
    );

    add_submenu_page(
        'garment-customizer',
        __('Quote Requests', 'garment-customizer'),
        __('Quote Requests', 'garment-customizer'),
        'manage_options',
        'edit.php?post_type=gc_quote'
    );
}
add_action('admin_menu', 'gc_add_admin_menu');

/**
 * Register plugin settings
 */
function gc_register_settings() {
    register_setting(
        'gc_settings',
        'gc_options',
        array(
            'type' => 'array',
            'sanitize_callback' => 'gc_sanitize_settings'
        )
    );

    add_settings_section(
        'gc_general_settings',
        __('General Settings', 'garment-customizer'),
        'gc_render_general_settings_section',
        'garment-customizer'
    );

    add_settings_field(
        'gc_enable_quotes',
        __('Enable Quote Requests', 'garment-customizer'),
        'gc_render_enable_quotes_field',
        'garment-customizer',
        'gc_general_settings'
    );

    add_settings_field(
        'gc_quote_email_template',
        __('Quote Email Template', 'garment-customizer'),
        'gc_render_quote_email_template_field',
        'garment-customizer',
        'gc_general_settings'
    );

    add_settings_field(
        'gc_default_prices',
        __('Default Customization Prices', 'garment-customizer'),
        'gc_render_default_prices_field',
        'garment-customizer',
        'gc_general_settings'
    );
}
add_action('admin_init', 'gc_register_settings');

/**
 * Render settings page
 */
function gc_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    $options = get_option('gc_options', array());
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <nav class="nav-tab-wrapper">
            <a href="?page=garment-customizer&tab=general" 
               class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('General', 'garment-customizer'); ?>
            </a>
            <a href="?page=garment-customizer&tab=customization" 
               class="nav-tab <?php echo $active_tab === 'customization' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Customization', 'garment-customizer'); ?>
            </a>
            <a href="?page=garment-customizer&tab=quotes" 
               class="nav-tab <?php echo $active_tab === 'quotes' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Quotes', 'garment-customizer'); ?>
            </a>
        </nav>

        <form action="options.php" method="post">
            <?php
            settings_fields('gc_settings');
            do_settings_sections('garment-customizer');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render general settings section
 */
function gc_render_general_settings_section() {
    echo '<p>' . esc_html__('Configure general settings for the Garment Customizer plugin.', 'garment-customizer') . '</p>';
}

/**
 * Render enable quotes field
 */
function gc_render_enable_quotes_field() {
    $options = get_option('gc_options', array());
    $enabled = isset($options['enable_quotes']) ? $options['enable_quotes'] : true;
    ?>
    <label>
        <input type="checkbox" 
               name="gc_options[enable_quotes]" 
               value="1" 
               <?php checked($enabled); ?>>
        <?php esc_html_e('Enable quote request functionality', 'garment-customizer'); ?>
    </label>
    <p class="description">
        <?php esc_html_e('Allow customers to request quotes for customized garments.', 'garment-customizer'); ?>
    </p>
    <?php
}

/**
 * Render quote email template field
 */
function gc_render_quote_email_template_field() {
    $options = get_option('gc_options', array());
    $template = isset($options['quote_email_template']) ? $options['quote_email_template'] : '';
    ?>
    <textarea name="gc_options[quote_email_template]" 
              rows="10" 
              class="large-text code"><?php echo esc_textarea($template); ?></textarea>
    <p class="description">
        <?php esc_html_e('Customize the email template sent to customers when requesting quotes.', 'garment-customizer'); ?>
        <?php esc_html_e('Available variables: {customer_name}, {garment_name}, {quote_id}', 'garment-customizer'); ?>
    </p>
    <?php
}

/**
 * Render default prices field
 */
function gc_render_default_prices_field() {
    $options = get_option('gc_options', array());
    $prices = isset($options['default_prices']) ? $options['default_prices'] : array(
        'color' => 5.00,
        'text' => 10.00,
        'logo' => 15.00
    );
    ?>
    <div class="gc-price-fields">
        <p>
            <label>
                <span><?php esc_html_e('Color Customization:', 'garment-customizer'); ?></span>
                <input type="number" 
                       name="gc_options[default_prices][color]" 
                       value="<?php echo esc_attr($prices['color']); ?>" 
                       step="0.01" 
                       min="0">
            </label>
        </p>
        <p>
            <label>
                <span><?php esc_html_e('Text Customization:', 'garment-customizer'); ?></span>
                <input type="number" 
                       name="gc_options[default_prices][text]" 
                       value="<?php echo esc_attr($prices['text']); ?>" 
                       step="0.01" 
                       min="0">
            </label>
        </p>
        <p>
            <label>
                <span><?php esc_html_e('Logo Customization:', 'garment-customizer'); ?></span>
                <input type="number" 
                       name="gc_options[default_prices][logo]" 
                       value="<?php echo esc_attr($prices['logo']); ?>" 
                       step="0.01" 
                       min="0">
            </label>
        </p>
    </div>
    <p class="description">
        <?php esc_html_e('Set default prices for different types of customization.', 'garment-customizer'); ?>
    </p>
    <?php
}

/**
 * Sanitize settings
 *
 * @param array $input The value being saved
 * @return array The sanitized value
 */
function gc_sanitize_settings($input) {
    $sanitized = array();

    // Enable quotes
    $sanitized['enable_quotes'] = isset($input['enable_quotes']);

    // Email template
    $sanitized['quote_email_template'] = wp_kses_post($input['quote_email_template']);

    // Default prices
    $sanitized['default_prices'] = array(
        'color' => (float) $input['default_prices']['color'],
        'text' => (float) $input['default_prices']['text'],
        'logo' => (float) $input['default_prices']['logo']
    );

    return $sanitized;
}

/**
 * Add action links to plugins page
 *
 * @param array $links Plugin action links
 * @return array
 */
function gc_add_action_links($links) {
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=garment-customizer') . '">' . 
        __('Settings', 'garment-customizer') . '</a>'
    );
    return array_merge($plugin_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(GC_PLUGIN_FILE), 'gc_add_action_links');

/**
 * Add custom admin styles
 */
function gc_admin_styles() {
    $screen = get_current_screen();
    
    if (strpos($screen->id, 'garment-customizer') !== false || 
        $screen->post_type === 'gc_quote') {
        wp_enqueue_style(
            'gc-admin-styles',
            plugins_url('assets/css/admin.css', GC_PLUGIN_FILE),
            array(),
            GC_PLUGIN_VERSION
        );
    }
}
add_action('admin_enqueue_scripts', 'gc_admin_styles');
