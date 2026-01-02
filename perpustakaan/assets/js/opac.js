// ==========================================
// OPAC PAGE JAVASCRIPT - COMPLETE VERSION
// ==========================================

(function() {
    'use strict';
    
    console.log('🔍 OPAC.js loaded');

    // ==========================================
    // STATE MANAGEMENT
    // ==========================================
    let currentFilter = 'all';
    let currentSort = 'relevance';
    let currentSearch = '';
    let currentCategory = 'all';

    // ==========================================
    // SAKURA ANIMATION
    // ==========================================

    function createSakuraBg() {
        const container = document.getElementById('sakura-container-bg');
        if (!container) return;
        
        const sakura = document.createElement("div");
        sakura.classList.add("sakura-bg");
        
        sakura.style.left = Math.random() * 100 + "%";
        sakura.style.top = (Math.random() * -30 - 20) + "px";
        
        const size = Math.random() * 12 + 8;
        sakura.style.width = size + "px";
        sakura.style.height = size + "px";
        
        const duration = Math.random() * 10 + 15;
        sakura.style.animationDuration = duration + "s";

        container.appendChild(sakura);

        setTimeout(() => {
            sakura.remove();
        }, (duration + 2) * 1000);
    }

    // Spawn sakura
    setInterval(() => {
        createSakuraBg();
    }, 400);

    setInterval(() => {
        if(Math.random() > 0.5) {
            createSakuraBg();
        }
    }, 600);

    console.log('🌸 Sakura animation started');

    // ==========================================
    // FETCH BOOKS FROM SERVER
    // ==========================================

    function fetchBooks() {
        const booksGrid = document.getElementById('booksGrid');
        const resultCount = document.getElementById('resultCount');
        
        // Show loading
        booksGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #F875AA;">Loading...</div>';
        
        // Build URL with parameters
        const params = new URLSearchParams({
            search: currentSearch,
            category: currentCategory,
            filter: currentFilter,
            sort: currentSort
        });
        
        fetch(`../api/search_books.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBooks(data.books);
                    resultCount.textContent = data.count;
                } else {
                    booksGrid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #ff6b6b;">Error: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching books:', error);
                booksGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #ff6b6b;">Terjadi kesalahan saat memuat data</div>';
            });
    }

    // ==========================================
    // DISPLAY BOOKS
    // ==========================================

    function displayBooks(books) {
        const booksGrid = document.getElementById('booksGrid');
        
        if (books.length === 0) {
            booksGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">Tidak ada buku ditemukan</div>';
            return;
        }
        
        booksGrid.innerHTML = books.map(book => `
            <div class="book-card" data-book-id="${book.id}">
                <div class="book-cover">
                    ${book.cover_image ? 
                        `<img src="/perpustakaan/uploads/covers/${book.cover_image}" alt="${escapeHtml(book.title)}" onerror="this.parentElement.innerHTML='<span>No Cover</span>'">` : 
                        '<span>No Cover</span>'
                    }
                </div>
                <div class="book-info">
                    <h3 class="book-title">${escapeHtml(book.title)}</h3>
                    <p class="book-author">${escapeHtml(book.author)}</p>
                    <div class="book-meta">
                        <span>${book.publish_year || '-'}</span>
                        <span class="book-status ${book.status === 'available' ? 'status-available' : 'status-borrowed'}">
                            ${book.status === 'available' ? 'Tersedia' : 'Dipinjam'}
                        </span>
                    </div>
                </div>
            </div>
        `).join('');
        
        // Re-attach click events
        attachBookCardEvents();
    }

    // ==========================================
    // ESCAPE HTML
    // ==========================================

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==========================================
    // FILTER TAGS
    // ==========================================

    const filterTags = document.querySelectorAll('.filter-tag');
    filterTags.forEach(tag => {
        tag.addEventListener('click', function() {
            filterTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            currentFilter = this.dataset.filter;
            console.log('Filter:', currentFilter);
            
            fetchBooks();
        });
    });

    // ==========================================
    // SEARCH FORM
    // ==========================================

    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            currentSearch = document.getElementById('searchInput').value;
            currentCategory = document.getElementById('searchCategory').value;
            
            console.log('Search:', currentSearch, 'Category:', currentCategory);
            
            fetchBooks();
        });
    }

    // ==========================================
    // SORT BY
    // ==========================================

    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentSort = this.value;
            console.log('Sort by:', currentSort);
            
            fetchBooks();
        });
    }

    // ==========================================
    // BOOK DETAIL MODAL
    // ==========================================

    function showBookDetail(bookId) {
        const modal = document.getElementById('bookDetailModal');
        
        // Fetch book details from server
        fetch(`../api/get_book_detail.php?id=${bookId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const book = data.book;
                    
                    // Update modal content
                    const modalCover = document.querySelector('.modal-book-cover');
                    if (book.cover_image) {
                        modalCover.innerHTML = `<img src="../uploads/covers/${book.cover_image}" alt="${escapeHtml(book.title)}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
                    } else {
                        modalCover.innerHTML = '<span>No Cover</span>';
                    }
                    
                    document.getElementById('modalBookTitle').textContent = book.title;
                    document.getElementById('modalBookAuthor').textContent = book.author;
                    document.getElementById('modalISBN').textContent = book.isbn || '-';
                    document.getElementById('modalPublisher').textContent = book.publisher || '-';
                    document.getElementById('modalPublishYear').textContent = book.publish_year || '-';
                    document.getElementById('modalPublishPlace').textContent = book.publish_place || '-';
                    document.getElementById('modalPages').textContent = book.pages ? book.pages + ' halaman' : '-';
                    document.getElementById('modalLanguage').textContent = book.language || '-';
                    document.getElementById('modalCategory').textContent = book.category || '-';
                    document.getElementById('modalCallNumber').textContent = book.call_number || '-';
                    document.getElementById('modalCopies').textContent = book.total_copies + ' eksemplar';
                    document.getElementById('modalAvailable').textContent = book.available_copies + ' tersedia';
                    document.getElementById('modalDescription').textContent = book.description || 'Tidak ada deskripsi';

                    // Update status badge
                    const statusBadge = document.getElementById('modalBookStatus');
                    const borrowBtn = document.getElementById('btnBorrow');
                    
                    if (book.status === 'available') {
                        statusBadge.className = 'modal-book-status status-available';
                        statusBadge.textContent = 'Tersedia';
                        borrowBtn.disabled = false;
                        borrowBtn.style.opacity = '1';
                        borrowBtn.style.cursor = 'pointer';
                    } else {
                        statusBadge.className = 'modal-book-status status-borrowed';
                        statusBadge.textContent = 'Tidak Tersedia';
                        borrowBtn.disabled = true;
                        borrowBtn.style.opacity = '0.5';
                        borrowBtn.style.cursor = 'not-allowed';
                    }
                    
                    // Store book ID for borrow action
                    borrowBtn.dataset.bookId = bookId;

                    // Show modal
                    modal.classList.add('active');
                } else {
                    alert('Gagal memuat detail buku: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching book detail:', error);
                alert('Terjadi kesalahan saat memuat detail buku');
            });
    }

    function closeBookDetail() {
        const modal = document.getElementById('bookDetailModal');
        modal.classList.remove('active');
    }

    // Close modal when clicking outside
    const modal = document.getElementById('bookDetailModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeBookDetail();
            }
        });
    }

    // Close button
    const closeBtn = document.querySelector('.modal-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeBookDetail);
    }

    // ==========================================
    // BOOK CARD CLICK
    // ==========================================

    function attachBookCardEvents() {
        const bookCards = document.querySelectorAll('.book-card');
        bookCards.forEach(card => {
            card.addEventListener('click', function() {
                const bookId = this.dataset.bookId;
                showBookDetail(bookId);
            });
        });
    }

    // ==========================================
    // BORROW BUTTON
    // ==========================================

    const borrowBtn = document.getElementById('btnBorrow');
    if (borrowBtn) {
        borrowBtn.addEventListener('click', function() {
            const bookId = this.dataset.bookId;
            
            if (confirm('Apakah Anda yakin ingin meminjam buku ini?')) {
                // Show loading
                borrowBtn.textContent = 'Memproses...';
                borrowBtn.disabled = true;
                
                fetch('../api/borrow_book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ book_id: bookId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Peminjaman berhasil! Kode transaksi: ' + data.transaction_code);
                        closeBookDetail();
                        fetchBooks(); // Refresh book list
                    } else {
                        alert('Gagal meminjam buku: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error borrowing book:', error);
                    alert('Terjadi kesalahan saat meminjam buku');
                })
                .finally(() => {
                    borrowBtn.textContent = 'Pinjam Buku';
                    borrowBtn.disabled = false;
                });
            }
        });
    }

    // ==========================================
    // INITIAL LOAD
    // ==========================================

    // Load books on page load
    fetchBooks();

    console.log('✅ OPAC functions initialized');

})(); // End IIFE