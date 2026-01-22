<x-buku-saku-layout>
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800">Riwayat Dokumen</h2>
        <p class="text-gray-500 text-xs">Daftar dokumen yang telah Anda unggah.</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            JUDUL DOKUMEN
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
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-2">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('buku-saku.preview', $doc->id) }}" target="_blank" class="hover:text-blue-600 hover:underline">
                                            {{ $doc->title }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $doc->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            @if($doc->valid_until)
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $diff = $now->diffInDays($doc->valid_until, false);
                                    $isExpired = $diff < 0;
                                    $color = $isExpired ? 'text-red-600' : ($diff < 365 ? ($diff < 30 ? 'text-red-500' : 'text-yellow-600') : 'text-green-600');
                                    // "countdown mulai dari 1 th hingga expired"
                                    // If > 1 year, maybe just show date. If < 1 year, show countdown.
                                    // Let's show both.
                                    $countdownText = $isExpired ? 'Expired' : ($diff . ' Hari Lagi');
                                @endphp
                                <div class="text-xs {{ $color }} font-medium">
                                    {{ $doc->valid_until->format('d/m/Y') }}<br>
                                    <span class="text-[10px]">({{ $countdownText }})</span>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                            {{ $doc->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-right text-xs font-medium">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('buku-saku.edit', $doc->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                <form action="{{ route('buku-saku.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($documents->isEmpty())
        <div class="text-center py-10 text-gray-500">
            Anda belum mengunggah dokumen apapun.
        </div>
        @endif
    </div>
</x-buku-saku-layout>