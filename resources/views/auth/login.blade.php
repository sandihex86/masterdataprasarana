<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Master Data Prasarana DJKA</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f3ede5;
            --panel: rgba(255, 255, 255, 0.84);
            --line: rgba(15, 23, 42, 0.1);
            --text: #162033;
            --muted: #667085;
            --accent: #d14d1f;
            --accent-deep: #9f3416;
            --accent-soft: rgba(209, 77, 31, 0.14);
            --ok-soft: rgba(15, 118, 110, 0.12);
            --ok-text: #0f766e;
            --danger-soft: rgba(180, 35, 24, 0.1);
            --danger-text: #b42318;
            --shadow: 0 30px 80px rgba(15, 23, 42, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            overflow: hidden;
            padding: 24px;
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 20%, rgba(209, 77, 31, 0.22), transparent 24%),
                radial-gradient(circle at 80% 18%, rgba(15, 118, 110, 0.14), transparent 22%),
                radial-gradient(circle at 50% 100%, rgba(245, 158, 11, 0.14), transparent 28%),
                linear-gradient(180deg, #faf7f2 0%, var(--bg) 100%);
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: auto;
            border-radius: 999px;
            filter: blur(20px);
            opacity: 0.55;
            pointer-events: none;
            animation: float 12s ease-in-out infinite;
        }

        body::before {
            width: 340px;
            height: 340px;
            top: -80px;
            right: -60px;
            background: rgba(209, 77, 31, 0.18);
        }

        body::after {
            width: 280px;
            height: 280px;
            left: -70px;
            bottom: -50px;
            background: rgba(15, 118, 110, 0.14);
            animation-delay: -4s;
        }

        .shell {
            width: min(460px, 100%);
            position: relative;
            z-index: 1;
        }

        .panel {
            position: relative;
            overflow: hidden;
            padding: 34px 32px 28px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.68);
            background: var(--panel);
            box-shadow: var(--shadow);
            backdrop-filter: blur(22px);
            animation: rise 700ms ease-out;
        }

        .panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(140deg, rgba(255, 255, 255, 0.4), transparent 42%),
                radial-gradient(circle at top right, rgba(209, 77, 31, 0.12), transparent 26%);
            pointer-events: none;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.72);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent-deep);
        }

        .brand-mark {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent), #f59e0b);
            box-shadow: 0 0 0 6px rgba(209, 77, 31, 0.08);
        }

        h1 {
            margin: 18px 0 10px;
            font-size: clamp(2rem, 7vw, 3.3rem);
            line-height: 0.94;
            letter-spacing: -0.06em;
        }

        .subtitle {
            margin: 0 0 26px;
            color: var(--muted);
            line-height: 1.7;
            font-size: 0.98rem;
        }

        .errors,
        .hint {
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

        .hint {
            background: var(--ok-soft);
            border: 1px solid rgba(15, 118, 110, 0.12);
            color: var(--ok-text);
        }

        .dummy-accounts {
            display: grid;
            gap: 10px;
            margin-bottom: 18px;
        }

        .dummy-card {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .dummy-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }

        .dummy-head strong {
            font-size: 0.96rem;
        }

        .dummy-role {
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent-deep);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .dummy-card span {
            display: block;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.6;
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
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: rgba(255, 255, 255, 0.94);
            font: inherit;
            color: inherit;
            transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        }

        .field:focus {
            outline: none;
            border-color: rgba(209, 77, 31, 0.45);
            box-shadow: 0 0 0 5px rgba(209, 77, 31, 0.12);
            transform: translateY(-1px);
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
            background: linear-gradient(135deg, var(--accent), #ee7a1a);
            color: white;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 16px 36px rgba(209, 77, 31, 0.28);
            transition: transform 160ms ease, box-shadow 160ms ease, filter 160ms ease;
        }

        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 42px rgba(209, 77, 31, 0.32);
            filter: saturate(1.04);
        }

        .button:active {
            transform: translateY(0);
        }

        .meta {
            margin-top: 20px;
            text-align: center;
            color: var(--muted);
            font-size: 0.9rem;
        }

        code {
            font-family: "IBM Plex Mono", monospace;
            font-size: 0.9em;
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

        @keyframes float {
            0%, 100% {
                transform: translate3d(0, 0, 0);
            }
            50% {
                transform: translate3d(0, 14px, 0);
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
    </style>
</head>
<body>
    <main class="shell">
        <section class="panel">
            <div class="brand">
                <span class="brand-mark"></span>
                Portal Aman
            </div>

            <h1>Master Data Prasarana DJKA</h1>
            <p class="subtitle">
                Masuk untuk mengakses dashboard operasional, dokumentasi API, dan monitoring sistem secara aman melalui koneksi terenkripsi.
            </p>

            @if ($errors->any())
                <div class="errors">{{ $errors->first() }}</div>
            @endif

            @if ($dummyAccounts !== [])
                <div class="hint">
                    Akun dummy debug aktif. Semua akun di bawah ini memakai password <code>password</code>.
                </div>

                <div class="dummy-accounts">
                    @foreach ($dummyAccounts as $account)
                        <div class="dummy-card">
                            <div class="dummy-head">
                                <strong>{{ $account['name'] }}</strong>
                                <span class="dummy-role">{{ $account['role'] }}</span>
                            </div>
                            <span><code>{{ $account['email'] }}</code></span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="post" action="/login">
                @csrf

                <div class="field-group">
                    <div class="label-row">
                        <label for="email">Email</label>
                    </div>
                    <div class="input-wrap">
                        <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@instansi.go.id">
                    </div>
                </div>

                <div class="field-group">
                    <div class="label-row">
                        <label for="password">Password</label>
                    </div>
                    <div class="input-wrap">
                        <input class="field" id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password">
                    </div>
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Pertahankan sesi login</span>
                </label>

                <button class="button" type="submit">Masuk ke Sistem</button>
            </form>

            <div class="meta">
                Pastikan domain diakses melalui <code>https://prasarana.labdata.id</code>
            </div>
        </section>
    </main>
</body>
</html>
