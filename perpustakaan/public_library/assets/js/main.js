// ========================================
// DIGITAL LIBRARY - MAIN JAVASCRIPT
// Japanese Anime Aesthetic
// ========================================

let currentBooks = [];
let selectedBook = null;
let currentCategory = 'all';

// ========================================
// INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    loadAllBooks();
});

function initializeEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('bookSearch');
    const searchBtn = document.getElementById('searchBtn');
    
    // Real-time search on input
    searchInput.addEventListener('input', function() {
        performSearch();
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
    
    searchBtn.addEventListener('click', performSearch);
    
    // Category filter tabs
    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active state
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            currentCategory = this.dataset.category;
            performSearch();
        });
    });
    
    // Modal close
    const closeModal = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBorrow');
    const modalOverlay = document.getElementById('borrowModal');
    
    if (closeModal) {
        closeModal.addEventListener('click', closeBorrowModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeBorrowModal);
    }
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeBorrowModal();
            }
        });
    }
    
    // Borrow form submission
    const borrowForm = document.getElementById('borrowForm');
    if (borrowForm) {
        borrowForm.addEventListener('submit', handleBorrow);
    }
}

// ========================================
// LOAD BOOKS
// ========================================
function loadAllBooks() {
    showLoading();
    fetch('api/search_books.php')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                currentBooks = data.books;
                displayBooks(data.books);
            } else {
                showEmptyState();
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showError('Terjadi kesalahan saat memuat buku');
        });
}

