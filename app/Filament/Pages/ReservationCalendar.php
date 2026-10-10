<?php

namespace App\Filament\Pages;

use App\Models\Reservation;
use App\Models\Vehicle;
use App\Models\VehicleBlock;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/** Mesečni pregled zauzetosti svih vozila (rezervacije i blokade za servis). */
class ReservationCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Kalendar';

    protected static ?string $title = 'Kalendar rezervacija';

    protected static string|\UnitEnum|null $navigationGroup = 'Rezervacije';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'kalendar';

    protected string $view = 'filament.pages.reservation-calendar';

    public string $month = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->month = CarbonImmutable::parse($this->month.'-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = CarbonImmutable::parse($this->month.'-01')->addMonth()->format('Y-m');
    }

    public function today(): void
    {
        $this->month = now()->format('Y-m');
    }

    protected function getViewData(): array
    {
        $first = CarbonImmutable::parse($this->month.'-01');
        $last = $first->endOfMonth();

        $reservations = Reservation::query()->blocking()->with('customer')
            ->where('starts_on', '<=', $last->toDateString())->where('ends_on', '>', $first->toDateString())->get();
        $blocks = VehicleBlock::query()
            ->where('starts_on', '<=', $last->toDateString())->where('ends_on', '>', $first->toDateString())->get();

        // [vehicle_id][Y-m-d] => ['type' => ..., 'label' => ..., 'record' => ...]
        $cells = [];
        foreach ($reservations as $r) {
            for ($d = $r->starts_on->toImmutable(); $d->lt($r->ends_on); $d = $d->addDay()) {
                $cells[$r->vehicle_id][$d->toDateString()] = ['type' => $r->status->value, 'label' => $r->number.' – '.$r->customer->name, 'id' => $r->id];
            }
        }
        foreach ($blocks as $b) {
            for ($d = $b->starts_on->toImmutable(); $d->lt($b->ends_on); $d = $d->addDay()) {
                $cells[$b->vehicle_id][$d->toDateString()] = ['type' => 'block', 'label' => 'Servis'.($b->reason ? ': '.$b->reason : ''), 'id' => null];
            }
        }

        return [
            'first' => $first,
            'days' => range(1, $first->daysInMonth),
            'vehicles' => Vehicle::query()->orderBy('name')->get(['id', 'name', 'plate']),
            'cells' => $cells,
        ];
    }
}
