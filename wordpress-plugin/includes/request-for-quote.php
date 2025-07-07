<?php
/**
 * Request for Quote Handler
 *
 * Handles quote request submission and processing.
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register quote post type
 */
function gc_register_quote_post_type() {
    $labels = array(
        'name'               => _x('Quote Requests', 'Post type general name', 'garment-customizer'),
        'singular_name'      => _x('Quote Request', 'Post type singular name', 'garment-customizer'),
        'menu_name'          => _x('Quote Requests', 'Admin Menu text', 'garment-customizer'),
        'name_admin_bar'     => _x('Quote Request', 'Add New on Toolbar', 'garment-customizer'),
        'add_new'           => __('Add New', 'garment-customizer'),
        'add_new_item'      => __('Add New Quote Request', 'garment-customizer'),
        'new_item'          => __('New Quote Request', 'garment-customizer'),
        'edit_item'         => __('Edit Quote Request', 'garment-customizer'),
        'view_item'         => __('View Quote Request', 'garment-customizer'),
        'all_items'         => __('All Quote Requests', 'garment-customizer'),
        'search_items'      => __('Search Quote Requests', 'garment-customizer'),
        'not_found'         => __('No quote requests found.', 'garment-customizer'),
        'not_found_in_trash' => __('No quote requests found in Trash.', 'garment-customizer')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'quote-request'),
        'capability_type'   => 'post',
        'has_archive'       => false,
        'hierarchical'      => false,
        'menu_position'     => null,
        'supports'          => array('title', 'editor', 'custom-fields'),
        'menu_icon'         => 'dashicons-money-alt'
    );

    register_post_type('gc_quote', $args);
}
add_action('init', 'gc_register_quote_post_type');

/**
 * Add meta boxes for quote requests
 */
