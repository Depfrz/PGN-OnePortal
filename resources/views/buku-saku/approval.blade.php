<x-buku-saku-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pengecekan File</h2>
        <p class="text-gray-500 text-sm">Daftar pengecekan dan riwayat status dokumen.</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        JUDUL DOKUMEN
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        UPLOADER
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        STATUS
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        INFO PROSES
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        TANGGAL
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        AKSI
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($documents as $doc)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-500 font-bold text-xs uppercase">
                                {{ $doc->file_type }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('buku-saku.preview', $doc->id) }}" target="_blank" class="hover:text-blue-600 hover:underline">
                                        {{ $doc->title }}
                                    </a>
                                </div>
                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ $doc->description }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $doc->user->name ?? 'Unknown' }}</div>
                        <div class="text-xs text-gray-500">{{ $doc->user->jabatan ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusClasses = [
                                'pending' => 'bg-blue-100 text-blue-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'deleted' => 'bg-gray-100 text-gray-800',
                            ];
                            $statusLabels = [
                                'pending' => 'Upload',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'deleted' => 'Terhapus',
                            ];
                            $status = $doc->status ?? 'pending';
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">
                            @if($status == 'approved')
                                <div>Disetujui oleh:</div>
                                <div class="font-medium text-gray-900">{{ $doc->approver->name ?? '-' }}</div>
                            @elseif($status == 'rejected')
                                <div class="text-red-600">{{ $doc->rejected_reason ?? '-' }}</div>
                            @elseif($status == 'deleted')
                                Deleted by: {{ $doc->user->name }}
                            @else
                                <span class="italic text-gray-400">Menunggu</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>Upload:</div>
                        <div>{{ $doc->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        @if(auth()->user()->hasAnyRole(['Admin', 'Supervisor']))
                            <div class="flex space-x-2">
                                @if($status == 'pending')
                                <form action="{{ route('buku-saku.approve', $doc->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded text-xs">
                                        Acc
                                    </button>
                                </form>
                                <form action="{{ route('buku-saku.reject', $doc->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-1 px-3 rounded text-xs">
                                        Reject
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('buku-saku.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-xs" title="Hapus Dokumen">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            @if($status == 'pending')
                                <span class="text-gray-400 text-xs italic">Menunggu Persetujuan</span>
                            @else
                                <span class="text-gray-400 text-xs">No Action</span>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @if($documents->isEmpty())
        <div class="text-center py-10 text-gray-500">
            Belum ada riwayat dokumen.
        </div>
        @endif
    </div>
</x-buku-saku-layout>
