# Core-MCP - January 2026

## Features Implemented

### Workspace Context Security

Prevents cross-tenant data leakage by requiring authenticated workspace context.

**Files:**
- `Exceptions/MissingWorkspaceContextException.php`
- `Context/WorkspaceContext.php` - Value object
- `Tools/Concerns/RequiresWorkspaceContext.php` - Tool trait
- `Middleware/ValidateWorkspaceContext.php`

**Security Guarantees:**
- Workspace context MUST come from authentication
- Cross-tenant access prevented by design
- Tools throw exceptions when called without context

---

### Query Security

Defence in depth for SQL injection prevention.

**Files:**
- `Exceptions/ForbiddenQueryException.php`
- `Services/SqlQueryValidator.php` - Multi-layer validation

**Features:**
- Blocked keywords: INSERT, UPDATE, DELETE, DROP, UNION
- Pattern detection: stacked queries, hex encoding, SLEEP/BENCHMARK
- Comment stripping to prevent obfuscation
- Query whitelist matching
- Read-only database connection support

**Config:** `mcp.database.connection`, `mcp.database.use_whitelist`, `mcp.database.blocked_tables`

---

### MCP Playground UI

Interactive interface for testing MCP tools.

**Files:**
- `Services/ToolRegistry.php` - Tool discovery and schemas
- `View/Modal/Admin/McpPlayground.php` - Livewire component
- `View/Blade/admin/mcp-playground.blade.php`

**Features:**
- Tool browser with search and category filtering
- Dynamic form builder from JSON schemas
- JSON response viewer with syntax highlighting
- Conversation history (last 50 executions)
- Example input pre-fill
- API key validation

**Route:** `GET /admin/mcp/playground`

---

### Tool Usage Analytics

Usage tracking and dashboard for MCP tools.

**Files:**
- `Migrations/2026_01_26_*` - mcp_tool_metrics, mcp_tool_combinations
- `Models/ToolMetric.php`
- `DTO/ToolStats.php`
- `Services/ToolAnalyticsService.php`
- `Events/ToolExecuted.php`
- `Listeners/RecordToolExecution.php`
- `View/Modal/Admin/ToolAnalyticsDashboard.php`
- `View/Modal/Admin/ToolAnalyticsDetail.php`
- `Console/Commands/PruneMetricsCommand.php`

**Features:**
- Per-tool call counts with daily granularity
- Average, min, max response times
- Error rates with threshold highlighting
- Tool combination tracking
- Admin dashboard with sortable tables
- Date range filtering

**Routes:**
- `GET /admin/mcp/analytics` - Dashboard
- `GET /admin/mcp/analytics/tool/{name}` - Tool detail

**Config:** `mcp.analytics.enabled`, `mcp.analytics.retention_days`

---

### EXPLAIN Query Analysis

Query optimization insights with automated performance analysis.

**Files:**
- `Tools/QueryDatabase.php` - Added `explain` parameter
- Enhanced with human-readable performance interpretation

**Features:**
- Optional EXPLAIN execution before query runs
- Detects full table scans
- Identifies missing indexes
- Warns about filesort and temporary tables
- Shows row count estimates
- Includes MySQL warnings when available

**Usage:**
```json
{
  "query": "SELECT * FROM users WHERE email = 'test@example.com'",
  "explain": true
}
```

**Response includes:**
- Raw EXPLAIN output
- Performance warnings (full scans, high row counts)
- Index usage analysis
- Optimization recommendations
