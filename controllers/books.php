<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/BrowseCatalog.php';
require_once __DIR__ . '/../classes/Session.php';

class BooksController {
    private $catalog;
    private $session;

    public function __construct() {
        $this->catalog = new BrowseCatalog();
        $this->session = new Session();
    }

    public function handle($action) {
        switch ($action) {
            case 'catalog':
                return [
                    'success' => true,
                    'data' => $this->catalog->getCatalog([
                        'page' => $_GET['page'] ?? 1,
                        'per_page' => $_GET['per_page'] ?? 12,
                        'search' => $_GET['search'] ?? '',
                        'genre' => $_GET['genre'] ?? 'ALL',
                        'available_only' => $_GET['available_only'] ?? 1,
                        'sort' => $_GET['sort'] ?? 'recent'
                    ])
                ];

            case 'suggestions':
                return [
                    'success' => true,
                    'books' => $this->catalog->getSuggestions(
                        $_GET['search'] ?? '',
                        $_GET['limit'] ?? 6
                    )
                ];

            case 'detail':
                $book = $this->catalog->getBookDetail($_GET['book_id'] ?? 0);
                if (!$book) {
                    return ['success' => false, 'message' => 'Book not found'];
                }
                return ['success' => true, 'data' => $book];

            case 'add-review':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['success' => false, 'message' => 'Invalid request method'];
                }
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login to submit a review'];
                }
                return $this->catalog->createReview(
                    $_POST['book_id'] ?? 0,
                    $this->session->getUserId(),
                    $_POST['rating'] ?? 0,
                    $_POST['review'] ?? ''
                );

            case 'edit-review':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['success' => false, 'message' => 'Invalid request method'];
                }
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login to edit your review'];
                }
                return $this->catalog->updateReview(
                    $_POST['review_id'] ?? 0,
                    $this->session->getUserId(),
                    $_POST['rating'] ?? 0,
                    $_POST['review'] ?? ''
                );

            case 'delete-review':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['success' => false, 'message' => 'Invalid request method'];
                }
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login to delete your review'];
                }
                return $this->catalog->deleteReview(
                    $_POST['review_id'] ?? 0,
                    $this->session->getUserId()
                );

            default:
                return ['error' => 'Invalid action'];
        }
    }
}

try {
    $controller = new BooksController();
    $action = $_GET['action'] ?? 'catalog';
    $response = $controller->handle($action);
    ob_clean();
    echo json_encode($response);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
