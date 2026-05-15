// App root URL (same origin as PHP APP_URL when main.js is loaded from /public/js/main.js).
function appBaseUrl() {
    const el = document.querySelector('script[src*="main.js"]');
    if (el && el.src) {
        return el.src.replace(/\/public\/js\/main\.js(?:\?.*)?$/i, '');
    }
    return '';
}

// Asset URL helper
function assetUrl(path) {
    const raw = String(path || '').trim();
    if (!raw) return '';
    if (/^https?:\/\//i.test(raw)) return raw;
    const base = appBaseUrl();
    if (raw.startsWith('/')) return base + raw;
    return base + '/' + raw;
}

// Global functions
function openLogoutModal() {
    const modal = document.getElementById('logoutConfirmModal');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
        return;
    }
    // Fallback for pages without modal
    confirmLogout();
}

async function confirmLogout(event = null) {
    if (event && typeof event.preventDefault === 'function') {
        event.preventDefault();
    }

    try {
        const response = await fetch(appBaseUrl() + '/controllers/auth.php?action=logout', {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store'
        });

        const result = await response.json().catch(() => ({}));

        if (result.success) {
            const modal = document.getElementById('logoutConfirmModal');
            if (modal && typeof modal.close === 'function') modal.close();
            window.location.href = appBaseUrl() + '/public/index.php?page=home';
            return;
        }
        // Fallback redirect even if API response is malformed.
        window.location.href = appBaseUrl() + '/public/index.php?page=login';
    } catch (error) {
        console.error('Logout error:', error);
        window.location.href = appBaseUrl() + '/public/index.php?page=login';
    }
}

// Toast notification
function showToast(message, type = 'info') {
    const typeMap = {
        danger: '#dc3545',
        error: '#dc3545',
        warning: '#ffc107',
        success: '#198754',
        info: '#0dcaf0'
    };
    const bgColor = typeMap[type] || typeMap.info;

    const toastDiv = document.createElement('div');
    toastDiv.style.cssText = [
        'position: fixed',
        'top: 20px',
        'right: 20px',
        'z-index: 99999',
        'min-width: 260px',
        'max-width: 420px',
        'padding: 12px 14px',
        'border-radius: 8px',
        'color: #fff',
        'background: ' + bgColor,
        'box-shadow: 0 8px 24px rgba(0,0,0,0.2)',
        'font-size: 14px'
    ].join(';');
    toastDiv.textContent = message;

    // If a native dialog is open, render toast inside it so it stays visible above the modal layer.
    const openDialog = document.querySelector('dialog[open]');
    if (openDialog) {
        openDialog.appendChild(toastDiv);
    } else {
        document.body.appendChild(toastDiv);
    }

    setTimeout(() => {
        toastDiv.remove();
    }, 3000);
}

// Format date
function formatDate(dateString) {
    if (!dateString) return '—';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Format currency
function formatCurrency(amount) {
    return amount;
}

// Format USD from cents
function formatUsdFromCents(cents) {
    const dollars = (Number(cents || 0) / 100);
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(dollars);
}

// Escape HTML
function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// API call with error handling
async function apiCall(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                ...options.headers
            },
            ...options
        });

        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showToast('An error occurred. Please try again.', 'danger');
        return null;
    }
}

// Form validation
function validateForm(formElement) {
    const requiredFields = formElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Initialize tooltips and popovers (REMOVED: Bootstrap tooltips/popovers not used)
document.addEventListener('DOMContentLoaded', function () {
    // DaisyUI or other vanilla logic can go here
});

// Check authentication status
async function checkAuth() {
    try {
        const response = await fetch(appBaseUrl() + '/controllers/user.php?action=getProfile');
        const data = await response.json();
        return data.success;
    } catch (error) {
        return false;
    }
}

// Verify email before operations
function verifyEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Password strength validator
function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    return strength;
}

// Get password strength label
function getPasswordStrengthLabel(strength) {
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    return labels[strength] || 'Very Weak';
}

// DOM ready function
function ready(fn) {
    if (document.readyState !== 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

// Redirect function
function redirect(url) {
    window.location.href = url;
}

// Local storage helpers
const Storage = {
    set: (key, value) => localStorage.setItem(key, JSON.stringify(value)),
    get: (key) => JSON.parse(localStorage.getItem(key)),
    remove: (key) => localStorage.removeItem(key),
    clear: () => localStorage.clear()
};

// Session storage helpers
const SessionStorage = {
    set: (key, value) => sessionStorage.setItem(key, JSON.stringify(value)),
    get: (key) => JSON.parse(sessionStorage.getItem(key)),
    remove: (key) => sessionStorage.removeItem(key),
    clear: () => sessionStorage.clear()
};

// Lazy load images
document.addEventListener('DOMContentLoaded', function () {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
});

// Export functions for use in other scripts
window.formatDate = formatDate;
window.formatCurrency = formatCurrency;
window.formatUsdFromCents = formatUsdFromCents;
window.escapeHtml = escapeHtml;
window.showToast = showToast;
window.assetUrl = assetUrl;
window.apiCall = apiCall;
window.validateForm = validateForm;
window.openLogoutModal = openLogoutModal;
window.confirmLogout = confirmLogout;
window.logout = confirmLogout;
window.redirect = redirect;
window.Storage = Storage;
window.SessionStorage = SessionStorage;


