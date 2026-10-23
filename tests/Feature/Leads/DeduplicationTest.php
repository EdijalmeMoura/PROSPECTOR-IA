<?php

declare(strict_types=1);

namespace Tests\Feature\Leads;

use App\Models\Company;
use App\Models\User;
use App\Services\Leads\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeduplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_same_place_id_does_not_duplicate_company(): void
    {
        $service = app(LeadService::class);
        $service->create([
            'company_name' => 'Empresa Original', 'city' => 'Recife', 'place_id' => 'PLACE_123',
        ], [], [], $this->user, 'google_places');

        $second = $service->create([
            'company_name' => 'Empresa Nome Diferente', 'city' => 'Recife', 'place_id' => 'PLACE_123',
        ], [], [], $this->user, 'google_places');

        $this->assertSame(1, Company::count());
        $this->assertSame($second->company_id, Company::first()->id);
    }

    public function test_same_normalized_domain_does_not_duplicate_company(): void
    {
        $service = app(LeadService::class);
        $service->create([
            'company_name' => 'Loja X', 'city' => 'Recife', 'website' => 'https://www.loja-x.com.br',
        ], [], [], $this->user);
        $service->create([
            'company_name' => 'Loja X Filial', 'city' => 'Olinda', 'website' => 'http://loja-x.com.br',
        ], [], [], $this->user);

        $this->assertSame(1, Company::count());
        $this->assertSame('loja-x.com.br', Company::first()->normalized_domain);
    }

    public function test_similar_names_are_not_treated_as_duplicates(): void
    {
        $service = app(LeadService::class);
        $service->create(['company_name' => 'Bar do João', 'city' => 'Recife'], [], [], $this->user);
        $service->create(['company_name' => 'Bar do Joãozinho', 'city' => 'Recife'], [], [], $this->user);

        $this->assertSame(2, Company::count());
    }

    public function test_company_without_website_or_place_id_is_always_created(): void
    {
        $service = app(LeadService::class);
        $service->create(['company_name' => 'Ambulante A', 'city' => 'Recife'], [], [], $this->user);
        $service->create(['company_name' => 'Ambulante B', 'city' => 'Recife'], [], [], $this->user);

        $this->assertSame(2, Company::count());
    }
}
