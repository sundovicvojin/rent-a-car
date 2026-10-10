{{-- Forma za pretragu: GET ka /vozila, radi bez JavaScript-a --}}
@props(['locations', 'minDate', 'values' => [], 'idPrefix' => 'pretraga'])
<form action="{{ route('fleet.index') }}" method="get" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.3fr_auto] lg:items-end" novalidate>
    <div class="field">
        <label class="label" for="{{ $idPrefix }}-od">Preuzimanje <span class="req" aria-hidden="true">*</span></label>
        <input class="input num" type="date" id="{{ $idPrefix }}-od" name="od" required min="{{ $minDate }}" value="{{ old('od', $values['od'] ?? '') }}"
               @error('od') aria-invalid="true" aria-describedby="{{ $idPrefix }}-od-err" @enderror>
        @error('od')<p class="error" id="{{ $idPrefix }}-od-err">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label class="label" for="{{ $idPrefix }}-do">Vraćanje <span class="req" aria-hidden="true">*</span></label>
        <input class="input num" type="date" id="{{ $idPrefix }}-do" name="do" required min="{{ $minDate }}" value="{{ old('do', $values['do'] ?? '') }}"
               @error('do') aria-invalid="true" aria-describedby="{{ $idPrefix }}-do-err" @enderror>
        @error('do')<p class="error" id="{{ $idPrefix }}-do-err">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label class="label" for="{{ $idPrefix }}-lokacija">Lokacija preuzimanja</label>
        <select class="select" id="{{ $idPrefix }}-lokacija" name="lokacija">
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected((string) old('lokacija', $values['lokacija'] ?? '') === (string) $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Pretraži vozila <x-ico name="arrow" /></button>
</form>
