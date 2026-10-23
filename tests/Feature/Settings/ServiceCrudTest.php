<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_manage_services(): void
    {
        $operator = User::factory()->create(['role' => 'operador']);

        $this->actingAs($operator)->get(route('services.index'))->assertForbidden();
    }

    public function test_admin_can_edit_service_price(): void
    {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create(['price' => '250.00']);

        $this->actingAs($admin)->put(route('services.update', $service), [
            'code' => $service->code,
            'name' => $service->name,
            'description' => $service->description,
            'price' => '299.90',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame('299.90', $service->fresh()->price);
    }

    public function test_admin_can_create_service(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('services.store'), [
            'code' => 'NOVO_PACOTE',
            'name' => 'Pacote premium',
            'description' => 'Pacote de artes premium',
            'price' => '499.00',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('services', ['code' => 'NOVO_PACOTE', 'price' => '499.00']);
    }
}
