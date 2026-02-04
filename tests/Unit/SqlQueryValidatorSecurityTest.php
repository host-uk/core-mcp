<?php

declare(strict_types=1);

namespace Core\Mcp\Tests;

require_once __DIR__ . '/../../src/Mcp/Exceptions/ForbiddenQueryException.php';
require_once __DIR__ . '/../../src/Mcp/Services/SqlQueryValidator.php';

use Core\Mcp\Exceptions\ForbiddenQueryException;
use Core\Mcp\Services\SqlQueryValidator;
use PHPUnit\Framework\TestCase;

class SqlQueryValidatorSecurityTest extends TestCase
{
    private SqlQueryValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SqlQueryValidator();
    }

    public function testAllowsLegitimateJoins(): void
    {
        $queries = [
            'SELECT * FROM users JOIN roles ON users.role_id = roles.id',
            'SELECT * FROM users LEFT JOIN roles ON users.role_id = roles.id',
            'SELECT * FROM users RIGHT JOIN roles ON users.role_id = roles.id',
            'SELECT * FROM users INNER JOIN roles ON users.role_id = roles.id',
            'SELECT * FROM users CROSS JOIN roles',
            'SELECT * FROM users JOIN roles ON users.role_id = roles.id JOIN permissions ON roles.id = permissions.role_id',
            'SELECT u.*, r.name FROM users AS u JOIN roles AS r ON u.role_id = r.id',
            'SELECT * FROM users JOIN roles USING (role_id)',
        ];

        foreach ($queries as $query) {
            $this->assertTrue($this->validator->isValid($query), "Should allow: $query");
        }
    }

    public function testAllowsMultipleTablesCommaSeparated(): void
    {
        $query = 'SELECT * FROM users, roles WHERE users.role_id = roles.id';
        $this->assertTrue($this->validator->isValid($query));
    }

    public function testAllowsGroupByAndHaving(): void
    {
        $queries = [
            'SELECT status, COUNT(*) FROM users GROUP BY status',
            'SELECT status, COUNT(*) FROM users GROUP BY status HAVING COUNT(*) > 1',
            'SELECT status, COUNT(*) FROM users WHERE created_at > "2023-01-01" GROUP BY status HAVING COUNT(*) > 1 ORDER BY status DESC',
        ];

        foreach ($queries as $query) {
            $this->assertTrue($this->validator->isValid($query), "Should allow: $query");
        }
    }

    public function testBlocksSubqueriesAnywhere(): void
    {
        $queries = [
            'SELECT * FROM users WHERE id = (SELECT id FROM admins LIMIT 1)',
            'SELECT (SELECT id FROM admins LIMIT 1) FROM users',
            'SELECT * FROM users JOIN (SELECT * FROM roles) AS r ON users.role_id = r.id',
            'SELECT * FROM users WHERE EXISTS (SELECT 1 FROM admins WHERE admins.user_id = users.id)',
            'SELECT * FROM users HAVING id = (SELECT 1)',
        ];

        foreach ($queries as $query) {
            $this->assertFalse($this->validator->isValid($query), "Should block subquery: $query");
        }
    }

    public function testBlocksDangerousFunctions(): void
    {
        $queries = [
            'SELECT USER() FROM users',
            'SELECT database()',
            'SELECT version()',
            'SELECT @@version',
            'SELECT @@hostname',
            'SELECT * FROM users WHERE SLEEP(5)',
            'SELECT * FROM users WHERE BENCHMARK(1000000, MD5(\'x\'))',
        ];

        foreach ($queries as $query) {
            $this->assertFalse($this->validator->isValid($query), "Should block: $query");
        }
    }

    public function testBlocksInjectionViaJoin(): void
    {
        $queries = [
            'SELECT * FROM users JOIN (SELECT * FROM passwords)', // Subquery in JOIN
            'SELECT * FROM users JOIN roles ON 1=(SELECT 1)', // Subquery in ON
            'SELECT * FROM users JOIN mysql.user', // System table
        ];

        foreach ($queries as $query) {
            $this->assertFalse($this->validator->isValid($query), "Should block: $query");
        }
    }

    public function testBlocksCommentObfuscation(): void
    {
        $queries = [
            'SELECT * FROM users WHERE id = 1 /*! UNION */ SELECT * FROM passwords',
        ];

        foreach ($queries as $query) {
            $this->assertFalse($this->validator->isValid($query), "Should block: $query");
        }
    }
}
