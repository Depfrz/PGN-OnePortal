<x-dashboard-layout>
    <div x-data="{
        search: '',
        addModal: false,
        editKeteranganModal: false,
        deleteModal: false,
        selectedPengawas: null,
        selectedKeterangan: [],
        newLabel: '',
        newPengawas: { nama: '', tanggal: '', keterangan: [], new_keterangan: '' },
        items: {{ Js::from($items) }},
        options: {{ Js::from($options) }},
        openAdd() {
            this.newPengawas = { nama: '', tanggal: '', keterangan: [], new_keterangan: '' };
            this.addModal = true;
        },
        addNewKeteranganToForm() {
            const label = this.newPengawas.new_keterangan?.trim();
            if (!label) return;
            if (!this.options.includes(label)) this.options.push(label);
            if (!this.newPengawas.keterangan.includes(label)) this.newPengawas.keterangan.push(label);
            this.newPengawas.new_keterangan = '';
        },
        async savePengawas() {
            try {
                const response = await fetch('/list-pengawasan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        nama: this.newPengawas.nama,
                        tanggal: this.newPengawas.tanggal || null,
                        keterangan: this.newPengawas.keterangan
                    })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.items.unshift({
                        id: data.id,
                        nama: this.newPengawas.nama,
                        tanggal: this.newPengawas.tanggal || '-',
                        status: 'Active',
                        keterangan: [...this.newPengawas.keterangan]
                    });
                    this.addModal = false;
                } else {
                    alert('Gagal menambah pengawas');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        openEditKeterangan(item) {
            this.selectedPengawas = JSON.parse(JSON.stringify(item));
            this.selectedKeterangan = [...item.keterangan];
            this.newLabel = '';
            this.editKeteranganModal = true;
        },
        addNewKeteranganToEdit() {
            const label = this.newLabel?.trim();
            if (!label) return;
            if (!this.options.includes(label)) this.options.push(label);
            if (!this.selectedKeterangan.includes(label)) this.selectedKeterangan.push(label);
            this.newLabel = '';
        },
        async saveKeterangan() {
            try {
                const response = await fetch(`/list-pengawasan/${this.selectedPengawas.id}/keterangan`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ keterangan: this.selectedKeterangan })
                });
                if (response.ok) {
                    const idx = this.items.findIndex(i => i.id === this.selectedPengawas.id);
                    if (idx !== -1) this.items[idx].keterangan = [...this.selectedKeterangan];
                    this.editKeteranganModal = false;
                } else {
                    alert('Gagal menyimpan keterangan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        async renameOption(oldName, newName) {
            try {
                const response = await fetch('/list-pengawasan/keterangan/rename', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ old_name: oldName, new_name: newName })
                });
                if (response.ok) {
                    const i = this.options.findIndex(o => o === oldName);
                    if (i !== -1) this.options[i] = newName;
                    this.selectedKeterangan = this.selectedKeterangan.map(k => k === oldName ? newName : k);
                    this.items = this.items.map(it => ({...it, keterangan: it.keterangan.map(k => k === oldName ? newName : k)}));
                } else {
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal mengganti nama');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        async deleteOption(name) {
            try {
                const response = await fetch('/list-pengawasan/keterangan', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ name })
                });
                if (response.ok) {
                    this.options = this.options.filter(o => o !== name);
                    this.selectedKeterangan = this.selectedKeterangan.filter(k => k !== name);
                    this.items = this.items.map(it => ({...it, keterangan: it.keterangan.filter(k => k !== name)}));
                } else {
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal menghapus keterangan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        openDelete(item) {
            this.selectedPengawas = item;
            this.deleteModal = true;
        },
        async deletePengawas() {
            try {
                const response = await fetch(`/list-pengawasan/${this.selectedPengawas.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                if (response.ok) {
                    this.items = this.items.filter(i => i.id !== this.selectedPengawas.id);
                    this.deleteModal = false;
                } else {
                    alert('Gagal menghapus pengawas');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        }
    }" class="p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">List Pengawasan</h2>
            <div class="flex items-center gap-3">
                <div class="relative w-[380px]">
                    <input x-model="search" type="text" placeholder="Cari Nama..." class="w-full bg-[#f7f8f9] border border-[#d6d9de] rounded-2xl pl-6 pr-12 py-3 text-base text-gray-800 placeholder:text-[#6f7a86] shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-400">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-[#6f7a86] dark:text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.3 6.3a7.5 7.5 0 0 0 10.35 10.35Z" />
                        </svg>
                    </div>
                </div>
                <button @click="openAdd()" class="bg-blue-600 text-white font-medium text-sm py-2.5 px-6 rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Pengawas Baru
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="grid grid-cols-12 gap-4 px-6 py-4 text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                <div class="col-span-3">Nama</div>
                <div class="col-span-3">Tanggal</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-4 text-center">Keterangan</div>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <template x-for="item in items.filter(i => i.nama.toLowerCase().includes(search.toLowerCase()))" :key="item.id">
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-12 gap-4 items-start">
                            <div class="col-span-3 text-gray-900 font-semibold text-base dark:text-white" x-text="item.nama"></div>
                            <div class="col-span-3 text-gray-700 text-sm dark:text-gray-300" x-text="item.tanggal"></div>
                            <div class="col-span-2">
                                <span class="inline-flex items-center rounded-full bg-green-100 px-4 py-1 text-sm font-semibold text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-200 dark:border-green-800" x-text="item.status"></span>
                            </div>
                            <div class="col-span-4">
                                <div class="flex items-center justify-between">
                                    <div @click="openEditKeterangan(item)" class="flex items-center justify-center space-x-2 cursor-pointer bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors border border-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 dark:border-blue-800">
                                        <span class="text-sm font-medium text-blue-700 dark:text-blue-200" x-text="item.keterangan.length ? item.keterangan.join(', ') : 'Pilih Keterangan'"></span>
                                        <div class="w-6 h-6 bg-blue-200 rounded flex items-center justify-center dark:bg-blue-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-700 dark:text-blue-200">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    <button @click="openDelete(item)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20" title="Hapus Pengawas">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Add Pengawas Modal -->
        <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[560px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah Pengawas Baru</h2>
                    <button @click="addModal = false" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Nama</label>
                        <input x-model="newPengawas.nama" type="text" placeholder="Masukkan nama pengawas" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Tanggal</label>
                        <input x-model="newPengawas.tanggal" type="date" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Keterangan</label>
                        <div class="grid grid-cols-2 gap-3">
                            <template x-for="opt in options" :key="opt">
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-3 dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                    <input type="checkbox" :value="opt" x-model="newPengawas.keterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="opt"></span>
                                </label>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <input x-model="newPengawas.new_keterangan" type="text" placeholder="Tambah keterangan baru" class="flex-1 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                            <button @click="addNewKeteranganToForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Tambah</button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="addModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="savePengawas()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Keterangan Modal -->
        <div x-show="editKeteranganModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[560px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Keterangan</h2>
                    <button @click="editKeteranganModal = false" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="opt in options" :key="opt">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 hover:border-blue-200 transition-colors dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" :value="opt" x-model="selectedKeterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="opt"></span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <button @click="const v=prompt('Ganti nama:', opt); if(v && v.trim() && v.trim()!==opt){ renameOption(opt, v.trim()); }" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-blue-300 dark:hover:bg-blue-900/20" title="Rename">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                        </svg>
                                    </button>
                                    <button @click="if(confirm('Hapus keterangan ini?')){ deleteOption(opt); }" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <input x-model="newLabel" type="text" placeholder="Tambah keterangan baru" class="flex-1 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                        <button @click="addNewKeteranganToEdit()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Tambah</button>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="editKeteranganModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="saveKeterangan()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 dark:bg-red-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Pengawas</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus pengawas berikut?</p>
                    <div class="bg-red-50 border border-red-100 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-900/40">
                        <div class="font-semibold text-gray-900 dark:text-white" x-text="selectedPengawas?.nama"></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedPengawas?.tanggal"></div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="deleteModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="deletePengawas()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
