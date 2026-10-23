<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Models\Approval;
use App\Models\Lead;

class OutboundMessage
{
    public function __construct(
        public readonly Lead $lead,
        public readonly Approval $approval,
        public readonly string $channel,
        public readonly string $recipient,
        public readonly string $text,
    ) {}
}
