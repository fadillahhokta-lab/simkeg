<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-primary">Dinas Luar Kota</h1>
            <p class="text-sm text-muted-foreground">
                {{ auth()->user()->isAdmin() ? 'Pencatatan perjalanan dinas yang terhubung ke kegiatan' : 'Perjalanan dinas dari kegiatan tim Anda dan dinas yang Anda ikuti' }}
            </p>
        </div>
        @can('create', App\Models\DinasLuarKota::class)
            <x-button :href="route('dinas.create')" icon="plus">Catat Dinas</x-button>
        @endcan
    </div>

    <x-flash />

    <div class="flex flex-col gap-3 rounded-lg border border-border bg-card p-4 lg:flex-row lg:flex-wrap lg:items-center">
        <div class="relative min-w-0 lg:flex-1">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" />
            <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari kota, keperluan, atau kode kegiatan" aria-label="Cari dinas"
                class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30">
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:flex">
            <x-select label="Kegiatan" wire:model.live="kegiatan" class="lg:w-44">
                <option value="">Semua kegiatan</option>
                @foreach ($daftarKegiatan as $k)
                    <option value="{{ $k->id }}">{{ $k->kode }}</option>
                @endforeach
            </x-select>
            <x-select label="Staff" wire:model.live="staff" class="lg:w-44">
                <option value="">Semua staff</option>
                @foreach ($daftarStaff as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Status" wire:model.live="status" class="lg:w-36">
                <option value="">Semua status</option>
                @foreach (App\Models\DinasLuarKota::STATUS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <input type="date" wire:model.live="dari" aria-label="Dari tanggal" class="h-10 rounded-md border border-input bg-card px-3 text-sm outline-none focus-visible:border-khaki-dark">
            <input type="date" wire:model.live="sampai" aria-label="Sampai tanggal" class="h-10 rounded-md border border-input bg-card px-3 text-sm outline-none focus-visible:border-khaki-dark">
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground">
        <span>Menampilkan {{ $dinas->total() }} perjalanan dinas</span>
        <span class="flex items-center gap-2">
            @if ($adaFilter)
                <button type="button" wire:click="resetFilter" class="flex items-center gap-1 font-medium text-primary hover:underline"><x-icon name="x" /> Reset filter</button>
            @endif
            <x-badge tone="primary">{{ $jumlahBerjalan }} berjalan</x-badge>
            <x-badge tone="khaki">{{ $jumlahTerjadwal }} terjadwal</x-badge>
        </span>
    </div>

    <x-table>
        <x-slot:head>
            <th>Tujuan</th>
            <th>Kegiatan</th>
            <th>Staff</th>
            <th>Tanggal</th>
            <th>Lampiran</th>
            @if (auth()->user()->isAdmin())<th class="text-right">Aksi</th>@endif
        </x-slot:head>

        @forelse ($dinas as $item)
            <tr wire:key="dinas-{{ $item->id }}" class="hover:bg-muted/40">
                <td class="whitespace-nowrap">
                    <a href="{{ route('dinas.show', $item) }}" class="flex items-center gap-1.5 font-medium hover:underline">
                        <x-icon name="map-pin" class="text-muted-foreground" />{{ $item->kota_tujuan }}
                    </a>
                    <x-badge :tone="$item->statusTone()" class="mt-1">{{ $item->statusLabel() }}</x-badge>
                </td>
                <td class="max-w-72">
                    @if ($kegiatanTerbuka === null || in_array($item->kegiatan_id, $kegiatanTerbuka))
                        <a href="{{ route('kegiatan.show', $item->kegiatan) }}" class="font-mono text-sm font-semibold text-primary hover:underline">{{ $item->kegiatan->kode }}</a>
                    @else
                        <span class="font-mono text-sm font-semibold text-primary">{{ $item->kegiatan->kode }}</span>
                    @endif
                    <p class="truncate text-xs text-muted-foreground" title="{{ $item->keperluan }}">{{ $item->keperluan }}</p>
                </td>
                <td><x-avatar-stack :users="$item->staff" /></td>
                <td class="whitespace-nowrap">
                    {{ $item->tanggal_berangkat->translatedFormat('j M Y') }} – {{ $item->tanggal_kembali->translatedFormat('j M Y') }}
                    <p class="text-xs text-muted-foreground">{{ $item->durasiHari() }} hari</p>
                </td>
                <td class="max-w-48">
                    @if ($item->lampiran_surat_tugas)
                        <a href="{{ route('dinas.lampiran', $item) }}" class="flex items-center gap-1.5 hover:underline" title="{{ $item->namaLampiran() }}">
                            <x-icon name="paperclip" class="text-muted-foreground" /><span class="truncate">{{ $item->namaLampiran() }}</span>
                        </a>
                    @else
                        <span class="text-muted-foreground">–</span>
                    @endif
                </td>
                @if (auth()->user()->isAdmin())
                    <td class="text-right whitespace-nowrap">
                        <a href="{{ route('dinas.edit', $item) }}" class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="Edit dinas {{ $item->kota_tujuan }}"><x-icon name="pencil" /></a>
                        <button type="button" wire:click="hapus({{ $item->id }})" wire:confirm="Hapus dinas ke {{ $item->kota_tujuan }}? Lampirannya ikut terhapus."
                            class="inline-flex size-8 items-center justify-center rounded-md text-danger hover:bg-danger-soft" aria-label="Hapus dinas {{ $item->kota_tujuan }}"><x-icon name="trash" /></button>
                    </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="6" class="py-10 text-center text-muted-foreground">Tidak ada perjalanan dinas yang cocok.</td></tr>
        @endforelse
    </x-table>

    {{ $dinas->links('components.pagination') }}

    <p class="flex items-center gap-2 text-xs text-muted-foreground">
        <x-icon name="lock" class="size-3.5" />
        Lampiran surat tugas disimpan privat dan hanya bisa diunduh oleh Admin, tim kegiatan terkait, dan staff yang berangkat.
    </p>
</div>
