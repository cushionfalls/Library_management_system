(function () {
    'use strict';

    function baseUrl() {
        if (typeof window.APP_URL === 'string' && window.APP_URL) return window.APP_URL.replace(/\/$/, '');
        const el = document.querySelector('script[src*="wallet.js"]');
        if (el && el.src) return el.src.replace(/\/public\/js\/wallet\.js(?:\?.*)?$/i, '');
        return '';
    }

    const BASE = baseUrl();
    const API  = BASE + '/controllers/wallet.php?action=';

    function getCsrfToken() {
        const el = document.querySelector('input[name="csrf_token"]');
        return el ? el.value : '';
    }

    // ── Unit convention ────────────────────────────────────────────────────
    // ALL amounts in this file are in CENTS (integer) unless a variable is
    // explicitly named *Dollars or *Float. formatUsdFromCents() is the only
    // place that divides by 100 for display. The wire protocol (POST body)
    // always sends `amount_cents` as an integer string so PHP never needs to
    // multiply and cannot accidentally double-convert.
    // ─────────────────────────────────────────────────────────────────────
    const TOPUP_MAX_CENTS = 100000; // $1,000.00 — 100 000 cents

    let txOffset   = 0;
    const TX_LIMIT = 10;
    let txDone     = false;

    const $balanceHero  = document.getElementById('walletBalanceHero');
    const $txBody       = document.getElementById('walletTxBody');
    const $loadMoreBtn  = document.getElementById('walletLoadMoreBtn');
    const $topUpBtn     = document.getElementById('walletTopUpBtn');
    const $topUpModal   = document.getElementById('walletTopUpModal');
    const $topUpForm    = document.getElementById('walletTopUpForm');
    const $submitBtn    = document.getElementById('walletTopUpSubmitBtn');
    const $submitLabel  = document.getElementById('walletTopUpSubmitLabel');
    const $gatewayInfo  = document.getElementById('walletGatewayInfo');
    const $gatewayText  = document.getElementById('walletGatewayInfoText');
    const $dlBtn        = document.getElementById('walletDownloadStatementBtn');
    const $brandIcon    = document.getElementById('walletCardBrandIcon');
    const $methodSel    = $topUpForm?.querySelector('[name="method"]');
    const $amountInput  = $topUpForm?.querySelector('[name="amount"]');
    const STRIPE_PK     = (typeof window.STRIPE_PUBLISHABLE_KEY === 'string' ? window.STRIPE_PUBLISHABLE_KEY : '').trim();


    function reasonLabel(r) {
        const map = {
            TOP_UP       : 'Wallet Top Up',
            BOOK_RENT    : 'Book Rental',
            BOOK_BUY     : 'Book Purchase',
            REFUND       : 'Refund',
            MEMBERSHIP   : 'Membership Purchase',
        };
        return map[String(r || '').toUpperCase()] || 'Wallet Transaction';
    }

    function reasonIcon(r) {
        const map = {
            TOP_UP       : 'account_balance_wallet',
            BOOK_RENT    : 'book',
            BOOK_BUY     : 'shopping_bag',
            REFUND       : 'replay',
            MEMBERSHIP   : 'verified_user',
        };
        return map[String(r || '').toUpperCase()] || 'payments';
    }

    function toast(msg, type = 'info') {
        document.querySelectorAll('.w-toast').forEach(el => el.remove());
        const colors = { success: '#16a34a', error: '#dc2626', info: '#3800bf', warning: '#d97706' };
        const icons  = { success: 'check_circle', error: 'error', warning: 'warning', info: 'info' };

        const el = document.createElement('div');
        el.className = 'w-toast';
        Object.assign(el.style, {
            position:     'fixed',
            top:          '24px',
            right:        '24px',
            zIndex:       '9999',
            background:   colors[type] || colors.info,
            color:        '#fff',
            padding:      '14px 22px',
            borderRadius: '12px',
            fontFamily:   'Inter, system-ui, sans-serif',
            fontSize:     '14px',
            fontWeight:   '600',
            display:      'flex',
            alignItems:   'center',
            gap:          '10px',
            boxShadow:    '0 8px 32px rgba(0,0,0,.18)',
            transition:   'opacity .3s, transform .3s',
        });
        el.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px">${icons[type] || 'info'}</span>${escapeHtml(msg)}`;
        document.body.appendChild(el);

        setTimeout(() => {
            el.style.opacity   = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 320);
        }, 3800);
    }

    async function loadBalance() {
        try {
            const res  = await fetch(API + 'getBalance', { credentials: 'same-origin', cache: 'no-store' });
            const data = await res.json();
            if (data.success && $balanceHero) {
                const balanceStr = typeof formatUsdFromCents === 'function' 
                    ? formatUsdFromCents(data.balance ?? 0)
                    : '$' + (Number(data.balance || 0) / 100).toFixed(2);
                $balanceHero.textContent = balanceStr;
                $balanceHero.style.color = '#ffffff'; // Restore white text
                $balanceHero.style.opacity = '1';
            }
        } catch (e) {
            console.error('Wallet: Failed to load balance', e);
        }
    }

    function txRowHtml(tx) {
        const isCredit = String(tx.type || '').toUpperCase() === 'CREDIT';
        const amtCls   = isCredit ? 'color:#16a34a' : 'color:#dc2626';
        const prefix   = isCredit ? '+' : '−';
        const statusBg = isCredit ? '#dcfce7' : '#fef2f2';
        const statusCol = isCredit ? '#166534' : '#991b1b';
        const statusText = isCredit ? 'Received' : 'Paid';

        return `
        <tr class="tx-row" style="border-bottom:1px solid #f1ebfb;transition:background .2s"
            onmouseover="this.style.background='rgba(56,0,191,0.02)'" onmouseout="this.style.background=''">
            <td class="px-6 py-5 text-sm font-semibold text-on-surface">${escapeHtml(formatDate(tx.created_at))}</td>
            <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[20px]">${escapeHtml(reasonIcon(tx.reason))}</span>
                    </div>
                    <span class="font-bold text-on-surface text-sm tracking-tight">${escapeHtml(reasonLabel(tx.reason))}</span>
                </div>
            </td>
            <td class="px-6 py-5 text-right text-sm font-black" style="${amtCls}">
                ${prefix}${escapeHtml(formatUsdFromCents(Math.abs(tx.amount || 0)))}
            </td>
            <td class="px-6 py-5">
                <div class="flex justify-end">
                    <span style="padding:4px 12px;border-radius:9999px;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:0.06em;
                                 background:${statusBg};color:${statusCol};border:1px solid ${statusCol}20">${statusText}</span>
                </div>
            </td>
        </tr>`;
    }

    function setTxSpinner() {
        if (!$txBody) return;
        $txBody.innerHTML = `
        <tr><td colspan="4" class="px-6 py-10 text-center text-on-surface">
            <div style="display:flex;align-items:center;justify-content:center;gap:12px">
                <svg style="animation:spin 1s linear infinite;width:24px;height:24px;color:#3800bf"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle opacity=".15" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path opacity=".9" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span class="font-bold tracking-tight">Loading transactions…</span>
            </div>
        </td></tr>`;
    }

    async function loadTransactions(append = false) {
        if (txDone && append) return;
        if (!$txBody) return;

        if (!append) {
            txOffset = 0;
            txDone   = false;
            setTxSpinner();
        }

        try {
            const res  = await fetch(`${API}getTransactions&limit=${TX_LIMIT}&offset=${txOffset}`,
                                     { credentials: 'same-origin', cache: 'no-store' });
            const data = await res.json();
            if (!data.success) throw new Error('failed');

            const txs = Array.isArray(data.transactions) ? data.transactions : [];
            if (!append) $txBody.innerHTML = '';

            if (txs.length === 0 && txOffset === 0) {
                $txBody.innerHTML = `<tr><td colspan="4" class="px-6 py-10 text-sm text-on-surface-variant text-center">
                    No transactions yet.</td></tr>`;
                txDone = true;
            } else {
                $txBody.insertAdjacentHTML('beforeend', txs.map(txRowHtml).join(''));
                txOffset += txs.length;
                if (txs.length < TX_LIMIT) txDone = true;
            }
        } catch (_) {
            if (!append) {
                $txBody.innerHTML = `<tr><td colspan="4" class="px-6 py-6 text-sm text-error">
                    Failed to load transactions.</td></tr>`;
            }
        }

        if ($loadMoreBtn) {
            $loadMoreBtn.disabled = txDone;
            $loadMoreBtn.style.opacity = txDone ? '0.4' : '1';
        }
    }

    let stripe = null;
    let stripeCardNumber = null;
    let stripeCardExpiry = null;
    let stripeCardCvc    = null;
    let stripePostal     = null;

    function setCardBrandIcon(brand) {
        if (!$brandIcon) return;
        const map = {
            visa: 'fa-brands fa-cc-visa',
            mastercard: 'fa-brands fa-cc-mastercard',
            amex: 'fa-brands fa-cc-amex',
            discover: 'fa-brands fa-cc-discover',
            diners: 'fa-brands fa-cc-diners-club',
            jcb: 'fa-brands fa-cc-jcb',
            unionpay: 'fa-brands fa-cc-stripe',
        };
        const cls = map[String(brand || '').toLowerCase()] || 'fa-regular fa-credit-card';
        $brandIcon.innerHTML = `<i class="${cls} text-lg"></i>`;
    }

    function ensureStripeMounted(publishableKey) {
        if (stripe && stripeCardNumber && stripeCardExpiry && stripeCardCvc && stripePostal) return;
        if (!publishableKey || typeof window.Stripe !== 'function') throw new Error('Stripe is not available');

        stripe = window.Stripe(publishableKey);
        const elements = stripe.elements();

        const style = {
            base: {
                fontSize: '16px',
                lineHeight: '24px',
                color: '#1c1a25',
                fontWeight: '500',
                // system fonts first: Stripe fields render in a cross-origin iframe where
                // page-loaded webfonts may not apply reliably.
                fontFamily: 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
                fontSmoothing: 'antialiased',
                letterSpacing: '0.02em',
                '::placeholder': {
                    color: '#94a3b8',
                },
            },
            invalid: {
                color: '#ef4444',
                iconColor: '#ef4444',
            },
        };

        stripeCardNumber = elements.create('cardNumber', { style });
        stripeCardExpiry = elements.create('cardExpiry', { style });
        stripeCardCvc    = elements.create('cardCvc', { style });
        stripePostal     = elements.create('postalCode', { style });

        const elNum = document.getElementById('walletStripeCardNumber');
        const elExp = document.getElementById('walletStripeCardExpiry');
        const elCvc = document.getElementById('walletStripeCardCvc');
        const elZip = document.getElementById('walletStripePostal');
        if (!elNum || !elExp || !elCvc || !elZip) throw new Error('Missing Stripe card fields');

        stripeCardNumber.mount(elNum);
        stripeCardExpiry.mount(elExp);
        stripeCardCvc.mount(elCvc);
        stripePostal.mount(elZip);

        stripeCardNumber.on('change', (evt) => setCardBrandIcon(evt.brand));
        setCardBrandIcon('');
    }

    function syncMethodUI() {
        if ($submitLabel) $submitLabel.textContent = 'Pay with card';
        if ($gatewayInfo) {
            $gatewayInfo.classList.remove('hidden');
            $gatewayInfo.classList.add('flex');
        }
    }

    /** Quick amount presets + visual selection ring */
    function wireTopUpPresets() {
        const root = document.getElementById('walletTopUpForm');
        if (!$amountInput || !root) return;

        root.addEventListener('click', (e) => {
            const btn = e.target.closest('.wallet-topup-preset');
            if (!btn) return;
            const raw = btn.getAttribute('data-dollar');
            const n   = Number(raw);
            if (!Number.isFinite(n)) return;
            $amountInput.value = n.toFixed(2);
            $amountInput.focus();
            console.log('Wallet: Set preset amount', n);
            root.querySelectorAll('.wallet-topup-preset.ring-preset').forEach(el => el.classList.remove('ring-preset'));
            btn.classList.add('ring-preset');
        });

        const clearRing = () => {
            root.querySelectorAll('.wallet-topup-preset.ring-preset').forEach(el => el.classList.remove('ring-preset'));
        };

        $amountInput.addEventListener('input', clearRing);
        $amountInput.addEventListener('keydown', clearRing);
    }

    function resetModal() {
        $topUpForm?.reset();
        document.querySelectorAll('#walletTopUpForm .wallet-topup-preset.ring-preset').forEach(el => el.classList.remove('ring-preset'));
        if ($submitLabel) $submitLabel.textContent = 'Pay with card';
        if ($submitBtn) $submitBtn.disabled    = false;
        syncMethodUI();
    }

    function setSubmitLoading(on, text = 'Processing…') {
        if (!$submitBtn) return;
        $submitBtn.disabled     = on;
        if ($submitLabel) $submitLabel.textContent = on ? text : 'Pay with card';
    }

    async function handleTopUpSubmit(e) {
        e.preventDefault();

        // User types dollars (e.g. "10.50") — convert to cents immediately.
        const amountDollars = Number($amountInput?.value || 0);
        const amountCents   = Math.round(amountDollars * 100); // integer cents

        /* Validate amount */
        if (!amountCents || amountCents <= 0) { toast('Enter a valid amount.', 'warning'); return; }
        if (amountCents > TOPUP_MAX_CENTS)     { toast(`Maximum top-up is ${formatUsdFromCents(TOPUP_MAX_CENTS)}.`, 'warning'); return; }

        setSubmitLoading(true, 'Creating payment…');
        try {
            // Send `amount_cents` (integer cents) — PHP reads this directly,
            // no multiplication needed on the server side.
            const body = new URLSearchParams({ 
                amount_cents: String(amountCents),
                csrf_token: getCsrfToken()
            });
            const res  = await fetch(API + 'stripe-create-intent', {
                method:      'POST',
                headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:        body.toString(),
                credentials: 'same-origin',
                cache:       'no-store',
            });
            const data = await res.json();

            if (!data.success) {
                toast(data.error || 'Failed to start payment.', 'error');
                setSubmitLoading(false);
                return;
            }

            ensureStripeMounted((data.publishableKey || STRIPE_PK));
            setSubmitLoading(true, 'Confirming card payment…');

            const result = await stripe.confirmCardPayment(data.client_secret, {
                payment_method: { card: stripeCardNumber }
            });
            if (result.error) {
                toast(result.error.message || 'Payment failed.', 'error');
                setSubmitLoading(false);
                return;
            }

            const pi = result.paymentIntent;
            if (!pi || pi.status !== 'succeeded') {
                toast('Payment not completed.', 'error');
                setSubmitLoading(false);
                return;
            }

            setSubmitLoading(true, 'Finalizing top up…');
            const res2 = await fetch(API + 'stripe-finalize', {
                method:      'POST',
                headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:        new URLSearchParams({ 
                    payment_intent: pi.id,
                    csrf_token: getCsrfToken()
                }).toString(),
                credentials: 'same-origin',
                cache:       'no-store',
            });
            const data2 = await res2.json();
            if (!data2.success) {
                toast(data2.error || data2.message || 'Failed to credit wallet.', 'error');
                setSubmitLoading(false);
                return;
            }

            // data2.balance is in cents — formatUsdFromCents handles display.
            toast(`Top up successful! New balance: ${formatUsdFromCents(data2.balance ?? 0)}`, 'success');
            if (typeof $topUpModal?.close === 'function') $topUpModal.close();
            resetModal();
            await loadBalance();
            await loadTransactions(false);
        } catch (_) {
            toast('Network error. Please try again.', 'error');
            setSubmitLoading(false);
        }
    }

    function handleGatewayCallback() {
        const params = new URLSearchParams(window.location.search);
        const topup  = params.get('topup');
        if (!topup) return;

        if (topup === 'success') {
            const method = (params.get('method') || 'gateway').toUpperCase();
            // `amount_cents` URL param is always integer cents (set by PHP redirect).
            const amountCentsStr = params.get('amount_cents') || params.get('amount') || '';
            const amountCents    = amountCentsStr ? parseInt(amountCentsStr, 10) : 0;
            toast(
                `${method} top up of ${amountCents > 0 ? formatUsdFromCents(amountCents) : ''} successful!`,
                'success'
            );
        } else if (topup === 'failed') {
            const reason = params.get('reason') || 'unknown';
            const msgs   = {
                cancelled:           'Payment was cancelled.',
                verification_failed: 'Payment verification failed. Contact support if amount was deducted.',
                session_expired:     'Session expired. Please try again.',
                credit_failed:       'Payment verified but wallet credit failed. Contact support.',
                no_data:             'Invalid payment response.',
            };
            toast(msgs[reason] || 'Top up failed. Please try again.', 'error');
        }

        /* Clean up URL without page reload */
        const clean = new URL(window.location.href);
        ['topup', 'method', 'amount', 'amount_cents', 'reason'].forEach(k => clean.searchParams.delete(k));
        window.history.replaceState({}, '', clean.toString());
    }

    function init() {
        if (STRIPE_PK) {
            try {
                ensureStripeMounted(STRIPE_PK);
            } catch (_) {
                toast('Card form unavailable. Check Stripe key.', 'error');
            }
        }

        /* Open modal */
        $topUpBtn?.addEventListener('click', () => {
            resetModal();
            if (typeof $topUpModal?.showModal === 'function') $topUpModal.showModal();
        });

        /* Reset on close */
        $topUpModal?.addEventListener('close', resetModal);

        /* Method change — swap button label */
        $methodSel?.addEventListener('change', () => {
            syncMethodUI();
        });

        /* Form submit */
        $topUpForm?.addEventListener('submit', handleTopUpSubmit);

        /* Load more transactions */
        $loadMoreBtn?.addEventListener('click', () => loadTransactions(true));

        /* Download statement */
        $dlBtn?.addEventListener('click', () => {
            window.location.href = API + 'downloadStatement&limit=500';
        });

        /* CSS spin keyframe (injected once) */
        if (!document.getElementById('w-spin-style')) {
            const s = document.createElement('style');
            s.id = 'w-spin-style';
            s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
            document.head.appendChild(s);
        }

        /* Initial data load */
        wireTopUpPresets();
        loadBalance();
        loadTransactions(false);
        handleGatewayCallback();
        syncMethodUI();
    }


    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
