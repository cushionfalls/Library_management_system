<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';

$session = new Session();

// Default entry: Sign In for guests, home for logged-in users (uses /library_management_system/ path via APP_URL).
if (isset($_GET['page']) && $_GET['page'] !== '') {
    $current_page = $_GET['page'];
} else {
    $current_page = $session->isLoggedIn() ? 'home' : 'login';
}

// Check authentication for protected routes
$protected_pages = ['dashboard', 'books', 'wallet', 'membership', 'profile', 'my-books', 'book-detail', 'admin'];

if (in_array($current_page, $protected_pages)) {
    if (!$session->isLoggedIn()) {
        header('Location: ' . APP_ROUTE . '?page=login');
        exit;
    }
}

// Check admin routes
$admin_pages = ['admin', 'manage-books', 'manage-authors', 'manage-users', 'transactions', 'overdue-books'];
if (in_array($current_page, $admin_pages)) {
    if (!$session->isAdmin() && !$session->isLibrarian()) {
        header('Location: ' . APP_ROUTE . '?page=home');
        exit;
    }
}

// Check session timeout
if (!$session->checkTimeout() && in_array($current_page, $protected_pages)) {
    header('Location: ' . APP_ROUTE . '?page=login');
    exit;
}

$navUser = ($session->isLoggedIn()) ? $session->getUserData() : null;

if (!function_exists('nav_profile_image_url')) {
    function nav_profile_image_url($img) {
        if (empty($img)) {
            return null;
        }
        if (preg_match('#^https?://#i', $img)) {
            return $img;
        }
        return APP_URL . '/public/uploads/profiles/' . basename($img);
    }
}

if (!function_exists('nav_user_initials_svg')) {
    function nav_user_initials_svg($navUser, $sessionName) {
        $fill = '#4F1BF1';
        return '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" class="rounded-full shrink-0 shadow-sm bg-[#ece5fa] text-[#4f1bf1]" role="img" aria-label="Profile"><path fill="currentColor" d="M18 19.5c3.04 0 5.5-2.46 5.5-5.5s-2.46-5.5-5.5-5.5-5.5 2.46-5.5 5.5 2.46 5.5 5.5 5.5zM18 22c-3.67 0-11 1.84-11 5.5V29h22v-1.5c0-3.66-7.33-5.5-11-5.5z"/></svg>';
    }
}

$navActive = [
    'dashboard' => $current_page === 'dashboard',
    'books' => $current_page === 'books',
    'my-books' => $current_page === 'my-books',
    'wallet' => $current_page === 'wallet',
    'membership' => $current_page === 'membership',
    'admin' => in_array($current_page, $admin_pages, true),
];

// Hide header/footer on guest-facing auth/landing pages
$guestAuthPages = ['login', 'register', 'forgot_password', 'home'];
$isGuestAuthPage = !$session->isLoggedIn() && in_array($current_page, $guestAuthPages, true);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&amp;display=swap" rel="stylesheet"/>
    <!-- DaisyUI CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.20/dist/full.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- LibBot Chatbot Styles -->
    <?php if ($session->isLoggedIn()): ?>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/chatbot.css">
    <?php endif; ?>
    <style>
        :root {
            --color-primary: 59, 130, 246;
            --color-primary-rgb: 59 130 246;
        }

        body {
            @apply bg-base-100 text-base-content;
            font-feature-settings: "kern";
        }

        .prose-headings-bold h1, .prose-headings-bold h2, .prose-headings-bold h3, .prose-headings-bold h4, .prose-headings-bold h5, .prose-headings-bold h6 {
            @apply font-semibold;
        }

        /* Smooth transitions */
        * {
            @apply transition-colors duration-200;
        }

        /* Button refinements */
        .btn {
            @apply font-medium;
        }

        .btn-primary {
            @apply btn-blue;
        }

        /* Card refinements */
        .card {
            @apply border border-base-200;
        }

        /* Typography */
        h1 { @apply text-3xl font-bold; }
        h2 { @apply text-2xl font-bold; }
        h3 { @apply text-xl font-semibold; }
        .nav-lumina-link { font-family: 'Manrope', system-ui, sans-serif; }
    </style>
