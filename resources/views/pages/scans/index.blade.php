@extends('layouts.app')

@section('title', 'Scan Results - ContentShield AI')
@section('page-title', 'Scan Results')

@section('styles')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
    }

    .page-description {
        color: var(--text-secondary);
        font-size: 0.9375rem;
        margin-top: 4px;
    }

    .scan-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .scan-stat {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 20px;
        animation: slideUp 0.5s ease-out both;
    }

    .scan-stat-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .scan-stat-label {
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .scan-stat.urls .scan-stat-value { color: var(--accent-cyan); }
    .scan-stat.clean .scan-stat-value { color: var(--accent-emerald); }
    .scan-stat.matches .scan-stat-value { color: var(--accent-rose); }
    .scan-stat.pending .scan-stat-value { color: var(--accent-amber); }

    .scans-table {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
        animation: slideUp 0.5s ease-out both;
        animation-delay: 0.1s;
    }

    .scans-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .scans-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
    }

    .scan-type {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .scan-type.scheduled {
        background: rgba(139, 92, 246, 0.1);
        color: var(--accent-violet);
    }

    .scan-type.manual {
        background: rgba(0, 212, 255, 0.1);
        color: var(--accent-cyan);
    }

    .scan-type.auto {
        background: rgba(16, 185, 129, 0.1);
        color: var(--accent-emerald);
    }

    .scan-progress {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .progress-bar-container {
        width: 120px;
        height: 6px;
        background: var(--bg-elevated);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: var(--accent-cyan);
        border-radius: 3px;
        transition: width var(--transition-base);
    }

    .progress-text {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: var(--text-muted);
        min-width: 40px;
    }

    .scan-results-summary {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .result-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8125rem;
    }

    .result-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .result-dot.clean { background: var(--accent-emerald); }
    .result-dot.match { background: var(--accent-rose); }

    .running-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        background: rgba(0, 212, 255, 0.1);
        border-radius: 100px;
        font-size: 0.75rem;
        color: var(--accent-cyan);
    }

    .running-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent-cyan);
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
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
