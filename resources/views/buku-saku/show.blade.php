<x-buku-saku-layout>
    <div class="mb-6">
        <a href="{{ route('buku-saku.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden h-[80vh] flex flex-col">
        <div class="p-4 border-b flex justify-between items-center bg-gray-50">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $document->title }}</h1>
                <p class="text-sm text-gray-500">{{ $document->description }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('buku-saku.download', $document->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Download
                </a>
            </div>
        </div>
        <div class="flex-1 bg-gray-100 p-4">
            @if(in_array(strtolower($document->file_type), ['pdf']))
                <iframe src="{{ route('buku-saku.preview', $document->id) }}" class="w-full h-full rounded border bg-white"></iframe>
            @elseif(in_array(strtolower($document->file_type), ['jpg', 'jpeg', 'png', 'gif']))
                <div class="flex items-center justify-center h-full">
                    <img src="{{ route('buku-saku.preview', $document->id) }}" alt="{{ $document->title }}" class="max-w-full max-h-full rounded shadow">
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg font-medium">Pratinjau tidak tersedia untuk jenis file ini.</p>
                    <p class="text-sm mt-2">Silakan download file untuk melihat isinya.</p>
                    <a href="{{ route('buku-saku.download', $document->id) }}" class="mt-4 text-blue-600 hover:underline">Download File</a>
                </div>
            @endif
        </div>
    </div>
</x-buku-saku-layout>