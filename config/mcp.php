<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Database Security Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the QueryDatabase tool and database execution service.
    |
    */

    'database' => [
        'connection' => env('MCP_DATABASE_CONNECTION'),
        'use_whitelist' => env('MCP_USE_WHITELIST', true),
        'whitelist_patterns' => [],
        'blocked_tables' => [
            'users',
            'api_keys',
            'personal_access_tokens',
            'sessions',
            'failed_jobs',
            'migrations',
            'mcp_api_requests',
            'mcp_audit_logs',
        ],
        'default_tier' => env('MCP_DEFAULT_TIER', 'free'),
        'tier_limits' => [
            'free' => [
                'max_rows' => 100,
                'timeout_seconds' => 5,
            ],
            'starter' => [
                'max_rows' => 500,
                'timeout_seconds' => 10,
            ],
            'professional' => [
                'max_rows' => 1000,
                'timeout_seconds' => 30,
            ],
            'enterprise' => [
                'max_rows' => 5000,
                'timeout_seconds' => 60,
            ],
            'unlimited' => [
                'max_rows' => 10000,
                'timeout_seconds' => 120,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker Settings
    |--------------------------------------------------------------------------
    |
    | Fault tolerance settings when dependent services are unavailable.
    |
    */

    'circuit_breaker' => [
        'default_threshold' => env('MCP_CIRCUIT_BREAKER_THRESHOLD', 5),
        'default_reset_timeout' => env('MCP_CIRCUIT_BREAKER_RESET_TIMEOUT', 60),
        'default_failure_window' => env('MCP_CIRCUIT_BREAKER_FAILURE_WINDOW', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Settings for MCP query auditing and security event logging.
    |
    */

    'audit' => [
        'log_channel' => env('MCP_AUDIT_LOG_CHANNEL', 'mcp-queries'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Settings for tool execution tracking and performance metrics.
    |
    */

    'analytics' => [
        'enabled' => env('MCP_ANALYTICS_ENABLED', true),
        'batch_size' => env('MCP_ANALYTICS_BATCH_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Throttling settings for MCP tool calls.
    |
    */

    'rate_limiting' => [
        'enabled' => env('MCP_RATE_LIMITING_ENABLED', true),
        'decay_seconds' => env('MCP_RATE_LIMITING_DECAY_SECONDS', 60),
        'calls_per_minute' => env('MCP_RATE_LIMITING_CALLS_PER_MINUTE', 60),
        'per_tool' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Session Settings
    |--------------------------------------------------------------------------
    |
    | Persistence settings for AI agent sessions.
    |
    */

    'session' => [
        'cache_ttl' => env('MCP_SESSION_CACHE_TTL', 86400),
    ],
];
