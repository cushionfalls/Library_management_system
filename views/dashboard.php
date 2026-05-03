<div class="space-y-8">
    <section>
        <h1 class="text-4xl font-extrabold tracking-tight text-on-surface mb-2 font-['Manrope']">Dashboard</h1>
        <p class="text-on-surface-variant">Track your books, membership, and reading activity.</p>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-surface-container-low rounded-xl p-6">
            <p class="text-xs uppercase tracking-widest text-on-surface-variant font-semibold mb-2">Wallet</p>
            <h3 id="walletBalance" class="text-3xl font-black text-primary">$0.00</h3>
        </div>
        <div class="bg-surface-container-low rounded-xl p-6">
            <p class="text-xs uppercase tracking-widest text-on-surface-variant font-semibold mb-2">My Books</p>
            <h3 id="myBooksCount" class="text-3xl font-black text-primary">0</h3>
        </div>
        <div class="bg-surface-container-low rounded-xl p-6">
            <p class="text-xs uppercase tracking-widest text-on-surface-variant font-semibold mb-2">Membership</p>
            <h3 id="membershipStatus" class="text-2xl font-black text-primary">Not Active</h3>
            <p id="membershipUntil" class="text-xs text-on-surface-variant mt-2"></p>
        </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white border border-outline-variant/20 rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold">Recent Books</h2>
                <a href="<?php echo APP_ROUTE; ?>?page=my-books" class="text-primary text-sm font-semibold hover:underline">Go to My Books</a>
            </div>
            <div id="dashboardBooksList" class="grid grid-cols-2 md:grid-cols-3 gap-4"></div>
        </div>
        <div class="bg-tertiary-fixed rounded-xl p-6 relative overflow-hidden">
            <span class="material-symbols-outlined absolute -right-2 -bottom-2 text-7xl opacity-10 rotate-12 pointer-events-none">format_quote</span>
            <h3 class="font-bold text-sm uppercase tracking-widest mb-3 text-on-tertiary-fixed">Quote</h3>
            <p class="text-on-tertiary-fixed font-bold italic" id="quoteText">Loading quote…</p>
            <p class="text-on-tertiary-fixed-variant text-xs mt-2 font-medium" id="quoteAuthor"></p>
        </div>
    </section>
</div>

<script>

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
    const [wallet, myBooks, membership] = await Promise.all([
        fetch('<?php echo APP_URL; ?>/controllers/wallet.php?action=getBalance').then(r => r.json()).catch(() => null),
        fetch('<?php echo APP_URL; ?>/controllers/books.php?action=my-books').then(r => r.json()).catch(() => null),
        fetch('<?php echo APP_URL; ?>/controllers/membership.php?action=getStatus').then(r => r.json()).catch(() => null),
    ]);

    if (wallet && wallet.success) {
        document.getElementById('walletBalance').textContent = window.formatUsdFromCents(wallet.balance || 0);
    }

    const books = (myBooks && myBooks.success && Array.isArray(myBooks.books)) ? myBooks.books : [];
    document.getElementById('myBooksCount').textContent = String(books.length);
    const booksList = document.getElementById('dashboardBooksList');
    if (booksList) {
        if (!books.length) {
            booksList.innerHTML = '<p class="text-sm text-on-surface-variant col-span-full">No books in your library yet.</p>';
        } else {
            booksList.innerHTML = books.slice(0, 6).map((b) => `
                <a href="<?php echo APP_ROUTE; ?>?page=books&book=${b.book_id}" class="block group">
                    <div class="aspect-[3/4] rounded-lg overflow-hidden mb-2 bg-surface-container">
                        <img src="${window.escapeHtml(b.cover_image_url || '')}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" alt="${window.escapeHtml(b.name || 'Book')}">
                    </div>
                    <p class="text-xs font-semibold truncate">${window.escapeHtml(b.name || 'Book')}</p>
                </a>
            `).join('');
        }
    }

    const statusEl = document.getElementById('membershipStatus');
    const untilEl = document.getElementById('membershipUntil');
    if (membership && membership.success && membership.active) {
        statusEl.textContent = membership.active.plan_name || 'Active';
        untilEl.textContent = 'Valid until: ' + window.formatDate(membership.active.ends_at);
    } else {
        statusEl.textContent = 'Not Active';
        untilEl.textContent = 'Activate membership to unlock more books.';
    }

    loadQuote();
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
