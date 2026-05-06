(function () {
    function adminSearchApiUrl(action) {
        const base = window.ADMIN_SEARCH_API_URL || '';
        return base + '?action=' + encodeURIComponent(action);
    }

    function getActiveAdminTab() {
        if (window.__adminActiveTab) {
            return window.__adminActiveTab;
        }
        const btn = document.querySelector('.admin-tab-btn.text-white');
        return btn ? btn.getAttribute('data-tab') || 'books' : 'books';
    }

    function placeholderForTab(tab) {
        switch (tab) {
            case 'users':
                return 'Search users by name or email...';
            case 'transactions':
                return 'Search by book, member, or type...';
            case 'overdue':
                return 'Search overdue by book or member...';
            default:
                return 'Search books by ISBN, title, author, or publisher...';
        }
    }

    function debounce(fn, ms) {
        let t;
        return function () {
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(function () {
                fn.apply(null, args);
            }, ms);
        };
    }

    async function runAdminSearch() {
        const input = document.getElementById('adminCatalogSearchInput');
        if (!input) return;

        const q = String(input.value || '').trim();
        const tab = getActiveAdminTab();

        if (q === '') {
            if (typeof window.loadAdminDashboard === 'function') {
                await window.loadAdminDashboard();
            }
            return;
        }

        const url =
            adminSearchApiUrl('search') +
            '&type=' +
            encodeURIComponent(tab) +
            '&q=' +
            encodeURIComponent(q);

        let result;
        try {
            const response = await fetch(url, { cache: 'no-store' });
            result = await response.json();
        } catch (e) {
            if (typeof window.adminToast === 'function') {
                window.adminToast('Search request failed', 'error');
            }
            return;
        }

        if (!result || !result.success) {
            if (typeof window.adminToast === 'function') {
                window.adminToast((result && result.error) || 'Search failed', 'error');
            }
            return;
        }

        const rows = result.data || [];
        if (tab === 'books' && typeof window.renderBooks === 'function') {
            window.renderBooks(rows);
            window.__adminBooks = rows;
        } else if (tab === 'users' && typeof window.renderUsers === 'function') {
            window.renderUsers(rows);
            window.__adminUsers = rows;
        } else if (tab === 'transactions' && typeof window.renderTransactions === 'function') {
            window.renderTransactions(rows);
        } else if (tab === 'overdue' && typeof window.renderOverdue === 'function') {
            window.renderOverdue(rows);
        }
    }

    const debouncedSearch = debounce(runAdminSearch, 320);

    function syncPlaceholder() {
        const input = document.getElementById('adminCatalogSearchInput');
        if (input) {
            input.placeholder = placeholderForTab(getActiveAdminTab());
        }
    }

    function bindAdminSearch() {
        const input = document.getElementById('adminCatalogSearchInput');
        if (!input) return;

        input.addEventListener('input', debouncedSearch);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                input.value = '';
                runAdminSearch();
            }
        });

        document.querySelectorAll('.admin-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setTimeout(function () {
                    syncPlaceholder();
                    debouncedSearch();
                }, 0);
            });
        });

        syncPlaceholder();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindAdminSearch);
    } else {
        bindAdminSearch();
    }
})();
