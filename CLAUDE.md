# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package Overview

This is `host-uk/core-mcp`, a standalone Laravel package providing Model Context Protocol (MCP) tools for AI-powered automation. It implements the JSON-RPC MCP standard for AI agent integrations with security-focused database access.

**Namespaces:**
- `Core\Mcp\` - Main package code (tools, services, middleware)
- `Core\Website\Mcp\` - Web UI components (Livewire modals, controllers)

## Commands

```bash
./vendor/bin/pest                      # Run all tests
./vendor/bin/pest --filter=SqlQuery    # Run specific tests
./vendor/bin/pint --dirty              # Format changed files
php artisan mcp:agent-server           # Run the MCP agent server (stdio)
php artisan mcp:prune-metrics          # Prune old analytics data
php artisan mcp:verify-audit-log       # Verify audit log integrity
```

## Architecture

### MCP Tool System

Tools extend `Laravel\Mcp\Server\Tool` and implement:
- `$description` property for tool purpose
- `schema(JsonSchema $schema): array` for input parameters
- `handle(Request $request): Response` for execution logic

```php
class QueryDatabase extends Tool
{
    protected string $description = 'Execute a read-only SQL SELECT query';

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string('SQL SELECT query'),
            'explain' => $schema->boolean('Run EXPLAIN instead')->default(false),
        ];
    }

    public function handle(Request $request): Response
    {
        // Validate and execute
        return Response::text(json_encode($results));
    }
}
```

### Boot Class (Event-Driven Registration)

`src/Mcp/Boot.php` registers via Core framework events:
- `AdminPanelBooting` - Admin routes, views, Livewire components
- `ConsoleBooting` - Artisan commands
- `McpToolsRegistering` - MCP tool handlers

### Security Layers

**SQL Query Validation** (`SqlQueryValidator`):
1. Blocked keywords (INSERT, UPDATE, DELETE, DROP, etc.)
2. Dangerous pattern detection (UNION injection, hex encoding, stacked queries)
3. Whitelist regex matching for allowed query structures
4. Comment stripping before validation

**Workspace Context Security**:
- `RequiresWorkspaceContext` trait enforces tenant isolation
- Tools throw `MissingWorkspaceContextException` without valid context
- `ValidateWorkspaceContext` middleware injects context from auth

### Key Services

| Service | Purpose |
|---------|---------|
| `ToolRegistry` | Discovers tools from YAML config, manages schemas |
| `SqlQueryValidator` | Multi-layer SQL injection protection |
| `ToolAnalyticsService` | Usage tracking and metrics |
| `McpQuotaService` | Workspace-level rate limiting |
| `AuditLogService` | Tamper-evident logging |
| `ToolDependencyService` | Validates tool dependencies at runtime |

### Tool Configuration

Tools are defined in `resources/mcp/servers/*.yaml`:
```yaml
id: workspace-tools
name: Workspace Tools
tools:
  - name: query_database
    description: Execute read-only SQL query
    inputSchema:
      type: object
      properties:
        query: { type: string }
```

## Testing

Tests use **Pest** and are located in:
- `tests/` - Integration tests (Laravel TestCase)
- `src/Mcp/Tests/` - Package unit tests

```php
describe('SqlQueryValidator', function () {
    it('blocks INSERT statements', function () {
        $validator = new SqlQueryValidator();

        expect(fn () => $validator->validate('INSERT INTO users VALUES (1)'))
            ->toThrow(ForbiddenQueryException::class);
    });
});
```

## Conventions

- UK English (colour, organisation, centre)
- `declare(strict_types=1);` in every PHP file
- PSR-12 via Laravel Pint
- Pest for testing (not PHPUnit syntax)
- All parameters and return types must have type hints

## License

EUPL-1.2 (copyleft applies to `Core\` namespace)