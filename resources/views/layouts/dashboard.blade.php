<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PGN One Portal') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-sans antialiased bg-[#d9d9d9]">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden" style="display: none;"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-[280px] bg-[#439df1] flex flex-col transition-transform duration-300 lg:static lg:translate-x-0">
            <!-- Logo -->
            <div class="p-6 flex items-center justify-center">
                <img src="{{ asset('images/pgn-logo.png') }}" alt="PGN Logo" class="w-[180px] h-auto object-contain">
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 space-y-4 mt-4">
                <!-- Beranda -->
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('dashboard') ? 'bg-white shadow-sm' : 'hover:bg-white/20' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <!-- Home Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-black">
                            <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                            <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                        </svg>
                    </div>
                    <span class="text-[20px] font-bold text-black">Beranda</span>
                </a>

                <!-- History -->
                <a href="{{ route('history') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('history') ? 'bg-white shadow-sm' : 'hover:bg-white/20' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <!-- History Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-black">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-[20px] font-bold text-black">History</span>
                </a>

                <!-- Management User -->
                <a href="{{ route('management-user') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('management-user') ? 'bg-white shadow-sm' : 'hover:bg-white/20' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <!-- User Group Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-black">
                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-[20px] font-bold text-black leading-tight">Management<br>User</span>
                </a>

                <!-- Integrasi Sistem -->
                <a href="{{ route('integrasi-sistem.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('integrasi-sistem*') ? 'bg-white shadow-sm' : 'hover:bg-white/20' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <!-- System/Monitor Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-black">
                            <path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3zm-9 15H9v-2h1.5v2zm4.5 0h-1.5v-2h1.5v2zm-3-4.5H9v-2h1.5v2zm4.5 0h-1.5v-2h1.5v2z" />
                            <path d="M6 7.5h12v9H6z" />
                        </svg>
                    </div>
                    <span class="text-[20px] font-bold text-black leading-tight">Integrasi<br>Sistem</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white h-[80px] shadow-sm flex items-center justify-between px-4 lg:px-8 z-20">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Breadcrumbs -->
                <div class="flex items-center text-[18px] lg:text-[24px] font-bold text-black overflow-hidden whitespace-nowrap">
                    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>

                    @if(request()->routeIs('integrasi-sistem*'))
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 lg:w-6 lg:h-6 mx-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                        <span class="truncate">Integrasi Sistem</span>
                    @elseif(request()->routeIs('management-user'))
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 lg:w-6 lg:h-6 mx-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                        <span class="truncate">Management User</span>
                    @elseif(request()->routeIs('history'))
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 lg:w-6 lg:h-6 mx-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                        <span class="truncate">History</span>
                    @endif
                </div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-6">
                    <!-- Notification -->
                    <button class="relative p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-black">
                            <path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9c.46.557.967 1.053 1.507 1.482a2.25 2.25 0 003.483 0 18.267 18.267 0 001.507-1.482.75.75 0 10-1.148-.96H9.012a.75.75 0 00-1.148.96z" clip-rule="evenodd" />
                        </svg>
                        <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border border-white"></span>
                    </button>

                    <!-- User Profile -->
                    <div class="flex items-center space-x-4">
                        <span class="text-[24px] font-normal text-black">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <div class="w-10 h-10 rounded-full border-2 border-black flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-black">
                                <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Scroll Area -->
            <main class="flex-1 overflow-y-auto p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>