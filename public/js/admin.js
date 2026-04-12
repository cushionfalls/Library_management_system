function adminEscape(value) {
    if (value == null) return '';
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function adminToast(message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
        return;
    }
    alert(message);
}

function adminApiUrl(action) {
    return (window.ADMIN_API_URL || '') + '?action=' + encodeURIComponent(action);
}

async function adminFetch(action, options = {}) {
    const response = await fetch(adminApiUrl(action), options);
    return response.json();
}

function adminFormatDate(value) {
    if (!value) return '-';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '-';
    return d.toLocaleDateString();
}

function renderOverview(overview) {
    document.getElementById('adminTotalUsers').textContent = overview.total_users ?? 0;
    document.getElementById('adminTotalBooks').textContent = overview.total_books ?? 0;
    document.getElementById('adminActiveRentals').textContent = overview.active_rentals ?? 0;
    document.getElementById('adminPendingFines').textContent = overview.pending_fines ?? 0;
    document.getElementById('adminOverdueBooks').textContent = overview.overdue_books ?? 0;
    document.getElementById('adminWalletCreditsToday').textContent = '₹' + (overview.wallet_credits_today ?? 0);
}

function renderBooks(books) {
    const body = document.getElementById('adminBooksBody');
    if (!body) return;
    if (!books || books.length === 0) {
        body.innerHTML = '<tr><td colspan="6" class="px-8 py-6 text-center text-[#595c5d]">No books found.</td></tr>';
        return;
    }

    body.innerHTML = books.map((book) => {
        return `
            <tr class="hover:bg-[#eef1f2]/20 transition-colors">
                <td class="px-8 py-6 text-sm font-medium text-[#2c2f30]">${adminEscape(book.isbn)}</td>
                <td class="px-8 py-6 text-sm font-bold text-[#2c2f30]">${adminEscape(book.name)}</td>
                <td class="px-8 py-6 text-sm text-[#595c5d]">${adminEscape(book.publisher)}</td>
                <td class="px-8 py-6 text-sm text-[#2c2f30]">${adminEscape(book.number_of_copies)}</td>
                <td class="px-8 py-6 text-sm font-semibold text-[#6933dc]">₹${adminEscape(book.price)}</td>
                <td class="px-8 py-6 text-right">
                    <button class="text-[#7343a9] hover:bg-[#e3c6ff]/30 px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-action="edit" data-id="${book.id}">Edit</button>
                    <button class="text-[#b41340] hover:bg-[#ffefef] px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-action="delete" data-id="${book.id}">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderUsers(users) {
    const body = document.getElementById('adminUsersBody');
    if (!body) return;
    if (!users || users.length === 0) {
        body.innerHTML = '<tr><td colspan="6" class="px-8 py-6 text-center text-[#595c5d]">No users found.</td></tr>';
        return;
    }

    body.innerHTML = users.map((u) => `
        <tr class="hover:bg-[#eef1f2]/20 transition-colors">
            <td class="px-8 py-6 text-sm font-medium text-[#2c2f30]">${adminEscape((u.first_name || '') + ' ' + (u.last_name || ''))}</td>
            <td class="px-8 py-6 text-sm text-[#595c5d]">${adminEscape(u.email)}</td>
            <td class="px-8 py-6 text-sm text-[#2c2f30]">${adminEscape(u.role)}</td>
            <td class="px-8 py-6 text-sm text-[#2c2f30]">${Number(u.is_active) === 1 ? 'Active' : 'Inactive'}</td>
            <td class="px-8 py-6 text-sm text-[#595c5d]">${adminFormatDate(u.created_at)}</td>
            <td class="px-8 py-6 text-right">
                <button class="text-[#7343a9] hover:bg-[#e3c6ff]/30 px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-user-action="edit" data-id="${u.id}">Edit</button>
                <button class="text-[#b41340] hover:bg-[#ffefef] px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-user-action="delete" data-id="${u.id}">Remove</button>
            </td>
        </tr>
    `).join('');
}

function renderTransactions(transactions) {
    const body = document.getElementById('adminTransactionsBody');
    if (!body) return;
    if (!transactions || transactions.length === 0) {
        body.innerHTML = '<tr><td colspan="6" class="px-8 py-6 text-center text-[#595c5d]">No transactions found.</td></tr>';
        return;
    }

    body.innerHTML = transactions.map((tx) => {
        const status = Number(tx.is_returned) === 1 ? 'Returned' : 'Active';
        return `
            <tr class="hover:bg-[#eef1f2]/20 transition-colors">
                <td class="px-8 py-6 text-sm font-medium text-[#2c2f30]">${adminEscape(tx.book_name)}</td>
                <td class="px-8 py-6 text-sm text-[#595c5d]">${adminEscape((tx.first_name || '') + ' ' + (tx.last_name || ''))}</td>
                <td class="px-8 py-6 text-sm text-[#2c2f30]">${adminEscape(tx.transaction_type)}</td>
                <td class="px-8 py-6 text-sm font-semibold text-[#6933dc]">₹${adminEscape(tx.amount_paid)}</td>
                <td class="px-8 py-6 text-sm text-[#595c5d]">${adminFormatDate(tx.due_date)}</td>
                <td class="px-8 py-6 text-sm text-[#2c2f30]">${status}</td>
            </tr>
        `;
    }).join('');
}

function renderOverdue(overdueBooks) {
    const body = document.getElementById('adminOverdueBody');
    if (!body) return;
    if (!overdueBooks || overdueBooks.length === 0) {
        body.innerHTML = '<tr><td colspan="4" class="px-8 py-6 text-center text-[#595c5d]">No overdue books found.</td></tr>';
        return;
    }

    body.innerHTML = overdueBooks.map((item) => `
        <tr class="hover:bg-[#eef1f2]/20 transition-colors">
            <td class="px-8 py-6 text-sm font-medium text-[#2c2f30]">${adminEscape(item.book_name)}</td>
            <td class="px-8 py-6 text-sm text-[#595c5d]">${adminEscape((item.first_name || '') + ' ' + (item.last_name || ''))}</td>
            <td class="px-8 py-6 text-sm text-[#2c2f30]">${adminFormatDate(item.due_date)}</td>
            <td class="px-8 py-6 text-sm text-[#595c5d]">${adminFormatDate(item.created_at)}</td>
        </tr>
    `).join('');
}

function setActiveTab(tabName) {
    window.__adminActiveTab = tabName;
    const tabs = ['books', 'users', 'transactions', 'overdue'];
    tabs.forEach((tab) => {
        const panel = document.getElementById('adminSection' + tab.charAt(0).toUpperCase() + tab.slice(1));
        const btn = document.querySelector('.admin-tab-btn[data-tab="' + tab + '"]');
        if (panel) panel.classList.toggle('hidden', tab !== tabName);
        if (btn) {
            btn.classList.toggle('bg-[#6933dc]', tab === tabName);
            btn.classList.toggle('text-white', tab === tabName);
            btn.classList.toggle('shadow-lg', tab === tabName);
            btn.classList.toggle('shadow-[#6933dc]/20', tab === tabName);
            btn.classList.toggle('text-[#595c5d]', tab !== tabName);
            btn.classList.toggle('hover:bg-[#dfe3e4]', tab !== tabName);
        }
    });
}

async function loadAdminDashboard() {
    const result = await adminFetch('dashboard', { cache: 'no-store' });
    if (!result.success || !result.data) {
        adminToast(result.error || 'Failed to load dashboard', 'error');
        return;
    }
    renderOverview(result.data.overview || {});
    renderBooks(result.data.books || []);
    renderUsers(result.data.recent_users || []);
    renderTransactions(result.data.recent_transactions || []);
    renderOverdue(result.data.overdue_books || []);
    window.__adminBooks = result.data.books || [];
    window.__adminUsers = result.data.recent_users || [];
}

function openBookModal(book = null) {
    const modal = document.getElementById('adminBookModal');
    const title = document.getElementById('adminBookModalTitle');
    const idEl = document.getElementById('adminBookId');
    const isbnEl = document.getElementById('adminBookIsbn');
    const nameEl = document.getElementById('adminBookName');
    const publisherEl = document.getElementById('adminBookPublisher');
    const copiesEl = document.getElementById('adminBookCopies');
    const priceEl = document.getElementById('adminBookPrice');

    if (!modal) return;

    if (book) {
        title.textContent = 'Edit Book';
        idEl.value = book.id;
        isbnEl.value = book.isbn || '';
        nameEl.value = book.name || '';
        publisherEl.value = book.publisher || '';
        copiesEl.value = book.number_of_copies ?? 0;
        priceEl.value = book.price ?? 0;
    } else {
        title.textContent = 'Add New Book';
        idEl.value = '';
        isbnEl.value = '';
        nameEl.value = '';
        publisherEl.value = '';
        copiesEl.value = 0;
        priceEl.value = 0;
    }

    modal.showModal();
}

async function saveBook(event) {
    event.preventDefault();
    const form = document.getElementById('adminBookForm');
    const formData = new FormData(form);
    const id = String(formData.get('id') || '').trim();
    const action = id ? 'update-book' : 'create-book';

    const result = await adminFetch(action, {
        method: 'POST',
        body: new URLSearchParams(formData)
    });

    if (!result || !result.success) {
        adminToast((result && result.message) || (result && result.error) || 'Failed to save book', 'error');
        return;
    }

    document.getElementById('adminBookModal').close();
    adminToast(result.message || 'Saved successfully', 'success');
    await loadAdminDashboard();
}

async function deleteBook(id) {
    if (!confirm('Delete this book?')) return;
    const params = new URLSearchParams();
    params.set('id', id);
    const result = await adminFetch('delete-book', {
        method: 'POST',
        body: params
    });

    if (!result || !result.success) {
        adminToast((result && result.message) || (result && result.error) || 'Failed to delete book', 'error');
        return;
    }

    adminToast(result.message || 'Deleted successfully', 'success');
    await loadAdminDashboard();
}

function openUserModal(user) {
    const modal = document.getElementById('adminUserModal');
    if (!modal || !user) return;
    document.getElementById('adminUserId').value = user.id || '';
    document.getElementById('adminUserFirstName').value = user.first_name || '';
    document.getElementById('adminUserLastName').value = user.last_name || '';
    document.getElementById('adminUserEmail').value = user.email || '';
    document.getElementById('adminUserRole').value = user.role || 'USER';
    document.getElementById('adminUserStatus').value = Number(user.is_active) === 1 ? '1' : '0';
    modal.showModal();
}

async function saveUser(event) {
    event.preventDefault();
    const form = document.getElementById('adminUserForm');
    const formData = new FormData(form);

    const result = await adminFetch('update-user', {
        method: 'POST',
        body: new URLSearchParams(formData)
    });

    if (!result || !result.success) {
        adminToast((result && result.message) || (result && result.error) || 'Failed to update user', 'error');
        return;
    }

    document.getElementById('adminUserModal').close();
    adminToast(result.message || 'User updated successfully', 'success');
    await loadAdminDashboard();
}

async function deleteUser(id) {
    if (!confirm('Remove this user? This action cannot be undone.')) return;
    const params = new URLSearchParams();
    params.set('id', id);
    const result = await adminFetch('delete-user', {
        method: 'POST',
        body: params
    });

    if (!result || !result.success) {
        adminToast((result && result.message) || (result && result.error) || 'Failed to remove user', 'error');
        return;
    }

    adminToast(result.message || 'User removed successfully', 'success');
    await loadAdminDashboard();
}

function bindAdminEvents() {
    const addBtn = document.getElementById('adminAddBookBtn');
    const cancelBtn = document.getElementById('adminBookCancelBtn');
    const form = document.getElementById('adminBookForm');
    const booksBody = document.getElementById('adminBooksBody');
    const usersBody = document.getElementById('adminUsersBody');
    const userForm = document.getElementById('adminUserForm');
    const userCancelBtn = document.getElementById('adminUserCancelBtn');
    const tabButtons = document.querySelectorAll('.admin-tab-btn');

    if (addBtn) addBtn.addEventListener('click', () => openBookModal(null));
    if (cancelBtn) cancelBtn.addEventListener('click', () => document.getElementById('adminBookModal').close());
    if (form) form.addEventListener('submit', saveBook);

    if (booksBody) {
        booksBody.addEventListener('click', async (event) => {
            const btn = event.target.closest('button[data-action]');
            if (!btn) return;
            const action = btn.getAttribute('data-action');
            const id = parseInt(btn.getAttribute('data-id'), 10);
            if (!id) return;

            if (action === 'edit') {
                const books = window.__adminBooks || [];
                const book = books.find((b) => Number(b.id) === Number(id));
                if (book) openBookModal(book);
                return;
            }
            if (action === 'delete') {
                await deleteBook(id);
            }
        });
    }

    if (usersBody) {
        usersBody.addEventListener('click', async (event) => {
            const btn = event.target.closest('button[data-user-action]');
            if (!btn) return;
            const action = btn.getAttribute('data-user-action');
            const id = parseInt(btn.getAttribute('data-id'), 10);
            if (!id) return;

            if (action === 'edit') {
                const users = window.__adminUsers || [];
                const user = users.find((u) => Number(u.id) === Number(id));
                if (user) openUserModal(user);
                return;
            }

            if (action === 'delete') {
                await deleteUser(id);
            }
        });
    }

    if (userForm) userForm.addEventListener('submit', saveUser);
    if (userCancelBtn) userCancelBtn.addEventListener('click', () => document.getElementById('adminUserModal').close());

    tabButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab') || 'books';
            setActiveTab(tab);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    window.__adminActiveTab = 'books';
    bindAdminEvents();
    setActiveTab('books');
    loadAdminDashboard().catch(() => {
        adminToast('Failed to load admin dashboard', 'error');
    });
});

