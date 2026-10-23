<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiManager;
use App\Services\Communication\CommunicationService;
use App\Services\Search\SearchProviderManager;
use Illuminate\View\View;

class ConnectorController extends Controller
{
    public function show(
        SearchProviderManager $search,
        AiManager $ai,
        CommunicationService $communication,
    ): View {
        $searchProvider = $search->make();
        $aiProvider = $ai->make();

        $channels = [
            'email' => app(\App\Services\Communication\EmailAdapter::class),
            'whatsapp' => app(\App\Services\Communication\WhatsAppAdapter::class),
            'instagram' => app(\App\Services\Communication\InstagramAdapter::class),
        ];

        return view('settings.connectors', compact('searchProvider', 'aiProvider', 'channels'));
    }
}
