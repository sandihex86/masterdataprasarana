<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Master Data Prasarana DJKA</title>
    @if ($recaptchaEnabled)
        @if ($recaptchaType === 'v3')
            <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
        @else
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endif
    @endif
    <style>
        :root {
            color-scheme: light;
            --bg: #eef2f6;
            --panel: rgba(255, 255, 255, 0.92);
            --line: rgba(15, 23, 42, 0.08);
            --text: #0f172a;
            --muted: #64748b;
            --accent: #0f766e;
            --accent-deep: #115e59;
            --danger-soft: rgba(180, 35, 24, 0.1);
            --danger-text: #b42318;
            --shadow: 0 24px 64px rgba(15, 23, 42, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.14), transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, var(--bg) 100%);
        }

        .shell {
            width: min(420px, 100%);
            position: relative;
            z-index: 1;
        }

        .panel {
            position: relative;
            overflow: hidden;
            padding: 32px 30px 28px;
            border-radius: 28px;
            border: 1px solid var(--line);
            background: var(--panel);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            animation: rise 700ms ease-out;
        }

        .panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(140deg, rgba(255, 255, 255, 0.45), transparent 42%),
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 28%);
            pointer-events: none;
        }

        .heading {
            margin-bottom: 24px;
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.5rem, 4.6vw, 2.05rem);
            line-height: 1.05;
            letter-spacing: -0.035em;
        }

        .errors {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 18px;
            font-size: 0.94rem;
            line-height: 1.6;
        }

        .errors {
            background: var(--danger-soft);
            border: 1px solid rgba(180, 35, 24, 0.14);
            color: var(--danger-text);
        }

        .security-note {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(248, 250, 252, 0.92);
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.6;
            text-align: center;
        }

        .field-group {
            margin-bottom: 16px;
        }

        .label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        label {
            font-size: 0.94rem;
            font-weight: 600;
        }

        .input-wrap {
            position: relative;
        }

        .field {
            width: 100%;
            padding: 15px 16px;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: rgba(255, 255, 255, 0.98);
            font: inherit;
            color: inherit;
            transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        }

        .field:focus {
            outline: none;
            border-color: rgba(15, 118, 110, 0.42);
            box-shadow: 0 0 0 5px rgba(15, 118, 110, 0.12);
            transform: translateY(-1px);
        }

        .recaptcha-note {
            margin: 6px 0 18px;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.5;
            text-align: center;
        }

        .recaptcha-wrap {
            margin: 18px 0 12px;
            display: flex;
            justify-content: center;
        }

        .recaptcha-shell {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 16px;
            border-radius: 22px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.05), rgba(14, 165, 233, 0.08));
        }

        .recaptcha-v3-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 54px;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.8);
            color: var(--accent-deep);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0 22px;
            color: var(--muted);
            font-size: 0.94rem;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
        }

        .button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 18px;
            border: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--accent), #0ea5e9);
            color: white;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(15, 118, 110, 0.16);
            transition: transform 160ms ease, box-shadow 160ms ease, filter 160ms ease;
        }

        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(15, 118, 110, 0.18);
            filter: saturate(1.04);
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.985);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 16px;
            }

            .panel {
                padding: 26px 20px 22px;
                border-radius: 24px;
            }
        }

        @media (max-width: 380px) {
            .recaptcha-shell {
                padding: 10px 6px;
            }

            .g-recaptcha {
                transform: scale(0.92);
                transform-origin: center top;
            }
        }
    </style>
</head>
<body>
    <main class="shell">
        <section class="panel">
            <div class="heading">
                <h1>Master Data Prasarana DJKA</h1>
            </div>

            @if ($errors->any())
                <div class="errors">{{ $errors->first() }}</div>
            @endif

            @unless ($recaptchaEnabled)
                <div class="security-note">
                    reCAPTCHA belum aktif. Isi <code>RECAPTCHA_ENABLED</code>, <code>RECAPTCHA_SITE_KEY</code>, dan <code>RECAPTCHA_SECRET_KEY</code>.
                </div>
            @endunless

            <form method="post" action="/login">
                @csrf
                <input id="gRecaptchaResponse" name="g-recaptcha-response" type="hidden" value="{{ old('g-recaptcha-response') }}">

                <div class="field-group">
                    <div class="label-row">
                        <label for="email">Email</label>
                    </div>
                    <div class="input-wrap">
                        <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Email">
                    </div>
                </div>

                <div class="field-group">
                    <div class="label-row">
                        <label for="password">Password</label>
                    </div>
                    <div class="input-wrap">
                        <input class="field" id="password" name="password" type="password" required autocomplete="current-password" placeholder="Password">
                    </div>
                </div>

                @if ($recaptchaEnabled)
                    <div class="recaptcha-wrap">
                        <div class="recaptcha-shell">
                            @if ($recaptchaType === 'v3')
                                <div class="recaptcha-v3-badge">reCAPTCHA aktif</div>
                            @else
                                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
                            @endif
                        </div>
                    </div>
                    <p class="recaptcha-note">
                        @if ($recaptchaType === 'v3')
                            Verifikasi keamanan Google reCAPTCHA v3 berjalan otomatis.
                        @else
                            Verifikasi keamanan Google reCAPTCHA.
                        @endif
                    </p>
                @endif

                <label class="remember">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Pertahankan sesi login</span>
                </label>

                <button class="button" type="submit">Masuk</button>
            </form>
        </section>
    </main>
    @if ($recaptchaEnabled && $recaptchaType === 'v3')
        <script>
            (() => {
                const form = document.querySelector('form[action="/login"]');
                const responseField = document.getElementById('gRecaptchaResponse');

                if (!form || !responseField || typeof grecaptcha === 'undefined') {
                    return;
                }

                form.addEventListener('submit', (event) => {
                    if (responseField.value !== '') {
                        return;
                    }

                    event.preventDefault();

                    grecaptcha.ready(() => {
                        grecaptcha.execute('{{ $recaptchaSiteKey }}', { action: '{{ $recaptchaAction }}' })
                            .then((token) => {
                                responseField.value = token;
                                form.submit();
                            });
                    });
                });
            })();
        </script>
    @endif
</body>
</html>
