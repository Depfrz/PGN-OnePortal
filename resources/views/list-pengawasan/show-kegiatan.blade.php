<x-dashboard-layout title="Detail Kegiatan" :can-write="$canWrite" :lp-permissions="$lpPermissions">
    <div x-data="{
        canWrite: {{ Js::from($canWrite ?? false) }},
        lpPerms: {{ Js::from($lpPermissions ?? []) }},
        toast: { show: false, message: '', timeoutId: null },
        project: {{ Js::from($item) }},
        editProject: { nama: {{ Js::from($item['nama'] ?? '') }}, deskripsi: {{ Js::from($item['deskripsi'] ?? '') }} },
        options: {{ Js::from($options ?? []) }},
        users: {{ Js::from($users ?? []) }},
        selectedKeterangan: {{ Js::from(collect($item['keterangan'] ?? [])->pluck('label')->values()) }},
        deleteBuktiModal: false,
        selectedBuktiItem: null,
        deleteKeteranganBuktiModal: false,
        selectedKeteranganBukti: { item: null, label: '' },
        editPengawasUserModal: false,
        deletePengawasUserModal: false,
        selectedPengawasUserItem: null,
        selectedPengawasUserAccount: null,
        replacePengawasUserId: '',
        addPengawasModal: false,
        pengawasSearch: '',
        selectedPengawasUserIds: [],
        selectedPengawasUser: null,
        selectedKeteranganOption: null,
        deleteKeteranganOptionModal: false,
        keteranganOptionToDelete: null,
        keteranganModal: { open: false, mode: 'add', value: '', originalValue: '' },
        savingAll: false,
        showToast(message) {
            this.toast.message = message;
            this.toast.show = true;
            if (this.toast.timeoutId) clearTimeout(this.toast.timeoutId);
            this.toast.timeoutId = setTimeout(() => { this.toast.show = false; }, 2200);
        },
        broadcastPengawasUpdate() {
            const payload = {
                pengawas_id: this.project?.pengawas_id,
                pengawas_users: this.project?.pengawas_users || [],
                at: Date.now()
            };
            try {
                localStorage.setItem('list-pengawasan:pengawas-update', JSON.stringify(payload));
            } catch (e) {}
            try {
                if ('BroadcastChannel' in window) {
                    const ch = new BroadcastChannel('list-pengawasan');
                    ch.postMessage({ type: 'pengawas-update', payload });
                    ch.close();
                }
            } catch (e) {}
        },
        openEditPengawasUser(user) {
            if (!this.canWrite || !this.lpPerms.edit_pengawasan) return;
            this.selectedPengawasUserItem = this.project;
            this.selectedPengawasUserAccount = user;
            this.replacePengawasUserId = user?.id || '';
            this.editPengawasUserModal = true;
        },
        closeEditPengawasUser() {
            this.editPengawasUserModal = false;
            this.selectedPengawasUserItem = null;
            this.selectedPengawasUserAccount = null;
            this.replacePengawasUserId = '';
        },
        openDeletePengawasUser(user) {
             if (!this.canWrite || !this.lpPerms.edit_pengawasan) return;
            this.selectedPengawasUserItem = this.project;
            this.selectedPengawasUserAccount = user;
            this.deletePengawasUserModal = true;
        },
        closeDeletePengawasUser() {
            this.deletePengawasUserModal = false;
            this.selectedPengawasUserItem = null;
            this.selectedPengawasUserAccount = null;
        },
        async replacePengawasUser() {
            if (!this.canWrite || !this.lpPerms.edit_pengawasan) return;
            const item = this.selectedPengawasUserItem;
            const account = this.selectedPengawasUserAccount;
            const newUserId = parseInt(this.replacePengawasUserId, 10);
            if (!item || !account || !newUserId) return;
            try {
                const response = await fetch(`/list-pengawasan/${this.project.pengawas_id}/pengawas-users`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ old_user_id: account.id, new_user_id: newUserId })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.project.pengawas_users = data.pengawas_users || [];
                    this.closeEditPengawasUser();
                    this.broadcastPengawasUpdate();
                    this.showToast('Pengawas berhasil diperbarui');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal memperbarui pengawas');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async removePengawasUser() {
            if (!this.canWrite || !this.lpPerms.edit_pengawasan) return;
            const item = this.selectedPengawasUserItem;
            const account = this.selectedPengawasUserAccount;
            if (!item || !account) return;
            try {
                const response = await fetch(`/list-pengawasan/${this.project.pengawas_id}/pengawas-users`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ user_id: account.id })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.project.pengawas_users = data.pengawas_users || [];
                    this.closeDeletePengawasUser();
                    this.broadcastPengawasUpdate();
                    this.showToast('Pengawas berhasil dihapus');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menghapus pengawas');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        openAddPengawas() {
            if (!this.canWrite || !this.lpPerms.tambah_pengawasan) return;
            this.selectedPengawasUserIds = [];
            this.pengawasSearch = '';
            this.addPengawasModal = true;
        },
        closeAddPengawas() {
            this.addPengawasModal = false;
            this.selectedPengawasUserIds = [];
            this.pengawasSearch = '';
        },
        isAssignedPengawas(userId) {
            return (this.project.pengawas_users || []).some(u => u.id === userId);
        },
        async addPengawasUsers() {
            if (!this.canWrite || !this.lpPerms.tambah_pengawasan) return;
            const userIds = (this.selectedPengawasUserIds || [])
                .map(v => parseInt(v, 10))
                .filter(Boolean)
                .filter(id => !this.isAssignedPengawas(id));
            if (userIds.length === 0) return;

            try {
                const response = await fetch(`/list-pengawasan/${this.project.pengawas_id}/pengawas-users`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ user_ids: userIds })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.project.pengawas_users = data.pengawas_users || [];
                    this.closeAddPengawas();
                    this.broadcastPengawasUpdate();
                    this.showToast('Pengawas berhasil ditambahkan');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menambah pengawas');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async saveProject() {
            if (!this.canWrite || !this.lpPerms.tambah_kegiatan) return;
            const payload = {
                nama_kegiatan: this.editProject.nama?.trim() || '',
                deskripsi: this.editProject.deskripsi?.trim() || '',
            };
            if (!payload.nama_kegiatan) {
                this.showToast('Nama kegiatan wajib diisi');
                return;
            }
            try {
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(payload)
                });
                if (response.ok) {
                    this.project.nama = payload.nama_kegiatan;
                    this.project.deskripsi = payload.deskripsi;
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
            const cleaned = (this.selectedKeterangan || [])
                .map(label => (typeof label === 'string' ? label.trim() : ''))
                .filter(Boolean);
            
            try {
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/keterangan`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ keterangan: cleaned })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.project.keterangan = data.keterangan || this.project.keterangan || [];
                    this.selectedKeterangan = (this.project.keterangan || []).map(k => k.label);
                }
            } catch (e) {
                console.error(e);
            }
        },
        async submitAll() {
            if (!this.canWrite) return;
            if (this.savingAll) return;

            const canSaveDetail = !!this.lpPerms.tambah_kegiatan;
            const canSaveKeterangan = !!this.lpPerms.bukti;
            if (!canSaveDetail && !canSaveKeterangan) return;

            const detailPayload = {
                nama_kegiatan: this.editProject.nama?.trim() || '',
                deskripsi: this.editProject.deskripsi?.trim() || '',
            };
            if (canSaveDetail && !detailPayload.nama_kegiatan) {
                this.showToast('Nama kegiatan wajib diisi');
                return;
            }

            this.savingAll = true;
            try {
                if (canSaveDetail) {
                    const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify(detailPayload)
                    });
                    if (!response.ok) {
                        const d = await response.json().catch(() => ({}));
                        this.showToast(d.message || 'Gagal menyimpan perubahan');
                        return;
                    }
                    this.project.nama = detailPayload.nama_kegiatan;
                    this.project.deskripsi = detailPayload.deskripsi;
                }

                if (canSaveKeterangan) {
                    const cleaned = (this.selectedKeterangan || [])
                        .map(label => (typeof label === 'string' ? label.trim() : ''))
                        .filter(Boolean);

                    const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/keterangan`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({ keterangan: cleaned })
                    });
                    if (!response.ok) {
                        const d = await response.json().catch(() => ({}));
                        this.showToast(d.message || 'Gagal menyimpan perubahan');
                        return;
                    }
                    const data = await response.json().catch(() => ({}));
                    this.project.keterangan = data.keterangan || this.project.keterangan || [];
                    this.selectedKeterangan = (this.project.keterangan || []).map(k => k.label);
                }

                this.showToast('Perubahan berhasil disimpan');
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            } finally {
                this.savingAll = false;
            }
        },
        async uploadBukti(file) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            try {
                const formData = new FormData();
                formData.append('bukti', file);
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/bukti`, {
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
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/bukti`, {
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
        hasKeterangan(label) {
            return (this.project.keterangan || []).some(k => k.label === label);
        },
        async uploadKeteranganBukti(label, file) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const formData = new FormData();
            formData.append('label', label);
            formData.append('bukti', file);
            try {
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/keterangan/bukti`, {
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
        async onKeteranganBuktiChange(label, e) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const file = e?.target?.files?.[0];
            if (!file) return;
            if (!this.hasKeterangan(label)) {
                await this.saveKeterangan();
            }
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
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/keterangan/bukti`, {
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
        },
        openKeteranganModal(mode, value = '') {
            if (!this.canWrite) return;
            if (mode === 'add' && !this.lpPerms.tambah_keterangan) return;
            if (mode === 'edit' && !this.lpPerms.edit_keterangan) return;
            this.keteranganModal.mode = mode;
            this.keteranganModal.value = value;
            this.keteranganModal.originalValue = value;
            this.keteranganModal.open = true;
        },
        saveKeteranganOption() {
            if (!this.canWrite) return;
            if (this.keteranganModal.mode === 'add' && !this.lpPerms.tambah_keterangan) return;
            if (this.keteranganModal.mode === 'edit' && !this.lpPerms.edit_keterangan) return;
            const val = this.keteranganModal.value.trim();
            if (!val) return;
            
            if (this.keteranganModal.mode === 'add') {
                if (!this.options.includes(val)) {
                    this.options.push(val);
                }
            } else if (this.keteranganModal.mode === 'edit') {
                const idx = this.options.indexOf(this.keteranganModal.originalValue);
                if (idx !== -1) {
                     // Check if new value already exists to prevent duplicates
                     if (val !== this.keteranganModal.originalValue && this.options.includes(val)) {
                         this.showToast('Keterangan sudah ada');
                         return;
                     }
                    this.options[idx] = val;
                    // Update selection
                    const selIdx = this.selectedKeterangan.indexOf(this.keteranganModal.originalValue);
                    if (selIdx !== -1) this.selectedKeterangan[selIdx] = val;
                    // Update project.keterangan
                    const kIdx = (this.project.keterangan || []).findIndex(k => k.label === this.keteranganModal.originalValue);
                    if (kIdx !== -1) this.project.keterangan[kIdx].label = val;
                }
            }
            this.keteranganModal.open = false;
            this.selectedKeteranganOption = null;
        },
        deleteKeteranganOption() {
             if (!this.canWrite || !this.lpPerms.edit_keterangan || !this.selectedKeteranganOption) return;
             this.keteranganOptionToDelete = this.selectedKeteranganOption;
             this.deleteKeteranganOptionModal = true;
        },
        confirmDeleteKeteranganOption() {
             if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
             const val = this.keteranganOptionToDelete;
             if (!val) return;

             this.options = this.options.filter(o => o !== val);
             this.selectedKeterangan = this.selectedKeterangan.filter(o => o !== val);
             this.project.keterangan = (this.project.keterangan || []).filter(k => k.label !== val);
             
             this.deleteKeteranganOptionModal = false;
             this.keteranganOptionToDelete = null;
             this.selectedKeteranganOption = null;
             this.showToast('Keterangan berhasil dihapus');
        }
    }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 lg:p-8 min-h-[800px] transition-colors duration-300">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('list-pengawasan.kegiatan.index', $item['pengawas_id']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Detail Kegiatan</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="project.nama"></div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Informasi Kegiatan</div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="col-span-1 sm:col-span-2">
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Kegiatan</div>
                            <input x-model="editProject.nama" :disabled="!canWrite || !lpPerms.tambah_kegiatan" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none disabled:opacity-60 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        </div>
                        <div class="col-span-1 sm:col-span-2">
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Deskripsi</div>
                            <textarea x-model="editProject.deskripsi" :disabled="!canWrite || !lpPerms.tambah_kegiatan" rows="3" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none disabled:opacity-60 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"></textarea>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Tanggal Mulai</div>
                            <div class="px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-800 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" x-text="project.tanggal || '-'"></div>
                        </div>
                        <div class="col-span-1 sm:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Pengawas</div>
                                <div class="flex items-center gap-1" x-show="canWrite">
                                     <button type="button" x-show="lpPerms.tambah_pengawasan" @click="openAddPengawas()" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg dark:text-blue-400 dark:hover:bg-blue-900/30" title="Tambah Pengawas">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                     </button>
                                     <button type="button" x-show="lpPerms.edit_pengawasan" :disabled="!selectedPengawasUser" @click="openEditPengawasUser(selectedPengawasUser)" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-30 disabled:cursor-not-allowed dark:text-gray-300 dark:hover:bg-gray-700" title="Edit Pengawas">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                     </button>
                                     <button type="button" x-show="lpPerms.edit_pengawasan" :disabled="!selectedPengawasUser" @click="openDeletePengawasUser(selectedPengawasUser)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg disabled:opacity-30 disabled:cursor-not-allowed dark:text-red-400 dark:hover:bg-red-900/30" title="Hapus Pengawas">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                     </button>
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-2.5 dark:bg-gray-900 dark:border-gray-700">
                                <template x-if="!project.pengawas_users || project.pengawas_users.length === 0">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                </template>
                                <template x-for="u in (project.pengawas_users || [])" :key="`pengawas-kegiatan-${project.id}-${u.id}`">
                                    <div class="flex items-center justify-between gap-3 py-2 px-2 -mx-2 rounded-lg cursor-pointer transition-colors"
                                         :class="selectedPengawasUser && selectedPengawasUser.id === u.id ? 'bg-blue-100 dark:bg-blue-900/40 ring-1 ring-blue-200 dark:ring-blue-800' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                                         @click="selectedPengawasUser = u">
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
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Bukti Kegiatan</div>
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
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Keterangan</div>
                        <div class="flex items-center gap-2" x-show="canWrite && (lpPerms.tambah_keterangan || lpPerms.edit_keterangan)">
                             <button type="button" x-show="lpPerms.tambah_keterangan" @click="openKeteranganModal('add')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors" title="Tambah Keterangan">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                             </button>
                             <button type="button" x-show="lpPerms.edit_keterangan" :disabled="!selectedKeteranganOption" @click="openKeteranganModal('edit', selectedKeteranganOption)" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-30 disabled:cursor-not-allowed dark:text-gray-300 dark:hover:bg-gray-700 transition-colors" title="Edit Keterangan">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                             </button>
                             <button type="button" x-show="lpPerms.edit_keterangan" :disabled="!selectedKeteranganOption" @click="deleteKeteranganOption()" class="p-2 text-red-600 hover:bg-red-50 rounded-lg disabled:opacity-30 disabled:cursor-not-allowed dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Hapus Keterangan">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                             </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        <template x-for="opt in options" :key="`opt-${opt}`">
                            <div class="relative flex flex-col p-4 border rounded-xl shadow-sm transition-all cursor-pointer overflow-hidden"
                                 :class="selectedKeteranganOption === opt ? 'bg-blue-50 border-blue-200 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:border-blue-700 dark:ring-blue-800' : 'bg-white border-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700/50'"
                                 @click="selectedKeteranganOption = (selectedKeteranganOption === opt ? null : opt)">
                                
                                <div class="flex items-center gap-3 mb-3">
                                    <label class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer select-none">
                                        <input type="checkbox" :value="opt" x-model="selectedKeterangan" :disabled="!canWrite || !lpPerms.bukti" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-60 flex-shrink-0" @click.stop>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate leading-snug" x-text="opt"></span>
                                    </label>
                                </div>

                                <div x-show="canWrite && lpPerms.bukti && selectedKeterangan.includes(opt)" class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-700">
                                    <template x-if="!getKeteranganBukti(opt)">
                                        <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors cursor-pointer text-xs w-full justify-center sm:w-auto sm:justify-start" @click.stop>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Upload Bukti
                                            <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="onKeteranganBuktiChange(opt, $event)" />
                                        </label>
                                    </template>
                                    <template x-if="getKeteranganBukti(opt)">
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900" @click.stop>
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="getKeteranganBukti(opt).name"></div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5" x-text="getKeteranganBukti(opt).uploaded_at ? `Diunggah: ${getKeteranganBukti(opt).uploaded_at}` : ''"></div>
                                                </div>
                                                <button type="button" @click="deleteKeteranganBukti(opt)" class="p-1 text-gray-400 hover:text-red-600 dark:text-gray-500 dark:hover:text-red-400 flex-shrink-0" title="Hapus File">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <a class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400" :href="getKeteranganBukti(opt)?.url" target="_blank" rel="noopener">
                                                Lihat File
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" x-show="canWrite" :disabled="savingAll" @click="submitAll()" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors disabled:opacity-60">
                        Submit
                    </button>
                </div>
        </div>

        <div x-show="deleteBuktiModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Bukti Kegiatan</h2>
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

        <div x-show="addPengawasModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800 max-h-[85vh] flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah Pengawas</h2>
                    <button @click="closeAddPengawas()" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto">
                    <div class="rounded-lg border border-blue-100 bg-blue-50/70 p-3 dark:border-blue-900/30 dark:bg-blue-900/20">
                        <div class="text-[11px] font-semibold tracking-wider text-blue-700 uppercase dark:text-blue-300">Kegiatan</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="project?.nama || '-'"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Cari Pengawas</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input x-model="pengawasSearch" type="text" placeholder="Cari nama atau email..." class="w-full pl-10 bg-white border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500 shadow-sm">
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col h-64 bg-white dark:bg-gray-900">
                        <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 dark:border-gray-700 dark:bg-gray-800/60 flex items-center justify-between">
                            <div class="text-sm font-bold text-gray-700 dark:text-gray-200">Daftar Pengawas</div>
                            <div class="px-2 py-0.5 bg-white rounded-md border border-gray-200 text-xs font-semibold text-gray-600 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" x-text="`${(selectedPengawasUserIds || []).length} Dipilih`"></div>
                        </div>
                        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar bg-white dark:bg-gray-900">
                            <template x-for="u in (users || []).filter(u => {
                                const q = (pengawasSearch || '').toLowerCase();
                                const s = `${u.name || ''} ${u.email || ''}`.toLowerCase();
                                return !q || s.includes(q);
                            })" :key="`add-user-${u.id}`">
                                <label class="group flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all border border-transparent hover:bg-blue-50 hover:border-blue-100 dark:hover:bg-blue-900/20 dark:hover:border-blue-800"
                                       :class="isAssignedPengawas(u.id) ? 'opacity-50 grayscale cursor-not-allowed bg-gray-50 dark:bg-gray-800/50' : (selectedPengawasUserIds.includes(u.id.toString()) ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/30 dark:border-blue-700' : '')">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" :value="u.id" x-model="selectedPengawasUserIds" :disabled="isAssignedPengawas(u.id)" 
                                               class="peer w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:text-gray-400">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate group-hover:text-blue-700 dark:group-hover:text-blue-300" x-text="u.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="u.email"></div>
                                    </div>
                                    <template x-if="isAssignedPengawas(u.id)">
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">Terdaftar</span>
                                    </template>
                                </label>
                            </template>
                            
                            <div x-show="(users || []).filter(u => {
                                const q = (pengawasSearch || '').toLowerCase();
                                const s = `${u.name || ''} ${u.email || ''}`.toLowerCase();
                                return !q || s.includes(q);
                            }).length === 0" class="flex flex-col items-center justify-center h-full py-8 text-center text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="text-sm">Tidak ada pengawas ditemukan</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-3">
                    <button @click="closeAddPengawas()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button @click="addPengawasUsers()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                        <span>Tambahkan</span>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="editPengawasUserModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Pengawas</h2>
                    <button @click="closeEditPengawasUser()" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Kegiatan</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="project?.nama || '-'"></div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Pengawas Saat Ini</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="selectedPengawasUserAccount?.name || '-'"></div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="selectedPengawasUserAccount?.email || '-'"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Ganti Pengawas</label>
                        <select x-model="replacePengawasUserId" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">Pilih pengawas baru</option>
                            <template x-for="u in users" :key="`replace-user-${u.id}`">
                                <option :value="u.id" x-text="`${u.name} - ${u.email}`"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="closeEditPengawasUser()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="replacePengawasUser()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="deletePengawasUserModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Pengawas</h2>
                    <button @click="closeDeletePengawasUser()" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/30 dark:bg-red-900/20">
                        <div class="text-[11px] font-semibold tracking-wider text-red-700 uppercase dark:text-red-300">Pengawas</div>
                        <div class="mt-1 text-sm font-semibold text-red-800 dark:text-red-200" x-text="selectedPengawasUserAccount?.name || '-'"></div>
                        <div class="mt-1 text-xs text-red-700 dark:text-red-300" x-text="selectedPengawasUserAccount?.email || '-'"></div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="closeDeletePengawasUser()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="removePengawasUser()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="keteranganModal.open" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white" x-text="keteranganModal.mode === 'add' ? 'Tambah Keterangan' : 'Edit Keterangan'"></h2>
                    <button @click="keteranganModal.open = false" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Label Keterangan</label>
                        <input type="text" x-model="keteranganModal.value" @keydown.enter="saveKeteranganOption()" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" placeholder="Contoh: Dokumen Lingkungan" autofocus>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="keteranganModal.open = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="saveKeteranganOption()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="deleteKeteranganOptionModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Keterangan</h2>
                    <button @click="deleteKeteranganOptionModal = false; keteranganOptionToDelete = null" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/30 dark:bg-red-900/20">
                        <div class="text-[11px] font-semibold tracking-wider text-red-700 uppercase dark:text-red-300">Label Keterangan</div>
                        <div class="mt-1 text-sm font-semibold text-red-800 dark:text-red-200" x-text="keteranganOptionToDelete || '-'"></div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="deleteKeteranganOptionModal = false; keteranganOptionToDelete = null" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmDeleteKeteranganOption()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-[10000]">
            <div class="bg-gray-900 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm" x-text="toast.message"></div>
        </div>
    </div>
</x-dashboard-layout>
