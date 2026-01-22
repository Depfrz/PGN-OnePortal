<x-buku-saku-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Edit Dokumen</h2>
        <p class="text-gray-500 text-sm">Perbarui informasi dokumen.</p>
    </div>

    <form action="{{ route('buku-saku.update', $document->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        @method('PUT')
        
        <!-- File Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">File Dokumen (Opsional)</label>
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 sm:p-10 text-center cursor-pointer hover:bg-gray-50 transition-colors relative">
                <input type="file" name="file" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-50" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                
                <div id="emptyState" class="{{ $document->file_path ? 'hidden' : '' }}">
                    <div class="mx-auto h-12 w-12 text-gray-400 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <p class="text-sm text-blue-600 font-medium"><span class="hover:underline">Klik untuk ganti file</span> <span class="text-gray-600 font-normal">atau drag & drop</span></p>
                    <p class="text-xs text-gray-500 mt-1">PDF, Word (DOC/DOCX) hingga 15MB. Biarkan kosong jika tidak ingin mengubah file.</p>
                </div>

                <div id="fileState" class="{{ $document->file_path ? '' : 'hidden' }}">
                     <div class="mx-auto h-12 w-12 text-blue-500 bg-blue-50 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p id="fileNameDisplay" class="text-sm text-gray-800 font-medium truncate max-w-xs mx-auto">File Saat Ini: {{ $document->title . '.' . $document->file_type }}</p>
                    <p id="fileSizeDisplay" class="text-xs text-gray-500 mt-1">{{ $document->file_size }}</p>
                    <p class="text-xs text-blue-500 mt-2 font-medium">Klik atau drop file baru untuk mengganti</p>
                </div>
            </div>
        </div>

        <!-- Title -->
        <div class="mb-6">
            <label for="titleInput" class="block text-sm font-medium text-gray-700 mb-2">Judul Dokumen</label>
            <input type="text" name="title" id="titleInput" value="{{ old('title', $document->title) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2" required>
        </div>

        <!-- Valid Until -->
        <div class="mb-6">
            <label for="validUntilInput" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Masa Berlaku</label>
            <input type="date" name="valid_until" id="validUntilInput" value="{{ old('valid_until', $document->valid_until ? $document->valid_until->format('Y-m-d') : '') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
            <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ada masa berlaku.</p>
        </div>

        <!-- Tags (Checklist) -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
            
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <!-- Toolbar (Search + Add) -->
                <div class="bg-gray-50 p-3 border-b border-gray-200 flex flex-col md:flex-row gap-3">
                    <!-- Search Box -->
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" id="searchTagInput" class="w-full rounded-md border-gray-300 pl-10 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Cari tags...">
                    </div>
                    <!-- Add New Box -->
                    <div class="flex-1 flex gap-2">
                        <input type="text" id="newTagInput" class="w-full rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Tags baru...">
                        <button type="button" id="addTagBtn" class="bg-blue-600 text-white px-4 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center min-w-[44px] shadow-sm" title="Tambah Tag">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="tagsContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-80 overflow-y-auto p-4 bg-white">
                    @php
                        $currentTags = $document->tags ? explode(',', $document->tags) : [];
                    @endphp
                    @foreach($availableTags as $tag)
                    <label class="relative flex items-center p-3 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-200 cursor-pointer transition-all tag-item group bg-white">
                        <input type="checkbox" name="tags[]" value="{{ $tag->name }}" {{ in_array($tag->name, $currentTags) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 mr-3">
                        <span class="text-sm text-gray-700 font-medium flex-1 tag-name truncate">{{ $tag->name }}</span>
                        <button type="button" class="text-gray-400 hover:text-red-500 delete-tag-btn opacity-0 group-hover:opacity-100 transition-opacity p-1 ml-2 z-10" data-id="{{ $tag->id }}" title="Hapus Tag">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="mb-8">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" id="description" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">{{ old('description', $document->description) }}</textarea>
        </div>

        <!-- Submit -->
         <div class="flex justify-end gap-3">
            <a href="{{ route('buku-saku.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg shadow transition-colors text-sm">
                Batal
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition-colors text-sm">
                Simpan Perubahan
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                            <button type="button" class="text-gray-400 hover:text-red-500 delete-tag-btn opacity-0 group-hover:opacity-100 transition-opacity p-1 ml-2 z-10" data-id="${data.id}" title="Hapus Tag">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        `;
                        tagsContainer.insertBefore(label, tagsContainer.firstChild);
                        newTagInput.value = '';
                        attachDeleteEvent(label.querySelector('.delete-tag-btn'));
                    } else {
                        alert('Gagal menambah tag. Mungkin sudah ada.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat menambah tag.');
                });
            });

            // Delete Tag
            function attachDeleteEvent(btn) {
                btn.addEventListener('click', function() {
                    if (!confirm('Hapus kategori ini secara permanen?')) return;
                    const id = this.getAttribute('data-id');
                    
                    fetch(`/buku-saku/tags/${id}`, {
                         method: 'DELETE',
                         headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            this.closest('.tag-item').remove();
                        } else {
                            alert('Gagal menghapus tag.');
                        }
                    })
                    .catch(err => {
                         console.error(err);
                         alert('Terjadi kesalahan.');
                    });
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
                } else {
                    // Don't revert to empty state if there's an existing file, just show current file info (or logic handled by blade)
                    // But here client side, if they clear input, we might want to show "Current File" again.
                    // For now, simple logic:
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
