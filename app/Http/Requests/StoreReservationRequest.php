<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Pravila za rezervaciju. Koristi ih i Livewire čarobnjak (po koracima) i budući API.
 */
class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxDays = (int) config('rentacar.max_rental_days', 60);

        return [
            'starts_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on', 'before_or_equal:'.now()->addYears(2)->toDateString()],
            'pickup_location_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'return_location_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('status', 'active')],
            'extra_ids' => ['array'],
            'extra_ids.*' => [Rule::exists('extras', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+()\/\-\s.]{6,}$/'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'note' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
            // Honeypot
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'starts_on.required' => 'Izaberite datum preuzimanja.',
            'starts_on.date_format' => 'Datum preuzimanja nije ispravan.',
            'starts_on.after_or_equal' => 'Datum preuzimanja ne može biti u prošlosti.',
            'ends_on.required' => 'Izaberite datum vraćanja.',
            'ends_on.date_format' => 'Datum vraćanja nije ispravan.',
            'ends_on.after_or_equal' => 'Datum vraćanja ne može biti pre datuma preuzimanja.',
            'ends_on.before_or_equal' => 'Izabrani datum vraćanja je predaleko.',
            'pickup_location_id.required' => 'Izaberite lokaciju preuzimanja.',
            'pickup_location_id.exists' => 'Izabrana lokacija preuzimanja nije dostupna.',
            'return_location_id.required' => 'Izaberite lokaciju vraćanja.',
            'return_location_id.exists' => 'Izabrana lokacija vraćanja nije dostupna.',
            'vehicle_id.required' => 'Izaberite vozilo.',
            'vehicle_id.exists' => 'Izabrano vozilo nije dostupno.',
            'extra_ids.*.exists' => 'Izabrani dodatak nije dostupan.',
            'name.required' => 'Unesite ime i prezime.',
            'name.min' => 'Ime i prezime mora imati najmanje :min znaka.',
            'phone.required' => 'Unesite broj telefona.',
            'phone.regex' => 'Telefon može sadržati samo brojeve, razmake i znakove + ( ) - /.',
            'email.required' => 'Unesite mejl adresu.',
            'email.email' => 'Unesite ispravnu mejl adresu.',
            'note.max' => 'Napomena može imati najviše :max znakova.',
            'consent.accepted' => 'Za slanje rezervacije potrebna je saglasnost za obradu podataka.',
            'website.prohibited' => 'Zahtev je odbijen.',
        ];
    }
}
