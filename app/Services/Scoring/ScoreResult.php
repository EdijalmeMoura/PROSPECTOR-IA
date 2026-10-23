<?php

declare(strict_types=1);

namespace App\Services\Scoring;

class ScoreResult
{
    /**
     * @param  array<string, array{points:int, max:int, reason:string}>  $criteria
     */
    public function __construct(
        public readonly int $total,
        public readonly array $criteria,
        public readonly string $priority,
        public readonly string $qualificationStatus,
        public readonly string $summary,
    ) {}
}
