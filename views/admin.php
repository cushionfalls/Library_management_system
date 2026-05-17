<div id="adminDashboardRoot" class="space-y-10">
    <section class="mb-2">
        <h1 class="text-4xl font-extrabold tracking-tight text-on-surface mb-2 font-['Manrope']" id="adminDashboardTitle">
            <?php echo $session->isAdmin() ? 'Admin' : 'Librarian'; ?> Dashboard
        </h1>
        <p class="text-on-surface-variant text-lg">Manage catalog, members, transactions, and library operations.</p>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all">
            <p class="text-sm font-semibold text-on-surface-variant mb-1">Total Users</p>
            <h3 class="text-3xl font-black text-primary" id="adminTotalUsers">0</h3>
        </div>
        <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all">
            <p class="text-sm font-semibold text-on-surface-variant mb-1">Total Books</p>
            <h3 class="text-3xl font-black text-primary" id="adminTotalBooks">0</h3>
        </div>
        <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all">
            <p class="text-sm font-semibold text-on-surface-variant mb-1">Total Memberships</p>
            <h3 class="text-3xl font-black text-primary" id="adminTotalMemberships">0</h3>
        </div>
        <div class="bg-surface-container-low p-6 rounded-xl hover:bg-surface-container-highest transition-all">
            <p class="text-sm font-semibold text-on-surface-variant mb-1">Total Income</p>
            <h3 class="text-3xl font-black text-primary" id="adminWalletCreditsToday">$0.00</h3>
        </div>
    </section>

    <section class="bg-surface-container p-1 rounded-full w-fit flex flex-wrap items-center gap-1">
        <button class="admin-tab-btn px-8 py-2.5 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-lg shadow-primary/25" data-tab="books">Books</button>
        <button class="admin-tab-btn px-8 py-2.5 rounded-full text-on-surface-variant text-sm font-semibold hover:bg-surface-container-highest transition-colors" data-tab="users">Users</button>
        <button class="admin-tab-btn px-8 py-2.5 rounded-full text-on-surface-variant text-sm font-semibold hover:bg-surface-container-highest transition-colors" data-tab="transactions">Transactions</button>
        <button class="admin-tab-btn px-8 py-2.5 rounded-full text-on-surface-variant text-sm font-semibold hover:bg-surface-container-highest transition-colors" data-tab="analytics">Analytics</button>
    </section>

    <?php require __DIR__ . '/partials/admin_search_bar.php'; ?>

    <section id="adminSectionBooks" class="admin-tab-panel bg-surface-container-lowest rounded-2xl shadow-xl shadow-primary/10 p-8 border border-outline-variant/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-on-surface font-['Manrope']">Books catalog</h2>
                <p class="text-on-surface-variant">Manage titles, stock levels, and pricing for the library.</p>
            </div>
            <button class="bg-primary hover:bg-primary-container text-on-primary px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition-all active:scale-95" id="adminAddBookBtn">
                <span class="material-symbols-outlined">add</span>
                Add New Book
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b border-outline-variant/30">
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">ISBN</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Cover Image</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Name</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Publisher</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Author</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Price</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminBooksBody" class="divide-y divide-outline-variant/20">
                    <tr><td colspan="7" class="px-4 py-6 text-center text-on-surface-variant">Loading books...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-8 flex justify-center">
            <button id="adminLoadMoreBooksBtn" class="px-8 py-3 bg-surface-container-high hover:bg-surface-container-highest text-primary font-bold rounded-xl transition-all hidden">Load More Books</button>
        </div>
    </section>

    <section id="adminSectionUsers" class="admin-tab-panel hidden bg-surface-container-lowest rounded-2xl shadow-xl shadow-primary/10 p-8 border border-outline-variant/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-on-surface font-['Manrope']">Users</h2>
                <p class="text-on-surface-variant">Manage users, roles, and account status.</p>
            </div>
            <button class="bg-primary hover:bg-primary-container text-on-primary px-6 py-3 rounded-xl font-bold items-center gap-2 transition-all active:scale-95 text-sm <?php echo $session->isLibrarian() ? 'hidden' : 'flex'; ?>" id="adminAddUserBtn">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                Add User
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b border-outline-variant/30">
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Name</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Email</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Role</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Status</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Joined</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminUsersBody" class="divide-y divide-outline-variant/20">
                    <tr><td colspan="6" class="px-4 py-6 text-center text-on-surface-variant">Loading users...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-8 flex justify-center">
            <button id="adminLoadMoreUsersBtn" class="px-8 py-3 bg-surface-container-high hover:bg-surface-container-highest text-primary font-bold rounded-xl transition-all hidden">Load More Users</button>
        </div>
    </section>

    <section id="adminSectionTransactions" class="admin-tab-panel hidden bg-surface-container-lowest rounded-2xl shadow-xl shadow-primary/10 p-8 border border-outline-variant/20">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-on-surface font-['Manrope']">Transactions</h2>
                    <p class="text-on-surface-variant">Review activity, due dates, and payment details.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-surface-container p-1 rounded-xl flex items-center gap-1">
                        <button class="admin-tx-filter-btn px-4 py-2 rounded-lg bg-primary text-on-primary text-xs font-bold transition-all" data-filter="all">All</button>
                        <button class="admin-tx-filter-btn px-4 py-2 rounded-lg text-on-surface-variant text-xs font-bold hover:bg-surface-container-highest transition-all" data-filter="BOOK_BUY">Purchased</button>
                        <button class="admin-tx-filter-btn px-4 py-2 rounded-lg text-on-surface-variant text-xs font-bold hover:bg-surface-container-highest transition-all" data-filter="MEMBERSHIP">Membership</button>
                        <button class="admin-tx-filter-btn px-4 py-2 rounded-lg text-on-surface-variant text-xs font-bold hover:bg-surface-container-highest transition-all" data-filter="TOP_UP">Top Up</button>
                    </div>
                    <button class="bg-primary hover:bg-primary-container text-on-primary px-5 py-2.5 rounded-xl font-bold flex items-center gap-2 transition-all active:scale-95 text-sm" id="adminExportTransactionsBtn">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Export CSV
                    </button>
                </div>
            </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b border-outline-variant/30">
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Book</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">User</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Type</th>
                        <th class="pb-4 pt-0 font-bold text-on-surface-variant uppercase text-xs tracking-wider px-4">Amount</th>
                    </tr>
                </thead>
                <tbody id="adminTransactionsBody" class="divide-y divide-outline-variant/20">
                    <tr><td colspan="6" class="px-4 py-6 text-center text-on-surface-variant">Loading transactions...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-8 flex justify-center">
            <button id="adminLoadMoreTransactionsBtn" class="px-8 py-3 bg-surface-container-high hover:bg-surface-container-highest text-primary font-bold rounded-xl transition-all hidden">Load More Transactions</button>
        </div>
    </section>

    <section id="adminSectionAnalytics" class="admin-tab-panel hidden space-y-8">
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">card_membership</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-on-surface-variant uppercase tracking-wider">Active Memberships</p>
                        <h3 class="text-3xl font-black text-on-surface" id="statActiveMemberships">0</h3>
                    </div>
                </div>
            </div>
            <div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined">person_add</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-on-surface-variant uppercase tracking-wider">New Registrations (7d)</p>
                        <h3 class="text-3xl font-black text-on-surface" id="statNewUsers">0</h3>
                    </div>
                </div>
            </div>
            <div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-tertiary/10 flex items-center justify-center text-tertiary">
                        <span class="material-symbols-outlined">shopping_cart</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-on-surface-variant uppercase tracking-wider">Books Purchased (Month)</p>
                        <h3 class="text-3xl font-black text-on-surface" id="statMonthlyPurchases">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-surface-container-lowest p-8 rounded-3xl border border-outline-variant/20 shadow-xl lg:col-span-2">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">insights</span>
                    Revenue Trend (Last 30 Days)
                </h3>
                <div id="revenueChart" class="w-full h-[350px]"></div>
            </div>
            
            <div class="bg-surface-container-lowest p-8 rounded-3xl border border-outline-variant/20 shadow-xl">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">trending_up</span>
                    Top 5 Best Sellers
                </h3>
                <div id="topBooksChart" class="w-full h-[350px]"></div>
            </div>

            <div class="bg-surface-container-lowest p-8 rounded-3xl border border-outline-variant/20 shadow-xl">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-tertiary">pie_chart</span>
                    Genre Distribution
                </h3>
                <div id="genreChart" class="w-full h-[350px]"></div>
            </div>
        </div>
    </section>

