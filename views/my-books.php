<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.5/jszip.min.js"></script>
<script src="https://unpkg.com/epubjs/dist/epub.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
    .mybooks-glass-card { background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(24px); }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    
    /* Premium Reader Themes */
    [data-reader-theme="light"] {
        --reader-bg: #f8fafc;
        --reader-card-bg: #ffffff;
        --reader-text: #1e293b;
        --reader-text-muted: #64748b;
        --reader-border: rgba(226, 232, 240, 0.8);
        --reader-header-bg: rgba(255, 255, 255, 0.85);
        --reader-sidebar-bg: #ffffff;
        --reader-active-item: #eff6ff;
        --reader-accent: #2563eb;
        --reader-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
    }

    [data-reader-theme="sepia"] {
        --reader-bg: #f4ecd8;
        --reader-card-bg: #fdf6e3;
        --reader-text: #5b4636;
        --reader-text-muted: #8c7355;
        --reader-border: rgba(91, 70, 54, 0.15);
        --reader-header-bg: rgba(244, 236, 216, 0.85);
        --reader-sidebar-bg: #f5eedb;
        --reader-active-item: #ebdcb9;
        --reader-accent: #b45309;
        --reader-shadow: 0 4px 6px -1px rgb(91 70 54 / 0.05);
    }

    [data-reader-theme="dark"] {
        --reader-bg: #121212;
        --reader-card-bg: #1e1e1e;
        --reader-text: #e2e8f0;
        --reader-text-muted: #94a3b8;
        --reader-border: rgba(255, 255, 255, 0.1);
        --reader-header-bg: rgba(30, 30, 30, 0.85);
        --reader-sidebar-bg: #181818;
        --reader-active-item: #2d2d2d;
        --reader-accent: #3b82f6;
        --reader-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.5);
    }

    [data-reader-theme="sage"] {
        --reader-bg: #e2ebd5;
        --reader-card-bg: #f0f4f1;
        --reader-text: #1c3d27;
        --reader-text-muted: #4e6b54;
        --reader-border: rgba(28, 61, 39, 0.15);
        --reader-header-bg: rgba(226, 235, 213, 0.85);
        --reader-sidebar-bg: #e7eedb;
        --reader-active-item: #d6e2c3;
        --reader-accent: #16a34a;
        --reader-shadow: 0 4px 6px -1px rgb(28 61 39 / 0.05);
    }

    /* Reset native dialog styles and prevent default browser scrollbars */
    #myBooksReaderModal {
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        outline: none !important;
        overflow: hidden !important;
        background: transparent !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        width: 100vw !important;
        height: 100vh !important;
    }

    /* Override DaisyUI modal box constraints specifically for the EPUB Reader modal */
    #myBooksReaderModal .modal-box {
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #myBooksReaderBox {
        background-color: var(--reader-bg) !important;
        color: var(--reader-text) !important;
        overflow: hidden !important;
    }

    /* Fullscreen Mode Overrides (Prevent default browser black background or backdrop override) */
    /* NOTE: #myBooksReaderBox is the actual fullscreen element (.modal-box), so its ::backdrop must also be targeted */
    #myBooksReaderModal:fullscreen,
    #myBooksReaderBox:fullscreen {
        background-color: var(--reader-bg) !important;
        background: var(--reader-bg) !important;
        color: var(--reader-text) !important;
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        border: none !important;
        outline: none !important;
        display: flex !important;
        flex-direction: column !important;
    }

    /* Fix black backdrop behind fullscreen element */
    #myBooksReaderModal::backdrop,
    #myBooksReaderBox::backdrop {
        background-color: var(--reader-bg, #f8fafc) !important;
        background: var(--reader-bg, #f8fafc) !important;
    }

    #myBooksReaderModal:-webkit-full-screen,
    #myBooksReaderBox:-webkit-full-screen {
        background-color: var(--reader-bg) !important;
        background: var(--reader-bg) !important;
        color: var(--reader-text) !important;
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        border: none !important;
        outline: none !important;
        display: -webkit-flex !important;
        display: flex !important;
        -webkit-flex-direction: column !important;
        flex-direction: column !important;
    }

    #myBooksReaderModal:-webkit-full-screen-backdrop,
    #myBooksReaderBox::-webkit-backdrop {
        background-color: var(--reader-bg, #f8fafc) !important;
        background: var(--reader-bg, #f8fafc) !important;
    }

    #myBooksReaderModal:-moz-full-screen,
    #myBooksReaderBox:-moz-full-screen {
        background-color: var(--reader-bg) !important;
        background: var(--reader-bg) !important;
        color: var(--reader-text) !important;
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        border: none !important;
        outline: none !important;
        display: flex !important;
        flex-direction: column !important;
    }

    #myBooksReaderModal:-ms-fullscreen,
    #myBooksReaderBox:-ms-fullscreen {
        background-color: var(--reader-bg) !important;
        background: var(--reader-bg) !important;
        color: var(--reader-text) !important;
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        border: none !important;
        outline: none !important;
        display: flex !important;
        flex-direction: column !important;
    }

    /* Modal Top Header */
    .reader-header {
        position: relative;
        z-index: 100;
        background: var(--reader-header-bg) !important;
        border-bottom: 1px solid var(--reader-border) !important;
        color: var(--reader-text) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .reader-header button, .reader-header select {
        color: var(--reader-text) !important;
    }

    .reader-header button:hover {
        background-color: var(--reader-active-item) !important;
    }

    /* Table of Contents Sidebar */
    .reader-sidebar {
        background-color: var(--reader-sidebar-bg) !important;
        border-right: 1px solid var(--reader-border) !important;
        color: var(--reader-text) !important;
        width: 320px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .reader-sidebar.collapsed {
        width: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
        border-right-width: 0 !important;
    }

    .reader-sidebar-tab-btn {
        border-bottom: 2px solid transparent;
        color: var(--reader-text-muted);
        transition: all 0.2s ease;
    }

    .reader-sidebar-tab-btn.active {
        border-bottom-color: var(--reader-accent) !important;
        color: var(--reader-accent) !important;
        font-weight: 700;
    }

    .toc-item {
        padding: 10px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--reader-text);
        line-height: 1.4;
    }

    .toc-item:hover {
        background-color: var(--reader-active-item);
    }

    .toc-item.active {
        background-color: var(--reader-active-item);
        color: var(--reader-accent);
        font-weight: 700;
    }

    .highlight-card {
        background-color: var(--reader-card-bg);
        border: 1px solid var(--reader-border);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        box-shadow: var(--reader-shadow);
        transition: all 0.2s ease;
    }

    .highlight-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    /* Viewport layout */
    .reader-viewport {
        background-color: var(--reader-bg);
        transition: all 0.3s ease;
    }

    /* Double-layered card page effect */
    .reader-page-card {
        background-color: var(--reader-card-bg) !important;
        border: 1px solid var(--reader-border) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12), 0 0 0 1px var(--reader-border) !important;
        transition: all 0.3s ease;
    }

    /* Floating navigation margins */
    .floating-nav-btn {
        background-color: var(--reader-card-bg);
        border: 1px solid var(--reader-border);
        color: var(--reader-text);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        opacity: 0.3;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .floating-nav-btn:hover {
        opacity: 0.95;
        transform: scale(1.05) translateY(-50%);
    }

    /* Settings Panel */
    .settings-popover {
        background-color: var(--reader-sidebar-bg);
        border: 1px solid var(--reader-border);
        color: var(--reader-text);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.15);
        border-radius: 12px;
        padding: 16px;
        width: 280px;
        z-index: 50;
        transition: all 0.2s ease;
    }

    /* Premium inputs & dropdown styling for the settings popover */
    .settings-popover select {
        background-color: var(--reader-bg) !important;
        color: var(--reader-text) !important;
        border: 1px solid var(--reader-border) !important;
        outline: none !important;
        cursor: pointer;
    }
    
    .settings-popover select:focus {
        border-color: var(--reader-accent) !important;
        box-shadow: 0 0 0 2px var(--reader-active-item) !important;
    }

    .settings-popover option {
        background-color: var(--reader-sidebar-bg) !important;
        color: var(--reader-text) !important;
    }

    /* Layout Toggle styling */
    .layout-toggle-container {
        background-color: var(--reader-bg) !important;
        border: 1px solid var(--reader-border) !important;
        padding: 4px;
        border-radius: 8px;
        display: flex;
        gap: 4px;
    }

    .layout-toggle-btn {
        flex: 1;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s ease;
        background: transparent;
        color: var(--reader-text-muted);
        border: none;
        cursor: pointer;
    }

    .layout-toggle-btn:hover {
        background-color: var(--reader-active-item);
        color: var(--reader-text);
    }

    .layout-toggle-btn.active {
        background-color: var(--reader-accent) !important;
        color: #ffffff !important;
    }

    [data-reader-theme="sepia"] .layout-toggle-btn.active,
    [data-reader-theme="sage"] .layout-toggle-btn.active,
    [data-reader-theme="light"] .layout-toggle-btn.active {
        color: #ffffff !important;
    }

    /* Color mode selection dots */
    .theme-dot {
        width: 34px;
        height: 34px;
        border-radius: 9999px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }

    .theme-dot:hover {
        transform: scale(1.1);
    }

    .theme-dot.active {
        border-color: var(--reader-accent) !important;
        transform: scale(1.05);
        box-shadow: 0 0 0 2px var(--reader-active-item);
    }

    #myBooksReaderContainer {
        display: flex;
        justify-content: center;
        overflow: hidden;
        width: 100%;
        height: 100%;
    }
    
    #myBooksReaderContainer > div {
        width: 100% !important;
        height: 100% !important;
        margin: 0 auto;
    }
    
    #myBooksReaderContainer iframe {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        border: 0 !important;
    }

    /* Scroll mode vs paginated structural class overrides */
    .scrolled-mode #myBooksReaderContainer {
        overflow-y: auto !important;
        display: block !important;
        -webkit-overflow-scrolling: touch;
    }
    
    .scrolled-mode #myBooksReaderContainer > div {
        height: auto !important;
        min-height: 100%;
    }

    /* Scrollbars matching themes */
    .reader-sidebar::-webkit-scrollbar,
    #myBooksTOCList::-webkit-scrollbar,
    #myBooksHighlightsList::-webkit-scrollbar,
    #myBooksReaderContainer::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .reader-sidebar::-webkit-scrollbar-thumb,
    #myBooksTOCList::-webkit-scrollbar-thumb,
    #myBooksHighlightsList::-webkit-scrollbar-thumb,
    #myBooksReaderContainer::-webkit-scrollbar-thumb {
        background-color: var(--reader-border);
        border-radius: 4px;
    }
