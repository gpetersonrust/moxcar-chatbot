<?php
/**
 * Class Moxcar_Chatbot_VectorStore
 *
 * Handles OpenAI Vector Store management via raw HTTP.
 *
 * @package Moxcar_Chatbot
 * @plugin Moxcar Chatbot
 */

defined( 'ABSPATH' ) || exit;

class Moxcar_Chatbot_VectorStore {

	protected $api_key;
	protected $api_base = 'https://api.openai.com/v1/vector_stores';

	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	protected function request( $method, $url, $body = [] ) {
		$headers = [
			'Authorization: Bearer ' . $this->api_key,
			'Content-Type: application/json',
			'OpenAI-Beta: assistants=v2',
		];

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		if ( ! empty( $body ) ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
		}

		$response = curl_exec( $ch );
		$code     = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $code >= 200 && $code < 300 ) {
			return json_decode( $response, true );
		}

		return new WP_Error( 'openai_request_failed', 'OpenAI API request failed: ' . $response );
	}

	public function create_vector_store( $name = 'Moxcar KB', $file_ids = [] ) {
		$url  = $this->api_base;
		$body = [
			'name'     => $name,
			'file_ids' => $file_ids,
		];
		return $this->request( 'POST', $url, $body );
	}

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
	 * Retrieves the ID of a vector store by its name or creates a new one if it doesn't exist.
	 *
	 * This method first attempts to fetch a list of existing vector stores from the API.
	 * If a store with the specified name (`$target_name`) is found, its ID is returned.
	 * If no matching store is found, a new vector store is created using the `create_vector_store` method,
	 * and the ID of the newly created store is returned.
	 *
	 * @param string $target_name The name of the vector store to retrieve or create.
	 * @param array  $file_ids    Optional. An array of file IDs to associate with the vector store during creation.
	 *                            Defaults to an empty array.
	 *
	 * @return string|WP_Error The ID of the vector store if successful, or a WP_Error object on failure.
	 *
	 * @throws WP_Error If the API request to retrieve the vector store list fails or if the creation of a new
	 *                  vector store fails.
	 */
	public function get_or_create_vector_store_id_by_name( $target_name, $file_ids = [] ) {
		$url      = $this->api_base . '?limit=100'; // Fetch up to 100 stores
		$response = $this->request( 'GET', $url ); // Get the list of vector stores
      
		if ( is_wp_error( $response ) || empty( $response['data'] ) ) { // Check for errors
			return new WP_Error( 'vectorstore_list_error', 'Failed to retrieve vector store list.' );
		}

		foreach ( $response['data'] as $store ) { //  
		 
			if ( isset( $store['name'] ) && $store['name'] === $target_name ) { // Check if the store name matches
				return $store['id']; // Return the ID of the existing store
			}
		}

	 
		// If the store doesn't exist, create it
		$create_response = $this->create_vector_store( $target_name, $file_ids ); // Create a new vector store

		if ( is_wp_error( $create_response ) || empty( $create_response['id'] ) ) {
			return new WP_Error( 'vectorstore_create_error', 'Failed to create vector store.' );
		}

		return $create_response['id']; // Return the ID of the newly created store
	}
}
