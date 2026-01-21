<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleAccess;
use Illuminate\Support\Facades\Auth;

class ListPengawasanController extends Controller
{
    public function index()
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

        $items = [
            [
                'nama' => 'Depe',
                'tanggal' => '19-01-2026',
                'status' => 'Active',
                'keterangan' => [
                    'Izin (WIR)',
                    'Permit to Work ( PTW )',
                    'Pekerja',
                    'Alat Pelindung Diri',
                ],
            ],
            [
                'nama' => 'Depe',
                'tanggal' => '19-01-2026',
                'status' => 'Active',
                'keterangan' => [
                    'Izin (WIR)',
                    'Permit to Work ( PTW )',
                    'Pekerja',
                    'Alat Pelindung Diri',
                ],
            ],
        ];

        return view('list-pengawasan.index', compact('items'));
    }
}

