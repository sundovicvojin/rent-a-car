<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Vehicle;
use App\Models\VehicleBlock;
use App\Support\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Vozilo je zauzeto ako se period preklapa sa rezervacijom koja nije otkazana
 * ili sa blokadom za servis. Kraj perioda je isključiv (isti dan vraćanja i preuzimanja je moguć).
 */
class AvailabilityService
{
    public function isAvailable(Vehicle $vehicle, DateRange $range, ?int $ignoreReservationId = null): bool
    {
        if ($vehicle->status !== \App\Enums\VehicleStatus::Active) {
            return false;
        }

        return ! $this->hasConflict($vehicle->id, $range, $ignoreReservationId);
    }

    public function hasConflict(int $vehicleId, DateRange $range, ?int $ignoreReservationId = null): bool
    {
        $start = $range->start->toDateString();
        $end = $range->end->toDateString();

        $reservation = Reservation::query()->blocking()
            ->where('vehicle_id', $vehicleId)
            ->where('starts_on', '<', $end)->where('ends_on', '>', $start)
            ->when($ignoreReservationId, fn ($q) => $q->where('id', '!=', $ignoreReservationId))
            ->exists();

        return $reservation || VehicleBlock::query()
            ->where('vehicle_id', $vehicleId)
            ->where('starts_on', '<', $end)->where('ends_on', '>', $start)
            ->exists();
    }

    /** Aktivna vozila koja su slobodna u zadatom periodu. */
    public function availableVehicles(DateRange $range): Builder
    {
        $start = $range->start->toDateString();
        $end = $range->end->toDateString();

        return Vehicle::query()->bookable()
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('reservations')
                ->whereColumn('reservations.vehicle_id', 'vehicles.id')
                ->where('reservations.status', '!=', 'cancelled')
                ->where('reservations.starts_on', '<', $end)->where('reservations.ends_on', '>', $start))
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('vehicle_blocks')
                ->whereColumn('vehicle_blocks.vehicle_id', 'vehicles.id')
                ->where('vehicle_blocks.starts_on', '<', $end)->where('vehicle_blocks.ends_on', '>', $start));
    }

    /**
     * Zauzeti periodi za kalendar na detalju vozila (od danas pa nadalje).
     *
     * @return Collection<int, array{start: string, end: string}>
     */
    public function busyPeriods(Vehicle $vehicle): Collection
    {
        $today = now()->toDateString();

        $reservations = Reservation::query()->blocking()->where('vehicle_id', $vehicle->id)
            ->where('ends_on', '>', $today)->get(['starts_on', 'ends_on']);
        $blocks = VehicleBlock::query()->where('vehicle_id', $vehicle->id)
            ->where('ends_on', '>', $today)->get(['starts_on', 'ends_on']);

        return $reservations->concat($blocks)
            ->map(fn ($p) => ['start' => $p->starts_on->toDateString(), 'end' => $p->ends_on->toDateString()])
            ->sortBy('start')->values();
    }
}
