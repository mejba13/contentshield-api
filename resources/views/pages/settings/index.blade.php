@extends('layouts.app')

@section('title', 'Settings - ContentShield AI')
@section('page-title', 'Settings')

@section('styles')
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 32px;
    }

    .settings-nav {
        position: sticky;
        top: 104px;
        align-self: start;
    }

    .settings-nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.9375rem;
        font-weight: 500;
        cursor: pointer;
        transition: all var(--transition-fast);
        margin-bottom: 4px;
    }

    .settings-nav-item:hover {
        color: var(--text-primary);
        background: var(--bg-card);
    }

    .settings-nav-item.active {
        color: var(--accent-cyan);
        background: var(--accent-cyan-glow);
    }

    .settings-nav-item svg {
        width: 20px;
        height: 20px;
    }

    .settings-content {
        max-width: 720px;
    }

    .settings-section {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        margin-bottom: 24px;
        animation: slideUp 0.5s ease-out both;
    }

    .settings-section-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
    }

    .settings-section-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
        margin-bottom: 4px;
    }

    .settings-section-desc {
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .settings-section-body {
        padding: 24px;
    }

    .setting-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 16px 0;
        border-bottom: 1px solid var(--border-subtle);
    }

    .setting-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .setting-row:first-child {
        padding-top: 0;
    }

    .setting-info {
        flex: 1;
        max-width: 400px;
    }

    .setting-label {
        font-weight: 500;
        margin-bottom: 4px;
    }

    .setting-desc {
        font-size: 0.8125rem;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .setting-control {
        flex-shrink: 0;
        margin-left: 24px;
    }

    /* Toggle Switch */
    .toggle {
        position: relative;
        width: 48px;
        height: 26px;
        background: var(--bg-elevated);
        border-radius: 13px;
        cursor: pointer;
        transition: background var(--transition-fast);
    }

    .toggle.active {
        background: var(--accent-cyan);
    }

    .toggle::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: transform var(--transition-fast);
    }

    .toggle.active::after {
        transform: translateX(22px);
    }

    /* Select Input */
    .setting-select {
        padding: 10px 40px 10px 14px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: 0.875rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        min-width: 180px;
        cursor: pointer;
    }

    .setting-select:focus {
        outline: none;
        border-color: var(--accent-cyan);
    }

    /* Plan Card */
    .plan-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
    }

    .plan-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-violet));
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .plan-icon svg {
        width: 24px;
        height: 24px;
        color: white;
    }

    .plan-details {
        flex: 1;
    }

    .plan-name {
        font-weight: 600;
        font-size: 1.125rem;
        margin-bottom: 4px;
    }

    .plan-features {
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .plan-price {
        text-align: right;
    }

    .plan-amount {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .plan-period {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* Danger Zone */
    .danger-zone {
        border-color: rgba(244, 63, 94, 0.3);
    }

    .danger-zone .settings-section-header {
        border-color: rgba(244, 63, 94, 0.3);
    }

    .danger-zone .settings-section-title {
        color: var(--accent-rose);
    }

    @media (max-width: 1024px) {
        .settings-layout {
            grid-template-columns: 1fr;
        }

        .settings-nav {
            position: static;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .settings-nav-item {
            margin-bottom: 0;
        }
    }

    @media (max-width: 768px) {
        .setting-row {
            flex-direction: column;
            gap: 12px;
        }

        .setting-control {
            margin-left: 0;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="settings-layout">
        <nav class="settings-nav">
            <a href="#general" class="settings-nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
                General
            </a>
            <a href="#notifications" class="settings-nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                Notifications
            </a>
            <a href="#scanning" class="settings-nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
                Scanning
            </a>
            <a href="#billing" class="settings-nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Billing
            </a>
            <a href="#danger" class="settings-nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Danger Zone
            </a>
        </nav>

        <div class="settings-content">
            <section id="general" class="settings-section" style="animation-delay: 0.1s">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">General Settings</h2>
                    <p class="settings-section-desc">Configure your account preferences</p>
                </div>
                <div class="settings-section-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Email Address</div>
                            <div class="setting-desc">Your primary email for notifications</div>
                        </div>
                        <div class="setting-control">
                            <input type="email" class="form-input" value="john@example.com" style="width: 240px;">
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Time Zone</div>
                            <div class="setting-desc">Used for scheduling and reports</div>
                        </div>
                        <div class="setting-control">
                            <select class="setting-select">
                                <option>UTC (GMT+0)</option>
                                <option selected>EST (GMT-5)</option>
                                <option>PST (GMT-8)</option>
                                <option>CET (GMT+1)</option>
                            </select>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Language</div>
                            <div class="setting-desc">Interface language preference</div>
                        </div>
                        <div class="setting-control">
                            <select class="setting-select">
                                <option selected>English</option>
                                <option>Spanish</option>
                                <option>French</option>
                                <option>German</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <section id="notifications" class="settings-section" style="animation-delay: 0.15s">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Notifications</h2>
                    <p class="settings-section-desc">Manage how you receive alerts</p>
                </div>
                <div class="settings-section-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Email Alerts</div>
                            <div class="setting-desc">Receive email notifications for new plagiarism matches</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle active" onclick="this.classList.toggle('active')"></div>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">DMCA Updates</div>
                            <div class="setting-desc">Get notified when DMCA requests change status</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle active" onclick="this.classList.toggle('active')"></div>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Weekly Reports</div>
                            <div class="setting-desc">Receive a weekly summary of protection activity</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle" onclick="this.classList.toggle('active')"></div>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Alert Threshold</div>
                            <div class="setting-desc">Minimum similarity to trigger an alert</div>
                        </div>
                        <div class="setting-control">
                            <select class="setting-select">
                                <option>50% and above</option>
                                <option selected>70% and above</option>
                                <option>90% and above</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <section id="scanning" class="settings-section" style="animation-delay: 0.2s">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Scanning Preferences</h2>
                    <p class="settings-section-desc">Configure automated scanning behavior</p>
                </div>
                <div class="settings-section-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Auto-Scan New Content</div>
                            <div class="setting-desc">Automatically scan content when added</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle active" onclick="this.classList.toggle('active')"></div>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Scan Frequency</div>
                            <div class="setting-desc">How often to check for plagiarism</div>
                        </div>
                        <div class="setting-control">
                            <select class="setting-select">
                                <option>Every 6 hours</option>
                                <option selected>Daily</option>
                                <option>Weekly</option>
                            </select>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">AI Matching</div>
                            <div class="setting-desc">Use AI for advanced content comparison</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle active" onclick="this.classList.toggle('active')"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="billing" class="settings-section" style="animation-delay: 0.25s">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Billing & Plan</h2>
                    <p class="settings-section-desc">Manage your subscription</p>
                </div>
                <div class="settings-section-body">
                    <div class="plan-card">
                        <div class="plan-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        </div>
                        <div class="plan-details">
                            <div class="plan-name">Agency Plan</div>
                            <div class="plan-features">Unlimited content, hourly scans, AI matching, priority support</div>
                        </div>
                        <div class="plan-price">
                            <div class="plan-amount">$49</div>
                            <div class="plan-period">/month</div>
                        </div>
                    </div>
                    <div style="margin-top: 16px;">
                        <button class="btn btn-secondary">Manage Subscription</button>
                    </div>
                </div>
            </section>

            <section id="danger" class="settings-section danger-zone" style="animation-delay: 0.3s">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Danger Zone</h2>
                    <p class="settings-section-desc">Irreversible actions</p>
                </div>
                <div class="settings-section-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Delete All Content</div>
                            <div class="setting-desc">Remove all protected content and scan history</div>
                        </div>
                        <div class="setting-control">
                            <button class="btn btn-danger btn-sm">Delete All</button>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Close Account</div>
                            <div class="setting-desc">Permanently delete your account and all data</div>
                        </div>
                        <div class="setting-control">
                            <button class="btn btn-danger btn-sm">Close Account</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Smooth scroll to sections
    document.querySelectorAll('.settings-nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endsection
