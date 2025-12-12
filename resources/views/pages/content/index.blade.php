@extends('layouts.app')

@section('title', 'Protected Content - ContentShield AI')
@section('page-title', 'Protected Content')

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

    .filter-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-select {
        padding: 8px 32px 8px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: 0.875rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--accent-cyan);
    }

    .search-filter {
        flex: 1;
        min-width: 300px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
    }

    .search-filter input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .search-filter svg {
        width: 18px;
        height: 18px;
        color: var(--text-muted);
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    .content-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all var(--transition-base);
        animation: slideUp 0.5s ease-out both;
    }

    .content-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-4px);
        box-shadow: var(--shadow-card);
    }

    .content-card-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-subtle);
    }

    .content-card-title {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .content-card-url {
        font-size: 0.75rem;
        color: var(--accent-cyan);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .content-card-url:hover {
        text-decoration: underline;
    }

    .content-card-body {
        padding: 20px;
    }

    .content-meta {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .content-meta-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.8125rem;
    }

    .content-meta-label {
        color: var(--text-muted);
    }

    .content-meta-value {
        font-weight: 500;
        font-family: 'JetBrains Mono', monospace;
    }

    .content-card-footer {
        padding: 16px 20px;
        background: var(--bg-elevated);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .word-count {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .card-actions {
        display: flex;
        gap: 8px;
    }

    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 32px;
    }

    .page-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        font-size: 0.875rem;
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .page-btn:hover {
        background: var(--bg-card-hover);
        color: var(--text-primary);
    }

    .page-btn.active {
        background: var(--accent-cyan);
        color: var(--bg-primary);
        border-color: var(--accent-cyan);
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .bulk-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        margin-bottom: 24px;
    }

    .bulk-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--accent-cyan);
        cursor: pointer;
    }

    .bulk-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-filter {
            min-width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <p class="page-description">Manage your protected content and monitor for plagiarism</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addContentModal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Content
        </button>
    </div>

    <div class="filter-bar">
        <div class="search-filter">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" placeholder="Search by title, URL, or fingerprint...">
        </div>
        <div class="filter-group">
            <select class="filter-select">
                <option>All Status</option>
                <option>Protected</option>
                <option>Monitoring</option>
                <option>Alert</option>
            </select>
            <select class="filter-select">
                <option>Last Scan</option>
                <option>Last 24h</option>
                <option>Last 7 days</option>
                <option>Last 30 days</option>
            </select>
            <select class="filter-select">
                <option>Sort by</option>
                <option>Newest First</option>
                <option>Oldest First</option>
                <option>Most Matches</option>
            </select>
        </div>
    </div>

    <div class="bulk-actions">
        <input type="checkbox" class="bulk-checkbox" id="selectAll">
        <span class="bulk-label">Select all</span>
        <div style="flex: 1;"></div>
        <button class="btn btn-secondary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            Scan Selected
        </button>
        <button class="btn btn-secondary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
        </button>
    </div>

    <div class="content-grid">
        <div class="content-card" style="animation-delay: 0.1s">
            <div class="content-card-header">
                <h3 class="content-card-title">How to Build Modern APIs with Laravel</h3>
                <a href="#" class="content-card-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    example.com/build-apis
                </a>
            </div>
            <div class="content-card-body">
                <div class="content-meta">
                    <div class="content-meta-item">
                        <span class="content-meta-label">Status</span>
                        <span class="status-badge protected"><span class="status-dot"></span> Protected</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Fingerprint</span>
                        <span class="content-meta-value">a1b2c3d4...f8g9</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Matches Found</span>
                        <span class="content-meta-value text-rose">3</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Last Scan</span>
                        <span class="content-meta-value">2 hours ago</span>
                    </div>
                </div>
            </div>
            <div class="content-card-footer">
                <span class="word-count">2,450 words</span>
                <div class="card-actions">
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card" style="animation-delay: 0.15s">
            <div class="content-card-header">
                <h3 class="content-card-title">Complete Guide to React Hooks</h3>
                <a href="#" class="content-card-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    example.com/react-hooks
                </a>
            </div>
            <div class="content-card-body">
                <div class="content-meta">
                    <div class="content-meta-item">
                        <span class="content-meta-label">Status</span>
                        <span class="status-badge monitoring"><span class="status-dot"></span> Monitoring</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Fingerprint</span>
                        <span class="content-meta-value">e5f6g7h8...l2m3</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Matches Found</span>
                        <span class="content-meta-value">0</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Last Scan</span>
                        <span class="content-meta-value">5 hours ago</span>
                    </div>
                </div>
            </div>
            <div class="content-card-footer">
                <span class="word-count">3,120 words</span>
                <div class="card-actions">
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card" style="animation-delay: 0.2s">
            <div class="content-card-header">
                <h3 class="content-card-title">Understanding TypeScript Generics</h3>
                <a href="#" class="content-card-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    example.com/ts-generics
                </a>
            </div>
            <div class="content-card-body">
                <div class="content-meta">
                    <div class="content-meta-item">
                        <span class="content-meta-label">Status</span>
                        <span class="status-badge alert"><span class="status-dot"></span> Alert</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Fingerprint</span>
                        <span class="content-meta-value">n4o5p6q7...r8s9</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Matches Found</span>
                        <span class="content-meta-value text-rose">7</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Last Scan</span>
                        <span class="content-meta-value">1 day ago</span>
                    </div>
                </div>
            </div>
            <div class="content-card-footer">
                <span class="word-count">1,890 words</span>
                <div class="card-actions">
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card" style="animation-delay: 0.25s">
            <div class="content-card-header">
                <h3 class="content-card-title">Vue.js 3 Composition API Deep Dive</h3>
                <a href="#" class="content-card-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    example.com/vue-composition
                </a>
            </div>
            <div class="content-card-body">
                <div class="content-meta">
                    <div class="content-meta-item">
                        <span class="content-meta-label">Status</span>
                        <span class="status-badge protected"><span class="status-dot"></span> Protected</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Fingerprint</span>
                        <span class="content-meta-value">q8r9s0t1...x4y5</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Matches Found</span>
                        <span class="content-meta-value">0</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Last Scan</span>
                        <span class="content-meta-value">3 hours ago</span>
                    </div>
                </div>
            </div>
            <div class="content-card-footer">
                <span class="word-count">4,200 words</span>
                <div class="card-actions">
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card" style="animation-delay: 0.3s">
            <div class="content-card-header">
                <h3 class="content-card-title">Node.js Performance Optimization</h3>
                <a href="#" class="content-card-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    example.com/node-performance
                </a>
            </div>
            <div class="content-card-body">
                <div class="content-meta">
                    <div class="content-meta-item">
                        <span class="content-meta-label">Status</span>
                        <span class="status-badge monitoring"><span class="status-dot"></span> Monitoring</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Fingerprint</span>
                        <span class="content-meta-value">z6a7b8c9...g2h3</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Matches Found</span>
                        <span class="content-meta-value text-rose">1</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Last Scan</span>
                        <span class="content-meta-value">12 hours ago</span>
                    </div>
                </div>
            </div>
            <div class="content-card-footer">
                <span class="word-count">2,780 words</span>
                <div class="card-actions">
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card" style="animation-delay: 0.35s">
            <div class="content-card-header">
                <h3 class="content-card-title">Database Design Best Practices</h3>
                <a href="#" class="content-card-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    example.com/db-design
                </a>
            </div>
            <div class="content-card-body">
                <div class="content-meta">
                    <div class="content-meta-item">
                        <span class="content-meta-label">Status</span>
                        <span class="status-badge protected"><span class="status-dot"></span> Protected</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Fingerprint</span>
                        <span class="content-meta-value">i4j5k6l7...o0p1</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Matches Found</span>
                        <span class="content-meta-value">0</span>
                    </div>
                    <div class="content-meta-item">
                        <span class="content-meta-label">Last Scan</span>
                        <span class="content-meta-value">6 hours ago</span>
                    </div>
                </div>
            </div>
            <div class="content-card-footer">
                <span class="word-count">3,560 words</span>
                <div class="card-actions">
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <button class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="pagination">
        <button class="page-btn" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <button class="page-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>
    </div>
</div>

<!-- Add Content Modal -->
<div class="modal-overlay" id="addContentModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Add Protected Content</h2>
            <button class="modal-close" onclick="closeModal('addContentModal')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Content URL</label>
                <input type="url" class="form-input" placeholder="https://example.com/your-article">
            </div>
            <div class="form-group">
                <label class="form-label">Title (Optional)</label>
                <input type="text" class="form-input" placeholder="Article title">
            </div>
            <div class="form-group">
                <label class="form-label">Content (Or paste your content directly)</label>
                <textarea class="form-textarea" placeholder="Paste your content here..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addContentModal')">Cancel</button>
            <button class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Protect Content
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
</script>
@endsection
