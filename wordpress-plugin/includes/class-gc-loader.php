<?php
/**
 * Plugin Loader Class
 *
 * @package GarmentCustomizer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * GC_Loader class
 */
class GC_Loader {
    /**
     * The single instance of the class
     */
    private static $instance = null;

    /**
     * Main GC_Loader Instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define constants
     */
    private function define_constants() {
        $this->define('GC_VERSION', '1.0.0');
        $this->define('GC_PLUGIN_FILE', dirname(dirname(__FILE__)) . '/garment-customizer.php');
        $this->define('GC_PLUGIN_DIR', plugin_dir_path(dirname(dirname(__FILE__))));
        $this->define('GC_PLUGIN_URL', plugin_dir_url(dirname(dirname(__FILE__))));
        $this->define('GC_PLUGIN_BASENAME', plugin_basename(GC_PLUGIN_FILE));
        $this->define('GC_PLUGIN_PATH', dirname(dirname(__FILE__)) . '/');
    }

    /**
     * Define constant if not already defined
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Include required files
     */
    private function includes() {
        // Core includes - load these first as other files may depend on them
        include_once GC_PLUGIN_DIR . 'includes/custom-post-types.php';
        include_once GC_PLUGIN_DIR . 'includes/meta-fields.php';

        // Template system
        include_once GC_PLUGIN_DIR . 'includes/template-functions.php';
        include_once GC_PLUGIN_DIR . 'includes/template-hooks.php';
        include_once GC_PLUGIN_DIR . 'includes/template-functions-hooks.php';

        // Features
        include_once GC_PLUGIN_DIR . 'includes/rest-api.php';
        include_once GC_PLUGIN_DIR . 'includes/shopping-cart.php';
        include_once GC_PLUGIN_DIR . 'includes/custom-cart.php';
        include_once GC_PLUGIN_DIR . 'includes/request-for-quote.php';

        // Admin
        if (is_admin()) {
            include_once GC_PLUGIN_DIR . 'includes/admin-ui.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Plugin activation/deactivation
        register_activation_hook(GC_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(GC_PLUGIN_FILE, array($this, 'deactivate'));

        // Init plugin
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        add_action('init', array($this, 'init'), 0);

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Load plugin text domain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'garment-customizer',
            false,
            dirname(GC_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize features that require WordPress to be fully loaded
        do_action('garment_customizer_init');
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Main styles
        wp_enqueue_style(
            'gc-styles',
            GC_PLUGIN_URL . 'assets/css/customizer.css',
            array(),
            GC_VERSION
        );

        // Main script
        wp_enqueue_script(
            'gc-script',
            GC_PLUGIN_URL . 'assets/js/customizer.js',
            array('jquery', 'jquery-ui-draggable'),
            GC_VERSION,
            true
        );

        // Localize script
        wp_localize_script('gc-script', 'GC_DATA', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('garment-customizer/v1'),
            'nonce' => wp_create_nonce('gc_nonce'),
            'i18n' => array(
                'errorMessage' => __('An error occurred. Please try again.', 'garment-customizer'),
                'selectLogo' => __('Select Logo', 'garment-customizer'),
                'addToCart' => __('Add to Cart', 'garment-customizer'),
                'requestQuote' => __('Request Quote', 'garment-customizer')
            )
        ));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts() {
        $screen = get_current_screen();

        if (strpos($screen->id, 'garment-customizer') !== false || 
            $screen->post_type === 'garment' || 
            $screen->post_type === 'gc_quote') {
            
            wp_enqueue_style(
                'gc-admin-styles',
                GC_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                GC_VERSION
            );

            wp_enqueue_media();
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create necessary database tables
        $this->create_tables();

        // Create default pages
        $this->create_pages();

        // Clear permalinks
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create tables if needed
        $sql = array();

        // Example table creation
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gc_customizations (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            garment_id bigint(20) NOT NULL,
            customization_data longtext NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY garment_id (garment_id)
        ) $charset_collate;";

        foreach ($sql as $query) {
            dbDelta($query);
        }
    }

    /**
     * Create default pages
     */
    private function create_pages() {
        $pages = array(
            'customizer' => array(
                'title' => __('Garment Customizer', 'garment-customizer'),
                'content' => '<!-- wp:shortcode -->[garment_customizer]<!-- /wp:shortcode -->'
            ),
            'quote-request' => array(
                'title' => __('Request a Quote', 'garment-customizer'),
                'content' => '<!-- wp:shortcode -->[gc_quote_request]<!-- /wp:shortcode -->'
            )
        );

        foreach ($pages as $key => $page) {
            $page_id = wp_insert_post(array(
                'post_title' => $page['title'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
                'post_type' => 'page'
            ));

            if ($page_id) {
                update_option('gc_page_' . $key, $page_id);
            }
        }
    }
}
