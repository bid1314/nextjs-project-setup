<?php
/**
 * Customer Quote Request Email Template
 *
 * This template is used when sending quote request confirmation emails to customers.
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

$site_name = get_bloginfo('name');
$site_url = get_bloginfo('url');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(sprintf(__('Quote Request for %s', 'garment-customizer'), get_the_title($garment_id))); ?></title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #007cba; margin: 0;"><?php echo esc_html($site_name); ?></h1>
        <p style="margin: 5px 0 0;"><?php esc_html_e('Custom Garment Quote Request', 'garment-customizer'); ?></p>
    </div>

    <div style="background: #f8f9fa; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <p style="margin-top: 0;">
            <?php
            printf(
                esc_html__('Dear %s,', 'garment-customizer'),
                esc_html($customer_data['name'])
            );
            ?>
        </p>
        
        <p>
            <?php
            printf(
                esc_html__('Thank you for your quote request for the customized %s. We have received your request and will review it shortly.', 'garment-customizer'),
                esc_html(get_the_title($garment_id))
            );
            ?>
        </p>

        <p><?php esc_html_e('We will get back to you within 24-48 business hours with a detailed quote.', 'garment-customizer'); ?></p>
    </div>

    <div style="margin-bottom: 20px;">
        <h2 style="color: #007cba; font-size: 1.2em; margin: 0 0 10px;">
            <?php esc_html_e('Quote Request Details', 'garment-customizer'); ?>
        </h2>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <strong><?php esc_html_e('Quote Reference:', 'garment-customizer'); ?></strong>
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
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <strong><?php esc_html_e('Date Requested:', 'garment-customizer'); ?></strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <?php echo esc_html(get_the_date('', $quote->ID)); ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 20px;">
        <h2 style="color: #007cba; font-size: 1.2em; margin: 0 0 10px;">
            <?php esc_html_e('Your Contact Information', 'garment-customizer'); ?>
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
                    <?php echo esc_html($customer_data['email']); ?>
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
        </table>
    </div>

    <div style="margin-top: 30px; text-align: center; color: #6c757d; font-size: 0.9em;">
        <p>
            <?php esc_html_e('If you have any questions, please contact us.', 'garment-customizer'); ?>
        </p>
        <p style="margin-bottom: 0;">
            <?php echo esc_html($site_name); ?><br>
            <a href="<?php echo esc_url($site_url); ?>" style="color: #007cba; text-decoration: none;">
                <?php echo esc_html($site_url); ?>
            </a>
        </p>
    </div>
</body>
</html>
