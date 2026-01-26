<x-layouts.mcp>
    <x-slot:title>Setup Guide</x-slot:title>

    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Setup Guide</h1>
            <p class="mt-2 text-lg text-zinc-600 dark:text-zinc-400">
                Connect to Host UK MCP servers via HTTP API or stdio.
            </p>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            <a href="{{ route('mcp.servers.index') }}"
               class="p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-cyan-500 transition-colors text-center">
                <flux:icon.server-stack class="w-5 h-5 mx-auto mb-1 text-emerald-600 dark:text-emerald-400" />
                <span class="text-sm font-medium text-zinc-900 dark:text-white">Servers</span>
            </a>
            <a href="{{ route('mcp.openapi.json') }}" target="_blank"
               class="p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-cyan-500 transition-colors text-center">
                <flux:icon.code-bracket class="w-5 h-5 mx-auto mb-1 text-cyan-600 dark:text-cyan-400" />
                <span class="text-sm font-medium text-zinc-900 dark:text-white">OpenAPI</span>
            </a>
            <a href="{{ route('mcp.registry') }}" target="_blank"
               class="p-3 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-cyan-500 transition-colors text-center">
                <flux:icon.document-text class="w-5 h-5 mx-auto mb-1 text-violet-600 dark:text-violet-400" />
                <span class="text-sm font-medium text-zinc-900 dark:text-white">Registry JSON</span>
            </a>
        </div>

        <!-- HTTP API (Primary) -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border-2 border-cyan-500 p-6 mb-8">
            <div class="flex items-center space-x-3 mb-4">
                <div class="p-2 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg">
                    <flux:icon.globe-alt class="w-6 h-6 text-cyan-600 dark:text-cyan-400" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">HTTP API</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300">
                        Recommended
                    </span>
                </div>
            </div>

            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                Call MCP tools from any language or platform using standard HTTP requests.
                Perfect for external integrations, webhooks, and remote agents.
            </p>

            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">1. Get your API key</h3>
            <p class="text-zinc-600 dark:text-zinc-400 mb-4 text-sm">
                Sign in to your Host UK account to create an API key from the admin dashboard.
            </p>

            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">2. Call a tool</h3>
            <pre class="bg-zinc-900 dark:bg-zinc-950 rounded-lg p-4 overflow-x-auto text-sm mb-4"><code class="text-emerald-400">curl -X POST https://mcp.host.uk.com/api/v1/mcp/tools/call \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "server": "commerce",
    "tool": "product_list",
    "arguments": { "category": "hosting" }
  }'</code></pre>

            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">3. List available tools</h3>
            <pre class="bg-zinc-900 dark:bg-zinc-950 rounded-lg p-4 overflow-x-auto text-sm"><code class="text-emerald-400">curl https://mcp.host.uk.com/api/v1/mcp/servers \
  -H "Authorization: Bearer YOUR_API_KEY"</code></pre>

            <div class="mt-6 p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">API Endpoints</h4>
                    <a href="{{ route('mcp.openapi.json') }}" target="_blank" class="text-xs text-cyan-600 hover:text-cyan-700 dark:text-cyan-400">
                        View OpenAPI Spec →
                    </a>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <code class="text-zinc-600 dark:text-zinc-400">GET /api/v1/mcp/servers</code>
                        <span class="text-zinc-500">List all servers</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <code class="text-zinc-600 dark:text-zinc-400">GET /api/v1/mcp/servers/{id}</code>
                        <span class="text-zinc-500">Server details</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <code class="text-zinc-600 dark:text-zinc-400">GET /api/v1/mcp/servers/{id}/tools</code>
                        <span class="text-zinc-500">List tools</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <code class="text-zinc-600 dark:text-zinc-400">POST /api/v1/mcp/tools/call</code>
                        <span class="text-zinc-500">Execute a tool</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <code class="text-zinc-600 dark:text-zinc-400">GET /api/v1/mcp/resources/{uri}</code>
                        <span class="text-zinc-500">Read a resource</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stdio (Secondary) -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 mb-8">
            <div class="flex items-center space-x-3 mb-4">
                <div class="p-2 bg-violet-100 dark:bg-violet-900/30 rounded-lg">
                    <flux:icon.command-line class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Stdio (Local)</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                        For local development
                    </span>
                </div>
            </div>

            <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                Direct stdio connection for Claude Code and other local AI agents.
                Ideal for OSS framework users running their own Host Hub instance.
            </p>

            <details class="group">
                <summary class="cursor-pointer text-sm font-medium text-cyan-600 dark:text-cyan-400 hover:text-cyan-700">
                    Show stdio configuration
                </summary>

                <div class="mt-4 space-y-6">
                    <!-- Claude Code -->
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Claude Code</h3>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-2">
                            Add to <code class="px-1 py-0.5 bg-zinc-100 dark:bg-zinc-700 rounded">~/.claude/claude_code_config.json</code>:
                        </p>
                        <pre class="bg-zinc-100 dark:bg-zinc-900 rounded-lg p-4 overflow-x-auto text-sm"><code class="text-zinc-800 dark:text-zinc-200">{
  "mcpServers": {
@foreach($servers as $server)
    "{{ $server['id'] }}": {
      "command": "{{ $server['connection']['command'] ?? 'php' }}",
      "args": {!! json_encode($server['connection']['args'] ?? ['artisan', 'mcp:agent-server']) !!},
      "cwd": "{{ $server['connection']['cwd'] ?? '/path/to/host.uk.com' }}"
    }{{ !$loop->last ? ',' : '' }}
@endforeach
  }
}</code></pre>
                    </div>

                    <!-- Cursor -->
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Cursor</h3>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-2">
                            Add to <code class="px-1 py-0.5 bg-zinc-100 dark:bg-zinc-700 rounded">.cursor/mcp.json</code>:
                        </p>
                        <pre class="bg-zinc-100 dark:bg-zinc-900 rounded-lg p-4 overflow-x-auto text-sm"><code class="text-zinc-800 dark:text-zinc-200">{
  "mcpServers": {
@foreach($servers as $server)
    "{{ $server['id'] }}": {
      "command": "{{ $server['connection']['command'] ?? 'php' }}",
      "args": {!! json_encode($server['connection']['args'] ?? ['artisan', 'mcp:agent-server']) !!}
    }{{ !$loop->last ? ',' : '' }}
@endforeach
  }
}</code></pre>
                    </div>

                    <!-- Docker -->
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Docker</h3>
                        <pre class="bg-zinc-100 dark:bg-zinc-900 rounded-lg p-4 overflow-x-auto text-sm"><code class="text-zinc-800 dark:text-zinc-200">{
  "mcpServers": {
    "hosthub-agent": {
      "command": "docker",
      "args": ["exec", "-i", "hosthub-app", "php", "artisan", "mcp:agent-server"]
    }
  }
}</code></pre>
                    </div>
                </div>
            </details>
        </div>

        <!-- Authentication Methods -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 mb-8">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Authentication</h2>

            <div class="space-y-4">
                <div class="p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg">
                    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Authorization Header (Recommended)</h3>
                    <pre class="bg-zinc-100 dark:bg-zinc-900 rounded p-2 text-sm"><code class="text-zinc-800 dark:text-zinc-200">Authorization: Bearer hk_abc123_your_key_here</code></pre>
                </div>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg">
                    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">X-API-Key Header</h3>
                    <pre class="bg-zinc-100 dark:bg-zinc-900 rounded p-2 text-sm"><code class="text-zinc-800 dark:text-zinc-200">X-API-Key: hk_abc123_your_key_here</code></pre>
                </div>
            </div>

            <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-1">Server-scoped keys</h4>
                <p class="text-sm text-amber-700 dark:text-amber-400">
                    API keys can be restricted to specific MCP servers. If you get a 403 error,
                    check your key's server scopes in your admin dashboard.
                </p>
            </div>
        </div>

        <!-- Help -->
        <div class="text-center py-8">
            <p class="text-zinc-500 dark:text-zinc-400 mb-4">Need help setting up?</p>
            <div class="flex justify-center gap-4">
                <flux:button href="{{ route('mcp.servers.index') }}" icon="server-stack">
                    Browse Servers
                </flux:button>
                <flux:button href="https://host.uk.com/contact" variant="ghost">
                    Contact Support
                </flux:button>
            </div>
        </div>
    </div>
</x-layouts.mcp>
