(function () {
    const state = {
        page: 1,
        perPage: 12,
        search: '',
        genre: 'ALL',
        sort: 'recent',
        view: 'grid',
        totalPages: 1,
        genresLoaded: false,
        catalogById: {},
        currentBookId: 0
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

    // We use the global formatUsdFromCents(cents) from main.js
    // which correctly divides by 100 and formats as USD.

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

    function setBookParam(id) {
        const url = new URL(window.location.href);
        if (id) {
            url.searchParams.set('book', id);
        } else {
            url.searchParams.delete('book');
        }
        window.history.pushState({ bookId: id }, '', url.toString());
    }

    function fallbackCover() {
        return 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80';
    }

    async function handleBuyNow(bookId, buttonEl = null) {
        if (!bookId) return;
        if (!window.BROWSE_IS_LOGGED_IN) {
            window.showToast?.('Please login to buy books', 'warning');
            return;
        }

        const originalText = buttonEl ? buttonEl.textContent : '';
        if (buttonEl) {
            buttonEl.disabled = true;
            buttonEl.textContent = 'Order Confirming...';
        }

        const body = new URLSearchParams();
        body.set('book_id', String(bookId));

        try {
            const response = await fetch(apiUrl('purchase-online'), { method: 'POST', body });
            const result = await response.json().catch(() => null);

            if (!result || !result.success) {
                window.showToast?.((result && result.message) || 'Unable to purchase book', 'error');
                if (buttonEl) {
                    buttonEl.disabled = false;
                    buttonEl.textContent = originalText;
                }
                return;
            }

            window.showToast?.(result.message || 'Book purchased', 'success');
            if (window.MY_BOOKS_PAGE_URL) {
                setTimeout(() => { window.location.href = window.MY_BOOKS_PAGE_URL; }, 300);
            }
        } catch (error) {
            window.showToast?.('An error occurred during purchase', 'error');
            if (buttonEl) {
                buttonEl.disabled = false;
                buttonEl.textContent = originalText;
            }
        }
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
            const priceCents = Number(book.price > 0 ? book.price : (book.online_buy_price || 0));
            const priceDisplay = priceCents > 0 ? formatUsdFromCents(priceCents) : 'FREE';

            return `
                <article class="group flex flex-col" data-book-id="${book.id}">
                    <div class="relative aspect-[2/3] rounded-lg overflow-hidden mb-3 transition-all duration-300 group-hover:-translate-y-1.5 group-hover:shadow-xl cursor-pointer" data-book-id="${book.id}" data-action="details">
                        <img class="w-full h-full object-cover" alt="${esc(book.name)}" src="${cover}" loading="lazy" />
                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                            <span class="px-2 py-0.5 bg-black/60 text-white text-[9px] font-bold rounded-md uppercase tracking-widest backdrop-blur-sm">${esc(book.genre_label)}</span>
                        </div>
                        <div class="absolute bottom-2 right-2">
                             <div class="bg-surface-container-lowest/90 backdrop-blur-sm px-1.5 py-0.5 rounded-md flex items-center gap-0.5 shadow-sm">
                                <span class="material-symbols-outlined text-amber-500 text-[10px]" style="font-variation-settings:'FILL' 1;">star</span>
                                <span class="text-[10px] font-black text-on-surface">${esc(Number(book.rating || 0).toFixed(1))}</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-1 flex flex-col flex-1">
                        <h3 class="text-sm font-bold text-on-surface leading-tight mb-0.5 group-hover:text-primary transition-colors line-clamp-2 cursor-pointer" data-book-id="${book.id}" data-action="details">${esc(book.name)}</h3>
                        <p class="text-[11px] text-on-surface-variant font-medium mb-1 truncate">${esc(book.author_display)}</p>
                        <p class="text-xs font-bold text-primary mb-3">${priceDisplay}</p>
                        
                        <div class="mt-auto flex gap-1.5">
                            ${(book.user_access_type || window.USER_ROLE === 'ADMIN' || window.USER_ROLE === 'LIBRARIAN') ? `
                                <button class="flex-1 py-2 text-[9px] font-black bg-success-container text-on-success-container rounded-md hover:opacity-90 transition-opacity uppercase tracking-tighter" onclick="window.location.href='${window.MY_BOOKS_PAGE_URL}${ (window.USER_ROLE === 'ADMIN' || window.USER_ROLE === 'LIBRARIAN') ? '&open_reader=' + book.id : '' }'">${ (window.USER_ROLE === 'ADMIN' || window.USER_ROLE === 'LIBRARIAN') ? 'Read Now' : 'Already Owned' }</button>
                            ` : `
                                <button class="flex-1 py-2 text-[9px] font-black bg-primary text-white rounded-md hover:opacity-90 transition-opacity uppercase tracking-tighter" data-action="buy" data-book-id="${book.id}">Buy Now</button>
                            `}
                            <button class="flex-1 py-2 text-[9px] font-black bg-surface-container-high text-primary rounded-md hover:opacity-80 transition-colors uppercase tracking-tighter" data-action="details" data-book-id="${book.id}">Details</button>
                        </div>
                    </div>
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
                <article class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row gap-4">
                    <img class="w-full sm:w-24 h-40 sm:h-32 rounded-lg object-cover shrink-0" src="${cover}" alt="${esc(book.name)}" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-lg font-bold text-on-surface">${esc(book.name)}</h3>
                            <span class="text-primary font-bold whitespace-nowrap">${formatUsdFromCents(book.price > 0 ? book.price : (book.online_buy_price || 0))}</span>
                        </div>
                        <p class="text-sm text-on-surface-variant mt-1">${esc(book.author_display)} • ${esc(book.genre_label)}</p>
                        <p class="text-sm text-on-surface-variant mt-2 line-clamp-2">${esc(book.description || 'No description available.')}</p>
                        <div class="flex items-center gap-4 mt-3 text-xs text-on-surface-variant">
                            <span>Rating: ${esc(Number(book.rating || 0).toFixed(1))}</span>
                        </div>
                    </div>
                    <div class="sm:self-center flex flex-col gap-2 min-w-[120px]">
                        ${(book.user_access_type || window.USER_ROLE === 'ADMIN' || window.USER_ROLE === 'LIBRARIAN') ? `
                            <button class="w-full py-2 text-[10px] font-black bg-success-container text-on-success-container rounded-lg hover:opacity-90 transition-all uppercase tracking-widest" onclick="window.location.href='${window.MY_BOOKS_PAGE_URL}${ (window.USER_ROLE === 'ADMIN' || window.USER_ROLE === 'LIBRARIAN') ? '&open_reader=' + book.id : '' }'">${ (window.USER_ROLE === 'ADMIN' || window.USER_ROLE === 'LIBRARIAN') ? 'Read Now' : 'Already Owned' }</button>
                        ` : `
                            <button class="w-full py-2 text-[10px] font-black bg-primary text-white rounded-lg hover:opacity-90 transition-all uppercase tracking-widest" data-action="buy" data-book-id="${book.id}">Buy Now</button>
                        `}
                        <button class="w-full py-2 text-[10px] font-black bg-surface-container-highest text-on-surface rounded-lg hover:bg-outline-variant/30 transition-all uppercase tracking-widest border border-outline-variant/30" data-action="details" data-book-id="${book.id}">Details</button>
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
            document.getElementById('bookDetailLanguage').textContent = book.language || 'English';
            document.getElementById('bookDetailTagGenre').textContent = book.genre_label || 'Genre';
            document.getElementById('bookDetailTitle').textContent = book.name || 'Untitled';
            document.getElementById('bookDetailAuthor').textContent = 'by ' + (book.author_display || 'Unknown Author');
            const catalogItem = state.catalogById[String(book.id || '')] || {};
            const publisherValue = String(book.publisher_display || book.publisher || catalogItem.publisher_display || catalogItem.publisher || '').trim();
            document.getElementById('bookDetailPublisher').textContent = 'Publisher: ' + (publisherValue || 'Unknown Publisher');
            document.getElementById('bookDetailSynopsis').textContent = book.synopsis || 'No synopsis available.';
            document.getElementById('bookDetailOnlinePrice').textContent = formatUsdFromCents(book.online_buy_price);
            document.getElementById('bookDetailReviewsCount').textContent = (book.total_reviews || 0) + ' reviews';
            document.getElementById('bookReviewBookId').value = String(book.id || '');
            state.currentBookId = Number(book.id || 0);
            const buyBtn = document.getElementById('bookDetailBuyOnlineBtn');
            const buyPriceEl = document.getElementById('bookDetailOnlinePrice');
            const membershipBtn = document.getElementById('bookDetailMembershipAccessBtn');
            const accessType = String(book.user_access && book.user_access.access_type ? book.user_access.access_type : '').toUpperCase();
            const alreadyOwned = accessType === 'OWNED';
            const alreadyMembership = accessType === 'MEMBERSHIP';
            const alreadyHasAccess = alreadyOwned || alreadyMembership;
            const accessSection = document.getElementById('bookDetailAccessSection');
            const ownedSection = document.getElementById('bookDetailOwnedSection');
            const ownedText = document.getElementById('bookDetailOwnedText');
            const goToMyBooksBtn = document.getElementById('bookDetailGoToMyBooksBtn');

            if (alreadyHasAccess) {
                if (accessSection) accessSection.classList.add('hidden');
                if (ownedSection) {
                    ownedSection.classList.remove('hidden');
                    if (ownedText) {
                        ownedText.textContent = alreadyOwned
                            ? 'You have full ownership of this book.'
                            : 'This book is unlocked via your active membership.';
                    }
                    if (goToMyBooksBtn) {
                        const isAdminOrLibrarian = window.USER_ROLE === 'ADMIN' || window.USER_ROLE === 'LIBRARIAN';
                        if (isAdminOrLibrarian) {
                            goToMyBooksBtn.textContent = 'Read Now';
                            goToMyBooksBtn.onclick = () => { window.location.href = window.MY_BOOKS_PAGE_URL + '&open_reader=' + book.id; };
                        } else {
                            goToMyBooksBtn.textContent = 'Go to My Books';
                            goToMyBooksBtn.onclick = () => { window.location.href = window.MY_BOOKS_PAGE_URL; };
                        }
                    }
                }
            } else {
                if (ownedSection) ownedSection.classList.add('hidden');
                if (accessSection) accessSection.classList.remove('hidden');

                if (buyBtn && buyPriceEl) {
                    buyBtn.textContent = 'Buy with Wallet';
                    buyBtn.classList.replace('bg-surface-container-lowest/90', 'bg-surface-container-lowest');
                    buyBtn.onclick = null;
                    buyPriceEl.textContent = formatUsdFromCents(book.online_buy_price);
                }
                if (membershipBtn) {
                    membershipBtn.textContent = 'Grant Access';
                    membershipBtn.onclick = null;
                }
            }

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
            const ownReview = Number(review.user_id || 0) === Number(window.BROWSE_CURRENT_USER_ID || 0);
            const editedBadge = review.is_edited ? '<span class="text-[11px] text-outline ml-2">(edited)</span>' : '';
            const stars = Array.from({ length: 5 }).map((_, i) => {
                const fill = i < Number(review.rating || 0) ? "style=\"font-variation-settings:'FILL' 1;\"" : '';
                return `<span class="material-symbols-outlined text-xs text-primary" ${fill}>star</span>`;
            }).join('');
            const avatar = esc(review.profile_image_url || 'https://images.unsplash.com/photo-1511367461989-f85a21fda167?q=80&w=1631&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            const ownerActions = ownReview
                ? `<div class="mt-3 flex gap-3">
                        <button type="button" class="text-xs font-semibold text-primary hover:underline" data-review-action="edit" data-review-id="${esc(review.id)}" data-review-rating="${esc(review.rating)}" data-review-text="${esc(review.review || '')}">Edit</button>
                        <button type="button" class="text-xs font-semibold text-red-600 hover:underline" data-review-action="delete" data-review-id="${esc(review.id)}">Delete</button>
                   </div>`
                : '';
            return `
                <article class="flex gap-4">
                    <img class="w-10 h-10 rounded-full object-cover" src="${avatar}" alt="${esc(review.reviewer_name || 'Reader')}" />
                    <div class="flex-1">
                        <div class="flex justify-between mb-1">
                            <span class="font-bold text-sm">${esc(review.reviewer_name || 'Reader')}</span>
                            <span class="text-xs text-outline">${esc(formatDate(review.created_at))}${editedBadge}</span>
                        </div>
                        <div class="flex mb-2">${stars}</div>
                        <p class="text-sm text-on-surface-variant italic">"${esc(review.review || '')}"</p>
                        ${ownerActions}
                    </div>
                </article>
            `;
        }).join('');
    }

    function configureReviewForm() {
        const form = document.getElementById('bookReviewForm');
        const textarea = document.getElementById('bookReviewText');
        const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
        const cancelEditBtn = document.getElementById('bookReviewCancelEdit');
        if (!form || !textarea || !submitBtn) return;

        if (!window.BROWSE_IS_LOGGED_IN) {
            textarea.disabled = true;
            textarea.placeholder = 'Please login to submit a review.';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            if (cancelEditBtn) cancelEditBtn.classList.add('hidden');
        } else if (!window.BROWSE_IS_VERIFIED) {
            textarea.disabled = true;
            textarea.placeholder = 'Please verify your email to submit a review.';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            if (cancelEditBtn) cancelEditBtn.classList.add('hidden');
        } else {
            textarea.disabled = false;
            textarea.placeholder = 'Share your thoughts on this title...';
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }

    function resetReviewForm() {
        const form = document.getElementById('bookReviewForm');
        if (!form) return;
        const reviewIdInput = document.getElementById('bookReviewId');
        const ratingInput = document.getElementById('bookReviewRating');
        const textInput = document.getElementById('bookReviewText');
        const submitBtn = form.querySelector('button[type="submit"]');
        const cancelEditBtn = document.getElementById('bookReviewCancelEdit');

        if (reviewIdInput) reviewIdInput.value = '';
        if (ratingInput) {
            ratingInput.value = '5';
            updateReviewStars(5);
        }
        if (textInput) textInput.value = '';
        if (submitBtn) submitBtn.textContent = 'Submit Review';
        if (cancelEditBtn) cancelEditBtn.classList.add('hidden');
    }

    function updateReviewStars(val) {
        const starContainer = document.getElementById('bookReviewStars');
        if (!starContainer) return;
        const stars = starContainer.querySelectorAll('[data-rating]');
        stars.forEach(star => {
            const r = Number(star.getAttribute('data-rating'));
            if (r <= val) {
                star.style.fontVariationSettings = "'FILL' 1";
                star.classList.remove('text-outline');
                star.classList.add('text-primary');
            } else {
                star.style.fontVariationSettings = "'FILL' 0";
                star.classList.remove('text-primary');
                star.classList.add('text-outline');
            }
        });
    }

    function initReviewStars() {
        const starContainer = document.getElementById('bookReviewStars');
        const ratingInput = document.getElementById('bookReviewRating');
        if (!starContainer || !ratingInput) return;

        let currentRating = Number(ratingInput.value) || 5;
        updateReviewStars(currentRating);

        const stars = starContainer.querySelectorAll('[data-rating]');
        stars.forEach(star => {
            star.addEventListener('mouseenter', () => {
                updateReviewStars(Number(star.getAttribute('data-rating')));
            });
            star.addEventListener('click', () => {
                currentRating = Number(star.getAttribute('data-rating'));
                ratingInput.value = currentRating;
                updateReviewStars(currentRating);
            });
        });

        starContainer.addEventListener('mouseleave', () => {
            updateReviewStars(currentRating);
        });
    }

    async function submitReview(event) {
        event.preventDefault();
        if (!window.BROWSE_IS_LOGGED_IN) {
            if (window.showToast) window.showToast('Please login to submit a review', 'warning');
            return;
        }
        if (!window.BROWSE_IS_VERIFIED) {
            if (window.showToast) window.showToast('Please verify your email to submit a review', 'warning');
            return;
        }
        const form = document.getElementById('bookReviewForm');
        if (!form) return;
        const formData = new FormData(form);
        const reviewId = String(formData.get('review_id') || '').trim();
        const action = reviewId ? 'edit-review' : 'add-review';
        const response = await fetch(apiUrl(action), { method: 'POST', body: new URLSearchParams(formData) });
        const result = await response.json().catch(() => null);
        if (!result || !result.success) {
            if (window.showToast) window.showToast((result && result.message) || 'Failed to submit review', 'error');
            return;
        }
        if (window.showToast) window.showToast(result.message || (reviewId ? 'Review updated' : 'Review submitted'), 'success');
        resetReviewForm();
        await openBookDetail(formData.get('book_id'));
        await loadCatalog();
    }

    async function deleteReview(reviewId) {
        if (!window.BROWSE_IS_LOGGED_IN) return;
        if (!confirm('Delete your review?')) return;

        const formData = new URLSearchParams();
        formData.set('review_id', String(reviewId || ''));
        const response = await fetch(apiUrl('delete-review'), { method: 'POST', body: formData });
        const result = await response.json().catch(() => null);
        if (!result || !result.success) {
            if (window.showToast) window.showToast((result && result.message) || 'Failed to delete review', 'error');
            return;
        }
        if (window.showToast) window.showToast(result.message || 'Review deleted', 'success');
        resetReviewForm();
        if (state.currentBookId > 0) {
            await openBookDetail(state.currentBookId);
            await loadCatalog();
        }
    }

    function closeBookDetail() {
        const overlay = document.getElementById('bookDetailOverlay');
        if (!overlay) return;
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        resetReviewForm();
        state.currentBookId = 0;
        setBookParam('');
    }

    function bindEvents() {
        const searchInput = document.getElementById('browseSearchInput');
        const genreSelect = document.getElementById('browseGenreSelect');
        const sortSelect = document.getElementById('browseSortSelect');
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
        if (gridBtn) gridBtn.addEventListener('click', () => { state.view = 'grid'; applyView(); });
        if (listBtn) listBtn.addEventListener('click', () => { state.view = 'list'; applyView(); });
        if (closeBtn) closeBtn.addEventListener('click', closeBookDetail);
        if (backdrop) backdrop.addEventListener('click', closeBookDetail);
        if (reviewForm) reviewForm.addEventListener('submit', submitReview);
        const buyOnlineBtn = document.getElementById('bookDetailBuyOnlineBtn');
        const membershipBtn = document.getElementById('bookDetailMembershipAccessBtn');
        const cancelEditBtn = document.getElementById('bookReviewCancelEdit');
        if (cancelEditBtn) cancelEditBtn.addEventListener('click', resetReviewForm);
        if (buyOnlineBtn) buyOnlineBtn.addEventListener('click', async () => {
            if (buyOnlineBtn.textContent === 'Go to My Books' || buyOnlineBtn.disabled) return;
            handleBuyNow(state.currentBookId, buyOnlineBtn);
        });
        if (membershipBtn) membershipBtn.addEventListener('click', async () => {
            if (membershipBtn.textContent === 'Go to My Books' || membershipBtn.disabled) return;
            if (!state.currentBookId) return;
            if (!window.BROWSE_IS_VERIFIED) {
                window.showToast?.('Please verify your email to use membership access', 'warning');
                return;
            }

            const originalText = membershipBtn.textContent;
            membershipBtn.disabled = true;
            membershipBtn.textContent = 'Granting Access...';

            const body = new URLSearchParams();
            body.set('book_id', String(state.currentBookId));
            try {
                const response = await fetch(apiUrl('unlock-with-membership'), { method: 'POST', body });
                const result = await response.json().catch(() => null);
                if (!result || !result.success) {
                    window.showToast?.((result && result.message) || 'Unable to grant access', 'error');
                    membershipBtn.disabled = false;
                    membershipBtn.textContent = originalText;
                    return;
                }
                window.showToast?.(result.message || 'Book added to your library', 'success');
                if (window.MY_BOOKS_PAGE_URL) {
                    setTimeout(() => { window.location.href = window.MY_BOOKS_PAGE_URL; }, 300);
                }
            } catch (err) {
                window.showToast?.('An error occurred', 'error');
                membershipBtn.disabled = false;
                membershipBtn.textContent = originalText;
            }
        });

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-book-id]');
            if (!trigger) return;

            const action = trigger.getAttribute('data-action') || 'details';
            const bookId = Number(trigger.getAttribute('data-book-id') || 0);
            if (bookId <= 0) return;

            event.preventDefault();

            if (action === 'buy') {
                state.currentBookId = bookId;
                handleBuyNow(bookId, trigger.querySelector('button[data-action="buy"]') || trigger);
            } else {
                openBookDetail(bookId);
            }
        });

        const reviewList = document.getElementById('bookDetailReviewsList');
        if (reviewList) {
            reviewList.addEventListener('click', async (event) => {
                const btn = event.target.closest('button[data-review-action]');
                if (!btn) return;
                const action = btn.getAttribute('data-review-action');
                const reviewId = Number(btn.getAttribute('data-review-id') || 0);
                if (!reviewId) return;

                if (action === 'edit') {
                    const reviewIdInput = document.getElementById('bookReviewId');
                    const ratingInput = document.getElementById('bookReviewRating');
                    const textInput = document.getElementById('bookReviewText');
                    const submitBtn = document.querySelector('#bookReviewForm button[type="submit"]');
                    const cancelEditBtnInner = document.getElementById('bookReviewCancelEdit');
                    if (reviewIdInput) reviewIdInput.value = String(reviewId);
                    if (ratingInput) {
                        ratingInput.value = String(btn.getAttribute('data-review-rating') || '5');
                        updateReviewStars(Number(ratingInput.value));
                    }
                    if (textInput) {
                        textInput.value = String(btn.getAttribute('data-review-text') || '');
                        textInput.focus();
                    }
                    if (submitBtn) submitBtn.textContent = 'Update Review';
                    if (cancelEditBtnInner) cancelEditBtnInner.classList.remove('hidden');
                    return;
                }

                if (action === 'delete') {
                    await deleteReview(reviewId);
                }
            });
        }
    }

    function openFromUrlIfAny() {
        const url = new URL(window.location.href);
        const bookId = Number(url.searchParams.get('book') || 0);
        if (bookId > 0) openBookDetail(bookId);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('browseCatalogGrid')) return;
        bindEvents();
        initReviewStars();
        loadCatalog().then(openFromUrlIfAny);
    });
})();
