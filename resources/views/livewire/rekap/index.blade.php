<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold tracking-tight text-primary">Rekap & Export</h1>
        <p class="text-sm text-muted-foreground">
            {{ auth()->user()->isAdmin() ? 'Buat laporan periodik untuk seluruh unit' : 'Buat laporan periodik dari kegiatan dan dinas tim Anda' }}
        </p>
    </div>

    <section class="flex flex-col gap-3">
        <h2 class="text-sm font-medium text-muted-foreground">1. Pilih jenis rekap</h2>
        <div class="grid gap-3 md:grid-cols-3">
            @foreach (App\Rekap\Rekap::JENIS as $key => [$label, $keterangan, $icon])
                <label wire:key="jenis-{{ $key }}" class="flex cursor-pointer items-center gap-3 rounded-lg border border-border bg-card p-4 transition-colors hover:border-khaki has-checked:border-primary has-checked:ring-1 has-checked:ring-primary">
                    <input type="radio" wire:model.live="jenis" value="{{ $key }}" class="peer sr-only">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-navy-soft text-primary peer-checked:bg-primary peer-checked:text-primary-foreground"><x-icon :name="$icon" /></span>
                    <span class="flex flex-col">
                        <span class="font-medium">{{ $label }}</span>
                        <span class="text-xs text-muted-foreground">{{ $keterangan }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </section>

    <section class="flex flex-col gap-3">
        <h2 class="text-sm font-medium text-muted-foreground">2. Atur periode</h2>
        <div class="flex flex-col gap-4 rounded-lg border border-border bg-card p-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-wrap gap-2">
                @foreach ($presets as $key => [$label, $awal, $akhir])
                    @php $aktif = $dari === $awal->toDateString() && $sampai === $akhir->toDateString(); @endphp
                    <button type="button" wire:click="pilihPeriode('{{ $key }}')" @class([
                        'h-9 rounded-full border px-4 text-sm transition-colors',
                        'border-khaki bg-khaki-soft font-medium text-khaki-dark' => $aktif,
                        'border-border hover:bg-muted' => ! $aktif,
                    ])>{{ $label }}</button>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-3">
                <x-input label="Dari" name="dari" type="date" wire:model.live="dari" />
                <x-input label="Sampai" name="sampai" type="date" wire:model.live="sampai" />
            </div>
        </div>
    </section>

    <section class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-medium text-muted-foreground">
                3. Pratinjau @if ($rekap) ({{ collect($rekap['tabel'])->sum(fn ($t) => count($t['baris'])) }} baris) @endif
            </h2>
            @if ($rekap)
                <div class="flex gap-2">
                    <x-button variant="outline" icon="file-spreadsheet" :href="$urlExcel">Export Excel</x-button>
                    <x-button icon="download" :href="$urlPdf">Export PDF</x-button>
                </div>
            @endif
        </div>

        @if (! $rekap)
            <div class="rounded-lg border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger" role="alert">
                Periode tidak valid. Pastikan tanggal akhir tidak sebelum tanggal awal.
            </div>
        @else
            <div class="overflow-hidden rounded-lg border border-border bg-card" wire:loading.class="opacity-60">
                <div class="border-b-4 border-khaki bg-primary px-5 py-4 text-primary-foreground">
                    <p class="text-xs font-semibold uppercase tracking-widest text-khaki">{{ config('app.name') }}</p>
                    <p class="mt-1 text-lg font-semibold">{{ $rekap['judul'] }}</p>
                    <p class="text-sm text-sidebar-foreground">Periode {{ $periode }} · Dicetak oleh {{ auth()->user()->name }}</p>
                </div>

                @foreach ($rekap['tabel'] as $tabel)
                    <div class="flex flex-col">
                        <h3 class="border-b border-border px-4 py-3 text-sm font-medium">{{ $tabel['judul'] }} <span class="text-muted-foreground">({{ count($tabel['baris']) }})</span></h3>
                        @if (empty($tabel['baris']))
                            <p class="px-4 py-6 text-center text-sm text-muted-foreground">Tidak ada data pada periode ini.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="border-b border-border text-muted-foreground">
                                        <tr>@foreach ($tabel['kolom'] as $kolom)<th class="px-4 py-2.5 font-medium whitespace-nowrap">{{ $kolom }}</th>@endforeach</tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        @foreach ($tabel['baris'] as $baris)
                                            <tr>
                                                @foreach ($baris as $i => $sel)
                                                    {{-- Teks pendek (kode, tanggal, angka) tidak dibungkus; teks panjang dibatasi lebar dan 3 baris. --}}
                                                    @php $panjang = is_string($sel) && mb_strlen($sel) > 24; @endphp
                                                    <td @class(['px-4 py-2.5 align-top', 'whitespace-nowrap' => ! $panjang, 'min-w-48 max-w-72' => $panjang, 'font-mono font-semibold' => $i === 0 && $jenis === 'kegiatan'])>
                                                        @if ($panjang)
                                                            <span class="line-clamp-3 whitespace-pre-line" title="{{ $sel }}">{{ $sel }}</span>
                                                        @else
                                                            {{ $sel ?? '–' }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
