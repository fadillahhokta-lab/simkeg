@props(['user', 'size' => 'sm'])

<span title="{{ $user->name }}" {{ $attributes->class([
    'flex shrink-0 items-center justify-center rounded-full bg-navy-soft font-semibold text-primary ring-2 ring-card',
    'size-7 text-[10px]' => $size === 'sm',
    'size-9 text-xs' => $size === 'md',
]) }}>{{ $user->initials() }}</span>
