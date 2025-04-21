<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://moxcar.com
 * @since      1.0.0
 *
 * @package    Moxcar_Chatbot
 * @subpackage Moxcar_Chatbot/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Moxcar_Chatbot
 * @subpackage Moxcar_Chatbot/includes
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Moxcar_Chatbot_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'moxcar-chatbot',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
