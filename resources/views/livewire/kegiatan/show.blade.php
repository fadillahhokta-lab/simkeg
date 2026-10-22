@php
    $k = $kegiatan;
    $durasi = $k->tanggal_mulai && $k->tanggal_target_selesai ? (int) $k->tanggal_mulai->diffInDays($k->tanggal_target_selesai) : null;
    $tgl = fn ($d) => $d?->translatedFormat('j M Y') ?? '–';
@endphp

<div class="flex flex-col gap-6">
    <a href="{{ route('kegiatan.index') }}" class="flex w-fit items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
        <x-icon name="arrow-left" /> Semua kegiatan
    </a>

    <x-flash />

    <div class="flex flex-col gap-3 rounded-lg border border-border bg-card p-5 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 flex-col gap-2">
            <span class="font-mono text-lg font-semibold text-khaki-dark">{{ $k->kode }}</span>
            <h1 class="text-2xl font-semibold tracking-tight text-primary">{{ $k->judul }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                <x-status-kegiatan :kegiatan="$k" />
                <x-badge :tone="$k->prioritas->tone()">{{ $k->prioritas->label() }}</x-badge>
                <x-badge mono>{{ $k->jenisKegiatan->kode }}</x-badge>
                <span class="text-xs text-muted-foreground">{{ $k->jenisKegiatan->nama }}</span>
            </div>
        </div>

        @can('update', $k)
            <div class="flex shrink-0 gap-2">
                <x-button variant="outline" icon="pencil" :href="route('kegiatan.edit', $k)">Edit</x-button>
                <x-button type="button" variant="outline" icon="trash" class="text-danger"
                    wire:click="hapus" wire:confirm="Hapus kegiatan {{ $k->kode }}? Tindakan ini tidak bisa dibatalkan.">Hapus</x-button>
            </div>
        @endcan
    </div>

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="flex min-w-0 flex-col gap-4 lg:col-span-2" x-data="{ tab: 'info' }">
            <div class="flex w-fit gap-1 rounded-md bg-muted p-1 text-sm" role="tablist">
                @foreach (['info' => 'Info', 'riwayat' => 'Riwayat ('.$k->logs->count().')', 'dinas' => 'Dinas ('.$k->dinasLuarKota->count().')'] as $key => $label)
                    <button type="button" role="tab" x-on:click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                        class="rounded px-3 py-1.5 font-medium text-muted-foreground aria-selected:bg-card aria-selected:text-foreground aria-selected:shadow-sm">{{ $label }}</button>
                @endforeach
            </div>

            {{-- Info --}}
            <div x-show="tab === 'info'" class="flex flex-col gap-4">
                <x-card>
                    <div class="flex flex-col gap-1">
                        <h2 class="text-sm font-medium text-muted-foreground">Deskripsi</h2>
                        <p class="text-sm leading-relaxed whitespace-pre-line">{{ $k->deskripsi ?: 'Belum ada deskripsi.' }}</p>
                    </div>

                    <dl class="grid grid-cols-2 gap-4 border-y border-border py-4 text-sm sm:grid-cols-4">
                        <div><dt class="text-xs text-muted-foreground">Mulai</dt><dd class="font-medium">{{ $tgl($k->tanggal_mulai) }}</dd></div>
                        <div><dt class="text-xs text-muted-foreground">Target selesai</dt><dd @class(['font-medium', 'text-danger' => $k->isTerlambat()])>{{ $tgl($k->tanggal_target_selesai) }}</dd></div>
                        <div><dt class="text-xs text-muted-foreground">Selesai aktual</dt><dd class="font-medium">{{ $tgl($k->tanggal_selesai_aktual) }}</dd></div>
                        <div><dt class="text-xs text-muted-foreground">Durasi rencana</dt><dd class="font-medium">{{ $durasi !== null ? $durasi.' hari' : '–' }}</dd></div>
                    </dl>

                    <div class="flex flex-col gap-2">
                        <h2 class="text-sm font-medium text-muted-foreground">Progres</h2>
                        <x-progress :value="$k->progres" />
                    </div>

                    @if ($k->detail_status)
                        <div class="flex flex-col gap-1 text-sm">
                            <h2 class="font-medium text-muted-foreground">Detail status</h2>
                            <p>{{ $k->detail_status }}</p>
                        </div>
                    @endif

                    @if ($k->catatan)
                        <div class="flex flex-col gap-1 rounded-md bg-khaki-soft p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-khaki-dark">Catatan terakhir</p>
                            <p class="text-sm whitespace-pre-line">{{ $k->catatan }}</p>
                        </div>
                    @endif
                </x-card>

                <x-card :title="'Tim ('.$k->tim->count().')'">
                    @if ($k->tim->isEmpty())
                        <p class="text-sm text-muted-foreground">Belum ada staff yang ditugaskan.</p>
                    @else
                        <ul class="grid gap-3 sm:grid-cols-2">
                            @foreach ($k->tim as $anggota)
                                <li class="flex items-center gap-3">
                                    <x-avatar :user="$anggota" size="md" />
                                    <span class="flex flex-col leading-tight">
                                        <span class="text-sm font-medium">{{ $anggota->name }}</span>
                                        <span class="font-mono text-xs text-muted-foreground">{{ $anggota->username }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </div>

            {{-- Riwayat --}}
            <x-card x-show="tab === 'riwayat'" x-cloak title="Riwayat perubahan" description="Perubahan status, progres, dan catatan.">
                @if ($k->logs->isEmpty())
                    <p class="text-sm text-muted-foreground">Belum ada perubahan.</p>
                @else
                    <ol class="flex flex-col divide-y divide-border">
                        @foreach ($k->logs as $log)
                            <li class="flex gap-3 py-3">
                                <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-md bg-navy-soft text-primary"><x-icon name="history" /></span>
                                <div class="flex min-w-0 flex-col gap-1 text-sm">
                                    <p class="text-xs text-muted-foreground">
                                        <span class="font-medium text-foreground">{{ $log->user?->name ?? 'Sistem' }}</span>
                                        · {{ $log->created_at->translatedFormat('j M Y, H.i') }}
                                    </p>
                                    @foreach ($log->ringkasan() as $ubah)
                                        @if ($ubah['field'] === 'catatan')
                                            <p><span class="text-muted-foreground">Catatan:</span> {{ $ubah['ke'] }}</p>
                                        @else
                                            <p>
                                                <span class="text-muted-foreground">{{ $ubah['label'] }}:</span>
                                                {{ $ubah['dari'] ?? '–' }} <span class="text-muted-foreground">→</span> <span class="font-medium">{{ $ubah['ke'] ?? '–' }}</span>
                                            </p>
                                        @endif
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-card>

            {{-- Dinas --}}
            <x-card x-show="tab === 'dinas'" x-cloak title="Dinas luar kota" description="Perjalanan dinas yang terkait kegiatan ini.">
                @if ($k->dinasLuarKota->isEmpty())
                    <p class="text-sm text-muted-foreground">Belum ada dinas luar kota.</p>
                @else
                    <ul class="flex flex-col divide-y divide-border">
                        @foreach ($k->dinasLuarKota->sortByDesc('tanggal_berangkat') as $dinas)
                            <li class="flex flex-col gap-1 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                                <span class="flex flex-col gap-0.5">
                                    <span class="flex items-center gap-1.5 font-medium"><x-icon name="map-pin" class="text-muted-foreground" />{{ $dinas->kota_tujuan }}</span>
                                    <span class="text-muted-foreground">{{ $dinas->keperluan }}</span>
                                </span>
                                <span class="flex items-center gap-3 text-muted-foreground">
                                    <x-avatar-stack :users="$dinas->staff" />
                                    <span class="whitespace-nowrap">{{ $dinas->tanggal_berangkat->translatedFormat('j M') }} – {{ $tgl($dinas->tanggal_kembali) }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <p class="text-xs text-muted-foreground">
                Dibuat oleh {{ $k->pembuat?->name ?? '–' }}
                · Terakhir diubah oleh {{ $k->pengubah?->name ?? '–' }}, {{ $k->updated_at->translatedFormat('j M Y, H.i') }}
            </p>
        </div>

        @can('updateProgres', $k)
            <form wire:submit="simpanProgres" class="flex flex-col gap-5 rounded-lg border border-border border-t-4 border-t-khaki bg-card p-5"
                x-data="{ progres: $wire.entangle('progres') }">
                <div class="flex flex-col gap-1">
                    <h2 class="text-base font-medium">Update progres</h2>
                    @if ($logTerakhir)
                        <p class="text-xs text-muted-foreground">Terakhir oleh {{ $logTerakhir->user?->name ?? 'Sistem' }}, {{ $logTerakhir->created_at->translatedFormat('j M Y, H.i') }}</p>
                    @endif
                </div>

                <fieldset class="flex flex-col gap-2">
                    <legend class="mb-2 text-sm font-medium">Status</legend>
                    <div class="grid grid-cols-2 gap-1 rounded-md bg-muted p-1">
                        @foreach (App\Enums\StatusKegiatan::cases() as $s)
                            <label class="cursor-pointer rounded px-3 py-2 text-center text-sm text-muted-foreground has-checked:bg-primary has-checked:text-primary-foreground has-focus-visible:ring-2 has-focus-visible:ring-khaki-dark">
                                <input type="radio" wire:model="status" value="{{ $s->value }}" class="sr-only">
                                {{ $s->label() }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <label for="progres" class="text-sm font-medium">Progres</label>
                        <span class="text-2xl font-semibold tabular-nums text-primary" x-text="progres + '%'"></span>
                    </div>
                    <input id="progres" type="range" min="0" max="100" step="5" x-model.number="progres" class="w-full accent-primary">
                    <div class="grid grid-cols-5 gap-1">
                        @foreach ([0, 25, 50, 75, 100] as $nilai)
                            <button type="button" x-on:click="progres = {{ $nilai }}"
                                class="h-8 rounded-md border border-border text-xs hover:bg-muted" :class="progres === {{ $nilai }} && 'border-primary font-semibold'">{{ $nilai }}%</button>
                        @endforeach
                    </div>
                    @error('progres') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <x-input label="Detail status" name="detailStatus" wire:model="detailStatus" placeholder="mis. Menunggu jadwal dengan pengelola" />

                <div class="flex flex-col gap-2">
                    <label for="catatan" class="text-sm font-medium">Catatan</label>
                    <textarea id="catatan" wire:model="catatan" rows="3" placeholder="Apa yang sudah dikerjakan atau kendala yang dihadapi?"
                        class="w-full rounded-md border border-input bg-card px-3 py-2 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-khaki-dark focus-visible:ring-3 focus-visible:ring-khaki-dark/30"></textarea>
                    @error('catatan') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <x-button size="lg" wire:loading.attr="disabled">Simpan pembaruan</x-button>
            </form>
        @endcan
    </div>
</div>
