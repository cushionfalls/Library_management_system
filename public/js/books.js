(function () {
    const state = {
        page: 1,
        perPage: 12,
        search: '',
        genre: 'ALL',
        availableOnly: true,
        sort: 'recent',
        view: 'grid',
        totalPages: 1,
        genresLoaded: false,
        catalogById: {}
    };

    function esc(value) {
        const raw = String(value ?? '');
        return raw
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatCurrency(value) {
        if (value == null || value === '') return 'N/A';
        const amount = Number(value || 0);
        return '₹' + new Intl.NumberFormat('en-IN').format(amount);
    }

    function formatDate(value) {
        if (!value) return '';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return '';
        return d.toLocaleDateString();
    }

    function apiUrl(action, params) {
        const qp = new URLSearchParams(params || {});
        qp.set('action', action);
        return (window.BROWSE_API_URL || '') + '?' + qp.toString();
    }

    function fallbackCover() {
        return 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80';
    }

    async function loadCatalog() {
        const grid = document.getElementById('browseCatalogGrid');
        const list = document.getElementById('browseCatalogList');
        if (!grid || !list) return;

        grid.innerHTML = '<p class="col-span-full text-center text-on-surface-variant">Loading books...</p>';
        list.innerHTML = '';

        try {
            const response = await fetch(apiUrl('catalog', {
                page: state.page,
                per_page: state.perPage,
                search: state.search,
                genre: state.genre,
                available_only: state.availableOnly ? 1 : 0,
                sort: state.sort
            }), { cache: 'no-store' });

            const result = await response.json();
            if (!result || !result.success || !result.data) throw new Error('Failed to fetch catalog');

            const data = result.data;
            const items = data.items || [];
            state.totalPages = Math.max(1, Number(data.meta && data.meta.total_pages) || 1);
            state.catalogById = {};
            items.forEach((item) => {
                state.catalogById[String(item.id)] = item;
            });

            renderGenres(data.genres || []);
            renderGrid(items);
            renderList(items);
            renderPagination();
            applyView();
        } catch (error) {
            grid.innerHTML = '<p class="col-span-full text-center text-red-600">Unable to load books right now.</p>';
        }
    }

    function renderGenres(genres) {
        if (state.genresLoaded) return;
        const select = document.getElementById('browseGenreSelect');
        if (!select) return;

        const options = genres.map((genre) => `<option value="${esc(genre.value)}">${esc(genre.label)}</option>`).join('');
        select.innerHTML = options || '<option value="ALL">All Categories</option>';
        select.value = state.genre;
        state.genresLoaded = true;
    }

    function renderGrid(items) {
        const grid = document.getElementById('browseCatalogGrid');
        if (!grid) return;

        if (!items.length) {
            grid.innerHTML = '<p class="col-span-full text-center text-on-surface-variant">No books found for your filters.</p>';
            return;
        }

        grid.innerHTML = items.map((book) => {
            const cover = esc(book.cover_image_url || fallbackCover());
            return `
                <article class="group flex flex-col cursor-pointer">
                    <div class="relative aspect-[3/4] rounded-xl overflow-hidden mb-5 transition-all duration-300 group-hover:-translate-y-2 group-hover:shadow-[0_20px_40px_-15px_rgba(56,0,191,0.15)]">
                        <img class="w-full h-full object-cover" alt="${esc(book.name)}" src="${cover}" />
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 bg-secondary-container text-on-secondary-container text-xs font-bold rounded-full uppercase tracking-widest backdrop-blur-md bg-opacity-80">${esc(book.genre_label)}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 mb-2">
                        <span class="material-symbols-outlined text-amber-500 text-sm" style="font-variation-settings:'FILL' 1;">star</span>
                        <span class="text-sm font-bold text-on-surface">${esc(Number(book.rating || 0).toFixed(1))}</span>
                        <span class="text-xs text-on-surface-variant ml-auto">${book.number_of_copies > 0 ? 'Available' : 'Out of stock'}</span>
                    </div>
                    <h3 class="text-lg font-bold text-on-surface leading-tight mb-1 group-hover:text-primary transition-colors">${esc(book.name)}</h3>
                    <p class="text-sm text-on-surface-variant font-medium mb-2">${esc(book.author_display)}</p>
                    <p class="text-sm font-semibold text-primary mb-4">${formatCurrency(book.price)}</p>
                    <a class="text-primary text-sm font-bold hover:underline decoration-2 underline-offset-4 inline-flex items-center gap-1" href="${window.BROWSE_PAGE_URL}&book=${encodeURIComponent(book.id)}" data-book-id="${book.id}">
                        View Details <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </article>
            `;
        }).join('');
    }

    function renderList(items) {
        const list = document.getElementById('browseCatalogList');
        if (!list) return;

        if (!items.length) {
            list.innerHTML = '<p class="text-center text-on-surface-variant">No books found for your filters.</p>';
            return;
        }

        list.innerHTML = items.map((book) => {
            const cover = esc(book.cover_image_url || fallbackCover());
            return `
                <article class="bg-white border border-outline-variant/30 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row gap-4">
                    <img class="w-full sm:w-24 h-40 sm:h-32 rounded-lg object-cover shrink-0" src="${cover}" alt="${esc(book.name)}" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-lg font-bold text-on-surface">${esc(book.name)}</h3>
                            <span class="text-primary font-bold whitespace-nowrap">${formatCurrency(book.price)}</span>
                        </div>
                        <p class="text-sm text-on-surface-variant mt-1">${esc(book.author_display)} • ${esc(book.genre_label)}</p>
                        <p class="text-sm text-on-surface-variant mt-2 line-clamp-2">${esc(book.description || 'No description available.')}</p>
                        <div class="flex items-center gap-4 mt-3 text-xs text-on-surface-variant">
                            <span>Rating: ${esc(Number(book.rating || 0).toFixed(1))}</span>
                            <span>Copies: ${esc(book.number_of_copies)}</span>
                        </div>
                    </div>
                    <div class="sm:self-center">
                        <a class="text-primary text-sm font-bold hover:underline decoration-2 underline-offset-4 inline-flex items-center gap-1" href="${window.BROWSE_PAGE_URL}&book=${encodeURIComponent(book.id)}" data-book-id="${book.id}">
                            View Details <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                </article>
            `;
        }).join('');
    }

    function renderPagination() {
        const container = document.getElementById('browsePagination');
        if (!container) return;

        if (state.totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let pages = '';
        for (let p = 1; p <= state.totalPages; p++) {
            if (p > 5 && p !== state.totalPages && p !== state.page) continue;
            if (p > 5 && p === state.totalPages && state.totalPages > 6) pages += '<span class="px-2 text-on-surface-variant">...</span>';
            pages += `<button type="button" data-page="${p}" class="w-10 h-10 flex items-center justify-center rounded-lg ${p === state.page ? 'bg-primary text-white font-bold' : 'hover:bg-surface-container-low font-medium'}">${p}</button>`;
        }

        container.innerHTML = `
            <button type="button" id="browsePrevPage" class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container-low transition-colors ${state.page <= 1 ? 'opacity-30 pointer-events-none' : ''}">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <div class="flex items-center gap-2">${pages}</div>
            <button type="button" id="browseNextPage" class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container-low transition-colors ${state.page >= state.totalPages ? 'opacity-30 pointer-events-none' : ''}">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        `;

        container.querySelectorAll('[data-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.page = Number(btn.getAttribute('data-page')) || 1;
                loadCatalog();
            });
        });

        const prev = document.getElementById('browsePrevPage');
        const next = document.getElementById('browseNextPage');
        if (prev) prev.addEventListener('click', () => state.page > 1 && (state.page--, loadCatalog()));
        if (next) next.addEventListener('click', () => state.page < state.totalPages && (state.page++, loadCatalog()));
    }

    function applyView() {
        const grid = document.getElementById('browseCatalogGrid');
        const list = document.getElementById('browseCatalogList');
        const gridBtn = document.getElementById('browseGridBtn');
        const listBtn = document.getElementById('browseListBtn');
        if (!grid || !list || !gridBtn || !listBtn) return;

        const isGrid = state.view === 'grid';
        grid.classList.toggle('hidden', !isGrid);
        list.classList.toggle('hidden', isGrid);
        gridBtn.className = isGrid ? 'px-3 py-1.5 bg-surface-container-lowest shadow-sm rounded-md text-primary' : 'px-3 py-1.5 text-on-surface-variant hover:text-primary transition-colors';
        listBtn.className = !isGrid ? 'px-3 py-1.5 bg-surface-container-lowest shadow-sm rounded-md text-primary' : 'px-3 py-1.5 text-on-surface-variant hover:text-primary transition-colors';
    }

    function debounce(fn, wait) {
        let timeout;
        return function debounced(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function setBookParam(bookId) {
        const url = new URL(window.location.href);
        if (bookId) url.searchParams.set('book', String(bookId));
        else url.searchParams.delete('book');
        window.history.replaceState({}, '', url.toString());
    }

    async function openBookDetail(bookId) {
        const overlay = document.getElementById('bookDetailOverlay');
        if (!overlay) return;

        try {
            const response = await fetch(apiUrl('detail', { book_id: bookId }), { cache: 'no-store' });
            const result = await response.json();
            if (!result || !result.success || !result.data) {
                if (window.showToast) window.showToast((result && result.message) || 'Book details unavailable', 'error');
                return;
            }

            const book = result.data;
            document.getElementById('bookDetailCover').src = book.cover_image_url || fallbackCover();
            document.getElementById('bookDetailRating').textContent = Number(book.rating || 0).toFixed(1);
            document.getElementById('bookDetailCopies').textContent = String(book.number_of_copies || 0);
            document.getElementById('bookDetailTagGenre').textContent = book.genre_label || 'Genre';
            document.getElementById('bookDetailTitle').textContent = book.name || 'Untitled';
            document.getElementById('bookDetailAuthor').textContent = 'by ' + (book.author_display || 'Unknown Author');
            const catalogItem = state.catalogById[String(book.id || '')] || {};
            const publisherValue = String(book.publisher_display || book.publisher || catalogItem.publisher_display || catalogItem.publisher || '').trim();
            document.getElementById('bookDetailPublisher').textContent = 'Publisher: ' + (publisherValue || 'Unknown Publisher');
            document.getElementById('bookDetailSynopsis').textContent = book.synopsis || 'No synopsis available.';
            document.getElementById('bookDetailRentPrice').textContent = formatCurrency(book.online_rent_price);
            document.getElementById('bookDetailBuyPrice').textContent = formatCurrency(book.price);
            document.getElementById('bookDetailOnlinePrice').textContent = formatCurrency(book.online_buy_price);
            document.getElementById('bookDetailReviewsCount').textContent = (book.total_reviews || 0) + ' reviews';
            document.getElementById('bookReviewBookId').value = String(book.id || '');

            renderReviews(book.reviews || []);
            configureReviewForm();

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setBookParam(bookId);
        } catch (error) {
            if (window.showToast) window.showToast('Failed to open details', 'error');
        }
    }

    function renderReviews(reviews) {
        const list = document.getElementById('bookDetailReviewsList');
        if (!list) return;
        if (!reviews.length) {
            list.innerHTML = '<p class="text-sm text-on-surface-variant">No reviews yet. Be the first to review this book.</p>';
            return;
        }

        list.innerHTML = reviews.map((review) => {
            const stars = Array.from({ length: 5 }).map((_, i) => {
                const fill = i < Number(review.rating || 0) ? "style=\"font-variation-settings:'FILL' 1;\"" : '';
                return `<span class="material-symbols-outlined text-xs text-primary" ${fill}>star</span>`;
            }).join('');
            const avatar = esc(review.profile_image_url || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80');
            return `
                <article class="flex gap-4">
                    <img class="w-10 h-10 rounded-full object-cover" src="${avatar}" alt="${esc(review.reviewer_name || 'Reader')}" />
                    <div class="flex-1">
                        <div class="flex justify-between mb-1">
                            <span class="font-bold text-sm">${esc(review.reviewer_name || 'Reader')}</span>
                            <span class="text-xs text-outline">${esc(formatDate(review.created_at))}</span>
                        </div>
                        <div class="flex mb-2">${stars}</div>
                        <p class="text-sm text-on-surface-variant italic">"${esc(review.review || '')}"</p>
                    </div>
                </article>
            `;
        }).join('');
    }

    function configureReviewForm() {
        const form = document.getElementById('bookReviewForm');
        const textarea = document.getElementById('bookReviewText');
        const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
        if (!form || !textarea || !submitBtn) return;

        if (!window.BROWSE_IS_LOGGED_IN) {
            textarea.disabled = true;
            textarea.placeholder = 'Please login to submit a review.';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
        } else {
            textarea.disabled = false;
            textarea.placeholder = 'Share your thoughts on this title...';
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }

    async function submitReview(event) {
        event.preventDefault();
        if (!window.BROWSE_IS_LOGGED_IN) {
            if (window.showToast) window.showToast('Please login to submit a review', 'warning');
            return;
        }
        const form = document.getElementById('bookReviewForm');
        if (!form) return;
        const formData = new FormData(form);

        const response = await fetch(apiUrl('add-review'), { method: 'POST', body: new URLSearchParams(formData) });
        const result = await response.json().catch(() => null);
        if (!result || !result.success) {
            if (window.showToast) window.showToast((result && result.message) || 'Failed to submit review', 'error');
            return;
        }
        if (window.showToast) window.showToast(result.message || 'Review submitted', 'success');
        document.getElementById('bookReviewText').value = '';
        await openBookDetail(formData.get('book_id'));
        await loadCatalog();
    }

    function closeBookDetail() {
        const overlay = document.getElementById('bookDetailOverlay');
        if (!overlay) return;
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        setBookParam('');
    }

    function bindEvents() {
        const searchInput = document.getElementById('browseSearchInput');
        const genreSelect = document.getElementById('browseGenreSelect');
        const sortSelect = document.getElementById('browseSortSelect');
        const availableBtn = document.getElementById('browseAvailableToggle');
        const availableSwitch = document.getElementById('browseAvailableSwitch');
        const gridBtn = document.getElementById('browseGridBtn');
        const listBtn = document.getElementById('browseListBtn');
        const closeBtn = document.getElementById('bookDetailCloseBtn');
        const backdrop = document.getElementById('bookDetailBackdrop');
        const reviewForm = document.getElementById('bookReviewForm');

        if (searchInput) {
            const onSearch = debounce(() => {
                state.search = searchInput.value.trim();
                state.page = 1;
                loadCatalog();
            }, 300);
            searchInput.addEventListener('input', onSearch);
        }

        if (genreSelect) genreSelect.addEventListener('change', () => { state.genre = genreSelect.value || 'ALL'; state.page = 1; loadCatalog(); });
        if (sortSelect) sortSelect.addEventListener('change', () => { state.sort = sortSelect.value || 'recent'; state.page = 1; loadCatalog(); });
        if (availableBtn && availableSwitch) {
            availableBtn.addEventListener('click', () => {
                state.availableOnly = !state.availableOnly;
                availableSwitch.classList.toggle('is-on', state.availableOnly);
                state.page = 1;
                loadCatalog();
            });
        }
        if (gridBtn) gridBtn.addEventListener('click', () => { state.view = 'grid'; applyView(); });
        if (listBtn) listBtn.addEventListener('click', () => { state.view = 'list'; applyView(); });
        if (closeBtn) closeBtn.addEventListener('click', closeBookDetail);
        if (backdrop) backdrop.addEventListener('click', closeBookDetail);
        if (reviewForm) reviewForm.addEventListener('submit', submitReview);

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[data-book-id]');
            if (!link) return;
            event.preventDefault();
            const bookId = Number(link.getAttribute('data-book-id') || 0);
            if (bookId > 0) openBookDetail(bookId);
        });
    }

    function openFromUrlIfAny() {
        const url = new URL(window.location.href);
        const bookId = Number(url.searchParams.get('book') || 0);
        if (bookId > 0) openBookDetail(bookId);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('browseCatalogGrid')) return;
        bindEvents();
        loadCatalog().then(openFromUrlIfAny);
    });
})();