</div>

<dialog id="adminBookModal" class="modal">
    <div class="modal-box max-w-6xl p-0 bg-surface-container-lowest border border-outline-variant/50 shadow-2xl overflow-hidden">
        <form id="adminBookForm" class="max-h-[92vh] flex flex-col">
            <input type="hidden" id="adminBookId" name="id" />
            <input type="hidden" id="adminBookExistingCoverImage" name="existing_cover_image" />
            <input type="hidden" id="adminBookExistingOnlinePdf" name="existing_online_copy_pdf" />

            <header class="flex justify-between items-center px-8 pt-8 pb-6 border-b border-outline-variant/30 bg-surface-container-lowest sticky top-0 z-20">
                <div>
                    <h3 class="text-2xl font-black tracking-tight text-primary font-['Manrope']" id="adminBookModalTitle">Add New Entry</h3>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="adminBookCancelBtn" class="px-5 py-2 text-sm font-bold text-on-surface-variant hover:text-on-surface transition-colors">Cancel</button>
                    <button type="submit" id="adminBookSaveBtn" class="px-7 py-2.5 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:brightness-110 active:scale-95 transition-all text-sm">Save Book</button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <aside class="lg:col-span-4 space-y-8">
                        <section class="space-y-4">
                            <label class="block text-sm font-semibold text-on-surface-variant uppercase tracking-wider">Cover Image <span class="text-primary font-bold">*</span></label>
                            <div class="group relative aspect-[3/4] w-full bg-surface-container rounded-xl overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-outline-variant hover:border-primary transition-all">
                                <img id="adminBookCoverPreview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Book cover preview" />
                                <div id="adminBookCoverPlaceholder" class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-primary mb-2">add_a_photo</span>
                                    <span class="text-sm font-medium text-primary">Upload Cover</span>
                                </div>
                                <input class="absolute inset-0 opacity-0 cursor-pointer" id="adminBookCoverImage" name="cover_image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,.heic,.heif" />
                            </div>
                            <p class="text-xs text-on-surface-variant text-center">Supported: JPG, PNG, GIF, WEBP. Max 5MB.</p>
                        </section>

                        <section class="space-y-4">
                            <label class="block text-sm font-semibold text-on-surface-variant uppercase tracking-wider">Online Copy EPUB <span class="text-primary font-bold">*</span></label>
                            <div class="p-6 bg-surface-container-low rounded-xl border border-outline-variant/50 flex flex-col items-center text-center">
                                <span class="material-symbols-outlined text-3xl text-secondary mb-3">menu_book</span>
                                <span class="text-sm font-medium text-on-surface mb-4" id="adminBookPdfFilename">No file selected</span>
                                <label class="w-full py-2.5 px-4 bg-surface-container-high text-primary font-semibold rounded-lg hover:bg-surface-container-highest transition-colors text-sm cursor-pointer">
                                    Choose File
                                    <input class="hidden" id="adminBookOnlinePdf" name="online_copy_pdf" type="file" accept=".epub" />
                                </label>
                            </div>
                        </section>
                    </aside>

                    <main class="lg:col-span-8">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1 space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">ISBN</label>
                                    <div class="flex gap-2">
                                        <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookIsbn" name="isbn" placeholder="978-..." required type="text"/>
                                        <button class="bg-primary text-on-primary px-3 rounded-lg hover:opacity-90 transition-opacity" type="button" id="adminBookAutofillBtn" title="Autofill from Google Books">
                                            <span class="material-symbols-outlined text-xl align-middle">auto_fix</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Book Name</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookName" name="name" placeholder="Enter full title" required type="text"/>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-bold text-on-surface">Description</label>
                                <textarea class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookDescription" name="description" placeholder="Brief summary of the book content..." rows="4"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Author</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookAuthor" name="author" placeholder="e.g. George Orwell" required type="text"/>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Publisher</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookPublisher" name="publisher" placeholder="e.g. Penguin Random House" required type="text"/>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Published Date</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookPublishedAt" name="published_at" type="date"/>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Language</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookLanguage" name="language" placeholder="English" type="text"/>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Genre</label>
                                    <select class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3 appearance-none" id="adminBookGenre" name="genre">
                                        <option value="FANTASY">Fantasy</option>
                                        <option value="SCIENCE_FICTION">Science Fiction</option>
                                        <option value="MYSTERY">Mystery</option>
                                        <option value="ROMANCE">Romance</option>
                                        <option value="THRILLER">Thriller</option>
                                        <option value="NON_FICTION">Non-Fiction</option>
                                        <option value="BIOGRAPHY">Biography</option>
                                        <option value="HISTORY">History</option>
                                        <option value="OTHERS" selected>Others</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-outline-variant/30">
                                <h3 class="text-sm font-semibold text-primary uppercase tracking-widest mb-4">Pricing</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-on-surface-variant">Buy Online (USD)</label>
                                        <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminBookOnlineBuyPrice" name="online_buy_price" min="0" step="0.01" placeholder="0.00" type="number"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>


        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close">close</button></form>
