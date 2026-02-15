<x-buku-saku-layout>
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800">Riwayat Aktivitas Dokumen</h2>
        <p class="text-gray-500 text-xs">Log aktivitas penambahan, perubahan, dan penghapusan dokumen.</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-bold text-gray-500 uppercase tracking-wider">
                            WAKTU
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-bold text-gray-500 uppercase tracking-wider">
                            USER
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-bold text-gray-500 uppercase tracking-wider">
                            AKSI
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-bold text-gray-500 uppercase tracking-wider">
                            DESKRIPSI
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->created_at->format('d M Y H:i') }}
                            <br>
                            <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $log->user ? $log->user->name : 'Unknown' }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $color = 'gray';
                                $label = $log->action;
                                if ($log->action == 'create') {
                                    $color = 'green';
                                    $label = 'Tambah';
                                } elseif ($log->action == 'update') {
                                    $color = 'blue';
                                    $label = 'Edit';
                                } elseif ($log->action == 'delete') {
                                    $color = 'red';
                                    $label = 'Hapus';
                                }
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ ucfirst($label) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $log->description }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($logs->isEmpty())
        <div class="text-center py-10 text-gray-500">
            Belum ada aktivitas tercatat.
        </div>
        @endif

        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</x-buku-saku-layout>