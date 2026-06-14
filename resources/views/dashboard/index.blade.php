<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard | Master Data Prasarana</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
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
            --shadow: 0 1px 2px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.05);
            --shadow-raised: 0 2px 6px rgba(15, 23, 42, 0.08), 0 6px 14px rgba(15, 23, 42, 0.05);
            --shadow-accent: 0 1px 2px rgba(209, 77, 31, 0.14), 0 3px 8px rgba(209, 77, 31, 0.1);
            --shadow-hover: 0 1px 3px rgba(15, 23, 42, 0.08), 0 4px 8px rgba(15, 23, 42, 0.05);
            --shadow-hover-accent: 0 1px 3px rgba(209, 77, 31, 0.14), 0 5px 10px rgba(209, 77, 31, 0.08);
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
            font-family: "Roboto", "Segoe UI", Arial, sans-serif;
            font-size: 14px;
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

        summary {
            list-style: none;
        }

        summary::-webkit-details-marker {
            display: none;
        }

        .dashboard {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(292px, max-content) minmax(0, 1fr);
            transition: grid-template-columns 180ms ease;
        }

        .sidebar {
            position: sticky;
            top: 0;
            width: max-content;
            min-width: 292px;
            max-width: min(390px, 34vw);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 22px 14px;
            background: var(--sidebar);
            border-right: 1px solid var(--line);
            backdrop-filter: blur(18px);
            overflow-x: hidden;
            overflow-y: auto;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            padding: 8px 8px 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            overflow: hidden;
            flex: 0 0 44px;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-toggle {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 0;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: background 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .sidebar-toggle:hover {
            background: rgba(15, 23, 42, 0.05);
            color: var(--text);
            transform: translateY(-1px);
        }

        .sidebar-toggle .icon {
            width: 16px;
            height: 16px;
            flex-basis: 16px;
        }

        .sidebar-action {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;
            padding-right: 2px;
        }

        .sidebar-action .sidebar-toggle {
            width: 34px;
            height: 34px;
            justify-content: center;
            border-radius: 10px;
            padding: 0;
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

        .nav-subtitle {
            margin: -2px 10px 10px;
            color: var(--muted);
            font-size: 0.78rem;
        }

        .nav-list {
            display: grid;
            gap: 6px;
        }

        .nav-group {
            display: grid;
            gap: 6px;
        }

        .nav-toggle-indicator {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            color: var(--muted);
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: transform 160ms ease, color 160ms ease;
        }

        .nav-group.is-expanded .nav-toggle-indicator {
            color: var(--accent);
            transform: rotate(180deg);
        }

        .nav-children {
            display: grid;
            gap: 4px;
            width: max-content;
            max-width: 300px;
            margin: -2px 0 4px 46px;
            padding-left: 10px;
            border-left: 1px dashed rgba(15, 23, 42, 0.12);
        }

        .nav-group:not(.is-expanded) > .nav-children {
            display: none;
        }

        .nav-child-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 12px;
            color: var(--muted);
            font-size: 0.82rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: background 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .nav-child-icon {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.06);
            color: #667085;
        }

        .nav-child-icon .icon {
            width: 14px;
            height: 14px;
            stroke-width: 1.9;
        }

        .nav-child-text {
            min-width: 0;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .nav-child-text span {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-child-text small {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .nav-child-link-combine .nav-child-icon {
            background: rgba(37, 99, 235, 0.1);
            color: #1d4ed8;
        }

        .nav-child-link-master .nav-child-icon {
            background: rgba(241, 129, 32, 0.13);
            color: #b45309;
        }

        .nav-child-link-lookup .nav-child-icon {
            background: rgba(15, 118, 110, 0.1);
            color: #0f766e;
        }

        .nav-child-link-detail .nav-child-icon {
            background: rgba(102, 112, 133, 0.1);
            color: #475467;
        }

        .nav-child-link:hover,
        .nav-child-link.active {
            background: rgba(15, 23, 42, 0.04);
            color: var(--text);
            transform: translateX(1px);
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
            min-width: max-content;
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
            text-align: left;
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
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid rgba(37, 99, 235, 0.28);
            background: rgba(255, 255, 255, 0.78);
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 650;
            white-space: nowrap;
        }

        .content {
            min-width: 0;
            padding: 18px 20px 28px;
        }

        .topbar {
            position: relative;
            z-index: 40;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
            padding: 16px 14px 16px 18px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.68);
            backdrop-filter: blur(12px);
        }

        .topbar-title strong {
            display: block;
            font-size: 1rem;
            line-height: 1.2;
            letter-spacing: 0;
            font-weight: 650;
        }

        .topbar-title span {
            color: var(--muted);
            display: block;
            font-size: 0.76rem;
            line-height: 1.35;
            margin-top: 4px;
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
            padding: 8px 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .pill {
            background: var(--violet-soft);
            color: var(--violet);
        }

        .top-button {
            position: relative;
            overflow: hidden;
            background: white;
            border: 1px solid var(--line);
            color: var(--text);
            border-radius: 14px;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .top-button::before {
            content: "";
            position: absolute;
            top: 6px;
            left: 50%;
            width: 24px;
            height: 3px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.09);
            transform: translateX(-50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 160ms ease;
        }

        .top-button.primary {
            background: linear-gradient(135deg, var(--accent), #ef7a1b);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow-accent);
        }

        .top-button.primary::before {
            background: rgba(255, 255, 255, 0.46);
        }

        .top-button:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-hover);
        }

        .top-button:hover::before {
            opacity: 1;
        }

        .top-button.primary:hover {
            box-shadow: var(--shadow-hover-accent);
        }

        .user-menu {
            position: relative;
        }

        .user-chip {
            background: white;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 8px 12px;
            font-size: 0.82rem;
            line-height: 1;
            min-height: 46px;
            cursor: pointer;
            transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .user-menu[open] .user-chip {
            background: #fff;
            border-color: rgba(15, 23, 42, 0.14);
            box-shadow: var(--shadow);
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(209, 77, 31, 0.16), rgba(109, 74, 255, 0.12));
            color: var(--accent-deep);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .user-chip-text {
            display: grid;
            gap: 2px;
        }

        .user-chip-text strong {
            font-size: 0.86rem;
            font-weight: 600;
            line-height: 1.1;
        }

        .user-chip-text span {
            color: var(--muted);
            font-size: 0.74rem;
            line-height: 1.1;
        }

        .user-menu-caret {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            transition: transform 160ms ease;
        }

        .user-menu[open] .user-menu-caret {
            transform: rotate(180deg);
        }

        .user-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: min(286px, 86vw);
            padding: 10px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06);
            z-index: 120;
            transform-origin: top right;
            animation: menu-pop 140ms ease-out;
        }

        .user-menu-list {
            display: grid;
            gap: 6px;
        }

        .user-menu-item,
        .user-menu-button {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 54px;
            padding: 11px 14px;
            border-radius: 13px;
            border: 0;
            background: transparent;
            color: inherit;
            font-family: "Inter", "Roboto", "IBM Plex Sans", "Segoe UI", sans-serif;
            text-align: left;
            cursor: pointer;
            transition: background 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .user-menu-item:hover,
        .user-menu-button:hover {
            background: rgba(209, 77, 31, 0.08);
            color: var(--accent-deep);
            transform: translateX(2px);
        }

        .user-menu-icon {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .user-menu-copy strong {
            display: block;
            font-size: 0.86rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .user-menu-copy span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 0.72rem;
            line-height: 1.4;
        }

        @keyframes menu-pop {
            from {
                opacity: 0;
                transform: translateY(-4px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .card {
            position: relative;
            overflow: hidden;
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.62);
            border-radius: 20px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(16px);
        }

        .card-body {
            padding: 24px;
            font-size: 0.86rem;
            line-height: 1.52;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            letter-spacing: 0;
        }

        h1 {
            margin-top: 0;
            font-size: clamp(2rem, 5vw, 3.4rem);
            line-height: 0.96;
        }

        .action-button,
        .chip,
        .status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .action-button {
            position: relative;
            overflow: hidden;
            background: white;
            border: 1px solid var(--line);
            border-radius: 14px;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .action-button::before {
            content: "";
            position: absolute;
            top: 6px;
            left: 50%;
            width: 24px;
            height: 3px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.09);
            transform: translateX(-50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 160ms ease;
        }

        .action-button.primary {
            background: linear-gradient(135deg, var(--accent), #ef7a1b);
            color: white;
            border-color: transparent;
            box-shadow: var(--shadow-accent);
        }

        .action-button.primary::before {
            background: rgba(255, 255, 255, 0.46);
        }

        .action-button:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-hover);
        }

        .action-button:hover::before {
            opacity: 1;
        }

        .action-button.primary:hover {
            box-shadow: var(--shadow-hover-accent);
        }

        .chip {
            background: var(--accent-soft);
            color: var(--accent-deep);
            border-radius: 999px;
        }

        .status {
            padding: 7px 10px;
            font-size: 0.78rem;
            line-height: 1.15;
            text-transform: capitalize;
            border-radius: 8px;
            border: 1px solid rgba(37, 99, 235, 0.28);
            background: rgba(255, 255, 255, 0.78);
            color: #1d4ed8;
        }

        .status.ok,
        .status.ready {
            border-color: rgba(15, 118, 110, 0.28);
            background: rgba(255, 255, 255, 0.78);
            color: var(--ok);
        }

        .status.degraded,
        .status.partial {
            border-color: rgba(180, 83, 9, 0.28);
            background: rgba(255, 255, 255, 0.78);
            color: var(--warn);
        }

        .status.missing {
            border-color: rgba(180, 35, 24, 0.28);
            background: rgba(255, 255, 255, 0.78);
            color: var(--danger);
        }

        .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .metric {
            padding: 20px;
            border-radius: 18px;
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
            font-size: 0.82rem;
            line-height: 1.6;
        }

        .overview-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .overview-card {
            padding: 20px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid var(--line-soft);
        }

        .overview-card strong {
            display: block;
            margin-top: 10px;
            font-size: 1.9rem;
            line-height: 1;
        }

        .section-header h2,
        .table-head h2 {
            font-size: 0.94rem;
            line-height: 1.25;
        }

        .overview-card h3 {
            font-size: 0.86rem;
            line-height: 1.28;
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
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--line-soft);
        }

        .menu-item:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
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
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            padding: 7px 10px;
            border-radius: 8px;
            border: 1px solid rgba(37, 99, 235, 0.28);
            background: rgba(255, 255, 255, 0.78);
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 650;
            line-height: 1.15;
        }

        .menu-tag .tag-icon,
        .nav-badge .tag-icon,
        .status .tag-icon,
        .detail-chip .tag-icon {
            width: 15px;
            height: 15px;
            flex: 0 0 15px;
            stroke: currentColor;
            stroke-width: 1.9;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .menu-tag .tag-label,
        .nav-badge .tag-label,
        .status .tag-label,
        .detail-chip .tag-label {
            color: currentColor;
        }

        .menu-tag .tag-value,
        .nav-badge .tag-value,
        .status .tag-value,
        .detail-chip .tag-value {
            color: var(--text);
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
            border-radius: 20px;
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
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(109, 74, 255, 0.08), rgba(209, 77, 31, 0.08));
            color: var(--text);
            border: 1px solid rgba(109, 74, 255, 0.08);
        }

        .footer-callout strong {
            display: block;
            margin-bottom: 4px;
        }

        .metadata-accordion {
            display: grid;
            gap: 10px;
            margin-top: 10px;
        }

        .metadata-accordion-item {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.72);
            overflow: hidden;
        }

        .metadata-accordion-item[open] {
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .metadata-accordion-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-height: 56px;
            padding: 14px 16px;
            cursor: pointer;
        }

        .metadata-accordion-main {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .metadata-accordion-main strong {
            font-size: 0.92rem;
            font-weight: 650;
        }

        .metadata-accordion-main span {
            color: var(--muted);
            font-size: 0.78rem;
            line-height: 1.35;
        }

        .metadata-accordion-side {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
        }

        .metadata-accordion-chevron {
            width: 16px;
            height: 16px;
            color: var(--muted);
            transition: transform 160ms ease;
        }

        .metadata-accordion-item[open] .metadata-accordion-chevron {
            transform: rotate(180deg);
        }

        .metadata-accordion-panel {
            padding: 0 16px 16px;
        }

        .metadata-panel-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .metadata-table-wrap {
            overflow-x: auto;
        }

        .metadata-table th,
        .metadata-table td {
            padding: 11px 12px 11px 0;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .metadata-table th:last-child,
        .metadata-table td:last-child {
            padding-right: 0;
        }

        .metadata-pending {
            margin: 0;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(148, 163, 184, 0.1);
            color: var(--muted);
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .metadata-value {
            max-width: min(520px, 54vw);
            white-space: pre-wrap;
            word-break: break-word;
        }

        .metadata-values-summary {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .metadata-values-scroll {
            max-height: min(62vh, 640px);
            overflow: auto;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 16px;
            padding: 0 14px;
            background: rgba(255, 255, 255, 0.78);
        }

        .metadata-value-json {
            display: block;
            margin: 0;
            padding: 10px 12px;
            border-radius: 12px;
            background: #111827;
            color: #f8fafc;
            font-size: 0.78rem;
            line-height: 1.45;
            overflow: auto;
            max-height: 220px;
        }

        .master-data-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 22px 22px 0;
            flex-wrap: wrap;
        }

        .master-data-toolbar-main,
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-field {
            min-width: min(100%, 320px);
            display: flex;
            align-items: center;
            gap: 10px;
            height: 46px;
            padding: 0 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: white;
        }

        .search-field input {
            width: 100%;
            border: 0;
            outline: none;
            background: transparent;
            color: var(--text);
        }

        .rows-select,
        .field input,
        .field textarea,
        .field select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: white;
            color: var(--text);
        }

        .master-data-alert {
            margin: 14px 22px 0;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(180, 83, 9, 0.14);
            background: rgba(180, 83, 9, 0.08);
            color: var(--warn);
            font-size: 0.88rem;
        }

        .relation-grid {
            display: grid;
            gap: 16px;
        }

        .relation-accordion {
            border-radius: 24px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.98));
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .relation-accordion-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            cursor: pointer;
        }

        .relation-accordion-summary:hover {
            background: rgba(248, 250, 252, 0.88);
        }

        .relation-accordion-copy {
            display: grid;
            gap: 6px;
        }

        .relation-accordion-copy p,
        .relation-graph-meta p,
        .relation-graph-empty p {
            margin: 0;
            color: var(--muted);
        }

        .relation-accordion-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.94);
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .relation-accordion[open] .relation-accordion-toggle .icon {
            transform: rotate(180deg);
        }

        .relation-accordion-toggle .icon {
            width: 16px;
            height: 16px;
            transition: transform 180ms ease;
        }

        .relation-accordion-body {
            display: grid;
            gap: 16px;
            padding: 0 20px 20px;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
        }

        .relation-graph-stage {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 290px;
            gap: 16px;
            padding-top: 18px;
        }

        .relation-graph-canvas {
            min-height: 430px;
            border-radius: 22px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background:
                radial-gradient(circle at top left, rgba(241, 129, 32, 0.12), transparent 34%),
                linear-gradient(180deg, rgba(255, 252, 248, 0.98), rgba(255, 255, 255, 0.98));
        }

        .relation-graph-meta {
            display: grid;
            gap: 12px;
            align-content: start;
        }

        .relation-meta-card {
            padding: 16px;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.96);
        }

        .relation-meta-card span {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .relation-meta-card strong {
            display: block;
            font-size: 0.95rem;
            line-height: 1.45;
            word-break: break-word;
        }

        .relation-legend {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .relation-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(248, 250, 252, 0.88);
            border: 1px solid rgba(15, 23, 42, 0.08);
            color: var(--muted);
            font-size: 0.77rem;
            font-weight: 700;
        }

        .relation-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
        }

        .relation-legend-dot.root {
            background: #d14d1f;
        }

        .relation-legend-dot.one-to-one {
            background: #2563eb;
        }

        .relation-legend-dot.one-to-many {
            background: #0f766e;
        }

        .relation-legend-dot.lookup {
            background: #b45309;
        }

        .relation-graph-empty {
            display: grid;
            place-items: center;
            min-height: 240px;
            text-align: center;
        }

        .detail-icon .icon,
        .detail-section-icon .icon,
        .detail-hero-icon .icon {
            width: 18px;
            height: 18px;
        }

        .form-section {
            display: grid;
            gap: 14px;
            padding: 18px;
            border-radius: 18px;
            border: 1px solid var(--line-soft);
            background: rgba(244, 241, 234, 0.42);
        }

        .form-stack {
            display: grid;
            gap: 18px;
        }

        .field-hint {
            margin: -2px 0 0;
            color: var(--muted);
            font-size: 0.74rem;
            line-height: 1.35;
        }

        .field input:disabled,
        .field textarea:disabled,
        .field select:disabled {
            border-color: rgba(15, 23, 42, 0.08);
            background: rgba(248, 250, 252, 0.82);
            color: #667085;
            cursor: not-allowed;
        }

        .section-header.compact {
            margin-bottom: 0;
        }

        .section-header.compact h3 {
            font-size: 1rem;
        }

        .section-header.compact p {
            margin: 6px 0 0;
        }

        .coordinate-input-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
            gap: 12px;
            align-items: end;
        }

        .coordinate-action-field {
            min-width: 132px;
        }

        .coordinate-action-field .action-button {
            width: 100%;
            min-height: 45px;
            justify-content: center;
        }

        .coordinate-pulse-button {
            border-color: rgba(37, 99, 235, 0.3);
            background: rgba(239, 246, 255, 0.92);
            color: #1d4ed8;
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2);
            animation: coordinateButtonPulse 1.6s ease-in-out infinite;
        }

        .coordinate-pulse-button .icon {
            width: 17px;
            height: 17px;
            color: #2563eb;
            animation: coordinateIconBlink 1.05s ease-in-out infinite;
        }

        .coordinate-pulse-button:hover {
            border-color: rgba(37, 99, 235, 0.46);
            background: rgba(219, 234, 254, 0.96);
            color: #1e40af;
        }

        .nested-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .nested-detail-card {
            display: grid;
            gap: 12px;
            min-height: 142px;
            padding: 14px;
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.86);
        }

        .nested-detail-card h4 {
            margin: 0;
            font-size: 0.95rem;
        }

        .nested-detail-card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .nested-detail-summary {
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .nested-detail-summary.is-filled {
            color: #0f766e;
        }

        .nested-detail-card .action-button {
            align-self: end;
            justify-content: center;
        }

        .inline-button.danger {
            border-color: rgba(180, 35, 24, 0.16);
            background: rgba(180, 35, 24, 0.08);
            color: var(--danger);
        }

        .inline-button.danger::before {
            background: rgba(180, 35, 24, 0.2);
        }

        .inline-button.danger:hover {
            box-shadow: 0 1px 3px rgba(180, 35, 24, 0.14), 0 5px 10px rgba(180, 35, 24, 0.08);
        }

        .master-data-table-wrap {
            overflow: auto;
        }

        .master-data-table th:last-child,
        .master-data-table td:last-child {
            text-align: right;
        }

        .grid-empty,
        .grid-loading {
            padding: 26px 0;
            text-align: center;
            color: var(--muted);
        }

        .row-title strong {
            display: block;
        }

        .row-title span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .inline-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .bridge-row-actions {
            flex-wrap: nowrap;
            gap: 6px;
            white-space: nowrap;
        }

        .tunnel-row-actions {
            flex-wrap: nowrap;
            gap: 6px;
            white-space: nowrap;
        }

        .tunnel-actions-cell {
            width: 208px;
            min-width: 208px;
            white-space: nowrap;
        }

        .bridge-row-actions .inline-button,
        .tunnel-row-actions .inline-button {
            min-width: 54px;
            padding: 7px 9px;
            border-radius: 10px;
            font-size: 0.76rem;
            line-height: 1.15;
        }

        .bridge-row-actions .inline-button::before,
        .tunnel-row-actions .inline-button::before {
            display: none;
        }

        .bridge-row-actions .inline-button:hover,
        .tunnel-row-actions .inline-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .bridge-actions-cell {
            width: 196px;
            min-width: 196px;
            white-space: nowrap;
        }

        .inline-button,
        .pagination-button,
        .icon-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid var(--line);
            background: white;
            color: var(--text);
            cursor: pointer;
            transition: background 160ms ease, border-color 160ms ease, transform 160ms ease, box-shadow 160ms ease;
        }

        .inline-button:hover,
        .pagination-button:hover,
        .icon-button:hover {
            background: rgba(15, 23, 42, 0.04);
            transform: translateY(-1px);
            box-shadow: var(--shadow-hover);
        }

        .inline-button {
            position: relative;
            overflow: hidden;
            padding: 9px 12px;
            border-radius: 13px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .inline-button::before {
            content: "";
            position: absolute;
            top: 6px;
            left: 50%;
            width: 20px;
            height: 3px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.08);
            transform: translateX(-50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 160ms ease;
        }

        .inline-button.primary {
            background: linear-gradient(135deg, var(--accent), #ef7a1b);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow-accent);
        }

        .inline-button.primary::before {
            background: rgba(255, 255, 255, 0.44);
        }

        .inline-button.primary:hover {
            box-shadow: var(--shadow-hover-accent);
        }

        .inline-button:hover::before {
            opacity: 1;
        }

        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 0 22px 22px;
            flex-wrap: wrap;
        }

        .pagination-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .rows-per-page {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 42px;
            padding: 6px 8px 6px 12px;
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }

        .rows-label {
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }

        .rows-select-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            min-width: 76px;
            padding-right: 18px;
        }

        .rows-select {
            min-width: 100%;
            height: 32px;
            padding: 0 22px 0 8px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: var(--text);
            font-family: "Roboto", "Segoe UI", Arial, sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.35;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
        }

        .rows-select option,
        .field select option {
            font-family: "Roboto", "Segoe UI", Arial, sans-serif;
            font-size: 0.84rem;
            padding: 10px 14px;
        }

        .rows-select-icon {
            position: absolute;
            right: 0;
            width: 16px;
            height: 16px;
            color: var(--muted);
            pointer-events: none;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination-button {
            min-width: 40px;
            min-height: 40px;
            padding: 0 14px;
            border-radius: 13px;
        }

        .pagination-button:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            transform: none;
        }

        .pagination-page,
        .helper-text {
            color: var(--muted);
            font-size: 0.84rem;
        }

        body.modal-open {
            overflow: hidden;
        }

        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            z-index: 220;
        }

        .modal.is-open {
            display: flex;
        }

        .modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(8px);
        }

        .modal-panel {
            position: relative;
            width: min(780px, 100%);
            max-height: min(88vh, 920px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            scroll-behavior: smooth;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.14), 0 2px 8px rgba(15, 23, 42, 0.08);
        }

        .modal-panel.modal-panel-xl {
            width: min(1180px, 100%);
            max-height: min(92vh, 1080px);
        }

        .modal-panel.modal-panel-xl .modal-head {
            z-index: 5;
            padding-bottom: 18px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.94));
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .modal-panel.modal-panel-xl .modal-body {
            padding-top: 18px;
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            flex: 0 0 auto;
            padding: 22px 22px 0;
        }

        .modal-head p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 0.8rem;
        }

        .modal-head h2 {
            font-size: 1rem;
            line-height: 1.25;
            font-weight: 650;
        }

        .icon-button {
            width: 38px;
            height: 38px;
            padding: 0;
        }

        .modal-head .icon-button {
            flex: 0 0 38px;
            border-radius: 999px;
            border-color: rgba(15, 23, 42, 0.08);
            background: rgba(248, 250, 252, 0.92);
            color: #475467;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .modal-head .icon-button .icon {
            width: 17px;
            height: 17px;
            stroke-width: 2;
        }

        .modal-head .icon-button:hover {
            border-color: transparent;
            background: #111827;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.14);
        }

        .modal-body {
            padding: 22px;
            display: grid;
            gap: 18px;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            scroll-behavior: smooth;
        }

        .modal-body::-webkit-scrollbar {
            width: 10px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.04);
            border-radius: 999px;
            margin: 8px 0;
        }

        .modal-body::-webkit-scrollbar-thumb {
            border: 2px solid rgba(255, 255, 255, 0.92);
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.28);
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: rgba(15, 23, 42, 0.42);
        }

        .modal-panel > form {
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
            flex-direction: column;
        }

        .modal-panel > form .modal-body {
            flex: 1 1 auto;
        }

        .form-grid,
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field.full,
        .detail-grid .full {
            grid-column: 1 / -1;
        }

        .field label,
        .detail-item span {
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .field input,
        .field textarea,
        .field select {
            padding: 12px 14px;
            outline: none;
            font-family: "Roboto", "Segoe UI", Arial, sans-serif;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .field select {
            min-height: 45px;
            padding-right: 42px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            background:
                linear-gradient(45deg, transparent 50%, #667085 50%),
                linear-gradient(135deg, #667085 50%, transparent 50%),
                white;
            background-position:
                calc(100% - 20px) 51%,
                calc(100% - 14px) 51%,
                0 0;
            background-size:
                6px 6px,
                6px 6px,
                100% 100%;
            background-repeat: no-repeat;
        }

        .field select:hover,
        .rows-select:hover {
            background-color: rgba(248, 250, 252, 0.96);
        }

        .field input:focus,
        .field textarea:focus,
        .field select:focus,
        .rows-select:focus {
            border-color: rgba(209, 77, 31, 0.34);
            box-shadow: 0 0 0 4px rgba(209, 77, 31, 0.1);
        }

        .password-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-field input {
            padding-right: 48px;
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.04);
            color: #667085;
            cursor: pointer;
            transition: background 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            background: rgba(209, 77, 31, 0.12);
            color: var(--accent-deep);
            transform: translateY(-1px);
            outline: none;
        }

        .password-toggle .icon {
            width: 18px;
            height: 18px;
            stroke-width: 2;
        }

        .password-toggle .icon[hidden] {
            display: none;
        }

        .field textarea {
            min-height: 116px;
            resize: vertical;
            font-family: "IBM Plex Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.88rem;
        }

        .detail-item {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--line-soft);
            background: rgba(255, 255, 255, 0.92);
        }

        .detail-item strong {
            display: block;
            margin-top: 6px;
            word-break: break-word;
        }

        .compact-data-table th,
        .compact-data-table td {
            padding: 10px 12px;
            font-size: 0.82rem;
            vertical-align: top;
            line-height: 1.5;
        }

        .compact-data-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: rgba(248, 250, 252, 0.98);
        }

        .compact-data-table tbody tr:nth-child(even) td {
            background: rgba(248, 250, 252, 0.52);
        }

        .detail-hero {
            display: grid;
            gap: 18px;
            padding: 20px;
            border-radius: 24px;
            border: 1px solid rgba(241, 129, 32, 0.16);
            background:
                radial-gradient(circle at top right, rgba(241, 129, 32, 0.14), transparent 30%),
                linear-gradient(135deg, rgba(255, 249, 243, 0.98), rgba(255, 255, 255, 0.98));
        }

        .detail-hero-main {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .detail-eyebrow {
            display: inline-block;
            margin-bottom: 8px;
            color: #b45309;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .detail-hero-main p,
        .detail-helper,
        .detail-empty {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .detail-hero-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .detail-stat {
            padding: 14px;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.94);
        }

        .detail-stat span {
            display: block;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .detail-stat strong {
            display: block;
            margin-top: 8px;
            font-size: 0.96rem;
            line-height: 1.45;
        }

        .bridge-summary-hero {
            gap: 12px;
            padding: 16px 18px;
            border-radius: 18px;
            background:
                radial-gradient(circle at top right, rgba(241, 129, 32, 0.1), transparent 26%),
                rgba(255, 255, 255, 0.96);
        }

        .bridge-summary-row {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .bridge-summary-primary {
            align-items: center;
        }

        .bridge-summary-primary .detail-hero-icon {
            width: 38px;
            height: 38px;
            border-radius: 999px;
        }

        .bridge-summary-copy {
            display: flex;
            align-items: baseline;
            gap: 10px;
            min-width: 0;
            flex-wrap: wrap;
        }

        .bridge-summary-copy .detail-eyebrow {
            margin: 0;
            font-size: 0.68rem;
        }

        .bridge-summary-copy h3 {
            font-size: 1rem;
            line-height: 1.25;
        }

        .bridge-summary-route {
            color: var(--muted);
            font-size: 0.84rem;
            font-weight: 650;
        }

        .bridge-summary-tags {
            flex-wrap: wrap;
            gap: 8px;
        }

        .bridge-summary-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            padding: 5px 10px 5px 5px;
            border-radius: 999px;
            border: 1px solid rgba(37, 99, 235, 0.22);
            background: rgba(255, 255, 255, 0.86);
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 650;
            white-space: nowrap;
        }

        .bridge-summary-tag .tag-icon-circle {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 24px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: #1d4ed8;
        }

        .bridge-summary-tag .tag-icon {
            width: 14px;
            height: 14px;
        }

        .bridge-summary-tag .tag-value {
            color: var(--text);
            font-weight: 700;
        }

        .detail-stack {
            display: grid;
            gap: 14px;
        }

        .detail-section {
            display: grid;
            gap: 14px;
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .detail-section-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .detail-section-title {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .detail-section-icon,
        .detail-hero-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            color: #b45309;
            background: rgba(241, 129, 32, 0.13);
            border: 1px solid rgba(241, 129, 32, 0.12);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.64);
            flex: 0 0 auto;
        }

        .detail-section-icon .icon,
        .detail-hero-icon .icon {
            width: 18px;
            height: 18px;
            flex-basis: 18px;
            stroke-width: 1.9;
        }

        .detail-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            border-radius: 8px;
            border: 1px solid rgba(37, 99, 235, 0.24);
            background: rgba(255, 255, 255, 0.78);
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 650;
        }

        .detail-chip-grid {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .detail-kv-table,
        .detail-record-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.83rem;
        }

        .detail-kv-table th,
        .detail-kv-table td,
        .detail-record-table th,
        .detail-record-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            vertical-align: top;
            text-align: left;
        }

        .detail-kv-table th,
        .detail-record-table th {
            color: var(--muted);
            font-size: 0.73rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(248, 250, 252, 0.94);
        }

        .detail-kv-table th {
            width: 30%;
            min-width: 200px;
        }

        .detail-kv-table td,
        .detail-record-table td {
            color: var(--text);
            background: rgba(255, 255, 255, 0.92);
            word-break: break-word;
        }

        .document-preview-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 100%;
            padding: 8px 10px;
            border: 1px solid rgba(37, 99, 235, 0.16);
            border-radius: 10px;
            background: rgba(239, 246, 255, 0.84);
            color: #1d4ed8;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .document-preview-button .icon {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
        }

        .document-preview-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .document-preview-frame {
            width: 100%;
            min-height: min(72vh, 720px);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 14px;
            background: #f8fafc;
        }

        .document-preview-image {
            display: block;
            max-width: 100%;
            max-height: min(72vh, 720px);
            margin: 0 auto;
            border-radius: 14px;
            object-fit: contain;
        }

        .detail-record-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .detail-record-table tbody tr:nth-child(even) td {
            background: rgba(248, 250, 252, 0.5);
        }

        .detail-kv-table tr:last-child th,
        .detail-kv-table tr:last-child td,
        .detail-record-table tr:last-child th,
        .detail-record-table tr:last-child td {
            border-bottom: 0;
        }

        .detail-table-wrap {
            overflow: auto;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .detail-table-wrap table {
            min-width: 100%;
        }

        .detail-map-layout {
            display: grid;
            grid-template-columns: minmax(220px, 0.36fr) minmax(0, 1fr);
            gap: 14px;
            align-items: stretch;
        }

        .detail-map-card {
            position: relative;
            min-height: 242px;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: #111827;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
        }

        .detail-map-canvas {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .detail-map-card .leaflet-container {
            width: 100%;
            height: 100%;
            background: #111827;
            font: inherit;
        }

        .detail-map-card .leaflet-control-attribution {
            border-radius: 999px 0 0 0;
            background: rgba(255, 255, 255, 0.72);
            color: #475467;
            font-size: 0.62rem;
        }

        .detail-map-card .detail-map-leaflet-marker {
            overflow: visible;
        }

        .detail-map-meta {
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 12px;
            z-index: 2;
            display: grid;
            gap: 2px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(15, 23, 42, 0.72);
            color: #ffffff;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.2);
        }

        .detail-map-meta span {
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .detail-map-meta strong {
            font-size: 0.82rem;
            line-height: 1.35;
            word-break: break-word;
        }

        .detail-map-marker {
            display: block;
            position: relative;
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        .detail-map-marker .marker-wave,
        .detail-map-marker .marker-dot {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            transform-origin: center;
        }

        .detail-map-marker .marker-wave {
            border: 2px solid rgba(59, 130, 246, 0.9);
            animation: detailMapRipple 2.4s ease-out infinite;
        }

        .detail-map-marker .marker-wave:nth-child(2) {
            animation-delay: 0.8s;
        }

        .detail-map-marker .marker-wave:nth-child(3) {
            animation-delay: 1.6s;
        }

        .detail-map-marker .marker-dot {
            inset: 4px;
            z-index: 2;
            border: 2px solid #ffffff;
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.28), 0 0 22px rgba(37, 99, 235, 0.95);
            animation: detailMapBlink 1.15s ease-in-out infinite;
        }

        .coordinate-picker {
            display: grid;
            gap: 12px;
        }

        .coordinate-search {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
        }

        .coordinate-map-shell {
            position: relative;
            min-height: min(62vh, 560px);
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: #111827;
        }

        .coordinate-map,
        .coordinate-map .leaflet-container {
            position: absolute;
            inset: 0;
            z-index: 1;
            width: 100%;
            height: 100%;
            background: #111827;
            font: inherit;
        }

        .coordinate-spotlight {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 3;
            width: 46px;
            height: 46px;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .coordinate-spotlight::before,
        .coordinate-spotlight::after {
            content: "";
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 18px rgba(37, 99, 235, 0.55);
        }

        .coordinate-spotlight::before {
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            transform: translateX(-50%);
        }

        .coordinate-spotlight::after {
            left: 0;
            right: 0;
            top: 50%;
            height: 2px;
            transform: translateY(-50%);
        }

        .coordinate-spotlight span {
            position: absolute;
            inset: 11px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.18), 0 0 24px rgba(37, 99, 235, 0.68);
        }

        .coordinate-live {
            position: absolute;
            left: 14px;
            bottom: 14px;
            z-index: 3;
            display: grid;
            grid-template-columns: auto auto;
            gap: 2px 10px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(15, 23, 42, 0.74);
            color: #ffffff;
            backdrop-filter: blur(12px);
        }

        .coordinate-live span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .coordinate-live strong {
            font-size: 0.82rem;
            line-height: 1.35;
            font-variant-numeric: tabular-nums;
        }

        @keyframes detailMapRipple {
            0% {
                opacity: 0.85;
                transform: scale(0.65);
            }

            72% {
                opacity: 0.18;
            }

            100% {
                opacity: 0;
                transform: scale(5.4);
            }
        }

        @keyframes detailMapBlink {
            0%,
            100% {
                transform: scale(0.9);
                opacity: 0.75;
            }

            50% {
                transform: scale(1.22);
                opacity: 1;
            }
        }

        @keyframes coordinateButtonPulse {
            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.22);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.08);
            }
        }

        @keyframes coordinateIconBlink {
            0%,
            100% {
                opacity: 0.55;
                transform: translateY(0) scale(0.95);
            }

            50% {
                opacity: 1;
                transform: translateY(-1px) scale(1.12);
            }
        }

        .detail-empty {
            padding: 20px;
            border-radius: 18px;
            border: 1px dashed rgba(15, 23, 42, 0.14);
            background: rgba(248, 250, 252, 0.74);
            text-align: center;
        }

        .json-preview {
            margin: 0;
            padding: 16px;
            border-radius: 16px;
            background: #111827;
            color: #f8fafc;
            overflow: auto;
            font-size: 0.82rem;
            line-height: 1.55;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 0 22px 22px;
            flex-wrap: wrap;
        }

        .feedback {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(180, 35, 24, 0.12);
            background: rgba(180, 35, 24, 0.08);
            color: var(--danger);
            font-size: 0.88rem;
        }

        .feedback.success {
            border-color: rgba(15, 118, 110, 0.12);
            background: rgba(15, 118, 110, 0.08);
            color: var(--ok);
        }

        .superadmin-kpis {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .superadmin-kpi {
            padding: 20px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--line-soft);
        }

        .superadmin-kpi span {
            display: block;
            color: var(--muted);
            font-size: 0.84rem;
        }

        .superadmin-kpi strong {
            display: block;
            margin-top: 10px;
            font-size: 1.9rem;
            line-height: 1;
        }

        .superadmin-kpi small {
            display: block;
            margin-top: 8px;
            color: var(--muted);
            line-height: 1.55;
        }

        .checkbox-field {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: white;
        }

        .checkbox-field input {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        .checkbox-field span {
            color: var(--text);
            font-size: 0.92rem;
            font-weight: 500;
        }

        .token-ability-grid {
            display: grid;
            gap: 10px;
        }

        .token-ability-card {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.94);
        }

        .token-ability-card input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
        }

        .token-ability-card strong,
        .token-ability-card span {
            display: block;
        }

        .token-ability-card span {
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .secret-preview {
            display: grid;
            gap: 10px;
            margin-top: 14px;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid rgba(15, 118, 110, 0.12);
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.08), rgba(109, 74, 255, 0.08));
        }

        .secret-preview strong {
            display: block;
            margin-bottom: 4px;
        }

        .secret-code {
            display: block;
            width: 100%;
            min-height: 92px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #111827;
            color: #f8fafc;
            overflow: auto;
            font-family: "IBM Plex Mono", "SFMono-Regular", Consolas, monospace;
            font-size: 0.84rem;
            line-height: 1.55;
            word-break: break-all;
            resize: vertical;
            user-select: text;
            white-space: pre-wrap;
        }

        .logout-form {
            margin: 0;
        }

        body.sidebar-collapsed .dashboard {
            grid-template-columns: 96px minmax(0, 1fr);
        }

        body.sidebar-collapsed .sidebar {
            width: 96px;
            min-width: 96px;
            max-width: 96px;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body.sidebar-collapsed .sidebar-header {
            justify-content: center;
        }

        body.sidebar-collapsed .brand-copy,
        body.sidebar-collapsed .nav-title,
        body.sidebar-collapsed .nav-subtitle,
        body.sidebar-collapsed .nav-copy,
        body.sidebar-collapsed .nav-toggle-indicator,
        body.sidebar-collapsed .nav-badge,
        body.sidebar-collapsed .nav-children {
            display: none;
        }

        body.sidebar-collapsed .brand {
            justify-content: center;
        }

        body.sidebar-collapsed .nav-link,
        body.sidebar-collapsed .nav-button {
            justify-content: center;
            padding-left: 10px;
            padding-right: 10px;
        }

        body.sidebar-collapsed .nav-main {
            justify-content: center;
            min-width: 0;
        }

        body.sidebar-collapsed .sidebar-toggle .icon {
            transform: rotate(180deg);
        }

        @media (max-width: 1180px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                width: 100%;
                min-width: 0;
                max-width: none;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
                overflow: visible;
            }

            body.sidebar-collapsed .dashboard {
                grid-template-columns: 1fr;
            }

            body.sidebar-collapsed .brand-copy {
                display: block;
            }

            body.sidebar-collapsed .nav-title {
                display: block;
            }

            body.sidebar-collapsed .nav-subtitle {
                display: block;
            }

            body.sidebar-collapsed .nav-copy {
                display: block;
            }

            body.sidebar-collapsed .nav-badge {
                display: inline-flex;
            }

            body.sidebar-collapsed .nav-children {
                display: grid;
            }

            body.sidebar-collapsed .sidebar-toggle {
                position: static;
            }

            body.sidebar-collapsed .sidebar-action .nav-copy {
                display: block;
            }
        }

        @media (max-width: 1040px) {
            .workspace,
            .overview-grid {
                grid-template-columns: 1fr;
            }

            .relation-graph-stage {
                grid-template-columns: 1fr;
            }

            .detail-hero-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .detail-map-layout {
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

            .topbar-actions {
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

            .master-data-toolbar,
            .pagination-bar,
            .modal-head,
            .modal-body,
            .modal-actions {
                padding-left: 18px;
                padding-right: 18px;
            }

            .form-grid,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .coordinate-input-grid {
                grid-template-columns: 1fr;
            }

            .nested-detail-grid {
                grid-template-columns: 1fr;
            }

            .coordinate-action-field {
                min-width: 0;
            }

            .detail-hero-stats {
                grid-template-columns: 1fr;
            }

            .detail-map-card {
                min-height: 220px;
            }

            .search-field {
                min-width: 100%;
            }

            .superadmin-kpis {
                grid-template-columns: 1fr;
            }

            .user-dropdown {
                right: auto;
                left: 0;
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
    $isSuperadmin = $user?->hasRole(\App\Enums\UserRole::Superadmin) ?? false;
    $isAdmin = $user?->hasRole(\App\Enums\UserRole::Admin) ?? false;
    $canViewDocumentation = true;
    $canViewMasterData = $isSuperadmin || $isAdmin || ($user?->hasRole(\App\Enums\UserRole::Operator) ?? false);
    $canViewMonitoring = $isSuperadmin || $isAdmin;
    $canViewFullDiagnostics = $isSuperadmin;
    $hasOperationalDashboard = $canViewMasterData || $canViewMonitoring;
    $isDocumentationOnly = ! $hasOperationalDashboard && ! $canViewFullDiagnostics;
    $masterDataMenu = $masterDataMenu ?? [];
    $masterDataPage = $masterDataPage ?? null;
    $bridgeSourceTablePage = $bridgeSourceTablePage ?? null;
    $tunnelSourceTablePage = $tunnelSourceTablePage ?? null;
    $superadminUserPage = $superadminUserPage ?? null;
    $superadminApiClientPage = $superadminApiClientPage ?? null;
    $activeMasterDataKey = $masterDataPage['key'] ?? $bridgeSourceTablePage['parent_key'] ?? $tunnelSourceTablePage['parent_key'] ?? null;
    $bridgeModule = $overview['bridge_module'] ?? null;
    $metadataModules = [
        ['key' => 'jembatan', 'label' => 'Jembatan', 'status' => $bridgeModule ? count($bridgeModule['fields']) . ' field' : 'Pending', 'fields' => $bridgeModule['fields'] ?? []],
        ['key' => 'terowongan', 'label' => 'Terowongan', 'status' => 'Pending', 'fields' => []],
    ];
    $infrastructureDomains = $overview['infrastructure_domains'] ?? collect();
    $currentPage = $currentPage ?? ($isDocumentationOnly ? 'documentation' : 'overview');
    $bridgeFieldValuesEndpoint = route('dashboard.metadata.jembatan.fields.values', ['field' => '__field__']);
    $tagIcon = function (string $icon): string {
        return match ($icon) {
            'api' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M3 12h4l2.5-6 5 12 2.5-6H21"/></svg>',
            'book' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg>',
            'check' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
            'clock' => '<svg class="tag-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
            'data' => '<svg class="tag-icon" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>',
            'field' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h10"/></svg>',
            'file' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v6h6"/></svg>',
            'key' => '<svg class="tag-icon" viewBox="0 0 24 24"><circle cx="7.5" cy="14.5" r="3.5"/><path d="M10 12 21 1"/><path d="m16 6 2 2"/><path d="m13 9 2 2"/></svg>',
            'layers' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5z"/><path d="m3 12 9 5 9-5"/><path d="m3 16 9 5 9-5"/></svg>',
            'mapping' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 7h10"/><path d="M10 3l4 4-4 4"/><path d="M20 17H10"/><path d="m14 13-4 4 4 4"/></svg>',
            'route' => '<svg class="tag-icon" viewBox="0 0 24 24"><circle cx="6" cy="19" r="3"/><circle cx="18" cy="5" r="3"/><path d="M9 19h2a7 7 0 0 0 7-7V8"/></svg>',
            'table' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 10h16"/><path d="M9 5v14"/></svg>',
            'user' => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>',
            default => '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 10 14 4H5v9l6 6z"/><path d="M8 8h.01"/></svg>',
        };
    };
    $tag = function (string $label, mixed $value = null, string $icon = 'tag', string $class = 'menu-tag', string $attributes = '') use ($tagIcon): \Illuminate\Support\HtmlString {
        $valueHtml = $value === null ? '' : '<span class="tag-value">'.e((string) $value).'</span>';

        return new \Illuminate\Support\HtmlString(
            '<span class="'.e($class).'"'.$attributes.'>'.$tagIcon($icon).'<span class="tag-label">'.e($label).'</span>'.$valueHtml.'</span>'
        );
    };
    $navChildIcon = function (string $kind): string {
        return match ($kind) {
            'combine' => '<svg class="icon" viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5z"/><path d="m3 12 9 5 9-5"/><path d="m3 16 9 5 9-5"/></svg>',
            'master' => '<svg class="icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 10h16"/><path d="M9 5v14"/></svg>',
            'lookup' => '<svg class="icon" viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="17" r="3"/><path d="M11 8h2a4 4 0 0 1 4 4v2"/><path d="M8 11v2a4 4 0 0 0 4 4h2"/></svg>',
            default => '<svg class="icon" viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h10"/></svg>',
        };
    };

    $pageMeta = [
        'overview' => ['title' => 'Dashboard', 'description' => 'Ringkasan operasional dan data inti.'],
        'documentation' => ['title' => 'Dokumentasi API', 'description' => 'Akses Swagger UI dan spesifikasi OpenAPI.'],
        'master-data' => ['title' => 'Master Data', 'description' => 'Tipe data dan record master data terbaru.'],
        'master-data-entity' => ['title' => $masterDataPage['label'] ?? 'Master Data', 'description' => 'Grid data aktif.'],
        'bridge-source-table' => ['title' => $bridgeSourceTablePage['label'] ?? 'Tabel Source Jembatan', 'description' => $bridgeSourceTablePage['description'] ?? 'Data tabel source dari dump SQL.'],
        'tunnel-source-table' => ['title' => $tunnelSourceTablePage['label'] ?? 'Tabel Terowongan', 'description' => $tunnelSourceTablePage['description'] ?? 'Data tabel source Terowongan.'],
        'superadmin-users' => ['title' => $superadminUserPage['label'] ?? 'Manajemen User', 'description' => 'CRUD akun internal, role, dan kontrol akses dashboard.'],
        'superadmin-api-clients' => ['title' => $superadminApiClientPage['label'] ?? 'Bearer Key API', 'description' => 'Kelola client API dan generate bearer token dengan desain dashboard modern.'],
    ];

    $currentPageMeta = $pageMeta[$currentPage] ?? $pageMeta['overview'];

    $quickMenu = [
        ['label' => 'Dokumentasi API', 'href' => route('dashboard.documentation'), 'tag' => 'Docs'],
        ['label' => 'OpenAPI Spec', 'href' => route('docs.openapi'), 'tag' => 'Spec'],
    ];

    if ($canViewMonitoring) {
        $quickMenu[] = ['label' => 'Health Check API', 'href' => '/api/v1/health', 'tag' => 'API'];
    }

    if ($canViewFullDiagnostics) {
        $quickMenu[] = ['label' => 'JSON Sistem', 'href' => route('dashboard.system'), 'tag' => 'Internal'];
        $quickMenu[] = ['label' => 'Manajemen User', 'href' => route('dashboard.superadmin.users'), 'tag' => 'IAM'];
        $quickMenu[] = ['label' => 'Bearer Key API', 'href' => route('dashboard.superadmin.api-clients'), 'tag' => 'Token'];
    }
@endphp
<div class="dashboard">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-mark">
                    <img src="/images/logo/logo.svg" alt="Logo DJKA">
                </div>
                <div class="brand-copy">
                    <strong>Master Data</strong>
                    <span>Prasarana</span>
                </div>
            </div>
        </div>

        <div class="nav-section">
            @if ($isDocumentationOnly)
                <p class="nav-title">Dokumentasi</p>
                <div class="nav-list">
                    <a class="nav-link {{ $currentPage === 'documentation' ? 'active' : '' }}" href="{{ route('dashboard.documentation') }}">
                        <div class="nav-main">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M8 4h8"/><path d="M8 20h8"/><path d="M5 8h14"/><path d="M5 16h14"/><path d="M7 8v8"/><path d="M17 8v8"/></svg>
                            <div class="nav-copy"><strong>Dokumentasi API</strong></div>
                        </div>
                    </a>
                    <a class="nav-link" href="{{ route('docs.openapi') }}">
                        <div class="nav-main">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M8 7h8"/><path d="M8 12h8"/><path d="M8 17h5"/><path d="M5 3h14v18H5z"/></svg>
                            <div class="nav-copy"><strong>OpenAPI Spec</strong></div>
                        </div>
                    </a>
                </div>
            @else
                <p class="nav-title">Utama</p>
                <div class="nav-list">
                    <a class="nav-link {{ $currentPage === 'overview' ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                        <div class="nav-main">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                            <div class="nav-copy"><strong>Beranda</strong></div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        @if (! $isDocumentationOnly)
            <div class="nav-section">
                @if ($canViewMasterData)
                    <p class="nav-title">Master Data</p>
                    <div class="nav-list">
                        @foreach ($masterDataMenu as $item)
                            @php
                                $isActiveMasterItem = in_array($currentPage, ['master-data-entity', 'bridge-source-table', 'tunnel-source-table'], true) && $activeMasterDataKey === $item['key'];
                            @endphp
                            <div class="nav-group {{ $isActiveMasterItem ? 'is-expanded' : '' }}" data-master-nav-group data-master-nav-key="{{ $item['key'] }}">
                                <button class="nav-link {{ $isActiveMasterItem ? 'active' : '' }}" type="button" data-master-nav-toggle aria-expanded="{{ $isActiveMasterItem ? 'true' : 'false' }}">
                                    <div class="nav-main">
                                        @switch($item['key'])
                                            @case('jembatan')
                                                <svg class="icon" viewBox="0 0 24 24"><path d="M4 18h16"/><path d="M6 18V9l3-3 3 3 3-3 3 3v9"/><path d="M9 12h6"/></svg>
                                                @break
                                            @case('terowongan')
                                                <svg class="icon" viewBox="0 0 24 24"><path d="M4 18V9a8 8 0 0 1 16 0v9"/><path d="M8 18V9a4 4 0 0 1 8 0v9"/><path d="M3 18h18"/><path d="M6 13h12"/></svg>
                                                @break
                                            @case('jalur')
                                                <svg class="icon" viewBox="0 0 24 24"><path d="M7 4h10"/><path d="M8 4l-2 16"/><path d="M16 4l2 16"/><path d="M6.5 10h11"/><path d="M5.5 16h13"/></svg>
                                                @break
                                            @case('fasilitas-operasional')
                                                <svg class="icon" viewBox="0 0 24 24"><path d="M4 20h16"/><path d="M6 20V8l6-4 6 4v12"/><path d="M10 12h4"/><path d="M10 16h4"/></svg>
                                                @break
                                            @case('sertifikat')
                                                <svg class="icon" viewBox="0 0 24 24"><path d="M7 3h8l4 4v14H7z"/><path d="M15 3v5h5"/><path d="M10 13h6"/><path d="M10 17h4"/></svg>
                                                @break
                                            @default
                                                <svg class="icon" viewBox="0 0 24 24"><path d="M3 8h18v11H3z"/><path d="M8 8V5h8v3"/><path d="M12 13v6"/></svg>
                                        @endswitch
                                        <div class="nav-copy">
                                            <strong>{{ $item['label'] }}</strong>
                                            <span>{{ $item['is_available'] ? number_format($item['record_count']) . ' data' : 'Siap diisi' }}</span>
                                        </div>
                                    </div>
                                    <svg class="nav-toggle-indicator" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
                                </button>
                                @if (!empty($item['children']))
                                    <div class="nav-children">
                                        @foreach ($item['children'] as $child)
                                            @php
                                                $isPrimaryChild = ($child['type'] ?? null) === 'entity';
                                                $childKind = $child['kind'] ?? ($isPrimaryChild ? 'combine' : 'detail');
                                                $isActiveChild = $isPrimaryChild
                                                    ? ($currentPage === 'master-data-entity' && $activeMasterDataKey === $item['key'])
                                                    : (
                                                        ($currentPage === 'bridge-source-table' && ($bridgeSourceTablePage['table'] ?? null) === ($child['table'] ?? null))
                                                        || ($currentPage === 'tunnel-source-table' && ($tunnelSourceTablePage['table'] ?? null) === ($child['table'] ?? null))
                                                    );
                                            @endphp
                                            <a class="nav-child-link nav-child-link-{{ $childKind }} {{ $isActiveChild ? 'active' : '' }}" href="{{ $child['href'] }}">
                                                <span class="nav-child-icon">{!! $navChildIcon($childKind) !!}</span>
                                                <span class="nav-child-text">
                                                    <span>{{ $child['label'] ?? $child['table'] }}</span>
                                                    <small>· {{ number_format($child['row_count']) }}</small>
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="nav-section">
                <p class="nav-title">Dokumentasi</p>
                <div class="nav-list">
                    @if ($canViewDocumentation)
                        <a class="nav-link {{ $currentPage === 'documentation' ? 'active' : '' }}" href="{{ route('dashboard.documentation') }}">
                            <div class="nav-main">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M8 4h8"/><path d="M8 20h8"/><path d="M5 8h14"/><path d="M5 16h14"/><path d="M7 8v8"/><path d="M17 8v8"/></svg>
                                <div class="nav-copy"><strong>Dokumentasi API</strong></div>
                            </div>
                        </a>
                        <a class="nav-link" href="{{ route('docs.openapi') }}" target="_blank" rel="noreferrer">
                            <div class="nav-main">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M8 7h8"/><path d="M8 12h8"/><path d="M8 17h5"/><path d="M5 3h14v18H5z"/></svg>
                                <div class="nav-copy"><strong>OpenAPI Spec</strong></div>
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            @if ($isSuperadmin)
                <div class="nav-section">
                    <p class="nav-title">Superadmin</p>
                    <p class="nav-subtitle">Identity & API</p>
                    <div class="nav-list">
                        <a class="nav-link {{ $currentPage === 'superadmin-users' ? 'active' : '' }}" href="{{ route('dashboard.superadmin.users') }}">
                            <div class="nav-main">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
                                <div class="nav-copy">
                                    <strong>Manajemen User</strong>
                                    <span>{{ number_format($metrics['users']) }} akun internal</span>
                                </div>
                            </div>
                        </a>
                        <a class="nav-link {{ $currentPage === 'superadmin-api-clients' ? 'active' : '' }}" href="{{ route('dashboard.superadmin.api-clients') }}">
                            <div class="nav-main">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M7 7h10v10H7z"/><path d="M3 10V8a1 1 0 0 1 1-1h2"/><path d="M21 10V8a1 1 0 0 0-1-1h-2"/><path d="M3 14v2a1 1 0 0 0 1 1h2"/><path d="M21 14v2a1 1 0 0 1-1 1h-2"/><path d="m10 12 2 2 4-4"/></svg>
                                <div class="nav-copy">
                                    <strong>Bearer Key API</strong>
                                    <span>{{ number_format($metrics['api_clients']) }} client API</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
        @endif

        <div class="sidebar-action">
            <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Collapse sidebar" aria-expanded="true">
                <svg class="icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M9 5v14"/><path d="m15 9-3 3 3 3"/></svg>
            </button>
        </div>
    </aside>

    <main class="content">
        <div class="topbar">
            <div class="topbar-title">
                <strong>{{ $currentPageMeta['title'] }}</strong>
                <span>{{ $app['name'] }} · {{ $app['generated_at']->format('d M Y H:i') }} UTC</span>
            </div>
            <div class="topbar-actions">
                <details class="user-menu">
                    <summary class="user-chip" aria-label="Buka menu akun">
                        <div class="user-avatar">{{ $userInitial }}</div>
                        <div class="user-chip-text">
                            <strong>{{ $userName }}</strong>
                            <span>{{ $userRoleLabel }}</span>
                        </div>
                        <span class="user-menu-caret">
                            <svg class="user-menu-icon" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                        </span>
                    </summary>
                    <div class="user-dropdown">
                        <div class="user-menu-list">
                            <a class="user-menu-item" href="{{ route('docs.swagger') }}">
                                <svg class="user-menu-icon" viewBox="0 0 24 24"><path d="M8 4h8"/><path d="M8 20h8"/><path d="M5 8h14"/><path d="M5 16h14"/><path d="M7 8v8"/><path d="M17 8v8"/></svg>
                                <div class="user-menu-copy">
                                    <strong>Dokumentasi API</strong>
                                    <span>Buka Swagger UI internal</span>
                                </div>
                            </a>
                            <a class="user-menu-item" href="{{ route('docs.openapi') }}" target="_blank" rel="noreferrer">
                                <svg class="user-menu-icon" viewBox="0 0 24 24"><path d="M8 7h8"/><path d="M8 12h8"/><path d="M8 17h5"/><path d="M5 3h14v18H5z"/></svg>
                                <div class="user-menu-copy">
                                    <strong>OpenAPI Spec</strong>
                                    <span>Lihat spesifikasi JSON</span>
                                </div>
                            </a>
                            @if ($canViewMonitoring)
                                <a class="user-menu-item" href="/api/v1/health" target="_blank" rel="noreferrer">
                                    <svg class="user-menu-icon" viewBox="0 0 24 24"><path d="M3 12h4l2.5-6 5 12 2.5-6H21"/></svg>
                                    <div class="user-menu-copy">
                                        <strong>Health API</strong>
                                        <span>Ringkasan status endpoint</span>
                                    </div>
                                </a>
                            @endif
                            @if ($canViewFullDiagnostics)
                                <a class="user-menu-item" href="{{ route('dashboard.system') }}">
                                    <svg class="user-menu-icon" viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h10"/></svg>
                                    <div class="user-menu-copy">
                                        <strong>JSON Sistem</strong>
                                        <span>Snapshot internal dashboard</span>
                                    </div>
                                </a>
                            @endif
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button class="user-menu-button" type="submit">
                                    <svg class="user-menu-icon" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                                    <div class="user-menu-copy">
                                        <strong>Logout</strong>
                                        <span>Keluar dari sesi aktif</span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </details>
            </div>
        </div>

        @if ($currentPage === 'documentation')
            <section class="section">
                <div class="card">
                    <div class="card-body menu-list">
                        <a class="menu-item" href="{{ route('docs.swagger') }}">
                            <div class="menu-main">
                                <strong>Swagger UI</strong>
                                <span>Dokumentasi interaktif untuk eksplorasi endpoint.</span>
                            </div>
                            {!! $tag('Docs', null, 'book') !!}
                        </a>
                        <a class="menu-item" href="{{ route('docs.openapi') }}" target="_blank" rel="noreferrer">
                            <div class="menu-main">
                                <strong>OpenAPI Spec</strong>
                                <span>Spesifikasi JSON resmi untuk integrasi dan tooling.</span>
                            </div>
                            {!! $tag('Spec', null, 'file') !!}
                        </a>
                        @if ($canViewMonitoring)
                            <a class="menu-item" href="/api/v1/health" target="_blank" rel="noreferrer">
                                <div class="menu-main">
                                    <strong>Health Check API</strong>
                                    <span>Status endpoint health untuk verifikasi layanan.</span>
                                </div>
                                {!! $tag('API', null, 'api') !!}
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            @if ($bridgeModule)
                <section class="section">
                    <div class="table-card">
                        <div class="table-head">
                            <div>
                                <h2>Metadata</h2>
                            </div>
                            {!! $tag('Modul', count($metadataModules), 'layers') !!}
                        </div>
                        <div class="table-body">
                            <div class="metadata-accordion">
                                @foreach ($metadataModules as $module)
                                    @php
                                        $visibleModuleFields = collect($module['fields'] ?? [])
                                            ->reject(fn ($field) => $module['key'] === 'jembatan' && in_array($field['key'] ?? null, ['created_at', 'updated_at', 'created_by'], true))
                                            ->values();
                                    @endphp
                                    <details class="metadata-accordion-item">
                                        <summary class="metadata-accordion-summary">
                                            <span class="metadata-accordion-main">
                                                <strong>{{ $module['label'] }}</strong>
                                                <span>{{ $module['key'] === 'jembatan' ? 'Metadata field response Master Data Jembatan API.' : 'Metadata modul belum tersedia.' }}</span>
                                            </span>
                                            <span class="metadata-accordion-side">
                                                {!! $module['key'] === 'jembatan' ? $tag('Field', $visibleModuleFields->count(), 'field') : $tag('Status', 'Pending', 'clock') !!}
                                                <svg class="metadata-accordion-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                        </summary>
                                        <div class="metadata-accordion-panel">
                                            @if ($visibleModuleFields->isNotEmpty())
                                                <div class="metadata-table-wrap">
                                                    <table class="metadata-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Field</th>
                                                                <th>Path API</th>
                                                                <th>Sumber</th>
                                                                <th>Keterangan</th>
                                                                <th>Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($visibleModuleFields as $field)
                                                                <tr>
                                                                    <td>
                                                                        <strong>{{ $field['label'] }}</strong>
                                                                        <div class="table-note mono">{{ $field['type'] }}</div>
                                                                    </td>
                                                                    <td class="mono">{{ $field['api_path'] }}</td>
                                                                    <td class="mono">{{ $field['source'] }}</td>
                                                                    <td>{{ $field['description'] }}</td>
                                                                    <td>
                                                                        <button class="inline-button" type="button" data-bridge-field-values-open data-field-key="{{ $field['key'] }}" data-field-label="{{ $field['label'] }}">
                                                                            <svg class="icon" viewBox="0 0 24 24"><path d="M3 5h18"/><path d="M3 12h18"/><path d="M3 19h18"/></svg>
                                                                            Value
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="metadata-pending">Pending. Metadata modul ini akan diisi setelah endpoint dan schema API modul tersedia.</p>
                                            @endif
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        @if ($currentPage === 'superadmin-users' && $isSuperadmin && $superadminUserPage)
            <section class="section">
                <div class="card">
                    <div class="card-body">
                        <div class="section-header" style="margin-bottom: 18px;">
                            <div>
                                <h2>Manajemen User Superadmin</h2>
                                <p>Kelola akun internal, role akses, dan pembaruan password dari satu workspace yang mengikuti template dashboard saat ini.</p>
                            </div>
                            {!! $tag('Akun', number_format($superadminUserPage['records_count']), 'user') !!}
                        </div>
                        <div class="grid superadmin-kpis">
                            <article class="superadmin-kpi">
                                <span>Total User</span>
                                <strong>{{ number_format($metrics['users']) }}</strong>
                                <small>Semua akun internal yang sudah terdaftar di aplikasi.</small>
                            </article>
                            <article class="superadmin-kpi">
                                <span>Superadmin</span>
                                <strong>{{ number_format(collect($overview['user_roles'])->firstWhere('code', 'superadmin')['count'] ?? 0) }}</strong>
                                <small>Role dengan akses penuh ke modul sensitif dan pengaturan sistem.</small>
                            </article>
                            <article class="superadmin-kpi">
                                <span>Admin Operasional</span>
                                <strong>{{ number_format(collect($overview['user_roles'])->firstWhere('code', 'admin')['count'] ?? 0) }}</strong>
                                <small>Akun admin yang saat ini dapat mengelola modul operasional dan source data.</small>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="table-card" data-superadmin-users-app='@json($superadminUserPage)'>
                    <div class="master-data-toolbar">
                        <div class="master-data-toolbar-main">
                            <label class="search-field" aria-label="Cari user">
                                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                <input type="search" placeholder="Cari nama, email, role, atau UUID user" data-grid-search>
                            </label>
                        </div>
                        <div class="toolbar-actions">
                            {!! $tag('Data', number_format($superadminUserPage['records_count']), 'data', 'menu-tag', ' data-grid-count') !!}
                            <button class="action-button primary" type="button" data-grid-create>Tambah User</button>
                        </div>
                    </div>

                    <div class="table-body">
                        <div class="master-data-table-wrap">
                            <table class="master-data-table">
                                <thead data-grid-head></thead>
                                <tbody data-grid-body>
                                    <tr>
                                        <td colspan="6" class="grid-loading">Memuat data user...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pagination-bar">
                        <div class="pagination-meta">
                            <div class="rows-per-page">
                                <span class="rows-label">Baris</span>
                                <label class="rows-select-wrap" aria-label="Jumlah data per halaman">
                                    <select class="rows-select" data-grid-per-page>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                    <svg class="rows-select-icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </label>
                            </div>
                            <div class="helper-text" data-grid-summary>Menyiapkan data user...</div>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-button" type="button" data-grid-prev>Sebelumnya</button>
                            <span class="pagination-page" data-grid-page>Halaman 1</span>
                            <button class="pagination-button" type="button" data-grid-next>Berikutnya</button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if ($currentPage === 'superadmin-api-clients' && $isSuperadmin && $superadminApiClientPage)
            <section class="section">
                <div class="card">
                    <div class="card-body">
                        <div class="section-header" style="margin-bottom: 18px;">
                            <div>
                                <h2>Generator Bearer Key API</h2>
                                <p>Kelola client integrasi, batas rate limit, dan buat bearer token Sanctum langsung dari panel Superadmin dengan pola desain dashboard yang sama.</p>
                            </div>
                            {!! $tag('Client', number_format($superadminApiClientPage['records_count']), 'key') !!}
                        </div>
                        <div class="grid superadmin-kpis">
                            <article class="superadmin-kpi">
                                <span>Client API Aktif</span>
                                <strong>{{ number_format($metrics['active_api_clients']) }}</strong>
                                <small>Client aktif yang siap menerima bearer token dan mengakses endpoint API.</small>
                            </article>
                            <article class="superadmin-kpi">
                                <span>Token Tersimpan</span>
                                <strong>{{ number_format($metrics['access_tokens']) }}</strong>
                                <small>Total token Sanctum yang saat ini tercatat pada sistem.</small>
                            </article>
                            <article class="superadmin-kpi">
                                <span>Request Hari Ini</span>
                                <strong>{{ number_format($metrics['request_logs_today']) }}</strong>
                                <small>Aktivitas API terbaru untuk membantu validasi penggunaan integrasi.</small>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="table-card" data-superadmin-api-clients-app='@json($superadminApiClientPage)'>
                    <div class="master-data-toolbar">
                        <div class="master-data-toolbar-main">
                            <label class="search-field" aria-label="Cari client API">
                                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                <input type="search" placeholder="Cari nama client, code, owner, atau UUID" data-grid-search>
                            </label>
                        </div>
                        <div class="toolbar-actions">
                            {!! $tag('Data', number_format($superadminApiClientPage['records_count']), 'data', 'menu-tag', ' data-grid-count') !!}
                            <button class="action-button primary" type="button" data-grid-create>Tambah Client API</button>
                        </div>
                    </div>

                    <div class="table-body">
                        <div class="master-data-table-wrap">
                            <table class="master-data-table">
                                <thead data-grid-head></thead>
                                <tbody data-grid-body>
                                    <tr>
                                        <td colspan="8" class="grid-loading">Memuat client API...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pagination-bar">
                        <div class="pagination-meta">
                            <div class="rows-per-page">
                                <span class="rows-label">Baris</span>
                                <label class="rows-select-wrap" aria-label="Jumlah data per halaman">
                                    <select class="rows-select" data-grid-per-page>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                    <svg class="rows-select-icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </label>
                            </div>
                            <div class="helper-text" data-grid-summary>Menyiapkan client API...</div>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-button" type="button" data-grid-prev>Sebelumnya</button>
                            <span class="pagination-page" data-grid-page>Halaman 1</span>
                            <button class="pagination-button" type="button" data-grid-next>Berikutnya</button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if ($currentPage === 'master-data-entity' && $canViewMasterData && $masterDataPage)
            @if (($masterDataPage['mode'] ?? 'master-data') === 'bridge-source')
                <section class="section">
                    <div class="grid" style="gap: 14px; margin-bottom: 14px;">
                        <div class="card">
                            <div class="card-body">
                                <details class="relation-accordion" data-bridge-relation-map='@json($masterDataPage["relation_map"] ?? [])'>
                                    <summary class="relation-accordion-summary">
                                        <div class="relation-accordion-copy">
                                            <h2>Relasi Tabel Source Jembatan</h2>
                                            <p class="helper-text">Diagram relasi dibuat dengan Cytoscape dan default tertutup agar fokus awal tetap di tabel gabungan.</p>
                                        </div>
                                        <span class="relation-accordion-toggle">
                                            <span>{{ count($masterDataPage['relation_map'] ?? []) }} relasi</span>
                                            <svg class="icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </summary>
                                    <div class="relation-accordion-body">
                                        <div class="relation-legend">
                                            <span class="relation-legend-item"><span class="relation-legend-dot root"></span>Root</span>
                                            <span class="relation-legend-item"><span class="relation-legend-dot one-to-one"></span>One to One</span>
                                            <span class="relation-legend-item"><span class="relation-legend-dot one-to-many"></span>One to Many</span>
                                            <span class="relation-legend-item"><span class="relation-legend-dot lookup"></span>Lookup</span>
                                        </div>
                                        <div class="relation-graph-stage">
                                            <div class="relation-graph-canvas" data-relation-graph></div>
                                            <aside class="relation-graph-meta">
                                                <div class="relation-meta-card">
                                                    <span>Tabel</span>
                                                    <strong data-relation-preview-title>m_jembatan</strong>
                                                </div>
                                                <div class="relation-meta-card">
                                                    <span>Tipe</span>
                                                    <strong data-relation-preview-type>ROOT</strong>
                                                </div>
                                                <div class="relation-meta-card">
                                                    <span>Relasi</span>
                                                    <strong data-relation-preview-description>Induk data jembatan</strong>
                                                </div>
                                                <div class="relation-meta-card">
                                                    <span>Kunci</span>
                                                    <strong data-relation-preview-key>uniqid</strong>
                                                </div>
                                                <div class="relation-meta-card">
                                                    <span>Target</span>
                                                    <strong data-relation-preview-target>Tabel utama CRUD source</strong>
                                                </div>
                                            </aside>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>

                    <div class="table-card" data-bridge-source-app='@json($masterDataPage)'>
                        <div class="master-data-toolbar">
                            <div class="master-data-toolbar-main">
                                <label class="search-field" aria-label="Cari data jembatan source">
                                    <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    <input type="search" placeholder="Cari uniqid, nomor jembatan, jenis, KM/HM, atau stasiun" data-grid-search>
                                </label>
                            </div>
                            <div class="toolbar-actions">
                                {!! $tag('Data', number_format($masterDataPage['records_count']), 'data', 'menu-tag', ' data-grid-count') !!}
                                @if (($masterDataPage['crud_enabled'] ?? false))
                                    <button class="action-button primary" type="button" data-grid-create>Tambah</button>
                                @else
                                    {!! $tag('Mode', 'Dump SQL', 'data') !!}
                                @endif
                            </div>
                        </div>

                        @if (($masterDataPage['crud_enabled'] ?? false) === false)
                            <div class="master-data-alert">
                                Source database belum tersedia di environment ini. Tabel gabungan tetap dimuat dari dump SQL agar seluruh data jembatan tetap bisa ditinjau.
                            </div>
                        @endif

                        <div class="table-body">
                            <div class="master-data-table-wrap">
                                <table class="master-data-table compact-data-table">
                                    <thead data-grid-head></thead>
                                    <tbody data-grid-body>
                                        <tr>
                                            <td colspan="7" class="grid-loading">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="pagination-bar">
                            <div class="pagination-meta">
                                <div class="rows-per-page">
                                    <span class="rows-label">Baris</span>
                                    <label class="rows-select-wrap" aria-label="Jumlah data per halaman">
                                        <select class="rows-select" data-grid-per-page>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                        <svg class="rows-select-icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </label>
                                </div>
                                <div class="helper-text" data-grid-summary>Menyiapkan data...</div>
                            </div>
                            <div class="pagination-controls">
                                <button class="pagination-button" type="button" data-grid-prev>Sebelumnya</button>
                                <span class="pagination-page" data-grid-page>Halaman 1</span>
                                <button class="pagination-button" type="button" data-grid-next>Berikutnya</button>
                            </div>
                        </div>
                    </div>
                </section>
            @elseif (($masterDataPage['mode'] ?? 'master-data') === 'tunnel-source')
                <section class="section">
                    <div class="table-card" data-tunnel-source-app='@json($masterDataPage)'>
                        <div class="master-data-toolbar">
                            <div class="master-data-toolbar-main">
                                <label class="search-field" aria-label="Cari data terowongan">
                                    <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    <input type="search" placeholder="Cari nama, nomor BH, kode aset, atau KM/HM" data-grid-search>
                                </label>
                            </div>
                            <div class="toolbar-actions">
                                {!! $tag('Data', number_format($masterDataPage['records_count']), 'data', 'menu-tag', ' data-grid-count') !!}
                                {!! $tag('DB', 'prasarana_tunnel', 'data') !!}
                            </div>
                        </div>

                        <div class="table-body">
                            <div class="master-data-table-wrap">
                                <table class="master-data-table compact-data-table">
                                    <thead data-grid-head></thead>
                                    <tbody data-grid-body>
                                        <tr>
                                            <td colspan="10" class="grid-loading">Memuat data terowongan...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="pagination-bar">
                            <div class="pagination-meta">
                                <div class="rows-per-page">
                                    <span class="rows-label">Baris</span>
                                    <label class="rows-select-wrap" aria-label="Jumlah data per halaman">
                                        <select class="rows-select" data-grid-per-page>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                        <svg class="rows-select-icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </label>
                                </div>
                                <div class="helper-text" data-grid-summary>Menyiapkan data terowongan...</div>
                            </div>
                            <div class="pagination-controls">
                                <button class="pagination-button" type="button" data-grid-prev>Sebelumnya</button>
                                <span class="pagination-page" data-grid-page>Halaman 1</span>
                                <button class="pagination-button" type="button" data-grid-next>Berikutnya</button>
                            </div>
                        </div>
                    </div>
                </section>
            @else
                <section class="section">
                    <div class="table-card" data-master-data-app='@json($masterDataPage)'>
                        <div class="master-data-toolbar">
                            <div class="master-data-toolbar-main">
                                <label class="search-field" aria-label="Cari data">
                                    <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    <input type="search" placeholder="Cari kode, nama, atau deskripsi" data-grid-search>
                                </label>
                            </div>
                            <div class="toolbar-actions">
                                {!! $tag('Data', number_format($masterDataPage['records_count']), 'data', 'menu-tag', ' data-grid-count') !!}
                                <button class="action-button primary" type="button" data-grid-create>Tambah</button>
                            </div>
                        </div>

                        @if (! $masterDataPage['type_exists'])
                            <div class="master-data-alert">
                                Tipe `{{ $masterDataPage['type_code'] }}` belum terdaftar di `master_data_types`, namun halaman ini sudah siap dipakai untuk input awal.
                            </div>
                        @endif

                        <div class="table-body">
                            <div class="master-data-table-wrap">
                                <table class="master-data-table">
                                    <thead data-grid-head></thead>
                                    <tbody data-grid-body>
                                        <tr>
                                            <td colspan="6" class="grid-loading">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="pagination-bar">
                            <div class="pagination-meta">
                                <div class="rows-per-page">
                                    <span class="rows-label">Baris</span>
                                    <label class="rows-select-wrap" aria-label="Jumlah data per halaman">
                                        <select class="rows-select" data-grid-per-page>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                        <svg class="rows-select-icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </label>
                                </div>
                                <div class="helper-text" data-grid-summary>Menyiapkan data...</div>
                            </div>
                            <div class="pagination-controls">
                                <button class="pagination-button" type="button" data-grid-prev>Sebelumnya</button>
                                <span class="pagination-page" data-grid-page>Halaman 1</span>
                                <button class="pagination-button" type="button" data-grid-next>Berikutnya</button>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        @if ($currentPage === 'bridge-source-table' && $canViewMasterData && $bridgeSourceTablePage)
            <section class="section">
                <div class="card" style="margin-bottom: 14px;">
                    <div class="card-body">
                        <div class="section-header">
                            <div>
                                <h2>{{ $bridgeSourceTablePage['table'] }}</h2>
                                <p>{{ $bridgeSourceTablePage['description'] }}</p>
                            </div>
                            {!! $tag('Baris', number_format($bridgeSourceTablePage['row_count']), 'table') !!}
                        </div>
                    </div>
                </div>

                <div class="table-card" data-bridge-source-table-app='@json($bridgeSourceTablePage)'>
                    <div class="master-data-toolbar">
                        <div class="master-data-toolbar-main">
                            <label class="search-field" aria-label="Cari baris source table">
                                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                <input type="search" placeholder="Cari nilai pada tabel source ini" data-grid-search>
                            </label>
                        </div>
                        <div class="toolbar-actions">
                            {!! $tag('Data', number_format($bridgeSourceTablePage['row_count']), 'data', 'menu-tag', ' data-grid-count') !!}
                        </div>
                    </div>

                    <div class="table-body">
                        <div class="master-data-table-wrap">
                            <table class="master-data-table compact-data-table">
                                <thead data-grid-head></thead>
                                <tbody data-grid-body>
                                    <tr>
                                        <td colspan="6" class="grid-loading">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pagination-bar">
                        <div class="pagination-meta">
                            <div class="rows-per-page">
                                <span class="rows-label">Baris</span>
                                <label class="rows-select-wrap" aria-label="Jumlah data per halaman">
                                    <select class="rows-select" data-grid-per-page>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                    <svg class="rows-select-icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </label>
                            </div>
                            <div class="helper-text" data-grid-summary>Menyiapkan data...</div>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-button" type="button" data-grid-prev>Sebelumnya</button>
                            <span class="pagination-page" data-grid-page>Halaman 1</span>
                            <button class="pagination-button" type="button" data-grid-next>Berikutnya</button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if ($currentPage === 'tunnel-source-table' && $canViewMasterData && $tunnelSourceTablePage)
            <section class="section">
                <div class="card" style="margin-bottom: 14px;">
                    <div class="card-body">
                        <div class="section-header">
                            <div>
                                <h2>{{ $tunnelSourceTablePage['table'] }}</h2>
                                <p>{{ $tunnelSourceTablePage['description'] }}</p>
                            </div>
                            {!! $tag('Baris', number_format($tunnelSourceTablePage['row_count']), 'table') !!}
                        </div>
                    </div>
                </div>

                <div class="table-card" data-tunnel-source-table-app='@json($tunnelSourceTablePage)'>
                    <div class="master-data-toolbar">
                        <div class="master-data-toolbar-main">
                            <label class="search-field" aria-label="Cari baris tabel terowongan">
                                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                <input type="search" placeholder="Cari nilai pada tabel ini" data-grid-search>
                            </label>
                        </div>
                        <div class="toolbar-actions">
                            {!! $tag('Data', number_format($tunnelSourceTablePage['row_count']), 'data', 'menu-tag', ' data-grid-count') !!}
                            <a class="action-button" href="{{ $tunnelSourceTablePage['template_endpoint'] }}">Template CSV</a>
                            <button class="action-button" type="button" data-tunnel-table-import-trigger>Import CSV</button>
                            <a class="action-button" href="{{ $tunnelSourceTablePage['export_endpoint'] }}">Export CSV</a>
                            <button class="action-button primary" type="button" data-grid-create>Tambah</button>
                            <input type="file" accept=".csv,text/csv" data-tunnel-table-import-file hidden>
                        </div>
                    </div>

                    <div class="table-body">
                        <div class="master-data-table-wrap">
                            <table class="master-data-table compact-data-table">
                                <thead data-grid-head></thead>
                                <tbody data-grid-body>
                                    <tr>
                                        <td colspan="8" class="grid-loading">Memuat data tabel terowongan...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pagination-bar">
                        <div class="pagination-meta">
                            <div class="rows-per-page">
                                <span class="rows-label">Baris</span>
                                <label class="rows-select-wrap" aria-label="Jumlah data per halaman">
                                    <select class="rows-select" data-grid-per-page>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                    <svg class="rows-select-icon" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </label>
                            </div>
                            <div class="helper-text" data-grid-summary>Menyiapkan data...</div>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-button" type="button" data-grid-prev>Sebelumnya</button>
                            <span class="pagination-page" data-grid-page>Halaman 1</span>
                            <button class="pagination-button" type="button" data-grid-next>Berikutnya</button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if ($currentPage === 'overview' && $hasOperationalDashboard)
        <section class="grid workspace section">
            @if ($canViewMonitoring)
                <div class="stack">
                    <div class="card" id="monitoring">
                        <div class="card-body">
                            <div class="section-header">
                                <div>
                                    <h2>Kesehatan Sistem</h2>
                                </div>
                                {!! $tag('Status', $health['status'], $health['status'] === 'ok' ? 'check' : 'clock', 'status '.$health['status']) !!}
                            </div>

                            <div class="health-list">
                                @foreach ($health['checks'] as $check)
                                    <div class="health-item">
                                        <div class="health-main">
                                            <strong>{{ $check['label'] }}</strong>
                                            <span>{{ $check['detail'] }}</span>
                                        </div>
                                        {!! $tag('Status', $check['ok'] ? 'ok' : 'issue', $check['ok'] ? 'check' : 'clock', 'status '.($check['ok'] ? 'ok' : 'missing')) !!}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="section-header">
                                <div>
                                    <h2>Domain Infrastruktur</h2>
                                </div>
                            </div>

                            <div class="health-list">
                                @foreach ($infrastructureDomains as $domain)
                                    <div class="health-item">
                                        <div class="health-main">
                                            <strong>{{ $domain['label'] }}</strong>
                                            <span>{{ $domain['connection'] }} → {{ $domain['database'] ?? 'belum dikonfigurasi' }}</span>
                                            <span>{{ $domain['description'] }}</span>
                                        </div>
                                        {!! $tag('Status', $domain['configured'] ? 'aktif' : 'opsional', $domain['configured'] ? 'check' : 'clock', 'status '.($domain['configured'] ? 'ok' : 'partial')) !!}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="stack">
                @if ($canViewMonitoring)
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
                                            <td>{!! $tag('Status', $import['status'], $import['status'] === 'completed' ? 'check' : 'clock', 'status '.($import['status'] === 'completed' ? 'ready' : 'partial')) !!}</td>
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
                                            <td>{!! $tag('Status', $client['is_active'] ? 'active' : 'inactive', $client['is_active'] ? 'check' : 'clock', 'status '.($client['is_active'] ? 'ready' : 'partial')) !!}</td>
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

                    <div class="card" id="menu-penting">
                        <div class="card-body">
                            <div class="section-header">
                                <div>
                                    <h2>Menu Penting</h2>
                                </div>
                            </div>
                            <div class="menu-list">
                                @foreach ($quickMenu as $item)
                                    <a class="menu-item" href="{{ $item['href'] }}" @if(\Illuminate\Support\Str::startsWith($item['href'], '/api/')) target="_blank" rel="noreferrer" @endif>
                                        <div class="menu-main"><strong>{{ $item['label'] }}</strong></div>
                                        {!! $tag($item['tag'], null, $item['tag'] === 'API' ? 'api' : ($item['tag'] === 'Docs' ? 'book' : ($item['tag'] === 'Spec' ? 'file' : 'tag'))) !!}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
        @endif

        @if ($currentPage === 'superadmin-users' && $isSuperadmin && $superadminUserPage)
            <div class="modal" data-superadmin-user-view-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel">
                    <div class="modal-head">
                        <div>
                            <h2>Detail User</h2>
                            <p data-user-view-subtitle>Memuat data...</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail user">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <div class="modal-body" data-user-view-content></div>
                </div>
            </div>

            <div class="modal" data-superadmin-user-form-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel">
                    <div class="modal-head">
                        <div>
                            <h2 data-user-form-title>Tambah User</h2>
                            <p data-user-form-subtitle>Isi akun baru beserta role aksesnya.</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup form user">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <form data-superadmin-user-form>
                        <div class="modal-body">
                            <div class="feedback" data-user-form-feedback hidden></div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="managed-user-name">Nama</label>
                                    <input id="managed-user-name" name="name" type="text" required>
                                </div>
                                <div class="field">
                                    <label for="managed-user-email">Email</label>
                                    <input id="managed-user-email" name="email" type="email" required>
                                </div>
                                <div class="field">
                                    <label for="managed-user-role">Role</label>
                                    <select id="managed-user-role" name="role" required>
                                        @foreach ($superadminUserPage['role_options'] as $role)
                                            <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="managed-user-password">Password</label>
                                    <div class="password-field">
                                        <input id="managed-user-password" name="password" type="password" placeholder="Minimal 8 karakter">
                                        <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan password" aria-pressed="false">
                                            <svg class="icon password-eye" viewBox="0 0 24 24"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg class="icon password-eye-off" viewBox="0 0 24 24" hidden><path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 4.4A9.8 9.8 0 0 1 12 4c6 0 9.5 6 9.5 6a16.6 16.6 0 0 1-2.1 2.7"/><path d="M6.1 6.1C3.8 7.6 2.5 10 2.5 10s3.5 6 9.5 6c1.1 0 2.2-.2 3.1-.6"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="field full">
                                    <label>Verifikasi Email</label>
                                    <label class="checkbox-field">
                                        <input name="email_verified" type="checkbox" checked>
                                        <span>Tandai email sudah terverifikasi</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="action-button" type="button" data-modal-close>Batal</button>
                            <button class="action-button primary" type="submit" data-user-form-submit>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($currentPage === 'superadmin-api-clients' && $isSuperadmin && $superadminApiClientPage)
            <div class="modal" data-superadmin-api-client-view-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2>Detail Client API</h2>
                            <p data-api-client-view-subtitle>Memuat data...</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail client API">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <div class="modal-body" data-api-client-view-content></div>
                </div>
            </div>

            <div class="modal" data-superadmin-api-client-form-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2 data-api-client-form-title>Tambah Client API</h2>
                            <p data-api-client-form-subtitle>Isi metadata integrasi lalu simpan.</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup form client API">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <form data-superadmin-api-client-form>
                        <div class="modal-body">
                            <div class="feedback" data-api-client-form-feedback hidden></div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="api-client-name">Nama Client</label>
                                    <input id="api-client-name" name="name" type="text" required>
                                </div>
                                <div class="field">
                                    <label for="api-client-code">Code</label>
                                    <input id="api-client-code" name="code" type="text" placeholder="contoh: partner_integrasi" required>
                                </div>
                                <div class="field">
                                    <label for="api-client-owner-name">PIC / Owner</label>
                                    <input id="api-client-owner-name" name="owner_name" type="text">
                                </div>
                                <div class="field">
                                    <label for="api-client-owner-email">Email Owner</label>
                                    <input id="api-client-owner-email" name="owner_email" type="email">
                                </div>
                                <div class="field">
                                    <label for="api-client-rate-minute">Rate Limit / Menit</label>
                                    <input id="api-client-rate-minute" name="rate_limit_per_minute" type="number" min="1">
                                </div>
                                <div class="field">
                                    <label for="api-client-rate-day">Rate Limit / Hari</label>
                                    <input id="api-client-rate-day" name="rate_limit_per_day" type="number" min="1">
                                </div>
                                <div class="field">
                                    <label for="api-client-expires-at">Expired At</label>
                                    <input id="api-client-expires-at" name="expires_at" type="datetime-local">
                                </div>
                                <div class="field">
                                    <label>Status</label>
                                    <label class="checkbox-field">
                                        <input name="is_active" type="checkbox" checked>
                                        <span>Client aktif dan boleh menerima token baru</span>
                                    </label>
                                </div>
                                <div class="field full">
                                    <label for="api-client-description">Deskripsi</label>
                                    <textarea id="api-client-description" name="description" placeholder="Deskripsi singkat integrasi ini"></textarea>
                                </div>
                                <div class="field full">
                                    <label for="api-client-allowed-ips">Allowed IPs</label>
                                    <textarea id="api-client-allowed-ips" name="allowed_ips" placeholder="Satu IP per baris"></textarea>
                                </div>
                                <div class="field full">
                                    <label for="api-client-allowed-origins">Allowed Origins</label>
                                    <textarea id="api-client-allowed-origins" name="allowed_origins" placeholder="Satu origin per baris"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="action-button" type="button" data-modal-close>Batal</button>
                            <button class="action-button primary" type="submit" data-api-client-form-submit>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal" data-superadmin-api-token-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2>Generate Bearer Token</h2>
                            <p data-api-token-subtitle>Pilih scope dan masa berlaku token.</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup generator token">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <form data-superadmin-api-token-form>
                        <div class="modal-body">
                            <div class="feedback" data-api-token-feedback hidden></div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="api-token-name">Nama Token</label>
                                    <input id="api-token-name" name="token_name" type="text" placeholder="contoh: prod-bridge-reader" required>
                                </div>
                                <div class="field">
                                    <label for="api-token-expires-at">Expired At</label>
                                    <input id="api-token-expires-at" name="expires_at" type="datetime-local">
                                </div>
                                <div class="field full">
                                    <label>Ability / Scope</label>
                                    <div class="token-ability-grid">
                                        @foreach ($superadminApiClientPage['ability_options'] as $ability)
                                            <label class="token-ability-card">
                                                <input name="abilities[]" type="checkbox" value="{{ $ability['value'] }}" @checked($ability['value'] === '*')>
                                                <div>
                                                    <strong>{{ $ability['label'] }}</strong>
                                                    <span>{{ $ability['description'] }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="secret-preview" data-api-token-result hidden>
                                <div>
                                    <strong>Bearer Token Baru</strong>
                                    <span class="table-note">Token hanya muncul satu kali. Simpan segera setelah dibuat.</span>
                                </div>
                                <textarea class="secret-code" data-api-token-secret readonly rows="3" spellcheck="false" aria-label="Bearer token baru"></textarea>
                                <div class="toolbar-actions">
                                    <button class="action-button" type="button" data-copy-api-token>Salin Token</button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="action-button" type="button" data-modal-close>Tutup</button>
                            <button class="action-button primary" type="submit" data-api-token-submit>Generate Token</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($currentPage === 'master-data-entity' && $canViewMasterData && $masterDataPage)
            @if (($masterDataPage['mode'] ?? 'master-data') === 'bridge-source')
                <div class="modal" data-bridge-view-modal aria-hidden="true">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-panel modal-panel-xl">
                        <div class="modal-head">
                            <div>
                                <h2>Detail {{ $masterDataPage['label'] }}</h2>
                                <p data-bridge-view-subtitle>Memuat data...</p>
                            </div>
                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                            </button>
                        </div>
                        <div class="modal-body" data-bridge-view-content></div>
                    </div>
                </div>

                <div class="modal" data-bridge-form-modal aria-hidden="true">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-panel">
                        <div class="modal-head">
                            <div>
                                <h2 data-bridge-form-title>Tambah {{ $masterDataPage['label'] }}</h2>
                                <p data-bridge-form-subtitle>Isi data source utama dan relasinya.</p>
                            </div>
                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup form">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                            </button>
                        </div>
                        <form data-bridge-source-form>
                            <div class="modal-body">
                                <div class="feedback" data-bridge-form-feedback hidden></div>
                                <div class="form-section">
                                    <div class="section-header compact">
                                        <div>
                                            <h3>Data Utama `m_jembatan`</h3>
                                        </div>
                                    </div>
                                    <div class="form-grid">
                                        <div class="field">
                                            <label for="bridge-tanggal">Tanggal</label>
                                            <input id="bridge-tanggal" name="tanggal" type="date">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-no-bh">No. Jembatan</label>
                                            <input id="bridge-no-bh" name="no_bh" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-jenis">Jenis</label>
                                            <input id="bridge-jenis" name="jenis" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-km-hm">KM/HM</label>
                                            <input id="bridge-km-hm" name="km_hm" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-arah">Arah</label>
                                            <input id="bridge-arah" name="arah_bh" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-nama">Nama</label>
                                            <input id="bridge-nama" name="nama" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-wil-ker">Wilayah Kerja</label>
                                            <input id="bridge-wil-ker" name="wil_ker" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-wil-op">Wilayah Operasi</label>
                                            <input id="bridge-wil-op" name="wil_op" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-province">Provinsi</label>
                                            <input id="bridge-province" name="id_prov" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-city">Kab/Kota</label>
                                            <input id="bridge-city" name="id_kabkot" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-lintas">Lintas</label>
                                            <input id="bridge-lintas" name="lintas" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-stasiun-1">Stasiun Awal</label>
                                            <input id="bridge-stasiun-1" name="stasiun1" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-stasiun-2">Stasiun Akhir</label>
                                            <input id="bridge-stasiun-2" name="stasiun2" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-lat">Latitude</label>
                                            <input id="bridge-lat" name="lat" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-lon">Longitude</label>
                                            <input id="bridge-lon" name="lon" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-active">Active</label>
                                            <select id="bridge-active" name="active">
                                                <option value="1">1</option>
                                                <option value="0">0</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label for="bridge-status">Status</label>
                                            <input id="bridge-status" name="status" type="number" value="1">
                                        </div>
                                        <div class="field">
                                            <label for="bridge-statusdata">Status Data</label>
                                            <input id="bridge-statusdata" name="statusdata" type="number" value="0">
                                        </div>
                                        <div class="field full">
                                            <label for="bridge-catatan">Catatan</label>
                                            <textarea id="bridge-catatan" name="catatan"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-header compact">
                                        <div>
                                            <h3>Profil `m_jembatan_profil`</h3>
                                        </div>
                                    </div>
                                    <div class="form-grid">
                                        <div class="field">
                                            <label for="profile-perpotongan">Perpotongan</label>
                                            <input id="profile-perpotongan" name="profile.perpotongan" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="profile-track-count">Jumlah Lintasan</label>
                                            <input id="profile-track-count" name="profile.jml_lintasan" type="number">
                                        </div>
                                        <div class="field">
                                            <label for="profile-span-count">Jumlah Bentang</label>
                                            <input id="profile-span-count" name="profile.jml_bentang" type="number">
                                        </div>
                                        <div class="field">
                                            <label for="profile-total-length">Panjang Total</label>
                                            <input id="profile-total-length" name="profile.pjg_total" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="profile-finish-year">Tahun Selesai</label>
                                            <input id="profile-finish-year" name="profile.thn_selesai" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="profile-top-height">RM BGN Atas</label>
                                            <input id="profile-top-height" name="profile.rm_bgn_atas" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="profile-bottom-height">RM BGN Bawah</label>
                                            <input id="profile-bottom-height" name="profile.rm_bgn_bawah" type="text">
                                        </div>
                                        <div class="field full">
                                            <label for="profile-span-json">Detail Bentang Profil</label>
                                            <textarea id="profile-span-json" name="profile_span_json" spellcheck="false">{"pjg_bentang1":"","pjg_bentang2":"","pjg_bentang3":""}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-header compact">
                                        <div>
                                            <h3>Detail 1:N</h3>
                                        </div>
                                    </div>
                                    <div class="field full">
                                        <label for="bridge-spans-json">Bentang `m_jembatan_bentang`</label>
                                        <textarea id="bridge-spans-json" name="spans_json" spellcheck="false">[]</textarea>
                                    </div>
                                    <div class="field full">
                                        <label for="bridge-substructures-json">Struktur Bawah `m_jembatan_bawah`</label>
                                        <textarea id="bridge-substructures-json" name="substructures_json" spellcheck="false">[]</textarea>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-header compact">
                                        <div>
                                            <h3>Proteksi dan Asesmen</h3>
                                        </div>
                                    </div>
                                    <div class="form-grid">
                                        <div class="field">
                                            <label for="protection-flow-material">Pelindung Arus Material</label>
                                            <input id="protection-flow-material" name="protection.pelindung_arus_material" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="protection-flow-type">Pelindung Arus Tipe</label>
                                            <input id="protection-flow-type" name="protection.pelindung_arus_tipe" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="protection-stream-material">Pengarah Arus Material</label>
                                            <input id="protection-stream-material" name="protection.pengarah_arus_material" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="protection-stream-type">Pengarah Arus Tipe</label>
                                            <input id="protection-stream-type" name="protection.pengarah_arus_tipe" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="protection-slide-material">Pelindung Longsoran Material</label>
                                            <input id="protection-slide-material" name="protection.pelindung_longsoran_material" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="protection-slide-type">Pelindung Longsoran Tipe</label>
                                            <input id="protection-slide-type" name="protection.pelindung_longsoran_tipe" type="text">
                                        </div>
                                        <div class="field">
                                            <label for="assessment-total">Nilai Total</label>
                                            <input id="assessment-total" name="assessment.total" type="number" step="0.01">
                                        </div>
                                        <div class="field">
                                            <label for="assessment-conclusion">Kesimpulan</label>
                                            <input id="assessment-conclusion" name="assessment.kesimpulan" type="number">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-actions">
                                <button class="action-button" type="button" data-modal-close>Batal</button>
                                <button class="action-button primary" type="submit" data-bridge-form-submit>Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif (($masterDataPage['mode'] ?? 'master-data') === 'tunnel-source')
                <div class="modal" data-tunnel-view-modal aria-hidden="true">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-panel modal-panel-xl">
                        <div class="modal-head">
                            <div>
                                <h2>Detail {{ $masterDataPage['label'] }}</h2>
                                <p data-tunnel-view-subtitle>Memuat data...</p>
                            </div>
                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                            </button>
                        </div>
                        <div class="modal-body" data-tunnel-view-content></div>
                    </div>
                </div>

                <div class="modal" data-tunnel-form-modal aria-hidden="true">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-panel modal-panel-xl">
                        <div class="modal-head">
                            <div>
                                <h2 data-tunnel-form-title>Tambah {{ $masterDataPage['label'] }}</h2>
                                <p data-tunnel-form-subtitle>Data disimpan ke prasarana_tunnel.</p>
                            </div>
                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup form">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                            </button>
                        </div>
                        <form data-tunnel-source-form>
                            <div class="modal-body">
                                <div class="feedback" data-tunnel-form-feedback hidden></div>
                                <div class="form-stack">
                                    <section class="form-section">
                                        <div class="section-header compact">
                                            <div>
                                                <h3>Identitas</h3>
                                            </div>
	                                        </div>
	                                        <div class="form-grid">
	                                            <div class="field">
	                                                <label for="tunnel-id-display">Tunnel ID</label>
	                                                <input id="tunnel-id-display" name="tunnel_id_display" type="text" disabled data-tunnel-id-display>
	                                            </div>
	                                            <div class="field">
	                                                <label for="tunnel-kode-aset">Kode Aset</label>
	                                                <input id="tunnel-kode-aset" name="kode_aset" type="text">
	                                            </div>
                                            <div class="field">
                                                <label for="tunnel-nomor-bh">No. BH</label>
                                                <input id="tunnel-nomor-bh" name="nomor_bh" type="text">
                                            </div>
                                            <div class="field full">
                                                <label for="tunnel-nama">Nama Terowongan</label>
                                                <input id="tunnel-nama" name="nama_terowongan" type="text" required>
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-wilayah">Wilayah Kerja</label>
                                                <select id="tunnel-wilayah" name="id_wilayah_kerja">
                                                    <option value="">Pilih Wilayah Kerja</option>
                                                    @foreach (($masterDataPage['lookup_options']['id_wilayah_kerja'] ?? []) as $option)
                                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-lintas">Lintas</label>
                                                <select id="tunnel-lintas" name="id_lintas">
                                                    <option value="">Pilih Lintas</option>
                                                    @foreach (($masterDataPage['lookup_options']['id_lintas'] ?? []) as $option)
                                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-kmhm">KM/HM</label>
                                                <input id="tunnel-kmhm" name="km_hm" type="text">
                                            </div>
                                        </div>
                                    </section>

                                    <section class="form-section">
                                        <div class="section-header compact">
                                            <div>
                                                <h3>Lokasi dan Dimensi</h3>
                                            </div>
                                        </div>
                                        <div class="coordinate-input-grid">
	                                            <div class="field">
	                                                <label for="tunnel-lat">Latitude</label>
	                                                <input id="tunnel-lat" name="lat" type="number" min="-90" max="90" step="0.0000001">
	                                                <p class="field-hint">Dipakai sebagai titik pointing pada peta.</p>
	                                            </div>
	                                            <div class="field">
	                                                <label for="tunnel-long">Longitude</label>
	                                                <input id="tunnel-long" name="long" type="number" min="-180" max="180" step="0.0000001">
	                                                <p class="field-hint">Dipakai bersama latitude untuk marker lokasi.</p>
	                                            </div>
	                                            <div class="field coordinate-action-field">
	                                                <label>&nbsp;</label>
	                                                <button class="action-button coordinate-pulse-button" type="button" data-tunnel-coordinate-open>
	                                                    <svg class="icon" viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2"/></svg>
	                                                    <span>Koordinat</span>
	                                                </button>
	                                                <p class="field-hint">&nbsp;</p>
	                                            </div>
	                                        </div>
	                                        <div class="form-grid">
                                            <div class="field">
                                                <label for="tunnel-panjang">Panjang (m)</label>
                                                <input id="tunnel-panjang" name="panjang_m" type="number" min="0" step="0.01">
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-tahun-bangunan">Tahun Bangunan</label>
                                                <input id="tunnel-tahun-bangunan" name="tahun_bangunan" type="number" min="1800">
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-tahun-operasi">Tahun Operasi</label>
                                                <input id="tunnel-tahun-operasi" name="tahun_operasi" type="number" min="1800">
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-umur">Umur</label>
                                                <input id="tunnel-umur" name="umur_tahun" type="number" min="0">
                                            </div>
                                        </div>
                                    </section>

                                    <section class="form-section">
                                        <div class="section-header compact">
                                            <div>
                                                <h3>Status</h3>
                                            </div>
                                        </div>
                                        <div class="form-grid">
                                            <div class="field">
                                                <label for="tunnel-status-operasi">Status Operasi</label>
                                                <input id="tunnel-status-operasi" name="status_operasi" type="text">
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-status-aset">Status Aset</label>
                                                <input id="tunnel-status-aset" name="status_aset" type="text">
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-kondisi">Kondisi Terakhir</label>
                                                <input id="tunnel-kondisi" name="kondisi_terakhir" type="text">
                                            </div>
                                            <div class="field">
                                                <label for="tunnel-inspeksi">Tanggal Inspeksi</label>
                                                <input id="tunnel-inspeksi" name="tgl_inspeksi_terakhir" type="date">
                                            </div>
                                        </div>
                                    </section>

                                    <section class="form-section">
                                        <div class="section-header compact">
                                            <div>
                                                <h3>Detail Tambahan</h3>
                                            </div>
                                        </div>
	                                        <div class="nested-detail-grid">
	                                            <div class="nested-detail-card">
	                                                <div>
	                                                    <h4>Struktur</h4>
	                                                    <p>Isi data pada tabel m_tunnel_structures.</p>
	                                                </div>
	                                                <span class="nested-detail-summary" data-tunnel-nested-summary="structure">Belum diisi</span>
	                                                <button class="action-button" type="button" data-tunnel-nested-open="structure">Isi Struktur</button>
	                                            </div>
	                                            <div class="nested-detail-card">
	                                                <div>
	                                                    <h4>Spesifikasi</h4>
	                                                    <p>Isi data teknis pada tabel m_tunnel_specs.</p>
	                                                </div>
	                                                <span class="nested-detail-summary" data-tunnel-nested-summary="specs">Belum diisi</span>
	                                                <button class="action-button" type="button" data-tunnel-nested-open="specs">Isi Spesifikasi</button>
	                                            </div>
	                                            <div class="nested-detail-card">
	                                                <div>
	                                                    <h4>Dokumen</h4>
	                                                    <p>Isi nomor dan referensi dokumen pada tabel m_tunnel_docs.</p>
	                                                </div>
	                                                <span class="nested-detail-summary" data-tunnel-nested-summary="docs">Belum diisi</span>
	                                                <button class="action-button" type="button" data-tunnel-nested-open="docs">Isi Dokumen</button>
	                                            </div>
	                                        </div>
	                                    </section>
                                </div>
                            </div>
                            <div class="modal-actions">
                                <button class="action-button" type="button" data-modal-close>Batal</button>
                                <button class="action-button primary" type="submit" data-tunnel-form-submit>Simpan</button>
                            </div>
                        </form>
	                    </div>
	                </div>

	                <div class="modal" data-tunnel-nested-modal aria-hidden="true">
	                    <div class="modal-backdrop" data-modal-close></div>
	                    <div class="modal-panel modal-panel-xl">
	                        <div class="modal-head">
	                            <div>
	                                <h2 data-tunnel-nested-title>Detail Terowongan</h2>
	                                <p data-tunnel-nested-subtitle>Isi data tabel terkait tanpa JSON manual.</p>
	                            </div>
	                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail terkait">
	                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
	                            </button>
	                        </div>
	                        <form data-tunnel-nested-form>
	                            <div class="modal-body">
	                                <div class="form-grid" data-tunnel-nested-fields></div>
	                            </div>
	                            <div class="modal-actions">
	                                <button class="action-button" type="button" data-modal-close>Batal</button>
	                                <button class="action-button primary" type="submit">Simpan Detail</button>
	                            </div>
	                        </form>
	                    </div>
	                </div>

	                <div class="modal" data-tunnel-coordinate-modal aria-hidden="true">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-panel modal-panel-xl">
                        <div class="modal-head">
                            <div>
                                <h2>Koordinat</h2>
                                <p>Pilih posisi dari center peta.</p>
                            </div>
                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup peta koordinat">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="coordinate-picker">
                                <form class="coordinate-search" data-coordinate-search-form>
                                    <label class="search-field" aria-label="Cari alamat">
                                        <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                        <input type="search" placeholder="Cari alamat atau lokasi" data-coordinate-search-input>
                                    </label>
                                    <button class="action-button" type="submit">Cari</button>
                                </form>
                                <div class="feedback" data-coordinate-feedback hidden></div>
                                <div class="coordinate-map-shell">
                                    <div class="coordinate-map" data-coordinate-map></div>
                                    <div class="coordinate-spotlight" aria-hidden="true">
                                        <span></span>
                                    </div>
                                    <div class="coordinate-live">
                                        <span>Lat</span>
                                        <strong data-coordinate-live-lat>-</strong>
                                        <span>Lon</span>
                                        <strong data-coordinate-live-lon>-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="action-button" type="button" data-modal-close>Batal</button>
                            <button class="action-button primary" type="button" data-coordinate-apply>Terapkan</button>
                        </div>
                    </div>
                </div>
            @else
                <div class="modal" data-view-modal aria-hidden="true">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-panel">
                        <div class="modal-head">
                            <div>
                                <h2>Detail {{ $masterDataPage['label'] }}</h2>
                                <p data-view-subtitle>Memuat data...</p>
                            </div>
                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                            </button>
                        </div>
                        <div class="modal-body" data-view-content></div>
                    </div>
                </div>

                <div class="modal" data-form-modal aria-hidden="true">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-panel">
                        <div class="modal-head">
                            <div>
                                <h2 data-form-title>Tambah {{ $masterDataPage['label'] }}</h2>
                                <p data-form-subtitle>Isi data inti lalu simpan.</p>
                            </div>
                            <button class="icon-button" type="button" data-modal-close aria-label="Tutup form">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                            </button>
                        </div>
                        <form data-master-data-form>
                            <div class="modal-body">
                                <div class="feedback" data-form-feedback hidden></div>
                                <div class="form-grid">
                                    <div class="field">
                                        <label for="record-code">Kode</label>
                                        <input id="record-code" name="code" type="text" required>
                                    </div>
                                    <div class="field">
                                        <label for="record-name">Nama</label>
                                        <input id="record-name" name="name" type="text">
                                    </div>
                                    <div class="field">
                                        <label for="record-status">Status</label>
                                        <select id="record-status" name="status" required>
                                            @foreach ($masterDataPage['status_options'] as $status)
                                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label for="record-parent-code">Parent Code</label>
                                        <input id="record-parent-code" name="parent_code" type="text">
                                    </div>
                                    <div class="field">
                                        <label for="record-source-system">Source System</label>
                                        <input id="record-source-system" name="source_system" type="text">
                                    </div>
                                    <div class="field">
                                        <label for="record-source-table">Source Table</label>
                                        <input id="record-source-table" name="source_table" type="text">
                                    </div>
                                    <div class="field full">
                                        <label for="record-source-id">Source ID</label>
                                        <input id="record-source-id" name="source_id" type="text">
                                    </div>
                                    <div class="field full">
                                        <label for="record-description">Deskripsi</label>
                                        <textarea id="record-description" name="description"></textarea>
                                    </div>
                                    <div class="field full">
                                        <label for="record-data-json">Data JSON</label>
                                        <textarea id="record-data-json" name="data_json" spellcheck="false">{}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-actions">
                                <button class="action-button" type="button" data-modal-close>Batal</button>
                                <button class="action-button primary" type="submit" data-form-submit>Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        @if ($currentPage === 'documentation' && $bridgeModule)
            <div class="modal" data-bridge-field-values-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2 data-bridge-field-values-title>Unique Value Field</h2>
                            <p data-bridge-field-values-subtitle>Pilih field metadata jembatan untuk melihat daftar value unik.</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup unique value">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <div class="modal-body" data-bridge-field-values-content>
                        <div class="grid-loading">Pilih field untuk melihat unique value.</div>
                    </div>
                </div>
            </div>
        @endif

        @if ($currentPage === 'bridge-source-table' && $canViewMasterData && $bridgeSourceTablePage)
            <div class="modal" data-bridge-source-table-view-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2>Detail {{ $bridgeSourceTablePage['table'] }}</h2>
                            <p data-bridge-source-table-view-subtitle>Memuat data...</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <div class="modal-body" data-bridge-source-table-view-content></div>
                </div>
            </div>
        @endif

        @if ($currentPage === 'tunnel-source-table' && $canViewMasterData && $tunnelSourceTablePage)
            <div class="modal" data-tunnel-source-table-view-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2>Detail {{ $tunnelSourceTablePage['table'] }}</h2>
                            <p data-tunnel-source-table-view-subtitle>Memuat data...</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup detail">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <div class="modal-body" data-tunnel-source-table-view-content></div>
                </div>
            </div>

            <div class="modal" data-tunnel-source-table-form-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2 data-tunnel-source-table-form-title>Tambah {{ $tunnelSourceTablePage['table'] }}</h2>
                            <p data-tunnel-source-table-form-subtitle>Input row baru langsung ke database prasarana_tunnel.</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup form">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <form data-tunnel-source-table-form>
                        <div class="modal-body">
                            <div class="feedback" data-tunnel-source-table-form-feedback hidden></div>
                            <div class="form-stack" data-tunnel-source-table-form-fields></div>
                        </div>
                        <div class="modal-actions">
                            <button class="action-button" type="button" data-modal-close>Batal</button>
                            <button class="action-button primary" type="submit" data-tunnel-source-table-form-submit>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal" data-tunnel-source-table-coordinate-modal aria-hidden="true">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-xl">
                    <div class="modal-head">
                        <div>
                            <h2>Pilih Koordinat</h2>
                            <p>Geser peta sampai titik berada di lokasi terowongan.</p>
                        </div>
                        <button class="icon-button" type="button" data-modal-close aria-label="Tutup selector koordinat">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="coordinate-picker">
                            <form class="coordinate-search" data-tunnel-source-table-coordinate-search-form>
                                <label class="search-field">
                                    <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    <input type="search" placeholder="Cari alamat atau lokasi" data-tunnel-source-table-coordinate-search-input>
                                </label>
                                <button class="action-button" type="submit">Cari</button>
                            </form>
                            <div class="feedback" data-tunnel-source-table-coordinate-feedback hidden></div>
                            <div class="coordinate-map-shell">
                                <div class="coordinate-map" data-tunnel-source-table-coordinate-map></div>
                                <div class="coordinate-spotlight" aria-hidden="true">
                                    <span></span>
                                </div>
                                <div class="coordinate-live">
                                    <span>Lat</span>
                                    <strong data-tunnel-source-table-coordinate-live-lat>-</strong>
                                    <span>Lon</span>
                                    <strong data-tunnel-source-table-coordinate-live-lon>-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="action-button" type="button" data-modal-close>Batal</button>
                        <button class="action-button primary" type="button" data-tunnel-source-table-coordinate-apply>Terapkan</button>
                    </div>
                </div>
            </div>
        @endif
        <div class="modal" data-document-preview-modal aria-hidden="true">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-panel modal-panel-xl">
                <div class="modal-head">
                    <div>
                        <h2 data-document-preview-title>Preview Dokumen</h2>
                        <p data-document-preview-subtitle>Dokumen terunggah</p>
                    </div>
                    <button class="icon-button" type="button" data-modal-close aria-label="Tutup preview dokumen">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M6 6 18 18"/><path d="M18 6 6 18"/></svg>
                    </button>
                </div>
                <div class="modal-body" data-document-preview-content></div>
            </div>
        </div>
    </main>
</div>
<script src="{{ asset('vendor-cytoscape.min.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    window.dashboardDetailMap = (() => {
        const mapStore = new WeakMap();
        const observedRoots = new WeakSet();
        const satelliteTileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
        const satelliteAttribution = 'Tiles &copy; Esri';

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const numericValue = (value) => {
            if (value === null || value === undefined || value === '') {
                return null;
            }

            const normalized = String(value).trim().replace(',', '.');
            const match = normalized.match(/-?\d+(?:\.\d+)?/);

            if (!match) {
                return null;
            }

            const number = Number(match[0]);

            return Number.isFinite(number) ? number : null;
        };

        const pairValue = (value) => {
            if (value === null || value === undefined || value === '') {
                return null;
            }

            const matches = String(value)
                .replaceAll(',', '.')
                .match(/-?\d+(?:\.\d+)?/g);

            if (!matches || matches.length < 2) {
                return null;
            }

            const lat = Number(matches[0]);
            const lon = Number(matches[1]);

            return isValidCoordinate(lat, lon) ? { lat, lon } : null;
        };

        const isValidCoordinate = (lat, lon) => (
            Number.isFinite(lat)
            && Number.isFinite(lon)
            && lat >= -90
            && lat <= 90
            && lon >= -180
            && lon <= 180
        );

        const coordinateRole = (key, label) => {
            const normalized = `${key || ''} ${label || ''}`
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_');

            if (/(^|_)(lat|latitude|lintang)(_|$)/.test(normalized)) {
                return 'lat';
            }

            if (/(^|_)(lon|lng|long|longitude|bujur)(_|$)/.test(normalized)) {
                return 'lon';
            }

            if (/(^|_)(koordinat|coordinate|coordinates)(_|$)/.test(normalized)) {
                return 'pair';
            }

            return null;
        };

        const coordinatesFromEntries = (entries) => {
            let lat = null;
            let lon = null;

            for (const [label, value, key] of entries || []) {
                const role = coordinateRole(key, label);

                if (role === 'pair') {
                    const pair = pairValue(value);

                    if (pair) {
                        return pair;
                    }
                }

                if (role === 'lat') {
                    const pair = pairValue(value);

                    if (pair) {
                        return pair;
                    }

                    lat = numericValue(value);
                }

                if (role === 'lon') {
                    lon = numericValue(value);
                }
            }

            return isValidCoordinate(lat, lon) ? { lat, lon } : null;
        };

        const coordinatesFromRows = (rows) => {
            for (const row of rows || []) {
                const entries = Object.entries(row || {}).map(([key, value]) => [key, value, key]);
                const coordinates = coordinatesFromEntries(entries);

                if (coordinates) {
                    return coordinates;
                }
            }

            return null;
        };

        const renderMapCard = ({ lat, lon }) => `
            <aside class="detail-map-card" data-detail-map data-lat="${escapeHtml(lat)}" data-lon="${escapeHtml(lon)}">
                <div class="detail-map-canvas" data-detail-map-canvas></div>
                <div class="detail-map-meta">
                    <span>Lokasi</span>
                    <strong>${escapeHtml(lat.toFixed(6))}, ${escapeHtml(lon.toFixed(6))}</strong>
                </div>
            </aside>
        `;

        const wrapTable = (coordinates, tableHtml) => {
            if (!coordinates) {
                return tableHtml;
            }

            return `
                <div class="detail-map-layout">
                    ${renderMapCard(coordinates)}
                    <div class="detail-map-table">${tableHtml}</div>
                </div>
            `;
        };

        const wrapEntries = (entries, tableHtml) => wrapTable(coordinatesFromEntries(entries), tableHtml);
        const wrapRows = (rows, tableHtml) => wrapTable(coordinatesFromRows(rows), tableHtml);

        const hasMapNode = (node) => {
            if (!(node instanceof Element)) {
                return false;
            }

            return node.matches('[data-detail-map]') || Boolean(node.querySelector('[data-detail-map]'));
        };

        const queueInit = (root) => {
            window.requestAnimationFrame(() => {
                init(root);
            });
        };

        const observeMapInsertions = (root) => {
            if (!window.MutationObserver || observedRoots.has(root)) {
                return;
            }

            observedRoots.add(root);

            new MutationObserver((mutations) => {
                const shouldInit = mutations.some((mutation) => (
                    Array.from(mutation.addedNodes).some(hasMapNode)
                ));

                if (shouldInit) {
                    queueInit(root);
                }
            }).observe(root, {
                childList: true,
                subtree: true,
            });
        };

        const init = (root = document) => {
            if (!window.L) {
                return;
            }

            observeMapInsertions(root);

            root.querySelectorAll('[data-detail-map]').forEach((mapCard) => {
                if (mapStore.has(mapCard)) {
                    return;
                }

                const canvas = mapCard.querySelector('[data-detail-map-canvas]');
                const lat = Number(mapCard.dataset.lat);
                const lon = Number(mapCard.dataset.lon);

                if (!canvas || !isValidCoordinate(lat, lon)) {
                    return;
                }

                const map = window.L.map(canvas, {
                    zoomControl: false,
                    attributionControl: true,
                    scrollWheelZoom: false,
                    dragging: true,
                    doubleClickZoom: false,
                    boxZoom: false,
                    keyboard: false,
                    tap: false,
                }).setView([lat, lon], 16);

                window.L.tileLayer(satelliteTileUrl, {
                    attribution: satelliteAttribution,
                    maxZoom: 19,
                    maxNativeZoom: 19,
                }).addTo(map);

                const markerIcon = window.L.divIcon({
                    className: 'detail-map-leaflet-marker',
                    html: '<span class="detail-map-marker"><span class="marker-wave"></span><span class="marker-wave"></span><span class="marker-wave"></span><span class="marker-dot"></span></span>',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                });

                window.L.marker([lat, lon], { icon: markerIcon, interactive: false }).addTo(map);
                mapStore.set(mapCard, map);

                requestAnimationFrame(() => {
                    map.invalidateSize();
                });

                window.setTimeout(() => {
                    map.invalidateSize();
                }, 180);
            });
        };

        return {
            wrapEntries,
            wrapRows,
            init,
        };
    })();

    window.dashboardDocumentPreview = (() => {
        const modal = document.querySelector('[data-document-preview-modal]');
        const titleNode = modal?.querySelector('[data-document-preview-title]');
        const subtitleNode = modal?.querySelector('[data-document-preview-subtitle]');
        const contentNode = modal?.querySelector('[data-document-preview-content]');
        const body = document.body;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const decodeAttribute = (value) => {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = value || '';
            return textarea.value;
        };

        const normalize = (value) => {
            if (value === null || value === undefined || value === '') {
                return null;
            }

            if (typeof value === 'string') {
                const trimmed = value.trim();

                if (trimmed.startsWith('{')) {
                    try {
                        return normalize(JSON.parse(trimmed));
                    } catch {
                        return null;
                    }
                }

                if (/^(https?:\/\/|\/storage\/|storage\/|tunnels\/docs\/).+\.(pdf|jpe?g|png|webp)(\?.*)?$/i.test(trimmed)) {
                    return {
                        path: trimmed,
                        file_name: trimmed.split('/').pop(),
                        mime_type: '',
                    };
                }

                return null;
            }

            if (Array.isArray(value)) {
                return value.map(normalize).find(Boolean) || null;
            }

            if (typeof value !== 'object') {
                return null;
            }

            const fallbackValue = typeof value.value === 'string' && /\.(pdf|jpe?g|png|webp)(\?.*)?$/i.test(value.value)
                ? value.value
                : null;
            const url = value.path || value.url || fallbackValue;

            if (!url) {
                return null;
            }

            return {
                url: String(url),
                path: value.path ? String(value.path) : null,
                file_name: String(value.file_name || value.name || String(url).split('/').pop() || 'Dokumen'),
                mime_type: value.mime_type ? String(value.mime_type) : '',
            };
        };

        const encodePath = (path) => String(path)
            .split('/')
            .filter((segment) => segment !== '')
            .map((segment) => encodeURIComponent(segment))
            .join('/');

        const storagePathFromRaw = (raw) => {
            const trimmed = String(raw || '').trim();

            if (!trimmed) {
                return null;
            }

            if (/^https?:\/\//i.test(trimmed)) {
                try {
                    const parsed = new URL(trimmed);

                    if (parsed.pathname.startsWith('/storage/tunnels/docs/')) {
                        return parsed.pathname.replace(/^\/storage\//, '');
                    }
                } catch {
                    return null;
                }

                return null;
            }

            let normalized = trimmed.replace(/^\/+/, '');

            if (normalized.startsWith('storage/')) {
                normalized = normalized.slice('storage/'.length);
            }

            return normalized.startsWith('tunnels/docs/') ? normalized : null;
        };

        const previewUrl = (documentFile) => {
            const raw = documentFile.path || documentFile.url || '';
            const storagePath = storagePathFromRaw(raw);

            if (storagePath) {
                return `/dashboard/master-data/terowongan/documents/${encodePath(storagePath)}`;
            }

            if (/^https?:\/\//i.test(raw) || raw.startsWith('/')) {
                return raw;
            }

            if (raw.startsWith('storage/')) {
                return `/${raw}`;
            }

            return `/storage/${raw}`;
        };

        const isImage = (document, url) => /^image\//i.test(document.mime_type || '') || /\.(jpe?g|png|webp)(\?.*)?$/i.test(url);

        const render = (value, fallbackLabel = 'Dokumen') => {
            const document = normalize(value);

            if (!document) {
                return escapeHtml(fallbackLabel === 'Dokumen' ? '-' : fallbackLabel);
            }

            const url = previewUrl(document);
            const label = document.file_name || fallbackLabel || 'Dokumen';

            return `
                <button class="document-preview-button" type="button" data-document-preview data-document-url="${escapeHtml(url)}" data-document-title="${escapeHtml(label)}" data-document-mime="${escapeHtml(document.mime_type || '')}">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v6h6"/><path d="M9 14h6"/><path d="M9 18h4"/></svg>
                    <span class="document-preview-label">${escapeHtml(label)}</span>
                </button>
            `;
        };

        const open = (url, title, mimeType = '') => {
            if (!modal || !contentNode) {
                return;
            }

            const label = title || 'Dokumen';

            if (titleNode) {
                titleNode.textContent = label;
            }

            if (subtitleNode) {
                subtitleNode.textContent = 'Preview dokumen terunggah';
            }

            contentNode.innerHTML = isImage({ mime_type: mimeType }, url)
                ? `<img class="document-preview-image" src="${escapeHtml(url)}" alt="${escapeHtml(label)}">`
                : `<iframe class="document-preview-frame" src="${escapeHtml(url)}" title="${escapeHtml(label)}"></iframe>`;

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');
        };

        const close = () => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (contentNode) {
                contentNode.innerHTML = '';
            }

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        modal?.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', close);
        });

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-document-preview]');

            if (!trigger) {
                return;
            }

            open(
                decodeAttribute(trigger.dataset.documentUrl || ''),
                decodeAttribute(trigger.dataset.documentTitle || 'Dokumen'),
                decodeAttribute(trigger.dataset.documentMime || ''),
            );
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                close();
            }
        });

        return { render, normalize, open };
    })();

    window.dashboardModalA11y = {
        prepareClose(modal) {
            const activeElement = document.activeElement;

            if (activeElement instanceof HTMLElement && modal?.contains(activeElement)) {
                activeElement.blur();
            }
        },
    };

    (() => {
        const body = document.body;
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebarToggleLabel = sidebarToggle?.querySelector('.nav-copy strong');
        const userMenu = document.querySelector('.user-menu');
        const desktopBreakpoint = window.matchMedia('(min-width: 1181px)');
        const storageKey = 'dashboard-sidebar-collapsed';
        const masterNavStorageKey = 'dashboard-master-nav-expanded';
        const masterNavGroups = Array.from(document.querySelectorAll('[data-master-nav-group]'));

        const readMasterNavState = () => {
            try {
                const parsed = JSON.parse(window.localStorage.getItem(masterNavStorageKey) || '{}');

                return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
            } catch {
                return {};
            }
        };

        const writeMasterNavState = (state) => {
            window.localStorage.setItem(masterNavStorageKey, JSON.stringify(state));
        };

        const syncMasterNavGroup = (group, expanded) => {
            group.classList.toggle('is-expanded', expanded);
            group.querySelector('[data-master-nav-toggle]')?.setAttribute('aria-expanded', String(expanded));
        };

        if (masterNavGroups.length) {
            const masterNavState = readMasterNavState();

            masterNavGroups.forEach((group) => {
                const key = group.dataset.masterNavKey || '';
                const toggle = group.querySelector('[data-master-nav-toggle]');
                const hasSavedState = Object.prototype.hasOwnProperty.call(masterNavState, key);
                const expanded = hasSavedState ? masterNavState[key] === true : group.classList.contains('is-expanded');

                syncMasterNavGroup(group, expanded);

                toggle?.addEventListener('click', () => {
                    const nextExpanded = !group.classList.contains('is-expanded');
                    masterNavState[key] = nextExpanded;
                    writeMasterNavState(masterNavState);
                    syncMasterNavGroup(group, nextExpanded);
                });
            });
        }

        const syncSidebarState = (collapsed) => {
            body.classList.toggle('sidebar-collapsed', collapsed && desktopBreakpoint.matches);

            if (sidebarToggle) {
                sidebarToggle.setAttribute('aria-expanded', String(!collapsed || !desktopBreakpoint.matches));
            }

            if (sidebarToggleLabel) {
                sidebarToggleLabel.textContent = collapsed && desktopBreakpoint.matches ? 'Expand Sidebar' : 'Collapse Sidebar';
            }
        };

        if (sidebarToggle) {
            const savedState = window.localStorage.getItem(storageKey) === 'true';
            syncSidebarState(savedState);

            sidebarToggle.addEventListener('click', () => {
                const collapsed = !body.classList.contains('sidebar-collapsed');
                window.localStorage.setItem(storageKey, String(collapsed));
                syncSidebarState(collapsed);
            });

            desktopBreakpoint.addEventListener('change', () => {
                const collapsed = window.localStorage.getItem(storageKey) === 'true';
                syncSidebarState(collapsed);
            });
        }

        document.addEventListener('click', (event) => {
            if (userMenu && userMenu.open && !userMenu.contains(event.target)) {
                userMenu.removeAttribute('open');
            }
        });

        const bridgeFieldValuesEndpoint = @json($bridgeFieldValuesEndpoint);
        const bridgeFieldValuesModal = document.querySelector('[data-bridge-field-values-modal]');
        const bridgeFieldValuesTitle = bridgeFieldValuesModal?.querySelector('[data-bridge-field-values-title]');
        const bridgeFieldValuesSubtitle = bridgeFieldValuesModal?.querySelector('[data-bridge-field-values-subtitle]');
        const bridgeFieldValuesContent = bridgeFieldValuesModal?.querySelector('[data-bridge-field-values-content]');
        const openBridgeFieldValuesButtons = document.querySelectorAll('[data-bridge-field-values-open]');

        const openStaticModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeStaticModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const escapeStaticHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const staticTagIcon = (icon) => ({
            check: '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
            clock: '<svg class="tag-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
            data: '<svg class="tag-icon" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>',
            field: '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h10"/></svg>',
            location: '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2"/></svg>',
            table: '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 10h16"/><path d="M9 5v14"/></svg>',
            tag: '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 10 14 4H5v9l6 6z"/><path d="M8 8h.01"/></svg>',
        }[icon] || '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 10 14 4H5v9l6 6z"/><path d="M8 8h.01"/></svg>');

        const staticTag = (label, value = null, icon = 'tag', className = 'menu-tag') => `
            <span class="${escapeStaticHtml(className)}">
                ${staticTagIcon(icon)}
                <span class="tag-label">${escapeStaticHtml(label)}</span>
                ${value === null ? '' : `<span class="tag-value">${escapeStaticHtml(String(value))}</span>`}
            </span>
        `;

        const fillStaticTag = (element, label, value, icon = 'tag') => {
            if (!element) {
                return;
            }

            element.innerHTML = `
                ${staticTagIcon(icon)}
                <span class="tag-label">${escapeStaticHtml(label)}</span>
                <span class="tag-value">${escapeStaticHtml(String(value))}</span>
            `;
        };

        window.dashboardFillStaticTag = fillStaticTag;

        const renderBridgeFieldValues = (payload) => {
            if (!bridgeFieldValuesContent) {
                return;
            }

            const values = Array.isArray(payload?.values) ? payload.values : [];

            if (!values.length) {
                bridgeFieldValuesContent.innerHTML = '<p class="metadata-pending">Belum ada value untuk field ini.</p>';
                return;
            }

            bridgeFieldValuesContent.innerHTML = `
                <div class="metadata-values-summary">
                    ${staticTag('Unique', String(payload.unique_count || values.length), 'field')}
                    <span class="helper-text">${escapeStaticHtml(String(payload.record_count || 0))} record dicek</span>
                </div>
                <div class="metadata-values-scroll">
                    <table class="metadata-table">
                        <thead>
                            <tr>
                                <th>Value Unik</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${values.map((item) => `
                                <tr>
                                    <td class="metadata-value">
                                        ${item.is_structured
                                            ? `<pre class="metadata-value-json">${escapeStaticHtml(item.value || '{}')}</pre>`
                                            : escapeStaticHtml(item.value || '(kosong)')}
                                    </td>
                                    <td class="mono">${escapeStaticHtml(String(item.count || 0))}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        };

        const openBridgeFieldValues = async (button) => {
            const fieldKey = button.dataset.fieldKey || '';
            const fieldLabel = button.dataset.fieldLabel || fieldKey || 'Field';

            if (bridgeFieldValuesTitle) {
                bridgeFieldValuesTitle.textContent = `Unique Value: ${fieldLabel}`;
            }

            if (bridgeFieldValuesSubtitle) {
                bridgeFieldValuesSubtitle.textContent = 'Mengambil semua value unik berdasarkan data jembatan yang tersedia sekarang...';
            }

            if (bridgeFieldValuesContent) {
                bridgeFieldValuesContent.innerHTML = '<div class="grid-loading">Memuat unique value...</div>';
            }

            openStaticModal(bridgeFieldValuesModal);

            try {
                const response = await fetch(bridgeFieldValuesEndpoint.replace('__field__', encodeURIComponent(fieldKey)), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const payload = await response.json().catch(() => null);

                if (!response.ok || payload?.success === false) {
                    throw payload || { message: 'Gagal mengambil unique value.' };
                }

                if (bridgeFieldValuesSubtitle) {
                    bridgeFieldValuesSubtitle.textContent = payload?.data?.field?.api_path
                        ? `${payload.data.field.api_path} · ${payload.data.unique_count || 0} unique value`
                        : `${payload?.data?.unique_count || 0} unique value`;
                }

                renderBridgeFieldValues(payload?.data || {});
            } catch (errorPayload) {
                const message = errorPayload?.message || 'Gagal mengambil unique value.';

                if (bridgeFieldValuesContent) {
                    bridgeFieldValuesContent.innerHTML = `<div class="feedback">${escapeStaticHtml(message)}</div>`;
                }
            }
        };

        openBridgeFieldValuesButtons.forEach((button) => {
            button.addEventListener('click', () => openBridgeFieldValues(button));
        });

        bridgeFieldValuesModal?.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeStaticModal(bridgeFieldValuesModal));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeStaticModal(bridgeFieldValuesModal);
            }
        });

        const masterDataRoot = document.querySelector('[data-master-data-app]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!masterDataRoot) {
            return;
        }

        const config = JSON.parse(masterDataRoot.dataset.masterDataApp || '{}');
        const columnLabels = {
            code: 'Kode',
            name: 'Nama',
            status: 'Status',
            parent_code: 'Parent',
            source_system: 'Source System',
            source_table: 'Source Table',
            source_id: 'Source ID',
            description: 'Deskripsi',
            created_at: 'Dibuat',
            updated_at: 'Diperbarui',
            synced_at: 'Sinkron',
            'data.bridge_number': 'No. Jembatan',
            'data.tunnel_number': 'No. Terowongan',
            'data.tunnel_kind': 'Jenis Terowongan',
            'data.lintas_code': 'Lintas',
            'data.km_hm': 'KM/HM',
        };
        const columns = Array.isArray(config.visible_fields) && config.visible_fields.length > 0
            ? config.visible_fields
            : ['code', 'name', 'status', 'updated_at'];
        const gridHead = masterDataRoot.querySelector('[data-grid-head]');
        const gridBody = masterDataRoot.querySelector('[data-grid-body]');
        const gridSearch = masterDataRoot.querySelector('[data-grid-search]');
        const gridPerPage = masterDataRoot.querySelector('[data-grid-per-page]');
        const gridCount = masterDataRoot.querySelector('[data-grid-count]');
        const gridSummary = masterDataRoot.querySelector('[data-grid-summary]');
        const gridPage = masterDataRoot.querySelector('[data-grid-page]');
        const prevButton = masterDataRoot.querySelector('[data-grid-prev]');
        const nextButton = masterDataRoot.querySelector('[data-grid-next]');
        const createButton = masterDataRoot.querySelector('[data-grid-create]');
        const viewModal = document.querySelector('[data-view-modal]');
        const viewSubtitle = viewModal?.querySelector('[data-view-subtitle]');
        const viewContent = viewModal?.querySelector('[data-view-content]');
        const formModal = document.querySelector('[data-form-modal]');
        const formTitle = formModal?.querySelector('[data-form-title]');
        const formSubtitle = formModal?.querySelector('[data-form-subtitle]');
        const form = formModal?.querySelector('[data-master-data-form]');
        const formFeedback = formModal?.querySelector('[data-form-feedback]');
        const submitButton = formModal?.querySelector('[data-form-submit]');

        const state = {
            page: 1,
            perPage: Number(gridPerPage?.value || 10),
            search: '',
            pagination: {
                current_page: 1,
                last_page: 1,
                total: Number(config.records_count || 0),
                per_page: Number(gridPerPage?.value || 10),
            },
            loading: false,
            mode: 'create',
            activeUuid: null,
        };

        let searchTimer = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const getValue = (object, path) => path.split('.').reduce((carry, segment) => {
            if (carry === null || carry === undefined) {
                return null;
            }

            return carry[segment] ?? null;
        }, object);

        const prettifyField = (field) => columnLabels[field]
            || field.replace('data.', '').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

        const formatText = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (typeof value === 'object') {
                return JSON.stringify(value);
            }

            return String(value);
        };

        const formatDate = (value) => {
            if (!value) {
                return '-';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return String(value);
            }

            return new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short',
                timeZone: 'UTC',
            }).format(date);
        };

        const formatAssessmentConclusion = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const normalized = Number.parseInt(String(value), 10);
            const labels = {
                1: 'Baik',
                2: 'Sedang',
                3: 'Rusak Ringan',
                4: 'Rusak Berat',
            };

            if (Number.isNaN(normalized)) {
                return String(value);
            }

            return labels[normalized] ? `${labels[normalized]} (${normalized})` : String(normalized);
        };

        const iconMarkup = (name) => {
            const icons = {
                bridge: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18h18"/><path d="M5 18v-4a7 7 0 0 1 14 0v4"/><path d="M8 18v-4"/><path d="M12 18v-6"/><path d="M16 18v-4"/><path d="M3 10h18"/></svg>',
                map: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 6 6-2 6 2 6-2v14l-6 2-6-2-6 2z"/><path d="M9 4v14"/><path d="M15 6v14"/></svg>',
                route: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="18" r="2"/><circle cx="18" cy="6" r="2"/><path d="M8 18h4a6 6 0 0 0 6-6V8"/></svg>',
                profile: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 18h16"/><path d="M6 18V8l4-2 4 2 4-2v12"/><path d="M10 6v12"/><path d="M14 8v10"/></svg>',
                span: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18h18"/><path d="M5 18c1.5-5 4-8 7-8s5.5 3 7 8"/><path d="M9 12h6"/></svg>',
                substructure: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16"/><path d="M6 20v-6h4v6"/><path d="M14 20v-10h4v10"/><path d="M4 10h16"/></svg>',
                shield: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 5 6v6c0 4.5 2.8 7.7 7 9 4.2-1.3 7-4.5 7-9V6z"/><path d="M9.5 12.5 11 14l3.5-3.5"/></svg>',
                assessment: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-4"/></svg>',
                media: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 15-4.5-4.5L7 19"/></svg>',
                database: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v14c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3"/></svg>',
            };

            return icons[name] || icons.database;
        };

        const formatLookup = (label, code) => {
            if (label && code && String(label) !== String(code)) {
                return `${label} (${code})`;
            }

            return label || code || '-';
        };

        const compactNumber = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const number = Number(value);

            if (Number.isNaN(number)) {
                return String(value);
            }

            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 2,
            }).format(number);
        };

        const formatDetailValue = (value, key = '') => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (Array.isArray(value)) {
                return value.length ? value.map((item) => formatDetailValue(item, key)).join(', ') : '-';
            }

            if (typeof value === 'object') {
                return Object.keys(value).length ? JSON.stringify(value) : '-';
            }

            if (key.endsWith('_at') || key === 'tanggal') {
                return formatDate(value);
            }

            if (key === 'kesimpulan') {
                return formatAssessmentConclusion(value);
            }

            return String(value);
        };

        const hiddenBridgeTableFields = new Set(['created_at', 'updated_at', 'created_by']);
        const isHiddenBridgeTableField = (key = '') => hiddenBridgeTableFields.has(String(key || '').toLowerCase());
        const buildRows = (entries) => entries.filter(([, value, key]) => value !== undefined && !isHiddenBridgeTableField(key));

        const renderKeyValueTable = (entries) => {
            const rows = buildRows(entries);

            if (!rows.length) {
                return '<div class="detail-empty">Belum ada data yang tersimpan pada bagian ini.</div>';
            }

            const tableHtml = `
                <div class="detail-table-wrap">
                    <table class="detail-kv-table">
                        <tbody>
                            ${rows.map(([label, value, key]) => `
                                <tr>
                                    <th>${escapeHtml(label)}</th>
                                    <td>${escapeHtml(formatDetailValue(value, key || ''))}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            return window.dashboardDetailMap?.wrapEntries(rows, tableHtml) || tableHtml;
        };

        const renderRecordTable = (rows, preferredOrder = []) => {
            if (!Array.isArray(rows) || !rows.length) {
                return '<div class="detail-empty">Belum ada baris data pada tabel relasi ini.</div>';
            }

            const allColumns = Array.from(new Set(rows.flatMap((row) => Object.keys(row || {}))));
            const columns = [
                ...preferredOrder.filter((column) => allColumns.includes(column)),
                ...allColumns.filter((column) => !preferredOrder.includes(column)),
            ];

            const tableHtml = `
                <div class="detail-table-wrap">
                    <table class="detail-record-table">
                        <thead>
                            <tr>
                                ${columns.map((column) => `<th>${escapeHtml(prettifyField(column))}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.map((row) => `
                                <tr>
                                    ${columns.map((column) => `<td>${escapeHtml(formatDetailValue(row[column], column))}</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            return window.dashboardDetailMap?.wrapRows(rows, tableHtml) || tableHtml;
        };

        const renderSection = (title, iconName, body, chip = null) => `
            <section class="detail-section">
                <div class="detail-section-head">
                    <div class="detail-section-title">
                        <span class="detail-section-icon">${iconMarkup(iconName)}</span>
                        <div>
                            <h4>${escapeHtml(title)}</h4>
                        </div>
                    </div>
                    ${chip ? `<span class="detail-chip"><svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 10 14 4H5v9l6 6z"/><path d="M8 8h.01"/></svg><span class="tag-label">Info</span><span class="tag-value">${escapeHtml(chip)}</span></span>` : ''}
                </div>
                ${body}
            </section>
        `;

        const statusTone = (value) => {
            if (value === 'active') {
                return 'ready';
            }

            if (value === 'draft' || value === 'inactive' || value === 'archived') {
                return 'partial';
            }

            return 'missing';
        };

        const renderStatus = (value) => `<span class="status ${statusTone(value)}"><svg class="tag-icon" viewBox="0 0 24 24"><path d="${statusTone(value) === 'ready' ? 'M20 6 9 17l-5-5' : 'M12 7v5l3 2'}"/><circle cx="12" cy="12" r="9"/></svg><span class="tag-label">Status</span><span class="tag-value">${escapeHtml(formatText(value))}</span></span>`;

        const setLoadingState = (loading) => {
            state.loading = loading;

            if (submitButton) {
                submitButton.disabled = loading;
            }
        };

        const renderHeader = () => {
            gridHead.innerHTML = `
                <tr>
                    ${columns.map((column) => `<th>${escapeHtml(prettifyField(column))}</th>`).join('')}
                    <th>Aksi</th>
                </tr>
            `;
        };

        const renderRows = (rows) => {
            const totalColumns = columns.length + 1;

            if (state.loading) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-loading">Memuat data...</td></tr>`;
                return;
            }

            if (!rows.length) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-empty">Belum ada data ${escapeHtml((config.label || 'master data').toLowerCase())}.</td></tr>`;
                return;
            }

            gridBody.innerHTML = rows.map((row) => {
                const cells = columns.map((column, index) => {
                    const value = getValue(row, column);

                    if (column === 'status') {
                        return `<td>${renderStatus(value)}</td>`;
                    }

                    if (column.endsWith('_at')) {
                        return `<td>${escapeHtml(formatDate(value))}</td>`;
                    }

                    if (index === 0) {
                        const primary = formatText(value);
                        const secondary = column === 'code'
                            ? formatText(row.name)
                            : formatText(row.code);

                        return `
                            <td>
                                <div class="row-title">
                                    <strong>${escapeHtml(primary)}</strong>
                                    ${secondary !== '-' ? `<span>${escapeHtml(secondary)}</span>` : ''}
                                </div>
                            </td>
                        `;
                    }

                    return `<td>${escapeHtml(formatText(value))}</td>`;
                }).join('');

                return `
                    <tr>
                        ${cells}
                        <td>
                            <div class="inline-actions">
                                <button class="inline-button" type="button" data-row-action="view" data-uuid="${escapeHtml(row.uuid)}">Lihat</button>
                                <button class="inline-button primary" type="button" data-row-action="edit" data-uuid="${escapeHtml(row.uuid)}">Edit</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const renderPagination = () => {
            const total = Number(state.pagination.total || 0);
            const currentPage = Number(state.pagination.current_page || 1);
            const lastPage = Number(state.pagination.last_page || 1);
            const perPage = Number(state.pagination.per_page || state.perPage || 10);
            const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            if (gridCount) {
                window.dashboardFillStaticTag(gridCount, 'Data', new Intl.NumberFormat('id-ID').format(total), 'data');
            }

            if (gridSummary) {
                gridSummary.textContent = total === 0
                    ? 'Belum ada data.'
                    : `Menampilkan ${new Intl.NumberFormat('id-ID').format(start)}-${new Intl.NumberFormat('id-ID').format(end)} dari ${new Intl.NumberFormat('id-ID').format(total)} data`;
            }

            if (gridPage) {
                gridPage.textContent = `Halaman ${currentPage} / ${lastPage}`;
            }

            if (prevButton) {
                prevButton.disabled = currentPage <= 1 || state.loading;
            }

            if (nextButton) {
                nextButton.disabled = currentPage >= lastPage || state.loading;
            }
        };

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const closeAllModals = () => {
            closeModal(viewModal);
            closeModal(formModal);
        };

        const fetchJson = async (url, options = {}) => {
            const isFormData = options.body instanceof FormData;
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.body && !isFormData ? { 'Content-Type': 'application/json' } : {}),
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    ...(options.headers || {}),
                },
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || (payload && payload.success === false)) {
                throw payload || { message: 'Permintaan gagal.' };
            }

            return payload;
        };

        const extractErrorMessage = (payload) => {
            if (!payload) {
                return 'Terjadi kesalahan saat memproses data.';
            }

            if (payload.error?.details && typeof payload.error.details === 'object') {
                return Object.values(payload.error.details).flat().join('\n');
            }

            if (payload.errors && typeof payload.errors === 'object') {
                return Object.values(payload.errors).flat().join('\n');
            }

            return payload.message || 'Terjadi kesalahan saat memproses data.';
        };

        const loadRecords = async () => {
            setLoadingState(true);
            renderRows([]);
            renderPagination();

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: String(state.perPage),
                sort: '-updated_at',
            });

            if (state.search) {
                params.set('search', state.search);
            }

            try {
                const payload = await fetchJson(`${config.list_endpoint}?${params.toString()}`);
                state.pagination = payload.meta?.pagination || state.pagination;
                setLoadingState(false);
                renderRows(Array.isArray(payload.data) ? payload.data : []);
                renderPagination();
            } catch (errorPayload) {
                setLoadingState(false);
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractErrorMessage(errorPayload))}</td></tr>`;
                renderPagination();
            } finally {
                renderPagination();
            }
        };

        const fetchRecord = async (uuid) => {
            const payload = await fetchJson(`${config.list_endpoint}/${uuid}`);
            return payload.data || null;
        };

        const renderDetail = (record) => {
            if (!viewContent || !viewSubtitle) {
                return;
            }

            viewSubtitle.textContent = `${record.code || '-'} · ${record.type?.name || config.label || ''}`;

            const detailItems = [
                ['Kode', record.code],
                ['Nama', record.name],
                ['Status', record.status],
                ['Parent Code', record.parent_code],
                ['Source System', record.source_system],
                ['Source Table', record.source_table],
                ['Source ID', record.source_id],
                ['Diperbarui', formatDate(record.updated_at)],
            ];

            viewContent.innerHTML = `
                <div class="detail-grid">
                    ${detailItems.map(([label, value]) => `
                        <div class="detail-item">
                            <span>${escapeHtml(label)}</span>
                            <strong>${label === 'Status' ? renderStatus(value) : escapeHtml(formatText(value))}</strong>
                        </div>
                    `).join('')}
                    <div class="detail-item full">
                        <span>Deskripsi</span>
                        <strong>${escapeHtml(formatText(record.description))}</strong>
                    </div>
                </div>
                <div>
                    <div class="detail-item" style="margin-bottom:12px;">
                        <span>Data JSON</span>
                    </div>
                    <pre class="json-preview">${escapeHtml(JSON.stringify(record.data || {}, null, 2))}</pre>
                </div>
            `;
        };

        const clearFormFeedback = () => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = true;
            formFeedback.textContent = '';
        };

        const showFormFeedback = (message) => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = false;
            formFeedback.textContent = message;
        };

        const resetForm = () => {
            if (!form) {
                return;
            }

            form.reset();
            form.querySelector('[name="status"]').value = 'active';
            form.querySelector('[name="data_json"]').value = '{}';
            state.activeUuid = null;
            state.mode = 'create';

            if (formTitle) {
                formTitle.textContent = `Tambah ${config.label || 'Data'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Isi data inti lalu simpan.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan';
            }

            clearFormFeedback();
        };

        const fillForm = (record) => {
            if (!form) {
                return;
            }

            form.querySelector('[name="code"]').value = record.code || '';
            form.querySelector('[name="name"]').value = record.name || '';
            form.querySelector('[name="status"]').value = record.status || 'active';
            form.querySelector('[name="parent_code"]').value = record.parent_code || '';
            form.querySelector('[name="source_system"]').value = record.source_system || '';
            form.querySelector('[name="source_table"]').value = record.source_table || '';
            form.querySelector('[name="source_id"]').value = record.source_id || '';
            form.querySelector('[name="description"]').value = record.description || '';
            form.querySelector('[name="data_json"]').value = JSON.stringify(record.data || {}, null, 2);
        };

        const openCreateForm = () => {
            resetForm();
            openModal(formModal);
        };

        const openEditForm = async (uuid) => {
            resetForm();
            state.mode = 'edit';
            state.activeUuid = uuid;

            if (formTitle) {
                formTitle.textContent = `Edit ${config.label || 'Data'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Perbarui data lalu simpan.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan Perubahan';
            }

            openModal(formModal);
            showFormFeedback('Memuat data...');

            try {
                const record = await fetchRecord(uuid);
                fillForm(record);
                clearFormFeedback();
            } catch (errorPayload) {
                showFormFeedback(extractErrorMessage(errorPayload));
            }
        };

        const openDetailModal = async (uuid) => {
            if (viewContent) {
                viewContent.innerHTML = '<div class="grid-loading">Memuat detail...</div>';
            }

            if (viewSubtitle) {
                viewSubtitle.textContent = 'Memuat data...';
            }

            openModal(viewModal);

            try {
                const record = await fetchRecord(uuid);
                renderDetail(record);
            } catch (errorPayload) {
                if (viewContent) {
                    viewContent.innerHTML = `<div class="feedback">${escapeHtml(extractErrorMessage(errorPayload))}</div>`;
                }
            }
        };

        renderHeader();
        renderPagination();
        loadRecords();

        gridSearch?.addEventListener('input', (event) => {
            window.clearTimeout(searchTimer);
            state.search = event.target.value.trim();
            searchTimer = window.setTimeout(() => {
                state.page = 1;
                loadRecords();
            }, 280);
        });

        gridPerPage?.addEventListener('change', (event) => {
            state.perPage = Number(event.target.value || 10);
            state.page = 1;
            loadRecords();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadRecords();
        });

        nextButton?.addEventListener('click', () => {
            if (state.page >= Number(state.pagination.last_page || 1)) {
                return;
            }

            state.page += 1;
            loadRecords();
        });

        createButton?.addEventListener('click', openCreateForm);

        masterDataRoot.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-row-action]');
            if (!trigger) {
                return;
            }

            const { rowAction, uuid } = trigger.dataset;

            if (rowAction === 'view') {
                openDetailModal(uuid);
            }

            if (rowAction === 'edit') {
                openEditForm(uuid);
            }
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeAllModals();
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFormFeedback();

            let parsedData = {};

            try {
                const rawData = form.querySelector('[name="data_json"]').value.trim();
                parsedData = rawData === '' ? {} : JSON.parse(rawData);
            } catch (parseError) {
                showFormFeedback('Data JSON tidak valid.');
                return;
            }

            const payload = {
                code: form.querySelector('[name="code"]').value.trim(),
                name: form.querySelector('[name="name"]').value.trim() || null,
                status: form.querySelector('[name="status"]').value,
                parent_code: form.querySelector('[name="parent_code"]').value.trim() || null,
                source_system: form.querySelector('[name="source_system"]').value.trim() || null,
                source_table: form.querySelector('[name="source_table"]').value.trim() || null,
                source_id: form.querySelector('[name="source_id"]').value.trim() || null,
                description: form.querySelector('[name="description"]').value.trim() || null,
                data: parsedData,
            };

            const url = state.mode === 'edit' && state.activeUuid
                ? `${config.list_endpoint}/${state.activeUuid}`
                : config.store_endpoint;
            const method = state.mode === 'edit' ? 'PATCH' : 'POST';

            setLoadingState(true);

            try {
                await fetchJson(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                state.page = 1;
                closeModal(formModal);
                resetForm();
                loadRecords();
            } catch (errorPayload) {
                showFormFeedback(extractErrorMessage(errorPayload));
            } finally {
                setLoadingState(false);
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-tunnel-source-app]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const body = document.body;

        if (!root) {
            return;
        }

        const config = JSON.parse(root.dataset.tunnelSourceApp || '{}');
        const columns = Array.isArray(config.columns) && config.columns.length > 0
            ? config.columns
            : ['nama_terowongan', 'nomor_bh', 'km_hm', 'status_operasi', 'updated_at'];
        const columnLabels = {
            tunnel_id: 'Tunnel ID',
            kode_aset: 'Kode Aset',
            nomor_bh: 'No. BH',
            nama_terowongan: 'Nama Terowongan',
            id_wilayah_kerja: 'Wilayah Kerja',
            id_lintas: 'Lintas',
            km_hm: 'KM/HM',
            panjang_m: 'Panjang (m)',
            tahun_bangunan: 'Tahun Bangunan',
            tahun_operasi: 'Tahun Operasi',
            umur_tahun: 'Umur',
            status_operasi: 'Status Operasi',
            status_aset: 'Status Aset',
            kondisi_terakhir: 'Kondisi',
            tgl_inspeksi_terakhir: 'Inspeksi Terakhir',
            created_at: 'Dibuat',
            updated_at: 'Diperbarui',
        };

        const gridHead = root.querySelector('[data-grid-head]');
        const gridBody = root.querySelector('[data-grid-body]');
        const gridSearch = root.querySelector('[data-grid-search]');
        const gridPerPage = root.querySelector('[data-grid-per-page]');
        const gridCount = root.querySelector('[data-grid-count]');
        const gridSummary = root.querySelector('[data-grid-summary]');
        const gridPage = root.querySelector('[data-grid-page]');
        const prevButton = root.querySelector('[data-grid-prev]');
        const nextButton = root.querySelector('[data-grid-next]');
        const createButton = root.querySelector('[data-grid-create]');
        const importButton = root.querySelector('[data-tunnel-import-trigger]');
        const importFile = root.querySelector('[data-tunnel-import-file]');
        const viewModal = document.querySelector('[data-tunnel-view-modal]');
        const viewSubtitle = viewModal?.querySelector('[data-tunnel-view-subtitle]');
        const viewContent = viewModal?.querySelector('[data-tunnel-view-content]');
        const formModal = document.querySelector('[data-tunnel-form-modal]');
        const form = formModal?.querySelector('[data-tunnel-source-form]');
        const formTitle = formModal?.querySelector('[data-tunnel-form-title]');
        const formSubtitle = formModal?.querySelector('[data-tunnel-form-subtitle]');
        const formFeedback = formModal?.querySelector('[data-tunnel-form-feedback]');
        const submitButton = formModal?.querySelector('[data-tunnel-form-submit]');
        const coordinateButton = formModal?.querySelector('[data-tunnel-coordinate-open]');
        const coordinateModal = document.querySelector('[data-tunnel-coordinate-modal]');
        const coordinateMapCanvas = coordinateModal?.querySelector('[data-coordinate-map]');
        const coordinateSearchForm = coordinateModal?.querySelector('[data-coordinate-search-form]');
        const coordinateSearchInput = coordinateModal?.querySelector('[data-coordinate-search-input]');
        const coordinateFeedback = coordinateModal?.querySelector('[data-coordinate-feedback]');
        const coordinateApplyButton = coordinateModal?.querySelector('[data-coordinate-apply]');
        const coordinateLiveLat = coordinateModal?.querySelector('[data-coordinate-live-lat]');
        const coordinateLiveLon = coordinateModal?.querySelector('[data-coordinate-live-lon]');
        const nestedModal = document.querySelector('[data-tunnel-nested-modal]');
        const nestedForm = nestedModal?.querySelector('[data-tunnel-nested-form]');
        const nestedTitle = nestedModal?.querySelector('[data-tunnel-nested-title]');
        const nestedSubtitle = nestedModal?.querySelector('[data-tunnel-nested-subtitle]');
        const nestedFields = nestedModal?.querySelector('[data-tunnel-nested-fields]');
        const nestedSummaryNodes = formModal
            ? Object.fromEntries(Array.from(formModal.querySelectorAll('[data-tunnel-nested-summary]')).map((node) => [node.dataset.tunnelNestedSummary, node]))
            : {};

        const state = {
            page: 1,
            perPage: Number(gridPerPage?.value || 10),
            search: '',
            loading: false,
            rows: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                total: Number(config.records_count || 0),
                per_page: Number(gridPerPage?.value || 10),
            },
        };
        let searchTimer = null;
        let editingTunnelId = null;
        let coordinateMap = null;
        let activeNestedKey = null;
        let nestedState = {
            structure: {},
            specs: {},
            docs: {},
        };

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const prettify = (field) => columnLabels[field]
            || field.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

        const isNameKey = (key) => key === 'nama' || key.startsWith('nama_') || key.includes('.nama_');

        const getValue = (object, path) => path.split('.').reduce((carry, segment) => {
            if (carry === null || carry === undefined) {
                return null;
            }

            return carry[segment] ?? null;
        }, object);

        const formatText = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (typeof value === 'object') {
                return JSON.stringify(value);
            }

            return String(value);
        };

        const formatNumber = (value, suffix = '') => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const number = Number(value);

            if (Number.isNaN(number)) {
                return String(value);
            }

            return `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(number)}${suffix}`;
        };

        const formatDate = (value) => {
            if (!value) {
                return '-';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return String(value);
            }

            return new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'medium',
                timeStyle: value.length > 10 ? 'short' : undefined,
                timeZone: 'UTC',
            }).format(date);
        };

        const formatValue = (value, key = '') => {
            if (isNameKey(key) && value !== null && value !== undefined && value !== '') {
                return String(value).toUpperCase();
            }

            if (key === 'panjang_m') {
                return formatNumber(value, ' m');
            }

            if (key === 'lat' || key === 'long') {
                return formatNumber(value);
            }

            if (key.endsWith('_at') || key.startsWith('tgl_')) {
                return formatDate(value);
            }

            return formatText(value);
        };

        const iconMarkup = (name) => {
            const icons = {
                tunnel: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 18V9a8 8 0 0 1 16 0v9"/><path d="M8 18V9a4 4 0 0 1 8 0v9"/><path d="M3 18h18"/></svg>',
                map: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 6 6-2 6 2 6-2v14l-6 2-6-2-6 2z"/><path d="M9 4v14"/><path d="M15 6v14"/></svg>',
                route: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="18" r="2"/><circle cx="18" cy="6" r="2"/><path d="M8 18h4a6 6 0 0 0 6-6V8"/></svg>',
                structure: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M6 19V9l6-4 6 4v10"/><path d="M9 19v-7h6v7"/></svg>',
                specs: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/><path d="M8 4v16"/><path d="M16 4v9"/></svg>',
                documents: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',
                database: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v14c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3"/></svg>',
            };

            return icons[name] || icons.database;
        };

        const nestedConfigs = {
            structure: {
                title: 'Struktur Terowongan',
                subtitle: 'Data disimpan ke tabel m_tunnel_structures.',
                fields: [
                    { name: 'jenis_struktur', label: 'Jenis Struktur' },
                    { name: 'material_struktur', label: 'Material Struktur' },
                    { name: 'material_lining', label: 'Material Lining' },
                    { name: 'material_portal', label: 'Material Portal' },
                    { name: 'material_invert', label: 'Material Invert' },
                    { name: 'metode_konstruksi', label: 'Metode Konstruksi' },
                    { name: 'waterproofing', label: 'Waterproofing' },
                    { name: 'tahun_rehabilitasi_terakhir', label: 'Tahun Rehabilitasi Terakhir', type: 'number', min: 1800 },
                ],
            },
            specs: {
                title: 'Spesifikasi Terowongan',
                subtitle: 'Data disimpan ke tabel m_tunnel_specs.',
                fields: [
                    { name: 'jumlah_jalur', label: 'Jumlah Jalur', type: 'number', min: 1 },
                    { name: 'jenis_jalur', label: 'Jenis Jalur' },
                    { name: 'gauge_m', label: 'Gauge (m)', type: 'number', min: 0, step: '0.001' },
                    { name: 'lebar_bersih_m', label: 'Lebar Bersih (m)', type: 'number', min: 0, step: '0.01' },
                    { name: 'tinggi_bersih_m', label: 'Tinggi Bersih (m)', type: 'number', min: 0, step: '0.01' },
                    { name: 'clearance_horizontal_mm', label: 'Clearance Horizontal (mm)', type: 'number', min: 1 },
                    { name: 'clearance_vertikal_mm', label: 'Clearance Vertikal (mm)', type: 'number', min: 1 },
                    { name: 'bentuk_penampang', label: 'Bentuk Penampang' },
                    { name: 'gradien_persen', label: 'Gradien (%)', type: 'number', min: 0, step: '0.01' },
                    { name: 'radius_lengkung_m', label: 'Radius Lengkung (m)', type: 'number', min: 0, step: '0.01' },
                    { name: 'catatan_teknis', label: 'Catatan Teknis', type: 'textarea', full: true },
                ],
            },
            docs: {
                title: 'Dokumen Terowongan',
                subtitle: 'Upload file PDF atau image, lalu path disimpan ke tabel m_tunnel_docs.',
                fields: [
                    { name: 'no_ded_bed_kajian_teknis', label: 'No. DED/BED/Kajian Teknis' },
                    { name: 'ded_bed_kajian_teknis', label: 'File DED/BED/Kajian Teknis', type: 'file', docObject: true, full: true },
                    { name: 'no_spesifikasi_teknis', label: 'No. Spesifikasi Teknis' },
                    { name: 'spesifikasi_teknis', label: 'File Spesifikasi Teknis', type: 'file', docObject: true, full: true },
                    { name: 'no_shop_drawing', label: 'No. Shop Drawing' },
                    { name: 'shop_drawing', label: 'File Shop Drawing', type: 'file', docObject: true, full: true },
                    { name: 'no_as_built_drawing', label: 'No. As Built Drawing' },
                    { name: 'as_built_drawing', label: 'File As Built Drawing', type: 'file', docObject: true, full: true },
                    { name: 'no_dok_hasil_uji', label: 'No. Dokumen Hasil Uji' },
                    { name: 'dok_hasil_uji', label: 'File Dokumen Hasil Uji', type: 'file', docObject: true, full: true },
                ],
            },
        };
        const documentFileFields = ['ded_bed_kajian_teknis', 'spesifikasi_teknis', 'shop_drawing', 'as_built_drawing', 'dok_hasil_uji'];

        const generateDisplayUlid = () => {
            const alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
            let time = Date.now();
            const chars = Array(26).fill('0');

            for (let index = 9; index >= 0; index -= 1) {
                chars[index] = alphabet[time % 32];
                time = Math.floor(time / 32);
            }

            const random = new Uint8Array(16);

            if (window.crypto?.getRandomValues) {
                window.crypto.getRandomValues(random);
            } else {
                for (let index = 0; index < random.length; index += 1) {
                    random[index] = Math.floor(Math.random() * 256);
                }
            }

            for (let index = 10; index < 26; index += 1) {
                chars[index] = alphabet[random[index - 10] % 32];
            }

            return chars.join('');
        };

        const visibleDocValue = (value) => {
            if (value === null || value === undefined || value === '') {
                return '';
            }

            if (typeof value !== 'object') {
                return String(value);
            }

            if (Array.isArray(value)) {
                return value.map(visibleDocValue).filter(Boolean).join(', ');
            }

            return String(value.value ?? value.file_name ?? value.name ?? value.path ?? value.url ?? Object.values(value).filter((item) => typeof item !== 'object').join(', ') ?? '');
        };

        const cleanNestedPayload = (value) => {
            if (!value || typeof value !== 'object') {
                return {};
            }

            return Object.fromEntries(Object.entries(value).filter(([, item]) => (
                item !== null
                && item !== undefined
                && item !== ''
                && !(typeof item === 'object' && !Array.isArray(item) && Object.keys(item).length === 0)
            )));
        };

        const updateNestedSummaries = () => {
            Object.entries(nestedConfigs).forEach(([key, config]) => {
                const node = nestedSummaryNodes[key];

                if (!node) {
                    return;
                }

                const filled = Object.keys(cleanNestedPayload(nestedState[key] || {})).length;
                node.textContent = filled ? `${filled} field terisi` : 'Belum diisi';
                node.classList.toggle('is-filled', filled > 0);
            });
        };

        const nestedFieldValue = (key, field) => {
            const value = nestedState[key]?.[field.name];

            return field.docObject ? visibleDocValue(value) : (value ?? '');
        };

        const renderNestedFields = (key) => {
            const config = nestedConfigs[key];

            if (!config || !nestedFields) {
                return;
            }

            nestedFields.innerHTML = config.fields.map((field) => {
                const id = `tunnel-nested-${key}-${field.name}`;
                const value = escapeHtml(nestedFieldValue(key, field));
                const fieldClass = field.full || field.type === 'textarea' ? 'field full' : 'field';

                if (field.type === 'file') {
                    const existing = nestedState[key]?.[field.name];
                    const existingLabel = escapeHtml(visibleDocValue(existing) || 'Belum ada file tersimpan');

                    return `
                        <div class="${fieldClass}">
                            <label for="${id}">${escapeHtml(field.label)}</label>
                            <input id="${id}" name="${escapeHtml(field.name)}" type="file" accept="application/pdf,image/*" data-doc-file="true">
                            <p class="field-hint">${existingLabel}</p>
                        </div>
                    `;
                }

                if (field.type === 'textarea') {
                    return `
                        <div class="${fieldClass}">
                            <label for="${id}">${escapeHtml(field.label)}</label>
                            <textarea id="${id}" name="${escapeHtml(field.name)}" data-doc-object="${field.docObject ? 'true' : 'false'}">${value}</textarea>
                        </div>
                    `;
                }

                return `
                    <div class="${fieldClass}">
                        <label for="${id}">${escapeHtml(field.label)}</label>
                        <input id="${id}" name="${escapeHtml(field.name)}" type="${escapeHtml(field.type || 'text')}" value="${value}"${field.min !== undefined ? ` min="${escapeHtml(field.min)}"` : ''}${field.step ? ` step="${escapeHtml(field.step)}"` : ''}>
                    </div>
                `;
            }).join('');
        };

        const openNestedModal = (key) => {
            const config = nestedConfigs[key];

            if (!config || !nestedModal) {
                return;
            }

            activeNestedKey = key;

            if (nestedTitle) {
                nestedTitle.textContent = config.title;
            }

            if (nestedSubtitle) {
                nestedSubtitle.textContent = config.subtitle;
            }

            renderNestedFields(key);
            openModal(nestedModal);
        };

        const saveNestedModal = () => {
            const config = nestedConfigs[activeNestedKey];

            if (!config || !nestedForm || !activeNestedKey) {
                return;
            }

            const nextValue = {};

            config.fields.forEach((field) => {
                const input = nestedForm.querySelector(`[name="${field.name}"]`);

                if (field.type === 'file') {
                    const file = input?.files?.[0] || null;
                    const existing = nestedState[activeNestedKey]?.[field.name];

                    if (file) {
                        nextValue[field.name] = {
                            ...(existing && typeof existing === 'object' && !Array.isArray(existing) ? existing : {}),
                            file,
                            file_name: file.name,
                            mime_type: file.type,
                            size: file.size,
                            pending_upload: true,
                        };
                    } else if (existing !== null && existing !== undefined && existing !== '') {
                        nextValue[field.name] = existing;
                    }

                    return;
                }

                const value = toNullable(input?.value);

                if (value === null) {
                    return;
                }

                nextValue[field.name] = field.docObject ? { value } : value;
            });

            nestedState = {
                ...nestedState,
                [activeNestedKey]: nextValue,
            };

            updateNestedSummaries();
            closeModal(nestedModal);
            activeNestedKey = null;
        };

        const fetchJson = async (url, options = {}) => {
            const isFormData = typeof FormData !== 'undefined' && options.body instanceof FormData;
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.body && !isFormData ? { 'Content-Type': 'application/json' } : {}),
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    ...(options.headers || {}),
                },
            });
            const payload = await response.json().catch(() => null);

            if (!response.ok || (payload && payload.success === false)) {
                throw payload || { message: 'Permintaan gagal.' };
            }

            return payload;
        };

        const extractErrorMessage = (payload) => {
            if (!payload) {
                return 'Terjadi kesalahan saat memproses data terowongan.';
            }

            if (payload.error?.details && typeof payload.error.details === 'object') {
                return Object.values(payload.error.details).flat().join('\n');
            }

            if (payload.errors && typeof payload.errors === 'object') {
                return Object.values(payload.errors).flat().join('\n');
            }

            return payload.message || 'Terjadi kesalahan saat memproses data terowongan.';
        };

        const hasPendingDocumentUpload = (payload) => Boolean(payload.docs && documentFileFields.some((field) => payload.docs?.[field]?.file instanceof File));

        const withoutUploadInternals = (value) => {
            if (value instanceof File) {
                return undefined;
            }

            if (Array.isArray(value)) {
                return value.map(withoutUploadInternals).filter((item) => item !== undefined);
            }

            if (value && typeof value === 'object') {
                return Object.fromEntries(Object.entries(value)
                    .filter(([key]) => !['file', 'pending_upload'].includes(key))
                    .map(([key, item]) => [key, withoutUploadInternals(item)])
                    .filter(([, item]) => item !== undefined));
            }

            return value;
        };

        const appendFormData = (formData, key, value) => {
            if (value === null || value === undefined) {
                return;
            }

            if (Array.isArray(value)) {
                value.forEach((item, index) => appendFormData(formData, `${key}[${index}]`, item));
                return;
            }

            if (typeof value === 'object') {
                Object.entries(value).forEach(([childKey, item]) => appendFormData(formData, `${key}[${childKey}]`, item));
                return;
            }

            formData.append(key, value);
        };

        const buildRequestBody = (payload) => {
            if (!hasPendingDocumentUpload(payload)) {
                return {
                    body: JSON.stringify(payload),
                    method: editingTunnelId ? 'PATCH' : 'POST',
                };
            }

            const formData = new FormData();

            documentFileFields.forEach((field) => {
                const file = payload.docs?.[field]?.file;

                if (file instanceof File) {
                    formData.append(`docs_files[${field}]`, file);
                }
            });

            Object.entries(withoutUploadInternals(payload)).forEach(([key, value]) => appendFormData(formData, key, value));

            if (editingTunnelId) {
                formData.append('_method', 'PATCH');
            }

            return {
                body: formData,
                method: editingTunnelId ? 'POST' : 'POST',
            };
        };

        const setLoadingState = (loading) => {
            state.loading = loading;

            if (submitButton) {
                submitButton.disabled = loading;
            }

            if (importButton) {
                importButton.disabled = loading;
            }
        };

        const clearFormFeedback = () => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = true;
            formFeedback.textContent = '';
        };

        const showFormFeedback = (message) => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = false;
            formFeedback.textContent = message;
        };

        const getField = (name) => form?.querySelector(`[name="${name}"]`);

        const toNullable = (value) => {
            const trimmed = String(value ?? '').trim();

            return trimmed === '' ? null : trimmed;
        };

        const resetForm = () => {
            form?.reset();
            editingTunnelId = null;
            nestedState = {
                structure: {},
                specs: {},
                docs: {},
            };
            setField('tunnel_id_display', generateDisplayUlid());

            if (formTitle) {
                formTitle.textContent = `Tambah ${config.label || 'Terowongan'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Data disimpan ke prasarana_tunnel.';
            }

            updateNestedSummaries();
            clearFormFeedback();
        };

        const setField = (name, value) => {
            const field = getField(name);

            if (!field) {
                return;
            }

            if (field.tagName === 'SELECT' && value !== null && value !== undefined && value !== '' && !Array.from(field.options).some((option) => option.value === String(value))) {
                field.add(new Option(String(value).toUpperCase(), String(value)));
            }

            field.value = value ?? '';
        };

        const cleanNested = (value) => {
            if (!value || typeof value !== 'object') {
                return {};
            }

            const copy = { ...value };
            ['id', 'tunnel_id', 'created_at', 'updated_at', 'deleted_at'].forEach((key) => {
                delete copy[key];
            });

            return copy;
        };

        const fillForm = (record) => {
            [
                'kode_aset',
                'nomor_bh',
                'nama_terowongan',
                'id_wilayah_kerja',
                'id_lintas',
                'km_hm',
                'panjang_m',
                'tahun_bangunan',
                'tahun_operasi',
                'umur_tahun',
                'lat',
                'long',
                'status_operasi',
                'status_aset',
                'kondisi_terakhir',
                'tgl_inspeksi_terakhir',
            ].forEach((name) => {
                setField(name, name === 'lat' || name === 'long'
                    ? (record.coordinates?.[name] ?? record[name] ?? '')
                    : (record[name] ?? ''));
            });

            setField('tunnel_id_display', record.tunnel_id || editingTunnelId || '');
            nestedState = {
                structure: cleanNested(record.structure),
                specs: cleanNested(record.specs),
                docs: cleanNested(record.docs),
            };
            updateNestedSummaries();
        };

        const buildPayload = () => {
            const payload = {
                kode_aset: toNullable(getField('kode_aset')?.value),
                nomor_bh: toNullable(getField('nomor_bh')?.value),
                nama_terowongan: toNullable(getField('nama_terowongan')?.value),
                id_wilayah_kerja: toNullable(getField('id_wilayah_kerja')?.value),
                id_lintas: toNullable(getField('id_lintas')?.value),
                km_hm: toNullable(getField('km_hm')?.value),
                panjang_m: toNullable(getField('panjang_m')?.value),
                tahun_bangunan: toNullable(getField('tahun_bangunan')?.value),
                tahun_operasi: toNullable(getField('tahun_operasi')?.value),
                umur_tahun: toNullable(getField('umur_tahun')?.value),
                lat: toNullable(getField('lat')?.value),
                long: toNullable(getField('long')?.value),
                status_operasi: toNullable(getField('status_operasi')?.value),
                status_aset: toNullable(getField('status_aset')?.value),
                kondisi_terakhir: toNullable(getField('kondisi_terakhir')?.value),
                tgl_inspeksi_terakhir: toNullable(getField('tgl_inspeksi_terakhir')?.value),
            };
            const structure = cleanNestedPayload(nestedState.structure);
            const specs = cleanNestedPayload(nestedState.specs);
            const docs = cleanNestedPayload(nestedState.docs);

            if (Object.keys(structure).length) {
                payload.structure = structure;
            }

            if (Object.keys(specs).length) {
                payload.specs = specs;
            }

            if (Object.keys(docs).length) {
                payload.docs = docs;
            }

            return Object.fromEntries(Object.entries(payload).filter(([, value]) => value !== null));
        };

        const openCreateForm = () => {
            resetForm();
            openModal(formModal);
        };

        const openEditForm = async (tunnelId) => {
            if (!tunnelId) {
                return;
            }

            resetForm();
            editingTunnelId = tunnelId;

            if (formTitle) {
                formTitle.textContent = `Edit ${config.label || 'Terowongan'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = `Memperbarui ${tunnelId}.`;
            }

            openModal(formModal);
            setLoadingState(true);

            try {
                const payload = await fetchJson(`${config.list_endpoint}/${encodeURIComponent(tunnelId)}`);
                fillForm(payload.data || {});
            } catch (errorPayload) {
                showFormFeedback(extractErrorMessage(errorPayload));
            } finally {
                setLoadingState(false);
            }
        };

        const coordinateCenter = () => {
            const lat = Number(getField('lat')?.value || NaN);
            const lon = Number(getField('long')?.value || NaN);

            if (Number.isFinite(lat) && Number.isFinite(lon) && lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                return { lat, lon, zoom: 17 };
            }

            return { lat: -6.175392, lon: 106.827153, zoom: 6 };
        };

        const showCoordinateFeedback = (message) => {
            if (!coordinateFeedback) {
                return;
            }

            coordinateFeedback.hidden = !message;
            coordinateFeedback.textContent = message || '';
        };

        const updateCoordinateLive = () => {
            if (!coordinateMap) {
                return;
            }

            const center = coordinateMap.getCenter();

            if (coordinateLiveLat) {
                coordinateLiveLat.textContent = center.lat.toFixed(7);
            }

            if (coordinateLiveLon) {
                coordinateLiveLon.textContent = center.lng.toFixed(7);
            }
        };

        const initCoordinateMap = () => {
            if (!window.L || !coordinateMapCanvas) {
                return;
            }

            const center = coordinateCenter();

	            if (!coordinateMap) {
	                coordinateMap = window.L.map(coordinateMapCanvas, {
	                    zoomControl: true,
	                    attributionControl: true,
	                    zoomSnap: 0.5,
	                    zoomDelta: 0.5,
	                    wheelDebounceTime: 140,
	                    wheelPxPerZoomLevel: 240,
	                }).setView([center.lat, center.lon], center.zoom);

                window.L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19,
                    maxNativeZoom: 19,
                }).addTo(coordinateMap);

                coordinateMap.on('move', updateCoordinateLive);
                coordinateMap.on('moveend', updateCoordinateLive);
            } else {
                coordinateMap.setView([center.lat, center.lon], center.zoom);
            }

            window.setTimeout(() => {
                coordinateMap?.invalidateSize();
                updateCoordinateLive();
            }, 120);
        };

        const openCoordinatePicker = () => {
            showCoordinateFeedback('');
            openModal(coordinateModal);
            initCoordinateMap();
        };

        const searchCoordinate = async (query) => {
            const keyword = query.trim();

            if (!keyword) {
                return;
            }

            showCoordinateFeedback('Mencari lokasi...');

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(keyword)}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                });
                const payload = await response.json();
                const result = Array.isArray(payload) ? payload[0] : null;

                if (!result) {
                    showCoordinateFeedback('Alamat tidak ditemukan.');
                    return;
                }

                coordinateMap?.setView([Number(result.lat), Number(result.lon)], 17);
                showCoordinateFeedback('');
            } catch {
                showCoordinateFeedback('Pencarian alamat gagal. Coba lagi beberapa saat.');
            }
        };

        const applyCoordinate = () => {
            if (!coordinateMap) {
                return;
            }

            const center = coordinateMap.getCenter();
            setField('lat', center.lat.toFixed(7));
            setField('long', center.lng.toFixed(7));
            closeModal(coordinateModal);
        };

        const importCsv = async (file) => {
            if (!file || !config.import_endpoint) {
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            setLoadingState(true);
            renderRows([]);

            try {
                await fetchJson(config.import_endpoint, {
                    method: 'POST',
                    body: formData,
                });
                state.page = 1;
                await loadRecords();
            } catch (errorPayload) {
                setLoadingState(false);
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractErrorMessage(errorPayload))}</td></tr>`;
                renderPagination();
            } finally {
                if (importFile) {
                    importFile.value = '';
                }
            }
        };

        const renderHeader = () => {
            gridHead.innerHTML = `
                <tr>
                    ${columns.map((column) => `<th>${escapeHtml(prettify(column))}</th>`).join('')}
                    <th>Aksi</th>
                </tr>
            `;
        };

        const renderRows = (rows) => {
            const totalColumns = columns.length + 1;

            if (state.loading) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-loading">Memuat data terowongan...</td></tr>`;
                return;
            }

            if (!rows.length) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-empty">Belum ada data terowongan di prasarana_tunnel.</td></tr>`;
                return;
            }

            gridBody.innerHTML = rows.map((row) => {
                const cells = columns.map((column, index) => {
                    const value = getValue(row, column);

                    if (index === 0) {
                        return `
                            <td>
                                <div class="row-title">
                                    <strong>${escapeHtml(formatValue(value, column))}</strong>
                                    <span>${escapeHtml([
                                        row.tunnel_id ? `ID: ${row.tunnel_id}` : null,
                                        row.nomor_bh,
                                        row.kode_aset,
                                        row.km_hm,
                                    ].filter(Boolean).join(' | ') || '-')}</span>
                                </div>
                            </td>
                        `;
                    }

                    return `<td>${escapeHtml(formatValue(value, column))}</td>`;
                }).join('');

                return `
                    <tr>
                        ${cells}
                        <td class="tunnel-actions-cell">
                            <div class="inline-actions tunnel-row-actions">
                                <button class="inline-button" type="button" data-tunnel-row-action="view" data-tunnel-id="${escapeHtml(row.tunnel_id)}">Lihat</button>
                                <button class="inline-button" type="button" data-tunnel-row-action="edit" data-tunnel-id="${escapeHtml(row.tunnel_id)}">Edit</button>
                                <button class="inline-button danger" type="button" data-tunnel-row-action="delete" data-tunnel-id="${escapeHtml(row.tunnel_id)}">Hapus</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const renderPagination = () => {
            const total = Number(state.pagination.total || 0);
            const currentPage = Number(state.pagination.current_page || 1);
            const lastPage = Number(state.pagination.last_page || 1);
            const perPage = Number(state.pagination.per_page || state.perPage || 10);
            const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            if (gridCount) {
                window.dashboardFillStaticTag(gridCount, 'Data', new Intl.NumberFormat('id-ID').format(total), 'data');
            }

            if (gridSummary) {
                gridSummary.textContent = total === 0
                    ? 'Belum ada data.'
                    : `Menampilkan ${new Intl.NumberFormat('id-ID').format(start)}-${new Intl.NumberFormat('id-ID').format(end)} dari ${new Intl.NumberFormat('id-ID').format(total)} data`;
            }

            if (gridPage) {
                gridPage.textContent = `Halaman ${currentPage} / ${lastPage}`;
            }

            if (prevButton) {
                prevButton.disabled = currentPage <= 1 || state.loading;
            }

            if (nextButton) {
                nextButton.disabled = currentPage >= lastPage || state.loading;
            }
        };

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const renderKeyValueTable = (entries) => {
            const rows = entries.filter(([, value]) => value !== undefined);

            if (!rows.length) {
                return '<div class="detail-empty">Belum ada data pada bagian ini.</div>';
            }

            const renderDetailValue = (label, value, key = '') => {
                if (window.dashboardDocumentPreview?.normalize(value)) {
                    return window.dashboardDocumentPreview.render(value, label);
                }

                return escapeHtml(formatValue(value, key || ''));
            };

            const tableHtml = `
                <div class="detail-table-wrap">
                    <table class="detail-kv-table">
                        <tbody>
                            ${rows.map(([label, value, key]) => `
                                <tr>
                                    <th>${escapeHtml(label)}</th>
                                    <td>${renderDetailValue(label, value, key)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            return window.dashboardDetailMap?.wrapEntries(rows, tableHtml) || tableHtml;
        };

        const renderSection = (title, iconName, body, chip = null) => `
            <section class="detail-section">
                <div class="detail-section-head">
                    <div class="detail-section-title">
                        <span class="detail-section-icon">${iconMarkup(iconName)}</span>
                        <div><h4>${escapeHtml(title)}</h4></div>
                    </div>
                    ${chip ? `<span class="detail-chip"><svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 10 14 4H5v9l6 6z"/><path d="M8 8h.01"/></svg><span class="tag-label">Info</span><span class="tag-value">${escapeHtml(chip)}</span></span>` : ''}
                </div>
                ${body}
            </section>
        `;

        const renderDetail = (record) => {
            if (!viewContent || !viewSubtitle) {
                return;
            }

            viewSubtitle.textContent = `${formatValue(record.nama_terowongan, 'nama_terowongan')} · ${record.nomor_bh || record.tunnel_id || '-'}`;
            const coordinates = record.coordinates || {};
            const structure = record.structure || {};
            const specs = record.specs || {};
            const docs = record.docs || {};
            const coordinateSummary = [coordinates.lat, coordinates.long]
                .filter((value) => value !== null && value !== undefined && value !== '')
                .join(', ') || '-';

            const summaryTag = (label, value, icon) => `
                <span class="bridge-summary-tag">
                    <span class="tag-icon-circle">${icon}</span>
                    <span class="tag-label">${escapeHtml(label)}</span>
                    <span class="tag-value">${escapeHtml(formatText(value))}</span>
                </span>
            `;

            const identityRows = [
                ['Tunnel ID', record.tunnel_id, 'tunnel_id'],
                ['Kode Aset', record.kode_aset, 'kode_aset'],
                ['Nama Terowongan', record.nama_terowongan, 'nama_terowongan'],
                ['Nomor BH', record.nomor_bh, 'nomor_bh'],
                ['KM/HM', record.km_hm, 'km_hm'],
                ['Wilayah Kerja', record.id_wilayah_kerja, 'id_wilayah_kerja'],
                ['Lintas', record.id_lintas, 'id_lintas'],
                ['Latitude', coordinates.lat, 'lat'],
                ['Longitude', coordinates.long, 'long'],
                ['Dibuat', record.created_at, 'created_at'],
                ['Diperbarui', record.updated_at, 'updated_at'],
            ];

            const operationRows = [
                ['Panjang', record.panjang_m, 'panjang_m'],
                ['Tahun Bangunan', record.tahun_bangunan, 'tahun_bangunan'],
                ['Tahun Operasi', record.tahun_operasi, 'tahun_operasi'],
                ['Umur', record.umur_tahun, 'umur_tahun'],
                ['Status Operasi', record.status_operasi, 'status_operasi'],
                ['Status Aset', record.status_aset, 'status_aset'],
                ['Kondisi Terakhir', record.kondisi_terakhir, 'kondisi_terakhir'],
                ['Tanggal Inspeksi Terakhir', record.tgl_inspeksi_terakhir, 'tgl_inspeksi_terakhir'],
            ];

            viewContent.innerHTML = `
                <section class="detail-hero bridge-summary-hero">
                    <div class="bridge-summary-row bridge-summary-primary">
                        <span class="detail-hero-icon">${iconMarkup('tunnel')}</span>
                        <div class="bridge-summary-copy">
                            <span class="detail-eyebrow">Source m_tunnels</span>
                            <h3>${escapeHtml(formatValue(record.nama_terowongan || record.nomor_bh || record.tunnel_id || 'Detail Terowongan', 'nama_terowongan'))}</h3>
                            <span class="bridge-summary-route">${escapeHtml([record.nomor_bh, record.km_hm].filter(Boolean).join(' | ') || '-')}</span>
                        </div>
                    </div>
                    <div class="bridge-summary-row bridge-summary-tags">
                        ${summaryTag('ID', record.tunnel_id || '-', '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>')}
                        ${summaryTag('Status Operasi', record.status_operasi || '-', '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M12 3 4 7v5c0 5 3.4 9.4 8 10 4.6-.6 8-5 8-10V7z"/><path d="m9 12 2 2 4-5"/></svg>')}
                        ${summaryTag('Status Aset', record.status_aset || '-', '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 18h16"/><path d="M6 18V9l3-3 3 3 3-3 3 3v9"/></svg>')}
                        ${summaryTag('Panjang', formatValue(record.panjang_m, 'panjang_m'), '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 12h16"/><path d="m7 9-3 3 3 3"/><path d="m17 9 3 3-3 3"/></svg>')}
                        ${summaryTag('Koordinat', coordinateSummary, '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2"/></svg>')}
                    </div>
                </section>

                <div class="detail-stack">
                    ${renderSection('Identitas & Lokasi', 'map', renderKeyValueTable(identityRows))}
                    ${renderSection('Operasional', 'route', renderKeyValueTable(operationRows), record.kondisi_terakhir || null)}
                    ${renderSection('Struktur', 'structure', renderKeyValueTable([
                        ['Jenis Struktur', structure.jenis_struktur, 'jenis_struktur'],
                        ['Material Struktur', structure.material_struktur, 'material_struktur'],
                        ['Material Lining', structure.material_lining, 'material_lining'],
                        ['Material Portal', structure.material_portal, 'material_portal'],
                        ['Material Invert', structure.material_invert, 'material_invert'],
                        ['Metode Konstruksi', structure.metode_konstruksi, 'metode_konstruksi'],
                        ['Waterproofing', structure.waterproofing, 'waterproofing'],
                        ['Rehabilitasi Terakhir', structure.tahun_rehabilitasi_terakhir, 'tahun_rehabilitasi_terakhir'],
                    ]))}
                    ${renderSection('Spesifikasi', 'specs', renderKeyValueTable([
                        ['Jumlah Jalur', specs.jumlah_jalur, 'jumlah_jalur'],
                        ['Jenis Jalur', specs.jenis_jalur, 'jenis_jalur'],
                        ['Gauge', specs.gauge_m, 'gauge_m'],
                        ['Lebar Bersih', specs.lebar_bersih_m, 'lebar_bersih_m'],
                        ['Tinggi Bersih', specs.tinggi_bersih_m, 'tinggi_bersih_m'],
                        ['Clearance Horizontal', specs.clearance_horizontal_mm, 'clearance_horizontal_mm'],
                        ['Clearance Vertikal', specs.clearance_vertikal_mm, 'clearance_vertikal_mm'],
                        ['Bentuk Penampang', specs.bentuk_penampang, 'bentuk_penampang'],
                        ['Gradien', specs.gradien_persen, 'gradien_persen'],
                        ['Radius Lengkung', specs.radius_lengkung_m, 'radius_lengkung_m'],
                        ['Catatan Teknis', specs.catatan_teknis, 'catatan_teknis'],
                    ]))}
                    ${renderSection('Dokumen', 'documents', renderKeyValueTable([
                        ['No. DED/BED/Kajian Teknis', docs.no_ded_bed_kajian_teknis, 'no_ded_bed_kajian_teknis'],
                        ['DED/BED/Kajian Teknis', docs.ded_bed_kajian_teknis, 'ded_bed_kajian_teknis'],
                        ['No. Spesifikasi Teknis', docs.no_spesifikasi_teknis, 'no_spesifikasi_teknis'],
                        ['Spesifikasi Teknis', docs.spesifikasi_teknis, 'spesifikasi_teknis'],
                        ['No. Shop Drawing', docs.no_shop_drawing, 'no_shop_drawing'],
                        ['Shop Drawing', docs.shop_drawing, 'shop_drawing'],
                        ['No. As Built Drawing', docs.no_as_built_drawing, 'no_as_built_drawing'],
                        ['As Built Drawing', docs.as_built_drawing, 'as_built_drawing'],
                        ['No. Dokumen Hasil Uji', docs.no_dok_hasil_uji, 'no_dok_hasil_uji'],
                        ['Dokumen Hasil Uji', docs.dok_hasil_uji, 'dok_hasil_uji'],
                    ]))}
                </div>
            `;
        };

        const loadRecords = async () => {
            setLoadingState(true);
            renderRows([]);
            renderPagination();

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: String(state.perPage),
                sort_by: 'updated_at',
                sort_dir: 'desc',
            });

            if (state.search) {
                params.set('search', state.search);
            }

            try {
                const payload = await fetchJson(`${config.list_endpoint}?${params.toString()}`);
                state.pagination = payload.meta?.pagination || state.pagination;
                state.rows = Array.isArray(payload.data) ? payload.data : [];
                setLoadingState(false);
                renderRows(state.rows);
                renderPagination();
            } catch (errorPayload) {
                setLoadingState(false);
                state.rows = [];
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractErrorMessage(errorPayload))}</td></tr>`;
                renderPagination();
            }
        };

        const openDetail = async (tunnelId) => {
            if (viewContent) {
                viewContent.innerHTML = '<div class="grid-loading">Memuat detail terowongan...</div>';
            }

            if (viewSubtitle) {
                viewSubtitle.textContent = 'Memuat data...';
            }

            openModal(viewModal);

            try {
                const payload = await fetchJson(`${config.list_endpoint}/${encodeURIComponent(tunnelId)}`);
                renderDetail(payload.data || {});
            } catch (errorPayload) {
                if (viewContent) {
                    viewContent.innerHTML = `<div class="feedback">${escapeHtml(extractErrorMessage(errorPayload))}</div>`;
                }
            }
        };

        const deleteRecord = async (tunnelId) => {
            if (!tunnelId || !config.delete_endpoint) {
                return;
            }

            const record = state.rows.find((row) => String(row.tunnel_id) === String(tunnelId));
            const label = record?.nama_terowongan || record?.nomor_bh || tunnelId;

            if (!window.confirm(`Hapus data terowongan ${label}?`)) {
                return;
            }

            setLoadingState(true);

            try {
                await fetchJson(config.delete_endpoint.replace('__tunnel__', encodeURIComponent(tunnelId)), {
                    method: 'DELETE',
                });

                if (state.rows.length === 1 && state.page > 1) {
                    state.page -= 1;
                }

                await loadRecords();
            } catch (errorPayload) {
                setLoadingState(false);
                window.alert(extractErrorMessage(errorPayload));
            }
        };

        renderHeader();
        renderPagination();
        loadRecords();

        gridSearch?.addEventListener('input', (event) => {
            window.clearTimeout(searchTimer);
            state.search = event.target.value.trim();
            searchTimer = window.setTimeout(() => {
                state.page = 1;
                loadRecords();
            }, 280);
        });

        gridPerPage?.addEventListener('change', (event) => {
            state.perPage = Number(event.target.value || 10);
            state.page = 1;
            loadRecords();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadRecords();
        });

        nextButton?.addEventListener('click', () => {
            if (state.page >= Number(state.pagination.last_page || 1)) {
                return;
            }

            state.page += 1;
            loadRecords();
        });

        createButton?.addEventListener('click', openCreateForm);

        importButton?.addEventListener('click', () => {
            importFile?.click();
        });

        importFile?.addEventListener('change', (event) => {
            const file = event.target.files?.[0] || null;
            importCsv(file);
        });

        coordinateButton?.addEventListener('click', openCoordinatePicker);

        coordinateSearchForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            searchCoordinate(coordinateSearchInput?.value || '');
        });

	        coordinateApplyButton?.addEventListener('click', applyCoordinate);

	        formModal?.querySelectorAll('[data-tunnel-nested-open]').forEach((button) => {
	            button.addEventListener('click', () => openNestedModal(button.dataset.tunnelNestedOpen));
	        });

	        nestedForm?.addEventListener('submit', (event) => {
	            event.preventDefault();
	            saveNestedModal();
	        });

	        form?.addEventListener('submit', async (event) => {
	            event.preventDefault();
            clearFormFeedback();

            let payload = {};

	            try {
	                payload = buildPayload();
	            } catch {
	                showFormFeedback('Data detail terowongan belum valid.');
	                return;
	            }

            setLoadingState(true);

            try {
                const endpoint = editingTunnelId && config.update_endpoint
                    ? config.update_endpoint.replace('__tunnel__', encodeURIComponent(editingTunnelId))
                    : config.store_endpoint;
                const requestBody = buildRequestBody(payload);

                await fetchJson(endpoint, {
                    method: requestBody.method,
                    body: requestBody.body,
                });
                closeModal(formModal);
                resetForm();
                state.page = 1;
                await loadRecords();
            } catch (errorPayload) {
                showFormFeedback(extractErrorMessage(errorPayload));
            } finally {
                setLoadingState(false);
            }
        });

        root.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-tunnel-row-action]');

            if (!trigger) {
                return;
            }

            if (trigger.dataset.tunnelRowAction === 'view') {
                openDetail(trigger.dataset.tunnelId);
                return;
            }

            if (trigger.dataset.tunnelRowAction === 'edit') {
                openEditForm(trigger.dataset.tunnelId);
                return;
            }

            if (trigger.dataset.tunnelRowAction === 'delete') {
                deleteRecord(trigger.dataset.tunnelId);
            }
        });

        viewModal?.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(viewModal));
        });

        formModal?.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(formModal));
        });

	        coordinateModal?.querySelectorAll('[data-modal-close]').forEach((button) => {
	            button.addEventListener('click', () => closeModal(coordinateModal));
	        });

	        nestedModal?.querySelectorAll('[data-modal-close]').forEach((button) => {
	            button.addEventListener('click', () => closeModal(nestedModal));
	        });

	        document.addEventListener('keydown', (event) => {
	            if (event.key === 'Escape') {
	                closeModal(viewModal);
	                closeModal(nestedModal);
	                closeModal(coordinateModal);
	                closeModal(formModal);
	            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-bridge-relation-map]');

        if (!root) {
            return;
        }

        const relationMap = JSON.parse(root.dataset.bridgeRelationMap || '[]');
        const graphHost = root.querySelector('[data-relation-graph]');
        const previewTitle = root.querySelector('[data-relation-preview-title]');
        const previewDescription = root.querySelector('[data-relation-preview-description]');
        const previewType = root.querySelector('[data-relation-preview-type]');
        const previewKey = root.querySelector('[data-relation-preview-key]');
        const previewTarget = root.querySelector('[data-relation-preview-target]');
        const updatePreview = (relation) => {
            if (previewTitle) {
                previewTitle.textContent = relation?.table || '-';
            }

            if (previewDescription) {
                previewDescription.textContent = relation?.relation || '-';
            }

            if (previewType) {
                previewType.textContent = String(relation?.type || '-').replaceAll('_', ' ').toUpperCase();
            }

            if (previewKey) {
                previewKey.textContent = relation?.key || '-';
            }

            if (previewTarget) {
                previewTarget.textContent = relation?.target || 'Tabel utama CRUD source';
            }
        };

        const initializeGraph = () => {
            if (!root.open || root.dataset.graphReady === 'true' || !graphHost) {
                return;
            }

            const cytoscapeFactory = window.cytoscape;

            if (typeof cytoscapeFactory !== 'function') {
                graphHost.innerHTML = '<div class="relation-graph-empty"><p>Library Cytoscape tidak berhasil dimuat.</p></div>';
                return;
            }

            const elements = relationMap.flatMap((relation) => {
                const nodeId = relation.table;
                const items = [{
                    data: {
                        id: nodeId,
                        label: relation.table,
                        type: relation.type,
                        relation: relation.relation,
                        key: relation.key,
                        target: relation.target || 'Tabel utama CRUD source',
                    },
                }];

                if (relation.type !== 'root') {
                    items.push({
                        data: {
                            id: `${relation.target || 'm_jembatan'}-${nodeId}`,
                            source: 'm_jembatan',
                            target: nodeId,
                            label: relation.key,
                        },
                    });
                }

                return items;
            });

            const cy = cytoscapeFactory({
                container: graphHost,
                elements,
                layout: {
                    name: 'breadthfirst',
                    directed: true,
                    padding: 28,
                    spacingFactor: 1.15,
                },
                style: [
                    {
                        selector: 'node',
                        style: {
                            'background-color': '#475569',
                            'border-width': 2,
                            'border-color': '#ffffff',
                            'label': 'data(label)',
                            'text-wrap': 'wrap',
                            'text-max-width': 130,
                            'text-valign': 'center',
                            'text-halign': 'center',
                            'color': '#172033',
                            'font-size': 11,
                            'font-weight': 700,
                            'width': 'label',
                            'height': 56,
                            'padding': '12px',
                        },
                    },
                    {
                        selector: 'node[type = "root"]',
                        style: {
                            'background-color': '#f18120',
                            'shape': 'round-rectangle',
                        },
                    },
                    {
                        selector: 'node[type = "one_to_one"]',
                        style: {
                            'background-color': '#93c5fd',
                            'shape': 'round-rectangle',
                        },
                    },
                    {
                        selector: 'node[type = "one_to_many"]',
                        style: {
                            'background-color': '#99f6e4',
                            'shape': 'round-rectangle',
                        },
                    },
                    {
                        selector: 'node[type = "lookup"]',
                        style: {
                            'background-color': '#fde68a',
                            'shape': 'ellipse',
                        },
                    },
                    {
                        selector: 'edge',
                        style: {
                            'curve-style': 'bezier',
                            'target-arrow-shape': 'triangle',
                            'target-arrow-color': '#cbd5e1',
                            'line-color': '#cbd5e1',
                            'width': 2,
                            'label': 'data(label)',
                            'font-size': 9,
                            'text-background-color': '#ffffff',
                            'text-background-opacity': 1,
                            'text-background-padding': 2,
                            'color': '#64748b',
                        },
                    },
                    {
                        selector: ':selected',
                        style: {
                            'border-color': '#d14d1f',
                            'border-width': 3,
                            'line-color': '#d14d1f',
                            'target-arrow-color': '#d14d1f',
                        },
                    },
                ],
            });

            cy.on('tap', 'node', (event) => {
                updatePreview(event.target.data());
            });

            const rootNode = cy.getElementById('m_jembatan');
            if (rootNode) {
                rootNode.select();
            }

            updatePreview(relationMap.find((item) => item.type === 'root') || relationMap[0] || null);
            root.dataset.graphReady = 'true';
        };

        root.addEventListener('toggle', initializeGraph);
        updatePreview(relationMap.find((item) => item.type === 'root') || relationMap[0] || null);
    })();

    (() => {
        const root = document.querySelector('[data-bridge-source-app]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const body = document.body;

        if (!root) {
            return;
        }

        const config = JSON.parse(root.dataset.bridgeSourceApp || '{}');
        const crudEnabled = Boolean(config.crud_enabled);
        const hiddenBridgeTableFields = new Set(['created_at', 'updated_at', 'created_by']);
        const isHiddenBridgeTableField = (key = '') => hiddenBridgeTableFields.has(String(key || '').toLowerCase());
        const columns = (Array.isArray(config.columns) && config.columns.length > 0
            ? config.columns
            : ['uniqid', 'no_bh', 'jenis', 'km_hm'])
            .filter((column) => !isHiddenBridgeTableField(column));
        const columnLabels = {
            bridge_identity: 'Identitas Jembatan',
            route_summary: 'Rute dan Stasiun',
            wilayah_summary: 'Wilayah',
            location_summary: 'Lokasi',
            structure_summary: 'Struktur Gabungan',
            assessment_summary: 'Asesmen',
        };

        const gridHead = root.querySelector('[data-grid-head]');
        const gridBody = root.querySelector('[data-grid-body]');
        const gridSearch = root.querySelector('[data-grid-search]');
        const gridPerPage = root.querySelector('[data-grid-per-page]');
        const gridCount = root.querySelector('[data-grid-count]');
        const gridSummary = root.querySelector('[data-grid-summary]');
        const gridPage = root.querySelector('[data-grid-page]');
        const prevButton = root.querySelector('[data-grid-prev]');
        const nextButton = root.querySelector('[data-grid-next]');
        const createButton = root.querySelector('[data-grid-create]');
        const viewModal = document.querySelector('[data-bridge-view-modal]');
        const viewSubtitle = viewModal?.querySelector('[data-bridge-view-subtitle]');
        const viewContent = viewModal?.querySelector('[data-bridge-view-content]');
        const formModal = document.querySelector('[data-bridge-form-modal]');
        const formTitle = formModal?.querySelector('[data-bridge-form-title]');
        const formSubtitle = formModal?.querySelector('[data-bridge-form-subtitle]');
        const form = formModal?.querySelector('[data-bridge-source-form]');
        const formFeedback = formModal?.querySelector('[data-bridge-form-feedback]');
        const submitButton = formModal?.querySelector('[data-bridge-form-submit]');

        const state = {
            page: 1,
            perPage: Number(gridPerPage?.value || 10),
            search: '',
            loading: false,
            mode: 'create',
            activeUniqid: null,
            pagination: {
                current_page: 1,
                last_page: 1,
                total: Number(config.records_count || 0),
                per_page: Number(gridPerPage?.value || 10),
            },
        };

        let searchTimer = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const prettifyField = (field) => columnLabels[field]
            || field.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

        const formatText = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (typeof value === 'object') {
                return JSON.stringify(value);
            }

            return String(value);
        };

        const formatDate = (value) => {
            if (!value) {
                return '-';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return String(value);
            }

            return new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short',
                timeZone: 'UTC',
            }).format(date);
        };

        const formatAssessmentConclusion = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const normalized = Number.parseInt(String(value), 10);
            const labels = {
                1: 'Baik',
                2: 'Sedang',
                3: 'Rusak Ringan',
                4: 'Rusak Berat',
            };

            if (Number.isNaN(normalized)) {
                return String(value);
            }

            return labels[normalized] ? `${labels[normalized]} (${normalized})` : String(normalized);
        };

        const iconMarkup = (name) => {
            const icons = {
                bridge: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18h18"/><path d="M5 18v-4a7 7 0 0 1 14 0v4"/><path d="M8 18v-4"/><path d="M12 18v-6"/><path d="M16 18v-4"/><path d="M3 10h18"/></svg>',
                map: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 6 6-2 6 2 6-2v14l-6 2-6-2-6 2z"/><path d="M9 4v14"/><path d="M15 6v14"/></svg>',
                route: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="18" r="2"/><circle cx="18" cy="6" r="2"/><path d="M8 18h4a6 6 0 0 0 6-6V8"/></svg>',
                profile: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 18h16"/><path d="M6 18V8l4-2 4 2 4-2v12"/><path d="M10 6v12"/><path d="M14 8v10"/></svg>',
                span: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18h18"/><path d="M5 18c1.5-5 4-8 7-8s5.5 3 7 8"/><path d="M9 12h6"/></svg>',
                substructure: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16"/><path d="M6 20v-6h4v6"/><path d="M14 20v-10h4v10"/><path d="M4 10h16"/></svg>',
                shield: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 5 6v6c0 4.5 2.8 7.7 7 9 4.2-1.3 7-4.5 7-9V6z"/><path d="M9.5 12.5 11 14l3.5-3.5"/></svg>',
                assessment: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-4"/></svg>',
                media: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 15-4.5-4.5L7 19"/></svg>',
                database: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v14c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3"/></svg>',
            };

            return icons[name] || icons.database;
        };

        const formatLookup = (label, code) => {
            if (label && code && String(label) !== String(code)) {
                return `${label} (${code})`;
            }

            return label || code || '-';
        };

        const compactNumber = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const number = Number(value);

            if (Number.isNaN(number)) {
                return String(value);
            }

            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 2,
            }).format(number);
        };

        const formatDetailValue = (value, key = '') => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (Array.isArray(value)) {
                return value.length ? value.map((item) => formatDetailValue(item, key)).join(', ') : '-';
            }

            if (typeof value === 'object') {
                return Object.keys(value).length ? JSON.stringify(value) : '-';
            }

            if (key.endsWith('_at') || key === 'tanggal') {
                return formatDate(value);
            }

            if (key === 'kesimpulan') {
                return formatAssessmentConclusion(value);
            }

            return String(value);
        };

        const buildRows = (entries) => entries.filter(([, value, key]) => value !== undefined && !isHiddenBridgeTableField(key));

        const renderKeyValueTable = (entries) => {
            const rows = buildRows(entries);

            if (!rows.length) {
                return '<div class="detail-empty">Belum ada data yang tersimpan pada bagian ini.</div>';
            }

            const tableHtml = `
                <div class="detail-table-wrap">
                    <table class="detail-kv-table">
                        <tbody>
                            ${rows.map(([label, value, key]) => `
                                <tr>
                                    <th>${escapeHtml(label)}</th>
                                    <td>${escapeHtml(formatDetailValue(value, key || ''))}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            return window.dashboardDetailMap?.wrapEntries(rows, tableHtml) || tableHtml;
        };

        const renderRecordTable = (rows, preferredOrder = []) => {
            if (!Array.isArray(rows) || !rows.length) {
                return '<div class="detail-empty">Belum ada baris data pada tabel relasi ini.</div>';
            }

            const allColumns = Array.from(new Set(rows.flatMap((row) => Object.keys(row || {}))))
                .filter((column) => !isHiddenBridgeTableField(column));
            const orderedColumns = [
                ...preferredOrder.filter((column) => allColumns.includes(column)),
                ...allColumns.filter((column) => !preferredOrder.includes(column)),
            ];

            const tableHtml = `
                <div class="detail-table-wrap">
                    <table class="detail-record-table">
                        <thead>
                            <tr>
                                ${orderedColumns.map((column) => `<th>${escapeHtml(prettifyField(column))}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.map((row) => `
                                <tr>
                                    ${orderedColumns.map((column) => `<td>${escapeHtml(formatDetailValue(row[column], column))}</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            return window.dashboardDetailMap?.wrapRows(rows, tableHtml) || tableHtml;
        };

        const renderSection = (title, iconName, body, chip = null) => `
            <section class="detail-section">
                <div class="detail-section-head">
                    <div class="detail-section-title">
                        <span class="detail-section-icon">${iconMarkup(iconName)}</span>
                        <div>
                            <h4>${escapeHtml(title)}</h4>
                        </div>
                    </div>
                    ${chip ? `<span class="detail-chip"><svg class="tag-icon" viewBox="0 0 24 24"><path d="M20 10 14 4H5v9l6 6z"/><path d="M8 8h.01"/></svg><span class="tag-label">Info</span><span class="tag-value">${escapeHtml(chip)}</span></span>` : ''}
                </div>
                ${body}
            </section>
        `;

        const fetchJson = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    ...(options.headers || {}),
                },
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || (payload && payload.success === false)) {
                throw payload || { message: 'Permintaan gagal.' };
            }

            return payload;
        };

        const extractErrorMessage = (payload) => {
            if (!payload) {
                return 'Terjadi kesalahan saat memproses data jembatan source.';
            }

            if (payload.error?.details && typeof payload.error.details === 'object') {
                return Object.values(payload.error.details).flat().join('\n');
            }

            if (payload.errors && typeof payload.errors === 'object') {
                return Object.values(payload.errors).flat().join('\n');
            }

            return payload.message || 'Terjadi kesalahan saat memproses data jembatan source.';
        };

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const closeAllModals = () => {
            closeModal(viewModal);
            closeModal(formModal);
        };

        const renderHeader = () => {
            gridHead.innerHTML = `
                <tr>
                    ${columns.map((column) => `<th>${escapeHtml(prettifyField(column))}</th>`).join('')}
                    <th>Aksi</th>
                </tr>
            `;
        };

        const renderRows = (rows) => {
            const totalColumns = columns.length + 1;

            if (state.loading) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-loading">Memuat data...</td></tr>`;
                return;
            }

            if (!rows.length) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-empty">Belum ada data jembatan source.</td></tr>`;
                return;
            }

            gridBody.innerHTML = rows.map((row) => {
                const cells = columns.map((column, index) => {
                    const value = row[column] ?? null;

                    if (column.endsWith('_at')) {
                        return `<td>${escapeHtml(formatDate(value))}</td>`;
                    }

                    if (index === 0) {
                        return `
                            <td>
                                <div class="row-title">
                                    <strong>${escapeHtml(formatText(value))}</strong>
                                    <span>${escapeHtml(formatText([row.uniqid, row.location_summary].filter(Boolean).join(' | ') || '-'))}</span>
                                </div>
                            </td>
                        `;
                    }

                    return `<td>${escapeHtml(formatText(value))}</td>`;
                }).join('');

                return `
                    <tr>
                        ${cells}
                        <td class="bridge-actions-cell">
                            <div class="inline-actions bridge-row-actions">
                                <button class="inline-button" type="button" data-bridge-row-action="view" data-uniqid="${escapeHtml(row.uniqid)}">Lihat</button>
                                ${crudEnabled ? `<button class="inline-button primary" type="button" data-bridge-row-action="edit" data-uniqid="${escapeHtml(row.uniqid)}">Edit</button>` : ''}
                                ${crudEnabled ? `<button class="inline-button danger" type="button" data-bridge-row-action="delete" data-uniqid="${escapeHtml(row.uniqid)}">Hapus</button>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const renderPagination = () => {
            const total = Number(state.pagination.total || 0);
            const currentPage = Number(state.pagination.current_page || 1);
            const lastPage = Number(state.pagination.last_page || 1);
            const perPage = Number(state.pagination.per_page || state.perPage || 10);
            const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            if (gridCount) {
                window.dashboardFillStaticTag(gridCount, 'Data', new Intl.NumberFormat('id-ID').format(total), 'data');
            }

            if (gridSummary) {
                gridSummary.textContent = total === 0
                    ? 'Belum ada data.'
                    : `Menampilkan ${new Intl.NumberFormat('id-ID').format(start)}-${new Intl.NumberFormat('id-ID').format(end)} dari ${new Intl.NumberFormat('id-ID').format(total)} data`;
            }

            if (gridPage) {
                gridPage.textContent = `Halaman ${currentPage} / ${lastPage}`;
            }

            if (prevButton) {
                prevButton.disabled = currentPage <= 1 || state.loading;
            }

            if (nextButton) {
                nextButton.disabled = currentPage >= lastPage || state.loading;
            }
        };

        const setLoadingState = (loading) => {
            state.loading = loading;

            if (submitButton) {
                submitButton.disabled = loading;
            }
        };

        const showFormFeedback = (message) => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = false;
            formFeedback.textContent = message;
        };

        const clearFormFeedback = () => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = true;
            formFeedback.textContent = '';
        };

        const safeJson = (value, fallback) => {
            try {
                return JSON.stringify(value ?? fallback, null, 2);
            } catch {
                return JSON.stringify(fallback, null, 2);
            }
        };

        const getField = (name) => form?.querySelector(`[name="${name}"]`);

        const setFieldValue = (name, value) => {
            const field = getField(name);

            if (!field) {
                return;
            }

            field.value = value ?? '';
        };

        const parseJsonField = (name, fallback) => {
            const field = getField(name);
            const raw = field?.value?.trim() ?? '';

            if (raw === '') {
                return fallback;
            }

            return JSON.parse(raw);
        };

        const toNullable = (value) => {
            const trimmed = String(value ?? '').trim();

            return trimmed === '' ? null : trimmed;
        };

        const toNullableInteger = (value) => {
            const normalized = toNullable(value);

            return normalized === null ? null : Number.parseInt(normalized, 10);
        };

        const toNullableFloat = (value) => {
            const normalized = toNullable(value);

            return normalized === null ? null : Number.parseFloat(normalized);
        };

        const resetForm = () => {
            if (!form) {
                return;
            }

            form.reset();
            state.mode = 'create';
            state.activeUniqid = null;
            setFieldValue('active', '1');
            setFieldValue('status', '1');
            setFieldValue('statusdata', '0');
            setFieldValue('profile_span_json', '{"pjg_bentang1":"","pjg_bentang2":"","pjg_bentang3":""}');
            setFieldValue('spans_json', '[]');
            setFieldValue('substructures_json', '[]');

            if (formTitle) {
                formTitle.textContent = `Tambah ${config.label || 'Jembatan'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Isi data source utama dan relasinya.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan';
            }

            clearFormFeedback();
        };

        const fillForm = (record) => {
            [
                'tanggal', 'no_bh', 'jenis', 'km_hm', 'arah_bh', 'nama', 'wil_ker', 'wil_op',
                'id_prov', 'id_kabkot', 'lintas', 'stasiun1', 'stasiun2', 'lat', 'lon',
                'active', 'status', 'statusdata', 'catatan',
            ].forEach((field) => setFieldValue(field, record[field]));

            setFieldValue('profile.perpotongan', record.profile?.perpotongan);
            setFieldValue('profile.jml_lintasan', record.profile?.jml_lintasan);
            setFieldValue('profile.jml_bentang', record.profile?.jml_bentang);
            setFieldValue('profile.pjg_total', record.profile?.pjg_total);
            setFieldValue('profile.thn_selesai', record.profile?.thn_selesai);
            setFieldValue('profile.rm_bgn_atas', record.profile?.rm_bgn_atas);
            setFieldValue('profile.rm_bgn_bawah', record.profile?.rm_bgn_bawah);
            setFieldValue('profile_span_json', safeJson({
                pjg_bentang1: record.profile?.pjg_bentang1 ?? '',
                pjg_bentang2: record.profile?.pjg_bentang2 ?? '',
                pjg_bentang3: record.profile?.pjg_bentang3 ?? '',
            }, { pjg_bentang1: '', pjg_bentang2: '', pjg_bentang3: '' }));
            setFieldValue('spans_json', safeJson(record.spans || [], []));
            setFieldValue('substructures_json', safeJson(record.substructures || [], []));
            setFieldValue('protection.pelindung_arus_material', record.protection?.pelindung_arus_material);
            setFieldValue('protection.pelindung_arus_tipe', record.protection?.pelindung_arus_tipe);
            setFieldValue('protection.pengarah_arus_material', record.protection?.pengarah_arus_material);
            setFieldValue('protection.pengarah_arus_tipe', record.protection?.pengarah_arus_tipe);
            setFieldValue('protection.pelindung_longsoran_material', record.protection?.pelindung_longsoran_material);
            setFieldValue('protection.pelindung_longsoran_tipe', record.protection?.pelindung_longsoran_tipe);
            setFieldValue('assessment.total', record.assessment?.total);
            setFieldValue('assessment.kesimpulan', record.assessment?.kesimpulan);
        };

        const buildPayload = () => {
            const profileSpan = parseJsonField('profile_span_json', {});
            const spans = parseJsonField('spans_json', []);
            const substructures = parseJsonField('substructures_json', []);

            return {
                tanggal: toNullable(getField('tanggal')?.value),
                no_bh: toNullable(getField('no_bh')?.value),
                jenis: toNullable(getField('jenis')?.value),
                km_hm: toNullable(getField('km_hm')?.value),
                arah_bh: toNullable(getField('arah_bh')?.value),
                nama: toNullable(getField('nama')?.value),
                wil_ker: toNullable(getField('wil_ker')?.value),
                wil_op: toNullable(getField('wil_op')?.value),
                id_prov: toNullable(getField('id_prov')?.value),
                id_kabkot: toNullable(getField('id_kabkot')?.value),
                lintas: toNullable(getField('lintas')?.value),
                stasiun1: toNullable(getField('stasiun1')?.value),
                stasiun2: toNullable(getField('stasiun2')?.value),
                lat: toNullable(getField('lat')?.value),
                lon: toNullable(getField('lon')?.value),
                active: Number.parseInt(getField('active')?.value || '1', 10),
                status: Number.parseInt(getField('status')?.value || '1', 10),
                statusdata: Number.parseInt(getField('statusdata')?.value || '0', 10),
                catatan: toNullable(getField('catatan')?.value),
                profile: {
                    perpotongan: toNullable(getField('profile.perpotongan')?.value),
                    jml_lintasan: toNullableInteger(getField('profile.jml_lintasan')?.value),
                    jml_bentang: toNullableInteger(getField('profile.jml_bentang')?.value),
                    pjg_bentang1: toNullable(profileSpan.pjg_bentang1),
                    pjg_bentang2: toNullable(profileSpan.pjg_bentang2),
                    pjg_bentang3: toNullable(profileSpan.pjg_bentang3),
                    pjg_total: toNullable(getField('profile.pjg_total')?.value),
                    thn_selesai: toNullable(getField('profile.thn_selesai')?.value),
                    rm_bgn_atas: toNullable(getField('profile.rm_bgn_atas')?.value),
                    rm_bgn_bawah: toNullable(getField('profile.rm_bgn_bawah')?.value),
                    active: 1,
                },
                spans: Array.isArray(spans) ? spans : [],
                substructures: Array.isArray(substructures) ? substructures : [],
                protection: {
                    pelindung_arus_material: toNullable(getField('protection.pelindung_arus_material')?.value),
                    pelindung_arus_tipe: toNullable(getField('protection.pelindung_arus_tipe')?.value),
                    pengarah_arus_material: toNullable(getField('protection.pengarah_arus_material')?.value),
                    pengarah_arus_tipe: toNullable(getField('protection.pengarah_arus_tipe')?.value),
                    pelindung_longsoran_material: toNullable(getField('protection.pelindung_longsoran_material')?.value),
                    pelindung_longsoran_tipe: toNullable(getField('protection.pelindung_longsoran_tipe')?.value),
                },
                assessment: {
                    total: toNullableFloat(getField('assessment.total')?.value),
                    kesimpulan: toNullableInteger(getField('assessment.kesimpulan')?.value),
                },
            };
        };

        const fetchRecord = async (uniqid) => {
            const payload = await fetchJson(`${config.list_endpoint}/${encodeURIComponent(uniqid)}`);
            return payload.data || null;
        };

        const renderDetail = (record) => {
            if (!viewContent || !viewSubtitle) {
                return;
            }

            viewSubtitle.textContent = `${record.uniqid || '-'} · ${record.no_bh || 'tanpa nomor'}`;

            const profile = record.profile || {};
            const spans = Array.isArray(record.spans) ? record.spans : [];
            const substructures = Array.isArray(record.substructures) ? record.substructures : [];
            const protection = record.protection || {};
            const assessment = record.assessment || {};
            const totalSpanLength = spans.reduce((sum, span) => {
                const number = Number(span.pjg_bentang || 0);

                return Number.isNaN(number) ? sum : sum + number;
            }, 0);

            const relationKeys = new Set([
                'profile', 'spans', 'substructures', 'protection', 'assessment', 'relations',
                'bridge_identity', 'location_summary', 'route_summary', 'wilayah_summary',
                'profile_summary', 'span_summary', 'substructure_summary', 'protection_summary',
                'assessment_summary', 'structure_summary',
            ]);
            const curatedSourceKeys = new Set([
                'uniqid', 'no_bh', 'nama', 'jenis', 'tanggal', 'km_hm', 'arah_bh', 'lat', 'lon',
                'wil_ker', 'wil_ker_name', 'wil_op', 'wil_op_name', 'id_prov', 'province_name',
                'id_kabkot', 'city_name', 'lintas', 'lintas_name', 'stasiun1', 'stasiun1_name',
                'stasiun2', 'stasiun2_name', 'catatan', 'foto1', 'foto2', 'foto3', 'foto4',
                'caption1', 'caption2', 'caption3', 'caption4', 'dokumen', 'video',
                'created_at', 'updated_at', 'created_by',
            ]);
            const extraSourceFields = Object.entries(record).filter(([key, value]) => {
                if (relationKeys.has(key) || curatedSourceKeys.has(key)) {
                    return false;
                }

                return typeof value !== 'object' || value === null;
            });

            const identityRows = [
                ['Uniqid', record.uniqid, 'uniqid'],
                ['No. Jembatan', record.no_bh, 'no_bh'],
                ['Nama', record.nama, 'nama'],
                ['Jenis', record.jenis, 'jenis'],
                ['Tanggal', record.tanggal, 'tanggal'],
                ['KM/HM', record.km_hm, 'km_hm'],
                ['Arah Jembatan', record.arah_bh, 'arah_bh'],
                ['Koordinat', [record.lat, record.lon].filter(Boolean).join(', '), 'lat'],
            ];

            const routeRows = [
                ['Wilayah Kerja', formatLookup(record.wil_ker_name, record.wil_ker), 'wil_ker'],
                ['Wilayah Operasi', formatLookup(record.wil_op_name, record.wil_op), 'wil_op'],
                ['Provinsi', formatLookup(record.province_name, record.id_prov), 'id_prov'],
                ['Kabupaten/Kota', formatLookup(record.city_name, record.id_kabkot), 'id_kabkot'],
                ['Lintas', formatLookup(record.lintas_name, record.lintas), 'lintas'],
                ['Stasiun Awal', formatLookup(record.stasiun1_name, record.stasiun1), 'stasiun1'],
                ['Stasiun Akhir', formatLookup(record.stasiun2_name, record.stasiun2), 'stasiun2'],
                ['Ringkasan Rute', record.route_summary, 'route_summary'],
            ];

            const profileRows = [
                ['Uniqid Profil', profile.uniqid, 'uniqid'],
                ['ID Jembatan', profile.id_jembatan, 'id_jembatan'],
                ['Perpotongan', profile.perpotongan, 'perpotongan'],
                ['Jumlah Lintasan', profile.jml_lintasan, 'jml_lintasan'],
                ['Jumlah Bentang', profile.jml_bentang, 'jml_bentang'],
                ['Panjang Bentang 1', profile.pjg_bentang1, 'pjg_bentang1'],
                ['Panjang Bentang 2', profile.pjg_bentang2, 'pjg_bentang2'],
                ['Panjang Bentang 3', profile.pjg_bentang3, 'pjg_bentang3'],
                ['Panjang Total', profile.pjg_total, 'pjg_total'],
                ['Tahun Selesai', profile.thn_selesai, 'thn_selesai'],
                ['RM Bangunan Atas', profile.rm_bgn_atas, 'rm_bgn_atas'],
                ['RM Bangunan Bawah', profile.rm_bgn_bawah, 'rm_bgn_bawah'],
                ['Active', profile.active, 'active'],
                ['Created By', profile.created_by, 'created_by'],
                ['Created At', profile.created_at, 'created_at'],
                ['Updated By', profile.updated_by, 'updated_by'],
                ['Updated At', profile.updated_at, 'updated_at'],
            ];

            const protectionRows = [
                ['Uniqid Proteksi', protection.uniqid, 'uniqid'],
                ['ID Jembatan', protection.id_jembatan, 'id_jembatan'],
                ['Pelindung Arus Material', protection.pelindung_arus_material, 'pelindung_arus_material'],
                ['Pelindung Arus Tipe', protection.pelindung_arus_tipe, 'pelindung_arus_tipe'],
                ['Pengarah Arus Material', protection.pengarah_arus_material, 'pengarah_arus_material'],
                ['Pengarah Arus Tipe', protection.pengarah_arus_tipe, 'pengarah_arus_tipe'],
                ['Pelindung Longsoran Material', protection.pelindung_longsoran_material, 'pelindung_longsoran_material'],
                ['Pelindung Longsoran Tipe', protection.pelindung_longsoran_tipe, 'pelindung_longsoran_tipe'],
                ['Created By', protection.created_by, 'created_by'],
                ['Created At', protection.created_at, 'created_at'],
                ['Updated By', protection.updated_by, 'updated_by'],
                ['Updated At', protection.updated_at, 'updated_at'],
            ];

            const assessmentRows = [
                ['Uniqid Asesmen', assessment.uniqid, 'uniqid'],
                ['ID Jembatan', assessment.id_jembatan, 'id_jembatan'],
                ['Nilai Total', assessment.total, 'total'],
                ['Kesimpulan', assessment.kesimpulan, 'kesimpulan'],
                ['Created By', assessment.created_by, 'created_by'],
                ['Created At', assessment.created_at, 'created_at'],
                ['Updated By', assessment.updated_by, 'updated_by'],
                ['Updated At', assessment.updated_at, 'updated_at'],
            ];

            const mediaRows = [
                ['Foto 1', record.foto1, 'foto1'],
                ['Caption 1', record.caption1, 'caption1'],
                ['Foto 2', record.foto2, 'foto2'],
                ['Caption 2', record.caption2, 'caption2'],
                ['Foto 3', record.foto3, 'foto3'],
                ['Caption 3', record.caption3, 'caption3'],
                ['Foto 4', record.foto4, 'foto4'],
                ['Caption 4', record.caption4, 'caption4'],
                ['Dokumen', record.dokumen, 'dokumen'],
                ['Video', record.video, 'video'],
                ['Catatan', record.catatan, 'catatan'],
            ];

            const summaryTag = (label, value, icon) => `
                <span class="bridge-summary-tag">
                    <span class="tag-icon-circle">${icon}</span>
                    <span class="tag-label">${escapeHtml(label)}</span>
                    <span class="tag-value">${escapeHtml(value)}</span>
                </span>
            `;

            viewContent.innerHTML = `
                <section class="detail-hero bridge-summary-hero">
                    <div class="bridge-summary-row bridge-summary-primary">
                        <span class="detail-hero-icon">${iconMarkup('bridge')}</span>
                        <div class="bridge-summary-copy">
                            <span class="detail-eyebrow">Source m_jembatan</span>
                            <h3>${escapeHtml(record.bridge_identity || record.no_bh || record.uniqid || 'Detail Jembatan')}</h3>
                            <span class="bridge-summary-route">${escapeHtml(record.route_summary || '-')}</span>
                        </div>
                    </div>
                    <div class="bridge-summary-row bridge-summary-tags">
                        ${summaryTag('Wilayah', record.wilayah_summary || 'Belum tersedia', '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2"/></svg>')}
                        ${summaryTag('Lokasi', record.location_summary || 'Belum tersedia', '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 18h16"/><path d="M6 18V9l3-3 3 3 3-3 3 3v9"/></svg>')}
                        ${summaryTag('Tabel', '6 source', '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 10h16"/><path d="M9 5v14"/></svg>')}
                        ${summaryTag('Bentang', `${spans.length} data`, '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 18h16"/><path d="M7 18V8l5-3 5 3v10"/><path d="M9 12h6"/></svg>')}
                        ${summaryTag('Struktur Bawah', `${substructures.length} data`, '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 20h16"/><path d="M8 20V10"/><path d="M16 20V10"/><path d="M6 10h12"/><path d="M12 4v16"/></svg>')}
                        ${summaryTag('Panjang Total', profile.pjg_total || (totalSpanLength > 0 ? compactNumber(totalSpanLength) : '-'), '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 12h16"/><path d="m7 9-3 3 3 3"/><path d="m17 9 3 3-3 3"/></svg>')}
                        ${summaryTag('Nilai Asesmen', assessment.total !== undefined && assessment.total !== null ? compactNumber(assessment.total) : '-', '<svg class="tag-icon" viewBox="0 0 24 24"><path d="M12 3 4 7v5c0 5 3.4 9.4 8 10 4.6-.6 8-5 8-10V7z"/><path d="m9 12 2 2 4-5"/></svg>')}
                    </div>
                </section>

                <div class="detail-stack">
                    ${renderSection('Identitas & Lokasi', 'map', renderKeyValueTable(identityRows))}
                    ${renderSection('Kewilayahan & Rute', 'route', renderKeyValueTable(routeRows))}
                    ${renderSection('Profil Struktur', 'profile', renderKeyValueTable(profileRows), record.profile_summary || 'profil')}
                    ${renderSection('Bentang', 'span', renderRecordTable(spans, ['urut', 'pjg_bentang', 'uniqid', 'id_jembatan', 'active']), `${spans.length} baris`)}
                    ${renderSection('Struktur Bawah', 'substructure', renderRecordTable(substructures, ['urut', 'nomor', 'material', 'tipe', 'manteling', 'jenis', 'uniqid', 'id_jembatan']), `${substructures.length} baris`)}
                    ${renderSection('Pelindung', 'shield', renderKeyValueTable(protectionRows))}
                    ${renderSection('Asesmen Total', 'assessment', renderKeyValueTable(assessmentRows), record.assessment_summary || 'nilai')}
                    ${renderSection('Media & Catatan', 'media', renderKeyValueTable(mediaRows))}
                    ${renderSection('Atribut Source Tambahan', 'database', renderKeyValueTable(extraSourceFields.map(([key, value]) => [prettifyField(key), value, key])))}
                </div>
            `;
        };

        const loadRecords = async () => {
            setLoadingState(true);
            renderRows([]);
            renderPagination();

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: String(state.perPage),
            });

            if (state.search) {
                params.set('search', state.search);
            }

            try {
                const payload = await fetchJson(`${config.list_endpoint}?${params.toString()}`);
                state.pagination = payload.meta?.pagination || state.pagination;
                setLoadingState(false);
                renderRows(Array.isArray(payload.data) ? payload.data : []);
                renderPagination();
            } catch (errorPayload) {
                setLoadingState(false);
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractErrorMessage(errorPayload))}</td></tr>`;
                renderPagination();
            }
        };

        const openCreateForm = () => {
            resetForm();
            openModal(formModal);
        };

        const openEditForm = async (uniqid) => {
            resetForm();
            state.mode = 'edit';
            state.activeUniqid = uniqid;

            if (formTitle) {
                formTitle.textContent = `Edit ${config.label || 'Jembatan'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Perbarui record source utama beserta relasinya.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan Perubahan';
            }

            openModal(formModal);
            showFormFeedback('Memuat data...');

            try {
                const record = await fetchRecord(uniqid);
                fillForm(record);
                clearFormFeedback();
            } catch (errorPayload) {
                showFormFeedback(extractErrorMessage(errorPayload));
            }
        };

        const openDetailModal = async (uniqid) => {
            if (viewContent) {
                viewContent.innerHTML = '<div class="grid-loading">Memuat detail...</div>';
            }

            if (viewSubtitle) {
                viewSubtitle.textContent = 'Memuat data...';
            }

            openModal(viewModal);

            try {
                const record = await fetchRecord(uniqid);
                renderDetail(record);
            } catch (errorPayload) {
                if (viewContent) {
                    viewContent.innerHTML = `<div class="feedback">${escapeHtml(extractErrorMessage(errorPayload))}</div>`;
                }
            }
        };

        const deleteRecord = async (uniqid) => {
            const confirmed = window.confirm(`Hapus data jembatan source ${uniqid}?`);

            if (!confirmed) {
                return;
            }

            try {
                await fetchJson(`${config.list_endpoint}/${encodeURIComponent(uniqid)}`, {
                    method: 'DELETE',
                });
                if (state.page > 1 && Number(state.pagination.total || 0) <= 1) {
                    state.page -= 1;
                }
                loadRecords();
            } catch (errorPayload) {
                window.alert(extractErrorMessage(errorPayload));
            }
        };

        renderHeader();
        renderPagination();
        loadRecords();

        gridSearch?.addEventListener('input', (event) => {
            window.clearTimeout(searchTimer);
            state.search = event.target.value.trim();
            searchTimer = window.setTimeout(() => {
                state.page = 1;
                loadRecords();
            }, 280);
        });

        gridPerPage?.addEventListener('change', (event) => {
            state.perPage = Number(event.target.value || 10);
            state.page = 1;
            loadRecords();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadRecords();
        });

        nextButton?.addEventListener('click', () => {
            if (state.page >= Number(state.pagination.last_page || 1)) {
                return;
            }

            state.page += 1;
            loadRecords();
        });

        createButton?.addEventListener('click', openCreateForm);

        root.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-bridge-row-action]');

            if (!trigger) {
                return;
            }

            const { bridgeRowAction, uniqid } = trigger.dataset;

            if (bridgeRowAction === 'view') {
                openDetailModal(uniqid);
            }

            if (bridgeRowAction === 'edit') {
                openEditForm(uniqid);
            }

            if (bridgeRowAction === 'delete') {
                deleteRecord(uniqid);
            }
        });

        document.querySelectorAll('[data-bridge-view-modal] [data-modal-close], [data-bridge-form-modal] [data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeAllModals();
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFormFeedback();

            let payload = null;

            try {
                payload = buildPayload();
            } catch (error) {
                showFormFeedback('JSON pada bagian bentang atau struktur bawah tidak valid.');
                return;
            }

            const url = state.mode === 'edit' && state.activeUniqid
                ? `${config.list_endpoint}/${encodeURIComponent(state.activeUniqid)}`
                : config.store_endpoint;
            const method = state.mode === 'edit' ? 'PATCH' : 'POST';

            setLoadingState(true);

            try {
                await fetchJson(url, {
                    method,
                    body: JSON.stringify(payload),
                });
                state.page = 1;
                closeModal(formModal);
                resetForm();
                loadRecords();
            } catch (errorPayload) {
                showFormFeedback(extractErrorMessage(errorPayload));
            } finally {
                setLoadingState(false);
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-bridge-source-table-app]');
        const body = document.body;

        if (!root) {
            return;
        }

        const config = JSON.parse(root.dataset.bridgeSourceTableApp || '{}');
        const hiddenBridgeSourceTableFields = new Set(['created_at', 'updated_at', 'created_by']);
        const isHiddenBridgeSourceTableField = (key = '') => hiddenBridgeSourceTableFields.has(String(key || '').toLowerCase());
        const configuredColumns = Array.isArray(config.columns) && config.columns.length > 0
            ? config.columns
            : ['row_key'];
        const visibleColumns = configuredColumns.filter((column) => !isHiddenBridgeSourceTableField(column));
        const columns = visibleColumns.length > 0 ? visibleColumns : ['row_key'];
        const gridHead = root.querySelector('[data-grid-head]');
        const gridBody = root.querySelector('[data-grid-body]');
        const gridSearch = root.querySelector('[data-grid-search]');
        const gridPerPage = root.querySelector('[data-grid-per-page]');
        const gridCount = root.querySelector('[data-grid-count]');
        const gridSummary = root.querySelector('[data-grid-summary]');
        const gridPage = root.querySelector('[data-grid-page]');
        const prevButton = root.querySelector('[data-grid-prev]');
        const nextButton = root.querySelector('[data-grid-next]');
        const viewModal = document.querySelector('[data-bridge-source-table-view-modal]');
        const viewSubtitle = viewModal?.querySelector('[data-bridge-source-table-view-subtitle]');
        const viewContent = viewModal?.querySelector('[data-bridge-source-table-view-content]');
        const state = {
            page: 1,
            perPage: Number(gridPerPage?.value || 10),
            search: '',
            rows: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                total: Number(config.row_count || 0),
                per_page: Number(gridPerPage?.value || 10),
            },
            loading: false,
        };

        let searchTimer = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const formatText = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (typeof value === 'object') {
                return JSON.stringify(value);
            }

            return String(value);
        };

        const formatFieldValue = (value, key = '') => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (typeof value === 'object') {
                return JSON.stringify(value);
            }

            if (key.endsWith('_at') || key === 'tanggal') {
                const date = new Date(value);

                if (!Number.isNaN(date.getTime())) {
                    return new Intl.DateTimeFormat('id-ID', {
                        dateStyle: 'medium',
                        timeStyle: key.endsWith('_at') ? 'short' : undefined,
                        timeZone: 'UTC',
                    }).format(date);
                }
            }

            return String(value);
        };

        const prettifyColumn = (column) => column
            .replaceAll('_', ' ')
            .replace(/\b\w/g, (letter) => letter.toUpperCase());

        const renderFieldTable = (record) => {
            const priority = ['row_key', 'uniqid', 'id', 'kode', 'nama', 'tanggal'];
            const entries = Object.entries(record || {})
                .filter(([key]) => !isHiddenBridgeSourceTableField(key))
                .sort(([left], [right]) => {
                const leftIndex = priority.indexOf(left);
                const rightIndex = priority.indexOf(right);

                if (leftIndex === -1 && rightIndex === -1) {
                    return left.localeCompare(right);
                }

                if (leftIndex === -1) {
                    return 1;
                }

                if (rightIndex === -1) {
                    return -1;
                }

                return leftIndex - rightIndex;
            });

            if (!entries.length) {
                return '<div class="detail-empty">Tidak ada field yang bisa ditampilkan.</div>';
            }

            const tableHtml = `
                <div class="detail-table-wrap">
                    <table class="detail-kv-table">
                        <tbody>
                            ${entries.map(([key, value]) => `
                                <tr>
                                    <th>${escapeHtml(prettifyColumn(key))}</th>
                                    <td>${escapeHtml(formatFieldValue(value, key))}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            return window.dashboardDetailMap?.wrapEntries(entries.map(([key, value]) => [key, value, key]), tableHtml) || tableHtml;
        };

        const fetchJson = async (url) => {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json().catch(() => null);

            if (!response.ok || (payload && payload.success === false)) {
                throw payload || { message: 'Permintaan gagal.' };
            }

            return payload;
        };

        const renderHeader = () => {
            gridHead.innerHTML = `
                <tr>
                    ${columns.map((column) => `<th>${escapeHtml(prettifyColumn(column))}</th>`).join('')}
                    <th>Aksi</th>
                </tr>
            `;
        };

        const renderRows = () => {
            const totalColumns = columns.length + 1;

            if (state.loading) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-loading">Memuat data...</td></tr>`;
                return;
            }

            if (!state.rows.length) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-empty">Belum ada data pada tabel ${escapeHtml(config.table || 'source')}.</td></tr>`;
                return;
            }

            gridBody.innerHTML = state.rows.map((row) => `
                <tr>
                    ${columns.map((column, index) => {
                        const value = row[column] ?? null;

                        if (index === 0) {
                            return `
                                <td>
                                    <div class="row-title">
                                        <strong>${escapeHtml(formatText(value))}</strong>
                                        <span>${escapeHtml(formatText(row.uniqid ?? row.nama ?? row.kode ?? '-'))}</span>
                                    </div>
                                </td>
                            `;
                        }

                        return `<td>${escapeHtml(formatFieldValue(value, column))}</td>`;
                    }).join('')}
                    <td>
                        <div class="inline-actions">
                            <button class="inline-button" type="button" data-bridge-source-table-view="${escapeHtml(row.row_key)}">Lihat</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        };

        const renderPagination = () => {
            const total = Number(state.pagination.total || 0);
            const currentPage = Number(state.pagination.current_page || 1);
            const lastPage = Number(state.pagination.last_page || 1);
            const perPage = Number(state.pagination.per_page || state.perPage || 10);
            const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            if (gridCount) {
                window.dashboardFillStaticTag(gridCount, 'Data', new Intl.NumberFormat('id-ID').format(total), 'data');
            }

            if (gridSummary) {
                gridSummary.textContent = total === 0
                    ? 'Belum ada data.'
                    : `Menampilkan ${new Intl.NumberFormat('id-ID').format(start)}-${new Intl.NumberFormat('id-ID').format(end)} dari ${new Intl.NumberFormat('id-ID').format(total)} data`;
            }

            if (gridPage) {
                gridPage.textContent = `Halaman ${currentPage} / ${lastPage}`;
            }

            if (prevButton) {
                prevButton.disabled = currentPage <= 1 || state.loading;
            }

            if (nextButton) {
                nextButton.disabled = currentPage >= lastPage || state.loading;
            }
        };

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const loadRows = async () => {
            state.loading = true;
            renderRows();
            renderPagination();

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: String(state.perPage),
            });

            if (state.search) {
                params.set('search', state.search);
            }

            try {
                const payload = await fetchJson(`${config.list_endpoint}?${params.toString()}`);
                state.rows = Array.isArray(payload.data) ? payload.data : [];
                state.pagination = payload.meta?.pagination || state.pagination;
            } catch {
                state.rows = [];
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
            } finally {
                state.loading = false;
                renderRows();
                renderPagination();
            }
        };

        const openDetail = (rowKey) => {
            const record = state.rows.find((row) => String(row.row_key) === String(rowKey));

            if (!record || !viewContent || !viewSubtitle) {
                return;
            }

            viewSubtitle.textContent = `${config.table || 'table'} · ${record.row_key}`;
            viewContent.innerHTML = `
                <section class="detail-hero">
                    <div class="detail-hero-main">
                        <span class="detail-hero-icon">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v14c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3"/>
                            </svg>
                        </span>
                        <div class="detail-hero-main-copy">
                            <span class="detail-eyebrow">Source Table</span>
                            <h3>${escapeHtml(config.table || 'Tabel Source')}</h3>
                            <p>${escapeHtml(config.description || 'Tampilan lengkap per baris dari tabel source hasil seeder SQL.')}</p>
                            <div class="detail-chip-grid">
                                <span class="detail-chip"><svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 10h16"/><path d="M9 5v14"/></svg><span class="tag-label">Row</span><span class="tag-value">${escapeHtml(record.row_key)}</span></span>
                                <span class="detail-chip"><svg class="tag-icon" viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h10"/></svg><span class="tag-label">Field</span><span class="tag-value">${escapeHtml(String(Object.keys(record).length))}</span></span>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="detail-section">
                    <div class="detail-section-head">
                        <div class="detail-section-title">
                            <span class="detail-section-icon">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/><path d="M8 6v12"/><path d="M16 6v12"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Detail Field</h4>
                            </div>
                        </div>
                    </div>
                    ${renderFieldTable(record)}
                </section>
            `;
            openModal(viewModal);
        };

        renderHeader();
        renderRows();
        renderPagination();
        loadRows();

        gridSearch?.addEventListener('input', (event) => {
            window.clearTimeout(searchTimer);
            state.search = event.target.value.trim();
            searchTimer = window.setTimeout(() => {
                state.page = 1;
                loadRows();
            }, 280);
        });

        gridPerPage?.addEventListener('change', (event) => {
            state.perPage = Number(event.target.value || 10);
            state.page = 1;
            loadRows();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadRows();
        });

        nextButton?.addEventListener('click', () => {
            if (state.page >= Number(state.pagination.last_page || 1)) {
                return;
            }

            state.page += 1;
            loadRows();
        });

        root.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-bridge-source-table-view]');

            if (!trigger) {
                return;
            }

            openDetail(trigger.dataset.bridgeSourceTableView);
        });

        document.querySelectorAll('[data-bridge-source-table-view-modal] [data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(viewModal);
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(viewModal);
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-tunnel-source-table-app]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const body = document.body;

        if (!root) {
            return;
        }

        const config = JSON.parse(root.dataset.tunnelSourceTableApp || '{}');
        const columns = Array.isArray(config.columns) && config.columns.length ? config.columns : ['row_key'];
        const formColumns = Array.isArray(config.form_columns) ? config.form_columns : [];
        const gridHead = root.querySelector('[data-grid-head]');
        const gridBody = root.querySelector('[data-grid-body]');
        const gridSearch = root.querySelector('[data-grid-search]');
        const gridPerPage = root.querySelector('[data-grid-per-page]');
        const gridCount = root.querySelector('[data-grid-count]');
        const gridSummary = root.querySelector('[data-grid-summary]');
        const gridPage = root.querySelector('[data-grid-page]');
        const prevButton = root.querySelector('[data-grid-prev]');
        const nextButton = root.querySelector('[data-grid-next]');
        const createButton = root.querySelector('[data-grid-create]');
        const importButton = root.querySelector('[data-tunnel-table-import-trigger]');
        const importFile = root.querySelector('[data-tunnel-table-import-file]');
        const viewModal = document.querySelector('[data-tunnel-source-table-view-modal]');
        const viewSubtitle = viewModal?.querySelector('[data-tunnel-source-table-view-subtitle]');
        const viewContent = viewModal?.querySelector('[data-tunnel-source-table-view-content]');
        const formModal = document.querySelector('[data-tunnel-source-table-form-modal]');
        const form = formModal?.querySelector('[data-tunnel-source-table-form]');
        const formTitle = formModal?.querySelector('[data-tunnel-source-table-form-title]');
        const formSubtitle = formModal?.querySelector('[data-tunnel-source-table-form-subtitle]');
        const formFields = formModal?.querySelector('[data-tunnel-source-table-form-fields]');
        const formFeedback = formModal?.querySelector('[data-tunnel-source-table-form-feedback]');
        const submitButton = formModal?.querySelector('[data-tunnel-source-table-form-submit]');
        const coordinateModal = document.querySelector('[data-tunnel-source-table-coordinate-modal]');
        const coordinateMapCanvas = coordinateModal?.querySelector('[data-tunnel-source-table-coordinate-map]');
        const coordinateSearchForm = coordinateModal?.querySelector('[data-tunnel-source-table-coordinate-search-form]');
        const coordinateSearchInput = coordinateModal?.querySelector('[data-tunnel-source-table-coordinate-search-input]');
        const coordinateFeedback = coordinateModal?.querySelector('[data-tunnel-source-table-coordinate-feedback]');
        const coordinateApplyButton = coordinateModal?.querySelector('[data-tunnel-source-table-coordinate-apply]');
        const coordinateLiveLat = coordinateModal?.querySelector('[data-tunnel-source-table-coordinate-live-lat]');
        const coordinateLiveLon = coordinateModal?.querySelector('[data-tunnel-source-table-coordinate-live-lon]');
        const isTunnelMasterTable = config.table === 'm_tunnels';
        const lookupOptions = config.lookup_options && typeof config.lookup_options === 'object' ? config.lookup_options : {};
        const state = {
            page: 1,
            perPage: Number(gridPerPage?.value || 10),
            search: '',
            rows: [],
            loading: false,
            pagination: {
                current_page: 1,
                last_page: 1,
                total: Number(config.row_count || 0),
                per_page: Number(gridPerPage?.value || 10),
            },
        };
        let searchTimer = null;
        let editingRowKey = null;
        let coordinateMap = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const prettify = (key) => key.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
        const isNameKey = (key) => key === 'nama' || key.startsWith('nama_') || key.includes('.nama_');

        const formatValue = (value, key = '') => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            if (isNameKey(key)) {
                return String(value).toUpperCase();
            }

            if (typeof value === 'object') {
                return JSON.stringify(value);
            }

            if (key.endsWith('_at') || key.startsWith('tgl_')) {
                const date = new Date(value);

                if (!Number.isNaN(date.getTime())) {
                    return new Intl.DateTimeFormat('id-ID', {
                        dateStyle: 'medium',
                        timeStyle: key.endsWith('_at') ? 'short' : undefined,
                        timeZone: 'UTC',
                    }).format(date);
                }
            }

            return String(value);
        };

        const isJsonColumn = (column) => String(column.type || '').toLowerCase().includes('json');
        const isTextColumn = (column) => /text|json/i.test(String(column.type || ''));
        const isDateColumn = (column) => /date|timestamp|datetime/i.test(String(column.type || ''));
        const isNumberColumn = (column) => /int|decimal|double|float/i.test(String(column.type || ''));
        const isCoordinateColumn = (name) => ['lat', 'long'].includes(name);
        const getFormField = (name) => form?.querySelector(`[name="${name}"]`);

        const generateDisplayUlid = () => {
            const alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
            let time = Date.now();
            const chars = Array(26).fill('0');

            for (let index = 9; index >= 0; index -= 1) {
                chars[index] = alphabet[time % 32];
                time = Math.floor(time / 32);
            }

            const random = new Uint8Array(16);

            if (window.crypto?.getRandomValues) {
                window.crypto.getRandomValues(random);
            } else {
                for (let index = 0; index < random.length; index += 1) {
                    random[index] = Math.floor(Math.random() * 256);
                }
            }

            for (let index = 10; index < 26; index += 1) {
                chars[index] = alphabet[random[index - 10] % 32];
            }

            return chars.join('');
        };

        const setFormField = (name, value) => {
            const field = getFormField(name);

            if (!field) {
                return;
            }

            field.value = value ?? '';
        };

        const fetchJson = async (url, options = {}) => {
            const isFormData = typeof FormData !== 'undefined' && options.body instanceof FormData;
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.body && !isFormData ? { 'Content-Type': 'application/json' } : {}),
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    ...(options.headers || {}),
                },
            });
            const payload = await response.json().catch(() => null);

            if (!response.ok || (payload && payload.success === false)) {
                throw payload || { message: 'Permintaan gagal.' };
            }

            return payload;
        };

        const extractErrorMessage = (payload) => {
            if (!payload) {
                return 'Terjadi kesalahan saat memproses tabel terowongan.';
            }

            if (payload.error?.details && typeof payload.error.details === 'object') {
                return Object.values(payload.error.details).flat().map((item) => {
                    if (typeof item === 'object') {
                        return JSON.stringify(item);
                    }

                    return String(item);
                }).join('\n');
            }

            if (payload.errors && typeof payload.errors === 'object') {
                return Object.values(payload.errors).flat().join('\n');
            }

            return payload.message || 'Terjadi kesalahan saat memproses tabel terowongan.';
        };

        const setLoadingState = (loading) => {
            state.loading = loading;

            if (submitButton) {
                submitButton.disabled = loading;
            }

            if (importButton) {
                importButton.disabled = loading;
            }
        };

        const renderHeader = () => {
            gridHead.innerHTML = `
                <tr>
                    ${columns.map((column) => `<th>${escapeHtml(prettify(column))}</th>`).join('')}
                    <th>Aksi</th>
                </tr>
            `;
        };

        const renderCellValue = (value, column) => {
            if (window.dashboardDocumentPreview?.normalize(value)) {
                return window.dashboardDocumentPreview.render(value, prettify(column));
            }

            return escapeHtml(formatValue(value, column));
        };

        const renderRows = () => {
            const totalColumns = columns.length + 1;

            if (state.loading) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-loading">Memuat data...</td></tr>`;
                return;
            }

            if (!state.rows.length) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-empty">Belum ada data pada tabel ${escapeHtml(config.table || 'terowongan')}.</td></tr>`;
                return;
            }

            gridBody.innerHTML = state.rows.map((row) => {
                const data = row.data || {};

                return `
                    <tr>
                        ${columns.map((column, index) => {
                            const value = data[column] ?? row[column] ?? null;
                            const cellValue = renderCellValue(value, column);

                            if (index === 0) {
                                return `
                                    <td>
                                        <div class="row-title">
                                            <strong>${cellValue}</strong>
                                            <span>${escapeHtml(formatValue(data.tunnel_id ?? data.kode_aset ?? row.row_key))}</span>
                                        </div>
                                    </td>
                                `;
                            }

                            return `<td>${cellValue}</td>`;
                        }).join('')}
                        <td class="tunnel-actions-cell">
                            <div class="inline-actions tunnel-row-actions">
                                <button class="inline-button" type="button" data-tunnel-source-table-view="${escapeHtml(row.row_key)}">Lihat</button>
                                <button class="inline-button" type="button" data-tunnel-source-table-edit="${escapeHtml(row.row_key)}">Edit</button>
                                <button class="inline-button danger" type="button" data-tunnel-source-table-delete="${escapeHtml(row.row_key)}">Hapus</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const renderPagination = () => {
            const total = Number(state.pagination.total || 0);
            const currentPage = Number(state.pagination.current_page || 1);
            const lastPage = Number(state.pagination.last_page || 1);
            const perPage = Number(state.pagination.per_page || state.perPage || 10);
            const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            if (gridCount) {
                window.dashboardFillStaticTag(gridCount, 'Data', new Intl.NumberFormat('id-ID').format(total), 'data');
            }

            if (gridSummary) {
                gridSummary.textContent = total === 0
                    ? 'Belum ada data.'
                    : `Menampilkan ${new Intl.NumberFormat('id-ID').format(start)}-${new Intl.NumberFormat('id-ID').format(end)} dari ${new Intl.NumberFormat('id-ID').format(total)} data`;
            }

            if (gridPage) {
                gridPage.textContent = `Halaman ${currentPage} / ${lastPage}`;
            }

            if (prevButton) {
                prevButton.disabled = currentPage <= 1 || state.loading;
            }

            if (nextButton) {
                nextButton.disabled = currentPage >= lastPage || state.loading;
            }
        };

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const formFieldGroup = (name) => {
            if (/dok|drawing|spesifikasi_teknis|kajian|uji|catatan|file|path/i.test(name)) {
                return 'documents';
            }

            if (/tahun|status|kondisi|jenis|material|metode|waterproofing|jumlah|gauge|lebar|tinggi|clearance|gradien|radius|panjang|km_hm|lat|long/i.test(name)) {
                return 'technical';
            }

            return 'identity';
        };

        const renderSelectField = (column, options) => {
            const name = column.name || '';
            const required = Array.isArray(config.required_columns) && config.required_columns.includes(name);

            return `
                <div class="field">
                    <label for="tunnel-table-${escapeHtml(name)}">${escapeHtml(prettify(name))}${required ? ' *' : ''}</label>
                    <select id="tunnel-table-${escapeHtml(name)}" name="${escapeHtml(name)}" ${required ? 'required' : ''}>
                        <option value="">Pilih ${escapeHtml(prettify(name))}</option>
                        ${options.map((option) => `<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`).join('')}
                    </select>
                </div>
            `;
        };

        const renderCoordinateFields = (columnsForGroup) => {
            const latColumn = columnsForGroup.find((column) => column.name === 'lat');
            const longColumn = columnsForGroup.find((column) => column.name === 'long');
            const latRequired = latColumn && Array.isArray(config.required_columns) && config.required_columns.includes('lat');
            const longRequired = longColumn && Array.isArray(config.required_columns) && config.required_columns.includes('long');

            if (!latColumn && !longColumn) {
                return '';
            }

            return `
                <div class="coordinate-input-grid">
                    ${latColumn ? `
                        <div class="field">
                            <label for="tunnel-table-lat">Latitude${latRequired ? ' *' : ''}</label>
                            <input id="tunnel-table-lat" name="lat" type="number" min="-90" max="90" step="0.0000001" ${latRequired ? 'required' : ''}>
                            <p class="field-hint">Dipakai sebagai titik pointing pada peta.</p>
                        </div>
                    ` : ''}
                    ${longColumn ? `
                        <div class="field">
                            <label for="tunnel-table-long">Longitude${longRequired ? ' *' : ''}</label>
                            <input id="tunnel-table-long" name="long" type="number" min="-180" max="180" step="0.0000001" ${longRequired ? 'required' : ''}>
                            <p class="field-hint">Dipakai bersama latitude untuk marker lokasi.</p>
                        </div>
                    ` : ''}
                    <div class="field coordinate-action-field">
                        <label>&nbsp;</label>
                        <button class="action-button coordinate-pulse-button" type="button" data-tunnel-source-table-coordinate-open>
                            <svg class="icon" viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2"/></svg>
                            <span>Koordinat</span>
                        </button>
                        <p class="field-hint">&nbsp;</p>
                    </div>
                </div>
            `;
        };

        const renderInputField = (column) => {
                const name = column.name || '';
                const required = Array.isArray(config.required_columns) && config.required_columns.includes(name);
                const inputType = isDateColumn(column) ? 'date' : (isNumberColumn(column) ? 'number' : 'text');
                const options = Array.isArray(lookupOptions[name]) ? lookupOptions[name] : [];

                if (isTunnelMasterTable && name === 'tunnel_id') {
                    return `
                        <div class="field">
                            <label for="tunnel-table-tunnel-id">Tunnel ID${required ? ' *' : ''}</label>
                            <input id="tunnel-table-tunnel-id" name="tunnel_id" type="text" disabled data-tunnel-source-table-generated-id>
                        </div>
                    `;
                }

                if (options.length) {
                    return renderSelectField(column, options);
                }

                if (isTextColumn(column)) {
                    return `
                        <div class="field full">
                            <label for="tunnel-table-${escapeHtml(name)}">${escapeHtml(prettify(name))}${required ? ' *' : ''}</label>
                            <textarea id="tunnel-table-${escapeHtml(name)}" name="${escapeHtml(name)}" ${required ? 'required' : ''} spellcheck="false">${isJsonColumn(column) ? '{}' : ''}</textarea>
                        </div>
                    `;
                }

                return `
                    <div class="field">
                        <label for="tunnel-table-${escapeHtml(name)}">${escapeHtml(prettify(name))}${required ? ' *' : ''}</label>
                        <input id="tunnel-table-${escapeHtml(name)}" name="${escapeHtml(name)}" type="${inputType}" ${isNumberColumn(column) ? 'step="any"' : ''} ${required ? 'required' : ''}>
                    </div>
                `;
        };

        const renderFormFields = () => {
            if (!formFields) {
                return;
            }

            const groups = [
                { key: 'identity', title: 'Identitas', columns: [] },
                { key: 'technical', title: 'Teknis dan Operasional', columns: [] },
                { key: 'documents', title: 'Dokumen dan Catatan', columns: [] },
            ];
            const groupMap = Object.fromEntries(groups.map((group) => [group.key, group]));

            formColumns.forEach((column) => {
                const name = column.name || '';
                groupMap[formFieldGroup(name)]?.columns.push(column);
            });

            formFields.innerHTML = groups
                .filter((group) => group.columns.length)
                .map((group) => {
                    const renderedColumns = isTunnelMasterTable
                        ? group.columns.filter((column) => !isCoordinateColumn(column.name || ''))
                        : group.columns;
                    const coordinateFields = isTunnelMasterTable ? renderCoordinateFields(group.columns) : '';

                    return `
                        <section class="form-section">
                            <div class="section-header compact">
                                <div>
                                    <h3>${escapeHtml(group.title)}</h3>
                                </div>
                            </div>
                            ${renderedColumns.length ? `
                                <div class="form-grid">
                                    ${renderedColumns.map(renderInputField).join('')}
                                </div>
                            ` : ''}
                            ${coordinateFields}
                        </section>
                    `;
                })
                .join('');
        };

        const clearFormFeedback = () => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = true;
            formFeedback.textContent = '';
        };

        const showFormFeedback = (message) => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = false;
            formFeedback.textContent = message;
        };

        const buildPayload = () => {
            const data = {};

            formColumns.forEach((column) => {
                const field = form?.querySelector(`[name="${column.name}"]`);
                const raw = field?.value?.trim() ?? '';

                if (raw === '') {
                    return;
                }

                if (isJsonColumn(column)) {
                    JSON.parse(raw);
                }

                data[column.name] = raw;
            });

            return { data };
        };

        const openCreateForm = () => {
            editingRowKey = null;
            form?.reset();
            renderFormFields();
            clearFormFeedback();

            if (isTunnelMasterTable) {
                setFormField('tunnel_id', generateDisplayUlid());
            }

            if (formTitle) {
                formTitle.textContent = `Tambah ${config.table || 'Tabel Terowongan'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Input row baru langsung ke database prasarana_tunnel.';
            }

            openModal(formModal);
        };

        const fieldValueForForm = (value, column) => {
            if (value === null || value === undefined) {
                return '';
            }

            if (isJsonColumn(column)) {
                if (typeof value === 'object') {
                    return JSON.stringify(value, null, 2);
                }

                try {
                    return JSON.stringify(JSON.parse(String(value)), null, 2);
                } catch {
                    return String(value);
                }
            }

            if (isDateColumn(column) && String(value).length >= 10) {
                return String(value).slice(0, 10);
            }

            return String(value);
        };

        const openEditForm = (rowKey) => {
            const record = state.rows.find((row) => String(row.row_key) === String(rowKey));

            if (!record) {
                return;
            }

            editingRowKey = rowKey;
            form?.reset();
            renderFormFields();
            clearFormFeedback();

            const data = record.data || {};

            formColumns.forEach((column) => {
                const field = form?.querySelector(`[name="${column.name}"]`);

                if (!field) {
                    return;
                }

                field.value = fieldValueForForm(data[column.name], column);
            });

            if (formTitle) {
                formTitle.textContent = `Edit ${config.table || 'Tabel Terowongan'}`;
            }

            if (formSubtitle) {
                formSubtitle.textContent = `${config.table || 'table'} · ${rowKey}`;
            }

            openModal(formModal);
        };

        const coordinateCenter = () => {
            const lat = Number(getFormField('lat')?.value || NaN);
            const lon = Number(getFormField('long')?.value || NaN);

            if (Number.isFinite(lat) && Number.isFinite(lon) && lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                return { lat, lon, zoom: 17 };
            }

            return { lat: -6.175392, lon: 106.827153, zoom: 6 };
        };

        const showCoordinateFeedback = (message) => {
            if (!coordinateFeedback) {
                return;
            }

            coordinateFeedback.hidden = !message;
            coordinateFeedback.textContent = message || '';
        };

        const updateCoordinateLive = () => {
            if (!coordinateMap) {
                return;
            }

            const center = coordinateMap.getCenter();

            if (coordinateLiveLat) {
                coordinateLiveLat.textContent = center.lat.toFixed(7);
            }

            if (coordinateLiveLon) {
                coordinateLiveLon.textContent = center.lng.toFixed(7);
            }
        };

        const initCoordinateMap = () => {
            if (!window.L || !coordinateMapCanvas) {
                showCoordinateFeedback('Peta belum siap dimuat.');
                return;
            }

            const center = coordinateCenter();

            if (!coordinateMap) {
                coordinateMap = window.L.map(coordinateMapCanvas, {
                    zoomControl: true,
                    attributionControl: true,
                    zoomSnap: 0.5,
                    zoomDelta: 0.5,
                    wheelDebounceTime: 140,
                    wheelPxPerZoomLevel: 240,
                }).setView([center.lat, center.lon], center.zoom);

                window.L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19,
                    maxNativeZoom: 19,
                }).addTo(coordinateMap);

                coordinateMap.on('move', updateCoordinateLive);
                coordinateMap.on('moveend', updateCoordinateLive);
            } else {
                coordinateMap.setView([center.lat, center.lon], center.zoom);
            }

            window.setTimeout(() => {
                coordinateMap?.invalidateSize();
                updateCoordinateLive();
            }, 120);
        };

        const openCoordinatePicker = () => {
            showCoordinateFeedback('');
            openModal(coordinateModal);
            initCoordinateMap();
        };

        const searchCoordinate = async (query) => {
            const keyword = query.trim();

            if (!keyword) {
                return;
            }

            showCoordinateFeedback('Mencari lokasi...');

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(keyword)}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                });
                const payload = await response.json();
                const result = Array.isArray(payload) ? payload[0] : null;

                if (!result) {
                    showCoordinateFeedback('Alamat tidak ditemukan.');
                    return;
                }

                coordinateMap?.setView([Number(result.lat), Number(result.lon)], 17);
                showCoordinateFeedback('');
            } catch {
                showCoordinateFeedback('Pencarian alamat gagal. Coba lagi beberapa saat.');
            }
        };

        const applyCoordinate = () => {
            if (!coordinateMap) {
                return;
            }

            const center = coordinateMap.getCenter();
            setFormField('lat', center.lat.toFixed(7));
            setFormField('long', center.lng.toFixed(7));
            closeModal(coordinateModal);
        };

        const importCsv = async (file) => {
            if (!file || !config.import_endpoint) {
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            setLoadingState(true);
            renderRows();
            renderPagination();

            try {
                await fetchJson(config.import_endpoint, {
                    method: 'POST',
                    body: formData,
                });
                state.page = 1;
                await loadRows();
            } catch (errorPayload) {
                state.rows = [];
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractErrorMessage(errorPayload))}</td></tr>`;
                renderPagination();
            } finally {
                setLoadingState(false);

                if (importFile) {
                    importFile.value = '';
                }
            }
        };

        const renderFieldTable = (record) => {
            const data = record.data || record || {};
            const entries = Object.entries(data);

            if (!entries.length) {
                return '<div class="detail-empty">Tidak ada field yang bisa ditampilkan.</div>';
            }

            const renderDetailValue = (key, value) => {
                if (window.dashboardDocumentPreview?.normalize(value)) {
                    return window.dashboardDocumentPreview.render(value, prettify(key));
                }

                return escapeHtml(formatValue(value, key));
            };

            const tableHtml = `
                <div class="detail-table-wrap">
                    <table class="detail-kv-table">
                        <tbody>
                            ${entries.map(([key, value]) => `
                                <tr>
                                    <th>${escapeHtml(prettify(key))}</th>
                                    <td>${renderDetailValue(key, value)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            return window.dashboardDetailMap?.wrapEntries(entries.map(([key, value]) => [key, value, key]), tableHtml) || tableHtml;
        };

        const openDetail = (rowKey) => {
            const record = state.rows.find((row) => String(row.row_key) === String(rowKey));

            if (!record || !viewContent || !viewSubtitle) {
                return;
            }

            viewSubtitle.textContent = `${config.table || 'table'} · ${record.row_key}`;
            viewContent.innerHTML = renderFieldTable(record);
            openModal(viewModal);
        };

        const deleteRow = async (rowKey) => {
            if (!rowKey || !config.delete_endpoint) {
                return;
            }

            const record = state.rows.find((row) => String(row.row_key) === String(rowKey));
            const data = record?.data || {};
            const label = data.nama_terowongan || data.nama || data.nomor_bh || data.kode || data.tunnel_id || rowKey;

            if (!window.confirm(`Hapus row ${label} dari ${config.table || 'tabel terowongan'}?`)) {
                return;
            }

            setLoadingState(true);

            try {
                await fetchJson(config.delete_endpoint.replace('__row__', encodeURIComponent(rowKey)), {
                    method: 'DELETE',
                });

                if (state.rows.length === 1 && state.page > 1) {
                    state.page -= 1;
                }

                await loadRows();
            } catch (errorPayload) {
                setLoadingState(false);
                window.alert(extractErrorMessage(errorPayload));
            }
        };

        const loadRows = async () => {
            setLoadingState(true);
            renderRows();
            renderPagination();

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: String(state.perPage),
            });

            if (state.search) {
                params.set('search', state.search);
            }

            try {
                const payload = await fetchJson(`${config.list_endpoint}?${params.toString()}`);
                state.rows = Array.isArray(payload.data) ? payload.data : [];
                state.pagination = payload.meta?.pagination || state.pagination;
            } catch (errorPayload) {
                state.rows = [];
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractErrorMessage(errorPayload))}</td></tr>`;
            } finally {
                setLoadingState(false);
                renderRows();
                renderPagination();
            }
        };

        renderHeader();
        renderFormFields();
        renderRows();
        renderPagination();
        loadRows();

        gridSearch?.addEventListener('input', (event) => {
            window.clearTimeout(searchTimer);
            state.search = event.target.value.trim();
            searchTimer = window.setTimeout(() => {
                state.page = 1;
                loadRows();
            }, 280);
        });

        gridPerPage?.addEventListener('change', (event) => {
            state.perPage = Number(event.target.value || 10);
            state.page = 1;
            loadRows();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadRows();
        });

        nextButton?.addEventListener('click', () => {
            if (state.page >= Number(state.pagination.last_page || 1)) {
                return;
            }

            state.page += 1;
            loadRows();
        });

        createButton?.addEventListener('click', openCreateForm);

        importButton?.addEventListener('click', () => {
            importFile?.click();
        });

        importFile?.addEventListener('change', (event) => {
            importCsv(event.target.files?.[0] || null);
        });

        formModal?.addEventListener('click', (event) => {
            const coordinateTrigger = event.target.closest('[data-tunnel-source-table-coordinate-open]');

            if (coordinateTrigger) {
                openCoordinatePicker();
            }
        });

        coordinateSearchForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            searchCoordinate(coordinateSearchInput?.value || '');
        });

        coordinateApplyButton?.addEventListener('click', applyCoordinate);

        root.addEventListener('click', (event) => {
            const deleteTrigger = event.target.closest('[data-tunnel-source-table-delete]');

            if (deleteTrigger) {
                deleteRow(deleteTrigger.dataset.tunnelSourceTableDelete);
                return;
            }

            const editTrigger = event.target.closest('[data-tunnel-source-table-edit]');

            if (editTrigger) {
                openEditForm(editTrigger.dataset.tunnelSourceTableEdit);
                return;
            }

            const trigger = event.target.closest('[data-tunnel-source-table-view]');

            if (!trigger) {
                return;
            }

            openDetail(trigger.dataset.tunnelSourceTableView);
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFormFeedback();

            let payload = null;

            try {
                payload = buildPayload();
            } catch {
                showFormFeedback('Kolom JSON harus berisi JSON valid.');
                return;
            }

            setLoadingState(true);

            try {
                const endpoint = editingRowKey && config.update_endpoint
                    ? config.update_endpoint.replace('__row__', encodeURIComponent(editingRowKey))
                    : config.store_endpoint;
                await fetchJson(endpoint, {
                    method: editingRowKey ? 'PATCH' : 'POST',
                    body: JSON.stringify(payload),
                });
                closeModal(formModal);
                editingRowKey = null;
                state.page = 1;
                await loadRows();
            } catch (errorPayload) {
                showFormFeedback(extractErrorMessage(errorPayload));
            } finally {
                setLoadingState(false);
            }
        });

        document.querySelectorAll('[data-tunnel-source-table-view-modal] [data-modal-close], [data-tunnel-source-table-form-modal] [data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(viewModal);
                closeModal(formModal);
            });
        });

        coordinateModal?.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(coordinateModal));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(viewModal);
                closeModal(coordinateModal);
                closeModal(formModal);
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-superadmin-users-app]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const body = document.body;

        if (!root) {
            return;
        }

        const config = JSON.parse(root.dataset.superadminUsersApp || '{}');
        const columns = Array.isArray(config.columns) && config.columns.length
            ? config.columns
            : ['name', 'email', 'role_label', 'email_verified_at', 'updated_at'];
        const labels = {
            name: 'Nama',
            email: 'Email',
            role_label: 'Role',
            email_verified_at: 'Email Verified',
            updated_at: 'Diperbarui',
        };

        const gridHead = root.querySelector('[data-grid-head]');
        const gridBody = root.querySelector('[data-grid-body]');
        const gridSearch = root.querySelector('[data-grid-search]');
        const gridPerPage = root.querySelector('[data-grid-per-page]');
        const gridCount = root.querySelector('[data-grid-count]');
        const gridSummary = root.querySelector('[data-grid-summary]');
        const gridPage = root.querySelector('[data-grid-page]');
        const prevButton = root.querySelector('[data-grid-prev]');
        const nextButton = root.querySelector('[data-grid-next]');
        const createButton = root.querySelector('[data-grid-create]');
        const viewModal = document.querySelector('[data-superadmin-user-view-modal]');
        const viewSubtitle = viewModal?.querySelector('[data-user-view-subtitle]');
        const viewContent = viewModal?.querySelector('[data-user-view-content]');
        const formModal = document.querySelector('[data-superadmin-user-form-modal]');
        const formTitle = formModal?.querySelector('[data-user-form-title]');
        const formSubtitle = formModal?.querySelector('[data-user-form-subtitle]');
        const form = formModal?.querySelector('[data-superadmin-user-form]');
        const formFeedback = formModal?.querySelector('[data-user-form-feedback]');
        const submitButton = formModal?.querySelector('[data-user-form-submit]');
        const passwordInput = form?.querySelector('[name="password"]');
        const passwordToggle = form?.querySelector('[data-password-toggle]');
        const passwordEye = passwordToggle?.querySelector('.password-eye');
        const passwordEyeOff = passwordToggle?.querySelector('.password-eye-off');

        const state = {
            page: 1,
            perPage: Number(gridPerPage?.value || 10),
            search: '',
            loading: false,
            mode: 'create',
            activeUuid: null,
            pagination: {
                current_page: 1,
                last_page: 1,
                total: Number(config.records_count || 0),
                per_page: Number(gridPerPage?.value || 10),
            },
        };

        let searchTimer = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const prettify = (key) => labels[key] || key.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

        const formatText = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            return String(value);
        };

        const formatDate = (value) => {
            if (!value) {
                return '-';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return String(value);
            }

            return new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short',
                timeZone: 'UTC',
            }).format(date);
        };

        const fetchJson = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    ...(options.headers || {}),
                },
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || (payload && payload.success === false)) {
                throw payload || { message: 'Permintaan gagal.' };
            }

            return payload;
        };

        const extractError = (payload) => {
            if (payload?.error?.details && typeof payload.error.details === 'object') {
                return Object.values(payload.error.details).flat().join('\n');
            }

            if (payload?.errors && typeof payload.errors === 'object') {
                return Object.values(payload.errors).flat().join('\n');
            }

            return payload?.message || 'Terjadi kesalahan saat memproses data user.';
        };

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const renderHeader = () => {
            gridHead.innerHTML = `
                <tr>
                    ${columns.map((column) => `<th>${escapeHtml(prettify(column))}</th>`).join('')}
                    <th>Aksi</th>
                </tr>
            `;
        };

        const renderRows = (rows = []) => {
            const totalColumns = columns.length + 1;

            if (state.loading) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-loading">Memuat data user...</td></tr>`;
                return;
            }

            if (!rows.length) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-empty">Belum ada data user.</td></tr>`;
                return;
            }

            gridBody.innerHTML = rows.map((row) => `
                <tr>
                    <td>
                        <div class="row-title">
                            <strong>${escapeHtml(formatText(row.name))}</strong>
                            <span>${escapeHtml(formatText(row.uuid))}</span>
                        </div>
                    </td>
                    <td>${escapeHtml(formatText(row.email))}</td>
                    <td>${escapeHtml(formatText(row.role_label))}</td>
                    <td>${escapeHtml(formatDate(row.email_verified_at))}</td>
                    <td>${escapeHtml(formatDate(row.updated_at))}</td>
                    <td>
                        <div class="inline-actions">
                            <button class="inline-button" type="button" data-user-action="view" data-user-uuid="${escapeHtml(row.uuid)}">Lihat</button>
                            <button class="inline-button primary" type="button" data-user-action="edit" data-user-uuid="${escapeHtml(row.uuid)}">Edit</button>
                            <button class="inline-button danger" type="button" data-user-action="delete" data-user-uuid="${escapeHtml(row.uuid)}">Hapus</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        };

        const renderPagination = () => {
            const total = Number(state.pagination.total || 0);
            const currentPage = Number(state.pagination.current_page || 1);
            const lastPage = Number(state.pagination.last_page || 1);
            const perPage = Number(state.pagination.per_page || state.perPage || 10);
            const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            if (gridCount) {
                window.dashboardFillStaticTag(gridCount, 'Data', new Intl.NumberFormat('id-ID').format(total), 'data');
            }

            if (gridSummary) {
                gridSummary.textContent = total === 0
                    ? 'Belum ada data user.'
                    : `Menampilkan ${new Intl.NumberFormat('id-ID').format(start)}-${new Intl.NumberFormat('id-ID').format(end)} dari ${new Intl.NumberFormat('id-ID').format(total)} user`;
            }

            if (gridPage) {
                gridPage.textContent = `Halaman ${currentPage} / ${lastPage}`;
            }

            if (prevButton) {
                prevButton.disabled = state.loading || currentPage <= 1;
            }

            if (nextButton) {
                nextButton.disabled = state.loading || currentPage >= lastPage;
            }
        };

        const clearFeedback = () => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = true;
            formFeedback.textContent = '';
            formFeedback.classList.remove('success');
        };

        const showFeedback = (message) => {
            if (!formFeedback) {
                return;
            }

            formFeedback.hidden = false;
            formFeedback.textContent = message;
            formFeedback.classList.remove('success');
        };

        const setPasswordVisible = (visible) => {
            if (!passwordInput || !passwordToggle) {
                return;
            }

            passwordInput.type = visible ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', visible ? 'true' : 'false');
            passwordToggle.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');

            if (passwordEye) {
                passwordEye.hidden = visible;
            }

            if (passwordEyeOff) {
                passwordEyeOff.hidden = !visible;
            }
        };

        const loadRows = async () => {
            state.loading = true;
            renderRows([]);
            renderPagination();

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: String(state.perPage),
            });

            if (state.search) {
                params.set('search', state.search);
            }

            try {
                const payload = await fetchJson(`${config.list_endpoint}?${params.toString()}`);
                state.pagination = payload.meta?.pagination || state.pagination;
                state.loading = false;
                renderRows(Array.isArray(payload.data) ? payload.data : []);
            } catch (errorPayload) {
                state.loading = false;
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractError(errorPayload))}</td></tr>`;
            } finally {
                renderPagination();
            }
        };

        const fetchRecord = async (uuid) => {
            const payload = await fetchJson(`${config.list_endpoint}/${uuid}`);
            return payload.data || null;
        };

        const resetForm = () => {
            if (!form) {
                return;
            }

            form.reset();
            form.querySelector('[name="email_verified"]').checked = true;
            setPasswordVisible(false);
            state.mode = 'create';
            state.activeUuid = null;

            if (formTitle) {
                formTitle.textContent = 'Tambah User';
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Isi akun baru beserta role aksesnya.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan';
                submitButton.disabled = false;
            }

            clearFeedback();
        };

        const fillForm = (record) => {
            if (!form) {
                return;
            }

            form.querySelector('[name="name"]').value = record.name || '';
            form.querySelector('[name="email"]').value = record.email || '';
            form.querySelector('[name="role"]').value = record.role || 'viewer';
            form.querySelector('[name="password"]').value = '';
            setPasswordVisible(false);
            form.querySelector('[name="email_verified"]').checked = Boolean(record.email_verified);
        };

        const renderDetail = (record) => {
            if (!viewSubtitle || !viewContent) {
                return;
            }

            viewSubtitle.textContent = `${record.name || '-'} · ${record.role_label || '-'}`;
            viewContent.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item"><span>Nama</span><strong>${escapeHtml(formatText(record.name))}</strong></div>
                    <div class="detail-item"><span>Email</span><strong>${escapeHtml(formatText(record.email))}</strong></div>
                    <div class="detail-item"><span>Role</span><strong>${escapeHtml(formatText(record.role_label))}</strong></div>
                    <div class="detail-item"><span>Email Verified</span><strong>${escapeHtml(formatDate(record.email_verified_at))}</strong></div>
                    <div class="detail-item full"><span>Deskripsi Role</span><strong>${escapeHtml(formatText(record.role_description))}</strong></div>
                </div>
                <section class="detail-section">
                    <div class="detail-section-head">
                        <div class="detail-section-title">
                            <span class="detail-section-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M9 12h6"/><path d="M12 9v6"/><path d="M12 3 4 7v5c0 5 3.4 9.4 8 10 4.6-.6 8-5 8-10V7z"/></svg></span>
                            <div>
                                <h4>Ability Role</h4>
                            </div>
                        </div>
                    </div>
                    <div class="detail-table-wrap">
                        <table class="detail-record-table">
                            <thead><tr><th>Ability</th></tr></thead>
                            <tbody>${(Array.isArray(record.abilities) ? record.abilities : []).map((ability) => `<tr><td class="mono">${escapeHtml(formatText(ability))}</td></tr>`).join('') || '<tr><td>-</td></tr>'}</tbody>
                        </table>
                    </div>
                </section>
            `;
        };

        const openCreate = () => {
            resetForm();
            openModal(formModal);
        };

        const openEdit = async (uuid) => {
            resetForm();
            state.mode = 'edit';
            state.activeUuid = uuid;

            if (formTitle) {
                formTitle.textContent = 'Edit User';
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Perbarui identitas, role, atau password user.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan Perubahan';
            }

            openModal(formModal);
            showFeedback('Memuat detail user...');

            try {
                const record = await fetchRecord(uuid);
                fillForm(record);
                clearFeedback();
            } catch (errorPayload) {
                showFeedback(extractError(errorPayload));
            }
        };

        const openView = async (uuid) => {
            if (viewContent) {
                viewContent.innerHTML = '<div class="grid-loading">Memuat detail user...</div>';
            }

            if (viewSubtitle) {
                viewSubtitle.textContent = 'Memuat data...';
            }

            openModal(viewModal);

            try {
                renderDetail(await fetchRecord(uuid));
            } catch (errorPayload) {
                if (viewContent) {
                    viewContent.innerHTML = `<div class="feedback">${escapeHtml(extractError(errorPayload))}</div>`;
                }
            }
        };

        const destroyUser = async (uuid) => {
            const confirmed = window.confirm('Hapus user ini? Tindakan ini tidak bisa dibatalkan.');

            if (!confirmed) {
                return;
            }

            try {
                await fetchJson(`${config.list_endpoint}/${uuid}`, {
                    method: 'DELETE',
                });
                await loadRows();
            } catch (errorPayload) {
                window.alert(extractError(errorPayload));
            }
        };

        renderHeader();
        renderPagination();
        loadRows();

        gridSearch?.addEventListener('input', (event) => {
            window.clearTimeout(searchTimer);
            state.search = event.target.value.trim();
            searchTimer = window.setTimeout(() => {
                state.page = 1;
                loadRows();
            }, 280);
        });

        gridPerPage?.addEventListener('change', (event) => {
            state.perPage = Number(event.target.value || 10);
            state.page = 1;
            loadRows();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadRows();
        });

        nextButton?.addEventListener('click', () => {
            if (state.page >= Number(state.pagination.last_page || 1)) {
                return;
            }

            state.page += 1;
            loadRows();
        });

        createButton?.addEventListener('click', openCreate);

        passwordToggle?.addEventListener('click', () => {
            setPasswordVisible(passwordInput?.type === 'password');
            passwordInput?.focus();
        });

        root.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-user-action]');

            if (!trigger) {
                return;
            }

            const uuid = trigger.dataset.userUuid;
            const action = trigger.dataset.userAction;

            if (action === 'view') {
                openView(uuid);
            }

            if (action === 'edit') {
                openEdit(uuid);
            }

            if (action === 'delete') {
                destroyUser(uuid);
            }
        });

        document.querySelectorAll('[data-superadmin-user-view-modal] [data-modal-close], [data-superadmin-user-form-modal] [data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(viewModal);
                closeModal(formModal);
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(viewModal);
                closeModal(formModal);
            }
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFeedback();

            const payload = {
                name: form.querySelector('[name="name"]').value.trim(),
                email: form.querySelector('[name="email"]').value.trim(),
                role: form.querySelector('[name="role"]').value,
                password: form.querySelector('[name="password"]').value,
                email_verified: form.querySelector('[name="email_verified"]').checked,
            };

            if (state.mode === 'edit' && payload.password.trim() === '') {
                delete payload.password;
            }

            submitButton.disabled = true;

            try {
                const url = state.mode === 'edit' && state.activeUuid
                    ? `${config.list_endpoint}/${state.activeUuid}`
                    : config.store_endpoint;
                const method = state.mode === 'edit' ? 'PATCH' : 'POST';

                await fetchJson(url, {
                    method,
                    body: JSON.stringify(payload),
                });

                closeModal(formModal);
                resetForm();
                await loadRows();
            } catch (errorPayload) {
                showFeedback(extractError(errorPayload));
            } finally {
                submitButton.disabled = false;
            }
        });
    })();

    (() => {
        const root = document.querySelector('[data-superadmin-api-clients-app]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const body = document.body;

        if (!root) {
            return;
        }

        const config = JSON.parse(root.dataset.superadminApiClientsApp || '{}');
        const columns = Array.isArray(config.columns) && config.columns.length
            ? config.columns
            : ['name', 'code', 'owner_email', 'access_tokens_count', 'is_active', 'expires_at', 'updated_at'];
        const labels = {
            name: 'Client',
            code: 'Code',
            owner_email: 'Owner',
            access_tokens_count: 'Token',
            is_active: 'Status',
            expires_at: 'Expired',
            updated_at: 'Diperbarui',
        };

        const gridHead = root.querySelector('[data-grid-head]');
        const gridBody = root.querySelector('[data-grid-body]');
        const gridSearch = root.querySelector('[data-grid-search]');
        const gridPerPage = root.querySelector('[data-grid-per-page]');
        const gridCount = root.querySelector('[data-grid-count]');
        const gridSummary = root.querySelector('[data-grid-summary]');
        const gridPage = root.querySelector('[data-grid-page]');
        const prevButton = root.querySelector('[data-grid-prev]');
        const nextButton = root.querySelector('[data-grid-next]');
        const createButton = root.querySelector('[data-grid-create]');
        const viewModal = document.querySelector('[data-superadmin-api-client-view-modal]');
        const viewSubtitle = viewModal?.querySelector('[data-api-client-view-subtitle]');
        const viewContent = viewModal?.querySelector('[data-api-client-view-content]');
        const formModal = document.querySelector('[data-superadmin-api-client-form-modal]');
        const formTitle = formModal?.querySelector('[data-api-client-form-title]');
        const formSubtitle = formModal?.querySelector('[data-api-client-form-subtitle]');
        const form = formModal?.querySelector('[data-superadmin-api-client-form]');
        const formFeedback = formModal?.querySelector('[data-api-client-form-feedback]');
        const submitButton = formModal?.querySelector('[data-api-client-form-submit]');
        const tokenModal = document.querySelector('[data-superadmin-api-token-modal]');
        const tokenSubtitle = tokenModal?.querySelector('[data-api-token-subtitle]');
        const tokenForm = tokenModal?.querySelector('[data-superadmin-api-token-form]');
        const tokenFeedback = tokenModal?.querySelector('[data-api-token-feedback]');
        const tokenSubmitButton = tokenModal?.querySelector('[data-api-token-submit]');
        const tokenResult = tokenModal?.querySelector('[data-api-token-result]');
        const tokenSecret = tokenModal?.querySelector('[data-api-token-secret]');
        const copyTokenButton = tokenModal?.querySelector('[data-copy-api-token]');

        const state = {
            page: 1,
            perPage: Number(gridPerPage?.value || 10),
            search: '',
            loading: false,
            mode: 'create',
            activeUuid: null,
            activeTokenUuid: null,
            activeTokenValue: '',
            pagination: {
                current_page: 1,
                last_page: 1,
                total: Number(config.records_count || 0),
                per_page: Number(gridPerPage?.value || 10),
            },
        };

        let searchTimer = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const prettify = (key) => labels[key] || key.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

        const formatText = (value) => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            return String(value);
        };

        const formatDate = (value) => {
            if (!value) {
                return '-';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return String(value);
            }

            return new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short',
                timeZone: 'UTC',
            }).format(date);
        };

        const toDateTimeInput = (value) => {
            if (!value) {
                return '';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return '';
            }

            const shifted = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));

            return shifted.toISOString().slice(0, 16);
        };

        const splitLines = (value) => value
            .split('\n')
            .map((item) => item.trim())
            .filter(Boolean);

        const fetchJson = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    ...(options.headers || {}),
                },
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || (payload && payload.success === false)) {
                throw payload || { message: 'Permintaan gagal.' };
            }

            return payload;
        };

        const extractError = (payload) => {
            if (payload?.error?.details && typeof payload.error.details === 'object') {
                return Object.values(payload.error.details).flat().join('\n');
            }

            if (payload?.errors && typeof payload.errors === 'object') {
                return Object.values(payload.errors).flat().join('\n');
            }

            return payload?.message || 'Terjadi kesalahan saat memproses client API.';
        };

        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('modal-open');

            requestAnimationFrame(() => window.dashboardDetailMap?.init(modal));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            window.dashboardModalA11y?.prepareClose(modal);
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');

            if (!document.querySelector('.modal.is-open')) {
                body.classList.remove('modal-open');
            }
        };

        const clearFeedback = (element) => {
            if (!element) {
                return;
            }

            element.hidden = true;
            element.textContent = '';
            element.classList.remove('success');
        };

        const showFeedback = (element, message, success = false) => {
            if (!element) {
                return;
            }

            element.hidden = false;
            element.textContent = message;
            element.classList.toggle('success', success);
        };

        const renderHeader = () => {
            gridHead.innerHTML = `
                <tr>
                    ${columns.map((column) => `<th>${escapeHtml(prettify(column))}</th>`).join('')}
                    <th>Aksi</th>
                </tr>
            `;
        };

        const renderRows = (rows = []) => {
            const totalColumns = columns.length + 1;

            if (state.loading) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-loading">Memuat client API...</td></tr>`;
                return;
            }

            if (!rows.length) {
                gridBody.innerHTML = `<tr><td colspan="${totalColumns}" class="grid-empty">Belum ada client API.</td></tr>`;
                return;
            }

            gridBody.innerHTML = rows.map((row) => `
                <tr>
                    <td>
                        <div class="row-title">
                            <strong>${escapeHtml(formatText(row.name))}</strong>
                            <span>${escapeHtml(formatText(row.uuid))}</span>
                        </div>
                    </td>
                    <td class="mono">${escapeHtml(formatText(row.code))}</td>
                    <td>${escapeHtml(formatText(row.owner_email || row.owner_name))}</td>
                    <td>${escapeHtml(formatText(row.access_tokens_count))}</td>
                    <td><span class="status ${row.is_active ? 'ready' : 'partial'}"><svg class="tag-icon" viewBox="0 0 24 24">${row.is_active ? '<path d="M20 6 9 17l-5-5"/>' : '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'}</svg><span class="tag-label">Status</span><span class="tag-value">${row.is_active ? 'active' : 'inactive'}</span></span></td>
                    <td>${escapeHtml(formatDate(row.expires_at))}</td>
                    <td>${escapeHtml(formatDate(row.updated_at))}</td>
                    <td>
                        <div class="inline-actions">
                            <button class="inline-button" type="button" data-api-client-action="view" data-api-client-uuid="${escapeHtml(row.uuid)}">Lihat</button>
                            <button class="inline-button primary" type="button" data-api-client-action="edit" data-api-client-uuid="${escapeHtml(row.uuid)}">Edit</button>
                            <button class="inline-button" type="button" data-api-client-action="token" data-api-client-uuid="${escapeHtml(row.uuid)}">Token</button>
                            <button class="inline-button danger" type="button" data-api-client-action="delete" data-api-client-uuid="${escapeHtml(row.uuid)}">Hapus</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        };

        const renderPagination = () => {
            const total = Number(state.pagination.total || 0);
            const currentPage = Number(state.pagination.current_page || 1);
            const lastPage = Number(state.pagination.last_page || 1);
            const perPage = Number(state.pagination.per_page || state.perPage || 10);
            const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            if (gridCount) {
                window.dashboardFillStaticTag(gridCount, 'Data', new Intl.NumberFormat('id-ID').format(total), 'data');
            }

            if (gridSummary) {
                gridSummary.textContent = total === 0
                    ? 'Belum ada client API.'
                    : `Menampilkan ${new Intl.NumberFormat('id-ID').format(start)}-${new Intl.NumberFormat('id-ID').format(end)} dari ${new Intl.NumberFormat('id-ID').format(total)} client`;
            }

            if (gridPage) {
                gridPage.textContent = `Halaman ${currentPage} / ${lastPage}`;
            }

            if (prevButton) {
                prevButton.disabled = state.loading || currentPage <= 1;
            }

            if (nextButton) {
                nextButton.disabled = state.loading || currentPage >= lastPage;
            }
        };

        const loadRows = async () => {
            state.loading = true;
            renderRows([]);
            renderPagination();

            const params = new URLSearchParams({
                page: String(state.page),
                per_page: String(state.perPage),
            });

            if (state.search) {
                params.set('search', state.search);
            }

            try {
                const payload = await fetchJson(`${config.list_endpoint}?${params.toString()}`);
                state.pagination = payload.meta?.pagination || state.pagination;
                state.loading = false;
                renderRows(Array.isArray(payload.data) ? payload.data : []);
            } catch (errorPayload) {
                state.loading = false;
                state.pagination = {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: state.perPage,
                };
                gridBody.innerHTML = `<tr><td colspan="${columns.length + 1}" class="grid-empty">${escapeHtml(extractError(errorPayload))}</td></tr>`;
            } finally {
                renderPagination();
            }
        };

        const fetchRecord = async (uuid) => {
            const payload = await fetchJson(`${config.list_endpoint}/${uuid}`);
            return payload.data || null;
        };

        const resetForm = () => {
            if (!form) {
                return;
            }

            form.reset();
            form.querySelector('[name="is_active"]').checked = true;
            state.mode = 'create';
            state.activeUuid = null;

            if (formTitle) {
                formTitle.textContent = 'Tambah Client API';
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Isi metadata integrasi lalu simpan.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan';
                submitButton.disabled = false;
            }

            clearFeedback(formFeedback);
        };

        const fillForm = (record) => {
            if (!form) {
                return;
            }

            form.querySelector('[name="name"]').value = record.name || '';
            form.querySelector('[name="code"]').value = record.code || '';
            form.querySelector('[name="owner_name"]').value = record.owner_name || '';
            form.querySelector('[name="owner_email"]').value = record.owner_email || '';
            form.querySelector('[name="rate_limit_per_minute"]').value = record.rate_limit_per_minute || '';
            form.querySelector('[name="rate_limit_per_day"]').value = record.rate_limit_per_day || '';
            form.querySelector('[name="expires_at"]').value = toDateTimeInput(record.expires_at);
            form.querySelector('[name="description"]').value = record.description || '';
            form.querySelector('[name="allowed_ips"]').value = Array.isArray(record.allowed_ips) ? record.allowed_ips.join('\n') : '';
            form.querySelector('[name="allowed_origins"]').value = Array.isArray(record.allowed_origins) ? record.allowed_origins.join('\n') : '';
            form.querySelector('[name="is_active"]').checked = Boolean(record.is_active);
        };

        const renderDetail = (record) => {
            if (!viewSubtitle || !viewContent) {
                return;
            }

            viewSubtitle.textContent = `${record.name || '-'} · ${record.code || '-'}`;

            const recentTokens = Array.isArray(record.recent_tokens) ? record.recent_tokens : [];
            const tokenRows = recentTokens.length
                ? recentTokens.map((token) => `
                    <tr>
                        <td>${escapeHtml(formatText(token.name))}</td>
                        <td class="mono">${escapeHtml((token.abilities || []).join(', ') || '-')}</td>
                        <td>${escapeHtml(formatDate(token.expires_at))}</td>
                        <td>${escapeHtml(formatDate(token.created_at))}</td>
                    </tr>
                `).join('')
                : '<tr><td colspan="4" class="empty">Belum ada token yang tercatat untuk client ini.</td></tr>';

            viewContent.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item"><span>Nama Client</span><strong>${escapeHtml(formatText(record.name))}</strong></div>
                    <div class="detail-item"><span>Code</span><strong class="mono">${escapeHtml(formatText(record.code))}</strong></div>
                    <div class="detail-item"><span>Owner</span><strong>${escapeHtml(formatText(record.owner_name || '-'))}</strong></div>
                    <div class="detail-item"><span>Email Owner</span><strong>${escapeHtml(formatText(record.owner_email || '-'))}</strong></div>
                    <div class="detail-item"><span>Status</span><strong>${record.is_active ? 'Active' : 'Inactive'}</strong></div>
                    <div class="detail-item"><span>Total Token</span><strong>${escapeHtml(formatText(record.access_tokens_count))}</strong></div>
                    <div class="detail-item"><span>Rate / Menit</span><strong>${escapeHtml(formatText(record.rate_limit_per_minute))}</strong></div>
                    <div class="detail-item"><span>Rate / Hari</span><strong>${escapeHtml(formatText(record.rate_limit_per_day))}</strong></div>
                    <div class="detail-item full"><span>Deskripsi</span><strong>${escapeHtml(formatText(record.description || '-'))}</strong></div>
                    <div class="detail-item full"><span>Allowed IPs</span><strong>${escapeHtml((record.allowed_ips || []).join(', ') || '-')}</strong></div>
                    <div class="detail-item full"><span>Allowed Origins</span><strong>${escapeHtml((record.allowed_origins || []).join(', ') || '-')}</strong></div>
                </div>
                <section class="detail-section">
                    <div class="detail-section-head">
                        <div class="detail-section-title">
                            <span class="detail-section-icon"><svg class="icon" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v14c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3"/></svg></span>
                            <div>
                                <h4>Riwayat Token Terakhir</h4>
                            </div>
                        </div>
                    </div>
                    <div class="detail-table-wrap">
                        <table class="detail-record-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Ability</th>
                                    <th>Expired</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>${tokenRows}</tbody>
                        </table>
                    </div>
                </section>
            `;
        };

        const openCreate = () => {
            resetForm();
            openModal(formModal);
        };

        const openEdit = async (uuid) => {
            resetForm();
            state.mode = 'edit';
            state.activeUuid = uuid;

            if (formTitle) {
                formTitle.textContent = 'Edit Client API';
            }

            if (formSubtitle) {
                formSubtitle.textContent = 'Perbarui metadata integrasi dan kebijakan akses.';
            }

            if (submitButton) {
                submitButton.textContent = 'Simpan Perubahan';
            }

            openModal(formModal);
            showFeedback(formFeedback, 'Memuat detail client API...');

            try {
                const record = await fetchRecord(uuid);
                fillForm(record);
                clearFeedback(formFeedback);
            } catch (errorPayload) {
                showFeedback(formFeedback, extractError(errorPayload));
            }
        };

        const openView = async (uuid) => {
            if (viewContent) {
                viewContent.innerHTML = '<div class="grid-loading">Memuat detail client API...</div>';
            }

            if (viewSubtitle) {
                viewSubtitle.textContent = 'Memuat data...';
            }

            openModal(viewModal);

            try {
                renderDetail(await fetchRecord(uuid));
            } catch (errorPayload) {
                if (viewContent) {
                    viewContent.innerHTML = `<div class="feedback">${escapeHtml(extractError(errorPayload))}</div>`;
                }
            }
        };

        const openTokenModal = async (uuid) => {
            state.activeTokenUuid = uuid;
            state.activeTokenValue = '';

            if (tokenForm) {
                tokenForm.reset();
                tokenForm.querySelectorAll('[name="abilities[]"]').forEach((input) => {
                    input.checked = input.value === '*';
                });
            }

            clearFeedback(tokenFeedback);
            if (tokenResult) {
                tokenResult.hidden = true;
            }

            if (tokenSecret) {
                tokenSecret.value = '';
            }

            try {
                const record = await fetchRecord(uuid);
                if (tokenSubtitle) {
                    tokenSubtitle.textContent = `Client ${record.name || '-'} (${record.code || '-'})`;
                }
            } catch (errorPayload) {
                if (tokenSubtitle) {
                    tokenSubtitle.textContent = extractError(errorPayload);
                }
            }

            openModal(tokenModal);
        };

        const destroyClient = async (uuid) => {
            const confirmed = window.confirm('Hapus client API ini? Semua token client ini akan ikut dicabut.');

            if (!confirmed) {
                return;
            }

            try {
                await fetchJson(`${config.list_endpoint}/${uuid}`, {
                    method: 'DELETE',
                });
                await loadRows();
            } catch (errorPayload) {
                window.alert(extractError(errorPayload));
            }
        };

        renderHeader();
        renderPagination();
        loadRows();

        gridSearch?.addEventListener('input', (event) => {
            window.clearTimeout(searchTimer);
            state.search = event.target.value.trim();
            searchTimer = window.setTimeout(() => {
                state.page = 1;
                loadRows();
            }, 280);
        });

        gridPerPage?.addEventListener('change', (event) => {
            state.perPage = Number(event.target.value || 10);
            state.page = 1;
            loadRows();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadRows();
        });

        nextButton?.addEventListener('click', () => {
            if (state.page >= Number(state.pagination.last_page || 1)) {
                return;
            }

            state.page += 1;
            loadRows();
        });

        createButton?.addEventListener('click', openCreate);

        root.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-api-client-action]');

            if (!trigger) {
                return;
            }

            const uuid = trigger.dataset.apiClientUuid;
            const action = trigger.dataset.apiClientAction;

            if (action === 'view') {
                openView(uuid);
            }

            if (action === 'edit') {
                openEdit(uuid);
            }

            if (action === 'token') {
                openTokenModal(uuid);
            }

            if (action === 'delete') {
                destroyClient(uuid);
            }
        });

        document.querySelectorAll('[data-superadmin-api-client-view-modal] [data-modal-close], [data-superadmin-api-client-form-modal] [data-modal-close], [data-superadmin-api-token-modal] [data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(viewModal);
                closeModal(formModal);
                closeModal(tokenModal);
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(viewModal);
                closeModal(formModal);
                closeModal(tokenModal);
            }
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFeedback(formFeedback);

            const payload = {
                name: form.querySelector('[name="name"]').value.trim(),
                code: form.querySelector('[name="code"]').value.trim(),
                owner_name: form.querySelector('[name="owner_name"]').value.trim() || null,
                owner_email: form.querySelector('[name="owner_email"]').value.trim() || null,
                description: form.querySelector('[name="description"]').value.trim() || null,
                allowed_ips: splitLines(form.querySelector('[name="allowed_ips"]').value),
                allowed_origins: splitLines(form.querySelector('[name="allowed_origins"]').value),
                rate_limit_per_minute: form.querySelector('[name="rate_limit_per_minute"]').value || null,
                rate_limit_per_day: form.querySelector('[name="rate_limit_per_day"]').value || null,
                expires_at: form.querySelector('[name="expires_at"]').value || null,
                is_active: form.querySelector('[name="is_active"]').checked,
            };

            submitButton.disabled = true;

            try {
                const url = state.mode === 'edit' && state.activeUuid
                    ? `${config.list_endpoint}/${state.activeUuid}`
                    : config.store_endpoint;
                const method = state.mode === 'edit' ? 'PATCH' : 'POST';

                await fetchJson(url, {
                    method,
                    body: JSON.stringify(payload),
                });

                closeModal(formModal);
                resetForm();
                await loadRows();
            } catch (errorPayload) {
                showFeedback(formFeedback, extractError(errorPayload));
            } finally {
                submitButton.disabled = false;
            }
        });

        tokenForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearFeedback(tokenFeedback);
            if (tokenResult) {
                tokenResult.hidden = true;
            }

            const abilities = Array.from(tokenForm.querySelectorAll('[name="abilities[]"]:checked')).map((input) => input.value);
            const endpoint = String(config.token_endpoint || '').replace('__client__', state.activeTokenUuid || '');

            tokenSubmitButton.disabled = true;

            try {
                const payload = await fetchJson(endpoint, {
                    method: 'POST',
                    body: JSON.stringify({
                        token_name: tokenForm.querySelector('[name="token_name"]').value.trim(),
                        abilities,
                        expires_at: tokenForm.querySelector('[name="expires_at"]').value || null,
                    }),
                });

                state.activeTokenValue = payload.data?.plain_text_token || '';

                if (tokenSecret) {
                    tokenSecret.value = state.activeTokenValue;
                    requestAnimationFrame(() => {
                        tokenSecret.focus();
                        tokenSecret.select();
                    });
                }

                if (tokenResult) {
                    tokenResult.hidden = false;
                }

                showFeedback(tokenFeedback, payload.message || 'Bearer token berhasil dibuat.', true);
                await loadRows();
            } catch (errorPayload) {
                showFeedback(tokenFeedback, extractError(errorPayload));
            } finally {
                tokenSubmitButton.disabled = false;
            }
        });

        copyTokenButton?.addEventListener('click', async () => {
            if (!state.activeTokenValue) {
                return;
            }

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(state.activeTokenValue);
                } else {
                    throw new Error('Clipboard API unavailable');
                }
                showFeedback(tokenFeedback, 'Token berhasil disalin ke clipboard.', true);
            } catch {
                if (tokenSecret) {
                    tokenSecret.focus();
                    tokenSecret.select();

                    try {
                        document.execCommand('copy');
                        showFeedback(tokenFeedback, 'Token berhasil disalin ke clipboard.', true);

                        return;
                    } catch {
                        showFeedback(tokenFeedback, 'Token sudah diblok. Tekan Ctrl+C atau Cmd+C untuk menyalin.', false);

                        return;
                    }
                }

                showFeedback(tokenFeedback, 'Gagal menyalin token. Salin manual dari panel token.', false);
            }
        });
    })();
</script>
</body>
</html>
