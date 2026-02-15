<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Single Session Enforcement (Database + Manual Check)
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user) {
             // 1. Database Driver Cleanup (General)
             \Illuminate\Support\Facades\DB::table('sessions')
                 ->where('user_id', $user->id)
                 ->where('id', '!=', $request->session()->getId())
                 ->delete();

             // 2. Specific Admin/Supervisor/User/SuperUser Session Tracking
             if ($user->hasRole(['Admin', 'Supervisor', 'User', 'SuperUser'])) {
                 $user->forceFill([
                     'last_session_id' => Session::getId(),
                 ])->save();
             }
        }

        AuditService::log(Auth::user(), 'login', 'Auth', 'User logged in');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditService::log(Auth::user(), 'logout', 'Auth', 'User logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
