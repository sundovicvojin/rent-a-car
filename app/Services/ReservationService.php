<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Exceptions\VehicleUnavailableException;
use App\Mail\ReservationConfirmation;
use App\Mail\ReservationReceived;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Support\DateRange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Pravljenje rezervacije: jedina tačka koja upisuje rezervaciju, koriste je sajt, admin panel
 * i (u fazi 2) mobilna aplikacija. Cena se uvek računa ovde, nikad se ne uzima od klijenta.
 */
class ReservationService
{
    public function __construct(
        private AvailabilityService $availability,
        private PricingService $pricing,
    ) {}

    /**
     * @param  array{vehicle_id:int, starts_on:string, ends_on:string, pickup_location_id:int, return_location_id:int, extra_ids?:array<int,int>, customer:array{name:string,phone:string,email:string}, note?:?string, consent?:bool, status?:ReservationStatus, internal_note?:?string}  $data
     *
     * @throws VehicleUnavailableException
     */
    public function create(array $data, string $source = 'web', bool $sendMail = true): Reservation
    {
        $range = new DateRange($data['starts_on'], $data['ends_on']);
        $pickup = Location::query()->findOrFail($data['pickup_location_id']);
        $return = Location::query()->findOrFail($data['return_location_id']);

        $reservation = DB::transaction(function () use ($data, $range, $pickup, $return, $source) {
            // Zaključavanje reda vozila: istovremeni zahtevi za isto vozilo idu jedan za drugim.
            $vehicle = Vehicle::query()->whereKey($data['vehicle_id'])->lockForUpdate()->firstOrFail();
            if (DB::connection()->getDriverName() === 'sqlite') {
                // SQLite ne podržava lockForUpdate: probni upis uzima write lock na bazi.
                DB::table('vehicles')->where('id', $vehicle->id)->update(['updated_at' => $vehicle->updated_at]);
            }

            if ($source === 'web' && ! $this->availability->isAvailable($vehicle, $range)) {
                throw new VehicleUnavailableException;
            }
            if ($this->availability->hasConflict($vehicle->id, $range)) {
                throw new VehicleUnavailableException;
            }

            $quote = $this->pricing->quote($vehicle, $range, $pickup, $return, $data['extra_ids'] ?? []);

            $customer = Customer::query()->firstOrNew(['email' => mb_strtolower(trim($data['customer']['email']))]);
            $customer->fill([
                'name' => $data['customer']['name'],
                'phone' => $data['customer']['phone'],
            ])->save();

            $reservation = Reservation::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'pickup_location_id' => $pickup->id,
                'return_location_id' => $return->id,
                'starts_on' => $range->start,
                'ends_on' => $range->end,
                'days' => $quote->days,
                'status' => $data['status'] ?? ReservationStatus::Pending,
                'rental_total' => $quote->rentalTotal,
                'discount_total' => $quote->discountTotal,
                'delivery_total' => $quote->deliveryTotal(),
                'extras_total' => $quote->extrasTotal,
                'total' => $quote->total(),
                'deposit' => $quote->deposit,
                'price_breakdown' => $quote->toArray(),
                'customer_note' => $data['note'] ?? null,
                'internal_note' => $data['internal_note'] ?? null,
                'payment_method' => PaymentMethod::PayOnPickup,
                'payment_status' => PaymentStatus::Unpaid,
                'source' => $source,
                'consent_at' => ($data['consent'] ?? false) ? now() : null,
            ]);

            foreach ($quote->extras as $extra) {
                $reservation->extras()->create($extra);
            }

            return $reservation;
        });

        if ($sendMail) {
            $this->notify($reservation);
        }

        return $reservation;
    }

    /** Mejl klijentu i firmi; greška slanja ne sme da poništi rezervaciju. */
    public function notify(Reservation $reservation): void
    {
        $reservation->loadMissing(['customer', 'vehicle', 'pickupLocation', 'returnLocation', 'extras']);

        try {
            Mail::to($reservation->customer->email)->send(new ReservationConfirmation($reservation));
            if ($to = CompanySetting::current()->email) {
                Mail::to($to)->send(new ReservationReceived($reservation));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
