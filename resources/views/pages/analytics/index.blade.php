@extends('layouts.app')

@section('title', 'Analytics - ContentShield AI')
@section('page-title', 'Analytics')

@section('styles')
<style>
    .date-range-picker {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .date-range-picker:hover {
        border-color: var(--border-hover);
    }

    .date-range-picker svg {
        width: 18px;
        height: 18px;
        color: var(--text-muted);
    }

    .date-range-picker span {
        font-size: 0.875rem;
        color: var(--text-primary);
    }

    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .chart-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
        animation: slideUp 0.5s ease-out both;
    }

    .chart-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chart-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.125rem;
    }

    .chart-body {
        padding: 24px;
    }

    .chart-placeholder {
        height: 280px;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .chart-y-axis {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 40px;
        width: 40px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.6875rem;
        color: var(--text-muted);
        text-align: right;
        padding-right: 8px;
    }

    .line-chart-area {
        position: absolute;
        left: 50px;
        right: 0;
        top: 0;
        bottom: 40px;
        border-left: 1px solid var(--border-subtle);
        border-bottom: 1px solid var(--border-subtle);
    }

    .chart-grid-lines {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .grid-line {
        height: 1px;
        background: var(--border-subtle);
    }

    .line-path {
        position: absolute;
        inset: 0;
        overflow: hidden;
    }

    .svg-line {
        width: 100%;
        height: 100%;
    }

    .chart-x-axis {
        position: absolute;
        left: 50px;
        right: 0;
        bottom: 0;
        height: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.6875rem;
        color: var(--text-muted);
        padding-top: 12px;
    }

    .donut-chart {
        display: flex;
        align-items: center;
        gap: 40px;
        height: 200px;
    }

    .donut-visual {
        width: 160px;
        height: 160px;
        position: relative;
    }

    .donut-center {
        position: absolute;
        inset: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .donut-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .donut-label {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .donut-legend {
        flex: 1;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-subtle);
    }

    .legend-item:last-child {
        border-bottom: none;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }

    .legend-name {
        flex: 1;
        font-size: 0.875rem;
    }

    .legend-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .legend-percent {
        font-size: 0.75rem;
        color: var(--text-muted);
        width: 40px;
        text-align: right;
    }

    .metrics-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .metric-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 20px;
        animation: slideUp 0.5s ease-out both;
    }

    .metric-label {
        font-size: 0.8125rem;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .metric-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .metric-change {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
    }

    .metric-change.up {
        color: var(--accent-emerald);
    }

    .metric-change.down {
        color: var(--accent-rose);
    }

    .metric-change svg {
        width: 14px;
        height: 14px;
    }

    .bar-chart {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        height: 200px;
        padding-top: 20px;
    }

    .bar-group {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .bar {
        width: 100%;
        max-width: 48px;
        border-radius: 4px 4px 0 0;
        background: linear-gradient(180deg, var(--accent-cyan), rgba(0, 212, 255, 0.4));
        transition: all var(--transition-base);
        position: relative;
    }

    .bar:hover {
        filter: brightness(1.2);
    }

    .bar::after {
        content: attr(data-value);
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.6875rem;
        color: var(--text-muted);
        opacity: 0;
        transition: opacity var(--transition-fast);
    }

    .bar:hover::after {
        opacity: 1;
    }

    .bar-label {
        font-size: 0.6875rem;
        color: var(--text-muted);
    }

    @media (max-width: 1400px) {
        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1200px) {
        .metrics-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .metrics-row {
            grid-template-columns: 1fr;
        }

        .donut-chart {
            flex-direction: column;
            height: auto;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <p class="page-description">Track your content protection metrics and trends</p>
        </div>
        <div class="date-range-picker">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>Last 30 days</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </div>

    <div class="metrics-row">
        <div class="metric-card" style="animation-delay: 0.1s">
            <div class="metric-label">Total Scans</div>
            <div class="metric-value">24,847</div>
            <div class="metric-change up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                </svg>
                +12.4% vs last period
            </div>
        </div>
        <div class="metric-card" style="animation-delay: 0.15s">
            <div class="metric-label">Matches Found</div>
            <div class="metric-value">156</div>
            <div class="metric-change down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                </svg>
                -8.2% vs last period
            </div>
        </div>
        <div class="metric-card" style="animation-delay: 0.2s">
            <div class="metric-label">DMCA Success Rate</div>
            <div class="metric-value">89%</div>
            <div class="metric-change up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                </svg>
                +5.1% vs last period
            </div>
        </div>
        <div class="metric-card" style="animation-delay: 0.25s">
            <div class="metric-label">Protected Content</div>
            <div class="metric-value">1,247</div>
            <div class="metric-change up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                </svg>
                +23 new this month
            </div>
        </div>
    </div>

    <div class="analytics-grid">
        <div class="chart-card" style="animation-delay: 0.3s">
            <div class="chart-header">
                <h3 class="chart-title">Scan Activity</h3>
                <div class="tabs">
                    <button class="tab active">Daily</button>
                    <button class="tab">Weekly</button>
                    <button class="tab">Monthly</button>
                </div>
            </div>
            <div class="chart-body">
                <div class="chart-placeholder">
                    <div class="chart-y-axis">
                        <span>1000</span>
                        <span>750</span>
                        <span>500</span>
                        <span>250</span>
                        <span>0</span>
                    </div>
                    <div class="line-chart-area">
                        <div class="chart-grid-lines">
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                        </div>
                        <div class="line-path">
                            <svg class="svg-line" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="rgba(0, 212, 255, 0.3)"/>
                                        <stop offset="100%" stop-color="rgba(0, 212, 255, 0)"/>
                                    </linearGradient>
                                </defs>
                                <path d="M0,70 L10,55 L20,60 L30,40 L40,45 L50,30 L60,35 L70,25 L80,30 L90,20 L100,15 L100,100 L0,100 Z" fill="url(#lineGradient)"/>
                                <path d="M0,70 L10,55 L20,60 L30,40 L40,45 L50,30 L60,35 L70,25 L80,30 L90,20 L100,15" fill="none" stroke="var(--accent-cyan)" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="chart-x-axis">
                        <span>1</span>
                        <span>5</span>
                        <span>10</span>
                        <span>15</span>
                        <span>20</span>
                        <span>25</span>
                        <span>30</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card" style="animation-delay: 0.35s">
            <div class="chart-header">
                <h3 class="chart-title">Match Distribution</h3>
            </div>
            <div class="chart-body">
                <div class="donut-chart">
                    <div class="donut-visual">
                        <svg viewBox="0 0 160 160">
                            <circle cx="80" cy="80" r="70" fill="none" stroke="var(--bg-elevated)" stroke-width="20"/>
                            <circle cx="80" cy="80" r="70" fill="none" stroke="var(--accent-rose)" stroke-width="20"
                                stroke-dasharray="110 330" stroke-dashoffset="0" transform="rotate(-90 80 80)"/>
                            <circle cx="80" cy="80" r="70" fill="none" stroke="var(--accent-amber)" stroke-width="20"
                                stroke-dasharray="88 352" stroke-dashoffset="-110" transform="rotate(-90 80 80)"/>
                            <circle cx="80" cy="80" r="70" fill="none" stroke="var(--accent-cyan)" stroke-width="20"
                                stroke-dasharray="154 286" stroke-dashoffset="-198" transform="rotate(-90 80 80)"/>
                            <circle cx="80" cy="80" r="70" fill="none" stroke="var(--accent-emerald)" stroke-width="20"
                                stroke-dasharray="88 352" stroke-dashoffset="-352" transform="rotate(-90 80 80)"/>
                        </svg>
                        <div class="donut-center">
                            <div class="donut-value">156</div>
                            <div class="donut-label">Total</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: var(--accent-rose);"></div>
                            <span class="legend-name">Critical (90%+)</span>
                            <span class="legend-value">39</span>
                            <span class="legend-percent">25%</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: var(--accent-amber);"></div>
                            <span class="legend-name">High (70-89%)</span>
                            <span class="legend-value">31</span>
                            <span class="legend-percent">20%</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: var(--accent-cyan);"></div>
                            <span class="legend-name">Medium (50-69%)</span>
                            <span class="legend-value">55</span>
                            <span class="legend-percent">35%</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: var(--accent-emerald);"></div>
                            <span class="legend-name">Low (<50%)</span>
                            <span class="legend-value">31</span>
                            <span class="legend-percent">20%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card" style="animation-delay: 0.4s">
            <div class="chart-header">
                <h3 class="chart-title">Top Infringers</h3>
            </div>
            <div class="chart-body">
                <div class="bar-chart">
                    <div class="bar-group">
                        <div class="bar" style="height: 180px;" data-value="23"></div>
                        <span class="bar-label">spam-site.com</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 140px;" data-value="18"></div>
                        <span class="bar-label">copycat.net</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 100px;" data-value="12"></div>
                        <span class="bar-label">scraper.io</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 80px;" data-value="9"></div>
                        <span class="bar-label">fake-blog.xyz</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 60px;" data-value="7"></div>
                        <span class="bar-label">stealer.com</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card" style="animation-delay: 0.45s">
            <div class="chart-header">
                <h3 class="chart-title">DMCA Resolution Time</h3>
            </div>
            <div class="chart-body">
                <div class="bar-chart">
                    <div class="bar-group">
                        <div class="bar" style="height: 160px; background: linear-gradient(180deg, var(--accent-emerald), rgba(16, 185, 129, 0.4));" data-value="45%"></div>
                        <span class="bar-label">&lt;24h</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 120px; background: linear-gradient(180deg, var(--accent-emerald), rgba(16, 185, 129, 0.4));" data-value="28%"></div>
                        <span class="bar-label">1-3 days</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 80px; background: linear-gradient(180deg, var(--accent-amber), rgba(245, 158, 11, 0.4));" data-value="15%"></div>
                        <span class="bar-label">3-7 days</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 50px; background: linear-gradient(180deg, var(--accent-amber), rgba(245, 158, 11, 0.4));" data-value="8%"></div>
                        <span class="bar-label">1-2 weeks</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar" style="height: 30px; background: linear-gradient(180deg, var(--accent-rose), rgba(244, 63, 94, 0.4));" data-value="4%"></div>
                        <span class="bar-label">&gt;2 weeks</span>
                    </div>
                </div>
            </div>
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
