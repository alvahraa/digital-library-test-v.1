// ==========================================
// KEANGGOTAAN PAGE - WITH ROLE SYSTEM
// ==========================================

(function() {
    'use strict';
    
    console.log('👥 Keanggotaan.js loaded with Role System');

    let members = [];
    let filteredMembers = [];
    let editingId = null;

    // ==========================================
    // LOAD MEMBERS FROM API
    // ==========================================

    function loadMembers() {
        showLoading();
        
        fetch('../api/get_all_members.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    members = data.data;
                    filteredMembers = members;
                    renderMembers();
                    updateStatsFromAPI(data.stats);
                    console.log('✅ Members loaded:', members.length);
                } else {
                    showNotification(data.message, 'error');
                    renderEmpty('Gagal memuat data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error loading members:', error);
                showNotification('Terjadi kesalahan saat memuat data', 'error');
                renderEmpty('Terjadi kesalahan saat memuat data');
            });
    }

    // ==========================================
    // UPDATE STATISTICS
    // ==========================================

    function updateStatsFromAPI(stats) {
        document.getElementById('totalMembers').textContent = stats.total || 0;
        document.getElementById('activeMembers').textContent = stats.active || 0;
        document.getElementById('newThisMonth').textContent = stats.newThisMonth || 0;
        document.getElementById('expiringThisMonth').textContent = stats.expiringThisMonth || 0;
    }

    // ==========================================
    // RENDER MEMBERS
    // ==========================================

    function renderMembers() {
        const tbody = document.getElementById('membersTableBody');
        
        if (filteredMembers.length === 0) {
            renderEmpty('Tidak ada data anggota ditemukan');
            return;
        }

        tbody.innerHTML = filteredMembers.map(member => `
            <tr>
                <td><strong>${escapeHtml(member.memberNumber)}</strong></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="member-photo" style="${member.photo ? `background-image: url('../uploads/members/${member.photo}'); background-size: cover; background-position: center;` : ''}">
                            ${member.photo ? '' : member.firstName.charAt(0).toUpperCase()}
                        </div>
                        ${escapeHtml(member.firstName)} ${escapeHtml(member.lastName)}
                    </div>
                </td>
                <td>${escapeHtml(member.email)}</td>
                <td>${escapeHtml(member.phone)}</td>
                <td>${getMemberTypeLabel(member.memberType)}</td>
                <td><span class="badge badge-role-${member.memberRole}">${getMemberRoleLabel(member.memberRole)}</span></td>
                <td>${formatDate(member.joinDate)}</td>
                <td>${formatDate(member.expireDate)}</td>
                <td>
                    <select class="status-dropdown status-${member.status}" 
                            onchange="toggleStatus(${member.id}, this.value)"
                            data-original="${member.status}">
                        <option value="active" ${member.status === 'active' ? 'selected' : ''}>Aktif</option>
                        <option value="inactive" ${member.status === 'inactive' ? 'selected' : ''}>Tidak Aktif</option>
                        <option value="suspended" ${member.status === 'suspended' ? 'selected' : ''}>Ditangguhkan</option>
                    </select>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-secondary" onclick="viewMember(${member.id})" title="Lihat Detail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            Detail
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editMember(${member.id})" title="Edit Data">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Edit
                        </button>
                        <button class="btn btn-sm btn-success" onclick="renewMembership(${member.id})" title="Perpanjang">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                            </svg>
                            Perpanjang
                        </button>
                        <button class="btn btn-sm btn-success" onclick="printCard(${member.id})" title="Cetak Kartu">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            Cetak Kartu
                        </button>
                        <button class="btn btn-sm btn-info" onclick="resetPassword(${member.id})" title="Reset Password">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            Reset Password
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteMember(${member.id})" title="Hapus">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderEmpty(message) {
        const tbody = document.getElementById('membersTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="10">
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <div class="empty-state-text">${message}</div>
                    </div>
                </td>
            </tr>
        `;
    }

    function showLoading() {
        const tbody = document.getElementById('membersTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 40px;">
                    <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f4f6; border-top-color: #F875AA; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="color: #F875AA; margin-top: 16px;">Memuat data...</p>
                </td>
            </tr>
        `;
    }

    // ==========================================
    // TOGGLE STATUS
    // ==========================================

    window.toggleStatus = function(memberId, newStatus) {
        const select = event.target;
        const originalStatus = select.getAttribute('data-original');
        
        if (!confirm(`Apakah Anda yakin ingin mengubah status menjadi "${getStatusLabel(newStatus)}"?`)) {
            select.value = originalStatus;
            return;
        }

        select.disabled = true;

        fetch('../api/toggle_member_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                member_id: memberId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                select.setAttribute('data-original', newStatus);
                select.className = `status-dropdown status-${newStatus}`;
                loadMembers();
            } else {
                showNotification(data.message, 'error');
                select.value = originalStatus;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat mengubah status', 'error');
            select.value = originalStatus;
        })
        .finally(() => {
            select.disabled = false;
        });
    };

    // ==========================================
    // SEARCH & FILTER
    // ==========================================

    window.searchMembers = function() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        const statusFilter = document.getElementById('statusFilter').value;
        const roleFilter = document.getElementById('roleFilter').value;
        
        filteredMembers = members.filter(member => {
            const matchSearch = !searchTerm || 
                member.firstName.toLowerCase().includes(searchTerm) ||
                member.lastName.toLowerCase().includes(searchTerm) ||
                member.email.toLowerCase().includes(searchTerm) ||
                member.memberNumber.toLowerCase().includes(searchTerm) ||
                member.phone.includes(searchTerm);
            
            const matchStatus = !statusFilter || member.status === statusFilter;
            const matchRole = !roleFilter || member.memberRole === roleFilter;
            
            return matchSearch && matchStatus && matchRole;
        });
        
        renderMembers();
        console.log('Search:', searchTerm, 'Results:', filteredMembers.length);
    };

    window.filterMembers = function() {
        searchMembers();
    };

    // ==========================================
    // MODAL FUNCTIONS
    // ==========================================

    window.openAddModal = function() {
        editingId = null;
        document.getElementById('modalTitle').textContent = 'Tambah Anggota Baru';
        document.getElementById('memberForm').reset();
        document.getElementById('memberId').value = '';
        document.getElementById('memberNumber').value = 'Otomatis';
        document.getElementById('photoPreview').innerHTML = '';
        
        const durationGroup = document.getElementById('durationGroup');
        if (durationGroup) durationGroup.style.display = 'block';
        
        // Show password field untuk member baru
        const passwordGroup = document.getElementById('passwordGroup');
        if (passwordGroup) passwordGroup.style.display = 'block';
        document.getElementById('password').required = true;
        document.getElementById('username').required = true;
        
        document.getElementById('memberModal').classList.add('active');
    };

    window.closeModal = function() {
        document.getElementById('memberModal').classList.remove('active');
        editingId = null;
    };

    window.closeViewModal = function() {
        document.getElementById('viewModal').classList.remove('active');
    };

    // ==========================================
    // SAVE MEMBER WITH PHOTO
    // ==========================================

    window.saveMember = function(event) {
        event.preventDefault();

        const formData = new FormData();
        
        formData.append('id', editingId || '');
        formData.append('firstName', document.getElementById('firstName').value.trim());
        formData.append('lastName', document.getElementById('lastName').value.trim());
        formData.append('email', document.getElementById('email').value.trim());
        formData.append('phone', document.getElementById('phone').value.trim());
        formData.append('address', document.getElementById('address').value.trim());
        formData.append('birthDate', document.getElementById('birthDate').value);
        formData.append('gender', document.getElementById('gender').value);
        formData.append('memberType', document.getElementById('memberType').value);
        formData.append('memberRole', document.getElementById('memberRole').value);
        formData.append('institution', document.getElementById('institution').value.trim());
        
        // Username & Password
        formData.append('username', document.getElementById('username').value.trim());
        const password = document.getElementById('password').value.trim();
        if (password) {
            formData.append('password', password);
        }
        
        if (!editingId) {
            formData.append('duration', document.getElementById('duration').value);
        }
        
        const photoInput = document.getElementById('photoUpload');
        if (photoInput && photoInput.files.length > 0) {
            formData.append('photo', photoInput.files[0]);
        }

        const firstName = formData.get('firstName');
        const lastName = formData.get('lastName');
        const email = formData.get('email');
        const phone = formData.get('phone');
        const username = formData.get('username');
        
        if (!firstName || !lastName || !email || !phone || !username) {
            showNotification('Mohon lengkapi semua field yang wajib diisi', 'error');
            return;
        }

        // Validasi username
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            showNotification('Username hanya boleh huruf, angka, dan underscore', 'error');
            return;
        }

        // Validasi password untuk member baru
        if (!editingId && !password) {
            showNotification('Password wajib diisi untuk anggota baru', 'error');
            return;
        }

        if (password && password.length < 6) {
            showNotification('Password minimal 6 karakter', 'error');
            return;
        }

        const submitBtn = event.target.querySelector('[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';

        fetch('../api/save_member.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (editingId) {
                    showNotification('Data anggota berhasil diupdate', 'success');
                } else {
                    showNotification(`Anggota baru berhasil ditambahkan! Username: ${data.username}`, 'success');
                }
                closeModal();
                loadMembers();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menyimpan data', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan';
        });
    };

    // ==========================================
    // EDIT MEMBER
    // ==========================================

    window.editMember = function(id) {
        editingId = id;
        const member = members.find(m => m.id === id);
        
        if (!member) {
            showNotification('Data anggota tidak ditemukan', 'error');
            return;
        }

        document.getElementById('modalTitle').textContent = 'Edit Data Anggota';
        document.getElementById('memberId').value = member.id;
        document.getElementById('memberNumber').value = member.memberNumber;
        document.getElementById('firstName').value = member.firstName;
        document.getElementById('lastName').value = member.lastName;
        document.getElementById('email').value = member.email;
        document.getElementById('phone').value = member.phone;
        document.getElementById('address').value = member.address;
        document.getElementById('birthDate').value = member.birthDate;
        document.getElementById('gender').value = member.gender;
        document.getElementById('memberType').value = member.memberType;
        document.getElementById('memberRole').value = member.memberRole || 'library_member';
        document.getElementById('institution').value = member.institution || '';
        document.getElementById('username').value = member.username || '';

        const durationGroup = document.getElementById('durationGroup');
        if (durationGroup) durationGroup.style.display = 'none';

        // Password optional saat edit
        const passwordGroup = document.getElementById('passwordGroup');
        if (passwordGroup) {
            passwordGroup.style.display = 'block';
            document.getElementById('password').required = false;
            document.getElementById('password').value = '';
            document.getElementById('password').placeholder = 'Kosongkan jika tidak ingin mengubah password';
        }

        const photoPreview = document.getElementById('photoPreview');
        if (member.photo) {
            photoPreview.innerHTML = `
                <div style="padding: 10px; background: #f9f9f9; border-radius: 8px; border: 2px dashed #e0e0e0;">
                    <img src="../uploads/members/${member.photo}" style="max-width: 150px; max-height: 150px; border-radius: 8px; display: block;">
                    <p style="font-size: 12px; color: #666; margin-top: 8px;">Foto saat ini</p>
                </div>
            `;
        } else {
            photoPreview.innerHTML = '';
        }

        document.getElementById('memberModal').classList.add('active');
    };

    // ==========================================
    // VIEW MEMBER DETAIL
    // ==========================================

    window.viewMember = function(id) {
        const member = members.find(m => m.id === id);
        if (!member) {
            showNotification('Data anggota tidak ditemukan', 'error');
            return;
        }

        const photoUrl = member.photo ? `../uploads/members/${member.photo}` : '';
        const permissions = member.permissions ? JSON.parse(member.permissions) : {};

        const detailsHtml = `
            <div style="display: grid; gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #FFF5F9; border-radius: 12px;">
                    <div class="member-photo" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 15px; ${member.photo ? `background-image: url('${photoUrl}'); background-size: cover; background-position: center;` : ''}">
                        ${member.photo ? '' : member.firstName.charAt(0).toUpperCase()}
                    </div>
                    <h3 style="margin-bottom: 5px;">${escapeHtml(member.firstName)} ${escapeHtml(member.lastName)}</h3>
                    <p style="color: #666; font-size: 14px;">${member.memberNumber}</p>
                    ${member.username ? `<p style="color: #999; font-size: 13px;">@${member.username}</p>` : ''}
                    <span class="badge badge-${member.status}" style="margin-top: 10px;">${getStatusLabel(member.status)}</span>
                    <span class="badge badge-role-${member.memberRole}" style="margin-top: 10px; margin-left: 8px;">${getMemberRoleLabel(member.memberRole)}</span>
                </div>

                <div style="display: grid; gap: 15px;">
                    <div>
                        <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Email</label>
                        <p style="margin-top: 5px;">${escapeHtml(member.email)}</p>
                    </div>
                    <div>
                        <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Telepon</label>
                        <p style="margin-top: 5px;">${escapeHtml(member.phone)}</p>
                    </div>
                    <div>
                        <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Alamat</label>
                        <p style="margin-top: 5px;">${escapeHtml(member.address)}</p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Tanggal Lahir</label>
                            <p style="margin-top: 5px;">${member.birthDate ? formatDate(member.birthDate) : '-'}</p>
                        </div>
                        <div>
                            <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Jenis Kelamin</label>
                            <p style="margin-top: 5px;">${member.gender === 'L' ? 'Laki-laki' : member.gender === 'P' ? 'Perempuan' : '-'}</p>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Tipe Keanggotaan</label>
                            <p style="margin-top: 5px;">${getMemberTypeLabel(member.memberType)}</p>
                        </div>
                        <div>
                            <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Role Anggota</label>
                            <p style="margin-top: 5px;">${getMemberRoleLabel(member.memberRole)}</p>
                        </div>
                    </div>
                    <div>
                        <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Institusi</label>
                        <p style="margin-top: 5px;">${member.institution || '-'}</p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Tanggal Bergabung</label>
                            <p style="margin-top: 5px;">${formatDate(member.joinDate)}</p>
                        </div>
                        <div>
                            <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase;">Masa Berlaku Sampai</label>
                            <p style="margin-top: 5px;">${formatDate(member.expireDate)}</p>
                        </div>
                    </div>
                    
                    ${member.permissions ? `
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 10px;">
                        <label style="font-weight: 500; color: #F875AA; font-size: 12px; text-transform: uppercase; display: block; margin-bottom: 10px;">Hak Akses (Permissions)</label>
                        <div style="display: grid; gap: 8px; font-size: 13px;">
                            ${permissions.can_borrow_books ? '<div>✅ Dapat Meminjam Buku</div>' : '<div>❌ Tidak Dapat Meminjam Buku</div>'}
                            ${permissions.can_add_bibliography ? '<div>✅ Dapat Menambah Bibliografi</div>' : '<div>❌ Tidak Dapat Menambah Bibliografi</div>'}
                            ${permissions.can_view_catalog ? '<div>✅ Dapat Melihat Katalog</div>' : '<div>❌ Tidak Dapat Melihat Katalog</div>'}
                            ${permissions.can_request_books ? '<div>✅ Dapat Request Buku</div>' : '<div>❌ Tidak Dapat Request Buku</div>'}
                            ${permissions.can_view_reports ? '<div>✅ Dapat Melihat Laporan</div>' : '<div>❌ Tidak Dapat Melihat Laporan</div>'}
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        document.getElementById('memberDetails').innerHTML = detailsHtml;
        document.getElementById('viewModal').classList.add('active');
    };

    // ==========================================
    // PRINT MEMBER CARD
    // ==========================================

    window.printCard = function(id) {
        const member = members.find(m => m.id === id);
        if (!member) {
            showNotification('Data anggota tidak ditemukan', 'error');
            return;
        }
        
        // Open print page in new window
        window.open(`print_member_card.php?id=${id}`, '_blank', 'width=900,height=700');
    };

    // ==========================================
    // RENEW MEMBERSHIP
    // ==========================================

    window.renewMembership = function(id) {
        const member = members.find(m => m.id === id);
        if (!member) {
            showNotification('Data anggota tidak ditemukan', 'error');
            return;
        }

        const duration = prompt('Perpanjang untuk berapa bulan? (3, 6, 12, atau 24)', '12');
        if (!duration) return;

        const months = parseInt(duration);
        if (isNaN(months) || ![3, 6, 12, 24].includes(months)) {
            showNotification('Durasi tidak valid! Pilih 3, 6, 12, atau 24 bulan.', 'error');
            return;
        }

        fetch('../api/renew_membership.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                member_id: id,
                months: months 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadMembers();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat perpanjang keanggotaan', 'error');
        });
    };

    // ==========================================
    // RESET PASSWORD
    // ==========================================

    window.resetPassword = function(id) {
        const member = members.find(m => m.id === id);
        if (!member) {
            showNotification('Data anggota tidak ditemukan', 'error');
            return;
        }

        // Tampilkan modal reset password
        document.getElementById('resetPasswordMemberId').value = id;
        document.getElementById('resetPasswordMemberName').textContent = `${member.firstName} ${member.lastName}`;
        document.getElementById('resetPasswordUsername').textContent = member.username || '-';
        document.getElementById('newPasswordInput').value = '';
        document.getElementById('newPasswordResult').style.display = 'none';
        document.getElementById('resetPasswordModal').classList.add('active');
    };

    window.closeResetPasswordModal = function() {
        document.getElementById('resetPasswordModal').classList.remove('active');
    };

    window.generateRandomPassword = function() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        let password = '';
        for (let i = 0; i < 8; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('newPasswordInput').value = password;
    };

    window.confirmResetPassword = function() {
        const memberId = parseInt(document.getElementById('resetPasswordMemberId').value);
        const newPassword = document.getElementById('newPasswordInput').value.trim();

        if (!newPassword) {
            showNotification('Mohon isi password baru atau klik "Generate Password"', 'error');
            return;
        }

        if (newPassword.length < 6) {
            showNotification('Password minimal 6 karakter', 'error');
            return;
        }

        const confirmBtn = event.target;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Mereset...';

        fetch('../api/reset_member_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                member_id: memberId,
                new_password: newPassword
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Tampilkan password baru
                document.getElementById('newPasswordDisplay').textContent = newPassword;
                document.getElementById('newPasswordResult').style.display = 'block';
                showNotification(data.message, 'success');
                
                // Auto close setelah 10 detik
                setTimeout(() => {
                    closeResetPasswordModal();
                }, 10000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat reset password', 'error');
        })
        .finally(() => {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Reset Password';
        });
    };

    window.copyPassword = function() {
        const password = document.getElementById('newPasswordDisplay').textContent;
        navigator.clipboard.writeText(password).then(() => {
            showNotification('Password berhasil dicopy!', 'success');
        });
    };

    // ==========================================
    // DELETE MEMBER
    // ==========================================

    window.deleteMember = function(id) {
        const member = members.find(m => m.id === id);
        if (!member) {
            showNotification('Data anggota tidak ditemukan', 'error');
            return;
        }

        if (!confirm(`Apakah Anda yakin ingin menghapus anggota ${member.firstName} ${member.lastName}?\n\nAkun login dan semua data terkait juga akan dihapus!`)) {
            return;
        }

        fetch('../api/delete_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ member_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Anggota berhasil dihapus', 'success');
                loadMembers();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menghapus data', 'error');
        });
    };

    // ==========================================
    // HELPER FUNCTIONS
    // ==========================================

    function getMemberTypeLabel(type) {
        const labels = {
            'student': 'Pelajar/Mahasiswa',
            'teacher': 'Guru/Dosen',
            'public': 'Umum'
        };
        return labels[type] || type;
    }

    function getMemberRoleLabel(role) {
        const labels = {
            'library_member': 'Anggota Perpustakaan',
            'intern': 'Anak Magang',
            'staff': 'Staff Perpustakaan'
        };
        return labels[role] || role;
    }

    function getStatusLabel(status) {
        const labels = {
            'active': 'Aktif',
            'inactive': 'Tidak Aktif',
            'suspended': 'Ditangguhkan'
        };
        return labels[status] || status;
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const notificationText = document.getElementById('notificationText');
        
        notification.className = `notification notification-${type} show`;
        notificationText.textContent = message;

        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }

    // ==========================================
    // EVENT LISTENERS
    // ==========================================

    const photoInput = document.getElementById('photoUpload');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('photoPreview');
            
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    showNotification('Ukuran foto terlalu besar. Maksimal 2MB', 'error');
                    this.value = '';
                    return;
                }
                
                if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                    showNotification('Format foto tidak didukung. Gunakan JPG, PNG, atau GIF', 'error');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div style="padding: 10px; background: #f9f9f9; border-radius: 8px; border: 2px dashed #e0e0e0;">
                            <img src="${e.target.result}" style="max-width: 150px; max-height: 150px; border-radius: 8px; display: block;">
                            <p style="font-size: 12px; color: #666; margin-top: 8px;">Preview foto baru</p>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    document.getElementById('searchInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            searchMembers();
        }
    });

    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (e.target.value.length >= 3 || e.target.value.length === 0) {
                searchMembers();
            }
        }, 500);
    });

    window.addEventListener('click', (e) => {
        const memberModal = document.getElementById('memberModal');
        const viewModal = document.getElementById('viewModal');
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        
        if (e.target === memberModal) {
            closeModal();
        }
        if (e.target === viewModal) {
            closeViewModal();
        }
        if (e.target === resetPasswordModal) {
            closeResetPasswordModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            closeViewModal();
            closeResetPasswordModal();
        }
    });

    // ==========================================
    // INITIALIZE
    // ==========================================

    window.addEventListener('DOMContentLoaded', () => {
        loadMembers();
        console.log('✅ Keanggotaan initialized with Role System');
    });

})();