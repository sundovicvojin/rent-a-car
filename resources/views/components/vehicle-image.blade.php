@props(['image' => null, 'sizes' => '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw', 'eager' => false, 'alt' => null])
@if ($image)
    <img
        src="{{ $image->url('md') }}"
        @if ($image->srcset()) srcset="{{ $image->srcset() }}" sizes="{{ $sizes }}" @endif
        alt="{{ $alt ?? $image->alt }}"
        width="1600" height="1000"
        @if ($eager) fetchpriority="high" @else loading="lazy" decoding="async" @endif
        {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}
    >
@else
    <div {{ $attributes->merge(['class' => 'flex h-full w-full items-center justify-center bg-surface-2 text-ink-2']) }} role="img" aria-label="Fotografija nije dostupna">
        <x-ico name="key" class="size-10" />
    </div>
@endif
