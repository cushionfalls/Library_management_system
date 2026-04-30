<?php
/**
 * Membership JSON API
 * - getPlans
 * - getStatus
 * - purchase (wallet)
 */
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Membership.php';

$session = new Session();

function membership_json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    ob_clean();
    echo json_encode($data);
    exit;
}

if (!$session->isLoggedIn()) {
    membership_json(['success' => false, 'error' => 'Unauthorized'], 401);
}

$userId = (int)$session->getUserId();
$action = $_GET['action'] ?? 'getStatus';
$svc = new Membership();

try {
    switch ($action) {
        case 'getPlans': {
            $plans = $svc->getPlans();
            membership_json(['success' => true, 'plans' => $plans]);
        }

        case 'getStatus': {
            $active = $svc->getActiveMembership($userId);
            membership_json(['success' => true, 'active' => $active]);
        }

        case 'getHistory': {
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = (int)($_GET['offset'] ?? 0);
            $rows = $svc->getPurchaseHistory($userId, $limit, $offset);
            membership_json(['success' => true, 'history' => $rows]);
        }

        case 'purchase': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                membership_json(['success' => false, 'error' => 'Invalid request method'], 405);
            }
            $planId = (int)($_POST['plan_id'] ?? 0);
            if ($planId <= 0) {
                membership_json(['success' => false, 'error' => 'plan_id is required'], 400);
            }

            $result = $svc->purchaseWithWallet($userId, $planId);
            membership_json($result, $result['success'] ? 200 : 400);
        }

        default:
            membership_json(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    membership_json(['success' => false, 'error' => 'Server error'], 500);
}

