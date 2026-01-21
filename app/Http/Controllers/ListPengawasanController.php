<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListPengawasanController extends Controller
{
    private function canWriteForModule($user): bool
    {
        if ($user->hasRole(['Admin', 'Supervisor'])) {
            return true;
        }

        $module = Module::where('slug', 'list-pengawasan')->first();
        if (!$module) {
            return false;
        }

        return ModuleAccess::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->where('can_write', true)
            ->exists();
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $module = Module::where('slug', 'list-pengawasan')->first();
        $hasAccess = $user->hasRole(['Admin', 'Supervisor']);

        if (!$hasAccess && $module) {
            $hasAccess = ModuleAccess::where('user_id', $user->id)
                ->where('module_id', $module->id)
                ->where('can_read', true)
                ->exists();
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized action.');
        }

        $search = $request->query('search', '');

        $pengawasQuery = DB::table('pengawas')
            ->select('id', 'name', 'tanggal', 'status')
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $pengawasQuery->where('name', 'like', '%' . $search . '%');
        }

        $pengawas = $pengawasQuery->get();

        $items = $pengawas->map(function ($p) {
            $labels = DB::table('pengawas_keterangan')
                ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_keterangan.keterangan_option_id')
                ->where('pengawas_keterangan.pengawas_id', $p->id)
                ->pluck('keterangan_options.name')
                ->toArray();

            return [
                'id' => $p->id,
                'nama' => $p->name,
                'tanggal' => $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') : '-',
                'status' => $p->status,
                'keterangan' => $labels,
            ];
        })->toArray();

        $options = DB::table('keterangan_options')->orderBy('name')->pluck('name')->toArray();

        return view('list-pengawasan.index', compact('items', 'options'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['array'],
            'keterangan.*' => ['string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
        ]);

        $pengawasId = DB::table('pengawas')->insertGetId([
            'name' => $data['nama'],
            'tanggal' => $data['tanggal'] ?? null,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $labels = $data['keterangan'] ?? [];
        foreach ($labels as $label) {
            $opt = DB::table('keterangan_options')->where('name', $label)->first();
            if (!$opt) {
                $optId = DB::table('keterangan_options')->insertGetId([
                    'name' => $label,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $optId = $opt->id;
            }
            DB::table('pengawas_keterangan')->updateOrInsert(
                ['pengawas_id' => $pengawasId, 'keterangan_option_id' => $optId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return response()->json(['message' => 'Pengawas berhasil ditambahkan', 'id' => $pengawasId]);
    }

    public function updateKeterangan(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'keterangan' => ['array'],
            'keterangan.*' => ['string', 'max:255'],
        ]);

        $labels = $data['keterangan'] ?? [];

        DB::table('pengawas_keterangan')->where('pengawas_id', $id)->delete();

        foreach ($labels as $label) {
            $opt = DB::table('keterangan_options')->where('name', $label)->first();
            if (!$opt) {
                $optId = DB::table('keterangan_options')->insertGetId([
                    'name' => $label,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $optId = $opt->id;
            }
            DB::table('pengawas_keterangan')->updateOrInsert(
                ['pengawas_id' => $id, 'keterangan_option_id' => $optId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return response()->json(['message' => 'Keterangan berhasil diperbarui']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        DB::table('pengawas_keterangan')->where('pengawas_id', $id)->delete();
        DB::table('pengawas')->where('id', $id)->delete();

        return response()->json(['message' => 'Pengawas berhasil dihapus']);
    }

    public function renameOption(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'old_name' => ['required', 'string', 'max:255'],
            'new_name' => ['required', 'string', 'max:255'],
        ]);

        $exists = DB::table('keterangan_options')->where('name', $data['new_name'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Nama keterangan sudah ada'], 422);
        }

        $updated = DB::table('keterangan_options')->where('name', $data['old_name'])->update([
            'name' => $data['new_name'],
            'updated_at' => now(),
        ]);

        if (!$updated) {
            return response()->json(['message' => 'Keterangan tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Keterangan berhasil diubah']);
    }

    public function deleteOption(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $opt = DB::table('keterangan_options')->where('name', $data['name'])->first();
        if (!$opt) {
            return response()->json(['message' => 'Keterangan tidak ditemukan'], 404);
        }

        DB::table('pengawas_keterangan')->where('keterangan_option_id', $opt->id)->delete();
        DB::table('keterangan_options')->where('id', $opt->id)->delete();

        return response()->json(['message' => 'Keterangan berhasil dihapus']);
    }
}
