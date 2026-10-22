@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'default', 'href' => null])

@php
    $iconTone = [
        'default' => 'bg-navy-soft text-primary',
        'success' => 'bg-success-soft text-success',
        'danger' => 'bg-danger-soft text-danger',
        'khaki' => 'bg-khaki-soft text-khaki-dark',
    ][$tone];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class([
    'flex flex-col gap-2 rounded-lg border border-border bg-card p-4 transition-colors',
    'hover:border-khaki' => $href,
    'border-l-4 border-l-danger' => $tone === 'danger',
]) }}>
    <div class="flex items-start justify-between gap-2">
        <p class="text-sm font-medium text-muted-foreground">{{ $label }}</p>
        @if ($icon)
            <span class="flex size-8 items-center justify-center rounded-md {{ $iconTone }}"><x-icon :name="$icon" /></span>
        @endif
    </div>
    <p @class(['text-3xl font-semibold tabular-nums', 'text-danger' => $tone === 'danger', 'text-primary' => $tone !== 'danger'])>{{ $value }}</p>
    @if ($hint)<p class="text-xs text-muted-foreground">{{ $hint }}</p>@endif
</{{ $tag }}>
