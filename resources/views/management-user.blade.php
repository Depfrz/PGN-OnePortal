<x-dashboard-layout>
    <div x-data="{
        search: '',
        addUserModal: false,
        resetPasswordModal: false,
        deleteUserModal: false,
        selectedUser: null,
        users: [
            { id: 1, name: 'Falih', email: 'falih@gmail.com', password: 'Falih123', instansi: 'Telkom', jabatan: 'Mahasiswa', role: 'Manager', status: 'Active', hak_akses: 'izin' }
        ],
        
        openResetPassword(user) {
            this.selectedUser = user;
            this.resetPasswordModal = true;
        },
        
        openDeleteUser(user) {
            this.selectedUser = user;
            this.deleteUserModal = true;
        }
    }" class="bg-white rounded-[10px] p-4 lg:p-8 min-h-[800px] flex flex-col">
        
        <!-- Search Section -->
        <div class="mb-8">
            <h2 class="text-[20px] font-bold text-black mb-4">Pencarian User</h2>
            <div class="relative w-full">
                <input x-model="search" type="text" 
                       placeholder="Cari Nama ......" 
                       class="w-full bg-white border border-black rounded-[10px] px-6 py-4 text-[18px] focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Management User Section -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-[24px] font-bold text-black">Manajemen User</h2>
                <button @click="addUserModal = true" class="bg-[#0643fb] text-white font-bold py-2 px-6 rounded-[10px] hover:bg-blue-700 transition-colors">
                    Tambah User baru
                </button>
            </div>

            <!-- Table -->
            <div class="bg-[#e5e5e5] rounded-[15px] p-6 overflow-x-auto">
                <table class="w-full min-w-[1000px]">
                    <thead>
                        <tr class="text-left">
                            <th class="pb-4 font-bold text-[18px] text-black">Nama / Email</th>
                            <th class="pb-4 font-bold text-[18px] text-black">Password</th>
                            <th class="pb-4 font-bold text-[18px] text-black">Instansi</th>
                            <th class="pb-4 font-bold text-[18px] text-black">Jabatan</th>
                            <th class="pb-4 font-bold text-[18px] text-black">Role</th>
                            <th class="pb-4 font-bold text-[18px] text-black text-center">Status</th>
                            <th class="pb-4 font-bold text-[18px] text-black text-center">Hak Akses</th>
                            <th class="pb-4 font-bold text-[18px] text-black text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="space-y-4">
                        <template x-for="user in users" :key="user.id">
                            <tr class="bg-white border border-black rounded-[15px]">
                                <td class="p-4 rounded-l-[15px]">
                                    <div class="font-bold text-[18px]" x-text="user.name"></div>
                                    <div class="text-[14px] text-gray-600" x-text="user.email"></div>
                                </td>
                                <td class="p-4 font-bold text-[18px]" x-text="user.password"></td>
                                <td class="p-4 font-bold text-[18px]" x-text="user.instansi"></td>
                                <td class="p-4 font-bold text-[18px]" x-text="user.jabatan"></td>
                                <td class="p-4">
                                    <div class="flex items-center">
                                        <span class="font-bold text-[18px] mr-2" x-text="user.role"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 cursor-pointer">
                                            <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                        </svg>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="bg-[#30ff07] px-6 py-1 rounded-full font-bold text-black text-[14px]" x-text="user.status"></span>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <span class="font-bold text-[18px]" x-text="user.hak_akses"></span>
                                        <div class="w-6 h-6 border border-black rounded flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 rounded-r-[15px] text-center">
                                    <div class="flex items-center justify-center space-x-4">
                                        <!-- Key Icon (Reset Password) -->
                                        <button @click="openResetPassword(user)" class="p-1 hover:bg-gray-100 rounded">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                        </button>
                                        <!-- Trash Icon (Delete) -->
                                        <button @click="openDeleteUser(user)" class="p-1 hover:bg-gray-100 rounded">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
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
        <div x-show="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div class="bg-white rounded-[15px] p-8 w-[600px] shadow-lg">
                <h2 class="text-[24px] font-bold mb-6">Tambah User Baru</h2>
                <div class="space-y-4">
                    <input type="text" placeholder="Nama" class="w-full border border-gray-300 rounded p-2">
                    <input type="email" placeholder="Email" class="w-full border border-gray-300 rounded p-2">
                    <input type="password" placeholder="Password" class="w-full border border-gray-300 rounded p-2">
                    <div class="flex justify-end space-x-4 mt-6">
                        <button @click="addUserModal = false" class="px-6 py-2 bg-gray-300 rounded hover:bg-gray-400 font-bold">Batal</button>
                        <button @click="addUserModal = false" class="px-6 py-2 bg-[#0643fb] text-white rounded hover:bg-blue-700 font-bold">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div x-show="resetPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div class="bg-white rounded-[15px] p-8 w-[500px] shadow-lg">
                <h2 class="text-[24px] font-bold mb-6">Reset Password</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block font-bold mb-2">Nama/Email</label>
                        <input type="text" :value="selectedUser?.name + '/' + selectedUser?.email" disabled class="w-full bg-[#d9d9d9] border-none rounded p-3">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Password baru</label>
                        <input type="password" class="w-full bg-[#d9d9d9] border-none rounded p-3">
                    </div>
                    <div class="flex justify-end space-x-4 mt-8">
                        <button @click="resetPasswordModal = false" class="px-8 py-2 bg-[#e5e5e5] rounded-[10px] font-bold text-black hover:bg-gray-300">Batal</button>
                        <button @click="resetPasswordModal = false" class="px-8 py-2 bg-[#0643fb] text-white rounded-[10px] font-bold hover:bg-blue-700">Setuju</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete User Modal -->
        <div x-show="deleteUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div class="bg-white rounded-[15px] p-8 w-[500px] shadow-lg">
                <h2 class="text-[24px] font-bold mb-6">Hapus User</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block font-bold mb-2">Nama/Email</label>
                        <input type="text" :value="selectedUser?.name + '/' + selectedUser?.email" disabled class="w-full bg-[#d9d9d9] border-none rounded p-3">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Password User</label>
                        <input type="password" class="w-full bg-[#d9d9d9] border-none rounded p-3">
                    </div>
                    <div class="flex justify-end space-x-4 mt-8">
                        <button @click="deleteUserModal = false" class="px-8 py-2 bg-[#e5e5e5] rounded-[10px] font-bold text-black hover:bg-gray-300">Batal</button>
                        <button @click="deleteUserModal = false" class="px-8 py-2 bg-[#0643fb] text-white rounded-[10px] font-bold hover:bg-blue-700">Setuju</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-dashboard-layout>
