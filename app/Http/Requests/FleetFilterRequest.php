<?php

namespace App\Http\Requests;

use App\Enums\Fuel;
use App\Enums\Transmission;
use App\Services\VehicleCatalog;
use App\Support\DateRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FleetFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'od' => ['nullable', 'date_format:Y-m-d', 'required_with:do', 'after_or_equal:today'],
            'do' => ['nullable', 'date_format:Y-m-d', 'required_with:od', 'after_or_equal:od'],
            'lokacija' => ['nullable', 'integer', 'exists:locations,id'],
            'vracanje' => ['nullable', 'integer', 'exists:locations,id'],
            'kategorija' => ['nullable', 'string', 'max:80'],
            'menjac' => ['nullable', Rule::enum(Transmission::class)],
            'gorivo' => ['nullable', Rule::enum(Fuel::class)],
            'sedista' => ['nullable', 'integer', 'min:1', 'max:20'],
            'sort' => ['nullable', Rule::in(array_keys(VehicleCatalog::SORTS))],
        ];
    }

    public function messages(): array
    {
        return [
            'od.after_or_equal' => 'Datum preuzimanja ne može biti u prošlosti.',
            'od.required_with' => 'Unesite datum preuzimanja.',
            'do.required_with' => 'Unesite datum vraćanja.',
            'do.after_or_equal' => 'Datum vraćanja ne može biti pre datuma preuzimanja.',
            '*.date_format' => 'Datum nije u ispravnom formatu.',
        ];
    }

    public function dateRange(): ?DateRange
    {
        $v = $this->validated();

        return ! empty($v['od']) && ! empty($v['do']) ? new DateRange($v['od'], $v['do']) : null;
    }

    /** GET forma: pri grešci ostajemo na istoj stranici bez parametara, sa porukama. */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new HttpResponseException(
            redirect()->route('fleet.index')->withErrors($validator)->withInput($this->except('page'))
        );
    }
}
