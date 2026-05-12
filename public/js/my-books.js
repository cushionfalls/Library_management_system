(function () {
    const apiBase = window.MYBOOKS_API_URL || '';
    let currentRendition = null;
    let saveTimer = null;
    let currentBookId = 0;
    let allBooks = [];
    let currentCategory = 'ALL';
    let currentFontSize = 100;
    let currentFontFamily = 'sans-serif';
    let currentSelectionCfi = null;
    let currentHighlights = [];

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

    function applyReaderSettings() {
        if (!currentRendition) return;
        currentRendition.themes.fontSize(currentFontSize + '%');
        currentRendition.themes.font(currentFontFamily);
        const level = document.getElementById('myBooksZoomLevel');
        if (level) level.textContent = currentFontSize + '%';
        
        currentRendition.themes.register('custom', {
            '::selection': { 'background': 'rgba(56, 0, 191, 0.2)' },
            '.epubjs-hl': { 'background-color': 'rgba(254, 240, 138, 0.6) !important' }
        });
        currentRendition.themes.select('custom');
        
        // Re-apply highlights when settings change or rendition is ready
        currentHighlights.forEach(h => {
            const cfi = typeof h === 'string' ? h : h.cfi;
            currentRendition.annotations.remove(cfi, 'highlight');
            currentRendition.annotations.add('highlight', cfi, {}, (e) => {
                // When clicking a highlight, we show the unhighlight button in the header
                currentSelectionCfi = cfi;
                const hBtn = document.getElementById('myBooksHighlightBtn');
                const uBtn = document.getElementById('myBooksUnhighlightBtn');
                if (hBtn) hBtn.classList.add('hidden');
                if (uBtn) uBtn.classList.remove('hidden');
            }, 'epubjs-hl');
        });
    }

    function saveHighlights() {
        if (!currentBookId) return;
        localStorage.setItem(`epub_highlights_${currentBookId}`, JSON.stringify(currentHighlights));
    }

    function loadHighlights(bookId) {
        const saved = localStorage.getItem(`epub_highlights_${bookId}`);
        const data = saved ? JSON.parse(saved) : [];
        // Migration: convert old string CFIs to objects if needed
        currentHighlights = data.map(h => typeof h === 'string' ? { cfi: h, text: '', page: '' } : h);
    }

    function removeHighlight(cfi) {
        if (!currentRendition) return;
        currentRendition.annotations.remove(cfi, 'highlight');
        currentHighlights = currentHighlights.filter(h => h.cfi !== cfi);
        saveHighlights();
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
                <div class="bg-surface-container-low rounded-xl p-4 sm:p-8 flex gap-4 sm:gap-8 items-center">
                    <a href="index.php?page=books&book=${book.book_id}" class="w-24 sm:w-28 h-36 sm:h-44 rounded-lg overflow-hidden shadow-xl shrink-0 block">
                        <img class="w-full h-full object-cover" src="${esc(book.cover_image_url || '')}" alt="${esc(book.name)}" />
                    </a>
                    <div class="flex-1">
                        <span class="px-3 py-1 bg-primary-container text-white text-[10px] font-bold rounded-full uppercase tracking-widest mb-2 sm:mb-3 inline-block">${esc(book.access_type)}</span>
                        <h3 class="text-xl sm:text-2xl font-black text-on-surface mb-1">${esc(book.name)}</h3>
                        <p class="text-on-surface-variant mb-3 sm:mb-5 font-medium text-sm sm:text-base">${esc(book.authors)}</p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-bold text-primary"><span>PROGRESS</span><span>${progressLabel(progress)}</span></div>
                            <div class="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden"><div class="h-full bg-primary rounded-full" style="width:${Math.max(0, Math.min(100, progress))}%;"></div></div>
                        </div>
                        <button class="mt-4 sm:mt-5 px-4 py-2 bg-primary text-white rounded-lg font-bold text-sm" data-open-reader="${book.book_id}">Read</button>
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
            try { currentRendition.destroy(); } catch (_) { }
            currentRendition = null;
        }

        modal.showModal();
        loadHighlights(id);
        
        // Slight delay to ensure the dialog layout is complete before ePub measures the container
        setTimeout(() => {
            const epub = ePub(book.epub_url);
            const rendition = epub.renderTo('myBooksReaderContainer', { 
                width: '100%', 
                height: '100%',
                spread: 'none',
                manager: 'continuous',
                flow: 'paginated'
            });
            currentRendition = rendition;
            applyReaderSettings();

            rendition.on('selected', (cfiRange) => {
                currentSelectionCfi = cfiRange;
                const hBtn = document.getElementById('myBooksHighlightBtn');
                const uBtn = document.getElementById('myBooksUnhighlightBtn');
                
                // Check if this range (or overlapping) is already highlighted
                const exists = currentHighlights.some(h => h.cfi === cfiRange);
                
                if (exists) {
                    if (hBtn) hBtn.classList.add('hidden');
                    if (uBtn) uBtn.classList.remove('hidden');
                } else {
                    if (hBtn) hBtn.classList.remove('hidden');
                    if (uBtn) uBtn.classList.add('hidden');
                }
            });

            // Hide highlight/unhighlight buttons when clicking anywhere in the rendition without a selection
            rendition.on('click', (e) => {
                setTimeout(() => {
                    if (!currentSelectionCfi) {
                        const hBtn = document.getElementById('myBooksHighlightBtn');
                        const uBtn = document.getElementById('myBooksUnhighlightBtn');
                        if (hBtn) hBtn.classList.add('hidden');
                        if (uBtn) uBtn.classList.add('hidden');
                    }
                }, 100);
            });

            const stateRes = fetch(apiUrl('reader-state', { book_id: id }), { cache: 'no-store' })
                .then(res => res.json())
                .then(stateData => {
                    const savedRaw = stateData?.state?.current_location || book.current_location || '';
                    const savedLocation = parseLocation(savedRaw);

                    if (String(book.epub_url).toLowerCase().endsWith('.pdf')) {
                        container.innerHTML = `<iframe src="${esc(book.epub_url)}#toolbar=0&navpanes=0" style="width:100%;height:100%;border:0;" title="PDF reader"></iframe>`;
                        return;
                    }

                    // Validate CFI string to prevent EPUB.js from crashing and bricking navigation
                    let startCfi = savedLocation.cfi;
                    if (startCfi && !startCfi.startsWith('epubcfi(')) {
                        startCfi = undefined;
                    }

                    rendition.display(startCfi || undefined);

                    rendition.on('keyup', (event) => {
                        const code = event.keyCode || event.which;
                        if (code === 37) rendition.prev();
                        if (code === 39) rendition.next();
                    });

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
                })
                .catch(() => null);
        }, 50);


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

    document.getElementById('myBooksZoomIn')?.addEventListener('click', () => {
        if (currentFontSize >= 200) return;
        currentFontSize += 10;
        applyReaderSettings();
    });

    document.getElementById('myBooksZoomOut')?.addEventListener('click', () => {
        if (currentFontSize <= 50) return;
        currentFontSize -= 10;
        applyReaderSettings();
    });

    document.getElementById('myBooksFontFamily')?.addEventListener('change', (e) => {
        currentFontFamily = e.target.value;
        applyReaderSettings();
    });

    document.getElementById('myBooksFullscreenBtn')?.addEventListener('click', () => {
        const modal = document.getElementById('myBooksReaderModal');
        const box = modal?.querySelector('.modal-box');
        if (!box) return;
        
        // Try requesting fullscreen on the content box
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
            const requestMethod = box.requestFullscreen || box.webkitRequestFullscreen || box.mozRequestFullScreen || box.msRequestFullscreen;
            if (requestMethod) {
                requestMethod.call(box).catch(err => {
                    // Fallback to modal if box fails
                    modal.requestFullscreen?.();
                });
            }
        } else {
            const exitMethod = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
            if (exitMethod) exitMethod.call(document);
        }
    });

    document.getElementById('myBooksHighlightBtn')?.addEventListener('click', async function() {
        if (currentRendition && currentSelectionCfi) {
            const cfi = currentSelectionCfi;
            
            // Check if already exists in memory
            if (currentHighlights.some(h => h.cfi === cfi)) {
                window.showToast?.('Already highlighted', 'info');
                return;
            }

            // Get text and page info
            let text = '';
            try {
                const range = await currentRendition.book.getRange(cfi);
                text = range.toString();
            } catch(e) {}

            const pageLabel = document.getElementById('myBooksReaderPageInfo')?.textContent || '';

            currentRendition.annotations.add('highlight', cfi, {}, (e) => {
                currentSelectionCfi = cfi;
                const hBtn = document.getElementById('myBooksHighlightBtn');
                const uBtn = document.getElementById('myBooksUnhighlightBtn');
                if (hBtn) hBtn.classList.add('hidden');
                if (uBtn) uBtn.classList.remove('hidden');
            }, 'epubjs-hl');
            
            currentHighlights.push({
                cfi: cfi,
                text: text,
                page: pageLabel,
                book_id: currentBookId
            });
            saveHighlights();
            
            // Clear selection and hide button
            currentRendition.getContents().forEach(c => c.window.getSelection().removeAllRanges());
            this.classList.add('hidden');
            currentSelectionCfi = null;
        }
    });

    document.getElementById('myBooksUnhighlightBtn')?.addEventListener('click', function() {
        if (currentRendition && currentSelectionCfi) {
            removeHighlight(currentSelectionCfi);
            this.classList.add('hidden');
            currentSelectionCfi = null;
        }
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
