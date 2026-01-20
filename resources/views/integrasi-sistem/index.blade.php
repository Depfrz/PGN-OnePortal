<x-dashboard-layout>
    <div class="bg-white rounded-[10px] p-6 min-h-[600px] flex flex-col">


        <!-- Page Title -->
        <h1 class="text-2xl lg:text-3xl font-bold text-black mb-6">Manajemen Modul Aplikasi</h1>

        <!-- Action Bar -->
        <div class="flex flex-col lg:flex-row items-center justify-between mb-8 gap-4">
            <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full lg:w-auto">
                <!-- Delete Module Button -->
                <button style="background-color: #dc2626;" class="w-full sm:w-auto text-white text-base font-medium px-6 py-2.5 rounded-lg hover:opacity-90 transition-all shadow-sm hover:shadow">
                    Hapus Modul
                </button>
                
                <!-- Add Module Button -->
                <a href="{{ route('integrasi-sistem.create') }}" style="background-color: #16a34a;" class="w-full sm:w-auto text-white text-base font-medium px-6 py-2.5 rounded-lg hover:opacity-90 transition-all shadow-sm hover:shadow text-center">
                    Tambah Modul
                </a>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full lg:w-[350px]">
                <input type="text" 
                       placeholder="Cari Modul..." 
                       class="w-full bg-gray-50 text-gray-900 text-base border border-gray-300 px-5 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10 placeholder-gray-500">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-gray-400">
                        <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Module List Container -->
        <div class="flex flex-col gap-5">
            @forelse($modules as $module)
                <!-- Module Card -->
                <div class="group border border-gray-200 rounded-xl p-6 flex flex-col md:flex-row items-start gap-6 bg-white transition-all hover:shadow-md hover:border-blue-200">
                    <!-- Module Image -->
                    <div class="w-full md:w-[220px] h-[150px] bg-gray-100 rounded-lg flex-shrink-0 bg-cover bg-center overflow-hidden border border-gray-200 shadow-sm">
                        @if($module->icon)
                            <img src="{{ asset('storage/' . $module->icon) }}" alt="{{ $module->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        @else
                            <!-- Placeholder image if asset is missing -->
                            <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-300">
                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 truncate group-hover:text-blue-600 transition-colors">{{ $module->name }}</h3>
                        <p class="text-base text-justify text-gray-600 leading-relaxed">
                            {{ $module->description ?? 'Deskripsi tidak tersedia.' }}
                        </p>
                    </div>

                    <!-- Action -->
                    <div class="md:self-center flex flex-col items-center gap-2 w-full md:w-auto mt-4 md:mt-0">
                        <a href="{{ $module->url ?? '#' }}" style="background-color: #2563eb;" class="w-full md:w-auto flex items-center justify-center gap-2 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition-all shadow-sm hover:shadow-md">
                            <span class="text-base font-semibold text-white">Kunjungi</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 13V19C18 19.5304 17.7893 20.0391 17.4142 20.4142C17.0391 20.7893 16.5304 21 16 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V8C3 7.46957 3.21071 6.96086 3.58579 6.58579C3.96086 6.21071 4.46957 6 5 6H11" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15 3H21V9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 14L21 3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <p class="text-gray-500 text-xl">Tidak ada modul yang tersedia untuk akun Anda.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
