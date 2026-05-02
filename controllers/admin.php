<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/AdminDashboard.php';

class AdminController {
    private $session;
    private $service;

    public function __construct() {
        $this->session = new Session();
        $this->service = new AdminDashboard();
    }

    public function handle($action) {
        if (!$this->session->isLoggedIn()) {
            return ['error' => 'Unauthorized'];
        }

        if (!$this->session->isAdmin() && !$this->session->isLibrarian()) {
            return ['error' => 'Forbidden'];
        }

        switch ($action) {
            case 'overview':
                return ['success' => true, 'data' => $this->service->getOverview()];
            case 'books':
                return ['success' => true, 'data' => $this->service->getBooks(200)];
            case 'recent-users':
                return ['success' => true, 'data' => $this->service->getRecentUsers(200)];
            case 'recent-transactions':
                return ['success' => true, 'data' => $this->service->getRecentTransactions(200)];
            case 'overdue-books':
                return ['success' => true, 'data' => $this->service->getOverdueBooks(200)];
            case 'create-book':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                $assets = $this->processBookUploads(
                    $_POST['existing_cover_image'] ?? null,
                    $_POST['existing_online_copy_pdf'] ?? null
                );
                if (!$assets['success']) {
                    return $assets;
                }
                return $this->service->createBook(
                    $_POST['isbn'] ?? '',
                    $_POST['name'] ?? '',
                    $_POST['publisher'] ?? '',
                    $_POST['number_of_copies'] ?? 0,
                    $_POST['price'] ?? 0,
                    [
                        'description' => $_POST['description'] ?? '',
                        'author' => $_POST['author'] ?? '',
                        'published_at' => $_POST['published_at'] ?? '',
                        'language' => $_POST['language'] ?? 'English',
                        'genre' => $_POST['genre'] ?? 'OTHERS',
                        'online_rent_price' => $_POST['online_rent_price'] ?? null,
                        'online_buy_price' => $_POST['online_buy_price'] ?? null,
                        'cover_image' => $assets['cover_image'],
                        'online_copy_pdf' => $assets['online_copy_pdf']
                    ]
                );
            case 'update-book':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                $assets = $this->processBookUploads(
                    $_POST['existing_cover_image'] ?? null,
                    $_POST['existing_online_copy_pdf'] ?? null
                );
                if (!$assets['success']) {
                    return $assets;
                }
                return $this->service->updateBook(
                    $_POST['id'] ?? 0,
                    $_POST['isbn'] ?? '',
                    $_POST['name'] ?? '',
                    $_POST['publisher'] ?? '',
                    $_POST['number_of_copies'] ?? 0,
                    $_POST['price'] ?? 0,
                    [
                        'description' => $_POST['description'] ?? '',
                        'author' => $_POST['author'] ?? '',
                        'published_at' => $_POST['published_at'] ?? '',
                        'language' => $_POST['language'] ?? 'English',
                        'genre' => $_POST['genre'] ?? 'OTHERS',
                        'online_rent_price' => $_POST['online_rent_price'] ?? null,
                        'online_buy_price' => $_POST['online_buy_price'] ?? null,
                        'cover_image' => $assets['cover_image'],
                        'online_copy_pdf' => $assets['online_copy_pdf']
                    ]
                );
            case 'delete-book':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                return $this->service->deleteBook($_POST['id'] ?? 0);
            case 'book-by-isbn':
                return $this->lookupBookByIsbn($_GET['isbn'] ?? '');
            case 'create-user':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                return $this->service->createUser(
                    $_POST['first_name'] ?? '',
                    $_POST['last_name'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? '',
                    $_POST['role'] ?? 'USER',
                    $_POST['is_active'] ?? 1
                );
            case 'update-user':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                return $this->service->updateUser(
                    $_POST['id'] ?? 0,
                    $_POST['first_name'] ?? '',
                    $_POST['last_name'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['role'] ?? 'USER',
                    $_POST['is_active'] ?? 0
                );
            case 'delete-user':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                return $this->service->deleteUser(
                    $_POST['id'] ?? 0,
                    $this->session->getUserId()
                );
            case 'dashboard':
                return [
                    'success' => true,
                    'data' => [
                        'overview' => $this->service->getOverview(),
                        'books' => $this->service->getBooks(200),
                        'recent_users' => $this->service->getRecentUsers(200),
                        'recent_transactions' => $this->service->getRecentTransactions(200),
                        'overdue_books' => $this->service->getOverdueBooks(200)
                    ]
                ];
            default:
                return ['error' => 'Invalid action'];
        }
    }

    private function processBookUploads($existingCoverImage, $existingOnlinePdf) {
        $coverImagePath = $this->normalizeAssetPath($existingCoverImage);
        $onlinePdfPath = $this->normalizeAssetPath($existingOnlinePdf);

        if (!empty($_FILES['cover_image']['name'])) {
            $coverUpload = $this->storeUpload(
                $_FILES['cover_image'],
                __DIR__ . '/../public/uploads/images',
                'book-cover-',
                ALLOWED_IMAGE_TYPES
            );
            if (!$coverUpload['success']) {
                return $coverUpload;
            }
            $coverImagePath = '/public/uploads/images/' . $coverUpload['filename'];
        }

        if (!empty($_FILES['online_copy_pdf']['name'])) {
            $pdfUpload = $this->storeUpload(
                $_FILES['online_copy_pdf'],
                __DIR__ . '/../public/uploads/epubs',
                'book-epub-',
                ALLOWED_PDF_TYPES
            );
            if (!$pdfUpload['success']) {
                return $pdfUpload;
            }
            $onlinePdfPath = '/public/uploads/epubs/' . $pdfUpload['filename'];
        }

        return [
            'success' => true,
            'cover_image' => $coverImagePath,
            'online_copy_pdf' => $onlinePdfPath
        ];
    }

    private function storeUpload($file, $targetDir, $namePrefix, $allowedTypes) {
        if (!isset($file['error']) || (int)$file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload failed'];
        }

        $tmpPath = $file['tmp_name'] ?? '';
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['success' => false, 'message' => 'Invalid uploaded file'];
        }

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            return ['success' => false, 'message' => 'Unable to create upload directory'];
        }

        $detectedType = mime_content_type($tmpPath) ?: '';
        if (!in_array($detectedType, $allowedTypes, true)) {
            return ['success' => false, 'message' => 'Unsupported file format'];
        }

        $originalName = $file['name'] ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension === '') {
            if ($detectedType === 'application/pdf') {
                $extension = 'pdf';
            } elseif (in_array($detectedType, ['application/epub+zip', 'application/zip'])) {
                $extension = 'epub';
            } else {
                $extension = 'jpg';
            }
        }

