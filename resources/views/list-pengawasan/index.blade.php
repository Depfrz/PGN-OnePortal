<x-dashboard-layout title="List Pengawasan" :can-write="$canWrite" :lp-permissions="$lpPermissions">
    <div x-data="{
        canWrite: {{ Js::from($canWrite ?? false) }},
        lpPerms: {{ Js::from($lpPermissions ?? []) }},
        init() {
            window.addEventListener('list-pengawasan:action', (e) => {
                const action = e?.detail?.action || '';
                if (action === 'tambah_proyek') {
                    this.openAdd();
                }
            });

            window.addEventListener('storage', (e) => {
                if (e.key !== 'list-pengawasan:pengawas-update') return;
                if (!e.newValue) return;
                try {
                    const payload = JSON.parse(e.newValue);
                    this.applyPengawasUpdate(payload);
                } catch (err) {}
            });

            try {
                if ('BroadcastChannel' in window) {
                    const ch = new BroadcastChannel('list-pengawasan');
                    ch.onmessage = (ev) => {
                        const msg = ev?.data;
                        if (!msg || msg.type !== 'pengawas-update') return;
                        this.applyPengawasUpdate(msg.payload);
                    };
                    this._lpChannel = ch;
                }
            } catch (err) {}

            try {
                const last = localStorage.getItem('list-pengawasan:pengawas-update');
                if (last) this.applyPengawasUpdate(JSON.parse(last));
            } catch (err) {}
        },
        applyPengawasUpdate(payload) {
            const pengawasId = payload?.pengawas_id;
            if (!pengawasId) return;
            const item = (this.items || []).find(it => it.id === pengawasId);
            if (!item) return;
            item.pengawas_users = payload?.pengawas_users || [];
        },
        closeAllOverlays() {
            this.closeAdd();
            this.closeKeteranganMenu();
            this.editKeteranganModal = false;
            this.deleteModal = false;
            this.deleteBuktiModal = false;
            this.deleteKeteranganBuktiModal = false;
            this.deleteOptionModal = false;
            this.renameOptionModal = false;
            this.saveKeteranganConfirmModal = false;
        },
        search: '',
        statusFilter: 'all',
        sortBy: 'created_desc',
        addModal: false,
        editKeteranganModal: false,
        deleteModal: false,
        deleteBuktiModal: false,
        deleteKeteranganBuktiModal: false,
        deleteOptionModal: false,
        renameOptionModal: false,
        saveKeteranganConfirmModal: false,
        selectedPengawas: null,
        selectedKeterangan: [],
        selectedKeteranganBukti: { item: null, label: '' },
        newLabel: '',
        deleteOptionName: '',
        renameOptionOldName: '',
        renameOptionNewName: '',
        toast: { show: false, message: '', timeoutId: null },
        editingId: null,
        editPengawas: { nama: '' },
        editingDeadlineId: null,
        editDeadline: '',
        statusMenu: { open: false, x: 0, y: 0, item: null },
        keteranganMenu: { open: false, x: 0, y: 0, item: null },
        newPengawas: { nama: '', deskripsi: '', tanggal: '', deadline: '', pengawas_users: [] },
        selectedBuktiItem: null,
        items: {{ Js::from($items) }},
        options: {{ Js::from($options) }},
        users: {{ Js::from($users ?? []) }},
        openAdd() {
            if (!this.canWrite || !this.lpPerms.tambah_proyek) return;
            this.newPengawas = { nama: '', deskripsi: '', tanggal: '', deadline: '', pengawas_users: [] };
            this.addModal = true;
            document.body.style.overflow = 'hidden';
        },
        closeAdd() {
            this.addModal = false;
            document.body.style.overflow = '';
        },
        showToast(message) {
            this.toast.message = message;
            this.toast.show = true;
            if (this.toast.timeoutId) clearTimeout(this.toast.timeoutId);
            this.toast.timeoutId = setTimeout(() => { this.toast.show = false; }, 2200);
        },
        async savePengawas() {
            if (!this.canWrite || !this.lpPerms.tambah_proyek) return;
            try {
                const response = await fetch('/list-pengawasan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        nama: this.newPengawas.nama,
                        deskripsi: this.newPengawas.deskripsi,
                        tanggal: this.newPengawas.tanggal || null,
                        deadline: this.newPengawas.deadline || null,
                        pengawas_users: this.newPengawas.pengawas_users
                    })
                });
                if (response.ok) {
                    const data = await response.json();
                    const deadlineDisplay = data.deadline ? data.deadline.split('-').reverse().join('-') : '-';
                    this.items.unshift({
                        id: data.id,
                        nama: this.newPengawas.nama,
                        created_at: new Date().toISOString(),
                        tanggal: data.tanggal || '-',
                        deadline: data.deadline || null,
                        deadline_display: deadlineDisplay,
                        status: data.status || 'On Progress',
                        keterangan: [],
                        pengawas_users: data.pengawas_users || [],
                        bukti: { path: null, name: null, mime: null, size: null, uploaded_at: null, url: null }
                    });
                    this.closeAdd();
                    this.showToast('Proyek berhasil ditambahkan');
                } else {
                    alert('Gagal menambah proyek');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        async setStatus() {},
        statusMeta(status) {
            if (status === 'OFF') return { label: 'Pending', cls: 'bg-red-600 text-white hover:bg-red-700' };
            if (status === 'Done') return { label: 'Done', cls: 'bg-green-600 text-white hover:bg-green-700' };
            return { label: 'On Progress', cls: 'bg-amber-600 text-white hover:bg-amber-700' };
        },
        isLateProject(item) {
            const deadline = item?.deadline || null;
            const status = item?.status || '';
            if (!deadline) return false;
            if (status === 'Done') return false;
            const today = new Date().toISOString().slice(0, 10);
            return deadline < today;
        },
        openStatusMenu(e, item) {
            if (!this.canWrite || !this.lpPerms.status) return;
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
                openKeteranganMenu(e, item) {
                    if (!this.canWrite) return;
                    this.closeStatusMenu();
                    
                    const rect = e.currentTarget.getBoundingClientRect();
                    const menuWidth = 320; 
                    const menuHeight = 300; 

                    // Align right of button
                    let x = rect.right - menuWidth;
                    let y = rect.bottom + 4;

                    if (x < 12) x = 12;
                    const maxX = window.innerWidth - menuWidth - 12;
                    if (x > maxX) x = maxX;

                    const maxY = window.innerHeight - menuHeight - 12;
                    if (y > maxY) y = rect.top - menuHeight - 8;
                    if (y < 12) y = 12;

                    this.keteranganMenu = { open: true, x, y, item };
                    document.body.style.overflow = 'hidden'; 
                },
                closeKeteranganMenu() {
                    this.keteranganMenu = { open: false, x: 0, y: 0, item: null };
                    document.body.style.overflow = '';
                },
        startEdit(item) {
            if (!this.canWrite || !this.lpPerms.nama_proyek) return;
            this.editingId = item.id;
            this.editPengawas = { nama: item.nama };
        },
        cancelEdit() {
            this.editingId = null;
            this.editPengawas = { nama: '' };
        },
        async saveEdit(item) {
            if (!this.canWrite || !this.lpPerms.nama_proyek) return;
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
            if (!this.canWrite || !this.lpPerms.keterangan) return;
            this.selectedPengawas = JSON.parse(JSON.stringify(item));
            this.selectedKeterangan = (item.keterangan || []).map(k => k.label);
            this.newLabel = '';
            this.editKeteranganModal = true;
        },
        replaceKeteranganLabel(oldName, newName) {
            this.options = this.options.map(o => (o === oldName ? newName : o));
            this.selectedKeterangan = this.selectedKeterangan.map(o => (o === oldName ? newName : o));
            this.items = this.items.map(it => ({
                ...it,
                keterangan: (it.keterangan || []).map(o => (o.label === oldName ? { ...o, label: newName } : o))
            }));
        },
        removeKeteranganLabel(name) {
            this.options = this.options.filter(o => o !== name);
            this.selectedKeterangan = this.selectedKeterangan.filter(o => o !== name);
            this.items = this.items.map(it => ({
                ...it,
                keterangan: (it.keterangan || []).filter(o => o.label !== name)
            }));
        },
        openRenameOption(name) {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            if (!name) return;
            this.renameOptionOldName = name;
            this.renameOptionNewName = name;
            this.renameOptionModal = true;
        },
        async confirmRenameOption() {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            const oldName = this.renameOptionOldName;
            const newName = this.renameOptionNewName?.trim() || '';
            if (!oldName) return;
            if (!newName) return;
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
                    if (response.status === 403) {
                        this.renameOptionModal = false;
                        this.showToast('Anda tidak punya akses untuk mengubah opsi');
                        return;
                    }
                    if (response.status === 419) {
                        this.renameOptionModal = false;
                        this.showToast('Sesi habis. Silakan refresh halaman lalu coba lagi');
                        return;
                    }

                    const contentType = response.headers.get('content-type') || '';
                    let message = 'Gagal mengubah nama keterangan';
                    if (contentType.includes('application/json')) {
                        const d = await response.json().catch(() => ({}));
                        message = d.message || message;
                    } else {
                        const t = await response.text().catch(() => '');
                        if (t) message = 'Gagal mengubah nama keterangan';
                    }
                    this.renameOptionModal = false;
                    this.showToast(message);
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
                if (response.ok) {
                    this.removeKeteranganLabel(name);
                    this.deleteOptionName = '';
                    this.deleteOptionModal = false;
                    this.showToast('Opsi keterangan berhasil dihapus');
                } else {
                    if (response.status === 404) {
                        this.removeKeteranganLabel(name);
                        this.deleteOptionName = '';
                        this.deleteOptionModal = false;
                        this.showToast('Opsi keterangan dihapus dari daftar');
                        return;
                    }
                    if (response.status === 403) {
                        this.deleteOptionModal = false;
                        this.showToast('Anda tidak punya akses untuk menghapus opsi');
                        return;
                    }
                    if (response.status === 419) {
                        this.deleteOptionModal = false;
                        this.showToast('Sesi habis. Silakan refresh halaman lalu coba lagi');
                        return;
                    }

                    const contentType = response.headers.get('content-type') || '';
                    let message = 'Gagal menghapus opsi keterangan';
                    if (contentType.includes('application/json')) {
                        const d = await response.json().catch(() => ({}));
                        message = d.message || message;
                    } else {
                        const t = await response.text().catch(() => '');
                        if (t) message = 'Gagal menghapus opsi keterangan';
                    }
                    this.deleteOptionModal = false;
                    this.showToast(message);
                }
            } catch (e) {
                console.error(e);
                this.deleteOptionModal = false;
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        addNewKeteranganToEdit() {
            if (!this.canWrite || !this.lpPerms.edit_keterangan) return;
            const label = this.newLabel?.trim();
            if (!label) return;
            if (!this.options.includes(label)) this.options.push(label);
            if (!this.selectedKeterangan.includes(label)) this.selectedKeterangan.push(label);
            this.newLabel = '';
        },
        hasKeterangan(item, label) {
            if (!item || !item.keterangan) return false;
            return item.keterangan.some(k => k.label === label);
        },
        getKeteranganBukti(item, label) {
            if (!item || !item.keterangan) return null;
            const found = item.keterangan.find(k => k.label === label);
            return found ? found.bukti : null;
        },
        async uploadKeteranganBukti(item, label, file) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const formData = new FormData();
            formData.append('label', label);
            formData.append('bukti', file);
            try {
                const response = await fetch(`/list-pengawasan/${item.id}/keterangan/bukti`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: formData
                });
                if (response.ok) {
                    const data = await response.json();
                    const idx = item.keterangan.findIndex(k => k.label === label);
                    if (idx !== -1) {
                        item.keterangan[idx].bukti = data.bukti;
                    }
                    this.showToast('Bukti keterangan berhasil diunggah');
                } else {
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal mengunggah bukti');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        deleteKeteranganBukti(item, label) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            this.selectedKeteranganBukti = { item, label };
            this.deleteKeteranganBuktiModal = true;
        },
        async confirmDeleteKeteranganBukti() {
            const { item, label } = this.selectedKeteranganBukti;
            if (!item || !label) return;
            try {
                const response = await fetch(`/list-pengawasan/${item.id}/keterangan/bukti`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ label })
                });
                if (response.ok) {
                    const idx = item.keterangan.findIndex(k => k.label === label);
                    if (idx !== -1) {
                        item.keterangan[idx].bukti = null;
                    }
                    this.showToast('Bukti keterangan berhasil dihapus');
                    this.deleteKeteranganBuktiModal = false;
                    this.selectedKeteranganBukti = { item: null, label: '' };
                } else {
                    alert('Gagal menghapus bukti');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
        onKeteranganBuktiChange(item, label, e) {
            const file = e.target.files[0];
            if (file) this.uploadKeteranganBukti(item, label, file);
            e.target.value = '';
        },
        async toggleKeteranganFromTable(item, label) {
            if (!this.canWrite || !this.lpPerms.keterangan) return;
            const currentLabels = (item.keterangan || []).map(k => k.label);
            const exists = currentLabels.includes(label);
            const nextLabels = exists ? currentLabels.filter(l => l !== label) : [...currentLabels, label];

            const previous = JSON.parse(JSON.stringify(item.keterangan || []));

            if (exists) {
                 item.keterangan = item.keterangan.filter(k => k.label !== label);
            } else {
                 item.keterangan.push({ label: label, bukti: null });
            }

            try {
                const response = await fetch(`/list-pengawasan/${item.id}/keterangan`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ keterangan: nextLabels })
                });
                if (response.ok) {
                    const data = await response.json();
                    item.keterangan = data.keterangan;
                    this.showToast('Keterangan diperbarui');
                } else {
                    item.keterangan = previous;
                    const d = await response.json().catch(() => ({}));
                    this.showToast(d.message || 'Gagal menyimpan keterangan');
                }
            } catch (e) {
                console.error(e);
                item.keterangan = previous;
                this.showToast('Terjadi kesalahan sistem');
            }
        },
        async saveKeterangan() {
            if (!this.canWrite) return;
            const cleaned = (this.selectedKeterangan || [])
                .map(label => (typeof label === 'string' ? label.trim() : ''))
                .filter(Boolean);
            try {
                const response = await fetch(`/list-pengawasan/${this.selectedPengawas.id}/keterangan`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ keterangan: cleaned })
                });
                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    const updated = data.keterangan || []; // Now array of objects
                    this.items = this.items.map(i => (i.id === this.selectedPengawas.id
                        ? { ...i, keterangan: updated }
                        : i));
                    if (this.selectedPengawas) {
                        this.selectedPengawas.keterangan = updated;
                    }
                    // Update selectedKeterangan (which is strings) for the modal
                    this.selectedKeterangan = updated.map(k => k.label);
                    this.editKeteranganModal = false;
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
        openSaveKeteranganConfirm() {
            if (!this.canWrite) return;
            this.saveKeteranganConfirmModal = true;
        },
        async confirmSaveKeterangan() {
            if (!this.canWrite || !this.lpPerms.keterangan) return;
            this.saveKeteranganConfirmModal = false;
            await this.saveKeterangan();
        },
        openDelete(item) {
            if (!this.canWrite || !this.lpPerms.nama_proyek) return;
            this.selectedPengawas = item;
            this.deleteModal = true;
        },
        async deletePengawas() {
            if (!this.canWrite || !this.lpPerms.nama_proyek) return;
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
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const el = document.getElementById(`bukti-input-${item.id}`);
            if (el) el.click();
        },
        get filteredItems() {
            let data = this.items.slice();

            if (this.statusFilter !== 'all') {
                data = data.filter(i => {
                    if (this.statusFilter === 'pending') return i.status === 'OFF';
                    if (this.statusFilter === 'on_progress') return i.status === 'On Progress';
                    if (this.statusFilter === 'done') return i.status === 'Done';
                    return true;
                });
            }

            if (this.search) {
                const q = this.search.toLowerCase();
                data = data.filter(i => i.nama.toLowerCase().includes(q));
            }

            data.sort((a, b) => {
                if (this.sortBy === 'created_desc') {
                    return (b.created_at || '').localeCompare(a.created_at || '');
                }
                if (this.sortBy === 'created_asc') {
                    return (a.created_at || '').localeCompare(b.created_at || '');
                }
                if (this.sortBy === 'deadline_asc') {
                    return (a.deadline || '').localeCompare(b.deadline || '');
                }
                if (this.sortBy === 'deadline_desc') {
                    return (b.deadline || '').localeCompare(a.deadline || '');
                }
                return 0;
            });

            return data;
        },
        async uploadBukti(item, file) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
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
            if (!this.canWrite || !this.lpPerms.bukti) return;
            const file = e?.target?.files?.[0];
            if (!file) return;
            this.uploadBukti(item, file);
            e.target.value = '';
        },
        openDeleteBukti(item) {
            if (!this.canWrite || !this.lpPerms.bukti) return;
            this.selectedBuktiItem = item;
            this.deleteBuktiModal = true;
        },
        async confirmDeleteBukti() {
            if (!this.canWrite || !this.lpPerms.bukti) return;
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
        startEditDeadline(item) {
            if (!this.canWrite || !this.lpPerms.deadline) return;
            this.editingDeadlineId = item.id;
            this.editDeadline = item.deadline || '';
        },
        cancelEditDeadline() {
            this.editingDeadlineId = null;
            this.editDeadline = '';
        },
        async saveDeadline(item) {
            if (!this.canWrite) return;
            try {
                const response = await fetch(`/list-pengawasan/${item.id}/deadline`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ deadline: this.editDeadline || null })
                });
                if (response.ok) {
                    const data = await response.json();
                    item.deadline = data.deadline || null;
                    item.deadline_display = data.deadline ? data.deadline.split('-').reverse().join('-') : '-';
                    this.cancelEditDeadline();
                    this.showToast('Deadline berhasil diperbarui');
                } else {
                    const d = await response.json().catch(() => ({}));
                    alert(d.message || 'Gagal memperbarui deadline');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan sistem');
            }
        },
    }" class="p-4 sm:p-6">
        <div x-effect="if (addModal) { closeKeteranganMenu(); }"></div>
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
            <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center justify-end gap-3">
                <div class="relative w-full sm:w-[340px]">
                    <input x-model="search" type="text" placeholder="Cari Proyek..." class="w-full bg-[#f7f8f9] border border-[#d6d9de] rounded-2xl pl-6 pr-12 py-3 text-base text-gray-800 placeholder:text-[#6f7a86] shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-400">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-[#6f7a86] dark:text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.3 6.3a7.5 7.5 0 0 0 10.35 10.35Z" />
                        </svg>
                    </div>
                </div>
                <select x-model="statusFilter" class="w-full sm:w-auto bg-[#f7f8f9] border border-[#d6d9de] rounded-xl px-7 py-3 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
                    <option value="all">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="on_progress">On Progress</option>
                    <option value="done">Done</option>
                </select>
                <select x-model="sortBy" class="w-full sm:w-auto bg-[#f7f8f9] border border-[#d6d9de] rounded-xl px-4 py-3 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
                    <option value="created_desc">Terbaru</option>
                    <option value="created_asc">Terlama</option>
                    <option value="deadline_asc">Deadline Terdekat</option>
                    <option value="deadline_desc">Deadline Terjauh</option>
                </select>
                <button x-show="false" @click="openAdd()" class="w-full sm:w-auto bg-blue-600 text-white font-medium text-sm py-2.5 px-6 rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg inline-flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Proyek
                </button>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 sm:p-6 sm:overflow-x-auto border border-gray-100 dark:border-gray-700">
            <template x-if="items.length === 0">
                <div class="py-14">
                    <div class="mx-auto max-w-md text-center">
                        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8">
                                <path fill-rule="evenodd" d="M7.5 6A4.5 4.5 0 0 1 12 1.5 4.5 4.5 0 0 1 16.5 6v.75h.75A3.75 3.75 0 0 1 21 10.5v7.5A3.75 3.75 0 0 1 17.25 21.75H6.75A3.75 3.75 0 0 1 3 18v-7.5A3.75 3.75 0 0 1 6.75 6.75h.75V6Zm7.5.75V6A3 3 0 0 0 12 3a3 3 0 0 0-3 3v.75h6Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-base font-semibold text-gray-900 dark:text-gray-100">Belum ada proyek.</div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Klik nama proyek untuk melihat detail.</div>
                    </div>
                </div>
            </template>

            <template x-if="items.length > 0">
                <div>
                    <div class="space-y-3 sm:hidden">
                        <template x-for="item in filteredItems" :key="'mobile-' + item.id">
                            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <template x-if="editingId === item.id">
                                            <input x-model="editPengawas.nama" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                                        </template>
                                        <template x-if="editingId !== item.id">
                                            <a :href="'/list-pengawasan/' + item.id + '/kegiatan'" class="text-gray-900 font-semibold text-base dark:text-white truncate hover:underline" x-text="item.nama"></a>
                                        </template>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="item.tanggal"></div>
                                        <div class="mt-3">
                                            <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Pengawas</div>
                                            <div class="mt-2 space-y-2">
                                                <template x-if="!item.pengawas_users || item.pengawas_users.length === 0">
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">-</div>
                                                </template>
                                                <template x-for="u in item.pengawas_users" :key="`mobile-pengawas-${item.id}-${u.id}`">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate" x-text="u.name"></div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="u.email"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 flex-shrink-0" x-show="false">
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

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Deadline</div>
                                        <div class="mt-2">
                                            <template x-if="editingDeadlineId === item.id">
                                                <div class="flex items-center gap-2">
                                                    <input type="date" x-model="editDeadline" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-2 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" />
                                                    <button @click="saveDeadline(item)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors dark:hover:bg-green-900/20" title="Simpan Deadline">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                            <path fill-rule="evenodd" d="M16.704 4.294a.75.75 0 01.002 1.06l-8.25 8.25a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 011.06-1.06l3.22 3.22 7.72-7.72a.75.75 0 011.058 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <button @click="cancelEditDeadline()" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20" title="Batal">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="editingDeadlineId !== item.id">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate" x-text="item.deadline_display || '-'"></span>
                                                    <button x-show="false" type="button" @click="startEditDeadline(item)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-gray-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20" title="Edit Deadline">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                            <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Status</div>
                                        <div class="mt-2">
                                            <div class="inline-flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs font-bold shadow-sm transition-colors whitespace-nowrap" :class="statusMeta(item.status).cls">
                                                <span class="truncate" x-text="statusMeta(item.status).label"></span>
                                            </div>
                                            <template x-if="isLateProject(item)">
                                                <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 border border-red-100 dark:bg-red-900/20 dark:text-red-300 dark:border-red-900/30">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.59c.75 1.334-.214 2.99-1.742 2.99H3.48c-1.528 0-2.492-1.656-1.742-2.99l6.52-11.59ZM10 8a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 8Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                                                    </svg>
                                                    Terlambat
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Kegiatan Selesai</div>
                                    <div class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-left transition-colors dark:border-gray-700 dark:bg-gray-900">
                                        <template x-if="!item.kegiatan_selesai || item.kegiatan_selesai.length === 0">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">-</span>
                                        </template>
                                        <template x-if="item.kegiatan_selesai && item.kegiatan_selesai.length > 0">
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="(k, idx) in item.kegiatan_selesai.slice(0, 3)" :key="k.id + '-' + idx">
                                                    <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" x-text="k.nama"></span>
                                                </template>
                                                <template x-if="item.kegiatan_selesai.length > 3">
                                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-900/40" x-text="'+' + (item.kegiatan_selesai.length - 3)"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">Bukti</div>
                                    <div class="mt-2">
                                        <div x-show="canWrite" class="flex items-center justify-between gap-3">
                                            <input type="file" class="hidden" :id="`bukti-input-mobile-${item.id}`" accept="image/png,image/jpeg,application/pdf" @change="onBuktiChange(item, $event)">

                                            <template x-if="!item.bukti || !item.bukti.url">
                                                <button type="button" @click="(() => { const el = document.getElementById(`bukti-input-mobile-${item.id}`); if (el) el.click(); })()" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                                    Upload
                                                </button>
                                            </template>

                                            <template x-if="item.bukti && item.bukti.url">
                                                <div class="min-w-0 flex-1">
                                                    <a :href="item.bukti.url" target="_blank" class="min-w-0 flex items-center gap-2">
                                                        <template x-if="isImage(item.bukti.mime)">
                                                            <img :src="item.bukti.url" alt="Bukti" class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700 flex-shrink-0">
                                                        </template>
                                                        <template x-if="!isImage(item.bukti.mime)">
                                                            <div class="w-10 h-10 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center text-red-600 dark:bg-red-900/20 dark:border-red-900/30 dark:text-red-300 flex-shrink-0">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                                    <path fill-rule="evenodd" d="M6 2.25A2.25 2.25 0 0 0 3.75 4.5v15A2.25 2.25 0 0 0 6 21.75h12A2.25 2.25 0 0 0 20.25 19.5V8.56a2.25 2.25 0 0 0-.659-1.591l-3.56-3.56A2.25 2.25 0 0 0 14.44 2.25H6Zm7.5 1.5V7.5a.75.75 0 0 0 .75.75h3.75l-4.5-4.5Z" clip-rule="evenodd" />
                                                                </svg>
                                                            </div>
                                                        </template>
                                                        <div class="min-w-0">
                                                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate" x-text="item.bukti.name"></div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="item.bukti.size ? formatSize(item.bukti.size) : ''"></div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="hidden sm:block">
                        <table class="w-full border-separate border-spacing-y-3">
                            <thead>
                                <tr class="text-left">
                                    <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider pl-4">Nama Proyek</th>
                                    <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[180px] pr-4">Pengawas</th>
                                    <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[130px] pr-4">Tanggal Mulai</th>
                                    <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[140px] pr-4">Deadline</th>
                                    <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[100px] pr-4">Status</th>
                                    <th class="pb-2 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[220px] pr-4">Kegiatan Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in filteredItems" :key="item.id">
                                    <tr class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 group">
                                <td class="p-4 rounded-l-lg border-y border-l border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <template x-if="editingId === item.id">
                                                <input x-model="editPengawas.nama" type="text" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                                            </template>
                                            <template x-if="editingId !== item.id">
                                                <a :href="'/list-pengawasan/' + item.id + '/kegiatan'" class="text-gray-900 font-semibold text-base dark:text-white truncate hover:underline" x-text="item.nama"></a>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-1 flex-shrink-0" x-show="false">
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
                                </td>

                                <td class="p-4 border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div class="space-y-2">
                                        <template x-if="!item.pengawas_users || item.pengawas_users.length === 0">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                        </template>
                                        <template x-for="u in item.pengawas_users" :key="`pengawas-${item.id}-${u.id}`">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate" x-text="u.name"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="u.email"></div>
                                            </div>
                                        </template>
                                    </div>
                                </td>

                                <td class="p-4 text-sm text-gray-700 dark:text-gray-300 border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors" x-text="item.tanggal"></td>

                                <td class="p-4 border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <template x-if="editingDeadlineId === item.id">
                                        <div class="flex items-center gap-1">
                                            <input type="date" x-model="editDeadline" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                                            <button @click="saveDeadline(item)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors dark:hover:bg-green-900/20" title="Simpan Deadline">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                    <path fill-rule="evenodd" d="M16.704 4.294a.75.75 0 01.002 1.06l-8.25 8.25a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 011.06-1.06l3.22 3.22 7.72-7.72a.75.75 0 011.058 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <button @click="cancelEditDeadline()" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20" title="Batal">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="editingDeadlineId !== item.id">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-gray-700 text-sm dark:text-gray-300 truncate" x-text="item.deadline_display || '-'"></span>
                                            <button x-show="false" type="button" @click="startEditDeadline(item)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:text-gray-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20" title="Edit Deadline">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                    <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.2z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </td>

                                <td class="p-4 border-y border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div class="inline-flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs font-bold shadow-sm transition-colors whitespace-nowrap" :class="statusMeta(item.status).cls">
                                        <span class="truncate" x-text="statusMeta(item.status).label"></span>
                                    </div>
                                    <template x-if="isLateProject(item)">
                                        <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 border border-red-100 dark:bg-red-900/20 dark:text-red-300 dark:border-red-900/30">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.59c.75 1.334-.214 2.99-1.742 2.99H3.48c-1.528 0-2.492-1.656-1.742-2.99l6.52-11.59ZM10 8a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 8Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                                            </svg>
                                            Terlambat
                                        </div>
                                    </template>
                                </td>

                                <td class="p-4 rounded-r-lg border-y border-r border-gray-200 dark:border-gray-700 group-hover:border-blue-300 dark:group-hover:border-blue-700 transition-colors">
                                    <div class="w-full rounded-2xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-left text-sm text-blue-700 flex items-start justify-between gap-3 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-200">
                                        <div class="flex-1 min-w-0">
                                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-blue-500/80 mb-0.5">Kegiatan Selesai</span>
                                            <span class="block text-xs sm:text-sm font-medium whitespace-normal leading-snug" x-text="(item.kegiatan_selesai && item.kegiatan_selesai.length) ? item.kegiatan_selesai.map(k => k.nama).join(', ') : '-'"></span>
                                        </div>
                                    </div>
                                </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
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
                    <button type="button" class="w-full text-left px-4 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed flex items-center gap-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                        Pending
                    </button>
                    <button type="button" class="w-full text-left px-4 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed flex items-center gap-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-600"></span>
                        On Progress
                    </button>
                    <button type="button" class="w-full text-left px-4 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed flex items-center gap-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span>
                        Done
                    </button>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div
                x-show="keteranganMenu.open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[9999]"
                style="display: none;"
            >
                <div class="absolute inset-0" @click="closeKeteranganMenu()"></div>
                <div
                    class="fixed w-72 sm:w-80 rounded-xl border border-gray-100 bg-white shadow-xl ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800"
                    :style="`left:${keteranganMenu.x}px; top:${keteranganMenu.y}px;`"
                >
                    <div class="max-h-64 overflow-y-auto py-2">
                        <template x-for="opt in options" :key="'opt-menu-' + (keteranganMenu.item?.id || 0) + '-' + opt">
                            <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 group">
                                <button
                                    type="button"
                                    class="flex-1 text-left text-xs sm:text-sm"
                                    @click="toggleKeteranganFromTable(keteranganMenu.item, opt)"
                                    :disabled="!canWrite"
                                >
                                    <div
                                        class="flex items-center gap-3 rounded-lg border border-transparent px-1 py-0.5"
                                        :class="hasKeterangan(keteranganMenu.item, opt)
                                            ? 'bg-blue-50 border-blue-100 dark:bg-blue-900/20 dark:border-blue-900/40'
                                            : ''"
                                    >
                                        <div
                                            class="h-5 w-5 rounded-md flex items-center justify-center flex-shrink-0"
                                            :class="hasKeterangan(keteranganMenu.item, opt)
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white border border-gray-300 text-transparent dark:bg-gray-800 dark:border-gray-600'"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                                <path fill-rule="evenodd" d="M16.704 4.294a.75.75 0 01.002 1.06l-8.25 8.25a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 011.06-1.06l3.22 3.22 7.72-7.72a.75.75 0 011.058 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <span class="truncate text-gray-800 dark:text-gray-100" x-text="opt"></span>
                                    </div>
                                </button>

                                <template x-if="hasKeterangan(keteranganMenu.item, opt)">
                                    <div class="flex items-center gap-1 ml-2">
                                        <template x-if="!getKeteranganBukti(keteranganMenu.item, opt)">
                                            <label class="cursor-pointer p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Upload Bukti Keterangan">
                                                <input type="file" class="hidden" @change="onKeteranganBuktiChange(keteranganMenu.item, opt, $event)" accept="image/png,image/jpeg,application/pdf">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                    <path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 011.06 0l4.5 4.5a.75.75 0 01-1.06 1.06l-3.22-3.22V16.5a.75.75 0 01-1.5 0V4.81L8.03 8.03a.75.75 0 01-1.06-1.06l4.5-4.5zM3 15.75a.75.75 0 01.75.75v2.25a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5V16.5a.75.75 0 011.5 0v2.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V16.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                                                </svg>
                                            </label>
                                        </template>
                                        <template x-if="getKeteranganBukti(keteranganMenu.item, opt)">
                                            <div class="flex items-center gap-1">
                                                <a :href="getKeteranganBukti(keteranganMenu.item, opt).url" target="_blank" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Lihat Bukti">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.766 1.766 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                                <button @click="deleteKeteranganBukti(keteranganMenu.item, opt)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Bukti">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                        <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.49 1.478 47.429 47.429 0 00-3.89-.514V13.5a1.5 1.5 0 01-3 0V6.18c-1.316.12-2.614.28-3.89.514a.75.75 0 11-.49-1.478 48.83 48.83 0 013.878-.512V4.478a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="border-t border-gray-100 dark:border-gray-700 px-3 py-2 flex justify-end" x-show="canWrite && lpPerms.keterangan">
                        <button
                            type="button"
                            class="text-[11px] font-medium text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-300 dark:hover:text-blue-200"
                            @click="openEditKeterangan(keteranganMenu.item); closeKeteranganMenu()"
                        >
                            Edit keterangan
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="addModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed inset-y-0 right-0 z-[90] w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl overflow-y-auto border-l border-gray-200 dark:border-gray-700" 
                 style="display: none;">
                <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah Proyek</h2>
                    <button @click="closeAdd()" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Deskripsi</label>
                        <textarea x-model="newPengawas.deskripsi" rows="3" placeholder="Masukkan deskripsi proyek" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Pengawas</label>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <template x-if="newPengawas.pengawas_users.length === 0">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Belum ada pengawas dipilih</span>
                            </template>
                            <template x-for="u in users" :key="`selected-user-${u.id}`">
                                <template x-if="newPengawas.pengawas_users.includes(u.id)">
                                    <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" x-text="u.email"></span>
                                </template>
                            </template>
                        </div>
                        <div class="grid grid-cols-1 gap-3 max-h-48 overflow-y-auto pr-1">
                            <template x-for="u in users" :key="`select-user-${u.id}`">
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-colors dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                    <input type="checkbox" :value="u.id" x-model="newPengawas.pengawas_users" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" x-text="u.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="u.email"></div>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Tanggal Mulai</label>
                            <input x-model="newPengawas.tanggal" type="date" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">Deadline</label>
                            <input x-model="newPengawas.deadline" type="date" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-8 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button @click="closeAdd()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="savePengawas()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
            </div>
        </template>

        <!-- Backdrop for Sidebar -->
        <template x-teleport="body">
            <div x-show="addModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeAdd()"
                 class="fixed inset-0 z-[85] bg-black/50 backdrop-blur-sm"
                 style="display: none;">
            </div>
        </template>

        <!-- Edit Keterangan Modal -->
        <div x-show="editKeteranganModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[560px] shadow-2xl transform transition-all dark:bg-gray-800 max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Keterangan</h2>
                    <button @click="editKeteranganModal = false" class="text-gray-400 hover:text-gray-600 transition-colors dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="opt in options" :key="opt">
                            <div class="flex items-center justify-between gap-2 p-3 border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 hover:border-blue-200 transition-colors dark:border-gray-700 dark:hover:bg-blue-900/20 dark:hover:border-blue-800">
                                <label class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer">
                                    <input type="checkbox" :value="opt" x-model="selectedKeterangan" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" x-text="opt"></span>
                                </label>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button type="button" @click.stop="openRenameOption(opt)" class="h-8 w-8 sm:h-9 sm:w-9 inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700" :disabled="!canWrite" :class="!canWrite ? 'opacity-60 cursor-not-allowed' : ''">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 sm:h-5 sm:w-5">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-8.25 8.25a1 1 0 01-.414.263l-3 1a1 1 0 01-1.263-1.263l1-3a1 1 0 01.263-.414l8.25-8.25z" />
                                            <path d="M12.293 5.293l2.414 2.414" />
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="openDeleteOption(opt)" class="h-8 w-8 sm:h-9 sm:w-9 inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 text-red-700 hover:bg-red-50 transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-red-300 dark:hover:bg-red-900/20" :disabled="!canWrite" :class="!canWrite ? 'opacity-60 cursor-not-allowed' : ''">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 sm:h-5 sm:w-5">
                                            <path fill-rule="evenodd" d="M6 8a1 1 0 011 1v7a1 1 0 11-2 0V9a1 1 0 011-1zm4 1a1 1 0 10-2 0v7a1 1 0 102 0V9zm3-4a1 1 0 00-1-1H8a1 1 0 00-1 1v1H4a1 1 0 100 2h1v10a2 2 0 002 2h6a2 2 0 002-2V8h1a1 1 0 100-2h-3V5z" clip-rule="evenodd" />
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
                    <div class="flex items-center justify-between mt-6">
                        <div class="flex items-center space-x-3">
                            <button @click="editKeteranganModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                            <button @click="openSaveKeteranganConfirm()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="renameOptionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 dark:bg-blue-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-6 w-6 text-blue-700 dark:text-blue-200">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-8.25 8.25a1 1 0 01-.414.263l-3 1a1 1 0 01-1.263-1.263l1-3a1 1 0 01.263-.414l8.25-8.25z" />
                            <path d="M12.293 5.293l2.414 2.414" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Ubah Nama Keterangan</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400" x-text="renameOptionOldName"></p>
                    </div>
                </div>
                <div class="space-y-5">
                    <input x-model="renameOptionNewName" type="text" class="w-full bg-white border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="renameOptionModal = false; renameOptionOldName = ''; renameOptionNewName = ''" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmRenameOption()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="deleteOptionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 dark:bg-red-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Opsi Keterangan</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Opsi ini akan dihapus dari daftar.</p>
                    </div>
                </div>
                <div class="space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus opsi keterangan berikut?</p>
                    <div class="bg-red-50 border border-red-100 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-900/40">
                        <div class="font-semibold text-gray-900 dark:text-white" x-text="deleteOptionName"></div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="deleteOptionModal = false; deleteOptionName = ''" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmDeleteOption()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="saveKeteranganConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 dark:bg-blue-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-6 w-6 text-blue-700 dark:text-blue-200">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v4.25c0 .414.336.75.75.75h2.5a.75.75 0 000-1.5h-1.75v-3.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Simpan Perubahan</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">Pastikan pilihan keterangan sudah sesuai.</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="saveKeteranganConfirmModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button @click="confirmSaveKeterangan()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md hover:shadow-lg transition-all">OK, Simpan</button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
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

        <div x-show="deleteBuktiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
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

        <div x-show="deleteKeteranganBuktiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity" style="display: none;">
            <div class="bg-white rounded-xl p-5 sm:p-6 w-[92vw] max-w-[480px] shadow-2xl transform transition-all dark:bg-gray-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 dark:bg-red-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Hapus Bukti Keterangan</h2>
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">File bukti akan dihapus dari sistem.</p>
                    </div>
                </div>
                <div class="space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus bukti untuk keterangan berikut?</p>
                    <div class="bg-red-50 border border-red-100 rounded-lg p-4 dark:bg-red-900/20 dark:border-red-900/40">
                        <div class="font-semibold text-gray-900 dark:text-white" x-text="selectedKeteranganBukti?.label"></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedKeteranganBukti?.item?.nama"></div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="deleteKeteranganBuktiModal = false; selectedKeteranganBukti = { item: null, label: '' }" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                        <button @click="confirmDeleteKeteranganBukti()" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md hover:shadow-lg transition-all">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
