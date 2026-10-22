@props(['users', 'max' => 4])

<span class="flex -space-x-1" aria-label="Tim: {{ $users->pluck('name')->implode(', ') }}">
    @foreach ($users->take($max) as $user)
        <x-avatar :user="$user" />
    @endforeach
    @if ($users->count() > $max)
        <span class="flex size-7 items-center justify-center rounded-full bg-muted text-[10px] font-semibold text-muted-foreground ring-2 ring-card">+{{ $users->count() - $max }}</span>
    @endif
</span>
