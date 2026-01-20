<x-dashboard-layout>
    <div x-data="{
        search: '',
        addUserModal: false,
        resetPasswordModal: false,
        deleteUserModal: false,
        editRoleModal: false,
        editAccessModal: false,
        selectedUser: null,
        selectedAccess: [],
        
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
                    alert('User berhasil ditambahkan');
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert('Gagal menambahkan user: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan sistem');
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
                    alert('Role berhasil diperbarui');
                } else {
                    alert('Gagal memperbarui role');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan sistem');
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
                    body: JSON.stringify({ hak_akses: this.selectedAccess })
                });

                if (response.ok) {
                    // Update local data
                    const index = this.users.findIndex(u => u.id === this.selectedUser.id);
                    if (index !== -1) {
                        this.users[index].hak_akses = [...this.selectedAccess];
                    }
                    this.editAccessModal = false;
                    alert('Hak akses berhasil diperbarui');
                } else {
                    alert('Gagal memperbarui hak akses');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan sistem');
            }
        },

        async updatePassword() {
            if (this.newPassword !== this.newPasswordConfirmation) {
                alert('Password konfirmasi tidak cocok');
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
                    alert('Password berhasil direset');
                } else {
                    const data = await response.json();
                    alert('Gagal reset password: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan sistem');
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
                    alert('User berhasil dihapus');
                } else {
                    const data = await response.json();
                    alert('Gagal menghapus user: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan sistem');
            }
        }
    }" class="bg-white rounded-xl shadow-sm p-6 lg:p-8 min-h-[800px] flex flex-col">
        
        <!-- Search Section -->
        <div class="mb-8 max-w-md">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Pencarian User</h2>
            <div class="relative w-full group">
                <input x-model="search" type="text" 
                       placeholder="Cari Nama User..." 
                       class="w-full bg-gray-50 border border-gray-300 rounded-lg pl-4 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Management User Section -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">Manajemen User</h2>
                <button @click="addUserModal = true" style="background-color: #2563eb !important; color: white !important;" class="bg-blue-600 text-white font-medium text-sm py-2.5 px-6 rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah User Baru
                </button>
            </div>

            <!-- Table -->
            <div class="bg-gray-50 rounded-xl p-6 overflow-x-auto border border-gray-100">
                <table class="w-full min-w-[1000px] border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-left">
                            <th class="pb-2 font-semibold text-xs text-gray-500 uppercase tracking-wider pl-4">Nama / Email</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 uppercase tracking-wider min-w-[150px] pr-4">Instansi</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 uppercase tracking-wider min-w-[150px] pr-4">Jabatan</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 uppercase tracking-wider min-w-[120px] pr-4">Role</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Hak Akses</th>
                            <th class="pb-2 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="space-y-4">
                        <template x-for="user in users.filter(u => u.name.toLowerCase().includes(search.toLowerCase()) || u.email.toLowerCase().includes(search.toLowerCase()))" :key="user.id">
                            <tr class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 group">
                                <td class="p-4 rounded-l-lg border-y border-l border-gray-200 group-hover:border-blue-300 transition-colors">
                                    <div class="font-bold text-sm text-gray-900" x-text="user.name"></div>
                                    <div class="text-xs text-gray-500" x-text="user.email"></div>
                                </td>
                                <td class="p-4 text-sm text-gray-700 border-y border-gray-200 group-hover:border-blue-300 transition-colors" x-text="user.instansi"></td>
                                <td class="p-4 text-sm text-gray-700 border-y border-gray-200 group-hover:border-blue-300 transition-colors" x-text="user.jabatan"></td>
                                <td class="p-4 border-y border-gray-200 group-hover:border-blue-300 transition-colors">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-700 mr-2" x-text="user.role"></span>
                                        <button @click="openEditRole(user)" class="text-blue-600 hover:text-blue-800 transition-colors p-1 rounded hover:bg-blue-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-blue-600">
                                                <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4 text-center border-y border-gray-200 group-hover:border-blue-300 transition-colors">
                                    <span style="background-color: #dcfce7 !important; color: #166534 !important; border: 1px solid #bbf7d0 !important;" class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200 shadow-sm" x-text="user.status"></span>
                                </td>
                                <td class="p-4 text-center border-y border-gray-200 group-hover:border-blue-300 transition-colors">
                                    <div @click="openEditAccess(user)" class="flex items-center justify-center space-x-2 cursor-pointer bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors group/access border border-blue-100">
                                        <span class="text-sm font-medium text-blue-700" x-text="user.hak_akses.length ? user.hak_akses.join(', ') : 'Pilih Akses'"></span>
                                        <div class="w-6 h-6 bg-blue-200 rounded flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-700">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 rounded-r-lg text-center border-y border-r border-gray-200 group-hover:border-blue-300 transition-colors">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Key Icon (Reset Password) -->
                                        <button @click="openResetPassword(user)" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Reset Password">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                        </button>
                                        <!-- Trash Icon (Delete) -->
                                        <button @click="openDeleteUser(user)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus User">
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
            <div class="bg-white rounded-xl p-6 w-[500px] shadow-2xl transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Tambah User Baru</h2>
                    <button @click="addUserModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input x-model="newUser.name" type="text" placeholder="Masukkan nama lengkap" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input x-model="newUser.email" type="email" placeholder="contoh@email.com" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instansi</label>
                        <input x-model="newUser.instansi" type="text" placeholder="Masukkan instansi" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <input x-model="newUser.jabatan" type="text" placeholder="Masukkan jabatan" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input x-model="newUser.password" type="password" placeholder="Minimal 8 karakter" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input x-model="newUser.password_confirmation" type="password" placeholder="Ulangi Password" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                         <select x-model="newUser.role" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                             <template x-for="role in availableRoles" :key="role">
                                 <option :value="role" x-text="role"></option>
                             </template>
                         </select>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="addUserModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Batal</button>
                        <button @click="saveUser()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan User</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div x-show="resetPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-8 w-[600px] shadow-2xl transform transition-all">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Reset Password</h2>
                    <p class="text-sm text-gray-500 mt-1">Buat password baru untuk user ini.</p>
                </div>
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User Terpilih</label>
                        <input type="text" :value="selectedUser?.name + ' (' + selectedUser?.email + ')'" disabled class="w-full bg-gray-100 border border-gray-200 rounded-lg p-3 text-sm text-gray-600 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input x-model="newPassword" type="password" placeholder="Masukkan password baru" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input x-model="newPasswordConfirmation" type="password" placeholder="Ulangi password baru" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div class="flex justify-end space-x-3 mt-8">
                        <button @click="resetPasswordModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Batal</button>
                        <button @click="updatePassword()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 !bg-blue-600 !text-white rounded-lg hover:!bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Update Password</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete User Modal -->
        <div x-show="deleteUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-8 w-[450px] shadow-2xl transform transition-all">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Hapus User</h2>
                        <p class="text-sm text-gray-500 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                
                <div class="space-y-5">
                    <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus user berikut?</p>
                    <div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4">
                            <div class="font-semibold text-gray-900" x-text="selectedUser?.name"></div>
                            <div class="text-sm text-gray-500" x-text="selectedUser?.email"></div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="deleteUserModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Batal</button>
                        <button @click="deleteUser()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Ya, Hapus User</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Role Modal -->
        <div x-show="editRoleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-8 w-[600px] shadow-2xl transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Edit Role</h2>
                    <button @click="editRoleModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 mb-2">Pilih role untuk user <span class="font-semibold" x-text="selectedUser?.name"></span>:</p>
                    <div class="grid grid-cols-2 gap-4">
                        <template x-for="role in availableRoles" :key="role">
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-4">
                                <input type="radio" name="role" :value="role" x-model="selectedUser.role" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="text-base font-medium text-gray-700" x-text="role"></span>
                            </label>
                        </template>
                    </div>
                    <div class="flex justify-end space-x-3 mt-8">
                        <button @click="editRoleModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Batal</button>
                        <button @click="saveRole()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 !bg-blue-600 !text-white rounded-lg hover:!bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Access Modal -->
        <div x-show="editAccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-8 w-[600px] shadow-2xl transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Edit Hak Akses</h2>
                    <button @click="editAccessModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 mb-2">Pilih modul yang dapat diakses oleh <span class="font-semibold" x-text="selectedUser?.name"></span>:</p>
                    <div class="grid grid-cols-2 gap-4">
                        <template x-for="access in availableAccess" :key="access">
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-4">
                                <input type="checkbox" :value="access" x-model="selectedAccess" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-base font-medium text-gray-700" x-text="access"></span>
                            </label>
                        </template>
                    </div>
                    <div class="flex justify-end space-x-3 mt-8">
                        <button @click="editAccessModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Batal</button>
                        <button @click="saveAccess()" style="background-color: #2563eb !important; color: white !important;" class="px-5 py-2.5 !bg-blue-600 !text-white rounded-lg hover:!bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
