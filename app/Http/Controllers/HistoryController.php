<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Admin & Supervisor get all history
        if ($user->hasRole(['Admin', 'Supervisor'])) {
            $logs = AuditLog::with('user')->latest()->paginate(20);
        } else {
            // Others only see their own
            $logs = AuditLog::with('user')->where('user_id', $user->id)->latest()->paginate(20);
        }

        return view('history', compact('logs'));
    }
}