// Real-time search with debounce
let searchTimeout;
function performSearch() {
    clearTimeout(searchTimeout);
    const searchTerm = document.getElementById('bookSearch').value.trim();
    
    searchTimeout = setTimeout(() => {
        showLoading();
        
        let url = 'api/search_books.php?';
        if (searchTerm) {
            url += `search=${encodeURIComponent(searchTerm)}&`;
        }
        if (currentCategory !== 'all') {
            url += `category=${encodeURIComponent(currentCategory)}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    currentBooks = data.books;
                    if (data.books.length > 0) {
                        displayBooks(data.books);
                    } else {
                        showEmptyState();
                    }
                } else {
                    showEmptyState();
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showError('Terjadi kesalahan saat mencari buku');
            });
    }, 300); // 300ms debounce
}

// ========================================
// DISPLAY BOOKS
// ========================================
function displayBooks(books) {
    const grid = document.getElementById('booksGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (books.length === 0) {
        grid.innerHTML = '';
        showEmptyState();
        return;
    }
    
    emptyState.style.display = 'none';
    grid.innerHTML = books.map((book, index) => {
        const card = createBookCard(book);
        // Add animation delay for staggered effect
        return card.replace('<div class="book-card">', `<div class="book-card" style="animation-delay: ${index * 0.05}s">`);
    }).join('');
    
    // Add event listeners to borrow buttons and card click
    setTimeout(() => {
        books.forEach(book => {
            const borrowBtn = document.querySelector(`[data-book-id="${book.id}"]`);
            if (borrowBtn) {
                borrowBtn.addEventListener('click', () => openBorrowModal(book));
            }

            const card = borrowBtn ? borrowBtn.closest('.book-card') : null;
            if (card) {
                card.addEventListener('click', (e) => {
                    // avoid double trigger when clicking borrow button
                    if (e.target.closest('.btn-borrow-card')) return;
                    openDetailModal(book);
                });
            }
        });
    }, 100);
}

function createBookCard(book) {
    const coverUrl = book.cover_image || '';
    const isAvailable = book.status === 'available' && book.available_copies > 0;
    
    return `
        <div class="book-card">
            <div class="book-cover-wrapper">
                ${coverUrl ? 
                    `<img src="${coverUrl}" alt="${escapeHtml(book.title)}" onerror="this.parentElement.innerHTML='<div class=\\'book-cover-placeholder\\'>📚</div>'">` :
                    `<div class="book-cover-placeholder">📚</div>`
                }
            </div>
            <div class="book-info">
                <h3 class="book-title">${escapeHtml(book.title)}</h3>
                <p class="book-author">${escapeHtml(book.author || 'Unknown Author')}</p>
                <div class="book-meta">
                    <span>${book.category || 'Uncategorized'}</span>
                    <span class="status-badge ${isAvailable ? 'status-available' : 'status-unavailable'}">
                        ${isAvailable ? '利用可能' : '貸出中'}
                    </span>
                </div>
                <button 
                    class="btn-borrow-card" 
                    data-book-id="${book.id}"
                    ${!isAvailable ? 'disabled' : ''}
                >
                    ${isAvailable ? '借りる' : '利用不可'}
                </button>
            </div>
        </div>
    `;
}

// ========================================
// BORROW MODAL
// ========================================
function openBorrowModal(book) {
    selectedBook = book;
    const modal = document.getElementById('borrowModal');
    const preview = document.getElementById('borrowBookPreview');
    const form = document.getElementById('borrowForm');
    
    // Reset form
    form.reset();
    
    // Set book preview
    preview.innerHTML = `
        <div class="book-preview-title">${escapeHtml(book.title)}</div>
        <div class="book-preview-author">${escapeHtml(book.author || 'Unknown Author')}</div>
    `;
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Focus on input
    setTimeout(() => {
        document.getElementById('memberIdInput').focus();
    }, 100);
}

function closeBorrowModal() {
    const modal = document.getElementById('borrowModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    selectedBook = null;
}

// ========================================
// DETAIL MODAL
// ========================================
function openDetailModal(book) {
    const detailModal = document.getElementById('detailModal');
    const coverEl = document.getElementById('detailCover');
    const titleEl = document.getElementById('detailTitle');
    const authorEl = document.getElementById('detailAuthor');
    const pagesEl = document.getElementById('detailPages');
    const dateEl = document.getElementById('detailDate');
    const synopsisEl = document.getElementById('detailSynopsis');
    const detailBorrowBtn = document.getElementById('detailBorrowBtn');
    const closeDetail = document.getElementById('closeDetail');

    if (!detailModal) return;

    selectedBook = book;

    // Cover
    if (book.cover_image) {
        coverEl.innerHTML = `<img src="${book.cover_image}" alt="${escapeHtml(book.title)}" onerror="this.parentElement.innerHTML='<div class=\\'cover-placeholder\\'>📚</div>'">`;
    } else {
        coverEl.innerHTML = `<div class="cover-placeholder">📚</div>`;
    }

    titleEl.textContent = book.title || 'Judul tidak tersedia';
    authorEl.textContent = book.author ? `Nama Pengarang: ${book.author}` : 'Nama Pengarang: -';
    
    const pages = book.pages ? `${book.pages} halaman` : 'Jumlah halaman belum tersedia';
    pagesEl.textContent = pages;

    const publish = book.publish_year ? `Terbit: ${book.publish_year}` : 'Tanggal terbit belum tersedia';
    dateEl.textContent = publish;

    const synopsis = book.description && book.description.trim().length > 0
        ? book.description
        : 'Kisah ini menunggu untuk kamu temukan...';
    synopsisEl.textContent = synopsis;

    detailModal.classList.add('active');
    document.body.style.overflow = 'hidden';

    if (detailBorrowBtn) {
        detailBorrowBtn.onclick = () => openBorrowModal(book);
    }

    if (closeDetail) {
        closeDetail.onclick = closeDetailModal;
    }

    detailModal.onclick = (e) => {
        if (e.target === detailModal) {
            closeDetailModal();
        }
    };
}

function closeDetailModal() {
    const detailModal = document.getElementById('detailModal');
    if (detailModal) {
        detailModal.classList.remove('active');
    }
    document.body.style.overflow = '';
}

function handleBorrow(e) {
    e.preventDefault();
    
    const memberId = document.getElementById('memberIdInput').value.trim();
    
    if (!memberId) {
        Swal.fire({
            icon: 'warning',
            title: '⚠️ 注意',
            text: 'Mohon masukkan Member ID',
            confirmButtonColor: '#F875AA',
            confirmButtonText: '了解'
        });
        return;
    }
    
    if (!selectedBook) {
        return;
    }
    
    // Disable form
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = '処理中...';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'create_borrow');
    formData.append('member_code', memberId);
    formData.append('book_code', selectedBook.call_number || selectedBook.isbn);
    formData.append('borrow_date', new Date().toISOString().split('T')[0]);
    
    // Calculate due date (7 days from now)
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 7);
    formData.append('due_date', dueDate.toISOString().split('T')[0]);
    
    // Send request to public borrowing API
    fetch('api/borrow.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success with Japanese style message
            Swal.fire({
                icon: 'success',
                title: '🎉 おめでとうございます！',
                html: `
                    <p style="font-size: 18px; margin: 20px 0;">本を借りることができました！</p>
                    <p style="color: #666; margin-bottom: 10px;"><strong>Transaction ID:</strong> ${data.transaction_code}</p>
                    ${data.copy_number ? `<p style="color: #666;"><strong>Copy:</strong> ${data.copy_number}</p>` : ''}
                `,
                confirmButtonColor: '#F875AA',
                confirmButtonText: '了解',
                customClass: {
                    popup: 'anime-swal-popup',
                    title: 'anime-swal-title',
                    confirmButton: 'anime-swal-button'
                }
            }).then(() => {
                closeBorrowModal();
                // Reload books to update availability
                performSearch();
            });
        } else {
            // Error message
            Swal.fire({
                icon: 'error',
                title: '❌ エラー',
                text: data.message || 'Gagal meminjam buku',
                confirmButtonColor: '#F875AA',
                confirmButtonText: '了解'
            });
            
            // Re-enable form
            submitBtn.disabled = false;
            submitBtn.textContent = '借りる';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: '❌ エラー',
            text: 'Terjadi kesalahan saat memproses peminjaman',
            confirmButtonColor: '#F875AA',
            confirmButtonText: '了解'
        });
        
        // Re-enable form
        submitBtn.disabled = false;
        submitBtn.textContent = '借りる';
    });
}

// ========================================
// UI HELPERS
// ========================================
function showLoading() {
    const loadingState = document.getElementById('loadingState');
    const booksGrid = document.getElementById('booksGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (loadingState) loadingState.style.display = 'block';
    if (booksGrid) booksGrid.style.display = 'none';
    if (emptyState) emptyState.style.display = 'none';
}

function hideLoading() {
    const loadingState = document.getElementById('loadingState');
    const booksGrid = document.getElementById('booksGrid');
    
    if (loadingState) {
        loadingState.style.display = 'none';
    }
    if (booksGrid) {
        booksGrid.style.display = 'grid';
        // Trigger fade-in animation
        booksGrid.style.animation = 'fadeInUp 0.5s ease-out';
    }
}

function showEmptyState() {
    const emptyState = document.getElementById('emptyState');
    const booksGrid = document.getElementById('booksGrid');
    
    if (emptyState) {
        emptyState.style.display = 'block';
        emptyState.style.animation = 'fadeInUp 0.5s ease-out';
    }
    if (booksGrid) {
        booksGrid.innerHTML = '';
        booksGrid.style.display = 'none';
    }
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: '❌ エラー',
        text: message,
        confirmButtonColor: '#F875AA',
        confirmButtonText: '了解'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

