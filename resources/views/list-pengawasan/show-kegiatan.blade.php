<x-dashboard-layout title="Detail Kegiatan" :can-write="$canWrite" :lp-permissions="$lpPermissions">
    <div x-data="{
        canWrite: {{ Js::from($canWrite ?? false) }},
        lpPerms: {{ Js::from($lpPermissions ?? []) }},
        init() {
            window.addEventListener('list-pengawasan:action', (e) => {
                const action = e?.detail?.action || '';
                if (action === 'tambah_keterangan') {
                    if (!this.canWrite) return;
                    if (!this.lpPerms.keterangan_checklist && !this.lpPerms.tambah_keterangan && !this.lpPerms.edit_keterangan) return;
                    this.activePanel = 'tambah_keterangan';
                    return;
                }
                if (action === 'edit_keterangan') {
                    if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
                    this.activePanel = 'edit_keterangan';
                    return;
                }
                if (action === 'tambah_pengawas') {
                    if (!this.canWrite || !this.lpPerms.tambah_pengawasan) return;
                    this.openAddPengawas();
                    return;
                }
                if (action === 'kelola_pengawas') {
                    if (!this.canWrite || !this.lpPerms.edit_pengawasan) return;
                    const first = (this.project.pengawas_users || [])[0] || null;
                    if (first) this.openManagePengawasUser(first);
                }
            });

            try {
                const params = new URLSearchParams(window.location.search || '');
                const action = params.get('action') || '';
                if (action === 'tambah_keterangan') {
                    if (this.canWrite && (this.lpPerms.keterangan_checklist || this.lpPerms.tambah_keterangan || this.lpPerms.edit_keterangan)) {
                        this.activePanel = 'tambah_keterangan';
                    }
                } else if (action === 'edit_keterangan') {
                    if (this.canWrite && this.lpPerms.edit_keterangan) {
                        this.activePanel = 'edit_keterangan';
                    }
                }
                if (action === 'tambah_keterangan' || action === 'edit_keterangan') {
                    params.delete('action');
                    const qs = params.toString();
                    const nextUrl = window.location.pathname + (qs ? `?${qs}` : '') + window.location.hash;
                    window.history.replaceState({}, '', nextUrl);
                }
            } catch (e) {}
        },
        sidebarOpen: false,
        activePanel: '',
        toast: { show: false, message: '', timeoutId: null },
        project: {{ Js::from($item) }},
        editProject: { nama: {{ Js::from($item['nama'] ?? '') }}, deskripsi: {{ Js::from($item['deskripsi'] ?? '') }} },
        options: {{ Js::from($options ?? []) }},
        users: {{ Js::from($users ?? []) }},
        selectedKeterangan: {{ Js::from(collect($item['keterangan'] ?? [])->pluck('label')->values()) }},
        saveKeteranganRequestId: 0,
        newOptionLabel: '',
        renameOptionModal: false,
        renameOptionOldName: '',
        renameOptionNewName: '',
        deleteOptionModal: false,
        deleteOptionName: '',
        deleteBuktiModal: false,
        selectedBuktiItem: null,
        deleteKeteranganBuktiModal: false,
        selectedKeteranganBukti: { item: null, label: '' },
        managePengawasUserModal: false,
        selectedPengawasUserItem: null,
        selectedPengawasUserAccount: null,
        replacePengawasUserId: '',
        addPengawasModal: false,
        pengawasSearch: '',
        selectedPengawasUserIds: [],
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
        statusMeta(status) {
            if (status === 'Selesai') return { label: 'Selesai', cls: 'bg-green-600 text-white' };
            if (status === 'Sedang Berjalan') return { label: 'Sedang Berjalan', cls: 'bg-blue-600 text-white' };
            if (status === 'Terlambat') return { label: 'Terlambat', cls: 'bg-red-600 text-white' };
            return { label: 'Belum Dimulai', cls: 'bg-gray-500 text-white' };
        },
        isLateKegiatan() {
            const deadline = this.project?.deadline || null;
            const status = this.project?.status || '';
            if (!deadline) return false;
            if (status === 'Selesai') return false;
            const today = new Date().toISOString().slice(0, 10);
            return deadline < today;
        },
        openManagePengawasUser(user) {
            if (!this.canWrite || !this.lpPerms.edit_pengawasan) return;
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
                    this.closeManagePengawasUser();
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
                    this.closeManagePengawasUser();
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
        async setStatus(status) {
            if (!this.canWrite || !this.lpPerms.status) return;
            const previous = this.project.status;
            this.project.status = status;
            try {
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/status`, {
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
                const response = await fetch(`/list-pengawasan/kegiatan/${this.project.id}/deadline`, {
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
            if (!this.canWrite || !this.lpPerms.keterangan_checklist) return;
            try {
                const cleaned = (this.selectedKeterangan || [])
                    .map(label => (typeof label === 'string' ? label.trim() : ''))
                    .filter(Boolean);
                const requestId = ++this.saveKeteranganRequestId;

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
                    if (requestId === this.saveKeteranganRequestId) {
                        this.project.keterangan = data.keterangan || [];
                        this.selectedKeterangan = (this.project.keterangan || []).map(k => k.label);
                    }
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
        async addNewKeteranganOption() {
            if (!this.canWrite) return;
            if (!this.lpPerms.tambah_keterangan && !this.lpPerms.edit_keterangan) return;
            const label = this.newOptionLabel?.trim();
            if (!label) return;

            if (!this.options.includes(label)) {
                try {
                    const response = await fetch('/list-pengawasan/keterangan', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({ name: label })
                    });
                    if (!response.ok) {
                        const d = await response.json().catch(() => ({}));
                        this.showToast(d.message || 'Gagal menambah keterangan');
                        return;
                    }
                    this.options.push(label);
                } catch (e) {
                    console.error(e);
                    this.showToast('Terjadi kesalahan sistem');
                    return;
                }
            }

            if (!this.selectedKeterangan.includes(label)) this.selectedKeterangan.push(label);
            this.newOptionLabel = '';
            if (this.lpPerms.keterangan_checklist) {
                this.saveKeterangan();
            } else {
                this.showToast('Keterangan berhasil ditambahkan');
            }
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
                        <button type="button" x-show="canWrite && lpPerms.tambah_kegiatan" @click="saveProject()" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
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
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Pengawas</div>
                            <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-2.5 dark:bg-gray-900 dark:border-gray-700">
                                <template x-if="!project.pengawas_users || project.pengawas_users.length === 0">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                </template>
                                <template x-for="u in (project.pengawas_users || [])" :key="`pengawas-kegiatan-${project.id}-${u.id}`">
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
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Status Kegiatan</div>
                        <div class="flex flex-wrap gap-2">
                             <button type="button" :disabled="!canWrite || !lpPerms.status" @click="setStatus('Belum Dimulai')" class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-60" :class="project.status === 'Belum Dimulai' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600'">Belum Dimulai</button>
                            <button type="button" :disabled="!canWrite || !lpPerms.status" @click="setStatus('Sedang Berjalan')" class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-60" :class="project.status === 'Sedang Berjalan' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600'">Sedang Berjalan</button>
                             <button type="button" :disabled="!canWrite || !lpPerms.status" @click="setStatus('Terlambat')" class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-60" :class="project.status === 'Terlambat' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600'">Terlambat</button>
                            <button type="button" :disabled="!canWrite || !lpPerms.status" @click="setStatus('Selesai')" class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-60" :class="project.status === 'Selesai' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600'">Selesai</button>
                        </div>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold" :class="statusMeta(project.status).cls" x-text="statusMeta(project.status).label"></span>
                            <template x-if="isLateKegiatan()">
                                <span class="ml-2 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 border border-red-100 dark:bg-red-900/20 dark:text-red-300 dark:border-red-900/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.59c.75 1.334-.214 2.99-1.742 2.99H3.48c-1.528 0-2.492-1.656-1.742-2.99l6.52-11.59ZM10 8a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 8Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                                    </svg>
                                    Terlambat
                                </span>
                            </template>
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
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Keterangan</div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="opt in options" :key="`opt-${opt}`">
                            <div class="flex items-center justify-between gap-3 p-3 border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 hover:border-blue-200 transition-colors dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <label class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer">
                                    <input type="checkbox" :value="opt" x-model="selectedKeterangan" @change="saveKeterangan()" :disabled="!canWrite || !lpPerms.keterangan_checklist" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-60">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" x-text="opt"></span>
                                </label>
                                <div class="flex items-center gap-2 flex-shrink-0" x-show="canWrite && lpPerms.keterangan_checklist && selectedKeterangan.includes(opt)">
                                    <template x-if="!getKeteranganBukti(opt)">
                                        <label class="px-3 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors cursor-pointer text-sm">
                                            Upload
                                            <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="onKeteranganBuktiChange(opt, $event)" />
                                        </label>
                                    </template>
                                    <template x-if="getKeteranganBukti(opt)">
                                        <div class="flex items-center gap-2">
                                            <a class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-800 font-semibold hover:bg-gray-50 transition-colors text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-700" :href="getKeteranganBukti(opt)?.url" target="_blank" rel="noopener">Lihat</a>
                                            <button type="button" @click="deleteKeteranganBukti(opt)" class="px-3 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors text-sm">Hapus</button>
                                        </div>
                                    </template>
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
                    <div class="text-sm text-gray-600 dark:text-gray-300">Tambah opsi keterangan baru lalu otomatis dimasukkan ke kegiatan ini.</div>
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Keterangan yang tersedia</div>
                        <button type="button" x-show="canWrite && lpPerms.keterangan_checklist" @click="saveKeterangan()" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="opt in options" :key="`opt-panel-${opt}`">
                            <label class="flex items-center p-2.5 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors gap-3 dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <input type="checkbox" :value="opt" x-model="selectedKeterangan" :disabled="!canWrite || !lpPerms.keterangan_checklist" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-60">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="opt"></span>
                            </label>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <input x-model="newOptionLabel" :disabled="!canWrite || (!lpPerms.tambah_keterangan && !lpPerms.edit_keterangan)" type="text" placeholder="Nama keterangan baru" class="flex-1 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none disabled:opacity-60 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                        <button type="button" @click="addNewKeteranganOption()" :disabled="!canWrite || (!lpPerms.tambah_keterangan && !lpPerms.edit_keterangan)" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold disabled:opacity-60">Tambah</button>
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

        <div x-show="addPengawasModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
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

        <div x-show="managePengawasUserModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[520px] shadow-2xl transform transition-all dark:bg-gray-800">
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

        <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-[90]">
            <div class="bg-gray-900 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm" x-text="toast.message"></div>
        </div>
    </div>
</x-dashboard-layout>
