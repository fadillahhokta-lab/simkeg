<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-primary">Kegiatan</h1>
            <p class="text-sm text-muted-foreground">
                {{ $totalTerlihat }} kegiatan {{ auth()->user()->isAdmin() ? 'tercatat di seluruh unit' : 'yang melibatkan tim Anda' }}
            </p>
        </div>
        @can('create', App\Models\Kegiatan::class)
            <x-button :href="route('kegiatan.create')" icon="plus">Tambah Kegiatan</x-button>
        @endcan
    </div>

    <div class="flex flex-col gap-3 rounded-lg border border-border bg-card p-4 lg:flex-row lg:flex-wrap lg:items-center">
        <div class="relative min-w-0 lg:flex-1">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" />
            <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari kode atau judul" aria-label="Cari kode atau judul"
                class="h-10 w-full rounded-md border border-input bg-card pr-3 pl-9 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30">
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:flex">
            <x-select label="Jenis" wire:model.live="jenis" class="lg:w-40">
                <option value="">Semua jenis</option>
                @foreach ($daftarJenis as $j)
                    <option value="{{ $j->id }}">{{ $j->kode }} · {{ $j->nama }}</option>
                @endforeach
            </x-select>
            <x-select label="Status" wire:model.live="status" class="lg:w-40">
                <option value="">Semua status</option>
                @foreach (App\Enums\StatusKegiatan::cases() as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </x-select>
            <x-select label="Staff" wire:model.live="staff" class="lg:w-44">
                <option value="">Semua staff</option>
                @foreach ($daftarStaff as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </x-select>
            <input type="date" wire:model.live="dari" aria-label="Dari tanggal" class="h-10 rounded-md border border-input bg-card px-3 text-sm outline-none focus-visible:border-khaki-dark">
            <input type="date" wire:model.live="sampai" aria-label="Sampai tanggal" class="h-10 rounded-md border border-input bg-card px-3 text-sm outline-none focus-visible:border-khaki-dark">
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground">
        <span class="flex items-center gap-2">
            Menampilkan {{ $kegiatan->total() }} kegiatan
            @if ($terlambat)
                <x-badge tone="danger" icon="triangle-alert">Hanya yang terlambat</x-badge>
            @endif
        </span>
        @if ($adaFilter)
            <button type="button" wire:click="resetFilter" class="flex items-center gap-1 font-medium text-primary hover:underline">
                <x-icon name="x" /> Reset filter
            </button>
        @endif
    </div>

    <x-table>
        <x-slot:head>
            <th>Kode</th>
            <th>Judul</th>
            <th>Tim</th>
            <th class="w-44">Progres</th>
            <th>Status</th>
            <th>Target</th>
        </x-slot:head>

        @forelse ($kegiatan as $item)
            <tr wire:key="kegiatan-{{ $item->id }}" class="hover:bg-muted/40">
                <td class="whitespace-nowrap">
                    <a href="{{ route('kegiatan.show', $item) }}" class="font-mono text-sm font-semibold text-primary hover:underline">{{ $item->kode }}</a>
                </td>
                <td class="min-w-64">
                    <a href="{{ route('kegiatan.show', $item) }}" class="font-medium hover:underline">{{ $item->judul }}</a>
                    <span class="mt-1 flex items-center gap-1.5">
                        <x-badge mono title="{{ $item->jenisKegiatan->nama }}">{{ $item->jenisKegiatan->kode }}</x-badge>
                        <x-badge :tone="$item->prioritas->tone()">{{ $item->prioritas->label() }}</x-badge>
                    </span>
                </td>
                <td><x-avatar-stack :users="$item->tim" /></td>
                <td><x-progress :value="$item->progres" /></td>
                <td><x-status-kegiatan :kegiatan="$item" stack /></td>
                <td @class(['whitespace-nowrap', 'text-danger' => $item->isTerlambat()])>
                    {{ $item->tanggal_target_selesai?->translatedFormat('j M Y') ?? '–' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-10 text-center text-muted-foreground">Tidak ada kegiatan yang cocok dengan filter.</td>
            </tr>
        @endforelse
    </x-table>

    {{ $kegiatan->links('components.pagination') }}
</div>
