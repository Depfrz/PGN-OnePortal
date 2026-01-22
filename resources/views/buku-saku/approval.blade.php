<x-buku-saku-layout>
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800">Pengecekan File</h2>
        <p class="text-gray-500 text-xs">Daftar dokumen dan riwayat.</p>
    </div>

    <div class="bg-white rounded-lg shadow w-full">
        <div class="overflow-x-auto w-full">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            JUDUL DOKUMEN
                        </th>
                        <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            UPLOADER
                        </th>
                        <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            MASA BERLAKU
                        </th>
                        <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            TANGGAL UPLOAD
                        </th>
                        <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($documents as $doc)
                    <tr>
                        <td class="px-2 py-1.5 whitespace-normal min-w-[250px]">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded flex items-center justify-center text-gray-500 font-bold text-[10px] uppercase mt-1">
                                    {{ $doc->file_type }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-bold text-gray-900">
                                        <a href="{{ route('buku-saku.preview', $doc->id) }}" target="_blank" class="hover:text-blue-600 hover:underline">
                                            {{ $doc->title }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $doc->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-900">{{ $doc->user->name ?? 'Unknown' }}</span>
                                <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full w-fit mt-0.5">{{ $doc->user->jabatan ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            @if($doc->valid_until)
                                @php
                                    $now = now();
                                    $diff = $now->diffInDays($doc->valid_until, false);
                                    $isExpired = $diff < 0;
                                    $color = $isExpired ? 'text-red-600' : ($diff < 30 ? 'text-yellow-600' : 'text-green-600');
                                @endphp
                                <div class="text-xs font-medium {{ $color }}">
                                    {{ $doc->valid_until->format('d/m/Y') }}
                                </div>
                                <div class="text-[10px] text-gray-500">
                                    @if($isExpired)
                                        Expired
                                    @else
                                        {{ $diff }} hari lagi
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                            {{ $doc->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium">
                            <div class="flex space-x-2">
                                @if(auth()->user()->id === $doc->user_id || auth()->user()->hasAnyRole(['Admin', 'Supervisor']))
                                    <!-- Edit Button -->
                                    <a href="{{ route('buku-saku.edit', $doc->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-2 rounded text-[10px] flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    
                                    <!-- Delete Button -->
                                    <form action="{{ route('buku-saku.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded text-[10px] flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('buku-saku.preview', $doc->id) }}" target="_blank" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-1 px-2 rounded text-[10px] flex items-center" title="Lihat Dokumen">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Lihat
                                </a>

                                <a href="{{ route('buku-saku.download', $doc->id) }}" class="text-gray-500 hover:text-gray-700 ml-1 p-1" title="Download">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0l-4 4m4-4v12" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-buku-saku-layout>
