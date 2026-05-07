<?php
require_once __DIR__ . '/../classes/Session.php';
$session = new Session();
if ($session->isLoggedIn()) {
    header('Location: ' . APP_ROUTE . '?page=dashboard');
    exit;
}
?>
<style>
    :root {
        --primary: #4c1d95;
        --primary-light: #6d28d9;
        --accent: #7c3aed;
        --surface: #faf5ff;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --border: #e5e7eb;
    }

    .auth-container {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        min-height: 100vh;
        background: white;
    }

    .auth-form-side {
        padding: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        max-width: 560px;
        margin: 0 auto;
        width: 100%;
    }

    .auth-visual-side {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        color: white;
        padding: 40px;
    }

    .auth-visual-side::before {
        content: '';
        position: absolute;
        width: 150%;
        height: 150%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    .visual-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .floating-icon {
        font-size: 5rem;
        margin-bottom: 24px;
        animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }

    .auth-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        font-size: 1.5rem;
        color: var(--primary);
        text-decoration: none;
        margin-bottom: 48px;
    }

    .input-lms {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface);
        transition: 0.3s;
        outline: none;
    }

    .input-lms:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(76, 29, 149, 0.1);
    }

    .btn-auth {
        background: var(--primary);
        color: white;
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        width: 100%;
        transition: 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-auth:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -10px rgba(76, 29, 149, 0.5);
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 24px;
        transition: 0.3s;
    }
    .back-btn:hover {
        color: var(--primary);
        transform: translateX(-4px);
    }

    /* Grid Background */
    .grid-bg {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: 
            linear-gradient(rgba(79, 27, 241, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(79, 27, 241, 0.03) 1px, transparent 1px);
        background-size: 40px 40px;
        z-index: -1;
    }

    @media (max-width: 968px) {
        .auth-container { grid-template-columns: 1fr; background: var(--surface); }
        .auth-visual-side { display: none; }
    }
</style>

<div class="grid-bg"></div>

<div class="auth-container">
    <div class="auth-form-side">
        <a href="<?php echo APP_ROUTE; ?>?page=home" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
        <a href="<?php echo APP_ROUTE; ?>?page=home" class="auth-logo">
            <i class="fas fa-book-open"></i>
            <?php echo APP_NAME; ?>
        </a>

        <div class="mb-10">
            <h1 class="text-4xl font-black text-gray-900 mb-3">Welcome Back</h1>
            <p class="text-gray-500">Please enter your details to sign in to your account.</p>
        </div>

        <div id="loginMessage" class="mb-6">
            <?php if (!empty($_GET['reset']) && (string) $_GET['reset'] === '1'): ?>
            <div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 flex items-center gap-3">
                <i class="fas fa-check-circle"></i> Password updated. You can sign in now.
            </div>
            <?php endif; ?>
        </div>

        <form id="loginForm" class="space-y-6" autocomplete="on">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                <input type="email" placeholder="you@example.com" class="input-lms" id="loginEmail" name="email" required>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-gray-700">Password</label>
                    <a href="<?php echo APP_ROUTE; ?>?page=forgot_password" class="text-sm font-bold text-primary hover:underline">Forgot password?</a>
                </div>
                <input type="password" placeholder="••••••••" class="input-lms" id="loginPassword" name="password" required autocomplete="current-password">
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="loginRemember" name="remember_me" value="1" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary">
                <label for="loginRemember" class="text-sm text-gray-600 cursor-pointer font-medium">Remember me on this device</label>
            </div>

            <button type="submit" class="btn-auth">
                Sign In
            </button>
        </form>

        <div class="mt-10 pt-10 border-t border-gray-100 text-center">
            <p class="text-gray-600">Don't have an account? <a href="<?php echo APP_ROUTE; ?>?page=register" class="font-bold text-primary hover:underline">Create one for free</a></p>
        </div>
    </div>

    <div class="auth-visual-side">
        <div class="visual-content">
            <div class="floating-icon"><i class="fas fa-book-reader"></i></div>
            <h2 class="text-4xl font-black mb-4">Discover Your Next Adventure</h2>
            <p class="text-lg opacity-80 max-w-sm mx-auto">Access thousands of books, track your reading progress, and join a community of book lovers.</p>
        </div>
    </div>
</div>

<script>
async function resendOTPFromLogin(email) {
    if (!email) return;
    try {
        const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=resend-otp', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'email=' + encodeURIComponent(email)
        });
        const result = await response.json();
        const messageDiv = document.getElementById('loginMessage');
        const verifyLink = '<?php echo APP_ROUTE; ?>?page=register&verify_email=' + encodeURIComponent(email);
        if (result.success) {
            messageDiv.innerHTML = '<div class="p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 flex items-center gap-3"><i class="fas fa-info-circle"></i>' + result.message + ' <a href="' + verifyLink + '" class="font-bold underline">Enter OTP now</a></div>';
        } else {
            messageDiv.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>' + (result.error || 'Failed to resend OTP') + '</div>';
        }
    } catch (error) {
        document.getElementById('loginMessage').innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
    }
}

const LMS_REMEMBER_KEY = 'lms_remember_email';
const LMS_REMEMBER_FLAG = 'lms_remember_me';

(function initLoginRemember() {
    const emailIn = document.getElementById('loginEmail');
    const remember = document.getElementById('loginRemember');
    if (!emailIn || !remember) return;
    try {
        if (localStorage.getItem(LMS_REMEMBER_FLAG) === '1') {
            const saved = localStorage.getItem(LMS_REMEMBER_KEY);
            if (saved) {
                emailIn.value = saved;
                remember.checked = true;
            }
        }
    } catch (e) {}
})();

document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const remember = document.getElementById('loginRemember');
    const emailVal = document.getElementById('loginEmail').value || '';

    try {
        if (remember && remember.checked) {
            localStorage.setItem(LMS_REMEMBER_FLAG, '1');
            localStorage.setItem(LMS_REMEMBER_KEY, emailVal.trim());
        } else {
            localStorage.removeItem(LMS_REMEMBER_FLAG);
            localStorage.removeItem(LMS_REMEMBER_KEY);
        }

        const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=login', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        const messageDiv = document.getElementById('loginMessage');

        if (result.success) {
            window.location.href = '<?php echo APP_ROUTE; ?>?page=dashboard';
        } else {
            if (result.unverified_email && result.email) {
                messageDiv.innerHTML = `
                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-xl border border-yellow-100">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${result.error || 'Email not verified.'}
                        <a href="#" class="font-bold underline ml-2" onclick="resendOTPFromLogin('${result.email.replace(/'/g, "\\'")}'); return false;">Resend OTP?</a>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>' + (result.error || 'Login failed') + '</div>';
            }
        }
    } catch (error) {
        document.getElementById('loginMessage').innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
    }
});
</script>
