@props(['cents'])
<span {{ $attributes->merge(['class' => 'num whitespace-nowrap']) }}>{{ \App\Support\Money::format((int) $cents) }}</span>
