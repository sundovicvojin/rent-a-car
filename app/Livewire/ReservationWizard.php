<?php

namespace App\Livewire;

use App\Exceptions\VehicleUnavailableException;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Extra;
use App\Models\Location;
use App\Models\Vehicle;
use App\Services\AvailabilityService;
use App\Services\PriceQuote;
use App\Services\PricingService;
use App\Services\ReservationService;
use App\Support\DateRange;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Tok rezervacije: termin i lokacije → vozilo → dodaci → podaci → pregled i potvrda.
 * Sva pravila dolaze iz StoreReservationRequest, a cena i dostupnost iz servisa.
 */
class ReservationWizard extends Component
{
    public const STEPS = [1 => 'Termin i lokacije', 2 => 'Vozilo', 3 => 'Dodaci', 4 => 'Vaši podaci', 5 => 'Pregled i potvrda'];

    public int $step = 1;

    public ?string $starts_on = null;

    public ?string $ends_on = null;

    public ?int $pickup_location_id = null;

    public ?int $return_location_id = null;

    public ?int $vehicle_id = null;

    /** @var array<int, int|string> */
    public array $extra_ids = [];

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $note = '';

    public bool $consent = false;

    public string $website = '';

    public function mount(): void
    {
        $default = Location::query()->active()->orderBy('id')->value('id');
        $q = request();

        $this->starts_on = $this->validDate($q->query('od'));
        $this->ends_on = $this->validDate($q->query('do'));
        $this->pickup_location_id = (int) ($q->query('lokacija') ?: $default) ?: null;
        $this->return_location_id = (int) ($q->query('vracanje') ?: $this->pickup_location_id) ?: null;

        if ($slug = $q->query('vozilo')) {
            $this->vehicle_id = Vehicle::query()->bookable()->where('slug', $slug)->value('id');
        }

        if ($this->starts_on && $this->ends_on && $this->stepRulesPass(1)) {
            $this->step = ($this->vehicle_id && $this->vehicleIsFree()) ? 3 : 2;
        }
    }

    private function validDate(?string $value): ?string
    {
        return $value && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) ? $value : null;
    }

    /** @return array<string, mixed> */
    private function rulesFor(int $step): array
    {
        $all = (new StoreReservationRequest)->rules();
        $keys = match ($step) {
            1 => ['starts_on', 'ends_on', 'pickup_location_id', 'return_location_id'],
            2 => ['vehicle_id'],
            3 => ['extra_ids', 'extra_ids.*'],
            4 => ['name', 'phone', 'email', 'note', 'consent', 'website'],
            default => array_keys($all),
        };

        return Arr::only($all, $keys);
    }

    private function stepRulesPass(int $step): bool
    {
        return validator($this->all(), $this->rulesFor($step))->passes();
    }

    public function next(): void
    {
        $this->validate($this->rulesFor($this->step), (new StoreReservationRequest)->messages());

        if ($this->step === 1 && $this->vehicle_id && ! $this->vehicleIsFree()) {
            $this->vehicle_id = null; // izabrano vozilo nije slobodno u novom terminu
        }
        if ($this->step === 2 && ! $this->vehicleIsFree()) {
            $this->addError('vehicle_id', 'Izabrano vozilo nije slobodno u ovom terminu. Izaberite drugo.');

            return;
        }

        $this->step = min(5, $this->step + 1);
        $this->dispatch('step-changed');
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->resetErrorBag();
        $this->dispatch('step-changed');
    }

    public function goTo(int $step): void
    {
        if ($step < $this->step && $step >= 1) {
            $this->step = $step;
            $this->resetErrorBag();
            $this->dispatch('step-changed');
        }
    }

    public function selectVehicle(int $id): void
    {
        $this->vehicle_id = $id;
        $this->resetErrorBag('vehicle_id');
    }

    public function submit(): void
    {
        $key = 'reserve:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('consent', 'Previše pokušaja. Pokušajte ponovo za nekoliko minuta ili nas pozovite.');

            return;
        }
        RateLimiter::hit($key, 600);

        $this->validate($this->rulesFor(5), (new StoreReservationRequest)->messages());

        try {
            $reservation = app(ReservationService::class)->create([
                'vehicle_id' => $this->vehicle_id,
                'starts_on' => $this->starts_on,
                'ends_on' => $this->ends_on,
                'pickup_location_id' => $this->pickup_location_id,
                'return_location_id' => $this->return_location_id,
                'extra_ids' => array_map('intval', $this->extra_ids),
                'customer' => ['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email],
                'note' => $this->note ?: null,
                'consent' => $this->consent,
            ]);
        } catch (VehicleUnavailableException $e) {
            $this->step = 2;
            $this->vehicle_id = null;
            $this->addError('vehicle_id', $e->getMessage().' Izaberite drugo vozilo.');

            return;
        }

        $this->redirect(URL::signedRoute('reservation.confirmation', ['reservation' => $reservation->number]));
    }

    #[Computed]
    public function range(): ?DateRange
    {
        if (! $this->starts_on || ! $this->ends_on) {
            return null;
        }
        try {
            return new DateRange($this->starts_on, $this->ends_on);
        } catch (\Throwable) {
            return null;
        }
    }

    private function vehicleIsFree(): bool
    {
        $vehicle = $this->vehicle_id ? Vehicle::query()->find($this->vehicle_id) : null;

        return $vehicle && $this->range && app(AvailabilityService::class)->isAvailable($vehicle, $this->range);
    }

    #[Computed]
    public function vehicles()
    {
        if (! $this->range) {
            return collect();
        }
        $pricing = app(PricingService::class);

        return app(AvailabilityService::class)->availableVehicles($this->range)
            ->with(['category', 'primaryImage'])->orderBy('daily_price')->get()
            ->each(function ($v) use ($pricing) {
                $quote = $pricing->quote($v, $this->range);
                $v->setAttribute('period_total', $quote->rentalTotal - $quote->discountTotal);
            });
    }

    #[Computed]
    public function vehicle(): ?Vehicle
    {
        return $this->vehicle_id ? Vehicle::query()->with(['category', 'primaryImage'])->find($this->vehicle_id) : null;
    }

    #[Computed]
    public function locations()
    {
        return Location::query()->active()->orderBy('id')->get();
    }

    #[Computed]
    public function extras()
    {
        return Extra::query()->active()->orderBy('id')->get();
    }

    #[Computed]
    public function quote(): ?PriceQuote
    {
        if (! $this->range || ! $this->vehicle) {
            return null;
        }

        return app(PricingService::class)->quote(
            $this->vehicle,
            $this->range,
            $this->locations->firstWhere('id', $this->pickup_location_id),
            $this->locations->firstWhere('id', $this->return_location_id),
            array_map('intval', $this->extra_ids),
        );
    }

    public function render()
    {
        return view('livewire.reservation-wizard', ['steps' => self::STEPS, 'minDate' => now()->toDateString()]);
    }
}
