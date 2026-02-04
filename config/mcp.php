<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Domain
    |--------------------------------------------------------------------------
    |
    | The domain name for MCP-related hostnames and URLs.
    |
    */
    'domain' => env('MCP_DOMAIN', 'mcp.host.uk.com'),

    /*
    |--------------------------------------------------------------------------
    | Database Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the database query tools.
    |
    */
    'database' => [
        // Dedicated read-only connection name from config/database.php
        'connection' => env('MCP_DATABASE_CONNECTION'),

        // Whether to enforce whitelist-based query validation
        'use_whitelist' => env('MCP_USE_WHITELIST', true),

        // Default query tier (free, starter, professional, enterprise, unlimited)
        'default_tier' => env('MCP_DEFAULT_TIER', 'free'),

        // Whitelist regex patterns for allowed query structures
        'whitelist_patterns' => [
            // Example: '/^SELECT\s+.*\s+FROM\s+products/i'
        ],

        // Tables that cannot be accessed via MCP tools
        'blocked_tables' => [
            'users',
            'api_keys',
            'failed_jobs',
            'migrations',
            'password_reset_tokens',
            'personal_access_tokens',
            'sessions',
        ],

        // Custom limits per tier
        'tier_limits' => [
            // 'free' => ['max_rows' => 100, 'timeout_seconds' => 5],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the fault tolerance system.
    |
    */
    'circuit_breaker' => [
        'default_threshold' => env('MCP_CB_DEFAULT_THRESHOLD', 5),
        'default_reset_timeout' => env('MCP_CB_DEFAULT_RESET_TIMEOUT', 60),
        'default_failure_window' => env('MCP_CB_DEFAULT_FAILURE_WINDOW', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Log Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the tamper-evident audit log.
    |
    */
    'audit' => [
        'log_channel' => env('MCP_AUDIT_LOG_CHANNEL', 'mcp-queries'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for tool usage and performance tracking.
    |
    */
    'analytics' => [
        'enabled' => env('MCP_ANALYTICS_ENABLED', true),
        'retention_days' => env('MCP_ANALYTICS_RETENTION_DAYS', 90),
        'batch_size' => env('MCP_ANALYTICS_BATCH_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention Configuration
    |--------------------------------------------------------------------------
    |
    | How long to keep various types of MCP data.
    |
    */
    'log_retention' => [
        'days' => env('MCP_LOG_RETENTION_DAYS', 90),
        'stats_days' => env('MCP_STATS_RETENTION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for per-tool and per-workspace rate limiting.
    |
    */
    'rate_limiting' => [
        'enabled' => env('MCP_RATE_LIMITING_ENABLED', true),
        'calls_per_minute' => env('MCP_RATE_LIMIT_PER_MINUTE', 60),
        'decay_seconds' => env('MCP_RATE_LIMIT_DECAY', 60),
        'per_tool' => [
            // 'query_database' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for agent sessions.
    |
    */
    'session' => [
        'cache_ttl' => env('MCP_SESSION_TTL', 86400),
    ],
];
