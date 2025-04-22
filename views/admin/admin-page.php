<?php
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['moxcar_chatbot_settings_action'] ) ) {
            if ( ! isset( $_POST['moxcar_chatbot_settings_nonce'] ) || ! wp_verify_nonce( $_POST['moxcar_chatbot_settings_nonce'], 'moxcar_chatbot_settings_action' ) ) {
                wp_die( __( 'Nonce verification failed.', 'moxcar-chatbot' ) );
            }

            if ( $_POST['moxcar_chatbot_settings_action'] === 'save_moxcar_chatbot_settings' ) {
                $openai_api_key = isset( $_POST['openai_api_key'] ) ? sanitize_text_field( $_POST['openai_api_key'] ) : '';
                $vector_store_name = isset( $_POST['vector_store_name'] ) ? sanitize_text_field( $_POST['vector_store_name'] ) : '';

                update_option( 'openai_api_key', $openai_api_key );
                update_option( 'vector_store_name', $vector_store_name );

                add_settings_error( 'moxcar_chatbot_settings', 'settings_updated', __( 'Settings saved.', 'moxcar-chatbot' ), 'updated' );
            }
        }
        settings_errors( 'moxcar_chatbot_settings' );
             
        ?>
        <div class="wrap"> 
            <h1
            class="moxcar-chatbot-settings-title"
            ><?php esc_html_e( 'Moxcar Chatbot Settings', 'moxcar-chatbot' ); ?></h1>
         <form
         class="moxcar-chatbot-settings-form"
            method="post"
            action="<?php echo esc_url( admin_url( 'admin.php?page=moxcar-chatbot-settings' ) ); ?>"
         >
            <input type="hidden" id="hidden_action" name="moxcar_chatbot_settings_action" value="save_moxcar_chatbot_settings" />
            <?php
            wp_nonce_field( 'moxcar_chatbot_settings_action', 'moxcar_chatbot_settings_nonce' );
            ?>
            <label for="openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'moxcar-chatbot' ); ?></label>
            <input type="text" id="openai_api_key" name="openai_api_key" value="<?php echo esc_attr( get_option( 'openai_api_key', '' ) ); ?>" />

            <label for="vector_store_name"><?php esc_html_e( 'Vector Store Name', 'moxcar-chatbot' ); ?></label>
            <input type="text" id="vector_store_name" name="vector_store_name" value="<?php echo esc_attr( get_option( 'vector_store_name', '' ) ); ?>" />

            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'moxcar-chatbot' ); ?>" />
         </form>
         </div>