function gc_add_quote_meta_boxes() {
    add_meta_box(
        'gc_quote_details',
        __('Quote Details', 'garment-customizer'),
        'gc_render_quote_details_meta_box',
        'gc_quote',
        'normal',
        'high'
    );

    add_meta_box(
        'gc_quote_customer',
        __('Customer Information', 'garment-customizer'),
        'gc_render_quote_customer_meta_box',
        'gc_quote',
        'normal',
        'high'
    );

    add_meta_box(
        'gc_quote_status',
        __('Quote Status', 'garment-customizer'),
        'gc_render_quote_status_meta_box',
        'gc_quote',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'gc_add_quote_meta_boxes');

/**
 * Render quote details meta box
 *
 * @param WP_Post $post Post object
 */
function gc_render_quote_details_meta_box($post) {
    $garment_id = get_post_meta($post->ID, 'gc_garment_id', true);
    $customizations = get_post_meta($post->ID, 'gc_customizations', true);
    $quantity = get_post_meta($post->ID, 'gc_quantity', true);
    
    wp_nonce_field('gc_quote_details_nonce', 'gc_quote_details_nonce');
    ?>
    <div class="gc-meta-box gc-meta-box--quote-details">
        <p>
            <label for="gc_garment_id"><?php esc_html_e('Garment', 'garment-customizer'); ?></label>
            <strong><?php echo esc_html(get_the_title($garment_id)); ?></strong>
            <a href="<?php echo esc_url(get_edit_post_link($garment_id)); ?>" target="_blank">
                <?php esc_html_e('View Garment', 'garment-customizer'); ?>
            </a>
        </p>

        <p>
            <label for="gc_quantity"><?php esc_html_e('Quantity', 'garment-customizer'); ?></label>
            <input type="number" 
                   id="gc_quantity" 
                   name="gc_quantity" 
                   value="<?php echo esc_attr($quantity); ?>" 
                   min="1">
        </p>

        <?php if ($customizations) : ?>
            <div class="gc-customizations">
                <h4><?php esc_html_e('Customizations', 'garment-customizer'); ?></h4>
                <pre><?php echo esc_html(json_encode($customizations, JSON_PRETTY_PRINT)); ?></pre>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render quote customer meta box
 *
 * @param WP_Post $post Post object
 */
function gc_render_quote_customer_meta_box($post) {
    $customer_data = get_post_meta($post->ID, 'gc_customer_data', true);
    ?>
    <div class="gc-meta-box gc-meta-box--customer">
        <p>
            <label><?php esc_html_e('Name', 'garment-customizer'); ?></label>
            <strong><?php echo esc_html($customer_data['name']); ?></strong>
        </p>

        <p>
            <label><?php esc_html_e('Email', 'garment-customizer'); ?></label>
            <a href="mailto:<?php echo esc_attr($customer_data['email']); ?>">
                <?php echo esc_html($customer_data['email']); ?>
            </a>
        </p>

        <?php if (!empty($customer_data['phone'])) : ?>
            <p>
                <label><?php esc_html_e('Phone', 'garment-customizer'); ?></label>
                <strong><?php echo esc_html($customer_data['phone']); ?></strong>
            </p>
        <?php endif; ?>

        <?php if (!empty($customer_data['message'])) : ?>
            <div class="gc-customer-message">
                <label><?php esc_html_e('Message', 'garment-customizer'); ?></label>
                <div class="gc-message-content">
                    <?php echo wp_kses_post(wpautop($customer_data['message'])); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render quote status meta box
 *
 * @param WP_Post $post Post object
 */
function gc_render_quote_status_meta_box($post) {
    $status = get_post_meta($post->ID, 'gc_quote_status', true) ?: 'pending';
    $quoted_price = get_post_meta($post->ID, 'gc_quoted_price', true);
    
    wp_nonce_field('gc_quote_status_nonce', 'gc_quote_status_nonce');
    ?>
    <div class="gc-meta-box gc-meta-box--status">
        <p>
            <label for="gc_quote_status"><?php esc_html_e('Status', 'garment-customizer'); ?></label>
            <select id="gc_quote_status" name="gc_quote_status">
                <option value="pending" <?php selected($status, 'pending'); ?>>
                    <?php esc_html_e('Pending', 'garment-customizer'); ?>
                </option>
                <option value="in-progress" <?php selected($status, 'in-progress'); ?>>
                    <?php esc_html_e('In Progress', 'garment-customizer'); ?>
                </option>
                <option value="quoted" <?php selected($status, 'quoted'); ?>>
                    <?php esc_html_e('Quoted', 'garment-customizer'); ?>
                </option>
                <option value="accepted" <?php selected($status, 'accepted'); ?>>
                    <?php esc_html_e('Accepted', 'garment-customizer'); ?>
                </option>
                <option value="rejected" <?php selected($status, 'rejected'); ?>>
                    <?php esc_html_e('Rejected', 'garment-customizer'); ?>
                </option>
            </select>
        </p>

        <p>
            <label for="gc_quoted_price"><?php esc_html_e('Quoted Price', 'garment-customizer'); ?></label>
            <input type="number" 
                   id="gc_quoted_price" 
                   name="gc_quoted_price" 
                   value="<?php echo esc_attr($quoted_price); ?>" 
                   step="0.01" 
                   min="0">
        </p>

        <div class="gc-quote-actions">
            <button type="button" 
                    class="button gc-send-quote" 
                    data-quote-id="<?php echo esc_attr($post->ID); ?>">
                <?php esc_html_e('Send Quote to Customer', 'garment-customizer'); ?>
            </button>
        </div>
    </div>
    <?php
}

/**
 * Save quote meta box data
 *
 * @param int $post_id Post ID
 */
function gc_save_quote_meta_boxes($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['gc_quote_details_nonce']) || !isset($_POST['gc_quote_status_nonce'])) {
        return;
    }

    // Verify the nonces
    if (!wp_verify_nonce($_POST['gc_quote_details_nonce'], 'gc_quote_details_nonce') ||
        !wp_verify_nonce($_POST['gc_quote_status_nonce'], 'gc_quote_status_nonce')) {
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

    // Save quantity
    if (isset($_POST['gc_quantity'])) {
        update_post_meta(
            $post_id,
            'gc_quantity',
            absint($_POST['gc_quantity'])
        );
    }

    // Save status
    if (isset($_POST['gc_quote_status'])) {
        update_post_meta(
            $post_id,
            'gc_quote_status',
            sanitize_text_field($_POST['gc_quote_status'])
        );
    }

    // Save quoted price
    if (isset($_POST['gc_quoted_price'])) {
        update_post_meta(
            $post_id,
            'gc_quoted_price',
            (float) $_POST['gc_quoted_price']
        );
    }
}
add_action('save_post_gc_quote', 'gc_save_quote_meta_boxes');

/**
 * Handle AJAX quote submission
 */
function gc_handle_quote_submission() {
    check_ajax_referer('gc_quote_nonce', 'nonce');

    $garment_id = isset($_POST['garment_id']) ? absint($_POST['garment_id']) : 0;
    $customizations = isset($_POST['customizations']) ? $_POST['customizations'] : array();
    $customer_data = isset($_POST['customer']) ? $_POST['customer'] : array();

    if (!$garment_id || empty($customer_data)) {
        wp_send_json_error(array(
            'message' => __('Invalid request data.', 'garment-customizer')
        ));
    }

    // Create quote
    $quote_id = gc_create_quote($garment_id, $customizations, $customer_data);

    if (!$quote_id || is_wp_error($quote_id)) {
        wp_send_json_error(array(
            'message' => __('Failed to create quote request.', 'garment-customizer')
        ));
    }

    // Send notifications
    gc_send_quote_notifications($quote_id);

    wp_send_json_success(array(
        'message' => __('Quote request submitted successfully.', 'garment-customizer'),
        'quote_id' => $quote_id
    ));
}
add_action('wp_ajax_gc_submit_quote', 'gc_handle_quote_submission');
add_action('wp_ajax_nopriv_gc_submit_quote', 'gc_handle_quote_submission');

/**
 * Create a new quote
 *
 * @param int   $garment_id    Garment ID
 * @param array $customizations Customization data
 * @param array $customer_data Customer information
 * @return int|WP_Error
 */
function gc_create_quote($garment_id, $customizations, $customer_data) {
    $quote_data = array(
        'post_title'  => sprintf(
            __('Quote Request for %s', 'garment-customizer'),
            get_the_title($garment_id)
        ),
        'post_type'   => 'gc_quote',
        'post_status' => 'publish',
        'meta_input'  => array(
            'gc_garment_id'     => $garment_id,
            'gc_customizations' => $customizations,
            'gc_customer_data'  => $customer_data,
            'gc_quote_status'   => 'pending'
        )
    );

    return wp_insert_post($quote_data);
}
