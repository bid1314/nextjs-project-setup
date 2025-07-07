      # Garment Customizer WordPress Plugin

A comprehensive WordPress plugin that allows users to customize garments with layers, colors, logos, and text. Features include live preview, shopping cart functionality, and request for quote (RFQ) system.

## Features

- **Interactive Garment Customization**
  - Real-time color selection with palette support
  - Custom text overlay with font options
  - Logo upload with content validation
  - Layer management system
  - Live preview functionality

- **Shopping Cart Integration**
  - Add customized garments to cart
  - Session-based cart for guests
  - User meta storage for logged-in users
  - Cart management via REST API

- **Request for Quote (RFQ)**
  - Contact form for custom inquiries
  - Email notifications to administrators
  - RFQ management in WordPress admin

- **Modern UI/UX**
  - Responsive design with Tailwind CSS
  - Accessibility compliant
  - Touch-friendly interface
  - Error boundaries and loading states

- **REST API Integration**
  - Secure endpoints with nonce verification
  - Comprehensive error handling
  - Input sanitization and validation

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the `garment-customizer` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure settings in the admin panel

### Via WordPress Admin

1. Go to Plugins > Add New
2. Upload the plugin zip file
3. Activate the plugin
4. Configure settings

## Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher (or MariaDB 10.0+)

## Usage

### Basic Usage

Add the customizer to any page or post using the shortcode:

```
[garment_customizer]
```

### Shortcode Parameters

```
[garment_customizer theme="default" width="100%" height="auto"]
```

- `theme`: UI theme (default: "default")
- `width`: Container width (default: "100%")
- `height`: Container height (default: "auto")

### Creating Garments

1. Go to **Garments** in the WordPress admin
2. Click **Add New**
3. Enter garment details:
   - Title and description
   - Featured image
   - Custom fields for layers, colors, etc.
4. Publish the garment

### Managing RFQs

1. Go to **Request for Quotes** in the admin
2. View submitted RFQs
3. Respond to customer inquiries
4. Track RFQ status

## Configuration

### Plugin Settings

Access settings via **Garment Customizer > Settings**:

- **Logo Validation**: Enable/disable logo content validation
- **File Upload Limits**: Set maximum file sizes
- **Email Notifications**: Configure admin notifications
- **Cart Settings**: Enable/disable shopping cart
- **API Settings**: Configure external integrations

### Custom Post Types

The plugin registers two custom post types:

1. **Garment** (`garment`)
   - Stores garment information
   - Supports custom fields for customization options
   - REST API enabled

2. **RFQ** (`rfq`)
   - Stores request for quote submissions
   - Admin-only visibility
   - Email integration

### REST API Endpoints

#### Garments
- `GET /wp-json/gc/v1/garments` - Get all garments
- `GET /wp-json/gc/v1/garment/{id}` - Get single garment
- `POST /wp-json/gc/v1/garment/{id}` - Update garment customization

#### Shopping Cart
- `GET /wp-json/gc/v1/cart` - Get cart contents
- `POST /wp-json/gc/v1/cart/add` - Add item to cart
- `POST /wp-json/gc/v1/cart/clear` - Clear cart

#### RFQ
- `POST /wp-json/gc/v1/rfq/submit` - Submit RFQ

#### Logo Validation
- `POST /wp-json/gc/v1/logo/validate` - Validate logo content

## Customization

### Styling

The plugin uses Tailwind CSS for styling. You can customize the appearance by:

1. **CSS Variables**: Override CSS custom properties
2. **Theme Integration**: Modify `assets/css/tailwind.css`
3. **Custom Styles**: Add styles to your theme

### JavaScript Customization

The React frontend can be customized by:

1. Modifying `assets/js/customizer.jsx`
2. Adding custom components
3. Extending the API service layer

### PHP Hooks and Filters

```php
// Customize garment data before sending to frontend
add_filter( 'gc_garment_data', function( $data, $garment_id ) {
    // Modify $data
    return $data;
}, 10, 2 );

// Customize RFQ email content
add_filter( 'gc_rfq_email_content', function( $content, $rfq_data ) {
    // Modify email content
    return $content;
}, 10, 2 );

// Add custom validation for logo uploads
add_filter( 'gc_logo_validation', function( $is_valid, $file_data ) {
    // Custom validation logic
    return $is_valid;
}, 10, 2 );
```

## Development

### File Structure

```
wordpress-plugin/
├── garment-customizer.php          # Main plugin file
├── README.md                       # Documentation
├── assets/
│   ├── css/
│   │   └── tailwind.css           # Tailwind CSS styles
│   └── js/
│       └── customizer.jsx         # React frontend
├── includes/
│   ├── custom-post-types.php      # Post type registration
│   ├── rest-api.php              # REST API endpoints
│   ├── shopping-cart.php         # Cart functionality
│   └── request-for-quote.php     # RFQ handling
└── languages/                     # Translation files
```

### Building Assets

For development, you may want to build the React components:

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Development mode with hot reload
npm run dev
```

### Testing

Run tests using PHPUnit:

```bash
# Install PHPUnit
composer install

# Run tests
vendor/bin/phpunit
```

## Security

The plugin implements several security measures:

- **Nonce Verification**: All AJAX and REST requests use nonces
- **Input Sanitization**: All user inputs are sanitized
- **Capability Checks**: Proper permission verification
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Output escaping

## Performance

- **Lazy Loading**: Scripts only load when needed
- **Caching**: Utilizes WordPress caching
- **Optimized Queries**: Efficient database operations
- **Asset Minification**: Compressed CSS/JS files

## Troubleshooting

### Common Issues

1. **Customizer Not Loading**
   - Check if shortcode is properly placed
   - Verify JavaScript console for errors
   - Ensure WordPress REST API is enabled

2. **Cart Not Working**
   - Check if sessions are enabled
   - Verify user permissions
   - Clear browser cache

3. **RFQ Emails Not Sending**
   - Check WordPress email configuration
   - Verify admin email address
   - Test with SMTP plugin

### Debug Mode

Enable debug mode by adding to `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

Check debug logs in `/wp-content/debug.log`

## Support

For support and bug reports:

1. Check the documentation
2. Search existing issues
3. Create a new support ticket
4. Provide detailed error information

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Changelog

### Version 1.0.0
- Initial release
- Basic garment customization
- Shopping cart functionality
- RFQ system
- REST API integration
- Modern React frontend

## License

This plugin is licensed under the MIT License. See LICENSE file for details.

## Credits

- Built with WordPress, React, and Tailwind CSS
- Icons from Lucide React
- Fonts from Google Fonts
- Images from Pexels (where applicable)

---

**Garment Customizer** - Transform your WordPress site into a powerful garment customization platform.
