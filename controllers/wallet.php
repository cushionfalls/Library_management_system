<?php
ob_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Wallet.php';

$session = new Session();

/* ── helpers ─────────────────────────────────────────────────────────────── */

function wallet_json(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    ob_clean();
    echo json_encode($data);
    exit;
}

function wallet_redirect(string $url): void {
    ob_clean();
    header('Location: ' . $url);
    exit;
}

function wallet_me(Session $session): ?array {
    $me = $session->getUserData();
    if (!$me) return null;
    return [
        'id'         => (int)($me['id']         ?? 0),
        'first_name' => (string)($me['first_name'] ?? ''),
        'last_name'  => (string)($me['last_name']  ?? ''),
        'email'      => (string)($me['email']      ?? ''),
        'phone'      => (string)($me['phone_number'] ?? '9800000001'),
    ];
}

$baseUrl = rtrim(defined('APP_URL') ? APP_URL : '', '/');

$action        = $_GET['action'] ?? 'getBalance';

if (!$session->isLoggedIn()) {
    wallet_json(['success' => false, 'error' => 'Unauthorized'], 401);
}

$userId   = $session->isLoggedIn() ? (int)$session->getUserId() : 0;
$wallet   = new Wallet();

function stripe_request(string $method, string $path, array $params = []): array {
    if (!defined('STRIPE_SECRET_KEY') || STRIPE_SECRET_KEY === '') {
        return ['ok' => false, 'error' => 'Stripe secret key is not configured'];
    }

    $url = 'https://api.stripe.com' . $path;
    $ch  = curl_init();

    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . STRIPE_SECRET_KEY,
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ];

    $m = strtoupper($method);
    if ($m === 'POST') {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
    } else {
        $opts[CURLOPT_CUSTOMREQUEST] = $m;
    }

    curl_setopt_array($ch, $opts);
    $raw     = curl_exec($ch);
    $curlErr = curl_error($ch);
    $status  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr || !$raw) return ['ok' => false, 'error' => 'Stripe request failed'];

    $data = json_decode($raw, true);
    if ($status >= 400) {
        return ['ok' => false, 'error' => $data['error']['message'] ?? 'Stripe API error'];
    }
    return ['ok' => true, 'data' => $data];
}

/* ═══════════════════════════════════════════════════════════════════════════
 *  ROUTER
 * ═══════════════════════════════════════════════════════════════════════════ */
