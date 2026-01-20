<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagementUserController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Strictly restricted to Admin via route middleware
        // Double check for safety
        if (!$user->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        return view('management-user');
    }
}
