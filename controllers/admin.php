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
                return $this->service->createBook(
                    $_POST['isbn'] ?? '',
                    $_POST['name'] ?? '',
                    $_POST['publisher'] ?? '',
                    $_POST['number_of_copies'] ?? 0,
                    $_POST['price'] ?? 0
                );
            case 'update-book':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                return $this->service->updateBook(
                    $_POST['id'] ?? 0,
                    $_POST['isbn'] ?? '',
                    $_POST['name'] ?? '',
                    $_POST['publisher'] ?? '',
                    $_POST['number_of_copies'] ?? 0,
                    $_POST['price'] ?? 0
                );
            case 'delete-book':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return ['error' => 'Invalid request method'];
                }
                return $this->service->deleteBook($_POST['id'] ?? 0);
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

