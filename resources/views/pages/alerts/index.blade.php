@extends('layouts.app')

@section('title', 'Plagiarism Alerts - ContentShield AI')
@section('page-title', 'Plagiarism Alerts')

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

    .alert-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .alert-stat {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 20px;
        text-align: center;
        animation: slideUp 0.5s ease-out both;
    }

    .alert-stat-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .alert-stat-label {
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .alert-stat.critical .alert-stat-value { color: var(--accent-rose); }
    .alert-stat.high .alert-stat-value { color: var(--accent-amber); }
    .alert-stat.medium .alert-stat-value { color: var(--accent-cyan); }
    .alert-stat.resolved .alert-stat-value { color: var(--accent-emerald); }

    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border-subtle);
        padding-bottom: 16px;
    }

    .filter-tab {
        padding: 8px 16px;
        background: transparent;
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-tab:hover {
        background: var(--bg-card);
        color: var(--text-primary);
    }

    .filter-tab.active {
        background: var(--accent-cyan-glow);
        border-color: var(--accent-cyan);
        color: var(--accent-cyan);
    }

    .filter-count {
        background: var(--bg-elevated);
        padding: 2px 8px;
        border-radius: 100px;
        font-size: 0.75rem;
    }

    .filter-tab.active .filter-count {
        background: var(--accent-cyan);
        color: var(--bg-primary);
    }

    .alerts-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .alert-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all var(--transition-base);
        animation: slideUp 0.5s ease-out both;
    }

    .alert-card:hover {
        border-color: var(--border-hover);
        box-shadow: var(--shadow-card);
    }

    .alert-card-main {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 20px;
        padding: 20px;
        align-items: center;
    }

    .alert-severity {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .alert-severity.critical {
        background: rgba(244, 63, 94, 0.1);
        color: var(--accent-rose);
    }

    .alert-severity.high {
        background: rgba(245, 158, 11, 0.1);
        color: var(--accent-amber);
    }

    .alert-severity.medium {
        background: rgba(0, 212, 255, 0.1);
        color: var(--accent-cyan);
    }

    .alert-severity svg {
        width: 24px;
        height: 24px;
    }

    .alert-content {
        min-width: 0;
    }

    .alert-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .alert-title {
        font-weight: 600;
        font-size: 1rem;
    }

    .similarity-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 100px;
    }

    .similarity-badge.critical {
        background: rgba(244, 63, 94, 0.1);
        color: var(--accent-rose);
    }

    .similarity-badge.high {
        background: rgba(245, 158, 11, 0.1);
        color: var(--accent-amber);
    }

    .similarity-badge.medium {
        background: rgba(0, 212, 255, 0.1);
        color: var(--accent-cyan);
    }

    .alert-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .alert-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .alert-meta-item svg {
        width: 14px;
        height: 14px;
    }

    .alert-meta-item a {
        color: var(--accent-cyan);
        text-decoration: none;
    }

    .alert-meta-item a:hover {
        text-decoration: underline;
    }

    .alert-actions {
        display: flex;
        gap: 8px;
    }

    .alert-expand {
        border-top: 1px solid var(--border-subtle);
        padding: 16px 20px;
        background: var(--bg-elevated);
        display: none;
    }

    .alert-expand.active {
        display: block;
    }

    .comparison-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .comparison-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .comparison-header {
        padding: 12px 16px;
        background: var(--bg-secondary);
        border-bottom: 1px solid var(--border-subtle);
        font-size: 0.8125rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .comparison-header.original {
        color: var(--accent-emerald);
    }

    .comparison-header.copied {
        color: var(--accent-rose);
    }

    .comparison-body {
        padding: 16px;
        font-size: 0.875rem;
        line-height: 1.6;
        color: var(--text-secondary);
        max-height: 200px;
        overflow-y: auto;
    }

    .highlight {
        background: rgba(244, 63, 94, 0.2);
        border-radius: 2px;
        padding: 1px 2px;
    }

    .expand-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 8px;
        border-radius: var(--radius-sm);
        transition: all var(--transition-fast);
    }

    .expand-btn:hover {
        background: var(--bg-elevated);
        color: var(--text-primary);
    }

    .expand-btn svg {
        width: 20px;
        height: 20px;
        transition: transform var(--transition-fast);
    }

    .expand-btn.expanded svg {
        transform: rotate(180deg);
    }

    @media (max-width: 1200px) {
        .alert-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .alert-stats {
            grid-template-columns: 1fr;
        }

        .alert-card-main {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .alert-severity {
            width: 40px;
            height: 40px;
        }

        .comparison-container {
            grid-template-columns: 1fr;
        }

        .filter-tabs {
            flex-wrap: wrap;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <p class="page-description">Monitor and resolve detected content matches</p>
        </div>
        <button class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            Run Manual Scan
        </button>
    </div>

    <div class="alert-stats">
        <div class="alert-stat critical" style="animation-delay: 0.1s">
            <div class="alert-stat-value">3</div>
            <div class="alert-stat-label">Critical (90%+)</div>
        </div>
        <div class="alert-stat high" style="animation-delay: 0.15s">
            <div class="alert-stat-value">5</div>
            <div class="alert-stat-label">High (70-89%)</div>
        </div>
        <div class="alert-stat medium" style="animation-delay: 0.2s">
            <div class="alert-stat-value">12</div>
            <div class="alert-stat-label">Medium (50-69%)</div>
        </div>
        <div class="alert-stat resolved" style="animation-delay: 0.25s">
            <div class="alert-stat-value">47</div>
            <div class="alert-stat-label">Resolved</div>
        </div>
    </div>

    <div class="filter-tabs">
        <button class="filter-tab active">
            All Alerts
            <span class="filter-count">20</span>
        </button>
        <button class="filter-tab">
            Critical
            <span class="filter-count">3</span>
        </button>
        <button class="filter-tab">
            High
            <span class="filter-count">5</span>
        </button>
        <button class="filter-tab">
            Medium
            <span class="filter-count">12</span>
        </button>
        <button class="filter-tab">
            Resolved
            <span class="filter-count">47</span>
        </button>
    </div>

    <div class="alerts-list">
        <div class="alert-card" style="animation-delay: 0.1s">
            <div class="alert-card-main">
                <div class="alert-severity critical">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div class="alert-content">
                    <div class="alert-header">
                        <span class="alert-title">How to Build Modern APIs with Laravel</span>
                        <span class="similarity-badge critical">92% Match</span>
                    </div>
                    <div class="alert-meta">
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Detected 2 hours ago
                        </div>
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            <a href="#">spam-content-site.com</a>
                        </div>
                    </div>
                </div>
                <div class="alert-actions">
                    <a href="{{ route('dmca.index') }}" class="btn btn-primary btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        File DMCA
                    </a>
                    <button class="btn btn-secondary btn-sm">Dismiss</button>
                    <button class="expand-btn" onclick="toggleExpand(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="alert-expand">
                <div class="comparison-container">
                    <div class="comparison-panel">
                        <div class="comparison-header original">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            Your Original Content
                        </div>
                        <div class="comparison-body">
                            <span class="highlight">Building modern APIs with Laravel</span> requires understanding several key concepts. First, you need to structure your routes properly using Resource Controllers. This approach ensures your API follows RESTful conventions while maintaining clean, organized code...
                        </div>
                    </div>
                    <div class="comparison-panel">
                        <div class="comparison-header copied">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            Copied Content Found
                        </div>
                        <div class="comparison-body">
                            <span class="highlight">Building modern APIs with Laravel</span> requires understanding several key concepts. First, you need to structure your routes properly using Resource Controllers. This approach ensures your API follows RESTful conventions while maintaining clean, organized code...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert-card" style="animation-delay: 0.15s">
            <div class="alert-card-main">
                <div class="alert-severity critical">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div class="alert-content">
                    <div class="alert-header">
                        <span class="alert-title">Understanding TypeScript Generics</span>
                        <span class="similarity-badge critical">95% Match</span>
                    </div>
                    <div class="alert-meta">
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Detected 1 day ago
                        </div>
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            <a href="#">copycat-blog.net</a>
                        </div>
                    </div>
                </div>
                <div class="alert-actions">
                    <a href="{{ route('dmca.index') }}" class="btn btn-primary btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        File DMCA
                    </a>
                    <button class="btn btn-secondary btn-sm">Dismiss</button>
                    <button class="expand-btn" onclick="toggleExpand(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="alert-expand">
                <div class="comparison-container">
                    <div class="comparison-panel">
                        <div class="comparison-header original">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            Your Original Content
                        </div>
                        <div class="comparison-body">
                            <span class="highlight">TypeScript generics are a powerful feature</span> that allows you to create reusable components that work with a variety of types rather than a single one. This enables users to consume these components and use their own types...
                        </div>
                    </div>
                    <div class="comparison-panel">
                        <div class="comparison-header copied">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            Copied Content Found
                        </div>
                        <div class="comparison-body">
                            <span class="highlight">TypeScript generics are a powerful feature</span> that allows you to create reusable components that work with a variety of types rather than a single one. This enables users to consume these components and use their own types...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert-card" style="animation-delay: 0.2s">
            <div class="alert-card-main">
                <div class="alert-severity high">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div class="alert-content">
                    <div class="alert-header">
                        <span class="alert-title">Node.js Performance Optimization</span>
                        <span class="similarity-badge high">78% Match</span>
                    </div>
                    <div class="alert-meta">
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Detected 3 days ago
                        </div>
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            <a href="#">tech-articles.io</a>
                        </div>
                    </div>
                </div>
                <div class="alert-actions">
                    <a href="{{ route('dmca.index') }}" class="btn btn-primary btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        File DMCA
                    </a>
                    <button class="btn btn-secondary btn-sm">Dismiss</button>
                    <button class="expand-btn" onclick="toggleExpand(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="alert-expand">
                <div class="comparison-container">
                    <div class="comparison-panel">
                        <div class="comparison-header original">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            Your Original Content
                        </div>
                        <div class="comparison-body">
                            <span class="highlight">Optimizing Node.js applications</span> involves understanding the event loop, managing memory efficiently, and utilizing clustering for better CPU usage. Let's explore the most effective strategies...
                        </div>
                    </div>
                    <div class="comparison-panel">
                        <div class="comparison-header copied">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            Copied Content Found
                        </div>
                        <div class="comparison-body">
                            <span class="highlight">Optimizing Node.js applications</span> involves understanding the event loop, efficient memory management, and using clustering for better CPU usage. Let's look at the most effective strategies...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert-card" style="animation-delay: 0.25s">
            <div class="alert-card-main">
                <div class="alert-severity medium">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div class="alert-content">
                    <div class="alert-header">
                        <span class="alert-title">Complete Guide to React Hooks</span>
                        <span class="similarity-badge medium">56% Match</span>
                    </div>
                    <div class="alert-meta">
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Detected 5 days ago
                        </div>
                        <div class="alert-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            <a href="#">medium-clone.com</a>
                        </div>
                    </div>
                </div>
                <div class="alert-actions">
                    <button class="btn btn-secondary btn-sm">Review</button>
                    <button class="btn btn-ghost btn-sm">Dismiss</button>
                    <button class="expand-btn" onclick="toggleExpand(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="alert-expand">
                <div class="comparison-container">
                    <div class="comparison-panel">
                        <div class="comparison-header original">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            Your Original Content
                        </div>
                        <div class="comparison-body">
                            React Hooks revolutionized the way we write React components. <span class="highlight">With useState and useEffect</span>, you can now manage state and side effects in functional components without needing class components...
                        </div>
                    </div>
                    <div class="comparison-panel">
                        <div class="comparison-header copied">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            Copied Content Found
                        </div>
                        <div class="comparison-body">
                            React Hooks changed how we write React code. <span class="highlight">With useState and useEffect</span>, you can manage state and side effects in functional components, eliminating the need for class components...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleExpand(btn) {
        btn.classList.toggle('expanded');
        const expandSection = btn.closest('.alert-card').querySelector('.alert-expand');
        expandSection.classList.toggle('active');
    }

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endsection
