<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://moxcar.com
 * @since      1.0.0
 *
 * @package    Moxcar_Chatbot
 * @subpackage Moxcar_Chatbot/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Moxcar_Chatbot
 * @subpackage Moxcar_Chatbot/admin
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Moxcar_Chatbot_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->wp_hash = $this->dynamic_hash(); 
        $this->admin_url =MOXCAR_CHATBOT_URL . "dist/chatbot-admin/";

	  

		 

	}

	/**
	 * Register the stylesheets for the chatbot-admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Moxcar_Chatbot_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Moxcar_Chatbot_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$admin_css = $this->admin_url . 'chatbot-admin' . $this->wp_hash . '.css';

 
		wp_enqueue_style( $this->plugin_name, $admin_css, array(), $this->version, 'all' );



	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Moxcar_Chatbot_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Moxcar_Chatbot_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$admin_js = $this->admin_url . 'chatbot-admin' . $this->wp_hash . '.js';
		wp_enqueue_script( $this->plugin_name, $admin_js, array( 'jquery' ), $this->version, false );


		wp_localize_script( $this->plugin_name, 'moxcarChatbotApi', [
			'apiUrl' => rest_url( 'moxcar-chatbot/v1' ),
			'nonce'  => wp_create_nonce( 'wp_rest' ),
		] );

	}


	function dynamic_hash() {
		$directory_path = MOXCAR_CHATBOT_DIR . 'dist/app/';
		$files = scandir($directory_path);
		$first_file = '';
		foreach ($files as $file) {
			if (!is_dir($directory_path . $file)) {
				$first_file = $file;
				break;
			}
		}
		$hash_parts = explode('-wp', $first_file);
		$hash = isset($hash_parts[1]) ? $hash_parts[1] : '';
		$hash_parts = explode('.', $hash);
		$hash = isset($hash_parts[0]) ? $hash_parts[0] : '';
		return '-wp' . $hash;
	}

}
