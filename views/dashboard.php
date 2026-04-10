<script id="tailwind-dashboard-lumina">
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'surface-container-lowest': '#ffffff',
                'secondary-fixed-dim': '#bfc5e8',
                'on-secondary-fixed-variant': '#3f4563',
                'tertiary-fixed-dim': '#ffb59f',
                'surface-dim': '#ddd8e7',
                'primary-fixed-dim': '#c8bfff',
                'on-primary-fixed-variant': '#4200da',
                'on-tertiary-container': '#ffb6a1',
                'on-tertiary-fixed-variant': '#862300',
                'on-tertiary-fixed': '#3a0a00',
                'on-surface-variant': '#474557',
                'secondary-fixed': '#dde1ff',
                'surface-container-highest': '#e5e0f0',
                'surface-container-high': '#ebe6f5',
                surface: '#fdf8ff',
                'primary-fixed': '#e5deff',
                outline: '#787588',
                'surface-container': '#f1ebfb',
                background: '#fdf8ff',
                'on-primary': '#ffffff',
                'inverse-surface': '#312f3a',
                'on-secondary-container': '#595f7e',
                'on-tertiary': '#ffffff',
                'on-primary-fixed': '#190064',
                'on-error': '#ffffff',
                'on-secondary': '#ffffff',
                'outline-variant': '#c9c4da',
                'on-error-container': '#93000a',
                primary: '#3800bf',
                'on-primary-container': '#cac1ff',
                'error-container': '#ffdad6',
                secondary: '#575d7c',
                'surface-container-low': '#f7f1ff',
                'tertiary-fixed': '#ffdbd1',
                'on-background': '#1c1a25',
                error: '#ba1a1a',
                'secondary-container': '#d6dbff',
                tertiary: '#741d00',
                'on-surface': '#1c1a25',
                'tertiary-container': '#9c2a00',
                'surface-variant': '#e5e0f0',
                'inverse-on-surface': '#f4eefe',
                'primary-container': '#4f1bf1',
                'inverse-primary': '#c8bfff',
                'surface-tint': '#5a30fb',
                'on-secondary-fixed': '#131a35',
                'surface-bright': '#fdf8ff'
            },
            fontFamily: {
                headline: ['Manrope', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
                label: ['Inter', 'sans-serif']
            },
            borderRadius: { DEFAULT: '0.125rem', lg: '0.25rem', xl: '0.5rem', full: '0.75rem' }
        }
    }
};
</script>
<style>
    body.lumina-surface {
        background-color: #fdf8ff !important;
        color: #1c1a25;
        font-family: 'Inter', system-ui, sans-serif;
    }
    .lumina-dashboard .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        vertical-align: middle;
    }
    .lumina-dashboard .font-headline { font-family: 'Manrope', sans-serif; }
    .lumina-dashboard .font-body { font-family: 'Inter', sans-serif; }
</style>

