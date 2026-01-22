<nav x-data="{ open: false, logoutConfirmOpen: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/pgn-logo.png') }}" alt="PGN Logo" class="h-8 w-auto object-contain select-none">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-xs">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Notification & Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Notification Dropdown -->
                <div class="relative mr-3">
                    <x-dropdown align="right" width="w-[450px]">
                        <x-slot name="trigger">
                            <button class="relative p-2 rounded-full bg-gray-50 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                <span class="sr-only">View notifications</span>
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @php
                                    $isBukuSaku = request()->routeIs('buku-saku.*');
                                    
                                    // Filter unread notifications count
                                    $unreadCount = auth()->user()->unreadNotifications->filter(function($n) use ($isBukuSaku) {
                                        if ($isBukuSaku) {
                                            return isset($n->data['module']) && $n->data['module'] === 'Buku Saku';
                                        }
                                        return true; // Show all in dashboard/other pages
                                    })->count();

                                    // Filter list of notifications
                                    $notifications = auth()->user()->notifications()->latest()->take(20)->get()->filter(function($n) use ($isBukuSaku) {
                                        if ($isBukuSaku) {
                                            return isset($n->data['module']) && $n->data['module'] === 'Buku Saku';
                                        }
                                        return true; // Show all in dashboard/other pages
                                    })->take(10);
                                @endphp

                                @if($unreadCount > 0)
                                    <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white bg-red-500 transform translate-x-1/4 -translate-y-1/4"></span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-700 bg-white">
                                Notifikasi {{ $isBukuSaku ? '(Buku Saku)' : '' }}
                            </div>
                            
                            <div class="max-h-80 overflow-y-auto">
                                @forelse($notifications as $notification)
                                    <div class="px-4 py-3 border-b border-gray-100 text-sm hover:bg-gray-50 {{ $notification->read_at ? 'bg-white opacity-75' : 'bg-blue-50' }}">
                                        <p class="font-medium text-gray-900">{{ $notification->data['description'] ?? 'No Description' }}</p>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-xs text-gray-500">{{ $notification->created_at->format('d/m/Y') }}</span>
                                            @if(!$isBukuSaku)
                                                <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">{{ $notification->data['module'] ?? 'System' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        Tidak ada notifikasi {{ $isBukuSaku ? 'Buku Saku' : '' }}
                                    </div>
                                @endforelse
                            </div>
                            
                            @if($unreadCount > 0)
                                <div class="block px-4 py-2 text-xs text-center text-blue-600 font-medium hover:bg-gray-100 cursor-pointer border-t border-gray-100">
                                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-center">Tandai semua sudah dibaca</button>
                                    </form>
                                </div>
                            @endif
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-xs leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link href="#" @click.prevent="logoutConfirmOpen = true">
                            {{ __('Logout') }}
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @can('view module integrasi-sistem')
            <x-responsive-nav-link :href="route('integrasi-sistem.index')" :active="request()->routeIs('integrasi-sistem.*')">
                {{ __('Integrasi Sistem') }}
            </x-responsive-nav-link>
            @endcan

            @can('view module management-user')
            <x-responsive-nav-link :href="route('management-user.index')" :active="request()->routeIs('management-user')">
                {{ __('Management User') }}
            </x-responsive-nav-link>
            @endcan

            @can('view module history')
            <x-responsive-nav-link :href="route('history')" :active="request()->routeIs('history.*')">
                {{ __('Data History') }}
            </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-sm text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="#" @click.prevent="logoutConfirmOpen = true">
                    {{ __('Logout') }}
                </x-responsive-nav-link>
            </div>
        </div>
    </div>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>

    <div x-show="logoutConfirmOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
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
</nav>
