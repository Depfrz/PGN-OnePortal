<x-buku-saku-layout>
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800">Upload Dokumen</h2>
    </div>

    <form action="{{ route('buku-saku.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        
        <!-- File Upload -->
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">File Dokumen</label>
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6 text-center cursor-pointer hover:bg-gray-50 transition-colors relative">
                <input type="file" name="file" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-50" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                
                <div id="emptyState">
                    <div class="mx-auto h-10 w-10 text-gray-400 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <p class="text-base text-blue-600 font-medium"><span class="hover:underline">Klik untuk upload</span> <span class="text-gray-600 font-normal">atau drag & drop</span></p>
                    <p class="text-sm text-gray-500 mt-1">PDF, Word (DOC/DOCX) hingga 30MB</p>
                </div>

                <div id="fileState" class="hidden">
                     <div class="mx-auto h-10 w-10 text-blue-500 bg-blue-50 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p id="fileNameDisplay" class="text-base text-gray-800 font-medium truncate max-w-xs mx-auto"></p>
                    <p id="fileSizeDisplay" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
        </div>

        <!-- Title (Auto-filled but editable) -->
        <div class="mb-6">
            <label for="titleInput" class="block text-base font-bold text-gray-700 mb-2">Judul Dokumen</label>
            <input type="text" name="title" id="titleInput" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 text-base" required>
        </div>

        <!-- Valid Until -->
        <div class="mb-6">
            <label for="validUntilInput" class="block text-base font-bold text-gray-700 mb-2">Tanggal Mulai Berlaku</label>
            <div class="relative">
                <input type="date" name="valid_until" id="validUntilInput" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 text-base cursor-pointer" placeholder="Pilih tanggal">
            </div>
            <p class="text-sm text-gray-500 mt-1">Dokumen akan berlaku selama 5 tahun dari tanggal ini.</p>
        </div>

        <style>
            /* Hide the default calendar icon */
            input[type="date"]::-webkit-calendar-picker-indicator {
                background: transparent;
                bottom: 0;
                color: transparent;
                cursor: pointer;
                height: auto;
                left: 0;
                position: absolute;
                right: 0;
                top: 0;
                width: auto;
            }
        </style>

        <!-- Tags (Checklist) -->
        <div class="mb-6">
            <label class="block text-base font-bold text-gray-700 mb-2">Tags</label>
            
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <!-- Toolbar (Search + Add) -->
                <div class="bg-gray-50 p-4 border-b border-gray-200 flex flex-col md:flex-row gap-4">
                    <!-- Search Box -->
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                             <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" id="searchTagInput" class="w-full rounded-md border-gray-300 pl-10 text-base focus:ring-blue-500 focus:border-blue-500" placeholder="Cari tags...">
                    </div>
                    <!-- Add New Box -->
                    <div class="flex-1 flex gap-2">
                        <input type="text" id="newTagInput" class="w-full rounded-md border-gray-300 text-base focus:ring-blue-500 focus:border-blue-500" placeholder="Tags baru...">
                        <button type="button" id="addTagBtn" class="bg-blue-600 text-white px-5 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center min-w-[48px] shadow-sm" title="Tambah Tag">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Grid Container -->
                <div id="tagsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-80 overflow-y-auto p-4 bg-white">
                    @foreach($availableTags as $tag)
                    <label class="relative flex items-center p-3 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-200 cursor-pointer transition-all tag-item group bg-white">
                        <input type="checkbox" name="tags[]" value="{{ $tag->name }}" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 mr-3">
                        <span class="text-base text-gray-700 font-medium flex-1 tag-name truncate">{{ $tag->name }}</span>
                        <button type="button" class="text-gray-400 hover:text-red-500 delete-tag-btn p-1 ml-2 z-10" data-id="{{ $tag->id }}" title="Hapus Tag">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="mb-8">
            <label for="description" class="block text-base font-bold text-gray-700 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" id="description" rows="4" placeholder="Tambahkan keterangan singkat tentang dokumen ini..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-4 text-base"></textarea>
        </div>

        <!-- Submit -->
         <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition-colors text-sm">
                Upload Dokumen
            </button>
        </div>
    </form>

    <!-- Delete Confirmation Modal -->
    <div id="deleteTagModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden transition-opacity">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 transform transition-all scale-100">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Kategori?</h3>
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus kategori ini secara permanen?</p>
                <div class="flex justify-center gap-3">
                    <button type="button" id="cancelDeleteBtn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">
                        Batal
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-sm transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastNotification" class="fixed top-5 right-5 z-[9999] transform transition-all duration-300 translate-x-full opacity-0 pointer-events-none">
        <div class="flex items-center gap-3 rounded-2xl bg-white border border-gray-100 shadow-2xl px-4 py-3">
             <div id="toastIcon" class="flex h-9 w-9 items-center justify-center rounded-xl">
                 <!-- Icon injected via JS -->
             </div>
             <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast Logic
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toastNotification');
                const iconContainer = document.getElementById('toastIcon');
                const msgElement = document.getElementById('toastMessage');

                // Reset classes
                toast.classList.remove('translate-x-full', 'opacity-0', 'pointer-events-none');
                
                // Set content based on type
                if (type === 'success') {
                    iconContainer.className = 'flex h-9 w-9 items-center justify-center rounded-xl bg-green-100 text-green-700';
                    iconContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M16.704 4.294a.75.75 0 01.002 1.06l-8.25 8.25a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 011.06-1.06l3.22 3.22 7.72-7.72a.75.75 0 011.058 0z" clip-rule="evenodd" /></svg>`;
                } else if (type === 'info') {
                    iconContainer.className = 'flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-700';
                    iconContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>`;
                } else {
                    iconContainer.className = 'flex h-9 w-9 items-center justify-center rounded-xl bg-red-100 text-red-700';
                    iconContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>`;
                }

                msgElement.textContent = message;

                // Hide after 3 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full', 'opacity-0', 'pointer-events-none');
                }, 3000);
            }

            // Upload Progress UI
            const uploadForm = document.getElementById('uploadForm');
            if (uploadForm) {
                uploadForm.addEventListener('submit', function() {
                    const btn = this.querySelector('button[type="submit"]');
                    
                    // Disable button
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                    
                    // Add spinner and change text
                    btn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sedang Mengupload...
                    `;
                    
                    // Show Toast
                    showToast('Sedang memproses upload, mohon tunggu...', 'info');
                });
            }

            // Tag Management
            const tagsContainer = document.getElementById('tagsContainer');
            const addTagBtn = document.getElementById('addTagBtn');
            const newTagInput = document.getElementById('newTagInput');
            const searchTagInput = document.getElementById('searchTagInput');

            // Search Filter
            searchTagInput.addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase();
                const items = tagsContainer.querySelectorAll('.tag-item');
                
                items.forEach(item => {
                    const name = item.querySelector('.tag-name').textContent.toLowerCase();
                    if (name.includes(keyword)) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });

            // Add Tag
            addTagBtn.addEventListener('click', function() {
                const name = newTagInput.value.trim();
                if (!name) return;

                fetch('{{ route("buku-saku.tags.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name: name })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.id) {
                        // Append new tag
                        const label = document.createElement('label');
                        label.className = 'relative flex items-center p-3 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-200 cursor-pointer transition-all tag-item group bg-white';
                        label.innerHTML = `
                            <input type="checkbox" name="tags[]" value="${data.name}" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 mr-3" checked>
                            <span class="text-sm text-gray-700 font-medium flex-1 tag-name truncate">${data.name}</span>
                            
                            <button type="button" class="text-gray-400 hover:text-red-500 delete-tag-btn p-1 ml-2 z-10" data-id="${data.id}" title="Hapus Tag">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        `;
                        // Insert at top
                        tagsContainer.insertBefore(label, tagsContainer.firstChild);
                        newTagInput.value = '';
                        attachDeleteEvent(label.querySelector('.delete-tag-btn'));
                        showToast('Kategori berhasil ditambahkan');
                    } else {
                        showToast('Gagal menambah tag. Mungkin sudah ada.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Terjadi kesalahan saat menambah tag.', 'error');
                });
            });

            // Delete Tag Modal Logic
            const deleteModal = document.getElementById('deleteTagModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            let tagToDeleteId = null;
            let tagToDeleteElement = null;

            function showDeleteModal(id, element) {
                tagToDeleteId = id;
                tagToDeleteElement = element;
                deleteModal.classList.remove('hidden');
                // Simple animation
                setTimeout(() => {
                    deleteModal.firstElementChild.classList.remove('scale-95', 'opacity-0');
                    deleteModal.firstElementChild.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function hideDeleteModal() {
                deleteModal.classList.add('hidden');
                tagToDeleteId = null;
                tagToDeleteElement = null;
            }

            cancelDeleteBtn.addEventListener('click', hideDeleteModal);

            // Close on click outside
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) hideDeleteModal();
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (!tagToDeleteId || !tagToDeleteElement) return;

                const id = tagToDeleteId;
                const element = tagToDeleteElement;
                const btn = confirmDeleteBtn;
                
                // Loading state
                const originalText = btn.textContent;
                btn.textContent = 'Menghapus...';
                btn.disabled = true;

                const url = "{{ route('buku-saku.tags.destroy', ':id') }}".replace(':id', id);

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    if (response.ok) {
                        element.closest('.tag-item').remove();
                        hideDeleteModal();
                        showToast('Kategori berhasil dihapus');
                    } else {
                        let msg = 'Gagal menghapus tag.';
                        try {
                            const data = await response.json();
                            msg = data.message || msg;
                        } catch (e) {
                            msg += ` (Status: ${response.status})`;
                        }
                        showToast(msg, 'error');
                    }
                })
                .catch(err => {
                        console.error(err);
                        showToast('Terjadi kesalahan sistem.', 'error');
                })
                .finally(() => {
                    btn.textContent = originalText;
                    btn.disabled = false;
                });
            });

            function attachDeleteEvent(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent triggering checkbox
                    const id = this.getAttribute('data-id');
                    showDeleteModal(id, this);
                });
            }

            // Attach to existing buttons
            document.querySelectorAll('.delete-tag-btn').forEach(btn => attachDeleteEvent(btn));

            // File Upload Logic
            const fileInput = document.getElementById('fileInput');
            const dropZone = document.getElementById('dropZone');
            const emptyState = document.getElementById('emptyState');
            const fileState = document.getElementById('fileState');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const fileSizeDisplay = document.getElementById('fileSizeDisplay');
            const titleInput = document.getElementById('titleInput');

            function updateFileUI(file) {
                if (file) {
                    emptyState.classList.add('hidden');
                    fileState.classList.remove('hidden');
                    fileNameDisplay.textContent = file.name;
                    fileSizeDisplay.textContent = (file.size / 1024).toFixed(2) + ' KB';
                    
                    // Auto-fill title if empty
                    if (!titleInput.value) {
                        const nameWithoutExt = file.name.split('.').slice(0, -1).join('.');
                        titleInput.value = nameWithoutExt.replace(/[-_]/g, ' ');
                    }
                } else {
                    emptyState.classList.remove('hidden');
                    fileState.classList.add('hidden');
                    fileNameDisplay.textContent = '';
                    fileSizeDisplay.textContent = '';
                }
            }

            fileInput.addEventListener('change', function(e) {
                updateFileUI(e.target.files[0]);
            });

            // Drag and Drop Effects
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dropZone.classList.add('bg-blue-50', 'border-blue-400');
            }

            function unhighlight(e) {
                dropZone.classList.remove('bg-blue-50', 'border-blue-400');
            }

            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    fileInput.files = files;
                    updateFileUI(files[0]);
                }
            }
        });
    </script>
</x-buku-saku-layout>
