<x-buku-saku-layout title="Buku Saku">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800">Selamat Datang, {{ Auth::user()->name }}</h2>
    </div>

    <!-- Search Section -->
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Pencarian Dokumen</h3>
        <form x-data="{ 
            open: false, 
            selected: {{ json_encode(request('selected_tags', [])) }},
            query: '{{ $query ?? '' }}',
            updateSearch(tag, checked) {
                if (checked) {
                    if (!this.query.toLowerCase().includes(tag.toLowerCase())) {
                        this.query = this.query ? this.query + ' ' + tag : tag;
                    }
                } else {
                    let regex = new RegExp('\\b' + tag + '\\b', 'gi');
                    this.query = this.query.replace(regex, '').replace(/\s\s+/g, ' ').trim();
                }
            }
        }" action="{{ route('buku-saku.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <!-- Search Input -->
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" name="q" x-model="query" 
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base" 
                    placeholder="Cari dokumen relevan, misal: welder">
            </div>

            <!-- Tags Dropdown Checklist -->
            @if(isset($availableTags) && $availableTags->count() > 0)
            <div class="relative min-w-[200px]">
                <button @click="open = !open" @click.away="open = false" type="button" 
                    class="w-full bg-white border border-gray-300 text-gray-700 py-2.5 px-4 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center">
                    <span x-text="selected.length > 0 ? selected.length + ' Kategori Dipilih' : 'Filter Kategori'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Dropdown Content -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute z-10 mt-2 w-full md:w-64 bg-white rounded-xl shadow-lg border border-gray-100 max-h-60 overflow-y-auto">
                    <div class="p-2 space-y-1">
                        @foreach($availableTags as $tag)
                            <label class="flex items-center px-3 py-2 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="selected_tags[]" value="{{ $tag->name }}" 
                                    x-model="selected" @change="updateSearch('{{ $tag->name }}', $event.target.checked)"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 h-4 w-4">
                                <span class="ml-3 text-sm text-gray-700 font-medium">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg shadow transition-colors w-full sm:w-auto text-base whitespace-nowrap">
                Cari
            </button>
        </form>
    </div>

    @php
        $sections = [];
        if($hasSearch) {
            $sections[] = [
                'title' => 'Dokumen Relevan',
                'subtitle' => $documents->isNotEmpty() ? $documents->count() . ' dokumen ditemukan' : null,
                'items' => $documents,
                'show_empty_msg' => 'Tidak ditemukan dokumen yang cocok dengan kata kunci "' . $query . '".'
            ];
            $sections[] = [
                'title' => 'Dokumen Lainnya',
                'subtitle' => null,
                'items' => $otherDocuments,
                'show_empty_msg' => null
            ];
        } else {
            $sections[] = [
                'title' => 'Dokumen Terbaru',
                'subtitle' => $documents->count() . ' dokumen',
                'items' => $documents,
                'show_empty_msg' => 'Belum ada dokumen.'
            ];
        }
    @endphp

    @foreach($sections as $section)
        @if($section['items']->isEmpty() && empty($section['show_empty_msg']))
            @continue
        @endif

        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4 border-b border-gray-100 pb-2">
                <h3 class="text-lg font-bold text-gray-800">{{ $section['title'] }}</h3>
                @if($section['subtitle'])
                    <span class="text-sm font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $section['subtitle'] }}</span>
                @endif
            </div>

            @if($section['items']->isEmpty())
                 <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                    <p class="text-gray-500 italic">{{ $section['show_empty_msg'] }}</p>
                 </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach($section['items'] as $doc)
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow flex items-start justify-between bg-white gap-4">
                            <div class="flex items-start gap-4 flex-1 min-w-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <h3 class="font-bold text-base text-gray-800 break-words mb-1">
                                            <a href="{{ route('buku-saku.preview', $doc->id) }}" target="_blank" rel="noopener noreferrer" class="hover:text-blue-600 hover:underline">
                                                {{ $doc->title }}
                                            </a>
                                        </h3>
                                        <!-- Action Buttons -->
                                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                            <form action="{{ route('buku-saku.toggle-favorite', $doc->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="p-1 rounded-full hover:bg-gray-100 {{ Auth::user()->favoriteDocuments->contains($doc->id) ? 'text-yellow-500' : 'text-gray-400' }}" title="Favorit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ Auth::user()->favoriteDocuments->contains($doc->id) ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <a href="{{ route('buku-saku.download', $doc->id) }}" class="p-1 rounded-full hover:bg-gray-100 text-blue-600" title="Unduh">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Metadata -->
                                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-2">
                                        <span class="uppercase bg-gray-100 px-2 py-0.5 rounded font-bold">{{ $doc->file_type }}</span>
                                        <span class="hidden sm:inline">&bull;</span>
                                        <span>{{ $doc->file_size }}</span>
                                        <span class="hidden sm:inline">&bull;</span>
                                        <span>{{ $doc->created_at->diffForHumans() }}</span>
                                    </div>

                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $doc->description }}</p>
                                    
                                    <!-- Tags above Validity -->
                                    @if($doc->tags)
                                        <div class="mb-2 flex flex-wrap gap-1.5">
                                            @foreach(explode(',', $doc->tags) as $tag)
                                                <span class="inline-block bg-gray-100 text-gray-600 text-sm px-2 py-0.5 rounded">{{ trim($tag) }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <!-- Validity / Countdown -->
                                    <div class="text-sm font-bold mt-1">
                                        @if($doc->valid_until)
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $diffInYears = $now->floatDiffInYears($doc->valid_until, false);
                                            @endphp
                                            @if($diffInYears > 1)
                                                <span class="text-green-600">Berlaku sampai: {{ \Carbon\Carbon::parse($doc->valid_until)->translatedFormat('d F Y') }}</span>
                                            @elseif($diffInYears <= 1 && $diffInYears > 0)
                                                <span class="text-red-600">
                                                    Masa berlaku habis dalam: 
                                                    {{ $now->diff($doc->valid_until)->format('%m Bulan %d Hari') }}
                                                </span>
                                            @else
                                                <span class="text-red-600">Sudah Kedaluwarsa</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Masa berlaku tidak diatur</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="q"]');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    if (this.value.trim() === '') {
                        window.location.href = "{{ route('buku-saku.index') }}";
                    }
                });
            }
        });
    </script>
</x-buku-saku-layout>
