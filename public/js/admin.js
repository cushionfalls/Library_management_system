
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

function adminBaseUrl() {
    const api = window.ADMIN_API_URL || '';
    return api.replace(/\/controllers\/admin\.php.*$/, '');
}

function adminAssetUrl(path) {
    const raw = String(path || '').trim();
    if (!raw) return '';
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith('/')) return adminBaseUrl() + raw;
    return adminBaseUrl() + '/' + raw.replace(/^\/+/, '');
}

async function adminFetch(action, options = {}) {
    const response = await fetch(adminApiUrl(action), options);
    return response.json();
}


function renderOverview(overview) {
    document.getElementById('adminTotalUsers').textContent = overview.total_users ?? 0;
    document.getElementById('adminTotalBooks').textContent = overview.total_books ?? 0;
    document.getElementById('adminActiveRentals').textContent = overview.active_rentals ?? 0;
    document.getElementById('adminWalletCreditsToday').textContent = formatUsdFromCents(overview.wallet_credits_today ?? 0);
}
    function fallbackCover() {
        return 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80';
    }
function renderBooks(books) {
    const body = document.getElementById('adminBooksBody');
    if (!body) return;
    if (!books || books.length === 0) {
        body.innerHTML = '<tr><td colspan="7" class="px-8 py-6 text-center text-[#595c5d]">No books found.</td></tr>';
        return;
    }


    body.innerHTML = books.map((book) => {
        return `
            <tr class="hover:bg-[#eef1f2]/20 transition-colors">
                <td class="px-8 py-6 text-sm font-medium text-[#2c2f30]">${escapeHtml(book.isbn)}</td>
                <td class="px-8 py-6"><img class="w-16 h-20 object-cover rounded-md" alt="Book Cover" src="${adminAssetUrl(book.cover_image) || fallbackCover()}"/></td>
                <td class="px-8 py-6 text-sm font-bold text-[#2c2f30]">${escapeHtml(book.name)}</td>
                <td class="px-8 py-6 text-sm text-[#595c5d]">${escapeHtml(book.publisher || '-')}</td>
                <td class="px-8 py-6 text-sm text-[#595c5d]">${escapeHtml(book.authors || book.author || '-')}</td>
                <td class="px-8 py-6 text-sm font-semibold text-[#6933dc]">${formatUsdFromCents(book.online_buy_price || book.price || 0)}</td>
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
            <td class="px-8 py-6 text-sm font-medium text-[#2c2f30]">${escapeHtml((u.first_name || '') + ' ' + (u.last_name || ''))}</td>
            <td class="px-8 py-6 text-sm text-[#595c5d]">${escapeHtml(u.email)}</td>
            <td class="px-8 py-6 text-sm text-[#2c2f30]">${escapeHtml(u.role)}</td>
            <td class="px-8 py-6 text-sm text-[#2c2f30]">${Number(u.is_active) === 1 ? 'Active' : 'Inactive'}</td>
            <td class="px-8 py-6 text-sm text-[#595c5d]">${formatDate(u.created_at)}</td>
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
        const displayType = tx.type.replace('_', ' ');
        return `
            <tr class="hover:bg-[#eef1f2]/20 transition-colors">
                <td class="px-8 py-6 text-sm font-medium text-[#2c2f30]">${escapeHtml(tx.title)}</td>
                <td class="px-8 py-6 text-sm text-[#595c5d]">${escapeHtml((tx.first_name || '') + ' ' + (tx.last_name || ''))}</td>
                <td class="px-8 py-6 text-sm text-[#2c2f30] capitalize">${escapeHtml(displayType.toLowerCase())}</td>
                <td class="px-8 py-6 text-sm font-semibold text-[#6933dc]">${formatUsdFromCents(tx.amount)}</td>
            </tr>
        `;
    }).join('');
}


function setActiveTab(tabName) {
    window.__adminActiveTab = tabName;
    const tabs = ['books', 'users', 'transactions'];
    tabs.forEach((tab) => {
        const panel = document.getElementById('adminSection' + tab.charAt(0).toUpperCase() + tab.slice(1));
        const btn = document.querySelector('.admin-tab-btn[data-tab="' + tab + '"]');
        if (panel) panel.classList.toggle('hidden', tab !== tabName);
        if (btn) {
            if (tab === tabName) {
                btn.classList.add('bg-[#3800bf]', 'text-white', 'shadow-lg', 'shadow-[#3800bf]/20');
                btn.classList.remove('text-[#474557]', 'hover:bg-[#e5e0f0]');
            } else {
                btn.classList.remove('bg-[#3800bf]', 'text-white', 'shadow-lg', 'shadow-[#3800bf]/20');
                btn.classList.add('text-[#474557]', 'hover:bg-[#e5e0f0]');
            }
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
    window.__adminBooks = result.data.books || [];
    window.__adminUsers = result.data.recent_users || [];
    window.__adminTransactions = result.data.recent_transactions || [];
}

function openBookModal(book = null) {
    const modal = document.getElementById('adminBookModal');
    const title = document.getElementById('adminBookModalTitle');
    const saveBtn = document.getElementById('adminBookSaveBtn');
    const idEl = document.getElementById('adminBookId');
    const existingCoverEl = document.getElementById('adminBookExistingCoverImage');
    const existingPdfEl = document.getElementById('adminBookExistingOnlinePdf');
    const isbnEl = document.getElementById('adminBookIsbn');
    const nameEl = document.getElementById('adminBookName');
    const descriptionEl = document.getElementById('adminBookDescription');
    const authorEl = document.getElementById('adminBookAuthor');
    const publisherEl = document.getElementById('adminBookPublisher');
    const publishedAtEl = document.getElementById('adminBookPublishedAt');
    const languageEl = document.getElementById('adminBookLanguage');
    const genreEl = document.getElementById('adminBookGenre');
    const onlineBuyPriceEl = document.getElementById('adminBookOnlineBuyPrice');
    const coverInput = document.getElementById('adminBookCoverImage');
    const pdfInput = document.getElementById('adminBookOnlinePdf');
    const coverPreview = document.getElementById('adminBookCoverPreview');
    const coverPlaceholder = document.getElementById('adminBookCoverPlaceholder');
    const pdfFilename = document.getElementById('adminBookPdfFilename');

    if (!modal) return;

    const setCover = (url) => {
        const finalUrl = adminAssetUrl(url);
        if (!finalUrl) {
            coverPreview.removeAttribute('src');
            coverPreview.classList.add('hidden');
            coverPlaceholder.classList.remove('hidden');
            return;
        }
        coverPreview.src = finalUrl;
        coverPreview.classList.remove('hidden');
        coverPlaceholder.classList.add('hidden');
    };

    if (book) {
        title.textContent = 'Edit Entry';
        saveBtn.textContent = 'Save Changes';
        idEl.value = book.id;
        existingCoverEl.value = book.cover_image || '';
        existingPdfEl.value = book.online_copy_pdf || '';
        isbnEl.value = book.isbn || '';
        nameEl.value = book.name || '';
        descriptionEl.value = book.description || '';
        authorEl.value = book.authors || '';
        publisherEl.value = book.publisher || '';
        publishedAtEl.value = book.published_at ? String(book.published_at).slice(0, 10) : '';
        languageEl.value = book.language || 'English';
        genreEl.value = String(book.genre || 'OTHERS').toUpperCase();
        if (onlineBuyPriceEl) onlineBuyPriceEl.value = book.online_buy_price != null ? (Number(book.online_buy_price) / 100).toFixed(2) : '';
        setCover(book.cover_image || '');
        pdfFilename.textContent = book.online_copy_pdf ? String(book.online_copy_pdf).split('/').pop() : 'No file selected';
    } else {
        title.textContent = 'Add New Entry';
        saveBtn.textContent = 'Add Book';
        idEl.value = '';
        existingCoverEl.value = '';
        existingPdfEl.value = '';
        isbnEl.value = '';
        nameEl.value = '';
        descriptionEl.value = '';
        authorEl.value = '';
        publisherEl.value = '';
        publishedAtEl.value = '';
        languageEl.value = 'English';
        genreEl.value = 'OTHERS';
        if (onlineBuyPriceEl) onlineBuyPriceEl.value = '';
        setCover('');
        pdfFilename.textContent = 'No file selected';
    }

    if (coverInput) coverInput.value = '';
    if (pdfInput) pdfInput.value = '';

    modal.showModal();
}

async function autofillBookByIsbn() {
    const isbnEl = document.getElementById('adminBookIsbn');
    const existingCoverEl = document.getElementById('adminBookExistingCoverImage');
    const autofillBtn = document.getElementById('adminBookAutofillBtn');
    const coverPreview = document.getElementById('adminBookCoverPreview');
    const coverPlaceholder = document.getElementById('adminBookCoverPlaceholder');

    const rawIsbn = String((isbnEl && isbnEl.value) || '').trim();
    if (!rawIsbn) {
        adminToast('Enter ISBN first', 'warning');
        return;
    }

    try {
        if (autofillBtn) {
            autofillBtn.disabled = true;
            autofillBtn.classList.add('opacity-60', 'cursor-not-allowed');
        }

        const response = await fetch(adminApiUrl('book-by-isbn') + '&isbn=' + encodeURIComponent(rawIsbn), { cache: 'no-store' });
        const result = await response.json();
        if (!result || !result.success || !result.data) {
            adminToast((result && (result.message || result.error)) || 'No book data found for this ISBN', 'error');
            return;
        }

        const data = result.data;
        document.getElementById('adminBookName').value = data.name || '';
        document.getElementById('adminBookDescription').value = data.description || '';
        document.getElementById('adminBookAuthor').value = data.author || '';
        document.getElementById('adminBookPublisher').value = data.publisher || '';
        document.getElementById('adminBookPublishedAt').value = data.published_at || '';
        document.getElementById('adminBookLanguage').value = data.language || 'English';
        document.getElementById('adminBookGenre').value = String(data.genre || 'OTHERS').toUpperCase();

        if (data.cover_image_url) {
            existingCoverEl.value = data.cover_image_url;
            coverPreview.src = data.cover_image_url;
            coverPreview.classList.remove('hidden');
            coverPlaceholder.classList.add('hidden');
        }

        adminToast('Book details autofilled from ISBN', 'success');
    } catch (error) {
        adminToast('Failed to autofill book details', 'error');
    } finally {
        if (autofillBtn) {
            autofillBtn.disabled = false;
            autofillBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }
}

async function saveBook(event) {
    event.preventDefault();
    const form = document.getElementById('adminBookForm');
    const formData = new FormData(form);
    const id = String(formData.get('id') || '').trim();
    const action = id ? 'update-book' : 'create-book';

    if (formData.has('online_buy_price') && formData.get('online_buy_price')) {
        formData.set('online_buy_price', Math.round(Number(formData.get('online_buy_price')) * 100));
    }
    
    // Set defaults for removed fields
    formData.set('number_of_copies', '1');
    formData.set('price', formData.get('online_buy_price') || '0');
    formData.set('online_rent_price', '0');

    const result = await adminFetch(action, {
        method: 'POST',
        body: formData
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

function openUserModal(user = null, preferredRole = 'USER') {
    const modal = document.getElementById('adminUserModal');
    if (!modal) return;
    const idEl = document.getElementById('adminUserId');
    const titleEl = document.getElementById('adminUserModalTitle');
    const subtitleEl = document.getElementById('adminUserModalSubtitle');
    const saveBtnEl = document.getElementById('adminUserSaveBtn');
    const passwordBlockEl = document.getElementById('adminUserPasswordBlock');
    const passwordEl = document.getElementById('adminUserPassword');
    const roleEl = document.getElementById('adminUserRole');
    const adminRoleOption = roleEl ? roleEl.querySelector('option[value="ADMIN"]') : null;

    if (user) {
        idEl.value = user.id || '';
        document.getElementById('adminUserFirstName').value = user.first_name || '';
        document.getElementById('adminUserLastName').value = user.last_name || '';
        document.getElementById('adminUserEmail').value = user.email || '';
        document.getElementById('adminUserRole').value = user.role || 'USER';
        document.getElementById('adminUserStatus').value = Number(user.is_active) === 1 ? '1' : '0';
        if (titleEl) titleEl.textContent = 'Edit Member';
        if (subtitleEl) subtitleEl.textContent = 'Update user information and access role.';
        if (saveBtnEl) saveBtnEl.textContent = 'Save Changes';
        if (adminRoleOption) adminRoleOption.disabled = false;
        if (passwordBlockEl) passwordBlockEl.classList.add('hidden');
        if (passwordEl) {
            passwordEl.value = '';
            passwordEl.required = false;
        }
    } else {
        idEl.value = '';
        document.getElementById('adminUserFirstName').value = '';
        document.getElementById('adminUserLastName').value = '';
        document.getElementById('adminUserEmail').value = '';
        document.getElementById('adminUserRole').value = preferredRole === 'LIBRARIAN' ? 'LIBRARIAN' : 'USER';
        document.getElementById('adminUserStatus').value = '1';
        if (titleEl) titleEl.textContent = preferredRole === 'LIBRARIAN' ? 'Add New Librarian' : 'Add New User';
        if (subtitleEl) subtitleEl.textContent = preferredRole === 'LIBRARIAN' ? 'Create a librarian account for this branch.' : 'Create a user account for this branch.';
        if (saveBtnEl) saveBtnEl.textContent = preferredRole === 'LIBRARIAN' ? 'Create Librarian' : 'Create User';
        if (adminRoleOption) adminRoleOption.disabled = true;
        if (passwordBlockEl) passwordBlockEl.classList.remove('hidden');
        if (passwordEl) {
            passwordEl.value = '';
            passwordEl.required = true;
        }
    }

    modal.showModal();
}

async function saveUser(event) {
    event.preventDefault();
    const form = document.getElementById('adminUserForm');
    const formData = new FormData(form);
    const id = String(formData.get('id') || '').trim();
    const action = id ? 'update-user' : 'create-user';

    if (id) {
        formData.delete('password');
    }

    const result = await adminFetch(action, {
        method: 'POST',
        body: new URLSearchParams(formData)
    });

    if (!result || !result.success) {
        adminToast((result && result.message) || (result && result.error) || 'Failed to save user', 'error');
        return;
    }

    document.getElementById('adminUserModal').close();
    adminToast(result.message || 'User saved successfully', 'success');
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
    const closeBtn = document.getElementById('adminBookCloseBtn');
    const addUserBtn = document.getElementById('adminAddUserBtn');
    const form = document.getElementById('adminBookForm');
    const booksBody = document.getElementById('adminBooksBody');
    const usersBody = document.getElementById('adminUsersBody');
    const userForm = document.getElementById('adminUserForm');
    const userCancelBtn = document.getElementById('adminUserCancelBtn');
    const userCloseBtn = document.getElementById('adminUserCloseBtn');
    const tabButtons = document.querySelectorAll('.admin-tab-btn');
    const autofillBtn = document.getElementById('adminBookAutofillBtn');
    const coverInput = document.getElementById('adminBookCoverImage');
    const coverPreview = document.getElementById('adminBookCoverPreview');
    const coverPlaceholder = document.getElementById('adminBookCoverPlaceholder');
    const pdfInput = document.getElementById('adminBookOnlinePdf');
    const pdfFilename = document.getElementById('adminBookPdfFilename');

    if (addBtn) addBtn.addEventListener('click', () => openBookModal(null));
    if (addUserBtn) addUserBtn.addEventListener('click', () => openUserModal(null, 'USER'));
    if (cancelBtn) cancelBtn.addEventListener('click', () => document.getElementById('adminBookModal').close());
    if (closeBtn) closeBtn.addEventListener('click', () => document.getElementById('adminBookModal').close());
    if (form) form.addEventListener('submit', saveBook);
    if (autofillBtn) autofillBtn.addEventListener('click', autofillBookByIsbn);

    if (coverInput) {
        coverInput.addEventListener('change', () => {
            const file = coverInput.files && coverInput.files[0];
            if (!file) return;
            const src = URL.createObjectURL(file);
            coverPreview.src = src;
            coverPreview.classList.remove('hidden');
            coverPlaceholder.classList.add('hidden');
        });
    }

    if (pdfInput) {
        pdfInput.addEventListener('change', () => {
            const file = pdfInput.files && pdfInput.files[0];
            pdfFilename.textContent = file ? file.name : 'No file selected';
        });
    }

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
    if (userCloseBtn) userCloseBtn.addEventListener('click', () => document.getElementById('adminUserModal').close());

    tabButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab') || 'books';
            setActiveTab(tab);
        });
    });

    const exportBtn = document.getElementById('adminExportTransactionsBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const txs = window.__adminTransactions || [];
            if (txs.length === 0) {
                adminToast('No transactions to export', 'warning');
                return;
            }
            let csv = 'Book,User,Type,Amount\n';
            txs.forEach(tx => {
                const user = (tx.first_name || '') + ' ' + (tx.last_name || '');
                const amount = (Number(tx.amount) / 100).toFixed(2);
                csv += `"${tx.title}","${user}","${tx.type}","${amount}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', 'transactions.csv');
            a.click();
        });
    }

    const filterBtns = document.querySelectorAll('.admin-tx-filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.getAttribute('data-filter');
            filterBtns.forEach(b => {
                b.classList.remove('bg-[#3800bf]', 'text-white');
                b.classList.add('text-[#474557]');
            });
            btn.classList.add('bg-[#3800bf]', 'text-white');
            btn.classList.remove('text-[#474557]');
            
            const allTxs = window.__adminTransactions || [];
            if (filter === 'all') {
                renderTransactions(allTxs);
            } else {
                renderTransactions(allTxs.filter(tx => tx.type === filter));
            }
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

