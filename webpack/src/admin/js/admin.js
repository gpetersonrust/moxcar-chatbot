import '../scss/admin.scss';

console.log('Admin JS loaded');

class FileUploadHandler {
    constructor(container) {
      this.container = container;
      this.overlay = container.querySelector('#dropOverlay');
      this.initEvents();
    }

    initEvents() {
      window.addEventListener('dragover', (e) => {
        e.preventDefault();
        this.overlay.classList.add('active');
      });

      window.addEventListener('dragleave', (e) => {
        if (!this.container.contains(e.relatedTarget)) {
          this.overlay.classList.remove('active');
        }
      });

      window.addEventListener('drop', (e) => {
        e.preventDefault();
        this.overlay.classList.remove('active');
        const files = e.dataTransfer.files;
        if (files.length) {
          this.confirmAndUpload(files[0]);
        }
      });
    }

    confirmAndUpload(file) {
      const confirmUpload = confirm(`Are you sure you want to add the file: ${file.name}?`);
      if (confirmUpload) {
        this.uploadFile(file);
      }
    }
   /**
 * Uploads a file to the WordPress backend via REST API.
 * Sends the file using a FormData object, includes a security nonce,
 * and handles both success and error cases.
 *
 * @param {File} file - The file object dropped by the user.
 */
async uploadFile(file) {
    // Step 1: Create a new FormData instance to hold the file
    const formData = new FormData();
    formData.append('file', file); // 'file' is the key expected by the PHP endpoint
  
    try {
      // Step 2: Send the file using fetch()
      // We're using POST to send binary data, along with a security nonce
      const response = await fetch('/wp-json/moxcar-chatbot/v1/upload-file', {
        method: 'POST',
        body: formData,
        headers: {
          // This nonce is required by WordPress to prevent CSRF attacks
          'X-WP-Nonce': moxcarChatbotApi.nonce
        }
      });
  
      // Step 3: Parse the JSON response
      const data = await response.json();
  
      // Step 4: Check if the upload succeeded
      // Even if the HTTP request was OK (200), the API might return an error
      if (!response.ok || !data.success) {
        const message = data?.error || 'Upload failed due to an unknown error.';
        console.error('❌ Upload error:', message);
        alert(`Upload failed: ${message}`);
        return;
      }
  
      // ✅ Upload succeeded!
      console.log(`✅ File uploaded: ${data.filename} (ID: ${data.file_id})`);

      // reload page 
      window.location.reload();
  
      // Optional: here you could dynamically add the new file to the UI
      // without needing a page refresh
  
    } catch (error) {
      // Step 5: Catch any network errors, parse errors, etc.
      console.error('❌ Network or parsing error:', error);
      alert('Something went wrong during the upload. Please try again.');
    }
  }
  
  
  }

  class FileActionsHandler {
    constructor() {
      this.bindEvents();
    }
  
    bindEvents() {
      // Toggle open menus
      document.querySelectorAll('.moxcar_chatbot_file-actions').forEach(action => {
        action.addEventListener('click', (e) => {
          e.stopPropagation();
          this.closeAllMenus();
          action.classList.toggle('open');
        });
      });
  
      // Delete file buttons
      document.querySelectorAll('.moxcar_chatbot_file-actions-menu button[data-file_id]').forEach(button => {
        button.addEventListener('click', async (e) => {
          e.stopPropagation();
          const fileId = button.dataset.file_id;
          const confirmed = confirm('Are you sure you want to delete this file?');
          if (confirmed) {
            await this.deleteFile(fileId);
          }
        });
      });
  
      // Global click = close menus
      window.addEventListener('click', () => this.closeAllMenus());
    }
  
    async deleteFile(fileId) {
      try {
        const response = await fetch('/wp-json/moxcar-chatbot/v1/delete-file', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce':  moxcarChatbotApi.nonce
          },
          body: JSON.stringify({ file_id: fileId }),
        });
  
        const data = await response.json();
  
        if (data.success) {
          console.log(`✅ File ${fileId} deleted.`);
          window.location.reload();
          // Remove from DOM
          const el = document.querySelector(`[data-file-id="${fileId}"]`);
          if (el) el.closest('.moxcar_chatbot_file-card').remove();
        } else {
          alert(`⚠️ Error deleting file: ${data.error}`);
        }
      } catch (err) {
        console.error('❌ Delete request failed:', err);
        alert('An error occurred while deleting the file.');
      }
    }
  
    closeAllMenus() {
      document.querySelectorAll('.moxcar_chatbot_file-actions').forEach(el => el.classList.remove('open'));
    }
  }
  

document.addEventListener('DOMContentLoaded', () => {
    new FileUploadHandler(document.getElementById('dropzone'));
    new FileActionsHandler();
});
