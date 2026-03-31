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

                <div id="loginMessage" class="mb-4"></div>

                <form id="loginForm" class="space-y-4">
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
                        <input type="password" placeholder="••••••••" class="input input-bordered focus:input-primary" id="loginPassword" name="password" required>
                    </div>

                    <div class="text-right">
                        <a href="<?php echo APP_URL; ?>/public/index.php?page=forgot-password" class="link link-primary text-sm font-semibold">
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
                    <a href="<?php echo APP_URL; ?>/public/index.php?page=register" class="link link-primary font-semibold">Create one</a>
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
        const verifyLink = '<?php echo APP_URL; ?>/public/index.php?page=register&verify_email=' + encodeURIComponent(email);

        if (result.success) {
            messageDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>' + result.message + ' <a href="' + verifyLink + '" class="link link-primary font-semibold">Enter OTP now</a></div>';
        } else {
            messageDiv.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (result.error || 'Failed to resend OTP') + '</div>';
        }
    } catch (error) {
        document.getElementById('loginMessage').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>An error occurred. Please try again.</div>';
    }
}

document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    try {
        const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=login', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        const messageDiv = document.getElementById('loginMessage');

        if (result.success) {
            messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>' + result.message + '</div>';
            setTimeout(() => {
                window.location.href = '<?php echo APP_URL; ?>/public/index.php?page=dashboard';
            }, 1500);
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

