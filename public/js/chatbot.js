/**
 * chatbot.js — Frontend logic for LibBot chatbot.
 *
 * Handles:
 *   - Open/close chat panel via floating bubble
 *   - Send messages to PHP endpoint via fetch()
 *   - Render user/bot messages with animations
 *   - Typing indicator while waiting for response
 *   - Error and rate-limit state display
 *   - Quick-action suggestion chips
 *   - Character counter with validation
 *   - Session-based message history (in-memory)
 */

(function () {
    'use strict';

    // ── Config ───────────────────────────────────────────────────────────────

    const MAX_CHARS   = 500;
    const API_URL     = (function () {
        // Derive the base URL from the main.js script tag (same pattern as main.js)
        const el = document.querySelector('script[src*="main.js"]');
        if (el && el.src) {
            return el.src.replace(/\/public\/js\/main\.js(?:\?.*)?$/i, '') + '/controllers/chatController.php';
        }
        return '/controllers/chatController.php';
    })();

    // ── State ────────────────────────────────────────────────────────────────

    let isOpen    = false;
    let isSending = false;
    let hasGreeted = false;

    // ── DOM References ───────────────────────────────────────────────────────

    const bubble      = document.getElementById('libbot-bubble');
    const panel       = document.getElementById('libbot-panel');
    const messagesDiv = document.getElementById('libbot-messages');
    const textarea    = document.getElementById('libbot-input');
    const sendBtn     = document.getElementById('libbot-send-btn');
    const charCount   = document.getElementById('libbot-char-count');
    const typingDiv   = document.getElementById('libbot-typing');
    const badge       = document.getElementById('libbot-badge');

    if (!bubble || !panel) return; // Guard — chatbot elements not on page

    // ── CSRF Token ───────────────────────────────────────────────────────────

    const csrfMeta  = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // ── Toggle Panel ─────────────────────────────────────────────────────────

    bubble.addEventListener('click', function () {
        isOpen = !isOpen;
        panel.classList.toggle('libbot-visible', isOpen);
        bubble.classList.toggle('libbot-open', isOpen);

        // Hide unread badge when opening
        if (isOpen && badge) {
            badge.classList.remove('show');
        }

        // Focus the input when opening
        if (isOpen && textarea) {
            setTimeout(function () { textarea.focus(); }, 350);
        }

        // Show welcome message on first open
        if (isOpen && !hasGreeted) {
            hasGreeted = true;
            showWelcome();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            isOpen = false;
            panel.classList.remove('libbot-visible');
            bubble.classList.remove('libbot-open');
        }
    });

    // ── Welcome Message ──────────────────────────────────────────────────────

    function showWelcome() {
        var userName = '';
        var nameEl = document.querySelector('.menu-title');
        if (nameEl) {
            userName = nameEl.textContent.trim();
        }

        var greeting = userName ? 'Hi ' + userName + '! 👋' : 'Hi there! 👋';

        var welcomeHTML = '<div class="libbot-welcome">'
            + '<span class="libbot-welcome-icon">📚</span>'
            + '<div class="libbot-welcome-title">' + greeting + '</div>'
            + '<p>I\'m LibBot, your library assistant. Ask me about books, your account, rentals, or library policies.</p>'
            + '<div class="libbot-welcome-chips">'
            + '<button class="libbot-chip" data-msg="What\'s my wallet balance?">💰 Wallet Balance</button>'
            + '<button class="libbot-chip" data-msg="Do I have any overdue books?">📅 Overdue Books</button>'
            + '<button class="libbot-chip" data-msg="Recommend me some books">📖 Recommend Books</button>'
            + '<button class="libbot-chip" data-msg="What are the membership plans?">🎫 Membership Info</button>'
            + '</div>'
            + '</div>';

        messagesDiv.innerHTML = welcomeHTML;

        // Bind chip click handlers
        var chips = messagesDiv.querySelectorAll('.libbot-chip');
        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                var msg = chip.getAttribute('data-msg');
                if (msg) {
                    sendMessage(msg);
                }
            });
        });
    }

    // ── Send Message ─────────────────────────────────────────────────────────

    /**
     * Send a message to the chatbot backend.
     * @param {string} [overrideMsg] Optional message to send instead of textarea value.
     */
    function sendMessage(overrideMsg) {
        var message = (overrideMsg || (textarea ? textarea.value : '')).trim();

        if (message === '' || isSending) return;

        if (message.length > MAX_CHARS) {
            showError('Message is too long. Maximum ' + MAX_CHARS + ' characters.');
            return;
        }

        // Clear input
        if (!overrideMsg && textarea) {
            textarea.value = '';
            updateCharCount();
            autoResize();
        }

        // Remove welcome message if present
        var welcome = messagesDiv.querySelector('.libbot-welcome');
        if (welcome) {
            welcome.remove();
        }

        // Render user message
        appendMessage('user', message);

        // Show typing indicator
        showTyping(true);
        isSending = true;
        updateSendButton();

        // Send to backend
        var body = new URLSearchParams();
        body.append('message', message);
        body.append('csrf_token', csrfToken);

        fetch(API_URL + '?action=send', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body.toString(),
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { status: response.status, data: data };
            });
        })
        .then(function (result) {
            showTyping(false);
            isSending = false;
            updateSendButton();

            if (result.data.success) {
                appendMessage('bot', result.data.reply);
            } else {
                // Handle specific error codes
                if (result.status === 429) {
                    showError(result.data.error || 'Rate limit reached. Please wait a while.');
                } else if (result.status === 401) {
                    showError('Your session has expired. Please refresh the page and log in again.');
                } else if (result.status === 403) {
                    showError('Security token expired. Please refresh the page.');
                } else {
                    showError(result.data.error || 'Something went wrong. Please try again.');
                }
            }
        })
        .catch(function (err) {
            console.error('[LibBot] Network error:', err);
            showTyping(false);
            isSending = false;
            updateSendButton();
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ── Render Messages ──────────────────────────────────────────────────────

    /**
     * Append a message bubble to the chat area.
     * @param {'user'|'bot'} type   Message sender type.
     * @param {string}       text   Message content (already HTML-escaped from server for bot messages).
     */
    function appendMessage(type, text) {
        var div = document.createElement('div');
        div.className = 'libbot-msg libbot-msg-' + type;

        if (type === 'user') {
            // User messages: escape HTML on the client side
            div.textContent = text;
        } else {
            // Bot messages: already HTML-escaped by the server, render with bold support
            // Convert **text** to <strong>text</strong> for emphasis
            var formatted = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
            div.innerHTML = formatted;
        }

        // Insert before typing indicator
        if (typingDiv && typingDiv.parentNode === messagesDiv) {
            messagesDiv.insertBefore(div, typingDiv);
        } else {
            messagesDiv.appendChild(div);
        }

        scrollToBottom();

        // Show badge if panel is closed (for bot messages)
        if (type === 'bot' && !isOpen && badge) {
            badge.classList.add('show');
        }
    }

    /**
     * Show an error message in the chat area.
     * @param {string} text Error message.
     */
    function showError(text) {
        var div = document.createElement('div');
        div.className = 'libbot-error';
        div.textContent = text;

        if (typingDiv && typingDiv.parentNode === messagesDiv) {
            messagesDiv.insertBefore(div, typingDiv);
        } else {
            messagesDiv.appendChild(div);
        }

        scrollToBottom();
    }

    // ── Typing Indicator ─────────────────────────────────────────────────────

    function showTyping(show) {
        if (typingDiv) {
            typingDiv.classList.toggle('show', show);
            if (show) {
                scrollToBottom();
            }
        }
    }

    // ── Input Handling ───────────────────────────────────────────────────────

    if (textarea) {
        // Send on Enter (Shift+Enter for new line)
        textarea.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Character counter
        textarea.addEventListener('input', function () {
            updateCharCount();
            autoResize();
        });
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', function () {
            sendMessage();
        });
    }

    /**
     * Update the character counter display.
     */
    function updateCharCount() {
        if (!charCount || !textarea) return;
        var len = textarea.value.length;
        charCount.textContent = len + ' / ' + MAX_CHARS;

        charCount.classList.remove('warn', 'error');
        if (len > MAX_CHARS) {
            charCount.classList.add('error');
        } else if (len > MAX_CHARS * 0.85) {
            charCount.classList.add('warn');
        }
    }

    /**
     * Auto-resize the textarea based on content.
     */
    function autoResize() {
        if (!textarea) return;
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
    }

    /**
     * Update send button disabled state.
     */
    function updateSendButton() {
        if (sendBtn) {
            sendBtn.disabled = isSending;
        }
    }

    // ── Scroll ───────────────────────────────────────────────────────────────

    function scrollToBottom() {
        if (messagesDiv) {
            setTimeout(function () {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }, 50);
        }
    }

    // ── Init ─────────────────────────────────────────────────────────────────

    updateCharCount();

})();
