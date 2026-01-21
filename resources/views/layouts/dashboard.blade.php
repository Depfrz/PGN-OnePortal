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

    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }

        // Global External Link Handler
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                // Find closest anchor tag if click was on a child element
                const anchor = e.target.closest('a');
                
                if (anchor && anchor.href) {
                    const url = new URL(anchor.href, window.location.origin);
                    
                    // Check if hostname is different from current site
                    if (url.hostname !== window.location.hostname) {
                        // It is an external link
                        anchor.target = '_blank';
                        anchor.rel = 'noopener noreferrer';
                    }
                }
            });
        });
    </script>
</head>
<body class="font-sans antialiased bg-[#d9d9d9] dark:bg-gray-900 transition-colors duration-300">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-[#d9d9d9] dark:bg-gray-900 transition-colors">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden" style="display: none;"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-[280px] bg-[#439df1] dark:bg-gray-800 flex flex-col transition-transform duration-300 lg:static lg:translate-x-0 border-r dark:border-gray-700">
            <!-- Logo -->
            <div class="p-6 flex items-center justify-center bg-[#439df1] dark:bg-gray-800 transition-colors">
                <a href="{{ route('dashboard') }}" class="block">
                    <img src="{{ asset('images/pgn-logo.png') }}" alt="PGN Logo" class="w-[160px] h-auto object-contain">
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 space-y-4 mt-4">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('dashboard') ? 'bg-white dark:bg-gray-700 shadow-sm' : 'hover:bg-white/20 dark:hover:bg-gray-700' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <!-- Home Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-black dark:text-white group-hover:text-black dark:group-hover:text-white transition-colors">
                            <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                            <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-black dark:text-white group-hover:text-black dark:group-hover:text-white transition-colors">Dashboard</span>
                </a>

                <!-- Data History -->
                @can('view module history')
                <a href="{{ route('history') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('history') ? 'bg-white dark:bg-gray-700 shadow-sm' : 'hover:bg-white/20 dark:hover:bg-gray-700' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <!-- History Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-black dark:text-white group-hover:text-black dark:group-hover:text-white transition-colors">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-black dark:text-white group-hover:text-black dark:group-hover:text-white transition-colors">Data History</span>
                </a>
                @endcan

                <!-- Management User -->
                @can('view module management-user')
                <a href="{{ route('management-user.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('management-user.*') ? 'bg-white dark:bg-gray-700 shadow-sm' : 'hover:bg-white/20 dark:hover:bg-gray-700' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <!-- User Group Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-black dark:text-white group-hover:text-black dark:group-hover:text-white transition-colors">
                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-black dark:text-white leading-tight group-hover:text-black dark:group-hover:text-white transition-colors">Management<br>User</span>
                </a>
                @endcan

                <!-- Integrasi Sistem -->
                @can('view module integrasi-sistem')
                <a href="{{ route('integrasi-sistem.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-colors group {{ request()->routeIs('integrasi-sistem*') ? 'bg-white dark:bg-gray-700 shadow-sm' : 'hover:bg-white/20 dark:hover:bg-gray-700' }}">
                    <div class="w-8 h-8 flex items-center justify-center mr-4">
                        <svg viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6">
                            <path d="M8.33333 45.8333H37.5M14.0771 9.91042L18.4979 14.3312M18.4979 14.3312C19.67 13.1591 21.2591 12.5 22.9167 12.5M18.4979 14.3312C17.3258 15.5034 16.6667 17.0924 16.6667 18.75M16.6667 18.75H10.4167M16.6667 18.75C16.6667 20.4076 17.3258 21.9966 18.4979 23.1687M18.4979 23.1687L14.0771 27.5896M18.4979 23.1687C19.67 24.3409 21.2591 25 22.9167 25M22.9167 25V31.25M22.9167 25C24.5743 25 26.1633 24.3409 27.3354 23.1687M27.3354 23.1687L31.7562 27.5896M27.3354 23.1687C28.5075 21.9966 29.1667 20.4076 29.1667 18.75M35.4167 18.75H29.1667M29.1667 18.75C29.1667 17.0924 28.5075 15.5034 27.3354 14.3312M31.7562 9.91042L27.3354 14.3312M27.3354 14.3312C26.1633 13.1591 24.5743 12.5 22.9167 12.5M22.9167 12.5V6.25M0 37.5H45.8333V0H0V37.5ZM14.5833 45.8333H31.25V37.5H14.5833V45.8333Z" stroke="currentColor" stroke-width="2" class="text-black dark:text-white group-hover:text-black dark:group-hover:text-white transition-colors" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-black dark:text-white leading-tight group-hover:text-black dark:group-hover:text-white transition-colors">Integrasi<br>Sistem</span>
                </a>
                @endcan
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 border-b border-transparent dark:border-gray-700 h-[80px] shadow-sm flex items-center justify-between px-4 lg:px-8 z-20 transition-colors duration-300">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 dark:text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Breadcrumbs (Removed) -->
                <div></div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-6">
                    <!-- Dark Mode Toggle -->
                    <div x-data="{
                        darkMode: localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                        toggleTheme() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('color-theme', 'dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('color-theme', 'light');
                            }
                        }
                    }" class="mr-2">
                        <button @click="toggleTheme()" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5 transition-colors duration-200 border border-transparent dark:border-gray-600">
                            <!-- Sun Icon (Show in Dark Mode) -->
                            <svg x-show="darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                            <!-- Moon Icon (Show in Light Mode) -->
                            <svg x-show="!darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        </button>
                    </div>

                    <!-- Notification -->
                    <div x-data="{ 
                        open: false, 
                        notifications: [], 
                        unreadCount: 0,
                        async fetchNotifications() {
                            try {
                                const res = await fetch('{{ route('notifications.index') }}');
                                const data = await res.json();
                                this.notifications = data.notifications;
                                this.unreadCount = data.unread_count;
                            } catch (e) {
                                console.error('Error fetching notifications:', e);
                            }
                        },
                        async markAsRead(id) {
                            await fetch('/notifications/mark-read/' + id, { 
                                method: 'POST', 
                                headers: { 
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                } 
                            });
                            this.fetchNotifications();
                        },
                        async markAllRead() {
                            await fetch('{{ route('notifications.mark-all-read') }}', { 
                                method: 'POST', 
                                headers: { 
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                } 
                            });
                            this.fetchNotifications();
                        }
                    }" 
                    x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 30000)" 
                    class="relative">
                        <button @click="open = !open" class="relative p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors duration-200 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-black dark:text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            <span x-show="unreadCount > 0" class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-gray-800"></span>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             style="display: none;" 
                             class="absolute right-0 mt-3 w-[450px] bg-white dark:bg-gray-800 rounded-xl shadow-2xl z-50 border border-gray-100 dark:border-gray-700 overflow-hidden ring-1 ring-black ring-opacity-5">
                            
                            <!-- Header -->
                            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-white dark:bg-gray-800">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notifikasi</h3>
                                <button @click="markAllRead()" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold hover:bg-blue-50 dark:hover:bg-gray-700 px-3 py-1.5 rounded-lg transition-all whitespace-nowrap">
                                    Tandai semua dibaca
                                </button>
                            </div>

                            <!-- Notification List -->
                            <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                                <template x-for="notification in notifications" :key="notification.id">
                                    <div @click="markAsRead(notification.id)" 
                                         :class="{'bg-blue-50/60 dark:bg-blue-900/20': !notification.read_at, 'bg-white dark:bg-gray-800': notification.read_at}" 
                                         class="px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors group relative">
                                        
                                        <!-- Unread Indicator Dot -->
                                        <div x-show="!notification.read_at" class="absolute left-2 top-5 w-2 h-2 bg-blue-500 rounded-full"></div>

                                        <div class="flex justify-between items-start mb-1">
                                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate pr-2" x-text="notification.data.module"></p>
                                            <span class="text-[10px] font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full whitespace-nowrap" x-text="notification.created_at"></span>
                                        </div>
                                        
                                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed break-words" x-text="notification.data.description"></p>
                                        
                                        <div class="mt-2 flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium"
                                                  :class="{
                                                      'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300': notification.data.action === 'create',
                                                      'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300': notification.data.action === 'delete',
                                                      'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300': notification.data.action === 'update',
                                                      'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300': notification.data.action === 'login',
                                                      'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': notification.data.action === 'logout'
                                                  }"
                                                  x-text="notification.data.action">
                                            </span>
                                            <span class="text-[11px] text-gray-400" x-text="'• ' + notification.data.actor_name"></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State -->
                                <div x-show="notifications.length === 0" class="px-6 py-10 text-center flex flex-col items-center justify-center">
                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-full mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-400 dark:text-gray-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-900 dark:text-gray-100 font-medium text-sm">Tidak ada notifikasi</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">Kami akan memberi tahu Anda jika ada pembaruan.</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <a href="{{ route('history') }}" class="block w-full py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-50 dark:hover:bg-gray-700 border-t border-gray-100 dark:border-gray-700 transition-colors">
                                Lihat Semua History
                            </a>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div x-data="{ open: false, logoutConfirmOpen: false }" class="relative">
                        <div @click="open = !open" class="flex items-center space-x-4 cursor-pointer select-none">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 transition-colors">{{ Auth::user()->name ?? 'Admin' }}</span>
                            <div class="w-8 h-8 rounded-full border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center transition-colors bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                @if(Auth::user()?->profile_photo_path)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Foto Profil" class="w-full h-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-colors">
                                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5"
                             style="display: none;">
                            
                            <!-- Settings -->
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                Settings
                            </a>
                            
                            <!-- Logout -->
                            <button type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150" @click="open = false; logoutConfirmOpen = true">
                                Logout
                            </button>
                        </div>

                        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                            @csrf
                        </form>

                        <div x-show="logoutConfirmOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 z-50 flex items-center justify-center p-4"
                             style="display: none;">
                            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="logoutConfirmOpen = false"></div>

                            <div role="dialog"
                                 aria-modal="true"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
                                 class="relative w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/5 border border-gray-100 dark:border-gray-700 p-6">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.052 3.25c.866-1.5 3.03-1.5 3.896 0l7.355 12.876ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Logout</h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin keluar dari aplikasi?</p>
                                    </div>

                                    <button type="button" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors" @click="logoutConfirmOpen = false" aria-label="Tutup">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:justify-end">
                                    <button type="button" class="w-full sm:w-auto rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100 px-5 py-2.5 text-sm font-semibold transition-colors" @click="logoutConfirmOpen = false">
                                        Batal
                                    </button>
                                    <button type="button" class="w-full sm:w-auto rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 text-sm font-semibold shadow-sm transition-colors" @click="logoutConfirmOpen = false; document.getElementById('logout-form').submit()">
                                        Logout
                                    </button>
                                </div>
                            </div>
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
