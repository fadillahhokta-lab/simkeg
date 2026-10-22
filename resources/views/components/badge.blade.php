@props(['tone' => 'neutral', 'icon' => null, 'mono' => false])

@php
    $tones = [
        'neutral' => 'bg-muted text-muted-foreground',
        'primary' => 'bg-primary text-primary-foreground',
        'success' => 'bg-success-soft text-success',
        'danger' => 'bg-danger-soft text-danger',
        'khaki' => 'bg-khaki-soft text-khaki-dark',
        'outline' => 'border border-border bg-card text-muted-foreground',
        'outline-danger' => 'border border-danger/40 bg-card text-danger',
        'outline-khaki' => 'border border-khaki bg-card text-khaki-dark',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium',
    'font-mono font-semibold' => $mono,
    $tones[$tone],
]) }}>
    @if ($icon)<x-icon :name="$icon" class="size-3" />@endif
    {{ $slot }}
</span>
