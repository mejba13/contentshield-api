# ContentShield AI - Complete Development Guide

> **AI-Powered Content Protection & Plagiarism Defense WordPress Plugin**

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Architecture Diagram](#3-architecture-diagram)
4. [WordPress Plugin Structure](#4-wordpress-plugin-structure)
5. [SaaS Backend Structure](#5-saas-backend-structure)
6. [Free vs Pro Features](#6-free-vs-pro-features)
7. [License Key System](#7-license-key-system)
8. [Payment Integration](#8-payment-integration)
9. [WordPress Guidelines Compliance](#9-wordpress-guidelines-compliance)
10. [Security Best Practices](#10-security-best-practices)
11. [Database Schema](#11-database-schema)
12. [API Endpoints](#12-api-endpoints)
13. [Implementation Roadmap](#13-implementation-roadmap)
14. [Submission Checklist](#14-submission-checklist)

---

## 1. Project Overview

### What is ContentShield AI?

ContentShield AI is a WordPress plugin that helps content creators protect their work from theft and plagiarism through:

- **Invisible Watermarking**: Zero-width character fingerprints embedded in content
- **Content Fingerprinting**: SimHash-based content identification
- **Plagiarism Detection**: Scan URLs to find stolen content
- **Automated DMCA**: Generate and send takedown notices (Pro)
- **Web Monitoring**: Continuous scanning for content theft (Pro)

### Business Model

| Tier | Price | Features |
|------|-------|----------|
| **Free** | $0 | Watermarking, manual scanning, basic reports |
| **Starter** | $9/month | Weekly monitoring, 50 posts, DMCA templates |
| **Pro** | $19/month | Daily monitoring, 500 posts, auto-DMCA |
| **Agency** | $49/month | Hourly monitoring, unlimited, white-label |

---

## 2. Technology Stack

### 2.1 WordPress Plugin (Free Version)

| Layer | Technology | Version | Purpose |
|-------|------------|---------|---------|
| **Language** | PHP | 7.4+ (recommend 8.0+) | Core plugin logic |
| **Database** | MySQL | 5.7+ | WordPress native DB |
| **Admin UI** | WordPress Settings API | - | Native admin pages |
| **REST API** | WordPress REST API | - | AJAX endpoints |
| **JavaScript** | Vanilla JS + jQuery | - | Admin interactions |
| **CSS** | Plain CSS | - | Admin styling |
| **Standards** | WordPress Coding Standards | 3.0 | Code quality |
| **Build Tools** | NPM + Webpack | - | Asset compilation |

### 2.2 SaaS Backend (Pro Features)

| Layer | Technology | Version | Purpose |
|-------|------------|---------|---------|
| **Framework** | Laravel | 10.x / 11.x | API backend |
| **Language** | PHP | 8.1+ | Server-side logic |
| **Database** | PostgreSQL | 15+ | Primary data store |
| **Vector DB** | pgvector | 0.5+ | AI embeddings search |
| **Cache** | Redis | 7+ | Caching & sessions |
| **Queue** | Laravel Queue + Redis | - | Background jobs |
| **Auth** | Laravel Sanctum | - | API authentication |
| **HTTP Client** | Guzzle | 7+ | External API calls |

### 2.3 Payment & Billing

| Provider | Use Case | Fees |
|----------|----------|------|
| **LemonSqueezy** (Recommended) | SaaS subscriptions | 5% + $0.50 |
| **Paddle** | Global tax handling | 5-10% |
| **Stripe** | Custom control | 2.9% + $0.30 |

### 2.4 AI & Content Analysis

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Fingerprinting** | SimHash (PHP) | Fast content identification |
| **Semantic Matching** | OpenAI API / Claude API | AI-powered similarity |
| **Vector Search** | pgvector | Similarity queries |
| **Web Scraping** | Guzzle / Laravel HTTP | Fetch external pages |

### 2.5 Infrastructure (Recommended)

| Service | Provider Options | Purpose |
|---------|------------------|---------|
| **Hosting** | DigitalOcean, AWS, Hetzner | Server hosting |
| **CDN** | Cloudflare | Asset delivery |
| **Email** | Postmark, SendGrid | Transactional emails |
| **Monitoring** | Sentry, Laravel Telescope | Error tracking |

---

## 3. Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                      CUSTOMER'S WORDPRESS SITE                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │           ContentShield AI Plugin (PHP)                  │   │
│   ├─────────────────────────────────────────────────────────┤   │
│   │                                                          │   │
│   │   ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │   │
│   │   │ Watermark   │  │ Fingerprint │  │  Scanner    │    │   │
│   │   │ (Zero-width)│  │  (SimHash)  │  │  (Manual)   │    │   │
│   │   └─────────────┘  └─────────────┘  └─────────────┘    │   │
│   │                                                          │   │
│   │   ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │   │
│   │   │   Admin     │  │  Settings   │  │  License    │    │   │
│   │   │   Pages     │  │    API      │  │   Client    │    │   │
│   │   └─────────────┘  └─────────────┘  └──────┬──────┘    │   │
│   │                                             │            │   │
│   └─────────────────────────────────────────────┼────────────┘   │
│                                                  │                │
│   ┌──────────────────┐    ┌──────────────────┐  │                │
│   │  WordPress DB    │    │   wp_options     │  │                │
│   │  (MySQL)         │    │   (License)      │  │                │
│   └──────────────────┘    └──────────────────┘  │                │
│                                                  │                │
└──────────────────────────────────────────────────┼────────────────┘
                                                   │
                                                   │ HTTPS
                                                   │ REST API
                                                   ▼
┌─────────────────────────────────────────────────────────────────┐
│                    YOUR SAAS BACKEND                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │                   Laravel Application                    │   │
│   ├─────────────────────────────────────────────────────────┤   │
│   │                                                          │   │
│   │   ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │   │
│   │   │  License    │  │  Content    │  │ Monitoring  │    │   │
│   │   │    API      │  │Registration │  │  Service    │    │   │
│   │   └─────────────┘  └─────────────┘  └─────────────┘    │   │
│   │                                                          │   │
│   │   ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │   │
│   │   │    DMCA     │  │  AI Match   │  │  Payment    │    │   │
│   │   │ Automation  │  │   Engine    │  │  Webhooks   │    │   │
│   │   └─────────────┘  └─────────────┘  └─────────────┘    │   │
│   │                                                          │   │
│   └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│   ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│   │  PostgreSQL  │  │    Redis     │  │   Queue      │         │
│   │  + pgvector  │  │   Cache      │  │  Workers     │         │
│   └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Webhooks
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES                             │
├─────────────────────────────────────────────────────────────────┤
│   ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│   │LemonSqueezy │  │  OpenAI /   │  │   Google    │            │
│   │  Payments   │  │   Claude    │  │   Search    │            │
│   └─────────────┘  └─────────────┘  └─────────────┘            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. WordPress Plugin Structure

```
contentshield-ai/
├── contentshield-ai.php              # Main plugin file (entry point)
├── uninstall.php                     # Clean uninstall handler
├── readme.txt                        # WordPress.org readme
├── LICENSE                           # GPL v2 license text
├── CHANGELOG.md                      # Version history
├── composer.json                     # PHP dependencies (dev)
├── package.json                      # NPM dependencies (build)
├── phpcs.xml                         # Coding standards config
│
├── includes/                         # PHP Classes
│   ├── class-contentshield-ai.php   # Main plugin class
│   ├── class-activator.php          # Activation hooks
│   ├── class-deactivator.php        # Deactivation hooks
│   │
│   ├── admin/                        # Admin functionality
│   │   ├── class-admin.php          # Admin menus & pages
│   │   ├── class-settings.php       # Settings API
│   │   └── views/                   # Admin templates
│   │       ├── dashboard.php
│   │       ├── settings.php
│   │       ├── protected-content.php
│   │       ├── scans.php
│   │       └── pro.php
│   │
│   ├── public/                       # Frontend functionality
│   │   ├── class-public.php         # Public hooks
│   │   └── class-copy-protection.php
│   │
│   ├── core/                         # Core features
│   │   ├── class-fingerprint.php    # SimHash fingerprinting
│   │   ├── class-watermark.php      # Zero-width watermarks
│   │   ├── class-scanner.php        # URL scanning
│   │   └── class-protection.php     # Content protection
│   │
│   └── api/                          # API integration
│       ├── class-api-client.php     # SaaS API client
│       ├── class-license.php        # License validation
│       └── class-rest-endpoints.php # Plugin REST API
│
├── assets/                           # Static assets
│   ├── css/
│   │   ├── admin.css                # Admin styles
│   │   └── public.css               # Frontend styles
│   ├── js/
│   │   ├── admin.js                 # Admin scripts
│   │   └── public.js                # Frontend scripts
│   └── images/
│       ├── icon-128x128.png         # Plugin icon
│       ├── icon-256x256.png
│       ├── banner-772x250.png       # Plugin banner
│       └── banner-1544x500.png
│
├── languages/                        # Translations
│   └── contentshield-ai.pot         # Translation template
│
└── templates/                        # User-overridable templates
    └── dmca-notice.php
```

### Main Plugin File Header

```php
<?php
/**
 * Plugin Name:       ContentShield AI
 * Plugin URI:        https://contentshield.ai
 * Description:       AI-powered content protection and plagiarism defense.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Engr Mejba Ahmed
 * Author URI:        https://www.mejba.me/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       contentshield-ai
 * Domain Path:       /languages
 */
```

---

## 5. SaaS Backend Structure

### Laravel Project Structure

```
contentshield-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── LicenseController.php
│   │   │   │   ├── ContentController.php
│   │   │   │   ├── MonitoringController.php
│   │   │   │   └── DmcaController.php
│   │   │   └── Webhooks/
│   │   │       └── LemonSqueezyController.php
│   │   ├── Middleware/
│   │   │   └── ValidateLicense.php
│   │   └── Requests/
│   │       └── LicenseValidateRequest.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── License.php
│   │   ├── Activation.php
│   │   ├── Content.php
│   │   ├── MonitoringResult.php
│   │   └── DmcaRequest.php
│   │
│   ├── Services/
│   │   ├── LicenseService.php
│   │   ├── FingerprintService.php
│   │   ├── MonitoringService.php
│   │   ├── AiMatchingService.php
│   │   └── DmcaService.php
│   │
│   └── Jobs/
│       ├── ScanContentJob.php
│       ├── MonitorSiteJob.php
│       └── SendDmcaJob.php
│
├── database/
│   └── migrations/
│       ├── create_licenses_table.php
│       ├── create_activations_table.php
│       ├── create_contents_table.php
│       └── create_monitoring_results_table.php
│
├── routes/
│   ├── api.php                      # API routes
│   └── webhooks.php                 # Payment webhooks
│
└── config/
    └── contentshield.php            # Plugin config
```

---

## 6. Free vs Pro Features

### Feature Comparison Matrix

```
┌─────────────────────────────────────────────────────────────────┐
│                     FREE VERSION (WordPress.org)                 │
├─────────────────────────────────────────────────────────────────┤
│ ✅ Invisible text watermarking (zero-width characters)          │
│ ✅ Content fingerprinting (SimHash algorithm)                   │
│ ✅ Manual URL scanning (check single URLs)                      │
│ ✅ Local fingerprint database                                   │
│ ✅ Copy-paste detection (JavaScript)                            │
│ ✅ RSS feed protection (attribution links)                      │
│ ✅ Basic reports (last 10 scans)                                │
│ ✅ Right-click protection (optional)                            │
│ ✅ Export fingerprints (CSV)                                    │
│ ✅ Email notifications (basic)                                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                 PRO VERSION (SaaS API Features)                  │
├─────────────────────────────────────────────────────────────────┤
│ ⭐ Automated web monitoring (scheduled crawling)                │
│ ⭐ Real-time plagiarism alerts                                  │
│ ⭐ AI-powered content matching (semantic similarity)            │
│ ⭐ Google Search API integration                                │
│ ⭐ Automated DMCA takedown generation                           │
│ ⭐ One-click DMCA submission to Google/Bing                     │
│ ⭐ Hosting provider notification system                         │
│ ⭐ Legal evidence package (PDF reports)                         │
│ ⭐ Historical tracking & analytics dashboard                    │
│ ⭐ White-label reports (Agency plan)                            │
│ ⭐ API access for custom integrations                           │
│ ⭐ Priority email support                                       │
└─────────────────────────────────────────────────────────────────┘
```

### Pricing Tiers

| Feature | Free | Starter ($9) | Pro ($19) | Agency ($49) |
|---------|------|--------------|-----------|--------------|
| Watermarking | ✅ | ✅ | ✅ | ✅ |
| Manual Scanning | ✅ | ✅ | ✅ | ✅ |
| Fingerprinting | ✅ | ✅ | ✅ | ✅ |
| Protected Posts | Unlimited | 50 | 500 | Unlimited |
| Monitoring | ❌ | Weekly | Daily | Hourly |
| AI Matching | ❌ | ✅ | ✅ | ✅ |
| DMCA Templates | ❌ | ✅ | ✅ | ✅ |
| Auto DMCA | ❌ | ❌ | ✅ | ✅ |
| Sites | 1 | 1 | 5 | 50 |
| White Label | ❌ | ❌ | ❌ | ✅ |
| API Access | ❌ | ❌ | ✅ | ✅ |

---

## 7. License Key System

### Key Format

```
CSAI-XXXX-XXXX-XXXX-XXXX
│    │    │    │    │
│    └────┴────┴────┴── Random alphanumeric segments
└── Prefix (ContentShield AI)

Example: CSAI-7K2M-9PQR-4X8N-2HJL
```

### License Generation (Laravel)

```php
<?php
// app/Services/LicenseService.php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Str;

class LicenseService
{
    public function generate(User $user, string $plan): License
    {
        // Generate secure key
        $segments = [];
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(Str::random(4));
        }
        $key = 'CSAI-' . implode('-', $segments);

        // Create license record
        return License::create([
            'key_hash' => hash('sha256', $key),
            'key_prefix' => substr($key, 0, 9), // CSAI-XXXX
            'user_id' => $user->id,
            'plan' => $plan,
            'status' => 'active',
            'activations_limit' => $this->getLimit($plan),
            'activations_count' => 0,
            'expires_at' => now()->addYear(),
        ]);
    }

    private function getLimit(string $plan): int
    {
        return match($plan) {
            'starter' => 1,
            'pro' => 5,
            'agency' => 50,
            default => 1,
        };
    }
}
```

### License Validation API

```php
<?php
// POST /api/v1/license/validate

{
    "license_key": "CSAI-7K2M-9PQR-4X8N-2HJL",
    "site_url": "https://example.com",
    "site_hash": "a1b2c3d4...",
    "plugin_version": "1.0.0"
}

// Response (Success)
{
    "valid": true,
    "license": {
        "plan": "pro",
        "status": "active",
        "expires_at": "2025-12-12T00:00:00Z",
        "features": {
            "monitoring_frequency": "daily",
            "monitored_posts": 500,
            "auto_dmca": true,
            "api_access": true
        }
    },
    "activation": {
        "site_url": "example.com",
        "activated_at": "2024-12-12T00:00:00Z"
    }
}

// Response (Error)
{
    "valid": false,
    "error": "activation_limit",
    "message": "Activation limit reached.",
    "limit": 5
}
```

### WordPress License Client

```php
<?php
// Plugin-side license validation

class ContentShield_License {
    
    public function activate($license_key) {
        $response = wp_remote_post(CONTENTSHIELD_API_URL . '/license/validate', [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode([
                'license_key' => $license_key,
                'site_url' => home_url(),
                'site_hash' => $this->generate_site_hash(),
                'plugin_version' => CONTENTSHIELD_VERSION,
            ]),
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body['valid']) {
            update_option('contentshield_license', [
                'key_masked' => $this->mask_key($license_key),
                'key_hash' => hash('sha256', $license_key),
                'plan' => $body['license']['plan'],
                'status' => 'active',
                'expires_at' => $body['license']['expires_at'],
                'features' => $body['license']['features'],
            ]);
            return ['success' => true];
        }

        return ['success' => false, 'error' => $body['message']];
    }

    private function generate_site_hash() {
        return hash('sha256', home_url() . '|' . DB_NAME);
    }

    private function mask_key($key) {
        return substr($key, 0, 9) . '-****-****-****';
    }
}
```

---

## 8. Payment Integration

### LemonSqueezy Setup

#### 1. Create Products in LemonSqueezy Dashboard

| Product | Variant ID | Price |
|---------|------------|-------|
| Starter Plan | 123456 | $9/month |
| Pro Plan | 123457 | $19/month |
| Agency Plan | 123458 | $49/month |

#### 2. Webhook Handler (Laravel)

```php
<?php
// app/Http/Controllers/Webhooks/LemonSqueezyController.php

namespace App\Http\Controllers\Webhooks;

use App\Models\User;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class LemonSqueezyController extends Controller
{
    public function handle(Request $request)
    {
        // Verify signature
        $signature = $request->header('X-Signature');
        $computed = hash_hmac('sha256', $request->getContent(), config('services.lemonsqueezy.webhook_secret'));
        
        if (!hash_equals($signature, $computed)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->input('meta.event_name');
        $data = $request->input('data');

        return match($event) {
            'order_created' => $this->handleOrderCreated($data),
            'subscription_cancelled' => $this->handleCancelled($data),
            'subscription_expired' => $this->handleExpired($data),
            default => response()->json(['received' => true]),
        };
    }

    private function handleOrderCreated(array $data)
    {
        $email = $data['attributes']['user_email'];
        $variantId = $data['attributes']['first_order_item']['variant_id'];
        
        $user = User::firstOrCreate(['email' => $email]);
        $plan = $this->getPlanFromVariant($variantId);
        
        $license = app(LicenseService::class)->generate($user, $plan);
        
        // Send license key email
        $user->notify(new LicenseKeyNotification($license));

        return response()->json(['success' => true]);
    }

    private function getPlanFromVariant(int $variantId): string
    {
        return match($variantId) {
            123456 => 'starter',
            123457 => 'pro',
            123458 => 'agency',
            default => 'starter',
        };
    }
}
```

---

## 9. WordPress Guidelines Compliance

### Critical Rules Summary

| Guideline | Rule | ContentShield Compliance |
|-----------|------|-------------------------|
| #1 | GPL Compatible License | ✅ GPL v2 or later |
| #4 | Human Readable Code | ✅ No obfuscation |
| #5 | No Trialware | ✅ Free version fully functional |
| #6 | SaaS Allowed | ✅ Pro features via API service |
| #7 | No Tracking Without Consent | ✅ Opt-in for API calls |
| #8 | No External Executable Code | ✅ All code local |
| #10 | No Forced Credits | ✅ Optional attribution |
| #11 | No Dashboard Hijacking | ✅ Dismissible notices |

### Compliance Checklist

```
✅ DO:
├── Provide real value in free version
├── Use SaaS for Pro features (service delivers value)
├── Get explicit consent before API calls
├── Make all notices dismissible
├── Include GPL license
└── Keep code human-readable

❌ DON'T:
├── Lock local features behind license
├── Use license-only validation service
├── Track users without consent
├── Download/execute external code
├── Force credits on frontend
└── Obfuscate any code
```

---

## 10. Security Best Practices

### WordPress Plugin Security

```php
<?php

// 1. ALWAYS prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// 2. ALWAYS verify nonces
if (!wp_verify_nonce($_POST['_wpnonce'], 'contentshield_action')) {
    wp_die('Security check failed');
}

// 3. ALWAYS check capabilities
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

// 4. ALWAYS sanitize input
$license_key = sanitize_text_field($_POST['license_key']);
$url = esc_url_raw($_POST['url']);
$content = sanitize_textarea_field($_POST['content']);

// 5. ALWAYS escape output
echo esc_html($data['title']);
echo esc_url($data['url']);
echo esc_attr($data['value']);

// 6. ALWAYS use prepared statements
global $wpdb;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}contentshield_scans WHERE post_id = %d",
        $post_id
    )
);
```

### API Security (Laravel)

```php
<?php

// Rate limiting
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/license/validate', [LicenseController::class, 'validate']);
});

// Request validation
public function validate(Request $request)
{
    $validated = $request->validate([
        'license_key' => 'required|string|regex:/^CSAI-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i',
        'site_url' => 'required|url',
        'site_hash' => 'required|string|size:64',
    ]);
}

// Hash comparison (timing-safe)
if (!hash_equals($expected, $provided)) {
    abort(401);
}
```

---

## 11. Database Schema

### WordPress Tables

```sql
-- Fingerprints table
CREATE TABLE {prefix}contentshield_fingerprints (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    fingerprint VARCHAR(128) NOT NULL,
    content_hash VARCHAR(64) NOT NULL,
    word_count INT(11) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY post_id (post_id),
    KEY fingerprint (fingerprint(32))
);

-- Scans table
CREATE TABLE {prefix}contentshield_scans (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT(20) UNSIGNED NULL,
    scanned_url VARCHAR(2048) NOT NULL,
    similarity_score DECIMAL(5,2) NULL,
    matched_content TEXT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    scan_type VARCHAR(20) DEFAULT 'manual',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    KEY status (status),
    KEY created_at (created_at)
);

-- Alerts table  
CREATE TABLE {prefix}contentshield_alerts (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    scan_id BIGINT(20) UNSIGNED NULL,
    alert_type VARCHAR(50) NOT NULL,
    severity VARCHAR(20) DEFAULT 'medium',
    source_url VARCHAR(2048) NULL,
    details LONGTEXT NULL,
    is_read TINYINT(1) DEFAULT 0,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    KEY is_read (is_read),
    KEY created_at (created_at)
);
```

### Laravel Migrations

```php
<?php
// Licenses table
Schema::create('licenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('key_hash', 64)->unique();
    $table->string('key_prefix', 9);
    $table->string('plan', 20);
    $table->string('status', 20)->default('active');
    $table->integer('activations_limit')->default(1);
    $table->integer('activations_count')->default(0);
    $table->timestamp('expires_at')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['status', 'expires_at']);
});

// Activations table
Schema::create('activations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('license_id')->constrained()->onDelete('cascade');
    $table->string('site_url');
    $table->string('site_hash', 64);
    $table->timestamp('activated_at');
    $table->timestamp('last_check')->nullable();
    $table->timestamps();
    
    $table->unique(['license_id', 'site_url']);
});
```

---

## 12. API Endpoints

### SaaS API Structure

```
Base URL: https://api.contentshield.ai/v1

Authentication: Bearer Token (License Key Hash)
Headers:
  Authorization: Bearer {key_hash}
  X-Site-URL: {site_url}
  Content-Type: application/json
```

### Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/license/validate` | Validate & activate license | No |
| POST | `/license/deactivate` | Deactivate from site | Yes |
| GET | `/license/status` | Check current status | Yes |
| POST | `/content/register` | Register content for monitoring | Yes |
| GET | `/content/list` | List registered content | Yes |
| DELETE | `/content/{id}` | Remove content from monitoring | Yes |
| GET | `/monitoring/status` | Get monitoring status | Yes |
| GET | `/monitoring/results` | Get plagiarism results | Yes |
| POST | `/monitoring/scan` | Trigger manual scan | Yes |
| POST | `/dmca/generate` | Generate DMCA notice | Yes |
| POST | `/dmca/send` | Send automated takedown | Yes |
| GET | `/dmca/templates` | Get DMCA templates | Yes |
| GET | `/dmca/history` | Get takedown history | Yes |
| GET | `/reports/dashboard` | Dashboard statistics | Yes |
| GET | `/reports/export` | Export reports (PDF/CSV) | Yes |

---

## 13. Implementation Roadmap

### Phase 1: MVP Free Plugin (Weeks 1-4)

```
Week 1-2: Foundation
├── Plugin boilerplate & structure
├── Admin settings page
├── Database tables creation
├── WordPress coding standards setup
└── Basic CI/CD pipeline

Week 3-4: Core Features
├── Content fingerprinting (SimHash)
├── Invisible watermarking (zero-width)
├── Manual URL scanner
├── Basic reporting dashboard
└── Email notifications
```

### Phase 2: SaaS Backend (Weeks 5-8)

```
Week 5-6: Infrastructure
├── Laravel project setup
├── PostgreSQL + Redis configuration
├── Database migrations
├── License management system
└── API authentication (Sanctum)

Week 7-8: Payment & Integration
├── LemonSqueezy integration
├── Webhook handlers
├── License validation API
├── Customer portal
└── Email templates (license delivery)
```

### Phase 3: Pro Features (Weeks 9-12)

```
Week 9-10: Monitoring System
├── Content registration API
├── Web crawler setup
├── Queue workers for scanning
├── AI matching integration (OpenAI)
└── Alert system

Week 11-12: DMCA & Reports
├── DMCA template system
├── Automated submission to Google
├── PDF report generation
├── Analytics dashboard
└── API documentation
```

### Phase 4: Launch (Weeks 13-14)

```
Week 13: Testing & Submission
├── Security audit
├── Performance testing
├── WordPress.org submission
├── Beta testing program
└── Bug fixes

Week 14: Go Live
├── Marketing website launch
├── Documentation site
├── Support system setup
├── Launch announcement
└── Monitoring & iteration
```

---

## 14. Submission Checklist

### WordPress.org Submission Requirements

```
□ LICENSE
  □ GPL v2 or later license file included
  □ All third-party code GPL-compatible
  □ License header in main plugin file

□ CODE QUALITY
  □ Passes WordPress Coding Standards (PHPCS)
  □ No PHP errors/warnings (PHP 7.4-8.3)
  □ No deprecated WordPress functions
  □ All output properly escaped
  □ All input properly sanitized
  □ All strings internationalized

□ SECURITY
  □ Nonce verification on all forms
  □ Capability checks on admin actions
  □ Prepared statements for DB queries
  □ No direct file access (ABSPATH check)
  □ Secure API communication (HTTPS)

□ PRIVACY
  □ External API calls documented in readme
  □ Explicit opt-in for any tracking
  □ Privacy policy section in readme
  □ GDPR compliance considered

□ FUNCTIONALITY
  □ Free version provides real value
  □ No locked/teaser features in free
  □ Works fully without Pro license
  □ Clean activation process
  □ Clean deactivation process
  □ Complete uninstall removes all data

□ readme.txt
  □ Valid format (use validator)
  □ Accurate description (no spam)
  □ Installation instructions
  □ FAQ section
  □ Screenshots (with descriptions)
  □ Changelog
  □ Maximum 5 tags
  □ Proper contributor usernames

□ ASSETS
  □ Plugin icon (128x128, 256x256 PNG)
  □ Banner image (772x250, 1544x500 PNG)
  □ Screenshots (named screenshot-1.png, etc.)

□ FINAL CHECKS
  □ Test on fresh WordPress install
  □ Test with popular themes
  □ Test with popular plugins
  □ Check all admin pages work
  □ Verify settings save correctly
  □ Test activation/deactivation cycle
```

---

## Quick Start Commands

### WordPress Plugin Development

```bash
# Install PHP dependencies (dev)
composer install

# Check coding standards
composer run lint

# Fix coding standards
composer run lint:fix

# Build assets
npm install
npm run build

# Create distribution zip
npm run package
```

### Laravel SaaS Development

```bash
# Create new Laravel project
composer create-project laravel/laravel contentshield-api

# Install dependencies
composer require laravel/sanctum
composer require lemonsqueezy/laravel

# Run migrations
php artisan migrate

# Start queue worker
php artisan queue:work

# Run tests
php artisan test
```

---

## Support & Resources

- **WordPress Plugin Handbook**: https://developer.wordpress.org/plugins/
- **WordPress Coding Standards**: https://developer.wordpress.org/coding-standards/
- **Laravel Documentation**: https://laravel.com/docs
- **LemonSqueezy Docs**: https://docs.lemonsqueezy.com

---

*Document Version: 1.0.0*
*Last Updated: December 2024*
*Author: ContentShield AI Development Team*
