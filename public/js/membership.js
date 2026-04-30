function membershipBaseUrl() {
    if (typeof window.appBaseUrl === 'function') return window.appBaseUrl();
    const el = document.querySelector('script[src*="membership.js"]');
    if (el && el.src) {
        return el.src.replace(/\/public\/js\/membership\.js(?:\?.*)?$/i, '');
    }
    return '';
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

function formatInr(amount) {
    if (typeof window.formatCurrency === 'function') return window.formatCurrency(Number(amount || 0));
    return '₹' + Number(amount || 0);
}

function fmtDate(dateStr) {
    if (!dateStr) return '';
    if (typeof window.formatDate === 'function') return window.formatDate(dateStr);
    return new Date(dateStr).toLocaleDateString('en-IN');
}

function planButtonLabel(active, plan) {
    if (!active) return 'Buy with Wallet';
    // If active, user is extending (or switching).
    return 'Extend with Wallet';
}

function planCardHtml(plan, active) {
    const name = plan.name || 'Plan';
    const price = Number(plan.price || 0);
    const days = Number(plan.duration_days || 0);

    const popular = String(plan.slug || '').toUpperCase() === 'MONTHS_6';
    const clsOuter = popular
        ? 'relative flex flex-col p-10 rounded-xl bg-surface-container-lowest ring-2 ring-primary ambient-shadow scale-105 z-10'
        : 'group relative flex flex-col p-10 rounded-xl bg-surface-container-low transition-all duration-300 hover:bg-surface-container-high ambient-shadow';

    const topBadge = popular
        ? `<div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-tertiary-fixed text-on-tertiary-fixed px-4 py-1 rounded-full text-xs font-bold tracking-wider uppercase">Best Value</div>`
        : '';

    const btnCls = popular
        ? 'w-full py-4 rounded-lg gradient-button text-white font-bold transition-all scale-98 active:opacity-70'
        : 'w-full py-4 rounded-lg bg-surface-container-highest text-on-surface font-bold hover:bg-outline-variant/20 transition-all scale-98 active:opacity-70';

    const durationLabel = days >= 365 ? '/yr' : '/plan';

    return `
        <div class="${clsOuter}">
            ${topBadge}
            <div class="mb-8">
                <h3 class="font-headline font-bold text-2xl mb-2">${escapeHtml(name)}</h3>
                <div class="flex items-baseline space-x-1">
                    <span class="text-4xl font-extrabold ${popular ? 'text-primary' : 'text-on-surface'}">${escapeHtml(formatInr(price))}</span>
                    <span class="text-on-surface-variant font-medium">${escapeHtml(durationLabel)}</span>
                </div>
                <p class="text-sm text-on-surface-variant mt-2">Duration: <span class="font-semibold text-on-surface">${escapeHtml(String(days))} days</span></p>
            </div>
            <ul class="flex-grow space-y-4 mb-10">
                <li class="flex items-start space-x-3 text-on-surface-variant font-medium">
                    <span class="material-symbols-outlined text-primary scale-90">check_circle</span>
                    <span>Unlimited monthly rentals</span>
                </li>
                <li class="flex items-start space-x-3 text-on-surface-variant font-medium">
                    <span class="material-symbols-outlined text-primary scale-90">check_circle</span>
                    <span>No late fines during active membership</span>
                </li>
                <li class="flex items-start space-x-3 text-on-surface-variant font-medium">
                    <span class="material-symbols-outlined text-primary scale-90">check_circle</span>
                    <span>Priority support</span>
                </li>
            </ul>
            <button class="${btnCls}" data-plan-id="${escapeHtml(plan.id)}">${escapeHtml(planButtonLabel(active, plan))}</button>
        </div>
    `;
}

async function loadWalletBalance() {
    const el = document.getElementById('membershipWalletBalance');
    if (!el) return;
    try {
        const res = await fetch(membershipBaseUrl() + '/controllers/wallet.php?action=getBalance', { cache: 'no-store' });
        const data = await res.json().catch(() => null);
        if (!data || !data.success) return;
        el.textContent = formatInr(data.balance || 0);
    } catch (_) {}
}

function renderStatus(active) {
    const planEl = document.getElementById('membershipCurrentPlan');
    const nextEl = document.getElementById('membershipNextBilling');
    if (!planEl || !nextEl) return;

    if (!active) {
        planEl.textContent = 'Not Activated';
        planEl.classList.remove('text-primary');
        planEl.classList.add('text-error');
        nextEl.textContent = 'Activate a plan to unlock benefits.';
        return;
    }

    planEl.textContent = active.plan_name || 'Active';
    planEl.classList.add('text-primary');
    planEl.classList.remove('text-error');
    nextEl.textContent = 'Valid until: ' + fmtDate(active.ends_at);
}

async function loadStatus() {
    const res = await fetch(membershipBaseUrl() + '/controllers/membership.php?action=getStatus', { cache: 'no-store' });
    const data = await res.json().catch(() => null);
    if (!data || !data.success) return null;
    renderStatus(data.active || null);
    return data.active || null;
}

async function loadPlans(active) {
    const grid = document.getElementById('membershipPlansGrid');
    if (!grid) return;
    const res = await fetch(membershipBaseUrl() + '/controllers/membership.php?action=getPlans', { cache: 'no-store' });
    const data = await res.json().catch(() => null);
    if (!data || !data.success) {
        grid.innerHTML = '<div class="p-10 rounded-xl bg-surface-container-low ambient-shadow"><p class="text-error font-medium">Failed to load plans.</p></div>';
        return;
    }
    const plans = Array.isArray(data.plans) ? data.plans : [];
    grid.innerHTML = plans.map(p => planCardHtml(p, active)).join('');

    grid.querySelectorAll('button[data-plan-id]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const planId = Number(btn.getAttribute('data-plan-id') || 0);
            if (!planId) return;

            btn.disabled = true;
            const oldText = btn.textContent;
            btn.textContent = 'Processing…';

            try {
                const body = new URLSearchParams();
                body.set('plan_id', String(planId));
                const res2 = await fetch(membershipBaseUrl() + '/controllers/membership.php?action=purchase', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                const data2 = await res2.json().catch(() => null);
                if (!data2 || !data2.success) {
                    window.showToast?.(data2?.message || data2?.error || 'Purchase failed.', 'danger');
                    return;
                }
                window.showToast?.('Membership activated.', 'success');
                await loadWalletBalance();
                const activeNow = await loadStatus();
                await loadPlans(activeNow);
            } catch (e) {
                window.showToast?.('Purchase failed. Please try again.', 'danger');
            } finally {
                btn.disabled = false;
                btn.textContent = oldText;
            }
        });
    });
}

