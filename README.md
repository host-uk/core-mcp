# Core MCP Package

Model Context Protocol (MCP) tools and analytics for AI-powered automation and integrations.

## Installation

```bash
composer require host-uk/core-mcp
```

## Features

### MCP Tool Registry
Extensible tool system for AI integrations:

```php
use Core\Mcp\Tools\BaseTool;

class GetProductsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_products';
    }

    public function description(): string
    {
        return 'Retrieve a list of products from the workspace';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer('Maximum number of products to return'),
        ];
    }

    public function handle(Request $request): Response
    {
        $products = Product::take($request->input('limit', 10))->get();
        return Response::text(json_encode($products));
    }
}
```

### Workspace Context Security
Prevents cross-tenant data leakage:

```php
use Core\Mcp\Tools\Concerns\RequiresWorkspaceContext;

class MyTool extends BaseTool
{
    use RequiresWorkspaceContext;

    // Automatically validates workspace context
    // Throws exception if context is missing
}
```

### SQL Query Validation
Multi-layer protection for database queries:

```php
use Core\Mcp\Services\SqlQueryValidator;

$validator = new SqlQueryValidator();
$validator->validate($query); // Throws if unsafe

// Features:
// - Blocked keywords (INSERT, UPDATE, DELETE, DROP)
// - Pattern detection (stacked queries, hex encoding)
// - Whitelist matching
// - Comment stripping
```

### Tool Analytics
Track tool usage and performance:

```php
use Core\Mcp\Services\ToolAnalyticsService;

$analytics = app(ToolAnalyticsService::class);

$stats = $analytics->getToolStats('get_products');
// Returns: calls, avg_duration, error_rate, etc.
```

**Admin dashboard:** `/admin/mcp/analytics`

### Tool Dependencies
Declare tool dependencies and validate at runtime:

```php
use Core\Mcp\Dependencies\{HasDependencies, ToolDependency};

class AdvancedTool extends BaseTool implements HasDependencies
{
    public function dependencies(): array
    {
        return [
            new ToolDependency('get_products', DependencyType::REQUIRED),
            new ToolDependency('send_email', DependencyType::OPTIONAL),
        ];
    }
}
```

### MCP Playground
Interactive UI for testing tools:

**Route:** `/admin/mcp/playground`

**Features:**
- Tool browser with search
- Dynamic form generation
- JSON response viewer
- Conversation history
- Example pre-fill

### Query EXPLAIN Analysis
Performance insights for database queries:

```json
{
  "query": "SELECT * FROM users WHERE email = ?",
  "explain": true
}
```

**Returns:**
- Raw EXPLAIN output
- Performance warnings
- Index usage analysis
- Optimization recommendations

### Usage Quotas
Workspace-level rate limiting:

```php
use Core\Mcp\Services\McpQuotaService;

$quota = app(McpQuotaService::class);

// Check if workspace can execute tool
if (!$quota->canExecute($workspace, 'expensive_tool')) {
    throw new QuotaExceededException();
}

// Record execution
$quota->recordExecution($workspace, 'expensive_tool');
```

## Configuration

```php
// config/mcp.php

return [
    'database' => [
        'connection' => 'readonly', // Dedicated read-only connection
        'use_whitelist' => true,
        'blocked_tables' => ['users', 'api_keys'],
    ],
    'analytics' => [
        'enabled' => true,
        'retention_days' => 90,
    ],
    'quota' => [
        'enabled' => true,
        'default_limit' => 1000, // Per workspace per day
    ],
];
```

## Security

### Query Security (Defense in Depth)
1. **Read-only database user** (infrastructure)
2. **Blocked keywords** (application)
3. **Pattern validation** (application)
4. **Whitelist matching** (application)
5. **Table access controls** (application)

### Workspace Isolation
- Context MUST come from authentication
- Cross-tenant access prevented by design
- Tools throw exceptions without context

See [changelog/2026/jan/security.md](changelog/2026/jan/security.md) for security updates.

## Requirements

- PHP 8.2+
- Laravel 11+ or 12+

## Changelog

See [changelog/2026/jan/features.md](changelog/2026/jan/features.md) for recent changes.

## License

EUPL-1.2 - See [LICENSE](../../LICENSE) for details.
