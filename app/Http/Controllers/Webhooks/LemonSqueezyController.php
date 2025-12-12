<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\User;
use App\Notifications\LicenseKeyNotification;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LemonSqueezyController extends Controller
{
    public function __construct(
        private LicenseService $licenseService
    ) {}

    /**
     * Handle incoming webhooks from LemonSqueezy.
     *
     * POST /webhooks/lemonsqueezy
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify webhook signature
        $signature = $request->header('X-Signature');
        $secret = config('services.lemonsqueezy.webhook_secret');

        if (!$signature || !$secret) {
            Log::warning('LemonSqueezy webhook: Missing signature or secret');
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $computed = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($signature, $computed)) {
            Log::warning('LemonSqueezy webhook: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->input('meta.event_name');
        $data = $request->input('data');

        Log::info('LemonSqueezy webhook received', [
            'event' => $event,
            'data_id' => $data['id'] ?? null,
        ]);

        return match ($event) {
            'order_created' => $this->handleOrderCreated($data),
            'order_refunded' => $this->handleOrderRefunded($data),
            'subscription_created' => $this->handleSubscriptionCreated($data),
            'subscription_updated' => $this->handleSubscriptionUpdated($data),
            'subscription_cancelled' => $this->handleSubscriptionCancelled($data),
            'subscription_resumed' => $this->handleSubscriptionResumed($data),
            'subscription_expired' => $this->handleSubscriptionExpired($data),
            'subscription_paused' => $this->handleSubscriptionPaused($data),
            'subscription_unpaused' => $this->handleSubscriptionUnpaused($data),
            'subscription_payment_success' => $this->handlePaymentSuccess($data),
            'subscription_payment_failed' => $this->handlePaymentFailed($data),
            'license_key_created' => $this->handleLicenseKeyCreated($data),
            default => response()->json(['received' => true]),
        };
    }

    /**
     * Handle order_created event.
     */
    private function handleOrderCreated(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $email = $attributes['user_email'] ?? null;
        $name = $attributes['user_name'] ?? null;

        if (!$email) {
            Log::error('LemonSqueezy order_created: Missing user email');
            return response()->json(['error' => 'Missing user email'], 400);
        }

        // Get the variant ID to determine the plan
        $variantId = $attributes['first_order_item']['variant_id'] ?? null;
        $plan = $this->getPlanFromVariant($variantId);

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name ?? explode('@', $email)[0], 'password' => bcrypt(str()->random(32))]
        );

        // Generate license
        $license = $this->licenseService->generate($user, $plan);

        // Store LemonSqueezy metadata
        $license->update([
            'metadata' => [
                'lemonsqueezy_order_id' => $data['id'],
                'lemonsqueezy_customer_id' => $attributes['customer_id'] ?? null,
                'lemonsqueezy_variant_id' => $variantId,
            ],
        ]);

        // Send license key notification
        try {
            $user->notify(new LicenseKeyNotification($license, $this->licenseService->getPlainKey()));
        } catch (\Exception $e) {
            Log::error('Failed to send license notification', ['error' => $e->getMessage()]);
        }

        Log::info('LemonSqueezy order processed', [
            'user_id' => $user->id,
            'license_id' => $license->id,
            'plan' => $plan,
        ]);

        return response()->json(['success' => true, 'license_id' => $license->id]);
    }

    /**
     * Handle order_refunded event.
     */
    private function handleOrderRefunded(array $data): JsonResponse
    {
        $orderId = $data['id'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_order_id', $orderId)->first();

        if ($license) {
            $license->update(['status' => 'revoked']);
            Log::info('License revoked due to refund', ['license_id' => $license->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription_created event.
     */
    private function handleSubscriptionCreated(array $data): JsonResponse
    {
        // Similar to order_created but for subscription
        return $this->handleOrderCreated($data);
    }

    /**
     * Handle subscription_updated event.
     */
    private function handleSubscriptionUpdated(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;
        $variantId = $attributes['variant_id'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_customer_id', $customerId)->first();

        if ($license && $variantId) {
            $newPlan = $this->getPlanFromVariant($variantId);
            $oldPlan = $license->plan;

            if ($newPlan !== $oldPlan) {
                $license->update([
                    'plan' => $newPlan,
                    'activations_limit' => $this->getActivationLimit($newPlan),
                ]);

                Log::info('License plan updated', [
                    'license_id' => $license->id,
                    'old_plan' => $oldPlan,
                    'new_plan' => $newPlan,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription_cancelled event.
     */
    private function handleSubscriptionCancelled(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;
        $endsAt = $attributes['ends_at'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_customer_id', $customerId)->first();

        if ($license) {
            $license->update([
                'status' => 'cancelled',
                'expires_at' => $endsAt ? \Carbon\Carbon::parse($endsAt) : now()->addMonth(),
            ]);

            Log::info('License cancelled', ['license_id' => $license->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription_resumed event.
     */
    private function handleSubscriptionResumed(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_customer_id', $customerId)->first();

        if ($license) {
            $license->update([
                'status' => 'active',
                'expires_at' => null,
            ]);

            Log::info('License resumed', ['license_id' => $license->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription_expired event.
     */
    private function handleSubscriptionExpired(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_customer_id', $customerId)->first();

        if ($license) {
            $license->update(['status' => 'expired']);
            Log::info('License expired', ['license_id' => $license->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription_paused event.
     */
    private function handleSubscriptionPaused(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_customer_id', $customerId)->first();

        if ($license) {
            $license->update(['status' => 'paused']);
            Log::info('License paused', ['license_id' => $license->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription_unpaused event.
     */
    private function handleSubscriptionUnpaused(array $data): JsonResponse
    {
        return $this->handleSubscriptionResumed($data);
    }

    /**
     * Handle subscription_payment_success event.
     */
    private function handlePaymentSuccess(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_customer_id', $customerId)->first();

        if ($license) {
            // Extend license by one billing period (monthly)
            $license->update([
                'status' => 'active',
                'expires_at' => now()->addMonth(),
            ]);

            Log::info('License renewed', ['license_id' => $license->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle subscription_payment_failed event.
     */
    private function handlePaymentFailed(array $data): JsonResponse
    {
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;

        $license = License::whereJsonContains('metadata->lemonsqueezy_customer_id', $customerId)->first();

        if ($license) {
            $license->update(['status' => 'payment_failed']);
            Log::warning('License payment failed', ['license_id' => $license->id]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle license_key_created event (if using LemonSqueezy's license keys).
     */
    private function handleLicenseKeyCreated(array $data): JsonResponse
    {
        // This is for when you want to use LemonSqueezy's built-in license keys
        // Instead of generating your own
        Log::info('LemonSqueezy license key created', ['data' => $data]);
        return response()->json(['success' => true]);
    }

    /**
     * Get plan from variant ID.
     */
    private function getPlanFromVariant(?int $variantId): string
    {
        $variants = config('contentshield.lemonsqueezy.variants', []);

        return $variants[$variantId] ?? 'starter';
    }

    /**
     * Get activation limit for plan.
     */
    private function getActivationLimit(string $plan): int
    {
        return match ($plan) {
            'starter' => 1,
            'pro' => 5,
            'agency' => 50,
            default => 1,
        };
    }
}
