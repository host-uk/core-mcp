<?php

declare(strict_types=1);

namespace Core\Mcp\View\Modal\Admin;

use Core\Mod\Api\Models\ApiKey;
use Core\Tenant\Models\Workspace;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * MCP API Key Manager.
 *
 * Allows workspace owners to create and manage API keys
 * for accessing MCP servers via HTTP API.
 */
#[Layout('hub::admin.layouts.app')]
class ApiKeyManager extends Component
{
    public Workspace $workspace;

    // Create form state
    public bool $showCreateModal = false;

    public string $newKeyName = '';

    public array $newKeyScopes = ['read', 'write'];

    public string $newKeyExpiry = 'never';

    // Show new key (only visible once after creation)
    public ?string $newPlainKey = null;

    public bool $showNewKeyModal = false;

    public function mount(Workspace $workspace): void
    {
        Gate::authorize('view', $workspace);
        $this->workspace = $workspace;
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
        $this->newKeyName = '';
        $this->newKeyScopes = ['read', 'write'];
        $this->newKeyExpiry = 'never';
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createKey(): void
    {
        Gate::authorize('create', ApiKey::class);

        if (RateLimiter::tooManyAttempts('create-key:'.auth()->id(), 5)) {
            $seconds = RateLimiter::availableIn('create-key:'.auth()->id());
            $this->addError('newKeyName', __('Too many attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]));

            return;
        }

        $this->validate([
            'newKeyName' => 'required|string|max:100',
        ]);

        RateLimiter::hit('create-key:'.auth()->id());

        $expiresAt = match ($this->newKeyExpiry) {
            '30days' => now()->addDays(30),
            '90days' => now()->addDays(90),
            '1year' => now()->addYear(),
            default => null,
        };

        $result = ApiKey::generate(
            workspaceId: $this->workspace->id,
            userId: auth()->id(),
            name: $this->newKeyName,
            scopes: $this->newKeyScopes,
            expiresAt: $expiresAt,
        );

        $this->newPlainKey = $result['plain_key'];
        $this->showCreateModal = false;
        $this->showNewKeyModal = true;

        Log::channel('security')->info('MCP API key created', [
            'workspace_id' => $this->workspace->id,
            'user_id' => auth()->id(),
            'key_name' => $this->newKeyName,
            'scopes' => $this->newKeyScopes,
        ]);

        session()->flash('message', 'API key created successfully.');
    }

    public function closeNewKeyModal(): void
    {
        $this->newPlainKey = null;
        $this->showNewKeyModal = false;
    }

    public function revokeKey(int $keyId): void
    {
        $key = $this->workspace->apiKeys()->find($keyId);

        if (! $key) {
            return;
        }

        Gate::authorize('delete', $key);

        $key->revoke();

        Log::channel('security')->info('MCP API key revoked', [
            'workspace_id' => $this->workspace->id,
            'user_id' => auth()->id(),
            'key_id' => $keyId,
        ]);

        session()->flash('message', 'API key revoked.');
    }

    public function toggleScope(string $scope): void
    {
        if (in_array($scope, $this->newKeyScopes)) {
            $this->newKeyScopes = array_values(array_diff($this->newKeyScopes, [$scope]));
        } else {
            $this->newKeyScopes[] = $scope;
        }
    }

    public function render()
    {
        return view('mcp::admin.api-key-manager', [
            'keys' => $this->workspace->apiKeys()->orderByDesc('created_at')->get(),
        ]);
    }
}
