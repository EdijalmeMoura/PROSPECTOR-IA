<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Csv\LeadCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CsvImportController extends Controller
{
    public function __construct(private readonly LeadCsvImporter $importer) {}

    public function create(): View
    {
        return view('csv.create');
    }

    public function preview(Request $request): RedirectResponse|View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ], ['file.mimes' => 'Envie um arquivo CSV válido.'], ['file' => 'arquivo']);

        $path = $request->file('file')->store('csv-imports');
        $absolute = Storage::path($path);
        $report = $this->importer->preview($absolute);

        if ($report->hasStructureError()) {
            Storage::delete($path);

            return back()->withErrors(['file' => 'Colunas obrigatórias ausentes: ' . implode(', ', $report->missingColumns) . '. Baixe o modelo.']);
        }

        return view('csv.preview', ['report' => $report, 'path' => $path]);
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate(['path' => ['required', 'string', 'starts_with:csv-imports/']]);
        $absolute = Storage::path($valid['path']);

        if (! is_file($absolute)) {
            return redirect()->route('csv.create')->withErrors(['file' => 'Arquivo de importação expirado. Envie novamente.']);
        }

        $report = $this->importer->import($absolute, $request->user());
        Storage::delete($valid['path']);

        return redirect()->route('leads.index')
            ->with('success', "Importação concluída: {$report->imported} importados, {$report->duplicates} duplicados, {$report->rejected} rejeitados.");
    }
}
