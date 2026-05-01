<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.5/jszip.min.js"></script>
<script src="https://unpkg.com/epubjs/dist/epub.min.js"></script>
<style>
    .mybooks-glass-card { background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(24px); }
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
    <div class="modal-box w-11/12 max-w-6xl p-0 bg-white flex flex-col h-[90vh] overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-bold text-lg" id="myBooksReaderTitle">Reader</h3>
                <p class="text-xs text-gray-500" id="myBooksReaderMeta">Saved position available</p>
            </div>
            <form method="dialog"><button class="btn btn-sm">Close</button></form>
        </div>
        <div id="myBooksReaderContainer" class="flex-1 relative bg-gray-100"></div>
        <div class="px-5 py-3 border-t flex items-center justify-between shrink-0 bg-gray-50">
            <button id="myBooksReaderPrevBtn" class="btn btn-outline btn-sm">Previous</button>
            <span class="text-sm font-medium" id="myBooksReaderPageInfo">Loading pages...</span>
            <button id="myBooksReaderNextBtn" class="btn btn-outline btn-sm">Next</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script>
window.MYBOOKS_API_URL = '<?php echo APP_URL; ?>/controllers/books.php';
</script>
<script src="<?php echo APP_URL; ?>/public/js/my-books.js"></script>
