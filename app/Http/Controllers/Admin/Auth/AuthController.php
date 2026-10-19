<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(LoginRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();
        $logger->log('login', "{$user->name} signed in", $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request, ActivityLogger $logger): RedirectResponse
    {
        if ($user = $request->user()) {
            $logger->log('logout', "{$user->name} signed out", $user);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been signed out.');
    }
}
