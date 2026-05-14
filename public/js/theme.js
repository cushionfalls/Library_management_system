(function () {
    'use strict';

    var STORAGE_KEY = 'lumina-theme';

    function getStoredMode() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function resolveMode() {
        var stored = getStoredMode();
        if (stored === 'light' || stored === 'dark') {
            return stored;
        }
        if (typeof window.matchMedia === 'function' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    function applyMode(mode) {
        var dark = mode === 'dark';
        var root = document.documentElement;
        root.classList.toggle('dark', dark);
        root.setAttribute('data-theme', dark ? 'dark' : 'light');
        try {
            document.dispatchEvent(new CustomEvent('lumina-themechange', { detail: { mode: mode } }));
        } catch (e) { /* ignore */ }
    }

    function persistMode(mode) {
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (e) { /* ignore */ }
    }

    function toggleMode() {
        var next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        persistMode(next);
        applyMode(next);
    }

    function bindToggles() {
        document.querySelectorAll('[data-lumina-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                toggleMode();
            });
        });
    }

    function syncBootScript() {
        applyMode(resolveMode());
    }

    window.LuminaTheme = {
        STORAGE_KEY: STORAGE_KEY,
        resolveMode: resolveMode,
        applyMode: applyMode,
        persistMode: persistMode,
        toggleMode: toggleMode,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            syncBootScript();
            bindToggles();
        });
    } else {
        syncBootScript();
        bindToggles();
    }

    window.addEventListener('storage', function (e) {
        if (e.key !== STORAGE_KEY) return;
        var v = e.newValue;
        if (v === 'light' || v === 'dark') {
            applyMode(v);
        } else {
            applyMode(resolveMode());
        }
    });

    if (typeof window.matchMedia === 'function') {
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', function () {
                if (getStoredMode() === 'light' || getStoredMode() === 'dark') return;
                applyMode(resolveMode());
            });
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(function () {
                if (getStoredMode() === 'light' || getStoredMode() === 'dark') return;
                applyMode(resolveMode());
            });
        }
    }
})();