<div class="lumina-dashboard font-body relative">
    <div class="fixed inset-0 pointer-events-none z-0 opacity-40">
        <div class="absolute inset-0 bg-[radial-gradient(#cac1ff_0.5px,transparent_0.5px)] [background-size:24px_24px]"></div>
    </div>

    <div class="relative z-10 grid grid-cols-12 gap-8">
        <div class="col-span-12 lg:col-span-9 space-y-10">
            <section>
                <h1 class="font-headline text-4xl font-extrabold tracking-tight text-on-surface mb-2">Dashboard</h1>
                <p class="text-on-surface-variant text-base">Welcome back! Manage your books and account activity here.</p>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-secondary-container rounded-lg text-on-secondary-container group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined">account_balance_wallet</span>
                        </div>
                    </div>
                    <p class="text-on-surface-variant text-sm font-medium mb-1 uppercase tracking-wider">Wallet Balance</p>
                    <h3 class="font-headline text-2xl font-extrabold text-on-surface" id="walletBalance">₹0</h3>
                </div>

                <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-primary-container/10 rounded-lg text-primary group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                        </div>
                    </div>
                    <p class="text-on-surface-variant text-sm font-medium mb-1 uppercase tracking-wider">Active Rentals</p>
                    <h3 class="font-headline text-2xl font-extrabold text-on-surface" id="activeRentals">0</h3>
                </div>

                <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-error-container/20 rounded-lg text-error group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined">priority_high</span>
                        </div>
                    </div>
                    <p class="text-on-surface-variant text-sm font-medium mb-1 uppercase tracking-wider">Unpaid Fines</p>
                    <h3 class="font-headline text-2xl font-extrabold text-on-surface" id="unpaidFines">₹0</h3>
                </div>

                <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-tertiary-container/10 rounded-lg text-tertiary group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined">library_books</span>
                        </div>
                    </div>
                    <p class="text-on-surface-variant text-sm font-medium mb-1 uppercase tracking-wider">Total Books</p>
                    <h3 class="font-headline text-2xl font-extrabold text-on-surface" id="totalBooks">0</h3>
                </div>
            </section>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <section class="bg-surface-container-lowest border border-outline-variant/15 p-8 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="font-headline text-xl font-bold tracking-tight text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">auto_stories</span>
                            Active Rentals
                        </h2>
                    </div>
                    <div id="activeRentalsList"></div>
                </section>

                <section class="bg-surface-container-lowest border border-outline-variant/15 p-8 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="font-headline text-xl font-bold tracking-tight text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">pending_actions</span>
                            Rent Requests
                        </h2>
                    </div>
                    <div id="rentRequestsDashboardList"></div>
                </section>
            </div>
        </div>

        <aside class="col-span-12 lg:col-span-3">
            <div class="bg-surface-container-low p-8 rounded-2xl lg:sticky lg:top-24">
                <h3 class="text-sm font-bold text-on-surface-variant uppercase tracking-widest mb-6">Quick Actions</h3>
                <div class="space-y-4">
                    <a href="<?php echo APP_URL; ?>/public/index.php?page=books" class="w-full flex items-center gap-3 p-4 bg-primary text-on-primary rounded-xl font-bold transition-all hover:bg-primary-container shadow-md active:scale-95 group">
                        <span class="material-symbols-outlined text-lg">search</span>
                        Browse Books
                    </a>
                    <a href="<?php echo APP_URL; ?>/public/index.php?page=wallet" class="w-full flex items-center gap-3 p-4 bg-[#10b981] text-white rounded-xl font-bold transition-all hover:brightness-110 shadow-md active:scale-95">
                        <span class="material-symbols-outlined text-lg">add_card</span>
                        Top Up Wallet
                    </a>
                    <a href="<?php echo APP_URL; ?>/public/index.php?page=fines" class="w-full flex items-center gap-3 p-4 bg-[#f59e0b] text-white rounded-xl font-bold transition-all hover:brightness-110 shadow-md active:scale-95">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        Pay Fines
                    </a>
                    <div class="pt-4 mt-4 border-t border-outline-variant/30">
                        <a href="<?php echo APP_URL; ?>/public/index.php?page=profile" class="flex items-center gap-3 p-4 text-on-surface-variant hover:text-primary hover:bg-surface-container-highest rounded-xl font-semibold transition-all">
                            <span class="material-symbols-outlined text-lg">person_edit</span>
                            Edit Profile
                        </a>
                    </div>
                </div>

                <div class="mt-12 p-6 bg-tertiary-fixed rounded-xl relative overflow-hidden">
                    <span class="material-symbols-outlined absolute -right-2 -bottom-2 text-7xl opacity-10 rotate-12 pointer-events-none">format_quote</span>
                    <p class="text-on-tertiary-fixed font-headline font-bold text-sm italic relative z-10 leading-relaxed" id="quoteText">Loading quote…</p>
                    <p class="text-on-tertiary-fixed-variant text-xs mt-2 font-medium relative z-10" id="quoteAuthor"></p>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
