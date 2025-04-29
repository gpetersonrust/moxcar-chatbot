<?php

/**
 * Class Moxcar_Chatbot_API
 *
 * @package Moxcar_Chatbot
 * @since 1.0.0
 * @plugin Moxcar Chatbot
 */

defined( 'ABSPATH' ) || exit;



class Moxcar_Chatbot_API {

    /**
     * Constructor.
     * DO NOT add hook registrations here — use the centralized hook registrar.
     */
    public function __construct( $vector_store, $vector_store_id) {
       $this->vector_store = $vector_store;
       $this->vector_store_id = $vector_store_id;

     
    }

    /**
     * Initialize plugin hooks (actions/filters).
     * This will be called by the loader, not from inside this class.
     */
    public function init_hooks() {
        // Example:
        // add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * Register REST API routes.
     *
     * @return void
     */
    public function register_routes() {
        // Example:
        register_rest_route( 'moxcar-chatbot/v1', '/example', [
            'methods'  => 'GET',
            'callback' => [ $this, 'handle_example_request' ],
        ] );

        register_rest_route( 'moxcar-chatbot/v1', '/upload-file', [
            'methods'  => 'POST',
            'callback' => [ $this, 'handle_upload_file_request' ],
            'permission_callback' => '__return_true',
        ] );

        register_rest_route( 'moxcar-chatbot/v1', '/delete-file', [
            'methods'             => 'DELETE',
            'callback'            => [ $this, 'handle_delete_file_request' ],
            'permission_callback' => '__return_true',
        ] );
    }

    /**
     * Handle example REST API request.
     *
     * @param WP_REST_Request $request The REST API request object.
     * @return WP_REST_Response
     */
    public function handle_example_request( $request ) {
        // Example response:
        return new WP_REST_Response( [ 'message' => 'Hello, world!' ], 200 );
    }



   /**
 * Handles uploading a file via REST and attaching it to a vector store.
 *
 * This route expects a POST request with a file under the `file` key (e.g. via drag-and-drop).
 * It validates the REST nonce and file type, uploads the file to OpenAI using their /v1/files endpoint,
 * attaches it to a specified vector store, and saves metadata to the `knowledge_base_files` option.
 *
 * This avoids using WordPress's Media Library and is purpose-built for vector search content.
 *
 * @param WP_REST_Request $request The incoming request object from WordPress REST API.
 * @return WP_REST_Response A structured JSON response indicating success or failure.
 */
public function handle_upload_file_request( WP_REST_Request $request ) {
  
	// 🔐 Validate REST nonce to prevent CSRF
	if ( ! isset( $_SERVER['HTTP_X_WP_NONCE'] ) || ! wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'], 'wp_rest' ) ) {
		return new WP_REST_Response( [ 'error' => 'Invalid or missing nonce.' ], 403 );
	}

	// 📁 Check that a file has been uploaded via $_FILES
	if ( empty( $_FILES['file'] ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
		return new WP_REST_Response( [ 'error' => 'No file uploaded.' ], 400 );
	}
  
	$file = $_FILES['file'];

  

	// 🧹 Clean the filename to prevent unwanted characters or path traversal
	$file['name'] = sanitize_file_name( $file['name'] );


	// ✅ Optional but important: enforce file type restrictions (e.g., .txt only)
	$allowed_mime_types = [ 'text/plain' ];
	$file_type = wp_check_filetype( $file['name'] );

	if ( ! in_array( $file_type['type'], $allowed_mime_types, true ) ) {
		return new WP_REST_Response( [ 'error' => 'Only .txt files are allowed.' ], 415 );
	}
  
	// 📤 Upload the file to OpenAI via the vector store class
	$response = $this->vector_store->upload_file( $file );

  
  
	// 🛑 Check if the upload failed or didn't return a file ID
	if ( is_wp_error( $response ) || empty( $response['id'] ) ) {
		return new WP_REST_Response( [ 'error' => 'File upload to OpenAI failed.' ], 500 );
	}

	$file_id = $response['id'];

	// 🔗 Attach this file to the existing vector store (already passed to this class)
	$attach = $this->vector_store->add_files_to_vector_store( $this->vector_store_id, [ $file_id ] );

	if ( is_wp_error( $attach ) ) {
		return $attach; // Return the WP_Error directly
	}

	// 💾 Save the file metadata (id, name, size, date) to the WordPress options table
	$saved_files   = get_option( 'knowledge_base_files', [] );

	$saved_files[] = [
		'id'   => $file_id,
		'name' => $file['name'],
		'size' => size_format( $file['size'], 2 ),
		'date' => date( 'Y-m-d' ),
	];

	update_option( 'knowledge_base_files', $saved_files );

	// ✅ Send back a success response to the client
	return new WP_REST_Response( [
		'success'  => true,
		'file_id'  => $file_id,
		'filename' => $file['name'],
	], 200 );
}

/**
 * Handles deleting a file from the vector store and updating WordPress options.
 *
 * @param WP_REST_Request $request The REST API request object.
 * @return WP_REST_Response
 */
public function handle_delete_file_request( WP_REST_Request $request ) {
    // 🔐 Validate REST nonce to prevent CSRF
    if ( ! isset( $_SERVER['HTTP_X_WP_NONCE'] ) || ! wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'], 'wp_rest' ) ) {
        return new WP_REST_Response( [ 'error' => 'Invalid or missing nonce.' ], 403 );
    }

    // 🆔 Retrieve the file ID from the request
    $file_id = $request->get_param( 'file_id' );

    if ( empty( $file_id ) ) {
        return new WP_REST_Response( [ 'error' => 'Missing file ID.' ], 400 );
    }

    // 🗑️ Delete the file from OpenAI
    $vector_store_id = $this->vector_store_id;
    $delete_response = $this->vector_store->delete_file( $vector_store_id, $file_id );

    if ( is_wp_error( $delete_response ) ) {
        return new WP_REST_Response( [ 'error' => 'Failed to delete file from OpenAI.' ], 500 );
    }

    // 🧹 Remove the file metadata from WordPress options
    $saved_files = get_option( 'knowledge_base_files', [] );
    $updated_files = array_filter( $saved_files, function( $file ) use ( $file_id ) {
        return $file['id'] !== $file_id;
    } );

    update_option( 'knowledge_base_files', $updated_files );

    // ✅ Send back a success response to the client
    return new WP_REST_Response( [
        'success' => true,
        'file_id' => $file_id,
    ], 200 );
}

}