<x-dashboard-layout>
    <div x-data="{
        search: '',
        addModal: false,
        editKeteranganModal: false,
        deleteModal: false,
        clearKeteranganModal: false,
        deleteBuktiModal: false,
        selectedPengawas: null,
        selectedKeterangan: [],
        newLabel: '',
        toast: { show: false, message: '', timeoutId: null },
        editingId: null,
        editPengawas: { nama: '' },
        statusMenu: { open: false, x: 0, y: 0, item: null },
        newPengawas: { nama: '', status: 'On Progress', keterangan: [], new_keterangan: '' },
        selectedBuktiItem: null,
        items: {{ Js::from($items) }},
        options: {{ Js::from($options) }},
        openAdd() {
            this.newPengawas = { nama: '', status: 'On Progress', keterangan: [], new_keterangan: '' };
            this.addModal = true;
        },
        addNewKeteranganToForm() {
            const label = this.newPengawas.new_keterangan?.trim();
            if (!label) return;
            if (!this.options.includes(label)) this.options.push(label);
            if (!this.newPengawas.keterangan.includes(label)) this.newPengawas.keterangan.push(label);
            this.newPengawas.new_keterangan = '';
        },
        showToast(message) {
            this.toast.message = message;
            this.toast.show = true;
            if (this.toast.timeoutId) clearTimeout(this.toast.timeoutId);
            this.toast.timeoutId = setTimeout(() => { this.toast.show = false; }, 2200);
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
                        status: this.newPengawas.status,
                        keterangan: this.newPengawas.keterangan
                    })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.items.unshift({
                        id: data.id,
                        nama: this.newPengawas.nama,
                        tanggal: data.tanggal || '-',
                        status: data.status || 'On Progress',
                        keterangan: [...this.newPengawas.keterangan],
                        bukti: { path: null, name: null, mime: null, size: null, uploaded_at: null, url: null }
                    });
                    this.addModal = false;
                    this.showToast('Proyek berhasil ditambahkan');
                } else {
                    alert('Gagal menambah proyek');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        async setStatus(item, status) {
            const previous = item.status;
            item.status = status;
            try {
                const response = await fetch(`/list-pengawasan/${item.id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ status })
                });
                if (!response.ok) {
                    item.status = previous;
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal memperbarui status');
                }
            } catch (e) {
                item.status = previous;
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        statusMeta(status) {
            if (status === 'OFF') return { label: 'OFF', cls: 'bg-red-600 text-white hover:bg-red-700' };
            if (status === 'Done') return { label: 'Done', cls: 'bg-green-600 text-white hover:bg-green-700' };
            return { label: 'On Progress', cls: 'bg-amber-600 text-white hover:bg-amber-700' };
        },
        openStatusMenu(e, item) {
            const rect = e.currentTarget.getBoundingClientRect();
            const menuWidth = 176;
            const menuHeight = 156;
            let x = rect.left;
            let y = rect.bottom + 8;

            const maxX = window.innerWidth - menuWidth - 12;
            if (x > maxX) x = maxX;
            if (x < 12) x = 12;

            const maxY = window.innerHeight - menuHeight - 12;
            if (y > maxY) y = rect.top - menuHeight - 8;
            if (y < 12) y = 12;

            this.statusMenu = { open: true, x, y, item };
        },
        closeStatusMenu() {
            this.statusMenu = { open: false, x: 0, y: 0, item: null };
        },
        startEdit(item) {
            this.editingId = item.id;
            this.editPengawas = { nama: item.nama };
        },
        cancelEdit() {
            this.editingId = null;
            this.editPengawas = { nama: '' };
        },
        async saveEdit(item) {
            const payload = {
                nama: this.editPengawas.nama?.trim() || ''
            };

            if (!payload.nama) {
                alert('Nama proyek wajib diisi');
                return;
            }

            try {
                const response = await fetch(`/list-pengawasan/${item.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(payload)
                });
                if (response.ok) {
                    item.nama = payload.nama;
                    this.cancelEdit();
                    this.showToast('Nama proyek berhasil diperbarui');
                } else {
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal memperbarui data');
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
                    this.showToast('Keterangan berhasil disimpan');
                } else {
                    alert('Gagal menyimpan keterangan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        openClearKeterangan() {
            this.clearKeteranganModal = true;
        },
        async confirmClearKeterangan() {
            this.selectedKeterangan = [];
            await this.saveKeterangan();
            this.clearKeteranganModal = false;
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
                    this.showToast('Proyek berhasil dihapus');
                } else {
                    alert('Gagal menghapus proyek');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        formatSize(bytes) {
            if (!bytes && bytes !== 0) return '';
            const units = ['B', 'KB', 'MB', 'GB'];
            let v = bytes;
            let idx = 0;
            while (v >= 1024 && idx < units.length - 1) {
                v /= 1024;
                idx++;
            }
            return `${v.toFixed(idx === 0 ? 0 : 1)} ${units[idx]}`;
        },
        isImage(mime) {
            return !!mime && mime.startsWith('image/');
        },
        triggerUpload(item) {
            const el = document.getElementById(`bukti-input-${item.id}`);
            if (el) el.click();
        },
        async uploadBukti(item, file) {
            try {
                const formData = new FormData();
                formData.append('bukti', file);
                const response = await fetch(`/list-pengawasan/${item.id}/bukti`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: formData
                });
                if (response.ok) {
                    const data = await response.json();
                    item.bukti = data.bukti;
                    this.showToast('Bukti berhasil diunggah');
                } else {
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal mengunggah bukti');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        onBuktiChange(item, e) {
            const file = e?.target?.files?.[0];
            if (!file) return;
            this.uploadBukti(item, file);
            e.target.value = '';
        },
        openDeleteBukti(item) {
            this.selectedBuktiItem = item;
            this.deleteBuktiModal = true;
        },
        async confirmDeleteBukti() {
            const item = this.selectedBuktiItem;
            if (!item) return;
            try {
                const response = await fetch(`/list-pengawasan/${item.id}/bukti`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                if (response.ok) {
                    item.bukti = { path: null, name: null, mime: null, size: null, uploaded_at: null, url: null };
                    this.deleteBuktiModal = false;
                    this.selectedBuktiItem = null;
                    this.showToast('Bukti berhasil dihapus');
                } else {
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal menghapus bukti');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
    }" class="p-6">
        <template x-teleport="body">
            <div x-show="toast.show"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed top-5 right-5 z-[9999]"
                 style="display: none;">
                <div class="flex items-center gap-3 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-2xl px-4 py-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                            <path fill-rule="evenodd" d="M16.704 4.294a.75.75 0 01.002 1.06l-8.25 8.25a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 011.06-1.06l3.22 3.22 7.72-7.72a.75.75 0 011.058 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="toast.message"></div>
                </div>
            </div>
        </template>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">List Proyek</h2>
            <div class="flex items-center gap-3">
                <div class="relative w-[380px]">
                    <input x-model="search" type="text" placeholder="Cari Proyek..." class="w-full bg-[#f7f8f9] border border-[#d6d9de] rounded-2xl pl-6 pr-12 py-3 text-base text-gray-800 placeholder:text-[#6f7a86] shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-400">
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
                    Tambah Proyek
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="grid grid-cols-12 gap-4 px-6 py-4 text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                <div class="col-span-4">Nama Proyek</div>
                <div class="col-span-2">Tanggal & Waktu</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-2">Keterangan</div>
                <div class="col-span-2">Bukti</div>
            </div>

            <div class="max-h-[60vh] overflow-y-auto custom-scrollbar">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="item in items.filter(i => i.nama.toLowerCase().includes(search.toLowerCase()))" :key="item.id">
                        <div class="px-6 py-6">
                            <div class="grid grid-cols-12 gap-4 items-start">
                            <div class="col-span-4">
                                <div class="flex items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <template x-if="editingId === item.id">
                                            <input x-model="editPengawas.nama" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                                        </template>
                                        <template x-if="editingId !== item.id">
                                            <div class="text-gray-900 font-semibold text-base dark:text-white truncate" x-text="item.nama"></div>
                                        </template>
                                    </div>

                                    <template x-if="editingId !== item.id">
                                        <div class="flex items-center gap-1">
                                            <button @click="startEdit(item)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-gray-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20" title="Edit Nama Proyek">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                    <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                                </svg>
                                            </button>
                                            <button @click="openDelete(item)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20" title="Hapus Proyek">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="editingId === item.id">
                                        <div class="flex items-center gap-1">
                                            <button @click="saveEdit(item)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors dark:hover:bg-green-900/20" title="Simpan">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                    <path fill-rule="evenodd" d="M16.704 4.294a.75.75 0 01.002 1.06l-8.25 8.25a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 011.06-1.06l3.22 3.22 7.72-7.72a.75.75 0 011.058 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <button @click="cancelEdit()" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20" title="Batal">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="col-span-2 text-gray-700 text-sm dark:text-gray-300" x-text="item.tanggal"></div>
                                <div class="col-span-2">
                                    <button
                                        type="button"
                                        class="inline-flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs font-bold shadow-sm transition-colors whitespace-nowrap"
                                        :class="statusMeta(item.status).cls"
                                        @click="openStatusMenu($event, item)"
                                    >
                                        <span class="truncate" x-text="statusMeta(item.status).label"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-90 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0l-4.24-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            <div class="col-span-2 min-w-0">
                                <button type="button" @click="openEditKeterangan(item)" class="w-full flex items-center justify-between gap-2 bg-gray-50 hover:bg-gray-100 p-2 rounded-lg transition-colors border border-gray-200 text-left dark:bg-gray-900 dark:hover:bg-gray-800 dark:border-gray-700">
                                    <span class="min-w-0 flex-1 text-sm font-medium text-gray-700 dark:text-gray-200 truncate" x-text="item.keterangan.length ? item.keterangan.join(', ') : 'Tambah keterangan'"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-gray-500 dark:text-gray-300 flex-shrink-0">
                                        <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="col-span-2">
                                <div class="flex items-center gap-2">
                                    <input type="file" class="hidden" :id="`bukti-input-${item.id}`" accept="image/png,image/jpeg,application/pdf" @change="onBuktiChange(item, $event)">

                                    <template x-if="!item.bukti || !item.bukti.url">
                                        <button type="button" @click="triggerUpload(item)" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                            Upload
                                        </button>
                                    </template>

                                    <template x-if="item.bukti && item.bukti.url">
                                        <a :href="item.bukti.url" target="_blank" class="min-w-0 flex-1 flex items-center gap-2">
                                            <template x-if="isImage(item.bukti.mime)">
                                                <img :src="item.bukti.url" alt="Bukti" class="w-9 h-9 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                            </template>
                                            <template x-if="!isImage(item.bukti.mime)">
                                                <div class="w-9 h-9 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center text-red-600 dark:bg-red-900/20 dark:border-red-900/30 dark:text-red-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                        <path fill-rule="evenodd" d="M6 2.25A2.25 2.25 0 0 0 3.75 4.5v15A2.25 2.25 0 0 0 6 21.75h12A2.25 2.25 0 0 0 20.25 19.5V8.56a2.25 2.25 0 0 0-.659-1.591l-3.56-3.56A2.25 2.25 0 0 0 14.44 2.25H6Zm7.5 1.5V7.5a.75.75 0 0 0 .75.75h3.75l-4.5-4.5Z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </template>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="item.bukti.name || 'Bukti'"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatSize(item.bukti.size)"></div>
                                            </div>
                                        </a>
                                    </template>

                                    <template x-if="item.bukti && item.bukti.url">
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="triggerUpload(item)" class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors dark:text-gray-300 dark:hover:bg-gray-800" title="Ganti Bukti">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                    <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="openDeleteBukti(item)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20" title="Hapus Bukti">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <template x-teleport="body">
            <div
                x-show="statusMenu.open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[9999]"
                style="display: none;"
            >
                <div class="absolute inset-0" @click="closeStatusMenu()"></div>
                <div
                    class="fixed w-44 rounded-xl border border-gray-100 bg-white shadow-xl ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 overflow-hidden"
                    :style="`left:${statusMenu.x}px; top:${statusMenu.y}px;`"
                >
                    <button type="button" class="w-full text-left px-4 py-3 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3"
                            @click="setStatus(statusMenu.item, 'OFF'); closeStatusMenu()">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                        OFF
                    </button>
                    <button type="button" class="w-full text-left px-4 py-3 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3"
                            @click="setStatus(statusMenu.item, 'On Progress'); closeStatusMenu()">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-600"></span>
                        On Progress
                    </button>
                    <button type="button" class="w-full text-left px-4 py-3 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3"
                            @click="setStatus(statusMenu.item, 'Done'); closeStatusMenu()">
                        <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span>
                        Done
                    </button>
                </div>
            </div>
        </template>

        <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[560px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah Proyek</h2>
                    <button @click="addModal = false" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Nama Proyek</label>
                        <input x-model="newPengawas.nama" type="text" placeholder="Masukkan nama proyek" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Status</label>
                        <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <button
                                type="button"
                                class="px-4 py-2 text-sm font-semibold rounded-md transition-colors"
                                :class="newPengawas.status === 'OFF' ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800'"
                                @click="newPengawas.status = 'OFF'"
                            >OFF</button>
                            <button
                                type="button"
                                class="px-4 py-2 text-sm font-semibold rounded-md transition-colors"
                                :class="newPengawas.status === 'On Progress' ? 'bg-amber-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800'"
                                @click="newPengawas.status = 'On Progress'"
                            >On Progress</button>
                            <button
                                type="button"
                                class="px-4 py-2 text-sm font-semibold rounded-md transition-colors"
                                :class="newPengawas.status === 'Done' ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800'"
                                @click="newPengawas.status = 'Done'"
                            >Done</button>
                        </div>
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
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-3 dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <input type="checkbox" :value="opt" x-model="selectedKeterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="opt"></span>
                            </label>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <input x-model="newLabel" type="text" placeholder="Tambah keterangan baru" class="flex-1 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                        <button @click="addNewKeteranganToEdit()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Tambah</button>
                    </div>
                    <div class="flex items-center justify-between mt-6">
                        <button @click="openClearKeterangan()" class="px-5 py-2.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-medium transition-colors dark:bg-red-900/20 dark:text-red-200 dark:hover:bg-red-900/30">Hapus Semua</button>
                        <div class="flex items-center space-x-3">
                            <button @click="editKeteranganModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                            <button @click="saveKeterangan()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                        </div>
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
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Proyek</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus proyek berikut?</p>
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

        <div x-show="clearKeteranganModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 dark:bg-red-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Semua Keterangan</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus semua keterangan untuk proyek ini?</p>
                    <div class="bg-red-50 border border-red-100 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-900/40">
                        <div class="font-semibold text-gray-900 dark:text-white" x-text="selectedPengawas?.nama"></div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="clearKeteranganModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmClearKeterangan()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="deleteBuktiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 dark:bg-red-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Bukti</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">File bukti akan dihapus dari sistem.</p>
                    </div>
                </div>
                <div class="space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus bukti untuk proyek ini?</p>
                    <div class="bg-red-50 border border-red-100 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-900/40">
                        <div class="font-semibold text-gray-900 dark:text-white" x-text="selectedBuktiItem?.nama"></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedBuktiItem?.bukti?.name"></div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="deleteBuktiModal = false; selectedBuktiItem = null" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmDeleteBukti()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
