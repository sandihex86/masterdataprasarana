<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'dummyAccounts' => config('app.debug') ? $this->dummyAccounts() : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function dummyAccounts(): array
    {
        return [
            ['name' => 'Superadmin Local', 'email' => 'superadmin@example.com', 'role' => UserRole::Superadmin->label(), 'password' => 'password'],
            ['name' => 'Admin Local', 'email' => 'admin@example.com', 'role' => UserRole::Admin->label(), 'password' => 'password'],
            ['name' => 'Operator Local', 'email' => 'operator@example.com', 'role' => UserRole::Operator->label(), 'password' => 'password'],
            ['name' => 'Verifikator Local', 'email' => 'verifikator@example.com', 'role' => UserRole::Verifikator->label(), 'password' => 'password'],
            ['name' => 'Viewer Local', 'email' => 'viewer@example.com', 'role' => UserRole::Viewer->label(), 'password' => 'password'],
        ];
    }
}
