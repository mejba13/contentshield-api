<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ContentShield AI')</title>

    <!-- Fonts - Premium Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Core Palette - Deep Space Theme */
            --bg-primary: #030508;
            --bg-secondary: #080C14;
            --bg-card: #0C1018;
            --bg-card-hover: #10151F;
            --bg-elevated: #141A26;
            --bg-glass: rgba(12, 16, 24, 0.85);

            /* Accent Colors - Luminous */
            --accent-cyan: #00E5FF;
            --accent-cyan-muted: #00B8D4;
            --accent-cyan-glow: rgba(0, 229, 255, 0.12);
            --accent-emerald: #00E676;
            --accent-emerald-glow: rgba(0, 230, 118, 0.12);
            --accent-amber: #FFB300;
            --accent-amber-glow: rgba(255, 179, 0, 0.12);
            --accent-rose: #FF5252;
            --accent-rose-glow: rgba(255, 82, 82, 0.12);
            --accent-violet: #7C4DFF;
            --accent-violet-glow: rgba(124, 77, 255, 0.12);

            /* Text - High Contrast */
            --text-primary: #FFFFFF;
            --text-secondary: #A0AEC0;
            --text-muted: #718096;
            --text-dim: #4A5568;

            /* Borders */
            --border-subtle: rgba(255, 255, 255, 0.06);
            --border-hover: rgba(255, 255, 255, 0.12);
            --border-active: rgba(0, 229, 255, 0.3);

            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-card: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-glow-cyan: 0 0 40px rgba(0, 229, 255, 0.15);
            --shadow-glow-violet: 0 0 40px rgba(124, 77, 255, 0.15);
            --shadow-inset: inset 0 1px 0 rgba(255, 255, 255, 0.05);

            /* Spacing */
            --sidebar-width: 272px;
            --header-height: 68px;

            /* Radius */
            --radius-xs: 6px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;

            /* Transitions */
            --transition-fast: 120ms cubic-bezier(0.2, 0, 0, 1);
            --transition-base: 200ms cubic-bezier(0.2, 0, 0, 1);
            --transition-slow: 350ms cubic-bezier(0.2, 0, 0, 1);
            --transition-spring: 500ms cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        body {
            font-family: 'Space Grotesk', system-ui, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
            letter-spacing: -0.01em;
        }

        /* Background Effects - Atmospheric */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: -2;
            background:
                radial-gradient(ellipse 100% 80% at 10% -30%, rgba(0, 229, 255, 0.07) 0%, transparent 50%),
                radial-gradient(ellipse 80% 60% at 90% 110%, rgba(124, 77, 255, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(0, 229, 255, 0.02) 0%, transparent 60%),
                var(--bg-primary);
        }

        .noise-overlay {
            position: fixed;
            inset: 0;
            z-index: -1;
            opacity: 0.025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            pointer-events: none;
            mix-blend-mode: overlay;
        }

        /* Animated Grid Background */
        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: -2;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, black 30%, transparent 70%);
        }

        /* Typography */
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-sans { font-family: 'Space Grotesk', system-ui, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', 'SF Mono', monospace; }

        /* Selection */
        ::selection {
            background: rgba(0, 229, 255, 0.25);
            color: var(--text-primary);
        }

        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform var(--transition-slow);
        }

        .sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 1px;
            background: linear-gradient(180deg, var(--accent-cyan-glow), transparent 30%, transparent 70%, var(--accent-violet-glow));
            opacity: 0.5;
        }

        .sidebar-header {
            height: var(--header-height);
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid var(--border-subtle);
            position: relative;
        }

        .logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-violet));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 0 20px rgba(0, 229, 255, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .logo::before {
            content: '';
            position: absolute;
            inset: 2px;
            background: linear-gradient(135deg, var(--bg-secondary), var(--bg-primary));
            border-radius: calc(var(--radius-md) - 2px);
        }

        .logo svg {
            position: relative;
            z-index: 1;
            width: 22px;
            height: 22px;
        }

        .brand-name {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            font-size: 1.125rem;
            letter-spacing: -0.03em;
        }

        .brand-name span {
            color: var(--accent-cyan);
            text-shadow: 0 0 20px rgba(0, 229, 255, 0.4);
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 14px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .nav-section { margin-bottom: 28px; }

        .nav-section-title {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-dim);
            padding: 0 12px;
            margin-bottom: 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all var(--transition-fast);
            position: relative;
            cursor: pointer;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-item.active {
            color: var(--accent-cyan);
            background: var(--accent-cyan-glow);
            box-shadow: var(--shadow-inset);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 22px;
            background: var(--accent-cyan);
            border-radius: 0 3px 3px 0;
            box-shadow: 0 0 12px var(--accent-cyan);
        }

        .nav-item svg {
            width: 19px;
            height: 19px;
            opacity: 0.7;
            flex-shrink: 0;
        }

        .nav-item:hover svg { opacity: 0.9; }
        .nav-item.active svg {
            opacity: 1;
            filter: drop-shadow(0 0 4px var(--accent-cyan));
        }

        .nav-badge {
            margin-left: auto;
            background: linear-gradient(135deg, var(--accent-rose), #FF1744);
            color: white;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.625rem;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 100px;
            min-width: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(255, 82, 82, 0.3);
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-subtle);
            position: relative;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--bg-card);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .user-card:hover { background: var(--bg-card-hover); }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--accent-emerald), var(--accent-cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-info { flex: 1; min-width: 0; }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-plan {
            font-size: 0.75rem;
            color: var(--accent-cyan);
            font-weight: 500;
        }

        .user-menu {
            position: absolute;
            bottom: 100%;
            left: 0;
            right: 0;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 6px;
            margin-bottom: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all var(--transition-fast);
            box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.4);
            z-index: 100;
        }

        .user-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            background: none;
            width: 100%;
            font-family: inherit;
        }

        .user-menu-item:hover {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-primary);
        }

        .user-menu-item svg {
            width: 16px;
            height: 16px;
        }

        .user-menu-item.logout-btn {
            color: var(--accent-rose);
        }

        .user-menu-item.logout-btn:hover {
            background: var(--accent-rose-glow);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .header {
            height: var(--header-height);
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-subtle);
            background: var(--bg-glass);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-subtle), transparent);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .page-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.625rem;
            font-weight: 500;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            min-width: 260px;
            transition: all var(--transition-fast);
        }

        .search-box:focus-within {
            border-color: var(--accent-cyan-muted);
            background: rgba(0, 229, 255, 0.04);
            box-shadow: 0 0 0 3px var(--accent-cyan-glow);
        }

        .search-box svg {
            width: 17px;
            height: 17px;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
            font-size: 0.8125rem;
            font-family: inherit;
        }

        .search-box input::placeholder { color: var(--text-dim); }

        .search-shortcut {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.625rem;
            font-weight: 500;
            color: var(--text-dim);
            padding: 3px 7px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: var(--radius-xs);
            border: 1px solid var(--border-subtle);
        }

        .icon-btn {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            cursor: pointer;
            transition: all var(--transition-fast);
            position: relative;
        }

        .icon-btn:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--border-hover);
            transform: translateY(-1px);
        }

        .icon-btn svg { width: 18px; height: 18px; }

        .notification-dot {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 8px;
            height: 8px;
            background: var(--accent-rose);
            border-radius: 50%;
            border: 2px solid var(--bg-card);
            animation: notificationPulse 2s infinite;
        }

        @keyframes notificationPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Page Content */
        .page-content {
            padding: 28px;
            max-width: 1560px;
            animation: fadeIn 0.5s ease-out;
        }

        /* Page Header with Action Buttons */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .page-description {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 4px;
            margin-bottom: 16px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Card Base */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            overflow: hidden;
            animation: slideUp 0.4s ease-out both;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-subtle);
            background: rgba(255, 255, 255, 0.01);
        }

        .card-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.125rem;
            font-weight: 500;
            letter-spacing: -0.01em;
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body { padding: 22px; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-size: 0.8125rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn svg { width: 16px; height: 16px; }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-cyan-muted));
            color: var(--bg-primary);
            font-weight: 700;
            box-shadow:
                0 2px 8px rgba(0, 229, 255, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow:
                0 4px 16px rgba(0, 229, 255, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .btn-primary:hover::before { opacity: 1; }

        .btn-primary:active { transform: translateY(0); }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-primary);
            border: 1px solid var(--border-subtle);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--border-hover);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            padding: 8px 10px;
        }

        .btn-ghost:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.04);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--accent-rose), #FF1744);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 82, 82, 0.25);
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(255, 82, 82, 0.35);
        }

        .btn-sm { padding: 6px 10px; font-size: 0.75rem; }
        .btn-lg { padding: 12px 24px; font-size: 0.875rem; }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 100px;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-badge.protected {
            background: var(--accent-emerald-glow);
            color: var(--accent-emerald);
            box-shadow: inset 0 0 0 1px rgba(0, 230, 118, 0.2);
        }

        .status-badge.monitoring {
            background: var(--accent-cyan-glow);
            color: var(--accent-cyan);
            box-shadow: inset 0 0 0 1px rgba(0, 229, 255, 0.2);
        }

        .status-badge.alert {
            background: var(--accent-rose-glow);
            color: var(--accent-rose);
            box-shadow: inset 0 0 0 1px rgba(255, 82, 82, 0.2);
        }

        .status-badge.pending {
            background: var(--accent-amber-glow);
            color: var(--accent-amber);
            box-shadow: inset 0 0 0 1px rgba(255, 179, 0, 0.2);
        }

        .status-badge.resolved {
            background: var(--accent-violet-glow);
            color: var(--accent-violet);
            box-shadow: inset 0 0 0 1px rgba(124, 77, 255, 0.2);
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            animation: statusPulse 2s ease-in-out infinite;
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.9); }
        }

        /* Tables */
        .table-wrapper {
            overflow-x: auto;
            scrollbar-width: thin;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 18px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-dim);
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border-subtle);
        }

        td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-subtle);
            font-size: 0.8125rem;
        }

        tr:last-child td { border-bottom: none; }

        tr {
            transition: background var(--transition-fast);
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Form Elements */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 11px 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.8125rem;
            font-family: inherit;
            transition: all var(--transition-fast);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent-cyan-muted);
            background: rgba(0, 229, 255, 0.04);
            box-shadow: 0 0 0 3px var(--accent-cyan-glow);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: var(--text-dim);
        }

        .form-textarea { resize: vertical; min-height: 100px; }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23718096' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 2px;
            background: rgba(255, 255, 255, 0.03);
            padding: 3px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-subtle);
        }

        .tab {
            padding: 7px 14px;
            border-radius: var(--radius-xs);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            background: transparent;
        }

        .tab:hover { color: var(--text-secondary); }

        .tab.active {
            background: var(--bg-card);
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
        }

        /* Dropdown */
        .dropdown { position: relative; }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 8px;
            box-shadow: var(--shadow-card);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all var(--transition-fast);
            z-index: 100;
        }

        .dropdown:hover .dropdown-menu,
        .dropdown-menu:hover {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            font-size: 0.875rem;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .dropdown-item:hover {
            background: var(--bg-elevated);
            color: var(--text-primary);
        }

        .dropdown-item svg { width: 16px; height: 16px; }

        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            text-align: center;
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            color: var(--text-muted);
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.125rem;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 20px;
        }

        /* Progress Bar */
        .progress-bar {
            height: 5px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-emerald));
            border-radius: 3px;
            transition: width var(--transition-slow);
            box-shadow: 0 0 10px rgba(0, 229, 255, 0.3);
        }

        /* Fingerprint Code */
        .fingerprint {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.6875rem;
            font-weight: 500;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.04);
            padding: 4px 8px;
            border-radius: var(--radius-xs);
            border: 1px solid var(--border-subtle);
            letter-spacing: 0.02em;
        }

        /* Shield Animation */
        .shield-pulse {
            animation: shieldPulse 3s ease-in-out infinite;
        }

        @keyframes shieldPulse {
            0%, 100% {
                filter: drop-shadow(0 0 6px rgba(0, 229, 255, 0.3));
            }
            50% {
                filter: drop-shadow(0 0 12px rgba(0, 229, 255, 0.6));
            }
        }

        /* Glow Effects */
        .glow-cyan { box-shadow: 0 0 20px var(--accent-cyan-glow); }
        .glow-violet { box-shadow: 0 0 20px var(--accent-violet-glow); }
        .glow-emerald { box-shadow: 0 0 20px var(--accent-emerald-glow); }

        /* Grid Utilities */
        .grid { display: grid; gap: 24px; }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

        /* Flex Utilities */
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .gap-6 { gap: 24px; }

        /* Spacing */
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .mb-8 { margin-bottom: 32px; }
        .mt-6 { margin-top: 24px; }

        /* Text */
        .text-muted { color: var(--text-muted); }
        .text-secondary { color: var(--text-secondary); }
        .text-dim { color: var(--text-dim); }
        .text-cyan { color: var(--accent-cyan); }
        .text-emerald { color: var(--accent-emerald); }
        .text-rose { color: var(--accent-rose); }
        .text-amber { color: var(--accent-amber); }
        .text-violet { color: var(--accent-violet); }
        .text-sm { font-size: 0.8125rem; }
        .text-xs { font-size: 0.75rem; }
        .text-2xs { font-size: 0.6875rem; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .tracking-tight { letter-spacing: -0.02em; }
        .tracking-wide { letter-spacing: 0.05em; }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .grid-cols-4 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .header { padding: 0 20px; }
            .search-box { display: none; }
            .mobile-menu-btn { display: flex; }
            .grid-cols-3 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .grid-cols-2,
            .grid-cols-3,
            .grid-cols-4 { grid-template-columns: 1fr; }
            .page-content { padding: 20px; }
            .card-body { padding: 16px; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-secondary); }
        ::-webkit-scrollbar-thumb { background: var(--bg-elevated); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(3, 5, 8, 0.85);
            backdrop-filter: blur(8px) saturate(150%);
            -webkit-backdrop-filter: blur(8px) saturate(150%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 200;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-base);
            padding: 24px;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 520px;
            max-height: 85vh;
            overflow: hidden;
            transform: scale(0.96) translateY(16px);
            transition: transform var(--transition-slow);
            box-shadow:
                0 24px 48px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        }

        .modal-overlay.active .modal {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .modal-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.25rem;
            font-weight: 500;
        }

        .modal-close {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: var(--radius-xs);
            transition: all var(--transition-fast);
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text-primary);
        }

        .modal-body {
            padding: 22px;
            overflow-y: auto;
            max-height: 55vh;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 18px 22px;
            border-top: 1px solid var(--border-subtle);
            background: rgba(255, 255, 255, 0.01);
        }

        @yield('styles')
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-grid"></div>
    <div class="noise-overlay"></div>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shield-pulse">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="url(#shieldGradient)"/>
                        <defs>
                            <linearGradient id="shieldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#00D4FF"/>
                                <stop offset="100%" stop-color="#8B5CF6"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="brand-name">Content<span>Shield</span></div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="{{ route('dashboard') }}" class="nav-item @if(request()->routeIs('dashboard')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('content.index') }}" class="nav-item @if(request()->routeIs('content.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Protected Content
                        <span class="nav-badge">12</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Monitoring</div>
                    <a href="{{ route('scans.index') }}" class="nav-item @if(request()->routeIs('scans.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        Scan Results
                    </a>
                    <a href="{{ route('alerts.index') }}" class="nav-item @if(request()->routeIs('alerts.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        Plagiarism Alerts
                        <span class="nav-badge">3</span>
                    </a>
                    <a href="{{ route('analytics.index') }}" class="nav-item @if(request()->routeIs('analytics.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        Analytics
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Actions</div>
                    <a href="{{ route('dmca.index') }}" class="nav-item @if(request()->routeIs('dmca.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        DMCA Requests
                    </a>
                    <a href="{{ route('schedule.index') }}" class="nav-item @if(request()->routeIs('schedule.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Schedule Scans
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="{{ route('settings.index') }}" class="nav-item @if(request()->routeIs('settings.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                        Settings
                    </a>
                    <a href="{{ route('api-keys.index') }}" class="nav-item @if(request()->routeIs('api-keys.*')) active @endif">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                        </svg>
                        API Keys
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-card" onclick="document.getElementById('user-menu').classList.toggle('show')">
                    <div class="user-avatar">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="user-plan">Agency Plan</div>
                    </div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="color: var(--text-muted);">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
                <div id="user-menu" class="user-menu">
                    <a href="{{ route('settings.index') }}" class="user-menu-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                        Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="user-menu-item logout-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="header-actions">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input type="text" placeholder="Search content, scans, alerts...">
                        <span class="search-shortcut">&#8984;K</span>
                    </div>

                    <button class="icon-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                        <span class="notification-dot"></span>
                    </button>

                    <button class="icon-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </button>
                </div>
            </header>

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('.search-box input')?.focus();
            }
        });

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('user-menu');
            const userCard = document.querySelector('.user-card');
            if (userMenu && userCard && !userCard.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.remove('show');
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
