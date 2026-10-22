@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'icon' => null])

@php
    $variants = [
        'primary' => 'bg-primary text-primary-foreground hover:bg-primary/90',
        'outline' => 'border border-border bg-card text-foreground hover:bg-muted',
        'ghost' => 'text-muted-foreground hover:bg-muted hover:text-foreground',
        'danger' => 'bg-danger text-primary-foreground hover:bg-danger/90',
    ];
    $sizes = [
        'sm' => 'h-8 px-2.5 text-xs',
        'md' => 'h-10 px-3.5 text-sm',
        'lg' => 'h-11 px-4 text-sm',
    ];
    $class = 'inline-flex shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-md font-medium transition-colors outline-none focus-visible:ring-3 focus-visible:ring-khaki-dark/50 disabled:pointer-events-none disabled:opacity-50 '
        .$variants[$variant].' '.$sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $class]) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