function escapeHtml(str) {
    if (str == null) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

function statusBadge(status) {
    if (status === 'APPROVED') return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">APPROVED</span>';
    if (status === 'REJECTED') return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">REJECTED</span>';
    return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">PENDING</span>';
}

async function loadQuote() {
    const textEl = document.getElementById('quoteText');
    const authorEl = document.getElementById('quoteAuthor');
    if (!textEl || !authorEl) return;

    try {
        const res = await fetch('<?php echo APP_URL; ?>/public/api/quote.php', { cache: 'no-store' });
        const data = await res.json();
        const q = data.q || '';
        const a = data.a || '';
        if (q) {
            textEl.textContent = '"' + q + '"';
            authorEl.textContent = a ? '— ' + a : '';
        } else {
            throw new Error('empty');
        }
    } catch (e) {
        textEl.textContent = '"The only thing that you absolutely have to know, is the location of the library."';
        authorEl.textContent = '— Albert Einstein';
    }
}

async function loadDashboard() {
    fetch('<?php echo APP_URL; ?>/controllers/wallet.php?action=getBalance')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('walletBalance').textContent = '₹' + data.balance;
            }
        });

    fetch('<?php echo APP_URL; ?>/controllers/transaction.php?action=getMyActiveRentals')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('activeRentals').textContent = data.rentals.length;
                let html = '';
                if (data.rentals.length === 0) {
                    html = `
                        <div class="flex flex-col items-center justify-center py-12 text-center bg-surface-container-low/50 rounded-lg border-2 border-dashed border-outline-variant/30">
                            <span class="material-symbols-outlined text-outline/40 text-5xl mb-4">sentiment_dissatisfied</span>
                            <p class="text-on-surface-variant text-base mb-4">No active rentals.</p>
                            <a href="<?php echo APP_URL; ?>/public/index.php?page=books" class="text-primary font-bold hover:underline decoration-2 underline-offset-4">Browse books</a>
                        </div>`;
                } else {
                    html = `
                        <div class="overflow-x-auto rounded-lg">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="text-on-surface-variant uppercase tracking-wider text-xs border-b border-outline-variant/20">
                                        <th class="py-3 pr-4 font-semibold">Book</th>
                                        <th class="py-3 pr-4 font-semibold">Due Date</th>
                                        <th class="py-3 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    data.rentals.forEach(rental => {
                        const dueDate = new Date(rental.due_date);
                        const today = new Date();
                        const isOverdue = dueDate < today;
                        const status = isOverdue
                            ? '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Overdue</span>'
                            : '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Active</span>';
                        html += `<tr class="border-b border-outline-variant/10 last:border-0">
                            <td class="py-3 pr-4 text-on-surface font-medium">${escapeHtml(rental.name)}</td>
                            <td class="py-3 pr-4 text-on-surface-variant">${dueDate.toLocaleDateString()}</td>
                            <td class="py-3">${status}</td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                }
                document.getElementById('activeRentalsList').innerHTML = html;
            }
        });

    fetch('<?php echo APP_URL; ?>/controllers/transaction.php?action=getMyRentalRequests')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            const container = document.getElementById('rentRequestsDashboardList');
            if (!container) return;

            if (!data.requests || data.requests.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 text-center bg-surface-container-low/50 rounded-lg border-2 border-dashed border-outline-variant/30">
                        <span class="material-symbols-outlined text-outline/40 text-5xl mb-4">inbox_customize</span>
                        <p class="text-on-surface-variant text-base">No rent requests yet.</p>
                    </div>`;
                return;
            }

            let html = `
                <div class="overflow-x-auto rounded-lg">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-on-surface-variant uppercase tracking-wider text-xs border-b border-outline-variant/20">
                                <th class="py-3 pr-4 font-semibold">Book</th>
                                <th class="py-3 pr-4 font-semibold">Status</th>
                                <th class="py-3 font-semibold">Requested</th>
                            </tr>
                        </thead>
                        <tbody>`;
            data.requests.slice(0, 5).forEach(req => {
                html += `<tr class="border-b border-outline-variant/10 last:border-0">
                    <td class="py-3 pr-4 text-on-surface font-medium">${escapeHtml(req.book_name)}</td>
                    <td class="py-3 pr-4">${statusBadge(req.status)}</td>
                    <td class="py-3 text-on-surface-variant">${escapeHtml(req.requested_days)} days</td>
                </tr>`;
            });
            html += '</tbody></table></div>';
            container.innerHTML = html;
        });

    fetch('<?php echo APP_URL; ?>/controllers/fine.php?action=getMyFines')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('unpaidFines').textContent = '₹' + data.total_unpaid;
            }
        });

    fetch('<?php echo APP_URL; ?>/controllers/transaction.php?action=getMyTransactions')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalBooks').textContent = data.transactions.length;
            }
        });

    loadQuote();
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
