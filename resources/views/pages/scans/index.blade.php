@extends('layouts.app')

@section('title', 'Scan Results - ContentShield AI')
@section('page-title', 'Scan Results')

@section('styles')
<style>
    .scan-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .scan-stat {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        animation: slideUp 0.4s ease-out both;
        position: relative;
        overflow: hidden;
    }

    .scan-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--stat-color, var(--accent-cyan));
        opacity: 0.5;
    }

    .scan-stat-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: -0.02em;
    }

    .scan-stat-label {
        font-size: 0.75rem;
        color: var(--text-dim);
        font-weight: 500;
    }

    .scan-stat.urls {
        --stat-color: var(--accent-cyan);
    }
    .scan-stat.urls .scan-stat-value { color: var(--accent-cyan); }

    .scan-stat.clean {
        --stat-color: var(--accent-emerald);
    }
    .scan-stat.clean .scan-stat-value { color: var(--accent-emerald); }

    .scan-stat.matches {
        --stat-color: var(--accent-rose);
    }
    .scan-stat.matches .scan-stat-value { color: var(--accent-rose); }

    .scan-stat.pending {
        --stat-color: var(--accent-amber);
    }
    .scan-stat.pending .scan-stat-value { color: var(--accent-amber); }

    .scans-table {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
        animation: slideUp 0.4s ease-out both;
        animation-delay: 0.1s;
        position: relative;
    }

    .scans-table::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.06), transparent);
    }

    .scans-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255, 255, 255, 0.01);
    }

    .scans-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.125rem;
        font-weight: 500;
    }

    .scan-type {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .scan-type.scheduled {
        background: var(--accent-violet-glow);
        color: var(--accent-violet);
    }

    .scan-type.manual {
        background: var(--accent-cyan-glow);
        color: var(--accent-cyan);
    }

    .scan-type.auto {
        background: var(--accent-emerald-glow);
        color: var(--accent-emerald);
    }

    .scan-progress {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .progress-bar-container {
        width: 100px;
        height: 5px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent-cyan), var(--accent-emerald));
        border-radius: 3px;
        transition: width var(--transition-base);
        box-shadow: 0 0 8px var(--accent-cyan-glow);
    }

    .progress-text {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--text-muted);
        min-width: 36px;
    }

    .scan-results-summary {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .result-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .result-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .result-dot.clean { background: var(--accent-emerald); }
    .result-dot.match { background: var(--accent-rose); }

    .running-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: var(--accent-cyan-glow);
        border-radius: 100px;
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--accent-cyan);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .running-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--accent-cyan);
        animation: runningPulse 1.5s ease-in-out infinite;
    }

    @keyframes runningPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.8); }
    }

    @media (max-width: 1200px) {
        .scan-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .scan-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <p class="page-description">View and manage plagiarism scan results</p>
        </div>
        <button class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            Start New Scan
        </button>
    </div>

    <div class="scan-stats">
        <div class="scan-stat urls" style="animation-delay: 0.1s">
            <div class="scan-stat-value">24,847</div>
            <div class="scan-stat-label">Total URLs Scanned</div>
        </div>
        <div class="scan-stat clean" style="animation-delay: 0.15s">
            <div class="scan-stat-value">24,691</div>
            <div class="scan-stat-label">Clean Results</div>
        </div>
        <div class="scan-stat matches" style="animation-delay: 0.2s">
            <div class="scan-stat-value">156</div>
            <div class="scan-stat-label">Matches Found</div>
        </div>
        <div class="scan-stat pending" style="animation-delay: 0.25s">
            <div class="scan-stat-value">3</div>
            <div class="scan-stat-label">Scans In Progress</div>
        </div>
    </div>

    <div class="scans-table">
        <div class="scans-header">
            <h2 class="scans-title">Recent Scans</h2>
            <div class="tabs">
                <button class="tab active">All</button>
                <button class="tab">Running</button>
                <button class="tab">Completed</button>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Scan ID</th>
                        <th>Content</th>
                        <th>Type</th>
                        <th>Progress</th>
                        <th>Results</th>
                        <th>Started</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="fingerprint">SCN-001</code></td>
                        <td style="font-size: 0.8125rem;">How to Build Modern APIs</td>
                        <td><span class="scan-type manual">Manual</span></td>
                        <td>
                            <div class="scan-progress">
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: 67%;"></div>
                                </div>
                                <span class="progress-text">67%</span>
                            </div>
                        </td>
                        <td><span class="running-indicator"><span class="running-dot"></span> Running</span></td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">2 min ago</td>
                        <td>
                            <button class="btn btn-ghost btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <rect x="6" y="4" width="4" height="16"/>
                                    <rect x="14" y="4" width="4" height="16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code class="fingerprint">SCN-002</code></td>
                        <td style="font-size: 0.8125rem;">React Hooks Complete Guide</td>
                        <td><span class="scan-type scheduled">Scheduled</span></td>
                        <td>
                            <div class="scan-progress">
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: 34%;"></div>
                                </div>
                                <span class="progress-text">34%</span>
                            </div>
                        </td>
                        <td><span class="running-indicator"><span class="running-dot"></span> Running</span></td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">5 min ago</td>
                        <td>
                            <button class="btn btn-ghost btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <rect x="6" y="4" width="4" height="16"/>
                                    <rect x="14" y="4" width="4" height="16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code class="fingerprint">SCN-003</code></td>
                        <td style="font-size: 0.8125rem;">TypeScript Generics Guide</td>
                        <td><span class="scan-type auto">Auto</span></td>
                        <td>
                            <div class="scan-progress">
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: 100%;"></div>
                                </div>
                                <span class="progress-text">100%</span>
                            </div>
                        </td>
                        <td>
                            <div class="scan-results-summary">
                                <div class="result-item">
                                    <span class="result-dot clean"></span>
                                    245 clean
                                </div>
                                <div class="result-item">
                                    <span class="result-dot match"></span>
                                    7 matches
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">1 hour ago</td>
                        <td>
                            <button class="btn btn-ghost btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code class="fingerprint">SCN-004</code></td>
                        <td style="font-size: 0.8125rem;">Vue.js Composition API</td>
                        <td><span class="scan-type scheduled">Scheduled</span></td>
                        <td>
                            <div class="scan-progress">
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: 100%;"></div>
                                </div>
                                <span class="progress-text">100%</span>
                            </div>
                        </td>
                        <td>
                            <div class="scan-results-summary">
                                <div class="result-item">
                                    <span class="result-dot clean"></span>
                                    189 clean
                                </div>
                                <div class="result-item">
                                    <span class="result-dot match"></span>
                                    0 matches
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">3 hours ago</td>
                        <td>
                            <button class="btn btn-ghost btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><code class="fingerprint">SCN-005</code></td>
                        <td style="font-size: 0.8125rem;">Node.js Performance</td>
                        <td><span class="scan-type manual">Manual</span></td>
                        <td>
                            <div class="scan-progress">
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: 100%;"></div>
                                </div>
                                <span class="progress-text">100%</span>
                            </div>
                        </td>
                        <td>
                            <div class="scan-results-summary">
                                <div class="result-item">
                                    <span class="result-dot clean"></span>
                                    312 clean
                                </div>
                                <div class="result-item">
                                    <span class="result-dot match"></span>
                                    1 match
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">1 day ago</td>
                        <td>
                            <button class="btn btn-ghost btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            this.parentElement.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endsection
