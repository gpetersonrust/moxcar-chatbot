<?php
/**
 * Class Moxcar_Chatbot_FileManager
 *
 * This class handles file operations with the OpenAI API,
 * including uploading, listing, and deleting files.
 *
 * @package Moxcar_Chatbot
 */

defined( 'ABSPATH' ) || exit;

class Moxcar_Chatbot_FileManager {

    /**
     * Your OpenAI API key.
     *
     * @var string
     */
    protected $api_key;

    /**
     * The base URL for OpenAI's file API.
     *
     * @var string
     */
    protected $api_base = 'https://api.openai.com/v1/files';

    /**
     * Constructor to set the API key.
     *
     * @param string $api_key Your OpenAI API key.
     */
    public function __construct( $api_key ) {
        $this->api_key = $api_key;
    }

    /**
     * Makes an HTTP request to the OpenAI API.
     *
     * @param string $method  The HTTP method (GET, POST, DELETE).
     * @param string $url     The full URL to request.
     * @param array  $headers Additional headers to include.
     * @param mixed  $body    The body of the request, if any.
     *
     * @return array|WP_Error The response data or a WP_Error on failure.
     */
    protected function request( $method, $url, $headers = [], $body = null ) {
        // Default headers required for OpenAI API.
        $default_headers = [
            'Authorization: Bearer ' . $this->api_key,
        ];

        // Merge default headers with any additional headers.
        $headers = array_merge( $default_headers, $headers );

        // Initialize cURL session.
        $ch = curl_init();

        // Set cURL options.
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

        // If there's a body, include it in the request.
        if ( $body ) {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        }

        // Execute the request.
        $response = curl_exec( $ch );

        // Get the HTTP status code.
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

        // Close the cURL session.
        curl_close( $ch );

        // Check if the request was successful (status code 2xx).
        if ( $http_code >= 200 && $http_code < 300 ) {
            return json_decode( $response, true );
        }

        // If the request failed, return a WP_Error with the response.
        return new WP_Error( 'openai_api_error', 'API request failed: ' . $response );
    }

    /**
     * Uploads a file to the OpenAI API.
     *
     * @param string $file_path The full path to the file to upload.
     * @param string $purpose   The purpose of the file (e.g., 'fine-tune', 'assistants').
     *
     * @return array|WP_Error The response data or a WP_Error on failure.
     */
    public function upload_file( $file_path, $purpose = 'assistants' ) {
        // Check if the file exists.
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', 'The specified file does not exist.' );
        }

        // Create a CURLFile object for the file.
        $cfile = curl_file_create( $file_path );

        // Prepare the body of the request.
        $body = [
            'file'    => $cfile,
            'purpose' => $purpose,
        ];

        // Make the POST request to upload the file.
        return $this->request(
            'POST',
            $this->api_base,
            [ 'Content-Type: multipart/form-data' ],
            $body
        );
    }

    /**
     * Retrieves a list of files from the OpenAI API.
     *
     * @return array|WP_Error The list of files or a WP_Error on failure.
     */
    public function list_files() {
        // Make the GET request to retrieve the list of files.
        return $this->request( 'GET', $this->api_base );
    }

    /**
     * Deletes a file from the OpenAI API.
     *
     * @param string $file_id The ID of the file to delete.
     *
     * @return array|WP_Error The response data or a WP_Error on failure.
     */
    public function delete_file( $file_id ) {
        // Construct the URL for deleting the specific file.
        $url = $this->api_base . '/' . $file_id;

        // Make the DELETE request to remove the file.
        return $this->request( 'DELETE', $url );
    }
}
