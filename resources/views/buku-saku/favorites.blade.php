<x-buku-saku-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Dokumen Favorit</h2>
        <p class="text-gray-500 text-sm">Koleksi dokumen yang Anda tandai sebagai favorit.</p>
    </div>

    @if($documents->isEmpty())
        <div class="text-center py-10 text-gray-500">
            <p>Anda belum memiliki dokumen favorit.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach($documents as $doc)
                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow flex items-start justify-between bg-white">
                    <div class="flex items-start gap-4">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ $doc->title }}</h3>
                            <p class="text-sm text-gray-500 mb-1">{{ $doc->description }}</p>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span>{{ $doc->file_type }}</span>
                                <span>&bull;</span>
                                <span>{{ $doc->file_size }}</span>
                                <span>&bull;</span>
                                <span>{{ $doc->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Unfavorite Button -->
                        <form action="{{ route('buku-saku.toggle-favorite', $doc->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 rounded-full hover:bg-gray-100 text-yellow-500" title="Hapus dari Favorit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current" viewBox="0 0 24 24" stroke="currentColor">
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
    @endif
</x-buku-saku-layout>
