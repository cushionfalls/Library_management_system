<?php
/**
 * Wallet JSON API
 * - getBalance
 * - getTransactions
 * - topUp
 * - downloadStatement (CSV)
 */
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Wallet.php';
require_once __DIR__ . '/../classes/OTP.php';
require_once __DIR__ . '/../classes/EmailService.php';

$session = new Session();

function wallet_json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    ob_clean();
    echo json_encode($data);
    exit;
}

if (!$session->isLoggedIn()) {
    wallet_json(['success' => false, 'error' => 'Unauthorized'], 401);
}

$userId = (int)$session->getUserId();
$action = $_GET['action'] ?? 'getBalance';
$wallet = new Wallet();
$otpSvc = new OTP();
$emailSvc = new EmailService();

function wallet_me($session) {
    $me = $session->getUserData();
    if (!$me) return null;
    return [
        'id' => (int)($me['id'] ?? 0),
        'first_name' => (string)($me['first_name'] ?? ''),
        'last_name' => (string)($me['last_name'] ?? ''),
        'email' => (string)($me['email'] ?? ''),
    ];
}

try {
    switch ($action) {
        case 'getBalance': {
            $bal = $wallet->getBalance($userId);
            wallet_json(['success' => true, 'balance' => $bal]);
        }

        case 'getTransactions': {
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = (int)($_GET['offset'] ?? 0);
            $tx = $wallet->getTransactions($userId, $limit, $offset);
            wallet_json(['success' => true, 'transactions' => $tx]);
        }

        case 'topUp': {
            wallet_json(['success' => false, 'error' => 'OTP required. Use action=request-topup-otp then action=verify-topup-otp'], 400);
        }

        case 'request-topup-otp': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                wallet_json(['success' => false, 'error' => 'Invalid request method'], 405);
            }

            $me = wallet_me($session);
            if (!$me || $me['email'] === '') {
                wallet_json(['success' => false, 'error' => 'Unable to resolve user email'], 400);
            }

            $amount = (int)($_POST['amount'] ?? 0);
            $method = trim((string)($_POST['method'] ?? 'OTHER'));
            if ($amount <= 0) {
                wallet_json(['success' => false, 'error' => 'Amount must be greater than 0'], 400);
            }
            if ($amount > Wallet::TOPUP_MAX_AMOUNT) {
                wallet_json(['success' => false, 'error' => 'Amount exceeds limit of ' . Wallet::TOPUP_MAX_AMOUNT], 400);
            }

            $otp = $otpSvc->generate($me['email']);
            if (!$otp) {
                wallet_json(['success' => false, 'error' => 'Failed to generate OTP'], 500);
            }

            $sent = $emailSvc->sendWalletTopUpOTP($me['email'], $otp, $me['first_name']);
            if (!$sent) {
                wallet_json(['success' => false, 'error' => 'Failed to send OTP email'], 500);
            }

            $session->set('wallet_topup_amount', $amount);
            $session->set('wallet_topup_method', $method);
            $session->set('wallet_topup_expires', time() + 300);

            wallet_json(['success' => true, 'message' => 'OTP sent to your email']);
        }

        case 'verify-topup-otp': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                wallet_json(['success' => false, 'error' => 'Invalid request method'], 405);
            }

            $me = wallet_me($session);
            if (!$me || $me['email'] === '') {
                wallet_json(['success' => false, 'error' => 'Unable to resolve user email'], 400);
            }

            $otp = trim((string)($_POST['otp'] ?? ''));
            if ($otp === '') {
                wallet_json(['success' => false, 'error' => 'OTP is required'], 400);
            }

            $amount = (int)$session->get('wallet_topup_amount', 0);
            $method = (string)$session->get('wallet_topup_method', 'OTHER');
            $expires = (int)$session->get('wallet_topup_expires', 0);
            if ($amount <= 0 || time() > $expires) {
                wallet_json(['success' => false, 'error' => 'Top up request expired. Please request a new OTP.'], 400);
            }

            if (!$otpSvc->verify($me['email'], $otp)) {
                wallet_json(['success' => false, 'error' => 'Invalid or expired OTP'], 400);
            }

            $session->remove('wallet_topup_amount');
            $session->remove('wallet_topup_method');
            $session->remove('wallet_topup_expires');

            $result = $wallet->topUp($userId, $amount, $method);
            wallet_json($result, $result['success'] ? 200 : 400);
        }

        case 'adminRefund': {
            if (!$session->isAdmin()) {
                wallet_json(['success' => false, 'error' => 'Forbidden'], 403);
            }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                wallet_json(['success' => false, 'error' => 'Invalid request method'], 405);
            }
            $targetUserId = (int)($_POST['user_id'] ?? 0);
            $amount = (int)($_POST['amount'] ?? 0);
            if ($targetUserId <= 0) wallet_json(['success' => false, 'error' => 'user_id is required'], 400);
            $result = $wallet->refund($targetUserId, $amount);
            wallet_json($result, $result['success'] ? 200 : 400);
        }

        case 'adminAllTransactions': {
            if (!$session->isAdmin()) {
                wallet_json(['success' => false, 'error' => 'Forbidden'], 403);
            }
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            $filterUserId = $_GET['user_id'] ?? null;
            $rows = $wallet->getAllTransactions($limit, $offset, $filterUserId);
            wallet_json(['success' => true, 'transactions' => $rows]);
        }

        case 'downloadStatement': {
            $limit = (int)($_GET['limit'] ?? 500);
            $limit = max(1, min(Wallet::TX_MAX_LIMIT, $limit));
            $tx = $wallet->getTransactions($userId, $limit, 0);

            $filename = 'wallet_statement_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store');
            ob_clean();

            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Reason', 'Amount']);
            foreach ($tx as $row) {
                fputcsv($out, [$row['created_at'], $row['type'], $row['reason'], $row['amount']]);
            }
            fclose($out);
            exit;
        }

        default:
            wallet_json(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    wallet_json(['success' => false, 'error' => 'Server error'], 500);
}

