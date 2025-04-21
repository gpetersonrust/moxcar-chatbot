# 🧠 Moxcar Chatbot Class Specification (`class_specs.md`)

This guide defines how **all new PHP classes** should be structured within the Moxcar Chatbot WordPress plugin. Follow it precisely to ensure consistency, maintainability, and compatibility with our autoloader and main plugin architecture.

---

## ✅ Naming Conventions

- **Class Prefix:** `Moxcar_Chatbot_`
- **Filename Format:** `class-moxcar-chatbot-<slug>.php`
- Example:  
  Class `Moxcar_Chatbot_VectorStore_Manager` → File `class-moxcar-chatbot-vectorstore-manager.php`

---

## 📁 Loader Integration

❗️**Do NOT load or require this file manually.**  
All class files are loaded automatically via the central loader system in `includes/class-moxcar-chatbot-loader.php`.  
Just ensure:
- Class name matches filename.
- Class file is in the appropriate folder (`/includes/`, `/admin/`, etc.).

---

## 🧱 Class Structure Template

```php
<?php
/**
 * Class Moxcar_Chatbot_Example
 *
 * @package Moxcar_Chatbot
 * @since 1.0.0
 * @plugin Moxcar Chatbot
 */

defined( 'ABSPATH' ) || exit;

class Moxcar_Chatbot_Example {

    /**
     * Constructor.
     * DO NOT add hook registrations here — use the centralized hook registrar.
     */
    public function __construct() {
        // Optionally set up internal properties or services.
    }

    /**
     * Initialize plugin hooks (actions/filters).
     * This will be called by the loader, not from inside this class.
     */
    public function init_hooks() {
        // Example:
        // add_action( 'init', [ $this, 'do_something' ] );
    }

    /**
     * Example method.
     *
     * @return void
     */
    public function do_something() {
        // ...
    }
}
