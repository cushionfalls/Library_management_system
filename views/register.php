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
        position: relative;
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
        margin-bottom: 32px;
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

    @media (max-width: 968px) {
        .auth-container { grid-template-columns: 1fr; }
        .auth-visual-side { display: none; }
    }

    /* Modal styling to match Lumina theme */
    .modal-box {
        background: white;
        border-radius: 24px;
        padding: 40px;
        border: 1px solid var(--border);
    }

    .auth-grid {
        position: absolute; inset: 0;
        background-image: linear-gradient(rgba(139, 92, 246, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(139, 92, 246, 0.03) 1px, transparent 1px);
        background-size: 60px 60px;
        z-index: 0;
        pointer-events: none;
    }
</style>

<div class="auth-container">
    <div class="auth-grid"></div>
    <div class="auth-form-side">
        <a href="<?php echo APP_ROUTE; ?>?page=home" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
        <a href="<?php echo APP_ROUTE; ?>?page=home" class="auth-logo">
            <i class="fas fa-book-open"></i>
            <?php echo APP_NAME; ?>
        </a>

        <div class="mb-8">
            <h1 class="text-4xl font-black text-gray-900 mb-3">Create Account</h1>
            <p class="text-gray-500">Join our community and start your reading journey today.</p>
        </div>

        <div id="registrationMessage" class="mb-6"></div>

        <form id="registerForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">First Name</label>
                    <input type="text" placeholder="John" class="input-lms" id="firstName" name="first_name" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Last Name</label>
                    <input type="text" placeholder="Doe" class="input-lms" id="lastName" name="last_name" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                <input type="email" placeholder="john@example.com" class="input-lms" id="email" name="email" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                <input type="password" placeholder="••••••••" class="input-lms" id="password" name="password" required>
                <p class="text-[10px] text-gray-400 mt-1">At least 6 characters</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                <input type="password" placeholder="••••••••" class="input-lms" id="confirmPassword" name="confirm_password" required>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-auth">
                    Create Account
                </button>
            </div>
        </form>

        <div class="mt-8 pt-8 border-t border-gray-100 text-center">
            <p class="text-gray-600">Already have an account? <a href="<?php echo APP_ROUTE; ?>?page=login" class="font-bold text-primary hover:underline">Sign in</a></p>
        </div>
    </div>

    <div class="auth-visual-side">
        <div class="visual-content">
            <div class="floating-icon"><i class="fas fa-user-plus"></i></div>
            <h2 class="text-4xl font-black mb-4">Start Your Journey</h2>
            <p class="text-lg opacity-80 max-w-sm mx-auto">Create an account to access our digital library, track your progress, and more.</p>
        </div>
    </div>
</div>

<!-- OTP Verification Modal -->
<dialog id="otpModal" class="modal">
    <div class="modal-box">
        <h3 class="text-2xl font-black text-gray-900 mb-4">Verify Email</h3>
        
        <div id="otpMessage" class="mb-6"></div>

        <form id="otpForm" class="space-y-6">
            <p class="text-sm text-gray-500">An OTP code has been sent to your email. Please enter it below to verify your account.</p>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2 text-center">OTP Code</label>
                <input type="text" placeholder="000000" class="input-lms text-center text-3xl tracking-[1rem] font-black" id="otp" name="otp" maxlength="6" required>
            </div>

            <input type="hidden" id="otpEmail" name="email">

            <button type="submit" class="btn-auth">
                Verify OTP
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Didn't receive the code? 
                <a href="#" onclick="resendOTP(); return false;" class="font-bold text-primary hover:underline">Resend OTP</a>
            </p>
        </div>
        
        <div class="modal-action justify-center mt-6">
            <button type="button" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors" onclick="document.getElementById('otpModal').close()">Cancel</button>
        </div>
    </div>
</dialog>

<script>
async function resendOTPForEmail(email, messageTargetId = 'registrationMessage') {
    if (!email) return;

    try {
        const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=resend-otp', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'email=' + encodeURIComponent(email)
        });
        const result = await response.json();
        const messageDiv = document.getElementById(messageTargetId);

        if (result.success) {
            messageDiv.innerHTML = '<div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 flex items-center gap-3"><i class="fas fa-check-circle"></i>' + result.message + '</div>';
            document.getElementById('otpEmail').value = email;
            document.getElementById('otpModal').showModal();
        } else {
            messageDiv.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>' + (result.error || 'Failed to resend OTP') + '</div>';
        }
    } catch (error) {
        document.getElementById(messageTargetId).innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
    }
}

document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const messageDiv = document.getElementById('registrationMessage');
    const otpModal = document.getElementById('otpModal');
    const otpMessage = document.getElementById('otpMessage');
    const otpEmail = (formData.get('email') || '').toString().trim();
    const submitBtn = this.querySelector('button[type="submit"]');

    if (submitBtn) submitBtn.disabled = true;
    document.getElementById('otpEmail').value = otpEmail;
    
    // Show loading in OTP modal
    otpMessage.innerHTML = '<div class="p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 flex items-center gap-3"><i class="fas fa-spinner fa-spin"></i>Sending OTP to <strong>' + otpEmail + '</strong>...</div>';
    otpModal.showModal();

    try {
        const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=register', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('otpEmail').value = result.email;
            messageDiv.innerHTML = '<div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 flex items-center gap-3"><i class="fas fa-check-circle"></i>' + result.message + '</div>';
            otpMessage.innerHTML = '<div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 flex items-center gap-3"><i class="fas fa-check-circle"></i>' + result.message + '</div>';
            const otpInput = document.getElementById('otp');
            if (otpInput) otpInput.focus();
        } else {
            otpModal.close();
            if (result.unverified_email && result.email) {
                messageDiv.innerHTML = `
                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-xl border border-yellow-100">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${result.error || 'Email already registered but not verified.'}
                        <a href="#" class="font-bold underline ml-2" onclick="resendOTPForEmail('${result.email.replace(/'/g, "\\'")}'); return false;">Resend OTP?</a>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>' + (result.error || 'Registration failed') + '</div>';
            }
        }
    } catch (error) {
        otpModal.close();
        document.getElementById('registrationMessage').innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
    } finally {
        if (submitBtn) submitBtn.disabled = false;
    }
});

document.getElementById('otpForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
        const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=verify-otp', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        const messageDiv = document.getElementById('otpMessage');

        if (result.success) {
            messageDiv.innerHTML = '<div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-100 flex items-center gap-3"><i class="fas fa-check-circle"></i>' + result.message + '</div>';
            setTimeout(() => {
                window.location.href = '<?php echo APP_ROUTE; ?>?page=login';
            }, 2000);
        } else {
            messageDiv.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>' + (result.error || 'Verification failed') + '</div>';
            submitBtn.disabled = false;
        }
    } catch (error) {
        document.getElementById('otpMessage').innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i>An error occurred.</div>';
        submitBtn.disabled = false;
    }
});

function resendOTP() {
    const email = document.getElementById('otpEmail').value;
    if (!email) return;
    resendOTPForEmail(email, 'otpMessage');
}

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const verifyEmail = params.get('verify_email');
    if (!verifyEmail) return;

    document.getElementById('otpEmail').value = verifyEmail;
    document.getElementById('registrationMessage').innerHTML = '<div class="p-4 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 flex items-center gap-3"><i class="fas fa-info-circle"></i>Enter the OTP sent to <strong>' + verifyEmail + '</strong>.</div>';

    document.getElementById('otpModal').showModal();
});
</script>

