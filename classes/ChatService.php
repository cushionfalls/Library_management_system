<?php
/**
 * ChatService — Core chatbot service for LibBot.
 *
 * Responsibilities:
 *   1. Analyse user message keywords to determine which DB context to fetch.
 *   2. Build dynamic context from database queries.
 *   3. Assemble the full prompt (static rules + dynamic context + conversation history).
 *   4. Call the primary LLM (Groq) with fallback to Gemini.
 *   5. Log every interaction to the ChatLogs table.
 *
 * Usage (from chatController.php):
 *   $service = new ChatService();
 *   $result  = $service->handleMessage($userId, $message, $conversationHistory);
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/BrowseCatalog.php';
require_once __DIR__ . '/Wallet.php';

class ChatService
{
    /** @var mysqli Database connection */
    private $db;

    /** @var string Groq API key */
    private $groqKey;

    /** @var string Gemini API key */
    private $geminiKey;

    /** @var string Static system prompt template */
    private $systemPromptTemplate;

    /** @var int cURL timeout per provider in seconds */
    private const API_TIMEOUT = 10;

    /** @var string Groq model identifier */
    private const GROQ_MODEL = 'llama-3.3-70b-versatile';

    /** @var string Gemini model identifier */
    private const GEMINI_MODEL = 'gemini-2.5-flash';

    /** @var string Groq API endpoint */
    private const GROQ_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    /** @var string Gemini API endpoint (key appended as query param) */
    private const GEMINI_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    /**
     * Keyword groups mapped to context-fetch categories.
     * Each group key corresponds to a context-building method.
     */
    private const KEYWORD_MAP = [
        'wallet'      => ['wallet', 'balance', 'money', 'fund', 'top up', 'topup', 'pay', 'payment'],
        'fines'       => ['fine', 'overdue', 'due', 'late', 'penalty', 'owe'],
        'rentals'     => ['rental', 'rented', 'borrow', 'borrowed', 'return', 'due date', 'active rental', 'my book', 'my rental'],
        'books'       => ['book', 'title', 'author', 'genre', 'read', 'find', 'search', 'thriller', 'mystery', 'fantasy', 'romance', 'fiction', 'biography', 'history', 'science'],
        'membership'  => ['membership', 'plan', 'subscribe', 'subscription', 'premium', 'basic', 'standard', 'member'],
        'recommend'   => ['recommend', 'suggest', 'what should i read', 'similar', 'like'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        // Load API keys from .env
        EnvLoader::load(__DIR__ . '/../.env');
        $this->groqKey   = EnvLoader::get('GROQ_API_KEY', '');
        $this->geminiKey = EnvLoader::get('GEMINI_API_KEY', '');

        // Load the static system prompt template
        $promptPath = __DIR__ . '/../config/chatbot_prompt.txt';
        $this->systemPromptTemplate = file_exists($promptPath)
            ? file_get_contents($promptPath)
            : 'You are LibBot, the AI assistant for Paper Library. Answer only library-related questions.';
    }

    // =========================================================================
    //  PUBLIC API
    // =========================================================================

    /**
     * Handle an incoming chat message.
     *
     * @param int    $userId              Authenticated user ID.
     * @param string $message             Sanitised user message.
     * @param array  $conversationHistory  Last N messages: [['role'=>'user'|'assistant','content'=>'...'], ...]
     * @return array ['success'=>bool, 'reply'=>string, 'model'=>string]
     */
    public function handleMessage(int $userId, string $message, array $conversationHistory): array
    {
        $startMs = (int)(microtime(true) * 1000);

        // 1. Determine which context categories to fetch
        $categories = $this->detectCategories($message);

        // 2. Build dynamic context string
        $dynamicContext = $this->buildContext($userId, $message, $categories);

        // 3. Format conversation history string
        $historyStr = $this->formatHistory($conversationHistory);

        // 4. Assemble the full system prompt
        $systemPrompt = str_replace(
            ['{DYNAMIC_CONTEXT}', '{CONVERSATION_HISTORY}'],
            [$dynamicContext, $historyStr],
            $this->systemPromptTemplate
        );

        // 5. Build the messages array for the LLM
        $messages = $this->buildLLMMessages($conversationHistory, $message);

        // 6. Call LLM (Groq primary, Gemini fallback)
        $result = $this->callLLM($systemPrompt, $messages);

        // 7. Calculate response time
        $responseMs = (int)(microtime(true) * 1000) - $startMs;

        // 8. Log the interaction
        $this->logInteraction(
            $userId,
            $message,
            $result['reply'],
            $result['model'],
            $result['tokens'],
            $responseMs
        );

        return [
            'success' => $result['success'],
            'reply'   => $result['reply'],
            'model'   => $result['model'],
        ];
    }

    // =========================================================================
    //  KEYWORD DETECTION
    // =========================================================================

    /**
     * Scan the user's message for keywords and return matching context categories.
     *
     * @param string $message Lowercased user message.
     * @return array List of category keys (e.g., ['wallet', 'books']).
     */
    private function detectCategories(string $message): array
    {
        $lower      = strtolower($message);
        $categories = [];

        foreach (self::KEYWORD_MAP as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($lower, $keyword) !== false) {
                    $categories[] = $category;
                    break; // One match per category is enough
                }
            }
        }

        return $categories;
    }

    // =========================================================================
    //  CONTEXT BUILDING
    // =========================================================================

    /**
     * Build the dynamic context string from database queries.
     *
     * @param int    $userId     User ID.
     * @param string $message    User's original message.
     * @param array  $categories Matched context categories.
     * @return string Formatted context block.
     */
    private function buildContext(int $userId, string $message, array $categories): string
    {
        $parts = [];

        // Always include basic user info
        $parts[] = $this->getUserContext($userId);

        // Category-specific context
        if (in_array('wallet', $categories, true)) {
            $parts[] = $this->getWalletContext($userId);
        }

        if (in_array('fines', $categories, true)) {
            $parts[] = $this->getFinesContext($userId);
        }

        if (in_array('rentals', $categories, true)) {
            $parts[] = $this->getRentalsContext($userId);
        }

        if (in_array('membership', $categories, true)) {
            $parts[] = $this->getMembershipContext($userId);
        }

        if (in_array('books', $categories, true) || in_array('recommend', $categories, true)) {
            $parts[] = $this->getBookSearchContext($message);
        }

        if (in_array('recommend', $categories, true)) {
            $parts[] = $this->getRecommendationContext($userId);
        }

        // Filter out empty parts
        $parts = array_filter($parts, function ($p) {
            return trim($p) !== '';
        });

        if (empty($parts)) {
            return 'No specific context was fetched for this query.';
        }

        return implode("\n\n", $parts);
    }

    /**
     * Fetch basic user info context.
     */
    private function getUserContext(int $userId): string
    {
        $stmt = $this->db->prepare(
            "SELECT first_name, last_name, email, role FROM Users WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            return 'User: Unknown';
        }

        return sprintf(
            "[User Info]\nName: %s %s\nEmail: %s\nRole: %s",
            $user['first_name'],
            $user['last_name'],
            $user['email'],
            $user['role']
        );
    }

    /**
     * Fetch wallet balance context.
     */
    private function getWalletContext(int $userId): string
    {
        $wallet  = new Wallet();
        $balance = $wallet->getBalance($userId);
        $dollars = number_format($balance / 100, 2);

        return sprintf("[Wallet]\nCurrent Balance: $%s USD", $dollars);
    }

    /**
     * Fetch overdue books / fines context.
     */
    private function getFinesContext(int $userId): string
    {
        $stmt = $this->db->prepare(
            "SELECT bt.id, b.name AS book_name, bt.due_date, bt.created_at
             FROM BookTransactions bt
             JOIN Books b ON b.id = bt.book_id
             WHERE bt.user_id = ? AND bt.is_returned = 0 AND bt.due_date IS NOT NULL AND bt.due_date < NOW()
             ORDER BY bt.due_date ASC
             LIMIT 10"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($rows)) {
            return "[Overdue Books]\nNo overdue books found. You're all caught up!";
        }

        $lines = ["[Overdue Books]"];
        foreach ($rows as $i => $row) {
            $lines[] = sprintf(
                "%d. \"%s\" — Due: %s",
                $i + 1,
                $row['book_name'],
                date('M j, Y', strtotime($row['due_date']))
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Fetch active (non-returned) rentals context.
     */
    private function getRentalsContext(int $userId): string
    {
        $stmt = $this->db->prepare(
            "SELECT bt.id, b.name AS book_name, bt.transaction_type, bt.due_date, bt.created_at
             FROM BookTransactions bt
             JOIN Books b ON b.id = bt.book_id
             WHERE bt.user_id = ? AND bt.is_returned = 0
             ORDER BY bt.created_at DESC
             LIMIT 10"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($rows)) {
            return "[Active Rentals]\nNo active rentals. Browse the catalog to find your next read!";
        }

        $lines = ["[Active Rentals]"];
        foreach ($rows as $i => $row) {
            $dueStr = $row['due_date'] ? date('M j, Y', strtotime($row['due_date'])) : 'No due date';
            $lines[] = sprintf(
                "%d. \"%s\" (%s) — Due: %s",
                $i + 1,
                $row['book_name'],
                $row['transaction_type'],
                $dueStr
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Fetch membership status context.
     */
    private function getMembershipContext(int $userId): string
    {
        $stmt = $this->db->prepare(
            "SELECT um.status, um.starts_at, um.ends_at, mp.name AS plan_name, mp.duration_days, mp.price
             FROM UserMemberships um
             JOIN MembershipPlans mp ON mp.id = um.plan_id
             WHERE um.user_id = ? AND um.status = 'ACTIVE'
             ORDER BY um.ends_at DESC
             LIMIT 1"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return "[Membership]\nStatus: Not active\nYou can subscribe to a plan on the Membership page.";
        }

        return sprintf(
            "[Membership]\nPlan: %s\nStatus: Active\nValid Until: %s",
            $row['plan_name'],
            date('M j, Y', strtotime($row['ends_at']))
        );
    }

    /**
     * Search the book catalog and return matching books as context.
     */
    private function getBookSearchContext(string $message): string
    {
        $searchQuery = $this->extractSearchTerm($message);

        if ($searchQuery === '') {
            return '';
        }

        $catalog = new BrowseCatalog();
        $results = $catalog->getSuggestions($searchQuery, 5);

        if (empty($results)) {
            return sprintf("[Book Search: \"%s\"]\nNo matching books found in the catalog.", $searchQuery);
        }

        // Fetch full details for each matched book
        $lines = [sprintf("[Book Search: \"%s\"]", $searchQuery)];
        foreach ($results as $i => $row) {
            $detail = $catalog->getBookDetail((int)$row['id']);
            if ($detail) {
                $price = $detail['price'] > 0 ? '$' . number_format($detail['price'] / 100, 2) : 'Free';
                $lines[] = sprintf(
                    "%d. \"%s\" by %s — Genre: %s, Rating: %s/5, Price: %s",
                    $i + 1,
                    $detail['name'],
                    $detail['author_display'],
                    $detail['genre_label'],
                    $detail['rating'],
                    $price
                );
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Build recommendation context based on user's reading history.
     */
    private function getRecommendationContext(int $userId): string
    {
        // Get user's most-read genres from their transaction history
        $stmt = $this->db->prepare(
            "SELECT b.genre, COUNT(*) AS cnt
             FROM BookTransactions bt
             JOIN Books b ON b.id = bt.book_id
             WHERE bt.user_id = ?
             GROUP BY b.genre
             ORDER BY cnt DESC
             LIMIT 3"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $genres = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($genres)) {
            return "[Recommendations]\nNo reading history yet — try browsing the catalog to discover books!";
        }

        // Find top-rated books in those genres that the user hasn't read
        $genreList = array_map(function ($g) {
            return $this->db->real_escape_string($g['genre']);
        }, $genres);
        $genreIn = "'" . implode("','", $genreList) . "'";

        $sql = "SELECT b.id, b.name, b.genre,
                    COALESCE(GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', '), 'Unknown Author') AS authors,
                    COALESCE(ROUND(AVG(br.rating), 1), 0) AS rating
                FROM Books b
                LEFT JOIN BookAuthors ba ON ba.book_id = b.id
                LEFT JOIN Authors a ON a.id = ba.author_id
                LEFT JOIN BookReviews br ON br.book_id = b.id
                WHERE b.genre IN ({$genreIn})
                  AND b.id NOT IN (SELECT book_id FROM BookTransactions WHERE user_id = ?)
                GROUP BY b.id
                ORDER BY rating DESC, b.created_at DESC
                LIMIT 5";

        $stmt2 = $this->db->prepare($sql);
        $stmt2->bind_param('i', $userId);
        $stmt2->execute();
        $books = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($books)) {
            return "[Recommendations]\nYou've explored most books in your favourite genres! Try a new genre from the catalog.";
        }

        $favGenres = implode(', ', array_column($genres, 'genre'));
        $lines = ["[Recommendations — Based on your favourite genres: {$favGenres}]"];
        foreach ($books as $i => $book) {
            $lines[] = sprintf(
                "%d. \"%s\" by %s — %s (Rating: %s/5)",
                $i + 1,
                $book['name'],
                $book['authors'],
                $book['genre'],
                $book['rating']
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Extract a likely search term from the user's message by removing common stop words
     * and chatbot-trigger keywords.
     *
     * @param string $message Raw user message.
     * @return string Cleaned search query.
     */
    private function extractSearchTerm(string $message): string
    {
        $lower = strtolower(trim($message));

        // Remove common stop words and chatbot trigger phrases
        $stopWords = [
            'do you have', 'can you find', 'search for', 'look for', 'find me',
            'show me', 'i want', 'i need', 'any', 'some', 'a', 'an', 'the',
            'books', 'book', 'about', 'by', 'in', 'on', 'of', 'for', 'is',
            'are', 'there', 'what', 'which', 'please', 'can', 'you', 'me',
            'recommend', 'suggest', 'do', 'have', 'find', 'search', 'look',
            'show', 'give', 'tell', 'want', 'need', 'like', 'similar', 'to',
        ];

        $words = preg_split('/\s+/', $lower);
        $filtered = array_filter($words, function ($w) use ($stopWords) {
            return strlen($w) > 1 && !in_array($w, $stopWords, true);
        });

        return trim(implode(' ', $filtered));
    }

    // =========================================================================
    //  CONVERSATION HISTORY
    // =========================================================================

    /**
     * Format conversation history into a readable string for the system prompt.
     *
     * @param array $history Array of ['role'=>..., 'content'=>...] entries.
     * @return string Formatted history.
     */
    private function formatHistory(array $history): string
    {
        if (empty($history)) {
            return 'No previous messages in this session.';
        }

        $lines = [];
        foreach ($history as $entry) {
            $role    = ($entry['role'] === 'user') ? 'User' : 'LibBot';
            $lines[] = "{$role}: {$entry['content']}";
        }

        return implode("\n", $lines);
    }

    /**
     * Build the messages array for the LLM API.
     * Includes the last 6 conversation messages plus the current user message.
     *
     * @param array  $history Array of conversation entries.
     * @param string $currentMessage The current user message.
     * @return array Messages array for the LLM.
     */
    private function buildLLMMessages(array $history, string $currentMessage): array
    {
        $messages = [];

        // Include last 6 messages from history
        $recent = array_slice($history, -6);
        foreach ($recent as $entry) {
            $messages[] = [
                'role'    => $entry['role'] === 'user' ? 'user' : 'assistant',
                'content' => $entry['content'],
            ];
        }

        // Add the current message
        $messages[] = [
            'role'    => 'user',
            'content' => $currentMessage,
        ];

        return $messages;
    }

    // =========================================================================
    //  LLM CALLS
    // =========================================================================

    /**
     * Call the LLM — try Groq first, fall back to Gemini.
     *
     * @param string $systemPrompt The full assembled system prompt.
     * @param array  $messages     The conversation messages (user/assistant turns).
     * @return array ['success'=>bool, 'reply'=>string, 'model'=>string, 'tokens'=>int|null]
     */
    private function callLLM(string $systemPrompt, array $messages): array
    {
        // Try Groq first
        if ($this->groqKey !== '') {
            $result = $this->callGroq($systemPrompt, $messages);
            if ($result['success']) {
                return $result;
            }
            // Log the Groq failure
            $this->logError('Groq API failed, falling back to Gemini: ' . ($result['error'] ?? 'unknown'));
        }

        // Fallback to Gemini
        if ($this->geminiKey !== '') {
            $result = $this->callGemini($systemPrompt, $messages);
            if ($result['success']) {
                return $result;
            }
            // Log the Gemini failure
            $this->logError('Gemini API also failed: ' . ($result['error'] ?? 'unknown'));
        }

        // Both failed
        return [
            'success' => false,
            'reply'   => "I'm having trouble connecting right now. Please try again in a moment.",
            'model'   => 'error',
            'tokens'  => null,
        ];
    }

    /**
     * Call the Groq API (OpenAI-compatible format).
     *
     * @param string $systemPrompt System prompt text.
     * @param array  $messages     Conversation messages.
     * @return array Standardised result.
     */
    private function callGroq(string $systemPrompt, array $messages): array
    {
        // Prepend system message
        $apiMessages   = [];
        $apiMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        foreach ($messages as $msg) {
            $apiMessages[] = $msg;
        }

        $payload = json_encode([
            'model'       => self::GROQ_MODEL,
            'messages'    => $apiMessages,
            'temperature' => 0.7,
            'max_tokens'  => 512,
        ]);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->groqKey,
        ];

        $response = $this->curlPost(self::GROQ_ENDPOINT, $payload, $headers);

        if (!$response['ok']) {
            return ['success' => false, 'error' => $response['error']];
        }

        $data  = $response['data'];
        $reply = $data['choices'][0]['message']['content'] ?? '';
        $tokens = $data['usage']['total_tokens'] ?? null;

        if (trim($reply) === '') {
            return ['success' => false, 'error' => 'Empty response from Groq'];
        }

        return [
            'success' => true,
            'reply'   => trim($reply),
            'model'   => self::GROQ_MODEL,
            'tokens'  => $tokens ? (int)$tokens : null,
        ];
    }

    /**
     * Call the Google Gemini API.
     *
     * @param string $systemPrompt System prompt text.
     * @param array  $messages     Conversation messages.
     * @return array Standardised result.
     */
    private function callGemini(string $systemPrompt, array $messages): array
    {
        // Build Gemini-format contents array
        $contents = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $payload = json_encode([
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 512,
            ],
        ]);

        $url     = self::GEMINI_ENDPOINT . '?key=' . urlencode($this->geminiKey);
        $headers = ['Content-Type: application/json'];

        $response = $this->curlPost($url, $payload, $headers);

        if (!$response['ok']) {
            return ['success' => false, 'error' => $response['error']];
        }

        $data   = $response['data'];
        $reply  = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $tokens = $data['usageMetadata']['totalTokenCount'] ?? null;

        if (trim($reply) === '') {
            return ['success' => false, 'error' => 'Empty response from Gemini'];
        }

        return [
            'success' => true,
            'reply'   => trim($reply),
            'model'   => self::GEMINI_MODEL,
            'tokens'  => $tokens ? (int)$tokens : null,
        ];
    }

    /**
     * Execute a cURL POST request.
     *
     * @param string $url     API endpoint URL.
     * @param string $payload JSON-encoded request body.
     * @param array  $headers HTTP headers.
     * @return array ['ok'=>bool, 'data'=>array|null, 'error'=>string|null]
     */
    private function curlPost(string $url, string $payload, array $headers): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::API_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw     = curl_exec($ch);
        $curlErr = curl_error($ch);
        $status  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // cURL error (network issue, timeout, etc.)
        if ($curlErr || $raw === false) {
            return ['ok' => false, 'data' => null, 'error' => 'cURL error: ' . ($curlErr ?: 'no response')];
        }

        $data = json_decode($raw, true);

        // HTTP error from the API
        if ($status >= 400) {
            $errMsg = 'HTTP ' . $status;
            if (isset($data['error']['message'])) {
                $errMsg .= ': ' . $data['error']['message'];
            }
            return ['ok' => false, 'data' => $data, 'error' => $errMsg];
        }

        return ['ok' => true, 'data' => $data, 'error' => null];
    }

    // =========================================================================
    //  LOGGING
    // =========================================================================

    /**
     * Log a chatbot interaction to the ChatLogs table.
     *
     * @param int         $userId     User ID.
     * @param string      $message    User message.
     * @param string      $reply      Bot reply.
     * @param string      $model      Model identifier.
     * @param int|null    $tokens     Token count (if available).
     * @param int|null    $responseMs Response time in ms.
     */
    private function logInteraction(
        int $userId,
        string $message,
        string $reply,
        string $model,
        ?int $tokens,
        ?int $responseMs
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO ChatLogs (user_id, message, reply, model_used, tokens_used, response_ms, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            if ($stmt) {
                $stmt->bind_param('isssii', $userId, $message, $reply, $model, $tokens, $responseMs);
                $stmt->execute();
            }
        } catch (\Exception $e) {
            // Don't let logging failures break the chat flow
            $this->logError('ChatLog insert failed: ' . $e->getMessage());
        }
    }

    /**
     * Log an error message to the application error log.
     *
     * @param string $message Error message.
     */
    private function logError(string $message): void
    {
        $logFile = __DIR__ . '/../logs/error.log';
        $entry   = date('[Y-m-d H:i:s]') . " [ChatService] {$message}" . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
?>
