<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/BrowseCatalog.php';
require_once __DIR__ . '/../classes/DigitalLibrary.php';
require_once __DIR__ . '/../classes/Session.php';

class BooksController {
    private $catalog;
    private $library;
    private $session;

    public function __construct() {
        $this->catalog = new BrowseCatalog();
        $this->library = new DigitalLibrary();
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
                if ($this->session->isLoggedIn()) {
                    $access = $this->library->getAccessForUserBook($this->session->getUserId(), (int)$book['id']);
                    $book['user_access'] = $access;
                } else {
                    $book['user_access'] = null;
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

            case 'purchase-online':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['success' => false, 'message' => 'Invalid request method'];
                }
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login to buy books'];
                }
                return $this->library->purchaseOnlineBook(
                    $this->session->getUserId(),
                    $_POST['book_id'] ?? 0
                );

            case 'unlock-with-membership':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['success' => false, 'message' => 'Invalid request method'];
                }
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login to use membership access'];
                }
                return $this->library->unlockWithMembership(
                    $this->session->getUserId(),
                    $_POST['book_id'] ?? 0
                );

            case 'my-books':
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login first'];
                }
                return [
                    'success' => true,
                    'books' => $this->library->getMyBooks($this->session->getUserId())
                ];

            case 'save-progress':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['success' => false, 'message' => 'Invalid request method'];
                }
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login first'];
                }
                return $this->library->saveProgress(
                    $this->session->getUserId(),
                    $_POST['book_id'] ?? 0,
                    $_POST['progress_percent'] ?? 0,
                    $_POST['current_location'] ?? ''
                );

            case 'reader-state':
                if (!$this->session->isLoggedIn()) {
                    return ['success' => false, 'message' => 'Please login first'];
                }
                $state = $this->library->getReaderState(
                    $this->session->getUserId(),
                    $_GET['book_id'] ?? 0
                );
                if ($state === null) {
                    return ['success' => false, 'message' => 'Access denied'];
                }
                return ['success' => true, 'state' => $state];

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
