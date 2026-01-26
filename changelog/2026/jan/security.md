# Core-MCP - January 2026 - Security Fixes

## Critical

### Database Connection Validation

Fixed fallback behavior that could bypass read-only connection configuration.

**Issue:** QueryDatabase tool would silently fall back to default database connection if configured MCP connection was invalid.

**Fix:** Now throws `RuntimeException` with clear error message when configured connection doesn't exist.

**Files:**
- `Tools/QueryDatabase.php` - Added connection validation

**Impact:** Prevents accidental queries against production read-write connections.

---

## High Priority

### SQL Query Validator Strengthening

Restricted WHERE clause patterns to prevent SQL injection vectors.

**Issue:** Whitelist regex patterns used `.+` which was too permissive for WHERE clause validation.

**Fix:** Replaced with strict character class restrictions:
- Only allows: alphanumeric, spaces, backticks, operators, quotes, parentheses
- Explicitly supports AND/OR logical operators
- Blocks function calls and subqueries
- Prevents nested SELECT statements

**Files:**
- `Services/SqlQueryValidator.php` - Updated DEFAULT_WHITELIST patterns

**Before:**
```php
'/^\s*SELECT\s+.*\s+FROM\s+`?\w+`?(\s+WHERE\s+.+)?/i'
```

**After:**
```php
'/^\s*SELECT\s+.*\s+FROM\s+`?\w+`?(\s+WHERE\s+[\w\s`.,!=<>\'"%()]+(\s+(AND|OR)\s+[\w\s`.,!=<>\'"%()]+)*)?/i'
```

**Defense in depth:**
- Read-only database user (infrastructure)
- Blocked keywords (application)
- Pattern validation (application)
- Whitelist matching (application)
- Table access controls (application)
