@extends('layouts.app')

@section('title', 'DMCA Requests - ContentShield AI')
@section('page-title', 'DMCA Requests')

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

    .dmca-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 32px;
    }

    .dmca-stat {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 20px;
        animation: slideUp 0.5s ease-out both;
    }

    .dmca-stat-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .dmca-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dmca-stat-icon svg {
        width: 18px;
        height: 18px;
    }

    .dmca-stat-icon.total {
        background: rgba(0, 212, 255, 0.1);
        color: var(--accent-cyan);
    }

    .dmca-stat-icon.pending {
        background: rgba(245, 158, 11, 0.1);
        color: var(--accent-amber);
    }

    .dmca-stat-icon.sent {
        background: rgba(139, 92, 246, 0.1);
        color: var(--accent-violet);
    }

    .dmca-stat-icon.resolved {
        background: rgba(16, 185, 129, 0.1);
        color: var(--accent-emerald);
    }

    .dmca-stat-icon.rejected {
        background: rgba(244, 63, 94, 0.1);
        color: var(--accent-rose);
    }

    .dmca-stat-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.75rem;
        font-weight: 600;
    }

    .dmca-stat-label {
        font-size: 0.8125rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .dmca-table-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .dmca-table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .dmca-table-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
    }

    .dmca-filters {
        display: flex;
        gap: 12px;
    }

    .filter-select {
        padding: 8px 32px 8px 12px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: 0.8125rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        cursor: pointer;
    }

    .recipient-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .recipient-badge.google {
        background: rgba(66, 133, 244, 0.1);
        color: #4285F4;
    }

    .recipient-badge.hosting {
        background: rgba(139, 92, 246, 0.1);
        color: var(--accent-violet);
    }

    .recipient-badge.cloudflare {
        background: rgba(245, 158, 11, 0.1);
        color: var(--accent-amber);
    }

    .ref-code {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: var(--text-muted);
        background: var(--bg-elevated);
        padding: 4px 8px;
        border-radius: 4px;
    }

    .timeline {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .timeline-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
    }

    .timeline-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .timeline-dot.complete { background: var(--accent-emerald); }
    .timeline-dot.pending { background: var(--accent-amber); }
    .timeline-dot.waiting { background: var(--text-muted); }

    @media (max-width: 1400px) {
        .dmca-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .dmca-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .dmca-stats {
            grid-template-columns: 1fr;
        }

        .dmca-filters {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <p class="page-description">Manage DMCA takedown requests and track their status</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('newDmcaModal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New DMCA Request
        </button>
    </div>

    <div class="dmca-stats">
        <div class="dmca-stat" style="animation-delay: 0.1s">
            <div class="dmca-stat-header">
                <div class="dmca-stat-icon total">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
            </div>
            <div class="dmca-stat-value">156</div>
            <div class="dmca-stat-label">Total Requests</div>
        </div>
        <div class="dmca-stat" style="animation-delay: 0.15s">
            <div class="dmca-stat-header">
                <div class="dmca-stat-icon pending">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </div>
            <div class="dmca-stat-value">12</div>
            <div class="dmca-stat-label">Pending</div>
        </div>
        <div class="dmca-stat" style="animation-delay: 0.2s">
            <div class="dmca-stat-header">
                <div class="dmca-stat-icon sent">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </div>
            </div>
            <div class="dmca-stat-value">28</div>
            <div class="dmca-stat-label">Sent</div>
        </div>
        <div class="dmca-stat" style="animation-delay: 0.25s">
            <div class="dmca-stat-header">
                <div class="dmca-stat-icon resolved">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </div>
            <div class="dmca-stat-value">109</div>
            <div class="dmca-stat-label">Resolved</div>
        </div>
        <div class="dmca-stat" style="animation-delay: 0.3s">
            <div class="dmca-stat-header">
                <div class="dmca-stat-icon rejected">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </div>
            </div>
            <div class="dmca-stat-value">7</div>
            <div class="dmca-stat-label">Rejected</div>
        </div>
    </div>

    <div class="dmca-table-card">
        <div class="dmca-table-header">
            <h2 class="dmca-table-title">Recent Requests</h2>
            <div class="dmca-filters">
                <select class="filter-select">
                    <option>All Status</option>
                    <option>Pending</option>
                    <option>Sent</option>
                    <option>Resolved</option>
                    <option>Rejected</option>
                </select>
                <select class="filter-select">
                    <option>All Recipients</option>
                    <option>Google</option>
                    <option>Hosting Provider</option>
                    <option>Cloudflare</option>
                </select>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Infringing URL</th>
                        <th>Original Content</th>
                        <th>Recipient</th>
                        <th>Status</th>
                        <th>Filed</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="ref-code">DMCA-2024-001</code></td>
                        <td>
                            <a href="#" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.8125rem;">spam-content-site.com/article</a>
                        </td>
                        <td style="font-size: 0.8125rem;">How to Build Modern APIs</td>
                        <td><span class="recipient-badge google">Google Search</span></td>
                        <td><span class="status-badge pending"><span class="status-dot"></span> Under Review</span></td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">2 days ago</td>
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
                        <td><code class="ref-code">DMCA-2024-002</code></td>
                        <td>
                            <a href="#" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.8125rem;">copycat-blog.net/post/123</a>
                        </td>
                        <td style="font-size: 0.8125rem;">TypeScript Generics Guide</td>
                        <td><span class="recipient-badge hosting">DigitalOcean</span></td>
                        <td><span class="status-badge monitoring"><span class="status-dot"></span> Sent</span></td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">3 days ago</td>
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
                        <td><code class="ref-code">DMCA-2024-003</code></td>
                        <td>
                            <a href="#" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.8125rem;">tech-scraper.io/article/45</a>
                        </td>
                        <td style="font-size: 0.8125rem;">React Hooks Complete Guide</td>
                        <td><span class="recipient-badge cloudflare">Cloudflare</span></td>
                        <td><span class="status-badge protected"><span class="status-dot"></span> Resolved</span></td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">1 week ago</td>
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
                        <td><code class="ref-code">DMCA-2024-004</code></td>
                        <td>
                            <a href="#" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.8125rem;">fake-blog.xyz/posts/vue</a>
                        </td>
                        <td style="font-size: 0.8125rem;">Vue.js Composition API</td>
                        <td><span class="recipient-badge google">Google Search</span></td>
                        <td><span class="status-badge protected"><span class="status-dot"></span> Resolved</span></td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">2 weeks ago</td>
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
                        <td><code class="ref-code">DMCA-2024-005</code></td>
                        <td>
                            <a href="#" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.8125rem;">content-stealer.com/node</a>
                        </td>
                        <td style="font-size: 0.8125rem;">Node.js Performance</td>
                        <td><span class="recipient-badge hosting">AWS</span></td>
                        <td><span class="status-badge alert"><span class="status-dot"></span> Rejected</span></td>
                        <td style="color: var(--text-secondary); font-size: 0.8125rem;">3 weeks ago</td>
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

<!-- New DMCA Modal -->
<div class="modal-overlay" id="newDmcaModal">
    <div class="modal" style="max-width: 640px;">
        <div class="modal-header">
            <h2 class="modal-title">File DMCA Takedown</h2>
            <button class="modal-close" onclick="closeModal('newDmcaModal')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Infringing URL *</label>
                <input type="url" class="form-input" placeholder="https://example.com/copied-content">
            </div>
            <div class="form-group">
                <label class="form-label">Your Original Content URL *</label>
                <input type="url" class="form-input" placeholder="https://yoursite.com/original-article">
            </div>
            <div class="form-group">
                <label class="form-label">Send To *</label>
                <select class="form-input">
                    <option>Google Search (for search deindexing)</option>
                    <option>Hosting Provider (for content removal)</option>
                    <option>Cloudflare (if behind Cloudflare)</option>
                    <option>All of the above</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Additional Notes</label>
                <textarea class="form-textarea" placeholder="Any additional information about the infringement..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('newDmcaModal')">Cancel</button>
            <button class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                Send DMCA Notice
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

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
</script>
@endsection
