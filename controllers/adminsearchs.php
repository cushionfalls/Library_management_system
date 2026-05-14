<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/adminsearch.php';

class AdminSearchController {
    private $session;
    private $service;

    public function __construct() {
        $this->session = new Session();
        $this->service = new AdminSearch();
    }

    public function handle($action) {
        if (!$this->session->isLoggedIn()) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }
        if (!$this->session->isAdmin() && !$this->session->isLibrarian()) {
            return ['success' => false, 'error' => 'Forbidden'];
        }

        switch ($action) {
            case 'search':
                $type = $_GET['type'] ?? $_POST['type'] ?? 'books';
                $q = $_GET['q'] ?? $_POST['q'] ?? '';
                $allowed = ['books', 'users', 'transactions'];
                if (!in_array(strtolower((string)$type), $allowed, true)) {
                    return ['success' => false, 'error' => 'Invalid search type'];
                }
                $type = strtolower((string)$type);
                $data = $this->service->search($type, (string)$q, 200);
                return [
                    'success' => true,
                    'type' => $type,
                    'data' => $data
                ];
            default:
                return ['success' => false, 'error' => 'Invalid action'];
        }
    }
}

try {
    $controller = new AdminSearchController();
    $action = $_GET['action'] ?? 'search';
    $response = $controller->handle($action);
    ob_clean();
    echo json_encode($response);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
