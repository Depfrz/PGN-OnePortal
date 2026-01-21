<x-dashboard-layout>
    <div class="p-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-12 gap-4 px-6 py-4 text-[11px] font-semibold tracking-wider text-gray-500 uppercase">
                <div class="col-span-3">Nama</div>
                <div class="col-span-3">Tanggal</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-4 text-center">Keterangan</div>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($items as $item)
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-12 gap-4 items-start">
                            <div class="col-span-3 text-gray-900 font-semibold text-base">
                                {{ $item['nama'] }}
                            </div>
                            <div class="col-span-3 text-gray-700 text-sm">
                                {{ $item['tanggal'] }}
                            </div>
                            <div class="col-span-2">
                                <span class="inline-flex items-center rounded-full bg-green-100 px-4 py-1 text-sm font-semibold text-green-700 border border-green-200">
                                    {{ $item['status'] }}
                                </span>
                            </div>
                            <div class="col-span-4">
                                <div class="mx-auto max-w-xs rounded-xl bg-blue-50 border border-blue-100 px-4 py-3">
                                    <div class="space-y-3">
                                        @foreach($item['keterangan'] as $label)
                                            <label class="flex items-center justify-between gap-3 text-sm font-semibold text-blue-700">
                                                <span>{{ $label }}</span>
                                                <input type="checkbox" class="h-5 w-5 rounded border-2 border-blue-300 text-blue-600 focus:ring-blue-400" />
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-dashboard-layout>
