<?php

namespace App\Contracts;

use App\Data\ActivityLogFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ActivityLogReadRepository
{
    public function paginate(ActivityLogFilters $filters, int $perPage = 30): LengthAwarePaginator;

    public function logNames(): Collection;

    /**
     * @return array{total: int, today: int, created: int, updated: int, deleted: int}
     */
    public function summary(): array;
}