</head>
<body class="<?php echo ($current_page === 'home' || $current_page === 'books' || $current_page === 'dashboard' || $current_page === 'my-books' || $current_page === 'admin' || $current_page === 'wallet' || $current_page === 'membership' || $current_page === 'profile') ? 'lumina-surface bg-[#fdf8ff] text-[#1c1a25]' : ''; ?>">
    <!-- Navigation: hidden on guest auth/landing pages -->
    <?php if (!$isGuestAuthPage): ?>
    <header class="nav-lumina sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-[#c9c4da]/30">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-8 py-3.5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:gap-8">
            <div class="flex items-center gap-4 lg:gap-10 flex-1 min-w-0">
                <a href="<?php echo APP_ROUTE; ?>?page=dashboard" class="flex items-center gap-2.5 shrink-0 text-[#1c1a25] hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[#4F1BF1] text-2xl" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                    <span class="text-lg font-bold tracking-tight font-['Manrope'] hidden sm:inline"><?php echo htmlspecialchars(APP_NAME); ?></span>
                </a>
            </div>

            <?php if ($session->isLoggedIn()): ?>
                <nav class="hidden lg:flex items-center gap-7 shrink-0" aria-label="Main">
                    <?php
                    $lum = function ($href, $label, $active) {
                        $cls = 'nav-lumina-link uppercase text-[13px] tracking-wide relative pb-1 ';
                        $cls .= $active
                            ? 'text-[#4F1BF1] font-bold after:absolute after:bottom-[-10px] after:left-0 after:w-full after:h-0.5 after:bg-[#4F1BF1]'
                            : 'text-[#474557] font-semibold hover:text-[#4F1BF1] transition-colors';
                        return '<a class="' . $cls . '" href="' . htmlspecialchars($href) . '">' . htmlspecialchars($label) . '</a>';
                    };
                    echo $lum(APP_ROUTE . '?page=dashboard', 'Dashboard', $navActive['dashboard']);
                    echo $lum(APP_ROUTE . '?page=books', 'Browse', $navActive['books']);
                    echo $lum(APP_ROUTE . '?page=my-books', 'My Books', $navActive['my-books']);
                    echo $lum(APP_ROUTE . '?page=wallet', 'Wallet', $navActive['wallet']);
                    echo $lum(APP_ROUTE . '?page=membership', 'Membership', $navActive['membership']);
                    if ($session->isAdmin() || $session->isLibrarian()) {
                        echo $lum(APP_ROUTE . '?page=admin', 'Admin', $navActive['admin']);
                    }
                    ?>
                </nav>

                <div class="flex items-center justify-between lg:justify-end gap-3 lg:pl-4 lg:border-l lg:border-[#c9c4da]/30">
                    <div class="dropdown lg:hidden">
                        <label tabindex="0" class="btn btn-ghost btn-sm font-['Manrope'] font-semibold text-[#474557] border border-[#c9c4da]/40">Menu</label>
                        <ul tabindex="0" class="dropdown-content z-[60] menu p-2 shadow-lg bg-white/95 backdrop-blur-md border border-[#c9c4da]/20 rounded-xl w-52">
                            <li><a class="font-['Manrope']" href="<?php echo APP_ROUTE; ?>?page=dashboard">Dashboard</a></li>
                            <li><a class="font-['Manrope']" href="<?php echo APP_ROUTE; ?>?page=books">Browse</a></li>
                            <li><a class="font-['Manrope']" href="<?php echo APP_ROUTE; ?>?page=my-books">My Books</a></li>
                            <li><a class="font-['Manrope']" href="<?php echo APP_ROUTE; ?>?page=wallet">Wallet</a></li>
                            <li><a class="font-['Manrope']" href="<?php echo APP_ROUTE; ?>?page=membership">Membership</a></li>
                            <?php if ($session->isAdmin() || $session->isLibrarian()): ?>
                                <li><a class="font-['Manrope']" href="<?php echo APP_ROUTE; ?>?page=admin">Admin</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="cursor-pointer p-1.5 hover:bg-[#e5e0f0] rounded-full transition-colors text-[#474557] hover:text-[#4F1BF1] flex items-center justify-center" title="Account">
                            <?php
                            $profileImg = nav_profile_image_url(($navUser && !empty($navUser['profile_image'])) ? $navUser['profile_image'] : '');
                            if ($profileImg):
                            ?>
                                <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="w-9 h-9 rounded-full object-cover ring-2 ring-[#4F1BF1]/20" width="36" height="36" />
                            <?php else: ?>
                                <?php echo nav_user_initials_svg($navUser, $_SESSION['user_name'] ?? ''); ?>
                            <?php endif; ?>
                        </label>
                        <ul tabindex="0" class="dropdown-content z-[60] menu p-2 shadow-lg bg-white/95 backdrop-blur-md border border-[#c9c4da]/20 rounded-xl w-56">
                            <li class="menu-title text-xs opacity-75"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></li>
                            <li class="lg:hidden"><a href="<?php echo APP_ROUTE; ?>?page=dashboard">Dashboard</a></li>
                            <li><a href="<?php echo APP_ROUTE; ?>?page=profile">Profile</a></li>
                            <li><a href="#" onclick="openLogoutModal(); return false;">Logout</a></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="<?php echo $isGuestAuthPage ? '' : 'min-h-screen'; ?>">
        <?php if ($isGuestAuthPage): ?>
            <?php
            $page = $_GET['page'] ?? 'home';
            $view_file = __DIR__ . '/../views/' . str_replace(['../', '..\\'], '', $page) . '.php';
            if (file_exists($view_file)) {
                include $view_file;
            } else {
                include __DIR__ . '/../views/home.php';
            }
            ?>
        <?php else: ?>
        <div class="<?php
            if ($current_page === 'books' || $current_page === 'dashboard' || $current_page === 'my-books' || $current_page === 'admin' || $current_page === 'wallet' || $current_page === 'membership' || $current_page === 'fines' || $current_page === 'profile') {
                echo 'w-full max-w-screen-2xl mx-auto px-4 sm:px-8 py-10';
            } else {
                echo 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8';
            }
        ?>">
            <?php
            $page = $_GET['page'] ?? 'home';
            $view_file = __DIR__ . '/../views/' . str_replace(['../', '..\\'], '', $page) . '.php';

            if (file_exists($view_file)) {
                include $view_file;
            } else {
                include __DIR__ . '/../views/home.php';
            }
            ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer: hidden on guest auth pages (landing page has its own footer) -->
    <?php if (!$isGuestAuthPage): ?>
    <footer class="bg-surface-container-low border-t border-outline-variant/30 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-bold text-lg mb-3 text-on-surface">About</h3>
                    <p class="text-sm text-on-surface-variant"><?php echo APP_NAME; ?> - Your digital library management solution.</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-3 text-on-surface">Quick Links</h3>
                    <ul class="text-sm space-y-2 text-on-surface-variant">
                        <li><a href="<?php echo APP_ROUTE; ?>?page=books" class="hover:text-primary transition-colors">Browse Books</a></li>
                        <li><a href="<?php echo APP_ROUTE; ?>?page=home" class="hover:text-primary transition-colors">Home</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-3 text-on-surface">Legal</h3>
                    <p class="text-sm text-on-surface-variant">&copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <?php if ($session->isLoggedIn()): ?>
    <!-- LibBot Chatbot Component -->
    <?php include __DIR__ . '/../views/components/chatbot.php'; ?>
    <?php endif; ?>

    <dialog id="logoutConfirmModal" class="modal">
        <div class="modal-box max-w-md">
            <h3 class="font-bold text-lg mb-3">Logout</h3>
            <p class="opacity-75 mb-5">Are you sure you want to logout?</p>
            <div class="flex justify-end gap-2">
                <button class="btn btn-ghost" onclick="document.getElementById('logoutConfirmModal').close()">Cancel</button>
                <button class="btn btn-primary" onclick="confirmLogout()">Yes, Logout</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <script src="<?php echo APP_URL; ?>/public/js/main.js"></script>
    <?php if ($session->isLoggedIn()): ?>
    <script src="<?php echo APP_URL; ?>/public/js/chatbot.js"></script>
    <?php endif; ?>
</body>
</html>
