@props(['value'])

<span {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-muted" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
        <span @class(['block h-full rounded-full', 'bg-success' => $value >= 100, 'bg-primary' => $value < 100]) style="width: {{ $value }}%"></span>
    </span>
    <span class="w-10 text-right text-xs tabular-nums text-muted-foreground">{{ $value }}%</span>
</span>
