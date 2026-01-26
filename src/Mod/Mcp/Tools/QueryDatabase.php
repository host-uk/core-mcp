<?php

declare(strict_types=1);

namespace Core\Mod\Mcp\Tools;

use Core\Mod\Mcp\Exceptions\ForbiddenQueryException;
use Core\Mod\Mcp\Services\SqlQueryValidator;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

/**
 * MCP Tool for executing read-only SQL queries.
 *
 * Security measures:
 * 1. Uses configurable read-only database connection
 * 2. Validates queries against blocked keywords and patterns
 * 3. Optional whitelist-based query validation
 * 4. Blocks access to sensitive tables
 * 5. Enforces row limits
 */
class QueryDatabase extends Tool
{
    protected string $description = 'Execute a read-only SQL SELECT query against the database';

    private SqlQueryValidator $validator;

    public function __construct()
    {
        $this->validator = $this->createValidator();
    }

    public function handle(Request $request): Response
    {
        $query = $request->input('query');
        $explain = $request->input('explain', false);

        if (empty($query)) {
            return $this->errorResponse('Query is required');
        }

        // Validate the query
        try {
            $this->validator->validate($query);
        } catch (ForbiddenQueryException $e) {
            return $this->errorResponse($e->getMessage());
        }

        // Check for blocked tables
        $blockedTable = $this->checkBlockedTables($query);
        if ($blockedTable !== null) {
            return $this->errorResponse(
                sprintf("Access to table '%s' is not permitted", $blockedTable)
            );
        }

        // Apply row limit if not present
        $query = $this->applyRowLimit($query);

        try {
            $connection = $this->getConnection();

            // If explain is requested, run EXPLAIN first
            if ($explain) {
                return $this->handleExplain($connection, $query);
            }

            $results = DB::connection($connection)->select($query);

            return Response::text(json_encode($results, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            // Log the actual error for debugging but return sanitised message
            report($e);

            return $this->errorResponse('Query execution failed: '.$this->sanitiseErrorMessage($e->getMessage()));
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string('SQL SELECT query to execute. Only read-only SELECT queries are permitted.'),
            'explain' => $schema->boolean('If true, runs EXPLAIN on the query instead of executing it. Useful for query optimization and debugging.')->default(false),
        ];
    }

    /**
     * Create the SQL validator with configuration.
     */
    private function createValidator(): SqlQueryValidator
    {
        $useWhitelist = Config::get('mcp.database.use_whitelist', true);
        $customPatterns = Config::get('mcp.database.whitelist_patterns', []);

        $validator = new SqlQueryValidator(null, $useWhitelist);

        foreach ($customPatterns as $pattern) {
            $validator->addWhitelistPattern($pattern);
        }

        return $validator;
    }

    /**
     * Get the database connection to use.
     *
     * @throws \RuntimeException If the configured connection is invalid
     */
    private function getConnection(): ?string
    {
        $connection = Config::get('mcp.database.connection');

        // If configured connection doesn't exist, throw exception
        if ($connection && ! Config::has("database.connections.{$connection}")) {
            throw new \RuntimeException(
                "Invalid MCP database connection '{$connection}' configured. ".
                "Please ensure 'database.connections.{$connection}' exists in your database configuration."
            );
        }

        return $connection;
    }

    /**
     * Check if the query references any blocked tables.
     */
    private function checkBlockedTables(string $query): ?string
    {
        $blockedTables = Config::get('mcp.database.blocked_tables', []);

        foreach ($blockedTables as $table) {
            // Check for table references in various formats
            $patterns = [
                '/\bFROM\s+`?'.preg_quote($table, '/').'`?\b/i',
                '/\bJOIN\s+`?'.preg_quote($table, '/').'`?\b/i',
                '/\b'.preg_quote($table, '/').'\./i', // table.column format
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $query)) {
                    return $table;
                }
            }
        }

        return null;
    }

    /**
     * Apply row limit to query if not already present.
     */
    private function applyRowLimit(string $query): string
    {
        $maxRows = Config::get('mcp.database.max_rows', 1000);

        // Check if LIMIT is already present
        if (preg_match('/\bLIMIT\s+\d+/i', $query)) {
            return $query;
        }

        // Remove trailing semicolon if present
        $query = rtrim(trim($query), ';');

        return $query.' LIMIT '.$maxRows;
    }

    /**
     * Sanitise database error messages to avoid leaking sensitive information.
     */
    private function sanitiseErrorMessage(string $message): string
    {
        // Remove specific database paths, credentials, etc.
        $message = preg_replace('/\/[^\s]+/', '[path]', $message);
        $message = preg_replace('/at \d+\.\d+\.\d+\.\d+/', 'at [ip]', $message);

        // Truncate long messages
        if (strlen($message) > 200) {
            $message = substr($message, 0, 200).'...';
        }

        return $message;
    }

    /**
     * Handle EXPLAIN query execution.
     */
    private function handleExplain(?string $connection, string $query): Response
    {
        try {
            // Run EXPLAIN on the query
            $explainResults = DB::connection($connection)->select("EXPLAIN {$query}");

            // Also try to get extended information if MySQL/MariaDB
            $warnings = [];
            try {
                $warnings = DB::connection($connection)->select('SHOW WARNINGS');
            } catch (\Exception $e) {
                // SHOW WARNINGS may not be available on all databases
            }

            $response = [
                'explain' => $explainResults,
                'query' => $query,
            ];

            if (! empty($warnings)) {
                $response['warnings'] = $warnings;
            }

            // Add helpful interpretation
            $response['interpretation'] = $this->interpretExplain($explainResults);

            return Response::text(json_encode($response, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            report($e);

            return $this->errorResponse('EXPLAIN failed: '.$this->sanitiseErrorMessage($e->getMessage()));
        }
    }

    /**
     * Provide human-readable interpretation of EXPLAIN results.
     */
    private function interpretExplain(array $explainResults): array
    {
        $interpretation = [];

        foreach ($explainResults as $row) {
            $rowAnalysis = [];

            // Convert stdClass to array for easier access
            $rowArray = (array) $row;

            // Check for full table scan
            if (isset($rowArray['type']) && $rowArray['type'] === 'ALL') {
                $rowAnalysis[] = 'WARNING: Full table scan detected. Consider adding an index.';
            }

            // Check for filesort
            if (isset($rowArray['Extra']) && str_contains($rowArray['Extra'], 'Using filesort')) {
                $rowAnalysis[] = 'INFO: Using filesort. Query may benefit from an index on ORDER BY columns.';
            }

            // Check for temporary table
            if (isset($rowArray['Extra']) && str_contains($rowArray['Extra'], 'Using temporary')) {
                $rowAnalysis[] = 'INFO: Using temporary table. Consider optimizing the query.';
            }

            // Check rows examined
            if (isset($rowArray['rows']) && $rowArray['rows'] > 10000) {
                $rowAnalysis[] = sprintf('WARNING: High row count (%d rows). Query may be slow.', $rowArray['rows']);
            }

            // Check if index is used
            if (isset($rowArray['key']) && $rowArray['key'] !== null) {
                $rowAnalysis[] = sprintf('GOOD: Using index: %s', $rowArray['key']);
            }

            if (! empty($rowAnalysis)) {
                $interpretation[] = [
                    'table' => $rowArray['table'] ?? 'unknown',
                    'analysis' => $rowAnalysis,
                ];
            }
        }

        return $interpretation;
    }

    /**
     * Create an error response.
     */
    private function errorResponse(string $message): Response
    {
        return Response::text(json_encode(['error' => $message]));
    }
}
