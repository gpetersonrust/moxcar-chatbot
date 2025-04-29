<?php
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['moxcar_chatbot_settings_action'] ) ) {
    if ( ! isset( $_POST['moxcar_chatbot_settings_nonce'] ) || ! wp_verify_nonce( $_POST['moxcar_chatbot_settings_nonce'], 'moxcar_chatbot_settings_action' ) ) {
        wp_die( __( 'Nonce verification failed.', 'moxcar-chatbot' ) );
    }

    if ( $_POST['moxcar_chatbot_settings_action'] === 'save_moxcar_chatbot_settings' ) {
        $openai_api_key   = isset( $_POST['openai_api_key'] ) ? sanitize_text_field( $_POST['openai_api_key'] ) : '';
        $vector_store_name = isset( $_POST['vector_store_name'] ) ? sanitize_text_field( $_POST['vector_store_name'] ) : '';
        $open_ai_model    = isset( $_POST['open_ai_model'] ) ? sanitize_text_field( $_POST['open_ai_model'] ) : '';
        $assistant_instructions = isset( $_POST['assistant_instructions'] ) ? sanitize_textarea_field( $_POST['assistant_instructions'] ) : '';

        update_option( 'openai_api_key', $openai_api_key );
        update_option( 'vector_store_name', $vector_store_name );
        update_option( 'open_ai_model', $open_ai_model );
        update_option( 'assistant_instructions', $assistant_instructions );

        add_settings_error( 'moxcar_chatbot_settings', 'settings_updated', __( 'Settings saved.', 'moxcar-chatbot' ), 'updated' );
    }
}
settings_errors( 'moxcar_chatbot_settings' );
?>

<div class="wrap">
    <h1 class="moxcar-chatbot-settings-title"><?php esc_html_e( 'Moxcar Chatbot Settings', 'moxcar-chatbot' ); ?></h1>

    <form class="moxcar-chatbot-settings-form" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=moxcar-chatbot-settings' ) ); ?>">
        <input type="hidden" id="hidden_action" name="moxcar_chatbot_settings_action" value="save_moxcar_chatbot_settings" />
        <?php wp_nonce_field( 'moxcar_chatbot_settings_action', 'moxcar_chatbot_settings_nonce' ); ?>

        <label for="openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'moxcar-chatbot' ); ?></label>
        <input type="text" id="openai_api_key" name="openai_api_key" value="<?php echo esc_attr( get_option( 'openai_api_key', '' ) ); ?>" />

        <label for="vector_store_name"><?php esc_html_e( 'Vector Store Name', 'moxcar-chatbot' ); ?></label>
        <input type="text" id="vector_store_name" name="vector_store_name" value="<?php echo esc_attr( get_option( 'vector_store_name', '' ) ); ?>" />

        <label for="open_ai_model"><?php esc_html_e( 'OpenAI Model', 'moxcar-chatbot' ); ?></label>
        <select
        class="moxcar-chatbot-select"
        id="open_ai_model" name="open_ai_model">
            <?php
            $models = [
                'gpt-4o', 'gpt-4o-mini', 
                'gpt-4.1', 'gpt-4.1-mini', 'gpt-4.1-nano',
                'o3', 'o4-mini',
                'gpt-image-1'
            ];
            $selected_model = get_option( 'open_ai_model', '' );
            foreach ( $models as $model ) {
                printf(
                    '<option value="%1$s" %2$s>%1$s</option>',
                    esc_attr( $model ),
                    selected( $selected_model, $model, false )
                );
            }
            ?>
        </select>

        <label for="assistant_instructions"><?php esc_html_e( 'Assistant Instructions', 'moxcar-chatbot' ); ?></label>
        <textarea 
        class="moxcar-chatbot-textarea"
        id="assistant_instructions" name="assistant_instructions" rows="5" cols="50"><?php echo esc_textarea( get_option( 'assistant_instructions', '' ) ); ?></textarea>
        <small
        style="display:block; margin-top:4px;"
         
         id="assistant_instructions_text_length"
        ><span id="assistant_instruction_length_value"></span> words left</small>

        <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'moxcar-chatbot' ); ?>" />
    </form>
</div>


<script>
    const assistantInstructions_TEXTAREA = document.getElementById('assistant_instructions');
    const assistantInstructionsTextLength_SMALL = document.getElementById('assistant_instructions_text_length');
    const assistantInstructionLengthValue_SPAN = document.getElementById('assistant_instruction_length_value');
    const maxLength = 800;

    function countWords(text) {
        return text.split(/[\s\n]+/).filter(word => word.length > 0).length;
    }

    function updateWordCount() {
        const textAreaValue = assistantInstructions_TEXTAREA.value;
        const textAreaWords = textAreaValue.trim().split(/[\s\n]+/).filter(word => word.length > 0);
        const textAreaLength = textAreaWords.length;
        const remainingWords = maxLength - textAreaLength;

        assistantInstructionLengthValue_SPAN.innerText = remainingWords;

        // If too many words, trim and alert
        if (textAreaLength > maxLength) {
            const trimmedText = textAreaWords.slice(0, maxLength).join(' ');
            assistantInstructions_TEXTAREA.value = trimmedText;
            alert('You have reached the 400-word limit. Please delete some words before continuing.');
            assistantInstructionLengthValue_SPAN.innerText = 0;
        }
    }

    assistantInstructions_TEXTAREA.addEventListener('input', updateWordCount);

    // Initialize word count on page load
    updateWordCount();
</script>