<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_verification_accepts_correct_token(): void
    {
        config(['prospector' => config('prospector')]);
        putenv('WHATSAPP_VERIFY_TOKEN=segredo');
        // O helper env() lê do ambiente putenv neste processo.
        $response = $this->getJson(route('webhooks.meta.verify', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'segredo',
            'hub_challenge' => '123456',
        ]));

        $response->assertOk()->assertSee('123456');
        putenv('WHATSAPP_VERIFY_TOKEN');
    }

    public function test_webhook_verification_rejects_wrong_token(): void
    {
        putenv('WHATSAPP_VERIFY_TOKEN=segredo');
        $this->getJson(route('webhooks.meta.verify', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'errado',
            'hub_challenge' => '123456',
        ]))->assertForbidden();
        putenv('WHATSAPP_VERIFY_TOKEN');
    }

    public function test_post_without_valid_signature_is_rejected(): void
    {
        putenv('WHATSAPP_APP_SECRET=segredo');
        $this->postJson(route('webhooks.meta.handle'), ['entry' => []])
            ->assertForbidden();
        putenv('WHATSAPP_APP_SECRET');
    }

    public function test_post_with_valid_signature_is_accepted(): void
    {
        putenv('WHATSAPP_APP_SECRET=segredo');
        $body = json_encode(['entry' => []], JSON_THROW_ON_ERROR);
        $signature = 'sha256=' . hash_hmac('sha256', $body, 'segredo');

        $this->call('POST', route('webhooks.meta.handle'), [], [], [], [
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();
        putenv('WHATSAPP_APP_SECRET');
    }
}
