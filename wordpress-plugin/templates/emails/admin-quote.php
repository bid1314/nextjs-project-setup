<?php
/**
 * Admin Quote Request Email Template
 *
 * This template is used when sending quote request notifications to administrators.
 *
 * @package GarmentCustomizer
 * @var int $quote_id Quote post ID
 */

if (!defined('ABSPATH')) {
    exit;
}

$quote = get_post($quote_id);
$customer_data = get_post_meta($quote_id, 'gc_customer_data', true);
$garment_id = get_post_meta($quote_id, 'gc_garment_id', true);
$customizations = get_post_meta($quote_id, 'gc_customizations', true);

$admin_url = admin_url('post.php?post=' . $quote_id . '&action=edit');
$site_name = get_bloginfo('name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(sprintf(__('New Quote Request: %s', 'garment-customizer'), get_the_title($garment_id))); ?></title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #007cba; margin: 0;">
            <?php esc_html_e('New Quote Request Received', 'garment-customizer'); ?>
        </h1>
        <p style="margin: 5px 0 0;">
            <?php echo esc_html(sprintf(__('From %s', 'garment-customizer'), $customer_data['name'])); ?>
        </p>
    </div>

    <div style="background: #f8f9fa; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <p style="margin-top: 0;">
            <?php
            printf(
                esc_html__('A new quote request has been submitted for %s.', 'garment-customizer'),
                esc_html(get_the_title($garment_id))
            );
            ?>
        </p>
        
        <p>
            <a href="<?php echo esc_url($admin_url); ?>" style="display: inline-block; background: #007cba; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
                <?php esc_html_e('View Quote Details', 'garment-customizer'); ?>
            </a>
        </p>
    </div>

    <div style="margin-bottom: 20px;">
        <h2 style="color: #007cba; font-size: 1.2em; margin: 0 0 10px;">
            <?php esc_html_e('Quote Details', 'garment-customizer'); ?>
        </h2>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <strong><?php esc_html_e('Quote ID:', 'garment-customizer'); ?></strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <?php echo esc_html($quote->ID); ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <strong><?php esc_html_e('Garment:', 'garment-customizer'); ?></strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <?php echo esc_html(get_the_title($garment_id)); ?>
                    (ID: <?php echo esc_html($garment_id); ?>)
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <strong><?php esc_html_e('Date:', 'garment-customizer'); ?></strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <?php echo esc_html(get_the_date('', $quote->ID)); ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 20px;">
        <h2 style="color: #007cba; font-size: 1.2em; margin: 0 0 10px;">
            <?php esc_html_e('Customer Information', 'garment-customizer'); ?>
        </h2>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <strong><?php esc_html_e('Name:', 'garment-customizer'); ?></strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <?php echo esc_html($customer_data['name']); ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <strong><?php esc_html_e('Email:', 'garment-customizer'); ?></strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <a href="mailto:<?php echo esc_attr($customer_data['email']); ?>" style="color: #007cba; text-decoration: none;">
                        <?php echo esc_html($customer_data['email']); ?>
                    </a>
                </td>
            </tr>
            <?php if (!empty($customer_data['phone'])) : ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                        <strong><?php esc_html_e('Phone:', 'garment-customizer'); ?></strong>
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                        <?php echo esc_html($customer_data['phone']); ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($customer_data['message'])) : ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                        <strong><?php esc_html_e('Message:', 'garment-customizer'); ?></strong>
                    </td>
                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                        <?php echo wp_kses_post(wpautop($customer_data['message'])); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div style="margin-bottom: 20px;">
        <h2 style="color: #007cba; font-size: 1.2em; margin: 0 0 10px;">
            <?php esc_html_e('Customization Details', 'garment-customizer'); ?>
        </h2>
        
        <div style="background: #fff; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px;">
            <pre style="margin: 0; white-space: pre-wrap;"><?php echo esc_html(json_encode($customizations, JSON_PRETTY_PRINT)); ?></pre>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; color: #6c757d; font-size: 0.9em;">
        <p style="margin-bottom: 0;">
            <?php esc_html_e('This is an automated notification from', 'garment-customizer'); ?>
            <?php echo esc_html($site_name); ?>
        </p>
    </div>
</body>
</html>
