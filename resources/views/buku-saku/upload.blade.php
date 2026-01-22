<x-buku-saku-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Upload Dokumen</h2>
        <p class="text-gray-500 text-sm">Judul akan otomatis diambil dari nama file.</p>
    </div>

    <form action="{{ route('buku-saku.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        
        <!-- File Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">File Dokumen</label>
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-10 text-center cursor-pointer hover:bg-gray-50 transition-colors relative">
                <input type="file" name="file" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-50" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                
                <div id="emptyState">
                    <div class="mx-auto h-12 w-12 text-gray-400 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <p class="text-sm text-blue-600 font-medium"><span class="hover:underline">Klik untuk upload</span> <span class="text-gray-600 font-normal">atau drag & drop</span></p>
                    <p class="text-xs text-gray-500 mt-1">PDF, Word (DOC/DOCX) hingga 10MB</p>
                </div>

                <div id="fileState" class="hidden">
                     <div class="mx-auto h-12 w-12 text-blue-500 bg-blue-50 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p id="fileNameDisplay" class="text-sm text-gray-800 font-medium truncate max-w-xs mx-auto"></p>
                    <p id="fileSizeDisplay" class="text-xs text-gray-500 mt-1"></p>
                </div>
            </div>
        </div>

        <!-- Title (Auto-filled but editable) -->
        <div class="mb-6">
            <label for="titleInput" class="block text-sm font-medium text-gray-700 mb-2">Judul Dokumen</label>
            <input type="text" name="title" id="titleInput" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2" placeholder="Judul akan terisi otomatis saat file dipilih" required>
        </div>

        <!-- Tags -->
        <div class="mb-6">
            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags (Opsional)</label>
            <input type="text" name="tags" id="tags" placeholder="Contoh: HSE, Safety, Pedoman" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
        </div>

        <!-- Kategori -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex gap-4 mb-4">
                    <input type="text" id="searchCategory" placeholder="Cari kategori..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <div class="flex flex-1 gap-2">
                        <input type="text" id="newCategory" placeholder="Nama kategori baru..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <button type="button" id="addCategoryBtn" class="bg-blue-500 hover:bg-blue-600 text-white rounded-md px-3 py-2 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div id="categoryList" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                    <!-- Categories injected via JS -->
                </div>
            </div>
            <!-- Hidden inputs for selected categories will be appended here -->
            <div id="selectedCategoriesContainer"></div>
        </div>

        <!-- Deskripsi -->
        <div class="mb-8">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" id="description" rows="4" placeholder="Tambahkan keterangan singkat tentang dokumen ini..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3"></textarea>
        </div>

        <!-- Submit -->
         <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-lg shadow transition-colors">
                Upload Dokumen
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    fileSizeDisplay.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                    
                    // Auto-fill title
                    const fileNameWithoutExt = file.name.split('.').slice(0, -1).join('.');
                    titleInput.value = fileNameWithoutExt;
                }
            }

            fileInput.addEventListener('change', function(e) {
                updateFileUI(this.files[0]);
            });

            // Drag & Drop visual feedback
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
            });

            ['dragleave', 'drop'].forEach(event => {
                dropZone.addEventListener(event, (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                });
            });
            
            dropZone.addEventListener('drop', (e) => {
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    updateFileUI(e.dataTransfer.files[0]);
                }
            });

            // Category Logic
            let categories = [
                { id: 1, name: 'Instruksi Kerja', selected: false },
                { id: 2, name: 'Materi', selected: false },
                { id: 3, name: 'Prosedur', selected: false },
                { id: 4, name: 'Safety', selected: false },
                { id: 5, name: 'HSE', selected: false }
            ];

            const categoryList = document.getElementById('categoryList');
            const searchInput = document.getElementById('searchCategory');
            const newCategoryInput = document.getElementById('newCategory');
            const addCategoryBtn = document.getElementById('addCategoryBtn');
            const form = document.getElementById('uploadForm');
            const selectedCategoriesContainer = document.getElementById('selectedCategoriesContainer');

            function renderCategories() {
                const searchTerm = searchInput.value.toLowerCase();
                categoryList.innerHTML = '';

                categories.forEach((cat, index) => {
                    if (cat.name.toLowerCase().includes(searchTerm)) {
                        const div = document.createElement('div');
                        div.className = 'flex items-center justify-between p-2 hover:bg-gray-50 rounded group';
                        div.innerHTML = `
                            <span class="text-sm text-gray-700">${cat.name}</span>
                            <div class="flex items-center gap-3">
                                <button type="button" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity" onclick="deleteCategory(${index})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5" 
                                    ${cat.selected ? 'checked' : ''} 
                                    onchange="toggleCategory(${index})">
                            </div>
                        `;
                        categoryList.appendChild(div);
                    }
                });
            }

            window.toggleCategory = function(index) {
                categories[index].selected = !categories[index].selected;
            };

            window.deleteCategory = function(index) {
                if(confirm('Hapus kategori ini?')) {
                    categories.splice(index, 1);
                    renderCategories();
                }
            };

            addCategoryBtn.addEventListener('click', () => {
                const name = newCategoryInput.value.trim();
                if (name) {
                    categories.push({ id: Date.now(), name: name, selected: true });
                    newCategoryInput.value = '';
                    renderCategories();
                }
            });

            searchInput.addEventListener('input', renderCategories);

            // Initial render
            renderCategories();

            // Form Submit - Append selected categories
            form.addEventListener('submit', function() {
                selectedCategoriesContainer.innerHTML = '';
                categories.filter(c => c.selected).forEach(c => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'categories[]';
                    input.value = c.name;
                    selectedCategoriesContainer.appendChild(input);
                });
            });
        });
    </script>
</x-buku-saku-layout>