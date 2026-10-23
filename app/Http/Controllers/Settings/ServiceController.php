<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('settings.services.index', ['services' => Service::orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        return view('settings.services.create', ['service' => new Service()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $this->validateData($request);
        $service = Service::create($valid);
        AuditLogger::record('service.created', $service);

        return redirect()->route('services.index')->with('success', 'Serviço cadastrado.');
    }

    public function edit(Service $service): View
    {
        return view('settings.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validateData($request, $service));
        AuditLogger::record('service.updated', $service);

        return back()->with('success', 'Preço e dados do serviço atualizados.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:services,code' . ($service ? ",{$service->id}" : '')],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