        $filename = $namePrefix . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['success' => false, 'message' => 'Unable to save uploaded file'];
        }

        return ['success' => true, 'filename' => $filename];
    }

    private function normalizeAssetPath($path) {
        $path = trim((string)$path);
        if ($path === '') {
            return null;
        }
        return $path;
    }

    private function lookupBookByIsbn($isbn) {
        $isbn = preg_replace('/[^0-9Xx]/', '', (string)$isbn);
        if ($isbn === '') {
            return ['success' => false, 'message' => 'ISBN is required'];
        }

        if (!defined('GOOGLE_BOOKS_API_KEY') || GOOGLE_BOOKS_API_KEY === '') {
            return ['success' => false, 'message' => 'Google Books API key is missing'];
        }

        $url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . urlencode($isbn) . '&key=' . urlencode(GOOGLE_BOOKS_API_KEY);
        $json = $this->httpGet($url);
        if ($json === false || $json === '') {
            return ['success' => false, 'message' => 'Failed to fetch data from Google Books'];
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['items'][0]['volumeInfo'])) {
            return ['success' => false, 'message' => 'No book found for this ISBN'];
        }

        $volume = $data['items'][0]['volumeInfo'];
        $dateRaw = (string)($volume['publishedDate'] ?? '');
        $publishedAt = null;
        if (preg_match('/^\d{4}$/', $dateRaw)) {
            $publishedAt = $dateRaw . '-01-01';
        } elseif (preg_match('/^\d{4}-\d{2}$/', $dateRaw)) {
            $publishedAt = $dateRaw . '-01';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRaw)) {
            $publishedAt = $dateRaw;
        }

        $genre = 'OTHERS';
        $categories = $volume['categories'] ?? [];
        if (!empty($categories[0])) {
            $genre = $this->mapCategoryToGenre($categories[0]);
        }

        $book = [
            'name' => (string)($volume['title'] ?? ''),
            'description' => (string)($volume['description'] ?? ''),
            'author' => !empty($volume['authors'][0]) ? (string)$volume['authors'][0] : '',
            'publisher' => (string)($volume['publisher'] ?? ''),
            'published_at' => $publishedAt,
            'language' => ucfirst(strtolower((string)($volume['language'] ?? 'English'))),
            'genre' => $genre,
            'cover_image_url' => (string)($volume['imageLinks']['thumbnail'] ?? '')
        ];

        return ['success' => true, 'data' => $book];
    }

    private function mapCategoryToGenre($category) {
        $value = strtoupper((string)$category);
        if (strpos($value, 'FANTASY') !== false) return 'FANTASY';
        if (strpos($value, 'SCIENCE') !== false || strpos($value, 'FICTION') !== false) return 'SCIENCE_FICTION';
        if (strpos($value, 'MYSTERY') !== false) return 'MYSTERY';
        if (strpos($value, 'ROMANCE') !== false) return 'ROMANCE';
        if (strpos($value, 'THRILLER') !== false) return 'THRILLER';
        if (strpos($value, 'BIOGRAPH') !== false || strpos($value, 'MEMOIR') !== false) return 'BIOGRAPHY';
        if (strpos($value, 'HISTORY') !== false) return 'HISTORY';
        if (strpos($value, 'NON') !== false) return 'NON_FICTION';
        return 'OTHERS';
    }

    private function httpGet($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $output = curl_exec($ch);
            curl_close($ch);
            return $output === false ? false : $output;
        }

        return @file_get_contents($url);
    }
}

try {
    $controller = new AdminController();
    $action = $_GET['action'] ?? 'dashboard';
    $response = $controller->handle($action);
    ob_clean();
    echo json_encode($response);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}

