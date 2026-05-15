<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    body { font-family: 'Inter', system-ui, sans-serif; }
    h1, h2, h3, .brand-logo { font-family: 'Manrope', system-ui, sans-serif; }
</style>

<?php $userRole = $_SESSION['user_role'] ?? 'USER'; ?>

<div class="w-full space-y-12">

    <!-- Hero Section -->
    <section>
        <div class="relative overflow-hidden bg-primary rounded-[2rem] p-10 md:p-14 text-on-primary shadow-2xl flex flex-col md:flex-row justify-between items-center">
            <!-- decorative rings -->
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none">
                <svg fill="none" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="200" cy="200" r="180" stroke="white" stroke-width="2"></circle>
                    <circle cx="200" cy="200" r="140" stroke="white" stroke-width="1"></circle>
                    <circle cx="200" cy="200" r="100" stroke="white" stroke-width="0.5"></circle>
                </svg>
            </div>

            <div class="z-10 text-center md:text-left mb-8 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tighter mb-3 font-headline">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Reader'); ?>!</h1>
                <p class="text-on-primary-container text-opacity-90 max-w-lg text-lg font-medium">
                    Dive into your next adventure. Your library is waiting for you.
                </p>
            </div>

            <div class="z-10 flex flex-col sm:flex-row gap-4">
                <a href="<?php echo APP_ROUTE; ?>?page=books" class="bg-primary-container text-on-primary px-8 py-4 rounded-xl font-bold text-lg hover:brightness-110 active:scale-95 transition-all shadow-lg flex items-center gap-3">
                    <span class="material-symbols-outlined">explore</span>
                    Browse Catalog
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Grid -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <a href="<?php echo APP_ROUTE; ?>?page=wallet" class="bg-surface-container-low hover:bg-surface-container transition-colors rounded-2xl p-6 flex items-center justify-between group border border-outline-variant/10">
            <div>
                <p class="text-xs uppercase tracking-widest text-on-surface-variant font-bold mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm text-primary">account_balance_wallet</span> Wallet
                </p>
                <h3 id="walletBalance" class="text-3xl font-black text-primary">$0.00</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-primary-container/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        <a href="<?php echo APP_ROUTE; ?>?page=my-books" class="bg-surface-container-low hover:bg-surface-container transition-colors rounded-2xl p-6 flex items-center justify-between group border border-outline-variant/10">
            <div>
                <p class="text-xs uppercase tracking-widest text-on-surface-variant font-bold mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm text-primary">auto_stories</span> My Books
                </p>
                <h3 id="myBooksCount" class="text-3xl font-black text-primary">0</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-primary-container/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        <?php if ($userRole === 'USER'): ?>
        <a href="<?php echo APP_ROUTE; ?>?page=membership" class="bg-surface-container-low hover:bg-surface-container transition-colors rounded-2xl p-6 flex items-center justify-between group border border-outline-variant/10">
            <div>
                <p class="text-xs uppercase tracking-widest text-on-surface-variant font-bold mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm text-primary">card_membership</span> Membership
                </p>
                <h3 id="membershipStatus" class="text-2xl font-black text-primary mb-1">Not Active</h3>
                <p id="membershipUntil" class="text-xs text-on-surface-variant font-medium"></p>
            </div>
            <div class="w-12 h-12 rounded-full bg-primary-container/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        <?php endif; ?>
    </section>

    <!-- Bottom Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant/20 rounded-2xl p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-extrabold font-headline">Recent Books</h2>
                <a href="<?php echo APP_ROUTE; ?>?page=my-books" class="text-primary text-sm font-bold hover:underline flex items-center gap-1">
                    View All <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
            </div>
            <div id="dashboardBooksList" class="grid grid-cols-2 md:grid-cols-3 gap-6"></div>
        </div>

        <div class="space-y-8">
            <!-- Quote Card -->
            <div class="bg-tertiary-container rounded-2xl p-8 relative overflow-hidden shadow-sm">
                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-[120px] opacity-10 rotate-12 pointer-events-none text-on-tertiary-container">format_quote</span>
                <h3 class="font-bold text-sm uppercase tracking-widest mb-6 text-on-tertiary-container flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">lightbulb</span> Daily Inspiration
                </h3>
                <p class="text-on-tertiary-container font-medium italic text-lg leading-relaxed mb-4" id="quoteText">Loading quote…</p>
                <p class="text-tertiary-fixed-dim text-sm font-bold" id="quoteAuthor"></p>
            </div>

            <!-- Quick Action Card -->
            <div class="bg-secondary-container rounded-2xl p-8 relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <h3 class="font-bold text-xl mb-2 text-on-secondary-container font-headline">Need more books?</h3>
                    <p class="text-on-secondary-container/80 text-sm mb-6">Browse new books</p>
                    <a href="<?php echo APP_ROUTE; ?>?page=books" class="inline-flex items-center justify-center w-full bg-on-secondary-container text-secondary-container px-6 py-3 rounded-xl font-bold hover:opacity-90 transition-opacity">
                        View Books
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php if ($userRole === 'USER'): ?>
    <!-- AI Recommendation Section -->
    <section class="bg-surface-container-lowest border border-outline-variant/20 rounded-2xl p-8 shadow-sm">
        <div id="recommendationsPlaceholder" class="py-12 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-4">
                <span class="material-symbols-outlined text-3xl">auto_awesome</span>
            </div>
            <h3 class="text-lg font-bold text-on-surface">Discover your next favorite book</h3>
            <p class="text-sm text-on-surface-variant mb-6 max-w-xs">Our AI will analyze your reading patterns to find the perfect matches for you.</p>
            <button id="getAiRecommendationsBtn" type="button" class="inline-flex items-center justify-center gap-2 bg-primary text-white px-8 py-3 rounded-xl font-bold hover:brightness-110 transition-all shadow-md">
                <span class="material-symbols-outlined text-lg">magic_button</span>
                Get AI Recommendations
            </button>
        </div>

        <div id="recommendationsLoading" class="hidden py-16 text-center">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p class="mt-4 text-sm font-bold text-on-surface-variant">Generating recommendations...</p>
        </div>

        <div id="recommendationsError" class="hidden mb-4 rounded-xl border border-error/30 bg-error-container/30 text-on-error-container px-4 py-3 text-sm font-medium"></div>

        <div id="recommendationsGrid" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-4xl mx-auto"></div>
    </section>
    <?php endif; ?>
