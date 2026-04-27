<?php
$selfUrl = APP_ROUTE . '?page=forgot_password';
$loginUrl = APP_ROUTE . '?page=login&reset=1';
$forgotJsPath = __DIR__ . '/../public/js/forgot_password.js';
$forgotJsVersion = file_exists($forgotJsPath) ? (string) @filemtime($forgotJsPath) : '1';
?>
<div
    id="forgotPasswordRoot"
    class="flex items-center justify-center min-h-[80vh] px-4 py-10"
    data-app-url="<?php echo htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8'); ?>"
>
    <div class="w-full max-w-md">
        <div class="card bg-base-100 shadow-lg border border-base-200">
            <div class="card-body">
                <h1 class="card-title text-2xl sm:text-3xl mb-2 text-center justify-center">Reset password</h1>
                <p class="text-center text-sm text-base-content/70 mb-4">Enter your email, then the code we send, then your new password.</p>

                <input type="hidden" id="forgotAfterResetUrl" value="<?php echo htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" />

                <div id="forgotStepEmail">
                    <div id="forgotMsgEmail" class="mb-4"></div>
                    <form id="forgotFormEmail" class="space-y-4" method="post" action="<?php echo htmlspecialchars($selfUrl, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="on">
                        <div class="form-control">
                            <label class="label" for="forgotEmail">
                                <span class="label-text font-medium">Email</span>
                            </label>
                            <input type="email" id="forgotEmail" name="email" required placeholder="you@example.com" class="input input-bordered w-full" autocomplete="email" />
                        </div>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-paper-plane mr-2" aria-hidden="true"></i> Send code
                        </button>
                    </form>
                </div>

                <div id="forgotStepOtp" class="hidden">
                    <div id="forgotMsgOtp" class="mb-4"></div>
                    <form id="forgotFormOtp" class="space-y-4" method="post" action="<?php echo htmlspecialchars($selfUrl, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Email</span>
                            </label>
                            <p id="forgotOtpEmailDisplay" class="input input-bordered w-full bg-base-200 flex items-center min-h-12 text-sm text-base-content/80"></p>
                            <input type="hidden" name="email" id="forgotOtpEmailHidden" value="" />
                        </div>
                        <div class="form-control">
                            <label class="label" for="forgotOtpInput">
                                <span class="label-text font-medium">One-time code (OTP)</span>
                            </label>
                            <input
                                type="text"
                                id="forgotOtpInput"
                                name="otp"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                class="input input-bordered w-full tracking-widest text-center text-lg"
                                placeholder="000000"
                                required
                            />
                        </div>
                        <div class="flex flex-wrap justify-between gap-2 items-center text-sm">
                            <button type="button" id="forgotBackToEmail" class="btn btn-ghost btn-sm">Change email</button>
                            <button type="button" id="forgotResendOtp" class="link link-primary font-semibold">Resend code</button>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-check mr-2" aria-hidden="true"></i> Verify code
                        </button>
                    </form>
                </div>

                <div id="forgotStepPassword" class="hidden">
                    <div id="forgotMsgPassword" class="mb-4"></div>
                    <form id="forgotFormPassword" class="space-y-4" method="post" action="<?php echo htmlspecialchars($selfUrl, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Email</span>
                            </label>
                            <p id="forgotNewEmail" class="input input-bordered w-full bg-base-200 flex items-center min-h-12 text-sm text-base-content/80"></p>
                            <input type="hidden" name="email" id="forgotPasswordEmailHidden" value="" />
                        </div>
                        <div class="form-control">
                            <label class="label" for="forgotNewPassword">
                                <span class="label-text font-medium">New password</span>
                            </label>
                            <input type="password" id="forgotNewPassword" name="new_password" minlength="6" class="input input-bordered w-full" required />
                        </div>
                        <div class="form-control">
                            <label class="label" for="forgotConfirmPassword">
                                <span class="label-text font-medium">Confirm password</span>
                            </label>
                            <input type="password" id="forgotConfirmPassword" name="confirm_password" minlength="6" class="input input-bordered w-full" required />
                        </div>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-key mr-2" aria-hidden="true"></i> Update password
                        </button>
                    </form>
                </div>

                <p class="text-center text-sm mt-6">
                    <a href="<?php echo htmlspecialchars(APP_ROUTE, ENT_QUOTES, 'UTF-8'); ?>?page=login" class="link link-primary font-semibold">Back to sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>
<script>
window.FORGOT_APP_URL = <?php echo json_encode(rtrim(APP_URL, '/'), JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="<?php echo htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8'); ?>/public/js/forgot_password.js?v=<?php echo htmlspecialchars($forgotJsVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>
