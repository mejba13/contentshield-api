<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | The current version of the ContentShield API.
    |
    */

    'api_version' => env('CONTENTSHIELD_API_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | License Key Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used for generated license keys.
    |
    */

    'license_prefix' => env('CONTENTSHIELD_LICENSE_PREFIX', 'CSAI'),

    /*
    |--------------------------------------------------------------------------
    | Plan Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each subscription plan.
    |
    */

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 9,
            'monitoring_frequency' => 'weekly',
            'monitored_posts' => 50,
            'auto_dmca' => false,
            'api_access' => false,
            'sites_limit' => 1,
            'ai_matching' => true,
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 19,
            'monitoring_frequency' => 'daily',
            'monitored_posts' => 500,
            'auto_dmca' => true,
            'api_access' => true,
            'sites_limit' => 5,
            'ai_matching' => true,
        ],
        'agency' => [
            'name' => 'Agency',
            'price' => 49,
            'monitoring_frequency' => 'hourly',
            'monitored_posts' => -1, // unlimited
            'auto_dmca' => true,
            'api_access' => true,
            'sites_limit' => 50,
            'ai_matching' => true,
            'white_label' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | LemonSqueezy Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for LemonSqueezy payment integration.
    |
    */

    'lemonsqueezy' => [
        'store_id' => env('LEMONSQUEEZY_STORE_ID'),
        'api_key' => env('LEMONSQUEEZY_API_KEY'),
        'webhook_secret' => env('LEMONSQUEEZY_WEBHOOK_SECRET'),

        // Map variant IDs to plans
        'variants' => [
            env('LEMONSQUEEZY_STARTER_VARIANT_ID', 123456) => 'starter',
            env('LEMONSQUEEZY_PRO_VARIANT_ID', 123457) => 'pro',
            env('LEMONSQUEEZY_AGENCY_VARIANT_ID', 123458) => 'agency',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI-powered content matching.
    |
    */

    'ai' => [
        'provider' => env('CONTENTSHIELD_AI_PROVIDER', 'openai'), // openai, anthropic
        'model' => env('CONTENTSHIELD_AI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => env('CONTENTSHIELD_AI_MAX_TOKENS', 1024),
        'temperature' => env('CONTENTSHIELD_AI_TEMPERATURE', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for plagiarism monitoring.
    |
    */

    'monitoring' => [
        // Minimum similarity score to flag as a match
        'min_similarity' => env('CONTENTSHIELD_MIN_SIMILARITY', 50),

        // Maximum URLs to check per scan
        'max_urls_per_scan' => env('CONTENTSHIELD_MAX_URLS_PER_SCAN', 20),

        // Delay between URL fetches (milliseconds)
        'fetch_delay' => env('CONTENTSHIELD_FETCH_DELAY', 500),

        // User agent for crawling
        'user_agent' => env('CONTENTSHIELD_USER_AGENT', 'ContentShield/1.0 (+https://contentshield.ai)'),

        // Timeout for URL fetches (seconds)
        'fetch_timeout' => env('CONTENTSHIELD_FETCH_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | DMCA Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for DMCA takedown notices.
    |
    */

    'dmca' => [
        // Default sender information
        'default_sender_name' => env('CONTENTSHIELD_DMCA_SENDER_NAME', 'ContentShield AI'),
        'default_sender_email' => env('CONTENTSHIELD_DMCA_SENDER_EMAIL'),

        // Reference number prefix
        'reference_prefix' => env('CONTENTSHIELD_DMCA_REFERENCE_PREFIX', 'DMCA'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for background job queues.
    |
    */

    'queues' => [
        'monitoring' => env('CONTENTSHIELD_QUEUE_MONITORING', 'monitoring'),
        'dmca' => env('CONTENTSHIELD_QUEUE_DMCA', 'dmca'),
        'default' => env('CONTENTSHIELD_QUEUE_DEFAULT', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching.
    |
    */

    'cache' => [
        // Cache TTL for license validation (seconds)
        'license_ttl' => env('CONTENTSHIELD_CACHE_LICENSE_TTL', 300),

        // Cache TTL for fingerprint results (seconds)
        'fingerprint_ttl' => env('CONTENTSHIELD_CACHE_FINGERPRINT_TTL', 3600),
    ],

];
