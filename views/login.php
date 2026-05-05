<head>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DaisyUI CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.20/dist/full.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="w-full max-w-md">
        <div class="card bg-base-100 shadow-lg border border-base-200">
            <div class="card-body">
                <h1 class="card-title text-3xl mb-6 text-center">Sign In</h1>

                <div id="loginMessage" class="mb-4">
                    <?php if (!empty($_GET['reset']) && (string) $_GET['reset'] === '1'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>Password updated. You can sign in with your new password.
                    </div>
                    <?php endif; ?>
                </div>

                <form id="loginForm" class="space-y-4" autocomplete="on">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email Address</span>
                        </label>
                        <input type="email" placeholder="you@example.com" class="input input-bordered focus:input-primary" id="loginEmail" name="email" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Password</span>
                        </label>
                        <input type="password" placeholder="••••••••" class="input input-bordered focus:input-primary" id="loginPassword" name="password" required autocomplete="current-password">
                    </div>

                    <div class="flex items-center gap-2.5 py-1">
                        <input type="checkbox" id="loginRemember" name="remember_me" value="1" class="checkbox checkbox-primary checkbox-sm shrink-0" />
                        <label for="loginRemember" class="text-sm cursor-pointer select-none">Remember me on this device</label>
                    </div>

                    <div class="text-right">
                        <a href="<?php echo APP_ROUTE; ?>?page=forgot_password" class="link link-primary text-sm font-semibold">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </form>

                <div class="divider">Or</div>

                <p class="text-center text-sm">
                    Don't have an account?
                    <a href="<?php echo APP_ROUTE; ?>?page=register" class="link link-primary font-semibold">Create one</a>
                </p>
            </div>
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
            messageDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>' + result.message + ' <a href="' + verifyLink + '" class="link link-primary font-semibold">Enter OTP now</a></div>';
        } else {
            messageDiv.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (result.error || 'Failed to resend OTP') + '</div>';
        }
    } catch (error) {
        document.getElementById('loginMessage').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>An error occurred. Please try again.</div>';
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
    } catch (e) { /* private mode */ }
})();

document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const remember = document.getElementById('loginRemember');
    const emailVal = (document.getElementById('loginEmail') && document.getElementById('loginEmail').value) || '';

    try {
        if (remember && remember.checked) {
            try {
                localStorage.setItem(LMS_REMEMBER_FLAG, '1');
                localStorage.setItem(LMS_REMEMBER_KEY, emailVal.trim());
            } catch (e) { /* ignore */ }
        } else {
            try {
                localStorage.removeItem(LMS_REMEMBER_FLAG);
                localStorage.removeItem(LMS_REMEMBER_KEY);
            } catch (e) { /* ignore */ }
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
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${result.error || 'Email already registered but not verified.'}
                        <a href="#" class="link link-primary font-semibold" onclick="resendOTPFromLogin('${result.email.replace(/'/g, "\\'")}'); return false;">Resend OTP?</a>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (result.error || 'Login failed') + '</div>';
            }
        }
    } catch (error) {
        document.getElementById('loginMessage').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>An error occurred. Please try again.</div>';
    }
});
</script>

