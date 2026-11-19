@props(['size' => 'md'])
@php
    $box = $size === 'lg' ? 'size-[64px]' : 'size-[42px]';
@endphp
<span {{ $attributes->class([$box, 'inline-flex shrink-0 items-center justify-center']) }}>
    <img src="{{ asset('images/logo-ruanglombajti.png') }}" alt="Logo RuangLombaJTI" class="h-full w-full object-contain">
</span>