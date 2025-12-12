@extends('layouts.app')

@section('title', 'API Keys - ContentShield AI')
@section('page-title', 'API Keys')

@section('styles')
<style>
    .api-info-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 24px;
        animation: slideUp 0.5s ease-out both;
    }

    .api-info-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
    }

    .api-info-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        background: var(--accent-cyan-glow);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-cyan);
    }

    .api-info-icon svg {
        width: 24px;
        height: 24px;
    }

    .api-info-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
    }

    .api-info-desc {
        color: var(--text-secondary);
        font-size: 0.875rem;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .api-docs-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent-cyan);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .api-docs-link:hover {
        text-decoration: underline;
    }

    .api-docs-link svg {
        width: 16px;
        height: 16px;
    }

    .keys-section {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
        animation: slideUp 0.5s ease-out both;
        animation-delay: 0.1s;
    }

    .keys-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .keys-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
    }

    .key-card {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        transition: background var(--transition-fast);
    }

    .key-card:last-child {
        border-bottom: none;
    }

    .key-card:hover {
        background: var(--bg-card-hover);
    }

    .key-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .key-name {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .key-badge {
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 100px;
        text-transform: uppercase;
    }

    .key-badge.live {
        background: rgba(16, 185, 129, 0.1);
        color: var(--accent-emerald);
    }

    .key-badge.test {
        background: rgba(245, 158, 11, 0.1);
        color: var(--accent-amber);
    }

    .key-value-container {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .key-value {
        flex: 1;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.875rem;
        padding: 12px 16px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .key-hidden {
        letter-spacing: 0.2em;
    }

    .key-actions {
        display: flex;
        gap: 8px;
    }

    .key-meta {
        display: flex;
        align-items: center;
        gap: 24px;
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .key-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .key-meta-item svg {
        width: 14px;
        height: 14px;
    }

    .usage-bar {
        width: 100px;
        height: 6px;
        background: var(--bg-elevated);
        border-radius: 3px;
        overflow: hidden;
    }

    .usage-fill {
        height: 100%;
        background: var(--accent-cyan);
        border-radius: 3px;
    }

    .empty-state {
        padding: 48px;
        text-align: center;
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        color: var(--text-muted);
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1rem;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 20px;
    }

    .webhook-section {
        margin-top: 24px;
        animation: slideUp 0.5s ease-out both;
        animation-delay: 0.2s;
    }

    @media (max-width: 768px) {
        .key-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .key-meta {
            flex-wrap: wrap;
            gap: 12px;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <p class="page-description">Manage your API keys for WordPress plugin integration</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('createKeyModal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create API Key
        </button>
    </div>

    <div class="api-info-card">
        <div class="api-info-header">
            <div class="api-info-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                </svg>
            </div>
            <div>
                <h3 class="api-info-title">API Authentication</h3>
            </div>
        </div>
        <p class="api-info-desc">
            Use your API keys to authenticate requests from your WordPress plugin. Each key is tied to a specific site URL
            and can be revoked at any time. Keep your keys secure and never share them publicly.
        </p>
        <a href="#" class="api-docs-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            View API Documentation
        </a>
    </div>

    <div class="keys-section">
        <div class="keys-header">
            <h2 class="keys-title">Your API Keys</h2>
            <span class="text-sm text-muted">2 of 5 keys used</span>
        </div>

        <div class="key-card">
            <div class="key-header">
                <div class="key-name">
                    Production Key
                    <span class="key-badge live">Live</span>
                </div>
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
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Rename
                        </div>
                        <div class="dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="1 4 1 10 7 10"/>
                                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                            </svg>
                            Regenerate
                        </div>
                        <div class="dropdown-item" style="color: var(--accent-rose);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                            </svg>
                            Revoke
                        </div>
                    </div>
                </div>
            </div>
            <div class="key-value-container">
                <div class="key-value">
                    <span class="key-hidden">CSAI-XXXX-XXXX-XXXX-XXXX</span>
                </div>
                <button class="btn btn-secondary btn-sm" onclick="toggleKeyVisibility(this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Show
                </button>
                <button class="btn btn-secondary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                    </svg>
                    Copy
                </button>
            </div>
            <div class="key-meta">
                <div class="key-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    example.com
                </div>
                <div class="key-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Created Dec 1, 2024
                </div>
                <div class="key-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    1,247 requests
                    <div class="usage-bar">
                        <div class="usage-fill" style="width: 45%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="key-card">
            <div class="key-header">
                <div class="key-name">
                    Development Key
                    <span class="key-badge test">Test</span>
                </div>
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
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Rename
                        </div>
                        <div class="dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="1 4 1 10 7 10"/>
                                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                            </svg>
                            Regenerate
                        </div>
                        <div class="dropdown-item" style="color: var(--accent-rose);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                            </svg>
                            Revoke
                        </div>
                    </div>
                </div>
            </div>
            <div class="key-value-container">
                <div class="key-value">
                    <span class="key-hidden">CSAI-XXXX-XXXX-XXXX-XXXX</span>
                </div>
                <button class="btn btn-secondary btn-sm" onclick="toggleKeyVisibility(this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Show
                </button>
                <button class="btn btn-secondary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                    </svg>
                    Copy
                </button>
            </div>
            <div class="key-meta">
                <div class="key-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    localhost:8000
                </div>
                <div class="key-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Created Dec 5, 2024
                </div>
                <div class="key-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    156 requests
                    <div class="usage-bar">
                        <div class="usage-fill" style="width: 8%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="keys-section webhook-section">
        <div class="keys-header">
            <h2 class="keys-title">Webhook Endpoints</h2>
        </div>
        <div class="key-card">
            <div class="key-header">
                <div class="key-name">
                    Plagiarism Alert Webhook
                    <span class="key-badge live">Active</span>
                </div>
            </div>
            <div class="key-value-container">
                <div class="key-value">
                    <span>https://example.com/webhooks/contentshield</span>
                </div>
                <button class="btn btn-secondary btn-sm">Edit</button>
            </div>
            <div class="key-meta">
                <div class="key-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Last successful: 2 hours ago
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Key Modal -->
<div class="modal-overlay" id="createKeyModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Create API Key</h2>
            <button class="modal-close" onclick="closeModal('createKeyModal')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Key Name *</label>
                <input type="text" class="form-input" placeholder="e.g., Production Key">
            </div>
            <div class="form-group">
                <label class="form-label">Site URL *</label>
                <input type="url" class="form-input" placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label class="form-label">Environment</label>
                <select class="form-input">
                    <option value="live">Live (Production)</option>
                    <option value="test">Test (Development)</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('createKeyModal')">Cancel</button>
            <button class="btn btn-primary">Create Key</button>
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

    function toggleKeyVisibility(btn) {
        const keySpan = btn.closest('.key-value-container').querySelector('.key-hidden');
        if (keySpan.textContent.includes('XXXX')) {
            keySpan.textContent = 'CSAI-UPBV-JZNA-KYIL-IFZ1';
            btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            </svg> Hide`;
        } else {
            keySpan.textContent = 'CSAI-XXXX-XXXX-XXXX-XXXX';
            btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg> Show`;
        }
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });
</script>
@endsection
