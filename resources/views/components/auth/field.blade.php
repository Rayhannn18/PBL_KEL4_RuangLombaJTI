@props([
    'name',
    'label',
    'type' => 'text',
    'placeholder' => '',
    'autocomplete' => null,
    'aside' => null,
    'check' => null,
])
@php
    $isPassword = $type === 'password';
    $hasError = $errors->has($name);
@endphp
<div>
    <div class="mb-1.5 flex items-baseline justify-between gap-3">
        <label for="{{ $name }}" class="text-[13px] font-semibold text-muted"><span data-text>{{ $label }}</span></label>
        @if ($aside !== null)
            <span id="{{ $name }}-aside" class="text-xs text-muted">{{ $aside }}</span>
        @endif
    </div>

    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if (! $isPassword) value="{{ old($name) }}" @endif
            placeholder="{{ $placeholder }}"
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @class([
                'h-[50px] w-full rounded-2xl border bg-white px-4 text-sm font-medium text-ink outline-none transition placeholder:font-normal placeholder:text-slate-400 focus:ring-4',
                ($isPassword || $check) ? 'pr-11' : null,
                'border-line focus:border-brand focus:ring-brand/15' => ! $hasError,
                'border-red-400 focus:border-red-500 focus:ring-red-500/15' => $hasError,
            ])
            @if ($hasError) aria-invalid="true" @endif
        >

        @if ($isPassword)
            <button type="button" data-toggle-password="{{ $name }}" aria-pressed="false" aria-label="Tampilkan atau sembunyikan kata sandi"
                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-muted hover:text-ink">
                <svg data-eye class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                <svg data-eye-off class="hidden size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
            </button>
        @elseif ($check)
            <span data-check-for="{{ $name }}" data-check-rule="{{ $check }}" class="pointer-events-none absolute inset-y-0 right-0 hidden w-11 items-center justify-center text-emerald-600 [&:not(.hidden)]:flex">
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
        @endif
    </div>

    @error($name)
        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror

    {{ $slot }}
</div>