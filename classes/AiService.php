<?php
require_once __DIR__ . '/../config/config.php';

class AiService
{
    private $apiKey;
    private $endpoint;
    private $model;
    private $cacheDir;
    private $cacheTtlSeconds;

    private $cooldownSeconds = 30;

    public function __construct($apiKey = OPENAI_API_KEY, $endpoint = OPENAI_ENDPOINT, $model = OPENAI_MODEL, $cacheTtlSeconds = 3600)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;
        $this->model = $model;
        $this->cacheTtlSeconds = max(3600, (int)$cacheTtlSeconds);
        $this->cacheDir = __DIR__ . '/../cache/ai';

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }
    }

    /**
     * Send a raw prompt to OpenAI Responses API and return generated text.
     */
    public function sendPrompt($prompt): string
    {
        $payload = [
            'model' => $this->model,
            'input' => $prompt,
            'store' => true
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError) {
            throw new RuntimeException('OpenAI request failed: ' . ($curlError ?: 'unknown cURL error'));
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('OpenAI request failed with HTTP ' . $httpCode . ': ' . $response);
        }

        $decoded = json_decode($response, true);
        $text = '';
        
        // 1. Handle the array-based response structure (seen in recent logs)
        if (is_array($decoded)) {
            $msg = isset($decoded[0]) ? $decoded[0] : $decoded;
            if (isset($msg['content']) && is_array($msg['content'])) {
                foreach ($msg['content'] as $block) {
                    if (isset($block['text']) && is_string($block['text'])) {
                        $text = $block['text'];
                        break;
                    }
                    if (isset($block['output_text']) && is_string($block['output_text'])) {
                        $text = $block['output_text'];
                        break;
                    }
                }
            }
        }

        // 2. Handle standard Chat Completion structure as fallback
        if (empty($text) && is_array($decoded)) {
            $text = $decoded['choices'][0]['message']['content'] ?? $decoded['output'] ?? $decoded['response'] ?? $decoded['text'] ?? '';
        }

        // 3. If still empty but the response itself looks like JSON, use the raw response
        if (empty($text) && (strpos($response, '{') !== false || strpos($response, '[') !== false)) {
            $text = $response;
        }
        
        if (is_array($text)) {
            $text = json_encode($text);
        }

        if (trim((string)$text) === '') {
            throw new RuntimeException('OpenAI returned empty content. Response: ' . substr($response, 0, 200));
        }

        return (string)$text;
    }

    public function getBookRecommendations($userProfile, $borrowingHistory, $availableBooks): array
    {
        $availableBooks = array_slice($availableBooks, 0, 40);
        $genrePriority = $this->deriveGenrePriority($borrowingHistory, $userProfile['favorite_genres'] ?? []);
        
        $stableProfile = $this->stabilizeProfile($userProfile);
        $stableHistory = $this->stabilizeHistory($borrowingHistory);
        $stableCatalog = $this->stabilizeForCacheKey($availableBooks);
        
        $dataToHash = [$stableProfile, $stableHistory, $stableCatalog, $genrePriority];
        $cacheKey = $this->buildCacheKey('recommendations', $dataToHash);

        $cached = $this->getCached($cacheKey);
        
        // Cooldown check: if we called the API recently, serve cache even if it's "stale"
        $lastCall = $_SESSION['ai_last_call'] ?? 0;
        $secondsSinceLast = time() - $lastCall;
        
        if ($cached !== null && $secondsSinceLast < $this->cooldownSeconds) {
            return $cached;
        }

        if ($cached !== null && !isset($_GET['t'])) {
            // Normal request (not forced refresh), serve cache if exists
            return $cached;
        }

        $prompt = $this->buildRecommendationsPrompt($userProfile, $borrowingHistory, $availableBooks, $genrePriority);
        $result = $this->askAiForRecommendations($prompt, $availableBooks, $genrePriority);
        
        // Update last call timestamp and cache
        $_SESSION['ai_last_call'] = time();
        $this->setCached($cacheKey, $result, $this->cacheTtlSeconds);
        return $result;
    }

    public function getSimilarBooks($bookTitle, $bookGenre, $availableBooks): array
    {
        $availableBooks = array_slice($availableBooks, 0, 40);
        $stableCatalog = $this->stabilizeForCacheKey($availableBooks);
        $cacheKey = $this->buildCacheKey('similar', [$bookTitle, $bookGenre, $stableCatalog]);
        $cached = $this->getCached($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a strict JSON recommendation engine for a library.\n"
            . "Find books similar to the selected book.\n"
            . "Selected book title: " . $bookTitle . "\n"
            . "Selected genre: " . $bookGenre . "\n"
            . "Use only these AVAILABLE books: " . $this->catalogJsonForPrompt($availableBooks) . "\n"
            . "Return only this JSON format: "
            . "{\"recommendations\":[{\"isbn\":\"\",\"title\":\"\",\"reason\":\"\",\"score\":0}]}.";

        $result = $this->askAiForRecommendations($prompt, $availableBooks);
        $this->setCached($cacheKey, $result);
        return $result;
    }

    public function getSearchSuggestions($searchQuery, $catalog): array
    {
        $catalog = array_slice($catalog, 0, 40);
        $stableCatalog = $this->stabilizeForCacheKey($catalog);
        $cacheKey = $this->buildCacheKey('search_suggestions', [$searchQuery, $stableCatalog]);
        $cached = $this->getCached($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are helping users search books in a library catalog.\n"
            . "User query: " . $searchQuery . "\n"
            . "Catalog (AVAILABLE only): " . $this->catalogJsonForPrompt($catalog) . "\n"
            . "Suggest the best matching books with clear reason and relevance score.\n"
            . "Return strict JSON only: "
            . "{\"recommendations\":[{\"isbn\":\"\",\"title\":\"\",\"reason\":\"\",\"score\":0}]}.";

        $result = $this->askAiForRecommendations($prompt, $catalog);
        $this->setCached($cacheKey, $result);
        return $result;
    }

    private function buildRecommendationsPrompt($userProfile, $borrowingHistory, $availableBooks, array $genrePriority): string
    {
        $genreLine = '';
        if (!empty($genrePriority)) {
            $genreLine = "6) Genre priority (strongest first): " . implode(', ', $genrePriority) . ".\n"
                . "   When user has rented/bought in a genre (e.g. FANTASY), recommend mostly same genre if available.\n"
                . "   Mention genre match in reason when applicable.\n";
        }

        $historyLine = '';
        if (!empty($borrowingHistory)) {
            $historyLine = "Activity history (most recent first): " . json_encode($borrowingHistory) . "\n";
        } else {
            $historyLine = "This is a NEW user with no reading history yet. Recommend a diverse, welcoming selection of popular and highly-rated books across different genres to help them discover what they enjoy.\n";
        }

        return "You are a recommendation engine for a Library Management System.\n"
            . "Goal: recommend books this user is likely to borrow or buy next.\n"
            . "User profile: " . json_encode($userProfile) . "\n"
            . $historyLine
            . "Available library books only: " . $this->catalogJsonForPrompt($availableBooks) . "\n"
            . "Rules:\n"
            . "1) Use ONLY books from available list.\n"
            . "2) Return up to 6 recommendations.\n"
            . "3) Prioritize strongest genre signals from history.\n"
            . "4) Use ratings when present.\n"
            . "5) Reason should be concise and personal.\n"
            . $genreLine
            . "Return strict JSON and nothing else:\n"
            . "{\"recommendations\":[{\"isbn\":\"\",\"title\":\"\",\"reason\":\"\",\"score\":0}]}";
    }

    private function catalogJsonForPrompt(array $books): string
    {
        $slim = [];
        foreach ($books as $b) {
            $slim[] = [
                'id' => (int)($b['id'] ?? 0),
                'isbn' => $b['isbn'] ?? '',
                'title' => $b['title'] ?? '',
                'author' => $b['author'] ?? '',
                'genre' => $b['genre'] ?? ''
            ];
        }
        return json_encode($slim);
    }

    private function deriveGenrePriority($borrowingHistory, $profileFavoriteGenres): array
    {
        $counts = [];
        foreach ($borrowingHistory as $row) {
            $genre = (string)($row['genre'] ?? '');
            if ($genre === '') {
                continue;
            }
            $counts[$genre] = ($counts[$genre] ?? 0) + 1;
        }
        arsort($counts);
        $fromHistory = array_keys($counts);
        $merged = array_values(array_unique(array_merge($fromHistory, $profileFavoriteGenres)));
        return array_slice($merged, 0, 5);
    }

    private function askAiForRecommendations($prompt, $availableBooks, array $genrePriority = []): array
    {
        $raw = $this->sendPrompt($prompt);
        $decoded = $this->extractJson($raw);
        if (!isset($decoded['recommendations']) || !is_array($decoded['recommendations'])) {
            // Check if it's nested or named differently
            if (isset($decoded['output']['recommendations'])) {
                $decoded = $decoded['output'];
            } elseif (isset($decoded['response']['recommendations'])) {
                $decoded = $decoded['response'];
            } else {
                throw new RuntimeException('Invalid JSON schema from OpenAI. Missing "recommendations" key. Raw: ' . substr($raw, 0, 200));
            }
        }

        $normalized = $this->normalizeRecommendations($decoded['recommendations'], $availableBooks);
        if (!count($normalized)) {
            throw new RuntimeException('OpenAI returned no usable recommendations matching our catalog.');
        }

        return ['source' => 'openai', 'recommendations' => array_slice($normalized, 0, 3)];
    }



    private function normalizeRecommendations($recommendations, $availableBooks): array
    {
        $catalogByIsbn = [];
        $catalogByTitle = [];
        foreach ($availableBooks as $book) {
            $isbn = strtolower(trim((string)($book['isbn'] ?? '')));
            $title = strtolower(trim((string)($book['title'] ?? '')));
            if ($isbn !== '') {
                $catalogByIsbn[$isbn] = $book;
            }
            if ($title !== '') {
                $catalogByTitle[$title] = $book;
            }
        }

        $result = [];
        $seen = [];
        foreach ($recommendations as $item) {
            $isbn = strtolower(trim((string)($item['isbn'] ?? '')));
            $title = strtolower(trim((string)($item['title'] ?? '')));
            $matchedBook = null;

            if ($isbn !== '' && isset($catalogByIsbn[$isbn])) {
                $matchedBook = $catalogByIsbn[$isbn];
            } elseif ($title !== '' && isset($catalogByTitle[$title])) {
                $matchedBook = $catalogByTitle[$title];
            }
            if (!$matchedBook) {
                continue;
            }

            $key = strtolower((string)$matchedBook['isbn']) . '|' . strtolower((string)$matchedBook['title']);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $scoreVal = $item['score'] ?? 0;
            if (is_numeric($scoreVal) && $scoreVal > 0 && $scoreVal <= 1) {
                $scoreVal = (int)($scoreVal * 100);
            } else {
                $scoreVal = (int)$scoreVal;
            }
            $score = max(0, min(100, $scoreVal));

            $result[] = [
                'book_id' => (int)($matchedBook['id'] ?? 0),
                'isbn' => $matchedBook['isbn'] ?? '',
                'title' => $matchedBook['title'] ?? '',
                'author' => $matchedBook['author'] ?? '',
                'genre' => $matchedBook['genre'] ?? '',
                'cover_image_url' => (string)($matchedBook['cover_image_url'] ?? ''),
                'reason' => (string)($item['reason'] ?? 'Good match for your reading profile.'),
                'score' => $score
            ];
        }

        usort($result, function ($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });
        return $result;
    }

    private function extractJson($raw): array
    {
        $raw = trim((string)$raw);
        
        // 1. Try direct decode
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $this->unwrapNestedJson($decoded);
        }

        // 2. Handle markdown blocks (e.g., ```json ... ```)
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $raw, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (is_array($decoded)) {
                return $this->unwrapNestedJson($decoded);
            }
        }

        // 3. Search for the first { and last } to isolate a potential JSON block
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        
        if ($start !== false && $end !== false && $end > $start) {
            $json = substr($raw, $start, $end - $start + 1);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return $this->unwrapNestedJson($decoded);
            }
        }

        throw new RuntimeException('Unable to parse JSON from OpenAI response. Raw: ' . substr($raw, 0, 150));
    }

    /**
     * If the decoded JSON is an array containing the OpenAI message structure,
     * drill down into it to find the recommendations.
     */
    private function unwrapNestedJson(array $data): array
    {
        // If it's already what we want, return it
        if (isset($data['recommendations'])) {
            return $data;
        }

        // If it's the array-based response [ { content: [ { text: "..." } ] } ]
        $msg = isset($data[0]) ? $data[0] : $data;
        if (isset($msg['content']) && is_array($msg['content'])) {
            foreach ($msg['content'] as $block) {
                $innerText = $block['text'] ?? $block['output_text'] ?? '';
                if (is_string($innerText) && (strpos($innerText, '{') !== false)) {
                    $innerDecoded = json_decode($innerText, true);
                    if (is_array($innerDecoded) && isset($innerDecoded['recommendations'])) {
                        return $innerDecoded;
                    }
                }
            }
        }

        // Check for common wrappers
        foreach (['output', 'response', 'data'] as $key) {
            if (isset($data[$key]['recommendations'])) {
                return $data[$key];
            }
        }

        return $data;
    }

    private function stabilizeForCacheKey(array $data): array
    {
        // If it's a list of books, only keep stable fields
        if (isset($data[0]['id'])) {
            return array_map(function ($b) {
                return [
                    'id' => (int)($b['id'] ?? 0),
                    'isbn' => (string)($b['isbn'] ?? ''),
                    'title' => (string)($b['title'] ?? ''),
                    'genre' => (string)($b['genre'] ?? '')
                ];
            }, $data);
        }
        return $data;
    }

    private function stabilizeProfile($profile): array
    {
        if (!is_array($profile)) return [];
        return [
            'id' => (int)($profile['id'] ?? 0),
            'email' => (string)($profile['email'] ?? ''),
            'favorite_genres' => $profile['favorite_genres'] ?? []
        ];
    }

    private function stabilizeHistory($history): array
    {
        if (!is_array($history)) return [];
        return array_map(function($h) {
            return [
                'book_id' => (int)($h['book_id'] ?? 0),
                'genre' => (string)($h['genre'] ?? ''),
                'rating' => $h['rating'] ?? null
            ];
        }, $history);
    }



    private function buildCacheKey($prefix, $data): string
    {
        return $prefix . '_' . sha1(json_encode($data));
    }

    private function getCached($key): ?array
    {
        if (isset($_SESSION['ai_cache'][$key])) {
            $entry = $_SESSION['ai_cache'][$key];
            if (($entry['expires_at'] ?? 0) > time()) {
                return $entry['value'] ?? null;
            }
            unset($_SESSION['ai_cache'][$key]);
        }

        $file = $this->cacheDir . '/' . $key . '.json';
        if (!file_exists($file)) {
            return null;
        }
        $payload = json_decode((string)file_get_contents($file), true);
        if (!is_array($payload) || ($payload['expires_at'] ?? 0) <= time()) {
            @unlink($file);
            return null;
        }
        return $payload['value'] ?? null;
    }

    private function setCached($key, $value, $ttl = null): void
    {
        $ttl = $ttl ?? $this->cacheTtlSeconds;
        $payload = [
            'expires_at' => time() + $ttl,
            'value' => $value
        ];
        $_SESSION['ai_cache'][$key] = $payload;
        @file_put_contents($this->cacheDir . '/' . $key . '.json', json_encode($payload));
    }
}
?>
