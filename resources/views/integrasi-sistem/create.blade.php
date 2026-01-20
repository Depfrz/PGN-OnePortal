<x-dashboard-layout>
    <div class="p-6">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <p class="text-[24px] font-bold text-black">
                Dashboard <span class="mx-2">→</span> Integrasi Sistem <span class="mx-2">→</span> Tambah Modul Aplikasi
            </p>
        </div>

        <div class="bg-white rounded-[10px] p-6 lg:p-10 min-h-[800px] flex flex-col relative">
            <!-- Back Button and Title -->
            <div class="flex items-center mb-8">
                <a href="{{ route('integrasi-sistem.index') }}" class="mr-4 hover:opacity-75 transition-opacity">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 19L5 12L12 5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <h1 class="text-[32px] font-bold text-black">Tambah Modul Aplikasi</h1>
            </div>

            <!-- Form Container -->
            <div class="border border-black rounded-[15px] p-8 lg:p-10 w-full max-w-[1200px] mx-auto">
                <form action="{{ route('integrasi-sistem.store') }}" method="POST" x-data="{ 
                    categoryOpen: false, 
                    tabTypeOpen: false,
                    selectedCategory: '',
                    selectedTabType: '',
                    isImportant: false,
                    categories: ['Project Management Office', 'City Gas Project', 'Corporate Finance', 'Human Capital Management', 'Procurement', 'Information and Communication Technology'],
                    tabTypes: ['Current Tab', 'New tab (Blank)']
                }">
                    @csrf

                    <!-- Nama Modul / Aplikasi -->
                    <div class="mb-8">
                        <label class="block text-[24px] font-bold text-black mb-3">Nama Modul / Aplikasi</label>
                        <input type="text" name="name" placeholder="Contoh : Buku Saku Digital" 
                            class="w-full h-[58px] px-4 text-[20px] font-light border border-black rounded-[5px] focus:ring-1 focus:ring-blue-500 placeholder-opacity-25 placeholder-black">
                    </div>

                    <!-- Deskripsi Singkat -->
                    <div class="mb-8">
                        <label class="block text-[24px] font-bold text-black mb-3">Deskripsi Singkat</label>
                        <textarea name="description" rows="4" placeholder='Contoh: "Panduan teknis lapangan untuk QAQC." (Maks 100-150 karakter).'
                            class="w-full p-4 text-[20px] font-light border border-black rounded-[5px] focus:ring-1 focus:ring-blue-500 placeholder-opacity-25 placeholder-black resize-none"></textarea>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-8 relative">
                        <label class="block text-[24px] font-bold text-black mb-3">Kategori</label>
                        <div class="relative">
                            <button type="button" @click="categoryOpen = !categoryOpen" @click.outside="categoryOpen = false"
                                class="w-full h-[58px] px-4 text-left border border-black rounded-[5px] flex items-center justify-between bg-white">
                                <span class="text-[20px] font-light" :class="selectedCategory ? 'text-black' : 'text-black opacity-25'" x-text="selectedCategory || 'Pilih kategori...'"></span>
                                <svg class="w-6 h-6 transform transition-transform duration-200" :class="categoryOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="categoryOpen" 
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-10 w-full mt-1 bg-white border border-black rounded-[5px] shadow-lg max-h-[300px] overflow-y-auto">
                                <template x-for="category in categories" :key="category">
                                    <div @click="selectedCategory = category; categoryOpen = false" 
                                        class="px-4 py-3 text-[20px] hover:bg-[#439df1] hover:text-white cursor-pointer transition-colors"
                                        :class="selectedCategory === category ? 'bg-[#439df1] text-white' : 'text-black'">
                                        <span x-text="category"></span>
                                    </div>
                                </template>
                            </div>
                            <input type="hidden" name="category" :value="selectedCategory">
                        </div>
                    </div>

                    <!-- Target URL / Endpoint -->
                    <div class="mb-8">
                        <label class="block text-[24px] font-bold text-black mb-3">Target URL / Endpoint</label>
                        <input type="text" name="url" placeholder="Contoh Internal / Eksternal: /buku-saku atau https://dashboard-pertamina.com" 
                            class="w-full h-[58px] px-4 text-[20px] font-light border border-black rounded-[5px] focus:ring-1 focus:ring-blue-500 placeholder-opacity-25 placeholder-black">
                    </div>

                    <!-- Tipe Tab -->
                    <div class="mb-8 relative">
                        <label class="block text-[24px] font-bold text-black mb-3">Tipe Tab</label>
                        <div class="relative">
                            <button type="button" @click="tabTypeOpen = !tabTypeOpen" @click.outside="tabTypeOpen = false"
                                class="w-full h-[58px] px-4 text-left border border-black rounded-[5px] flex items-center justify-between bg-white">
                                <span class="text-[20px] font-light" :class="selectedTabType ? 'text-black' : 'text-black opacity-25'" x-text="selectedTabType || 'Pilih tipe tab...'"></span>
                                <svg class="w-6 h-6 transform transition-transform duration-200" :class="tabTypeOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="tabTypeOpen" 
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-10 w-full mt-1 bg-white border border-black rounded-[5px] shadow-lg">
                                <template x-for="type in tabTypes" :key="type">
                                    <div @click="selectedTabType = type; tabTypeOpen = false" 
                                        class="px-4 py-3 text-[20px] hover:bg-[#439df1] hover:text-white cursor-pointer transition-colors"
                                        :class="selectedTabType === type ? 'bg-[#439df1] text-white' : 'text-black'">
                                        <span x-text="type"></span>
                                    </div>
                                </template>
                            </div>
                            <input type="hidden" name="tab_type" :value="selectedTabType">
                        </div>
                    </div>

                    <!-- Status Modul (Penting) -->
                    <div class="mb-12">
                        <label class="block text-[24px] font-bold text-black mb-3">Status Modul (Penting)</label>
                        <div class="relative inline-block w-14 h-8 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="is_important" id="toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer translate-x-1 translate-y-1 transition-transform duration-200" :class="isImportant ? 'translate-x-7 border-white' : 'border-gray-300'" @click="isImportant = !isImportant"/>
                            <label for="toggle" class="toggle-label block overflow-hidden h-8 rounded-full cursor-pointer border border-black transition-colors duration-200" :class="isImportant ? 'bg-black' : 'bg-white'"></label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="bg-[#0dcd4d] text-black text-[20px] font-bold px-12 py-3 rounded-[15px] hover:bg-green-600 hover:text-white transition-colors shadow-md">
                        Tambah Modul
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-layout>