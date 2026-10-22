@props(['label' => null, 'name', 'id' => null, 'type' => 'text'])

@php
    $id ??= $name;
    $hasError = $errors->has($name);
@endphp

<div class="flex flex-col gap-2">
    @if ($label)
        <label for="{{ $id }}" class="text-sm font-medium leading-none">{{ $label }}</label>
    @endif

    <div class="relative">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($type !== 'password') value="{{ old($name, $attributes->get('value')) }}" @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except('value')->class([
                'h-10 w-full min-w-0 rounded-md border bg-card px-3 text-sm outline-none transition-colors placeholder:text-muted-foreground',
                'focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30',
                'border-input' => ! $hasError,
                'border-danger ring-3 ring-danger/15' => $hasError,
            ]) }}
        >
        {{ $slot }}
    </div>

    @error($name)
        <p id="{{ $id }}-error" class="text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
