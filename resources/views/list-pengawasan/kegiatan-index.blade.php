<x-dashboard-layout title="Daftar Kegiatan" :can-write="$canWrite" :lp-permissions="$lpPermissions">
    <div x-data="{
        canWrite: {{ Js::from($canWrite ?? false) }},
        lpPerms: {{ Js::from($lpPermissions ?? []) }},
        projectId: {{ Js::from($project->id) }},
        search: '',
        addModal: false,
        deleteModal: false,
        selectedItem: null,
        toast: { show: false, message: '', timeoutId: null },
        newKegiatan: { nama_kegiatan: '', deskripsi: '' },
        items: {{ Js::from($activities->items()) }},

        init() {
        },
        
        showToast(message) {
            this.toast.message = message;
            this.toast.show = true;
            if (this.toast.timeoutId) clearTimeout(this.toast.timeoutId);
            this.toast.timeoutId = setTimeout(() => { this.toast.show = false; }, 2200);
        },

        openAdd() {
            if (!this.canWrite) return;
            this.newKegiatan = { nama_kegiatan: '', deskripsi: '' };
            this.addModal = true;
        },

        async saveKegiatan() {
            if (!this.canWrite) return;
            try {
                const response = await fetch(`/list-pengawasan/${this.projectId}/kegiatan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(this.newKegiatan)
                });
                
                if (response.ok) {
                    const data = await response.json();
                    window.location.reload();
                } else {
                    const d = await response.json().catch(() => ({}));
                    console.error('Error response:', d);
                    alert(d.message || 'Gagal menambah kegiatan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem: ' + e.message);
            }
        },

        openDelete(item) {
            if (!this.canWrite) return;
            this.selectedItem = item;
            this.deleteModal = true;
        },

        async deleteKegiatan() {
            if (!this.canWrite || !this.selectedItem) return;
            try {
                const response = await fetch(`/list-pengawasan/kegiatan/${this.selectedItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Gagal menghapus kegiatan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
    }" class="p-4 sm:p-6">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('list-pengawasan.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                        </svg>
                        List Pengawasan
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">{{ $project->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Daftar Kegiatan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Proyek: {{ $project->name }}</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Search & Filter would go here if implemented fully -->
                
                <button x-show="canWrite && lpPerms.tambah_kegiatan" :disabled="!canWrite || !lpPerms.tambah_kegiatan" @click="openAdd()" class="w-full sm:w-auto bg-blue-600 text-white font-medium text-sm py-2.5 px-6 rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg inline-flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Kegiatan
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Mobile View -->
            <div class="block sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($activities as $item)
                <div class="p-4 flex flex-col gap-2">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('list-pengawasan.kegiatan.show', $item->id) }}" class="text-base font-semibold text-gray-900 hover:text-blue-600 hover:underline dark:text-white line-clamp-2">
                                {{ $item->nama_kegiatan }}
                            </a>
                            <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0" x-show="canWrite && lpPerms.hapus_kegiatan">
                            <button @click="openDelete({{ Js::from($item) }})" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20 dark:hover:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    Belum ada kegiatan.
                </div>
                @endforelse
            </div>

            <!-- Desktop View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Kegiatan</th>
                            <th scope="col" class="px-6 py-3">Tanggal Mulai</th>
                            <th scope="col" class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $item)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <a href="{{ route('list-pengawasan.kegiatan.show', $item->id) }}" class="hover:text-blue-600 hover:underline">
                                    {{ $item->nama_kegiatan }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button x-show="canWrite && lpPerms.hapus_kegiatan" :disabled="!canWrite || !lpPerms.hapus_kegiatan" @click="openDelete({{ Js::from($item) }})" class="font-medium text-red-600 dark:text-red-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                Belum ada kegiatan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $activities->links() }}
            </div>
        </div>

        <!-- Add Modal -->
        <div x-show="addModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-md shadow-2xl dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Kegiatan Baru</h3>
                    <button @click="addModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kegiatan</label>
                        <input x-model="newKegiatan.nama_kegiatan" type="text" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                        <textarea x-model="newKegiatan.deskripsi" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="addModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Batal</button>
                        <button @click="saveKegiatan()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="deleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-sm shadow-2xl dark:bg-gray-800">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Hapus Kegiatan?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Apakah Anda yakin ingin menghapus kegiatan <span class="font-bold" x-text="selectedItem?.nama_kegiatan"></span>? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="flex justify-center space-x-3">
                        <button @click="deleteModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Batal</button>
                        <button @click="deleteKegiatan()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
