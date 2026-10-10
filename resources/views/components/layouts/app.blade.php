<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $pageTitle = isset($title) ? $title.' | '.$company->name : $company->name.' – iznajmljivanje vozila';
        $pageDescription = $description ?? 'Iznajmite vozilo u Novom Sadu: jasna cena po danu, bez skrivenih troškova, preuzimanje i vraćanje na više lokacija.';
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    @isset($noindex)<meta name="robots" content="noindex, nofollow">@endisset
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="sr_RS">
    <meta property="og:site_name" content="{{ $company->name }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    @isset($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endisset
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0e6e8c">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%230E6E8C'/%3E%3Cpath d='M6 20v-4l3-5h14l3 5v4h-3v2h-3v-2H12v2H9v-2Z' fill='%23fff'/%3E%3C/svg%3E">
    <link rel="preload" href="/fonts/dm-sans-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/bricolage-grotesque-latin.woff2" as="font" type="font/woff2" crossorigin>
    @isset($jsonLd)<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>@endisset
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (! empty($map)) @vite(['resources/js/map.js']) @endif
    @if (! empty($livewire)) @livewireStyles @endif
</head>
<body>
    <a href="#sadrzaj" class="skip-link">Preskoči na sadržaj</a>

    <header class="sticky top-0 z-40 border-b border-line bg-surface/95 backdrop-blur">
        <div class="wrap flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display text-lg font-bold text-ink no-underline">
                <span class="grid size-9 place-items-center bg-brand text-on-brand" style="border-radius: 6px 14px 6px 6px;"><x-ico name="key" class="size-5" /></span>
                <span>{{ $company->name }}</span>
            </a>

            <nav aria-label="Glavna navigacija" class="hidden items-center gap-1 lg:flex">
                @foreach ([['home', 'Početna'], ['fleet.index', 'Vozni park'], ['terms', 'Uslovi najma'], ['contact', 'Kontakt']] as [$route, $label])
                    <a href="{{ route($route) }}" @if (request()->routeIs($route) || ($route === 'fleet.index' && request()->routeIs('fleet.*'))) aria-current="page" @endif
                       class="inline-flex min-h-11 items-center px-3 text-sm font-semibold text-ink no-underline hover:text-brand-text aria-[current=page]:text-brand-text aria-[current=page]:underline aria-[current=page]:decoration-2 aria-[current=page]:underline-offset-8">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @if ($company->phone)
                    <a href="tel:{{ preg_replace('/[^+\d]/', '', $company->phone) }}" class="num hidden min-h-11 items-center gap-2 px-3 text-sm font-medium text-ink no-underline xl:inline-flex"><x-ico name="phone" class="size-4" />{{ $company->phone }}</a>
                @endif
                <a href="{{ route('reservation.create') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">Rezerviši</a>
                <button type="button" class="btn btn-ghost btn-sm lg:hidden" data-nav-toggle aria-expanded="false" aria-controls="mobilni-meni" aria-label="Meni">
                    <x-ico name="menu" />
                </button>
            </div>
        </div>
        <nav id="mobilni-meni" data-nav-menu class="hidden border-t border-line bg-surface lg:hidden" aria-label="Mobilna navigacija">
            <div class="wrap flex flex-col py-2">
                @foreach ([['home', 'Početna'], ['fleet.index', 'Vozni park'], ['reservation.create', 'Rezervacija'], ['terms', 'Uslovi najma'], ['contact', 'Kontakt']] as [$route, $label])
                    <a href="{{ route($route) }}" class="flex min-h-12 items-center border-b border-line text-base font-semibold text-ink no-underline last:border-0">{{ $label }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <main id="sadrzaj" tabindex="-1">
        {{ $slot }}
    </main>

    <footer class="mt-24 bg-ink text-page">
        <div class="wrap grid gap-10 py-14 md:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <p class="font-display text-2xl font-bold" style="color: var(--bg)">{{ $company->name }}</p>
                <p class="mt-3 max-w-sm" style="color: color-mix(in srgb, var(--bg) 75%, transparent)">Pouzdana vozila, jasna cena i preuzimanje tamo gde vam odgovara.</p>
            </div>
            <div>
                <p class="eyebrow" style="color: color-mix(in srgb, var(--bg) 70%, transparent)">Kontakt</p>
                <ul class="mt-3 space-y-2" style="color: var(--bg)">
                    @if ($company->phone)<li><a href="tel:{{ preg_replace('/[^+\d]/', '', $company->phone) }}" class="num inline-flex min-h-11 items-center gap-2 no-underline" style="color: var(--bg)"><x-ico name="phone" class="size-4" />{{ $company->phone }}</a></li>@endif
                    @if ($company->email)<li><a href="mailto:{{ $company->email }}" class="inline-flex min-h-11 items-center gap-2 no-underline" style="color: var(--bg)"><x-ico name="mail" class="size-4" />{{ $company->email }}</a></li>@endif
                    @if ($company->address)<li class="flex items-start gap-2"><x-ico name="pin" class="mt-1 size-4 shrink-0" />{{ $company->address }}</li>@endif
                </ul>
            </div>
            <div>
                <p class="eyebrow" style="color: color-mix(in srgb, var(--bg) 70%, transparent)">Radno vreme</p>
                <p class="mt-3" style="color: var(--bg)">
                    @foreach (preg_split('/\R/', (string) $company->working_hours) as $line)
                        {{ $line }}@if (! $loop->last)<br>@endif
                    @endforeach
                </p>
            </div>
        </div>
        <div class="border-t" style="border-color: color-mix(in srgb, var(--bg) 18%, transparent)">
            <div class="wrap flex flex-col gap-2 py-5 text-sm sm:flex-row sm:items-center sm:justify-between" style="color: color-mix(in srgb, var(--bg) 75%, transparent)">
                <p>© {{ date('Y') }} {{ $company->name }}. Sva prava zadržana.</p>
                <p class="flex gap-4">
                    <a href="{{ route('terms') }}" class="inline-flex min-h-11 items-center no-underline" style="color: var(--bg)">Uslovi najma</a>
                    <a href="{{ route('privacy') }}" class="inline-flex min-h-11 items-center no-underline" style="color: var(--bg)">Politika privatnosti</a>
                </p>
            </div>
        </div>
    </footer>
    @if (! empty($livewire)) @livewireScripts @endif
</body>
</html>
