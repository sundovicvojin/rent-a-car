<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Support\DateRange;
use Illuminate\Database\Eloquent\Builder;

/** Pretraga i filtriranje voznog parka za javni sajt. */
class VehicleCatalog
{
    public const SORTS = ['cena_rastuce' => ['daily_price', 'asc'], 'cena_opadajuce' => ['daily_price', 'desc']];

    public function __construct(private AvailabilityService $availability) {}

    /** @param  array<string, mixed>  $filters */
    public function search(array $filters, ?DateRange $range = null): Builder
    {
        $query = $range
            ? $this->availability->availableVehicles($range)
            : Vehicle::query()->bookable();

        $query->with(['category', 'primaryImage']);

        if (! empty($filters['kategorija'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['kategorija']));
        }
        if (! empty($filters['menjac'])) {
            $query->where('transmission', $filters['menjac']);
        }
        if (! empty($filters['gorivo'])) {
            $query->where('fuel', $filters['gorivo']);
        }
        if (! empty($filters['sedista'])) {
            $query->where('seats', '>=', (int) $filters['sedista']);
        }

        [$column, $direction] = self::SORTS[$filters['sort'] ?? ''] ?? ['name', 'asc'];

        return $query->orderBy($column, $direction)->orderBy('id');
    }
}
