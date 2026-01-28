<x-dashboard-layout title="Detail Proyek" :can-write="$canWrite" :lp-permissions="$lpPermissions">
    <div x-data="{
        canWrite: {{ Js::from($canWrite ?? false) }},
        lpPerms: {{ Js::from($lpPermissions ?? []) }},
        toast: { show: false, message: '', timeoutId: null },
        project: {{ Js::from($item) }},
        editProject: { nama: {{ Js::from($item['nama'] ?? '') }}, divisi: {{ Js::from(($item['divisi'] ?? '-') === '-' ? '' : ($item['divisi'] ?? '')) }} },
        options: {{ Js::from($options ?? []) }},
        users: {{ Js::from($users ?? []) }},
        selectedKeterangan: {{ Js::from(collect($item['keterangan'] ?? [])->pluck('label')->values()) }},
        deleteBuktiModal: false,
        selectedBuktiItem: null,
        deleteKeteranganBuktiModal: false,
        selectedKeteranganBukti: { item: null, label: '' },
        showToast(message) {
            this.toast.message = message;
            this.toast.show = true;
            if (this.toast.timeoutId) clearTimeout(this.toast.timeoutId);
            this.toast.timeoutId = setTimeout(() => { this.toast.show = false; }, 2200);
        },
        async saveProject() {
            if (!this.canWrite || !this.lpPerms.nama_proyek) return;
            const payload = {
                nama: this.editProject.nama?.trim() || '',
                divisi: this.editProject.divisi?.trim() || null,
            };
            if (!payload.nama) {
                this.showToast('Nama kegiatan wajib diisi');
                return;
            }
            try {
                const response = await fetch(`/list-pengawasan/${this.project.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(payload)
                });
                if (response.ok) {
                    this.project.nama = payload.nama;
                    this.project.divisi = payload.divisi || '-';
                    this.showToast('Detail kegiatan berhasil diperbarui');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal memperbarui detail kegiatan');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async saveKeterangan() {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            try {
                const response = await fetch(`/list-pengawasan/${this.project.id}/keterangan`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ keterangan: this.selectedKeterangan })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.project.keterangan = data.keterangan || [];
                    this.selectedKeterangan = (this.project.keterangan || []).map(k => k.label);
                    this.showToast('Keterangan berhasil disimpan');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menyimpan keterangan');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async uploadBukti(file) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            try {
                const formData = new FormData();
                formData.append('bukti', file);
                const response = await fetch(`/list-pengawasan/${this.project.id}/bukti`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: formData
                });
                if (response.ok) {
                    const data = await response.json();
                    this.project.bukti = data.bukti;
                    this.showToast('Bukti berhasil diunggah');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal mengunggah bukti');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        onBuktiChange(e) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const file = e?.target?.files?.[0];
            if (!file) return;
            this.uploadBukti(file);
            e.target.value = '';
        },
        openDeleteBukti() {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            this.selectedBuktiItem = this.project;
            this.deleteBuktiModal = true;
        },
        async confirmDeleteBukti() {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            try {
                const response = await fetch(`/list-pengawasan/${this.project.id}/bukti`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                if (response.ok) {
                    this.project.bukti = { path: null, name: null, mime: null, size: null, uploaded_at: null, url: null };
                    this.deleteBuktiModal = false;
                    this.selectedBuktiItem = null;
                    this.showToast('Bukti berhasil dihapus');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menghapus bukti');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        getKeteranganBukti(label) {
            const found = (this.project.keterangan || []).find(k => k.label === label);
            return found ? found.bukti : null;
        },
        async uploadKeteranganBukti(label, file) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const formData = new FormData();
            formData.append('label', label);
            formData.append('bukti', file);
            try {
                const response = await fetch(`/list-pengawasan/${this.project.id}/keterangan/bukti`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: formData
                });
                if (response.ok) {
                    const data = await response.json();
                    const idx = (this.project.keterangan || []).findIndex(k => k.label === label);
                    if (idx !== -1) this.project.keterangan[idx].bukti = data.bukti;
                    this.showToast('Bukti keterangan berhasil diunggah');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal mengunggah bukti');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        onKeteranganBuktiChange(label, e) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const file = e?.target?.files?.[0];
            if (!file) return;
            this.uploadKeteranganBukti(label, file);
            e.target.value = '';
        },
        deleteKeteranganBukti(label) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            this.selectedKeteranganBukti = { item: this.project, label };
            this.deleteKeteranganBuktiModal = true;
        },
        async confirmDeleteKeteranganBukti() {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const { label } = this.selectedKeteranganBukti;
            if (!label) return;
            try {
                const response = await fetch(`/list-pengawasan/${this.project.id}/keterangan/bukti`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ label })
                });
                if (response.ok) {
                    const idx = (this.project.keterangan || []).findIndex(k => k.label === label);
                    if (idx !== -1) this.project.keterangan[idx].bukti = null;
                    this.deleteKeteranganBuktiModal = false;
                    this.selectedKeteranganBukti = { item: null, label: '' };
                    this.showToast('Bukti keterangan berhasil dihapus');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menghapus bukti');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        }
    }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 lg:p-8 min-h-[800px] transition-colors duration-300">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('list-pengawasan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Detail Proyek</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="project.nama"></div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Informasi Kegiatan</div>
                        <button type="button" x-show="canWrite && lpPerms.nama_proyek" @click="saveProject()" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Kegiatan</div>
                            <input x-model="editProject.nama" :disabled="!canWrite || !lpPerms.nama_proyek" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none disabled:opacity-60 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Divisi</div>
                            <input x-model="editProject.divisi" :disabled="!canWrite || !lpPerms.nama_proyek" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none disabled:opacity-60 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Tanggal Dibuat</div>
                            <div class="px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-800 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" x-text="project.tanggal || '-'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Pengawas</div>
                            <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-2.5 dark:bg-gray-900 dark:border-gray-700">
                                <template x-if="!project.pengawas_users || project.pengawas_users.length === 0">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                </template>
                                <template x-for="u in (project.pengawas_users || [])" :key="`pengawas-${project.id}-${u.id}`">
                                    <div class="flex items-center justify-between gap-3 py-1">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="u.name"></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="u.email"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-1">Deadline</div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="project.deadline_display || '-'"></div>
                </div>

                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Bukti Proyek</div>
                        <div class="flex items-center gap-2" x-show="canWrite && lpPerms.bukti">
                            <label class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors cursor-pointer">
                                Upload
                                <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="onBuktiChange($event)" />
                            </label>
                            <button type="button" x-show="project.bukti && project.bukti.url" @click="openDeleteBukti()" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">Hapus</button>
                        </div>
                    </div>
                    <template x-if="project.bukti && project.bukti.url">
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="project.bukti.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="project.bukti.uploaded_at ? `Diunggah: ${project.bukti.uploaded_at}` : ''"></div>
                            <a class="inline-flex mt-3 text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400" :href="project.bukti.url" target="_blank" rel="noopener">Lihat File</a>
                        </div>
                    </template>
                    <template x-if="!project.bukti || !project.bukti.url">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Belum ada bukti.</div>
                    </template>
                </div>

                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Keterangan</div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="opt in options" :key="`opt-${opt}`">
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-3 dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <input type="checkbox" :value="opt" x-model="selectedKeterangan" :disabled="!canWrite || !lpPerms.bukti" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-60">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="opt"></span>
                            </label>
                        </template>
                    </div>

                    <div class="mt-6 space-y-3">
                        <template x-for="k in (project.keterangan || [])" :key="`k-${k.label}`">
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="k.label"></div>
                                        <template x-if="getKeteranganBukti(k.label)">
                                            <div class="mt-2">
                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="getKeteranganBukti(k.label)?.name"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="getKeteranganBukti(k.label)?.uploaded_at ? `Diunggah: ${getKeteranganBukti(k.label).uploaded_at}` : ''"></div>
                                                <a class="inline-flex mt-2 text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400" :href="getKeteranganBukti(k.label)?.url" target="_blank" rel="noopener">Lihat File</a>
                                            </div>
                                        </template>
                                        <template x-if="!getKeteranganBukti(k.label)">
                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Belum ada bukti.</div>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2" x-show="canWrite && lpPerms.bukti">
                                        <label class="px-3 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors cursor-pointer text-sm">
                                            Upload
                                            <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="onKeteranganBuktiChange(k.label, $event)" />
                                        </label>
                                        <button type="button" x-show="getKeteranganBukti(k.label)" @click="deleteKeteranganBukti(k.label)" class="px-3 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors text-sm">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
        </div>

        <div x-show="deleteBuktiModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Bukti Proyek</h2>
                    <button @click="deleteBuktiModal = false; selectedBuktiItem = null" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/30 dark:bg-red-900/20">
                        <div class="text-[11px] font-semibold tracking-wider text-red-700 uppercase dark:text-red-300">File</div>
                        <div class="mt-1 text-sm font-semibold text-red-800 dark:text-red-200" x-text="project?.bukti?.name || '-'"></div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="deleteBuktiModal = false; selectedBuktiItem = null" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmDeleteBukti()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="deleteKeteranganBuktiModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Bukti Keterangan</h2>
                    <button @click="deleteKeteranganBuktiModal = false; selectedKeteranganBukti = { item: null, label: '' }" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/30 dark:bg-red-900/20">
                        <div class="text-[11px] font-semibold tracking-wider text-red-700 uppercase dark:text-red-300">Keterangan</div>
                        <div class="mt-1 text-sm font-semibold text-red-800 dark:text-red-200" x-text="selectedKeteranganBukti?.label || '-'"></div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="deleteKeteranganBuktiModal = false; selectedKeteranganBukti = { item: null, label: '' }" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmDeleteKeteranganBukti()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-[10000]">
            <div class="bg-gray-900 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm" x-text="toast.message"></div>
        </div>
    </div>
</x-dashboard-layout>
