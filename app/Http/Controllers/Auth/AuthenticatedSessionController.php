<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'recaptchaEnabled' => $this->recaptchaEnabled(),
            'recaptchaType' => (string) config('services.recaptcha.type', 'v3'),
            'recaptchaSiteKey' => (string) config('services.recaptcha.site_key'),
            'recaptchaAction' => (string) config('services.recaptcha.login_action', 'login'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => $this->recaptchaEnabled()
                ? ['required', 'string']
                : ['nullable', 'string'],
        ]);

        if ($this->recaptchaEnabled() && ! $this->verifyRecaptcha($request, (string) $validated['g-recaptcha-response'])) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Verifikasi reCAPTCHA tidak valid.',
            ]);
        }

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

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

    private function recaptchaEnabled(): bool
    {
        return (bool) config('services.recaptcha.enabled')
            && filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret_key'));
    }

    private function verifyRecaptcha(Request $request, string $token): bool
    {
        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post((string) config('services.recaptcha.verify_url'), [
                    'secret' => (string) config('services.recaptcha.secret_key'),
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            if (! $response->ok()) {
                return false;
            }

            if (! (bool) $response->json('success', false)) {
                return false;
            }

            if ($this->isRecaptchaV3()) {
                $expectedAction = (string) config('services.recaptcha.login_action', 'login');
                $action = (string) $response->json('action', '');
                $score = (float) $response->json('score', 0);
                $threshold = (float) config('services.recaptcha.score_threshold', 0.5);

                return $action === $expectedAction && $score >= $threshold;
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function isRecaptchaV3(): bool
    {
        return strtolower((string) config('services.recaptcha.type', 'v3')) === 'v3';
    }
}
