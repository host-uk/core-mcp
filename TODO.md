# Core-MCP TODO

## Testing & Quality Assurance

### High Priority

- [ ] **Test Coverage: SQL Query Validator** - Test injection prevention
  - [ ] Test all forbidden SQL keywords (DROP, INSERT, UPDATE, DELETE, etc.)
  - [ ] Test SQL injection attempts (UNION, boolean blinds, etc.)
  - [ ] Test parameterized query validation
  - [ ] Test subquery restrictions
  - [ ] Test multi-statement detection
  - **Estimated effort:** 4-5 hours

- [ ] **Test Coverage: Workspace Context** - Test isolation and validation
  - [ ] Test WorkspaceContext resolution from headers
  - [ ] Test automatic workspace scoping in queries
  - [ ] Test MissingWorkspaceContextException
  - [ ] Test workspace boundary enforcement
  - [ ] Test cross-workspace query prevention
  - **Estimated effort:** 3-4 hours

- [ ] **Test Coverage: Tool Analytics** - Test metrics tracking
  - [ ] Test ToolAnalyticsService recording
  - [ ] Test ToolStats DTO calculations
  - [ ] Test performance percentiles (P95, P99)
  - [ ] Test error rate calculations
  - [ ] Test daily trend aggregation
  - **Estimated effort:** 3-4 hours

- [ ] **Test Coverage: Quota System** - Test limits and enforcement
  - [ ] Test McpQuotaService tier limits
  - [ ] Test quota exceeded detection
  - [ ] Test quota reset timing
  - [ ] Test workspace-scoped quotas
  - [ ] Test custom quota overrides
  - **Estimated effort:** 3-4 hours

### Medium Priority

- [ ] **Test Coverage: Tool Dependencies** - Test dependency validation
  - [ ] Test ToolDependencyService resolution
  - [ ] Test MissingDependencyException
  - [ ] Test circular dependency detection
  - [ ] Test version compatibility checking
  - **Estimated effort:** 2-3 hours

- [ ] **Test Coverage: Query Database Tool** - Test complete workflow
  - [ ] Test SELECT query execution
  - [ ] Test EXPLAIN plan analysis
  - [ ] Test connection validation
  - [ ] Test result formatting
  - [ ] Test error handling
  - **Estimated effort:** 3-4 hours

### Low Priority

- [ ] **Test Coverage: Tool Registry** - Test tool registration
  - [ ] Test AgentToolRegistry with multiple tools
  - [ ] Test tool discovery
  - [ ] Test tool metadata
  - **Estimated effort:** 2-3 hours

## Security (Critical)

### High Priority - Security Fixes Needed

- [x] **COMPLETED: Database Connection Fallback** - Throw exception instead of fallback
  - [x] Fixed to throw ForbiddenConnectionException
  - [x] No silent fallback to default connection
  - [x] Prevents accidental production data exposure
  - **Completed:** January 2026

- [x] **COMPLETED: SQL Validator Regex Strengthening** - Stricter WHERE clause validation
  - [x] Replaced permissive `.+` with restrictive character classes
  - [x] Added explicit structure validation
  - [x] Better detection of injection attempts
  - **Completed:** January 2026

### Medium Priority - Additional Security

- [ ] **Security: Query Result Size Limits** - Prevent data exfiltration
  - [ ] Add max_rows configuration per tier
  - [ ] Enforce result set limits
  - [ ] Return truncation warnings
  - [ ] Test with large result sets
  - **Estimated effort:** 2-3 hours

- [ ] **Security: Query Timeout Enforcement** - Prevent resource exhaustion
  - [ ] Add per-query timeout configuration
  - [ ] Kill long-running queries
  - [ ] Log slow query attempts
  - [ ] Test with expensive queries
  - **Estimated effort:** 2-3 hours

- [ ] **Security: Audit Logging** - Complete query audit trail
  - [ ] Log all query attempts (success and failure)
  - [ ] Include user, workspace, query, and bindings
  - [ ] Add tamper-proof logging
  - [ ] Implement log retention policy
  - **Estimated effort:** 3-4 hours

