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
        background: linear-gradient(135deg, #4c1d95, #7c3aed);
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
        animation: rotate 25s linear infinite;
    }

    @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    .floating-icon {
        font-size: 5rem;
        margin-bottom: 24px;
        animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(-5deg); }
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

    @media (max-width: 968px) {
        .auth-container { grid-template-columns: 1fr; }
        .auth-visual-side { display: none; }
    }
</style>

<?php
$selfUrl = APP_ROUTE . '?page=forgot_password';
$loginUrl = APP_ROUTE . '?page=login&reset=1';
$forgotJsPath = __DIR__ . '/../public/js/forgot_password.js';
$forgotJsVersion = file_exists($forgotJsPath) ? (string) @filemtime($forgotJsPath) : '1';
?>

<div id="forgotPasswordRoot" class="auth-container" data-app-url="<?php echo htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8'); ?>">
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
            <h1 class="text-3xl font-black text-gray-900 mb-3">Reset Password</h1>
            <p class="text-gray-500">We'll help you get back into your account.</p>
        </div>

        <input type="hidden" id="forgotAfterResetUrl" value="<?php echo htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" />

        <!-- Step 1: Email -->
        <div id="forgotStepEmail">
            <div id="forgotMsgEmail" class="mb-6"></div>
            <form id="forgotFormEmail" class="space-y-6" method="post" autocomplete="on">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="forgotEmail" name="email" required placeholder="you@example.com" class="input-lms" autocomplete="email" />
                </div>
                <button type="submit" class="btn-auth">
                    Send Verification Code
                </button>
            </form>
        </div>

        <!-- Step 2: OTP -->
        <div id="forgotStepOtp" class="hidden">
            <div id="forgotMsgOtp" class="mb-6"></div>
            <form id="forgotFormOtp" class="space-y-6" method="post" autocomplete="off">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Verification Code</label>
                    <input type="text" id="forgotOtpInput" name="otp" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="w-full text-center text-4xl font-black tracking-[1rem] p-4 bg-gray-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-primary" placeholder="000000" required />
                    <input type="hidden" name="email" id="forgotOtpEmailHidden" value="" />
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <button type="button" id="forgotBackToEmail" class="text-gray-400 hover:text-primary">Change Email</button>
                    <button type="button" id="forgotResendOtp" class="text-primary hover:underline">Resend Code</button>
                </div>
                <button type="submit" class="btn-auth">
                    Verify Code
                </button>
            </form>
        </div>

        <!-- Step 3: New Password -->
        <div id="forgotStepPassword" class="hidden">
            <div id="forgotMsgPassword" class="mb-6"></div>
            <form id="forgotFormPassword" class="space-y-6" method="post" autocomplete="off">
                <input type="hidden" name="email" id="forgotPasswordEmailHidden" value="" />
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                    <input type="password" id="forgotNewPassword" name="new_password" minlength="6" class="input-lms" placeholder="••••••••" required />
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="forgotConfirmPassword" name="confirm_password" minlength="6" class="input-lms" placeholder="••••••••" required />
                </div>
                <button type="submit" class="btn-auth">
                    Update Password
                </button>
            </form>
        </div>

        <div class="mt-10 pt-10 border-t border-gray-100 text-center">
            <a href="<?php echo APP_ROUTE; ?>?page=login" class="font-bold text-primary hover:underline">Back to Sign In</a>
        </div>
    </div>

    <div class="auth-visual-side">
        <div class="visual-content text-center">
            <div class="floating-icon"><i class="fas fa-key"></i></div>
            <h2 class="text-4xl font-black mb-4">Secure Your Account</h2>
            <p class="text-lg opacity-80 max-w-sm mx-auto">Don't worry, it happens. Just follow the steps to reset your password and regain access to your library.</p>
        </div>
    </div>
</div>

<script>
window.FORGOT_APP_URL = <?php echo json_encode(rtrim(APP_URL, '/'), JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="<?php echo htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8'); ?>/public/js/forgot_password.js?v=<?php echo htmlspecialchars($forgotJsVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>
