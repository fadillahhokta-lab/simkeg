@props(['title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'flex min-w-0 flex-col gap-4 rounded-lg border border-border bg-card p-4']) }}>
    @if ($title || isset($actions))
        <div class="flex items-start justify-between gap-3">
            <div class="flex flex-col gap-1">
                @if ($title)<h2 class="text-base font-medium leading-snug">{{ $title }}</h2>@endif
                @if ($description)<p class="text-sm text-muted-foreground">{{ $description }}</p>@endif
            </div>
            {{ $actions ?? '' }}
        </div>
    @endif

    {{ $slot }}
</div>
