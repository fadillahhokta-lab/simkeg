@props(['label' => null])

<div class="relative">
    @if ($label)<label class="sr-only">{{ $label }}</label>@endif
    <select {{ $attributes->merge(['class' => 'h-10 w-full appearance-none rounded-md border border-input bg-card pr-9 pl-3 text-sm outline-none focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30']) }} @if ($label) aria-label="{{ $label }}" @endif>
        {{ $slot }}
    </select>
    <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground" />
</div>
