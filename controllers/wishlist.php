<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Wishlist.php';
require_once __DIR__ . '/../classes/Session.php';

class WishlistController {
    private $wishlist;
    private $session;

    public function __construct() {
        $this->wishlist = new Wishlist();
        $this->session = new Session();
    }

    public function handle($action) {
        if (!$this->session->isLoggedIn()) {
            return ['success' => false, 'message' => 'Please login to use the wishlist', 'is_guest' => true];
        }

        $userId = $this->session->getUserId();
        $role = $this->session->getRole();

        if ($role === 'ADMIN' || $role === 'LIBRARIAN') {
            return ['success' => false, 'message' => 'Administrators and Librarians cannot use the wishlist feature.'];
        }

        switch ($action) {
            case 'toggle':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['success' => false, 'message' => 'Invalid request method'];
                }
                $bookId = (int)($_POST['book_id'] ?? 0);
                
                // Check if user already owns the book
                require_once __DIR__ . '/../classes/DigitalLibrary.php';
                $library = new DigitalLibrary();
                $access = $library->getAccessForUserBook($userId, $bookId);
                if ($access) {
                    return ['success' => false, 'message' => 'You already have access to this book'];
                }

                return $this->wishlist->toggle($userId, $bookId);

            case 'list':
                return [
                    'success' => true,
                    'data' => $this->wishlist->getWishlist($userId)
                ];

            case 'count':
                return [
                    'success' => true,
                    'count' => $this->wishlist->getCount($userId)
                ];

            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }
    }
}

try {
    $controller = new WishlistController();
    $action = $_GET['action'] ?? 'list';
    $response = $controller->handle($action);
    ob_clean();
    echo json_encode($response);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
