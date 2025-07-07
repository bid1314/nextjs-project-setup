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
        <div class="gc-meta-section">
            <h4><?php esc_html_e( 'Layers Management', 'garment-customizer' ); ?></h4>
            <p class="description">
                <?php esc_html_e( 'Define customizable layers for this garment. Drag to reorder.', 'garment-customizer' ); ?>
            </p>
            <div id="gc-layers-container"></div>
            <button type="button" class="button gc-add-layer">
                <?php esc_html_e( 'Add Layer', 'garment-customizer' ); ?>
            </button>
            <textarea 
                id="gc_layers" 
                name="gc_layers" 
                style="display: none;"
            ><?php echo esc_textarea( $layers ); ?></textarea>
        </div>

        <div class="gc-meta-section">
            <h4><?php esc_html_e( 'Available Colors', 'garment-customizer' ); ?></h4>
            <p class="description">
                <?php esc_html_e( 'Define available colors for this garment.', 'garment-customizer' ); ?>
            </p>
            <div id="gc-colors-container"></div>
            <button type="button" class="button gc-add-color">
                <?php esc_html_e( 'Add Color', 'garment-customizer' ); ?>
            </button>
            <textarea 
                id="gc_colors" 
                name="gc_colors" 
                style="display: none;"
            ><?php echo esc_textarea( $colors ); ?></textarea>
        </div>

        <div class="gc-meta-section">
            <h4><?php esc_html_e( 'Default Customization State', 'garment-customizer' ); ?></h4>
            <p class="description">
                <?php esc_html_e( 'Set the default customization state for this garment.', 'garment-customizer' ); ?>
            </p>
            <textarea 
                id="gc_customization_state" 
                name="gc_customization_state" 
                class="large-text" 
                rows="6"
            ><?php echo esc_textarea( $customization_state ); ?></textarea>
            <p class="description">
                <?php esc_html_e( 'Enter a valid JSON object with customization defaults.', 'garment-customizer' ); ?>
            </p>
        </div>

        <div class="gc-meta-section">
            <h4><?php esc_html_e( 'Live Preview', 'garment-customizer' ); ?></h4>
            <div class="gc-preview">
                <div id="gc-preview-container">
                    <?php esc_html_e( 'Preview will be shown here as you configure layers and colors.', 'garment-customizer' ); ?>
                </div>
            </div>
        </div>
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
        // Only load on garment post type screens and our custom admin pages
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook && 
             strpos( $hook, 'gc-' ) === false ) {
            return;
        }

        global $post_type;
        if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
            if ( 'garment' !== $post_type ) {
                return;
            }
        }

        // Enqueue WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // Enqueue jQuery UI for sortable functionality
        wp_enqueue_script( 'jquery-ui-sortable' );

        // Enqueue our custom admin styles and scripts
        wp_enqueue_style( 
            'gc-admin-style', 
            GC_PLUGIN_URL . 'assets/css/admin.css', 
            array( 'wp-color-picker' ), 
            GC_PLUGIN_VERSION 
        );
        
        wp_enqueue_script( 
            'gc-admin-script', 
            GC_PLUGIN_URL . 'assets/js/admin.js', 
            array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), 
            GC_PLUGIN_VERSION, 
            true 
        );

        // Pass data to our script
        wp_localize_script( 'gc-admin-script', 'gcAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'gc_admin_nonce' ),
            'strings' => array(
                'confirmDelete' => __( 'Are you sure you want to delete this item?', 'garment-customizer' ),
                'savingChanges' => __( 'Saving changes...', 'garment-customizer' ),
                'changesSaved' => __( 'Changes saved successfully.', 'garment-customizer' ),
                'errorSaving' => __( 'Error saving changes.', 'garment-customizer' ),
            )
        ));
    }
}

new GC_Admin_UI();
?>
