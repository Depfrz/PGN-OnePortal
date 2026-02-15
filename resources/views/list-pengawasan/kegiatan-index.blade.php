<x-dashboard-layout title="Daftar Kegiatan" :can-write="$canWrite" :lp-permissions="$lpPermissions">
    <div x-data="kegiatanApp({
        canWrite: {{ Js::from($canWrite ?? false) }},
        lpPerms: {{ Js::from($lpPermissions ?? []) }},
        projectId: {{ Js::from($project->id) }},
        currentType: {{ Js::from($type ?? 'Pipa Baja') }},
        items: {{ Js::from($activities->items()) }}
    })" class="p-4 sm:p-6">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('list-pengawasan.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                        </svg>
                        List Pengawasan
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">{{ $project->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <template x-teleport="body">
            <div x-show="toast.show"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed top-5 right-5 z-[10000]"
                 style="display: none;">
                <div class="flex items-center gap-3 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-2xl px-4 py-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                            <path fill-rule="evenodd" d="M16.704 4.294a.75.75 0 01.002 1.06l-8.25 8.25a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 011.06-1.06l3.22 3.22 7.72-7.72a.75.75 0 011.058 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="toast.message"></div>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="dialog.open"
                 x-transition
                 class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                 style="display: none;">
                <div class="bg-white rounded-2xl p-6 w-[92vw] max-w-md shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700 max-h-[85vh] overflow-y-auto">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="dialog.title"></div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line" x-text="dialog.message"></div>
                        </div>
                        <button @click="closeDialog()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button @click="closeDialog()" class="px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">OK</button>
                    </div>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="confirm.open"
                 x-transition
                 class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                 style="display: none;">
                <div class="bg-white rounded-2xl p-6 w-[92vw] max-w-md shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700 max-h-[85vh] overflow-y-auto">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="confirm.title"></div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line" x-text="confirm.message"></div>
                        </div>
                        <button @click="closeConfirm()" :disabled="confirm.busy" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button @click="closeConfirm()" :disabled="confirm.busy" class="px-4 py-2.5 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">Batal</button>
                        <button @click="runConfirm()" :disabled="confirm.busy" class="px-4 py-2.5 rounded-lg text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed" :class="confirm.confirmClass">
                            <span x-show="!confirm.busy" x-text="confirm.confirmText"></span>
                            <span x-show="confirm.busy">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Daftar Kegiatan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Proyek: {{ $project->name }}</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <form action="{{ url()->current() }}" method="GET" class="w-full sm:w-[280px]">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="status" value="{{ $status ?? '' }}">
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Kegiatan..." class="w-full bg-[#f7f8f9] border border-[#d6d9de] rounded-lg pl-4 pr-10 py-2.5 text-sm text-gray-800 placeholder:text-[#6f7a86] shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-400">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-[#6f7a86] dark:text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.3 6.3a7.5 7.5 0 0 0 10.35 10.35Z" />
                                </svg>
                            </div>
                        </div>
                    </form>
                    <details class="relative flex-shrink-0" x-show="lpPerms.filter_status_kegiatan" style="display: none;">
                        <summary class="list-none cursor-pointer relative inline-flex items-center justify-center w-10 h-10 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-.293.707L12 11.414V16a1 1 0 01-1.447.894l-2-1A1 1 0 018 15V11.414L3.293 6.707A1 1 0 013 6V4z" />
                            </svg>
                            @if(!empty($status))
                                <span class="absolute -top-1 -right-1 inline-flex h-3 w-3 rounded-full bg-blue-600"></span>
                            @endif
                        </summary>
                        <div class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white shadow-lg z-50 dark:bg-gray-800 dark:border-gray-700">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Filter Status</div>
                            <div class="py-1">
                                <a href="{{ request()->fullUrlWithoutQuery('status') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 {{ empty($status) ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">Semua</a>
                                <a href="{{ request()->fullUrlWithQuery(['status' => 'Belum Dikerjakan']) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($status ?? '') === 'Belum Dikerjakan' ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">Belum Dikerjakan</a>
                                <a href="{{ request()->fullUrlWithQuery(['status' => 'Sedang Dikerjakan']) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($status ?? '') === 'Sedang Dikerjakan' ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">Sedang Dikerjakan</a>
                                <a href="{{ request()->fullUrlWithQuery(['status' => 'Selesai']) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($status ?? '') === 'Selesai' ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">Selesai</a>
                            </div>
                        </div>
                    </details>
                </div>
                
                <button x-show="canWrite && lpPerms.tambah_kegiatan && lpPerms.bulk_kegiatan" :disabled="!canWrite || !lpPerms.tambah_kegiatan || !lpPerms.bulk_kegiatan" @click="openBulkAdd()" style="background-color: #16a34a !important;" class="w-full sm:w-auto bg-green-600 text-white font-medium text-sm py-2.5 px-6 rounded-lg hover:bg-green-700 transition-all shadow-md hover:shadow-lg inline-flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Bulk
                </button>
                <button x-show="canWrite && lpPerms.tambah_kegiatan" :disabled="!canWrite || !lpPerms.tambah_kegiatan" @click="openAdd()" class="w-full sm:w-auto bg-blue-600 text-white font-medium text-sm py-2.5 px-6 rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg inline-flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Kegiatan
                </button>
            </div>
        </div>

        <div class="mb-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-end gap-2">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center overflow-x-auto w-full sm:w-auto">
                @foreach($pipeTypes as $pt)
                <li class="mr-2">
                    <a href="{{ request()->fullUrlWithQuery(['type' => $pt->name]) }}" class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 {{ ($type ?? '') == $pt->name ? 'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent text-gray-500 dark:text-gray-400' }}">
                        {{ $pt->name }}
                    </a>
                </li>
                @endforeach
            </ul>
            <div class="mb-2 flex gap-3 shrink-0" x-show="canWrite && (lpPerms.kelola_jenis_pekerjaan || lpPerms.kelola_template_bulk)" style="display: none;">
                 <button x-show="lpPerms.kelola_jenis_pekerjaan" @click="openManagePipeTypes()" class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline flex items-center" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Kelola Jenis Pekerjaan
                 </button>
                 <button x-show="lpPerms.kelola_template_bulk" @click="openManageTemplates()" class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline flex items-center" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    Kelola Template Bulk
                 </button>
            </div>
        </div>

        <div x-show="selectedIds.length > 0 && canWrite && lpPerms.bulk_kegiatan" class="mb-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between dark:border-blue-900/40 dark:bg-blue-900/20" style="display: none;">
            <div class="text-sm font-semibold text-blue-800 dark:text-blue-200">
                <span x-text="selectedIds.length"></span> kegiatan dipilih
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center w-full sm:w-auto">
                <button type="button" @click="clearSelection()" class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">Batal</button>
                <select x-model="bulkStatus" class="w-full sm:w-auto bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                    <option value="Belum Dimulai">Belum Dimulai</option>
                    <option value="Belum Dikerjakan">Belum Dikerjakan</option>
                    <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                    <option value="Selesai">Selesai</option>
                </select>
                <button type="button" @click="bulkUpdateStatus()" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Ubah Status</button>
                <button type="button" x-show="lpPerms.hapus_kegiatan" :disabled="!lpPerms.hapus_kegiatan" @click="bulkDelete()" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">Hapus</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Mobile View -->
            <div class="block sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($activities as $item)
                <div class="p-4 flex flex-col gap-2">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0">
                            <div class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-200 mb-1">
                                No {{ $activities->firstItem() + $loop->index }}
                            </div>
                            <a href="{{ route('list-pengawasan.kegiatan.show', $item->id) }}" class="text-base font-semibold text-gray-900 hover:text-blue-600 hover:underline dark:text-white line-clamp-2">
                                {{ $item->nama_kegiatan }}
                            </a>
                            <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}</span>
                                @php
                                    $statusColorsMobile = [
                                        'Belum Dimulai' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'Belum Dikerjakan' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'Sedang Dikerjakan' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                        'Selesai' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    ];
                                    $colorClassMobile = $statusColorsMobile[$item->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $colorClassMobile }}">
                                    {{ $item->status }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-2" x-show="canWrite">
                            <input type="checkbox" x-show="canWrite && lpPerms.bulk_kegiatan" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800" @click.stop="toggleSelected({{ $item->id }})" :checked="selectedIds.includes({{ $item->id }})" style="display: none;">
                            <button x-show="lpPerms.hapus_kegiatan" :disabled="!lpPerms.hapus_kegiatan" @click="openDelete({{ Js::from($item) }})" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/20 dark:hover:text-red-400 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    Belum ada kegiatan.
                </div>
                @endforelse
            </div>

            <!-- Desktop View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-12 text-center">
                                <input type="checkbox" x-show="canWrite" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800" @click.stop="toggleSelectAll()" :checked="bulkAllSelected" style="display: none;">
                            </th>
                            <th scope="col" class="px-6 py-3 w-16 text-center">No</th>
                            <th scope="col" class="px-6 py-3">Nama Kegiatan</th>
                            <th scope="col" class="px-6 py-3">Tanggal Mulai</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $item)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-show="canWrite && lpPerms.bulk_kegiatan" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800" @click.stop="toggleSelected({{ $item->id }})" :checked="selectedIds.includes({{ $item->id }})" style="display: none;">
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-gray-700 dark:text-gray-200">
                                {{ $activities->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <a href="{{ route('list-pengawasan.kegiatan.show', $item->id) }}" class="hover:text-blue-600 hover:underline">
                                    {{ $item->nama_kegiatan }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'Belum Dimulai' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'Belum Dikerjakan' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'Sedang Dikerjakan' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                        'Selesai' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    ];
                                    $colorClass = $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button x-show="canWrite && lpPerms.hapus_kegiatan" :disabled="!canWrite || !lpPerms.hapus_kegiatan" @click="openDelete({{ Js::from($item) }})" class="font-medium text-red-600 dark:text-red-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                Belum ada kegiatan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="sm:hidden flex justify-center">
                    @php
                        $current = $activities->currentPage();
                        $last = $activities->lastPage();
                        $pages = [];

                        if ($last <= 7) {
                            $pages = range(1, $last);
                        } else {
                            $pages[] = 1;
                            $start = max(2, $current - 1);
                            $end = min($last - 1, $current + 1);

                            if ($start > 2) {
                                $pages[] = null;
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                $pages[] = $i;
                            }
                            if ($end < $last - 1) {
                                $pages[] = null;
                            }
                            $pages[] = $last;
                        }
                    @endphp

                    <nav class="inline-flex rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm dark:bg-gray-800 dark:border-gray-700" aria-label="Pagination">
                        <a
                            href="{{ $activities->previousPageUrl() ?: '#' }}"
                            class="w-12 h-10 inline-flex items-center justify-center text-gray-600 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 border-r border-gray-200 dark:border-gray-700 {{ $activities->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}"
                            aria-label="Previous"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>

                        @foreach ($pages as $p)
                            @if ($p === null)
                                <span class="w-12 h-10 inline-flex items-center justify-center text-gray-400 border-r border-gray-200 dark:border-gray-700 dark:text-gray-500">…</span>
                            @else
                                <a
                                    href="{{ $activities->url($p) }}"
                                    class="w-12 h-10 inline-flex items-center justify-center text-sm font-semibold border-r border-gray-200 dark:border-gray-700 {{ $p === $current ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700' }}"
                                    @if ($p === $current) aria-current="page" @endif
                                >
                                    {{ $p }}
                                </a>
                            @endif
                        @endforeach

                        <a
                            href="{{ $activities->nextPageUrl() ?: '#' }}"
                            class="w-12 h-10 inline-flex items-center justify-center text-gray-600 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 {{ $current >= $last ? 'pointer-events-none opacity-50' : '' }}"
                            aria-label="Next"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </nav>
                </div>

                <div class="hidden sm:block">
                    {{ $activities->links() }}
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div x-show="addModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-md shadow-2xl dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Kegiatan Baru</h3>
                    <button @click="addModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kegiatan</label>
                        <input x-model="newKegiatan.nama_kegiatan" type="text" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                        <textarea x-model="newKegiatan.deskripsi" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="addModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Batal</button>
                        <button @click="saveKegiatan()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Add Modal (Sidebar) -->
        <template x-teleport="body">
            <div x-show="bulkModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed inset-y-0 right-0 z-[9999] w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl flex flex-col border-l border-gray-200 dark:border-gray-700"
                 style="display: none;">
                
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-white dark:bg-gray-800">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Kegiatan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Jenis: <span x-text="currentType"></span></p>
                    </div>
                    <button @click="bulkModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <button @click="toggleBulkAll()" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-2 transition-colors">
                        <div class="w-5 h-5 rounded border border-blue-600 dark:border-blue-400 flex items-center justify-center bg-white dark:bg-gray-800">
                            <svg x-show="selectedBulk.length === bulkActivities.length" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            <div x-show="selectedBulk.length > 0 && selectedBulk.length < bulkActivities.length" class="w-2.5 h-2.5 bg-blue-600 dark:bg-blue-400 rounded-sm"></div>
                        </div>
                        <span x-text="selectedBulk.length === bulkActivities.length ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                    </button>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400" x-text="selectedBulk.length + ' item dipilih'"></span>
                </div>

                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-white dark:bg-gray-800">
                    <div class="flex flex-col space-y-1">
                        <template x-for="item in bulkActivities" :key="item">
                            <label class="flex items-center px-4 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors group border border-transparent hover:border-gray-100 dark:hover:border-gray-700">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :value="item" :checked="selectedBulk.includes(item)" @change="toggleBulkItem(item)" class="peer w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 transition-all">
                                </div>
                                <span class="ml-4 text-base text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors select-none" x-text="item"></span>
                            </label>
                        </template>
                        <div x-show="bulkActivities.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <p class="text-base text-gray-500 dark:text-gray-400">Tidak ada item kegiatan.</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50">
                    <button @click="bulkModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-all">Batal</button>
                    <button @click="saveBulkKegiatan()" :disabled="selectedBulk.length === 0" class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all flex items-center">
                        <span>Tambahkan</span>
                        <span x-show="selectedBulk.length > 0" class="ml-2 bg-green-500 text-white px-2 py-0.5 rounded text-xs font-bold" x-text="selectedBulk.length"></span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Backdrop for Sidebar -->
        <template x-teleport="body">
            <div x-show="bulkModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="bulkModal = false"
                 class="fixed inset-0 z-[9998] bg-black/50 backdrop-blur-sm"
                 style="display: none;">
            </div>
        </template>

        <!-- Error Modal -->
        <div x-show="errorModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" style="display: none;" x-transition>
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-sm shadow-2xl dark:bg-gray-800 border-2 border-red-100 dark:border-red-900/50">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Duplikasi Data!</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4" x-text="errorMessage"></p>
                    
                    <template x-if="errorList.length > 0">
                        <div class="mb-6 text-left bg-red-50 dark:bg-red-900/20 p-3 rounded-lg max-h-40 overflow-y-auto">
                            <p class="text-xs font-semibold text-red-700 dark:text-red-300 mb-2">Item berikut sudah ada:</p>
                            <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-400 space-y-1">
                                <template x-for="item in errorList" :key="item">
                                    <li x-text="item"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <button @click="errorModal = false" class="w-full px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800 transition-colors">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="deleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" style="display: none;">
            <div class="bg-white rounded-xl p-6 w-[92vw] max-w-sm shadow-2xl dark:bg-gray-800">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Hapus Kegiatan?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Apakah Anda yakin ingin menghapus kegiatan <span class="font-bold" x-text="selectedItem?.nama_kegiatan"></span>? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="flex justify-center space-x-3">
                        <button @click="deleteModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Batal</button>
                        <button @click="deleteKegiatan()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="pipeTypesModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" style="display: none;" x-transition>
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl shadow-2xl dark:bg-gray-800 max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Kelola Jenis Pekerjaan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tambah, edit, dan hapus jenis pekerjaan.</p>
                    </div>
                    <button @click="pipeTypesModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 mb-4">
                    <input type="text" x-model="newPipeTypeName" placeholder="Nama jenis pekerjaan baru" class="flex-1 bg-[#f7f8f9] border border-[#d6d9de] rounded-lg px-4 py-2.5 text-sm text-gray-800 placeholder:text-[#6f7a86] shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-400">
                    <button @click="createPipeType()" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Tambah</button>
                </div>

                <div class="max-h-[45vh] sm:max-h-[55vh] overflow-y-auto border border-gray-200 rounded-xl dark:border-gray-700">
                    <template x-for="pt in pipeTypes" :key="pt.id">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                            <div class="flex-1">
                                <div x-show="editPipeTypeId !== pt.id" class="text-sm font-medium text-gray-900 dark:text-white" x-text="pt.name"></div>
                                <div x-show="editPipeTypeId === pt.id" class="flex gap-2">
                                    <input type="text" x-model="editPipeTypeName" class="flex-1 bg-[#f7f8f9] border border-[#d6d9de] rounded-lg px-3 py-2 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                    <button @click="savePipeType(pt.id)" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
                                    <button @click="cancelEditPipeType()" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                <button x-show="editPipeTypeId !== pt.id" @click="startEditPipeType(pt)" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">Edit</button>
                                <button x-show="editPipeTypeId !== pt.id" @click="deletePipeType(pt.id)" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">Hapus</button>
                            </div>
                        </div>
                    </template>
                    <div x-show="pipeTypes.length === 0" class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada jenis pekerjaan.</div>
                </div>
            </div>
        </div>

        <div x-show="templatesModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" style="display: none;" x-transition>
            <div class="bg-white rounded-xl p-6 w-full max-w-3xl shadow-2xl dark:bg-gray-800 max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Kelola Template Bulk</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tambah, edit, dan hapus template kegiatan per jenis pekerjaan.</p>
                    </div>
                    <button @click="templatesModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="sm:w-64">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Jenis Pekerjaan</label>
                        <select x-model="templatesType" class="w-full bg-[#f7f8f9] border border-[#d6d9de] rounded-lg px-3 py-2.5 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <template x-for="pt in pipeTypes" :key="pt.id">
                                <option :value="pt.name" x-text="pt.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Tambah Template (pisahkan per baris)</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <textarea x-model="newTemplatesText" rows="2" placeholder="Contoh:\nSurvey\nPengelasan" class="flex-1 bg-[#f7f8f9] border border-[#d6d9de] rounded-lg px-3 py-2.5 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"></textarea>
                            <button @click="createTemplates()" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium self-start">Tambah</button>
                        </div>
                    </div>
                </div>

                <div class="max-h-[45vh] sm:max-h-[55vh] overflow-y-auto border border-gray-200 rounded-xl dark:border-gray-700">
                    <template x-for="tpl in templatesForType(templatesType)" :key="tpl.id">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                            <div class="flex-1">
                                <div x-show="editTemplateId !== tpl.id" class="text-sm text-gray-900 dark:text-white" x-text="tpl.nama_kegiatan"></div>
                                <div x-show="editTemplateId === tpl.id" class="flex gap-2">
                                    <input type="text" x-model="editTemplateName" class="flex-1 bg-[#f7f8f9] border border-[#d6d9de] rounded-lg px-3 py-2 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                    <button @click="saveTemplate(tpl.id)" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
                                    <button @click="cancelEditTemplate()" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                <button x-show="editTemplateId !== tpl.id" @click="startEditTemplate(tpl)" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">Edit</button>
                                <button x-show="editTemplateId !== tpl.id" @click="deleteTemplate(tpl.id)" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">Hapus</button>
                            </div>
                        </div>
                    </template>
                    <div x-show="templatesForType(templatesType).length === 0" class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada template untuk jenis ini.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kegiatanApp', (params) => ({
                canWrite: params.canWrite,
                lpPerms: params.lpPerms,
                projectId: params.projectId,
                currentType: params.currentType,
                items: params.items,
                search: '',
                addModal: false,
                bulkModal: false,
                deleteModal: false,
                errorModal: false,
                errorMessage: '',
                errorList: [],
                selectedItem: null,
                toast: { show: false, message: '', timeoutId: null },
                dialog: { open: false, title: '', message: '', variant: 'info' },
                confirm: { open: false, title: '', message: '', confirmText: 'Ya', confirmClass: 'bg-blue-600 hover:bg-blue-700', busy: false, onConfirm: null },
                newKegiatan: { nama_kegiatan: '', deskripsi: '', jenis_pipa: '' },
                bulkActivities: [],
                selectedBulk: [],
                pageIds: [],
                selectedIds: [],
                bulkStatus: 'Belum Dimulai',
                masterData: [],
                pipeTypes: [],
                pipeTypeMap: {},
                pipeTypesModal: false,
                templatesModal: false,
                newPipeTypeName: '',
                editPipeTypeId: null,
                editPipeTypeName: '',
                templatesType: '',
                newTemplatesText: '',
                editTemplateId: null,
                editTemplateName: '',

                init() {
                    this.newKegiatan.jenis_pipa = this.currentType;
                    this.templatesType = this.currentType;
                    this.pageIds = (this.items || []).map(i => i.id).filter(Boolean);
                    this.loadMasterData();
                },

                async loadMasterData() {
                    try {
                        const response = await fetch('/list-pengawasan/master-data', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!response.ok) return;
                        const data = await response.json();
                        this.masterData = Array.isArray(data) ? data : [];
                        this.pipeTypes = this.masterData.map(pt => ({ id: pt.id, name: pt.name }));
                        this.pipeTypeMap = this.masterData.reduce((acc, pt) => {
                            acc[pt.name] = pt;
                            return acc;
                        }, {});

                        if (this.pipeTypes.length > 0 && !this.pipeTypeMap[this.currentType]) {
                            this.currentType = this.pipeTypes[0].name;
                        }
                        this.newKegiatan.jenis_pipa = this.currentType;
                        if (!this.pipeTypeMap[this.templatesType] && this.pipeTypes.length > 0) {
                            this.templatesType = this.pipeTypes[0].name;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                templatesForType(typeName) {
                    const pt = this.pipeTypeMap[typeName];
                    const list = pt && Array.isArray(pt.template_kegiatans) ? pt.template_kegiatans : [];
                    return list.map(t => ({ id: t.id, nama_kegiatan: t.nama_kegiatan, master_jenis_pipa_id: t.master_jenis_pipa_id }));
                },
                
                showToast(message) {
                    this.toast.message = message;
                    this.toast.show = true;
                    if (this.toast.timeoutId) clearTimeout(this.toast.timeoutId);
                    this.toast.timeoutId = setTimeout(() => { this.toast.show = false; }, 2200);
                },
                openDialog(message, title = 'Informasi', variant = 'info') {
                    this.dialog = { open: true, title, message, variant };
                    document.body.style.overflow = 'hidden';
                },
                closeDialog() {
                    this.dialog.open = false;
                    document.body.style.overflow = '';
                },
                openConfirm({ title, message, confirmText = 'Ya', confirmClass = 'bg-blue-600 hover:bg-blue-700', onConfirm }) {
                    this.confirm = { open: true, title, message, confirmText, confirmClass, busy: false, onConfirm };
                    document.body.style.overflow = 'hidden';
                },
                closeConfirm() {
                    this.confirm.open = false;
                    this.confirm.busy = false;
                    this.confirm.onConfirm = null;
                    document.body.style.overflow = '';
                },
                async runConfirm() {
                    if (!this.confirm.open || this.confirm.busy || typeof this.confirm.onConfirm !== 'function') return;
                    this.confirm.busy = true;
                    try {
                        await this.confirm.onConfirm();
                    } finally {
                        this.confirm.busy = false;
                    }
                },

                csrfHeaders(extra = {}) {
                    return {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        ...extra,
                    };
                },
                toggleSelected(id) {
                    if (this.selectedIds.includes(id)) {
                        this.selectedIds = this.selectedIds.filter(v => v !== id);
                    } else {
                        this.selectedIds.push(id);
                    }
                },
                toggleSelectAll() {
                    const ids = this.pageIds || [];
                    const allSelected = ids.length > 0 && ids.every(id => this.selectedIds.includes(id));
                    this.selectedIds = allSelected ? [] : [...ids];
                },
                clearSelection() {
                    this.selectedIds = [];
                },
                get bulkAllSelected() {
                    const ids = this.pageIds || [];
                    return ids.length > 0 && ids.every(id => this.selectedIds.includes(id));
                },
                async bulkUpdateStatus() {
                    if (!this.canWrite || !this.lpPerms.bulk_kegiatan || this.selectedIds.length === 0) return;
                    if (!this.bulkStatus) return;
                    try {
                        const response = await fetch('/list-pengawasan/kegiatan/bulk-update', {
                            method: 'POST',
                            headers: this.csrfHeaders({ 'Content-Type': 'application/json' }),
                            body: JSON.stringify({ ids: this.selectedIds, status: this.bulkStatus })
                        });
                        const d = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            this.openDialog(d.message || 'Gagal mengubah status bulk', 'Gagal', 'error');
                            return;
                        }
                        this.items = (this.items || []).map(i => this.selectedIds.includes(i.id) ? { ...i, status: this.bulkStatus } : i);
                        this.showToast('Status kegiatan berhasil diperbarui');
                        this.clearSelection();
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                },
                async bulkDelete() {
                    if (!this.canWrite || !this.lpPerms.bulk_kegiatan || !this.lpPerms.hapus_kegiatan) return;
                    if (this.selectedIds.length === 0) return;
                    const ids = [...this.selectedIds];
                    this.openConfirm({
                        title: 'Hapus Kegiatan (Bulk)',
                        message: `Hapus ${ids.length} kegiatan terpilih? Tindakan ini tidak dapat dibatalkan.`,
                        confirmText: 'Ya, Hapus',
                        confirmClass: 'bg-red-600 hover:bg-red-700',
                        onConfirm: async () => {
                            try {
                                const response = await fetch('/list-pengawasan/kegiatan/bulk-delete', {
                                    method: 'POST',
                                    headers: this.csrfHeaders({ 'Content-Type': 'application/json' }),
                                    body: JSON.stringify({ ids })
                                });
                                const d = await response.json().catch(() => ({}));
                                if (!response.ok) {
                                    this.openDialog(d.message || 'Gagal menghapus kegiatan bulk', 'Gagal', 'error');
                                    return;
                                }
                                this.items = (this.items || []).filter(i => !ids.includes(i.id));
                                this.pageIds = (this.items || []).map(i => i.id).filter(Boolean);
                                this.clearSelection();
                                this.closeConfirm();
                                this.showToast('Kegiatan berhasil dihapus');
                            } catch (e) {
                                console.error(e);
                                this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                            }
                        }
                    });
                },

                openAdd() {
                    if (!this.canWrite) return;
                    this.newKegiatan = { nama_kegiatan: '', deskripsi: '', jenis_pipa: this.currentType };
                    this.addModal = true;
                },

                openBulkAdd() {
                    if (!this.canWrite || !this.lpPerms.bulk_kegiatan) return;
                    this.bulkActivities = this.templatesForType(this.currentType).map(t => t.nama_kegiatan);
                    this.selectedBulk = [];
                    this.bulkModal = true;
                },

                openManagePipeTypes() {
                    if (!this.canWrite || !this.lpPerms.kelola_jenis_pekerjaan) return;
                    this.loadMasterData();
                    this.newPipeTypeName = '';
                    this.cancelEditPipeType();
                    this.pipeTypesModal = true;
                },

                openManageTemplates() {
                    if (!this.canWrite || !this.lpPerms.kelola_template_bulk) return;
                    this.loadMasterData();
                    this.templatesType = this.currentType;
                    this.newTemplatesText = '';
                    this.cancelEditTemplate();
                    this.templatesModal = true;
                },

                startEditPipeType(pt) {
                    this.editPipeTypeId = pt.id;
                    this.editPipeTypeName = pt.name;
                },

                cancelEditPipeType() {
                    this.editPipeTypeId = null;
                    this.editPipeTypeName = '';
                },

                async createPipeType() {
                    if (!this.canWrite) return;
                    const name = (this.newPipeTypeName || '').trim();
                    if (!name) return;

                    try {
                        const response = await fetch('/list-pengawasan/master-jenis-pipa', {
                            method: 'POST',
                            headers: this.csrfHeaders({ 'Content-Type': 'application/json' }),
                            body: JSON.stringify({ name })
                        });
                        const d = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            this.openDialog(d.message || 'Gagal menambah jenis pekerjaan', 'Gagal', 'error');
                            return;
                        }
                        this.showToast('Jenis pekerjaan berhasil ditambahkan');
                        setTimeout(() => window.location.reload(), 350);
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                },

                async savePipeType(id) {
                    if (!this.canWrite) return;
                    const name = (this.editPipeTypeName || '').trim();
                    if (!name) return;

                    try {
                        const response = await fetch(`/list-pengawasan/master-jenis-pipa/${id}`, {
                            method: 'PUT',
                            headers: this.csrfHeaders({ 'Content-Type': 'application/json' }),
                            body: JSON.stringify({ name })
                        });
                        const d = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            this.openDialog(d.message || 'Gagal mengubah jenis pekerjaan', 'Gagal', 'error');
                            return;
                        }
                        this.showToast('Jenis pekerjaan berhasil diperbarui');
                        setTimeout(() => window.location.reload(), 350);
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                },

                async deletePipeType(id) {
                    if (!this.canWrite) return;
                    this.openConfirm({
                        title: 'Hapus Jenis Pekerjaan',
                        message: 'Hapus jenis pekerjaan ini? Template bulk di dalamnya juga akan terhapus.',
                        confirmText: 'Ya, Hapus',
                        confirmClass: 'bg-red-600 hover:bg-red-700',
                        onConfirm: async () => {
                            try {
                                const response = await fetch(`/list-pengawasan/master-jenis-pipa/${id}`, {
                                    method: 'DELETE',
                                    headers: this.csrfHeaders()
                                });
                                const d = await response.json().catch(() => ({}));
                                if (!response.ok) {
                                    this.openDialog(d.message || 'Gagal menghapus jenis pekerjaan', 'Gagal', 'error');
                                    return;
                                }
                                this.closeConfirm();
                                this.showToast('Jenis pekerjaan berhasil dihapus');
                                setTimeout(() => window.location.reload(), 350);
                            } catch (e) {
                                console.error(e);
                                this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                            }
                        }
                    });
                },

                startEditTemplate(tpl) {
                    this.editTemplateId = tpl.id;
                    this.editTemplateName = tpl.nama_kegiatan;
                },

                cancelEditTemplate() {
                    this.editTemplateId = null;
                    this.editTemplateName = '';
                },

                async createTemplates() {
                    if (!this.canWrite) return;
                    const lines = (this.newTemplatesText || '')
                        .split('\n')
                        .map(s => s.trim())
                        .filter(Boolean);
                    if (lines.length === 0) return;

                    const pt = this.pipeTypeMap[this.templatesType];
                    if (!pt) return;

                    try {
                        for (const nama_kegiatan of lines) {
                            const response = await fetch('/list-pengawasan/template-kegiatan', {
                                method: 'POST',
                                headers: this.csrfHeaders({ 'Content-Type': 'application/json' }),
                                body: JSON.stringify({ master_jenis_pipa_id: pt.id, nama_kegiatan })
                            });
                            const d = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                this.openDialog(d.message || `Gagal menambah template: ${nama_kegiatan}`, 'Gagal', 'error');
                                return;
                            }
                        }
                        this.showToast('Template berhasil ditambahkan');
                        this.newTemplatesText = '';
                        await this.loadMasterData();
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                },

                async saveTemplate(id) {
                    if (!this.canWrite) return;
                    const nama_kegiatan = (this.editTemplateName || '').trim();
                    if (!nama_kegiatan) return;

                    try {
                        const response = await fetch(`/list-pengawasan/template-kegiatan/${id}`, {
                            method: 'PUT',
                            headers: this.csrfHeaders({ 'Content-Type': 'application/json' }),
                            body: JSON.stringify({ nama_kegiatan })
                        });
                        const d = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            this.openDialog(d.message || 'Gagal mengubah template', 'Gagal', 'error');
                            return;
                        }
                        this.showToast('Template berhasil diperbarui');
                        this.cancelEditTemplate();
                        await this.loadMasterData();
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                },

                async deleteTemplate(id) {
                    if (!this.canWrite) return;
                    this.openConfirm({
                        title: 'Hapus Template',
                        message: 'Hapus template ini?',
                        confirmText: 'Ya, Hapus',
                        confirmClass: 'bg-red-600 hover:bg-red-700',
                        onConfirm: async () => {
                            try {
                                const response = await fetch(`/list-pengawasan/template-kegiatan/${id}`, {
                                    method: 'DELETE',
                                    headers: this.csrfHeaders()
                                });
                                const d = await response.json().catch(() => ({}));
                                if (!response.ok) {
                                    this.openDialog(d.message || 'Gagal menghapus template', 'Gagal', 'error');
                                    return;
                                }
                                this.closeConfirm();
                                this.showToast('Template berhasil dihapus');
                                await this.loadMasterData();
                            } catch (e) {
                                console.error(e);
                                this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                            }
                        }
                    });
                },

                toggleBulkItem(item) {
                    if (this.selectedBulk.includes(item)) {
                        this.selectedBulk = this.selectedBulk.filter(i => i !== item);
                    } else {
                        this.selectedBulk.push(item);
                    }
                },

                toggleBulkAll() {
                    if (this.selectedBulk.length === this.bulkActivities.length) {
                        this.selectedBulk = [];
                    } else {
                        this.selectedBulk = [...this.bulkActivities];
                    }
                },

                async saveBulkKegiatan() {
                    if (!this.canWrite || this.selectedBulk.length === 0) return;
                    try {
                        const response = await fetch(`/list-pengawasan/${this.projectId}/kegiatan/bulk`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                ...this.csrfHeaders()
                            },
                            body: JSON.stringify({
                                kegiatan: this.selectedBulk,
                                jenis_pipa: this.currentType
                            })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.bulkModal = false;
                            this.showToast('Kegiatan bulk berhasil ditambahkan');
                            setTimeout(() => window.location.reload(), 350);
                        } else {
                            const d = await response.json().catch(() => ({}));
                            console.error('Error response:', d);
                            
                            if (response.status === 422 && d.duplicates) {
                                this.errorMessage = d.message || 'Beberapa kegiatan sudah ada.';
                                this.errorList = d.duplicates;
                                this.errorModal = true;
                            } else {
                                this.openDialog(d.message || 'Gagal menambah kegiatan bulk', 'Gagal', 'error');
                            }
                        }
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                },

                async saveKegiatan() {
                    if (!this.canWrite) return;
                    try {
                        const response = await fetch(`/list-pengawasan/${this.projectId}/kegiatan`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                ...this.csrfHeaders()
                            },
                            body: JSON.stringify(this.newKegiatan)
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.addModal = false;
                            this.showToast('Kegiatan berhasil ditambahkan');
                            setTimeout(() => window.location.reload(), 350);
                        } else {
                            const d = await response.json().catch(() => ({}));
                            console.error('Error response:', d);

                            if (response.status === 422 && d.message.includes('sudah ada')) {
                                this.errorMessage = d.message;
                                this.errorList = [];
                                this.errorModal = true;
                            } else {
                                this.openDialog(d.message || 'Gagal menambah kegiatan', 'Gagal', 'error');
                            }
                        }
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                },

                openDelete(item) {
                    if (!this.canWrite) return;
                    this.selectedItem = item;
                    this.deleteModal = true;
                },

                async deleteKegiatan() {
                    if (!this.canWrite || !this.selectedItem) return;
                    try {
                        const response = await fetch(`/list-pengawasan/kegiatan/${this.selectedItem.id}`, {
                            method: 'DELETE',
                            headers: this.csrfHeaders()
                        });
                        
                        if (response.ok) {
                            this.deleteModal = false;
                            this.showToast('Kegiatan berhasil dihapus');
                            setTimeout(() => window.location.reload(), 350);
                        } else {
                            const d = await response.json().catch(() => ({}));
                            this.openDialog(d.message || 'Gagal menghapus kegiatan', 'Gagal', 'error');
                        }
                    } catch (e) {
                        console.error(e);
                        this.openDialog('Terjadi kesalahan sistem', 'Gagal', 'error');
                    }
                }
            }));
        });
    </script>
</x-dashboard-layout>