</div>

<script>
async function loadQuote() {
    const textEl = document.getElementById('quoteText');
    const authorEl = document.getElementById('quoteAuthor');
    if (!textEl || !authorEl) return;

    try {
        const res = await fetch('https://dummyjson.com/quotes/random', { cache: 'no-store' });
        const data = await res.json();
        const q = data.quote || '';
        const a = data.author || '';
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
    try {
        const wallet = await fetch('<?php echo APP_URL; ?>/controllers/wallet.php?action=getBalance').then(r => r.json()).catch(() => null);
        if (wallet && wallet.success) {
            document.getElementById('walletBalance').textContent = window.formatUsdFromCents(wallet.balance || 0);
        }

        const myBooks = await fetch('<?php echo APP_URL; ?>/controllers/books.php?action=my-books').then(r => r.json()).catch(() => null);
        const books = (myBooks && myBooks.success && Array.isArray(myBooks.books)) ? myBooks.books : [];
        document.getElementById('myBooksCount').textContent = String(books.length);
        const booksList = document.getElementById('dashboardBooksList');
        if (booksList) {
            if (!books.length) {
                booksList.innerHTML = '<div class="col-span-full py-8 text-center text-on-surface-variant"><span class="material-symbols-outlined text-4xl mb-3 text-outline/50">auto_stories</span><p class="font-medium">No books in your library yet.</p></div>';
            } else {
                booksList.innerHTML = books.slice(0, 6).map((b) => `
                    <a href="<?php echo APP_ROUTE; ?>?page=books&book=${b.book_id}" class="block group">
                        <div class="aspect-[3/4] rounded-xl overflow-hidden mb-3 shadow-sm border border-outline-variant/10 bg-surface-container">
                            <img src="${window.escapeHtml(b.cover_image_url || '')}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="${window.escapeHtml(b.name || 'Book')}">
                        </div>
                        <p class="text-sm font-bold text-on-surface truncate group-hover:text-primary transition-colors">${window.escapeHtml(b.name || 'Book')}</p>
                    </a>
                `).join('');
            }
        }

        const membership = await fetch('<?php echo APP_URL; ?>/controllers/membership.php?action=getStatus').then(r => r.json()).catch(() => null);
        const statusEl = document.getElementById('membershipStatus');
        const untilEl = document.getElementById('membershipUntil');
        if (membership && membership.success && membership.active) {
            statusEl.textContent = membership.active.plan_name || 'Active';
            untilEl.textContent = 'Valid until: ' + window.formatDate(membership.active.ends_at);
        } else {
            statusEl.textContent = 'Not Active';
            untilEl.textContent = 'Activate membership to unlock more books.';
        }
    } catch (err) {
        console.error('Dashboard load error:', err);
    }

    loadQuote();
    // Recommendations are now loaded on-demand via button click to save API quota.
}

function recommendationCard(book) {
    const title = window.escapeHtml(book.title || 'Untitled');
    const author = window.escapeHtml(book.author || 'Unknown Author');
    const genre = window.escapeHtml(book.genre || 'General');
    const score = Number(book.score || 0);
    const scoreBadge = isNaN(score) ? 'Match' : (score + '% Match');
    const rawCover = book.cover_image_url ? String(book.cover_image_url).trim() : '';
    const coverEsc = rawCover ? window.escapeHtml(rawCover) : 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80';
    const bookLink = book.book_id ? ('<?php echo htmlspecialchars(APP_ROUTE, ENT_QUOTES); ?>?page=books&book=' + encodeURIComponent(String(book.book_id))) : '#';

    return `
        <article class="group flex flex-col w-full" data-book-id="${book.book_id}">
            <div class="relative aspect-[2/3] rounded-lg overflow-hidden mb-3 transition-all duration-300 group-hover:-translate-y-1.5 group-hover:shadow-[0_12px_24px_-8px_rgba(56,0,191,0.2)] cursor-pointer">
                <a href="${bookLink}">
                    <img class="w-full h-full object-cover" alt="${title}" src="${coverEsc}" loading="lazy" />
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="px-2 py-0.5 bg-black/60 text-white text-[9px] font-bold rounded-md uppercase tracking-widest backdrop-blur-sm">${genre}</span>
                    </div>
                    <div class="absolute bottom-2 right-2">
                         <div class="bg-primary px-2 py-0.5 rounded-md flex items-center gap-1 shadow-sm border border-white/20">
                            <span class="material-symbols-outlined text-white text-[10px]" style="font-variation-settings:'FILL' 1;">auto_awesome</span>
                            <span class="text-[10px] font-black text-white">${scoreBadge}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="px-1 flex flex-col flex-1">
                <h3 class="text-sm font-bold text-on-surface leading-tight mb-0.5 group-hover:text-primary transition-colors line-clamp-2 cursor-pointer">
                    <a href="${bookLink}">${title}</a>
                </h3>
                <p class="text-[11px] text-on-surface-variant font-medium mb-1 truncate">${author}</p>
                <p class="mt-2 text-[10px] text-on-surface-variant font-medium italic line-clamp-2 opacity-80">${window.escapeHtml(book.reason || '')}</p>
                
                <div class="mt-4">
                    <a href="${bookLink}" class="block w-full text-center py-2 text-[9px] font-black bg-primary text-white rounded-md hover:opacity-90 transition-opacity uppercase tracking-tighter">Details</a>
                </div>
            </div>
        </article>
    `;
}

async function loadRecommendations(forceRefresh = false) {
    const grid = document.getElementById('recommendationsGrid');
    const loading = document.getElementById('recommendationsLoading');
    const error = document.getElementById('recommendationsError');
    const placeholder = document.getElementById('recommendationsPlaceholder');
    const getBtn = document.getElementById('getAiRecommendationsBtn');
    
    if (!grid || !loading || !error) return;

    error.classList.add('hidden');
    if (placeholder) placeholder.classList.add('hidden');
    grid.classList.add('hidden');
    loading.classList.remove('hidden');

    if (getBtn) {
        getBtn.disabled = true;
        getBtn.classList.add('opacity-60');
    }

    const statusText = loading.querySelector('p');
    const messages = [
        'Fetching your reading profile...',
        'Analyzing reading patterns...',
        'Comparing with trending titles...',
        'Consulting AI librarian...',
        'Finalizing recommendations...'
    ];
    let msgIdx = 0;
    const msgInterval = setInterval(() => {
        if (statusText && messages[msgIdx]) {
            statusText.textContent = messages[msgIdx];
            msgIdx++;
        }
        if (msgIdx >= messages.length) clearInterval(msgInterval);
    }, 1200);

    try {
        const url = '<?php echo APP_URL; ?>/controllers/recommendations.php?action=for-user' + (forceRefresh ? '&t=' + Date.now() : '');
        const res = await fetch(url, { cache: 'no-store' });
        const payload = await res.json();

        if (!payload.success) {
            throw new Error(payload.message || 'Unable to fetch recommendations');
        }

        const data = payload.data || {};
        const recs = Array.isArray(data.recommendations) ? data.recommendations.slice(0, 3) : [];
        if (!recs.length) {
            grid.innerHTML = `
                <div class="col-span-full py-10 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-5xl mb-3 text-outline/60">auto_stories</span>
                    <p class="font-semibold">No personalized recommendations yet.</p>
                    <p class="text-sm mt-1">Borrow or buy at least one book and we’ll tailor picks to your taste.</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = recs.map((item) => recommendationCard(item)).join('');
    } catch (err) {
        if (err.message === 'Buy some book to use this feature') {
            grid.innerHTML = `
                <div class="col-span-full py-10 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-5xl mb-3 text-primary/60">shopping_cart</span>
                    <p class="font-bold text-lg text-on-surface">Buy some book to use this feature</p>
                    <p class="text-sm mt-2 max-w-xs mx-auto">Once you have at least one book in your library, our AI can start analyzing your taste to find perfect matches.</p>
                    <a href="<?php echo APP_ROUTE; ?>?page=books" class="mt-6 inline-flex items-center gap-2 text-primary font-bold hover:underline">
                        Browse Books <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
            `;
        } else {
            error.textContent = err.message || 'Could not load AI recommendations.';
            error.classList.remove('hidden');
            grid.innerHTML = '';
        }
    } finally {
        clearInterval(msgInterval);
        loading.classList.add('hidden');
        grid.classList.remove('hidden');
        if (getBtn) {
            getBtn.disabled = false;
            getBtn.classList.remove('opacity-60');
            // Change button text after first load to suggest refreshing
            getBtn.innerHTML = '<span class="material-symbols-outlined text-lg">refresh</span> Refresh Recommendations';
        }
    }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('getAiRecommendationsBtn');
    if (btn) {
        btn.addEventListener('click', function () {
            loadRecommendations(true);
        });
    }
});
</script>
