// ==========================================
// BIBLIOGRAFI PAGE - FIXED VERSION
// ==========================================

(function() {
    'use strict';
    
    console.log('📚 Bibliografi.js loaded');

    let currentPage = 1;
    let totalPages = 1;
    let currentBookId = null;

    // ==========================================
    // TOAST NOTIFICATION
    // ==========================================
    
    function showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }
        
        const colors = { 
            success: '#10b981', 
            error: '#ef4444', 
            warning: '#f59e0b', 
            info: '#3b82f6' 
        };
        const icons = { 
            success: '✓', 
            error: '✕', 
            warning: '⚠', 
            info: 'ℹ' 
        };
        
        const toast = document.createElement('div');
        toast.style.cssText = `
            background: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 320px;
            border-left: 4px solid ${colors[type]};
            animation: slideIn 0.3s ease-out;
        `;
        
        toast.innerHTML = `
            <div style="width: 24px; height: 24px; border-radius: 50%; background: ${colors[type]}; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">${icons[type]}</div>
            <div style="flex: 1; color: #333; font-size: 14px;">${message}</div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #999; cursor: pointer; font-size: 20px; padding: 0; width: 24px; height: 24px; flex-shrink: 0;">×</button>
        `;
        
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    window.showToast = showToast;

    // ==========================================
    // LOAD BOOKS
    // ==========================================

    function loadBooks() {
        const searchTerm = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        
        const params = new URLSearchParams({
            search: searchTerm,
            category: category,
            page: currentPage
        });
        
        console.log('Loading books with params:', params.toString());
        
        const tbody = document.querySelector('#booksTable tbody');
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #999;">Memuat data...</td></tr>';
        
        fetch(`../api/get_all_books.php?${params.toString()}`)
            .then(response => {
                console.log('Response status:', response.status);
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server tidak mengembalikan JSON');
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data);
                
                if (data.success) {
                    displayBooks(data.data);
                    totalPages = data.pagination.total_pages;
                    updatePagination(data.pagination);
                } else {
                    showToast(data.message || 'Gagal memuat data', 'error');
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #ef4444;">Gagal memuat data</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading books:', error);
                showToast('Terjadi kesalahan saat memuat data: ' + error.message, 'error');
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 40px; color: #ef4444;">Terjadi kesalahan: ${error.message}</td></tr>`;
            });
    }

    // ==========================================
    // DISPLAY BOOKS
    // ==========================================

    function displayBooks(books) {
        const tbody = document.querySelector('#booksTable tbody');
        
        if (books.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                        Tidak ada buku ditemukan
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = books.map(book => `
            <tr>
                <td>
                    <div class="book-cover-mini">
                        ${book.cover_image ? 
                            `<img src="../uploads/covers/${book.cover_image}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">` : 
                            'No Cover'
                        }
                    </div>
                </td>
                <td>${escapeHtml(book.title)}</td>
                <td>${escapeHtml(book.author)}</td>
                <td>${book.isbn || '-'}</td>
                <td>${book.publish_year || '-'}</td>
                <td>${book.total_copies}</td>
                <td>
                    <span class="status-badge ${book.available_copies > 0 ? 'status-available' : 'status-unavailable'}">
                        ${book.available_copies > 0 ? 'Tersedia (' + book.available_copies + ')' : 'Habis'}
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <button class="btn-icon btn-barcode" onclick="generateBarcode(${book.id})" title="Generate Barcode">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <line x1="7" y1="8" x2="7" y2="16"/>
                                <line x1="11" y1="8" x2="11" y2="16"/>
                                <line x1="15" y1="8" x2="15" y2="16"/>
                                <line x1="17" y1="8" x2="17" y2="16"/>
                            </svg>
                        </button>
                        <button class="btn-icon btn-edit" onclick="editBook(${book.id})" title="Edit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteBook(${book.id})" title="Hapus">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==========================================
    // PAGINATION
    // ==========================================

    function updatePagination(pagination) {
        const container = document.querySelector('.pagination');
        
        if (pagination.total_pages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = '';
        
        if (currentPage > 1) {
            html += `<button class="page-btn" data-page="${currentPage - 1}">« Prev</button>`;
        }
        
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === currentPage) {
                html += `<button class="page-btn active">${i}</button>`;
            } else if (i === 1 || i === pagination.total_pages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<button class="page-btn" data-page="${i}">${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span style="color: #999;">...</span>`;
            }
        }
        
        if (currentPage < pagination.total_pages) {
            html += `<button class="page-btn" data-page="${currentPage + 1}">Next »</button>`;
        }
        
        container.innerHTML = html;
        
        container.querySelectorAll('.page-btn[data-page]').forEach(btn => {
            btn.addEventListener('click', function() {
                currentPage = parseInt(this.dataset.page);
                loadBooks();
            });
        });
    }

    // ==========================================
    // MODAL FUNCTIONS
    // ==========================================

    window.openAddModal = function() {
        currentBookId = null;
        document.getElementById('modalTitle').textContent = 'Tambah Buku Baru';
        document.getElementById('bookForm').reset();
        document.getElementById('coverPreview').innerHTML = '';
        document.getElementById('bookModal').classList.add('active');
    };

    window.editBook = function(id) {
        currentBookId = id;
        
        console.log('Editing book:', id);
        
        fetch(`../api/get_book_detail.php?id=${id}`)
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server tidak mengembalikan JSON');
                }
                return response.json();
            })
            .then(data => {
                console.log('Book detail:', data);
                
                if (data.success) {
                    const book = data.book;
                    document.getElementById('modalTitle').textContent = 'Edit Buku';
                    
                    document.querySelector('[name="title"]').value = book.title;
                    document.querySelector('[name="author"]').value = book.author;
                    document.querySelector('[name="isbn"]').value = book.isbn || '';
                    document.querySelector('[name="publisher"]').value = book.publisher || '';
                    document.querySelector('[name="publish_year"]').value = book.publish_year || '';
                    document.querySelector('[name="publish_place"]').value = book.publish_place || '';
                    document.querySelector('[name="pages"]').value = book.pages || '';
                    document.querySelector('[name="language"]').value = book.language || '';
                    document.querySelector('[name="category"]').value = book.category || '';
                    document.querySelector('[name="call_number"]').value = book.call_number || '';
                    document.querySelector('[name="total_copies"]').value = book.total_copies;
                    document.querySelector('[name="description"]').value = book.description || '';
                    
                    if (book.cover_image) {
                        document.getElementById('coverPreview').innerHTML = `
                            <img src="../uploads/covers/${book.cover_image}" style="max-width: 200px; border-radius: 8px;">
                            <p style="font-size: 12px; color: #666; margin-top: 8px;">Cover saat ini</p>
                        `;
                    }
                    
                    document.getElementById('bookModal').classList.add('active');
                } else {
                    showToast('Gagal memuat data buku', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            });
    };

    window.closeModal = function() {
        document.getElementById('bookModal').classList.remove('active');
    };

    window.deleteBook = function(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus buku ini?\n\nPeringatan: Buku yang memiliki riwayat transaksi tidak dapat dihapus.')) {
            return;
        }
        
        console.log('Deleting book:', id);
        
        // Show loading toast
        showToast('Menghapus buku...', 'info');
        
        fetch('../api/delete_book.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ book_id: id })
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            console.log('Delete response headers:', response.headers);
            
            // Cek content type
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (!contentType || !contentType.includes('application/json')) {
                // Jika bukan JSON, ambil text untuk debugging
                return response.text().then(text => {
                    console.error('Response is not JSON:', text.substring(0, 500));
                    throw new Error('Server tidak mengembalikan JSON. Kemungkinan terjadi error di server. Periksa console untuk detail.');
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Delete response data:', data);
            
            if (data.success) {
                showToast(data.message || 'Buku berhasil dihapus', 'success');
                loadBooks();
            } else {
                showToast(data.message || 'Gagal menghapus buku', 'error');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showToast('Terjadi kesalahan: ' + error.message, 'error');
        });
    };

    window.generateBarcode = function(id) {
        window.open(`../api/generate_barcode.php?id=${id}`, '_blank', 'width=600,height=800');
    };

    // ==========================================
    // FORM SUBMIT
    // ==========================================

    document.getElementById('bookForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        if (currentBookId) {
            formData.append('book_id', currentBookId);
        }
        
        const submitBtn = this.querySelector('[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
        
        console.log('Submitting form...');
        
        fetch('../api/save_book.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Save response status:', response.status);
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Response is not JSON:', text.substring(0, 500));
                    throw new Error('Server tidak mengembalikan JSON');
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Save response data:', data);
            
            if (data.success) {
                showToast(data.message || 'Buku berhasil disimpan', 'success');
                closeModal();
                loadBooks();
            } else {
                showToast(data.message || 'Gagal menyimpan buku', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan Buku';
        });
    });

    // ==========================================
    // COVER IMAGE PREVIEW
    // ==========================================

    document.querySelector('[name="cover_image"]')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('coverPreview');
        
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                showToast('Ukuran file terlalu besar. Maksimal 2MB', 'warning');
                this.value = '';
                return;
            }
            
            if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                showToast('Format file tidak didukung. Gunakan JPG, PNG, atau GIF', 'warning');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" style="max-width: 200px; border-radius: 8px;">
                    <p style="font-size: 12px; color: #666; margin-top: 8px;">Preview cover baru</p>
                `;
            };
            reader.readAsDataURL(file);
        }
    });

    // ==========================================
    // SEARCH & FILTER
    // ==========================================

    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadBooks();
        }, 500);
    });

    document.getElementById('categoryFilter').addEventListener('change', function() {
        currentPage = 1;
        loadBooks();
    });

    // ==========================================
    // MODAL EVENTS
    // ==========================================

    document.getElementById('bookModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('bookModal').classList.contains('active')) {
            closeModal();
        }
    });

    // ==========================================
    // INITIAL LOAD
    // ==========================================

    console.log('Initializing bibliografi...');
    loadBooks();

    console.log('✅ Bibliografi initialized');


})();

// ==========================================
// BOOK COPIES MANAGEMENT - TAMBAHAN BARU
// Paste code ini DI BAWAH })(); yang lama
// ==========================================

let currentCopies = [];
let currentCopyId = null;

window.openCopiesModal = function(bookId) {
    window.currentBookId = bookId;
    console.log('Opening copies modal for book:', bookId);
    
    // Ambil info buku
    const book = document.querySelector(`#booksTable tbody tr td:nth-child(2)`);
    const bookTitle = book ? book.textContent : 'Buku';
    document.getElementById('copiesBookTitle').textContent = bookTitle;
    
    document.getElementById('copiesModal').classList.add('active');
    loadBookCopies(bookId);
};

