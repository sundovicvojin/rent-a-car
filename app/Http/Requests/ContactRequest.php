<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\/\-\s.]{6,}$/'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'consent' => ['accepted'],
            // Honeypot: ljudi ga ne vide, botovi ga popunjavaju.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Unesite vaše ime i prezime.',
            'email.required' => 'Unesite mejl adresu.',
            'email.email' => 'Unesite ispravnu mejl adresu.',
            'phone.regex' => 'Telefon može sadržati samo brojeve, razmake i znakove + ( ) - /.',
            'message.required' => 'Napišite poruku.',
            'message.min' => 'Poruka mora imati najmanje :min znakova.',
            'consent.accepted' => 'Potrebna je saglasnost za obradu podataka da bismo vam odgovorili.',
            'website.prohibited' => 'Poruka je odbijena.',
        ];
    }
}
