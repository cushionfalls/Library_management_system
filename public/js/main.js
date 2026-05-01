// App root URL (same origin as PHP APP_URL when main.js is loaded from /public/js/main.js).
function appBaseUrl() {
    const el = document.querySelector('script[src*="main.js"]');
    if (el && el.src) {
        return el.src.replace(/\/public\/js\/main\.js(?:\?.*)?$/i, '');
    }
    return '';
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
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Format currency
function formatCurrency(amount) {
    return amount;
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

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
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
document.addEventListener('DOMContentLoaded', function() {
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
window.showToast = showToast;
window.apiCall = apiCall;
window.validateForm = validateForm;
window.openLogoutModal = openLogoutModal;
window.confirmLogout = confirmLogout;
window.logout = confirmLogout;
window.redirect = redirect;
window.Storage = Storage;
window.SessionStorage = SessionStorage;

// Navbar book search dropdown (always visible).
function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function initNavbarBookSearch() {
    const input = document.getElementById('navbarSearchInput');
    const results = document.getElementById('navbarSearchResults');
    if (!input || !results) return;

    const runSearch = debounce(async function () {
        const query = (input.value || '').trim();
        if (query.length < 2) {
            results.classList.add('hidden');
            results.innerHTML = '';
            return;
        }

        const url = appBaseUrl() + '/controllers/books.php?action=suggestions&search=' + encodeURIComponent(query) + '&limit=6';
        const response = await fetch(url);
        const data = await response.json().catch(() => null);

        if (!data || !data.success) {
            results.classList.add('hidden');
            results.innerHTML = '';
            return;
        }

        const books = data.books || [];
        if (books.length === 0) {
            results.classList.add('hidden');
            results.innerHTML = '';
            return;
        }

        const items = books.map(b => {
            const safeName = escapeHtml(b.name);
            const detailUrl = appBaseUrl() + '/public/index.php?page=books&book=' + encodeURIComponent(b.id) + '&search=' + encodeURIComponent(query);
            return '<li><a href="' + detailUrl + '" class="justify-between"><span>' + safeName + '</span></a></li>';
        }).join('');

        results.innerHTML = '<ul class="menu bg-base-100 rounded-box shadow border border-base-200 p-2">' + items + '</ul>';
        results.classList.remove('hidden');
    }, 300);

    input.addEventListener('input', runSearch);

    document.addEventListener('click', function (e) {
        if (e.target === input) return;
        if (results.contains(e.target)) return;
        results.classList.add('hidden');
    });
}

document.addEventListener('DOMContentLoaded', initNavbarBookSearch);

