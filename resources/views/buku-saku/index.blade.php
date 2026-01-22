<x-buku-saku-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ Auth::user()->name }}</h2>
    </div>

    <!-- Search Section -->
    <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pencarian Dokumen</h3>
        <form action="{{ route('buku-saku.index') }}" method="GET" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="q" value="{{ $query ?? '' }}" 
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Cari dokumen relevan, misal: welder" required>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg shadow transition-colors">
                Cari
            </button>
        </form>
    </div>

    @if($documents->isNotEmpty())
        <div class="mb-4 text-sm text-gray-600">
            @if($hasSearch && (!isset($resultsNotFound) || !$resultsNotFound))
                Ditemukan {{ $documents->count() }} dokumen yang relevan.
            @else
                Dokumen Terbaru ({{ $documents->count() }})
            @endif
        </div>
        <div class="grid grid-cols-1 gap-4">
            @foreach($documents as $doc)
                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow flex items-start justify-between bg-white">
                    <div class="flex items-start gap-4">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">
                                <a href="{{ route('buku-saku.show', $doc->id) }}" class="hover:text-blue-600 hover:underline">
                                    {{ $doc->title }}
                                </a>
                            </h3>
                            
                            <!-- Metadata -->
                            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                                <span class="uppercase">{{ $doc->file_type }}</span>
                                <span>&bull;</span>
                                <span>{{ $doc->file_size }}</span>
                                <span>&bull;</span>
                                <span>{{ $doc->created_at->diffForHumans() }}</span>
                            </div>

                            <p class="text-sm text-gray-600 mb-2">{{ $doc->description }}</p>
                            
                            <!-- Tags above Status -->
                            @if($doc->tags)
                                <div class="mb-2">
                                    @foreach(explode(',', $doc->tags) as $tag)
                                        <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded mr-1 mb-1">{{ trim($tag) }}</span>
                                    @endforeach
                                </div>
                            @endif
                            
                            <!-- Status -->
                            <div class="text-sm font-medium text-gray-800">
                                Status: <span class="{{ $doc->status == 'approved' ? 'text-green-600' : 'text-gray-600' }}">{{ $doc->status }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Favorite Button -->
                        <form action="{{ route('buku-saku.toggle-favorite', $doc->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 rounded-full hover:bg-gray-100 {{ Auth::user()->favoriteDocuments->contains($doc->id) ? 'text-yellow-500' : 'text-gray-400' }}" title="Favorit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ Auth::user()->favoriteDocuments->contains($doc->id) ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </button>
                        </form>

                        <!-- Download Button -->
                        <a href="{{ route('buku-saku.download', $doc->id) }}" class="p-2 rounded-full hover:bg-gray-100 text-blue-600" title="Unduh">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-10 text-gray-500">
            @if($hasSearch)
                <div class="flex flex-col items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="text-lg font-semibold text-gray-700">Tidak ditemukan hasil untuk "{{ $query }}"</p>
                    <p class="text-sm mt-2 text-gray-500">Coba gunakan kata kunci lain atau periksa ejaan Anda.</p>
                    <a href="{{ route('buku-saku.index') }}" class="inline-block mt-4 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                        Kembali ke Semua Dokumen
                    </a>
                </div>
            @else
                <p>Belum ada dokumen yang tersedia.</p>
            @endif
        </div>
    @endif
</x-buku-saku-layout>
