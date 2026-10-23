<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaWebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * O Dotenv usa um repositório imutável: quando a variável já existe no
     * .env (ainda que vazia), o putenv() sozinho não a sobrescreve.
     * Remover primeiro de $_ENV/$_SERVER faz o env() cair no adapter getenv().
     */
    private function setEnv(string $name, string $value): void
    {
        unset($_ENV[$name], $_SERVER[$name]);
        putenv("$name=$value");
    }

    private function clearEnv(string $name): void
    {
        unset($_ENV[$name], $_SERVER[$name]);
        putenv($name);
    }

    public function test_webhook_verification_accepts_correct_token(): void
    {
        $this->setEnv('WHATSAPP_VERIFY_TOKEN', 'segredo');

        $response = $this->getJson(route('webhooks.meta.verify', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'segredo',
            'hub_challenge' => '123456',
        ]));

        $response->assertOk()->assertSee('123456');
        $this->clearEnv('WHATSAPP_VERIFY_TOKEN');
    }

    public function test_webhook_verification_rejects_wrong_token(): void
    {
        $this->setEnv('WHATSAPP_VERIFY_TOKEN', 'segredo');
        $this->getJson(route('webhooks.meta.verify', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'errado',
            'hub_challenge' => '123456',
        ]))->assertForbidden();
        $this->clearEnv('WHATSAPP_VERIFY_TOKEN');
    }

    public function test_post_without_valid_signature_is_rejected(): void
    {
        $this->setEnv('WHATSAPP_APP_SECRET', 'segredo');
        $this->postJson(route('webhooks.meta.handle'), ['entry' => []])
            ->assertForbidden();
        $this->clearEnv('WHATSAPP_APP_SECRET');
    }

    public function test_post_with_valid_signature_is_accepted(): void
    {
        $this->setEnv('WHATSAPP_APP_SECRET', 'segredo');
        $body = json_encode(['entry' => []], JSON_THROW_ON_ERROR);
        $signature = 'sha256=' . hash_hmac('sha256', $body, 'segredo');

        $this->call('POST', route('webhooks.meta.handle'), [], [], [], [
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();
        $this->clearEnv('WHATSAPP_APP_SECRET');
    }
}
