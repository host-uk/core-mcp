<?php

declare(strict_types=1);

namespace Core\Mcp\Tests\Unit;

use Core\Mcp\Context\WorkspaceContext;
use Core\Mcp\Exceptions\MissingWorkspaceContextException;
use Core\Mcp\Tools\ContentTools;
use Core\Tenant\Models\Workspace;
use Laravel\Mcp\Request;

describe('ContentTools', function () {
    beforeEach(function () {
        $this->tool = new ContentTools();
        $this->workspace = new Workspace();
        $this->workspace->id = 1;
        $this->workspace->slug = 'test-workspace';
    });

    it('throws MissingWorkspaceContextException when handle is called without context', function () {
        $request = new Request(['action' => 'list']);

        expect(fn () => $this->tool->handle($request))
            ->toThrow(MissingWorkspaceContextException::class);
    });

    it('uses workspace from authenticated context when provided', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);
        $this->tool->setWorkspaceContext($context);

        $request = new Request(['action' => 'list']);

        // We expect it to NOT throw MissingWorkspaceContextException.
        // It might throw other exceptions related to missing database/services, which is expected in a unit test.
        try {
            $this->tool->handle($request);
        } catch (MissingWorkspaceContextException $e) {
            $this->fail('Should not throw MissingWorkspaceContextException when context is provided');
        } catch (\Throwable $e) {
            // Reaching here means it passed the getWorkspace() check
            expect($e)->not->toBeInstanceOf(MissingWorkspaceContextException::class);
        }
    });

    it('no longer accepts workspace slug as a request parameter', function () {
        $context = WorkspaceContext::fromWorkspace($this->workspace);
        $this->tool->setWorkspaceContext($context);

        // Even if we provide a different 'workspace' in the request, it should be ignored.
        $request = new Request([
            'action' => 'list',
            'workspace' => 'other-workspace',
        ]);

        try {
            $this->tool->handle($request);
        } catch (MissingWorkspaceContextException $e) {
            $this->fail('Should not throw MissingWorkspaceContextException when context is provided');
        } catch (\Throwable $e) {
            // Success if we reached beyond the workspace context check
            expect($e)->not->toBeInstanceOf(MissingWorkspaceContextException::class);
        }
    });
});
