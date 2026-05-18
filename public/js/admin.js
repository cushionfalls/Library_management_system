
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

function adminAssetUrl(path) {
    return window.assetUrl ? window.assetUrl(path) : path;
}

function adminUserProfileAssetUrl(path) {
    const raw = String(path || '').trim();
    if (!raw) return '';
    if (/^https?:\/\//i.test(raw)) return raw;
    const filename = raw.split('/').pop().split('\\').pop().trim();
    // Validate filename structure to ensure it is a valid file, not a directory or null string
    if (!filename || filename === 'profiles' || filename === 'uploads' || filename === 'null' || !filename.includes('.')) {
        return '';
    }
    return window.assetUrl ? window.assetUrl('public/uploads/profiles/' + filename) : '/public/uploads/profiles/' + filename;
}

async function adminFetch(action, options = {}) {
    const isPost = (options.method || 'GET').toUpperCase() === 'POST';
    if (isPost && window.CSRF_TOKEN) {
        if (options.body instanceof FormData) {
            options.body.append('csrf_token', window.CSRF_TOKEN);
        } else if (options.body instanceof URLSearchParams) {
            options.body.append('csrf_token', window.CSRF_TOKEN);
        } else if (typeof options.body === 'string') {
            options.body += (options.body ? '&' : '') + 'csrf_token=' + encodeURIComponent(window.CSRF_TOKEN);
        } else if (!options.body) {
            options.body = 'csrf_token=' + encodeURIComponent(window.CSRF_TOKEN);
            options.headers = { ...options.headers, 'Content-Type': 'application/x-www-form-urlencoded', ...options.headers };
        }
    }
    const response = await fetch(adminApiUrl(action), options);
    return response.json();
}

let adminOffsets = {
    books: 0,
    users: 0,
    transactions: 0
};
const adminLimit = 20;


function renderOverview(overview) {
    document.getElementById('adminTotalUsers').textContent = overview.total_users ?? 0;
    document.getElementById('adminTotalBooks').textContent = overview.total_books ?? 0;
    document.getElementById('adminTotalMemberships').textContent = overview.total_memberships ?? 0;
    document.getElementById('adminWalletCreditsToday').textContent = formatUsdFromCents(overview.wallet_credits_today ?? 0);
    window.__adminExists = !!overview.admin_exists;
}
    function fallbackCover() {
        return 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80';
    }
function renderBooks(books, append = false) {
    const body = document.getElementById('adminBooksBody');
    if (!body) return;
    if (!append && (!books || books.length === 0)) {
        body.innerHTML = '<tr><td colspan="7" class="px-8 py-6 text-center text-on-surface-variant">No books found.</td></tr>';
        document.getElementById('adminLoadMoreBooksBtn')?.classList.add('hidden');
        return;
    }

    const html = books.map((book) => {
        return `
            <tr class="hover:bg-surface-variant/30 transition-colors">
                <td class="px-8 py-6 text-sm font-medium text-on-surface">${escapeHtml(book.isbn)}</td>
                <td class="px-8 py-6"><img class="w-16 h-20 object-cover rounded-md" alt="Book Cover" src="${adminAssetUrl(book.cover_image) || fallbackCover()}"/></td>
                <td class="px-8 py-6 text-sm font-bold text-on-surface">${escapeHtml(book.name)}</td>
                <td class="px-8 py-6 text-sm text-on-surface-variant">${escapeHtml(book.publisher || '-')}</td>
                <td class="px-8 py-6 text-sm text-on-surface-variant">${escapeHtml(book.authors || book.author || '-')}</td>
                <td class="px-8 py-6 text-sm font-semibold text-primary">${formatUsdFromCents(book.online_buy_price || book.price || 0)}</td>
                <td class="px-8 py-6 text-right">
                    <button class="text-primary hover:bg-primary-fixed/30 px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-action="edit" data-id="${book.id}">Edit</button>
                    ${window.IS_ADMIN ? `<button class="text-error hover:bg-error-container/40 px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-action="delete" data-id="${book.id}">Delete</button>` : ''}
                </td>
            </tr>
        `;
    }).join('');

    if (append) {
        body.insertAdjacentHTML('beforeend', html);
    } else {
        body.innerHTML = html;
    }

    const loadMoreBtn = document.getElementById('adminLoadMoreBooksBtn');
    if (loadMoreBtn) {
        loadMoreBtn.classList.toggle('hidden', books.length < adminLimit);
    }
}

function renderUsers(users, append = false) {
    const body = document.getElementById('adminUsersBody');
    if (!body) return;
    if (!append && (!users || users.length === 0)) {
        body.innerHTML = '<tr><td colspan="6" class="px-8 py-6 text-center text-on-surface-variant">No users found.</td></tr>';
        document.getElementById('adminLoadMoreUsersBtn')?.classList.add('hidden');
        return;
    }

    const html = users.map((u) => {
        const actionButtons = window.IS_ADMIN 
            ? `<button class="text-primary hover:bg-primary-fixed/30 px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-user-action="edit" data-id="${u.id}">Edit</button>
               <button class="text-error hover:bg-error-container/40 px-3 py-1.5 rounded-md text-sm font-semibold transition-all" data-user-action="delete" data-id="${u.id}">Remove</button>`
            : '<span class="text-xs text-on-surface-variant font-medium italic">View Only</span>';

        return `
            <tr class="hover:bg-surface-variant/30 transition-colors">
                <td class="px-8 py-6 text-sm font-medium text-on-surface">${escapeHtml((u.first_name || '') + ' ' + (u.last_name || ''))}</td>
                <td class="px-8 py-6 text-sm text-on-surface-variant">${escapeHtml(u.email)}</td>
                <td class="px-8 py-6 text-sm text-on-surface">${escapeHtml(u.role)}</td>
                <td class="px-8 py-6 text-sm text-on-surface">${Number(u.is_active) === 1 ? 'Active' : 'Inactive'}</td>
                <td class="px-8 py-6 text-sm text-on-surface-variant">${formatDate(u.created_at)}</td>
                <td class="px-8 py-6 text-right">
                    ${actionButtons}
                </td>
            </tr>
        `;
    }).join('');

    if (append) {
        body.insertAdjacentHTML('beforeend', html);
    } else {
        body.innerHTML = html;
    }

    const loadMoreBtn = document.getElementById('adminLoadMoreUsersBtn');
    if (loadMoreBtn) {
        loadMoreBtn.classList.toggle('hidden', users.length < adminLimit);
    }
}

function renderTransactions(transactions, append = false) {
    const body = document.getElementById('adminTransactionsBody');
    if (!body) return;
    if (!append && (!transactions || transactions.length === 0)) {
        body.innerHTML = '<tr><td colspan="6" class="px-8 py-6 text-center text-on-surface-variant">No transactions found.</td></tr>';
        document.getElementById('adminLoadMoreTransactionsBtn')?.classList.add('hidden');
        return;
    }

    const html = transactions.map((tx) => {
        const displayType = tx.type.replace('_', ' ');
        return `
            <tr class="hover:bg-surface-variant/30 transition-colors">
                <td class="px-8 py-6 text-sm font-medium text-on-surface">${escapeHtml(tx.title)}</td>
                <td class="px-8 py-6 text-sm text-on-surface-variant">${escapeHtml((tx.first_name || '') + ' ' + (tx.last_name || ''))}</td>
                <td class="px-8 py-6 text-sm text-on-surface capitalize">${escapeHtml(displayType.toLowerCase())}</td>
                <td class="px-8 py-6 text-sm font-semibold text-primary">${formatUsdFromCents(tx.amount)}</td>
            </tr>
        `;
    }).join('');

    if (append) {
        body.insertAdjacentHTML('beforeend', html);
    } else {
        body.innerHTML = html;
    }

    const loadMoreBtn = document.getElementById('adminLoadMoreTransactionsBtn');
    if (loadMoreBtn) {
        loadMoreBtn.classList.toggle('hidden', transactions.length < adminLimit);
    }
}


function setActiveTab(tabName) {
    window.__adminActiveTab = tabName;
    const tabs = ['books', 'users', 'transactions', 'analytics'];
    tabs.forEach((tab) => {
        const panel = document.getElementById('adminSection' + tab.charAt(0).toUpperCase() + tab.slice(1));
        const btn = document.querySelector('.admin-tab-btn[data-tab="' + tab + '"]');
        if (panel) panel.classList.toggle('hidden', tab !== tabName);
        if (btn) {
            if (tab === tabName) {
                btn.classList.add('bg-primary', 'text-on-primary', 'shadow-lg', 'shadow-primary/25');
                btn.classList.remove('text-on-surface-variant', 'hover:bg-surface-container-highest');
            } else {
                btn.classList.remove('bg-primary', 'text-on-primary', 'shadow-lg', 'shadow-primary/25');
                btn.classList.add('text-on-surface-variant', 'hover:bg-surface-container-highest');
            }
        }
    });

    if (tabName === 'analytics') {
        loadAnalytics();
    }
}

async function loadAdminDashboard() {
    adminOffsets = { books: 0, users: 0, transactions: 0 };
    
    // Use POST to send limits and offsets for initial load
    const result = await adminFetch('dashboard', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `limit=${adminLimit}&offset=0`
    });

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

async function loadMoreSection(type) {
    const btn = document.getElementById('adminLoadMore' + type.charAt(0).toUpperCase() + type.slice(1) + 'Btn');
    if (btn) btn.disabled = true;

    adminOffsets[type] += adminLimit;
    const action = type === 'books' ? 'books' : (type === 'users' ? 'recent-users' : 'recent-transactions');
    
    try {
        const result = await adminFetch(action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `limit=${adminLimit}&offset=${adminOffsets[type]}`
        });

        if (result.success && result.data) {
            if (type === 'books') {
                renderBooks(result.data, true);
                window.__adminBooks = [...(window.__adminBooks || []), ...result.data];
            } else if (type === 'users') {
                renderUsers(result.data, true);
                window.__adminUsers = [...(window.__adminUsers || []), ...result.data];
            } else if (type === 'transactions') {
                renderTransactions(result.data, true);
                window.__adminTransactions = [...(window.__adminTransactions || []), ...result.data];
            }
        }
    } catch (e) {
        adminToast('Failed to load more ' + type, 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function loadAnalytics() {
    const result = await adminFetch('analytics', { cache: 'no-store' });
    if (!result.success || !result.data) {
        adminToast(result.error || 'Failed to load analytics', 'error');
        return;
    }

    const { stats, revenue, top_books, genres } = result.data;

    // Update Stats
    document.getElementById('statActiveMemberships').textContent = stats.active_memberships ?? 0;
    document.getElementById('statNewUsers').textContent = stats.new_users_7d ?? 0;
    document.getElementById('statMonthlyPurchases').textContent = stats.monthly_books_purchased ?? 0;

    renderRevenueChart(revenue);
    renderTopBooksChart(top_books);
    renderGenreChart(genres);
}

let charts = {};

function renderRevenueChart(data) {
    const options = {
        series: [{
            name: 'Revenue',
            data: data.map(d => Number((d.total / 100).toFixed(2)))
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: false },
            zoom: { enabled: false },
            foreColor: 'rgb(var(--color-on-surface-variant))'
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['rgb(var(--color-primary))'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100]
            }
        },
        xaxis: {
            categories: data.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: (val) => '$' + val
            }
        },
        grid: {
            borderColor: 'rgb(var(--color-outline-variant) / 0.1)',
            strokeDashArray: 4
        },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            y: { formatter: (val) => '$' + val }
        }
    };

    if (charts.revenue) charts.revenue.destroy();
    charts.revenue = new ApexCharts(document.querySelector("#revenueChart"), options);
    charts.revenue.render();
}

function renderTopBooksChart(data) {
    const options = {
        series: [{
            name: 'Sales',
            data: data.map(d => Number(d.sales || 0))
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: false },
            foreColor: 'rgb(var(--color-on-surface-variant))'
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '40%',
                distributed: true
            }
        },
        dataLabels: { enabled: false },
        colors: ['rgb(var(--color-primary))', 'rgb(var(--color-secondary))', 'rgb(var(--color-tertiary))', 'rgb(var(--color-error))', 'rgb(var(--color-primary-container))'],
        xaxis: {
            categories: data.map(d => {
                const words = d.name.split(' ');
                const lines = [];
                let currentLine = '';
                words.forEach(w => {
                    if (currentLine.length + w.length > 12) {
                        lines.push(currentLine.trim());
                        currentLine = w + ' ';
                    } else {
                        currentLine += w + ' ';
                    }
                });
                if (currentLine) lines.push(currentLine.trim());
                return lines;
            }),
            labels: {
                rotate: 0,
                style: {
                    fontSize: '11px',
                    fontFamily: 'Manrope',
                    lineHeight: '1.2'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: (val) => Math.floor(val)
            },
            tickAmount: Math.max(1, ...data.map(d => Number(d.sales || 0))),
            min: 0
        },
        grid: {
            borderColor: 'rgb(var(--color-outline-variant) / 0.1)',
            strokeDashArray: 4
        },
        legend: { show: false },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
        }
    };

    if (charts.topBooks) charts.topBooks.destroy();
    charts.topBooks = new ApexCharts(document.querySelector("#topBooksChart"), options);
    charts.topBooks.render();
}

function renderGenreChart(data) {
    const options = {
        series: data.map(d => Number(d.count || 0)),
        labels: data.map(d => d.genre.replace('_', ' ')),
        dataLabels: {
            formatter: (val) => Math.round(val) + '%',
            style: {
                fontSize: '13px',
                fontFamily: 'Manrope',
                fontWeight: '800',
                colors: ['#111827']
            },
            dropShadow: {
                enabled: true,
                top: 0,
                left: 0,
                blur: 3,
                color: '#fff',
                opacity: 1
            }
        },
        chart: {
            type: 'donut',
            height: 350,
            foreColor: 'rgb(var(--color-on-surface-variant))',
            fontFamily: 'Manrope'
        },
        colors: ['#4F1BF1', '#7C52FF', '#A384FF', '#C9B6FF', '#EFEDFF'],
        stroke: { show: false },
        legend: {
            position: 'bottom',
            fontFamily: 'Manrope'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Books',
                            color: 'rgb(var(--color-on-surface-variant))',
                            fontSize: '14px',
                            fontFamily: 'Manrope',
                            fontWeight: 600,
                            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        },
                        value: {
                            show: true,
                            fontSize: '24px',
                            fontFamily: 'Manrope',
                            fontWeight: 900,
                            color: 'rgb(var(--color-on-surface))',
                            offsetY: 10
                        },
                        name: {
                            show: true,
                            fontSize: '14px',
                            fontFamily: 'Manrope',
                            fontWeight: 600,
                            color: 'rgb(var(--color-on-surface-variant))',
                            offsetY: -10
                        }
                    }
                }
            }
        },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
        }
    };

    if (charts.genre) charts.genre.destroy();
    charts.genre = new ApexCharts(document.querySelector("#genreChart"), options);
    charts.genre.render();
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

    let rawPrice = String(formData.get('online_buy_price') || '').replace(/[^0-9.]/g, '');
    let priceCents = Math.round(Number(rawPrice || 0) * 100);
    formData.set('online_buy_price', priceCents);

    const pdfInput = document.getElementById('adminBookOnlinePdf');
    const existingPdfEl = document.getElementById('adminBookExistingOnlinePdf');
    if (!id && (!pdfInput.files || pdfInput.files.length === 0) && (!existingPdfEl || !existingPdfEl.value)) {
        adminToast('Electronic copy (EPUB) is mandatory for new books', 'error');
        return;
    }

    const coverInput = document.getElementById('adminBookCoverImage');
    const existingCoverEl = document.getElementById('adminBookExistingCoverImage');
    if (!id && (!coverInput.files || coverInput.files.length === 0) && (!existingCoverEl || !existingCoverEl.value)) {
        adminToast('Cover image is mandatory for new books', 'error');
        return;
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
    const existingProfileImageEl = document.getElementById('adminUserExistingProfileImage');
    const profileInputEl = document.getElementById('adminUserProfileImage');
    const profilePreviewEl = document.getElementById('adminUserProfilePreview');
    const profilePlaceholderEl = document.getElementById('adminUserProfilePlaceholder');
    const adminRoleOption = roleEl ? roleEl.querySelector('option[value="ADMIN"]') : null;
    const setProfileImage = (url) => {
        const finalUrl = adminUserProfileAssetUrl(url);
        if (!profilePreviewEl || !profilePlaceholderEl) return;
        if (!finalUrl) {
            profilePreviewEl.removeAttribute('src');
            profilePreviewEl.classList.add('hidden');
            profilePlaceholderEl.classList.remove('hidden');
            return;
        }
        profilePreviewEl.src = finalUrl;
        profilePreviewEl.classList.remove('hidden');
        profilePlaceholderEl.classList.add('hidden');
    };

    if (user) {
        idEl.value = user.id || '';
        document.getElementById('adminUserFirstName').value = user.first_name || '';
        document.getElementById('adminUserLastName').value = user.last_name || '';
        document.getElementById('adminUserEmail').value = user.email || '';
        document.getElementById('adminUserPhone').value = user.phone_number || '';
        document.getElementById('adminUserDob').value = user.dob ? String(user.dob).slice(0, 10) : '';
        document.getElementById('adminUserRole').value = user.role || 'USER';
        document.getElementById('adminUserStatus').value = Number(user.is_active) === 1 ? '1' : '0';
        if (existingProfileImageEl) existingProfileImageEl.value = user.profile_image || '';
        setProfileImage(user.profile_image || '');
        if (titleEl) titleEl.textContent = 'Edit Member';
        if (subtitleEl) subtitleEl.textContent = "Modify this user account's credentials, role, status or picture.";
        if (saveBtnEl) saveBtnEl.textContent = 'Save Changes';
        if (adminRoleOption) {
            // Disable ADMIN option if an admin already exists AND this user is not currently an admin
            adminRoleOption.disabled = window.__adminExists && user.role !== 'ADMIN';
        }
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
        document.getElementById('adminUserPhone').value = '';
        document.getElementById('adminUserDob').value = '';
        document.getElementById('adminUserRole').value = preferredRole === 'LIBRARIAN' ? 'LIBRARIAN' : 'USER';
        document.getElementById('adminUserStatus').value = '1';
        if (existingProfileImageEl) existingProfileImageEl.value = '';
        setProfileImage('');
        if (titleEl) titleEl.textContent = 'Add New Member';
        if (subtitleEl) subtitleEl.textContent = preferredRole === 'LIBRARIAN' ? 'Create a librarian account for this branch.' : 'Create a user account for this branch.';
        if (saveBtnEl) saveBtnEl.textContent = preferredRole === 'LIBRARIAN' ? 'Create Librarian' : 'Create User';
        if (adminRoleOption) {
            // Disable ADMIN option for new users if an admin already exists
            adminRoleOption.disabled = window.__adminExists;
        }
        if (passwordBlockEl) passwordBlockEl.classList.remove('hidden');
        if (passwordEl) {
            passwordEl.value = '';
            passwordEl.required = true;
        }
    }
    if (profileInputEl) profileInputEl.value = '';

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
        body: formData
    });

    if (!result || !result.success) {
        adminToast((result && result.message) || (result && result.error) || 'Failed to save user', 'error');
        return;
    }

    document.getElementById('adminUserModal').close();
    adminToast(result.message || 'User saved successfully', 'success');
    setTimeout(() => {
        window.location.reload();
    }, 1000);
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
    setTimeout(() => {
        window.location.reload();
    }, 1000);
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
    const userProfileInput = document.getElementById('adminUserProfileImage');
    const userProfilePreview = document.getElementById('adminUserProfilePreview');
    const userProfilePlaceholder = document.getElementById('adminUserProfilePlaceholder');

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
            if (file) {
                const extension = file.name.split('.').pop().toLowerCase();
                if (extension !== 'epub') {
                    adminToast('Only .epub files are allowed', 'error');
                    pdfInput.value = '';
                    pdfFilename.textContent = 'No file selected';
                    return;
                }
            }
            pdfFilename.textContent = file ? file.name : 'No file selected';
        });
    }

    if (userProfileInput && userProfilePreview && userProfilePlaceholder) {
        userProfileInput.addEventListener('change', () => {
            const file = userProfileInput.files && userProfileInput.files[0];
            if (!file) return;
            const src = URL.createObjectURL(file);
            userProfilePreview.src = src;
            userProfilePreview.classList.remove('hidden');
            userProfilePlaceholder.classList.add('hidden');
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
                b.classList.remove('bg-primary', 'text-on-primary');
                b.classList.add('text-on-surface-variant');
            });
            btn.classList.add('bg-primary', 'text-on-primary');
            btn.classList.remove('text-on-surface-variant');
            
            const allTxs = window.__adminTransactions || [];
            if (filter === 'all') {
                renderTransactions(allTxs);
            } else {
                renderTransactions(allTxs.filter(tx => tx.type === filter));
            }
        });
    });

    // Pagination Listeners
    document.getElementById('adminLoadMoreBooksBtn')?.addEventListener('click', () => loadMoreSection('books'));
    document.getElementById('adminLoadMoreUsersBtn')?.addEventListener('click', () => loadMoreSection('users'));
    document.getElementById('adminLoadMoreTransactionsBtn')?.addEventListener('click', () => loadMoreSection('transactions'));
}

document.addEventListener('DOMContentLoaded', () => {
    window.__adminActiveTab = 'books';
    bindAdminEvents();
    setActiveTab('books');
    loadAdminDashboard().catch(() => {
        adminToast('Failed to load admin dashboard', 'error');
    });
});

