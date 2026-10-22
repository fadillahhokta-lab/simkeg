@php $d = $dinas; @endphp

<div class="flex flex-col gap-6">
    <a href="{{ route('dinas.index') }}" class="flex w-fit items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
        <x-icon name="arrow-left" /> Semua dinas luar kota
    </a>

    <x-flash />

    <div class="flex flex-col gap-3 rounded-lg border border-border bg-card p-5 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 flex-col gap-2">
            <span class="flex items-center gap-1.5 text-sm text-muted-foreground"><x-icon name="plane" /> Dinas luar kota</span>
            <h1 class="flex items-center gap-2 text-2xl font-semibold tracking-tight text-primary"><x-icon name="map-pin" class="size-6" />{{ $d->kota_tujuan }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                <x-badge :tone="$d->statusTone()">{{ $d->statusLabel() }}</x-badge>
                <span class="text-sm text-muted-foreground">{{ $d->tanggal_berangkat->translatedFormat('j M Y') }} – {{ $d->tanggal_kembali->translatedFormat('j M Y') }} · {{ $d->durasiHari() }} hari</span>
            </div>
        </div>

        @can('update', $d)
            <div class="flex shrink-0 gap-2">
                <x-button variant="outline" icon="pencil" :href="route('dinas.edit', $d)">Edit</x-button>
                <x-button type="button" variant="outline" icon="trash" class="text-danger"
                    wire:click="hapus" wire:confirm="Hapus dinas ke {{ $d->kota_tujuan }}? Lampirannya ikut terhapus.">Hapus</x-button>
            </div>
        @endcan
    </div>

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="flex min-w-0 flex-col gap-4 lg:col-span-2">
            <x-card>
                <div class="flex flex-col gap-1">
                    <h2 class="text-sm font-medium text-muted-foreground">Kegiatan</h2>
                    @php $bisaBukaKegiatan = auth()->user()->can('view', $d->kegiatan); @endphp
                    <{{ $bisaBukaKegiatan ? 'a' : 'div' }} @if ($bisaBukaKegiatan) href="{{ route('kegiatan.show', $d->kegiatan) }}" @endif class="group flex flex-col gap-0.5">
                        <span class="flex items-center gap-2">
                            <x-badge mono>{{ $d->kegiatan->jenisKegiatan->kode }}</x-badge>
                            <span @class(['font-mono text-sm font-semibold text-primary', 'group-hover:underline' => $bisaBukaKegiatan])>{{ $d->kegiatan->kode }}</span>
                        </span>
                        <span @class(['font-medium', 'group-hover:underline' => $bisaBukaKegiatan])>{{ $d->kegiatan->judul }}</span>
                    </{{ $bisaBukaKegiatan ? 'a' : 'div' }}>
                </div>
                <div class="flex flex-col gap-1 border-t border-border pt-4">
                    <h2 class="text-sm font-medium text-muted-foreground">Keperluan</h2>
                    <p class="text-sm leading-relaxed whitespace-pre-line">{{ $d->keperluan }}</p>
                </div>
            </x-card>

            <x-card :title="'Staff yang berangkat ('.$d->staff->count().')'">
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($d->staff as $anggota)
                        <li class="flex items-center gap-3">
                            <x-avatar :user="$anggota" size="md" />
                            <span class="flex flex-col leading-tight">
                                <span class="text-sm font-medium">{{ $anggota->name }}</span>
                                <span class="font-mono text-xs text-muted-foreground">{{ $anggota->username }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-card>

            <p class="text-xs text-muted-foreground">
                Dicatat oleh {{ $d->dicatatOleh?->name ?? '–' }}, {{ $d->created_at->translatedFormat('j M Y, H.i') }}
                @if ($d->updated_at->ne($d->created_at))
                    · Terakhir diubah oleh {{ $d->pengubah?->name ?? '–' }}, {{ $d->updated_at->translatedFormat('j M Y, H.i') }}
                @endif
            </p>
        </div>

        <x-card title="Lampiran surat tugas">
            @if ($d->lampiran_surat_tugas)
                <a href="{{ route('dinas.lampiran', $d) }}" class="flex items-center gap-3 rounded-md border border-border p-3 hover:bg-muted">
                    <span class="flex size-9 items-center justify-center rounded-md bg-navy-soft text-primary"><x-icon name="paperclip" /></span>
                    <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ $d->namaLampiran() }}</span>
                    <x-icon name="download" class="text-muted-foreground" />
                </a>
            @else
                <p class="text-sm text-muted-foreground">Tidak ada lampiran.</p>
            @endif
        </x-card>
    </div>
</div>
