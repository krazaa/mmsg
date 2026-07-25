<?php

namespace App\Data;

final readonly class ActivityLogFilters
{
    public function __construct(
        public ?string $search = null,
        public ?string $logName = null,
        public ?string $event = null,
    ) {}
}
