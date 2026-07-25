<?php

namespace App\Repositories;

use App\Contracts\ActivityLogReadRepository;
use App\Data\ActivityLogFilters;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

final class EloquentActivityLogReadRepository implements ActivityLogReadRepository
{
    public function paginate(ActivityLogFilters $filters, int $perPage = 30): LengthAwarePaginator
    {
        return Activity::query()
            ->with('causer')
            ->when($filters->event, fn (Builder $query, string $event) => $query->where('event', $event))
            ->when($filters->logName, fn (Builder $query, string $logName) => $query->where('log_name', $logName))
            ->when($filters->search, fn (Builder $query, string $search) => $this->applySearch($query, $search))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function logNames(): Collection
    {
        return Activity::query()
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');
    }

    public function summary(): array
    {
        $counts = Activity::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today', [today()->toDateString()])
            ->selectRaw("SUM(CASE WHEN event = 'created' THEN 1 ELSE 0 END) as created")
            ->selectRaw("SUM(CASE WHEN event = 'updated' THEN 1 ELSE 0 END) as updated")
            ->selectRaw("SUM(CASE WHEN event = 'deleted' THEN 1 ELSE 0 END) as deleted")
            ->first();

        return [
            'total' => (int) $counts->total,
            'today' => (int) $counts->today,
            'created' => (int) $counts->created,
            'updated' => (int) $counts->updated,
            'deleted' => (int) $counts->deleted,
        ];
    }

    private function applySearch(Builder $query, string $search): void
    {
        $term = '%'.$search.'%';

        $query->where(fn (Builder $inner) => $inner
            ->where('description', 'like', $term)
            ->orWhereHasMorph('causer', [User::class], fn (Builder $causer) => $causer
                ->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)));
    }
}
