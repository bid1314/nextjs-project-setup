<?php
/**
 * Plugin Name: Garment Customizer
 * Description: A comprehensive WordPress plugin for customizing garments with layers, colors, logos, and text. Features live preview, shopping cart, and request for quote functionality.
 * Version: 1.0.0
 * Author: Garment Customizer Team
 * License: MIT
 * Text Domain: garment-customizer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'GC_PLUGIN_VERSION', '1.0.0' );
define( 'GC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Garment Customizer Plugin Class
 */
class GarmentCustomizer {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_head', array( $this, 'add_meta_tags' ) );
        
        // Plugin lifecycle hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        register_uninstall_hook( __FILE__, array( 'GarmentCustomizer', 'uninstall' ) );
        
        // Admin hooks
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_init', array( $this, 'create_customizer_pages' ) );
        
        // AJAX hooks for non-logged in users
        add_action( 'wp_ajax_nopriv_gc_get_garments', array( $this, 'ajax_get_garments' ) );
        add_action( 'wp_ajax_gc_get_garments', array( $this, 'ajax_get_garments' ) );
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        $required_files = array(
            'includes/custom-post-types.php',
            'includes/meta-fields.php',
            'includes/rest-api.php',
            'includes/shopping-cart.php',
            'includes/request-for-quote.php',
        );
        
