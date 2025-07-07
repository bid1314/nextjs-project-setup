<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Admin UI for Garment Customizer Plugin
 * Provides custom admin pages and meta boxes for Garments and RFQs
 */

class GC_Admin_UI {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_garment_meta_boxes' ) );
        add_action( 'save_post_garment', array( $this, 'save_garment_meta' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function add_admin_menus() {
        add_menu_page(
            __( 'Garment Customizer', 'garment-customizer' ),
            __( 'Garment Customizer', 'garment-customizer' ),
            'manage_options',
            'gc-dashboard',
            array( $this, 'render_dashboard_page' ),
            'dashicons-admin-appearance',
            30
        );

        add_submenu_page(
            'gc-dashboard',
            __( 'Garments', 'garment-customizer' ),
            __( 'Garments', 'garment-customizer' ),
            'manage_options',
            'gc-garment-customizer',
            array( $this, 'render_garment_customizer_page' )
        );

        add_submenu_page(
            'gc-dashboard',
            __( 'Add New Garment', 'garment-customizer' ),
            __( 'Add New Garment', 'garment-customizer' ),
            'manage_options',
            'gc-add-new-garment',
            array( $this, 'render_garment_customizer_page' )
        );

        add_submenu_page(
            'gc-dashboard',
            __( 'Request for Quotes', 'garment-customizer' ),
            __( 'Request for Quotes', 'garment-customizer' ),
            'manage_options',
            'edit.php?post_type=rfq'
        );

        add_submenu_page(
            'gc-dashboard',
            __( 'Settings', 'garment-customizer' ),
            __( 'Settings', 'garment-customizer' ),
            'manage_options',
            'gc-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function create_customizer_pages() {
        $pages = array(
            array(
                'post_title'   => 'Garment Customizer',
                'post_name'    => 'garment-customizer',
                'post_content' => '[garment_customizer]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ),
            array(
                'post_title'   => 'Garment Shop',
                'post_name'    => 'garment-shop',
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ),
        );

        foreach ( $pages as $page ) {
            $existing_page = get_page_by_path( $page['post_name'] );
            if ( ! $existing_page ) {
                wp_insert_post( $page );
            }
        }
    }

    public function render_garment_customizer_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Garment Customizer', 'garment-customizer' ); ?></h1>
            <div id="gc-garment-customizer-root"></div>
        </div>
        <?php
    }

    public function render_dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Garment Customizer Dashboard', 'garment-customizer' ); ?></h1>
            <p><?php esc_html_e( 'Welcome to the Garment Customizer plugin dashboard.', 'garment-customizer' ); ?></p>
            <ul>
                <li><a href="<?php echo admin_url( 'edit.php?post_type=garment' ); ?>"><?php esc_html_e( 'Manage Garments', 'garment-customizer' ); ?></a></li>
                <li><a href="<?php echo admin_url( 'edit.php?post_type=rfq' ); ?>"><?php esc_html_e( 'Manage Request for Quotes', 'garment-customizer' ); ?></a></li>
                <li><a href="<?php echo admin_url( 'admin.php?page=gc-settings' ); ?>"><?php esc_html_e( 'Plugin Settings', 'garment-customizer' ); ?></a></li>
            </ul>
        </div>
        <?php
    }

    public function render_settings_page() {
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

    public function add_garment_meta_boxes() {
        add_meta_box(
            'gc_garment_customization',
            __( 'Garment Customization', 'garment-customizer' ),
            array( $this, 'render_garment_customization_meta_box' ),
            'garment',
            'normal',
            'high'
        );

        add_meta_box(
            'gc_garment_layers_colors',
            __( 'Garment Layers and Colors', 'garment-customizer' ),
            array( $this, 'render_garment_layers_colors_meta_box' ),
            'garment',
            'normal',
            'default'
        );
    }

    public function render_garment_customization_meta_box( $post ) {
        wp_nonce_field( 'gc_save_garment_customization', 'gc_garment_customization_nonce' );

        $color = get_post_meta( $post->ID, '_gc_color', true );
        $logo = get_post_meta( $post->ID, '_gc_logo', true );
        $text = get_post_meta( $post->ID, '_gc_text', true );

        ?>
        <p>
            <label for="gc_color"><?php esc_html_e( 'Color', 'garment-customizer' ); ?></label><br />
            <input type="text" id="gc_color" name="gc_color" value="<?php echo esc_attr( $color ); ?>" class="regular-text" />
        </p>
        <p>
            <label for="gc_logo"><?php esc_html_e( 'Logo URL', 'garment-customizer' ); ?></label><br />
            <input type="text" id="gc_logo" name="gc_logo" value="<?php echo esc_url( $logo ); ?>" class="regular-text" />
        </p>
        <p>
            <label for="gc_text"><?php esc_html_e( 'Custom Text', 'garment-customizer' ); ?></label><br />
            <textarea id="gc_text" name="gc_text" rows="4" class="large-text"><?php echo esc_textarea( $text ); ?></textarea>
        </p>
        <?php
    }

    public function render_garment_layers_colors_meta_box( $post ) {
        wp_nonce_field( 'gc_save_garment_layers_colors', 'gc_garment_layers_colors_nonce' );

        $layers = get_post_meta( $post->ID, '_gc_layers', true );
        $colors = get_post_meta( $post->ID, '_gc_colors', true );
        $customization_state = get_post_meta( $post->ID, '_gc_customization_state', true );

        ?>
        <p>
            <label for="gc_layers"><?php esc_html_e( 'Layers (JSON)', 'garment-customizer' ); ?></label><br />
            <textarea id="gc_layers" name="gc_layers" rows="6" class="large-text"><?php echo esc_textarea( $layers ); ?></textarea>
        </p>
        <p>
            <label for="gc_colors"><?php esc_html_e( 'Colors (JSON)', 'garment-customizer' ); ?></label><br />
            <textarea id="gc_colors" name="gc_colors" rows="6" class="large-text"><?php echo esc_textarea( $colors ); ?></textarea>
        </p>
        <p>
            <label for="gc_customization_state"><?php esc_html_e( 'Customization State (JSON)', 'garment-customizer' ); ?></label><br />
            <textarea id="gc_customization_state" name="gc_customization_state" rows="6" class="large-text"><?php echo esc_textarea( $customization_state ); ?></textarea>
        </p>
        <?php
    }

    public function save_garment_meta( $post_id, $post ) {
        if ( ! isset( $_POST['gc_garment_customization_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['gc_garment_customization_nonce'], 'gc_save_garment_customization' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( $post->post_type !== 'garment' ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['gc_color'] ) ) {
            update_post_meta( $post_id, '_gc_color', sanitize_text_field( $_POST['gc_color'] ) );
        }
        if ( isset( $_POST['gc_logo'] ) ) {
            update_post_meta( $post_id, '_gc_logo', esc_url_raw( $_POST['gc_logo'] ) );
        }
        if ( isset( $_POST['gc_text'] ) ) {
            update_post_meta( $post_id, '_gc_text', sanitize_textarea_field( $_POST['gc_text'] ) );
        }

        if ( ! isset( $_POST['gc_garment_layers_colors_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['gc_garment_layers_colors_nonce'], 'gc_save_garment_layers_colors' ) ) {
            return;
        }

        if ( isset( $_POST['gc_layers'] ) ) {
            update_post_meta( $post_id, '_gc_layers', wp_kses_post( $_POST['gc_layers'] ) );
        }
        if ( isset( $_POST['gc_colors'] ) ) {
            update_post_meta( $post_id, '_gc_colors', wp_kses_post( $_POST['gc_colors'] ) );
        }
        if ( isset( $_POST['gc_customization_state'] ) ) {
            update_post_meta( $post_id, '_gc_customization_state', wp_kses_post( $_POST['gc_customization_state'] ) );
        }
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'gc-garment-customizer' ) !== false || strpos( $hook, 'gc-add-new-garment' ) !== false || strpos( $hook, 'rfq' ) !== false || strpos( $hook, 'gc-settings' ) !== false ) {
            wp_enqueue_style( 'gc-admin-style', GC_PLUGIN_URL . 'assets/css/admin.css', array(), GC_PLUGIN_VERSION );
            wp_enqueue_script( 'gc-admin-script', GC_PLUGIN_URL . 'assets/js/customizer.jsx', array( 'wp-element', 'wp-components', 'wp-i18n', 'wp-api' ), GC_PLUGIN_VERSION, true );
        }
    }
}

new GC_Admin_UI();
?>
