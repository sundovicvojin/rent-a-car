<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Enums\ReservationStatus;
use App\Exceptions\VehicleUnavailableException;
use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Customer;
use App\Services\ReservationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    /** Rezervacija se pravi preko servisa: ista pravila dostupnosti i cene kao na sajtu. */
    protected function handleRecordCreation(array $data): Model
    {
        $customer = Customer::findOrFail($data['customer_id']);

        try {
            $reservation = app(ReservationService::class)->create([
                'vehicle_id' => $data['vehicle_id'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'pickup_location_id' => $data['pickup_location_id'],
                'return_location_id' => $data['return_location_id'],
                'extra_ids' => array_map('intval', $this->data['extra_ids'] ?? []),
                'customer' => ['name' => $customer->name, 'phone' => $customer->phone, 'email' => $customer->email],
                'status' => ReservationStatus::from($data['status']),
                'internal_note' => $data['internal_note'] ?? null,
                'consent' => false,
            ], source: 'admin', sendMail: (bool) ($this->data['send_mail'] ?? false));
        } catch (VehicleUnavailableException $e) {
            Notification::make()->danger()->title('Vozilo nije slobodno')->body($e->getMessage())->send();
            $this->halt();
        }

        $reservation->update(Arr::only($data, ['payment_method', 'payment_status']));

        return $reservation;
    }
}
