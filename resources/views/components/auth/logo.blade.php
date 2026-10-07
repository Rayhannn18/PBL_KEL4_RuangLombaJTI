@props(['size' => 'md'])
@php
    $box = $size === 'lg' ? 'size-[50px] rounded-2xl' : 'size-[38px] rounded-xl';
    $icon = $size === 'lg' ? 'size-6' : 'size-[18px]';
@endphp
<span {{ $attributes->class([$box, 'inline-flex shrink-0 items-center justify-center bg-gradient-to-br from-brand to-brand-dark shadow-md shadow-brand/30']) }}>
    <svg class="{{ $icon }} text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
    </svg>
</span>