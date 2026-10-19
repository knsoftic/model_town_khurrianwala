<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailSettings;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function email(Request $request, MailSettings $mail): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $mail->apply();
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['email' => 'The reset email could not be sent. Please check the email settings or contact a Super Admin.']);
        }

        // Same response whether or not the account exists, so emails cannot be enumerated.
        return back()->with('status', 'If that email belongs to an admin account, a reset link has been sent.');
    }

    public function reset(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => $request->string('password'),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('admin.login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
