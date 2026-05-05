<?php
/**
 * chatbot.php — LibBot floating chat bubble + panel component.
 *
 * Included in public/index.php for authenticated users only.
 * Outputs:
 *   - A <meta> tag with the CSRF token for JS to read
 *   - The floating bubble button (bottom-right)
 *   - The chat panel with header, messages area, typing indicator, and input
 *
 * Required variables (available from public/index.php):
 *   $session — Session instance (already instantiated in the layout)
 */

// Generate CSRF token for chatbot API calls
$chatCsrfToken = $session->generateCSRFToken();
?>

<!-- LibBot CSRF Token (read by chatbot.js) -->
<meta name="csrf-token" content="<?php echo htmlspecialchars($chatCsrfToken); ?>" />

<!-- LibBot Floating Bubble -->
<button id="libbot-bubble" type="button" aria-label="Open chat assistant" title="Chat with LibBot">
    <span class="material-symbols-outlined libbot-icon libbot-icon-chat">chat</span>
    <span class="material-symbols-outlined libbot-icon libbot-icon-close">close</span>
    <span id="libbot-badge" class="libbot-badge"></span>
</button>

<!-- LibBot Chat Panel -->
<div id="libbot-panel" role="dialog" aria-label="LibBot Chat Assistant">

    <!-- Header -->
    <div class="libbot-header">
        <div class="libbot-header-avatar">
            <span class="material-symbols-outlined" style="font-size: 22px;">smart_toy</span>
        </div>
        <div class="libbot-header-info">
            <div class="libbot-header-name">LibBot</div>
            <div class="libbot-header-status">
                <span class="libbot-dot"></span>Online — Ask me anything about the library
            </div>
        </div>
    </div>

    <!-- Messages Area -->
    <div id="libbot-messages" class="libbot-messages">
        <!-- Welcome message and conversation rendered by JS -->

        <!-- Typing Indicator (hidden by default) -->
        <div id="libbot-typing" class="libbot-typing">
            <span class="libbot-typing-dot"></span>
            <span class="libbot-typing-dot"></span>
            <span class="libbot-typing-dot"></span>
        </div>
    </div>

    <!-- Character Counter -->
    <div id="libbot-char-count" class="libbot-char-count">0 / 500</div>

    <!-- Input Area -->
    <div class="libbot-input-area">
        <textarea
            id="libbot-input"
            placeholder="Type your question..."
            maxlength="500"
            rows="1"
            aria-label="Type your message"
        ></textarea>
        <button id="libbot-send-btn" class="libbot-send-btn" type="button" aria-label="Send message" title="Send">
            <span class="material-symbols-outlined">send</span>
        </button>
    </div>
</div>
