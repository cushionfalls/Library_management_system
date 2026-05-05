<?php
/**
 * chatController.php — JSON API endpoint for the LibBot chatbot.
 *
 * Route: controllers/chatController.php?action=send
 * Method: POST
 * Body (form-encoded): message, csrf_token
 *
 * Security layers (applied in order):
 *   1. Auth gate — 401 if not logged in
 *   2. CSRF validation — 403 if token mismatch
 *   3. Rate limit — 429 if > 20 messages/hour
 *   4. Input validation — 400 if message empty or > 500 chars
 *   5. Input sanitisation — strip control characters
 *
 * Returns JSON: { success: bool, reply: string, [error: string] }
 */

ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/ChatService.php';

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Send a JSON response and exit.
 *
 * @param array $data    Response payload.
 * @param int   $code    HTTP status code.
 */
function chat_json(array $data, int $code = 200): void
{
    http_response_code($code);
    ob_clean();
    echo json_encode($data);
    exit;
}

// ── Init ─────────────────────────────────────────────────────────────────────

$session = new Session();
$action  = $_GET['action'] ?? '';

// ── Auth Gate ────────────────────────────────────────────────────────────────

if (!$session->isLoggedIn()) {
    chat_json(['success' => false, 'error' => 'Unauthorized. Please log in.'], 401);
}

$userId = (int)$session->getUserId();

// ── Router ───────────────────────────────────────────────────────────────────

try {
    switch ($action) {

        case 'send':
            handleSendMessage($session, $userId);
            break;

        default:
            chat_json(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    // Log unexpected errors
    $logFile = __DIR__ . '/../logs/error.log';
    $entry   = date('[Y-m-d H:i:s]') . " [chatController] Uncaught: {$e->getMessage()}" . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

    chat_json(['success' => false, 'error' => 'An unexpected error occurred. Please try again.'], 500);
}

// ── Action Handler ───────────────────────────────────────────────────────────

/**
 * Handle the "send" action — process a user chat message.
 *
 * @param Session $session Active session instance.
 * @param int     $userId  Authenticated user ID.
 */
function handleSendMessage(Session $session, int $userId): void
{
    // 1. Only POST allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        chat_json(['success' => false, 'error' => 'POST required'], 405);
    }

    // 2. CSRF validation (matches existing pattern from profile controller)
    $csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!$session->validateCSRFToken((string)$csrfToken)) {
        chat_json(['success' => false, 'error' => 'Security token mismatch. Please refresh the page.'], 403);
    }

    // 3. Rate limit check (20 messages per user per hour)
    if (!checkRateLimit($userId)) {
        chat_json([
            'success' => false,
            'error'   => "You've reached the message limit (20 per hour). Please try again later."
        ], 429);
    }

    // 4. Extract and validate message
    $message = trim((string)($_POST['message'] ?? ''));

    if ($message === '') {
        chat_json(['success' => false, 'error' => 'Message cannot be empty.'], 400);
    }

    if (mb_strlen($message) > 500) {
        chat_json(['success' => false, 'error' => 'Message is too long. Maximum 500 characters.'], 400);
    }

    // 5. Sanitise input — strip control characters (keep newlines and tabs)
    $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message);
    $message = trim($message);

    if ($message === '') {
        chat_json(['success' => false, 'error' => 'Message contains only invalid characters.'], 400);
    }

    // 6. Retrieve conversation history from the PHP session
    $history = $session->get('chat_history', []);
    if (!is_array($history)) {
        $history = [];
    }

    // 7. Call ChatService
    $chatService = new ChatService();
    $result      = $chatService->handleMessage($userId, $message, $history);

    // 8. Update session conversation history (keep last 12 entries = 6 turns)
    $history[] = ['role' => 'user',      'content' => $message];
    $history[] = ['role' => 'assistant', 'content' => $result['reply']];

    // Trim history to last 12 entries
    if (count($history) > 12) {
        $history = array_slice($history, -12);
    }
    $session->set('chat_history', $history);

    // 9. Sanitise LLM response before returning (escape HTML for safe rendering)
    $safeReply = htmlspecialchars($result['reply'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // 10. Return the response
    chat_json([
        'success' => true,
        'reply'   => $safeReply,
    ]);
}

// ── Rate Limiting ────────────────────────────────────────────────────────────

/**
 * Check and increment the per-user hourly rate limit.
 * Uses INSERT ... ON DUPLICATE KEY UPDATE for atomic operation.
 *
 * @param int $userId User ID.
 * @return bool True if within limit, false if exceeded.
 */
function checkRateLimit(int $userId): bool
{
    $db = Database::getInstance()->getConnection();
    $maxMessages = 20;

    // Current hour window (truncate to the start of the hour)
    $windowStart = date('Y-m-d H:00:00');

    // Atomic upsert — increment counter or insert new row
    $stmt = $db->prepare(
        "INSERT INTO ChatRateLimits (user_id, message_count, window_start)
         VALUES (?, 1, ?)
         ON DUPLICATE KEY UPDATE message_count = message_count + 1"
    );

    if (!$stmt) {
        // If the table doesn't exist yet, allow the message (graceful degradation)
        return true;
    }

    $stmt->bind_param('is', $userId, $windowStart);
    $stmt->execute();

    // Now check the current count
    $checkStmt = $db->prepare(
        "SELECT message_count FROM ChatRateLimits WHERE user_id = ? AND window_start = ? LIMIT 1"
    );
    $checkStmt->bind_param('is', $userId, $windowStart);
    $checkStmt->execute();
    $row = $checkStmt->get_result()->fetch_assoc();

    $count = (int)($row['message_count'] ?? 0);

    return $count <= $maxMessages;
}
?>
