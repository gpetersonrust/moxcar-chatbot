<?php
/**
 * Class Moxcar_Chatbot_VectorStore
 *
 * Handles OpenAI Vector Store management and file uploads via raw HTTP.
 *
 * @package Moxcar_Chatbot
 * @plugin  Moxcar Chatbot
 */

defined( 'ABSPATH' ) || exit;

class Moxcar_Chatbot_VectorStore {

	/**
	 * OpenAI API key.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * Base URL for vector stores.
	 *
	 * @var string
	 */
	protected $api_base = 'https://api.openai.com/v1/vector_stores';

	/**
	 * Constructor.
	 *
	 * @param string $api_key Your OpenAI API key.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Internal HTTP request handler.
	 *
	 * Automatically switches between JSON and multipart form-data based on body contents.
	 *
	 * @param string    $method  HTTP method (GET, POST, DELETE).
	 * @param string    $url     Full endpoint URL.
	 * @param array     $body    Request payload.
	 * @param string[]  $headers Additional headers.
	 *
	 * @return array|WP_Error    Decoded response array or WP_Error on failure.
	 */
	protected function request( $method, $url, $body = [], $headers = [] ) {
		// Merge default auth header.
		$default_headers = [
			'Authorization: Bearer ' . $this->api_key,
		];
		$headers = array_merge( $default_headers, $headers );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		if ( ! empty( $body ) ) {
			// Detect file uploads.
			$has_file = false;
			foreach ( $body as $v ) {
				if ( $v instanceof CURLFile ) {
					$has_file = true;
					break;
				}
			}

			if ( $has_file ) {
				// Multipart form-data; let cURL set the boundary header.
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
			} else {
				// JSON payload.
				$headers[] = 'Content-Type: application/json';
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
			}
		}

		$response  = curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $http_code >= 200 && $http_code < 300 ) {
			return json_decode( $response, true );
		}

		return new WP_Error( 'openai_api_error', 'API request failed: ' . $response );
	}

	/**
	 * Search a vector store.
	 *
	 * @param string $vector_store_id The store ID.
	 * @param string $query           Text query.
	 * @param int    $max_results     Maximum results to return.
	 *
	 * @return array|WP_Error
	 */
	public function search( $vector_store_id, $query, $max_results = 5 ) {
		$url  = "{$this->api_base}/{$vector_store_id}/search";
		$body = [
			'query'           => $query,
			'max_num_results' => $max_results,
			'rewrite_query'   => false,
		];
		return $this->request( 'POST', $url, $body );
	}

	/**
	 * Get or create a vector store by name.
	 *
	 * @param string $target_name Name of the store.
	 * @param array  $file_ids    Optional file IDs to attach on creation.
	 *
	 * @return string|WP_Error
	 */
	public function get_or_create_vector_store_id_by_name( $target_name, $file_ids = [] ) {
		$url      = $this->api_base . '?limit=100';
		$response = $this->request( 'GET', $url );

		if ( is_wp_error( $response ) || empty( $response['data'] ) ) {
			return new WP_Error( 'vectorstore_list_error', 'Failed to retrieve vector store list.' );
		}

		foreach ( $response['data'] as $store ) {
			if ( isset( $store['name'] ) && $store['name'] === $target_name ) {
				return $store['id'];
			}
		}

		// Create a new store if none found.
		$body   = [
			'name'     => $target_name,
			'file_ids' => $file_ids,
		];
		$create = $this->request( 'POST', $this->api_base, $body );

		if ( is_wp_error( $create ) || empty( $create['id'] ) ) {
			return new WP_Error( 'vectorstore_create_error', 'Failed to create vector store.' );
		}

		return $create['id'];
	}

	/**
	 * Add files to an existing vector store.
	 *
	 * @param string $vector_store_id Store ID.
	 * @param array  $file_ids        Array of file IDs.
	 *
	 * @return array|WP_Error
	 */
	public function add_files_to_vector_store( $vector_store_id, $file_ids ) {
		$url  = "{$this->api_base}/{$vector_store_id}/file_batches";
		$body = [
			'file_ids' => $file_ids,
			'chunking_strategy' => [
				'type'   => 'static',
				'static' => [
					'max_chunk_size_tokens' => 800,
					'chunk_overlap_tokens'  => 400,
				],
			],
		];
		return $this->request( 'POST', $url, $body );
	}

	/**
	 * Upload a file to OpenAI's Files API.
	 *
	 * @param array $file The $_FILES['file'] array.
	 *
	 * @return array|WP_Error
	 */
	public function upload_file( $file ) {
		if ( empty( $file['tmp_name'] ) || ! file_exists( $file['tmp_name'] ) ) {
			return new WP_Error( 'invalid_file', 'The uploaded file is invalid or missing.' );
		}

		$curl_file = curl_file_create(
			$file['tmp_name'],
			$file['type'] ?? mime_content_type( $file['tmp_name'] ),
			$file['name']
		);

		$body = [
			'file'    => $curl_file,
			'purpose' => 'assistants',
		];

		// No manual Content-Type header here; cURL will handle boundary.
		$url = 'https://api.openai.com/v1/files';
		return $this->request( 'POST', $url, $body );
	}


	/**
 * Deletes a file from the OpenAI vector store.
 *
 * @param string $file_id The ID of the file to delete.
 * @return array|WP_Error The response from the OpenAI API or a WP_Error on failure.
 */
public function delete_file( $vector_store_id, $file_id ) {
	// First, delete the file from the vector store.
	$vector_store_url = "https://api.openai.com/v1/vector_stores/{$vector_store_id}/files/{$file_id}";
	$response = $this->request( 'DELETE', $vector_store_url );

	if ( is_wp_error( $response ) ) {
		return $response; // Return the error if the deletion from the vector store fails.
	}

	// Then, delete the file itself.
	$file_url = "https://api.openai.com/v1/files/{$file_id}";
	return $this->request( 'DELETE', $file_url );
}

}
