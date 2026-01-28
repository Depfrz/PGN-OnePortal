<x-dashboard-layout title="Management User">
    <div x-data="{
        search: '',
        addUserModal: false,
        resetPasswordModal: false,
        deleteUserModal: false,
        editRoleModal: false,
        editAccessModal: false,
        showSuccessModal: false,
        successMessage: '',
        toastType: 'success',
        selectedUser: null,
        selectedAccess: [],
        dashboardAccess: [],
        listPengawasanPermissions: {
            tambah_proyek: true,
            nama_proyek: true,
            tambah_kegiatan: true,
            hapus_kegiatan: true,
            tambah_keterangan: true,
            edit_keterangan: true,
            tambah_pengawasan: true,
            edit_pengawasan: true,
            deadline: true,
            status: true,
            keterangan_checklist: true,
            bukti: true,
        },
        
        // Form Data
        newUser: {
            name: '',
            email: '',
            instansi: '',
            jabatan: '',
            password: '',
            password_confirmation: '',
            role: 'User',
            hak_akses: []
        },
        newPassword: '',
        newPasswordConfirmation: '',

        // Data from Backend
        users: {{ Js::from($users) }},
        availableRoles: {{ Js::from($availableRoles) }},
        availableAccess: {{ Js::from($availableAccess) }},
        
        showSuccess(message) {
            this.successMessage = message;
            this.toastType = 'success';
            this.showSuccessModal = true;
            setTimeout(() => {
                this.showSuccessModal = false;
            }, 5000);
        },

        showError(message) {
            this.successMessage = message;
            this.toastType = 'error';
            this.showSuccessModal = true;
            setTimeout(() => {
                this.showSuccessModal = false;
            }, 5000);
        },
        
        openResetPassword(user) {
            this.selectedUser = user;
            this.newPassword = '';
            this.newPasswordConfirmation = '';
            this.resetPasswordModal = true;
        },
        
        openDeleteUser(user) {
            this.selectedUser = user;
            this.deleteUserModal = true;
        },

        openEditRole(user) {
            this.selectedUser = JSON.parse(JSON.stringify(user)); // Clone object
            this.editRoleModal = true;
        },

        openEditAccess(user) {
            this.selectedUser = JSON.parse(JSON.stringify(user)); // Clone object
            this.selectedAccess = [...this.selectedUser.hak_akses];
            this.dashboardAccess = [...(this.selectedUser.dashboard_access || [])];
            this.listPengawasanPermissions = {
                tambah_proyek: true,
                nama_proyek: true,
                tambah_kegiatan: true,
                hapus_kegiatan: true,
                tambah_keterangan: true,
                edit_keterangan: true,
                tambah_pengawasan: true,
                edit_pengawasan: true,
                deadline: true,
                status: true,
                keterangan_checklist: true,
                bukti: true,
                ...(this.selectedUser.list_pengawasan_permissions || {}),
            };
            
            // Auto-enable dashboard access for specific modules if they are selected
            // This ensures backward compatibility for users who previously had access but dashboard unchecked
            const autoDashboardModules = ['Buku Saku', 'List Pengawasan'];
            autoDashboardModules.forEach(moduleName => {
                if (this.selectedAccess.includes(moduleName) && !this.dashboardAccess.includes(moduleName)) {
                    this.dashboardAccess.push(moduleName);
                }
            });
            
            this.editAccessModal = true;
        },

        async saveUser() {
            try {
                const response = await fetch('{{ route('management-user.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(this.newUser)
                });
                
                if (response.ok) {
                    this.showSuccess('User berhasil ditambahkan');
                    window.location.reload();
                } else {
                    const data = await response.json();
                    this.showSuccess('Gagal menambahkan user: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
                this.showSuccess('Terjadi kesalahan sistem');
            }
        },

        async saveRole() {
            try {
                const response = await fetch(`/management-user/${this.selectedUser.id}/role`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ role: this.selectedUser.role })
                });

                if (response.ok) {
                    // Update local data
                    const index = this.users.findIndex(u => u.id === this.selectedUser.id);
                    if (index !== -1) {
                        this.users[index].role = this.selectedUser.role;
                    }
                    this.editRoleModal = false;
                    this.showSuccess('Role berhasil diperbarui');
                } else {
                    this.showSuccess('Gagal memperbarui role');
                }
            } catch (error) {
                console.error(error);
                this.showSuccess('Terjadi kesalahan sistem');
            }
        },

        async saveAccess() {
            try {
                const response = await fetch(`/management-user/${this.selectedUser.id}/access`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ 
                        hak_akses: this.selectedAccess,
                        dashboard_access: this.dashboardAccess,
                        list_pengawasan_permissions: this.listPengawasanPermissions,
                    })
                });

                if (response.ok) {
                    // Update local data
                    const index = this.users.findIndex(u => u.id === this.selectedUser.id);
                    if (index !== -1) {
                        this.users[index].hak_akses = [...this.selectedAccess];
                        this.users[index].dashboard_access = [...this.dashboardAccess];
                        this.users[index].list_pengawasan_permissions = { ...this.listPengawasanPermissions };
                    }
                    this.editAccessModal = false;
                    this.showSuccess('Hak akses berhasil diperbarui');
                } else {
                    this.showSuccess('Gagal memperbarui hak akses');
                }
            } catch (error) {
                console.error(error);
                this.showSuccess('Terjadi kesalahan sistem');
            }
        },

        async updatePassword() {
            if (this.newPassword !== this.newPasswordConfirmation) {
                this.showError('Password konfirmasi tidak cocok');
                return;
            }

            try {
                const response = await fetch(`/management-user/${this.selectedUser.id}/password`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ 
                        password: this.newPassword,
                        password_confirmation: this.newPasswordConfirmation
                    })
                });

                if (response.ok) {
                    this.resetPasswordModal = false;
                    this.showSuccess('Password berhasil direset');
                } else {
                    const data = await response.json();
                    this.showError('Gagal reset password: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
                this.showError('Terjadi kesalahan sistem');
            }
        },

        async deleteUser() {
            try {
                const response = await fetch(`/management-user/${this.selectedUser.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                if (response.ok) {
                    this.users = this.users.filter(u => u.id !== this.selectedUser.id);
                    this.deleteUserModal = false;
                    this.showSuccess('User berhasil dihapus');
                } else {
                    const data = await response.json();
                    this.showSuccess('Gagal menghapus user: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
                this.showSuccess('Terjadi kesalahan sistem');
            }
        }
    }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 lg:p-8 min-h-[800px] flex flex-col transition-colors duration-300">
        
        <!-- Search Section -->
        <div class="mb-8 max-w-md">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Pencarian User</h2>
            <div class="relative w-full group">
                <input x-model="search" type="text" 
                       placeholder="Cari Nama User..." 
                       class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg pl-4 pr-10 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 group-focus-within:text-blue-500 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Management User Section -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manajemen User</h2>
                <button @click="addUserModal = true" style="background-color: #2563eb !important; color: white !important;" class="bg-blue-600 text-white font-medium text-sm py-2.5 px-6 rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah User Baru
                </button>
            </div>

            <!-- Table -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-6 overflow-x-auto border border-gray-100 dark:border-gray-700">
                <table class="w-full min-w-[1000px] border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-left">
                            <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider pl-4">Nama / Email</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[150px] pr-4">Instansi</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[150px] pr-4">Jabatan</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px] pr-4">Role</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Status</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Hak Akses</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="space-y-4">
                        <template x-for="user in users.filter(u => u.name.toLowerCase().includes(search.toLowerCase()) || u.email.toLowerCase().includes(search.toLowerCase()))" :key="user.id">
                            <tr class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 group">
                                <td class="p-4 rounded-l-lg border-y border-l border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div class="font-bold text-sm text-gray-900 dark:text-white" x-text="user.name"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="user.email"></div>
                                </td>
                                <td class="p-4 text-sm text-gray-700 dark:text-gray-300 border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors" x-text="user.instansi"></td>
                                <td class="p-4 text-sm text-gray-700 dark:text-gray-300 border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors" x-text="user.jabatan"></td>
                                <td class="p-4 border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2" x-text="user.role"></span>
                                        <button @click="openEditRole(user)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4 text-center border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <span style="background-color: #dcfce7 !important; color: #166534 !important; border: 1px solid #bbf7d0 !important;" class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200 shadow-sm" x-text="user.status"></span>
                                </td>
                                <td class="p-4 text-center border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div @click="openEditAccess(user)" class="flex items-center justify-center space-x-2 cursor-pointer bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 p-2 rounded-lg transition-colors group/access border border-blue-100 dark:border-blue-800">
                                        <span class="text-sm font-medium text-blue-700 dark:text-blue-300" x-text="user.hak_akses.length ? user.hak_akses.join(', ') : 'Pilih Akses'"></span>
                                        <div class="w-6 h-6 bg-blue-200 dark:bg-blue-800 rounded flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-700 dark:text-blue-300">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 rounded-r-lg text-center border-y border-r border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Key Icon (Reset Password) -->
                                        <button @click="openResetPassword(user)" class="p-2 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded-lg transition-colors" title="Reset Password">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                        </button>
                                        <!-- Trash Icon (Delete) -->
                                        <button @click="openDeleteUser(user)" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Hapus User">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Modal -->
        <div x-show="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-[500px] shadow-2xl transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah User Baru</h2>
                    <button @click="addUserModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                        <input x-model="newUser.name" type="text" placeholder="Masukkan nama lengkap" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                        <input x-model="newUser.email" type="email" placeholder="contoh@email.com" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instansi</label>
                        <input x-model="newUser.instansi" type="text" placeholder="Masukkan instansi" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                        <input x-model="newUser.jabatan" type="text" placeholder="Masukkan jabatan" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input x-model="newUser.password" type="password" placeholder="Minimal 8 karakter" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password</label>
                        <input x-model="newUser.password_confirmation" type="password" placeholder="Ulangi Password" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                         <select x-model="newUser.role" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                             <template x-for="role in availableRoles" :key="role">
                                 <option :value="role" x-text="role"></option>
                             </template>
                         </select>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="addUserModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">Batal</button>
                        <button @click="saveUser()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan User</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div x-show="resetPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-8 w-[600px] shadow-2xl transform transition-all">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Reset Password</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Buat password baru untuk user ini.</p>
                </div>
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Terpilih</label>
                        <input type="text" :value="selectedUser?.name + ' (' + selectedUser?.email + ')'" disabled class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3 text-sm text-gray-600 dark:text-gray-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                        <input x-model="newPassword" type="password" placeholder="Masukkan password baru" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password Baru</label>
                        <input x-model="newPasswordConfirmation" type="password" placeholder="Ulangi password baru" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div class="flex justify-end space-x-3 mt-8">
                        <button @click="resetPasswordModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">Batal</button>
                        <button @click="updatePassword()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 !bg-blue-600 !text-white rounded-lg hover:!bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Update Password</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete User Modal -->
        <div x-show="deleteUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-8 w-[450px] shadow-2xl transform transition-all">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus User</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                
                <div class="space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus user berikut?</p>
                    <div>
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-lg p-4">
                            <div class="font-semibold text-gray-900 dark:text-white" x-text="selectedUser?.name"></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedUser?.email"></div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="deleteUserModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">Batal</button>
                        <button @click="deleteUser()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Ya, Hapus User</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Role Modal -->
        <div x-show="editRoleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-8 w-[600px] shadow-2xl transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Role</h2>
                    <button @click="editRoleModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <template x-if="selectedUser">
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Pilih role untuk user <span class="font-semibold" x-text="selectedUser.name"></span>:</p>
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="role in availableRoles" :key="role">
                                <label class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors gap-4">
                                    <input type="radio" name="role" :value="role" x-model="selectedUser.role" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <span class="text-base font-medium text-gray-700 dark:text-gray-300" x-text="role"></span>
                                </label>
                            </template>
                        </div>
                        <div class="flex justify-end space-x-3 mt-8">
                            <button @click="editRoleModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">Batal</button>
                            <button @click="saveRole()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 !bg-blue-600 !text-white rounded-lg hover:!bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Edit Access Modal -->
        <div x-show="editAccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-8 w-[600px] shadow-2xl transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Hak Akses</h2>
                    <button @click="editAccessModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Pilih modul yang dapat diakses oleh <span class="font-semibold" x-text="selectedUser?.name"></span>:</p>
                    
                    <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                        @php
                            // 1. Flatten all modules from the controller's grouping
                            $allModules = $availableAccess->flatten();

                            // 2. Custom Grouping & Re-ordering
                            $groupedModules = $allModules->groupBy(function($item) {
                                // Force 'Buku Saku' module into 'Buku Saku' group
                                if ($item->name === 'Buku Saku') return 'Buku Saku';
                                return $item->group;
                            });
                            $groupedModules = $groupedModules->except(['Web Utama']);

                            // Define desired order
                            $orderedGroups = ['Buku Saku', 'List Pengawasan'];
                            
                            // Get other groups that might exist (excluding Lainnya if empty/merged)
                            $otherGroups = $groupedModules->keys()
                                ->diff($orderedGroups)
                                ->filter(fn($g) => $g !== 'Lainnya' || $groupedModules[$g]->isNotEmpty());
                                
                            $finalGroupOrder = collect($orderedGroups)->merge($otherGroups);
                            
                            $subModules = ['Dokumen Favorit', 'Riwayat Dokumen', 'Pengecekan File', 'Upload Dokumen', 'Beranda'];
                            $accessDescriptions = [
                                'Buku Saku' => [
                                    'Beranda' => 'Akses halaman beranda Buku Saku.',
                                    'Dokumen Favorit' => 'Melihat dan mengelola dokumen favorit.',
                                    'Pengecekan File' => 'Melakukan pengecekan dan approval dokumen.',
                                    'Riwayat Dokumen' => 'Melihat riwayat dokumen.',
                                    'Upload Dokumen' => 'Mengunggah dokumen baru.',
                                ],
                            ];
                        @endphp

                        @foreach($finalGroupOrder as $groupName)
                            @php
                                $modules = $groupedModules->get($groupName);
                                if (!$modules) continue;

                                // Check if there is a "Main Module" that matches the Group Name
                                // e.g. Group "Buku Saku" contains module "Buku Saku"
                                $headerModule = $modules->first(fn($m) => strcasecmp($m->name, $groupName) === 0);
                                
                                // Filter out the header module from the list of items to display below
                                $itemModules = $modules->filter(fn($m) => !$headerModule || $m->id !== $headerModule->id);
                            @endphp

                            <div class="mb-6">
                                <!-- Group Header -->
                                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-gray-700 min-h-[40px]">
                                    @if($headerModule)
                                        <!-- Header is a Module (Access + Dashboard) -->
                                        <div class="flex items-center justify-between w-full">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="checkbox" 
                                                       value="{{ $headerModule->name }}" 
                                                       x-model="selectedAccess" 
                                                       @change="
                                                           if ($el.checked) {
                                                               // Auto-enable dashboard access for Header Modules (Buku Saku, List Pengawasan)
                                                               if (!dashboardAccess.includes('{{ $headerModule->name }}')) {
                                                                   dashboardAccess.push('{{ $headerModule->name }}');
                                                               }
                                                           } else {
                                                               // Remove from dashboard access if disabled
                                                               dashboardAccess = dashboardAccess.filter(i => i !== '{{ $headerModule->name }}');
                                                           }
                                                       "
                                                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <span class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">{{ $groupName }}</span>
                                            </label>
                                        </div>
                                    @else
                                        <!-- Header is just Text (Web Utama, etc.) -->
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                            {{ $groupName }}
                                        </h3>
                                    @endif
                                </div>
                                
                                @if(in_array($groupName, ['Buku Saku']))
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300 mb-2">
                                        Hak Akses Detail
                                    </div>
                                @endif

                                <!-- Module Items -->
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($itemModules as $module)
                                        @php
                                            $desc = $accessDescriptions[$groupName][$module->name] ?? null;
                                        @endphp
                                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                            <!-- Access Checkbox (Left) -->
                                            <label class="flex items-center gap-3 cursor-pointer flex-grow">
                                                <input type="checkbox" 
                                                       value="{{ $module->name }}" 
                                                       x-model="selectedAccess" 
                                                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $module->name }}</span>
                                                    @if($desc)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $desc }}</span>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                @if($groupName === 'List Pengawasan')
                                    <div class="mt-4">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300 mb-2">
                                            Hak Akses List Pengawasan
                                        </div>
                                        <div class="grid grid-cols-1 gap-3 max-h-[400px] overflow-y-auto pr-2">
                                            <!-- Group: Proyek -->
                                            <div class="font-medium text-gray-700 dark:text-gray-300 mt-2 mb-1">Manajemen Proyek</div>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.tambah_proyek" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tambah Proyek</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Menambahkan proyek baru ke daftar.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.nama_proyek" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Edit Proyek</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Mengedit nama proyek dan menghapus proyek.</span>
                                                </div>
                                            </label>

                                            <!-- Group: Kegiatan -->
                                            <div class="font-medium text-gray-700 dark:text-gray-300 mt-2 mb-1">Manajemen Kegiatan</div>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.tambah_kegiatan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tambah Kegiatan</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Menambahkan kegiatan baru ke proyek.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.hapus_kegiatan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Hapus Kegiatan</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Menghapus kegiatan dari proyek.</span>
                                                </div>
                                            </label>

                                            <!-- Group: Keterangan -->
                                            <div class="font-medium text-gray-700 dark:text-gray-300 mt-2 mb-1">Manajemen Keterangan</div>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.tambah_keterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tambah Keterangan</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Menambahkan keterangan/catatan baru.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.edit_keterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Edit Keterangan</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Mengedit teks dan menghapus keterangan.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.keterangan_checklist" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Keterangan (Checklist & Upload)</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Mencentang checklist dan upload foto keterangan.</span>
                                                </div>
                                            </label>

                                            <!-- Group: Pengawas -->
                                            <div class="font-medium text-gray-700 dark:text-gray-300 mt-2 mb-1">Manajemen Pengawas</div>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.tambah_pengawasan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tambah Pengawasan</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Menambahkan pengawas baru ke kegiatan.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.edit_pengawasan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Edit Pengawasan</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Mengganti dan menghapus pengawas.</span>
                                                </div>
                                            </label>

                                            <!-- Group: Lainnya -->
                                            <div class="font-medium text-gray-700 dark:text-gray-300 mt-2 mb-1">Lainnya</div>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.deadline" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Deadline</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Mengedit tanggal dan waktu deadline.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.status" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Status</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Mengganti status kemajuan kegiatan.</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-4 cursor-pointer p-3 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                                                <input type="checkbox" x-model="listPengawasanPermissions.bukti" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Bukti</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Upload bukti fisik/digital & keterangan bukti.</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end space-x-3 mt-8">
                        <button @click="editAccessModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">Batal</button>
                        <button @click="saveAccess()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 !bg-blue-600 !text-white rounded-lg hover:!bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Notification Modal -->
        <div x-show="showSuccessModal" 
             style="display: none;"
             class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             role="alertdialog"
             aria-modal="true"
             aria-labelledby="success-title"
             @keydown.escape.window="showSuccessModal = false">
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 transform transition-all flex flex-col items-center text-center"
                 @click.away="showSuccessModal = false">
                
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4"
                     :class="toastType === 'error' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-green-100 dark:bg-green-900/30'">
                    <svg x-show="toastType !== 'error'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="toastType === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <h3 id="success-title" class="text-xl font-bold text-gray-900 dark:text-white mb-2" x-text="toastType === 'error' ? 'Gagal!' : 'Berhasil!'"></h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6" x-text="successMessage"></p>

                <!-- Close Button -->
                <button @click="showSuccessModal = false" 
                        class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</x-dashboard-layout>
