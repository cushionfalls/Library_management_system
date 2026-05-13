<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.5/jszip.min.js"></script>
<script src="https://unpkg.com/epubjs/dist/epub.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
    .mybooks-glass-card { background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(24px); }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    
    #myBooksReaderContainer {
        display: flex;
        justify-content: center;
        background-color: #f1f5f9;
        overflow: hidden;
    }
    #myBooksReaderContainer > div {
        background: white;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        width: 100%;
        height: 100%;
        margin: 0 auto;
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

    <section>
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
</div>

<dialog id="myBooksReaderModal" class="modal">
    <div class="modal-box w-11/12 max-w-7xl p-0 bg-surface-container-lowest flex flex-col h-[90vh] overflow-hidden">
        <div class="px-5 py-4 border-b border-outline-variant/20 flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-bold text-lg text-on-surface" id="myBooksReaderTitle">Reader</h3>
                <p class="text-xs text-on-surface-variant" id="myBooksReaderMeta">Saved position available</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="myBooksHighlightBtn" class="p-2 hover:bg-surface-container-high rounded-lg transition-all text-primary hidden" title="Highlight Selection">
                    <span class="material-symbols-outlined block">draw</span>
                </button>
                <button id="myBooksUnhighlightBtn" class="p-2 hover:bg-surface-container-high rounded-lg transition-all text-error hidden" title="Remove Highlight">
                    <span class="material-symbols-outlined block">ink_eraser</span>
                </button>
                <div class="hidden sm:flex items-center bg-surface-container-high rounded-lg border border-outline-variant/40 h-[34px] overflow-hidden">
                    <button id="myBooksZoomOut" class="h-full px-2 hover:bg-surface-container-lowest transition-all active:scale-90 border-r border-outline-variant/30" title="Zoom Out">
                        <span class="material-symbols-outlined text-[18px] block">remove</span>
                    </button>
                    <span id="myBooksZoomLevel" class="text-[10px] font-black px-2 w-10 text-center text-on-surface-variant">100%</span>
                    <button id="myBooksZoomIn" class="h-full px-2 hover:bg-surface-container-lowest transition-all active:scale-90 border-l border-outline-variant/30" title="Zoom In">
                        <span class="material-symbols-outlined text-[18px] block">add</span>
                    </button>
                </div>
                <select id="myBooksFontFamily" class="hidden sm:block text-[11px] font-bold bg-surface-container-high border border-outline-variant/40 rounded-lg focus:ring-2 focus:ring-primary/20 py-1.5 px-3">
                    <option value="sans-serif">Sans Serif</option>
                    <option value="serif">Serif</option>
                    <option value="'Inter', sans-serif">Inter</option>
                    <option value="'Manrope', sans-serif">Manrope</option>
                </select>
                <button id="myBooksFullscreenBtn" class="p-2 hover:bg-surface-container-high rounded-lg transition-colors" title="Toggle Fullscreen">
                    <span class="material-symbols-outlined block">fullscreen</span>
                </button>
                <form method="dialog"><button class="btn btn-sm btn-ghost">✕</button></form>
            </div>
        </div>
        <div id="myBooksReaderContainer" class="flex-1 relative bg-surface-dim shadow-inner"></div>
        <div class="px-5 py-3 border-t border-outline-variant/20 flex items-center justify-between shrink-0 bg-surface-container-lowest">
            <button id="myBooksReaderPrevBtn" class="btn btn-ghost btn-sm gap-2">
                <span class="material-symbols-outlined">chevron_left</span>
                Previous
            </button>
            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-widest" id="myBooksReaderPageInfo">Loading pages...</span>
            <button id="myBooksReaderNextBtn" class="btn btn-ghost btn-sm gap-2">
                Next
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script>
window.MYBOOKS_API_URL = '<?php echo APP_URL; ?>/controllers/books.php';
</script>
<script src="<?php echo APP_URL; ?>/public/js/my-books.js"></script>
