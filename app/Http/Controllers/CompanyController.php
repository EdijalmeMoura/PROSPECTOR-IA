<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use App\Models\CompanySource;
use App\Services\Dedup\CompanyDeduplicator;
use App\Support\AuditLogger;
use App\Support\DomainNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $companies = Company::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->string('city')))
            ->when($request->string('scope')->value() === 'demo', fn ($q) => $q->demo(), fn ($q) => $q->real())
            ->withCount('leads')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('companies.index', [
            'companies' => $companies,
            'cities' => config('prospector.region.cities'),
        ]);
    }

    public function create(): View
    {
        return view('companies.create', [
            'cities' => config('prospector.region.cities'),
            'segments' => config('prospector.segments'),
            'legalBases' => config('prospector.legal_bases'),
        ]);
    }

    public function store(StoreCompanyRequest $request, CompanyDeduplicator $deduplicator): RedirectResponse
    {
        $data = $request->validated();

        $existing = $deduplicator->findExisting($data['place_id'] ?? null, $data['website'] ?? null);
        if ($existing) {
            return redirect()->route('companies.show', $existing)
                ->with('warning', 'Empresa já existente (identificador/domínio confiável). Nenhuma duplicidade foi criada.');
        }

        $company = Company::create([
            ...$data,
            'state' => $data['state'] ?? config('prospector.region.state'),
            'normalized_domain' => DomainNormalizer::normalize($data['website'] ?? null),
            'last_verified_at' => now(),
        ]);

        CompanySource::create([
            'company_id' => $company->id,
            'source_name' => 'manual',
            'source_url' => $data['website'] ?? null,
            'external_id' => 'manual:' . md5($company->id . '|' . $company->name),
            'retention_condition' => 'permanente',
            'collected_at' => now(),
            'verified_at' => now(),
            'is_primary' => true,
        ]);

        AuditLogger::record('company.created', $company);

        return redirect()->route('companies.show', $company)->with('success', 'Empresa cadastrada.');
    }

    public function show(Company $company): View
    {
        $company->load(['sources', 'leads.company', 'leads.service']);

        return view('companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('companies.edit', [
            'company' => $company,
            'cities' => config('prospector.region.cities'),
            'segments' => config('prospector.segments'),
            'legalBases' => config('prospector.legal_bases'),
        ]);
    }

    public function update(StoreCompanyRequest $request, Company $company): RedirectResponse
    {
        $data = $request->validated();
        $data['normalized_domain'] = DomainNormalizer::normalize($data['website'] ?? null);
        $company->update($data);
        AuditLogger::record('company.updated', $company, $request->safe()->all());

        return redirect()->route('companies.show', $company)->with('success', 'Empresa atualizada.');
    }
}
