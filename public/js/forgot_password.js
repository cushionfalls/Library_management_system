/**
 * Forgot password: email -> OTP -> new password.
 * Uses window.FORGOT_APP_URL from views/forgot_password.php (avoids wrong base when script path fails).
 */
(function () {
    const root = document.getElementById("forgotPasswordRoot");
    let base =
        typeof window.FORGOT_APP_URL === "string" && window.FORGOT_APP_URL
            ? window.FORGOT_APP_URL
            : root && root.getAttribute("data-app-url");
    if (!base) {
        const s = document.querySelector('script[src*="forgot_password.js"]');
        if (s && s.src) {
            base = s.src.replace(/\/public\/js\/forgot_password\.js(?:\?.*)?$/i, "");
        }
    }
    if (!base) {
        return;
    }
    base = String(base).replace(/\/$/, "");

    const el = (id) => document.getElementById(id);
    const msg = (id, html) => {
        const n = el(id);
        if (n) n.innerHTML = html || "";
    };

    const step1 = el("forgotStepEmail");
    const step2 = el("forgotStepOtp");
    const step3 = el("forgotStepPassword");
    if (!step1 || !step2 || !step3) {
        return;
    }

    const emailState = { value: "" };

    function showStep(n) {
        step1.classList.toggle("hidden", n !== 1);
        step2.classList.toggle("hidden", n !== 2);
        step3.classList.toggle("hidden", n !== 3);
    }

    function goStep2WithEmail(addr) {
        const hid = el("forgotOtpEmailHidden");
        const disp = el("forgotOtpEmailDisplay");
        if (hid) hid.value = addr;
        if (disp) disp.textContent = addr;
        if (el("forgotOtpInput")) el("forgotOtpInput").value = "";
        if (el("forgotPasswordEmailHidden")) el("forgotPasswordEmailHidden").value = addr;
        if (el("forgotNewEmail")) el("forgotNewEmail").textContent = addr;
        emailState.value = addr;
        showStep(2);
    }

    function goStep3() {
        const addr = (el("forgotOtpEmailHidden") && el("forgotOtpEmailHidden").value) || emailState.value;
        if (el("forgotPasswordEmailHidden")) el("forgotPasswordEmailHidden").value = addr;
        if (el("forgotNewEmail")) el("forgotNewEmail").textContent = addr;
        if (el("forgotNewPassword")) el("forgotNewPassword").value = "";
        if (el("forgotConfirmPassword")) el("forgotConfirmPassword").value = "";
        showStep(3);
    }

    el("forgotFormEmail")?.addEventListener("submit", async function (e) {
        e.preventDefault();
        e.stopPropagation();
        const fd = new FormData(this);
        const email = (fd.get("email") || "").toString().trim();
        if (!email) return;

        msg("forgotMsgEmail", "");
        try {
            const res = await fetch(base + "/controllers/auth.php?action=request-password-reset", {
                method: "POST",
                body: fd,
                credentials: "same-origin",
            });
            const out = await res.json();
            if (out.success) {
                goStep2WithEmail(email);
                msg(
                    "forgotMsgOtp",
                    '<div class="alert alert-info"><i class="fas fa-envelope mr-2"></i>' +
                        (out.message || "Check your email.") +
                        "</div>"
                );
            } else {
                msg(
                    "forgotMsgEmail",
                    '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' +
                        (out.error || "Request failed") +
                        "</div>"
                );
            }
        } catch (err) {
            msg(
                "forgotMsgEmail",
                '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>Network error. Try again.</div>'
            );
        }
    });

    el("forgotResendOtp")?.addEventListener("click", async function (e) {
        e.preventDefault();
        const email = (el("forgotOtpEmailHidden") && el("forgotOtpEmailHidden").value) || emailState.value;
        if (!email) return;
        msg("forgotMsgOtp", "");
        const body = new URLSearchParams();
        body.set("email", email);
        try {
            const res = await fetch(base + "/controllers/auth.php?action=request-password-reset", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body.toString(),
                credentials: "same-origin",
            });
            const out = await res.json();
            if (out.success) {
                msg(
                    "forgotMsgOtp",
                    '<div class="alert alert-info"><i class="fas fa-check mr-2"></i>' + (out.message || "OTP sent.") + "</div>"
                );
            } else {
                msg(
                    "forgotMsgOtp",
                    '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (out.error || "Failed") + "</div>"
                );
            }
        } catch (err) {
            msg("forgotMsgOtp", '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>Network error.</div>');
        }
    });

    el("forgotFormOtp")?.addEventListener("submit", async function (e) {
        e.preventDefault();
        e.stopPropagation();
        const email = (el("forgotOtpEmailHidden") && el("forgotOtpEmailHidden").value.trim()) || emailState.value.trim();
        const otp = (el("forgotOtpInput") && el("forgotOtpInput").value.trim()) || "";
        if (!email || !otp) {
            msg("forgotMsgOtp", '<div class="alert alert-error">Enter the 6-digit code.</div>');
            return;
        }
        const fd = new FormData();
        fd.set("email", email);
        fd.set("otp", otp);

        msg("forgotMsgOtp", "");
        try {
            const res = await fetch(base + "/controllers/auth.php?action=verify-password-reset-otp", {
                method: "POST",
                body: fd,
                credentials: "same-origin",
            });
            const text = await res.text();
            let out;
            try {
                out = JSON.parse(text);
            } catch (parseErr) {
                msg("forgotMsgOtp", '<div class="alert alert-error">Server error. Check APP_URL in config and try again.</div>');
                return;
            }
            if (out.success) {
                goStep3();
                msg(
                    "forgotMsgPassword",
                    '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>' + (out.message || "Verified.") + "</div>"
                );
            } else {
                msg(
                    "forgotMsgOtp",
                    '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (out.error || "Invalid code") + "</div>"
                );
            }
        } catch (err) {
            msg("forgotMsgOtp", '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>Network error.</div>');
        }
    });

    el("forgotFormPassword")?.addEventListener("submit", async function (e) {
        e.preventDefault();
        e.stopPropagation();
        const email = (el("forgotPasswordEmailHidden") && el("forgotPasswordEmailHidden").value.trim()) || emailState.value.trim();
        const p1 = el("forgotNewPassword") && el("forgotNewPassword").value;
        const p2 = el("forgotConfirmPassword") && el("forgotConfirmPassword").value;
        const fd = new FormData();
        fd.set("email", email);
        fd.set("new_password", p1);
        fd.set("confirm_password", p2);
        msg("forgotMsgPassword", "");
        try {
            const res = await fetch(base + "/controllers/auth.php?action=reset-password", {
                method: "POST",
                body: fd,
                credentials: "same-origin",
            });
            const out = await res.json();
            if (out.success) {
                const dest = el("forgotAfterResetUrl") && el("forgotAfterResetUrl").value;
                if (dest) window.location.href = dest;
            } else {
                msg(
                    "forgotMsgPassword",
                    '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>' + (out.error || "Failed") + "</div>"
                );
            }
        } catch (err) {
            msg("forgotMsgPassword", '<div class="alert alert-error"><i class="fas fa-exclamation-circle mr-2"></i>Network error.</div>');
        }
    });

    el("forgotBackToEmail")?.addEventListener("click", function () {
        showStep(1);
        msg("forgotMsgOtp", "");
    });
})();
