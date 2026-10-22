@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif {{ $attributes->class([
    'relative flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium transition-colors',
    'bg-sidebar-accent text-sidebar-primary before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-sidebar-primary' => $active,
    'text-sidebar-foreground hover:bg-sidebar-accent/60 hover:text-primary-foreground' => ! $active,
]) }}>
    <x-icon :name="$icon" />
    <span>{{ $slot }}</span>
</a>
