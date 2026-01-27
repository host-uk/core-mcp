<?php

declare(strict_types=1);

/**
 * Unit: Workspace Context Security
 *
 * Tests for MCP workspace context security to prevent cross-tenant data leakage.
 */

use Core\Mod\Tenant\Models\User;
use Core\Mod\Tenant\Models\Workspace;
use Mod\Mcp\Context\WorkspaceContext;
use Mod\Mcp\Exceptions\MissingWorkspaceContextException;
use Mod\Mcp\Tools\Concerns\RequiresWorkspaceContext;

// Test class using the trait
class TestToolWithWorkspaceContext
{
    use RequiresWorkspaceContext;

    protected string $name = 'test_tool';
}

describe('MissingWorkspaceContextException', function () {
    it('creates exception with tool name', function () {
        $exception = new MissingWorkspaceContextException('ListInvoices');

        expect($exception->tool)->toBe('ListInvoices');
        expect($exception->getMessage())->toContain('ListInvoices');
        expect($exception->getMessage())->toContain('workspace context');
    });

    it('creates exception with custom message', function () {
        $exception = new MissingWorkspaceContextException('TestTool', 'Custom error message');

        expect($exception->getMessage())->toBe('Custom error message');
        expect($exception->tool)->toBe('TestTool');
    });

    it('returns correct status code', function () {
        $exception = new MissingWorkspaceContextException('TestTool');

        expect($exception->getStatusCode())->toBe(403);
    });

    it('returns correct error type', function () {
        $exception = new MissingWorkspaceContextException('TestTool');

        expect($exception->getErrorType())->toBe('missing_workspace_context');
    });
});

describe('WorkspaceContext', function () {
    beforeEach(function () {
        $this->workspace = Workspace::factory()->create([
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
        ]);
    });

    it('creates context from workspace model', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);

        expect($context->workspaceId)->toBe($this->workspace->id);
        expect($context->workspace)->toBe($this->workspace);
    });

    it('creates context from workspace ID', function () {
        $context = WorkspaceContext::fromId($this->workspace->id);

        expect($context->workspaceId)->toBe($this->workspace->id);
        expect($context->workspace)->toBeNull();
    });

    it('loads workspace when accessing from ID-only context', function () {
        $context = WorkspaceContext::fromId($this->workspace->id);

        $loadedWorkspace = $context->getWorkspace();

        expect($loadedWorkspace->id)->toBe($this->workspace->id);
        expect($loadedWorkspace->name)->toBe('Test Workspace');
    });

    it('validates ownership correctly', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);

        // Should not throw for matching workspace
        $context->validateOwnership($this->workspace->id, 'invoice');

        expect(true)->toBeTrue(); // If we get here, no exception was thrown
    });

    it('throws on ownership validation failure', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);
        $differentWorkspaceId = $this->workspace->id + 999;

        expect(fn () => $context->validateOwnership($differentWorkspaceId, 'invoice'))
            ->toThrow(\RuntimeException::class, 'invoice does not belong to the authenticated workspace');
    });

    it('checks workspace ID correctly', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);

        expect($context->hasWorkspaceId($this->workspace->id))->toBeTrue();
        expect($context->hasWorkspaceId($this->workspace->id + 1))->toBeFalse();
    });
});

describe('RequiresWorkspaceContext trait', function () {
    beforeEach(function () {
        $this->workspace = Workspace::factory()->create();
        $this->tool = new TestToolWithWorkspaceContext;
    });

    it('throws MissingWorkspaceContextException when no context set', function () {
        expect(fn () => $this->tool->getWorkspaceId())
            ->toThrow(MissingWorkspaceContextException::class);
    });

    it('returns workspace ID when context is set', function () {
        $this->tool->setWorkspace($this->workspace);

        expect($this->tool->getWorkspaceId())->toBe($this->workspace->id);
    });

    it('returns workspace when context is set', function () {
        $this->tool->setWorkspace($this->workspace);

        $workspace = $this->tool->getWorkspace();

        expect($workspace->id)->toBe($this->workspace->id);
    });

    it('allows setting context from workspace ID', function () {
        $this->tool->setWorkspaceId($this->workspace->id);

        expect($this->tool->getWorkspaceId())->toBe($this->workspace->id);
    });

    it('allows setting context object directly', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);
        $this->tool->setWorkspaceContext($context);

        expect($this->tool->getWorkspaceId())->toBe($this->workspace->id);
    });

    it('correctly reports whether context is available', function () {
        expect($this->tool->hasWorkspaceContext())->toBeFalse();

        $this->tool->setWorkspace($this->workspace);

        expect($this->tool->hasWorkspaceContext())->toBeTrue();
    });

    it('validates resource ownership through context', function () {
        $this->tool->setWorkspace($this->workspace);
        $differentWorkspaceId = $this->workspace->id + 999;

        expect(fn () => $this->tool->validateResourceOwnership($differentWorkspaceId, 'subscription'))
            ->toThrow(\RuntimeException::class, 'subscription does not belong');
    });

    it('requires context with custom error message', function () {
        expect(fn () => $this->tool->requireWorkspaceContext('listing invoices'))
            ->toThrow(MissingWorkspaceContextException::class, 'listing invoices');
    });
});

describe('Workspace-scoped tool security', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->workspace->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_default' => true,
        ]);

        // Create another workspace to test isolation
        $this->otherWorkspace = Workspace::factory()->create();
    });

    it('prevents accessing another workspace data by setting context correctly', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);

        // Trying to validate ownership of data from another workspace should fail
        expect(fn () => $context->validateOwnership($this->otherWorkspace->id, 'data'))
            ->toThrow(\RuntimeException::class);
    });
});