</dialog>

<dialog id="adminUserModal" class="modal">
    <div class="modal-box max-w-6xl p-0 bg-surface-container-lowest border border-outline-variant/50 shadow-2xl overflow-hidden">
        <form id="adminUserForm" class="max-h-[92vh] flex flex-col">
            <input type="hidden" id="adminUserId" name="id" />
            <input type="hidden" id="adminUserExistingProfileImage" name="existing_profile_image" />
            <header class="flex justify-between items-center px-8 pt-8 pb-6 border-b border-outline-variant/30 bg-surface-container-lowest sticky top-0 z-20">
                <div>
                    <h3 class="text-2xl font-black tracking-tight text-primary font-['Manrope']" id="adminUserModalTitle">Add New Member</h3>
                    <p class="text-on-surface-variant text-xs mt-0.5" id="adminUserModalSubtitle">Create a user account for this branch.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="adminUserCancelBtn" class="px-5 py-2 text-sm font-bold text-on-surface-variant hover:text-on-surface transition-colors">Cancel</button>
                    <button type="submit" id="adminUserSaveBtn" class="px-7 py-2.5 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:brightness-110 active:scale-95 transition-all text-sm">Create User</button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <aside class="lg:col-span-4 space-y-8">
                        <section class="space-y-4">
                            <label class="block text-sm font-semibold text-on-surface-variant uppercase tracking-wider">Profile Picture</label>
                            <div class="group relative aspect-square w-full max-w-[320px] mx-auto bg-surface-container rounded-xl overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-outline-variant hover:border-primary transition-all">
                                <img id="adminUserProfilePreview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Profile preview" />
                                <div id="adminUserProfilePlaceholder" class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-primary mb-2">account_circle</span>
                                    <span class="text-sm font-medium text-primary">Upload Photo</span>
                                </div>
                                <input class="absolute inset-0 opacity-0 cursor-pointer" id="adminUserProfileImage" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,.heic,.heif,image/*" />
                            </div>
                            <p class="text-xs text-on-surface-variant text-center">Supported: JPG, PNG, GIF, WEBP. Max 5MB.</p>
                        </section>
                    </aside>

                    <main class="lg:col-span-8">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">First Name</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserFirstName" name="first_name" required />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Last Name</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserLastName" name="last_name" required />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-bold text-on-surface">Email</label>
                                <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserEmail" name="email" type="email" required />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Phone Number</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserPhone" name="phone_number" type="tel" placeholder="98XXXXXXXX" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Date of Birth</label>
                                    <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserDob" name="dob" type="date" />
                                </div>
                            </div>

                            <div id="adminUserPasswordBlock" class="space-y-1.5">
                                <label class="text-sm font-bold text-on-surface">Password</label>
                                <input class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserPassword" name="password" type="password" minlength="6" />
                                <p class="text-xs text-on-surface-variant">Minimum 6 characters.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Role</label>
                                    <select class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserRole" name="role" required>
                                        <option value="USER">USER</option>
                                        <option value="LIBRARIAN">LIBRARIAN</option>
                                        <option value="ADMIN">ADMIN</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-on-surface">Status</label>
                                    <select class="w-full bg-surface-container-high border-none rounded-lg focus:ring-2 focus:ring-primary/40 text-sm py-3" id="adminUserStatus" name="is_active" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close">close</button></form>
</dialog>

<script>
window.ADMIN_API_URL = '<?php echo APP_URL; ?>/controllers/admin.php';
window.ADMIN_SEARCH_API_URL = '<?php echo APP_URL; ?>/controllers/adminsearchs.php';
window.IS_LIBRARIAN = <?php echo $session->isLibrarian() ? 'true' : 'false'; ?>;
window.IS_ADMIN = <?php echo $session->isAdmin() ? 'true' : 'false'; ?>;
window.CSRF_TOKEN = '<?php echo $session->generateCSRFToken(); ?>';
</script>
<script src="<?php echo APP_URL; ?>/public/js/admin.js"></script>
<script src="<?php echo APP_URL; ?>/public/js/adminsearch.js"></script>
