<?php
require_once __DIR__ . '/../config/config.php';

class GeminiService
{
    private $apiKey;
    private $endpoint;
    private $cacheDir;
    private $cacheTtlSeconds;

    public function __construct($apiKey = GEMINI_API_KEY, $endpoint = GEMINI_ENDPOINT, $cacheTtlSeconds = 3600)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;
        $this->cacheTtlSeconds = (int)$cacheTtlSeconds;
        $this->cacheDir = __DIR__ . '/../cache/gemini';

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }
    }

    /**
     * Send a raw prompt to Gemini API and return generated text.
     */
    public function sendPrompt($prompt): string
    {
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'topP' => 0.9,
                'maxOutputTokens' => 1200
            ]
        ];

        $url = $this->endpoint . '?key=' . urlencode($this->apiKey);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 8
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError) {
            throw new RuntimeException('Gemini request failed: ' . ($curlError ?: 'unknown cURL error'));
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('Gemini request failed with HTTP ' . $httpCode);
        }

        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            throw new RuntimeException('Gemini returned empty content.');
        }

        return $text;
    }

    public function getBookRecommendations($userProfile, $borrowingHistory, $availableBooks): array
    {
        $availableBooks = array_slice($availableBooks, 0, 40);
        $genrePriority = $this->deriveGenrePriority($borrowingHistory, $userProfile['favorite_genres'] ?? []);
        $cacheKey = $this->buildCacheKey('recommendations', [$userProfile, $borrowingHistory, $availableBooks, $genrePriority]);

        $cached = $this->getCached($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = $this->buildRecommendationsPrompt($userProfile, $borrowingHistory, $availableBooks, $genrePriority);
        $result = $this->askGeminiForRecommendations($prompt, $availableBooks, $genrePriority);
        $this->setCached($cacheKey, $result);
        return $result;
    }

    public function getSimilarBooks($bookTitle, $bookGenre, $availableBooks): array
    {
        $availableBooks = array_slice($availableBooks, 0, 40);
        $cacheKey = $this->buildCacheKey('similar', [$bookTitle, $bookGenre, $availableBooks]);
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

        $result = $this->askGeminiForRecommendations($prompt, $availableBooks);
        $this->setCached($cacheKey, $result);
        return $result;
    }

    public function getSearchSuggestions($searchQuery, $catalog): array
    {
        $catalog = array_slice($catalog, 0, 40);
        $cacheKey = $this->buildCacheKey('search_suggestions', [$searchQuery, $catalog]);
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

        $result = $this->askGeminiForRecommendations($prompt, $catalog);
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

        return "You are a recommendation engine for a Library Management System.\n"
            . "Goal: recommend books this user is likely to borrow or buy next.\n"
            . "User profile: " . json_encode($userProfile) . "\n"
            . "Activity history (most recent first): " . json_encode($borrowingHistory) . "\n"
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

    private function askGeminiForRecommendations($prompt, $availableBooks, array $genrePriority = []): array
    {
        try {
            $raw = $this->sendPrompt($prompt);
            $decoded = $this->extractJson($raw);
            if (!isset($decoded['recommendations']) || !is_array($decoded['recommendations'])) {
                throw new RuntimeException('Invalid JSON schema from Gemini.');
            }

            $normalized = $this->normalizeRecommendations($decoded['recommendations'], $availableBooks);
            if (!count($normalized)) {
                throw new RuntimeException('Gemini returned no usable recommendations.');
            }

            return ['source' => 'gemini', 'recommendations' => array_slice($normalized, 0, 6)];
        } catch (Throwable $e) {
            error_log('GeminiService fallback used: ' . $e->getMessage());
            return ['source' => 'fallback', 'recommendations' => $this->fallbackRecommendations($availableBooks, $genrePriority)];
        }
    }

    private function fallbackRecommendations($availableBooks, array $genrePriority = []): array
    {
        $books = $availableBooks;
        if (!empty($genrePriority)) {
            usort($books, function ($a, $b) use ($genrePriority) {
                $ga = (string)($a['genre'] ?? '');
                $gb = (string)($b['genre'] ?? '');
                $ia = array_search($ga, $genrePriority, true);
                $ib = array_search($gb, $genrePriority, true);
                $ra = $ia === false ? PHP_INT_MAX : (int)$ia;
                $rb = $ib === false ? PHP_INT_MAX : (int)$ib;
                if ($ra !== $rb) {
                    return $ra <=> $rb;
                }
                return strcmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
            });
        }

        $fallback = [];
        foreach (array_slice($books, 0, 6) as $book) {
            $g = (string)($book['genre'] ?? '');
            $reason = 'Popular available pick in the library catalog.';
            if ($g !== '' && !empty($genrePriority) && $g === ($genrePriority[0] ?? '')) {
                $reason = 'Matches your recent interest in ' . $g . ' titles from your library activity.';
            }
            $fallback[] = [
                'book_id' => (int)($book['id'] ?? 0),
                'isbn' => $book['isbn'] ?? '',
                'title' => $book['title'] ?? 'Unknown Title',
                'author' => $book['author'] ?? '',
                'genre' => $g,
                'cover_image_url' => $book['cover_image_url'] ?? '',
                'reason' => $reason,
                'score' => 70
            ];
        }
        return $fallback;
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

            $score = (int)($item['score'] ?? 0);
            $score = max(0, min(100, $score));

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
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        throw new RuntimeException('Unable to parse JSON from Gemini response.');
    }

    private function buildCacheKey($prefix, $data): string
    {
        return $prefix . '_' . sha1(json_encode($data));
    }

    private function getCached($key): ?array
    {
        if (isset($_SESSION['gemini_cache'][$key])) {
            $entry = $_SESSION['gemini_cache'][$key];
            if (($entry['expires_at'] ?? 0) > time()) {
                return $entry['value'] ?? null;
            }
            unset($_SESSION['gemini_cache'][$key]);
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

    private function setCached($key, $value): void
    {
        $payload = [
            'expires_at' => time() + $this->cacheTtlSeconds,
            'value' => $value
        ];
        $_SESSION['gemini_cache'][$key] = $payload;
        @file_put_contents($this->cacheDir . '/' . $key . '.json', json_encode($payload));
    }
}
?>
