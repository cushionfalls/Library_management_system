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
    
    // Modern Reader state additions
    let currentBook = null; 
    let currentTheme = localStorage.getItem('epub_reader_theme') || 'light';
    let currentFlow = localStorage.getItem('epub_reader_flow') || 'paginated';
    let activeSidebarTab = 'toc';
    let sidebarOpen = false;

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
        
        // Font customization
        currentRendition.themes.fontSize(currentFontSize + '%');
        currentRendition.themes.font(currentFontFamily);
        const level = document.getElementById('myBooksZoomLevel');
        if (level) level.textContent = currentFontSize + '%';
        
        // Register Themes inside the EPUB iframe
        currentRendition.themes.register('light', {
            'body': { 
                'background': '#ffffff !important', 
                'color': '#1e293b !important', 
                'font-family': currentFontFamily + ' !important', 
                'line-height': '1.6 !important',
                'margin': '0 !important',
                'padding': '0 32px !important',
                'max-width': '100% !important',
                'box-sizing': 'border-box !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'p': { 
                'color': '#1e293b !important', 
                'font-size': '1em !important', 
                'line-height': '1.6 !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'h1, h2, h3, h4, h5, h6': { 'color': '#0f172a !important', 'font-weight': 'bold !important' },
            'a': { 'color': '#2563eb !important' },
            'img': { 'max-width': '100% !important', 'height': 'auto !important' },
            'hr, .divider, .hr, [class*="separator"], [id*="separator"]': { 
                'display': 'none !important', 
                'border': 'none !important', 
                'background': 'transparent !important', 
                'height': '0 !important' 
            },
            '::selection': { 'background': 'rgba(37, 99, 235, 0.15) !important' },
            '.epubjs-hl': { 'background-color': 'rgba(254, 240, 138, 0.6) !important' }
        });
        currentRendition.themes.register('sepia', {
            'body': { 
                'background': '#fdf6e3 !important', 
                'color': '#5b4636 !important', 
                'font-family': currentFontFamily + ' !important', 
                'line-height': '1.6 !important',
                'margin': '0 !important',
                'padding': '0 32px !important',
                'max-width': '100% !important',
                'box-sizing': 'border-box !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'p': { 
                'color': '#5b4636 !important', 
                'font-size': '1em !important', 
                'line-height': '1.6 !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'h1, h2, h3, h4, h5, h6': { 'color': '#433422 !important', 'font-weight': 'bold !important' },
            'a': { 'color': '#b45309 !important' },
            'img': { 'max-width': '100% !important', 'height': 'auto !important' },
            'hr, .divider, .hr, [class*="separator"], [id*="separator"]': { 
                'display': 'none !important', 
                'border': 'none !important', 
                'background': 'transparent !important', 
                'height': '0 !important' 
            },
            '::selection': { 'background': 'rgba(180, 83, 9, 0.15) !important' },
            '.epubjs-hl': { 'background-color': 'rgba(254, 240, 138, 0.6) !important' }
        });
        currentRendition.themes.register('dark', {
            'body': { 
                'background': '#1e1e1e !important', 
                'color': '#e2e8f0 !important', 
                'font-family': currentFontFamily + ' !important', 
                'line-height': '1.6 !important',
                'margin': '0 !important',
                'padding': '0 32px !important',
                'max-width': '100% !important',
                'box-sizing': 'border-box !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'p': { 
                'color': '#e2e8f0 !important', 
                'font-size': '1em !important', 
                'line-height': '1.6 !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'h1, h2, h3, h4, h5, h6': { 'color': '#f8fafc !important', 'font-weight': 'bold !important' },
            'a': { 'color': '#60a5fa !important' },
            'img': { 'max-width': '100% !important', 'height': 'auto !important' },
            'hr, .divider, .hr, [class*="separator"], [id*="separator"]': { 
                'display': 'none !important', 
                'border': 'none !important', 
                'background': 'transparent !important', 
                'height': '0 !important' 
            },
            '::selection': { 'background': 'rgba(59, 130, 246, 0.3) !important' },
            '.epubjs-hl': { 'background-color': 'rgba(254, 240, 138, 0.3) !important' }
        });
        currentRendition.themes.register('sage', {
            'body': { 
                'background': '#f0f4f1 !important', 
                'color': '#1c3d27 !important', 
                'font-family': currentFontFamily + ' !important', 
                'line-height': '1.6 !important',
                'margin': '0 !important',
                'padding': '0 32px !important',
                'max-width': '100% !important',
                'box-sizing': 'border-box !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'p': { 
                'color': '#1c3d27 !important', 
                'font-size': '1em !important', 
                'line-height': '1.6 !important',
                'word-wrap': 'break-word !important',
                'overflow-wrap': 'break-word !important'
            },
            'h1, h2, h3, h4, h5, h6': { 'color': '#0e2415 !important', 'font-weight': 'bold !important' },
            'a': { 'color': '#16a34a !important' },
            'img': { 'max-width': '100% !important', 'height': 'auto !important' },
            'hr, .divider, .hr, [class*="separator"], [id*="separator"]': { 
                'display': 'none !important', 
                'border': 'none !important', 
                'background': 'transparent !important', 
                'height': '0 !important' 
            },
            '::selection': { 'background': 'rgba(22, 163, 74, 0.15) !important' },
            '.epubjs-hl': { 'background-color': 'rgba(254, 240, 138, 0.6) !important' }
        });

        // Apply active theme
        currentRendition.themes.select(currentTheme);

        // Update active class on settings popover theme dots
        document.querySelectorAll('.theme-dot').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-theme-id') === currentTheme);
        });

        // Apply theme to the surrounding layout modal box
        const readerBox = document.getElementById('myBooksReaderBox');
        if (readerBox) {
            readerBox.setAttribute('data-reader-theme', currentTheme);
        }

        // Propagate theme colors to :root so that ::backdrop pseudo-element can access them.
        // ::backdrop lives in the top layer and does NOT inherit CSS custom properties from its
        // originating element — so we must mirror the values onto document.documentElement.
        const THEME_COLORS = {
            light:  { bg: '#f8fafc', text: '#1e293b' },
            sepia:  { bg: '#f4ecd8', text: '#5b4636' },
            dark:   { bg: '#121212', text: '#e2e8f0' },
            sage:   { bg: '#e2ebd5', text: '#1c3d27' },
        };
        const themeColors = THEME_COLORS[currentTheme] || THEME_COLORS.light;
        document.documentElement.style.setProperty('--reader-bg',   themeColors.bg);
        document.documentElement.style.setProperty('--reader-text', themeColors.text);

        // Re-apply highlights when settings change or rendition is ready
        currentHighlights.forEach(h => {
            const cfi = typeof h === 'string' ? h : h.cfi;
            currentRendition.annotations.remove(cfi, 'highlight');
            currentRendition.annotations.add('highlight', cfi, {}, (e) => {
                currentSelectionCfi = cfi;
                const hBtn = document.getElementById('myBooksHighlightBtn');
                const uBtn = document.getElementById('myBooksUnhighlightBtn');
                if (hBtn) hBtn.classList.add('hidden');
                if (uBtn) uBtn.classList.remove('hidden');
            }, 'epubjs-hl');
        });
    }

    function selectTheme(themeId) {
        currentTheme = themeId;
        localStorage.setItem('epub_reader_theme', themeId);
        applyReaderSettings();
    }

    function selectFlow(flowMode) {
        if (currentFlow === flowMode) return;
        currentFlow = flowMode;
        localStorage.setItem('epub_reader_flow', flowMode);
        
        updateFlowUI();
        
        if (currentRendition && currentRendition.manager && typeof currentRendition.currentLocation === 'function' && currentBook) {
            let activeCfi = null;
            try {
                const loc = currentRendition.currentLocation();
                if (loc && loc.start) {
                    activeCfi = loc.start.cfi;
                }
            } catch (_) {}
            recreateRendition(activeCfi);
        }
    }

    function updateFlowUI() {
        const paginatedBtn = document.getElementById('myBooksFlowPaginated');
        const scrolledBtn = document.getElementById('myBooksFlowScrolled');
        const prevBtnLabel = document.getElementById('myBooksPrevLabel');
        const nextBtnLabel = document.getElementById('myBooksNextLabel');
        const readerBox = document.getElementById('myBooksReaderBox');
        
        const isPaginated = currentFlow === 'paginated';
        
        if (paginatedBtn && scrolledBtn) {
            paginatedBtn.classList.toggle('active', isPaginated);
            scrolledBtn.classList.toggle('active', !isPaginated);
        }

        if (readerBox) {
            readerBox.classList.toggle('scrolled-mode', !isPaginated);
        }

        const leftChevron = document.getElementById('myBooksFloatingPrev');
        const rightChevron = document.getElementById('myBooksFloatingNext');
        if (leftChevron) leftChevron.classList.toggle('hidden', !isPaginated);
        if (rightChevron) rightChevron.classList.toggle('hidden', !isPaginated);

        if (prevBtnLabel) prevBtnLabel.textContent = isPaginated ? 'Prev' : 'Prev Chapter';
        if (nextBtnLabel) nextBtnLabel.textContent = isPaginated ? 'Next' : 'Next Chapter';
    }

    function recreateRendition(cfiToDisplay) {
        if (!currentBook) return;

        if (currentRendition) {
            try {
                currentRendition.destroy();
            } catch (_) {}
            currentRendition = null;
        }

        const container = document.getElementById('myBooksReaderContainer');
        if (container) container.innerHTML = '';

        const isPaginated = currentFlow === 'paginated';

        const renditionOptions = {
            width: '100%',
            height: '100%',
            spread: 'none',
            allowScriptedContent: true,
        };

        if (isPaginated) {
            renditionOptions.manager = 'default';
            renditionOptions.flow = 'paginated';
        } else {
            // Per chapter scroll requested
            renditionOptions.manager = 'default';
            renditionOptions.flow = 'scrolled';
        }

        const rendition = currentBook.renderTo('myBooksReaderContainer', renditionOptions);
        currentRendition = rendition;
        
        applyReaderSettings();
        setupRenditionEvents(rendition);

        if (cfiToDisplay) {
            rendition.display(cfiToDisplay);
        }
    }

    function toggleSidebar(open) {
        const sidebar = document.getElementById('myBooksReaderSidebar');
        if (!sidebar) return;

        sidebarOpen = (open !== undefined) ? open : sidebar.classList.contains('collapsed');
        
        if (sidebarOpen) {
            sidebar.classList.remove('collapsed');
            renderHighlights();
        } else {
            sidebar.classList.add('collapsed');
        }
    }

    function toggleSettingsPanel(open) {
        const panel = document.getElementById('myBooksSettingsPanel');
        if (!panel) return;
        
        const show = (open !== undefined) ? open : panel.classList.contains('hidden');
        if (show) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
        }
    }

    function switchSidebarTab(tabId) {
        activeSidebarTab = tabId;
        
        const tocBtn = document.querySelector('.reader-sidebar-tab-btn[data-tab-id="toc"]');
        const highlightsBtn = document.querySelector('.reader-sidebar-tab-btn[data-tab-id="highlights"]');
        const tocList = document.getElementById('myBooksTOCList');
        const highlightsList = document.getElementById('myBooksHighlightsList');
        
        if (tocBtn && highlightsBtn && tocList && highlightsList) {
            if (tabId === 'toc') {
                tocBtn.classList.add('active');
                highlightsBtn.classList.remove('active');
                tocList.classList.remove('hidden');
                highlightsList.classList.add('hidden');
            } else {
                tocBtn.classList.remove('active');
                highlightsBtn.classList.add('active');
                tocList.classList.add('hidden');
                highlightsList.classList.remove('hidden');
                renderHighlights();
            }
        }
    }

    function renderTOC(toc) {
        const container = document.getElementById('myBooksTOCList');
        if (!container) return;

        if (!toc || toc.length === 0) {
            container.innerHTML = '<div class="text-xs opacity-50 py-4 text-center">No chapters available</div>';
            return;
        }

        function generateTOCItemHTML(item, depth = 0) {
            const indent = depth * 14;
            let html = `
                <div class="toc-item" data-href="${esc(item.href)}" style="padding-left: ${14 + indent}px;">
                    <span class="material-symbols-outlined text-[15px] opacity-60">bookmark</span>
                    <span class="truncate flex-1">${esc(item.label)}</span>
                </div>
            `;
            if (item.subitems && item.subitems.length > 0) {
                item.subitems.forEach(sub => {
                    html += generateTOCItemHTML(sub, depth + 1);
                });
            }
            return html;
        }

        let fullHTML = '';
        toc.forEach(item => {
            fullHTML += generateTOCItemHTML(item, 0);
        });
        container.innerHTML = fullHTML;

        container.querySelectorAll('.toc-item').forEach(el => {
            el.addEventListener('click', () => {
                const href = el.getAttribute('data-href');
                if (currentRendition) {
                    currentRendition.display(href);
                    if (window.innerWidth < 768) {
                        toggleSidebar(false);
                    }
                }
            });
        });

        if (currentRendition && currentRendition.manager && typeof currentRendition.currentLocation === 'function') {
            try {
                const loc = currentRendition.currentLocation();
                if (loc && loc.start) {
                    updateActiveChapterInTOC(loc.start.cfi);
                }
            } catch (_) {}
        }
    }

    function updateActiveChapterInTOC(cfi) {
        if (!currentBook || !currentBook.navigation || !currentBook.navigation.toc) return;
        const container = document.getElementById('myBooksTOCList');
        if (!container) return;

        container.querySelectorAll('.toc-item').forEach(item => item.classList.remove('active'));

        try {
            const spineItem = currentBook.spine.get(cfi);
            if (spineItem && spineItem.href) {
                let bestMatch = null;
                let normalizedSpineHref = spineItem.href.replace(/.*\//, '');

                container.querySelectorAll('.toc-item').forEach(item => {
                    const tocHref = item.getAttribute('data-href');
                    const normalizedTocHref = tocHref.replace(/.*\//, '').split('#')[0];
                    if (normalizedSpineHref === normalizedTocHref) {
                        bestMatch = item;
                    }
                });

                if (bestMatch) {
                    bestMatch.classList.add('active');
                    const metaEl = document.getElementById('myBooksReaderMeta');
                    const activeTitle = bestMatch.querySelector('span:not(.material-symbols-outlined)').textContent;
                    if (metaEl && activeTitle) {
                        metaEl.textContent = activeTitle;
                    }
                    bestMatch.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            }
        } catch (_) {}
    }

    function renderHighlights() {
        const container = document.getElementById('myBooksHighlightsList');
        if (!container) return;

        if (!currentHighlights || currentHighlights.length === 0) {
            container.innerHTML = '<div class="text-xs opacity-50 py-12 text-center flex flex-col items-center gap-2"><span class="material-symbols-outlined text-3xl">draw</span><span>No highlights yet. Select text to draw highlights.</span></div>';
            return;
        }

        container.innerHTML = currentHighlights.map((h, index) => {
            const quote = h.text ? `"${esc(h.text)}"` : 'Highlighted Passage';
            return `
                <div class="highlight-card flex flex-col gap-2 relative group" data-cfi="${esc(h.cfi)}">
                    <p class="text-xs italic font-medium opacity-90 line-clamp-3">${quote}</p>
                    <div class="flex items-center justify-between mt-1 border-t border-outline-variant/10 pt-2 shrink-0">
                        <span class="text-[10px] font-bold text-primary tracking-wider uppercase">${esc(h.page || 'Page Reference')}</span>
                        <button class="text-[10px] text-error hover:underline flex items-center gap-0.5" data-delete-highlight="${esc(h.cfi)}" title="Remove Note">
                            <span class="material-symbols-outlined text-xs">delete</span> Delete
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        container.querySelectorAll('.highlight-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.closest('[data-delete-highlight]')) return;
                const cfi = card.getAttribute('data-cfi');
                if (currentRendition) {
                    currentRendition.display(cfi);
                    if (window.innerWidth < 768) {
                        toggleSidebar(false);
                    }
                }
            });
        });

        container.querySelectorAll('[data-delete-highlight]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const cfi = btn.getAttribute('data-delete-highlight');
                if (confirm('Delete this highlight?')) {
                    removeHighlight(cfi);
                    renderHighlights();
                }
            });
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
        loadWishlist();
    }

    async function loadWishlist() {
        const wishlistContainer = document.getElementById('myBooksWishlistList');
        if (!wishlistContainer) return;

        try {
            const response = await fetch((window.WISHLIST_API_URL || '') + '?action=list', { cache: 'no-store' });
            const result = await response.json();
            
            if (!result || !result.success) {
                wishlistContainer.innerHTML = '<p class="col-span-full text-center text-red-500">Failed to load wishlist</p>';
                return;
            }

            renderWishlist(result.data || []);
        } catch (error) {
            wishlistContainer.innerHTML = '<p class="col-span-full text-center text-red-500">An error occurred while loading wishlist</p>';
        }
    }

    function renderWishlist(items) {
        const container = document.getElementById('myBooksWishlistList');
        const countEl = document.getElementById('myBooksWishlistCount');
        if (!container || !countEl) return;

        countEl.textContent = items.length + ' item' + (items.length !== 1 ? 's' : '');

        if (items.length === 0) {
            container.innerHTML = `
                <div class="col-span-full py-12 text-center text-on-surface-variant bg-surface-container-low rounded-2xl border-2 border-dashed border-outline-variant/30">
                    <span class="material-symbols-outlined text-4xl mb-3 opacity-50 block">bookmark_add</span>
                    <p class="font-medium">Your wishlist is empty. Start adding books from the catalog!</p>
                    <a href="${window.BROWSE_BOOKS_URL || '#'}" class="btn btn-primary btn-sm mt-4">Browse Books</a>
                </div>
            `;
            return;
        }

        container.innerHTML = items.map(item => {
            const priceCents = Number(item.online_buy_price || item.price || 0);
            const priceDisplay = priceCents > 0 ? formatUsdFromCents(priceCents) : 'FREE';
            
            return `
                <div class="group flex flex-col bg-surface-container-low/50 dark:bg-surface-container-low/30 rounded-xl p-3 border border-outline-variant/20 hover:border-primary/40 hover:shadow-xl transition-all duration-300" data-wishlist-item="${item.id}">
                    <div class="relative aspect-[3/4] rounded-lg overflow-hidden mb-3 shadow-md group-hover:shadow-lg transition-shadow">
                        <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="${esc(item.cover_image_url || '')}" alt="${esc(item.name)}"/>
                        <button class="absolute top-2 right-2 w-7 h-7 bg-white/90 dark:bg-surface-container-low/90 backdrop-blur-md rounded-full flex items-center justify-center text-red-500 shadow-md hover:scale-110 transition-transform active:scale-95 group/remove" data-remove-wishlist="${item.id}" title="Remove from wishlist">
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">bookmark</span>
                        </button>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-on-surface truncate mb-0.5">${esc(item.name)}</h4>
                        <p class="text-[11px] text-on-surface-variant truncate mb-3 font-medium">${esc(item.authors)}</p>
                        <div class="flex items-center justify-between mt-auto pt-2 border-t border-outline-variant/10">
                            <span class="text-xs font-black text-primary tracking-tight">${priceDisplay}</span>
                            <button class="px-3 py-1.5 bg-primary text-white text-[10px] font-bold rounded-lg hover:brightness-110 active:scale-95 transition-all uppercase tracking-tighter shadow-sm" data-buy-wishlist="${item.id}">Buy Now</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function removeFromWishlist(bookId) {
        if (!confirm('Remove this book from your wishlist?')) return;
        
        try {
            const body = new URLSearchParams();
            body.set('book_id', String(bookId));
            const response = await fetch((window.WISHLIST_API_URL || '') + '?action=toggle', { method: 'POST', body });
            const result = await response.json();
            
            if (result && result.success) {
                window.showToast?.(result.message, 'success');
                loadWishlist();
                updateWishlistBadge();
            } else {
                window.showToast?.(result.message || 'Failed to remove from wishlist', 'error');
            }
        } catch (e) {
            window.showToast?.('An error occurred', 'error');
        }
    }

    async function updateWishlistBadge() {
        try {
            const response = await fetch((window.WISHLIST_API_URL || '') + '?action=count');
            const result = await response.json();
            const badge = document.getElementById('navWishlistBadge');
            if (badge && result.success) {
                badge.textContent = result.count;
                badge.classList.toggle('hidden', result.count <= 0);
            }
        } catch (e) {}
    }

    async function handleWishlistBuy(bookId, buttonEl) {
        if (!bookId) return;
        const originalText = buttonEl.textContent;
        buttonEl.disabled = true;
        buttonEl.textContent = '...';

        try {
            const body = new URLSearchParams();
            body.set('book_id', String(bookId));
            const response = await fetch(window.MYBOOKS_API_URL + '?action=purchase-online', { method: 'POST', body });
            const result = await response.json();

            if (result && result.success) {
                window.showToast?.(result.message, 'success');
                await loadMyBooks(); // Will also call loadWishlist()
                updateWishlistBadge();
            } else {
                window.showToast?.(result.message || 'Purchase failed', 'error');
                buttonEl.disabled = false;
                buttonEl.textContent = originalText;
            }
        } catch (error) {
            window.showToast?.('An error occurred', 'error');
            buttonEl.disabled = false;
            buttonEl.textContent = originalText;
        }
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
        if (currentBook && currentBook.destroy) {
            try { currentBook.destroy(); } catch (_) {}
            currentBook = null;
        }

        modal.showModal();
        loadHighlights(id);
        
        toggleSidebar(false);
        toggleSettingsPanel(false);
        updateFlowUI();

        setTimeout(() => {
            if (String(book.epub_url).toLowerCase().endsWith('.pdf')) {
                container.innerHTML = `<iframe src="${esc(book.epub_url)}#toolbar=0&navpanes=0" style="width:100%;height:100%;border:0;" title="PDF reader"></iframe>`;
                document.getElementById('myBooksSidebarToggle')?.classList.add('hidden');
                document.getElementById('myBooksSettingsToggle')?.classList.add('hidden');
                return;
            } else {
                document.getElementById('myBooksSidebarToggle')?.classList.remove('hidden');
                document.getElementById('myBooksSettingsToggle')?.classList.remove('hidden');
            }

            const epub = ePub(book.epub_url);
            currentBook = epub;

            const isPaginated = currentFlow === 'paginated';
            const renditionOptions = { 
                width: '100%', 
                height: '100%',
                spread: 'none',
                allowScriptedContent: true
            };

            if (isPaginated) {
                renditionOptions.manager = 'default';
                renditionOptions.flow = 'paginated';
            } else {
                renditionOptions.manager = 'default';
                renditionOptions.flow = 'scrolled';
            }

            const rendition = epub.renderTo('myBooksReaderContainer', renditionOptions);
            currentRendition = rendition;
            
            applyReaderSettings();
            setupRenditionEvents(rendition);

            const stateRes = fetch(apiUrl('reader-state', { book_id: id }), { cache: 'no-store' })
                .then(res => res.json())
                .then(stateData => {
                    const savedRaw = stateData?.state?.current_location || book.current_location || '';
                    const savedLocation = parseLocation(savedRaw);

                    let startCfi = savedLocation.cfi;
                    if (startCfi && !startCfi.startsWith('epubcfi(')) {
                        startCfi = undefined;
                    }

                    rendition.display(startCfi || undefined);

                    epub.ready.then(() => {
                        epub.loaded.navigation.then(nav => {
                            renderTOC(nav.toc);
                        });
                        return epub.locations.generate(1600);
                    }).then(() => {
                        if (!rendition || !rendition.manager || typeof rendition.currentLocation !== 'function' || !currentBook) return;
                        try {
                            const loc = rendition.currentLocation();
                            if (loc && loc.start) {
                                const cur = epub.locations.locationFromCfi(loc.start.cfi);
                                const tot = epub.locations.total;
                                const pageInfoEl = document.getElementById('myBooksReaderPageInfo');
                                if (pageInfoEl) {
                                    pageInfoEl.textContent = `Page ${cur} of ${tot}`;
                                }
                                updateActiveChapterInTOC(loc.start.cfi);
                            }
                        } catch (_) {}
                    });
                })
                .catch(() => null);
        }, 80);
    }

    function setupRenditionEvents(rendition) {
        rendition.hooks.content.register((contents) => {
            const iframe = contents.window.frameElement;
            if (iframe) {
                iframe.removeAttribute('sandbox');
            }
        });

        rendition.on('selected', (cfiRange) => {
            currentSelectionCfi = cfiRange;
            const hBtn = document.getElementById('myBooksHighlightBtn');
            const uBtn = document.getElementById('myBooksUnhighlightBtn');
            
            const exists = currentHighlights.some(h => h.cfi === cfiRange);
            
            if (exists) {
                if (hBtn) hBtn.classList.add('hidden');
                if (uBtn) uBtn.classList.remove('hidden');
            } else {
                if (hBtn) hBtn.classList.remove('hidden');
                if (uBtn) uBtn.classList.add('hidden');
            }
        });

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

        rendition.on('keyup', (event) => {
            const code = event.keyCode || event.which;
            if (code === 37) rendition.prev();
            if (code === 39) rendition.next();
            if (code === 27) {
                if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                    const exitMethod = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
                    if (exitMethod) exitMethod.call(document);
                } else {
                    document.getElementById('myBooksReaderModal')?.close();
                }
            }
        });

        rendition.on('relocated', (location) => {
            if (!currentBook || !currentBookId) return;
            let percentage = location?.start?.percentage != null ? Math.round(location.start.percentage * 100) : 0;
            let pageLabel = '';
            
            if (currentBook.locations && currentBook.locations.length() > 0) {
                const currentPage = currentBook.locations.locationFromCfi(location.start.cfi);
                const totalPages = currentBook.locations.total;
                percentage = Math.round(currentBook.locations.percentageFromCfi(location.start.cfi) * 100);
                pageLabel = `Page ${currentPage} of ${totalPages}`;
                document.getElementById('myBooksReaderPageInfo').textContent = pageLabel;

                const marker = JSON.stringify({
                    cfi: location?.start?.cfi || '',
                    page: pageLabel || 'Start'
                });
                queueSaveProgress(currentBookId, Math.min(100, Math.max(0, percentage)), marker);
            } else {
                document.getElementById('myBooksReaderPageInfo').textContent = 'Calculating pages...';
            }

            if (currentFlow === 'scrolled') {
                const container = document.getElementById('myBooksReaderContainer');
                if (container) {
                    container.scrollTop = 0;
                }
            }

            updateActiveChapterInTOC(location.start.cfi);
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
        if (btn) {
            openReader(btn.getAttribute('data-open-reader'));
            return;
        }

        const removeBtn = event.target.closest('[data-remove-wishlist]');
        if (removeBtn) {
            removeFromWishlist(removeBtn.getAttribute('data-remove-wishlist'));
            return;
        }

        const buyBtn = event.target.closest('[data-buy-wishlist]');
        if (buyBtn) {
            handleWishlistBuy(buyBtn.getAttribute('data-buy-wishlist'), buyBtn);
            return;
        }
    });

    document.getElementById('myBooksReaderPrevBtn')?.addEventListener('click', () => {
        if (currentRendition) currentRendition.prev();
    });

    document.getElementById('myBooksReaderNextBtn')?.addEventListener('click', () => {
        if (currentRendition) currentRendition.next();
    });

    document.getElementById('myBooksFloatingPrev')?.addEventListener('click', () => {
        if (currentRendition) currentRendition.prev();
    });

    document.getElementById('myBooksFloatingNext')?.addEventListener('click', () => {
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
        
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
            const requestMethod = box.requestFullscreen || box.webkitRequestFullscreen || box.mozRequestFullScreen || box.msRequestFullscreen;
            if (requestMethod) {
                requestMethod.call(box).catch(err => {
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
            
            if (currentHighlights.some(h => h.cfi === cfi)) {
                window.showToast?.('Already highlighted', 'info');
                return;
            }

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

    document.getElementById('myBooksCloseReaderBtn')?.addEventListener('click', () => {
        document.getElementById('myBooksReaderModal')?.close();
    });

    document.getElementById('myBooksReaderModal')?.addEventListener('close', () => {
        if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
            const exitMethod = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
            if (exitMethod) {
                try {
                    exitMethod.call(document);
                } catch(e) {}
            }
        }
        if (currentRendition && currentRendition.destroy) {
            try { currentRendition.destroy(); } catch (_) {}
            currentRendition = null;
        }
        if (currentBook && currentBook.destroy) {
            try { currentBook.destroy(); } catch (_) {}
            currentBook = null;
        }
        if (currentBookId) loadMyBooks();
    });

    function init() {
        loadMyBooks().then(() => {
            const url = new URL(window.location.href);
            const openId = url.searchParams.get('open_reader');
            if (openId) {
                openReader(openId);
                url.searchParams.delete('open_reader');
                window.history.replaceState({}, '', url.toString());
            }
        });
        
        const filter = document.getElementById('myBooksCategoryFilter');
        if (filter) {
            filter.addEventListener('change', (e) => {
                currentCategory = e.target.value;
                applyFilter();
            });
        }

        document.getElementById('myBooksSidebarToggle')?.addEventListener('click', () => {
            toggleSidebar();
        });

        document.querySelectorAll('.reader-sidebar-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                switchSidebarTab(btn.getAttribute('data-tab-id'));
            });
        });

        const settingsToggle = document.getElementById('myBooksSettingsToggle');
        if (settingsToggle) {
            settingsToggle.addEventListener('click', (e) => {
                console.log('[EPUB Reader] Settings toggle button clicked.');
                try {
                    e.stopPropagation();
                    const panel = document.getElementById('myBooksSettingsPanel');
                    if (!panel) {
                        const errorMsg = 'Settings Panel element (#myBooksSettingsPanel) is missing in DOM.';
                        console.error('[EPUB Reader Error]', errorMsg);
                        window.showToast?.(errorMsg, 'error');
                        throw new Error(errorMsg);
                    }
                    toggleSettingsPanel();
                    console.log('[EPUB Reader] Settings Panel toggle succeeded. Panel hidden state:', panel.classList.contains('hidden'));
                } catch (err) {
                    console.error('[EPUB Reader Exception] Failed during settings panel click processing:', err);
                    window.showToast?.('Settings Error: ' + err.message, 'error');
                    throw err;
                }
            });
        } else {
            console.error('[EPUB Reader Warning] myBooksSettingsToggle button not found in DOM.');
        }

        document.querySelectorAll('.theme-dot').forEach(btn => {
            btn.addEventListener('click', () => {
                selectTheme(btn.getAttribute('data-theme-id'));
            });
        });

        document.getElementById('myBooksFlowPaginated')?.addEventListener('click', () => {
            selectFlow('paginated');
        });
        document.getElementById('myBooksFlowScrolled')?.addEventListener('click', () => {
            selectFlow('scrolled');
        });

        document.addEventListener('click', (e) => {
            const panel = document.getElementById('myBooksSettingsPanel');
            const toggleBtn = document.getElementById('myBooksSettingsToggle');
            if (panel && toggleBtn && !panel.contains(e.target) && !toggleBtn.contains(e.target)) {
                panel.classList.add('hidden');
            }
        });

        // Dynamic resizing for EPUB.js iframe when window or fullscreen state changes
        window.addEventListener('resize', () => {
            if (!currentRendition) return;
            try {
                const container = document.getElementById('myBooksReaderContainer');
                if (container && container.offsetWidth > 0 && container.offsetHeight > 0) {
                    currentRendition.resize(container.offsetWidth, container.offsetHeight);
                } else {
                    currentRendition.resize();
                }
            } catch(e) {}
        });

        // EPUB.js in paginated mode computes a fixed iframe height at render time.
        // When entering fullscreen the container dimensions change, but the browser's
        // fullscreen animation takes time to settle — so we must retry resize() several
        // times with increasing delays to guarantee it runs after the layout is stable.
        // Passing the actual pixel dimensions is required; resize() with no args is unreliable.
        const handleFullscreenResize = () => {
            if (!currentRendition) return;

            const doResize = () => {
                try {
                    const container = document.getElementById('myBooksReaderContainer');
                    if (container && container.offsetWidth > 0 && container.offsetHeight > 0) {
                        currentRendition.resize(container.offsetWidth, container.offsetHeight);
                    } else {
                        currentRendition.resize();
                    }
                } catch(e) {}
            };

            // Three retries: first fast (catches immediate repaints), then medium and slow
            // to handle browsers with longer fullscreen animation durations.
            setTimeout(doResize, 100);
            setTimeout(doResize, 350);
            setTimeout(doResize, 700);
        };

        document.addEventListener('fullscreenchange', handleFullscreenResize);
        document.addEventListener('webkitfullscreenchange', handleFullscreenResize);
        document.addEventListener('mozfullscreenchange', handleFullscreenResize);
        document.addEventListener('msfullscreenchange', handleFullscreenResize);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