        foreach ( $required_files as $file ) {
            $file_path = GC_PLUGIN_DIR . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            } else {
                add_action( 'admin_notices', function() use ( $file ) {
                    echo '<div class="notice notice-error"><p>';
                    printf( 
                        esc_html__( 'Garment Customizer: Required file %s is missing.', 'garment-customizer' ),
                        esc_html( $file )
                    );
                    echo '</p></div>';
                });
            }
        }
    }
    
    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain( 
            'garment-customizer', 
            false, 
            dirname( GC_PLUGIN_BASENAME ) . '/languages' 
        );
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Only enqueue on pages that have the shortcode
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'garment_customizer' ) ) {
            return;
        }
        
        // Enqueue React and ReactDOM from WordPress core
        wp_enqueue_script( 'wp-element' );
        wp_enqueue_script( 'wp-components' );
        wp_enqueue_script( 'wp-i18n' );
        wp_enqueue_script( 'wp-api' );
        
        // Enqueue Tailwind CSS
        wp_enqueue_style( 
            'gc-tailwind', 
            GC_PLUGIN_URL . 'assets/css/tailwind.css', 
            array(), 
            filemtime( GC_PLUGIN_DIR . 'assets/css/tailwind.css' ) 
        );
        
        // Enqueue customizer script
        wp_enqueue_script( 
            'gc-customizer', 
            GC_PLUGIN_URL . 'assets/js/customizer.jsx', 
            array( 'wp-element', 'wp-components', 'wp-i18n', 'wp-api' ), 
            filemtime( GC_PLUGIN_DIR . 'assets/js/customizer.jsx' ), 
            true 
        );
        
        // Localize script with settings
        wp_localize_script( 'gc-customizer', 'gcSettings', array(
            'restUrl'    => esc_url_raw( rest_url() ),
            'nonce'      => wp_create_nonce( 'wp_rest' ),
            'pluginUrl'  => esc_url( GC_PLUGIN_URL ),
            'ajaxUrl'    => esc_url( admin_url( 'admin-ajax.php' ) ),
            'version'    => GC_PLUGIN_VERSION,
            'debug'      => defined( 'WP_DEBUG' ) && WP_DEBUG,
            'strings'    => array(
                'loading'           => __( 'Loading...', 'garment-customizer' ),
                'error'            => __( 'An error occurred', 'garment-customizer' ),
                'success'          => __( 'Success!', 'garment-customizer' ),
                'addToCart'        => __( 'Add to Cart', 'garment-customizer' ),
                'requestQuote'     => __( 'Request Quote', 'garment-customizer' ),
                'saveCustomization' => __( 'Save Customization', 'garment-customizer' ),
                'selectGarment'    => __( 'Select a garment', 'garment-customizer' ),
                'customizeGarment' => __( 'Customize your garment', 'garment-customizer' ),
            ),
        ) );
        
        // Add inline styles for better integration
        wp_add_inline_style( 'gc-tailwind', $this->get_inline_styles() );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts( $hook ) {
        // Only load on our admin pages
        if ( strpos( $hook, 'garment-customizer' ) === false ) {
            return;
        }
        
        wp_enqueue_style( 'gc-admin', GC_PLUGIN_URL . 'assets/css/admin.css', array(), GC_PLUGIN_VERSION );
        wp_enqueue_script( 'gc-admin', GC_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), GC_PLUGIN_VERSION, true );
        
        wp_localize_script( 'gc-admin', 'gcAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'gc_admin_nonce' ),
        ) );
    }
    
    /**
     * Add meta tags for better SEO and social sharing
     */
    public function add_meta_tags() {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'garment_customizer' ) ) {
            return;
        }
        
        echo '<meta name="description" content="' . esc_attr__( 'Customize your garments with our interactive garment customizer. Choose colors, add text, upload logos, and see live previews.', 'garment-customizer' ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr__( 'Interactive garment customization tool', 'garment-customizer' ) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    }
    
    /**
     * Get inline styles for better theme integration
     */
    private function get_inline_styles() {
        return '
            .gc-customizer-wrapper {
                margin: 2rem 0;
                padding: 1rem;
                background: #ffffff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            @media (max-width: 768px) {
                .gc-customizer-wrapper {
                    margin: 1rem 0;
                    padding: 0.5rem;
                    border-radius: 4px;
                }
            }
        ';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Register custom post types
        if ( function_exists( 'gc_register_garment_post_type' ) ) {
            gc_register_garment_post_type();
        }
        if ( function_exists( 'gc_register_rfq_post_type' ) ) {
            gc_register_rfq_post_type();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        $this->set_default_options();
        
        // Log activation
        error_log( 'Garment Customizer plugin activated successfully' );
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any cached data
        wp_cache_flush();
        
        // Log deactivation
        error_log( 'Garment Customizer plugin deactivated' );
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove plugin options
        delete_option( 'gc_settings' );
        delete_option( 'gc_version' );
        
        // Remove user meta
        delete_metadata( 'user', 0, '_gc_cart', '', true );
        
        // Remove custom post types and their data
        $garments = get_posts( array( 'post_type' => 'garment', 'numberposts' => -1 ) );
        foreach ( $garments as $garment ) {
            wp_delete_post( $garment->ID, true );
        }
        
        $rfqs = get_posts( array( 'post_type' => 'rfq', 'numberposts' => -1 ) );
        foreach ( $rfqs as $rfq ) {
            wp_delete_post( $rfq->ID, true );
        }
        
        // Drop custom tables if any
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gc_customizations" );
        
        // Clear any cached data
        wp_cache_flush();
    }
    
    /**
     * Create custom database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Customizations table for complex data
        $table_name = $wpdb->prefix . 'gc_customizations';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            garment_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT 0,
            session_id varchar(255) DEFAULT '',
            customization_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY garment_id (garment_id),
            KEY user_id (user_id),
            KEY session_id (session_id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_settings = array(
            'enable_logo_validation' => true,
            'max_logo_size'         => 5242880, // 5MB
            'allowed_logo_types'    => array( 'jpg', 'jpeg', 'png', 'gif', 'svg' ),
            'enable_cart'           => true,
            'enable_rfq'            => true,
            'admin_email'           => get_option( 'admin_email' ),
            'currency_symbol'       => '$',
            'enable_live_preview'   => true,
        );
        
        add_option( 'gc_settings', $default_settings );
        add_option( 'gc_version', GC_PLUGIN_VERSION );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Garment Customizer', 'garment-customizer' ),
            __( 'Garment Customizer', 'garment-customizer' ),
            'manage_options',
            'garment-customizer',
            array( $this, 'admin_page' ),
            'dashicons-admin-appearance',
            30
        );
        
        add_submenu_page(
            'garment-customizer',
            __( 'Settings', 'garment-customizer' ),
            __( 'Settings', 'garment-customizer' ),
            'manage_options',
            'garment-customizer-settings',
            array( $this, 'settings_page' )
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Garment Customizer', 'garment-customizer' ); ?></h1>
            <div class="card">
                <h2><?php esc_html_e( 'Welcome to Garment Customizer', 'garment-customizer' ); ?></h2>
                <p><?php esc_html_e( 'Use the shortcode [garment_customizer] to display the customizer on any page or post.', 'garment-customizer' ); ?></p>
                <h3><?php esc_html_e( 'Quick Stats', 'garment-customizer' ); ?></h3>
                <ul>
                    <li><?php printf( esc_html__( 'Total Garments: %d', 'garment-customizer' ), wp_count_posts( 'garment' )->publish ); ?></li>
                    <li><?php printf( esc_html__( 'Total RFQs: %d', 'garment-customizer' ), wp_count_posts( 'rfq' )->publish ); ?></li>
                    <li><?php printf( esc_html__( 'Plugin Version: %s', 'garment-customizer' ), GC_PLUGIN_VERSION ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page content
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Garment Customizer Settings', 'garment-customizer' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'gc_settings' );
                do_settings_sections( 'gc_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Initialize admin settings
     */
    public function admin_init() {
        register_setting( 'gc_settings', 'gc_settings', array( $this, 'sanitize_settings' ) );
        
        add_settings_section(
            'gc_general_settings',
            __( 'General Settings', 'garment-customizer' ),
            array( $this, 'general_settings_callback' ),
            'gc_settings'
        );
        
        add_settings_field(
            'enable_logo_validation',
            __( 'Enable Logo Validation', 'garment-customizer' ),
            array( $this, 'checkbox_field_callback' ),
            'gc_settings',
            'gc_general_settings',
            array( 'field' => 'enable_logo_validation' )
        );
    }
    
    /**
     * General settings section callback
     */
    public function general_settings_callback() {
        echo '<p>' . esc_html__( 'Configure general settings for the Garment Customizer plugin.', 'garment-customizer' ) . '</p>';
    }
    
    /**
     * Checkbox field callback
     */
    public function checkbox_field_callback( $args ) {
        $settings = get_option( 'gc_settings' );
        $value = isset( $settings[ $args['field'] ] ) ? $settings[ $args['field'] ] : false;
        echo '<input type="checkbox" name="gc_settings[' . esc_attr( $args['field'] ) . ']" value="1" ' . checked( 1, $value, false ) . ' />';
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();
        
        if ( isset( $input['enable_logo_validation'] ) ) {
            $sanitized['enable_logo_validation'] = (bool) $input['enable_logo_validation'];
        }
        
        return $sanitized;
    }

    /**
     * Create required pages for the customizer
     */
    public function create_customizer_pages() {
        // Create Shop page if it doesn't exist
        $shop_page = get_page_by_path('garment-shop');
        if (!$shop_page) {
            wp_insert_post(
                array(
                    'post_title'     => esc_html__('Garment Shop', 'garment-customizer'),
                    'post_content'   => '[garment_customizer_shop]',
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_name'      => 'garment-shop'
                )
            );
        }

        // Create Customizer page if it doesn't exist
        $customizer_page = get_page_by_path('garment-customizer');
        if (!$customizer_page) {
            wp_insert_post(
                array(
                    'post_title'     => esc_html__('Customize Garment', 'garment-customizer'),
                    'post_content'   => '[garment_customizer]',
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_name'      => 'garment-customizer'
                )
            );
        }

        // Create Cart page if it doesn't exist
        $cart_page = get_page_by_path('garment-cart');
        if (!$cart_page) {
            wp_insert_post(
                array(
                    'post_title'     => esc_html__('Shopping Cart', 'garment-customizer'),
                    'post_content'   => '[garment_customizer_cart]',
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_name'      => 'garment-cart'
                )
            );
        }

        // Create Request for Quote page if it doesn't exist
        $rfq_page = get_page_by_path('request-quote');
        if (!$rfq_page) {
            wp_insert_post(
                array(
                    'post_title'     => esc_html__('Request for Quote', 'garment-customizer'),
                    'post_content'   => '[garment_customizer_rfq]',
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_name'      => 'request-quote'
                )
            );
        }

        // Flush rewrite rules to ensure new pages are accessible
        flush_rewrite_rules();
    }
    
    /**
     * AJAX handler for getting garments
     */
    public function ajax_get_garments() {
        check_ajax_referer( 'gc_admin_nonce', 'nonce' );
        
        $garments = get_posts( array(
            'post_type'      => 'garment',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ) );
        
        wp_send_json_success( $garments );
    }
}

