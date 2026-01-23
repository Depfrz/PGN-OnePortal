<x-buku-saku-layout>
    <div x-data="{ showDeleteModal: false, deleteUrl: '' }">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800">Pengecekan File</h2>
        <p class="text-gray-500 text-xs">Daftar dokumen dan riwayat.</p>
    </div>

    <div class="bg-white rounded-lg shadow w-full">
        <div class="overflow-x-auto w-full">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                            JUDUL DOKUMEN
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                            UPLOADER
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                            MASA BERLAKU
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                            TANGGAL UPLOAD
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($documents as $doc)
                    <tr>
                        <td class="px-4 py-3 whitespace-normal min-w-[300px]">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-500 font-bold text-xs uppercase mt-1">
                                    {{ $doc->file_type }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-base font-bold text-gray-900">
                                        <a href="{{ route('buku-saku.preview', $doc->id) }}" target="_blank" class="hover:text-blue-600 hover:underline">
                                            {{ $doc->title }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $doc->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">{{ $doc->user->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full w-fit mt-1">{{ $doc->user->jabatan ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($doc->valid_until)
                                @php
                                    $now = now();
                                    $diffInYears = $now->floatDiffInYears($doc->valid_until, false);
                                @endphp
                                @if($diffInYears < 0)
                                    <div class="text-sm font-bold text-red-600">
                                        Expired
                                    </div>
                                    <div class="text-xs text-red-500 font-semibold mt-0.5">
                                        {{ $doc->valid_until->format('d/m/Y') }}
                                    </div>
                                @elseif($diffInYears <= 1)
                                    <div class="text-sm font-bold text-red-600">
                                        <span class="countdown-timer tabular-nums" data-target="{{ $doc->valid_until->toIso8601String() }}">Hitung mundur...</span>
                                    </div>
                                    <div class="text-xs text-red-500 font-semibold mt-0.5">
                                        Exp: {{ $doc->valid_until->format('d/m/Y') }}
                                    </div>
                                @else
                                    <div class="text-sm font-bold text-green-600">
                                        {{ $doc->valid_until->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-green-500 font-semibold mt-0.5">
                                        Masih Berlaku
                                    </div>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $doc->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @if(auth()->user()->id === $doc->user_id || auth()->user()->hasAnyRole(['Admin', 'Supervisor']))
                                    <!-- Edit Button -->
                                    <a href="{{ route('buku-saku.edit', $doc->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1.5 px-3 rounded text-xs flex items-center transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    
                                    <!-- Delete Button -->
                                    <button type="button" @click="showDeleteModal = true; deleteUrl = '{{ route('buku-saku.destroy', $doc->id) }}'" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-3 rounded text-xs flex items-center transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                @endif
                                
                                <!-- Preview Button (Always visible) -->
                                <a href="{{ route('buku-saku.preview', $doc->id) }}" target="_blank" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-1.5 px-3 rounded text-xs flex items-center transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Lihat
                                </a>
                                
                                <!-- Download Button (Always visible) -->
                                <a href="{{ route('buku-saku.download', $doc->id) }}" class="text-gray-400 hover:text-gray-600 p-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
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

    <!-- Modal Delete -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[110] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" @click="showDeleteModal = false"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Hapus Dokumen</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan dan data akan hilang permanen.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form :action="deleteUrl" method="POST" class="inline-flex w-full justify-center sm:ml-3 sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto">Hapus</button>
                    </form>
                    <button type="button" @click="showDeleteModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
                </div>
            </div>
        </div>
    </div>
    </div></x-buku-saku-layout>
