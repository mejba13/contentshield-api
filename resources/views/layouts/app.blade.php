<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ContentShield AI')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Core Palette */
            --bg-primary: #050810;
            --bg-secondary: #0A0F1C;
            --bg-card: #0D1224;
            --bg-card-hover: #111827;
            --bg-elevated: #151D2E;

            /* Accent Colors */
            --accent-cyan: #00D4FF;
            --accent-cyan-glow: rgba(0, 212, 255, 0.15);
            --accent-emerald: #10B981;
            --accent-amber: #F59E0B;
            --accent-rose: #F43F5E;
            --accent-violet: #8B5CF6;

            /* Text */
            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --text-muted: #64748B;

            /* Borders */
            --border-subtle: rgba(148, 163, 184, 0.1);
            --border-hover: rgba(148, 163, 184, 0.2);

            /* Shadows */
            --shadow-card: 0 4px 24px rgba(0, 0, 0, 0.4);
            --shadow-glow: 0 0 40px var(--accent-cyan-glow);

            /* Spacing */
            --sidebar-width: 280px;
            --header-height: 72px;

            /* Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;

            /* Transitions */
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 400ms cubic-bezier(0.4, 0, 0.2, 1);
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
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Background Effects */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(ellipse 80% 50% at 20% -20%, rgba(0, 212, 255, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse 60% 40% at 80% 100%, rgba(139, 92, 246, 0.06) 0%, transparent 50%),
                var(--bg-primary);
        }

        .noise-overlay {
            position: fixed;
            inset: 0;
            z-index: -1;
            opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        /* Typography */
        .font-serif { font-family: 'Instrument Serif', serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

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
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform var(--transition-base);
        }

        .sidebar-header {
            height: var(--header-height);
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-violet));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .logo::before {
            content: '';
            position: absolute;
            inset: 2px;
            background: var(--bg-secondary);
            border-radius: calc(var(--radius-md) - 2px);
        }

        .logo svg {
            position: relative;
            z-index: 1;
            width: 22px;
            height: 22px;
        }

        .brand-name {
            font-weight: 600;
            font-size: 1.125rem;
            letter-spacing: -0.02em;
        }

        .brand-name span { color: var(--accent-cyan); }

        .sidebar-nav {
            flex: 1;
            padding: 24px 16px;
            overflow-y: auto;
        }

        .nav-section { margin-bottom: 32px; }

        .nav-section-title {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9375rem;
            font-weight: 500;
            transition: all var(--transition-fast);
            position: relative;
            cursor: pointer;
        }

        .nav-item:hover {
            color: var(--text-primary);
            background: var(--bg-card);
        }

        .nav-item.active {
            color: var(--accent-cyan);
            background: var(--accent-cyan-glow);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--accent-cyan);
            border-radius: 0 4px 4px 0;
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
            opacity: 0.8;
        }

        .nav-item.active svg { opacity: 1; }

        .nav-badge {
            margin-left: auto;
            background: var(--accent-rose);
            color: white;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 100px;
            min-width: 22px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-subtle);
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

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .header {
            height: var(--header-height);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-subtle);
            background: rgba(5, 8, 16, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .page-title {
            font-family: 'Instrument Serif', serif;
            font-size: 1.75rem;
            font-weight: 400;
            letter-spacing: -0.02em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            min-width: 280px;
            transition: all var(--transition-fast);
        }

        .search-box:focus-within {
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 3px var(--accent-cyan-glow);
        }

        .search-box svg {
            width: 18px;
            height: 18px;
            color: var(--text-muted);
        }

        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .search-box input::placeholder { color: var(--text-muted); }

        .search-shortcut {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.6875rem;
            color: var(--text-muted);
            padding: 4px 8px;
            background: var(--bg-elevated);
            border-radius: 6px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all var(--transition-fast);
            position: relative;
        }

        .icon-btn:hover {
            color: var(--text-primary);
            background: var(--bg-card-hover);
            border-color: var(--border-hover);
        }

        .icon-btn svg { width: 20px; height: 20px; }

        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--accent-rose);
            border-radius: 50%;
            border: 2px solid var(--bg-card);
        }

        /* Page Content */
        .page-content {
            padding: 32px;
            max-width: 1600px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Card Base */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            overflow: hidden;
            animation: slideUp 0.5s ease-out both;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .card-title {
            font-family: 'Instrument Serif', serif;
            font-size: 1.25rem;
            font-weight: 400;
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body { padding: 24px; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            text-decoration: none;
        }

        .btn svg { width: 18px; height: 18px; }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-cyan), #00A8CC);
            color: var(--bg-primary);
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4);
        }

        .btn-secondary {
            background: var(--bg-elevated);
            color: var(--text-primary);
            border: 1px solid var(--border-subtle);
        }

        .btn-secondary:hover {
            background: var(--bg-card-hover);
            border-color: var(--border-hover);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-secondary);
            padding: 8px 12px;
        }

        .btn-ghost:hover {
            color: var(--text-primary);
            background: var(--bg-card);
        }

        .btn-danger {
            background: var(--accent-rose);
            color: white;
        }

        .btn-danger:hover {
            filter: brightness(1.1);
        }

        .btn-sm { padding: 6px 12px; font-size: 0.8125rem; }
        .btn-lg { padding: 14px 28px; font-size: 1rem; }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.protected {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-emerald);
        }

        .status-badge.monitoring {
            background: rgba(0, 212, 255, 0.1);
            color: var(--accent-cyan);
        }

        .status-badge.alert {
            background: rgba(244, 63, 94, 0.1);
            color: var(--accent-rose);
        }

        .status-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-amber);
        }

        .status-badge.resolved {
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-violet);
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Tables */
        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 16px 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            background: var(--bg-elevated);
            border-bottom: 1px solid var(--border-subtle);
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-subtle);
            font-size: 0.875rem;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--bg-card-hover); }

        /* Form Elements */
        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.875rem;
            font-family: inherit;
            transition: all var(--transition-fast);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 3px var(--accent-cyan-glow);
        }

        .form-textarea { resize: vertical; min-height: 120px; }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 4px;
            background: var(--bg-elevated);
            padding: 4px;
            border-radius: var(--radius-md);
        }

        .tab {
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            background: transparent;
        }

        .tab:hover { color: var(--text-primary); }

        .tab.active {
            background: var(--bg-card);
            color: var(--text-primary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            height: 6px;
            background: var(--bg-elevated);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-emerald));
            border-radius: 3px;
            transition: width var(--transition-slow);
        }

        /* Fingerprint Code */
        .fingerprint {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
            background: var(--bg-elevated);
            padding: 4px 8px;
            border-radius: 4px;
        }

        /* Shield Animation */
        .shield-pulse {
            animation: shieldPulse 3s ease-in-out infinite;
        }

        @keyframes shieldPulse {
            0%, 100% { filter: drop-shadow(0 0 8px rgba(0, 212, 255, 0.3)); }
            50% { filter: drop-shadow(0 0 16px rgba(0, 212, 255, 0.6)); }
        }

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
        .text-cyan { color: var(--accent-cyan); }
        .text-emerald { color: var(--accent-emerald); }
        .text-rose { color: var(--accent-rose); }
        .text-amber { color: var(--accent-amber); }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }

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
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 200;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-base);
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
            max-width: 560px;
            max-height: 90vh;
            overflow: hidden;
            transform: scale(0.95) translateY(20px);
            transition: transform var(--transition-base);
        }

        .modal-overlay.active .modal {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .modal-title {
            font-family: 'Instrument Serif', serif;
            font-size: 1.5rem;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
        }

        .modal-close:hover {
            background: var(--bg-elevated);
            color: var(--text-primary);
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            max-height: 60vh;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid var(--border-subtle);
        }

        @yield('styles')
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
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
                <div class="user-card">
                    <div class="user-avatar">JD</div>
                    <div class="user-info">
                        <div class="user-name">John Doe</div>
                        <div class="user-plan">Agency Plan</div>
                    </div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="color: var(--text-muted);">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
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
    </script>

    @yield('scripts')
</body>
</html>
