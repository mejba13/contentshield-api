@extends('layouts.app')

@section('title', 'Dashboard - ContentShield AI')
@section('page-title', 'Dashboard')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all var(--transition-base);
        animation: slideUp 0.5s ease-out both;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.15s; }
    .stat-card:nth-child(3) { animation-delay: 0.2s; }
    .stat-card:nth-child(4) { animation-delay: 0.25s; }

    .stat-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-card);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--stat-accent, var(--accent-cyan)), transparent);
        opacity: 0;
        transition: opacity var(--transition-base);
    }

    .stat-card:hover::before { opacity: 1; }

    .stat-card.cyan { --stat-accent: var(--accent-cyan); }
    .stat-card.emerald { --stat-accent: var(--accent-emerald); }
    .stat-card.amber { --stat-accent: var(--accent-amber); }
    .stat-card.rose { --stat-accent: var(--accent-rose); }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--stat-accent, var(--accent-cyan));
        opacity: 0.15;
    }

    .stat-icon svg {
        width: 22px;
        height: 22px;
        color: var(--stat-accent, var(--accent-cyan));
        opacity: 1;
        position: relative;
    }

    .stat-change {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 100px;
    }

    .stat-change.up {
        color: var(--accent-emerald);
        background: rgba(16, 185, 129, 0.1);
    }

    .stat-change.down {
        color: var(--accent-rose);
        background: rgba(244, 63, 94, 0.1);
    }

    .stat-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.5rem;
        font-weight: 500;
        letter-spacing: -0.02em;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .main-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 24px;
    }

    .chart-card { animation-delay: 0.3s; }

    .chart-container {
        height: 300px;
        position: relative;
    }

    .chart-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
    }

    .chart-grid {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 0;
    }

    .chart-line {
        height: 1px;
        background: var(--border-subtle);
        position: relative;
    }

    .chart-line::before {
        content: attr(data-value);
        position: absolute;
        left: -40px;
        top: 50%;
        transform: translateY(-50%);
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.6875rem;
        color: var(--text-muted);
    }

    .chart-bars {
        position: absolute;
        bottom: 40px;
        left: 40px;
        right: 20px;
        height: 220px;
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .chart-bar-group {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .chart-bar {
        width: 100%;
        max-width: 32px;
        border-radius: 4px 4px 0 0;
        background: linear-gradient(180deg, var(--accent-cyan), rgba(0, 212, 255, 0.3));
        transition: all var(--transition-base);
        position: relative;
    }

    .chart-bar:hover {
        filter: brightness(1.2);
        transform: scaleY(1.02);
        transform-origin: bottom;
    }

    .chart-bar::after {
        content: attr(data-value);
        position: absolute;
        top: -24px;
        left: 50%;
        transform: translateX(-50%);
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.6875rem;
        color: var(--text-muted);
        opacity: 0;
        transition: opacity var(--transition-fast);
    }

    .chart-bar:hover::after { opacity: 1; }

    .chart-label {
        font-size: 0.6875rem;
        color: var(--text-muted);
        text-align: center;
    }

    .chart-legend {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-subtle);
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8125rem;
        color: var(--text-secondary);
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .activity-card { animation-delay: 0.35s; }

    .activity-list {
        display: flex;
        flex-direction: column;
    }

    .activity-item {
        display: flex;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid var(--border-subtle);
        transition: all var(--transition-fast);
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-item:hover {
        background: var(--bg-elevated);
        margin: 0 -24px;
        padding-left: 24px;
        padding-right: 24px;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-icon.match {
        background: rgba(244, 63, 94, 0.1);
        color: var(--accent-rose);
    }

    .activity-icon.scan {
        background: rgba(0, 212, 255, 0.1);
        color: var(--accent-cyan);
    }

    .activity-icon.dmca {
        background: rgba(139, 92, 246, 0.1);
        color: var(--accent-violet);
    }

    .activity-icon.resolved {
        background: rgba(16, 185, 129, 0.1);
        color: var(--accent-emerald);
    }

    .activity-icon svg {
        width: 20px;
        height: 20px;
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-title {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .activity-title a {
        color: var(--accent-cyan);
        text-decoration: none;
    }

    .activity-title a:hover { text-decoration: underline; }

    .activity-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .content-section {
        margin-top: 32px;
        animation: slideUp 0.5s ease-out both;
        animation-delay: 0.4s;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .section-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.5rem;
        font-weight: 400;
    }

    .content-table {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .content-title {
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .content-title-icon {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-sm);
        background: var(--accent-cyan-glow);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-cyan);
    }

    .content-title-icon svg {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 1400px) {
        .main-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card cyan">
            <div class="stat-header">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div class="stat-change up">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    </svg>
                    +12%
                </div>
            </div>
            <div class="stat-value" data-target="1247">0</div>
            <div class="stat-label">Protected Posts</div>
        </div>

        <div class="stat-card emerald">
            <div class="stat-header">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </div>
                <div class="stat-change up">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    </svg>
                    +8%
                </div>
            </div>
            <div class="stat-value" data-target="8934">0</div>
            <div class="stat-label">URLs Scanned</div>
        </div>

        <div class="stat-card rose">
            <div class="stat-header">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div class="stat-change down">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                    </svg>
                    -24%
                </div>
            </div>
            <div class="stat-value" data-target="23">0</div>
            <div class="stat-label">Matches Found</div>
        </div>

        <div class="stat-card amber">
            <div class="stat-header">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <div class="stat-change up">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    </svg>
                    +5
                </div>
            </div>
            <div class="stat-value" data-target="89" data-suffix="%">0%</div>
            <div class="stat-label">DMCA Success Rate</div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="main-grid">
        <!-- Chart Card -->
        <div class="card chart-card">
            <div class="card-header">
                <h2 class="card-title">Monitoring Overview</h2>
                <div class="card-actions">
                    <div class="tabs">
                        <button class="tab active">7 Days</button>
                        <button class="tab">30 Days</button>
                        <button class="tab">90 Days</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div class="chart-placeholder">
                        <div class="chart-grid">
                            <div class="chart-line" data-value="100"></div>
                            <div class="chart-line" data-value="75"></div>
                            <div class="chart-line" data-value="50"></div>
                            <div class="chart-line" data-value="25"></div>
                            <div class="chart-line" data-value="0"></div>
                        </div>
                        <div class="chart-bars">
                            <div class="chart-bar-group">
                                <div class="chart-bar" style="height: 0%" data-height="65%" data-value="65"></div>
                                <span class="chart-label">Mon</span>
                            </div>
                            <div class="chart-bar-group">
                                <div class="chart-bar" style="height: 0%" data-height="85%" data-value="85"></div>
                                <span class="chart-label">Tue</span>
                            </div>
                            <div class="chart-bar-group">
                                <div class="chart-bar" style="height: 0%" data-height="45%" data-value="45"></div>
                                <span class="chart-label">Wed</span>
                            </div>
                            <div class="chart-bar-group">
                                <div class="chart-bar" style="height: 0%" data-height="78%" data-value="78"></div>
                                <span class="chart-label">Thu</span>
                            </div>
                            <div class="chart-bar-group">
                                <div class="chart-bar" style="height: 0%" data-height="92%" data-value="92"></div>
                                <span class="chart-label">Fri</span>
                            </div>
                            <div class="chart-bar-group">
                                <div class="chart-bar" style="height: 0%" data-height="55%" data-value="55"></div>
                                <span class="chart-label">Sat</span>
                            </div>
                            <div class="chart-bar-group">
                                <div class="chart-bar" style="height: 0%" data-height="38%" data-value="38"></div>
                                <span class="chart-label">Sun</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background: var(--accent-cyan);"></div>
                        URLs Scanned
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background: var(--accent-rose);"></div>
                        Matches Found
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Card -->
        <div class="card activity-card">
            <div class="card-header">
                <h2 class="card-title">Recent Activity</h2>
                <a href="{{ route('alerts.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="card-body" style="padding: 0 24px;">
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon match">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                Match detected on <a href="#">example-site.com</a>
                            </div>
                            <div class="activity-meta">92% similarity - "How to Build APIs"</div>
                        </div>
                        <div class="activity-time">2m ago</div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon scan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Scheduled scan completed</div>
                            <div class="activity-meta">245 URLs checked - 0 matches</div>
                        </div>
                        <div class="activity-time">1h ago</div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon dmca">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">DMCA sent to Google</div>
                            <div class="activity-meta">Ref: DMCA-ABC123 - copy-site.net</div>
                        </div>
                        <div class="activity-time">3h ago</div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon resolved">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Content removed</div>
                            <div class="activity-meta">spam-blog.com - Deindexed from Google</div>
                        </div>
                        <div class="activity-time">1d ago</div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon scan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">New content protected</div>
                            <div class="activity-meta">"Ultimate Guide to Laravel" added</div>
                        </div>
                        <div class="activity-time">2d ago</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Protected Content Section -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Protected Content</h2>
            <div style="display: flex; gap: 12px;">
                <button class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('content.index') }}" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Content
                </a>
            </div>
        </div>

        <div class="content-table">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Content</th>
                            <th>Fingerprint</th>
                            <th>Status</th>
                            <th>Last Scan</th>
                            <th>Matches</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="content-title">
                                    <div class="content-title-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div>How to Build Modern APIs with Laravel</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">example.com/build-apis</div>
                                    </div>
                                </div>
                            </td>
                            <td><code class="fingerprint">a1b2c3d4...f8g9</code></td>
                            <td><span class="status-badge protected"><span class="status-dot"></span> Protected</span></td>
                            <td style="color: var(--text-secondary);">2 hours ago</td>
                            <td style="color: var(--accent-rose); font-weight: 600;">3</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-ghost btn-sm">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                            <circle cx="12" cy="12" r="1"/>
                                            <circle cx="19" cy="12" r="1"/>
                                            <circle cx="5" cy="12" r="1"/>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu">
                                        <div class="dropdown-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="11" cy="11" r="8"/>
                                                <path d="M21 21l-4.35-4.35"/>
                                            </svg>
                                            Scan Now
                                        </div>
                                        <div class="dropdown-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            View Matches
                                        </div>
                                        <div class="dropdown-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                            Remove
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="content-title">
                                    <div class="content-title-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div>Complete Guide to React Hooks</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">example.com/react-hooks</div>
                                    </div>
                                </div>
                            </td>
                            <td><code class="fingerprint">e5f6g7h8...l2m3</code></td>
                            <td><span class="status-badge monitoring"><span class="status-dot"></span> Monitoring</span></td>
                            <td style="color: var(--text-secondary);">5 hours ago</td>
                            <td style="color: var(--text-secondary);">0</td>
                            <td>
                                <button class="btn btn-ghost btn-sm">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                        <circle cx="12" cy="12" r="1"/>
                                        <circle cx="19" cy="12" r="1"/>
                                        <circle cx="5" cy="12" r="1"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="content-title">
                                    <div class="content-title-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div>Understanding TypeScript Generics</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">example.com/ts-generics</div>
                                    </div>
                                </div>
                            </td>
                            <td><code class="fingerprint">n4o5p6q7...r8s9</code></td>
                            <td><span class="status-badge alert"><span class="status-dot"></span> Alert</span></td>
                            <td style="color: var(--text-secondary);">1 day ago</td>
                            <td style="color: var(--accent-rose); font-weight: 600;">7</td>
                            <td>
                                <button class="btn btn-ghost btn-sm">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                        <circle cx="12" cy="12" r="1"/>
                                        <circle cx="19" cy="12" r="1"/>
                                        <circle cx="5" cy="12" r="1"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function animateValue(element, start, end, duration, suffix = '') {
        const range = end - start;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + range * easeOut);
            element.textContent = current.toLocaleString() + suffix;
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        requestAnimationFrame(update);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Animate stat values
        document.querySelectorAll('.stat-value').forEach((stat, index) => {
            const target = parseInt(stat.dataset.target);
            const suffix = stat.dataset.suffix || '';
            setTimeout(() => {
                animateValue(stat, 0, target, 1500, suffix);
            }, index * 100);
        });

        // Animate chart bars
        document.querySelectorAll('.chart-bar').forEach((bar, index) => {
            const height = bar.dataset.height;
            setTimeout(() => {
                bar.style.transition = 'height 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                bar.style.height = height;
            }, 500 + index * 100);
        });

        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                this.parentElement.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>
@endsection
