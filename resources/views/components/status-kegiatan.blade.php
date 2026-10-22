{{-- Badge status + badge Terlambat (turunan dari tanggal target). --}}
@props(['kegiatan', 'stack' => false])

<span @class(['flex gap-1', 'flex-col items-start' => $stack, 'flex-wrap items-center' => ! $stack])>
    <x-badge :tone="$kegiatan->status->tone()" :icon="$kegiatan->status->icon()">{{ $kegiatan->status->label() }}</x-badge>
    @if ($kegiatan->isTerlambat())
        <x-badge tone="danger" icon="triangle-alert">Terlambat {{ $kegiatan->hariTerlambat() }} hari</x-badge>
    @endif
</span>
