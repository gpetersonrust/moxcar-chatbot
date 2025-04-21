<?php
/**
 * Class Moxcar_Chatbot_Admin_Pages
 *
 * @package Moxcar_Chatbot
 * @since 1.0.0
 * @plugin Moxcar Chatbot
 */

defined( 'ABSPATH' ) || exit;

class Moxcar_Chatbot_Admin_Pages {

    /**
     * Constructor.
     * DO NOT add hook registrations here — use the centralized hook registrar.
     */
    public function __construct() {
        // Optionally set up internal properties or services.
    }

 

    /**
     * Register admin pages in the WordPress dashboard.
     *
     * @return void
     */
    public function register_admin_pages() {
        $menus = [
            [
                'function' => 'add_menu_page',
                'args'     => [
                    'page_title' => __( 'Moxcar Chatbot', 'moxcar-chatbot' ),
                    'menu_title' => __( 'Moxcar Chatbot', 'moxcar-chatbot' ),
                    'capability' => 'manage_options',
                    'menu_slug'  => 'moxcar-chatbot',
                    'callback'   => [ $this, 'render_admin_page' ],
                    'icon_url'   => 'dashicons-format-chat',
                    'position'   => 20,
                ],
                'submenus' => [
                    [
                        'page_title' => __( 'Settings', 'moxcar-chatbot' ),
                        'menu_title' => __( 'Settings', 'moxcar-chatbot' ),
                        'capability' => 'manage_options',
                        'menu_slug'  => 'moxcar-chatbot-settings',
                        'callback'   => [ $this, 'render_admin_page' ],
                    ],
                    [
                        'page_title' => __( 'Logs', 'moxcar-chatbot' ),
                        'menu_title' => __( 'Logs', 'moxcar-chatbot' ),
                        'capability' => 'manage_options',
                        'menu_slug'  => 'moxcar-chatbot-logs',
                        'callback'   => [ $this, 'render_logs_page' ],
                    ],
                ],
            ],
            // [
            //     'function' => 'add_menu_page',
            //     'args'     => [
            //         'page_title' => __( 'AI Reports', 'moxcar-chatbot' ),
            //         'menu_title' => __( 'Reports', 'moxcar-chatbot' ),
            //         'capability' => 'manage_options',
            //         'menu_slug'  => 'moxcar-chatbot-reports',
            //         'callback'   => [ $this, 'render_reports_page' ],
            //         'icon_url'   => 'dashicons-chart-line',
            //         'position'   => 21,
            //     ],
            //     'submenus' => [], // or null/omit if no submenus
            // ]
        ];
    
        foreach ( $menus as $menu ) {
            call_user_func_array( $menu['function'], array_values( $menu['args'] ) );
    
            if ( ! empty( $menu['submenus'] ) ) {
                foreach ( $menu['submenus'] as $submenu ) {
                    call_user_func_array( 'add_submenu_page', array_merge(
                        [ $menu['args']['menu_slug'] ], // parent_slug
                        array_values( $submenu )
                    ));
                }
            }
        }
    }
    /**
     * Render the admin page content.
     *
     * @return void
     */
    public function render_admin_page() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['moxcar_chatbot_settings_action'] ) ) {
            if ( ! isset( $_POST['moxcar_chatbot_settings_nonce'] ) || ! wp_verify_nonce( $_POST['moxcar_chatbot_settings_nonce'], 'moxcar_chatbot_settings_action' ) ) {
                wp_die( __( 'Nonce verification failed.', 'moxcar-chatbot' ) );
            }

            if ( $_POST['moxcar_chatbot_settings_action'] === 'save_moxcar_chatbot_settings' ) {
                $openai_api_key = isset( $_POST['openai_api_key'] ) ? sanitize_text_field( $_POST['openai_api_key'] ) : '';
                $vector_store_name = isset( $_POST['vector_store_name'] ) ? sanitize_text_field( $_POST['vector_store_name'] ) : '';

                update_option( 'openai_api_key', $openai_api_key );
                update_option( 'vector_store_name', $vector_store_name );

                add_settings_error( 'moxcar_chatbot_settings', 'settings_updated', __( 'Settings saved.', 'moxcar-chatbot' ), 'updated' );
            }
        }
        settings_errors( 'moxcar_chatbot_settings' );
             
        ?>
        <div class="wrap"> 
            <h1
            class="moxcar-chatbot-settings-title"
            ><?php esc_html_e( 'Moxcar Chatbot Settings', 'moxcar-chatbot' ); ?></h1>
         <form
         class="moxcar-chatbot-settings-form"
            method="post"
            action="<?php echo esc_url( admin_url( 'admin.php?page=moxcar-chatbot-settings' ) ); ?>"
         >
            <input type="hidden" id="hidden_action" name="moxcar_chatbot_settings_action" value="save_moxcar_chatbot_settings" />
            <?php
            wp_nonce_field( 'moxcar_chatbot_settings_action', 'moxcar_chatbot_settings_nonce' );
            ?>
            <label for="openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'moxcar-chatbot' ); ?></label>
            <input type="text" id="openai_api_key" name="openai_api_key" value="<?php echo esc_attr( get_option( 'openai_api_key', '' ) ); ?>" />

            <label for="vector_store_name"><?php esc_html_e( 'Vector Store Name', 'moxcar-chatbot' ); ?></label>
            <input type="text" id="vector_store_name" name="vector_store_name" value="<?php echo esc_attr( get_option( 'vector_store_name', '' ) ); ?>" />

            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'moxcar-chatbot' ); ?>" />
         </form>
         </div>
   <?php }
}