## Features & Enhancements

### High Priority

- [x] **COMPLETED: EXPLAIN Plan Analysis** - Query optimization insights
  - [x] Added `explain` parameter to QueryDatabase tool
  - [x] Returns human-readable performance analysis
  - [x] Shows index usage and optimization opportunities
  - **Completed:** January 2026

- [ ] **Feature: Query Templates** - Reusable parameterized queries
  - [ ] Create query template system
  - [ ] Support named parameters
  - [ ] Add template validation
  - [ ] Store templates per workspace
  - [ ] Test with complex queries
  - **Estimated effort:** 5-6 hours
  - **Files:** `src/Mod/Mcp/Templates/`

- [ ] **Feature: Schema Exploration Tools** - Database metadata access
  - [ ] Add ListTables tool
  - [ ] Add DescribeTable tool
  - [ ] Add ListIndexes tool
  - [ ] Respect information_schema restrictions
  - [ ] Test with multiple database types
  - **Estimated effort:** 4-5 hours
  - **Files:** `src/Mod/Mcp/Tools/Schema/`

### Medium Priority

- [ ] **Enhancement: Query Result Caching** - Cache frequent queries
  - [ ] Implement result caching with TTL
  - [ ] Add cache key generation
  - [ ] Support cache invalidation
  - [ ] Test cache hit rates
  - **Estimated effort:** 3-4 hours

- [ ] **Enhancement: Query History** - Track agent queries
  - [ ] Store query history per workspace
  - [ ] Add query rerun capability
  - [ ] Create history browser UI
  - [ ] Add favorite queries
  - **Estimated effort:** 4-5 hours
  - **Files:** `src/Mod/Mcp/History/`

- [ ] **Enhancement: Advanced Analytics** - Deeper insights
  - [ ] Add query complexity scoring
  - [ ] Track table access patterns
  - [ ] Identify slow query patterns
  - [ ] Create optimization recommendations
  - **Estimated effort:** 5-6 hours
  - **Files:** `src/Mod/Mcp/Analytics/`

### Low Priority

- [ ] **Enhancement: Multi-Database Support** - Query multiple databases
  - [ ] Support cross-database queries
  - [ ] Add database selection parameter
  - [ ] Test with MySQL, PostgreSQL, SQLite
  - **Estimated effort:** 4-5 hours

- [ ] **Enhancement: Query Builder UI** - Visual query construction
  - [ ] Create Livewire query builder component
  - [ ] Add table/column selection
  - [ ] Support WHERE clause builder
  - [ ] Generate safe SQL
  - **Estimated effort:** 8-10 hours
  - **Files:** `src/Mod/Mcp/QueryBuilder/`

## Tool Development

### High Priority

- [ ] **Tool: Create/Update Records** - Controlled data modification
  - [ ] Create InsertRecord tool with strict validation
  - [ ] Create UpdateRecord tool with WHERE requirements
  - [ ] Implement record-level permissions
  - [ ] Require explicit confirmation for modifications
  - [ ] Test with workspace scoping
  - **Estimated effort:** 6-8 hours
  - **Files:** `src/Mod/Mcp/Tools/Modify/`
  - **Note:** Requires careful security review

- [ ] **Tool: Export Data** - Export query results
  - [ ] Add ExportResults tool
  - [ ] Support CSV, JSON, Excel formats
  - [ ] Add row limits per tier
  - [ ] Implement streaming for large exports
  - **Estimated effort:** 4-5 hours
  - **Files:** `src/Mod/Mcp/Tools/Export/`

### Medium Priority

- [ ] **Tool: Analyze Performance** - Database health insights
  - [ ] Add TableStats tool (row count, size, etc.)
  - [ ] Add SlowQueries tool
  - [ ] Add IndexUsage tool
  - [ ] Create performance dashboard
  - **Estimated effort:** 5-6 hours
  - **Files:** `src/Mod/Mcp/Tools/Performance/`

