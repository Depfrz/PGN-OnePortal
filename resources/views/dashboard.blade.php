<x-dashboard-layout>
    <div class="bg-white dark:bg-gray-800 rounded-[10px] p-6 min-h-[800px] transition-colors duration-300">
        <!-- Welcome Message -->
        <h1 class="text-lg lg:text-xl font-semibold text-black dark:text-white mb-10">Selamat Datang, {{ Auth::user()->name ?? 'Admin' }}.</h1>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @php
                // Use explicit hex colors for inline styles to guarantee rendering
                $colors = ['#0643fb', '#fb060a', '#30ff07', '#9333ea', '#f97316', '#14b8a6'];
            @endphp

            @forelse($modules as $index => $module)
                <a href="{{ $module->url }}" 
                   target="{{ ($module->tab_type === 'new' || Str::startsWith($module->url, ['http://', 'https://'])) ? '_blank' : '_self' }}"
                   rel="{{ ($module->tab_type === 'new' || Str::startsWith($module->url, ['http://', 'https://'])) ? 'noopener noreferrer' : '' }}"
                   class="flex flex-col items-center text-center group transition-transform hover:scale-105 duration-200">
                    <!-- Badge -->
                    <div style="background-color: {{ $colors[$index % count($colors)] }}" class="text-white rounded-[15px] px-6 py-2 mb-4 flex items-center shadow-md min-w-[180px] justify-center z-10">
                        <!-- Dynamic Icon based on module icon name or default -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-2">
                            @if($module->icon === 'home')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            @elseif($module->icon === 'database')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            @elseif($module->icon === 'users')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            @elseif($module->icon === 'clock')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @elseif($module->icon === 'briefcase')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 2.622V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v2.335m17.627 .922c-.12.433-.48.75-.902.793a48.868 48.868 0 01-5.356.417c-2.825 0-5.46-.35-7.85-1.01m13.206.593a48.652 48.652 0 00-3.678-.544M3.75 14.25c.066.864.634 1.573 1.45 1.815a49.12 49.12 0 006.8 1.035m-6.8-1.035a48.65 48.65 0 00-3.25-.56m0 0a48.665 48.665 0 00-2.062-.224M3.75 14.25V8.625c0-1.168.863-2.148 2.023-2.27 1.838-.191 3.73-.306 5.652-.306 1.923 0 3.815.115 5.652.306 1.16.122 2.023 1.102 2.023 2.27v5.625" />
                            @elseif($module->icon === 'clipboard')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                            @elseif($module->icon === 'shopping-cart')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            @endif
                        </svg>
                        <span class="font-bold text-xs">{{ $module->name }}</span>
                    </div>
                    <!-- Content Box -->
                    <div class="bg-[#fcf9f9] dark:bg-gray-800 rounded-[10px] p-6 w-full min-h-[300px] flex items-start justify-center pt-8 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all duration-300">
                        <p class="text-sm font-normal text-gray-700 dark:text-gray-200 max-w-[260px] leading-relaxed break-words text-justify line-clamp-[12] overflow-hidden" title="{{ $module->description ?? 'Akses cepat ke modul ' . $module->name }}">
                            {{ $module->description ?? 'Akses cepat ke modul ' . $module->name }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="col-span-1 lg:col-span-3 text-center py-10 text-gray-500 dark:text-gray-400">
                    <p>Belum ada modul yang dapat diakses.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
