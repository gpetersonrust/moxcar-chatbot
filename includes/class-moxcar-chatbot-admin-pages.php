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
                        'page_title' => __( 'Knowledgebase Files', 'moxcar-chatbot' ),
                        'menu_title' => __( 'Knowledgebase Files', 'moxcar-chatbot' ),
                        'capability' => 'manage_options',
                        'menu_slug'  => 'moxcar-chatbot-knowledgebase-files',
                        'callback'   => [ $this, 'render_knowledgebase_files_page' ],
                    ],
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
        include MOXCAR_CHATBOT_DIR . 'views/admin/admin-page.php';  
    }



/**
 * Render the Knowledgebase Files admin page content.
 *
 * @return void
 */
public function render_knowledgebase_files_page() {
    include MOXCAR_CHATBOT_DIR . 'views/admin/knowledgeable-base-file.php';
}
}