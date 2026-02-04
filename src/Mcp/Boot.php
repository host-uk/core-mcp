<?php

declare(strict_types=1);

namespace Core\Mcp;

use Core\Events\AdminPanelBooting;
use Core\Events\ConsoleBooting;
use Core\Events\McpToolsRegistering;
use Core\Mcp\Events\ToolExecuted;
use Core\Mcp\Listeners\RecordToolExecution;
use Core\Mcp\Services\AuditLogService;
use Core\Mcp\Services\McpQuotaService;
use Core\Mcp\Services\QueryAuditService;
use Core\Mcp\Services\QueryExecutionService;
use Core\Mcp\Services\ToolAnalyticsService;
use Core\Mcp\Services\ToolDependencyService;
use Core\Mcp\Services\ToolRegistry;
use Core\Mcp\Services\ToolVersionService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class Boot extends ServiceProvider
{
    /**
     * The module name.
     */
    protected string $moduleName = 'mcp';

    /**
     * Events this module listens to for lazy loading.
     *
     * @var array<class-string, string>
     */
    public static array $listens = [
        AdminPanelBooting::class => 'onAdminPanel',
        ConsoleBooting::class => 'onConsole',
        McpToolsRegistering::class => 'onMcpTools',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/mcp.php', 'mcp');

        $this->app->singleton(ToolRegistry::class);
        $this->app->singleton(ToolAnalyticsService::class);
        $this->app->singleton(McpQuotaService::class);
        $this->app->singleton(ToolDependencyService::class);
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(ToolVersionService::class);
        $this->app->singleton(QueryAuditService::class);
        $this->app->singleton(QueryExecutionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/mcp.php' => config_path('mcp.php'),
            ], 'mcp-config');
        }

        // Register event listener for tool execution analytics
        Event::listen(ToolExecuted::class, RecordToolExecution::class);

        // Validate security-critical configuration
        $this->validateConfig();
    }

    /**
     * Validate security-critical MCP configuration.
     */
    protected function validateConfig(): void
    {
        // Only validate if not running in console (e.g., web requests)
        // or if explicitly running an MCP command
        $argv = $_SERVER['argv'] ?? [];
        $isMcpCommand = false;
        foreach ($argv as $arg) {
            if (str_contains($arg, 'mcp:')) {
                $isMcpCommand = true;
                break;
            }
        }

        if ($this->app->runningInConsole() && ! $isMcpCommand) {
            return;
        }

        // 1. Database Connection Security
        $connection = config('mcp.database.connection');
        if ($this->app->environment('production') && empty($connection)) {
            \Illuminate\Support\Facades\Log::warning(
                'MCP: No dedicated database connection configured. ' .
                'Using the default connection in production is a security risk.'
            );
        }

        // 2. Audit Logging
        $channel = config('mcp.audit.log_channel');
        if (! empty($channel) && $channel !== 'mcp-queries' && ! config("logging.channels.{$channel}")) {
            \Illuminate\Support\Facades\Log::error(
                "MCP: Configured audit log channel '{$channel}' does not exist in logging configuration."
            );
        }

        // 3. SQL Whitelist
        if (! config('mcp.database.use_whitelist', true)) {
            \Illuminate\Support\Facades\Log::notice(
                'MCP: SQL whitelist validation is disabled. This reduces the protection against unauthorized queries.'
            );
        }
    }

    // -------------------------------------------------------------------------
    // Event-driven handlers
    // -------------------------------------------------------------------------

    public function onAdminPanel(AdminPanelBooting $event): void
    {
        $event->views($this->moduleName, __DIR__.'/View/Blade');

        if (file_exists(__DIR__.'/Routes/admin.php')) {
            $event->routes(fn () => require __DIR__.'/Routes/admin.php');
        }

        $event->livewire('mcp.admin.api-key-manager', View\Modal\Admin\ApiKeyManager::class);
        $event->livewire('mcp.admin.playground', View\Modal\Admin\Playground::class);
        $event->livewire('mcp.admin.mcp-playground', View\Modal\Admin\McpPlayground::class);
        $event->livewire('mcp.admin.request-log', View\Modal\Admin\RequestLog::class);
        $event->livewire('mcp.admin.tool-analytics-dashboard', View\Modal\Admin\ToolAnalyticsDashboard::class);
        $event->livewire('mcp.admin.tool-analytics-detail', View\Modal\Admin\ToolAnalyticsDetail::class);
        $event->livewire('mcp.admin.quota-usage', View\Modal\Admin\QuotaUsage::class);
        $event->livewire('mcp.admin.audit-log-viewer', View\Modal\Admin\AuditLogViewer::class);
        $event->livewire('mcp.admin.tool-version-manager', View\Modal\Admin\ToolVersionManager::class);
    }

    public function onConsole(ConsoleBooting $event): void
    {
        $event->command(Console\Commands\McpAgentServerCommand::class);
        $event->command(Console\Commands\PruneMetricsCommand::class);
        $event->command(Console\Commands\VerifyAuditLogCommand::class);
    }

    public function onMcpTools(McpToolsRegistering $event): void
    {
        // MCP tool handlers will be registered here once extracted
        // from the monolithic McpAgentServerCommand
    }
}
