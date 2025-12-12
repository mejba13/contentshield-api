@extends('layouts.app')

@section('title', 'Settings - ContentShield AI')
@section('page-title', 'Settings')

@section('styles')
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 28px;
    }

    .settings-nav {
        position: sticky;
        top: 100px;
        align-self: start;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 12px;
    }

    .settings-nav::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.06), transparent);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    .settings-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: var(--radius-sm);
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.8125rem;
        font-weight: 500;
        cursor: pointer;
        transition: all var(--transition-fast);
        margin-bottom: 2px;
    }

    .settings-nav-item:hover {
        color: var(--text-secondary);
        background: rgba(255, 255, 255, 0.03);
    }

    .settings-nav-item.active {
        color: var(--accent-cyan);
        background: var(--accent-cyan-glow);
    }

    .settings-nav-item svg {
        width: 17px;
        height: 17px;
        opacity: 0.8;
    }

    .settings-nav-item.active svg {
        opacity: 1;
    }

    .settings-content {
        max-width: 100%;
    }

    .settings-section {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        margin-bottom: 20px;
        animation: slideUp 0.4s ease-out both;
        position: relative;
    }

    .settings-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.06), transparent);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    .settings-section-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border-subtle);
    }

    .settings-section-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.125rem;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .settings-section-desc {
        font-size: 0.75rem;
        color: var(--text-dim);
    }

    .settings-section-body {
        padding: 20px 22px;
    }

    .setting-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 14px 0;
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
        max-width: 360px;
    }

    .setting-label {
        font-weight: 600;
        font-size: 0.8125rem;
        margin-bottom: 3px;
    }

    .setting-desc {
        font-size: 0.75rem;
        color: var(--text-dim);
        line-height: 1.5;
    }

    .setting-control {
        flex-shrink: 0;
        margin-left: 20px;
    }

    /* Toggle Switch */
    .toggle {
        position: relative;
        width: 44px;
        height: 24px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        cursor: pointer;
        transition: all var(--transition-fast);
        border: 1px solid var(--border-subtle);
    }

    .toggle:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .toggle.active {
        background: var(--accent-cyan);
        border-color: var(--accent-cyan);
        box-shadow: 0 0 12px var(--accent-cyan-glow);
    }

    .toggle::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background: white;
        border-radius: 50%;
        transition: transform var(--transition-fast);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .toggle.active::after {
        transform: translateX(20px);
    }

    /* Select Input */
    .setting-select {
        padding: 9px 36px 9px 12px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        font-size: 0.8125rem;
        font-family: inherit;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23718096' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        min-width: 160px;
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .setting-select:hover {
        border-color: var(--border-hover);
    }

    .setting-select:focus {
        outline: none;
        border-color: var(--accent-cyan-muted);
        background: rgba(0, 229, 255, 0.04);
        box-shadow: 0 0 0 3px var(--accent-cyan-glow);
    }

    /* Plan Card */
    .plan-card {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 18px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        position: relative;
        overflow: hidden;
    }

    .plan-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, var(--accent-cyan-glow), var(--accent-violet-glow));
    }

    .plan-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-violet));
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 20px rgba(0, 229, 255, 0.2);
    }

    .plan-icon svg {
        width: 22px;
        height: 22px;
        color: white;
    }

    .plan-details {
        flex: 1;
    }

    .plan-name {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 3px;
    }

    .plan-features {
        font-size: 0.75rem;
        color: var(--text-dim);
    }

    .plan-price {
        text-align: right;
    }

    .plan-amount {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.375rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-violet));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .plan-period {
        font-size: 0.6875rem;
        color: var(--text-dim);
    }

    /* Danger Zone */
    .danger-zone {
        border-color: rgba(255, 82, 82, 0.2);
    }

    .danger-zone::before {
        background: linear-gradient(90deg, transparent, rgba(255, 82, 82, 0.2), transparent);
    }

    .danger-zone .settings-section-header {
        border-color: rgba(255, 82, 82, 0.2);
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
            gap: 6px;
            padding: 10px;
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
    <!-- Account Stats Row - 4 Column Grid -->
    <div style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; gap: 16px; margin-bottom: 28px; width: 100%;">
        <!-- Protected Content -->
        <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #00E5FF;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px;">
                <div style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(0,229,255,0.12); color: #00E5FF;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div style="font-size: 11px; color: #718096; padding: 4px 8px; border-radius: 100px; background: rgba(255,255,255,0.05);">
                    Unlimited
                </div>
            </div>
            <div style="font-family: 'Space Grotesk', sans-serif; font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 6px;">12</div>
            <div style="font-size: 12px; color: #718096; font-weight: 500; text-transform: uppercase; letter-spacing: 0.03em;">Protected Content</div>
        </div>

        <!-- API Calls -->
        <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #00E676;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px;">
                <div style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(0,230,118,0.12); color: #00E676;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <div style="font-size: 11px; color: #00E676; padding: 4px 8px; border-radius: 100px; background: rgba(0,230,118,0.12);">
                    85% left
                </div>
            </div>
            <div style="font-family: 'Space Grotesk', sans-serif; font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 6px;">8,500</div>
            <div style="font-size: 12px; color: #718096; font-weight: 500; text-transform: uppercase; letter-spacing: 0.03em;">API Calls Left</div>
        </div>

        <!-- Storage Used -->
        <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #7C4DFF;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px;">
                <div style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(124,77,255,0.12); color: #7C4DFF;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <div style="font-size: 11px; color: #7C4DFF; padding: 4px 8px; border-radius: 100px; background: rgba(124,77,255,0.12);">
                    24% used
                </div>
            </div>
            <div style="font-family: 'Space Grotesk', sans-serif; font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 6px;">2.4 GB</div>
            <div style="font-size: 12px; color: #718096; font-weight: 500; text-transform: uppercase; letter-spacing: 0.03em;">Storage Used</div>
        </div>

        <!-- Next Billing -->
        <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #FFB300;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px;">
                <div style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(255,179,0,0.12); color: #FFB300;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div style="font-size: 11px; color: #FFB300; padding: 4px 8px; border-radius: 100px; background: rgba(255,179,0,0.12);">
                    18 days
                </div>
            </div>
            <div style="font-family: 'Space Grotesk', sans-serif; font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 6px;">$49</div>
            <div style="font-size: 12px; color: #718096; font-weight: 500; text-transform: uppercase; letter-spacing: 0.03em;">Next Billing</div>
        </div>
    </div>

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
            <!-- General Settings - 4 Column Grid -->
            <div style="margin-bottom: 28px;">
                <h2 style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.125rem; font-weight: 500; margin-bottom: 8px;">General Settings</h2>
                <p style="font-size: 0.8125rem; color: #718096; margin-bottom: 20px;">Configure your account preferences</p>

                <div style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; gap: 16px; width: 100%;">
                    <!-- Email Address -->
                    <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #00E5FF;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(0,229,255,0.12); color: #00E5FF;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: #fff;">Email Address</div>
                        </div>
                        <input type="email" class="form-input" value="admin@contentshield.ai" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 0.8125rem;">
                        <div style="font-size: 0.6875rem; color: #718096; margin-top: 8px;">Primary email for notifications</div>
                    </div>

                    <!-- Time Zone -->
                    <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #00E676;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(0,230,118,0.12); color: #00E676;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: #fff;">Time Zone</div>
                        </div>
                        <select class="setting-select" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 0.8125rem;">
                            <option>UTC (GMT+0)</option>
                            <option selected>EST (GMT-5)</option>
                            <option>PST (GMT-8)</option>
                            <option>CET (GMT+1)</option>
                        </select>
                        <div style="font-size: 0.6875rem; color: #718096; margin-top: 8px;">Used for scheduling and reports</div>
                    </div>

                    <!-- Language -->
                    <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #7C4DFF;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(124,77,255,0.12); color: #7C4DFF;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="2" y1="12" x2="22" y2="12"/>
                                    <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                                </svg>
                            </div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: #fff;">Language</div>
                        </div>
                        <select class="setting-select" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 0.8125rem;">
                            <option selected>English</option>
                            <option>Spanish</option>
                            <option>French</option>
                            <option>German</option>
                        </select>
                        <div style="font-size: 0.6875rem; color: #718096; margin-top: 8px;">Interface language preference</div>
                    </div>

                    <!-- Date Format -->
                    <div style="flex: 1 1 25%; min-width: 0; background: #0C1018; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; position: relative; border-top: 3px solid #FFB300;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(255,179,0,0.12); color: #FFB300;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: #fff;">Date Format</div>
                        </div>
                        <select class="setting-select" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 0.8125rem;">
                            <option selected>MM/DD/YYYY</option>
                            <option>DD/MM/YYYY</option>
                            <option>YYYY-MM-DD</option>
                        </select>
                        <div style="font-size: 0.6875rem; color: #718096; margin-top: 8px;">Display format for dates</div>
                    </div>
                </div>
            </div>

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