</style>

<div class="max-w-[1920px] mx-auto px-2 sm:px-4 py-8" id="myBooksRoot">
    <div class="mb-12">
        <h1 class="text-5xl font-black text-on-surface tracking-tight mb-4 font-['Manrope']">My Library</h1>
        <p class="text-on-surface-variant text-lg max-w-2xl">Read your purchased and membership books online, and continue exactly where you left off.</p>
    </div>

    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold tracking-tight">Currently Reading</h2>
            <div class="h-px flex-1 bg-surface-container mx-8"></div>
            <span class="text-primary font-bold text-sm uppercase tracking-widest" id="myBooksActiveCount">0 active books</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" id="myBooksCurrentList">
            <div class="mybooks-glass-card rounded-xl p-8 text-on-surface-variant">Loading your books...</div>
        </div>
    </section>

    <section class="mb-16">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
            <div class="flex items-center gap-4 flex-1 w-full">
                <h2 class="text-2xl font-bold tracking-tight whitespace-nowrap">Your Collection</h2>
                <div class="h-px flex-1 bg-surface-container mx-4 hidden sm:block"></div>
            </div>
            <select id="myBooksCategoryFilter" class="bg-surface-container-low px-4 py-2 rounded-lg text-sm font-semibold text-on-surface border-none focus:ring-2 focus:ring-primary/40 min-w-[200px]">
                <option value="ALL">All Categories</option>
            </select>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6" id="myBooksCollectionList"></div>
    </section>

    <?php if (($session->getRole() ?? '') === 'USER'): ?>
    <section id="wishlist" class="mt-20 scroll-mt-24">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold tracking-tight">Your Wishlist</h2>
            <div class="h-px flex-1 bg-surface-container mx-8"></div>
            <span class="text-primary font-bold text-sm uppercase tracking-widest" id="myBooksWishlistCount">0 items</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-6" id="myBooksWishlistList">
            <div class="col-span-full py-12 text-center text-on-surface-variant bg-surface-container-low rounded-2xl border-2 border-dashed border-outline-variant/30">
                <span class="material-symbols-outlined text-4xl mb-3 opacity-50 block">bookmark_add</span>
                <p class="font-medium">Your wishlist is empty. Start adding books from the catalog!</p>
                <a href="<?php echo APP_ROUTE; ?>?page=books" class="btn btn-primary btn-sm mt-4">Browse Books</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<dialog id="myBooksReaderModal" class="modal">
    <div class="modal-box flex flex-col h-screen overflow-hidden transition-all duration-300" id="myBooksReaderBox" data-reader-theme="light">
        
        <!-- Reader Header -->
        <div class="reader-header px-6 py-4 flex items-center justify-between shrink-0">
            <!-- Left Header -->
            <div class="flex items-center gap-4">
                <button id="myBooksSidebarToggle" class="p-2 hover:bg-surface-container-high rounded-lg flex items-center justify-center transition-all" title="Table of Contents">
                    <span class="material-symbols-outlined block">menu_book</span>
                </button>
                <div class="min-w-0">
                    <h3 class="font-bold text-sm sm:text-base truncate max-w-[180px] sm:max-w-[320px] md:max-w-[480px]" id="myBooksReaderTitle">Book Title</h3>
                    <p class="text-[11px] font-semibold opacity-75 truncate" id="myBooksReaderMeta">Saved position available</p>
                </div>
            </div>

            <!-- Right Header Controls -->
            <div class="flex items-center gap-2 sm:gap-3">
                
                <!-- Highlights Selection Menu (shows when text selected) -->
                <button id="myBooksHighlightBtn" class="p-2 hover:bg-surface-container-high rounded-lg transition-all text-primary hidden" title="Highlight Selection">
                    <span class="material-symbols-outlined block">draw</span>
                </button>
                <button id="myBooksUnhighlightBtn" class="p-2 hover:bg-surface-container-high rounded-lg transition-all text-error hidden" title="Remove Highlight">
                    <span class="material-symbols-outlined block">ink_eraser</span>
                </button>

                <!-- Zoom Controls (Visible directly on md+ screens) -->
                <div class="hidden md:flex items-center bg-surface-container-high/40 rounded-lg border border-outline-variant/20 h-[34px] overflow-hidden">
                    <button id="myBooksZoomOut" class="h-full px-2.5 hover:bg-surface-container-lowest transition-all" title="Zoom Out">
                        <span class="material-symbols-outlined text-[16px] block">remove</span>
                    </button>
                    <span id="myBooksZoomLevel" class="text-[10px] font-black px-2.5 w-11 text-center">100%</span>
                    <button id="myBooksZoomIn" class="h-full px-2.5 hover:bg-surface-container-lowest transition-all" title="Zoom In">
                        <span class="material-symbols-outlined text-[16px] block">add</span>
                    </button>
                </div>

                <!-- Settings Toggle Button (Menu dropdown style) -->
                <div class="relative">
                    <button id="myBooksSettingsToggle" class="p-2 hover:bg-surface-container-high rounded-lg transition-all" title="Reading Settings">
                        <span class="material-symbols-outlined block">tune</span>
                    </button>
                    
                    <!-- Settings Popover Panel (Hidden by default) -->
                    <div id="myBooksSettingsPanel" class="settings-popover absolute right-0 mt-2 hidden flex-col gap-4 text-left">
                        <h4 class="text-xs font-bold uppercase tracking-wider opacity-60">Reading Settings</h4>
                        
                        <!-- Color themes selection -->
                        <div class="flex flex-col gap-1.5">
                            <span class="text-xs font-bold">Theme</span>
                            <div class="flex justify-between items-center gap-2 mt-1">
                                <button class="theme-dot active" data-theme-id="light" style="background-color: #ffffff; border-color: #e2e8f0;" title="Light Mode">
                                    <span class="text-[10px] text-slate-800 font-bold">A</span>
                                </button>
                                <button class="theme-dot" data-theme-id="sepia" style="background-color: #fdf6e3; border-color: #f4ecd8;" title="Sepia Mode">
                                    <span class="text-[10px] text-amber-900 font-bold">A</span>
                                </button>
                                <button class="theme-dot" data-theme-id="dark" style="background-color: #1e1e1e; border-color: #121212;" title="Dark Mode">
                                    <span class="text-[10px] text-gray-200 font-bold">A</span>
                                </button>
                                <button class="theme-dot" data-theme-id="sage" style="background-color: #f0f4f1; border-color: #e2ebd5;" title="Sage Mode">
                                    <span class="text-[10px] text-green-900 font-bold">A</span>
                                </button>
                            </div>
                        </div>

                        <hr class="border-outline-variant/20" />

                        <!-- Font Family Selection -->
                        <div class="flex flex-col gap-1.5">
                            <label for="myBooksFontFamily" class="text-xs font-bold">Font Family</label>
                            <select id="myBooksFontFamily" class="text-xs font-semibold bg-surface-container-high border border-outline-variant/20 rounded-lg focus:ring-2 focus:ring-primary/20 py-2 px-3 w-full">
                                <option value="sans-serif">Sans Serif</option>
                                <option value="serif">Serif</option>
                                <option value="'Inter', sans-serif">Inter</option>
                                <option value="'Manrope', sans-serif">Manrope</option>
                            </select>
                        </div>

                        <hr class="border-outline-variant/20" />

                        <!-- Flow/Layout Style (Paginated vs Scroll Mode) -->
                        <div class="flex flex-col gap-1.5">
                            <span class="text-xs font-bold">Layout Style</span>
                            <div class="layout-toggle-container mt-1">
                                <button id="myBooksFlowPaginated" class="layout-toggle-btn active" title="Paginated Reading">
                                    <span class="material-symbols-outlined text-[14px]">auto_stories</span> Paginated
                                </button>
                                <button id="myBooksFlowScrolled" class="layout-toggle-btn" title="Scroll Reading">
                                    <span class="material-symbols-outlined text-[14px]">view_headline</span> Scroll
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fullscreen & Close -->
                <button id="myBooksFullscreenBtn" class="p-2 hover:bg-surface-container-high rounded-lg transition-colors" title="Toggle Fullscreen">
                    <span class="material-symbols-outlined block">fullscreen</span>
                </button>
                <button id="myBooksCloseReaderBtn" class="btn btn-sm btn-ghost" type="button">✕</button>
            </div>
        </div>

        <!-- Reader Wrapper -->
        <div class="flex-1 flex overflow-hidden relative">
            
            <!-- Collapsible Sidebar (TOC & Highlights) -->
            <div id="myBooksReaderSidebar" class="reader-sidebar h-full flex flex-col shrink-0 overflow-hidden collapsed z-30">
                <!-- Sidebar Tabs Header -->
                <div class="flex border-b border-outline-variant/15 shrink-0 px-2">
                    <button class="reader-sidebar-tab-btn active flex-1 py-3 text-xs font-bold flex items-center justify-center gap-1.5" data-tab-id="toc">
                        <span class="material-symbols-outlined text-[16px]">list</span> Chapters
                    </button>
                    <button class="reader-sidebar-tab-btn flex-1 py-3 text-xs font-bold flex items-center justify-center gap-1.5" data-tab-id="highlights">
                        <span class="material-symbols-outlined text-[16px]">draw</span> Notes
                    </button>
                </div>
                
                <!-- TOC Panel -->
                <div id="myBooksTOCList" class="flex-1 overflow-y-auto p-4 flex flex-col gap-0.5">
                    <!-- Chapters will be rendered dynamically here -->
                    <div class="text-xs opacity-50 py-4 text-center">Loading chapters...</div>
                </div>

                <!-- Highlights Panel -->
                <div id="myBooksHighlightsList" class="flex-1 overflow-y-auto p-4 hidden">
                    <!-- Highlights will be rendered dynamically here -->
                    <div class="text-xs opacity-50 py-4 text-center">No highlights yet</div>
                </div>
            </div>

            <!-- Viewport Area (center of page) -->
            <div class="flex-1 flex flex-col justify-between items-center relative overflow-hidden reader-viewport p-4 md:p-6 select-none">
                
                <!-- Floating Left chevron (Paginated mode only) -->
                <button id="myBooksFloatingPrev" class="floating-nav-btn absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full flex items-center justify-center z-10" title="Previous Page">
                    <span class="material-symbols-outlined text-xl">chevron_left</span>
                </button>

                <!-- Simulated Paper Page Viewport -->
                <div class="reader-page-card w-full max-w-4xl flex-1 rounded-xl overflow-hidden shadow-2xl relative flex flex-col p-6 sm:p-10 md:p-14">
                    <div id="myBooksReaderContainer" class="w-full h-full relative"></div>
                </div>

                <!-- Floating Right chevron (Paginated mode only) -->
                <button id="myBooksFloatingNext" class="floating-nav-btn absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full flex items-center justify-center z-10" title="Next Page">
                    <span class="material-symbols-outlined text-xl">chevron_right</span>
                </button>

            </div>
        </div>

        <!-- Footer Navigation / Information -->
        <div class="px-6 py-3 border-t border-outline-variant/15 flex items-center justify-between shrink-0 reader-header text-xs">
            <button id="myBooksReaderPrevBtn" class="btn btn-ghost btn-xs sm:btn-sm gap-1 sm:gap-2">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
                <span id="myBooksPrevLabel">Prev</span>
            </button>
            
            <div class="flex items-center gap-4">
                <!-- Reading progress bar indicator -->
                <span class="font-bold uppercase tracking-widest text-[10px] text-center" id="myBooksReaderPageInfo">Loading pages...</span>
            </div>

            <button id="myBooksReaderNextBtn" class="btn btn-ghost btn-xs sm:btn-sm gap-1 sm:gap-2">
                <span id="myBooksNextLabel">Next</span>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
        </div>

    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script>
window.MYBOOKS_API_URL = '<?php echo APP_URL; ?>/controllers/books.php';
window.WISHLIST_API_URL = '<?php echo APP_URL; ?>/controllers/wishlist.php';
window.BROWSE_BOOKS_URL = '<?php echo APP_ROUTE; ?>?page=books';
</script>
<script src="<?php echo APP_URL; ?>/public/js/my-books.js"></script>
