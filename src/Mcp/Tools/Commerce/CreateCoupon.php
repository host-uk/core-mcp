<?php

declare(strict_types=1);

namespace Core\Mcp\Tools\Commerce;

use Core\Mod\Commerce\Models\Coupon;
use Core\Mcp\Tools\Concerns\RequiresWorkspaceContext;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateCoupon extends Tool
{
    use RequiresWorkspaceContext;

    protected string $description = 'Create a new discount coupon code';

    public function handle(Request $request): Response
    {
        // Ensure workspace context and authorization
        $workspace = $this->getWorkspace();
        $user = auth()->user();

        // Verify the caller has permission (admin role check)
        $isHades = method_exists($user, 'isHades') && $user->isHades();
        $isWorkspaceAdmin = $user && $workspace->users()
            ->where('user_id', $user->id)
            ->whereIn('role', ['admin', 'owner'])
            ->exists();

        // If authenticated via API key, we trust the key has proper workspace access
        // but we still want to ensure it's not a restricted key if possible.
        if (! $isHades && ! $isWorkspaceAdmin && ! $request->attributes->has('api_key')) {
            return Response::text(json_encode([
                'error' => 'Unauthorized. Admin permissions required to create coupons.',
            ]));
        }

        $code = strtoupper($request->input('code'));
        $name = $request->input('name');
        $type = $request->input('type', 'percentage');
        $value = $request->input('value');
        $duration = $request->input('duration', 'once');
        $maxUses = $request->input('max_uses');
        $validUntil = $request->input('valid_until');

        // Validate code format
        if (! preg_match('/^[A-Z0-9_-]+$/', $code)) {
            return Response::text(json_encode([
                'error' => 'Invalid code format. Use only uppercase letters, numbers, hyphens, and underscores.',
            ]));
        }

        // Check for existing code (workspace-scoped)
        if (Coupon::where('code', $code)->where('workspace_id', $workspace->id)->exists()) {
            return Response::text(json_encode([
                'error' => 'A coupon with this code already exists in this workspace.',
            ]));
        }

        // Validate type
        if (! in_array($type, ['percentage', 'fixed_amount'])) {
            return Response::text(json_encode([
                'error' => 'Invalid type. Use percentage or fixed_amount.',
            ]));
        }

        // Validate value
        if ($type === 'percentage' && ($value < 1 || $value > 100)) {
            return Response::text(json_encode([
                'error' => 'Percentage value must be between 1 and 100.',
            ]));
        }

        try {
            $coupon = Coupon::create([
                'workspace_id' => $workspace->id,
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'value' => $value,
                'duration' => $duration,
                'max_uses' => $maxUses,
                'max_uses_per_workspace' => 1,
                'valid_until' => $validUntil ? \Carbon\Carbon::parse($validUntil) : null,
                'is_active' => true,
                'applies_to' => 'all',
            ]);

            return Response::text(json_encode([
                'success' => true,
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'type' => $coupon->type,
                    'value' => (float) $coupon->value,
                    'duration' => $coupon->duration,
                    'max_uses' => $coupon->max_uses,
                    'valid_until' => $coupon->valid_until?->toDateString(),
                    'is_active' => $coupon->is_active,
                ],
            ], JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            return Response::text(json_encode([
                'error' => 'Failed to create coupon: '.$e->getMessage(),
            ]));
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'code' => $schema->string('Unique coupon code (uppercase letters, numbers, hyphens, underscores)')->required(),
            'name' => $schema->string('Display name for the coupon')->required(),
            'type' => $schema->string('Discount type: percentage or fixed_amount (default: percentage)'),
            'value' => $schema->number('Discount value (percentage 1-100 or fixed amount)')->required(),
            'duration' => $schema->string('How long discount applies: once, repeating, or forever (default: once)'),
            'max_uses' => $schema->integer('Maximum total uses (null for unlimited)'),
            'valid_until' => $schema->string('Expiry date in YYYY-MM-DD format'),
        ];
    }
}
