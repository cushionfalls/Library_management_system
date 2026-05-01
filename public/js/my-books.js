(function () {
    const apiBase = window.MYBOOKS_API_URL || '';
    let currentRendition = null;
    let saveTimer = null;
    let currentBookId = 0;
    let allBooks = [];
    let currentCategory = 'ALL';

    function esc(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function apiUrl(action, params = {}) {
        const qp = new URLSearchParams(params);
        qp.set('action', action);
        return apiBase + '?' + qp.toString();
    }

    function progressLabel(progress) {
        const p = Math.max(0, Math.min(100, Number(progress || 0)));
        return p + '%';
    }

    function parseLocation(raw) {
        const value = String(raw || '').trim();
        if (!value) return { cfi: '', page: '' };
        try {
            const parsed = JSON.parse(value);
            return {
                cfi: String(parsed.cfi || ''),
                page: String(parsed.page || ''),
            };
        } catch (_) {
            return { cfi: value, page: '' };
        }
    }

    async function loadMyBooks() {
        const res = await fetch(apiUrl('my-books'), { cache: 'no-store' });
        const data = await res.json().catch(() => null);
        if (!data || !data.success) {
            renderError((data && data.message) || 'Unable to load your books');
            return;
        }
        allBooks = Array.isArray(data.books) ? data.books : [];
        populateCategories(allBooks);
        applyFilter();
    }

    function populateCategories(books) {
        const filter = document.getElementById('myBooksCategoryFilter');
        if (!filter) return;
        
        const genres = new Set();
        books.forEach(b => {
            if (b.genre && b.genre.trim() !== '') {
                genres.add(b.genre.toUpperCase());
            }
        });
        
        let html = '<option value="ALL">All Categories</option>';
        Array.from(genres).sort().forEach(g => {
            const label = g.charAt(0) + g.slice(1).toLowerCase().replace(/_/g, ' ');
            html += `<option value="${esc(g)}">${esc(label)}</option>`;
        });
        filter.innerHTML = html;
        filter.value = currentCategory;
    }

    function applyFilter() {
        const filtered = currentCategory === 'ALL' 
            ? allBooks 
            : allBooks.filter(b => (b.genre || '').toUpperCase() === currentCategory);
        renderBooks(filtered);
    }

    function renderError(message) {
        const current = document.getElementById('myBooksCurrentList');
        if (current) current.innerHTML = '<div class="mybooks-glass-card rounded-xl p-8 text-red-600">' + esc(message) + '</div>';
    }

    function renderBooks(books) {
        const current = document.getElementById('myBooksCurrentList');
        const collection = document.getElementById('myBooksCollectionList');
        const count = document.getElementById('myBooksActiveCount');
        if (!current || !collection || !count) return;

        count.textContent = books.length + ' active books';
        if (!books.length) {
            current.innerHTML = '<div class="mybooks-glass-card rounded-xl p-8 text-on-surface-variant">No books yet. Buy online or use membership access from Browse.</div>';
            collection.innerHTML = '';
            return;
        }

        current.innerHTML = books.slice(0, 2).map((book) => {
            const progress = Number(book.progress_percent || 0);
            return `
                <div class="bg-surface-container-low rounded-xl p-8 flex gap-8 items-center">
                    <a href="index.php?page=books&book=${book.book_id}" class="w-28 h-44 rounded-lg overflow-hidden shadow-xl shrink-0 block">
                        <img class="w-full h-full object-cover" src="${esc(book.cover_image_url || '')}" alt="${esc(book.name)}" />
                    </a>
                    <div class="flex-1">
                        <span class="px-3 py-1 bg-primary-container text-white text-[10px] font-bold rounded-full uppercase tracking-widest mb-3 inline-block">${esc(book.access_type)}</span>
                        <h3 class="text-2xl font-black text-on-surface mb-1">${esc(book.name)}</h3>
                        <p class="text-on-surface-variant mb-5 font-medium">${esc(book.authors)}</p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-primary"><span>PROGRESS</span><span>${progressLabel(progress)}</span></div>
                            <div class="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden"><div class="h-full bg-primary rounded-full" style="width:${Math.max(0, Math.min(100, progress))}%;"></div></div>
                        </div>
                        <button class="mt-5 px-4 py-2 bg-primary text-white rounded-lg font-bold text-sm" data-open-reader="${book.book_id}">Read</button>
                        <a class="mt-2 inline-block text-xs text-on-surface-variant hover:text-primary" href="index.php?page=books&book=${book.book_id}">View Description</a>
                    </div>
                </div>`;
        }).join('');

        collection.innerHTML = books.map((book) => `
            <div class="group">
                <a href="index.php?page=books&book=${book.book_id}" class="aspect-[3/4] rounded-lg overflow-hidden mb-3 shadow-lg block">
                    <img class="w-full h-full object-cover" src="${esc(book.cover_image_url || '')}" alt="${esc(book.name)}"/>
                </a>
                <h4 class="text-sm font-bold text-on-surface truncate">${esc(book.name)}</h4>
                <p class="text-xs text-on-surface-variant">${esc(book.authors)}</p>
                <p class="text-xs mt-1 text-primary font-semibold">Page: ${esc(parseLocation(book.current_location).page || 'Start')}</p>
                <button class="mt-2 text-xs font-bold text-primary hover:underline" data-open-reader="${book.book_id}">Read</button>
                <a class="mt-1 block text-xs text-on-surface-variant hover:text-primary" href="index.php?page=books&book=${book.book_id}">View Description</a>
            </div>
        `).join('');
    }

    async function openReader(bookId) {
        const id = Number(bookId || 0);
        if (!id) return;
        currentBookId = id;

        const booksRes = await fetch(apiUrl('my-books'), { cache: 'no-store' });
        const booksData = await booksRes.json().catch(() => null);
        const books = Array.isArray(booksData?.books) ? booksData.books : [];
        const book = books.find((b) => Number(b.book_id) === id);
        if (!book || !book.epub_url) {
            window.showToast?.('EPUB is not available for this book', 'error');
            return;
        }

        const modal = document.getElementById('myBooksReaderModal');
        const title = document.getElementById('myBooksReaderTitle');
        const meta = document.getElementById('myBooksReaderMeta');
        const container = document.getElementById('myBooksReaderContainer');
        if (!modal || !container) return;

        if (title) title.textContent = book.name || 'Reader';
        if (meta) meta.textContent = '';
        container.innerHTML = '';

        if (currentRendition && currentRendition.destroy) {
            try { currentRendition.destroy(); } catch (_) {}
            currentRendition = null;
        }

        modal.showModal();
        const epub = ePub(book.epub_url);
        const rendition = epub.renderTo('myBooksReaderContainer', { width: '100%', height: '100%' });
        currentRendition = rendition;

        const stateRes = await fetch(apiUrl('reader-state', { book_id: id }), { cache: 'no-store' });
        const stateData = await stateRes.json().catch(() => null);
        const savedRaw = stateData?.state?.current_location || book.current_location || '';
        const savedLocation = parseLocation(savedRaw);

        if (String(book.epub_url).toLowerCase().endsWith('.pdf')) {
            container.innerHTML = `<iframe src="${esc(book.epub_url)}#toolbar=0&navpanes=0" style="width:100%;height:100%;border:0;" title="PDF reader"></iframe>`;
            return;
        }

        rendition.display(savedLocation.cfi || undefined);

        epub.ready.then(() => {
            return epub.locations.generate(1600);
        }).then(() => {
            const loc = rendition.currentLocation();
            if (loc && loc.start) {
                const cur = epub.locations.locationFromCfi(loc.start.cfi);
                const tot = epub.locations.total;
                document.getElementById('myBooksReaderPageInfo').textContent = `Page ${cur} of ${tot}`;
            }
        });

        rendition.on('relocated', (location) => {
            let percentage = location?.start?.percentage != null ? Math.round(location.start.percentage * 100) : 0;
            let pageLabel = '';
            
            if (epub.locations && epub.locations.length() > 0) {
                const currentPage = epub.locations.locationFromCfi(location.start.cfi);
                const totalPages = epub.locations.total;
                percentage = Math.round(epub.locations.percentageFromCfi(location.start.cfi) * 100);
                pageLabel = `Page ${currentPage} of ${totalPages}`;
                document.getElementById('myBooksReaderPageInfo').textContent = pageLabel;

                const marker = JSON.stringify({
                    cfi: location?.start?.cfi || '',
                    page: pageLabel || 'Start'
                });
                queueSaveProgress(id, Math.min(100, Math.max(0, percentage)), marker);
            } else {
                document.getElementById('myBooksReaderPageInfo').textContent = 'Calculating pages...';
            }
        });
    }

    function queueSaveProgress(bookId, progress, marker) {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(async () => {
            const form = new URLSearchParams();
            form.set('book_id', String(bookId));
            form.set('progress_percent', String(progress || 0));
            form.set('current_location', String(marker || ''));
            await fetch(apiUrl('save-progress'), { method: 'POST', body: form });
        }, 800);
    }

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-open-reader]');
        if (!btn) return;
        openReader(btn.getAttribute('data-open-reader'));
    });

    document.getElementById('myBooksReaderPrevBtn')?.addEventListener('click', () => {
        if (currentRendition) currentRendition.prev();
    });

    document.getElementById('myBooksReaderNextBtn')?.addEventListener('click', () => {
        if (currentRendition) currentRendition.next();
    });

    document.getElementById('myBooksReaderModal')?.addEventListener('close', () => {
        if (currentBookId) loadMyBooks();
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadMyBooks();
        const filter = document.getElementById('myBooksCategoryFilter');
        if (filter) {
            filter.addEventListener('change', (e) => {
                currentCategory = e.target.value;
                applyFilter();
            });
        }
    });
})();
