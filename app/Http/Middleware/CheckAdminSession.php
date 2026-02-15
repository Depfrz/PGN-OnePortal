<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Cek apakah user login dan memiliki role Admin/Supervisor/User/SuperUser
        if ($user && $user->hasRole(['Admin', 'Supervisor', 'User', 'SuperUser'])) {
            
            // 2. Cek apakah session ID saat ini cocok dengan yang di database
            // Jika last_session_id di DB null, kita izinkan (mungkin baru migrasi),
            // tapi idealnya akan di-set saat login.
            if ($user->last_session_id && $user->last_session_id !== Session::getId()) {
                
                // Jika tidak cocok, artinya ada login baru di tempat lain.
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun ini telah login di perangkat lain. Sesi Anda diakhiri demi keamanan.',
                ]);
            }
        }

        return $next($request);
    }
}
