// ========================================
// NOTIFICATION SYSTEM
// ========================================
function showNotification(type, title, message) {
    // Remove existing notifications
    document.querySelectorAll('.notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icon = type === 'success' ? '✓' : '✕';
    const iconColor = type === 'success' ? '#28a745' : '#dc3545';
    
    notification.innerHTML = `
        <div class="notification-icon" style="color: ${iconColor}">${icon}</div>
        <div class="notification-content">
            <div class="notification-title">${title}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('hiding');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// ========================================
// TAB SWITCHING
// ========================================
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

// ========================================
// INITIALIZE
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tglPinjam').value = today;
    document.getElementById('tglPengembalian').value = today;

    // Auto calculate tanggal kembali (7 hari dari pinjam)
    document.getElementById('tglPinjam').addEventListener('change', function() {
        const pinjamDate = new Date(this.value);
        pinjamDate.setDate(pinjamDate.getDate() + 7);
        document.getElementById('tglKembali').value = pinjamDate.toISOString().split('T')[0];
    });
    
    document.getElementById('tglPinjam').dispatchEvent(new Event('change'));

    // ========================================
    // CARI ANGGOTA
    // ========================================
    document.getElementById('idAnggota').addEventListener('blur', function() {
        if (!this.value) return;
        
        const formData = new FormData();
        formData.append('action', 'get_member');
        formData.append('member_code', this.value);

        fetch('sirkulasi_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('namaAnggota').value = data.data.full_name;
                showNotification('success', 'Anggota Ditemukan', `${data.data.full_name} - ${data.data.member_code}`);
            } else {
                document.getElementById('namaAnggota').value = '';
                showNotification('error', 'Gagal', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Terjadi kesalahan saat mencari anggota');
        });
    });

    // ========================================
    // CARI BUKU
    // ========================================
    document.getElementById('kodeBuku').addEventListener('blur', function() {
        if (!this.value) return;
        
        const formData = new FormData();
        formData.append('action', 'get_book');
        formData.append('book_code', this.value);

        fetch('sirkulasi_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let bookInfo = data.data.title + ' - ' + data.data.author;
                let stockInfo = `Stok: ${data.data.available_copies}`;
                
                // Show copy information if available
                if (data.data.has_copies && data.data.available_copy) {
                    stockInfo += ` | Eksemplar: ${data.data.available_copy.copy_number}`;
                    if (data.data.available_copy.barcode) {
                        stockInfo += ` (${data.data.available_copy.barcode})`;
                    }
                }
                
                document.getElementById('judulBuku').value = bookInfo;
                showNotification('success', 'Buku Ditemukan', `${data.data.title} (${stockInfo})`);
            } else {
                document.getElementById('judulBuku').value = '';
                showNotification('error', 'Gagal', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Terjadi kesalahan saat mencari buku');
        });
    });

    // ========================================
    // FORM PEMINJAMAN
    // ========================================
    document.getElementById('formPeminjaman').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'create_borrow');
        
        fetch('sirkulasi_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = `ID Transaksi: ${data.transaction_code}`;
                if (data.copy_number) {
                    message += `<br>Eksemplar: ${data.copy_number}`;
                    if (data.barcode) {
                        message += ` (${data.barcode})`;
                    }
                }
                showNotification('success', 'Peminjaman Berhasil!', message);
                this.reset();
                document.getElementById('tglPinjam').value = today;
                document.getElementById('tglPinjam').dispatchEvent(new Event('change'));
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('error', 'Peminjaman Gagal', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Terjadi kesalahan saat memproses peminjaman');
        });
    });

    // ========================================
    // CARI TRANSAKSI
    // ========================================
    document.getElementById('idTransaksi').addEventListener('blur', function() {
        if (!this.value) return;
        
        const formData = new FormData();
        formData.append('action', 'get_transaction');
        formData.append('transaction_code', this.value);

        fetch('sirkulasi_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const trans = data.data;
                let infoText = '';
                
                // Add copy information if available
                if (trans.copy_number) {
                    infoText = `Eksemplar: ${trans.copy_number}`;
                    if (trans.barcode) {
                        infoText += ` (${trans.barcode})`;
                    }
                    infoText += '<br>';
                }
                
                if (trans.days_overdue > 0) {
                    infoText += `Terlambat ${trans.days_overdue} hari • Denda: Rp ${trans.fine_amount.toLocaleString('id-ID')}`;
                    document.getElementById('dendaInfo').innerHTML = infoText;
                    showNotification('error', 'Terlambat!', `Denda keterlambatan: Rp ${trans.fine_amount.toLocaleString('id-ID')}`);
                } else {
                    infoText += 'Tidak ada denda keterlambatan';
                    document.getElementById('dendaInfo').innerHTML = infoText;
                    showNotification('success', 'Transaksi Ditemukan', 'Tidak ada denda keterlambatan');
                }
                document.getElementById('infoKeterlambatan').style.display = 'block';
            } else {
                document.getElementById('infoKeterlambatan').style.display = 'none';
                showNotification('error', 'Gagal', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Terjadi kesalahan saat mencari transaksi');
        });
    });

    // ========================================
    // FORM PENGEMBALIAN
    // ========================================
    document.getElementById('formPengembalian').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'process_return');
        
        fetch('sirkulasi_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = 'Pengembalian berhasil diproses';
                
                if (data.fine_amount > 0) {
                    message += `<br>Total Denda: Rp ${data.fine_amount.toLocaleString('id-ID')}`;
                    
                    if (data.late_fine > 0) {
                        message += `<br>• Keterlambatan: Rp ${data.late_fine.toLocaleString('id-ID')}`;
                    }
                    if (data.damage_fine > 0) {
                        message += `<br>• Kerusakan: Rp ${data.damage_fine.toLocaleString('id-ID')}`;
                    }
                }
                
                showNotification('success', 'Pengembalian Berhasil!', message);
                this.reset();
                document.getElementById('tglPengembalian').value = today;
                document.getElementById('infoKeterlambatan').style.display = 'none';
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('error', 'Pengembalian Gagal', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error', 'Terjadi kesalahan saat memproses pengembalian');
        });
    });
});