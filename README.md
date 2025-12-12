# ContentShield AI

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/License-Proprietary-blue?style=for-the-badge" alt="License">
</p>

A Laravel SaaS backend for content protection, plagiarism detection, and DMCA automation. This API powers the ContentShield AI WordPress plugin.

## Features

- **License Management** - Generate, validate, and manage software licenses with activation limits
- **Content Fingerprinting** - SimHash algorithm for unique content identification
- **Zero-Width Watermarking** - Invisible watermarks for content tracking
- **Plagiarism Detection** - AI-powered content matching using OpenAI/Claude
- **DMCA Automation** - Generate and send takedown notices to Google, hosting providers, and Cloudflare
- **Scheduled Monitoring** - Automated scanning at configurable intervals
- **Premium Dashboard** - Modern dark-themed admin interface with interactive animations

## Tech Stack

- **Framework**: Laravel 12.x
- **Database**: SQLite (dev) / PostgreSQL (prod)
- **Queue**: Redis + Laravel Queue
- **Authentication**: Laravel Sanctum
- **Payment**: LemonSqueezy integration

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ (for frontend assets)
- Redis (for queues)

## Installation

```bash
# Clone the repository
git clone https://github.com/mejba13/contentshield-api.git
cd contentshield-api

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Generate a test license
php artisan licenses:generate test@example.com --plan=agency
```

## Configuration

Update your `.env` file with the required settings:

```env
# LemonSqueezy
LEMONSQUEEZY_API_KEY=your_api_key
LEMONSQUEEZY_WEBHOOK_SECRET=your_webhook_secret

# AI Matching (optional)
OPENAI_API_KEY=your_openai_key
ANTHROPIC_API_KEY=your_anthropic_key

# Monitoring
GOOGLE_API_KEY=your_google_api_key
GOOGLE_CSE_ID=your_custom_search_engine_id
```

## API Endpoints

### License Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/license/validate` | Validate license key |
| POST | `/api/v1/license/deactivate` | Deactivate license |
| GET | `/api/v1/license/status` | Get license status |

### Content Protection
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/content/register` | Register content for protection |
| GET | `/api/v1/content/list` | List protected content |
| POST | `/api/v1/content/bulk-register` | Bulk register content |

### Monitoring
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/monitoring/scan` | Trigger manual scan |
| GET | `/api/v1/monitoring/results` | Get scan results |
| GET | `/api/v1/monitoring/status` | Get monitoring status |

### DMCA
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/dmca/generate` | Generate DMCA notice |
| POST | `/api/v1/dmca/send` | Send DMCA notice |
| GET | `/api/v1/dmca/templates` | Get DMCA templates |

### Reports
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/reports/dashboard` | Dashboard statistics |
| GET | `/api/v1/reports/trends` | Trend data |

## Admin Dashboard

Access the admin dashboard at `/dashboard` with the following pages:

| Page | Route | Description |
|------|-------|-------------|
| Dashboard | `/dashboard` | Overview statistics and recent activity |
| Protected Content | `/content` | Manage protected content |
| Plagiarism Alerts | `/alerts` | View and manage detected matches |
| Scan Results | `/scans` | Monitor scan progress |
| DMCA Requests | `/dmca` | Track takedown requests |
| Analytics | `/analytics` | Detailed metrics and charts |
| Schedule | `/schedule` | Configure automated scans |
| Settings | `/settings` | Configure preferences |
| API Keys | `/api-keys` | Manage API authentication |

## Pricing Plans

| Feature | Starter ($9/mo) | Pro ($19/mo) | Agency ($49/mo) |
|---------|-----------------|--------------|-----------------|
| Protected Posts | 50 | 200 | Unlimited |
| Scan Frequency | Weekly | Daily | Hourly |
| AI Matching | - | Basic | Advanced |
| DMCA Automation | - | Yes | Yes |
| Priority Support | - | - | Yes |
| Activations | 1 site | 3 sites | 10 sites |

## Artisan Commands

```bash
# License Management
php artisan licenses:generate {email} --plan={plan}
php artisan licenses:list --status=active
php artisan licenses:revoke {id}

# Check Expiring Licenses
php artisan licenses:check-expiry
```

## Queue Workers

Start the queue worker for background jobs:

```bash
php artisan queue:work --queue=scans,dmca,default
```

## Testing

```bash
# Run API tests
php tests/api_test.php

# Run PHPUnit tests
php artisan test
```

## Project Structure

```
contentshield-api/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/
│   │   ├── Controllers/Api/  # API controllers
│   │   ├── Middleware/       # License validation
│   │   └── Requests/         # Form requests
│   ├── Jobs/                 # Queue jobs
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Email notifications
│   └── Services/             # Business logic
├── config/
│   └── contentshield.php     # Plugin configuration
├── database/migrations/      # Database schema
├── resources/views/
│   ├── layouts/              # Blade layouts
│   └── pages/                # Dashboard pages
├── routes/
│   ├── api.php               # API routes
│   ├── web.php               # Web routes
│   └── webhooks.php          # Webhook routes
└── tests/
    └── api_test.php          # API test script
```

## License

Proprietary - All rights reserved.

## Author

**Engr Mejba Ahmed**

- GitHub: [@mejba13](https://github.com/mejba13)