/**
 * Shortcode to display the customizer
 */
function gc_customizer_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'theme' => 'default',
        'width' => '100%',
        'height' => 'auto',
    ), $atts, 'garment_customizer' );
    
    $wrapper_style = sprintf( 
        'width: %s; height: %s;', 
        esc_attr( $atts['width'] ), 
        esc_attr( $atts['height'] ) 
    );
    
    ob_start();
    ?>
    <div class="gc-customizer-wrapper" style="<?php echo $wrapper_style; ?>">
        <div id="gc-customizer-root" data-theme="<?php echo esc_attr( $atts['theme'] ); ?>">
            <div class="gc-loading">
                <p><?php esc_html_e( 'Loading Garment Customizer...', 'garment-customizer' ); ?></p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'garment_customizer', 'gc_customizer_shortcode' );

/**
 * Initialize the plugin
 */
function gc_init() {
    return GarmentCustomizer::get_instance();
}

// Start the plugin
add_action( 'plugins_loaded', 'gc_init' );

/**
 * Plugin compatibility checks
 */
function gc_check_compatibility() {
    if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            esc_html_e( 'Garment Customizer requires PHP 7.4 or higher.', 'garment-customizer' );
            echo '</p></div>';
        });
        return false;
    }
    
    if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            esc_html_e( 'Garment Customizer requires WordPress 5.0 or higher.', 'garment-customizer' );
            echo '</p></div>';
        });
        return false;
    }
    
    return true;
}

// Check compatibility on admin init
add_action( 'admin_init', 'gc_check_compatibility' );
