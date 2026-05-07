<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Session.php';

$session = new Session();
if ($session->isLoggedIn()) {
    header('Location: ' . APP_ROUTE . '?page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        max-width: 640px;
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
            <h1 class="text-4xl font-black text-gray-900 mb-3">Create Account</h1>
            <p class="text-gray-500">Join our community and start your reading journey.</p>
        </div>

        <div id="registrationMessage" class="mb-6"></div>

        <form id="registerForm" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">First Name</label>
                    <input type="text" placeholder="First Name" class="input-lms" name="first_name" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Last Name</label>
                    <input type="text" placeholder="Last Name" class="input-lms" name="last_name" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                <input type="email" placeholder="you@example.com" class="input-lms" name="email" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <input type="password" placeholder="••••••••" class="input-lms" name="password" minlength="6" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" placeholder="••••••••" class="input-lms" name="confirm_password" required>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="terms" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary" required>
                <label for="terms" class="text-sm text-gray-600 cursor-pointer font-medium">
                    I agree to the <a href="#" class="text-primary font-bold hover:underline">Terms of Service</a>
                </label>
            </div>

            <button type="submit" class="btn-auth">
                Create Account
            </button>
        </form>

        <div class="mt-10 pt-10 border-t border-gray-100 text-center">
            <p class="text-gray-600">Already have an account? <a href="<?php echo APP_ROUTE; ?>?page=login" class="font-bold text-primary hover:underline">Sign In</a></p>
        </div>
    </div>

    <div class="auth-visual-side">
        <div class="visual-content">
            <div class="floating-icon"><i class="fas fa-user-plus"></i></div>
            <h2 class="text-4xl font-black mb-4">Start Your Journey</h2>
            <p class="text-lg opacity-80 max-w-sm mx-auto">Create an account to access our digital library, track your progress, and join a community of book lovers.</p>
        </div>
    </div>
</div>

    <!-- OTP Verification Modal -->
    <dialog id="otpModal" class="modal">
        <div class="modal-box">
            <h3 class="text-2xl font-black text-gray-900 mb-4 font-lumina text-center">Verify Email</h3>
            
            <div id="otpMessage" class="mb-6"></div>

            <form id="otpForm" class="space-y-6">
                <p class="text-sm text-gray-500 text-center">An OTP code has been sent to your email. Please enter it below to verify your account.</p>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2 text-center uppercase tracking-widest">OTP Code</label>
                    <input type="text" placeholder="0 0 0 0 0 0" class="input-lms text-center text-4xl tracking-[1rem] font-black" id="otp" name="otp" maxlength="6" required>
                </div>

                <input type="hidden" id="otpEmail" name="email">

                <button type="submit" class="btn-auth">
                    Verify OTP
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    Didn't receive the code? 
                    <a href="#" onclick="resendOTP()" class="text-primary font-bold hover:underline">Resend OTP</a>
                </p>
            </div>
            
            <div class="mt-6 text-center">
                <button type="button" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors" onclick="document.getElementById('otpModal').close()">Cancel</button>
            </div>
        </div>
    </dialog>

    <script>
    const registerForm = document.getElementById('registerForm');
    const otpModal = document.getElementById('otpModal');
    const otpForm = document.getElementById('otpForm');
    const registrationMessage = document.getElementById('registrationMessage');
    const otpMessage = document.getElementById('otpMessage');

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        registrationMessage.innerHTML = '<div class="p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 flex items-center gap-3"><i class="fas fa-spinner fa-spin"></i>Creating account...</div>';

        try {
            const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=register', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('otpEmail').value = formData.get('email');
                registrationMessage.innerHTML = '';
                otpModal.showModal();
            } else {
                registrationMessage.innerHTML = `<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>${result.error}</div>`;
            }
        } catch (error) {
            registrationMessage.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
        }
    });

    otpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(otpForm);
        otpMessage.innerHTML = '<div class="p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 flex items-center gap-3"><i class="fas fa-spinner fa-spin"></i>Verifying...</div>';

        try {
            const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=verify-otp', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                otpMessage.innerHTML = '<div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 flex items-center gap-3"><i class="fas fa-check-circle"></i>Verified! Redirecting...</div>';
                setTimeout(() => {
                    window.location.href = '<?php echo APP_ROUTE; ?>?page=login';
                }, 2000);
            } else {
                otpMessage.innerHTML = `<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>${result.error}</div>`;
            }
        } catch (error) {
            otpMessage.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
        }
    });

    async function resendOTP() {
        const email = document.getElementById('otpEmail').value;
        otpMessage.innerHTML = '<div class="p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 flex items-center gap-3"><i class="fas fa-spinner fa-spin"></i>Resending...</div>';

        try {
            const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=resend-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}`
            });

            const result = await response.json();

            if (result.success) {
                otpMessage.innerHTML = `<div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 flex items-center gap-3"><i class="fas fa-check-circle"></i>${result.message}</div>`;
            } else {
                otpMessage.innerHTML = `<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>${result.error}</div>`;
            }
        } catch (error) {
            otpMessage.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
        }
    }
    </script>
</body>
</html>
