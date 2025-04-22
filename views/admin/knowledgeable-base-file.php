
<?php
 
// Fetch saved data from the wp_options table
$option_name = 'knowledge_base_files';
$saved_files = get_option($option_name, []);

 
?>


<div class="wrap">
    <h1>Knowledge Base File</h1>
    <div class="moxcar_chatbot_container" id="dropzone">
        <div class="drop-overlay" id="dropOverlay">Drop your file here...</div>
        <div class="moxcar_chatbot_header">
            <h2>📁 Recent Files</h2>
            <p style="font-size: 0.85rem; color: var(--wp-gray-700)">Home - Knowledge Base - Recent Files</p>
            <p style="font-size: 0.85rem; color: var(--wp-gray-700)">Drag and drop files to add them to the list.</p>
        </div>
        <div class="moxcar_chatbot_file-list">
            <?php if (!empty($saved_files)) : ?>
                <?php foreach ($saved_files as $file) : ?>
                    <div class="moxcar_chatbot_file-card">
                        <div class="moxcar_chatbot_file-info">
                            <div class="moxcar_chatbot_file-icon"><?php echo strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)); ?></div>
                            <div class="moxcar_chatbot_file-text">
                                <div class="moxcar_chatbot_file-name"><?php echo htmlspecialchars($file['name']); ?></div>
                                <div class="moxcar_chatbot_file-meta"><?php echo htmlspecialchars($file['date']); ?> | <?php echo htmlspecialchars($file['size']); ?></div>
                            </div>
                        </div>
                        <div class="moxcar_chatbot_file-actions">
                            ⋮
                            <div class="moxcar_chatbot_file-actions-menu">
                                <button data-file_id="<?php echo htmlspecialchars($file['id']); ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p 
                 style="text-align:center;"
                >No files found. Drag and drop files to add them to the list.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
