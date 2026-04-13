<div id="adminDashboardRoot" class="space-y-8">
    <section class="mb-4">
        <h1 class="text-4xl font-extrabold tracking-tight font-['Manrope'] text-[#2c2f30] mb-2">Admin Dashboard</h1>
        <p class="text-[#595c5d] text-lg">Manage catalog, members, transactions, and library operations.</p>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)]">
            <p class="text-[#595c5d] text-xs font-semibold uppercase tracking-widest mb-1">Total Users</p>
            <h3 class="text-3xl font-bold text-[#2c2f30]" id="adminTotalUsers">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)]">
            <p class="text-[#595c5d] text-xs font-semibold uppercase tracking-widest mb-1">Total Books</p>
            <h3 class="text-3xl font-bold text-[#2c2f30]" id="adminTotalBooks">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)]">
            <p class="text-[#595c5d] text-xs font-semibold uppercase tracking-widest mb-1">Overdue Books</p>
            <h3 class="text-3xl font-bold text-[#2c2f30]" id="adminOverdueBooks">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)]">
            <p class="text-[#595c5d] text-xs font-semibold uppercase tracking-widest mb-1">Unpaid Fines</p>
            <h3 class="text-3xl font-bold text-[#2c2f30]" id="adminPendingFines">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)]">
            <p class="text-[#595c5d] text-xs font-semibold uppercase tracking-widest mb-1">Active Rentals</p>
            <h3 class="text-3xl font-bold text-[#2c2f30]" id="adminActiveRentals">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)]">
            <p class="text-[#595c5d] text-xs font-semibold uppercase tracking-widest mb-1">Total Income</p>
            <h3 class="text-3xl font-bold text-[#2c2f30]" id="adminWalletCreditsToday">₹0</h3>
        </div>
    </section>

    <section class="flex items-center gap-2 bg-[#eef1f2] p-1 rounded-full w-fit flex-wrap">
        <button class="admin-tab-btn rounded-full px-8 py-2.5 text-sm font-semibold tracking-wide bg-[#6933dc] text-white shadow-lg shadow-[#6933dc]/20" data-tab="books">Books</button>
        <button class="admin-tab-btn rounded-full px-8 py-2.5 text-sm font-semibold tracking-wide text-[#595c5d] hover:bg-[#dfe3e4]" data-tab="users">Users</button>
        <button class="admin-tab-btn rounded-full px-8 py-2.5 text-sm font-semibold tracking-wide text-[#595c5d] hover:bg-[#dfe3e4]" data-tab="transactions">Transactions</button>
        <button class="admin-tab-btn rounded-full px-8 py-2.5 text-sm font-semibold tracking-wide text-[#595c5d] hover:bg-[#dfe3e4]" data-tab="overdue">Overdue</button>
    </section>

    <?php require __DIR__ . '/partials/admin_search_bar.php'; ?>

    <section id="adminSectionBooks" class="admin-tab-panel bg-white rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)] overflow-hidden">
        <div class="p-8 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center bg-[#eef1f2]/50">
            <div>
                <h2 class="text-2xl font-bold text-[#2c2f30] tracking-tight font-['Manrope']">Books catalog</h2>
                <p class="text-[#595c5d] text-sm mt-1">Manage titles, stock levels, and pricing for the library.</p>
            </div>
            <button class="bg-gradient-to-br from-[#6933dc] to-[#ac8eff] text-white px-6 py-3 rounded-lg font-bold flex items-center gap-2 hover:opacity-90 transition-opacity active:scale-95 shadow-lg shadow-[#6933dc]/20" id="adminAddBookBtn">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Add New Book
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#eef1f2]/30 border-b border-[#e5e9ea]">
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">ISBN</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Name</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Publisher</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Copies</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Price</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminBooksBody" class="divide-y divide-[#eef1f2]">
                    <tr><td colspan="6" class="px-8 py-6 text-center text-[#595c5d]">Loading books...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="adminSectionUsers" class="admin-tab-panel hidden bg-white rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)] overflow-hidden">
        <div class="p-8 bg-[#eef1f2]/50">
            <h2 class="text-2xl font-bold text-[#2c2f30] tracking-tight font-['Manrope']">Users</h2>
            <p class="text-[#595c5d] text-sm mt-1">Manage users, roles, and account status.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#eef1f2]/30 border-b border-[#e5e9ea]">
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Name</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Email</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Role</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Joined</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminUsersBody" class="divide-y divide-[#eef1f2]">
                    <tr><td colspan="6" class="px-8 py-6 text-center text-[#595c5d]">Loading users...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="adminSectionTransactions" class="admin-tab-panel hidden bg-white rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)] overflow-hidden">
        <div class="p-8 bg-[#eef1f2]/50">
            <h2 class="text-2xl font-bold text-[#2c2f30] tracking-tight font-['Manrope']">Transactions</h2>
            <p class="text-[#595c5d] text-sm mt-1">Review activity, due dates, and payment details.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#eef1f2]/30 border-b border-[#e5e9ea]">
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Book</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">User</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Type</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Amount</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Due Date</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody id="adminTransactionsBody" class="divide-y divide-[#eef1f2]">
                    <tr><td colspan="6" class="px-8 py-6 text-center text-[#595c5d]">Loading transactions...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="adminSectionOverdue" class="admin-tab-panel hidden bg-white rounded-lg shadow-[0_12px_40px_rgba(44,47,48,0.06)] overflow-hidden">
        <div class="p-8 bg-[#eef1f2]/50">
            <h2 class="text-2xl font-bold text-[#2c2f30] tracking-tight font-['Manrope']">Overdue books</h2>
            <p class="text-[#595c5d] text-sm mt-1">Track books that have crossed due dates.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#eef1f2]/30 border-b border-[#e5e9ea]">
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Book</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">User</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Due Date</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#595c5d] uppercase tracking-widest">Rented On</th>
                    </tr>
                </thead>
                <tbody id="adminOverdueBody" class="divide-y divide-[#eef1f2]">
                    <tr><td colspan="4" class="px-8 py-6 text-center text-[#595c5d]">Loading overdue books...</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<dialog id="adminBookModal" class="modal">
    <div class="modal-box max-w-xl">
        <h3 class="font-bold text-lg mb-4" id="adminBookModalTitle">Add New Book</h3>
        <form id="adminBookForm" class="space-y-3">
            <input type="hidden" id="adminBookId" name="id" />
            <div>
                <label class="label"><span class="label-text">ISBN</span></label>
                <input class="input input-bordered w-full" id="adminBookIsbn" name="isbn" required />
            </div>
            <div>
                <label class="label"><span class="label-text">Name</span></label>
                <input class="input input-bordered w-full" id="adminBookName" name="name" required />
            </div>
            <div>
                <label class="label"><span class="label-text">Publisher</span></label>
                <input class="input input-bordered w-full" id="adminBookPublisher" name="publisher" required />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label"><span class="label-text">Copies</span></label>
                    <input class="input input-bordered w-full" id="adminBookCopies" name="number_of_copies" type="number" min="0" required />
                </div>
                <div>
                    <label class="label"><span class="label-text">Price</span></label>
                    <input class="input input-bordered w-full" id="adminBookPrice" name="price" type="number" min="0" required />
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" id="adminBookCancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="adminBookSaveBtn">Save</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<dialog id="adminUserModal" class="modal">
    <div class="modal-box max-w-xl">
        <h3 class="font-bold text-lg mb-4">Edit User</h3>
        <form id="adminUserForm" class="space-y-3">
            <input type="hidden" id="adminUserId" name="id" />
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label"><span class="label-text">First Name</span></label>
                    <input class="input input-bordered w-full" id="adminUserFirstName" name="first_name" required />
                </div>
                <div>
                    <label class="label"><span class="label-text">Last Name</span></label>
                    <input class="input input-bordered w-full" id="adminUserLastName" name="last_name" required />
                </div>
            </div>
            <div>
                <label class="label"><span class="label-text">Email</span></label>
                <input class="input input-bordered w-full" id="adminUserEmail" name="email" type="email" required />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label"><span class="label-text">Role</span></label>
                    <select class="select select-bordered w-full" id="adminUserRole" name="role" required>
                        <option value="USER">USER</option>
                        <option value="LIBRARIAN">LIBRARIAN</option>
                        <option value="ADMIN">ADMIN</option>
                    </select>
                </div>
                <div>
                    <label class="label"><span class="label-text">Status</span></label>
                    <select class="select select-bordered w-full" id="adminUserStatus" name="is_active" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" id="adminUserCancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script>
window.ADMIN_API_URL = '<?php echo APP_URL; ?>/controllers/admin.php';
window.ADMIN_SEARCH_API_URL = '<?php echo APP_URL; ?>/controllers/adminsearchs.php';
</script>
<script src="<?php echo APP_URL; ?>/public/js/admin.js"></script>
<script src="<?php echo APP_URL; ?>/public/js/adminsearch.js"></script>
