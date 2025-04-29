<?php
/**
 * Class Moxcar_Chatbot_QA
 *
 * This class handles the retrieval phase of our chatbot system.
 * Given a user's question (query), it talks to OpenAI's Vector Store
 * and pulls back relevant matching documents or knowledge snippets.
 *
 * Later on, we can rerank or summarize those documents.
 *
 * @package Moxcar_Chatbot
 * @plugin Moxcar Chatbot
 */

defined( 'ABSPATH' ) || exit;

class Moxcar_Chatbot_QA {

    /**
     * Your OpenAI API Key.
     *
     * @var string
     */
    protected $api_key;

    /**
     * The Vector Store ID you want to query against.
     *
     * @var string
     */
    protected $vector_store_id;

    /**
     * Constructor.
     *
     * When we make a new Retrieval object, we pass it the API Key
     * and the Vector Store ID we want to use.
     *
     * @param string $api_key         OpenAI API key (kept secure).
     * @param string $vector_store_id The ID of the vector store to search inside.
     */
    public function __construct( $args = [] ) {
        $this->api_key               = $args['api_key'] ?? '';
        $this->vector_store_id       = $args['vector_store_id'] ?? '';
        $this->model                 = $args['open_ai_model'] ?? 'gpt-4o-mini';
        $this->assistant_instructions = $args['assistant_instructions'] ?? '';

        $this->thread_id = $args['thread_id'] ?? '';
    }

    /**
     * Internal helper to make HTTP requests to OpenAI APIs.
     *
     * We use CURL under the hood for flexibility (e.g., handling headers, body, etc).
     * 
     * This method automatically adds authorization headers.
     *
     * @param string $method  HTTP method like GET, POST, DELETE.
     * @param string $url     Full URL endpoint.
     * @param array  $body    Optional: request payload for POSTs.
     * @param array  $headers Optional: extra headers to merge in.
     *
     * @return array|WP_Error Decoded JSON response or WP_Error on failure.
     */
    protected function request( $method, $url, $body = [], $headers = [] ) {
        $default_headers = [
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2', // Required for Assistant v2 and Vector Store APIs
        ];
        $headers = array_merge( $default_headers, $headers );

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

        // If there's a body payload, send it as JSON
        if ( ! empty( $body ) ) {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
        }

        $response  = curl_exec( $ch );
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

        if ( $http_code >= 200 && $http_code < 300 ) {
            return json_decode( $response, true );
        }

        // Return a WordPress WP_Error object on failure
        return new WP_Error( 'openai_retrieval_error', 'Retrieval request failed: ' . $response );
    }

  /**
 * Retrieves and formats documents from the vector store, applying a score threshold.
 *
 * Only documents scoring above the threshold are included.
 *
 * @param string $query        The user's input question.
 * @param int    $max_results  Maximum number of documents to fetch. Default 5.
 * @param float  $threshold    Minimum similarity score to accept (0.0 - 1.0). Default 0.7.
 *
 * @return array|WP_Error Filtered and formatted documents, or WP_Error on failure.
 */
public function retrieve_documents( $query, $max_results = 5, $threshold = 0.7 ) {
    $url = "https://api.openai.com/v1/vector_stores/{$this->vector_store_id}/search";

    $body = [
        'query'           => $query,
        'max_num_results' => $max_results,
        'rewrite_query'   => false,
    ];

    $response = $this->request( 'POST', $url, $body );

    if ( is_wp_error( $response ) || empty( $response['data'] ) ) {
        return $response;
    }

    $formatted_data = [];

    foreach ( $response['data'] as $document_data ) {
        $similarity_score = $document_data['score'] ?? 0;

        // 🛑 Skip documents that don't meet the threshold
        // if ( $similarity_score < $threshold ) {
        //     continue;
        // }

        $formatted_data[] = [
            'similarity_score' => $similarity_score,
            'attributes'       => $document_data['attributes'] ?? [],
            'content'          => $document_data['content'][0]['text'] ?? '',
        ];
    }

    return $formatted_data;
}
}
