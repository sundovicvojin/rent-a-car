<x-layouts.app title="Kontakt" description="Kontaktirajte nas telefonom, mejlom ili preko forme. Radno vreme, adresa i način preuzimanja vozila.">
    <div class="wrap pb-6 pt-10">
        <p class="eyebrow">Kontakt</p>
        <h1 class="h2 mt-2" style="font-size: clamp(2rem, 4.5vw, 3.4rem)">Javite nam se</h1>
    </div>
    <div class="wrap grid gap-10 pb-6 lg:grid-cols-[1fr_1.3fr]">
        <div class="space-y-6">
            <dl class="card divide-y divide-line">
                @if ($company->phone)<div class="flex gap-4 p-5"><x-ico name="phone" class="mt-1 shrink-0 text-brand-text" /><div><dt class="text-sm text-ink-2">Telefon</dt><dd class="num text-lg font-semibold"><a href="tel:{{ preg_replace('/[^+\d]/', '', $company->phone) }}" class="text-ink">{{ $company->phone }}</a></dd></div></div>@endif
                @if ($company->email)<div class="flex gap-4 p-5"><x-ico name="mail" class="mt-1 shrink-0 text-brand-text" /><div><dt class="text-sm text-ink-2">Mejl</dt><dd class="text-lg font-semibold"><a href="mailto:{{ $company->email }}" class="text-ink">{{ $company->email }}</a></dd></div></div>@endif
                @if ($company->address)<div class="flex gap-4 p-5"><x-ico name="pin" class="mt-1 shrink-0 text-brand-text" /><div><dt class="text-sm text-ink-2">Adresa</dt><dd class="text-lg font-semibold">{{ $company->address }}</dd></div></div>@endif
                <div class="flex gap-4 p-5"><x-ico name="clock" class="mt-1 shrink-0 text-brand-text" /><div><dt class="text-sm text-ink-2">Radno vreme</dt><dd class="font-semibold">@foreach (preg_split('/\R/', (string) $company->working_hours) as $line){{ $line }}@if (! $loop->last)<br>@endif @endforeach</dd></div></div>
            </dl>
        </div>

        <form action="{{ route('contact.send') }}" method="post" class="card space-y-5 p-6 sm:p-8" novalidate>
            @csrf
            <h2 class="text-2xl font-bold">Pošaljite poruku</h2>
            @if (session('sent'))
                <p class="badge badge-good w-full justify-start px-4 py-3 text-base" role="status">Hvala, poruka je poslata. Javićemo vam se što pre.</p>
            @endif

            <div class="honeypot" aria-hidden="true">
                <label for="website">Ne popunjavajte ovo polje</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="field">
                <label class="label" for="c-name">Ime i prezime <span class="req" aria-hidden="true">*</span></label>
                <input class="input" id="c-name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" @error('name') aria-invalid="true" aria-describedby="c-name-err" @enderror>
                @error('name')<p class="error" id="c-name-err">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="field">
                    <label class="label" for="c-email">Mejl <span class="req" aria-hidden="true">*</span></label>
                    <input class="input" id="c-email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="c-email-err" @enderror>
                    @error('email')<p class="error" id="c-email-err">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="c-phone">Telefon</label>
                    <input class="input num" id="c-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}" @error('phone') aria-invalid="true" aria-describedby="c-phone-err" @enderror>
                    @error('phone')<p class="error" id="c-phone-err">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="field">
                <label class="label" for="c-message">Poruka <span class="req" aria-hidden="true">*</span></label>
                <textarea class="textarea" id="c-message" name="message" rows="5" required @error('message') aria-invalid="true" aria-describedby="c-message-err" @enderror>{{ old('message') }}</textarea>
                @error('message')<p class="error" id="c-message-err">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label class="check" for="c-consent">
                    <input type="checkbox" id="c-consent" name="consent" value="1" required @checked(old('consent')) @error('consent') aria-invalid="true" aria-describedby="c-consent-err" @enderror>
                    <span class="text-sm">Saglasan/saglasna sam da se moji podaci obrade radi odgovora na poruku, u skladu sa <a href="{{ route('privacy') }}">politikom privatnosti</a>.</span>
                </label>
                @error('consent')<p class="error" id="c-consent-err">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Pošalji poruku</button>
        </form>
    </div>
</x-layouts.app>
