<?php

declare(strict_types=1);

namespace Core\Mod\Mcp\Tests\Unit;

use Core\Mod\Mcp\Models\McpUsageQuota;
use Core\Mod\Mcp\Services\McpQuotaService;
use Core\Mod\Tenant\Models\Workspace;
use Core\Mod\Tenant\Services\EntitlementResult;
use Core\Mod\Tenant\Services\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class McpQuotaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected McpQuotaService $quotaService;

    protected EntitlementService $entitlementsMock;

    protected Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entitlementsMock = Mockery::mock(EntitlementService::class);
        $this->quotaService = new McpQuotaService($this->entitlementsMock);

        $this->workspace = Workspace::factory()->create();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_records_usage_for_workspace(): void
    {
        $quota = $this->quotaService->recordUsage($this->workspace, toolCalls: 5, inputTokens: 100, outputTokens: 50);

        $this->assertInstanceOf(McpUsageQuota::class, $quota);
        $this->assertEquals(5, $quota->tool_calls_count);
        $this->assertEquals(100, $quota->input_tokens);
        $this->assertEquals(50, $quota->output_tokens);
        $this->assertEquals(now()->format('Y-m'), $quota->month);
    }

    public function test_increments_existing_usage(): void
    {
        // First call
        $this->quotaService->recordUsage($this->workspace, toolCalls: 5, inputTokens: 100, outputTokens: 50);

        // Second call
        $quota = $this->quotaService->recordUsage($this->workspace, toolCalls: 3, inputTokens: 200, outputTokens: 100);

        $this->assertEquals(8, $quota->tool_calls_count);
        $this->assertEquals(300, $quota->input_tokens);
        $this->assertEquals(150, $quota->output_tokens);
    }

    public function test_check_quota_returns_true_when_unlimited(): void
    {
        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS)
            ->andReturn(EntitlementResult::unlimited(McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS));

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOKENS)
            ->andReturn(EntitlementResult::unlimited(McpQuotaService::FEATURE_MONTHLY_TOKENS));

        $result = $this->quotaService->checkQuota($this->workspace);

        $this->assertTrue($result);
    }

    public function test_check_quota_returns_false_when_denied(): void
    {
        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS)
            ->andReturn(EntitlementResult::denied('Not included in plan', featureCode: McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS));

        $result = $this->quotaService->checkQuota($this->workspace);

        $this->assertFalse($result);
    }

    public function test_check_quota_returns_false_when_limit_exceeded(): void
    {
        // Set up existing usage that exceeds limit
        McpUsageQuota::create([
            'workspace_id' => $this->workspace->id,
            'month' => now()->format('Y-m'),
            'tool_calls_count' => 100,
            'input_tokens' => 0,
            'output_tokens' => 0,
        ]);

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS)
            ->andReturn(EntitlementResult::allowed(limit: 100, used: 100, featureCode: McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS));

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOKENS)
            ->andReturn(EntitlementResult::unlimited(McpQuotaService::FEATURE_MONTHLY_TOKENS));

        $result = $this->quotaService->checkQuota($this->workspace);

        $this->assertFalse($result);
    }

    public function test_check_quota_returns_true_when_within_limit(): void
    {
        McpUsageQuota::create([
            'workspace_id' => $this->workspace->id,
            'month' => now()->format('Y-m'),
            'tool_calls_count' => 50,
            'input_tokens' => 0,
            'output_tokens' => 0,
        ]);

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS)
            ->andReturn(EntitlementResult::allowed(limit: 100, used: 50, featureCode: McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS));

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOKENS)
            ->andReturn(EntitlementResult::unlimited(McpQuotaService::FEATURE_MONTHLY_TOKENS));

        $result = $this->quotaService->checkQuota($this->workspace);

        $this->assertTrue($result);
    }

    public function test_get_remaining_quota_calculates_correctly(): void
    {
        McpUsageQuota::create([
            'workspace_id' => $this->workspace->id,
            'month' => now()->format('Y-m'),
            'tool_calls_count' => 30,
            'input_tokens' => 500,
            'output_tokens' => 500,
        ]);

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS)
            ->andReturn(EntitlementResult::allowed(limit: 100, used: 30, featureCode: McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS));

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOKENS)
            ->andReturn(EntitlementResult::allowed(limit: 5000, used: 1000, featureCode: McpQuotaService::FEATURE_MONTHLY_TOKENS));

        $remaining = $this->quotaService->getRemainingQuota($this->workspace);

        $this->assertEquals(70, $remaining['tool_calls']);
        $this->assertEquals(4000, $remaining['tokens']);
        $this->assertFalse($remaining['tool_calls_unlimited']);
        $this->assertFalse($remaining['tokens_unlimited']);
    }

    public function test_get_quota_headers_returns_correct_format(): void
    {
        McpUsageQuota::create([
            'workspace_id' => $this->workspace->id,
            'month' => now()->format('Y-m'),
            'tool_calls_count' => 25,
            'input_tokens' => 300,
            'output_tokens' => 200,
        ]);

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS)
            ->andReturn(EntitlementResult::allowed(limit: 100, used: 25, featureCode: McpQuotaService::FEATURE_MONTHLY_TOOL_CALLS));

        $this->entitlementsMock
            ->shouldReceive('can')
            ->with($this->workspace, McpQuotaService::FEATURE_MONTHLY_TOKENS)
            ->andReturn(EntitlementResult::unlimited(McpQuotaService::FEATURE_MONTHLY_TOKENS));

        $headers = $this->quotaService->getQuotaHeaders($this->workspace);

        $this->assertArrayHasKey('X-MCP-Quota-Tool-Calls-Used', $headers);
        $this->assertArrayHasKey('X-MCP-Quota-Tool-Calls-Limit', $headers);
        $this->assertArrayHasKey('X-MCP-Quota-Tool-Calls-Remaining', $headers);
        $this->assertArrayHasKey('X-MCP-Quota-Tokens-Used', $headers);
        $this->assertArrayHasKey('X-MCP-Quota-Tokens-Limit', $headers);
        $this->assertArrayHasKey('X-MCP-Quota-Reset', $headers);

        $this->assertEquals('25', $headers['X-MCP-Quota-Tool-Calls-Used']);
        $this->assertEquals('100', $headers['X-MCP-Quota-Tool-Calls-Limit']);
        $this->assertEquals('unlimited', $headers['X-MCP-Quota-Tokens-Limit']);
    }

    public function test_reset_monthly_quota_clears_usage(): void
    {
        McpUsageQuota::create([
            'workspace_id' => $this->workspace->id,
            'month' => now()->format('Y-m'),
            'tool_calls_count' => 50,
            'input_tokens' => 1000,
            'output_tokens' => 500,
        ]);

        $quota = $this->quotaService->resetMonthlyQuota($this->workspace);

        $this->assertEquals(0, $quota->tool_calls_count);
        $this->assertEquals(0, $quota->input_tokens);
        $this->assertEquals(0, $quota->output_tokens);
    }

    public function test_get_usage_history_returns_ordered_records(): void
    {
        // Create usage for multiple months
        foreach (['2026-01', '2025-12', '2025-11'] as $month) {
            McpUsageQuota::create([
                'workspace_id' => $this->workspace->id,
                'month' => $month,
                'tool_calls_count' => rand(10, 100),
                'input_tokens' => rand(100, 1000),
                'output_tokens' => rand(100, 1000),
            ]);
        }

        $history = $this->quotaService->getUsageHistory($this->workspace, 3);

        $this->assertCount(3, $history);
        // Should be ordered by month descending
        $this->assertEquals('2026-01', $history->first()->month);
        $this->assertEquals('2025-11', $history->last()->month);
    }
}
