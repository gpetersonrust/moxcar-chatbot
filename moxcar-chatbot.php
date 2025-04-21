<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://moxcar.com
 * @since             1.0.0
 * @package           Moxcar_Chatbot
 *
 * @wordpress-plugin
 * Plugin Name:       Moxcar Chatbot
 * Plugin URI:        https://moxcar.com
 * Description:       Integrates a GPT‑driven chat assistant into WordPress, using your site’s content and custom Q&A for intelligent support.
 * Version:           1.0.0
 * Author:            Gino Peterson
 * Author URI:        https://moxcar.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       moxcar-chatbot
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MOXCAR_CHATBOT_VERSION', '1.0.0' );

// Define plugin directory path and URL
define( 'MOXCAR_CHATBOT_DIR', plugin_dir_path( __FILE__ ) );
define( 'MOXCAR_CHATBOT_URL', plugin_dir_url( __FILE__ ) );

// Define constants for OpenAI API Key and Vector Store Name
define( 'MOXCAR_CHATBOT_OPENAI_API_KEY', get_option( 'openai_api_key', '' ) );
define( 'MOXCAR_CHATBOT_VECTOR_STORE_NAME', get_option( 'vector_store_name', '' ) );

// Automatically require all PHP files in the 'open-ai' folder
$open_ai_dir = MOXCAR_CHATBOT_DIR . 'open-ai/';
if ( is_dir( $open_ai_dir ) ) {
	$files = glob( $open_ai_dir . '*.php' );
	if ( $files ) {
		foreach ( $files as $file ) {
			require_once $file;
		}
	}
}
 


 
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-moxcar-chatbot-activator.php
 */
function activate_moxcar_chatbot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-moxcar-chatbot-activator.php';
	Moxcar_Chatbot_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-moxcar-chatbot-deactivator.php
 */
function deactivate_moxcar_chatbot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-moxcar-chatbot-deactivator.php';
	Moxcar_Chatbot_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_moxcar_chatbot' );
register_deactivation_hook( __FILE__, 'deactivate_moxcar_chatbot' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-moxcar-chatbot.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_moxcar_chatbot() {

	$vector_store = new Moxcar_Chatbot_VectorStore( MOXCAR_CHATBOT_OPENAI_API_KEY );
	$vector_store_id = $vector_store->get_or_create_vector_store_id_by_name( MOXCAR_CHATBOT_VECTOR_STORE_NAME );
 	 
	$plugin = new Moxcar_Chatbot();
	$plugin->run();

}
run_moxcar_chatbot();
