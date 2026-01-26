<x-dashboard-layout title="Detail Proyek" :can-write="$canWrite" :lp-permissions="$lpPermissions">
    <div x-data="{
        canWrite: {{ Js::from($canWrite ?? false) }},
        lpPerms: {{ Js::from($lpPermissions ?? []) }},
        init() {
            window.addEventListener('list-pengawasan:action', (e) => {
                const action = e?.detail?.action || '';
                if (action === 'tambah_proyek') {
                    this.openAddProject();
                    return;
                }
                if (action === 'tambah_keterangan') {
                    if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
                    this.activePanel = 'tambah_keterangan';
                    return;
                }
                if (action === 'edit_keterangan') {
                    if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
                    this.activePanel = 'edit_keterangan';
                }
            });
        },
        sidebarOpen: false,
        activePanel: '',
        toast: { show: false, message: '', timeoutId: null },
        project: {{ Js::from($item) }},
        editProject: { nama: {{ Js::from($item['nama'] ?? '') }}, divisi: {{ Js::from(($item['divisi'] ?? '-') === '-' ? '' : ($item['divisi'] ?? '')) }} },
        options: {{ Js::from($options ?? []) }},
        users: {{ Js::from($users ?? []) }},
        selectedKeterangan: {{ Js::from(collect($item['keterangan'] ?? [])->pluck('label')->values()) }},
        newOptionLabel: '',
        renameOptionModal: false,
        renameOptionOldName: '',
        renameOptionNewName: '',
        deleteOptionModal: false,
        deleteOptionName: '',
        addProjectModal: false,
        newPengawas: { nama: '', divisi: '', status: 'On Progress', deadline: '', keterangan: [], new_keterangan: '', pengawas_users: [] },
        managePengawasUserModal: false,
        selectedPengawasUserItem: null,
        selectedPengawasUserAccount: null,
        replacePengawasUserId: '',
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
        statusMeta(status) {
            if (status === 'OFF') return { label: 'Pending', cls: 'bg-red-600 text-white' };
            if (status === 'Done') return { label: 'Done', cls: 'bg-green-600 text-white' };
            return { label: 'On Progress', cls: 'bg-amber-600 text-white' };
        },
        async saveProject() {
            if (!this.canWrite || !this.lpPerms.nama_proyek) return;
            const payload = {
                nama: this.editProject.nama?.trim() || '',
                divisi: this.editProject.divisi?.trim() || null,
            };
            if (!payload.nama) {
                this.showToast('Nama proyek wajib diisi');
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
                    this.showToast('Detail proyek berhasil diperbarui');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal memperbarui detail proyek');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async setStatus(status) {
            if (!this.canWrite || !this.lpPerms.status) return;
            const previous = this.project.status;
            this.project.status = status;
            try {
                const response = await fetch(`/list-pengawasan/${this.project.id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ status })
                });
                if (!response.ok) {
                    this.project.status = previous;
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal memperbarui status');
                } else {
                    this.showToast('Status berhasil diperbarui');
                }
            } catch (e) {
                this.project.status = previous;
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async saveDeadline() {
            if (!this.canWrite || !this.lpPerms.deadline) return;
            try {
                const response = await fetch(`/list-pengawasan/${this.project.id}/deadline`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ deadline: this.project.deadline || null })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.project.deadline = data.deadline || null;
                    this.project.deadline_display = data.deadline ? data.deadline.split('-').reverse().join('-') : '-';
                    this.showToast('Deadline berhasil diperbarui');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal memperbarui deadline');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async saveKeterangan() {
            if (!this.canWrite || !this.lpPerms.keterangan) return;
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
        addNewKeteranganOption() {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            const label = this.newOptionLabel?.trim();
            if (!label) return;
            if (!this.options.includes(label)) this.options.push(label);
            if (!this.selectedKeterangan.includes(label)) this.selectedKeterangan.push(label);
            this.newOptionLabel = '';
            this.saveKeterangan();
        },
        openRenameOption(name) {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            if (!name) return;
            this.renameOptionOldName = name;
            this.renameOptionNewName = name;
            this.renameOptionModal = true;
        },
        replaceKeteranganLabel(oldName, newName) {
            this.options = this.options.map(o => (o === oldName ? newName : o));
            this.selectedKeterangan = this.selectedKeterangan.map(o => (o === oldName ? newName : o));
            this.project.keterangan = (this.project.keterangan || []).map(o => (o.label === oldName ? { ...o, label: newName } : o));
        },
        removeKeteranganLabel(name) {
            this.options = this.options.filter(o => o !== name);
            this.selectedKeterangan = this.selectedKeterangan.filter(o => o !== name);
            this.project.keterangan = (this.project.keterangan || []).filter(o => o.label !== name);
        },
        async confirmRenameOption() {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            const oldName = this.renameOptionOldName;
            const newName = this.renameOptionNewName?.trim() || '';
            if (!oldName || !newName) return;
            if (newName === oldName) {
                this.renameOptionModal = false;
                this.renameOptionOldName = '';
                this.renameOptionNewName = '';
                return;
            }
            try {
                const response = await fetch('/list-pengawasan/keterangan/rename', {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ old_name: oldName, new_name: newName })
                });
                if (response.ok) {
                    this.replaceKeteranganLabel(oldName, newName);
                    this.renameOptionModal = false;
                    this.renameOptionOldName = '';
                    this.renameOptionNewName = '';
                    this.showToast('Nama keterangan berhasil diubah');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal mengubah nama keterangan');
                    this.renameOptionModal = false;
                }
            } catch (e) {
                console.error(e);
                this.renameOptionModal = false;
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        openDeleteOption(name) {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            if (!name) return;
            this.deleteOptionName = name;
            this.deleteOptionModal = true;
        },
        async confirmDeleteOption() {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            const name = this.deleteOptionName;
            if (!name) return;
            try {
                const response = await fetch('/list-pengawasan/keterangan', {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ name })
                });
                if (response.ok || response.status === 404) {
                    this.removeKeteranganLabel(name);
                    this.deleteOptionName = '';
                    this.deleteOptionModal = false;
                    this.showToast('Opsi keterangan berhasil dihapus');
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menghapus opsi keterangan');
                    this.deleteOptionModal = false;
                }
            } catch (e) {
                console.error(e);
                this.deleteOptionModal = false;
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        openAddProject() {
            if (!this.canWrite || !this.lpPerms.tambah_proyek) return;
            this.newPengawas = { nama: '', divisi: '', status: 'On Progress', deadline: '', keterangan: [], new_keterangan: '', pengawas_users: [] };
            this.addProjectModal = true;
        },
        addNewKeteranganToForm() {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            const label = this.newPengawas.new_keterangan?.trim();
            if (!label) return;
            if (!this.options.includes(label)) this.options.push(label);
            if (!this.newPengawas.keterangan.includes(label)) this.newPengawas.keterangan.push(label);
            this.newPengawas.new_keterangan = '';
        },
        async savePengawas() {
            if (!this.canWrite || !this.lpPerms.tambah_proyek) return;
            try {
                const response = await fetch('/list-pengawasan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        nama: this.newPengawas.nama,
                        divisi: this.newPengawas.divisi || null,
                        status: this.newPengawas.status,
                        deadline: this.newPengawas.deadline || null,
                        keterangan: this.newPengawas.keterangan,
                        pengawas_users: this.newPengawas.pengawas_users
                    })
                });
                if (response.ok) {
                    const data = await response.json();
                    this.addProjectModal = false;
                    window.location.href = `/list-pengawasan/${data.id}`;
                } else {
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menambah proyek');
                }
            } catch (e) {
                console.error(e);
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        openManagePengawasUser(user) {
            if (!this.canWrite || !this.lpPerms.pengawas) return;
            this.selectedPengawasUserItem = this.project;
            this.selectedPengawasUserAccount = user;
            this.replacePengawasUserId = user?.id || '';
            this.managePengawasUserModal = true;
        },
        closeManagePengawasUser() {
            this.managePengawasUserModal = false;
            this.selectedPengawasUserItem = null;
            this.selectedPengawasUserAccount = null;
            this.replacePengawasUserId = '';
        },
        async replacePengawasUser() {
            if (!this.canWrite || !this.lpPerms.pengawas) return;
            const item = this.selectedPengawasUserItem;
            const account = this.selectedPengawasUserAccount;
            const newUserId = parseInt(this.replacePengawasUserId, 10);
            if (!item || !account || !newUserId) return;
            try {
                const response = await fetch(`/list-pengawasan/${item.id}/pengawas-users`, {
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
                    this.closeManagePengawasUser();
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
            if (!this.canWrite || !this.lpPerms.pengawas) return;
            const item = this.selectedPengawasUserItem;
            const account = this.selectedPengawasUserAccount;
            if (!item || !account) return;
            try {
                const response = await fetch(`/list-pengawasan/${item.id}/pengawas-users`, {
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
                    this.closeManagePengawasUser();
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
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Informasi Proyek</div>
                        <button type="button" x-show="canWrite && lpPerms.nama_proyek" @click="saveProject()" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Proyek</div>
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
                                            <template x-if="canWrite && lpPerms.pengawas">
                                                <button type="button" class="text-xs text-blue-600 hover:underline dark:text-blue-400 truncate" @click="openManagePengawasUser(u)" x-text="u.email"></button>
                                            </template>
                                            <template x-if="!canWrite || !lpPerms.pengawas">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="u.email"></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Deadline</div>
                            <button type="button" x-show="canWrite && lpPerms.deadline" @click="saveDeadline()" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
                        </div>
                        <input type="date" x-model="project.deadline" :disabled="!canWrite || !lpPerms.deadline" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none disabled:opacity-60 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Tampilan: <span class="font-semibold" x-text="project.deadline_display || '-'"></span></div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Status</div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" :disabled="!canWrite || !lpPerms.status" @click="setStatus('OFF')" class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-60" :class="project.status === 'OFF' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600'">Pending</button>
                            <button type="button" :disabled="!canWrite || !lpPerms.status" @click="setStatus('On Progress')" class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-60" :class="project.status === 'On Progress' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600'">On Progress</button>
                            <button type="button" :disabled="!canWrite || !lpPerms.status" @click="setStatus('Done')" class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-60" :class="project.status === 'Done' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600'">Done</button>
                        </div>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold" :class="statusMeta(project.status).cls" x-text="statusMeta(project.status).label"></span>
                        </div>
                    </div>
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
                        <button type="button" x-show="canWrite && lpPerms.keterangan" @click="saveKeterangan()" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="opt in options" :key="`opt-${opt}`">
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-3 dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <input type="checkbox" :value="opt" x-model="selectedKeterangan" :disabled="!canWrite || !lpPerms.keterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-60">
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

        <div x-show="activePanel === 'tambah_keterangan'" class="fixed inset-x-0 bottom-0 top-[80px] z-[60] flex items-center justify-end bg-black/40 backdrop-blur-sm" style="display: none;">
            <div class="w-full max-w-md h-full bg-white dark:bg-gray-800 shadow-2xl p-6 overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-bold text-gray-900 dark:text-white">Tambah Keterangan</div>
                    <button type="button" class="text-gray-500 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white" @click="activePanel = ''">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <div class="text-sm text-gray-600 dark:text-gray-300">Tambah opsi keterangan baru lalu otomatis dimasukkan ke proyek ini.</div>
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Keterangan yang tersedia</div>
                        <button type="button" x-show="canWrite && lpPerms.keterangan" @click="saveKeterangan()" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="opt in options" :key="`opt-panel-${opt}`">
                            <label class="flex items-center p-2.5 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-3 dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <input type="checkbox" :value="opt" x-model="selectedKeterangan" :disabled="!canWrite || !lpPerms.keterangan" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-60">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="opt"></span>
                            </label>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <input x-model="newOptionLabel" type="text" placeholder="Nama keterangan baru" class="flex-1 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                        <button type="button" @click="addNewKeteranganOption()" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">Tambah</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activePanel === 'edit_keterangan'" class="fixed inset-x-0 bottom-0 top-[80px] z-[60] flex items-center justify-end bg-black/40 backdrop-blur-sm" style="display: none;">
            <div class="w-full max-w-md h-full bg-white dark:bg-gray-800 shadow-2xl p-6 overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-bold text-gray-900 dark:text-white">Edit Keterangan</div>
                    <button type="button" class="text-gray-500 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white" @click="activePanel = ''">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="opt in options" :key="`opt-edit-${opt}`">
                        <div class="flex items-center justify-between gap-2 p-3 border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 hover:border-blue-200 transition-colors dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="opt"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openRenameOption(opt)" class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-8.25 8.25a1 1 0 01-.414.263l-3 1a1 1 0 01-1.263-1.263l1-3a1 1 0 01.263-.414l8.25-8.25z" />
                                        <path d="M12.293 5.293l2.414 2.414" />
                                    </svg>
                                </button>
                                <button type="button" @click="openDeleteOption(opt)" class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 text-red-700 hover:bg-red-50 transition-colors dark:bg-gray-900 dark:border-gray-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                        <path fill-rule="evenodd" d="M6 8a1 1 0 011 1v7a1 1 0 11-2 0V9a1 1 0 011-1zm4 1a1 1 0 10-2 0v7a1 1 0 102 0V9zm3-4a1 1 0 00-1-1H8a1 1 0 00-1 1v1H4a1 1 0 100 2h1v10a2 2 0 002 2h6a2 2 0 002-2V8h1a1 1 0 100-2h-3V5z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="addProjectModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[640px] shadow-2xl transform transition-all dark:bg-gray-800 max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah Proyek</h2>
                    <button @click="addProjectModal = false" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Nama Proyek</label>
                        <input x-model="newPengawas.nama" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Divisi</label>
                        <input x-model="newPengawas.divisi" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Pengawas</label>
                        <select multiple x-model="newPengawas.pengawas_users" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <template x-for="u in users" :key="`new-pengawas-${u.id}`">
                                <option :value="u.id" x-text="`${u.name} - ${u.email}`"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Deadline</label>
                        <input x-model="newPengawas.deadline" type="date" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Status</label>
                        <div class="grid grid-cols-3 gap-2 bg-gray-50 border border-gray-200 rounded-lg p-2 dark:bg-gray-900 dark:border-gray-700">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-md transition-colors w-full" :class="newPengawas.status === 'OFF' ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800'" @click="newPengawas.status = 'OFF'">Pending</button>
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-md transition-colors w-full" :class="newPengawas.status === 'On Progress' ? 'bg-amber-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800'" @click="newPengawas.status = 'On Progress'">On Progress</button>
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-md transition-colors w-full" :class="newPengawas.status === 'Done' ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800'" @click="newPengawas.status = 'Done'">Done</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Keterangan</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="opt in options" :key="`new-opt-${opt}`">
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-3 dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                    <input type="checkbox" :value="opt" x-model="newPengawas.keterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="opt"></span>
                                </label>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 mt-3" x-show="canWrite && lpPerms.edit_keterangan">
                            <input x-model="newPengawas.new_keterangan" type="text" placeholder="Tambah keterangan baru" class="flex-1 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                            <button type="button" @click="addNewKeteranganToForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">Tambah</button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="addProjectModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="savePengawas()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="managePengawasUserModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800 max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Kelola Pengawas</h2>
                    <button @click="closeManagePengawasUser()" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Proyek</div>
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
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <button @click="removePengawasUser()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">Hapus Pengawas</button>
                        <div class="flex items-center gap-3">
                            <button @click="closeManagePengawasUser()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                            <button @click="replacePengawasUser()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="renameOptionModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Ubah Nama Keterangan</h2>
                    <button @click="renameOptionModal = false; renameOptionOldName=''; renameOptionNewName=''" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Nama Lama</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="renameOptionOldName"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Nama Baru</label>
                        <input x-model="renameOptionNewName" type="text" class="w-full bg-white border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="renameOptionModal = false; renameOptionOldName=''; renameOptionNewName=''" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmRenameOption()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="deleteOptionModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Keterangan</h2>
                    <button @click="deleteOptionModal = false; deleteOptionName=''" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/30 dark:bg-red-900/20">
                        <div class="text-[11px] font-semibold tracking-wider text-red-700 uppercase dark:text-red-300">Keterangan</div>
                        <div class="mt-1 text-sm font-semibold text-red-800 dark:text-red-200" x-text="deleteOptionName"></div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="deleteOptionModal = false; deleteOptionName=''" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmDeleteOption()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="deleteBuktiModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
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

        <div x-show="deleteKeteranganBuktiModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
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

        <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-[90]">
            <div class="bg-gray-900 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm" x-text="toast.message"></div>
        </div>
    </div>
</x-dashboard-layout>
