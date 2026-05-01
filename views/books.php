<script id="tailwind-books-lumina">
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'on-primary-fixed': '#190064',
                'on-secondary': '#ffffff',
                'surface-tint': '#5a30fb',
                'surface-dim': '#ddd8e7',
                'surface': '#fdf8ff',
                'secondary-fixed': '#dde1ff',
                'primary-container': '#4f1bf1',
                'on-primary-container': '#cac1ff',
                'surface-container-lowest': '#ffffff',
                'on-surface-variant': '#474557',
                'secondary-container': '#d6dbff',
                'on-surface': '#1c1a25',
                'surface-container-low': '#f7f1ff',
                'on-secondary-container': '#595f7e',
                'surface-container-high': '#ebe6f5',
                'background': '#fdf8ff',
                'primary': '#3800bf',
                'outline': '#787588',
                'outline-variant': '#c9c4da'
            }
        }
    }
};
</script>
<style>
    .browse-shell .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        vertical-align: middle;
    }
    .browse-switch {
        width: 42px;
        height: 24px;
        border-radius: 999px;
        background: #c9c4da;
        position: relative;
        transition: background-color .2s ease;
    }
    .browse-switch::after {
        content: '';
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #ffffff;
        position: absolute;
        top: 3px;
        left: 3px;
        transition: transform .2s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,.2);
    }
    .browse-switch.is-on {
        background: #4f1bf1;
    }
    .browse-switch.is-on::after {
        transform: translateX(18px);
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    }
</style>

<div class="browse-shell">
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-12">
        <div>
            <h1 class="text-4xl font-extrabold tracking-tight text-on-surface mb-2 font-['Manrope']">Browse Catalog</h1>
            <p class="text-on-surface-variant font-medium">Discover books curated for your reading journey.</p>
        </div>
        <div class="w-full md:w-auto md:min-w-[320px]">
            <label class="sr-only" for="browseSearchInput">Search books</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input id="browseSearchInput" class="w-full pl-10 pr-4 py-2.5 bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm transition-all" placeholder="Search by title, ISBN, publisher, or author..." type="text" />
            </div>
        </div>
    </section>

    <section class="flex flex-wrap items-center gap-4 mb-10">
        <div class="flex bg-surface-container-low p-1 rounded-lg">
            <button class="px-3 py-1.5 bg-surface-container-lowest shadow-sm rounded-md text-primary" id="browseGridBtn" type="button">
                <span class="material-symbols-outlined">grid_view</span>
            </button>
            <button class="px-3 py-1.5 text-on-surface-variant hover:text-primary transition-colors" id="browseListBtn" type="button">
                <span class="material-symbols-outlined">view_list</span>
            </button>
        </div>

        <select id="browseGenreSelect" class="bg-surface-container-low px-6 py-3 rounded-lg text-sm font-semibold text-on-surface border-none focus:ring-2 focus:ring-primary/40">
            <option value="ALL">Genre: All Categories</option>
        </select>


        <select id="browseSortSelect" class="bg-surface-container-low px-6 py-3 rounded-lg text-sm font-semibold text-on-surface border-none focus:ring-2 focus:ring-primary/40">
            <option value="recent">Recently Added</option>
            <option value="rating">Top Rated</option>
            <option value="title">Title (A-Z)</option>
            <option value="price_low">Price (Low to High)</option>
            <option value="price_high">Price (High to Low)</option>
        </select>
    </section>

    <section id="browseCatalogGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12"></section>
    <section id="browseCatalogList" class="hidden space-y-4"></section>

    <section class="mt-16 flex justify-center items-center gap-4" id="browsePagination"></section>
</div>