- [ ] **Tool: Data Validation** - Validate data quality
  - [ ] Add ValidateData tool
  - [ ] Check for NULL values, duplicates
  - [ ] Validate foreign key integrity
  - [ ] Generate data quality report
  - **Estimated effort:** 4-5 hours
  - **Files:** `src/Mod/Mcp/Tools/Validation/`

## Documentation

- [x] **Guide: Creating MCP Tools** - Comprehensive tutorial
  - [x] Document tool interface
  - [x] Show parameter validation
  - [x] Explain workspace context
  - [x] Add dependency examples
  - [x] Include security best practices
  - **Completed:** January 2026
  - **File:** `docs/packages/mcp/creating-mcp-tools.md`

- [x] **Guide: SQL Security** - Safe query patterns
  - [x] Document allowed SQL patterns
  - [x] Show parameterized query examples
  - [x] Explain validation rules
  - [x] List forbidden operations
  - **Completed:** January 2026
  - **File:** `docs/packages/mcp/sql-security.md`

- [x] **API Reference: All MCP Tools** - Complete tool catalog
  - [x] Document each tool's parameters
  - [x] Add usage examples
  - [x] Show response formats
  - [x] Include error cases
  - **Completed:** January 2026
  - **File:** `docs/packages/mcp/tools-reference.md`

## Code Quality

- [ ] **Refactor: Extract SQL Parser** - Better query validation
  - [ ] Create proper SQL parser
  - [ ] Replace regex with AST parsing
  - [ ] Support dialect-specific syntax
  - [ ] Add comprehensive tests
  - **Estimated effort:** 8-10 hours

- [ ] **Refactor: Standardize Tool Responses** - Consistent API
  - [ ] Create ToolResult DTO
  - [ ] Standardize error responses
  - [ ] Add response metadata
  - [ ] Update all tools
  - **Estimated effort:** 3-4 hours

- [ ] **PHPStan: Fix Level 5 Errors** - Improve type safety
  - [ ] Fix property type declarations
  - [ ] Add missing return types
  - [ ] Fix array shape types
  - **Estimated effort:** 2-3 hours

## Performance

- [ ] **Optimization: Query Result Streaming** - Handle large results
  - [ ] Implement cursor-based result streaming
  - [ ] Add chunked response delivery
  - [ ] Test with millions of rows
  - **Estimated effort:** 3-4 hours

- [ ] **Optimization: Connection Pooling** - Reuse database connections
  - [ ] Implement connection pool
  - [ ] Add connection health checks
  - [ ] Test connection lifecycle
  - **Estimated effort:** 3-4 hours

## Infrastructure

- [ ] **Monitoring: Alert on Suspicious Queries** - Security monitoring
  - [ ] Detect unusual query patterns
  - [ ] Alert on potential injection attempts
  - [ ] Track query anomalies
  - [ ] Create security dashboard
  - **Estimated effort:** 4-5 hours

- [ ] **CI/CD: Add Security Regression Tests** - Prevent vulnerabilities
  - [ ] Test SQL injection prevention
  - [ ] Test workspace isolation
  - [ ] Test quota enforcement
  - [ ] Fail CI on security issues
  - **Estimated effort:** 3-4 hours

---

## Completed (January 2026)

- [x] **Security: Database Connection Validation** - Throws exception for invalid connections
- [x] **Security: SQL Validator Strengthening** - Stricter WHERE clause patterns
- [x] **Feature: EXPLAIN Plan Analysis** - Query optimization insights
- [x] **Tool Analytics System** - Complete usage tracking and metrics
- [x] **Quota System** - Tier-based limits with enforcement
- [x] **Workspace Context** - Automatic query scoping and validation
- [x] **Documentation: Creating MCP Tools Guide** - Complete tutorial with workspace context, dependencies, security
- [x] **Documentation: SQL Security Guide** - Allowed patterns, forbidden operations, injection prevention
- [x] **Documentation: MCP Tools API Reference** - All tools with parameters, examples, error handling

*See `changelog/2026/jan/` for completed features and security fixes.*
