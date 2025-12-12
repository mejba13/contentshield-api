@extends('layouts.app')

@section('title', 'Schedule Scans - ContentShield AI')
@section('page-title', 'Schedule Scans')

@section('styles')
<style>
    .schedule-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .schedule-card {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        padding: 24px;
        animation: slideUp 0.5s ease-out both;
        transition: all var(--transition-base);
    }

    .schedule-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-2px);
    }

    .schedule-card.active {
        border-color: var(--accent-cyan);
        box-shadow: 0 0 0 1px var(--accent-cyan-glow);
    }

    .schedule-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .schedule-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .schedule-icon.hourly {
        background: rgba(0, 212, 255, 0.1);
        color: var(--accent-cyan);
    }

    .schedule-icon.daily {
        background: rgba(139, 92, 246, 0.1);
        color: var(--accent-violet);
    }

    .schedule-icon.weekly {
        background: rgba(16, 185, 129, 0.1);
        color: var(--accent-emerald);
    }

    .schedule-icon svg {
        width: 24px;
        height: 24px;
    }

    .schedule-title {
        font-weight: 600;
        font-size: 1.125rem;
        margin-bottom: 4px;
    }

    .schedule-desc {
        font-size: 0.8125rem;
        color: var(--text-muted);
        margin-bottom: 16px;
    }

    .schedule-info {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 0.8125rem;
        color: var(--text-secondary);
        margin-bottom: 16px;
    }

    .schedule-info-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .schedule-info-item svg {
        width: 14px;
        height: 14px;
        color: var(--text-muted);
    }

    .schedule-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 16px;
        border-top: 1px solid var(--border-subtle);
    }

    .next-run {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .next-run strong {
        color: var(--text-secondary);
    }

    /* Content Selection */
    .content-selection {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        overflow: hidden;
        animation: slideUp 0.5s ease-out both;
        animation-delay: 0.2s;
    }

    .content-selection-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .content-selection-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
    }

    .selection-count {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .content-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .content-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-subtle);
        transition: background var(--transition-fast);
    }

    .content-item:last-child {
        border-bottom: none;
    }

    .content-item:hover {
        background: var(--bg-card-hover);
    }

    .content-checkbox {
        width: 20px;
        height: 20px;
        accent-color: var(--accent-cyan);
        cursor: pointer;
    }

    .content-details {
        flex: 1;
        min-width: 0;
    }

    .content-name {
        font-weight: 500;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .content-url {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .content-schedule {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .schedule-select {
        padding: 6px 28px 6px 10px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        font-size: 0.75rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 6px center;
        cursor: pointer;
    }

    /* Calendar Preview */
    .calendar-preview {
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-lg);
        margin-top: 24px;
        animation: slideUp 0.5s ease-out both;
        animation-delay: 0.3s;
    }

    .calendar-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .calendar-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
    }

    .calendar-nav {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .calendar-nav-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-elevated);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-sm);
        color: var(--text-secondary);
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .calendar-nav-btn:hover {
        background: var(--bg-card-hover);
        color: var(--text-primary);
    }

    .calendar-nav-btn svg {
        width: 16px;
        height: 16px;
    }

    .calendar-month {
        font-weight: 500;
        min-width: 140px;
        text-align: center;
    }

    .calendar-grid {
        padding: 24px;
    }

    .calendar-days-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }

    .calendar-day-name {
        text-align: center;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 8px;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        font-size: 0.875rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        position: relative;
    }

    .calendar-day:hover {
        background: var(--bg-elevated);
    }

    .calendar-day.today {
        background: var(--accent-cyan-glow);
        color: var(--accent-cyan);
        font-weight: 600;
    }

    .calendar-day.has-scans::after {
        content: '';
        position: absolute;
        bottom: 6px;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: var(--accent-cyan);
    }

    .calendar-day.other-month {
        color: var(--text-muted);
        opacity: 0.5;
    }

    @media (max-width: 1200px) {
        .schedule-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .schedule-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <p class="page-description">Configure automated scanning schedules for your content</p>
        </div>
        <button class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create Schedule
        </button>
    </div>

    <div class="schedule-grid">
        <div class="schedule-card active" style="animation-delay: 0.1s">
            <div class="schedule-header">
                <div class="schedule-icon hourly">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="toggle active" onclick="this.classList.toggle('active')"></div>
            </div>
            <h3 class="schedule-title">Hourly Scan</h3>
            <p class="schedule-desc">Scan high-priority content every hour for immediate plagiarism detection</p>
            <div class="schedule-info">
                <div class="schedule-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    5 content items
                </div>
                <div class="schedule-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    24 scans/day
                </div>
            </div>
            <div class="schedule-footer">
                <div class="next-run">Next run: <strong>in 23 minutes</strong></div>
                <button class="btn btn-ghost btn-sm">Edit</button>
            </div>
        </div>

        <div class="schedule-card active" style="animation-delay: 0.15s">
            <div class="schedule-header">
                <div class="schedule-icon daily">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div class="toggle active" onclick="this.classList.toggle('active')"></div>
            </div>
            <h3 class="schedule-title">Daily Scan</h3>
            <p class="schedule-desc">Comprehensive daily scan of all protected content at midnight</p>
            <div class="schedule-info">
                <div class="schedule-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    All content (1,247)
                </div>
                <div class="schedule-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    00:00 UTC
                </div>
            </div>
            <div class="schedule-footer">
                <div class="next-run">Next run: <strong>in 8 hours</strong></div>
                <button class="btn btn-ghost btn-sm">Edit</button>
            </div>
        </div>

        <div class="schedule-card" style="animation-delay: 0.2s">
            <div class="schedule-header">
                <div class="schedule-icon weekly">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <path d="M8 14h.01"/>
                        <path d="M12 14h.01"/>
                        <path d="M16 14h.01"/>
                        <path d="M8 18h.01"/>
                        <path d="M12 18h.01"/>
                        <path d="M16 18h.01"/>
                    </svg>
                </div>
                <div class="toggle" onclick="this.classList.toggle('active')"></div>
            </div>
            <h3 class="schedule-title">Weekly Deep Scan</h3>
            <p class="schedule-desc">Extended weekly scan with AI-powered analysis</p>
            <div class="schedule-info">
                <div class="schedule-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    All content
                </div>
                <div class="schedule-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                    </svg>
                    Sundays
                </div>
            </div>
            <div class="schedule-footer">
                <div class="next-run">Disabled</div>
                <button class="btn btn-ghost btn-sm">Edit</button>
            </div>
        </div>
    </div>

    <div class="content-selection">
        <div class="content-selection-header">
            <h2 class="content-selection-title">Content Schedule Assignment</h2>
            <span class="selection-count">12 items selected</span>
        </div>
        <div class="content-list">
            <div class="content-item">
                <input type="checkbox" class="content-checkbox" checked>
                <div class="content-details">
                    <div class="content-name">How to Build Modern APIs with Laravel</div>
                    <div class="content-url">example.com/build-apis</div>
                </div>
                <div class="content-schedule">
                    <select class="schedule-select">
                        <option selected>Hourly</option>
                        <option>Daily</option>
                        <option>Weekly</option>
                    </select>
                </div>
            </div>
            <div class="content-item">
                <input type="checkbox" class="content-checkbox" checked>
                <div class="content-details">
                    <div class="content-name">Complete Guide to React Hooks</div>
                    <div class="content-url">example.com/react-hooks</div>
                </div>
                <div class="content-schedule">
                    <select class="schedule-select">
                        <option>Hourly</option>
                        <option selected>Daily</option>
                        <option>Weekly</option>
                    </select>
                </div>
            </div>
            <div class="content-item">
                <input type="checkbox" class="content-checkbox" checked>
                <div class="content-details">
                    <div class="content-name">Understanding TypeScript Generics</div>
                    <div class="content-url">example.com/ts-generics</div>
                </div>
                <div class="content-schedule">
                    <select class="schedule-select">
                        <option selected>Hourly</option>
                        <option>Daily</option>
                        <option>Weekly</option>
                    </select>
                </div>
            </div>
            <div class="content-item">
                <input type="checkbox" class="content-checkbox" checked>
                <div class="content-details">
                    <div class="content-name">Vue.js 3 Composition API Deep Dive</div>
                    <div class="content-url">example.com/vue-composition</div>
                </div>
                <div class="content-schedule">
                    <select class="schedule-select">
                        <option>Hourly</option>
                        <option selected>Daily</option>
                        <option>Weekly</option>
                    </select>
                </div>
            </div>
            <div class="content-item">
                <input type="checkbox" class="content-checkbox">
                <div class="content-details">
                    <div class="content-name">Node.js Performance Optimization</div>
                    <div class="content-url">example.com/node-performance</div>
                </div>
                <div class="content-schedule">
                    <select class="schedule-select">
                        <option>Hourly</option>
                        <option selected>Daily</option>
                        <option>Weekly</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="calendar-preview">
        <div class="calendar-header">
            <h2 class="calendar-title">Scan Calendar</h2>
            <div class="calendar-nav">
                <button class="calendar-nav-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
                <span class="calendar-month">December 2024</span>
                <button class="calendar-nav-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="calendar-grid">
            <div class="calendar-days-header">
                <div class="calendar-day-name">Sun</div>
                <div class="calendar-day-name">Mon</div>
                <div class="calendar-day-name">Tue</div>
                <div class="calendar-day-name">Wed</div>
                <div class="calendar-day-name">Thu</div>
                <div class="calendar-day-name">Fri</div>
                <div class="calendar-day-name">Sat</div>
            </div>
            <div class="calendar-days">
                <div class="calendar-day other-month">24</div>
                <div class="calendar-day other-month">25</div>
                <div class="calendar-day other-month">26</div>
                <div class="calendar-day other-month">27</div>
                <div class="calendar-day other-month">28</div>
                <div class="calendar-day other-month">29</div>
                <div class="calendar-day other-month">30</div>
                <div class="calendar-day has-scans">1</div>
                <div class="calendar-day has-scans">2</div>
                <div class="calendar-day has-scans">3</div>
                <div class="calendar-day has-scans">4</div>
                <div class="calendar-day has-scans">5</div>
                <div class="calendar-day has-scans">6</div>
                <div class="calendar-day has-scans">7</div>
                <div class="calendar-day has-scans">8</div>
                <div class="calendar-day has-scans">9</div>
                <div class="calendar-day has-scans">10</div>
                <div class="calendar-day has-scans">11</div>
                <div class="calendar-day today has-scans">12</div>
                <div class="calendar-day has-scans">13</div>
                <div class="calendar-day has-scans">14</div>
                <div class="calendar-day has-scans">15</div>
                <div class="calendar-day has-scans">16</div>
                <div class="calendar-day has-scans">17</div>
                <div class="calendar-day has-scans">18</div>
                <div class="calendar-day has-scans">19</div>
                <div class="calendar-day has-scans">20</div>
                <div class="calendar-day has-scans">21</div>
                <div class="calendar-day has-scans">22</div>
                <div class="calendar-day has-scans">23</div>
                <div class="calendar-day has-scans">24</div>
                <div class="calendar-day has-scans">25</div>
                <div class="calendar-day has-scans">26</div>
                <div class="calendar-day has-scans">27</div>
                <div class="calendar-day has-scans">28</div>
                <div class="calendar-day has-scans">29</div>
                <div class="calendar-day has-scans">30</div>
                <div class="calendar-day has-scans">31</div>
                <div class="calendar-day other-month">1</div>
                <div class="calendar-day other-month">2</div>
                <div class="calendar-day other-month">3</div>
                <div class="calendar-day other-month">4</div>
            </div>
        </div>
    </div>
</div>
@endsection