<div id="bookDetailOverlay" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 md:p-8">
    <div class="absolute inset-0 bg-on-surface/30 backdrop-blur-sm" id="bookDetailBackdrop"></div>
    <div class="glass-panel relative w-full max-w-6xl max-h-[96vh] overflow-y-auto rounded-[2rem] shadow-2xl flex flex-col md:flex-row overflow-hidden border border-outline-variant/15">
        <button class="absolute top-6 right-6 z-[110] p-2 bg-surface-container-highest rounded-full hover:bg-surface-dim transition-colors" id="bookDetailCloseBtn" type="button">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="md:w-5/12 bg-surface-container-low p-8 md:p-10 flex flex-col items-center justify-center relative">
            <div class="w-full max-w-[280px] aspect-[2/3] rounded-lg shadow-[0_20px_50px_rgba(0,0,0,0.2)] overflow-hidden mb-8">
                <img id="bookDetailCover" alt="Featured Book Cover" class="w-full h-full object-cover" />
            </div>
            <div class="flex gap-4 w-full">
                <div class="flex-1 text-center p-4 bg-surface-container-lowest rounded-2xl">
                    <span class="block text-xs uppercase tracking-widest text-outline mb-1">Rating</span>
                    <div class="flex justify-center text-primary">
                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="font-bold ml-1" id="bookDetailRating">0.0</span>
                    </div>
                </div>
                <div class="flex-1 text-center p-4 bg-surface-container-lowest rounded-2xl">
                    <span class="block text-xs uppercase tracking-widest text-outline mb-1">Copies</span>
                    <span class="font-bold text-on-surface" id="bookDetailCopies">0</span>
                </div>
            </div>
        </div>

        <div class="md:w-7/12 p-8 md:p-12 overflow-y-auto">
            <header class="mb-8">
                <div class="flex gap-2 mb-4">
                    <span class="px-3 py-1 bg-tertiary-fixed text-on-tertiary-fixed rounded-full text-[10px] font-bold uppercase tracking-tighter" id="bookDetailTagFeatured">Archive</span>
                    <span class="px-3 py-1 bg-secondary-container text-on-secondary-container rounded-full text-[10px] font-bold uppercase tracking-tighter" id="bookDetailTagGenre">Genre</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-on-background leading-tight mb-2" id="bookDetailTitle">Book Title</h2>
                <p class="text-lg md:text-xl text-primary font-medium italic" id="bookDetailAuthor">by Author</p>
                <p class="text-sm text-on-surface-variant font-medium mt-1" id="bookDetailPublisher">Publisher: Unknown Publisher</p>
            </header>

            <div class="space-y-10">
                <section>
                    <h3 class="text-sm font-bold uppercase tracking-widest text-outline mb-4">Synopsis</h3>
                    <p class="text-on-surface-variant leading-relaxed text-base md:text-lg" id="bookDetailSynopsis"></p>
                </section>

                <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-5 rounded-2xl bg-primary-container text-on-primary shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined mb-3 block">shopping_bag</span>
                        <h4 class="font-bold mb-1 text-white">Buy Online</h4>
                        <p class="text-xs text-on-primary-container mb-4">Own this digital EPUB</p>
                        <span class="font-bold text-white" id="bookDetailOnlinePrice">N/A</span>
                        <button id="bookDetailBuyOnlineBtn" type="button" class="mt-4 w-full py-2.5 bg-white text-primary rounded-xl font-bold text-sm">Buy with Wallet</button>
                    </div>
                    <div class="p-5 rounded-2xl bg-surface-container-high border border-transparent">
                        <span class="material-symbols-outlined text-primary mb-3 block">verified_user</span>
                        <h4 class="font-bold mb-1">Use Membership</h4>
                        <p class="text-xs text-on-surface-variant mb-4">Add to My Books with active membership</p>
                        <button id="bookDetailMembershipAccessBtn" type="button" class="w-full py-2.5 bg-primary text-white rounded-xl font-bold text-sm">Grant Access</button>
                        <a href="<?php echo APP_ROUTE; ?>?page=membership" class="mt-3 inline-block text-xs text-primary font-semibold hover:underline">Manage membership</a>
                    </div>
                </section>
                <p class="text-xs text-on-surface-variant -mt-6">Purchased or membership books can be read in-browser via EPUB reader from My Books. Downloads are disabled.</p>

                <section>
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-outline">Reader Reviews</h3>
                        <span class="text-primary font-bold text-sm" id="bookDetailReviewsCount">0 reviews</span>
                    </div>

                    <form id="bookReviewForm" class="bg-surface-container-lowest p-6 rounded-2xl mb-8 border border-outline-variant/20">
                        <input type="hidden" id="bookReviewBookId" name="book_id" />
                        <input type="hidden" id="bookReviewId" name="review_id" value="" />
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-sm font-medium text-on-surface text-left">Your Rating:</span>
                            <select id="bookReviewRating" name="rating" class="bg-surface-container-low border-none rounded-xl px-6 py-3 text-sm">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <textarea id="bookReviewText" name="review" class="w-full bg-surface-container-low border-none rounded-xl p-4 text-sm focus:ring-2 focus:ring-primary/20 h-24 resize-none mb-4" placeholder="Share your thoughts on this title..."></textarea>
                        <button id="bookReviewCancelEdit" class="w-full py-3 mb-3 border border-outline-variant text-on-surface font-bold rounded-xl text-sm transition-all hover:bg-surface-container-low hidden" type="button">
                            Cancel Edit
                        </button>
                        <button class="w-full py-3 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-white rounded-xl text-sm transition-all hover:opacity-90" type="submit">
                            Submit Review
                        </button>
                    </form>

                    <div class="space-y-5" id="bookDetailReviewsList"></div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
window.BROWSE_API_URL = '<?php echo APP_URL; ?>/controllers/books.php';
window.BROWSE_PAGE_URL = '<?php echo APP_ROUTE; ?>?page=books';
window.BROWSE_IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
window.BROWSE_CURRENT_USER_ID = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>;
window.MY_BOOKS_PAGE_URL = '<?php echo APP_ROUTE; ?>?page=my-books';
</script>
<script src="<?php echo APP_URL; ?>/public/js/books.js"></script>
