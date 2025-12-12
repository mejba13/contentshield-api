<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - ContentShield AI</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-primary: #030508;
            --bg-secondary: #080C14;
            --bg-card: #0C1018;
            --bg-elevated: #141A26;
            --accent-cyan: #00E5FF;
            --accent-cyan-muted: #00B8D4;
            --accent-cyan-glow: rgba(0, 229, 255, 0.12);
            --accent-violet: #7C4DFF;
            --accent-violet-glow: rgba(124, 77, 255, 0.12);
            --accent-rose: #FF5252;
            --text-primary: #FFFFFF;
            --text-secondary: #A0AEC0;
            --text-muted: #718096;
            --text-dim: #4A5568;
            --border-subtle: rgba(255, 255, 255, 0.06);
            --border-hover: rgba(255, 255, 255, 0.12);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --transition-fast: 120ms cubic-bezier(0.2, 0, 0, 1);
            --transition-base: 200ms cubic-bezier(0.2, 0, 0, 1);
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
            font-family: 'Space Grotesk', system-ui, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Background Effects */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: -2;
            background:
                radial-gradient(ellipse 100% 80% at 10% -30%, rgba(0, 229, 255, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse 80% 60% at 90% 110%, rgba(124, 77, 255, 0.07) 0%, transparent 50%),
                radial-gradient(ellipse 60% 50% at 50% 50%, rgba(0, 229, 255, 0.03) 0%, transparent 60%),
                var(--bg-primary);
        }

        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: -2;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 50%, black 20%, transparent 70%);
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

        /* Floating elements */
        .floating-shape {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: float 20s ease-in-out infinite;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: var(--accent-cyan);
            top: -200px;
            left: -100px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: var(--accent-violet);
            bottom: -150px;
            right: -100px;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.05); }
            50% { transform: translate(0, -20px) scale(1); }
            75% { transform: translate(-30px, -10px) scale(0.95); }
        }

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo */
        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-violet));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow:
                0 0 30px rgba(0, 229, 255, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            inset: 2px;
            background: linear-gradient(135deg, var(--bg-secondary), var(--bg-primary));
            border-radius: calc(var(--radius-md) - 2px);
        }

        .logo-icon svg {
            position: relative;
            z-index: 1;
            width: 26px;
            height: 26px;
            color: var(--accent-cyan);
        }

        .logo-text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.03em;
        }

        .logo-text span {
            color: var(--accent-cyan);
            text-shadow: 0 0 30px rgba(0, 229, 255, 0.5);
        }

        /* Login Card */
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            padding: 36px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.75rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.9375rem;
            font-family: inherit;
            transition: all var(--transition-fast);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-cyan-muted);
            background: rgba(0, 229, 255, 0.04);
            box-shadow: 0 0 0 3px var(--accent-cyan-glow);
        }

        .form-input::placeholder {
            color: var(--text-dim);
        }

        /* Input with icon */
        .input-group {
            position: relative;
        }

        .input-group .form-input {
            padding-left: 48px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .input-icon svg {
            width: 18px;
            height: 18px;
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            transition: color var(--transition-fast);
        }

        .password-toggle:hover {
            color: var(--text-secondary);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
        }

        /* Remember & Forgot */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            border: 1px solid var(--border-subtle);
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
        }

        .checkbox-group:hover .checkbox {
            border-color: var(--border-hover);
        }

        .checkbox-group input:checked + .checkbox {
            background: var(--accent-cyan);
            border-color: var(--accent-cyan);
        }

        .checkbox-group input:checked + .checkbox svg {
            opacity: 1;
        }

        .checkbox svg {
            width: 12px;
            height: 12px;
            color: var(--bg-primary);
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .checkbox-group input {
            display: none;
        }

        .checkbox-label {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .forgot-link {
            font-size: 0.8125rem;
            color: var(--accent-cyan);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .forgot-link:hover {
            color: var(--accent-cyan-muted);
        }

        /* Button */
        .btn-login {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-cyan-muted));
            color: var(--bg-primary);
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 700;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
            position: relative;
            overflow: hidden;
            box-shadow:
                0 4px 15px rgba(0, 229, 255, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow:
                0 6px 25px rgba(0, 229, 255, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .btn-login:hover::before {
            opacity: 1;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 28px 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: var(--border-subtle);
        }

        .divider-text {
            font-size: 0.75rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Social login */
        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-social:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--border-hover);
            color: var(--text-primary);
        }

        .btn-social svg {
            width: 18px;
            height: 18px;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .login-footer a {
            color: var(--accent-cyan);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-fast);
        }

        .login-footer a:hover {
            color: var(--accent-cyan-muted);
        }

        /* Error message */
        .error-message {
            background: rgba(255, 82, 82, 0.1);
            border: 1px solid rgba(255, 82, 82, 0.2);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.8125rem;
            color: var(--accent-rose);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Success message */
        .success-message {
            background: rgba(0, 230, 118, 0.1);
            border: 1px solid rgba(0, 230, 118, 0.2);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.8125rem;
            color: #00E676;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-grid"></div>
    <div class="noise-overlay"></div>
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>

    <div class="login-container">
        <!-- Logo -->
        <div class="login-logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div class="logo-text">Content<span>Shield</span></div>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to your dashboard</p>
            </div>

            @if ($errors->any())
            <div class="error-message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if (session('status'))
            <div class="success-message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-group">
                        <input type="checkbox" name="remember">
                        <span class="checkbox">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </span>
                        <span class="checkbox-label">Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">or continue with</span>
                <div class="divider-line"></div>
            </div>

            <div class="social-buttons">
                <button type="button" class="btn-social">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google
                </button>
                <button type="button" class="btn-social">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    GitHub
                </button>
            </div>
        </div>

        <div class="login-footer">
            Don't have an account? <a href="#">Contact Sales</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
    </script>
</body>
</html>
