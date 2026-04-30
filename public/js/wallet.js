function walletBaseUrl() {
    // Reuse appBaseUrl from main.js if present.
    if (typeof window.appBaseUrl === 'function') return window.appBaseUrl();
    const el = document.querySelector('script[src*="wallet.js"]');
    if (el && el.src) {
        return el.src.replace(/\/public\/js\/wallet\.js(?:\?.*)?$/i, '');
    }
    return '';
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

function reasonLabel(reason) {
    switch (String(reason || '').toUpperCase()) {
        case 'TOP_UP': return 'Wallet Top Up';
        case 'BOOK_RENT': return 'Book Rental';
        case 'BOOK_BUY': return 'Book Purchase';
        case 'FINE_PAYMENT': return 'Fine Settlement';
        case 'REFUND': return 'Refund';
        case 'MEMBERSHIP': return 'Membership Purchase';
        default: return 'Wallet Transaction';
    }
}

function reasonIcon(reason) {
    switch (String(reason || '').toUpperCase()) {
        case 'TOP_UP': return 'account_balance_wallet';
        case 'BOOK_RENT': return 'book';
        case 'BOOK_BUY': return 'shopping_bag';
        case 'FINE_PAYMENT': return 'warning';
        case 'REFUND': return 'replay';
        case 'MEMBERSHIP': return 'verified_user';
        default: return 'payments';
    }
}

function amountClass(type) {
    return String(type || '').toUpperCase() === 'CREDIT'
        ? 'text-on-tertiary-fixed-variant'
        : 'text-error';
}

function amountPrefix(type) {
    return String(type || '').toUpperCase() === 'CREDIT' ? '+' : '-';
}

function badgeHtml() {
    return '<span class="px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold">Completed</span>';
}

let walletTxOffset = 0;
const walletTxLimit = 10;
let walletTxDone = false;

async function loadWalletBalance() {
    const res = await fetch(walletBaseUrl() + '/controllers/wallet.php?action=getBalance', { cache: 'no-store' });
    const data = await res.json().catch(() => null);
    if (!data || !data.success) return;

    const bal = Number(data.balance || 0);
    const el = document.getElementById('walletBalanceHero');
    if (el) el.textContent = (window.formatCurrency ? window.formatCurrency(bal) : ('₹' + bal));
}

function txRowHtml(tx) {
    const date = window.formatDate ? window.formatDate(tx.created_at) : (tx.created_at || '');
    const icon = reasonIcon(tx.reason);
    const desc = reasonLabel(tx.reason);
    const cls = amountClass(tx.type);
    const prefix = amountPrefix(tx.type);
    const amt = window.formatCurrency ? window.formatCurrency(Number(tx.amount || 0)) : ('₹' + (tx.amount || 0));

    return `
        <tr class="hover:bg-surface-container-highest transition-colors">
            <td class="px-6 py-6 text-sm font-medium text-on-surface-variant">${escapeHtml(date)}</td>
            <td class="px-6 py-6">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">${escapeHtml(icon)}</span>
                    <span class="font-semibold text-on-surface">${escapeHtml(desc)}</span>
                </div>
            </td>
            <td class="px-6 py-6 text-right font-bold ${cls}">${escapeHtml(prefix)}${escapeHtml(amt)}</td>
            <td class="px-6 py-6">${badgeHtml()}</td>
        </tr>
    `;
}

async function loadWalletTransactions(append = false) {
    if (walletTxDone) return;

    const tbody = document.getElementById('walletTxBody');
    if (!tbody) return;

    if (!append) {
        walletTxOffset = 0;
        walletTxDone = false;
        tbody.innerHTML = '<tr><td class="px-6 py-6 text-sm text-on-surface-variant" colspan="4">Loading transactions…</td></tr>';
    }

    const url = walletBaseUrl() + '/controllers/wallet.php?action=getTransactions&limit=' + walletTxLimit + '&offset=' + walletTxOffset;
    const res = await fetch(url, { cache: 'no-store' });
    const data = await res.json().catch(() => null);
    if (!data || !data.success) {
        tbody.innerHTML = '<tr><td class="px-6 py-6 text-sm text-error" colspan="4">Failed to load transactions.</td></tr>';
        return;
    }

    const txs = Array.isArray(data.transactions) ? data.transactions : [];
    if (!append) tbody.innerHTML = '';

    if (txs.length === 0 && walletTxOffset === 0) {
        tbody.innerHTML = '<tr><td class="px-6 py-6 text-sm text-on-surface-variant" colspan="4">No transactions yet.</td></tr>';
        walletTxDone = true;
        return;
    }

    tbody.insertAdjacentHTML('beforeend', txs.map(txRowHtml).join(''));
    walletTxOffset += txs.length;

    if (txs.length < walletTxLimit) walletTxDone = true;

    const loadMoreBtn = document.getElementById('walletLoadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.disabled = walletTxDone;
        loadMoreBtn.classList.toggle('opacity-50', walletTxDone);
    }
}

function initWalletTopUp() {
    const btn = document.getElementById('walletTopUpBtn');
    const modal = document.getElementById('walletTopUpModal');
    const form = document.getElementById('walletTopUpForm');
    const otpWrap = document.getElementById('walletOtpWrap');
    const submitBtn = document.getElementById('walletTopUpSubmitBtn');
    if (!btn || !modal || !form) return;

    let stage = 'request'; // request | verify

    btn.addEventListener('click', () => {
        if (typeof modal.showModal === 'function') modal.showModal();
    });

    modal.addEventListener('close', () => {
        stage = 'request';
        otpWrap?.classList.add('hidden');
        if (submitBtn) submitBtn.textContent = 'Send OTP';
        form.reset();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const fd = new FormData(form);
        const amount = Number(fd.get('amount') || 0);
        const method = String(fd.get('method') || 'OTHER');
        const otp = String(fd.get('otp') || '').trim();

        if (!Number.isFinite(amount) || amount <= 0) {
            window.showToast?.('Please enter a valid amount.', 'warning');
            return;
        }
        if (amount > 10000) {
            window.showToast?.('Max top up is 10000.', 'warning');
            return;
        }

        if (stage === 'request') {
            const body = new URLSearchParams();
            body.set('amount', String(Math.floor(amount)));
            body.set('method', method);

            const res = await fetch(walletBaseUrl() + '/controllers/wallet.php?action=request-topup-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const data = await res.json().catch(() => null);
            if (!data || !data.success) {
                window.showToast?.(data?.message || data?.error || 'Failed to send OTP.', 'danger');
                return;
            }

            window.showToast?.('OTP sent. Check your email.', 'success');
            stage = 'verify';
            otpWrap?.classList.remove('hidden');
            if (submitBtn) submitBtn.textContent = 'Verify & Top Up';
            return;
        }

        // verify
        if (otp.length < 4) {
            window.showToast?.('Please enter the OTP from your email.', 'warning');
            return;
        }

        const body2 = new URLSearchParams();
        body2.set('otp', otp);

        const res2 = await fetch(walletBaseUrl() + '/controllers/wallet.php?action=verify-topup-otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body2.toString(),
            credentials: 'same-origin',
            cache: 'no-store'
        });
        const data2 = await res2.json().catch(() => null);
        if (!data2 || !data2.success) {
            window.showToast?.(data2?.message || data2?.error || 'OTP verification failed.', 'danger');
            return;
        }

        window.showToast?.('Top up successful.', 'success');
        if (typeof modal.close === 'function') modal.close();
        await loadWalletBalance();
        await loadWalletTransactions(false);
    });
}

function initWalletDownloads() {
    const btn = document.getElementById('walletDownloadStatementBtn');
    if (!btn) return;
    btn.addEventListener('click', () => {
        const url = walletBaseUrl() + '/controllers/wallet.php?action=downloadStatement&limit=500';
        window.location.href = url;
    });
}

function initWalletLoadMore() {
    const btn = document.getElementById('walletLoadMoreBtn');
    if (!btn) return;
    btn.addEventListener('click', () => loadWalletTransactions(true));
}

document.addEventListener('DOMContentLoaded', () => {
    loadWalletBalance();
    loadWalletTransactions(false);
    initWalletTopUp();
    initWalletDownloads();
    initWalletLoadMore();
});