try {
    switch ($action) {

        /* ── Existing: balance ───────────────────────────────────────────── */
        case 'getBalance': {
            if ($session->isAdmin() || $session->isLibrarian()) {
                wallet_json(['success' => false, 'error' => 'Administrators and Librarians do not have a wallet.'], 403);
            }
            wallet_json(['success' => true, 'balance' => $wallet->getBalance($userId)]);
        }

        /* ── Existing: transactions ──────────────────────────────────────── */
        case 'getTransactions': {
            if ($session->isAdmin() || $session->isLibrarian()) {
                wallet_json(['success' => false, 'error' => 'Administrators and Librarians do not have wallet transactions.'], 403);
            }
            $limit  = (int)($_GET['limit']  ?? 20);
            $offset = (int)($_GET['offset'] ?? 0);
            $tx     = $wallet->getTransactions($userId, $limit, $offset);
            wallet_json(['success' => true, 'transactions' => $tx]);
        }

        case 'stripe-create-intent': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') wallet_json(['success' => false, 'error' => 'POST required'], 405);
            if ($session->isAdmin() || $session->isLibrarian()) {
                wallet_json(['success' => false, 'error' => 'Administrators and Librarians cannot top up a wallet.'], 403);
            }
            if (!$session->isVerified()) {
                wallet_json(['success' => false, 'error' => 'Please verify your email address to top up your wallet.'], 403);
            }

            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$session->validateCSRFToken($csrfToken)) {
                wallet_json(['success' => false, 'error' => 'Invalid security token. Please refresh and try again.'], 403);
            }

            // Convention: We now expect `amount_cents` as a direct integer from the frontend.
            $amountCents = (int)($_POST['amount_cents'] ?? 0);
            if ($amountCents <= 0) wallet_json(['success' => false, 'error' => 'Amount must be > 0'], 400);
            if ($amountCents > Wallet::TOPUP_MAX_AMOUNT) wallet_json(['success' => false, 'error' => 'Exceeds max $1,000.00'], 400);

            $resp = stripe_request('POST', '/v1/payment_intents', [
                'amount'   => $amountCents,
                'currency' => defined('STRIPE_CURRENCY') ? STRIPE_CURRENCY : 'usd',
                'automatic_payment_methods[enabled]' => 'true',
                'metadata[user_id]' => (string)$userId,
                'metadata[purpose]' => 'wallet_topup',
            ]);

            if (!$resp['ok']) wallet_json(['success' => false, 'error' => $resp['error']], 500);

            $pi = $resp['data'];
            $session->set('stripe_topup_amount_cents', $amountCents);
            $session->set('stripe_pi', (string)($pi['id'] ?? ''));
            $session->set('stripe_expires', time() + 900);

            wallet_json([
                'success'        => true,
                'client_secret'  => $pi['client_secret'] ?? null,
                'payment_intent' => $pi['id'] ?? null,
                'publishableKey' => defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : '',
            ]);
        }

        case 'stripe-finalize': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') wallet_json(['success' => false, 'error' => 'POST required'], 405);

            $piId          = trim((string)($_POST['payment_intent'] ?? ''));
            $pendingCents  = (int)$session->get('stripe_topup_amount_cents', 0);
            $pendingPi     = (string)$session->get('stripe_pi', '');
            $expires       = (int)$session->get('stripe_expires', 0);

            if ($piId === '' || $pendingPi === '' || $piId !== $pendingPi) wallet_json(['success' => false, 'error' => 'Invalid payment intent'], 400);
            if ($pendingCents <= 0 || time() > $expires) wallet_json(['success' => false, 'error' => 'Session expired'], 400);

            // Verify CSRF again for finalization
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$session->validateCSRFToken($csrfToken)) {
                wallet_json(['success' => false, 'error' => 'Security token mismatch.'], 403);
            }

            $resp = stripe_request('GET', '/v1/payment_intents/' . rawurlencode($piId));
            if (!$resp['ok']) wallet_json(['success' => false, 'error' => $resp['error']], 500);

            $pi = $resp['data'];
            if (($pi['status'] ?? '') !== 'succeeded') wallet_json(['success' => false, 'error' => 'Payment not completed'], 400);
            if ((int)($pi['amount_received'] ?? 0) !== $pendingCents) wallet_json(['success' => false, 'error' => 'Amount mismatch'], 400);
            if ((int)($pi['metadata']['user_id'] ?? 0) !== $userId) wallet_json(['success' => false, 'error' => 'User mismatch'], 400);

            if ($wallet->hasExternalRef($userId, $piId)) {
                wallet_json(['success' => true, 'message' => 'Already credited', 'balance' => $wallet->getBalance($userId)], 200);
            }

            $result = $wallet->topUp($userId, $pendingCents, 'STRIPE', $piId);

            $session->remove('stripe_topup_amount_cents');
            $session->remove('stripe_pi');
            $session->remove('stripe_expires');

            wallet_json($result, $result['success'] ? 200 : 400);
        }

        /* ── Existing: admin refund ──────────────────────────────────────── */
        case 'adminRefund': {
            if (!$session->isAdmin()) wallet_json(['success' => false, 'error' => 'Forbidden'], 403);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') wallet_json(['success' => false, 'error' => 'POST required'], 405);

            $targetUserId = (int)($_POST['user_id'] ?? 0);
            $amount       = (int)($_POST['amount_cents']  ?? 0);
            $csrfToken    = $_POST['csrf_token'] ?? '';
            
            if (!$session->validateCSRFToken($csrfToken)) {
                wallet_json(['success' => false, 'error' => 'CSRF failure'], 403);
            }

            if ($targetUserId <= 0) wallet_json(['success' => false, 'error' => 'user_id required'], 400);

            $result = $wallet->refund($targetUserId, $amount);
            wallet_json($result, $result['success'] ? 200 : 400);
        }

        /* ── Existing: admin all transactions ────────────────────────────── */
        case 'adminAllTransactions': {
            if (!$session->isAdmin()) wallet_json(['success' => false, 'error' => 'Forbidden'], 403);

            $limit        = (int)($_GET['limit']   ?? 50);
            $offset       = (int)($_GET['offset']  ?? 0);
            $filterUserId = $_GET['user_id']        ?? null;
            $rows         = $wallet->getAllTransactions($limit, $offset, $filterUserId);
            wallet_json(['success' => true, 'transactions' => $rows]);
        }

        /* ── Existing: CSV download ──────────────────────────────────────── */
        case 'downloadStatement': {
            if ($session->isAdmin() || $session->isLibrarian()) {
                wallet_json(['success' => false, 'error' => 'Forbidden'], 403);
            }
            $limit = max(1, min(Wallet::TX_MAX_LIMIT, (int)($_GET['limit'] ?? 500)));
            $tx    = $wallet->getTransactions($userId, $limit, 0);

            $filename = 'wallet_statement_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store');
            ob_clean();

            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Reason', 'Amount (USD)']);
            foreach ($tx as $row) {
                $formattedAmount = number_format($row['amount'] / 100, 2);
                fputcsv($out, [$row['created_at'], $row['type'], $row['reason'], $formattedAmount]);
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