function historyRowHtml(row) {
    const date = fmtDate(row.purchased_at);
    const plan = row.plan_name || row.plan_slug || 'Plan';
    const amt = formatInr(row.amount || 0);
    return `
        <tr class="hover:bg-surface-container-highest transition-colors">
            <td class="px-6 py-6 text-sm font-medium text-on-surface-variant">${escapeHtml(date)}</td>
            <td class="px-6 py-6 text-on-surface font-semibold">${escapeHtml(plan)}</td>
            <td class="px-6 py-6 text-right font-bold text-on-surface">${escapeHtml(amt)}</td>
        </tr>
    `;
}

async function loadHistory() {
    const bodyEl = document.getElementById('membershipHistoryBody');
    if (!bodyEl) return;
    bodyEl.innerHTML = '<tr><td class="px-6 py-6 text-sm text-on-surface-variant" colspan="3">Loading…</td></tr>';

    const res = await fetch(membershipBaseUrl() + '/controllers/membership.php?action=getHistory&limit=50', { cache: 'no-store' });
    const data = await res.json().catch(() => null);
    if (!data || !data.success) {
        bodyEl.innerHTML = '<tr><td class="px-6 py-6 text-sm text-error" colspan="3">Failed to load history.</td></tr>';
        return;
    }
    const rows = Array.isArray(data.history) ? data.history : [];
    if (rows.length === 0) {
        bodyEl.innerHTML = '<tr><td class="px-6 py-6 text-sm text-on-surface-variant" colspan="3">No purchases yet.</td></tr>';
        return;
    }
    bodyEl.innerHTML = rows.map(historyRowHtml).join('');
}

function initHistoryModal() {
    const btn = document.getElementById('membershipViewHistoryBtn');
    const modal = document.getElementById('membershipHistoryModal');
    if (!btn || !modal) return;
    btn.addEventListener('click', async () => {
        if (typeof modal.showModal === 'function') modal.showModal();
        await loadHistory();
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadWalletBalance();
    const active = await loadStatus();
    await loadPlans(active);
    initHistoryModal();
});

