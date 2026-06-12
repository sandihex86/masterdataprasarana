<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Master Data Prasarana DJKA</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f1ea;
            --sidebar: rgba(255, 255, 255, 0.92);
            --panel: rgba(255, 255, 255, 0.9);
            --panel-strong: #ffffff;
            --line: rgba(15, 23, 42, 0.08);
            --line-soft: rgba(15, 23, 42, 0.05);
            --text: #172033;
            --muted: #667085;
            --accent: #d14d1f;
            --accent-deep: #a33918;
            --accent-soft: rgba(209, 77, 31, 0.12);
            --violet: #6d4aff;
            --violet-soft: rgba(109, 74, 255, 0.1);
            --ok: #0f766e;
            --ok-soft: rgba(15, 118, 110, 0.12);
            --warn: #b45309;
            --warn-soft: rgba(180, 83, 9, 0.12);
            --danger: #b42318;
            --danger-soft: rgba(180, 35, 24, 0.11);
            --shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 0% 0%, rgba(209, 77, 31, 0.12), transparent 24%),
                radial-gradient(circle at 100% 0%, rgba(109, 74, 255, 0.08), transparent 20%),
                linear-gradient(180deg, #faf8f4 0%, var(--bg) 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font: inherit;
        }

        .dashboard {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 272px minmax(0, 1fr);
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 22px 14px;
            background: var(--sidebar);
            border-right: 1px solid var(--line);
            backdrop-filter: blur(18px);
            overflow: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 8px 18px;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--accent), #f59e0b);
            color: white;
            box-shadow: 0 16px 32px rgba(209, 77, 31, 0.18);
            font-weight: 800;
            letter-spacing: -0.05em;
        }

        .brand-copy strong {
            display: block;
            font-size: 1rem;
            letter-spacing: -0.03em;
        }

        .brand-copy span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .nav-section {
            margin-top: 18px;
        }

        .nav-title {
            margin: 0 10px 10px;
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .nav-list {
            display: grid;
            gap: 6px;
        }

        .nav-link,
        .nav-button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 12px;
            border-radius: 16px;
            border: 1px solid transparent;
            background: transparent;
            color: inherit;
            cursor: pointer;
            transition: background 160ms ease, border-color 160ms ease, transform 160ms ease;
        }

        .nav-link:hover,
        .nav-button:hover,
        .nav-link.active {
            background: rgba(15, 23, 42, 0.04);
            border-color: rgba(15, 23, 42, 0.05);
            transform: translateX(1px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(209, 77, 31, 0.1), rgba(109, 74, 255, 0.08));
            border-color: rgba(209, 77, 31, 0.14);
        }

        .nav-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .icon {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .nav-copy {
            min-width: 0;
        }

        .nav-copy strong {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .nav-copy span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 0.78rem;
        }

        .nav-badge {
            padding: 6px 9px;
            border-radius: 999px;
            background: var(--violet-soft);
            color: var(--violet);
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .content {
            min-width: 0;
            padding: 18px 20px 28px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
            padding: 10px 12px 10px 16px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.68);
            backdrop-filter: blur(12px);
        }

        .topbar-title strong {
            display: block;
            font-size: 1rem;
        }

        .topbar-title span {
            color: var(--muted);
            font-size: 0.87rem;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .pill,
        .top-button,
        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .pill {
            background: var(--violet-soft);
            color: var(--violet);
        }

        .top-button {
            background: white;
            border: 1px solid var(--line);
            color: var(--text);
        }

        .top-button.primary {
            background: linear-gradient(135deg, var(--accent), #ef7a1b);
            border-color: transparent;
            color: white;
            box-shadow: 0 16px 28px rgba(209, 77, 31, 0.22);
        }

        .user-chip {
            background: white;
            border: 1px solid var(--line);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(209, 77, 31, 0.16), rgba(109, 74, 255, 0.12));
            color: var(--accent-deep);
            font-weight: 800;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .hero {
            grid-template-columns: 1.25fr 0.95fr;
            align-items: stretch;
        }

        .card {
            position: relative;
            overflow: hidden;
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.62);
            border-radius: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(16px);
        }

        .card-body {
            padding: 24px;
        }

        .hero-main::before {
            content: "";
            position: absolute;
            inset: auto -80px -80px auto;
            width: 240px;
            height: 240px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(209, 77, 31, 0.16), rgba(109, 74, 255, 0.05));
            filter: blur(2px);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.72);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent-deep);
        }

        h1,
        h2,
        h3 {
            margin: 0;
            letter-spacing: -0.04em;
        }

        h1 {
            margin-top: 16px;
            font-size: clamp(2rem, 5vw, 3.4rem);
            line-height: 0.96;
        }

        .lead {
            margin-top: 12px;
            max-width: 60ch;
            color: var(--muted);
            line-height: 1.7;
        }

        .hero-actions,
        .hero-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .action-button,
        .chip,
        .status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .action-button {
            background: white;
            border: 1px solid var(--line);
        }

        .action-button.primary {
            background: linear-gradient(135deg, var(--accent), #ef7a1b);
            color: white;
            border-color: transparent;
            box-shadow: 0 16px 28px rgba(209, 77, 31, 0.22);
        }

        .chip {
            background: var(--accent-soft);
            color: var(--accent-deep);
        }

        .status {
            text-transform: capitalize;
        }

        .status.ok,
        .status.ready {
            background: var(--ok-soft);
            color: var(--ok);
        }

        .status.degraded,
        .status.partial {
            background: var(--warn-soft);
            color: var(--warn);
        }

        .status.missing {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .metric {
            padding: 20px;
            border-radius: 24px;
            background: var(--panel-strong);
            border: 1px solid var(--line-soft);
        }

        .metric span {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .metric strong {
            display: block;
            margin-top: 8px;
            font-size: 2rem;
            line-height: 1;
        }

        .metric small {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.84rem;
        }

        .section {
            margin-top: 18px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 14px;
            margin-bottom: 14px;
        }

        .section-header p {
            margin: 6px 0 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .overview-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .overview-card {
            padding: 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid var(--line-soft);
        }

        .overview-card strong {
            display: block;
            margin-top: 10px;
            font-size: 1.9rem;
            line-height: 1;
        }

        .overview-card span,
        .overview-card small,
        .meta,
        .table-note {
            color: var(--muted);
        }

        .workspace {
            grid-template-columns: 1.1fr 0.9fr;
            align-items: start;
        }

        .stack {
            display: grid;
            gap: 18px;
        }

        .module-list,
        .menu-list,
        .health-list {
            display: grid;
            gap: 12px;
        }

        .menu-item,
        .health-item,
        .module-item {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--line-soft);
        }

        .menu-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .menu-main,
        .module-main,
        .health-main {
            min-width: 0;
        }

        .menu-main strong,
        .module-main strong,
        .health-main strong {
            display: block;
        }

        .menu-main span,
        .module-main span,
        .health-main span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .menu-tag {
            white-space: nowrap;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--violet-soft);
            color: var(--violet);
            font-size: 0.8rem;
            font-weight: 700;
        }

        .meter {
            margin-top: 12px;
            height: 10px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.16);
            overflow: hidden;
        }

        .meter span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent), #f59e0b);
        }

        .table-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.62);
            border-radius: 26px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 20px 22px 0;
        }

        .table-body {
            padding: 8px 22px 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 0;
            text-align: left;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-size: 0.76rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .mono {
            font-family: "IBM Plex Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.9rem;
        }

        .empty {
            padding: 14px 0;
            color: var(--muted);
        }

        .footer-callout {
            margin-top: 14px;
            padding: 16px 18px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(109, 74, 255, 0.08), rgba(209, 77, 31, 0.08));
            color: var(--text);
            border: 1px solid rgba(109, 74, 255, 0.08);
        }

        .footer-callout strong {
            display: block;
            margin-bottom: 4px;
        }

        .logout-form {
            margin: 0;
        }

        @media (max-width: 1180px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }
        }

        @media (max-width: 1040px) {
            .hero,
            .workspace,
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .content {
                padding: 14px 14px 24px;
            }

            .topbar,
            .section-header,
            .menu-item,
            .module-item,
            .health-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-actions,
            .hero-actions,
            .hero-pills {
                width: 100%;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .table-head,
            .table-body,
            .card-body {
                padding-left: 18px;
                padding-right: 18px;
            }
        }
    </style>
</head>
<body>
@php
    $app = $overview['application'];
    $metrics = $overview['metrics'];
    $health = $overview['health'];
    $user = auth()->user();
    $userName = $user?->name ?? 'Operator';
    $userInitial = strtoupper(substr($userName, 0, 1));
    $userRole = $user?->resolveRole();
    $userRoleLabel = $userRole?->label() ?? 'Guest';

    $quickMenu = [
        ['label' => 'Swagger Docs', 'href' => route('docs.swagger'), 'tag' => 'Docs'],
        ['label' => 'OpenAPI Spec', 'href' => route('l5-swagger.default.docs'), 'tag' => 'Spec'],
        ['label' => 'Health Check API', 'href' => '/api/v1/health', 'tag' => 'API'],
    ];

    if ($user?->isAdministrator()) {
        $quickMenu[] = ['label' => 'JSON Sistem', 'href' => route('dashboard.system'), 'tag' => 'Internal'];
    }
@endphp
<div class="dashboard">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">MD</div>
            <div class="brand-copy">
                <strong>Master Data</strong>
                <span>Prasarana DJKA</span>
            </div>
        </div>

        <div class="nav-section">
            <p class="nav-title">Utama</p>
            <div class="nav-list">
                <a class="nav-link active" href="#beranda">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                        <div class="nav-copy"><strong>Beranda</strong></div>
                    </div>
                </a>
                <a class="nav-link" href="#status-modul">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M4 19h16"/><path d="M7 15V9"/><path d="M12 15V5"/><path d="M17 15v-3"/></svg>
                        <div class="nav-copy"><strong>Status Modul</strong></div>
                    </div>
                </a>
                <a class="nav-link" href="#menu-penting">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
                        <div class="nav-copy"><strong>Menu Penting</strong></div>
                    </div>
                </a>
            </div>
        </div>

        <div class="nav-section">
            <p class="nav-title">Data dan Integrasi</p>
            <div class="nav-list">
                <a class="nav-link" href="#master-data">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
                        <div class="nav-copy"><strong>Master Data</strong></div>
                    </div>
                    <span class="nav-badge">{{ number_format($metrics['master_data_records']) }}</span>
                </a>
                <a class="nav-link" href="#import-mapping">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                        <div class="nav-copy"><strong>Import & Mapping</strong></div>
                    </div>
                    <span class="nav-badge">{{ number_format($metrics['import_mappings']) }}</span>
                </a>
                <a class="nav-link" href="#monitoring">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M3 12h4l2.5-6 5 12 2.5-6H21"/></svg>
                        <div class="nav-copy"><strong>Monitoring</strong></div>
                    </div>
                </a>
                <a class="nav-link" href="{{ route('docs.swagger') }}">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M8 4h8"/><path d="M8 20h8"/><path d="M5 8h14"/><path d="M5 16h14"/><path d="M7 8v8"/><path d="M17 8v8"/></svg>
                        <div class="nav-copy"><strong>Swagger Docs</strong></div>
                    </div>
                </a>
            </div>
        </div>

        <div class="nav-section">
            <p class="nav-title">Akun</p>
            <div class="nav-list">
                <a class="nav-link" href="{{ route('l5-swagger.default.docs') }}">
                    <div class="nav-main">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M8 7h8"/><path d="M8 12h8"/><path d="M8 17h5"/><path d="M5 3h14v18H5z"/></svg>
                        <div class="nav-copy"><strong>OpenAPI JSON</strong></div>
                    </div>
                </a>
                <form class="logout-form" method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-button" type="submit">
                        <div class="nav-main">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                            <div class="nav-copy"><strong>Logout</strong></div>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="content">
        <div class="topbar">
            <div class="topbar-title">
                <strong>Dashboard</strong>
                <span>{{ $app['name'] }} · {{ $app['generated_at']->format('d M Y H:i') }} UTC</span>
            </div>
            <div class="topbar-actions">
                <span class="pill">Swagger aktif</span>
                @if ($user?->isAdministrator())
                    <a class="top-button" href="{{ route('dashboard.system') }}">JSON Sistem</a>
                @endif
                <a class="top-button primary" href="{{ route('docs.swagger') }}">Buka Swagger Docs</a>
                <div class="user-chip">
                    <div class="user-avatar">{{ $userInitial }}</div>
                    <span>{{ $userName }} · {{ $userRoleLabel }}</span>
                </div>
            </div>
        </div>

        <section class="grid hero section" id="beranda">
            <article class="card hero-main">
                <div class="card-body">
                    <span class="eyebrow">Dashboard</span>
                    <h1>{{ $userName }}</h1>

                    <div class="hero-pills">
                        <span class="status {{ $health['status'] }}">{{ $health['status'] }}</span>
                        <span class="chip">ENV {{ \Illuminate\Support\Str::upper($app['environment']) }}</span>
                        <span class="chip">Debug {{ $app['debug'] ? 'enabled' : 'disabled' }}</span>
                        <span class="chip">Role {{ $userRoleLabel }}</span>
                        <span class="chip">Laravel {{ $app['laravel_version'] }}</span>
                    </div>

                    <div class="hero-actions">
                        <a class="action-button primary" href="{{ route('docs.swagger') }}">Swagger Docs</a>
                        <a class="action-button" href="/api/v1/health" target="_blank" rel="noreferrer">Health API</a>
                        <a class="action-button" href="{{ route('l5-swagger.default.docs') }}" target="_blank" rel="noreferrer">OpenAPI Spec</a>
                    </div>
                </div>
            </article>

            <article class="grid stats">
                <div class="metric card">
                    <div class="card-body">
                        <span>Master data aktif</span>
                        <strong>{{ number_format($metrics['active_master_data_records']) }}</strong>
                        <small>{{ number_format($metrics['master_data_types']) }} tipe data tersedia</small>
                    </div>
                </div>
                <div class="metric card">
                    <div class="card-body">
                        <span>Import mapping</span>
                        <strong>{{ number_format($metrics['import_mappings']) }}</strong>
                        <small>{{ number_format($metrics['active_import_mappings']) }} mapping aktif</small>
                    </div>
                </div>
                <div class="metric card">
                    <div class="card-body">
                        <span>Client API aktif</span>
                        <strong>{{ number_format($metrics['active_api_clients']) }}</strong>
                        <small>{{ number_format($metrics['access_tokens']) }} token tersimpan</small>
                    </div>
                </div>
                <div class="metric card">
                    <div class="card-body">
                        <span>Request hari ini</span>
                        <strong>{{ number_format($metrics['request_logs_today']) }}</strong>
                        <small>{{ number_format($metrics['audit_logs']) }} audit log tercatat</small>
                    </div>
                </div>
            </article>
        </section>

        <section class="section" id="akses-role">
            <div class="section-header">
                <div>
                    <h2>Matriks Role</h2>
                </div>
            </div>
            <div class="grid overview-grid">
                @foreach ($overview['user_roles'] as $role)
                    <article class="overview-card">
                        <span class="menu-tag">{{ number_format($role['count']) }} user</span>
                        <h3 style="margin-top: 14px;">{{ $role['label'] }}</h3>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="section" id="menu-penting">
            <div class="section-header">
                <div>
                    <h2>Menu Penting</h2>
                </div>
            </div>
            <div class="card">
                <div class="card-body menu-list">
                    @foreach ($quickMenu as $item)
                        <a class="menu-item" href="{{ $item['href'] }}" @if(\Illuminate\Support\Str::startsWith($item['href'], '/api/')) target="_blank" rel="noreferrer" @endif>
                            <div class="menu-main"><strong>{{ $item['label'] }}</strong></div>
                            <span class="menu-tag">{{ $item['tag'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section" id="status-modul">
            <div class="section-header">
                <div>
                    <h2>Status Modul</h2>
                </div>
            </div>
            <div class="grid overview-grid">
                @foreach ($overview['modules'] as $module)
                    <article class="overview-card">
                        <span class="status {{ $module['status'] }}">{{ $module['status'] }}</span>
                        <strong>{{ $module['percentage'] }}%</strong>
                        <h3 style="margin-top: 12px;">{{ $module['label'] }}</h3>
                        <div class="meter">
                            <span style="width: {{ $module['percentage'] }}%"></span>
                        </div>
                        <small>{{ $module['completed'] }} dari {{ $module['total'] }} indikator terpenuhi</small>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid workspace section">
            <div class="stack">
                <div class="card" id="monitoring">
                    <div class="card-body">
                        <div class="section-header">
                            <div>
                                <h2>Kesehatan Sistem</h2>
                            </div>
                            <span class="status {{ $health['status'] }}">{{ $health['status'] }}</span>
                        </div>

                        <div class="health-list">
                            @foreach ($health['checks'] as $check)
                                <div class="health-item">
                                    <div class="health-main">
                                        <strong>{{ $check['label'] }}</strong>
                                        <span>{{ $check['detail'] }}</span>
                                    </div>
                                    <span class="status {{ $check['ok'] ? 'ok' : 'missing' }}">{{ $check['ok'] ? 'ok' : 'issue' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="table-card" id="master-data">
                    <div class="table-head">
                        <div>
                            <h2>Master Data</h2>
                        </div>
                        <span class="menu-tag">{{ $overview['entity_types']->count() }} tipe</span>
                    </div>
                    <div class="table-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tipe</th>
                                    <th>Kode</th>
                                    <th>Record</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overview['entity_types'] as $type)
                                    <tr>
                                        <td><strong>{{ $type['name'] }}</strong></td>
                                        <td class="mono">{{ $type['code'] }}</td>
                                        <td>{{ number_format($type['records_count']) }}</td>
                                        <td><span class="status {{ $type['is_active'] ? 'ready' : 'partial' }}">{{ $type['is_active'] ? 'active' : 'inactive' }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty">Belum ada tipe master data yang tersimpan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <table style="margin-top: 8px;">
                            <thead>
                                <tr>
                                    <th>Record</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th>Diperbarui</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overview['recent_records'] as $record)
                                    <tr>
                                        <td>
                                            <strong>{{ $record['name'] ?? '-' }}</strong>
                                            <div class="table-note mono">{{ $record['code'] }}</div>
                                        </td>
                                        <td>{{ $record['type_name'] ?? $record['entity_type'] }}</td>
                                        <td><span class="status {{ $record['status'] === 'active' ? 'ready' : 'partial' }}">{{ $record['status'] }}</span></td>
                                        <td>{{ optional($record['updated_at'])->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty">Belum ada record master data terbaru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="stack">
                <div class="card" id="import-mapping">
                    <div class="card-body">
                        <div class="section-header">
                            <div>
                                <h2>Import dan Mapping</h2>
                            </div>
                        </div>
                        <div class="module-list">
                            @forelse ($overview['recent_mappings'] as $mapping)
                                <div class="module-item">
                                    <div class="module-main">
                                        <strong>{{ $mapping['name'] }}</strong>
                                        <span>{{ $mapping['source_system'] }}/{{ $mapping['source_table'] }} → {{ $mapping['entity_type'] }}</span>
                                    </div>
                                    <span class="status {{ $mapping['is_active'] ? 'ready' : 'partial' }}">v{{ $mapping['version'] }}</span>
                                </div>
                            @empty
                                <div class="module-item">
                                    <div class="module-main">
                                        <strong>Belum ada mapping import.</strong>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="footer-callout" style="margin-top: 16px;">
                            <strong>Batch import tersimpan: {{ number_format($metrics['import_batches']) }}</strong>
                            Error import yang tercatat saat ini: {{ number_format($metrics['import_errors']) }}.
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-head">
                        <div>
                            <h2>Import, Client API, dan Monitoring</h2>
                        </div>
                    </div>
                    <div class="table-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>Import</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overview['recent_imports'] as $import)
                                    <tr>
                                        <td>
                                            <strong>{{ $import['entity_type'] ?? 'n/a' }}</strong>
                                            <div class="table-note mono">{{ $import['source_system'] }}/{{ $import['source_table'] ?? '-' }}</div>
                                        </td>
                                        <td><span class="status {{ $import['status'] === 'completed' ? 'ready' : 'partial' }}">{{ $import['status'] }}</span></td>
                                        <td>{{ $import['progress_percentage'] }}% / {{ number_format($import['total_rows']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty">Belum ada batch import terbaru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <table style="margin-top: 8px;">
                            <thead>
                                <tr>
                                    <th>Client API</th>
                                    <th>Kode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overview['recent_clients'] as $client)
                                    <tr>
                                        <td>
                                            <strong>{{ $client['name'] }}</strong>
                                            <div class="table-note">{{ $client['owner_email'] ?? 'owner belum diisi' }}</div>
                                        </td>
                                        <td class="mono">{{ $client['code'] }}</td>
                                        <td><span class="status {{ $client['is_active'] ? 'ready' : 'partial' }}">{{ $client['is_active'] ? 'active' : 'inactive' }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty">Belum ada client API tersimpan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <table style="margin-top: 8px;">
                            <thead>
                                <tr>
                                    <th>Request API</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overview['recent_requests'] as $request)
                                    <tr>
                                        <td class="mono">{{ $request['method'] }} {{ $request['endpoint'] }}</td>
                                        <td>{{ $request['status_code'] }} / {{ $request['response_time_ms'] }}ms</td>
                                        <td>{{ optional($request['requested_at'])->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty">Belum ada request log yang direkam.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="table-card">
                <div class="table-head">
                    <div>
                        <h2>Audit Log dan Endpoint API</h2>
                    </div>
                    <span class="menu-tag">{{ $overview['api_routes']->count() }} route</span>
                </div>
                <div class="table-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Audit</th>
                                <th>Objek</th>
                                <th>Request ID</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($overview['recent_audits'] as $audit)
                                <tr>
                                    <td class="mono">{{ $audit['action'] }}</td>
                                    <td>{{ $audit['auditable_type'] }} #{{ $audit['auditable_id'] }}</td>
                                    <td class="mono">{{ $audit['request_id'] ?? '-' }}</td>
                                    <td>{{ optional($audit['created_at'])->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty">Belum ada audit log yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <table style="margin-top: 8px;">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>URI</th>
                                <th>Route Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($overview['api_routes'] as $route)
                                <tr>
                                    <td class="mono">{{ implode(', ', $route['methods']) }}</td>
                                    <td class="mono">{{ $route['uri'] }}</td>
                                    <td>{{ $route['name'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
