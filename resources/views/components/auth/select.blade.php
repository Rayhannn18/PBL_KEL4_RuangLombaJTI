@props([
    'name',
    'label',
    'options' => [],
    'placeholder' => 'Pilih salah satu',
])
@php
    $hasError = $errors->has($name);
    $selected = (string) old($name);
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="mb-1.5 block text-[13px] font-semibold text-muted">{{ $label }}</label>

    <div class="relative">
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @class([
                'h-[50px] w-full appearance-none rounded-2xl border bg-white pl-4 pr-11 text-sm font-medium outline-none transition focus:ring-4',
                'text-ink' => $selected !== '',
                'text-slate-400' => $selected === '',
                'border-line focus:border-brand focus:ring-brand/15' => ! $hasError,
                'border-red-400 focus:border-red-500 focus:ring-red-500/15' => $hasError,
            ])
            @if ($hasError) aria-invalid="true" @endif
            onchange="this.classList.toggle('text-slate-400', this.value === ''); this.classList.toggle('text-ink', this.value !== '')"
        >
            <option value="" @selected($selected === '')>{{ $placeholder }}</option>
            @foreach ($options as $option)
                <option value="{{ $option }}" class="text-ink" @selected($selected === (string) $option)>{{ $option }}</option>
            @endforeach
        </select>
        <svg class="pointer-events-none absolute inset-y-0 right-4 my-auto size-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </div>

    @error($name)
        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>