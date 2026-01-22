<x-app-layout :full-width="true">
    <div class="py-0 overflow-x-hidden">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-0 md:gap-6 w-full" x-data="{ sidebarOpen: false }">
    
    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-gray-900/50 md:hidden" 
         style="display: none;"></div>

    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
         class="fixed inset-y-0 left-0 z-50 w-48 bg-white shadow-xl transition-transform duration-300 md:translate-x-0 md:static md:block md:shadow-none md:bg-transparent md:w-48 flex-shrink-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6 h-full md:h-auto overflow-y-auto md:overflow-visible">
            <div class="p-2 bg-blue-600 text-white font-bold text-sm rounded-t-none md:rounded-t-lg flex justify-between items-center">
                <span>Buku Saku</span>
                <!-- Close Button (Mobile Only) -->
                <button @click="sidebarOpen = false" class="md:hidden text-white hover:bg-blue-700 rounded p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="flex flex-col p-1.5 space-y-0.5">
                            @if(auth()->user()->hasModuleAccess('Buku Saku'))
                            <a href="{{ route('buku-saku.index') }}" 
                               class="flex items-center px-2 py-1 text-xs font-medium rounded-md {{ request()->routeIs('buku-saku.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 {{ request()->routeIs('buku-saku.index') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Beranda
                            </a>
                            @endif

                            @if(auth()->user()->hasModuleAccess('Dokumen Favorit'))
                            <a href="{{ route('buku-saku.favorites') }}" 
                               class="flex items-center px-3 py-1.5 text-sm font-medium rounded-md {{ request()->routeIs('buku-saku.favorites') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 {{ request()->routeIs('buku-saku.favorites') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                Dokumen Favorit
                            </a>
                            @endif

                            @if(auth()->user()->hasModuleAccess('Riwayat Dokumen'))
                            <a href="{{ route('buku-saku.history') }}" 
                               class="flex items-center px-3 py-1.5 text-sm font-medium rounded-md {{ request()->routeIs('buku-saku.history') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 {{ request()->routeIs('buku-saku.history') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Riwayat Dokumen
                            </a>
                            @endif

                            @if(auth()->user()->hasModuleAccess('Pengecekan File'))
                            <a href="{{ route('buku-saku.approval') }}" 
                               class="flex items-center px-3 py-1.5 text-sm font-medium rounded-md {{ request()->routeIs('buku-saku.approval') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 {{ request()->routeIs('buku-saku.approval') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                Pengecekan File
                            </a>
                            @endif

                            @if(auth()->user()->hasModuleAccess('Upload Dokumen'))
                            <a href="{{ route('buku-saku.upload') }}" 
                               class="flex items-center px-3 py-1.5 text-sm font-medium rounded-md {{ request()->routeIs('buku-saku.upload') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 {{ request()->routeIs('buku-saku.upload') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Upload Dokumen
                            </a>
                            @endif
            </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 min-w-0">
                    <div class="bg-white shadow-sm sm:rounded-lg min-h-[500px] relative">
                    <!-- Mobile Sidebar Toggle (Toolbar) -->
                    <div class="md:hidden border-b border-gray-100 p-3 flex items-center bg-gray-50">
                        <button @click="sidebarOpen = true" class="inline-flex items-center justify-center p-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <span class="text-gray-700 font-medium text-sm">Menu</span>
                    </div>

                    <div class="p-3 sm:p-4 text-gray-900">

                            @if(session('success'))
                                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Berhasil!</strong>
                                    <span class="block sm:inline">{{ session('success') }}</span>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Error!</strong>
                                    <span class="block sm:inline">{{ session('error') }}</span>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Periksa Inputan Anda!</strong>
                                    <ul class="list-disc pl-5 mt-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