function loadBookCopies(bookId) {
    console.log('Loading copies for book:', bookId);
    
    const tbody = document.querySelector('#copiesTable tbody');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">Memuat data eksemplar...</td></tr>';
    
    fetch(`../api/book_copies/get.php?book_id=${bookId}`)
        .then(response => {
            console.log('Get copies response:', response.status);
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server tidak mengembalikan JSON');
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Copies data:', data);
            
            if (data.success) {
                currentCopies = data.copies;
                displayCopies(data.copies, data.summary);
            } else {
                showToast(data.message || 'Gagal memuat eksemplar', 'error');
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #ef4444;">Gagal memuat data</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading copies:', error);
            showToast('Terjadi kesalahan: ' + error.message, 'error');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #ef4444;">Terjadi kesalahan</td></tr>';
        });
}

function displayCopies(copies, summary) {
    console.log('Displaying copies:', copies);
    
    // Update summary cards
    document.getElementById('summaryAvailable').textContent = summary.available || 0;
    document.getElementById('summaryBorrowed').textContent = summary.borrowed || 0;
    document.getElementById('summaryMaintenance').textContent = summary.maintenance || 0;
    document.getElementById('summaryDamaged').textContent = (summary.damaged || 0) + (summary.lost || 0);
    
    const tbody = document.querySelector('#copiesTable tbody');
    
    if (copies.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                    Belum ada eksemplar. Klik "Tambah Eksemplar" untuk menambahkan.
                </td>
            </tr>
        `;
        return;
    }
    
    const statusLabels = {
        'available': 'Tersedia',
        'borrowed': 'Dipinjam',
        'maintenance': 'Maintenance',
        'lost': 'Hilang',
        'damaged': 'Rusak',
        'reserved': 'Reserved'
    };
    
    const conditionLabels = {
        'good': 'Baik',
        'fair': 'Cukup',
        'poor': 'Buruk'
    };
    
    tbody.innerHTML = copies.map(copy => `
        <tr>
            <td>${copy.copy_number}</td>
            <td>${copy.barcode || '-'}</td>
            <td>
                <select class="form-select" style="padding: 6px 10px; font-size: 13px;" 
                        onchange="updateCopyStatus(${copy.copy_id}, this.value)"
                        ${copy.status === 'borrowed' ? 'disabled' : ''}>
                    <option value="available" ${copy.status === 'available' ? 'selected' : ''}>Tersedia</option>
                    <option value="borrowed" ${copy.status === 'borrowed' ? 'selected' : ''}>Dipinjam</option>
                    <option value="maintenance" ${copy.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                    <option value="lost" ${copy.status === 'lost' ? 'selected' : ''}>Hilang</option>
                    <option value="damaged" ${copy.status === 'damaged' ? 'selected' : ''}>Rusak</option>
                    <option value="reserved" ${copy.status === 'reserved' ? 'selected' : ''}>Reserved</option>
                </select>
            </td>
            <td>${conditionLabels[copy.condition] || copy.condition}</td>
            <td>${copy.location}</td>
            <td>${copy.notes || '-'}</td>
            <td>
                <div class="action-btns">
                    <button class="btn-icon btn-edit" onclick="openEditCopyModal(${copy.copy_id})" title="Edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="btn-icon btn-delete" onclick="deleteCopy(${copy.copy_id})" title="Hapus"
                            ${copy.status === 'borrowed' ? 'disabled' : ''}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

window.openAddCopyModal = function() {
    console.log('Opening add copy modal');
    
    currentCopyId = null;
    document.getElementById('copyModalTitle').textContent = 'Tambah Eksemplar Baru';
    document.getElementById('copyForm').reset();
    document.querySelector('[name="copy_id"]').value = '';
    document.getElementById('copyFormModal').classList.add('active');
};

window.openEditCopyModal = function(copyId) {
    console.log('Opening edit copy modal:', copyId);
    
    const copy = currentCopies.find(c => c.copy_id == copyId);
    if (!copy) {
        showToast('Eksemplar tidak ditemukan', 'error');
        return;
    }
    
    currentCopyId = copyId;
    document.getElementById('copyModalTitle').textContent = 'Edit Eksemplar';
    
    const form = document.getElementById('copyForm');
    form.querySelector('[name="copy_id"]').value = copy.copy_id;
    form.querySelector('[name="copy_number"]').value = copy.copy_number;
    form.querySelector('[name="barcode"]').value = copy.barcode || '';
    form.querySelector('[name="status"]').value = copy.status;
    form.querySelector('[name="condition"]').value = copy.condition;
    form.querySelector('[name="location"]').value = copy.location;
    form.querySelector('[name="notes"]').value = copy.notes || '';
    
    document.getElementById('copyFormModal').classList.add('active');
};

window.closeCopyFormModal = function() {
    document.getElementById('copyFormModal').classList.remove('active');
};

window.handleCopySubmit = function(e) {
    e.preventDefault();
    
    console.log('Submitting copy form');
    
    const formData = new FormData(e.target);
    const data = {
        book_id: window.currentBookId,
        copy_id: formData.get('copy_id') || 0,
        copy_number: formData.get('copy_number'),
        barcode: formData.get('barcode'),
        status: formData.get('status'),
        condition: formData.get('condition'),
        location: formData.get('location'),
        notes: formData.get('notes')
    };
    
    console.log('Copy data:', data);
    
    const submitBtn = e.target.querySelector('[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    
    fetch('../api/book_copies/save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Save copy response:', response.status);
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Response is not JSON:', text.substring(0, 500));
                throw new Error('Server tidak mengembalikan JSON');
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Save copy result:', data);
        
        if (data.success) {
            showToast(data.message || 'Eksemplar berhasil disimpan', 'success');
            closeCopyFormModal();
            loadBookCopies(window.currentBookId);
            
            // Reload books table to update stock
            if (typeof loadBooks === 'function') {
                loadBooks();
            }
        } else {
            showToast(data.message || 'Gagal menyimpan eksemplar', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving copy:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Eksemplar';
    });
};

window.updateCopyStatus = function(copyId, newStatus) {
    console.log('Updating copy status:', copyId, newStatus);
    
    fetch('../api/book_copies/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            copy_id: copyId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Update status result:', data);
        
        if (data.success) {
            showToast(data.message || 'Status berhasil diubah', 'success');
            loadBookCopies(window.currentBookId);
            
            if (typeof loadBooks === 'function') {
                loadBooks();
            }
        } else {
            showToast(data.message || 'Gagal mengubah status', 'error');
            loadBookCopies(window.currentBookId); // Reload to reset dropdown
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
    });
};

window.deleteCopy = function(copyId) {
    if (!confirm('Apakah Anda yakin ingin menghapus eksemplar ini?')) {
        return;
    }
    
    console.log('Deleting copy:', copyId);
    
    fetch('../api/book_copies/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ copy_id: copyId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Delete copy result:', data);
        
        if (data.success) {
            showToast(data.message || 'Eksemplar berhasil dihapus', 'success');
            loadBookCopies(window.currentBookId);
            
            if (typeof loadBooks === 'function') {
                loadBooks();
            }
        } else {
            showToast(data.message || 'Gagal menghapus eksemplar', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting copy:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
    });
};

// Override closeModal untuk handle kedua modal
const originalCloseModal = window.closeModal;
window.closeModal = function() {
    document.getElementById('bookModal').classList.remove('active');
    document.getElementById('copiesModal').classList.remove('active');
    document.getElementById('copyFormModal').classList.remove('active');
};

console.log('✅ Book copies management loaded');