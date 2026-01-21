<x-dashboard-layout>
    <div x-data="{ deleteMode: false }" class="bg-white dark:bg-gray-800 rounded-[10px] p-6 min-h-[600px] flex flex-col transition-colors duration-200">

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Page Title -->
        <h1 class="text-2xl lg:text-3xl font-bold text-black dark:text-white mb-6 transition-colors">Manajemen Modul Aplikasi</h1>

        <!-- Action Bar -->
        <div class="flex flex-col lg:flex-row items-center justify-between mb-8 gap-4">
            <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full lg:w-auto">
                <!-- Delete Module Button (Toggle) -->
                @hasrole('Supervisor|Admin')
                <button @click="deleteMode = !deleteMode" 
                        :class="deleteMode ? 'bg-gray-500 hover:bg-gray-600' : 'bg-red-600 hover:bg-red-700'"
                        class="w-full sm:w-auto text-white text-base font-medium px-6 py-2.5 rounded-lg transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                    <!-- Icon Trash (Show when NOT in delete mode) -->
                    <svg x-show="!deleteMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    <!-- Icon X (Show when in delete mode) -->
                    <svg x-show="deleteMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span x-text="deleteMode ? 'Batal' : 'Hapus Modul'"></span>
                </button>
                @endhasrole
                
                <!-- Add Module Button -->
                @hasrole('Supervisor|Admin')
                <a x-show="!deleteMode" href="{{ route('integrasi-sistem.create') }}" style="background-color: #16a34a;" class="w-full sm:w-auto text-white text-base font-medium px-6 py-2.5 rounded-lg hover:opacity-90 transition-all shadow-sm hover:shadow text-center">
                    Tambah Modul
                </a>
                @endhasrole
            </div>

            <!-- Search Bar -->
            <form action="{{ route('integrasi-sistem.index') }}" method="GET" class="relative w-full lg:w-[350px]">
                <label for="search" class="sr-only">Cari Modul</label>
                <input type="text" 
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari Modul..." 
                       class="w-full bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-base border border-gray-300 dark:border-gray-600 px-5 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10 placeholder-gray-500 dark:placeholder-gray-400">
                <button type="submit" aria-label="Cari" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-gray-400 hover:text-blue-500">
                        <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Module List Container -->
        <div class="flex flex-col gap-5">
            @forelse($modules as $module)
                <!-- Module Card -->
                <div :class="deleteMode ? 'border-red-400 bg-red-50 dark:bg-red-900/20 ring-1 ring-red-200 dark:ring-red-900/30' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-blue-200 dark:hover:border-blue-500 hover:shadow-lg hover:-translate-y-1'"
                     class="group border rounded-xl p-6 flex flex-col md:flex-row items-start gap-6 transition-all duration-300 relative">
                    
                    <!-- Module Image -->
                    <div class="w-full md:w-[220px] h-[150px] bg-gray-100 dark:bg-gray-700 rounded-lg flex-shrink-0 bg-cover bg-center overflow-hidden border border-gray-200 dark:border-gray-600 shadow-sm">
                        @if($module->icon)
                            <img src="{{ asset('storage/' . $module->icon) }}" alt="{{ $module->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        @else
                            <!-- Placeholder image if asset is missing -->
                            <div class="w-full h-full flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-300 dark:text-gray-500">
                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $module->name }}</h3>
                        <p class="text-base text-justify text-gray-600 dark:text-gray-300 leading-relaxed line-clamp-3" title="{{ $module->description }}">
                            {{ $module->description ?? 'Deskripsi tidak tersedia.' }}
                        </p>
                    </div>

                    <!-- Action -->
                    <div class="md:self-center flex flex-col items-center gap-2 w-full md:w-auto mt-4 md:mt-0">
                        <!-- Normal Mode: Visit Button -->
                        <a x-show="!deleteMode" href="{{ $module->url ?? '#' }}" 
                           target="{{ ($module->tab_type === 'new' || Str::startsWith($module->url, ['http://', 'https://'])) ? '_blank' : '_self' }}"
                           rel="{{ ($module->tab_type === 'new' || Str::startsWith($module->url, ['http://', 'https://'])) ? 'noopener noreferrer' : '' }}"
                           style="background-color: #2563eb;" class="w-full md:w-auto flex items-center justify-center gap-2 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition-all shadow-sm hover:shadow-md">
                            <span class="text-base font-semibold text-white">Kunjungi</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 13V19C18 19.5304 17.7893 20.0391 17.4142 20.4142C17.0391 20.7893 16.5304 21 16 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V8C3 7.46957 3.21071 6.96086 3.58579 6.58579C3.96086 6.21071 4.46957 6 5 6H11" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15 3H21V9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 14L21 3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>

                        <!-- Edit Button (Supervisor/Admin Only, Normal Mode) -->
                        @hasrole('Supervisor|Admin')
                        <a x-show="!deleteMode" href="{{ route('integrasi-sistem.edit', $module->id) }}" style="background-color: #eab308;" class="w-full md:w-auto flex items-center justify-center gap-2 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition-all shadow-sm hover:shadow-md">
                            <span class="text-base font-semibold text-white">Edit</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </a>
                        @endhasrole

                        <!-- Delete Mode: Delete Button -->
                        @hasrole('Supervisor|Admin')
                        <form x-show="deleteMode" action="{{ route('integrasi-sistem.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul ini?');" class="w-full" style="display: none;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full md:w-auto flex items-center justify-center gap-2 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition-all shadow-sm hover:shadow-md bg-red-600">
                                <span class="text-base font-semibold text-white">Hapus</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
                        @endhasrole
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <p class="text-gray-500 dark:text-gray-400 text-xl">Tidak ada modul yang tersedia untuk akun Anda.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
