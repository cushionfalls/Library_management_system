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
                <h1 class="card-title text-3xl mb-6 text-center">Create Account</h1>

                <div id="registrationMessage" class="mb-4"></div>

                <form id="registerForm" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">First Name</span>
                        </label>
                        <input type="text" placeholder="John" class="input input-bordered focus:input-primary" id="firstName" name="first_name" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Last Name</span>
                        </label>
                        <input type="text" placeholder="Doe" class="input input-bordered focus:input-primary" id="lastName" name="last_name" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email Address</span>
                        </label>
                        <input type="email" placeholder="john@example.com" class="input input-bordered focus:input-primary" id="email" name="email" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Password</span>
                        </label>
                        <input type="password" placeholder="••••••••" class="input input-bordered focus:input-primary" id="password" name="password" required>
                        <label class="label">
                            <span class="label-text-alt text-xs">At least 6 characters</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Confirm Password</span>
                        </label>
                        <input type="password" placeholder="••••••••" class="input input-bordered focus:input-primary" id="confirmPassword" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-user-plus mr-2"></i> Create Account
                    </button>
                </form>

                <div class="divider">Or</div>

                <p class="text-center text-sm">
                    Already have an account?
                    <a href="<?php echo APP_ROUTE; ?>?page=login" class="link link-primary font-semibold">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- OTP Verification Modal -->
<dialog id="otpModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Verify Email Address</h3>

        <div id="otpMessage" class="mb-4"></div>

        <form id="otpForm" class="space-y-4">
            <p class="text-sm opacity-75">An OTP code has been sent to your email. Please enter it below:</p>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">OTP Code</span>
                </label>
                <input type="text" placeholder="000000" class="input input-bordered input-lg text-center tracking-widest text-2xl focus:input-primary" id="otp" name="otp" maxlength="6" required>
            </div>

            <input type="hidden" id="otpEmail" name="email">

            <button type="submit" class="btn btn-primary w-full">
                <i class="fas fa-check mr-2"></i> Verify OTP
            </button>
        </form>

        <div class="modal-action">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('otpModal').close()">Close</button>
        </div>

        <p class="text-center text-xs mt-4">
            Didn't receive the code?
            <a href="#" onclick="resendOTP()" class="link link-primary">Resend OTP</a>
        </p>
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
            messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>' + result.message + '</div>';
            document.getElementById('otpEmail').value = email;
            document.getElementById('otpModal').showModal();
        } else {
            messageDiv.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (result.error || 'Failed to resend OTP') + '</div>';
        }
    } catch (error) {
        document.getElementById(messageTargetId).innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>An error occurred. Please try again.</div>';
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
    this.style.display = 'none';
    otpMessage.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin mr-2"></i>Sending OTP to <strong>' + otpEmail + '</strong>...</div>';
    otpModal.showModal();

    try {
        const response = await fetch('<?php echo APP_URL; ?>/controllers/auth.php?action=register', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('otpEmail').value = result.email;
            messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>' + result.message + '</div>';
            otpMessage.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>' + result.message + '</div>';
            const otpInput = document.getElementById('otp');
            if (otpInput) otpInput.focus();
        } else {
            otpModal.close();
            this.style.display = '';
            if (result.unverified_email && result.email) {
                messageDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${result.error || 'Email already registered but not verified.'}
                        <a href="#" class="link link-primary font-semibold" onclick="resendOTPForEmail('${result.email.replace(/'/g, "\\'")}'); return false;">Resend OTP?</a>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (result.error || 'Registration failed') + '</div>';
            }
        }
    } catch (error) {
        otpModal.close();
        this.style.display = '';
        document.getElementById('registrationMessage').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>An error occurred. Please try again.</div>';
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
            messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>' + result.message + '</div>';
            setTimeout(() => {
                window.location.href = '<?php echo APP_ROUTE; ?>?page=login';
            }, 2000);
        } else {
            messageDiv.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (result.error || 'Verification failed') + '</div>';
            submitBtn.disabled = false;
        }
    } catch (error) {
        document.getElementById('otpMessage').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>An error occurred. Please try again.</div>';
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
    document.getElementById('registrationMessage').innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Enter the OTP sent to <strong>' + verifyEmail + '</strong>.</div>';

    document.getElementById('otpModal').showModal();
});
</script>
