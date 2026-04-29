<div id="adminDashboardRoot" class="space-y-10">
    <section class="mb-2">
        <h1 class="text-4xl font-extrabold tracking-tight text-[#1c1a25] mb-2 font-['Manrope']">Admin Dashboard</h1>
        <p class="text-[#474557] text-lg">Manage catalog, members, transactions, and library operations.</p>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <div class="bg-[#f7f1ff] p-6 rounded-xl hover:bg-[#e5e0f0] transition-all">
            <p class="text-sm font-semibold text-[#474557] mb-1">Total Users</p>
            <h3 class="text-3xl font-black text-[#3800bf]" id="adminTotalUsers">0</h3>
        </div>
        <div class="bg-[#f7f1ff] p-6 rounded-xl hover:bg-[#e5e0f0] transition-all">
            <p class="text-sm font-semibold text-[#474557] mb-1">Total Books</p>
            <h3 class="text-3xl font-black text-[#3800bf]" id="adminTotalBooks">0</h3>
        </div>
        <div class="bg-[#f7f1ff] p-6 rounded-xl hover:bg-[#e5e0f0] transition-all">
            <p class="text-sm font-semibold text-[#474557] mb-1">Overdue Books</p>
            <h3 class="text-3xl font-black text-[#3800bf]" id="adminOverdueBooks">0</h3>
        </div>
        <div class="bg-[#f7f1ff] p-6 rounded-xl hover:bg-[#e5e0f0] transition-all">
            <p class="text-sm font-semibold text-[#474557] mb-1">Unpaid Fines</p>
            <h3 class="text-3xl font-black text-[#3800bf]" id="adminPendingFines">0</h3>
        </div>
        <div class="bg-[#f7f1ff] p-6 rounded-xl hover:bg-[#e5e0f0] transition-all">
            <p class="text-sm font-semibold text-[#474557] mb-1">Active Rentals</p>
            <h3 class="text-3xl font-black text-[#3800bf]" id="adminActiveRentals">0</h3>
        </div>
        <div class="bg-[#f7f1ff] p-6 rounded-xl hover:bg-[#e5e0f0] transition-all">
            <p class="text-sm font-semibold text-[#474557] mb-1">Total Income</p>
            <h3 class="text-3xl font-black text-[#3800bf]" id="adminWalletCreditsToday">₹0</h3>
        </div>
    </section>

    <section class="bg-[#f1ebfb] p-1 rounded-full w-fit flex flex-wrap items-center gap-1">
        <button class="admin-tab-btn px-8 py-2.5 rounded-full bg-[#3800bf] text-white text-sm font-semibold shadow-lg shadow-[#3800bf]/20" data-tab="books">Books</button>
        <button class="admin-tab-btn px-8 py-2.5 rounded-full text-[#474557] text-sm font-semibold hover:bg-[#e5e0f0] transition-colors" data-tab="users">Users</button>
        <button class="admin-tab-btn px-8 py-2.5 rounded-full text-[#474557] text-sm font-semibold hover:bg-[#e5e0f0] transition-colors" data-tab="transactions">Transactions</button>
        <button class="admin-tab-btn px-8 py-2.5 rounded-full text-[#474557] text-sm font-semibold hover:bg-[#e5e0f0] transition-colors" data-tab="overdue">Overdue</button>
    </section>

    <?php require __DIR__ . '/partials/admin_search_bar.php'; ?>

    <section id="adminSectionBooks" class="admin-tab-panel bg-white rounded-2xl shadow-xl shadow-[#5a30fb]/5 p-8 border border-[#c9c4da]/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-[#1c1a25] font-['Manrope']">Books catalog</h2>
                <p class="text-[#474557]">Manage titles, stock levels, and pricing for the library.</p>
            </div>
            <button class="bg-[#3800bf] hover:bg-[#4f1bf1] text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition-all active:scale-95" id="adminAddBookBtn">
                <span class="material-symbols-outlined">add</span>
                Add New Book
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b border-[#c9c4da]/30">
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">ISBN</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Cover Image</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Name</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Publisher</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Author</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Price</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminBooksBody" class="divide-y divide-[#c9c4da]/20">
                    <tr><td colspan="7" class="px-4 py-6 text-center text-[#474557]">Loading books...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="adminSectionUsers" class="admin-tab-panel hidden bg-white rounded-2xl shadow-xl shadow-[#5a30fb]/5 p-8 border border-[#c9c4da]/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-[#1c1a25] font-['Manrope']">Users</h2>
                <p class="text-[#474557]">Manage users, roles, and account status.</p>
            </div>
            <button class="bg-[#3800bf] hover:bg-[#4f1bf1] text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition-all active:scale-95 text-sm" id="adminAddUserBtn">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                Add User
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b border-[#c9c4da]/30">
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Name</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Email</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Role</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Status</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Joined</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminUsersBody" class="divide-y divide-[#c9c4da]/20">
                    <tr><td colspan="6" class="px-4 py-6 text-center text-[#474557]">Loading users...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="adminSectionTransactions" class="admin-tab-panel hidden bg-white rounded-2xl shadow-xl shadow-[#5a30fb]/5 p-8 border border-[#c9c4da]/20">
        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-[#1c1a25] font-['Manrope']">Transactions</h2>
            <p class="text-[#474557]">Review activity, due dates, and payment details.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b border-[#c9c4da]/30">
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Book</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">User</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Type</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Amount</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Due Date</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Status</th>
                    </tr>
                </thead>
                <tbody id="adminTransactionsBody" class="divide-y divide-[#c9c4da]/20">
                    <tr><td colspan="6" class="px-4 py-6 text-center text-[#474557]">Loading transactions...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="adminSectionOverdue" class="admin-tab-panel hidden bg-white rounded-2xl shadow-xl shadow-[#5a30fb]/5 p-8 border border-[#c9c4da]/20">
        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-[#1c1a25] font-['Manrope']">Overdue books</h2>
            <p class="text-[#474557]">Track books that have crossed due dates.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b border-[#c9c4da]/30">
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Book</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">User</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Due Date</th>
                        <th class="pb-4 pt-0 font-bold text-[#474557] uppercase text-xs tracking-wider px-4">Rented On</th>
                    </tr>
                </thead>
                <tbody id="adminOverdueBody" class="divide-y divide-[#c9c4da]/20">
                    <tr><td colspan="4" class="px-4 py-6 text-center text-[#474557]">Loading overdue books...</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<dialog id="adminBookModal" class="modal">
    <div class="modal-box max-w-6xl p-0 bg-white border border-[#d7d2e7] shadow-2xl overflow-hidden">
        <form id="adminBookForm" class="max-h-[92vh] flex flex-col">
            <input type="hidden" id="adminBookId" name="id" />
            <input type="hidden" id="adminBookExistingCoverImage" name="existing_cover_image" />
            <input type="hidden" id="adminBookExistingOnlinePdf" name="existing_online_copy_pdf" />

            <header class="flex justify-between items-center px-8 pt-8 pb-6 border-b border-[#ece8f7]">
                <div>
                    <h3 class="text-3xl font-extrabold tracking-tight text-[#3800bf] font-['Manrope']" id="adminBookModalTitle">Add New Entry</h3>
                    
                </div>
                <button type="button" id="adminBookCloseBtn" class="text-[#595c5d] hover:bg-[#f1ebfb] p-2 rounded-full transition-colors">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <aside class="lg:col-span-4 space-y-8">
                        <section class="space-y-4">
                            <label class="block text-sm font-semibold text-[#595c5d] uppercase tracking-wider">Cover Image</label>
                            <div class="group relative aspect-[3/4] w-full bg-[#f1ebfb] rounded-xl overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-[#c9c4da] hover:border-[#5a30fb] transition-all">
                                <img id="adminBookCoverPreview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Book cover preview" />
                                <div id="adminBookCoverPlaceholder" class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-[#3800bf] mb-2">add_a_photo</span>
                                    <span class="text-sm font-medium text-[#3800bf]">Upload Cover</span>
                                </div>
                                <input class="absolute inset-0 opacity-0 cursor-pointer" id="adminBookCoverImage" name="cover_image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,.heic,.heif" />
                            </div>
                            <p class="text-xs text-[#595c5d] text-center">Supported: JPG, PNG, GIF, WEBP. Max 5MB.</p>
                        </section>

                        <section class="space-y-4">
                            <label class="block text-sm font-semibold text-[#595c5d] uppercase tracking-wider">Online Copy PDF</label>
                            <div class="p-6 bg-[#f7f1ff] rounded-xl border border-[#d7d2e7] flex flex-col items-center text-center">
                                <span class="material-symbols-outlined text-3xl text-[#575d7c] mb-3">picture_as_pdf</span>
                                <span class="text-sm font-medium text-[#1c1a25] mb-4" id="adminBookPdfFilename">No file selected</span>
                                <label class="w-full py-2.5 px-4 bg-[#ebe6f5] text-[#3800bf] font-semibold rounded-lg hover:bg-[#e5e0f0] transition-colors text-sm cursor-pointer">
                                    Choose File
                                    <input class="hidden" id="adminBookOnlinePdf" name="online_copy_pdf" type="file" accept=".pdf" />
                                </label>
                            </div>
                        </section>
                    </aside>

                    <main class="lg:col-span-8">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1 space-y-1.5">
                                    <label class="text-sm font-bold text-[#1c1a25]">ISBN</label>
                                    <div class="flex gap-2">
                                        <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookIsbn" name="isbn" placeholder="978-..." required type="text"/>
                                        <button class="bg-[#3800bf] text-white px-3 rounded-lg hover:opacity-90 transition-opacity" type="button" id="adminBookAutofillBtn" title="Autofill from Google Books">
                                            <span class="material-symbols-outlined text-xl align-middle">auto_fix</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="text-sm font-bold text-[#1c1a25]">Book Name</label>
                                    <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookName" name="name" placeholder="Enter full title" required type="text"/>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-bold text-[#1c1a25]">Description</label>
                                <textarea class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookDescription" name="description" placeholder="Brief summary of the book content..." rows="4"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-[#1c1a25]">Author</label>
                                    <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookAuthor" name="author" placeholder="e.g. George Orwell" required type="text"/>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-[#1c1a25]">Publisher</label>
                                    <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookPublisher" name="publisher" placeholder="e.g. Penguin Random House" required type="text"/>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-[#1c1a25]">Published Date</label>
                                    <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookPublishedAt" name="published_at" type="date"/>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-[#1c1a25]">Language</label>
                                    <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookLanguage" name="language" placeholder="English" type="text"/>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-bold text-[#1c1a25]">Genre</label>
                                    <select class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3 appearance-none" id="adminBookGenre" name="genre">
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

                            <div class="pt-4 border-t border-[#ece8f7]">
                                <h3 class="text-sm font-semibold text-[#3800bf] uppercase tracking-widest mb-4">Inventory &amp; Pricing</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-[#595c5d]">Copies</label>
                                        <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookCopies" name="number_of_copies" min="0" type="number" value="1"/>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-[#595c5d]">Price (NPR)</label>
                                        <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookPrice" name="price" min="0" placeholder="0" type="number" value="0"/>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-[#595c5d]">Rent (NPR)</label>
                                        <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookOnlineRentPrice" name="online_rent_price" min="0" placeholder="0" type="number"/>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-[#595c5d]">Buy (NPR)</label>
                                        <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminBookOnlineBuyPrice" name="online_buy_price" min="0" placeholder="0" type="number"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>

            <footer class="bg-[#f7f1ff] px-8 py-6 flex items-center justify-end gap-4 border-t border-[#ece8f7]">
                <button class="px-6 py-3 text-sm font-bold text-[#595c5d] hover:text-[#1c1a25] transition-colors" type="button" id="adminBookCancelBtn">
                    Cancel
                </button>
                <button class="px-10 py-3 bg-gradient-to-r from-[#3800bf] to-[#4f1bf1] text-white font-bold rounded-xl shadow-lg hover:shadow-[#5a30fb]/20 hover:scale-[1.02] active:scale-[0.98] transition-all" type="submit" id="adminBookSaveBtn">
                    Add Book
                </button>
            </footer>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close">close</button></form>
</dialog>

<dialog id="adminUserModal" class="modal">
    <div class="modal-box max-w-3xl p-0 bg-white border border-[#d7d2e7] shadow-2xl overflow-hidden">
        <form id="adminUserForm" class="max-h-[88vh] flex flex-col">
            <input type="hidden" id="adminUserId" name="id" />
            <header class="flex justify-between items-center px-8 pt-7 pb-5 border-b border-[#ece8f7]">
                <div>
                    <h3 class="text-2xl font-extrabold tracking-tight text-[#3800bf] font-['Manrope']" id="adminUserModalTitle">Add New Member</h3>
                    <p class="text-[#595c5d] text-sm mt-1" id="adminUserModalSubtitle">Create a user account for this branch.</p>
                </div>
                <button type="button" id="adminUserCloseBtn" class="text-[#595c5d] hover:bg-[#f1ebfb] p-2 rounded-full transition-colors">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto px-8 py-7 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-bold text-[#1c1a25]">First Name</label>
                        <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminUserFirstName" name="first_name" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-bold text-[#1c1a25]">Last Name</label>
                        <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminUserLastName" name="last_name" required />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-[#1c1a25]">Email</label>
                    <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminUserEmail" name="email" type="email" required />
                </div>

                <div id="adminUserPasswordBlock" class="space-y-1.5">
                    <label class="text-sm font-bold text-[#1c1a25]">Password</label>
                    <input class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminUserPassword" name="password" type="password" minlength="6" />
                    <p class="text-xs text-[#595c5d]">Minimum 6 characters.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-bold text-[#1c1a25]">Role</label>
                        <select class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminUserRole" name="role" required>
                            <option value="USER">USER</option>
                            <option value="LIBRARIAN">LIBRARIAN</option>
                            <option value="ADMIN">ADMIN</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-bold text-[#1c1a25]">Status</label>
                        <select class="w-full bg-[#ebe6f5] border-none rounded-lg focus:ring-2 focus:ring-[#5a30fb]/40 text-sm py-3" id="adminUserStatus" name="is_active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <footer class="bg-[#f7f1ff] px-8 py-5 flex items-center justify-end gap-4 border-t border-[#ece8f7]">
                <button type="button" class="px-6 py-3 text-sm font-bold text-[#595c5d] hover:text-[#1c1a25] transition-colors" id="adminUserCancelBtn">Cancel</button>
                <button type="submit" class="px-10 py-3 bg-gradient-to-r from-[#3800bf] to-[#4f1bf1] text-white font-bold rounded-xl shadow-lg hover:shadow-[#5a30fb]/20 hover:scale-[1.02] active:scale-[0.98] transition-all" id="adminUserSaveBtn">Create User</button>
            </footer>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Close">close</button></form>
</dialog>

<script>
window.ADMIN_API_URL = '<?php echo APP_URL; ?>/controllers/admin.php';
window.ADMIN_SEARCH_API_URL = '<?php echo APP_URL; ?>/controllers/adminsearchs.php';
</script>
<script src="<?php echo APP_URL; ?>/public/js/admin.js"></script>
<script src="<?php echo APP_URL; ?>/public/js/adminsearch.js"></script>
